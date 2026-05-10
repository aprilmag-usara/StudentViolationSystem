<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByUsername($username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findStudentByIdNumber($idNumber) {
        $stmt = $this->conn->prepare("SELECT * FROM students WHERE student_id_number = ?");
        $stmt->bind_param("s", $idNumber);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function register($data) {
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['username'], $data['password'], $data['full_name'], $data['role']);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function registerStudent($userId, $data) {
        $stmt = $this->conn->prepare("INSERT INTO students (user_id, student_id_number, course, year_level, section) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $data['student_id'], $data['course'], $data['year_level'], $data['section']);
        return $stmt->execute();
    }

    public function registerGuard($userId, $data) {
        $stmt = $this->conn->prepare("INSERT INTO guards (user_id, guard_rank, schedule) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $data['guard_rank'], $data['schedule']);
        return $stmt->execute();
    }

    public function updateProfile($userId, $username, $bio) {
        $stmt = $this->conn->prepare("UPDATE users SET username = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $bio, $userId);
        return $stmt->execute();
    }

    public function updateProfilePhoto($userId, $photoName) {
        $stmt = $this->conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        $stmt->bind_param("si", $photoName, $userId);
        return $stmt->execute();
    }

    public function updatePassword($userId, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $userId);
        return $stmt->execute();
    }

    public function getStudentInfo($userId) {
        $stmt = $this->conn->prepare("SELECT u.*, s.student_id_number, s.course, s.year_level, s.section FROM users u JOIN students s ON u.id = s.user_id WHERE u.id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getGuardInfo($userId) {
        $stmt = $this->conn->prepare("SELECT u.full_name FROM users u JOIN guards g ON u.id = g.user_id WHERE u.id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getGuardList() {
        $stmt = $this->conn->prepare("SELECT name FROM guard_list WHERE status = 'active' ORDER BY name ASC");
        if (!$stmt) {
            error_log("Database error (guard_list table might be missing): " . $this->conn->error);
            return null;
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getGuardsWithCounts() {
        $sql = "SELECT g.id, g.name, 
                (SELECT COUNT(*) FROM violations v WHERE v.recorded_by_guard_name = g.name) as report_count
                FROM guard_list g 
                WHERE g.status = 'active'
                ORDER BY g.name ASC";
        return $this->conn->query($sql);
    }

    public function updateGuardListName($id, $newName) {
        $stmt = $this->conn->prepare("UPDATE guard_list SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $id);
        return $stmt->execute();
    }

    public function getAuthPasscode($role) {
        $stmt = $this->conn->prepare("SELECT passcode FROM system_auth_codes WHERE role = ?");
        if (!$stmt) {
            error_log("Database error: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['passcode'] : null;
    }

    // Notifications Logic
    public function getUnreadNotificationCount($userId, $role = null) {
        $sql = "SELECT COUNT(*) FROM notifications n JOIN users u ON n.user_id = u.id WHERE n.is_read = FALSE AND (n.user_id = ?";
        if ($role === 'GUARD' || $role === 'OSAS') {
            $sql .= " OR u.role = ?";
        }
        $sql .= ")";
        
        $stmt = $this->conn->prepare($sql);
        if ($role === 'GUARD' || $role === 'OSAS') {
            $stmt->bind_param("is", $userId, $role);
        } else {
            $stmt->bind_param("i", $userId);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        return $result ? (int)$result[0] : 0;
    }

    public function getNotifications($userId, $limit = 5, $role = null) {
        $sql = "SELECT n.* FROM notifications n JOIN users u ON n.user_id = u.id WHERE (n.user_id = ?";
        if ($role === 'GUARD' || $role === 'OSAS') {
            $sql .= " OR u.role = ?";
        }
        $sql .= ") ORDER BY n.created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if ($role === 'GUARD' || $role === 'OSAS') {
            $stmt->bind_param("isi", $userId, $role, $limit);
        } else {
            $stmt->bind_param("ii", $userId, $limit);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function markNotificationsAsRead($userId, $role = null) {
        $sql = "UPDATE notifications n JOIN users u ON n.user_id = u.id SET n.is_read = TRUE WHERE n.is_read = FALSE AND (n.user_id = ?";
        if ($role === 'GUARD' || $role === 'OSAS') {
            $sql .= " OR u.role = ?";
        }
        $sql .= ")";
        
        $stmt = $this->conn->prepare($sql);
        if ($role === 'GUARD' || $role === 'OSAS') {
            $stmt->bind_param("is", $userId, $role);
        } else {
            $stmt->bind_param("i", $userId);
        }
        return $stmt->execute();
    }

    public function createNotification($userId, $message, $violationId = null) {
        if ($violationId) {
            $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message, violation_id) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isi", $userId, $message, $violationId);
                return $stmt->execute();
            }
        }
        
        // Fallback or if violationId is null
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("is", $userId, $message);
            return $stmt->execute();
        }
        
        error_log("Failed to create notification: " . $this->conn->error);
        return false;
    }

    public function getOsasAdmins() {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE role = 'OSAS'");
        $stmt->execute();
        return $stmt->get_result();
    }

    public function searchStudents($query) {
        $searchTerm = "%$query%";
        $stmt = $this->conn->prepare("SELECT u.id, u.full_name, u.username, s.student_id_number, s.course, s.year_level, s.section, u.profile_photo 
                                    FROM users u 
                                    JOIN students s ON u.id = s.user_id 
                                    WHERE u.full_name LIKE ? OR s.student_id_number LIKE ? 
                                    LIMIT 10");
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllStudents() {
        $stmt = $this->conn->prepare("SELECT u.*, s.student_id_number, s.course, s.year_level, s.section 
                                    FROM users u 
                                    LEFT JOIN students s ON u.id = s.user_id 
                                    ORDER BY s.year_level ASC, u.full_name ASC");
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getStudentDetails($userId) {
        // Basic student info
        $stmt = $this->conn->prepare("SELECT u.*, s.student_id_number, s.course, s.year_level, s.section 
                                    FROM users u 
                                    JOIN students s ON u.id = s.user_id 
                                    WHERE u.id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();

        if ($student) {
            // Get violations
            $stmt = $this->conn->prepare("SELECT v.*, g.full_name as guard_name 
                                        FROM violations v 
                                        JOIN users g ON v.guard_user_id = g.id 
                                        WHERE v.student_user_id = ? 
                                        ORDER BY v.created_at DESC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $student['violations'] = $stmt->get_result();
        }

        return $student;
    }
}
?>
