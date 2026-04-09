<?php
require_once __DIR__ . '/../includes/auth_employee.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->prepare("SELECT * FROM credentials WHERE employee_id=? ORDER BY uploaded_at DESC");
$stmt->execute([$_SESSION['employee_id']]);
$data = $stmt->fetchAll();

include __DIR__ . '/../includes/header_employee.php';
?>

<h3 class="mb-4">My Credentials</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Type</th>
            <th>File</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
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