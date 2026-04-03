/**
 * OSAS Dashboard Charts
 */

document.addEventListener('DOMContentLoaded', () => {
    initCharts();
});

function initCharts() {
    // Shared chart options
    const sharedOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }
            },
            x: {
                grid: { display: false },
                ticks: { color: 'rgba(255,255,255,0.5)', font: { size: 10 } }
            }
        }
    };

    // Use global chartData if available, otherwise use defaults
    const data = typeof chartData !== 'undefined' ? chartData : {
        monthly: { months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], counts: [0,0,0,0,0,0,0,0,0,0,0,0] },
        category: { Minor: 0, Major: 0 },
        course: { courses: [], counts: [] },
        yearLevel: { levels: [], counts: [] }
    };

    // 1. Violation Trends Chart (Line Chart)
    const trendsCtx = document.getElementById('violationTrendsChart');
    if (trendsCtx) {
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: data.monthly.months,
                datasets: [{
                    label: 'Violations',
                    data: data.monthly.counts,
                    borderColor: '#40916c',
                    backgroundColor: 'rgba(64, 145, 108, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#40916c',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: sharedOptions
        });
    }

    // 2. Type Distribution (Doughnut Chart)
    const categoryCtx = document.getElementById('violationCategoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Minor', 'Major'],
                datasets: [{
                    data: [data.category.Minor, data.category.Major],
                    backgroundColor: ['#f1c40f', '#e74c3c'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255,255,255,0.7)',
                            padding: 20,
                            font: { family: 'Poppins', size: 11 }
                        }
                    }
                }
            }
        });
    }

    // 3. Top Course Cases (Bar Chart)
    const courseCtx = document.getElementById('courseChart');
    if (courseCtx) {
        new Chart(courseCtx, {
            type: 'bar',
            data: {
                labels: data.course.courses.length > 0 ? data.course.courses : ['None'],
                datasets: [{
                    data: data.course.counts.length > 0 ? data.course.counts : [0],
                    backgroundColor: '#40916c',
                    borderRadius: 6
                }]
            },
            options: sharedOptions
        });
    }

    // 4. Year Level Growth (Bar Chart)
    const yearLevelCtx = document.getElementById('yearLevelChart');
    if (yearLevelCtx) {
        new Chart(yearLevelCtx, {
            type: 'bar',
            data: {
                labels: data.yearLevel.levels.length > 0 ? data.yearLevel.levels.map(l => l + (isNaN(l) ? '' : ' Year')) : ['None'],
                datasets: [{
                    data: data.yearLevel.counts.length > 0 ? data.yearLevel.counts : [0],
                    backgroundColor: 'rgba(64, 145, 108, 0.7)',
                    borderRadius: 6
                }]
            },
            options: sharedOptions
        });
    }
}

/**
 * OSAS Modal & UI Logic
 */

// Profile Modals
function showEditModal() { 
    const modal = document.getElementById('editProfileModal');
    if (modal) {
        modal.style.display = 'flex';
        toggleBodyScroll(true);
    }
}

function hideEditModal() { 
    const modal = document.getElementById('editProfileModal');
    if (modal) {
        modal.style.display = 'none';
        toggleBodyScroll(false);
    }
}

function showPasswordModal() { 
    const modal = document.getElementById('passwordModal');
    if (modal) {
        modal.style.display = 'flex';
        toggleBodyScroll(true);
    }
}

function hidePasswordModal() { 
    const modal = document.getElementById('passwordModal');
    if (modal) {
        modal.style.display = 'none';
        toggleBodyScroll(false);
    }
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
    toggleBodyScroll(true);
}

function hideViolationEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
        toggleBodyScroll(false);
    }
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
    toggleBodyScroll(true);
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
    if (modal) {
        modal.style.display = 'none';
        toggleBodyScroll(false);
    }
}

// Student Management
function viewStudent(id) {
    const modal = document.getElementById('studentModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    
    if (!modal || !loading || !content) return;

    modal.style.display = 'flex';
    toggleBodyScroll(true);
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
                        item.className = `history-item-modern ${v.violation_type.toLowerCase()}`;
                        item.innerHTML = `
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; align-items:center;">
                                <span class="v-type-badge ${v.violation_type.toLowerCase()}" style="margin-bottom:0; font-size:0.65rem;">${v.violation_type} Violation</span>
                                <span style="font-size:0.8rem; opacity:0.5; font-weight:500;">${new Date(v.violation_time).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                            </div>
                            <div style="font-size:1.05rem; color:white; font-weight:500; margin-bottom:10px;">${v.description}</div>
                            <div class="flex-between align-center">
                                <span style="font-size:0.8rem; color:var(--mint-green); font-weight:600;">Sanction: ${v.sanction || 'Pending review'}</span>
                                <span class="status-pill-modern ${v.status.toLowerCase()}" style="padding:4px 12px; font-size:0.65rem;">${v.status.replace('_', ' ')}</span>
                            </div>
                        `;
                        historyContainer.appendChild(item);
                    });
                } else {
                    historyContainer.innerHTML = '<div style="padding:60px; text-align:center; opacity:0.3; font-style:italic;">No disciplinary records found. This student has a clean conduct record.</div>';
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
    if (modal) {
        modal.style.display = 'none';
        toggleBodyScroll(false);
    }
}

// Student Search & Filtering (On-page)
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('studentSearch');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
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

            // Show/Hide sections based on visible cards
            let totalVisible = 0;
            sections.forEach(section => {
                const visibleCards = section.querySelectorAll('.student-mini-card[style="display: flex;"]');
                section.style.display = (visibleCards.length === 0 && query !== '') ? 'none' : 'block';
                totalVisible += visibleCards.length;
            });

            // Handle empty state
            const noResults = document.getElementById('noResults');
            if (noResults) {
                noResults.style.display = (totalVisible === 0 && query !== '') ? 'block' : 'none';
            }
        });
    }
});

function showEditGuardModal(id, name) {
    const modal = document.getElementById('editGuardModal');
    if (modal) {
        document.getElementById('editGuardId').value = id;
        document.getElementById('editGuardName').value = name;
        modal.style.display = 'flex';
        toggleBodyScroll(true);
    }
}

function hideEditGuardModal() {
    const modal = document.getElementById('editGuardModal');
    if (modal) {
        modal.style.display = 'none';
        toggleBodyScroll(false);
    }
}

// Generic Outside Click Handler for OSAS specific modals
window.addEventListener('click', (e) => {
    const modals = ['editProfileModal', 'passwordModal', 'editModal', 'guardModal', 'studentModal', 'editGuardModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && e.target === modal) {
            modal.style.display = 'none';
            toggleBodyScroll(false);
        }
    });
});
