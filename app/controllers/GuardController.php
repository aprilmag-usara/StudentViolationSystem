<?php
require_once __DIR__ . '/../models/Violation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class GuardController extends BaseController {
    private $violationModel;
    private $userModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->violationModel = new Violation($db);
        $this->userModel = new User($db);
    }

    public function dashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'GUARD') {
            header("Location: index.php");
            exit();
        }
        $guardInfo = $this->userModel->getGuardInfo($_SESSION['user_id']);
        
        // Use CHMSU Security as the account name if it matches our shared account logic
        $displayName = ($guardInfo && $guardInfo['full_name'] === 'CHMSU Security') ? 'CHMSU Security' : ($_SESSION['username'] ?? 'Guard');

        $message = "";
        $studentData = null;
        $guardList = $this->userModel->getGuardList();
        
        if ($guardList === null) {
            $message = "System Note: Please update your database schema using database.sql to enable the Guard List feature.";
        }

        // Handle QR/Manual Search Submission (Both button and JS auto-submit)
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['search_student']) || isset($_POST['student_search_query']))) {
            $query = $_POST['student_search_query'] ?? '';
            if (!empty($query)) {
                $results = $this->userModel->searchStudents($query);
                if ($results->num_rows == 1) {
                    $studentData = $results->fetch_assoc();
                } elseif ($results->num_rows > 1) {
                    $message = "Multiple students found. Please select from the dropdown.";
                } else {
                    $message = "No student found with that name or ID.";
                }
            }
        }

        // Handle Violation Submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_violation'])) {
            $data = [
                'student_user_id' => $_POST['student_user_id'],
                'guard_user_id' => $_SESSION['user_id'],
                'recorded_by_guard_name' => $_POST['recorded_by_guard_name'],
                'violation_type' => $_POST['violation_type'],
                'description' => $_POST['description'],
                'violation_time' => date('Y-m-d H:i:s')
            ];

            if ($violationId = $this->violationModel->create($data)) {
                $message = "Violation submitted to OSAS successfully!";
                
                // Notify all OSAS admins
                $osasAdmins = $this->userModel->getOsasAdmins();
                $studentName = $_POST['student_name'] ?? ($_POST['student_search_query'] ?? 'a student');
                $notifMsg = "A new violation for " . $studentName . " was submitted by " . $_POST['recorded_by_guard_name'];
                while($admin = $osasAdmins->fetch_assoc()) {
                    $this->userModel->createNotification($admin['id'], $notifMsg, $violationId);
                }
                
                // Notify the student
                $studentNotifMsg = "You have a new violation report. A guard has reported you for a school violation. OSAS will review it and contact you shortly. Click to view your record.";
                $this->userModel->createNotification($_POST['student_user_id'], $studentNotifMsg, $violationId);
            } else {
                $message = "Error submitting violation.";
            }
        }

        $navData = $this->getNavData($_SESSION['user_id']);

        echo $this->render_view('guard/guard_dashboard', array_merge($navData, [
            'guardInfo' => $guardInfo,
            'displayName' => $displayName,
            'message' => $message,
            'studentData' => $studentData,
            'guardList' => $guardList,
            'active' => 'home'
        ]));
    }

    public function profile() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'GUARD') {
            header("Location: index.php");
            exit();
        }
        
        $userId = $_SESSION['user_id'];
        $message = "";
        
        // Handle Profile Updates
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['update_profile'])) {
                $data = [
                    'username' => $_POST['username'],
                    'full_name' => $_POST['full_name'],
                    'bio' => $_POST['bio'] ?? ''
                ];
                if ($this->userModel->updateUserBasic($userId, $data['username'], $data['full_name'], $data['bio'])) {
                    $_SESSION['username'] = $data['username'];
                    $message = "Profile updated successfully!";
                } else {
                    $message = "Error updating profile.";
                }
            } elseif (isset($_FILES['profile_photo'])) {
                $file = $_FILES['profile_photo'];
                $ext = pathinfo($file['name'], PATHINFO_EXT);
                $newName = "user_" . $userId . "_" . time() . "." . $ext;
                $target = "assets/img/profiles/" . $newName;
                
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    if ($this->userModel->updateProfilePhoto($userId, $newName)) {
                        $message = "Photo updated successfully!";
                    }
                }
            } elseif (isset($_POST['change_password'])) {
                $oldPass = $_POST['old_password'];
                $newPass = $_POST['new_password'];
                $confirmPass = $_POST['confirm_password'];
                
                $user = $this->userModel->findByUsername($_SESSION['username']);
                
                if (!password_verify($oldPass, $user['password'])) {
                    $message = "Incorrect password";
                } elseif ($newPass !== $confirmPass) {
                    $message = "New passwords do not match.";
                } else {
                    if ($this->userModel->updatePassword($userId, $newPass)) {
                        $message = "Password changed successfully!";
                    } else {
                        $message = "Error updating password.";
                    }
                }
            }
        }

        $guardInfo = $this->userModel->getGuardInfo($userId);
        $guardsList = $this->userModel->getGuardsWithCounts();
        
        // Get all violations organized by guard name
        $violationsByGuard = [];
        $allViolations = $this->violationModel->findByGuard($_SESSION['user_id']);
        if ($allViolations) {
            while($v = $allViolations->fetch_assoc()) {
                $guardName = $v['recorded_by_guard_name'] ?? 'Unknown';
                if (!isset($violationsByGuard[$guardName])) {
                    $violationsByGuard[$guardName] = [];
                }
                $violationsByGuard[$guardName][] = $v;
            }
        }
        
        // Ensure bio and profile_photo exist
        $userBase = $this->userModel->findByUsername($_SESSION['username']);
        if ($guardInfo) {
            $guardInfo['username'] = $userBase['username'];
            $guardInfo['bio'] = $userBase['bio'] ?? "";
            $guardInfo['profile_photo'] = $userBase['profile_photo'] ?? "default_profile.png";
        } else {
            // Fallback if guardInfo is null
            $guardInfo = [
                'full_name' => $userBase['full_name'],
                'username' => $userBase['username'],
                'bio' => $userBase['bio'] ?? "",
                'profile_photo' => $userBase['profile_photo'] ?? "default_profile.png",
                'id' => $userId
            ];
        }

        $navData = $this->getNavData($userId);

        echo $this->render_view('guard/guard_profile', array_merge($navData, [
            'guardInfo' => $guardInfo,
            'guardsList' => $guardsList,
            'violationsByGuard' => $violationsByGuard,
            'message' => $message,
            'active' => 'profile'
        ]));
    }

    public function records() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'GUARD') {
            header("Location: index.php");
            exit();
        }
        $userId = $_SESSION['user_id'];
        
        // Handle mark as read from URL parameter
        if (isset($_GET['mark_read'])) {
            $notifId = $_GET['mark_read'];
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $notifId, $userId);
            $stmt->execute();
        }
        
        $guardInfo = $this->userModel->getGuardInfo($_SESSION['user_id']);
        $activeViolations = $this->violationModel->findActiveByGuard($_SESSION['user_id']);
        $completedViolations = $this->violationModel->findCompletedByGuard($_SESSION['user_id']);
        
        $displayName = ($guardInfo && $guardInfo['full_name'] === 'CHMSU Security') ? 'CHMSU Security' : ($_SESSION['username'] ?? 'Guard');

        $navData = $this->getNavData($_SESSION['user_id']);

        echo $this->render_view('guard/guard_records', array_merge($navData, [
            'guardInfo' => $guardInfo,
            'displayName' => $displayName,
            'activeViolations' => $activeViolations,
            'completedViolations' => $completedViolations,
            'active' => 'records'
        ]));
    }

    public function notifications() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'GUARD') {
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
                    header("Location: index.php?url=guard/notifications");
                    exit();
                }
            }
        }

        $message = $_SESSION['notification_message'] ?? '';
        unset($_SESSION['notification_message']);

        $navData = $this->getNavData($userId);
        $notifResult = $this->userModel->getNotifications($userId, 50, $_SESSION['role']);
        
        $allNotifications = [];
        if ($notifResult) {
            while ($row = $notifResult->fetch_assoc()) {
                $allNotifications[] = $row;
            }
        }

        echo $this->render_view('guard/guard_notifications', array_merge($navData, [
            'notifications' => $allNotifications,
            'message' => $message,
            'active' => 'notifications'
        ]));
    }

    public function mark_read() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'GUARD') return;
        $role = $_SESSION['role'] ?? null;
        $this->userModel->markNotificationsAsRead($_SESSION['user_id'], $role);
        header("Location: index.php?url=guard/notifications");
    }

    private function getNavData($userId) {
        $role = $_SESSION['role'] ?? null;
        return [
            'unreadCount' => $this->userModel->getUnreadNotificationCount($userId, $role),
            'notifications' => $this->userModel->getNotifications($userId, 5, $role)
        ];
    }

    // Helper for AJAX search if we want it more dynamic
    public function search_ajax() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'GUARD') return;
        $query = $_GET['query'] ?? '';
        $results = $this->userModel->searchStudents($query);
        $data = [];
        while($row = $results->fetch_assoc()) {
            $data[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
?>