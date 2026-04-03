<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Guard Management</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/osas.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-dashboard">
        <div class="welcome-section">
            <h1>Guard Management</h1>
            <p>Monitor and manage the campus security guard team.</p>
        </div>

            <?php if (!empty($message)): ?>
                <div class="toast-container" id="toast">
                    <div class="toast-message">
                        <?php echo $message; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="add-guard-section">
                <h2 class="text-mint-green m-0">Add New Guard Name</h2>
                <p class="text-white-40 fs-0-9">The name will appear in the Guard on Duty dropdown.</p>
                <form method="POST" class="add-guard-form">
                    <input type="text" name="guard_name" class="add-input" placeholder="Enter guard's full name..." required>
                    <button type="submit" name="add_guard" class="add-btn">Add Guard</button>
                </form>
            </div>

            <div class="guard-grid">
                <?php if ($guards && $guards->num_rows > 0): ?>
                    <?php while ($g = $guards->fetch_assoc()): ?>
                        <div class="guard-card">
                            <div class="guard-header">
                                <div class="guard-avatar">
                                    <?php echo substr($g['name'], 0, 1); ?>
                                </div>
                                <div class="guard-info">
                                    <h3><?php echo htmlspecialchars($g['name']); ?></h3>
                                    <p>Active System Guard</p>
                                </div>
                            </div>

                            <div class="guard-stats">
                                <div class="stat-item">
                                    <h4>Reports</h4>
                                    <div class="value"><?php echo $g['report_count'] ?? 0; ?></div>
                                </div>
                            </div>

                            <div class="guard-actions">
                                <button onclick="viewGuardRecords('<?php echo addslashes($g['name']); ?>')" class="guard-btn view">View Activity</button>
                                <button onclick="showEditGuardModal(<?php echo $g['id']; ?>, '<?php echo addslashes($g['name']); ?>')" class="guard-btn edit">Edit Name</button>
                                <form method="POST" class="flex display-flex" style="flex: 1;">
                                    <input type="hidden" name="guard_name" value="<?php echo htmlspecialchars($g['name']); ?>">
                                    <button type="submit" name="delete_guard" class="guard-btn delete w-100" onclick="return confirm('Remove this guard from the system?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="glass-card text-center p-40 grid-column-full">
                        <p class="text-white-40">No guards registered in the system.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

    <!-- Edit Guard Modal -->
    <div id="editGuardModal" class="modal-overlay">
        <div class="modal-content text-left">
            <span class="modal-close" onclick="hideEditGuardModal()">&times;</span>
            <h2 class="mb-20">Edit Guard Name</h2>
            <form method="POST" class="flex-column gap-20">
                <input type="hidden" name="guard_id" id="editGuardId">
                <div class="form-group">
                    <label>Guard Name</label>
                    <input type="text" name="new_guard_name" id="editGuardName" class="form-input-styled" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideEditGuardModal()">Cancel</button>
                    <button type="submit" name="edit_guard" class="modal-btn modal-btn-yes">Update Name</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Guard Activity Modal -->
    <div id="guardModal" class="modal-overlay">
        <div class="modal-content max-w-750 w-90">
            <span class="modal-close" onclick="hideGuardModal()">&times;</span>
            <h2 id="modalTitle" class="mb-5">Guard Activity</h2>
            <p id="modalSubtitle" class="text-white-40 mb-25"></p>
            
            <div id="modalLoading" class="p-40 text-center opacity-50">Fetching reporting history...</div>
            <div id="modalContent" class="display-none">
                <div id="activityList" class="history-list">
                    <!-- Filled by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideLogoutModal()">&times;</span>
            <h2>Logging Out?</h2>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-no" onclick="hideLogoutModal()">Cancel</button>
                <a href="index.php?url=auth/logout" class="modal-btn modal-btn-yes" style="text-decoration: none;">Yes, logout</a>
            </div>
        </div>
    </div>

    <script src="assets/js/osas.js"></script>
</body>
</html>