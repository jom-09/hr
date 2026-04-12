<?php
require_once __DIR__ . '/../includes/auth_employee.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$employeeId = $_SESSION['employee_id'];

/* =========================
   TOTAL UPLOADS
========================= */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM credentials WHERE employee_id = ?");
$stmt->execute([$employeeId]);
$totalUploads = (int)$stmt->fetchColumn();

/* =========================
   UPLOADS THIS MONTH
========================= */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM credentials
    WHERE employee_id = ?
      AND YEAR(created_at) = YEAR(CURDATE())
      AND MONTH(created_at) = MONTH(CURDATE())
");
$stmt->execute([$employeeId]);
$uploadsThisMonth = (int)$stmt->fetchColumn();

/* =========================
   LAST 6 MONTHS UPLOAD TREND
========================= */
$labels = [];
$data = [];

for ($i = 5; $i >= 0; $i--) {
    $monthLabel = date('M Y', strtotime("-$i months"));
    $monthNum   = date('m', strtotime("-$i months"));
    $yearNum    = date('Y', strtotime("-$i months"));

    $labels[] = $monthLabel;

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM credentials
        WHERE employee_id = ?
          AND MONTH(created_at) = ?
          AND YEAR(created_at) = ?
    ");
    $stmt->execute([$employeeId, $monthNum, $yearNum]);
    $data[] = (int)$stmt->fetchColumn();
}

include __DIR__ . '/../includes/header_employee.php';
?>

<div class="employee-dashboard">
    <div class="dashboard-topbar">
        <div>
            <p class="dashboard-kicker">Employee Dashboard</p>
            <h1 class="dashboard-title">Welcome, <?= e($_SESSION['employee_name'] ?? '') ?></h1>
            <p class="dashboard-subtitle">
                View your credential uploads, monitor your activity, and manage your records easily.
            </p>
        </div>

        <div class="dashboard-user-badge">
            <div class="user-badge-icon">E</div>
            <div class="user-badge-info">
                <strong><?= e($_SESSION['employee_name'] ?? '') ?></strong>
                <span>Approved Account</span>
            </div>
        </div>
    </div>

    <div class="row g-4 dashboard-summary-row">
        <div class="col-xl-4 col-md-6">
            <div class="summary-card primary-card">
                <div class="summary-card-head">
                    <span class="summary-label">Total Uploaded Credentials</span>
                    <span class="summary-icon">📄</span>
                </div>
                <h2 class="summary-value"><?= $totalUploads ?></h2>
                <p class="summary-note">All submitted credentials under your account</p>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="summary-card secondary-card">
                <div class="summary-card-head">
                    <span class="summary-label">Uploads This Month</span>
                    <span class="summary-icon">📈</span>
                </div>
                <h2 class="summary-value"><?= $uploadsThisMonth ?></h2>
                <p class="summary-note">Files uploaded during the current month</p>
            </div>
        </div>

        <div class="col-xl-4 col-md-12">
            <div class="summary-card neutral-card">
                <div class="summary-card-head">
                    <span class="summary-label">Account Status</span>
                    <span class="summary-icon">✅</span>
                </div>
                <h2 class="summary-value summary-status">Active</h2>
                <p class="summary-note">Your employee account is approved and active</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-panel chart-panel h-100">
                <div class="panel-header">
                    <div>
                        <h4>Upload Activity</h4>
                        <p>Credential upload trend for the last 6 months</p>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="chart-card">
                        <canvas id="uploadTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-panel info-panel h-100">
                <div class="panel-header">
                    <div>
                        <h4>Quick Info</h4>
                        <p>Your current employee account details</p>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-title">Employee Name</span>
                            <strong><?= e($_SESSION['employee_name'] ?? '') ?></strong>
                        </div>

                        <div class="info-item">
                            <span class="info-title">Status</span>
                            <span class="status-pill">Approved</span>
                        </div>

                        <div class="info-item">
                            <span class="info-title">Total Uploaded</span>
                            <strong><?= $totalUploads ?></strong>
                        </div>

                        <div class="info-item">
                            <span class="info-title">This Month</span>
                            <strong><?= $uploadsThisMonth ?></strong>
                        </div>
                    </div>

                    <div class="info-message">
                        Keep your credentials updated and complete for better record management.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/chart.js/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('uploadTrendChart');

    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Uploads',
                    data: <?= json_encode($data) ?>,
                    fill: true,
                    tension: 0.35,
                    backgroundColor: 'rgba(125, 170, 203, 0.20)',
                    borderColor: '#021A54',
                    borderWidth: 3,
                    pointBackgroundColor: '#7DAACB',
                    pointBorderColor: '#021A54',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#021A54',
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#021A54',
                        titleColor: '#F5F5F5',
                        bodyColor: '#F5F5F5',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#5f6b85',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#5f6b85',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(2, 26, 84, 0.08)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>