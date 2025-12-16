<?php
/**
 * Admin Menu Registration
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu for theme settings
 */
function nymia_add_admin_menu() {
    add_menu_page(
        'Nymia Theme Settings',
        'Nymia Settings',
        'manage_options',
        'nymia-theme-settings',
        'nymia_theme_settings_page',
        'dashicons-admin-generic',
        30
    );
    
    add_submenu_page(
        'nymia-theme-settings',
        'Dashboard Overview',
        'Dashboard Overview',
        'manage_options',
        'nymia-theme-settings',
        'nymia_theme_settings_page'
    );
    
    add_submenu_page(
        'nymia-theme-settings',
        'General Settings',
        'General',
        'manage_options',
        'nymia-general-settings',
        'nymia_general_settings_page'
    );
    
    add_submenu_page(
        'nymia-theme-settings',
        'Social Login Settings',
        'Social Login',
        'manage_options',
        'nymia-social-login-settings',
        'nymia_social_login_settings_page'
    );

    add_submenu_page(
        'nymia-theme-settings',
        'ZEGO Cloud Settings',
        'ZEGO Cloud',
        'manage_options',
        'nymia-zegocloud-settings',
        'nymia_zegocloud_settings_page'
    );

    add_submenu_page(
        'nymia-theme-settings',
        'Stripe Payments',
        'Stripe Payments',
        'manage_options',
        'nymia-stripe-settings',
        'nymia_stripe_settings_page'
    );

    add_submenu_page(
        'nymia-theme-settings',
        'Email Templates Settings',
        'Email Templates',
        'manage_options',
        'nymia-email-templates-settings',
        'nymia_email_templates_settings_page'
    );

    add_submenu_page(
        'nymia-theme-settings',
        'Footer Menu Settings',
        'Footer Menu',
        'manage_options',
        'nymia-footer-menu-settings',
        'nymia_footer_menu_settings_page'
    );

    add_submenu_page(
        'nymia-theme-settings',
        'User Management',
        'User Management',
        'manage_options',
        'nymia-user-management',
        'nymia_user_management_page'
    );

}
add_action('admin_menu', 'nymia_add_admin_menu');

function nymia_sanitize_stripe_mode($value) {
    return in_array($value, array('test', 'live'), true) ? $value : 'test';
}

function nymia_sanitize_stripe_currency($value) {
    $value = strtolower(sanitize_text_field($value));
    return preg_match('/^[a-z]{3}$/', $value) ? $value : 'usd';
}

function nymia_sanitize_checkbox($value) {
    return $value ? 1 : 0;
}

