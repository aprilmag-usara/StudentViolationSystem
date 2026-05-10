document.addEventListener('DOMContentLoaded', () => {
    // Notification item clicks
    const notifItems = document.querySelectorAll('.notif-row');
    notifItems.forEach(item => {
        item.addEventListener('click', () => {
            const url = item.getAttribute('data-url');
            if (url) window.location.href = url;
        });
    });
});

/**
 * Violation Record Modal Logic
 */
function showViolationEditModal(v) {
    const modal = document.getElementById('editModal');
    if (!modal) return;

    // Populate Modal Fields
    document.getElementById('edit_v_id').value = v.id;
    document.getElementById('edit_s_user_id').value = v.student_user_id;
    document.getElementById('edit_name').innerText = v.student_name;
    document.getElementById('edit_id_num').innerText = v.student_id_number;
    document.getElementById('edit_course_year').innerText = `${v.course} - ${v.year_level}${v.section}`;
    document.getElementById('edit_desc').value = v.description;
    document.getElementById('edit_status').value = v.status;
    document.getElementById('edit_sanction').value = v.sanction || '';

    // Handle Photo
    const photoImg = document.getElementById('edit_photo');
    if (photoImg) {
        const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(v.student_name)}&background=1b4332&color=fff&size=100`;
        photoImg.src = v.profile_photo && v.profile_photo !== 'default_profile.png' 
            ? `assets/img/profiles/${v.profile_photo}` 
            : avatarUrl;
    }

    modal.style.display = 'flex';
    if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
}

function hideViolationEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
    }
}

// Close modal on outside click
window.addEventListener('click', (event) => {
    const editModal = document.getElementById('editModal');
    if (event.target === editModal) {
        hideViolationEditModal();
    }

    // Close other modals
    const modals = [
        'addGuardModal', 'editGuardModal', 'guardModal', 
        'addStudentModal', 'editStudentModal', 'deleteStudentModal', 'studentModal'
    ];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (event.target === modal) {
            modal.style.display = 'none';
            if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
        }
    });
});

function showAddGuardModal() {
    const modal = document.getElementById('addGuardModal');
    if (modal) {
        modal.style.display = 'flex';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
    }
}

function hideAddGuardModal() {
    const modal = document.getElementById('addGuardModal');
    if (modal) {
        modal.style.display = 'none';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
    }
}

function showEditGuardModal(id, name) {
    const modal = document.getElementById('editGuardModal');
    if (modal) {
        document.getElementById('editGuardId').value = id;
        document.getElementById('editGuardName').value = name;
        modal.style.display = 'flex';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
    }
}

function hideEditGuardModal() {
    const modal = document.getElementById('editGuardModal');
    if (modal) {
        modal.style.display = 'none';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
    }
}

function viewGuardRecords(name) {
    const modal = document.getElementById('guardModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const list = document.getElementById('activityList');

    if (!modal) return;

    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.classList.add('display-none');
    title.innerText = name;
    subtitle.innerText = "Security Personnel";
    if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);

    fetch(`index.php?url=osas/guard_ajax&name=${encodeURIComponent(name)}`)
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            content.classList.remove('display-none');
            list.innerHTML = '';

            if (data.records.length > 0) {
                data.records.forEach(r => {
                    const item = document.createElement('div');
                    item.className = 'history-item-minimal';
                    item.innerHTML = `
                        <div class="flex-between mb-5">
                            <span class="fw-600 text-white">${r.student_name}</span>
                            <span class="fs-0-7 text-white-40">${new Date(r.created_at).toLocaleDateString()}</span>
                        </div>
                        <div class="fs-0-85 text-white-60 mb-5">${r.description}</div>
                        <div class="flex-between align-center">
                            <span class="badge badge-${r.status.toLowerCase()}">${r.status.replace('_', ' ')}</span>
                            <span class="fs-0-75 text-mint-green italic">${r.sanction || 'No sanction'}</span>
                        </div>
                    `;
                    list.appendChild(item);
                });
            } else {
                list.innerHTML = '<div class="text-center p-30 text-white-30">No reports recorded by this guard.</div>';
            }
        });
}

function hideGuardModal() {
    const modal = document.getElementById('guardModal');
    if (modal) {
        modal.style.display = 'none';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
    }
}

function showAddStudentModal() {
    const modal = document.getElementById('addStudentModal');
    if (modal) {
        modal.style.display = 'flex';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);
    }
}

function hideAddStudentModal() {
    const modal = document.getElementById('addStudentModal');
    if (modal) {
        modal.style.display = 'none';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
    }
}

let currentStudent = null;

function viewStudent(id) {
    const modal = document.getElementById('studentModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');

    if (!modal) return;

    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.classList.add('display-none');
    if (typeof toggleBodyScroll === 'function') toggleBodyScroll(true);

    fetch(`index.php?url=osas/student_ajax&id=${id}`)
        .then(response => response.json())
        .then(data => {
            currentStudent = data.student;
            loading.style.display = 'none';
            content.classList.remove('display-none');

            // Populate Info
            const s = data.student;
            const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(s.full_name)}&background=1b4332&color=fff&size=150`;
            document.getElementById('det_photo').src = s.profile_photo && s.profile_photo !== 'default_profile.png' 
                ? `assets/img/profiles/${s.profile_photo}` 
                : avatarUrl;
            document.getElementById('det_name').innerText = s.full_name;
            document.getElementById('det_id').innerText = s.student_id_number;
            document.getElementById('det_course').innerText = s.course;
            document.getElementById('det_year_sec').innerText = `${s.year_level} - ${s.section}`;
            document.getElementById('det_bio').innerText = s.bio || "No biography provided.";

            // Populate History
            const historyList = document.getElementById('det_history');
            historyList.innerHTML = '';
            if (data.violations.length > 0) {
                data.violations.forEach(v => {
                    const item = document.createElement('div');
                    item.className = 'history-item-minimal';
                    item.innerHTML = `
                        <div class="flex-between mb-5">
                            <span class="fw-600 text-white">${v.violation_type} Violation</span>
                            <span class="fs-0-7 text-white-40">${new Date(v.created_at).toLocaleDateString()}</span>
                        </div>
                        <div class="fs-0-85 text-white-60 mb-5">${v.description}</div>
                        <div class="flex-between align-center">
                            <span class="badge badge-${v.status.toLowerCase()}">${v.status.replace('_', ' ')}</span>
                            <span class="fs-0-75 text-white-30">By: ${v.guard_name}</span>
                        </div>
                    `;
                    historyList.appendChild(item);
                });
            } else {
                historyList.innerHTML = '<div class="text-center p-30 text-white-30">No violation records.</div>';
            }
        });
}

