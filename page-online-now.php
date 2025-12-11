<?php
/**
 * ========================================
 * NYMIA THEME - ONLINE NOW PAGE
 * ========================================
 * Displays creators who have "Online Now" enabled
 * Shows live streams with green "ONLINE NOW" status
 * 
 * @package Nymia
 * @version 1.0
 */

get_header(); ?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <!-- ======================================== -->
        <!-- ONLINE NOW PAGE CONTAINER -->
        <!-- ======================================== -->
        <div class="nymia-online-now-page">
            <!-- ======================================== -->
            <!-- ONLINE NOW HEADER -->
            <!-- ======================================== -->
            <div class="nymia-online-now-header">
                <div class="nymia-online-now-header-content">
                    <h1 class="nymia-online-now-main-title">
                        <span class="nymia-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <?php esc_html_e('Online Now', 'nymia'); ?>
                    </h1>
                    <p class="nymia-online-now-subtitle"><?php esc_html_e('Creators available for instant calls and live sessions', 'nymia'); ?></p>
                </div>
            </div>

            <!-- ======================================== -->
            <!-- ONLINE NOW CONTENT GRID -->
            <!-- ======================================== -->
            <div class="nymia-online-now-content">
                <div id="nymia-online-now-rooms" class="nymia-grid nymia-grid-3 nymia-live-streaming-grid">
                    <!-- Online now rooms will be loaded here via JavaScript -->
                    <div class="nymia-loading-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php esc_html_e('Loading online creators...', 'nymia'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php get_template_part('template-parts/sidebar-right'); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('nymia-online-now-rooms');
    if (!grid) return;

    let fetchInterval = null;
    let isFetching = false;
    let currentRoomsData = null;

    function formatScheduledTime(timestamp) {
        if (!timestamp) return '';
        const date = new Date(timestamp * 1000);
        const diff = date - Date.now();
        if (diff <= 0) return '<?php echo esc_js(__('Starting soon', 'nymia')); ?>';

        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) {
            return '<?php echo esc_js(__('Starts in', 'nymia')); ?> ' + days + 'd ' + (hours % 24) + 'h';
        }
        if (hours > 0) {
            return '<?php echo esc_js(__('Starts in', 'nymia')); ?> ' + hours + 'h ' + (minutes % 60) + 'm';
        }
        return '<?php echo esc_js(__('Starts in', 'nymia')); ?> ' + minutes + 'm';
    }

    function formatDateTime(timestamp) {
        if (!timestamp) return '';
        const date = new Date(timestamp * 1000);
        return date.toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function renderRooms(rooms) {
        if (!grid) return;

        const roomsStr = JSON.stringify(rooms);
        if (currentRoomsData === roomsStr) {
            return;
        }
        currentRoomsData = roomsStr;

        if (rooms && rooms.length > 0) {
            const existingCards = grid.querySelectorAll('.nymia-live-stream-card');
            existingCards.forEach(card => {
                card.style.transition = 'opacity 0.3s ease-out';
                card.style.opacity = '0';
            });
            setTimeout(() => doRenderRooms(rooms), existingCards.length ? 300 : 0);
        } else {
            doRenderRooms(rooms);
        }
    }

    async function doRenderRooms(rooms) {
        grid.innerHTML = '';
        if (!rooms || rooms.length === 0) {
            grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php echo esc_js(__('No creators online at the moment.', 'nymia')); ?></p></div>';
            return;
        }

        // Get user bookings if logged in
        let userBookings = [];
        if (typeof window.nymiaAjax !== 'undefined') {
            try {
                const formData = new FormData();
                formData.append('action', 'nymia_get_user_bookings');
                formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.checkoutNonce) || '');
                
                const response = await fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data && data.success) {
                    userBookings = data.data?.bookings || [];
                }
            } catch (err) {
                console.error('Error fetching user bookings:', err);
            }
        }

        rooms.forEach(r => {
            const card = document.createElement('div');
            card.className = 'nymia-content-card nymia-live-stream-card';
            const coverPhoto = r.cover_photo || '';
            const creatorAvatar = r.creator_avatar || '<?php echo get_template_directory_uri(); ?>/assets/images/profile.png';
            const creatorName = (r.creator_name && r.creator_name.trim() !== '') 
                ? r.creator_name 
                : (r.creator_id ? 'User ' + r.creator_id : 'Creator');
            const bgStyle = coverPhoto ? `background-image: url('${coverPhoto}'); background-size: cover; background-position: center;` : 'background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);';
            const viewerCount = r.viewers || 0;
            const isScheduled = r.status === 'scheduled';
            const startTimestamp = r.start_timestamp || 0;
            const isEvent = !!r.is_event;
            const maxAttendees = r.max_attendees || 0;
            const currentAttendees = r.current_attendees || 0;
            const isFull = isEvent && maxAttendees > 0 && currentAttendees >= maxAttendees;
            const scheduledCountdown = isScheduled ? formatScheduledTime(startTimestamp) : '';
            const scheduledExact = isScheduled ? formatDateTime(startTimestamp) : '';
            const eventPrice = Number(r.event_price || 0);
            const basePrice = Number(r.price || 0);
            const streamPrice = eventPrice > 0 ? eventPrice : basePrice;
            const perMinutePrice = Number(r.per_minute_price || 0);
            const isAvailableNow = r.is_available_now === true || r.is_available_now === '1' || r.is_available_now === 1;

            const badgeHtml = isScheduled
                ? `<div class="nymia-live-badge nymia-scheduled-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; margin-right: 4px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?php echo esc_js(__('Scheduled', 'nymia')); ?>
                   </div>`
                : `<div class="nymia-live-badge ${isAvailableNow ? 'nymia-online-now-badge' : ''}">
                        <span class="nymia-live-dot"></span>
                        ${isAvailableNow ? '<?php echo esc_js(__('ONLINE NOW', 'nymia')); ?>' : 'LIVE'}
                   </div>`;

            const viewersHtml = !isScheduled ? `
                <div class="nymia-live-viewers">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>${viewerCount}</span>
                </div>` : '';

            const roomId = r.room_id || r.roomId || '';
            const isVirtual = r.is_virtual === true || r.is_virtual === '1' || r.is_virtual === 1;
            const hasBooking = userBookings.some(booking => booking.room_id === roomId);
            const canJoin = hasBooking && (!isScheduled || startTimestamp <= Math.floor(Date.now() / 1000));

            let buttonLabel, buttonAction, buttonClass;
            if (isVirtual) {
                // For virtual rooms (creators with "Online Now" enabled but no active room)
                buttonLabel = '<?php echo esc_js(__('Call Now', 'nymia')); ?>';
                buttonAction = 'call';
                buttonClass = 'nymia-live-join-btn nymia-btn-gradient';
            } else if (isFull) {
                buttonLabel = '<?php echo esc_js(__('Event Full', 'nymia')); ?>';
                buttonAction = '';
                buttonClass = 'nymia-live-join-btn nymia-live-book-btn nymia-btn-gradient';
            } else if (hasBooking && isScheduled && !canJoin) {
                buttonLabel = '<?php echo esc_js(__('Booked', 'nymia')); ?>';
                buttonAction = '';
                buttonClass = 'nymia-live-join-btn nymia-live-book-btn';
            } else if (hasBooking && canJoin) {
                buttonLabel = '<?php echo esc_js(__('Join Session', 'nymia')); ?>';
                buttonAction = 'join';
                buttonClass = 'nymia-live-join-btn nymia-btn-gradient';
            } else {
                buttonLabel = isScheduled
                    ? (isEvent ? '<?php echo esc_js(__('Book Event', 'nymia')); ?>' : '<?php echo esc_js(__('Book Scheduled Stream', 'nymia')); ?>')
                    : '<?php echo esc_js(__('Book Live Session', 'nymia')); ?>';
                buttonAction = 'book';
                buttonClass = 'nymia-live-join-btn nymia-live-book-btn nymia-btn-gradient';
            }

            const priceHtml = hasBooking
                ? `<p class="nymia-live-price-text booked"><?php echo esc_js(__('Booked', 'nymia')); ?></p>`
                : `<p class="nymia-live-price-text"><?php echo esc_js(__('Price', 'nymia')); ?>: $${(streamPrice || 0).toFixed(2)}</p>`;

            const scheduleInfoHtml = isScheduled ? `
                <p class="nymia-scheduled-time" style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 4px 0;">
                    ${scheduledCountdown}
                </p>
                <p class="nymia-scheduled-datetime" style="font-size: 11px; color: rgba(255,255,255,0.6); margin: 0 0 6px;">
                    ${scheduledExact}
                </p>` : '';

            card.innerHTML = `
                <div class="nymia-card-image aspect-portrait nymia-live-cover" style="${bgStyle}">
                    <div class="nymia-live-overlay"></div>
                    ${badgeHtml}
                    ${viewersHtml}
                    <div class="nymia-live-content-wrapper">
                        <div class="nymia-live-creator-overlay">
                            <div class="nymia-live-creator-info">
                                <img src="${creatorAvatar}" alt="${creatorName}" class="nymia-live-creator-avatar" />
                                <div class="nymia-live-creator-details">
                                    <span class="nymia-live-creator-name">${creatorName}</span>
                                </div>
                            </div>
                        </div>
                        <div class="nymia-live-bottom-section">
                            <p class="nymia-live-stream-title">${r.title || ''}</p>
                            ${priceHtml}
                            ${scheduleInfoHtml}
                            <button
                                type="button"
                                class="${buttonClass}"
                                data-room-id="${roomId}"
                                data-creator-id="${r.creator_id || ''}"
                                data-stream-title="${r.title || ''}"
                                data-stream-price="${streamPrice || 0}"
                                data-per-minute-price="${perMinutePrice}"
                                data-is-scheduled="${isScheduled ? '1' : '0'}"
                                data-start-timestamp="${startTimestamp}"
                                data-is-event="${isEvent ? '1' : '0'}"
                                data-action="${buttonAction}"
                                ${(isFull || buttonAction === '') ? 'disabled' : ''}
                            >
                                ${buttonLabel}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            grid.appendChild(card);

            // Add click handler for join/book/call button
            const btn = card.querySelector('.nymia-live-join-btn');
            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const action = this.dataset.action || 'book';
                    const roomId = this.dataset.roomId || '';
                    const isVirtualRoom = roomId.startsWith('online_now_');
                    
                    if (action === 'join' && roomId && !isVirtualRoom) {
                        window.location.href = '<?php echo esc_url(home_url('/live-audio/')); ?>?room_id=' + encodeURIComponent(roomId);
                        return;
                    }
                    
                    if (action === 'call' || isVirtualRoom) {
                        // For virtual rooms, open booking modal to initiate call
                        if (typeof window.nymiaOpenBookingModal === 'function') {
                            window.nymiaOpenBookingModal({
                                room_id: roomId,
                                creator_id: this.dataset.creatorId || '',
                                stream_title: this.dataset.streamTitle || '<?php echo esc_js(__('Online Now', 'nymia')); ?>',
                                stream_price: parseFloat(this.dataset.streamPrice || '0'),
                                per_minute_price: parseFloat(this.dataset.perMinutePrice || '0'),
                                is_scheduled: false,
                                start_timestamp: 0,
                                is_event: false,
                                event_type: 'single',
                                max_attendees: 0,
                                current_attendees: 0
                            });
                        }
                        return;
                    }
                    
                    if (typeof window.nymiaOpenBookingModal === 'function') {
                        window.nymiaOpenBookingModal({
                            room_id: roomId,
                            creator_id: this.dataset.creatorId || '',
                            stream_title: this.dataset.streamTitle || '',
                            stream_price: parseFloat(this.dataset.streamPrice || '0'),
                            per_minute_price: parseFloat(this.dataset.perMinutePrice || '0'),
                            is_scheduled: this.dataset.isScheduled === '1',
                            start_timestamp: parseInt(this.dataset.startTimestamp || '0', 10),
                            is_event: this.dataset.isEvent === '1',
                            event_type: r.event_type || 'single',
                            max_attendees: parseInt(maxAttendees || '0', 10),
                            current_attendees: parseInt(currentAttendees || '0', 10)
                        });
                    }
                });
            }
        });

        // Animate cards in
        grid.querySelectorAll('.nymia-live-stream-card').forEach((card, index) => {
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transition = 'opacity 0.3s ease-in';
                card.style.opacity = '1';
            }, index * 50);
        });
    }

    function fetchOnlineNowRooms() {
        if (isFetching) return;
        isFetching = true;

        const formData = new FormData();
        formData.append('action', 'nymia_zego_list_rooms');
        formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.zegoNonce) || '');
        formData.append('online_now', '1'); // Request only online now creators

        fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && data.data && data.data.rooms) {
                renderRooms(data.data.rooms);
            } else {
                grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php echo esc_js(__('No creators online at the moment.', 'nymia')); ?></p></div>';
            }
            isFetching = false;
        })
        .catch(error => {
            console.error('Error fetching online now rooms:', error);
            grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: #ff6b6b; font-size: 1.1rem;"><?php echo esc_js(__('Error loading online creators. Please try again.', 'nymia')); ?></p></div>';
            isFetching = false;
        });
    }

    // Initial fetch
    fetchOnlineNowRooms();

    // Refresh every 30 seconds
    if (!fetchInterval) {
        fetchInterval = setInterval(fetchOnlineNowRooms, 30000);
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (fetchInterval) {
            clearInterval(fetchInterval);
            fetchInterval = null;
        }
    });
});
</script>

<?php get_footer(); ?>

