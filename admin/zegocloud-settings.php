<?php
if (!defined('ABSPATH')) { exit; }

function nymia_zegocloud_settings_page() {
    if (isset($_GET['settings-updated'])) {
        add_settings_error('nymia_zegocloud_messages', 'nymia_message', __('ZEGO Cloud settings saved.', 'nymia'), 'updated');
    }
    settings_errors('nymia_zegocloud_messages');

    $app_id  = trim(get_option('nymia_zego_app_id', ''));
    $secret  = trim(get_option('nymia_zego_server_secret', ''));
    $env     = get_option('nymia_zego_env', 'production');

    $status = 'warning';
    $status_text = __('Configuration Incomplete', 'nymia');
    if ($app_id && $secret) {
        $status = 'success';
        $status_text = __('Ready for Token Generation', 'nymia');
    } elseif ($app_id || $secret) {
        $status = 'info';
        $status_text = __('Partially Configured', 'nymia');
    }

    $test_notice = '';
    if (isset($_POST['nymia_test_zego']) && check_admin_referer('nymia_test_zego')) {
        $errors = array();
        if ($app_id === '' || !ctype_digit($app_id)) {
            $errors[] = __('App ID must be numeric and not empty.', 'nymia');
        }
        if ($secret === '' || strlen($secret) < 8) {
            $errors[] = __('Server Secret must be at least 8 characters.', 'nymia');
        }
        if (empty($errors)) {
            $test_notice = '<div class="notice notice-success"><p><strong>✅ ' . esc_html__('Basic validation passed.', 'nymia') . '</strong> ' . esc_html__('App ID and Server Secret look valid.', 'nymia') . '</p><p>' . esc_html__('Next step: generate access tokens server-side and initialize ZEGO UIKit on the front-end.', 'nymia') . '</p></div>';
        } else {
            $test_notice = '<div class="notice notice-error"><p><strong>❌ ' . esc_html__('Validation failed', 'nymia') . '</strong></p><ul>';
            foreach ($errors as $error) {
                $test_notice .= '<li>' . esc_html($error) . '</li>';
            }
            $test_notice .= '</ul></div>';
        }
    }
    nymia_render_admin_settings_styles();
    ?>
    <div class="wrap nymia-stripe-wrap">
        <div class="nymia-stripe-header">
            <div>
                <h1><?php esc_html_e('ZEGO Cloud Live Stream', 'nymia'); ?></h1>
                <p><?php esc_html_e('Connect your live streaming credentials to enable high-quality broadcasts and rooms.', 'nymia'); ?></p>
            </div>
        </div>

        <nav class="nymia-dashboard-nav">
            <a href="?page=nymia-theme-settings"><?php esc_html_e('Dashboard Overview', 'nymia'); ?></a>
            <a href="?page=nymia-general-settings"><?php esc_html_e('General Settings', 'nymia'); ?></a>
            <a href="?page=nymia-social-login-settings"><?php esc_html_e('Social Login', 'nymia'); ?></a>
            <a class="active" href="?page=nymia-zegocloud-settings"><?php esc_html_e('ZEGO Cloud', 'nymia'); ?></a>
            <a href="?page=nymia-stripe-settings"><?php esc_html_e('Stripe Payments', 'nymia'); ?></a>
        </nav>

        <div class="nymia-grid nymia-stripe-grid">
            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Credentials', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Paste your ZEGO Cloud identifiers used for token generation and SDK initialization.', 'nymia'); ?></p>
                </header>
                <form method="post" action="options.php" class="nymia-stripe-form">
                    <?php
                        settings_fields('nymia_zegocloud_settings');
                        do_settings_sections('nymia_zegocloud_settings');
                    ?>
                    <div class="nymia-form-grid">
                        <div class="nymia-form-field">
                            <label for="nymia_zego_app_id"><?php esc_html_e('App ID', 'nymia'); ?></label>
                            <input type="text" id="nymia_zego_app_id" name="nymia_zego_app_id" value="<?php echo esc_attr($app_id); ?>" placeholder="e.g. 123456789">
                            <p class="description"><?php esc_html_e('Numeric App ID from ZEGO Cloud Console.', 'nymia'); ?></p>
                        </div>
                        <div class="nymia-form-field">
                            <label for="nymia_zego_server_secret"><?php esc_html_e('Server Secret', 'nymia'); ?></label>
                            <input type="password" id="nymia_zego_server_secret" name="nymia_zego_server_secret" value="<?php echo esc_attr($secret); ?>" placeholder="<?php esc_attr_e('Server Secret', 'nymia'); ?>">
                            <p class="description"><?php esc_html_e('Keep this secure — used to generate access tokens.', 'nymia'); ?></p>
                        </div>
                    </div>

                    <div class="nymia-form-field">
                        <label for="nymia_zego_env"><?php esc_html_e('Environment', 'nymia'); ?></label>
                        <select id="nymia_zego_env" name="nymia_zego_env">
                            <option value="production" <?php selected($env, 'production'); ?>><?php esc_html_e('Production', 'nymia'); ?></option>
                            <option value="test" <?php selected($env, 'test'); ?>><?php esc_html_e('Test / Sandbox', 'nymia'); ?></option>
                        </select>
                    </div>

                    <?php submit_button(__('Save ZEGO Settings', 'nymia'), 'primary', 'submit', false, array('class' => 'nymia-primary-btn')); ?>
                </form>
            </section>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Connection Status', 'nymia'); ?></h2>
                    <span class="nymia-status-pill <?php echo esc_attr('is-' . $status); ?>">
                        <?php echo esc_html($status_text); ?>
                    </span>
                </header>
                <div class="nymia-card-subtitle">
                    <?php if ($status === 'success') : ?>
                        <p><?php esc_html_e('Credentials look good. Generate tokens server-side and initialize the UIKit to go live.', 'nymia'); ?></p>
                    <?php elseif ($status === 'info') : ?>
                        <p><?php esc_html_e('Finish filling both the App ID and Server Secret to complete setup.', 'nymia'); ?></p>
                    <?php else : ?>
                        <p><?php esc_html_e('Add your ZEGO credentials to unlock live streaming features.', 'nymia'); ?></p>
                    <?php endif; ?>
                </div>
                <ol class="nymia-guide-list">
                    <li><?php esc_html_e('Log in to ZEGO Cloud Console and create an application.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Copy the App ID and Server Secret into the form on this page.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Generate access tokens in WordPress when users join a room.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Load the ZEGO UIKit Web SDK on your streaming pages.', 'nymia'); ?></li>
                </ol>
                <a class="nymia-link-btn" href="https://docs.zegocloud.com/" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('ZEGO Cloud Documentation', 'nymia'); ?>
                </a>
            </section>

            <section class="nymia-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Basic Validation', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Run a quick check to ensure your App ID and Server Secret look correct.', 'nymia'); ?></p>
                </header>
                <?php echo $test_notice; ?>
                <form method="post" class="nymia-stripe-form">
                    <?php wp_nonce_field('nymia_test_zego'); ?>
                    <button type="submit" name="nymia_test_zego" value="1" class="nymia-primary-btn"><?php esc_html_e('Run Basic Check', 'nymia'); ?></button>
                </form>
            </section>
        </div>
    </div>
    <?php
}


