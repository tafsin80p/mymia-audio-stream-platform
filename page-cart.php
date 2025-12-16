<?php
/**
 * Template Name: Cart
 * 
 * Shopping Cart Page for Ebooks, Audio, and Audio Books
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login?redirect_to=' . urlencode(home_url('/cart'))));
    exit;
}

get_header();
get_sidebar();

// Get cart - ensure function exists
if (!function_exists('nymia_get_cart')) {
    error_log('NYMIA_CART_PAGE_ERROR: nymia_get_cart function does not exist!');
    $cart = array();
} else {
    $cart = nymia_get_cart();
}

if (!function_exists('nymia_get_cart_total')) {
    error_log('NYMIA_CART_PAGE_ERROR: nymia_get_cart_total function does not exist!');
    $cart_total = 0;
} else {
    $cart_total = nymia_get_cart_total();
}

$currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));

// Debug: Log cart contents
$user_id = get_current_user_id();
error_log('NYMIA_CART_PAGE: User ID: ' . $user_id . ', Cart items count: ' . count($cart) . ', Cart total: ' . $cart_total);
if (!empty($cart)) {
    error_log('NYMIA_CART_PAGE: Cart contents: ' . print_r($cart, true));
} else {
    error_log('NYMIA_CART_PAGE: Cart is empty. Checking user meta directly...');
    $direct_cart = get_user_meta($user_id, 'nymia_cart', true);
    error_log('NYMIA_CART_PAGE: Direct user meta cart: ' . print_r($direct_cart, true));
}
?>

<div class="nymia-container">
    <main class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-cart-page" style="max-width: 1000px; margin: 0 auto; padding: 40px 20px;">
            <!-- Page Header -->
            <div style="margin-bottom: 40px;">
                <h1 style="margin: 0 0 10px 0; font-size: 2.5rem; color: #fff; font-weight: 700;">Shopping Cart</h1>
                <p style="margin: 0; color: #bbb; font-size: 1.1rem;">
                    <?php 
                    $item_count = count($cart);
                    if ($item_count === 0) {
                        echo esc_html__('Your cart is empty.', 'nymia');
                    } else {
                        printf(esc_html(_n('%d item in your cart', '%d items in your cart', $item_count, 'nymia')), $item_count);
                    }
                    ?>
                </p>
            </div>

            <?php if (empty($cart)): ?>
                <!-- Empty Cart -->
                <div style="text-align: center; padding: 80px 20px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 80px; height: 80px; color: #666; margin: 0 auto 24px;">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <h2 style="margin: 0 0 12px 0; font-size: 1.5rem; color: #fff;">Your cart is empty</h2>
                    <p style="margin: 0 0 30px 0; color: #bbb; font-size: 1rem;">Start adding items to your cart to continue shopping.</p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="nymia-btn-gradient" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: 12px; text-decoration: none; color: #fff; font-weight: 600;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: 1fr 400px; gap: 30px; align-items: start;">
                    <!-- Cart Items -->
                    <div>
                        <div id="nymia-cart-items" style="display: flex; flex-direction: column; gap: 16px;">
                            <?php foreach ($cart as $index => $item): ?>
                                <div class="nymia-cart-item" data-item-type="<?php echo esc_attr($item['type']); ?>" data-item-id="<?php echo esc_attr($item['id']); ?>" style="background: #1a1a1a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; display: flex; gap: 20px; align-items: center;">
                                    <!-- Item Image -->
                                    <div style="flex-shrink: 0;">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>" style="width: 100px; height: <?php echo $item['type'] === 'ebook' ? '130px' : '100px'; ?>; object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <div style="width: 100px; height: <?php echo $item['type'] === 'ebook' ? '130px' : '100px'; ?>; background: #2a2a2a; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <?php if ($item['type'] === 'ebook'): ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 40px; height: 40px; color: #666;">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                        <polyline points="14 2 14 8 20 8"></polyline>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 40px; height: 40px; color: #666;">
                                                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Item Details -->
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 16px; margin-bottom: 8px;">
                                            <div style="flex: 1;">
                                                <h3 style="margin: 0 0 6px 0; font-size: 1.2rem; color: #fff; font-weight: 600;"><?php echo esc_html($item['title']); ?></h3>
                                                <p style="margin: 0 0 8px 0; color: #bbb; font-size: 0.95rem;">by <?php echo esc_html($item['author']); ?></p>
                                                <span style="display: inline-block; padding: 4px 12px; background: rgba(191, 76, 26, 0.2); border: 1px solid rgba(191, 76, 26, 0.4); border-radius: 20px; font-size: 0.85rem; color: #ff8c66; text-transform: capitalize;">
                                                    <?php echo esc_html($item['type'] === 'audio_book' ? 'Audio Book' : ucfirst($item['type'])); ?>
                                                </span>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 8px;">
                                                    <?php 
                                                    if (function_exists('nymia_format_currency_for_display')) {
                                                        echo esc_html(nymia_format_currency_for_display($item['price'], $currency));
                                                    } else {
                                                        echo esc_html('$' . number_format($item['price'], 2));
                                                    }
                                                    ?>
                                                </div>
                                                <button type="button" class="nymia-remove-cart-item" data-item-type="<?php echo esc_attr($item['type']); ?>" data-item-id="<?php echo esc_attr($item['id']); ?>" style="background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 0.9rem; padding: 4px 8px; opacity: 0.8; transition: opacity 0.2s;">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div style="position: sticky; top: 20px;">
                        <div style="background: #1a1a1a; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px;">
                            <h2 style="margin: 0 0 20px 0; font-size: 1.5rem; color: #fff; font-weight: 600;">Order Summary</h2>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <span style="color: #bbb; font-size: 1rem;">Subtotal</span>
                                <span style="color: #fff; font-size: 1rem; font-weight: 600;" id="cart-subtotal">
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
                                <span style="color: #fff; font-size: 1.5rem; font-weight: 700;" id="cart-total">
                                    <?php 
                                    if (function_exists('nymia_format_currency_for_display')) {
                                        echo esc_html(nymia_format_currency_for_display($cart_total, $currency));
                                    } else {
                                        echo esc_html('$' . number_format($cart_total, 2));
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <a href="<?php echo esc_url(home_url('/checkout?cart_checkout=1')); ?>" class="nymia-btn-gradient" id="cart-checkout-btn" style="display: block; width: 100%; padding: 16px; border-radius: 12px; text-align: center; text-decoration: none; color: #fff; font-weight: 700; font-size: 1.1rem; background: linear-gradient(135deg, #BF4C1A, #9F2B1A); transition: all 0.3s ease;">
                                <?php esc_html_e('Proceed to Checkout', 'nymia'); ?>
                            </a>
                            
                            <a href="<?php echo esc_url(home_url('/')); ?>" style="display: block; text-align: center; margin-top: 16px; color: #bbb; text-decoration: none; font-size: 0.95rem; transition: color 0.2s;">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
(function() {
    // Remove item from cart
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('nymia-remove-cart-item') || e.target.closest('.nymia-remove-cart-item')) {
            e.preventDefault();
            const btn = e.target.classList.contains('nymia-remove-cart-item') ? e.target : e.target.closest('.nymia-remove-cart-item');
            const itemType = btn.getAttribute('data-item-type');
            const itemId = btn.getAttribute('data-item-id');
            
            if (!itemType || !itemId) return;
            
            // Disable button
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.textContent = 'Removing...';
            
            // AJAX request
            const formData = new FormData();
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
                    const cartItem = btn.closest('.nymia-cart-item');
                    if (cartItem) {
                        cartItem.style.transition = 'opacity 0.3s, transform 0.3s';
                        cartItem.style.opacity = '0';
                        cartItem.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            cartItem.remove();
                            
                            // Check if cart is empty
                            const cartItems = document.getElementById('nymia-cart-items');
                            if (!cartItems || cartItems.children.length === 0) {
                                location.reload();
                            } else {
                                // Update totals
                                if (data.data && data.data.cart_total !== undefined) {
                                    const total = parseFloat(data.data.cart_total);
                                    const currency = '<?php echo esc_js($currency); ?>';
                                    const formatted = currency === 'USD' ? '$' + total.toFixed(2) : total.toFixed(2) + ' ' + currency;
                                    const totalEl = document.getElementById('cart-total');
                                    const subtotalEl = document.getElementById('cart-subtotal');
                                    if (totalEl) totalEl.textContent = formatted;
                                    if (subtotalEl) subtotalEl.textContent = formatted;
                                }
                            }
                        }, 300);
                    }
                } else {
                    alert(data.data && data.data.message ? data.data.message : 'Failed to remove item');
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.textContent = 'Remove';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.textContent = 'Remove';
            });
        }
    });
})();
</script>

<?php get_footer(); ?>

