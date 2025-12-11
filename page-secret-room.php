<?php
/**
 * ========================================
 * NYMIA THEME - SECRET ROOM PAGE
 * ========================================
 * Displays private/secret audio content
 * Requires user consent before showing content
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
        <!-- SECRET ROOM PAGE CONTAINER -->
        <!-- ======================================== -->
        <div class="nymia-secret-room-page">
            <!-- ======================================== -->
            <!-- SECRET ROOM HEADER -->
            <!-- ======================================== -->
            <div class="nymia-secret-room-header">
                <div class="nymia-secret-room-header-content">
                    <h1 class="nymia-secret-room-main-title">
                        <span class="nymia-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <?php esc_html_e('Secret Room', 'nymia'); ?>
                    </h1>
                    <p class="nymia-secret-room-subtitle"><?php esc_html_e('Private audio content. Your entry implies consent and discretion.', 'nymia'); ?></p>
                </div>
            </div>

            <!-- ======================================== -->
            <!-- SECRET ROOM CONTENT GRID -->
            <!-- ======================================== -->
            <div class="nymia-secret-room-content">
                <?php
                // Get Secret Room content grouped by sub-category
                $secret_content = function_exists('nymia_get_secret_room_content') ? nymia_get_secret_room_content() : array();
                $subcategories = nymia_get_secret_room_subcategories();
                
                $has_content = false;
                foreach ($secret_content as $cat => $items) {
                    if (!empty($items)) {
                        $has_content = true;
                        break;
                    }
                }
                
                if (!$has_content): ?>
                    <div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php esc_html_e('No secret room content available.', 'nymia'); ?></p>
                    </div>
                <?php else:
                    // Display each sub-category section
                    foreach ($subcategories as $cat_key => $cat_label):
                        if (empty($secret_content[$cat_key])) {
                            continue;
                        }
                        $items = $secret_content[$cat_key];
                ?>
                        <div class="nymia-secret-room-category-section" style="margin-bottom: 48px;">
                            <h2 class="nymia-secret-room-category-title" style="font-size: 1.5rem; margin-bottom: 24px; color: var(--foreground); display: flex; align-items: center; gap: 12px;">
                                <span class="nymia-category-icon">
                                    <?php if ($cat_key === 'audio_book'): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
                                    <?php elseif ($cat_key === 'live_streaming'): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                                    <?php elseif ($cat_key === 'ebook'): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>
                                    <?php endif; ?>
                                </span>
                                <?php echo esc_html($cat_label); ?>
                                <span style="color: var(--muted-foreground); font-size: 0.9rem; font-weight: normal;">(<?php echo count($items); ?>)</span>
                            </h2>
                            
                            <div class="nymia-grid nymia-grid-3 nymia-live-streaming-grid">
                                <?php foreach ($items as $item): ?>
                                    <div class="nymia-content-card nymia-live-stream-card">
                                        <?php
                                        $cover_image = '';
                                        $title = isset($item['title']) ? $item['title'] : '';
                                        $author = isset($item['author']) ? $item['author'] : 'Unknown';
                                        
                                        if (isset($item['content_type']) && $item['content_type'] === 'ebook') {
                                            $cover_image = isset($item['thumbnail']) ? $item['thumbnail'] : '';
                                            $link = home_url('/single-ebook/?ebook=' . esc_attr($item['id']));
                                        } elseif (isset($item['content_type']) && $item['content_type'] === 'audiobook') {
                                            $cover_image = isset($item['cover_image']) ? $item['cover_image'] : (isset($item['thumbnail']) ? $item['thumbnail'] : '');
                                            $link = home_url('/audiobook/?id=' . esc_attr($item['id']));
                                        } else {
                                            $cover_image = isset($item['cover_image']) ? $item['cover_image'] : '';
                                            $link = home_url('/single-audio/?audio=' . esc_attr($item['id']));
                                        }
                                        
                                        $bg_style = $cover_image 
                                            ? 'background-image: url(' . esc_url($cover_image) . '); background-size: cover; background-position: center;'
                                            : 'background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);';
                                        ?>
                                        <div class="nymia-card-image aspect-portrait" style="<?php echo $bg_style; ?>">
                                            <div class="nymia-card-overlay">
                                                <div class="nymia-live-creator-info">
                                                    <span class="nymia-live-creator-name"><?php echo esc_html($author); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="nymia-card-content">
                                            <h3 class="nymia-card-title"><?php echo esc_html($title); ?></h3>
                                            <div class="nymia-card-footer">
                                                <a href="<?php echo esc_url($link); ?>" class="nymia-live-join-btn" style="text-decoration: none; display: inline-block;">
                                                    <?php esc_html_e('View', 'nymia'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- JavaScript-loaded secret rooms (for Live Streaming via ZegoCloud) -->
                <div id="nymia-secret-rooms" class="nymia-grid nymia-grid-3 nymia-live-streaming-grid" style="display: none;">
                    <!-- Secret rooms will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <?php get_template_part('template-parts/sidebar-right'); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('nymia-secret-rooms');
    if (!grid) return;

    // Check if user has consented to view secret rooms
    const hasConsented = sessionStorage.getItem('nymia_secret_room_consent') === 'true';
    
    if (!hasConsented) {
        // Show modal first
        const secretRoomModal = document.getElementById('nymia-secret-room-modal');
        if (secretRoomModal) {
            secretRoomModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Handle continue button
            const continueBtn = document.getElementById('nymia-secret-room-continue');
            if (continueBtn) {
                continueBtn.addEventListener('click', function() {
                    sessionStorage.setItem('nymia_secret_room_consent', 'true');
                    secretRoomModal.style.display = 'none';
                    document.body.style.overflow = '';
                    fetchSecretRooms();
                });
            }
            
            // Handle go back button
            const goBackBtn = document.getElementById('nymia-secret-room-go-back');
            if (goBackBtn) {
                goBackBtn.addEventListener('click', function() {
                    window.history.back();
                });
            }
        } else {
            // If modal not found, just fetch rooms
            fetchSecretRooms();
        }
    } else {
        // User has already consented, fetch rooms directly
        fetchSecretRooms();
    }

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

    async function getUserBookings() {
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
                return data.data?.bookings || [];
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
                
                if (paymentType === 'full') {
                    return true;
                }
                
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

    function canJoinScheduledBooking(booking, startTimestamp) {
        if (!booking) return false;
        
        const isScheduled = booking.is_scheduled === true || booking.is_scheduled === '1' || booking.is_scheduled === 1;
        if (!isScheduled) {
            return true;
        }
        
        if (!startTimestamp || startTimestamp <= 0) {
            return true;
        }
        
        const now = Math.floor(Date.now() / 1000);
        return startTimestamp <= now;
    }

    async function renderRooms(rooms) {
        if (!grid) return;

        grid.innerHTML = '';
        
        if (!rooms || rooms.length === 0) {
            grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php echo esc_js(__('No secret rooms available.', 'nymia')); ?></p></div>';
            return;
        }

        const userBookings = await getUserBookings();

        rooms.forEach((r, index) => {
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
            const roomId = r.room_id || r.roomId || '';
            const booking = getBookingDetails(userBookings, roomId);
            const hasBooking = hasValidBooking(userBookings, roomId);
            const canJoin = hasBooking && canJoinScheduledBooking(booking, startTimestamp);

            const badgeHtml = isScheduled 
                ? '<span class="nymia-live-badge nymia-scheduled-badge"><?php echo esc_js(__('Scheduled', 'nymia')); ?></span>'
                : '<span class="nymia-live-badge"><?php echo esc_js(__('LIVE', 'nymia')); ?></span>';

            const viewersHtml = !isScheduled && viewerCount > 0
                ? `<div class="nymia-viewers-count"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg> ${viewerCount}</div>`
                : '';

            const attendeesHtml = isEvent && maxAttendees > 0
                ? `<div class="nymia-attendees-count"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg> ${currentAttendees}/${maxAttendees}</div>`
                : '';

            const priceHtml = streamPrice > 0
                ? `<span class="nymia-card-price">$${streamPrice.toFixed(2)}</span>`
                : '<span class="nymia-card-price nymia-free"><?php echo esc_js(__('Free', 'nymia')); ?></span>';

            // For secret rooms, always show "Join" button instead of "Book"
            const joinButtonText = isFull && !canJoin
                ? '<?php echo esc_js(__('Full', 'nymia')); ?>'
                : '<?php echo esc_js(__('Join', 'nymia')); ?>';

            // Always use join button class for secret rooms
            const joinButtonClass = 'nymia-live-join-btn';

            function getBookingDetails(bookings, roomId) {
                if (!bookings || !Array.isArray(bookings)) return null;
                for (const booking of bookings) {
                    if (booking.room_id === roomId) {
                        return booking;
                    }
                }
                return null;
            }

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
                    <h3 class="nymia-card-title">${r.title || '<?php echo esc_js(__('Secret Room', 'nymia')); ?>'}</h3>
                    ${isScheduled ? `<p class="nymia-card-meta">${scheduledExact}</p>` : ''}
                    ${isScheduled && scheduledCountdown ? `<p class="nymia-card-meta">${scheduledCountdown}</p>` : ''}
                    <div class="nymia-card-footer">
                        ${priceHtml}
                        <button 
                            type="button" 
                            class="${joinButtonClass}" 
                            data-room-id="${roomId}"
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
            
            // Animate card in
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transition = 'opacity 0.3s ease-in';
                card.style.opacity = '1';
            }, index * 50);
        });

        // Attach event listeners to join/book buttons
        attachButtonListeners();
    }

    function attachButtonListeners() {
        const joinButtons = grid.querySelectorAll('.nymia-live-join-btn, .nymia-live-book-btn');
        joinButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const roomId = this.dataset.roomId;
                const creatorId = this.dataset.creatorId;
                const streamTitle = this.dataset.streamTitle || '';
                const streamPrice = parseFloat(this.dataset.streamPrice || '0');
                const perMinutePrice = parseFloat(this.dataset.perMinutePrice || '0');
                const isScheduled = this.dataset.isScheduled === '1';
                const startTimestamp = parseInt(this.dataset.startTimestamp || '0', 10);
                const isEvent = this.dataset.isEvent === '1';
                const eventType = this.dataset.eventType || 'single';
                const maxAttendees = parseInt(this.dataset.maxAttendees || '0', 10);
                const currentAttendees = parseInt(this.dataset.currentAttendees || '0', 10);

                if (!roomId) {
                    console.error('Room ID is missing');
                    return;
                }

                // Redirect to live audio page to join the secret room
                const url = new URL('<?php echo esc_url(home_url('/live-audio/')); ?>', window.location.origin);
                url.searchParams.set('room_id', roomId);
                if (creatorId) url.searchParams.set('creator', creatorId);
                if (streamPrice > 0) {
                    url.searchParams.set('price', streamPrice);
                }
                if (perMinutePrice > 0) {
                    url.searchParams.set('per_minute_price', perMinutePrice);
                }
                if (isScheduled) {
                    url.searchParams.set('scheduled', '1');
                }
                if (isEvent) {
                    url.searchParams.set('is_event', '1');
                }
                window.location.href = url.toString();
            });
        });
    }

    function fetchSecretRooms() {
        if (!grid) return;

        const formData = new FormData();
        formData.append('action', 'nymia_zego_list_rooms');
        formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.zegoNonce) || '');
        formData.append('secret_only', '1'); // Request only secret rooms

        fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && data.data && data.data.rooms) {
                // Strictly filter for secret rooms only - exclude any room without is_secret flag
                const secretRooms = data.data.rooms.filter(room => {
                    // Only include rooms that explicitly have is_secret set to true/1/'1'
                    const isSecret = room.is_secret === true || room.is_secret === '1' || room.is_secret === 1;
                    // Exclude rooms where is_secret is false, null, undefined, or 0
                    return isSecret === true;
                });
                
                if (secretRooms.length === 0) {
                    grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php echo esc_js(__('No secret rooms available.', 'nymia')); ?></p></div>';
                } else {
                    renderRooms(secretRooms);
                }
            } else {
                grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: rgba(255, 255, 255, 0.6); font-size: 1.1rem;"><?php echo esc_js(__('No secret rooms available.', 'nymia')); ?></p></div>';
            }
        })
        .catch(error => {
            console.error('Error fetching secret rooms:', error);
            grid.innerHTML = '<div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;"><p style="color: #ff6b6b; font-size: 1.1rem;"><?php echo esc_js(__('Error loading secret rooms. Please try again.', 'nymia')); ?></p></div>';
        });
    }

    // Make fetchSecretRooms available globally for modal continue button
    window.fetchSecretRooms = fetchSecretRooms;
});
</script>

<?php get_footer(); ?>

