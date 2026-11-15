# Web-Based Exit Exam Preparation and Progress Tracker

**St. Mary's University · Faculty of Informatics · Department of Computer Science**
Senior Project — Edel Wondimeneh, Fitsum Abrham, Kaleb Aklilu, Solomon Dawit
Advisor: Instructor Tilahun Kebede

A working implementation of the senior project document: a centralized, data-driven
platform where SMU undergraduate students prepare for the national university exit
examination (MOE, July 2023) through structured practice, timed mock exams, permanent
attempt records, progress analytics and instructor reporting.

---

## 1. Quick start (XAMPP — the documented environment)

1. Install **XAMPP** (Apache + PHP 8.x + MySQL/MariaDB) and start **Apache** and **MySQL**.
2. Copy this folder to `C:\xampp\htdocs\senior-project`.
3. Import the database:
   - open `http://localhost/phpmyadmin` → **Import** → `database/schema.sql` → **Go**
   - then **Import** → `database/seed_questions.sql` → **Go**
   (or from CLI: `mysql -u root < database/schema.sql` and again for the seed file)
4. Open the app: `http://localhost/senior-project/public/`
   (also works at `http://localhost/senior-project/` thanks to the root `.htaccess`).

### Default accounts (from the seed file — change the passwords)

| Role       | Login ID    | Password     | Notes                                  |
|------------|-------------|--------------|----------------------------------------|
| Admin      | `ADMIN001`  | `Admin@123`  | seeded during setup (business rule)    |
| Instructor | `STAFF018`  | `Staff@123`  | Inst. Tilahun Kebede — created by admin|
| Students   | `SMU/CS/1234`, `SMU/CS/1207`, `SMU/CS/1211`, `SMU/CS/1219` | `Student@123` | demo students; anyone can self-register |

## 2. What the system does

- **Three roles** with role-based access control: Student, Instructor, Admin
  (no session → login; wrong role → 403 page).
- **Student self-registration** with Name + Student ID + password; the Student ID is
  the unique login credential and duplicates are rejected. Instructor accounts are
  created **only** by the Admin; the Admin account is seeded at setup.
- **Practice Mode** — untimed, topic-level, questions fetched **randomized** from the
  validated question bank, **immediate AJAX feedback** (correct option + explanation),
  every answer logged.
- **Mock Exam Mode** — replicates the timed national exam: admin-configurable question
  count (default 50) and duration (default 60 min), deferred feedback, auto-submit at
  the deadline, skipped questions counted incorrect.
- **Permanent records** — every attempt and every answer is stored
  (`exam_sessions`, `session_answers`); results pages show score, percentage,
  correct/incorrect/skipped, topic breakdown and a full answer review with explanations.
- **Progress tracking** — score-history chart, per-subject averages, topic-level
  diagnostics (red < 50%, amber 50–74%, green ≥ 75%), and the **readiness metric**
  `65% × avg(last 5 attempts) + 35% × avg(all attempts)` (0 when there are no
  attempts — explicit division-by-zero boundary condition from the document),
  plus a **goal-setting tool** (per-student readiness target).
- **Instructor reporting** — class KPIs, class average by subject, weekly trend,
  weakest topics (needs ≥ 3 recorded answers per topic), per-student performance
  with each student's weakest subject, read-only question review with cohort
  success rates, and CSV export.
- **Admin module** — user management (create instructor, activate/deactivate,
  reset password, delete), subject/topic CRUD, question bank CRUD with a
  **validation workflow** (pending questions are never served to students),
  and exam configuration (question counts, time limit, session timeout, readiness target).

## 3. Tech stack (as specified in the document)

| Layer      | Technology |
|------------|------------|
| Frontend   | HTML5, CSS3 (glassmorphism theme, mobile responsive), JavaScript (vanilla, AJAX + canvas charts — zero external libraries for low bandwidth) |
| Backend    | PHP 8 (plain MVC: front controller → controller → service → model) |
| Database   | MySQL/MariaDB via **PDO prepared statements** (SQL injection-safe) |
| Dev tools  | XAMPP, VS Code, Git & GitHub |

