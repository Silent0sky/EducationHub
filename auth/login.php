<?php
/* 
   ============================================
   LOGIN PAGE - auth/login.php
   ============================================
   Handles user authentication:
   1. Displays login form
   2. Validates email & password
   3. Sets session variables for authenticated users
   4. Redirects to dashboard or admin area
*/

/* Include configuration and helper functions */
require_once '../config/functions.php';

/* VARIABLE INITIALIZATION - Set default error message to empty */
$error = '';

/* CHECK IF ALREADY LOGGED IN - Prevent re-login */
if (isLoggedIn()) {
    /* User already has valid session - redirect to dashboard */
    redirect('../dashboard.php');
}

/* ============================================
   HANDLE FORM SUBMISSION (POST REQUEST)
   ============================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* SANITIZE USER INPUT - Clean email and get password */
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /* VALIDATE INPUT - Check if both fields are filled */
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        /* PREPARE STATEMENT - Prevent SQL injection */
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        /* DATABASE LOOKUP - Check if email exists in users table */
        if ($result->num_rows === 1) {
            /* USER FOUND - Fetch user record */
            $user = $result->fetch_assoc();

            /* PASSWORD VERIFICATION - Compare hashed password using bcrypt */
            if (password_verify($password, $user['password'])) {
                /* LOGIN SUCCESSFUL - Create session variables */
                $_SESSION['user_id'] = $user['id'];           // User ID for database queries
                $_SESSION['user_name'] = $user['name'];       // User name for display
                $_SESSION['user_email'] = $user['email'];     // Email for reference
                $_SESSION['user_role'] = $user['role'];       // Role for permission checks

                /* ROLE-BASED REDIRECT - Send user to appropriate dashboard */
                if ($user['role'] === 'admin') {
                    /* ADMIN USER - Redirect to admin dashboard */
                    redirect('../admin/dashboard.php');
                } else {
                    /* STUDENT/TEACHER - Redirect to main dashboard */
                    redirect('../dashboard.php');
                }
            } else {
                /* WRONG PASSWORD - Set error message */
                $error = 'Invalid email or password';
            }
        } else {
            /* USER NOT FOUND - Set error message (generic to prevent user enumeration) */
            $error = 'Invalid email or password';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Education Hub</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-page">
    <!-- MAIN LOGIN CONTAINER - Centered card layout -->
    <div class="auth-container">
        <div class="auth-card">
            <!-- HEADER SECTION - Logo and welcome message -->
            <div class="auth-header">
                <div class="logo">
                    <span class="logo-icon">📚</span>
                    <h1>Education Hub</h1>
                </div>
                <p>Sign in to your account</p>
            </div>

            <!-- ERROR ALERT - Display if login fails -->
            <?php if ($error): ?>
                <?= showAlert($error, 'error') ?>
            <?php endif; ?>

            <!-- LOGIN FORM - Sends credentials via POST to this same page -->
            <form method="POST" class="auth-form">
                <!-- EMAIL INPUT FIELD - Required, validated server-side -->
                <div class="form-group">
                    <label for="email">📧 Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <!-- PASSWORD INPUT FIELD - Hidden, not stored in HTML after POST -->
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required
                           value="">
                </div>

                <!-- SUBMIT BUTTON - Sends form to server -->
                <button type="submit" class="btn btn-primary btn-block">🔑 Sign In</button>
            </form>

            <!-- FOOTER SECTION - Link to registration for new users -->
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up</a></p>
            </div>
        </div>
    </div>
</body>
</html>
