<?php
/**
 * ========================================
 * NYMIA THEME - USER PROFILE PAGE
 * ========================================
 * User profile page template
 * Displays: User info, avatar, cover image, stats, social links
 * Features: Dynamic user data, profile editing, image uploads
 * Includes: Email verification status and alert
 * 
 * @package Nymia
 * @version 1.0
 */

// ========================================
// GET USER DATA (Current or Target User)
// ========================================

// CHECK: If user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/'));
    exit;
}

// GET: Current logged-in user
$current_logged_in_user = wp_get_current_user();

// GET: Target user from URL parameter (prioritize username, fallback to user_id for backward compatibility)
$viewed_username = isset($_GET['username']) ? sanitize_text_field($_GET['username']) : '';
$viewed_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// VALIDATE: If viewing another user's profile, check if user exists
if (!empty($viewed_username)) {
    // Try to get user by username (preferred method)
    $target_user = get_user_by('login', $viewed_username);
    if (!$target_user) {
        // User doesn't exist, redirect to own profile
        wp_redirect(home_url('/profile/'));
        exit;
    }
    $profile_user = $target_user;
} elseif ($viewed_user_id > 0) {
    // Fallback: Try to get user by ID (backward compatibility)
    $target_user = get_user_by('ID', $viewed_user_id);
    if (!$target_user) {
        // User doesn't exist, redirect to own profile
        wp_redirect(home_url('/profile/'));
        exit;
    }
    $profile_user = $target_user;
    // REDIRECT: Update URL to use username instead of user_id
    $username = $profile_user->user_login;
    wp_redirect(home_url('/profile/?username=' . urlencode($username)));
    exit;
} else {
    // Viewing own profile
    $profile_user = $current_logged_in_user;
}

// CHECK: If profile user data is valid
if (!$profile_user || !$profile_user->ID) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();

// GET: Basic user information
$full_name = $profile_user->display_name;
$user_login = $profile_user->user_login;
$user_email = $profile_user->user_email;
$user_website = $profile_user->user_url;

// ========================================
// FETCH CUSTOM USER META DATA
// ========================================

// CHECK: If viewing own profile (for editing capabilities)
$is_own_profile = ($profile_user->ID === $current_logged_in_user->ID);

$user_phone = get_user_meta($profile_user->ID, 'phone', true) ?: '+1 (555) 000-0000';
$user_location = get_user_meta($profile_user->ID, 'location', true) ?: 'Not specified';
$user_stripe_account = get_user_meta($profile_user->ID, 'stripe_account_no', true);
if (empty($user_stripe_account)) {
    $user_stripe_account = get_user_meta($profile_user->ID, 'nymia_stripe_account_id', true);
}
$display_stripe_account = $user_stripe_account ? esc_html($user_stripe_account) : __('Not linked', 'nymia');
$user_swift_bic = get_user_meta($profile_user->ID, 'nymia_swift_bic', true);
$display_swift_bic = $user_swift_bic ? esc_html($user_swift_bic) : __('Not set', 'nymia');
$user_description = get_user_meta($profile_user->ID, 'description', true) ?: get_user_meta($profile_user->ID, 'bio', true) ?: 'No bio yet.' . ($is_own_profile ? ' Click "Edit Profile" to add one!' : '');

// Get registration date
$user_registered_date = get_userdata($profile_user->ID)->user_registered;
$user_registered = date_i18n('F j, Y', strtotime($user_registered_date));

// Get user roles
$user_roles_array = $profile_user->roles;
$user_roles = !empty($user_roles_array) ? implode(', ', array_map(function($role) {
    // Show "Nymi" for subscriber role instead of "Subscriber"
    if (strtolower($role) === 'subscriber') {
        return 'Nymi';
    }
    // Show "Creator" for administrator and author roles instead of their role names
    if (strtolower($role) === 'administrator' || strtolower($role) === 'author') {
        return 'Creator';
    }
    return ucfirst($role);
}, $user_roles_array)) : 'User';
$user_is_creator = in_array('creator', (array) $user_roles_array, true);
$user_is_admin = in_array('administrator', (array) $user_roles_array, true);
$user_can_manage_stripe = $user_is_creator || $user_is_admin;

// Get avatar
$user_avatar = get_avatar_url($profile_user->ID, array('size' => 120));
$custom_avatar = get_user_meta($profile_user->ID, 'custom_avatar', true);
if ($custom_avatar) {
    $user_avatar = esc_url($custom_avatar);
}

// Get cover image
$user_cover_image = get_user_meta($profile_user->ID, 'cover_image', true);
$cover_image_style = '';
if ($user_cover_image) {
    $cover_image_style = 'background-image: url(' . esc_url($user_cover_image) . ');';
}

// Get social links
$facebook_url = get_user_meta($profile_user->ID, 'facebook', true);
$twitter_url = get_user_meta($profile_user->ID, 'twitter', true);
$linkedin_url = get_user_meta($profile_user->ID, 'linkedin', true);
$github_url = get_user_meta($profile_user->ID, 'github', true);

// Check email verification status (only for own profile)
if ($is_own_profile) {
    $email_verified = get_user_meta($profile_user->ID, 'email_verified', true);
    $verification_code = get_user_meta($profile_user->ID, 'verification_code', true);
    $verification_expiry = get_user_meta($profile_user->ID, 'verification_expiry', true);
    $is_unverified = ($email_verified !== '1');
} else {
    // For other users, just check if verified (show badge only)
    $email_verified = get_user_meta($profile_user->ID, 'email_verified', true);
    $is_unverified = false; // Don't show verification alert for other users
}

