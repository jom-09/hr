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

$vacancies = [
    [
        'position' => 'Administrative Aide',
        'office'   => 'Human Resource Office',
        'status'   => 'Open',
        'type'     => 'Full-time',
        'details'  => 'Assists in records management, document handling, and office support tasks.'
    ],
    [
        'position' => 'IT Support Staff',
        'office'   => 'MIS / ICT Unit',
        'status'   => 'Open',
        'type'     => 'Contractual',
        'details'  => 'Provides technical assistance, system support, and maintenance of office devices and software.'
    ],
    [
        'position' => 'Clerk',
        'office'   => 'Municipal Office',
        'status'   => 'Open',
        'type'     => 'Full-time',
        'details'  => 'Handles encoding, filing, and daily clerical operations.'
    ],
];
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
                    <div class="hero-brand-logo">EC</div>
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
                                <h5>Secure Access</h5>
                                <p>Employee and HR portal with organized account entry points.</p>
                            </div>
                            <div class="hero-info-card">
                                <h5>Centralized Records</h5>
                                <p>Store and manage employee credentials in one system.</p>
                            </div>
                            <div class="hero-info-card">
                                <h5>Hiring Updates</h5>
                                <p>Vacancy posts and announcements can be displayed here.</p>
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
    <section id="vacancies" class="landing-section section-dark">
        <div class="container-fluid px-4 px-lg-5">
            <div class="section-heading text-center text-white">
                <span class="section-tag section-tag-light">Opportunities</span>
                <h2>List of Vacancies</h2>
                <p>Available job openings and hiring opportunities in LGU Aglipay.</p>
            </div>

            <div class="row g-4">
                <?php foreach ($vacancies as $vacancy): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="vacancy-card h-100">
                            <div class="vacancy-top">
                                <span class="vacancy-badge"><?= e($vacancy['status']) ?></span>
                                <span class="vacancy-type"><?= e($vacancy['type']) ?></span>
                            </div>

                            <h4><?= e($vacancy['position']) ?></h4>
                            <h6><?= e($vacancy['office']) ?></h6>
                            <p><?= e($vacancy['details']) ?></p>

                            <div class="vacancy-footer">
                                <a href="employee/register.php" class="btn vacancy-btn">Apply / Register</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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