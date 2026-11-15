<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Result model — read-side of exam_sessions ("results table" of the
 * design document): scores, answer review and feedback data.
 */
final class Result extends Model
{
    /** Detailed answer review of one attempt (results & feedback page). */
    public static function answerReview(int $sessionId, int $limit = 50): array
    {
        return Database::query(
            'SELECT q.id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d,
                    q.correct_option, q.explanation, t.name AS topic,
                    sa.selected_option, sa.is_correct
             FROM session_answers sa
             JOIN questions q ON sa.question_id = q.id
             JOIN topics t ON q.topic_id = t.id
             WHERE sa.session_id = ?
             ORDER BY sa.id
             LIMIT ' . max(1, $limit),
            [$sessionId]
        );
    }

    /** Strengths & weaknesses of one attempt, grouped by topic. */
    public static function topicBreakdown(int $sessionId): array
    {
        return Database::query(
            'SELECT t.name AS topic, s.name AS subject,
                    COUNT(*) AS answered,
                    SUM(sa.is_correct = 1) AS correct,
                    ROUND(SUM(sa.is_correct = 1) / COUNT(*) * 100) AS accuracy
             FROM session_answers sa
             JOIN questions q ON sa.question_id = q.id
             JOIN topics t ON q.topic_id = t.id
             JOIN subjects s ON t.subject_id = s.id
             WHERE sa.session_id = ?
             GROUP BY t.id
             ORDER BY accuracy ASC',
            [$sessionId]
        );
    }
}
