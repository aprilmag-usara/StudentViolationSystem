<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Violation Records</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section">
            <h1>Violation History</h1>
            <p>A complete list of your past and present violation records.</p>
        </div>

        <div class="glass-card">
            <?php if ($violations->num_rows > 0): ?>
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Violation</th>
                            <th>Type</th>
                            <th>Date & Time</th>
                            <th>Sanction</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $violations->data_seek(0); // Reset pointer to beginning
                        while ($row = $violations->fetch_assoc()): 
                        ?>
                        <tr>
                                <td class="fw-500 text-white">
                                    <?php echo htmlspecialchars($row['violation_type']); ?>
                                    <div class="fs-0-75 text-white-50 mt-4">By: <?php echo htmlspecialchars($row['guard_name']); ?></div>
                                </td>
                                <td>
                                    <span class="status-dot <?php echo strtolower($row['violation_type']); ?>"></span>
                                    <?php echo $row['violation_type']; ?>
                                </td>
                                <td class="fs-0-85 text-white-60">
                                    <div class="text-white fw-500 mb-3">
                                        <?php echo date('M d, Y', strtotime($row['violation_time'])); ?>
                                    </div>
                                    <?php echo date('h:i A', strtotime($row['violation_time'])); ?>
                                </td>
                                <td class="text-mint-green fw-600 font-italic">
                                    <?php echo !empty($row['sanction']) ? htmlspecialchars($row['sanction']) : '<span class="opacity-50 fw-400">Pending review</span>'; ?>
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
                <div class="text-center py-40">
                    <p class="text-white-50">No records found.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
