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
                $notifMsg = "A new violation for " . $_POST['student_search_query'] . " was submitted by " . $_POST['recorded_by_guard_name'];
                while($admin = $osasAdmins->fetch_assoc()) {
                    $this->userModel->createNotification($admin['id'], $notifMsg, $violationId);
                }
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

    public function mark_read() {
        if (!isset($_SESSION['user_id'])) return;
        $role = $_SESSION['role'] ?? null;
        $this->userModel->markNotificationsAsRead($_SESSION['user_id'], $role);
        header("Location: index.php?url=guard/dashboard");
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

        $guardInfo = $this->userModel->getGuardInfo($userId);
        $guardsList = $this->userModel->getGuardsWithCounts();
        
        // Ensure bio and profile_photo exist
        $userBase = $this->userModel->findByUsername($_SESSION['username']);
        $guardInfo['username'] = $userBase['username'];
        $guardInfo['bio'] = $userBase['bio'] ?? "";
        $guardInfo['profile_photo'] = $userBase['profile_photo'] ?? "default_profile.png";

        $navData = $this->getNavData($userId);

        echo $this->render_view('guard/guard_profile', array_merge($navData, [
            'guardInfo' => $guardInfo,
            'guardsList' => $guardsList,
            'message' => $message,
            'active' => 'profile'
        ]));
    }

    public function records() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'GUARD') {
            header("Location: index.php");
            exit();
        }
        $guardInfo = $this->userModel->getGuardInfo($_SESSION['user_id']);
        $violations = $this->violationModel->findByGuard($_SESSION['user_id']);
        
        $displayName = ($guardInfo && $guardInfo['full_name'] === 'CHMSU Security') ? 'CHMSU Security' : ($_SESSION['username'] ?? 'Guard');

        $navData = $this->getNavData($_SESSION['user_id']);

        echo $this->render_view('guard/guard_records', array_merge($navData, [
            'guardInfo' => $guardInfo,
            'displayName' => $displayName,
            'violations' => $violations,
            'active' => 'records'
        ]));
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
