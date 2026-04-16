<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_calendar_activity'])) {
    $title = trim($_POST['title'] ?? '');
    $activity_date = trim($_POST['activity_date'] ?? '');
    $activity_time = trim($_POST['activity_time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $activity_date === '') {
        $error = 'Activity title and date are required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO calendar_activities (title, activity_date, activity_time, venue, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $activity_date, $activity_time, $venue, $description]);
        $message = 'Calendar activity added successfully.';
    }
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $del = $pdo->prepare("DELETE FROM calendar_activities WHERE id = ?");
    $del->execute([$id]);

    header("Location: calendar_activities.php?deleted=1");
    exit;
}

$calendarActivities = $pdo->query("
    SELECT *
    FROM calendar_activities
    ORDER BY activity_date DESC, created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-hero">
        <div class="dashboard-hero__content">
            <div class="hero-badge">Admin Panel</div>
            <h1 class="dashboard-title">Calendar of Activities</h1>
            <p class="dashboard-subtitle">
                Manage scheduled activities that will appear on the landing page.
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
        <div class="alert alert-success rounded-4 shadow-sm border-0">Calendar activity deleted successfully.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger rounded-4 shadow-sm border-0"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Add Calendar Activity</h4>
                        <p class="card-subtitle-custom">Input activity details for the landing page</p>
                    </div>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Activity Title</label>
                        <input type="text" name="title" class="form-control form-control-lg rounded-4" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="activity_date" class="form-control rounded-4" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Time</label>
                        <input type="text" name="activity_time" class="form-control rounded-4" placeholder="e.g. 8:00 AM - 10:00 AM">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Venue</label>
                        <input type="text" name="venue" class="form-control rounded-4">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="5" class="form-control rounded-4"></textarea>
                    </div>

                    <button type="submit" name="add_calendar_activity" class="btn btn-primary rounded-pill px-4 py-2">
                        Save Calendar Activity
                    </button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Posted Activities</h4>
                        <p class="card-subtitle-custom">Latest scheduled activities visible on the landing page</p>
                    </div>
                </div>

                <?php if (!$calendarActivities): ?>
                    <div class="text-muted">No calendar activities posted yet.</div>
                <?php else: ?>
                    <div class="activity-admin-list">
                        <?php foreach ($calendarActivities as $activity): ?>
                            <div class="activity-admin-card">
                                <div class="activity-admin-card__top">
                                    <div>
                                        <h5 class="mb-1"><?= e($activity['title']) ?></h5>
                                        <small class="text-muted">
                                            <?= !empty($activity['activity_date']) ? date('F d, Y', strtotime($activity['activity_date'])) : '-' ?>
                                            <?php if (!empty($activity['activity_time'])): ?>
                                                • <?= e($activity['activity_time']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <a href="calendar_activities.php?delete=<?= (int)$activity['id'] ?>"
                                           class="btn btn-sm btn-outline-danger rounded-pill"
                                           onclick="return confirm('Delete this calendar activity?');">
                                            Delete
                                        </a>
                                    </div>
                                </div>

                                <?php if (!empty($activity['venue'])): ?>
                                    <p class="mt-3 mb-2"><strong>Venue:</strong> <?= e($activity['venue']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($activity['description'])): ?>
                                    <p class="mb-0"><?= nl2br(e($activity['description'])) ?></p>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">No description</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>