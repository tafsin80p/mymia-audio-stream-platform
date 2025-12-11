<?php
/**
 * ========================================
 * NYMIA THEME - EMAIL VERIFICATION PAGE
 * ========================================
 * Template for email verification
 * Displays verification code input form
 * Handles resend code functionality
 * Shows verification status and errors
 * 
 * @package Nymia
 * @version 1.0
 */

// Get the verification key or user_id from URL
$verification_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$error = isset($_GET['error']) ? $_GET['error'] : '';
$resent = isset($_GET['resent']) ? $_GET['resent'] : false;

// Get verification data
$verification_data = null;
$email = '';
$user = null;

// If user_id is provided, get user data directly
if ($user_id > 0) {
    $user = get_user_by('ID', $user_id);
    if ($user) {
        $email = $user->user_email;
        $verification_data = array(
            'user_id' => $user_id,
            'email' => $email,
            'username' => $user->user_login
        );
    }
} elseif (!empty($verification_key)) {
    // Legacy support for transient-based verification
    $verification_data = get_transient($verification_key);
    if ($verification_data) {
        $email = $verification_data['email'];
        if (isset($verification_data['user_id'])) {
            $user_id = intval($verification_data['user_id']);
            $user = get_user_by('ID', $user_id);
        }
    }
}

// Error messages
$error_message = '';
switch ($error) {
    case 'security':
        $error_message = 'Security check failed. Please try again.';
        break;
    case 'missing':
        $error_message = 'Please enter the verification code.';
        break;
    case 'invalid':
        $error_message = 'Invalid verification code. Please check and try again.';
        break;
    case 'send':
        $error_message = 'Failed to send verification email. Please try again.';
        break;
    default:
        if (!empty($error) && $error !== 'security' && $error !== 'missing' && $error !== 'invalid' && $error !== 'send') {
            $error_message = urldecode($error);
        }
        break;
}

