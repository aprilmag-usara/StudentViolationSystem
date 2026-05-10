<?php 
/** @var string $message */
/** @var mysqli_result $guards */
/** @var int $unreadCount */
/** @var array $notifications */
$message = $message ?? '';
$guards = $guards ?? null;
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
?>
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

    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section mb-40">
            <div class="flex-between align-center">
                <div>
                    <h1 class="glow-text mb-5">Guard Management</h1>
                    <p class="subtitle-text">Monitor and manage the campus security guard team.</p>
                </div>
                <button class="btn-primary-small" onclick="showAddGuardModal()">
                    <span>+</span> Add Guard Name
                </button>
            </div>
        </div>

        <div class="search-bar-standalone mb-50">
            <div class="search-container-full">
                <span class="search-icon-fixed"><img src="assets/img/icons/search.svg" alt="Search" width="20" height="20"></span>
                <input type="text" id="guardSearch" placeholder="Search guards by name..." class="search-input-modern">
                <div id="searchDropdown" class="search-results-dropdown"></div>
            </div>
        </div>

        <div id="noGuardResults" class="text-center py-60 display-none" style="display: none;">
            <div class="fs-3-0 mb-10"><img src="assets/img/icons/search.svg" alt="Magnifying Glass icon" width="35" height="35"></div>
            <h2 class="text-white-50">No guards found</h2>
            <p class="text-white-30">Try searching for a different name.</p>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                <?php if (!empty($message)): ?>
                    const msg = "<?php echo addslashes($message); ?>";
                    const isError = msg.toLowerCase().includes('error') || msg.toLowerCase().includes('failed');
                    showToast(isError ? 'Action Failed' : 'Success', msg, isError ? 'error' : 'success');
                <?php endif; ?>
            });
        </script>

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
                    <label class="form-label">Guard Name</label>
                    <input type="text" name="new_guard_name" id="editGuardName" class="form-input" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideEditGuardModal()">Cancel</button>
                    <button type="submit" name="edit_guard" class="modal-btn modal-btn-yes">Update Name</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Guard Modal -->
    <div id="addGuardModal" class="modal-overlay">
        <div class="modal-content text-left">
            <span class="modal-close" onclick="hideAddGuardModal()">&times;</span>
            <h2 class="mb-5">Add New Guard</h2>
            <p class="text-white-40 mb-25">The name will appear in the Guard on Duty dropdown.</p>
            <form method="POST" class="standard-form">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="guard_name" class="form-input" placeholder="Enter guard's full name..." required>
                </div>
                <div class="flex-row justify-center mt-30">
                    <button type="submit" name="add_guard" class="btn-primary-enhanced px-40">Create Guard Name</button>
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

    <script src="assets/js/osas.js"></script>
</body>
</html>