<?php
/**
 * ========================================
 * NYMIA THEME - BACK BUTTON COMPONENT
 * ========================================
 * Reusable back button that uses browser history
 * 
 * @package Nymia
 * @version 1.0
 */

$back_url = isset($back_url) ? $back_url : null; // Optional fallback URL
$back_text = isset($back_text) ? $back_text : __('Back', 'nymia');
?>

<div class="nymia-back-button-wrapper">
    <button type="button" class="nymia-back-button" <?php echo $back_url ? 'data-fallback-url="' . esc_url($back_url) . '"' : ''; ?>>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span><?php echo esc_html($back_text); ?></span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const backButtons = document.querySelectorAll('.nymia-back-button');
    backButtons.forEach(button => {
        button.addEventListener('click', function() {
            const fallbackUrl = this.getAttribute('data-fallback-url');
            
            // Check if there's history to go back to
            if (window.history.length > 1) {
                window.history.back();
            } else if (fallbackUrl) {
                // Fallback to provided URL if no history
                window.location.href = fallbackUrl;
            } else {
                // Default fallback to home
                window.location.href = '<?php echo esc_js(home_url('/')); ?>';
            }
        });
    });
});
</script>

