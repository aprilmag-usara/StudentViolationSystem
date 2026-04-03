<?php
require_once __DIR__ . '/../models/Violation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class StudentController extends BaseController {
    private $userModel;
    private $violationModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new User($db);
        $this->violationModel = new Violation($db);
    }

    public function dashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'STUDENT') {
            header("Location: index.php");
            exit();
        }
        $userId = $_SESSION['user_id'];
        $studentInfo = $this->getStudentInfo($userId);
        
        // If profile is missing, redirect to a setup page or show error
        if (!$studentInfo) {
            // For now, let's just provide a default array to prevent crash
            $studentInfo = [
                'full_name' => $_SESSION['username'],
                'student_id_number' => 'N/A',
                'course' => 'N/A',
                'year_level' => 'N/A',
                'section' => 'N/A'
            ];
        }

        $stats = $this->getViolationStats($userId);
        $violations = $this->violationModel->findByStudent($userId, 5);
        
        $navData = $this->getNavData($userId);

        echo $this->render_view('student/student_dashboard', array_merge($navData, [
            'studentInfo' => $studentInfo,
            'stats' => $stats,
            'violations' => $violations,
            'active' => 'home'
        ]));
    }

    public function mark_read() {
        if (!isset($_SESSION['user_id'])) return;
        $role = $_SESSION['role'] ?? null;
        $this->userModel->markNotificationsAsRead($_SESSION['user_id'], $role);
        header("Location: index.php?url=student/dashboard");
    }

    public function records() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'STUDENT') {
            header("Location: index.php");
            exit();
        }
        $userId = $_SESSION['user_id'];
        $studentInfo = $this->getStudentInfo($userId);
        $violations = $this->getStudentViolations($userId);
        
        $navData = $this->getNavData($userId);

        echo $this->render_view('student/student_records', array_merge($navData, [
            'studentInfo' => $studentInfo,
            'violations' => $violations,
            'active' => 'records'
        ]));
    }

    public function profile() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'STUDENT') {
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
                $oldPass = $_POST['old_password'];
                $newPass = $_POST['new_password'];
                $confirmPass = $_POST['confirm_password'];
                
                $user = $this->userModel->getStudentInfo($userId);
                
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

        $studentInfo = $this->getStudentInfo($userId);
        
        // Ensure bio and profile_photo exist in the array to avoid warnings
        if ($studentInfo) {
            if (!isset($studentInfo['bio'])) $studentInfo['bio'] = "";
            if (!isset($studentInfo['profile_photo'])) $studentInfo['profile_photo'] = "default_profile.png";
        }

        $stats = $this->getViolationStats($userId);
        
        $navData = $this->getNavData($userId);

        echo $this->render_view('student/student_profile', array_merge($navData, [
            'studentInfo' => $studentInfo,
            'message' => $message,
            'stats' => $stats,
            'active' => 'profile'
        ]));
    }

    private function getNavData($userId) {
        $role = $_SESSION['role'] ?? null;
        return [
            'unreadCount' => $this->userModel->getUnreadNotificationCount($userId, $role),
            'notifications' => $this->userModel->getNotifications($userId, 5, $role)
        ];
    }

    private function updateProfile($userId, $username, $bio) {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $bio, $userId);
        return $stmt->execute();
    }

    public function getStudentInfo($userId) {
        return $this->userModel->getStudentInfo($userId);
    }

    public function getStudentViolations($userId) {
        return $this->violationModel->findByStudent($userId);
    }

    public function getNotifications($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getViolationStats($userId) {
        $stmt = $this->db->prepare("SELECT 
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending
            FROM violations WHERE student_user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
