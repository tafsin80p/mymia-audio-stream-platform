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

<!-- Custom Confirmation Dialog -->
<div id="nymia-confirm-dialog" class="nymia-confirm-dialog">
    <div class="nymia-confirm-dialog-content">
        <div class="nymia-confirm-dialog-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 class="nymia-confirm-dialog-title">Confirm Action</h3>
        <p class="nymia-confirm-dialog-message"></p>
        <div class="nymia-confirm-dialog-buttons">
            <button type="button" class="nymia-confirm-btn-cancel" onclick="nymiaConfirmDialogClose()">Cancel</button>
            <button type="button" class="nymia-confirm-btn-confirm" onclick="nymiaConfirmDialogExecute()">Confirm</button>
        </div>
    </div>
</div>