## 4. Project structure

```
senior-project/
├── public/                 # the only web-facing folder (document root)
│   ├── index.php           # front controller: /index.php/controller/action/param
│   ├── .htaccess
│   └── assets/             # css (glass theme), js (timer, ajax, charts, validation), images
├── app/
│   ├── config/config.php   # DB credentials + behaviour defaults
│   ├── core/               # App (router/RBAC), Controller, View, Model,
│   │                       # Database (PDO), Session (30-min idle timeout, CSRF), Auth
│   ├── controllers/        # Auth, Practice, MockExam, Progress, Reporting,
│   │                       # QuestionBank, Admin, Home
│   ├── models/             # User, Subject, Topic, Question, ExamSession, Result
│   ├── services/           # UserService, QuestionBankService, TrackerService,
│   │                       # ScoreService, ReportService (the 5 subsystems)
│   └── views/              # auth, student, instructor, admin, partials, errors
├── database/
│   ├── schema.sql          # MySQL schema (users, subjects, topics, questions,
│   │                       # exam_sessions, session_answers, user_goals, settings)
│   └── seed_questions.sql  # 5 subjects, 20 topics, 100 validated questions,
│                           # default users, exam configuration
├── docs/documentation.md   # full technical documentation
├── tests/                  # offline test profile + test-case checklist
│   ├── run_local_test.sh   # no MySQL needed: SQLite mirror of the schema
│   └── test_cases.md
├── uploads/                # reserved (protected from direct access)
└── README.md
```

## 5. Without MySQL — offline demo profile

If you just want to run the system without configuring MySQL (e.g. on a machine
with PHP only):

```bash
php tests/local_test/make_db.php                # builds tests/local_test/smu_exit_exam.sqlite
SMU_DB_DRIVER=sqlite php -S 127.0.0.1:8080 -t public tests/local_test/router.php
# open http://127.0.0.1:8080
```

`bash tests/run_local_test.sh` does both steps. The production/documented setup
remains XAMPP + MySQL; `app/config/config.php` drives everything.

## 6. Security summary (implemented)

- Passwords hashed with bcrypt (`password_hash` / `password_verify`).
- All SQL through PDO **prepared statements**; outputs escaped (`htmlspecialchars`).
- Sessions: secure cookie, `session_regenerate_id` on login, CSRF token on every
  POST form, **automatic logout after 30 minutes of inactivity** (admin-configurable).
- Role-based access control on every controller action (403 page for violations).
- History protection: questions/subjects/topics with recorded answers cannot be
  deleted (FK RESTRICT + application guards); deactivated users cannot log in.

## 7. Verification

All PHP files pass `php -l`; the full system was exercised end-to-end
(login for all roles, registration + duplicate rejection, practice session with
AJAX feedback, mock exam with skipped-question scoring, results/feedback, progress
analytics + goal setting, instructor dashboards + CSV, admin CRUD + guards +
configuration). See `tests/test_cases.md` for the checklist mapped to the
functional requirements.


## Profile photos and live account presence (added 15 Nov 2026)

For an existing XAMPP database, first back up `smu_exit_exam`, then open phpMyAdmin, select the `smu_exit_exam` database, open the SQL tab, and run `database/migrations/2026_11_15_profile_and_presence.sql` once. Fresh installations already receive these columns from `database/schema.sql`.

Profile photos are stored in `public/uploads/profiles/`. The application accepts JPG, PNG and WebP images up to 2 MB and checks their MIME type and dimensions. Ensure the web-server account can write to `public/uploads/`. Do not remove the `.htaccess` file in that folder.

The presence indicator sends a small authenticated heartbeat while a signed-in page is open. It reports Online while active, Away after two minutes without interaction or while the tab is hidden, and Offline on logout or when the browser sends its best-effort page-close signal. If the browser or device loses power/network abruptly, the last state may remain until a later heartbeat or page load; browser close signals cannot be guaranteed.
