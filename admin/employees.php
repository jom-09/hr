<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM employees
        WHERE employee_no LIKE ?
           OR firstname LIKE ?
           OR middlename LIKE ?
           OR lastname LIKE ?
           OR department LIKE ?
           OR position_title LIKE ?
        ORDER BY created_at DESC
    ");
    $term = "%{$search}%";
    $stmt->execute([$term, $term, $term, $term, $term, $term]);
} else {
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC");
}

$employees = $stmt->fetchAll();

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>All Registered Employees</h2>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Search employee..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark w-100">Search</button>
            </div>
        </form>
    </div>
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
                        <th>Active</th>
                        <th>Created At</th>
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
                                <td>
                                    <?php
                                        $badge = 'secondary';
                                        if ($emp['status'] === 'APPROVED') $badge = 'success';
                                        if ($emp['status'] === 'PENDING') $badge = 'warning text-dark';
                                        if ($emp['status'] === 'REJECTED') $badge = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= e($emp['status']) ?></span>
                                </td>
                                <td><?= (int)$emp['is_active'] === 1 ? 'Yes' : 'No' ?></td>
                                <td><?= e($emp['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>