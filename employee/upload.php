<?php
require_once __DIR__ . '/../includes/auth_employee.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = '';

$allowed_ext = ['jpg','jpeg','png','pdf','doc','docx','xls','xlsx'];

if (is_post()) {
    require_csrf();

    $type = $_POST['credential_type'] ?? '';
    $file = $_FILES['file'] ?? null;

    if (!$type) $errors[] = 'Credential type is required.';
    if (!$file || $file['error'] !== 0) $errors[] = 'File upload failed.';

    if (!$errors) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $errors[] = 'Invalid file type.';
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $errors[] = 'File too large (max 10MB).';
        }
    }

    if (!$errors) {
        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = '../assets/uploads/credentials/' . $newName;
        $relativePath = 'assets/uploads/credentials/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $path)) {

            $stmt = $pdo->prepare("
                INSERT INTO credentials
                (employee_id, credential_type, original_name, stored_name, file_path, file_ext, mime_type, file_size)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['employee_id'],
                $type,
                $file['name'],
                $newName,
                $path,
                $ext,
                $file['type'],
                $file['size']
            ]);

            $success = "File uploaded successfully!";
        } else {
            $errors[] = 'Failed to save file.';
        }
    }
}

$csrf = generate_csrf_token();
include __DIR__ . '/../includes/header_employee.php';
?>

<h3 class="mb-4">Upload Credential</h3>

<?php if ($errors): ?>
<div class="alert alert-danger">
<?php foreach ($errors as $e): ?>
    <div><?= e($e) ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

    <div class="mb-3">
        <label>Credential Type</label>
        <select name="credential_type" class="form-control" required>
            <option value="">Select</option>
            <option value="PDS">PDS</option>
            <option value="SALN">SALN</option>
            <option value="ELIGIBILITY">Eligibility</option>
            <option value="SEMINAR_CERTIFICATE">Seminar/Certificate</option>
            <option value="TOR">TOR</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Upload File</label>
        <input type="file" name="file" class="form-control" required>
    </div>

    <button class="btn btn-primary">Upload</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>