<div class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand__logo">HR</div>
        <div class="sidebar-brand__text">
            <h4>HR Panel</h4>
            <small><?= e($_SESSION['admin_name'] ?? '') ?></small>
        </div>
    </div>

    <div class="sidebar-section-title">Navigation</div>

    <ul class="sidebar-menu">
        <li class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="<?= basename($_SERVER['PHP_SELF']) === 'pending_registrations.php' ? 'active' : '' ?>">
            <a href="pending_registrations.php">
                <span class="menu-icon">📝</span>
                <span>Pending Registrations</span>
            </a>
        </li>

        <li class="<?= basename($_SERVER['PHP_SELF']) === 'credentials.php' ? 'active' : '' ?>">
            <a href="credentials.php">
                <span class="menu-icon">📁</span>
                <span>Credentials</span>
            </a>
        </li>

        <li class="<?= basename($_SERVER['PHP_SELF']) === 'employees.php' ? 'active' : '' ?>">
            <a href="employees.php">
                <span class="menu-icon">👨‍💼</span>
                <span>Employees</span>
            </a>
        </li>

        <li class="logout-item">
            <a href="logout.php">
                <span class="menu-icon">↪</span>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>