<?php
/**
 * ========================================
 * NYMIA THEME - SIDEBAR TEMPLATE
 * ========================================
 * Displays the main sidebar navigation with:
 * - Logo and tagline
 * - Main navigation items (Dashboard, Live Audio, Ebooks, Audio)
 * - Bottom actions (Earnings, Settings submenu, Policies, Logout)
 * - Responsive menu toggle
 * 
 * @package Nymia
 * @version 1.0
 */
?>

<!-- ======================================== -->
<!-- MAIN SIDEBAR CONTAINER -->
<!-- ======================================== -->
<aside class="nymia-sidebar">
    <!-- ======================================== -->
    <!-- SIDEBAR LOGO -->
    <!-- ======================================== -->
    <div class="nymia-logo">
        <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nymia-logo.jpg'); ?>" alt="Nymia Logo" onerror="this.onerror=null; this.src='<?php echo esc_url(get_template_directory_uri() . '/assets/images/dashboardLogo-Bt6G_8pS.png'); ?>';" />
        </a>
    </div>

    <!-- ======================================== -->
    <!-- MAIN NAVIGATION -->
    <!-- ======================================== -->
    <nav class="nymia-nav">
        <a href="<?php echo home_url('/'); ?>" class="nymia-nav-item <?php echo is_front_page() ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
            </svg>
            <span>Dashboard</span>
        </a>
        
        <a href="<?php echo home_url('/live-audio'); ?>" class="nymia-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <line x1="12" x2="12" y1="19" y2="22"></line>
            </svg>
            <span>Live Audio Streaming</span>
        </a>
        
        <a href="<?php echo home_url('/ebook'); ?>" class="nymia-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" x2="8" y1="13" y2="13"></line>
                <line x1="16" x2="8" y1="17" y2="17"></line>
                <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
            <span>E-Books</span>
        </a>
        
        <a href="<?php echo home_url('/audio'); ?>" class="nymia-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 14v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3"></path>
                <polyline points="8,2 8,8 16,8 16,2"></polyline>
                <line x1="12" x2="12" y1="22" y2="8"></line>
            </svg>
            <span>Audio</span>
        </a>
    </nav>

    <!-- ======================================== -->
    <!-- BOTTOM NAVIGATION ACTIONS -->
    <!-- ======================================== -->
    <div class="nymia-nav-bottom">
        <a href="<?php echo home_url('/earnings'); ?>" class="nymia-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" x2="12" y1="2" y2="22"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <span>Earnings</span>
        </a>
        
        <div class="nymia-nav-item-wrapper">
            <button class="nymia-nav-item nymia-nav-item-toggle<?php echo (is_page('profile') || is_page('privacy')) ? ' active' : ''; ?>" data-submenu="settings">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Settings</span>
                <svg class="nymia-nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="nymia-submenu<?php echo (is_page('profile') || is_page('privacy')) ? ' active' : ''; ?>" id="submenu-settings">
                <a href="<?php echo home_url('/profile'); ?>" class="nymia-submenu-item<?php echo is_page('profile') ? ' active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Profile</span>
                </a>
                <a href="<?php echo home_url('/privacy'); ?>" class="nymia-submenu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Privacy</span>
                </a>
            </div>
        </div>
        
        <a href="<?php echo home_url('/policies'); ?>" class="nymia-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <span>Policies</span>
        </a>
        
        <a href="<?php echo wp_logout_url(home_url('/')); ?>" class="nymia-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16,17 21,12 16,7"></polyline>
                <line x1="21" x2="9" y1="12" y2="12"></line>
            </svg>
            <span>Logout</span>
        </a>
    </div>
</aside>
<!-- END: Sidebar Template -->
