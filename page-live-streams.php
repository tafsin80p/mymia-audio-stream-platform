<?php
/**
 * ========================================
 * NYMIA THEME - LIVE AUDIO STREAMS PAGE
 * ========================================
 * Displays all live and scheduled audio streams
 * 
 * @package Nymia
 * @version 1.0
 */

get_header(); 
?>

<div class="nymia-container">
    <?php 
    // Only show sidebar for logged-in users with appropriate capabilities
    if (is_user_logged_in() && (current_user_can('edit_posts') || current_user_can('manage_options'))) {
        get_sidebar(); 
    }
    ?>
    
    <main class="nymia-main<?php echo (!is_user_logged_in() || (!current_user_can('edit_posts') && !current_user_can('manage_options'))) ? ' nymia-main-fullwidth' : ''; ?>">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php 
        $back_url = home_url('/dashboard/');
        get_template_part('template-parts/back-button'); 
        ?>
        
        <!-- ======================================== -->
        <!-- LIVE AUDIO STREAMS PAGE CONTAINER -->
        <!-- ======================================== -->
        <div class="nymia-live-streams-page">
            <!-- Page Header -->
            <div class="nymia-live-streams-header">
                <div class="nymia-live-streams-header-content">
                    <h1 class="nymia-live-streams-main-title">
                        <div class="nymia-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18V5l12-2v13"></path>
                                <circle cx="6" cy="18" r="3"></circle>
                                <circle cx="18" cy="16" r="3"></circle>
                            </svg>
                        </div>
                        Live Audio Streaming
                    </h1>
                    <p class="nymia-live-streams-subtitle">Join live streams and scheduled sessions from creators</p>
                </div>
            </div>

            <!-- Live Streams Grid -->
            <div class="nymia-grid nymia-live-streaming-grid" id="nymia-live-rooms"></div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Prevent duplicate initialization
    if (window._nymiaLiveStreamsPageInitialized) {
        return;
    }
    window._nymiaLiveStreamsPageInitialized = true;

    const grid = document.getElementById('nymia-live-rooms');
    if (!grid) {
        return;
    }

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

    function renderRooms(rooms){
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

    // Cache user bookings to avoid repeated AJAX calls
    let userBookingsCache = null;
    let bookingsCacheTime = 0;
    const BOOKINGS_CACHE_TTL = 60000; // 1 minute

    async function getUserBookings() {
        const now = Date.now();
        if (userBookingsCache && (now - bookingsCacheTime) < BOOKINGS_CACHE_TTL) {
            return userBookingsCache;
        }

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
                userBookingsCache = data.data?.bookings || [];
                bookingsCacheTime = now;
                return userBookingsCache;
            }
        } catch (err) {
            console.error('Error fetching user bookings:', err);
        }
        
        return [];
    }

    function hasValidBooking(bookings, roomId) {
        if (!bookings || !Array.isArray(bookings)) return false;
        
        for (const booking of bookings) {
            if (booking.room_id === roomId) {
                const paymentType = booking.payment_type || 'full';
                const expiresAt = booking.expires_at || '';
                
                // Full session bookings never expire
                if (paymentType === 'full') {
                    return true;
                }
                
                // For per-minute bookings, check if not expired
                if (paymentType === 'per_minute' && expiresAt) {
                    const expiresTimestamp = new Date(expiresAt).getTime();
                    const now = Date.now();
                    if (now <= expiresTimestamp) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    function getBookingDetails(bookings, roomId) {
        if (!bookings || !Array.isArray(bookings)) return null;
        
        for (const booking of bookings) {
            if (booking.room_id === roomId) {
                return booking;
            }
        }
        
        return null;
    }

    function canJoinScheduledBooking(booking, startTimestamp) {
        if (!booking) return false;
        
        // Check if booking is scheduled
        const isScheduled = booking.is_scheduled === true || booking.is_scheduled === '1' || booking.is_scheduled === 1;
        if (!isScheduled) {
            // Not scheduled, can join immediately
            return true;
        }
        
        // For scheduled bookings, check if time has started
        if (!startTimestamp || startTimestamp <= 0) {
            // No start timestamp, allow join
            return true;
        }
        
        const now = Math.floor(Date.now() / 1000);
        return startTimestamp <= now;
    }

    async function doRenderRooms(rooms){
        grid.innerHTML = '';
        if (!rooms || rooms.length === 0){
            grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php echo esc_js(__('No live or scheduled streams available.', 'nymia')); ?></p></div>';
            return;
        }

        // Get user bookings once
        const userBookings = await getUserBookings();

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

            const badgeHtml = isScheduled
                ? `<div class="nymia-live-badge nymia-scheduled-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; margin-right: 4px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?php echo esc_js(__('Scheduled', 'nymia')); ?>
                   </div>`
                : `<div class="nymia-live-badge">
                        <span class="nymia-live-dot"></span>
                        LIVE
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

            let attendeesHtml = '';
            if (isEvent && maxAttendees > 0) {
                if (isScheduled) {
                    attendeesHtml = `
                        <div class="nymia-live-attendees">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>${currentAttendees}/${maxAttendees}</span>
                        </div>`;
                } else {
                    attendeesHtml = `
                        <div class="nymia-live-attendees">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>${currentAttendees}/${maxAttendees}</span>
                        </div>`;
                }
            }

            const hasBooking = hasValidBooking(userBookings, r.room_id);
            const bookingDetails = getBookingDetails(userBookings, r.room_id);
            const canJoin = hasBooking && canJoinScheduledBooking(bookingDetails, startTimestamp);

            let priceHtml = '';
            if (streamPrice > 0) {
                priceHtml = `<div class="nymia-live-price">$${streamPrice.toFixed(2)}</div>`;
            } else if (perMinutePrice > 0) {
                priceHtml = `<div class="nymia-live-price">$${perMinutePrice.toFixed(2)}/min</div>`;
            } else {
                priceHtml = `<div class="nymia-live-price"><?php echo esc_js(__('Free', 'nymia')); ?></div>`;
            }

            const joinButtonText = canJoin 
                ? '<?php echo esc_js(__('Join Now', 'nymia')); ?>'
                : (isScheduled 
                    ? (isFull ? '<?php echo esc_js(__('Fully Booked', 'nymia')); ?>' : '<?php echo esc_js(__('Book Now', 'nymia')); ?>')
                    : (streamPrice > 0 || perMinutePrice > 0 ? '<?php echo esc_js(__('Join Now', 'nymia')); ?>' : '<?php echo esc_js(__('Join Now', 'nymia')); ?>'));

            const joinButtonClass = canJoin || (streamPrice === 0 && perMinutePrice === 0) 
                ? 'nymia-live-join-btn' 
                : 'nymia-live-join-btn nymia-live-book-btn';

            card.innerHTML = `
                <div class="nymia-card-image aspect-portrait" style="${bgStyle}">
                    ${badgeHtml}
                    ${viewersHtml}
                    ${attendeesHtml}
                    <div class="nymia-card-overlay">
                        <div class="nymia-live-creator-info">
                            <img src="${creatorAvatar}" alt="${creatorName}" class="nymia-live-creator-avatar" />
                            <span class="nymia-live-creator-name">${creatorName}</span>
                        </div>
                    </div>
                </div>
                <div class="nymia-card-content">
                    <h3 class="nymia-card-title">${r.title || '<?php echo esc_js(__('Live Stream', 'nymia')); ?>'}</h3>
                    ${isScheduled ? `<p class="nymia-card-meta">${scheduledExact}</p>` : ''}
                    ${isScheduled && scheduledCountdown ? `<p class="nymia-card-meta">${scheduledCountdown}</p>` : ''}
                    <div class="nymia-card-footer">
                        ${priceHtml}
                        <button 
                            type="button" 
                            class="${joinButtonClass}" 
                            data-room-id="${r.room_id || ''}"
                            data-creator-id="${r.creator_id || ''}"
                            data-stream-title="${r.title || ''}"
                            data-stream-price="${streamPrice}"
                            data-per-minute-price="${perMinutePrice}"
                            data-is-scheduled="${isScheduled ? '1' : '0'}"
                            data-start-timestamp="${startTimestamp}"
                            data-is-event="${isEvent ? '1' : '0'}"
                            data-event-type="${r.event_type || 'single'}"
                            data-max-attendees="${maxAttendees}"
                            data-current-attendees="${currentAttendees}"
                            ${isFull && !canJoin ? 'disabled' : ''}
                        >
                            ${joinButtonText}
                        </button>
                    </div>
                </div>
            `;

            grid.appendChild(card);
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

    function fetchRooms(){
        if (isFetching) {
            return;
        }
        isFetching = true;

        const fd = new FormData();
        fd.append('action','nymia_zego_list_rooms');
        fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {method:'POST', body: fd})
            .then(r=>r.json())
            .then(d=>{ 
                if(d&&d.success){ 
                    const rooms = d.data?.rooms || d.data;
                    renderRooms(rooms); 
                }
                isFetching = false;
            })
            .catch(err => {
                console.error('Fetch error:', err);
                isFetching = false;
            });
    }

    if (!fetchInterval) {
        fetchRooms();
        fetchInterval = setInterval(fetchRooms, 30000);
    }

    window.addEventListener('beforeunload', function() {
        if (fetchInterval) {
            clearInterval(fetchInterval);
            fetchInterval = null;
        }
    });

    // Handle join button clicks
    document.addEventListener('click', function(event){
        const btn = event.target.closest('.nymia-live-join-btn, .nymia-live-book-btn');
        if (!btn) {
            return;
        }
        event.preventDefault();

        const roomId = btn.dataset.roomId || '';
        const creatorId = btn.dataset.creatorId || '';
        const streamTitle = btn.dataset.streamTitle || '';
        const streamPrice = parseFloat(btn.dataset.streamPrice || '0');
        const perMinutePrice = parseFloat(btn.dataset.perMinutePrice || '0');
        const isScheduled = btn.dataset.isScheduled === '1';
        const startTimestamp = parseInt(btn.dataset.startTimestamp || '0', 10);
        const isEvent = btn.dataset.isEvent === '1';
        const eventType = btn.dataset.eventType || 'single';
        const maxAttendees = parseInt(btn.dataset.maxAttendees || '0', 10);
        const currentAttendees = parseInt(btn.dataset.currentAttendees || '0', 10);

        if (!roomId) {
            console.error('No room ID');
            return;
        }

        // Check if user is logged in
        <?php if (!is_user_logged_in()): ?>
        window.location.href = '<?php echo esc_js(home_url('/login')); ?>';
        return;
        <?php endif; ?>

        // Redirect to live audio page with room_id
        const liveAudioUrl = new URL('<?php echo esc_js(home_url('/live-audio')); ?>', window.location.origin);
        liveAudioUrl.searchParams.set('room_id', roomId);
        if (isScheduled) {
            liveAudioUrl.searchParams.set('scheduled', '1');
        }
        if (streamPrice > 0 || perMinutePrice > 0) {
            liveAudioUrl.searchParams.set('price', streamPrice > 0 ? streamPrice : perMinutePrice);
            if (perMinutePrice > 0) {
                liveAudioUrl.searchParams.set('per_minute', '1');
            }
        }
        if (isEvent) {
            liveAudioUrl.searchParams.set('is_event', '1');
            liveAudioUrl.searchParams.set('event_type', eventType);
            liveAudioUrl.searchParams.set('max_attendees', maxAttendees);
            liveAudioUrl.searchParams.set('current_attendees', currentAttendees);
        }

        window.location.href = liveAudioUrl.toString();
    });
});
</script>

<?php get_template_part('template-parts/live-booking-modal'); ?>

<?php get_footer(); ?>

