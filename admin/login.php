<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];

if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

if (is_post()) {
    require_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    } else {
        if (is_locked_out($pdo, 'ADMIN', null, $username)) {
            $errors[] = 'Too many failed login attempts. Please try again later.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($password, $admin['password'])) {
                record_login_attempt($pdo, 'ADMIN', false, null, $username);
                $errors[] = 'Invalid credentials.';
                usleep(500000);
            } else {
                session_regenerate_id(true);

                $_SESSION['user_type'] = 'ADMIN';
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['fullname'];
                $_SESSION['admin_role'] = $admin['role'];

                record_login_attempt($pdo, 'ADMIN', true, null, $username);

                redirect('dashboard.php');
            }
        }
    }
}

$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR / Admin Login</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/admin_style.css">
</head>
<body class="auth-page">
    <div class="auth-layout">
        <div class="auth-panel auth-panel--left">
            <div class="auth-brand-wrap">
                <div class="auth-brand-badge">HR Management System</div>
                <h1 class="auth-main-title">Welcome Back, Admin</h1>
                <p class="auth-main-subtitle">
                    Securely access the admin panel to manage employees, credentials, and registration requests.
                </p>

                <div class="auth-feature-list">
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon">👨‍💼</div>
                        <div>
                            <h5>Employee Management</h5>
                            <p>Monitor employee records and account status.</p>
                        </div>
                    </div>

                    <div class="auth-feature-item">
                        <div class="auth-feature-icon">📁</div>
                        <div>
                            <h5>Credential Records</h5>
                            <p>Access uploaded files and employee documents.</p>
                        </div>
                    </div>

                    <div class="auth-feature-item">
                        <div class="auth-feature-icon">📝</div>
                        <div>
                            <h5>Registration Review</h5>
                            <p>Approve or reject pending registration requests.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-panel auth-panel--right">
            <div class="auth-card auth-card--login">

                <a href="../index.php" class="auth-back-btn">
                    ← Back to Home
                </a>
                <div class="auth-card-header">
                    <div class="auth-card-icon">🔐</div>
                    <h2>HR / Admin Login</h2>
                    <p>Enter your credentials to continue to the admin dashboard.</p>
                </div>

                <?php if ($errors): ?>
                    <div class="auth-alert auth-alert--danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?= e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

                    <div class="auth-form-group">
                        <label class="auth-label">Username</label>
                        <input
                            type="text"
                            name="username"
                            class="auth-input"
                            value="<?= e(old('username')) ?>"
                            placeholder="Enter your username"
                            required
                        >
                    </div>

                    <div class="auth-form-group">
                        <label class="auth-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="auth-input"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <button type="submit" class="auth-submit-btn">Login to Admin Panel</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>