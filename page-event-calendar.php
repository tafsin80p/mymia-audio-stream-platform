<?php
/**
 * Template Name: Event Calendar
 * Description: Public event calendar showing all scheduled live streams and group events
 */

get_header();

$current_user_id = get_current_user_id();
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-event-calendar-container">
            <div class="nymia-event-calendar-header">
                <h1><?php esc_html_e('Event Calendar', 'nymia'); ?></h1>
                <p class="nymia-event-calendar-subtitle">
                    <?php esc_html_e('Browse and book scheduled live streams and group events', 'nymia'); ?>
                </p>
            </div>
            
            <div class="nymia-event-calendar-filters">
                <button type="button" class="nymia-calendar-filter active" data-filter="all">
                    <?php esc_html_e('All Events', 'nymia'); ?>
                </button>
                <button type="button" class="nymia-calendar-filter" data-filter="group">
                    <?php esc_html_e('Group Events', 'nymia'); ?>
                </button>
                <button type="button" class="nymia-calendar-filter" data-filter="single">
                    <?php esc_html_e('Single User', 'nymia'); ?>
                </button>
            </div>
            
            <div class="nymia-event-calendar-grid" id="nymia-event-calendar-grid">
                <div class="nymia-loading" style="text-align: center; padding: 40px; color: rgba(255, 255, 255, 0.7);">
                    <?php esc_html_e('Loading events...', 'nymia'); ?>
                </div>
            </div>

<div class="nymia-private-card" style="margin-top:32px;">
    <h3 style="margin-bottom:16px;"><?php esc_html_e('Private 1:1 Availability', 'nymia'); ?></h3>
    <div class="nymia-event-calendar-grid" id="nymia-private-calendar-grid">
        <p style="color:rgba(255,255,255,0.6); grid-column:1/-1; text-align:center; padding:20px;"><?php esc_html_e('Loading private slots…', 'nymia'); ?></p>
    </div>
</div>
        </div>
    </div>
    
    <?php get_template_part('template-parts/sidebar-right'); ?>
</div>

<style>
.nymia-event-calendar-container {
    padding: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.nymia-event-calendar-header {
    margin-bottom: 30px;
}

.nymia-event-calendar-header h1 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--foreground);
}

.nymia-event-calendar-subtitle {
    color: rgba(255, 255, 255, 0.7);
    font-size: 1rem;
}

.nymia-event-calendar-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.nymia-calendar-filter {
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.nymia-calendar-filter:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
}

.nymia-calendar-filter.active {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--primary-foreground);
}

.nymia-event-calendar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.nymia-event-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.nymia-event-card:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.nymia-event-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.nymia-event-creator-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.nymia-event-creator-name {
    font-weight: 500;
    color: var(--foreground);
    font-size: 0.9rem;
}

.nymia-event-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--foreground);
    margin-bottom: 10px;
    line-height: 1.4;
}

.nymia-event-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

.nymia-event-date {
    display: flex;
    align-items: center;
    gap: 6px;
}

.nymia-event-duration {
    display: flex;
    align-items: center;
    gap: 6px;
}

.nymia-event-attendees {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--primary);
}

.nymia-event-price {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 15px;
}

.nymia-event-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 10px;
}

.nymia-event-badge.group {
    background: rgba(191, 76, 26, 0.2);
    color: var(--primary);
    border: 1px solid rgba(191, 76, 26, 0.3);
}

.nymia-event-badge.single {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
}

.nymia-event-book-btn {
    width: 100%;
    padding: 12px;
    background: var(--primary);
    color: var(--primary-foreground);
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nymia-event-book-btn:hover {
    background: hsl(18, 75%, 50%);
    transform: translateY(-1px);
}

.nymia-event-book-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .nymia-event-calendar-grid {
        grid-template-columns: 1fr;
    }
    
    .nymia-event-calendar-filters {
        justify-content: center;
    }
}
</style>

