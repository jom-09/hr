<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = '';
$csrf = generate_csrf_token();

$departments = [
    'Accounting',
    'Admin',
    'Agriculture',
    'Assessor',
    'Motorpool',
    'Engineering',
    'DAO',
    'Executive',
    'MBO',
    'MCR',
    'MENRO',
    'MPDC',
    'MTO',
    'NUT/POP',
    'SB Sec',
    'SB Staff',
    'VET',
    'Gen Pub Utilities',
    'Market',
    'PESO',
    'DRRM',
    'MHO',
    'MSWDO',
    'EEU Market',
    'Slaughter House'
];

$sexOptions = ['Male', 'Female'];

$employmentStatuses = [
    'Permanent',
    'Contractual',
    'Co-Terminus',
    'Casual',
    'Job Order'
];

if (is_post()) {
    require_csrf();

    $employee_no         = trim($_POST['employee_no'] ?? '');
    $firstname           = trim($_POST['firstname'] ?? '');
    $middlename          = trim($_POST['middlename'] ?? '');
    $lastname            = trim($_POST['lastname'] ?? '');
    $email               = trim($_POST['email'] ?? '');
    $date_of_appointment = trim($_POST['date_of_appointment'] ?? '');
    $sex                 = trim($_POST['sex'] ?? '');
    $department          = trim($_POST['department'] ?? '');
    $employment_status   = trim($_POST['employment_status'] ?? '');
    $password            = $_POST['password'] ?? '';
    $confirm             = $_POST['confirm_password'] ?? '';

    if ($employee_no === '') $errors[] = 'Employee No. is required.';
    if ($firstname === '') $errors[] = 'First name is required.';
    if ($lastname === '') $errors[] = 'Last name is required.';
    if ($date_of_appointment === '') $errors[] = 'Date of appointment is required.';
    if ($sex === '') $errors[] = 'Sex is required.';
    if ($department === '') $errors[] = 'Department is required.';
    if ($employment_status === '') $errors[] = 'Status of employment is required.';
    if ($password === '') $errors[] = 'Password is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if ($sex !== '' && !in_array($sex, $sexOptions, true)) {
        $errors[] = 'Invalid sex selected.';
    }

    if ($department !== '' && !in_array($department, $departments, true)) {
        $errors[] = 'Invalid department selected.';
    }

    if ($employment_status !== '' && !in_array($employment_status, $employmentStatuses, true)) {
        $errors[] = 'Invalid employment status selected.';
    }

    if ($date_of_appointment !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $date_of_appointment);
        if (!$d || $d->format('Y-m-d') !== $date_of_appointment) {
            $errors[] = 'Invalid date of appointment.';
        }
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
                    employee_no,
                    firstname,
                    middlename,
                    lastname,
                    email,
                    date_of_appointment,
                    sex,
                    department,
                    employment_status,
                    password,
                    status,
                    is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', 1)
            ");

            $stmt->execute([
                $employee_no,
                $firstname,
                $middlename ?: null,
                $lastname,
                $email ?: null,
                $date_of_appointment,
                $sex,
                $department,
                $employment_status,
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
    <link rel="stylesheet" href="../assets/bootstrap/css/employee_style.css">
</head>
<body class="employee-auth-page">

<div class="employee-auth-wrapper">
    <div class="employee-auth-shell employee-register-shell">

        <div class="employee-auth-left">
            <div class="auth-brand-badge">Employee Portal</div>
            <h1>Create Your Account</h1>
            <p>
                Register your employee account to access the portal, upload credentials,
                and manage your employee records online.
            </p>

            <div class="auth-feature-list">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon">📝</span>
                    <div>
                        <strong>Quick Registration</strong>
                        <p>Submit your employee details to request account access.</p>
                    </div>
                </div>

                <div class="auth-feature-item">
                    <span class="auth-feature-icon">👨‍💼</span>
                    <div>
                        <strong>HR Approval</strong>
                        <p>Your registration will be reviewed before activation.</p>
                    </div>
                </div>

                <div class="auth-feature-item">
                    <span class="auth-feature-icon">📁</span>
                    <div>
                        <strong>Credential Access</strong>
                        <p>Once approved, you can upload and manage documents.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="employee-auth-right employee-register-right">
            <div class="employee-auth-card employee-register-card">
                <div class="auth-card-header">
                    <p class="auth-kicker">Create account</p>
                    <h2>Employee Registration</h2>
                    <span>Fill in your details to submit a registration request.</span>
                </div>

                <?php if ($errors): ?>
                    <div class="custom-alert custom-alert-danger mb-4">
                        <div class="custom-alert-title">Registration Error</div>
                        <?php foreach ($errors as $error): ?>
                            <div class="custom-alert-text"><?= e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="custom-alert custom-alert-success mb-4">
                        <div class="custom-alert-title">Success</div>
                        <div class="custom-alert-text"><?= e($success) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group-custom">
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
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Email (Optional)</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control-custom"
                                    value="<?= e(old('email')) ?>"
                                    placeholder="Enter email address"
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group-custom">
                                <label class="form-label-custom">First Name</label>
                                <input
                                    type="text"
                                    name="firstname"
                                    class="form-control-custom"
                                    value="<?= e(old('firstname')) ?>"
                                    placeholder="First name"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Middle Name</label>
                                <input
                                    type="text"
                                    name="middlename"
                                    class="form-control-custom"
                                    value="<?= e(old('middlename')) ?>"
                                    placeholder="Middle name"
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Last Name</label>
                                <input
                                    type="text"
                                    name="lastname"
                                    class="form-control-custom"
                                    value="<?= e(old('lastname')) ?>"
                                    placeholder="Last name"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Date of Appointment</label>
                                <input
                                    type="date"
                                    name="date_of_appointment"
                                    class="form-control-custom"
                                    value="<?= e(old('date_of_appointment')) ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Sex</label>
                                <select name="sex" class="form-control-custom" required>
                                    <option value="">Select sex</option>
                                    <?php foreach ($sexOptions as $option): ?>
                                        <option value="<?= e($option) ?>" <?= old('sex') === $option ? 'selected' : '' ?>>
                                            <?= e($option) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Department</label>
                                <select name="department" class="form-control-custom" required>
                                    <option value="">Select department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= e($dept) ?>" <?= old('department') === $dept ? 'selected' : '' ?>>
                                            <?= e($dept) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Status of Employment</label>
                                <select name="employment_status" class="form-control-custom" required>
                                    <option value="">Select status</option>
                                    <?php foreach ($employmentStatuses as $status): ?>
                                        <option value="<?= e($status) ?>" <?= old('employment_status') === $status ? 'selected' : '' ?>>
                                            <?= e($status) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control-custom"
                                    placeholder="Enter password"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label class="form-label-custom">Confirm Password</label>
                                <input
                                    type="password"
                                    name="confirm_password"
                                    class="form-control-custom"
                                    placeholder="Confirm password"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-submit mt-4">Submit Registration</button>

                    <div class="auth-footer-link">
                        <span>Already have an account?</span>
                        <a href="login.php">Login</a>
                    </div>

                    <a href="../index.php" class="auth-back-btn">← Back to Home</a>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>