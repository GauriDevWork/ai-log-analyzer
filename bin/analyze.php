<?php

require_once __DIR__ . '/../src/Parser/LogParser.php';
require_once __DIR__ . '/../src/Classifier/ErrorClassifier.php';

use AILogAnalyzer\Parser\LogParser;
use AILogAnalyzer\Classifier\ErrorClassifier;

$logContent = file_get_contents(__DIR__ . '/../sample.log');

$parser = new LogParser();
$classifier = new ErrorClassifier();

$parsedErrors = $parser->parse($logContent);

$classifiedErrors = [];

foreach ($parsedErrors as $error) {
    $classifiedErrors[] = $classifier->classify($error);
}

print_r($classifiedErrors);
