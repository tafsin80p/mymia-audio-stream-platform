<?php
/**
 * Social Login Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Social login settings page
 */
function nymia_social_login_settings_page() {
    // Handle form submission
    if (isset($_POST['submit'])) {
        // Save Google settings
        update_option('nymia_google_client_id', sanitize_text_field($_POST['nymia_google_client_id'] ?? ''));
        update_option('nymia_google_client_secret', sanitize_text_field($_POST['nymia_google_client_secret'] ?? ''));
        update_option('nymia_google_enabled', isset($_POST['nymia_google_enabled']) ? '1' : '0');
        
        // Save Facebook settings
        update_option('nymia_facebook_app_id', sanitize_text_field($_POST['nymia_facebook_app_id'] ?? ''));
        update_option('nymia_facebook_app_secret', sanitize_text_field($_POST['nymia_facebook_app_secret'] ?? ''));
        update_option('nymia_facebook_enabled', isset($_POST['nymia_facebook_enabled']) ? '1' : '0');
        
        // Save Apple settings
        update_option('nymia_apple_client_id', sanitize_text_field($_POST['nymia_apple_client_id'] ?? ''));
        update_option('nymia_apple_team_id', sanitize_text_field($_POST['nymia_apple_team_id'] ?? ''));
        update_option('nymia_apple_key_id', sanitize_text_field($_POST['nymia_apple_key_id'] ?? ''));
        update_option('nymia_apple_private_key', sanitize_textarea_field($_POST['nymia_apple_private_key'] ?? ''));
        update_option('nymia_apple_enabled', isset($_POST['nymia_apple_enabled']) ? '1' : '0');
        
        echo '<div class="notice notice-success is-dismissible"><p>Social login settings saved successfully!</p></div>';
    }
    
    // Get current settings
    $google_client_id = get_option('nymia_google_client_id', '');
    $google_client_secret = get_option('nymia_google_client_secret', '');
    $google_enabled = get_option('nymia_google_enabled', '0');
    
    $facebook_app_id = get_option('nymia_facebook_app_id', '');
    $facebook_app_secret = get_option('nymia_facebook_app_secret', '');
    $facebook_enabled = get_option('nymia_facebook_enabled', '0');
    
    $apple_client_id = get_option('nymia_apple_client_id', '');
    $apple_team_id = get_option('nymia_apple_team_id', '');
    $apple_key_id = get_option('nymia_apple_key_id', '');
    $apple_private_key = get_option('nymia_apple_private_key', '');
    $apple_enabled = get_option('nymia_apple_enabled', '0');
    nymia_render_admin_settings_styles();
    ?>
    <div class="wrap nymia-stripe-wrap">
        <div class="nymia-stripe-header">
            <div>
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <p><?php esc_html_e('Configure third-party OAuth providers to streamline user sign-in flows.', 'nymia'); ?></p>
            </div>
        </div>

        <nav class="nymia-dashboard-nav">
            <a href="?page=nymia-theme-settings"><?php esc_html_e('Dashboard Overview', 'nymia'); ?></a>
            <a href="?page=nymia-general-settings"><?php esc_html_e('General Settings', 'nymia'); ?></a>
            <a class="active" href="?page=nymia-social-login-settings"><?php esc_html_e('Social Login', 'nymia'); ?></a>
            <a href="?page=nymia-zegocloud-settings"><?php esc_html_e('ZEGO Cloud', 'nymia'); ?></a>
            <a href="?page=nymia-stripe-settings"><?php esc_html_e('Stripe Payments', 'nymia'); ?></a>
        </nav>

        <form method="post" action="" class="nymia-grid nymia-stripe-grid">
            <?php settings_fields('nymia_social_login_settings'); ?>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Google Sign-In', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Connect your Google Cloud OAuth credentials to allow one-click sign-in.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label class="nymia-toggle">
                        <input type="checkbox" id="nymia_google_enabled" name="nymia_google_enabled" value="1" <?php checked($google_enabled, '1'); ?>>
                        <span><?php esc_html_e('Enable Google Login', 'nymia'); ?></span>
                    </label>
                </div>

                <div class="nymia-form-grid">
                    <div class="nymia-form-field">
                        <label for="nymia_google_client_id"><?php esc_html_e('Client ID', 'nymia'); ?></label>
                        <input type="text" id="nymia_google_client_id" name="nymia_google_client_id" value="<?php echo esc_attr($google_client_id); ?>" placeholder="Your Google Client ID">
                        <p class="description"><?php esc_html_e('Create OAuth credentials in Google Cloud Console.', 'nymia'); ?></p>
                    </div>
                    <div class="nymia-form-field">
                        <label for="nymia_google_client_secret"><?php esc_html_e('Client Secret', 'nymia'); ?></label>
                        <input type="password" id="nymia_google_client_secret" name="nymia_google_client_secret" value="<?php echo esc_attr($google_client_secret); ?>" placeholder="Your Google Client Secret">
                    </div>
                </div>

                <div class="nymia-card-subtitle">
                    <strong><?php esc_html_e('Redirect URI:', 'nymia'); ?></strong>
                    <code><?php echo esc_html(home_url('/?nymia_auth=google_callback')); ?></code>
                </div>
                <ol class="nymia-guide-list">
                    <li><?php esc_html_e('Visit Google Cloud Console and create an OAuth 2.0 Client ID.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Add the redirect URI above to the authorized list.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Paste your Client ID and Secret, then save.', 'nymia'); ?></li>
                </ol>
            </section>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Facebook Login', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Allow creators and fans to authenticate with their Facebook accounts.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label class="nymia-toggle">
                        <input type="checkbox" id="nymia_facebook_enabled" name="nymia_facebook_enabled" value="1" <?php checked($facebook_enabled, '1'); ?>>
                        <span><?php esc_html_e('Enable Facebook Login', 'nymia'); ?></span>
                    </label>
                </div>

                <div class="nymia-form-grid">
                    <div class="nymia-form-field">
                        <label for="nymia_facebook_app_id"><?php esc_html_e('App ID', 'nymia'); ?></label>
                        <input type="text" id="nymia_facebook_app_id" name="nymia_facebook_app_id" value="<?php echo esc_attr($facebook_app_id); ?>" placeholder="Your Facebook App ID">
                    </div>
                    <div class="nymia-form-field">
                        <label for="nymia_facebook_app_secret"><?php esc_html_e('App Secret', 'nymia'); ?></label>
                        <input type="password" id="nymia_facebook_app_secret" name="nymia_facebook_app_secret" value="<?php echo esc_attr($facebook_app_secret); ?>" placeholder="Your Facebook App Secret">
                    </div>
                </div>

                <div class="nymia-card-subtitle">
                    <strong><?php esc_html_e('Valid OAuth Redirect URI:', 'nymia'); ?></strong>
                    <code><?php echo esc_html(home_url('/?nymia_auth=facebook_callback')); ?></code>
                </div>
                <ol class="nymia-guide-list">
                    <li><?php esc_html_e('Create an app in Facebook Developers and enable Facebook Login.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Add the redirect URI above to your Facebook Login configuration.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Copy your App ID and Secret into the fields here.', 'nymia'); ?></li>
                </ol>
            </section>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Sign In with Apple', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Offer a privacy-focused login option for Apple users.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label class="nymia-toggle">
                        <input type="checkbox" id="nymia_apple_enabled" name="nymia_apple_enabled" value="1" <?php checked($apple_enabled, '1'); ?>>
                        <span><?php esc_html_e('Enable Apple Login', 'nymia'); ?></span>
                    </label>
                </div>

                <div class="nymia-form-grid">
                    <div class="nymia-form-field">
                        <label for="nymia_apple_client_id"><?php esc_html_e('Client ID (Service ID)', 'nymia'); ?></label>
                        <input type="text" id="nymia_apple_client_id" name="nymia_apple_client_id" value="<?php echo esc_attr($apple_client_id); ?>" placeholder="com.example.service">
                    </div>
                    <div class="nymia-form-field">
                        <label for="nymia_apple_team_id"><?php esc_html_e('Team ID', 'nymia'); ?></label>
                        <input type="text" id="nymia_apple_team_id" name="nymia_apple_team_id" value="<?php echo esc_attr($apple_team_id); ?>" placeholder="ABC123DEF4">
                    </div>
                </div>

                <div class="nymia-form-grid">
                    <div class="nymia-form-field">
                        <label for="nymia_apple_key_id"><?php esc_html_e('Key ID', 'nymia'); ?></label>
                        <input type="text" id="nymia_apple_key_id" name="nymia_apple_key_id" value="<?php echo esc_attr($apple_key_id); ?>" placeholder="XYZ789ABC1">
                    </div>
                    <div class="nymia-form-field">
                        <label for="nymia_apple_private_key"><?php esc_html_e('Private Key', 'nymia'); ?></label>
                        <textarea id="nymia_apple_private_key" name="nymia_apple_private_key" rows="6" placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----"><?php echo esc_textarea($apple_private_key); ?></textarea>
                        <p class="description"><?php esc_html_e('Paste the contents of your Apple .p8 private key file.', 'nymia'); ?></p>
                    </div>
                </div>

                <div class="nymia-card-subtitle">
                    <strong><?php esc_html_e('Redirect URL:', 'nymia'); ?></strong>
                    <code><?php echo esc_html(home_url('/?nymia_auth=apple_callback')); ?></code>
                </div>
                <ol class="nymia-guide-list">
                    <li><?php esc_html_e('Create a Service ID and Sign in with Apple key in Apple Developer.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Add the redirect URL above to the configuration.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Enter your identifiers and paste the private key.', 'nymia'); ?></li>
                </ol>
            </section>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Security Checklist', 'nymia'); ?></h2>
                </header>
                <ul class="nymia-guide-list">
                    <li><?php esc_html_e('Keep all secrets stored securely and rotate them periodically.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Ensure callback URLs exactly match provider settings.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Test each provider after updates using a private window.', 'nymia'); ?></li>
                </ul>
            </section>

            <?php submit_button(__('Save Social Login Settings', 'nymia'), 'primary', 'submit', false, array('class' => 'nymia-primary-btn')); ?>
        </form>
    </div>
    <?php
}

