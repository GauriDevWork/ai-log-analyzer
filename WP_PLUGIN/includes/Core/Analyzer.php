<?php

namespace AILA\Core;

defined('ABSPATH') || exit;

class Analyzer {

    /**
     * Analyze log content passed as string
     */
    public function analyzeFromString(string $logContent): array {

        $lines = preg_split('/\r\n|\r|\n/', $logContent);

        $results = [
            'total_lines' => count($lines),
            'errors'      => [],
            'warnings'    => [],
            'notices'     => [],
            'other'       => [],
        ];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (stripos($line, 'fatal error') !== false || stripos($line, 'php fatal') !== false) {
                $results['errors'][] = $line;
            } elseif (stripos($line, 'warning') !== false) {
                $results['warnings'][] = $line;
            } elseif (stripos($line, 'notice') !== false) {
                $results['notices'][] = $line;
            } else {
                $results['other'][] = $line;
            }
        }

        $results['summary'] = [
            'error_count'   => count($results['errors']),
            'warning_count' => count($results['warnings']),
            'notice_count'  => count($results['notices']),
            'other_count'   => count($results['other']),
        ];

        return $results;
    }
}
