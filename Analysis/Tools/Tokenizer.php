<?php

namespace Sentiment\Analysis\Tools;

class Tokenizer {
	
	private $patterns;
	
	public function __construct () {
		$this->patterns = require_once('DataStore/RegexDatabase.php');
	}
	
	public function filterNonsense ($string) {

		$string = preg_replace($this->patterns['url'], '', $string);
		$string = preg_replace($this->patterns['non_latin'], '', $string);
		$string = preg_replace($this->patterns['emoticons'], '', $string);
		return $string;

	}
	
	public function tokenize ($string) {
		$string = strtolower($string);
		$string = $this->filterNonsense($string);
		return preg_split($this->patterns['sentence'], $string, null, PREG_SPLIT_NO_EMPTY);
	}

	public function getSplits ($string) {
		return preg_split($this->patterns['negate'], $string, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}
	
	public function getEmoticons ($string) {
		
		preg_match_all($this->patterns['emoticons'], $string, $matches);
		return $matches[0];
		
	}

	public function getSentences ($string) {
		return preg_split($this->patterns['split_string'], $string, null, PREG_SPLIT_NO_EMPTY);
	}
	
}
