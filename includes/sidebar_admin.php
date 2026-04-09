<div class="sidebar">
    <div class="sidebar-brand">
        <h4>HR Panel</h4>
        <small><?= e($_SESSION['admin_name'] ?? '') ?></small>
    </div>

    <ul class="sidebar-menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="pending_registrations.php">Pending Registrations</a></li>
        <li><a href="credentials.php">Credentials</a></li>
        <li><a href="employees.php">Employees</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>