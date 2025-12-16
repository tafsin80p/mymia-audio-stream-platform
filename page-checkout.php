<?php
/**
 * Template Name: Checkout
 * 
 * Checkout Page for Cart Items (Ebooks, Audio, Audio Books)
 * Uses Stripe Checkout for secure payment processing
 */

// IMPORTANT: All redirects must happen BEFORE get_header() to avoid "headers already sent" error

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login?redirect_to=' . urlencode(home_url('/checkout'))));
    exit;
}

// Get cart
$cart = function_exists('nymia_get_cart') ? nymia_get_cart() : array();
$cart_total = function_exists('nymia_get_cart_total') ? nymia_get_cart_total() : 0;
$currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
$current_user_id = get_current_user_id();

// If cart is empty, redirect to cart page
if (empty($cart)) {
    wp_redirect(home_url('/cart'));
    exit;
}

// Validate cart items and check if already purchased
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

// If no valid items, redirect to cart
if (empty($valid_cart)) {
    if (function_exists('nymia_clear_cart')) {
        nymia_clear_cart();
    }
    wp_redirect(home_url('/cart'));
    exit;
}

// Recalculate total from valid cart
$cart_total = 0;
foreach ($valid_cart as $item) {
    $cart_total += (float)($item['price'] ?? 0);
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
        
        <div class="nymia-checkout-page" style="max-width:900px; margin:0 auto; padding:40px 20px;">
            <!-- Back Button -->
            <a href="<?php echo esc_url(home_url('/cart')); ?>" style="display:inline-flex; align-items:center; gap:8px; color:#fff; text-decoration:none; margin-bottom:30px; opacity:0.8; transition:opacity 0.2s;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span><?php esc_html_e('Back to Cart', 'nymia'); ?></span>
            </a>

            <!-- Checkout Header -->
            <div style="text-align:center; margin-bottom:40px;">
                <h1 style="margin:0 0 10px 0; font-size:2rem; color:#fff;">Checkout</h1>
                <p style="margin:0; color:#bbb; font-size:1rem;"><?php printf(esc_html__('Complete your purchase for %d item(s)', 'nymia'), count($valid_cart)); ?></p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 400px; gap: 30px; align-items: start;">
                <!-- Order Items -->
                <div>
                    <h2 style="margin:0 0 20px 0; font-size:1.5rem; color:#fff; font-weight:600;">Order Items</h2>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($valid_cart as $item): ?>
                            <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:20px; display:flex; gap:16px; align-items:center;">
                                <!-- Item Image -->
                                <div style="flex-shrink: 0;">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>" style="width: 80px; height: <?php echo $item['type'] === 'ebook' ? '100px' : '80px'; ?>; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div style="width: 80px; height: <?php echo $item['type'] === 'ebook' ? '100px' : '80px'; ?>; background: #2a2a2a; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <?php if ($item['type'] === 'ebook'): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; color: #666;">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <polyline points="14 2 14 8 20 8"></polyline>
                                                </svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; color: #666;">
                                                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Item Details -->
                                <div style="flex: 1; min-width: 0;">
                                    <h3 style="margin:0 0 6px 0; font-size:1.1rem; color:#fff; font-weight:600;"><?php echo esc_html($item['title']); ?></h3>
                                    <p style="margin:0 0 8px 0; color:#bbb; font-size:0.9rem;">by <?php echo esc_html($item['author']); ?></p>
                                    <span style="display: inline-block; padding: 4px 10px; background: rgba(191, 76, 26, 0.2); border: 1px solid rgba(191, 76, 26, 0.4); border-radius: 16px; font-size: 0.8rem; color: #ff8c66; text-transform: capitalize;">
                                        <?php echo esc_html($item['type'] === 'audio_book' ? 'Audio Book' : ucfirst($item['type'])); ?>
                                    </span>
                                </div>
                                
                                <!-- Item Price -->
                                <div style="text-align: right;">
                                    <div style="font-size: 1.3rem; font-weight: 700; color: #fff;">
                                        <?php 
                                        if (function_exists('nymia_format_currency_for_display')) {
                                            echo esc_html(nymia_format_currency_for_display($item['price'], $currency));
                                        } else {
                                            echo esc_html('$' . number_format($item['price'], 2));
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div style="position: sticky; top: 20px;">
                    <div style="background:#1a1a1a; border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:24px;">
                        <h2 style="margin:0 0 20px 0; font-size:1.5rem; color:#fff; font-weight:600;">Order Summary</h2>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: #bbb; font-size: 1rem;">Subtotal (<?php echo count($valid_cart); ?> items)</span>
                            <span style="color: #fff; font-size: 1rem; font-weight: 600;">
                                <?php 
                                if (function_exists('nymia_format_currency_for_display')) {
                                    echo esc_html(nymia_format_currency_for_display($cart_total, $currency));
                                } else {
                                    echo esc_html('$' . number_format($cart_total, 2));
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: #fff; font-size: 1.2rem; font-weight: 600;">Total</span>
                            <span style="color: #fff; font-size: 1.5rem; font-weight: 700;">
                                <?php 
                                if (function_exists('nymia_format_currency_for_display')) {
                                    echo esc_html(nymia_format_currency_for_display($cart_total, $currency));
                                } else {
                                    echo esc_html('$' . number_format($cart_total, 2));
                                }
                                ?>
                            </span>
                        </div>
                        
                        <!-- Payment Section -->
                        <?php if (empty($stripe_publishable_key)): ?>
                            <div style="background:#2a1a0a; border:1px solid rgba(255,100,50,0.3); border-radius:12px; padding:16px; text-align:center;">
                                <p style="margin:0; color:#ff6; font-size:0.9rem;">
                                    <strong>Payment gateway is not configured.</strong>
                                </p>
                            </div>
                        <?php else: ?>
                            <div style="display:flex; align-items:start; gap:12px; margin-bottom:20px; padding:16px; background:#2a2a2a; border-radius:12px;">
                                <input type="checkbox" name="agree" id="checkout_agree" required style="margin-top:4px; width:20px; height:20px; cursor:pointer; accent-color:#BF4C1A;">
                                <label for="checkout_agree" style="margin:0; color:#bbb; font-size:0.9rem; cursor:pointer; line-height:1.5;">
                                    I agree to the <a href="<?php echo esc_url(home_url('/policies')); ?>" target="_blank" style="color:#BF4C1A; text-decoration:underline;">Terms and Privacy Policy</a>
                                </label>
                            </div>

                            <button type="button" id="nymiaCheckoutBtn" class="nymia-btn-gradient" style="width:100%; padding:16px; border-radius:12px; border:none; background:linear-gradient(135deg,#BF4C1A,#9F2B1A); color:#fff; font-weight:700; font-size:1.1rem; cursor:pointer; transition:all 0.3s ease; display:flex; align-items:center; justify-content:center; gap:10px;">
                                <span id="checkoutBtnText">Pay with Stripe</span>
                                <span id="checkoutBtnLoading" style="display:none;">
                                    <svg style="width:20px; height:20px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M12 6v6l4 2"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>

                            <div style="text-align:center; margin-top:16px;">
                                <p style="margin:0; font-size:0.8rem; color:#888; display:flex; align-items:center; justify-content:center; gap:6px;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px; height:16px;">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    </svg>
                                    Secure payment powered by Stripe
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php if (!empty($stripe_publishable_key)): ?>
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
            
            // Create checkout session for cart
            var formData = new FormData();
            formData.append('action', 'nymia_create_checkout_session');
            formData.append('cart_checkout', '1'); // Flag to indicate cart checkout
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
    
    // Add spin animation
    if (!document.getElementById('nymiaSpinAnimation')) {
        var style = document.createElement('style');
        style.id = 'nymiaSpinAnimation';
        style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
