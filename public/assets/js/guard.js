/**
 * Guard Dashboard Functionality
 * Includes QR/Barcode Scanner and Live Search
 */

document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
    initScanner();
    handleViolationHighlight();
});

function initLiveSearch() {
    const searchInput = document.getElementById('studentSearchInput');
    const resultsDropdown = document.getElementById('searchResultsDropdown');
    const searchForm = document.getElementById('searchForm');

    if (!searchInput || !resultsDropdown || !searchForm) return;

    let timeout = null;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(timeout);
        
        if (query.length < 2) {
            resultsDropdown.style.display = 'none';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`index.php?url=guard/search_ajax&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsDropdown.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach(student => {
                            const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(student.full_name)}&background=1b4332&color=fff&size=40`;
                            const photoPath = student.profile_photo && student.profile_photo !== 'default_profile.png' 
                                ? `assets/img/profiles/${student.profile_photo}` 
                                : avatarUrl;

                            const item = document.createElement('div');
                            item.className = 'search-result-item';
                            item.innerHTML = `
                                <img src="${photoPath}" class="result-avatar" onerror="this.src='${avatarUrl}'">
                                <div class="result-info">
                                    <h4>${student.full_name}</h4>
                                    <p>${student.student_id_number} | ${student.course}</p>
                                </div>
                            `;
                            
                            item.addEventListener('click', () => {
                                searchInput.value = student.student_id_number;
                                resultsDropdown.style.display = 'none';
                                searchForm.submit();
                            });
                            
                            resultsDropdown.appendChild(item);
                        });
                        resultsDropdown.style.display = 'block';
                    } else {
                        resultsDropdown.style.display = 'none';
                    }
                });
        }, 300);
    });

    window.addEventListener('click', function(e) {
        if (!searchForm.contains(e.target)) {
            resultsDropdown.style.display = 'none';
        }
    });
}

/**
 * Handles highlighting a specific violation row based on URL parameter.
 */
function handleViolationHighlight() {
    const urlParams = new URLSearchParams(window.location.search);
    const violationId = urlParams.get('violation_id');

    if (violationId) {
        const targetRow = document.getElementById(`violation-${violationId}`);
        if (targetRow) {
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetRow.classList.add('highlight-row');
            setTimeout(() => {
                targetRow.classList.remove('highlight-row');
            }, 4500); 
        }
    }
}

let html5QrCodeScanner = null;

function initScanner() {
    const startBtn = document.getElementById('startScanner');
    const stopBtn = document.getElementById('stopScanner');
    
    if (!startBtn || !stopBtn) return;
    
    startBtn.addEventListener('click', startScanner);
    stopBtn.addEventListener('click', stopScanner);
}

function startScanner() {
    const readerContainer = document.getElementById('reader');
    const startBtn = document.getElementById('startScanner');
    const stopBtn = document.getElementById('stopScanner');
    const scannerTitle = document.getElementById('scannerTitle');
    const scannerDesc = document.getElementById('scannerDesc');
    const scannerIcon = document.getElementById('scannerIcon');
    const searchForm = document.getElementById('searchForm');
    
    if (!readerContainer) return;
    
    scannerTitle.textContent = 'Scanning...';
    scannerDesc.textContent = 'Align the QR or barcode within the frame';
    scannerIcon.style.display = 'none';
    readerContainer.style.display = 'block';
    startBtn.style.display = 'none';
    stopBtn.style.display = 'inline-flex';
    
    html5QrCodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: { width: 200, height: 120 }, rememberLastUsedCamera: true },
        false
    );
    
    html5QrCodeScanner.render(onScanSuccess, onScanFailure);
}

function onScanSuccess(decodedText, decodedResult) {
    stopScanner(true);
    
    const searchInput = document.getElementById('studentSearchInput');
    const searchForm = document.getElementById('searchForm');
    
    if (searchInput && searchForm) {
        searchInput.value = decodedText.trim();
        searchForm.submit();
    }
}

function onScanFailure(error) {
    // Ignore scan failures - scanner will keep trying
}

function stopScanner(skipUI = false) {
    if (html5QrCodeScanner) {
        try {
            html5QrCodeScanner.clear().catch(err => console.log('Scanner clear error:', err));
        } catch (e) {
            console.log('Scanner stop error:', e);
        }
        html5QrCodeScanner = null;
    }
    
    if (!skipUI) {
        const readerContainer = document.getElementById('reader');
        const startBtn = document.getElementById('startScanner');
        const stopBtn = document.getElementById('stopScanner');
        const scannerTitle = document.getElementById('scannerTitle');
        const scannerDesc = document.getElementById('scannerDesc');
        const scannerIcon = document.getElementById('scannerIcon');
        
        if (readerContainer) {
            readerContainer.innerHTML = '';
            readerContainer.style.display = 'none';
        }
        
        if (scannerTitle) scannerTitle.textContent = 'Barcode Scanner';
        if (scannerDesc) scannerDesc.textContent = 'Position the student ID barcode within the frame';
        if (scannerIcon) scannerIcon.style.display = 'block';
        if (startBtn) startBtn.style.display = 'inline-flex';
        if (stopBtn) stopBtn.style.display = 'none';
    }
}

/**
 * Profile & UI Logic
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

function showEditGuardNameModal(id, name) {
    const modal = document.getElementById('editGuardNameModal');
    if (modal) {
        document.getElementById('editGuardId').value = id;
        document.getElementById('editGuardName').value = name;
        modal.style.display = 'flex';
    }
}

function hideEditGuardNameModal() {
    const modal = document.getElementById('editGuardNameModal');
    if (modal) modal.style.display = 'none';
}

// Close profile modals on outside click
window.addEventListener('click', function(event) {
    const modals = ['editModal', 'passwordModal', 'editGuardNameModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && event.target === modal) {
            modal.style.display = "none";
            toggleBodyScroll(false);
        }
    });
});
