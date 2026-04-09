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

include __DIR__ . '/../includes/header_admin.php';
?>

<h3 class="mb-4">All Credentials</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>File</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= e($row['firstname'] . ' ' . $row['lastname']) ?> (<?= e($row['employee_no']) ?>)</td>
            <td><?= e($row['credential_type']) ?></td>
            <td><?= e($row['original_name']) ?></td>
            <td><?= e($row['uploaded_at']) ?></td>
            <td>
                <a href="<?= e($row['file_path']) ?>" target="_blank" class="btn btn-sm btn-primary">View</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>