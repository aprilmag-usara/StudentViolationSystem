/**
 * Shared Navigation and Modal Functionality
 */

function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) modal.style.display = 'flex';
}

function hideLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) modal.style.display = 'none';
}

function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
}

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
    const notifLink = document.querySelector('a[onclick="toggleNotifications()"]');
    
    // Close Logout Modal
    if (event.target === logoutModal) hideLogoutModal();
    
    // Close Notifications Dropdown
    if (notifDropdown && !notifDropdown.contains(event.target) && (!notifLink || !notifLink.contains(event.target))) {
        notifDropdown.style.display = 'none';
    }
});

// Initialize on load
document.addEventListener('DOMContentLoaded', initToast);
