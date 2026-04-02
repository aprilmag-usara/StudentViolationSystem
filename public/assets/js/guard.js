/**
 * Guard Dashboard Functionality
 * Includes QR/Barcode Scanner and Live Search
 */

document.addEventListener('DOMContentLoaded', () => {
    initScanner();
    initLiveSearch();
});

/**
 * QR/Barcode Scanner Logic
 */
function initScanner() {
    const readerElement = document.getElementById('reader');
    if (!readerElement) return;

    const html5QrCode = new Html5Qrcode("reader");
    const startBtn = document.getElementById('startScanner');
    const stopBtn = document.getElementById('stopScanner');
    const scannerIcon = document.getElementById('scannerIcon');
    const scannerTitle = document.getElementById('scannerTitle');
    const scannerDesc = document.getElementById('scannerDesc');
    const searchInput = document.getElementById('studentSearchInput');
    const searchForm = document.getElementById('searchForm');

    // Explicitly support QR and common Barcode formats
    const formatsToSupport = [
        Html5QrcodeSupportedFormats.QR_CODE,
        Html5QrcodeSupportedFormats.UPC_A,
        Html5QrcodeSupportedFormats.UPC_E,
        Html5QrcodeSupportedFormats.UPC_EAN_EXTENSION,
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.EAN_8,
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.CODE_93,
        Html5QrcodeSupportedFormats.CODABAR,
        Html5QrcodeSupportedFormats.ITF
    ];

    const config = { 
        fps: 25, 
        qrbox: function(viewfinderWidth, viewfinderHeight) {
            let minEdgePercentage = 0.7; 
            let width = viewfinderWidth * minEdgePercentage;
            let height = width * 0.4; 
            return { width: width, height: height };
        },
        aspectRatio: 1.0,
        formatsToSupport: formatsToSupport,
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true
        }
    };

    const onScanSuccess = (decodedText, decodedResult) => {
        html5QrCode.stop().then(() => {
            readerElement.style.display = "none";
            startBtn.style.display = "inline-block";
            stopBtn.style.display = "none";
            scannerIcon.style.display = "block";
            
            searchInput.value = decodedText;
            searchForm.submit();
        }).catch(err => console.error("Error stopping scanner:", err));
    };

    startBtn.addEventListener('click', () => {
        readerElement.style.display = "block";
        startBtn.style.display = "none";
        stopBtn.style.display = "inline-block";
        scannerIcon.style.display = "none";
        scannerTitle.innerText = "Scanning...";
        scannerDesc.innerText = "Please hold the student ID steady.";

        html5QrCode.start(
            { facingMode: "environment" }, 
            config, 
            onScanSuccess
        ).catch(err => {
            console.error("Camera init error:", err);
            alert("Could not access camera. Please check permissions.");
            readerElement.style.display = "none";
            startBtn.style.display = "inline-block";
            stopBtn.style.display = "none";
            scannerIcon.style.display = "block";
            scannerTitle.innerText = "QR Code Scanner";
        });
    });

    stopBtn.addEventListener('click', () => {
        html5QrCode.stop().then(() => {
            readerElement.style.display = "none";
            startBtn.style.display = "inline-block";
            stopBtn.style.display = "none";
            scannerIcon.style.display = "block";
            scannerTitle.innerText = "QR Code Scanner";
            scannerDesc.innerText = "Position the student ID QR code within the frame";
        }).catch(err => console.error("Error stopping scanner:", err));
    });
}

/**
 * Live Search Functionality
 */
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

// Close profile modals on outside click
window.addEventListener('click', function(event) {
    const modals = ['editProfileModal', 'passwordModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && event.target === modal) modal.style.display = "none";
    });
});
