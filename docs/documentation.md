# Technical Documentation
## Web-Based Exit Exam Preparation and Progress Tracker
### St. Mary's University — Department of Computer Science (Senior Project)

---

## 1. Introduction

This document describes the implementation of the **Web-Based Exit Exam Preparation
and Progress Tracker**, the system designed in the senior project document for St.
Mary's University (SMU). Following the introduction of the national university exit
examination by the Ethiopian Ministry of Education (July 2023), undergraduate
students must pass the exam to graduate. The project replaces scattered, untracked
study habits with a centralized, performance-driven platform that provides:

1. **Standardized evaluation** — a validated question bank organized by subject and
   topic, with two testing experiences (Practice Mode and Mock Exam Mode).
2. **Progress visualization** — every attempt is permanently recorded and transformed
   into score history, subject/topic diagnostics and a readiness metric.
3. **Institutional feedback** — aggregated class-level analytics that let instructors
   identify commonly difficult topics and schedule data-backed tutorials.

The implementation follows the design of the project document: the **MVC
architectural pattern**, the **five subsystems**, the documented **business rules**,
**access-control matrix**, **database design** and **non-functional requirements**.

---

## 2. System architecture

### 2.1 Three-tier MVC

| Layer | Components | Location |
|---|---|---|
| Presentation | HTML5 templates (glassmorphism theme), CSS3, vanilla JS | `app/views`, `public/assets` |
| Application | Controllers → Services → Models | `app/controllers`, `app/services`, `app/models` |
| Data | MySQL through a single PDO connection | `app/core/Database.php`, `database/*.sql` |

**Request flow (global software control):** every request enters
`public/index.php` → `App\Core\Session::start()` (idle-timeout check, CSRF token) →
`App\Core\App` resolves `/controller/action/param` → access-control gate →
controller action → services/models → view render (or JSON for AJAX endpoints).

### 2.2 Subsystem decomposition (per the design document)

| Subsystem | Implementation |
|---|---|
| User Management | `AuthController`, `UserService`, `User` model, `Session`, `Auth` |
| Question Bank Management | `QuestionBankController`, `QuestionBankService`, `Subject`/`Topic`/`Question` models |
| Examination (Practice Engine) | `PracticeController`, `MockExamController`, `TrackerService`, `ExamSession` model |
| Progress Tracking | `ProgressController`, `ScoreService`, `ExamSession`/`Result` models |
| Reporting & Analytics | `ReportingController`, `AdminController` (reports), `ReportService` |

### 2.3 Routing

Pretty PATH_INFO URLs work everywhere (XAMPP sub-folder, root, `php -S`):

```
/index.php/auth/login          GET   login page
/index.php/auth/dologin        POST  authenticate (CSRF-protected)
/index.php/auth/register       GET   student registration
/index.php/auth/doregister     POST  create student account
/index.php/auth/logout         GET   destroy session
/index.php/home                GET   role-based dashboard redirect
/index.php/practice/index      GET   choose subject/topic
/index.php/practice/start      POST  create practice session
/index.php/practice/question   GET   AJAX: current question (answers hidden)
/index.php/practice/answer     POST  AJAX: submit answer, instant feedback
/index.php/practice/submit     POST  finalize session → results
/index.php/mock/index          GET   mock exam rules
/index.php/mock/start          POST  create timed session
/index.php/mock/submit         POST  evaluate answers (auto-submit supported)
/index.php/progress/dashboard  GET   student dashboard (KPIs + charts)
/index.php/progress/index      GET   full progress analytics + goal tool
/index.php/progress/goal       POST  save readiness target
/index.php/progress/results/5  GET   results & feedback of attempt #5
/index.php/progress/history    GET   paginated attempt history
/index.php/progress/profile    GET   profile, password change, delete account
/index.php/reporting/dashboard GET   instructor KPIs + charts
/index.php/reporting/students  GET   per-student performance
/index.php/reporting/topics    GET   weak topics detail
/index.php/reporting/questions GET   read-only question review
/index.php/reporting/export    GET   CSV export
/index.php/admin/...           GET   users / subjects / topics / config
/index.php/questionbank/...    GET   question CRUD (index/create/store/edit/
                                     update/delete/togglevalidate)
```