$creator_verified = nymia_is_creator_verified($profile_user->ID);
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-profile-container">
            <!-- Profile Header -->
            <div class="nymia-profile-header">
                <div class="nymia-profile-cover" style="<?php echo $cover_image_style; ?> position: relative; overflow: hidden;">
                    <?php if ($user_cover_image): ?>
                        <img src="<?php echo esc_url($user_cover_image); ?>" alt="<?php echo esc_attr($full_name); ?> Cover" style="width: 100%; height: 100%; object-fit: cover;" />
                    <?php endif; ?>
                    <?php if ($is_own_profile): ?>
                    <input type="file" id="cover_image_upload_profile" accept="image/*" style="display: none;" />
                    <button type="button" class="nymia-cover-upload-btn" onclick="document.getElementById('cover_image_upload_profile').click();" title="<?php esc_attr_e('Change Cover Image', 'nymia'); ?>" style="position: absolute; bottom: 15px; right: 15px; background: rgba(191, 76, 26, 0.9); border: none; color: white; padding: 10px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6"></path>
                            <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                            <path d="M1 12h6m6 0h6"></path>
                            <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="nymia-profile-info-header">
                    <div class="nymia-profile-avatar-wrap <?php echo nymia_is_creator_verified($profile_user->ID) ? 'has-creator-badge' : ''; ?>">
                    <img src="<?php echo esc_url($user_avatar); ?>" 
                         alt="<?php echo esc_attr($full_name); ?>" 
                         class="nymia-profile-avatar" 
                         width="120" 
                         height="120"
                         onerror="this.onerror=null; this.src='<?php echo get_template_directory_uri(); ?>/assets/images/profile.png';" />
                    </div>
                    <div class="nymia-profile-name-section">
                        <h1><?php echo esc_html($full_name); ?></h1>
                        <div class="nymia-username-section">
                            <p class="nymia-profile-username">@<?php echo esc_html($user_login); ?></p>
                            <?php if ($is_unverified): ?>
                                <div class="nymia-verification-badge nymia-verification-pending">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span>Email not verified</span>
                                </div>
                            <?php endif; ?>
                            <?php
                            // Show appropriate badge (creator badge for creators, verified user badge for regular users)
                            $user_badge_markup = nymia_get_user_badge_markup($profile_user->ID, null, 'nymia-creator-badge--inline');
                            if ($user_badge_markup) {
                                echo wp_kses_post($user_badge_markup);
                            }
                            ?>
                        </div>
                        
                        <?php
                        // Get follower and following counts
                        $followers_count = nymia_get_followers_count($profile_user->ID);
                        $following_count = nymia_get_following_count($profile_user->ID);
                        ?>
                        
                        <!-- Follower/Following Counts -->
                        <div class="nymia-profile-follow-stats" style="display: flex; gap: 20px; margin-top: 15px;">
                            <div class="nymia-follow-stat" style="cursor: pointer;" onclick="nymiaShowUserList(<?php echo $profile_user->ID; ?>, 'followers', <?php echo $is_own_profile ? 'true' : 'false'; ?>);">
                                <span class="nymia-follow-count" style="font-weight: 600; color: white;"><?php echo number_format($followers_count); ?></span>
                                <span class="nymia-follow-label" style="color: white; font-size: 14px;"><?php esc_html_e('Followers', 'nymia'); ?></span>
                            </div>
                            <div class="nymia-follow-stat" style="cursor: pointer;" onclick="nymiaShowUserList(<?php echo $profile_user->ID; ?>, 'following', <?php echo $is_own_profile ? 'true' : 'false'; ?>);">
                                <span class="nymia-follow-count" style="font-weight: 600; color: white;"><?php echo number_format($following_count); ?></span>
                                <span class="nymia-follow-label" style="color: white; font-size: 14px;"><?php esc_html_e('Following', 'nymia'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="nymia-profile-action-buttons">
                        <?php if (!$is_own_profile): ?>
                        <?php
                        // Check if creator is available for instant calls
                        $is_available = function_exists('nymia_is_creator_available') ? nymia_is_creator_available($profile_user->ID) : false;
                        $per_minute_price = $is_available ? floatval(get_user_meta($profile_user->ID, 'nymia_available_per_minute_price', true)) : 0;
                        ?>
                        <?php if ($is_available && $per_minute_price > 0): ?>
                        <div class="nymia-available-indicator" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 12px; margin-right: 8px;">
                            <span class="nymia-green-light" style="width: 10px; height: 10px; background: #22c55e; border-radius: 50%; display: inline-block; animation: pulse-green 2s infinite;"></span>
                            <span style="color: #22c55e; font-size: 0.9rem; font-weight: 500;"><?php esc_html_e('Available Now', 'nymia'); ?></span>
                        </div>
                        <button type="button" class="nymia-call-now-btn" id="nymiaCallNowBtn" data-creator-id="<?php echo $profile_user->ID; ?>" data-per-minute-price="<?php echo esc_attr($per_minute_price); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; margin-right: 6px;">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <?php esc_html_e('Call Now', 'nymia'); ?>
                            <span style="margin-left: 6px; font-size: 0.85rem; opacity: 0.9;">$<?php echo number_format($per_minute_price, 2); ?>/min</span>
                        </button>
                        <?php endif; ?>
                        <?php 
                        // Show tip button if user is a creator
                        if ($creator_verified || $user_is_creator || $user_is_admin): ?>
                        <button type="button" class="nymia-tip-btn" id="nymiaTipBtn" data-creator-id="<?php echo $profile_user->ID; ?>" data-creator-name="<?php echo esc_attr($full_name); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; margin-right: 6px;">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            <?php esc_html_e('Tip', 'nymia'); ?>
                        </button>
                        <?php endif; ?>
                        <button type="button" class="nymia-messenger-btn" title="Message" onclick="openChat(<?php echo $profile_user->ID; ?>, '<?php echo esc_js($full_name); ?>', '<?php echo esc_js($user_avatar); ?>');">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                <line x1="9" y1="10" x2="15" y2="10"></line>
                                <line x1="9" y1="14" x2="15" y2="14"></line>
                            </svg>
                        </button>
                        <?php 
                        // Check if current user is following this profile user
                        $is_following = nymia_is_following($current_logged_in_user->ID, $profile_user->ID);
                        $follow_btn_text = $is_following ? __('Following', 'nymia') : __('Follow', 'nymia');
                        $follow_btn_class = $is_following ? 'following' : '';
                        $profile_username = $profile_user->user_login;
                        ?>
                        <button type="button" class="nymia-follow-btn <?php echo esc_attr($follow_btn_class); ?>" id="followBtn" data-username="<?php echo esc_attr($profile_username); ?>"><?php echo esc_html($follow_btn_text); ?></button>
                        <?php else: ?>
                        <button type="button" class="nymia-edit-profile-btn" id="openEditModal"><?php esc_html_e('Edit Profile', 'nymia'); ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($is_own_profile && $is_unverified): ?>
                <!-- Email Verification Alert -->
                <div class="nymia-verify-alert" id="nymia-verify-alert">
                    <div class="nymia-verify-alert-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <div class="nymia-verify-alert-content">
                        <h3>Verify Your Email Address</h3>
                        <p>We've sent a verification code to <strong><?php echo esc_html($user_email); ?></strong>. Please check your inbox and enter the code below.</p>
                        
                        <form id="nymia-verify-email-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                            <?php wp_nonce_field('nymia_verify_profile_email', 'nymia_verify_profile_nonce'); ?>
                            <input type="hidden" name="action" value="nymia_verify_profile_email">
                            <input type="hidden" name="user_id" value="<?php echo $current_user->ID; ?>">
                            
                            <div class="nymia-verify-input-group">
                                <input type="text" id="profile_verification_code" name="verification_code" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
                                <button type="submit" class="nymia-verify-btn">Verify Email</button>
                            </div>
                            
                            <button type="button" class="nymia-resend-code-btn" onclick="nymia_resend_profile_code()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 4 23 10 17 10"></polyline>
                                    <polyline points="1 20 1 14 7 14"></polyline>
                                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4-4.64 4.36A9 9 0 0 1 3.51 15"></path>
                                </svg>
                                Resend Code
                            </button>
                        </form>
                    </div>
                    <button class="nymia-verify-alert-close" onclick="document.getElementById('nymia-verify-alert').remove()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Profile Content -->
            <div class="nymia-profile-content">
                <!-- About Section -->
                <div class="nymia-profile-card">
                    <h2><?php esc_html_e('About', 'nymia'); ?></h2>
                    <div class="nymia-profile-about">
                        <p><?php echo esc_html($user_description); ?></p>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="nymia-profile-card">
                    <h2><?php esc_html_e('Personal Information', 'nymia'); ?></h2>
                    <div class="nymia-profile-details">
                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Full Name', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo esc_html($full_name); ?></span>
                            </div>
                        </div>

                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Username', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo esc_html($user_login); ?></span>
                            </div>
                        </div>

                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Email', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo esc_html($user_email); ?></span>
                            </div>
                        </div>

                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Phone', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo esc_html($user_phone); ?></span>
                            </div>
                        </div>

                        <?php if ($user_can_manage_stripe): ?>
                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="5" width="22" height="14" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                                <path d="M7 15h.01"></path>
                                <path d="M11 15h2"></path>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Stripe Account No.', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo $display_stripe_account; ?></span>
                            </div>
                        </div>
                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('SWIFT/BIC Code', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo $display_swift_bic; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Location', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo esc_html($user_location); ?></span>
                            </div>
                        </div>

                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Joined', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo esc_html($user_registered); ?></span>
                            </div>
                        </div>

                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Role', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><?php echo esc_html($user_roles); ?></span>
                            </div>
                        </div>

                        <?php if ($user_website): ?>
                        <div class="nymia-profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                            <div>
                                <span class="nymia-detail-label"><?php esc_html_e('Website', 'nymia'); ?></span>
                                <span class="nymia-detail-value"><a href="<?php echo esc_url($user_website); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($user_website); ?></a></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="nymia-profile-card">
                    <h2><?php esc_html_e('Statistics', 'nymia'); ?></h2>
                    <div class="nymia-profile-stats">
                        <div class="nymia-stat-item" style="cursor: pointer;" onclick="nymiaShowUserList(<?php echo $profile_user->ID; ?>, 'followers', <?php echo $is_own_profile ? 'true' : 'false'; ?>);">
                            <div class="nymia-stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <div class="nymia-stat-content">
                                <?php
                                // Get real follower count
                                $followers_count = nymia_get_followers_count($profile_user->ID);
                                ?>
                                <span class="nymia-stat-value"><?php echo number_format($followers_count); ?></span>
                                <span class="nymia-stat-label"><?php esc_html_e('Followers', 'nymia'); ?></span>
                            </div>
                        </div>

                        <div class="nymia-stat-item" style="cursor: pointer;" onclick="nymiaShowUserList(<?php echo $profile_user->ID; ?>, 'following', <?php echo $is_own_profile ? 'true' : 'false'; ?>);">
                            <div class="nymia-stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <div class="nymia-stat-content">
                                <?php
                                // Get real following count
                                $following_count = nymia_get_following_count($profile_user->ID);
                                ?>
                                <span class="nymia-stat-value" style="cursor: pointer;" onclick="nymiaShowUserList(<?php echo $profile_user->ID; ?>, 'following', <?php echo $is_own_profile ? 'true' : 'false'; ?>);"><?php echo number_format($following_count); ?></span>
                                <span class="nymia-stat-label" style="cursor: pointer;" onclick="nymiaShowUserList(<?php echo $profile_user->ID; ?>, 'following', <?php echo $is_own_profile ? 'true' : 'false'; ?>);"><?php esc_html_e('Following', 'nymia'); ?></span>
                            </div>
                        </div>

                        <?php
                        // Get Posts Count (social posts by this user)
                        $posts_count = 0;
                        $posts_query = new WP_Query(array(
                            'post_type' => 'nymia_social_post',
                            'post_status' => 'publish',
                            'author' => $profile_user->ID,
                            'posts_per_page' => -1,
                            'fields' => 'ids'
                        ));
                        if ($posts_query) {
                            $posts_count = $posts_query->found_posts;
                        }
                        
                        // Get Favorites Count (liked posts + bookmarked ebooks)
                        $favorites_count = 0;
                        $liked_posts = get_user_meta($profile_user->ID, '_nymia_liked_posts', true);
                        if (is_array($liked_posts)) {
                            $favorites_count += count($liked_posts);
                        }
                        $bookmarked_ebooks = get_user_meta($profile_user->ID, '_nymia_ebook_bookmarks', true);
                        if (is_array($bookmarked_ebooks)) {
                            $favorites_count += count($bookmarked_ebooks);
                        }
                        ?>
                        <div class="nymia-stat-item">
                            <div class="nymia-stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14,2 14,8 20,8"></polyline>
                                </svg>
                            </div>
                            <div class="nymia-stat-content">
                                <span class="nymia-stat-value"><?php echo number_format($posts_count); ?></span>
                                <span class="nymia-stat-label"><?php esc_html_e('Posts', 'nymia'); ?></span>
                            </div>
                        </div>

                        <div class="nymia-stat-item">
                            <div class="nymia-stat-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                            <div class="nymia-stat-content">
                                <span class="nymia-stat-value"><?php echo number_format($favorites_count); ?></span>
                                <span class="nymia-stat-label"><?php esc_html_e('Favorites', 'nymia'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ======================================== -->
<!-- EDIT PROFILE MODAL (Only for own profile) -->
<!-- ======================================== -->
<?php if ($is_own_profile): ?>
<div id="editProfileModal" class="nymia-modal">
    <div class="nymia-modal-content">
        <div class="nymia-modal-header">
            <h2><?php esc_html_e('Edit Profile', 'nymia'); ?></h2>
            <button type="button" class="nymia-modal-close" id="closeEditModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <form id="editProfileForm" class="nymia-edit-profile-form" enctype="multipart/form-data">
            <!-- Profile Picture Upload -->
            <div class="nymia-form-group">
                <label><?php esc_html_e('Profile Picture', 'nymia'); ?></label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img id="profile_preview" src="<?php echo esc_url($user_avatar); ?>" alt="Profile Preview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.1);" />
                    <div>
                        <input type="file" id="profile_avatar" name="profile_avatar" accept="image/*" style="display: none;" />
                        <button type="button" class="nymia-btn-outline" onclick="document.getElementById('profile_avatar').click();">
                            <?php esc_html_e('Change Picture', 'nymia'); ?>
                        </button>
                        <p class="description" style="margin-top: 5px; color: #999; font-size: 0.9rem;">JPG, PNG or GIF (max. 2MB)</p>
                    </div>
                </div>
            </div>
            
            <!-- Cover Picture Upload -->
            <div class="nymia-form-group">
                <label><?php esc_html_e('Cover Picture (Banner)', 'nymia'); ?></label>
                <p class="description" style="margin-bottom: 12px; color: rgba(255, 255, 255, 0.6); font-size: 0.85rem; line-height: 1.5;">
                    <strong><?php esc_html_e('Recommended Banner Size:', 'nymia'); ?></strong> 1500x500 pixels (3:1 ratio)<br>
                    <?php esc_html_e('This banner will be displayed on:', 'nymia'); ?><br>
                    • <?php esc_html_e('Your profile page', 'nymia'); ?><br>
                    • <?php esc_html_e('Home feed (Recent Audio section)', 'nymia'); ?><br>
                    • <?php esc_html_e('Suggestions sidebar (400x250px will be cropped from your banner)', 'nymia'); ?>
                </p>
                <div style="position: relative;">
                    <div id="cover_preview" style="width: 100%; height: 200px; background: <?php echo $user_cover_image ? 'url(' . esc_url($user_cover_image) . ')' : 'linear-gradient(135deg, #1E1E1E 0%, #141414 100%)'; ?>; background-size: cover; background-position: center; border-radius: 8px; border: 2px dashed rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative;" onclick="document.getElementById('cover_image').click();">
                        <?php if ($user_cover_image): ?>
                            <img src="<?php echo esc_url($user_cover_image); ?>" alt="Cover Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;" />
                        <?php else: ?>
                            <div style="text-align: center; color: #999;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; margin: 0 auto 10px;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <p><?php esc_html_e('Click to upload cover image', 'nymia'); ?></p>
                                <p style="font-size: 0.8rem; margin-top: 5px; opacity: 0.7;"><?php esc_html_e('1500x500px recommended', 'nymia'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" style="display: none;" />
                    <div style="margin-top: 10px; display: flex; gap: 10px;">
                        <button type="button" class="nymia-btn-outline" style="flex: 1;" onclick="document.getElementById('cover_image').click();">
                            <?php echo $user_cover_image ? esc_html__('Change Banner', 'nymia') : esc_html__('Upload Banner', 'nymia'); ?>
                        </button>
                    <?php if ($user_cover_image): ?>
                        <button type="button" class="nymia-btn-outline" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #ef4444;" onclick="if(confirm('<?php echo esc_js(__('Are you sure you want to remove the banner?', 'nymia')); ?>')) { document.getElementById('cover_image').value = ''; document.getElementById('cover_preview').innerHTML = '<div style=\'text-align: center; color: #999; padding: 40px;\'><svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' style=\'width: 48px; height: 48px; margin: 0 auto 10px;\'><path d=\'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4\'></path><polyline points=\'17 8 12 3 7 8\'></polyline><line x1=\'12\' y1=\'3\' x2=\'12\' y2=\'15\'></line></svg><p><?php esc_html_e('Click to upload cover image', 'nymia'); ?></p></div>'; const formData = new FormData(); formData.append('action', 'nymia_update_profile'); formData.append('nonce', '<?php echo wp_create_nonce("nymia_profile_update"); ?>'); formData.append('remove_cover_image', '1'); fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData }).then(r => r.json()).then(d => { if(d.success) location.reload(); else alert('Failed to remove banner'); }); }">
                            <?php esc_html_e('Remove', 'nymia'); ?>
                        </button>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="nymia-form-row" style="margin-top: 24px;">
                <div class="nymia-form-group">
                    <label for="edit_full_name"><?php esc_html_e('Full Name', 'nymia'); ?></label>
                    <input type="text" id="edit_full_name" name="display_name" value="<?php echo esc_attr($full_name); ?>" required />
                </div>
                
                <div class="nymia-form-group">
                    <label for="edit_username"><?php esc_html_e('Username', 'nymia'); ?></label>
                    <input type="text" id="edit_username" name="user_login" value="<?php echo esc_attr($user_login); ?>" disabled />
                    <p class="description" style="margin-top: 5px; color: #999; font-size: 0.9rem;"><?php esc_html_e('Username cannot be changed', 'nymia'); ?></p>
                </div>
            </div>
            
            <div class="nymia-form-row">
                <div class="nymia-form-group">
                    <label for="edit_email"><?php esc_html_e('Email', 'nymia'); ?></label>
                    <input type="email" id="edit_email" name="user_email" value="<?php echo esc_attr($user_email); ?>" required />
                </div>
                
                <div class="nymia-form-group">
                    <label for="edit_phone"><?php esc_html_e('Phone', 'nymia'); ?></label>
                    <input type="tel" id="edit_phone" name="phone" value="<?php echo esc_attr($user_phone); ?>" />
                </div>
            </div>
            
            <div class="nymia-form-row">
                <div class="nymia-form-group">
                    <label for="edit_location"><?php esc_html_e('Location', 'nymia'); ?></label>
                    <input type="text" id="edit_location" name="location" value="<?php echo esc_attr($user_location); ?>" />
                </div>
                
                <div class="nymia-form-group">
                    <label for="edit_website"><?php esc_html_e('Website', 'nymia'); ?></label>
                    <input type="url" id="edit_website" name="user_url" value="<?php echo esc_attr($user_website); ?>" />
                </div>
            </div>

            <?php if ($user_can_manage_stripe): ?>
            <div class="nymia-form-row">
                <div class="nymia-form-group" style="flex:1;">
                    <label for="edit_stripe_account"><?php esc_html_e('Stripe Account No.', 'nymia'); ?></label>
                    <input type="text" id="edit_stripe_account" name="stripe_account_no" value="<?php echo esc_attr($user_stripe_account); ?>" placeholder="acct_1234ABC..." />
                    <p class="description" style="margin-top: 5px; color: #999; font-size: 0.9rem;"><?php esc_html_e('Enter your Stripe account ID (starts with acct_).', 'nymia'); ?></p>
                </div>
                <div class="nymia-form-group" style="flex:1;">
                    <label for="edit_swift_bic"><?php esc_html_e('SWIFT/BIC Code', 'nymia'); ?></label>
                    <input type="text" id="edit_swift_bic" name="swift_bic" value="<?php echo esc_attr($user_swift_bic); ?>" placeholder="SWIFT123" />
                    <p class="description" style="margin-top: 5px; color: #999; font-size: 0.9rem;"><?php esc_html_e('Enter your SWIFT or BIC code.', 'nymia'); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="nymia-form-group">
                <label for="edit_bio"><?php esc_html_e('Bio', 'nymia'); ?></label>
                <textarea id="edit_bio" name="description" rows="4"><?php echo esc_textarea($user_description); ?></textarea>
            </div>
            
            
            <div class="nymia-modal-footer">
                <button type="button" class="nymia-btn-outline" id="cancelEdit"><?php esc_html_e('Cancel', 'nymia'); ?></button>
                <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Save Changes', 'nymia'); ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editProfileModal');
    const openBtn = document.getElementById('openEditModal');
    const closeBtn = document.getElementById('closeEditModal');
    const cancelBtn = document.getElementById('cancelEdit');
    const form = document.getElementById('editProfileForm');
    
    // Edit Profile button handler
    if (openBtn) {
        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                console.error('Edit Profile modal not found');
                alert('Edit Profile modal not found. Please refresh the page.');
            }
        });
    }
    
    if (modal) {
        
        // Close modal function
        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        // Cancel button
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Profile picture preview
        const profileAvatar = document.getElementById('profile_avatar');
        const profilePreview = document.getElementById('profile_preview');
        
        if (profileAvatar && profilePreview) {
            profileAvatar.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Cover image preview in modal
        const coverImage = document.getElementById('cover_image');
        const coverPreview = document.getElementById('cover_preview');
        
        if (coverImage && coverPreview) {
            coverImage.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('<?php echo esc_js(__('File size too large. Maximum size is 5MB.', 'nymia')); ?>');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('<?php echo esc_js(__('Invalid file type. Please upload JPG, PNG, GIF, or WebP image.', 'nymia')); ?>');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        coverPreview.innerHTML = '<img src="' + e.target.result + '" alt="Cover Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;" />';
                        // Update change button text
                        const changeBtn = coverPreview.nextElementSibling?.querySelector('button');
                        if (changeBtn) {
                            changeBtn.textContent = '<?php echo esc_js(__('Change Banner', 'nymia')); ?>';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Cover image upload from profile page
        const coverImageUploadProfile = document.getElementById('cover_image_upload_profile');
        
        if (coverImageUploadProfile) {
            coverImageUploadProfile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Upload immediately
                    const formData = new FormData();
                    formData.append('action', 'nymia_update_profile');
                    formData.append('nonce', '<?php echo wp_create_nonce("nymia_profile_update"); ?>');
                    formData.append('cover_image', file);
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to upload cover image. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
                }
            });
        }
        
        // Handle form submission
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(form);
                formData.append('action', 'nymia_update_profile');
                formData.append('nonce', '<?php echo wp_create_nonce("nymia_profile_update"); ?>');
                
                // Disable submit button
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '<?php esc_html_e('Saving...', 'nymia'); ?>';
                
                // Send AJAX request
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        alert('<?php esc_html_e('Profile updated successfully!', 'nymia'); ?>');
                        
                        // Close modal
                        closeModal();
                        
                        // Reload page to show updated data
                        location.reload();
                    } else {
                        alert(data.data.message || '<?php esc_html_e('Failed to update profile', 'nymia'); ?>');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php esc_html_e('An error occurred. Please try again.', 'nymia'); ?>');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        }
    }
    
    // Email verification functionality
    const verifyEmailForm = document.getElementById('nymia-verify-email-form');
    if (verifyEmailForm) {
        verifyEmailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const codeInput = document.getElementById('profile_verification_code');
            
            // Validate code
            if (codeInput.value.length !== 6) {
                alert('Please enter a 6-digit verification code.');
                return;
            }
            
            // Disable submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying...';
            
            // Submit form
            fetch('<?php echo admin_url("admin-post.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('success')) {
                    alert('Email verified successfully!');
                    location.reload();
                } else {
                    alert('Invalid verification code. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Verify Email';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Verify Email';
            });
        });
    }
});

