<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;

class QuizScoringService
{
    /**
     * @param  array<int, mixed>  $answersByQuestionId
     * @return array{score: int, max: int, details: array<int, array<string, mixed>>}
     */
    public function score(Quiz $quiz, array $answersByQuestionId): array
    {
        $quiz->load('questions');
        $score = 0;
        $max = 0;
        $details = [];

        foreach ($quiz->questions as $question) {
            $max += $question->points;
            $given = $answersByQuestionId[$question->id] ?? null;
            $correct = $this->gradeQuestion($question, $given);
            if ($correct) {
                $score += $question->points;
            }
            $details[$question->id] = ['correct' => $correct];
        }

        return ['score' => $score, 'max' => $max, 'details' => $details];
    }

    protected function gradeQuestion(Question $question, mixed $given): bool
    {
        $p = $question->payload ?? [];

        return match ($question->type) {
            'multiple_choice' => isset($p['correct']) && (string) $given === (string) $p['correct'],
            'fill_blank' => $this->matchText($given, $p['answers'] ?? []),
            'translation' => $this->matchText($given, $p['answers'] ?? []),
            'reorder' => $this->sameSequence($given, $p['correct_order'] ?? null),
            'match_pairs' => $this->sameMap($given, $p['pairs'] ?? null),
            'listening', 'image_based' => isset($p['correct']) && (string) $given === (string) $p['correct'],
            default => false,
        };
    }

    /**
     * @param  list<string>|string  $accepted
     */
    protected function matchText(mixed $given, array|string $accepted): bool
    {
        if ($given === null || $given === '') {
            return false;
        }

        $norm = fn (string $s) => mb_strtolower(trim($s));

        $g = $norm((string) $given);
        $list = is_array($accepted) ? $accepted : [$accepted];

        foreach ($list as $a) {
            if ($g === $norm((string) $a)) {
                return true;
            }
        }

        return false;
    }

    protected function sameSequence(mixed $given, mixed $expected): bool
    {
        if (! is_array($given) || ! is_array($expected)) {
            return false;
        }

        return array_values($given) === array_values($expected);
    }

    protected function sameMap(mixed $given, mixed $expected): bool
    {
        if (! is_array($given) || ! is_array($expected)) {
            return false;
        }

        ksort($given);
        ksort($expected);

        return $given == $expected;
    }
}
