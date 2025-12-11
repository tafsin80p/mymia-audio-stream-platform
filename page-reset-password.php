<?php
/**
 * ========================================
 * NYMIA THEME - RESET PASSWORD PAGE
 * ========================================
 * Template for password reset
 * Displays password reset form
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Get reset key and login from URL
$key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';
$error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';

// Error messages
$error_message = '';
switch ($error) {
    case 'security':
        $error_message = 'Security check failed. Please try again.';
        break;
    case 'invalid':
        $error_message = 'Invalid reset link. Please request a new password reset.';
        break;
    case 'expired':
        $error_message = 'This password reset link has expired. Please request a new one.';
        break;
    case 'mismatch':
        $error_message = 'Passwords do not match. Please try again.';
        break;
    case 'weak':
        $error_message = 'Password must be at least 8 characters long.';
        break;
}

// Verify reset key if provided
$user = null;
if (!empty($key) && !empty($login)) {
    $user = check_password_reset_key($key, $login);
    if (is_wp_error($user)) {
        $error_message = 'This password reset link is invalid or has expired. Please request a new one.';
        $key = '';
        $login = '';
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php bloginfo('name'); ?> - Reset Password</title>
    <?php wp_head(); ?>
    
    <?php require_once(get_template_directory() . '/auth/auth-styles.php'); ?>
    
    <style>
        .nymia-reset-password-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
        }
        
        .nymia-reset-password-box {
            background: linear-gradient(145deg, rgba(24, 24, 28, 0.98), rgba(15, 15, 17, 0.98));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.6);
        }
        
        .nymia-reset-password-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .nymia-reset-password-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin: 0 0 8px 0;
        }
        
        .nymia-reset-password-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            margin: 0;
        }
        
        .nymia-reset-password-form {
            margin-top: 24px;
        }
        
        .nymia-password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .nymia-password-strength.strong {
            color: #22c55e;
        }
        
        .nymia-password-strength.medium {
            color: #f59e0b;
        }
        
        .nymia-password-strength.weak {
            color: #ef4444;
        }
    </style>
</head>
<body class="nymia-login-body" style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;">

<div class="nymia-reset-password-container">
    <div class="nymia-reset-password-box">
        <div class="nymia-reset-password-header">
            <div class="nymia-login-logo" style="margin-bottom: 24px;">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nymia-logo.jpg'); ?>" alt="Nymia Logo" onerror="this.onerror=null; this.src='<?php echo esc_url(get_template_directory_uri() . '/assets/images/dashboardLogo-Bt6G_8pS.png'); ?>';" style="max-width: 120px; height: auto;" />
            </div>
            <h1><?php esc_html_e('Reset Password', 'nymia'); ?></h1>
            <p><?php esc_html_e('Enter your new password below', 'nymia'); ?></p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="nymia-error-message">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?php echo esc_html($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (empty($key) || empty($login)): ?>
            <div class="nymia-error-message">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?php esc_html_e('Invalid or missing reset link. Please request a new password reset.', 'nymia'); ?></span>
            </div>
            <div style="text-align: center; margin-top: 24px;">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="nymia-submit-button" style="display: inline-block; text-decoration: none;"><?php esc_html_e('Back to Home', 'nymia'); ?></a>
            </div>
        <?php else: ?>
            <form method="post" action="" class="nymia-reset-password-form" id="reset-password-form">
                <?php wp_nonce_field('nymia_reset_password', 'nymia_reset_password_nonce'); ?>
                <input type="hidden" name="nymia_reset_password" value="1">
                <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
                <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
                
                <div class="nymia-form-group">
                    <label class="nymia-form-label" for="pass1">New Password</label>
                    <input type="password" name="pass1" id="pass1" class="nymia-form-input" placeholder="Enter your new password" required autocomplete="new-password" minlength="8">
                    <div id="password-strength" class="nymia-password-strength"></div>
                </div>
                
                <div class="nymia-form-group">
                    <label class="nymia-form-label" for="pass2">Confirm New Password</label>
                    <input type="password" name="pass2" id="pass2" class="nymia-form-input" placeholder="Confirm your new password" required autocomplete="new-password" minlength="8">
                    <div id="password-match" style="margin-top: 8px; font-size: 0.85rem; display: none;"></div>
                </div>
                
                <button type="submit" class="nymia-submit-button" id="reset-password-submit"><?php esc_html_e('Reset Password', 'nymia'); ?></button>
            </form>
            
            <div class="nymia-forgot-password" style="margin-top: 24px; text-align: center;">
                <a href="<?php echo esc_url(home_url('/')); ?>">← Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reset-password-form');
    const pass1 = document.getElementById('pass1');
    const pass2 = document.getElementById('pass2');
    const strengthDiv = document.getElementById('password-strength');
    const matchDiv = document.getElementById('password-match');
    
    if (form && pass1 && pass2) {
        // Password strength checker
        function checkPasswordStrength(password) {
            if (password.length === 0) {
                strengthDiv.textContent = '';
                strengthDiv.className = 'nymia-password-strength';
                return;
            }
            
            if (password.length < 8) {
                strengthDiv.textContent = 'Password must be at least 8 characters';
                strengthDiv.className = 'nymia-password-strength weak';
                return;
            }
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                strengthDiv.textContent = 'Weak password';
                strengthDiv.className = 'nymia-password-strength weak';
            } else if (strength <= 3) {
                strengthDiv.textContent = 'Medium password';
                strengthDiv.className = 'nymia-password-strength medium';
            } else {
                strengthDiv.textContent = 'Strong password';
                strengthDiv.className = 'nymia-password-strength strong';
            }
        }
        
        // Password match checker
        function checkPasswordMatch() {
            if (pass2.value.length === 0) {
                matchDiv.style.display = 'none';
                return;
            }
            
            matchDiv.style.display = 'block';
            if (pass1.value === pass2.value) {
                matchDiv.textContent = '✓ Passwords match';
                matchDiv.style.color = '#22c55e';
            } else {
                matchDiv.textContent = '✗ Passwords do not match';
                matchDiv.style.color = '#ef4444';
            }
        }
        
        pass1.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });
        
        pass2.addEventListener('input', checkPasswordMatch);
        
        form.addEventListener('submit', function(e) {
            if (pass1.value !== pass2.value) {
                e.preventDefault();
                matchDiv.textContent = '✗ Passwords do not match';
                matchDiv.style.color = '#ef4444';
                matchDiv.style.display = 'block';
                return false;
            }
            
            if (pass1.value.length < 8) {
                e.preventDefault();
                strengthDiv.textContent = 'Password must be at least 8 characters';
                strengthDiv.className = 'nymia-password-strength weak';
                return false;
            }
        });
    }
});
</script>

<?php wp_footer(); ?>
</body>
</html>


