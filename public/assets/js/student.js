/**
 * Student Dashboard & Profile Functionality
 */

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

// Close profile modals on outside click
window.addEventListener('click', function(event) {
    const modals = ['editProfileModal', 'passwordModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && event.target === modal) modal.style.display = "none";
    });
});
