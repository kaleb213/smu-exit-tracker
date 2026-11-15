<?php
class ExamAttempt extends Model
{
    public function save($userId, $subjectId, $topicId, $type, $total, $score, $percentage)
    {
        $this->db->query(
            "INSERT INTO exam_attempts (user_id, subject_id, topic_id, exam_type, total_questions, score, percentage)
             VALUES (:uid, :sid, :tid, :type, :total, :score, :pct)",
            [
                ':uid'   => (int)$userId,
                ':sid'   => (int)$subjectId,
                ':tid'   => ($topicId === null) ? null : (int)$topicId,
                ':type'  => $type,
                ':total' => (int)$total,
                ':score' => (int)$score,
                ':pct'   => $percentage,
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    public function saveAnswers($attemptId, array $rows)
    {
        $sql = "INSERT INTO attempt_answers (attempt_id, question_id, selected_option, is_correct)
                VALUES (:aid, :qid, :sel, :cor)";

        foreach ($rows as $r) {
            $this->db->query($sql, [
                ':aid' => (int)$attemptId,
                ':qid' => (int)$r['question_id'],
                ':sel' => $r['selected'],
                ':cor' => (int)$r['is_correct'],
            ]);
        }
    }

    public function findDetailed($id)
    {
        return $this->db->fetch(
            "SELECT a.*, s.name AS subject_name, t.name AS topic_name
             FROM exam_attempts a
             JOIN subjects s ON s.id = a.subject_id
             LEFT JOIN topics t ON t.id = a.topic_id
             WHERE a.id = :id",
            [':id' => (int)$id]
        );
    }

    public function getReview($attemptId)
    {
        return $this->db->fetchAll(
            "SELECT aa.question_id, aa.selected_option AS selected, aa.is_correct,
                    q.question_text, q.option_a, q.option_b, q.option_c, q.option_d,
                    q.correct_option, q.explanation
             FROM attempt_answers aa
             JOIN questions q ON q.id = aa.question_id
             WHERE aa.attempt_id = :aid
             ORDER BY q.id",
            [':aid' => (int)$attemptId]
        );
    }

    public function getSummary($userId)
    {
        return $this->db->fetch(
            "SELECT COUNT(*) AS attempts, COUNT(*) AS total_attempts,
                    COALESCE(ROUND(AVG(percentage), 2), 0) AS avg_percentage,
                    COALESCE(MAX(percentage), 0) AS best_percentage
             FROM exam_attempts
             WHERE user_id = :uid",
            [':uid' => (int)$userId]
        );
    }
}