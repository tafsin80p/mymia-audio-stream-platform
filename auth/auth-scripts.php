<?php
/**
 * ========================================
 * NYMIA THEME - AUTHENTICATION SCRIPTS
 * ========================================
 * JavaScript for authentication pages
 * Handles: Tab switching, form validation
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>

<script>
/**
 * SWITCH TAB FUNCTION
 * -------------------
 * Switches between login and signup tabs
 * Handles tab content visibility and active states
 */
function switchTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.nymia-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.nymia-tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    const targetContent = document.getElementById(tab + '-form');
    if (targetContent) {
        targetContent.classList.add('active');
    }
    
    // Add active class to clicked tab
    if (event && event.target) {
        event.target.classList.add('active');
    }
} // END: switchTab()

// ========================================
// ACCOUNT TYPE SELECTOR
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const kycSection = document.getElementById('nymia-signup-kyc-section');
    const kycIdType = document.getElementById('reg_kyc_id_type');
    const kycIdNumber = document.getElementById('reg_kyc_id_number');
    const kycDocument = document.getElementById('reg_kyc_document');
    
    // Function to toggle KYC section visibility
    function toggleKycSection() {
        const accountTypeRadios = document.querySelectorAll('input[name="account_type"]');
        let selectedType = 'user';
        
        accountTypeRadios.forEach(radio => {
            if (radio.checked) {
                selectedType = radio.value;
            }
        });
        
        if (kycSection) {
            if (selectedType === 'creator') {
                kycSection.style.display = 'block';
                // Make KYC fields required
                if (kycIdType) kycIdType.setAttribute('required', 'required');
                if (kycIdNumber) kycIdNumber.setAttribute('required', 'required');
            } else {
                kycSection.style.display = 'none';
                // Remove required attribute
                if (kycIdType) kycIdType.removeAttribute('required');
                if (kycIdNumber) kycIdNumber.removeAttribute('required');
            }
        }
    }
    
    // Handle account type radio button selection
    const accountTypeRadios = document.querySelectorAll('input[name="account_type"]');
    accountTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected class from all cards
            document.querySelectorAll('.nymia-account-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to selected card
            if (this.checked) {
                const card = this.closest('.nymia-account-type-option').querySelector('.nymia-account-type-card');
                if (card) {
                    card.classList.add('selected');
                }
            }
            
            // Toggle KYC section
            toggleKycSection();
        });
        
        // Set initial state for checked radio
        if (radio.checked) {
            const card = radio.closest('.nymia-account-type-option').querySelector('.nymia-account-type-card');
            if (card) {
                card.classList.add('selected');
            }
        }
    });
    
    // Initial toggle on page load
    toggleKycSection();
    
    // Check URL parameter for creator signup
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('signup') === 'creator') {
        // Auto-select creator account type
        setTimeout(() => {
            const creatorRadio = document.querySelector('input[name="account_type"][value="creator"]');
            if (creatorRadio) {
                creatorRadio.checked = true;
                creatorRadio.dispatchEvent(new Event('change', { bubbles: true }));
                // Scroll to account type section
                const accountTypeSection = creatorRadio.closest('.nymia-form-group');
                if (accountTypeSection) {
                    accountTypeSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }, 300);
    }
    
    // ========================================
    // KYC CAMERA CAPTURE FUNCTIONALITY
    // ========================================
    let regKycStream = null;
    const $regKycVideo = document.getElementById('reg-kyc-video');
    const $regKycCanvas = document.getElementById('reg-kyc-canvas');
    const $regKycCamera = document.getElementById('reg-kyc-camera');
    const $regStartCameraBtn = document.getElementById('reg-kyc-start-camera');
    const $regStopCameraBtn = document.getElementById('reg-kyc-stop-camera');
    const $regCaptureBtn = document.getElementById('reg-kyc-capture');
    const $regPhotoPreview = document.getElementById('reg-kyc-photo-preview');
    const $regPhotoImg = $regPhotoPreview ? $regPhotoPreview.querySelector('img') : null;
    const $regPhotoDataInput = document.getElementById('reg-kyc-photo-data');
    const $regCameraInstruction = document.getElementById('reg-kyc-camera-instruction');
    const $regRetakeBtn = document.getElementById('reg-kyc-retake');
    
    function stopRegKycCamera() {
        if (regKycStream) {
            regKycStream.getTracks().forEach(track => track.stop());
            regKycStream = null;
        }
        if ($regKycVideo) {
            $regKycVideo.srcObject = null;
        }
    }
    
    async function startRegKycCamera() {
        // Check if browser supports getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            // Fallback for older browsers
            const getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
            if (!getUserMedia) {
                alert('<?php echo esc_js(__('Camera access is not supported in this browser. Please use a modern browser like Chrome, Firefox, or Safari. You can still upload a document file instead.', 'nymia')); ?>');
                // Highlight file upload option
                const docUpload = document.getElementById('reg_kyc_document');
                if (docUpload) {
                    docUpload.style.border = '2px solid #BF4C1A';
                    docUpload.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
        }
        
        // Check if running on HTTPS or localhost
        const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        if (!isSecure) {
            alert('<?php echo esc_js(__('Camera access requires HTTPS connection. Please access this site via HTTPS. You can still upload a document file instead.', 'nymia')); ?>');
            // Highlight file upload option
            const docUpload = document.getElementById('reg_kyc_document');
            if (docUpload) {
                docUpload.style.border = '2px solid #BF4C1A';
                docUpload.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        
        // Try to enumerate devices first (optional, for better UX)
        let hasVideoDevices = false;
        try {
            if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                const devices = await navigator.mediaDevices.enumerateDevices();
                hasVideoDevices = devices.some(device => device.kind === 'videoinput');
            }
        } catch (enumError) {
            console.log('Could not enumerate devices:', enumError);
            // Continue anyway, will try getUserMedia
        }
        
        try {
            // Try to get camera with preferred facing mode (back camera for mobile)
            let constraints = { 
                video: { 
                    facingMode: { ideal: 'environment' } // Prefer back camera
                } 
            };
            
            // First try with preferred camera
            try {
                regKycStream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (firstError) {
                console.log('Back camera not available, trying front camera...', firstError);
                // Fallback to front camera or any available camera
                constraints = { video: true };
                regKycStream = await navigator.mediaDevices.getUserMedia(constraints);
            }
            
            if ($regKycVideo && regKycStream) {
                $regKycVideo.srcObject = regKycStream;
                // Wait for video to be ready
                $regKycVideo.onloadedmetadata = function() {
                    if ($regKycVideo) {
                        $regKycVideo.play().catch(err => {
                            console.error('Error playing video:', err);
                        });
                    }
                };
                // Also try to play immediately
                $regKycVideo.play().catch(err => {
                    console.log('Video play attempt:', err);
                });
            }
            
            if ($regKycCamera) $regKycCamera.style.display = 'block';
            if ($regCameraInstruction) $regCameraInstruction.style.display = 'block';
            if ($regStopCameraBtn) $regStopCameraBtn.style.display = 'inline-block';
            if ($regStartCameraBtn) $regStartCameraBtn.style.display = 'none';
            if ($regPhotoPreview) $regPhotoPreview.style.display = 'none';
            if ($regPhotoImg) $regPhotoImg.src = '';
            if ($regPhotoDataInput) $regPhotoDataInput.value = '';
            
        } catch (error) {
            console.error('Camera access error:', error);
            stopRegKycCamera();
            
            let errorMessage = '<?php echo esc_js(__('Unable to access the camera.', 'nymia')); ?>';
            
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                errorMessage = '<?php echo esc_js(__('Camera permission denied. Please allow camera access in your browser settings and try again.', 'nymia')); ?>';
            } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                errorMessage = '<?php echo esc_js(__('No camera found. Please connect a camera device or use the file upload option above to upload your ID document.', 'nymia')); ?>';
                // Highlight file upload option when camera not found
                const docUpload = document.getElementById('reg_kyc_document');
                if (docUpload) {
                    docUpload.style.border = '2px solid #BF4C1A';
                    docUpload.style.boxShadow = '0 0 10px rgba(191, 76, 26, 0.5)';
                    setTimeout(() => {
                        docUpload.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);
                    // Remove highlight after 5 seconds
                    setTimeout(() => {
                        docUpload.style.border = '';
                        docUpload.style.boxShadow = '';
                    }, 5000);
                }
            } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                errorMessage = '<?php echo esc_js(__('Camera is being used by another application. Please close other apps using the camera and try again.', 'nymia')); ?>';
            } else if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
                errorMessage = '<?php echo esc_js(__('Camera constraints not satisfied. Trying with default settings...', 'nymia')); ?>';
                // Try again with basic constraints
                try {
                    regKycStream = await navigator.mediaDevices.getUserMedia({ video: true });
                    if ($regKycVideo) {
                        $regKycVideo.srcObject = regKycStream;
                        $regKycVideo.play().catch(err => {
                            console.log('Video play attempt:', err);
                        });
                    }
                    if ($regKycCamera) $regKycCamera.style.display = 'block';
                    if ($regCameraInstruction) $regCameraInstruction.style.display = 'block';
                    if ($regStopCameraBtn) $regStopCameraBtn.style.display = 'inline-block';
                    if ($regStartCameraBtn) $regStartCameraBtn.style.display = 'none';
                    if ($regPhotoPreview) $regPhotoPreview.style.display = 'none';
                    if ($regPhotoImg) $regPhotoImg.src = '';
                    if ($regPhotoDataInput) $regPhotoDataInput.value = '';
                    return; // Success with fallback
                } catch (fallbackError) {
                    console.error('Fallback camera access also failed:', fallbackError);
                    errorMessage = '<?php echo esc_js(__('Unable to access camera. Please check your browser settings and camera permissions.', 'nymia')); ?>';
                }
            }
            
            alert(errorMessage);
        }
    }
    
    if ($regStartCameraBtn) {
        $regStartCameraBtn.addEventListener('click', startRegKycCamera);
    }
    
    if ($regStopCameraBtn) {
        $regStopCameraBtn.addEventListener('click', function() {
            stopRegKycCamera();
            if ($regKycCamera) $regKycCamera.style.display = 'none';
            if ($regCameraInstruction) $regCameraInstruction.style.display = 'none';
            if ($regStopCameraBtn) $regStopCameraBtn.style.display = 'none';
            if ($regStartCameraBtn) $regStartCameraBtn.style.display = 'inline-block';
        });
    }
    
    if ($regRetakeBtn) {
        $regRetakeBtn.addEventListener('click', function() {
            if ($regPhotoPreview) $regPhotoPreview.style.display = 'none';
            if ($regPhotoImg) $regPhotoImg.src = '';
            if ($regPhotoDataInput) $regPhotoDataInput.value = '';
            startRegKycCamera();
        });
    }
    
    if ($regCaptureBtn) {
        $regCaptureBtn.addEventListener('click', function() {
            if (!regKycStream || !$regKycVideo) {
                alert('<?php echo esc_js(__('Camera not ready. Please enable camera first.', 'nymia')); ?>');
                return;
            }
            
            const video = $regKycVideo;
            const canvas = $regKycCanvas;
            if (!canvas || !video.videoWidth) {
                alert('<?php echo esc_js(__('Camera stream not ready yet. Try again in a moment.', 'nymia')); ?>');
                return;
            }
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            
            // Save context state
            ctx.save();
            
            // Flip horizontally to match the mirrored video preview
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Restore context state
            ctx.restore();
            
            const dataURL = canvas.toDataURL('image/png');
            if ($regPhotoDataInput) $regPhotoDataInput.value = dataURL;
            if ($regPhotoImg) {
                $regPhotoImg.src = dataURL;
                $regPhotoImg.style.transform = 'scaleX(1)'; // Normal orientation for preview
            }
            if ($regPhotoPreview) $regPhotoPreview.style.display = 'block';
            
            stopRegKycCamera();
            if ($regKycCamera) $regKycCamera.style.display = 'none';
            if ($regCameraInstruction) $regCameraInstruction.style.display = 'none';
            if ($regStopCameraBtn) $regStopCameraBtn.style.display = 'none';
            if ($regStartCameraBtn) $regStartCameraBtn.style.display = 'inline-block';
        });
    }
    
    // ID Number validation - only allow numbers
    if (kycIdNumber) {
        const kycIdNumberError = document.getElementById('reg_kyc_id_number_error');
        kycIdNumber.addEventListener('input', function() {
            const value = this.value.trim();
            if (value && !/^[0-9]+$/.test(value)) {
                if (kycIdNumberError) kycIdNumberError.style.display = 'block';
                this.setCustomValidity('<?php echo esc_js(__('ID Number must contain only numbers', 'nymia')); ?>');
            } else {
                if (kycIdNumberError) kycIdNumberError.style.display = 'none';
                this.setCustomValidity('');
            }
        });
    }
});

