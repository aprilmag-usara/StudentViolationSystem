<?php 
$role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'student';
$active = $active ?? '';
?>

<?php if ($role === 'osas'): ?>
    <!-- OSAS Sidebar Layout -->
    <aside class="sidebar">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="SVS Logo" class="logo-img">
            <span class="logo-text">SVS.</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php?url=osas/dashboard" class="sidebar-link <?php echo $active === 'home' ? 'active' : ''; ?>">
                <span class="icon">🏠</span> Dashboard
            </a>
            <a href="index.php?url=osas/students" class="sidebar-link <?php echo $active === 'students' ? 'active' : ''; ?>">
                <span class="icon">👨‍🎓</span> Students
            </a>
            <a href="index.php?url=osas/guards" class="sidebar-link <?php echo $active === 'guards' ? 'active' : ''; ?>">
                <span class="icon">🛡️</span> Guards
            </a>
            <a href="index.php?url=osas/records" class="sidebar-link <?php echo $active === 'records' ? 'active' : ''; ?>">
                <span class="icon">📋</span> Records
            </a>
            <a href="index.php?url=osas/profile" class="sidebar-link <?php echo $active === 'profile' ? 'active' : ''; ?>">
                <span class="icon">👤</span> Profile
            </a>
            
            <a href="javascript:void(0)" onclick="toggleNotifications()" class="sidebar-link relative-pos">
                <span class="icon">🔔</span> Notifications
                <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                    <span class="notif-badge-sidebar"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="#" onclick="showLogoutModal()" class="sidebar-link logout-link">
                <span class="icon">🚪</span> Log Out
            </a>
        </div>
    </aside>

<?php else: ?>
    <!-- Student & Guard Top Navigation Bar -->
    <nav class="navbar">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="SVS Logo" class="logo-img">
            <span class="logo-text">SVS.</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php?url=<?php echo $role; ?>/dashboard" class="<?php echo $active === 'home' ? 'active' : ''; ?>">Home Page</a></li>
            <li><a href="index.php?url=<?php echo $role; ?>/records" class="<?php echo $active === 'records' ? 'active' : ''; ?>">Records</a></li>
            <li>
                <a href="javascript:void(0)" onclick="toggleNotifications()" class="relative-pos">
                    Notifications
                    <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                        <span class="notif-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="index.php?url=<?php echo $role; ?>/profile" class="<?php echo $active === 'profile' ? 'active' : ''; ?>">Profile</a></li>
            <li><a href="#" onclick="showLogoutModal()">Log Out</a></li>
        </ul>
    </nav>
<?php endif; ?>

<!-- Notifications Dropdown -->
<div id="notifDropdown" class="notif-dropdown">
    <div class="notif-header">
        <h4 class="m-0">Notifications</h4>
        <?php if (isset($unreadCount) && $unreadCount > 0): ?>
            <a href="index.php?url=<?php echo $role; ?>/mark_read" class="fs-0-75 text-sage-green no-underline">Mark all as read</a>
        <?php endif; ?>
    </div>
    <div class="notif-list-container">
        <?php if (isset($notifications) && $notifications && $notifications->num_rows > 0): ?>
            <?php 
            $notifications->data_seek(0);
            while($n = $notifications->fetch_assoc()): 
            ?>
                <div class="notif-item <?php echo $n['is_read'] ? '' : 'unread'; ?>" 
                     onclick="window.location.href='index.php?url=<?php echo $role; ?>/records'">
                    <div class="notif-msg"><?php echo htmlspecialchars($n['message']); ?></div>
                    <div class="notif-time"><?php echo date('M d, h:i A', strtotime($n['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="p-30 text-center text-white-30 fs-0-9">
                No notifications yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Logout Modal -->
<div id="logoutModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Logging Out?</h2>
        <p>Are you sure you want to log out of your account?</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-no" onclick="hideLogoutModal()">No, stay</button>
            <a href="index.php?url=auth/logout" class="modal-btn modal-btn-yes no-underline">Yes, logout</a>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
