<?php
/**
 * ========================================
 * NYMIA THEME - DASHBOARD TEMPLATE
 * ========================================
 * Displays the main dashboard with:
 * - Success notifications
 * - Filter pills (All, Live Audio, E-Books, Audio Creator)
 * - Recent content grid
 * - Live audio streaming section
 * - Audio books section
 * - Audio creator suggestions
 * 
 * @package Nymia
 * @version 1.0
 */

// GET: Dashboard data and filter options
$dashboard_data = nymia_get_dashboard_data();
$filter_buttons = array('All', 'Live Audio', 'E-Books', 'Audio Book', 'Audio Creator');
?>

<!-- ======================================== -->
<!-- DASHBOARD CONTAINER -->
<!-- ======================================== -->
<div class="nymia-dashboard">
    <!-- ======================================== -->
    <!-- SUCCESS NOTIFICATIONS (Auto-dismiss after 5 seconds) -->
    <!-- ======================================== -->
    <?php get_template_part('template-parts/notification-success'); ?>
    
    <!-- ======================================== -->
    <!-- FILTER PILLS -->
    <!-- ======================================== -->
    <div class="nymia-filters">
        <?php foreach ($filter_buttons as $filter): ?>
            <?php if ($filter === 'E-Books') : ?>
                <?php 
                $ebook_page = get_page_by_path('ebook');
                $ebook_link = $ebook_page ? get_permalink($ebook_page) : site_url('/ebook/');
                ?>
                <a class="nymia-filter-btn" href="<?php echo esc_url($ebook_link); ?>">
                    <?php echo esc_html($filter); ?>
                </a>
            <?php elseif ($filter === 'Audio Creator') : ?>
                <?php 
                $audio_page = get_page_by_path('audio');
                $audio_link = $audio_page ? get_permalink($audio_page) : site_url('/audio/');
                ?>
                <a class="nymia-filter-btn" href="<?php echo esc_url($audio_link); ?>">
                    <?php echo esc_html($filter); ?>
                </a>
            <?php else: ?>
                <button class="nymia-filter-btn <?php echo $filter === 'All' ? 'active' : ''; ?>" data-filter="<?php echo esc_attr(strtolower(str_replace(' ', '-', $filter))); ?>">
                    <?php echo esc_html($filter); ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- ======================================== -->
    <!-- RECENT AUDIO SECTION (Audio Only) -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="all">
        <h3>Recents Audio</h3>
        <div class="nymia-grid nymia-grid-3">
            <?php 
            // Filter to show only audio content
            $audio_only_content = array_filter($dashboard_data['recent_content'], function($content) {
                $badge = isset($content['badge']) ? strtolower(trim($content['badge'])) : '';
                return $badge === 'audio';
            });
            
            if (empty($audio_only_content)): ?>
                <p style="color: rgba(255, 255, 255, 0.6); grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <?php esc_html_e('No audio content available.', 'nymia'); ?>
                </p>
            <?php else: ?>
                <?php foreach ($audio_only_content as $content): 
                    // Build link for audio content
                    $recent_link = '';
                    $item_id = isset($content['id']) ? intval($content['id']) : 0; // Creator user ID
                    
                    // Get unique track ID - try multiple sources
                    $audio_track_id = 0;
                    
                    // Method 1: Direct audio_id from content array
                    if (isset($content['audio_id']) && intval($content['audio_id']) > 0) {
                        $audio_track_id = intval($content['audio_id']);
                    }
                    
                    // Method 2: Try to find from audio URL match in user's transient
                    if ($audio_track_id <= 0 && !empty($content['_audio_url']) && $item_id > 0) {
                        $user_audio = get_transient('nymia_user_audio_' . $item_id);
                        if ($user_audio && is_array($user_audio)) {
                            foreach ($user_audio as $audio_item) {
                                if (isset($audio_item['url']) && $audio_item['url'] === $content['_audio_url']) {
                                    $audio_track_id = isset($audio_item['id']) ? intval($audio_item['id']) : 0;
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Method 3: Try from all_audio transient (global list)
                    if ($audio_track_id <= 0 && !empty($content['_audio_url'])) {
                        $all_audio = get_transient('nymia_all_audio');
                        if ($all_audio && is_array($all_audio)) {
                            foreach ($all_audio as $audio_item) {
                                if (isset($audio_item['url']) && $audio_item['url'] === $content['_audio_url']) {
                                    $audio_track_id = isset($audio_item['id']) ? intval($audio_item['id']) : 0;
                                    // Also update item_id if we found the track
                                    if ($audio_track_id > 0 && isset($audio_item['user_id'])) {
                                        $item_id = intval($audio_item['user_id']);
                                    }
                                    break;
                                }
                            }
                        }
                    }

                    if ($item_id > 0) {
                        // Build user-friendly URL using unique track ID: /single-audio/?track_id=123
                        // track_id is unique for each audio post, user_id is derived from it
                        $single_audio_page = get_page_by_path('single-audio');
                        if ($single_audio_page) {
                            $recent_link = get_permalink($single_audio_page);
                        } else {
                            // Fallback: use direct URL structure
                            $recent_link = home_url('/single-audio/');
                        }
                        
                        // Use unique track_id as primary identifier (each audio has different track_id)
                        if ($audio_track_id > 0) {
                            // Primary: track_id (unique for each audio)
                            $recent_link = add_query_arg('track_id', $audio_track_id, $recent_link);
                            // Secondary: user_id (for compatibility, but track_id is the key)
                            $recent_link = add_query_arg('user_id', $item_id, $recent_link);
                        } else {
                            // Fallback: if track_id not found, use user_id only
                            $recent_link = add_query_arg('user_id', $item_id, $recent_link);
                        }
                    }
                ?>
                <?php if (!empty($recent_link)): ?>
                <a href="<?php echo esc_url($recent_link); ?>" class="nymia-content-card" style="text-decoration: none; display: block; cursor: pointer;">
                <?php else: ?>
                <div class="nymia-content-card">
                <?php endif; ?>
                    <div class="nymia-card-image aspect-video">
                        <img src="<?php echo esc_url($content['image']); ?>" alt="<?php echo esc_attr($content['name']); ?>" />
                        <?php if (isset($content['badge'])): ?>
                            <span class="nymia-card-badge <?php echo esc_attr(strtolower(str_replace(' ', '-', $content['badge']))); ?>">
                                <?php echo esc_html($content['badge']); ?>
                            </span>
                        <?php endif; ?>
                        <div class="nymia-card-overlay"></div>
                    </div>
                    
                    <div class="nymia-card-info">
                        <img src="<?php echo esc_url($content['avatar']); ?>" alt="<?php echo esc_attr($content['name']); ?>" class="nymia-avatar" />
                        <div>
                            <h4><?php echo esc_html($content['name']); ?></h4>
                            <p><?php echo esc_html($content['username']); ?></p>
                        </div>
                    </div>
                <?php if (!empty($recent_link)): ?>
                </a>
                <?php else: ?>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- ======================================== -->
    <!-- LIVE AUDIO STREAMING SECTION -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="live-audio">
        <h3>Live Audio Streaming</h3>
        <div class="nymia-grid nymia-live-streaming-grid" id="nymia-live-rooms"></div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            if (window._nymiaLiveDashboardInitialized) {
                return;
            }
            window._nymiaLiveDashboardInitialized = true;

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

            function handleSlotDetailsClick(event) {
                const btn = event.currentTarget;
                const details = {
                    title: btn.dataset.slotTitle || '',
                    booked: parseInt(btn.dataset.slotBooked || '0', 10),
                    max: parseInt(btn.dataset.slotMax || '0', 10),
                    available: parseInt(btn.dataset.slotAvailable || '0', 10),
                    startTimestamp: parseInt(btn.dataset.slotStart || '0', 10),
                };
                openSlotDetailsModal(details);
            }

            function buildCalendarDays(targetDate) {
                const year = targetDate.getFullYear();
                const month = targetDate.getMonth();
                const selectedDay = targetDate.getDate();
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const leadingEmpty = (firstDay.getDay() + 6) % 7; // Monday-first
                const totalDays = lastDay.getDate();
                const cells = [];

                for (let i = 0; i < leadingEmpty; i++) {
                    cells.push({ label: '', state: 'empty' });
                }

                for (let d = 1; d <= totalDays; d++) {
                    cells.push({
                        label: d,
                        state: d === selectedDay ? 'booked' : 'default'
                    });
                }

                while (cells.length % 7 !== 0) {
                    cells.push({ label: '', state: 'empty' });
                }

                return cells;
            }

            function formatTime(timestamp) {
                const date = new Date(timestamp * 1000);
                return date.toLocaleString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit'
                }).toLowerCase();
            }

            function openSlotDetailsModal(details) {
                const overlay = document.createElement('div');
                overlay.className = 'nymia-slot-modal-overlay';

                const modal = document.createElement('div');
                modal.className = 'nymia-slot-modal';

                const availableText = details.available > 0
                    ? `<span class="nymia-slot-available">${details.available} <?php echo esc_js(__('slots left', 'nymia')); ?></span>`
                    : `<span class="nymia-slot-full"><?php echo esc_js(__('Fully Booked', 'nymia')); ?></span>`;

                const scheduledDate = details.startTimestamp
                    ? formatDateTime(details.startTimestamp)
                    : '<?php echo esc_js(__('Not scheduled', 'nymia')); ?>';
                const selectedTime = details.startTimestamp
                    ? formatTime(details.startTimestamp)
                    : '<?php echo esc_js(__('Not available', 'nymia')); ?>';
                const calendarDate = details.startTimestamp
                    ? new Date(details.startTimestamp * 1000)
                    : new Date();
                const monthLabel = calendarDate.toLocaleString('en-US', { month: 'long' });
                const yearLabel = calendarDate.getFullYear();
                const weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                    .map(label => `<span class="nymia-calendar-weekday">${label}</span>`).join('');
                const calendarDays = buildCalendarDays(calendarDate)
                    .map(day => {
                        if (!day.label) {
                            return `<span class="nymia-calendar-day empty"></span>`;
                        }
                        const classes = ['nymia-calendar-day'];
                        if (day.state === 'booked') {
                            classes.push('booked');
                        }
                        return `<span class="${classes.join(' ')}">${String(day.label).padStart(2, '0')}</span>`;
                    })
                    .join('');

                modal.innerHTML = `
                    <button type="button" class="nymia-slot-modal-close" aria-label="<?php echo esc_js(__('Close', 'nymia')); ?>">
                        &times;
                    </button>
                    <div class="nymia-slot-modal-header">
                        <p class="nymia-slot-modal-subtitle"><?php echo esc_js(__('Scheduled Session', 'nymia')); ?></p>
                        <h3>${details.title || '<?php echo esc_js(__('Live Session', 'nymia')); ?>'}</h3>
                    </div>
                    <div class="nymia-slot-modal-body">
                        <div class="nymia-calendar-wrapper">
                            <div class="nymia-calendar-header">
                                <button type="button" class="nymia-calendar-nav" disabled>&lsaquo;</button>
                                <div class="nymia-calendar-month">
                                    <span class="month">${monthLabel}</span>
                                    <span class="year">${yearLabel}</span>
                                </div>
                                <button type="button" class="nymia-calendar-nav" disabled>&rsaquo;</button>
                            </div>
                            <div class="nymia-calendar-grid nymia-calendar-weekdays">
                                ${weekdayLabels}
                            </div>
                            <div class="nymia-calendar-grid nymia-calendar-days">
                                ${calendarDays}
                            </div>
                            <div class="nymia-calendar-legend">
                                <span class="legend booked"></span> <?php echo esc_js(__('Booked day', 'nymia')); ?>
                            </div>
                        </div>
                        <div class="nymia-slot-datetime-card">
                            <div>
                                <span><?php echo esc_js(__('Selected Date', 'nymia')); ?></span>
                                <strong>${scheduledDate}</strong>
                            </div>
                            <div>
                                <span><?php echo esc_js(__('Selected Time', 'nymia')); ?></span>
                                <strong>${selectedTime}</strong>
                            </div>
                        </div>
                        <div class="nymia-slot-stats-grid">
                            <div class="nymia-slot-stat-card">
                                <span><?php echo esc_js(__('Slots Booked', 'nymia')); ?></span>
                                <strong>${details.booked}/${details.max}</strong>
                            </div>
                            <div class="nymia-slot-stat-card">
                                <span><?php echo esc_js(__('Available Slots', 'nymia')); ?></span>
                                <strong>${details.available}</strong>
                            </div>
                        </div>
                        <div class="nymia-slot-info-note">
                            ${availableText}
                        </div>
                    </div>
                    <div class="nymia-slot-countdown">
                        <span class="nymia-slot-countdown-label"><?php echo esc_js(__('Starts In', 'nymia')); ?></span>
                        <strong id="nymia-slot-countdown-value"></strong>
                    </div>
                `;

                overlay.appendChild(modal);
                document.body.appendChild(overlay);

                const countdownEl = modal.querySelector('#nymia-slot-countdown-value');

                function updateCountdown() {
                    if (!countdownEl) return;
                    if (!details.startTimestamp) {
                        countdownEl.textContent = '<?php echo esc_js(__('Not scheduled', 'nymia')); ?>';
                        return;
                    }
                    const countdown = formatScheduledTime(details.startTimestamp);
                    countdownEl.textContent = countdown;
                }

                updateCountdown();
                const countdownInterval = setInterval(updateCountdown, 1000);

                function closeModal() {
                    clearInterval(countdownInterval);
                    overlay.remove();
                    document.removeEventListener('keydown', escHandler);
                }

                function escHandler(e) {
                    if (e.key === 'Escape') {
                        closeModal();
                    }
                }

                document.addEventListener('keydown', escHandler);

                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeModal();
                    }
                });

                const closeBtn = modal.querySelector('.nymia-slot-modal-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', closeModal);
                }
            }

            async function doRenderRooms(rooms){
                grid.innerHTML = '';
                if (!rooms || rooms.length === 0){
                    grid.innerHTML = '<p style="color:#aaa;"><?php echo esc_js(__('No live or scheduled streams available.', 'nymia')); ?></p>';
                    return;
                }

                // Get user bookings once
                const userBookings = await getUserBookings();

                rooms.forEach(r => {
                    const card = document.createElement('div');
                    card.className = 'nymia-content-card nymia-live-stream-card';
                    const coverPhoto = r.cover_photo || '';
                    const creatorAvatar = r.creator_avatar || '<?php echo get_template_directory_uri(); ?>/assets/images/profile.png';
                    // Creator name should always be provided by backend from user's display_name or user_login
                    // Fallback only if somehow missing
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
                                <button
                                    type="button"
                                    class="nymia-slot-details-btn"
                                    data-slot-title="${r.title || ''}"
                                    data-slot-booked="${currentAttendees}"
                                    data-slot-max="${maxAttendees}"
                                    data-slot-available="${Math.max(maxAttendees - currentAttendees, 0)}"
                                    data-slot-start="${startTimestamp}"
                                >
                                    <?php echo esc_js(__('Slots Booked', 'nymia')); ?>: ${currentAttendees}/${maxAttendees}
                                </button>
                            `;
                        } else {
                            attendeesHtml = `
                                <p class="nymia-event-attendees" style="font-size: 12px; color: rgba(255,255,255,0.75); margin: 4px 0;">
                                    <?php echo esc_js(__('Slots Booked', 'nymia')); ?>: ${currentAttendees}/${maxAttendees}
                                </p>
                            `;
                        }
                    }

                    const scheduleInfoHtml = isScheduled ? `
                        <p class="nymia-scheduled-time" style="font-size: 12px; color: rgba(255,255,255,0.8); margin: 4px 0;">
                            ${scheduledCountdown}
                        </p>
                        <p class="nymia-scheduled-datetime" style="font-size: 11px; color: rgba(255,255,255,0.6); margin: 0 0 6px;">
                            ${scheduledExact}
                        </p>` : '';

                    const roomId = r.room_id || r.roomId || '';
                    const hasBooking = hasValidBooking(userBookings, roomId);
                    const bookingDetails = getBookingDetails(userBookings, roomId);
                    const canJoin = hasBooking && (!isScheduled || canJoinScheduledBooking(bookingDetails, startTimestamp));
                    
                    let buttonLabel, buttonAction, buttonClass;
                    if (isFull) {
                        buttonLabel = '<?php echo esc_js(__('Event Full', 'nymia')); ?>';
                        buttonAction = '';
                        buttonClass = 'nymia-live-join-btn nymia-live-book-btn nymia-btn-gradient';
                    } else if (hasBooking && isScheduled && !canJoin) {
                        // Scheduled booking but time hasn't started yet
                        buttonLabel = '<?php echo esc_js(__('Booked', 'nymia')); ?>';
                        buttonAction = '';
                        buttonClass = 'nymia-live-join-btn nymia-live-book-btn';
                        // Use different styling for "Booked" state (no gradient, disabled look)
                    } else if (hasBooking && canJoin) {
                        // Has booking and can join (either not scheduled or scheduled time has started)
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
                        ? `<p style="font-size:12px;color:#34d399;margin:4px 0;"><?php echo esc_js(__('Booked', 'nymia')); ?></p>`
                        : `<p style="font-size:12px;color:rgba(255,255,255,0.8);margin:4px 0;"><?php echo esc_js(__('Price', 'nymia')); ?>: $${(streamPrice || 0).toFixed(2)}</p>`;

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
                                    ${attendeesHtml}
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
                                        data-event-type="${r.event_type || 'single'}"
                                        data-max-attendees="${maxAttendees}"
                                        data-current-attendees="${currentAttendees}"
                                        data-action="${buttonAction}"
                                        ${(isFull || buttonAction === '') ? 'disabled' : ''}
                                    >
                                        ${buttonLabel}
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    grid.appendChild(card);

                    const slotBtn = card.querySelector('.nymia-slot-details-btn');
                    if (slotBtn) {
                        slotBtn.addEventListener('click', handleSlotDetailsClick);
                    }
                });

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

            document.addEventListener('click', function(event){
                const btn = event.target.closest('.nymia-live-join-btn');
                if (!btn) {
                    return;
                }
                event.preventDefault();
                
                const action = btn.dataset.action || 'book';
                const roomId = btn.dataset.roomId || '';
                
                // If user has booking, join directly
                if (action === 'join' && roomId) {
                    window.location.href = '<?php echo esc_url(home_url('/live-audio/')); ?>?room_id=' + encodeURIComponent(roomId);
                    return;
                }
                
                // Otherwise, open booking modal
                if (typeof window.nymiaOpenBookingModal !== 'function') {
                    console.warn('Booking modal is not ready');
                    return;
                }
                window.nymiaOpenBookingModal({
                    room_id: roomId,
                    creator_id: btn.dataset.creatorId || '',
                    stream_title: btn.dataset.streamTitle || '',
                    stream_price: parseFloat(btn.dataset.streamPrice || '0'),
                    per_minute_price: parseFloat(btn.dataset.perMinutePrice || '0'),
                    is_scheduled: btn.dataset.isScheduled === '1',
                    start_timestamp: parseInt(btn.dataset.startTimestamp || '0', 10),
                    is_event: btn.dataset.isEvent === '1',
                    event_type: btn.dataset.eventType || 'single',
                    max_attendees: parseInt(btn.dataset.maxAttendees || '0', 10),
                    current_attendees: parseInt(btn.dataset.currentAttendees || '0', 10)
                });
            });
        });
        </script>
    </section>
    
    <!-- ======================================== -->
    <!-- PRIVATE 1:1 SESSIONS LIST -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="private-sessions-list">
        <div class="nymia-section-header" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
            <div>
                <h3><?php esc_html_e('Pre-booked Private Sessions', 'nymia'); ?></h3>
                <p class="nymia-section-subtitle" style="margin:4px 0 0;"><?php esc_html_e('These are the 1:1 availability slots you have published. Customers book them from the public dashboard.', 'nymia'); ?></p>
            </div>
        </div>
        <div class="nymia-private-card" style="margin-top:16px;">
            <div class="nymia-private-list-header" style="margin-bottom:8px;">
                <div>
                    <h4><?php esc_html_e('Upcoming Slots', 'nymia'); ?></h4>
                    <p class="nymia-section-subtitle" style="margin:4px 0 0;"><?php esc_html_e('Booked sessions are locked automatically. Manage edits from the creator dashboard.', 'nymia'); ?></p>
                </div>
                <button type="button" class="nymia-btn-outline" id="nymiaDashboardPrivateRefresh"><?php esc_html_e('Refresh', 'nymia'); ?></button>
            </div>
            <div id="nymiaDashboardPrivateSlots" class="nymia-private-slots"></div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const container = document.getElementById('nymiaDashboardPrivateSlots');
            if (!container) {
                return;
            }
            const refreshBtn = document.getElementById('nymiaDashboardPrivateRefresh');
            const ajaxUrl = (window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php';
            const nonce = '<?php echo wp_create_nonce('nymia_private_sessions'); ?>';

            function renderSlots(slots){
                if (!slots || !slots.length) {
                    container.innerHTML = '<p style="color:#9ca3af;text-align:center;padding:20px;"><?php echo esc_js(__('No private slots published yet. Use the Manage button above to add some.', 'nymia')); ?></p>';
                    return;
                }
                container.innerHTML = slots.map(function(slot){
                    const start = new Date((slot.start_timestamp || 0) * 1000);
                    const end = new Date((slot.start_timestamp + (slot.duration || 0) * 60) * 1000);
                    const booked = slot.status !== 'available';
                    return `
                        <div class="nymia-private-slot-row">
                            <div>
                                <p class="nymia-slot-title">${start.toLocaleString([], { month:'short', day:'numeric', hour:'numeric', minute:'2-digit' })} – ${end.toLocaleTimeString([], { hour:'numeric', minute:'2-digit' })}</p>
                                <small><?php echo esc_js(__('Duration', 'nymia')); ?>: ${slot.duration} <?php echo esc_js(__('min', 'nymia')); ?> · <?php echo esc_js(__('Price', 'nymia')); ?> $${Number(slot.price || 0).toFixed(2)}</small>
                                ${booked ? `<p style="margin:4px 0 0;color:#fbbf24;font-size:0.85rem;"><?php echo esc_js(__('Booked', 'nymia')); ?></p>` : ''}
                            </div>
                            <div class="nymia-slot-actions">
                                <span class="nymia-slot-badge ${slot.status}">${booked ? '<?php echo esc_js(__('Booked', 'nymia')); ?>' : '<?php echo esc_js(__('Available', 'nymia')); ?>'}</span>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function fetchSlots(){
                const fd = new FormData();
                fd.append('action','nymia_private_get_slots');
                fd.append('nonce', nonce);
                container.innerHTML = '<p style="color:#9ca3af;text-align:center;padding:20px;"><?php echo esc_js(__('Loading slots…', 'nymia')); ?></p>';
                fetch(ajaxUrl, { method:'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (!data || !data.success) {
                            throw new Error((data && data.data && data.data.message) || (data && data.message) || 'Error');
                        }
                        renderSlots(data.data?.slots || []);
                    })
                    .catch(err => {
                        container.innerHTML = `<p style="color:#f87171;text-align:center;padding:20px;">${err.message}</p>`;
                    });
            }

            if (refreshBtn) {
                refreshBtn.addEventListener('click', fetchSlots);
            }

            fetchSlots();
        });
        </script>
    </section>
    
    <!-- ======================================== -->
    <!-- AUDIO BOOKS SECTION -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="audio-book">
        <h3>Audio Book</h3>
        <div class="nymia-grid nymia-grid-3">
            <?php if (!empty($dashboard_data['audio_books'])): ?>
            <?php foreach ($dashboard_data['audio_books'] as $content): 
                // Build link to single ebook page
                $ebook_id = isset($content['id']) ? intval($content['id']) : 0;
                $ebook_url = '';
                if ($ebook_id > 0) {
                    $single_ebook_page = get_page_by_path('single-ebook');
                    $ebook_url = $single_ebook_page ? get_permalink($single_ebook_page) : home_url('/single-ebook/');
                    $ebook_url = add_query_arg('ebook', $ebook_id, $ebook_url);
                } elseif (!empty($content['ebook_url'])) {
                    // Fallback to ebook URL if no ID
                    $ebook_url = $content['ebook_url'];
                }
                ?>
                <?php if (!empty($ebook_url)): ?>
                    <a href="<?php echo esc_url($ebook_url); ?>" class="nymia-content-card" style="text-decoration: none; display: block; cursor: pointer;">
                <?php else: ?>
                    <div class="nymia-content-card">
                <?php endif; ?>
                    <div class="nymia-card-image aspect-video">
                        <img src="<?php echo esc_url($content['image']); ?>" alt="<?php echo esc_attr($content['name']); ?>" />
                        <?php if (isset($content['badge'])): ?>
                            <span class="nymia-card-badge <?php echo esc_attr(strtolower(str_replace(' ', '-', $content['badge']))); ?>">
                                <?php echo esc_html($content['badge']); ?>
                            </span>
                        <?php endif; ?>
                        <div class="nymia-card-overlay"></div>
                    </div>
                    
                    <div class="nymia-card-info">
                        <img src="<?php echo esc_url($content['avatar']); ?>" alt="<?php echo esc_attr($content['name']); ?>" class="nymia-avatar" />
                        <div>
                            <h4><?php echo esc_html($content['name']); ?></h4>
                            <p><?php echo esc_html($content['username']); ?></p>
                        </div>
                    </div>
                <?php if (!empty($ebook_url)): ?>
                    </a>
                <?php else: ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #9CA3AF; text-align: center; padding: 40px 20px;">No ebooks available yet.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- ======================================== -->
    <!-- RECENT POSTS SECTION -->
    <!-- ======================================== -->
    <?php 
    $recent_posts = function_exists('nymia_get_recent_social_posts') ? nymia_get_recent_social_posts(9) : array();
    if (!empty($recent_posts)): 
    ?>
    <section class="nymia-section" data-category="posts">
        <h3><?php esc_html_e('Recent Posts', 'nymia'); ?></h3>
        <div class="nymia-grid nymia-grid-3">
            <?php foreach ($recent_posts as $post): 
                $single_post_page = get_page_by_path('single-post');
                $post_link = $single_post_page ? get_permalink($single_post_page) : home_url('/single-post/');
                $post_link = add_query_arg('post_id', $post['id'], $post_link);
            ?>
                <a href="<?php echo esc_url($post_link); ?>" class="nymia-content-card nymia-post-card" style="text-decoration: none; display: block; cursor: pointer;">
                    <div class="nymia-card-image aspect-video">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo esc_url($post['image']); ?>" alt="<?php echo esc_attr($post['title']); ?>" />
                        <?php else: ?>
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100%; display: flex; align-items: center; justify-content: center;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <span class="nymia-card-badge post"><?php esc_html_e('Post', 'nymia'); ?></span>
                        <div class="nymia-card-overlay"></div>
                    </div>
                    
                    <div class="nymia-card-info">
                        <img src="<?php echo esc_url($post['avatar']); ?>" alt="<?php echo esc_attr($post['author_name']); ?>" class="nymia-avatar" />
                        <div>
                            <h4><?php echo esc_html($post['title']); ?></h4>
                            <p><?php echo esc_html($post['author_username']); ?></p>
                        </div>
                    </div>
                    
                    <div class="nymia-post-card-excerpt">
                        <p><?php echo esc_html(wp_trim_words(strip_tags($post['excerpt']), 15, '...')); ?></p>
                        <span class="nymia-post-time"><?php echo esc_html($post['time_ago']); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- ======================================== -->
    <!-- AUDIO CREATOR LIST (Dynamic) -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="erotic-audio-creator">
        <div class="nymia-section-header">
            <h3>Audio Creator</h3>
            <p class="nymia-section-subtitle">Discover premium adult audio content from talented creators</p>
        </div>
        <div class="nymia-grid nymia-grid-4">
            <?php 
            if (function_exists('nymia_get_all_creators_with_audio')) {
                $creators_data = nymia_get_all_creators_with_audio();
                // Filter to only show Author or Administrator roles, exclude subscribers
                if (!empty($creators_data)) {
                    $creators_data = array_values(array_filter($creators_data, function($creator){
                        $uid = isset($creator['user_id']) ? intval($creator['user_id']) : 0;
                        if (!$uid) return false;
                        // Only show authors and administrators
                        return user_can($uid, 'administrator') || user_can($uid, 'author');
                    }));
                }
            } else {
                $creators_data = array();
            }

            if (!empty($creators_data)):
                foreach ($creators_data as $creator):
                    $creator_id = isset($creator['user_id']) ? intval($creator['user_id']) : 0;
                    $creator_name = isset($creator['display_name']) ? $creator['display_name'] : (isset($creator['name']) ? $creator['name'] : 'Creator');
                    $avatar = '';
                    if (!empty($creator['profile_image'])) {
                        $avatar = $creator['profile_image'];
                    } elseif ($creator_id) {
                        $avatar = get_avatar_url($creator_id, array('size' => 150));
                    } else {
                        $avatar = get_template_directory_uri() . '/assets/images/profile.png';
                    }

                    $cover_image = '';
                    if (!empty($creator['audio_files']) && is_array($creator['audio_files'])) {
                        // Use the latest audio's cover image if present
                        $latest = $creator['audio_files'][0];
                        if (!empty($latest['cover_image'])) {
                            $cover_image = $latest['cover_image'];
                        }
                    }
                    if (empty($cover_image)) {
                        $cover_image = get_template_directory_uri() . '/assets/images/audio-placeholder.jpg';
                    }

                    // Build link to creator profile page (not single-audio page)
                    $profile_url = '';
                    if ($creator_id) {
                        $profile_url = nymia_get_user_profile_url($creator_id);
                    } elseif (!empty($creator_name)) {
                        // Try to get user by name
                        $user_by_name = get_user_by('login', $creator_name);
                        if ($user_by_name) {
                            $profile_url = nymia_get_user_profile_url($user_by_name->ID);
                        } else {
                            // Fallback to profile page with username
                            $profile_url = home_url('/profile/?username=' . urlencode($creator_name));
                        }
                    }
                    
                    // If no profile URL, use home as fallback
                    if (empty($profile_url)) {
                        $profile_url = home_url('/profile/');
                    }

                    ?>
                    <a href="<?php echo esc_url($profile_url); ?>" class="nymia-content-card nymia-creator-card" style="text-decoration: none; display: block; cursor: pointer;">
                        <div class="nymia-card-image aspect-portrait">
                            <img src="<?php echo esc_url($cover_image); ?>" alt="<?php echo esc_attr($creator_name); ?>" />
                            <div class="nymia-card-overlay nymia-creator-overlay">
                                <div class="nymia-creator-info">
                                    <div class="nymia-avatar-shell <?php echo nymia_is_creator_verified($creator_id) ? 'has-creator-badge' : ''; ?>">
                                    <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($creator_name); ?>" class="nymia-creator-avatar" />
                                    </div>
                                    <div class="nymia-creator-details">
                                        <div class="nymia-creator-heading">
                                        <span class="nymia-creator-name"><?php echo esc_html($creator_name); ?></span>
                                            <?php echo wp_kses_post(nymia_get_user_badge_markup($creator_id, null, 'nymia-creator-badge--inline')); ?>
                                        </div>
                                        <?php 
                                        $num_tracks = !empty($creator['audio_files']) && is_array($creator['audio_files']) ? count($creator['audio_files']) : 0;
                                        ?>
                                        <div class="nymia-creator-category">
                                            <span class="nymia-category-tag"><?php echo esc_html($num_tracks . ' tracks'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; else: ?>
                <p style="color:#aaa;">No creators found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php get_template_part('template-parts/live-booking-modal'); ?>
