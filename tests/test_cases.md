# Test Cases — Verification & Validation Checklist

Each functional requirement from Chapter II of the project document is mapped to a
test case. All cases below were executed against the running system and pass.

| # | Requirement (document) | Test case | Expected result | Status |
|---|---|---|---|---|
| 1 | Three roles with permissions (FR) | Login as student / instructor / admin and compare sidebars | Each role sees only its own navigation | ✅ |
| 2 | Secure login gateway (FR) | Login with `SMU/CS/1234` + `Student@123` | Redirect to role dashboard | ✅ |
| 3 | Invalid credentials | Login with wrong password | "Invalid ID number or password" message | ✅ |
| 4 | Deactivated accounts | Deactivate a user in admin, try to log in | "account has been deactivated" message | ✅ |
| 5 | Student self-registration (business rule) | Register with Name + ID + password | Account created; success notice on login page | ✅ |
| 6 | Duplicate Student ID rejected | Register again with the same ID | "already registered" error, no duplicate row | ✅ |
| 7 | Instructors created by admin only | Admin → Manage Users → Create instructor; check register page has no instructor option | Account created; registration is student-only | ✅ |
| 8 | Subject/topic question banks (FR) | Practice → choose subject → topic | Topics with available-question counts listed | ✅ |
| 9 | Empty topic notification | Open a topic with 0 validated questions | "No questions are available for this topic yet" | ✅ |
| 10 | Randomized retrieval | Start the same practice twice | Question order differs | ✅ |
| 11 | Immediate feedback (FR) | Answer a practice question via AJAX | Correct/incorrect marking + explanation shown instantly | ✅ |
| 12 | Answers never leak to client | Inspect the `practice/question` JSON | No `correct_option`/`explanation` fields present | ✅ |
| 13 | Mock exam timed (FR) | Start mock; observe countdown | Countdown runs; danger colour in last minute; auto-submit at 0 | ✅ |
| 14 | Skipped = incorrect | Submit a mock with unanswered questions | Review shows "Skipped"; scored 0 for those | ✅ |
| 15 | Auto score calculation (FR) | Submit any session | `score_percent` matches correct/total | ✅ |
| 16 | Permanent attempt records (FR) | Complete a session, re-open Results & History | Attempt listed with score and review | ✅ |
| 17 | Results detail (use case) | Open results of an attempt | Score, %, correct, incorrect, explanations, topic breakdown | ✅ |
| 18 | Progress summary (FR) | Open My Progress after attempts | History chart, subject averages, topic diagnostics | ✅ |
| 19 | Readiness metric boundaries | (a) new student, (b) score 0, (c) score 100 | (a) 0% without error; (b) 0%; (c) 100% | ✅ |
| 20 | Weak topics highlighted (FR) | Check dashboard "weak topics" card and progress page | Topics < 50% flagged red | ✅ |
| 21 | Goal setting | Save target 85 in My Progress | Target stored and used on dashboard | ✅ |
| 22 | Instructor aggregated reports (FR) | Open Performance Dashboard after students practise | Class avg by subject, weekly trend, weak topics | ✅ |
| 23 | Instructor weak-topic threshold | Topic with < 3 answers | Not ranked (avoids single-sample noise) | ✅ |
| 24 | Instructor cannot see raw data of others | Check instructor pages | Aggregated statistics only; no answer-level student data | ✅ |
| 25 | Admin manages users (FR) | Create instructor / deactivate / activate / reset password / delete | Each action applies and is confirmed by a flash message | ✅ |
| 26 | Admin manages subjects/topics/questions (FR) | Add, edit, delete each entity | CRUD works; flash confirmations | ✅ |
| 27 | Missing fields prevent saving (use case) | Submit the question form without options | Error list rendered; nothing saved | ✅ |
| 28 | Validation workflow | Toggle "Un-validate" on a question, start a practice on that topic | Pending question is not served to students | ✅ |
| 29 | History protection | Delete a question that has answers / subject with questions | Refused with explanation flash | ✅ |
| 30 | Exam configuration (FR) | Change mock count to 20 → start a mock | Session has 20 questions, 30 min if configured | ✅ |
| 31 | Session timeout (NFR security) | Idle 31 minutes (or set timeout 1 min and wait) | Next request redirects to login with expiry notice | ✅ |
| 32 | CSRF protection | POST any form without/with wrong `_csrf` | 403 page | ✅ |
| 33 | SQL injection | `id_number=' OR 1=1 --` at login | Login fails normally (prepared statements) | ✅ |
| 34 | XSS escaping | Register a name containing `<script>` | Name rendered escaped everywhere | ✅ |
| 35 | RBAC enforcement (access-control flow) | Student opens `/admin/dashboard`; guest opens `/progress/dashboard` | 403 page; redirect to login | ✅ |
| 36 | 404 handling | Open `/index.php/nonsense/page` | Friendly "Page not found" | ✅ |
| 37 | Mobile responsiveness (NFR) | Open at ≤ 600 px viewport | Sidebar collapses, cards stack, tables scroll | ✅ |
| 38 | Pagination (boundary) | Register >10 users / complete >10 attempts | Lists paginate at 10 rows/page | ✅ |
| 39 | Double submission protection | Submit the same mock twice | Second submit redirects to existing results; no duplicate answers (UNIQUE upsert) | ✅ |
| 40 | Logout & session destruction (FR) | Logout, press Back | Protected pages redirect to login | ✅ |

## Automated lint

```bash
find . -name "*.php" -exec php -l {} \;     # all files: No syntax errors
```
