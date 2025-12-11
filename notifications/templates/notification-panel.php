<?php
/**
 * ========================================
 * NYMIA NOTIFICATION PANEL TEMPLATE
 * ========================================
 * Notification dropdown panel for header
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Notifications Dropdown -->
<div class="nymia-notification-wrapper">
    <button type="button" class="nymia-notification-btn" id="nymiaNotificationBtn" aria-label="<?php esc_attr_e('Notifications', 'nymia'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
        </svg>
        <span class="nymia-notification-count" id="nymiaNotificationCount">0</span>
    </button>
    
    <!-- Notification Dropdown -->
    <div class="nymia-notification-dropdown" id="nymiaNotificationDropdown">
        <div class="nymia-notification-header">
            <h3>Notifications</h3>
            <button type="button" class="nymia-notification-close" id="nymiaNotificationClose">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="nymia-notification-body" id="nymiaNotificationBody">
            <div class="nymia-notification-loading">
                <span>Loading notifications...</span>
            </div>
        </div>
        <div class="nymia-notification-footer">
            <button type="button" class="nymia-notification-view-all" onclick="window.location.href='<?php echo home_url('/notifications'); ?>'">
                View All Notifications
            </button>
        </div>
    </div>
</div>

