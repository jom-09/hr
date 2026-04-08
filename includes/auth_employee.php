<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/functions.php';

if (!is_employee_logged_in()) {
    redirect('../employee/login.php');
}