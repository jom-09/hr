<?php
require_once __DIR__ . '/../includes/auth_employee.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = '';

$allowed_ext = ['jpg','jpeg','png','pdf','doc','docx','xls','xlsx'];
$allowed_types = ['PDS', 'SALN', 'ELIGIBILITY', 'SEMINAR_CERTIFICATE', 'TOR', 'APPOINTMENT_PAPER'];

if (is_post()) {
    require_csrf();

    $type = $_POST['credential_type'] ?? '';
    $file = $_FILES['file'] ?? null;

    if (!$type) {
        $errors[] = 'Credential type is required.';
    } elseif (!in_array($type, $allowed_types, true)) {
        $errors[] = 'Invalid credential type selected.';
    }

    if (!$file || $file['error'] !== 0) {
        $errors[] = 'File upload failed.';
    }

    if (!$errors) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext, true)) {
            $errors[] = 'Invalid file type.';
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $errors[] = 'File too large (max 10MB).';
        }
    }

    if (!$errors) {
        $uploadDir = __DIR__ . '/../assets/uploads/credentials/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        $absolutePath = $uploadDir . $newName;
        $relativePath = 'assets/uploads/credentials/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $absolutePath)) {

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
                $relativePath,
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

<div class="upload-page">
    <div class="page-topbar">
        <div>
            <p class="page-kicker">Employee Portal</p>
            <h1 class="page-title">Upload Credential</h1>
            <p class="page-subtitle">
                Submit your required files and keep your employee records updated and organized.
            </p>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="custom-alert custom-alert-danger mb-4">
            <div class="custom-alert-title">Upload Error</div>
            <?php foreach ($errors as $e): ?>
                <div class="custom-alert-text"><?= e($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="custom-alert custom-alert-success mb-4">
            <div class="custom-alert-title">Success</div>
            <div class="custom-alert-text"><?= e($success) ?></div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-panel upload-panel">
                <div class="panel-header">
                    <div>
                        <h4>Credential Submission Form</h4>
                        <p>Fill in the required details and upload your document.</p>
                    </div>
                </div>

                <div class="panel-body">
                    <form method="POST" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

                        <div class="form-group-custom mb-4">
                            <label for="credential_type" class="form-label-custom">Credential Type</label>
                            <select name="credential_type" id="credential_type" class="form-control-custom" required>
                                <option value="">Select credential type</option>
                                <option value="PDS" <?= (($_POST['credential_type'] ?? '') === 'PDS') ? 'selected' : '' ?>>PDS</option>
                                <option value="SALN" <?= (($_POST['credential_type'] ?? '') === 'SALN') ? 'selected' : '' ?>>SALN</option>
                                <option value="ELIGIBILITY" <?= (($_POST['credential_type'] ?? '') === 'ELIGIBILITY') ? 'selected' : '' ?>>Eligibility</option>
                                <option value="SEMINAR_CERTIFICATE" <?= (($_POST['credential_type'] ?? '') === 'SEMINAR_CERTIFICATE') ? 'selected' : '' ?>>Seminar/Certificate</option>
                                <option value="TOR" <?= (($_POST['credential_type'] ?? '') === 'TOR') ? 'selected' : '' ?>>TOR</option>
                                <option value="APPOINTMENT_PAPER" <?= (($_POST['credential_type'] ?? '') === 'APPOINTMENT_PAPER') ? 'selected' : '' ?>>Appointment Paper</option>
                            </select>
                        </div>

                        <div class="form-group-custom mb-4">
                            <label for="file" class="form-label-custom">Upload File</label>
                            <div class="file-upload-box">
                                <div class="file-upload-icon">📎</div>
                                <div class="file-upload-text">
                                    <strong>Choose a file to upload</strong>
                                    <span>Supported: JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, XLSX</span>
                                </div>
                                <input type="file" name="file" id="file" class="form-control-file-custom" required>
                            </div>
                        </div>

                        <div class="upload-actions">
                            <button type="submit" class="btn-submit-upload">Upload Credential</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-panel upload-info-panel h-100">
                <div class="panel-header">
                    <div>
                        <h4>Upload Guidelines</h4>
                        <p>Please review before uploading your file</p>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-title">Maximum File Size</span>
                            <strong>10MB</strong>
                        </div>

                        <div class="info-item">
                            <span class="info-title">Allowed File Types</span>
                            <strong>JPG, PNG, PDF, DOC, XLS</strong>
                        </div>

                        <div class="info-item">
                            <span class="info-title">Account Owner</span>
                            <strong><?= e($_SESSION['employee_name'] ?? '') ?></strong>
                        </div>
                    </div>

                    <div class="info-message mt-3">
                        Make sure the uploaded file is clear, complete, and correct before submission.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>