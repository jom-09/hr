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
    <link rel="stylesheet" href="../assets/css/employee_style.css">
</head>
<body class="auth-page">
<div class="container py-5">
    <div class="auth-card">
        <h2 class="mb-4">Employee Login</h2>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

            <div class="mb-3">
                <label class="form-label">Employee No.</label>
                <input type="text" name="employee_no" class="form-control" value="<?= e(old('employee_no')) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>

            <div class="text-center mt-3">
                <a href="register.php">Register account</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>