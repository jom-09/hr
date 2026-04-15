<div class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-brand">
            <div class="brand-logo">EP</div>
            <div class="brand-text">
                <h4>Employee Panel</h4>
                <small><?= e($_SESSION['employee_name'] ?? '') ?></small>
            </div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="upload.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'upload.php' ? 'active' : '' ?>">
                <span class="menu-text">Upload Credentials</span>
            </a>
        </li>
        <li>
            <a href="my_credentials.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'my_credentials.php' ? 'active' : '' ?>">
                <span class="menu-text">My Credentials</span>
            </a>
        </li>
        <li>
            <a href="logout.php" class="menu-link logout">
                <span class="menu-text">Logout</span>
            </a>
        </li>
    </ul>
</div>