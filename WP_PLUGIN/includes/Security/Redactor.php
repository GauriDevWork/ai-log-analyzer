<?php

namespace AILogAnalyzer\Security;

/**
 * Redacts sensitive information before data is processed or sent to AI services.
 */
class Redactor
{
    /**
     * Redact sensitive fields in a parsed error entry.
     *
     * @param array $error
     * @return array
     */
    public function redact(array $error): array
    {
        foreach ($error as $key => $value) {
            if (is_string($value)) {
                $error[$key] = $this->redactString($value);
            }
        }

        return $error;
    }

    /**
     * Apply redaction rules to a string.
     *
     * @param string $value
     * @return string
     */
    private function redactString(string $value): string
    {
        // Redact email addresses
        $value = preg_replace(
            '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            '[REDACTED_EMAIL]',
            $value
        );

        // Redact API keys / tokens (generic high-entropy strings)
        $value = preg_replace(
            '/(key|token|secret)[=:\s]+[a-zA-Z0-9-_]{8,}/i',
            '$1=[REDACTED]',
            $value
        );

        // Redact Windows user paths
        $value = preg_replace(
            '/C:\\\\Users\\\\[^\\\\]+/i',
            'C:\\Users\\[REDACTED]',
            $value
        );

        // Redact Linux/macOS user paths
        $value = preg_replace(
            '/\/home\/[^\/]+/i',
            '/home/[REDACTED]',
            $value
        );

        // Remove query strings from URLs
        $value = preg_replace(
            '/(https?:\/\/[^\s\?]+)\?[^\s]+/i',
            '$1?[REDACTED]',
            $value
        );

        return $value;
    }
}
