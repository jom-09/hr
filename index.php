<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/functions.php';

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