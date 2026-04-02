<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Notifications</title>
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
            <div class="flex-between align-end">
                <div>
                    <h1>Notifications</h1>
                    <p>Stay updated on recent reports and system activity.</p>
                </div>
                <?php if ($notifications && $notifications->num_rows > 0): ?>
                    <div>
                        <a href="index.php?url=osas/mark_read" class="modal-btn modal-btn-yes px-25 no-underline">Mark All as Read</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="toast-container" id="toast">
                <div class="toast-message">
                    <?php echo $message; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="max-w-800 mx-auto">
            <?php if ($notifications && $notifications->num_rows > 0): ?>
                <?php while ($n = $notifications->fetch_assoc()): ?>
                    <div class="notif-card <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                        <div class="notif-content">
                            <div class="notif-msg"><?php echo htmlspecialchars($n['message']); ?></div>
                            <div class="notif-time"><?php echo date('M d, Y | h:i A', strtotime($n['created_at'])); ?></div>
                        </div>
                        <div class="notif-actions">
                            <?php if (!$n['is_read']): ?>
                                <form method="POST" class="display-inline">
                                    <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                                    <button type="submit" name="mark_read" class="action-btn" title="Mark as read">✔️</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" class="display-inline" onsubmit="return confirm('Delete this notification?');">
                                <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                                <button type="submit" name="delete_notif" class="action-btn delete" title="Delete notification">🗑️</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center p-40">
                    <div class="fs-4-0 mb-20 opacity-20">🔔</div>
                    <h3 class="text-white-50">No notifications found</h3>
                    <p class="text-white-30">You're all caught up!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

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