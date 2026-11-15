# Offline test profile

The documented environment is XAMPP + MySQL (`database/schema.sql` +
`database/seed_questions.sql`). This folder adds an optional **SQLite mirror** of
the same schema so the system can be demonstrated on a machine that has PHP but
no MySQL server — same code, only the PDO driver changes
(`SMU_DB_DRIVER=sqlite`).

## Files

| File | Purpose |
|---|---|
| `schema-sqlite.sql` | SQLite translation of `database/schema.sql` |
| `make_db.php` | builds `smu_exit_exam.sqlite` from the schema + the shared seed |
| `router.php` | router script for `php -S` (serves assets, forwards the rest to the front controller) |
| `smu_exit_exam.sqlite` | generated on demand (not committed) |

## Run

```bash
bash tests/run_local_test.sh
# or manually:
php tests/local_test/make_db.php
SMU_DB_DRIVER=sqlite php -S 127.0.0.1:8080 -t public tests/local_test/router.php
```

Open http://127.0.0.1:8080 and sign in with the seeded accounts
(Admin `ADMIN001 / Admin@123`, Instructor `STAFF018 / Staff@123`,
Students `SMU/CS/1234 / Student@123` …).
