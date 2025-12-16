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
        
        <!-- Shopping Cart Icon (only for logged-in users) -->
        <?php if (is_user_logged_in()): 
            // Get cart data
            $cart_count = 0;
            $cart_items = array();
            $cart_total = 0;
            if (function_exists('nymia_get_cart')) {
                $cart_items = nymia_get_cart();
                $cart_count = is_array($cart_items) ? count($cart_items) : 0;
            }
            if (function_exists('nymia_get_cart_total')) {
                $cart_total = nymia_get_cart_total();
            }
            $cart_page = get_page_by_path('cart');
            $cart_link = $cart_page ? get_permalink($cart_page) : home_url('/cart/');
            $currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
        ?>
            <div class="nymia-cart-wrapper">
                <button type="button" class="nymia-cart-btn" id="nymiaCartBtn" aria-label="<?php esc_attr_e('Shopping Cart', 'nymia'); ?>" title="<?php esc_attr_e('Shopping Cart', 'nymia'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <?php if ($cart_count > 0): ?>
                        <span class="nymia-cart-count"><?php echo esc_html($cart_count > 99 ? '99+' : $cart_count); ?></span>
                    <?php endif; ?>
                </button>
                
                <!-- Cart Dropdown -->
                <div class="nymia-cart-dropdown" id="nymiaCartDropdown">
                    <div class="nymia-cart-header">
                        <h3><?php esc_html_e('Shopping Cart', 'nymia'); ?></h3>
                        <button type="button" class="nymia-cart-close" id="nymiaCartClose">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="nymia-cart-body" id="nymiaCartBody">
                        <?php if (empty($cart_items)): ?>
                            <div class="nymia-cart-empty">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; color: #666; margin: 0 auto 16px;">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                <p style="margin: 0; color: #bbb; text-align: center;"><?php esc_html_e('Your cart is empty', 'nymia'); ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($cart_items as $item): 
                                $item_title = esc_html($item['title'] ?? 'Unknown Item');
                                $item_author = esc_html($item['author'] ?? 'Unknown Author');
                                $item_price = (float)($item['price'] ?? 0);
                                $item_image = esc_url($item['image'] ?? '');
                                $item_type = esc_attr($item['type'] ?? '');
                                $item_id = esc_attr($item['id'] ?? '');
                            ?>
                                <div class="nymia-cart-item" data-item-type="<?php echo $item_type; ?>" data-item-id="<?php echo $item_id; ?>">
                                    <?php if (!empty($item_image)): ?>
                                        <img src="<?php echo $item_image; ?>" alt="<?php echo $item_title; ?>" class="nymia-cart-item-image">
                                    <?php else: ?>
                                        <div class="nymia-cart-item-image-placeholder">
                                            <?php if ($item_type === 'ebook'): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <polyline points="14 2 14 8 20 8"></polyline>
                                                </svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                                                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="nymia-cart-item-details">
                                        <h4 class="nymia-cart-item-title"><?php echo $item_title; ?></h4>
                                        <p class="nymia-cart-item-author"><?php echo $item_author; ?></p>
                                        <div class="nymia-cart-item-price">
                                            <?php 
                                            if (function_exists('nymia_format_currency_for_display')) {
                                                echo esc_html(nymia_format_currency_for_display($item_price, $currency));
                                            } else {
                                                echo esc_html('$' . number_format($item_price, 2));
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <button type="button" class="nymia-cart-item-remove" data-item-type="<?php echo $item_type; ?>" data-item-id="<?php echo $item_id; ?>" aria-label="<?php esc_attr_e('Remove item', 'nymia'); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($cart_items)): ?>
                        <div class="nymia-cart-footer">
                            <div class="nymia-cart-total">
                                <span><?php esc_html_e('Total:', 'nymia'); ?></span>
                                <span class="nymia-cart-total-amount">
                                    <?php 
                                    if (function_exists('nymia_format_currency_for_display')) {
                                        echo esc_html(nymia_format_currency_for_display($cart_total, $currency));
                                    } else {
                                        echo esc_html('$' . number_format($cart_total, 2));
                                    }
                                    ?>
                                </span>
                            </div>
                            <a href="<?php echo esc_url($cart_link); ?>" class="nymia-cart-checkout-btn">
                                <?php esc_html_e('View Cart', 'nymia'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
            
            <!-- Cart Dropdown JavaScript -->
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var cartBtn = document.getElementById('nymiaCartBtn');
                var cartDropdown = document.getElementById('nymiaCartDropdown');
                var cartClose = document.getElementById('nymiaCartClose');
                var cartWrapper = document.querySelector('.nymia-cart-wrapper');
                
                if (!cartBtn || !cartDropdown || !cartWrapper) return;
                
                var hoverTimeout;
                
                // Show cart on hover
                cartWrapper.addEventListener('mouseenter', function() {
                    clearTimeout(hoverTimeout);
                    cartDropdown.classList.add('active');
                });
                
                // Hide cart when mouse leaves
                cartWrapper.addEventListener('mouseleave', function() {
                    hoverTimeout = setTimeout(function() {
                        cartDropdown.classList.remove('active');
                    }, 200);
                });
                
                // Toggle cart dropdown on click
                cartBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    cartDropdown.classList.toggle('active');
                });
                
                // Close cart dropdown
                if (cartClose) {
                    cartClose.addEventListener('click', function(e) {
                        e.stopPropagation();
                        cartDropdown.classList.remove('active');
                    });
                }
                
                // Close cart when clicking outside
                document.addEventListener('click', function(e) {
                    if (cartDropdown.classList.contains('active') && 
                        !cartBtn.contains(e.target) && 
                        !cartDropdown.contains(e.target)) {
                        cartDropdown.classList.remove('active');
                    }
                });
                
                // Handle remove item from cart
                document.addEventListener('click', function(e) {
                    var removeBtn = e.target.closest('.nymia-cart-item-remove');
                    if (!removeBtn) return;
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var itemType = removeBtn.getAttribute('data-item-type');
                    var itemId = removeBtn.getAttribute('data-item-id');
                    var cartItem = removeBtn.closest('.nymia-cart-item');
                    
                    if (!itemType || !itemId) return;
                    
                    // Disable button
                    removeBtn.disabled = true;
                    removeBtn.style.opacity = '0.5';
                    
                    // AJAX request
                    var formData = new FormData();
                    formData.append('action', 'nymia_remove_from_cart');
                    formData.append('item_type', itemType);
                    formData.append('item_id', itemId);
                    formData.append('nonce', '<?php echo wp_create_nonce('nymia_cart'); ?>');
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove item from DOM
                            if (cartItem) {
                                cartItem.style.transition = 'opacity 0.3s, transform 0.3s';
                                cartItem.style.opacity = '0';
                                cartItem.style.transform = 'translateX(-20px)';
                                setTimeout(function() {
                                    cartItem.remove();
                                    
                                    // Check if cart is empty
                                    var cartBody = document.getElementById('nymiaCartBody');
                                    var cartItems = cartBody.querySelectorAll('.nymia-cart-item');
                                    
                                    if (cartItems.length === 0) {
                                        location.reload();
                                    } else {
                                        // Update cart count badge
                                        if (data.data && data.data.cart_count !== undefined) {
                                            var cartCount = document.querySelector('.nymia-cart-count');
                                            if (data.data.cart_count === 0) {
                                                if (cartCount) cartCount.remove();
                                            } else {
                                                if (!cartCount) {
                                                    var countSpan = document.createElement('span');
                                                    countSpan.className = 'nymia-cart-count';
                                                    cartBtn.appendChild(countSpan);
                                                }
                                                var countEl = document.querySelector('.nymia-cart-count');
                                                if (countEl) {
                                                    countEl.textContent = data.data.cart_count > 99 ? '99+' : data.data.cart_count;
                                                }
                                            }
                                        }
                                        
                                        // Update total
                                        if (data.data && data.data.cart_total !== undefined) {
                                            var totalEl = document.querySelector('.nymia-cart-total-amount');
                                            if (totalEl) {
                                                var total = parseFloat(data.data.cart_total);
                                                var currency = '<?php echo esc_js($currency); ?>';
                                                var formatted = currency === 'USD' ? '$' + total.toFixed(2) : total.toFixed(2) + ' ' + currency;
                                                totalEl.textContent = formatted;
                                            }
                                        }
                                    }
                                }, 300);
                            }
                        } else {
                            alert(data.data && data.data.message ? data.data.message : 'Failed to remove item');
                            removeBtn.disabled = false;
                            removeBtn.style.opacity = '1';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        removeBtn.disabled = false;
                        removeBtn.style.opacity = '1';
                    });
                });
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