// Function to resend verification code
function nymia_resend_profile_code() {
    if (confirm('Resend verification code to your email?')) {
        const formData = new FormData();
        formData.append('action', 'nymia_resend_profile_code');
        formData.append('nymia_resend_profile_nonce', '<?php echo wp_create_nonce("nymia_resend_profile_code"); ?>');
        formData.append('user_id', '<?php echo $current_user->ID; ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert('Verification code has been resent to your email!');
                } else {
                    alert('Failed to resend code: ' + (data.data?.message || 'Please try again.'));
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                alert('Invalid response from server. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

// ========================================
// FOLLOW BUTTON FUNCTIONALITY
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const followBtn = document.getElementById('followBtn');
    
    if (followBtn) {
        const profileUsername = followBtn.getAttribute('data-username');
        
        if (profileUsername) {
            followBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const currentText = followBtn.textContent.trim();
                const isFollowing = currentText === 'Following' || followBtn.classList.contains('following');
                
                const ajaxConfig = window.nymiaAjax || (typeof nymiaAjax !== 'undefined' ? nymiaAjax : null);
                
                if (!ajaxConfig || !ajaxConfig.followNonce) {
                    alert('Configuration error. Please refresh the page.');
                    console.error('AJAX config missing:', ajaxConfig);
                    return;
                }
                
                followBtn.disabled = true;
                followBtn.textContent = isFollowing ? 'Unfollowing...' : 'Following...';
                
                const formData = new FormData();
                formData.append('action', 'nymia_toggle_follow');
                formData.append('username', profileUsername);
                formData.append('nonce', ajaxConfig.followNonce);
                
                fetch(ajaxConfig.ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    followBtn.disabled = false;
                    
                    if (data.success) {
                        if (data.data.is_following) {
                            followBtn.textContent = 'Following';
                            followBtn.classList.add('following');
                        } else {
                            followBtn.textContent = 'Follow';
                            followBtn.classList.remove('following');
                        }
                        
                        const followersStat = document.querySelector('.nymia-follow-stat:first-child .nymia-follow-count');
                        if (followersStat && data.data.followers_count !== undefined) {
                            followersStat.textContent = parseInt(data.data.followers_count).toLocaleString();
                        }
                    } else {
                        followBtn.textContent = isFollowing ? 'Following' : 'Follow';
                        if (isFollowing) {
                            followBtn.classList.add('following');
                        } else {
                            followBtn.classList.remove('following');
                        }
                        alert(data.data?.message || 'Something went wrong. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Follow toggle error:', error);
                    followBtn.disabled = false;
                    followBtn.textContent = isFollowing ? 'Following' : 'Follow';
                    if (isFollowing) {
                        followBtn.classList.add('following');
                    } else {
                        followBtn.classList.remove('following');
                    }
                    alert('Network error. Please try again.');
                });
            });
        } else {
            console.error('Profile username not found on Follow button');
        }
    } else {
        console.log('Follow button not found (this is normal for own profile)');
    }
    
    // ========================================
    // INSTANT CALL (CALL NOW) BUTTON
    // ========================================
    const callNowBtn = document.getElementById('nymiaCallNowBtn');
    if (callNowBtn) {
        callNowBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const creatorId = parseInt(this.dataset.creatorId || 0);
            const perMinutePrice = parseFloat(this.dataset.perMinutePrice || 0);
            
            if (!creatorId || perMinutePrice <= 0) {
                alert('<?php echo esc_js(__('Creator is not available for instant calls.', 'nymia')); ?>');
                return;
            }
            
            if (!confirm('<?php echo esc_js(__('You will be charged per minute for this call. Continue?', 'nymia')); ?>')) {
                return;
            }
            
            this.disabled = true;
            this.textContent = '<?php echo esc_js(__('Connecting...', 'nymia')); ?>';
            
            const formData = new FormData();
            formData.append('action', 'nymia_initiate_instant_call');
            formData.append('nonce', '<?php echo wp_create_nonce('nymia_instant_call'); ?>');
            formData.append('creator_id', creatorId);
            
            const ajaxConfig = window.nymiaAjax || (typeof nymiaAjax !== 'undefined' ? nymiaAjax : null);
            const ajaxUrl = ajaxConfig ? ajaxConfig.ajaxurl : '/wp-admin/admin-ajax.php';
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success && data.data && data.data.checkout_url) {
                    window.location.href = data.data.checkout_url;
                } else {
                    alert(data.data?.message || '<?php echo esc_js(__('Failed to initiate call. Please try again.', 'nymia')); ?>');
                    this.disabled = false;
                    this.textContent = '<?php echo esc_js(__('Call Now', 'nymia')); ?>';
                }
            })
            .catch(err => {
                console.error('Error initiating call:', err);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
                this.disabled = false;
                this.textContent = '<?php echo esc_js(__('Call Now', 'nymia')); ?>';
            });
        });
    }
    
    // ========================================
    // TIP BUTTON FUNCTIONALITY
    // ========================================
    const tipBtn = document.getElementById('nymiaTipBtn');
    if (tipBtn) {
        tipBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const creatorId = parseInt(this.dataset.creatorId || 0);
            const creatorName = this.dataset.creatorName || 'Creator';
            
            if (!creatorId) {
                alert('<?php echo esc_js(__('Invalid creator.', 'nymia')); ?>');
                return;
            }
            
            // Open tip modal
            const tipModal = document.getElementById('nymiaTipModal');
            if (tipModal) {
                tipModal.dataset.creatorId = creatorId;
                tipModal.dataset.creatorName = creatorName;
                tipModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    }
    
    // Tip modal functionality
    const tipModal = document.getElementById('nymiaTipModal');
    if (tipModal) {
        const closeTipModal = document.getElementById('closeTipModal');
        const cancelTipBtn = document.getElementById('cancelTipBtn');
        const tipForm = document.getElementById('tipForm');
        const tipAmountInput = document.getElementById('tip_amount');
        const tipCustomAmount = document.getElementById('tip_custom_amount');
        const tipQuickAmounts = document.querySelectorAll('.tip-quick-amount');
        
        // Close modal
        function closeTipModalFunc() {
            tipModal.classList.remove('active');
            document.body.style.overflow = '';
            if (tipForm) tipForm.reset();
            if (tipCustomAmount) tipCustomAmount.style.display = 'none';
        }
        
        if (closeTipModal) closeTipModal.addEventListener('click', closeTipModalFunc);
        if (cancelTipBtn) cancelTipBtn.addEventListener('click', closeTipModalFunc);
        
        tipModal.addEventListener('click', function(e) {
            if (e.target === tipModal) {
                closeTipModalFunc();
            }
        });
        
        // Quick amount selection
        tipQuickAmounts.forEach(btn => {
            btn.addEventListener('click', function() {
                tipQuickAmounts.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if (tipAmountInput) {
                    tipAmountInput.value = this.dataset.amount || '';
                }
                if (tipCustomAmount) {
                    tipCustomAmount.style.display = 'none';
                }
            });
        });
        
        // Custom amount button
        const customAmountBtn = document.getElementById('tip_custom_btn');
        if (customAmountBtn) {
            customAmountBtn.addEventListener('click', function() {
                tipQuickAmounts.forEach(b => b.classList.remove('active'));
                if (tipCustomAmount) {
                    tipCustomAmount.style.display = 'block';
                    tipCustomAmount.focus();
                }
                if (tipAmountInput) tipAmountInput.value = '';
            });
        }
        
        // Custom amount input
        if (tipCustomAmount) {
            tipCustomAmount.addEventListener('input', function() {
                if (tipAmountInput) {
                    tipAmountInput.value = this.value || '';
                }
            });
        }
        
        // Form submission
        if (tipForm) {
            tipForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const creatorId = parseInt(tipModal.dataset.creatorId || 0);
                const amount = parseFloat(tipAmountInput?.value || 0);
                const message = document.getElementById('tip_message')?.value || '';
                
                if (!creatorId || creatorId <= 0) {
                    alert('<?php echo esc_js(__('Invalid creator.', 'nymia')); ?>');
                    return;
                }
                
                if (amount <= 0) {
                    alert('<?php echo esc_js(__('Please enter a valid tip amount.', 'nymia')); ?>');
                    return;
                }
                
                if (amount < 1) {
                    alert('<?php echo esc_js(__('Minimum tip amount is $1.00.', 'nymia')); ?>');
                    return;
                }
                
                // Disable submit button
                const submitBtn = tipForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '<?php echo esc_js(__('Processing...', 'nymia')); ?>';
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'nymia_send_tip');
                formData.append('nonce', '<?php echo wp_create_nonce('nymia_tip'); ?>');
                formData.append('creator_id', creatorId);
                formData.append('amount', amount);
                formData.append('message', message);
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.checkout_url) {
                        window.location.href = data.data.checkout_url;
                    } else {
                        alert(data.data?.message || '<?php echo esc_js(__('Failed to process tip. Please try again.', 'nymia')); ?>');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        }
    }
});
</script>

