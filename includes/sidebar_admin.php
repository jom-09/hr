<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$activitiesOpen = in_array($currentPage, ['calendar_activities.php', 'monday_programs.php']);
?>

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
        <li class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <span>Dashboard</span>
            </a>
        </li>

        <li class="<?= $currentPage === 'pending_registrations.php' ? 'active' : '' ?>">
            <a href="pending_registrations.php">
                <span>Pending Registrations</span>
            </a>
        </li>

        <li class="<?= $currentPage === 'credentials.php' ? 'active' : '' ?>">
            <a href="credentials.php">
                <span>Credentials</span>
            </a>
        </li>

        <li class="<?= $currentPage === 'employees.php' ? 'active' : '' ?>">
            <a href="employees.php">
                <span>Employees</span>
            </a>
        </li>

        <li class="<?= $currentPage === 'announcements.php' ? 'active' : '' ?>">
            <a href="announcements.php">
                <span>Announcements</span>
            </a>
        </li>

        <li class="<?= $currentPage === 'vacancies.php' ? 'active' : '' ?>">
            <a href="vacancies.php">
                <span>Vacancies</span>
            </a>
        </li>

        <li class="has-submenu <?= $activitiesOpen ? 'open active' : '' ?>">
            <button type="button" class="submenu-toggle">
                <span>Activities</span>
                <span class="submenu-arrow"><?= $activitiesOpen ? '▾' : '▸' ?></span>
            </button>

            <ul class="submenu" style="<?= $activitiesOpen ? 'display:block;' : 'display:none;' ?>">
                <li class="<?= $currentPage === 'calendar_activities.php' ? 'active' : '' ?>">
                    <a href="calendar_activities.php">Calendar of Activities</a>
                </li>
                <li class="<?= $currentPage === 'monday_programs.php' ? 'active' : '' ?>">
                    <a href="monday_programs.php">Monday Programs</a>
                </li>
            </ul>
        </li>

        <li class="<?= $currentPage === 'archive.php' ? 'active' : '' ?>">
            <a href="archive.php">
                <span>Archive</span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.submenu-toggle');
    const submenu = document.querySelector('.submenu');
    const arrow = document.querySelector('.submenu-arrow');
    const parent = document.querySelector('.has-submenu');

    if (toggle && submenu && arrow) {
        toggle.addEventListener('click', function () {
            const isOpen = submenu.style.display === 'block';
            submenu.style.display = isOpen ? 'none' : 'block';
            arrow.textContent = isOpen ? '▸' : '▾';

            if (parent) {
                parent.classList.toggle('open', !isOpen);
            }
        });
    }
});
</script>