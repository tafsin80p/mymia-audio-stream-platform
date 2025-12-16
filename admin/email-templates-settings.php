<?php
/**
 * Email Templates Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email templates settings page
 */
function nymia_email_templates_settings_page() {
    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('nymia_email_templates_settings')) {
        // Save OTP verification email template
        if (isset($_POST['nymia_otp_email_subject'])) {
            update_option('nymia_otp_email_subject', sanitize_text_field($_POST['nymia_otp_email_subject']));
        }
        if (isset($_POST['nymia_otp_email_message'])) {
            update_option('nymia_otp_email_message', wp_kses_post($_POST['nymia_otp_email_message']));
        }
        
        // Save new user welcome email template
        if (isset($_POST['nymia_new_user_email_subject'])) {
            update_option('nymia_new_user_email_subject', sanitize_text_field($_POST['nymia_new_user_email_subject']));
        }
        if (isset($_POST['nymia_new_user_email_message'])) {
            update_option('nymia_new_user_email_message', wp_kses_post($_POST['nymia_new_user_email_message']));
        }
        
        // Save new creator welcome email template
        if (isset($_POST['nymia_new_creator_email_subject'])) {
            update_option('nymia_new_creator_email_subject', sanitize_text_field($_POST['nymia_new_creator_email_subject']));
        }
        if (isset($_POST['nymia_new_creator_email_message'])) {
            update_option('nymia_new_creator_email_message', wp_kses_post($_POST['nymia_new_creator_email_message']));
        }
        
        // Show success message
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Email templates saved successfully!', 'nymia') . '</p></div>';
    }
    
    // Get current templates
    $otp_subject = get_option('nymia_otp_email_subject', 'Verify Your Email - {site_name}');
    $otp_message = get_option('nymia_otp_email_message', "Hello!\n\nThank you for signing up with {site_name}!\n\nYour verification code is: {verification_code}\n\nPlease enter this code on the verification page to complete your registration.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nThe {site_name} Team");
    
    $new_user_subject = get_option('nymia_new_user_email_subject', 'Welcome to {site_name}!');
    $new_user_message = get_option('nymia_new_user_email_message', "Hello {display_name}!\n\nWelcome to {site_name}! We're excited to have you join our community.\n\nYour account has been successfully created:\nUsername: {username}\nEmail: {email}\n\nYou can now start exploring all the features we have to offer.\n\nIf you have any questions, feel free to reach out to our support team.\n\nBest regards,\nThe {site_name} Team");
    
    $new_creator_subject = get_option('nymia_new_creator_email_subject', 'Welcome Creator - {site_name}');
    $new_creator_message = get_option('nymia_new_creator_email_message', "Hello {display_name}!\n\nCongratulations! Your creator account has been successfully created on {site_name}.\n\nAs a creator, you now have access to:\n- Upload and share your content\n- Connect with your audience\n- Earn from your creations\n\nYour account details:\nUsername: {username}\nEmail: {email}\n\nWe're reviewing your application and will notify you once it's approved.\n\nIf you have any questions, feel free to reach out to our support team.\n\nBest regards,\nThe {site_name} Team");
    
    nymia_render_admin_settings_styles();
    ?>
    <div class="wrap nymia-stripe-wrap nymia-email-templates-settings">
        <div class="nymia-stripe-header">
            <div>
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <p><?php esc_html_e('Customize email messages sent to users for verification, welcome, and creator account creation.', 'nymia'); ?></p>
            </div>
        </div>

        <nav class="nymia-dashboard-nav">
            <a href="?page=nymia-theme-settings"><?php esc_html_e('Dashboard Overview', 'nymia'); ?></a>
            <a href="?page=nymia-general-settings"><?php esc_html_e('General Settings', 'nymia'); ?></a>
            <a class="active" href="?page=nymia-email-templates-settings"><?php esc_html_e('Email Templates', 'nymia'); ?></a>
            <a href="?page=nymia-social-login-settings"><?php esc_html_e('Social Login', 'nymia'); ?></a>
            <a href="?page=nymia-zegocloud-settings"><?php esc_html_e('ZEGO Cloud', 'nymia'); ?></a>
            <a href="?page=nymia-stripe-settings"><?php esc_html_e('Stripe Payments', 'nymia'); ?></a>
        </nav>

        <form method="post" action="" class="nymia-grid nymia-stripe-grid">
            <?php wp_nonce_field('nymia_email_templates_settings'); ?>
            
            <!-- OTP Verification Email Template -->
            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('OTP Verification Email', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Email sent to users with verification code for email confirmation.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label for="nymia_otp_email_subject"><?php esc_html_e('Email Subject', 'nymia'); ?></label>
                    <input 
                        type="text"
                        id="nymia_otp_email_subject"
                        name="nymia_otp_email_subject"
                        value="<?php echo esc_attr($otp_subject); ?>"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Verify Your Email - {site_name}', 'nymia'); ?>"
                    >
                    <p class="description">
                        <?php esc_html_e('Available variables: {site_name}, {verification_code}', 'nymia'); ?>
                    </p>
                </div>

                <div class="nymia-form-field">
                    <label for="nymia_otp_email_message"><?php esc_html_e('Email Message', 'nymia'); ?></label>
                    <textarea 
                        id="nymia_otp_email_message"
                        name="nymia_otp_email_message"
                        rows="12"
                        class="large-text"
                        placeholder="<?php esc_attr_e('Enter email message...', 'nymia'); ?>"
                    ><?php echo esc_textarea($otp_message); ?></textarea>
                    <p class="description">
                        <?php esc_html_e('Available variables: {site_name}, {verification_code}, {site_url}', 'nymia'); ?>
                    </p>
                </div>
            </section>

            <!-- New User Welcome Email Template -->
            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('New User Welcome Email', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Welcome email sent to new users after successful registration.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label for="nymia_new_user_email_subject"><?php esc_html_e('Email Subject', 'nymia'); ?></label>
                    <input 
                        type="text"
                        id="nymia_new_user_email_subject"
                        name="nymia_new_user_email_subject"
                        value="<?php echo esc_attr($new_user_subject); ?>"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Welcome to {site_name}!', 'nymia'); ?>"
                    >
                    <p class="description">
                        <?php esc_html_e('Available variables: {site_name}, {display_name}, {username}', 'nymia'); ?>
                    </p>
                </div>

                <div class="nymia-form-field">
                    <label for="nymia_new_user_email_message"><?php esc_html_e('Email Message', 'nymia'); ?></label>
                    <textarea 
                        id="nymia_new_user_email_message"
                        name="nymia_new_user_email_message"
                        rows="12"
                        class="large-text"
                        placeholder="<?php esc_attr_e('Enter email message...', 'nymia'); ?>"
                    ><?php echo esc_textarea($new_user_message); ?></textarea>
                    <p class="description">
                        <?php esc_html_e('Available variables: {site_name}, {display_name}, {username}, {email}, {site_url}', 'nymia'); ?>
                    </p>
                </div>
            </section>

            <!-- New Creator Welcome Email Template -->
            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('New Creator Welcome Email', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Welcome email sent to users when they become creators.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label for="nymia_new_creator_email_subject"><?php esc_html_e('Email Subject', 'nymia'); ?></label>
                    <input 
                        type="text"
                        id="nymia_new_creator_email_subject"
                        name="nymia_new_creator_email_subject"
                        value="<?php echo esc_attr($new_creator_subject); ?>"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Welcome Creator - {site_name}', 'nymia'); ?>"
                    >
                    <p class="description">
                        <?php esc_html_e('Available variables: {site_name}, {display_name}, {username}', 'nymia'); ?>
                    </p>
                </div>

                <div class="nymia-form-field">
                    <label for="nymia_new_creator_email_message"><?php esc_html_e('Email Message', 'nymia'); ?></label>
                    <textarea 
                        id="nymia_new_creator_email_message"
                        name="nymia_new_creator_email_message"
                        rows="12"
                        class="large-text"
                        placeholder="<?php esc_attr_e('Enter email message...', 'nymia'); ?>"
                    ><?php echo esc_textarea($new_creator_message); ?></textarea>
                    <p class="description">
                        <?php esc_html_e('Available variables: {site_name}, {display_name}, {username}, {email}, {site_url}', 'nymia'); ?>
                    </p>
                </div>
            </section>

            <?php submit_button(__('Save Email Templates', 'nymia'), 'primary', 'submit', false, array('class' => 'nymia-primary-btn')); ?>
        </form>
    </div>
    <style>
        .nymia-email-templates-settings .button.button-primary,
        .nymia-email-templates-settings .button.button-primary:visited {
            padding: 10px 18px !important;
            height: 45px !important;
            font-size: 0.8125rem !important;
        }
        .nymia-shortcode-badge {
            display: inline-block;
            background: rgba(191, 76, 26, 0.15);
            border: 1px solid rgba(191, 76, 26, 0.3);
            color: #BF4C1A;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin: 2px;
            cursor: default;
        }
        .nymia-shortcode-required {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        .nymia-shortcode-warning {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 13px;
            display: none;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Required shortcodes for each template
        const requiredShortcodes = {
            'nymia_otp_email_subject': [],
            'nymia_otp_email_message': ['{verification_code}'],
            'nymia_new_user_email_subject': [],
            'nymia_new_user_email_message': [],
            'nymia_new_creator_email_subject': [],
            'nymia_new_creator_email_message': []
        };
        
        // Add clickable shortcode badges
        function addShortcodeBadges() {
            const fields = {
                'nymia_otp_email_subject': ['{site_name}', '{verification_code}'],
                'nymia_otp_email_message': ['{site_name}', '{verification_code}', '{site_url}'],
                'nymia_new_user_email_subject': ['{site_name}', '{display_name}', '{username}'],
                'nymia_new_user_email_message': ['{site_name}', '{display_name}', '{username}', '{email}', '{site_url}'],
                'nymia_new_creator_email_subject': ['{site_name}', '{display_name}', '{username}'],
                'nymia_new_creator_email_message': ['{site_name}', '{display_name}', '{username}', '{email}', '{site_url}']
            };
            
            Object.keys(fields).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field) return;
                
                const wrapper = field.parentElement;
                const description = wrapper.querySelector('.description');
                if (!description) return;
                
                // Create shortcode badges container
                const badgesContainer = document.createElement('div');
                badgesContainer.style.marginTop = '8px';
                badgesContainer.style.display = 'flex';
                badgesContainer.style.flexWrap = 'wrap';
                badgesContainer.style.gap = '4px';
                badgesContainer.style.alignItems = 'center';
                
                const label = document.createElement('span');
                label.textContent = 'Click to insert: ';
                label.style.fontSize = '12px';
                label.style.color = 'rgba(255, 255, 255, 0.6)';
                label.style.marginRight = '4px';
                badgesContainer.appendChild(label);
                
                fields[fieldId].forEach(shortcode => {
                    const badge = document.createElement('span');
                    badge.className = 'nymia-shortcode-badge';
                    if (requiredShortcodes[fieldId] && requiredShortcodes[fieldId].includes(shortcode)) {
                        badge.className += ' nymia-shortcode-required';
                        badge.title = 'Required - Do not remove';
                    }
                    badge.textContent = shortcode;
                    badge.style.cursor = 'pointer';
                    badge.addEventListener('click', function() {
                        insertShortcode(field, shortcode);
                    });
                    badgesContainer.appendChild(badge);
                });
                
                description.parentNode.insertBefore(badgesContainer, description.nextSibling);
            });
        }
        
        // Insert shortcode at cursor position
        function insertShortcode(field, shortcode) {
            const start = field.selectionStart;
            const end = field.selectionEnd;
            const text = field.value;
            const before = text.substring(0, start);
            const after = text.substring(end, text.length);
            
            field.value = before + shortcode + after;
            field.selectionStart = field.selectionEnd = start + shortcode.length;
            field.focus();
            
            // Trigger validation
            validateField(field);
        }
        
        // Validate field for required shortcodes
        function validateField(field) {
            const fieldId = field.id;
            const value = field.value;
            const required = requiredShortcodes[fieldId] || [];
            
            let warning = field.parentElement.querySelector('.nymia-shortcode-warning');
            if (!warning) {
                warning = document.createElement('div');
                warning.className = 'nymia-shortcode-warning';
                field.parentElement.appendChild(warning);
            }
            
            const missing = required.filter(sc => !value.includes(sc));
            
            if (missing.length > 0) {
                warning.style.display = 'block';
                warning.innerHTML = '⚠️ <strong>Warning:</strong> Required shortcode(s) missing: ' + missing.join(', ') + '. These must be included for the email to work properly.';
            } else {
                warning.style.display = 'none';
            }
        }
        
        // Validate all fields on form submit
        const form = document.querySelector('.nymia-grid form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let hasErrors = false;
                
                Object.keys(requiredShortcodes).forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (!field) return;
                    
                    const required = requiredShortcodes[fieldId] || [];
                    const value = field.value;
                    const missing = required.filter(sc => !value.includes(sc));
                    
                    if (missing.length > 0) {
                        hasErrors = true;
                        validateField(field);
                        field.style.borderColor = '#ef4444';
                        field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
                    } else {
                        field.style.borderColor = '';
                        field.style.boxShadow = '';
                    }
                });
                
                if (hasErrors) {
                    e.preventDefault();
                    alert('Please include all required shortcodes before saving. Check the warnings below each field.');
                    return false;
                }
            });
        }
        
        // Add validation on input
        Object.keys(requiredShortcodes).forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    validateField(this);
                });
                field.addEventListener('blur', function() {
                    validateField(this);
                });
                // Initial validation
                validateField(field);
            }
        });
        
        // Add shortcode badges
        addShortcodeBadges();
    });
    </script>
    <?php
}

