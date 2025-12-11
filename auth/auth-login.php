<?php
/**
 * ========================================
 * NYMIA THEME - LOGIN PAGE TEMPLATE
 * ========================================
 * Handles user authentication display
 * Shows login/signup forms with tab switching
 * Redirects logged-in users to dashboard
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// REDIRECT: Logged-in users to dashboard
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php bloginfo('name'); ?> - Login</title>
    <?php wp_head(); ?>
    
    <?php require_once(get_template_directory() . '/auth/auth-styles.php'); ?>
</head>
<body class="nymia-login-body" style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;">

<div class="nymia-login-container">
    <div class="nymia-login-header">
        <div class="nymia-login-logo">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nymia-logo.jpg'); ?>" alt="Nymia Logo" onerror="this.onerror=null; this.src='<?php echo esc_url(get_template_directory_uri() . '/assets/images/dashboardLogo-Bt6G_8pS.png'); ?>';" />
        </div>
        <h1 class="nymia-login-title">Welcome to Nymia</h1>
        <p class="nymia-login-subtitle">Sign in to your account or create a new one</p>
    </div>
    
    <div class="nymia-tabs">
        <button class="nymia-tab-button<?php echo (!isset($_GET['registration']) || $_GET['registration'] != 'error') ? ' active' : ''; ?>" onclick="switchTab('login')">Sign In</button>
        <button class="nymia-tab-button<?php echo (isset($_GET['registration']) && $_GET['registration'] == 'error') ? ' active' : ''; ?>" onclick="switchTab('signup')">Sign Up</button>
    </div>
    
    <!-- Login Form -->
    <div id="login-form" class="nymia-tab-content<?php echo (!isset($_GET['registration']) || $_GET['registration'] != 'error') ? ' active' : ''; ?>">
        <?php 
        $login_failed = isset($_GET['login']) && $_GET['login'] == 'failed';
        if ($login_failed): ?>
            <div class="nymia-error-message">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Invalid username or password. Please try again.</span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['loggedout']) && $_GET['loggedout'] == 'true'): ?>
            <div class="nymia-success-message">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>You have been successfully logged out.</span>
            </div>
        <?php endif; ?>
        
        <form method="post" action="" id="loginform">
            <input type="hidden" name="nymia_login" value="1">
            
            <div class="nymia-form-group">
                <label class="nymia-form-label" for="user_login">Username or Email</label>
                <input type="text" name="log" id="user_login" class="nymia-form-input" placeholder="Enter your username or email" required autocomplete="username">
            </div>
            
            <div class="nymia-form-group">
                <label class="nymia-form-label" for="user_pass">Password</label>
                <div class="nymia-password-input-wrapper">
                <input type="password" name="pwd" id="user_pass" class="nymia-form-input" placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="nymia-password-toggle" id="toggle-password" aria-label="Show password">
                        <svg class="nymia-eye-icon nymia-eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="nymia-eye-icon nymia-eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="nymia-remember-me">
                <input type="checkbox" name="rememberme" id="rememberme" value="forever">
                <label for="rememberme">Remember me</label>
            </div>
            
            <button type="submit" class="nymia-submit-button">Sign In</button>
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
            <a href="#" id="show-forgot-password" onclick="event.preventDefault(); switchTab('forgot-password');">Forgot your password?</a>
        </div>
        
        <!-- Forgot Password Form -->
        <div id="forgot-password-form" class="nymia-tab-content">
            <div class="nymia-forgot-password-header">
                <h2 style="font-size: 1.5rem; font-weight: 600; color: #fff; margin: 0 0 8px 0;">Reset Password</h2>
                <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin: 0 0 24px 0;">Enter your email address and we'll send you a link to reset your password.</p>
            </div>
            
            <div id="forgot-password-message" style="display: none; margin-bottom: 20px;"></div>
            
            <form id="forgot-password-form-element" method="post">
                <?php wp_nonce_field('nymia_forgot_password', 'nymia_forgot_password_nonce'); ?>
                <input type="hidden" name="action" value="nymia_forgot_password">
                
                <div class="nymia-form-group">
                    <label class="nymia-form-label" for="forgot_email">Email Address</label>
                    <input type="email" name="user_email" id="forgot_email" class="nymia-form-input" placeholder="Enter your email address" required autocomplete="email">
                </div>
                
                <button type="submit" class="nymia-submit-button" id="forgot-password-submit">
                    <span class="submit-text">Send Reset Link</span>
                    <span class="submit-loading" style="display: none;">
                        <svg style="width: 16px; height: 16px; animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                        </svg>
                        Sending...
                    </span>
                </button>
            </form>
            
            <div class="nymia-forgot-password" style="margin-top: 20px;">
                <a href="#" onclick="event.preventDefault(); switchTab('login');">← Back to Sign In</a>
            </div>
        </div>
    </div>
    
    <!-- Include Signup Form -->
    <?php require_once(get_template_directory() . '/auth/auth-signup.php'); ?>
</div>

<?php require_once(get_template_directory() . '/auth/auth-scripts.php'); ?>

<?php wp_footer(); ?>
</body>
</html>

