<div class="sidebar">
    <div class="sidebar-brand">
        <h4>Employee Panel</h4>
        <small><?= e($_SESSION['employee_name'] ?? '') ?></small>
    </div>

    <ul class="sidebar-menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="upload.php">Upload Credentials</a></li>
        <li><a href="my_credentials.php">My Credentials</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>