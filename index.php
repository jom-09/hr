<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/constants.php';

if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
}

if (is_employee_logged_in()) {
    redirect('employee/dashboard.php');
}
?><?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/constants.php';

if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
}

if (is_employee_logged_in()) {
    redirect('employee/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/employee_style.css">
</head>
<body class="landing-page">

<div class="landing-wrapper">
    <div class="landing-shell">
        <div class="landing-content">
            <div class="landing-badge">Employee Credential Management System</div>

            <h1 class="landing-title"><?= e(APP_NAME) ?></h1>

            <p class="landing-subtitle">
                Secure profiling and storage of employee credentials for LGU-Aglipay.
                Access the employee portal, submit registration requests, and manage records
                through a clean and organized system.
            </p>

            <div class="landing-actions">
                <a href="employee/login.php" class="landing-btn landing-btn-primary">
                    Employee Login
                </a>

                <a href="employee/register.php" class="landing-btn landing-btn-secondary">
                    Employee Registration
                </a>

                <a href="admin/login.php" class="landing-btn landing-btn-dark">
                    HR/Admin Login
                </a>
            </div>
        </div>

        <div class="landing-side-card">
            <div class="landing-side-header">
                <div class="landing-side-icon">EC</div>
                <div>
                    <h4>Portal Access</h4>
                    <p>System entry points</p>
                </div>
            </div>

            <div class="landing-feature-list">
                <div class="landing-feature-item">
                    <span class="landing-feature-icon">👤</span>
                    <div>
                        <strong>Employee Access</strong>
                        <p>Login, register, and manage your uploaded credentials.</p>
                    </div>
                </div>

                <div class="landing-feature-item">
                    <span class="landing-feature-icon">📁</span>
                    <div>
                        <strong>Credential Storage</strong>
                        <p>Keep employee files organized and securely stored.</p>
                    </div>
                </div>

                <div class="landing-feature-item">
                    <span class="landing-feature-icon">🛡️</span>
                    <div>
                        <strong>HR/Admin Control</strong>
                        <p>Review registrations and manage employee records.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; }
        .hero-box {
            max-width: 720px;
            margin: 80px auto;
            background: #fff;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="hero-box">
        <h1 class="mb-3"><?= e(APP_NAME) ?></h1>
        <p class="text-muted mb-4">Secure profiling and storage of employee credentials for LGU-Aglipay.</p>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="employee/login.php" class="btn btn-primary me-2">Employee Login</a>
            <a href="employee/register.php" class="btn btn-outline-primary me-2">Employee Registration</a>
            <a href="admin/login.php" class="btn btn-dark">HR/Admin Login</a>
        </div>
    </div>
</div>
</body>
</html>