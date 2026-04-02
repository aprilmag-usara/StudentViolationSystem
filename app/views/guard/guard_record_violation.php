<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Violation - SVS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>SVS Guard</h2>
            <nav>
                <ul>
                    <li><a href="index.php?url=guard/dashboard">Home Page</a></li>
                    <li><a href="index.php?url=guard/record_violation" class="active">Input Violation</a></li>
                    <li><a href="index.php?url=auth/logout">Log Out</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Record Student Violation</h1>
                <p>Enter the details below to record a violation.</p>
            </header>

            <section class="form-section">
                <?php if (isset($message)) echo $message; ?>
                <form method="POST" action="index.php?url=guard/record_violation">
                    <input type="text" name="student_id" placeholder="Student ID Number" value="<?php echo $student_id_from_qr; ?>" required>
                    <select name="violation_type" required>
                        <option value="Minor">Minor Violation</option>
                        <option value="Major">Major Violation</option>
                    </select>
                    <textarea name="description" placeholder="Description of Violation" rows="4" required></textarea>
                    <input type="datetime-local" name="violation_time" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                    <button type="submit" name="record_violation">Send Violation to OSAS</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
