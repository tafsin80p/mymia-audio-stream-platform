/**
 * ========================================
 * NYMIA NOTIFICATION SYSTEM - JAVASCRIPT
 * ========================================
 * Handles all notification UI interactions
 * 
 * @package Nymia
 * @version 1.0
 */

// ==========================================
// NOTIFICATION SYSTEM
// ==========================================
let nymiaNotifications = {
    isOpen: false,
    soundEnabled: true,
    audioElement: null,
    audioContext: null,
    notificationSoundSrc: null,

    init: function () {
        // Determine notification sound source
        this.notificationSoundSrc = window.nymiaNotificationSound || '';

        if (this.notificationSoundSrc) {
            // Initialize audio element for notification sound
            this.audioElement = new Audio(this.notificationSoundSrc);
            this.audioElement.volume = 0.5; // Set volume to 50%
            this.audioElement.preload = 'auto'; // Preload the audio file
        } else if (window.AudioContext || window.webkitAudioContext) {
            // Fallback to Web Audio API beep if no file is set
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } else {
            console.warn('Notification sound disabled - no audio support.');
            this.soundEnabled = false;
        }

        // Store initial count to detect new notifications
        this.previousCount = 0;

        const btn = document.getElementById('nymiaNotificationBtn');
        const dropdown = document.getElementById('nymiaNotificationDropdown');
        const closeBtn = document.getElementById('nymiaNotificationClose');

        console.log('Notification system init:', { btn, dropdown, closeBtn });

        if (!btn || !dropdown) {
            console.error('Notification elements not found!');
            return;
        }

        // Toggle dropdown
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            console.log('Notification button clicked');
            this.toggle();
        });

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.close();
            });
        }

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (this.isOpen && !dropdown.contains(e.target) && !btn.contains(e.target)) {
                this.close();
            }
        });

        // Load notification count on page load
        this.loadCount();

        // Auto-refresh count every 30 seconds
        setInterval(() => {
            if (!this.isOpen) {
                this.loadCount();
            }
        }, 30000);
    },

    toggle: function () {
        const dropdown = document.getElementById('nymiaNotificationDropdown');
        if (!dropdown) {
            console.error('Dropdown not found in toggle');
            return;
        }

        console.log('Toggle called, isOpen:', this.isOpen);

        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    },

    open: function () {
        const dropdown = document.getElementById('nymiaNotificationDropdown');
        if (!dropdown) {
            console.error('Dropdown not found in open');
            return;
        }

        console.log('Opening notification dropdown');
        dropdown.classList.add('active');
        this.isOpen = true;
        this.loadNotifications();
    },

    close: function () {
        const dropdown = document.getElementById('nymiaNotificationDropdown');
        if (!dropdown) {
            console.error('Dropdown not found in close');
            return;
        }

        console.log('Closing notification dropdown');
        dropdown.classList.remove('active');
        this.isOpen = false;
    },

    loadCount: function () {
        const ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'nymia_get_notifications_count'
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const countEl = document.getElementById('nymiaNotificationCount');
                    const previousCount = parseInt(countEl ? countEl.textContent : 0) || 0;

                    if (countEl) {
                        if (data.data.count > 0) {
                            countEl.textContent = data.data.count > 99 ? '99+' : data.data.count;
                            countEl.style.display = 'flex';
                        } else {
                            countEl.style.display = 'none';
                        }
                    }

                    // Play sound if new notification arrived (count increased)
                    if (data.data.count > previousCount && this.soundEnabled && data.data.count > 0 && previousCount >= 0) {
                        console.log('New notification detected. Playing sound...', { previous: previousCount, current: data.data.count });
                        this.playNotificationSound();
                    }

                    // Update previous count
                    this.previousCount = data.data.count;
                }
            })
            .catch(error => {
                console.error('Error loading notification count:', error);
            });
    },

    loadNotifications: function () {
        const body = document.getElementById('nymiaNotificationBody');
        if (!body) return;

        body.innerHTML = '<div class="nymia-notification-loading"><span>Loading notifications...</span></div>';

        const ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'nymia_get_notifications'
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.notifications) {
                    this.renderNotifications(data.data.notifications);
                } else {
                    body.innerHTML = '<div class="nymia-notification-empty">No notifications</div>';
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                body.innerHTML = '<div class="nymia-notification-empty">Error loading notifications</div>';
            });
    },

    renderNotifications: function (notifications) {
        const body = document.getElementById('nymiaNotificationBody');
        if (!body) return;

        if (notifications.length === 0) {
            body.innerHTML = '<div class="nymia-notification-empty">No notifications</div>';
            return;
        }

        let html = '';
        notifications.forEach(notif => {
            const unreadClass = !notif.read ? 'unread' : '';
            const avatar = notif.avatar || '';
            const defaultAvatar = '/wp-content/themes/nymia-wp-theme/assets/images/profile.png';

            html += `
                <div class="nymia-notification-item ${unreadClass}" onclick="nymiaNotifications.openNotification('${notif.id}', '${notif.link}')">
                    <div class="nymia-notification-item-content">
                        <div class="nymia-notification-item-icon">
                            <img src="${avatar || defaultAvatar}" alt="Avatar" class="nymia-notification-avatar" onerror="this.src='${defaultAvatar}';" />
                        </div>
                        <div class="nymia-notification-item-text">
                            <div class="nymia-notification-item-message">${this.escapeHtml(notif.message)}</div>
                            <div class="nymia-notification-item-time">${notif.time_ago} ago</div>
                        </div>
                    </div>
                </div>
            `;
        });

        body.innerHTML = html;
    },

    openNotification: function (id, link) {
        // Mark as read
        const ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'nymia_mark_notification_read',
                notification_id: id
            })
        });

        // Reload notifications
        this.loadNotifications();

        // Reload count
        this.loadCount();

        // Redirect if link exists - use absolute URL
        if (link) {
            // If link is relative, make it absolute
            if (link.startsWith('/')) {
                window.location.href = window.location.origin + link;
            } else if (!link.startsWith('http')) {
                window.location.href = window.location.origin + '/' + link;
            } else {
                window.location.href = link;
            }
        }
    },

    escapeHtml: function (text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    playNotificationSound: function () {
        try {
            if (!this.soundEnabled) {
                console.log('Notification sound is disabled');
                return;
            }

            if (this.audioElement) {
                // Reset audio to start from beginning
                this.audioElement.currentTime = 0;

                // Play the sound with better error handling
                const playPromise = this.audioElement.play();

                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            console.log('Notification sound played successfully');
                        })
                        .catch(err => {
                            console.error('Error playing notification sound:', err);
                            if (err.name === 'NotAllowedError') {
                                console.log('Browser blocked autoplay. User interaction required first.');
                            }
                        });
                }
                return;
            }

            if (this.audioContext) {
                if (this.audioContext.state === 'suspended') {
                    this.audioContext.resume();
                }

                const duration = 0.35;
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, this.audioContext.currentTime);

                gainNode.gain.setValueAtTime(0.0001, this.audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.2, this.audioContext.currentTime + 0.02);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, this.audioContext.currentTime + duration);

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.start(this.audioContext.currentTime);
                oscillator.stop(this.audioContext.currentTime + duration);
                return;
            }

            console.warn('Notification sound could not play - no valid audio source.');
        } catch (error) {
            console.error('Error in playNotificationSound:', error);
        }
    }
};

// Initialize notifications when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        nymiaNotifications.init();
    });
} else {
    nymiaNotifications.init();
}

