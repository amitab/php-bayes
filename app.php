<?php

// This path should point to Composer's autoloader
require 'vendor/autoload.php';

use Sentiment\Analysis\SentimentAnalysis;

$x = new SentimentAnalysis(new PDO("mysql:dbname=test;host=127.0.0.1;port=3306", "root", ""));
$pos_dir = "/home/amitabh/Downloads/aclImdb/train/pos/*";
$neg_dir = "/home/amitabh/Downloads/aclImdb/train/neg/*";

$data = [];

function setup_word ($word) {
	global $data;
	if (!array_key_exists($word, $data)) {
			$data[$word] = [
				'word' => $word,
				'pos' => 0,
				'neg' => 0,
				'irr' => 0,
				'neu' => 0,
				'sar' => 0
			];
	}
}

$files = glob($pos_dir);
$tot = count($files);
$i = 0;
foreach($files as $file) {
	$words = $x->tokenize($file);
	foreach ($words as $word) {
		setup_word($word);
		$data[$word]['pos'] += 1;
	}
	echo "Processed $i/$tot files\n";
	$i += 1;
}
$files = glob($neg_dir);
$tot = count($files);
$i = 0;
foreach(glob($neg_dir) as $file) {
	$words = $x->tokenize($file);
	foreach ($words as $word) {
		setup_word($word);
		$data[$word]['neg'] += 1;
	}
	echo "Processed $i/$tot files\n";
	$i += 1;
}

echo "Completed pos and neg dirs\n";

$file = fopen('/home/amitabh/Downloads/sanders-twitter-0.2/full-corpus.csv', 'r');
$tot = 5396;
$i = 0;
$line = fgetcsv($file);
echo print_r($line);
while (($line = fgetcsv($file)) !== FALSE) {
	switch ($line[1]) {
		case 'positive':
			$label = 'pos';
			break;
		case 'negative':
			$label = 'neg';
			break;
		case 'irrelevant':
			$label = 'irr';
			break;
		case 'neutral':
			$label = 'neu';
			break;
		default:
			throw new Exception("Found invalid category $line[1] in csv\n");
	}
	$words = $x->tokenize_str($line[4]);
	foreach ($words as $word) {
		setup_word($word);
		$data[$word][$label] += 1;
	}
	echo "Processed $i lines of $tot lines\n";
	$i += 1;
}
fclose($file);

$fp = fopen('results.json', 'w');
fwrite($fp, json_encode(array_values($data)));
fclose($fp);

$f = fopen('output.csv', 'w');
foreach ($data as $line) {
    fputcsv($f, $line);
}

/*
create table words(word VARCHAR(100), pos int, neg int);
load data local infile '/home/amitabh/github/temp/test/php-bayes/output.csv'
into table words
fields terminated by ','
lines terminated by '\n'
(word, pos, neg);
create index word_index on words(word(100));
*/