/**
 * Shared Navigation and Modal Functionality
 */

// Helper to prevent background scroll
function toggleBodyScroll(prevent) {
    if (prevent) {
        document.body.classList.add('modal-open');
    } else {
        // Only remove if no other modals are visible
        const visibleModals = document.querySelectorAll('.modal-overlay[style*="display: flex"]');
        if (visibleModals.length <= 1) {
            document.body.classList.remove('modal-open');
        }
    }
}

// Logout Modal Logic
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

// Global Notifications
function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
        if (dropdown.classList.contains('active')) {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }
}

/**
 * Event Listeners Initialization
 */
document.addEventListener('DOMContentLoaded', () => {
    // Notification Toggles
    const notifLinks = ['osasNotifLink', 'navNotifLink'];
    notifLinks.forEach(id => {
        const link = document.getElementById(id);
        if (link) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                toggleNotifications();
            });
        }
    });

    // Logout Buttons
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

    // Modal Cancel Button
    const cancelLogout = document.getElementById('cancelLogout');
    if (cancelLogout) {
        cancelLogout.addEventListener('click', hideLogoutModal);
    }

    // Notification Item Clicks
    const notifItems = document.querySelectorAll('.notif-item');
    notifItems.forEach(item => {
        item.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) window.location.href = url;
        });
    });

    // Auto-scroll to specific violation if ID is in URL
    const urlParams = new URLSearchParams(window.location.search);
    const violationId = urlParams.get('violation_id');
    if (violationId) {
        const element = document.getElementById('violation-' + violationId);
        if (element) {
            // Give it a moment for potential tables to render/load
            setTimeout(() => {
                element.classList.add('highlight-record');
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Remove highlight after animation (matched to CSS)
                setTimeout(() => {
                    element.classList.remove('highlight-record');
                }, 3000);
            }, 100);
        }
    }

    // Initialize Toast
    initToast();
});

/**
 * Common UI Helpers
 */

// Auto-hide toast notifications
function initToast() {
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
}

// Close dropdowns/modals on outside click
window.addEventListener('click', function(event) {
    const logoutModal = document.getElementById('logoutModal');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifOverlay = document.getElementById('notifOverlay');
    const notifLinks = ['osasNotifLink', 'navNotifLink'];
    
    // Close Logout Modal
    if (event.target === logoutModal) hideLogoutModal();
    
    // Close Notifications if clicking on overlay
    if (event.target === notifOverlay) {
        toggleNotifications();
        return;
    }
    
    // Close Notifications Dropdown if clicking outside
    if (notifDropdown && notifDropdown.classList.contains('active')) {
        let clickedLink = false;
        notifLinks.forEach(id => {
            const link = document.getElementById(id);
            if (link && link.contains(event.target)) clickedLink = true;
        });

        if (!clickedLink && !notifDropdown.contains(event.target) && !notifOverlay.contains(event.target)) {
            toggleNotifications();
        }
    }
});
