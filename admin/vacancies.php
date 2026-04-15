<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vacancy'])) {
    $position = trim($_POST['position'] ?? '');
    $employment_status = trim($_POST['employment_status'] ?? '');
    $department_office = trim($_POST['department_office'] ?? '');
    $need_count = (int)($_POST['need_count'] ?? 0);
    $qualification = trim($_POST['qualification'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $duties_functions = trim($_POST['duties_functions'] ?? '');

    if (
        $position === '' ||
        $employment_status === '' ||
        $department_office === '' ||
        $need_count <= 0 ||
        $qualification === '' ||
        $deadline === '' ||
        $duties_functions === ''
    ) {
        $error = 'Please fill in all fields properly.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO vacancies (
                    position,
                    employment_status,
                    department_office,
                    need_count,
                    qualification,
                    deadline,
                    duties_functions
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $position,
                $employment_status,
                $department_office,
                $need_count,
                $qualification,
                $deadline,
                $duties_functions
            ]);

            header('Location: vacancies.php?success=added');
            exit;
        } catch (Throwable $e) {
            $error = 'Failed to save vacancy.';
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = (int)($_GET['delete'] ?? 0);

    if ($delete_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM vacancies WHERE id = ?");
            $stmt->execute([$delete_id]);

            header('Location: vacancies.php?success=deleted');
            exit;
        } catch (Throwable $e) {
            $error = 'Failed to delete vacancy.';
        }
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') {
        $success = 'Vacancy posted successfully.';
    } elseif ($_GET['success'] === 'deleted') {
        $success = 'Vacancy deleted successfully.';
    }
}

$vacancies = [];
try {
    $stmt = $pdo->query("
        SELECT *
        FROM vacancies
        ORDER BY created_at DESC
    ");
    $vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = 'Failed to fetch vacancies.';
}

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="content-inner">
    <div class="admin-page">
        <div class="page-hero page-hero--compact">
            <div class="page-hero__content">
                <div class="hero-badge">Admin Vacancy Manager</div>
                <h1 class="page-title">Vacancies</h1>
                <p class="page-subtitle">
                    Add and manage available job openings for posting on the landing page.
                </p>
            </div>

            <div class="page-hero__side">
                <div class="hero-mini-card">
                    <span class="hero-mini-label">Active Vacancy Posts</span>
                    <h3><?= number_format(count($vacancies)) ?></h3>
                    <p>Total vacancy records currently posted</p>
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
                    <h4 class="card-title-custom">Post New Vacancy</h4>
                    <p class="card-subtitle-custom">Fill out the vacancy details below.</p>
                </div>
            </div>

            <div class="vacancy-form-wrap">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label vacancy-form-label">Position</label>
                            <input type="text" name="position" class="form-control vacancy-input" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label vacancy-form-label">Employment Status</label>
                            <input type="text" name="employment_status" class="form-control vacancy-input" placeholder="Permanent / Casual / Job Order / Contractual" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label vacancy-form-label">Department / Office</label>
                            <input type="text" name="department_office" class="form-control vacancy-input" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label vacancy-form-label">Need</label>
                            <input type="number" name="need_count" class="form-control vacancy-input" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label vacancy-form-label">Deadline</label>
                            <input type="date" name="deadline" class="form-control vacancy-input" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label vacancy-form-label">Qualification</label>
                            <textarea name="qualification" class="form-control vacancy-input vacancy-textarea" rows="4" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label vacancy-form-label">Duties and Functions</label>
                            <textarea name="duties_functions" class="form-control vacancy-input vacancy-textarea" rows="4" required></textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="add_vacancy" class="btn primary-btn">
                                <i class="fas fa-plus-circle me-2"></i>Post Vacancy
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="dashboard-card mt-4">
            <div class="card-header-custom card-header-custom--stack-mobile">
                <div>
                    <h4 class="card-title-custom">Posted Vacancies</h4>
                    <p class="card-subtitle-custom">All current vacancy posts are listed below.</p>
                </div>
                <div class="table-top-badge"><?= number_format(count($vacancies)) ?> Records</div>
            </div>

            <div class="table-responsive custom-table-wrap">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Position</th>
                            <th>Employment Status</th>
                            <th>Department / Office</th>
                            <th>Need</th>
                            <th>Qualification</th>
                            <th>Deadline</th>
                            <th>Duties and Functions</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($vacancies): ?>
                            <?php foreach ($vacancies as $index => $vacancy): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= e($vacancy['position']) ?></td>
                                    <td><?= e($vacancy['employment_status']) ?></td>
                                    <td><?= e($vacancy['department_office']) ?></td>
                                    <td><?= (int)$vacancy['need_count'] ?></td>
                                    <td><?= nl2br(e($vacancy['qualification'])) ?></td>
                                    <td><?= e(date('F d, Y', strtotime($vacancy['deadline']))) ?></td>
                                    <td><?= nl2br(e($vacancy['duties_functions'])) ?></td>
                                    <td class="text-center">
                                        <div class="action-icon-group">
                                            <a href="vacancies.php?delete=<?= (int)$vacancy['id'] ?>"
                                               class="icon-action-btn icon-action-btn--delete"
                                               title="Delete Vacancy"
                                               onclick="return confirm('Delete this vacancy?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">💼</div>
                                        <div class="empty-state__title">No vacancies posted yet</div>
                                        <div class="empty-state__text">Your posted vacancies will appear here.</div>
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