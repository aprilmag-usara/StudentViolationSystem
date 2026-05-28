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

// Hamburger Menu Toggle for Student/Guard Nav
function toggleNavMenu() {
    const hamburger = document.getElementById('hamburgerNav');
    const navLinks = document.getElementById('navLinks');
    if (hamburger && navLinks) {
        navLinks.classList.toggle('active');
    }
}

// Hamburger Menu Toggle for OSAS Sidebar
function toggleOsasSidebar() {
    const sidebar = document.getElementById('osasSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }
}

// Close OSAS Sidebar when clicking overlay
function closeOsasSidebar() {
    const sidebar = document.getElementById('osasSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar && overlay) {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
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
                element.scrollIntoView({ behavior: 'smooth', block: 'center'});
                
                setTimeout(() => {
                    element.classList.remove('highlight-record');
                }, 3000);
            }
        }, 300);
    }

    initToast();

    // Hamburger menu event listeners
    const hamburgerNav = document.getElementById('hamburgerNav');
    if (hamburgerNav) {
        hamburgerNav.addEventListener('click', toggleNavMenu);
    }

    const hamburgerOsas = document.getElementById('hamburgerOsas');
    if (hamburgerOsas) {
        hamburgerOsas.addEventListener('click', toggleOsasSidebar);
    }

    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeOsasSidebar);
    }
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
