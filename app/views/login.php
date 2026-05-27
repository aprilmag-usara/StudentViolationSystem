<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="assets/js/main.js"></script>
</head>
<body>
    <div class="bg-overlay"></div>

    <div class="auth-page-container">
        <div class="auth-card max-w-450">
            <div class="logo-container flex-center mb-20">
                <img src="assets/img/logos2.png" alt="SVS Logo" class="logo-img">
            </div>
            
            <h2>Welcome Back</h2>
            <h3>Login to your SVS account</h3>

            <?php if (isset($message) && !empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?url=auth/login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>

                <button type="submit" name="login" class="btn-auth">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="index.php?url=auth/signup">Register here</a></p>
                <p class="mt-15"><a href="index.php?url=home/index">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