function hideStudentModal() {
    const modal = document.getElementById('studentModal');
    if (modal) {
        modal.style.display = 'none';
        if (typeof toggleBodyScroll === 'function') toggleBodyScroll(false);
    }
}

function showEditStudentModal() {
    if (!currentStudent) return;
    const modal = document.getElementById('editStudentModal');
    if (modal) {
        document.getElementById('edit_user_id').value = currentStudent.id;
        document.getElementById('edit_full_name').value = currentStudent.full_name;
        document.getElementById('edit_student_id').value = currentStudent.student_id_number;
        document.getElementById('edit_course').value = currentStudent.course;
        document.getElementById('edit_year_level').value = currentStudent.year_level;
        document.getElementById('edit_section').value = currentStudent.section;
        
        modal.style.display = 'flex';
        // Keep scroll locked since studentModal is underneath
    }
}

function hideEditStudentModal() {
    const modal = document.getElementById('editStudentModal');
    if (modal) modal.style.display = 'none';
}

function confirmDeleteStudent() {
    if (!currentStudent) return;
    const modal = document.getElementById('deleteStudentModal');
    if (modal) {
        document.getElementById('delete_user_id').value = currentStudent.id;
        modal.style.display = 'flex';
    }
}

function hideDeleteStudentModal() {
    const modal = document.getElementById('deleteStudentModal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    // Guard Search
    const guardSearch = document.getElementById('guardSearch');
    if (guardSearch) {
        guardSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const cards = document.querySelectorAll('.guard-card');
            let found = 0;
            cards.forEach(card => {
                const name = card.querySelector('h3').innerText.toLowerCase();
                if (name.includes(query)) {
                    card.style.display = 'flex';
                    found++;
                } else {
                    card.style.display = 'none';
                }
            });
            document.getElementById('noGuardResults').style.display = found === 0 ? 'block' : 'none';
        });
    }

    // Student Search
    const studentSearch = document.getElementById('studentSearch');
    if (studentSearch) {
        studentSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const cards = document.querySelectorAll('.student-mini-card');
            const sections = document.querySelectorAll('.year-section');
            let totalFound = 0;

            sections.forEach(section => {
                let sectionFound = 0;
                const sectionCards = section.querySelectorAll('.student-mini-card');
                sectionCards.forEach(card => {
                    const name = card.querySelector('h4').innerText.toLowerCase();
                    const idNum = card.querySelector('p').innerText.toLowerCase();
                    if (name.includes(query) || idNum.includes(query)) {
                        card.style.display = 'flex';
                        sectionFound++;
                        totalFound++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                section.style.display = sectionFound > 0 ? 'block' : 'none';
            });
            document.getElementById('noResults').style.display = totalFound === 0 ? 'block' : 'none';
        });
    }
});
