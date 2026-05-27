<?php 
/** @var array $studentInfo */
/** @var string $message */
/** @var array $stats */
$studentInfo = $studentInfo ?? [];
$message = $message ?? '';
$stats = $stats ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Student Profile</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/student.css">
    <link rel="stylesheet" href="assets/css/student_profile_new.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal-overlay">
        <div class="modal-content text-left edit-profile-modal">
            <span class="modal-close" onclick="hideEditModal()">&times;</span>
            <div class="modal-header-edit">
                <h2>Edit Your Profile</h2>
                <p class="modal-subtitle">Update your personal information</p>
            </div>
            <form method="POST" action="index.php?url=student/profile" class="edit-profile-form">
                <div class="form-row-edit">
                    <div class="form-group-edit">
                        <label class="form-label-edit">Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($studentInfo['full_name']); ?>" required class="form-input-edit">
                    </div>
                    <div class="form-group-edit">
                        <label class="form-label-edit">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($studentInfo['username']); ?>" required class="form-input-edit">
                    </div>
                </div>
                
                <div class="form-row-edit">
                    <div class="form-group-edit">
                        <label class="form-label-edit">Student ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($studentInfo['student_id_number']); ?>" class="form-input-edit" disabled>
                    </div>
                </div>
                
                <div class="form-row-edit">
                    <div class="form-group-edit">
                        <label class="form-label-edit">Course</label>
                        <input type="text" name="course" value="<?php echo htmlspecialchars($studentInfo['course']); ?>" required class="form-input-edit">
                    </div>
                </div>
                
                <div class="form-row-edit">
                    <div class="form-group-edit">
                        <label class="form-label-edit">Year Level</label>
                        <input type="text" name="year_level" value="<?php echo htmlspecialchars($studentInfo['year_level']); ?>" required class="form-input-edit">
                    </div>
                    <div class="form-group-edit">
                        <label class="form-label-edit">Section</label>
                        <input type="text" name="section" value="<?php echo htmlspecialchars($studentInfo['section']); ?>" required class="form-input-edit">
                    </div>
                </div>
                
                <div class="form-group-edit">
                    <label class="form-label-edit">Bio</label>
                    <textarea name="bio" rows="4" class="form-textarea-edit" placeholder="Tell us a little about yourself..."><?php echo htmlspecialchars($studentInfo['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="modal-buttons-edit">
                    <button type="button" class="modal-btn-edit modal-btn-cancel" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" name="update_profile" class="modal-btn-edit modal-btn-save">Save Changes</button>
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
        <input type="file" name="profile_photo" id="photoInput" onchange="document.getElementById('photoForm').submit()" accept="image/*" class="display-none">
    </form>

    <main class="main-dashboard">
        <div class="welcome-section text-center mb-40">
            <h1 class="glow-text">Account Settings</h1>
            <p class="subtitle-text">Manage your digital identity and security credentials.</p>
        </div>

        <div class="profile-layout max-w-1000 mx-auto">
            <!-- Sidebar: Photo, QR & Actions -->
            <aside class="profile-sidebar-card">
                <div class="profile-photo-container mx-auto mb-20" onclick="document.getElementById('photoInput').click()" title="Change Profile Photo">
                    <?php 
                        $photoPath = !empty($studentInfo['profile_photo']) && $studentInfo['profile_photo'] !== 'default_profile.png' 
                            ? 'assets/img/profiles/' . $studentInfo['profile_photo'] 
                            : '';
                        $full_name = $studentInfo['full_name'] ?? 'User';
                        $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=150";
                    ?>
                    <img src="<?php echo $photoPath; ?>" alt="Profile" class="profile-photo" onerror="this.src='<?php echo $avatar_url; ?>'">
                    <div class="photo-edit-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </div>
                </div>
                
                <?php if (!empty($studentInfo['bio'])): ?>
                    <div class="profile-bio-sidebar">
                        <p class="bio-text-sidebar"><?php echo htmlspecialchars($studentInfo['bio']); ?></p>
                    </div>
                <?php endif; ?>

                <div class="profile-actions-modern">
                    <button onclick="showEditModal()" class="btn-profile-action btn-edit-p">Edit Profile Details</button>
                    <button onclick="showPasswordModal()" class="btn-profile-action btn-pass-p">Change Security Password</button>
                </div>
            </aside>

            <!-- Main Content: Stats & Personal Info -->
            <div class="profile-main-content">
                <!-- Statistics Card -->
                <div class="profile-data-card">
                    <h3 class="card-title-modern">Disciplinary Overview</h3>
                    <div class="stats-grid-modern">
                        <div class="stat-box-modern total">
                            <span class="val"><?php echo $stats['total'] ?? 0; ?></span>
                            <span class="lab">Total Cases</span>
                        </div>
                        <div class="stat-box-modern pending">
                            <span class="val"><?php echo $stats['pending'] ?? 0; ?></span>
                            <span class="lab">Active Cases</span>
                        </div>
                        <div class="stat-box-modern completed">
                            <span class="val"><?php echo $stats['completed'] ?? 0; ?></span>
                            <span class="lab">Settled Cases</span>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Card -->
                <div class="profile-data-card">
                    <h3 class="card-title-modern">Personal Information</h3>
                    <div class="info-grid-modern">
                        <div class="info-block">
                            <span class="label">Full Name</span>
                            <span class="value"><?php echo htmlspecialchars($full_name); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="label">Student ID</span>
                            <span class="value"><?php echo htmlspecialchars($studentInfo['student_id_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="label">Course</span>
                            <span class="value"><?php echo htmlspecialchars($studentInfo['course'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="label">Year & Section</span>
                            <span class="value"><?php echo htmlspecialchars(($studentInfo['year_level'] ?? 'N/A') . ' - ' . ($studentInfo['section'] ?? 'N/A')); ?></span>
                        </div>
                    </div>

                    <?php if(!empty($studentInfo['bio'])): ?>
                        <div class="profile-bio-modern mt-30">
                            "<?php echo htmlspecialchars($studentInfo['bio']); ?>"
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/student.js"></script>
</body>
</html>
