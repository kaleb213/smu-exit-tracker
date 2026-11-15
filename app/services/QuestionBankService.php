<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Question;
use App\Models\Topic;

/**
 * Question Bank Management subsystem.
 * Retrieves randomized question sets for the Practice Engine and
 * wraps admin CRUD with validation rules.
 */
final class QuestionBankService
{
    /**
     * Randomized validated questions for a topic.
     * Boundary condition: when the topic has fewer questions than the
     * requested count, return everything available (and the caller
     * shows a notice when the topic is empty).
     */
    public static function practiceSet(int $topicId, int $count): array
    {
        $available = Question::countValidatedByTopic($topicId);
        if ($available === 0) {
            return []; // documented alternative course: notify the student
        }
        return Question::randomByTopic($topicId, min($count, $available));
    }

    /** Randomized set across all subjects for the mock exam. */
    public static function mockSet(int $count): array
    {
        return Question::randomAcrossSubjects($count);
    }

    /** Validate a question form submission. */
    public static function validateQuestion(array $input): array
    {
        $errors = [];
        $topicId = (int) ($input['topic_id'] ?? 0);
        if ($topicId <= 0) {
            $errors[] = 'Please choose a subject and a topic.';
        }
        if (mb_strlen(trim((string) ($input['question_text'] ?? ''))) < 8) {
            $errors[] = 'Question text is too short.';
        }
        foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $opt) {
            if (mb_strlen(trim((string) ($input[$opt] ?? ''))) < 1) {
                $errors[] = 'All four options (A-D) are required.';
                break;
            }
        }
        if (!in_array($input['correct_option'] ?? '', ['A', 'B', 'C', 'D'], true)) {
            $errors[] = 'Please select the correct option.';
        }
        if (!in_array($input['difficulty'] ?? '', ['easy', 'medium', 'hard'], true)) {
            $input['difficulty'] = 'medium';
        }
        // documented alternative course: missing fields prevent saving
        return ['errors' => $errors, 'data' => $input];
    }

    public static function topicSelectable(int $topicId): bool
    {
        return Topic::find($topicId) !== null && Question::countValidatedByTopic($topicId) > 0;
    }
}
