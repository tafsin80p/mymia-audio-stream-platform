<?php
/**
 * ========================================
 * NYMIA THEME - BECOME A CREATOR MODAL
 * ========================================
 * Modal for users to apply to become a creator
 * 
 * @package Nymia
 * @version 1.0
 */
?>

<div id="nymia-become-creator-modal" class="nymia-become-creator-modal" style="display: none;">
    <div class="nymia-become-creator-overlay"></div>
    <div class="nymia-become-creator-container">
        <button type="button" class="nymia-become-creator-close" id="nymia-become-creator-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div class="nymia-become-creator-content">
            <div class="nymia-become-creator-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="8" x2="19" y2="14"></line>
                    <line x1="22" y1="11" x2="16" y2="11"></line>
                </svg>
            </div>
            
            <h2>Become a Creator</h2>
            <p class="nymia-become-creator-subtitle">Join our community of content creators and start sharing your work with the world!</p>
            
            <div class="nymia-become-creator-benefits">
                <div class="nymia-benefit-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <h4>Create Content</h4>
                        <p>Upload audio files, ebooks, and more</p>
                    </div>
                </div>
                
                <div class="nymia-benefit-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <h4>Build Your Audience</h4>
                        <p>Grow followers and engage with your community</p>
                    </div>
                </div>
                
                <div class="nymia-benefit-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <h4>Earn Money</h4>
                        <p>Monetize your content and track earnings</p>
                    </div>
                </div>
                
                <div class="nymia-benefit-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <h4>Go Live</h4>
                        <p>Start live streaming and interact in real-time</p>
                    </div>
                </div>
            </div>
            
            <div class="nymia-become-creator-message" id="nymia-become-creator-message" style="display: none;"></div>
            
            <div class="nymia-become-creator-actions">
                <button type="button" class="nymia-btn-outline" id="nymia-become-creator-cancel">
                    Not Now
                </button>
                <button type="button" class="nymia-btn-gradient" id="nymia-become-creator-submit">
                    Apply Now
                </button>
            </div>
        </div>
    </div>
</div>

