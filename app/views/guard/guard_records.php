<?php 
/** @var string $displayName */
/** @var mysqli_result $activeViolations */
/** @var mysqli_result $completedViolations */
/** @var int $unreadCount */
/** @var array $notifications */
$displayName = $displayName ?? 'Guard';
$activeViolations = $activeViolations ?? null;
$completedViolations = $completedViolations ?? null;
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Guard Records</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/guard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section">
            <h1>Violation Records</h1>
            <p>Welcome, <?php echo htmlspecialchars($displayName); ?>. History of all violations recorded by CHMSU Security.</p>
        </div>

        <!-- Active Violations Section -->
        <h2 class="record-section-title">Active Violations</h2>
        
        <?php if ($activeViolations && $activeViolations->num_rows > 0): ?>
            <div class="glass-card">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID Number</th>
                            <th>Type</th>
                            <th>Violation Details</th>
                            <th>Guard in Charge</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $highlightViolationId = $_GET['violation_id'] ?? null;
                        while ($row = $activeViolations->fetch_assoc()): 
                            $rowClass = ($highlightViolationId == $row['id']) ? 'highlight-row' : '';
                        ?>
                        <tr class="<?php echo $rowClass; ?>" id="violation-<?php echo $row['id']; ?>">
                                <td>
                                    <div class="flex-center gap-12 justify-start">
                                        <?php 
                                            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['student_name']) . "&background=1b4332&color=fff&size=35";
                                        ?>
                                        <img src="assets/img/profiles/<?php echo $row['profile_photo']; ?>" onerror="this.src='<?php echo $avatar_url; ?>'" class="border-radius-50 border-sage-1 object-cover" style="width: 35px; height: 35px;">
                                        <div class="flex-column">
                                            <span class="fw-500"><?php echo htmlspecialchars($row['student_name']); ?></span>
                                            <span class="fs-0-7 text-white-50"><?php echo htmlspecialchars($row['course']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-white-70 font-monospace fs-1-0"><?php echo htmlspecialchars($row['student_id_number']); ?></td>
                                <td>
                                    <span class="status-dot <?php echo strtolower($row['violation_type']); ?>"></span>
                                    <?php echo $row['violation_type']; ?>
                                </td>
                                <td class="text-white-80 fs-0-9" style="max-width: 250px;">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </td>
                                <td class="text-sage-green fw-500">
                                    <?php echo htmlspecialchars($row['recorded_by_guard_name'] ?: 'System Admin'); ?>
                                </td>
                                <td class="fs-0-85 text-white-60">
                                    <div class="text-white fw-500 mb-3">
                                        <?php echo date('M d, Y', strtotime($row['violation_time'])); ?>
                                    </div>
                                    <?php echo date('h:i A', strtotime($row['violation_time'])); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="glass-card text-center py-50">
                    <h3>No Active Violations</h3>
                    <p class="text-white-50">You don't have any active violations at the moment.</p>
                </div>
            <?php endif; ?>

        <!-- Completed History Section -->
        <h2 class="record-section-title mt-60 opacity-60">Completed History</h2>
        
        <?php if ($completedViolations && $completedViolations->num_rows > 0): ?>
            <div class="glass-card opacity-80">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID Number</th>
                            <th>Type</th>
                            <th>Violation Details</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $completedViolations->fetch_assoc()): ?>
                        <tr class="bg-white-02">
                            <td>
                                <div class="fw-500 text-white-70"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                <div class="fs-0-7 text-white-30"><?php echo htmlspecialchars($row['course']); ?></div>
                            </td>
                            <td class="font-monospace text-white-50"><?php echo htmlspecialchars($row['student_id_number']); ?></td>
                            <td>
                                <span class="fs-0-7 text-white-40 border-glass px-8 py-2 border-radius-5">
                                    <?php echo $row['violation_type']; ?>
                                </span>
                            </td>
                            <td class="text-white-60">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </td>
                            <td class="fs-0-8 text-white-40">
                                <?php echo date('M d, Y', strtotime($row['violation_time'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="glass-card opacity-80 text-center py-50">
                <p class="text-white-30">No completed history found.</p>
            </div>
        <?php endif; ?>

    </main>

    </body>
</html>
