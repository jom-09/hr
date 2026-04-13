<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';

$uploadDir = __DIR__ . '/../uploads/announcements/';
$uploadDirDb = 'uploads/announcements/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* -----------------------------
   HANDLE ADD ANNOUNCEMENT
----------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');

    $attachmentName = null;
    $attachmentPath = null;

    if ($title === '' || $body === '') {
        $error = 'Title and announcement body are required.';
    } else {
        if (!empty($_FILES['attachment']['name'])) {
            $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $originalName = $_FILES['attachment']['name'];
            $tmpName = $_FILES['attachment']['tmp_name'];
            $fileSize = $_FILES['attachment']['size'];
            $fileError = $_FILES['attachment']['error'];

            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($fileError !== 0) {
                $error = 'File upload failed.';
            } elseif (!in_array($ext, $allowedExt, true)) {
                $error = 'Only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed.';
            } elseif ($fileSize > 10 * 1024 * 1024) {
                $error = 'Maximum file size is 10MB only.';
            } else {
                $newFileName = 'announcement_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $destination)) {
                    $attachmentName = $originalName;
                    $attachmentPath = $uploadDirDb . $newFileName;
                } else {
                    $error = 'Failed to move uploaded file.';
                }
            }
        }

        if ($error === '') {
            $stmt = $pdo->prepare("
                INSERT INTO announcements (title, body, attachment_name, attachment_path)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$title, $body, $attachmentName, $attachmentPath]);
            $message = 'Announcement posted successfully.';
        }
    }
}

/* -----------------------------
   HANDLE DELETE
----------------------------- */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("SELECT attachment_path FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $ann = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ann) {
        if (!empty($ann['attachment_path'])) {
    $fileToDelete = __DIR__ . '/../' . $ann['attachment_path'];
    if (file_exists($fileToDelete)) {
        @unlink($fileToDelete);
    }
}

        $del = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $del->execute([$id]);
        header("Location: announcements.php?deleted=1");
        exit;
    }
}

/* -----------------------------
   FETCH ANNOUNCEMENTS
----------------------------- */
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-hero">
        <div class="dashboard-hero__content">
            <div class="hero-badge">Admin Panel</div>
            <h1 class="dashboard-title">Announcements</h1>
            <p class="dashboard-subtitle">
                Post important notices, memorandums, and downloadable files for employees and visitors.
            </p>
        </div>
        <div class="dashboard-hero__side">
            <div class="hero-mini-card">
                <span class="hero-mini-label">Module</span>
                <h3>Announcement Manager</h3>
                <p>Create • Publish • Downloadable Attachments</p>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success rounded-4 shadow-sm border-0"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success rounded-4 shadow-sm border-0">Announcement deleted successfully.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger rounded-4 shadow-sm border-0"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Post New Announcement</h4>
                        <p class="card-subtitle-custom">Attachment is optional for memos and documents</p>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control form-control-lg rounded-4" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Announcement Body</label>
                        <textarea name="body" rows="6" class="form-control rounded-4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Attach File <small class="text-muted">(Optional)</small></label>
                        <input type="file" name="attachment" class="form-control rounded-4" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB)</small>
                    </div>

                    <button type="submit" name="add_announcement" class="btn btn-primary rounded-pill px-4 py-2">
                        Post Announcement
                    </button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Posted Announcements</h4>
                        <p class="card-subtitle-custom">Latest announcements visible on the landing page</p>
                    </div>
                </div>

                <?php if (!$announcements): ?>
                    <div class="text-muted">No announcements posted yet.</div>
                <?php else: ?>
                    <div class="announcement-admin-list">
                        <?php foreach ($announcements as $item): ?>
                            <div class="announcement-admin-card">
                                <div class="announcement-admin-card__top">
                                    <div>
                                        <h5 class="mb-1"><?= e($item['title']) ?></h5>
                                        <small class="text-muted"><?= date('F d, Y h:i A', strtotime($item['created_at'])) ?></small>
                                    </div>
                                    <div>
                                        <a href="announcements.php?delete=<?= (int)$item['id'] ?>"
                                           class="btn btn-sm btn-outline-danger rounded-pill"
                                           onclick="return confirm('Delete this announcement?');">
                                            Delete
                                        </a>
                                    </div>
                                </div>

                                <p class="mt-3 mb-3"><?= nl2br(e($item['body'])) ?></p>

                                <?php if (!empty($item['attachment_path'])): ?>
                                    <a href="<?= e($item['attachment_path']) ?>" class="btn btn-sm btn-outline-primary rounded-pill" download>
                                        Download Attachment: <?= e($item['attachment_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">No attachment</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.announcement-admin-list{
    display:flex;
    flex-direction:column;
    gap:16px;
}
.announcement-admin-card{
    background:#fff;
    border:1px solid rgba(0,0,0,0.06);
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.06);
}
.announcement-admin-card__top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    flex-wrap:wrap;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>