<div id="nymia-kyc-modal" class="nymia-become-creator-modal nymia-kyc-modal" style="display: none;">
    <div class="nymia-become-creator-overlay"></div>
    <div class="nymia-kyc-container">
        <button type="button" class="nymia-become-creator-close" id="nymia-kyc-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="nymia-kyc-content">
            <div class="nymia-kyc-header">
                <div class="nymia-become-creator-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11a4 4 0 1 0 6 0"></path>
                        <path d="M12 3c2.667 0 4.823 1.456 6 4c1.333 3 .667 6-2 9c-1.333 1.333-2.667 2-4 2s-2.667-.667-4-2c-2.667-3-3.333-6-2-9c1.177-2.544 3.333-4 6-4z"></path>
                    </svg>
                </div>
                <h2><?php esc_html_e('Verify Your Identity', 'nymia'); ?></h2>
                <p class="nymia-become-creator-subtitle"><?php esc_html_e('Complete KYC verification so we can review and approve your creator application.', 'nymia'); ?></p>
            </div>

            <form id="nymia-kyc-form" class="nymia-kyc-form" enctype="multipart/form-data">
                <div class="nymia-kyc-grid">
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('First Name', 'nymia'); ?> *</span>
                        <input type="text" name="kyc_first_name" required placeholder="<?php esc_attr_e('Enter your first name', 'nymia'); ?>">
                    </label>
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('Last Name', 'nymia'); ?> *</span>
                        <input type="text" name="kyc_last_name" required placeholder="<?php esc_attr_e('Enter your last name', 'nymia'); ?>">
                    </label>
                </div>

                <div class="nymia-kyc-grid">
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('Date of Birth', 'nymia'); ?> *</span>
                        <input type="date" name="kyc_dob" required>
                    </label>
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('Phone Number', 'nymia'); ?> *</span>
                        <input type="tel" name="kyc_phone" required placeholder="<?php esc_attr_e('Enter your phone number', 'nymia'); ?>">
                    </label>
                </div>

                <label class="nymia-kyc-field">
                    <span><?php esc_html_e('Street', 'nymia'); ?> *</span>
                    <input type="text" name="kyc_street" required placeholder="<?php esc_attr_e('Enter your street address', 'nymia'); ?>">
                </label>

                <div class="nymia-kyc-grid">
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('City', 'nymia'); ?> *</span>
                        <input type="text" name="kyc_city" required placeholder="<?php esc_attr_e('Enter your city', 'nymia'); ?>">
                    </label>
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('Country', 'nymia'); ?> *</span>
                        <select name="kyc_country" required>
                            <option value=""><?php esc_html_e('Select your country', 'nymia'); ?></option>
                            <?php
                            // Comprehensive list of countries
                            $countries = array(
                                'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France', 'Italy', 'Spain', 'Netherlands', 'Belgium',
                                'Switzerland', 'Austria', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Poland', 'Portugal', 'Greece', 'Ireland',
                                'Czech Republic', 'Romania', 'Hungary', 'Bulgaria', 'Croatia', 'Slovakia', 'Slovenia', 'Lithuania', 'Latvia', 'Estonia',
                                'Luxembourg', 'Malta', 'Cyprus', 'Iceland', 'Russia', 'Ukraine', 'Turkey', 'Israel', 'Saudi Arabia', 'United Arab Emirates',
                                'Qatar', 'Kuwait', 'Bahrain', 'Oman', 'Jordan', 'Lebanon', 'Egypt', 'South Africa', 'Nigeria', 'Kenya',
                                'Ghana', 'Morocco', 'Tunisia', 'Algeria', 'Ethiopia', 'Tanzania', 'Uganda', 'Zimbabwe', 'Botswana', 'Namibia',
                                'China', 'Japan', 'South Korea', 'India', 'Indonesia', 'Thailand', 'Malaysia', 'Singapore', 'Philippines', 'Vietnam',
                                'Taiwan', 'Hong Kong', 'New Zealand', 'Fiji', 'Papua New Guinea', 'Brazil', 'Mexico', 'Argentina', 'Chile', 'Colombia',
                                'Peru', 'Venezuela', 'Ecuador', 'Uruguay', 'Paraguay', 'Bolivia', 'Panama', 'Costa Rica', 'Guatemala', 'Honduras',
                                'El Salvador', 'Nicaragua', 'Dominican Republic', 'Jamaica', 'Trinidad and Tobago', 'Barbados', 'Bahamas', 'Belize', 'Guyana', 'Suriname'
                            );
                            sort($countries);
                            foreach ($countries as $country) {
                                echo '<option value="' . esc_attr($country) . '">' . esc_html($country) . '</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>

                <label class="nymia-kyc-field">
                    <span><?php esc_html_e('ZIP CODE', 'nymia'); ?> *</span>
                    <input type="text" name="kyc_zip_code" required placeholder="<?php esc_attr_e('Enter your ZIP/postal code', 'nymia'); ?>">
                </label>

                <div class="nymia-kyc-grid">
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('ID Type', 'nymia'); ?> *</span>
                        <select name="kyc_id_type" required>
                            <option value=""><?php esc_html_e('Select document type', 'nymia'); ?></option>
                            <option value="passport"><?php esc_html_e('Passport', 'nymia'); ?></option>
                            <option value="driver_license"><?php esc_html_e('Driver’s License', 'nymia'); ?></option>
                            <option value="national_id"><?php esc_html_e('National ID', 'nymia'); ?></option>
                        </select>
                    </label>
                    <label class="nymia-kyc-field">
                        <span><?php esc_html_e('ID Number', 'nymia'); ?> *</span>
                        <input type="text" name="kyc_id_number" id="kyc_id_number" required pattern="[0-9]+" inputmode="numeric" placeholder="<?php esc_attr_e('Enter numbers only', 'nymia'); ?>" title="<?php esc_attr_e('Only numbers are allowed', 'nymia'); ?>">
                        <div id="kyc_id_number_error" style="display: none; color: #ef4444; font-size: 0.85rem; margin-top: 5px;"><?php esc_html_e('ID Number must contain only numbers', 'nymia'); ?></div>
                    </label>
                </div>

                <label class="nymia-kyc-field">
                    <span><?php esc_html_e('Upload ID Document (PDF/JPG/PNG)', 'nymia'); ?></span>
                    <input type="file" name="kyc_document" accept=".jpg,.jpeg,.png,.pdf">
                </label>

                <div class="nymia-kyc-photo-wrapper">
                    <div class="nymia-kyc-photo-buttons">
                        <button type="button" class="nymia-btn-outline" id="nymia-kyc-start-camera">
                            <?php esc_html_e('Use Camera', 'nymia'); ?>
                        </button>
                        <button type="button" class="nymia-btn-outline" id="nymia-kyc-stop-camera" style="display:none;">
                            <?php esc_html_e('Stop Camera', 'nymia'); ?>
                        </button>
                    </div>

                    <div class="nymia-kyc-camera" id="nymia-kyc-camera" style="display:none;">
                        <video id="nymia-kyc-video" playsinline autoplay></video>
                        <canvas id="nymia-kyc-canvas" style="display:none;"></canvas>
                        <button type="button" class="nymia-btn-gradient" id="nymia-kyc-capture">
                            <?php esc_html_e('Capture', 'nymia'); ?>
                        </button>
                    </div>

                    <p class="nymia-kyc-camera-instruction" id="nymia-kyc-camera-instruction" style="display: none; margin-top: 16px; text-align: center; color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; line-height: 1.5; padding: 0 16px;">
                        <?php esc_html_e('Take a photo with your document close to your face, so that both the document and your face are visible', 'nymia'); ?>
                    </p>

                    <div class="nymia-kyc-photo-preview" id="nymia-kyc-photo-preview" style="display:none;">
                        <img src="" alt="<?php esc_attr_e('Captured document', 'nymia'); ?>">
                        <div class="nymia-kyc-photo-preview-actions">
                            <button type="button" class="nymia-btn-outline" id="nymia-kyc-retake">
                                <?php esc_html_e('Retake', 'nymia'); ?>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="kyc_photo_data" id="nymia-kyc-photo-data">
                </div>
            </form>

            <div class="nymia-become-creator-message" id="nymia-kyc-message" style="display:none;"></div>

            <div class="nymia-become-creator-actions" style="margin-top:24px;">
                <button type="button" class="nymia-btn-outline" id="nymia-kyc-back">
                    <?php esc_html_e('Back', 'nymia'); ?>
                </button>
                <button type="button" class="nymia-btn-gradient" id="nymia-kyc-submit">
                    <?php esc_html_e('Submit Verification', 'nymia'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let kycStream = null;
    // Open modal
    $('#nymia-become-creator-btn').on('click', function() {
        $('#nymia-become-creator-modal').fadeIn(300);
    });
    
    // Close modal
    function closeBecomeCreatorModal() {
        $('#nymia-become-creator-modal').fadeOut(300);
        $('#nymia-become-creator-message').hide().removeClass('success error').html('');
    }
    
    $('#nymia-become-creator-close').on('click', closeBecomeCreatorModal);
    $('#nymia-become-creator-cancel').on('click', closeBecomeCreatorModal);
    $('.nymia-become-creator-overlay').on('click', closeBecomeCreatorModal);
    
    $('#nymia-become-creator-submit').on('click', function() {
        $('#nymia-become-creator-modal').fadeOut(200, function() {
            const $kycModal = $('#nymia-kyc-modal');
            $kycModal.css('display', 'flex').hide().fadeIn(300);
        });
    });

    function closeKycModal() {
        stopKycCamera();
        $('#nymia-kyc-modal').fadeOut(300);
        resetKycForm();
    }

    function resetKycForm() {
        const $form = $('#nymia-kyc-form');
        if ($form.length) {
            $form[0].reset();
        }
        $('#nymia-kyc-message').hide().removeClass('success error').html('');
        $('#nymia-kyc-photo-data').val('');
        $('#nymia-kyc-photo-preview').hide().find('img').attr('src', '');
        $('#nymia-kyc-camera').hide();
        $('#nymia-kyc-camera-instruction').hide();
        $('#nymia-kyc-stop-camera').hide();
        $('#nymia-kyc-start-camera').show();
        $('#nymia-kyc-capture').show();
        stopKycCamera();
        $('#nymia-kyc-submit').prop('disabled', false).text('<?php echo esc_js(__('Submit Verification', 'nymia')); ?>');
    }

    $('#nymia-kyc-close').on('click', closeKycModal);
    $('.nymia-kyc-modal .nymia-become-creator-overlay').on('click', closeKycModal);

    $('#nymia-kyc-back').on('click', function() {
        closeKycModal();
        $('#nymia-become-creator-modal').fadeIn(300);
    });

    const $kycVideo = $('#nymia-kyc-video');
    const $kycCanvas = $('#nymia-kyc-canvas');
    const $kycCamera = $('#nymia-kyc-camera');
    const $startCameraBtn = $('#nymia-kyc-start-camera');
    const $stopCameraBtn = $('#nymia-kyc-stop-camera');
    const $captureBtn = $('#nymia-kyc-capture');
    const $photoPreview = $('#nymia-kyc-photo-preview');
    const $photoImg = $('#nymia-kyc-photo-preview img');
    const $photoDataInput = $('#nymia-kyc-photo-data');

    function stopKycCamera() {
        if (kycStream) {
            kycStream.getTracks().forEach(track => track.stop());
            kycStream = null;
        }
        if ($kycVideo.length) {
            $kycVideo.get(0).srcObject = null;
        }
    }

    async function startKycCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('<?php echo esc_js(__('Camera access is not supported in this browser.', 'nymia')); ?>');
            return;
        }
        try {
            kycStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            if ($kycVideo.length) {
                $kycVideo.get(0).srcObject = kycStream;
            }
            $kycCamera.fadeIn(200);
            $('#nymia-kyc-camera-instruction').fadeIn(200);
            $stopCameraBtn.show();
            $startCameraBtn.hide();
            $photoPreview.hide();
            $photoImg.attr('src', '');
            $photoDataInput.val('');
        } catch (error) {
            console.error('Unable to start camera', error);
            alert('<?php echo esc_js(__('Unable to access the camera. Please check permissions.', 'nymia')); ?>');
        }
    }

    $startCameraBtn.on('click', startKycCamera);

    $stopCameraBtn.on('click', function() {
        stopKycCamera();
        $kycCamera.fadeOut(200);
        $('#nymia-kyc-camera-instruction').fadeOut(200);
        $stopCameraBtn.hide();
        $startCameraBtn.show();
    });

    $('#nymia-kyc-retake').on('click', function() {
        $photoPreview.hide();
        $photoImg.attr('src', '');
        $photoDataInput.val('');
        startKycCamera();
    });

    $captureBtn.on('click', function() {
        if (!kycStream || !$kycVideo.length) {
            alert('<?php echo esc_js(__('Camera not ready. Please enable camera first.', 'nymia')); ?>');
            return;
        }

        const video = $kycVideo.get(0);
        const canvas = $kycCanvas.get(0);
        if (!canvas || !video.videoWidth) {
            alert('<?php echo esc_js(__('Camera stream not ready yet. Try again in a moment.', 'nymia')); ?>');
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataURL = canvas.toDataURL('image/png');
        $photoDataInput.val(dataURL);
        $photoImg.attr('src', dataURL);
        $photoPreview.fadeIn(200);

        stopKycCamera();
        $kycCamera.hide();
        $('#nymia-kyc-camera-instruction').hide();
        $stopCameraBtn.hide();
        $startCameraBtn.show();
    });

    function dataURLToBlob(dataURL) {
        const parts = dataURL.split(',');
        const mime = parts[0].match(/:(.*?);/)[1];
        const bstr = atob(parts[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }

    // ID Number validation - only allow numbers
    const $idNumberInput = $('#kyc_id_number');
    const $idNumberError = $('#kyc_id_number_error');
    
    if ($idNumberInput.length) {
        // Restrict input to numbers only
        $idNumberInput.on('input', function() {
            const value = $(this).val();
            // Remove any non-numeric characters
            const numericValue = value.replace(/[^0-9]/g, '');
            if (value !== numericValue) {
                $(this).val(numericValue);
            }
            // Hide error if valid
            if (numericValue && /^[0-9]+$/.test(numericValue)) {
                $idNumberError.hide();
            }
        });
        
        // Prevent paste of non-numeric content
        $idNumberInput.on('paste', function(e) {
            const paste = (e.originalEvent || e).clipboardData.getData('text');
            if (!/^[0-9]+$/.test(paste)) {
                e.preventDefault();
                const currentValue = $(this).val();
                const numericPaste = paste.replace(/[^0-9]/g, '');
                $(this).val(currentValue + numericPaste);
            }
        });
        
        // Validate on blur
        $idNumberInput.on('blur', function() {
            const value = $(this).val();
            if (value && !/^[0-9]+$/.test(value)) {
                $idNumberError.show();
            } else {
                $idNumberError.hide();
            }
        });
    }

    $('#nymia-kyc-submit').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.text();
        const $message = $('#nymia-kyc-message');
        const $form = $('#nymia-kyc-form');

        if (!$form.length) {
            return;
        }
        
        // Client-side validation for all required fields
        const requiredFields = {
            'kyc_first_name': '<?php echo esc_js(__('First Name', 'nymia')); ?>',
            'kyc_last_name': '<?php echo esc_js(__('Last Name', 'nymia')); ?>',
            'kyc_dob': '<?php echo esc_js(__('Date of Birth', 'nymia')); ?>',
            'kyc_phone': '<?php echo esc_js(__('Phone Number', 'nymia')); ?>',
            'kyc_street': '<?php echo esc_js(__('Street', 'nymia')); ?>',
            'kyc_city': '<?php echo esc_js(__('City', 'nymia')); ?>',
            'kyc_country': '<?php echo esc_js(__('Country', 'nymia')); ?>',
            'kyc_zip_code': '<?php echo esc_js(__('ZIP Code', 'nymia')); ?>',
            'kyc_id_type': '<?php echo esc_js(__('ID Type', 'nymia')); ?>',
            'kyc_id_number': '<?php echo esc_js(__('ID Number', 'nymia')); ?>'
        };
        
        const missingFields = [];
        for (const [fieldName, fieldLabel] of Object.entries(requiredFields)) {
            const $field = $form.find('[name="' + fieldName + '"]');
            const fieldValue = $field.val() ? $field.val().trim() : '';
            if (!fieldValue) {
                missingFields.push(fieldLabel);
            }
        }
        
        if (missingFields.length > 0) {
            $message.html('<?php echo esc_js(__('Please complete all required fields:', 'nymia')); ?> ' + missingFields.join(', '))
                .addClass('error')
                .show();
            $btn.prop('disabled', false).text(originalText);
            // Focus on first missing field
            const firstMissingField = Object.keys(requiredFields).find(fieldName => {
                const $field = $form.find('[name="' + fieldName + '"]');
                return !($field.val() ? $field.val().trim() : '');
            });
            if (firstMissingField) {
                $form.find('[name="' + firstMissingField + '"]').focus();
            }
            return;
        }
        
        // Validate ID Number format before submission
        const idNumber = $idNumberInput.val() ? $idNumberInput.val().trim() : '';
        if (idNumber && !/^[0-9]+$/.test(idNumber)) {
            $idNumberError.show();
            $idNumberInput.focus();
            $btn.prop('disabled', false).text(originalText);
            return;
        }
        $idNumberError.hide();
        
        const formData = new FormData($form[0]);
        formData.append('action', 'nymia_submit_creator_kyc');
        formData.append('nonce', '<?php echo wp_create_nonce("nymia_creator_kyc"); ?>');

        const photoData = $photoDataInput.val();
        if (!formData.get('kyc_document') && !photoData) {
            $message.html('<?php echo esc_js(__('Please upload a document or capture a photo for verification.', 'nymia')); ?>')
                .addClass('error')
                .show();
            $btn.prop('disabled', false).text(originalText);
            return;
        }

        if (photoData) {
            const blob = dataURLToBlob(photoData);
            formData.append('kyc_photo_capture', blob, 'kyc-photo.png');
        }

        $message.hide().removeClass('success error').html('');
        $btn.prop('disabled', true).text('<?php echo esc_js(__('Submitting...', 'nymia')); ?>');
        
        $.ajax({
            url: (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response && response.success) {
                    $message.html(response.data.message || '<?php echo esc_js(__('Verification submitted! Our team will contact you soon.', 'nymia')); ?>')
                        .addClass('success')
                        .show();
                    setTimeout(function() {
                        closeKycModal();
                    }, 2500);
                } else {
                    $message.html(response && response.data && response.data.message ? response.data.message : '<?php echo esc_js(__('Unable to submit verification. Please check your details and try again.', 'nymia')); ?>')
                        .addClass('error')
                        .show();
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                $message.html('<?php echo esc_js(__('An unexpected error occurred. Please try again later.', 'nymia')); ?>')
                    .addClass('error')
                    .show();
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

