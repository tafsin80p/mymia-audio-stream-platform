<?php
/**
 * ========================================
 * NYMIA FOLLOWERS POPUP MODAL
 * ========================================
 * Template for displaying followers/following popup
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Followers/Following Modal -->
<div id="nymia-followers-modal">
    <div class="nymia-followers-modal-content">
        <!-- Modal Header -->
        <div class="nymia-followers-modal-header">
            <h3 id="nymia-followers-modal-title">Followers</h3>
            <button type="button" class="nymia-followers-modal-close" onclick="nymiaCloseUserList()" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div id="nymia-followers-modal-body">
            <!-- User list will be loaded here via AJAX -->
            <div style="text-align: center; padding: 40px; color: #999;">Loading...</div>
        </div>
    </div>
</div>

