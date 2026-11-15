#!/usr/bin/env bash
# Offline smoke test launcher (no MySQL/XAMPP required).
# Creates the sqlite fixture and serves the app on port 8080.
# Usage: bash tests/run_local_test.sh
set -e
cd "$(dirname "$0")/.."   # project root

php tests/local_test/make_db.php
echo "Serving on http://127.0.0.1:8080  (Ctrl+C to stop)"
SMU_DB_DRIVER=sqlite php -S 127.0.0.1:8080 -t public tests/local_test/router.php
