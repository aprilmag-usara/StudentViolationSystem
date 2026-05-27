<?php 
/** @var array $studentInfo */
/** @var array $stats */
/** @var mysqli_result|null $violations */
$studentInfo = $studentInfo ?? ['full_name' => 'Student'];
$stats = $stats ?? ['pending' => 0, 'total' => 0];
$violations = $violations ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Student Home</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section">
            <div class="welcome-header">
                <div>
                    <h1 class="glow-text">Hello, <?php echo explode(' ', $studentInfo['full_name'])[0]; ?>!</h1>
                    <p class="subtitle-text">Your personal disciplinary overview.</p>
                </div>
                <div class="quick-stats">
                    <div class="stat-card-modern pending">
                        <span class="label">Pending</span>
                        <span class="value"><?php echo $stats['pending'] ?? 0; ?></span>
                    </div>
                    <div class="stat-card-modern total">
                        <span class="label">Total Cases</span>
                        <span class="value"><?php echo $stats['total'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Violations Section -->
        <div class="max-w-1000 mx-auto">
            <div class="glass-card mb-40 overflow-hidden">
                <div class="card-header-styled">
                    <h3 class="m-0 fs-1-2 fw-600">Recent Violations</h3>
                    <a href="index.php?url=student/records" class="view-all-link">View All Records</a>
                </div>
                
                <div class="violation-container p-30">
                    <?php if ($violations && $violations->num_rows > 0): ?>
                        <div class="violation-grid-modern">
                            <?php while ($v = $violations->fetch_assoc()): 
                                $vTypeClass = strtolower($v['violation_type']);
                                $statusClass = strtolower($v['status']);
                            ?>
                            <div class="violation-item-modern <?php echo $vTypeClass; ?>">
                                <div class="v-main-info">
                                    <div class="v-type-badge <?php echo $vTypeClass; ?>">
                                        <?php echo $v['violation_type']; ?>
                                    </div>
                                    <h4 class="v-title"><?php echo htmlspecialchars($v['description']); ?></h4>
                                    <div class="v-meta">
                                        <span class="v-date"><i class="far fa-calendar-alt mr-5"></i><?php echo date('M d, Y', strtotime($v['created_at'])); ?></span>
                                        <span class="v-guard"><i class="fas fa-user-shield mr-5"></i><?php echo htmlspecialchars($v['recorded_by_guard_name'] ?: 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="v-status-section">
                                    <div class="v-sanction">
                                        <span class="label">Assigned Sanction</span>
                                        <span class="value <?php echo $v['sanction'] ? 'text-mint-green' : 'text-white-20'; ?>">
                                            <?php echo $v['sanction'] ?: 'Under Review'; ?>
                                        </span>
                                    </div>
                                    <div class="v-actions">
                                        <span class="status-pill-modern <?php echo $statusClass; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $v['status'])); ?>
                                        </span>
                                        <button class="btn-details" onclick='showViolationDetails(<?php echo json_encode($v); ?>)'>
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-80">
                           
                            <h2 class="text-mint-green fw-700 mb-15">All Clear!</h2>
                            <p class="text-white-40 fw-300 fs-1-1 max-w-500 mx-auto">You currently have no recorded violations. Keep maintaining your excellent disciplinary standing!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Violation Detail Modal -->
    <div id="violationModal" class="modal-overlay">
        <div class="modal-content max-w-600">
            <span class="modal-close" onclick="hideViolationModal()">&times;</span>
            <div class="modal-header-styled mb-25">
                <h2 class="m-0 fs-1-5">Violation Details</h2>
                <div id="modalBadge" class="v-type-badge mt-10"></div>
            </div>
            
            <div class="modal-body-styled">
                <div class="detail-row mb-20">
                    <label class="text-white-40 fs-0-8 text-uppercase fw-600 letter-spacing-1">Violation Description</label>
                    <p id="modalDesc" class="fs-1-1 fw-500 text-white mt-5"></p>
                </div>
                
                <div class="detail-grid-2 mb-25">
                    <div class="detail-item">
                        <label class="text-white-40 fs-0-8 text-uppercase fw-600 letter-spacing-1">Date & Time</label>
                        <p id="modalDateTime" class="text-white mt-5"></p>
                    </div>
                    <div class="detail-item">
                        <label class="text-white-40 fs-0-8 text-uppercase fw-600 letter-spacing-1">Guard in Charge</label>
                        <p id="modalGuard" class="text-white mt-5"></p>
                    </div>
                </div>

                <div class="detail-row mb-25 p-20 bg-black-20 border-radius-15 border-1 border-white-10">
                    <label class="text-white-40 fs-0-8 text-uppercase fw-600 letter-spacing-1">Assigned Sanction</label>
                    <p id="modalSanction" class="fs-1-1 fw-600 text-mint-green mt-5"></p>
                </div>

                <div class="detail-row">
                    <label class="text-white-40 fs-0-8 text-uppercase fw-600 letter-spacing-1">Current Status</label>
                    <div class="mt-10">
                        <span id="modalStatus" class="status-pill-modern"></span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer-styled mt-30 text-right">
                <button class="modal-btn modal-btn-no" onclick="hideViolationModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function showViolationDetails(v) {
            const modal = document.getElementById('violationModal');
            const badge = document.getElementById('modalBadge');
            const desc = document.getElementById('modalDesc');
            const dateTime = document.getElementById('modalDateTime');
            const guard = document.getElementById('modalGuard');
            const sanction = document.getElementById('modalSanction');
            const status = document.getElementById('modalStatus');

            // Set content
            badge.textContent = v.violation_type;
            badge.className = 'v-type-badge ' + v.violation_type.toLowerCase();
            desc.textContent = v.description;
            
            const date = new Date(v.violation_time);
            dateTime.textContent = date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            guard.textContent = v.recorded_by_guard_name || 'System Recorded';
            sanction.textContent = v.sanction || 'Pending Review by OSAS';
            
            status.textContent = v.status.charAt(0).toUpperCase() + v.status.slice(1).replace('_', ' ');
            status.className = 'status-pill-modern ' + v.status.toLowerCase();

            modal.style.display = 'flex';
            if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
        }

        function hideViolationModal() {
            document.getElementById('violationModal').style.display = 'none';
            if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('violationModal');
            if (event.target == modal) {
                hideViolationModal();
            }
        }
    </script>

</body>
</html>
