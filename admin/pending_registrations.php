<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("SELECT * FROM employees WHERE status = 'PENDING' ORDER BY created_at DESC");
$employees = $stmt->fetchAll();

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Pending Registrations</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>Employee No.</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Registered At</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employees): ?>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?= e($emp['employee_no']) ?></td>
                                <td><?= e(full_name($emp)) ?></td>
                                <td><?= e($emp['department']) ?></td>
                                <td><?= e($emp['position_title']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= e($emp['status']) ?></span></td>
                                <td><?= e($emp['created_at']) ?></td>
                                <td>
                                    <a href="approve_employee.php?id=<?= (int)$emp['id'] ?>" class="btn btn-success btn-sm"
                                       onclick="return confirm('Approve this employee?')">Approve</a>
                                    <a href="reject_employee.php?id=<?= (int)$emp['id'] ?>" class="btn btn-danger btn-sm"
                                       onclick="return confirm('Reject this employee?')">Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No pending registrations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>