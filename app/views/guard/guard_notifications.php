<?php 
/** @var string $message */
/** @var array $notifications */
/** @var int $unreadCount */
$message = $message ?? '';
$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Guard Notifications</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/guard.css">
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
                    <h1>Security Alerts</h1>
                    <p>Track all updates regarding reported violations and system alerts.</p>
                </div>
                <?php if (!empty($notifications)): ?>
                    <div>
                        <a href="index.php?url=guard/mark_read" class="modal-btn modal-btn-yes px-25 no-underline">Mark All as Read</a>
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
            <div class="glass-card p-0 overflow-hidden">
                <div class="p-20 border-bottom-glass flex-between align-center">
                    <h3 class="m-0 text-sage-green">Recent Notifications</h3>
                    <span class="fs-0-8 text-white-40"><?php echo count($notifications); ?> total</span>
                </div>
                
                <div class="notif-list-full">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach($notifications as $n): ?>
                            <div class="notif-full-item <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                                <div class="notif-full-icon">
                                    <img src="assets/img/icons/notification.svg" alt="Notif" width="24" height="24">
                                </div>
                                <div class="notif-full-content">
                                    <div class="notif-full-msg">
                                        <?php echo htmlspecialchars($n['message']); ?>
                                        <?php if (!$n['is_read']): ?>
                                            <span class="new-badge-notif">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-full-time"><?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?></div>
                                </div>
                                <div class="notif-full-actions">
                                    <form method="POST" action="index.php?url=guard/notifications">
                                        <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                        <button type="submit" name="delete_notification" class="delete-btn-notif" title="Delete">
                                            &times;
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-60 text-center">
                            <img src="assets/img/icons/notification.svg" alt="Empty" width="60" height="60" style="opacity: 0.2; margin-bottom: 15px;">
                            <p class="text-white-30">No notifications found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>