<!-- Tip Modal -->
<div id="nymiaTipModal" class="nymia-modal">
    <div class="nymia-modal-content" style="max-width: 500px;">
        <div class="nymia-modal-header">
            <h2><?php esc_html_e('Send a Tip', 'nymia'); ?></h2>
            <button type="button" class="nymia-modal-close" id="closeTipModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <form id="tipForm" class="nymia-tip-form">
            <div class="nymia-form-group">
                <label><?php esc_html_e('Tip Amount', 'nymia'); ?></label>
                <div class="tip-quick-amounts" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
                    <button type="button" class="tip-quick-amount" data-amount="5">$5</button>
                    <button type="button" class="tip-quick-amount" data-amount="10">$10</button>
                    <button type="button" class="tip-quick-amount" data-amount="25">$25</button>
                    <button type="button" class="tip-quick-amount" data-amount="50">$50</button>
                    <button type="button" class="tip-quick-amount" data-amount="100">$100</button>
                    <button type="button" class="tip-quick-amount" id="tip_custom_btn"><?php esc_html_e('Custom', 'nymia'); ?></button>
                </div>
                <input type="number" id="tip_custom_amount" name="custom_amount" placeholder="<?php esc_attr_e('Enter custom amount', 'nymia'); ?>" min="1" step="0.01" style="display: none; width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 1rem; margin-bottom: 15px;">
                <input type="hidden" id="tip_amount" name="amount" required>
            </div>
            
            <div class="nymia-form-group">
                <label for="tip_message"><?php esc_html_e('Message (Optional)', 'nymia'); ?></label>
                <textarea id="tip_message" name="message" rows="3" placeholder="<?php esc_attr_e('Add a message with your tip...', 'nymia'); ?>" style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 0.95rem; resize: vertical;"></textarea>
            </div>
            
            <div class="nymia-modal-footer">
                <button type="button" class="nymia-btn-outline" id="cancelTipBtn"><?php esc_html_e('Cancel', 'nymia'); ?></button>
                <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Send Tip', 'nymia'); ?></button>
            </div>
        </form>
    </div>
