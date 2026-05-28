<?php
require_once __DIR__ . '/../models/Violation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class OSASController extends BaseController {
    private $userModel;
    private $violationModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new User($db);
        $this->violationModel = new Violation($db);
    }

    private function getNavData($userId) {
        $role = $_SESSION['role'] ?? null;
        $notifResult = $this->userModel->getNotifications($userId, 5, $role);
        $notifications = [];
        if ($notifResult) {
            while ($row = $notifResult->fetch_assoc()) {
                $notifications[] = $row;
            }
        }
        return [
            'unreadCount' => $this->userModel->getUnreadNotificationCount($userId, $role),
            'notifications' => $notifications
        ];
    }

    public function dashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        // Handle Review Action (Notify Guard when read)
        if (isset($_POST['review_violation'])) {
            $violationId = $_POST['violation_id'];
            $guardId = $_POST['guard_id'];
            $studentName = $_POST['student_name'];
            $sanction = $_POST['sanction'] ?? '';
            
            // Update status and sanction
            $stmt = $this->db->prepare("UPDATE violations SET status = 'in_progress', sanction = ? WHERE id = ?");
            $stmt->bind_param("si", $sanction, $violationId);
            $stmt->execute();
            
            // Notify the guard
            $msg = "OSAS has reviewed the violation for $studentName and it is now being processed.";
            $this->userModel->createNotification($guardId, $msg, $violationId);
            
            // Notify the student
            $studentUserId = $_POST['student_user_id'];
            $notifMsg = "OSAS has assigned a sanction for your violation: " . ($sanction ? $sanction : "Under review") . ". Click to view your records.";
            $this->userModel->createNotification($studentUserId, $notifMsg, $violationId);
            
            // Redirect to records to see the change
            header("Location: index.php?url=osas/records&violation_id=" . $violationId);
            exit();
        } elseif (isset($_POST['receive_violation_dash'])) {
            $violationId = $_POST['violation_id'];
            $guardId = $_POST['guard_id'];
            $studentName = $_POST['student_name'];
            
            // Fetch student info for duplicate check
            $stmt = $this->db->prepare("SELECT student_user_id, violation_type, description FROM violations WHERE id = ?");
            $stmt->bind_param("i", $violationId);
            $stmt->execute();
            $vData = $stmt->get_result()->fetch_assoc();
            $studentUserId = $vData['student_user_id'];
            $vType = $vData['violation_type'];
            $vDesc = $vData['description'];

            // Update status
            $stmt = $this->db->prepare("UPDATE violations SET status = 'received' WHERE id = ?");
            $stmt->bind_param("i", $violationId);
            $stmt->execute();

            // Also update any IDENTICAL pending violations
            $stmt2 = $this->db->prepare("UPDATE violations SET status = 'received' 
                                       WHERE student_user_id = ? 
                                       AND violation_type = ? 
                                       AND description = ? 
                                       AND status = 'pending' 
                                       AND id != ?");
            $stmt2->bind_param("issi", $studentUserId, $vType, $vDesc, $violationId);
            $stmt2->execute();
            
            // Notify the guard
            $msg = "OSAS has officially received the violation you submitted for $studentName.";
            $this->userModel->createNotification($guardId, $msg, $violationId);
            
            // Redirect to records to see the change
            header("Location: index.php?url=osas/records&violation_id=" . $violationId);
            exit();
        }

        $this->runEscalationCheck();
        $stats = $this->getStats();
        $recentViolations = $this->getRecentViolations(10);
        
        // Monthly Data for Charts
        $monthlyData = $this->getMonthlyViolationData();
        $categoryData = $this->getCategoryData();
        $courseData = $this->getCourseViolationData();
        $yearLevelData = $this->getYearLevelViolationData();

        $navData = $this->getNavData($_SESSION['user_id']);

        echo $this->render_view('osas/osas_dashboard', array_merge($navData, [
            'stats' => $stats,
            'recentViolations' => $recentViolations,
            'monthlyData' => $monthlyData,
            'categoryData' => $categoryData,
            'courseData' => $courseData,
            'yearLevelData' => $yearLevelData,
            'active' => 'home'
        ]));
    }

    private function getMonthlyViolationData() {
        $months = [];
        $currentYearCounts = [];
        $previousYearCounts = [];
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;
        
        for ($m = 1; $m <= 12; $m++) {
            $date = DateTime::createFromFormat('!m', $m);
            $monthName = $date->format('M');
            $months[] = $monthName;
            
            // Current Year Data
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM violations WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
            $stmt->bind_param("ii", $m, $currentYear);
            $stmt->execute();
            $currentYearCounts[] = $stmt->get_result()->fetch_row()[0];

            // Previous Year Data
            $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM violations WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
            $stmt2->bind_param("ii", $m, $previousYear);
            $stmt2->execute();
            $previousYearCounts[] = $stmt2->get_result()->fetch_row()[0];
        }
        
        return [
            'months' => $months, 
            'current' => $currentYearCounts, 
            'previous' => $previousYearCounts,
            'currentYear' => $currentYear,
            'previousYear' => $previousYear
        ];
    }

    private function getCategoryData() {
        $major = $this->db->query("SELECT COUNT(*) FROM violations WHERE violation_type = 'Major'")->fetch_row()[0];
        $minor = $this->db->query("SELECT COUNT(*) FROM violations WHERE violation_type = 'Minor'")->fetch_row()[0];
        return ['Major' => $major, 'Minor' => $minor];
    }

    private function getCourseViolationData() {
        $sql = "SELECT s.course, COUNT(*) as count 
                FROM violations v 
                JOIN students s ON v.student_user_id = s.user_id 
                GROUP BY s.course 
                ORDER BY count DESC 
                LIMIT 5";
        $result = $this->db->query($sql);
        $courses = [];
        $counts = [];
        while($row = $result->fetch_assoc()) {
            $courses[] = $row['course'];
            $counts[] = $row['count'];
        }
        return ['courses' => $courses, 'counts' => $counts];
    }

    private function getYearLevelViolationData() {
        $sql = "SELECT s.year_level, COUNT(*) as count 
                FROM violations v 
                JOIN students s ON v.student_user_id = s.user_id 
                GROUP BY s.year_level 
                ORDER BY s.year_level ASC";
        $result = $this->db->query($sql);
        $levels = [];
        $counts = [];
        while($row = $result->fetch_assoc()) {
            $levels[] = $row['year_level'];
            $counts[] = $row['count'];
        }
        return ['levels' => $levels, 'counts' => $counts];
    }

    public function mark_read() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') return;
        $role = $_SESSION['role'] ?? null;
        $this->userModel->markNotificationsAsRead($_SESSION['user_id'], $role);
        header("Location: index.php?url=osas/records");
    }

    public function records() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $message = "";

        // Handle mark as read from URL parameter
        if (isset($_GET['mark_read'])) {
            $notifId = $_GET['mark_read'];
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $notifId, $userId);
            $stmt->execute();
        }

        // Handle Administrative Actions
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['receive_violation'])) {
                $violationId = $_POST['violation_id'];
                $studentName = $_POST['student_name'];
                $guardId = $_POST['guard_id'] ?? null;
                
                if (!$guardId) {
                    // Fetch the guard ID and other info if not provided
                    $stmt = $this->db->prepare("SELECT guard_user_id, student_user_id, violation_type, description FROM violations WHERE id = ?");
                    $stmt->bind_param("i", $violationId);
                    $stmt->execute();
                    $vData = $stmt->get_result()->fetch_assoc();
                    $guardId = $vData['guard_user_id'];
                    $studentUserId = $vData['student_user_id'];
                    $vType = $vData['violation_type'];
                    $vDesc = $vData['description'];
                } else {
                    // Still need student info for duplicate check
                    $stmt = $this->db->prepare("SELECT student_user_id, violation_type, description FROM violations WHERE id = ?");
                    $stmt->bind_param("i", $violationId);
                    $stmt->execute();
                    $vData = $stmt->get_result()->fetch_assoc();
                    $studentUserId = $vData['student_user_id'];
                    $vType = $vData['violation_type'];
                    $vDesc = $vData['description'];
                }

                // Update status
                $stmt = $this->db->prepare("UPDATE violations SET status = 'received' WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                if ($stmt->execute()) {
                    // Also update any IDENTICAL pending violations
                    $stmt2 = $this->db->prepare("UPDATE violations SET status = 'received' 
                                               WHERE student_user_id = ? 
                                               AND violation_type = ? 
                                               AND description = ? 
                                               AND status = 'pending' 
                                               AND id != ?");
                    $stmt2->bind_param("issi", $studentUserId, $vType, $vDesc, $violationId);
                    $stmt2->execute();

                    $message = "Violation for $studentName officially received.";
                    // Notify the guard
                    $this->userModel->createNotification($guardId, "OSAS has officially received the violation you submitted for $studentName.", $violationId);
                    // Notify the student
                    $this->userModel->createNotification($studentUserId, "OSAS has received your violation and is reviewing it. You will be notified when a sanction is assigned.", $violationId);
                }
            } elseif (isset($_POST['update_violation'])) {
                $violationId = $_POST['violation_id'];
                $status = $_POST['status'];
                $sanction = $_POST['sanction'];
                $description = $_POST['description'] ?? null;
                $studentUserId = $_POST['student_user_id'];
                
                // If status is empty but we have a sanction, default to 'in_progress'
                if (empty($status) && !empty($sanction)) {
                    $status = 'in_progress';
                }
                // If status is still empty, get current status from database
                if (empty($status)) {
                    $stmtCheck = $this->db->prepare("SELECT status FROM violations WHERE id = ?");
                    $stmtCheck->bind_param("i", $violationId);
                    $stmtCheck->execute();
                    $result = $stmtCheck->get_result();
                    if ($result->num_rows > 0) {
                        $status = $result->fetch_assoc()['status'];
                    } else {
                        $status = 'in_progress';
                    }
                }
                
                if ($description !== null) {
                    $stmt = $this->db->prepare("UPDATE violations SET status = ?, sanction = ?, description = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $status, $sanction, $description, $violationId);
                } else {
                    $stmt = $this->db->prepare("UPDATE violations SET status = ?, sanction = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $status, $sanction, $violationId);
                }

                if ($stmt->execute()) {
                    // If marked as completed, also check for identical violations
                    if ($status === 'completed') {
                        // Fetch current violation details to find duplicates
                        $stmtDetails = $this->db->prepare("SELECT violation_type, description FROM violations WHERE id = ?");
                        $stmtDetails->bind_param("i", $violationId);
                        $stmtDetails->execute();
                        $vData = $stmtDetails->get_result()->fetch_assoc();
                        $vType = $vData['violation_type'];
                        $vDesc = $vData['description'];

                        $stmt2 = $this->db->prepare("UPDATE violations SET status = 'completed' 
                                                   WHERE student_user_id = ? 
                                                   AND violation_type = ? 
                                                   AND description = ? 
                                                   AND status != 'completed' 
                                                   AND id != ?");
                        $stmt2->bind_param("issi", $studentUserId, $vType, $vDesc, $violationId);
                        $stmt2->execute();
                    }

                    $message = "Violation record updated successfully.";
                    // Notify the student about the update
                    $notifMsg = "Your violation has been updated. ";
                    if (!empty($sanction)) {
                        $notifMsg .= "Sanction: " . $sanction . ". ";
                    }
                    $notifMsg .= "Status: " . ucfirst($status) . ". Click to view your record.";
                    $this->userModel->createNotification($studentUserId, $notifMsg, $violationId);
                }
            } elseif (isset($_POST['complete_sanction'])) {
                $violationId = $_POST['violation_id'];
                $studentUserId = $_POST['student_user_id'] ?? null;
                $studentName = $_POST['student_name'] ?? 'Student';

                if (!$studentUserId) {
                    $stmt = $this->db->prepare("SELECT student_user_id, violation_type, description FROM violations WHERE id = ?");
                    $stmt->bind_param("i", $violationId);
                    $stmt->execute();
                    $vData = $stmt->get_result()->fetch_assoc();
                    $studentUserId = $vData['student_user_id'];
                    $vType = $vData['violation_type'];
                    $vDesc = $vData['description'];
                } else {
                    // Fetch type and desc if we only have studentUserId
                    $stmt = $this->db->prepare("SELECT violation_type, description FROM violations WHERE id = ?");
                    $stmt->bind_param("i", $violationId);
                    $stmt->execute();
                    $vData = $stmt->get_result()->fetch_assoc();
                    $vType = $vData['violation_type'];
                    $vDesc = $vData['description'];
                }

                // Update the main violation
                $stmt = $this->db->prepare("UPDATE violations SET status = 'completed' WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                
                if ($stmt->execute()) {
                    // Also check for and complete any IDENTICAL violations for this student that are still active
                    // This handles the "double entry" issue where the same violation was recorded twice
                    $stmt2 = $this->db->prepare("UPDATE violations SET status = 'completed' 
                                               WHERE student_user_id = ? 
                                               AND violation_type = ? 
                                               AND description = ? 
                                               AND status != 'completed' 
                                               AND id != ?");
                    $stmt2->bind_param("issi", $studentUserId, $vType, $vDesc, $violationId);
                    $stmt2->execute();
                    $mergedCount = $this->db->affected_rows;

                    $message = "Violation sanction marked as completed for $studentName.";
                    if ($mergedCount > 0) {
                        $message .= " (Also cleared $mergedCount duplicate record" . ($mergedCount > 1 ? "s" : "") . ")";
                    }

                    // Notify student
                    $notifMsg = "Congratulations! Your sanction has been marked as completed by OSAS. This record has been moved to your history.";
                    $this->userModel->createNotification($studentUserId, $notifMsg, $violationId);
                }
            } elseif (isset($_POST['delete_violation'])) {
                $violationId = $_POST['violation_id'];
                $stmt = $this->db->prepare("DELETE FROM violations WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                if ($stmt->execute()) {
                    $message = "Violation record permanently deleted.";
                }
            } elseif (isset($_POST['restore_violation'])) {
                $violationId = $_POST['violation_id'];
                $stmt = $this->db->prepare("UPDATE violations SET status = 'in_progress' WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                if ($stmt->execute()) {
                    $message = "Violation restored to Active Violations.";
                }
            } elseif (isset($_POST['delete_violation_permanent'])) {
                $violationId = $_POST['violation_id'];
                $stmt = $this->db->prepare("DELETE FROM violations WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                if ($stmt->execute()) {
                    $message = "Violation record permanently deleted from database.";
                }
            }
        }

        // Fetch all active/uncompleted violations
        $activeViolations = $this->db->query("
            SELECT v.*, u.full_name as student_name, s.student_id_number, s.course, s.year_level, s.section, u.profile_photo, g.full_name as guard_name, v.recorded_by_guard_name
            FROM violations v 
            JOIN users u ON v.student_user_id = u.id 
            JOIN students s ON u.id = s.user_id 
            JOIN users g ON v.guard_user_id = g.id
            WHERE v.status != 'completed' 
            ORDER BY v.created_at DESC
        ");

        $completedViolations = $this->violationModel->getCompletedViolations();

        $navData = $this->getNavData($userId);

        echo $this->render_view('osas/osas_records', array_merge($navData, [
            'activeViolations' => $activeViolations,
            'completedViolations' => $completedViolations,
            'message' => $message,
            'active' => 'records'
        ]));
    }

    public function profile() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $message = "";

        // Handle Profile Updates
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['update_profile'])) {
                $username = $_POST['username'];
                $fullName = $_POST['full_name'];
                $bio = $_POST['bio'];
                if ($this->userModel->updateUserBasic($userId, $username, $fullName, $bio)) {
                    $_SESSION['username'] = $username;
                    $message = "Profile updated successfully!";
                } else {
                    $message = "Error updating profile.";
                }
            } elseif (isset($_FILES['profile_photo'])) {
                $file = $_FILES['profile_photo'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newName = "user_" . $userId . "_" . time() . "." . $ext;
                $target = "assets/img/profiles/" . $newName;
                
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    if ($this->userModel->updateProfilePhoto($userId, $newName)) {
                        $message = "Photo updated successfully!";
                    }
                }
            } elseif (isset($_POST['change_password'])) {
                $old = $_POST['old_password'];
                $new = $_POST['new_password'];
                $confirm = $_POST['confirm_password'];

                if ($new !== $confirm) {
                    $message = "Error: Passwords do not match.";
                } else {
                    $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    if (password_verify($old, $user['password'])) {
                        if ($this->userModel->updatePassword($userId, $new)) {
                            $message = "Password updated successfully!";
                        } else {
                            $message = "Error: Could not update password.";
                        }
                    } else {
                        $message = "Error: Incorrect old password.";
                    }
                }
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userData = $stmt->get_result()->fetch_assoc();
        
        $navData = $this->getNavData($userId);

        echo $this->render_view('osas/osas_profile', array_merge($navData, [
            'userData' => $userData,
            'message' => $message,
            'active' => 'profile'
        ]));
    }

    public function guards() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $message = "";

        // Handle Add/Delete Guard Names
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['add_guard'])) {
                $name = trim($_POST['guard_name']);
                if (!empty($name)) {
                    $stmt = $this->db->prepare("INSERT INTO guard_list (name) VALUES (?) ON DUPLICATE KEY UPDATE status = 'active'");
                    $stmt->bind_param("s", $name);
                    if ($stmt->execute()) {
                        $message = "Guard '$name' added successfully.";
                    }
                }
            } elseif (isset($_POST['delete_guard'])) {
                $name = $_POST['guard_name'];
                $stmt = $this->db->prepare("UPDATE guard_list SET status = 'inactive' WHERE name = ?");
                $stmt->bind_param("s", $name);
                if ($stmt->execute()) {
                    $message = "Guard '$name' removed successfully.";
                }
            } elseif (isset($_POST['edit_guard'])) {
                $id = $_POST['guard_id'];
                $newName = trim($_POST['new_guard_name']);
                if (!empty($newName)) {
                    if ($this->userModel->updateGuardListName($id, $newName)) {
                        $message = "Guard name updated successfully.";
                    } else {
                        $message = "Error updating guard name.";
                    }
                } else {
                    $message = "Guard name cannot be empty.";
                }
            }
        }

        // Fetch guards and their violation count
        $guards = $this->db->query("
            SELECT g.id, g.name, 
            (SELECT COUNT(*) FROM violations v WHERE v.recorded_by_guard_name = g.name) as report_count
            FROM guard_list g 
            WHERE g.status = 'active'
            ORDER BY g.name ASC
        ");

        $navData = $this->getNavData($userId);

        echo $this->render_view('osas/osas_guards', array_merge($navData, [
            'guards' => $guards,
            'message' => $message,
            'active' => 'guards'
        ]));
    }

    public function guard_ajax() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') return;
        $name = $_GET['name'] ?? null;
        if (!$name) return;

        // Fetch all violations reported by this specific guard name
        $stmt = $this->db->prepare("
            SELECT v.*, u.full_name as student_name, s.student_id_number, s.course, s.year_level
            FROM violations v 
            JOIN users u ON v.student_user_id = u.id 
            JOIN students s ON u.id = s.user_id 
            WHERE v.recorded_by_guard_name = ? 
            ORDER BY v.created_at DESC
        ");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $res = $stmt->get_result();
        $records = [];
        while($r = $res->fetch_assoc()) {
            $records[] = $r;
        }

        header('Content-Type: application/json');
        echo json_encode(['name' => $name, 'records' => $records]);
    }

    public function students() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        $message = "";

        // Handle Student CRUD
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['add_student'])) {
                $username = trim($_POST['username']);
                $fullName = trim($_POST['full_name']);
                $studentId = trim($_POST['student_id']);
                $course = $_POST['course'];
                $yearLevel = $_POST['year_level'];
                $section = $_POST['section'];
                $password = password_hash("student123", PASSWORD_DEFAULT); // Default password

                // Check if username or student ID exists
                if ($this->userModel->findByUsername($username)) {
                    $message = "Error: Username already exists.";
                } elseif ($this->userModel->findStudentByIdNumber($studentId)) {
                    $message = "Error: Student ID already exists.";
                } else {
                    $userId = $this->userModel->register([
                        'username' => $username,
                        'password' => $password,
                        'full_name' => $fullName,
                        'role' => 'STUDENT'
                    ]);

                    if ($userId) {
                        $this->userModel->registerStudent($userId, [
                            'student_id' => $studentId,
                            'course' => $course,
                            'year_level' => $yearLevel,
                            'section' => $section
                        ]);
                        $message = "Student '$fullName' added successfully. Default password is 'student123'.";
                    }
                }
            } elseif (isset($_POST['update_student'])) {
                $userId = $_POST['user_id'];
                $fullName = trim($_POST['full_name']);
                $studentId = trim($_POST['student_id']);
                $course = $_POST['course'];
                $yearLevel = $_POST['year_level'];
                $section = $_POST['section'];

                $stmt = $this->db->prepare("UPDATE users SET full_name = ? WHERE id = ?");
                $stmt->bind_param("si", $fullName, $userId);
                $stmt->execute();

                $stmt = $this->db->prepare("UPDATE students SET student_id_number = ?, course = ?, year_level = ?, section = ? WHERE user_id = ?");
                $stmt->bind_param("ssssi", $studentId, $course, $yearLevel, $section, $userId);
                if ($stmt->execute()) {
                    $message = "Student details updated successfully.";
                }
            } elseif (isset($_POST['delete_student'])) {
                $userId = $_POST['user_id'];
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'STUDENT'");
                $stmt->bind_param("i", $userId);
                if ($stmt->execute()) {
                    $message = "Student account deleted successfully.";
                }
            }
        }

        // Fetch all students using userModel
        $allAccounts = $this->userModel->getAllStudents();
        
        $studentsByYear = [
            '1st Year' => [],
            '2nd Year' => [],
            '3rd Year' => [],
            '4th Year' => []
        ];

        $yearMapping = [
            '1' => '1st Year', '1st' => '1st Year', '1st Year' => '1st Year',
            '2' => '2nd Year', '2nd' => '2nd Year', '2nd Year' => '2nd Year',
            '3' => '3rd Year', '3rd' => '3rd Year', '3rd Year' => '3rd Year',
            '4' => '4th Year', '4th' => '4th Year', '4th Year' => '4th Year'
        ];

        if ($allAccounts) {
            while($row = $allAccounts->fetch_assoc()) {
                if ($row['role'] === 'STUDENT') {
                    $rawYear = $row['year_level'];
                    $year = $yearMapping[$rawYear] ?? 'Others';
                    
                    if ($year !== 'Others') {
                        $studentsByYear[$year][] = $row;
                    } else {
                        if (!isset($studentsByYear['Others'])) $studentsByYear['Others'] = [];
                        $studentsByYear['Others'][] = $row;
                    }
                }
            }
        }

        $navData = $this->getNavData($_SESSION['user_id']);

        echo $this->render_view('osas/osas_students', array_merge($navData, [
            'studentsByYear' => $studentsByYear,
            'message' => $message,
            'active' => 'students'
        ]));
    }

    public function student_ajax() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') return;
        $userId = $_GET['id'] ?? null;
        if (!$userId) return;

        $student = $this->userModel->getStudentDetails($userId);
        $violations = [];
        if (isset($student['violations'])) {
            while($v = $student['violations']->fetch_assoc()) {
                $violations[] = $v;
            }
            unset($student['violations']); // Remove resource object
        }

        header('Content-Type: application/json');
        echo json_encode(['student' => $student, 'violations' => $violations]);
    }

    public function notifications() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $message = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['delete_notification'])) {
                $notifId = $_POST['notification_id'];
                $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $notifId, $userId);
                if ($stmt->execute()) {
                    $_SESSION['notification_message'] = "Notification deleted.";
                    header("Location: index.php?url=osas/notifications");
                    exit();
                }
            } elseif (isset($_POST['mark_as_read'])) {
                $notifId = $_POST['notification_id'];
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $notifId, $userId);
                if ($stmt->execute()) {
                    $_SESSION['notification_message'] = "Notification marked as read.";
                    header("Location: index.php?url=osas/notifications");
                    exit();
                }
            }
        }

        $message = $_SESSION['notification_message'] ?? '';
        unset($_SESSION['notification_message']);

        $navData = $this->getNavData($userId);
        $notifResult = $this->userModel->getNotifications($userId, 50, $_SESSION['role']);
        
        // Full list for the main view
        $allNotifications = [];
        if ($notifResult) {
            while ($row = $notifResult->fetch_assoc()) {
                $allNotifications[] = $row;
            }
        }

        echo $this->render_view('osas/osas_notifications', array_merge($navData, [
            'notifications' => $allNotifications,
            'message' => $message,
            'active' => 'notifications'
        ]));
    }

    public function getStats() {
        $stats = [
            'total_violations' => 0,
            'pending_violations' => 0,
            'major_violations' => 0,
            'minor_violations' => 0,
            'expulsions' => 0
        ];

        $res = $this->db->query("SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status NOT IN ('completed', 'dropped') THEN 1 END) as pending,
            COUNT(CASE WHEN violation_type = 'Major' THEN 1 END) as major,
            COUNT(CASE WHEN violation_type = 'Minor' THEN 1 END) as minor,
            COUNT(CASE WHEN sanction LIKE '%expulsion%' THEN 1 END) as expulsion
            FROM violations");

        if ($res && $row = $res->fetch_assoc()) {
            $stats = [
                'total_violations' => $row['total'],
                'pending_violations' => $row['pending'],
                'major_violations' => $row['major'],
                'minor_violations' => $row['minor'],
                'expulsions' => $row['expulsion']
            ];
        }
        return $stats;
    }

    public function getRecentViolations($limit = 10) {
        $stmt = $this->db->prepare("SELECT v.*, u.full_name as student_name, s.student_id_number, g.full_name as guard_name FROM violations v JOIN users u ON v.student_user_id = u.id JOIN students s ON u.id = s.user_id JOIN users g ON v.guard_user_id = g.id ORDER BY v.created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function updateViolationStatus($violationId, $status) {
        $stmt = $this->db->prepare("UPDATE violations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $violationId);
        return $stmt->execute();
    }

    public function runEscalationCheck() {
        $now = new DateTime();
        $violations = $this->db->query("SELECT * FROM violations WHERE status NOT IN ('completed', 'dropped')");
        
        while ($v = $violations->fetch_assoc()) {
            $created_at = new DateTime($v['created_at']);
            $diff = $now->diff($created_at)->days;
            $type = $v['violation_type'];
            $status = $v['status'];
            $escalation = $v['escalation_level'];

            if ($type == 'Minor') {
                if ($diff >= 2 && $status == 'pending') {
                    $this->updateViolationStatus($v['id'], 'warning_sent');
                    $this->userModel->createNotification($v['student_user_id'], "Warning: You have 2 days to act on your minor violation.", $v['id']);
                } elseif ($diff >= 3 && $status == 'warning_sent') {
                    $this->upgradeToMajor($v['id']);
                    $this->userModel->createNotification($v['student_user_id'], "Critical: Your minor violation has been upgraded to MAJOR due to inactivity.", $v['id']);
                } elseif ($diff >= 6 && $status == 'warning_sent') {
                    $this->updateViolationStatus($v['id'], 'parent_called');
                    $this->userModel->createNotification($v['student_user_id'], "Alert: Parents will be called regarding your violation.", $v['id']);
                }
            } else { // Major
                if ($diff >= 3 && $status == 'pending') {
                    $this->updateViolationStatus($v['id'], 'warning_sent');
                    $this->userModel->createNotification($v['student_user_id'], "Warning: Action required for your Major violation.", $v['id']);
                } elseif ($diff >= 6 && $status == 'warning_sent' && $escalation < 2) {
                    $this->incrementEscalation($v['id']);
                    $this->userModel->createNotification($v['student_user_id'], "Second Warning: Final notice before calling parents.", $v['id']);
                } elseif ($diff >= 9 && $status == 'warning_sent' && $escalation >= 2) {
                    $this->updateViolationStatus($v['id'], 'parent_called');
                    $this->userModel->createNotification($v['student_user_id'], "Alert: Parents will be called for this major violation.", $v['id']);
                } elseif ($diff >= 12 && $status == 'parent_called') {
                    $this->updateViolationStatus($v['id'], 'dropped');
                    $this->userModel->createNotification($v['student_user_id'], "Final Notice: Dropped from system due to non-compliance.", $v['id']);
                }
            }
        }
    }

    private function upgradeToMajor($violationId) {
        $stmt = $this->db->prepare("UPDATE violations SET violation_type = 'Major', status = 'warning_sent' WHERE id = ?");
        $stmt->bind_param("i", $violationId);
        $stmt->execute();
    }

    private function incrementEscalation($violationId) {
        $stmt = $this->db->prepare("UPDATE violations SET escalation_level = escalation_level + 1 WHERE id = ?");
        $stmt->bind_param("i", $violationId);
        $stmt->execute();
    }
}
?>