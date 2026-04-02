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
            <h1>Student Management</h1>
            <p>View and manage all registered students in the system.</p>
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
        <div class="modal-content" style="max-width: 850px; width: 90%;">
            <span class="modal-close" onclick="hideStudentModal()">&times;</span>
            <div id="modalLoading" class="p-40 fs-1-2 opacity-50">Loading profile...</div>
            <div id="modalContent" class="display-none">
                <!-- Left: Profile Info -->
                <div class="border-right-glass pr-30">
                    <div class="text-center mb-25">
                        <img id="det_photo" src="" class="student-avatar-large border-sage-3 mb-15" style="width: 120px; height: 120px; border-radius: 50%;">
                        <h2 id="det_name" class="mb-5"></h2>
                        <p id="det_id" class="text-sage-green fw-600 font-monospace"></p>
                    </div>
                    
                    <div class="student-info-grid grid-single gap-10">
                        <div class="info-item">
                            <span class="info-label">Course</span>
                            <div id="det_course" class="info-value"></div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Year & Section</span>
                            <div id="det_year_sec" class="info-value"></div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Biography</span>
                            <div id="det_bio" class="fs-0-9 font-italic text-white-70"></div>
                        </div>
                    </div>
                </div>

                <!-- Right: History -->
                <div class="pl-30">
                    <h3 class="text-mint-green mb-5">Violation History</h3>
                    <p class="fs-0-8 text-white-40">Chronological record of student conduct.</p>
                    <div id="det_history" class="history-list">
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