<?php

namespace AILogAnalyzer\AI;

/**
 * Generates contextual, human-readable explanations for errors using AI.
 */
class AIExplainer
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Explain an error using AI (safe wrapper).
     */
    public function explain(array $error): array
    {
        $response = $this->callAI($this->buildPrompt($error));

        if ($response === null) {
            return $this->fallbackExplanation();
        }

        return $response;
    }

    /**
     * Build a constrained AI prompt.
     */
    private function buildPrompt(array $error): string
    {
        return <<<PROMPT
You are a senior WordPress engineer.

Explain the following error clearly.
Do NOT suggest code changes.
Do NOT mention file paths.

Error Type: {$error['type']}
Message: {$error['message']}
Source: {$error['source']}
Severity: {$error['severity']}

Respond strictly in this format:

Explanation:
Likely Cause:
Suggested Next Steps:
PROMPT;
    }

    /**
     * Call OpenAI API safely.
     */
    private function callAI(string $prompt): ?array
    {
        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 15,
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            return null;
        }

        $data = json_decode($result, true);

        // 🔐 SINGLE DEFENSIVE GATE
        if (
            !is_array($data) ||
            isset($data['error']) ||
            !isset($data['choices'][0]['message']['content'])
        ) {
            return null;
        }

        return [
            'ai_response' => trim($data['choices'][0]['message']['content'])
        ];
    }

    /**
     * Safe fallback when AI fails.
     */
    private function fallbackExplanation(): array
    {
        return [
            'ai_response' =>
                "Explanation:\nThe error indicates an issue during runtime.\n\n" .
                "Likely Cause:\nThe problem is related to the reported error type and source.\n\n" .
                "Suggested Next Steps:\nReview recent changes and validate input values."
        ];
    }
}
