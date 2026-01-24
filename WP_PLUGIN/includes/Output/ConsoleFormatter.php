<?php

namespace AILogAnalyzer\Output;

/**
 * Formats analysis results for human-readable CLI output.
 */
class ConsoleFormatter
{
    /**
     * Render errors to CLI.
     *
     * @param array $errors
     */
    public function render(array $errors): void
    {
        if (empty($errors)) {
            echo "No errors found.\n";
            return;
        }

        foreach ($errors as $index => $error) {
            echo str_repeat('=', 60) . "\n";
            echo "Error #" . ($index + 1) . "\n";
            echo str_repeat('-', 60) . "\n";

            echo "Type      : {$error['type']}\n";
            echo "Source    : {$error['source']}\n";
            echo "Severity  : {$error['severity']}\n";

            if (!empty($error['message'])) {
                echo "Message   : {$error['message']}\n";
            }

            if (!empty($error['explanation']['ai_response'])) {
                echo "\n{$error['explanation']['ai_response']}\n";
            }

            echo "\n";
        }
    }
}
