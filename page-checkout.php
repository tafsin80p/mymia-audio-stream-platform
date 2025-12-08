<?php
/**
 * Template Name: Checkout
 * 
 * Checkout Page for Audio and Ebook Purchases
 * Uses Stripe Checkout for secure payment processing
 */

// IMPORTANT: All redirects must happen BEFORE get_header() to avoid "headers already sent" error

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login?redirect_to=' . urlencode(home_url($_SERVER['REQUEST_URI']))));
    exit;
}

// Get item type and ID from URL
$item_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : ''; // 'audio', 'ebook', or 'tip'
$item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : ''; // Item ID
$creator_id = isset($_GET['creator_id']) ? intval($_GET['creator_id']) : 0; // Creator ID for tips

// Validate item type
if (!in_array($item_type, array('audio', 'ebook', 'tip'))) {
    wp_redirect(home_url('/'));
    exit;
}

$item = null;
$item_title = '';
$item_author = '';
$item_image = '';
$item_price = 0;
$currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
$current_user_id = get_current_user_id();
$has_access = false;
$redirect_url = '';

// Fetch item data based on type and check access (BEFORE get_header)
if ($item_type === 'ebook') {
    $all_ebooks = function_exists('nymia_get_all_ebooks') ? nymia_get_all_ebooks() : array();
    
    foreach ($all_ebooks as $ebook_item) {
        if (!empty($ebook_item['id']) && (string)$ebook_item['id'] === (string)$item_id) {
            $item = $ebook_item;
            break;
        }
    }
    
    if (!$item) {
        wp_redirect(home_url('/ebook'));
        exit;
    }
    
    // Check if user already has access
    if (function_exists('nymia_user_has_ebook_access')) {
        if (nymia_user_has_ebook_access($current_user_id, $item_id)) {
            wp_redirect(home_url('/single-ebook?ebook=' . urlencode($item_id)));
            exit;
        }
    }
    
    // Check if ebook is free
    $is_paid = (!empty($item['paid_access']) && $item['paid_access'] === 'yes' && (float)($item['price'] ?? 0) > 0);
    if (!$is_paid) {
        wp_redirect(home_url('/single-ebook?ebook=' . urlencode($item_id)));
        exit;
    }
    
    $item_title = $item['title'] ?? 'Ebook';
    $item_author = $item['author'] ?? 'Unknown';
    $item_image = $item['thumbnail'] ?? $item['image'] ?? '';
    $item_price = (float)($item['price'] ?? 0);
    $redirect_url = home_url('/single-ebook?ebook=' . urlencode($item_id));
    
} elseif ($item_type === 'audio') {
    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as $audio_item) {
            if (!empty($audio_item['id']) && (string)$audio_item['id'] === (string)$item_id) {
                $item = $audio_item;
                break;
            }
        }
    }
    
    if (!$item) {
        wp_redirect(home_url('/audio'));
        exit;
    }
    
    // Check if user already has access
    if (function_exists('nymia_user_has_audio_access')) {
        if (nymia_user_has_audio_access($current_user_id, $item_id)) {
            $creator_id = isset($item['user_id']) ? intval($item['user_id']) : 0;
            wp_redirect(home_url('/single-audio?track_id=' . urlencode($item_id) . '&user_id=' . $creator_id));
            exit;
        }
    }
    
    // Check if audio is free
    $is_paid = (!empty($item['paid_access']) && $item['paid_access'] === 'yes' && (float)($item['price'] ?? 0) > 0);
    if (!$is_paid) {
        $creator_id = isset($item['user_id']) ? intval($item['user_id']) : 0;
        wp_redirect(home_url('/single-audio?track_id=' . urlencode($item_id) . '&user_id=' . $creator_id));
        exit;
    }
    
    $item_title = $item['title'] ?? 'Audio Track';
    $item_author = $item['author'] ?? 'Unknown';
    $item_image = $item['cover_image'] ?? '';
    $item_price = (float)($item['price'] ?? 0);
    $creator_id = isset($item['user_id']) ? intval($item['user_id']) : 0;
    $redirect_url = home_url('/single-audio?track_id=' . urlencode($item_id) . '&user_id=' . $creator_id);
}

// Validate price
if ($item_price <= 0) {
    wp_redirect($redirect_url);
    exit;
}

// Now we can safely call get_header() after all redirects are handled
get_header(); 
get_sidebar();

// Get Stripe publishable key
$stripe_publishable_key = get_option('nymia_stripe_publishable_key', '');
$stripe_mode = get_option('nymia_stripe_mode', 'test');
?>

