-- ============================================================
--  smu_exit_exam_schema.sql
--  Web-Based Exit Exam Preparation and Progress Tracker
--  St. Mary's University - Senior Project
--  Import this file ONCE in phpMyAdmin.
--
--  To start completely over later, run first:
--    DROP DATABASE IF EXISTS smu_exit_exam;
-- ============================================================

CREATE DATABASE IF NOT EXISTS smu_exit_exam
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smu_exit_exam;

-- ------------------------------------------------------------
-- 1. USERS - students, instructors, admins (one table, role column)
-- ------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    id_number     VARCHAR(30)   NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('student','instructor','admin') NOT NULL DEFAULT 'student',
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_id_number (id_number)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. SUBJECTS - top-level exam content categories
-- ------------------------------------------------------------
CREATE TABLE subjects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_subjects_name (name)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. TOPICS - subcategories inside each subject
-- ------------------------------------------------------------
CREATE TABLE topics (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id INT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_topics_subject_name (subject_id, name),
    CONSTRAINT fk_topics_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. QUESTIONS - the question bank (multiple choice, A-D)
-- ------------------------------------------------------------
CREATE TABLE questions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topic_id       INT UNSIGNED NOT NULL,
    question_text  TEXT NOT NULL,
    option_a       VARCHAR(255) NOT NULL,
    option_b       VARCHAR(255) NOT NULL,
    option_c       VARCHAR(255) NOT NULL,
    option_d       VARCHAR(255) NOT NULL,
    correct_option ENUM('A','B','C','D') NOT NULL,
    explanation    TEXT NULL,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_questions_topic
        FOREIGN KEY (topic_id) REFERENCES topics(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. EXAM_ATTEMPTS - one row per submitted practice/mock exam
--    (score is calculated on the server - PDF requirement)
-- ------------------------------------------------------------
CREATE TABLE exam_attempts (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    subject_id       INT UNSIGNED NOT NULL,
    topic_id         INT UNSIGNED NULL,          -- NULL for mock exams
    exam_type        ENUM('practice','mock') NOT NULL,
    total_questions  INT NOT NULL,
    score            INT NOT NULL,               -- number of correct answers
    percentage       DECIMAL(5,2) NOT NULL,      -- 0.00 - 100.00
    duration_seconds INT NULL,                   -- time taken (mock exams)
    submitted_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attempts_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_attempts_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempts_topic   FOREIGN KEY (topic_id)   REFERENCES topics(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_attempts_user    ON exam_attempts (user_id);
CREATE INDEX idx_attempts_subject ON exam_attempts (subject_id);
CREATE INDEX idx_attempts_topic   ON exam_attempts (topic_id);

-- ------------------------------------------------------------
-- 6. ATTEMPT_ANSWERS - every answer given inside an attempt
-- ------------------------------------------------------------
CREATE TABLE attempt_answers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id      INT UNSIGNED NOT NULL,
    question_id     INT UNSIGNED NOT NULL,
    selected_option ENUM('A','B','C','D') NULL,  -- NULL = not answered
    is_correct      TINYINT(1) NOT NULL,
    CONSTRAINT fk_answers_attempt  FOREIGN KEY (attempt_id)  REFERENCES exam_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_answers_attempt ON attempt_answers (attempt_id);

-- ------------------------------------------------------------
-- 7. PERFORMANCE RECORDS (view)
--    Aggregated per student + subject. Used later by the
--    Instructor module for weak-topic and trend reports.
--    Computed from exam_attempts so it can never go out of sync.
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW vw_performance_records AS
SELECT
    a.user_id,
    u.full_name,
    a.subject_id,
    s.name AS subject_name,
    COUNT(*)                    AS attempts_count,
    ROUND(AVG(a.percentage), 2) AS avg_percentage,
    MAX(a.percentage)           AS best_percentage,
    MAX(a.submitted_at)         AS last_attempt_at
FROM exam_attempts a
JOIN users u    ON u.id = a.user_id
JOIN subjects s ON s.id = a.subject_id
GROUP BY a.user_id, u.full_name, a.subject_id, s.name;