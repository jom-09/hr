<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$success = '';
$error   = '';

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

$sexOptions = ['Male', 'Female'];

$employmentStatuses = [
    'Permanent',
    'Contractual',
    'Co-Terminus',
    'Casual',
    'Job Order'
];

/*
|--------------------------------------------------------------------------
| Detect password column
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
    // ignore column detection failure
}

/*
|--------------------------------------------------------------------------
| Handle POST actions
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        /*
        |--------------------------------------------------------------------------
        | EDIT EMPLOYEE
        |--------------------------------------------------------------------------
        */
        if ($action === 'edit_employee') {
            $id                  = (int)($_POST['id'] ?? 0);
            $employee_no         = trim($_POST['employee_no'] ?? '');
            $firstname           = trim($_POST['firstname'] ?? '');
            $middlename          = trim($_POST['middlename'] ?? '');
            $lastname            = trim($_POST['lastname'] ?? '');
            $date_of_appointment = trim($_POST['date_of_appointment'] ?? '');
            $sex                 = trim($_POST['sex'] ?? '');
            $department          = trim($_POST['department'] ?? '');
            $employment_status   = trim($_POST['employment_status'] ?? '');
            $status              = trim($_POST['status'] ?? 'PENDING');
            $is_active           = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
            $new_password        = trim($_POST['new_password'] ?? '');

            if ($id <= 0) {
                throw new Exception('Invalid employee ID.');
            }

            if (
                $employee_no === '' ||
                $firstname === '' ||
                $lastname === '' ||
                $date_of_appointment === '' ||
                $sex === '' ||
                $department === '' ||
                $employment_status === ''
            ) {
                throw new Exception('Please fill in all required fields.');
            }

            if (!in_array($sex, $sexOptions, true)) {
                throw new Exception('Invalid sex selected.');
            }

            if (!in_array($department, $departments, true)) {
                throw new Exception('Invalid department selected.');
            }

            if (!in_array($employment_status, $employmentStatuses, true)) {
                throw new Exception('Invalid employment status selected.');
            }

            if (!in_array($status, ['PENDING', 'APPROVED', 'REJECTED'], true)) {
                $status = 'PENDING';
            }

            if (!in_array($is_active, [0, 1], true)) {
                $is_active = 0;
            }

            $d = DateTime::createFromFormat('Y-m-d', $date_of_appointment);
            if (!$d || $d->format('Y-m-d') !== $date_of_appointment) {
                throw new Exception('Invalid date of appointment.');
            }

            // Check duplicate employee number
            $dupStmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE employee_no = ? AND id != ?");
            $dupStmt->execute([$employee_no, $id]);
            if ((int)$dupStmt->fetchColumn() > 0) {
                throw new Exception('Employee number already exists.');
            }

            if ($new_password !== '' && $passwordColumn !== null) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

                $sql = "UPDATE employees SET 
                            employee_no = ?,
                            firstname = ?,
                            middlename = ?,
                            lastname = ?,
                            date_of_appointment = ?,
                            sex = ?,
                            department = ?,
                            employment_status = ?,
                            status = ?,
                            is_active = ?,
                            {$passwordColumn} = ?
                        WHERE id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $employee_no,
                    $firstname,
                    $middlename ?: null,
                    $lastname,
                    $date_of_appointment,
                    $sex,
                    $department,
                    $employment_status,
                    $status,
                    $is_active,
                    $hashedPassword,
                    $id
                ]);
            } else {
                $sql = "UPDATE employees SET 
                            employee_no = ?,
                            firstname = ?,
                            middlename = ?,
                            lastname = ?,
                            date_of_appointment = ?,
                            sex = ?,
                            department = ?,
                            employment_status = ?,
                            status = ?,
                            is_active = ?
                        WHERE id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $employee_no,
                    $firstname,
                    $middlename ?: null,
                    $lastname,
                    $date_of_appointment,
                    $sex,
                    $department,
                    $employment_status,
                    $status,
                    $is_active,
                    $id
                ]);
            }

            $success = 'Employee updated successfully.';
        }

        /*
        |--------------------------------------------------------------------------
        | DELETE EMPLOYEE
        |--------------------------------------------------------------------------
        */
        if ($action === 'delete_employee') {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid employee ID.');
            }

            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$id]);

            $success = 'Employee deleted successfully.';
        }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM employees
        WHERE employee_no LIKE ?
           OR firstname LIKE ?
           OR middlename LIKE ?
           OR lastname LIKE ?
           OR department LIKE ?
           OR sex LIKE ?
           OR employment_status LIKE ?
           OR date_of_appointment LIKE ?
        ORDER BY created_at DESC
    ");
    $term = "%{$search}%";
    $stmt->execute([$term, $term, $term, $term, $term, $term, $term, $term]);
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
                    View, search, edit, and manage all registered employees in the system.
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

        <?php if ($success): ?>
            <div class="alert alert-success mt-4"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-4"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom">
                <div>
                    <h4 class="card-title-custom">Employee Search</h4>
                    <p class="card-subtitle-custom">Search across employee number, name, department, sex, employment status, and appointment date</p>
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
                            <th>Date of Appointment</th>
                            <th>Employment Status</th>
                            <th>Status</th>
                            <th>Active</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($employees): ?>
                            <?php foreach ($employees as $emp): ?>
                                <?php
                                    $statusClass = 'status-badge--dark';
                                    if (($emp['status'] ?? '') === 'APPROVED') $statusClass = 'status-badge--green';
                                    if (($emp['status'] ?? '') === 'PENDING')  $statusClass = 'status-badge--yellow';
                                    if (($emp['status'] ?? '') === 'REJECTED') $statusClass = 'status-badge--danger';

                                    $activeClass = ((int)($emp['is_active'] ?? 0) === 1) ? 'status-badge--green' : 'status-badge--dark';
                                    $activeText  = ((int)($emp['is_active'] ?? 0) === 1) ? 'Yes' : 'No';
                                ?>
                                <tr>
                                    <td>
                                        <div class="table-title"><?= e($emp['employee_no'] ?? '') ?></div>
                                        <div class="table-text-muted">Employee ID</div>
                                    </td>

                                    <td>
                                        <div class="table-title"><?= e(full_name($emp)) ?></div>
                                        <div class="table-text-muted"><?= e($emp['sex'] ?? 'N/A') ?></div>
                                    </td>

                                    <td>
                                        <div class="table-title"><?= e($emp['department'] ?? '') ?></div>
                                        <div class="table-text-muted">Department</div>
                                    </td>

                                    <td>
                                        <div class="table-title">
                                            <?= !empty($emp['date_of_appointment']) ? e(date('F d, Y', strtotime($emp['date_of_appointment']))) : 'N/A' ?>
                                        </div>
                                        <div class="table-text-muted">Appointment date</div>
                                    </td>

                                    <td>
                                        <div class="table-title"><?= e($emp['employment_status'] ?? 'N/A') ?></div>
                                        <div class="table-text-muted">Employment type</div>
                                    </td>

                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= e($emp['status'] ?? '') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="status-badge <?= $activeClass ?>">
                                            <?= e($activeText) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="table-date">
                                            <?= !empty($emp['created_at']) ? e(date('F d, Y h:i A', strtotime($emp['created_at']))) : 'N/A' ?>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary editEmployeeBtn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editEmployeeModal"
                                                data-id="<?= e($emp['id']) ?>"
                                                data-employee_no="<?= e($emp['employee_no'] ?? '') ?>"
                                                data-firstname="<?= e($emp['firstname'] ?? '') ?>"
                                                data-middlename="<?= e($emp['middlename'] ?? '') ?>"
                                                data-lastname="<?= e($emp['lastname'] ?? '') ?>"
                                                data-date_of_appointment="<?= e($emp['date_of_appointment'] ?? '') ?>"
                                                data-sex="<?= e($emp['sex'] ?? '') ?>"
                                                data-department="<?= e($emp['department'] ?? '') ?>"
                                                data-employment_status="<?= e($emp['employment_status'] ?? '') ?>"
                                                data-status="<?= e($emp['status'] ?? '') ?>"
                                                data-is_active="<?= (int)($emp['is_active'] ?? 0) ?>"
                                            >
                                                Edit
                                            </button>

                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                <input type="hidden" name="action" value="delete_employee">
                                                <input type="hidden" name="id" value="<?= e($emp['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">
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

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <input type="hidden" name="action" value="edit_employee">
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee No.</label>
                            <input type="text" name="employee_no" id="edit_employee_no" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date of Appointment</label>
                            <input type="date" name="date_of_appointment" id="edit_date_of_appointment" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Firstname</label>
                            <input type="text" name="firstname" id="edit_firstname" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Middlename</label>
                            <input type="text" name="middlename" id="edit_middlename" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Lastname</label>
                            <input type="text" name="lastname" id="edit_lastname" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sex</label>
                            <select name="sex" id="edit_sex" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select name="department" id="edit_department" class="form-select" required>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= e($dept) ?>"><?= e($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Employment Status</label>
                            <select name="employment_status" id="edit_employment_status" class="form-select" required>
                                <?php foreach ($employmentStatuses as $empStatus): ?>
                                    <option value="<?= e($empStatus) ?>"><?= e($empStatus) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="PENDING">PENDING</option>
                                <option value="APPROVED">APPROVED</option>
                                <option value="REJECTED">REJECTED</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Active</label>
                            <select name="is_active" id="edit_is_active" class="form-select" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input
                                type="password"
                                name="new_password"
                                id="edit_new_password"
                                class="form-control"
                                placeholder="Leave blank if you do not want to change the password"
                            >
                            <small class="text-muted">Only fill this in if you want to update the employee password.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.editEmployeeBtn');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.getAttribute('data-id') || '';
            document.getElementById('edit_employee_no').value = this.getAttribute('data-employee_no') || '';
            document.getElementById('edit_firstname').value = this.getAttribute('data-firstname') || '';
            document.getElementById('edit_middlename').value = this.getAttribute('data-middlename') || '';
            document.getElementById('edit_lastname').value = this.getAttribute('data-lastname') || '';
            document.getElementById('edit_date_of_appointment').value = this.getAttribute('data-date_of_appointment') || '';
            document.getElementById('edit_sex').value = this.getAttribute('data-sex') || 'Male';
            document.getElementById('edit_department').value = this.getAttribute('data-department') || '';
            document.getElementById('edit_employment_status').value = this.getAttribute('data-employment_status') || '';
            document.getElementById('edit_status').value = this.getAttribute('data-status') || 'PENDING';
            document.getElementById('edit_is_active').value = this.getAttribute('data-is_active') || '0';
            document.getElementById('edit_new_password').value = '';
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>