<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];

if (is_employee_logged_in()) {
    redirect('dashboard.php');
}

if (is_post()) {
    require_csrf();

    $employee_no = trim($_POST['employee_no'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($employee_no === '' || $password === '') {
        $errors[] = 'Employee No. and password are required.';
    } else {
        if (is_locked_out($pdo, 'EMPLOYEE', $employee_no, null)) {
            $errors[] = 'Too many failed login attempts. Please try again later.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_no = ? LIMIT 1");
            $stmt->execute([$employee_no]);
            $employee = $stmt->fetch();

            if (!$employee || !password_verify($password, $employee['password'])) {
                record_login_attempt($pdo, 'EMPLOYEE', false, $employee_no, null);
                $errors[] = 'Invalid credentials.';
                usleep(500000);
            } else {
                if ($employee['status'] !== 'APPROVED') {
                    record_login_attempt($pdo, 'EMPLOYEE', false, $employee_no, null);
                    $errors[] = 'Your account is not yet approved by HR.';
                } elseif ((int)$employee['is_active'] !== 1) {
                    record_login_attempt($pdo, 'EMPLOYEE', false, $employee_no, null);
                    $errors[] = 'Your account is inactive.';
                } else {
                    session_regenerate_id(true);

                    $_SESSION['user_type'] = 'EMPLOYEE';
                    $_SESSION['employee_id'] = $employee['id'];
                    $_SESSION['employee_no'] = $employee['employee_no'];
                    $_SESSION['employee_name'] = full_name($employee);

                    record_login_attempt($pdo, 'EMPLOYEE', true, $employee_no, null);

                    redirect('dashboard.php');
                }
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
    <title>Employee Login</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/employee_style.css">
</head>
<body class="employee-auth-page">

<div class="employee-auth-wrapper">
    <div class="employee-auth-shell">

        <div class="employee-auth-left">
            <div class="auth-brand-badge">Employee Portal</div>
            <h1>Welcome Back</h1>
            <p>
                Access your employee account, manage your credentials,
                and keep your records updated through the portal.
            </p>

            <div class="auth-feature-list">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon">📄</span>
                    <div>
                        <strong>Credential Management</strong>
                        <p>Upload and view your official files anytime.</p>
                    </div>
                </div>

                <div class="auth-feature-item">
                    <span class="auth-feature-icon">🔒</span>
                    <div>
                        <strong>Secure Login</strong>
                        <p>Your account access is protected and monitored.</p>
                    </div>
                </div>

                <div class="auth-feature-item">
                    <span class="auth-feature-icon">⚡</span>
                    <div>
                        <strong>Fast Access</strong>
                        <p>Quickly open your dashboard and employee tools.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="employee-auth-right">
            <div class="employee-auth-card">
                <div class="auth-card-header">
                    <p class="auth-kicker">Sign in</p>
                    <h2>Employee Login</h2>
                    <span>Enter your employee credentials to continue.</span>
                </div>

                <?php if ($errors): ?>
                    <div class="custom-alert custom-alert-danger mb-4">
                        <div class="custom-alert-title">Login Error</div>
                        <?php foreach ($errors as $error): ?>
                            <div class="custom-alert-text"><?= e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">Employee No.</label>
                        <input
                            type="text"
                            name="employee_no"
                            class="form-control-custom"
                            value="<?= e(old('employee_no')) ?>"
                            placeholder="Enter employee number"
                            required
                        >
                    </div>

                    <div class="form-group-custom mb-4">
                        <label class="form-label-custom">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control-custom"
                            placeholder="Enter password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-auth-submit">Login</button>
                    <div class="auth-back-bottom">
                        <a href="../index.php">← Back to Home</a>
                    </div>

                    <div class="auth-footer-link">
                        <span>Don't have an account?</span>
                        <a href="register.php">Register account</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>