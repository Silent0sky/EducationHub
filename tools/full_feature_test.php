<?php
/**
 * FULL FEATURE TEST - Automated headless tests for EducationHub
 * Tests login flow, dashboards, and key features for Student/Teacher/Admin roles
 * 
 * Usage: C:\xampp\php\php.exe tools\full_feature_test.php
 * 
 * Requirements:
 * - Apache/XAMPP running with localhost:80 available
 * - PHP cURL extension enabled
 * - Valid test users in database (student, teacher, admin)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test data - actual test users in database with password: test123
$TEST_USERS = [
    'student' => ['email' => 'student@gmail.com', 'password' => 'test123'],
    'teacher' => ['email' => 'teacher@gmail.com', 'password' => 'test123'],
    'admin' => ['email' => 'admin@gmail.com', 'password' => 'test123'],
];

$TEST_PAGES = [
    'dashboard.php',
    'search_notes.php',
    'quiz.php',
    'performance.php',
];

$ADMIN_PAGES = [
    'admin/users.php',
    'admin/subjects.php',
];

$TEACHER_PAGES = [
    'upload_notes.php',
    'manage_questions.php',
    'my_uploads.php',
];

$results = [
    'timestamp' => date('c'),
    'host' => 'http://localhost',
    'tests' => [],
    'summary' => ['passed' => 0, 'failed' => 0, 'errors' => []]
];

// ============================================
// TEST FUNCTION: HTTP Request with cookies
// ============================================
function test_page($method, $url, $data = null, $cookies = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Add cookies if provided (for authenticated requests)
    if ($cookies) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }
    
    // POST request with form data
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_errno($ch) ? curl_error($ch) : null;
    
    // Extract Set-Cookie headers to get session cookie
    $cookies_out = [];
    if (preg_match_all('/Set-Cookie: ([^;\r\n]+)/i', $response, $m)) {
        foreach ($m[1] as $cookie) {
            $cookies_out[] = $cookie;
        }
    }
    
    curl_close($ch);
    
    return [
        'code' => $http_code,
        'error' => $error,
        'cookies' => $cookies_out,
        'response_length' => strlen($response)
    ];
}

// ============================================
// MAIN TEST FLOW
// ============================================

if (!function_exists('curl_version')) {
    $results['summary']['errors'][] = 'cURL extension not available in CLI PHP';
    echo json_encode($results, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(1);
}

// Test each role
foreach ($TEST_USERS as $role => $creds) {
    $role_result = [
        'role' => $role,
        'login' => null,
        'dashboard' => null,
        'pages' => [],
        'admin_pages' => [],
        'teacher_pages' => []
    ];
    
    echo "[TEST] Testing $role login...\n";
    
    // Step 1: Login
    $login_result = test_page('POST', 'http://localhost/EDUCATION_HUB/auth/login.php', $creds);
    $role_result['login'] = $login_result;
    
    if ($login_result['code'] != 302) {
        $results['summary']['failed']++;
        $results['summary']['errors'][] = "Login failed for $role (HTTP {$login_result['code']})";
        $results['tests'][] = $role_result;
        continue;
    }
    
    // Extract session cookie from login response
    $session_cookie = null;
    foreach ($login_result['cookies'] as $cookie) {
        if (strpos($cookie, 'PHPSESSID') !== false) {
            $session_cookie = $cookie;
            break;
        }
    }
    
    if (!$session_cookie) {
        $results['summary']['errors'][] = "No session cookie found after login for $role";
        $results['tests'][] = $role_result;
        continue;
    }
    
    // Step 2: Access main dashboard
    echo "[TEST] Testing $role dashboard...\n";
    $dash_result = test_page('GET', 'http://localhost/EDUCATION_HUB/dashboard.php', null, $session_cookie);
    $role_result['dashboard'] = $dash_result;
    
    if ($dash_result['code'] != 200) {
        $results['summary']['errors'][] = "Dashboard failed for $role (HTTP {$dash_result['code']})";
    }
    
    // Step 3: Test common pages
    foreach ($TEST_PAGES as $page) {
        $page_result = test_page('GET', "http://localhost/EDUCATION_HUB/$page", null, $session_cookie);
        $role_result['pages'][$page] = ['code' => $page_result['code']];
        
        // Alert if not 200 or 302
        if ($page_result['code'] != 200 && $page_result['code'] != 302) {
            $results['summary']['errors'][] = "$role accessing $page returned HTTP {$page_result['code']}";
        }
    }
    
    // Step 4: Test role-specific pages
    if ($role === 'admin') {
        foreach ($ADMIN_PAGES as $page) {
            $page_result = test_page('GET', "http://localhost/EDUCATION_HUB/$page", null, $session_cookie);
            $role_result['admin_pages'][$page] = ['code' => $page_result['code']];
            
            if ($page_result['code'] != 200 && $page_result['code'] != 302) {
                $results['summary']['errors'][] = "Admin accessing $page returned HTTP {$page_result['code']}";
            }
        }
    } elseif ($role === 'teacher') {
        foreach ($TEACHER_PAGES as $page) {
            $page_result = test_page('GET', "http://localhost/EDUCATION_HUB/$page", null, $session_cookie);
            $role_result['teacher_pages'][$page] = ['code' => $page_result['code']];
            
            if ($page_result['code'] != 200 && $page_result['code'] != 302) {
                $results['summary']['errors'][] = "Teacher accessing $page returned HTTP {$page_result['code']}";
            }
        }
    }
    
    $results['summary']['passed']++;
    $results['tests'][] = $role_result;
}

// Output results
echo "\n=== TEST SUMMARY ===\n";
echo "Passed: " . $results['summary']['passed'] . "\n";
echo "Failed: " . $results['summary']['failed'] . "\n";

if (!empty($results['summary']['errors'])) {
    echo "\nErrors:\n";
    foreach ($results['summary']['errors'] as $error) {
        echo "  - $error\n";
    }
}

echo "\n" . json_encode($results, JSON_PRETTY_PRINT) . PHP_EOL;
?>
