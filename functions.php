<?php
/**
 * NYMIA THEME - MAIN FUNCTIONS FILE
 * ===================================
 * This file contains all the core theme functionality including:
 * - Theme setup and configuration
 * - Custom post/page creation
 * - User authentication handlers
 * - AJAX endpoints for file uploads
 * - Profile management
 * - Dashboard data management
 * 
 * @package Nymia
 * @version 1.0
 */

// ==========================================
// SECURITY: Prevent direct file access
// ==========================================
if (!defined('ABSPATH')) {
    exit;
}

/**
 * HIDE ADMIN BAR FOR ALL USERS (CREATORS AND CLIENTS)
 * ---------------------------------------------------
 * Removes the WordPress admin bar for all users on frontend
 * This ensures WordPress admin bar doesn't appear for creators or clients
 * Hooks into: show_admin_bar
 */
function hide_admin_bar_for_all_users($show) {
    // Hide admin bar for all users on frontend (both creators and clients)
    // Only show admin bar in WordPress admin area
    if (!is_admin()) {
            return false;
    }
    return $show;
}
add_filter('show_admin_bar', 'hide_admin_bar_for_all_users');

/**
 * LOCO TRANSLATE LANGUAGE SWITCHING
 * ----------------------------------
 * Handles language switching for Loco Translate plugin
 * Uses cookie to store language preference
 * Hooks into: locale filter
 * Priority: 1 (runs early to ensure translations load correctly)
 */
function nymia_set_locale_from_cookie($locale) {
    // Only apply on frontend (not in admin)
    if (is_admin()) {
        return $locale;
    }
    
    // Check if language cookie is set
    if (isset($_COOKIE['nymia_language'])) {
        $lang_code = sanitize_text_field($_COOKIE['nymia_language']);
        
        // Map language codes to WordPress locales
        $locale_map = array(
            'en' => 'en_US',
            'it' => 'it_IT',
        );
        
        if (isset($locale_map[$lang_code])) {
            return $locale_map[$lang_code];
        }
    }
    
    return $locale;
}
add_filter('locale', 'nymia_set_locale_from_cookie', 1, 1);

/**
 * RELOAD TEXTDOMAIN AFTER LOCALE CHANGE
 * --------------------------------------
 * Ensures translations are reloaded when locale changes
 * This is important for Loco Translate to work properly
 */
function nymia_reload_textdomain_after_locale_change() {
    // Unload and reload textdomain to ensure fresh translations
    // This runs after locale filter has been applied
    unload_textdomain('nymia');
    load_theme_textdomain('nymia', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'nymia_reload_textdomain_after_locale_change', 20);

/**
 * THEME SETUP FUNCTION
 * --------------------
 * Sets up theme support, navigation menus, and creates initial pages.
 * Hooks into: after_setup_theme
 */
function nymia_theme_setup() {
    // ENABLE THEME FEATURES
    add_theme_support('title-tag');                    // WordPress manages document title
    add_theme_support('post-thumbnails');              // Enable featured images
    add_theme_support('html5', array(
        'search-form',                                  // HTML5 search form markup
        'comment-form',                                 // HTML5 comment form markup
        'comment-list',                                 // HTML5 comment list markup
        'gallery',                                      // HTML5 gallery markup
        'caption',                                      // HTML5 caption markup
    ));
    
    // REGISTER NAVIGATION MENUS
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'nymia'),       // Main site navigation
        'sidebar' => __('Sidebar Menu', 'nymia'),       // Sidebar navigation
    ));
    
    // LOAD THEME TEXTDOMAIN FOR LOCO TRANSLATE
    // This must be called in after_setup_theme hook
    // Loco Translate will automatically create translation files in /languages/ directory
    load_theme_textdomain('nymia', get_template_directory() . '/languages');
    
    // CREATE ESSENTIAL PAGES ON THEME ACTIVATION
    nymia_create_dashboard_page();
} // END: nymia_theme_setup()
add_action('after_setup_theme', 'nymia_theme_setup');

/**
 * Register lightweight social posts for community feed.
 */
function nymia_register_social_post_type() {
    $labels = array(
        'name' => __('Social Posts', 'nymia'),
        'singular_name' => __('Social Post', 'nymia'),
    );

    register_post_type('nymia_social_post', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => false,
        'supports' => array('title', 'editor', 'author', 'thumbnail'),
    ));
}
add_action('init', 'nymia_register_social_post_type');

/**
 * CREATE ESSENTIAL PAGES
 * ----------------------
 * Creates all required theme pages on activation:
 * - Dashboard
 * - Live Audio
 * - Audio Library
 * - Single Audio
 * - Ebook Library
 * - Single Ebook
 * - Profile
 * - Policies
 * - Earnings
 * - Create
 * - Email Verification
 */
function nymia_create_dashboard_page() {
    // ========================================
    // CREATE DASHBOARD PAGE
    // ========================================
    $dashboard_page = get_page_by_path('dashboard');
    
    if (!$dashboard_page) {
        $page_data = array(
            'post_title'    => 'Dashboard',
            'post_content'  => 'Welcome to your Nymia Dashboard. This page contains all your audio content overview.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'dashboard',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        
        // SET DASHBOARD AS FRONT PAGE (if no front page is set)
        if ($page_id && !get_option('page_on_front')) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $page_id);
        }
    }
    
    // ========================================
    // CREATE LIVE AUDIO STREAMING PAGE
    // ========================================
    $live_audio_page = get_page_by_path('live-audio');
    
    if (!$live_audio_page) {
        $live_audio_data = array(
            'post_title'    => 'Live Audio Streaming',
            'post_content'  => 'Live Audio Streaming page with interactive features.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'live-audio',
            'post_author'   => 1,
        );
        
        wp_insert_post($live_audio_data);
    }
    
    // ========================================
    // CREATE EVENT CALENDAR PAGE
    // ========================================
    $event_calendar_page = get_page_by_path('event-calendar');
    
    if (!$event_calendar_page) {
        $event_calendar_data = array(
            'post_title'    => 'Event Calendar',
            'post_content'  => 'Public event calendar showing all scheduled live streams and group events.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'event-calendar',
            'post_author'   => 1,
            'page_template' => 'page-event-calendar.php',
        );
        
        $event_calendar_id = wp_insert_post($event_calendar_data);
        
        // Set the template for the event calendar page
        if ($event_calendar_id) {
            update_post_meta($event_calendar_id, '_wp_page_template', 'page-event-calendar.php');
        }
    } else {
        // Update template if page exists but doesn't have template
        $template = get_post_meta($event_calendar_page->ID, '_wp_page_template', true);
        if ($template !== 'page-event-calendar.php') {
            update_post_meta($event_calendar_page->ID, '_wp_page_template', 'page-event-calendar.php');
        }
    }

    // ========================================
    // CREATE AUDIO LIBRARY PAGE
    // ========================================
    $audio_page = get_page_by_path('audio');
    
    if (!$audio_page) {
        $audio_data = array(
            'post_title'    => 'Audio',
            'post_content'  => 'Browse and listen to audio posts.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'audio',
            'post_author'   => 1,
            'page_template' => 'page-audio.php',
        );
        
        $audio_id = wp_insert_post($audio_data);
    }
    
    // ========================================
    // CREATE SINGLE AUDIO PAGE
    // ========================================
    $single_audio_page = get_page_by_path('single-audio');
    
    if (!$single_audio_page) {
        $single_audio_data = array(
            'post_title'    => 'Single Audio',
            'post_content'  => 'Individual audio creator page with track list.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'single-audio',
            'post_author'   => 1,
        );
        
        $single_audio_id = wp_insert_post($single_audio_data);
        
        // Set the template for the single audio page
        if ($single_audio_id) {
            update_post_meta($single_audio_id, '_wp_page_template', 'page-single-audio.php');
        }
        
        // Set the template for the audio page
        if (isset($audio_id) && $audio_id) {
            update_post_meta($audio_id, '_wp_page_template', 'page-audio.php');
        }
    } else {
        // Always update template to ensure it's correct
        update_post_meta($single_audio_page->ID, '_wp_page_template', 'page-single-audio.php');
        
        // Also update audio page template if needed
        if (isset($audio_page) && $audio_page) {
            update_post_meta($audio_page->ID, '_wp_page_template', 'page-audio.php');
        }
    }
    
    // ========================================
    // CREATE EBOOK LIBRARY PAGE
    // ========================================
    $ebook_page = get_page_by_path('ebook');
    
    if (!$ebook_page) {
        $ebook_data = array(
            'post_title'    => 'Ebook',
            'post_content'  => 'Browse and read premium Ebook content.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'ebook',
            'post_author'   => 1,
            'page_template' => 'page-ebook.php',
        );
        
        $ebook_id = wp_insert_post($ebook_data);
        
        // Set the template for the Ebook page
        if ($ebook_id) {
            update_post_meta($ebook_id, '_wp_page_template', 'page-ebook.php');
        }
    } else {
        // Update template if page exists but doesn't have template
        $template = get_post_meta($ebook_page->ID, '_wp_page_template', true);
        if ($template !== 'page-ebook.php') {
            update_post_meta($ebook_page->ID, '_wp_page_template', 'page-ebook.php');
        }
    }
    
    // ========================================
    // CREATE SINGLE EBOOK PAGE
    // ========================================
    $single_ebook_page = get_page_by_path('single-ebook');
    
    if (!$single_ebook_page) {
        $single_ebook_data = array(
            'post_title'    => 'Single Ebook',
            'post_content'  => 'Single Ebook reader page.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'single-ebook',
            'post_author'   => 1,
            'page_template' => 'page-single-ebook.php',
        );
        
        $single_ebook_id = wp_insert_post($single_ebook_data);
        
        // Set the template for the single Ebook page
        if ($single_ebook_id) {
            update_post_meta($single_ebook_id, '_wp_page_template', 'page-single-ebook.php');
        }
    } else {
        // Update template if page exists but doesn't have template
        $template = get_post_meta($single_ebook_page->ID, '_wp_page_template', true);
        if ($template !== 'page-single-ebook.php') {
            update_post_meta($single_ebook_page->ID, '_wp_page_template', 'page-single-ebook.php');
        }
    }
    
    // ========================================
    // CREATE AUDIO BOOK LIBRARY PAGE
    // ========================================
    $audiobook_page = get_page_by_path('audiobook');
    
    if (!$audiobook_page) {
        $audiobook_data = array(
            'post_title'    => 'Audio Book',
            'post_content'  => 'Browse and listen to premium Audio Book content.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'audiobook',
            'post_author'   => 1,
            'page_template' => 'page-audiobook.php',
        );
        
        $audiobook_id = wp_insert_post($audiobook_data);
        
        // Set the template for the Audio Book page
        if ($audiobook_id) {
            update_post_meta($audiobook_id, '_wp_page_template', 'page-audiobook.php');
        }
    } else {
        // Update template if page exists but doesn't have template
        $template = get_post_meta($audiobook_page->ID, '_wp_page_template', true);
        if ($template !== 'page-audiobook.php') {
            update_post_meta($audiobook_page->ID, '_wp_page_template', 'page-audiobook.php');
        }
    }
    
    // ========================================
    // CREATE SINGLE AUDIO BOOK PAGE
    // ========================================
    $single_audiobook_page = get_page_by_path('single-audiobook');
    
    if (!$single_audiobook_page) {
        $single_audiobook_data = array(
            'post_title'    => 'Single Audio Book',
            'post_content'  => 'Audio Book player page.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'single-audiobook',
            'post_author'   => 1,
            'page_template' => 'page-single-audiobook.php',
        );
        
        $single_audiobook_id = wp_insert_post($single_audiobook_data);
        
        // Set the template for the single Audio Book page
        if ($single_audiobook_id) {
            update_post_meta($single_audiobook_id, '_wp_page_template', 'page-single-audiobook.php');
        }
    } else {
        // Update template if page exists but doesn't have template
        $template = get_post_meta($single_audiobook_page->ID, '_wp_page_template', true);
        if ($template !== 'page-single-audiobook.php') {
            update_post_meta($single_audiobook_page->ID, '_wp_page_template', 'page-single-audiobook.php');
        }
    }
    
    // ========================================
    // CREATE LIVE STREAMS PAGE
    // ========================================
    $live_streams_page = get_page_by_path('live-streams');
    
    if (!$live_streams_page) {
        $live_streams_data = array(
            'post_title'    => 'Live Streams',
            'post_content'  => 'Browse and join live audio streaming sessions.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'live-streams',
            'post_author'   => 1,
            'page_template' => 'page-live-streams.php',
        );
        
        $live_streams_id = wp_insert_post($live_streams_data);
        
        // Set the template for the Live Streams page
        if ($live_streams_id) {
            update_post_meta($live_streams_id, '_wp_page_template', 'page-live-streams.php');
        }
    } else {
        // Update template if page exists but doesn't have template
        $template = get_post_meta($live_streams_page->ID, '_wp_page_template', true);
        if ($template !== 'page-live-streams.php') {
            update_post_meta($live_streams_page->ID, '_wp_page_template', 'page-live-streams.php');
        }
    }
    
    // ========================================
    // CREATE SINGLE POST PAGE
    // ========================================
    $single_post_page = get_page_by_path('single-post');
    
    if (!$single_post_page) {
        $single_post_data = array(
            'post_title'    => 'Single Post',
            'post_content'  => 'Individual social post display page.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'single-post',
            'post_author'   => 1,
            'page_template' => 'page-single-post.php',
        );
        
        $single_post_id = wp_insert_post($single_post_data);
        
        // Set the template for the single post page
        if ($single_post_id) {
            update_post_meta($single_post_id, '_wp_page_template', 'page-single-post.php');
        }
    } else {
        // Update template if page exists but doesn't have template
        $template = get_post_meta($single_post_page->ID, '_wp_page_template', true);
        if ($template !== 'page-single-post.php') {
            update_post_meta($single_post_page->ID, '_wp_page_template', 'page-single-post.php');
        }
    }
    
    // ========================================
    // CREATE USER PROFILE PAGE
    // ========================================
    $profile_page = get_page_by_path('profile');
    
    if (!$profile_page) {
        $profile_data = array(
            'post_title'    => 'Profile',
            'post_content'  => 'User profile page with personal information and statistics.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'profile',
            'post_author'   => 1,
            'page_template' => 'page-profile.php',
        );
        
        $profile_id = wp_insert_post($profile_data);
        
        // Set the template for the profile page
        if ($profile_id) {
            update_post_meta($profile_id, '_wp_page_template', 'page-profile.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($profile_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($profile_page->ID, '_wp_page_template', 'page-profile.php');
        }
    }
    
    // ========================================
    // CREATE POLICIES PAGE
    // ========================================
    $policies_page = get_page_by_path('policies');
    
    if (!$policies_page) {
        $policies_data = array(
            'post_title'    => 'Policies',
            'post_content'  => 'Platform policies, terms of service, and community guidelines.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'policies',
            'post_author'   => 1,
            'page_template' => 'page-policies.php',
        );
        
        $policies_id = wp_insert_post($policies_data);
        
        // Set the template for the policies page
        if ($policies_id) {
            update_post_meta($policies_id, '_wp_page_template', 'page-policies.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($policies_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($policies_page->ID, '_wp_page_template', 'page-policies.php');
        }
    }
    
    // ========================================
    // CREATE PRIVACY POLICY PAGE
    // ========================================
    $privacy_page = get_page_by_path('privacy');
    
    if (!$privacy_page) {
        $privacy_data = array(
            'post_title'    => 'Privacy Policy',
            'post_content'  => 'Your privacy and trust matter to us. Please read our privacy policy carefully.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'privacy',
            'post_author'   => 1,
            'page_template' => 'page-privacy.php',
        );
        
        $privacy_id = wp_insert_post($privacy_data);
        
        // Set the template for the privacy page
        if ($privacy_id) {
            update_post_meta($privacy_id, '_wp_page_template', 'page-privacy.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($privacy_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($privacy_page->ID, '_wp_page_template', 'page-privacy.php');
        }
    }
    
    // ========================================
    // CREATE CONTACT US PAGE
    // ========================================
    $contact_page = get_page_by_path('contact');
    
    if (!$contact_page) {
        $contact_data = array(
            'post_title'    => 'Contact Us',
            'post_content'  => 'Get in touch with us. We\'d love to hear from you!',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'contact',
            'post_author'   => 1,
            'page_template' => 'page-contact.php',
        );
        
        $contact_id = wp_insert_post($contact_data);
        
        // Set the template for the contact page
        if ($contact_id) {
            update_post_meta($contact_id, '_wp_page_template', 'page-contact.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($contact_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($contact_page->ID, '_wp_page_template', 'page-contact.php');
        }
    }
    
    // ========================================
    // CREATE EARNINGS DASHBOARD PAGE
    // ========================================
    $earnings_page = get_page_by_path('earnings');
    
    if (!$earnings_page) {
        $earnings_data = array(
            'post_title'    => 'Earnings',
            'post_content'  => 'Track your revenue, manage payouts, and view transaction history.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'earnings',
            'post_author'   => 1,
            'page_template' => 'page-earnings.php',
        );
        
        $earnings_id = wp_insert_post($earnings_data);
        
        // Set the template for the earnings page
        if ($earnings_id) {
            update_post_meta($earnings_id, '_wp_page_template', 'page-earnings.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($earnings_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($earnings_page->ID, '_wp_page_template', 'page-earnings.php');
        }
    }
    
    // ========================================
    // CREATE CONTENT CREATION PAGE
    // ========================================
    $create_page = get_page_by_path('create');
    
    if (!$create_page) {
        $create_data = array(
            'post_title'    => 'Create',
            'post_content'  => 'Create new content - Go Live, upload Audio, or create Text posts.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'create',
            'post_author'   => 1,
            'page_template' => 'page-create.php',
        );
        
        $create_id = wp_insert_post($create_data);
        
        // Set the template for the create page
        if ($create_id) {
            update_post_meta($create_id, '_wp_page_template', 'page-create.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($create_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($create_page->ID, '_wp_page_template', 'page-create.php');
        }
    }
    
    // ========================================
    // CREATE SETTINGS PAGE
    // ========================================
    $settings_page = get_page_by_path('settings');
    
    if (!$settings_page) {
        $settings_data = array(
            'post_title'    => 'Settings',
            'post_content'  => 'Manage your account settings and preferences.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'settings',
            'post_author'   => 1,
            'page_template' => 'page-settings.php',
        );
        
        $settings_id = wp_insert_post($settings_data);
        
        // Set the template for the settings page
        if ($settings_id) {
            update_post_meta($settings_id, '_wp_page_template', 'page-settings.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($settings_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($settings_page->ID, '_wp_page_template', 'page-settings.php');
        }
    }
    
    // ========================================
    // CREATE EMAIL VERIFICATION PAGE
    // ========================================
    $verify_email_page = get_page_by_path('verify-email');
    
    if (!$verify_email_page) {
        $verify_email_data = array(
            'post_title'    => 'Verify Email',
            'post_content'  => 'Email verification page for new user registrations.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'verify-email',
            'post_author'   => 1,
            'page_template' => 'page-verify-email.php',
        );
        
        $verify_email_id = wp_insert_post($verify_email_data);
        
        // Set the template for the verify email page
        if ($verify_email_id) {
            update_post_meta($verify_email_id, '_wp_page_template', 'page-verify-email.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($verify_email_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($verify_email_page->ID, '_wp_page_template', 'page-verify-email.php');
        }
    }
    
    // ========================================
    // CREATE RESET PASSWORD PAGE
    // ========================================
    $reset_password_page = get_page_by_path('reset-password');
    
    if (!$reset_password_page) {
        $reset_password_data = array(
            'post_title'    => 'Reset Password',
            'post_content'  => 'Password reset page.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'reset-password',
            'post_author'   => 1,
            'page_template' => 'page-reset-password.php',
        );
        
        $reset_password_id = wp_insert_post($reset_password_data);
        
        // Set the template for the reset password page
        if ($reset_password_id) {
            update_post_meta($reset_password_id, '_wp_page_template', 'page-reset-password.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($reset_password_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($reset_password_page->ID, '_wp_page_template', 'page-reset-password.php');
        }
    }
    
    // ========================================
    // CREATE CART PAGE
    // ========================================
    $cart_page = get_page_by_path('cart');
    
    if (!$cart_page) {
        $cart_data = array(
            'post_title'    => 'Cart',
            'post_content'  => 'Shopping cart page for managing items before checkout.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'cart',
            'post_author'   => 1,
            'page_template' => 'page-cart.php',
        );
        
        $cart_id = wp_insert_post($cart_data);
        
        // Set the template for the cart page
        if ($cart_id) {
            update_post_meta($cart_id, '_wp_page_template', 'page-cart.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($cart_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($cart_page->ID, '_wp_page_template', 'page-cart.php');
        }
    }
    
    // ========================================
    // CREATE CHECKOUT PAGE
    // ========================================
    $checkout_page = get_page_by_path('checkout');
    
    if (!$checkout_page) {
        $checkout_data = array(
            'post_title'    => 'Checkout',
            'post_content'  => 'Ebook checkout and payment page.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'checkout',
            'post_author'   => 1,
            'page_template' => 'page-checkout.php',
        );
        
        $checkout_id = wp_insert_post($checkout_data);
        
        // Set the template for the checkout page
        if ($checkout_id) {
            update_post_meta($checkout_id, '_wp_page_template', 'page-checkout.php');
        }
    } else {
        // Update template if page exists but doesn't have the template assigned
        $template = get_post_meta($checkout_page->ID, '_wp_page_template', true);
        if (empty($template) || $template === 'default') {
            update_post_meta($checkout_page->ID, '_wp_page_template', 'page-checkout.php');
        }
    }
} // END: nymia_create_dashboard_page()

// ========================================
// CURRENCY AND COMMISSION FUNCTIONS
// ========================================

if (!function_exists('nymia_format_currency_for_display')) {
function nymia_format_currency_for_display($amount, $currency = '') {
    $amount = max(0.0, (float) $amount);
    $currency = $currency ? strtoupper($currency) : strtoupper(get_option('nymia_stripe_currency', 'USD'));

    if (function_exists('wc_price')) {
        $formatted = wc_price($amount, array('currency' => $currency));
        return strip_tags($formatted);
    }

    $symbol = '';
    if (function_exists('get_woocommerce_currency_symbol')) {
        $symbol = get_woocommerce_currency_symbol($currency);
    }

    if ($symbol === '' || $symbol === $currency) {
        switch ($currency) {
            case 'USD':
                $symbol = '$';
                break;
            case 'EUR':
                $symbol = '€';
                break;
            case 'GBP':
                $symbol = '£';
                break;
            case 'CAD':
                $symbol = 'CA$';
                break;
            case 'AUD':
                $symbol = 'A$';
                break;
            default:
                $symbol = '$';
                break;
        }
    }

    return $symbol . number_format_i18n($amount, 2);
}
}

if (!function_exists('nymia_get_platform_commission_rate')) {
function nymia_get_platform_commission_rate() {
    $rate = floatval(get_option('nymia_platform_commission_rate', 20));
    if ($rate < 0) {
        $rate = 0;
    }
    if ($rate > 95) {
        $rate = 95;
    }
    return $rate;
}
}

if (!function_exists('nymia_get_creator_share_percentage')) {
function nymia_get_creator_share_percentage() {
    return 100 - nymia_get_platform_commission_rate();
}
}

if (!function_exists('nymia_calculate_creator_share')) {
function nymia_calculate_creator_share($amount, $precision = 2) {
    $amount = (float) $amount;
    if ($amount <= 0) {
        return 0.0;
    }
    $share = ($amount * nymia_get_creator_share_percentage()) / 100;
    return round($share, $precision);
}
}

if (!function_exists('nymia_collect_creator_charges')) {
function nymia_collect_creator_charges($user_id, $start = null, $end = null, $args = array()) {
    $user_id = (int) $user_id;
    if (!$user_id) {
        return array(
            'amount'       => 0.0,
            'share_amount' => 0.0,
            'currency'     => strtoupper(get_option('nymia_stripe_currency', 'USD')),
            'charges'      => array(),
        );
    }

    $secret_key = get_option('nymia_stripe_secret_key', '');
    if (empty($secret_key)) {
        return array(
            'amount'       => 0.0,
            'share_amount' => 0.0,
            'currency'     => strtoupper(get_option('nymia_stripe_currency', 'USD')),
            'charges'      => array(),
        );
    }

    $defaults = array(
        'max_iterations' => 10,
        'limit'          => 100,
        'status'         => 'succeeded',
        'return_charges' => false,
    );
    $args = wp_parse_args($args, $defaults);

    $cache_key = 'nymia_creator_charges_' . md5(
        $user_id . '|' .
        (string) $start . '|' .
        (string) $end . '|' .
        wp_json_encode($args) . '|' .
        substr($secret_key, 0, 8)
    );

    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $params = array(
        'limit'                => (int) $args['limit'],
        'metadata[creator_id]' => (string) $user_id,
    );

    if (!empty($args['status'])) {
        $params['status'] = $args['status'];
    }

    if ($start !== null) {
        $params['created[gte]'] = max(0, (int) $start);
    }

    if ($end !== null) {
        $params['created[lt]'] = max(0, (int) $end);
    }

    $results = array(
        'amount'       => 0.0,
        'share_amount' => 0.0,
        'currency'     => '',
        'charges'      => array(),
    );

    $iterations = 0;
    $starting_after = null;
    $has_more = false;
    $default_currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));

    do {
        $iterations++;

        $request_params = $params;
        if ($starting_after) {
            $request_params['starting_after'] = $starting_after;
        }

        $endpoint = add_query_arg($request_params, 'https://api.stripe.com/v1/charges');
        $response = wp_remote_get($endpoint, array(
            'timeout' => 20,
            'headers' => array(
                'Authorization'  => 'Bearer ' . $secret_key,
                'Stripe-Version' => '2023-10-16',
            ),
        ));

        if (is_wp_error($response)) {
            break;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 400) {
            break;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body) || empty($body['data']) || !is_array($body['data'])) {
            break;
        }

        foreach ($body['data'] as $charge) {
            if (!is_array($charge)) {
                continue;
            }

            $currency = isset($charge['currency']) ? strtoupper($charge['currency']) : '';
            if ($results['currency'] === '') {
                $results['currency'] = $currency ? $currency : $default_currency;
            }

            if ($currency && $results['currency'] && $currency !== $results['currency']) {
                continue;
            }

            $amount_captured = isset($charge['amount_captured']) ? (int) $charge['amount_captured'] : (isset($charge['amount']) ? (int) $charge['amount'] : 0);
            $amount_refunded = isset($charge['amount_refunded']) ? (int) $charge['amount_refunded'] : 0;
            $net_amount = ($amount_captured - $amount_refunded) / 100;

            if ($net_amount <= 0) {
                continue;
            }

            $results['amount'] += $net_amount;
            $share_value = nymia_calculate_creator_share($net_amount);
            $results['share_amount'] += $share_value;

            if (!empty($args['return_charges'])) {
                $results['charges'][] = array(
                    'id'          => isset($charge['id']) ? $charge['id'] : '',
                    'amount'      => $net_amount,
                    'share_amount'=> $share_value,
                    'created'     => isset($charge['created']) ? (int) $charge['created'] : 0,
                    'currency'    => $currency ?: $default_currency,
                    'status'      => isset($charge['status']) ? $charge['status'] : '',
                    'description' => isset($charge['description']) ? $charge['description'] : '',
                    'metadata'    => isset($charge['metadata']) && is_array($charge['metadata']) ? $charge['metadata'] : array(),
                    'payment_method' => isset($charge['payment_method_details']['type']) ? $charge['payment_method_details']['type'] : '',
                );
            }
        }

        $has_more = !empty($body['has_more']);
        if ($has_more) {
            $last_item = end($body['data']);
            $starting_after = isset($last_item['id']) ? $last_item['id'] : null;
        }
    } while ($has_more && $iterations < (int) $args['max_iterations']);

    if ($results['currency'] === '') {
        $results['currency'] = $default_currency;
    }

    set_transient($cache_key, $results, 5 * MINUTE_IN_SECONDS);

    return $results;
}
}

// ========================================
// CREATOR EARNINGS HELPERS
// ========================================

if (!function_exists('nymia_get_creator_chart_data')) {
function nymia_get_creator_chart_data($user_id, $days = 30) {
    $user_id = (int) $user_id;
    if (!$user_id) {
        return array(
            'labels'   => array(),
            'amounts'  => array(),
            'currency' => strtoupper(get_option('nymia_stripe_currency', 'USD')),
            'total'    => 0.0,
        );
    }

    $days = max(7, min(120, (int) $days));
    $now_gmt = current_time('timestamp', true);
    $range_start = $now_gmt - ($days * DAY_IN_SECONDS);

    $charges = nymia_collect_creator_charges($user_id, $range_start, $now_gmt, array(
        'return_charges' => true,
        'limit'          => 100,
        'max_iterations' => 8,
    ));

    $daily_totals = array_fill(0, $days, 0.0);
    if (!empty($charges['charges']) && is_array($charges['charges'])) {
        foreach ($charges['charges'] as $charge) {
            $created = isset($charge['created']) ? (int) $charge['created'] : 0;
            if (!$created || $created < $range_start || $created > $now_gmt) {
                continue;
            }

            $day_index = (int) floor(($created - $range_start) / DAY_IN_SECONDS);
            if ($day_index < 0 || $day_index >= $days) {
                continue;
            }

            $share_amount = isset($charge['share_amount']) ? (float) $charge['share_amount'] : 0.0;
            $daily_totals[$day_index] += $share_amount;
        }
    }

    $labels = array();
    $amounts = array();
    $timezone = wp_timezone();

    for ($i = 0; $i < $days; $i++) {
        $day_timestamp = $range_start + ($i * DAY_IN_SECONDS);
        $labels[] = wp_date('M j', $day_timestamp, $timezone);
        $amounts[] = round($daily_totals[$i], 2);
    }

    return array(
        'labels'   => $labels,
        'amounts'  => $amounts,
        'currency' => isset($charges['currency']) ? $charges['currency'] : strtoupper(get_option('nymia_stripe_currency', 'USD')),
        'total'    => array_sum($amounts),
    );
}
}

if (!function_exists('nymia_get_creator_transactions')) {
function nymia_get_creator_transactions($user_id, $limit = 10) {
    $user_id = (int) $user_id;
    if (!$user_id) {
        return array();
    }

    $limit = max(1, min(200, (int) $limit));

    $charges = nymia_collect_creator_charges($user_id, null, null, array(
        'return_charges' => true,
        'limit'          => max(50, $limit),
        'max_iterations' => 10,
    ));

    if (empty($charges['charges']) || !is_array($charges['charges'])) {
        return array();
    }

    $currency = isset($charges['currency']) ? $charges['currency'] : strtoupper(get_option('nymia_stripe_currency', 'USD'));
    $transactions = $charges['charges'];

    usort($transactions, function($a, $b) {
        $a_time = isset($a['created']) ? (int) $a['created'] : 0;
        $b_time = isset($b['created']) ? (int) $b['created'] : 0;
        return $b_time <=> $a_time;
    });

    $transactions = array_slice($transactions, 0, $limit);
    $formatted = array();

    foreach ($transactions as $charge) {
        $created = isset($charge['created']) ? (int) $charge['created'] : 0;
        $amount = isset($charge['share_amount']) ? (float) $charge['share_amount'] : 0.0;
        $raw_amount = isset($charge['amount']) ? (float) $charge['amount'] : $amount;
        $meta = isset($charge['metadata']) && is_array($charge['metadata']) ? $charge['metadata'] : array();
        $item_type = isset($meta['item_type']) ? sanitize_key($meta['item_type']) : 'general';

        switch ($item_type) {
            case 'audio':
                $type_label = __('Audio', 'nymia');
                break;
            case 'ebook':
                $type_label = __('Ebook', 'nymia');
                break;
            case 'live':
                $type_label = __('Live Session', 'nymia');
                break;
            default:
                $type_label = __('General', 'nymia');
                break;
        }

        $status = isset($charge['status']) ? strtolower($charge['status']) : 'succeeded';
        $status_label = ucfirst($status);

        $formatted[] = array(
            'date_display'   => $created ? wp_date(get_option('date_format'), $created, wp_timezone()) : '',
            'datetime'       => $created ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $created, wp_timezone()) : '',
            'description'    => !empty($charge['description']) ? $charge['description'] : __('Creator sale', 'nymia'),
            'type_label'     => $type_label,
            'type_slug'      => 'type-' . $item_type,
            'status_label'   => $status_label,
            'status_slug'    => 'status-' . sanitize_title_with_dashes($status),
            'amount_display' => nymia_format_currency_for_display($amount, $currency),
            'amount_sign'    => $amount >= 0 ? 'positive' : 'negative',
            'raw_amount'     => $raw_amount,
            'share_amount'   => $amount,
            'currency'       => $currency,
        );
    }

    return $formatted;
}
}

if (!function_exists('nymia_get_creator_breakdown')) {
function nymia_get_creator_breakdown($user_id, $days = 30) {
    $user_id = (int) $user_id;
    if (!$user_id) {
        return array(
            'segments' => array(),
            'currency' => strtoupper(get_option('nymia_stripe_currency', 'USD')),
            'total'    => 0.0,
        );
    }

    $days = max(7, min(90, (int) $days));
    $now_gmt = current_time('timestamp', true);
    $range_start = $now_gmt - ($days * DAY_IN_SECONDS);

    $charges = nymia_collect_creator_charges($user_id, $range_start, $now_gmt, array(
        'return_charges' => true,
        'limit'          => 100,
        'max_iterations' => 8,
    ));

    $currency = isset($charges['currency']) ? $charges['currency'] : strtoupper(get_option('nymia_stripe_currency', 'USD'));
    $buckets = array();
    $total = 0.0;

    if (!empty($charges['charges']) && is_array($charges['charges'])) {
        foreach ($charges['charges'] as $charge) {
            $meta = isset($charge['metadata']) && is_array($charge['metadata']) ? $charge['metadata'] : array();
            $item_type = isset($meta['item_type']) ? sanitize_key($meta['item_type']) : 'general';
            $share_amount = isset($charge['share_amount']) ? (float) $charge['share_amount'] : 0.0;
            $total_amount = isset($charge['amount']) ? (float) $charge['amount'] : 0.0;

            if ($share_amount <= 0) {
                continue;
            }

            if (!isset($buckets[$item_type])) {
                $buckets[$item_type] = array(
                    'amount' => 0.0,
                    'count'  => 0,
                    'total_amount' => 0.0, // Total customer paid (before commission)
                    'minutes' => 0, // For one-to-one sessions
                    'items' => array(), // Store individual item details
                );
            }

            $buckets[$item_type]['amount'] += $share_amount;
            $buckets[$item_type]['count']++;
            $buckets[$item_type]['total_amount'] += $total_amount;
            $total += $share_amount;
            
            // Track minutes for live/one-to-one sessions
            if ($item_type === 'live' && isset($meta['minutes'])) {
                $minutes = intval($meta['minutes']);
                if ($minutes > 0) {
                    $buckets[$item_type]['minutes'] += $minutes;
                }
            }
            
            // Store item details for detailed breakdown
            $buckets[$item_type]['items'][] = array(
                'amount' => $total_amount,
                'share_amount' => $share_amount,
                'minutes' => isset($meta['minutes']) ? intval($meta['minutes']) : 0,
                'description' => isset($charge['description']) ? $charge['description'] : '',
            );
        }
    }

    $labels = array(
        'audio'        => __('Audio Sales', 'nymia'),
        'ebook'        => __('Ebook Sales', 'nymia'),
        'live'         => __('Live Sessions', 'nymia'),
        'tips'         => __('Tips & Donations', 'nymia'),
        'tip'          => __('Tips & Donations', 'nymia'),
        'subscription' => __('Subscriptions', 'nymia'),
        'general'      => __('Other Revenue', 'nymia'),
    );
    $icons = array(
        'audio'        => 'audio',
        'ebook'        => 'ebook',
        'live'         => 'live',
        'tips'         => 'tips',
        'tip'          => 'tips',
        'subscription' => 'payout',
        'general'      => 'payout',
    );

    $segments = array();
    foreach ($buckets as $type => $value) {
        $amount = isset($value['amount']) ? (float) $value['amount'] : 0.0;
        $count = isset($value['count']) ? (int) $value['count'] : 0;
        if ($amount <= 0) {
            continue;
        }

        $count_label = '';
        $details_label = '';
        $total_customer_paid = isset($value['total_amount']) ? (float) $value['total_amount'] : 0.0;
        $average_price = $count > 0 ? ($total_customer_paid / $count) : 0.0;
        $total_minutes = isset($value['minutes']) ? (int) $value['minutes'] : 0;
        
        if ($count > 0) {
            $count_label = sprintf(
                _n('%s sale', '%s sales', $count, 'nymia'),
                number_format_i18n($count)
            );
            
            // Build details label with price and minutes info
            $details_parts = array();
            
            // Show average price per item
            if ($average_price > 0) {
                $details_parts[] = sprintf(
                    __('Avg: %s per item', 'nymia'),
                    nymia_format_currency_for_display($average_price, $currency)
                );
            }
            
            // For live sessions, show minutes
            if ($type === 'live' && $total_minutes > 0) {
                $details_parts[] = sprintf(
                    _n('%s minute sold', '%s minutes sold', $total_minutes, 'nymia'),
                    number_format_i18n($total_minutes)
                );
            }
            
            if (!empty($details_parts)) {
                $details_label = implode(' • ', $details_parts);
            }
        }

        $segments[] = array(
            'label'     => isset($labels[$type]) ? $labels[$type] : ucfirst($type),
            'value'     => round($amount, 2),
            'amount_display' => nymia_format_currency_for_display($amount, $currency),
            'formatted' => nymia_format_currency_for_display($amount, $currency),
            'percent'   => $total > 0 ? round(($amount / $total) * 100, 1) : 0,
            'slug'      => sanitize_title_with_dashes($type),
            'icon'      => isset($icons[$type]) ? $icons[$type] : 'audio',
            'count'     => $count,
            'count_label' => $count_label,
            'details_label' => $details_label,
            'total_customer_paid' => $total_customer_paid,
            'average_price' => $average_price,
            'total_minutes' => $total_minutes,
        );
    }

    usort($segments, function($a, $b) {
        return $b['value'] <=> $a['value'];
    });

    return array(
        'segments' => $segments,
        'currency' => $currency,
        'total'    => $total,
    );
}
}

function nymia_ajax_get_creator_transactions() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to view transactions.', 'nymia')));
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'nymia_creator_transactions')) {
        wp_send_json_error(array('message' => __('Invalid nonce. Please refresh the page.', 'nymia')));
    }

    $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 100;
    $transactions = nymia_get_creator_transactions(get_current_user_id(), $limit);

    wp_send_json_success(array(
        'transactions' => $transactions,
    ));
}
add_action('wp_ajax_nymia_get_creator_transactions', 'nymia_ajax_get_creator_transactions');

function nymia_ajax_get_creator_earnings_chart() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to view earnings data.', 'nymia')));
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'nymia_creator_earnings_chart')) {
        wp_send_json_error(array('message' => __('Invalid nonce. Please refresh the page.', 'nymia')));
    }

    $period = isset($_POST['period']) ? (int) $_POST['period'] : 30;
    $chart = nymia_get_creator_chart_data(get_current_user_id(), $period);
    $breakdown = nymia_get_creator_breakdown(get_current_user_id(), $period);

    wp_send_json_success(array(
        'chart'     => $chart,
        'breakdown' => $breakdown,
    ));
}
add_action('wp_ajax_nymia_get_creator_earnings_chart', 'nymia_ajax_get_creator_earnings_chart');

/**
 * ENQUEUE SCRIPTS AND STYLES
 * ---------------------------
 * Loads the theme's CSS and JavaScript files
 * Hooks into: wp_enqueue_scripts
 */
function nymia_scripts() {
    // LOAD MAIN STYLESHEET
    wp_enqueue_style('nymia-style', get_stylesheet_uri(), array(), '3.6.0');
    
    // LOAD CUSTOM JAVASCRIPT (mobile menu, interactions)
    wp_enqueue_script('nymia-script', get_template_directory_uri() . '/js/main.js', array('jquery'), '3.6.0', true);
    
    // ENSURE JQUERY IS LOADED
    wp_enqueue_script('jquery');
    
    // LOAD FOLLOWERS SYSTEM CSS
    wp_enqueue_style('nymia-followers-style', get_template_directory_uri() . '/followers/css/followers.css', array(), '3.6.0');
    
    // LOAD FOLLOWERS SYSTEM JAVASCRIPT
    wp_enqueue_script('nymia-followers-script', get_template_directory_uri() . '/followers/js/followers.js', array('jquery'), '3.6.0', true);
    
    // LOAD NOTIFICATIONS SYSTEM CSS
    wp_enqueue_style('nymia-notifications-style', get_template_directory_uri() . '/notifications/css/notifications.css', array(), '3.6.0');
    
    // LOAD NOTIFICATIONS SYSTEM JAVASCRIPT
    wp_enqueue_script('nymia-notifications-script', get_template_directory_uri() . '/notifications/js/notifications.js', array('jquery'), '3.6.0', true);
    
    // ZEGO UIKit Prebuilt (loaded on pages that use it)
    wp_enqueue_script('nymia-zego-uikit', 'https://cdn.jsdelivr.net/npm/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js', array(), null, true);
    
    $current_user = wp_get_current_user();
    $current_user_id = get_current_user_id();
    $current_user_roles = is_user_logged_in() ? (array) $current_user->roles : array();
    $privileged_roles = apply_filters('nymia_paid_audio_privileged_roles', array('administrator', 'editor', 'author', 'shop_manager'));

    $ajax_data = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('nymia_audio_upload'),
        'followNonce' => wp_create_nonce('nymia_follow_action'),
        'zegoNonce' => wp_create_nonce('nymia_zego_action'),
        'purchaseNonce' => wp_create_nonce('nymia_purchase_ebook'),
        'checkoutNonce' => wp_create_nonce('nymia_checkout'),
        'textPostNonce' => wp_create_nonce('nymia_text_post'),
        'ebookBookmarkNonce' => wp_create_nonce('nymia_ebook_bookmark'),
        'searchNonce' => wp_create_nonce('nymia_live_search'),
        'instantCallNonce' => wp_create_nonce('nymia_instant_call'),
        'trackCallNonce' => wp_create_nonce('nymia_track_call'),
        'currentUserId' => $current_user_id,
        'currentUserRoles' => $current_user_roles,
        'isLoggedIn' => is_user_logged_in(),
        'paidAudioPrivilegedRoles' => $privileged_roles
    );

    // LOCALIZE SCRIPT: Pass data to JavaScript
    wp_localize_script('nymia-script', 'nymiaAjax', $ajax_data);
    
    // Localize followers script with the same AJAX data
    wp_localize_script('nymia-followers-script', 'nymiaAjax', $ajax_data);

    // Load Ebook Library interactions on Ebook pages
    if (is_page('ebook') || is_page('single-ebook')) {
        wp_enqueue_script('nymia-ebook-library', get_template_directory_uri() . '/ebook-archive/js/ebook-library.js', array(), '1.0.0', true);
    }
} // END: nymia_scripts()
add_action('wp_enqueue_scripts', 'nymia_scripts');

/**
 * REGISTER WIDGET AREAS
 * ---------------------
 * Registers sidebar widgets for the theme
 * Hooks into: widgets_init
 */
function nymia_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'nymia'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'nymia'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
} // END: nymia_widgets_init()
add_action('widgets_init', 'nymia_widgets_init');

/**
 * CUSTOM EXCERPT LENGTH
 * ---------------------
 * Limits post excerpts to 20 words
 * Hooks into: excerpt_length filter
 */
function nymia_excerpt_length($length) {
    return 20;
} // END: nymia_excerpt_length()
add_filter('excerpt_length', 'nymia_excerpt_length');

/**
 * CUSTOM EXCERPT SUFFIX
 * ---------------------
 * Changes the "more" text to "..." instead of "[...]"
 * Hooks into: excerpt_more filter
 */
function nymia_excerpt_more($more) {
    return '...';
} // END: nymia_excerpt_more()
add_filter('excerpt_more', 'nymia_excerpt_more');

/**
 * ADD CUSTOM BODY CLASSES
 * -----------------------
 * Adds dashboard-page class for dashboard/front page
 * Adds user role classes
 * Hooks into: body_class filter
 */
function nymia_body_classes($classes) {
    if (is_page('dashboard') || is_front_page()) {
        $classes[] = 'dashboard-page';
    }
    
    // Add user role class
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (in_array('subscriber', $user->roles)) {
            $classes[] = 'subscriber';
        } elseif (in_array('administrator', $user->roles)) {
            $classes[] = 'admin';
        } elseif (in_array('editor', $user->roles)) {
            $classes[] = 'editor';
        } elseif (in_array('author', $user->roles)) {
            $classes[] = 'author';
        }
    }
    
    return $classes;
} // END: nymia_body_classes()
add_filter('body_class', 'nymia_body_classes');

/**
 * GET DASHBOARD DATA
 * ------------------
 * Returns static dashboard content data including:
 * - Recent content
 * - Live audio streaming
 * - Audio books
 * - Suggestions
 * @return array Dashboard data
 */
/**
 * GET DASHBOARD EBOOKS
 * --------------------
 * Retrieves ebooks formatted for dashboard display
 * @return array Formatted ebook data
 */
function nymia_get_dashboard_ebooks() {
    $ebooks = array();
    
    // Get all ebooks if function exists
    if (function_exists('nymia_get_all_ebooks')) {
        $all_ebooks = nymia_get_all_ebooks();
        
        // Limit to 6 ebooks for dashboard
        $all_ebooks = array_slice($all_ebooks, 0, 6);
        
        foreach ($all_ebooks as $ebook) {
            // Get ebook thumbnail or use default
            $image = !empty($ebook['thumbnail']) ? $ebook['thumbnail'] : 
                     (get_template_directory_uri() . '/assets/images/ebook-placeholder.jpg');
            
            // Get author name
            $author_name = !empty($ebook['author']) ? $ebook['author'] : 'Unknown Author';

            // Defaults
            $username = '@' . strtolower(str_replace(' ', '', $author_name));
            $avatar_url = get_template_directory_uri() . '/assets/images/profile.png';

            // Prefer explicit user_id if present in ebook data
            $user = null;
            if (!empty($ebook['user_id'])) {
                $possible_user = get_user_by('id', intval($ebook['user_id']));
                if ($possible_user) { $user = $possible_user; }
            }

            // Fallbacks: try to locate by display_name, user_login, or user_nicename
            if (!$user && !empty($author_name)) {
                // Exact display_name match
                $user = get_user_by('display_name', $author_name);
                if (!$user) {
                    // Broad search across common user fields
                    $query = new WP_User_Query(array(
                        'search' => '*' . esc_attr($author_name) . '*',
                        'search_columns' => array('display_name', 'user_login', 'user_nicename'),
                        'number' => 1,
                    ));
                    $results = $query->get_results();
                    if (!empty($results)) {
                        $user = $results[0];
                    }
                }
            }

            if ($user) {
                $custom_avatar = get_user_meta($user->ID, 'custom_avatar', true);
                $avatar_url = $custom_avatar ?: get_avatar_url($user->ID, array('size' => 150));
                $username = '@' . $user->user_login;
            }
            
            // Format ebook data for dashboard
            $ebooks[] = array(
                'id' => !empty($ebook['id']) ? $ebook['id'] : 0,
                'image' => $image,
                'avatar' => $avatar_url,
                'name' => !empty($ebook['title']) ? $ebook['title'] : 'Untitled Ebook',
                'username' => $username,
                'badge' => 'Read Book',
                'ebook_url' => !empty($ebook['url']) ? $ebook['url'] : '',
            );
        }
    }
    
    // If no ebooks found, return empty array (or keep some fallback if needed)
    if (empty($ebooks)) {
        // Optional: Return empty array or a placeholder
        return array();
    }
    
    return $ebooks;
}

if (!function_exists('nymia_normalize_media_url')) {
function nymia_normalize_media_url($value) {
    if (empty($value) || !is_string($value)) {
        return '';
    }
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    // Already a full or protocol-relative URL
    if (preg_match('#^(https?:)?//#i', $value)) {
        return $value;
    }

    $value = str_replace('\\', '/', $value);
    $uploads = wp_upload_dir();
    $basedir = str_replace('\\', '/', $uploads['basedir']);
    $baseurl = $uploads['baseurl'];

    if (strpos($value, $basedir) === 0) {
        return str_replace($basedir, $baseurl, $value);
    }

    $content_dir = str_replace('\\', '/', WP_CONTENT_DIR);
    if (strpos($value, $content_dir) === 0) {
        $content_url = content_url();
        return str_replace($content_dir, $content_url, $value);
    }

    if ($value[0] === '/') {
        return rtrim(home_url(), '/') . $value;
    }

    return $value;
}}

// ==========================================
// SOCIAL POSTS (TITLE + DESCRIPTION + IMAGE)
// ==========================================
function nymia_prepare_social_post_payload($post) {
    if (!$post instanceof WP_Post) {
        return array();
    }

    $author_id = intval($post->post_author);
    $user = $author_id ? get_userdata($author_id) : null;
    $display_name = $user ? ($user->display_name ?: $user->user_login) : __('Member', 'nymia');
    $username = $user ? '@' . $user->user_login : '@member';
    $avatar = get_template_directory_uri() . '/assets/images/profile.png';
    if ($author_id) {
        $custom_avatar = get_user_meta($author_id, 'custom_avatar', true);
        $avatar = $custom_avatar ?: get_avatar_url($author_id, array('size' => 96));
    }

    $cover = '';
    if ($author_id) {
        $cover = get_user_meta($author_id, 'cover_image', true);
        $cover = nymia_normalize_media_url($cover);
    }
    if (empty($cover)) {
        $cover = get_template_directory_uri() . '/assets/images/blue.png';
    }

    $content_raw = wp_kses_post($post->post_content);
    $content_trimmed = trim($content_raw);
    $excerpt = wp_trim_words(strip_tags($content_trimmed), 16, '…');
    $title = get_the_title($post) ?: __('New Post', 'nymia');

    $image_id = get_post_thumbnail_id($post->ID);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
    if (!$image_url) {
        $image_url = get_post_meta($post->ID, '_nymia_social_image_url', true);
    }
    $image_url = $image_url ? esc_url($image_url) : '';

    // Get likes
    $likes = get_post_meta($post->ID, '_nymia_post_likes', true);
    $likes = is_array($likes) ? $likes : array();
    $like_count = count($likes);
    $current_user_id = get_current_user_id();
    $is_liked = $current_user_id > 0 && in_array($current_user_id, $likes);
    
    // Get comments
    $comments = get_post_meta($post->ID, '_nymia_post_comments', true);
    $comments = is_array($comments) ? $comments : array();
    $comment_count = count($comments);

    return array(
        'id' => $post->ID,
        'author_id' => $author_id,
        'author_name' => $display_name,
        'author_username' => $username,
        'avatar' => $avatar,
        'background' => $cover,
        'title' => $title,
        'description' => wpautop($content_trimmed),
        'excerpt' => $excerpt,
        'image' => $image_url,
        'time_ago' => human_time_diff(get_post_time('U', true, $post), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
        'date' => get_post_time('mysql', false, $post),
        'like_count' => $like_count,
        'is_liked' => $is_liked,
        'comment_count' => $comment_count,
    );
}

function nymia_get_recent_social_posts($limit = 10) {
    // Only allow logged-in users to get posts
    if (!is_user_logged_in()) {
        return array();
    }
    
    $limit = intval($limit);
    if ($limit <= 0) {
        $limit = 10;
    }

    $query = new WP_Query(array(
        'post_type' => 'nymia_social_post',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    $posts = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = nymia_prepare_social_post_payload(get_post());
        }
        wp_reset_postdata();
    }

    return $posts;
}

function nymia_submit_social_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to share a post.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    $description = isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : '';

    if ($title === '') {
        wp_send_json_error(array('message' => __('Please enter a title.', 'nymia')));
    }
    if (trim(strip_tags($description)) === '') {
        wp_send_json_error(array('message' => __('Please enter a description.', 'nymia')));
    }

    $post_id = wp_insert_post(array(
        'post_type' => 'nymia_social_post',
        'post_status' => 'publish',
        'post_title' => mb_substr($title, 0, 140),
        'post_content' => mb_substr($description, 0, 5000),
        'post_author' => get_current_user_id(),
    ), true);

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => __('Unable to publish post. Please try again.', 'nymia')));
    }

    if (!empty($_FILES['image']) && isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name']) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('image', $post_id);
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
            update_post_meta($post_id, '_nymia_social_image_url', wp_get_attachment_url($attachment_id));
        }
    }

    wp_send_json_success(nymia_prepare_social_post_payload(get_post($post_id)));
}
add_action('wp_ajax_nymia_submit_text_post', 'nymia_submit_social_post');

function nymia_fetch_social_posts() {
    // Only allow logged-in users to fetch posts
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to view posts.', 'nymia')), 401);
    }
    
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
    wp_send_json_success(array('posts' => nymia_get_recent_social_posts($limit)));
}
add_action('wp_ajax_nymia_fetch_text_posts', 'nymia_fetch_social_posts');
// Removed wp_ajax_nopriv - posts are only for logged-in users

// ==========================================
// EDIT SOCIAL POST
// ==========================================
function nymia_edit_social_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to edit a post.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(array('message' => __('Invalid post ID.', 'nymia')));
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nymia_social_post') {
        wp_send_json_error(array('message' => __('Post not found.', 'nymia')));
    }

    // Check if user owns the post
    if (intval($post->post_author) !== get_current_user_id() && !current_user_can('edit_others_posts')) {
        wp_send_json_error(array('message' => __('You do not have permission to edit this post.', 'nymia')), 403);
    }

    $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    $description = isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : '';

    if ($title === '') {
        wp_send_json_error(array('message' => __('Please enter a title.', 'nymia')));
    }
    if (trim(strip_tags($description)) === '') {
        wp_send_json_error(array('message' => __('Please enter a description.', 'nymia')));
    }

    $update_result = wp_update_post(array(
        'ID' => $post_id,
        'post_title' => mb_substr($title, 0, 140),
        'post_content' => mb_substr($description, 0, 5000),
    ), true);

    if (is_wp_error($update_result)) {
        wp_send_json_error(array('message' => __('Unable to update post. Please try again.', 'nymia')));
    }

    // Handle image upload if provided
    if (!empty($_FILES['image']) && isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name']) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('image', $post_id);
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
            update_post_meta($post_id, '_nymia_social_image_url', wp_get_attachment_url($attachment_id));
        }
    }

    wp_send_json_success(nymia_prepare_social_post_payload(get_post($post_id)));
}
add_action('wp_ajax_nymia_edit_text_post', 'nymia_edit_social_post');

// ==========================================
// EDIT AUDIO POST
// ==========================================
function nymia_edit_audio_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to edit audio.', 'nymia')), 401);
        return;
    }

    check_ajax_referer('nymia_audio_upload', 'nonce');

    $audio_id = isset($_POST['audio_id']) ? intval($_POST['audio_id']) : 0;
    if (!$audio_id) {
        wp_send_json_error(array('message' => __('Invalid audio ID.', 'nymia')));
        return;
    }

    $user_id = get_current_user_id();
    
    // GET: User's audio posts
    $audio_posts = get_transient('nymia_user_audio_' . $user_id);
    if (!$audio_posts || !is_array($audio_posts)) {
        wp_send_json_error(array('message' => __('Audio post not found.', 'nymia')));
        return;
    }

    // FIND: Audio post by ID
    $audio_index = -1;
    foreach ($audio_posts as $index => $audio) {
        if (isset($audio['id']) && intval($audio['id']) === $audio_id) {
            // CHECK: User owns this audio
            if (isset($audio['user_id']) && intval($audio['user_id']) === $user_id) {
                $audio_index = $index;
                break;
            }
        }
    }

    if ($audio_index === -1) {
        wp_send_json_error(array('message' => __('Audio post not found or you do not have permission to edit it.', 'nymia')));
        return;
    }

    // GET: Form data
    $title = isset($_POST['audio_title']) ? sanitize_text_field(wp_unslash($_POST['audio_title'])) : '';
    $paid_access = isset($_POST['audio_paid_access']) ? 'yes' : 'no';
    $price = isset($_POST['audio_price']) ? floatval($_POST['audio_price']) : 0;
    $category = isset($_POST['audio_category']) ? sanitize_text_field($_POST['audio_category']) : '';
    $subcategory = isset($_POST['audio_subcategory']) ? sanitize_text_field($_POST['audio_subcategory']) : '';
    $language = isset($_POST['audio_language']) ? sanitize_text_field($_POST['audio_language']) : '';

    if (empty($title)) {
        wp_send_json_error(array('message' => __('Please enter a title.', 'nymia')));
        return;
    }

    // PROCESS: Upload cover image if provided
    $cover_image_url = '';
    if (isset($_FILES['audio_cover_image']) && $_FILES['audio_cover_image']['error'] === UPLOAD_ERR_OK) {
        $cover_file = $_FILES['audio_cover_image'];
        $allowed_image_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
        $cover_file_type = wp_check_filetype($cover_file['name']);
        $allowed_image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        if (in_array($cover_file['type'], $allowed_image_types) || in_array($cover_file_type['ext'], $allowed_image_extensions)) {
            $upload_dir = wp_upload_dir();
            $cover_dir = $upload_dir['basedir'] . '/nymia-audio-covers';
            if (!file_exists($cover_dir)) {
                wp_mkdir_p($cover_dir);
            }
            
            $cover_filename = wp_unique_filename($cover_dir, $cover_file['name']);
            $cover_destination = $cover_dir . '/' . $cover_filename;
            
            if (move_uploaded_file($cover_file['tmp_name'], $cover_destination)) {
                $cover_image_url = $upload_dir['baseurl'] . '/nymia-audio-covers/' . $cover_filename;
            }
        }
    } else {
        // Keep existing cover image if not uploading new one
        $cover_image_url = isset($audio_posts[$audio_index]['cover_image']) ? $audio_posts[$audio_index]['cover_image'] : '';
    }

    // UPDATE: Audio post data
    $audio_posts[$audio_index]['title'] = $title;
    $audio_posts[$audio_index]['paid_access'] = $paid_access;
    $audio_posts[$audio_index]['price'] = $price;
    $audio_posts[$audio_index]['category'] = !empty($category) ? $category : 'New Upload';
    $audio_posts[$audio_index]['subcategory'] = $subcategory;
    $audio_posts[$audio_index]['language'] = $language;
    if (!empty($cover_image_url)) {
        $audio_posts[$audio_index]['cover_image'] = $cover_image_url;
    }

    // UPDATE: Post meta
    update_post_meta($audio_id, '_nymia_audio_paid_access', $paid_access);
    update_post_meta($audio_id, '_nymia_audio_price', $price);
    update_post_meta($audio_id, '_nymia_audio_category', $category);
    update_post_meta($audio_id, '_nymia_audio_subcategory', $subcategory);
    update_post_meta($audio_id, '_nymia_audio_language', $language);
    if (!empty($cover_image_url)) {
        update_post_meta($audio_id, '_nymia_audio_cover_image', $cover_image_url);
    }

    // SAVE: Updated audio posts
    set_transient('nymia_user_audio_' . $user_id, $audio_posts, 30 * DAY_IN_SECONDS);

    // UPDATE: Global all audio transient
    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as $index => $audio) {
            if (isset($audio['id']) && intval($audio['id']) === $audio_id) {
                $all_audio[$index]['title'] = $title;
                $all_audio[$index]['paid_access'] = $paid_access;
                $all_audio[$index]['price'] = $price;
                $all_audio[$index]['category'] = !empty($category) ? $category : 'New Upload';
                $all_audio[$index]['subcategory'] = $subcategory;
                $all_audio[$index]['language'] = $language;
                if (!empty($cover_image_url)) {
                    $all_audio[$index]['cover_image'] = $cover_image_url;
                }
                break;
            }
        }
        set_transient('nymia_all_audio', $all_audio, 30 * DAY_IN_SECONDS);
    }

    wp_send_json_success(array(
        'message' => __('Audio updated successfully!', 'nymia'),
        'audio' => $audio_posts[$audio_index]
    ));
}
add_action('wp_ajax_nymia_edit_audio', 'nymia_edit_audio_post');

// ==========================================
// GET SINGLE AUDIO POST (for edit modal)
// ==========================================
function nymia_get_single_audio_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')), 401);
        return;
    }

    check_ajax_referer('nymia_audio_upload', 'nonce');

    $audio_id = isset($_POST['audio_id']) ? intval($_POST['audio_id']) : 0;
    if (!$audio_id) {
        wp_send_json_error(array('message' => __('Invalid audio ID.', 'nymia')));
        return;
    }

    $user_id = get_current_user_id();
    $audio_posts = get_transient('nymia_user_audio_' . $user_id);
    
    if (!$audio_posts || !is_array($audio_posts)) {
        wp_send_json_error(array('message' => __('Audio post not found.', 'nymia')));
        return;
    }

    foreach ($audio_posts as $audio) {
        if (isset($audio['id']) && intval($audio['id']) === $audio_id) {
            if (isset($audio['user_id']) && intval($audio['user_id']) === $user_id) {
                wp_send_json_success(array('audio' => $audio));
                return;
            }
        }
    }

    wp_send_json_error(array('message' => __('Audio post not found.', 'nymia')));
}
add_action('wp_ajax_nymia_get_single_audio', 'nymia_get_single_audio_post');

// ==========================================
// DELETE SOCIAL POST
// ==========================================
function nymia_delete_social_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to delete a post.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(array('message' => __('Invalid post ID.', 'nymia')));
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nymia_social_post') {
        wp_send_json_error(array('message' => __('Post not found.', 'nymia')));
    }

    // Check if user owns the post
    if (intval($post->post_author) !== get_current_user_id() && !current_user_can('delete_others_posts')) {
        wp_send_json_error(array('message' => __('You do not have permission to delete this post.', 'nymia')), 403);
    }

    $result = wp_delete_post($post_id, true);

    if (!$result) {
        wp_send_json_error(array('message' => __('Unable to delete post. Please try again.', 'nymia')));
    }

    wp_send_json_success(array('message' => __('Post deleted successfully.', 'nymia')));
}
add_action('wp_ajax_nymia_delete_text_post', 'nymia_delete_social_post');

// ==========================================
// GET SINGLE SOCIAL POST (for editing)
// ==========================================
function nymia_get_single_social_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(array('message' => __('Invalid post ID.', 'nymia')));
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nymia_social_post') {
        wp_send_json_error(array('message' => __('Post not found.', 'nymia')));
    }

    // Check if user owns the post
    if (intval($post->post_author) !== get_current_user_id() && !current_user_can('edit_others_posts')) {
        wp_send_json_error(array('message' => __('You do not have permission to view this post.', 'nymia')), 403);
    }

    wp_send_json_success(nymia_prepare_social_post_payload($post));
}
add_action('wp_ajax_nymia_get_single_post', 'nymia_get_single_social_post');

// ==========================================
// POST LIKES SYSTEM
// ==========================================
function nymia_toggle_post_like() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to like posts.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(array('message' => __('Invalid post ID.', 'nymia')));
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nymia_social_post') {
        wp_send_json_error(array('message' => __('Post not found.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $likes = get_post_meta($post_id, '_nymia_post_likes', true);
    $likes = is_array($likes) ? $likes : array();

    $is_liked = in_array($user_id, $likes);

    if ($is_liked) {
        // Unlike: remove user from likes
        $likes = array_values(array_filter($likes, function($id) use ($user_id) {
            return intval($id) !== $user_id;
        }));
        
        // REMOVE: Post from user's liked posts list (for smart suggestions)
        $user_liked_posts = get_user_meta($user_id, '_nymia_liked_posts', true);
        if (!is_array($user_liked_posts)) {
            $user_liked_posts = array();
        }
        $user_liked_posts = array_values(array_filter($user_liked_posts, function($id) use ($post_id) {
            return intval($id) !== $post_id;
        }));
        update_user_meta($user_id, '_nymia_liked_posts', $user_liked_posts);
    } else {
        // Like: add user to likes
        if (!in_array($user_id, $likes)) {
            $likes[] = $user_id;
        }
        
        // ADD: Post to user's liked posts list (for smart suggestions)
        $user_liked_posts = get_user_meta($user_id, '_nymia_liked_posts', true);
        if (!is_array($user_liked_posts)) {
            $user_liked_posts = array();
        }
        if (!in_array($post_id, $user_liked_posts)) {
            $user_liked_posts[] = $post_id;
            // Limit to last 100 liked posts to prevent bloat
            if (count($user_liked_posts) > 100) {
                $user_liked_posts = array_slice($user_liked_posts, -100);
            }
            update_user_meta($user_id, '_nymia_liked_posts', $user_liked_posts);
        }
    }

    update_post_meta($post_id, '_nymia_post_likes', $likes);
    
    // Send notification to post author if liked (not if unliked)
    if (!$is_liked) {
        $post_author_id = intval($post->post_author);
        if ($post_author_id !== $user_id && function_exists('nymia_create_notification')) {
            $post_title = get_the_title($post_id);
            $actor = get_userdata($user_id);
            $actor_name = $actor ? ($actor->display_name ?: $actor->user_login) : __('Someone', 'nymia');
            $single_post_page = get_page_by_path('single-post');
            $post_link = $single_post_page ? get_permalink($single_post_page) : home_url('/single-post/');
            $post_link = add_query_arg('post_id', $post_id, $post_link);
            nymia_create_notification(
                $post_author_id,
                'post_like',
                sprintf(__('%s liked your post "%s"', 'nymia'), $actor_name, $post_title),
                $post_link,
                $user_id
            );
        }
    }

    wp_send_json_success(array(
        'like_count' => count($likes),
        'is_liked' => !$is_liked,
    ));
}
add_action('wp_ajax_nymia_toggle_post_like', 'nymia_toggle_post_like');

// ==========================================
// POST COMMENTS SYSTEM
// ==========================================
function nymia_get_post_comments() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to view comments.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(array('message' => __('Invalid post ID.', 'nymia')));
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nymia_social_post') {
        wp_send_json_error(array('message' => __('Post not found.', 'nymia')));
    }

    $comments = get_post_meta($post_id, '_nymia_post_comments', true);
    $comments = is_array($comments) ? $comments : array();

    // Sort by timestamp (newest first)
    usort($comments, function($a, $b) {
        $time_a = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
        $time_b = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
        return $time_b <=> $time_a;
    });

    $formatted_comments = array();
    foreach ($comments as $comment) {
        $comment_user_id = isset($comment['user_id']) ? intval($comment['user_id']) : 0;
        $comment_user = $comment_user_id ? get_userdata($comment_user_id) : null;
        $display_name = $comment_user ? ($comment_user->display_name ?: $comment_user->user_login) : __('User', 'nymia');
        $username = $comment_user ? '@' . $comment_user->user_login : '@user';
        
        $avatar = get_template_directory_uri() . '/assets/images/profile.png';
        if ($comment_user_id) {
            $custom_avatar = get_user_meta($comment_user_id, 'custom_avatar', true);
            $avatar = $custom_avatar ?: get_avatar_url($comment_user_id, array('size' => 64));
        }

        // Get comment likes
        $comment_likes = isset($comment['likes']) && is_array($comment['likes']) ? $comment['likes'] : array();
        $current_user_id = get_current_user_id();
        $is_comment_liked = $current_user_id > 0 && in_array($current_user_id, $comment_likes);

        // Get replies
        $replies = isset($comment['replies']) && is_array($comment['replies']) ? $comment['replies'] : array();
        $formatted_replies = array();
        foreach ($replies as $reply) {
            $reply_user_id = isset($reply['user_id']) ? intval($reply['user_id']) : 0;
            $reply_user = $reply_user_id ? get_userdata($reply_user_id) : null;
            $reply_display_name = $reply_user ? ($reply_user->display_name ?: $reply_user->user_login) : __('User', 'nymia');
            $reply_username = $reply_user ? '@' . $reply_user->user_login : '@user';
            
            $reply_avatar = get_template_directory_uri() . '/assets/images/profile.png';
            if ($reply_user_id) {
                $reply_custom_avatar = get_user_meta($reply_user_id, 'custom_avatar', true);
                $reply_avatar = $reply_custom_avatar ?: get_avatar_url($reply_user_id, array('size' => 64));
            }

            // Get reply likes
            $reply_likes = isset($reply['likes']) && is_array($reply['likes']) ? $reply['likes'] : array();
            $is_reply_liked = $current_user_id > 0 && in_array($current_user_id, $reply_likes);

            $formatted_replies[] = array(
                'id' => isset($reply['id']) ? $reply['id'] : '',
                'user_id' => $reply_user_id,
                'author_name' => $reply_display_name,
                'author_username' => $reply_username,
                'avatar' => $avatar,
                'content' => isset($reply['content']) ? $reply['content'] : '',
                'timestamp' => isset($reply['timestamp']) ? $reply['timestamp'] : '',
                'time_ago' => isset($reply['timestamp']) ? human_time_diff(strtotime($reply['timestamp']), current_time('timestamp')) . ' ' . __('ago', 'nymia') : '',
                'like_count' => count($reply_likes),
                'is_liked' => $is_reply_liked,
            );
        }

        $formatted_comments[] = array(
            'id' => isset($comment['id']) ? $comment['id'] : '',
            'user_id' => $comment_user_id,
            'author_name' => $display_name,
            'author_username' => $username,
            'avatar' => $avatar,
            'content' => isset($comment['content']) ? $comment['content'] : '',
            'timestamp' => isset($comment['timestamp']) ? $comment['timestamp'] : '',
            'time_ago' => isset($comment['timestamp']) ? human_time_diff(strtotime($comment['timestamp']), current_time('timestamp')) . ' ' . __('ago', 'nymia') : '',
            'like_count' => count($comment_likes),
            'is_liked' => $is_comment_liked,
            'replies' => $formatted_replies,
        );
    }

    wp_send_json_success(array('comments' => $formatted_comments));
}
add_action('wp_ajax_nymia_get_post_comments', 'nymia_get_post_comments');

function nymia_submit_post_comment() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to comment.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $content = isset($_POST['content']) ? sanitize_textarea_field(wp_unslash($_POST['content'])) : '';
    $parent_comment_id = isset($_POST['parent_comment_id']) ? sanitize_text_field(wp_unslash($_POST['parent_comment_id'])) : '';

    if (!$post_id) {
        wp_send_json_error(array('message' => __('Invalid post ID.', 'nymia')));
    }

    if (trim($content) === '') {
        wp_send_json_error(array('message' => __('Please enter a comment.', 'nymia')));
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nymia_social_post') {
        wp_send_json_error(array('message' => __('Post not found.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $comments = get_post_meta($post_id, '_nymia_post_comments', true);
    $comments = is_array($comments) ? $comments : array();

    $new_comment = array(
        'id' => uniqid('comment_'),
        'user_id' => $user_id,
        'content' => mb_substr($content, 0, 2000),
        'timestamp' => current_time('mysql'),
        'likes' => array(),
        'replies' => array(),
    );

    if ($parent_comment_id) {
        // This is a reply to a comment
        foreach ($comments as $index => $comment) {
            if (isset($comment['id']) && $comment['id'] === $parent_comment_id) {
                if (!isset($comments[$index]['replies'])) {
                    $comments[$index]['replies'] = array();
                }
                $comments[$index]['replies'][] = $new_comment;
                
                // Send notification to comment author
                $comment_author_id = isset($comment['user_id']) ? intval($comment['user_id']) : 0;
                if ($comment_author_id !== $user_id && function_exists('nymia_create_notification')) {
                    $actor = get_userdata($user_id);
                    $actor_name = $actor ? ($actor->display_name ?: $actor->user_login) : __('Someone', 'nymia');
                    $single_post_page = get_page_by_path('single-post');
                    $post_link = $single_post_page ? get_permalink($single_post_page) : home_url('/single-post/');
                    $post_link = add_query_arg('post_id', $post_id, $post_link);
                    nymia_create_notification(
                        $comment_author_id,
                        'comment_reply',
                        sprintf(__('%s replied to your comment', 'nymia'), $actor_name),
                        $post_link,
                        $user_id
                    );
                }
                break;
            }
        }
    } else {
        // This is a top-level comment
        $comments[] = $new_comment;
        
        // Send notification to post author
        $post_author_id = intval($post->post_author);
        if ($post_author_id !== $user_id && function_exists('nymia_create_notification')) {
            $actor = get_userdata($user_id);
            $actor_name = $actor ? ($actor->display_name ?: $actor->user_login) : __('Someone', 'nymia');
            $post_title = get_the_title($post_id);
            $single_post_page = get_page_by_path('single-post');
            $post_link = $single_post_page ? get_permalink($single_post_page) : home_url('/single-post/');
            $post_link = add_query_arg('post_id', $post_id, $post_link);
            nymia_create_notification(
                $post_author_id,
                'post_comment',
                sprintf(__('%s commented on your post "%s"', 'nymia'), $actor_name, $post_title),
                $post_link,
                $user_id
            );
        }
    }

    update_post_meta($post_id, '_nymia_post_comments', $comments);

    // Return formatted comment
    $user = get_userdata($user_id);
    $display_name = $user ? ($user->display_name ?: $user->user_login) : __('User', 'nymia');
    $username = $user ? '@' . $user->user_login : '@user';
    $avatar = get_template_directory_uri() . '/assets/images/profile.png';
    $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
    $avatar = $custom_avatar ?: get_avatar_url($user_id, array('size' => 64));

    wp_send_json_success(array(
        'comment' => array(
            'id' => $new_comment['id'],
            'user_id' => $user_id,
            'author_name' => $display_name,
            'author_username' => $username,
            'avatar' => $avatar,
            'content' => $new_comment['content'],
            'timestamp' => $new_comment['timestamp'],
            'time_ago' => human_time_diff(strtotime($new_comment['timestamp']), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
            'like_count' => 0,
            'is_liked' => false,
            'replies' => array(),
        ),
        'parent_id' => $parent_comment_id,
    ));
}
add_action('wp_ajax_nymia_submit_post_comment', 'nymia_submit_post_comment');

function nymia_toggle_comment_like() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to like comments.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_text_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $comment_id = isset($_POST['comment_id']) ? sanitize_text_field(wp_unslash($_POST['comment_id'])) : '';
    $is_reply = isset($_POST['is_reply']) ? (bool)$_POST['is_reply'] : false;
    $parent_comment_id = isset($_POST['parent_comment_id']) ? sanitize_text_field(wp_unslash($_POST['parent_comment_id'])) : '';

    if (!$post_id || !$comment_id) {
        wp_send_json_error(array('message' => __('Invalid request.', 'nymia')));
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nymia_social_post') {
        wp_send_json_error(array('message' => __('Post not found.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $comments = get_post_meta($post_id, '_nymia_post_comments', true);
    $comments = is_array($comments) ? $comments : array();

    $found = false;
    foreach ($comments as $index => $comment) {
        if ($is_reply && isset($comment['id']) && $comment['id'] === $parent_comment_id) {
            // Handle reply like
            if (isset($comment['replies']) && is_array($comment['replies'])) {
                foreach ($comment['replies'] as $reply_index => $reply) {
                    if (isset($reply['id']) && $reply['id'] === $comment_id) {
                        $reply_likes = isset($reply['likes']) && is_array($reply['likes']) ? $reply['likes'] : array();
                        $is_liked = in_array($user_id, $reply_likes);
                        
                        if ($is_liked) {
                            $reply_likes = array_values(array_filter($reply_likes, function($id) use ($user_id) {
                                return intval($id) !== $user_id;
                            }));
                        } else {
                            if (!in_array($user_id, $reply_likes)) {
                                $reply_likes[] = $user_id;
                            }
                        }
                        
                        $comments[$index]['replies'][$reply_index]['likes'] = $reply_likes;
                        update_post_meta($post_id, '_nymia_post_comments', $comments);
                        
                        wp_send_json_success(array(
                            'like_count' => count($reply_likes),
                            'is_liked' => !$is_liked,
                        ));
                        return;
                    }
                }
            }
        } elseif (!$is_reply && isset($comment['id']) && $comment['id'] === $comment_id) {
            // Handle comment like
            $comment_likes = isset($comment['likes']) && is_array($comment['likes']) ? $comment['likes'] : array();
            $is_liked = in_array($user_id, $comment_likes);
            
            if ($is_liked) {
                $comment_likes = array_values(array_filter($comment_likes, function($id) use ($user_id) {
                    return intval($id) !== $user_id;
                }));
            } else {
                if (!in_array($user_id, $comment_likes)) {
                    $comment_likes[] = $user_id;
                }
            }
            
            $comments[$index]['likes'] = $comment_likes;
            update_post_meta($post_id, '_nymia_post_comments', $comments);
            
            wp_send_json_success(array(
                'like_count' => count($comment_likes),
                'is_liked' => !$is_liked,
            ));
            return;
        }
    }

    wp_send_json_error(array('message' => __('Comment not found.', 'nymia')));
}
add_action('wp_ajax_nymia_toggle_comment_like', 'nymia_toggle_comment_like');

function nymia_get_dashboard_recents() {
    $recents = array();

    // Get ALL Recent Ebooks (don't limit yet - we'll sort and limit after combining)
    if (function_exists('nymia_get_all_ebooks')) {
        $ebooks = nymia_get_all_ebooks();
        $ebooks = is_array($ebooks) ? $ebooks : array();
        foreach ($ebooks as $ebook) {
            $image = !empty($ebook['thumbnail']) ? $ebook['thumbnail'] : (!empty($ebook['image']) ? $ebook['image'] : (get_template_directory_uri() . '/assets/images/ebook-placeholder.jpg'));
            $author_name = !empty($ebook['author']) ? $ebook['author'] : 'Unknown Author';

            // Resolve author -> avatar and username
            $avatar = get_template_directory_uri() . '/assets/images/profile.png';
            $username = '@' . strtolower(str_replace(' ', '', $author_name));
            $user = null;
            if (!empty($ebook['user_id'])) {
                $user = get_user_by('id', intval($ebook['user_id']));
            }
            if (!$user && !empty($author_name)) {
                $user = get_user_by('display_name', $author_name);
                if (!$user) {
                    $query = new WP_User_Query(array(
                        'search' => '*' . esc_attr($author_name) . '*',
                        'search_columns' => array('display_name', 'user_login', 'user_nicename'),
                        'number' => 1,
                    ));
                    $results = $query->get_results();
                    if (!empty($results)) { $user = $results[0]; }
                }
            }
            if ($user) {
                $custom_avatar = get_user_meta($user->ID, 'custom_avatar', true);
                $avatar = $custom_avatar ?: get_avatar_url($user->ID, array('size' => 150));
                $username = '@' . $user->user_login;
            }

            $ebook_date = !empty($ebook['date']) ? strtotime($ebook['date']) : time();
            
            $recents[] = array(
                'id' => !empty($ebook['id']) ? $ebook['id'] : 0,
                'image' => $image,
                'avatar' => $avatar,
                'name' => !empty($ebook['title']) ? $ebook['title'] : 'Untitled Ebook',
                'username' => $username,
                'badge' => 'Read Book',
                '_date' => $ebook_date,
            );
        }
    }

    // Get ALL Recent Audio uploads (don't limit yet)
    // Filter to only show audio from authors and administrators, exclude subscribers
    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as $audio) {
            $user_id = isset($audio['user_id']) ? intval($audio['user_id']) : 0;
            
            // Skip if user is not an author or administrator
            if ($user_id > 0) {
                $user = get_user_by('id', $user_id);
                if (!$user || (!user_can($user_id, 'administrator') && !user_can($user_id, 'author'))) {
                    continue; // Skip subscribers and other roles
                }
            }
            
            $avatar = get_template_directory_uri() . '/assets/images/profile.png';
            $username = '@creator';
            if ($user_id) {
                $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
                $avatar = $custom_avatar ?: get_avatar_url($user_id, array('size' => 150));
                $u = get_user_by('id', $user_id);
                if ($u) { $username = '@' . $u->user_login; }
            }
            $cover_image = isset($audio['cover_image']) ? $audio['cover_image'] : '';
            $cover_image = nymia_normalize_media_url($cover_image);
            if (empty($cover_image) && !empty($audio['id'])) {
                $meta_cover = get_post_meta($audio['id'], '_nymia_audio_cover_image', true);
                $cover_image = nymia_normalize_media_url($meta_cover);
            }
            if (empty($cover_image) && $user_id) {
                $user_cover = get_user_meta($user_id, 'cover_image', true);
                $cover_image = nymia_normalize_media_url($user_cover);
            }
            if (empty($cover_image)) {
                $cover_image = get_template_directory_uri() . '/assets/images/audio-placeholder.jpg';
            }

            $audio_date = !empty($audio['date']) ? strtotime($audio['date']) : time();
            $audio_url = !empty($audio['url']) ? $audio['url'] : '';
            
            // Get unique audio track ID - CRITICAL for unique URLs
            $audio_track_id = 0;
            if (!empty($audio['id'])) {
                // The ID field is the unique identifier for each audio track
                $audio_track_id = intval($audio['id']);
            }
            
            $recents[] = array(
                'id' => $user_id, // Creator user ID
                'image' => $cover_image,
                'avatar' => $avatar,
                'name' => !empty($audio['title']) ? $audio['title'] : 'New Audio',
                'username' => $username,
                'badge' => 'Audio',
                '_date' => $audio_date,
                '_audio_url' => $audio_url, // For duplicate detection
                'audio_id' => $audio_track_id, // UNIQUE track ID for each audio post - IMPORTANT!
            );
        }
    }

    // Recent Audio Creators - add only their most recent audio upload (not the creator itself)
    // Filter to only show authors and administrators, exclude subscribers
    if (function_exists('nymia_get_all_creators_with_audio')) {
        $creators = nymia_get_all_creators_with_audio();
        $creators = is_array($creators) ? $creators : array();
        
        // Filter creators to only include 'author' or 'administrator' roles
        $creators = array_filter($creators, function($creator) {
            $creator_id = isset($creator['user_id']) ? intval($creator['user_id']) : 0;
            if (!$creator_id) return false;
            $user = get_user_by('id', $creator_id);
            if (!$user) return false;
            return user_can($creator_id, 'administrator') || user_can($creator_id, 'author');
        });
        
        foreach ($creators as $creator) {
            // Only add the most recent audio from each creator
            if (!empty($creator['audio_files']) && is_array($creator['audio_files'])) {
                $latest = $creator['audio_files'][0]; // Most recent is first
                if (empty($latest)) continue;
                
                $creator_id = isset($creator['user_id']) ? intval($creator['user_id']) : 0;
                $display_name = isset($creator['display_name']) ? $creator['display_name'] : (isset($creator['name']) ? $creator['name'] : 'Creator');

                $avatar = get_template_directory_uri() . '/assets/images/profile.png';
                if ($creator_id) {
                    $custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
                    $avatar = $custom_avatar ?: get_avatar_url($creator_id, array('size' => 150));
                }
                $username = '@' . strtolower(str_replace(' ', '', $display_name));
                $u = $creator_id ? get_user_by('id', $creator_id) : null;
                if ($u) { $username = '@' . $u->user_login; }

                $image = !empty($latest['cover_image']) ? nymia_normalize_media_url($latest['cover_image']) : '';
                if (empty($image)) {
                    $image = get_template_directory_uri() . '/assets/images/audio-placeholder.jpg';
                }
                $latest_date_ts = !empty($latest['date']) ? strtotime($latest['date']) : (time() - 86400); // Default to yesterday if no date

                // Only add if not already added from all_audio transient (avoid duplicates)
                $audio_url = !empty($latest['url']) ? $latest['url'] : '';
                $is_duplicate = false;
                if (!empty($audio_url)) {
                    foreach ($recents as $existing) {
                        if (isset($existing['_audio_url']) && $existing['_audio_url'] === $audio_url) {
                            $is_duplicate = true;
                            break;
                        }
                    }
                }

                if (!$is_duplicate) {
                    // Get audio track ID - try multiple fields with priority
                    $audio_track_id = 0;
                    
                    // Priority 1: Direct ID from latest audio entry
                    if (!empty($latest['id'])) {
                        $audio_track_id = intval($latest['id']);
                    }
                    // Priority 2: post_id as fallback
                    elseif (!empty($latest['post_id'])) {
                        $audio_track_id = intval($latest['post_id']);
                    }
                    // Priority 3: Search in user's audio transient by URL match
                    if ($audio_track_id <= 0 && !empty($audio_url) && $creator_id > 0) {
                        $user_audio = get_transient('nymia_user_audio_' . $creator_id);
                        if ($user_audio && is_array($user_audio)) {
                            foreach ($user_audio as $audio_item) {
                                if (isset($audio_item['url']) && $audio_item['url'] === $audio_url) {
                                    // Prefer 'id' field, but accept 'post_id' or attachment ID
                                    if (isset($audio_item['id']) && intval($audio_item['id']) > 0) {
                                        $audio_track_id = intval($audio_item['id']);
                                    } elseif (isset($audio_item['post_id']) && intval($audio_item['post_id']) > 0) {
                                        $audio_track_id = intval($audio_item['post_id']);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    // Priority 4: Search in global all_audio transient
                    if ($audio_track_id <= 0 && !empty($audio_url)) {
                        $all_audio = get_transient('nymia_all_audio');
                        if ($all_audio && is_array($all_audio)) {
                            foreach ($all_audio as $audio_item) {
                                if (isset($audio_item['url']) && $audio_item['url'] === $audio_url) {
                                    if (isset($audio_item['id']) && intval($audio_item['id']) > 0) {
                                        $audio_track_id = intval($audio_item['id']);
                                    } elseif (isset($audio_item['post_id']) && intval($audio_item['post_id']) > 0) {
                                        $audio_track_id = intval($audio_item['post_id']);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Store audio_id - CRITICAL for unique URLs per audio post
                    // Each audio post MUST have a unique track ID
                    $recents[] = array(
                        'id' => $creator_id, // Creator user ID (same for all tracks by same creator)
                        'image' => $image,
                        'avatar' => $avatar,
                        'name' => !empty($latest['title']) ? $latest['title'] : $display_name,
                        'username' => $username,
                        'badge' => 'Audio',
                        '_date' => $latest_date_ts,
                        '_audio_url' => $audio_url, // For duplicate detection and fallback lookup
                        'audio_id' => $audio_track_id, // UNIQUE track ID - different for each audio post!
                    );
                }
            }
        }
    }

    // Include latest social posts
    $social_posts = nymia_get_recent_social_posts(6);
    if (!empty($social_posts)) {
        foreach ($social_posts as $post) {
            $recents[] = array(
                'id' => $post['id'],
                'image' => !empty($post['image']) ? $post['image'] : $post['background'],
                'avatar' => $post['avatar'],
                'name' => $post['title'],
                'username' => $post['author_username'],
                'badge' => 'Post',
                '_date' => !empty($post['date']) ? strtotime($post['date']) : time(),
                '_post_id' => $post['id'],
            );
        }
    }

    // Sort by date desc (most recent first) and limit to 9 most recent items
    usort($recents, function($a, $b) {
        $date_a = $a['_date'] ?? 0;
        $date_b = $b['_date'] ?? 0;
        return $date_b <=> $date_a; // Descending order (newest first)
    });
    
    // Get only the 9 most recently added items
    $recents = array_slice($recents, 0, 9);

    // Remove internal fields
    foreach ($recents as &$r) { 
        unset($r['_date']); 
        unset($r['_audio_url']);
    }
    unset($r);

    return $recents;
}

function nymia_get_dashboard_data() {
    // For non-logged-in users, show rated creators in suggestions
    $suggestions = is_user_logged_in() ? nymia_get_real_user_suggestions() : nymia_get_rated_creators_for_suggestions();
    
    return array(
        'recent_content' => nymia_get_dashboard_recents(),
        'live_audio_streaming' => array(
            array(
                'id' => 1,
                'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&h=500&fit=crop',
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Alex',
                'name' => 'Kimberly',
                'username' => '@c.a.glasser',
            ),
            array(
                'id' => 2,
                'image' => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=400&h=500&fit=crop',
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Jessica',
                'name' => 'Kimberly',
                'username' => '@c.a.glasser',
            ),
            array(
                'id' => 3,
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=500&fit=crop',
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Michael',
                'name' => 'Kimberly',
                'username' => '@c.a.glasser',
            ),
            array(
                'id' => 4,
                'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=500&fit=crop',
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Lisa',
                'name' => 'Kimberly',
                'username' => '@c.a.glasser',
            ),
            array(
                'id' => 5,
                'image' => 'https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?w=400&h=500&fit=crop',
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=David',
                'name' => 'Kimberly',
                'username' => '@c.a.glasser',
            ),
        ),
        'audio_books' => nymia_get_dashboard_ebooks(),
        'suggestions' => $suggestions,
    );
} // END: nymia_get_dashboard_data()

/**
 * THEME ACTIVATION HOOK
 * ---------------------
 * Runs when theme is activated - creates pages and flushes rewrite rules
 * Hooks into: after_switch_theme
 */
function nymia_theme_activation() {
    nymia_create_dashboard_page();
    flush_rewrite_rules();
} // END: nymia_theme_activation()
add_action('after_switch_theme', 'nymia_theme_activation');

/**
 * DEBUG INFORMATION
 * ------------------
 * Displays theme debug info when ?nymia_debug=1 is added to URL
 * Only visible to admin users
 * Hooks into: wp_head
 */
function nymia_debug_info() {
    if (current_user_can('manage_options') && isset($_GET['nymia_debug'])) {
        echo '<div style="background: #000; color: #fff; padding: 20px; margin: 20px; border-radius: 5px;">';
        echo '<h3>Nymia Theme Debug Info</h3>';
        echo '<p>Theme is active: ' . (wp_get_theme()->get('Name') === 'Nymia Theme' ? 'Yes' : 'No') . '</p>';
        echo '<p>Dashboard page exists: ' . (get_page_by_path('dashboard') ? 'Yes' : 'No') . '</p>';
        echo '<p>Front page: ' . get_option('page_on_front') . '</p>';
        echo '<p>Show on front: ' . get_option('show_on_front') . '</p>';
        echo '</div>';
    }
} // END: nymia_debug_info()
add_action('wp_head', 'nymia_debug_info');

// ==========================================
// SECRET ROOM VISIBILITY SYSTEM
// ==========================================
/**
 * GET VISIBILITY DESTINATION OPTIONS
 * -----------------------------------
 * Returns available destination options for content visibility
 * 
 * @return array Array of destination options
 */
function nymia_get_visibility_destinations() {
    return array(
        'normal' => __('Normal Category', 'nymia'),
        'secret_room' => __('Secret Room (choose sub-category)', 'nymia'),
    );
}

/**
 * GET SECRET ROOM SUB-CATEGORIES
 * -------------------------------
 * Returns available sub-categories for Secret Room content
 * 
 * @return array Array of sub-category options
 */
function nymia_get_secret_room_subcategories() {
    return array(
        'audio_book' => __('Audio Book', 'nymia'),
        'live_streaming' => __('Live Streaming', 'nymia'),
        'online_now' => __('Online Now', 'nymia'),
        'audio_creator' => __('Audio Creator', 'nymia'),
        'ebook' => __('E-Book', 'nymia'),
    );
}

/**
 * GET CONTENT VISIBILITY META
 * ----------------------------
 * Gets visibility settings for a content item
 * 
 * @param int|string $content_id Content ID
 * @return array Visibility settings (destination, subcategory)
 */
function nymia_get_content_visibility($content_id) {
    // Default return value
    $visibility = array(
        'destination' => 'normal',
        'subcategory' => '',
    );
    
    // Only proceed if content_id is numeric and greater than 0
    if (!is_numeric($content_id)) {
        return $visibility;
    }
    
    $content_id = intval($content_id);
    
    // Skip if ID is 0 or negative
    if ($content_id <= 0) {
        return $visibility;
    }
    
    // Check if post exists before trying to get meta
    // get_post can return null, false, or WP_Post object - never WP_Error
    $post = get_post($content_id);
    if (!$post || !is_a($post, 'WP_Post')) {
        return $visibility;
    }
    
    // Get meta safely - get_post_meta works even if post doesn't exist, but we check anyway
    $destination = get_post_meta($content_id, '_nymia_content_destination', true);
    $subcategory = get_post_meta($content_id, '_nymia_secret_room_subcategory', true);
    
    // Validate and set destination
    if (!empty($destination) && in_array($destination, array('normal', 'secret_room'))) {
        $visibility['destination'] = $destination;
    }
    
    // Set subcategory if destination is secret_room
    if ($visibility['destination'] === 'secret_room' && !empty($subcategory)) {
        $visibility['subcategory'] = sanitize_text_field($subcategory);
    }
    
    return $visibility;
}

/**
 * SAVE CONTENT VISIBILITY META
 * -----------------------------
 * Saves visibility settings for a content item
 * 
 * @param int $content_id Content ID
 * @param string $destination 'normal' or 'secret_room'
 * @param string $subcategory Sub-category (required if destination is 'secret_room')
 * @return bool Success status
 */
function nymia_save_content_visibility($content_id, $destination, $subcategory = '') {
    $content_id = intval($content_id);
    if (!$content_id) {
        return false;
    }
    
    $destination = sanitize_text_field($destination);
    $subcategory = sanitize_text_field($subcategory);
    
    // Validate destination
    $valid_destinations = array('normal', 'secret_room');
    if (!in_array($destination, $valid_destinations)) {
        return false;
    }
    
    // If secret room, validate subcategory
    if ($destination === 'secret_room') {
        $valid_subcategories = array_keys(nymia_get_secret_room_subcategories());
        if (!in_array($subcategory, $valid_subcategories)) {
            return false;
        }
    }
    
    // Save meta
    update_post_meta($content_id, '_nymia_content_destination', $destination);
    if ($destination === 'secret_room' && !empty($subcategory)) {
        update_post_meta($content_id, '_nymia_secret_room_subcategory', $subcategory);
    } else {
        delete_post_meta($content_id, '_nymia_secret_room_subcategory');
    }
    
    return true;
}

/**
 * FILTER CONTENT BY VISIBILITY
 * -----------------------------
 * Filters content array based on visibility settings
 * 
 * @param array $content_array Array of content items
 * @param string $context 'normal' or 'secret_room'
 * @param string $subcategory Optional subcategory filter for secret room
 * @return array Filtered content array
 */
function nymia_filter_content_by_visibility($content_array, $context = 'normal', $subcategory = '') {
    if (!is_array($content_array)) {
        return array();
    }
    
    $filtered = array();
    
    foreach ($content_array as $item) {
        $content_id = isset($item['id']) ? $item['id'] : 0;
        if (!$content_id) {
            continue;
        }
        
        // Try to get visibility from post meta (for numeric IDs)
        $visibility = array('destination' => 'normal', 'subcategory' => '');
        
        if (is_numeric($content_id)) {
            $visibility = nymia_get_content_visibility(intval($content_id));
        } else {
            // For transient-based IDs (like 'audiobook_xxx'), check if visibility is in the array
            if (isset($item['visibility_destination'])) {
                $visibility['destination'] = $item['visibility_destination'];
                $visibility['subcategory'] = isset($item['visibility_subcategory']) ? $item['visibility_subcategory'] : '';
            }
        }
        
        // Filter by context
        if ($context === 'normal' && $visibility['destination'] === 'normal') {
            $filtered[] = $item;
        } elseif ($context === 'secret_room' && $visibility['destination'] === 'secret_room') {
            // If subcategory specified, filter by it
            if (empty($subcategory) || $visibility['subcategory'] === $subcategory) {
                $filtered[] = $item;
            }
        }
    }
    
    return $filtered;
}

/**
 * CHECK IF CONTENT SHOULD BE VISIBLE IN NORMAL CATEGORY
 * ------------------------------------------------------
 * Helper function to check if content should appear in normal archive pages
 * 
 * @param int|string $content_id Content ID (numeric or string)
 * @param array $item Optional content item array (for transient-based IDs)
 * @return bool True if should be visible in normal category
 */
function nymia_is_content_visible_in_normal($content_id, $item = array()) {
    // Default to visible in normal category for safety (backward compatibility)
    $visibility = array('destination' => 'normal', 'subcategory' => '');
    
    // First check if visibility is in the item array (for transient-based content)
    if (!empty($item) && is_array($item)) {
        if (isset($item['visibility_destination'])) {
            $visibility['destination'] = $item['visibility_destination'];
            $visibility['subcategory'] = isset($item['visibility_subcategory']) ? $item['visibility_subcategory'] : '';
        }
    }
    
    // If visibility not found in item array and content_id is numeric and > 0, try to get from post meta
    if ($visibility['destination'] === 'normal' && is_numeric($content_id)) {
        $content_id_int = intval($content_id);
        if ($content_id_int > 0 && function_exists('nymia_get_content_visibility')) {
            // Safely get visibility from post meta
            $meta_visibility = nymia_get_content_visibility($content_id_int);
            // Only use meta visibility if it's actually set (not default)
            if (!empty($meta_visibility['destination']) && $meta_visibility['destination'] !== 'normal') {
                $visibility = $meta_visibility;
            }
        }
    }
    
    // Return true if destination is 'normal' or empty (default to normal for backward compatibility)
    return (empty($visibility['destination']) || $visibility['destination'] === 'normal');
}

/**
 * GET SECRET ROOM CONTENT BY SUB-CATEGORY
 * ----------------------------------------
 * Retrieves all Secret Room content grouped by sub-category
 * 
 * @param string $subcategory Optional sub-category filter
 * @return array Content grouped by sub-category
 */
function nymia_get_secret_room_content($subcategory = '') {
    $secret_content = array(
        'audio_book' => array(),
        'live_streaming' => array(),
        'online_now' => array(),
        'audio_creator' => array(),
        'ebook' => array(),
    );
    
    // Get all audio files (including audiobooks)
    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as $audio) {
            $audio_id = isset($audio['id']) ? $audio['id'] : 0;
            $visibility = array('destination' => 'normal', 'subcategory' => '');
            
            if (is_numeric($audio_id) && intval($audio_id) > 0) {
                if (function_exists('nymia_get_content_visibility')) {
                    $visibility = nymia_get_content_visibility(intval($audio_id));
                }
            } elseif (isset($audio['visibility_destination'])) {
                $visibility['destination'] = $audio['visibility_destination'];
                $visibility['subcategory'] = isset($audio['visibility_subcategory']) ? $audio['visibility_subcategory'] : '';
            }
            
            if ($visibility['destination'] === 'secret_room') {
                $cat = $visibility['subcategory'];
                if (empty($subcategory) || $cat === $subcategory) {
                    if (isset($audio['type']) && $audio['type'] === 'audiobook') {
                        if (isset($secret_content[$cat])) {
                            $secret_content[$cat][] = array_merge($audio, array('content_type' => 'audiobook'));
                        }
                    } else {
                        // Regular audio content goes to audio_creator
                        if (isset($secret_content['audio_creator'])) {
                            $secret_content['audio_creator'][] = array_merge($audio, array('content_type' => 'audio'));
                        }
                    }
                }
            }
        }
    }
    
    // Get all ebooks
    $all_ebooks = get_transient('nymia_all_ebooks');
    if ($all_ebooks && is_array($all_ebooks)) {
        foreach ($all_ebooks as $ebook) {
            $ebook_id = isset($ebook['id']) ? $ebook['id'] : 0;
            $visibility = array('destination' => 'normal', 'subcategory' => '');
            if (function_exists('nymia_get_content_visibility') && is_numeric($ebook_id) && intval($ebook_id) > 0) {
                $visibility = nymia_get_content_visibility(intval($ebook_id));
            } elseif (isset($ebook['visibility_destination'])) {
                $visibility['destination'] = $ebook['visibility_destination'];
                $visibility['subcategory'] = isset($ebook['visibility_subcategory']) ? $ebook['visibility_subcategory'] : '';
            }
            
            if ($visibility['destination'] === 'secret_room') {
                $cat = $visibility['subcategory'];
                if (empty($subcategory) || $cat === $subcategory) {
                    if (isset($secret_content[$cat])) {
                        $secret_content[$cat][] = array_merge($ebook, array('content_type' => 'ebook'));
                    }
                }
            }
        }
    }
    
    // Get from user transients as well (to catch any that might not be in global)
    // Limit users to prevent performance issues
    $users = get_users(array('number' => 100));
    if (is_array($users)) {
        foreach ($users as $user) {
            if (!isset($user->ID)) {
                continue;
            }
            
            // Check user audio
            $user_audio = get_transient('nymia_user_audio_' . $user->ID);
            if ($user_audio && is_array($user_audio)) {
                foreach ($user_audio as $audio) {
                    $audio_id = isset($audio['id']) ? $audio['id'] : 0;
                    $visibility = array('destination' => 'normal', 'subcategory' => '');
                    
                    if (is_numeric($audio_id) && intval($audio_id) > 0) {
                        if (function_exists('nymia_get_content_visibility')) {
                            $visibility = nymia_get_content_visibility(intval($audio_id));
                        }
                    } elseif (isset($audio['visibility_destination'])) {
                        $visibility['destination'] = $audio['visibility_destination'];
                        $visibility['subcategory'] = isset($audio['visibility_subcategory']) ? $audio['visibility_subcategory'] : '';
                    }
                    
                    if ($visibility['destination'] === 'secret_room') {
                        $cat = $visibility['subcategory'];
                        if (empty($subcategory) || $cat === $subcategory) {
                            if (isset($audio['type']) && $audio['type'] === 'audiobook') {
                                if (isset($secret_content[$cat])) {
                                    // Check if not already added
                                    $already_added = false;
                                    foreach ($secret_content[$cat] as $existing) {
                                        if (isset($existing['id']) && $existing['id'] === $audio_id) {
                                            $already_added = true;
                                            break;
                                        }
                                    }
                                    if (!$already_added) {
                                        $secret_content[$cat][] = array_merge($audio, array('content_type' => 'audiobook'));
                                    }
                                }
                            } else {
                                if (isset($secret_content['audio_creator'])) {
                                    $already_added = false;
                                    foreach ($secret_content['audio_creator'] as $existing) {
                                        if (isset($existing['id']) && $existing['id'] === $audio_id) {
                                            $already_added = true;
                                            break;
                                        }
                                    }
                                    if (!$already_added) {
                                        $secret_content['audio_creator'][] = array_merge($audio, array('content_type' => 'audio'));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Check user ebooks
            $user_ebooks = get_transient('nymia_user_ebook_' . $user->ID);
            if ($user_ebooks && is_array($user_ebooks)) {
                foreach ($user_ebooks as $ebook) {
                    $ebook_id = isset($ebook['id']) ? $ebook['id'] : 0;
                    $visibility = array('destination' => 'normal', 'subcategory' => '');
                    if (function_exists('nymia_get_content_visibility') && is_numeric($ebook_id) && intval($ebook_id) > 0) {
                        $visibility = nymia_get_content_visibility(intval($ebook_id));
                    } elseif (isset($ebook['visibility_destination'])) {
                        $visibility['destination'] = $ebook['visibility_destination'];
                        $visibility['subcategory'] = isset($ebook['visibility_subcategory']) ? $ebook['visibility_subcategory'] : '';
                    }
                    
                    if ($visibility['destination'] === 'secret_room') {
                        $cat = $visibility['subcategory'];
                        if (empty($subcategory) || $cat === $subcategory) {
                            if (isset($secret_content[$cat])) {
                                // Check if not already added
                                $already_added = false;
                                foreach ($secret_content[$cat] as $existing) {
                                    if (isset($existing['id']) && $existing['id'] === $ebook_id) {
                                        $already_added = true;
                                        break;
                                    }
                                }
                                if (!$already_added) {
                                    $secret_content[$cat][] = array_merge($ebook, array('content_type' => 'ebook'));
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $secret_content;
}

// ==========================================
// AUDIO UPLOAD HANDLER (AJAX)
// ==========================================
/**
 * HANDLE AUDIO UPLOAD VIA AJAX
 * -----------------------------
 * Processes audio file uploads from the "Create" page
 * Handles: MP3, WAV, OGG, M4A, FLAC formats
 * Saves files to: /wp-content/uploads/nymia-audio/
 * Hooks into: wp_ajax_nymia_upload_audio
 */
function nymia_handle_audio_upload() {
    // DEBUG: Log upload request
    error_log('nymia_handle_audio_upload called');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));
    
    // SECURITY: Verify nonce (optional for development)
    /*
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nymia_audio_upload')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }
    */
    
    // CHECK: User must be logged in
    if (!is_user_logged_in()) {
        // DEVELOPMENT: Temporarily allow uploads without login
        // In production, uncomment the error response below:
        // wp_send_json_error(array('message' => 'Please log in to upload audio'));
        // return;
    }
    
    // VALIDATE: Check if file was uploaded
    if (!isset($_FILES['audio_file'])) {
        error_log('No audio_file in FILES');
        wp_send_json_error(array('message' => 'No file uploaded'));
        return;
    }
    
    // VALIDATE: Check for upload errors
    if ($_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = 'Upload error (Code: ' . $_FILES['audio_file']['error'] . ')';
        error_log('Upload error: ' . $error_msg);
        wp_send_json_error(array('message' => $error_msg));
        return;
    }
    
    // GET: Upload data from form
    $file = $_FILES['audio_file'];
    $title = sanitize_text_field($_POST['audio_title']);
    $paid_access = isset($_POST['audio_paid_access']) ? 'yes' : 'no';
    $price = floatval($_POST['audio_price']);
    $category = isset($_POST['audio_category']) ? sanitize_text_field($_POST['audio_category']) : '';
    $subcategory = isset($_POST['audio_subcategory']) ? sanitize_text_field($_POST['audio_subcategory']) : '';
    $language = isset($_POST['audio_language']) ? sanitize_text_field($_POST['audio_language']) : '';
    
    // VALIDATE: Allowed file types
    $allowed_types = array('audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/wave', 'audio/ogg', 'audio/m4a', 'audio/flac');
    $file_type = wp_check_filetype($file['name']);
    $allowed_extensions = array('mp3', 'wav', 'ogg', 'm4a', 'flac', 'mp4', 'webm');
    
    if (!in_array($file['type'], $allowed_types) && !in_array($file_type['ext'], $allowed_extensions)) {
        wp_send_json_error(array('message' => 'Invalid file type. Please upload MP3, WAV, OGG, M4A, or FLAC'));
        return;
    }
    
    // PROCESS: Upload the audio file
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    
    // CREATE: Upload directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $audio_dir = $upload_dir['basedir'] . '/nymia-audio';
    if (!file_exists($audio_dir)) {
        wp_mkdir_p($audio_dir);
    }
    
    // GENERATE: Unique filename to prevent overwrites
    $filename = wp_unique_filename($audio_dir, $file['name']);
    $destination = $audio_dir . '/' . $filename;
    
    // MOVE: Uploaded file to destination
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        wp_send_json_error(array('message' => 'Failed to move uploaded file'));
        return;
    }
    
    // CREATE: Upload array similar to wp_handle_upload
    $upload = array(
        'file' => $destination,
        'url' => $upload_dir['baseurl'] . '/nymia-audio/' . $filename,
        'type' => $file['type']
    );
    
    // PROCESS: Upload cover image if provided
    $cover_image_url = '';
    if (isset($_FILES['audio_cover_image']) && $_FILES['audio_cover_image']['error'] === UPLOAD_ERR_OK) {
        $cover_file = $_FILES['audio_cover_image'];
        
        // Validate image type
        $allowed_image_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
        $cover_file_type = wp_check_filetype($cover_file['name']);
        $allowed_image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        if (in_array($cover_file['type'], $allowed_image_types) || in_array($cover_file_type['ext'], $allowed_image_extensions)) {
            // CREATE: Upload directory for cover images
            $cover_dir = $upload_dir['basedir'] . '/nymia-audio-covers';
            if (!file_exists($cover_dir)) {
                wp_mkdir_p($cover_dir);
            }
            
            // GENERATE: Unique filename for cover image
            $cover_filename = wp_unique_filename($cover_dir, $cover_file['name']);
            $cover_destination = $cover_dir . '/' . $cover_filename;
            
            // MOVE: Cover image to destination
            if (move_uploaded_file($cover_file['tmp_name'], $cover_destination)) {
                $cover_image_url = $upload_dir['baseurl'] . '/nymia-audio-covers/' . $cover_filename;
            }
        }
    }
    
    // GENERATE: Unique attachment ID (demo purposes)
    $attach_id = time() . rand(1000, 9999);
    
    // SAVE: Custom metadata
    update_post_meta($attach_id, '_nymia_audio_paid_access', $paid_access);
    update_post_meta($attach_id, '_nymia_audio_price', $price);
    update_post_meta($attach_id, '_nymia_audio_duration', 0); // Calculated later
    update_post_meta($attach_id, '_nymia_audio_category', $category);
    update_post_meta($attach_id, '_nymia_audio_subcategory', $subcategory);
    update_post_meta($attach_id, '_nymia_audio_language', $language);
    update_post_meta($attach_id, '_nymia_audio_owner', $user_id);
    update_post_meta($attach_id, '_nymia_audio_rating_sum', 0);
    update_post_meta($attach_id, '_nymia_audio_rating_count', 0);
    if (!empty($cover_image_url)) {
        update_post_meta($attach_id, '_nymia_audio_cover_image', $cover_image_url);
    }
    
    // SAVE: Visibility settings
    $visibility_destination = isset($_POST['audio_visibility_destination']) ? sanitize_text_field($_POST['audio_visibility_destination']) : 'normal';
    $visibility_subcategory = isset($_POST['audio_visibility_subcategory']) ? sanitize_text_field($_POST['audio_visibility_subcategory']) : '';
    nymia_save_content_visibility($attach_id, $visibility_destination, $visibility_subcategory);
    
    // STORE: In user's audio collection (using transients)
    $user_id = get_current_user_id();
    if (!$user_id) {
        $user_id = 0; // Guest users
    }
    
    $audio_posts = get_transient('nymia_user_audio_' . $user_id);
    if (!$audio_posts) {
        $audio_posts = array();
    }
    
    // GET: Author name
    $author_name = 'Guest User';
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $author_name = $current_user->display_name;
    }
    
    // CREATE: Audio post data structure
    $audio_post = array(
        'id' => $attach_id,
        'title' => $title,
        'url' => $upload['url'],
        'filename' => basename($upload['file']),
        'duration' => '0:00',
        'author' => $author_name,
        'category' => !empty($category) ? $category : 'New Upload',
        'subcategory' => $subcategory,
        'language' => $language,
        'rating' => 0,
        'review_count' => 0,
        'views' => 0,
        'date' => current_time('mysql'),
        'paid_access' => $paid_access,
        'price' => $price,
        'cover_image' => $cover_image_url,
        'user_id' => $user_id,
    );
    
    // ADD: New audio to beginning of array
    array_unshift($audio_posts, $audio_post);
    
    // LIMIT: Keep only last 10 uploads
    $audio_posts = array_slice($audio_posts, 0, 10);
    
    // SAVE: To transient (30 day expiry)
    set_transient('nymia_user_audio_' . $user_id, $audio_posts, 30 * DAY_IN_SECONDS);
    
    // UPDATE: Global all audio transient
    $all_audio = get_transient('nymia_all_audio');
    if (!$all_audio) {
        $all_audio = array();
    }
    array_unshift($all_audio, $audio_post);
    // LIMIT: Keep only last 100 audio files in global list
    $all_audio = array_slice($all_audio, 0, 100);
    set_transient('nymia_all_audio', $all_audio, 30 * DAY_IN_SECONDS);
    
    // RETURN: Success response
    wp_send_json_success(array(
        'message' => 'Audio uploaded successfully',
        'audio' => $audio_post
    ));
} // END: nymia_handle_audio_upload()
add_action('wp_ajax_nymia_upload_audio', 'nymia_handle_audio_upload');

/**
 * HANDLE AUDIO BOOK UPLOAD VIA AJAX
 * -----------------------------------
 * Processes audio book file uploads from the "Create" page
 * Similar to audio upload but stores as audio book type
 * Hooks into: wp_ajax_nymia_upload_audiobook
 */
function nymia_handle_audiobook_upload() {
    // SECURITY: Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nymia_audiobook_upload')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'nymia')));
        return;
    }
    
    // CHECK: User must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to upload audio books.', 'nymia')));
        return;
    }
    
    $user_id = get_current_user_id();
    
    // VALIDATE: Required fields
    $title = isset($_POST['audiobook_title']) ? sanitize_text_field($_POST['audiobook_title']) : '';
    $category = isset($_POST['audiobook_category']) ? sanitize_text_field($_POST['audiobook_category']) : '';
    $language = isset($_POST['audiobook_language']) ? sanitize_text_field($_POST['audiobook_language']) : '';
    
    if (empty($title) || empty($category) || empty($language)) {
        wp_send_json_error(array('message' => __('Please fill in all required fields.', 'nymia')));
        return;
    }
    
    // VALIDATE: Audio file
    if (empty($_FILES['audiobook_file']['name'])) {
        wp_send_json_error(array('message' => __('Please select an audio file.', 'nymia')));
        return;
    }
    
    // VALIDATE: Thumbnail
    if (empty($_FILES['audiobook_thumbnail']['name'])) {
        wp_send_json_error(array('message' => __('Please upload a thumbnail image.', 'nymia')));
        return;
    }
    
    // Process audio file upload
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    
    $audio_upload = wp_handle_upload($_FILES['audiobook_file'], array('test_form' => false));
    if (isset($audio_upload['error'])) {
        wp_send_json_error(array('message' => sprintf(__('Audio upload failed: %s', 'nymia'), $audio_upload['error'])));
        return;
    }
    
    // Process thumbnail upload
    $thumbnail_upload = wp_handle_upload($_FILES['audiobook_thumbnail'], array('test_form' => false));
    if (isset($thumbnail_upload['error'])) {
        wp_send_json_error(array('message' => sprintf(__('Thumbnail upload failed: %s', 'nymia'), $thumbnail_upload['error'])));
        return;
    }
    
    // Get user audio data
    $user_audio = get_transient('nymia_user_audio_' . $user_id);
    if (!is_array($user_audio)) {
        $user_audio = array();
    }
    
    // Get author name
    $author_name = 'Unknown';
    if ($user_id) {
        $user = get_user_by('id', $user_id);
        if ($user) {
            $author_name = $user->display_name ?: $user->user_login;
        }
    }
    
    // Get visibility settings
    $visibility_destination = isset($_POST['audiobook_visibility_destination']) ? sanitize_text_field($_POST['audiobook_visibility_destination']) : 'normal';
    $visibility_subcategory = isset($_POST['audiobook_visibility_subcategory']) ? sanitize_text_field($_POST['audiobook_visibility_subcategory']) : '';
    
    // Create new audio book entry
    $audiobook_id = uniqid('audiobook_');
    $new_audiobook = array(
        'id' => $audiobook_id,
        'title' => $title,
        'description' => isset($_POST['audiobook_description']) ? sanitize_textarea_field($_POST['audiobook_description']) : '',
        'category' => $category,
        'language' => $language,
        'url' => $audio_upload['url'],
        'cover_image' => $thumbnail_upload['url'],
        'thumbnail' => $thumbnail_upload['url'], // Alias for compatibility
        'paid_access' => isset($_POST['audiobook_paid_access']) && $_POST['audiobook_paid_access'] === 'on' ? 'yes' : 'no',
        'price' => isset($_POST['audiobook_price']) && $_POST['audiobook_paid_access'] === 'on' ? floatval($_POST['audiobook_price']) : 0,
        'type' => 'audiobook',
        'created' => current_time('mysql'),
        'date' => current_time('mysql'), // Alias for compatibility
        'user_id' => $user_id,
        'author' => $author_name,
        'visibility_destination' => $visibility_destination,
        'visibility_subcategory' => $visibility_subcategory,
    );
    
    // Save visibility to post meta if we have a numeric ID (for compatibility)
    if (is_numeric($audiobook_id)) {
        nymia_save_content_visibility(intval($audiobook_id), $visibility_destination, $visibility_subcategory);
    }
    
    // Add to user's audio list
    array_unshift($user_audio, $new_audiobook);
    
    // Save to transient
    set_transient('nymia_user_audio_' . $user_id, $user_audio, 30 * DAY_IN_SECONDS);
    
    // Also add to global audio list
    $all_audio = get_transient('nymia_all_audio');
    if (!is_array($all_audio)) {
        $all_audio = array();
    }
    array_unshift($all_audio, $new_audiobook);
    set_transient('nymia_all_audio', $all_audio, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array(
        'message' => __('Audio book uploaded successfully!', 'nymia'),
        'audiobook' => $new_audiobook
    ));
}
add_action('wp_ajax_nymia_upload_audiobook', 'nymia_handle_audiobook_upload');

/**
 * GET ALL AUDIO BOOKS
 * -------------------
 * Retrieves all audio books from transients
 * Filters audio files by type='audiobook'
 * Filters out Secret Room content for normal display
 * @return array All audio books
 */
function nymia_get_all_audiobooks() {
    $all_audiobooks = array();
    $seen_ids = array(); // Track IDs to avoid duplicates
    
    // Get all audio from global transient
    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        // Filter to only get audio books
        foreach ($all_audio as $audio) {
            if (isset($audio['type']) && $audio['type'] === 'audiobook') {
                $audio_id = isset($audio['id']) ? $audio['id'] : '';
                if ($audio_id && !in_array($audio_id, $seen_ids)) {
                    // Filter out Secret Room content - only show normal category
                    if (function_exists('nymia_is_content_visible_in_normal')) {
                        if (!nymia_is_content_visible_in_normal($audio_id, $audio)) {
                            continue; // Skip Secret Room content
                        }
                    }
                    $all_audiobooks[] = $audio;
                    $seen_ids[] = $audio_id;
                }
            }
        }
    }
    
    // Also get from individual user transients (to catch any that might not be in global)
    $users = get_users(array('number' => 200)); // Increased limit
    foreach ($users as $user) {
        $user_audio = get_transient('nymia_user_audio_' . $user->ID);
        if ($user_audio && is_array($user_audio)) {
            foreach ($user_audio as $audio) {
                if (isset($audio['type']) && $audio['type'] === 'audiobook') {
                    $audio_id = isset($audio['id']) ? $audio['id'] : '';
                    // Only add if not already in list
                    if ($audio_id && !in_array($audio_id, $seen_ids)) {
                        // Filter out Secret Room content
                        if (function_exists('nymia_is_content_visible_in_normal')) {
                            if (!nymia_is_content_visible_in_normal($audio_id, $audio)) {
                                continue; // Skip Secret Room content
                            }
                        }
                        
                        // Ensure author field is set
                        if (empty($audio['author']) && !empty($audio['user_id'])) {
                            $user_obj = get_user_by('id', intval($audio['user_id']));
                            if ($user_obj) {
                                $audio['author'] = $user_obj->display_name ?: $user_obj->user_login;
                            }
                        }
                        $all_audiobooks[] = $audio;
                        $seen_ids[] = $audio_id;
                    }
                }
            }
        }
    }
    
    // Sort by date (most recent first)
    usort($all_audiobooks, function($a, $b) {
        $date_a = isset($a['created']) ? strtotime($a['created']) : (isset($a['date']) ? strtotime($a['date']) : 0);
        $date_b = isset($b['created']) ? strtotime($b['created']) : (isset($b['date']) ? strtotime($b['date']) : 0);
        return $date_b - $date_a;
    });
    
    return $all_audiobooks;
}

/**
 * GET USER'S AUDIO POSTS
 * ----------------------
 * Retrieves all audio uploads for the current user
 * @return array User's audio posts
 */
function nymia_get_user_audio_posts() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        $user_id = 0; // Use 0 for guest users
    }
    
    $audio_posts = get_transient('nymia_user_audio_' . $user_id);
    
    return $audio_posts ? $audio_posts : array();
} // END: nymia_get_user_audio_posts()

/**
 * Determine if the current viewer can access paid audio for a given creator.
 * Allows creators and privileged roles to bypass paywalls. Other viewers must purchase.
 *
 * @param int $creator_id Creator (author) user ID tied to the audio content.
 * @param int|null $user_id Optional override for user ID (defaults to current user).
 * @return bool
 */
function nymia_can_user_access_paid_audio($creator_id, $user_id = null) {
    $creator_id = intval($creator_id);
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    $user_id = intval($user_id);

    $allow = false;

    if ($creator_id && $user_id && $creator_id === $user_id) {
        // Creators can always access their own paid audio
        $allow = true;
    } elseif ($user_id) {
        $user = wp_get_current_user();
        $roles = is_user_logged_in() ? (array) $user->roles : array();
        $privileged_roles = apply_filters('nymia_paid_audio_privileged_roles', array('administrator', 'editor', 'author', 'shop_manager'));
        if (!empty(array_intersect($privileged_roles, $roles))) {
            $allow = true;
        }
    }

    /**
     * Filter: allow plugins/integrations to unlock paid audio access (e.g. after purchase).
     */
    return apply_filters('nymia_user_can_access_paid_audio', $allow, $user_id, $creator_id);
}

/**
 * Determine whether a creator has an approved KYC status.
 *
 * @param int $user_id
 * @return bool
 */
function nymia_is_creator_verified($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return false;
    }

    if (!user_can($user_id, 'edit_posts')) {
        return false;
    }

    $status = get_user_meta($user_id, 'nymia_creator_kyc_status', true);
    return $status === 'approved';
}

/**
 * Get the markup for a verified creator badge.
 *
 * @param int $user_id
 * @param string|null $label Optional label text.
 * @param int    $user_id
 * @param string|null $label Optional label; defaults to none (icon-only).
 * @param string $extra_class Additional classes to append to badge element.
 *
 * @return string
 */
function nymia_get_creator_badge_markup($user_id, $label = null, $extra_class = '') {
    if (!nymia_is_creator_verified($user_id)) {
        return '';
    }

    $sr_label = __('Verified Creator', 'nymia');

    $icon_path = get_template_directory() . '/assets/images/blue.png';
    if (file_exists($icon_path)) {
        $icon_src = esc_url(get_template_directory_uri() . '/assets/images/blue.png');
    } else {
        $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="#1D9BF0"/><path fill="#fff" d="M10.6 15.6 7.3 12.3 8.7 10.9 10.6 12.8 15.3 8.1 16.7 9.5z"/></svg>';
        $icon_src = 'data:image/svg+xml,' . rawurlencode($icon_svg);
    }

    $classes = 'nymia-creator-badge';
    if (!empty($extra_class)) {
        $classes .= ' ' . trim($extra_class);
    }

    $badge  = '<span class="' . esc_attr($classes) . '" role="img" aria-label="' . esc_attr($sr_label) . '" title="' . esc_attr($sr_label) . '">';
    $badge .= '<img src="' . esc_attr($icon_src) . '" alt="" />';
    $badge .= '<span class="screen-reader-text">' . esc_html($label !== null ? $label : $sr_label) . '</span>';
    $badge .= '</span>';

    /**
     * Filter the rendered creator badge markup.
     */
    return apply_filters('nymia_creator_badge_markup', $badge, $user_id, $label);
}

/**
 * Check if a user is a verified regular user (email verified but not a creator)
 *
 * @param int $user_id
 * @return bool
 */
function nymia_is_user_verified($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return false;
    }

    // Check if user is a creator first - creators get blue badge, not green badge
    if (nymia_is_creator_verified($user_id)) {
        return false; // Creators get the blue badge, not the verified user badge
    }

    // Check if user has approved creator KYC status
    $creator_kyc_status = get_user_meta($user_id, 'nymia_creator_kyc_status', true);
    if ($creator_kyc_status === 'approved') {
        return false; // Don't show verified badge for approved creators
    }

    // Check if email is verified
    $email_verification_enabled = get_option('nymia_enable_email_verification', '1');
    
    if ($email_verification_enabled == '1') {
        // Email verification is enabled, check if verified
        $email_verified = get_user_meta($user_id, 'email_verified', true);
        
        // If email_verified is not set (old users), check if they have a verification code
        // If they have a code but haven't verified, they're not verified
        // If they don't have a code and verification is enabled, they need to verify
        if (empty($email_verified)) {
            // Check if user has a pending verification code
            $verification_code = get_user_meta($user_id, 'verification_code', true);
            if (!empty($verification_code)) {
                // User has a code but hasn't verified yet
                return false;
            }
            // For users who signed up before verification was enabled, 
            // if no verification code exists, consider them verified (backward compatibility)
            // But only if they're not a creator
            return true;
        }
        
        // Explicitly check if verified
        if ($email_verified !== '1') {
            return false;
        }
    } else {
        // Email verification is disabled, consider all non-creator users as verified
        // (for backward compatibility with users who signed up before verification)
        return true;
    }

    return true;
}

/**
 * Get the markup for a verified user badge (for regular users, not creators).
 *
 * @param int $user_id
 * @param string|null $label Optional label text.
 * @param string $extra_class Additional classes to append to badge element.
 *
 * @return string
 */
function nymia_get_verified_user_badge_markup($user_id, $label = null, $extra_class = '') {
    if (!nymia_is_user_verified($user_id)) {
        return '';
    }

    $sr_label = __('Verified User', 'nymia');

    // Use the approved.png badge image for verified users
    $icon_path = get_template_directory() . '/assets/images/approved.png';
    if (file_exists($icon_path)) {
        $icon_src = esc_url(get_template_directory_uri() . '/assets/images/approved.png');
    } else {
        // Fallback to SVG if image doesn't exist
        $icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" fill="#10B981" fill-opacity="0.1" stroke="#10B981"/><path d="M9 12l2 2 4-4" stroke="#ffffff" stroke-width="2.5" fill="none"/></svg>';
        $icon_src = 'data:image/svg+xml;charset=utf-8,' . rawurlencode($icon_svg);
    }

    $classes = 'nymia-verified-user-badge';
    if (!empty($extra_class)) {
        $classes .= ' ' . trim($extra_class);
    }

    $badge  = '<span class="' . esc_attr($classes) . '" role="img" aria-label="' . esc_attr($sr_label) . '" title="' . esc_attr($sr_label) . '">';
    $badge .= '<img src="' . esc_attr($icon_src) . '" alt="" />';
    $badge .= '<span class="screen-reader-text">' . esc_html($label !== null ? $label : $sr_label) . '</span>';
    $badge .= '</span>';

    /**
     * Filter the rendered verified user badge markup.
     */
    return apply_filters('nymia_verified_user_badge_markup', $badge, $user_id, $label);
}

/**
 * Get the appropriate badge markup for a user (creator badge or verified user badge)
 * This is a convenience function that returns creator badge if user is a creator,
 * otherwise returns verified user badge if user is verified
 *
 * @param int $user_id
 * @param string|null $label Optional label text.
 * @param string $extra_class Additional classes to append to badge element.
 *
 * @return string
 */
function nymia_get_user_badge_markup($user_id, $label = null, $extra_class = '') {
    // First check for creator badge (blue badge)
    $creator_badge = nymia_get_creator_badge_markup($user_id, $label, $extra_class);
    if ($creator_badge) {
        return $creator_badge;
    }
    
    // If not a creator, check for verified user badge (green badge)
    // Replace creator-badge class with verified-user-badge class if needed
    $badge_class = $extra_class;
    if (!empty($extra_class)) {
        // Replace any creator-badge references with verified-user-badge
        $badge_class = str_replace('nymia-creator-badge--inline', 'nymia-verified-user-badge--inline', $badge_class);
        $badge_class = str_replace('nymia-creator-badge', 'nymia-verified-user-badge', $badge_class);
        // If no replacement happened and it's not already a verified badge class, add inline class
        if (strpos($badge_class, 'nymia-verified-user-badge') === false) {
            $badge_class = 'nymia-verified-user-badge--inline';
        }
    }
    $verified_user_badge = nymia_get_verified_user_badge_markup($user_id, $label, $badge_class);
    if ($verified_user_badge) {
        return $verified_user_badge;
    }
    
    // No badge
    return '';
}

/**
 * INCREMENT AUDIO VIEWS (AJAX HANDLER)
 * Tracks when an audio file is played and increments view count
 */
function nymia_increment_audio_views_handler() {
    // Verify nonce (reuse the main ajax nonce used across uploads)
    check_ajax_referer('nymia_audio_upload', 'nonce');
    
    // Get parameters
    $creator_id = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : 0;
    $audio_url = isset($_POST['audio_url']) ? esc_url_raw($_POST['audio_url']) : '';
    
    if (!$creator_id || !$audio_url) {
        wp_send_json_error(array('message' => 'Invalid parameters'));
        return;
    }
    
    // Get creator's audio posts
    $audio_posts = get_transient('nymia_user_audio_' . $creator_id);
    if (!$audio_posts || !is_array($audio_posts)) {
        wp_send_json_error(array('message' => 'No audio found'));
        return;
    }
    
    // Normalize target URL parts for robust matching
    $target_parts = wp_parse_url($audio_url);
    $target_path = isset($target_parts['path']) ? $target_parts['path'] : '';
    $target_file = $target_path ? wp_basename($target_path) : '';

    // Find and increment views for the matching audio file
    $updated = false;
    $total_views = 0;
    foreach ($audio_posts as &$audio_post) {
        $post_url = !empty($audio_post['url']) ? $audio_post['url'] : '';
        $is_match = false;
        if ($post_url) {
            // Exact match first
            if ($post_url === $audio_url) {
                $is_match = true;
            } else {
                // Fallback: compare by filename to avoid query string/cdn variations
                $post_parts = wp_parse_url($post_url);
                $post_path = isset($post_parts['path']) ? $post_parts['path'] : '';
                $post_file = $post_path ? wp_basename($post_path) : '';
                if ($target_file && $post_file && $target_file === $post_file) {
                    $is_match = true;
                }
            }
        }

        if ($is_match) {
            $audio_post['views'] = isset($audio_post['views']) ? intval($audio_post['views']) + 1 : 1;
            $updated = true;
        }
        $total_views += isset($audio_post['views']) ? intval($audio_post['views']) : 0;
    }
    unset($audio_post); // Release reference
    
    // Save updated audio posts back to transient
    if ($updated) {
        set_transient('nymia_user_audio_' . $creator_id, $audio_posts, 30 * DAY_IN_SECONDS);
        
        // Also update global all audio transient
        $all_audio = get_transient('nymia_all_audio');
        if ($all_audio && is_array($all_audio)) {
            foreach ($all_audio as &$global_audio) {
                if (isset($global_audio['user_id']) && $global_audio['user_id'] == $creator_id && !empty($global_audio['url'])) {
                    $post_url = $global_audio['url'];
                    $match = false;
                    if ($post_url === $audio_url) {
                        $match = true;
                    } else {
                        $post_parts = wp_parse_url($post_url);
                        $post_path = isset($post_parts['path']) ? $post_parts['path'] : '';
                        $post_file = $post_path ? wp_basename($post_path) : '';
                        if ($target_file && $post_file && $target_file === $post_file) {
                            $match = true;
                        }
                    }
                    if ($match) {
                        $global_audio['views'] = isset($global_audio['views']) ? intval($global_audio['views']) + 1 : 1;
                    }
                }
            }
            unset($global_audio);
            set_transient('nymia_all_audio', $all_audio, 30 * DAY_IN_SECONDS);
        }
    }
    
    // Format total views for display
    $views_formatted = $total_views >= 1000 ? round($total_views / 1000, 1) . 'k' : (string)$total_views;
    
    wp_send_json_success(array(
        'message' => 'Views updated',
        'total_views' => $total_views,
        'views_formatted' => $views_formatted
    ));
}
add_action('wp_ajax_nymia_increment_audio_views', 'nymia_increment_audio_views_handler');
add_action('wp_ajax_nopriv_nymia_increment_audio_views', 'nymia_increment_audio_views_handler');

// ==========================================
// AUDIO REVIEWS (RATINGS + COMMENTS)
// ==========================================
function nymia_get_audio_reviews($audio_id) {
    $reviews = get_post_meta($audio_id, '_nymia_audio_reviews', true);
    return is_array($reviews) ? $reviews : array();
}

function nymia_get_audio_owner_id($audio_id) {
    $owner = get_post_meta($audio_id, '_nymia_audio_owner', true);
    if ($owner) {
        return intval($owner);
    }

    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as $audio) {
            if (isset($audio['id']) && intval($audio['id']) === intval($audio_id)) {
                $owner_id = isset($audio['user_id']) ? intval($audio['user_id']) : 0;
                if ($owner_id) {
                    update_post_meta($audio_id, '_nymia_audio_owner', $owner_id);
                }
                return $owner_id;
            }
        }
    }
    return 0;
}

/**
 * Ensure audio entries in transients carry the correct creator ID.
 */
function nymia_sync_audio_creator_reference($audio_id, $creator_id) {
    $audio_id = intval($audio_id);
    $creator_id = intval($creator_id);
    if (!$audio_id || !$creator_id) {
        return;
    }

    $all_audio = get_transient('nymia_all_audio');
    $updated_all = false;
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as &$audio_entry) {
            if (isset($audio_entry['id']) && intval($audio_entry['id']) === $audio_id) {
                if (!isset($audio_entry['user_id']) || intval($audio_entry['user_id']) !== $creator_id) {
                    $audio_entry['user_id'] = $creator_id;
                    $updated_all = true;
                }
                break;
            }
        }
        unset($audio_entry);
        if ($updated_all) {
            set_transient('nymia_all_audio', $all_audio, 30 * DAY_IN_SECONDS);
        }
    }

    $user_audio = get_transient('nymia_user_audio_' . $creator_id);
    $updated_user = false;
    if ($user_audio && is_array($user_audio)) {
        foreach ($user_audio as &$audio_entry) {
            if (isset($audio_entry['id']) && intval($audio_entry['id']) === $audio_id) {
                if (!isset($audio_entry['user_id']) || intval($audio_entry['user_id']) !== $creator_id) {
                    $audio_entry['user_id'] = $creator_id;
                    $updated_user = true;
                }
                break;
            }
        }
        unset($audio_entry);
        if ($updated_user) {
            set_transient('nymia_user_audio_' . $creator_id, $user_audio, 30 * DAY_IN_SECONDS);
        }
    }
}

function nymia_prepare_audio_reviews_payload($audio_id) {
    $reviews = nymia_get_audio_reviews($audio_id);
    $count = count($reviews);
    $average = 0;
    if ($count > 0) {
        $sum = 0;
        foreach ($reviews as $entry) {
            $sum += isset($entry['rating']) ? intval($entry['rating']) : 0;
        }
        $average = $sum > 0 ? round($sum / $count, 1) : 0;
    }

    $current_user_id = get_current_user_id();
    $formatted = array();
    foreach ($reviews as $entry) {
        $user_id = isset($entry['user_id']) ? intval($entry['user_id']) : 0;
        $display_name = isset($entry['display_name']) ? $entry['display_name'] : '';
        $user_avatar = get_template_directory_uri() . '/assets/images/profile.png';
        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $display_name = $display_name ?: $user->display_name;
                $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
                $user_avatar = $custom_avatar ?: get_avatar_url($user_id, array('size' => 64));
            }
        }

        // Get review likes
        $review_likes = isset($entry['likes']) && is_array($entry['likes']) ? $entry['likes'] : array();
        $is_review_liked = $current_user_id > 0 && in_array($current_user_id, $review_likes);
        
        // Get replies
        $replies = isset($entry['replies']) && is_array($entry['replies']) ? $entry['replies'] : array();
        $formatted_replies = array();
        foreach ($replies as $reply) {
            $reply_user_id = isset($reply['user_id']) ? intval($reply['user_id']) : 0;
            $reply_user = $reply_user_id ? get_userdata($reply_user_id) : null;
            $reply_display_name = $reply_user ? ($reply_user->display_name ?: $reply_user->user_login) : __('User', 'nymia');
            
            $reply_avatar = get_template_directory_uri() . '/assets/images/profile.png';
            if ($reply_user_id) {
                $reply_custom_avatar = get_user_meta($reply_user_id, 'custom_avatar', true);
                $reply_avatar = $reply_custom_avatar ?: get_avatar_url($reply_user_id, array('size' => 64));
            }
            
            // Get reply likes
            $reply_likes = isset($reply['likes']) && is_array($reply['likes']) ? $reply['likes'] : array();
            $is_reply_liked = $current_user_id > 0 && in_array($current_user_id, $reply_likes);
            
            $formatted_replies[] = array(
                'id' => isset($reply['id']) ? $reply['id'] : '',
                'user_id' => $reply_user_id,
                'display_name' => $reply_display_name,
                'avatar' => esc_url($reply_avatar),
                'comment' => isset($reply['comment']) ? $reply['comment'] : '',
                'timestamp' => isset($reply['timestamp']) ? $reply['timestamp'] : current_time('mysql'),
                'time_human' => human_time_diff(strtotime(isset($reply['timestamp']) ? $reply['timestamp'] : current_time('mysql')), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
                'like_count' => count($reply_likes),
                'is_liked' => $is_reply_liked,
            );
        }

        $formatted[] = array(
            'id' => isset($entry['id']) ? $entry['id'] : uniqid('review_'),
            'user_id' => $user_id,
            'display_name' => $display_name ?: __('Listener', 'nymia'),
            'avatar' => esc_url($user_avatar),
            'rating' => isset($entry['rating']) ? intval($entry['rating']) : 0,
            'comment' => isset($entry['comment']) ? $entry['comment'] : '',
            'timestamp' => isset($entry['timestamp']) ? $entry['timestamp'] : current_time('mysql'),
            'time_human' => human_time_diff(strtotime(isset($entry['timestamp']) ? $entry['timestamp'] : current_time('mysql')), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
            'like_count' => count($review_likes),
            'is_liked' => $is_review_liked,
            'replies' => $formatted_replies,
        );
    }

    return array(
        'audio_id' => intval($audio_id),
        'average' => $average,
        'count' => $count,
        'review_count' => $count,
        'reviews' => $formatted
    );
}

function nymia_sync_audio_rating_in_cache($audio_id, $average, $count = null) {
    $updated = false;
    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as $index => $audio) {
            if (isset($audio['id']) && intval($audio['id']) === intval($audio_id)) {
                $all_audio[$index]['rating'] = $average;
                if ($count !== null) {
                    $all_audio[$index]['review_count'] = $count;
                }
                $updated = true;
                break;
            }
        }
        if ($updated) {
            set_transient('nymia_all_audio', $all_audio, 30 * DAY_IN_SECONDS);
        }
    }

    $owner_id = nymia_get_audio_owner_id($audio_id);
    if ($owner_id) {
        $user_audio = get_transient('nymia_user_audio_' . $owner_id);
        if ($user_audio && is_array($user_audio)) {
            foreach ($user_audio as $index => $audio) {
                if (isset($audio['id']) && intval($audio['id']) === intval($audio_id)) {
                    $user_audio[$index]['rating'] = $average;
                    if ($count !== null) {
                        $user_audio[$index]['review_count'] = $count;
                    }
                    break;
                }
            }
            set_transient('nymia_user_audio_' . $owner_id, $user_audio, 30 * DAY_IN_SECONDS);
        }
    }
}

function nymia_submit_audio_review() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to leave a review.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_audio_review_nonce', 'nonce');

    $audio_id = isset($_POST['audio_id']) ? intval($_POST['audio_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

    if (!$audio_id) {
        wp_send_json_error(array('message' => __('Invalid audio track.', 'nymia')));
    }

    if ($rating < 1 || $rating > 5) {
        wp_send_json_error(array('message' => __('Rating must be between 1 and 5 stars.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $reviews = nymia_get_audio_reviews($audio_id);
    $timestamp = current_time('mysql');
    $display_name = wp_get_current_user()->display_name ?: wp_get_current_user()->user_login;

    $updated = false;
    foreach ($reviews as $index => $entry) {
        if (isset($entry['user_id']) && intval($entry['user_id']) === $user_id) {
            $reviews[$index]['rating'] = $rating;
            $reviews[$index]['comment'] = $comment;
            $reviews[$index]['timestamp'] = $timestamp;
            $reviews[$index]['display_name'] = $display_name;
            $updated = true;
            break;
        }
    }

    if (!$updated) {
        $reviews[] = array(
            'user_id' => $user_id,
            'display_name' => $display_name,
            'rating' => $rating,
            'comment' => $comment,
            'timestamp' => $timestamp
        );
    }

    update_post_meta($audio_id, '_nymia_audio_reviews', $reviews);

    $sum = 0;
    foreach ($reviews as $entry) {
        $sum += isset($entry['rating']) ? intval($entry['rating']) : 0;
    }
    $count = count($reviews);
    $average = $count > 0 ? round($sum / $count, 1) : 0;

    update_post_meta($audio_id, '_nymia_audio_rating_sum', $sum);
    update_post_meta($audio_id, '_nymia_audio_rating_count', $count);

    nymia_sync_audio_rating_in_cache($audio_id, $average, $count);

    wp_send_json_success(nymia_prepare_audio_reviews_payload($audio_id));
}
add_action('wp_ajax_nymia_submit_audio_review', 'nymia_submit_audio_review');

function nymia_fetch_audio_reviews() {
    check_ajax_referer('nymia_audio_review_nonce', 'nonce');
    $audio_id = isset($_POST['audio_id']) ? intval($_POST['audio_id']) : 0;
    if (!$audio_id) {
        wp_send_json_error(array('message' => __('Invalid audio track.', 'nymia')));
    }
    wp_send_json_success(nymia_prepare_audio_reviews_payload($audio_id));
}
add_action('wp_ajax_nymia_fetch_audio_reviews', 'nymia_fetch_audio_reviews');
add_action('wp_ajax_nopriv_nymia_fetch_audio_reviews', 'nymia_fetch_audio_reviews');

// ==========================================
// AUDIO REVIEW LIKES & REPLIES
// ==========================================
function nymia_toggle_audio_review_like() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to like reviews.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_audio_review_nonce', 'nonce');

    $audio_id = isset($_POST['audio_id']) ? intval($_POST['audio_id']) : 0;
    $review_id = isset($_POST['review_id']) ? sanitize_text_field(wp_unslash($_POST['review_id'])) : '';
    $is_reply = isset($_POST['is_reply']) ? (bool)$_POST['is_reply'] : false;
    $parent_review_id = isset($_POST['parent_review_id']) ? sanitize_text_field(wp_unslash($_POST['parent_review_id'])) : '';

    if (!$audio_id || !$review_id) {
        wp_send_json_error(array('message' => __('Invalid request.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $reviews = nymia_get_audio_reviews($audio_id);

    foreach ($reviews as $index => $review) {
        if ($is_reply && isset($review['id']) && $review['id'] === $parent_review_id) {
            // Handle reply like
            if (isset($review['replies']) && is_array($review['replies'])) {
                foreach ($review['replies'] as $reply_index => $reply) {
                    if (isset($reply['id']) && $reply['id'] === $review_id) {
                        $reply_likes = isset($reply['likes']) && is_array($reply['likes']) ? $reply['likes'] : array();
                        $is_liked = in_array($user_id, $reply_likes);
                        
                        if ($is_liked) {
                            $reply_likes = array_values(array_filter($reply_likes, function($id) use ($user_id) {
                                return intval($id) !== $user_id;
                            }));
                        } else {
                            if (!in_array($user_id, $reply_likes)) {
                                $reply_likes[] = $user_id;
                            }
                        }
                        
                        $reviews[$index]['replies'][$reply_index]['likes'] = $reply_likes;
                        update_post_meta($audio_id, '_nymia_audio_reviews', $reviews);
                        
                        wp_send_json_success(array(
                            'like_count' => count($reply_likes),
                            'is_liked' => !$is_liked,
                        ));
                        return;
                    }
                }
            }
        } elseif (!$is_reply && isset($review['id']) && $review['id'] === $review_id) {
            // Handle review like
            $review_likes = isset($review['likes']) && is_array($review['likes']) ? $review['likes'] : array();
            $is_liked = in_array($user_id, $review_likes);
            
            if ($is_liked) {
                $review_likes = array_values(array_filter($review_likes, function($id) use ($user_id) {
                    return intval($id) !== $user_id;
                }));
            } else {
                if (!in_array($user_id, $review_likes)) {
                    $review_likes[] = $user_id;
                }
            }
            
            $reviews[$index]['likes'] = $review_likes;
            update_post_meta($audio_id, '_nymia_audio_reviews', $reviews);
            
            wp_send_json_success(array(
                'like_count' => count($review_likes),
                'is_liked' => !$is_liked,
            ));
            return;
        }
    }

    wp_send_json_error(array('message' => __('Review not found.', 'nymia')));
}
add_action('wp_ajax_nymia_toggle_audio_review_like', 'nymia_toggle_audio_review_like');

function nymia_submit_audio_review_reply() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to reply.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_audio_review_nonce', 'nonce');

    $audio_id = isset($_POST['audio_id']) ? intval($_POST['audio_id']) : 0;
    $parent_review_id = isset($_POST['parent_review_id']) ? sanitize_text_field(wp_unslash($_POST['parent_review_id'])) : '';
    $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

    if (!$audio_id || !$parent_review_id) {
        wp_send_json_error(array('message' => __('Invalid request.', 'nymia')));
    }

    if (trim($comment) === '') {
        wp_send_json_error(array('message' => __('Please enter a reply.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $reviews = nymia_get_audio_reviews($audio_id);
    $timestamp = current_time('mysql');
    $display_name = wp_get_current_user()->display_name ?: wp_get_current_user()->user_login;

    foreach ($reviews as $index => $review) {
        if (isset($review['id']) && $review['id'] === $parent_review_id) {
            if (!isset($reviews[$index]['replies'])) {
                $reviews[$index]['replies'] = array();
            }
            
            $new_reply = array(
                'id' => uniqid('reply_'),
                'user_id' => $user_id,
                'comment' => mb_substr($comment, 0, 2000),
                'timestamp' => $timestamp,
                'display_name' => $display_name,
                'likes' => array(),
            );
            
            $reviews[$index]['replies'][] = $new_reply;
            update_post_meta($audio_id, '_nymia_audio_reviews', $reviews);
            
            // Send notification to review author
            $review_author_id = isset($review['user_id']) ? intval($review['user_id']) : 0;
            if ($review_author_id !== $user_id && function_exists('nymia_create_notification')) {
                $actor = get_userdata($user_id);
                $actor_name = $actor ? ($actor->display_name ?: $actor->user_login) : __('Someone', 'nymia');
                $single_audio_page = get_page_by_path('single-audio');
                $audio_link = $single_audio_page ? get_permalink($single_audio_page) : home_url('/single-audio/');
                $audio_link = add_query_arg('audio_id', $audio_id, $audio_link);
                nymia_create_notification(
                    $review_author_id,
                    'review_reply',
                    sprintf(__('%s replied to your review', 'nymia'), $actor_name),
                    $audio_link,
                    $user_id
                );
            }
            
            // Format reply for response
            $avatar = get_template_directory_uri() . '/assets/images/profile.png';
            $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
            $avatar = $custom_avatar ?: get_avatar_url($user_id, array('size' => 64));
            
            wp_send_json_success(array(
                'reply' => array(
                    'id' => $new_reply['id'],
                    'user_id' => $user_id,
                    'display_name' => $display_name,
                    'avatar' => esc_url($avatar),
                    'comment' => $new_reply['comment'],
                    'timestamp' => $timestamp,
                    'time_human' => human_time_diff(strtotime($timestamp), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
                    'like_count' => 0,
                    'is_liked' => false,
                ),
            ));
            return;
        }
    }

    wp_send_json_error(array('message' => __('Review not found.', 'nymia')));
}
add_action('wp_ajax_nymia_submit_audio_review_reply', 'nymia_submit_audio_review_reply');

// ==========================================
// EBOOK REVIEWS (RATINGS + COMMENTS)
// ==========================================
function nymia_get_ebook_reviews($ebook_id) {
    $reviews = get_post_meta($ebook_id, '_nymia_ebook_reviews', true);
    return is_array($reviews) ? $reviews : array();
}

function nymia_get_ebook_owner_id($ebook_id) {
    $owner = get_post_meta($ebook_id, '_nymia_ebook_owner', true);
    if ($owner) {
        return intval($owner);
    }
    $all_ebooks = get_transient('nymia_all_ebooks');
    if ($all_ebooks && is_array($all_ebooks)) {
        foreach ($all_ebooks as $ebook) {
            if (isset($ebook['id']) && (string)$ebook['id'] === (string)$ebook_id) {
                $owner_id = isset($ebook['user_id']) ? intval($ebook['user_id']) : 0;
                if ($owner_id) {
                    update_post_meta($ebook_id, '_nymia_ebook_owner', $owner_id);
                }
                return $owner_id;
            }
        }
    }
    return 0;
}

function nymia_prepare_ebook_reviews_payload($ebook_id) {
    $reviews = nymia_get_ebook_reviews($ebook_id);
    $count = count($reviews);
    $average = 0;
    if ($count > 0) {
        $sum = 0;
        foreach ($reviews as $entry) {
            $sum += isset($entry['rating']) ? intval($entry['rating']) : 0;
        }
        $average = $sum > 0 ? round($sum / $count, 1) : 0;
    }

    $current_user_id = get_current_user_id();
    $formatted = array();
    foreach ($reviews as $entry) {
        $user_id = isset($entry['user_id']) ? intval($entry['user_id']) : 0;
        $display_name = isset($entry['display_name']) ? $entry['display_name'] : '';
        $user_avatar = get_template_directory_uri() . '/assets/images/profile.png';
        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $display_name = $display_name ?: $user->display_name;
                $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
                $user_avatar = $custom_avatar ?: get_avatar_url($user_id, array('size' => 64));
            }
        }

        $timestamp = isset($entry['timestamp']) ? $entry['timestamp'] : current_time('mysql');

        // Get review likes
        $review_likes = isset($entry['likes']) && is_array($entry['likes']) ? $entry['likes'] : array();
        $is_review_liked = $current_user_id > 0 && in_array($current_user_id, $review_likes);
        
        // Get replies
        $replies = isset($entry['replies']) && is_array($entry['replies']) ? $entry['replies'] : array();
        $formatted_replies = array();
        foreach ($replies as $reply) {
            $reply_user_id = isset($reply['user_id']) ? intval($reply['user_id']) : 0;
            $reply_user = $reply_user_id ? get_userdata($reply_user_id) : null;
            $reply_display_name = $reply_user ? ($reply_user->display_name ?: $reply_user->user_login) : __('User', 'nymia');
            
            $reply_avatar = get_template_directory_uri() . '/assets/images/profile.png';
            if ($reply_user_id) {
                $reply_custom_avatar = get_user_meta($reply_user_id, 'custom_avatar', true);
                $reply_avatar = $reply_custom_avatar ?: get_avatar_url($reply_user_id, array('size' => 64));
            }
            
            // Get reply likes
            $reply_likes = isset($reply['likes']) && is_array($reply['likes']) ? $reply['likes'] : array();
            $is_reply_liked = $current_user_id > 0 && in_array($current_user_id, $reply_likes);
            
            $formatted_replies[] = array(
                'id' => isset($reply['id']) ? $reply['id'] : '',
                'user_id' => $reply_user_id,
                'display_name' => $reply_display_name,
                'avatar' => esc_url($reply_avatar),
                'comment' => isset($reply['comment']) ? $reply['comment'] : '',
                'timestamp' => isset($reply['timestamp']) ? $reply['timestamp'] : current_time('mysql'),
                'time_human' => human_time_diff(strtotime(isset($reply['timestamp']) ? $reply['timestamp'] : current_time('mysql')), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
                'like_count' => count($reply_likes),
                'is_liked' => $is_reply_liked,
            );
        }

        $formatted[] = array(
            'id' => isset($entry['id']) ? $entry['id'] : uniqid('review_'),
            'user_id' => $user_id,
            'display_name' => $display_name ?: __('Reader', 'nymia'),
            'avatar' => esc_url($user_avatar),
            'rating' => isset($entry['rating']) ? intval($entry['rating']) : 0,
            'comment' => isset($entry['comment']) ? $entry['comment'] : '',
            'timestamp' => $timestamp,
            'time_human' => human_time_diff(strtotime($timestamp), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
            'like_count' => count($review_likes),
            'is_liked' => $is_review_liked,
            'replies' => $formatted_replies,
        );
    }

    return array(
        'ebook_id' => intval($ebook_id),
        'average' => $average,
        'count' => $count,
        'reviews' => $formatted
    );
}

function nymia_sync_ebook_rating_in_cache($ebook_id, $average, $count = null) {
    $all_ebooks = get_transient('nymia_all_ebooks');
    if ($all_ebooks && is_array($all_ebooks)) {
        foreach ($all_ebooks as $index => $ebook) {
            if (isset($ebook['id']) && (string)$ebook['id'] === (string)$ebook_id) {
                $all_ebooks[$index]['rating'] = $average;
                if ($count !== null) {
                    $all_ebooks[$index]['review_count'] = $count;
                }
                break;
            }
        }
        set_transient('nymia_all_ebooks', $all_ebooks, 30 * DAY_IN_SECONDS);
    }

    $owner_id = nymia_get_ebook_owner_id($ebook_id);
    if ($owner_id) {
        $user_key = 'nymia_user_ebook_' . $owner_id;
        $user_ebooks = get_transient($user_key);
        if ($user_ebooks && is_array($user_ebooks)) {
            foreach ($user_ebooks as $index => $ebook) {
                if (isset($ebook['id']) && (string)$ebook['id'] === (string)$ebook_id) {
                    $user_ebooks[$index]['rating'] = $average;
                    if ($count !== null) {
                        $user_ebooks[$index]['review_count'] = $count;
                    }
                    break;
                }
            }
            set_transient($user_key, $user_ebooks, 30 * DAY_IN_SECONDS);
        }
    }
}

function nymia_submit_ebook_review() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to leave a review.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_ebook_review_nonce', 'nonce');

    $ebook_id = isset($_POST['ebook_id']) ? intval($_POST['ebook_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

    if (!$ebook_id) {
        wp_send_json_error(array('message' => __('Invalid ebook.', 'nymia')));
    }

    if ($rating < 1 || $rating > 5) {
        wp_send_json_error(array('message' => __('Rating must be between 1 and 5 stars.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $reviews = nymia_get_ebook_reviews($ebook_id);
    $timestamp = current_time('mysql');
    $current_user = wp_get_current_user();
    $display_name = $current_user->display_name ?: $current_user->user_login;

    $updated = false;
    foreach ($reviews as $index => $entry) {
        if (isset($entry['user_id']) && intval($entry['user_id']) === $user_id) {
            $reviews[$index]['rating'] = $rating;
            $reviews[$index]['comment'] = $comment;
            $reviews[$index]['timestamp'] = $timestamp;
            $reviews[$index]['display_name'] = $display_name;
            $updated = true;
            break;
        }
    }

    if (!$updated) {
        $reviews[] = array(
            'id' => uniqid('review_'),
            'user_id' => $user_id,
            'display_name' => $display_name,
            'rating' => $rating,
            'comment' => $comment,
            'timestamp' => $timestamp,
            'likes' => array(),
            'replies' => array()
        );
    } else {
        // Ensure existing review has ID and arrays
        foreach ($reviews as $index => $entry) {
            if (isset($entry['user_id']) && intval($entry['user_id']) === $user_id) {
                if (!isset($reviews[$index]['id'])) {
                    $reviews[$index]['id'] = uniqid('review_');
                }
                if (!isset($reviews[$index]['likes'])) {
                    $reviews[$index]['likes'] = array();
                }
                if (!isset($reviews[$index]['replies'])) {
                    $reviews[$index]['replies'] = array();
                }
                break;
            }
        }
    }

    update_post_meta($ebook_id, '_nymia_ebook_reviews', $reviews);

    $sum = 0;
    foreach ($reviews as $entry) {
        $sum += isset($entry['rating']) ? intval($entry['rating']) : 0;
    }
    $count = count($reviews);
    $average = $count > 0 ? round($sum / $count, 1) : 0;

    update_post_meta($ebook_id, '_nymia_ebook_rating_sum', $sum);
    update_post_meta($ebook_id, '_nymia_ebook_rating_count', $count);

    nymia_sync_ebook_rating_in_cache($ebook_id, $average, $count);

    wp_send_json_success(nymia_prepare_ebook_reviews_payload($ebook_id));
}
add_action('wp_ajax_nymia_submit_ebook_review', 'nymia_submit_ebook_review');

function nymia_fetch_ebook_reviews() {
    check_ajax_referer('nymia_ebook_review_nonce', 'nonce');
    $ebook_id = isset($_POST['ebook_id']) ? intval($_POST['ebook_id']) : 0;
    if (!$ebook_id) {
        wp_send_json_error(array('message' => __('Invalid ebook.', 'nymia')));
    }
    wp_send_json_success(nymia_prepare_ebook_reviews_payload($ebook_id));
}
add_action('wp_ajax_nymia_fetch_ebook_reviews', 'nymia_fetch_ebook_reviews');
add_action('wp_ajax_nopriv_nymia_fetch_ebook_reviews', 'nymia_fetch_ebook_reviews');

// ==========================================
// EBOOK REVIEW LIKES & REPLIES
// ==========================================
function nymia_toggle_ebook_review_like() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to like reviews.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_ebook_review_nonce', 'nonce');

    $ebook_id = isset($_POST['ebook_id']) ? intval($_POST['ebook_id']) : 0;
    $review_id = isset($_POST['review_id']) ? sanitize_text_field(wp_unslash($_POST['review_id'])) : '';
    $is_reply = isset($_POST['is_reply']) ? (bool)$_POST['is_reply'] : false;
    $parent_review_id = isset($_POST['parent_review_id']) ? sanitize_text_field(wp_unslash($_POST['parent_review_id'])) : '';

    if (!$ebook_id || !$review_id) {
        wp_send_json_error(array('message' => __('Invalid request.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $reviews = nymia_get_ebook_reviews($ebook_id);

    foreach ($reviews as $index => $review) {
        if ($is_reply && isset($review['id']) && $review['id'] === $parent_review_id) {
            // Handle reply like
            if (isset($review['replies']) && is_array($review['replies'])) {
                foreach ($review['replies'] as $reply_index => $reply) {
                    if (isset($reply['id']) && $reply['id'] === $review_id) {
                        $reply_likes = isset($reply['likes']) && is_array($reply['likes']) ? $reply['likes'] : array();
                        $is_liked = in_array($user_id, $reply_likes);
                        
                        if ($is_liked) {
                            $reply_likes = array_values(array_filter($reply_likes, function($id) use ($user_id) {
                                return intval($id) !== $user_id;
                            }));
                        } else {
                            if (!in_array($user_id, $reply_likes)) {
                                $reply_likes[] = $user_id;
                            }
                        }
                        
                        $reviews[$index]['replies'][$reply_index]['likes'] = $reply_likes;
                        update_post_meta($ebook_id, '_nymia_ebook_reviews', $reviews);
                        
                        wp_send_json_success(array(
                            'like_count' => count($reply_likes),
                            'is_liked' => !$is_liked,
                        ));
                        return;
                    }
                }
            }
        } elseif (!$is_reply && isset($review['id']) && $review['id'] === $review_id) {
            // Handle review like
            $review_likes = isset($review['likes']) && is_array($review['likes']) ? $review['likes'] : array();
            $is_liked = in_array($user_id, $review_likes);
            
            if ($is_liked) {
                $review_likes = array_values(array_filter($review_likes, function($id) use ($user_id) {
                    return intval($id) !== $user_id;
                }));
            } else {
                if (!in_array($user_id, $review_likes)) {
                    $review_likes[] = $user_id;
                }
            }
            
            $reviews[$index]['likes'] = $review_likes;
            update_post_meta($ebook_id, '_nymia_ebook_reviews', $reviews);
            
            wp_send_json_success(array(
                'like_count' => count($review_likes),
                'is_liked' => !$is_liked,
            ));
            return;
        }
    }

    wp_send_json_error(array('message' => __('Review not found.', 'nymia')));
}
add_action('wp_ajax_nymia_toggle_ebook_review_like', 'nymia_toggle_ebook_review_like');

function nymia_submit_ebook_review_reply() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to reply.', 'nymia')), 401);
    }

    check_ajax_referer('nymia_ebook_review_nonce', 'nonce');

    $ebook_id = isset($_POST['ebook_id']) ? intval($_POST['ebook_id']) : 0;
    $parent_review_id = isset($_POST['parent_review_id']) ? sanitize_text_field(wp_unslash($_POST['parent_review_id'])) : '';
    $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

    if (!$ebook_id || !$parent_review_id) {
        wp_send_json_error(array('message' => __('Invalid request.', 'nymia')));
    }

    if (trim($comment) === '') {
        wp_send_json_error(array('message' => __('Please enter a reply.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $reviews = nymia_get_ebook_reviews($ebook_id);
    $timestamp = current_time('mysql');
    $current_user = wp_get_current_user();
    $display_name = $current_user->display_name ?: $current_user->user_login;

    foreach ($reviews as $index => $review) {
        if (isset($review['id']) && $review['id'] === $parent_review_id) {
            if (!isset($reviews[$index]['replies'])) {
                $reviews[$index]['replies'] = array();
            }
            
            $new_reply = array(
                'id' => uniqid('reply_'),
                'user_id' => $user_id,
                'comment' => mb_substr($comment, 0, 2000),
                'timestamp' => $timestamp,
                'display_name' => $display_name,
                'likes' => array(),
            );
            
            $reviews[$index]['replies'][] = $new_reply;
            update_post_meta($ebook_id, '_nymia_ebook_reviews', $reviews);
            
            // Send notification to review author
            $review_author_id = isset($review['user_id']) ? intval($review['user_id']) : 0;
            if ($review_author_id !== $user_id && function_exists('nymia_create_notification')) {
                $actor = get_userdata($user_id);
                $actor_name = $actor ? ($actor->display_name ?: $actor->user_login) : __('Someone', 'nymia');
                $single_ebook_page = get_page_by_path('single-ebook');
                $ebook_link = $single_ebook_page ? get_permalink($single_ebook_page) : home_url('/single-ebook/');
                $ebook_link = add_query_arg('ebook_id', $ebook_id, $ebook_link);
                nymia_create_notification(
                    $review_author_id,
                    'review_reply',
                    sprintf(__('%s replied to your review', 'nymia'), $actor_name),
                    $ebook_link,
                    $user_id
                );
            }
            
            // Format reply for response
            $avatar = get_template_directory_uri() . '/assets/images/profile.png';
            $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
            $avatar = $custom_avatar ?: get_avatar_url($user_id, array('size' => 64));
            
            wp_send_json_success(array(
                'reply' => array(
                    'id' => $new_reply['id'],
                    'user_id' => $user_id,
                    'display_name' => $display_name,
                    'avatar' => esc_url($avatar),
                    'comment' => $new_reply['comment'],
                    'timestamp' => $timestamp,
                    'time_human' => human_time_diff(strtotime($timestamp), current_time('timestamp')) . ' ' . __('ago', 'nymia'),
                    'like_count' => 0,
                    'is_liked' => false,
                ),
            ));
            return;
        }
    }

    wp_send_json_error(array('message' => __('Review not found.', 'nymia')));
}
add_action('wp_ajax_nymia_submit_ebook_review_reply', 'nymia_submit_ebook_review_reply');

/**
 * GET ALL CREATORS WITH AUDIO
 * ---------------------------
 * Retrieves all creators who have uploaded audio files, grouped by creator
 * @return array Creators with their audio files
 */
function nymia_get_all_creators_with_audio() {
    $creators_data = array();
    
    // Get all registered users
    $users = get_users();
    
    foreach ($users as $user) {
        $user_id = $user->ID;
        $audio_posts = get_transient('nymia_user_audio_' . $user_id);
        
        // Only include creators who have uploaded audio
        if ($audio_posts && is_array($audio_posts) && !empty($audio_posts)) {
            // Get user avatar
            $avatar_url = get_avatar_url($user_id, array('size' => 150));
            $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
            if ($custom_avatar) {
                $avatar_url = $custom_avatar;
            }
            
            // Determine primary cover image (first real cover, fallback to avatar or placeholder)
            $primary_cover_image = '';
            $placeholder_cover = get_template_directory_uri() . '/assets/images/audio-placeholder.jpg';
            if (!empty($audio_posts[0]['cover_image'])) {
                $primary_cover_image = nymia_normalize_media_url($audio_posts[0]['cover_image']);
            }
            if (empty($primary_cover_image)) {
                $first_meta_cover = !empty($audio_posts[0]['id']) ? get_post_meta($audio_posts[0]['id'], '_nymia_audio_cover_image', true) : '';
                $primary_cover_image = nymia_normalize_media_url($first_meta_cover);
            }
            if (empty($primary_cover_image)) {
                $primary_cover_image = nymia_normalize_media_url(get_user_meta($user_id, 'cover_image', true));
            }
            if (empty($primary_cover_image)) {
                $primary_cover_image = $avatar_url ?: $placeholder_cover;
            }
            
            // Calculate total views, average rating, and review count
            $total_views = 0;
            $total_rating = 0;
            $rating_count = 0;
            $total_review_count = 0;
            foreach ($audio_posts as $audio) {
                $total_views += isset($audio['views']) ? intval($audio['views']) : 0;
                if (isset($audio['rating']) && $audio['rating'] > 0) {
                    $total_rating += floatval($audio['rating']);
                    $rating_count++;
                }
                // Get review count from audio post or meta
                $review_count = isset($audio['review_count']) ? intval($audio['review_count']) : 0;
                if (!$review_count && !empty($audio['id'])) {
                    $review_count = intval(get_post_meta($audio['id'], '_nymia_audio_rating_count', true));
                }
                $total_review_count += $review_count;
            }
            
            $avg_rating = $rating_count > 0 ? round($total_rating / $rating_count, 1) : 0;
            
            // Format views
            $views_formatted = $total_views >= 1000 ? round($total_views / 1000, 1) . 'k' : (string)$total_views;
            
            // Get category from first audio post or use default
            $category = !empty($audio_posts[0]['category']) ? $audio_posts[0]['category'] : 'General';
            
            // Format audio files for display
            $formatted_audio_files = array();
            foreach ($audio_posts as $audio) {
                // Filter out Secret Room content - only show normal category content
                $audio_id = isset($audio['id']) ? $audio['id'] : 0;
                
                // Only filter if we have a valid function and valid ID
                if (function_exists('nymia_is_content_visible_in_normal')) {
                    if (!nymia_is_content_visible_in_normal($audio_id, $audio)) {
                        continue; // Skip Secret Room content
                    }
                }
                
                $cover_image = isset($audio['cover_image']) ? $audio['cover_image'] : '';
                $cover_image = nymia_normalize_media_url($cover_image);
                if (empty($cover_image) && !empty($audio['id'])) {
                    $meta_cover = get_post_meta($audio['id'], '_nymia_audio_cover_image', true);
                    $cover_image = nymia_normalize_media_url($meta_cover);
                }
                if (empty($cover_image)) {
                    $cover_image = $placeholder_cover;
                }
                if (empty($primary_cover_image) || $primary_cover_image === $avatar_url || $primary_cover_image === $placeholder_cover) {
                    $primary_cover_image = $cover_image;
                }
                $review_count = isset($audio['review_count']) ? intval($audio['review_count']) : 0;
                if (!$review_count && !empty($audio['id'])) {
                    $review_count = intval(get_post_meta($audio['id'], '_nymia_audio_rating_count', true));
                }
                $formatted_audio_files[] = array(
                    'title' => $audio['title'],
                    'duration' => isset($audio['duration']) ? $audio['duration'] : '0:00',
                    'rating' => isset($audio['rating']) ? floatval($audio['rating']) : 0,
                    'url' => isset($audio['url']) ? $audio['url'] : '',
                    'cover_image' => $cover_image,
                    'paid_access' => isset($audio['paid_access']) ? $audio['paid_access'] : 'no',
                    'price' => isset($audio['price']) ? floatval($audio['price']) : 0,
                    'review_count' => $review_count,
                    'user_id' => $user_id
                );
            }
            
            $creators_data[] = array(
                'user_id' => $user_id,
                'name' => $user->display_name ? $user->display_name : $user->user_login,
                'avatar' => $avatar_url,
                'image' => $primary_cover_image ?: $placeholder_cover,
                'cover_image' => $primary_cover_image ?: $placeholder_cover,
                'views' => $views_formatted,
                'rating' => $avg_rating,
                'review_count' => $total_review_count,
                'category' => $category,
                'subcategory' => '', // Can be added later if needed
                'audio_files' => $formatted_audio_files
            );
        }
    }
    
    // Sort by total views or rating (most popular first)
    usort($creators_data, function($a, $b) {
        // Sort by rating first, then by views
        if ($b['rating'] != $a['rating']) {
            return $b['rating'] <=> $a['rating'];
        }
        return strcmp($b['views'], $a['views']);
    });
    
    return $creators_data;
} // END: nymia_get_all_creators_with_audio()

/**
 * LOCALIZE SCRIPT FOR AJAX
 * ------------------------
 * Makes AJAX URL and nonce available to JavaScript
 * Hooks into: wp_enqueue_scripts
 */
function nymia_localize_scripts() {
    // DEPRECATED: Localization is handled in nymia_scripts() with followNonce
    // This duplicate localization is being overridden
    wp_localize_script('nymia-script', 'nymiaAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('nymia_audio_upload'),
        'followNonce' => wp_create_nonce('nymia_follow_action')
    ));
} // END: nymia_localize_scripts()
add_action('wp_enqueue_scripts', 'nymia_localize_scripts');

/**
 * ALLOW AUDIO FILE UPLOADS
 * -------------------------
 * Adds audio MIME types to WordPress allowed upload types
 * Hooks into: upload_mimes filter
 */
function nymia_allow_audio_uploads($mimes) {
    $mimes['wav'] = 'audio/wav';
    $mimes['mp3'] = 'audio/mpeg';
    $mimes['ogg'] = 'audio/ogg';
    $mimes['m4a'] = 'audio/m4a';
    $mimes['flac'] = 'audio/flac';
    $mimes['mp4'] = 'audio/mp4';
    $mimes['webm'] = 'audio/webm';
    
    // Add ebook MIME types
    $mimes['pdf'] = 'application/pdf';
    $mimes['epub'] = 'application/epub+zip';
    $mimes['mobi'] = 'application/x-mobipocket-ebook';
    $mimes['txt'] = 'text/plain';
    
    return $mimes;
} // END: nymia_allow_audio_uploads()
add_filter('upload_mimes', 'nymia_allow_audio_uploads');

/**
 * ADD AUDIO MIME TYPES
 * --------------------
 * Registers audio MIME types with WordPress
 * Hooks into: get_allowed_mime_types filter
 */
function nymia_add_audio_mime_types($types) {
    $types[] = 'audio/wav';
    $types[] = 'audio/x-wav';
    $types[] = 'audio/wave';
    $types[] = 'audio/mpeg';
    $types[] = 'audio/mp3';
    $types[] = 'audio/ogg';
    $types[] = 'audio/m4a';
    $types[] = 'audio/flac';
    
    // Add ebook MIME types
    $types[] = 'application/pdf';
    $types[] = 'application/epub+zip';
    $types[] = 'application/x-mobipocket-ebook';
    $types[] = 'text/plain';
    
    return $types;
} // END: nymia_add_audio_mime_types()
add_filter('get_allowed_mime_types', 'nymia_add_audio_mime_types');

// ==========================================
// EBOOK SYSTEM
// ==========================================
/**
 * INCLUDE EBOOK FUNCTIONS
 * -----------------------
 * Loads ebook functionality for all pages
 */
require_once get_template_directory() . '/create-ebook/includes/ebook-functions.php';
// Archive module additions (unlock/purchase helpers)
require_once get_template_directory() . '/ebook-archive/includes/ebook-archive-functions.php';

// ==========================================
// AUTHENTICATION HANDLERS
// ==========================================
/**
 * CUSTOM LOGIN HANDLER
 * --------------------
 * Processes login form submissions and authenticates users
 * Redirects to home on success, shows error on failure
 * Hooks into: init
 */
function nymia_custom_login_handler() {
    // CHECK: If login form was submitted
    if (isset($_POST['log']) && isset($_POST['pwd'])) {
        // GET: Form data and sanitize
        $username = sanitize_user($_POST['log']);
        $password = $_POST['pwd'];
        $remember = isset($_POST['rememberme']) ? true : false;
        
        // ATTEMPT: WordPress authentication
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, false);
        
        if (!is_wp_error($user)) {
            // CHECK: Email verification status
            $email_verification_enabled = get_option('nymia_enable_email_verification', '1');
            
            if ($email_verification_enabled == '1') {
                $email_verified = get_user_meta($user->ID, 'email_verified', true);
                
                if ($email_verified != '1') {
                    // User is not verified, redirect to verification page
                    wp_redirect(home_url('/verify-email/?user_id=' . $user->ID . '&error=' . urlencode('Please verify your email address to continue.')));
                    exit;
                }
            }
            
            // SUCCESS: Set authentication cookie
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, $remember);
            
            // REDIRECT: To home page
            wp_redirect(home_url('/'));
            exit;
        } else {
            // FAILED: Redirect back with error
            wp_redirect(home_url('/?login=failed'));
            exit;
        }
    }
} // END: nymia_custom_login_handler()
add_action('init', 'nymia_custom_login_handler');

/**
 * CUSTOM FORGOT PASSWORD AJAX HANDLER
 * ------------------------------------
 * Handles password reset requests via AJAX
 * Sends password reset link to user's email
 */
function nymia_forgot_password_handler() {
    // Verify nonce
    if (!isset($_POST['nymia_forgot_password_nonce']) || !wp_verify_nonce($_POST['nymia_forgot_password_nonce'], 'nymia_forgot_password')) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', 'nymia')));
        return;
    }
    
    // Get email
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    
    if (empty($user_email)) {
        wp_send_json_error(array('message' => __('Please enter your email address.', 'nymia')));
        return;
    }
    
    // Check if user exists
    $user = get_user_by('email', $user_email);
    if (!$user) {
        // Don't reveal if email exists for security
        wp_send_json_success(array('message' => __('If that email address exists in our system, we have sent a password reset link to it.', 'nymia')));
        return;
    }
    
    // Generate reset key
    $key = get_password_reset_key($user);
    
    if (is_wp_error($key)) {
        wp_send_json_error(array('message' => __('An error occurred while generating the reset key. Please try again.', 'nymia')));
        return;
    }
    
    // Create reset URL
    $reset_url = add_query_arg(array(
        'action' => 'rp',
        'key' => $key,
        'login' => rawurlencode($user->user_login)
    ), home_url('/reset-password/'));
    
    // Send email
    $site_name = get_bloginfo('name');
    $subject = sprintf(__('[%s] Password Reset', 'nymia'), $site_name);
    
    $message = sprintf(__('Hello %s,', 'nymia'), $user->display_name ?: $user->user_login) . "\n\n";
    $message .= __('Someone has requested a password reset for your account.', 'nymia') . "\n\n";
    $message .= __('If you made this request, click the link below to reset your password:', 'nymia') . "\n\n";
    $message .= $reset_url . "\n\n";
    $message .= __('If you did not request a password reset, please ignore this email.', 'nymia') . "\n\n";
    $message .= sprintf(__('This link will expire in 24 hours.', 'nymia')) . "\n\n";
    $message .= sprintf(__('Best regards,\n%s', 'nymia'), $site_name);
    
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site_name . ' <' . get_option('admin_email') . '>'
    );
    
    $sent = wp_mail($user->user_email, $subject, $message, $headers);
    
    if ($sent) {
        wp_send_json_success(array('message' => __('Password reset link has been sent to your email address.', 'nymia')));
    } else {
        wp_send_json_error(array('message' => __('Failed to send email. Please try again later.', 'nymia')));
    }
}
add_action('wp_ajax_nymia_forgot_password', 'nymia_forgot_password_handler');
add_action('wp_ajax_nopriv_nymia_forgot_password', 'nymia_forgot_password_handler');

/**
 * CUSTOM PASSWORD RESET HANDLER
 * -----------------------------
 * Handles password reset form submission
 */
function nymia_reset_password_handler() {
    // Check if this is a password reset request
    if (isset($_POST['nymia_reset_password']) && isset($_POST['pass1']) && isset($_POST['pass2'])) {
        // Verify nonce
        if (!isset($_POST['nymia_reset_password_nonce']) || !wp_verify_nonce($_POST['nymia_reset_password_nonce'], 'nymia_reset_password')) {
            wp_redirect(home_url('/reset-password/?error=security'));
            exit;
        }
        
        $key = isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '';
        $login = isset($_POST['login']) ? sanitize_user($_POST['login']) : '';
        $pass1 = $_POST['pass1'];
        $pass2 = $_POST['pass2'];
        
        if (empty($key) || empty($login)) {
            wp_redirect(home_url('/reset-password/?error=invalid'));
            exit;
        }
        
        // Verify reset key
        $user = check_password_reset_key($key, $login);
        
        if (is_wp_error($user)) {
            wp_redirect(home_url('/reset-password/?error=expired&key=' . urlencode($key) . '&login=' . urlencode($login)));
            exit;
        }
        
        // Check if passwords match
        if ($pass1 !== $pass2) {
            wp_redirect(home_url('/reset-password/?error=mismatch&key=' . urlencode($key) . '&login=' . urlencode($login)));
            exit;
        }
        
        // Validate password strength
        if (strlen($pass1) < 8) {
            wp_redirect(home_url('/reset-password/?error=weak&key=' . urlencode($key) . '&login=' . urlencode($login)));
            exit;
        }
        
        // Reset password
        reset_password($user, $pass1);
        
        // Redirect to login with success message
        wp_redirect(home_url('/?password_reset=success'));
        exit;
    }
}
add_action('init', 'nymia_reset_password_handler');

/**
 * GENERATE VERIFICATION CODE
 * ---------------------------
 * Creates a random 6-digit verification code for email verification
 * @return string 6-digit verification code
 */
function nymia_generate_verification_code() {
    return sprintf('%06d', mt_rand(0, 999999));
} // END: nymia_generate_verification_code()

/**
 * SEND VERIFICATION EMAIL
 * ------------------------
 * Sends verification code to user's email address
 * @param string $email User's email address
 * @param string $verification_code 6-digit verification code
 * @return bool True on success, false on failure
 */
function nymia_send_verification_email($email, $verification_code) {
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    // Get template from settings or use default
    $subject_template = get_option('nymia_otp_email_subject', 'Verify Your Email - {site_name}');
    $message_template = get_option('nymia_otp_email_message', "Hello!\n\nThank you for signing up with {site_name}!\n\nYour verification code is: {verification_code}\n\nPlease enter this code on the verification page to complete your registration.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nThe {site_name} Team");
    
    // Replace variables
    $subject = str_replace(
        array('{site_name}', '{verification_code}'),
        array($site_name, $verification_code),
        $subject_template
    );
    
    $message = str_replace(
        array('{site_name}', '{verification_code}', '{site_url}'),
        array($site_name, $verification_code, $site_url),
        $message_template
    );
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    return wp_mail($email, $subject, $message, $headers);
} // END: nymia_send_verification_email()

/**
 * SEND ADMIN NEW USER NOTIFICATION EMAIL
 * ---------------------------------------
 * Sends notification email to admin when a new user registers
 * @param int $user_id The newly created user ID
 * @param string $username User's username
 * @param string $email User's email address
 * @param string $first_name User's first name (optional)
 * @param string $last_name User's last name (optional)
 * @return bool True on success, false on failure
 */
function nymia_send_admin_new_user_notification($user_id, $username, $email, $first_name = '', $last_name = '') {
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    // Build user display name
    $display_name = trim($first_name . ' ' . $last_name);
    if (empty($display_name)) {
        $display_name = $username;
    }
    
    $subject = sprintf(__('[%s] New User Registration', 'nymia'), $site_name);
    
    $message = __("A new customer has signed up on Nymia.\n\n", 'nymia');
    $message .= sprintf(__("User Details:\n", 'nymia'));
    $message .= sprintf(__("Username: %s\n", 'nymia'), $username);
    $message .= sprintf(__("Email: %s\n", 'nymia'), $email);
    if (!empty($first_name) || !empty($last_name)) {
        $message .= sprintf(__("Name: %s\n", 'nymia'), $display_name);
    }
    $message .= sprintf(__("User ID: %d\n", 'nymia'), $user_id);
    $message .= sprintf(__("Registration Date: %s\n\n", 'nymia'), date_i18n(get_option('date_format') . ' ' . get_option('time_format')));
    $message .= sprintf(__("You can view this user in the WordPress admin:\n", 'nymia'));
    $message .= admin_url('user-edit.php?user_id=' . $user_id) . "\n\n";
    $message .= sprintf(__("Best regards,\n%s", 'nymia'), $site_name);
    
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site_name . ' <' . $admin_email . '>'
    );
    
    return wp_mail($admin_email, $subject, $message, $headers);
} // END: nymia_send_admin_new_user_notification()

/**
 * SEND ADMIN NEW CREATOR NOTIFICATION EMAIL
 * ------------------------------------------
 * Sends notification email to admin when a user becomes a creator
 * @param int $user_id The user ID who became a creator
 * @param string $username User's username
 * @param string $email User's email address
 * @param array $kyc_data KYC data submitted by the creator (optional)
 * @return bool True on success, false on failure
 */
function nymia_send_admin_new_creator_notification($user_id, $username, $email, $kyc_data = array()) {
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    // Build user display name
    $display_name = $user->display_name ?: $username;
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    if (!empty($first_name) || !empty($last_name)) {
        $display_name = trim($first_name . ' ' . $last_name);
    }
    
    $subject = sprintf(__('[%s] New Creator Registration', 'nymia'), $site_name);
    
    $message = __("A new creator has registered on Nymia,\n\n", 'nymia');
    $message .= sprintf(__("Creator Details:\n", 'nymia'));
    $message .= sprintf(__("Username: %s\n", 'nymia'), $username);
    $message .= sprintf(__("Email: %s\n", 'nymia'), $email);
    $message .= sprintf(__("Name: %s\n", 'nymia'), $display_name);
    $message .= sprintf(__("User ID: %d\n", 'nymia'), $user_id);
    
    // Add KYC information if available
    if (!empty($kyc_data)) {
        $message .= "\n" . __("KYC Information:\n", 'nymia');
        if (!empty($kyc_data['first_name'])) {
            $message .= sprintf(__("First Name: %s\n", 'nymia'), $kyc_data['first_name']);
        }
        if (!empty($kyc_data['last_name'])) {
            $message .= sprintf(__("Last Name: %s\n", 'nymia'), $kyc_data['last_name']);
        }
        if (!empty($kyc_data['phone'])) {
            $message .= sprintf(__("Phone: %s\n", 'nymia'), $kyc_data['phone']);
        }
        if (!empty($kyc_data['id_type'])) {
            $message .= sprintf(__("ID Type: %s\n", 'nymia'), $kyc_data['id_type']);
        }
        if (!empty($kyc_data['country'])) {
            $message .= sprintf(__("Country: %s\n", 'nymia'), $kyc_data['country']);
        }
        if (!empty($kyc_data['city'])) {
            $message .= sprintf(__("City: %s\n", 'nymia'), $kyc_data['city']);
        }
    }
    
    $message .= sprintf(__("\nApplication Date: %s\n\n", 'nymia'), date_i18n(get_option('date_format') . ' ' . get_option('time_format')));
    $message .= sprintf(__("You can review this creator application in the WordPress admin:\n", 'nymia'));
    $message .= admin_url('admin.php?page=nymia-theme-settings') . "\n";
    $message .= sprintf(__("Or view the user profile:\n", 'nymia'));
    $message .= admin_url('user-edit.php?user_id=' . $user_id) . "\n\n";
    $message .= sprintf(__("Best regards,\n%s", 'nymia'), $site_name);
    
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site_name . ' <' . $admin_email . '>'
    );
    
    return wp_mail($admin_email, $subject, $message, $headers);
} // END: nymia_send_admin_new_creator_notification()

/**
 * SEND NEW USER WELCOME EMAIL
 * ----------------------------
 * Sends welcome email to new users after successful registration
 * @param int $user_id The newly created user ID
 * @param string $username User's username
 * @param string $email User's email address
 * @param string $first_name User's first name (optional)
 * @param string $last_name User's last name (optional)
 * @return bool True on success, false on failure
 */
function nymia_send_new_user_welcome_email($user_id, $username, $email, $first_name = '', $last_name = '') {
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    // Build user display name
    $display_name = trim($first_name . ' ' . $last_name);
    if (empty($display_name)) {
        $display_name = $username;
    }
    
    // Get template from settings or use default
    $subject_template = get_option('nymia_new_user_email_subject', 'Welcome to {site_name}!');
    $message_template = get_option('nymia_new_user_email_message', "Hello {display_name}!\n\nWelcome to {site_name}! We're excited to have you join our community.\n\nYour account has been successfully created:\nUsername: {username}\nEmail: {email}\n\nYou can now start exploring all the features we have to offer.\n\nIf you have any questions, feel free to reach out to our support team.\n\nBest regards,\nThe {site_name} Team");
    
    // Replace variables
    $subject = str_replace(
        array('{site_name}', '{display_name}', '{username}'),
        array($site_name, $display_name, $username),
        $subject_template
    );
    
    $message = str_replace(
        array('{site_name}', '{display_name}', '{username}', '{email}', '{site_url}'),
        array($site_name, $display_name, $username, $email, $site_url),
        $message_template
    );
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    return wp_mail($email, $subject, $message, $headers);
} // END: nymia_send_new_user_welcome_email()

/**
 * SEND NEW CREATOR WELCOME EMAIL
 * -------------------------------
 * Sends welcome email to users when they become creators
 * @param int $user_id The user ID who became a creator
 * @param string $username User's username
 * @param string $email User's email address
 * @return bool True on success, false on failure
 */
function nymia_send_new_creator_welcome_email($user_id, $username, $email) {
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    // Build user display name
    $display_name = $user->display_name ?: $username;
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    if (!empty($first_name) || !empty($last_name)) {
        $display_name = trim($first_name . ' ' . $last_name);
    }
    
    // Get template from settings or use default
    $subject_template = get_option('nymia_new_creator_email_subject', 'Welcome Creator - {site_name}');
    $message_template = get_option('nymia_new_creator_email_message', "Hello {display_name}!\n\nCongratulations! Your creator account has been successfully created on {site_name}.\n\nAs a creator, you now have access to:\n- Upload and share your content\n- Connect with your audience\n- Earn from your creations\n\nYour account details:\nUsername: {username}\nEmail: {email}\n\nWe're reviewing your application and will notify you once it's approved.\n\nIf you have any questions, feel free to reach out to our support team.\n\nBest regards,\nThe {site_name} Team");
    
    // Replace variables
    $subject = str_replace(
        array('{site_name}', '{display_name}', '{username}'),
        array($site_name, $display_name, $username),
        $subject_template
    );
    
    $message = str_replace(
        array('{site_name}', '{display_name}', '{username}', '{email}', '{site_url}'),
        array($site_name, $display_name, $username, $email, $site_url),
        $message_template
    );
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    return wp_mail($email, $subject, $message, $headers);
} // END: nymia_send_new_creator_welcome_email()

/**
 * USER REGISTRATION HANDLER (With Email Verification)
 * -----------------------------------------------------
 * Processes new user registrations with email verification
 * Validates: passwords match, password length, username length, email validity
 * Sends verification code via email
 * Stores verification data temporarily for verification step
 * Hooks into: admin_post_nymia_user_register
 */
function nymia_user_register_handler() {
    // SECURITY: Verify nonce
    if (!isset($_POST['nymia_register_nonce']) || !wp_verify_nonce($_POST['nymia_register_nonce'], 'nymia_register')) {
        wp_redirect(home_url('/?registration=error'));
        exit;
    }
    
    // GET: Form data and sanitize
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $date_of_birth = isset($_POST['date_of_birth']) ? sanitize_text_field($_POST['date_of_birth']) : '';
    // Languages selection (optional multi-select)
    $languages = array();
    if (isset($_POST['languages'])) {
        $lang_input = $_POST['languages'];
        if (is_array($lang_input)) {
            $languages = array_map('sanitize_text_field', $lang_input);
        } else {
            $languages = array(sanitize_text_field($lang_input));
        }
    }
    
    // VALIDATE: Date of birth is required
    if (empty($date_of_birth)) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Please enter your date of birth!')));
        exit;
    }
    
    // VALIDATE: User must be 18+ years old
    $birth_date = new DateTime($date_of_birth);
    $today = new DateTime();
    $age = $today->diff($birth_date)->y;
    
    if ($age < 18) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('You must be at least 18 years old to register!')));
        exit;
    }
    
    // VALIDATE: Passwords must match
    if ($password !== $confirm_password) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Passwords do not match!')));
        exit;
    }
    
    // VALIDATE: Password must be at least 6 characters
    if (strlen($password) < 6) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Password must be at least 6 characters long!')));
        exit;
    }
    
    // VALIDATE: Username must be at least 4 characters
    if (empty($username) || strlen($username) < 4) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Username must be at least 4 characters long!')));
        exit;
    }
    
    // VALIDATE: Email must be valid
    if (!is_email($email)) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Please enter a valid email address!')));
        exit;
    }
    
    // VALIDATE: Account type must be provided
    $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : '';
    if (empty($account_type) || !in_array($account_type, array('user', 'creator'))) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Please select an account type!')));
        exit;
    }
    
    // VALIDATE: Check if username already exists
    if (username_exists($username)) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('User already exists. Please choose a different username.')));
        exit;
    }
    
    // VALIDATE: Check if email already exists
    if (email_exists($email)) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Email already exists. Please use a different email or login instead.')));
        exit;
    }
    
    // CREATE: WordPress user immediately
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        $error_message = $user_id->get_error_message();
        wp_redirect(home_url('/?registration=error&error=' . urlencode($error_message)));
        exit;
    }
    
    // ASSIGN ROLE: Default to subscriber on signup
    if (!is_wp_error($user_id)) {
        $user_obj = new WP_User($user_id);
        // Ensure a single role
        $user_obj->set_role('subscriber');
        
        // Save first name and last name
        if (!empty($first_name)) {
            update_user_meta($user_id, 'first_name', $first_name);
            wp_update_user(array('ID' => $user_id, 'first_name' => $first_name));
        }
        
        if (!empty($last_name)) {
            update_user_meta($user_id, 'last_name', $last_name);
            wp_update_user(array('ID' => $user_id, 'last_name' => $last_name));
        }
        
        // Set display name to first name + last name if both are provided
        if (!empty($first_name) && !empty($last_name)) {
            $display_name = trim($first_name . ' ' . $last_name);
            wp_update_user(array('ID' => $user_id, 'display_name' => $display_name));
        } elseif (!empty($first_name)) {
            wp_update_user(array('ID' => $user_id, 'display_name' => $first_name));
        } elseif (!empty($last_name)) {
            wp_update_user(array('ID' => $user_id, 'display_name' => $last_name));
        }
        
        // Save preferred languages
        if (!empty($languages)) {
            update_user_meta($user_id, 'preferred_languages', $languages);
        }
        
        // Save address fields
        if (isset($_POST['street'])) {
            $street = sanitize_text_field($_POST['street']);
            if (!empty($street)) {
                update_user_meta($user_id, 'street', $street);
            }
        }
        
        if (isset($_POST['country'])) {
            $country = sanitize_text_field($_POST['country']);
            if (!empty($country)) {
                update_user_meta($user_id, 'country', $country);
            }
        }
        
        if (isset($_POST['city'])) {
            $city = sanitize_text_field($_POST['city']);
            if (!empty($city)) {
                update_user_meta($user_id, 'city', $city);
            }
        }
        
        if (isset($_POST['postal_code'])) {
            $postal_code = sanitize_text_field($_POST['postal_code']);
            if (!empty($postal_code)) {
                update_user_meta($user_id, 'postal_code', $postal_code);
            }
        }
        
        // Save date of birth
        if (!empty($date_of_birth)) {
            update_user_meta($user_id, 'date_of_birth', $date_of_birth);
            // Also save age for quick reference
            update_user_meta($user_id, 'age', $age);
        }
        
        // Save account type (user or creator)
        $account_type = isset($_POST['account_type']) ? sanitize_text_field($_POST['account_type']) : 'user';
        if (in_array($account_type, array('user', 'creator'))) {
            update_user_meta($user_id, 'account_type', $account_type);
        } else {
            // Default to user if invalid value
            update_user_meta($user_id, 'account_type', 'user');
        }
        
        // Save KYC data if creator account type
        if ($account_type === 'creator') {
            $kyc_id_type = isset($_POST['kyc_id_type']) ? sanitize_text_field($_POST['kyc_id_type']) : '';
            $kyc_id_number = isset($_POST['kyc_id_number']) ? sanitize_text_field($_POST['kyc_id_number']) : '';
            
            // Validate KYC fields
            if (empty($kyc_id_type) || empty($kyc_id_number)) {
                // Delete user if KYC is missing for creator
                require_once(ABSPATH . 'wp-admin/includes/user.php');
                wp_delete_user($user_id);
                wp_redirect(home_url('/?registration=error&error=' . urlencode('KYC verification is required for creators. Please provide ID Type and ID Number.')));
                exit;
            }
            
            // Validate ID Number format (numbers only)
            if (!preg_match('/^[0-9]+$/', $kyc_id_number)) {
                require_once(ABSPATH . 'wp-admin/includes/user.php');
                wp_delete_user($user_id);
                wp_redirect(home_url('/?registration=error&error=' . urlencode('ID Number must contain only numbers.')));
                exit;
            }
            
            // Handle ID document upload
            $kyc_document_url = '';
            if (!empty($_FILES['kyc_document']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                $upload = wp_handle_upload($_FILES['kyc_document'], array('test_form' => false));
                if (isset($upload['error'])) {
                    require_once(ABSPATH . 'wp-admin/includes/user.php');
                    wp_delete_user($user_id);
                    wp_redirect(home_url('/?registration=error&error=' . urlencode('Document upload failed: ' . $upload['error'])));
                    exit;
                }
                $kyc_document_url = esc_url_raw($upload['url']);
            }
            
            // Handle photo capture upload
            $photo_capture_url = '';
            if (!empty($_FILES['kyc_photo_capture']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                $photo_upload = wp_handle_upload($_FILES['kyc_photo_capture'], array('test_form' => false));
                if (isset($photo_upload['error'])) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    wp_delete_user($user_id);
                    wp_redirect(home_url('/?registration=error&error=' . urlencode('Photo capture upload failed: ' . $photo_upload['error'])));
                    exit;
                }
                $photo_capture_url = esc_url_raw($photo_upload['url']);
            }
            
            // Check if either document or photo is provided
            if (empty($kyc_document_url) && empty($photo_capture_url)) {
                // Check if photo data is in POST (from camera capture)
                $kyc_photo_data = isset($_POST['kyc_photo_data']) ? $_POST['kyc_photo_data'] : '';
                if (empty($kyc_photo_data)) {
                    require_once(ABSPATH . 'wp-admin/includes/user.php');
                    wp_delete_user($user_id);
                    wp_redirect(home_url('/?registration=error&error=' . urlencode('Please upload an ID document or capture a photo for verification.')));
                    exit;
                } else {
                    // Save photo data as base64 and convert to file
                    $photo_data = $kyc_photo_data;
                    $upload_dir = wp_upload_dir();
                    $kyc_dir = $upload_dir['basedir'] . '/nymia-kyc';
                    if (!file_exists($kyc_dir)) {
                        wp_mkdir_p($kyc_dir);
                    }
                    
                    // Decode base64 image
                    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photo_data));
                    $filename = 'kyc-photo-' . $user_id . '-' . time() . '.png';
                    $filepath = $kyc_dir . '/' . $filename;
                    
                    if (file_put_contents($filepath, $image_data)) {
                        $photo_capture_url = $upload_dir['baseurl'] . '/nymia-kyc/' . $filename;
                    }
                }
            }
            
            // Save KYC data
            $kyc_data = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $date_of_birth,
                'phone' => isset($_POST['kyc_phone']) ? sanitize_text_field($_POST['kyc_phone']) : '',
                'street' => isset($_POST['street']) ? sanitize_text_field($_POST['street']) : '',
                'city' => isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '',
                'country' => isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '',
                'zip_code' => isset($_POST['postal_code']) ? sanitize_text_field($_POST['postal_code']) : '',
                'id_type' => $kyc_id_type,
                'id_number' => $kyc_id_number,
                'document_url' => $kyc_document_url,
                'photo_capture_url' => $photo_capture_url,
                'submitted_at' => current_time('mysql'),
            );
            
            update_user_meta($user_id, 'nymia_creator_kyc_data', $kyc_data);
            update_user_meta($user_id, 'nymia_creator_kyc_status', 'pending');
        }
    }

    // SEND: Admin notification email about new user registration
    nymia_send_admin_new_user_notification($user_id, $username, $email, $first_name, $last_name);

    // CHECK: If email verification is enabled
    $email_verification_enabled = get_option('nymia_enable_email_verification', '1');
    
    if ($email_verification_enabled == '1') {
        // MARK: User as unverified
        update_user_meta($user_id, 'email_verified', '0');
        
        // GENERATE: Verification code
        $verification_code = nymia_generate_verification_code();
        
        // STORE: Verification code for user
        update_user_meta($user_id, 'verification_code', $verification_code);
        
        // GET: Expiry time from settings
        $verification_expiry = get_option('nymia_verification_code_expiry', 15);
        update_user_meta($user_id, 'verification_expiry', time() + ($verification_expiry * MINUTE_IN_SECONDS));
        
        // SEND: Verification email to user
        nymia_send_verification_email($email, $verification_code);
        
        // REDIRECT: To verification page (DO NOT auto-login)
        wp_redirect(home_url('/verify-email/?user_id=' . $user_id));
        exit;
    } else {
        // MARK: User as verified (verification disabled)
        update_user_meta($user_id, 'email_verified', '1');
    
        // AUTO-LOGIN: Only if verification is disabled
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        
        // SEND: Welcome email to new user
        nymia_send_new_user_welcome_email($user_id, $username, $email, $first_name, $last_name);
    
        // REDIRECT: To profile page with success message
        wp_redirect(home_url('/?registration=success'));
        exit;
    }
} // END: nymia_user_register_handler()
add_action('admin_post_nymia_user_register', 'nymia_user_register_handler');
add_action('admin_post_nopriv_nymia_user_register', 'nymia_user_register_handler');

/**
 * EMAIL VERIFICATION HANDLER
 * ---------------------------
 * Verifies the email verification code and activates the user account
 * Hooks into: admin_post_nymia_verify_email
 */
function nymia_verify_email_handler() {
    // SECURITY: Verify nonce
    if (!isset($_POST['nymia_verify_nonce']) || !wp_verify_nonce($_POST['nymia_verify_nonce'], 'nymia_verify_email')) {
        wp_redirect(home_url('/verify-email/?error=security'));
        exit;
    }
    
    // GET: User ID or verification key and code
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $verification_key = isset($_POST['verification_key']) ? sanitize_text_field($_POST['verification_key']) : '';
    $entered_code = isset($_POST['verification_code']) ? sanitize_text_field($_POST['verification_code']) : '';
    
    if (empty($entered_code)) {
        if ($user_id > 0) {
            wp_redirect(home_url('/verify-email/?user_id=' . $user_id . '&error=missing'));
        } else {
        wp_redirect(home_url('/verify-email/?key=' . $verification_key . '&error=missing'));
        }
        exit;
    }
    
    // HANDLE: User ID based verification (new flow)
    if ($user_id > 0) {
        $user = get_user_by('ID', $user_id);
        
        if (!$user) {
            wp_redirect(home_url('/?registration=error&error=' . urlencode('User not found. Please sign up again!')));
            exit;
        }
        
        // CHECK: If already verified
        $email_verified = get_user_meta($user_id, 'email_verified', true);
        if ($email_verified == '1') {
            // Already verified, just log them in
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id, true);
            wp_redirect(home_url('/?registration=success'));
            exit;
        }
        
        // GET: Stored verification code
        $stored_code = get_user_meta($user_id, 'verification_code', true);
        $expiry = get_user_meta($user_id, 'verification_expiry', true);
        
        // VALIDATE: Code expiry
        if (empty($expiry) || time() > $expiry) {
            wp_redirect(home_url('/verify-email/?user_id=' . $user_id . '&error=' . urlencode('Verification code expired. Please request a new one.')));
            exit;
        }
        
        // VALIDATE: Verification code
        if ($stored_code !== $entered_code) {
            wp_redirect(home_url('/verify-email/?user_id=' . $user_id . '&error=invalid'));
            exit;
        }
        
        // MARK: Email as verified
        update_user_meta($user_id, 'email_verified', '1');
        
        // DELETE: Verification code and expiry
        delete_user_meta($user_id, 'verification_code');
        delete_user_meta($user_id, 'verification_expiry');
        
        // GET: User data for welcome email
        $user = get_userdata($user_id);
        if ($user) {
            $first_name = get_user_meta($user_id, 'first_name', true);
            $last_name = get_user_meta($user_id, 'last_name', true);
            // SEND: Welcome email to new user after verification
            nymia_send_new_user_welcome_email($user_id, $user->user_login, $user->user_email, $first_name, $last_name);
        }
        
        // AUTO-LOGIN: Automatically log in the verified user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        
        // REDIRECT: To dashboard with success message
        wp_redirect(home_url('/?registration=success&verified=1'));
        exit;
    }
    
    // HANDLE: Legacy transient-based verification (backward compatibility)
    if (!empty($verification_key)) {
    $verification_data = get_transient($verification_key);
    
    if (!$verification_data) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Verification link expired. Please sign up again!')));
        exit;
    }
    
    // VALIDATE: Verification code
    if ($verification_data['verification_code'] !== $entered_code) {
        wp_redirect(home_url('/verify-email/?key=' . $verification_key . '&error=invalid'));
        exit;
    }
    
        // CREATE: WordPress user (legacy flow)
    $user_id = wp_create_user($verification_data['username'], $verification_data['password'], $verification_data['email']);
    
    if (is_wp_error($user_id)) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode($user_id->get_error_message())));
        exit;
    }
        
        // MARK: Email as verified
        update_user_meta($user_id, 'email_verified', '1');
    
    // DELETE: Verification data after successful creation
    delete_transient($verification_key);
    
    // AUTO-LOGIN: Automatically log in the new user
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    
    // REDIRECT: To dashboard with success message
    wp_redirect(home_url('/?registration=success'));
        exit;
    }
    
    // If we get here, something went wrong
    wp_redirect(home_url('/?registration=error&error=' . urlencode('Invalid verification request.')));
    exit;
} // END: nymia_verify_email_handler()
add_action('admin_post_nymia_verify_email', 'nymia_verify_email_handler');
add_action('admin_post_nopriv_nymia_verify_email', 'nymia_verify_email_handler');

/**
 * RESEND VERIFICATION CODE
 * -------------------------
 * Resends the verification code to the user's email
 * Hooks into: admin_post_nymia_resend_code
 */
function nymia_resend_verification_code_handler() {
    // SECURITY: Verify nonce
    if (!isset($_POST['nymia_resend_nonce']) || !wp_verify_nonce($_POST['nymia_resend_nonce'], 'nymia_resend_code')) {
        wp_redirect(home_url('/verify-email/?error=security'));
        exit;
    }
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $verification_key = isset($_POST['verification_key']) ? sanitize_text_field($_POST['verification_key']) : '';
    
    // HANDLE: User ID based resend (new flow)
    if ($user_id > 0) {
        $user = get_user_by('ID', $user_id);
        
        if (!$user) {
            wp_redirect(home_url('/?registration=error&error=' . urlencode('User not found. Please sign up again!')));
        exit;
    }
    
        // CHECK: If already verified
        $email_verified = get_user_meta($user_id, 'email_verified', true);
        if ($email_verified == '1') {
            // Already verified, redirect to login
            wp_redirect(home_url('/?registration=success'));
            exit;
        }
        
        // GENERATE: New verification code
        $verification_code = nymia_generate_verification_code();
        
        // GET: Expiry time from settings
        $verification_expiry = get_option('nymia_verification_code_expiry', 15);
        
        // UPDATE: Store new verification code
        update_user_meta($user_id, 'verification_code', $verification_code);
        update_user_meta($user_id, 'verification_expiry', time() + ($verification_expiry * MINUTE_IN_SECONDS));
        
        // SEND: New verification email
        $email_sent = nymia_send_verification_email($user->user_email, $verification_code);
        
        if ($email_sent) {
            wp_redirect(home_url('/verify-email/?user_id=' . $user_id . '&resent=1'));
        } else {
            wp_redirect(home_url('/verify-email/?user_id=' . $user_id . '&error=send'));
        }
        exit;
    }
    
    // HANDLE: Legacy transient-based resend (backward compatibility)
    if (!empty($verification_key)) {
    $verification_data = get_transient($verification_key);
    
    if (!$verification_data) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode('Verification link expired. Please sign up again!')));
        exit;
    }
    
    // GENERATE: New verification code
    $verification_code = nymia_generate_verification_code();
    
    // GET: Expiry time from settings
    $verification_expiry = get_option('nymia_verification_code_expiry', 15);
    
    // UPDATE: Verification data with new code
    $verification_data['verification_code'] = $verification_code;
    $verification_data['timestamp'] = time();
    
    set_transient($verification_key, $verification_data, $verification_expiry * MINUTE_IN_SECONDS);
    
    // SEND: New verification email
    $email_sent = nymia_send_verification_email($verification_data['email'], $verification_code);
    
    if ($email_sent) {
        wp_redirect(home_url('/verify-email/?key=' . $verification_key . '&resent=1'));
    } else {
        wp_redirect(home_url('/verify-email/?key=' . $verification_key . '&error=send'));
    }
        exit;
    }
    
    // If we get here, something went wrong
    wp_redirect(home_url('/?registration=error&error=invalid'));
    exit;
} // END: nymia_resend_verification_code_handler()
add_action('admin_post_nymia_resend_code', 'nymia_resend_verification_code_handler');
add_action('admin_post_nopriv_nymia_resend_code', 'nymia_resend_verification_code_handler');

/**
 * VERIFY EMAIL FROM PROFILE
 * --------------------------
 * Handles email verification from profile page
 * Hooks into: admin_post_nymia_verify_profile_email
 */
function nymia_verify_profile_email_handler() {
    // SECURITY: Verify nonce
    if (!isset($_POST['nymia_verify_profile_nonce']) || !wp_verify_nonce($_POST['nymia_verify_profile_nonce'], 'nymia_verify_profile_email')) {
        wp_redirect(home_url('/profile/?verify=security'));
        exit;
    }
    
    // GET: User ID and code
    $user_id = intval($_POST['user_id']);
    $entered_code = sanitize_text_field($_POST['verification_code']);
    
    if (empty($user_id) || empty($entered_code)) {
        wp_redirect(home_url('/profile/?verify=missing'));
        exit;
    }
    
    // GET: Stored verification code
    $stored_code = get_user_meta($user_id, 'verification_code', true);
    
    // VALIDATE: Verification code
    if ($stored_code !== $entered_code) {
        wp_redirect(home_url('/profile/?verify=invalid'));
        exit;
    }
    
    // MARK: User as verified
    update_user_meta($user_id, 'email_verified', '1');
    
    // DELETE: Verification code and expiry
    delete_user_meta($user_id, 'verification_code');
    delete_user_meta($user_id, 'verification_expiry');
    
    // REDIRECT: Back to profile with success
    wp_redirect(home_url('/profile/?verify=success'));
    exit;
} // END: nymia_verify_profile_email_handler()
add_action('admin_post_nymia_verify_profile_email', 'nymia_verify_profile_email_handler');

/**
 * RESEND VERIFICATION CODE FROM PROFILE
 * -------------------------------------
 * Resends verification code to user's email from profile page
 * Hooks into: wp_ajax_nymia_resend_profile_code
 */
function nymia_resend_profile_code_handler() {
    // SECURITY: Verify nonce
    check_ajax_referer('nymia_resend_profile_code', 'nymia_resend_profile_nonce');
    
    $user_id = intval($_POST['user_id']);
    
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Invalid request'));
        return;
    }
    
    // GET: User data
    $user = get_userdata($user_id);
    
    if (!$user) {
        wp_send_json_error(array('message' => 'User not found'));
        return;
    }
    
    // GENERATE: New verification code
    $verification_code = nymia_generate_verification_code();
    
    // UPDATE: Verification code
    update_user_meta($user_id, 'verification_code', $verification_code);
    
    // GET: Expiry time from settings
    $verification_expiry = get_option('nymia_verification_code_expiry', 15);
    update_user_meta($user_id, 'verification_expiry', time() + ($verification_expiry * MINUTE_IN_SECONDS));
    
    // SEND: Verification email
    $email_sent = nymia_send_verification_email($user->user_email, $verification_code);
    
    if ($email_sent) {
        wp_send_json_success(array('message' => 'Verification code resent'));
    } else {
        wp_send_json_error(array('message' => 'Failed to send email'));
    }
} // END: nymia_resend_profile_code_handler()
add_action('wp_ajax_nymia_resend_profile_code', 'nymia_resend_profile_code_handler');

// ==========================================
// EBOOK BOOKMARK FUNCTIONALITY
// ==========================================
/**
 * Toggle ebook bookmark
 * Add or remove ebook from user's bookmarks
 */
function nymia_toggle_ebook_bookmark() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to bookmark ebooks.', 'nymia')), 401);
        return;
    }

    check_ajax_referer('nymia_ebook_bookmark', 'nonce');

    $ebook_id = isset($_POST['ebook_id']) ? sanitize_text_field($_POST['ebook_id']) : '';
    if (empty($ebook_id)) {
        wp_send_json_error(array('message' => __('Invalid ebook ID.', 'nymia')));
        return;
    }

    $user_id = get_current_user_id();
    $bookmarks = get_user_meta($user_id, '_nymia_ebook_bookmarks', true);
    $bookmarks = is_array($bookmarks) ? $bookmarks : array();

    $is_bookmarked = in_array($ebook_id, $bookmarks);

    if ($is_bookmarked) {
        // Remove bookmark
        $bookmarks = array_values(array_filter($bookmarks, function($id) use ($ebook_id) {
            return $id !== $ebook_id;
        }));
        $message = __('Bookmark removed.', 'nymia');
    } else {
        // Add bookmark
        if (!in_array($ebook_id, $bookmarks)) {
            $bookmarks[] = $ebook_id;
        }
        $message = __('Bookmark added.', 'nymia');
    }

    update_user_meta($user_id, '_nymia_ebook_bookmarks', $bookmarks);

    wp_send_json_success(array(
        'message' => $message,
        'is_bookmarked' => !$is_bookmarked,
        'bookmark_count' => count($bookmarks)
    ));
}
add_action('wp_ajax_nymia_toggle_ebook_bookmark', 'nymia_toggle_ebook_bookmark');

/**
 * Get user's bookmarked ebooks
 */
function nymia_get_user_ebook_bookmarks() {
    if (!is_user_logged_in()) {
        return array();
    }

    $user_id = get_current_user_id();
    $bookmarks = get_user_meta($user_id, '_nymia_ebook_bookmarks', true);
    return is_array($bookmarks) ? $bookmarks : array();
}

/**
 * Check if ebook is bookmarked by current user
 */
function nymia_is_ebook_bookmarked($ebook_id) {
    if (!is_user_logged_in()) {
        return false;
    }

    $user_id = get_current_user_id();
    $bookmarks = get_user_meta($user_id, '_nymia_ebook_bookmarks', true);
    $bookmarks = is_array($bookmarks) ? $bookmarks : array();
    
    return in_array($ebook_id, $bookmarks);
}

// ==========================================
// ACCESS CONTROL
// ==========================================
/**
 * RESTRICT PAGE ACCESS
 * --------------------
 * Redirects non-logged-in users to home page (login form)
 * Protects all pages - users must login to see anything
 * Hooks into: template_redirect
 */
function nymia_restrict_page_access() {
    // Only intercept requests from logged-out visitors on the public site.
    if (is_user_logged_in() || is_admin()) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    // Always allow the login form, admin area, REST API, feeds, and preview links.
    $skip_fragments = array('wp-login.php', 'wp-admin', 'wp-json', 'feed', 'preview=');
    foreach ($skip_fragments as $fragment) {
        if (strpos($request_uri, $fragment) !== false) {
            return;
        }
    }

    // Allow the homepage or blog index to load freely for guests.
    if (is_front_page() || is_home()) {
        return;
    }

    // Allow public marketing/content pages without forcing login.
    $public_pages = array('audio', 'ebook', 'single-audio', 'single-ebook', 'single-post', 'policies', 'login');
    if (is_page($public_pages)) {
        return;
    }

    // Restrict only the creator/finance tools.
    $protected_pages = array('earnings', 'create', 'profile', 'privacy');
    if (is_page($protected_pages)) {
        wp_redirect(home_url('/login/'));
        exit;
    }
} // END: nymia_restrict_page_access()
add_action('template_redirect', 'nymia_restrict_page_access');

/**
 * FORCE PUBLIC DASHBOARD TEMPLATE FOR FRONT PAGE
 * ----------------------------------------------
 * Guarantees the site root always renders the dashboard/public layout
 * even if the WordPress "Front page" setting points at the login page.
 */
function nymia_force_public_front_page_template($template) {
    if (!is_user_logged_in() && is_front_page()) {
        $index_template = get_template_directory() . '/index.php';
        if (file_exists($index_template)) {
            return $index_template;
        }
    }
    return $template;
}
add_filter('template_include', 'nymia_force_public_front_page_template', 5);

/**
 * FORCE TEMPLATE FOR SINGLE AUDIO PAGE
 * ------------------------------------
 * Ensures page-single-audio.php is used for the single-audio page
 */
function nymia_force_single_audio_template($template) {
    global $post;
    
    if (is_page() && $post) {
        // Check if this is the single-audio page
        if ($post->post_name === 'single-audio') {
            $single_audio_template = get_template_directory() . '/page-single-audio.php';
            if (file_exists($single_audio_template)) {
                return $single_audio_template;
            }
        }
        // Check if this is the single-ebook page
        elseif ($post->post_name === 'single-ebook') {
            $single_ebook_template = get_template_directory() . '/page-single-ebook.php';
            if (file_exists($single_ebook_template)) {
                return $single_ebook_template;
            }
        }
        // Check if this is the single-post page
        elseif ($post->post_name === 'single-post') {
            $single_post_template = get_template_directory() . '/page-single-post.php';
            if (file_exists($single_post_template)) {
                return $single_post_template;
            }
        }
    }
    
    return $template;
}
add_filter('page_template', 'nymia_force_single_audio_template');

/**
 * REDIRECT LOGGED-IN USERS
 * -------------------------
 * (Currently unused - placeholders for future implementation)
 */
function nymia_redirect_logged_in_users() {
    // No redirect needed - logged in users should see content
} // END: nymia_redirect_logged_in_users()

/**
 * CUSTOM LOGIN REDIRECT
 * ----------------------
 * Redirects users to dashboard after successful login
 * Hooks into: login_redirect filter
 */
function nymia_custom_login_redirect($redirect_to, $requested_redirect_to, $user) {
    // REDIRECT: To dashboard if no login errors
    if (!isset($user->errors)) {
        return home_url('/dashboard');
    }
    return $redirect_to;
} // END: nymia_custom_login_redirect()
add_filter('login_redirect', 'nymia_custom_login_redirect', 10, 3);

/**
 * CREATE LOGIN PAGE
 * -----------------
 * Creates the login page on theme activation
 * Hooks into: after_setup_theme
 */
function nymia_create_login_page() {
    $login_page = get_page_by_path('page-login');
    
    if (!$login_page) {
        $page_data = array(
            'post_title'    => 'Login / Signup',
            'post_content'  => 'Login and signup page.',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'page-login',
            'post_author'   => 1,
            'page_template' => 'page-login.php',
        );
        
        $login_id = wp_insert_post($page_data);
        
        if ($login_id) {
            update_post_meta($login_id, '_wp_page_template', 'page-login.php');
        }
    }
} // END: nymia_create_login_page()
add_action('after_setup_theme', 'nymia_create_login_page');

/**
 * CREATE LOGIN PAGE ON ACTIVATION
 * --------------------------------
 * Runs login page creation when theme is activated
 * Hooks into: after_switch_theme
 */
function nymia_create_login_page_on_activation() {
    nymia_create_login_page();
} // END: nymia_create_login_page_on_activation()
add_action('after_switch_theme', 'nymia_create_login_page_on_activation');

// ==========================================
// PROFILE MANAGEMENT (AJAX)
// ==========================================
/**
 * HANDLE PROFILE UPDATE VIA AJAX
 * -------------------------------
 * Processes profile updates including:
 * - Display name, email, website
 * - Phone, location, description
 * - Profile avatar and cover image uploads
 * - Social media links (Facebook, Twitter, LinkedIn, GitHub)
 * Hooks into: wp_ajax_nymia_update_profile
 */
function nymia_update_profile_handler() {
    // SECURITY: Verify AJAX nonce
    check_ajax_referer('nymia_profile_update', 'nonce');
    
    // CHECK: User must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to update your profile.'));
        return;
    }
    
    // GET: Current user data
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    
    // ========================================
    // HANDLE PROFILE AVATAR UPLOAD
    // ========================================
    if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] == UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        $upload_overrides = array('test_form' => false);
        $uploaded = wp_handle_upload($_FILES['profile_avatar'], $upload_overrides);
        
        if ($uploaded && !isset($uploaded['error'])) {
            // SAVE: Avatar URL to user meta
            update_user_meta($user_id, 'custom_avatar', $uploaded['url']);
        }
    }
    
    // ========================================
    // HANDLE COVER IMAGE UPLOAD
    // ========================================
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        // Validate file type
        $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
        $file_type = wp_check_filetype($_FILES['cover_image']['name']);
        
        if (!in_array($_FILES['cover_image']['type'], $allowed_types) && !in_array($file_type['type'], $allowed_types)) {
            wp_send_json_error(array('message' => 'Invalid file type. Please upload JPG, PNG, GIF, or WebP image.'));
            return;
        }
        
        // Validate file size (max 5MB)
        if ($_FILES['cover_image']['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(array('message' => 'File size too large. Maximum size is 5MB.'));
            return;
        }
        
        $upload_overrides = array('test_form' => false);
        $uploaded = wp_handle_upload($_FILES['cover_image'], $upload_overrides);
        
        if ($uploaded && !isset($uploaded['error'])) {
            // SAVE: Cover image URL to user meta
            update_user_meta($user_id, 'cover_image', $uploaded['url']);
        } else {
            wp_send_json_error(array('message' => isset($uploaded['error']) ? $uploaded['error'] : 'Failed to upload cover image.'));
            return;
        }
    }
    
    // ========================================
    // HANDLE COVER IMAGE REMOVAL
    // ========================================
    if (isset($_POST['remove_cover_image']) && $_POST['remove_cover_image'] == '1') {
        delete_user_meta($user_id, 'cover_image');
    }
    
    // ========================================
    // UPDATE USER DATA
    // ========================================
    // Update display name
    if (isset($_POST['display_name'])) {
        $display_name = sanitize_text_field($_POST['display_name']);
        wp_update_user(array('ID' => $user_id, 'display_name' => $display_name));
    }
    
    // UPDATE: Email address
    if (isset($_POST['user_email'])) {
        $user_email = sanitize_email($_POST['user_email']);
        if (is_email($user_email)) {
            wp_update_user(array('ID' => $user_id, 'user_email' => $user_email));
        }
    }
    
    // UPDATE: Website URL
    if (isset($_POST['user_url'])) {
        $user_url = esc_url_raw($_POST['user_url']);
        wp_update_user(array('ID' => $user_id, 'user_url' => $user_url));
    }
    
    // UPDATE: Description/Bio
    if (isset($_POST['description'])) {
        $description = sanitize_textarea_field($_POST['description']);
        update_user_meta($user_id, 'description', $description);
    }
    
    // UPDATE: Phone number
    if (isset($_POST['phone'])) {
        $phone = sanitize_text_field($_POST['phone']);
        update_user_meta($user_id, 'phone', $phone);
    }
    
    // UPDATE: Location
    if (isset($_POST['location'])) {
        $location = sanitize_text_field($_POST['location']);
        update_user_meta($user_id, 'location', $location);
    }
    
    // UPDATE: Stripe Account Number (creators only)
    $current_roles = (array) $current_user->roles;
    $can_manage_stripe = in_array('creator', $current_roles, true) || in_array('administrator', $current_roles, true);
    if ($can_manage_stripe && isset($_POST['stripe_account_no'])) {
        $stripe_account = sanitize_text_field($_POST['stripe_account_no']);
        update_user_meta($user_id, 'stripe_account_no', $stripe_account);
        if (!empty($stripe_account) && stripos($stripe_account, 'acct_') === 0) {
            update_user_meta($user_id, 'nymia_stripe_account_id', $stripe_account);
        }
    }
    
    // UPDATE: SWIFT/BIC Code (creators only)
    if ($can_manage_stripe && isset($_POST['swift_bic'])) {
        $swift_bic = sanitize_text_field($_POST['swift_bic']);
        update_user_meta($user_id, 'nymia_swift_bic', $swift_bic);
    }
    
    // ========================================
    // UPDATE SOCIAL MEDIA LINKS
    // ========================================
    if (isset($_POST['facebook'])) {
        update_user_meta($user_id, 'facebook', esc_url_raw($_POST['facebook']));
    }
    if (isset($_POST['twitter'])) {
        update_user_meta($user_id, 'twitter', esc_url_raw($_POST['twitter']));
    }
    if (isset($_POST['linkedin'])) {
        update_user_meta($user_id, 'linkedin', esc_url_raw($_POST['linkedin']));
    }
    if (isset($_POST['github'])) {
        update_user_meta($user_id, 'github', esc_url_raw($_POST['github']));
    }
    
    // RETURN: Success response
    wp_send_json_success(array('message' => 'Profile updated successfully!'));
} // END: nymia_update_profile_handler()
add_action('wp_ajax_nymia_update_profile', 'nymia_update_profile_handler');

// ==========================================
// HELPER FUNCTIONS
// ==========================================

/**
 * GET USER PROFILE URL
 * --------------------
 * Returns the URL to view a user's profile
 * 
 * @param int $user_id User ID
 * @return string Profile URL
 */
function nymia_get_user_profile_url($user_id) {
    if (!$user_id) {
        return home_url('/profile/');
    }
    
    // GET: User by ID to get username
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return home_url('/profile/');
    }
    
    // USE: Username in URL instead of user_id
    $username = $user->user_login;
    return add_query_arg('username', $username, home_url('/profile/'));
}

/**
 * Format a user record for the admin dashboard tables.
 *
 * @param WP_User $wp_user           User object.
 * @param int     $current_admin_id  Current admin ID.
 * @param bool    $can_promote       Whether current admin can promote users.
 * @param bool    $can_delete_users  Whether current admin can delete users.
 * @param bool    $can_edit_users    Whether current admin can edit users.
 *
 * @return array
 */
function nymia_format_admin_user_entry($wp_user, $current_admin_id = 0, $can_promote = false, $can_delete_users = false, $can_edit_users = false) {
    if (!$wp_user instanceof WP_User) {
        return array();
    }

    $role_key = !empty($wp_user->roles) ? strtolower($wp_user->roles[0]) : 'subscriber';
    $display_role = ucwords(str_replace('_', ' ', $role_key));

    if (in_array($role_key, array('administrator', 'author'), true)) {
        $display_role = __('Creator', 'nymia');
    } elseif ($role_key === 'subscriber') {
        $display_role = __('Buyer', 'nymia');
    }

    $status_slug = nymia_get_user_account_status($wp_user->ID);
    $status_label = nymia_get_user_account_status_label($status_slug);

    $is_self = ($wp_user->ID === $current_admin_id);
    $is_admin = user_can($wp_user->ID, 'manage_options');

    $can_manage_status = $can_promote && !$is_self && !$is_admin;
    $can_delete = $can_delete_users && !$is_self && !$is_admin;
    $can_edit = $can_edit_users && !$is_self;

    $joined = $wp_user->user_registered ? date_i18n(get_option('date_format'), strtotime($wp_user->user_registered)) : '';

    return array(
        'user_id'            => $wp_user->ID,
        'avatar'             => get_avatar_url($wp_user->ID),
        'name'               => $wp_user->display_name ?: $wp_user->user_login,
        'role'               => $display_role,
        'status'             => $status_label,
        'status_slug'        => $status_slug,
        'joined'             => $joined,
        'earnings'           => '-',
        'profile_url'        => nymia_get_user_profile_url($wp_user->ID),
        'admin_url'          => $can_edit ? admin_url('user-edit.php?user_id=' . $wp_user->ID) : '',
        'email'              => $wp_user->user_email,
        'can_manage_status'  => $can_manage_status,
        'can_delete'         => $can_delete,
        'can_edit'           => $can_edit,
        'is_self'            => $is_self,
    );
}

/**
 * Retrieve the stored account status for a given user.
 *
 * @param int $user_id User ID.
 * @return string one of active|suspended|banned
 */
function nymia_get_user_account_status($user_id) {
    $status = get_user_meta($user_id, 'nymia_account_status', true);
    $status = $status ? sanitize_key($status) : 'active';
    $allowed = array('active', 'suspended', 'banned');
    if (!in_array($status, $allowed, true)) {
        $status = 'active';
    }

    if ($status === 'suspended') {
        $expires = (int) get_user_meta($user_id, 'nymia_account_status_expires', true);
        if ($expires && $expires <= current_time('timestamp')) {
            nymia_set_user_account_status($user_id, 'active', null);
            $status = 'active';
        }
    }

    return $status;
}

/**
 * Store the account status for a user.
 *
 * @param int    $user_id User ID.
 * @param string $status  Status slug.
 * @return void
 */
function nymia_set_user_account_status($user_id, $status, $expires_at = null) {
    $status = sanitize_key($status);
    if ($status === 'active' || $status === '') {
        delete_user_meta($user_id, 'nymia_account_status');
        delete_user_meta($user_id, 'nymia_account_status_expires');
    } else {
        update_user_meta($user_id, 'nymia_account_status', $status);
        if (!empty($expires_at)) {
            update_user_meta($user_id, 'nymia_account_status_expires', (int) $expires_at);
        } else {
            delete_user_meta($user_id, 'nymia_account_status_expires');
        }
    }
    update_user_meta($user_id, 'nymia_account_status_changed', current_time('mysql'));
}

/**
 * Human readable label for account status.
 *
 * @param string $status Status slug.
 * @return string
 */
function nymia_get_user_account_status_label($status) {
    $status = sanitize_key($status);
    switch ($status) {
        case 'suspended':
            return __('Suspended', 'nymia');
        case 'banned':
            return __('Banned', 'nymia');
        default:
            return __('Active', 'nymia');
    }
}

/**
 * Prevent suspended or banned users from authenticating.
 */
function nymia_block_inactive_logins($user, $username, $password) {
    if ($user instanceof WP_User) {
        $status = nymia_get_user_account_status($user->ID);
        if ($status === 'suspended') {
            return new WP_Error('nymia_account_suspended', __('Your account is suspended. Please contact support.', 'nymia'));
        }
        if ($status === 'banned') {
            return new WP_Error('nymia_account_banned', __('Your account has been banned. Please contact support.', 'nymia'));
        }
    }
    return $user;
}
add_filter('authenticate', 'nymia_block_inactive_logins', 30, 3);

/**
 * Force logout for users whose status becomes inactive while logged in.
 */
function nymia_enforce_account_status() {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();
    $status = nymia_get_user_account_status($user_id);
    if ($status === 'active') {
        return;
    }

    wp_logout();
    wp_redirect(add_query_arg('account_status', $status, home_url('/')));
    exit;
}
add_action('init', 'nymia_enforce_account_status');

/**
 * Send an email to the user about their account status change.
 *
 * @param WP_User $user  User object.
 * @param string  $status Status slug (active|suspended|banned|deleted).
 * @param string  $note   Optional note from admin.
 */
function nymia_send_account_status_email($user, $status, $note = '', $expires_at = null) {
    if (!$user instanceof WP_User || empty($user->user_email)) {
        return;
    }

    $status = sanitize_key($status);
    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $user_name = $user->display_name ?: $user->user_login;

    switch ($status) {
        case 'suspended':
            $subject = sprintf(__('[%s] Your account has been suspended', 'nymia'), $site_name);
            $intro   = __('Your account has been temporarily suspended by our team. You will not be able to sign in until the suspension is lifted.', 'nymia');
            break;
        case 'banned':
            $subject = sprintf(__('[%s] Your account has been banned', 'nymia'), $site_name);
            $intro   = __('Your account has been permanently banned. You will no longer be able to access the platform.', 'nymia');
            break;
        case 'deleted':
            $subject = sprintf(__('[%s] Your account has been deleted', 'nymia'), $site_name);
            $intro   = __('Your account has been removed from the platform and all associated data has been deleted.', 'nymia');
            break;
        case 'active':
        default:
            $subject = sprintf(__('[%s] Your account has been reactivated', 'nymia'), $site_name);
            $intro   = __('Your account has been reactivated and you can sign in again.', 'nymia');
            break;
    }

    $message_lines = array(
        sprintf(__('Hi %s,', 'nymia'), $user_name),
        '',
        $intro,
    );

    if (!empty($note)) {
        $message_lines[] = '';
        $message_lines[] = __('Message from the Nymia team:', 'nymia');
        $message_lines[] = $note;
    }

    if ($status === 'suspended' && !empty($expires_at)) {
        $message_lines[] = '';
        $message_lines[] = sprintf(
            __('Your account will be reactivated automatically on %s.', 'nymia'),
            wp_date(get_option('date_format') . ' ' . get_option('time_format'), $expires_at)
        );
    }

    $message_lines[] = '';
    $message_lines[] = __('If you have any questions, just reply to this email and we\'ll be happy to help.', 'nymia');
    $message_lines[] = '';
    $message_lines[] = sprintf(__('— %s Team', 'nymia'), $site_name);

    $message = implode("\n", $message_lines);
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    wp_mail($user->user_email, $subject, $message, $headers);
}

/**
 * GET REAL USER SUGGESTIONS
 * -------------------------
 * Returns array of real WordPress users for suggestions
 * Excludes current user from the suggestions
 * 
 * @return array List of user suggestions
 */
/**
 * GET SMART USER SUGGESTIONS (Facebook-like Algorithm)
 * ----------------------------------------------------
 * Suggests users based on:
 * 1. Mutual connections (people you both follow)
 * 2. People who liked/commented on the same posts
 * 3. Popular creators with content
 * 4. People who follow you back
 * 
 * @return array Sorted suggestions with scores
 */
function nymia_get_real_user_suggestions() {
    // GET: Current user
    $current_user = wp_get_current_user();
    $current_user_id = $current_user->ID;
    
    if (!$current_user_id) {
        return array(); // Not logged in, return empty
    }
    
    // GET: Current user's following list
    $current_following = get_user_meta($current_user_id, 'nymia_following', true);
    if (!is_array($current_following)) {
        $current_following = array();
    }
    $current_following = array_map('intval', $current_following);
    
    // GET: All users (exclude current user and already following)
    $exclude_users = array_merge(array($current_user_id), $current_following);
    $all_users = get_users(array(
        'number' => 50, // Get more users for better scoring
        'exclude' => $exclude_users,
    ));
    
    // INIT: Suggestions array with scores
    $scored_users = array();
    
    // LOOP: Through users and calculate suggestion scores
    foreach ($all_users as $user) {
        $user_id = intval($user->ID);
        
        // FILTER: Only show creators with approved KYC status in suggestions
        // Skip regular users/clients - only show verified creators
        if (!nymia_is_creator_verified($user_id)) {
            continue; // Skip this user - not a verified creator
        }
        
        $score = 0;
        $reasons = array();
        
        // 1. MUTUAL CONNECTIONS (High Priority)
        // Check how many mutual friends/connections
        $user_following = get_user_meta($user_id, 'nymia_following', true);
        if (!is_array($user_following)) {
            $user_following = array();
        }
        $user_following = array_map('intval', $user_following);
        
        $mutual_connections = array_intersect($current_following, $user_following);
        $mutual_count = count($mutual_connections);
        if ($mutual_count > 0) {
            $score += ($mutual_count * 20); // 20 points per mutual connection
            $reasons[] = sprintf(__('%d mutual connection(s)', 'nymia'), $mutual_count);
        }
        
        // 2. PEOPLE WHO LIKED/COMMENTED ON SAME POSTS
        // Check social posts liked by both users
        $current_liked_posts = get_user_meta($current_user_id, '_nymia_liked_posts', true);
        $user_liked_posts = get_user_meta($user_id, '_nymia_liked_posts', true);
        
        if (is_array($current_liked_posts) && is_array($user_liked_posts) && !empty($current_liked_posts)) {
            $common_liked = array_intersect($current_liked_posts, $user_liked_posts);
            $common_liked_count = count($common_liked);
            if ($common_liked_count > 0) {
                $score += ($common_liked_count * 10); // 10 points per common like
                if (empty($reasons)) {
                    $reasons[] = __('Liked similar content', 'nymia');
                }
            }
        }
        
        // 3. PEOPLE WHO FOLLOW YOU BACK (Higher Priority)
        $user_followers = get_user_meta($user_id, 'nymia_followers', true);
        if (!is_array($user_followers)) {
            $user_followers = array();
        }
        $user_followers = array_map('intval', $user_followers);
        
        if (in_array($current_user_id, $user_followers)) {
            $score += 50; // High priority - they already follow you
            $reasons[] = __('Follows you', 'nymia');
        }
        
        // 4. POPULAR CREATORS (Content-based)
        // Check if user has audio/ebook/social posts
        $user_has_content = false;
        
        // Check for audio
        $user_audio = get_transient('nymia_user_audio_' . $user_id);
        if ($user_audio && is_array($user_audio) && !empty($user_audio)) {
            $score += 15;
            $user_has_content = true;
        }
        
        // Check for ebooks
        $all_ebooks = get_transient('nymia_all_ebooks');
        if ($all_ebooks && is_array($all_ebooks)) {
            foreach ($all_ebooks as $ebook) {
                if (isset($ebook['user_id']) && intval($ebook['user_id']) === $user_id) {
                    $score += 15;
                    $user_has_content = true;
                    break;
                }
            }
        }
        
        // Check for social posts
        $user_posts = new WP_Query(array(
            'post_type' => 'nymia_social_post',
            'author' => $user_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ));
        if ($user_posts->have_posts()) {
            $score += 10;
            $user_has_content = true;
        }
        
        if ($user_has_content && empty($reasons)) {
            $reasons[] = __('Active creator', 'nymia');
        }
        
        // 5. RECENT ACTIVITY BONUS
        // Recent registrations get small bonus
        $registered_time = strtotime($user->user_registered);
        $days_since_registration = (current_time('timestamp') - $registered_time) / DAY_IN_SECONDS;
        if ($days_since_registration < 30) {
            $score += 5; // New users get small boost
        }
        
        // 6. BASE SCORE - Give all users a base score so they appear in suggestions
        // This ensures all users can be suggested, not just those with connections/content
        if ($score === 0) {
            $score = 1; // Base score for all users
        }
        
        // ADD: All users to suggestions (now that we have a base score)
        // We'll sort by score later, so users with connections/content will appear first
        
        // GET: User data
        $user_name = $user->display_name ?: $user->user_login;
        $user_username = '@' . $user->user_login;
            
            // GET: User avatar
            $user_avatar = get_avatar_url($user_id, array('size' => 150));
            $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
            if ($custom_avatar) {
                $user_avatar = esc_url($custom_avatar);
            }
            
            // GET: Cover image or use placeholder
            // NOTE: Suggestion cards display images at 400x250px (1.6:1 ratio)
            // Banner images (1500x500px, 3:1 ratio) will be cropped to fit
            $user_cover_image = get_user_meta($user_id, 'cover_image', true);
            $cover_image_url = $user_cover_image;
            
            // FALLBACK: Use random placeholder image from Unsplash
            // Placeholder size: 400x250px (1.6:1 ratio) - matches suggestion card display size
            if (!$cover_image_url) {
                $placeholder_images = array(
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&h=250&fit=crop',
                );
                $cover_image_url = $placeholder_images[array_rand($placeholder_images)];
            }
            
        // ADD: User to scored suggestions
        $scored_users[] = array(
            'id' => $user_id,
            'image' => esc_url($cover_image_url),
            'avatar' => esc_url($user_avatar),
            'name' => esc_html($user_name),
            'username' => esc_html($user_username),
            'score' => $score,
            'reasons' => $reasons,
        );
    }
    
    // SORT: By score (highest first)
    usort($scored_users, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    // LIMIT: Top 3 suggestions
    $suggestions = array_slice($scored_users, 0, 3);
    
    // REMOVE: Score and reasons from final output (keep data clean)
    foreach ($suggestions as $key => $suggestion) {
        unset($suggestions[$key]['score']);
        unset($suggestions[$key]['reasons']);
    }
    
    // DON'T ADD: Placeholder suggestions - just return what we have
    // If there are no suggestions, the template will handle it gracefully
    return $suggestions;
} // END: nymia_get_real_user_suggestions()

/**
 * GET RATED CREATORS FOR SUGGESTIONS (Public Dashboard)
 * -----------------------------------------------------
 * Returns top-rated creators for non-logged-in users
 * Shows creators sorted by their average rating
 * 
 * @return array Array of creator suggestions with ratings
 */
if (!function_exists('nymia_get_rated_creators_for_suggestions')) {
    function nymia_get_rated_creators_for_suggestions() {
        $creators_data = array();
        
        // Get all creators with audio
        $all_creators = nymia_get_all_creators_with_audio();
        
        if (empty($all_creators)) {
            return array();
        }
        
        // Filter and calculate ratings
        foreach ($all_creators as $creator) {
            $user_id = isset($creator['user_id']) ? intval($creator['user_id']) : 0;
            if (!$user_id) {
                continue;
            }
            
            // FILTER: Only show verified creators (with approved KYC status)
            // Skip regular users/clients - only show verified creators
            if (!nymia_is_creator_verified($user_id)) {
                continue; // Skip this user - not a verified creator
            }
            
            // Only include creators with ratings
            $rating = isset($creator['rating']) ? floatval($creator['rating']) : 0;
            if ($rating <= 0) {
                continue; // Skip creators without ratings
            }
            
            // Get user data
            $user = get_user_by('ID', $user_id);
            if (!$user) {
                continue;
            }
            
            $user_name = $user->display_name ?: $user->user_login;
            $user_username = '@' . $user->user_login;
            
            // Get avatar
            $user_avatar = get_avatar_url($user_id, array('size' => 150));
            $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
            if ($custom_avatar) {
                $user_avatar = esc_url($custom_avatar);
            }
            
            // Get cover image
            $cover_image_url = isset($creator['cover_image']) ? $creator['cover_image'] : '';
            if (empty($cover_image_url)) {
                $cover_image_url = get_user_meta($user_id, 'cover_image', true);
            }
            if (empty($cover_image_url)) {
                // Use placeholder
                $placeholder_images = array(
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&h=250&fit=crop',
                    'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&h=250&fit=crop',
                );
                $cover_image_url = $placeholder_images[array_rand($placeholder_images)];
            }
            
            $creators_data[] = array(
                'id' => $user_id,
                'image' => esc_url($cover_image_url),
                'avatar' => esc_url($user_avatar),
                'name' => esc_html($user_name),
                'username' => esc_html($user_username),
                'rating' => $rating,
                'rating_count' => isset($creator['review_count']) ? intval($creator['review_count']) : 0,
            );
        }
        
        // Sort by rating (highest first)
        usort($creators_data, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
        
        // Return top 3 rated creators
        return array_slice($creators_data, 0, 3);
    }
}

/**
 * CHECK IF FOLLOWING USER
 * ------------------------
 * Checks if one user is following another user
 * 
 * @param int $follower_id User ID of follower
 * @param int $following_id User ID of user being followed
 * @return bool True if following, false otherwise
 */
function nymia_is_following($follower_id, $following_id) {
    if (!$follower_id || !$following_id || $follower_id === $following_id) {
        return false;
    }
    
    // GET: List of users this user is following
    $following_list = get_user_meta($follower_id, 'nymia_following', true);
    
    // CHECK: If list exists and contains the user ID
    if (is_array($following_list)) {
        return in_array($following_id, $following_list);
    }
    
    return false;
} // END: nymia_is_following()

/**
 * TOGGLE FOLLOW STATUS
 * --------------------
 * AJAX handler to follow/unfollow a user
 * 
 * Hooks into: wp_ajax_nymia_toggle_follow
 */
function nymia_toggle_follow_handler() {
    // VERIFY: Nonce for security (before checking login)
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if (!wp_verify_nonce($nonce, 'nymia_follow_action')) {
        wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.'));
        return;
    }
    
    // CHECK: User is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in'));
        return;
    }
    
    // GET: Current user
    $current_user_id = get_current_user_id();
    
    // GET: Target user by username (preferred) or user_id (backward compatibility)
    $target_username = isset($_POST['username']) ? sanitize_text_field(trim($_POST['username'])) : '';
    $target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    $target_user = null;
    
    // Try to get user by username first (preferred method)
    if (!empty($target_username)) {
        $target_user = get_user_by('login', $target_username);
    }
    
    // Fallback: Try to get user by ID (backward compatibility)
    if (!$target_user && $target_user_id > 0) {
        $target_user = get_user_by('ID', $target_user_id);
    }
    
    // VALIDATE: Target user exists
    if (!$target_user) {
        wp_send_json_error(array('message' => 'User not found'));
        return;
    }
    
    $target_user_id = intval($target_user->ID);
    
    // VALIDATE: Cannot follow yourself
    if ($target_user_id === $current_user_id) {
        wp_send_json_error(array('message' => 'Cannot follow yourself'));
        return;
    }
    
    // GET: Current following list
    $following_list = get_user_meta($current_user_id, 'nymia_following', true);
    if (!is_array($following_list)) {
        $following_list = array();
    }
    
    // CHECK: If already following
    $is_following = in_array($target_user_id, $following_list);
    
    if ($is_following) {
        // UNFOLLOW: Remove from list
        $following_list = array_diff($following_list, array($target_user_id));
        $action = 'unfollowed';
    } else {
        // FOLLOW: Add to list
        $following_list[] = $target_user_id;
        $action = 'followed';
    }
    
    // UPDATE: Save new following list
    update_user_meta($current_user_id, 'nymia_following', array_values($following_list));
    
    // UPDATE: Follower count for target user
    $target_followers = get_user_meta($target_user_id, 'nymia_followers', true);
    if (!is_array($target_followers)) {
        $target_followers = array();
    }
    
    if ($is_following) {
        // REMOVE: Follower
        $target_followers = array_diff($target_followers, array($current_user_id));
    } else {
        // ADD: Follower
        if (!in_array($current_user_id, $target_followers)) {
            $target_followers[] = $current_user_id;
        }
        
        // CREATE: Notification for the user being followed
        $current_user = get_userdata($current_user_id);
        $current_user_name = $current_user ? $current_user->display_name : 'Someone';
        $current_user_obj = get_userdata($current_user_id);
        $current_username = $current_user_obj ? $current_user_obj->user_login : '';
        $profile_url = !empty($current_username) ? home_url('/profile/?username=' . urlencode($current_username)) : home_url('/profile/');
        $notification_message = $current_user_name . ' started following you';
        
        // Check if notification function exists before calling
        if (function_exists('nymia_create_notification')) {
            nymia_create_notification($target_user_id, 'follow', $notification_message, $profile_url, $current_user_id);
        }
    }
    
    update_user_meta($target_user_id, 'nymia_followers', array_values($target_followers));
    
    // RESPONSE: Send success with new status
    wp_send_json_success(array(
        'action' => $action,
        'is_following' => !$is_following,
        'followers_count' => count($target_followers),
        'following_count' => count($following_list)
    ));
} // END: nymia_toggle_follow_handler()
add_action('wp_ajax_nymia_toggle_follow', 'nymia_toggle_follow_handler');


// ==========================================
// CHAT SYSTEM
// ==========================================
/**
 * SEND CHAT MESSAGE (AJAX)
 * Handles sending a chat message between users
 */
function nymia_send_chat_message() {
    check_ajax_referer('nymia_chat_action', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to send messages'));
        return;
    }
    
    $current_user_id = get_current_user_id();
    $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
    $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    
    if (!$recipient_id || $recipient_id === $current_user_id) {
        wp_send_json_error(array('message' => 'Invalid recipient'));
        return;
    }
    
    // Check if users follow each other
    if (!nymia_is_following($current_user_id, $recipient_id) && !nymia_is_following($recipient_id, $current_user_id)) {
        wp_send_json_error(array('message' => 'You can only message users you follow or who follow you'));
        return;
    }
    
    if (empty($message)) {
        wp_send_json_error(array('message' => 'Message cannot be empty'));
        return;
    }
    
    // Get current user info
    $sender = get_userdata($current_user_id);
    $sender_name = $sender ? $sender->display_name : 'User';
    $sender_avatar = get_avatar_url($current_user_id, array('size' => 150));
    $custom_avatar = get_user_meta($current_user_id, 'custom_avatar', true);
    if ($custom_avatar) {
        $sender_avatar = $custom_avatar;
    }
    
    // Create message ID
    $message_id = uniqid();
    
    // Create message object
    $chat_message = array(
        'id' => $message_id,
        'sender_id' => $current_user_id,
        'recipient_id' => $recipient_id,
        'message' => $message,
        'time' => current_time('mysql'),
        'read' => false,
        'sender_name' => $sender_name,
        'sender_avatar' => $sender_avatar
    );
    
    // Save to transient (last 100 messages per conversation)
    $conversation_key = min($current_user_id, $recipient_id) . '_' . max($current_user_id, $recipient_id);
    $conversations = get_transient('nymia_chat_messages');
    if (!is_array($conversations)) {
        $conversations = array();
    }
    
    if (!isset($conversations[$conversation_key])) {
        $conversations[$conversation_key] = array();
    }
    
    $conversations[$conversation_key][] = $chat_message;
    
    // Keep only last 100 messages
    if (count($conversations[$conversation_key]) > 100) {
        $conversations[$conversation_key] = array_slice($conversations[$conversation_key], -100);
    }
    
    set_transient('nymia_chat_messages', $conversations, 30 * DAY_IN_SECONDS);
    
    // Send notification to recipient
    if (function_exists('nymia_create_notification')) {
        $current_user_obj = get_userdata($current_user_id);
        $current_username = $current_user_obj ? $current_user_obj->user_login : '';
        $profile_url = !empty($current_username) ? home_url('/profile/?username=' . urlencode($current_username)) : home_url('/profile/');
        nymia_create_notification($recipient_id, 'message', $sender_name . ' sent you a message', $profile_url, $current_user_id);
    }
    
    wp_send_json_success(array('message' => $chat_message));
}
add_action('wp_ajax_nymia_send_chat_message', 'nymia_send_chat_message');

/**
 * GET CHAT MESSAGES (AJAX)
 * Retrieves chat messages for a conversation
 */
function nymia_get_chat_messages() {
    check_ajax_referer('nymia_chat_action', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in'));
        return;
    }
    
    $current_user_id = get_current_user_id();
    $other_user_id = isset($_POST['other_user_id']) ? intval($_POST['other_user_id']) : 0;
    
    if (!$other_user_id) {
        wp_send_json_error(array('message' => 'Invalid user'));
        return;
    }
    
    $conversation_key = min($current_user_id, $other_user_id) . '_' . max($current_user_id, $other_user_id);
    $conversations = get_transient('nymia_chat_messages');
    
    if (!is_array($conversations) || !isset($conversations[$conversation_key])) {
        wp_send_json_success(array('messages' => array()));
        return;
    }
    
    // Mark messages as read
    foreach ($conversations[$conversation_key] as $key => $msg) {
        if ($msg['recipient_id'] == $current_user_id) {
            $conversations[$conversation_key][$key]['read'] = true;
        }
    }
    
    set_transient('nymia_chat_messages', $conversations, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array('messages' => $conversations[$conversation_key]));
}
add_action('wp_ajax_nymia_get_chat_messages', 'nymia_get_chat_messages');

/**
 * Determine if a user is currently online (active within threshold seconds).
 *
 * @param int $user_id
 * @param int $threshold
 * @return bool
 */
function nymia_is_user_online($user_id, $threshold = 120) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return false;
    }

    $last_active = (int) get_user_meta($user_id, 'nymia_chat_last_active', true);
    if (!$last_active) {
        return false;
    }

    return (current_time('timestamp') - $last_active) <= $threshold;
}

/**
 * Record the current user's chat activity timestamp.
 */
function nymia_chat_ping() {
    check_ajax_referer('nymia_chat_action', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in'));
        return;
    }

    update_user_meta(get_current_user_id(), 'nymia_chat_last_active', current_time('timestamp'));
    wp_send_json_success();
}
add_action('wp_ajax_nymia_chat_ping', 'nymia_chat_ping');

/**
 * Retrieve a user's chat status (online/offline).
 */
function nymia_get_user_status() {
    check_ajax_referer('nymia_chat_action', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in'));
        return;
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Invalid user'));
        return;
    }

    $last_active = (int) get_user_meta($user_id, 'nymia_chat_last_active', true);
    $is_online = nymia_is_user_online($user_id);
    $last_active_human = $last_active ? human_time_diff($last_active, current_time('timestamp')) : '';

    wp_send_json_success(array(
        'is_online' => $is_online,
        'last_active' => $last_active,
        'last_active_human' => $last_active_human,
    ));
}
add_action('wp_ajax_nymia_get_user_status', 'nymia_get_user_status');

/**
 * GET CHAT CONVERSATIONS (AJAX)
 * Retrieves list of conversations for current user
 */
function nymia_get_chat_conversations() {
    check_ajax_referer('nymia_chat_action', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in'));
        return;
    }
    
    $current_user_id = get_current_user_id();
    $conversations = get_transient('nymia_chat_messages');
    $user_conversations = array();
    $now = current_time('timestamp');
    
    if (is_array($conversations)) {
        foreach ($conversations as $key => $messages) {
            if (!empty($messages)) {
                $last_message = end($messages);
                
                // Check if current user is part of this conversation
                if ($last_message['sender_id'] == $current_user_id || $last_message['recipient_id'] == $current_user_id) {
                    $other_user_id = ($last_message['sender_id'] == $current_user_id) ? $last_message['recipient_id'] : $last_message['sender_id'];
                    $other_user = get_userdata($other_user_id);
                    
                    if ($other_user) {
                        $user_avatar = get_avatar_url($other_user_id, array('size' => 150));
                        $custom_avatar = get_user_meta($other_user_id, 'custom_avatar', true);
                        if ($custom_avatar) {
                            $user_avatar = $custom_avatar;
                        }
                        
                        // Count unread messages
                        $last_active = (int) get_user_meta($other_user_id, 'nymia_chat_last_active', true);
                        $unread_count = 0;
                        foreach ($messages as $msg) {
                            if ($msg['recipient_id'] == $current_user_id && !$msg['read']) {
                                $unread_count++;
                            }
                        }
                        
                        $user_conversations[] = array(
                            'user_id' => $other_user_id,
                            'user_name' => $other_user->display_name,
                            'user_avatar' => $user_avatar,
                            'last_message' => $last_message['message'],
                            'last_message_time' => $last_message['time'],
                            'unread_count' => $unread_count,
                            'is_online' => nymia_is_user_online($other_user_id),
                            'last_active' => $last_active,
                            'last_active_human' => $last_active ? human_time_diff($last_active, $now) : '',
                        );
                    }
                }
            }
        }
        
        // Sort by last message time
        usort($user_conversations, function($a, $b) {
            return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
        });
    }
    
    wp_send_json_success(array('conversations' => $user_conversations));
}
add_action('wp_ajax_nymia_get_chat_conversations', 'nymia_get_chat_conversations');

/**
 * Process creator request helper.
 *
 * @param int   $user_id
 * @param array $kyc_data
 * @return true|WP_Error
 */
function nymia_process_creator_request($user_id, $kyc_data = array()) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return new WP_Error('invalid_user', __('Invalid user.', 'nymia'));
    }

    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('missing_user', __('User not found.', 'nymia'));
    }

    // Check if user is already approved as creator (based on KYC status, not just capability)
    $kyc_status = get_user_meta($user_id, 'nymia_creator_kyc_status', true);
    if ($kyc_status === 'approved') {
        return new WP_Error('already_creator', __('You are already a creator!', 'nymia'));
    }

    $existing_request = get_user_meta($user_id, 'nymia_creator_request_pending', true);
    if ($existing_request === 'yes') {
        return new WP_Error('already_pending', __('You already have a pending creator request.', 'nymia'));
    }

    if (!empty($kyc_data)) {
        update_user_meta($user_id, 'nymia_creator_kyc_data', $kyc_data);
        update_user_meta($user_id, 'nymia_creator_kyc_status', 'submitted');
        if (!empty($kyc_data['photo_capture'])) {
            update_user_meta($user_id, 'nymia_creator_kyc_photo', esc_url_raw($kyc_data['photo_capture']));
        }
    }

    update_user_meta($user_id, 'nymia_creator_request_pending', 'yes');
    update_user_meta($user_id, 'nymia_creator_request_date', current_time('mysql'));

    $pending_requests = get_option('nymia_creator_pending_requests', array());
    $kyc_document = !empty($kyc_data['document']) ? $kyc_data['document'] : '';
    $kyc_photo    = !empty($kyc_data['photo_capture']) ? $kyc_data['photo_capture'] : '';
    $pending_requests[$user_id] = array(
        'user_id'      => $user_id,
        'name'         => $user->display_name ?: $user->user_login,
        'submitted_at' => current_time('mysql'),
        'kyc_status'   => 'submitted',
        'kyc_photo'    => $kyc_photo,
        'kyc_document' => $kyc_document,
        'kyc_first_name' => !empty($kyc_data['first_name']) ? $kyc_data['first_name'] : '',
        'kyc_last_name'  => !empty($kyc_data['last_name']) ? $kyc_data['last_name'] : '',
        'kyc_dob'       => !empty($kyc_data['dob']) ? $kyc_data['dob'] : '',
        'kyc_phone'     => !empty($kyc_data['phone']) ? $kyc_data['phone'] : '',
        'kyc_id_type'  => !empty($kyc_data['id_type']) ? $kyc_data['id_type'] : '',
        'kyc_id_number'=> !empty($kyc_data['id_number']) ? $kyc_data['id_number'] : '',
        'kyc_street'   => !empty($kyc_data['street']) ? $kyc_data['street'] : '',
        'kyc_city'     => !empty($kyc_data['city']) ? $kyc_data['city'] : '',
        'kyc_country'  => !empty($kyc_data['country']) ? $kyc_data['country'] : '',
        'kyc_zip_code'=> !empty($kyc_data['zip_code']) ? $kyc_data['zip_code'] : '',
    );
    update_option('nymia_creator_pending_requests', $pending_requests);

    $admins = get_users(array('role' => 'administrator'));
    foreach ($admins as $admin) {
        if (function_exists('nymia_create_notification')) {
            $profile_url = home_url('/profile/?user_id=' . $user_id);
            nymia_create_notification(
                $admin->ID,
                'creator_request',
                sprintf(__('%s submitted a creator application.', 'nymia'), $user->display_name ?: $user->user_login),
                $profile_url,
                $user_id
            );
        }
    }

    // SEND: Admin notification email about new creator registration
    nymia_send_admin_new_creator_notification($user_id, $user->user_login, $user->user_email, $kyc_data);
    
    // SEND: Welcome email to new creator
    nymia_send_new_creator_welcome_email($user_id, $user->user_login, $user->user_email);

    return true;
}

/**
 * Submit creator KYC (AJAX)
 */
function nymia_submit_creator_kyc() {
    check_ajax_referer('nymia_creator_kyc', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to submit verification.', 'nymia')));
        return;
    }
    
    $current_user_id = get_current_user_id();

    $first_name = isset($_POST['kyc_first_name']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_first_name']))) : '';
    $last_name  = isset($_POST['kyc_last_name']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_last_name']))) : '';
    $dob        = isset($_POST['kyc_dob']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_dob']))) : '';
    $phone      = isset($_POST['kyc_phone']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_phone']))) : '';
    $id_type    = isset($_POST['kyc_id_type']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_id_type']))) : '';
    $id_number  = isset($_POST['kyc_id_number']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_id_number']))) : '';
    $street     = isset($_POST['kyc_street']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_street']))) : '';
    $city       = isset($_POST['kyc_city']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_city']))) : '';
    $country    = isset($_POST['kyc_country']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_country']))) : '';
    $zip_code   = isset($_POST['kyc_zip_code']) ? trim(sanitize_text_field(wp_unslash($_POST['kyc_zip_code']))) : '';

    // Check each field individually to provide better error messages
    $missing_fields = array();
    if (empty($first_name)) $missing_fields[] = __('First Name', 'nymia');
    if (empty($last_name)) $missing_fields[] = __('Last Name', 'nymia');
    if (empty($dob)) $missing_fields[] = __('Date of Birth', 'nymia');
    if (empty($phone)) $missing_fields[] = __('Phone Number', 'nymia');
    if (empty($id_type)) $missing_fields[] = __('ID Type', 'nymia');
    if (empty($id_number)) $missing_fields[] = __('ID Number', 'nymia');
    if (empty($street)) $missing_fields[] = __('Street', 'nymia');
    if (empty($city)) $missing_fields[] = __('City', 'nymia');
    if (empty($country)) $missing_fields[] = __('Country', 'nymia');
    if (empty($zip_code)) $missing_fields[] = __('ZIP Code', 'nymia');

    if (!empty($missing_fields)) {
        $error_message = __('Please complete all required KYC fields.', 'nymia');
        if (count($missing_fields) <= 3) {
            $error_message .= ' ' . sprintf(__('Missing: %s', 'nymia'), implode(', ', $missing_fields));
        }
        wp_send_json_error(array('message' => $error_message));
        return;
    }
    
    // VALIDATE: ID Number must contain only numbers
    if (!empty($id_number) && !preg_match('/^[0-9]+$/', $id_number)) {
        wp_send_json_error(array('message' => __('ID Number must contain only numbers.', 'nymia')));
        return;
    }
    
    $kyc_document_url = '';
    if (!empty($_FILES['kyc_document']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $upload = wp_handle_upload($_FILES['kyc_document'], array('test_form' => false));
        if (isset($upload['error'])) {
            wp_send_json_error(array('message' => sprintf(__('Document upload failed: %s', 'nymia'), $upload['error'])));
        return;
    }
        $kyc_document_url = esc_url_raw($upload['url']);
    }

    $photo_capture_url = '';
    if (!empty($_FILES['kyc_photo_capture']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $photo_upload = wp_handle_upload($_FILES['kyc_photo_capture'], array('test_form' => false));
        if (isset($photo_upload['error'])) {
            wp_send_json_error(array('message' => sprintf(__('Photo capture upload failed: %s', 'nymia'), $photo_upload['error'])));
            return;
        }
        $photo_capture_url = esc_url_raw($photo_upload['url']);
    }

    if (empty($kyc_document_url) && empty($photo_capture_url)) {
        wp_send_json_error(array('message' => __('Please upload a document or capture a photo for verification.', 'nymia')));
        return;
    }

    $kyc_data = array(
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'dob'        => $dob,
        'phone'      => $phone,
        'id_type'    => $id_type,
        'id_number'  => $id_number,
        'street'     => $street,
        'city'       => $city,
        'country'    => $country,
        'zip_code'   => $zip_code,
        'document'   => $kyc_document_url,
        'photo_capture' => $photo_capture_url,
        'submitted'  => current_time('mysql'),
    );

    $result = nymia_process_creator_request($current_user_id, $kyc_data);
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }
    
    wp_send_json_success(array(
        'message' => __('Thank you for applying to become a Creator. Your request is now under review. We will notify you once a decision has been made.', 'nymia')
    ));
}
add_action('wp_ajax_nymia_submit_creator_kyc', 'nymia_submit_creator_kyc');

/**
 * Admin review of creator KYC requests (AJAX).
 */
function nymia_admin_review_kyc() {
    check_ajax_referer('nymia_admin_review_kyc', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'nymia')));
        return;
    }

    $user_id  = isset($_POST['userId']) ? absint($_POST['userId']) : 0;
    $decision = isset($_POST['decision']) ? sanitize_text_field(wp_unslash($_POST['decision'])) : '';
    $valid_decisions = array('approve', 'reject');

    if (!$user_id || !in_array($decision, $valid_decisions, true)) {
        wp_send_json_error(array('message' => __('Invalid request parameters.', 'nymia')));
        return;
    }

    $pending_requests = get_option('nymia_creator_pending_requests', array());
    if (!isset($pending_requests[$user_id])) {
        wp_send_json_error(array('message' => __('This request has already been processed.', 'nymia')));
        return;
    }

    $user = get_userdata($user_id);
    if (!$user) {
        unset($pending_requests[$user_id]);
        update_option('nymia_creator_pending_requests', $pending_requests);
        wp_send_json_error(array('message' => __('User no longer exists.', 'nymia')));
        return;
    }

    $status_label = '';

    if ($decision === 'approve') {
        if (!user_can($user_id, 'edit_posts')) {
            $user->set_role('author');
        }
        update_user_meta($user_id, 'nymia_creator_request_pending', 'approved');
        update_user_meta($user_id, 'nymia_creator_kyc_status', 'approved');
        update_user_meta($user_id, 'nymia_creator_request_approved_at', current_time('mysql'));
        $status_label = __('Approved', 'nymia');
    } else {
        update_user_meta($user_id, 'nymia_creator_request_pending', 'rejected');
        update_user_meta($user_id, 'nymia_creator_kyc_status', 'rejected');
        update_user_meta($user_id, 'nymia_creator_request_rejected_at', current_time('mysql'));
        $status_label = __('Rejected', 'nymia');
    }

    unset($pending_requests[$user_id]);
    update_option('nymia_creator_pending_requests', $pending_requests);

    wp_send_json_success(array(
        'message'       => __('KYC decision recorded.', 'nymia'),
        'status'        => $decision,
        'statusLabel'   => $status_label,
        'pendingCount'  => count($pending_requests),
    ));
}
add_action('wp_ajax_nymia_admin_review_kyc', 'nymia_admin_review_kyc');

/**
 * Update a user's account status (active, suspended, banned) via AJAX.
 */
function nymia_admin_update_user_status() {
    check_ajax_referer('nymia_admin_user_actions', 'nonce');

    if (!current_user_can('promote_users')) {
        wp_send_json_error(array('message' => __('You do not have permission to modify users.', 'nymia')));
    }

    $user_id = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $status  = isset($_POST['status']) ? sanitize_key($_POST['status']) : '';
    $note    = isset($_POST['note']) ? sanitize_textarea_field(wp_unslash($_POST['note'])) : '';

    $allowed_statuses = array('active', 'suspended', 'banned');

    if (!$user_id || !in_array($status, $allowed_statuses, true)) {
        wp_send_json_error(array('message' => __('Invalid user or status.', 'nymia')));
    }

    if ($user_id === get_current_user_id()) {
        wp_send_json_error(array('message' => __('You cannot change your own status.', 'nymia')));
    }

    $user = get_userdata($user_id);
    if (!$user) {
        wp_send_json_error(array('message' => __('User not found.', 'nymia')));
    }

    if (user_can($user_id, 'manage_options')) {
        wp_send_json_error(array('message' => __('You cannot modify another administrator.', 'nymia')));
    }

    $expires_ts = null;
    if ($status === 'suspended') {
        $duration_value = isset($_POST['durationValue']) ? intval($_POST['durationValue']) : 0;
        $duration_unit  = isset($_POST['durationUnit']) ? sanitize_key($_POST['durationUnit']) : '';

        if ($duration_value > 0) {
            $interval_spec = '';
            switch ($duration_unit) {
                case 'weeks':
                    $interval_spec = 'P' . $duration_value . 'W';
                    break;
                case 'months':
                    $interval_spec = 'P' . $duration_value . 'M';
                    break;
                case 'years':
                    $interval_spec = 'P' . $duration_value . 'Y';
                    break;
                case 'days':
                default:
                    $interval_spec = 'P' . $duration_value . 'D';
                    break;
            }

            try {
                $timezone = wp_timezone();
                $date = new DateTime('now', $timezone);
                $date->add(new DateInterval($interval_spec));
                $expires_ts = $date->getTimestamp();
            } catch (Exception $e) {
                $expires_ts = null;
            }
        }
    }

    nymia_set_user_account_status($user_id, $status, $expires_ts);
    update_user_meta($user_id, 'nymia_last_status_note', $note);
    update_user_meta($user_id, 'nymia_last_status_changed_by', get_current_user_id());
    update_user_meta($user_id, 'nymia_last_status_changed_at', current_time('mysql'));

    nymia_send_account_status_email($user, $status, $note, $expires_ts);

    wp_send_json_success(array(
        'status'      => $status,
        'statusLabel' => nymia_get_user_account_status_label($status),
        'expiresAt'   => $expires_ts ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $expires_ts) : '',
        'expiresTimestamp' => $expires_ts ? (int) $expires_ts : 0,
    ));
}
add_action('wp_ajax_nymia_admin_update_user_status', 'nymia_admin_update_user_status');

/**
 * Delete a user account through the dashboard table.
 */
function nymia_admin_delete_user_account() {
    check_ajax_referer('nymia_admin_user_actions', 'nonce');

    if (!current_user_can('delete_users')) {
        wp_send_json_error(array('message' => __('You do not have permission to delete users.', 'nymia')));
    }

    $user_id = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $note    = isset($_POST['note']) ? sanitize_textarea_field(wp_unslash($_POST['note'])) : '';
    if (!$user_id) {
        wp_send_json_error(array('message' => __('Invalid user.', 'nymia')));
    }

    if ($user_id === get_current_user_id()) {
        wp_send_json_error(array('message' => __('You cannot delete your own account.', 'nymia')));
    }

    if (user_can($user_id, 'manage_options')) {
        wp_send_json_error(array('message' => __('You cannot delete another administrator.', 'nymia')));
    }

    $user = get_userdata($user_id);
    if (!$user) {
        wp_send_json_error(array('message' => __('User not found.', 'nymia')));
    }

    nymia_send_account_status_email($user, 'deleted', $note, null);

    if (!function_exists('wp_delete_user')) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
    }

    $deleted = wp_delete_user($user_id);
    if (!$deleted) {
        wp_send_json_error(array('message' => __('Unable to delete user.', 'nymia')));
    }

    wp_send_json_success(array('deleted' => true));
}
add_action('wp_ajax_nymia_admin_delete_user', 'nymia_admin_delete_user_account');

/**
 * Delete a product (audio or ebook) from the dashboard (AJAX).
 */
function nymia_admin_delete_product() {
    check_ajax_referer('nymia_admin_delete_product', 'nonce');

    if (!current_user_can('delete_posts') && !current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to delete products.', 'nymia')));
    }

    $product_type  = isset($_POST['productType']) ? sanitize_key(wp_unslash($_POST['productType'])) : '';
    $product_id    = isset($_POST['productId']) ? (int) $_POST['productId'] : 0;
    $product_user  = isset($_POST['productUser']) ? (int) $_POST['productUser'] : 0;
    $product_title = isset($_POST['productTitle']) ? sanitize_text_field(wp_unslash($_POST['productTitle'])) : '';
    $note          = isset($_POST['productNote']) ? wp_strip_all_tags(wp_unslash($_POST['productNote'])) : '';

    if (!$product_type || !$product_id) {
        wp_send_json_error(array('message' => __('Invalid product reference.', 'nymia')));
    }

    $deleted = false;
    $removed_item = null;
    $token = '';

    if ($product_type === 'audio') {
        $all_audio = get_transient('nymia_all_audio');
        if (is_array($all_audio)) {
            $filtered_audio = array();
            foreach ($all_audio as $audio_item) {
                $item_id = isset($audio_item['id']) ? (int) $audio_item['id'] : 0;
                if ($item_id === $product_id) {
                    if (!$removed_item) {
                        $removed_item = $audio_item;
                    }
                    $deleted = true;
                    continue;
                }
                $filtered_audio[] = $audio_item;
            }
            set_transient('nymia_all_audio', $filtered_audio, 30 * DAY_IN_SECONDS);
        }

        if ($product_user) {
            $user_audio = get_transient('nymia_user_audio_' . $product_user);
            if (is_array($user_audio)) {
                $filtered_user_audio = array();
                foreach ($user_audio as $audio_item) {
                    $item_id = isset($audio_item['id']) ? (int) $audio_item['id'] : 0;
                    if ($item_id === $product_id) {
                        if (!$removed_item) {
                            $removed_item = $audio_item;
                        }
                        $deleted = true;
                        continue;
                    }
                    $filtered_user_audio[] = $audio_item;
                }
                set_transient('nymia_user_audio_' . $product_user, $filtered_user_audio, 30 * DAY_IN_SECONDS);
            }
        }
    } elseif ($product_type === 'ebook') {
        $all_ebooks = get_transient('nymia_all_ebooks');
        if (is_array($all_ebooks)) {
            $filtered_ebooks = array();
            foreach ($all_ebooks as $ebook_item) {
                $item_id = isset($ebook_item['id']) ? (int) $ebook_item['id'] : 0;
                if ($item_id === $product_id) {
                    if (!$removed_item) {
                        $removed_item = $ebook_item;
                    }
                    $deleted = true;
                    continue;
                }
                $filtered_ebooks[] = $ebook_item;
            }
            set_transient('nymia_all_ebooks', $filtered_ebooks, 30 * DAY_IN_SECONDS);
        }

        if ($product_user) {
            $user_ebooks = get_transient('nymia_user_ebook_' . $product_user);
            if (is_array($user_ebooks)) {
                $filtered_user_ebooks = array();
                foreach ($user_ebooks as $ebook_item) {
                    $item_id = isset($ebook_item['id']) ? (int) $ebook_item['id'] : 0;
                    if ($item_id === $product_id) {
                        if (!$removed_item) {
                            $removed_item = $ebook_item;
                        }
                        $deleted = true;
                        continue;
                    }
                    $filtered_user_ebooks[] = $ebook_item;
                }
                set_transient('nymia_user_ebook_' . $product_user, $filtered_user_ebooks, 30 * DAY_IN_SECONDS);
            }
        }
    } else {
        wp_send_json_error(array('message' => __('Unsupported product type.', 'nymia')));
    }

    if (!$deleted) {
        wp_send_json_error(array('message' => __('Product could not be located.', 'nymia')));
    }

    if ($product_user) {
        $creator = get_userdata($product_user);
        if ($creator && $creator->user_email) {
            $blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $subject = sprintf(__('Your product was removed from %s', 'nymia'), $blog_name);

            $message_lines = array();
            $message_lines[] = sprintf(__('Hello %s,', 'nymia'), $creator->display_name ? $creator->display_name : $creator->user_login);

            if ($product_title) {
                $message_lines[] = sprintf(__('We removed your product: %s', 'nymia'), $product_title);
            } else {
                $message_lines[] = __('We removed one of your products.', 'nymia');
            }

            if ($note) {
                $message_lines[] = '';
                $message_lines[] = __('Reason provided by the team:', 'nymia');
                $message_lines[] = $note;
            }

            $message_lines[] = '';
            $message_lines[] = sprintf(__('If you have any questions, reply to this email or contact %s support.', 'nymia'), $blog_name);
            $message_lines[] = '';
            $message_lines[] = sprintf(__('— %s Team', 'nymia'), $blog_name);

            $headers = array('Content-Type: text/plain; charset=UTF-8');
            wp_mail($creator->user_email, $subject, implode("\n", $message_lines), $headers);
        }
    }

    if ($removed_item && empty($product_title) && isset($removed_item['title'])) {
        $product_title = sanitize_text_field($removed_item['title']);
    }

    if ($removed_item) {
        $queue = get_option('nymia_deleted_products_queue', array());
        if (!is_array($queue)) {
            $queue = array();
        }

        $token = uniqid('prod_', true);

        $queue_entry = array(
            'token'      => $token,
            'type'       => $product_type,
            'product_id' => $product_id,
            'user_id'    => $product_user,
            'data'       => $removed_item,
            'title'      => $product_title,
            'note'       => $note,
            'deleted_at' => current_time('timestamp'),
        );

        array_unshift($queue, $queue_entry);
        if (count($queue) > 20) {
            $queue = array_slice($queue, 0, 20);
        }
        update_option('nymia_deleted_products_queue', $queue);
    }

    if (!function_exists('nymia_get_dashboard_metrics')) {
        require_once get_template_directory() . '/admin/dashboard-settings.php';
    }

    $metrics = nymia_get_dashboard_metrics();
    $products = isset($metrics['products']) ? $metrics['products'] : array();

    wp_send_json_success(array(
        'products' => $products,
        'deleted'  => isset($metrics['deleted']) ? $metrics['deleted'] : array(),
        'deleted_product' => $token ? array(
            'token' => $token,
            'title' => $product_title,
            'type'  => $product_type,
        ) : null,
    ));
}
add_action('wp_ajax_nymia_admin_delete_product', 'nymia_admin_delete_product');

/**
 * Restore a previously deleted product.
 */
function nymia_admin_restore_product() {
    check_ajax_referer('nymia_admin_restore_product', 'nonce');

    if (!current_user_can('delete_posts') && !current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to restore products.', 'nymia')));
    }

    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
    if (!$token) {
        wp_send_json_error(array('message' => __('Missing restore token.', 'nymia')));
    }

    $queue = get_option('nymia_deleted_products_queue', array());
    if (!is_array($queue) || empty($queue)) {
        wp_send_json_error(array('message' => __('No products available to restore.', 'nymia')));
    }

    $entry = null;
    foreach ($queue as $index => $item) {
        if (isset($item['token']) && $item['token'] === $token) {
            $entry = $item;
            unset($queue[$index]);
            break;
        }
    }

    if (!$entry) {
        wp_send_json_error(array('message' => __('Restore token has expired.', 'nymia')));
    }

    $queue = array_values($queue);
    update_option('nymia_deleted_products_queue', $queue);

    $product_type = isset($entry['type']) ? $entry['type'] : '';
    $product_id   = isset($entry['product_id']) ? (int) $entry['product_id'] : 0;
    $product_user = isset($entry['user_id']) ? (int) $entry['user_id'] : 0;
    $product_data = isset($entry['data']) ? $entry['data'] : array();
    $product_title = isset($entry['title']) ? $entry['title'] : '';

    if (!$product_type || empty($product_data)) {
        wp_send_json_error(array('message' => __('Unable to restore product data.', 'nymia')));
    }

    if (empty($product_title) && isset($product_data['title'])) {
        $product_title = sanitize_text_field($product_data['title']);
    }

    if ($product_type === 'audio') {
        $all_audio = get_transient('nymia_all_audio');
        $all_audio = is_array($all_audio) ? $all_audio : array();

        $entry_id = isset($product_data['id']) ? (int) $product_data['id'] : 0;
        $all_audio = array_values(array_filter($all_audio, static function ($item) use ($entry_id) {
            $item_id = isset($item['id']) ? (int) $item['id'] : 0;
            return $item_id !== $entry_id;
        }));
        array_unshift($all_audio, $product_data);
        $all_audio = array_slice($all_audio, 0, 100);
        set_transient('nymia_all_audio', $all_audio, 30 * DAY_IN_SECONDS);

        if ($product_user) {
            $user_audio = get_transient('nymia_user_audio_' . $product_user);
            $user_audio = is_array($user_audio) ? $user_audio : array();
            $user_audio = array_values(array_filter($user_audio, static function ($item) use ($entry_id) {
                $item_id = isset($item['id']) ? (int) $item['id'] : 0;
                return $item_id !== $entry_id;
            }));
            array_unshift($user_audio, $product_data);
            $user_audio = array_slice($user_audio, 0, 10);
            set_transient('nymia_user_audio_' . $product_user, $user_audio, 30 * DAY_IN_SECONDS);
        }
    } elseif ($product_type === 'ebook') {
        $all_ebooks = get_transient('nymia_all_ebooks');
        $all_ebooks = is_array($all_ebooks) ? $all_ebooks : array();

        $entry_id = isset($product_data['id']) ? (int) $product_data['id'] : 0;
        $all_ebooks = array_values(array_filter($all_ebooks, static function ($item) use ($entry_id) {
            $item_id = isset($item['id']) ? (int) $item['id'] : 0;
            return $item_id !== $entry_id;
        }));
        array_unshift($all_ebooks, $product_data);
        $all_ebooks = array_slice($all_ebooks, 0, 100);
        set_transient('nymia_all_ebooks', $all_ebooks, 30 * DAY_IN_SECONDS);

        if ($product_user) {
            $user_ebooks = get_transient('nymia_user_ebook_' . $product_user);
            $user_ebooks = is_array($user_ebooks) ? $user_ebooks : array();
            $user_ebooks = array_values(array_filter($user_ebooks, static function ($item) use ($entry_id) {
                $item_id = isset($item['id']) ? (int) $item['id'] : 0;
                return $item_id !== $entry_id;
            }));
            array_unshift($user_ebooks, $product_data);
            $user_ebooks = array_slice($user_ebooks, 0, 20);
            set_transient('nymia_user_ebook_' . $product_user, $user_ebooks, 30 * DAY_IN_SECONDS);
        }
    } else {
        wp_send_json_error(array('message' => __('Unsupported product type.', 'nymia')));
    }

    if ($product_user) {
        $creator = get_userdata($product_user);
        if ($creator && $creator->user_email) {
            $blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $subject = sprintf(__('Your product was restored on %s', 'nymia'), $blog_name);
            $message_lines = array();
            $message_lines[] = sprintf(__('Hello %s,', 'nymia'), $creator->display_name ? $creator->display_name : $creator->user_login);
            if ($product_title) {
                $message_lines[] = sprintf(__('We restored your product: %s', 'nymia'), $product_title);
            } else {
                $message_lines[] = __('We restored one of your products.', 'nymia');
            }
            $message_lines[] = '';
            $message_lines[] = __('The item is now available again in the marketplace.', 'nymia');
            $message_lines[] = '';
            $message_lines[] = sprintf(__('— %s Team', 'nymia'), $blog_name);

            $headers = array('Content-Type: text/plain; charset=UTF-8');
            wp_mail($creator->user_email, $subject, implode("\n", $message_lines), $headers);
        }
    }

    if (!function_exists('nymia_get_dashboard_metrics')) {
        require_once get_template_directory() . '/admin/dashboard-settings.php';
    }

    $metrics = nymia_get_dashboard_metrics();
    $products = isset($metrics['products']) ? $metrics['products'] : array();

    wp_send_json_success(array(
        'products' => $products,
        'deleted'  => isset($metrics['deleted']) ? $metrics['deleted'] : array(),
        'restored_product' => array(
            'title' => $product_title,
            'type'  => $product_type,
        ),
    ));
}
add_action('wp_ajax_nymia_admin_restore_product', 'nymia_admin_restore_product');

/**
 * Search users for the admin dashboard (AJAX).
 */
function nymia_admin_search_users() {
    check_ajax_referer('nymia_admin_user_search', 'nonce');

    if (!current_user_can('list_users')) {
        wp_send_json_error(array('message' => __('You do not have permission to search users.', 'nymia')));
    }

    $term = isset($_POST['term']) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
    $role_filter = isset($_POST['role']) ? sanitize_key($_POST['role']) : '';

    $args = array(
        'number' => 25,
        'orderby' => 'registered',
        'order' => 'DESC',
    );

    if ($term !== '') {
        $args['search'] = '*' . esc_sql($term) . '*';
        $args['search_columns'] = array('user_login', 'user_email', 'display_name');
    }

    if ($role_filter === 'creator') {
        $args['role__in'] = array('administrator', 'author');
    } elseif ($role_filter === 'buyer') {
        $args['role__in'] = array('subscriber');
    }

    $users = get_users($args);

    $current_admin_id = get_current_user_id();
    $can_promote = current_user_can('promote_users');
    $can_delete_users = current_user_can('delete_users');
    $can_edit_users = current_user_can('edit_users');

    $data = array();
    foreach ($users as $wp_user) {
        $entry = nymia_format_admin_user_entry($wp_user, $current_admin_id, $can_promote, $can_delete_users, $can_edit_users);
        if (!empty($entry)) {
            $data[] = $entry;
        }
    }

    wp_send_json_success(array(
        'users' => $data,
    ));
}
add_action('wp_ajax_nymia_admin_search_users', 'nymia_admin_search_users');

// ==========================================
// INCLUDE ADMIN FILES
// ==========================================
require_once get_template_directory() . '/admin/admin-menu.php';
require_once get_template_directory() . '/admin/email-templates-settings.php';
require_once get_template_directory() . '/admin/dashboard-settings.php';
require_once get_template_directory() . '/admin/general-settings.php';
require_once get_template_directory() . '/admin/social-login-settings.php';
require_once get_template_directory() . '/admin/zegocloud-settings.php';
require_once get_template_directory() . '/admin/stream-settings.php';
require_once get_template_directory() . '/admin/stripe-settings.php';
require_once get_template_directory() . '/admin/payout-requests.php';
require_once get_template_directory() . '/admin/email-templates-settings.php';
require_once get_template_directory() . '/admin/footer-menu-settings.php';
require_once get_template_directory() . '/admin/user-management.php';

// ==========================================
// DELETE ACCOUNT AJAX HANDLER
// ==========================================
/**
 * Delete user account
 * Permanently deletes the user account and all associated data
 */
function nymia_delete_account() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('You must be logged in to delete your account.', 'nymia')), 401);
        return;
    }
    
    // Verify nonce
    check_ajax_referer('nymia_delete_account', 'nonce');
    
    $user_id = get_current_user_id();
    
    // Prevent admins from deleting their own account (optional safety check)
    $user = wp_get_current_user();
    if (in_array('administrator', (array) $user->roles)) {
        wp_send_json_error(array('message' => __('Administrator accounts cannot be deleted through this interface.', 'nymia')));
        return;
    }
    
    // Delete user meta
    $meta_keys = array(
        'custom_avatar',
        'cover_image',
        'phone',
        'location',
        'bio',
        'description',
        'facebook',
        'twitter',
        'linkedin',
        'github',
        'nymia_following',
        'nymia_followers',
        'nymia_creator_kyc_status',
        'account_type',
        'stripe_account_no',
        'swift_bic',
    );
    
    foreach ($meta_keys as $key) {
        delete_user_meta($user_id, $key);
    }
    
    // Delete user's transients
    delete_transient('nymia_user_audio_' . $user_id);
    
    // Delete user's posts (social posts, etc.)
    $user_posts = get_posts(array(
        'author' => $user_id,
        'post_type' => 'any',
        'posts_per_page' => -1,
        'post_status' => 'any',
    ));
    
    foreach ($user_posts as $post) {
        wp_delete_post($post->ID, true);
    }
    
    // Logout the user before deletion
    wp_logout();
    
    // Delete the user
    require_once(ABSPATH . 'wp-admin/includes/user.php');
    $deleted = wp_delete_user($user_id);
    
    if ($deleted) {
        wp_send_json_success(array('message' => __('Your account has been deleted successfully.', 'nymia')));
    } else {
        wp_send_json_error(array('message' => __('Failed to delete account. Please contact support.', 'nymia')));
    }
}
add_action('wp_ajax_nymia_delete_account', 'nymia_delete_account');

// ==========================================
// INCLUDE AUTHENTICATION FILES
// ==========================================
require_once get_template_directory() . '/auth/auth-functions.php';

// ==========================================
// SOCIAL LOGIN / OAUTH HANDLERS
// ==========================================

/**
 * Handle OAuth requests (Google, Facebook, Apple)
 */
function nymia_handle_oauth() {
    if (!isset($_GET['nymia_auth'])) {
        return;
    }
    
    $provider = sanitize_text_field($_GET['nymia_auth']);
    
    switch ($provider) {
        case 'google':
            nymia_google_oauth_init();
            break;
        case 'google_callback':
            nymia_google_oauth_callback();
            break;
        case 'facebook':
            nymia_facebook_oauth_init();
            break;
        case 'facebook_callback':
            nymia_facebook_oauth_callback();
            break;
        case 'apple':
            nymia_apple_oauth_init();
            break;
        case 'apple_callback':
            nymia_apple_oauth_callback();
            break;
    }
}
add_action('init', 'nymia_handle_oauth');

/**
 * Initialize Google OAuth flow
 */
function nymia_google_oauth_init() {
    $client_id = get_option('nymia_google_client_id');
    $redirect_uri = home_url('/?nymia_auth=google_callback');
    
    if (empty($client_id)) {
        wp_redirect(home_url('/?login=failed&error=google_not_configured'));
        exit;
    }
    
    $params = array(
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    );
    
    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    wp_redirect($auth_url);
    exit;
}

/**
 * Handle Google OAuth callback
 */
function nymia_google_oauth_callback() {
    if (!isset($_GET['code'])) {
        wp_redirect(home_url('/?login=failed&error=google_auth_failed'));
        exit;
    }
    
    $code = sanitize_text_field($_GET['code']);
    $client_id = get_option('nymia_google_client_id');
    $client_secret = get_option('nymia_google_client_secret');
    $redirect_uri = home_url('/?nymia_auth=google_callback');
    
    // Exchange code for access token
    $token_response = wp_remote_post('https://oauth2.googleapis.com/token', array(
        'body' => array(
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        )
    ));
    
    if (is_wp_error($token_response)) {
        wp_redirect(home_url('/?login=failed&error=google_token_error'));
        exit;
    }
    
    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
    
    if (!isset($token_data['access_token'])) {
        wp_redirect(home_url('/?login=failed&error=google_token_missing'));
        exit;
    }
    
    // Get user info
    $user_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token_data['access_token']
        )
    ));
    
    if (is_wp_error($user_response)) {
        wp_redirect(home_url('/?login=failed&error=google_user_error'));
        exit;
    }
    
    $user_data = json_decode(wp_remote_retrieve_body($user_response), true);
    
    if (!isset($user_data['email'])) {
        wp_redirect(home_url('/?login=failed&error=google_email_missing'));
        exit;
    }
    
    // Create or login user
    nymia_social_login_user($user_data['email'], $user_data['name'] ?? $user_data['given_name'], 'google', $user_data['picture'] ?? '');
}

/**
 * Initialize Facebook OAuth flow
 */
function nymia_facebook_oauth_init() {
    $app_id = get_option('nymia_facebook_app_id');
    $redirect_uri = home_url('/?nymia_auth=facebook_callback');
    
    if (empty($app_id)) {
        wp_redirect(home_url('/?login=failed&error=facebook_not_configured'));
        exit;
    }
    
    $params = array(
        'client_id' => $app_id,
        'redirect_uri' => $redirect_uri,
        'scope' => 'email,public_profile',
        'response_type' => 'code'
    );
    
    $auth_url = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
    wp_redirect($auth_url);
    exit;
}

/**
 * Handle Facebook OAuth callback
 */
function nymia_facebook_oauth_callback() {
    if (!isset($_GET['code'])) {
        wp_redirect(home_url('/?login=failed&error=facebook_auth_failed'));
        exit;
    }
    
    $code = sanitize_text_field($_GET['code']);
    $app_id = get_option('nymia_facebook_app_id');
    $app_secret = get_option('nymia_facebook_app_secret');
    $redirect_uri = home_url('/?nymia_auth=facebook_callback');
    
    // Exchange code for access token
    $token_response = wp_remote_post('https://graph.facebook.com/v18.0/oauth/access_token', array(
        'body' => array(
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'redirect_uri' => $redirect_uri,
            'code' => $code
        )
    ));
    
    if (is_wp_error($token_response)) {
        wp_redirect(home_url('/?login=failed&error=facebook_token_error'));
        exit;
    }
    
    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
    
    if (!isset($token_data['access_token'])) {
        wp_redirect(home_url('/?login=failed&error=facebook_token_missing'));
        exit;
    }
    
    // Get user info
    $user_response = wp_remote_get('https://graph.facebook.com/v18.0/me?fields=id,name,email,picture&access_token=' . urlencode($token_data['access_token']));
    
    if (is_wp_error($user_response)) {
        wp_redirect(home_url('/?login=failed&error=facebook_user_error'));
        exit;
    }
    
    $user_data = json_decode(wp_remote_retrieve_body($user_response), true);
    
    if (!isset($user_data['email'])) {
        wp_redirect(home_url('/?login=failed&error=facebook_email_missing'));
        exit;
    }
    
    $picture_url = isset($user_data['picture']['data']['url']) ? $user_data['picture']['data']['url'] : '';
    
    // Create or login user
    nymia_social_login_user($user_data['email'], $user_data['name'] ?? '', 'facebook', $picture_url);
}

/**
 * Initialize Apple OAuth flow
 */
function nymia_apple_oauth_init() {
    $client_id = get_option('nymia_apple_client_id');
    $redirect_uri = home_url('/?nymia_auth=apple_callback');
    
    if (empty($client_id)) {
        wp_redirect(home_url('/?login=failed&error=apple_not_configured'));
        exit;
    }
    
    // Generate state and nonce for security
    $state = wp_generate_password(32, false);
    set_transient('nymia_apple_state_' . $state, $state, 600); // 10 minutes
    
    $params = array(
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code id_token',
        'scope' => 'email name',
        'response_mode' => 'form_post',
        'state' => $state
    );
    
    $auth_url = 'https://appleid.apple.com/auth/authorize?' . http_build_query($params);
    wp_redirect($auth_url);
    exit;
}

/**
 * Handle Apple OAuth callback
 */
function nymia_apple_oauth_callback() {
    if (!isset($_POST['code']) && !isset($_POST['id_token'])) {
        wp_redirect(home_url('/?login=failed&error=apple_auth_failed'));
        exit;
    }
    
    // Apple sends POST data
    $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
    $id_token = isset($_POST['id_token']) ? sanitize_text_field($_POST['id_token']) : '';
    $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
    
    // Verify state
    if (empty($state) || !get_transient('nymia_apple_state_' . $state)) {
        wp_redirect(home_url('/?login=failed&error=apple_state_invalid'));
        exit;
    }
    delete_transient('nymia_apple_state_' . $state);
    
    if (empty($id_token)) {
        wp_redirect(home_url('/?login=failed&error=apple_token_missing'));
        exit;
    }
    
    // Decode ID token (simplified - in production, verify JWT signature)
    $token_parts = explode('.', $id_token);
    if (count($token_parts) < 2) {
        wp_redirect(home_url('/?login=failed&error=apple_token_invalid'));
        exit;
    }
    
    $payload = json_decode(base64_decode($token_parts[1]), true);
    
    if (!isset($payload['email'])) {
        wp_redirect(home_url('/?login=failed&error=apple_email_missing'));
        exit;
    }
    
    $name = '';
    if (isset($_POST['user']) && is_string($_POST['user'])) {
        $user_data = json_decode(stripslashes($_POST['user']), true);
        if (isset($user_data['name'])) {
            $given_name = $user_data['name']['firstName'] ?? '';
            $family_name = $user_data['name']['lastName'] ?? '';
            $name = trim($given_name . ' ' . $family_name);
        }
    }
    
    // Create or login user
    nymia_social_login_user($payload['email'], $name, 'apple', '');
}

/**
 * Create or login user from social provider
 */
function nymia_social_login_user($email, $name, $provider, $avatar_url = '') {
    // Check if user exists by email
    $user = get_user_by('email', $email);
    
    if ($user) {
        // User exists, log them in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        // Update meta if needed
        update_user_meta($user->ID, 'social_provider', $provider);
        if (!empty($avatar_url)) {
            update_user_meta($user->ID, 'social_avatar', $avatar_url);
        }
        
        wp_redirect(home_url('/dashboard'));
        exit;
    }
    
    // User doesn't exist, create new account
    $username = sanitize_user($email);
    $username_base = $username;
    $counter = 1;
    
    // Ensure unique username
    while (username_exists($username)) {
        $username = $username_base . $counter;
        $counter++;
    }
    
    // Generate random password
    $password = wp_generate_password(24, true, true);
    
    // Create user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_redirect(home_url('/?registration=error&error=' . urlencode($user_id->get_error_message())));
        exit;
    }
    
    // Set default role
    $user_obj = new WP_User($user_id);
    $user_obj->set_role('subscriber');
    
    // Update user metadata
    if (!empty($name)) {
        $name_parts = explode(' ', $name, 2);
        update_user_meta($user_id, 'first_name', $name_parts[0]);
        if (isset($name_parts[1])) {
            update_user_meta($user_id, 'last_name', $name_parts[1]);
        }
    }
    
    update_user_meta($user_id, 'social_provider', $provider);
    update_user_meta($user_id, 'email_verified', '1'); // Social logins are pre-verified
    
    if (!empty($avatar_url)) {
        update_user_meta($user_id, 'social_avatar', $avatar_url);
    }
    
    // Auto-login
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    
    wp_redirect(home_url('/dashboard'));
    exit;
}


// ==========================================
// INCLUDE FOLLOWERS SYSTEM FILES
// ==========================================
require_once get_template_directory() . '/followers/includes/followers-functions.php';

// ==========================================
// INCLUDE NOTIFICATION SYSTEM FILES
// ==========================================
require_once get_template_directory() . '/notifications/includes/notification-functions.php';

// ==========================================
// ZEGO Cloud helpers and AJAX
// ==========================================
function nymia_zego_generate_token($appId, $userId, $serverSecret, $expireSeconds = 3600) {
    $now = time();
    $payload = array(
        'app_id' => intval($appId),
        'user_id' => (string)$userId,
        'ctime' => $now,
        'expire' => $expireSeconds,
        'nonce' => wp_generate_password(12, false, false)
    );
    $data = json_encode($payload);
    $sig = hash_hmac('sha256', $data, $serverSecret);
    return base64_encode($data . '.' . $sig);
}

function nymia_zego_create_room() {
    if (!is_user_logged_in()) { wp_send_json_error(array('message' => 'Not logged in')); return; }
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if (!wp_verify_nonce($nonce, 'nymia_zego_action')) { wp_send_json_error(array('message' => 'Security error')); return; }
    $appId = trim(get_option('nymia_zego_app_id', ''));
    $secret = trim(get_option('nymia_zego_server_secret', ''));
    if ($appId === '' || $secret === '') { wp_send_json_error(array('message' => 'ZEGO not configured')); return; }
    $roomId = 'room_' . get_current_user_id() . '_' . time();
    $userId = 'u' . get_current_user_id();
    $env = get_option('nymia_zego_env', 'production');
    $token = nymia_zego_generate_token($appId, $userId, $secret, 3600);
    $rooms = get_transient('nymia_zego_rooms'); if (!is_array($rooms)) $rooms = array();
    
    // Get creator profile data
    $creator_id = get_current_user_id();
    $creator = get_user_by('ID', $creator_id);
    // Use nickname or username for anonymity, not display_name
    if ($creator) {
        $nickname = get_user_meta($creator_id, 'nickname', true);
        $creator_name = !empty($nickname) && $nickname !== $creator->user_login ? $nickname : $creator->user_login;
    } else {
        $creator_name = 'User';
    }
    
    // Get avatar
    $custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
    $avatar = $custom_avatar ? $custom_avatar : get_avatar_url($creator_id, array('size' => 150));
    
    // Handle stream thumbnail upload
    $cover_photo = '';
    if (!empty($_FILES['stream_thumbnail']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        $upload = wp_handle_upload($_FILES['stream_thumbnail'], array('test_form' => false));
        if (!isset($upload['error']) && isset($upload['url'])) {
            $cover_photo = $upload['url'];
        }
    }
    
    // Fallback to user cover image if no stream thumbnail uploaded
    if (empty($cover_photo)) {
    $cover_photo = get_user_meta($creator_id, 'cover_image', true);
    if (!$cover_photo) {
        $cover_photo = ''; // Empty string to use gradient fallback
        }
    }
    
    // Check if this is a secret room
    $is_secret = isset($_POST['is_secret']) && $_POST['is_secret'] === '1';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    
    $rooms[$roomId] = array(
        'creator' => $creator_id,
        'title' => sanitize_text_field($_POST['title'] ?? 'Live'),
        'created' => time(),
        'creator_name' => $creator_name,
        'creator_avatar' => $avatar,
        'cover_photo' => $cover_photo,
        'viewers' => 0,
        'viewer_list' => array(),
        'is_secret' => $is_secret,
        'price' => $price
    );
    set_transient('nymia_zego_rooms', $rooms, 6 * HOUR_IN_SECONDS);
    $resp = array('appId' => intval($appId), 'roomId' => $roomId, 'userId' => $userId, 'token' => $token, 'env' => $env);
    if ($env === 'test') { $resp['serverSecret'] = $secret; }
    wp_send_json_success($resp);
}
add_action('wp_ajax_nymia_zego_create_room', 'nymia_zego_create_room');

function nymia_zego_list_rooms() {
    $rooms = get_transient('nymia_zego_rooms'); if (!is_array($rooms)) $rooms = array();
    $list = array();
    $now = current_time('timestamp');
    
    // Check if only secret rooms are requested
    $secret_only = isset($_POST['secret_only']) && $_POST['secret_only'] === '1';
    
    // Check if only online now (available) creators are requested
    $online_now_only = isset($_POST['online_now']) && $_POST['online_now'] === '1';
    
    // Process active/live rooms
    foreach ($rooms as $roomId => $r) {
        // If secret_only is requested, skip non-secret rooms
        if ($secret_only) {
            $is_secret = isset($r['is_secret']) && ($r['is_secret'] === true || $r['is_secret'] === '1' || $r['is_secret'] === 1);
            if (!$is_secret) {
                continue; // Skip non-secret rooms
            }
        }
        
        // Get creator ID first for availability check
        $creator_id = isset($r['creator']) ? intval($r['creator']) : 0;
        
        // If online_now_only is requested, filter by availability status
        if ($online_now_only && $creator_id > 0) {
            $is_available = get_user_meta($creator_id, 'nymia_available_now', true) === '1';
            if (!$is_available) {
                continue; // Skip creators who don't have "Online Now" enabled
            }
        }
        $roomData = array(
            'roomId' => $roomId, 
            'title' => $r['title'],
            'status' => 'live'
        );
        // Get creator ID first
        $creator_id = isset($r['creator']) ? intval($r['creator']) : 0;
        $roomData['creator_id'] = $creator_id;
        $roomData['room_id'] = $roomId;
        
        // Always get creator name from user data to ensure it's accurate
        $creator_name = '';
        $creator_avatar = '';
        $cover_photo = '';
        
        if ($creator_id > 0) {
            $creator = get_user_by('ID', $creator_id);
            if ($creator) {
                // Get creator name - use nickname (user_nicename) or username (user_login) for anonymity
                $nickname = get_user_meta($creator_id, 'nickname', true);
                $creator_name = !empty($nickname) && $nickname !== $creator->user_login ? $nickname : $creator->user_login;
                
                // Get avatar
                $custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
                $creator_avatar = $custom_avatar ? $custom_avatar : get_avatar_url($creator_id, array('size' => 150));
                
                // Get cover photo
                $cover_photo = get_user_meta($creator_id, 'cover_image', true);
                if (!$cover_photo) {
                    $cover_photo = ''; // Empty string to use gradient fallback
                }
            }
        }
        
        // Always use nickname/username from user data for anonymity, ignore display_name from room data
        $roomData['creator_name'] = $creator_name;
        $roomData['creator_avatar'] = !empty($r['creator_avatar']) ? $r['creator_avatar'] : $creator_avatar;
        $roomData['cover_photo'] = !empty($r['cover_photo']) ? $r['cover_photo'] : $cover_photo;
        
        // Include viewer count
        if (isset($r['viewers'])) {
            $roomData['viewers'] = intval($r['viewers']);
        } else {
            $roomData['viewers'] = 0;
        }
        
        // Include pricing information
        if ($creator_id > 0) {
            // Check if room has custom price set
            $room_price = isset($r['price']) ? floatval($r['price']) : 0;
            if ($room_price > 0) {
                $stream_price = $room_price;
            } else {
                $stream_price = floatval(get_user_meta($creator_id, 'nymia_stream_full_price', true));
            }
            $per_minute_price = floatval(get_user_meta($creator_id, 'nymia_stream_per_minute_price', true));
            
            // Fallback to default pricing if not set
            if ($stream_price <= 0) {
                $stream_price = floatval(get_option('nymia_default_stream_price', 9.99));
            }
            if ($per_minute_price <= 0) {
                $per_minute_price = floatval(get_option('nymia_default_per_minute_price', 0.99));
            }
            
            $roomData['price'] = $stream_price;
            $roomData['per_minute_price'] = $per_minute_price;
        } else {
            $roomData['price'] = floatval(get_option('nymia_default_stream_price', 9.99));
            $roomData['per_minute_price'] = floatval(get_option('nymia_default_per_minute_price', 0.99));
        }
        
        // Include is_secret flag if set
        if (isset($r['is_secret'])) {
            $roomData['is_secret'] = $r['is_secret'];
        } else {
            $roomData['is_secret'] = false;
        }
        
        // Include availability status (Online Now) for dashboard display
        if ($creator_id > 0) {
            $is_available = get_user_meta($creator_id, 'nymia_available_now', true) === '1';
            $roomData['is_available_now'] = $is_available;
        } else {
            $roomData['is_available_now'] = false;
        }
        
        $list[] = $roomData;
    }
    
    // Get all scheduled streams from all creators
    $creators = get_users(array(
        'meta_key' => 'nymia_live_stream_schedules',
        'meta_compare' => 'EXISTS',
    ));
    
    foreach ($creators as $creator) {
        $schedules = nymia_get_creator_stream_schedules($creator->ID);
        if (empty($schedules)) {
            continue;
        }
        
        $creator_id = $creator->ID;
        
        // If online_now_only is requested, filter by availability status
        if ($online_now_only) {
            $is_available = get_user_meta($creator_id, 'nymia_available_now', true) === '1';
            if (!$is_available) {
                continue; // Skip creators who don't have "Online Now" enabled
            }
        }
        
        // Use nickname or username for anonymity, not display_name
        $nickname = get_user_meta($creator_id, 'nickname', true);
        $creator_name = !empty($nickname) && $nickname !== $creator->user_login ? $nickname : $creator->user_login;
        $custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
        $creator_avatar = $custom_avatar ? $custom_avatar : get_avatar_url($creator_id, array('size' => 150));
        $cover_photo = get_user_meta($creator_id, 'cover_image', true);
        
        // Get pricing
        $stream_price = floatval(get_user_meta($creator_id, 'nymia_stream_full_price', true));
        $per_minute_price = floatval(get_user_meta($creator_id, 'nymia_stream_per_minute_price', true));
        if ($stream_price <= 0) {
            $stream_price = floatval(get_option('nymia_default_stream_price', 9.99));
        }
        if ($per_minute_price <= 0) {
            $per_minute_price = floatval(get_option('nymia_default_per_minute_price', 0.99));
        }
        
        foreach ($schedules as $schedule) {
            $start_timestamp = intval($schedule['start_timestamp']);
            
            // Only include future schedules
            if ($start_timestamp <= $now) {
                continue;
            }
            
            // If secret_only is requested, skip non-secret scheduled streams
            if ($secret_only) {
                $is_secret = isset($schedule['is_secret']) && ($schedule['is_secret'] === true || $schedule['is_secret'] === '1' || $schedule['is_secret'] === 1);
                if (!$is_secret) {
                    continue; // Skip non-secret scheduled streams
                }
            }
            
            // Create a unique room ID for scheduled stream
            $schedule_room_id = 'scheduled_' . $creator_id . '_' . $schedule['id'];
            
            // Check if this schedule has custom pricing
            $event_type = isset($schedule['event_type']) ? $schedule['event_type'] : 'single';
            $event_price = isset($schedule['event_price']) ? floatval($schedule['event_price']) : 0;
            $max_attendees = isset($schedule['max_attendees']) ? intval($schedule['max_attendees']) : 0;
            $attendees = isset($schedule['attendees']) && is_array($schedule['attendees']) ? $schedule['attendees'] : array();
            $current_attendees = count($attendees);
            
            // Respect custom event price for any scheduled session
            $display_price = $event_price > 0 ? $event_price : $stream_price;
            
            // Include is_secret flag for scheduled streams
            $is_secret_scheduled = isset($schedule['is_secret']) && ($schedule['is_secret'] === true || $schedule['is_secret'] === '1' || $schedule['is_secret'] === 1);
            
            // Include availability status (Online Now) for scheduled streams
            $is_available_scheduled = get_user_meta($creator_id, 'nymia_available_now', true) === '1';
            
            $scheduleData = array(
                'roomId' => $schedule_room_id,
                'room_id' => $schedule_room_id,
                'title' => $schedule['title'] ?: __('Scheduled Stream', 'nymia'),
                'status' => 'scheduled',
                'creator_id' => $creator_id,
                'creator_name' => $creator_name,
                'creator_avatar' => $creator_avatar,
                'cover_photo' => $cover_photo ?: '',
                'viewers' => 0,
                'price' => $display_price,
                'per_minute_price' => $per_minute_price,
                'start_timestamp' => $start_timestamp,
                'duration' => isset($schedule['duration']) ? intval($schedule['duration']) : 60,
                'schedule_id' => $schedule['id'],
                'event_type' => $event_type,
                'event_price' => $event_price,
                'max_attendees' => $max_attendees,
                'current_attendees' => $current_attendees,
                'is_event' => ($event_type === 'group' && $event_price > 0),
                'is_secret' => $is_secret_scheduled,
                'is_available_now' => $is_available_scheduled,
            );
            
            $list[] = $scheduleData;
        }
    }
    
    // If online_now_only is requested, also include creators who have "Online Now" enabled but no active room
    if ($online_now_only) {
        // Get all creators who have "Online Now" enabled
        $available_creators = get_users(array(
            'meta_key' => 'nymia_available_now',
            'meta_value' => '1',
            'meta_compare' => '='
        ));
        
        // Get list of creator IDs who already have rooms
        $creators_with_rooms = array();
        foreach ($list as $room) {
            if (isset($room['creator_id']) && $room['creator_id'] > 0) {
                $creators_with_rooms[$room['creator_id']] = true;
            }
        }
        
        // Add virtual room entries for creators with "Online Now" enabled but no active room
        foreach ($available_creators as $creator) {
            $creator_id = $creator->ID;
            
            // Skip if creator already has a room in the list
            if (isset($creators_with_rooms[$creator_id])) {
                continue;
            }
            
            // Get creator info
            $nickname = get_user_meta($creator_id, 'nickname', true);
            $creator_name = !empty($nickname) && $nickname !== $creator->user_login ? $nickname : $creator->user_login;
            $custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
            $creator_avatar = $custom_avatar ? $custom_avatar : get_avatar_url($creator_id, array('size' => 150));
            $cover_photo = get_user_meta($creator_id, 'cover_image', true);
            if (!$cover_photo) {
                $cover_photo = '';
            }
            
            // Get online thumbnail if set
            $online_thumbnail = get_user_meta($creator_id, 'nymia_available_thumbnail', true);
            if ($online_thumbnail) {
                $cover_photo = $online_thumbnail;
            }
            
            // Get pricing
            $per_minute_price = floatval(get_user_meta($creator_id, 'nymia_available_per_minute_price', true));
            if ($per_minute_price <= 0) {
                $per_minute_price = floatval(get_user_meta($creator_id, 'nymia_stream_per_minute_price', true));
            }
            if ($per_minute_price <= 0) {
                $per_minute_price = floatval(get_option('nymia_default_per_minute_price', 0.99));
            }
            
            $stream_price = floatval(get_user_meta($creator_id, 'nymia_stream_full_price', true));
            if ($stream_price <= 0) {
                $stream_price = floatval(get_option('nymia_default_stream_price', 9.99));
            }
            
            // Create virtual room entry
            $virtual_room_id = 'online_now_' . $creator_id;
            $virtual_room = array(
                'roomId' => $virtual_room_id,
                'room_id' => $virtual_room_id,
                'title' => __('Online Now', 'nymia'),
                'status' => 'live',
                'creator_id' => $creator_id,
                'creator_name' => $creator_name,
                'creator_avatar' => $creator_avatar,
                'cover_photo' => $cover_photo,
                'viewers' => 0,
                'price' => $stream_price,
                'per_minute_price' => $per_minute_price,
                'is_secret' => false,
                'is_available_now' => true,
                'is_virtual' => true, // Flag to indicate this is a virtual room
            );
            
            $list[] = $virtual_room;
        }
    }
    
    // Sort by status (live first) and then by timestamp
    usort($list, function($a, $b) {
        $a_status = isset($a['status']) ? $a['status'] : 'live';
        $b_status = isset($b['status']) ? $b['status'] : 'live';
        
        // Live streams first
        if ($a_status === 'live' && $b_status !== 'live') {
            return -1;
        }
        if ($a_status !== 'live' && $b_status === 'live') {
            return 1;
        }
        
        // For scheduled streams, sort by start time
        if ($a_status === 'scheduled' && $b_status === 'scheduled') {
            $a_time = isset($a['start_timestamp']) ? intval($a['start_timestamp']) : 0;
            $b_time = isset($b['start_timestamp']) ? intval($b['start_timestamp']) : 0;
            return $a_time <=> $b_time;
        }
        
        return 0;
    });
    
    wp_send_json_success(array('rooms' => $list));
}
add_action('wp_ajax_nymia_zego_list_rooms', 'nymia_zego_list_rooms');

/**
 * Get ZegoCloud token for joining an existing room
 */
function nymia_zego_get_token() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to join the room.', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_zego_action', 'nonce');
    
    $appId = trim(get_option('nymia_zego_app_id', ''));
    $secret = trim(get_option('nymia_zego_server_secret', ''));
    $env = get_option('nymia_zego_env', 'production');
    
    if ($appId === '' || $secret === '') {
        wp_send_json_error(array('message' => __('ZEGO is not configured.', 'nymia')));
        return;
    }
    
    $roomId = isset($_POST['roomId']) ? sanitize_text_field($_POST['roomId']) : '';
    $userId = isset($_POST['userId']) ? sanitize_text_field($_POST['userId']) : '';
    
    if (empty($roomId) || empty($userId)) {
        wp_send_json_error(array('message' => __('Room ID and User ID are required.', 'nymia')));
        return;
    }
    
    // Generate token
    $token = nymia_zego_generate_token($appId, $userId, $secret, 3600);
    
    $resp = array(
        'appId' => intval($appId),
        'roomId' => $roomId,
        'userId' => $userId,
        'token' => $token,
        'env' => $env
    );
    
    if ($env === 'test') {
        $resp['serverSecret'] = $secret;
    }
    
    wp_send_json_success($resp);
}
add_action('wp_ajax_nymia_zego_get_token', 'nymia_zego_get_token');
add_action('wp_ajax_nopriv_nymia_zego_list_rooms', 'nymia_zego_list_rooms');

function nymia_zego_join_room() {
    $appId = trim(get_option('nymia_zego_app_id', ''));
    $secret = trim(get_option('nymia_zego_server_secret', ''));
    if ($appId === '' || $secret === '') { wp_send_json_error(array('message' => 'ZEGO not configured')); return; }
    $roomId = sanitize_text_field($_POST['roomId'] ?? ''); if ($roomId === '') { wp_send_json_error(array('message' => 'Invalid room')); return; }
    $userId = is_user_logged_in() ? ('u' . get_current_user_id()) : ('guest_' . wp_generate_password(6, false, false));
    $env = get_option('nymia_zego_env', 'production');
    $token = nymia_zego_generate_token($appId, $userId, $secret, 3600);
    
    // Increment viewer count
    $rooms = get_transient('nymia_zego_rooms'); if (!is_array($rooms)) $rooms = array();
    if (isset($rooms[$roomId])) {
        if (!isset($rooms[$roomId]['viewers'])) $rooms[$roomId]['viewers'] = 0;
        if (!isset($rooms[$roomId]['viewer_list'])) $rooms[$roomId]['viewer_list'] = array();
        
        // Only count unique viewers
        if (!in_array($userId, $rooms[$roomId]['viewer_list'])) {
            $rooms[$roomId]['viewers']++;
            $rooms[$roomId]['viewer_list'][] = $userId;
            set_transient('nymia_zego_rooms', $rooms, 6 * HOUR_IN_SECONDS);
        }
    }
    
    $resp = array('appId' => intval($appId), 'roomId' => $roomId, 'userId' => $userId, 'token' => $token, 'env' => $env);
    if ($env === 'test') { $resp['serverSecret'] = $secret; }
    wp_send_json_success($resp);
}
add_action('wp_ajax_nymia_zego_join_room', 'nymia_zego_join_room');
add_action('wp_ajax_nopriv_nymia_zego_join_room', 'nymia_zego_join_room');

function nymia_zego_get_user_history() {
    if (!is_user_logged_in()) { wp_send_json_error(array('message' => 'Not logged in')); return; }
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if (!wp_verify_nonce($nonce, 'nymia_zego_action')) { wp_send_json_error(array('message' => 'Security error')); return; }
    $rooms = get_transient('nymia_zego_rooms'); if (!is_array($rooms)) $rooms = array();
    $userId = get_current_user_id();
    $userRooms = array();
    foreach ($rooms as $roomId => $r) {
        if (isset($r['creator']) && $r['creator'] == $userId) {
            $userRooms[] = array(
                'roomId' => $roomId,
                'title' => isset($r['title']) ? $r['title'] : 'Untitled Stream',
                'created' => isset($r['created']) ? $r['created'] : time(),
                'duration' => isset($r['duration']) ? $r['duration'] : 0,
                'viewers' => isset($r['viewers']) ? $r['viewers'] : 0,
                'status' => 'completed',
                'is_secret' => isset($r['is_secret']) ? $r['is_secret'] : false,
                'price' => isset($r['price']) ? floatval($r['price']) : 0
            );
        }
    }
    // Sort by created date (newest first)
    usort($userRooms, function($a, $b) { return $b['created'] - $a['created']; });
    wp_send_json_success(array('streams' => array_slice($userRooms, 0, 10))); // Last 10 streams
}
add_action('wp_ajax_nymia_zego_get_user_history', 'nymia_zego_get_user_history');

function nymia_zego_delete_room() {
    if (!is_user_logged_in()) { wp_send_json_error(array('message' => 'Not logged in')); return; }
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if (!wp_verify_nonce($nonce, 'nymia_zego_action')) { wp_send_json_error(array('message' => 'Security error')); return; }
    $roomId = sanitize_text_field($_POST['roomId'] ?? ''); if ($roomId === '') { wp_send_json_error(array('message' => 'Invalid room')); return; }
    $rooms = get_transient('nymia_zego_rooms'); if (!is_array($rooms)) $rooms = array();
    $userId = get_current_user_id();
    // Check if user owns this room
    if (isset($rooms[$roomId]) && isset($rooms[$roomId]['creator']) && $rooms[$roomId]['creator'] == $userId) {
        unset($rooms[$roomId]);
        set_transient('nymia_zego_rooms', $rooms, 6 * HOUR_IN_SECONDS);
        wp_send_json_success(array('message' => 'Stream deleted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Cannot delete stream'));
    }
}
add_action('wp_ajax_nymia_zego_delete_room', 'nymia_zego_delete_room');

// ==========================================
// LIVE SEARCH FUNCTIONALITY
// ==========================================
/**
 * LIVE SEARCH AJAX HANDLER
 * ------------------------
 * Handles live search requests for users, posts, audio, and ebooks
 * Returns JSON results for dropdown display
 * 
 * Hooks into: wp_ajax_nymia_live_search, wp_ajax_nopriv_nymia_live_search
 */
function nymia_live_search_handler() {
    // Only allow logged-in users to search
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to search.', 'nymia')), 401);
        return;
    }

    check_ajax_referer('nymia_live_search', 'nonce');

    $query = isset($_POST['query']) ? sanitize_text_field(trim($_POST['query'])) : '';
    $query = wp_unslash($query);

    if (empty($query) || strlen($query) < 2) {
        wp_send_json_success(array(
            'users' => array(),
            'posts' => array(),
            'audio' => array(),
            'ebooks' => array(),
        ));
        return;
    }

    $results = array(
        'users' => array(),
        'posts' => array(),
        'audio' => array(),
        'ebooks' => array(),
    );

    // CHECK: If search starts with "@" (user search only)
    $is_user_search = (strpos($query, '@') === 0);
    $username_search = $is_user_search ? substr($query, 1) : $query;

    // 1. SEARCH USERS (if @username or general search)
    if ($is_user_search || !$is_user_search) {
        $user_search_query = $is_user_search ? $username_search : $query;
        
        $users = get_users(array(
            'search' => '*' . esc_attr($user_search_query) . '*',
            'search_columns' => array('user_login', 'user_nicename', 'display_name'),
            'number' => 5,
        ));

        foreach ($users as $user) {
            $user_id = intval($user->ID);
            $user_name = $user->display_name ?: $user->user_login;
            $user_username = '@' . $user->user_login;
            
            // Get avatar
            $user_avatar = get_avatar_url($user_id, array('size' => 64));
            $custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
            if ($custom_avatar) {
                $user_avatar = esc_url($custom_avatar);
            }

            $results['users'][] = array(
                'id' => $user_id,
                'name' => esc_html($user_name),
                'username' => esc_html($user_username),
                'avatar' => esc_url($user_avatar),
                'profile_url' => home_url('/profile/?username=' . urlencode($user->user_login)),
            );
        }
    }

    // 2. SEARCH SOCIAL POSTS (if not @username search)
    if (!$is_user_search) {
        $posts_query = new WP_Query(array(
            'post_type' => 'nymia_social_post',
            'post_status' => 'publish',
            's' => $query,
            'posts_per_page' => 5,
            'orderby' => 'relevance',
            'order' => 'DESC',
        ));

        if ($posts_query->have_posts()) {
            while ($posts_query->have_posts()) {
                $posts_query->the_post();
                $post_id = get_the_ID();
                $post_data = nymia_prepare_social_post_payload(get_post());
                
                $results['posts'][] = array(
                    'id' => $post_id,
                    'title' => esc_html($post_data['title']),
                    'excerpt' => esc_html($post_data['excerpt']),
                    'image' => esc_url($post_data['image']),
                    'author_name' => esc_html($post_data['author_name']),
                    'url' => home_url('/single-post/?post_id=' . $post_id),
                );
            }
            wp_reset_postdata();
        }
    }

    // 3. SEARCH AUDIO (if not @username search)
    if (!$is_user_search) {
        $all_audio = get_transient('nymia_all_audio');
        if ($all_audio && is_array($all_audio)) {
            $search_lower = strtolower($query);
            $audio_count = 0;
            
            foreach ($all_audio as $audio) {
                if ($audio_count >= 5) break;
                
                $audio_title = isset($audio['title']) ? strtolower($audio['title']) : '';
                $creator_name = isset($audio['creator_name']) ? strtolower($audio['creator_name']) : '';
                
                if (strpos($audio_title, $search_lower) !== false || strpos($creator_name, $search_lower) !== false) {
                    $audio_id = isset($audio['id']) ? intval($audio['id']) : 0;
                    $user_id = isset($audio['user_id']) ? intval($audio['user_id']) : 0;
                    
                    $cover_image = isset($audio['cover_image']) ? $audio['cover_image'] : '';
                    $cover_image = nymia_normalize_media_url($cover_image);
                    if (empty($cover_image) && $audio_id) {
                        $meta_cover = get_post_meta($audio_id, '_nymia_audio_cover_image', true);
                        $cover_image = nymia_normalize_media_url($meta_cover);
                    }
                    if (empty($cover_image) && $user_id) {
                        $user_cover = get_user_meta($user_id, 'cover_image', true);
                        $cover_image = nymia_normalize_media_url($user_cover);
                    }
                    if (empty($cover_image)) {
                        $cover_image = get_template_directory_uri() . '/assets/images/audio-placeholder.jpg';
                    }

                    $results['audio'][] = array(
                        'id' => $audio_id,
                        'title' => esc_html($audio['title'] ?? 'Untitled'),
                        'creator' => esc_html($audio['creator_name'] ?? 'Unknown'),
                        'image' => esc_url($cover_image),
                        'url' => home_url('/single-audio/?user_id=' . $user_id . '&track_id=' . $audio_id),
                    );
                    $audio_count++;
                }
            }
        }
    }

    // 4. SEARCH EBOOKS (if not @username search)
    if (!$is_user_search) {
        $all_ebooks = get_transient('nymia_all_ebooks');
        if ($all_ebooks && is_array($all_ebooks)) {
            $search_lower = strtolower($query);
            $ebook_count = 0;
            
            foreach ($all_ebooks as $ebook) {
                if ($ebook_count >= 5) break;
                
                $ebook_title = isset($ebook['title']) ? strtolower($ebook['title']) : '';
                $author_name = isset($ebook['author']) ? strtolower($ebook['author']) : '';
                
                if (strpos($ebook_title, $search_lower) !== false || strpos($author_name, $search_lower) !== false) {
                    $ebook_id = isset($ebook['id']) ? (string)$ebook['id'] : '';
                    $thumbnail = isset($ebook['thumbnail']) ? $ebook['thumbnail'] : '';
                    if (empty($thumbnail)) {
                        $thumbnail = get_template_directory_uri() . '/assets/images/ebook-placeholder.jpg';
                    }

                    $results['ebooks'][] = array(
                        'id' => $ebook_id,
                        'title' => esc_html($ebook['title'] ?? 'Untitled'),
                        'author' => esc_html($ebook['author'] ?? 'Unknown'),
                        'thumbnail' => esc_url($thumbnail),
                        'url' => home_url('/single-ebook/?ebook=' . urlencode($ebook_id)),
                    );
                    $ebook_count++;
                }
            }
        }
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_nymia_live_search', 'nymia_live_search_handler');
add_action('wp_ajax_nopriv_nymia_live_search', 'nymia_live_search_handler');

// ========================================
// CREATOR STRIPE PAYOUT PROFILE
// ========================================

if (!function_exists('nymia_get_creator_stripe_profile')) {
    /**
     * Returns sanitized Stripe payout profile data for a creator.
     */
    function nymia_get_creator_stripe_profile($user_id = 0) {
        $user_id = $user_id ? intval($user_id) : get_current_user_id();
        if (!$user_id) {
            return array(
                'email'              => '',
                'account_id'         => '',
                'status'             => 'not_connected',
                'status_label'       => __('Not Connected', 'nymia'),
                'status_description' => __('Add your Stripe details to start receiving payouts.', 'nymia'),
            );
        }

        $email = sanitize_email(get_user_meta($user_id, 'nymia_stripe_payout_email', true));
        $account_id = sanitize_text_field(get_user_meta($user_id, 'nymia_stripe_account_id', true));
        $status_meta = sanitize_text_field(get_user_meta($user_id, 'nymia_stripe_payout_status', true));

        $status = 'not_connected';
        $status_label = __('Not Connected', 'nymia');
        $status_description = __('Add your Stripe details to start receiving payouts.', 'nymia');

        if (!empty($account_id)) {
            $status = 'pending';
            $status_label = __('Pending Review', 'nymia');
            $status_description = __('Finish connecting your Stripe account to enable payouts.', 'nymia');

            $account_details = nymia_fetch_stripe_account($account_id);
            if (!is_wp_error($account_details) && !empty($account_details)) {
                $charges_enabled = !empty($account_details['charges_enabled']);
                $payouts_enabled = !empty($account_details['payouts_enabled']);
                if (!empty($account_details['email']) && empty($email)) {
                    $email = sanitize_email($account_details['email']);
                }

                if ($charges_enabled && $payouts_enabled) {
                    $status = 'connected';
                    $status_label = __('Connected', 'nymia');
                    $status_description = __('Stripe Connect details stored. Admin can trigger payouts to this account.', 'nymia');
                }

                update_user_meta($user_id, 'nymia_stripe_payout_status', $status);
                update_user_meta($user_id, 'nymia_stripe_profile_updated', time());
            }
        } elseif (!empty($email)) {
            $status = 'pending';
            $status_label = __('Pending Review', 'nymia');
            $status_description = __('Your payout details were sent to the admin. You will be notified once verified.', 'nymia');
        }

        if ($status_meta === 'disabled') {
            $status = 'disabled';
            $status_label = __('Disabled', 'nymia');
            $status_description = __('The admin disabled Stripe payouts for this account. Contact support for help.', 'nymia');
        }

        $last_updated = intval(get_user_meta($user_id, 'nymia_stripe_profile_updated', true));

        return array(
            'email'              => $email,
            'account_id'         => $account_id,
            'status'             => $status,
            'status_label'       => $status_label,
            'status_description' => $status_description,
            'last_updated'       => $last_updated,
        );
    }
}

/**
 * AJAX handler to save Stripe payout details for the logged-in creator.
 */
function nymia_save_stripe_payout_method() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to update payout details.', 'nymia')), 403);
    }

    check_ajax_referer('nymia_save_stripe_payout', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => __('Unable to determine user.', 'nymia')), 400);
    }

    $email_raw = isset($_POST['stripe_email']) ? wp_unslash($_POST['stripe_email']) : '';
    $account_id_raw = isset($_POST['stripe_account_id']) ? wp_unslash($_POST['stripe_account_id']) : '';

    $email = sanitize_email($email_raw);
    $account_id = sanitize_text_field($account_id_raw);

    if (empty($email) && empty($account_id)) {
        wp_send_json_error(array('message' => __('Provide at least a Stripe email or account ID.', 'nymia')), 400);
    }

    if (!empty($email_raw) && empty($email)) {
        wp_send_json_error(array('message' => __('Invalid Stripe email address.', 'nymia')), 400);
    }

    if (!empty($account_id) && stripos($account_id, 'acct_') !== 0) {
        wp_send_json_error(array('message' => __('Stripe account IDs must start with acct_.', 'nymia')), 400);
    }

    update_user_meta($user_id, 'nymia_stripe_payout_email', $email);
    update_user_meta($user_id, 'nymia_stripe_account_id', $account_id);
    if (!empty($account_id)) {
        update_user_meta($user_id, 'stripe_account_no', $account_id);
    }
    update_user_meta($user_id, 'nymia_stripe_profile_updated', time());

    if (!empty($account_id)) {
        update_user_meta($user_id, 'nymia_stripe_payout_status', 'connected');
    } elseif (!empty($email)) {
        update_user_meta($user_id, 'nymia_stripe_payout_status', 'pending');
    }

    $profile = nymia_get_creator_stripe_profile($user_id);

    wp_send_json_success(array(
        'message' => __('Stripe payout details saved.', 'nymia'),
        'profile' => $profile,
    ));
}
add_action('wp_ajax_nymia_save_stripe_payout_method', 'nymia_save_stripe_payout_method');

/**
 * Save Bank Account Details
 * Saves bank account information for creator payouts
 */
function nymia_save_bank_account() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to update bank account details.', 'nymia')), 403);
    }

    check_ajax_referer('nymia_save_bank_account', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => __('Unable to determine user.', 'nymia')), 400);
    }

    $bank_name = isset($_POST['bank_name']) ? sanitize_text_field(wp_unslash($_POST['bank_name'])) : '';
    $account_holder_name = isset($_POST['account_holder_name']) ? sanitize_text_field(wp_unslash($_POST['account_holder_name'])) : '';
    $account_number = isset($_POST['account_number']) ? sanitize_text_field(wp_unslash($_POST['account_number'])) : '';
    $routing_number = isset($_POST['routing_number']) ? sanitize_text_field(wp_unslash($_POST['routing_number'])) : '';
    $swift_bic = isset($_POST['swift_bic']) ? sanitize_text_field(wp_unslash($_POST['swift_bic'])) : '';
    $account_type = isset($_POST['account_type']) ? sanitize_text_field(wp_unslash($_POST['account_type'])) : '';
    $country = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';

    // Validate required fields
    if (empty($bank_name)) {
        wp_send_json_error(array('message' => __('Bank name is required.', 'nymia')), 400);
    }

    if (empty($account_holder_name)) {
        wp_send_json_error(array('message' => __('Account holder name is required.', 'nymia')), 400);
    }

    if (empty($account_number)) {
        wp_send_json_error(array('message' => __('Account number is required.', 'nymia')), 400);
    }

    // Save bank account details
    update_user_meta($user_id, 'nymia_bank_name', $bank_name);
    update_user_meta($user_id, 'nymia_bank_account_holder_name', $account_holder_name);
    update_user_meta($user_id, 'nymia_bank_account_number', $account_number);
    update_user_meta($user_id, 'nymia_bank_routing_number', $routing_number);
    update_user_meta($user_id, 'nymia_bank_swift_bic', $swift_bic);
    update_user_meta($user_id, 'nymia_bank_account_type', $account_type);
    update_user_meta($user_id, 'nymia_bank_country', $country);
    update_user_meta($user_id, 'nymia_bank_account_updated', time());

    wp_send_json_success(array(
        'message' => __('Bank account details saved successfully.', 'nymia'),
    ));
}
add_action('wp_ajax_nymia_save_bank_account', 'nymia_save_bank_account');

if (!function_exists('nymia_get_stripe_secret_key')) {
function nymia_get_stripe_secret_key() {
    return trim(get_option('nymia_stripe_secret_key', ''));
}
}

if (!function_exists('nymia_get_stripe_account_country')) {
function nymia_get_stripe_account_country() {
    $default_country = strtoupper(apply_filters('nymia_stripe_account_country', 'US'));
    return $default_country ?: 'US';
}
}

if (!function_exists('nymia_fetch_stripe_account')) {
function nymia_fetch_stripe_account($account_id, $force = false) {
    $account_id = trim($account_id);
    if (empty($account_id)) {
        return new WP_Error('invalid_account', __('Missing Stripe account ID.', 'nymia'));
    }

    $cache_key = 'nymia_stripe_account_' . sanitize_key($account_id);
    if (!$force) {
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }

    $secret_key = nymia_get_stripe_secret_key();
    if (empty($secret_key)) {
        return new WP_Error('missing_keys', __('Stripe keys are not configured.', 'nymia'));
    }

    $response = wp_remote_get('https://api.stripe.com/v1/accounts/' . rawurlencode($account_id), array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
        ),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($code >= 400 || !is_array($body)) {
        $message = isset($body['error']['message']) ? $body['error']['message'] : __('Unable to fetch Stripe account.', 'nymia');
        return new WP_Error('stripe_error', $message);
    }

    set_transient($cache_key, $body, 5 * MINUTE_IN_SECONDS);
    return $body;
}
}

if (!function_exists('nymia_create_stripe_express_account')) {
function nymia_create_stripe_express_account($user_id) {
    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('invalid_user', __('Unable to locate the creator account.', 'nymia'));
    }

    $secret_key = nymia_get_stripe_secret_key();
    if (empty($secret_key)) {
        return new WP_Error('missing_keys', __('Stripe keys are not configured.', 'nymia'));
    }

    $country = nymia_get_stripe_account_country();
    $body = array(
        'type'                                      => 'express',
        'country'                                   => $country,
        'email'                                     => $user->user_email,
        'business_type'                             => 'individual',
        'business_profile[url]'                     => home_url(),
        'capabilities[transfers][requested]'        => 'true',
        'capabilities[card_payments][requested]'    => 'true',
    );

    $response = wp_remote_post('https://api.stripe.com/v1/accounts', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
        ),
        'body'    => $body,
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($code >= 400 || !is_array($body) || empty($body['id'])) {
        $message = isset($body['error']['message']) ? $body['error']['message'] : __('Unable to create Stripe Express account.', 'nymia');
        return new WP_Error('stripe_error', $message);
    }

    $account_id = sanitize_text_field($body['id']);
    update_user_meta($user_id, 'nymia_stripe_account_id', $account_id);
    update_user_meta($user_id, 'nymia_stripe_payout_status', 'pending');
    update_user_meta($user_id, 'nymia_stripe_profile_updated', time());

    delete_transient('nymia_stripe_account_' . sanitize_key($account_id));

    return $account_id;
}
}

if (!function_exists('nymia_create_stripe_account_link')) {
function nymia_create_stripe_account_link($account_id, $return_url, $refresh_url, $type = 'account_onboarding') {
    $secret_key = nymia_get_stripe_secret_key();
    if (empty($secret_key)) {
        return new WP_Error('missing_keys', __('Stripe keys are not configured.', 'nymia'));
    }

    $body = array(
        'account'     => $account_id,
        'refresh_url' => $refresh_url,
        'return_url'  => $return_url,
        'type'        => $type,
    );

    $response = wp_remote_post('https://api.stripe.com/v1/account_links', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
        ),
        'body'    => $body,
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($code >= 400 || !is_array($body) || empty($body['url'])) {
        $message = isset($body['error']['message']) ? $body['error']['message'] : __('Unable to create Stripe onboarding link.', 'nymia');
        return new WP_Error('stripe_error', $message);
    }

    return esc_url_raw($body['url']);
}
}

function nymia_start_stripe_onboarding() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to connect your Stripe account.', 'nymia')), 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'nymia_start_stripe_onboarding')) {
        wp_send_json_error(array('message' => __('Invalid request. Please refresh the page.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $account_id = sanitize_text_field(get_user_meta($user_id, 'nymia_stripe_account_id', true));

    if (empty($account_id)) {
        $account_id = nymia_create_stripe_express_account($user_id);
        if (is_wp_error($account_id)) {
            wp_send_json_error(array('message' => $account_id->get_error_message()));
        }
    }

    $return_url = apply_filters('nymia_stripe_onboarding_return_url', add_query_arg('stripe_onboard', 'success', home_url('/earnings/')), $user_id);
    $refresh_url = apply_filters('nymia_stripe_onboarding_refresh_url', add_query_arg('stripe_onboard', 'refresh', home_url('/earnings/')), $user_id);

    $account_link = nymia_create_stripe_account_link($account_id, $return_url, $refresh_url);
    if (is_wp_error($account_link)) {
        wp_send_json_error(array('message' => $account_link->get_error_message()));
    }

    wp_send_json_success(array('url' => $account_link));
}
add_action('wp_ajax_nymia_start_stripe_onboarding', 'nymia_start_stripe_onboarding');

// ========================================
// CREATOR PAYOUT REQUESTS
// ========================================

if (!function_exists('nymia_register_payout_request_cpt')) {
    function nymia_register_payout_request_cpt() {
        $labels = array(
            'name'          => __('Payout Requests', 'nymia'),
            'singular_name' => __('Payout Request', 'nymia'),
        );

        register_post_type('nymia_payout_request', array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'supports'            => array('title'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'rewrite'             => false,
        ));
    }
    add_action('init', 'nymia_register_payout_request_cpt');
}

if (!function_exists('nymia_sum_payout_requests')) {
function nymia_sum_payout_requests($creator_id, $statuses = array('pending')) {
    $creator_id = (int) $creator_id;
    if (!$creator_id || empty($statuses)) {
        return 0.0;
    }

    $query = new WP_Query(array(
        'post_type'      => 'nymia_payout_request',
        'post_status'    => $statuses,
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'   => '_creator_id',
                'value' => $creator_id,
                'compare' => '=',
                'type' => 'NUMERIC',
            ),
        ),
    ));

    $total = 0.0;
    if ($query->have_posts()) {
        foreach ($query->posts as $request_id) {
            $total += (float) get_post_meta($request_id, '_amount', true);
        }
    }
    wp_reset_postdata();

    return $total;
}
}

if (!function_exists('nymia_get_creator_payout_requests')) {
function nymia_get_creator_payout_requests($creator_id, $args = array()) {
    $defaults = array(
        'posts_per_page' => 10,
        'post_status'    => array('pending', 'publish', 'draft', 'future'),
    );
    $args = wp_parse_args($args, $defaults);
    $args['post_type'] = 'nymia_payout_request';
    $args['meta_query'] = array(
        array(
            'key'   => '_creator_id',
            'value' => (int) $creator_id,
            'compare' => '=',
            'type' => 'NUMERIC',
        ),
    );

    return new WP_Query($args);
}
}

if (!function_exists('nymia_get_creator_payout_summary')) {
function nymia_get_creator_payout_summary($creator_id, $context = array()) {
    $creator_id = (int) $creator_id;
    if (!$creator_id) {
        return array(
            'total_share'      => 0.0,
            'pending_window'   => 0.0,
            'pending_requests' => 0.0,
            'paid_total'       => 0.0,
            'available'        => 0.0,
        );
    }

    $lifetime = isset($context['lifetime']) ? $context['lifetime'] : nymia_collect_creator_charges($creator_id);
    $now_gmt = time();
    $pending_window = isset($context['pending_window'])
        ? $context['pending_window']
        : nymia_collect_creator_charges($creator_id, $now_gmt - 7 * DAY_IN_SECONDS, $now_gmt);

    $total_share = isset($lifetime['share_amount']) ? (float) $lifetime['share_amount'] : nymia_calculate_creator_share(isset($lifetime['amount']) ? $lifetime['amount'] : 0);
    $pending_window_share = isset($pending_window['share_amount']) ? (float) $pending_window['share_amount'] : nymia_calculate_creator_share(isset($pending_window['amount']) ? $pending_window['amount'] : 0);

    $pending_requests_total = nymia_sum_payout_requests($creator_id, array('pending'));
    $approved_total = nymia_sum_payout_requests($creator_id, array('publish'));

    $available = max(0, $total_share - $pending_window_share - $pending_requests_total - $approved_total);

    return array(
        'total_share'      => $total_share,
        'pending_window'   => $pending_window_share,
        'pending_requests' => $pending_requests_total,
        'paid_total'       => $approved_total,
        'available'        => $available,
    );
}
}

if (!function_exists('nymia_prepare_payout_history_payload')) {
function nymia_prepare_payout_history_payload($creator_id, $limit = 10) {
    $query = nymia_get_creator_payout_requests($creator_id, array(
        'posts_per_page' => $limit,
        'post_status'    => array('pending', 'publish', 'draft'),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    $history = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $request_id = get_the_ID();
            $status = get_post_status($request_id);
            $submitted_ts = get_post_time('U', true, $request_id);
            $history[] = array(
                'id'        => $request_id,
                'amount'    => (float) get_post_meta($request_id, '_amount', true),
                'currency'  => get_post_meta($request_id, '_currency', true) ?: strtoupper(get_option('nymia_stripe_currency', 'USD')),
                'status'    => $status,
                'status_label' => $status === 'publish' ? __('Paid', 'nymia') : ($status === 'draft' ? __('Rejected', 'nymia') : __('Pending', 'nymia')),
                'submitted' => $submitted_ts ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $submitted_ts) : '',
                'note'      => get_post_meta($request_id, '_note', true),
                'admin_note'=> get_post_meta($request_id, '_admin_note', true),
                'transfer'  => get_post_meta($request_id, '_transfer_id', true),
            );
        }
        wp_reset_postdata();
    }

    return $history;
}
}

if (!function_exists('nymia_request_payout')) {
function nymia_request_payout() {
    check_ajax_referer('nymia_request_payout', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to request a payout.', 'nymia')), 403);
    }

    $creator_id = get_current_user_id();
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $note = isset($_POST['note']) ? wp_kses_post($_POST['note']) : '';
    $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
    $min_amount = (float) apply_filters('nymia_minimum_payout_amount', 10.0);

    if ($amount < $min_amount) {
        wp_send_json_error(array('message' => sprintf(__('Minimum payout amount is %s.', 'nymia'), nymia_format_currency_for_display($min_amount, $currency))));
    }

    $summary = nymia_get_creator_payout_summary($creator_id);
    $stripe_profile = nymia_get_creator_stripe_profile($creator_id);
    if (empty($stripe_profile['account_id']) || $stripe_profile['status'] !== 'connected') {
        wp_send_json_error(array('message' => __('Please connect and complete Stripe onboarding before requesting a payout.', 'nymia')));
    }
    if ($amount > $summary['available']) {
        wp_send_json_error(array('message' => __('Requested amount exceeds available balance.', 'nymia')));
    }

    $request_id = wp_insert_post(array(
        'post_type'   => 'nymia_payout_request',
        'post_status' => 'pending',
        'post_title'  => sprintf(__('Payout Request - %s', 'nymia'), wp_date(get_option('date_format') . ' ' . get_option('time_format'))),
        'post_author' => $creator_id,
    ));

    if (!$request_id) {
        wp_send_json_error(array('message' => __('Unable to create payout request. Please try again later.', 'nymia')));
    }

    update_post_meta($request_id, '_creator_id', $creator_id);
    update_post_meta($request_id, '_amount', $amount);
    update_post_meta($request_id, '_currency', $currency);
    update_post_meta($request_id, '_note', $note);
    update_post_meta($request_id, '_payout_status', 'pending');

    $updated_summary = nymia_get_creator_payout_summary($creator_id);
    $history = nymia_prepare_payout_history_payload($creator_id);

    wp_send_json_success(array(
        'message' => __('Payout request submitted successfully.', 'nymia'),
        'summary' => $updated_summary,
        'history' => $history,
    ));
}
add_action('wp_ajax_nymia_request_payout', 'nymia_request_payout');
}

if (!function_exists('nymia_get_payout_history_ajax')) {
function nymia_get_payout_history_ajax() {
    check_ajax_referer('nymia_request_payout', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to view payout history.', 'nymia')), 403);
    }

    $creator_id = get_current_user_id();
    $summary = nymia_get_creator_payout_summary($creator_id);
    $history = nymia_prepare_payout_history_payload($creator_id);

    wp_send_json_success(array(
        'summary' => $summary,
        'history' => $history,
    ));
}
add_action('wp_ajax_nymia_get_payout_history', 'nymia_get_payout_history_ajax');
}

if (!function_exists('nymia_process_stripe_payout')) {
function nymia_process_stripe_payout($creator_id, $amount, $currency) {
    $creator_id = (int) $creator_id;
    $amount = (float) $amount;
    if ($creator_id <= 0 || $amount <= 0) {
        return new WP_Error('invalid_data', __('Invalid payout information.', 'nymia'));
    }

    $destination_account = get_user_meta($creator_id, 'nymia_stripe_account_id', true);
    if (empty($destination_account)) {
        return new WP_Error('missing_account', __('Creator does not have a connected Stripe account.', 'nymia'));
    }

    $secret_key = get_option('nymia_stripe_secret_key', '');
    if (empty($secret_key)) {
        return new WP_Error('missing_keys', __('Stripe is not configured.', 'nymia'));
    }

    $transfer_data = array(
        'amount'      => (int) round($amount * 100),
        'currency'    => strtolower($currency),
        'destination' => $destination_account,
        'description' => sprintf(__('Payout to creator #%d', 'nymia'), $creator_id),
    );

    $response = wp_remote_post('https://api.stripe.com/v1/transfers', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
        ),
        'body'    => $transfer_data,
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code >= 400 || (isset($body['error']) && !empty($body['error']['message']))) {
        $message = isset($body['error']['message']) ? $body['error']['message'] : __('Stripe transfer failed.', 'nymia');
        return new WP_Error('stripe_error', $message);
    }

    return isset($body['id']) ? $body['id'] : true;
}
}

if (!function_exists('nymia_handle_admin_payout_action')) {
function nymia_handle_admin_payout_action() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'nymia'));
    }

    check_admin_referer('nymia_payout_action');

    $request_id = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
    $action_type = isset($_POST['payout_action']) ? sanitize_key($_POST['payout_action']) : '';
    $admin_note = isset($_POST['admin_note']) ? wp_kses_post($_POST['admin_note']) : '';

    if (!$request_id || !$action_type) {
        wp_redirect(add_query_arg('nymia_payout_notice', 'invalid', wp_get_referer()));
        exit;
    }

    $creator_id = (int) get_post_meta($request_id, '_creator_id', true);
    $amount = (float) get_post_meta($request_id, '_amount', true);
    $currency = get_post_meta($request_id, '_currency', true) ?: strtoupper(get_option('nymia_stripe_currency', 'USD'));

    if ($action_type === 'approve') {
        $transfer = nymia_process_stripe_payout($creator_id, $amount, $currency);
        if (is_wp_error($transfer)) {
            update_post_meta($request_id, '_payout_status', 'failed');
            update_post_meta($request_id, '_admin_note', wp_strip_all_tags($transfer->get_error_message()));
            wp_redirect(add_query_arg('nymia_payout_notice', 'failed', wp_get_referer()));
            exit;
        }

        wp_update_post(array(
            'ID'          => $request_id,
            'post_status' => 'publish',
        ));
        update_post_meta($request_id, '_payout_status', 'approved');
        update_post_meta($request_id, '_transfer_id', is_string($transfer) ? $transfer : '');
        update_post_meta($request_id, '_approved_by', get_current_user_id());
        update_post_meta($request_id, '_approved_at', current_time('mysql'));
        update_post_meta($request_id, '_admin_note', $admin_note);
    } elseif ($action_type === 'reject') {
        wp_update_post(array(
            'ID'          => $request_id,
            'post_status' => 'draft',
        ));
        update_post_meta($request_id, '_payout_status', 'rejected');
        update_post_meta($request_id, '_admin_note', $admin_note);
    }

    wp_redirect(add_query_arg('nymia_payout_notice', 'updated', wp_get_referer()));
    exit;
}
add_action('admin_post_nymia_handle_payout_action', 'nymia_handle_admin_payout_action');
}

// ========================================
// CREATOR LIVE STREAM SCHEDULING
// ========================================

if (!function_exists('nymia_get_creator_stream_schedules')) {
    /**
     * Fetches upcoming live stream schedules for creator.
     */
    function nymia_get_creator_stream_schedules($user_id = 0) {
        $user_id = $user_id ? intval($user_id) : get_current_user_id();
        if (!$user_id) {
            return array();
        }
        $schedules = get_user_meta($user_id, 'nymia_live_stream_schedules', true);
        if (!is_array($schedules)) {
            return array();
        }
        $prepared = array();
        foreach ($schedules as $schedule) {
            if (empty($schedule['id']) || empty($schedule['start_timestamp'])) {
                continue;
            }

            $event_type = isset($schedule['event_type']) ? sanitize_key($schedule['event_type']) : 'single';
            if (!in_array($event_type, array('single', 'group'), true)) {
                $event_type = 'single';
            }

            $event_price   = isset($schedule['event_price']) ? floatval($schedule['event_price']) : 0;
            $max_attendees = isset($schedule['max_attendees']) ? absint($schedule['max_attendees']) : 0;
            $attendees     = array();

            if (!empty($schedule['attendees']) && is_array($schedule['attendees'])) {
                foreach ($schedule['attendees'] as $attendee) {
                    $attendees[] = array(
                        'user_id'    => isset($attendee['user_id']) ? intval($attendee['user_id']) : 0,
                        'user_name'  => isset($attendee['user_name']) ? sanitize_text_field($attendee['user_name']) : '',
                        'user_email' => isset($attendee['user_email']) ? sanitize_email($attendee['user_email']) : '',
                        'booked_at'  => isset($attendee['booked_at']) ? intval($attendee['booked_at']) : 0,
                        'session_id' => isset($attendee['session_id']) ? sanitize_text_field($attendee['session_id']) : '',
                    );
                }
            }

            $prepared[] = array(
                'id'              => sanitize_text_field($schedule['id']),
                'title'           => sanitize_text_field($schedule['title'] ?? ''),
                'start_timestamp' => intval($schedule['start_timestamp']),
                'duration'        => isset($schedule['duration']) ? absint($schedule['duration']) : 60,
                'created'         => isset($schedule['created']) ? intval($schedule['created']) : 0,
                'event_type'      => $event_type,
                'event_price'     => $event_price,
                'max_attendees'   => $event_type === 'group' ? max(2, $max_attendees) : 0,
                'attendees'       => $attendees,
            );
        }
        return $prepared;
    }
}

if (!function_exists('nymia_save_creator_stream_schedules')) {
    function nymia_save_creator_stream_schedules($user_id, $schedules) {
        if (!$user_id) {
            return false;
        }
        if (!is_array($schedules)) {
            $schedules = array();
        }
        update_user_meta($user_id, 'nymia_live_stream_schedules', array_values($schedules));
        return true;
    }
}

function nymia_schedule_live_stream() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to schedule live streams.', 'nymia')), 403);
    }

    check_ajax_referer('nymia_schedule_live_stream', 'nonce');

    $user_id       = get_current_user_id();
    $title_raw     = isset($_POST['title']) ? wp_unslash($_POST['title']) : '';
    $start_raw     = isset($_POST['start_time']) ? wp_unslash($_POST['start_time']) : '';
    $duration_raw  = isset($_POST['duration']) ? wp_unslash($_POST['duration']) : '60';
    $event_type_raw = isset($_POST['event_type']) ? wp_unslash($_POST['event_type']) : 'single';
    $event_price_raw = isset($_POST['event_price']) ? wp_unslash($_POST['event_price']) : '0';
    $max_attendees_raw = isset($_POST['max_attendees']) ? wp_unslash($_POST['max_attendees']) : '';

    $title         = sanitize_text_field($title_raw);
    $duration      = max(10, min(480, absint($duration_raw))); // 10min to 8h
    $event_type    = in_array($event_type_raw, array('single', 'group')) ? $event_type_raw : 'single';
    $event_price   = max(0, floatval($event_price_raw));
    $max_attendees = $event_type === 'group' ? max(2, absint($max_attendees_raw)) : 0;

    if (empty($title)) {
        $title = __('Scheduled Stream', 'nymia');
    }

    if (empty($start_raw)) {
        wp_send_json_error(array('message' => __('Select a start time for your stream.', 'nymia')));
    }

    $start_timestamp = strtotime($start_raw);
    if (!$start_timestamp) {
        wp_send_json_error(array('message' => __('Invalid start time.', 'nymia')));
    }

    $current_timestamp = current_time('timestamp');
    if ($start_timestamp <= $current_timestamp + 300) { // at least 5 minutes ahead
        wp_send_json_error(array('message' => __('Please schedule at least 5 minutes in advance.', 'nymia')));
    }

    // Validate group event requirements
    if ($event_type === 'group' && $max_attendees < 2) {
        wp_send_json_error(array('message' => __('Group events must have at least 2 maximum attendees.', 'nymia')));
    }

    $schedules = nymia_get_creator_stream_schedules($user_id);

    // Prevent too many schedules
    if (count($schedules) >= 10) {
        wp_send_json_error(array('message' => __('You have reached the maximum number of scheduled streams.', 'nymia')));
    }

    // Handle schedule thumbnail upload
    $schedule_thumbnail_url = '';
    if (!empty($_FILES['schedule_thumbnail']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        $upload = wp_handle_upload($_FILES['schedule_thumbnail'], array('test_form' => false));
        if (!isset($upload['error']) && isset($upload['url'])) {
            $schedule_thumbnail_url = $upload['url'];
        }
    }

    $schedule_id = uniqid('schedule_', true);
    $schedules[] = array(
        'id'              => $schedule_id,
        'title'           => $title,
        'start_timestamp' => $start_timestamp,
        'thumbnail'       => $schedule_thumbnail_url,
        'duration'        => $duration,
        'event_type'      => $event_type,
        'event_price'     => $event_price,
        'max_attendees'   => $max_attendees,
        'attendees'       => array(), // Track booked attendees
        'created'         => $current_timestamp,
    );

    // Sort schedules by start time
    usort($schedules, function($a, $b) {
        return $a['start_timestamp'] <=> $b['start_timestamp'];
    });

    nymia_save_creator_stream_schedules($user_id, $schedules);

    wp_send_json_success(array(
        'message'   => __('Stream scheduled successfully.', 'nymia'),
        'schedules' => $schedules,
    ));
}
add_action('wp_ajax_nymia_schedule_live_stream', 'nymia_schedule_live_stream');

function nymia_delete_stream_schedule() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to manage schedules.', 'nymia')), 403);
    }

    check_ajax_referer('nymia_delete_stream_schedule', 'nonce');

    $user_id = get_current_user_id();
    $schedule_id = isset($_POST['schedule_id']) ? sanitize_text_field(wp_unslash($_POST['schedule_id'])) : '';
    if (empty($schedule_id)) {
        wp_send_json_error(array('message' => __('Missing schedule identifier.', 'nymia')));
    }

    $schedules = nymia_get_creator_stream_schedules($user_id);
    $updated = array();
    $found = false;
    foreach ($schedules as $schedule) {
        if ($schedule['id'] === $schedule_id) {
            $found = true;
            continue;
        }
        $updated[] = $schedule;
    }

    if (!$found) {
        wp_send_json_error(array('message' => __('Schedule not found.', 'nymia')));
    }

    nymia_save_creator_stream_schedules($user_id, $updated);

    wp_send_json_success(array(
        'message'   => __('Schedule removed.', 'nymia'),
        'schedules' => $updated,
    ));
}
add_action('wp_ajax_nymia_delete_stream_schedule', 'nymia_delete_stream_schedule');

// ========================================
// PRIVATE 1:1 SESSION AVAILABILITY
// ========================================

if (!function_exists('nymia_get_private_session_settings')) {
    function nymia_get_private_session_settings($user_id = 0) {
        $user_id = $user_id ? intval($user_id) : get_current_user_id();
        if (!$user_id) {
            return array(
                'price' => 0,
                'reminder_default' => 15,
            );
        }
        $settings = get_user_meta($user_id, 'nymia_private_session_settings', true);
        if (!is_array($settings)) {
            $settings = array();
        }
        $defaults = array(
            'price' => 0,
            'reminder_default' => 15,
        );
        return array_merge($defaults, $settings);
    }
}

if (!function_exists('nymia_save_private_session_settings')) {
    function nymia_save_private_session_settings($user_id, $settings) {
        if (!$user_id) {
            return false;
        }
        if (!is_array($settings)) {
            $settings = array();
        }
        update_user_meta($user_id, 'nymia_private_session_settings', $settings);
        return true;
    }
}

if (!function_exists('nymia_get_private_session_slots')) {
    function nymia_get_private_session_slots($user_id = 0, $args = array()) {
        $user_id = $user_id ? intval($user_id) : get_current_user_id();
        if (!$user_id) {
            return array();
        }
        $slots = get_user_meta($user_id, 'nymia_private_session_slots', true);
        if (!is_array($slots)) {
            $slots = array();
        }
        // Optional filters
        if (!empty($args['status'])) {
            $slots = array_filter($slots, function($slot) use ($args) {
                return isset($slot['status']) && $slot['status'] === $args['status'];
            });
        }
        if (!empty($args['future_only'])) {
            $now = current_time('timestamp');
            $slots = array_filter($slots, function($slot) use ($now) {
                return isset($slot['start_timestamp']) && intval($slot['start_timestamp']) >= $now;
            });
        }
        // Sort ascending by start time
        usort($slots, function($a, $b) {
            return intval($a['start_timestamp'] ?? 0) <=> intval($b['start_timestamp'] ?? 0);
        });
        return array_values($slots);
    }
}

if (!function_exists('nymia_save_private_session_slots')) {
    function nymia_save_private_session_slots($user_id, $slots) {
        if (!$user_id) {
            return false;
        }
        if (!is_array($slots)) {
            $slots = array();
        }
        update_user_meta($user_id, 'nymia_private_session_slots', array_values($slots));
        return true;
    }
}

function nymia_private_get_timezone() {
    $tz_string = get_option('timezone_string');
    if ($tz_string) {
        return new DateTimeZone($tz_string);
    }
    $offset = floatval(get_option('gmt_offset'));
    $hours = (int) $offset;
    $minutes = ($offset - $hours) * 60;
    $sign = $offset >= 0 ? '+' : '-';
    return new DateTimeZone(sprintf('%s%02d:%02d', $sign, abs($hours), abs($minutes)));
}

function nymia_private_parse_datetime($date, $time) {
    $tz = nymia_private_get_timezone();
    $date = sanitize_text_field($date);
    $time = sanitize_text_field($time);
    $datetime = DateTime::createFromFormat('Y-m-d H:i', "{$date} {$time}", $tz);
    if (!$datetime) {
        return false;
    }
    return $datetime->getTimestamp();
}

function nymia_private_add_slots_handler() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to manage private sessions.', 'nymia')), 403);
    }
    check_ajax_referer('nymia_private_sessions', 'nonce');

    $user_id = get_current_user_id();
    $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'single';
    $duration = isset($_POST['duration']) ? max(15, min(240, absint($_POST['duration']))) : 30;
    $slots = nymia_get_private_session_slots($user_id);
    $settings = nymia_get_private_session_settings($user_id);
    $price = floatval($settings['price']);
    if ($price <= 0) {
        wp_send_json_error(array('message' => __('Set a session price before adding slots.', 'nymia')));
    }

    $created = array();

    if ($mode === 'single') {
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
        $timestamp = nymia_private_parse_datetime($date, $time);
        if (!$timestamp) {
            wp_send_json_error(array('message' => __('Invalid date or time.', 'nymia')));
        }
        $created[] = $timestamp;
    } else {
        $weekdays = isset($_POST['weekdays']) && is_array($_POST['weekdays']) ? array_map('sanitize_text_field', $_POST['weekdays']) : array();
        $weekdays = array_filter($weekdays, function($day) {
            return in_array($day, array('mon','tue','wed','thu','fri','sat','sun'), true);
        });
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $time_start = isset($_POST['time_start']) ? sanitize_text_field($_POST['time_start']) : '';
        $time_end = isset($_POST['time_end']) ? sanitize_text_field($_POST['time_end']) : '';
        if (empty($weekdays) || !$start_date || !$end_date || !$time_start || !$time_end) {
            wp_send_json_error(array('message' => __('Provide weekdays, date range, and time range.', 'nymia')));
        }
        $tz = nymia_private_get_timezone();
        $start = DateTime::createFromFormat('Y-m-d', $start_date, $tz);
        $end = DateTime::createFromFormat('Y-m-d', $end_date, $tz);
        if (!$start || !$end || $end < $start) {
            wp_send_json_error(array('message' => __('Invalid date range.', 'nymia')));
        }
        $interval_minutes = max(15, min(240, absint($_POST['interval'] ?? $duration)));
        $current = clone $start;
        while ($current <= $end) {
            $weekday_key = strtolower($current->format('D'));
            $weekday_key = substr($weekday_key, 0, 3);
            if (in_array($weekday_key, $weekdays, true)) {
                $window_start = nymia_private_parse_datetime($current->format('Y-m-d'), $time_start);
                $window_end = nymia_private_parse_datetime($current->format('Y-m-d'), $time_end);
                if ($window_start && $window_end && $window_end > $window_start) {
                    for ($ts = $window_start; $ts < $window_end; $ts += $interval_minutes * MINUTE_IN_SECONDS) {
                        if ($ts + ($duration * MINUTE_IN_SECONDS) <= $window_end) {
                            $created[] = $ts;
                        }
                    }
                }
            }
            $current->modify('+1 day');
        }
    }

    if (empty($created)) {
        wp_send_json_error(array('message' => __('No slots generated for the provided inputs.', 'nymia')));
    }

    $existing_starts = array_map(function($slot) {
        return intval($slot['start_timestamp'] ?? 0);
    }, $slots);

    foreach ($created as $timestamp) {
        if (in_array($timestamp, $existing_starts, true)) {
            continue;
        }
        $slot_id = uniqid('priv_', true);
        $slots[] = array(
            'id' => $slot_id,
            'start_timestamp' => $timestamp,
            'duration' => $duration,
            'price' => $price,
            'status' => 'available',
            'max_attendees' => 1,
            'current_attendees' => 0,
            'created_at' => current_time('mysql'),
        );
    }

    nymia_save_private_session_slots($user_id, $slots);

    wp_send_json_success(array(
        'message' => __('Slots added successfully.', 'nymia'),
        'slots' => nymia_get_private_session_slots($user_id, array('future_only' => true)),
    ));
}
add_action('wp_ajax_nymia_private_add_slots', 'nymia_private_add_slots_handler');

function nymia_private_delete_slot_handler() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to manage private sessions.', 'nymia')), 403);
    }
    check_ajax_referer('nymia_private_sessions', 'nonce');

    $slot_id = isset($_POST['slot_id']) ? sanitize_text_field($_POST['slot_id']) : '';
    if (!$slot_id) {
        wp_send_json_error(array('message' => __('Missing slot identifier.', 'nymia')));
    }

    $user_id = get_current_user_id();
    $slots = nymia_get_private_session_slots($user_id);
    $updated = array();
    $removed = false;
    foreach ($slots as $slot) {
        if ($slot['id'] === $slot_id) {
            if (isset($slot['status']) && $slot['status'] !== 'available') {
                wp_send_json_error(array('message' => __('Cannot remove a booked slot.', 'nymia')));
            }
            $removed = true;
            continue;
        }
        $updated[] = $slot;
    }

    if (!$removed) {
        wp_send_json_error(array('message' => __('Slot not found.', 'nymia')));
    }

    nymia_save_private_session_slots($user_id, $updated);

    wp_send_json_success(array(
        'message' => __('Slot removed.', 'nymia'),
        'slots' => nymia_get_private_session_slots($user_id, array('future_only' => true)),
    ));
}
add_action('wp_ajax_nymia_private_delete_slot', 'nymia_private_delete_slot_handler');

function nymia_private_save_settings_handler() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to manage private sessions.', 'nymia')), 403);
    }
    check_ajax_referer('nymia_private_sessions', 'nonce');

    $user_id = get_current_user_id();
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    if ($price < 0) {
        $price = 0;
    }
    $reminder_default = isset($_POST['reminder_default']) ? intval($_POST['reminder_default']) : 15;
    $allowed_reminders = array(5, 15, 30, 60, 120);
    if (!in_array($reminder_default, $allowed_reminders, true)) {
        $reminder_default = 15;
    }

    nymia_save_private_session_settings($user_id, array(
        'price' => $price,
        'reminder_default' => $reminder_default,
    ));

    wp_send_json_success(array(
        'message' => __('Settings updated.', 'nymia'),
        'settings' => nymia_get_private_session_settings($user_id),
    ));
}
add_action('wp_ajax_nymia_private_save_settings', 'nymia_private_save_settings_handler');

function nymia_private_get_slots_handler() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to manage private sessions.', 'nymia')), 403);
    }
    check_ajax_referer('nymia_private_sessions', 'nonce');

    $user_id = get_current_user_id();
    $settings = nymia_get_private_session_settings($user_id);
    $slots = nymia_get_private_session_slots($user_id, array('future_only' => true));

    wp_send_json_success(array(
        'settings' => $settings,
        'slots' => $slots,
    ));
}
add_action('wp_ajax_nymia_private_get_slots', 'nymia_private_get_slots_handler');

function nymia_private_all_available_slots($limit = 50) {
    $users = get_users(array(
        'meta_key' => 'nymia_private_session_slots',
        'meta_compare' => 'EXISTS',
        'fields' => 'all',
    ));
    $now = current_time('timestamp');
    $slots = array();
    foreach ($users as $user) {
        $creator_id = $user->ID;
        $settings = nymia_get_private_session_settings($creator_id);
        $creator_slots = nymia_get_private_session_slots($creator_id, array('future_only' => true));
        if (empty($creator_slots)) {
            continue;
        }
        $avatar = get_avatar_url($creator_id, array('size' => 120));
        $custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
        if ($custom_avatar) {
            $avatar = esc_url($custom_avatar);
        }
        foreach ($creator_slots as $slot) {
            if (empty($slot['id']) || ($slot['status'] ?? '') !== 'available') {
                continue;
            }
            if (($slot['start_timestamp'] ?? 0) < $now) {
                continue;
            }
            $slots[] = array(
                'slot_id' => $slot['id'],
                'creator_id' => $creator_id,
                'creator_name' => $user->display_name ?: $user->user_login,
                'creator_avatar' => $avatar,
                'start_timestamp' => intval($slot['start_timestamp']),
                'duration' => intval($slot['duration']),
                'price' => floatval($slot['price']),
                'reminder_default' => intval($settings['reminder_default']),
            );
        }
    }
    usort($slots, function($a, $b) {
        return $a['start_timestamp'] <=> $b['start_timestamp'];
    });
    if ($limit > 0) {
        $slots = array_slice($slots, 0, $limit);
    }
    return $slots;
}

function nymia_private_public_slots_handler() {
    $slots = nymia_private_all_available_slots();
    wp_send_json_success(array('slots' => $slots));
}
add_action('wp_ajax_nymia_private_public_slots', 'nymia_private_public_slots_handler');
add_action('wp_ajax_nopriv_nymia_private_public_slots', 'nymia_private_public_slots_handler');

function nymia_private_bookings_meta($user_id = 0) {
    $user_id = $user_id ? intval($user_id) : get_current_user_id();
    if (!$user_id) return array();
    $bookings = get_user_meta($user_id, 'nymia_private_bookings', true);
    if (!is_array($bookings)) {
        $bookings = array();
    }
    return $bookings;
}

function nymia_private_save_bookings($user_id, $bookings) {
    update_user_meta($user_id, 'nymia_private_bookings', array_values($bookings));
}

function nymia_private_mark_slot_booked($creator_id, $slot_id, $customer_id, $customer_name, $customer_email, $session_id, $reminder_minutes) {
    $slots = nymia_get_private_session_slots($creator_id);
    $found = false;
    $slot_data = null;
    foreach ($slots as &$slot) {
        if ($slot['id'] === $slot_id) {
            if (($slot['status'] ?? 'available') !== 'available') {
                return new WP_Error('slot_unavailable', __('Slot already booked.', 'nymia'));
            }
            $slot['status'] = 'booked';
            $slot['booking_user_id'] = $customer_id;
            $slot['booking_user_name'] = $customer_name;
            $slot['booking_user_email'] = $customer_email;
            $slot['booking_session_id'] = $session_id;
            $slot['reminder_minutes'] = $reminder_minutes;
            $slot['current_attendees'] = 1;
            $slot_data = $slot;
            $found = true;
            break;
        }
    }
    if (!$found) {
        return new WP_Error('slot_not_found', __('Slot not found.', 'nymia'));
    }
    nymia_save_private_session_slots($creator_id, $slots);
    return $slot_data;
}

function nymia_book_private_session() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to book a private session.', 'nymia')), 403);
    }
    check_ajax_referer('nymia_book_live_stream', 'nonce');

    $slot_id = isset($_POST['slot_id']) ? sanitize_text_field($_POST['slot_id']) : '';
    $creator_id = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : 0;
    $reminder_minutes = isset($_POST['reminder_minutes']) ? intval($_POST['reminder_minutes']) : 0;
    $allowed_reminders = array(0, 5, 15, 30, 60, 120);
    if (!in_array($reminder_minutes, $allowed_reminders, true)) {
        $reminder_minutes = 0;
    }

    if (!$slot_id || !$creator_id) {
        wp_send_json_error(array('message' => __('Invalid slot selection.', 'nymia')));
    }

    $slots = nymia_get_private_session_slots($creator_id);
    $slot = null;
    foreach ($slots as $s) {
        if ($s['id'] === $slot_id) {
            $slot = $s;
            break;
        }
    }
    if (!$slot || ($slot['status'] ?? 'available') !== 'available') {
        wp_send_json_error(array('message' => __('This slot is not available anymore.', 'nymia')));
    }

    $price = isset($slot['price']) ? floatval($slot['price']) : 0;
    if ($price <= 0) {
        wp_send_json_error(array('message' => __('Invalid session price.', 'nymia')));
    }

    $start_timestamp = intval($slot['start_timestamp'] ?? 0);
    $duration = intval($slot['duration'] ?? 60);
    $creator = get_user_by('id', $creator_id);
    $creator_name = $creator ? ($creator->display_name ?: $creator->user_login) : __('Creator', 'nymia');
    $session_title = sprintf(__('Private session with %s', 'nymia'), $creator_name);

    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
    if (empty($stripe_secret_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured.', 'nymia')));
    }

    $current_user = wp_get_current_user();
    $success_url = add_query_arg(array(
        'type' => 'private_session',
        'slot_id' => $slot_id,
        'session_id' => '{CHECKOUT_SESSION_ID}'
    ), home_url('/checkout'));
    $success_url = str_replace(array('%7B', '%7D'), array('{', '}'), $success_url);
    $cancel_url = home_url('/?booking=cancelled');

    $session_data = array(
        'payment_method_types' => array('card'),
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'line_items' => array(
            array(
                'price_data' => array(
                    'currency' => strtolower($currency),
                    'product_data' => array(
                        'name' => $session_title,
                        'description' => sprintf(__('Duration: %d minutes', 'nymia'), $duration),
                    ),
                    'unit_amount' => round($price * 100),
                ),
                'quantity' => 1,
            ),
        ),
        'metadata' => array(
            'item_type' => 'private_session',
            'slot_id' => $slot_id,
            'creator_id' => $creator_id,
            'user_id' => $current_user->ID,
            'start_timestamp' => $start_timestamp,
            'duration' => $duration,
            'reminder_minutes' => $reminder_minutes,
            'stream_title' => $session_title,
        ),
    );

    $response = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query($session_data),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => __('Failed to create checkout session.', 'nymia')));
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['url'])) {
        wp_send_json_success(array('checkout_url' => $response_body['url']));
    } else {
        $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create checkout session.', 'nymia');
        wp_send_json_error(array('message' => $error_message));
    }
}
add_action('wp_ajax_nymia_book_private_session', 'nymia_book_private_session');

// ========================================
// AVAILABLE NOW (GREEN LIGHT) FEATURE
// ========================================

/**
 * Get creator availability status
 */
function nymia_get_availability_status() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_availability', 'nonce');

    $user_id = get_current_user_id();
    $is_available = get_user_meta($user_id, 'nymia_available_now', true) === '1';
    $per_minute_price = floatval(get_user_meta($user_id, 'nymia_available_per_minute_price', true));
    $online_thumbnail = get_user_meta($user_id, 'nymia_available_thumbnail', true);

    wp_send_json_success(array(
        'is_available' => $is_available,
        'per_minute_price' => $per_minute_price,
        'online_thumbnail' => $online_thumbnail ? $online_thumbnail : ''
    ));
}
add_action('wp_ajax_nymia_get_availability_status', 'nymia_get_availability_status');

/**
 * Toggle creator availability status
 */
function nymia_toggle_availability() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_availability', 'nonce');

    $user_id = get_current_user_id();
    $is_available = isset($_POST['is_available']) && intval($_POST['is_available']) === 1;
    $per_minute_price = isset($_POST['per_minute_price']) ? floatval($_POST['per_minute_price']) : 0;

    // Check if creator is currently in a scheduled session
    if ($is_available) {
        $schedules = nymia_get_creator_stream_schedules($user_id);
        $now = current_time('timestamp');
        
        foreach ($schedules as $schedule) {
            if (empty($schedule['start_timestamp'])) continue;
            
            $start_time = intval($schedule['start_timestamp']);
            $duration = intval($schedule['duration'] ?? 60);
            $end_time = $start_time + ($duration * 60);
            
            // Check if there's an active scheduled session
            if ($now >= $start_time && $now <= $end_time) {
                wp_send_json_error(array('message' => __('You cannot enable availability while you are in a scheduled session.', 'nymia')));
                return;
            }
        }
        
        // Check if there's an active instant call
        $active_call = get_user_meta($user_id, 'nymia_active_instant_call', true);
        if (!empty($active_call)) {
            wp_send_json_error(array('message' => __('You have an active instant call. Please end it first.', 'nymia')));
            return;
        }
        
        if ($per_minute_price <= 0) {
            wp_send_json_error(array('message' => __('Please set a price per minute before enabling availability.', 'nymia')));
            return;
        }
    }

    // Handle online thumbnail upload
    $online_thumbnail_url = '';
    if (!empty($_FILES['online_thumbnail']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        $upload = wp_handle_upload($_FILES['online_thumbnail'], array('test_form' => false));
        if (!isset($upload['error']) && isset($upload['url'])) {
            $online_thumbnail_url = $upload['url'];
        }
    }

    update_user_meta($user_id, 'nymia_available_now', $is_available ? '1' : '0');
    if ($is_available && $per_minute_price > 0) {
        update_user_meta($user_id, 'nymia_available_per_minute_price', $per_minute_price);
    }
    
    // Save online thumbnail if uploaded
    if (!empty($online_thumbnail_url)) {
        update_user_meta($user_id, 'nymia_available_thumbnail', $online_thumbnail_url);
    }

    wp_send_json_success(array(
        'is_available' => $is_available,
        'message' => $is_available ? __('You are now available for instant calls.', 'nymia') : __('Availability turned off.', 'nymia')
    ));
}
add_action('wp_ajax_nymia_toggle_availability', 'nymia_toggle_availability');

/**
 * Update availability per minute price
 */
function nymia_update_availability_price() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_availability', 'nonce');

    $user_id = get_current_user_id();
    $per_minute_price = isset($_POST['per_minute_price']) ? floatval($_POST['per_minute_price']) : 0;

    if ($per_minute_price <= 0) {
        wp_send_json_error(array('message' => __('Price must be greater than 0.', 'nymia')));
        return;
    }

    update_user_meta($user_id, 'nymia_available_per_minute_price', $per_minute_price);

    wp_send_json_success(array('message' => __('Price updated successfully.', 'nymia')));
}
add_action('wp_ajax_nymia_update_availability_price', 'nymia_update_availability_price');

/**
 * Check if creator is available for instant calls
 */
function nymia_is_creator_available($creator_id) {
    if (!$creator_id) return false;
    
    $is_available = get_user_meta($creator_id, 'nymia_available_now', true) === '1';
    if (!$is_available) return false;
    
    // Check if creator is in a scheduled session
    $schedules = nymia_get_creator_stream_schedules($creator_id);
    $now = current_time('timestamp');
    
    foreach ($schedules as $schedule) {
        if (empty($schedule['start_timestamp'])) continue;
        
        $start_time = intval($schedule['start_timestamp']);
        $duration = intval($schedule['duration'] ?? 60);
        $end_time = $start_time + ($duration * 60);
        
        if ($now >= $start_time && $now <= $end_time) {
            return false;
        }
    }
    
    // Check if creator has an active instant call
    $active_call = get_user_meta($creator_id, 'nymia_active_instant_call', true);
    if (!empty($active_call)) {
        return false;
    }
    
    return true;
}

/**
 * Initiate instant call (pay-per-minute)
 */
function nymia_initiate_instant_call() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to call.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_instant_call', 'nonce');

    $creator_id = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : 0;
    $customer_id = get_current_user_id();

    if ($creator_id <= 0 || $creator_id === $customer_id) {
        wp_send_json_error(array('message' => __('Invalid creator.', 'nymia')));
        return;
    }

    // Check if creator is available
    if (!nymia_is_creator_available($creator_id)) {
        wp_send_json_error(array('message' => __('This creator is not available for instant calls right now.', 'nymia')));
        return;
    }

    // Get per minute price
    $per_minute_price = floatval(get_user_meta($creator_id, 'nymia_available_per_minute_price', true));
    if ($per_minute_price <= 0) {
        wp_send_json_error(array('message' => __('Creator has not set a price per minute.', 'nymia')));
        return;
    }

    // Create a temporary room for the instant call
    $room_id = 'instant_' . $creator_id . '_' . time() . '_' . wp_generate_password(8, false);
    $stream_title = sprintf(__('Instant Call with %s', 'nymia'), get_userdata($creator_id)->display_name);

    // Get Stripe keys
    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    $stripe_mode = get_option('nymia_stripe_mode', 'test');
    $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));

    if (empty($stripe_secret_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured. Please contact the administrator.', 'nymia')));
        return;
    }

    // For instant calls, we'll pre-authorize a minimum amount (e.g., 5 minutes)
    // Then track actual usage and capture the correct amount after the call
    $preauth_minutes = 5; // Pre-authorize for 5 minutes
    $preauth_amount = $per_minute_price * $preauth_minutes;

    $success_url = add_query_arg(array(
        'type' => 'instant_call',
        'room_id' => $room_id,
        'creator_id' => $creator_id,
        'payment' => 'success',
        'session_id' => '{CHECKOUT_SESSION_ID}'
    ), home_url('/checkout'));
    $success_url = str_replace(array('%7B', '%7D'), array('{', '}'), $success_url);
    $cancel_url = home_url('/?call=cancelled');

    // Create Stripe Checkout Session with manual capture (pre-authorization)
    $session_data = array(
        'payment_method_types' => array('card'),
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'payment_intent_data' => array(
            'capture_method' => 'manual', // Pre-authorize, capture later
        ),
        'line_items' => array(
            array(
                'price_data' => array(
                    'currency' => strtolower($currency),
                    'product_data' => array(
                        'name' => $stream_title . ' - Instant Call (Pre-authorization)',
                        'description' => 'Instant call with ' . get_userdata($creator_id)->display_name . ' - Pay per minute (pre-authorized for ' . $preauth_minutes . ' minutes)',
                    ),
                    'unit_amount' => round($preauth_amount * 100), // Convert to cents
                ),
                'quantity' => 1,
            ),
        ),
        'metadata' => array(
            'item_type' => 'instant_call',
            'room_id' => $room_id,
            'user_id' => $customer_id,
            'creator_id' => $creator_id,
            'per_minute_price' => (string)$per_minute_price,
            'preauth_minutes' => (string)$preauth_minutes,
            'stream_title' => $stream_title,
            'wp_site_url' => home_url(),
        ),
    );

    // Create session via Stripe API
    $response = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query($session_data),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => __('Failed to create checkout session. Please try again.', 'nymia')));
        return;
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['url'])) {
        wp_send_json_success(array('checkout_url' => $response_body['url']));
    } else {
        $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create checkout session.', 'nymia');
        wp_send_json_error(array('message' => $error_message));
    }
}
add_action('wp_ajax_nymia_initiate_instant_call', 'nymia_initiate_instant_call');

/**
 * Send tip to creator
 */
if (!function_exists('nymia_send_tip')) {
function nymia_send_tip() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to send a tip.', 'nymia')), 403);
        return;
    }

    check_ajax_referer('nymia_tip', 'nonce');

    $creator_id = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : 0;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $message = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '';
    $customer_id = get_current_user_id();

    if ($creator_id <= 0 || $creator_id === $customer_id) {
        wp_send_json_error(array('message' => __('Invalid creator.', 'nymia')));
        return;
    }

    // Validate creator exists and is a creator
    $creator = get_user_by('id', $creator_id);
    if (!$creator) {
        wp_send_json_error(array('message' => __('Creator not found.', 'nymia')));
        return;
    }

    // Check if user is a verified creator
    $is_creator = nymia_is_creator_verified($creator_id);
    if (!$is_creator) {
        wp_send_json_error(array('message' => __('This user is not a verified creator.', 'nymia')));
        return;
    }

    // Validate amount
    if ($amount <= 0) {
        wp_send_json_error(array('message' => __('Please enter a valid tip amount.', 'nymia')));
        return;
    }

    if ($amount < 1) {
        wp_send_json_error(array('message' => __('Minimum tip amount is $1.00.', 'nymia')));
        return;
    }

    // Get Stripe keys
    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    $stripe_publishable_key = get_option('nymia_stripe_publishable_key', '');
    $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));

    if (empty($stripe_secret_key) || empty($stripe_publishable_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured. Please contact the administrator.', 'nymia')));
        return;
    }

    $current_user = wp_get_current_user();
    $creator_name = $creator->display_name ?: $creator->user_login;
    $customer_name = $current_user->display_name ?: $current_user->user_login;

    // Create success and cancel URLs
    $success_url = add_query_arg(array(
        'type' => 'tip',
        'creator_id' => $creator_id,
        'payment' => 'success',
        'session_id' => '{CHECKOUT_SESSION_ID}'
    ), home_url('/checkout'));
    $success_url = str_replace(array('%7B', '%7D'), array('{', '}'), $success_url);
    $cancel_url = add_query_arg(array(
        'type' => 'tip',
        'creator_id' => $creator_id,
        'payment' => 'cancelled'
    ), home_url('/checkout'));

    // Create Stripe checkout session using WordPress HTTP API
    // Build the request body manually to handle nested arrays properly
    $body_params = array(
        'payment_method_types[0]' => 'card',
        'line_items[0][price_data][currency]' => strtolower($currency),
        'line_items[0][price_data][product_data][name]' => sprintf(__('Tip to %s', 'nymia'), $creator_name),
        'line_items[0][price_data][product_data][description]' => !empty($message) ? $message : sprintf(__('Tip from %s', 'nymia'), $customer_name),
        'line_items[0][price_data][unit_amount]' => round($amount * 100), // Convert to cents
        'line_items[0][quantity]' => 1,
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'metadata[item_type]' => 'tip',
        'metadata[creator_id]' => (string)$creator_id,
        'metadata[customer_id]' => (string)$customer_id,
        'metadata[amount]' => (string)$amount,
        'metadata[currency]' => $currency,
        'customer_email' => $current_user->user_email,
    );
    
    // Add message to metadata if provided
    if (!empty($message)) {
        $body_params['metadata[message]'] = $message;
    }

    // Create session via Stripe API
    $response = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query($body_params),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        error_log('Stripe tip error: ' . $response->get_error_message());
        wp_send_json_error(array('message' => __('Failed to create checkout session. Please try again.', 'nymia')));
        return;
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    $code = wp_remote_retrieve_response_code($response);

    if ($code >= 400 || !isset($response_body['url'])) {
        $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create checkout session.', 'nymia');
        error_log('Stripe tip error: ' . $error_message);
        wp_send_json_error(array('message' => $error_message));
        return;
    }

    wp_send_json_success(array('checkout_url' => $response_body['url']));
}
}
add_action('wp_ajax_nymia_send_tip', 'nymia_send_tip');

/**
 * Track instant call start (when user actually joins the room)
 */
function nymia_track_call_start() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_track_call', 'nonce');

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
    $user_id = get_current_user_id();

    if (empty($session_id) || empty($room_id)) {
        wp_send_json_error(array('message' => __('Missing required parameters.', 'nymia')));
        return;
    }

    // Update customer's call record
    $calls = get_user_meta($user_id, 'nymia_instant_calls', true);
    if (!is_array($calls)) {
        wp_send_json_error(array('message' => __('Call record not found.', 'nymia')));
        return;
    }

    $call_found = false;
    $creator_id = 0;
    foreach ($calls as $key => $call) {
        if (isset($call['session_id']) && $call['session_id'] === $session_id && isset($call['room_id']) && $call['room_id'] === $room_id) {
            if ($call['status'] === 'pending_start') {
                $calls[$key]['start_time'] = current_time('timestamp');
                $calls[$key]['status'] = 'active';
                $creator_id = isset($call['creator_id']) ? intval($call['creator_id']) : 0;
                $call_found = true;
                break;
            }
        }
    }

    if (!$call_found) {
        wp_send_json_error(array('message' => __('Call already started or not found.', 'nymia')));
        return;
    }

    update_user_meta($user_id, 'nymia_instant_calls', $calls);

    // Update creator's active call record
    if ($creator_id > 0) {
        $active_call = get_user_meta($creator_id, 'nymia_active_instant_call', true);
        if (is_array($active_call) && isset($active_call['session_id']) && $active_call['session_id'] === $session_id) {
            $active_call['start_time'] = current_time('timestamp');
            $active_call['status'] = 'active';
            update_user_meta($creator_id, 'nymia_active_instant_call', $active_call);
        }
    }

    wp_send_json_success(array('message' => __('Call start tracked.', 'nymia')));
}
add_action('wp_ajax_nymia_track_call_start', 'nymia_track_call_start');

/**
 * Track instant call end and capture final payment
 */
function nymia_track_call_end() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_track_call', 'nonce');

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
    $user_id = get_current_user_id();

    if (empty($session_id) || empty($room_id)) {
        wp_send_json_error(array('message' => __('Missing required parameters.', 'nymia')));
        return;
    }

    // Get call record
    $calls = get_user_meta($user_id, 'nymia_instant_calls', true);
    if (!is_array($calls)) {
        wp_send_json_error(array('message' => __('Call record not found.', 'nymia')));
        return;
    }

    $call_data = null;
    $call_key = null;
    foreach ($calls as $key => $call) {
        if (isset($call['session_id']) && $call['session_id'] === $session_id && isset($call['room_id']) && $call['room_id'] === $room_id) {
            if ($call['status'] === 'active' || $call['status'] === 'pending_start') {
                $call_data = $call;
                $call_key = $key;
                break;
            }
        }
    }

    if (!$call_data) {
        wp_send_json_error(array('message' => __('Active call not found.', 'nymia')));
        return;
    }

    $end_time = current_time('timestamp');
    $start_time = isset($call_data['start_time']) ? intval($call_data['start_time']) : 0;
    
    // If call never actually started (pending_start), set start_time to now to avoid negative duration
    if ($start_time <= 0) {
        $start_time = $end_time;
    }

    // Calculate actual minutes used (round up to nearest minute)
    $duration_seconds = max(0, $end_time - $start_time);
    $minutes_used = max(1, ceil($duration_seconds / 60)); // Minimum 1 minute
    
    $per_minute_price = isset($call_data['per_minute_price']) ? floatval($call_data['per_minute_price']) : 0;
    $final_amount = $per_minute_price * $minutes_used;
    $preauth_amount = isset($call_data['preauth_amount']) ? floatval($call_data['preauth_amount']) : 0;
    $currency = isset($call_data['currency']) ? strtolower($call_data['currency']) : 'usd';
    
    // Get Stripe keys
    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    if (empty($stripe_secret_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured.', 'nymia')));
        return;
    }

    $payment_intent_id = isset($call_data['payment_intent_id']) ? $call_data['payment_intent_id'] : '';
    if (empty($payment_intent_id)) {
        wp_send_json_error(array('message' => __('Payment intent not found.', 'nymia')));
        return;
    }

    $final_amount_cents = round($final_amount * 100);
    $preauth_amount_cents = round($preauth_amount * 100);
    
    // Update payment intent amount to final amount (Stripe allows this before capture)
    // If final amount is less than preauth, we capture less. If more, we capture more (up to Stripe's limits)
    $update_data = array(
        'amount' => $final_amount_cents,
    );
    
    $update_response = wp_remote_post('https://api.stripe.com/v1/payment_intents/' . $payment_intent_id, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query($update_data),
        'method' => 'POST',
        'timeout' => 30,
    ));

    // If update fails, log error but proceed with capturing preauth amount
    if (is_wp_error($update_response)) {
        error_log('NYMIA: Failed to update payment intent amount: ' . $update_response->get_error_message());
        error_log('NYMIA: Final amount: ' . $final_amount . ', Preauth amount: ' . $preauth_amount);
        // Fall back to capturing preauth amount
        $final_amount_cents = $preauth_amount_cents;
        $final_amount = $preauth_amount; // Update final_amount for record keeping
    } else {
        $update_body = json_decode(wp_remote_retrieve_body($update_response), true);
        // Check if update was successful
        if (isset($update_body['error'])) {
            error_log('NYMIA: Payment intent update error: ' . $update_body['error']['message']);
            $final_amount_cents = $preauth_amount_cents;
            $final_amount = $preauth_amount;
        }
    }

    // Capture the payment (either updated amount or preauth amount as fallback)
    $capture_response = wp_remote_post('https://api.stripe.com/v1/payment_intents/' . $payment_intent_id . '/capture', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query(array()),
        'method' => 'POST',
        'timeout' => 30,
    ));

    if (is_wp_error($capture_response)) {
        error_log('NYMIA: Failed to capture payment: ' . $capture_response->get_error_message());
        wp_send_json_error(array('message' => __('Failed to capture payment. Please contact support.', 'nymia')));
        return;
    }

    $capture_body = json_decode(wp_remote_retrieve_body($capture_response), true);
    
    // Update call record
    $calls[$call_key]['end_time'] = $end_time;
    $calls[$call_key]['minutes_used'] = $minutes_used;
    $calls[$call_key]['final_amount'] = $final_amount;
    $calls[$call_key]['status'] = 'completed';
    update_user_meta($user_id, 'nymia_instant_calls', $calls);

    // Clear creator's active call
    $creator_id = isset($call_data['creator_id']) ? intval($call_data['creator_id']) : 0;
    if ($creator_id > 0) {
        $active_call = get_user_meta($creator_id, 'nymia_active_instant_call', true);
        if (is_array($active_call) && isset($active_call['session_id']) && $active_call['session_id'] === $session_id) {
            delete_user_meta($creator_id, 'nymia_active_instant_call');
        }
    }

    wp_send_json_success(array(
        'message' => __('Call ended and payment processed.', 'nymia'),
        'minutes_used' => $minutes_used,
        'final_amount' => $final_amount,
    ));
}
add_action('wp_ajax_nymia_track_call_end', 'nymia_track_call_end');

// ========================================
// STRIPE CHECKOUT INTEGRATION
// ========================================

/**
 * CHECK IF USER HAS AUDIO ACCESS
 * -------------------------------
 * Checks if a user has purchased/accessed a specific audio track
 */
if (!function_exists('nymia_user_has_audio_access')) {
    function nymia_user_has_audio_access($user_id, $audio_id) {
        if (!$user_id || !$audio_id) {
            return false;
        }
        $unlocked = get_user_meta($user_id, 'nymia_audio_unlocked', true);
        if (!is_array($unlocked)) {
            return false;
        }
        $audio_id_str = (string)$audio_id;
        foreach ($unlocked as $id) {
            if ((string)$id === $audio_id_str) {
                return true;
            }
        }
        return false;
    }
}

/**
 * GRANT AUDIO ACCESS TO USER
 * ---------------------------
 * Adds an audio ID to user's unlocked list
 */
function nymia_grant_audio_access($user_id, $audio_id) {
    if (!$user_id || !$audio_id) {
        return false;
    }
    $unlocked = get_user_meta($user_id, 'nymia_audio_unlocked', true);
    if (!is_array($unlocked)) {
        $unlocked = array();
    }
    $audio_id_str = (string)$audio_id;
    $exists = false;
    foreach ($unlocked as $id) {
        if ((string)$id === $audio_id_str) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $unlocked[] = $audio_id_str;
        update_user_meta($user_id, 'nymia_audio_unlocked', array_values($unlocked));
    }
    return true;
}

/**
 * UPDATE AUDIO ACCESS FILTER
 * ---------------------------
 * Checks for purchased audio in the access filter
 */
function nymia_check_purchased_audio_access($allow, $user_id, $creator_id) {
    // This will be checked per track, not per creator
    // For now, just return the default allow status
    return $allow;
}
add_filter('nymia_user_can_access_paid_audio', 'nymia_check_purchased_audio_access', 10, 3);

/**
 * CREATE STRIPE CHECKOUT SESSION
 * -------------------------------
 * AJAX handler to create a Stripe Checkout Session for Audio or Ebook purchase
 */
function nymia_create_checkout_session() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to purchase.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_checkout', 'nonce');

    $current_user_id = get_current_user_id();
    $cart_checkout = isset($_POST['cart_checkout']) && $_POST['cart_checkout'] === '1';
    
    // Save billing information if provided
    if (isset($_POST['billing_data'])) {
        $billing_data = json_decode(stripslashes($_POST['billing_data']), true);
        if (is_array($billing_data)) {
            // Save billing information to user meta
            if (isset($billing_data['first_name'])) {
                update_user_meta($current_user_id, 'first_name', sanitize_text_field($billing_data['first_name']));
            }
            if (isset($billing_data['last_name'])) {
                update_user_meta($current_user_id, 'last_name', sanitize_text_field($billing_data['last_name']));
            }
            if (isset($billing_data['email'])) {
                $email = sanitize_email($billing_data['email']);
                if (is_email($email)) {
                    wp_update_user(array('ID' => $current_user_id, 'user_email' => $email));
                }
            }
            if (isset($billing_data['phone'])) {
                update_user_meta($current_user_id, 'phone', sanitize_text_field($billing_data['phone']));
            }
            if (isset($billing_data['street'])) {
                update_user_meta($current_user_id, 'street', sanitize_text_field($billing_data['street']));
            }
            if (isset($billing_data['city'])) {
                update_user_meta($current_user_id, 'city', sanitize_text_field($billing_data['city']));
            }
            if (isset($billing_data['state'])) {
                update_user_meta($current_user_id, 'state', sanitize_text_field($billing_data['state']));
            }
            if (isset($billing_data['postcode'])) {
                update_user_meta($current_user_id, 'postcode', sanitize_text_field($billing_data['postcode']));
            }
            if (isset($billing_data['country'])) {
                update_user_meta($current_user_id, 'country', sanitize_text_field($billing_data['country']));
            }
        }
    }
    
    // Handle cart checkout
    if ($cart_checkout) {
        $cart = function_exists('nymia_get_cart') ? nymia_get_cart() : array();
        
        if (empty($cart)) {
            wp_send_json_error(array('message' => __('Your cart is empty.', 'nymia')));
            return;
        }
        
        // Validate cart items
        $valid_cart = array();
        foreach ($cart as $item) {
            $item_type = $item['type'] ?? '';
            $item_id = $item['id'] ?? '';
            
            if (empty($item_type) || empty($item_id)) {
                continue;
            }
            
            // Check if already purchased
            $has_access = false;
            if ($item_type === 'ebook' && function_exists('nymia_user_has_ebook_access')) {
                $has_access = nymia_user_has_ebook_access($current_user_id, $item_id);
            } elseif ($item_type === 'audio' && function_exists('nymia_user_has_audio_access')) {
                $has_access = nymia_user_has_audio_access($current_user_id, $item_id);
            }
            
            if (!$has_access) {
                $valid_cart[] = $item;
            }
        }
        
        if (empty($valid_cart)) {
            wp_send_json_error(array('message' => __('All items in your cart are already purchased.', 'nymia')));
            return;
        }
        
        // Get Stripe keys
        $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
        $stripe_mode = get_option('nymia_stripe_mode', 'test');
        $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
        
        if (empty($stripe_secret_key)) {
            wp_send_json_error(array('message' => __('Stripe is not configured. Please contact the administrator.', 'nymia')));
            return;
        }
        
        // Build line items for cart
        $line_items = array();
        $total_amount = 0;
        $cart_metadata = array();
        
        foreach ($valid_cart as $index => $item) {
            $item_type = $item['type'] ?? '';
            $item_id = $item['id'] ?? '';
            $item_title = $item['title'] ?? ($item_type === 'ebook' ? 'Ebook' : 'Audio Track');
            $item_price = (float)($item['price'] ?? 0);
            $item_image = $item['image'] ?? '';
            
            if ($item_price <= 0) {
                continue;
            }
            
            $line_items[] = array(
                'price_data' => array(
                    'currency' => strtolower($currency),
                    'product_data' => array(
                        'name' => $item_title,
                        'images' => !empty($item_image) ? array($item_image) : array(),
                    ),
                    'unit_amount' => round($item_price * 100), // Convert to cents
                ),
                'quantity' => 1,
            );
            
            $total_amount += $item_price;
            $cart_metadata['item_' . $index . '_type'] = $item_type;
            $cart_metadata['item_' . $index . '_id'] = $item_id;
        }
        
        if (empty($line_items)) {
            wp_send_json_error(array('message' => __('No valid items to purchase.', 'nymia')));
            return;
        }
        
        // Get current user
        $current_user = wp_get_current_user();
        $customer_email = $current_user->user_email;
        
        // Get or create Stripe Customer
        $stripe_customer_id = nymia_get_or_create_stripe_customer($current_user_id);
        if (is_wp_error($stripe_customer_id)) {
            $stripe_customer_id = null;
        }
        
        // Create Stripe Checkout Session for cart
        $success_url = add_query_arg(array(
            'payment' => 'success',
            'session_id' => '{CHECKOUT_SESSION_ID}',
            'cart' => '1'
        ), home_url('/checkout'));
        $success_url = str_replace(array('%7B', '%7D'), array('{', '}'), $success_url);
        $cancel_url = home_url('/cart');
        
        $session_data = array(
            'payment_method_types' => array('card'),
            'mode' => 'payment',
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
            'payment_intent_data' => array(
                'setup_future_usage' => 'on_session',
            ),
            'line_items' => $line_items,
            'billing_address_collection' => 'required', // Require billing address in Stripe Checkout
            'metadata' => array_merge(array(
                'cart_checkout' => '1',
                'user_id' => $current_user->ID,
                'item_count' => count($valid_cart),
                'wp_site_url' => home_url(),
            ), $cart_metadata),
        );
        
        if ($stripe_customer_id) {
            $session_data['customer'] = $stripe_customer_id;
        } else {
            $session_data['customer_email'] = $customer_email;
        }
        
        // Create session via Stripe API
        $response = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $stripe_secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => http_build_query($session_data),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('Failed to create checkout session. Please try again.', 'nymia')));
            return;
        }
        
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($response_body['id'])) {
            wp_send_json_success(array('sessionId' => $response_body['id']));
        } else {
            $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create checkout session.', 'nymia');
            wp_send_json_error(array('message' => $error_message));
        }
        
        return; // Exit early for cart checkout
    }
    
    // Original single item checkout logic (for backward compatibility)
    $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : '';
    $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';

    if (!in_array($item_type, array('audio', 'ebook'))) {
        wp_send_json_error(array('message' => __('Invalid item type.', 'nymia')));
        return;
    }

    if (empty($item_id)) {
        wp_send_json_error(array('message' => __('Invalid item ID.', 'nymia')));
        return;
    }

    // Get Stripe keys
    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    $stripe_mode = get_option('nymia_stripe_mode', 'test');
    $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));

    if (empty($stripe_secret_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured. Please contact the administrator.', 'nymia')));
        return;
    }

    $item = null;
    $item_title = '';
    $item_price = 0;
    $item_image = '';
    $success_url = '';
    $cancel_url = '';
    $creator_id = 0;

    // Fetch item data
    if ($item_type === 'ebook') {
        $all_ebooks = function_exists('nymia_get_all_ebooks') ? nymia_get_all_ebooks() : array();
        foreach ($all_ebooks as $ebook) {
            if (!empty($ebook['id']) && (string)$ebook['id'] === (string)$item_id) {
                $item = $ebook;
                break;
            }
        }

        if (!$item) {
            wp_send_json_error(array('message' => __('Ebook not found.', 'nymia')));
            return;
        }

        // Check if already purchased
        $current_user_id = get_current_user_id();
        if (function_exists('nymia_user_has_ebook_access')) {
            if (nymia_user_has_ebook_access($current_user_id, $item_id)) {
                wp_send_json_error(array('message' => __('You already have access to this ebook.', 'nymia')));
                return;
            }
        }

        $creator_id = isset($item['user_id']) ? intval($item['user_id']) : 0;
        $item_title = $item['title'] ?? 'Ebook';
        $item_price = (float)($item['price'] ?? 0);
        $item_image = $item['thumbnail'] ?? $item['image'] ?? '';
        $success_url = add_query_arg(array(
            'type' => 'ebook',
            'id' => $item_id,
            'payment' => 'success',
            'session_id' => '{CHECKOUT_SESSION_ID}'
        ), home_url('/checkout'));
        $success_url = str_replace(array('%7B', '%7D'), array('{', '}'), $success_url);
        $cancel_url = home_url('/checkout?type=ebook&id=' . urlencode($item_id));

    } elseif ($item_type === 'audio') {
        $all_audio = get_transient('nymia_all_audio');
        if ($all_audio && is_array($all_audio)) {
            foreach ($all_audio as $audio) {
                if (!empty($audio['id']) && (string)$audio['id'] === (string)$item_id) {
                    $item = $audio;
                    break;
                }
            }
        }

        if (!$item) {
            wp_send_json_error(array('message' => __('Audio track not found.', 'nymia')));
            return;
        }

        // Check if already purchased
        $current_user_id = get_current_user_id();
        if (function_exists('nymia_user_has_audio_access')) {
            if (nymia_user_has_audio_access($current_user_id, $item_id)) {
                wp_send_json_error(array('message' => __('You already have access to this audio track.', 'nymia')));
                return;
            }
        }

        $creator_id = isset($item['user_id']) ? intval($item['user_id']) : 0;
        if (!$creator_id && !empty($item['id'])) {
            $owner_meta = nymia_get_audio_owner_id($item['id']);
            if ($owner_meta) {
                $creator_id = $owner_meta;
                nymia_sync_audio_creator_reference($item['id'], $creator_id);
            }
        }
        $item_title = $item['title'] ?? 'Audio Track';
        $item_price = (float)($item['price'] ?? 0);
        $item_image = $item['cover_image'] ?? '';
        $success_url = add_query_arg(array(
            'type' => 'audio',
            'id' => $item_id,
            'payment' => 'success',
            'session_id' => '{CHECKOUT_SESSION_ID}'
        ), home_url('/checkout'));
        $success_url = str_replace(array('%7B', '%7D'), array('{', '}'), $success_url);
        $cancel_url = home_url('/checkout?type=audio&id=' . urlencode($item_id));
    }

    // Validate price
    if ($item_price <= 0) {
        wp_send_json_error(array('message' => __('Invalid price.', 'nymia')));
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();
    $customer_email = $current_user->user_email;
    $current_user_id = get_current_user_id();
    
    // Get or create Stripe Customer for saving payment methods
    $stripe_customer_id = nymia_get_or_create_stripe_customer($current_user_id);
    if (is_wp_error($stripe_customer_id)) {
        // If customer creation fails, continue without customer (payment methods won't be saved)
        $stripe_customer_id = null;
    }

    // Create Stripe Checkout Session
    $session_data = array(
        'payment_method_types' => array('card'),
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'payment_intent_data' => array(
            'setup_future_usage' => 'on_session', // Save payment method for future use
        ),
        'customer_email' => $customer_email,
        'line_items' => array(
            array(
                'price_data' => array(
                    'currency' => strtolower($currency),
                    'product_data' => array(
                        'name' => $item_title,
                        'images' => !empty($item_image) ? array($item_image) : array(),
                    ),
                    'unit_amount' => round($item_price * 100), // Convert to cents
                ),
                'quantity' => 1,
            ),
        ),
        'metadata' => array(
            'item_type'  => $item_type,
            'item_id'    => $item_id,
            'user_id'    => $current_user->ID,
            'creator_id' => $creator_id ?: 0,
            'wp_site_url'=> home_url(),
        ),
    );
    
    // Add customer to session if available
    if ($stripe_customer_id) {
        $session_data['customer'] = $stripe_customer_id;
    } else {
        $session_data['customer_email'] = $customer_email;
    }

    // Create session via Stripe API
    $response = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query($session_data),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => __('Failed to create checkout session. Please try again.', 'nymia')));
        return;
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['id'])) {
        wp_send_json_success(array('sessionId' => $response_body['id']));
    } else {
        $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create checkout session.', 'nymia');
        wp_send_json_error(array('message' => $error_message));
    }
}
add_action('wp_ajax_nymia_create_checkout_session', 'nymia_create_checkout_session');

/**
 * Check if user has valid booking for a room
 * 
 * @param int $user_id User ID
 * @param string $room_id Room ID
 * @return bool|array Returns false if no valid booking, or booking data if valid
 */
function nymia_user_has_valid_booking($user_id, $room_id) {
    $bookings = get_user_meta($user_id, 'nymia_live_bookings', true);
    if (!is_array($bookings)) {
        return false;
    }
    
    foreach ($bookings as $booking) {
        if (isset($booking['room_id']) && $booking['room_id'] === $room_id) {
            $payment_type = isset($booking['payment_type']) ? $booking['payment_type'] : 'full';
            $expires_at = isset($booking['expires_at']) ? $booking['expires_at'] : '';
            
            // Full session bookings never expire
            if ($payment_type === 'full') {
                return $booking;
            }
            
            // For per-minute bookings, check remaining minutes based on actual usage
            if ($payment_type === 'per_minute') {
                $minutes_purchased = isset($booking['minutes_purchased']) ? intval($booking['minutes_purchased']) : (isset($booking['minutes']) ? intval($booking['minutes']) : 0);
                $minutes_used = isset($booking['minutes_used']) ? intval($booking['minutes_used']) : 0;
                
                // Also check if there's an active session that hasn't been closed
                if (isset($booking['join_sessions']) && is_array($booking['join_sessions'])) {
                    $current_time = current_time('timestamp');
                    foreach ($booking['join_sessions'] as $session) {
                        if (isset($session['start']) && (!isset($session['end']) || $session['end'] == 0)) {
                            // Active session - add current time to used minutes
                            $active_session_seconds = $current_time - $session['start'];
                            $active_session_minutes = ceil($active_session_seconds / 60);
                            $minutes_used += $active_session_minutes;
                        }
                    }
                }
                
                // Calculate remaining minutes
                $remaining_minutes = $minutes_purchased - $minutes_used;
                
                // Return booking if there are remaining minutes
                if ($remaining_minutes > 0) {
                    return $booking;
                }
            }
        }
    }
    
    return false;
}

/**
 * Get or create Stripe Customer for a WordPress user
 * Stores the Stripe Customer ID in user meta for future use
 * 
 * @param int $user_id WordPress user ID
 * @return string|WP_Error Stripe Customer ID or WP_Error on failure
 */
if (!function_exists('nymia_get_or_create_stripe_customer')) {
    function nymia_get_or_create_stripe_customer($user_id) {
        if (!$user_id) {
            return new WP_Error('invalid_user', __('Invalid user ID.', 'nymia'));
        }

        // Check if user already has a Stripe Customer ID
        $stripe_customer_id = get_user_meta($user_id, 'nymia_stripe_customer_id', true);
        if (!empty($stripe_customer_id) && is_string($stripe_customer_id)) {
            return $stripe_customer_id;
        }

        // Get Stripe secret key
        $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
        if (empty($stripe_secret_key)) {
            return new WP_Error('stripe_not_configured', __('Stripe is not configured.', 'nymia'));
        }

        // Get user data
        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('user_not_found', __('User not found.', 'nymia'));
        }

        // Create Stripe Customer
        $customer_data = array(
            'email' => $user->user_email,
            'name' => $user->display_name ?: $user->user_login,
            'metadata' => array(
                'wp_user_id' => (string)$user_id,
                'wp_site_url' => home_url(),
            ),
        );

        $response = wp_remote_post('https://api.stripe.com/v1/customers', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $stripe_secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => http_build_query($customer_data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('stripe_api_error', __('Failed to create Stripe customer.', 'nymia'));
        }

        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 400 || !isset($response_body['id'])) {
            $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create Stripe customer.', 'nymia');
            return new WP_Error('stripe_error', $error_message);
        }

        // Store the Stripe Customer ID in user meta
        $stripe_customer_id = sanitize_text_field($response_body['id']);
        update_user_meta($user_id, 'nymia_stripe_customer_id', $stripe_customer_id);

        return $stripe_customer_id;
    }
}

/**
 * Create Payment Intent for embedded payment (Live Stream Booking)
 * Returns client_secret for Stripe Elements
 */
function nymia_create_live_stream_payment_intent() {
    // WordPress AJAX handlers should use wp_die() instead of exit
    // wp_send_json_* already handles output buffering and headers
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to book a live session.', 'nymia')));
        wp_die();
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'nymia_book_live_stream')) {
        wp_send_json_error(array('message' => __('Security check failed. Please refresh the page.', 'nymia')));
        wp_die();
    }

    $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
    $creator_id = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : 0;
    $stream_title = isset($_POST['stream_title']) ? sanitize_text_field($_POST['stream_title']) : '';
    $payment_type = isset($_POST['payment_type']) ? sanitize_text_field($_POST['payment_type']) : 'full';
    $minutes = isset($_POST['minutes']) ? intval($_POST['minutes']) : 0;
    $is_scheduled = isset($_POST['is_scheduled']) ? (intval($_POST['is_scheduled']) === 1) : false;
    $start_timestamp = isset($_POST['start_timestamp']) ? intval($_POST['start_timestamp']) : 0;
    $reminder_minutes = isset($_POST['reminder_minutes']) ? intval($_POST['reminder_minutes']) : 0;
    $allowed_reminders = array(0, 5, 15, 30, 60, 120);
    if (!in_array($reminder_minutes, $allowed_reminders, true)) {
        $reminder_minutes = 0;
    }

    if (empty($room_id) || empty($stream_title)) {
        wp_send_json_error(array('message' => __('Invalid booking information.', 'nymia')));
        return;
    }
    
    // Extract schedule_id if this is a scheduled stream
    $schedule_id = '';
    $schedule_data = null;
    if ($is_scheduled && strpos($room_id, 'scheduled_') === 0) {
        $parts = explode('_', $room_id, 3);
        if (count($parts) >= 3) {
            $schedule_id = $parts[2];
            if ($creator_id > 0) {
                $schedules = nymia_get_creator_stream_schedules($creator_id);
                foreach ($schedules as $schedule) {
                    if (isset($schedule['id']) && $schedule['id'] === $schedule_id) {
                        $schedule_data = $schedule;
                        break;
                    }
                }
            }
        }
    }

    // For scheduled events, check if it's an event-based payment
    $is_event_booking = false;
    $event_price = 0;
    $max_attendees = 0;
    $current_attendees = 0;
    
    $schedule_event_price = $schedule_data ? floatval($schedule_data['event_price'] ?? 0) : 0;

    if ($schedule_data && isset($schedule_data['event_type']) && $schedule_data['event_type'] === 'group') {
        $is_event_booking = true;
        $event_price = $schedule_event_price;
        $max_attendees = intval($schedule_data['max_attendees'] ?? 0);
        $current_attendees = count($schedule_data['attendees'] ?? array());
        
        if ($max_attendees > 0 && $current_attendees >= $max_attendees) {
            wp_send_json_error(array('message' => __('This event is full. Maximum attendees reached.', 'nymia')));
            wp_die();
        }
        
        $payment_type = 'full';
        $total_amount = $event_price;
    } else {
        if (!in_array($payment_type, array('full', 'per_minute'))) {
            wp_send_json_error(array('message' => __('Invalid payment type.', 'nymia')));
            wp_die();
        }

        $stream_price = 0;
        $per_minute_price = 0;
        
        if ($creator_id > 0) {
            $stream_price = floatval(get_user_meta($creator_id, 'nymia_stream_full_price', true));
            $per_minute_price = floatval(get_user_meta($creator_id, 'nymia_stream_per_minute_price', true));
        }
        
        if ($schedule_event_price > 0) {
            $stream_price = $schedule_event_price;
        }

        if ($stream_price <= 0) {
            $stream_price = floatval(get_option('nymia_default_stream_price', 9.99));
        }
        if ($per_minute_price <= 0) {
            $per_minute_price = floatval(get_option('nymia_default_per_minute_price', 0.99));
        }

        $total_amount = 0;
        if ($payment_type === 'full') {
            $total_amount = $stream_price;
        } else {
            if ($minutes < 1) {
                wp_send_json_error(array('message' => __('Please select at least 1 minute.', 'nymia')));
                return;
            }
            if ($minutes > 480) {
                wp_send_json_error(array('message' => __('Maximum 480 minutes allowed.', 'nymia')));
                return;
            }
            $total_amount = $per_minute_price * $minutes;
        }
    }

    if ($total_amount <= 0) {
        wp_send_json_error(array('message' => __('Invalid booking amount.', 'nymia')));
        wp_die();
    }

    // Get Stripe keys
    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    $stripe_publishable_key = get_option('nymia_stripe_publishable_key', '');
    $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));

    if (empty($stripe_secret_key) || empty($stripe_publishable_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured. Please contact the administrator.', 'nymia')));
        wp_die();
    }

    $current_user = wp_get_current_user();
    $current_user_id = get_current_user_id();
    
    // Get or create Stripe Customer
    $stripe_customer_id = nymia_get_or_create_stripe_customer($current_user_id);
    if (is_wp_error($stripe_customer_id)) {
        $stripe_customer_id = null;
    }

    // Create Payment Intent
    $payment_intent_data = array(
        'amount' => round($total_amount * 100), // Convert to cents
        'currency' => strtolower($currency),
        'payment_method_types' => array('card'),
        'setup_future_usage' => 'on_session', // Save payment method
        'metadata' => array(
            'item_type'  => 'live',
            'room_id'    => $room_id,
            'user_id'    => (string)$current_user_id,
            'creator_id' => (string)$creator_id,
            'payment_type' => $payment_type,
            'minutes'     => $payment_type === 'per_minute' ? (string)$minutes : '0',
            'is_scheduled' => $is_scheduled ? '1' : '0',
            'schedule_id' => $schedule_id,
            'start_timestamp' => $start_timestamp > 0 ? (string)$start_timestamp : '',
            'reminder_minutes' => (string)$reminder_minutes,
            'stream_title' => $stream_title,
            'is_event_booking' => $is_event_booking ? '1' : '0',
            'event_type' => $schedule_data && isset($schedule_data['event_type']) ? $schedule_data['event_type'] : 'single',
            'wp_site_url'=> home_url(),
        ),
    );
    
    if ($stripe_customer_id) {
        $payment_intent_data['customer'] = $stripe_customer_id;
    }

    $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query($payment_intent_data),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => __('Failed to create payment. Please try again.', 'nymia')));
        wp_die();
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    $code = wp_remote_retrieve_response_code($response);

    if ($code >= 400 || !isset($response_body['client_secret'])) {
        $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create payment intent.', 'nymia');
        wp_send_json_error(array('message' => $error_message));
        wp_die();
    }

    wp_send_json_success(array(
        'client_secret' => $response_body['client_secret'],
        'publishable_key' => $stripe_publishable_key,
        'amount' => $total_amount,
        'currency' => $currency,
    ));
    wp_die();
}
add_action('wp_ajax_nymia_create_live_stream_payment_intent', 'nymia_create_live_stream_payment_intent');

/**
 * Confirm Payment Intent after user submits payment form
 */
function nymia_confirm_live_stream_payment() {
    // WordPress AJAX handlers should use wp_die() instead of exit
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        wp_die();
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'nymia_book_live_stream')) {
        wp_send_json_error(array('message' => __('Security check failed. Please refresh the page.', 'nymia')));
        wp_die();
    }

    $payment_intent_id = isset($_POST['payment_intent_id']) ? sanitize_text_field($_POST['payment_intent_id']) : '';
    
    if (empty($payment_intent_id) || strpos($payment_intent_id, 'pi_') !== 0) {
        wp_send_json_error(array('message' => __('Invalid payment intent.', 'nymia')));
        wp_die();
    }

    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    if (empty($stripe_secret_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured.', 'nymia')));
        wp_die();
    }

    // Retrieve payment intent to verify status
    $response = wp_remote_get('https://api.stripe.com/v1/payment_intents/' . $payment_intent_id, array(
        'headers' => array('Authorization' => 'Bearer ' . $stripe_secret_key),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => __('Failed to verify payment.', 'nymia')));
        wp_die();
    }

    $payment_intent = json_decode(wp_remote_retrieve_body($response), true);
    $code = wp_remote_retrieve_response_code($response);

    if ($code >= 400) {
        $error_message = isset($payment_intent['error']['message']) ? $payment_intent['error']['message'] : __('Payment verification failed.', 'nymia');
        wp_send_json_error(array('message' => $error_message));
        wp_die();
    }

    // Check payment status
    if (!isset($payment_intent['status']) || $payment_intent['status'] !== 'succeeded') {
        wp_send_json_error(array('message' => __('Payment not completed.', 'nymia')));
        wp_die();
    }

    // Process the booking (similar to checkout success handler)
    $metadata = isset($payment_intent['metadata']) ? $payment_intent['metadata'] : array();
    $room_id = isset($metadata['room_id']) ? $metadata['room_id'] : '';
    $current_user_id = get_current_user_id();
    $session_user_id = isset($metadata['user_id']) ? intval($metadata['user_id']) : 0;

    if ($session_user_id !== $current_user_id) {
        wp_send_json_error(array('message' => __('Payment does not belong to your account.', 'nymia')));
        wp_die();
    }

    // Process booking using existing logic from checkout success handler
    $payment_type = isset($metadata['payment_type']) ? $metadata['payment_type'] : 'full';
    $minutes = isset($metadata['minutes']) ? intval($metadata['minutes']) : 0;
    $creator_id = isset($metadata['creator_id']) ? intval($metadata['creator_id']) : 0;
    $is_scheduled = isset($metadata['is_scheduled']) && $metadata['is_scheduled'] === '1';
    $schedule_id = isset($metadata['schedule_id']) ? sanitize_text_field($metadata['schedule_id']) : '';
    $start_timestamp = isset($metadata['start_timestamp']) ? intval($metadata['start_timestamp']) : 0;
    $reminder_minutes = isset($metadata['reminder_minutes']) ? intval($metadata['reminder_minutes']) : 0;
    $stream_title = isset($metadata['stream_title']) ? sanitize_text_field($metadata['stream_title']) : __('Live Stream', 'nymia');
    $is_event_booking = isset($metadata['is_event_booking']) && $metadata['is_event_booking'] === '1';
    $event_type = isset($metadata['event_type']) ? $metadata['event_type'] : 'single';

    // Track attendee for group events
    if ($is_event_booking && $event_type === 'group' && !empty($schedule_id) && $creator_id > 0) {
        $schedules = nymia_get_creator_stream_schedules($creator_id);
        $schedule_updated = false;
        foreach ($schedules as $key => $schedule) {
            if (isset($schedule['id']) && $schedule['id'] === $schedule_id) {
                if (!isset($schedule['attendees']) || !is_array($schedule['attendees'])) {
                    $schedule['attendees'] = array();
                }
                $already_registered = false;
                foreach ($schedule['attendees'] as $attendee) {
                    if (isset($attendee['user_id']) && $attendee['user_id'] === $current_user_id) {
                        $already_registered = true;
                        break;
                    }
                }
                if (!$already_registered) {
                    $current_user = wp_get_current_user();
                    $schedule['attendees'][] = array(
                        'user_id' => $current_user_id,
                        'user_name' => $current_user->display_name,
                        'user_email' => $current_user->user_email,
                        'booked_at' => current_time('timestamp'),
                        'session_id' => $payment_intent_id,
                    );
                    $schedules[$key] = $schedule;
                    $schedule_updated = true;
                }
                break;
            }
        }
        if ($schedule_updated) {
            nymia_save_creator_stream_schedules($creator_id, $schedules);
        }
    }
    
    // Store booking access
    $bookings = get_user_meta($current_user_id, 'nymia_live_bookings', true);
    if (!is_array($bookings)) {
        $bookings = array();
    }
    
    $booking_exists = false;
    foreach ($bookings as $booking) {
        if (isset($booking['session_id']) && $booking['session_id'] === $payment_intent_id) {
            $booking_exists = true;
            break;
        }
    }
    
    if (!$booking_exists) {
        $booking_data = array(
            'room_id' => $room_id,
            'creator_id' => $creator_id,
            'session_id' => $payment_intent_id,
            'payment_type' => $payment_type,
            'minutes' => $minutes,
            'amount' => isset($payment_intent['amount']) ? ($payment_intent['amount'] / 100) : 0,
            'currency' => isset($payment_intent['currency']) ? strtoupper($payment_intent['currency']) : 'USD',
            'booked_at' => current_time('mysql'),
            'expires_at' => $payment_type === 'full' ? '' : date('Y-m-d H:i:s', strtotime('+' . $minutes . ' minutes')),
            'reminder_minutes' => $reminder_minutes,
            'stream_title' => $stream_title,
            'is_scheduled' => $is_scheduled,
            'start_timestamp' => $start_timestamp,
        );
        
        // For per-minute bookings, track usage time
        if ($payment_type === 'per_minute') {
            $booking_data['minutes_purchased'] = $minutes;
            $booking_data['minutes_used'] = 0;
            $booking_data['first_join_time'] = 0;
            $booking_data['last_leave_time'] = 0;
            $booking_data['join_sessions'] = array();
        }
        
        if ($is_scheduled) {
            $booking_data['is_scheduled'] = true;
            $booking_data['schedule_id'] = $schedule_id;
            $booking_data['start_timestamp'] = $start_timestamp;
        }
        
        $bookings[] = $booking_data;
        update_user_meta($current_user_id, 'nymia_live_bookings', $bookings);
    } else {
        // Booking already exists, get it from the bookings array for email
        foreach ($bookings as $existing_booking) {
            if (isset($existing_booking['session_id']) && $existing_booking['session_id'] === $payment_intent_id) {
                $booking_data = $existing_booking;
                break;
            }
        }
    }
    
    // Schedule reminder if applicable
    if ($is_scheduled && $reminder_minutes > 0 && $start_timestamp > 0) {
        nymia_schedule_live_stream_reminder($payment_intent_id, $current_user_id, $creator_id, $stream_title, $room_id, $start_timestamp, $reminder_minutes, $schedule_id);
    }

    // Send invoice email to user (only send if booking_data exists and hasn't been sent before)
    if (isset($booking_data) && !isset($booking_data['invoice_sent'])) {
        nymia_send_booking_invoice_email($current_user_id, $payment_intent, $booking_data);
        
        // Mark invoice as sent to prevent duplicate emails
        if (!$booking_exists) {
            $booking_data['invoice_sent'] = true;
            // Update the booking in the array
            foreach ($bookings as $key => $booking) {
                if (isset($booking['session_id']) && $booking['session_id'] === $payment_intent_id) {
                    $bookings[$key] = $booking_data;
                    update_user_meta($current_user_id, 'nymia_live_bookings', $bookings);
                    break;
                }
            }
        }
    }

    wp_send_json_success(array(
        'message' => __('Payment successful! Booking confirmed.', 'nymia'),
        'room_id' => $room_id,
        'redirect_url' => home_url('/live-audio/?room_id=' . urlencode($room_id) . ($is_scheduled ? '&scheduled=1' : '')),
        'is_scheduled' => $is_scheduled,
        'start_timestamp' => $start_timestamp,
        'stream_title' => $stream_title,
        'schedule_id' => $schedule_id,
    ));
    wp_die();
}
add_action('wp_ajax_nymia_confirm_live_stream_payment', 'nymia_confirm_live_stream_payment');

/**
 * Send invoice email to user after successful payment
 * 
 * @param int $user_id WordPress user ID
 * @param array $payment_intent Stripe payment intent data
 * @param array $booking_data Booking information
 */
if (!function_exists('nymia_send_booking_invoice_email')) {
    function nymia_send_booking_invoice_email($user_id, $payment_intent, $booking_data) {
        $user = get_userdata($user_id);
        if (!$user || empty($user->user_email)) {
            return false;
        }

        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $user_name = $user->display_name ?: $user->user_login;
        $amount = isset($payment_intent['amount']) ? ($payment_intent['amount'] / 100) : 0;
        $currency = isset($payment_intent['currency']) ? strtoupper($payment_intent['currency']) : 'USD';
        $payment_intent_id = isset($payment_intent['id']) ? $payment_intent['id'] : '';
        
        // Format amount with currency symbol
        $formatted_amount = $currency . ' ' . number_format($amount, 2);
        
        // Get booking details
        $stream_title = isset($booking_data['stream_title']) ? $booking_data['stream_title'] : __('Live Stream', 'nymia');
        $payment_type = isset($booking_data['payment_type']) ? $booking_data['payment_type'] : 'full';
        $minutes = isset($booking_data['minutes']) ? intval($booking_data['minutes']) : 0;
        $booked_at = isset($booking_data['booked_at']) ? $booking_data['booked_at'] : current_time('mysql');
        $is_scheduled = isset($booking_data['is_scheduled']) ? $booking_data['is_scheduled'] : false;
        $start_timestamp = isset($booking_data['start_timestamp']) ? intval($booking_data['start_timestamp']) : 0;
        
        // Get creator info
        $creator_id = isset($booking_data['creator_id']) ? intval($booking_data['creator_id']) : 0;
        $creator_name = __('Unknown Creator', 'nymia');
        if ($creator_id > 0) {
            $creator = get_userdata($creator_id);
            if ($creator) {
                $creator_name = $creator->display_name ?: $creator->user_login;
            }
        }
        
        // Format booking date/time
        $booking_date = date_i18n(get_option('date_format'), strtotime($booked_at));
        $booking_time = date_i18n(get_option('time_format'), strtotime($booked_at));
        
        // Build email subject
        $subject = sprintf(__('Invoice for %s - %s', 'nymia'), $stream_title, $site_name);
        
        // Build email body (HTML)
        $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(145deg, #c7541a, #e66a2e); color: #fff; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
        .invoice-details { background: #fff; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #ddd; }
        .invoice-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .invoice-row:last-child { border-bottom: none; font-weight: bold; font-size: 1.1em; }
        .label { color: #666; }
        .value { color: #333; font-weight: 500; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9em; }
        .button { display: inline-block; padding: 12px 24px; background: #c7541a; color: #fff; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">' . esc_html($site_name) . '</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">' . esc_html__('Payment Invoice', 'nymia') . '</p>
        </div>
        <div class="content">
            <p>' . sprintf(esc_html__('Hello %s,', 'nymia'), esc_html($user_name)) . '</p>
            <p>' . esc_html__('Thank you for your purchase! Your payment has been successfully processed.', 'nymia') . '</p>
            
            <div class="invoice-details">
                <h2 style="margin-top: 0;">' . esc_html__('Invoice Details', 'nymia') . '</h2>
                
                <div class="invoice-row">
                    <span class="label">' . esc_html__('Item:', 'nymia') . '</span>
                    <span class="value">' . esc_html($stream_title) . '</span>
                </div>
                
                <div class="invoice-row">
                    <span class="label">' . esc_html__('Creator:', 'nymia') . '</span>
                    <span class="value">' . esc_html($creator_name) . '</span>
                </div>
                
                <div class="invoice-row">
                    <span class="label">' . esc_html__('Payment Type:', 'nymia') . '</span>
                    <span class="value">' . ($payment_type === 'full' ? esc_html__('Full Session', 'nymia') : sprintf(esc_html__('Pay Per Minute (%d minutes)', 'nymia'), $minutes)) . '</span>
                </div>';
        
        if ($is_scheduled && $start_timestamp > 0) {
            $start_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $start_timestamp);
            $message .= '
                <div class="invoice-row">
                    <span class="label">' . esc_html__('Scheduled Date:', 'nymia') . '</span>
                    <span class="value">' . esc_html($start_date) . '</span>
                </div>';
        }
        
        $message .= '
                <div class="invoice-row">
                    <span class="label">' . esc_html__('Booking Date:', 'nymia') . '</span>
                    <span class="value">' . esc_html($booking_date . ' ' . $booking_time) . '</span>
                </div>
                
                <div class="invoice-row">
                    <span class="label">' . esc_html__('Transaction ID:', 'nymia') . '</span>
                    <span class="value" style="font-family: monospace; font-size: 0.9em;">' . esc_html($payment_intent_id) . '</span>
                </div>
                
                <div class="invoice-row">
                    <span class="label">' . esc_html__('Amount Paid:', 'nymia') . '</span>
                    <span class="value" style="color: #c7541a; font-size: 1.2em;">' . esc_html($formatted_amount) . '</span>
                </div>
            </div>
            
            <p style="text-align: center;">
                <a href="' . esc_url(home_url('/live-audio/?room_id=' . urlencode($booking_data['room_id']))) . '" class="button">' . esc_html__('Access Your Session', 'nymia') . '</a>
            </p>
            
            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
                ' . esc_html__('If you have any questions about this invoice, please contact our support team.', 'nymia') . '
            </p>
        </div>
        <div class="footer">
            <p>' . sprintf(esc_html__('© %s %s. All rights reserved.', 'nymia'), date('Y'), esc_html($site_name)) . '</p>
        </div>
    </div>
</body>
</html>';
        
        // Set email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
        );
        
        // Send email
        return wp_mail($user->user_email, $subject, $message, $headers);
    }
}

/**
 * BOOK LIVE STREAM
 * ----------------
 * AJAX handler to create a Stripe Checkout Session for live stream booking
 * (Kept for backward compatibility, but will be replaced by payment intent flow)
 */
function nymia_book_live_stream() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to book a live session.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_book_live_stream', 'nonce');

    $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
    $creator_id = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : 0;
    $stream_title = isset($_POST['stream_title']) ? sanitize_text_field($_POST['stream_title']) : '';
    $payment_type = isset($_POST['payment_type']) ? sanitize_text_field($_POST['payment_type']) : 'full';
    $minutes = isset($_POST['minutes']) ? intval($_POST['minutes']) : 0;
    $is_scheduled = isset($_POST['is_scheduled']) ? (intval($_POST['is_scheduled']) === 1) : false;
    $start_timestamp = isset($_POST['start_timestamp']) ? intval($_POST['start_timestamp']) : 0;
    $reminder_minutes = isset($_POST['reminder_minutes']) ? intval($_POST['reminder_minutes']) : 0;
    $allowed_reminders = array(0, 5, 15, 30, 60, 120);
    if (!in_array($reminder_minutes, $allowed_reminders, true)) {
        $reminder_minutes = 0;
    }
    $reminder_minutes = isset($_POST['reminder_minutes']) ? intval($_POST['reminder_minutes']) : 0;
    $allowed_reminders = array(0, 5, 15, 30, 60, 120);
    if (!in_array($reminder_minutes, $allowed_reminders, true)) {
        $reminder_minutes = 0;
    }

    if (empty($room_id) || empty($stream_title)) {
        wp_send_json_error(array('message' => __('Invalid booking information.', 'nymia')));
        return;
    }
    
    // Extract schedule_id if this is a scheduled stream
    $schedule_id = '';
    $schedule_data = null;
    if ($is_scheduled && strpos($room_id, 'scheduled_') === 0) {
        // room_id format: scheduled_{creator_id}_{schedule_id}
        $parts = explode('_', $room_id, 3);
        if (count($parts) >= 3) {
            $schedule_id = $parts[2];
            // Get schedule data to check event type and pricing
            if ($creator_id > 0) {
                $schedules = nymia_get_creator_stream_schedules($creator_id);
                foreach ($schedules as $schedule) {
                    if (isset($schedule['id']) && $schedule['id'] === $schedule_id) {
                        $schedule_data = $schedule;
                        break;
                    }
                }
            }
        }
    }

    // For scheduled events, check if it's an event-based payment
    $is_event_booking = false;
    $event_price = 0;
    $max_attendees = 0;
    $current_attendees = 0;
    
    $schedule_event_price = $schedule_data ? floatval($schedule_data['event_price'] ?? 0) : 0;

    if ($schedule_data && isset($schedule_data['event_type']) && $schedule_data['event_type'] === 'group') {
        $is_event_booking = true;
        $event_price = $schedule_event_price;
        $max_attendees = intval($schedule_data['max_attendees'] ?? 0);
        $current_attendees = count($schedule_data['attendees'] ?? array());
        
        // Check if event is full
        if ($max_attendees > 0 && $current_attendees >= $max_attendees) {
            wp_send_json_error(array('message' => __('This event is full. Maximum attendees reached.', 'nymia')));
            return;
        }
        
        // For event bookings, only allow full payment
        $payment_type = 'full';
        $total_amount = $event_price;
    } else {
        // Regular live stream booking (existing logic)
        if (!in_array($payment_type, array('full', 'per_minute'))) {
            wp_send_json_error(array('message' => __('Invalid payment type.', 'nymia')));
            return;
        }

        // Get stream pricing from room data or user meta
        $stream_price = 0;
        $per_minute_price = 0;
        
        // Try to get pricing from room data (if available)
        if ($creator_id > 0) {
            $stream_price = floatval(get_user_meta($creator_id, 'nymia_stream_full_price', true));
            $per_minute_price = floatval(get_user_meta($creator_id, 'nymia_stream_per_minute_price', true));
        }
        
        // Override with schedule-specific price if provided
        if ($schedule_event_price > 0) {
            $stream_price = $schedule_event_price;
        }

        // Fallback to default pricing if not set
        if ($stream_price <= 0) {
            $stream_price = floatval(get_option('nymia_default_stream_price', 9.99));
        }
        if ($per_minute_price <= 0) {
            $per_minute_price = floatval(get_option('nymia_default_per_minute_price', 0.99));
        }

        // Calculate total amount
        $total_amount = 0;
        if ($payment_type === 'full') {
            $total_amount = $stream_price;
        } else {
            if ($minutes < 1) {
                wp_send_json_error(array('message' => __('Please select at least 1 minute.', 'nymia')));
                return;
            }
            if ($minutes > 480) {
                wp_send_json_error(array('message' => __('Maximum 480 minutes allowed.', 'nymia')));
                return;
            }
            $total_amount = $per_minute_price * $minutes;
        }
    }

    if ($total_amount <= 0) {
        wp_send_json_error(array('message' => __('Invalid booking amount.', 'nymia')));
        return;
    }

    // Get Stripe keys
    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    $stripe_mode = get_option('nymia_stripe_mode', 'test');
    $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));

    if (empty($stripe_secret_key)) {
        wp_send_json_error(array('message' => __('Stripe is not configured. Please contact the administrator.', 'nymia')));
        return;
    }

    $current_user = wp_get_current_user();
    $success_url = add_query_arg(array(
        'type' => 'live',
        'room_id' => $room_id,
        'payment' => 'success',
        'session_id' => '{CHECKOUT_SESSION_ID}'
    ), home_url('/checkout'));
    $success_url = str_replace(array('%7B', '%7D'), array('{', '}'), $success_url);
    $cancel_url = home_url('/?booking=cancelled');

    // Create Stripe Checkout Session
    $session_data = array(
        'payment_method_types' => array('card'),
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'line_items' => array(
            array(
                'price_data' => array(
                    'currency' => strtolower($currency),
                    'product_data' => array(
                        'name' => $stream_title . ' - Live Stream ' . ($payment_type === 'full' ? 'Full Session' : $minutes . ' Minutes'),
                        'description' => $payment_type === 'full' ? 'Full live stream session access' : $minutes . ' minutes of live stream access',
                    ),
                    'unit_amount' => round($total_amount * 100), // Convert to cents
                ),
                'quantity' => 1,
            ),
        ),
        'metadata' => array(
            'item_type'  => 'live',
            'room_id'    => $room_id,
            'user_id'    => $current_user->ID,
            'creator_id' => $creator_id,
            'payment_type' => $payment_type,
            'minutes'     => $payment_type === 'per_minute' ? $minutes : 0,
            'is_scheduled' => $is_scheduled ? '1' : '0',
            'schedule_id' => $schedule_id,
            'start_timestamp' => $start_timestamp > 0 ? (string)$start_timestamp : '',
            'reminder_minutes' => (string)$reminder_minutes,
            'stream_title' => $stream_title,
            'is_event_booking' => $is_event_booking ? '1' : '0',
            'event_type' => $schedule_data && isset($schedule_data['event_type']) ? $schedule_data['event_type'] : 'single',
            'wp_site_url'=> home_url(),
        ),
    );

    // Create session via Stripe API
    $response = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => http_build_query($session_data),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => __('Failed to create checkout session. Please try again.', 'nymia')));
        return;
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['url'])) {
        // Store booking info temporarily
        $booking_data = array(
            'room_id' => $room_id,
            'creator_id' => $creator_id,
            'stream_title' => $stream_title,
            'payment_type' => $payment_type,
            'minutes' => $minutes,
            'total_amount' => $total_amount,
            'user_id' => $current_user->ID,
            'is_scheduled' => $is_scheduled,
            'schedule_id' => $schedule_id,
            'start_timestamp' => $start_timestamp,
            'reminder_minutes' => $reminder_minutes,
            'stream_title' => $stream_title,
            'created_at' => current_time('mysql'),
        );
        set_transient('nymia_booking_' . $current_user->ID . '_' . $room_id, $booking_data, 30 * MINUTE_IN_SECONDS);
        
        wp_send_json_success(array('checkout_url' => $response_body['url']));
    } else {
        $error_message = isset($response_body['error']['message']) ? $response_body['error']['message'] : __('Failed to create checkout session.', 'nymia');
        wp_send_json_error(array('message' => $error_message));
    }
}
add_action('wp_ajax_nymia_book_live_stream', 'nymia_book_live_stream');

/**
 * Check if user has valid booking for a room (AJAX)
 */
function nymia_check_booking_status() {
    if (!is_user_logged_in()) {
        wp_send_json_success(array('has_booking' => false));
        return;
    }

    check_ajax_referer('nymia_book_live_stream', 'nonce');

    $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
    $user_id = get_current_user_id();

    if (empty($room_id)) {
        wp_send_json_success(array('has_booking' => false));
        return;
    }

    $booking = nymia_user_has_valid_booking($user_id, $room_id);
    wp_send_json_success(array(
        'has_booking' => $booking !== false,
        'booking' => $booking
    ));
}
add_action('wp_ajax_nymia_check_booking_status', 'nymia_check_booking_status');

/**
 * Get all user bookings (AJAX)
 */
function nymia_get_user_bookings() {
    if (!is_user_logged_in()) {
        wp_send_json_success(array('bookings' => array()));
        return;
    }

    check_ajax_referer('nymia_checkout', 'nonce');

    $user_id = get_current_user_id();
    $bookings = get_user_meta($user_id, 'nymia_live_bookings', true);
    
    if (!is_array($bookings)) {
        $bookings = array();
    }
    
    // Filter out expired per-minute bookings based on actual usage
    $valid_bookings = array();
    foreach ($bookings as $booking) {
        $payment_type = isset($booking['payment_type']) ? $booking['payment_type'] : 'full';
        
        // Full session bookings never expire
        if ($payment_type === 'full') {
            $valid_bookings[] = $booking;
            continue;
        }
        
        // For per-minute bookings, check remaining minutes based on actual usage
        if ($payment_type === 'per_minute') {
            $minutes_purchased = isset($booking['minutes_purchased']) ? intval($booking['minutes_purchased']) : (isset($booking['minutes']) ? intval($booking['minutes']) : 0);
            $minutes_used = isset($booking['minutes_used']) ? intval($booking['minutes_used']) : 0;
            
            // Also check if there's an active session that hasn't been closed
            if (isset($booking['join_sessions']) && is_array($booking['join_sessions'])) {
                $current_time = current_time('timestamp');
                foreach ($booking['join_sessions'] as $session) {
                    if (isset($session['start']) && (!isset($session['end']) || $session['end'] == 0)) {
                        // Active session - add current time to used minutes
                        $active_session_seconds = $current_time - $session['start'];
                        $active_session_minutes = ceil($active_session_seconds / 60);
                        $minutes_used += $active_session_minutes;
                    }
                }
            }
            
            // Calculate remaining minutes
            $remaining_minutes = $minutes_purchased - $minutes_used;
            
            // Include booking if there are remaining minutes
            if ($remaining_minutes > 0) {
                $valid_bookings[] = $booking;
            }
        }
    }
    
    wp_send_json_success(array('bookings' => $valid_bookings));
}
add_action('wp_ajax_nymia_get_user_bookings', 'nymia_get_user_bookings');

/**
 * Track when user joins a per-minute booking session
 */
function nymia_track_per_minute_join() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_track_call', 'nonce');

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
    $user_id = get_current_user_id();

    if (empty($session_id) || empty($room_id)) {
        wp_send_json_error(array('message' => __('Missing required parameters.', 'nymia')));
        return;
    }

    // Update booking record
    $bookings = get_user_meta($user_id, 'nymia_live_bookings', true);
    if (!is_array($bookings)) {
        wp_send_json_error(array('message' => __('Booking not found.', 'nymia')));
        return;
    }

    $booking_found = false;
    foreach ($bookings as $key => $booking) {
        if (isset($booking['session_id']) && $booking['session_id'] === $session_id && 
            isset($booking['room_id']) && $booking['room_id'] === $room_id &&
            isset($booking['payment_type']) && $booking['payment_type'] === 'per_minute') {
            
            $current_time = current_time('timestamp');
            
            // Initialize tracking fields if not set
            if (!isset($bookings[$key]['minutes_purchased'])) {
                $bookings[$key]['minutes_purchased'] = isset($booking['minutes']) ? intval($booking['minutes']) : 0;
            }
            if (!isset($bookings[$key]['minutes_used'])) {
                $bookings[$key]['minutes_used'] = 0;
            }
            if (!isset($bookings[$key]['join_sessions'])) {
                $bookings[$key]['join_sessions'] = array();
            }
            
            // Set first join time if not set
            if (!isset($bookings[$key]['first_join_time']) || $bookings[$key]['first_join_time'] == 0) {
                $bookings[$key]['first_join_time'] = $current_time;
            }
            
            // Add new join session
            $bookings[$key]['join_sessions'][] = array(
                'start' => $current_time,
                'end' => 0, // Will be set when user leaves
            );
            
            $bookings[$key]['last_join_time'] = $current_time;
            $booking_found = true;
            break;
        }
    }

    if (!$booking_found) {
        wp_send_json_error(array('message' => __('Per-minute booking not found.', 'nymia')));
        return;
    }

    update_user_meta($user_id, 'nymia_live_bookings', $bookings);
    wp_send_json_success(array('message' => __('Join time tracked.', 'nymia')));
}
add_action('wp_ajax_nymia_track_per_minute_join', 'nymia_track_per_minute_join');

/**
 * Track when user leaves a per-minute booking session
 */
function nymia_track_per_minute_leave() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }

    check_ajax_referer('nymia_track_call', 'nonce');

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
    $user_id = get_current_user_id();

    if (empty($session_id) || empty($room_id)) {
        wp_send_json_error(array('message' => __('Missing required parameters.', 'nymia')));
        return;
    }

    // Update booking record
    $bookings = get_user_meta($user_id, 'nymia_live_bookings', true);
    if (!is_array($bookings)) {
        wp_send_json_error(array('message' => __('Booking not found.', 'nymia')));
        return;
    }

    $booking_found = false;
    foreach ($bookings as $key => $booking) {
        if (isset($booking['session_id']) && $booking['session_id'] === $session_id && 
            isset($booking['room_id']) && $booking['room_id'] === $room_id &&
            isset($booking['payment_type']) && $booking['payment_type'] === 'per_minute') {
            
            $current_time = current_time('timestamp');
            $join_sessions = isset($booking['join_sessions']) ? $booking['join_sessions'] : array();
            
            // Find the most recent join session that hasn't ended
            $last_session_index = -1;
            for ($i = count($join_sessions) - 1; $i >= 0; $i--) {
                if (isset($join_sessions[$i]['end']) && $join_sessions[$i]['end'] == 0) {
                    $last_session_index = $i;
                    break;
                }
            }
            
            if ($last_session_index >= 0 && isset($join_sessions[$last_session_index]['start'])) {
                $session_start = $join_sessions[$last_session_index]['start'];
                $session_duration_seconds = max(0, $current_time - $session_start);
                $session_duration_minutes = ceil($session_duration_seconds / 60); // Round up to nearest minute
                
                // Update the session end time
                $bookings[$key]['join_sessions'][$last_session_index]['end'] = $current_time;
                
                // Recalculate total minutes used
                $total_seconds = 0;
                foreach ($bookings[$key]['join_sessions'] as $session) {
                    if (isset($session['start']) && isset($session['end']) && $session['end'] > 0) {
                        $total_seconds += ($session['end'] - $session['start']);
                    } else if (isset($session['start']) && (!isset($session['end']) || $session['end'] == 0)) {
                        // Active session - use current time
                        $total_seconds += ($current_time - $session['start']);
                    }
                }
                
                $total_minutes_used = ceil($total_seconds / 60); // Round up to nearest minute
                $bookings[$key]['minutes_used'] = $total_minutes_used;
                $bookings[$key]['last_leave_time'] = $current_time;
                
                // Calculate remaining minutes
                $minutes_purchased = isset($bookings[$key]['minutes_purchased']) ? intval($bookings[$key]['minutes_purchased']) : (isset($booking['minutes']) ? intval($booking['minutes']) : 0);
                $remaining_minutes = max(0, $minutes_purchased - $total_minutes_used);
                
                $booking_found = true;
                break;
            }
        }
    }

    if (!$booking_found) {
        wp_send_json_error(array('message' => __('Per-minute booking not found.', 'nymia')));
        return;
    }

    update_user_meta($user_id, 'nymia_live_bookings', $bookings);
    
    // Get remaining minutes for response
    $remaining_minutes = 0;
    if (isset($bookings[$key])) {
        $minutes_purchased = isset($bookings[$key]['minutes_purchased']) ? intval($bookings[$key]['minutes_purchased']) : 0;
        $minutes_used = isset($bookings[$key]['minutes_used']) ? intval($bookings[$key]['minutes_used']) : 0;
        $remaining_minutes = max(0, $minutes_purchased - $minutes_used);
    }
    
    wp_send_json_success(array(
        'message' => __('Leave time tracked.', 'nymia'),
        'minutes_used' => isset($bookings[$key]['minutes_used']) ? $bookings[$key]['minutes_used'] : 0,
        'remaining_minutes' => $remaining_minutes,
    ));
}
add_action('wp_ajax_nymia_track_per_minute_leave', 'nymia_track_per_minute_leave');

/**
 * Schedule live stream reminder via WP-Cron.
 */
function nymia_schedule_live_stream_reminder($session_id, $user_id, $creator_id, $stream_title, $room_id, $start_timestamp, $reminder_minutes, $schedule_id = '') {
    if (!$session_id || !$user_id || !$start_timestamp || $reminder_minutes <= 0) {
        return;
    }

    $send_timestamp = intval($start_timestamp) - (intval($reminder_minutes) * MINUTE_IN_SECONDS);
    $now = current_time('timestamp');
    if ($send_timestamp <= $now) {
        $send_timestamp = $now + 60; // send in next minute if already passed
    }

    $reminder_key = md5($session_id . '|' . $room_id . '|' . $reminder_minutes);
    $args = array(
        'reminder_key'    => $reminder_key,
        'session_id'      => $session_id,
        'user_id'         => intval($user_id),
        'creator_id'      => intval($creator_id),
        'stream_title'    => $stream_title,
        'room_id'         => $room_id,
        'start_timestamp' => intval($start_timestamp),
        'reminder_minutes'=> intval($reminder_minutes),
        'schedule_id'     => $schedule_id,
    );

    if (!wp_next_scheduled('nymia_send_live_stream_reminder', array($args))) {
        wp_schedule_single_event($send_timestamp, 'nymia_send_live_stream_reminder', array($args));
    }
}

/**
 * Cron handler to deliver reminder notifications/emails.
 */
function nymia_send_live_stream_reminder_handler($args) {
    if (!is_array($args)) {
        return;
    }

    $user_id = isset($args['user_id']) ? intval($args['user_id']) : 0;
    $stream_title = isset($args['stream_title']) ? $args['stream_title'] : __('Live Stream', 'nymia');
    $reminder_minutes = isset($args['reminder_minutes']) ? intval($args['reminder_minutes']) : 0;
    $start_timestamp = isset($args['start_timestamp']) ? intval($args['start_timestamp']) : 0;
    $creator_id = isset($args['creator_id']) ? intval($args['creator_id']) : 0;

    if (!$user_id || $reminder_minutes <= 0) {
        return;
    }

    $start_formatted = $start_timestamp ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $start_timestamp) : '';
    $link = add_query_arg(array(
        'room_id' => isset($args['room_id']) ? $args['room_id'] : ''
    ), home_url('/dashboard'));

    $message = sprintf(
        __('Reminder: "%1$s" starts in %2$d minutes (%3$s).', 'nymia'),
        $stream_title,
        $reminder_minutes,
        $start_formatted
    );

    if (function_exists('nymia_create_notification')) {
        nymia_create_notification($user_id, 'live_reminder', $message, $link, $creator_id);
    }

    $user = get_user_by('id', $user_id);
    if ($user && !empty($user->user_email)) {
        $subject = sprintf(__('Reminder: %s starts soon', 'nymia'), $stream_title);
        $body = sprintf(
            __("Hi %s,\n\nThis is a reminder that \"%s\" will begin in %d minutes (%s).\n\nYou can join or manage the session here: %s\n\nThanks,\n%s", 'nymia'),
            $user->display_name ?: $user->user_login,
            $stream_title,
            $reminder_minutes,
            $start_formatted,
            $link,
            get_bloginfo('name')
        );
        wp_mail($user->user_email, $subject, $body);
    }
}
add_action('nymia_send_live_stream_reminder', 'nymia_send_live_stream_reminder_handler', 10, 1);

/**
 * STRIPE CHECKOUT SUCCESS HANDLER
 * --------------------------------
 * Handles successful payment and grants access
 */
function nymia_checkout_success_handler() {
    // This will be called via URL redirect from Stripe
    // Check for session_id in query params
    $session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';
    $item_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
    $item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    $room_id = isset($_GET['room_id']) ? sanitize_text_field($_GET['room_id']) : '';
    $is_cart = isset($_GET['cart']) && $_GET['cart'] === '1';

    // Debug logging
    error_log('NYMIA_CHECKOUT_SUCCESS: session_id=' . $session_id . ', type=' . $item_type . ', id=' . $item_id . ', cart=' . ($is_cart ? '1' : '0'));

    // For live streams, room_id is required instead of item_id
    // For tips, creator_id is required instead of item_id
    if ($item_type === 'live') {
        if (empty($session_id) || empty($item_type) || empty($room_id)) {
            error_log('NYMIA_CHECKOUT_SUCCESS: Missing required parameters for live stream');
            wp_redirect(home_url('/'));
            exit;
        }
    } elseif ($item_type === 'tip') {
        $creator_id = isset($_GET['creator_id']) ? intval($_GET['creator_id']) : 0;
        if (empty($session_id) || empty($item_type) || empty($creator_id)) {
            error_log('NYMIA_CHECKOUT_SUCCESS: Missing required parameters for tip');
            wp_redirect(home_url('/'));
            exit;
        }
    } elseif ($is_cart) {
        // Cart checkout - only session_id is required
        if (empty($session_id)) {
            error_log('NYMIA_CHECKOUT_SUCCESS: Missing session_id for cart checkout');
            wp_redirect(home_url('/cart'));
            exit;
        }
    } else {
    if (empty($session_id) || empty($item_type) || empty($item_id)) {
        error_log('NYMIA_CHECKOUT_SUCCESS: Missing required parameters');
        wp_redirect(home_url('/'));
        exit;
        }
    }

    // Verify session with Stripe
    $stripe_secret_key = get_option('nymia_stripe_secret_key', '');
    if (empty($stripe_secret_key)) {
        wp_redirect(home_url('/'));
        exit;
    }

    $response = wp_remote_get('https://api.stripe.com/v1/checkout/sessions/' . $session_id, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $stripe_secret_key,
        ),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        wp_redirect(home_url('/checkout?type=' . urlencode($item_type) . '&id=' . urlencode($item_id) . '&error=verification'));
        exit;
    }

    $session = json_decode(wp_remote_retrieve_body($response), true);

    // Save payment method if customer exists and payment method was saved
    $current_user_id = get_current_user_id();
    if ($current_user_id > 0 && isset($session['customer']) && isset($session['payment_intent'])) {
        // Payment method should be automatically saved to customer via setup_future_usage
        // We can verify this later if needed
    }

    // Verify payment was successful
    if (!isset($session['payment_status']) || $session['payment_status'] !== 'paid') {
        if ($item_type === 'live' && !empty($room_id)) {
            wp_redirect(home_url('/?booking=payment_failed'));
        } else {
        wp_redirect(home_url('/checkout?type=' . urlencode($item_type) . '&id=' . urlencode($item_id) . '&error=payment'));
        }
        exit;
    }

    // Check if this is a cart checkout
    $is_cart_checkout = isset($session['metadata']['cart_checkout']) && $session['metadata']['cart_checkout'] === '1';
    
    if ($is_cart_checkout) {
        // Process cart checkout
        $item_count = isset($session['metadata']['item_count']) ? intval($session['metadata']['item_count']) : 0;
        $purchased_items = array();
        
        // Extract all items from metadata
        for ($i = 0; $i < $item_count; $i++) {
            $item_type_key = 'item_' . $i . '_type';
            $item_id_key = 'item_' . $i . '_id';
            
            if (isset($session['metadata'][$item_type_key]) && isset($session['metadata'][$item_id_key])) {
                $purchased_items[] = array(
                    'type' => $session['metadata'][$item_type_key],
                    'id' => $session['metadata'][$item_id_key]
                );
            }
        }
        
        // Grant access to all items
        foreach ($purchased_items as $item) {
            $item_type = $item['type'];
            $item_id = $item['id'];
            
            if ($item_type === 'ebook') {
                $unlocked = get_user_meta($current_user_id, 'nymia_ebooks_unlocked', true);
                if (!is_array($unlocked)) {
                    $unlocked = array();
                }
                $ebook_id_str = (string)$item_id;
                if (!in_array($ebook_id_str, $unlocked)) {
                    $unlocked[] = $ebook_id_str;
                    update_user_meta($current_user_id, 'nymia_ebooks_unlocked', array_values($unlocked));
                }
            } elseif ($item_type === 'audio') {
                $unlocked = get_user_meta($current_user_id, 'nymia_audio_unlocked', true);
                if (!is_array($unlocked)) {
                    $unlocked = array();
                }
                $audio_id_str = (string)$item_id;
                if (!in_array($audio_id_str, $unlocked)) {
                    $unlocked[] = $audio_id_str;
                    update_user_meta($current_user_id, 'nymia_audio_unlocked', array_values($unlocked));
                }
            }
        }
        
        // Store purchase record
        $purchases = get_user_meta($current_user_id, 'nymia_purchases', true);
        if (!is_array($purchases)) {
            $purchases = array();
        }
        $purchase_exists = false;
        foreach ($purchases as $purchase) {
            if (isset($purchase['session_id']) && $purchase['session_id'] === $session_id) {
                $purchase_exists = true;
                break;
            }
        }
        if (!$purchase_exists) {
            $purchases[] = array(
                'type' => 'cart',
                'items' => $purchased_items,
                'session_id' => $session_id,
                'amount' => isset($session['amount_total']) ? ($session['amount_total'] / 100) : 0,
                'currency' => isset($session['currency']) ? strtoupper($session['currency']) : 'USD',
                'date' => current_time('mysql'),
            );
            update_user_meta($current_user_id, 'nymia_purchases', $purchases);
        }
        
        // Clear cart
        if (function_exists('nymia_clear_cart')) {
            nymia_clear_cart();
        }
        
        // Redirect to dashboard with success message
        wp_redirect(home_url('/dashboard?purchased=1'));
        exit;
    }
    
    // Verify metadata matches
    $session_item_type = isset($session['metadata']['item_type']) ? $session['metadata']['item_type'] : '';
    
    // For live streams, check for room_id instead of item_id
    // For tips, check for creator_id instead of item_id
    if ($session_item_type === 'live') {
        if (!isset($session['metadata']['room_id']) || !isset($session['metadata']['user_id'])) {
            wp_redirect(home_url('/?booking=error'));
            exit;
        }
    } elseif ($session_item_type === 'tip') {
        if (!isset($session['metadata']['creator_id']) || !isset($session['metadata']['customer_id'])) {
            $creator_id = isset($_GET['creator_id']) ? intval($_GET['creator_id']) : 0;
            wp_redirect(home_url('/checkout?type=tip&creator_id=' . $creator_id . '&error=metadata'));
            exit;
        }
    } else {
    if (!isset($session['metadata']['item_type']) || !isset($session['metadata']['item_id']) || !isset($session['metadata']['user_id'])) {
        wp_redirect(home_url('/checkout?type=' . urlencode($item_type) . '&id=' . urlencode($item_id) . '&error=metadata'));
        exit;
    }
    }
    $session_item_id = isset($session['metadata']['item_id']) ? $session['metadata']['item_id'] : '';
    $session_room_id = isset($session['metadata']['room_id']) ? $session['metadata']['room_id'] : '';
    $session_user_id = intval($session['metadata']['user_id']);
    $current_user_id = get_current_user_id();

    // Verify user matches (for tips, check customer_id instead of user_id)
    if ($session_item_type === 'tip') {
        $session_customer_id = isset($session['metadata']['customer_id']) ? intval($session['metadata']['customer_id']) : 0;
        if ($session_customer_id !== $current_user_id) {
            wp_redirect(home_url('/login'));
            exit;
        }
    } else {
    if ($session_user_id !== $current_user_id) {
        wp_redirect(home_url('/login'));
        exit;
        }
    }

    // Grant access
    if ($session_item_type === 'tip') {
        // Tips are processed via webhook, just redirect to creator profile
        $session_creator_id = isset($session['metadata']['creator_id']) ? intval($session['metadata']['creator_id']) : 0;
        if ($session_creator_id > 0) {
            $creator = get_user_by('id', $session_creator_id);
            if ($creator) {
                wp_redirect(home_url('/profile/?username=' . urlencode($creator->user_login) . '&tip=success'));
                exit;
            }
        }
        wp_redirect(home_url('/?tip=success'));
        exit;
    } elseif ($session_item_type === 'ebook') {
        // Always grant access when payment is successful (avoid duplicates)
        $unlocked = get_user_meta($current_user_id, 'nymia_ebooks_unlocked', true);
        if (!is_array($unlocked)) {
            $unlocked = array();
        }
        $ebook_id_str = (string)$session_item_id;
        $exists = false;
        foreach ($unlocked as $id) {
            if ((string)$id === $ebook_id_str) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $unlocked[] = $ebook_id_str;
            update_user_meta($current_user_id, 'nymia_ebooks_unlocked', array_values($unlocked));
        }

        // Store purchase record (check for duplicates)
        $purchases = get_user_meta($current_user_id, 'nymia_purchases', true);
        if (!is_array($purchases)) {
            $purchases = array();
        }
        $purchase_exists = false;
        foreach ($purchases as $purchase) {
            if (isset($purchase['session_id']) && $purchase['session_id'] === $session_id) {
                $purchase_exists = true;
                break;
            }
        }
        if (!$purchase_exists) {
            $purchases[] = array(
                'type' => 'ebook',
                'id' => $session_item_id,
                'session_id' => $session_id,
                'amount' => isset($session['amount_total']) ? ($session['amount_total'] / 100) : 0,
                'currency' => isset($session['currency']) ? strtoupper($session['currency']) : 'USD',
                'date' => current_time('mysql'),
            );
            update_user_meta($current_user_id, 'nymia_purchases', $purchases);
        }
        
        wp_redirect(home_url('/single-ebook?ebook=' . urlencode($session_item_id) . '&purchased=1'));
        exit;

    } elseif ($session_item_type === 'audio') {
        // ALWAYS grant access when payment is successful (avoid duplicates)
        $unlocked = get_user_meta($current_user_id, 'nymia_audio_unlocked', true);
        if (!is_array($unlocked)) {
            $unlocked = array();
        }
        $audio_id_str = (string)$session_item_id;
        $exists = false;
        foreach ($unlocked as $id) {
            if ((string)$id === $audio_id_str) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $unlocked[] = $audio_id_str;
            update_user_meta($current_user_id, 'nymia_audio_unlocked', array_values($unlocked));
        }

        // Store purchase record (check for duplicates by session_id)
        $purchases = get_user_meta($current_user_id, 'nymia_purchases', true);
        if (!is_array($purchases)) {
            $purchases = array();
        }
        $purchase_exists = false;
        foreach ($purchases as $purchase) {
            if (isset($purchase['session_id']) && $purchase['session_id'] === $session_id) {
                $purchase_exists = true;
                break;
            }
        }
        if (!$purchase_exists) {
            $purchases[] = array(
                'type' => 'audio',
                'id' => $session_item_id,
                'session_id' => $session_id,
                'amount' => isset($session['amount_total']) ? ($session['amount_total'] / 100) : 0,
                'currency' => isset($session['currency']) ? strtoupper($session['currency']) : 'USD',
                'date' => current_time('mysql'),
            );
            update_user_meta($current_user_id, 'nymia_purchases', $purchases);
        }
        
        // Get creator ID for redirect
        $all_audio = get_transient('nymia_all_audio');
        $creator_id = 0;
        if ($all_audio && is_array($all_audio)) {
            foreach ($all_audio as $audio) {
                if (!empty($audio['id']) && (string)$audio['id'] === (string)$session_item_id) {
                    $creator_id = isset($audio['user_id']) ? intval($audio['user_id']) : 0;
                    break;
                }
            }
        }
        
        // Also check user-specific transients if not found
        if (!$creator_id && $current_user_id > 0) {
            $user_audio = get_transient('nymia_user_audio_' . $current_user_id);
            if ($user_audio && is_array($user_audio)) {
                foreach ($user_audio as $audio) {
                    if (!empty($audio['id']) && (string)$audio['id'] === (string)$session_item_id) {
                        $creator_id = isset($audio['user_id']) ? intval($audio['user_id']) : ($current_user_id ?: 0);
                        break;
                    }
                }
            }
        }
        
        wp_redirect(home_url('/single-audio?track_id=' . urlencode($session_item_id) . '&user_id=' . $creator_id . '&purchased=1'));
        exit;

    } elseif ($session_item_type === 'live') {
        // Handle live stream booking
        $payment_type = isset($session['metadata']['payment_type']) ? $session['metadata']['payment_type'] : 'full';
        $minutes = isset($session['metadata']['minutes']) ? intval($session['metadata']['minutes']) : 0;
        $creator_id = isset($session['metadata']['creator_id']) ? intval($session['metadata']['creator_id']) : 0;
        $is_scheduled = isset($session['metadata']['is_scheduled']) && $session['metadata']['is_scheduled'] === '1';
        $schedule_id = isset($session['metadata']['schedule_id']) ? sanitize_text_field($session['metadata']['schedule_id']) : '';
        $start_timestamp = isset($session['metadata']['start_timestamp']) ? intval($session['metadata']['start_timestamp']) : 0;
        $reminder_minutes = isset($session['metadata']['reminder_minutes']) ? intval($session['metadata']['reminder_minutes']) : 0;
        $stream_title = isset($session['metadata']['stream_title']) ? sanitize_text_field($session['metadata']['stream_title']) : __('Live Stream', 'nymia');
        $allowed_reminders = array(0, 5, 15, 30, 60, 120);
        if (!in_array($reminder_minutes, $allowed_reminders, true)) {
            $reminder_minutes = 0;
        }
        $is_event_booking = isset($session['metadata']['is_event_booking']) ? ($session['metadata']['is_event_booking'] === '1') : false;
        $event_type = isset($session['metadata']['event_type']) ? $session['metadata']['event_type'] : 'single';
        
        // Track attendee for group events
        if ($is_event_booking && $event_type === 'group' && !empty($schedule_id) && $creator_id > 0) {
            $schedules = nymia_get_creator_stream_schedules($creator_id);
            $schedule_updated = false;
            foreach ($schedules as $key => $schedule) {
                if (isset($schedule['id']) && $schedule['id'] === $schedule_id) {
                    if (!isset($schedule['attendees']) || !is_array($schedule['attendees'])) {
                        $schedule['attendees'] = array();
                    }
                    // Check if user is already registered
                    $already_registered = false;
                    foreach ($schedule['attendees'] as $attendee) {
                        if (isset($attendee['user_id']) && $attendee['user_id'] === $current_user_id) {
                            $already_registered = true;
                            break;
                        }
                    }
                    if (!$already_registered) {
                        $current_user = wp_get_current_user();
                        $schedule['attendees'][] = array(
                            'user_id' => $current_user_id,
                            'user_name' => $current_user->display_name,
                            'user_email' => $current_user->user_email,
                            'booked_at' => current_time('timestamp'),
                            'session_id' => $session_id,
                        );
                        $schedules[$key] = $schedule;
                        $schedule_updated = true;
                    }
                    break;
                }
            }
            if ($schedule_updated) {
                nymia_save_creator_stream_schedules($creator_id, $schedules);
            }
        }
        
        // Store booking access
        $bookings = get_user_meta($current_user_id, 'nymia_live_bookings', true);
        if (!is_array($bookings)) {
            $bookings = array();
        }
        
        $booking_key = $session_room_id . '_' . $session_id;
        $booking_exists = false;
        foreach ($bookings as $booking) {
            if (isset($booking['session_id']) && $booking['session_id'] === $session_id) {
                $booking_exists = true;
                break;
            }
        }
        
        if (!$booking_exists) {
            $booking_data = array(
                'room_id' => $session_room_id,
                'creator_id' => $creator_id,
                'session_id' => $session_id,
                'payment_type' => $payment_type,
                'minutes' => $minutes,
                'amount' => isset($session['amount_total']) ? ($session['amount_total'] / 100) : 0,
                'currency' => isset($session['currency']) ? strtoupper($session['currency']) : 'USD',
                'booked_at' => current_time('mysql'),
                'expires_at' => $payment_type === 'full' ? '' : date('Y-m-d H:i:s', strtotime('+' . $minutes . ' minutes')), // Keep for backward compatibility
                'reminder_minutes' => $reminder_minutes,
                'stream_title' => $stream_title,
            );
            
            // For per-minute bookings, track usage time
            if ($payment_type === 'per_minute') {
                $booking_data['minutes_purchased'] = $minutes;
                $booking_data['minutes_used'] = 0; // Track actual minutes used
                $booking_data['first_join_time'] = 0; // Timestamp when first joined
                $booking_data['last_leave_time'] = 0; // Timestamp when last left
                $booking_data['join_sessions'] = array(); // Array of {start, end} for each join session
            }
            
            // Add scheduled stream information if applicable
            if ($is_scheduled) {
                $booking_data['is_scheduled'] = true;
                $booking_data['schedule_id'] = $schedule_id;
                $booking_data['start_timestamp'] = $start_timestamp;
            }
            
            $bookings[] = $booking_data;
            update_user_meta($current_user_id, 'nymia_live_bookings', $bookings);
        }
        
        // Schedule reminder if applicable
        if ($is_scheduled && $reminder_minutes > 0 && $start_timestamp > 0) {
            nymia_schedule_live_stream_reminder($session_id, $current_user_id, $creator_id, $stream_title, $session_room_id, $start_timestamp, $reminder_minutes, $schedule_id);
        }
        
        // Redirect to live stream
        if ($is_scheduled) {
            wp_redirect(home_url('/live-audio/?room_id=' . urlencode($session_room_id) . '&scheduled=1'));
        } else {
            wp_redirect(home_url('/live-audio/?room_id=' . urlencode($session_room_id)));
        }
    exit;
        
    } elseif ($session_item_type === 'instant_call') {
        // Handle instant call booking (pre-authorized payment)
        $creator_id = isset($session['metadata']['creator_id']) ? intval($session['metadata']['creator_id']) : 0;
        $per_minute_price = isset($session['metadata']['per_minute_price']) ? floatval($session['metadata']['per_minute_price']) : 0;
        $preauth_minutes = isset($session['metadata']['preauth_minutes']) ? intval($session['metadata']['preauth_minutes']) : 5;
        $stream_title = isset($session['metadata']['stream_title']) ? sanitize_text_field($session['metadata']['stream_title']) : __('Instant Call', 'nymia');
        
        // Get payment intent ID from session
        $payment_intent_id = isset($session['payment_intent']) ? $session['payment_intent'] : '';
        if (is_array($payment_intent_id)) {
            $payment_intent_id = isset($payment_intent_id['id']) ? $payment_intent_id['id'] : '';
        }
        
        // Store call access for customer (status: pending_start - waiting for actual call start)
        $calls = get_user_meta($current_user_id, 'nymia_instant_calls', true);
        if (!is_array($calls)) {
            $calls = array();
        }
        
        $call_exists = false;
        foreach ($calls as $call) {
            if (isset($call['session_id']) && $call['session_id'] === $session_id) {
                $call_exists = true;
                break;
            }
        }
        
        if (!$call_exists) {
            $call_data = array(
                'room_id' => $session_room_id,
                'creator_id' => $creator_id,
                'session_id' => $session_id,
                'payment_intent_id' => $payment_intent_id,
                'per_minute_price' => $per_minute_price,
                'preauth_minutes' => $preauth_minutes,
                'preauth_amount' => isset($session['amount_total']) ? ($session['amount_total'] / 100) : 0,
                'minutes_used' => 0,
                'final_amount' => 0,
                'currency' => isset($session['currency']) ? strtoupper($session['currency']) : 'USD',
                'start_time' => 0, // Will be set when call actually starts
                'end_time' => 0,
                'status' => 'pending_start', // pending_start -> active -> completed
            );
            $calls[] = $call_data;
            update_user_meta($current_user_id, 'nymia_instant_calls', $calls);
        }
        
        // Mark creator as having a pending instant call (will be activated when call starts)
        update_user_meta($creator_id, 'nymia_active_instant_call', array(
            'room_id' => $session_room_id,
            'customer_id' => $current_user_id,
            'start_time' => 0, // Will be set when call actually starts
            'per_minute_price' => $per_minute_price,
            'session_id' => $session_id,
            'payment_intent_id' => $payment_intent_id,
            'status' => 'pending_start',
        ));
        
        // Redirect to live stream room
        wp_redirect(home_url('/live-audio/?room_id=' . urlencode($session_room_id) . '&instant_call=1'));
        exit;
    } elseif ($session_item_type === 'private_session') {
        $slot_id = isset($session['metadata']['slot_id']) ? sanitize_text_field($session['metadata']['slot_id']) : '';
        $creator_id = isset($session['metadata']['creator_id']) ? intval($session['metadata']['creator_id']) : 0;
        $start_timestamp = isset($session['metadata']['start_timestamp']) ? intval($session['metadata']['start_timestamp']) : 0;
        $reminder_minutes = isset($session['metadata']['reminder_minutes']) ? intval($session['metadata']['reminder_minutes']) : 0;
        $stream_title = isset($session['metadata']['stream_title']) ? sanitize_text_field($session['metadata']['stream_title']) : __('Private Session', 'nymia');
        $allowed_reminders = array(0, 5, 15, 30, 60, 120);
        if (!in_array($reminder_minutes, $allowed_reminders, true)) {
            $reminder_minutes = 0;
        }

        if (!$slot_id || !$creator_id) {
            wp_redirect(home_url('/?booking=error'));
            exit;
        }

        $customer = wp_get_current_user();
        $slot_update = nymia_private_mark_slot_booked(
            $creator_id,
            $slot_id,
            $current_user_id,
            $customer->display_name ?: $customer->user_login,
            $customer->user_email,
            $session_id,
            $reminder_minutes
        );

        if (is_wp_error($slot_update)) {
            wp_redirect(home_url('/?booking=error'));
            exit;
        }

        $bookings = nymia_private_bookings_meta($current_user_id);
        $bookings[] = array(
            'slot_id' => $slot_id,
            'creator_id' => $creator_id,
            'session_id' => $session_id,
            'amount' => isset($session['amount_total']) ? ($session['amount_total'] / 100) : 0,
            'currency' => isset($session['currency']) ? strtoupper($session['currency']) : 'USD',
            'booked_at' => current_time('mysql'),
            'start_timestamp' => $slot_update['start_timestamp'] ?? 0,
            'duration' => $slot_update['duration'] ?? 60,
            'reminder_minutes' => $reminder_minutes,
        );
        nymia_private_save_bookings($current_user_id, $bookings);

        $purchases = get_user_meta($current_user_id, 'nymia_purchases', true);
        if (!is_array($purchases)) {
            $purchases = array();
        }
        $purchases[] = array(
            'type' => 'private_session',
            'slot_id' => $slot_id,
            'session_id' => $session_id,
            'amount' => isset($session['amount_total']) ? ($session['amount_total'] / 100) : 0,
            'currency' => isset($session['currency']) ? strtoupper($session['currency']) : 'USD',
            'date' => current_time('mysql'),
        );
        update_user_meta($current_user_id, 'nymia_purchases', $purchases);

        if ($reminder_minutes > 0 && $start_timestamp > 0) {
            nymia_schedule_live_stream_reminder(
                $session_id,
                $current_user_id,
                $creator_id,
                $stream_title,
                $slot_id,
                $start_timestamp,
                $reminder_minutes,
                ''
            );
        }

        wp_redirect(home_url('/?booking=private_success'));
        exit;
    }
}

/**
 * GET AUDIO CATEGORIES
 * --------------------
 * Retrieves audio categories from admin settings
 * Falls back to default categories if none are set
 * @return array List of category names
 */
function nymia_get_audio_categories() {
    // Get categories from admin settings (stored in transient)
    $categories = get_transient('nymia_product_categories');
    
    // If no categories are set in admin, use default categories
    if (!is_array($categories) || empty($categories)) {
        $categories = array(
            'Cooking',
            'Gardening',
            'Sports & Fitness',
            'Music',
            'Art & Design',
            'Technology',
            'Education',
            'Lifestyle',
            'Business',
            'Health & Wellness'
        );
        set_transient('nymia_product_categories', $categories, 30 * DAY_IN_SECONDS);
    }
    
    $categories = array_unique(array_map('sanitize_text_field', $categories));
    
    // Sort categories alphabetically
    sort($categories);
    
    return $categories;
}

/**
 * GET EBOOK CATEGORIES
 * --------------------
 * Retrieves ebook categories from admin settings
 * Falls back to default categories if none are set
 * @return array List of category names
 */
function nymia_get_ebook_categories() {
    $categories = get_transient('nymia_ebook_categories');
    
    if (!is_array($categories) || empty($categories)) {
        $categories = array(
            'Fiction',
            'Non-Fiction',
            'Business & Finance',
            'Self-Help & Personal Growth',
            'Technology & Programming',
            'Health & Wellness',
            'Education & Academic',
            'Lifestyle & Travel',
            'Children & Young Adult',
            'Art & Design'
        );
        set_transient('nymia_ebook_categories', $categories, 30 * DAY_IN_SECONDS);
    }
    
    $categories = array_unique(array_map('sanitize_text_field', $categories));
    sort($categories);
    
    return $categories;
}

/**
 * GET EBOOK LANGUAGES
 * -------------------
 * Retrieves ebook languages from admin settings with defaults
 * @return array
 */
function nymia_get_ebook_languages() {
    $languages = get_transient('nymia_ebook_languages');
    
    if (!is_array($languages) || empty($languages)) {
        $languages = array(
            'English',
            'Spanish',
            'French',
            'German',
            'Portuguese',
            'Italian',
            'Arabic',
            'Hindi',
            'Chinese',
            'Japanese'
        );
        set_transient('nymia_ebook_languages', $languages, 30 * DAY_IN_SECONDS);
    }
    
    $languages = array_unique(array_map('sanitize_text_field', $languages));
    sort($languages);
    
    return $languages;
}
/**
 * AJAX HANDLER: Add Audio Category
 * --------------------------------
 * Adds a new category via AJAX (for instant updates)
 */
function nymia_ajax_add_category() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
        return;
    }
    
    // Verify nonce
    check_ajax_referer('nymia_category_action', 'nonce');
    
    $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
    
    if (empty($category_name)) {
        wp_send_json_error(array('message' => __('Category name is required', 'nymia')));
        return;
    }
    
    // Get existing categories
    $categories = get_transient('nymia_product_categories');
    if (!is_array($categories)) {
        $categories = array();
    }
    
    // Check if category already exists
    if (in_array($category_name, $categories)) {
        wp_send_json_error(array('message' => __('Category already exists', 'nymia')));
        return;
    }
    
    // Add category
    $categories[] = $category_name;
    sort($categories);
    set_transient('nymia_product_categories', $categories, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array(
        'message' => __('Category added successfully', 'nymia'),
        'category' => $category_name,
        'categories' => $categories
    ));
}
add_action('wp_ajax_nymia_add_category', 'nymia_ajax_add_category');

/**
 * AJAX HANDLER: Delete Audio Category
 * -----------------------------------
 * Deletes a category via AJAX (for instant updates)
 */
function nymia_ajax_delete_category() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
        return;
    }
    
    // Verify nonce
    check_ajax_referer('nymia_category_action', 'nonce');
    
    $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
    
    if (empty($category_name)) {
        wp_send_json_error(array('message' => __('Category name is required', 'nymia')));
        return;
    }
    
    // Get existing categories
    $categories = get_transient('nymia_product_categories');
    if (!is_array($categories)) {
        $categories = array();
    }
    
    // Remove category
    $categories = array_filter($categories, function($cat) use ($category_name) {
        return $cat !== $category_name;
    });
    $categories = array_values($categories);
    sort($categories);
    set_transient('nymia_product_categories', $categories, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array(
        'message' => __('Category deleted successfully', 'nymia'),
        'categories' => $categories
    ));
}
add_action('wp_ajax_nymia_delete_category', 'nymia_ajax_delete_category');

/**
 * GET AUDIO SUBCATEGORIES
 * -----------------------
 * Retrieves audio sub-categories organized by parent category
 * @return array Associative array with parent category as key and array of sub-categories as value
 */
if (!function_exists('nymia_get_audio_subcategories')) {
    function nymia_get_audio_subcategories() {
        $subcategories = get_transient('nymia_audio_subcategories');
        
        if (!is_array($subcategories)) {
            $subcategories = array();
        }
        
        return $subcategories;
    }
}

/**
 * AJAX HANDLER: Add Audio Sub-Category
 * ------------------------------------
 * Adds a new sub-category to a parent category via AJAX
 */
if (!function_exists('nymia_ajax_add_audio_subcategory')) {
    function nymia_ajax_add_audio_subcategory() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
            return;
        }
        
        // Verify nonce
        check_ajax_referer('nymia_subcategory_action', 'nonce');
        
        $parent_category = isset($_POST['parent_category']) ? sanitize_text_field($_POST['parent_category']) : '';
        $subcategory_name = isset($_POST['subcategory_name']) ? sanitize_text_field($_POST['subcategory_name']) : '';
        
        if (empty($parent_category) || empty($subcategory_name)) {
            wp_send_json_error(array('message' => __('Parent category and sub-category name are required', 'nymia')));
            return;
        }
        
        // Verify parent category exists
        $categories = nymia_get_audio_categories();
        if (!in_array($parent_category, $categories)) {
            wp_send_json_error(array('message' => __('Parent category does not exist', 'nymia')));
            return;
        }
        
        // Get existing sub-categories
        $subcategories = nymia_get_audio_subcategories();
        
        // Initialize parent category array if it doesn't exist
        if (!isset($subcategories[$parent_category])) {
            $subcategories[$parent_category] = array();
        }
        
        // Check if sub-category already exists for this parent
        if (in_array($subcategory_name, $subcategories[$parent_category])) {
            wp_send_json_error(array('message' => __('Sub-category already exists for this parent category', 'nymia')));
            return;
        }
        
        // Add sub-category
        $subcategories[$parent_category][] = $subcategory_name;
        $subcategories[$parent_category] = array_unique(array_map('sanitize_text_field', $subcategories[$parent_category]));
        sort($subcategories[$parent_category]);
        
        set_transient('nymia_audio_subcategories', $subcategories, 30 * DAY_IN_SECONDS);
        
        wp_send_json_success(array(
            'message' => __('Sub-category added successfully', 'nymia'),
            'parent_category' => $parent_category,
            'subcategory' => $subcategory_name
        ));
    }
}
add_action('wp_ajax_nymia_add_audio_subcategory', 'nymia_ajax_add_audio_subcategory');

/**
 * AJAX HANDLER: Delete Audio Sub-Category
 * ---------------------------------------
 * Deletes a sub-category from a parent category via AJAX
 */
if (!function_exists('nymia_ajax_delete_audio_subcategory')) {
    function nymia_ajax_delete_audio_subcategory() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
            return;
        }
        
        // Verify nonce
        check_ajax_referer('nymia_subcategory_action', 'nonce');
        
        $parent_category = isset($_POST['parent_category']) ? sanitize_text_field($_POST['parent_category']) : '';
        $subcategory_name = isset($_POST['subcategory_name']) ? sanitize_text_field($_POST['subcategory_name']) : '';
        
        if (empty($parent_category) || empty($subcategory_name)) {
            wp_send_json_error(array('message' => __('Parent category and sub-category name are required', 'nymia')));
            return;
        }
        
        // Get existing sub-categories
        $subcategories = nymia_get_audio_subcategories();
        
        // Check if parent category exists
        if (!isset($subcategories[$parent_category])) {
            wp_send_json_error(array('message' => __('Parent category not found', 'nymia')));
            return;
        }
        
        // Remove sub-category
        $subcategories[$parent_category] = array_filter($subcategories[$parent_category], function($sub) use ($subcategory_name) {
            return $sub !== $subcategory_name;
        });
        $subcategories[$parent_category] = array_values($subcategories[$parent_category]);
        
        // Remove parent category if no sub-categories remain
        if (empty($subcategories[$parent_category])) {
            unset($subcategories[$parent_category]);
        }
        
        set_transient('nymia_audio_subcategories', $subcategories, 30 * DAY_IN_SECONDS);
        
        wp_send_json_success(array(
            'message' => __('Sub-category deleted successfully', 'nymia')
        ));
    }
}
add_action('wp_ajax_nymia_delete_audio_subcategory', 'nymia_ajax_delete_audio_subcategory');

/**
 * AJAX HANDLER: Get Audio Sub-Categories List
 * -------------------------------------------
 * Returns HTML for the sub-categories list (for refreshing after add/delete)
 */
if (!function_exists('nymia_ajax_get_audio_subcategories')) {
    function nymia_ajax_get_audio_subcategories() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
            return;
        }
        
        // Verify nonce
        check_ajax_referer('nymia_subcategory_action', 'nonce');
        
        $subcategories = nymia_get_audio_subcategories();
        $total_count = 0;
        $html = '';
        
        if (empty($subcategories)) {
            $html = '<div id="nymia-audio-empty-subcategories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: rgba(255, 255, 255, 0.6);"><p style="margin: 0;">' . esc_html__('No sub-categories created yet. Add your first sub-category above.', 'nymia') . '</p></div>';
        } else {
            ksort($subcategories);
            foreach ($subcategories as $parent_cat => $subs) {
                if (empty($subs)) continue;
                $total_count += count($subs);
                sort($subs);
                
                $html .= '<div style="margin-bottom: 24px;">';
                $html .= '<h4 style="color: rgba(255, 255, 255, 0.9); margin-bottom: 12px; font-size: 1rem; font-weight: 600; padding-bottom: 8px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">' . esc_html($parent_cat) . '</h4>';
                $html .= '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
                
                foreach ($subs as $sub) {
                    $html .= '<div class="nymia-subcategory-item" data-parent="' . esc_attr($parent_cat) . '" data-subcategory="' . esc_attr($sub) . '" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">';
                    $html .= '<span style="color: rgba(255, 255, 255, 0.85); font-weight: 500; font-size: 0.9rem;">' . esc_html($sub) . '</span>';
                    $html .= '<button type="button" class="nymia-delete-subcategory-btn" data-parent="' . esc_attr($parent_cat) . '" data-subcategory="' . esc_attr($sub) . '" style="color: #ff6b6b; background: none; border: none; cursor: pointer; padding: 2px 6px; font-size: 16px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity=\'1\'" onmouseout="this.style.opacity=\'0.7\'" title="' . esc_attr__('Delete Sub-Category', 'nymia') . '">×</button>';
                    $html .= '</div>';
                }
                
                $html .= '</div></div>';
            }
        }
        
        wp_send_json_success(array(
            'html' => $html,
            'count' => $total_count
        ));
    }
}
add_action('wp_ajax_nymia_get_audio_subcategories', 'nymia_ajax_get_audio_subcategories');

/**
 * AJAX HANDLER: Get Audio Sub-Categories by Parent
 * ------------------------------------------------
 * Returns sub-categories for a specific parent category
 */
if (!function_exists('nymia_ajax_get_audio_subcategories_by_parent')) {
    function nymia_ajax_get_audio_subcategories_by_parent() {
        // Verify nonce
        check_ajax_referer('nymia_subcategory_action', 'nonce');
        
        $parent_category = isset($_POST['parent_category']) ? sanitize_text_field($_POST['parent_category']) : '';
        
        if (empty($parent_category)) {
            wp_send_json_error(array('message' => __('Parent category is required', 'nymia')));
            return;
        }
        
        $subcategories = nymia_get_audio_subcategories();
        
        if (isset($subcategories[$parent_category]) && !empty($subcategories[$parent_category])) {
            wp_send_json_success(array('data' => $subcategories[$parent_category]));
        } else {
            wp_send_json_success(array('data' => array()));
        }
    }
}
add_action('wp_ajax_nymia_get_audio_subcategories_by_parent', 'nymia_ajax_get_audio_subcategories_by_parent');

/**
 * AJAX HANDLER: Add Ebook Category
 * --------------------------------
 * Adds a new ebook category via AJAX
 */
function nymia_ajax_add_ebook_category() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_ebook_category_action', 'nonce');
    
    $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
    
    if (empty($category_name)) {
        wp_send_json_error(array('message' => __('Category name is required', 'nymia')));
        return;
    }
    
    $categories = get_transient('nymia_ebook_categories');
    if (!is_array($categories)) {
        $categories = array();
    }
    
    if (in_array($category_name, $categories, true)) {
        wp_send_json_error(array('message' => __('Category already exists', 'nymia')));
        return;
    }
    
    $categories[] = $category_name;
    $categories = array_unique(array_map('sanitize_text_field', $categories));
    sort($categories);
    
    set_transient('nymia_ebook_categories', $categories, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array(
        'message' => __('Category added successfully', 'nymia'),
        'category' => $category_name,
        'categories' => $categories
    ));
}
add_action('wp_ajax_nymia_add_ebook_category', 'nymia_ajax_add_ebook_category');

/**
 * AJAX HANDLER: Delete Ebook Category
 * -----------------------------------
 * Deletes an ebook category via AJAX
 */
function nymia_ajax_delete_ebook_category() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_ebook_category_action', 'nonce');
    
    $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
    
    if (empty($category_name)) {
        wp_send_json_error(array('message' => __('Category name is required', 'nymia')));
        return;
    }
    
    $categories = get_transient('nymia_ebook_categories');
    if (!is_array($categories)) {
        $categories = array();
    }
    
    $categories = array_filter($categories, function($cat) use ($category_name) {
        return $cat !== $category_name;
    });
    $categories = array_values($categories);
    $categories = array_unique(array_map('sanitize_text_field', $categories));
    sort($categories);
    
    set_transient('nymia_ebook_categories', $categories, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array(
        'message' => __('Category deleted successfully', 'nymia'),
        'categories' => $categories
    ));
}
add_action('wp_ajax_nymia_delete_ebook_category', 'nymia_ajax_delete_ebook_category');

/**
 * GET EBOOK SUBCATEGORIES
 * -----------------------
 * Retrieves ebook sub-categories organized by parent category
 * @return array Associative array with parent category as key and array of sub-categories as value
 */
if (!function_exists('nymia_get_ebook_subcategories')) {
    function nymia_get_ebook_subcategories() {
        $subcategories = get_transient('nymia_ebook_subcategories');
        
        if (!is_array($subcategories)) {
            $subcategories = array();
        }
        
        return $subcategories;
    }
}

/**
 * AJAX HANDLER: Add Ebook Sub-Category
 * ------------------------------------
 * Adds a new sub-category to a parent category via AJAX
 */
if (!function_exists('nymia_ajax_add_ebook_subcategory')) {
    function nymia_ajax_add_ebook_subcategory() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
            return;
        }
        
        // Verify nonce
        check_ajax_referer('nymia_ebook_subcategory_action', 'nonce');
        
        $parent_category = isset($_POST['parent_category']) ? sanitize_text_field($_POST['parent_category']) : '';
        $subcategory_name = isset($_POST['subcategory_name']) ? sanitize_text_field($_POST['subcategory_name']) : '';
        
        if (empty($parent_category) || empty($subcategory_name)) {
            wp_send_json_error(array('message' => __('Parent category and sub-category name are required', 'nymia')));
            return;
        }
        
        // Verify parent category exists
        $categories = nymia_get_ebook_categories();
        if (!in_array($parent_category, $categories)) {
            wp_send_json_error(array('message' => __('Parent category does not exist', 'nymia')));
            return;
        }
        
        // Get existing sub-categories
        $subcategories = nymia_get_ebook_subcategories();
        
        // Initialize parent category array if it doesn't exist
        if (!isset($subcategories[$parent_category])) {
            $subcategories[$parent_category] = array();
        }
        
        // Check if sub-category already exists for this parent
        if (in_array($subcategory_name, $subcategories[$parent_category])) {
            wp_send_json_error(array('message' => __('Sub-category already exists for this parent category', 'nymia')));
            return;
        }
        
        // Add sub-category
        $subcategories[$parent_category][] = $subcategory_name;
        $subcategories[$parent_category] = array_unique(array_map('sanitize_text_field', $subcategories[$parent_category]));
        sort($subcategories[$parent_category]);
        
        set_transient('nymia_ebook_subcategories', $subcategories, 30 * DAY_IN_SECONDS);
        
        wp_send_json_success(array(
            'message' => __('Sub-category added successfully', 'nymia'),
            'parent_category' => $parent_category,
            'subcategory' => $subcategory_name
        ));
    }
}
add_action('wp_ajax_nymia_add_ebook_subcategory', 'nymia_ajax_add_ebook_subcategory');

/**
 * AJAX HANDLER: Delete Ebook Sub-Category
 * ---------------------------------------
 * Deletes a sub-category from a parent category via AJAX
 */
if (!function_exists('nymia_ajax_delete_ebook_subcategory')) {
    function nymia_ajax_delete_ebook_subcategory() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
            return;
        }
        
        // Verify nonce
        check_ajax_referer('nymia_ebook_subcategory_action', 'nonce');
        
        $parent_category = isset($_POST['parent_category']) ? sanitize_text_field($_POST['parent_category']) : '';
        $subcategory_name = isset($_POST['subcategory_name']) ? sanitize_text_field($_POST['subcategory_name']) : '';
        
        if (empty($parent_category) || empty($subcategory_name)) {
            wp_send_json_error(array('message' => __('Parent category and sub-category name are required', 'nymia')));
            return;
        }
        
        // Get existing sub-categories
        $subcategories = nymia_get_ebook_subcategories();
        
        // Check if parent category exists
        if (!isset($subcategories[$parent_category])) {
            wp_send_json_error(array('message' => __('Parent category not found', 'nymia')));
            return;
        }
        
        // Remove sub-category
        $subcategories[$parent_category] = array_filter($subcategories[$parent_category], function($sub) use ($subcategory_name) {
            return $sub !== $subcategory_name;
        });
        $subcategories[$parent_category] = array_values($subcategories[$parent_category]);
        
        // Remove parent category if no sub-categories remain
        if (empty($subcategories[$parent_category])) {
            unset($subcategories[$parent_category]);
        }
        
        set_transient('nymia_ebook_subcategories', $subcategories, 30 * DAY_IN_SECONDS);
        
        wp_send_json_success(array(
            'message' => __('Sub-category deleted successfully', 'nymia')
        ));
    }
}
add_action('wp_ajax_nymia_delete_ebook_subcategory', 'nymia_ajax_delete_ebook_subcategory');

/**
 * AJAX HANDLER: Get Ebook Sub-Categories List
 * -------------------------------------------
 * Returns HTML for the sub-categories list (for refreshing after add/delete)
 */
if (!function_exists('nymia_ajax_get_ebook_subcategories')) {
    function nymia_ajax_get_ebook_subcategories() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
            return;
        }
        
        // Verify nonce
        check_ajax_referer('nymia_ebook_subcategory_action', 'nonce');
        
        $subcategories = nymia_get_ebook_subcategories();
        $total_count = 0;
        $html = '';
        
        if (empty($subcategories)) {
            $html = '<div id="nymia-ebook-empty-subcategories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);"><p style="margin: 0;">' . esc_html__('No sub-categories created yet. Add your first sub-category above.', 'nymia') . '</p></div>';
        } else {
            ksort($subcategories);
            foreach ($subcategories as $parent_cat => $subs) {
                if (empty($subs)) continue;
                $total_count += count($subs);
                sort($subs);
                
                $html .= '<div style="margin-bottom: 24px;">';
                $html .= '<h4 style="color: var(--nymia-text-primary); margin-bottom: 12px; font-size: 1rem; font-weight: 600; padding-bottom: 8px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">' . esc_html($parent_cat) . '</h4>';
                $html .= '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">';
                
                foreach ($subs as $sub) {
                    $html .= '<div class="nymia-ebook-subcategory-item" data-parent="' . esc_attr($parent_cat) . '" data-subcategory="' . esc_attr($sub) . '" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">';
                    $html .= '<span style="color: var(--nymia-text-primary); font-weight: 500; font-size: 0.9rem;">' . esc_html($sub) . '</span>';
                    $html .= '<button type="button" class="nymia-delete-ebook-subcategory-btn" data-parent="' . esc_attr($parent_cat) . '" data-subcategory="' . esc_attr($sub) . '" style="color: var(--nymia-danger); background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity=\'1\'" onmouseout="this.style.opacity=\'0.7\'" title="' . esc_attr__('Delete Sub-Category', 'nymia') . '">×</button>';
                    $html .= '</div>';
                }
                
                $html .= '</div></div>';
            }
        }
        
        wp_send_json_success(array(
            'html' => $html,
            'count' => $total_count
        ));
    }
}
add_action('wp_ajax_nymia_get_ebook_subcategories', 'nymia_ajax_get_ebook_subcategories');

/**
 * AJAX HANDLER: Get Ebook Sub-Categories by Parent
 * ------------------------------------------------
 * Returns sub-categories for a specific parent category
 */
if (!function_exists('nymia_ajax_get_ebook_subcategories_by_parent')) {
    function nymia_ajax_get_ebook_subcategories_by_parent() {
        // Verify nonce
        check_ajax_referer('nymia_ebook_subcategory_action', 'nonce');
        
        $parent_category = isset($_POST['parent_category']) ? sanitize_text_field($_POST['parent_category']) : '';
        
        if (empty($parent_category)) {
            wp_send_json_error(array('message' => __('Parent category is required', 'nymia')));
            return;
        }
        
        $subcategories = nymia_get_ebook_subcategories();
        
        if (isset($subcategories[$parent_category]) && !empty($subcategories[$parent_category])) {
            wp_send_json_success(array('data' => $subcategories[$parent_category]));
        } else {
            wp_send_json_success(array('data' => array()));
        }
    }
}
add_action('wp_ajax_nymia_get_ebook_subcategories_by_parent', 'nymia_ajax_get_ebook_subcategories_by_parent');

/**
 * AJAX HANDLER: Add Ebook Language
 */
function nymia_ajax_add_ebook_language() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_ebook_language_action', 'nonce');
    
    $language_name = isset($_POST['language_name']) ? sanitize_text_field($_POST['language_name']) : '';
    
    if (empty($language_name)) {
        wp_send_json_error(array('message' => __('Language name is required', 'nymia')));
        return;
    }
    
    $languages = get_transient('nymia_ebook_languages');
    if (!is_array($languages)) {
        $languages = array();
    }
    
    if (in_array($language_name, $languages, true)) {
        wp_send_json_error(array('message' => __('Language already exists', 'nymia')));
        return;
    }
    
    $languages[] = $language_name;
    $languages = array_unique(array_map('sanitize_text_field', $languages));
    sort($languages);
    
    set_transient('nymia_ebook_languages', $languages, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array(
        'message' => __('Language added successfully', 'nymia'),
        'languages' => $languages,
        'language' => $language_name
    ));
}
add_action('wp_ajax_nymia_add_ebook_language', 'nymia_ajax_add_ebook_language');

/**
 * AJAX HANDLER: Send Contact Form Message
 */
function nymia_send_contact_message() {
    check_ajax_referer('nymia_contact_form', 'nonce');
    
    $name = isset($_POST['name']) ? trim(sanitize_text_field($_POST['name'])) : '';
    $email = isset($_POST['email']) ? trim(sanitize_email($_POST['email'])) : '';
    $subject = isset($_POST['subject']) ? trim(sanitize_text_field($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? trim(sanitize_textarea_field($_POST['message'])) : '';
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        wp_send_json_error(array('message' => __('Please fill in all required fields.', 'nymia')));
        return;
    }
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => __('Please enter a valid email address.', 'nymia')));
        return;
    }
    
    // Get recipient email (prefer contact email from settings, fallback to admin email)
    $recipient_email = get_option('nymia_contact_email', '');
    if (empty($recipient_email) || !is_email($recipient_email)) {
        $recipient_email = get_option('admin_email');
    }
    
    // Final validation - ensure we have a valid recipient email
    if (empty($recipient_email) || !is_email($recipient_email)) {
        wp_send_json_error(array('message' => __('Contact email is not configured. Please contact the site administrator.', 'nymia')));
        return;
    }
    
    $admin_email = get_option('admin_email');
    if (empty($admin_email) || !is_email($admin_email)) {
        $admin_email = $recipient_email; // Fallback to recipient email if admin email is invalid
    }
    
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    // ========================================
    // EMAIL 1: Send to Admin/Recipient
    // ========================================
    $admin_email_subject = sprintf(__('[%s] New Contact Form Message: %s', 'nymia'), $site_name, $subject);
    
    $admin_email_body = sprintf(
        __("You have received a new contact form message from %s.\n\n", 'nymia'),
        $site_name
    );
    $admin_email_body .= sprintf(__("Name: %s\n", 'nymia'), $name);
    $admin_email_body .= sprintf(__("Email: %s\n", 'nymia'), $email);
    $admin_email_body .= sprintf(__("Subject: %s\n\n", 'nymia'), $subject);
    $admin_email_body .= __("Message:\n", 'nymia');
    $admin_email_body .= $message . "\n\n";
    $admin_email_body .= "---\n";
    $admin_email_body .= sprintf(__("This message was sent from the contact form on %s\n", 'nymia'), $site_url);
    $admin_email_body .= sprintf(__("Date: %s", 'nymia'), current_time('mysql'));
    
    // Prepare email headers for admin email
    $admin_headers = array();
    $admin_headers[] = sprintf('From: %s <%s>', $site_name, $admin_email);
    $admin_headers[] = sprintf('Reply-To: %s <%s>', $name, $email);
    $admin_headers[] = 'Content-Type: text/plain; charset=UTF-8';
    
    // Send email to admin
    $admin_sent = wp_mail($recipient_email, $admin_email_subject, $admin_email_body, $admin_headers);
    
    // ========================================
    // EMAIL 2: Send Confirmation to User
    // ========================================
    $user_email_subject = sprintf(__('Thank you for contacting %s', 'nymia'), $site_name);
    
    $user_email_body = sprintf(__("Dear %s,\n\n", 'nymia'), $name);
    $user_email_body .= sprintf(__("Thank you for contacting %s. We have received your message and will get back to you as soon as possible.\n\n", 'nymia'), $site_name);
    $user_email_body .= __("Your message details:\n", 'nymia');
    $user_email_body .= sprintf(__("Subject: %s\n", 'nymia'), $subject);
    $user_email_body .= sprintf(__("Message: %s\n\n", 'nymia'), $message);
    $user_email_body .= "---\n";
    $user_email_body .= sprintf(__("This is an automated confirmation email. Please do not reply to this email.\n", 'nymia'));
    $user_email_body .= sprintf(__("If you have any urgent questions, please contact us directly at %s\n\n", 'nymia'), $recipient_email);
    $user_email_body .= sprintf(__("Best regards,\n%s Team", 'nymia'), $site_name);
    
    // Prepare email headers for user confirmation
    $user_headers = array();
    $user_headers[] = sprintf('From: %s <%s>', $site_name, $admin_email);
    $user_headers[] = 'Content-Type: text/plain; charset=UTF-8';
    
    // Send confirmation email to user
    $user_sent = wp_mail($email, $user_email_subject, $user_email_body, $user_headers);
    
    // ========================================
    // STORE: Save to database as backup
    // ========================================
    $contact_data = array(
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'date' => current_time('mysql'),
        'admin_email_sent' => $admin_sent ? 'yes' : 'no',
        'user_email_sent' => $user_sent ? 'yes' : 'no',
    );
    
    // Store in options (as a log - keep last 100 messages)
    $contact_logs = get_option('nymia_contact_form_logs', array());
    if (!is_array($contact_logs)) {
        $contact_logs = array();
    }
    array_unshift($contact_logs, $contact_data);
    // Keep only last 100 entries
    $contact_logs = array_slice($contact_logs, 0, 100);
    update_option('nymia_contact_form_logs', $contact_logs);
    
    // ========================================
    // RESPONSE
    // ========================================
    if ($admin_sent) {
        // Log success
        error_log('Contact form: Admin email sent successfully to ' . $recipient_email . ' from ' . $email);
        
        if ($user_sent) {
            error_log('Contact form: User confirmation email sent successfully to ' . $email);
    } else {
            error_log('Contact form: User confirmation email failed to send to ' . $email);
        }
        
        wp_send_json_success(array(
            'message' => __('Thank you! Your message has been sent successfully. You will receive a confirmation email shortly.', 'nymia')
        ));
    } else {
        // Log error
        $error_details = array(
            'recipient' => $recipient_email,
            'sender' => $email,
            'php_mail_error' => error_get_last()
        );
        error_log('Contact form email failed to send: ' . print_r($error_details, true));
        
        // Check if it's a server configuration issue
        $error_message = __('Sorry, there was an error sending your message. This might be a server configuration issue. Please try again later or contact us directly at ', 'nymia') . $recipient_email;
        
        wp_send_json_error(array('message' => $error_message));
    }
}
add_action('wp_ajax_nymia_send_contact_message', 'nymia_send_contact_message');
add_action('wp_ajax_nopriv_nymia_send_contact_message', 'nymia_send_contact_message');

/**
 * AJAX HANDLER: Delete Ebook Language
 */
function nymia_ajax_delete_ebook_language() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_ebook_language_action', 'nonce');
    
    $language_name = isset($_POST['language_name']) ? sanitize_text_field($_POST['language_name']) : '';
    
    if (empty($language_name)) {
        wp_send_json_error(array('message' => __('Language name is required', 'nymia')));
        return;
    }
    
    $languages = get_transient('nymia_ebook_languages');
    if (!is_array($languages)) {
        $languages = array();
    }
    
    $languages = array_filter($languages, function($lang) use ($language_name) {
        return $lang !== $language_name;
    });
    $languages = array_values($languages);
    $languages = array_unique(array_map('sanitize_text_field', $languages));
    sort($languages);
    
    set_transient('nymia_ebook_languages', $languages, 30 * DAY_IN_SECONDS);
    
    wp_send_json_success(array(
        'message' => __('Language deleted successfully', 'nymia'),
        'languages' => $languages
    ));
}
add_action('wp_ajax_nymia_delete_ebook_language', 'nymia_ajax_delete_ebook_language');

// Handle Stripe Checkout success redirect
add_action('template_redirect', function() {
    if (is_page('checkout') && isset($_GET['payment']) && $_GET['payment'] === 'success' && isset($_GET['session_id'])) {
        nymia_checkout_success_handler();
    }
});

// ==========================================
// CART MANAGEMENT FUNCTIONS
// ==========================================

/**
 * Get user's cart
 */
function nymia_get_cart() {
    if (!is_user_logged_in()) {
        // For guests, use session/cookie
        if (isset($_COOKIE['nymia_cart'])) {
            $cart = json_decode(stripslashes($_COOKIE['nymia_cart']), true);
            return is_array($cart) ? $cart : array();
        }
        return array();
    }
    
    $user_id = get_current_user_id();
    $cart = get_user_meta($user_id, 'nymia_cart', true);
    $cart_array = is_array($cart) ? $cart : array();
    
    // Debug logging
    error_log('NYMIA_CART_GET: User ID: ' . $user_id . ', Cart items: ' . count($cart_array));
    
    return $cart_array;
}

/**
 * Add item to cart
 * @return array|bool Returns array with 'success' and 'message' keys, or false on critical error
 */
function nymia_add_to_cart($item_type, $item_id) {
    if (!in_array($item_type, array('ebook', 'audio', 'audio_book'))) {
        return array('success' => false, 'message' => __('Invalid item type.', 'nymia'));
    }
    
    $cart = nymia_get_cart();
    
    // Check if item already in cart
    foreach ($cart as $key => $item) {
        if ($item['type'] === $item_type && (string)$item['id'] === (string)$item_id) {
            return array('success' => true, 'message' => __('Item is already in your cart.', 'nymia'));
        }
    }
    
    // Get item details
    $item_data = null;
    if ($item_type === 'ebook') {
        $all_ebooks = function_exists('nymia_get_all_ebooks') ? nymia_get_all_ebooks() : array();
        if (empty($all_ebooks)) {
            return array('success' => false, 'message' => __('Ebook library is empty or not loaded. Please refresh the page.', 'nymia'));
        }
        foreach ($all_ebooks as $ebook) {
            // Try multiple ID comparison methods
            $ebook_id = isset($ebook['id']) ? $ebook['id'] : '';
            if (empty($ebook_id)) {
                continue;
            }
            // Compare as strings and integers
            if ((string)$ebook_id === (string)$item_id || (int)$ebook_id === (int)$item_id) {
                $item_data = $ebook;
                break;
            }
        }
    } elseif ($item_type === 'audio') {
        $all_audio = get_transient('nymia_all_audio');
        if (!$all_audio || !is_array($all_audio)) {
            return array('success' => false, 'message' => __('Audio library is empty or not loaded. Please refresh the page.', 'nymia'));
        }
        foreach ($all_audio as $audio) {
            // Try multiple ID comparison methods
            $audio_id = isset($audio['id']) ? $audio['id'] : '';
            if (empty($audio_id)) {
                continue;
            }
            // Compare as strings and integers
            if ((string)$audio_id === (string)$item_id || (int)$audio_id === (int)$item_id) {
                $item_data = $audio;
                break;
            }
        }
    }
    
    if (!$item_data) {
        return array('success' => false, 'message' => sprintf(__('Item not found. Item ID: %s, Type: %s', 'nymia'), esc_html($item_id), esc_html($item_type)));
    }
    
    // Check if already purchased
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        if ($item_type === 'ebook' && function_exists('nymia_user_has_ebook_access')) {
            if (nymia_user_has_ebook_access($user_id, $item_id)) {
                return array('success' => false, 'message' => __('You already have access to this item.', 'nymia'));
            }
        } elseif ($item_type === 'audio' && function_exists('nymia_user_has_audio_access')) {
            if (nymia_user_has_audio_access($user_id, $item_id)) {
                return array('success' => false, 'message' => __('You already have access to this item.', 'nymia'));
            }
        }
    }
    
    // Check if item is paid
    $paid_access = $item_data['paid_access'] ?? '';
    $price = (float)($item_data['price'] ?? 0);
    $is_paid = (!empty($paid_access) && $paid_access === 'yes' && $price > 0);
    
    if (!$is_paid) {
        if (empty($paid_access) || $paid_access !== 'yes') {
            return array('success' => false, 'message' => __('This item is not set as a paid item.', 'nymia'));
        }
        if ($price <= 0) {
            return array('success' => false, 'message' => __('This item has no price set.', 'nymia'));
        }
        return array('success' => false, 'message' => __('This item is free and does not require purchase.', 'nymia'));
    }
    
    // Add to cart
    $cart[] = array(
        'type' => $item_type,
        'id' => (string)$item_id,
        'title' => $item_data['title'] ?? ($item_type === 'ebook' ? 'Ebook' : 'Audio Track'),
        'author' => $item_data['author'] ?? 'Unknown',
        'price' => (float)($item_data['price'] ?? 0),
        'image' => $item_data['thumbnail'] ?? $item_data['image'] ?? $item_data['cover_image'] ?? '',
        'user_id' => isset($item_data['user_id']) ? intval($item_data['user_id']) : 0,
    );
    
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $updated = update_user_meta($user_id, 'nymia_cart', $cart);
        
        // Verify the cart was saved
        $saved_cart = get_user_meta($user_id, 'nymia_cart', true);
        error_log('NYMIA_CART_SAVE: User ID: ' . $user_id . ', Cart items: ' . count($cart) . ', Saved items: ' . (is_array($saved_cart) ? count($saved_cart) : 0));
        
        if (!$updated && get_user_meta($user_id, 'nymia_cart', true) !== $cart) {
            error_log('NYMIA_CART_SAVE_ERROR: Failed to save cart for user ' . $user_id);
            return array('success' => false, 'message' => __('Failed to save cart. Please try again.', 'nymia'));
        }
    } else {
        // Store in cookie for guests
        setcookie('nymia_cart', json_encode($cart), time() + (30 * DAY_IN_SECONDS), '/');
    }
    
    error_log('NYMIA_CART_ADD_SUCCESS: Item added. Cart now has ' . count($cart) . ' items');
    return array('success' => true, 'message' => __('Item added to cart successfully.', 'nymia'));
}

/**
 * Remove item from cart
 */
function nymia_remove_from_cart($item_type, $item_id) {
    $cart = nymia_get_cart();
    
    foreach ($cart as $key => $item) {
        if ($item['type'] === $item_type && (string)$item['id'] === (string)$item_id) {
            unset($cart[$key]);
            $cart = array_values($cart); // Re-index
            
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                update_user_meta($user_id, 'nymia_cart', $cart);
            } else {
                setcookie('nymia_cart', json_encode($cart), time() + (30 * DAY_IN_SECONDS), '/');
            }
            
            return true;
        }
    }
    
    return false;
}

/**
 * Clear cart
 */
function nymia_clear_cart() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        delete_user_meta($user_id, 'nymia_cart');
    } else {
        setcookie('nymia_cart', '', time() - 3600, '/');
    }
}

/**
 * Get cart total
 */
function nymia_get_cart_total() {
    $cart = nymia_get_cart();
    $total = 0;
    
    foreach ($cart as $item) {
        $total += (float)($item['price'] ?? 0);
    }
    
    return $total;
}

/**
 * AJAX: Add to cart
 */
function nymia_ajax_add_to_cart() {
    // Log incoming request for debugging
    error_log('NYMIA_ADD_TO_CART: Request received. POST data: ' . print_r($_POST, true));
    
    try {
        // Check if action is set correctly
        if (!isset($_POST['action']) || $_POST['action'] !== 'nymia_add_to_cart') {
            error_log('NYMIA_ADD_TO_CART: Invalid or missing action parameter');
            wp_send_json_error(array('message' => __('Invalid request. Please refresh the page and try again.', 'nymia')));
            return;
        }
        
        // Verify nonce first to prevent 400 errors
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (empty($nonce)) {
            error_log('NYMIA_ADD_TO_CART: Nonce is empty');
            wp_send_json_error(array('message' => __('Security check failed. Nonce is missing.', 'nymia')));
            return;
        }
        
        $nonce_verified = wp_verify_nonce($nonce, 'nymia_cart');
        if (!$nonce_verified) {
            error_log('NYMIA_ADD_TO_CART: Nonce verification failed. Nonce: ' . $nonce);
            wp_send_json_error(array('message' => __('Security check failed. Please refresh the page and try again.', 'nymia')));
            return;
        }
        
        if (!is_user_logged_in()) {
            error_log('NYMIA_ADD_TO_CART: User not logged in');
            wp_send_json_error(array('message' => __('Please log in to add items to cart.', 'nymia')));
            return;
        }
        
        $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : '';
        $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';
        
        error_log('NYMIA_ADD_TO_CART: Item type: ' . $item_type . ', Item ID: ' . $item_id);
        
        if (empty($item_type) || empty($item_id)) {
            error_log('NYMIA_ADD_TO_CART: Missing item type or ID');
            wp_send_json_error(array('message' => __('Invalid item. Missing item type or ID.', 'nymia')));
            return;
        }
        
        $result = nymia_add_to_cart($item_type, $item_id);
        
        // Handle new return format (array with success and message)
        if (is_array($result)) {
            if ($result['success']) {
                // Get fresh cart data after adding item
                $cart = nymia_get_cart();
                error_log('NYMIA_AJAX_ADD_TO_CART_SUCCESS: Cart now has ' . count($cart) . ' items');
                wp_send_json_success(array(
                    'message' => isset($result['message']) ? $result['message'] : __('Item added to cart.', 'nymia'),
                    'cart_count' => count($cart),
                    'cart_total' => nymia_get_cart_total(),
                    'cart' => $cart // Include cart data in response for debugging
                ));
            } else {
                wp_send_json_error(array('message' => $result['message']));
            }
        } else {
            // Backward compatibility - if function returns boolean
            if ($result) {
                $cart = nymia_get_cart();
                wp_send_json_success(array(
                    'message' => __('Item added to cart.', 'nymia'),
                    'cart_count' => count($cart),
                    'cart_total' => nymia_get_cart_total()
                ));
            } else {
                wp_send_json_error(array('message' => __('Failed to add item to cart. Item may already be in cart, already purchased, or is free.', 'nymia')));
            }
        }
    } catch (Exception $e) {
        error_log('NYMIA Add to Cart Error: ' . $e->getMessage());
        error_log('NYMIA Add to Cart Error Trace: ' . $e->getTraceAsString());
        wp_send_json_error(array('message' => __('An error occurred while adding item to cart. Please try again.', 'nymia')));
    } catch (Throwable $e) {
        // Catch both Exception and Error (PHP 7+)
        error_log('NYMIA Add to Cart Fatal Error: ' . $e->getMessage());
        error_log('NYMIA Add to Cart Fatal Error Trace: ' . $e->getTraceAsString());
        wp_send_json_error(array('message' => __('A system error occurred. Please contact support.', 'nymia')));
    }
}
// Register AJAX handler for logged-in users
add_action('wp_ajax_nymia_add_to_cart', 'nymia_ajax_add_to_cart');

/**
 * AJAX: Remove from cart
 */
function nymia_ajax_remove_from_cart() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_cart', 'nonce');
    
    $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : '';
    $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';
    
    if (empty($item_type) || empty($item_id)) {
        wp_send_json_error(array('message' => __('Invalid item.', 'nymia')));
        return;
    }
    
    $result = nymia_remove_from_cart($item_type, $item_id);
    
    if ($result) {
        $cart = nymia_get_cart();
        wp_send_json_success(array(
            'message' => __('Item removed from cart.', 'nymia'),
            'cart_count' => count($cart),
            'cart_total' => nymia_get_cart_total()
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to remove item from cart.', 'nymia')));
    }
}
add_action('wp_ajax_nymia_remove_from_cart', 'nymia_ajax_remove_from_cart');

/**
 * AJAX: Get cart
 */
function nymia_ajax_get_cart() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_cart', 'nonce');
    
    $cart = nymia_get_cart();
    wp_send_json_success(array(
        'cart' => $cart,
        'cart_count' => count($cart),
        'cart_total' => nymia_get_cart_total()
    ));
}
add_action('wp_ajax_nymia_get_cart', 'nymia_ajax_get_cart');

