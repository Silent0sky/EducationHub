<?php
require_once __DIR__ . '/../config/database.php';

echo "PHP MySQL connection test\n";
if ($conn && $conn->ping()) {
    echo "Connected to MySQL server. Host: " . DB_HOST . " Database: " . DB_NAME . "\n";
} else {
    echo "Connection failed: ";
    if ($conn) echo $conn->connect_error; else echo "no connection object";
    echo "\n";
    exit(1);
}

// List tables
$result = $conn->query("SHOW TABLES");
if (!$result) {
    echo "SHOW TABLES failed: " . $conn->error . "\n";
    exit(1);
}

$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "Tables in " . DB_NAME . ":\n";
foreach ($tables as $t) echo " - $t\n";

// Simple counts for important tables if they exist
$checks = ['users','subjects','notes','questions','quiz_results'];
foreach ($checks as $table) {
    if (in_array($table, $tables)) {
        $r = $conn->query("SELECT COUNT(*) as c FROM $table");
        $c = $r ? $r->fetch_assoc()['c'] : 'error';
        echo "Table $table: $c rows\n";
    } else {
        echo "Table $table: NOT FOUND\n";
    }
}

// Close
$conn->close();
?>