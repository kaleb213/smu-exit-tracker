<?php
/**
 * ============================================================
 *  Subject.php — Subject model
 * ============================================================
 */

class Subject extends Model
{
    // All subjects + how many topics each contains
    public function getAll()
    {
        return $this->db->fetchAll(
            'SELECT s.*,
                    (SELECT COUNT(*) FROM topics t WHERE t.subject_id = s.id) AS topic_count
             FROM subjects s
             ORDER BY s.name'
        );
    }

    public function findById($id)
    {
        return $this->db->fetch(
            'SELECT * FROM subjects WHERE id = ? LIMIT 1',
            [(int)$id]
        );
    }
}