</div>

<style>
/* Profile Action Buttons */
.nymia-edit-profile-btn {
    background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
    border: none;
    border-radius: 12px;
    padding: 14px 28px;
    font-size: 15px;
    font-weight: 600;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.nymia-edit-profile-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(191, 76, 26, 0.4);
}

.nymia-edit-profile-btn:active {
    transform: translateY(0);
}

.nymia-follow-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    padding: 14px 28px;
    font-size: 15px;
    font-weight: 600;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.nymia-follow-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
}

.nymia-follow-btn:active {
    transform: translateY(0);
}

.nymia-follow-btn.following {
    background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
    border-color: transparent;
}

.nymia-follow-btn.following:hover {
    background: linear-gradient(135deg, #9F2B1A 0%, #7F1B0A 100%);
    box-shadow: 0 8px 20px rgba(191, 76, 26, 0.4);
}

.nymia-follow-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Username Section with Badge Alignment */
.nymia-username-section {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
    padding-top: 0;
}

@media (min-width: 768px) {
    .nymia-username-section {
        padding-top: 12px;
    }
}

.nymia-profile-username {
    margin: 0;
    display: block;
    line-height: 1.5;
}

/* Verification Badge Styles */
.nymia-verification-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 16px;
    font-size: 11px;
    font-weight: 500;
    background: rgba(230, 116, 68, 0.12);
    border: 1px solid rgba(230, 116, 68, 0.28);
    color: #e67444;
    white-space: nowrap;
    align-self: flex-start;
    margin-top: 2px;
}