// ========================================
// FORM VALIDATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerform');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('reg_password').value;
            const confirmPassword = document.getElementById('reg_confirm_password').value;
            const firstName = document.getElementById('reg_first_name').value;
            const lastName = document.getElementById('reg_last_name').value;
            const country = document.getElementById('reg_country').value;
            const city = document.getElementById('reg_city').value;
            const dateOfBirth = document.getElementById('reg_date_of_birth').value;
            const dateOfBirthError = document.getElementById('dateOfBirthError');
            
            // Validate first name
            if (!firstName || firstName.trim() === '') {
                e.preventDefault();
                alert('Please enter your first name!');
                return false;
            }
            
            // Validate last name
            if (!lastName || lastName.trim() === '') {
                e.preventDefault();
                alert('Please enter your last name!');
                return false;
            }
            
            // Validate date of birth and age (18+)
            if (!dateOfBirth || dateOfBirth === '') {
                e.preventDefault();
                dateOfBirthError.textContent = 'Please enter your date of birth!';
                dateOfBirthError.style.display = 'block';
                document.getElementById('reg_date_of_birth').focus();
                return false;
            }
            
            // Calculate age
            const birthDate = new Date(dateOfBirth);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            // Adjust age if birthday hasn't occurred this year
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            // Check if user is 18 or older
            if (age < 18) {
                e.preventDefault();
                dateOfBirthError.textContent = 'You must be at least 18 years old to register!';
                dateOfBirthError.style.display = 'block';
                document.getElementById('reg_date_of_birth').focus();
                return false;
            }
            
            // Clear error if validation passes
            dateOfBirthError.style.display = 'none';
            
            // Validate country
            if (!country || country === '') {
                e.preventDefault();
                alert('Please select your country!');
                return false;
            }
            
            // Validate city
            if (!city || city === '') {
                e.preventDefault();
                alert('Please select your city!');
                return false;
            }
            
            // Check if password exists
            if (!password || password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            // Validate KYC fields if creator is selected
            const accountType = document.querySelector('input[name="account_type"]:checked');
            if (accountType && accountType.value === 'creator') {
                const kycIdType = document.getElementById('reg_kyc_id_type');
                const kycIdNumber = document.getElementById('reg_kyc_id_number');
                const kycDocument = document.getElementById('reg_kyc_document');
                const kycPhotoData = document.getElementById('reg-kyc-photo-data');
                
                if (!kycIdType || !kycIdType.value || kycIdType.value === '') {
                    e.preventDefault();
                    alert('<?php echo esc_js(__('Please select an ID Type for verification.', 'nymia')); ?>');
                    if (kycIdType) kycIdType.focus();
                    return false;
                }
                
                if (!kycIdNumber || !kycIdNumber.value || kycIdNumber.value.trim() === '') {
                    e.preventDefault();
                    alert('<?php echo esc_js(__('Please enter your ID Number.', 'nymia')); ?>');
                    if (kycIdNumber) kycIdNumber.focus();
                    return false;
                }
                
                // Validate ID Number format
                if (kycIdNumber && !/^[0-9]+$/.test(kycIdNumber.value.trim())) {
                    e.preventDefault();
                    alert('<?php echo esc_js(__('ID Number must contain only numbers.', 'nymia')); ?>');
                    if (kycIdNumber) kycIdNumber.focus();
                    return false;
                }
                
                // Check if either document upload or photo capture is provided
                const hasDocument = kycDocument && kycDocument.files && kycDocument.files.length > 0;
                const hasPhoto = kycPhotoData && kycPhotoData.value && kycPhotoData.value.trim() !== '';
                
                if (!hasDocument && !hasPhoto) {
                    e.preventDefault();
                    alert('<?php echo esc_js(__('Please upload an ID document or capture a photo for verification.', 'nymia')); ?>');
                    return false;
                }
            }
        });
        
        // Real-time age validation on date change
        const dateOfBirthInput = document.getElementById('reg_date_of_birth');
        const dateOfBirthError = document.getElementById('dateOfBirthError');
        
        if (dateOfBirthInput) {
            dateOfBirthInput.addEventListener('change', function() {
                const dateOfBirth = this.value;
                
                if (!dateOfBirth) {
                    dateOfBirthError.style.display = 'none';
                    return;
                }
                
                // Calculate age
                const birthDate = new Date(dateOfBirth);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                // Adjust age if birthday hasn't occurred this year
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                // Check if user is 18 or older
                if (age < 18) {
                    dateOfBirthError.textContent = 'You must be at least 18 years old to register!';
                    dateOfBirthError.style.display = 'block';
                    this.setCustomValidity('You must be at least 18 years old to register!');
                } else {
                    dateOfBirthError.style.display = 'none';
                    this.setCustomValidity('');
                }
            });
        }
    }

    // Password strength meter
    const passwordInput = document.getElementById('reg_password');
    const strengthBar = document.getElementById('passwordStrengthFill');
    if (passwordInput && strengthBar) {
        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let score = 0;
            if (val.length >= 6) score += 25;
            if (/[A-Z]/.test(val)) score += 25;
            if (/[0-9]/.test(val)) score += 25;
            if (/[^A-Za-z0-9]/.test(val)) score += 25;
            strengthBar.style.width = score + '%';
            if (score < 50) {
                strengthBar.style.background = '#dc3545';
            } else if (score < 75) {
                strengthBar.style.background = '#fd7e14';
            } else {
                strengthBar.style.background = '#28a745';
            }
        });
    }

    // Show/hide password toggles for signup form
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passField = document.getElementById('reg_password');
    const confirmField = document.getElementById('reg_confirm_password');
    if (togglePassword && passField) {
        togglePassword.addEventListener('click', function() {
            const isText = passField.getAttribute('type') === 'text';
            passField.setAttribute('type', isText ? 'password' : 'text');
            togglePassword.textContent = isText ? 'Show' : 'Hide';
        });
    }
    if (toggleConfirmPassword && confirmField) {
        toggleConfirmPassword.addEventListener('click', function() {
            const isText = confirmField.getAttribute('type') === 'text';
            confirmField.setAttribute('type', isText ? 'password' : 'text');
            toggleConfirmPassword.textContent = isText ? 'Show' : 'Hide';
        });
    }
    
    // Show/hide password toggle for login form
    const toggleLoginPassword = document.getElementById('toggle-password');
    const loginPasswordField = document.getElementById('user_pass');
    if (toggleLoginPassword && loginPasswordField) {
        toggleLoginPassword.addEventListener('click', function() {
            const isPassword = loginPasswordField.getAttribute('type') === 'password';
            loginPasswordField.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Toggle eye icons
            const eyeOpen = toggleLoginPassword.querySelector('.nymia-eye-open');
            const eyeClosed = toggleLoginPassword.querySelector('.nymia-eye-closed');
            
            if (isPassword) {
                // Show password - show closed eye, hide open eye
                if (eyeOpen) eyeOpen.style.display = 'none';
                if (eyeClosed) eyeClosed.style.display = 'block';
            } else {
                // Hide password - show open eye, hide closed eye
                if (eyeOpen) eyeOpen.style.display = 'block';
                if (eyeClosed) eyeClosed.style.display = 'none';
            }
        });
    }

    // ========================================
    // COUNTRY-CITY AUTO-POPULATION
    // ========================================
    const countrySelect = document.getElementById('reg_country');
    const citySelect = document.getElementById('reg_city');
    
    
    // Comprehensive cities database by country
    const citiesByCountry = {
        'United States': ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville', 'San Francisco', 'Indianapolis', 'Columbus', 'Fort Worth', 'Charlotte', 'Seattle', 'Denver', 'Washington'],
        'United Kingdom': ['London', 'Manchester', 'Birmingham', 'Glasgow', 'Liverpool', 'Leeds', 'Edinburgh', 'Bristol', 'Cardiff', 'Belfast', 'Newcastle', 'Sheffield', 'Leicester', 'Coventry', 'Nottingham', 'Southampton', 'Portsmouth', 'Brighton', 'Reading', 'Northampton'],
        'Canada': ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Kitchener', 'London', 'Victoria', 'Halifax', 'Oshawa', 'Windsor', 'Saskatoon', 'Regina', 'Sherbrooke', 'Kelowna', 'Barrie'],
        'Australia': ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast', 'Newcastle', 'Canberra', 'Sunshine Coast', 'Wollongong', 'Hobart', 'Geelong', 'Townsville', 'Cairns', 'Darwin', 'Toowoomba', 'Ballarat', 'Bendigo', 'Albury', 'Launceston'],
        'Germany': ['Berlin', 'Munich', 'Hamburg', 'Cologne', 'Frankfurt', 'Stuttgart', 'Düsseldorf', 'Dortmund', 'Essen', 'Leipzig', 'Bremen', 'Dresden', 'Hannover', 'Nuremberg', 'Duisburg', 'Bochum', 'Wuppertal', 'Bielefeld', 'Bonn', 'Münster'],
        'France': ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille', 'Rennes', 'Reims', 'Le Havre', 'Saint-Étienne', 'Toulon', 'Grenoble', 'Dijon', 'Angers', 'Nîmes', 'Villeurbanne'],
        'Italy': ['Rome', 'Milan', 'Naples', 'Turin', 'Palermo', 'Genoa', 'Bologna', 'Florence', 'Bari', 'Catania', 'Venice', 'Verona', 'Messina', 'Padua', 'Trieste', 'Brescia', 'Parma', 'Taranto', 'Prato', 'Modena'],
        'Spain': ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Zaragoza', 'Málaga', 'Murcia', 'Palma', 'Las Palmas', 'Bilbao', 'Alicante', 'Córdoba', 'Valladolid', 'Vigo', 'Gijón', 'Hospitalet', 'Granada', 'Vitoria', 'A Coruña', 'Elche'],
        'India': ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata', 'Pune', 'Ahmedabad', 'Jaipur', 'Surat', 'Lucknow', 'Kanpur', 'Nagpur', 'Indore', 'Thane', 'Bhopal', 'Visakhapatnam', 'Patna', 'Vadodara', 'Ghaziabad'],
        'China': ['Shanghai', 'Beijing', 'Guangzhou', 'Shenzhen', 'Chengdu', 'Hangzhou', 'Wuhan', 'Xi\'an', 'Nanjing', 'Tianjin', 'Suzhou', 'Chongqing', 'Dongguan', 'Foshan', 'Qingdao', 'Dalian', 'Shenyang', 'Xiamen', 'Jinan', 'Zhengzhou'],
        'Japan': ['Tokyo', 'Yokohama', 'Osaka', 'Nagoya', 'Sapporo', 'Fukuoka', 'Kobe', 'Kawasaki', 'Kyoto', 'Saitama', 'Hiroshima', 'Sendai', 'Chiba', 'Kitakyushu', 'Sakai', 'Niigata', 'Hamamatsu', 'Shizuoka', 'Okayama', 'Kumamoto'],
        'Brazil': ['São Paulo', 'Rio de Janeiro', 'Brasília', 'Salvador', 'Fortaleza', 'Belo Horizonte', 'Manaus', 'Curitiba', 'Recife', 'Porto Alegre', 'Belém', 'Goiânia', 'Guarulhos', 'Campinas', 'São Luís', 'São Gonçalo', 'Maceió', 'Duque de Caxias', 'Natal', 'Teresina'],
        'Mexico': ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana', 'León', 'Juárez', 'Torreón', 'Querétaro', 'San Luis Potosí', 'Mérida', 'Mexicali', 'Aguascalientes', 'Tampico', 'Culiacán', 'Acapulco', 'Cancún', 'Chihuahua', 'Morelia', 'Saltillo'],
        'Argentina': ['Buenos Aires', 'Córdoba', 'Rosario', 'Mendoza', 'Tucumán', 'La Plata', 'Mar del Plata', 'Salta', 'Santa Fe', 'San Juan', 'Resistencia', 'Santiago del Estero', 'Corrientes', 'Bahía Blanca', 'Posadas', 'Paraná', 'Neuquén', 'Formosa', 'San Salvador de Jujuy', 'La Rioja'],
        'South Africa': ['Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth', 'Bloemfontein', 'East London', 'Kimberley', 'Polokwane', 'Nelspruit', 'Rustenburg', 'Welkom', 'Vereeniging', 'Pietermaritzburg', 'Benoni', 'Tembisa', 'Vanderbijlpark', 'Boksburg', 'Klerksdorp', 'Uitenhage'],
        'Nigeria': ['Lagos', 'Kano', 'Ibadan', 'Abuja', 'Port Harcourt', 'Benin City', 'Kaduna', 'Maiduguri', 'Zaria', 'Aba', 'Jos', 'Ilorin', 'Oyo', 'Abeokuta', 'Onitsha', 'Warri', 'Enugu', 'Calabar', 'Akure', 'Osogbo'],
        'Kenya': ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Malindi', 'Kitale', 'Garissa', 'Kakamega', 'Nyeri', 'Meru', 'Machakos', 'Embu', 'Narok', 'Kericho', 'Bungoma', 'Busia', 'Homa Bay', 'Kilifi'],
        'Saudi Arabia': ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar', 'Taif', 'Abha', 'Tabuk', 'Buraydah', 'Khamis Mushait', 'Hail', 'Najran', 'Jizan', 'Al Jubail', 'Yanbu', 'Abqaiq', 'Dhahran', 'Al Khafji', 'Arar'],
        'United Arab Emirates': ['Dubai', 'Abu Dhabi', 'Sharjah', 'Al Ain', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Umm Al Quwain', 'Khor Fakkan', 'Dibba Al-Fujairah'],
        'Turkey': ['Istanbul', 'Ankara', 'Izmir', 'Bursa', 'Antalya', 'Adana', 'Gaziantep', 'Konya', 'Kayseri', 'Mersin', 'Eskişehir', 'Diyarbakır', 'Samsun', 'Şanlıurfa', 'Malatya', 'Erzurum', 'Van', 'Batman', 'Elazığ', 'Denizli'],
        'Thailand': ['Bangkok', 'Chiang Mai', 'Pattaya', 'Phuket', 'Hat Yai', 'Nakhon Ratchasima', 'Udon Thani', 'Khon Kaen', 'Surat Thani', 'Chon Buri', 'Nakhon Si Thammarat', 'Rayong', 'Lampang', 'Trang', 'Phitsanulok', 'Songkhla', 'Kanchanaburi', 'Ratchaburi', 'Sakon Nakhon', 'Nakhon Sawan'],
        'Malaysia': ['Kuala Lumpur', 'George Town', 'Ipoh', 'Johor Bahru', 'Malacca', 'Kota Kinabalu', 'Kuching', 'Shah Alam', 'Klang', 'Subang Jaya', 'Petaling Jaya', 'Kota Bharu', 'Seremban', 'Kuantan', 'Alor Setar', 'Miri', 'Taiping', 'Sandakan', 'Kuala Terengganu', 'Sibu'],
        'Singapore': ['Singapore'],
        'Philippines': ['Manila', 'Quezon City', 'Caloocan', 'Davao', 'Cebu', 'Zamboanga', 'Antipolo', 'Pasig', 'Taguig', 'Cagayan de Oro', 'Parañaque', 'Dasmariñas', 'Valenzuela', 'Las Piñas', 'Makati', 'Bacolod', 'General Santos', 'Muntinlupa', 'San Jose del Monte', 'Calamba'],
        'Indonesia': ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Palembang', 'Makassar', 'Tangerang', 'Depok', 'Bekasi', 'Batam', 'Pekanbaru', 'Padang', 'Denpasar', 'Malang', 'Surakarta', 'Pontianak', 'Manado', 'Jambi', 'Cimahi'],
        'Vietnam': ['Ho Chi Minh City', 'Hanoi', 'Da Nang', 'Haiphong', 'Can Tho', 'Bien Hoa', 'Nha Trang', 'Hue', 'Vung Tau', 'Quy Nhon', 'Rach Gia', 'Long Xuyen', 'My Tho', 'Cam Ranh', 'Thai Nguyen', 'Pleiku', 'Vinh', 'Nam Dinh', 'Buon Ma Thuot', 'Vinh Long'],
        'New Zealand': ['Auckland', 'Wellington', 'Christchurch', 'Hamilton', 'Tauranga', 'Napier', 'Palmerston North', 'Dunedin', 'Rotorua', 'New Plymouth', 'Whangarei', 'Invercargill', 'Nelson', 'Hastings', 'Gisborne', 'Timaru', 'Blenheim', 'Whanganui', 'Masterton', 'Levin'],
        'Netherlands': ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven', 'Groningen', 'Tilburg', 'Almere', 'Breda', 'Nijmegen', 'Enschede', 'Haarlem', 'Arnhem', 'Zaanstad', 'Amersfoort', 'Apeldoorn', 'Hoofddorp', 'Maastricht', 'Leiden', 'Dordrecht'],
        'Belgium': ['Brussels', 'Antwerp', 'Ghent', 'Charleroi', 'Liège', 'Bruges', 'Namur', 'Leuven', 'Mons', 'Aalst', 'Mechelen', 'La Louvière', 'Kortrijk', 'Hasselt', 'Ostend', 'Sint-Niklaas', 'Tournai', 'Genk', 'Seraing', 'Roeselare'],
        'Switzerland': ['Zurich', 'Geneva', 'Basel', 'Bern', 'Lausanne', 'St. Gallen', 'Lucerne', 'Lugano', 'Biel', 'Thun', 'Köniz', 'La Chaux-de-Fonds', 'Schaffhausen', 'Fribourg', 'Chur', 'Neuchâtel', 'Vernier', 'Uster', 'Sion', 'Lancy'],
        'Sweden': ['Stockholm', 'Gothenburg', 'Malmö', 'Uppsala', 'Västerås', 'Örebro', 'Linköping', 'Helsingborg', 'Jönköping', 'Norrköping', 'Lund', 'Umeå', 'Gävle', 'Borås', 'Södertälje', 'Eskilstuna', 'Halmstad', 'Växjö', 'Karlstad', 'Sundsvall'],
        'Norway': ['Oslo', 'Bergen', 'Trondheim', 'Stavanger', 'Bærum', 'Kristiansand', 'Fredrikstad', 'Tromsø', 'Sandnes', 'Asker', 'Skien', 'Ålesund', 'Sandefjord', 'Haugesund', 'Tønsberg', 'Moss', 'Porsgrunn', 'Bodø', 'Arendal', 'Hamar'],
        'Denmark': ['Copenhagen', 'Aarhus', 'Odense', 'Aalborg', 'Esbjerg', 'Randers', 'Kolding', 'Horsens', 'Vejle', 'Roskilde', 'Herning', 'Helsingør', 'Hørsholm', 'Silkeborg', 'Næstved', 'Fredericia', 'Viborg', 'Køge', 'Holstebro', 'Taastrup'],
        'Poland': ['Warsaw', 'Kraków', 'Łódź', 'Wrocław', 'Poznań', 'Gdańsk', 'Szczecin', 'Bydgoszcz', 'Lublin', 'Katowice', 'Białystok', 'Gdynia', 'Częstochowa', 'Radom', 'Sosnowiec', 'Toruń', 'Kielce', 'Gliwice', 'Zabrze', 'Bytom'],
        'Portugal': ['Lisbon', 'Porto', 'Vila Nova de Gaia', 'Amadora', 'Braga', 'Funchal', 'Coimbra', 'Setúbal', 'Almada', 'Agualva-Cacém', 'Queluz', 'Rio de Mouro', 'Barreiro', 'Aveiro', 'Corroios', 'Leiria', 'Faro', 'Évora', 'Viseu', 'Guimarães'],
        'Greece': ['Athens', 'Thessaloniki', 'Patras', 'Heraklion', 'Larissa', 'Volos', 'Rhodes', 'Ioannina', 'Chania', 'Kavala', 'Kalamata', 'Agrinio', 'Chalcis', 'Serres', 'Alexandroupoli', 'Xanthi', 'Katerini', 'Kalamata', 'Trikala', 'Lamia'],
        'Ireland': ['Dublin', 'Cork', 'Limerick', 'Galway', 'Waterford', 'Drogheda', 'Dundalk', 'Swords', 'Bray', 'Navan', 'Ennis', 'Kilkenny', 'Carlow', 'Tralee', 'Newbridge', 'Naas', 'Athlone', 'Portlaoise', 'Mullingar', 'Wexford'],
        'Russia': ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan', 'Nizhny Novgorod', 'Chelyabinsk', 'Samara', 'Omsk', 'Rostov-on-Don', 'Ufa', 'Krasnoyarsk', 'Voronezh', 'Perm', 'Volgograd', 'Krasnodar', 'Saratov', 'Tyumen', 'Tolyatti', 'Izhevsk'],
        'South Korea': ['Seoul', 'Busan', 'Incheon', 'Daegu', 'Daejeon', 'Gwangju', 'Suwon', 'Ulsan', 'Changwon', 'Goyang', 'Seongnam', 'Bucheon', 'Ansan', 'Anyang', 'Jeonju', 'Cheonan', 'Namyangju', 'Hwaseong', 'Gimhae', 'Pyeongtaek'],
        'Egypt': ['Cairo', 'Alexandria', 'Giza', 'Shubra El Kheima', 'Port Said', 'Suez', 'Luxor', 'Aswan', 'Asyut', 'Ismailia', 'Faiyum', 'Zagazig', 'Damietta', 'Mansoura', 'Tanta', 'Beni Suef', 'Qena', 'Sohag', 'Hurghada', 'Minya'],
        'Israel': ['Jerusalem', 'Tel Aviv', 'Haifa', 'Rishon LeZion', 'Petah Tikva', 'Ashdod', 'Netanya', 'Beer Sheva', 'Holon', 'Bnei Brak', 'Ramat Gan', 'Rehovot', 'Bat Yam', 'Ashkelon', 'Kfar Saba', 'Herzliya', 'Hadera', 'Modiin', 'Nazareth', 'Lod']
    };
    
    if (countrySelect && citySelect) {
        countrySelect.addEventListener('change', function() {
            const selectedCountry = this.value;
            citySelect.innerHTML = '<option value="">Select a city</option>';
            
            if (selectedCountry && citiesByCountry[selectedCountry]) {
                const cities = citiesByCountry[selectedCountry];
                cities.forEach(function(city) {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
                citySelect.disabled = false;
            } else {
                citySelect.disabled = true;
                citySelect.innerHTML = '<option value="">Select country first</option>';
            }
        });
    }
    
    // Handle forgot password form submission (for standalone login page)
    const forgotPasswordForm = document.getElementById('forgot-password-form-element');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('forgot-password-submit');
            const submitText = submitBtn ? submitBtn.querySelector('.submit-text') : null;
            const submitLoading = submitBtn ? submitBtn.querySelector('.submit-loading') : null;
            const messageDiv = document.getElementById('forgot-password-message');
            const emailInput = document.getElementById('forgot_email');
            const email = emailInput ? emailInput.value.trim() : '';
            
            if (!email) {
                if (messageDiv) {
                    messageDiv.innerHTML = '<div class="nymia-error-message"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>Please enter your email address.</span></div>';
                    messageDiv.style.display = 'block';
                }
                return;
            }
            
            // Show loading state
            if (submitBtn) submitBtn.disabled = true;
            if (submitText) submitText.style.display = 'none';
            if (submitLoading) {
                submitLoading.style.display = 'inline-flex';
                submitLoading.style.alignItems = 'center';
                submitLoading.style.gap = '8px';
            }
            if (messageDiv) messageDiv.style.display = 'none';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'nymia_forgot_password');
            formData.append('user_email', email);
            const nonceField = document.querySelector('input[name="nymia_forgot_password_nonce"]');
            if (nonceField) {
                formData.append('nymia_forgot_password_nonce', nonceField.value);
            }
            
            // Send AJAX request
            const ajaxUrl = (window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php';
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.style.display = 'inline';
                if (submitLoading) submitLoading.style.display = 'none';
                
                if (messageDiv) {
                    if (data.success) {
                        messageDiv.innerHTML = '<div class="nymia-success-message"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg><span>' + (data.data.message || 'Password reset link has been sent to your email address.') + '</span></div>';
                        if (emailInput) emailInput.value = '';
                    } else {
                        messageDiv.innerHTML = '<div class="nymia-error-message"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>' + (data.data.message || 'An error occurred. Please try again.') + '</span></div>';
                    }
                    messageDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.style.display = 'inline';
                if (submitLoading) submitLoading.style.display = 'none';
                if (messageDiv) {
                    messageDiv.innerHTML = '<div class="nymia-error-message"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>An error occurred. Please try again.</span></div>';
                    messageDiv.style.display = 'block';
                }
            });
        });
    }
});
</script>

