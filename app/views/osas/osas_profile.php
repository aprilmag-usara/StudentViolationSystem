<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | My Profile</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/osas.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section text-center">
            <h1>My Administrator Profile</h1>
            <p>Manage your account settings and profile information.</p>
        </div>

        <?php if (!empty($message)): 
            $isError = strpos(strtolower($message), 'error') !== false;
        ?>
            <div class="toast-container" id="toast">
                <div class="toast-message" style="background: <?php echo $isError ? 'rgba(231, 76, 60, 0.9)' : 'rgba(45, 106, 79, 0.9)'; ?>;">
                    <?php echo $message; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="max-w-800 mx-auto">
            <!-- Profile Info Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-photo-container" onclick="document.getElementById('photoInput').click()" title="Click to upload photo">
                        <?php 
                            $photoPath = !empty($userData['profile_photo']) && $userData['profile_photo'] !== 'default_profile.png' 
                                ? 'assets/img/profiles/' . $userData['profile_photo'] 
                                : '';
                            $full_name = $userData['full_name'] ?? 'Administrator';
                            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=150";
                        ?>
                        <img src="<?php echo $photoPath; ?>" alt="Profile" class="profile-photo" onerror="this.src='<?php echo $avatar_url; ?>'">
                        <div class="photo-overlay">
                            <span class="text-white fs-0-9">Change Photo</span>
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($userData['username'] ?? ''); ?></h2>
                        <span class="role-badge"><?php echo htmlspecialchars($userData['role'] ?? 'OSAS'); ?></span>
                        
                        <?php if(!empty($userData['bio'])): ?>
                            <p class="profile-bio">
                                "<?php echo htmlspecialchars($userData['bio']); ?>"
                            </p>
                        <?php endif; ?>

                        <div class="profile-actions">
                            <button onclick="showEditModal()" class="action-btn">
                                <span><img src="assets/img/icons/profedit.svg" alt="Edit Profile" width="20" height="20"></span> Edit Profile
                            </button>
                            <button onclick="showPasswordModal()" class="action-btn">
                                <span><img src="assets/img/icons/pass.svg" alt="Change Password" width="20" height="20"></span> Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal-overlay">
        <div class="modal-content text-left max-w-500">
            <span class="modal-close" onclick="hideEditModal()">&times;</span>
            <h2 class="mb-25">Edit Administrator Profile</h2>
            <form method="POST" action="index.php?url=osas/profile" class="flex-column">
                <div class="form-group mb-20">
                    <label class="display-block mb-8 text-white-60">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" required 
                           class="form-input-styled">
                </div>
                <div class="form-group mb-25">
                    <label class="display-block mb-8 text-white-60">Bio</label>
                    <textarea name="bio" rows="4" class="form-textarea-styled"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" name="update_profile" class="modal-btn modal-btn-yes">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <div id="passwordModal" class="modal-overlay">
        <div class="modal-content text-left max-w-500">
            <span class="modal-close" onclick="hidePasswordModal()">&times;</span>
            <h2 class="mb-25">Update Security Password</h2>
            <form method="POST" action="index.php?url=osas/profile" class="flex-column">
                <div class="form-group mb-15">
                    <label class="display-block mb-8 text-white-60">Current Password</label>
                    <input type="password" name="old_password" required class="form-input-styled">
                </div>
                <div class="form-group mb-15">
                    <label class="display-block mb-8 text-white-60">New Password</label>
                    <input type="password" name="new_password" required class="form-input-styled">
                </div>
                <div class="form-group mb-25">
                    <label class="display-block mb-8 text-white-60">Confirm New Password</label>
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
    <form id="photoForm" method="POST" action="index.php?url=osas/profile" enctype="multipart/form-data" class="display-none">
        <input type="file" name="profile_photo" id="photoInput" onchange="document.getElementById('photoForm').submit()" accept="image/*">
    </form>

    <script src="assets/js/osas.js"></script>
</body>
</html>