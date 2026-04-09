<?php require_once __DIR__ . '/../config/constants.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/admin_style.css">
</head>
<body>
<div class="main-layout">
    <?php include __DIR__ . '/sidebar_admin.php'; ?>
    <div class="content-area"></div>