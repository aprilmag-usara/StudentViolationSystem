<?php 
/** @var string $message */
/** @var mysqli_result $activeViolations */
/** @var mysqli_result $completedViolations */
/** @var int $unreadCount */
/** @var array $notifications */
$message = $message ?? '';
$activeViolations = $activeViolations ?? null;
$completedViolations = $completedViolations ?? null;
$unreadCount = $unreadCount ?? 0;
$notifications = $notifications ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Violation Records</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/osas.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section mb-40">
            <h1 class="glow-text">Violation Management</h1>
            <p class="subtitle-text">Full administrative control over all student records.</p>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                <?php if (!empty($message)): ?>
                    const msg = "<?php echo addslashes($message); ?>";
                    const isError = msg.toLowerCase().includes('error') || msg.toLowerCase().includes('failed');
                    showToast(isError ? 'Action Failed' : 'Success', msg, isError ? 'error' : 'success');
                <?php endif; ?>
            });
        </script>

        <!-- Active Violations Section -->
        <h2 class="record-section-title">Active Violations</h2>
        
        <?php if ($activeViolations && $activeViolations->num_rows > 0): ?>
            <div class="records-container">
                <?php while ($v = $activeViolations->fetch_assoc()): ?>
                <div class="record-item-card" id="violation-<?php echo $v['id']; ?>">
                    <!-- Student Info -->
                    <div>
                        <div class="record-label">Student</div>
                        <div class="fw-600 fs-1-1 text-white"><?php echo htmlspecialchars($v['student_name']); ?></div>
                        <div class="fs-0-8 text-white-40 mt-2"><?php echo htmlspecialchars($v['course']); ?></div>
                    </div>

                    <!-- ID Number -->
                    <div>
                        <div class="record-label">ID Number</div>
                        <div class="font-monospace text-sage-green fw-600"><?php echo htmlspecialchars($v['student_id_number']); ?></div>
                    </div>

                    <!-- Violation Type -->
                    <div>
                        <div class="record-label">Violation</div>
                        <div class="flex align-center gap-8">
                            <span class="status-dot <?php echo strtolower($v['violation_type']); ?>"></span>
                            <span class="text-white-70"><?php echo $v['violation_type']; ?></span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="record-label">Status</div>
                        <span class="badge badge-<?php echo strtolower($v['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $v['status'])); ?>
                        </span>
                    </div>

                    <!-- Sanction -->
                    <div>
                        <div class="record-label">Sanction</div>
                        <div class="font-italic text-mint-green fs-0-9">
                            <?php echo $v['sanction'] ?: '<span class="opacity-20">None</span>'; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="action-btn-group">
                        <button onclick='showViolationEditModal(<?php echo json_encode($v); ?>)' class="btn-record-action btn-edit-record">
                            Edit Record
                        </button>

                        <?php if ($v['status'] === 'pending'): ?>
                            <form method="POST" class="w-100">
                                <input type="hidden" name="violation_id" value="<?php echo $v['id']; ?>">
                                <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($v['student_name']); ?>">
                                <button type="submit" name="receive_violation" class="btn-record-action btn-complete-record">Receive</button>
                            </form>
                        <?php elseif ($v['status'] !== 'completed'): ?>
                            <form method="POST" class="w-100">
                                <input type="hidden" name="violation_id" value="<?php echo $v['id']; ?>">
                                <button type="submit" name="complete_sanction" class="btn-record-action btn-complete-record">Complete</button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" class="w-100" onsubmit="return confirm('Are you sure you want to delete this record?');">
                            <input type="hidden" name="violation_id" value="<?php echo $v['id']; ?>">
                            <button type="submit" name="delete_violation" class="btn-record-action btn-delete-record-styled">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="glass-card text-center p-60">
                <p class="text-white-30">No active violations found in the system.</p>
            </div>
        <?php endif; ?>

        <!-- History Section -->
        <h2 class="record-section-title mt-60 opacity-60">Completed History</h2>
        <div class="glass-card opacity-80">
            <table class="records-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID Number</th>
                        <th>Type</th>
                        <th>Violation Date</th>
                        <th>Sanction Served</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($completedViolations && $completedViolations->num_rows > 0): ?>
                        <?php while ($v = $completedViolations->fetch_assoc()): ?>
                        <tr class="bg-white-02">
                            <td>
                                <div class="fw-500 text-white-70"><?php echo htmlspecialchars($v['student_name']); ?></div>
                                <div class="fs-0-7 text-white-30"><?php echo htmlspecialchars($v['course']); ?></div>
                            </td>
                            <td class="font-monospace text-white-50"><?php echo htmlspecialchars($v['student_id_number']); ?></td>
                            <td>
                                <span class="fs-0-7 text-white-40 border-glass px-8 py-2 border-radius-5">
                                    <?php echo $v['violation_type']; ?>
                                </span>
                            </td>
                            <td class="fs-0-8 text-white-40">
                                <?php echo date('M d, Y', strtotime($v['updated_at'])); ?>
                            </td>
                            <td class="font-italic text-sage-green opacity-60">
                                <?php echo htmlspecialchars($v['sanction']); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-40 opacity-30">No history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content edit-modal-content">
            <span class="modal-close" onclick="hideViolationEditModal()">&times;</span>
            <h2 class="mb-25">Review Violation</h2>
            
            <form method="POST">
                <input type="hidden" name="violation_id" id="edit_v_id">
                <input type="hidden" name="student_user_id" id="edit_s_user_id">

                <div class="student-info-grid">
                    <img id="edit_photo" src="" class="border-radius-15 object-fit-cover border-sage-2" style="width: 100px; height: 100px;">
                    <div>
                        <div class="info-label">Student Name</div>
                        <div class="info-value" id="edit_name"></div>
                        <div class="form-row">
                            <div>
                                <div class="info-label">ID Number</div>
                                <div class="info-value" id="edit_id_num"></div>
                            </div>
                            <div>
                                <div class="info-label">Course / Year</div>
                                <div class="info-value" id="edit_course_year"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-20">
                    <label class="form-label">Violation Description</label>
                    <textarea name="description" id="edit_desc" class="form-textarea" placeholder="Enter violation details..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Current Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="warning_sent">Warning Sent</option>
                            <option value="parent_called">Parent Called</option>
                            <option value="completed">Completed</option>
                            <option value="dropped">Dropped</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assigned Sanction</label>
                        <input type="text" name="sanction" id="edit_sanction" class="form-input" placeholder="e.g. 3 days cleaning">
                    </div>
                </div>

                <div class="modal-buttons mt-30">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideViolationEditModal()">Cancel</button>
                    <button type="submit" name="update_violation" class="modal-btn modal-btn-yes">Save & Notify Student</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/osas.js"></script>
</body>
</html>