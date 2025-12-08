<?php
/**
 * ========================================
 * NYMIA THEME - PUBLIC DASHBOARD TEMPLATE
 * ========================================
 * Displays the public home page for non-logged-in users (subscribers/visitors)
 * Shows content preview with login prompts
 * 
 * @package Nymia
 * @version 1.0
 */

// GET: Dashboard data and filter options
$dashboard_data = nymia_get_dashboard_data();
$filter_buttons = array('All', 'Live Audio', 'E-Books', 'Audio Creator');
$login_url = home_url('/login/');
?>

<!-- ======================================== -->
<!-- PUBLIC DASHBOARD CONTAINER -->
<!-- ======================================== -->
<div class="nymia-dashboard">
    <!-- ======================================== -->
    <!-- WELCOME BANNER FOR NON-LOGGED-IN USERS -->
    <!-- ======================================== -->
    <div class="nymia-public-welcome" style="background: linear-gradient(135deg, rgba(255, 87, 34, 0.1) 0%, rgba(255, 87, 34, 0.05) 100%); border: 1px solid rgba(255, 87, 34, 0.2); border-radius: 12px; padding: 30px; margin-bottom: 30px; text-align: center;">
        <h2 style="color: #fff; margin-bottom: 10px; font-size: 28px;">Welcome to Nymia</h2>
        <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 20px; font-size: 16px;">Discover amazing audio content, ebooks, and live streaming from talented creators</p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo esc_url($login_url); ?>" class="nymia-btn-gradient" style="text-decoration: none; display: inline-block; padding: 12px 24px; border-radius: 8px; font-weight: 600;">
                <?php esc_html_e('Sign In', 'nymia'); ?>
            </a>
            <a href="<?php echo esc_url($login_url); ?>" style="text-decoration: none; display: inline-block; padding: 12px 24px; border-radius: 8px; background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid rgba(255, 255, 255, 0.2); font-weight: 600;">
                <?php esc_html_e('Create Account', 'nymia'); ?>
            </a>
        </div>
    </div>
    
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
    <!-- RECENT AUDIO SECTION (Public Preview) -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="all">
        <h3>Recent Audio</h3>
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
                <?php 
                // Show first 6 items as preview
                $preview_items = array_slice($audio_only_content, 0, 6);
                foreach ($preview_items as $content): 
                    // Build link for audio content
                    $recent_link = '';
                    $item_id = isset($content['id']) ? intval($content['id']) : 0;
                    
                    if ($item_id > 0) {
                        $single_audio_page = get_page_by_path('single-audio');
                        if ($single_audio_page) {
                            $recent_link = get_permalink($single_audio_page);
                            $recent_link = add_query_arg('user_id', $item_id, $recent_link);
                        } else {
                            $recent_link = home_url('/single-audio/?user_id=' . $item_id);
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
        
        <!-- Login Prompt -->
        <div style="text-align: center; margin-top: 30px;">
            <p style="color: rgba(255, 255, 255, 0.6); margin-bottom: 15px;"><?php esc_html_e('Sign in to access all audio content', 'nymia'); ?></p>
            <a href="<?php echo esc_url($login_url); ?>" class="nymia-btn-gradient" style="text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                <?php esc_html_e('Sign In', 'nymia'); ?>
            </a>
        </div>
    </section>

    <!-- ======================================== -->
    <!-- PRIVATE 1:1 SESSIONS -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="private-sessions-public">
        <h3><?php esc_html_e('Private 1:1 Sessions', 'nymia'); ?></h3>
        <div class="nymia-grid nymia-grid-3" id="nymia-private-public-grid">
            <p style="color:#aaa; grid-column:1/-1; text-align:center; padding:30px;"><?php esc_html_e('Loading private slots…', 'nymia'); ?></p>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            if (window._nymiaPrivatePublicInitialized) {
                return;
            }
            window._nymiaPrivatePublicInitialized = true;
            const grid = document.getElementById('nymia-private-public-grid');
            if (!grid) return;

            function renderSlots(slots) {
                if (!slots || !slots.length) {
                    grid.innerHTML = '<p style="color:#aaa; grid-column:1/-1; text-align:center; padding:30px;"><?php echo esc_js(__('No private sessions available right now.', 'nymia')); ?></p>';
                    return;
                }
                const html = slots.map(slot => {
                    const start = slot.start_timestamp ? new Date(slot.start_timestamp * 1000) : null;
                    const timeLabel = start ? start.toLocaleString([], { month:'short', day:'numeric', hour:'numeric', minute:'2-digit' }) : '';
                    return `
                        <div class="nymia-content-card">
                            <div class="nymia-card-info" style="margin-bottom:12px;">
                                <img src="${slot.creator_avatar || '<?php echo get_template_directory_uri(); ?>/assets/images/profile.png';}" alt="${slot.creator_name || ''}" class="nymia-avatar" />
                                <div>
                                    <h4>${slot.creator_name || ''}</h4>
                                    <p style="color:rgba(255,255,255,0.6);margin:0;"><?php echo esc_js(__('1:1 Private Session', 'nymia')); ?></p>
                                </div>
                            </div>
                            <p style="margin:0 0 6px;color:rgba(255,255,255,0.8);font-weight:600;">$${Number(slot.price || 0).toFixed(2)}</p>
                            <p style="margin:0 0 6px;color:rgba(255,255,255,0.7);">${timeLabel}</p>
                    <button type="button" class="nymia-btn-gradient nymia-private-book-btn"
                        data-slot-id="${slot.slot_id}"
                        data-creator-id="${slot.creator_id}"
                        data-creator-name="${slot.creator_name || ''}"
                        data-price="${slot.price}"
                        data-start="${slot.start_timestamp}"
                        data-duration="${slot.duration || 60}">
                                <?php echo esc_js(__('Book Private Session', 'nymia')); ?>
                            </button>
                        </div>
                    `;
                }).join('');
                grid.innerHTML = html;
                grid.querySelectorAll('.nymia-private-book-btn').forEach(btn => {
                    btn.addEventListener('click', function(){
                        if (typeof window.nymiaOpenBookingModal !== 'function') {
                            alert('<?php echo esc_js(__('Booking modal unavailable', 'nymia')); ?>');
                            return;
                        }
                        window.nymiaOpenBookingModal({
                            booking_type: 'private',
                            slot_id: this.dataset.slotId,
                            room_id: this.dataset.slotId,
                            creator_id: parseInt(this.dataset.creatorId || '0', 10),
                            stream_title: '<?php echo esc_js(__('Private session with', 'nymia')); ?> ' + (this.dataset.creatorName || ''),
                            stream_price: parseFloat(this.dataset.price || '0'),
                            per_minute_price: 0,
                            is_scheduled: true,
                            start_timestamp: parseInt(this.dataset.start || '0', 10),
                            is_event: false,
                            event_type: 'single',
                            max_attendees: 1,
                            current_attendees: 0
                        });
                    });
                });
            }

            fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'nymia_private_public_slots' })
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    renderSlots(data.data?.slots || []);
                } else {
                    renderSlots([]);
                }
            })
            .catch(() => renderSlots([]));
        });
        </script>
    </section>
    
    <!-- ======================================== -->
    <!-- LIVE AUDIO STREAMING SECTION (Public) -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="live-audio">
        <h3>Live Audio Streaming</h3>
        <div class="nymia-grid nymia-live-streaming-grid" id="nymia-live-rooms-public"></div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            // Prevent multiple initializations
            if (window._nymiaLivePublicInitialized) {
                console.log('Live Audio Streaming already initialized, skipping...');
                return;
            }
            window._nymiaLivePublicInitialized = true;
            
            const grid = document.getElementById('nymia-live-rooms-public');
            if (!grid) return;
            
            let fetchInterval = null;
            let isFetching = false;
            let currentRoomsData = null;
            
            function formatScheduledTime(timestamp) {
                if (!timestamp) return '';
                const date = new Date(timestamp * 1000);
                const now = new Date();
                const diff = date - now;
                
                if (diff < 0) return '';
                
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                if (days > 0) {
                    return `Starts in ${days}d ${hours}h`;
                } else if (hours > 0) {
                    return `Starts in ${hours}h ${minutes}m`;
                } else if (minutes > 0) {
                    return `Starts in ${minutes}m`;
                } else {
                    return 'Starting soon';
                }
            }
            
            function formatDateTime(timestamp) {
                if (!timestamp) return '';
                const date = new Date(timestamp * 1000);
                return date.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
            
            function renderRooms(rooms){
                if (!grid) return;
                
                // Check if data has actually changed to avoid unnecessary re-renders
                const roomsStr = JSON.stringify(rooms);
                if (currentRoomsData === roomsStr) {
                    return; // No changes, skip render
                }
                currentRoomsData = roomsStr;
                
                grid.innerHTML = '';
                if (!rooms || rooms.length === 0){
                    grid.innerHTML = '<p style="color:#aaa; grid-column: 1/-1;">No live or scheduled streams available.</p>';
                    return;
                }
                rooms.forEach(r => {
                    const card = document.createElement('div');
                    card.className = 'nymia-content-card nymia-live-stream-card';
                    const coverPhoto = r.cover_photo || '';
                    const creatorAvatar = r.creator_avatar || '<?php echo get_template_directory_uri(); ?>/assets/images/profile.png';
                    const creatorName = r.creator_name || 'Anonymous';
                    const bgStyle = coverPhoto ? `background-image: url('${coverPhoto}'); background-size: cover; background-position: center;` : 'background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);';
                    const viewerCount = r.viewers || 0;
                    const isScheduled = r.status === 'scheduled';
                    const isEvent = !!r.is_event;
                    const startTimestamp = r.start_timestamp || 0;
                    const maxAttendees = r.max_attendees || 0;
                    const currentAttendees = r.current_attendees || 0;
                    const isFull = isEvent && maxAttendees > 0 && currentAttendees >= maxAttendees;
                    const scheduledTimeText = isScheduled ? formatScheduledTime(startTimestamp) : '';
                    const scheduledDateTime = isScheduled ? formatDateTime(startTimestamp) : '';
                    const eventPrice = Number(r.event_price || 0);
                    const basePrice = Number(r.price || 0);
                    const streamPrice = eventPrice > 0 ? eventPrice : basePrice;
                    const buttonLabel = isScheduled
                        ? (isEvent ? '<?php echo esc_js(__('Book Event', 'nymia')); ?>' : '<?php echo esc_js(__('Book Scheduled Stream', 'nymia')); ?>')
                        : '<?php echo esc_js(__('Book Live Session', 'nymia')); ?>';
                    
                    let badgeHtml = '';
                    let viewersHtml = '';
                    
                    if (isScheduled) {
                        badgeHtml = `
                            <div class="nymia-live-badge nymia-scheduled-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; margin-right: 4px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                SCHEDULED
                            </div>`;
                    } else {
                        badgeHtml = `
                            <div class="nymia-live-badge">
                                <span class="nymia-live-dot"></span>
                                LIVE
                            </div>`;
                        viewersHtml = `
                            <div class="nymia-live-viewers">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>${viewerCount}</span>
                            </div>`;
                    }
                    
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
                                    <p class="nymia-live-stream-title">${r.title}</p>
                                    ${isScheduled && scheduledTimeText ? `<p class="nymia-scheduled-time" style="font-size: 12px; color: rgba(255,255,255,0.7); margin: 4px 0 8px;">${scheduledTimeText}</p>` : ''}
                                    ${isScheduled && scheduledDateTime ? `<p class="nymia-scheduled-datetime" style="font-size: 11px; color: rgba(255,255,255,0.5); margin: 0 0 8px;">${scheduledDateTime}</p>` : ''}
                                    ${isEvent && maxAttendees > 0 ? `<p class="nymia-event-attendees" style="font-size: 12px; color: rgba(255,255,255,0.7); margin: 0 0 8px;">
                                        <?php echo esc_js(__('Slots Booked', 'nymia')); ?>: ${currentAttendees}/${maxAttendees}
                                    </p>` : ''}
                                    <button type="button" class="nymia-live-join-btn nymia-btn-gradient" data-room-id="${r.room_id || ''}" data-creator-id="${r.creator_id || ''}" data-stream-title="${r.title || ''}" data-stream-price="${streamPrice}" data-per-minute-price="${r.per_minute_price || '0'}" data-is-scheduled="${isScheduled ? '1' : '0'}" data-start-timestamp="${startTimestamp}" data-is-event="${isEvent ? '1' : '0'}" data-event-type="${r.event_type || 'single'}" data-max-attendees="${maxAttendees}" data-current-attendees="${currentAttendees}" style="padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;" ${isFull ? 'disabled' : ''}>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 5px;">
                                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                        </svg>
                                        ${isFull ? '<?php echo esc_js(__('Event Full', 'nymia')); ?>' : buttonLabel}
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    grid.appendChild(card);
                });
            }
            
            function fetchRooms(){
                // Prevent concurrent fetches
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
                            renderRooms(d.data.rooms); 
                        }
                        isFetching = false;
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        isFetching = false;
                    });
            }
            
            // Initial fetch
            fetchRooms();
            
            // Set up interval only if not already set
            if (!fetchInterval) {
                fetchInterval = setInterval(fetchRooms, 30000);
            }
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                if (fetchInterval) {
                    clearInterval(fetchInterval);
                    fetchInterval = null;
                }
            });
            
            document.addEventListener('click', function(event) {
                const btn = event.target.closest('.nymia-live-join-btn');
                if (!btn) {
                    return;
                }
                event.preventDefault();
                if (typeof window.nymiaOpenBookingModal !== 'function') {
                    console.warn('Booking modal unavailable');
                    return;
                }
                window.nymiaOpenBookingModal({
                    room_id: btn.dataset.roomId || '',
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
    
    <?php get_template_part('template-parts/live-booking-modal'); ?>
    
    <!-- ======================================== -->
    <!-- AUDIO BOOKS SECTION (Public Preview) -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="e-books">
        <h3>Audio Books</h3>
        <div class="nymia-grid nymia-grid-3">
            <?php if (!empty($dashboard_data['audio_books'])): 
                // Show first 6 items as preview
                $preview_ebooks = array_slice($dashboard_data['audio_books'], 0, 6);
                foreach ($preview_ebooks as $content): 
                    $ebook_id = isset($content['id']) ? intval($content['id']) : 0;
                    $ebook_url = '';
                    if ($ebook_id > 0) {
                        $single_ebook_page = get_page_by_path('single-ebook');
                        $ebook_url = $single_ebook_page ? get_permalink($single_ebook_page) : home_url('/single-ebook/');
                        $ebook_url = add_query_arg('ebook', $ebook_id, $ebook_url);
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
            <?php endforeach; else: ?>
                <p style="color: #9CA3AF; text-align: center; padding: 40px 20px; grid-column: 1/-1;">No ebooks available yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Login Prompt -->
        <div style="text-align: center; margin-top: 30px;">
            <p style="color: rgba(255, 255, 255, 0.6); margin-bottom: 15px;"><?php esc_html_e('Sign in to access full ebook library', 'nymia'); ?></p>
            <a href="<?php echo esc_url($login_url); ?>" class="nymia-btn-gradient" style="text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                <?php esc_html_e('Sign In', 'nymia'); ?>
            </a>
        </div>
    </section>
    
    <!-- ======================================== -->
    <!-- AUDIO CREATOR LIST (Public Preview) -->
    <!-- ======================================== -->
    <section class="nymia-section" data-category="erotic-audio-creator">
        <div class="nymia-section-header">
            <h3>Audio Creators</h3>
            <p class="nymia-section-subtitle">Discover premium audio content from talented creators</p>
        </div>
        <div class="nymia-grid nymia-grid-4">
            <?php 
            if (function_exists('nymia_get_all_creators_with_audio')) {
                $creators_data = nymia_get_all_creators_with_audio();
                if (!empty($creators_data)) {
                    $creators_data = array_values(array_filter($creators_data, function($creator){
                        $uid = isset($creator['user_id']) ? intval($creator['user_id']) : 0;
                        if (!$uid) return false;
                        return user_can($uid, 'administrator') || user_can($uid, 'author');
                    }));
                    // Show first 8 creators as preview
                    $creators_data = array_slice($creators_data, 0, 8);
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
                <p style="color:#aaa; grid-column: 1/-1; text-align: center; padding: 40px;">No creators found.</p>
            <?php endif; ?>
        </div>
        
        <!-- Login Prompt -->
        <div style="text-align: center; margin-top: 30px;">
            <p style="color: rgba(255, 255, 255, 0.6); margin-bottom: 15px;"><?php esc_html_e('Sign in to follow creators and access exclusive content', 'nymia'); ?></p>
            <a href="<?php echo esc_url($login_url); ?>" class="nymia-btn-gradient" style="text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                <?php esc_html_e('Sign In', 'nymia'); ?>
            </a>
        </div>
    </section>
</div>


