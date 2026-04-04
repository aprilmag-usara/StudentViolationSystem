<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Guard Dashboard</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/guard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section">
            <h1>Guard Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($displayName); ?>. Ready to record violations?</p>
        </div>

        <?php if (!empty($message)): 
            $isError = strpos(strtolower($message), 'error') !== false || strpos(strtolower($message), 'no student') !== false;
        ?>
            <div class="toast-container" id="toast">
                <div class="toast-message <?php echo $isError ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid grid-single">
            <!-- QR Scanner & Search Section -->
            <div class="glass-card">
                <div class="card-header">
                    <h3 class="text-sage-green">Scan or Search Student</h3>
                    <span class="fs-0-8 text-white-50">Today: <?php echo date('M d, Y'); ?></span>
                </div>

                <div class="scanner-area" id="scannerArea">
                    <div id="scannerIcon" class="scanner-icon"><img src="https://proicons.com/icon/11866.svg" alt="Camera icon" width="70" height="70"></div>
                    <h4 id="scannerTitle">QR Code Scanner</h4>
                    <p id="scannerDesc" class="scanner-desc">Position the student ID QR code within the frame</p>
                    
                    <!-- Scanner Container -->
                    <div id="reader" class="reader-container"></div>
                    
                    <div class="scanner-controls">
                        <button id="startScanner" class="modal-btn modal-btn-yes px-30 py-10">Initialize Camera</button>
                        <button id="stopScanner" class="modal-btn modal-btn-no px-30 py-10 display-none btn-stop-camera">Stop Camera</button>
                    </div>
                </div>

                <div class="manual-search-divider">
                    <span>OR SEARCH MANUALLY</span>
                    <hr>
                </div>

                <form method="POST" action="index.php?url=guard/dashboard" class="search-container" id="searchForm">
                    <input type="text" name="student_search_query" id="studentSearchInput" class="search-input" placeholder="Enter student name or ID number..." required autocomplete="off">
                    <button type="submit" name="search_student" class="search-btn-absolute">Search</button>
                    
                    <!-- Live Search Dropdown -->
                    <div id="searchResultsDropdown" class="search-results-dropdown"></div>
                </form>
            </div>

            <?php if ($studentData): ?>
            <!-- Student Result & Violation Form -->
            <div class="student-result-card">
                <!-- Student Info -->
                <div class="glass-card">
                    <h3 class="student-info-header">Student Information</h3>
                    <div class="text-center">
                        <?php 
                            $full_name = $studentData['full_name'];
                            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=100";
                            $photoPath = !empty($studentData['profile_photo']) && $studentData['profile_photo'] !== 'default_profile.png' 
                                ? 'assets/img/profiles/' . $studentData['profile_photo'] 
                                : '';
                        ?>
                        <img src="<?php echo $photoPath; ?>" onerror="this.src='<?php echo $avatar_url; ?>'" class="profile-img-large">
                        <h2 class="mb-5"><?php echo htmlspecialchars($full_name); ?></h2>
                        <p class="text-sage-green fw-600"><?php echo htmlspecialchars($studentData['student_id_number']); ?></p>
                    </div>
                    
                    <div class="mt-25 grid grid-gap-15">
                        <div class="info-item-box">
                            <span class="info-item-label">Course</span>
                            <span class="info-item-value"><?php echo htmlspecialchars($studentData['course']); ?></span>
                        </div>
                        <div class="info-item-box">
                            <span class="info-item-label">Year & Section</span>
                            <span class="info-item-value"><?php echo htmlspecialchars($studentData['year_level'] . ' - ' . $studentData['section']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Violation Form -->
                <div class="glass-card">
                    <h3 class="student-info-header">Record Violation</h3>
                    <form method="POST" action="index.php?url=guard/dashboard">
                        <input type="hidden" name="student_user_id" value="<?php echo $studentData['id']; ?>">
                        
                        <div class="mb-20">
                            <label class="fs-0-9 text-white-70">Violation Type</label>
                            <select name="violation_type" class="form-select" required>
                                <option value="MINOR">Minor Violation</option>
                                <option value="MAJOR">Major Violation</option>
                            </select>
                        </div>

                        <div class="mb-20">
                            <label class="fs-0-9 text-white-70">Guard in Charge</label>
                            <select name="recorded_by_guard_name" class="form-select" required>
                                <option value="">Select Guard on Duty...</option>
                                <?php if ($guardList): ?>
                                    <?php while($g = $guardList->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($g['name']); ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="System Admin">Default Guard (DB Needs Update)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-20">
                            <label class="fs-0-9 text-white-70">Description</label>
                            <textarea name="description" class="form-textarea" placeholder="Provide details about the violation..." required></textarea>
                        </div>

                        <div class="modal-buttons">
                            <button type="button" class="modal-btn modal-btn-no" onclick="window.location.href='index.php?url=guard/dashboard'">Clear</button>
                            <button type="submit" name="submit_violation" class="modal-btn modal-btn-yes">Submit to OSAS</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/guard.js"></script>
</body>
</html>