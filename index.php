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
$stmtVacancies = $pdo->query("SELECT * FROM vacancies WHERE deadline >= CURDATE() ORDER BY deadline ASC, created_at DESC");
$vacancies = $stmtVacancies->fetchAll(PDO::FETCH_ASSOC);
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

    <!-- NAVBAR / TOPBAR -->
    <header class="hero-topbar">
        <div class="container-fluid px-4 px-lg-5">
            <div class="hero-topbar-inner">
                <a href="#home" class="hero-brand">
                    <div class="hero-brand-text">
                        <span class="hero-brand-title"><?= e(APP_NAME) ?></span>
                    </div>
                </a>

                <nav class="hero-nav d-none d-lg-flex">
                    <a href="#home">Home</a>
                    <a href="#announcements">Announcements</a>
                    <a href="#vacancies">Vacancies</a>
                    <a href="#contact">Contact Us</a>
                </nav>

                <div class="hero-auth-buttons">
                    <a href="employee/login.php" class="btn hero-btn hero-btn-light">Employee Login</a>
                    <a href="employee/register.php" class="btn hero-btn hero-btn-outline">Register</a>
                    <a href="admin/login.php" class="btn hero-btn hero-btn-primary">HR Login</a>
                </div>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section id="home" class="hero-main-section">
        <div class="hero-overlay"></div>

        <div class="container-fluid px-4 px-lg-5 position-relative">
            <div class="row align-items-center hero-row">
                <div class="col-lg-7">
                    <div class="hero-content-box">
                        <span class="hero-kicker">Welcome to LGU Aglipay</span>
                        <h1 class="hero-main-title">
                            Employee Credential
                            <span>Management System</span>
                        </h1>

                        <p class="hero-main-text">
                            A centralized and secure web portal for managing employee credentials,
                            registration, records, and important HR-related information of
                            <strong>LGU Aglipay</strong>. This platform helps streamline employee access,
                            document organization, announcements, and hiring information in one place.
                        </p>

                        <div class="hero-quick-info">
                            <div class="hero-info-card">
                                <h5>PRIME HR</h5>
                                <p>Dito ilalagay</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ANNOUNCEMENTS -->
  <section id="announcements" class="landing-section section-light">
    <div class="container-fluid px-4 px-lg-5">
        <div class="section-heading text-center">
            <span class="section-tag">Updates</span>
            <h2>Announcements</h2>
            <p>Important notices, advisories, and memorandums from the LGU Aglipay HR/Admin Office.</p>
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

    <!-- VACANCIES -->
    <section class="vacancies-section py-5" id="vacancies">
    <div class="container">
        <div class="section-title text-center mb-4">
            <h2>Vacancies</h2>
            <p>Available positions open for application.</p>
        </div>

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
                                    <td><?= (int)$vacancy['need_count'] ?></td>
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
    <section id="contact" class="landing-section section-light">
        <div class="container-fluid px-4 px-lg-5">
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
                        <p>San Leonardo,Aglipay, Quirino</p>
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
                            <a href="https://www.facebook.com/" target="_blank">
                                Visit Official Facebook Page
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="landing-footer">
        <div class="container-fluid px-4 px-lg-5">
            <p class="mb-0">
                &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> | LGU Aglipay Employee Credential Management System
            </p>
        </div>
    </footer>

</body>
</html>