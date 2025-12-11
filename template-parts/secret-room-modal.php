<?php
/**
 * ========================================
 * NYMIA THEME - SECRET ROOM MODAL
 * ========================================
 * Modal popup for Secret Room access warning
 * Shows consent message before allowing access to private content
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Secret Room Modal -->
<div id="nymia-secret-room-modal" class="nymia-secret-room-modal" style="display: none;">
    <div class="nymia-secret-room-overlay"></div>
    <div class="nymia-secret-room-container">
        <button type="button" class="nymia-secret-room-close" id="nymia-secret-room-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <div class="nymia-secret-room-content">
            <div class="nymia-secret-room-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            
            <h2><?php esc_html_e('Secret Room', 'nymia'); ?></h2>
            <p class="nymia-secret-room-message">
                <?php esc_html_e('Private audio content. Your entry implies consent and discretion.', 'nymia'); ?>
            </p>
            
            <div class="nymia-secret-room-actions">
                <button type="button" class="nymia-btn-outline" id="nymia-secret-room-go-back">
                    <?php esc_html_e('Go Back', 'nymia'); ?>
                </button>
                <button type="button" class="nymia-btn-gradient" id="nymia-secret-room-continue" data-secret-room-url="<?php echo esc_url(home_url('/secret-room/')); ?>">
                    <?php esc_html_e('Continue', 'nymia'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

