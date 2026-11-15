<?php
require_once APP_PATH . '/core/Model.php';

class Report extends Model
{
    private $nameExpression = null;

    /* Detects how the full student name is stored in the users table */
    private function nameExpr()
    {
        if ($this->nameExpression !== null) {
            return $this->nameExpression;
        }

        $rows   = $this->db->fetchAll("SHOW COLUMNS FROM users");
        $fields = [];
        foreach ($rows as $row) {
            $fields[] = $row['Field'];
        }

        $has = function ($f) use ($fields) {
            return in_array($f, $fields, true);
        };

        if ($has('first_name') && $has('last_name')) {
            $this->nameExpression = "CONCAT(u.first_name, ' ', u.last_name)";
        } elseif ($has('full_name')) {
            $this->nameExpression = 'u.full_name';
        } elseif ($has('name')) {
            $this->nameExpression = 'u.name';
        } else {
            $this->nameExpression = 'u.id_number';
        }

        return $this->nameExpression;
    }

    /* Top-level numbers for the report header */
    public function classOverview()
    {
        return $this->db->fetch("
            SELECT
                (SELECT COUNT(*) FROM users WHERE LOWER(role) = 'student') AS total_students,
                (SELECT COUNT(*) FROM exam_attempts) AS total_attempts,
                COALESCE((SELECT ROUND(AVG(percentage), 1) FROM exam_attempts), 0) AS class_avg,
                COALESCE((SELECT ROUND(AVG(percentage), 1) FROM exam_attempts
                          WHERE exam_type = 'mock'), 0) AS mock_avg
        ");
    }

    /* Class average per subject (subjects with 0 attempts included as 0) */
    public function subjectAverages()
    {
        return $this->db->fetchAll("
            SELECT s.id, s.name,
                   COUNT(a.id) AS attempts,
                   COALESCE(ROUND(AVG(a.percentage), 1), 0) AS avg_percentage,
                   COALESCE(MAX(a.percentage), 0) AS best_percentage
            FROM subjects s
            LEFT JOIN exam_attempts a ON a.subject_id = s.id
            GROUP BY s.id, s.name
            ORDER BY s.name
        ");
    }

    /* Weakest topics class-wide, lowest average first */
    public function weakTopics($limit = 10)
    {
        $limit = (int)$limit;
        return $this->db->fetchAll("
            SELECT t.id, t.name AS topic_name, s.name AS subject_name,
                   COUNT(a.id) AS attempts,
                   COALESCE(ROUND(AVG(a.percentage), 1), 0) AS avg_percentage
            FROM exam_attempts a
            INNER JOIN topics t   ON t.id = a.topic_id
            INNER JOIN subjects s ON s.id = t.subject_id
            GROUP BY t.id, t.name, s.name
            ORDER BY avg_percentage ASC, attempts DESC
            LIMIT $limit
        ");
    }

    /* One row per student: activity and averages */
    public function studentSummaries()
    {
        $nameExpr = $this->nameExpr();
        return $this->db->fetchAll("
            SELECT u.id, u.id_number, $nameExpr AS student_name,
                   COUNT(a.id) AS attempts,
                   COALESCE(ROUND(AVG(a.percentage), 1), 0) AS avg_percentage,
                   COALESCE(MAX(a.percentage), 0) AS best_percentage,
                   MAX(a.submitted_at) AS last_attempt
            FROM users u
            LEFT JOIN exam_attempts a ON a.user_id = u.id
            WHERE LOWER(u.role) = 'student'
            GROUP BY u.id
            ORDER BY avg_percentage DESC
        ");
    }

    /* Class average per day, last N active days, oldest -> newest */
    public function dailyAverages($days = 14)
    {
        $days = (int)$days;
        return $this->db->fetchAll("
            SELECT t.attempt_date, t.attempts, t.avg_percentage
            FROM (
                SELECT DATE(a.submitted_at) AS attempt_date,
                       COUNT(*) AS attempts,
                       ROUND(AVG(a.percentage), 1) AS avg_percentage
                FROM exam_attempts a
                GROUP BY DATE(a.submitted_at)
                ORDER BY attempt_date DESC
                LIMIT $days
            ) t
            ORDER BY t.attempt_date ASC
        ");
    }

    /* Single student summary for the drill-down page */
    public function studentDetail($userId)
    {
        $nameExpr = $this->nameExpr();
        return $this->db->fetch("
            SELECT u.id, u.id_number, u.role, $nameExpr AS student_name,
                   COUNT(a.id) AS attempts,
                   COALESCE(ROUND(AVG(a.percentage), 1), 0) AS avg_percentage,
                   COALESCE(MAX(a.percentage), 0) AS best_percentage,
                   MAX(a.submitted_at) AS last_attempt
            FROM users u
            LEFT JOIN exam_attempts a ON a.user_id = u.id
            WHERE u.id = :uid
            GROUP BY u.id
        ", [':uid' => (int)$userId]);
    }
}