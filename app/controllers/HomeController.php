<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Violation.php';

class HomeController extends BaseController {
    private $userModel;
    private $violationModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new User($db);
        $this->violationModel = new Violation($db);
    }

    public function index() {
        echo $this->render_view('landing');
    }

    public function view_user() {
        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            header("Location: index.php");
            exit();
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            echo "User not found.";
            return;
        }

        $studentData = null;
        $violations = null;
        if ($user['role'] === 'STUDENT') {
            $studentData = $this->userModel->getStudentInfo($userId);
            $violations = $this->violationModel->getStudentViolations($userId);
        }

        echo $this->render_view('user_details', [
            'user' => $user,
            'studentData' => $studentData,
            'violations' => $violations
        ]);
    }
}
?>
