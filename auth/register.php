<?php
/* 
   ============================================
   USER REGISTRATION PAGE - auth/register.php
   ============================================
   Handles new user account creation:
   1. Validates form input (name, email, password)
   2. Checks for duplicate emails
   3. Hashes password securely with bcrypt
   4. Inserts new user into database
   5. Redirects to login page after success
*/

/* Include configuration and helper functions */
require_once '../config/functions.php';

/* VARIABLE INITIALIZATION - Error and success messages */
$error = '';
$success = '';

/* CHECK IF ALREADY LOGGED IN - Skip registration if user already authenticated */
if (isLoggedIn()) {
    /* User already has valid session - take to dashboard */
    redirect('../dashboard.php');
}

/* ============================================
   HANDLE FORM SUBMISSION (POST REQUEST)
   ============================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* COLLECT AND SANITIZE FORM INPUT */
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'student');

    /* ============================================
       VALIDATION CHAIN - Check all requirements
       ============================================ */
    
    /* STEP 1: Validate required fields are not empty */
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    }
    /* STEP 2: Validate email format using PHP filter */
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    }
    /* STEP 3: Validate password length (minimum 6 characters) */
    elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    }
    /* STEP 4: Verify passwords match */
    elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    }
    /* STEP 5: Validate role - only student/teacher can self-register */
    /* Note: Admin accounts are created manually by existing admins */
    elseif (!in_array($role, ['student', 'teacher'])) {
        $error = 'Invalid role selected';
    } else {
        /* ============================================
           DUPLICATE EMAIL CHECK
           ============================================ */
        /* Prepare statement to prevent SQL injection */
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        /* CHECK IF EMAIL EXISTS - If yes, show error */
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            /* ============================================
               PASSWORD HASHING - Secure bcrypt hashing
               ============================================ */
            /* Hash password securely using PASSWORD_DEFAULT (bcrypt) */
            /* Never store plain passwords in database */
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            /* ============================================
               INSERT NEW USER INTO DATABASE
               ============================================ */
            /* Prepare statement for safe parameterized query */
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

            /* EXECUTE INSERT - Add new user to database */
            if ($stmt->execute()) {
                /* REGISTRATION SUCCESSFUL - Set success message */
                $success = 'Registration successful! Redirecting to login...';
                /* AUTO-REDIRECT - Redirect to login page after 2 seconds */
                header("refresh:2;url=login.php");
            } else {
                /* REGISTRATION FAILED - Database error occurred */
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Register - Education Hub</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-page">
    <!-- MAIN REGISTRATION CONTAINER - Centered card layout -->
    <div class="auth-container">
        <div class="auth-card">
            <!-- HEADER SECTION - Logo and welcome message -->
            <div class="auth-header">
                <div class="logo">
                    <span class="logo-icon">📚</span>
                    <h1>Education Hub</h1>
                </div>
                <p>Create your account</p>
            </div>

            <!-- ERROR AND SUCCESS ALERTS -->
            <?php if ($error): ?>
                <?= showAlert($error, 'error') ?>
            <?php endif; ?>
            <?php if ($success): ?>
                <?= showAlert($success, 'success') ?>
            <?php endif; ?>

            <!-- REGISTRATION FORM - Sends data via POST to this same page -->
            <form method="POST" class="auth-form">
                <!-- FULL NAME INPUT - Required, used for display and initials -->
                <div class="form-group">
                    <label for="name">👤 Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <!-- EMAIL INPUT - Required, checked for duplicates -->
                <div class="form-group">
                    <label for="email">📧 Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <!-- ROLE SELECTOR - Dropdown for Student or Teacher selection -->
                <!-- Admin accounts must be created by existing admins -->
                <div class="form-group">
                    <label for="role">🎓 I am a</label>
                    <select id="role" name="role" required>
                        <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>👨‍🎓 Student</option>
                        <option value="teacher" <?= ($_POST['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>👩‍🏫 Teacher</option>
                    </select>
                </div>

                <!-- PASSWORD INPUT - Required, must be 6+ characters -->
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
                </div>

                <!-- CONFIRM PASSWORD INPUT - Must match password field -->
                <div class="form-group">
                    <label for="confirm_password">🔒 Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                <!-- SUBMIT BUTTON - Send form to server -->
                <button type="submit" class="btn btn-primary btn-block">✅ Create Account</button>
            </form>

            <!-- FOOTER SECTION - Link to login for existing users -->
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</body>
</html>
