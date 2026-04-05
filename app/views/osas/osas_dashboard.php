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
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-dashboard">
        <div class="welcome-section">
            <div class="flex-between align-end">
                <div>
                    <h1>OSAS Administrator</h1>
                    <p>Student violation management system overview.</p>
                </div>
                <div class="fs-0-9 text-white-50 fw-300">
                    Today is <?php echo date('l, M d Y'); ?>
                </div>
            </div>
        </div>

        <div class="osas-stats-grid">
                <div class="stat-card">
                    <span class="stat-icon"><img src="assets/img/icons/stat.svg" alt="Statistics icon"></span>
                    <div class="stat-info">
                        <h3>Total Violations</h3>
                        <div class="value"><?php echo $stats['total_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon text-orange"><img src="assets/img/icons/pending2.svg" alt="Pending Actions icon"></span>
                    <div class="stat-info">
                        <h3>Pending Actions</h3>
                        <div class="value"><?php echo $stats['pending_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon text-error"><img src="assets/img/icons/cautions.svg" alt="Caution icon"></span>
                    <div class="stat-info">
                        <h3>Major Cases</h3>
                        <div class="value"><?php echo $stats['major_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon text-yellow"><img src="assets/img/icons/lists.svg" alt="Bxs List Ul Square icon"></span>
                    <div class="stat-info">
                        <h3>Minor Cases</h3>
                        <div class="value"><?php echo $stats['minor_violations']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon text-danger"><img src="assets/img/icons/expel.svg" alt="Expulsion icon"></span>
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
                        <h3>Case Summary</h3>
                        <span class="subtitle">Live System Totals</span>
                    </div>
                    <div class="stat-mini-grid">
                        <div class="stat-mini-card">
                            <span class="label">Total Records</span>
                            <span class="value"><?php echo $stats['total_violations']; ?></span>
                        </div>
                        <div class="stat-mini-card">
                            <span class="label">Pending Review</span>
                            <span class="value text-orange"><?php echo $stats['pending_violations']; ?></span>
                        </div>
                        <div class="stat-mini-card">
                            <span class="label">Major Violations</span>
                            <span class="value text-error"><?php echo $stats['major_violations']; ?></span>
                        </div>
                        <div class="stat-mini-card">
                            <span class="label">Minor Violations</span>
                            <span class="value text-yellow"><?php echo $stats['minor_violations']; ?></span>
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
                <a href="index.php?url=osas/violations" class="view-all-btn">View All Records →</a>
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
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST" action="index.php?url=osas/dashboard" class="mb-10">
                                            <input type="hidden" name="violation_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="guard_id" value="<?php echo $row['guard_user_id']; ?>">
                                            <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($row['student_name']); ?>">
                                            <button type="submit" name="receive_violation_dash" class="modal-btn btn-receive-dash">Receive Violation</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="index.php?url=osas/dashboard" class="flex-column gap-10">
                                        <input type="hidden" name="violation_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="guard_id" value="<?php echo $row['guard_user_id']; ?>">
                                        <input type="hidden" name="student_user_id" value="<?php echo $row['student_user_id']; ?>">
                                        <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($row['student_name']); ?>">
                                        
                                        <input type="text" name="sanction" placeholder="Enter sanction..." class="sanction-input-dash" required>
                                        <button type="submit" name="review_violation" class="btn-submit-sanction">Submit Sanction</button>
                                    </form>
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

    <!-- Logout Modal removed since it is now in navbar.php -->

    <!-- Charts Script -->
    <script>
        // Inject dynamic data from PHP
        const chartData = {
            monthly: <?php echo json_encode($monthlyData); ?>,
            category: <?php echo json_encode($categoryData); ?>,
            course: <?php echo json_encode($courseData); ?>,
            yearLevel: <?php echo json_encode($yearLevelData); ?>
        };
    </script>
    <script src="assets/js/osas.js"></script>
</body>
</html>