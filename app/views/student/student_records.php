<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVS | Violation Records</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-bg-overlay"></div>

    <!-- Navigation & Modals -->
    <?php include __DIR__ . '/../navbar.php'; ?>

    <main class="main-dashboard">
        <div class="welcome-section">
            <h1 class="fs-2-5">Violation History</h1>
            <p class="text-white-60">A complete list of your past and present violation records recorded by CHMSU Security.</p>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="card-header-styled">
                <h3 class="m-0 fs-1-2 fw-600">Disciplinary Records</h3>
            </div>
            
            <div class="p-30">
                <?php if ($violations->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="records-table-modern">
                            <thead>
                                <tr>
                                    <th>Violation Description</th>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                    <th>Guard in Charge</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $violations->data_seek(0); 
                                while ($row = $violations->fetch_assoc()): 
                                    $vTypeClass = strtolower($row['violation_type']);
                                    $statusClass = strtolower($row['status']);
                                ?>
                                <tr>
                                    <td class="fw-500 text-white min-w-200">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </td>
                                    <td>
                                        <span class="v-type-badge <?php echo $vTypeClass; ?>">
                                            <?php echo $row['violation_type']; ?>
                                        </span>
                                    </td>
                                    <td class="fs-0-85 text-white-60">
                                        <div class="text-white fw-500 mb-3">
                                            <?php echo date('M d, Y', strtotime($row['violation_time'])); ?>
                                        </div>
                                        <?php echo date('h:i A', strtotime($row['violation_time'])); ?>
                                    </td>
                                    <td class="fs-0-9 text-white-70">
                                        <i class="fas fa-user-shield mr-5 text-sage-green fs-0-8"></i>
                                        <?php echo htmlspecialchars($row['recorded_by_guard_name'] ?: 'System'); ?>
                                    </td>
                                    <td>
                                        <span class="status-pill-modern <?php echo $statusClass; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-details-mini" onclick='showViolationDetails(<?php echo json_encode($row); ?>)'>
                                            Details
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-60">
                        <div class="empty-state-icon mb-20">🎉</div>
                        <h2 class="text-mint-green mb-10">Clean Record!</h2>
                        <p class="text-white-50 fw-300 fs-1-1">You don't have any violation records yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Violation Detail Modal (Same as dashboard) -->
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

            badge.textContent = v.violation_type;
            badge.className = 'v-type-badge ' + v.violation_type.toLowerCase();
            desc.textContent = v.description;
            
            const date = new Date(v.violation_time);
            dateTime.textContent = date.toLocaleDateString('en-US', { 
                month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
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

        window.onclick = function(event) {
            const modal = document.getElementById('violationModal');
            if (event.target == modal) hideViolationModal();
        }
    </script>

</body>
</html>
