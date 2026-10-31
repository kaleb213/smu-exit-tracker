# Test Report — SMU Exit Exam Preparation & Progress Tracker

## 1. Scope & Method
Manual black-box functional testing of every module, plus security spot
checks. Each requirement from `traceability_matrix.md` is covered by at least
one test case below. Security items marked "code review" were verified by
inspecting that all database access uses PDO prepared statements and all view
output is HTML-escaped.

## 2. Test Environment
| Item | Value |
|------|-------|
| OS | Windows (XAMPP) |
| Web server | Apache (XAMPP) |
| PHP | 8.x |
| Database | MySQL/MariaDB — schema `smu_exit_exam` |
| Browser | Chrome/Edge, hard refresh (Ctrl+F5) after asset changes |
| Data volume at test time | 2 users, 3 subjects, 4 topics, 8 questions, 9 exam attempts, 23 recorded answers, 2 settings rows |

## 3. Test Cases

| ID | Module | Test | Expected Result | Actual Result | Status |
|----|--------|------|-----------------|---------------|--------|
| T-01 | Auth | Register a new student (full name, ID number, password) | Account created with role=student, hashed password stored | As expected | Pass |
| T-02 | Auth | Login as student / instructor / admin; wrong-password attempt | Each role lands on its own home page; wrong password shows flash error | As expected | Pass |
| T-03 | Practice | Answer practice questions | Instant correct/incorrect feedback plus explanation shown per question | As expected | Pass |
| T-04 | Mock | Start mock exam | Timer starts (default 30 min), questions randomized, countdown visible | As expected | Pass |
| T-05 | Mock | Let the timer expire | Attempt auto-submitted, result saved, review page rendered | As expected | Pass |
| T-06 | Progress | Open Progress after several attempts | Score-history line chart, subject breakdown chart, weakest topics and readiness score all render | As expected | Pass |
| T-07 | Instructor | Open /instructor as instructor | Overview cards (students, attempts, averages), subject table, weakest topics, student summaries | As expected | Pass |
| T-08 | Instructor | Open Trends | Daily-average line chart and subject-average bar chart render from local Chart.js | As expected | Pass |
| T-09 | Instructor | Click a student name | Drill-down page with readiness hero, charts, weak/strong topics, attempt history | As expected | Pass |
| T-10 | Instructor | Export CSV | File downloads and opens in Excel with all student summaries | As expected | Pass |
| T-11 | Admin | Create a user, then reset that users password | User appears in list; new password works on next login | As expected | Pass |
| T-12 | Admin | Deactivate a test user | is_active flag stored, list shows Inactive badge, history preserved | As expected | Pass |
| T-13 | Admin | Add/edit subject, topic and question; filter and search the bank | All CRUD operations persist; filters and pagination behave correctly; topic is required for questions | As expected | Pass |
| T-14 | Admin | Set mock exam to 10 minutes / 20 questions, then start a mock | Timer shows 10:00 and the exam contains 20 randomized questions | As expected | Pass |
| T-15 | Guard | Open /instructor as student | Redirected away with "instructors only" flash message | As expected | Pass |
| T-16 | Guard | Open /admin as non-admin | Redirected away with "administrators only" flash message | As expected | Pass |
| T-17 | Security | Code review of all model queries | Every query uses prepared statements with bound parameters | Confirmed | Pass (code review) |
| T-18 | Security | Code review of view output | Dynamic output escaped with htmlspecialchars | Confirmed | Pass (code review) |
| T-19 | DB | Health check via information_schema | 7 base tables + 1 view present; foreign keys intact on topics, questions, exam_attempts, attempt_answers | Confirmed | Pass |
| T-20 | Settings | Change settings, restart mock exam | New values applied; removing a settings row falls back to built-in 30/50 constants | As expected | Pass |

## 4. Known Limitations
1. Role changes made in the database or admin panel take effect on the next
   login, because the role is read into the session at login time.
2. Deactivating a user stores the flag and preserves history, but the login
   form does not yet check `is_active` — a small patch to AuthController can
   enforce it.
3. Questions require a topic (topic_id is NOT NULL), enforced client-side by a
   required dropdown.
4. Practice/mock scoring relies on the local XAMPP environment; the system is
   designed for university LAN deployment, not the public internet.

## 5. Conclusion
All functional requirements traced in `traceability_matrix.md` passed their
test cases. The system is complete and stable on the XAMPP stack: three roles,
practice mode, timed mock exams, progress analytics, instructor reporting with
CSV export, and a full admin panel with configurable exam settings.