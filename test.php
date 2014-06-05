<?php

// This path should point to Composer's autoloader
require 'vendor/autoload.php';
use Sentiment\Analysis\SentimentAnalysis;

$analyser = new SentimentAnalysis(new PDO("mysql:dbname=test1;host=127.0.0.1;port=3306", "root", ""));
echo print_r($analyser->classify('Life is not about how hard you can hit, but how much you can get hit and still keep moving forward.'));
echo print_r($analyser->classify('More time jumps confuse ppl'));
echo print_r($analyser->classify('I didn\'t mean to scare you.'));
