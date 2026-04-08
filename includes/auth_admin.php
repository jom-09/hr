<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/functions.php';

if (!is_admin_logged_in()) {
    redirect('../admin/login.php');
}