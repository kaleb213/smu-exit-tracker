-- ============================================================
--  Web-Based Exit Exam Preparation and Progress Tracker
--  St. Mary's University - Department of Computer Science
--  Senior Project: Edel Wondimeneh, Fitsum Abrham,
--                  Kaleb Aklilu, Solomon Dawit
--
--  MySQL schema (run in phpMyAdmin or: mysql -u root < schema.sql)
-- ============================================================

CREATE DATABASE IF NOT EXISTS smu_exit_exam
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smu_exit_exam;

-- ---------- users : one table for all three roles ----------
-- role: student | instructor | admin
-- Students register themselves; instructors are created by the
-- admin; the admin account is seeded during setup (business rule).
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  id_number     VARCHAR(30)  NOT NULL UNIQUE,   -- login credential (e.g. SMU/CS/1234, STAFF/018)
  password_hash VARCHAR(255) NOT NULL,          -- password_hash() (bcrypt)
  role          ENUM('student','instructor','admin') NOT NULL DEFAULT 'student',
  status        ENUM('active','deactivated') NOT NULL DEFAULT 'active',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login    DATETIME NULL,
  profile_photo VARCHAR(255) NULL,
  presence_status ENUM('online','away','offline') NOT NULL DEFAULT 'offline',
  last_seen_at  DATETIME NULL
) ENGINE=InnoDB;

-- ---------- subjects : exit exam subject areas ----------
CREATE TABLE IF NOT EXISTS subjects (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80)  NOT NULL UNIQUE,
  description VARCHAR(255) NOT NULL DEFAULT '',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- topics : subjects are organised into topics ----------
CREATE TABLE IF NOT EXISTS topics (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subject_id INT UNSIGNED NOT NULL,
  name       VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_topic (subject_id, name),
  CONSTRAINT fk_topics_subject FOREIGN KEY (subject_id)
    REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- questions : validated bank, options A-D ----------
-- is_validated: admin review state (doc: question bank validation)
CREATE TABLE IF NOT EXISTS questions (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  topic_id       INT UNSIGNED NOT NULL,
  question_text  TEXT NOT NULL,
  option_a       VARCHAR(500) NOT NULL,
  option_b       VARCHAR(500) NOT NULL,
  option_c       VARCHAR(500) NOT NULL,
  option_d       VARCHAR(500) NOT NULL,
  correct_option ENUM('A','B','C','D') NOT NULL,
  explanation    TEXT NOT NULL DEFAULT '',
  difficulty     ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
  is_validated   TINYINT(1) NOT NULL DEFAULT 0,
  image_path     VARCHAR(255) NULL,          -- room for images in questions
  created_by     INT UNSIGNED NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_questions_topic FOREIGN KEY (topic_id)
    REFERENCES topics(id) ON DELETE CASCADE,
  CONSTRAINT fk_questions_creator FOREIGN KEY (created_by)
    REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_questions_topic (topic_id),
  INDEX idx_questions_valid (is_validated)
) ENGINE=InnoDB;

-- ---------- exam_sessions : one row per student attempt ----------
-- mode: practice (untimed, instant feedback) | mock (timed, deferred)
-- status: in_progress | completed | expired (auto-submit on timeout)
CREATE TABLE IF NOT EXISTS exam_sessions (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id            INT UNSIGNED NOT NULL,
  mode               ENUM('practice','mock') NOT NULL,
  subject_id         INT UNSIGNED NULL,      -- NULL for full mock (all subjects)
  topic_id           INT UNSIGNED NULL,
  total_questions    INT UNSIGNED NOT NULL DEFAULT 0,
  correct_count      INT UNSIGNED NOT NULL DEFAULT 0,
  score_percent      DECIMAL(5,2) NOT NULL DEFAULT 0,
  time_limit_minutes INT UNSIGNED NULL,      -- NULL = untimed
  started_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  submitted_at       DATETIME NULL,
  status             ENUM('in_progress','completed','expired') NOT NULL DEFAULT 'in_progress',
  CONSTRAINT fk_sessions_user    FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sessions_subject FOREIGN KEY (subject_id)
    REFERENCES subjects(id) ON DELETE SET NULL,
  CONSTRAINT fk_sessions_topic   FOREIGN KEY (topic_id)
    REFERENCES topics(id) ON DELETE SET NULL,
  INDEX idx_sessions_user (user_id, status),
  INDEX idx_sessions_mode (mode, status)
) ENGINE=InnoDB;

-- ---------- session_answers : per-question audit trail ----------
-- Kept permanently: powers results review, topic diagnostics and
-- instructor analytics. RestRICT: a question with recorded answers
-- cannot be deleted (protects historical data).
CREATE TABLE IF NOT EXISTS session_answers (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id      INT UNSIGNED NOT NULL,
  question_id     INT UNSIGNED NOT NULL,
  selected_option CHAR(1) NULL,             -- NULL = skipped
  is_correct      TINYINT(1) NOT NULL DEFAULT 0,
  answered_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_answers_session  FOREIGN KEY (session_id)
    REFERENCES exam_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_answers_question FOREIGN KEY (question_id)
    REFERENCES questions(id) ON DELETE RESTRICT,
  UNIQUE KEY uq_session_question (session_id, question_id),
  INDEX idx_answers_question (question_id)
) ENGINE=InnoDB;

-- ---------- user_goals : the goal-setting tool ----------
CREATE TABLE IF NOT EXISTS user_goals (
  user_id        INT UNSIGNED PRIMARY KEY,
  target_percent TINYINT UNSIGNED NOT NULL DEFAULT 80,
  updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_goals_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- settings : admin-managed exam configuration ----------
CREATE TABLE IF NOT EXISTS settings (
  setting_key   VARCHAR(40) PRIMARY KEY,
  setting_value VARCHAR(100) NOT NULL
) ENGINE=InnoDB;
