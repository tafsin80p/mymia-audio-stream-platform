<?php
/**
 * ========================================
 * NYMIA THEME - HEADER TEMPLATE
 * ========================================
 * Displays the main header with:
 * - Page title
 * - Search bar
 * - User actions (Create, Notifications, Profile)
 * 
 * @package Nymia
 * @version 1.0
 */

// ========================================
// GET CURRENT USER DATA
// ========================================
$current_user = wp_get_current_user();
$user_avatar = get_template_directory_uri() . '/assets/images/profile.png';
$user_name = 'Guest';

// CHECK: If user is logged in, get their avatar and name
if (is_user_logged_in() && $current_user->ID) {
    // GET: User avatar from Gravatar or custom meta
    $avatar_url = get_avatar_url($current_user->ID, array('size' => 40));
    
    // CHECK: If user has uploaded a custom avatar
    $custom_avatar = get_user_meta($current_user->ID, 'custom_avatar', true);
    if ($custom_avatar) {
        $user_avatar = esc_url($custom_avatar);
    } else {
        $user_avatar = $avatar_url;
    }
    
    // GET: User's display name
    $user_name = esc_html($current_user->display_name);
}
?>

<?php
$default_page_title = (is_front_page() || is_page('dashboard')) ? __('Home', 'nymia') : get_the_title();
$page_title = apply_filters('nymia_header_page_title', $default_page_title);
$show_page_title = apply_filters('nymia_show_header_page_title', true, $page_title);
$is_creator_role = current_user_can('edit_posts') || current_user_can('manage_options');
?>

