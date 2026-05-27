<?php 
/** @var array $guardInfo */
/** @var string $message */
/** @var int $unreadCount */
/** @var mysqli_result|array $notifications */
/** @var mysqli_result|null $guardsList */
/** @var array $violationsByGuard */
$guardInfo = $guardInfo ?? ['username' => '', 'full_name' => '', 'bio' => '', 'profile_photo' => 'default_profile.png', 'guard_rank' => 'I', 'schedule' => 'Full Time'];
$message = $message ?? '';
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
$guardsList = $guardsList ?? null;
$violationsByGuard = $violationsByGuard ?? [];
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
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content text-left edit-profile-modal">
            <span class="modal-close" onclick="hideEditModal()">&times;</span>
            <div class="modal-header-edit">
                <h2>Edit Your Profile</h2>
                <p class="modal-subtitle">Update your professional information</p>
            </div>
            <form method="POST" action="index.php?url=guard/profile" class="edit-profile-form">
                <div class="form-row-edit">
                    <div class="form-group-edit">
                        <label class="form-label-edit">Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($guardInfo['full_name'] ?? ''); ?>" required class="form-input-edit">
                    </div>
                    <div class="form-group-edit">
                        <label class="form-label-edit">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($guardInfo['username'] ?? ''); ?>" required class="form-input-edit">
                    </div>
                </div>
                
                <div class="form-group-edit">
                    <label class="form-label-edit">Professional Bio</label>
                    <textarea name="bio" rows="4" class="form-textarea-edit" placeholder="Write something about yourself..."><?php echo htmlspecialchars($guardInfo['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="modal-buttons-edit mt-30">
                    <button type="button" class="modal-btn-edit modal-btn-cancel" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" name="update_profile" class="modal-btn-edit modal-btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-content text-left">
            <span class="modal-close" onclick="hidePasswordModal()">&times;</span>
            <div class="modal-header-edit">
                <h2>Change Password</h2>
                <p class="modal-subtitle">Update your security credentials</p>
            </div>
            <form method="POST" action="index.php?url=guard/profile" class="edit-profile-form">
                <div class="form-group-edit">
                    <label class="form-label-edit">Current Password</label>
                    <input type="password" name="old_password" required class="form-input-edit">
                </div>
                <div class="form-group-edit">
                    <label class="form-label-edit">New Password</label>
                    <input type="password" name="new_password" required class="form-input-edit">
                </div>
                <div class="form-group-edit">
                    <label class="form-label-edit">Confirm New Password</label>
                    <input type="password" name="confirm_password" required class="form-input-edit">
                </div>
                <div class="modal-buttons-edit">
                    <button type="button" class="modal-btn-edit modal-btn-cancel" onclick="hidePasswordModal()">Cancel</button>
                    <button type="submit" name="change_password" class="modal-btn-edit modal-btn-save">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Guard Info Modal -->
    <div id="viewGuardModal" class="modal-overlay">
        <div class="modal-content text-left view-guard-modal" style="max-width: 800px; width: 90%;">
            <span class="modal-close" onclick="hideViewGuardModal()">&times;</span>
            <div class="modal-header-edit">
                <h2 id="viewGuardName">Guard Information</h2>
                <p class="modal-subtitle">View guard details and submitted violations</p>
            </div>
            
            <!-- Guard Info Section -->
            <div class="profile-data-card mb-20">
                <h3 class="card-title-modern">Personal Information</h3>
                <div class="info-grid-modern">
                    <div class="info-block">
                        <span class="label">Full Name</span>
                        <span class="value" id="viewGuardFullName">-</span>
                    </div>
                    <div class="info-block">
                        <span class="label">Rank</span>
                        <span class="value text-sage-green" id="viewGuardRank">-</span>
                    </div>
                    <div class="info-block">
                        <span class="label">Age</span>
                        <span class="value" id="viewGuardAge">-</span>
                    </div>
                    <div class="info-block">
                        <span class="label">Schedule</span>
                        <span class="value" id="viewGuardSchedule">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Violations Submitted Section -->
            <div class="profile-data-card">
                <h3 class="card-title-modern">Violations Submitted to OSAS</h3>
                <div id="viewGuardViolations" class="mt-15">
                    <p class="text-white-50">Loading violations...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Photo Upload -->
    <form id="photoForm" action="index.php?url=guard/profile" method="POST" enctype="multipart/form-data" class="display-none">
        <input type="file" name="profile_photo" id="photoInput" onchange="this.form.submit()" class="display-none">
    </form>

    <main class="main-dashboard">
        <div class="welcome-section text-center mb-40">
            <h1 class="glow-text">Staff Profile</h1>
            <p class="subtitle-text">Manage your security credentials and professional information.</p>
        </div>

        <script>
            // Store violations data in JavaScript
            const violationsByGuard = <?php echo json_encode($violationsByGuard); ?>;
            
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
                <div class="profile-photo-container mx-auto mb-20" onclick="document.getElementById('photoInput').click()" title="Change Profile Photo">
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
                
                <?php if(!empty($guardInfo['bio'])): ?>
                    <div class="profile-bio-sidebar">
                        <p class="bio-text-sidebar"><?php echo htmlspecialchars($guardInfo['bio']); ?></p>
                    </div>
                <?php endif; ?>

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
                        <?php 
                        // Reset the result pointer
                        if ($guardsList) {
                            $guardsList->data_seek(0);
                        }
                        ?>
                        <?php if ($guardsList && $guardsList->num_rows > 0): ?>
                            <div class="security-team-cards">
                                <?php while($g = $guardsList->fetch_assoc()): ?>
                                    <div class="security-team-card" onclick="showViewGuardModal(<?php echo htmlspecialchars(json_encode($g['id'])); ?>, <?php echo htmlspecialchars(json_encode($g['name'])); ?>)">
                                        <div class="security-team-card-avatar">
                                            <?php 
                                                $gName = $g['name'] ?? 'Security';
                                                $gAvatar = "https://ui-avatars.com/api/?name=" . urlencode($gName) . "&background=1b4332&color=fff&size=64";
                                            ?>
                                            <img src="<?php echo $gAvatar; ?>" alt="Security Avatar">
                                        </div>
                                        <div class="security-team-card-info">
                                            <h4 class="security-team-card-name"><?php echo htmlspecialchars($gName); ?></h4>
                                            <p class="security-team-card-role">Security Personnel</p>
                                        </div>
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
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        function showEditModal() { 
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.style.display = 'flex';
                if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
            }
        }

        function hideEditModal() { 
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.style.display = 'none';
                if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
            }
        }

        function showPasswordModal() { 
            const modal = document.getElementById('passwordModal');
            if (modal) {
                modal.style.display = 'flex';
                if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
            }
        }

        function hidePasswordModal() { 
            const modal = document.getElementById('passwordModal');
            if (modal) {
                modal.style.display = 'none';
                if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
            }
        }

        function showViewGuardModal(guardId, guardName) {
            document.getElementById('viewGuardName').textContent = guardName;
            document.getElementById('viewGuardFullName').textContent = guardName;
            document.getElementById('viewGuardRank').textContent = 'Security Personnel';
            document.getElementById('viewGuardAge').textContent = '-';
            document.getElementById('viewGuardSchedule').textContent = 'Full Time';
            loadGuardViolations(guardName);
            
            const modal = document.getElementById('viewGuardModal');
            if (modal) {
                modal.style.display = 'flex';
                if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
            }
        }
        
        function hideViewGuardModal() {
            const modal = document.getElementById('viewGuardModal');
            if (modal) {
                modal.style.display = 'none';
                if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
            }
        }
        
        function loadGuardViolations(guardName) {
            const container = document.getElementById('viewGuardViolations');
            container.innerHTML = '<p class="text-white-50">Loading violations...</p>';
            
            setTimeout(() => {
                const violations = violationsByGuard[guardName] || [];
                
                if (violations.length === 0) {
                    container.innerHTML = '<p class="text-white-50">No violations submitted by this guard yet.</p>';
                    return;
                }
                
                let html = '<div class="violation-list-mini">';
                violations.forEach(v => {
                    const date = new Date(v.created_at);
                    const formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                    html += `
                        <div class="violation-item-mini">
                            <div class="violation-item-mini-header">
                                <span class="violation-type-mini">${v.violation_type || 'Minor'}</span>
                                <span class="violation-date-mini">${formattedDate}</span>
                            </div>
                            <p class="violation-student-mini">Student: ${v.student_name || 'Unknown'}</p>
                            <p class="violation-desc-mini">${v.description || 'No description'}</p>
                        </div>
                    `;
                });
                html += '</div>';
                html += `
                    <style>
                        .violation-list-mini { max-height: 250px; overflow-y: auto; }
                        .violation-item-mini { background: rgba(255,255,255,0.05); border-radius: 10px; padding: 12px; margin-bottom: 10px; }
                        .violation-item-mini-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
                        .violation-type-mini { background: linear-gradient(135deg, #2d6a4f, #40916c); padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
                        .violation-date-mini { color: rgba(255,255,255,0.6); font-size: 0.85rem; }
                        .violation-student-mini { color: #a7f3d0; margin: 6px 0 4px; font-size: 0.9rem; font-weight: 500; }
                        .violation-desc-mini { color: rgba(255,255,255,0.85); margin: 0; font-size: 0.95rem; }
                    </style>
                `;
                
                container.innerHTML = html;
            }, 300);
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const editModal = document.getElementById('editModal');
            const passwordModal = document.getElementById('passwordModal');
            const viewGuardModal = document.getElementById('viewGuardModal');
            const logoutModal = document.getElementById('logoutModal');
            
            if (event.target === editModal) hideEditModal();
            if (event.target === passwordModal) hidePasswordModal();
            if (event.target === viewGuardModal) hideViewGuardModal();
            if (event.target === logoutModal && typeof hideLogoutModal === 'function') hideLogoutModal();
        });
    </script>
</body>
</html>
