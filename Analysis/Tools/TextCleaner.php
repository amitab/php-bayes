<?php

namespace Sentiment\Analysis\Tools;

class TextCleaner {
	
	private $wordDatabase;
	private $negationCount;
	
	public function __construct () {
		$this->wordDatabase = [
			'stop_words' => ['i', 'me', 'my', 'myself', 'we', 'our', 'ours',
				'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves',
				'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself',
				'it', 'its', 'itself', 'they', 'them', 'their', 'theirs',
				'themselves', 'what', 'which', 'who', 'whom', 'this', 'that',
				'these', 'those', 'am', 'is', 'are', 'a', 'an', 'the', 'and',
				'because', 'as', 'while', 'of', 'at', 'by', 'for', 'about', 'into',
				'through', 'during', 'before', 'after', 'above', 'below', 'to',
				'from','in', 'out', 'over', 'under', 'then', 'here', 'there', 'all',
				'any', 'both', 'each', 'other', 'so', 'than', 's', 't', 'now'],
			
			'negation_words' => ['not', 'won\'t', 'can\'t', 'isn\'t', 'wasn\'t',
				'aren\'t', 'weren\'t', 'couldn\'t', 'shouldn\'t', 'wont', 'cant',
				'isnt', 'wasnt', 'arent', 'werent', 'couldnt', 'shouldnt', 'didn\'t',
				'didnt', 'but', 'however', 'nevertheless' , 'never', 'no', 'dont',
				'don\'t', '\'twon\'t', 'twont', 'ain\'t', 'aint', 'amn\'t', 'doesn\'t',
				'doesnt', 'hadn\'t', 'hadnt', 'haven\'t', 'havent', 'hasn\'t', 'hasnt',
				'mustn\'t', 'mustnt', 'needn\'t', 'neednt', 'oughtn\'t', 'oughtnt',
				'sha\'n\'t', 'shant', 'shalln\'t', 'shallnt', 'shan\'t', 'shant'],
				
		];
		$this->negationCount = 0;
	}
	
	private function filter ($word) {
		return (!in_array($word, $this->wordDatabase['stop_words']));
	}
	
	public function filterArray ($array_of_words) {
		return array_values(array_unique(array_filter($array_of_words, array($this, 'filter'))));
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
			} 
			
		}

		return $array_of_words;
	}

}
