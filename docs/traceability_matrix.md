# Traceability Matrix — SMU Exit Exam Preparation & Progress Tracker

Maps every functional requirement to the code that implements it and to the
test case that verifies it (see `test_report.md`). Requirement IDs are
sequential here; re-number them to match the proposal document if its
numbering differs.

| ID | Requirement | Implemented In | Verified By | Status |
|----|-------------|----------------|-------------|--------|
| FR-01 | Student self-registration with hashed passwords | AuthController, views/auth (register), User model | T-01 | Complete |
| FR-02 | Single login form for all roles with role-based redirect | AuthController (redirectByRole: admin/admin, instructor/instructor, student/student) | T-02 | Complete |
| FR-03 | Session security: ID regeneration on login, "keep me signed in" cookie | AuthController | T-02 | Complete |
| FR-04 | Practice mode with instant feedback and explanations | PracticeController, Question model, exam.js | T-03 | Complete |
| FR-05 | Mock exam: timed, randomized questions, auto-submit on expiry | MockController (cfgTimeLimit/cfgMaxQuestions), exam.js | T-04, T-05 | Complete |
| FR-06 | Server-side validation of submitted answers (no trusting the client) | MockController, ExamAttempt model | T-04 | Complete |
| FR-07 | Attempt history and score persistence | ExamAttempt model, exam_attempts + attempt_answers tables | T-06 | Complete |
| FR-08 | Student progress tracking: score history chart | Progress model (getScoreHistory), ProgressController, charts.js | T-06 | Complete |
| FR-09 | Progress: subject breakdown chart (incl. subjects with no attempts) | Progress model (getSubjectBreakdown) | T-06 | Complete |
| FR-10 | Progress: weakest-topic identification | Progress model (getTopicStats) | T-06 | Complete |
| FR-11 | Exam-readiness score based on recent attempts | Progress model (getReadiness: average of last 5 scores) | T-06 | Complete |
| FR-12 | Instructor reports: class overview (totals, averages) | Report model (classOverview), InstructorController::index | T-07 | Complete |
| FR-13 | Instructor reports: subject averages and weakest topics | Report model (subjectAverages, weakTopics) | T-07 | Complete |
| FR-14 | Instructor reports: per-student summary table | Report model (studentSummaries) | T-07 | Complete |
| FR-15 | Instructor trends: daily average line + subject comparison bar | Report model (dailyAverages), instructor-charts.js | T-08 | Complete |
| FR-16 | Instructor per-student drill-down with charts | InstructorController::student, views/instructor/student | T-09 | Complete |
| FR-17 | Instructor CSV export of student summaries | InstructorController::export | T-10 | Complete |
| FR-18 | Admin user management: create, edit, deactivate/activate, delete | AdminController::users / user, Admin model | T-11, T-12 | Complete |
| FR-19 | Admin password reset for any account | AdminController::user (resetpass), Admin model (resetPassword) | T-11 | Complete |
| FR-20 | Admin subject / topic / question CRUD | AdminController::subjects / topics / questions / question | T-13 | Complete |
| FR-21 | Question bank filters, search and pagination | Admin model (searchQuestions) | T-13 | Complete |
| FR-22 | Exam settings: mock duration + question count configurable by admin | AdminController::settings, settings table | T-14 | Complete |
| FR-23 | Settings applied to the live mock exam with safe fallback | MockController (adminSetting helper, patched) | T-14 | Complete |
| FR-24 | Role-based access guards on instructor and admin modules | InstructorController::guard, AdminController::guard | T-15, T-16 | Complete |

## Non-functional requirements

| ID | Requirement | How it is met | Status |
|----|-------------|---------------|--------|
| NFR-01 | Security: no SQL injection | PDO prepared statements in every model query | T-17 (code review) |
| NFR-02 | Security: password storage | password_hash / password_verify (bcrypt) | T-01, T-11 |
| NFR-03 | Security: XSS protection | htmlspecialchars escaping throughout views | T-18 (code review) |
| NFR-04 | Data integrity | InnoDB foreign keys on topics, questions, exam_attempts, attempt_answers | DB health check |
| NFR-05 | Usability | Responsive green/gold UI, flash messages, confirm dialogs, print styles | T-02..T-14 |
| NFR-06 | Offline capability | Chart.js 4.4.1 stored locally in public/lib (no CDN dependency) | T-06 |
| NFR-07 | Configurability | settings table drives mock exam parameters without code changes | T-14 |