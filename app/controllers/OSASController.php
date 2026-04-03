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
        } elseif (isset($_POST['receive_violation_dash'])) {
            $violationId = $_POST['violation_id'];
            $guardId = $_POST['guard_id'];
            $studentName = $_POST['student_name'];
            
            // Update status
            $stmt = $this->db->prepare("UPDATE violations SET status = 'received' WHERE id = ?");
            $stmt->bind_param("i", $violationId);
            $stmt->execute();
            
            // Notify the guard
            $msg = "OSAS has officially received the violation you submitted for $studentName.";
            $this->userModel->createNotification($guardId, $msg, $violationId);
        }

        $this->runEscalationCheck();
        $stats = $this->getStats();
        $recentViolations = $this->getRecentViolations(10);
        
        // Monthly Data for Charts
        $monthlyData = $this->getMonthlyViolationData();
        $categoryData = $this->getCategoryData();
        $courseData = $this->getCourseViolationData();
        $yearLevelData = $this->getYearLevelViolationData();

        $unreadCount = $this->userModel->getUnreadNotificationCount($_SESSION['user_id'], $_SESSION['role']);
        $notifications = $this->userModel->getNotifications($_SESSION['user_id'], 5, $_SESSION['role']);

        echo $this->render_view('osas/osas_dashboard', [
            'stats' => $stats,
            'recentViolations' => $recentViolations,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
            'monthlyData' => $monthlyData,
            'categoryData' => $categoryData,
            'courseData' => $courseData,
            'yearLevelData' => $yearLevelData
        ]);
    }

    private function getMonthlyViolationData() {
        $months = [];
        $counts = [];
        $year = date('Y');
        
        for ($m = 1; $m <= 12; $m++) {
            $date = DateTime::createFromFormat('!m', $m);
            $monthName = $date->format('M');
            $months[] = $monthName;
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM violations WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
            $stmt->bind_param("ii", $m, $year);
            $stmt->execute();
            $counts[] = $stmt->get_result()->fetch_row()[0];
        }
        
        return ['months' => $months, 'counts' => $counts];
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
        header("Location: index.php?url=osas/dashboard");
    }

    public function records() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $message = "";

        // Handle Administrative Actions
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['receive_violation'])) {
                $violationId = $_POST['violation_id'];
                $studentName = $_POST['student_name'];
                
                // Fetch the guard ID for notification
                $stmt = $this->db->prepare("SELECT guard_user_id FROM violations WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                $stmt->execute();
                $guardId = $stmt->get_result()->fetch_row()[0];

                // Update status
                $stmt = $this->db->prepare("UPDATE violations SET status = 'received' WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                if ($stmt->execute()) {
                    $message = "Violation for $studentName officially received.";
                    // Notify the guard
                    $this->userModel->createNotification($guardId, "OSAS has officially received the violation you submitted for $studentName.", $violationId);
                }
            } elseif (isset($_POST['update_violation'])) {
                $violationId = $_POST['violation_id'];
                $status = $_POST['status'];
                $sanction = $_POST['sanction'];
                $studentUserId = $_POST['student_user_id'];
                
                $stmt = $this->db->prepare("UPDATE violations SET status = ?, sanction = ? WHERE id = ?");
                $stmt->bind_param("ssi", $status, $sanction, $violationId);
                if ($stmt->execute()) {
                    $message = "Violation record updated successfully.";
                    // Notify the student
                    $notifMsg = "Your violation record has been updated by OSAS. Status: " . ucfirst($status) . ". Sanction: " . ($sanction ?: "Pending");
                    $this->userModel->createNotification($studentUserId, $notifMsg, $violationId);
                }
            } elseif (isset($_POST['complete_sanction'])) {
                $violationId = $_POST['violation_id'];
                $stmt = $this->db->prepare("UPDATE violations SET status = 'completed' WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                if ($stmt->execute()) {
                    $message = "Violation sanction marked as completed.";
                }
            } elseif (isset($_POST['delete_violation'])) {
                $violationId = $_POST['violation_id'];
                $stmt = $this->db->prepare("DELETE FROM violations WHERE id = ?");
                $stmt->bind_param("i", $violationId);
                if ($stmt->execute()) {
                    $message = "Violation record permanently deleted.";
                }
            }
        }

        // Fetch all active/uncompleted violations
        $activeViolations = $this->db->query("
            SELECT v.*, u.full_name as student_name, s.student_id_number, s.course, s.year_level, s.section, u.profile_photo
            FROM violations v 
            JOIN users u ON v.student_user_id = u.id 
            JOIN students s ON u.id = s.user_id 
            WHERE v.status != 'completed' 
            ORDER BY v.created_at DESC
        ");

        // Fetch history (completed)
        $completedViolations = $this->db->query("
            SELECT v.*, v.last_action_date as updated_at, u.full_name as student_name, s.student_id_number, s.course, s.year_level, s.section
            FROM violations v 
            JOIN users u ON v.student_user_id = u.id 
            JOIN students s ON u.id = s.user_id 
            WHERE v.status = 'completed' 
            ORDER BY v.created_at DESC
        ");

        $unreadCount = $this->userModel->getUnreadNotificationCount($userId, $_SESSION['role']);
        $notifications = $this->userModel->getNotifications($userId, 5, $_SESSION['role']);

        echo $this->render_view('osas/osas_records', [
            'activeViolations' => $activeViolations,
            'completedViolations' => $completedViolations,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
            'message' => $message
        ]);
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
                $bio = $_POST['bio'];
                if ($this->userModel->updateProfile($userId, $username, $bio)) {
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
                    $user = $this->userModel->getStudentInfo($userId); // Works for any user
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

        $userData = $this->db->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
        $unreadCount = $this->userModel->getUnreadNotificationCount($userId, $_SESSION['role']);
        $notifications = $this->userModel->getNotifications($userId, 5, $_SESSION['role']);

        echo $this->render_view('osas/osas_profile', [
            'userData' => $userData,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
            'message' => $message
        ]);
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

        $unreadCount = $this->userModel->getUnreadNotificationCount($userId, $_SESSION['role']);
        $notifications = $this->userModel->getNotifications($userId, 5, $_SESSION['role']);

        echo $this->render_view('osas/osas_guards', [
            'guards' => $guards,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
            'message' => $message
        ]);
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

        $allAccounts = $this->userModel->getAllStudents();
        
        // Group by year level - Only for students
        $studentsByYear = [
            '1st Year' => [],
            '2nd Year' => [],
            '3rd Year' => [],
            '4th Year' => []
        ];

        while($row = $allAccounts->fetch_assoc()) {
            if ($row['role'] === 'STUDENT') {
                $year = $row['year_level'];
                if (array_key_exists($year, $studentsByYear)) {
                    $studentsByYear[$year][] = $row;
                } else {
                    if (!isset($studentsByYear['Others'])) $studentsByYear['Others'] = [];
                    $studentsByYear['Others'][] = $row;
                }
            }
        }

        $unreadCount = $this->userModel->getUnreadNotificationCount($_SESSION['user_id'], $_SESSION['role']);
        $notifications = $this->userModel->getNotifications($_SESSION['user_id'], 5, $_SESSION['role']);

        echo $this->render_view('osas/osas_students', [
            'studentsByYear' => $studentsByYear,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications
        ]);
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
                    $message = "Notification deleted.";
                }
            } elseif (isset($_POST['mark_as_read'])) {
                $notifId = $_POST['notification_id'];
                $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $notifId, $userId);
                $stmt->execute();
            }
        }

        $unreadCount = $this->userModel->getUnreadNotificationCount($userId, $_SESSION['role']);
        $allNotifications = $this->userModel->getNotifications($userId, 50, $_SESSION['role']); // Get more for the full page

        echo $this->render_view('osas/osas_notifications', [
            'notifications' => $allNotifications,
            'unreadCount' => $unreadCount,
            'message' => $message
        ]);
    }

    public function violations() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'OSAS') {
            header("Location: index.php");
            exit();
        }

        $message = "";
        
        // Handle CRUD Operations
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['update_violation'])) {
                $vId = $_POST['violation_id'];
                $status = $_POST['status'];
                $sanction = $_POST['sanction'];
                $desc = $_POST['description'];
                
                $sql = "UPDATE violations SET status = ?, sanction = ?, description = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("sssi", $status, $sanction, $desc, $vId);
                
                if ($stmt->execute()) {
                    $message = "Violation updated successfully!";
                    
                    // Notify student of update
                    $studentId = $_POST['student_user_id'];
                    $notifMsg = "Your violation record has been updated by OSAS. Status: " . ucfirst($status) . ". Sanction: " . ($sanction ?: 'None');
                    $this->userModel->createNotification($studentId, $notifMsg);
                }
            } elseif (isset($_POST['receive_violation'])) {
                $vId = $_POST['violation_id'];
                $guardId = $_POST['guard_id'];
                $studentName = $_POST['student_name'];
                
                $sql = "UPDATE violations SET status = 'received' WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $vId);
                
                if ($stmt->execute()) {
                    $message = "Violation for $studentName marked as received!";
                    
                    // Notify Guard
                    $notifMsg = "OSAS has officially received the violation you submitted for $studentName.";
                    $this->userModel->createNotification($guardId, $notifMsg);
                }
            } elseif (isset($_POST['complete_sanction'])) {
                $vId = $_POST['violation_id'];
                $studentId = $_POST['student_user_id'];
                $studentName = $_POST['student_name'];
                
                $sql = "UPDATE violations SET status = 'completed' WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $vId);
                
                if ($stmt->execute()) {
                    $message = "Sanction marked as completed for $studentName!";
                    
                    // Notify student
                    $notifMsg = "Congratulations! Your sanction has been marked as completed by OSAS. This record has been moved to your history.";
                    $this->userModel->createNotification($studentId, $notifMsg);
                }
            } elseif (isset($_POST['delete_violation'])) {
                $vId = $_POST['violation_id'];
                $stmt = $this->db->prepare("DELETE FROM violations WHERE id = ?");
                $stmt->bind_param("i", $vId);
                if ($stmt->execute()) {
                    $message = "Violation deleted successfully.";
                }
            }
        }

        // Fetch Active Violations (Status NOT 'completed')
        $activeViolations = $this->db->query("SELECT v.*, u.full_name as student_name, u.profile_photo, s.student_id_number, s.course, s.year_level, s.section, g.full_name as guard_name 
                                      FROM violations v 
                                      JOIN users u ON v.student_user_id = u.id 
                                      JOIN students s ON u.id = s.user_id 
                                      JOIN users g ON v.guard_user_id = g.id 
                                      WHERE v.status != 'completed'
                                      ORDER BY v.created_at DESC");

        // Fetch Completed Violations (Status 'completed')
        $completedViolations = $this->db->query("SELECT v.*, u.full_name as student_name, u.profile_photo, s.student_id_number, s.course, s.year_level, s.section, g.full_name as guard_name 
                                      FROM violations v 
                                      JOIN users u ON v.student_user_id = u.id 
                                      JOIN students s ON u.id = s.user_id 
                                      JOIN users g ON v.guard_user_id = g.id 
                                      WHERE v.status = 'completed'
                                      ORDER BY v.created_at DESC");

        $unreadCount = $this->userModel->getUnreadNotificationCount($_SESSION['user_id'], $_SESSION['role']);
        $notifications = $this->userModel->getNotifications($_SESSION['user_id'], 5, $_SESSION['role']);

        echo $this->render_view('osas/osas_records', [
            'activeViolations' => $activeViolations,
            'completedViolations' => $completedViolations,
            'message' => $message,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    public function getStats() {
        $stats = [];
        $stats['total_violations'] = $this->db->query("SELECT COUNT(*) FROM violations")->fetch_row()[0];
        $stats['pending_violations'] = $this->db->query("SELECT COUNT(*) FROM violations WHERE status = 'pending'")->fetch_row()[0];
        $stats['major_violations'] = $this->db->query("SELECT COUNT(*) FROM violations WHERE violation_type = 'Major'")->fetch_row()[0];
        $stats['minor_violations'] = $this->db->query("SELECT COUNT(*) FROM violations WHERE violation_type = 'Minor'")->fetch_row()[0];
        $stats['expulsions'] = $this->db->query("SELECT COUNT(*) FROM violations WHERE sanction LIKE '%expulsion%'")->fetch_row()[0];
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
                    $this->createNotification($v['student_user_id'], "Warning: You have 2 days to act on your minor violation.");
                } elseif ($diff >= 3 && $status == 'warning_sent') {
                    $this->upgradeToMajor($v['id']);
                    $this->createNotification($v['student_user_id'], "Critical: Your minor violation has been upgraded to MAJOR due to inactivity.");
                } elseif ($diff >= 6 && $status == 'warning_sent') {
                    $this->updateViolationStatus($v['id'], 'parent_called');
                    $this->createNotification($v['student_user_id'], "Alert: Parents will be called regarding your violation.");
                }
            } else { // Major
                if ($diff >= 3 && $status == 'pending') {
                    $this->updateViolationStatus($v['id'], 'warning_sent');
                    $this->createNotification($v['student_user_id'], "Warning: Action required for your Major violation.");
                } elseif ($diff >= 6 && $status == 'warning_sent' && $escalation < 2) {
                    $this->incrementEscalation($v['id']);
                    $this->createNotification($v['student_user_id'], "Second Warning: Final notice before calling parents.");
                } elseif ($diff >= 9 && $status == 'warning_sent' && $escalation >= 2) {
                    $this->updateViolationStatus($v['id'], 'parent_called');
                    $this->createNotification($v['student_user_id'], "Alert: Parents will be called for this major violation.");
                } elseif ($diff >= 12 && $status == 'parent_called') {
                    $this->updateViolationStatus($v['id'], 'dropped');
                    $this->createNotification($v['student_user_id'], "Final Notice: Dropped from system due to non-compliance.");
                }
            }
        }
    }

    private function createNotification($userId, $message) {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $message);
        $stmt->execute();
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
