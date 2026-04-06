<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Student Profile</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/student.css">
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
        <form method="POST" action="index.php?url=student/profile" class="flex-column gap-20">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($studentInfo['username'] ?? ''); ?>" required class="form-input-styled">
            </div>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($studentInfo['full_name'] ?? ''); ?>" required class="form-input-styled">
            </div>

            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="4" class="form-textarea-styled"><?php echo htmlspecialchars($studentInfo['bio'] ?? ''); ?></textarea>
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
            <form method="POST" action="index.php?url=student/profile" class="flex-column gap-20">
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
    <form id="photoForm" method="POST" action="index.php?url=student/profile" enctype="multipart/form-data" class="display-none">
        <input type="file" name="profile_photo" id="photoInput" onchange="document.getElementById('photoForm').submit()" accept="image/*">
    </form>

    <main class="main-dashboard">
        <div class="welcome-section text-center">
            <h1>My Profile</h1>
            <p>Manage your account settings and your activity statistics.</p>
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
                            $photoPath = !empty($studentInfo['profile_photo']) && $studentInfo['profile_photo'] !== 'default_profile.png' 
                                ? 'assets/img/profiles/' . $studentInfo['profile_photo'] 
                                : '';
                            $full_name = $studentInfo['full_name'] ?? 'User';
                            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=150";
                        ?>
                        <img src="<?php echo $photoPath; ?>" alt="Profile" class="profile-photo" onerror="this.src='<?php echo $avatar_url; ?>'">
                        <div class="photo-edit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        </div>
                    </div>
                    
                    <div class="mt-20">
                        <h2 class="fs-1-8 mb-5"><?php echo htmlspecialchars($studentInfo['username'] ?? ''); ?></h2>
                        <p class="fs-1-1 text-white-80 fw-300"><?php echo htmlspecialchars($full_name); ?></p>
                        <p class="text-white-50 fs-0-9 mt-5">ID Number: <?php echo htmlspecialchars($studentInfo['student_id_number'] ?? 'N/A'); ?></p>
                    </div>

                    <?php if(!empty($studentInfo['bio'])): ?>
                        <p class="profile-bio-text">
                            "<?php echo htmlspecialchars($studentInfo['bio']); ?>"
                        </p>
                    <?php endif; ?>

                    <div class="flex gap-15 justify-center mt-30">
                        <button onclick="showEditModal()" class="modal-btn modal-btn-yes px-40">Edit Profile</button>
                        <button onclick="showPasswordModal()" class="modal-btn modal-btn-no px-40">Change Password</button>
                    </div>
                </div>

                <div class="stats-grid border-top-glass pt-30 mt-40">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['total'] ?? 0; ?></span>
                        <span class="stat-label">Total Violations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value text-error"><?php echo $stats['pending'] ?? 0; ?></span>
                        <span class="stat-label">Active Sanctions</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value text-sage-green"><?php echo $stats['completed'] ?? 0; ?></span>
                        <span class="stat-label">Completed</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student.js"></script>
</body>
</html>
