<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("SELECT * FROM employees WHERE status = 'PENDING' ORDER BY created_at DESC");
$employees = $stmt->fetchAll();

$totalPending = count($employees);

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="content-inner">
    <div class="admin-page">
        <div class="page-hero page-hero--compact">
            <div class="page-hero__content">
                <div class="hero-badge">Admin Management</div>
                <h1 class="page-title">Pending Registrations</h1>
                <p class="page-subtitle">
                    Review employee registration requests and approve or reject submissions.
                </p>
            </div>

            <div class="page-hero__side">
                <div class="hero-mini-card">
                    <span class="hero-mini-label">Total Pending</span>
                    <h3><?= number_format($totalPending) ?></h3>
                    <p>Employees waiting for approval</p>
                </div>
            </div>
        </div>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom card-header-custom--stack-mobile">
                <div>
                    <h4 class="card-title-custom">Pending Employees Table</h4>
                    <p class="card-subtitle-custom">
                        List of all employee accounts currently waiting for approval.
                    </p>
                </div>

                <div class="table-top-badge">
                    <?= number_format($totalPending) ?> Pending
                </div>
            </div>

            <div class="table-responsive custom-table-wrap">
                <table class="table custom-table custom-table--pending align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Employee No.</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Registered At</th>
                            <th class="text-center action-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($employees): ?>
                            <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td>
                                        <div class="table-title"><?= e($emp['employee_no']) ?></div>
                                        <div class="table-text-muted">Employee ID</div>
                                    </td>

                                    <td>
                                        <div class="table-title"><?= e(full_name($emp)) ?></div>
                                        <div class="table-text-muted">Pending employee</div>
                                    </td>

                                    <td><?= e($emp['department']) ?></td>

                                    <td><?= e($emp['position_title']) ?></td>

                                    <td>
                                        <span class="status-badge status-badge--yellow">
                                            <?= e($emp['status']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="table-date"><?= e($emp['created_at']) ?></div>
                                    </td>

                                    <td>
                                        <div class="action-group">
                                            <a href="approve_employee.php?id=<?= (int)$emp['id'] ?>"
                                               class="btn-action btn-action--success"
                                               onclick="return confirm('Approve this employee?')">
                                                Approve
                                            </a>

                                            <a href="reject_employee.php?id=<?= (int)$emp['id'] ?>"
                                               class="btn-action btn-action--danger"
                                               onclick="return confirm('Reject this employee?')">
                                                Reject
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">📭</div>
                                        <div class="empty-state__title">No pending registrations found</div>
                                        <div class="empty-state__text">
                                            There are currently no employee registrations waiting for review.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>