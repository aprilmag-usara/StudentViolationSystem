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

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-dashboard">
        <div class="welcome-section">
            <h1>Violation Management</h1>
            <p>Full administrative control over all student records.</p>
        </div>

            <?php if (!empty($message)): ?>
                <div class="toast-container" id="toast">
                    <div class="toast-message">
                        <?php echo $message; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="glass-card">
                <div class="section-header">
                    <h2>Active Violations</h2>
                </div>

                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID Number</th>
                            <th>Violation</th>
                            <th>Status</th>
                            <th>Sanction</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($activeViolations && $activeViolations->num_rows > 0): ?>
                            <?php while ($v = $activeViolations->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="fw-600"><?php echo htmlspecialchars($v['student_name']); ?></div>
                                    <div class="fs-0-7 text-white-50"><?php echo htmlspecialchars($v['course']); ?></div>
                                </td>
                                <td class="font-monospace"><?php echo htmlspecialchars($v['student_id_number']); ?></td>
                                <td>
                                    <span class="status-dot <?php echo strtolower($v['violation_type']); ?>"></span>
                                    <?php echo $v['violation_type']; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($v['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $v['status'])); ?>
                                    </span>
                                </td>
                                <td class="font-italic text-mint-green">
                                    <?php echo $v['sanction'] ?: '<span class="opacity-30">None</span>'; ?>
                                </td>
                                <td>
                                    <div class="flex gap-10">
                                        <?php if ($v['status'] === 'pending'): ?>
                                            <form method="POST" class="display-inline">
                                                <input type="hidden" name="violation_id" value="<?php echo $v['id']; ?>">
                                                <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($v['student_name']); ?>">
                                                <button type="submit" name="receive_violation" class="modal-btn btn-receive px-15 fs-0-7">Receive</button>
                                            </form>
                                        <?php endif; ?>
                                        <button onclick='showViolationEditModal(<?php echo json_encode($v); ?>)' class="modal-btn modal-btn-yes px-15 fs-0-7">Edit</button>
                                        
                                        <?php if ($v['status'] !== 'completed' && $v['status'] !== 'pending'): ?>
                                            <form method="POST" class="display-inline">
                                                <input type="hidden" name="violation_id" value="<?php echo $v['id']; ?>">
                                                <button type="submit" name="complete_sanction" class="modal-btn btn-complete px-15 fs-0-7">Complete</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" class="display-inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                            <input type="hidden" name="violation_id" value="<?php echo $v['id']; ?>">
                                            <button type="submit" name="delete_violation" class="modal-btn btn-delete-record px-15 fs-0-7">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center p-40 opacity-50">No active violations.</div>
                <?php endif; ?>
            </div>

            <div class="glass-card mt-40 opacity-80">
                <div class="section-header">
                    <h2 class="text-white-60">Completed Sanctions History</h2>
                </div>
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
    </div>

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
                    <label class="info-label">Violation Description</label>
                    <textarea name="description" id="edit_desc" class="form-textarea mt-8"></textarea>
                </div>

                <div class="form-row">
                    <div>
                        <label class="info-label">Current Status</label>
                        <select name="status" id="edit_status" class="form-select mt-8">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="warning_sent">Warning Sent</option>
                            <option value="parent_called">Parent Called</option>
                            <option value="completed">Completed</option>
                            <option value="dropped">Dropped</option>
                        </select>
                    </div>
                    <div>
                        <label class="info-label">Assigned Sanction</label>
                        <input type="text" name="sanction" id="edit_sanction" class="search-input px-12 mt-8 border-radius-12 fs-0-9" placeholder="e.g. 3 days cleaning">
                    </div>
                </div>

                <div class="modal-buttons mt-30">
                    <button type="button" class="modal-btn modal-btn-no" onclick="hideViolationEditModal()">Cancel</button>
                    <button type="submit" name="update_violation" class="modal-btn modal-btn-yes">Save & Notify Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="hideLogoutModal()">&times;</span>
            <h2>Logging Out?</h2>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-no" onclick="hideLogoutModal()">Cancel</button>
                <a href="index.php?url=auth/logout" class="modal-btn modal-btn-yes no-underline">Yes, logout</a>
            </div>
        </div>
    </div>

    <script src="assets/js/osas.js"></script>
</body>
</html>