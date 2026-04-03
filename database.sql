CREATE DATABASE IF NOT EXISTS student_violation_system;
USE student_violation_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('STUDENT', 'GUARD', 'OSAS') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    bio TEXT,
    profile_photo VARCHAR(255) DEFAULT 'default_profile.png'
);

CREATE TABLE IF NOT EXISTS students (
    user_id INT PRIMARY KEY,
    student_id_number VARCHAR(20) UNIQUE NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_level VARCHAR(10) NOT NULL,
    section VARCHAR(10) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS system_auth_codes (
    role ENUM('GUARD', 'OSAS') PRIMARY KEY,
    passcode VARCHAR(255) NOT NULL
);

INSERT IGNORE INTO system_auth_codes (role, passcode) VALUES ('GUARD', 'guard123');
INSERT IGNORE INTO system_auth_codes (role, passcode) VALUES ('OSAS', 'osas_admin_123');

CREATE TABLE IF NOT EXISTS guards (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS guard_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO guard_list (name) VALUES ('Juan de la Cruz'), ('Christine Reyes'), ('John Pineda');

CREATE TABLE IF NOT EXISTS violations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_user_id INT NOT NULL,
    guard_user_id INT NOT NULL,
    recorded_by_guard_name VARCHAR(100), -- The specific guard name from guard_list
    violation_type ENUM('Minor', 'Major') NOT NULL,
    description TEXT NOT NULL,
    sanction TEXT, -- The assigned sanction from OSAS
    violation_time DATETIME NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'warning_sent', 'parent_called', 'dropped') DEFAULT 'pending',
    sanction_deadline DATETIME,
    last_action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    escalation_level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (guard_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    violation_id INT DEFAULT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (violation_id) REFERENCES violations(id) ON DELETE SET NULL
);
