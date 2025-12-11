<?php
/**
 * Template Name: Payment Methods
 * 
 * Payment Methods Management Page
 * Allows users to view, add, and delete saved payment methods
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

get_header();

$current_user_id = get_current_user_id();
$checkout_nonce = wp_create_nonce('nymia_checkout');
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-content-wrapper">
            <div class="nymia-content">
                <div class="nymia-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h2><?php esc_html_e('Payment Methods', 'nymia'); ?></h2>
                        <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="nymia-btn-secondary" style="text-decoration: none;">
                            <?php esc_html_e('← Back to Dashboard', 'nymia'); ?>
                        </a>
                    </div>
                    
                    <p style="color: rgba(255,255,255,0.7); margin-bottom: 24px;">
                        <?php esc_html_e('Manage your saved payment methods for faster checkout.', 'nymia'); ?>
                    </p>
                    
                    <!-- Payment Methods List -->
                    <div id="nymia-payment-methods-list" style="margin-bottom: 24px;">
                        <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.5);">
                            <?php esc_html_e('Loading payment methods...', 'nymia'); ?>
                        </div>
                    </div>
                    
                    <!-- Add New Payment Method -->
                    <div class="nymia-card" style="padding: 24px; margin-top: 24px;">
                        <h3 style="margin-bottom: 16px;"><?php esc_html_e('Add Payment Method', 'nymia'); ?></h3>
                        <p style="color: rgba(255,255,255,0.7); margin-bottom: 16px;">
                            <?php esc_html_e('Add a new payment method by making a purchase. Your payment method will be saved for future use.', 'nymia'); ?>
                        </p>
                        <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="nymia-btn-gradient" style="text-decoration: none; display: inline-block;">
                            <?php esc_html_e('Browse Content', 'nymia'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('nymia-payment-methods-list');
    const nonce = '<?php echo esc_js($checkout_nonce); ?>';
    const ajaxUrl = (window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php';
    
    function formatCardBrand(brand) {
        const brands = {
            'visa': 'Visa',
            'mastercard': 'Mastercard',
            'amex': 'American Express',
            'discover': 'Discover',
            'jcb': 'JCB',
            'diners': 'Diners Club',
            'unionpay': 'UnionPay',
        };
        return brands[brand.toLowerCase()] || brand.charAt(0).toUpperCase() + brand.slice(1);
    }
    
    function formatExpiry(month, year) {
        const m = String(month).padStart(2, '0');
        const y = String(year).slice(-2);
        return m + '/' + y;
    }
    
    function renderPaymentMethods(methods) {
        if (!methods || methods.length === 0) {
            listContainer.innerHTML = `
                <div class="nymia-card" style="padding: 40px; text-align: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; margin: 0 auto 16px; color: rgba(255,255,255,0.3);">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <p style="color: rgba(255,255,255,0.7); margin-bottom: 16px;">
                        <?php echo esc_js(__('No saved payment methods.', 'nymia')); ?>
                    </p>
                    <p style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">
                        <?php echo esc_js(__('Payment methods will be saved automatically when you make a purchase.', 'nymia')); ?>
                    </p>
                </div>
            `;
            return;
        }
        
        let html = '<div style="display: grid; gap: 16px;">';
        methods.forEach(function(pm) {
            const brand = formatCardBrand(pm.card.brand);
            const last4 = pm.card.last4;
            const expiry = formatExpiry(pm.card.exp_month, pm.card.exp_year);
            const isExpired = new Date(pm.card.exp_year, pm.card.exp_month - 1) < new Date();
            
            html += `
                <div class="nymia-card" style="padding: 20px; display: flex; justify-content: space-between; align-items: center; ${isExpired ? 'opacity: 0.6;' : ''}">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 48px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                            ${brand.charAt(0)}
                        </div>
                        <div>
                            <div style="font-weight: 500; margin-bottom: 4px;">
                                ${brand} •••• ${last4}
                            </div>
                            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">
                                ${isExpired ? '<span style="color: #ef4444;">Expired</span>' : 'Expires ' + expiry}
                            </div>
                        </div>
                    </div>
                    <button 
                        class="nymia-btn-secondary" 
                        onclick="deletePaymentMethod('${pm.id}')"
                        style="padding: 8px 16px; font-size: 0.9rem;"
                    >
                        <?php echo esc_js(__('Delete', 'nymia')); ?>
                    </button>
                </div>
            `;
        });
        html += '</div>';
        listContainer.innerHTML = html;
    }
    
    function loadPaymentMethods() {
        const formData = new FormData();
        formData.append('action', 'nymia_get_payment_methods');
        formData.append('nonce', nonce);
        
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                renderPaymentMethods(data.data.payment_methods || []);
            } else {
                listContainer.innerHTML = `
                    <div class="nymia-card" style="padding: 24px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                        <p style="color: #ef4444;">
                            ${data?.data?.message || '<?php echo esc_js(__('Failed to load payment methods.', 'nymia')); ?>'}
                        </p>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error loading payment methods:', err);
            listContainer.innerHTML = `
                <div class="nymia-card" style="padding: 24px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                    <p style="color: #ef4444;">
                        <?php echo esc_js(__('Error loading payment methods. Please refresh the page.', 'nymia')); ?>
                    </p>
                </div>
            `;
        });
    }
    
    window.deletePaymentMethod = function(paymentMethodId) {
        if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this payment method?', 'nymia')); ?>')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'nymia_delete_payment_method');
        formData.append('nonce', nonce);
        formData.append('payment_method_id', paymentMethodId);
        
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                loadPaymentMethods(); // Reload list
            } else {
                alert(data?.data?.message || '<?php echo esc_js(__('Failed to delete payment method.', 'nymia')); ?>');
            }
        })
        .catch(err => {
            console.error('Error deleting payment method:', err);
            alert('<?php echo esc_js(__('Error deleting payment method. Please try again.', 'nymia')); ?>');
        });
    };
    
    // Load payment methods on page load
    loadPaymentMethods();
});
</script>

<?php get_footer(); ?>

