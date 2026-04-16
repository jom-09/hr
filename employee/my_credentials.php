<?php
require_once __DIR__ . '/../includes/auth_employee.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = '';

function credential_label($type) {
    $labels = [
        'PDS' => 'PDS',
        'SALN' => 'SALN',
        'ELIGIBILITY' => 'Eligibility',
        'SEMINAR_CERTIFICATE' => 'Seminar/Certificate',
        'TOR' => 'TOR',
        'APPOINTMENT_PAPER' => 'Appointment Paper'
    ];

    return $labels[$type] ?? $type;
}

/* =========================
   DELETE LOGIC
========================= */
if (is_post() && isset($_POST['delete_id'])) {
    require_csrf();

    $id = (int)$_POST['delete_id'];

    $stmt = $pdo->prepare("SELECT * FROM credentials WHERE id = ? AND employee_id = ?");
    $stmt->execute([$id, $_SESSION['employee_id']]);
    $file = $stmt->fetch();

    if ($file) {
        $dbPath = $file['file_path'];

        // Convert relative path to absolute server path
        $absolutePath = __DIR__ . '/../' . ltrim($dbPath, '/');

        if (!empty($dbPath) && file_exists($absolutePath)) {
            unlink($absolutePath);
        }

        $stmt = $pdo->prepare("DELETE FROM credentials WHERE id = ?");
        $stmt->execute([$id]);

        $success = "Credential deleted successfully.";
    } else {
        $errors[] = "File not found or unauthorized.";
    }
}

/* =========================
   FETCH DATA
========================= */
$stmt = $pdo->prepare("SELECT * FROM credentials WHERE employee_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$_SESSION['employee_id']]);
$data = $stmt->fetchAll();

$csrf = generate_csrf_token();

include __DIR__ . '/../includes/header_employee.php';
?>

<div class="credentials-page">
    <div class="page-topbar">
        <div>
            <p class="page-kicker">Employee Portal</p>
            <h1 class="page-title">My Credentials</h1>
            <p class="page-subtitle">
                View, manage, and access all your uploaded credentials in one place.
            </p>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="custom-alert custom-alert-danger mb-4">
            <?php foreach ($errors as $e): ?>
                <div class="custom-alert-text"><?= e($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="custom-alert custom-alert-success mb-4">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-panel">
        <div class="panel-header">
            <div>
                <h4>Uploaded Credentials</h4>
                <p>List of all your submitted files</p>
            </div>
        </div>

        <div class="panel-body">

            <?php if (!$data): ?>
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <h5>No Credentials Yet</h5>
                    <p>You haven't uploaded any credentials yet.</p>
                    <a href="upload.php" class="btn-empty-action">Upload Now</a>
                </div>
            <?php else: ?>

            <div class="table-responsive-custom">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>File Name</th>
                            <th>Date Uploaded</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td>
                                <span class="badge-type">
                                    <?= e(credential_label($row['credential_type'])) ?>
                                </span>
                            </td>

                            <td class="file-name">
                                <?= e($row['original_name']) ?>
                            </td>

                            <td class="date-text">
                                <?= date('M d, Y', strtotime($row['uploaded_at'])) ?>
                            </td>

                            <td class="text-end action-buttons">
                                <a href="../<?= e($row['file_path']) ?>" target="_blank" class="btn-view">
                                    View
                                </a>

                                <form method="POST" class="delete-form" onsubmit="return confirmDelete();">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                    <input type="hidden" name="delete_id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn-delete">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this file?");
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>