.nymia-verification-badge.nymia-verification-pending {
    background: rgba(239, 68, 68, 0.12);
    border-color: rgba(239, 68, 68, 0.28);
    color: #f87171;
}

.nymia-verification-badge svg {
    width: 13px;
    height: 13px;
    flex-shrink: 0;
}

/* Verification Alert Styles */
.nymia-verify-alert {
    position: relative;
    background: linear-gradient(135deg, rgba(191, 76, 26, 0.1) 0%, rgba(25, 25, 30, 0.95) 100%);
    border: 1px solid rgba(191, 76, 26, 0.3);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.nymia-verify-alert-icon {
    color: #BF4C1A;
    flex-shrink: 0;
}

.nymia-verify-alert-content {
    flex: 1;
}

.nymia-verify-alert-content h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #ffffff;
}

.nymia-verify-alert-content p {
    font-size: 14px;
    color: #a0a0a0;
    margin-bottom: 20px;
}

.nymia-verify-input-group {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
}

.nymia-verify-input-group input {
    flex: 1;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 16px;
    color: #ffffff;
    text-align: center;
    letter-spacing: 4px;
    font-family: 'Courier New', monospace;
}

.nymia-verify-input-group input:focus {
    outline: none;
    border-color: #BF4C1A;
    box-shadow: 0 0 0 3px rgba(191, 76, 26, 0.2);
}

