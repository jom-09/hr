<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$totalEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
$pendingEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='PENDING'")->fetchColumn();
$approvedEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='APPROVED'")->fetchColumn();
$totalCredentials = (int)$pdo->query("SELECT COUNT(*) FROM credentials")->fetchColumn();

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="page-header mb-4">
    <h2>Admin Dashboard</h2>
    <p class="text-muted">Welcome, <?= e($_SESSION['admin_name'] ?? '') ?></p>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="stat-card">
            <h6>Total Employees</h6>
            <h2><?= $totalEmployees ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6>Pending Registrations</h6>
            <h2><?= $pendingEmployees ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6>Approved Employees</h6>
            <h2><?= $approvedEmployees ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h6>Total Credentials</h6>
            <h2><?= $totalCredentials ?></h2>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>