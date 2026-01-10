<?php

require_once __DIR__ . '/../src/Parser/LogParser.php';
require_once __DIR__ . '/../src/Classifier/ErrorClassifier.php';
require_once __DIR__ . '/../src/Security/Redactor.php';

use AILogAnalyzer\Parser\LogParser;
use AILogAnalyzer\Classifier\ErrorClassifier;
use AILogAnalyzer\Security\Redactor;

$logContent = file_get_contents(__DIR__ . '/../sample.log');

$parser = new LogParser();
$classifier = new ErrorClassifier();
$redactor = new Redactor();

$parsedErrors = $parser->parse($logContent);
$finalErrors = [];

$classifiedErrors = [];

foreach ($parsedErrors as $error) {
    $classified = $classifier->classify($error);
    $finalErrors[] = $redactor->redact($classified);
}

print_r($finalErrors);
