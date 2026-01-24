<?php

namespace AILogAnalyzer\Output;

/**
 * Formats analysis results as JSON output.
 */
class JsonFormatter
{
    /**
     * Render errors as JSON.
     *
     * @param array $errors
     */
    public function render(array $errors): void
    {
        echo json_encode($errors, JSON_PRETTY_PRINT) . PHP_EOL;
    }
}
