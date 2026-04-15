<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
}

if (is_employee_logged_in()) {
    redirect('employee/dashboard.php');
}

$announcements = $pdo->query("
    SELECT * 
    FROM announcements
    ORDER BY created_at DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$vacancies = [];
$stmtVacancies = $pdo->query("
    SELECT * 
    FROM vacancies 
    WHERE deadline >= CURDATE() 
    ORDER BY deadline ASC, created_at DESC
");
$vacancies = $stmtVacancies->fetchAll(PDO::FETCH_ASSOC);

$calendarActivities = [];
$mondayPrograms = [];

try {
    $stmtCalendar = $pdo->query("
        SELECT *
        FROM calendar_activities
        ORDER BY activity_date ASC, created_at DESC
    ");
    $calendarActivities = $stmtCalendar->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $calendarActivities = [];
}

try {
    $stmtMonday = $pdo->query("
        SELECT *
        FROM monday_programs
        ORDER BY schedule_date ASC, created_at DESC
    ");
    $mondayPrograms = $stmtMonday->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $mondayPrograms = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/employee_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="ecms-landing-body">

    <header class="hero-topbar" id="mainHeader">
        <div class="container-fluid px-4 px-xl-5">
            <div class="hero-topbar-inner">

                <div class="hero-topbar-left">
                </div>

                <div class="hero-topbar-center">
                    <nav class="hero-nav d-none d-lg-flex" id="mainNav">
                        <a href="#home" class="nav-link-item active">Home</a>
                        <a href="#about" class="nav-link-item">About</a>
                        <a href="#announcements" class="nav-link-item">Announcements</a>
                        <a href="#calendar" class="nav-link-item">Calendar of Activities</a>
                        <a href="#vacancies" class="nav-link-item">Vacancies</a>
                        <a href="#contact" class="nav-link-item">Contact Us</a>
                    </nav>
                </div>

                <div class="hero-topbar-right">
                    <div class="hero-auth-buttons">
                        <a href="employee/login.php" class="btn hero-btn hero-btn-light">Employee Login</a>
                        <a href="employee/register.php" class="btn hero-btn hero-btn-outline">Register</a>
                        <a href="admin/login.php" class="btn hero-btn hero-btn-primary">HR Login</a>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- HOME -->
    <section id="home" class="hero-main-section section-hero-alt">
        <div class="hero-overlay"></div>
        <div class="container-fluid px-4 px-xl-5 position-relative">
            <div class="row justify-content-center align-items-center hero-row text-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="hero-content-box home-welcome-box">
                        <h1 class="hero-main-title">
                            Welcome to
                            <span><?= e(APP_NAME) ?></span>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="about" class="landing-section section-theme-light">
        <div class="section-bg section-bg-about"></div>
        <div class="container-fluid px-4 px-xl-5 position-relative">
            <div class="section-heading text-center">
                <span class="section-tag">About</span>
                <h2>About Us</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-11">
                    <div class="about-card">
                        <div class="row g-4 align-items-stretch">

                            <div class="col-lg-6">
                                <div class="about-side-box h-100">
                                    <div class="about-mini-card">
                                        <i class="bi bi-person-badge"></i>
                                        <div>
                                            <h5>Employee Records</h5>
                                            <p>Centralized employee information, profile details, and credential monitoring.</p>
                                        </div>
                                    </div>

                                    <div class="about-mini-card">
                                        <i class="bi bi-megaphone"></i>
                                        <div>
                                            <h5>Announcements</h5>
                                            <p>Official notices, advisories, and important HR related updates in one place.</p>
                                        </div>
                                    </div>

                                    <div class="about-mini-card">
                                        <i class="bi bi-calendar-event"></i>
                                        <div>
                                            <h5>Calendar of Activities</h5>
                                            <p>Easy access to upcoming activities, schedules, and Monday program details.</p>
                                        </div>
                                    </div>

                                    <div class="about-mini-card">
                                        <i class="bi bi-briefcase"></i>
                                        <div>
                                            <h5>Vacancy Posting</h5>
                                            <p>View available positions, qualifications, office assignments, and deadlines.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="about-content h-100">
                                    <span class="about-label">PRIME HR</span>
                                    <h3>PRIME HR</h3>
                                    <p>The Civil Service Commission (CSC) through Director Elpidio S. Bunagan, Jr. of CSC Field Office - Quirino and Nueva Vizcaya
                                        bestows excellence in Human Resources Management (Prime HRM) - Bronze Award to the Municipality of Aglipay represented by 
                                        our Local Chief Executive Jerry T. Agsalda.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ANNOUNCEMENTS -->
    <section id="announcements" class="landing-section section-theme-dark">
        <div class="section-bg section-bg-announcements"></div>
        <div class="container-fluid px-4 px-xl-5 position-relative">
            <div class="section-heading text-center">
                <span class="section-tag">Updates</span>
                <h2>Announcements</h2>
            </div>

            <div class="row g-4">
                <?php if (!empty($announcements)): ?>
                    <?php foreach ($announcements as $item): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="announcement-card h-100">
                                <div class="announcement-date">
                                    <i class="bi bi-calendar-event"></i>
                                    <span><?= date('F d, Y', strtotime($item['created_at'])) ?></span>
                                </div>

                                <h4><?= e($item['title']) ?></h4>
                                <p><?= nl2br(e($item['body'])) ?></p>

                                <?php if (!empty($item['attachment_path'])): ?>
                                    <div class="mt-3">
                                        <a href="<?= e($item['attachment_path']) ?>" class="btn btn-sm btn-outline-primary rounded-pill" download>
                                            <i class="bi bi-download me-1"></i>
                                            Download Attachment
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="announcement-card text-center">
                            <h4>No announcements yet</h4>
                            <p>Please check back later for updates and official notices.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CALENDAR OF ACTIVITIES -->
    <section id="calendar" class="landing-section section-theme-light">
        <div class="section-bg section-bg-calendar"></div>
        <div class="container-fluid px-4 px-xl-5 position-relative">
            <div class="section-heading text-center">
                <span class="section-tag">Schedules</span>
                <h2>Calendar of Activities</h2>
            </div>

            <!-- KEEP TABLE FORMAT AS IS -->
            <div class="calendar-table-wrap mb-5">
                <div class="table-responsive">
                    <table class="table calendar-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Venue</th>
                                <th>Time</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($calendarActivities)): ?>
                                <?php foreach ($calendarActivities as $activity): ?>
                                    <tr>
                                        <td><?= !empty($activity['activity_date']) ? e(date('F d, Y', strtotime($activity['activity_date']))) : '-' ?></td>
                                        <td><strong><?= e($activity['title'] ?? '') ?></strong></td>
                                        <td><?= e($activity['venue'] ?? '-') ?></td>
                                        <td><?= e($activity['activity_time'] ?? '-') ?></td>
                                        <td><?= nl2br(e($activity['description'] ?? '-')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No calendar activities available yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section-heading text-center monday-program-heading">
                <span class="section-tag">Weekly Program</span>
                <h2>Schedule of Monday Program</h2>
            </div>

            <!-- KEEP TABLE FORMAT AS IS -->
            <div class="calendar-table-wrap">
                <div class="table-responsive">
                    <table class="table calendar-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Program</th>
                                <th>Venue</th>
                                <th>Time</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mondayPrograms)): ?>
                                <?php foreach ($mondayPrograms as $program): ?>
                                    <tr>
                                        <td><?= !empty($program['schedule_date']) ? e(date('F d, Y', strtotime($program['schedule_date']))) : '-' ?></td>
                                        <td><strong><?= e($program['title'] ?? '') ?></strong></td>
                                        <td><?= e($program['venue'] ?? '-') ?></td>
                                        <td><?= e($program['schedule_time'] ?? '-') ?></td>
                                        <td><?= nl2br(e($program['description'] ?? '-')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No Monday program available yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- VACANCIES -->
    <section class="vacancies-section landing-section section-theme-dark" id="vacancies">
        <div class="section-bg section-bg-vacancies"></div>
        <div class="container-fluid px-4 px-xl-5 position-relative">
            <div class="section-heading text-center mb-4">
                <span class="section-tag">Hiring</span>
                <h2>Vacancies</h2>
                <p>Available positions open for application.</p>
            </div>

            <!-- KEEP TABLE FORMAT AS IS -->
            <div class="vacancies-table-wrap">
                <div class="table-responsive">
                    <table class="table vacancies-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Employment Status</th>
                                <th>Department / Office</th>
                                <th>Need</th>
                                <th>Qualification</th>
                                <th>Deadline</th>
                                <th>Duties and Functions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vacancies)): ?>
                                <?php foreach ($vacancies as $vacancy): ?>
                                    <tr>
                                        <td><strong><?= e($vacancy['position']) ?></strong></td>
                                        <td><?= e($vacancy['employment_status']) ?></td>
                                        <td><?= e($vacancy['department_office']) ?></td>
                                        <td><?= (int) ($vacancy['need_count'] ?? 0) ?></td>
                                        <td><?= nl2br(e($vacancy['qualification'])) ?></td>
                                        <td><?= e(date('F d, Y', strtotime($vacancy['deadline']))) ?></td>
                                        <td><?= nl2br(e($vacancy['duties_functions'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No vacancies available at the moment.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" class="landing-section section-theme-light">
        <div class="section-bg section-bg-contact"></div>
        <div class="container-fluid px-4 px-xl-5 position-relative">
            <div class="section-heading text-center">
                <span class="section-tag">Get in Touch</span>
                <h2>Contact Us</h2>
                <p>You may contact LGU Aglipay through the details below.</p>
            </div>

            <div class="row justify-content-center g-4">
                <div class="col-md-4">
                    <div class="contact-card text-center h-100">
                        <div class="contact-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h5>Address</h5>
                        <p>San Leonardo, Aglipay, Quirino</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="contact-card text-center h-100">
                        <div class="contact-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <h5>Email Us</h5>
                        <p>
                            <a href="mailto:lguaglipayhr@example.com">lguaglipayhr@example.com</a>
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="contact-card text-center h-100">
                        <div class="contact-icon">
                            <i class="bi bi-facebook"></i>
                        </div>
                        <h5>Facebook</h5>
                        <p>
                            <a href="https://www.facebook.com/" target="_blank">Visit Official Facebook Page</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <div class="container-fluid px-4 px-xl-5">
            <p class="mb-0">
                &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> | LGU Aglipay Employee Credential Management System
            </p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('#mainNav .nav-link-item');
        const sections = document.querySelectorAll('section[id]');
        const header = document.getElementById('mainHeader');

        function activateLink(sectionId) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + sectionId) {
                    link.classList.add('active');
                }
            });
        }

        function updateHeaderState() {
            if (window.scrollY > 20) {
                header.classList.add('is-scrolled');
            } else {
                header.classList.remove('is-scrolled');
            }
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    activateLink(entry.target.id);
                }
            });
        }, {
            rootMargin: '-35% 0px -45% 0px',
            threshold: 0.05
        });

        sections.forEach(section => observer.observe(section));
        updateHeaderState();
        window.addEventListener('scroll', updateHeaderState);
    });
    </script>

</body>
</html>