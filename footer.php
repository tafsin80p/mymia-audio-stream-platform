<!-- ======================================== -->
<!-- NYMIA THEME - FOOTER TEMPLATE -->
<!-- ========================================
 * Displays the site footer with:
 * - Logo and description
 * - Footer navigation (Platform, Categories, Support, Legal)
 * - Social media links
 * - Copyright information
 * 
 * @package Nymia
 * @version 1.0
 ========================================== -->

<footer class="nymia-footer">
    <div class="nymia-footer-content">
        <!-- Main Navigation Links -->
        <div class="nymia-footer-navigation">
            <!-- Logo and Description -->
            <div class="nymia-footer-brand">
                <div class="nymia-footer-logo">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nymia-logo.jpg'); ?>" alt="Nymia Logo" onerror="this.onerror=null; this.src='<?php echo esc_url(get_template_directory_uri() . '/assets/images/dashboardLogo-Bt6G_8pS.png'); ?>';" />
                    </a>
                </div>
                <p class="nymia-footer-description"><?php echo esc_html(get_option('nymia_footer_description', 'Design amazing digital experiences that create more happy in the world.')); ?></p>
            </div>
            
            <!-- Navigation Columns -->
            <div class="nymia-footer-nav-columns">
                <?php
                $footer_columns = get_option('nymia_footer_menu_columns', array(
                    array(
                        'title' => 'Platform',
                        'links' => array(
                            array('text' => 'Home', 'url' => home_url('/')),
                            array('text' => 'Audio Library', 'url' => home_url('/audio/')),
                            array('text' => 'Create Content', 'url' => home_url('/create/')),
                            array('text' => 'My Profile', 'url' => home_url('/profile/')),
                            array('text' => 'Earnings', 'url' => home_url('/earnings/')),
                            array('text' => 'Live Audio', 'url' => home_url('/live-audio/')),
                        )
                    ),
                    array(
                        'title' => 'Support',
                        'links' => array(
                            array('text' => 'Help Center', 'url' => '#'),
                            array('text' => 'Contact Us', 'url' => home_url('/contact/')),
                            array('text' => 'FAQ', 'url' => '#'),
                            array('text' => 'Community Guidelines', 'url' => '#'),
                            array('text' => 'Report Issue', 'url' => '#'),
                        )
                    ),
                    array(
                        'title' => 'Legal',
                        'links' => array(
                            array('text' => 'Privacy Policy', 'url' => home_url('/policies/')),
                            array('text' => 'Terms & Conditions', 'url' => home_url('/policies/')),
                            array('text' => 'Cookie Policy', 'url' => '#'),
                            array('text' => 'DMCA Policy', 'url' => '#'),
                            array('text' => 'Refund Policy', 'url' => '#'),
                        )
                    )
                ));
                
                foreach ($footer_columns as $column):
                    if (empty($column['title']) || empty($column['links'])) {
                        continue;
                    }
                ?>
                <div class="nymia-footer-nav-column">
                    <h3 class="nymia-footer-column-title"><?php echo esc_html($column['title']); ?></h3>
                    <ul class="nymia-footer-links">
                        <?php foreach ($column['links'] as $link): ?>
                            <?php if (!empty($link['text']) && !empty($link['url'])): ?>
                            <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['text']); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Copyright and Social Icons -->
        <div class="nymia-footer-bottom">
            <p class="nymia-footer-copyright">&copy; <?php echo date('Y'); ?> Nymia. All rights reserved.</p>
            <div class="nymia-footer-social-icons">
                <a href="#" class="nymia-social-icon" aria-label="Twitter">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                    </svg>
                </a>
                <a href="#" class="nymia-social-icon" aria-label="LinkedIn">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/>
                        <circle cx="4" cy="4" r="2"/>
                    </svg>
                </a>
                <a href="#" class="nymia-social-icon" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                    </svg>
                </a>
                <a href="#" class="nymia-social-icon" aria-label="GitHub">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/>
                    </svg>
                </a>
                <a href="#" class="nymia-social-icon" aria-label="AngelList">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    </svg>
                </a>
                <a href="#" class="nymia-social-icon" aria-label="Dribbble">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"/>
                        <path d="M12 6c-3.314 0-6 2.686-6 6s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6zm0 10c-2.209 0-4-1.791-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<?php 
// Include followers popup modal if on a page that uses it
if (is_page('profile') || is_page('dashboard') || is_page('audio')) {
    require_once(get_template_directory() . '/followers/templates/followers-popup.php');
}

// Include chat modal (only for logged-in users)
if (is_user_logged_in()) {
get_template_part('template-parts/chat-modal');
}

// Include become creator modal
get_template_part('template-parts/become-creator-modal');

// Include secret room modal
get_template_part('template-parts/secret-room-modal');

// Include settings modal (only for logged-in users)
if (is_user_logged_in()) {
    get_template_part('template-parts/settings-modal');
    get_template_part('template-parts/profile-modal');
}

// Include login modal for non-logged-in users
if (!is_user_logged_in()) {
    require_once(get_template_directory() . '/template-parts/login-modal.php');
    require_once(get_template_directory() . '/auth/auth-styles.php');
    require_once(get_template_directory() . '/auth/auth-scripts.php');
}
?>

<?php wp_footer(); ?>
</body>
</html>
