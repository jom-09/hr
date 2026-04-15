<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$totalEmployees    = (int)$pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
$totalJobOrders    = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE employment_status = 'Job Order'")->fetchColumn();
$totalMale         = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE sex = 'Male'")->fetchColumn();
$totalFemale       = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE sex = 'Female'")->fetchColumn();

$pendingEmployees  = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='PENDING'")->fetchColumn();
$approvedEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='APPROVED'")->fetchColumn();
$totalCredentials  = (int)$pdo->query("SELECT COUNT(*) FROM credentials")->fetchColumn();

$rejectedEmployees = max(0, $totalEmployees - ($pendingEmployees + $approvedEmployees));

$pendingPercent   = $totalEmployees > 0 ? round(($pendingEmployees / $totalEmployees) * 100, 1) : 0;
$approvedPercent  = $totalEmployees > 0 ? round(($approvedEmployees / $totalEmployees) * 100, 1) : 0;
$jobOrderPercent  = $totalEmployees > 0 ? round(($totalJobOrders / $totalEmployees) * 100, 1) : 0;
$malePercent      = $totalEmployees > 0 ? round(($totalMale / $totalEmployees) * 100, 1) : 0;
$femalePercent    = $totalEmployees > 0 ? round(($totalFemale / $totalEmployees) * 100, 1) : 0;

include __DIR__ . '/../includes/header_admin.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-hero">
        <div class="dashboard-hero__content">
            <div class="hero-badge">Admin Panel</div>
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <p class="dashboard-subtitle">
                Welcome back, <strong><?= e($_SESSION['admin_name'] ?? '') ?></strong>.
                Here’s a quick overview of your HR system.
            </p>
        </div>
        <div class="dashboard-hero__side">
            <div class="hero-mini-card">
                <span class="hero-mini-label">System Theme</span>
                <h3>Modern HR Admin</h3>
                <p>Responsive • Clean • Professional</p>
            </div>
        </div>
    </div>

    <div class="row g-4 stats-row">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card--primary">
                <div class="stat-card__top">
                    <span class="stat-card__label">Total Employees</span>
                    <span class="stat-card__icon">👥</span>
                </div>
                <div class="stat-card__value"><?= number_format($totalEmployees) ?></div>
                <div class="stat-card__meta">All registered employee records</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card--warning">
                <div class="stat-card__top">
                    <span class="stat-card__label">Total Job Orders</span>
                    <span class="stat-card__icon">🧾</span>
                </div>
                <div class="stat-card__value"><?= number_format($totalJobOrders) ?></div>
                <div class="stat-card__meta"><?= $jobOrderPercent ?>% of total employees</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card--success">
                <div class="stat-card__top">
                    <span class="stat-card__label">Total Male</span>
                    <span class="stat-card__icon">👨</span>
                </div>
                <div class="stat-card__value"><?= number_format($totalMale) ?></div>
                <div class="stat-card__meta"><?= $malePercent ?>% of total employees</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card--neutral">
                <div class="stat-card__top">
                    <span class="stat-card__label">Total Female</span>
                    <span class="stat-card__icon">👩</span>
                </div>
                <div class="stat-card__value"><?= number_format($totalFemale) ?></div>
                <div class="stat-card__meta"><?= $femalePercent ?>% of total employees</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-7">
            <div class="dashboard-card chart-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Employee Status Overview</h4>
                        <p class="card-subtitle-custom">Visual summary of employee and credential counts</p>
                    </div>
                </div>
                <div class="chart-wrap chart-wrap--bar">
                    <canvas id="employeeBarChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="dashboard-card chart-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Sex Distribution</h4>
                        <p class="card-subtitle-custom">Male and female employee breakdown</p>
                    </div>
                </div>
                <div class="chart-wrap chart-wrap--doughnut">
                    <canvas id="employeeDoughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-xl-8">
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">System Summary Table</h4>
                        <p class="card-subtitle-custom">Clean and mobile-friendly admin overview</p>
                    </div>
                </div>

                <div class="table-responsive custom-table-wrap">
                    <table class="table custom-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Total</th>
                                <th>Details</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="table-title">Employees</div>
                                    <div class="table-text-muted">All employee accounts</div>
                                </td>
                                <td><?= number_format($totalEmployees) ?></td>
                                <td>Total registered employees in the system</td>
                                <td><span class="status-badge status-badge--blue">Active Monitor</span></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="table-title">Job Orders</div>
                                    <div class="table-text-muted">Job Order employees</div>
                                </td>
                                <td><?= number_format($totalJobOrders) ?></td>
                                <td><?= $jobOrderPercent ?>% of all employees are Job Orders</td>
                                <td><span class="status-badge status-badge--yellow">Tracked</span></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="table-title">Male Employees</div>
                                    <div class="table-text-muted">Male employee records</div>
                                </td>
                                <td><?= number_format($totalMale) ?></td>
                                <td><?= $malePercent ?>% of all employees are male</td>
                                <td><span class="status-badge status-badge--green">Updated</span></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="table-title">Female Employees</div>
                                    <div class="table-text-muted">Female employee records</div>
                                </td>
                                <td><?= number_format($totalFemale) ?></td>
                                <td><?= $femalePercent ?>% of all employees are female</td>
                                <td><span class="status-badge status-badge--dark">Updated</span></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="table-title">Pending</div>
                                    <div class="table-text-muted">Awaiting approval</div>
                                </td>
                                <td><?= number_format($pendingEmployees) ?></td>
                                <td><?= $pendingPercent ?>% of all employees are pending</td>
                                <td><span class="status-badge status-badge--yellow">Needs Review</span></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="table-title">Approved</div>
                                    <div class="table-text-muted">Verified employees</div>
                                </td>
                                <td><?= number_format($approvedEmployees) ?></td>
                                <td><?= $approvedPercent ?>% of all employees are approved</td>
                                <td><span class="status-badge status-badge--green">Stable</span></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="table-title">Credentials</div>
                                    <div class="table-text-muted">Stored files and records</div>
                                </td>
                                <td><?= number_format($totalCredentials) ?></td>
                                <td>Credential records currently saved in database</td>
                                <td><span class="status-badge status-badge--dark">Tracked</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="dashboard-card quick-actions-card">
                <div class="card-header-custom">
                    <div>
                        <h4 class="card-title-custom">Quick Actions</h4>
                        <p class="card-subtitle-custom">Fast navigation for admin tasks</p>
                    </div>
                </div>

                <div class="quick-actions-grid">
                    <a href="pending_registrations.php" class="quick-action-box">
                        <span class="quick-action-icon">📝</span>
                        <span class="quick-action-title">Review Pending</span>
                        <small><?= number_format($pendingEmployees) ?> waiting</small>
                    </a>

                    <a href="employees.php" class="quick-action-box">
                        <span class="quick-action-icon">👨‍💼</span>
                        <span class="quick-action-title">Manage Employees</span>
                        <small>View all records</small>
                    </a>

                    <a href="credentials.php" class="quick-action-box">
                        <span class="quick-action-icon">📂</span>
                        <span class="quick-action-title">View Credentials</span>
                        <small><?= number_format($totalCredentials) ?> stored</small>
                    </a>

                    <a href="archive.php" class="quick-action-box">
                        <span class="quick-action-icon">🗃️</span>
                        <span class="quick-action-title">Open Archive</span>
                        <small>Manage archived records</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Local Chart.js only -->
