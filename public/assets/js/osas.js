/**
 * OSAS Dashboard Charts
 */

document.addEventListener('DOMContentLoaded', () => {
    initCharts();
});

function initCharts() {
    // Violation Trends Chart
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Violations Reported',
                    data: [12, 19, 8, 15, 22, 10, 5, 12, 18, 25, 15, 10],
                    borderColor: '#40916c',
                    backgroundColor: 'rgba(64, 145, 108, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#40916c',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: 'rgba(255,255,255,0.5)' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: 'rgba(255,255,255,0.5)' }
                    }
                }
            }
        });
    }

    // Violation Types Distribution
    const typeCtx = document.getElementById('typeChart');
    if (typeCtx) {
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Minor', 'Major'],
                datasets: [{
                    data: [75, 25],
                    backgroundColor: ['#40916c', '#e74c3c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'rgba(255,255,255,0.7)', padding: 20, font: { family: 'Poppins' } }
                    }
                },
                cutout: '70%'
            }
        });
    }
}

/**
 * OSAS Modal & UI Logic
 */

// Profile Modals
function showEditModal() { 
    const modal = document.getElementById('editProfileModal');
    if (modal) modal.style.display = 'flex'; 
}

function hideEditModal() { 
    const modal = document.getElementById('editProfileModal');
    if (modal) modal.style.display = 'none'; 
}

function showPasswordModal() { 
    const modal = document.getElementById('passwordModal');
    if (modal) modal.style.display = 'flex'; 
}

function hidePasswordModal() { 
    const modal = document.getElementById('passwordModal');
    if (modal) modal.style.display = 'none'; 
}

// Records Management
function showViolationEditModal(v) {
    const modal = document.getElementById('editModal');
    if (!modal) return;

    const fields = {
        'edit_v_id': v.id,
        'edit_s_user_id': v.student_user_id,
        'edit_desc': v.description,
        'edit_status': v.status,
        'edit_sanction': v.sanction || ''
    };

    for (const [id, value] of Object.entries(fields)) {
        const el = document.getElementById(id);
        if (el) el.value = value;
    }

    const textFields = {
        'edit_name': v.student_name,
        'edit_id_num': v.student_id_number,
        'edit_course_year': `${v.course} - ${v.year_level}`
    };

    for (const [id, text] of Object.entries(textFields)) {
        const el = document.getElementById(id);
        if (el) el.innerText = text;
    }
    
    const photoEl = document.getElementById('edit_photo');
    if (photoEl) {
        const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(v.student_name)}&background=1b4332&color=fff&size=100`;
        photoEl.src = v.profile_photo && v.profile_photo !== 'default_profile.png' 
            ? `assets/img/profiles/${v.profile_photo}` 
            : avatarUrl;
    }

    modal.style.display = 'flex';
}

function hideViolationEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.style.display = 'none';
}

// Guard Activity
function viewGuardRecords(name) {
    const modal = document.getElementById('guardModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    const activityList = document.getElementById('activityList');
    
    if (!modal || !loading || !content || !activityList) return;

    const titleEl = document.getElementById('modalTitle');
    const subtitleEl = document.getElementById('modalSubtitle');
    if (titleEl) titleEl.innerText = name;
    if (subtitleEl) subtitleEl.innerText = `Full reporting history for ${name}`;
    
    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';

    fetch(`index.php?url=osas/guard_ajax&name=${encodeURIComponent(name)}`)
        .then(r => r.json())
        .then(data => {
            activityList.innerHTML = '';
            if (data.records && data.records.length > 0) {
                data.records.forEach(v => {
                    const item = document.createElement('div');
                    item.className = 'history-item';
                    item.innerHTML = `
                        <div class="history-info">
                            <h4>${v.student_name}</h4>
                            <p>${v.student_id_number} | ${v.violation_type} Violation</p>
                            <p style="font-size: 0.7rem; margin-top: 5px; opacity: 0.6;">${new Date(v.created_at).toLocaleString()}</p>
                        </div>
                        <div class="history-badge ${v.violation_type.toLowerCase()}">${v.violation_type}</div>
                    `;
                    activityList.appendChild(item);
                });
            } else {
                activityList.innerHTML = '<div style="padding:40px; text-align:center; opacity:0.3;">No records found for this guard.</div>';
            }
            loading.style.display = 'none';
            content.style.display = 'block';
        })
        .catch(err => {
            console.error('Error fetching guard records:', err);
            activityList.innerHTML = '<div style="padding:40px; text-align:center; color:#e74c3c;">Failed to load records.</div>';
            loading.style.display = 'none';
            content.style.display = 'block';
        });
}

function hideGuardModal() {
    const modal = document.getElementById('guardModal');
    if (modal) modal.style.display = 'none';
}

// Student Management
function viewStudent(id) {
    const modal = document.getElementById('studentModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    
    if (!modal || !loading || !content) return;

    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';

    fetch(`index.php?url=osas/student_ajax&id=${id}`)
        .then(r => r.json())
        .then(data => {
            const s = data.student;
            const vs = data.violations;

            const fields = {
                'det_name': s.full_name,
                'det_id': s.role === 'STUDENT' ? s.student_id_number : s.role,
                'det_course': s.role === 'STUDENT' ? s.course : 'System Staff',
                'det_year_sec': s.role === 'STUDENT' ? `${s.year_level} - ${s.section}` : 'N/A',
                'det_bio': s.bio || 'No bio provided.'
            };

            for (const [id, value] of Object.entries(fields)) {
                const el = document.getElementById(id);
                if (el) el.innerText = value;
            }
            
            const photoEl = document.getElementById('det_photo');
            if (photoEl) {
                const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(s.full_name)}&background=1b4332&color=fff&size=120`;
                photoEl.src = s.profile_photo && s.profile_photo !== 'default_profile.png' 
                    ? `assets/img/profiles/${s.profile_photo}` 
                    : avatarUrl;
            }

            const historyContainer = document.getElementById('det_history');
            if (historyContainer) {
                historyContainer.innerHTML = '';
                if (vs && vs.length > 0) {
                    vs.forEach(v => {
                        const item = document.createElement('div');
                        item.className = `history-item ${v.violation_type.toLowerCase()}`;
                        item.innerHTML = `
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                <span style="font-weight:600; color:white;">${v.violation_type} Violation</span>
                                <span style="font-size:0.7rem; opacity:0.5;">${new Date(v.violation_time).toLocaleDateString()}</span>
                            </div>
                            <div style="font-size:0.85rem; color:rgba(255,255,255,0.8); margin-bottom:8px;">${v.description}</div>
                            <div style="font-size:0.75rem; color:var(--mint-green);">Sanction: ${v.sanction || 'Pending review'}</div>
                        `;
                        historyContainer.appendChild(item);
                    });
                } else {
                    historyContainer.innerHTML = '<div style="padding:20px; text-align:center; opacity:0.3;">Clean record. No violations found.</div>';
                }
            }

            loading.style.display = 'none';
            content.style.display = 'grid';
        })
        .catch(err => {
            console.error('Error fetching student details:', err);
            loading.innerText = 'Failed to load profile.';
        });
}

