<?php
require_once __DIR__ . '/../includes/auth_employee.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$employeeId = $_SESSION['employee_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM credentials WHERE employee_id = ?");
$stmt->execute([$employeeId]);
$totalUploads = (int)$stmt->fetchColumn();

include __DIR__ . '/../includes/header_employee.php';
?>

<div class="page-header mb-4">
    <h2>Employee Dashboard</h2>
    <p class="text-muted">Welcome, <?= e($_SESSION['employee_name'] ?? '') ?></p>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="stat-card">
            <h6>Total Uploaded Credentials</h6>
            <h2><?= $totalUploads ?></h2>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5>Account Status</h5>
        <p class="mb-0">Your account is approved and active.</p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>