if (!function_exists('nymia_render_admin_settings_styles')) {
    function nymia_render_admin_settings_styles() {
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        ?>
        <style>
            .wrap.nymia-stripe-wrap {
                background: #0b0b0d;
                border-radius: 18px;
                padding: 32px;
                margin-top: 30px;
                box-shadow: 0 25px 80px rgba(0, 0, 0, 0.45);
                color: #f5f5f7;
            }

            .wrap.nymia-stripe-wrap.nymia-dashboard-overview {
                padding: 20px 20px;
                margin: 0;
                margin-top: 20px;
                margin-right: 20px;
            }

            .nymia-stripe-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 24px;
                margin-bottom: 32px;
            }

            .nymia-stripe-header h1 {
                margin: 0;
                font-size: 2rem;
                font-weight: 700;
                color: #ffffff;
            }

            .nymia-stripe-header p {
                margin: 6px 0 0;
                color: rgba(255, 255, 255, 0.65);
                max-width: 640px;
            }

            .nymia-stripe-actions {
                display: flex;
                gap: 16px;
            }

            .wrap.nymia-stripe-wrap .nymia-secondary-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 12px 24px;
                border-radius: 999px;
                background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
                color: #fff;
                font-weight: 600;
                text-decoration: none;
                border: none;
                box-shadow: 0 12px 30px rgba(191, 76, 26, 0.35);
                transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
            }

            .wrap.nymia-stripe-wrap .nymia-secondary-btn:hover,
            .wrap.nymia-stripe-wrap .nymia-secondary-btn:focus {
                transform: translateY(-1px);
                box-shadow: 0 16px 36px rgba(191, 76, 26, 0.45);
                opacity: 0.95;
                color: #fff;
            }

            .wrap.nymia-stripe-wrap .button.button-primary,
            .wrap.nymia-stripe-wrap .button.button-primary:visited {
                background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
                border: none;
                border-radius: 999px;
                color: #fff;
                font-weight: 600;
                padding: 12px 28px;
                box-shadow: 0 12px 30px rgba(191, 76, 26, 0.35);
                transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
            }

            .wrap.nymia-stripe-wrap.nymia-general-settings .button.button-primary,
            .wrap.nymia-stripe-wrap.nymia-general-settings .button.button-primary:visited,
            .wrap.nymia-stripe-wrap.nymia-email-templates-settings .button.button-primary,
            .wrap.nymia-stripe-wrap.nymia-email-templates-settings .button.button-primary:visited {
                padding: 10px 18px;
                height: 45px;
                font-size: 0.8125rem;
                box-shadow: 0 8px 18px rgba(191, 76, 26, 0.28);
            }

            .wrap.nymia-stripe-wrap .button.button-primary:hover,
            .wrap.nymia-stripe-wrap .button.button-primary:focus {
                transform: translateY(-1px);
                box-shadow: 0 16px 36px rgba(191, 76, 26, 0.45);
                opacity: 0.95;
                color: #fff;
            }

            .nymia-dashboard-nav {
                display: flex;
                gap: 16px;
                margin-bottom: 32px;
                flex-wrap: wrap;
            }

            .nymia-dashboard-nav a {
                padding: 10px 18px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.06);
                color: rgba(255, 255, 255, 0.7);
                text-decoration: none;
                transition: all 0.25s ease;
            }

            .nymia-dashboard-nav a:hover {
                color: #fff;
                background: rgba(191, 76, 26, 0.4);
            }

            .nymia-dashboard-nav a.active {
                background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
                color: #fff;
            }

            .nymia-dashboard-nav.nymia-nav-loading {
                opacity: 0.7;
                pointer-events: none;
            }

            .nymia-dashboard-nav .nymia-nav-link-loading {
                position: relative;
            }

            .nymia-dashboard-nav .nymia-nav-link-loading::after {
                content: '';
                position: absolute;
                right: -22px;
                top: 50%;
                width: 14px;
                height: 14px;
                border: 2px solid rgba(255, 255, 255, 0.6);
                border-top-color: transparent;
                border-radius: 50%;
                animation: nymia-spin 0.8s linear infinite;
                transform: translateY(-50%);
            }

            .nymia-stripe-grid {
                display: grid;
                gap: 24px;
            }

            @keyframes nymia-spin {
                from {
                    transform: translateY(-50%) rotate(0deg);
                }
                to {
                    transform: translateY(-50%) rotate(360deg);
                }
            }

            @media (min-width: 1000px) {
                .nymia-stripe-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .nymia-stripe-form-card {
                    grid-column: span 2;
                }
            }

            .nymia-card {
                background: linear-gradient(145deg, rgba(24, 24, 28, 0.95), rgba(15, 15, 17, 0.95));
                border: 1px solid rgba(255, 255, 255, 0.06);
                border-radius: 20px;
                padding: 28px;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }

            .nymia-card-header {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-bottom: 16px;
            }

            .nymia-card-header h2 {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 700;
                color: #ffffff;
            }

            .nymia-card-header p {
                margin: 10px 0 0;
                color: rgba(255, 255, 255, 0.6);
            }

            .nymia-card-subtitle {
                color: rgba(255, 255, 255, 0.65);
                margin: 12px 0 18px;
            }

            .nymia-card-subtitle code {
                display: inline-flex;
                padding: 4px 8px;
                border-radius: 6px;
                background: rgba(255, 255, 255, 0.08);
                color: rgba(255, 255, 255, 0.8);
                font-size: 0.85rem;
            }

            .wrap.nymia-stripe-wrap .notice {
                border-radius: 14px;
                padding: 16px 18px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                background: rgba(17, 17, 19, 0.85);
                color: rgba(255, 255, 255, 0.75);
                box-shadow: 0 16px 35px rgba(0, 0, 0, 0.35);
            }

            .wrap.nymia-stripe-wrap .notice.notice-success {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.18), rgba(4, 120, 87, 0.24));
                border-color: rgba(16, 185, 129, 0.35);
                color: #d1fae5;
            }

            .wrap.nymia-stripe-wrap .notice.notice-error {
                background: linear-gradient(135deg, rgba(248, 113, 113, 0.18), rgba(220, 38, 38, 0.24));
                border-color: rgba(220, 38, 38, 0.35);
                color: #fee2e2;
            }

            .nymia-status-pill {
                border-radius: 999px;
                padding: 6px 14px;
                font-size: 0.8rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .nymia-status-pill.is-success {
                background: rgba(16, 185, 129, 0.18);
                color: #34d399;
            }

            .nymia-status-pill.is-warning {
                background: rgba(251, 191, 36, 0.18);
                color: #fbbf24;
            }

            .nymia-status-pill.is-info {
                background: rgba(96, 165, 250, 0.18);
                color: #93c5fd;
            }

            .nymia-status-meta {
                display: grid;
                gap: 16px;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                margin-top: 20px;
            }

            .nymia-meta-label {
                display: block;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: rgba(255, 255, 255, 0.45);
            }

            .nymia-meta-value {
                font-size: 0.95rem;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.85);
            }

            .nymia-stripe-form {
                display: flex;
                flex-direction: column;
                gap: 22px;
            }

            .nymia-form-grid {
                display: grid;
                gap: 22px;
            }

            @media (min-width: 768px) {
                .nymia-form-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            .nymia-form-field label {
                display: block;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.85);
                margin-bottom: 8px;
            }

            .nymia-form-field input,
            .nymia-form-field select,
            .nymia-form-field textarea {
                width: 100%;
                padding: 12px 14px;
                border-radius: 10px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #fff;
                transition: all 0.2s ease;
            }

            .nymia-form-field textarea {
                min-height: 140px;
                resize: vertical;
            }

            .nymia-form-field input:focus,
            .nymia-form-field select:focus,
            .nymia-form-field textarea:focus {
                border-color: rgba(191, 76, 26, 0.6);
                box-shadow: 0 0 0 3px rgba(191, 76, 26, 0.2);
                outline: none;
            }

            .nymia-form-field .description {
                color: rgba(255, 255, 255, 0.45);
                margin-top: 8px;
            }

            .nymia-primary-btn {
                align-self: flex-start;
                background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
                border: none;
                border-radius: 999px;
                padding: 12px 28px;
                font-weight: 600;
                font-size: 0.95rem;
                cursor: pointer;
                color: #fff;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .nymia-primary-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 25px rgba(191, 76, 26, 0.35);
            }

            .nymia-link-btn {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                color: #ffb080;
                text-decoration: none;
                font-weight: 600;
                margin-top: 16px;
                transition: color 0.2s ease;
            }

            .nymia-link-btn::after {
                content: '↗';
                font-size: 0.75rem;
            }

            .nymia-link-btn:hover,
            .nymia-link-btn:focus {
                color: #ffd7b3;
            }

            .nymia-guide-list {
                counter-reset: step;
                padding-left: 18px;
            }

            .nymia-guide-list li {
                margin-bottom: 10px;
                color: rgba(255, 255, 255, 0.7);
            }

            .nymia-pill-list {
                list-style: none;
                padding: 0;
                margin: 0 0 18px;
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            .nymia-pill-list li {
                padding: 10px 16px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.08);
                color: rgba(255, 255, 255, 0.75);
                font-size: 0.85rem;
                border: 1px solid rgba(255, 255, 255, 0.08);
            }

            .nymia-toggle {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                cursor: pointer;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.85);
            }

            .nymia-toggle input[type="checkbox"] {
                width: 22px;
                height: 22px;
                border-radius: 6px;
                border: 1px solid rgba(255, 255, 255, 0.25);
                background: rgba(255, 255, 255, 0.05);
                accent-color: #BF4C1A;
            }

            @media (max-width: 782px) {
                .wrap.nymia-stripe-wrap {
                    padding: 24px;
                }

                .nymia-stripe-header {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .nymia-dashboard-nav {
                    gap: 12px;
                }

                .nymia-dashboard-nav a {
                    padding: 8px 14px;
                    font-size: 0.85rem;
                }
            }
        </style>
        <?php
    }
}

