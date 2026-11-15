<?php
/**
 * Builds the SQLite test database (tests/local_test/smu_exit_exam.sqlite)
 * from schema-sqlite.sql + the shared MySQL seed file.
 *
 * Run:  php tests/local_test/make_db.php
 */
$base = dirname(__DIR__, 2); // project root
$dbFile = __DIR__ . '/smu_exit_exam.sqlite';

if (is_file($dbFile)) {
    unlink($dbFile);
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$schema = file_get_contents(__DIR__ . '/schema-sqlite.sql');
$pdo->exec($schema);

// The MySQL seed is plain INSERT statements + a USE line, which are
// portable to SQLite once the USE statement is stripped.
$seed = file_get_contents($base . '/database/seed_questions.sql');
$seed = preg_replace('/^USE\s+\w+;\s*$/mi', '', $seed);
$seed = preg_replace('/^--.*$/m', '', $seed);
$pdo->exec($seed);

// report
echo 'users:     ' . $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() . PHP_EOL;
echo 'subjects:  ' . $pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn() . PHP_EOL;
echo 'topics:    ' . $pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn() . PHP_EOL;
echo 'questions: ' . $pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn() . PHP_EOL;
echo 'settings:  ' . $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn() . PHP_EOL;
echo 'sqlite db ready at ' . $dbFile . PHP_EOL;