<header class="nymia-header">
    <!-- Page Title (role-based): Subscribers see logo, authors/admins see Home text -->
    <?php if ($show_page_title): ?>
        <?php if ($is_creator_role): ?>
            <h2 class="nymia-page-title"><?php echo esc_html($page_title); ?></h2>
        <?php else: ?>
            <h2 class="nymia-page-title">
                <a href="<?php echo esc_url(home_url('/')); ?>" style="display: inline-flex; align-items: center; line-height: 1; margin-top: 20px;">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nymia-logo.jpg'); ?>" alt="Nymia" style="height: 65px; width: auto; display: block;" onerror="this.onerror=null; this.src='<?php echo esc_url(get_template_directory_uri() . '/assets/images/dashboardLogo-Bt6G_8pS.png'); ?>';" />
                </a>
            </h2>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Search with Live Results -->
    <div class="nymia-header-search-wrapper">
        <form role="search" method="get" class="nymia-header-search" action="<?php echo esc_url(home_url('/')); ?>">
            <label for="nymia-header-search" class="screen-reader-text"><?php esc_html_e('Search', 'nymia'); ?></label>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="search" id="nymia-header-search" name="s" placeholder="<?php esc_attr_e('Search or @username', 'nymia'); ?>" value="<?php echo get_search_query(); ?>" autocomplete="off" />
        </form>
        <!-- Live Search Results Dropdown -->
        <div id="nymia-live-search-results" class="nymia-live-search-dropdown" style="display: none;">
            <div class="nymia-live-search-loading" style="display: none; padding: 20px; text-align: center; color: rgba(255,255,255,0.6);">
                <?php esc_html_e('Searching...', 'nymia'); ?>
            </div>
            <div class="nymia-live-search-content"></div>
        </div>
    </div>
    
    <!-- Right Actions -->
    <div class="nymia-header-actions">
        <!-- Language Selector (GTranslate) -->
        <div class="nymia-language-switcher">
        <?php echo do_shortcode('[gtranslate]'); ?>
        </div>
        
        <!-- Creator CTA: Show Create for approved creators, Become a Creator for users who need to apply, nothing for regular subscribers -->
        <?php if (is_user_logged_in()): 
            $current_user_id = get_current_user_id();
            $user_roles = (array) $current_user->roles;
            $is_subscriber_only = in_array('subscriber', $user_roles) && count($user_roles) === 1; // Only subscriber role
            $account_type = get_user_meta($current_user_id, 'account_type', true);
            $kyc_status = get_user_meta($current_user_id, 'nymia_creator_kyc_status', true);
            $is_kyc_approved = ($kyc_status === 'approved');
            
            // Show Create button only for users with approved KYC status
            if ($is_kyc_approved): ?>
            <a href="<?php echo esc_url(home_url('/create')); ?>" class="nymia-btn-gradient" aria-label="<?php esc_attr_e('Create new content', 'nymia'); ?>">
                <?php esc_html_e('Create', 'nymia'); ?>
            </a>
            <?php elseif ($account_type === 'creator'): 
                // Show Become a Creator button for users who signed up as creator but haven't been approved yet
                ?>
                <button type="button" class="nymia-btn-gradient" id="nymia-become-creator-btn" aria-label="<?php esc_attr_e('Become a creator', 'nymia'); ?>">
                    <?php esc_html_e('Become a Creator', 'nymia'); ?>
                </button>
            <?php elseif (!$is_subscriber_only): 
                // Show Become a Creator button for non-subscribers (authors, admins, etc.) who haven't been approved
                ?>
            <button type="button" class="nymia-btn-gradient" id="nymia-become-creator-btn" aria-label="<?php esc_attr_e('Become a creator', 'nymia'); ?>">
                <?php esc_html_e('Become a Creator', 'nymia'); ?>
            </button>
            <?php endif; 
            // Regular subscribers (account_type = 'user' or empty) don't see any button
            ?>
        <?php endif; ?>
        
        <!-- Notifications (only for logged-in users) -->
        <?php if (is_user_logged_in()): ?>
        <?php get_template_part('notifications/templates/notification-panel'); ?>
        <?php endif; ?>
        
        <!-- Profile / Login -->
        <?php if (is_user_logged_in()): 
            // Check if user is a creator (has approved KYC or creator capabilities)
            $current_user_id = get_current_user_id();
            $kyc_status = get_user_meta($current_user_id, 'nymia_creator_kyc_status', true);
            $is_creator = ($kyc_status === 'approved') || current_user_can('edit_posts') || current_user_can('manage_options');
            $earnings_page = get_page_by_path('earnings');
            $earnings_link = $earnings_page ? get_permalink($earnings_page) : home_url('/earnings/');
        ?>
            <div class="nymia-profile-menu" style="position: relative;">
                <button type="button" class="nymia-profile-btn" id="nymiaProfileMenuBtn" aria-label="<?php esc_attr_e('Open profile menu', 'nymia'); ?>" title="<?php echo esc_attr($user_name); ?>" style="display: inline-flex; align-items: center; justify-content: center;">
                    <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($user_name); ?>" class="nymia-profile-avatar" onerror="this.onerror=null; this.src='<?php echo get_template_directory_uri(); ?>/assets/images/profile.png';" />
                </button>
                <div id="nymiaProfileMenu" class="nymia-profile-dropdown" style="display: none;">
                    <a href="#" class="nymia-profile-menu-item" id="openProfileModal" onclick="event.preventDefault();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span><?php esc_html_e('Profile', 'nymia'); ?></span>
                    </a>
                    <a href="#" class="nymia-profile-menu-item" id="openProfileModalFromEdit" onclick="event.preventDefault();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span><?php esc_html_e('Edit Profile', 'nymia'); ?></span>
                    </a>
                    <?php if ($is_creator): ?>
                    <a href="<?php echo esc_url($earnings_link); ?>" class="nymia-profile-menu-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="22" height="18" rx="2" ry="2"></rect>
                            <line x1="1" y1="9" x2="23" y2="9"></line>
                            <path d="M7 15h.01M11 15h2"></path>
                        </svg>
                        <span><?php esc_html_e('Wallet', 'nymia'); ?></span>
                    </a>
                    <?php endif; ?>
                    <a href="#" class="nymia-profile-menu-item" id="openSettingsModal" onclick="event.preventDefault();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6m9-9h-6m-6 0H3"></path>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span><?php esc_html_e('Settings', 'nymia'); ?></span>
                    </a>
                    <a href="#" class="nymia-profile-menu-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span><?php esc_html_e('Help Center', 'nymia'); ?></span>
                    </a>
                    <div class="nymia-profile-menu-divider"></div>
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="nymia-profile-menu-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span><?php esc_html_e('Logout', 'nymia'); ?></span>
                    </a>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('nymiaProfileMenuBtn');
                var menu = document.getElementById('nymiaProfileMenu');
                if (!btn || !menu) return;
                
                // Toggle menu
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
                });
                
                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (menu.style.display === 'block' && !btn.contains(e.target) && !menu.contains(e.target)) {
                        menu.style.display = 'none';
                    }
                });
                
                // Open profile modal from Edit Profile menu item
                var openProfileFromEdit = document.getElementById('openProfileModalFromEdit');
                if (openProfileFromEdit) {
                    openProfileFromEdit.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        // Close profile menu
                        menu.style.display = 'none';
                        // Open profile modal
                        var profileModal = document.getElementById('nymiaProfileModal');
                        if (profileModal) {
                            profileModal.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        }
                    });
                }
            });
            </script>
        <?php else: ?>
            <!-- Non-logged-in users see login and creator signup buttons -->
            <div style="display: flex; gap: 12px; align-items: center;">
                <a href="#" onclick="event.preventDefault(); if(typeof openLoginModal === 'function') { openLoginModal('signup-creator'); } else { window.location.href='<?php echo esc_url(home_url('/?signup=creator')); ?>'; }" class="nymia-btn-gradient" style="text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);">
                    <?php esc_html_e('Sign Up as Creator', 'nymia'); ?>
                </a>
                <a href="#" onclick="event.preventDefault(); if(typeof openLoginModal === 'function') { openLoginModal('login'); } else { window.location.href='<?php echo esc_url(home_url('/login/')); ?>'; }" class="nymia-btn-outline" style="text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff;">
                <?php esc_html_e('Sign In', 'nymia'); ?>
            </a>
            </div>
        <?php endif; ?>
    </div>
</header>