.nymia-verify-btn {
    background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.nymia-verify-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(191, 76, 26, 0.4);
}

.nymia-resend-code-btn {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 13px;
    color: #BF4C1A;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.nymia-resend-code-btn:hover {
    background: rgba(191, 76, 26, 0.1);
    border-color: #BF4C1A;
}

.nymia-verify-alert-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: transparent;
    border: none;
    color: #a0a0a0;
    cursor: pointer;
    padding: 4px;
    transition: color 0.3s ease;
}

.nymia-verify-alert-close:hover {
    color: #ffffff;
}

/* Available Now (Green Light) Styles */
@keyframes pulse-green {
    0%, 100% {
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    50% {
        opacity: 0.8;
        box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
    }
}

.nymia-call-now-btn {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border: none;
    border-radius: 12px;
    padding: 14px 24px;
    font-size: 15px;
    font-weight: 600;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.nymia-call-now-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
}

.nymia-call-now-btn:active {
    transform: translateY(0);
}

.nymia-call-now-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Tip Button Styles */
.nymia-tip-btn {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border: none;
    border-radius: 12px;
    padding: 14px 24px;
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
}

.nymia-tip-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
    background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
}

.nymia-tip-btn:active {
    transform: translateY(0);
}

.nymia-tip-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Tip Modal Styles */
.tip-quick-amounts {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 15px;
}

.tip-quick-amount {
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.tip-quick-amount:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.tip-quick-amount.active {
    background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
    border-color: #BF4C1A;
    color: #fff;
}

.nymia-tip-form {
    padding: 0;
}

.nymia-tip-form .nymia-form-group {
    margin-bottom: 20px;
}

.nymia-tip-form label {
    display: block;
    margin-bottom: 8px;
    color: #fff;
    font-weight: 500;
    font-size: 0.95rem;
}
</style>

<?php get_footer();?>

