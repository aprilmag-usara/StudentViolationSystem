function toggleBodyScroll(prevent) {
    if (prevent) {
        document.body.classList.add('modal-open');
    } else {
        const visibleModals = document.querySelectorAll('.modal-overlay[style*="display: flex"]');
        if (visibleModals.length <= 1) {
            document.body.classList.remove('modal-open');
        }
    }
}

window.showToast = function(title, message, type = 'success', duration = 3000) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    toast.innerHTML = `
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
        <div class="toast-progress">
            <div class="toast-progress-fill"></div>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 100);

    const progressFill = toast.querySelector('.toast-progress-fill');
    progressFill.style.transition = `width ${duration}ms linear`;
    setTimeout(() => progressFill.style.width = '0%', 100);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 500);
    }, duration);
};

function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.display = 'flex';
        toggleBodyScroll(true);
    }
}

function hideLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.display = 'none';
        toggleBodyScroll(false);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const logoutBtns = ['osasLogoutBtn', 'navLogoutBtn'];
    logoutBtns.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                showLogoutModal();
            });
        }
    });

    const cancelLogout = document.getElementById('cancelLogout');
    if (cancelLogout) {
        cancelLogout.addEventListener('click', hideLogoutModal);
    }

    const urlParams = new URLSearchParams(window.location.search);
    const violationId = urlParams.get('violation_id');
    if (violationId) {
        setTimeout(() => {
            const element = document.getElementById('violation-' + violationId);
            if (element) {
                element.classList.add('highlight-record');
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                setTimeout(() => {
                    element.classList.remove('highlight-record');
                }, 3000);
            }
        }, 300);
    }

    initToast();
});

function initToast() {
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
}

window.addEventListener('click', function(event) {
    const logoutModal = document.getElementById('logoutModal');
    if (event.target === logoutModal) hideLogoutModal();
});
