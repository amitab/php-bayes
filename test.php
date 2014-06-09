<?php

// This path should point to Composer's autoloader
require 'vendor/autoload.php';

use Sentiment\Analysis\SentimentAnalysis;

$analyser = new SentimentAnalysis(new PDO("mysql:dbname=test1;host=127.0.0.1;port=3306", "root", ""));
echo print_r($analyser->classify('Life is not good.'));
echo print_r($analyser->classify('Life is good.'));
echo print_r($analyser->classify('I don\'t give up.'));
echo print_r($analyser->classify('I give up.'));
echo print_r($analyser->classify('Life is not about how hard you can hit, but how much you can get hit and still keep moving forward.'));
echo print_r($analyser->classify('More time jumps confuse ppl'));
echo print_r($analyser->classify('I didn\'t mean to scare you.'));
echo print_r($analyser->classify('The best of friends won\'t betray you'));
echo print_r($analyser->classify('Your enemies won\'t help you and won\'t trust you'));
echo print_r($analyser->classify(" First of all, I just went to watch this because I've seen the other two, which I kind of enjoyed, but I didn't have much expectation when I went to see this. And I was still disappointed. I felt like the plot was very weak and I was confused about what they were doing most of the time. There was plenty of singing and dancing, obviously, which was good for the most part. But it wasn't as good as the other films. Probably my biggest complaint would be about Fat Amy and her dad. Now, John Lithgow is a great actor in my opinion, but as an Australian, I was a big taken aback when he was cast as an Australian. His Aussie accent was hit and miss and after the movie all I was thinking was the possible Australian actors that could've been cast in his place. John Jarratt comes to mind (joke). Also, why do people keep casting Ruby Rose. No offence, but she just can't act. And she did an American accent in this so that was a bit weird as well. Overall, I wasn't expecting much from this movie and I got what I expected. I'm probably glad they're not making these movies anymore. I give it a low score for the lack of plot and it just felt like the same movie but worse. "));
