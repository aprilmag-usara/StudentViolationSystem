<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Guard Profile</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/guard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal-overlay">
        <div class="modal-content text-left">
            <span class="modal-close" onclick="hideEditModal()">&times;</span>
            <h2 class="mb-20">Edit Profile</h2>
            <form method="POST" action="index.php?url=guard/profile" class="flex-column gap-20">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($guardInfo['username']); ?>" required class="form-input-styled">
                </div>
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" rows="4" class="form-textarea-styled"><?php echo htmlspecialchars($guardInfo['bio'] ?? ''); ?></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" name="update_profile" class="modal-btn modal-btn-yes">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-content text-left">
            <span class="modal-close" onclick="hidePasswordModal()">&times;</span>
            <h2 class="mb-20">Change Password</h2>
            <form method="POST" action="index.php?url=guard/profile" class="flex-column gap-20">
                <div class="form-group">
                    <label>Old Password</label>
                    <input type="password" name="old_password" required class="form-input-styled">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required class="form-input-styled">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required class="form-input-styled">
                </div>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hidePasswordModal()">Cancel</button>
                    <button type="submit" name="change_password" class="modal-btn modal-btn-yes">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Photo Upload Form -->
    <form id="photoForm" method="POST" action="index.php?url=guard/profile" enctype="multipart/form-data" class="display-none">
        <input type="file" name="profile_photo" id="photoInput" onchange="document.getElementById('photoForm').submit()" accept="image/*">
    </form>

    <main class="main-dashboard">
        <div class="welcome-section text-center">
            <h1>My Profile</h1>
            <p>Manage your guard account and security settings.</p>
        </div>

        <?php if (!empty($message)): 
            $isError = strpos(strtolower($message), 'error') !== false || strpos(strtolower($message), 'incorrect') !== false || strpos(strtolower($message), 'match') !== false;
        ?>
            <div class="toast-container" id="toast">
                <div class="toast-message <?php echo $isError ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="max-w-800 mx-auto">
            <!-- Profile Info Card -->
            <div class="glass-card text-center">
                <div class="profile-header">
                    <div class="profile-photo-container" onclick="document.getElementById('photoInput').click()" title="Click to upload photo">
                        <?php 
                            $photoPath = !empty($guardInfo['profile_photo']) && $guardInfo['profile_photo'] !== 'default_profile.png' 
                                ? 'assets/img/profiles/' . $guardInfo['profile_photo'] 
                                : '';
                            $full_name = $guardInfo['full_name'] ?? 'Guard';
                            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=150";
                        ?>
                        <img src="<?php echo $photoPath; ?>" alt="Profile" class="profile-photo-img" onerror="this.src='<?php echo $avatar_url; ?>'">
                        <div class="photo-edit-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        </div>
                    </div>
                    
                    <div class="profile-name-section">
                        <h2 class="fs-1-8 mb-5"><?php echo htmlspecialchars($guardInfo['username'] ?? ''); ?></h2>
                        <p class="fs-1-1 text-white-80 fw-300"><?php echo htmlspecialchars($full_name); ?></p>
                    </div>

                    <?php if(!empty($guardInfo['bio'])): ?>
                        <p class="profile-bio-text">
                            "<?php echo htmlspecialchars($guardInfo['bio']); ?>"
                        </p>
                    <?php endif; ?>

                    <div class="profile-actions-center">
                        <button onclick="showEditModal()" class="modal-btn modal-btn-yes px-40">Edit Profile</button>
                        <button onclick="showPasswordModal()" class="modal-btn modal-btn-no px-40">Change Password</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/guard.js"></script>
</body>
</html>