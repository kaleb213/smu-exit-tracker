<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Controller;
use App\Core\Database;
use App\Models\ExamSession;

/**
 * Score & readiness calculations (Progress Tracking subsystem).
 *
 * Readiness metric (documented, deterministic and safe at boundaries):
 *   readiness = 65% * average(last 5 completed attempts)
 *             + 35% * average(all completed attempts)
 *   If a student has no attempts, readiness is 0 (no division by zero
 *   — explicit boundary condition from the design document).
 */
final class ScoreService
{
    private const RECENT_WEIGHT = 0.65;
    private const OVERALL_WEIGHT = 0.35;
    private const RECENT_COUNT = 5;

    public static function readinessForUser(int $userId): int
    {
        $overall = (float) (Database::first(
            'SELECT COALESCE(AVG(score_percent), 0) AS avg
             FROM exam_sessions WHERE user_id = ? AND status = "completed"',
            [$userId]
        )['avg'] ?? 0);

        $recent = array_column(ExamSession::lastCompleted($userId, self::RECENT_COUNT), 'score_percent');
        if (count($recent) === 0) {
            return 0; // boundary: no attempts yet
        }

        $recentAvg = array_sum($recent) / count($recent);
        return (int) round(self::RECENT_WEIGHT * $recentAvg + self::OVERALL_WEIGHT * $overall);
    }

    /** Progress trend: difference between the last two completed attempts. */
    public static function trendForUser(int $userId): int
    {
        $rows = ExamSession::lastCompleted($userId, 2);
        if (count($rows) < 2) {
            return 0;
        }
        return (int) round((float) $rows[1]['score_percent'] - (float) $rows[0]['score_percent']);
    }

    /** Goal-setting tool: per-student readiness target (default from settings). */
    public static function goalForUser(int $userId): int
    {
        $row = Database::first('SELECT target_percent FROM user_goals WHERE user_id = ?', [$userId]);
        if ($row !== null) {
            return (int) $row['target_percent'];
        }
        return Controller::setting('readiness_target', 80);
    }

    public static function saveGoal(int $userId, int $target): void
    {
        $target = max(0, min(100, $target));
        if (DB_DRIVER === 'sqlite') {
            Database::execute(
                'INSERT INTO user_goals (user_id, target_percent) VALUES (?,?)
                 ON CONFLICT(user_id) DO UPDATE SET target_percent = excluded.target_percent',
                [$userId, $target]
            );
            return;
        }
        Database::execute(
            'INSERT INTO user_goals (user_id, target_percent) VALUES (?,?)
             ON DUPLICATE KEY UPDATE target_percent = VALUES(target_percent)',
            [$userId, $target]
        );
    }

    /** Colour band used across the UI: red < 50, amber 50-74, green >= 75. */
    public static function band(int|float $percent): string
    {
        return $percent >= 75 ? 'green' : ($percent >= 50 ? 'amber' : 'red');
    }
}