---

## 3. Database design

Relational database (per the logical ER design): every column typed, primary keys
`AUTO_INCREMENT`, foreign keys enforce referential integrity, indexes on hot paths.

| Table | Purpose | Key columns |
|---|---|---|
| `users` | one row per account, all roles | `id_number` UNIQUE (login credential), `password_hash` (bcrypt), `role` ENUM(student,instructor,admin), `status` ENUM(active,deactivated), `last_login` |
| `subjects` | exit-exam subject areas | `name` UNIQUE, `description` |
| `topics` | topics of a subject | FK `subject_id` ON DELETE CASCADE, UNIQUE(subject,name) |
| `questions` | validated bank | FK `topic_id`, `option_a..d`, `correct_option` ENUM(A–D), `explanation`, `difficulty`, `is_validated` (pending questions are never served), `image_path` (room for media), FK `created_by` |
| `exam_sessions` | one attempt = one row | FK `user_id`, `mode` ENUM(practice,mock), `subject_id`/`topic_id` (NULL for full mock), `total_questions`, `correct_count`, `score_percent`, `time_limit_minutes`, `status` ENUM(in_progress,completed,expired) |
| `session_answers` | per-question audit trail | FK `session_id` CASCADE, FK `question_id` **RESTRICT** (history protection), `selected_option` (NULL = skipped), `is_correct`, UNIQUE(session,question) |
| `user_goals` | goal-setting tool | PK `user_id`, `target_percent` |
| `settings` | exam configuration | `mock_question_count`=50, `mock_time_limit_minutes`=60, `practice_question_count`=10, `session_timeout_minutes`=30, `readiness_target`=80 |

Deleting a subject cascades its topics and questions; a question that already has
recorded answers cannot be deleted (RESTRICT + application-level guard) so that
historical analytics remain intact.

---

## 4. Implemented business rules

1. **Registration** — students sign up with Name + Student ID + password only.
   The Student ID is the unique login credential; duplicates are rejected
   (use-case alternative course). Password minimum length: 8.
2. **Instructor accounts** are created and managed **only** by the Admin — no
   public registration (access-control matrix).
3. **Admin account** is seeded during installation (`ADMIN001`, bcrypt hash in the
   seed file).
4. **Two testing environments** — Practice Mode (untimed, instant feedback,
   randomized questions) and Mock Exam Mode (timed, deferred feedback, auto-submit
   at the deadline, skipped = incorrect), exactly as in the practice-session
   control flow table of the document.
5. **Scores are calculated automatically** on submission; every attempt and answer
   is stored permanently.
6. **Validation workflow** — only `is_validated = 1` questions are served to
   students; the Admin can toggle validation (state chart: question management).
7. **Session timeout** — automatic logout after 30 minutes of inactivity
   (configurable in Exam Configuration), protecting accounts on shared computers.

---

## 5. Analytics and formulas

- **Score %** of an attempt = `correct / total × 100` (0 when total = 0 — no
  division by zero).
- **Readiness metric** = `round(0.65 × avg(last 5 completed attempts) +
  0.35 × avg(all completed attempts))`; **0** when the student has no completed
  attempts (explicit boundary condition from the design document).
- **Trend** = difference between the two most recent completed attempts.
- **Topic accuracy** = `SUM(correct)/COUNT(answers)` per topic across the
  student's completed attempts (student view) or the whole cohort (instructor
  view). Bands: < 50 % red, 50–74 % amber, ≥ 75 % green.
- **Weak topics (instructor)** ranked only for topics with ≥ 3 recorded answers,
  so single-sample topics cannot dominate the ranking.

---

## 6. Security (non-functional requirements → implementation)

