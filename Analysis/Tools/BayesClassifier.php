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
				'sentence_count' => ($stmt->rowCount() == 0 ? 0 : $row[$label . '_total'])
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
		$inverse_data['sentence_count'] = 0;
		$inverse_data['score'] = 0;

		foreach($this->labels as $label) {
			if ($label['label'] != $class) {
				$inverse_data['sentence_count'] += $label['sentence_count'];
				$inverse_data['score'] += $query_data[$label['label']];
			}
		}

		return $inverse_data;
	}

	public function findSentiment ($tokens) {
		foreach($this->labels as $label) {
			$log_sum = 0;

			foreach($tokens as $token) {
				$token = Porter::Stem($token);
				$stmt = $this->client->prepare("SELECT word, pos, neg FROM words WHERE word = :word;");
	
			    	if (!$stmt->execute(['word' => $token])) {
					continue;
				}
				if ($stmt->rowCount() == 0) {
					$stmt->closeCursor();
					$stmt = null;
					continue;
				}
				$data = $stmt->fetch(PDO::FETCH_ASSOC);
				$stmt->closeCursor();
				$stmt = null;
				$occurance_count = $this->findOccuranceCount($data);
				if($occurance_count == 0) {
					continue;
				} else {

					$word_probability = $data[$label['label']]/$label['sentence_count'];
					$inverse_data = $this->findInverseData($label['label'], $data);
					$word_inverse_probability = $inverse_data['score']/$inverse_data['sentence_count'];

					$wordicity = $word_probability/($word_probability + $word_inverse_probability);
					$wordicity = ( (1 * 0.5) + ($occurance_count * $wordicity) ) / ( 1 + $occurance_count );

					if($wordicity == 0) $wordicity = 0.001;
					else if ($wordicity == 1) $wordicity = 0.999;
					//echo $token . ' : '. $wordicity . '</br>';
					$log_sum += (log( 1 - $wordicity ) - log( $wordicity ));

				}

			}

			$this->score[$label['label']] = 1 / ( 1 + exp ( $log_sum ) );
		}
	}

	public function classify ($string) {

		$emoticons = $this->tokenizer->getEmoticons($string);
		$bag_of_words = $this->tokenizer->tokenize($string);

		$bag_of_words = $this->textCleaner->clean($bag_of_words);
		$this->findSentiment(array_merge($bag_of_words, $emoticons));

		return $this->score;		
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