/**
 * Register theme settings
 */
function nymia_register_settings() {
    // Register settings
    register_setting('nymia_dashboard_settings', 'nymia_hero_title');
    register_setting('nymia_dashboard_settings', 'nymia_hero_subtitle');
    register_setting('nymia_dashboard_settings', 'nymia_hero_button_text');
    register_setting('nymia_dashboard_settings', 'nymia_hero_button_link');
    
    // General settings
    register_setting('nymia_general_settings', 'nymia_primary_color');
    register_setting('nymia_general_settings', 'nymia_logo_url');
    register_setting('nymia_general_settings', 'nymia_footer_text');
    register_setting('nymia_general_settings', 'nymia_enable_audio_upload');
    
    // Email verification settings
    register_setting('nymia_general_settings', 'nymia_enable_email_verification');
    register_setting('nymia_general_settings', 'nymia_verification_code_expiry');
    
    // Social login settings
    register_setting('nymia_social_login_settings', 'nymia_google_client_id');
    register_setting('nymia_social_login_settings', 'nymia_google_client_secret');
    register_setting('nymia_social_login_settings', 'nymia_facebook_app_id');
    register_setting('nymia_social_login_settings', 'nymia_facebook_app_secret');
    register_setting('nymia_social_login_settings', 'nymia_apple_client_id');
    register_setting('nymia_social_login_settings', 'nymia_apple_team_id');
    register_setting('nymia_social_login_settings', 'nymia_apple_key_id');
    register_setting('nymia_social_login_settings', 'nymia_apple_private_key');
    register_setting('nymia_social_login_settings', 'nymia_google_enabled');
    register_setting('nymia_social_login_settings', 'nymia_facebook_enabled');
    register_setting('nymia_social_login_settings', 'nymia_apple_enabled');

    // ZEGO Cloud settings
    register_setting('nymia_zegocloud_settings', 'nymia_zego_app_id');
    register_setting('nymia_zegocloud_settings', 'nymia_zego_server_secret');
    register_setting('nymia_zegocloud_settings', 'nymia_zego_env');

    // Stream chat settings
    register_setting('nymia_stream_settings', 'nymia_stream_enable', array('sanitize_callback' => 'nymia_sanitize_checkbox', 'default' => 0));
    register_setting('nymia_stream_settings', 'nymia_stream_api_key', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('nymia_stream_settings', 'nymia_stream_api_secret', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('nymia_stream_settings', 'nymia_stream_region', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'us-east'));
    register_setting('nymia_stream_settings', 'nymia_stream_default_audio', array('sanitize_callback' => 'nymia_sanitize_checkbox', 'default' => 1));
    register_setting('nymia_stream_settings', 'nymia_stream_default_video', array('sanitize_callback' => 'nymia_sanitize_checkbox', 'default' => 1));
    register_setting('nymia_stream_settings', 'nymia_stream_enable_guest', array('sanitize_callback' => 'nymia_sanitize_checkbox', 'default' => 0));

    // Stripe payment settings
    register_setting('nymia_stripe_settings', 'nymia_stripe_mode', array('sanitize_callback' => 'nymia_sanitize_stripe_mode', 'default' => 'test'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_publishable_key', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_secret_key', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_webhook_secret', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_success_url', array('sanitize_callback' => 'esc_url_raw'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_cancel_url', array('sanitize_callback' => 'esc_url_raw'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_connect_client_id', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_currency', array('sanitize_callback' => 'nymia_sanitize_stripe_currency', 'default' => 'usd'));
    register_setting('nymia_stripe_settings', 'nymia_stripe_statement_descriptor', array('sanitize_callback' => 'sanitize_text_field'));
}
add_action('admin_init', 'nymia_register_settings');



