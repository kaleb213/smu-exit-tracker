-- SQLite mirror of database/schema.sql — used ONLY by the offline
-- test profile (SMU_DB_DRIVER=sqlite) so the system can be tried
-- without MySQL. For production/XAMPP use database/schema.sql.

CREATE TABLE IF NOT EXISTS users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  name          TEXT    NOT NULL,
  id_number     TEXT    NOT NULL UNIQUE,
  password_hash TEXT    NOT NULL,
  role          TEXT    NOT NULL DEFAULT 'student' CHECK (role IN ('student','instructor','admin')),
  status        TEXT    NOT NULL DEFAULT 'active' CHECK (status IN ('active','deactivated')),
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login    DATETIME NULL,
  profile_photo TEXT NULL,
  presence_status TEXT NOT NULL DEFAULT 'offline' CHECK (presence_status IN ('online','away','offline')),
  last_seen_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS subjects (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  name        TEXT NOT NULL UNIQUE,
  description TEXT NOT NULL DEFAULT '',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS topics (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  subject_id INTEGER NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
  name       TEXT NOT NULL,
  UNIQUE (subject_id, name)
);

CREATE TABLE IF NOT EXISTS questions (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  topic_id       INTEGER NOT NULL REFERENCES topics(id) ON DELETE CASCADE,
  question_text  TEXT NOT NULL,
  option_a       TEXT NOT NULL,
  option_b       TEXT NOT NULL,
  option_c       TEXT NOT NULL,
  option_d       TEXT NOT NULL,
  correct_option TEXT NOT NULL CHECK (correct_option IN ('A','B','C','D')),
  explanation    TEXT NOT NULL DEFAULT '',
  difficulty     TEXT NOT NULL DEFAULT 'medium' CHECK (difficulty IN ('easy','medium','hard')),
  is_validated   INTEGER NOT NULL DEFAULT 0,
  image_path     TEXT NULL,
  created_by     INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_questions_topic ON questions(topic_id);
CREATE INDEX IF NOT EXISTS idx_questions_valid ON questions(is_validated);

CREATE TABLE IF NOT EXISTS exam_sessions (
  id                 INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id            INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  mode               TEXT NOT NULL CHECK (mode IN ('practice','mock')),
  subject_id         INTEGER NULL REFERENCES subjects(id) ON DELETE SET NULL,
  topic_id           INTEGER NULL REFERENCES topics(id) ON DELETE SET NULL,
  total_questions    INTEGER NOT NULL DEFAULT 0,
  correct_count      INTEGER NOT NULL DEFAULT 0,
  score_percent      REAL NOT NULL DEFAULT 0,
  time_limit_minutes INTEGER NULL,
  started_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  submitted_at       DATETIME NULL,
  status             TEXT NOT NULL DEFAULT 'in_progress' CHECK (status IN ('in_progress','completed','expired'))
);
CREATE INDEX IF NOT EXISTS idx_sessions_user ON exam_sessions(user_id, status);
CREATE INDEX IF NOT EXISTS idx_sessions_mode ON exam_sessions(mode, status);

CREATE TABLE IF NOT EXISTS session_answers (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  session_id      INTEGER NOT NULL REFERENCES exam_sessions(id) ON DELETE CASCADE,
  question_id     INTEGER NOT NULL REFERENCES questions(id) ON DELETE RESTRICT,
  selected_option TEXT NULL,
  is_correct      INTEGER NOT NULL DEFAULT 0,
  answered_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (session_id, question_id)
);
CREATE INDEX IF NOT EXISTS idx_answers_question ON session_answers(question_id);

CREATE TABLE IF NOT EXISTS user_goals (
  user_id        INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
  target_percent INTEGER NOT NULL DEFAULT 80,
  updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
  setting_key   TEXT PRIMARY KEY,
  setting_value TEXT NOT NULL
);
