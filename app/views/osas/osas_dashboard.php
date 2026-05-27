<?php 
/** @var array $stats */
/** @var mysqli_result $recentViolations */
/** @var array $monthlyData */
/** @var array $categoryData */
/** @var array $courseData */
/** @var array $yearLevelData */
/** @var int $unreadCount */
/** @var array $notifications */
$stats = $stats ?? [];
$recentViolations = $recentViolations ?? null;
$monthlyData = $monthlyData ?? ['months' => [], 'counts' => []];
$categoryData = $categoryData ?? [];
$courseData = $courseData ?? [];
$yearLevelData = $yearLevelData ?? [];
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | OSAS Dashboard</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/osas.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-dashboard">
        <div class="welcome-section mb-20">
            <div class="flex-between align-center">
                <div>
                    <h1 class="glow-text mb-5">Dashboard Overview</h1>
                    <div class="flex align-center gap-10 opacity-50">
                        <span class="icon-calendar"><img src="assets/img/icons/clipboard.svg" alt="" width="14"></span>
                        <span class="fs-0-8 fw-500"><?php echo date('l, F d, Y'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="osas-stats-grid">
                <div class="stat-card stat-total">
                    <span class="stat-icon"><img src="assets/img/icons/stat.svg" alt="Statistics icon"></span>
                    <div class="stat-info">
                        <h3>Total Violations</h3>
                        <div class="value"><?php echo $stats['total_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card stat-pending">
                    <span class="stat-icon"><img src="assets/img/icons/pending2.svg" alt="Pending Actions icon"></span>
                    <div class="stat-info">
                        <h3>Pending Actions</h3>
                        <div class="value"><?php echo $stats['pending_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card stat-major">
                    <span class="stat-icon"><img src="assets/img/icons/cautions.svg" alt="Caution icon"></span>
                    <div class="stat-info">
                        <h3>Major Cases</h3>
                        <div class="value"><?php echo $stats['major_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card stat-minor">
                    <span class="stat-icon"><img src="assets/img/icons/lists.svg" alt="Bxs List Ul Square icon"></span>
                    <div class="stat-info">
                        <h3>Minor Cases</h3>
                        <div class="value"><?php echo $stats['minor_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card stat-expulsion">
                    <span class="stat-icon"><img src="assets/img/icons/expel.svg" alt="Expulsion icon"></span>
                    <div class="stat-info">
                        <h3>Expulsions</h3>
                        <div class="value"><?php echo $stats['expulsions']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Visualization Grid -->
            <div class="dashboard-grid">
                <!-- Main Trend Chart -->
                <div class="chart-card col-8">
                    <div class="chart-header">
                        <h3>Violation Trends</h3>
                        <span class="subtitle">Monthly Activity Overview (Jan - Dec)</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="violationTrendsChart"></canvas>
                    </div>
                </div>

                <!-- Right Side Stats -->
                <div class="chart-card col-4">
                    <div class="chart-header">
                        <h3>System Integrity</h3>
                        <span class="subtitle">Automated Escalation Active</span>
                    </div>
                    <div class="p-20 glass-card text-center">
                        <p class="text-white-50 fs-0-8 mb-10">All records are being monitored for non-compliance.</p>
                        <div class="status-badge-secure">
                            <span class="secure-dot"></span> SECURE
                        </div>
                    </div>
                </div>

                <!-- Bottom Charts -->
                <div class="chart-card col-4">
                    <div class="chart-header">
                        <h3>Type Distribution</h3>
                        <span class="subtitle">Major vs Minor Ratio</span>
                    </div>
                    <div class="chart-body min-h-200">
                        <canvas id="violationCategoryChart"></canvas>
                    </div>
                </div>

                <div class="chart-card col-4">
                    <div class="chart-header">
                        <h3>Top Course Cases</h3>
                        <span class="subtitle">Most Frequent Courses</span>
                    </div>
                    <div class="chart-body min-h-200">
                        <canvas id="courseChart"></canvas>
                    </div>
                </div>

                <div class="chart-card col-4">
                    <div class="chart-header">
                        <h3>Year Level Growth</h3>
                        <span class="subtitle">Violations by Year</span>
                    </div>
                    <div class="chart-body min-h-200">
                        <canvas id="yearLevelChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="section-header">
                <h2>Recent Violations</h2>
                <a href="index.php?url=osas/records" class="view-all-btn">View All Records →</a>
            </div>

            <div class="glass-card">
                <?php if ($recentViolations && $recentViolations->num_rows > 0): ?>
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Reported By</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recentViolations->fetch_assoc()): ?>
                            <tr id="violation-<?php echo $row['id']; ?>">
                                <td>
                                    <div class="fw-600"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                    <div class="fs-0-7 text-white-50"><?php echo htmlspecialchars($row['student_id_number']); ?></div>
                                </td>
                                <td>
                                    <span class="status-dot <?php echo strtolower($row['violation_type']); ?>"></span>
                                    <?php echo $row['violation_type']; ?>
                                </td>
                                <td class="text-white-70"><?php echo htmlspecialchars($row['guard_name']); ?></td>
                                <td class="fs-0-8 text-white-50">
                                    <?php echo date('M d, Y | h:i A', strtotime($row['violation_time'])); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-container">
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form method="POST" action="index.php?url=osas/dashboard" class="mb-5">
                                                <input type="hidden" name="violation_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="guard_id" value="<?php echo $row['guard_user_id']; ?>">
                                                <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($row['student_name']); ?>">
                                                <button type="submit" name="receive_violation_dash" class="btn-action btn-receive-dash">
                                                    <img src="assets/img/icons/pass.svg" alt="" width="14"> Receive
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if (!in_array($row['status'], ['completed', 'dismissed'])): ?>
                                            <form method="POST" action="index.php?url=osas/dashboard" class="sanction-form">
                                                <input type="hidden" name="violation_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="guard_id" value="<?php echo $row['guard_user_id']; ?>">
                                                <input type="hidden" name="student_user_id" value="<?php echo $row['student_user_id']; ?>">
                                                <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($row['student_name']); ?>">
                                                
                                                <div class="input-group">
                                                    <input type="text" name="sanction" placeholder="Add sanction..." class="sanction-input-dash" required>
                                                    <button type="submit" name="review_violation" class="btn-submit-sanction" title="Submit Sanction">
                                                        <img src="assets/img/icons/lightning.svg" alt="" width="14">
                                                    </button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-white-30 fs-0-8 italic">No actions needed</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center p-40">
                        <p class="text-white-50">No recent activity found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/osas.js"></script>
    <script>
        // Inject dynamic data from PHP
        const chartData = {
            monthly: <?php echo json_encode($monthlyData); ?>,
            category: <?php echo json_encode($categoryData); ?>,
            course: <?php echo json_encode($courseData); ?>,
            yearLevel: <?php echo json_encode($yearLevelData); ?>
        };

        document.addEventListener('DOMContentLoaded', () => {
            // 1. Violation Trends Chart (Line Chart)
            const trendCtx = document.getElementById('violationTrendsChart').getContext('2d');
            
            // Create Gradient
            const currentGradient = trendCtx.createLinearGradient(0, 0, 0, 400);
            currentGradient.addColorStop(0, 'rgba(64, 145, 108, 0.4)');
            currentGradient.addColorStop(1, 'rgba(64, 145, 108, 0)');

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: chartData.monthly.months,
                    datasets: [
                        {
                            label: `Current Year (${chartData.monthly.currentYear})`,
                            data: chartData.monthly.current,
                            borderColor: '#40916c',
                            backgroundColor: currentGradient,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.5,
                            cubicInterpolationMode: 'monotone',
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#40916c',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            order: 1
                        },
                        {
                            label: `Previous Year (${chartData.monthly.previousYear})`,
                            data: chartData.monthly.previous,
                            borderColor: 'rgba(255, 255, 255, 0.15)',
                            borderDash: [5, 5],
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.5,
                            cubicInterpolationMode: 'monotone',
                            pointBackgroundColor: 'rgba(255, 255, 255, 0.1)',
                            pointRadius: 2,
                            order: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                color: 'rgba(255, 255, 255, 0.5)',
                                font: { family: 'Poppins', size: 10 },
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(8, 28, 21, 0.9)',
                            titleFont: { family: 'Poppins' },
                            bodyFont: { family: 'Poppins' },
                            padding: 12,
                            cornerRadius: 10
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                            ticks: { color: 'rgba(255, 255, 255, 0.3)', font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: 'rgba(255, 255, 255, 0.3)', font: { size: 11 } }
                        }
                    }
                }
            });

            // 2. Type Distribution (Doughnut Chart)
            const categoryCtx = document.getElementById('violationCategoryChart');
            if (categoryCtx) {
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(chartData.category),
                        datasets: [{
                            data: Object.values(chartData.category),
                            backgroundColor: ['#f1c40f', '#e74c3c'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: 'rgba(255,255,255,0.7)',
                                    padding: 20,
                                    font: { family: 'Poppins', size: 11 }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Top Course Cases (Bar Chart)
            const courseCtx = document.getElementById('courseChart');
            if (courseCtx) {
                new Chart(courseCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.course.courses,
                        datasets: [{
                            label: 'Cases',
                            data: chartData.course.counts,
                            backgroundColor: '#40916c',
                            borderRadius: 5,
                            barThickness: 15
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } } },
                            y: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } } }
                        }
                    }
                });
            }

            // 4. Year Level Chart (Bar Chart)
            const yearLevelCtx = document.getElementById('yearLevelChart');
            if (yearLevelCtx) {
                new Chart(yearLevelCtx, {
                    type: 'bar',
                    data: {
                        labels: chartData.yearLevel.levels,
                        datasets: [{
                            label: 'Cases',
                            data: chartData.yearLevel.counts,
                            backgroundColor: '#52b788',
                            borderRadius: 5,
                            barThickness: 20
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } } },
                            y: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } } }
                        }
                    }
                });
            }


        });
    </script>
</body>
</html>