<?php
/**
 * Simple migration runner for local development.
 * Usage: php tools/apply_migration.php database/migrations/001_create_quizzes_table.sql
 */

require_once __DIR__ . '/../config/database.php';

$migration = $argv[1] ?? 'database/migrations/001_create_quizzes_table.sql';

if (!file_exists(__DIR__ . '/../' . $migration)) {
    echo "Migration file not found: $migration\n";
    exit(1);
}

$sql = file_get_contents(__DIR__ . '/../' . $migration);
if (!$sql) {
    echo "Failed to read migration file: $migration\n";
    exit(1);
}

/* Execute SQL (may contain multiple statements) */
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Migration applied: $migration\n";
} else {
    echo "Migration failed: " . $conn->error . "\n";
    exit(1);
}

/* Verify table exists */
$res = $conn->query("SHOW TABLES LIKE 'quizzes'");
if ($res && $res->num_rows === 1) {
    echo "Verified: 'quizzes' table exists.\n";
    exit(0);
}

echo "Warning: 'quizzes' table not found after migration.\n";
exit(1);
