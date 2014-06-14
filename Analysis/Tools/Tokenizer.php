<?php

namespace Sentiment\Analysis\Tools;

class Tokenizer {
	
	private $patterns;
	
	public function __construct () {
		$this->patterns = [
			'emoticons' => '/(?:>|<|O|0)*(?::|;|=|B|X)(?:-+)?(?:\)+|\*+|\\+|\/+|>+|X+|D+|P+|p+|d+|\}+|\]+|\{\}|\(+|<+|\[+|O+|o+|S+|s+|@|\|\||\|+|Z+|\?|C|c)|\(\s{0,}[\^\-o@\?;][_\.][\^\-o@\?;].{0,1}\s{0,}\)|<\/?3|[>\-][_\.][<\-]/u',
			
			//'sentence' => '/\'(?=\w)|\s+|@.+?\b|[#\^\*_\+=\|\.\\/:><`~\(\)\[\]\{\};]|(?=[!,\?"&])|(?<=[!,\?"&])|\-(?!\w)(?<!\w)/u',
			'sentence' => '/&.+;|(?<!n)\'(?!t)|\s+|@.+?\b|[#\^\*_\+=\|",\.\\/:><`~\(\)\[\]\{\};]|(?=[!,\?"&])|(?<=[!,\?"&])|(?<!\w)-|-(?!\w)|(?<=(not))[[:punct:]]|[[:punct:]](?=(not))/u',
			
			'non_latin' => '/[^\00-\255]+/u',
			
			'url' => '/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/u',
			
			'split_string' => '/[\.!;\:\?]/u',
		
			'negate' => '/\b(dont|don\'t|wasnt|shallnt|didn\'t|mustnt|hadn\'t|isnt|doesnt|won\'t|oughtnt|couldn\'t|cant|couldnt|shan\'t|aint|doesn\'t|hasnt|no|needn\'t|can\'t|shouldn\'t|amnt|haven\'t|arent|sha\'n\'t|shalln\'t|never|oughtn\'t|not|ain\'t|werent|hadnt|shant|aren\'t|neednt|twont|wont|hasn\'t|shouldnt|amn\'t|wasn\'t|weren\'t|\'twon\'t|havent|didnt|mustn\'t|isn\'t)\b/u',

			'sentence_shift' => '/\b(though|although|even though|despite|yet|however|but|nonetheless|nevertheless)\b/u'
		];
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

	public function getShifts ($string) {
		return preg_split($this->patterns['sentence_shift'], $string, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}
	
	public function getEmoticons ($string) {
		
		preg_match_all($this->patterns['emoticons'], $string, $matches);
		return $matches[0];
		
	}

	public function getSentences ($string) {
		return preg_split($this->patterns['split_string'], preg_replace($this->patterns['url'], '', $string), null, PREG_SPLIT_NO_EMPTY);
	}
	
}