<script src="../assets/chart.js/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const textColor = '#28396C';
    const gridColor = 'rgba(40, 57, 108, 0.10)';

    const employeeBarCtx = document.getElementById('employeeBarChart');
    if (employeeBarCtx) {
        new Chart(employeeBarCtx, {
            type: 'bar',
            data: {
                labels: ['Employees', 'Job Orders', 'Male', 'Female', 'Credentials'],
                datasets: [{
                    label: 'System Count',
                    data: [<?= $totalEmployees ?>, <?= $totalJobOrders ?>, <?= $totalMale ?>, <?= $totalFemale ?>, <?= $totalCredentials ?>],
                    backgroundColor: [
                        'rgba(40, 57, 108, 0.82)',
                        'rgba(212, 172, 13, 0.75)',
                        'rgba(46, 125, 50, 0.78)',
                        'rgba(13, 110, 253, 0.75)',
                        'rgba(108, 117, 125, 0.82)'
                    ],
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 56
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#28396C',
                        padding: 12,
                        cornerRadius: 10
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            precision: 0
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            }
        });
    }

    const employeeDoughnutCtx = document.getElementById('employeeDoughnutChart');
    if (employeeDoughnutCtx) {
        new Chart(employeeDoughnutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?= $totalMale ?>, <?= $totalFemale ?>],
                    backgroundColor: [
                        'rgba(46, 125, 50, 0.82)',
                        'rgba(13, 110, 253, 0.82)'
                    ],
                    borderColor: '#F6F4E8',
                    borderWidth: 6,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            boxWidth: 14,
                            padding: 18,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#28396C',
                        padding: 12,
                        cornerRadius: 10
                    }
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>