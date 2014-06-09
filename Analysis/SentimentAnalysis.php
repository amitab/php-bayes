<?php

namespace Sentiment\Analysis;
use Porter;

class SentimentAnalysis {
	
	private $classifier;
	private $tokenizer;
	private $cleaner;
	
	public function __construct($client) {
		$this->tokenizer = new \Sentiment\Analysis\Tools\Tokenizer();
		$this->cleaner = new \Sentiment\Analysis\Tools\TextCleaner();
		$this->classifier = new \Sentiment\Analysis\Tools\BayesClassifier(
			$this->tokenizer, $this->cleaner, ['pos', 'neg'], $client);
	}

    public function classify ($para) {
        echo "$para\n";
        $scores = ['pos' => 0, 'neg' => 0];
        foreach ($this->tokenizer->getSentences($para) as $sentence) {
            $res = $this->classify_sentence($sentence);
            $scores['pos'] += $res['pos'];
            $scores['neg'] += $res['neg'];
        }
		$scores['pos'] = 1 / ( 1 + exp ( $scores['pos'] ) );
		$scores['neg'] = 1 / ( 1 + exp ( $scores['neg'] ) );
		return $scores;
    }

	public function classify_sentence ($string) {
		$scores = [ 'pos' => 0, 'neg' => 0 ];
		$string = strtolower($string);
		//echo "$string\n";
		$shifts = $this->tokenizer->getShifts($string);
		$doShift = false;
		for($k = 0; $k < count($shifts); $k++) {
			$splits = $this->tokenizer->getSplits($shifts[$k]);
			$split_res = $this->classifier->classify_sentence($splits[0]);
            //echo "----------------------------------------------------------\n";
            //echo "Main Split Res\n";
            //echo $splits[0] . " : pos : ".$split_res['pos']." : neg : " .$split_res['neg']."\n";
            //echo "----------------------------------------------------------\n";
			$i = 1;
			while($i < count($splits)) {
				$negate_res = $this->classifier->classify_token($splits[$i]);
				$res = $this->classifier->classify_sentence($splits[$i + 1]);
                //echo "----------------------------------------------------------\n";
                //echo "Negate Res\n";
                //echo $splits[$i] . " : pos : ".$negate_res['pos']." : neg : " .$negate_res['neg']."\n";
                //echo $splits[$i+1] . " : pos : ".$res['neg']." : neg : " .$res['pos']."\n";
                //echo "----------------------------------------------------------\n";

				$split_res['neg'] += $res['pos'];
				$split_res['pos'] += $res['neg'];

				if ($res['pos'] > $res['neg']) {
					$split_res['neg'] += $negate_res['pos'];
					$split_res['pos'] += $negate_res['neg'];
				} else {
					$split_res['neg'] += $negate_res['neg'];
					$split_res['pos'] += $negate_res['pos'];
				}
				$i += 2;
			}
            //echo "----------------------------------------------------------\n";
            //echo "Shift Res\n";
			if ($doShift == false || $k % 2 != 0 || $k == 0) {
				$scores['pos'] += $split_res['pos'];
				$scores['neg'] += $split_res['neg'];
                //echo $shifts[$k] . " (NO SWAP) : pos : ".$split_res['pos']." : neg : " .$split_res['neg']."\n";
			} else {
				$scores['neg'] += $split_res['pos'];
				$scores['pos'] += $split_res['neg'];
                //echo $shifts[$k] . " (SWAP) : pos : ".$split_res['neg']." : neg : " .$split_res['pos']."\n";
			}
            //echo "----------------------------------------------------------\n";
			$doShift = !$doShift;
		}
		return $scores;		
	}

	public function tokenize ($file) {
		$string = file_get_contents($file);
		return $this->tokenize_str($string);
	}

	public function tokenize_str ($string) {
		$emoticons = $this->tokenizer->getEmoticons($string);
		$bag_of_words = $this->tokenizer->tokenize($string);
		$bag_of_words = $this->cleaner->clean($bag_of_words);

		for ($i = 0; $i < count($bag_of_words); $i++) {
			$bag_of_words[$i] = Porter::Stem($bag_of_words[$i]);
		}

		return array_merge($bag_of_words, $emoticons);
	}
}
