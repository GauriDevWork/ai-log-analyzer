<?php

namespace AILogAnalyzer\Classifier;

/**
 * Classifies errors by source and severity using deterministic rules.
 */
class ErrorClassifier
{
    /**
     * Classify a parsed error entry.
     *
     * @param array $error
     * @return array
     */
    public function classify(array $error): array
    {
        $error['source'] = $this->detectSource($error['file'] ?? '');
        $error['severity'] = $this->determineSeverity($error['type'] ?? '');

        return $error;
    }

    /**
     * Detect error source based on file path.
     *
     * @param string $filePath
     * @return string
     */
    private function detectSource(string $filePath): string
    {
        if (strpos($filePath, '/wp-content/plugins/') !== false) {
            return 'Plugin';
        }

        if (strpos($filePath, '/wp-content/themes/') !== false) {
            return 'Theme';
        }

        if (
            strpos($filePath, '/wp-includes/') !== false ||
            strpos($filePath, '/wp-admin/') !== false
        ) {
            return 'WordPress Core';
        }

        return 'Unknown';
    }

    /**
     * Determine severity based on error type.
     *
     * @param string $type
     * @return string
     */
    private function determineSeverity(string $type): string
    {
        return match ($type) {
            'Fatal Error' => 'High',
            'Warning'     => 'Medium',
            'Notice'      => 'Low',
            default       => 'Low',
        };
    }
}
