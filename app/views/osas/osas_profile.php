<?php 
/** @var array $userData */
/** @var string $message */
/** @var int $unreadCount */
/** @var array $notifications */
$userData = $userData ?? ['username' => '', 'full_name' => 'Administrator', 'bio' => '', 'profile_photo' => 'default_profile.png'];
$message = $message ?? '';
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | OSAS Profile</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/osas.css">
    <link rel="stylesheet" href="assets/css/student_profile_new.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section text-center mb-40">
            <h1 class="glow-text">Administrative Profile</h1>
            <p class="subtitle-text">Manage your system access and administrative identity.</p>
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

        <div class="profile-layout max-w-1000 mx-auto">
            <!-- Sidebar -->
            <aside class="profile-sidebar-card">
                <div class="profile-photo-container mx-auto mb-40" onclick="document.getElementById('photoInput').click()" title="Change Admin Photo">
                    <?php 
                        $photoPath = !empty($userData['profile_photo']) && $userData['profile_photo'] !== 'default_profile.png' 
                            ? 'assets/img/profiles/' . $userData['profile_photo'] 
                            : '';
                        $full_name = $userData['full_name'] ?? 'Administrator';
                        $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=150";
                    ?>
                    <img src="<?php echo $photoPath; ?>" alt="Profile" class="profile-photo" onerror="this.src='<?php echo $avatar_url; ?>'">
                    <div class="photo-edit-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </div>
                </div>

                <div class="profile-actions-modern">
                    <button onclick="showEditModal()" class="btn-profile-action btn-edit-p">Update Profile Info</button>
                    <button onclick="showPasswordModal()" class="btn-profile-action btn-pass-p">Security Settings</button>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="profile-main-content">
                <!-- Admin Info Card -->
                <div class="profile-data-card">
                    <h3 class="card-title-modern">System Identity</h3>
                    <div class="info-grid-modern">
                        <div class="info-block">
                            <span class="label">Display Name</span>
                            <span class="value"><?php echo htmlspecialchars($full_name); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="label">Username</span>
                            <span class="value"><?php echo htmlspecialchars($userData['username'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="label">Access Role</span>
                            <span class="value">OSAS Administrator</span>
                        </div>
                        <div class="info-block">
                            <span class="label">Status</span>
                            <span class="value text-sage-green">System Master</span>
                        </div>
                    </div>

                    <?php if(!empty($userData['bio'])): ?>
                        <div class="profile-bio-modern mt-30">
                            "<?php echo htmlspecialchars($userData['bio']); ?>"
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Achievements Card -->
                <div class="profile-data-card">
                    <h3 class="card-title-modern">Certificates & Awards</h3>
                    <div class="info-grid-modern">
                        <div class="achievement-item-modern">
                            <div class="achievement-icon-modern">
                                <img src="assets/img/icons/student.svg" alt="Award" width="22">
                            </div>
                            <div class="achievement-text-modern">
                                <h4>Affairs Excellence</h4>
                                <p>Distinction - 2024</p>
                            </div>
                        </div>
                        <div class="achievement-item-modern">
                            <div class="achievement-icon-modern">
                                <img src="assets/img/icons/shield.svg" alt="Award" width="22">
                            </div>
                            <div class="achievement-text-modern">
                                <h4>Policy Board Cert</h4>
                                <p>Management - 2025</p>
                            </div>
                        </div>
                        <div class="achievement-item-modern">
                            <div class="achievement-icon-modern">
                                <img src="assets/img/icons/userpro.svg" alt="Award" width="22">
                            </div>
                            <div class="achievement-text-modern">
                                <h4>Leadership Award</h4>
                                <p>OSAS Excellence - 2026</p>
                            </div>
                        </div>
                        <div class="achievement-item-modern">
                            <div class="achievement-icon-modern">
                                <img src="assets/img/icons/analytics.svg" alt="Award" width="22">
                            </div>
                            <div class="achievement-text-modern">
                                <h4>Efficiency Cert</h4>
                                <p>Implementation - 2026</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Hidden Photo Upload -->
    <form id="photoForm" action="index.php?url=osas/profile" method="POST" enctype="multipart/form-data" class="display-none">
        <input type="file" name="profile_photo" id="photoInput" onchange="this.form.submit()" class="display-none">
    </form>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideEditModal()">&times;</span>
            <h2 class="mb-25">Edit Admin Profile</h2>
            <form action="index.php?url=osas/profile" method="POST">
                <div class="mb-20">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" required>
                </div>
                <div class="mb-20">
                    <label class="form-label">Administrative Bio</label>
                    <textarea name="bio" class="form-textarea" placeholder="Describe your administrative role..."><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                </div>
                <div class="modal-buttons mt-30">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" name="update_profile" class="modal-btn modal-btn-yes">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hidePasswordModal()">&times;</span>
            <h2 class="mb-25">Change Security Password</h2>
            <form action="index.php?url=osas/profile" method="POST">
                <div class="mb-15">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="old_password" class="form-input" required>
                </div>
                <div class="mb-15">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-input" required>
                </div>
                <div class="mb-20">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>
                <div class="modal-buttons mt-30">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hidePasswordModal()">Cancel</button>
                    <button type="submit" name="change_password" class="modal-btn modal-btn-yes">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>