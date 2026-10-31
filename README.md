# SMU Exit Exam Preparation & Progress Tracker

A web-based platform for St. Marys University (SMU) that helps students prepare
for the university exit exam, and gives instructors and administrators the tools
to manage the question bank, monitor performance, and configure the mock exam.

Built with PHP, MySQL and Chart.js on the XAMPP stack, following an MVC
front-controller architecture.

## Features

### Students
- Self-registration and secure login (single login form for all three roles)
- **Practice Mode** — answer questions one by one with instant feedback and
  explanations after every answer
- **Mock Exam Mode** — timed exam (default 30 minutes), randomized question
  order, automatic submit when time expires, answers validated on the server
- **Progress dashboard** — score history, subject breakdown, weakest topics and
  an exam-readiness score, all rendered with Chart.js (loaded locally)

### Instructors
- Class reports — totals, class average, subject averages, weakest topics and
  per-student summaries
- Trends — daily class average (line chart) and subject comparison (bar chart)
- Per-student drill-down page with readiness score, charts and attempt history
- One-click CSV export of all student summaries

### Administrators
- Dashboard — counters for users, subjects, topics, questions and attempts,
  class average and the latest exam attempts
- User management — create, edit, deactivate/activate, delete and reset
  passwords (accounts with exam history should be deactivated, not deleted)
- Subject / Topic / Question management — full CRUD with filters, search and
  pagination on the question bank
- **Exam Settings** — mock exam duration and question count stored in the
  `settings` table; MockController reads them at runtime and falls back to its
  built-in constants (30 min / 50 questions) if a value is missing

## Technology
| Layer     | Choice |
|-----------|--------|
| Backend   | PHP 8.x, MVC front controller (URL -> Controller -> Model -> View) |
| Database  | MySQL / MariaDB (XAMPP), InnoDB with foreign keys |
| Charts    | Chart.js 4.4.1 served locally from `public/lib/` |
| Security  | PDO prepared statements everywhere, `password_hash` / `password_verify`, session regeneration on login, role guards, HTML escaping in views |

## Installation (XAMPP)
1. Install XAMPP, start **Apache** and **MySQL**.
2. Copy the project folder to `C:\xampp\htdocs\smu-exam-tracker`.
3. Import the database (either way works):
   - phpMyAdmin (http://localhost/phpmyadmin) -> Import ->
     `database/smu_exit_exam_dump.sql`, or
   - command line: `C:\xampp\mysql\bin\mysql.exe -u root < database\smu_exit_exam_dump.sql`
4. Check the application configuration (DB name `smu_exit_exam`, credentials,
   BASE_URL) in the config file under `app/config/`.
5. Open http://localhost/smu-exam-tracker and log in.

## URL Map
| URL | Page |
|-----|------|
| `/login`, `/` | Login (all roles) |
| `/register` | Student self-registration |
| `/student` | Student dashboard |
| `/practice` | Practice mode |
| `/mock` | Mock exam |
| `/progress` | Progress charts |
| `/instructor` | Instructor reports |
| `/instructor/trends` | Class trends |
| `/instructor/student/{id}` | Per-student drill-down |
| `/instructor/export` | CSV export |
| `/admin` | Admin dashboard |
| `/admin/users` | User management |
| `/admin/user/{id}` | Edit user, reset password, deactivate |
| `/admin/subjects` | Subjects CRUD |
| `/admin/topics` | Topics CRUD |
| `/admin/questions` | Question bank (filter, search, paginate) |
| `/admin/question/{id}` | Add (0) / edit question |
| `/admin/settings` | Mock exam settings |

## Accounts & Roles
- Registration always creates a **student** account.
- Create instructor/admin accounts from **Admin -> Users**, or change the role
  of an existing account under **Admin -> Users -> Manage**.
- Role changes take effect on the **next login** (the role is read at login).
- The `is_active` flag on users stores deactivated accounts; their exam history
  is preserved.

## Database (smu_exit_exam)
| Table | Purpose |
|-------|---------|
| `users` | full_name, id_number (unique), password_hash, role enum(student/instructor/admin), is_active |
| `subjects` | exam subjects (name, description) |
| `topics` | topics per subject (FK subject_id) |
| `questions` | question_text, option_a..d, correct_option enum(A-D), explanation, is_active, FK topic_id |
| `exam_attempts` | one row per practice/mock submission (score, submitted_at) |
| `attempt_answers` | per-question answers for each attempt (FKs to attempts and questions) |
| `settings` | key/value configuration: mock_time_limit, mock_max_questions |
| `vw_performance_records` | reporting view over attempts |

## Backup / Restore
Backup:  `C:\xampp\mysql\bin\mysqldump.exe -u root --databases smu_exit_exam > database\smu_exit_exam_dump.sql`
Restore: `C:\xampp\mysql\bin\mysql.exe -u root < database\smu_exit_exam_dump.sql`

## Troubleshooting
- **New styles not visible** -> hard refresh with Ctrl+F5 (browser caches CSS).
- **Role changed but no new menu** -> log out and log back in.
- **Page says administrators/instructors only** -> you are logged in with the
  wrong role for that URL.
- **Charts empty** -> complete at least one practice or mock attempt first.