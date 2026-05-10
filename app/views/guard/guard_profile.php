<?php 
/** @var array $guardInfo */
/** @var string $message */
/** @var int $unreadCount */
/** @var mysqli_result|array $notifications */
/** @var mysqli_result|null $guardsList */
$guardInfo = $guardInfo ?? ['username' => '', 'full_name' => '', 'bio' => '', 'profile_photo' => 'default_profile.png'];
$message = $message ?? '';
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
$guardsList = $guardsList ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Guard Profile</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/guard.css">
    <link rel="stylesheet" href="assets/css/student_profile_new.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section text-center mb-40">
            <h1 class="glow-text">Staff Profile</h1>
            <p class="subtitle-text">Manage your security credentials and professional information.</p>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                <?php if (!empty($message)): ?>
                    const msg = "<?php echo addslashes($message); ?>";
                    const isError = msg.toLowerCase().includes('error') || msg.toLowerCase().includes('incorrect') || msg.toLowerCase().includes('match');
                    showToast(isError ? 'Action Failed' : 'Success', msg, isError ? 'error' : 'success');
                <?php endif; ?>
            });
        </script>

        <div class="profile-layout max-w-1000 mx-auto">
            <!-- Sidebar: Photo, QR & Actions -->
            <aside class="profile-sidebar-card">
                <div class="profile-photo-container mx-auto mb-40" onclick="document.getElementById('photoInput').click()" title="Change Profile Photo">
                    <?php 
                        $photoPath = !empty($guardInfo['profile_photo']) && $guardInfo['profile_photo'] !== 'default_profile.png' 
                            ? 'assets/img/profiles/' . $guardInfo['profile_photo'] 
                            : '';
                        $full_name = $guardInfo['full_name'] ?? 'Staff Member';
                        $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=150";
                    ?>
                    <img src="<?php echo $photoPath; ?>" alt="Profile" class="profile-photo" onerror="this.src='<?php echo $avatar_url; ?>'">
                    <div class="photo-edit-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </div>
                </div>

                <div id="qrcode" class="qr-card-modern mb-25"></div>
                <p class="text-white-40 fs-0-75 mb-30">Scan to verify security credentials</p>

                <div class="profile-actions-modern">
                    <button onclick="showEditModal()" class="btn-profile-action btn-edit-p">Edit Profile Details</button>
                    <button onclick="showPasswordModal()" class="btn-profile-action btn-pass-p">Change Security Password</button>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="profile-main-content">
                <!-- Staff Info Card -->
                <div class="profile-data-card">
                    <h3 class="card-title-modern">Professional Information</h3>
                    <div class="info-grid-modern">
                        <div class="info-block">
                            <span class="label">Full Name</span>
                            <span class="value"><?php echo htmlspecialchars($full_name); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="label">Username</span>
                            <span class="value"><?php echo htmlspecialchars($guardInfo['username'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="label">Role</span>
                            <span class="value">Security Personnel</span>
                        </div>
                        <div class="info-block">
                            <span class="label">Status</span>
                            <span class="value text-sage-green">Active Duty</span>
                        </div>
                    </div>

                    <?php if(!empty($guardInfo['bio'])): ?>
                        <div class="profile-bio-modern mt-30">
                            "<?php echo htmlspecialchars($guardInfo['bio']); ?>"
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Security Team Card -->
                <div class="profile-data-card">
                    <h3 class="card-title-modern">Security Team Members</h3>
                    <div class="guards-mini-list mt-10">
                        <?php if ($guardsList && $guardsList->num_rows > 0): ?>
                            <div class="guards-grid">
                                <?php while($g = $guardsList->fetch_assoc()): ?>
                                    <div class="guard-member-pill">
                                        <div class="member-dot"></div>
                                        <?php echo htmlspecialchars($g['name']); ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-white-30 fs-0-9">No other staff members listed.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const qrContainer = document.getElementById("qrcode");
                const userId = "<?php echo $guardInfo['id'] ?? $_SESSION['user_id'] ?? ''; ?>";
                
                if (!userId) {
                    qrContainer.style.background = "rgba(231, 76, 60, 0.1)";
                    qrContainer.innerHTML = "<p style='color: #e74c3c; font-size: 0.8rem;'>Error: ID not found</p>";
                    return;
                }

                const protocol = window.location.protocol;
                const host = window.location.host;
                const pathParts = window.location.pathname.split('/');
                const basePath = pathParts.slice(0, pathParts.indexOf('public') + 1).join('/');
                const qrData = `${protocol}//${host}${basePath}/index.php?url=home/view_user&id=${userId}`;
                
                new QRCode(qrContainer, {
                    text: qrData,
                    width: 140,
                    height: 140,
                    colorDark : "#1b4332",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            });
        </script>
    </main>

    <!-- Hidden Photo Upload -->
    <form id="photoForm" action="index.php?url=guard/profile" method="POST" enctype="multipart/form-data" class="display-none">
        <input type="file" name="profile_photo" id="photoInput" onchange="this.form.submit()">
    </form>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideEditModal()">&times;</span>
            <h2 class="mb-25">Edit Profile</h2>
            <form action="index.php?url=guard/profile" method="POST">
                <div class="mb-20">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($guardInfo['username'] ?? ''); ?>" required>
                </div>
                <div class="mb-20">
                    <label class="form-label">Professional Bio</label>
                    <textarea name="bio" class="form-textarea" placeholder="Write something about yourself..."><?php echo htmlspecialchars($guardInfo['bio'] ?? ''); ?></textarea>
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
            <h2 class="mb-25">Change Password</h2>
            <form action="index.php?url=guard/profile" method="POST">
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

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideLogoutModal()">&times;</span>
            <h2>Logging Out?</h2>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-no" onclick="hideLogoutModal()">Cancel</button>
                <a href="index.php?url=auth/logout" class="modal-btn modal-btn-yes no-underline">Yes, logout</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>