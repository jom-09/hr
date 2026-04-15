<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| Detect password column in employees
|--------------------------------------------------------------------------
*/
$passwordColumn = null;
try {
    $check = $pdo->query("SHOW COLUMNS FROM employees");
    $columns = $check->fetchAll(PDO::FETCH_COLUMN, 0);

    if (in_array('password_hash', $columns, true)) {
        $passwordColumn = 'password_hash';
    } elseif (in_array('password', $columns, true)) {
        $passwordColumn = 'password';
    }
} catch (Throwable $e) {
    // ignore
}

/*
|--------------------------------------------------------------------------
| Handle archive actions
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'restore_employee') {
            $id = (int)($_POST['id'] ?? 0);

            $stmt = $pdo->prepare("SELECT * FROM archived_employees WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new Exception('Archived employee not found.');
            }

            $dupStmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE employee_no = ?");
            $dupStmt->execute([$row['employee_no']]);
            if ((int)$dupStmt->fetchColumn() > 0) {
                throw new Exception('Cannot restore employee. Employee number already exists.');
            }

            if ($passwordColumn === 'password_hash') {
                $insert = $pdo->prepare("
                    INSERT INTO employees (
                        employee_no, firstname, middlename, lastname, email,
                        date_of_appointment, sex, department, employment_status,
                        password_hash, status, is_active, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
            } else {
                $insert = $pdo->prepare("
                    INSERT INTO employees (
                        employee_no, firstname, middlename, lastname, email,
                        date_of_appointment, sex, department, employment_status,
                        password, status, is_active, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
            }

            $insert->execute([
                $row['employee_no'],
                $row['firstname'],
                $row['middlename'],
                $row['lastname'],
                $row['email'],
                $row['date_of_appointment'],
                $row['sex'],
                $row['department'],
                $row['employment_status'],
                $row['password_value'],
                $row['status'],
                (int)$row['is_active'],
                $row['created_at']
            ]);

            $del = $pdo->prepare("DELETE FROM archived_employees WHERE id = ?");
            $del->execute([$id]);

            $success = 'Employee restored successfully.';
        }

        if ($action === 'delete_employee_permanently') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM archived_employees WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Archived employee permanently deleted.';
        }

        if ($action === 'restore_credential') {
            $id = (int)($_POST['id'] ?? 0);

            $stmt = $pdo->prepare("SELECT * FROM archived_credentials WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new Exception('Archived credential not found.');
            }

            $insert = $pdo->prepare("
                INSERT INTO credentials (
                    employee_id, credential_type, original_name, file_path, uploaded_at
                ) VALUES (?, ?, ?, ?, ?)
            ");

            $insert->execute([
                $row['employee_id'],
                $row['credential_type'],
                $row['original_name'],
                $row['file_path'],
                $row['uploaded_at']
            ]);

            $del = $pdo->prepare("DELETE FROM archived_credentials WHERE id = ?");
            $del->execute([$id]);

            $success = 'Credential restored successfully.';
        }

        if ($action === 'delete_credential_permanently') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM archived_credentials WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Archived credential permanently deleted.';
        }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| Fetch archived data
|--------------------------------------------------------------------------
*/
$archivedEmployees = $pdo->query("
    SELECT * FROM archived_employees
    ORDER BY archived_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$archivedCredentials = $pdo->query("
    SELECT * FROM archived_credentials
    ORDER BY archived_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="content-inner">
    <div class="admin-page">
        <div class="page-hero page-hero--compact">
            <div class="page-hero__content">
                <div class="hero-badge">Admin Archive</div>
                <h1 class="page-title">Archive</h1>
                <p class="page-subtitle">
                    Manage deleted employees and credentials. Restore them or delete permanently.
                </p>
            </div>

            <div class="page-hero__side">
                <div class="hero-mini-card">
                    <span class="hero-mini-label">Archived Records</span>
                    <h3><?= number_format(count($archivedEmployees) + count($archivedCredentials)) ?></h3>
                    <p>Total archived employees and credentials</p>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success mt-4"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-4"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom card-header-custom--stack-mobile">
                <div>
                    <h4 class="card-title-custom">Archived Employees</h4>
                    <p class="card-subtitle-custom">Employees moved from the main list are stored here.</p>
                </div>
                <div class="table-top-badge"><?= number_format(count($archivedEmployees)) ?> Records</div>
            </div>

            <div class="table-responsive custom-table-wrap">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Employee No.</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Employment Status</th>
                            <th>Archived At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($archivedEmployees): ?>
                            <?php foreach ($archivedEmployees as $row): ?>
                                <tr>
                                    <td><?= e($row['employee_no']) ?></td>
                                    <td><?= e(trim($row['lastname'] . ', ' . $row['firstname'] . ' ' . ($row['middlename'] ?? ''))) ?></td>
                                    <td><?= e($row['department'] ?? 'N/A') ?></td>
                                    <td><?= e($row['employment_status'] ?? 'N/A') ?></td>
                                    <td><?= e(date('F d, Y h:i A', strtotime($row['archived_at']))) ?></td>
                                    <td class="text-center">
                                        <div class="action-icon-group">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="restore_employee">
                                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                <button type="submit" class="icon-action-btn icon-action-btn--view" title="Restore">
                                                    <i class="fas fa-rotate-left"></i>
                                                </button>
                                            </form>

                                            <form method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this archived employee?');">
                                                <input type="hidden" name="action" value="delete_employee_permanently">
                                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                <button type="submit" class="icon-action-btn icon-action-btn--delete" title="Delete Permanently">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">🗃️</div>
                                        <div class="empty-state__title">No archived employees</div>
                                        <div class="empty-state__text">Deleted employees will appear here.</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom card-header-custom--stack-mobile">
                <div>
                    <h4 class="card-title-custom">Archived Credentials</h4>
                    <p class="card-subtitle-custom">Deleted credentials are stored here for recovery.</p>
                </div>
                <div class="table-top-badge"><?= number_format(count($archivedCredentials)) ?> Records</div>
            </div>

            <div class="table-responsive custom-table-wrap">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Type</th>
                            <th>File Name</th>
                            <th>Archived At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($archivedCredentials): ?>
                            <?php foreach ($archivedCredentials as $row): ?>
                                <tr>
                                    <td><?= e(trim(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? '') . ' ' . ($row['middlename'] ?? ''))) ?></td>
                                    <td><?= e($row['department'] ?? 'N/A') ?></td>
                                    <td><?= e($row['credential_type'] ?? 'N/A') ?></td>
                                    <td><?= e($row['original_name'] ?? 'N/A') ?></td>
                                    <td><?= e(date('F d, Y h:i A', strtotime($row['archived_at']))) ?></td>
                                    <td class="text-center">
                                        <div class="action-icon-group">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="restore_credential">
                                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                <button type="submit" class="icon-action-btn icon-action-btn--view" title="Restore">
                                                    <i class="fas fa-rotate-left"></i>
                                                </button>
                                            </form>

                                            <form method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this archived credential?');">
                                                <input type="hidden" name="action" value="delete_credential_permanently">
                                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                <button type="submit" class="icon-action-btn icon-action-btn--delete" title="Delete Permanently">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">📂</div>
                                        <div class="empty-state__title">No archived credentials</div>
                                        <div class="empty-state__text">Deleted credentials will appear here.</div>
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