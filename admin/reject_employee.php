<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE employees SET status = 'REJECTED' WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: pending_registrations.php");
exit;