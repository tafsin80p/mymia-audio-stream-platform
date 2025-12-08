<?php
/**
 * ========================================
 * NYMIA THEME - SIGNUP FORM PARTIAL
 * ========================================
 * Displays signup form with validation
 * Includes username, email, password fields
 * Shows error messages for failed registrations
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- ======================================== -->
<!-- SIGNUP FORM SECTION -->
<!-- ======================================== -->
<!-- Signup Form -->
<div id="signup-form" class="nymia-tab-content<?php echo (isset($_GET['registration']) && $_GET['registration'] == 'error') ? ' active' : ''; ?>">
    <?php
    // Handle registration errors
    if (isset($_GET['registration']) && $_GET['registration'] == 'error'): 
        $error_msg = isset($_GET['error']) ? urldecode($_GET['error']) : 'Registration failed. Please try again.';
        ?>
        <div class="nymia-error-message">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span><?php echo esc_html($error_msg); ?></span>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="registerform" enctype="multipart/form-data">
        <input type="hidden" name="action" value="nymia_user_register">
        <?php wp_nonce_field('nymia_register', 'nymia_register_nonce'); ?>
        
        <div class="nymia-form-row" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_languages"><?php esc_html_e('Language', 'nymia'); ?></label>
                <div class="nymia-form-input" style="padding: 0;">
                    <?php echo do_shortcode('[gtranslate]'); ?>
                </div>
            </div>
            
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_username">Username</label>
                <input type="text" name="username" id="reg_username" class="nymia-form-input" placeholder="Choose a username" required autocomplete="username">
            </div>
        </div>
        
        <div class="nymia-form-row" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_first_name">First Name</label>
                <input type="text" name="first_name" id="reg_first_name" class="nymia-form-input" placeholder="Enter your first name" required autocomplete="given-name">
            </div>
            
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_last_name">Last Name</label>
                <input type="text" name="last_name" id="reg_last_name" class="nymia-form-input" placeholder="Enter your last name" required autocomplete="family-name">
            </div>
        </div>
        
        <div class="nymia-form-group">
            <label class="nymia-form-label" for="reg_email">Email</label>
            <input type="email" name="email" id="reg_email" class="nymia-form-input" placeholder="Enter your email" required autocomplete="email">
        </div>
        
        <!-- Account Type Selection -->
        <div class="nymia-form-group">
            <label class="nymia-form-label">Account Type <span style="color: var(--muted-foreground); font-size: 0.85rem;">(Required)</span></label>
            <div class="nymia-account-type-selector" style="display: flex; gap: 16px; margin-top: 8px;">
                <label class="nymia-account-type-option" style="flex: 1; cursor: pointer;">
                    <input type="radio" name="account_type" value="user" checked required style="display: none;">
                    <div class="nymia-account-type-card" style="padding: 20px; border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 12px; background: rgba(255, 255, 255, 0.03); transition: all 0.3s ease; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 8px;">👤</div>
                        <div style="font-weight: 600; color: #fff; margin-bottom: 4px;">User</div>
                        <div style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6);">Browse and enjoy content</div>
                    </div>
                </label>
                <label class="nymia-account-type-option" style="flex: 1; cursor: pointer;">
                    <input type="radio" name="account_type" value="creator" required style="display: none;">
                    <div class="nymia-account-type-card" style="padding: 20px; border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 12px; background: rgba(255, 255, 255, 0.03); transition: all 0.3s ease; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 8px;">🎨</div>
                        <div style="font-weight: 600; color: #fff; margin-bottom: 4px;">Creator</div>
                        <div style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6);">Create and share content</div>
                    </div>
                </label>
            </div>
        </div>
        
        <div class="nymia-form-group">
            <label class="nymia-form-label" for="reg_date_of_birth">Date of Birth <span style="color: var(--muted-foreground); font-size: 0.85rem;">(Must be 18+ years old)</span></label>
            <input type="date" name="date_of_birth" id="reg_date_of_birth" class="nymia-form-input" placeholder="Select your date of birth" required autocomplete="bday" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" style="padding: 12px 16px;">
            <div id="dateOfBirthError" style="display: none; color: #dc3545; font-size: 0.85rem; margin-top: 5px;"></div>
        </div>
        
        <div class="nymia-form-row" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_password">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="reg_password" class="nymia-form-input" placeholder="Create a password" required autocomplete="new-password" aria-describedby="passwordHelp">
                    <button type="button" id="togglePassword" aria-label="Show password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #999; cursor: pointer;">Show</button>
                </div>
            </div>
            
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_confirm_password">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" name="confirm_password" id="reg_confirm_password" class="nymia-form-input" placeholder="Confirm your password" required autocomplete="new-password">
                    <button type="button" id="toggleConfirmPassword" aria-label="Show password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #999; cursor: pointer;">Show</button>
                </div>
            </div>
        </div>
        
        <div class="nymia-form-group" style="margin-top: -8px;">
            <div id="passwordHelp" style="margin-top: 8px; color: var(--text-gray); font-size: 0.85rem;">Use at least 6 characters. Add numbers and symbols for a stronger password.</div>
            <div id="passwordStrength" style="margin-top: 10px; height: 8px; border-radius: 6px; background: rgba(255,255,255,0.08); overflow: hidden;">
                <div id="passwordStrengthFill" style="height: 100%; width: 0; background: #dc3545; transition: width 0.2s ease, background 0.2s ease;"></div>
            </div>
        </div>
        
        <!-- Address Fields -->
        <div class="nymia-form-group">
            <label class="nymia-form-label" for="reg_street">Street Address</label>
            <input type="text" name="street" id="reg_street" class="nymia-form-input" placeholder="Enter your street address" autocomplete="street-address">
        </div>
        
        <div class="nymia-form-row" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_country">Country</label>
                <select name="country" id="reg_country" class="nymia-form-input" required autocomplete="country-name" style="padding: 12px 16px; background: var(--input); border: 1px solid var(--border); border-radius: var(--radius); color: var(--foreground); font-size: 0.95rem; width: 100%; cursor: pointer;">
                    <option value="">Select your country</option>
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
            </div>
            
            <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                <label class="nymia-form-label" for="reg_city">City</label>
                <select name="city" id="reg_city" class="nymia-form-input" required autocomplete="address-level2" style="padding: 12px 16px; background: var(--input); border: 1px solid var(--border); border-radius: var(--radius); color: var(--foreground); font-size: 0.95rem; width: 100%; cursor: pointer;">
                    <option value="">Select country first</option>
                </select>
            </div>
        </div>
        
        <div class="nymia-form-group">
            <label class="nymia-form-label" for="reg_postal_code">Postal Code</label>
            <input type="text" name="postal_code" id="reg_postal_code" class="nymia-form-input" placeholder="Enter your postal code" autocomplete="postal-code">
        </div>
        
        <!-- KYC Section for Creators (Conditional) -->
        <div id="nymia-signup-kyc-section" class="nymia-signup-kyc-section" style="display: none; margin-top: 24px; padding-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <div style="margin-bottom: 20px;">
                <h3 style="color: #fff; font-size: 1.1rem; font-weight: 600; margin-bottom: 8px;"><?php esc_html_e('Identity Verification (KYC)', 'nymia'); ?></h3>
                <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem; margin: 0;"><?php esc_html_e('Please provide your ID document for verification. This is required for creators.', 'nymia'); ?></p>
            </div>
            
            <div class="nymia-form-row" style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                    <label class="nymia-form-label" for="reg_kyc_id_type"><?php esc_html_e('ID Type', 'nymia'); ?> <span style="color: #dc3545;">*</span></label>
                    <select name="kyc_id_type" id="reg_kyc_id_type" class="nymia-form-input" style="padding: 12px 16px; background: var(--input); border: 1px solid var(--border); border-radius: var(--radius); color: var(--foreground); font-size: 0.95rem; width: 100%; cursor: pointer;">
                        <option value=""><?php esc_html_e('Select document type', 'nymia'); ?></option>
                        <option value="passport"><?php esc_html_e('Passport', 'nymia'); ?></option>
                        <option value="driver_license"><?php esc_html_e('Driver\'s License', 'nymia'); ?></option>
                        <option value="national_id"><?php esc_html_e('National ID', 'nymia'); ?></option>
                    </select>
                </div>
                
                <div class="nymia-form-group" style="flex: 1 1 240px; min-width: 220px; max-width: none;">
                    <label class="nymia-form-label" for="reg_kyc_id_number"><?php esc_html_e('ID Number', 'nymia'); ?> <span style="color: #dc3545;">*</span></label>
                    <input type="text" name="kyc_id_number" id="reg_kyc_id_number" class="nymia-form-input" pattern="[0-9]+" inputmode="numeric" placeholder="<?php esc_attr_e('Enter numbers only', 'nymia'); ?>" title="<?php esc_attr_e('Only numbers are allowed', 'nymia'); ?>">
                    <div id="reg_kyc_id_number_error" style="display: none; color: #dc3545; font-size: 0.85rem; margin-top: 5px;"><?php esc_html_e('ID Number must contain only numbers', 'nymia'); ?></div>
                </div>
            </div>
            
            <div class="nymia-form-group">
                <label class="nymia-form-label" for="reg_kyc_document">
                    <?php esc_html_e('Upload ID Document (PDF/JPG/PNG)', 'nymia'); ?> 
                    <span style="color: rgba(255, 255, 255, 0.6); font-size: 0.85rem; font-weight: normal;"><?php esc_html_e('(Alternative to camera)', 'nymia'); ?></span>
                </label>
                <input type="file" name="kyc_document" id="reg_kyc_document" accept=".jpg,.jpeg,.png,.pdf" class="nymia-form-input" style="padding: 8px;">
                <p style="margin-top: 8px; color: rgba(255, 255, 255, 0.6); font-size: 0.85rem;">
                    <?php esc_html_e('Take a photo with your document placed close to your face', 'nymia'); ?>
                </p>
            </div>
            
            <!-- Camera Capture Section -->
            <div class="nymia-form-group">
                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <button type="button" class="nymia-btn-outline" id="reg-kyc-start-camera" style="padding: 10px 20px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; border-radius: 8px; cursor: pointer; font-size: 0.9rem;">
                        <?php esc_html_e('Use Camera', 'nymia'); ?>
                    </button>
                    <button type="button" class="nymia-btn-outline" id="reg-kyc-stop-camera" style="display:none; padding: 10px 20px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; border-radius: 8px; cursor: pointer; font-size: 0.9rem;">
                        <?php esc_html_e('Stop Camera', 'nymia'); ?>
                    </button>
                    <span style="color: rgba(255, 255, 255, 0.5); font-size: 0.85rem;">
                        <?php esc_html_e('(or upload file above)', 'nymia'); ?>
                    </span>
                </div>
                
                <div id="reg-kyc-camera" style="display:none; margin-bottom: 16px;">
                    <video id="reg-kyc-video" playsinline autoplay muted style="width: 100%; max-width: 500px; border-radius: 8px; background: #000; transform: scaleX(-1);"></video>
                    <canvas id="reg-kyc-canvas" style="display:none;"></canvas>
                    <button type="button" class="nymia-btn-gradient" id="reg-kyc-capture" style="margin-top: 12px; padding: 10px 24px; background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%); border: none; border-radius: 8px; color: #fff; cursor: pointer; font-weight: 600;">
                        <?php esc_html_e('Capture', 'nymia'); ?>
                    </button>
                </div>
                
                <p id="reg-kyc-camera-instruction" style="display: none; margin-top: 12px; text-align: center; color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; line-height: 1.5; padding: 0 16px;">
                    <?php esc_html_e('Take a photo with your document close to your face, so that both the document and your face are visible', 'nymia'); ?>
                </p>
                
                <div id="reg-kyc-photo-preview" style="display:none; margin-top: 16px;">
                    <img src="" alt="<?php esc_attr_e('Captured document', 'nymia'); ?>" style="max-width: 100%; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <button type="button" class="nymia-btn-outline" id="reg-kyc-retake" style="margin-top: 12px; padding: 8px 16px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; border-radius: 8px; cursor: pointer; font-size: 0.9rem;">
                        <?php esc_html_e('Retake', 'nymia'); ?>
                    </button>
                </div>
                <input type="hidden" name="kyc_photo_data" id="reg-kyc-photo-data">
            </div>
        </div>
        
        <div class="nymia-form-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap;">
            <input type="checkbox" id="accept_terms" required style="width: 18px; height: 18px; accent-color: var(--primary-color); flex-shrink: 0;">
            <label for="accept_terms" style="margin: 0; font-weight: 400; color: var(--text-gray); font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center;">I agree to the <a href="<?php echo esc_url(home_url('/policies')); ?>" style="color: var(--primary-light); text-decoration: none; margin-left: 4px;">Terms & Policies</a></label>
        </div>
        
        <button type="submit" class="nymia-submit-button">Create Account</button>
    </form>
    
    <?php
    // Show social login buttons if enabled
    $google_enabled = get_option('nymia_google_enabled', '0');
    $facebook_enabled = get_option('nymia_facebook_enabled', '0');
    $apple_enabled = get_option('nymia_apple_enabled', '0');
    
    if ($google_enabled == '1' || $facebook_enabled == '1' || $apple_enabled == '1'):
    ?>
        <div class="nymia-divider">or</div>
        
        <div class="nymia-social-login">
            <?php if ($google_enabled == '1'): ?>
                <a href="<?php echo esc_url(home_url('/?nymia_auth=google')); ?>" class="nymia-social-btn nymia-social-google">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>
            <?php endif; ?>
            
            <?php if ($facebook_enabled == '1'): ?>
                <a href="<?php echo esc_url(home_url('/?nymia_auth=facebook')); ?>" class="nymia-social-btn nymia-social-facebook">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="#1877F2">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span>Continue with Facebook</span>
                </a>
            <?php endif; ?>
            
            <?php if ($apple_enabled == '1'): ?>
                <a href="<?php echo esc_url(home_url('/?nymia_auth=apple')); ?>" class="nymia-social-btn nymia-social-apple">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="#FFFFFF">
                        <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                    </svg>
                    <span>Continue with Apple</span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="nymia-forgot-password">
        Already have an account? <a href="#" onclick="switchTab('login'); return false;">Sign In</a>
    </div>
</div>

