<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Student Management</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/osas.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-dashboard">
        <div class="welcome-section">
            <div class="flex-between align-center mb-10">
                <div>
                    <h1>Student Management</h1>
                    <p>View and manage all registered students in the system.</p>
                </div>
                <div class="search-container max-w-400 w-100">
                    <span class="fs-1-2 opacity-50 mr-10">🔍</span>
                    <input type="text" id="studentSearch" placeholder="Search by name or ID number..." class="search-input-clear w-100">
                    <div id="searchDropdown" class="search-results-dropdown"></div>
                </div>
            </div>
        </div>

        <div id="noResults" class="text-center py-60 display-none">
            <div class="fs-3-0 mb-10">🔍</div>
            <h2 class="text-white-50">No students found</h2>
            <p class="text-white-30">Try searching for a different name or ID number.</p>
        </div>

            <?php foreach ($studentsByYear as $year => $students): ?>
                <?php if (count($students) > 0): ?>
                    <div class="year-section" id="section-<?php echo str_replace(' ', '-', $year); ?>">
                        <div class="year-title">
                            <?php echo $year; ?>
                            <span class="fs-0-8 fw-400 opacity-50"><?php echo count($students); ?> Students</span>
                        </div>
                        <div class="student-grid">
                            <?php foreach ($students as $s): ?>
                                <div class="student-mini-card" onclick="viewStudent(<?php echo $s['id']; ?>)">
                                    <?php 
                                        $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($s['full_name']) . "&background=1b4332&color=fff&size=50";
                                        $photo = !empty($s['profile_photo']) && $s['profile_photo'] !== 'default_profile.png' 
                                            ? "assets/img/profiles/" . $s['profile_photo'] 
                                            : $avatarUrl;
                                    ?>
                                    <img src="<?php echo $photo; ?>" class="student-avatar" onerror="this.src='<?php echo $avatarUrl; ?>'">
                                    <div class="student-basic">
                                        <h4><?php echo htmlspecialchars($s['full_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($s['student_id_number']); ?></p>
                                        <p class="text-mint-green fw-600"><?php echo htmlspecialchars($s['role']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </main>
    </div>

    <!-- Student Detail Modal -->
    <div id="studentModal" class="modal-overlay">
        <div class="modal-content student-modal-content">
            <span class="modal-close" onclick="hideStudentModal()">&times;</span>
            <div id="modalLoading" class="p-40 text-center fs-1-2 opacity-50">
                <span class="loading-spinner"></span>
                <p class="mt-15">Fetching student profile...</p>
            </div>
            
            <div id="modalContent" class="display-none flex-column align-center">
                <!-- Top: Centralized Profile Header -->
                <div class="profile-header-centralized text-center mb-40">
                    <div class="avatar-container mb-20">
                        <img id="det_photo" src="" class="student-avatar-large shadow-glow" style="width: 130px; height: 130px; border-radius: 50%; border: 3px solid var(--sage-green);">
                    </div>
                    <h2 id="det_name" class="fs-2-2 fw-700 text-white mb-5"></h2>
                    <p id="det_id" class="text-sage-green fs-1-1 fw-600 font-monospace tracking-wide"></p>
                </div>
                
                <!-- Middle: Information Grid -->
                <div class="info-grid-centralized mb-50 w-100">
                    <div class="glass-info-card">
                        <div class="info-group">
                            <span class="info-label-styled">Course</span>
                            <div id="det_course" class="info-value-styled"></div>
                        </div>
                        <div class="info-group">
                            <span class="info-label-styled">Year & Section</span>
                            <div id="det_year_sec" class="info-value-styled"></div>
                        </div>
                        <div class="info-group grid-column-full border-top-glass pt-20 mt-10">
                            <span class="info-label-styled">Biography</span>
                            <div id="det_bio" class="info-value-styled bio-text"></div>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Violation History -->
                <div class="violation-history-centralized w-100">
                    <div class="section-divider mb-30">
                        <span class="divider-line"></span>
                        <h3 class="divider-text">Violation History</h3>
                        <span class="divider-line"></span>
                    </div>
                    <p class="text-center text-white-40 fs-0-9 mb-25">Chronological record of disciplinary actions and conduct.</p>
                    <div id="det_history" class="history-list-modern">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideLogoutModal()">&times;</span>
            <h2>Logging Out?</h2>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-no" onclick="hideLogoutModal()">Cancel</button>
                <a href="index.php?url=auth/logout" class="modal-btn modal-btn-yes" style="text-decoration: none;">Yes, logout</a>
            </div>
        </div>
    </div>

    <script src="assets/js/osas.js"></script>
</body>
</html>