<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$departments = [
    'Accounting',
    'Admin',
    'Agriculture',
    'Assessor',
    'Motorpool',
    'Engineering',
    'DAO',
    'Executive',
    'MBO',
    'MCR',
    'MENRO',
    'MPDC',
    'MTO',
    'NUT/POP',
    'SB Sec',
    'SB Staff',
    'VET',
    'Gen Pub Utilities',
    'Market',
    'PESO',
    'DRRM',
    'MHO',
    'MSWDO',
    'EEU Market',
    'Slaughter House'
];

/*
|--------------------------------------------------------------------------
| Fetch credentials joined with employees
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT 
        c.*,
        e.firstname,
        e.middlename,
        e.lastname,
        e.employee_no,
        e.department
    FROM credentials c
    JOIN employees e ON c.employee_id = e.id
    ORDER BY e.department ASC, e.lastname ASC, e.firstname ASC, c.uploaded_at DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalCredentials = count($rows);

/*
|--------------------------------------------------------------------------
| Group by department
|--------------------------------------------------------------------------
*/
$groupedCredentials = [];
foreach ($departments as $dept) {
    $groupedCredentials[$dept] = [];
}

foreach ($rows as $row) {
    $dept = trim($row['department'] ?? '');
    if ($dept === '') {
        $dept = 'Unassigned';
    }

    if (!isset($groupedCredentials[$dept])) {
        $groupedCredentials[$dept] = [];
    }

    $groupedCredentials[$dept][] = $row;
}

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="content-inner">
    <div class="admin-page">
        <div class="page-hero page-hero--compact">
            <div class="page-hero__content">
                <div class="hero-badge">Admin Records</div>
                <h1 class="page-title">Employee Credentials</h1>
                <p class="page-subtitle">
                    View uploaded employee credentials organized by department.
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
                    <h4 class="card-title-custom">Credentials by Department</h4>
                    <p class="card-subtitle-custom">
                        Click each department to view employee credentials sorted by employee name.
                    </p>
                </div>

                <div class="table-top-badge">
                    <?= number_format($totalCredentials) ?> Records
                </div>
            </div>

            <?php if ($groupedCredentials): ?>
                <div class="department-accordion" id="departmentCredentialsAccordion">
                    <?php
                    $accordionIndex = 0;
                    foreach ($groupedCredentials as $departmentName => $departmentRows):
                        $accordionIndex++;
                        $collapseId = 'deptCollapse' . $accordionIndex;
                        $headingId  = 'deptHeading' . $accordionIndex;
                        $countRows  = count($departmentRows);
                    ?>
                        <div class="department-accordion-item">
                            <h2 class="department-accordion-header" id="<?= e($headingId) ?>">
                                <button
                                    class="department-accordion-button collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= e($collapseId) ?>"
                                    aria-expanded="false"
                                    aria-controls="<?= e($collapseId) ?>"
                                >
                                    <span class="department-accordion-title"><?= e($departmentName) ?></span>
                                    <span class="department-accordion-count"><?= number_format($countRows) ?> credential<?= $countRows === 1 ? '' : 's' ?></span>
                                </button>
                            </h2>

                            <div
                                id="<?= e($collapseId) ?>"
                                class="accordion-collapse collapse"
                                aria-labelledby="<?= e($headingId) ?>"
                                data-bs-parent="#departmentCredentialsAccordion"
                            >
                                <div class="department-accordion-body">
                                    <?php if ($departmentRows): ?>
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
                                                    <?php foreach ($departmentRows as $row): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="table-title">
                                                                    <?= e(trim(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? '') . ' ' . ($row['middlename'] ?? ''))) ?>
                                                                </div>
                                                                <div class="table-text-muted">
                                                                    Employee No: <?= e($row['employee_no'] ?? '') ?>
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
                                                                <div class="table-date">
                                                                    <?= !empty($row['uploaded_at']) ? e(date('F d, Y h:i A', strtotime($row['uploaded_at']))) : 'N/A' ?>
                                                                </div>
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
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state empty-state--compact">
                                            <div class="empty-state__icon">📂</div>
                                            <div class="empty-state__title">No credentials found</div>
                                            <div class="empty-state__text">
                                                No uploaded credentials for this department yet.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (isset($groupedCredentials['Unassigned'])): ?>
                        <?php
                            $accordionIndex++;
                            $collapseId = 'deptCollapse' . $accordionIndex;
                            $headingId  = 'deptHeading' . $accordionIndex;
                            $departmentRows = $groupedCredentials['Unassigned'];
                            $countRows = count($departmentRows);
                        ?>
                        <div class="department-accordion-item">
                            <h2 class="department-accordion-header" id="<?= e($headingId) ?>">
                                <button
                                    class="department-accordion-button collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= e($collapseId) ?>"
                                    aria-expanded="false"
                                    aria-controls="<?= e($collapseId) ?>"
                                >
                                    <span class="department-accordion-title">Unassigned</span>
                                    <span class="department-accordion-count"><?= number_format($countRows) ?> credential<?= $countRows === 1 ? '' : 's' ?></span>
                                </button>
                            </h2>

                            <div
                                id="<?= e($collapseId) ?>"
                                class="accordion-collapse collapse"
                                aria-labelledby="<?= e($headingId) ?>"
                                data-bs-parent="#departmentCredentialsAccordion"
                            >
                                <div class="department-accordion-body">
                                    <?php if ($departmentRows): ?>
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
                                                    <?php foreach ($departmentRows as $row): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="table-title">
                                                                    <?= e(trim(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? '') . ' ' . ($row['middlename'] ?? ''))) ?>
                                                                </div>
                                                                <div class="table-text-muted">
                                                                    Employee No: <?= e($row['employee_no'] ?? '') ?>
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
                                                                <div class="table-date">
                                                                    <?= !empty($row['uploaded_at']) ? e(date('F d, Y h:i A', strtotime($row['uploaded_at']))) : 'N/A' ?>
                                                                </div>
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
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state empty-state--compact">
                                            <div class="empty-state__icon">📂</div>
                                            <div class="empty-state__title">No credentials found</div>
                                            <div class="empty-state__text">
                                                No uploaded credentials for this department yet.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state__icon">📂</div>
                    <div class="empty-state__title">No credentials found</div>
                    <div class="empty-state__text">
                        There are currently no uploaded employee credentials in the system.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include __DIR__ . '/../includes/footer.php'; ?>