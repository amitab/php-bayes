<?php

namespace Sentiment\Analysis\Tools;
use PDO;
use Porter;

class BayesClassifier {

	private $labels;
	private $score;
	private $client;
	private $words;

	public $tokenizer;
	public $textCleaner;
	public $total;

	public function __construct ($tokenizer, $cleaner, $labels, $pdo) {
		$this->client = $pdo;
		$this->tokenizer = $tokenizer;
		$this->textCleaner = $cleaner;
		$this->labels = [];
		$this->total = 0;

        $fields = [];
        foreach ($labels as $label) {
            array_push($fields, "sum($label) as " . $label . "_total");
        }

		$stmt = $this->client->prepare("SELECT " . implode($fields, ",") . " FROM words;");
		$stmt->execute();

		if ($stmt->rowCount() == 0) {
			echo "Warning: Empty results\n";
		}

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
		foreach ($labels as $label) {
			$this->total += $row[$label . '_total'];
			array_push($this->labels, array(
				'label' => $label,
				'total_word_count' => ($stmt->rowCount() == 0 ? 0 : $row[$label . '_total'])
			));
		}
		$stmt->closeCursor();
		$stmt = null;
	}

	private function findOccuranceCount($query_data) {
		$occurance_count = 0;
		foreach($this->labels as $label) {
			$occurance_count += $query_data[$label['label']];
		}
		return $occurance_count;
	}

	private function findInverseData ($class, $query_data) {
		$inverse_data['total_word_count'] = 0;
		$inverse_data['word_count'] = 0;

		foreach($this->labels as $label) {
			if ($label['label'] != $class) {
				$inverse_data['total_word_count'] += $label['total_word_count'];
				$inverse_data['word_count'] += $query_data[$label['label']];
			}
		}

		return $inverse_data;
	}

	public function pofLabel ($label) {
		return $label['total_word_count']/$this->total;
	}

	public function pofNotLabel ($class) {
		$tmp = 0;
		foreach ($this->labels as $label) {
			if ($label['label'] == $class['label'])
				continue;
			$tmp += $label['total_word_count'];
		}
		return $tmp/$this->total;
	}

	public function pofLabelIfWord ($word, $label) {
		$stmt = $this->client->prepare("SELECT word, pos, neg FROM words WHERE word = :word;");

		if (!$stmt->execute(['word' => $word])) {
			return 0;
		}
		if ($stmt->rowCount() == 0) {
			$stmt->closeCursor();
			$stmt = null;
			return 0;
		}
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		$occurance_count = $this->findOccuranceCount($data);

		if ($occurance_count == 0) return 0;

		$pofWordIfLabel = $data[$label['label']]/$label['total_word_count'];

		$inverse_data = $this->findInverseData($label['label'], $data);
		$pofWordIfNotLabel = $inverse_data['word_count']/$inverse_data['total_word_count'];

		$pofLabel = $this->pofLabel($label);
		$pofNotLabel = $this->pofNotLabel($label);
		$pofLabelIfWord = ($pofWordIfLabel * $pofLabel)/($pofWordIfLabel * $pofLabel + $pofWordIfNotLabel * $pofNotLabel);

		$wordicity = ( (10 * 0.5) + ($occurance_count * $pofLabelIfWord) ) / ( 10 + $occurance_count );
		if($wordicity == 0) $wordicity = 0.001;
		else if ($wordicity == 1) $wordicity = 0.999;

		return $wordicity;
	}

	public function findSentiment ($tokens) {
		$test = [];
		foreach($this->labels as $label) {
			$test[$label['label']] = 0;
			foreach($tokens as $token) {
				$token = Porter::Stem($token);

				$pOfLabelIfWord = $this->pOfLabelIfWord($token, $label);
				if ($pOfLabelIfWord > 0)
					$test[$label['label']] += (log( 1 - $pOfLabelIfWord ) - log( $pOfLabelIfWord ));
			}
		}
		return $test;
	}

	public function classify ($string) {
		$scores = [
			'pos' => 0,
			'neg' => 0
		];
		$splits = $this->tokenizer->getSplits($string);
		echo "$string\n";
		for($i = 0; $i < count($splits); $i++) {
			$emoticons = $this->tokenizer->getEmoticons($splits[$i]);
			$bag_of_words = $this->tokenizer->tokenize($splits[$i]);
	
			$bag_of_words = $this->textCleaner->clean($bag_of_words);
			$res = $this->findSentiment(array_merge($bag_of_words, $emoticons));
			if ($i % 2 != 0 || $i == 0) {
				echo "Keep for " . $i . " " . $splits[$i] ."\n";
				$scores['pos'] += $res['pos'];
				$scores['neg'] += $res['neg'];
			} else {
				echo "Swap for " . $i . " " . $splits[$i] ."\n";
				$scores['neg'] += $res['pos'];
				$scores['pos'] += $res['neg'];
			}
			echo "POS: " . $res['pos'] . ", " . $scores['pos'] . "\n";
			echo "NEG: " . $res['neg'] . ", " . $scores['neg'] . "\n";
		}
		echo "----------------------------------------------------------\n";
		$scores['pos'] = 1 / ( 1 + exp ( $scores['pos'] ) );
		$scores['neg'] = 1 / ( 1 + exp ( $scores['neg'] ) );
		return $scores;		
	}

	public function tokenize ($string) {
		$emoticons = $this->tokenizer->getEmoticons($string);
		$bag_of_words = $this->tokenizer->tokenize($string);
		$bag_of_words = $this->textCleaner->clean($bag_of_words);

		for ($i = 0; $i < count($bag_of_words); $i++) {
			$bag_of_words[$i] = Porter::Stem($bag_of_words[$i]);
		}

		return $bag_of_words;
	}
}
