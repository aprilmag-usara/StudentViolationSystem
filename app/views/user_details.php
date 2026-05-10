<?php 
/** @var array $user */
/** @var array|null $studentData */
/** @var mysqli_result|null $violations */
$user = $user ?? [];
$studentData = $studentData ?? null;
$violations = $violations ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - SVS</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .details-card {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            text-align: center;
        }
        .details-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--sage-green);
            margin-bottom: 20px;
        }
        .details-info {
            margin-top: 20px;
            text-align: left;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .info-label {
            color: var(--white-50);
            font-weight: 300;
        }
        .info-value {
            color: var(--white);
            font-weight: 500;
        }
        .violation-tag {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .tag-major { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .tag-minor { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    </style>
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <main class="main-dashboard">
        <div class="details-card glass-card">
            <?php 
                $full_name = $user['full_name'];
                $photoPath = !empty($user['profile_photo']) && $user['profile_photo'] !== 'default_profile.png' 
                    ? 'assets/img/profiles/' . $user['profile_photo'] 
                    : '';
                $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=1b4332&color=fff&size=150";
            ?>
            <img src="<?php echo $photoPath; ?>" alt="Profile" class="details-photo" onerror="this.src='<?php echo $avatar_url; ?>'">
            
            <h2 class="fs-1-8 mb-5"><?php echo htmlspecialchars($full_name); ?></h2>
            <p class="text-sage-green fw-600 mb-20"><?php echo htmlspecialchars($user['role']); ?></p>

            <div class="details-info">
                <?php if ($user['role'] === 'STUDENT' && $studentData): ?>
                    <div class="info-row">
                        <span class="info-label">Student ID</span>
                        <span class="info-value"><?php echo htmlspecialchars($studentData['student_id_number']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Course</span>
                        <span class="info-value"><?php echo htmlspecialchars($studentData['course']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Year & Section</span>
                        <span class="info-value"><?php echo htmlspecialchars($studentData['year_level'] . ' - ' . $studentData['section']); ?></span>
                    </div>
                    
                    <h3 class="mt-30 mb-15 fs-1-2">Violation History</h3>
                    <?php if ($violations && $violations->num_rows > 0): ?>
                        <?php while($v = $violations->fetch_assoc()): ?>
                            <div class="info-row">
                                <div>
                                    <span class="violation-tag tag-<?php echo strtolower($v['violation_type']); ?>">
                                        <?php echo $v['violation_type']; ?>
                                    </span>
                                    <span class="fs-0-9 ml-10"><?php echo htmlspecialchars($v['description']); ?></span>
                                </div>
                                <span class="text-white-50 fs-0-8"><?php echo date('M d, Y', strtotime($v['created_at'])); ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-white-50 fs-0-9">No violations recorded.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="info-row">
                        <span class="info-label">Username</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">Active</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-40">
                <a href="index.php" class="modal-btn modal-btn-yes px-40">Back to Home</a>
            </div>
        </div>
    </main>
</body>
</html>