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

$totalEmployees = count($employees);
$searchLabel = $search !== '' ? 'Search Results' : 'All Employees';

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="content-inner">
    <div class="admin-page">
        <div class="page-hero page-hero--compact">
            <div class="page-hero__content">
                <div class="hero-badge">Admin Directory</div>
                <h1 class="page-title">Registered Employees</h1>
                <p class="page-subtitle">
                    View, search, and monitor all registered employees in the system.
                </p>
            </div>

            <div class="page-hero__side">
                <div class="hero-mini-card">
                    <span class="hero-mini-label"><?= e($searchLabel) ?></span>
                    <h3><?= number_format($totalEmployees) ?></h3>
                    <p>Employee records displayed</p>
                </div>
            </div>
        </div>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom">
                <div>
                    <h4 class="card-title-custom">Employee Search</h4>
                    <p class="card-subtitle-custom">Search across employee number, name, department, and position</p>
                </div>
            </div>

            <form method="GET" class="employee-search-form">
                <div class="employee-search-grid">
                    <div class="employee-search-input-wrap">
                        <input
                            type="text"
                            name="search"
                            class="employee-search-input"
                            placeholder="Search employee..."
                            value="<?= e($search) ?>"
                        >
                    </div>

                    <div class="employee-search-btn-wrap">
                        <button type="submit" class="employee-search-btn">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom card-header-custom--stack-mobile">
                <div>
                    <h4 class="card-title-custom">Employees Table</h4>
                    <p class="card-subtitle-custom">
                        Complete list of all registered employees and their account details.
                    </p>
                </div>

                <div class="table-top-badge">
                    <?= number_format($totalEmployees) ?> Records
                </div>
            </div>

            <div class="table-responsive custom-table-wrap">
                <table class="table custom-table custom-table--employees align-middle mb-0">
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
                                <?php
                                    $statusClass = 'status-badge--dark';
                                    if ($emp['status'] === 'APPROVED') $statusClass = 'status-badge--green';
                                    if ($emp['status'] === 'PENDING') $statusClass = 'status-badge--yellow';
                                    if ($emp['status'] === 'REJECTED') $statusClass = 'status-badge--danger';

                                    $activeClass = ((int)$emp['is_active'] === 1) ? 'status-badge--green' : 'status-badge--dark';
                                    $activeText  = ((int)$emp['is_active'] === 1) ? 'Yes' : 'No';
                                ?>
                                <tr>
                                    <td>
                                        <div class="table-title"><?= e($emp['employee_no']) ?></div>
                                        <div class="table-text-muted">Employee ID</div>
                                    </td>

                                    <td>
                                        <div class="table-title"><?= e(full_name($emp)) ?></div>
                                        <div class="table-text-muted">Registered employee</div>
                                    </td>

                                    <td><?= e($emp['department']) ?></td>

                                    <td><?= e($emp['position_title']) ?></td>

                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= e($emp['status']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="status-badge <?= $activeClass ?>">
                                            <?= e($activeText) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="table-date"><?= e($emp['created_at']) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">👤</div>
                                        <div class="empty-state__title">No employees found</div>
                                        <div class="empty-state__text">
                                            No employee records matched your current search.
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