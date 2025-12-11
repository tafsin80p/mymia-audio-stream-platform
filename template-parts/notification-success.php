<?php
/**
 * Success Notification Component
 * This component shows success messages that automatically disappear after a few seconds
 */

// Check for registration success
if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
    <div class="nymia-notification-alert nymia-notification-success" id="nymia-registration-success" data-auto-dismiss="true">
        <div class="nymia-notification-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="nymia-notification-content">
            <h4>Account Created!</h4>
            <p>Your account has been created successfully. Welcome to Nymia!</p>
        </div>
        <button class="nymia-notification-close" onclick="this.closest('.nymia-notification-alert').remove()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                <line x1="18" x2="6" y1="6" y2="18"></line>
                <line x1="6" x2="18" y1="6" y2="18"></line>
            </svg>
        </button>
        <script>
        (function() {
            var notification = document.getElementById('nymia-registration-success');
            if (notification && notification.dataset.autoDismiss === 'true') {
                setTimeout(function() {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(500px)';
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }, 5000); // Auto-dismiss after 5 seconds
            }
        })();
        </script>
    </div>
<?php endif; ?>