<div class="nymia-container">
    <main class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-checkout-page" style="max-width:800px; margin:0 auto; padding:40px 20px;">
            <!-- Back Button -->
            <a href="<?php echo esc_url($redirect_url); ?>" style="display:inline-flex; align-items:center; gap:8px; color:#fff; text-decoration:none; margin-bottom:30px; opacity:0.8; transition:opacity 0.2s;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span><?php echo $item_type === 'audio' ? esc_html__('Back to Audio', 'nymia') : esc_html__('Back to Ebook', 'nymia'); ?></span>
            </a>

            <!-- Checkout Header -->
            <div style="text-align:center; margin-bottom:40px;">
                <h1 style="margin:0 0 10px 0; font-size:2rem; color:#fff;">Checkout</h1>
                <p style="margin:0; color:#bbb; font-size:1rem;"><?php echo esc_html__('Complete your purchase to unlock this content', 'nymia'); ?></p>
            </div>

            <!-- Item Summary -->
            <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:24px; margin-bottom:30px;">
                <div style="display:flex; gap:20px; align-items:center;">
                    <?php if (!empty($item_image)): ?>
                        <img src="<?php echo esc_url($item_image); ?>" alt="<?php echo esc_attr($item_title); ?>" style="width:120px; height:<?php echo $item_type === 'audio' ? '120px' : '160px'; ?>; object-fit:cover; border-radius:8px;">
                    <?php else: ?>
                        <div style="width:120px; height:<?php echo $item_type === 'audio' ? '120px' : '160px'; ?>; background:#2a2a2a; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <?php if ($item_type === 'audio'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:48px; height:48px; color:#666;">
                                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
                                </svg>
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:48px; height:48px; color:#666;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div style="flex:1;">
                        <h2 style="margin:0 0 8px 0; font-size:1.5rem; color:#fff;"><?php echo esc_html($item_title); ?></h2>
                        <p style="margin:0 0 12px 0; color:#bbb; font-size:1rem;">by <?php echo esc_html($item_author); ?></p>
                        <div style="font-size:2rem; font-weight:700; color:#fff;">
                            <?php 
                            if (function_exists('nymia_format_currency_for_display')) {
                                echo esc_html(nymia_format_currency_for_display($item_price, $currency));
                            } else {
                                echo esc_html('$' . number_format($item_price, 2));
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <?php if (empty($stripe_publishable_key)): ?>
                <div style="background:#2a1a0a; border:1px solid rgba(255,100,50,0.3); border-radius:12px; padding:24px; margin-bottom:30px; text-align:center;">
                    <p style="margin:0; color:#ff6; font-size:1rem;">
                        <strong>Payment gateway is not configured.</strong><br>
                        <span style="font-size:0.9rem; color:#aaa; margin-top:8px; display:block;">Please configure Stripe settings in the admin panel.</span>
                    </p>
                </div>
            <?php else: ?>
                <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:32px;">
                    <h3 style="margin:0 0 24px 0; font-size:1.5rem; color:#fff; text-align:center;">Complete Your Purchase</h3>
                    
                    <div style="display:flex; align-items:start; gap:12px; margin-bottom:32px; padding:20px; background:#2a2a2a; border-radius:12px;">
                        <input type="checkbox" name="agree" id="checkout_agree" required style="margin-top:4px; width:20px; height:20px; cursor:pointer;">
                        <label for="checkout_agree" style="margin:0; color:#bbb; font-size:0.95rem; cursor:pointer; line-height:1.5;">
                            I agree to the <a href="<?php echo esc_url(home_url('/policies')); ?>" target="_blank" style="color:#BF4C1A; text-decoration:underline;">Terms and Privacy Policy</a>
                        </label>
                    </div>

                    <button type="button" id="nymiaCheckoutBtn" class="nymia-btn-gradient" style="width:100%; padding:18px; border-radius:12px; border:none; background:linear-gradient(135deg,#BF4C1A,#9F2B1A); color:#fff; font-weight:700; font-size:1.1rem; cursor:pointer; transition:all 0.3s ease; display:flex; align-items:center; justify-content:center; gap:10px;">
                        <span id="checkoutBtnText">Pay with Stripe</span>
                        <span id="checkoutBtnLoading" style="display:none;">
                            <svg style="width:20px; height:20px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 6v6l4 2"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>

                    <div style="text-align:center; margin-top:20px;">
                        <p style="margin:0; font-size:0.875rem; color:#888; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px; height:18px;">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            Secure payment powered by Stripe
                        </p>
                    </div>
                </div>

                <!-- Load Stripe.js -->
                <script src="https://js.stripe.com/v3/"></script>
                <script>
                (function(){
                    var stripe = Stripe('<?php echo esc_js($stripe_publishable_key); ?>');
                    var checkoutBtn = document.getElementById('nymiaCheckoutBtn');
                    var checkoutBtnText = document.getElementById('checkoutBtnText');
                    var checkoutBtnLoading = document.getElementById('checkoutBtnLoading');
                    var agreeCheckbox = document.getElementById('checkout_agree');
                    
                    if (checkoutBtn) {
                        checkoutBtn.addEventListener('click', function(e){
                            e.preventDefault();
                            
                            if (!agreeCheckbox || !agreeCheckbox.checked) {
                                alert('<?php echo esc_js(__('Please agree to the Terms and Privacy Policy to continue.', 'nymia')); ?>');
                                return;
                            }
                            
                            checkoutBtn.disabled = true;
                            checkoutBtnText.style.display = 'none';
                            checkoutBtnLoading.style.display = 'inline';
                            
                            // Create checkout session
                            var formData = new FormData();
                            formData.append('action', 'nymia_create_checkout_session');
                            formData.append('item_type', '<?php echo esc_js($item_type); ?>');
                            formData.append('item_id', '<?php echo esc_js($item_id); ?>');
                            formData.append('nonce', (typeof nymiaAjax !== 'undefined' && nymiaAjax.checkoutNonce) ? nymiaAjax.checkoutNonce : '<?php echo wp_create_nonce('nymia_checkout'); ?>');
                            
                            var ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';
                            
                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: formData
                            })
                            .then(function(response) {
                                return response.json();
                            })
                            .then(function(data) {
                                if (data && data.success && data.data && data.data.sessionId) {
                                    // Redirect to Stripe Checkout
                                    return stripe.redirectToCheckout({
                                        sessionId: data.data.sessionId
                                    });
                                } else {
                                    throw new Error(data.data && data.data.message ? data.data.message : 'Failed to create checkout session');
                                }
                            })
                            .then(function(result) {
                                if (result.error) {
                                    throw new Error(result.error.message);
                                }
                            })
                            .catch(function(error) {
                                checkoutBtn.disabled = false;
                                checkoutBtnText.style.display = 'inline';
                                checkoutBtnLoading.style.display = 'none';
                                
                                console.error('Checkout error:', error);
                                alert(error.message || '<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
                            });
                        });
                    }
                })();
                </script>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>

