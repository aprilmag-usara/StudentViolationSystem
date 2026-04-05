<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController {
    private $userModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new User($db);
    }

    public function login() {
        $message = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
            $user = $this->userModel->findByUsername($_POST['username']);
            if ($user && password_verify($_POST['password'], $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] == 'STUDENT') header("Location: index.php?url=student/dashboard");
                elseif ($user['role'] == 'GUARD') header("Location: index.php?url=guard/dashboard");
                elseif ($user['role'] == 'OSAS') header("Location: index.php?url=osas/dashboard");
                exit();
            } else {
                $message = "Invalid username or password.";
            }
        }
        echo $this->render_view('login', ['message' => $message]);
    }


    public function signup() {
        $message = "";
        $formData = [];
        $success = false;
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
            $formData = $_POST;
            $role = $_POST['role'];
            $authorized = true;

            // Security check for Guard and OSAS roles
            if ($role == 'GUARD' || $role == 'OSAS') {
                $correctPasscode = $this->userModel->getAuthPasscode($role);
                
                if (empty($_POST['auth_pass'])) {
                    $authorized = false;
                    $message = "Please enter the " . ($role == 'GUARD' ? 'Guard' : 'OSAS') . " Authorization Passcode.";
                } elseif ($_POST['auth_pass'] != $correctPasscode) {
                    $authorized = false;
                    $message = "Invalid " . ($role == 'GUARD' ? 'Guard' : 'OSAS') . " Authorization Passcode.";
                }
            }

            if ($authorized) {
                $data = [
                    'username' => $_POST['username'],
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'full_name' => $_POST['full_name'],
                    'role' => $role,
                    'student_id' => $_POST['student_id'],
                    'course' => $_POST['course'],
                    'year_level' => $_POST['year_level'],
                    'section' => $_POST['section']
                ];

                // Check if username already exists
                if ($this->userModel->findByUsername($data['username'])) {
                    $message = "Username already exists. Please choose another.";
                } elseif ($role == 'STUDENT' && $this->userModel->findStudentByIdNumber($data['student_id'])) {
                    $message = "Student ID Number already registered.";
                } else {
                    $userId = $this->userModel->register($data);
                    if ($userId) {
                        if ($role == 'STUDENT') {
                            $this->userModel->registerStudent($userId, $data);
                        } elseif ($role == 'GUARD') {
                            $data['guard_rank'] = 'I'; 
                            $data['schedule'] = 'Full Time';
                            $this->userModel->registerGuard($userId, $data);
                        }
                        $success = true;
                        $message = "Successfully Registered!";
                        $formData = []; // Clear form on success
                    } else {
                        $message = "Error in registration. Please try again.";
                    }
                }
            }
        }
        echo $this->render_view('signup', ['message' => $message, 'formData' => $formData, 'success' => $success]);
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?url=auth/login");
        exit();
    }
}
?>
