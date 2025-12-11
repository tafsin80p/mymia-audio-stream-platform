<?php
/**
 * ========================================
 * NYMIA THEME - INDEX TEMPLATE (Main)
 * ========================================
 * Main template file that handles:
 * - User authentication check
 * - Redirects to login if not logged in
 * - Displays dashboard or page content based on route
 * - Includes sidebar navigation
 * - Handles @username search to redirect to user profile
 * 
 * @package Nymia
 * @version 1.0
 */

// ========================================
// CHECK: User authentication
// ========================================
// Allow non-logged-in users to see public home page
// They will see subscriber/public content instead of being forced to login

// ========================================
// HANDLE: @username Search
// ========================================
// Check if search query starts with "@" (user search)
$search_query = isset($_GET['s']) ? trim($_GET['s']) : '';
if (!empty($search_query) && strpos($search_query, '@') === 0) {
    // Extract username (remove "@" prefix)
    $username = substr($search_query, 1);
    $username = sanitize_user($username);
    
    if (!empty($username)) {
        // Try to find user by username
        $user = get_user_by('login', $username);
        
        if ($user) {
            // User found - redirect to their profile
            $profile_url = home_url('/profile/?username=' . urlencode($username));
            wp_redirect($profile_url);
            exit;
        } else {
            // User not found - show search results page with message
            // We'll pass a flag to show "User not found" message
            $_GET['user_not_found'] = $username;
        }
    }
}

// ========================================
// DISPLAY CONTENT (Logged in or Public)
// ========================================
get_header(); ?>

<!-- ======================================== -->
<!-- MAIN CONTAINER -->
<!-- ======================================== -->
<div class="nymia-container">
    <?php 
    // Only show sidebar for logged-in users with appropriate capabilities
    if (is_user_logged_in() && (current_user_can('edit_posts') || current_user_can('manage_options'))) {
        get_sidebar(); 
    }
    ?>
    
    <div class="nymia-main<?php echo (!is_user_logged_in() || (!current_user_can('edit_posts') && !current_user_can('manage_options'))) ? ' nymia-main-fullwidth' : ''; ?>">
        <?php get_template_part('template-parts/header'); ?>
        
        <div class="nymia-content-wrapper">
            <div class="nymia-content">
                <?php
                // Check if user is logged in
                if (is_user_logged_in()) {
                    // Logged-in users see dashboard
                if (is_page('dashboard') || is_front_page()) {
                    get_template_part('template-parts/dashboard');
                } else {
                    // Regular page content
                    if (have_posts()) :
                        while (have_posts()) : the_post();
                            get_template_part('template-parts/content', get_post_type());
                        endwhile;
                    else :
                        get_template_part('template-parts/content', 'none');
                    endif;
                    }
                } else {
                    // Non-logged-in users see public home page
                    if (is_page('dashboard') || is_front_page()) {
                        get_template_part('template-parts/dashboard-public');
                    } else {
                        // For other pages, show public version or redirect
                        if (have_posts()) :
                            while (have_posts()) : the_post();
                                get_template_part('template-parts/content', get_post_type());
                            endwhile;
                        else :
                            get_template_part('template-parts/content', 'none');
                        endif;
                    }
                }
                ?>
            </div>
            
            <?php get_template_part('template-parts/sidebar-right'); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
