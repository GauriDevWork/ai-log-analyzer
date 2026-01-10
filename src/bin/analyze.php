<?php
// get environment
$env = getenv('APP_ENV') ?: 'dev';

if ($env === 'prod') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Load .env file manually
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

require_once __DIR__ . '/../src/Parser/LogParser.php';
require_once __DIR__ . '/../src/Classifier/ErrorClassifier.php';
require_once __DIR__ . '/../src/Security/Redactor.php';
require_once __DIR__ . '/../src/AI/AIExplainer.php';

use AILogAnalyzer\Parser\LogParser;
use AILogAnalyzer\Classifier\ErrorClassifier;
use AILogAnalyzer\Security\Redactor;
use AILogAnalyzer\AI\AIExplainer;

$logContent = file_get_contents(__DIR__ . '/../sample.log');
$apiKey = getenv('OPENAI_API_KEY');

$parser = new LogParser();
$classifier = new ErrorClassifier();
$redactor = new Redactor();
$ai = new AIExplainer($apiKey);

$parsed = $parser->parse($logContent);
$output = [];

foreach ($parsed as $error) {
    $classified = $classifier->classify($error);
    $safe = $redactor->redact($classified);
    $safe['explanation'] = $ai->explain([
        'type' => $safe['type'],
        'message' => $safe['message'],
        'source' => $safe['source'],
        'severity' => $safe['severity'],
    ]);

    $output[] = $safe;
}

print_r($output);