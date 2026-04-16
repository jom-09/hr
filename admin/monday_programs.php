<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_monday_program'])) {
    $department = trim($_POST['department'] ?? '');
    $schedule_date = trim($_POST['schedule_date'] ?? '');

    if ($department === '' || $schedule_date === '') {
        $error = 'Department and schedule date are required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO monday_programs (department, schedule_date)
            VALUES (?, ?)
        ");
        $stmt->execute([$department, $schedule_date]);
        $message = 'Monday program added successfully.';
    }
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $del = $pdo->prepare("DELETE FROM monday_programs WHERE id = ?");
    $del->execute([$id]);

    header("Location: monday_programs.php?deleted=1");
    exit;
}

$mondayPrograms = $pdo->query("
    SELECT *
    FROM monday_programs
    ORDER BY schedule_date DESC, created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-hero">
        <div class="dashboard-hero__content">
            <div class="hero-badge">Admin Panel</div>
            <h1 class="dashboard-title">Monday Programs</h1>
            <p class="dashboard-subtitle">
                Manage department schedules for the Monday Program section of the landing page.
            </p>
        </div>
        <div class="dashboard-hero__side">
            <div class="hero-mini-card">
                <span class="hero-mini-label">Module</span>
                <h3>Activities Manager</h3>
                <p>Create • Schedule • Publish</p>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success rounded-4 shadow-sm border-0"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success rounded-4 shadow-sm border-0">Monday program deleted successfully.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger rounded-4 shadow-sm border-0"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Add Monday Program</h4>
                        <p class="card-subtitle-custom">Input department schedule for the landing page</p>
                    </div>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="schedule_date" class="form-control rounded-4" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department</label>
                        <input type="text" name="department" class="form-control rounded-4" required>
                    </div>

                    <button type="submit" name="add_monday_program" class="btn btn-primary rounded-pill px-4 py-2">
                        Save Monday Program
                    </button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Posted Monday Programs</h4>
                        <p class="card-subtitle-custom">Departments currently listed on the landing page</p>
                    </div>
                </div>

                <?php if (!$mondayPrograms): ?>
                    <div class="text-muted">No Monday programs posted yet.</div>
                <?php else: ?>
                    <div class="activity-admin-list">
                        <?php foreach ($mondayPrograms as $program): ?>
                            <div class="activity-admin-card">
                                <div class="activity-admin-card__top">
                                    <div>
                                        <h5 class="mb-1"><?= e($program['department']) ?></h5>
                                        <small class="text-muted">
                                            <?= !empty($program['schedule_date']) ? date('F d, Y', strtotime($program['schedule_date'])) : '-' ?>
                                        </small>
                                    </div>
                                    <div>
                                        <a href="monday_programs.php?delete=<?= (int)$program['id'] ?>"
                                           class="btn btn-sm btn-outline-danger rounded-pill"
                                           onclick="return confirm('Delete this Monday program?');">
                                            Delete
                                        </a>
                                    </div>
                                </div>

                                <span class="badge bg-light text-dark border mt-3">Department Schedule</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>