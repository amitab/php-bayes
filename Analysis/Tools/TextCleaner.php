<?php

namespace Sentiment\Analysis\Tools;

class TextCleaner {
	
	private $wordDatabase;
	private $negationCount;
	
	public function __construct () {
		$this->wordDatabase = require_once('DataStore/WordDatabase.php');
		$this->negationCount = 0;
	}
	
	private function filter ($word) {
		return (!in_array($word, $this->wordDatabase['stop_words']));
	}
	
	public function filterArray ($array_of_words) {
		return array_values(array_unique(array_filter($array_of_words, array($this, 'filter'))));
	}
	
	public function negation ($array_of_words) {
		
		$this->negationCount = 0;
		
		for($i = 0; $i < count($array_of_words); $i++) {
			if(in_array($array_of_words[$i], $this->wordDatabase['negation_words'])) {
				if(isset($array_of_words[$i-1])) {
					$array_of_words[$i-1] = '!' . $array_of_words[$i-1];
				}
				if(isset($array_of_words[$i+1])) {
					$array_of_words[$i+1] = '!' . $array_of_words[$i+1];
				}
			}
		}

		return $array_of_words;
	}
	
	public function getNegationCount() {
		return $this->negationCount;
	}

	public function clean ($array_of_words) {
		$array_of_words = array_filter($array_of_words, array($this, 'filter'));
		$array_of_words = array_values($array_of_words);
		$this->negationCount = 0;
		
		for($i = 0; $i < count($array_of_words); $i++) {
			if(in_array($array_of_words[$i], $this->wordDatabase['negation_words'])) {
				$this->negationCount++;
				if(isset($array_of_words[$i-1])) {
					if(strpos($array_of_words[$i-1],'!') === false)
						$array_of_words[$i-1] = '!' . $array_of_words[$i-1];
				}
				
				if(isset($array_of_words[$i+1])) {
					if(strpos($array_of_words[$i+1],'!') === false)
						$array_of_words[$i+1] = '!' . $array_of_words[$i+1];
				}
			} 
			
		}
		
		//return array_filter($array_of_words, array($this, 'filter'));
		return $array_of_words;
	}

}
