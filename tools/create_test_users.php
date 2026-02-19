<?php
/**
 * Create three test users (student, teacher, admin) with known passwords.
 * Usage: php tools/create_test_users.php
 */

require_once __DIR__ . '/../config/database.php';

$users = [
    ['name' => 'Test Student', 'email' => 'test_student@local.test', 'password' => 'TestPass123', 'role' => 'student'],
    ['name' => 'Test Teacher', 'email' => 'test_teacher@local.test', 'password' => 'TestPass123', 'role' => 'teacher'],
    ['name' => 'Test Admin',   'email' => 'test_admin@local.test',   'password' => 'TestPass123', 'role' => 'admin'],
];

foreach ($users as $u) {
    $email = $conn->real_escape_string($u['email']);
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check && $check->num_rows > 0) {
        echo "User already exists: {$u['email']}\n";
        continue;
    }

    $name = $conn->real_escape_string($u['name']);
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);
    $role = $conn->real_escape_string($u['role']);
    $sql = "INSERT INTO users (name, email, password, role, created_at) VALUES ('$name', '$email', '$hash', '$role', NOW())";
    if ($conn->query($sql)) {
        echo "Created user: {$u['email']} ({$u['role']})\n";
    } else {
        echo "Failed to create user {$u['email']}: " . $conn->error . "\n";
    }
}

echo "\nCreated/verified test users. Credentials:\n";
foreach ($users as $u) {
    echo " - {$u['email']} / {$u['password']} ({$u['role']})\n";
}
