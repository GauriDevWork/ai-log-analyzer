<?php

namespace AILA\Core;

defined('ABSPATH') || exit;

class Analyzer
{
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
                    'id'       => md5($line),
                    'type'     => $this->detectType($line),
                    'message'  => $line,
                    'severity' => $this->detectSeverity($line),
                ];
            }
        }

        return [
            'total_lines' => count($lines),
            'error_count' => count($errors),
            'errors'      => $errors,
        ];
    }

    private function detectType(string $line): string
    {
        if (stripos($line, 'fatal') !== false) return 'Fatal Error';
        if (stripos($line, 'warning') !== false) return 'Warning';
        if (stripos($line, 'notice') !== false) return 'Notice';
        return 'Unknown';
    }

    private function detectSeverity(string $line): string
    {
        if (stripos($line, 'fatal') !== false) return 'high';
        if (stripos($line, 'warning') !== false) return 'medium';
        return 'low';
    }
}