<?php get_template_part('template-parts/live-booking-modal'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarGrid = document.getElementById('nymia-event-calendar-grid');
    const filters = document.querySelectorAll('.nymia-calendar-filter');
    let currentFilter = 'all';
    let allEvents = [];
    
    // Fetch events
    function fetchEvents() {
        const ajaxUrl = (window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php';
        
        fetch(ajaxUrl + '?action=nymia_zego_list_rooms', {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && data.data) {
                // Filter only scheduled events
                allEvents = data.data.filter(event => event.status === 'scheduled');
                renderEvents();
            } else {
                calendarGrid.innerHTML = '<p style="color: rgba(255, 255, 255, 0.7); text-align: center; padding: 40px;">No scheduled events available.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching events:', error);
            calendarGrid.innerHTML = '<p style="color: rgba(255, 255, 255, 0.7); text-align: center; padding: 40px;">Error loading events. Please try again.</p>';
        });
    }
    
    // Render events
    function renderEvents() {
        let filteredEvents = allEvents;
        
        if (currentFilter === 'group') {
            filteredEvents = allEvents.filter(event => event.event_type === 'group' && event.is_event);
        } else if (currentFilter === 'single') {
            filteredEvents = allEvents.filter(event => !event.is_event || event.event_type === 'single');
        }
        
        if (filteredEvents.length === 0) {
            calendarGrid.innerHTML = '<p style="color: rgba(255, 255, 255, 0.7); text-align: center; padding: 40px;">No events found for this filter.</p>';
            return;
        }
        
        // Sort by start time
        filteredEvents.sort((a, b) => (a.start_timestamp || 0) - (b.start_timestamp || 0));
        
        const now = Math.floor(Date.now() / 1000);
        const html = filteredEvents.map(event => {
            const startTime = event.start_timestamp || 0;
            const isPast = startTime < now;
            const isFull = event.is_event && event.max_attendees > 0 && event.current_attendees >= event.max_attendees;
            
            const startDate = new Date(startTime * 1000);
            const formattedDate = startDate.toLocaleDateString('en-US', { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const duration = event.duration || 60;
            const price = event.event_price > 0 ? event.event_price : event.price || 0;
            const currency = 'USD'; // You can get this from settings
            
            let attendeesInfo = '';
            if (event.is_event && event.max_attendees > 0) {
                attendeesInfo = `<div class="nymia-event-attendees">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    ${event.current_attendees || 0} / ${event.max_attendees} attendees
                </div>`;
            }
            
            return `
                <div class="nymia-event-card" data-event-type="${event.event_type || 'single'}">
                    ${event.is_event ? `<span class="nymia-event-badge ${event.event_type || 'single'}">${event.event_type === 'group' ? 'Group Event' : 'Single User'}</span>` : ''}
                    <div class="nymia-event-card-header">
                        <img src="${event.creator_avatar || ''}" alt="${event.creator_name || ''}" class="nymia-event-creator-avatar" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/profile.png'">
                        <div class="nymia-event-creator-name">${event.creator_name || 'Unknown Creator'}</div>
                    </div>
                    <h3 class="nymia-event-title">${event.title || 'Scheduled Stream'}</h3>
                    <div class="nymia-event-details">
                        <div class="nymia-event-date">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            ${formattedDate}
                        </div>
                        <div class="nymia-event-duration">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            ${duration} minutes
                        </div>
                        ${attendeesInfo}
                    </div>
                    <div class="nymia-event-price">$${price.toFixed(2)}</div>
                    <button 
                        type="button" 
                        class="nymia-event-book-btn" 
                        data-room-id="${event.room_id || ''}"
                        data-creator-id="${event.creator_id || ''}"
                        data-stream-title="${event.title || ''}"
                        data-stream-price="${price}"
                        data-per-minute-price="${event.per_minute_price || 0}"
                        data-is-scheduled="1"
                        data-start-timestamp="${startTime}"
                        data-is-event="${event.is_event ? '1' : '0'}"
                        data-event-type="${event.event_type || 'single'}"
                        data-max-attendees="${event.max_attendees || 0}"
                        data-current-attendees="${event.current_attendees || 0}"
                        ${isPast || isFull ? 'disabled' : ''}
                        onclick="bookEvent(this)"
                    >
                        ${isPast ? 'Event Ended' : isFull ? 'Event Full' : 'Book Event'}
                    </button>
                </div>
            `;
        }).join('');
        
        calendarGrid.innerHTML = html;
    }
    
    // Filter handlers
    filters.forEach(filter => {
        filter.addEventListener('click', function() {
            filters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            renderEvents();
        });
    });
    
    // Book event function (reuse booking modal from dashboard)
    window.bookEvent = function(button) {
        if (!button) {
            return;
        }
        const data = {
            room_id: button.getAttribute('data-room-id') || '',
            creator_id: button.getAttribute('data-creator-id') || '',
            stream_title: button.getAttribute('data-stream-title') || '',
            stream_price: parseFloat(button.getAttribute('data-stream-price') || '0'),
            per_minute_price: parseFloat(button.getAttribute('data-per-minute-price') || '0'),
            is_scheduled: true,
            start_timestamp: parseInt(button.getAttribute('data-start-timestamp') || '0', 10),
            is_event: button.getAttribute('data-is-event') === '1',
            event_type: button.getAttribute('data-event-type') || 'single',
            max_attendees: parseInt(button.getAttribute('data-max-attendees') || '0', 10),
            current_attendees: parseInt(button.getAttribute('data-current-attendees') || '0', 10)
        };

        if (typeof window.nymiaOpenBookingModal === 'function') {
            window.nymiaOpenBookingModal(data);
        } else {
            window.location.href = '<?php echo home_url('/dashboard'); ?>?book_event=' + data.room_id;
        }
    };
    
    const privateGrid = document.getElementById('nymia-private-calendar-grid');

    function renderPrivateSlots(slots) {
        if (!privateGrid) return;
        if (!slots || !slots.length) {
            privateGrid.innerHTML = '<p style="color:rgba(255,255,255,0.6); grid-column:1/-1; text-align:center; padding:20px;"><?php echo esc_js(__('No private sessions available.', 'nymia')); ?></p>';
            return;
        }
        privateGrid.innerHTML = slots.map(slot => {
            const start = slot.start_timestamp ? new Date(slot.start_timestamp * 1000) : null;
            const timeLabel = start ? start.toLocaleString([], { month:'short', day:'numeric', hour:'numeric', minute:'2-digit' }) : '';
            return `
                <div class="nymia-event-card">
                    <div class="nymia-event-card-header">
                        <img src="${slot.creator_avatar || ''}" alt="${slot.creator_name || ''}" class="nymia-event-creator-avatar" onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/profile.png'">
                        <div class="nymia-event-creator-name">${slot.creator_name || ''}</div>
                    </div>
                    <h3 class="nymia-event-title"><?php echo esc_js(__('Private Session', 'nymia')); ?></h3>
                    <div class="nymia-event-details">
                        <div class="nymia-event-date">${timeLabel}</div>
                        <div class="nymia-event-duration"><?php echo esc_js(__('Duration', 'nymia')); ?>: ${slot.duration || 60} <?php echo esc_js(__('min', 'nymia')); ?></div>
                    </div>
                    <div class="nymia-event-price">$${Number(slot.price || 0).toFixed(2)}</div>
                    <button type="button" class="nymia-event-book-btn"
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
        privateGrid.querySelectorAll('.nymia-event-book-btn').forEach(btn => {
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

    function fetchPrivateSlots() {
        if (!privateGrid) return;
        privateGrid.innerHTML = '<p style="color:rgba(255,255,255,0.6); grid-column:1/-1; text-align:center; padding:20px;"><?php echo esc_js(__('Loading private slots…', 'nymia')); ?></p>';
        fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'nymia_private_public_slots' })
        })
        .then(r => r.json())
        .then(data => renderPrivateSlots(data && data.success ? (data.data?.slots || []) : []))
        .catch(() => renderPrivateSlots([]));
    }

    // Initial load
    fetchEvents();
    fetchPrivateSlots();
    
    // Refresh every 60 seconds
    setInterval(fetchEvents, 60000);
    setInterval(fetchPrivateSlots, 60000);
});
</script>

<?php get_footer(); ?>
