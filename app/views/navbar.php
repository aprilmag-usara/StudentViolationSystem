<?php 
$role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'student';
$active = $active ?? '';
?>

<?php if ($role === 'osas'): ?>
    <!-- OSAS Sidebar Layout -->
    <aside class="sidebar">
        <div class="logo-container">
            <img src="assets/img/logos2.png" alt="SVS Logo" class="logo-img">
            <span class="logo-text">Student Violation System</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php?url=osas/dashboard" class="sidebar-link <?php echo $active === 'home' ? 'active' : ''; ?>">
                <span class="icon"><img src="assets/img/icons/dashboardd.svg" alt="Dashboard icon" width="24" height="24"></span> Dashboard
            </a>
            <a href="index.php?url=osas/students" class="sidebar-link <?php echo $active === 'students' ? 'active' : ''; ?>">
                <span class="icon"><img src="assets/img/icons/student.svg" alt="Group Students icon" width="24" height="24"></span> Students
            </a>
            <a href="index.php?url=osas/guards" class="sidebar-link <?php echo $active === 'guards' ? 'active' : ''; ?>">
                <span class="icon"><img src="assets/img/icons/shield.svg" alt="Building Shield icon" width="24" height="24"></span> Guards
            </a>
            <a href="index.php?url=osas/records" class="sidebar-link <?php echo $active === 'records' ? 'active' : ''; ?>">
                <span class="icon"><img src="assets/img/icons/clipboard.svg" alt="File Records icon" width="24" height="24"></span> Records
            </a>
            
            <a href="index.php?url=osas/notifications" class="sidebar-link relative-pos <?php echo $active === 'notifications' ? 'active' : ''; ?>">
                <span class="icon"><img src="assets/img/icons/notification.svg" alt="Add Bell Notification icon" width="24" height="24"></span> Notifications
                <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                    <span class="notif-badge-sidebar"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>

            <a href="index.php?url=osas/profile" class="sidebar-link <?php echo $active === 'profile' ? 'active' : ''; ?>">
                <span class="icon"><img src="assets/img/icons/userpro.svg" alt="Profile icon" width="25" height="25"></span> Profile
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="#" id="osasLogoutBtn" class="sidebar-link logout-link">
                <span class="icon"><img src="assets/img/icons/logouts.svg" alt="Log Out icon" width="30" height="30"></span> Log Out
            </a>
        </div>
    </aside>

<?php else: ?>
    <!-- Student & Guard Top Navigation Bar -->
    <nav class="navbar">
        <div class="logo-container">
            <img src="assets/img/logos2.png" alt="SVS Logo" class="logo-img">
            <span class="logo-text">Student Violation System</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php?url=<?php echo $role; ?>/dashboard" class="<?php echo $active === 'home' ? 'active' : ''; ?>">Home Page</a></li>
            <li><a href="index.php?url=<?php echo $role; ?>/records" class="<?php echo $active === 'records' ? 'active' : ''; ?>">Records</a></li>
            <li>
                <a href="index.php?url=<?php echo $role; ?>/notifications" class="relative-pos <?php echo $active === 'notifications' ? 'active' : ''; ?>">
                    Notifications
                    <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                        <span class="notif-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="index.php?url=<?php echo $role; ?>/profile" class="<?php echo $active === 'profile' ? 'active' : ''; ?>">Profile</a></li>
            <li><a href="#" id="navLogoutBtn">Log Out</a></li>
        </ul>
    </nav>
<?php endif; ?>
 
<!-- Logout Modal -->
<div id="logoutModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Logging Out?</h2>
        <p>Are you sure you want to log out of your account?</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-no" id="cancelLogout">No, stay</button>
            <a href="index.php?url=auth/logout" class="modal-btn modal-btn-yes no-underline">Yes, logout</a>
        </div>
    </div>
</div>

<script src="assets/js/main.js"></script>
