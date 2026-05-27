<?php 
/** @var string $message */
/** @var array $studentsByYear */
/** @var int $unreadCount */
/** @var array $notifications */
$message = $message ?? '';
$studentsByYear = $studentsByYear ?? [];
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
?>
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

    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section mb-40">
            <div class="flex-between align-center">
                <div>
                    <h1 class="glow-text mb-5">Student Management</h1>
                    <p class="subtitle-text">View and manage all registered students in the system.</p>
                </div>
                <button class="btn-primary-small" onclick="showAddStudentModal()">
                    <span>+</span> Add New Student
                </button>
            </div>
        </div>

        <div class="search-bar-standalone mb-60">
            <div class="search-container-full">
                <span class="search-icon-fixed"><img src="assets/img/icons/search.svg" alt="Search" width="20" height="20"></span>
                <input type="text" id="studentSearch" placeholder="Search by student name or ID number..." class="search-input-modern">
                <div id="searchDropdown" class="search-results-dropdown"></div>
            </div>
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

        <div id="noResults" class="text-center py-60 display-none" style="display: none;">
            <div class="fs-3-0 mb-10"><img src="assets/img/icons/search.svg" alt="Magnifying Glass icon" width="35" height="35"></div>
            <h2 class="text-white-50">No students found</h2>
            <p class="text-white-30">Try searching for a different name or ID number.</p>
        </div>

            <?php foreach ($studentsByYear as $year => $students): ?>
                <?php if (count($students) > 0): ?>
                    <section class="year-section" id="section-<?php echo str_replace(' ', '-', $year); ?>">
                        <div class="year-title">
                            <span><?php echo $year; ?> Level</span>
                            <span class="year-count-badge"><?php echo count($students); ?> Registered Students</span>
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
                                        <p class="text-mint-green fw-600 fs-0-7 mt-5"><?php echo htmlspecialchars($s['course'] . ' - ' . $s['section']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endforeach; ?>
        </main>

    <!-- Student Detail Modal -->
    <div id="studentModal" class="modal-overlay">
        <div class="modal-content student-modal-content">
            <span class="modal-close" onclick="hideStudentModal()">&times;</span>
            <div id="modalLoading" class="p-40 text-center fs-1-2 opacity-50">
                <span class="loading-spinner"></span>
                <p class="mt-15">Fetching student profile...</p>
            </div>
            
            <div id="modalContent" class="display-none">
                <div class="student-profile-layout">
                    <!-- Left: Profile Summary -->
                    <div class="profile-summary-side">
                        <div class="avatar-container-large mb-20">
                            <img id="det_photo" src="" class="student-avatar-large shadow-glow">
                        </div>
                        <h2 id="det_name" class="text-white mb-5"></h2>
                        <p id="det_id" class="text-sage-green fw-600 font-monospace"></p>
                        
                        <div class="admin-actions-vertical mt-30">
                            <button class="btn-secondary w-100 mb-10" onclick="showEditStudentModal()">Edit Details</button>
                            <button class="btn-danger w-100" onclick="confirmDeleteStudent()">Delete Account</button>
                        </div>
                    </div>

                    <!-- Right: Detailed Info & History -->
                    <div class="profile-details-side">
                        <div class="glass-info-card-horizontal mb-30">
                            <div class="info-group-minimal">
                                <span class="info-label-styled">Course</span>
                                <div id="det_course" class="info-value-styled"></div>
                            </div>
                            <div class="info-group-minimal">
                                <span class="info-label-styled">Year & Section</span>
                                <div id="det_year_sec" class="info-value-styled"></div>
                            </div>
                            <div class="info-group-minimal grid-column-full">
                                <span class="info-label-styled">Biography</span>
                                <div id="det_bio" class="info-value-styled bio-text"></div>
                            </div>
                        </div>

                        <div class="violation-history-section">
                            <div class="section-divider-minimal mb-20">
                                <h3 class="divider-text">Violation History</h3>
                                <span class="divider-line"></span>
                            </div>
                            <div id="det_history" class="history-list-modern-scroll">
                                <!-- Filled by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideAddStudentModal()">&times;</span>
            <h2>Add New Student</h2>
            <form action="index.php?url=osas/students" method="POST" class="standard-form">
                <input type="hidden" name="add_student" value="1">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" required placeholder="e.g. juandelacruz">
                </div>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-input" required placeholder="e.g. Juan De La Cruz">
                </div>
                <div class="form-group">
                    <label class="form-label">Student ID Number</label>
                    <input type="text" name="student_id" class="form-input" required placeholder="e.g. 21-12345">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Course</label>
                        <select name="course" class="form-select" required>
                            <option value="BSIT">BSIT</option>
                            <option value="BSIS">BSIS</option>
                            <option value="BIT">BIT</option>
                            <option value="BTVTED">BTVTED</option>
                            <option value="BSECE">BSECE</option>
                            <option value="BSCPE">BSCPE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year Level</label>
                        <select name="year_level" class="form-select" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Section</label>
                    <input type="text" name="section" class="form-input" required placeholder="e.g. A, B, C">
                </div>
                <p class="text-white-40 fs-0-8 mb-20 text-center">Note: Default password will be 'student123'</p>
                <div class="flex-row justify-center mt-30">
                    <button type="submit" class="btn-primary-enhanced px-40">Create Student Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideEditStudentModal()">&times;</span>
            <h2>Edit Student Details</h2>
            <form action="index.php?url=osas/students" method="POST" class="standard-form">
                <input type="hidden" name="update_student" value="1">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Student ID Number</label>
                    <input type="text" name="student_id" id="edit_student_id" class="form-input" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Course</label>
                        <select name="course" id="edit_course" class="form-select" required>
                            <option value="BSIT">BSIT</option>
                            <option value="BSIS">BSIS</option>
                            <option value="BIT">BIT</option>
                            <option value="BTVTED">BTVTED</option>
                            <option value="BSECE">BSECE</option>
                            <option value="BSCPE">BSCPE</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year Level</label>
                        <select name="year_level" id="edit_year_level" class="form-select" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Section</label>
                    <input type="text" name="section" id="edit_section" class="form-input" required>
                </div>
                <div class="flex-row justify-center mt-30">
                    <button type="submit" class="btn-primary-enhanced px-40">Update Student Details</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteStudentModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideDeleteStudentModal()">&times;</span>
            <h2>Delete Student Account?</h2>
            <p class="mb-30 text-white-70">This will permanently remove the student and all their violation records. This action cannot be undone.</p>
            <form action="index.php?url=osas/students" method="POST">
                <input type="hidden" name="delete_student" value="1">
                <input type="hidden" name="user_id" id="delete_user_id">
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideDeleteStudentModal()">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn-yes">Yes, Delete Account</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/osas.js"></script>
</body>
</html>