| Requirement | Implementation |
|---|---|
| Password protection | `password_hash(PASSWORD_DEFAULT)` (bcrypt); `password_verify` at login |
| SQL injection prevention | PDO prepared statements everywhere (`Database::query/first/execute`), native prepares, no string-built values |
| XSS prevention | every dynamic output escaped via `View::e()` (htmlspecialchars) |
| Session security | `session_regenerate_id(true)` at login, HTTP-only default cookie, idle timeout, CSRF token verified on **all** POST requests |
| Authorization | role gate per controller (`requireRole`); guests → login, wrong role → **403** page; private app folders blocked by `.htaccess` |
| Data protection | students see only their own results (`belongsToUser` checks); instructor pages expose aggregated statistics only; admin actions are guarded |
| Error handling | friendly 403/404/500 pages; debug traces only when `APP_DEBUG=true` |

---

## 7. Boundary conditions (from the design document)

1. **0 % and 100 % scores** — the scoring formula and readiness metric handle both
   extremes; `readiness` returns 0 (not an error) for students with zero attempts.
2. **Large question banks / long history** — question retrieval is randomized with
   LIMIT; attempt history, user lists and question lists are **paginated**
   (10 rows/page, `PAGINATION_PER_PAGE`).
3. **Concurrent submissions** — sessions are per-user (PHP sessions are isolated);
   answer storage uses a UNIQUE(session,question) upsert so double submissions
   cannot corrupt records; a mock exam submitted after its deadline is marked
   `expired` but still evaluated (no data loss).
4. **Empty content** — a topic without validated questions produces the documented
   notification instead of an empty exam.

---

## 8. User guide (per role)

### Student
1. Register (or use a demo account) → sign in → dashboard shows readiness,
   target, attempts and weak topics.
2. **Practice Mode**: choose subject → topic → Start. Answer each question — the
   correct option and an explanation appear immediately; Skip moves on; Finish
   stores and shows the results page.
3. **Mock Exam Mode**: Start → all questions on one page with a live countdown →
   Submit (or let the timer auto-submit) → score with full review.
4. **My Progress**: history chart, subject averages, topic diagnostics; set your
   readiness target with the goal tool.
5. **Results & History**: paginated permanent record; open any attempt to review
   every answer and explanation.
6. **My Profile**: change name/password, inspect recent activity, delete the
   account with password confirmation.

### Instructor
- **Performance Dashboard**: active students, class average, attempts, weekly
  trend, class average by subject, and the topics needing tutorial intervention.
- **Student Performance** lists each student's average, weakest subject and last
  activity; **Export CSV** downloads the table.
- **Weak Topics** ranks cohort-accuracy per topic with a recommended action.
- **Question Review** is read-only and shows how the cohort performs per question.

### Administrator
- **Manage Users**: create instructor accounts, search/filter, deactivate or
  activate, reset passwords, delete (admin account and self are protected).
- **Subjects / Topics**: full CRUD; deletion is refused while questions exist.
- **Question Bank**: filter by subject/topic/text, add, edit, validate/un-validate,
  delete (guarded when answers exist).
- **Exam Configuration**: mock question count & time limit, practice length,
  session timeout, readiness target — applied to every new session.

---

## 9. Testing

`tests/test_cases.md` maps each functional requirement of the document to a test
case with the expected result; the system passes the complete checklist. The
offline profile (`tests/run_local_test.sh`, SQLite mirror of the schema) lets the
system be demonstrated without MySQL — same code path, driver swapped in
`app/core/Database.php` only.

## 10. Limitations (as documented in the project)

- Web-only: requires connectivity; no offline mode; no native mobile app
  (the interface is mobile responsive instead).
- Content is limited to what the Admin enters (the seeded bank is representative
  of the five core CS subjects).
- Standalone system: no integration with the university's student information
  or grading systems.

## 11. Future work

LMS integration, question media (the schema already reserves `image_path`),
instructor-authored questions, e-mail notifications (PHPMailer), and
standard-setting per program.
