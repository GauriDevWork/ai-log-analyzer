<?php

namespace AILA\Core;

use AILA\AI\AIExplainer;

defined('ABSPATH') || exit;

/**
 * Orchestrates the full log analysis pipeline.
 */
class Analyzer
{
    /**
     * Analyze raw log content
     */
    public function analyzeFromString(string $logContent): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $logContent);

        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (
                stripos($line, 'fatal error') !== false ||
                stripos($line, 'warning') !== false ||
                stripos($line, 'notice') !== false
            ) {
                $errors[] = [
                    'type'     => $this->detectType($line),
                    'message'  => $line,
                    'source'   => 'log',
                    'severity' => $this->detectSeverity($line),
                ];
            }
        }

        $result = [
            'total_lines' => count($lines),
            'error_count' => count($errors),
            'errors'      => $errors,
            'ai_summary'  => null,
        ];

        // 🔹 AI explanation (safe, optional)
        if (!empty($errors)) {
            try {
                $apiKey = get_option('aila_ai_api_key');

                if ($apiKey) {
                    $explainer = new AIExplainer($apiKey);
                    // $aiContext = [
                    //     'total_errors' => count($errors),
                    //     'high_severity' => array_values(array_filter($errors, fn($e) => $e['severity'] === 'high')),
                    //     'medium_severity' => array_values(array_filter($errors, fn($e) => $e['severity'] === 'medium')),
                    //     'low_severity' => array_values(array_filter($errors, fn($e) => $e['severity'] === 'low')),
                    // ];

                    $summaryForAI = [
                        'total_errors' => count($errors),
                        'by_severity' => [
                            'high'   => count(array_filter($errors, fn($e) => $e['severity'] === 'high')),
                            'medium' => count(array_filter($errors, fn($e) => $e['severity'] === 'medium')),
                            'low'    => count(array_filter($errors, fn($e) => $e['severity'] === 'low')),
                        ],
                        'top_patterns' => [
                            'early_translation_loading' => count(array_filter(
                                $errors,
                                fn($e) => str_contains($e['message'], '_load_textdomain_just_in_time')
                            )),
                            'missing_class_or_method' => count(array_filter(
                                $errors,
                                fn($e) => str_contains($e['message'], 'Class "') || str_contains($e['message'], 'undefined method')
                            )),
                        ],
                        'sample_critical_errors' => array_slice(
                            array_values(array_filter($errors, fn($e) => $e['severity'] === 'high')),
                            0,
                            2
                        ),
                    ];


                    $result['ai_summary'] = $explainer->explainSummary($summaryForAI);
                }
            } catch (\Throwable $e) {
                // AI failure must never break analysis
                $result['ai_summary'] = null;
            }
        }

        return $result;
    }

    private function detectType(string $line): string
    {
        if (stripos($line, 'fatal') !== false) {
            return 'Fatal Error';
        }
        if (stripos($line, 'warning') !== false) {
            return 'Warning';
        }
        if (stripos($line, 'notice') !== false) {
            return 'Notice';
        }
        return 'Unknown';
    }

    private function detectSeverity(string $line): string
    {
        if (stripos($line, 'fatal') !== false) {
            return 'high';
        }
        if (stripos($line, 'warning') !== false) {
            return 'medium';
        }
        return 'low';
    }
}