// Success message
$success_message = '';
if ($resent) {
    $success_message = 'Verification code has been resent to your email!';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your Email - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        /* Email Verification Page Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }
        
        .nymia-verify-container {
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
        }
        
        .nymia-verify-card {
            background: linear-gradient(135deg, rgba(191, 76, 26, 0.1) 0%, rgba(25, 25, 30, 0.9) 100%);
            border: 1px solid rgba(191, 76, 26, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .nymia-verify-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .nymia-verify-logo {
            font-size: 32px;
            font-weight: bold;
            color: #BF4C1A;
            margin-bottom: 10px;
        }
        
        .nymia-verify-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .nymia-verify-subtitle {
            font-size: 14px;
            color: #a0a0a0;
            line-height: 1.6;
        }
        
        .nymia-verify-message {
            background: rgba(191, 76, 26, 0.1);
            border-left: 3px solid #BF4C1A;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .nymia-verify-message.error {
            background: rgba(220, 50, 50, 0.1);
            border-left-color: #dc3232;
        }
        
        .nymia-verify-message.success {
            background: rgba(50, 220, 50, 0.1);
            border-left-color: #32dc32;
        }
        
        .nymia-verify-form {
            margin-bottom: 20px;
        }
        
        .nymia-verify-form-group {
            margin-bottom: 20px;
        }
        
        .nymia-verify-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #e0e0e0;
        }
        
        .nymia-verify-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 18px;
            color: #ffffff;
            text-align: center;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            transition: all 0.3s ease;
        }
        
        .nymia-verify-input:focus {
            outline: none;
            border-color: #BF4C1A;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(191, 76, 26, 0.2);
        }
        
        .nymia-verify-btn {
            width: 100%;
            background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 12px;
        }
        
        .nymia-verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(191, 76, 26, 0.4);
        }
        
        .nymia-verify-btn:active {
            transform: translateY(0);
        }
        
        .nymia-resend-btn {
            width: 100%;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 14px;
            font-size: 14px;
            font-weight: 500;
            color: #BF4C1A;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .nymia-resend-btn:hover {
            background: rgba(191, 76, 26, 0.1);
            border-color: #BF4C1A;
        }
        
        .nymia-verify-back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .nymia-verify-back-link a {
            color: #BF4C1A;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .nymia-verify-back-link a:hover {
            color: #d44a1e;
        }
        
        .nymia-verify-expired {
            text-align: center;
            padding: 20px;
        }
        
        .nymia-verify-expired-icon {
            margin-bottom: 20px;
            color: rgba(191, 76, 26, 0.3);
            display: flex;
            justify-content: center;
        }
        
        .nymia-verify-expired h2 {
            margin-bottom: 10px;
        }
        
        .nymia-verify-expired p {
            color: #a0a0a0;
            margin-bottom: 20px;
        }
        
        @media (max-width: 480px) {
            .nymia-verify-container {
                padding: 20px;
            }
            
            .nymia-verify-card {
                padding: 30px 20px;
            }
            
            .nymia-verify-title {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="nymia-verify-container">
        <?php if ($verification_data): ?>
            <!-- Verification Form -->
            <div class="nymia-verify-card">
                <div class="nymia-verify-header">
                    <div class="nymia-verify-logo">nymia</div>
                    <h1 class="nymia-verify-title">Verify Your Email</h1>
                    <p class="nymia-verify-subtitle">
                        We've sent a verification code to<br>
                        <strong><?php echo esc_html($email); ?></strong>
                    </p>
                </div>
                
                <?php if ($error_message): ?>
                    <div class="nymia-verify-message error">
                        <?php echo esc_html($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="nymia-verify-message success">
                        <?php echo esc_html($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="nymia-verify-form">
                    <?php wp_nonce_field('nymia_verify_email', 'nymia_verify_nonce'); ?>
                    <input type="hidden" name="action" value="nymia_verify_email">
                    <?php if ($user_id > 0): ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
                    <?php else: ?>
                        <input type="hidden" name="verification_key" value="<?php echo esc_attr($verification_key); ?>">
                    <?php endif; ?>
                    
                    <div class="nymia-verify-form-group">
                        <label for="verification_code" class="nymia-verify-label">Enter Verification Code</label>
                        <input 
                            type="text" 
                            id="verification_code" 
                            name="verification_code" 
                            class="nymia-verify-input" 
                            placeholder="000000" 
                            maxlength="6" 
                            pattern="[0-9]{6}" 
                            required 
                            autocomplete="off"
                            autofocus
                        >
                    </div>
                    
                    <button type="submit" class="nymia-verify-btn">Verify Email</button>
                </form>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('nymia_resend_code', 'nymia_resend_nonce'); ?>
                    <input type="hidden" name="action" value="nymia_resend_code">
                    <?php if ($user_id > 0): ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
                    <?php else: ?>
                        <input type="hidden" name="verification_key" value="<?php echo esc_attr($verification_key); ?>">
                    <?php endif; ?>
                    
                    <button type="submit" class="nymia-resend-btn">Resend Code</button>
                </form>
                
                <div class="nymia-verify-back-link">
                    <a href="<?php echo home_url('/'); ?>">← Back to Sign Up</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Expired or Invalid Key -->
            <div class="nymia-verify-card">
                <div class="nymia-verify-expired">
                    <div class="nymia-verify-expired-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h2>Verification Expired</h2>
                    <p>Your verification link has expired or is invalid.</p>
                    <a href="<?php echo home_url('/'); ?>" class="nymia-verify-btn" style="text-decoration: none; display: inline-block; text-align: center;">Sign Up Again</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-format verification code input
        const verificationInput = document.getElementById('verification_code');
        
        if (verificationInput) {
            verificationInput.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto-submit when 6 digits are entered
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
            
            // Paste handler
            verificationInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                this.value = pastedText.replace(/[^0-9]/g, '').substring(0, 6);
                
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
        }
    </script>
</body>
</html>

