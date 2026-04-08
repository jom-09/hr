<?php

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: {$url}");
    exit;
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function old($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        exit('Invalid CSRF token.');
    }
}

function full_name($row) {
    return trim(($row['firstname'] ?? '') . ' ' . ($row['middlename'] ?? '') . ' ' . ($row['lastname'] ?? ''));
}

function is_employee_logged_in() {
    return !empty($_SESSION['employee_id']) && ($_SESSION['user_type'] ?? '') === 'EMPLOYEE';
}

function is_admin_logged_in() {
    return !empty($_SESSION['admin_id']) && ($_SESSION['user_type'] ?? '') === 'ADMIN';
}

function record_login_attempt(PDO $pdo, $userType, $success, $employeeNo = null, $username = null) {
    $ip = get_client_ip();
    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (employee_no, username, ip_address, user_type, success)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $employeeNo,
        $username,
        $ip,
        $userType,
        $success ? 1 : 0
    ]);
}

function is_locked_out(PDO $pdo, $userType, $employeeNo = null, $username = null) {
    $ip = get_client_ip();

    $sql = "
        SELECT COUNT(*) AS failed_count
        FROM login_attempts
        WHERE success = 0
          AND user_type = ?
          AND ip_address = ?
          AND attempted_at >= (NOW() - INTERVAL ? MINUTE)
    ";

    $params = [$userType, $ip, LOGIN_LOCK_MINUTES];

    if ($userType === 'EMPLOYEE' && $employeeNo !== null) {
        $sql .= " AND employee_no = ?";
        $params[] = $employeeNo;
    }

    if ($userType === 'ADMIN' && $username !== null) {
        $sql .= " AND username = ?";
        $params[] = $username;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    return ($row && (int)$row['failed_count'] >= LOGIN_MAX_ATTEMPTS);
}