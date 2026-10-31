<?php
/**
 * ============================================================
 *  Topic.php — Topic model
 * ============================================================
 */

class Topic extends Model
{
    public function getById($id)
    {
        return $this->db->fetch(
            'SELECT * FROM topics WHERE id = ? LIMIT 1',
            [(int)$id]
        );
    }

    // Topics of a subject + live count of active questions in each
    public function getBySubject($subjectId)
    {
        return $this->db->fetchAll(
            'SELECT t.*,
                    (SELECT COUNT(*) FROM questions q
                      WHERE q.topic_id = t.id AND q.is_active = 1) AS question_count
             FROM topics t
             WHERE t.subject_id = ?
             ORDER BY t.name',
            [(int)$subjectId]
        );
    }
}