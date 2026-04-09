<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = '';
$csrf = generate_csrf_token();

if (is_post()) {
    require_csrf();

    $employee_no    = trim($_POST['employee_no'] ?? '');
    $firstname      = trim($_POST['firstname'] ?? '');
    $middlename     = trim($_POST['middlename'] ?? '');
    $lastname       = trim($_POST['lastname'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $department     = trim($_POST['department'] ?? '');
    $position_title = trim($_POST['position_title'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirm        = $_POST['confirm_password'] ?? '';

    if ($employee_no === '') $errors[] = 'Employee No. is required.';
    if ($firstname === '') $errors[] = 'First name is required.';
    if ($lastname === '') $errors[] = 'Last name is required.';
    if ($department === '') $errors[] = 'Department is required.';
    if ($position_title === '') $errors[] = 'Position title is required.';
    if ($password === '') $errors[] = 'Password is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM employees WHERE employee_no = ?");
        $stmt->execute([$employee_no]);

        if ($stmt->fetch()) {
            $errors[] = 'Employee No. already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO employees (
                    employee_no, firstname, middlename, lastname, email, department, position_title, password, status, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', 1)
            ");

            $stmt->execute([
                $employee_no,
                $firstname,
                $middlename ?: null,
                $lastname,
                $email ?: null,
                $department,
                $position_title,
                $hashed
            ]);

            $success = 'Registration submitted successfully. Please wait for HR approval.';
            $_POST = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/employee_style.css">
</head>
<body class="auth-page">
<div class="container py-5">
    <div class="auth-card">
        <h2 class="mb-4">Employee Registration</h2>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employee No.</label>
                    <input type="text" name="employee_no" class="form-control" value="<?= e(old('employee_no')) ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email (optional)</label>
                    <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="firstname" class="form-control" value="<?= e(old('firstname')) ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middlename" class="form-control" value="<?= e(old('middlename')) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lastname" class="form-control" value="<?= e(old('lastname')) ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="<?= e(old('department')) ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Position Title</label>
                    <input type="text" name="position_title" class="form-control" value="<?= e(old('position_title')) ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>

            <div class="text-center mt-3">
                <a href="login.php">Already have an account? Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>