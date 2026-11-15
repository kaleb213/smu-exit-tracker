<?php
require_once APP_PATH . '/core/Model.php';

class Progress extends Model
{
    /* Cached name of the real timestamp column in exam_attempts */
    private $timeColumn = null;

    /*
     * Returns the raw PDO connection no matter how the Database class
     * exposes it (direct PDO, accessor method, or internal property).
     */
    private function pdo()
    {
        if ($this->db instanceof PDO) {
            return $this->db;
        }

        foreach (['getConnection', 'getPDO', 'getConn', 'connect', 'pdo'] as $method) {
            if (method_exists($this->db, $method)) {
                $result = $this->db->$method();
                if ($result instanceof PDO) {
                    return $result;
                }
            }
        }

        /* PHP 8.1+: reflection reads private props directly (no setAccessible) */
        $ref = new ReflectionObject($this->db);
        foreach ($ref->getProperties() as $prop) {
            try {
                $value = $prop->isStatic() ? $prop->getValue() : $prop->getValue($this->db);
                if ($value instanceof PDO) {
                    return $value;
                }
            } catch (Throwable $t) {
                /* skip uninitialized / unreadable property */
            }
        }

        throw new RuntimeException('Progress model could not locate the PDO connection.');
    }

    /*
     * Detects the actual date/time column of exam_attempts once,
     * so queries never depend on the column being named created_at.
     */
    private function timeCol()
    {
        if ($this->timeColumn !== null) {
            return $this->timeColumn;
        }

        $pdo  = $this->pdo();
        $rows = $pdo->query("SHOW COLUMNS FROM exam_attempts")->fetchAll(PDO::FETCH_ASSOC);

        $preferred = [
            'created_at', 'attempted_at', 'attempt_date', 'date_taken',
            'taken_at', 'completed_at', 'submitted_at', 'finished_at',
            'started_at', 'created', 'created_on'
        ];
        foreach ($rows as $row) {
            if (in_array($row['Field'], $preferred, true)) {
                $this->timeColumn = $row['Field'];
                return $this->timeColumn;
            }
        }

        /* Fall back to the first date/time-like column */
        foreach ($rows as $row) {
            $type = strtolower($row['Type']);
            if (strpos($type, 'timestamp') !== false || strpos($type, 'datetime') !== false || strpos($type, 'date') !== false) {
                $this->timeColumn = $row['Field'];
                return $this->timeColumn;
            }
        }

        /* Last resort: insertion order */
        $this->timeColumn = 'id';
        return $this->timeColumn;
    }

    /* Last $limit attempts, oldest -> newest (line chart data) */
    public function getScoreHistory($userId, $limit = 20)
    {
        $limit = (int)$limit;
        $pdo = $this->pdo();
        $tc  = $this->timeCol();
        $sql = "SELECT percentage, exam_type, $tc AS created_at
                FROM (
                    SELECT percentage, exam_type, $tc
                    FROM exam_attempts
                    WHERE user_id = :uid
                    ORDER BY $tc DESC
                    LIMIT $limit
                ) AS recent
                ORDER BY created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Average % per subject (subjects with 0 attempts included as 0) */
    public function getSubjectBreakdown($userId)
    {
        $pdo = $this->pdo();
        $sql = "SELECT s.id, s.name,
                       COUNT(a.id) AS attempts,
                       COALESCE(ROUND(AVG(a.percentage), 1), 0) AS avg_percentage
                FROM subjects s
                LEFT JOIN exam_attempts a
                       ON a.subject_id = s.id AND a.user_id = :uid
                GROUP BY s.id, s.name
                ORDER BY s.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Accuracy per topic, weakest first (mock has no topic so excluded) */
    public function getTopicStats($userId)
    {
        $pdo = $this->pdo();
        $sql = "SELECT t.id, t.name AS topic_name, s.name AS subject_name,
                       COUNT(a.id) AS attempts,
                       COALESCE(ROUND(AVG(a.percentage), 1), 0) AS avg_percentage
                FROM exam_attempts a
                INNER JOIN topics t   ON t.id = a.topic_id
                INNER JOIN subjects s ON s.id = t.subject_id
                WHERE a.user_id = :uid
                GROUP BY t.id, t.name, s.name
                ORDER BY avg_percentage ASC, attempts DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Readiness = average % of last 5 attempts (null when no attempts) */
    public function getReadiness($userId)
    {
        $pdo = $this->pdo();
        $tc  = $this->timeCol();

        $check = $pdo->prepare("SELECT COUNT(*) FROM exam_attempts WHERE user_id = :uid");
        $check->execute([':uid' => (int)$userId]);
        if ((int)$check->fetchColumn() === 0) {
            return null;
        }

        $sql = "SELECT ROUND(AVG(percentage), 1) AS readiness
                FROM (
                    SELECT percentage
                    FROM exam_attempts
                    WHERE user_id = :uid
                    ORDER BY $tc DESC
                    LIMIT 5
                ) AS last5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$userId]);
        return (float)$stmt->fetchColumn();
    }

    /* Recent attempts for the table (date aliased as created_at for the view) */
    public function getRecentAttempts($userId, $limit = 10)
    {
        $limit = (int)$limit;
        $pdo = $this->pdo();
        $tc  = $this->timeCol();
        $sql = "SELECT a.id, a.exam_type, a.total_questions, a.score, a.percentage,
                       a.$tc AS created_at, s.name AS subject_name, t.name AS topic_name
                FROM exam_attempts a
                LEFT JOIN subjects s ON s.id = a.subject_id
                LEFT JOIN topics t   ON t.id = a.topic_id
                WHERE a.user_id = :uid
                ORDER BY a.$tc DESC
                LIMIT $limit";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}