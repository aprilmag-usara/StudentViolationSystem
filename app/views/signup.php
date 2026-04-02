<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS - Create Account</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</head>
<body>
    <div class="bg-overlay"></div>

    <div class="auth-page-container">
        <div class="auth-card">
        <img src="assets/img/logo.png" alt="SVS Logo" class="logo-img">
        <?php if (isset($success) && $success): ?>
            <div class="success-container">
                <h2>Successfully Registered!</h2>
                <div class="login-box">
                    <p>Welcome to SVS. You can now access your account.</p>
                    <a href="index.php?url=auth/login" class="btn-auth ready no-underline display-block">Go to Login</a>
                </div>
            </div>
        <?php else: ?>
            <h2>Create Your Account</h2>
            <h3>Join our student violation management system</h3>

            <?php if (isset($message) && !empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?url=auth/signup">
                <!-- Role Selection -->
                <div class="form-group full-width">
                    <label for="role">Select Your Role</label>
                    <select name="role" id="role" onchange="handleRoleChange()" required>
                        <option value="">Choose your role...</option>
                        <option value="STUDENT" <?php echo (isset($formData['role']) && $formData['role'] == 'STUDENT') ? 'selected' : ''; ?>>Student</option>
                        <option value="GUARD" <?php echo (isset($formData['role']) && $formData['role'] == 'GUARD') ? 'selected' : ''; ?>>Guard</option>
                        <option value="OSAS" <?php echo (isset($formData['role']) && $formData['role'] == 'OSAS') ? 'selected' : ''; ?>>OSAS (Admin)</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" name="full_name" id="full_name" placeholder="John Doe" required value="<?php echo isset($formData['full_name']) ? htmlspecialchars($formData['full_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" placeholder="johndoe123" required value="<?php echo isset($formData['username']) ? htmlspecialchars($formData['username']) : ''; ?>">
                    </div>
                </div>

                <!-- Student Specific Fields -->
                <div id="student-fields" class="form-row display-none">
                    <div class="form-group">
                        <label for="student_id">ID Number</label>
                        <input type="text" name="student_id" id="student_id" placeholder="MAB04150500" value="<?php echo isset($formData['student_id']) ? htmlspecialchars($formData['student_id']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="course">Course</label>
                        <input type="text" name="course" id="course" placeholder="BSIT" value="<?php echo isset($formData['course']) ? htmlspecialchars($formData['course']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="year_level">Year Level</label>
                        <input type="text" name="year_level" id="year_level" placeholder="3rd Year" value="<?php echo isset($formData['year_level']) ? htmlspecialchars($formData['year_level']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="section">Section</label>
                        <input type="text" name="section" id="section" placeholder="A" value="<?php echo isset($formData['section']) ? htmlspecialchars($formData['section']) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                    </div>
                    <div id="auth-pass-container" class="form-group display-none">
                        <label for="auth_pass">Authorization Code</label>
                        <input type="password" name="auth_pass" id="auth_pass" placeholder="Required for staff">
                    </div>
                </div>

                <button type="submit" name="signup" id="submit-btn" class="btn-auth">Sign Up Now</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="index.php?url=auth/login">Log In Here</a></p>
                <p class="mt-15"><a href="index.php?url=home/index">← Back to Home</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
