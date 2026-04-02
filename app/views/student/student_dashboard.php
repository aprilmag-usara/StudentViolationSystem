<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Student Home</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-dashboard">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($studentInfo['full_name']); ?>!</h1>
            <p>Here is an overview of your current status.</p>
        </div>

        <!-- Current Violations Section -->
        <div class="dashboard-grid">
            <div class="glass-card mb-40">
                <h3 class="mb-20 text-sage-green">Current Violations</h3>
                <div class="violation-list">
                    <?php if ($violations && $violations->num_rows > 0): ?>
                        <?php while ($v = $violations->fetch_assoc()): ?>
                        <div class="violation-card <?php echo strtolower($v['violation_type']); ?> p-20 border-radius-15 mb-15">
                            <div class="violation-header">
                                <div class="violation-info">
                                    <span class="v-badge <?php echo strtolower($v['violation_type']); ?>">
                                        <?php echo $v['violation_type']; ?>
                                    </span>
                                    <h4 class="mt-10"><?php echo htmlspecialchars($v['description']); ?></h4>
                                </div>
                                <span class="fs-0-8 text-white-50">
                                    <?php echo date('M d, Y', strtotime($v['violation_time'])); ?>
                                </span>
                            </div>
                            <div class="violation-footer">
                                <p class="mt-10 fs-0-9">
                                    <strong>Sanction:</strong> 
                                    <span class="text-mint-green"><?php echo isset($v['sanction']) ? $v['sanction'] : 'Pending Review'; ?></span>
                                </p>
                                <span class="status-pill <?php echo strtolower($v['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $v['status'])); ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-40">
                            <div class="fs-3-0 mb-10">🎉</div>
                            <h2 class="text-mint-green">You're All Clear!</h2>
                            <p class="text-white-50 fw-300">You have a clean record. Keep it up!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
