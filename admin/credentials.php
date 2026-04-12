<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("
    SELECT c.*, e.firstname, e.lastname, e.employee_no
    FROM credentials c
    JOIN employees e ON c.employee_id = e.id
    ORDER BY c.uploaded_at DESC
");

$data = $stmt->fetchAll();

$totalCredentials = count($data);

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="content-inner">
    <div class="admin-page">
        <div class="page-hero page-hero--compact">
            <div class="page-hero__content">
                <div class="hero-badge">Admin Records</div>
                <h1 class="page-title">Employee Credentials</h1>
                <p class="page-subtitle">
                    View and manage all uploaded employee credential files in one place.
                </p>
            </div>

            <div class="page-hero__side">
                <div class="hero-mini-card">
                    <span class="hero-mini-label">Total Credentials</span>
                    <h3><?= number_format($totalCredentials) ?></h3>
                    <p>Uploaded employee files</p>
                </div>
            </div>
        </div>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom card-header-custom--stack-mobile">
                <div>
                    <h4 class="card-title-custom">Credentials Table</h4>
                    <p class="card-subtitle-custom">
                        Complete list of employee credential uploads and file records.
                    </p>
                </div>

                <div class="table-top-badge">
                    <?= number_format($totalCredentials) ?> Records
                </div>
            </div>

            <div class="table-responsive custom-table-wrap">
                <table class="table custom-table custom-table--credentials align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>File Name</th>
                            <th>Date Uploaded</th>
                            <th class="text-center action-column">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($data): ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td>
                                        <div class="table-title">
                                            <?= e($row['firstname'] . ' ' . $row['lastname']) ?>
                                        </div>
                                        <div class="table-text-muted">
                                            Employee No: <?= e($row['employee_no']) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="status-badge status-badge--blue">
                                            <?= e($row['credential_type']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="table-title table-file-name">
                                            <?= e($row['original_name']) ?>
                                        </div>
                                        <div class="table-text-muted">Uploaded document</div>
                                    </td>

                                    <td>
                                        <div class="table-date"><?= e($row['uploaded_at']) ?></div>
                                    </td>

                                    <td>
                                        <div class="action-group">
                                            <a href="<?= e($row['file_path']) ?>"
                                               target="_blank"
                                               class="btn-action btn-action--primary">
                                                View File
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">📂</div>
                                        <div class="empty-state__title">No credentials found</div>
                                        <div class="empty-state__text">
                                            There are currently no uploaded employee credentials in the system.
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