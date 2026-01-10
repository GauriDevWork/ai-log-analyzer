<?php

namespace AILogAnalyzer\Parser;

/**
 * Parses raw PHP and WordPress error logs into structured data.
 */
class LogParser
{
    /**
     * Parse a raw log file into structured error entries.
     *
     * @param string $logContent
     * @return array
     */
    public function parse(string $logContent): array
    {
        $lines = explode(PHP_EOL, $logContent);
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parsed = $this->parseLine($line);

            if ($parsed !== null) {
                $errors[] = $parsed;
            }
        }

        return $errors;
    }

    /**
     * Parse a single log line.
     *
     * @param string $line
     * @return array|null
     */
    private function parseLine(string $line): ?array
    {
        $type = null;

        if (stripos($line, 'PHP Fatal error') !== false) {
            $type = 'Fatal Error';
        } elseif (stripos($line, 'PHP Warning') !== false) {
            $type = 'Warning';
        } elseif (stripos($line, 'PHP Notice') !== false) {
            $type = 'Notice';
        }

        if ($type === null) {
            return null;
        }

        $message = null;
        $file = null;
        $lineNumber = null;

        // Extract message
        if (preg_match('/PHP (Fatal error|Warning|Notice):\s+(.*?)\s+in/i', $line, $matches)) {
            $message = $matches[2];
        }

        // Extract file and line number (Format: file.php:123)
        if (preg_match('/in\s+(.*?):(\d+)/i', $line, $matches)) {
            $file = $matches[1];
            $lineNumber = (int) $matches[2];
        }

        // Extract file and line number (Format: file.php on line 45)
        elseif (preg_match('/in\s+(.*?)\s+on\s+line\s+(\d+)/i', $line, $matches)) {
            $file = $matches[1];
            $lineNumber = (int) $matches[2];
        }

        return [
            'type'      => $type,
            'message'   => $message,
            'file'      => $file,
            'line'      => $lineNumber,
            'timestamp' => null,
            'raw'       => $line
        ];
    }

}
