<?php
/**
 * Admin model — dashboard stats, users CRUD, subjects/topics/questions CRUD,
 * exam settings. Uses the Database wrapper (query/fetch/fetchAll/lastInsertId).
 * Column names are auto-detected so this matches the real schema.
 */
class Admin
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* ============ schema detection ============ */

    private function colsOf($table)
    {
        static $cache = [];
        if (!isset($cache[$table])) {
            $cache[$table] = [];
            try {
                foreach ($this->db->fetchAll("SHOW COLUMNS FROM " . $table) as $r) {
                    $cache[$table][strtolower($r['Field'])] = strtolower((string)$r['Type']);
                }
            } catch (Throwable $e) {
                $cache[$table] = [];
            }
        }
        return $cache[$table];
    }

    public function has($table, $col)
    {
        return array_key_exists(strtolower($col), $this->colsOf($table));
    }

    public function colType($table, $col)
    {
        $c = $this->colsOf($table);
        return isset($c[strtolower($col)]) ? $c[strtolower($col)] : '';
    }

    private function firstCol($table, array $candidates)
    {
        foreach ($candidates as $c) {
            if ($this->has($table, $c)) return $c;
        }
        return null;
    }

    /* ============ users ============ */

    public function userNameCols()
    {
        if ($this->has('users', 'first_name') && $this->has('users', 'last_name')) return ['first_name', 'last_name'];
        if ($this->has('users', 'full_name')) return ['full_name'];
        if ($this->has('users', 'name')) return ['name'];
        return [];
    }

    public function userSelectName($alias = 'u')
    {
        if ($this->has('users', 'first_name') && $this->has('users', 'last_name'))
            return "CONCAT(COALESCE($alias.first_name,''), ' ', COALESCE($alias.last_name,''))";
        if ($this->has('users', 'full_name')) return "$alias.full_name";
        if ($this->has('users', 'name')) return "$alias.name";
        return "$alias.id_number";
    }

    public function passwordCol()
    {
        return $this->firstCol('users', ['password', 'password_hash', 'pass']) ?: 'password';
    }

    public function activeCol()
    {
        return $this->firstCol('users', ['is_active', 'active', 'status']);
    }

    public function allUsers($role = null, $search = '')
    {
        $name = $this->userSelectName('u');
        $sql = "SELECT u.*, $name AS display_name,
                (SELECT COUNT(*) FROM exam_attempts ea WHERE ea.user_id = u.id) AS attempt_count
                FROM users u WHERE 1=1";
        $params = [];
        if ($role !== null && $role !== '') { $sql .= " AND u.role = ?"; $params[] = $role; }
        if ($search !== '') {
            $sql .= " AND (u.id_number LIKE ? OR $name LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= " ORDER BY u.id ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function findUser($id)
    {
        $name = $this->userSelectName('u');
        return $this->db->fetch("SELECT u.*, $name AS display_name FROM users u WHERE u.id = ?", [(int)$id]);
    }

    public function idNumberTaken($idNumber, $exceptId = 0)
    {
        $r = $this->db->fetch("SELECT id FROM users WHERE id_number = ? AND id <> ?", [$idNumber, (int)$exceptId]);
        return (bool)$r;
    }

    public function createUser(array $data)
    {
        $cols = [];
        $params = [];
        foreach ($data as $col => $val) {
            if ($col !== 'id' && $this->has('users', $col)) { $cols[] = $col; $params[] = $val; }
        }
        if (empty($cols)) throw new RuntimeException('No matching columns found in the users table.');
        $sql = "INSERT INTO users (" . implode(', ', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")";
        $this->db->query($sql, $params);
        return (int)$this->db->lastInsertId();
    }

    public function updateUser($id, array $data)
    {
        $set = [];
        $params = [];
        foreach ($data as $col => $val) {
            if ($col !== 'id' && $this->has('users', $col)) { $set[] = "$col = ?"; $params[] = $val; }
        }
        if (empty($set)) return false;
        $params[] = (int)$id;
        $this->db->query("UPDATE users SET " . implode(', ', $set) . " WHERE id = ?", $params);
        return true;
    }

    public function setUserActive($id, $on)
    {
        $col = $this->activeCol();
        if (!$col) return false;
        $this->db->query("UPDATE users SET $col = ? WHERE id = ?", [$on ? 1 : 0, (int)$id]);
        return true;
    }

    public function resetPassword($id, $plain)
    {
        $this->db->query("UPDATE users SET " . $this->passwordCol() . " = ? WHERE id = ?",
            [password_hash($plain, PASSWORD_DEFAULT), (int)$id]);
        return true;
    }

    public function deleteUser($id)
    {
        $r = $this->db->fetch("SELECT COUNT(*) AS c FROM exam_attempts WHERE user_id = ?", [(int)$id]);
        if ($r && (int)$r['c'] > 0) return 'has_attempts';
        $this->db->query("DELETE FROM users WHERE id = ?", [(int)$id]);
        return 'ok';
    }

    /* ============ subjects / topics ============ */

    public function subjectNameCol() { return $this->firstCol('subjects', ['name', 'subject_name', 'title', 'subject']) ?: 'name'; }
    public function topicNameCol()   { return $this->firstCol('topics', ['name', 'topic_name', 'title', 'topic']) ?: 'name'; }
    public function topicSubjectCol(){ return $this->firstCol('topics', ['subject_id', 'subjects_id', 'subject']) ?: 'subject_id'; }
    public function topicQuestionCol(){ return $this->firstCol('questions', ['topic_id', 'topics_id', 'topic']); }
    public function questionSubjectCol(){ return $this->firstCol('questions', ['subject_id', 'subjects_id', 'subject']); }

    public function allSubjects()
    {
        $n = $this->subjectNameCol();
        return $this->db->fetchAll(
            "SELECT s.*, s.$n AS display_name,
             (SELECT COUNT(*) FROM topics t WHERE t." . $this->topicSubjectCol() . " = s.id) AS topic_count
             FROM subjects s ORDER BY s.$n ASC");
    }

    public function findSubject($id)
    {
        $n = $this->subjectNameCol();
        return $this->db->fetch("SELECT s.*, s.$n AS display_name FROM subjects s WHERE s.id = ?", [(int)$id]);
    }

    public function saveSubject($id, $name)
    {
        $n = $this->subjectNameCol();
        if ($id > 0) {
            $this->db->query("UPDATE subjects SET $n = ? WHERE id = ?", [$name, (int)$id]);
            return (int)$id;
        }
        $this->db->query("INSERT INTO subjects ($n) VALUES (?)", [$name]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteSubject($id)
    {
        $c = $this->db->fetch("SELECT COUNT(*) AS c FROM topics WHERE " . $this->topicSubjectCol() . " = ?", [(int)$id]);
        if ($c && (int)$c['c'] > 0) return 'has_topics';
        $this->db->query("DELETE FROM subjects WHERE id = ?", [(int)$id]);
        return 'ok';
    }

    public function allTopics($subjectId = 0)
    {
        $tn = $this->topicNameCol();
        $sc = $this->topicSubjectCol();
        $sn = $this->subjectNameCol();
        $sql = "SELECT t.*, t.$tn AS display_name, s.$sn AS subject_name
                FROM topics t LEFT JOIN subjects s ON s.id = t.$sc WHERE 1=1";
        $params = [];
        if ((int)$subjectId > 0) { $sql .= " AND t.$sc = ?"; $params[] = (int)$subjectId; }
        $sql .= " ORDER BY s.$sn ASC, t.$tn ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function findTopic($id)
    {
        $tn = $this->topicNameCol();
        return $this->db->fetch("SELECT t.*, t.$tn AS display_name FROM topics t WHERE t.id = ?", [(int)$id]);
    }

    public function saveTopic($id, $name, $subjectId)
    {
        $tn = $this->topicNameCol();
        $sc = $this->topicSubjectCol();
        if ($id > 0) {
            $this->db->query("UPDATE topics SET $tn = ?, $sc = ? WHERE id = ?", [$name, (int)$subjectId, (int)$id]);
            return (int)$id;
        }
        $this->db->query("INSERT INTO topics ($tn, $sc) VALUES (?, ?)", [$name, (int)$subjectId]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteTopic($id)
    {
        $qc = $this->topicQuestionCol();
        if ($qc) {
            $c = $this->db->fetch("SELECT COUNT(*) AS c FROM questions WHERE $qc = ?", [(int)$id]);
            if ($c && (int)$c['c'] > 0) return 'has_questions';
        }
        $this->db->query("DELETE FROM topics WHERE id = ?", [(int)$id]);
        return 'ok';
    }

    /* ============ questions ============ */

    public function qTextCol()    { return $this->firstCol('questions', ['question_text', 'question', 'text', 'body', 'content']) ?: 'question_text'; }
    public function qAnswerCol()  { return $this->firstCol('questions', ['correct_answer', 'correct_option', 'correct', 'answer', 'answer_key']); }
    public function qExplainCol() { return $this->firstCol('questions', ['explanation', 'explanation_text', 'explain', 'rationale']); }
    public function qDiffCol()    { return $this->firstCol('questions', ['difficulty', 'level', 'difficulty_level']); }
    public function qMockCol()    { return $this->firstCol('questions', ['is_mock', 'mock_only', 'for_mock', 'is_mock_exam']); }

    public function qOptionCols()
    {
        $groups = [
            ['option_a', 'option_b', 'option_c', 'option_d'],
            ['a', 'b', 'c', 'd'],
            ['choice_a', 'choice_b', 'choice_c', 'choice_d'],
            ['option1', 'option2', 'option3', 'option4'],
            ['option_1', 'option_2', 'option_3', 'option_4'],
            ['choice_1', 'choice_2', 'choice_3', 'choice_4'],
        ];
        foreach ($groups as $g) {
            if ($this->has('questions', $g[0]) && $this->has('questions', $g[3])) return $g;
        }
        return null;
    }

    public function questionEditableCols()
    {
        $all = array_merge(
            [$this->qTextCol(), $this->qAnswerCol(), $this->qExplainCol(), $this->qDiffCol(),
             $this->topicQuestionCol(), $this->questionSubjectCol(), $this->qMockCol()],
            (array)$this->qOptionCols()
        );
        $cols = [];
        foreach ($all as $c) {
            if ($c !== null && $c !== '' && !in_array($c, $cols, true)) $cols[] = $c;
        }
        return $cols;
    }

    public function searchQuestions($subjectId = 0, $topicId = 0, $search = '', $page = 1, $perPage = 15)
    {
        $tc = $this->qTextCol();
        $qc = $this->topicQuestionCol();
        $sc = $this->questionSubjectCol();
        $tn = $this->topicNameCol();
        $sn = $this->subjectNameCol();
        $ts = $this->topicSubjectCol();

        $joins = '';
        if ($qc) $joins .= " LEFT JOIN topics t ON t.id = q.$qc";
        if ($sc) $joins .= " LEFT JOIN subjects s ON s.id = q.$sc";

        $where = ' WHERE 1=1';
        $params = [];
        if ((int)$subjectId > 0) {
            if ($sc)     { $where .= " AND q.$sc = ?"; $params[] = (int)$subjectId; }
            elseif ($qc) { $where .= " AND t.$ts = ?"; $params[] = (int)$subjectId; }
        }
        if ((int)$topicId > 0 && $qc) { $where .= " AND q.$qc = ?"; $params[] = (int)$topicId; }
        if ($search !== '') { $where .= " AND q.$tc LIKE ?"; $params[] = '%' . $search . '%'; }

        $total = 0;
        $cnt = $this->db->fetch("SELECT COUNT(*) AS c FROM questions q $joins $where", $params);
        if ($cnt) $total = (int)$cnt['c'];
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min(max(1, (int)$page), $pages);
        $offset = ($page - 1) * $perPage;

        $sel = "q.*, q.$tc AS display_text";
        if ($qc) $sel .= ", t.$tn AS topic_name";
        if ($sc) $sel .= ", s.$sn AS subject_name";

        $rows = $this->db->fetchAll(
            "SELECT $sel FROM questions q $joins $where ORDER BY q.id DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            $params);

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    public function findQuestion($id)
    {
        return $this->db->fetch("SELECT * FROM questions WHERE id = ?", [(int)$id]);
    }

    public function answerChoices()
    {
        $vals = [];
        $col = $this->qAnswerCol();
        if ($col) {
            $rows = $this->db->fetchAll("SELECT DISTINCT $col AS v FROM questions WHERE $col IS NOT NULL AND $col <> '' LIMIT 12");
            foreach ($rows as $r) {
                if (isset($r['v']) && $r['v'] !== null && $r['v'] !== '') $vals[] = (string)$r['v'];
            }
        }
        foreach (['A', 'B', 'C', 'D'] as $l) {
            if (!in_array($l, $vals, true)) $vals[] = $l;
        }
        return $vals;
    }

    public function saveQuestion($id, array $data)
    {
        $set = [];
        $params = [];
        foreach ($data as $col => $val) {
            if ($col !== 'id' && $this->has('questions', $col)) { $set[] = "$col = ?"; $params[] = $val; }
        }
        if (empty($set)) throw new RuntimeException('No matching columns found in the questions table.');
        if ($id > 0) {
            $params[] = (int)$id;
            $this->db->query("UPDATE questions SET " . implode(', ', $set) . " WHERE id = ?", $params);
            return (int)$id;
        }
        $this->db->query("INSERT INTO questions SET " . implode(', ', $set), $params);
        return (int)$this->db->lastInsertId();
    }

    public function deleteQuestion($id)
    {
        try {
            $c = $this->db->fetch("SELECT COUNT(*) AS c FROM attempt_answers WHERE question_id = ?", [(int)$id]);
            if ($c && (int)$c['c'] > 0) return 'has_answers';
        } catch (Throwable $e) {
            // answers table/column unknown — let the FK decide below
        }
        try {
            $this->db->query("DELETE FROM questions WHERE id = ?", [(int)$id]);
        } catch (Throwable $e) {
            return 'has_answers';
        }
        return 'ok';
    }

    public function topicOptions()
    {
        $tn = $this->topicNameCol();
        $sc = $this->topicSubjectCol();
        $sn = $this->subjectNameCol();
        return $this->db->fetchAll(
            "SELECT t.id, CONCAT(COALESCE(s.$sn,'-'),' - ',t.$tn) AS label
             FROM topics t LEFT JOIN subjects s ON s.id = t.$sc
             ORDER BY s.$sn ASC, t.$tn ASC");
    }

    public function subjectOptions()
    {
        $sn = $this->subjectNameCol();
        return $this->db->fetchAll("SELECT id, $sn AS label FROM subjects ORDER BY $sn ASC");
    }

    /* ============ dashboard + settings ============ */

    private function countRows($t)
    {
        $r = $this->db->fetch("SELECT COUNT(*) AS c FROM $t");
        return $r ? (int)$r['c'] : 0;
    }

    private function countUsers($role)
    {
        $r = $this->db->fetch("SELECT COUNT(*) AS c FROM users WHERE role = ?", [$role]);
        return $r ? (int)$r['c'] : 0;
    }

    public function attemptScoreCol()
    {
        return $this->firstCol('exam_attempts', ['score', 'percentage', 'score_percent', 'percent', 'total_score', 'marks', 'result']);
    }

    private function attemptTimeCol()
    {
        return $this->firstCol('exam_attempts', ['submitted_at', 'created_at', 'attempted_at', 'time']) ?: 'submitted_at';
    }

    public function stats()
    {
        $s = [];
        $s['students']    = $this->countUsers('student');
        $s['instructors'] = $this->countUsers('instructor');
        $s['admins']      = $this->countUsers('admin');
        $s['subjects']    = $this->countRows('subjects');
        $s['topics']      = $this->countRows('topics');
        $s['questions']   = $this->countRows('questions');
        $s['attempts']    = $this->countRows('exam_attempts');
        $s['answers']     = $this->countRows('attempt_answers');
        $s['class_avg'] = null;
        $sc = $this->attemptScoreCol();
        if ($sc) {
            $r = $this->db->fetch("SELECT AVG($sc) AS a FROM exam_attempts");
            if ($r && $r['a'] !== null) $s['class_avg'] = round((float)$r['a'], 1);
        }
        return $s;
    }

    public function recentAttempts($limit = 8)
    {
        $name = $this->userSelectName('u');
        $tc = $this->attemptTimeCol();
        $sc = $this->attemptScoreCol();
        $scoreSel = $sc ? ", ea.$sc AS attempt_score" : ", NULL AS attempt_score";
        return $this->db->fetchAll(
            "SELECT ea.*, $name AS student_name $scoreSel
             FROM exam_attempts ea JOIN users u ON u.id = ea.user_id
             ORDER BY ea.$tc DESC LIMIT " . (int)$limit);
    }

    public function allSettings()
    {
        return $this->db->fetchAll("SELECT * FROM settings ORDER BY setting_key ASC");
    }

    public function getSetting($key, $default = null)
    {
        $r = $this->db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        return ($r && isset($r['setting_value']) && $r['setting_value'] !== '') ? $r['setting_value'] : $default;
    }

    public function setSetting($key, $value)
    {
        $r = $this->db->fetch("SELECT setting_key FROM settings WHERE setting_key = ?", [$key]);
        if ($r) {
            $this->db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        } else {
            $this->db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
        }
    }
}