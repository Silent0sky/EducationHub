<?php
// Simple smoke test runner for EducationHub (CLI)
// Usage: C:\xampp\php\php.exe tools\smoke_test.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/functions.php';

$out = [
    'time' => date('c'),
    'db_connected' => false,
    'tables' => [],
    'http' => [],
];

// DB connection
if (isset($conn) && $conn instanceof mysqli) {
    $out['db_connected'] = true;
    $tables = ['users','notes','subjects','quizzes','quiz_results','questions'];
    foreach ($tables as $t) {
        $res = $conn->query("SHOW TABLES LIKE '{$t}'");
        $out['tables'][$t] = ($res && $res->num_rows > 0) ? 'OK' : 'MISSING';
    }
} else {
    $out['db_error'] = 'No $conn mysqli connection available from config/functions.php';
}

// HTTP checks (requires local webserver running and accessible at localhost)
$pages = [
    '/EDUCATION_HUB/index.php',
    '/EDUCATION_HUB/dashboard.php',
    '/EDUCATION_HUB/auth/login.php',
    '/EDUCATION_HUB/search_notes.php',
    '/EDUCATION_HUB/quiz.php',
    '/EDUCATION_HUB/performance.php',
];

if (function_exists('curl_version')) {
    foreach ($pages as $p) {
        $url = 'http://localhost' . $p;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $err = $errno ? curl_error($ch) : null;
        curl_close($ch);
        $out['http'][$p] = ['code' => $http, 'error' => $err];
    }
} else {
    $out['http_error'] = 'cURL extension not available in CLI PHP';
}

echo json_encode($out, JSON_PRETTY_PRINT) . PHP_EOL;
