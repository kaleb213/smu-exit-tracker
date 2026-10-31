<?php
/**
 * ============================================================
 *  Question.php — Question bank model
 * ============================================================
 */

class Question extends Model
{
    // Active questions for one topic (used by Practice Mode)
    public function getByTopic($topicId)
    {
        return $this->db->fetchAll(
            'SELECT * FROM questions
             WHERE topic_id = ? AND is_active = 1
             ORDER BY id',
            [(int)$topicId]
        );
    }
}