<?php


return array(
	'emoticons' => '/(?:>|<|O|0)*(?::|;|=|B|X)(?:-+)?(?:\)+|\*+|\\+|\/+|>+|X+|D+|P+|p+|d+|\}+|\]+|\{\}|\(+|<+|\[+|O+|o+|S+|s+|@|\|\||\|+|Z+|\?|C|c)|\(\s{0,}[\^\-o@\?;][_\.][\^\-o@\?;].{0,1}\s{0,}\)|<\/?3|[>\-][_\.][<\-]/u',
	
	//'sentence' => '/\'(?=\w)|\s+|@.+?\b|[#\^\*_\+=\|\.\\/:><`~\(\)\[\]\{\};]|(?=[!,\?"&])|(?<=[!,\?"&])|\-(?!\w)(?<!\w)/u',
	'sentence' => '/&.+;|(?<!n)\'(?!t)|\s+|@.+?\b|[#\^\*_\+=\|",\.\\/:><`~\(\)\[\]\{\};]|(?=[!,\?"&])|(?<=[!,\?"&])|(?<!\w)-|-(?!\w)|(?<=(not))[[:punct:]]|[[:punct:]](?=(not))/u',
	
	'non_latin' => '/[^\00-\255]+/u',
	
	'url' => '/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/u',
	
	'split_string' => '/\./u',

	'negate' => '/(dont|don\'t|wasnt|shallnt|didn\'t|mustnt|hadn\'t|isnt|doesnt|won\'t|oughtnt|couldn\'t|cant|couldnt|shan\'t|aint|doesn\'t|hasnt|not|needn\'t|can\'t|shouldn\'t|amnt|haven\'t|arent|sha\'n\'t|shalln\'t|never|however|but|oughtn\'t|no|ain\'t|werent|hadnt|shant|aren\'t|neednt|twont|wont|nevertheless|hasn\'t|shouldnt|amn\'t|wasn\'t|weren\'t|\'twon\'t|havent|didnt|mustn\'t|isn\'t)/u'
	
);