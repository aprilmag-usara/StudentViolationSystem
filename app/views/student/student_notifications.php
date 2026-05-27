<?php 
$message = $message ?? '';
$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Student Notifications</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section">
            <div class="flex-between align-end">
                <div>
                    <h1>Your Notifications</h1>
                    <p>Track all updates regarding your violations and system alerts.</p>
                </div>
                <?php if (!empty($notifications)): ?>
                    <div>
                        <a href="index.php?url=student/mark_read" class="modal-btn modal-btn-yes px-25 no-underline">Mark All as Read</a>
                    </div>
                <?php endif; ?>
            </div>
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

        <div class="notif-page-container">
            <div class="notif-list-header">
                <h3>Recent Notifications</h3>
            </div>
            
            <?php if (!empty($notifications)): ?>
                <div class="notif-list">
                    <?php foreach($notifications as $n): 
                        $targetUrl = "index.php?url=student/records" . (isset($n['violation_id']) ? "&violation_id=" . $n['violation_id'] : "");
                    ?>
                        <div class="notif-item <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                            <div class="notif-icon-wrapper" style="cursor: pointer;" onclick="window.location.href='<?php echo $targetUrl; ?><?php echo !$n['is_read'] ? (strpos($targetUrl, '?') !== false ? '&' : '?') . 'mark_read=' . $n['id'] : ''; ?>'">
                                <img src="assets/img/icons/notification.svg" alt="Notification">
                            </div>
                            <div class="notif-content-wrapper" style="cursor: pointer;" onclick="window.location.href='<?php echo $targetUrl; ?><?php echo !$n['is_read'] ? (strpos($targetUrl, '?') !== false ? '&' : '?') . 'mark_read=' . $n['id'] : ''; ?>'">
                                <div class="notif-message">
                                    <?php echo htmlspecialchars($n['message']); ?>
                                </div>
                                <div class="notif-meta">
                                    <div class="notif-time"><?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?></div>
                                    <?php if (!$n['is_read']): ?>
                                        <span class="notif-badge">NEW</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="notif-actions-wrapper">
                                <form method="POST" action="index.php?url=student/notifications" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                    <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                    <button type="submit" name="delete_notification" class="notif-action-btn delete" title="Delete">
                                        &times;
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="notif-empty-state">
                    <div class="notif-empty-state-icon">
                        <img src="assets/img/icons/notification.svg" alt="Empty">
                    </div>
                    <h3>No notifications yet</h3>
                    <p>You're all caught up!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
