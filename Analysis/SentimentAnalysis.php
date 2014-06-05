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
	
	public function classify ($string) {
		
		$scores = $this->classifier->classify($string);
		// double negation solution
		
		$negationCount = $this->classifier->textCleaner->getNegationCount();
		if($negationCount % 2 == 0 && $negationCount != 0) {
			$temp = $scores['pos'];
			$scores['pos'] = $scores['neg'];
			$scores['neg'] = $temp;
		}
		return $scores;
		
	}

	public function tokenize ($file) {
		$str = file_get_contents($file);
		return $this->classifier->tokenize($str);
	}

	public function tokenize_str ($str) {
		return $this->classifier->tokenize($str);
	}
}
