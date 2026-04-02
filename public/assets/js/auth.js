/**
 * Auth Pages (Login/Signup) Logic
 */

function handleRoleChange() {
    const roleSelect = document.getElementById('role');
    if (!roleSelect) return;

    const role = roleSelect.value;
    const studentFields = document.getElementById('student-fields');
    const authPassContainer = document.getElementById('auth-pass-container');
    const submitBtn = document.getElementById('submit-btn');

    if (!studentFields || !authPassContainer || !submitBtn) return;

    // Select all student-specific inputs
    const studentInputs = studentFields.querySelectorAll('input');
    const authPassInput = document.getElementById('auth_pass');

    if (role === 'STUDENT') {
        studentFields.style.display = 'grid';
        authPassContainer.style.display = 'none';
        submitBtn.classList.add('ready');
        
        studentInputs.forEach(input => input.setAttribute('required', ''));
        if (authPassInput) authPassInput.removeAttribute('required');
    } else if (role === 'GUARD' || role === 'OSAS') {
        studentFields.style.display = 'none';
        authPassContainer.style.display = 'block';
        submitBtn.classList.remove('ready');
        
        studentInputs.forEach(input => input.removeAttribute('required'));
        if (authPassInput) authPassInput.setAttribute('required', '');
    } else {
        studentFields.style.display = 'none';
        authPassContainer.style.display = 'none';
        submitBtn.classList.remove('ready');
        
        studentInputs.forEach(input => input.removeAttribute('required'));
        if (authPassInput) authPassInput.removeAttribute('required');
    }
}

// Initialize on Signup Page
document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        handleRoleChange();
        roleSelect.addEventListener('change', handleRoleChange);
    }
});
