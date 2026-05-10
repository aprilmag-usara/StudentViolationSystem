<?php
class Violation {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        // Check for duplicates (same student, same type, same description, not completed)
        // within the last 5 minutes to prevent accidental double submission
        $stmt = $this->conn->prepare("SELECT id FROM violations 
                                    WHERE student_user_id = ? 
                                    AND violation_type = ? 
                                    AND description = ? 
                                    AND status NOT IN ('completed', 'dropped')
                                    AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        $stmt->bind_param("iss", $data['student_user_id'], $data['violation_type'], $data['description']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return false; // Duplicate found
        }

        $stmt = $this->conn->prepare("INSERT INTO violations (student_user_id, guard_user_id, recorded_by_guard_name, violation_type, description, violation_time) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Database error (recorded_by_guard_name column might be missing): " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iissss", $data['student_user_id'], $data['guard_user_id'], $data['recorded_by_guard_name'], $data['violation_type'], $data['description'], $data['violation_time']);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function findByGuard($guardId) {
        $stmt = $this->conn->prepare("SELECT v.*, u.full_name as student_name, u.profile_photo, s.student_id_number, s.course 
                                    FROM violations v 
                                    JOIN users u ON v.student_user_id = u.id 
                                    JOIN students s ON u.id = s.user_id 
                                    WHERE v.guard_user_id = ?
                                    ORDER BY v.created_at DESC");
        if (!$stmt) {
            error_log("Database error in findByGuard: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $guardId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function findStudentById($studentIdNum) {
        $stmt = $this->conn->prepare("SELECT user_id FROM students WHERE student_id_number = ?");
        $stmt->bind_param("s", $studentIdNum);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findByStudent($userId, $limit = null) {
        $sql = "SELECT v.*, u.full_name as guard_name FROM violations v JOIN users u ON v.guard_user_id = u.id WHERE v.student_user_id = ? ORDER BY v.created_at DESC";
        if ($limit) {
            $sql .= " LIMIT ?";
        }
        $stmt = $this->conn->prepare($sql);
        if ($limit) {
            $stmt->bind_param("ii", $userId, $limit);
        } else {
            $stmt->bind_param("i", $userId);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getCompletedViolations() {
        $sql = "SELECT v.*, v.last_action_date as updated_at, u.full_name as student_name, s.student_id_number, s.course, s.year_level, s.section, u.profile_photo, g.full_name as guard_name
                FROM violations v 
                JOIN users u ON v.student_user_id = u.id 
                JOIN students s ON u.id = s.user_id 
                JOIN users g ON v.guard_user_id = g.id
                WHERE v.status = 'completed' 
                ORDER BY v.created_at DESC";
        return $this->conn->query($sql);
    }
}
?>
