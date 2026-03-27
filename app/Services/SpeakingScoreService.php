<?php

namespace App\Services;

/**
 * AI-ready hook: swap transcript comparison with cloud STT / pronunciation APIs.
 */
class SpeakingScoreService
{
    /**
     * @return array{fluency: int, accuracy: int, pronunciation: int, note: string}
     */
    public function score(string $reference, ?string $transcript): array
    {
        $reference = mb_strtolower(trim($reference));
        $transcript = $transcript ? mb_strtolower(trim($transcript)) : '';

        if ($reference === '' || $transcript === '') {
            return [
                'fluency' => 0,
                'accuracy' => 0,
                'pronunciation' => 0,
                'note' => 'No transcript yet — integrate speech-to-text for real scoring.',
            ];
        }

        similar_text($reference, $transcript, $pct);
        $accuracy = (int) round($pct);

        return [
            'fluency' => min(100, $accuracy + 5),
            'accuracy' => $accuracy,
            'pronunciation' => (int) round($accuracy * 0.9),
            'note' => 'Heuristic demo score. Plug in your STT + ASR provider.',
        ];
    }
}