function hideStudentModal() {
    const modal = document.getElementById('studentModal');
    if (modal) modal.style.display = 'none';
}

// Student Search & Filtering
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('studentSearch');
    const searchDropdown = document.getElementById('searchDropdown');

    if (searchInput && searchDropdown) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 1) {
                searchDropdown.style.display = 'none';
                return;
            }

            fetch(`index.php?url=guard/search_ajax&query=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    searchDropdown.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(s => {
                            const div = document.createElement('div');
                            div.className = 'search-result-item';
                            div.innerHTML = `
                                <div class="result-info">
                                    <h4>${s.full_name}</h4>
                                    <p>${s.student_id_number} | ${s.course} (${s.year_level})</p>
                                </div>
                            `;
                            div.onclick = () => {
                                viewStudent(s.id);
                                searchDropdown.style.display = 'none';
                                searchInput.value = '';
                            };
                            searchDropdown.appendChild(div);
                        });
                        searchDropdown.style.display = 'block';
                    } else {
                        searchDropdown.style.display = 'none';
                    }
                });
        });

        // Instant Filter (on-page filter as user types)
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const cards = document.querySelectorAll('.student-mini-card');
            const sections = document.querySelectorAll('.year-section');

            cards.forEach(card => {
                const name = card.querySelector('h4').innerText.toLowerCase();
                const id = card.querySelector('p').innerText.toLowerCase();
                if (name.includes(query) || id.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            sections.forEach(section => {
                const visibleCards = section.querySelectorAll('.student-mini-card[style="display: flex;"]');
                section.style.display = (visibleCards.length === 0 && query !== '') ? 'none' : 'block';
            });
        });
    }
});

// Generic Outside Click Handler for OSAS specific modals
window.addEventListener('click', (e) => {
    const modals = ['editProfileModal', 'passwordModal', 'editModal', 'guardModal', 'studentModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && e.target === modal) modal.style.display = 'none';
    });
    
    const searchDropdown = document.getElementById('searchDropdown');
    const searchInput = document.getElementById('studentSearch');
    if (searchDropdown && searchInput && !searchInput.contains(e.target)) {
        searchDropdown.style.display = 'none';
    }
});
