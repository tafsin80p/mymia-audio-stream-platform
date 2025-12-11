<?php
/**
 * General Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * General settings page
 */
function nymia_general_settings_page() {
    // Handle form submission
    if (isset($_POST['submit'])) {
        // Process and save settings
        if (isset($_POST['nymia_enable_email_verification'])) {
            update_option('nymia_enable_email_verification', '1');
        } else {
            update_option('nymia_enable_email_verification', '0');
        }
        
        if (isset($_POST['nymia_verification_code_expiry'])) {
            update_option('nymia_verification_code_expiry', intval($_POST['nymia_verification_code_expiry']));
        }
        
        // Show success message
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }
    
    // Get current settings
    $enable_verification = get_option('nymia_enable_email_verification', '1');
    $verification_expiry = get_option('nymia_verification_code_expiry', 15);
    nymia_render_admin_settings_styles();
    ?>
    <div class="wrap nymia-stripe-wrap nymia-general-settings">
        <div class="nymia-stripe-header">
            <div>
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <p><?php esc_html_e('Control core platform defaults for onboarding, verification, and upload permissions.', 'nymia'); ?></p>
            </div>
        </div>

        <nav class="nymia-dashboard-nav">
            <a href="?page=nymia-theme-settings"><?php esc_html_e('Dashboard Overview', 'nymia'); ?></a>
            <a class="active" href="?page=nymia-general-settings"><?php esc_html_e('General Settings', 'nymia'); ?></a>
            <a href="?page=nymia-social-login-settings"><?php esc_html_e('Social Login', 'nymia'); ?></a>
            <a href="?page=nymia-zegocloud-settings"><?php esc_html_e('ZEGO Cloud', 'nymia'); ?></a>
            <a href="?page=nymia-stripe-settings"><?php esc_html_e('Stripe Payments', 'nymia'); ?></a>
        </nav>

        <form method="post" action="" class="nymia-grid nymia-stripe-grid">
            <?php settings_fields('nymia_general_settings'); ?>
            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('User Verification', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Manage email confirmation rules and grace periods for new registrations.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label class="nymia-toggle">
                        <input 
                            type="checkbox"
                            id="nymia_enable_email_verification"
                            name="nymia_enable_email_verification"
                            value="1"
                            <?php checked($enable_verification, '1'); ?>
                        >
                        <span><?php esc_html_e('Require email verification for new user registrations', 'nymia'); ?></span>
                    </label>
                    <p class="description">
                        <?php esc_html_e('When enabled, users must confirm their email via a verification code before activation.', 'nymia'); ?>
                    </p>
                </div>

                <div class="nymia-form-field">
                    <label for="nymia_verification_code_expiry"><?php esc_html_e('Verification code expiry (minutes)', 'nymia'); ?></label>
                    <input 
                        type="number"
                        id="nymia_verification_code_expiry"
                        name="nymia_verification_code_expiry"
                        value="<?php echo esc_attr($verification_expiry); ?>"
                        min="5"
                        max="60"
                    >
                    <p class="description">
                        <?php esc_html_e('Define how long a verification code remains valid (5-60 minutes). Default: 15 minutes.', 'nymia'); ?>
                    </p>
                </div>
            </section>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Content Uploads', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Toggle creator audio uploads for the entire platform.', 'nymia'); ?></p>
                </header>

                <div class="nymia-form-field">
                    <label class="nymia-toggle">
                        <input
                            type="checkbox"
                            id="nymia_enable_audio_upload"
                            name="nymia_enable_audio_upload"
                            value="1"
                            <?php checked(get_option('nymia_enable_audio_upload', '1'), '1'); ?>
                        >
                        <span><?php esc_html_e('Allow users to upload audio files', 'nymia'); ?></span>
                    </label>
                </div>
            </section>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Best Practices', 'nymia'); ?></h2>
                </header>
                <ul class="nymia-guide-list">
                    <li><?php esc_html_e('Keep email verification enabled to reduce spam and fraudulent accounts.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Ensure your WordPress email settings are configured so codes can be delivered.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Review audio upload permissions regularly to maintain quality standards.', 'nymia'); ?></li>
                </ul>
            </section>
            <?php submit_button(__('Save General Settings', 'nymia'), 'primary', 'submit', false, array('class' => 'nymia-primary-btn')); ?>
        </form>
    </div>
    <?php
}
