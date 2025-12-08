<?php
/**
 * Template Name: Create
 * Description: Create new content - Go Live, Audio, Ebook, or Text
 */

// Enqueue ebook styles and scripts
function nymia_enqueue_ebook_assets() {
    wp_enqueue_style('nymia-ebook-style', get_template_directory_uri() . '/create-ebook/css/ebook.css', array(), '1.0');
    wp_enqueue_script('nymia-ebook-script', get_template_directory_uri() . '/create-ebook/js/ebook.js', array('jquery'), '1.0', true);
    
    // Localize ebook script with ebookNonce
    wp_localize_script('nymia-ebook-script', 'ebookNonce', wp_create_nonce('nymia_upload_ebook'));
}
add_action('wp_enqueue_scripts', 'nymia_enqueue_ebook_assets');

get_header();

$text_posts = function_exists('nymia_get_recent_social_posts') ? nymia_get_recent_social_posts(8) : array();
$current_user_id = get_current_user_id();
$live_schedules = function_exists('nymia_get_creator_stream_schedules') ? nymia_get_creator_stream_schedules($current_user_id) : array();
$schedule_now = current_time('timestamp');
$next_schedule = null;
if (!empty($live_schedules)) {
    $sorted_schedules = $live_schedules;
    usort($sorted_schedules, function ($a, $b) {
        return ($a['start_timestamp'] ?? 0) <=> ($b['start_timestamp'] ?? 0);
    });
    foreach ($sorted_schedules as $schedule_item) {
        if (!empty($schedule_item['start_timestamp']) && $schedule_item['start_timestamp'] >= $schedule_now) {
            $next_schedule = $schedule_item;
            break;
        }
    }
}
$schedule_config = array(
    'ajaxUrl'      => admin_url('admin-ajax.php'),
    'createNonce'  => wp_create_nonce('nymia_schedule_live_stream'),
    'deleteNonce'  => wp_create_nonce('nymia_delete_stream_schedule'),
    'schedules'    => $live_schedules,
    'serverNow'    => $schedule_now,
);
if ($next_schedule) {
    $schedule_config['nextSchedule'] = $next_schedule;
}
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>  
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-create-container">
            <div class="nymia-create-main">
                <!-- Content Type Tabs -->
                <div class="nymia-create-tabs">
                    <button type="button" class="nymia-create-tab active" data-tab="live">
                        <?php esc_html_e('Go Live', 'nymia'); ?>
                    </button>
                    <button type="button" class="nymia-create-tab" data-tab="audio">
                        <?php esc_html_e('Audio', 'nymia'); ?>
                    </button>
                    <button type="button" class="nymia-create-tab" data-tab="ebook">
                        <?php esc_html_e('Ebook', 'nymia'); ?>
                    </button>
                    <button type="button" class="nymia-create-tab" data-tab="audio-book">
                        <?php esc_html_e('Audio Book', 'nymia'); ?>
                    </button>
                    <button type="button" class="nymia-create-tab" data-tab="text">
                        <?php esc_html_e('Text', 'nymia'); ?>
                    </button>
                </div>

                <!-- Go Live Content -->
                <div class="nymia-create-content active" id="content-live">
                    <div class="nymia-create-header">
                        <h1><?php esc_html_e('Live Audio Streaming', 'nymia'); ?></h1>
                        <p class="nymia-create-subtitle"><?php esc_html_e('Start your live audio session and connect with your followers', 'nymia'); ?></p>
                    </div>

                    <!-- Stream Control -->
                    <div class="nymia-stream-control">
                        <h2><?php esc_html_e('Stream Control', 'nymia'); ?></h2>
                        
                        <form id="streamSetupForm" class="nymia-stream-form">
                            <div class="nymia-form-group">
                                <label for="stream_title"><?php esc_html_e('Stream Title', 'nymia'); ?></label>
                                <input type="text" id="stream_title" name="stream_title" placeholder="<?php esc_attr_e('Enter Stream Title', 'nymia'); ?>" required />
                            </div>

                            <!-- Stream Thumbnail Upload -->
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Stream Thumbnail', 'nymia'); ?></label>
                                <div class="stream-thumbnail-upload">
                                    <input type="file" id="stream_thumbnail" name="stream_thumbnail" accept="image/*" hidden />
                                    <button type="button" id="streamThumbnailBtn" class="stream-thumbnail-upload-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        <span><?php esc_html_e('Upload Thumbnail', 'nymia'); ?></span>
                                    </button>
                                    
                                    <div id="streamThumbnailPreview" class="stream-thumbnail-preview" style="display: none;">
                                        <button type="button" id="streamThumbnailRemove" class="stream-thumbnail-remove" title="Remove thumbnail">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                        <img id="streamThumbnailImg" src="" alt="Stream thumbnail preview" />
                                    </div>
                                </div>
                            </div>
                            <div class="nymia-stream-options">
                                <div class="nymia-paid-access">
                                    <label class="nymia-toggle-wrapper">
                                        <input type="checkbox" id="paid_access" name="paid_access" />
                                        <span class="nymia-toggle-slider"></span>
                                    </label>
                                    <span class="nymia-toggle-label"><?php esc_html_e('Paid Access', 'nymia'); ?></span>
                                    <div class="nymia-price-input">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="16"></line>
                                            <line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>
                                        <input type="number" id="stream_price" name="stream_price" value="0.00" step="0.01" min="0" disabled />
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="submit" id="nymiaGoLiveBtn" class="nymia-btn-continue">
                                    <?php esc_html_e('Go Live', 'nymia'); ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Online Now (Green Light) -->
                    <div class="nymia-available-now-card" style="margin-top: 24px;">
                        <div class="nymia-available-now-header">
                            <div>
                                <h3><?php esc_html_e('Online Now', 'nymia'); ?></h3>
                                <p class="nymia-available-now-subtitle"><?php esc_html_e('Turn on to allow customers to call you instantly. You can only enable this when you\'re not in a scheduled session.', 'nymia'); ?></p>
                            </div>
                            <div class="nymia-available-toggle-wrapper">
                                <label class="nymia-available-toggle">
                                    <input type="checkbox" id="nymiaAvailableNowToggle" />
                                    <span class="nymia-available-slider"></span>
                                    <span class="nymia-available-status" id="nymiaAvailableStatus"><?php esc_html_e('Off', 'nymia'); ?></span>
                                </label>
                            </div>
                        </div>
                        <div class="nymia-available-now-settings" id="nymiaAvailableSettings" style="display: none; margin-top: 16px;">
                            <!-- Online Now Thumbnail Upload -->
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Thumbnail Image', 'nymia'); ?></label>
                                <div class="online-thumbnail-upload">
                                    <input type="file" id="online_thumbnail" name="online_thumbnail" accept="image/*" hidden />
                                    <button type="button" id="onlineThumbnailBtn" class="online-thumbnail-upload-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        <span><?php esc_html_e('Upload Thumbnail', 'nymia'); ?></span>
                                    </button>
                                    
                                    <div id="onlineThumbnailPreview" class="online-thumbnail-preview" style="display: none;">
                                        <button type="button" id="onlineThumbnailRemove" class="online-thumbnail-remove" title="Remove thumbnail">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                        <img id="onlineThumbnailImg" src="" alt="Online thumbnail preview" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="nymia-form-group">
                                <label for="nymia_per_minute_price_available"><?php esc_html_e('Price Per Minute', 'nymia'); ?></label>
                                <input type="number" id="nymia_per_minute_price_available" name="per_minute_price" step="0.01" min="0" placeholder="0.99" />
                                <small style="color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-top: 4px; display: block;"><?php esc_html_e('Set the price customers will pay per minute for instant calls.', 'nymia'); ?></small>
                            </div>
                        </div>
                    </div>

                    <?php
                    $countdown_style = empty($next_schedule) ? 'style="display:none;"' : '';
                    $next_schedule_title = $next_schedule['title'] ?? '';
                    $next_schedule_time = $next_schedule && !empty($next_schedule['start_timestamp'])
                        ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_schedule['start_timestamp'])
                        : '';
                    $schedule_min_attr = date_i18n('Y-m-d\TH:i', $schedule_now + 900);
                    ?>
                    <div class="nymia-schedule-next-card" id="nymiaScheduleCountdownCard" <?php echo $countdown_style; ?>>
                        <div>
                            <p class="nymia-schedule-label"><?php esc_html_e('Next Scheduled Stream', 'nymia'); ?></p>
                            <h3 class="nymia-schedule-countdown-title" id="nymiaScheduleCountdownTitle">
                                <?php echo esc_html($next_schedule_title); ?>
                            </h3>
                            <p class="nymia-schedule-date" id="nymiaScheduleCountdownDate">
                                <?php echo esc_html($next_schedule_time); ?>
                            </p>
                            <p class="nymia-schedule-countdown-hint" id="nymiaScheduleCountdownHint">
                                <?php esc_html_e('Your stream will start automatically when the countdown reaches zero.', 'nymia'); ?>
                            </p>
                        </div>
                        <div class="nymia-schedule-countdown" id="nymiaScheduleCountdown">--:--:--</div>
                    </div>

                    <div class="nymia-schedule-manager">
                        <div class="nymia-schedule-card">
                            <h3><?php esc_html_e('Schedule your stream', 'nymia'); ?></h3>
                            <p class="nymia-schedule-helper">
                                <?php esc_html_e('Pick a future time to automatically start a live session. We will trigger the Go Live button for you when the timer finishes.', 'nymia'); ?>
                            </p>
                            <form id="nymiaScheduleStreamForm" class="nymia-stream-form">
                                <div class="nymia-schedule-form-grid">
                                    <div class="nymia-form-group">
                                        <label for="nymia-schedule-title"><?php esc_html_e('Stream Title', 'nymia'); ?></label>
                                        <input type="text" id="nymia-schedule-title" name="title" placeholder="<?php esc_attr_e('Morning Motivation Session', 'nymia'); ?>" required />
                                    </div>
                                    <div class="nymia-form-group">
                                        <label for="nymia-schedule-start"><?php esc_html_e('Start Time', 'nymia'); ?></label>
                                        <input type="datetime-local" id="nymia-schedule-start" name="start_time" min="<?php echo esc_attr($schedule_min_attr); ?>" required />
                                    </div>
                                    <div class="nymia-form-group">
                                        <label for="nymia-schedule-duration"><?php esc_html_e('Planned Duration (minutes)', 'nymia'); ?></label>
                                        <input type="number" id="nymia-schedule-duration" name="duration" min="10" max="480" value="60" />
                                    </div>
                                    <div class="nymia-form-group">
                                        <label for="nymia-schedule-event-type"><?php esc_html_e('Event Type', 'nymia'); ?></label>
                                        <select id="nymia-schedule-event-type" name="event_type" class="nymia-select" required>
                                            <option value="single"><?php esc_html_e('Single User', 'nymia'); ?></option>
                                            <option value="group"><?php esc_html_e('Multi-User / Group Event', 'nymia'); ?></option>
                                        </select>
                                    </div>
                                    <!-- Schedule Thumbnail Upload -->
                                    <div class="nymia-form-group">
                                        <label><?php esc_html_e('Thumbnail Image', 'nymia'); ?></label>
                                        <div class="schedule-thumbnail-upload">
                                            <input type="file" id="schedule_thumbnail" name="schedule_thumbnail" accept="image/*" hidden />
                                            <button type="button" id="scheduleThumbnailBtn" class="schedule-thumbnail-upload-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <polyline points="14 2 14 8 20 8"></polyline>
                                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                                    <polyline points="10 9 9 9 8 9"></polyline>
                                                </svg>
                                                <span><?php esc_html_e('Upload Thumbnail', 'nymia'); ?></span>
                                            </button>
                                            
                                            <div id="scheduleThumbnailPreview" class="schedule-thumbnail-preview" style="display: none;">
                                                <button type="button" id="scheduleThumbnailRemove" class="schedule-thumbnail-remove" title="Remove thumbnail">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                </button>
                                                <img id="scheduleThumbnailImg" src="" alt="Schedule thumbnail preview" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="nymia-form-group">
                                        <label for="nymia-schedule-price"><?php esc_html_e('Event Price', 'nymia'); ?> <span style="color: rgba(255, 255, 255, 0.5); font-size: 0.85rem;">(<?php esc_html_e('Fixed price per event', 'nymia'); ?>)</span></label>
                                        <input type="number" id="nymia-schedule-price" name="event_price" min="0" step="0.01" value="0" placeholder="0.00" required />
                                    </div>
                                    <div class="nymia-form-group" id="nymia-schedule-max-attendees-wrapper" style="display: none;">
                                        <label for="nymia-schedule-max-attendees"><?php esc_html_e('Maximum Attendees', 'nymia'); ?></label>
                                        <input type="number" id="nymia-schedule-max-attendees" name="max_attendees" min="2" value="10" placeholder="10" />
                                    </div>
                                </div>
                                <div class="nymia-schedule-inline" style="margin-top: 18px;">
                                    <button type="submit" class="nymia-btn-continue" style="flex: none; padding: 12px 24px;">
                                        <?php esc_html_e('Add Schedule', 'nymia'); ?>
                                    </button>
                                    <div id="nymiaScheduleMessage" class="nymia-schedule-message" role="status" aria-live="polite"></div>
                                </div>
                            </form>
                        </div>

                        <div class="nymia-schedule-card">
                            <h3><?php esc_html_e('Upcoming schedules', 'nymia'); ?></h3>
                            <p class="nymia-schedule-helper">
                                <?php esc_html_e('You can start early or remove a schedule at any time.', 'nymia'); ?>
                            </p>
                            <div class="nymia-schedule-list" id="nymiaScheduleList">
                                <?php if (!empty($live_schedules)) : ?>
                                    <?php foreach ($live_schedules as $schedule) :
                                        if (empty($schedule['start_timestamp'])) {
                                            continue;
                                        }
                                        $formatted_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $schedule['start_timestamp']);
                                        $duration_label = sprintf(
                                            _n('%d minute planned', '%d minutes planned', max(1, intval($schedule['duration'] ?? 60)), 'nymia'),
                                            max(1, intval($schedule['duration'] ?? 60))
                                        );
                                    ?>
                                        <div class="nymia-schedule-row" data-schedule-id="<?php echo esc_attr($schedule['id']); ?>" data-schedule-start="<?php echo esc_attr($schedule['start_timestamp']); ?>">
                                            <div>
                                                <h4><?php echo esc_html($schedule['title']); ?></h4>
                                                <p><?php echo esc_html($formatted_date . ' • ' . $duration_label); ?></p>
                                            </div>
                                            <div class="nymia-schedule-actions">
                                                <button type="button" class="nymia-schedule-btn primary" data-schedule-start-btn="<?php echo esc_attr($schedule['id']); ?>">
                                                    <?php esc_html_e('Start now', 'nymia'); ?>
                                                </button>
                                                <button type="button" class="nymia-schedule-btn danger" data-schedule-delete="<?php echo esc_attr($schedule['id']); ?>">
                                                    <?php esc_html_e('Delete', 'nymia'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="nymia-schedule-empty" data-schedule-empty>
                                        <?php esc_html_e('No scheduled sessions yet. Add your first schedule to automate your next live event.', 'nymia'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <div class="nymia-schedule-card nymia-private-sessions-card" id="nymiaPrivateSessions">
                        <div class="nymia-section-header" style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
                            <div>
                                <h3><?php esc_html_e('Pre-booked Private Sessions (1:1)', 'nymia'); ?></h3>
                                <p class="nymia-schedule-helper" style="margin-top:4px;"><?php esc_html_e('Define fixed-price availability slots that customers can pre-book from your public profile.', 'nymia'); ?></p>
                            </div>
                            <?php
                            $event_calendar_page = get_page_by_path('event-calendar');
                            $event_calendar_link = $event_calendar_page ? get_permalink($event_calendar_page) : home_url('/event-calendar/');
                            ?>
                            <a href="<?php echo esc_url($event_calendar_link); ?>" class="nymia-btn-outline" style="padding:10px 18px;border-radius:999px;white-space:nowrap;">
                                <?php esc_html_e('View Public Calendar', 'nymia'); ?>
                            </a>
                        </div>

                        <div class="nymia-private-sessions-grid">
                            <div class="nymia-private-card">
                                <h4><?php esc_html_e('Session Pricing & Defaults', 'nymia'); ?></h4>
                                <form id="nymiaPrivateSettingsForm" class="nymia-private-form">
                                    <label>
                                        <?php esc_html_e('Fixed price per session', 'nymia'); ?>
                                        <input type="number" step="0.01" min="0" name="price" placeholder="<?php esc_attr_e('e.g. 120', 'nymia'); ?>" required />
                                    </label>
                                    <label>
                                        <?php esc_html_e('Default reminder (minutes before start)', 'nymia'); ?>
                                        <select name="reminder_default">
                                            <option value="5">5</option>
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="60">60</option>
                                            <option value="120">120</option>
                                        </select>
                                    </label>
                                    <div class="nymia-private-actions">
                                        <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Save Settings', 'nymia'); ?></button>
                                        <p class="nymia-private-status" data-private-status></p>
                                    </div>
                                </form>
                            </div>

                            <div class="nymia-private-card">
                                <h4><?php esc_html_e('Quick Slot', 'nymia'); ?></h4>
                                <form id="nymiaPrivateSingleForm" class="nymia-private-form">
                                    <input type="hidden" name="mode" value="single" />
                                    <label>
                                        <?php esc_html_e('Date', 'nymia'); ?>
                                        <input type="date" name="date" required />
                                    </label>
                                    <label>
                                        <?php esc_html_e('Start time', 'nymia'); ?>
                                        <input type="time" name="time" required />
                                    </label>
                                    <label>
                                        <?php esc_html_e('Duration (minutes)', 'nymia'); ?>
                                        <input type="number" name="duration" min="15" max="240" step="15" value="60" required />
                                    </label>
                                    <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Add Slot', 'nymia'); ?></button>
                                </form>
                            </div>

                            <div class="nymia-private-card">
                                <h4><?php esc_html_e('Recurring Slots', 'nymia'); ?></h4>
                                <form id="nymiaPrivateRecurringForm" class="nymia-private-form">
                                    <input type="hidden" name="mode" value="recurring" />
                                    <label>
                                        <?php esc_html_e('Weekdays', 'nymia'); ?>
                                        <div class="nymia-weekday-grid">
                                            <?php
                                            $weekdays = array(
                                                'mon' => __('Mon', 'nymia'),
                                                'tue' => __('Tue', 'nymia'),
                                                'wed' => __('Wed', 'nymia'),
                                                'thu' => __('Thu', 'nymia'),
                                                'fri' => __('Fri', 'nymia'),
                                                'sat' => __('Sat', 'nymia'),
                                                'sun' => __('Sun', 'nymia'),
                                            );
                                            foreach ($weekdays as $key => $label) :
                                            ?>
                                            <label class="nymia-weekday">
                                                <input type="checkbox" name="weekdays[]" value="<?php echo esc_attr($key); ?>" />
                                                <span><?php echo esc_html($label); ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </label>
                                    <div class="nymia-recurring-grid">
                                        <label>
                                            <?php esc_html_e('From date', 'nymia'); ?>
                                            <input type="date" name="start_date" required />
                                        </label>
                                        <label>
                                            <?php esc_html_e('To date', 'nymia'); ?>
                                            <input type="date" name="end_date" required />
                                        </label>
                                        <label>
                                            <?php esc_html_e('Daily start', 'nymia'); ?>
                                            <input type="time" name="time_start" required />
                                        </label>
                                        <label>
                                            <?php esc_html_e('Daily end', 'nymia'); ?>
                                            <input type="time" name="time_end" required />
                                        </label>
                                    </div>
                                    <div class="nymia-recurring-grid">
                                        <label>
                                            <?php esc_html_e('Slot duration (minutes)', 'nymia'); ?>
                                            <input type="number" name="duration" min="15" max="240" step="15" value="60" required />
                                        </label>
                                        <label>
                                            <?php esc_html_e('Gap between slots (minutes)', 'nymia'); ?>
                                            <input type="number" name="interval" min="15" max="240" step="15" value="60" />
                                        </label>
                                    </div>
                                    <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Generate Slots', 'nymia'); ?></button>
                                </form>
                            </div>
                        </div>

                        <div class="nymia-private-card" style="margin-top:24px;">
                            <div class="nymia-private-list-header">
                                <div>
                                    <h4><?php esc_html_e('Upcoming Slots', 'nymia'); ?></h4>
                                    <p class="nymia-schedule-helper" style="margin-top:4px;"><?php esc_html_e('Booked sessions are blocked automatically once a customer pays.', 'nymia'); ?></p>
                                </div>
                                <button type="button" class="nymia-btn-outline" id="nymiaPrivateRefresh"><?php esc_html_e('Refresh', 'nymia'); ?></button>
                            </div>
                            <div id="nymiaPrivateSlots" class="nymia-private-slots"></div>
                        </div>
                    </div>
                    </div>

                    <!-- Discover Live Streams -->
                    <div class="nymia-discover-streams">
                        <h2><?php esc_html_e('Discover Live Streams', 'nymia'); ?></h2>
                        
                        <div class="nymia-live-streams-grid">
                            <?php
                            // Dummy live streams data
                            $live_streams = array(
                                array(
                                    'title' => 'Morning Motivation Session',
                                    'author' => 'Sarah Johnson',
                                    'viewers' => 123,
                                    'tips' => 50,
                                ),
                                array(
                                    'title' => 'Morning Motivation Session',
                                    'author' => 'Sarah Johnson',
                                    'viewers' => 123,
                                    'tips' => 50,
                                ),
                                array(
                                    'title' => 'Morning Motivation Session',
                                    'author' => 'Sarah Johnson',
                                    'viewers' => 123,
                                    'tips' => 50,
                                ),
                            );

                            foreach ($live_streams as $stream) :
                            ?>
                                <div class="nymia-live-stream-card">
                                    <div class="nymia-live-badge">
                                        <span class="nymia-live-dot"></span>
                                        <?php esc_html_e('Live', 'nymia'); ?>
                                    </div>
                                    <?php if (is_user_logged_in() && in_array('subscriber', (array) wp_get_current_user()->roles)) : ?>
                                    <button type="button" class="nymia-join-btn" data-stream-ref="<?php echo esc_attr($stream['title']); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>
                                        </svg>
                                        <?php esc_html_e('Join', 'nymia'); ?>
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="nymia-join-btn" disabled title="<?php esc_attr_e('Only subscribers can join live streams', 'nymia'); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>
                                        </svg>
                                        <?php esc_html_e('Join', 'nymia'); ?>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <div class="nymia-stream-info">
                                        <h3><?php echo esc_html($stream['title']); ?></h3>
                                        <p class="nymia-stream-author"><?php echo esc_html('by ' . $stream['author']); ?></p>
                                        
                                        <div class="nymia-stream-stats">
                                            <span class="nymia-stat-item">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="9" cy="7" r="4"></circle>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                </svg>
                                                <?php echo esc_html($stream['viewers']); ?>
                                            </span>
                                            <span class="nymia-stat-item">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                                </svg>
                                                <?php echo esc_html($stream['tips']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Audio Content -->
                <div class="nymia-create-content" id="content-audio">
                    <div class="nymia-create-header">
                        <h1><?php esc_html_e('Upload Audio Post', 'nymia'); ?></h1>
                        <p class="nymia-create-subtitle"><?php esc_html_e('Share your audio with your audience.', 'nymia'); ?></p>
                    </div>

                    <!-- Audio Control -->
                    <div class="nymia-stream-control">
                        <h2><?php esc_html_e('Audio Control', 'nymia'); ?></h2>
                        
                        <form id="audioUploadForm" class="nymia-stream-form">
                            <div class="nymia-form-group">
                                <label for="audio_title"><?php esc_html_e('Audio Title', 'nymia'); ?></label>
                                <input type="text" id="audio_title" name="audio_title" placeholder="<?php esc_attr_e('Enter Audio Title', 'nymia'); ?>" required />
                            </div>

                            <div class="nymia-form-group">
                                <label for="audio_category"><?php esc_html_e('Category', 'nymia'); ?></label>
                                <select id="audio_category" name="audio_category" class="nymia-select">
                                    <option value=""><?php esc_html_e('Select Category', 'nymia'); ?></option>
                                    <?php 
                                    // Get dynamic categories from admin settings
                                    $audio_categories = function_exists('nymia_get_audio_categories') ? nymia_get_audio_categories() : array();
                                    
                                    // Get sub-categories
                                    $audio_subcategories = function_exists('nymia_get_audio_subcategories') ? nymia_get_audio_subcategories() : array();
                                    
                                    foreach ($audio_categories as $category): 
                                        // Check if this category has sub-categories
                                        $has_subcategories = isset($audio_subcategories[$category]) && !empty($audio_subcategories[$category]);
                                        
                                        // Show main category as selectable option
                                        ?>
                                        <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                                        <?php
                                        
                                        // Show sub-categories if they exist
                                        if ($has_subcategories) {
                                            foreach ($audio_subcategories[$category] as $subcat): ?>
                                                <option value="<?php echo esc_attr($category . '|' . $subcat); ?>" data-category="<?php echo esc_attr($category); ?>" data-subcategory="<?php echo esc_attr($subcat); ?>">
                                                    &nbsp;&nbsp;&nbsp;→ <?php echo esc_html($subcat); ?>
                                                </option>
                                            <?php endforeach;
                                        }
                                    endforeach; 
                                    ?>
                                </select>
                            </div>

                            <div class="nymia-form-group">
                                <label for="audio_language"><?php esc_html_e('Language', 'nymia'); ?></label>
                                <select id="audio_language" name="audio_language" class="nymia-select">
                                    <option value=""><?php esc_html_e('Select Language', 'nymia'); ?></option>
                                    <option value="English"><?php esc_html_e('English', 'nymia'); ?></option>
                                    <option value="Spanish"><?php esc_html_e('Spanish', 'nymia'); ?></option>
                                    <option value="French"><?php esc_html_e('French', 'nymia'); ?></option>
                                    <option value="German"><?php esc_html_e('German', 'nymia'); ?></option>
                                    <option value="Italian"><?php esc_html_e('Italian', 'nymia'); ?></option>
                                    <option value="Portuguese"><?php esc_html_e('Portuguese', 'nymia'); ?></option>
                                    <option value="Chinese"><?php esc_html_e('Chinese', 'nymia'); ?></option>
                                    <option value="Japanese"><?php esc_html_e('Japanese', 'nymia'); ?></option>
                                    <option value="Korean"><?php esc_html_e('Korean', 'nymia'); ?></option>
                                    <option value="Arabic"><?php esc_html_e('Arabic', 'nymia'); ?></option>
                                    <option value="Hindi"><?php esc_html_e('Hindi', 'nymia'); ?></option>
                                    <option value="Russian"><?php esc_html_e('Russian', 'nymia'); ?></option>
                                    <option value="Dutch"><?php esc_html_e('Dutch', 'nymia'); ?></option>
                                    <option value="Polish"><?php esc_html_e('Polish', 'nymia'); ?></option>
                                    <option value="Turkish"><?php esc_html_e('Turkish', 'nymia'); ?></option>
                                </select>
                            </div>

                            <!-- Audio Cover Image Upload -->
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Cover Image', 'nymia'); ?> <span style="color: #BF4C1A;">(<?php esc_html_e('Optional', 'nymia'); ?>)</span></label>
                                <div class="audio-cover-upload">
                                    <input type="file" id="audio_cover_image" name="audio_cover_image" accept="image/*" hidden />
                                    <button type="button" id="audioCoverImageBtn" class="audio-cover-upload-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                        <span><?php esc_html_e('Upload Cover Image', 'nymia'); ?></span>
                                    </button>
                                    
                                    <div id="audioCoverImagePreview" class="audio-cover-preview" style="display: none;">
                                        <button type="button" id="audioCoverImageRemove" class="audio-cover-remove" title="<?php esc_attr_e('Remove cover image', 'nymia'); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                        <img id="audioCoverImageImg" src="" alt="<?php esc_attr_e('Cover image preview', 'nymia'); ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="nymia-stream-options">
                                <div class="nymia-paid-access">
                                    <label class="nymia-toggle-wrapper">
                                        <input type="checkbox" id="audio_paid_access" name="audio_paid_access" />
                                        <span class="nymia-toggle-slider"></span>
                                    </label>
                                    <span class="nymia-toggle-label"><?php esc_html_e('Paid Access', 'nymia'); ?></span>
                                    <div class="nymia-price-input">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="16"></line>
                                            <line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>
                                        <input type="number" id="audio_price" name="audio_price" value="0.00" step="0.01" min="0" disabled />
                                    </div>
                                </div>
                            </div>

                            <!-- Audio Upload Mode Tabs -->
                            <div class="nymia-upload-mode-tabs">
                                <button type="button" class="nymia-upload-mode-btn active" data-mode="upload">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span><?php esc_html_e('Upload File', 'nymia'); ?></span>
                                </button>
                                <button type="button" class="nymia-upload-mode-btn" data-mode="record">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span><?php esc_html_e('Record Audio', 'nymia'); ?></span>
                                </button>
                            </div>
                            
                            <!-- File Upload Area -->
                            <div class="nymia-upload-area active" id="audioUploadArea">
                                <input type="file" id="audioFileInput" name="audio_file" accept="audio/*" hidden />
                                <div class="nymia-upload-content">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <p class="nymia-upload-text"><?php esc_html_e('Drop file here or click to browse', 'nymia'); ?></p>
                                    <p class="nymia-upload-formats"><?php esc_html_e('Supported formats: MP3, WAV, OGG, M4A, and more', 'nymia'); ?></p>
                                </div>
                                <div class="nymia-upload-progress" style="display: none;">
                                    <div class="nymia-progress-bar">
                                        <div class="nymia-progress-fill"></div>
                                    </div>
                                    <span class="nymia-progress-text">0%</span>
                                </div>
                            </div>
                            
                            <!-- Audio Recording Area -->
                            <div class="nymia-recording-area" id="audioRecordingArea" style="display: none;">
                                <div class="nymia-recording-controls">
                                    <button type="button" class="nymia-btn-record" id="recordBtn">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span><?php esc_html_e('Start Recording', 'nymia'); ?></span>
                                    </button>
                                    
                                    <button type="button" class="nymia-btn-stop" id="stopBtn" style="display: none;">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <rect x="6" y="6" width="12" height="12"></rect>
                                        </svg>
                                        <span><?php esc_html_e('Stop Recording', 'nymia'); ?></span>
                                    </button>
                                    
                                    <button type="button" class="nymia-btn-pause" id="pauseBtn" style="display: none;">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <rect x="6" y="4" width="4" height="16"></rect>
                                            <rect x="14" y="4" width="4" height="16"></rect>
                                        </svg>
                                        <span><?php esc_html_e('Pause', 'nymia'); ?></span>
                                    </button>
                                </div>
                                
                                <div class="nymia-recording-status">
                                    <div class="nymia-recording-indicator" id="recordingIndicator" style="display: none;">
                                        <span class="nymia-recording-dot"></span>
                                        <span><?php esc_html_e('Recording...', 'nymia'); ?></span>
                                    </div>
                                    <div class="nymia-recording-timer" id="recordingTimer">00:00</div>
                                </div>
                                
                                <div class="nymia-recording-visualizer">
                                    <canvas id="audioVisualizer" width="400" height="100"></canvas>
                                </div>
                                
                                <div class="nymia-recording-preview" id="recordingPreview" style="display: none;">
                                    <audio id="recordedAudio" controls></audio>
                                    <button type="button" class="nymia-btn-re-record"><?php esc_html_e('Record Again', 'nymia'); ?></button>
                                </div>
                            </div>

                            <button type="submit" class="nymia-btn-continue">
                                <?php esc_html_e('Post', 'nymia'); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Ebook Content -->
                <div class="nymia-create-content" id="content-ebook">
                    <div class="nymia-create-header">
                        <h1><?php esc_html_e('Upload Ebook', 'nymia'); ?></h1>
                        <p class="nymia-create-subtitle"><?php esc_html_e('Share your ebooks with your audience.', 'nymia'); ?></p>
                    </div>
                    
                    <!-- Success Message -->
                    <div id="ebook-success-message" class="nymia-success-message" style="display: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Ebook uploaded successfully!</span>
                    </div>

                    <!-- Ebook Upload Control -->
                    <div class="nymia-stream-control">
                        <h2><?php esc_html_e('Ebook Upload', 'nymia'); ?></h2>
                        
                        <form id="ebookUploadForm" class="nymia-stream-form">
                            <?php wp_nonce_field('nymia_ebook_subcategory_action', 'nymia_ebook_subcategory_nonce'); ?>
                            <input type="hidden" id="nymia_ebook_subcategory_nonce" name="nymia_ebook_subcategory_nonce" value="<?php echo wp_create_nonce('nymia_ebook_subcategory_action'); ?>" />
                            <div class="nymia-form-group">
                                <label for="ebook_title"><?php esc_html_e('Ebook Title', 'nymia'); ?></label>
                                <input type="text" id="ebook_title" name="ebook_title" placeholder="<?php esc_attr_e('Enter Ebook Title', 'nymia'); ?>" required />
                            </div>

                            <div class="nymia-form-group">
                                <label for="ebook_description"><?php esc_html_e('Description (Optional)', 'nymia'); ?></label>
                                <textarea id="ebook_description" name="ebook_description" rows="3" placeholder="<?php esc_attr_e('Enter ebook description', 'nymia'); ?>"></textarea>
                            </div>

                            <div class="nymia-form-group">
                                <label for="ebook_category"><?php esc_html_e('Category', 'nymia'); ?></label>
                                <select id="ebook_category" name="ebook_category" class="nymia-select" required>
                                    <option value=""><?php esc_html_e('Select Category', 'nymia'); ?></option>
                                    <?php 
                                    // Get dynamic categories from admin settings (ebook specific)
                                    if (!function_exists('nymia_get_ebook_categories') && function_exists('nymia_get_audio_categories')) {
                                        $ebook_categories = nymia_get_audio_categories();
                                    } else {
                                        $ebook_categories = function_exists('nymia_get_ebook_categories') ? nymia_get_ebook_categories() : array();
                                    }
                                    
                                    // Get sub-categories
                                    $ebook_subcategories = function_exists('nymia_get_ebook_subcategories') ? nymia_get_ebook_subcategories() : array();
                                    
                                    foreach ($ebook_categories as $category): 
                                        // Check if this category has sub-categories
                                        $has_subcategories = isset($ebook_subcategories[$category]) && !empty($ebook_subcategories[$category]);
                                        
                                        // Show main category as selectable option
                                        ?>
                                        <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                                        <?php
                                        
                                        // Show sub-categories if they exist
                                        if ($has_subcategories) {
                                            foreach ($ebook_subcategories[$category] as $subcat): ?>
                                                <option value="<?php echo esc_attr($category . '|' . $subcat); ?>" data-category="<?php echo esc_attr($category); ?>" data-subcategory="<?php echo esc_attr($subcat); ?>">
                                                    &nbsp;&nbsp;&nbsp;→ <?php echo esc_html($subcat); ?>
                                                </option>
                                            <?php endforeach;
                                        }
                                    endforeach; 
                                    ?>
                                </select>
                            </div>

                            <div class="nymia-form-group">
                                <label for="ebook_language"><?php esc_html_e('Language', 'nymia'); ?></label>
                                <select id="ebook_language" name="ebook_language" class="nymia-select" required>
                                    <option value=""><?php esc_html_e('Select Language', 'nymia'); ?></option>
                                    <?php
                                    if (function_exists('nymia_get_ebook_languages')) {
                                        $ebook_languages = nymia_get_ebook_languages();
                                    } else {
                                        $ebook_languages = array('English', 'Spanish', 'French', 'German', 'Portuguese');
                                    }
                                    
                                    foreach ($ebook_languages as $language):
                                    ?>
                                        <option value="<?php echo esc_attr($language); ?>"><?php echo esc_html($language); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="nymia-stream-options">
                                <div class="nymia-paid-access">
                                    <label class="nymia-toggle-wrapper">
                                        <input type="checkbox" id="ebook_paid_access" name="ebook_paid_access" />
                                        <span class="nymia-toggle-slider"></span>
                                    </label>
                                    <span class="nymia-toggle-label"><?php esc_html_e('Paid Access', 'nymia'); ?></span>
                                    <div class="nymia-price-input">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="16"></line>
                                            <line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>
                                        <input type="number" id="ebook_price" name="ebook_price" value="0.00" step="0.01" min="0" disabled />
                                    </div>
                                </div>
                            </div>

                            <!-- Ebook File Upload Area -->
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Ebook File', 'nymia'); ?></label>
                                <div id="ebookUploadArea" class="nymia-upload-area">
                                    <input type="file" id="ebookFileInput" name="ebook_file" accept=".pdf,.epub,.mobi,.txt" hidden />
                                    <div class="nymia-upload-content">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <p class="nymia-upload-text"><?php esc_html_e('Drop ebook file here or click to browse', 'nymia'); ?></p>
                                        <p class="nymia-upload-formats"><?php esc_html_e('Supported formats: PDF, EPUB, MOBI, TXT', 'nymia'); ?></p>
                                    </div>
                                    <div class="ebook-upload-progress" style="display: none;">
                                        <div class="ebook-progress-bar">
                                            <div class="ebook-progress-fill"></div>
                                        </div>
                                        <span class="ebook-progress-text">0%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ebook Thumbnail Upload -->
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Thumbnail Image', 'nymia'); ?> <span style="color: #BF4C1A;">*</span></label>
                                <div class="ebook-thumbnail-upload">
                                    <input type="file" id="ebook_thumbnail" name="ebook_thumbnail" accept="image/*" hidden />
                                    <button type="button" id="ebookThumbnailBtn" class="ebook-thumbnail-upload-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                        <span><?php esc_html_e('Upload Thumbnail', 'nymia'); ?></span>
                                    </button>
                                    
                                    <div id="ebookThumbnailPreview" class="ebook-thumbnail-preview">
                                        <button type="button" id="ebookThumbnailRemove" class="ebook-thumbnail-remove" title="Remove thumbnail">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                        <img id="ebookThumbnailImg" src="" alt="Thumbnail preview" />
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="nymia-btn-continue">
                                <?php esc_html_e('Upload Ebook', 'nymia'); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Audio Book Content -->
                <div class="nymia-create-content" id="content-audio-book">
                    <div class="nymia-create-header">
                        <h1><?php esc_html_e('Upload Audio Book', 'nymia'); ?></h1>
                        <p class="nymia-create-subtitle"><?php esc_html_e('Share your audio books with your audience.', 'nymia'); ?></p>
                    </div>
                    
                    <!-- Success Message -->
                    <div id="audiobook-success-message" class="nymia-success-message" style="display: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span><?php esc_html_e('Audio book uploaded successfully!', 'nymia'); ?></span>
                    </div>

                    <!-- Audio Book Upload Control -->
                    <div class="nymia-stream-control">
                        <h2><?php esc_html_e('Audio Book Upload', 'nymia'); ?></h2>
                        
                        <form id="audiobookUploadForm" class="nymia-stream-form">
                            <?php wp_nonce_field('nymia_audiobook_upload', 'nymia_audiobook_nonce'); ?>
                            <input type="hidden" id="nymia_audiobook_nonce" name="nymia_audiobook_nonce" value="<?php echo wp_create_nonce('nymia_audiobook_upload'); ?>" />
                            
                            <div class="nymia-form-group">
                                <label for="audiobook_title"><?php esc_html_e('Audio Book Title', 'nymia'); ?></label>
                                <input type="text" id="audiobook_title" name="audiobook_title" placeholder="<?php esc_attr_e('Enter Audio Book Title', 'nymia'); ?>" required />
                            </div>

                            <div class="nymia-form-group">
                                <label for="audiobook_description"><?php esc_html_e('Description (Optional)', 'nymia'); ?></label>
                                <textarea id="audiobook_description" name="audiobook_description" rows="3" placeholder="<?php esc_attr_e('Enter audio book description', 'nymia'); ?>"></textarea>
                            </div>

                            <div class="nymia-form-group">
                                <label for="audiobook_category"><?php esc_html_e('Category', 'nymia'); ?></label>
                                <select id="audiobook_category" name="audiobook_category" class="nymia-select" required>
                                    <option value=""><?php esc_html_e('Select Category', 'nymia'); ?></option>
                                    <?php 
                                    // Get dynamic categories from admin settings
                                    $audiobook_categories = function_exists('nymia_get_audio_categories') ? nymia_get_audio_categories() : array();
                                    
                                    foreach ($audiobook_categories as $category): ?>
                                        <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="nymia-form-group">
                                <label for="audiobook_language"><?php esc_html_e('Language', 'nymia'); ?></label>
                                <select id="audiobook_language" name="audiobook_language" class="nymia-select" required>
                                    <option value=""><?php esc_html_e('Select Language', 'nymia'); ?></option>
                                    <?php
                                    $audiobook_languages = array('English', 'Spanish', 'French', 'German', 'Portuguese', 'Italian', 'Japanese', 'Chinese', 'Korean', 'Arabic');
                                    foreach ($audiobook_languages as $language):
                                    ?>
                                        <option value="<?php echo esc_attr($language); ?>"><?php echo esc_html($language); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="nymia-stream-options">
                                <div class="nymia-paid-access">
                                    <label class="nymia-toggle-wrapper">
                                        <input type="checkbox" id="audiobook_paid_access" name="audiobook_paid_access" />
                                        <span class="nymia-toggle-slider"></span>
                                    </label>
                                    <span class="nymia-toggle-label"><?php esc_html_e('Paid Access', 'nymia'); ?></span>
                                    <div class="nymia-price-input">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="16"></line>
                                            <line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>
                                        <input type="number" id="audiobook_price" name="audiobook_price" value="0.00" step="0.01" min="0" disabled />
                                    </div>
                                </div>
                            </div>

                            <!-- Audio Book File Upload Area -->
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Audio Book File', 'nymia'); ?></label>
                                <div id="audiobookUploadArea" class="nymia-upload-area">
                                    <input type="file" id="audiobookFileInput" name="audiobook_file" accept="audio/*" hidden />
                                    <div class="nymia-upload-content">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <p class="nymia-upload-text"><?php esc_html_e('Drop audio book file here or click to browse', 'nymia'); ?></p>
                                        <p class="nymia-upload-formats"><?php esc_html_e('Supported formats: MP3, WAV, M4A, OGG', 'nymia'); ?></p>
                                    </div>
                                    <div class="audiobook-upload-progress" style="display: none;">
                                        <div class="audiobook-progress-bar">
                                            <div class="audiobook-progress-fill"></div>
                                        </div>
                                        <span class="audiobook-progress-text">0%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Audio Book Thumbnail Upload -->
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Thumbnail Image', 'nymia'); ?> <span style="color: #BF4C1A;">*</span></label>
                                <div class="audiobook-thumbnail-upload">
                                    <input type="file" id="audiobook_thumbnail" name="audiobook_thumbnail" accept="image/*" hidden />
                                    <button type="button" id="audiobookThumbnailBtn" class="audiobook-thumbnail-upload-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                        <span><?php esc_html_e('Upload Thumbnail', 'nymia'); ?></span>
                                    </button>
                                    
                                    <div id="audiobookThumbnailPreview" class="audiobook-thumbnail-preview">
                                        <button type="button" id="audiobookThumbnailRemove" class="audiobook-thumbnail-remove" title="Remove thumbnail">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                        <img id="audiobookThumbnailImg" src="" alt="Thumbnail preview" />
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="nymia-btn-continue">
                                <?php esc_html_e('Upload Audio Book', 'nymia'); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Text Content -->
                <div class="nymia-create-content" id="content-text">
                    <div class="nymia-create-header">
                        <h1><?php esc_html_e('Create Post', 'nymia'); ?></h1>
                        <p class="nymia-create-subtitle"><?php esc_html_e('Share your thoughts with your community', 'nymia'); ?></p>
                    </div>
                    <div class="nymia-text-post-card">
                        <?php if (is_user_logged_in()):
                            $current_user = wp_get_current_user();
                            $current_avatar = get_template_directory_uri() . '/assets/images/profile.png';
                            if ($current_user && $current_user->ID) {
                                $custom_avatar = get_user_meta($current_user->ID, 'custom_avatar', true);
                                $current_avatar = $custom_avatar ?: get_avatar_url($current_user->ID, array('size' => 96));
                            }
                        ?>
                        <form id="nymiaTextPostForm" enctype="multipart/form-data">
                            <?php wp_nonce_field('nymia_text_post', 'nymia_text_post_nonce'); ?>
                            <div class="nymia-text-post-user">
                                <img src="<?php echo esc_url($current_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name ?: $current_user->user_login); ?>">
                                <div>
                                    <h4><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></h4>
                                    <p><?php esc_html_e('Share something with your followers', 'nymia'); ?></p>
                                </div>
                            </div>
                            <div class="nymia-form-group">
                                <label for="nymiaTextPostTitle"><?php esc_html_e('Post Title', 'nymia'); ?></label>
                                <input type="text" id="nymiaTextPostTitle" name="title" placeholder="<?php esc_attr_e('Enter a catchy title', 'nymia'); ?>" required />
                            </div>
                            <div class="nymia-form-group">
                                <label for="nymiaTextPostMessage"><?php esc_html_e('Description', 'nymia'); ?></label>
                                <textarea id="nymiaTextPostMessage" name="description" rows="4" maxlength="2000" placeholder="<?php esc_attr_e('Share your thoughts...', 'nymia'); ?>" required></textarea>
                                <span id="nymiaTextPostCharCount">0/2000</span>
                            </div>
                            <div class="nymia-form-group">
                                <label><?php esc_html_e('Image (optional)', 'nymia'); ?></label>
                                <div class="nymia-text-image-upload">
                                    <input type="file" id="nymiaTextPostImage" name="image" accept="image/*" hidden />
                                    <button type="button" id="nymiaTextPostImageBtn" class="nymia-btn-outline"><?php esc_html_e('Upload Image', 'nymia'); ?></button>
                                    <div class="nymia-text-image-preview" id="nymiaTextPostImagePreview" style="display:none;">
                                        <img src="" alt="<?php esc_attr_e('Preview', 'nymia'); ?>">
                                        <button type="button" id="nymiaTextPostImageRemove" aria-label="<?php esc_attr_e('Remove image', 'nymia'); ?>">×</button>
                                    </div>
                                </div>
                            </div>
                            <div class="nymia-text-post-actions">
                                <button type="submit" class="nymia-btn-gradient" id="nymiaTextPostSubmit">
                                    <?php esc_html_e('Share Post', 'nymia'); ?>
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                            <p class="nymia-text-post-login">
                                <?php esc_html_e('Please log in to share updates with the community.', 'nymia'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Dynamic Content -->
            <aside class="nymia-create-sidebar">
                <!-- Stream History (for Go Live tab) -->
                <div class="nymia-sidebar-content live-chat-content active">
                    <div class="nymia-live-chat-header">
                        <h3><?php esc_html_e('Stream History', 'nymia'); ?></h3>
                    </div>
                    
                    <!-- Stream History -->
                    <div class="nymia-stream-history">
                        <div class="nymia-stream-history-list" id="nymiaStreamHistoryList">
                            <div class="nymia-history-empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <p><?php esc_html_e('No streams yet', 'nymia'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Audio Posts (for Audio tab) -->
                <div class="nymia-sidebar-content audio-posts-content">
                    <div class="nymia-sidebar-header">
                        <h3><?php esc_html_e('Recent Audio Posts', 'nymia'); ?></h3>
                    </div>
                    
                    <div class="nymia-recent-audio-list">
                        <?php
                        // Get user's uploaded audio posts
                        $user_audio_posts = nymia_get_user_audio_posts();
                        
                        // If no user uploads, show demo data
                        if (empty($user_audio_posts)) {
                            $recent_audio = array(
                                array(
                                    'title' => 'Morning Motivation Session',
                                    'author' => 'Sarah Johnson',
                                    'duration' => '12:34',
                                    'category' => 'Lifestyle',
                                    'views' => 123,
                                    'rating' => 4.5,
                                    'url' => '',
                                    'paid_access' => 'no',
                                    'price' => 0,
                                ),
                                array(
                                    'title' => 'Fitness Routine Guide',
                                    'author' => 'Mike Davis',
                                    'duration' => '15:20',
                                    'category' => 'Sports',
                                    'views' => 89,
                                    'rating' => 4.2,
                                    'url' => '',
                                    'paid_access' => 'no',
                                    'price' => 0,
                                ),
                                array(
                                    'title' => 'Meditation for Beginners',
                                    'author' => 'Emma Wilson',
                                    'duration' => '20:45',
                                    'category' => 'Lifestyle',
                                    'views' => 156,
                                    'rating' => 4.8,
                                    'url' => '',
                                    'paid_access' => 'no',
                                    'price' => 0,
                                ),
                            );
                        } else {
                            $recent_audio = $user_audio_posts;
                        }

                        foreach ($recent_audio as $audio) :
                            $audio_id = isset($audio['id']) ? intval($audio['id']) : 0;
                            $is_owner = ($current_user_id > 0 && isset($audio['user_id']) && intval($audio['user_id']) === $current_user_id);
                        ?>
                            <div class="nymia-audio-post-card-compact" data-audio-url="<?php echo esc_attr($audio['url']); ?>" data-paid="<?php echo esc_attr(isset($audio['paid_access']) ? $audio['paid_access'] : 'no'); ?>" data-price="<?php echo esc_attr(isset($audio['price']) ? $audio['price'] : 0); ?>" data-creator-id="<?php echo esc_attr(get_current_user_id()); ?>" data-title="<?php echo esc_attr($audio['title']); ?>" data-audio-id="<?php echo esc_attr($audio_id); ?>" style="position: relative;">
                                <div class="nymia-compact-audio-cover" style="position: relative;">
                                    <?php if (!empty($audio['cover_image'])): ?>
                                        <img src="<?php echo esc_url($audio['cover_image']); ?>" alt="<?php echo esc_attr($audio['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 18V5l12-2v13"></path>
                                            <circle cx="6" cy="18" r="3"></circle>
                                            <circle cx="18" cy="16" r="3"></circle>
                                        </svg>
                                    <?php endif; ?>
                                    <button class="nymia-compact-play-btn">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </button>
                                    <?php if ($is_owner && $audio_id > 0): ?>
                                        <button class="nymia-compact-edit-btn" data-audio-id="<?php echo esc_attr($audio_id); ?>" onclick="event.stopPropagation(); openEditAudioModal(<?php echo esc_attr($audio_id); ?>);" title="<?php esc_attr_e('Edit Audio', 'nymia'); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="nymia-compact-audio-info">
                                    <h4 class="nymia-compact-title"><?php echo esc_html($audio['title']); ?></h4>
                                    <p class="nymia-compact-author"><?php echo esc_html($audio['author']); ?></p>
                                    
                                    <div class="nymia-compact-meta">
                                        <span class="nymia-compact-duration">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                            <?php echo esc_html($audio['duration']); ?>
                                        </span>
                                        <span class="nymia-compact-category"><?php echo esc_html($audio['category']); ?></span>
                                    </div>
                                    
                                    <div class="nymia-compact-stats">
                                        <div class="nymia-compact-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg class="nymia-star <?php echo $i <= floor($audio['rating']) ? 'filled' : ''; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                            <?php endfor; ?>
                                            <span class="nymia-rating-num"><?php echo $audio['rating']; ?></span>
                                        </div>
                                        <div class="nymia-compact-views">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                            </svg>
                                            <?php echo esc_html($audio['views']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="nymia-view-all-btn">
                        <?php esc_html_e('View All', 'nymia'); ?>
                    </button>
                </div>

                <!-- Ebook Posts (for Ebook tab) -->
                <div class="nymia-sidebar-content ebook-posts-content">
                    <div class="nymia-sidebar-header">
                        <h3><?php esc_html_e('Recent Ebooks', 'nymia'); ?></h3>
                    </div>
                    
                    <div class="nymia-recent-ebook-list">
                        <?php
                        // Get user's ebook posts
                        $user_ebook_posts = nymia_get_user_ebook_posts();
                        
                        if (empty($user_ebook_posts)) {
                        ?>
                            <div class="ebook-post-card-compact">
                                <div class="ebook-cover">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                </div>
                                <div class="ebook-title">No ebooks yet</div>
                                <div class="ebook-meta">Upload your first ebook to get started</div>
                            </div>
                        <?php
                        } else {
                            foreach ($user_ebook_posts as $ebook):
                                $ebook_id = isset($ebook['id']) ? $ebook['id'] : '';
                                $is_owner = ($current_user_id > 0 && isset($ebook['user_id']) && intval($ebook['user_id']) === $current_user_id);
                        ?>
                            <div class="ebook-post-card-compact" data-ebook-id="<?php echo esc_attr($ebook_id); ?>" style="position: relative;">
                                <?php if ($is_owner && !empty($ebook_id)): ?>
                                    <button class="nymia-compact-edit-btn" data-ebook-id="<?php echo esc_attr($ebook_id); ?>" onclick="event.stopPropagation(); openEditEbookModal('<?php echo esc_js($ebook_id); ?>');" title="<?php esc_attr_e('Edit Ebook', 'nymia'); ?>" style="position: absolute; top: 8px; right: 8px; background: rgba(191, 76, 26, 0.9); border: none; color: white; padding: 6px; border-radius: 6px; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; width: 28px; height: 28px;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($ebook['thumbnail'])): ?>
                                    <img src="<?php echo esc_url($ebook['thumbnail']); ?>" alt="<?php echo esc_attr($ebook['title']); ?>" style="width: 60px; height: 80px; object-fit: cover; border-radius: 6px; margin-bottom: 12px;" />
                                <?php else: ?>
                                    <div class="ebook-cover">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="ebook-title"><?php echo esc_html($ebook['title']); ?></div>
                                <div class="ebook-meta"><?php echo esc_html($ebook['format']); ?> • <?php echo esc_html($ebook['size']); ?></div>
                                <div class="ebook-stats">
                                    <span><?php echo esc_html($ebook['views']); ?> views</span>
                                    <span>•</span>
                                    <span><?php echo esc_html($ebook['downloads']); ?> downloads</span>
                                </div>
                            </div>
                        <?php
                            endforeach;
                        }
                        ?>
                    </div>
                </div>

                <!-- Text Posts (for Text tab) -->
                <div class="nymia-sidebar-content text-posts-content">
                    <div class="nymia-sidebar-header">
                        <h3><?php esc_html_e('Recent Posts', 'nymia'); ?></h3>
                    </div>
                    <div class="nymia-sidebar-text-list" id="nymiaTextSidebarList">
                        <?php if (!empty($text_posts)): ?>
                            <?php foreach ($text_posts as $post): 
                                $single_post_page = get_page_by_path('single-post');
                                $post_link = $single_post_page ? get_permalink($single_post_page) : home_url('/single-post/');
                                $post_link = add_query_arg('post_id', $post['id'], $post_link);
                            ?>
                                <a href="<?php echo esc_url($post_link); ?>" class="nymia-sidebar-text-item" data-post-id="<?php echo esc_attr($post['id']); ?>">
                                    <div class="nymia-sidebar-text-head">
                                        <img src="<?php echo esc_url($post['avatar']); ?>" alt="<?php echo esc_attr($post['author_name']); ?>">
                                        <div>
                                            <h4><?php echo esc_html($post['author_name']); ?></h4>
                                            <p><?php echo esc_html($post['time_ago']); ?></p>
                                        </div>
                                    </div>
                                    <h5 class="nymia-sidebar-text-title"><?php echo esc_html($post['title']); ?></h5>
                                    <p class="nymia-sidebar-text-body"><?php echo esc_html($post['excerpt']); ?></p>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="nymia-chat-empty">
                                <div class="nymia-chat-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                </div>
                                <p><?php esc_html_e('Your recent posts will appear here', 'nymia'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<style>
.nymia-text-post-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 24px;
}
.nymia-text-post-user {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 12px;
}
.nymia-text-post-user img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}
.nymia-text-post-card input[type="text"],
.nymia-text-post-card textarea {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.03);
    color: #fff;
    padding: 12px;
    margin-top: 6px;
}
.nymia-text-post-card textarea {
    min-height: 140px;
    resize: vertical;
}
.nymia-text-post-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
}
.nymia-text-post-feed {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.nymia-text-post-item {
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.05);
    padding: 18px;
    background: rgba(255,255,255,0.015);
}
.nymia-text-post-head {
    display: flex;
    gap: 12px;
    margin-bottom: 10px;
}
.nymia-text-post-head img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
}
.nymia-text-post-title {
    margin: 0 0 8px;
}
.nymia-text-post-image img {
    width: 100%;
    border-radius: 12px;
    margin-bottom: 12px;
}
.nymia-text-post-body {
    color: rgba(255,255,255,0.9);
    line-height: 1.6;
}
.nymia-text-post-empty,
.nymia-text-post-login {
    text-align: center;
    padding: 20px;
    border-radius: 12px;
    border: 1px dashed rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.6);
}
.nymia-text-image-upload {
    position: relative;
}
.nymia-text-image-preview {
    margin-top: 12px;
    position: relative;
}
.nymia-text-image-preview img {
    width: 100%;
    border-radius: 12px;
}
.nymia-text-image-preview button {
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(0,0,0,0.5);
    border: none;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
}
.nymia-sidebar-text-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.nymia-sidebar-text-item {
    display: block;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.02);
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    margin-bottom: 12px;
}
.nymia-sidebar-text-item:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.12);
    transform: translateY(-2px);
}
.nymia-sidebar-text-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.nymia-sidebar-text-head img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}
.nymia-sidebar-text-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #fff;
    margin: 8px 0 6px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.nymia-sidebar-text-body {
    margin: 0;
    color: rgba(255,255,255,0.7);
    font-size: 0.875rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.nymia-schedule-next-card {
    margin-top: 24px;
    padding: 20px;
    border-radius: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, rgba(199, 84, 26, 0.15), rgba(199, 84, 26, 0.05));
    border: 1px solid rgba(199, 84, 26, 0.25);
}

.nymia-schedule-label {
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
    margin: 0 0 6px;
    color: rgba(255,255,255,0.8);
}

.nymia-schedule-countdown-title {
    margin: 0;
    font-size: 1.2rem;
    color: #fff;
}

.nymia-schedule-date {
    margin: 4px 0 0;
    color: rgba(255,255,255,0.8);
}

.nymia-schedule-countdown {
    font-size: 2.2rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.08em;
    min-width: 160px;
    text-align: center;
}

.nymia-schedule-countdown-hint {
    margin: 6px 0 0;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.75);
}

.nymia-schedule-manager {
    margin-top: 24px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.nymia-schedule-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 20px;
}

/* Available Now Card */
.nymia-available-now-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 20px;
}

.nymia-available-now-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.nymia-available-now-subtitle {
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
    margin: 4px 0 0;
}

.nymia-available-toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nymia-available-toggle {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.nymia-available-toggle input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.nymia-available-slider {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    background: rgba(255,255,255,0.2);
    border-radius: 26px;
    transition: background 0.3s ease;
}

.nymia-available-slider:before {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.3s ease;
}

.nymia-available-toggle input[type="checkbox"]:checked + .nymia-available-slider {
    background: #22c55e;
}

.nymia-available-toggle input[type="checkbox"]:checked + .nymia-available-slider:before {
    transform: translateX(24px);
}

.nymia-available-status {
    font-size: 0.9rem;
    font-weight: 500;
    color: rgba(255,255,255,0.8);
    min-width: 30px;
}

.nymia-available-now-settings {
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.nymia-schedule-card h3 {
    margin-top: 0;
    margin-bottom: 8px;
    color: #fff;
}

.nymia-schedule-helper {
    margin: 0 0 16px;
    color: rgba(255,255,255,0.7);
    font-size: 0.95rem;
}

.nymia-schedule-form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.nymia-schedule-inline {
    display: flex;
    gap: 12px;
}

.nymia-schedule-inline {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.nymia-schedule-inline .nymia-form-group {
    flex: 1;
}

.nymia-schedule-message {
    font-size: 0.9rem;
    min-height: 18px;
    padding: 8px 12px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    font-weight: 500;
    transition: all 0.3s ease;
    opacity: 0;
    visibility: hidden;
    margin: 0;
    white-space: nowrap;
}

.nymia-schedule-message:not(:empty) {
    opacity: 1;
    visibility: visible;
}

.nymia-schedule-message.is-success {
    color: #23c68f;
    background-color: rgba(35, 198, 143, 0.1);
    border: 1px solid rgba(35, 198, 143, 0.3);
}

.nymia-schedule-message.is-error {
    color: #ff6b6b;
    background-color: rgba(255, 107, 107, 0.1);
    border: 1px solid rgba(255, 107, 107, 0.3);
}

.nymia-schedule-message.is-loading {
    color: rgba(255, 255, 255, 0.7);
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.nymia-schedule-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.nymia-schedule-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.01);
}

.nymia-schedule-row h4 {
    margin: 0 0 4px;
    font-size: 1rem;
    color: #fff;
}

.nymia-schedule-row p {
    margin: 0;
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
}

.nymia-schedule-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
    align-items: center;
}

.nymia-schedule-btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.15);
    background: transparent;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.nymia-schedule-btn.primary {
    border-color: #C7541A;
    background: rgba(199, 84, 26, 0.2);
}

.nymia-schedule-btn.primary:hover {
    background: rgba(199, 84, 26, 0.35);
    border-color: #C7541A;
    transform: translateY(-1px);
}

.nymia-schedule-btn.danger {
    border-color: rgba(255,107,107,0.5);
    color: #ff6b6b;
    background: rgba(255, 107, 107, 0.1);
}

.nymia-schedule-btn.danger:hover {
    background: rgba(255, 107, 107, 0.2);
    border-color: rgba(255,107,107,0.7);
    transform: translateY(-1px);
}

.nymia-schedule-btn:hover {
    background: rgba(255,255,255,0.08);
}

.nymia-schedule-empty {
    padding: 24px;
    text-align: center;
    color: rgba(255,255,255,0.6);
    border: 1px dashed rgba(255,255,255,0.1);
    border-radius: 12px;
}

/* Responsive styles for schedule actions */
@media (max-width: 768px) {
    .nymia-schedule-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .nymia-schedule-actions {
        width: 100%;
        justify-content: flex-start;
        margin-top: 12px;
    }
    
    .nymia-schedule-btn {
        flex: 1;
        min-width: 120px;
    }
}

@media (max-width: 640px) {
    .nymia-schedule-row {
        flex-direction: column;
        align-items: flex-start;
    }
    .nymia-schedule-actions {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}
</style>

<script>
window.nymiaLiveScheduleConfig = <?php echo wp_json_encode($schedule_config); ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Define nymiaAjax if not already defined
    if (typeof nymiaAjax === 'undefined') {
        var nymiaAjax = {
            ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('nymia_audio_upload'); ?>',
            ebookNonce: '<?php echo wp_create_nonce('nymia_upload_ebook'); ?>',
            zegoNonce: '<?php echo wp_create_nonce('nymia_zego_action'); ?>'
        };
    }
    
    // Make ebookNonce available globally for ebook.js
    var ebookNonce = nymiaAjax.ebookNonce;
    
    // Load Stream History
    function loadStreamHistory() {
        const historyList = document.getElementById('nymiaStreamHistoryList');
        if (!historyList) return;
        
        const formData = new FormData();
        formData.append('action', 'nymia_zego_get_user_history');
        formData.append('nonce', nymiaAjax.zegoNonce);
        
        fetch(nymiaAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && data.data && data.data.streams && data.data.streams.length > 0) {
                historyList.innerHTML = '';
                data.data.streams.forEach(stream => {
                    const historyItem = document.createElement('div');
                    historyItem.className = 'nymia-history-item';
                    historyItem.dataset.roomId = stream.roomId;
                    const date = new Date(stream.created * 1000);
                    const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    historyItem.innerHTML = `
                        <div class="nymia-history-content">
                            <h5>${stream.title}</h5>
                            <div class="nymia-history-meta">
                                <span>${formattedDate} at ${formattedTime}</span>
                                ${stream.viewers > 0 ? `<span>• ${stream.viewers} viewers</span>` : ''}
                            </div>
                        </div>
                        <div class="nymia-history-actions">
                            <div class="nymia-history-status completed">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <button class="nymia-delete-stream-btn" data-room-id="${stream.roomId}" title="Delete Stream">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                    historyList.appendChild(historyItem);
                    
                    // Add delete functionality
                    const deleteBtn = historyItem.querySelector('.nymia-delete-stream-btn');
                    if (deleteBtn) {
                        console.log('Adding delete button listener for room:', stream.roomId);
                        deleteBtn.addEventListener('click', function(e) {
                            console.log('Delete button clicked for room:', stream.roomId);
                            e.stopPropagation();
                            e.preventDefault();
                            deleteStream(stream.roomId, historyItem);
                        });
                    } else {
                        console.error('Delete button not found for stream:', stream.roomId);
                    }
                });
            } else {
                historyList.innerHTML = `
                    <div class="nymia-history-empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <p>No streams yet</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading stream history:', error);
        });
    }
    
    // Delete Stream Function
    function deleteStream(roomId, historyItem) {
        console.log('Delete stream called with roomId:', roomId);
        
        if (!confirm('Are you sure you want to delete this stream?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'nymia_zego_delete_room');
        formData.append('nonce', nymiaAjax.zegoNonce);
        formData.append('roomId', roomId);
        
        console.log('Sending delete request for room:', roomId);
        
        fetch(nymiaAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Delete response received, status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Delete response data:', data);
            if (data && data.success) {
                // Remove the item from the list
                historyItem.style.opacity = '0';
                historyItem.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    historyItem.remove();
                    // Reload history to check if empty
                    loadStreamHistory();
                }, 200);
            } else {
                alert('Failed to delete stream: ' + (data.data && data.data.message ? data.data.message : 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting stream:', error);
            alert('Failed to delete stream. Please try again.');
        });
    }
    
    // Load history on page load
    loadStreamHistory();
    
    // ========================================
    // AVAILABLE NOW (GREEN LIGHT) TOGGLE
    // ========================================
    (function() {
        const availableToggle = document.getElementById('nymiaAvailableNowToggle');
        const availableStatus = document.getElementById('nymiaAvailableStatus');
        const availableSettings = document.getElementById('nymiaAvailableSettings');
        const perMinuteInput = document.getElementById('nymia_per_minute_price_available');
        
        if (!availableToggle) return;
        
        // Load current availability status
        function loadAvailabilityStatus() {
            const formData = new FormData();
            formData.append('action', 'nymia_get_availability_status');
            formData.append('nonce', '<?php echo wp_create_nonce('nymia_availability'); ?>');
            
            fetch(nymiaAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    const isAvailable = data.data.is_available || false;
                    const perMinutePrice = parseFloat(data.data.per_minute_price || 0);
                    const onlineThumbnail = data.data.online_thumbnail || '';
                    
                    availableToggle.checked = isAvailable;
                    if (perMinuteInput) {
                        perMinuteInput.value = perMinutePrice > 0 ? perMinutePrice.toFixed(2) : '';
                    }
                    
                    // Load thumbnail if exists
                    if (onlineThumbnail) {
                        const onlineThumbnailImg = document.getElementById('onlineThumbnailImg');
                        const onlineThumbnailPreview = document.getElementById('onlineThumbnailPreview');
                        const onlineThumbnailBtn = document.getElementById('onlineThumbnailBtn');
                        if (onlineThumbnailImg && onlineThumbnailPreview) {
                            onlineThumbnailImg.src = onlineThumbnail;
                            onlineThumbnailPreview.style.display = 'block';
                            if (onlineThumbnailBtn) onlineThumbnailBtn.style.display = 'none';
                        }
                    }
                    
                    updateAvailabilityUI(isAvailable);
                }
            })
            .catch(err => console.error('Error loading availability:', err));
        }
        
        function updateAvailabilityUI(isAvailable) {
            if (availableStatus) {
                availableStatus.textContent = isAvailable ? '<?php echo esc_js(__('On', 'nymia')); ?>' : '<?php echo esc_js(__('Off', 'nymia')); ?>';
            }
            if (availableSettings) {
                availableSettings.style.display = isAvailable ? 'block' : 'none';
            }
        }
        
        // Handle toggle change
        availableToggle.addEventListener('change', function() {
            const isAvailable = this.checked;
            
            // Immediately show/hide settings UI
            updateAvailabilityUI(isAvailable);
            
            // If turning ON, show settings and don't save yet (let user set price first)
            if (isAvailable) {
                // Focus on price input if it's empty
                if (perMinuteInput && (!perMinuteInput.value || parseFloat(perMinuteInput.value) <= 0)) {
                    perMinuteInput.focus();
                }
                return; // Don't save yet, let user set the price first
            }
            
            // If turning OFF, save immediately
            const formData = new FormData();
            formData.append('action', 'nymia_toggle_availability');
            formData.append('nonce', '<?php echo wp_create_nonce('nymia_availability'); ?>');
            formData.append('is_available', '0');
            
            fetch(nymiaAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    // Revert toggle on error
                    this.checked = true;
                    updateAvailabilityUI(true);
                    alert(data.data?.message || '<?php echo esc_js(__('Failed to update availability. Please try again.', 'nymia')); ?>');
                }
            })
            .catch(err => {
                console.error('Error toggling availability:', err);
                this.checked = true;
                updateAvailabilityUI(true);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            });
        });
        
        // Save availability when price is entered and toggle is ON
        if (perMinuteInput) {
            perMinuteInput.addEventListener('blur', function() {
                if (availableToggle.checked) {
                    const price = parseFloat(this.value) || 0;
                    if (price > 0) {
                        // Auto-save when price is entered
                        saveAvailability();
                    }
                }
            });
            
            // Also allow Enter key to save
            perMinuteInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && availableToggle.checked) {
                    e.preventDefault();
                    saveAvailability();
                }
            });
        }
        
        // Function to save availability with price
        function saveAvailability() {
            if (!availableToggle.checked) return;
            
            const price = perMinuteInput ? parseFloat(perMinuteInput.value) || 0 : 0;
            if (price <= 0) {
                alert('<?php echo esc_js(__('Please set a price per minute (must be greater than 0).', 'nymia')); ?>');
                if (perMinuteInput) perMinuteInput.focus();
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'nymia_toggle_availability');
            formData.append('nonce', '<?php echo wp_create_nonce('nymia_availability'); ?>');
            formData.append('is_available', '1');
            formData.append('per_minute_price', price);
            
            // Include online thumbnail if uploaded
            const onlineThumbnailInput = document.getElementById('online_thumbnail');
            if (onlineThumbnailInput && onlineThumbnailInput.files && onlineThumbnailInput.files[0]) {
                formData.append('online_thumbnail', onlineThumbnailInput.files[0]);
            }
            
            // Show loading state
            if (availableStatus) {
                const originalText = availableStatus.textContent;
                availableStatus.textContent = '<?php echo esc_js(__('Saving...', 'nymia')); ?>';
            }
            
            fetch(nymiaAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    if (availableStatus) {
                        availableStatus.textContent = '<?php echo esc_js(__('On', 'nymia')); ?>';
                    }
                    // Show success message
                    if (data.data.message) {
                        console.log(data.data.message);
                    }
                } else {
                    // Revert toggle on error
                    availableToggle.checked = false;
                    updateAvailabilityUI(false);
                    alert(data.data?.message || '<?php echo esc_js(__('Failed to update availability. Please try again.', 'nymia')); ?>');
                }
            })
            .catch(err => {
                console.error('Error saving availability:', err);
                availableToggle.checked = false;
                updateAvailabilityUI(false);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            });
        }
        
        
        // Load status on page load
        loadAvailabilityStatus();
    })();
    
    // ========================================
    // TEXT POST COMPOSER (TITLE + DESCRIPTION + IMAGE)
    // ========================================
    (function(){
        const placeholderAvatar = "<?php echo esc_js(get_template_directory_uri() . '/assets/images/profile.png'); ?>";
        try {
            const formEl = document.getElementById('nymiaTextPostForm');
            const titleEl = document.getElementById('nymiaTextPostTitle');
            const descEl = document.getElementById('nymiaTextPostMessage');
            const charCountEl = document.getElementById('nymiaTextPostCharCount');
            const sidebarEl = document.getElementById('nymiaTextSidebarList');
            const imageInput = document.getElementById('nymiaTextPostImage');
            const imageBtn = document.getElementById('nymiaTextPostImageBtn');
            const imagePreview = document.getElementById('nymiaTextPostImagePreview');
            const imagePreviewImg = imagePreview ? imagePreview.querySelector('img') : null;
            const imageRemove = document.getElementById('nymiaTextPostImageRemove');

            function escapeHtml(str) {
                const value = (str || '').toString();
                return value.replace(/[&<>"']/g, function(char) {
                    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                    return map[char] || char;
                });
            }

            function renderSidebarCard(post) {
                const singlePostPage = '<?php 
                    $single_post_page = get_page_by_path('single-post');
                    echo $single_post_page ? esc_url(get_permalink($single_post_page)) : esc_url(home_url('/single-post/'));
                ?>';
                const postLink = singlePostPage + (singlePostPage.includes('?') ? '&' : '?') + 'post_id=' + post.id;
                return `
                    <a href="${escapeHtml(postLink)}" class="nymia-sidebar-text-item" data-post-id="${post.id}">
                        <div class="nymia-sidebar-text-head">
                            <img src="${escapeHtml(post.avatar || placeholderAvatar)}" alt="${escapeHtml(post.author_name || 'User')}">
                            <div>
                                <h4>${escapeHtml(post.author_name || 'User')}</h4>
                                <p>${escapeHtml(post.time_ago || '')}</p>
                            </div>
                        </div>
                        <h5 class="nymia-sidebar-text-title">${escapeHtml(post.title || '')}</h5>
                        <p class="nymia-sidebar-text-body">${escapeHtml(post.excerpt || '')}</p>
                    </a>
                `;
            }

            function prependToSidebar(post) {
                if (!sidebarEl) return;
                const empty = sidebarEl.querySelector('.nymia-chat-empty');
                if (empty) empty.remove();
                sidebarEl.insertAdjacentHTML('afterbegin', renderSidebarCard(post));
            }

            if (descEl && charCountEl) {
                descEl.addEventListener('input', function(){
                    charCountEl.textContent = this.value.length + '/2000';
                });
            }

            if (imageBtn && imageInput) {
                imageBtn.addEventListener('click', function(){
                    imageInput.click();
                });
            }
            if (imageInput && imagePreview && imagePreviewImg) {
                imageInput.addEventListener('change', function(){
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        if (!file.type.startsWith('image/')) {
                            alert('<?php echo esc_js(__('Please select an image file.', 'nymia')); ?>');
                            this.value = '';
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e){
                            imagePreviewImg.src = e.target.result;
                            imagePreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                });
                if (imageRemove) {
                    imageRemove.addEventListener('click', function(){
                        imageInput.value = '';
                        imagePreview.style.display = 'none';
                        imagePreviewImg.src = '';
                    });
                }
            }

            if (formEl && titleEl && descEl) {
                formEl.addEventListener('submit', function(e){
                    e.preventDefault();
                    const title = titleEl.value.trim();
                    const description = descEl.value.trim();
                    if (!title) {
                        alert('<?php echo esc_js(__('Please enter a title.', 'nymia')); ?>');
                        return;
                    }
                    if (!description) {
                        alert('<?php echo esc_js(__('Please enter a description.', 'nymia')); ?>');
                        return;
                    }
                    const submitBtn = document.getElementById('nymiaTextPostSubmit');
                    const nonceField = formEl.querySelector('input[name="nymia_text_post_nonce"]');
                    const fd = new FormData();
                    fd.append('action', 'nymia_submit_text_post');
                    fd.append('title', title);
                    fd.append('description', description);
                    fd.append('nonce', nonceField ? nonceField.value : ((window.nymiaAjax && window.nymiaAjax.textPostNonce) || ''));
                    if (imageInput && imageInput.files.length) {
                        fd.append('image', imageInput.files[0]);
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = '<?php echo esc_js(__('Posting...', 'nymia')); ?>';
                    }

                    fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data || !data.success || !data.data) {
                            throw new Error((data && data.data && data.data.message) ? data.data.message : '<?php echo esc_js(__('Unable to publish post.', 'nymia')); ?>');
                        }
                        titleEl.value = '';
                        descEl.value = '';
                        if (charCountEl) charCountEl.textContent = '0/2000';
                        if (imageInput) imageInput.value = '';
                        if (imagePreview) {
                            imagePreview.style.display = 'none';
                            if (imagePreviewImg) imagePreviewImg.src = '';
                        }
                        prependToSidebar(data.data);
                    })
                    .catch(error => {
                        alert(error.message || '<?php echo esc_js(__('Unable to publish post.', 'nymia')); ?>');
                    })
                    .finally(() => {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = '<?php echo esc_js(__('Share Post', 'nymia')); ?>';
                        }
                    });
                });
            }
        } catch (err) {
            console.error('Create text post init failed:', err);
        }
    })();
    
    
    // Tab switching
    const tabs = document.querySelectorAll('.nymia-create-tab');
    const contents = document.querySelectorAll('.nymia-create-content');
    const sidebarContents = document.querySelectorAll('.nymia-sidebar-content');
    const streamForm = document.getElementById('streamSetupForm');
    const goLiveBtn = document.getElementById('nymiaGoLiveBtn');
    
    // Function to switch tabs
    function switchTab(targetTab) {
        // Remove active class from all tabs and contents
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));
        sidebarContents.forEach(s => s.classList.remove('active'));
        
        // Add active class to target tab and corresponding content
        const tabBtn = document.querySelector('.nymia-create-tab[data-tab="' + targetTab + '"]');
        const tabContent = document.getElementById('content-' + targetTab);
        
        if (tabBtn) tabBtn.classList.add('active');
        if (tabContent) tabContent.classList.add('active');
        
        // Show corresponding sidebar content
        if (targetTab === 'live') {
            const sidebarContent = document.querySelector('.live-chat-content');
            if (sidebarContent) sidebarContent.classList.add('active');
        } else if (targetTab === 'audio') {
            const sidebarContent = document.querySelector('.audio-posts-content');
            if (sidebarContent) sidebarContent.classList.add('active');
        } else if (targetTab === 'ebook') {
            const sidebarContent = document.querySelector('.ebook-posts-content');
            if (sidebarContent) sidebarContent.classList.add('active');
        } else if (targetTab === 'audio-book') {
            const sidebarContent = document.querySelector('.ebook-posts-content');
            if (sidebarContent) sidebarContent.classList.add('active');
        } else if (targetTab === 'text') {
            const sidebarContent = document.querySelector('.text-posts-content');
            if (sidebarContent) sidebarContent.classList.add('active');
        }
    }
    
    // Check for tab parameter in URL and switch to it
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam && ['live', 'audio', 'ebook', 'audio-book', 'text'].includes(tabParam)) {
        switchTab(tabParam);
    }
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            switchTab(targetTab);
        });
    });
    
    // Stream Thumbnail Upload Handler
    (function() {
        const streamThumbnailInput = document.getElementById('stream_thumbnail');
        const streamThumbnailBtn = document.getElementById('streamThumbnailBtn');
        const streamThumbnailPreview = document.getElementById('streamThumbnailPreview');
        const streamThumbnailImg = document.getElementById('streamThumbnailImg');
        const streamThumbnailRemove = document.getElementById('streamThumbnailRemove');
        
        if (streamThumbnailBtn && streamThumbnailInput) {
            streamThumbnailBtn.addEventListener('click', function() {
                streamThumbnailInput.click();
            });
        }
        
        if (streamThumbnailInput && streamThumbnailPreview && streamThumbnailImg) {
            streamThumbnailInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    if (!file.type.startsWith('image/')) {
                        alert('<?php echo esc_js(__('Please select an image file.', 'nymia')); ?>');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        streamThumbnailImg.src = e.target.result;
                        streamThumbnailPreview.style.display = 'block';
                        if (streamThumbnailBtn) streamThumbnailBtn.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        if (streamThumbnailRemove && streamThumbnailInput && streamThumbnailPreview && streamThumbnailBtn) {
            streamThumbnailRemove.addEventListener('click', function() {
                streamThumbnailInput.value = '';
                streamThumbnailImg.src = '';
                streamThumbnailPreview.style.display = 'none';
                streamThumbnailBtn.style.display = 'flex';
            });
        }
    })();
    
    // Online Now Thumbnail Upload Handler
    (function() {
        const onlineThumbnailInput = document.getElementById('online_thumbnail');
        const onlineThumbnailBtn = document.getElementById('onlineThumbnailBtn');
        const onlineThumbnailPreview = document.getElementById('onlineThumbnailPreview');
        const onlineThumbnailImg = document.getElementById('onlineThumbnailImg');
        const onlineThumbnailRemove = document.getElementById('onlineThumbnailRemove');
        
        if (onlineThumbnailBtn && onlineThumbnailInput) {
            onlineThumbnailBtn.addEventListener('click', function() {
                onlineThumbnailInput.click();
            });
        }
        
        if (onlineThumbnailInput && onlineThumbnailPreview && onlineThumbnailImg) {
            onlineThumbnailInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    if (!file.type.startsWith('image/')) {
                        alert('<?php echo esc_js(__('Please select an image file.', 'nymia')); ?>');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        onlineThumbnailImg.src = e.target.result;
                        onlineThumbnailPreview.style.display = 'block';
                        if (onlineThumbnailBtn) onlineThumbnailBtn.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        if (onlineThumbnailRemove && onlineThumbnailInput && onlineThumbnailPreview && onlineThumbnailBtn) {
            onlineThumbnailRemove.addEventListener('click', function() {
                onlineThumbnailInput.value = '';
                onlineThumbnailImg.src = '';
                onlineThumbnailPreview.style.display = 'none';
                onlineThumbnailBtn.style.display = 'flex';
            });
        }
    })();
    
    // Schedule Thumbnail Upload Handler
    (function() {
        const scheduleThumbnailInput = document.getElementById('schedule_thumbnail');
        const scheduleThumbnailBtn = document.getElementById('scheduleThumbnailBtn');
        const scheduleThumbnailPreview = document.getElementById('scheduleThumbnailPreview');
        const scheduleThumbnailImg = document.getElementById('scheduleThumbnailImg');
        const scheduleThumbnailRemove = document.getElementById('scheduleThumbnailRemove');
        
        if (scheduleThumbnailBtn && scheduleThumbnailInput) {
            scheduleThumbnailBtn.addEventListener('click', function() {
                scheduleThumbnailInput.click();
            });
        }
        
        if (scheduleThumbnailInput && scheduleThumbnailPreview && scheduleThumbnailImg) {
            scheduleThumbnailInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    if (!file.type.startsWith('image/')) {
                        alert('<?php echo esc_js(__('Please select an image file.', 'nymia')); ?>');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        scheduleThumbnailImg.src = e.target.result;
                        scheduleThumbnailPreview.style.display = 'block';
                        if (scheduleThumbnailBtn) scheduleThumbnailBtn.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        if (scheduleThumbnailRemove && scheduleThumbnailInput && scheduleThumbnailPreview && scheduleThumbnailBtn) {
            scheduleThumbnailRemove.addEventListener('click', function() {
                scheduleThumbnailInput.value = '';
                scheduleThumbnailImg.src = '';
                scheduleThumbnailPreview.style.display = 'none';
                scheduleThumbnailBtn.style.display = 'flex';
            });
        }
    })();
    
    // Paid access toggle for live stream
    const paidAccessToggle = document.getElementById('paid_access');
    const priceInput = document.getElementById('stream_price');
    
    if (paidAccessToggle && priceInput) {
        paidAccessToggle.addEventListener('change', function() {
            priceInput.disabled = !this.checked;
            if (!this.checked) {
                priceInput.value = '0.00';
            }
        });
    }

    // Live stream scheduling system
    const scheduleConfig = window.nymiaLiveScheduleConfig || {};
    const scheduleForm = document.getElementById('nymiaScheduleStreamForm');
    const scheduleListEl = document.getElementById('nymiaScheduleList');
    const scheduleMessageEl = document.getElementById('nymiaScheduleMessage');
    const scheduleTitleInput = document.getElementById('nymia-schedule-title');
    const scheduleStartInput = document.getElementById('nymia-schedule-start');
    const scheduleDurationInput = document.getElementById('nymia-schedule-duration');
    const scheduleEventType = document.getElementById('nymia-schedule-event-type');
    const scheduleMaxAttendeesWrapper = document.getElementById('nymia-schedule-max-attendees-wrapper');
    const scheduleMaxAttendees = document.getElementById('nymia-schedule-max-attendees');
    
    // Toggle max attendees field based on event type
    if (scheduleEventType && scheduleMaxAttendeesWrapper) {
        scheduleEventType.addEventListener('change', function() {
            if (this.value === 'group') {
                scheduleMaxAttendeesWrapper.style.display = 'block';
                if (scheduleMaxAttendees) {
                    scheduleMaxAttendees.setAttribute('required', 'required');
                }
            } else {
                scheduleMaxAttendeesWrapper.style.display = 'none';
                if (scheduleMaxAttendees) {
                    scheduleMaxAttendees.removeAttribute('required');
                    scheduleMaxAttendees.value = '';
                }
            }
        });
        // Trigger on page load to set initial state
        scheduleEventType.dispatchEvent(new Event('change'));
    }
    const countdownCard = document.getElementById('nymiaScheduleCountdownCard');
    const countdownTitle = document.getElementById('nymiaScheduleCountdownTitle');
    const countdownDate = document.getElementById('nymiaScheduleCountdownDate');
    const countdownDisplay = document.getElementById('nymiaScheduleCountdown');
    const countdownHint = document.getElementById('nymiaScheduleCountdownHint');
    const scheduleAjaxUrl = scheduleConfig.ajaxUrl || (window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php';
    const scheduleCreateNonce = scheduleConfig.createNonce || '';
    const scheduleDeleteNonce = scheduleConfig.deleteNonce || '';
    const streamTitleInput = document.getElementById('stream_title');
    let scheduleState = normalizeSchedules(scheduleConfig.schedules || []);
    let countdownIntervalId = null;
    let currentCountdownId = '';
    let scheduledStartInFlight = '';
    let autoStartDisabledId = '';
    const serverOffsetSeconds = (scheduleConfig.serverNow ? parseInt(scheduleConfig.serverNow, 10) : Math.floor(Date.now() / 1000)) - Math.floor(Date.now() / 1000);

    function getServerNow() {
        return Math.floor(Date.now() / 1000) + serverOffsetSeconds;
    }

    function normalizeSchedules(list) {
        if (!Array.isArray(list)) {
            return [];
        }
        return list
            .map(function(item) {
                return {
                    id: String(item.id || ''),
                    title: String(item.title || ''),
                    start_timestamp: parseInt(item.start_timestamp, 10) || 0,
                    duration: parseInt(item.duration, 10) || 60,
                    created: parseInt(item.created, 10) || 0
                };
            })
            .filter(function(item) { return !!item.id; });
    }

    function prunePastSchedules() {
        const cutoff = getServerNow() - 300;
        scheduleState = scheduleState.filter(function(item) {
            return item.start_timestamp >= cutoff;
        });
    }

    function formatScheduleDate(timestamp) {
        if (!timestamp) {
            return '';
        }
        const date = new Date(timestamp * 1000);
        return date.toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
    }

    function renderScheduleList() {
        if (!scheduleListEl) {
            return;
        }
        if (!scheduleState.length) {
            scheduleListEl.innerHTML = '<div class="nymia-schedule-empty" data-schedule-empty>' +
                '<?php echo esc_js(__('No scheduled sessions yet. Add your first schedule to automate your next live event.', 'nymia')); ?>' +
                '</div>';
            return;
        }
        const rows = scheduleState
            .slice()
            .sort(function(a, b) { return a.start_timestamp - b.start_timestamp; })
            .map(function(schedule) {
                const durationLabel = schedule.duration === 1
                    ? '<?php echo esc_js(__('1 minute planned', 'nymia')); ?>'
                    : schedule.duration + ' <?php echo esc_js(__('minutes planned', 'nymia')); ?>';
                return `
                    <div class="nymia-schedule-row" data-schedule-id="${schedule.id}" data-schedule-start="${schedule.start_timestamp}">
                        <div>
                            <h4>${escapeHtml(schedule.title || '')}</h4>
                            <p>${formatScheduleDate(schedule.start_timestamp)} • ${durationLabel}</p>
                        </div>
                        <div class="nymia-schedule-actions">
                            <button type="button" class="nymia-schedule-btn primary" data-schedule-start-btn="${schedule.id}">
                                <?php echo esc_js(__('Start now', 'nymia')); ?>
                            </button>
                            <button type="button" class="nymia-schedule-btn danger" data-schedule-delete="${schedule.id}">
                                <?php echo esc_js(__('Delete', 'nymia')); ?>
                            </button>
                        </div>
                    </div>
                `;
            })
            .join('');
        scheduleListEl.innerHTML = rows;
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function(char) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return map[char] || char;
        });
    }

    function setScheduleMessage(message, type) {
        if (!scheduleMessageEl) {
            return;
        }
        scheduleMessageEl.textContent = message || '';
        scheduleMessageEl.classList.remove('is-success', 'is-error', 'is-loading');
        
        if (type === 'success') {
            scheduleMessageEl.classList.add('is-success');
            // Auto-clear success message after 5 seconds
            setTimeout(function() {
                if (scheduleMessageEl && scheduleMessageEl.classList.contains('is-success')) {
                    setScheduleMessage('', '');
                }
            }, 5000);
        } else if (type === 'error') {
            scheduleMessageEl.classList.add('is-error');
        } else if (message && message.trim() !== '') {
            // Default styling for loading/info messages
            scheduleMessageEl.classList.add('is-loading');
        }
    }

    function getNextSchedule() {
        if (!scheduleState.length) {
            return null;
        }
        const now = getServerNow();
        const upcoming = scheduleState
            .slice()
            .filter(function(schedule) { return schedule.start_timestamp >= now - 5; })
            .sort(function(a, b) { return a.start_timestamp - b.start_timestamp; });
        return upcoming.length ? upcoming[0] : null;
    }

    function updateCountdownDisplay() {
        if (!countdownDisplay) {
            return;
        }
        const next = getNextSchedule();
        if (!next) {
            currentCountdownId = '';
            if (countdownCard) countdownCard.style.display = 'none';
            countdownDisplay.textContent = '--:--:--';
            if (countdownTitle) countdownTitle.textContent = '';
            if (countdownDate) countdownDate.textContent = '';
            return;
        }
        if (countdownCard) countdownCard.style.display = '';
        const now = getServerNow();
        const diff = Math.max(0, next.start_timestamp - now);
        const hours = Math.floor(diff / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
        const seconds = Math.floor(diff % 60).toString().padStart(2, '0');
        countdownDisplay.textContent = `${hours}:${minutes}:${seconds}`;
        if (countdownTitle) countdownTitle.textContent = next.title || '<?php echo esc_js(__('Scheduled Stream', 'nymia')); ?>';
        if (countdownDate) countdownDate.textContent = formatScheduleDate(next.start_timestamp);
        currentCountdownId = next.id;

        if (diff === 0 && scheduledStartInFlight !== next.id) {
            startScheduledStream(next, true);
        }
    }

    function refreshCountdown() {
        if (countdownIntervalId) {
            clearInterval(countdownIntervalId);
        }
        updateCountdownDisplay();
        countdownIntervalId = setInterval(updateCountdownDisplay, 1000);
    }

    function startScheduledStream(schedule, triggeredAutomatically) {
        if (!schedule || !streamForm || !goLiveBtn) {
            return;
        }
        if (triggeredAutomatically && autoStartDisabledId && autoStartDisabledId === schedule.id) {
            return;
        }
        if (scheduledStartInFlight && scheduledStartInFlight === schedule.id) {
            return;
        }
        scheduledStartInFlight = schedule.id;
        if (streamTitleInput && schedule.title) {
            streamTitleInput.value = schedule.title;
        }
        if (countdownHint && triggeredAutomatically) {
            countdownHint.textContent = '<?php echo esc_js(__('Starting stream automatically…', 'nymia')); ?>';
        }
        streamForm.dataset.scheduleId = schedule.id;
        streamForm.dispatchEvent(new Event('submit'));
    }

    function handleScheduleFormSubmit(event) {
        event.preventDefault();
        if (!scheduleForm) {
            return;
        }
        const formData = new FormData(scheduleForm);
        formData.append('action', 'nymia_schedule_live_stream');
        formData.append('nonce', scheduleCreateNonce);
        
        // Ensure max_attendees is included only for group events
        if (scheduleEventType && scheduleEventType.value === 'single') {
            formData.delete('max_attendees');
        }
        setScheduleMessage('<?php echo esc_js(__('Saving schedule…', 'nymia')); ?>');
        fetch(scheduleAjaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data || !data.success) {
                throw new Error((data && data.data && data.data.message) || (data && data.message) || '<?php echo esc_js(__('Unable to save schedule.', 'nymia')); ?>');
            }
            scheduleState = normalizeSchedules((data.data && data.data.schedules) || []);
            prunePastSchedules();
            renderScheduleList();
            refreshCountdown();
            setScheduleMessage((data.data && data.data.message) || '<?php echo esc_js(__('Stream scheduled successfully.', 'nymia')); ?>', 'success');
            scheduleForm.reset();
            if (scheduleStartInput) {
                scheduleStartInput.value = '';
            }
            // Clear thumbnail preview
            const scheduleThumbnailInput = document.getElementById('schedule_thumbnail');
            const scheduleThumbnailPreview = document.getElementById('scheduleThumbnailPreview');
            const scheduleThumbnailImg = document.getElementById('scheduleThumbnailImg');
            const scheduleThumbnailBtn = document.getElementById('scheduleThumbnailBtn');
            if (scheduleThumbnailInput) scheduleThumbnailInput.value = '';
            if (scheduleThumbnailImg) scheduleThumbnailImg.src = '';
            if (scheduleThumbnailPreview) scheduleThumbnailPreview.style.display = 'none';
            if (scheduleThumbnailBtn) scheduleThumbnailBtn.style.display = 'flex';
        })
        .catch(function(error) {
            setScheduleMessage(error.message || '<?php echo esc_js(__('Unable to save schedule.', 'nymia')); ?>', 'error');
        });
    }

    function deleteSchedule(scheduleId) {
        if (!scheduleId) {
            return;
        }
        const fd = new FormData();
        fd.append('action', 'nymia_delete_stream_schedule');
        fd.append('nonce', scheduleDeleteNonce);
        fd.append('schedule_id', scheduleId);
        return fetch(scheduleAjaxUrl, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data || !data.success) {
                throw new Error((data && data.data && data.data.message) || (data && data.message) || '<?php echo esc_js(__('Unable to delete schedule.', 'nymia')); ?>');
            }
            scheduleState = normalizeSchedules((data.data && data.data.schedules) || []);
            prunePastSchedules();
            renderScheduleList();
            refreshCountdown();
            return true;
        });
    }

    function markScheduleConsumed(scheduleId) {
        if (!scheduleId) {
            return;
        }
        scheduleState = scheduleState.filter(function(item) { return item.id !== scheduleId; });
        renderScheduleList();
        refreshCountdown();
        scheduledStartInFlight = '';
        if (autoStartDisabledId === scheduleId) {
            autoStartDisabledId = '';
        }
        if (countdownHint) {
            countdownHint.textContent = '<?php echo esc_js(__('Your stream will start automatically when the countdown reaches zero.', 'nymia')); ?>';
        }
        deleteSchedule(scheduleId).catch(function() {
            // Ignore background delete errors; user will not see duplicates
        });
    }

    // expose for go live handler
    window.nymiaMarkScheduleConsumed = markScheduleConsumed;

    if (scheduleForm) {
        scheduleForm.addEventListener('submit', handleScheduleFormSubmit);
    }

    if (scheduleListEl) {
        scheduleListEl.addEventListener('click', function(event) {
            const startBtn = event.target.closest('[data-schedule-start-btn]');
            const deleteBtn = event.target.closest('[data-schedule-delete]');
            if (startBtn && startBtn.dataset.scheduleStartBtn) {
                const schedule = scheduleState.find(function(item) { return item.id === startBtn.dataset.scheduleStartBtn; });
                if (schedule) {
                    startScheduledStream(schedule, false);
                }
            }
            if (deleteBtn && deleteBtn.dataset.scheduleDelete) {
                if (confirm('<?php echo esc_js(__('Delete this scheduled stream?', 'nymia')); ?>')) {
                    deleteSchedule(deleteBtn.dataset.scheduleDelete).catch(function(error) {
                        alert(error.message || '<?php echo esc_js(__('Unable to delete schedule.', 'nymia')); ?>');
                    });
                }
            }
        });
    }

    prunePastSchedules();
    renderScheduleList();
    refreshCountdown();
    
    // Paid access toggle for audio upload
    const audioPaidAccessToggle = document.getElementById('audio_paid_access');
    const audioPriceInput = document.getElementById('audio_price');
    
    if (audioPaidAccessToggle && audioPriceInput) {
        audioPaidAccessToggle.addEventListener('change', function() {
            audioPriceInput.disabled = !this.checked;
            if (!this.checked) {
                audioPriceInput.value = '0.00';
            }
        });
    }
    
    
    // Upload mode switching
    const uploadModeBtns = document.querySelectorAll('.nymia-upload-mode-btn');
    const uploadArea = document.getElementById('audioUploadArea');
    const recordingArea = document.getElementById('audioRecordingArea');
    
    uploadModeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const mode = this.getAttribute('data-mode');
            
            // Remove active class from all mode buttons
            uploadModeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide appropriate areas
            if (mode === 'upload') {
                uploadArea.style.display = 'block';
                recordingArea.style.display = 'none';
            } else if (mode === 'record') {
                uploadArea.style.display = 'none';
                recordingArea.style.display = 'block';
            }
        });
    });
    
    // File upload functionality
    const fileInput = document.getElementById('audioFileInput');
    const progressBar = document.querySelector('.nymia-upload-progress');
    const progressFill = document.querySelector('.nymia-progress-fill');
    const progressText = document.querySelector('.nymia-progress-text');
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('nymia-drag-over');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('nymia-drag-over');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('nymia-drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    // Click to upload
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        // Validate file type
        if (!file.type.startsWith('audio/')) {
            alert('Please select an audio file.');
            return;
        }
        
        // Simulate upload progress
        progressBar.style.display = 'block';
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                progressBar.style.display = 'none';
                alert('File uploaded successfully!');
            }
            progressFill.style.width = progress + '%';
            progressText.textContent = Math.round(progress) + '%';
        }, 200);
    }
    
    // Audio recording functionality
    let mediaRecorder;
    let audioChunks = [];
    let recordingTimer;
    let recordingStartTime;
    let isRecording = false;
    let isPaused = false;
    let recordedAudioFile = null; // Store recorded audio file
    
    const recordBtn = document.getElementById('recordBtn');
    const stopBtn = document.getElementById('stopBtn');
    const pauseBtn = document.getElementById('pauseBtn');
    const recordingIndicator = document.getElementById('recordingIndicator');
    const recordingTimerDisplay = document.getElementById('recordingTimer');
    const recordedAudio = document.getElementById('recordedAudio');
    const recordingPreview = document.getElementById('recordingPreview');
    const reRecordBtn = document.querySelector('.nymia-btn-re-record');
    
    if (recordBtn) {
        recordBtn.addEventListener('click', startRecording);
    }
    
    if (stopBtn) {
        stopBtn.addEventListener('click', stopRecording);
    }
    
    if (pauseBtn) {
        pauseBtn.addEventListener('click', togglePause);
    }
    
    if (reRecordBtn) {
        reRecordBtn.addEventListener('click', function() {
            recordingPreview.style.display = 'none';
            recordBtn.style.display = 'inline-flex';
            stopBtn.style.display = 'none';
            pauseBtn.style.display = 'none';
            recordedAudio.src = '';
            audioChunks = [];
            recordedAudioFile = null; // Clear recorded file
            
            // Clear file input
            const fileInput = document.getElementById('audioFileInput');
            if (fileInput) {
                fileInput.value = '';
            }
        });
    }
    
    async function startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            
            mediaRecorder.ondataavailable = function(event) {
                audioChunks.push(event.data);
            };
            
            mediaRecorder.onstop = function() {
                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                const audioURL = URL.createObjectURL(audioBlob);
                recordedAudio.src = audioURL;
                recordingPreview.style.display = 'block';
                
                // Store recorded audio file for submission
                recordedAudioFile = new File([audioBlob], 'recorded-audio.wav', { type: 'audio/wav' });
                
                // Set the recorded file to the hidden input
                const fileInput = document.getElementById('audioFileInput');
                if (fileInput) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(recordedAudioFile);
                    fileInput.files = dataTransfer.files;
                }
            };
            
            mediaRecorder.start();
            isRecording = true;
            recordingStartTime = Date.now();
            
            // Update UI
            recordBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';
            pauseBtn.style.display = 'inline-flex';
            recordingIndicator.style.display = 'flex';
            
            // Start timer
            recordingTimer = setInterval(updateTimer, 1000);
            
        } catch (error) {
            console.error('Error accessing microphone:', error);
            alert('Error accessing microphone. Please check your permissions.');
        }
    }
    
    function stopRecording() {
        if (mediaRecorder && isRecording) {
            mediaRecorder.stop();
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            
            isRecording = false;
            isPaused = false;
            
            // Update UI
            stopBtn.style.display = 'none';
            pauseBtn.style.display = 'none';
            recordingIndicator.style.display = 'none';
            
            clearInterval(recordingTimer);
        }
    }
    
    function togglePause() {
        if (isPaused) {
            mediaRecorder.resume();
            isPaused = false;
            pauseBtn.innerHTML = `
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
                <span>Pause</span>
            `;
            recordingTimer = setInterval(updateTimer, 1000);
        } else {
            mediaRecorder.pause();
            isPaused = true;
            pauseBtn.innerHTML = `
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <span>Resume</span>
            `;
            clearInterval(recordingTimer);
        }
    }
    
    function updateTimer() {
        const elapsed = Date.now() - recordingStartTime;
        const minutes = Math.floor(elapsed / 60000);
        const seconds = Math.floor((elapsed % 60000) / 1000);
        recordingTimerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    // Audio visualizer
    const canvas = document.getElementById('audioVisualizer');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        
        function drawVisualizer() {
            if (isRecording) {
                // Simple visualizer animation
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#ff6b6b';
                
                for (let i = 0; i < 20; i++) {
                    const height = Math.random() * canvas.height;
                    const x = i * (canvas.width / 20);
                    const barWidth = canvas.width / 20 - 2;
                    ctx.fillRect(x, canvas.height - height, barWidth, height);
                }
                
                requestAnimationFrame(drawVisualizer);
            }
        }
        
        // Start visualizer when recording starts
        const originalStartRecording = startRecording;
        startRecording = function() {
            originalStartRecording();
            drawVisualizer();
        };
    }
    
    // Stream setup form (streaming disabled)
    if (streamForm && goLiveBtn) {
        streamForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(streamForm);
            const streamTitle = formData.get('stream_title');
            const streamThumbnail = formData.get('stream_thumbnail');
            const pendingScheduleId = streamForm.dataset.scheduleId || '';
            goLiveBtn.disabled = true;
            goLiveBtn.textContent = 'Starting...';
            const fd = new FormData();
            fd.append('action', 'nymia_zego_create_room');
            fd.append('nonce', (window.nymiaAjax && window.nymiaAjax.zegoNonce) || '');
            fd.append('title', streamTitle);
            // Include stream thumbnail if uploaded
            if (streamThumbnail && streamThumbnail.size > 0) {
                fd.append('stream_thumbnail', streamThumbnail);
            }
            fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', { method:'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (!data || !data.success) throw new Error((data && data.data && data.data.message) || 'Failed');
                    const appID = data.data.appId;
                    const roomID = data.data.roomId;
                    const userID = data.data.userId;
                    const token = data.data.token;
                    const env = data.data.env || 'production';
                    const serverSecret = data.data.serverSecret || '';
                    if (!window.ZegoUIKitPrebuilt) {
                        alert('ZEGO SDK not loaded.');
                        goLiveBtn.textContent = 'Go Live';
                        goLiveBtn.disabled = false;
                        return;
                    }
                    const creatorName = '<?php $cu = wp_get_current_user(); echo esc_js($cu && $cu->ID ? ($cu->display_name ?: $cu->user_login) : 'Host'); ?>';
                    const creatorAvatar = '<?php 
                        $cu = wp_get_current_user(); 
                        if ($cu && $cu->ID) {
                            $custom_avatar = get_user_meta($cu->ID, 'custom_avatar', true);
                            if ($custom_avatar) {
                                echo esc_js($custom_avatar);
                            } else {
                                echo esc_js(get_avatar_url($cu->ID, array('size' => 150)));
                            }
                        } else {
                            echo esc_js(get_template_directory_uri() . '/assets/images/profile.png');
                        }
                    ?>';
                    const kitToken = (env === 'test' && serverSecret)
                        ? window.ZegoUIKitPrebuilt.generateKitTokenForTest(appID, serverSecret, roomID, userID, creatorName)
                        : window.ZegoUIKitPrebuilt.generateKitTokenForProduction(appID, token, roomID, userID, creatorName);
                    const zp = window.ZegoUIKitPrebuilt.create(kitToken);
                    const mount = document.createElement('div');
                    mount.id = 'zego-live-container';
                    mount.style.width = '100%';
                    mount.style.height = '600px';
                    mount.style.maxWidth = '100%';
                    mount.style.overflow = 'hidden';
                    
                    // Set responsive height based on viewport with better calculation
                    function setZegoHeight() {
                        const width = window.innerWidth;
                        const isLandscape = window.innerWidth > window.innerHeight;
                        
                        if (width <= 480) {
                            mount.style.height = '250px';
                        } else if (width <= 768) {
                            if (isLandscape) {
                                mount.style.height = '200px';
                            } else {
                                mount.style.height = '300px';
                            }
                        } else if (width <= 1024) {
                            mount.style.height = '500px';
                        } else {
                            mount.style.height = '600px';
                        }
                        
                        // Force update on Zego container if it exists
                        const zegoContainer = document.getElementById('zego-live-container');
                        if (zegoContainer) {
                            zegoContainer.style.width = '100%';
                            zegoContainer.style.maxWidth = '100%';
                            // Force Zego elements to be responsive
                            setTimeout(function() {
                                const zegoElements = zegoContainer.querySelectorAll('[class*="zego"], [id*="zego"], video, canvas, iframe');
                                zegoElements.forEach(function(el) {
                                    el.style.maxWidth = '100%';
                                    if (el.tagName === 'VIDEO' || el.tagName === 'CANVAS' || el.tagName === 'IFRAME') {
                                        el.style.width = '100%';
                                        el.style.height = 'auto';
                                        el.style.objectFit = 'contain';
                                    }
                                });
                            }, 100);
                        }
                    }
                    setZegoHeight();
                    
                    // Throttled resize handler
                    let resizeTimeout;
                    window.addEventListener('resize', function() {
                        clearTimeout(resizeTimeout);
                        resizeTimeout = setTimeout(setZegoHeight, 150);
                    });
                    
                    window.addEventListener('orientationchange', function() {
                        setTimeout(setZegoHeight, 200);
                    });
                    
                    document.querySelector('.nymia-stream-control').appendChild(mount);
                    
                    // Monitor DOM changes and force responsive styles + inject profile images
                    const observer = new MutationObserver(function(mutations) {
                        const zegoContainer = document.getElementById('zego-live-container');
                        if (zegoContainer) {
                            // Force all video elements to be responsive
                            const videos = zegoContainer.querySelectorAll('video');
                            videos.forEach(function(video) {
                                video.style.maxWidth = '100%';
                                video.style.width = '100%';
                                video.style.height = 'auto';
                                video.style.objectFit = 'contain';
                            });
                            
                            // Force all containers to respect width
                            const containers = zegoContainer.querySelectorAll('[class*="container"], [class*="wrapper"], [class*="layout"]');
                            containers.forEach(function(container) {
                                container.style.maxWidth = '100%';
                            });
                                  
                            // Inject user profile images for user list and chat
                            const userElements = zegoContainer.querySelectorAll('[class*="user"], [class*="User"], [class*="name"], [class*="Name"]');
                            userElements.forEach(function(element) {
                                // Look for img tags or elements that might contain user info
                                const imgs = element.querySelectorAll('img');
                                if (imgs.length > 0) {
                                    imgs.forEach(function(img) {
                                        if (!img.src || img.src.includes('data:image') || img.src.includes('placeholder') || img.src === '') {
                                            img.src = creatorAvatar;
                                            img.alt = creatorName;
                                        }
                                    });
                                }
                            });
                            
                            // Find Zego avatar elements and update them
                            const zegoAvatars = zegoContainer.querySelectorAll('[class*="avatar"], [class*="Avatar"]');
                            zegoAvatars.forEach(function(avatar) {
                                const imgs = avatar.querySelectorAll('img');
                                imgs.forEach(function(img) {
                                    if (!img.src || img.src.includes('default') || img.src === '') {
                                        img.src = creatorAvatar;
                                        img.style.objectFit = 'cover';
                                        img.style.borderRadius = '50%';
                                    }
                                });
                            });
                        }
                    });
                    
                    // Start observing
                    observer.observe(mount, {
                        childList: true,
                        subtree: true
                    });
                    
                    zp.joinRoom({
                        container: mount,
                        scenario: { mode: window.ZegoUIKitPrebuilt.LiveStreaming, config: { role: 'Host' } },
                        showScreenSharingButton: true,
                        turnOnCameraWhenJoining: false,
                        turnOnMicrophoneWhenJoining: true,
                        showPreJoinView: false,
                        showTextChat: true,
                        showUserList: true,
                        showLeavingView: true,
                        sharedLinks: [{ name: 'Join from Dashboard', url: window.location.origin + '/?roomId=' + roomID }],
                        onLeaveRoom: function() {
                            observer.disconnect();
                            mount.remove();
                            goLiveBtn.textContent = 'Go Live';
                            // Reload the page via AJAX
                            window.location.reload();
                        }
                    });
                    goLiveBtn.textContent = 'Live Running';
                    if (pendingScheduleId && typeof window.nymiaMarkScheduleConsumed === 'function') {
                        window.nymiaMarkScheduleConsumed(pendingScheduleId);
                        streamForm.dataset.scheduleId = '';
                    }
                    
                    // Reload stream history after successful stream creation
                    setTimeout(loadStreamHistory, 1000);
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to start: ' + err.message);
                    if (pendingScheduleId) {
                        autoStartDisabledId = pendingScheduleId;
                        if (countdownHint) {
                            countdownHint.textContent = '<?php echo esc_js(__('Automatic start failed. Please use "Start now" to retry.', 'nymia')); ?>';
                        }
                    }
                    goLiveBtn.textContent = 'Go Live';
                })
                .finally(() => {
                    scheduledStartInFlight = '';
                    goLiveBtn.disabled = false;
                    streamForm.dataset.scheduleId = '';
                });
        });
    }
    
    // Audio Cover Image Upload Functionality
    const audioCoverInput = document.getElementById('audio_cover_image');
    const audioCoverBtn = document.getElementById('audioCoverImageBtn');
    const audioCoverPreview = document.getElementById('audioCoverImagePreview');
    const audioCoverImg = document.getElementById('audioCoverImageImg');
    const audioCoverRemove = document.getElementById('audioCoverImageRemove');
    
    if (audioCoverBtn && audioCoverInput) {
        // Click button to trigger file input
        audioCoverBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            audioCoverInput.click();
        });
        
        // File input change - handle preview
        audioCoverInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                
                // Validate image type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file.');
                    this.value = '';
                    return;
                }
                
                // Validate file size (max 5MB)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('Cover image size exceeds maximum limit of 5MB.');
                    this.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (audioCoverImg) {
                        audioCoverImg.src = e.target.result;
                    }
                    if (audioCoverPreview) {
                        audioCoverPreview.style.display = 'block';
                    }
                    if (audioCoverBtn) {
                        audioCoverBtn.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Remove cover image
        if (audioCoverRemove) {
            audioCoverRemove.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (audioCoverInput) {
                    audioCoverInput.value = '';
                }
                if (audioCoverPreview) {
                    audioCoverPreview.style.display = 'none';
                }
                if (audioCoverBtn) {
                    audioCoverBtn.style.display = 'flex';
                }
                if (audioCoverImg) {
                    audioCoverImg.src = '';
                }
            });
        }
    }
    
    // Audio upload form
    const audioForm = document.getElementById('audioUploadForm');
    
    if (audioForm) {
        audioForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(audioForm);
            const audioTitle = formData.get('audio_title');
            let file = formData.get('audio_file');
            
            // Check if audio is uploaded or recorded
            if (!file || !file.name) {
                // Check if we have a recorded audio file
                if (recordedAudioFile) {
                    file = recordedAudioFile;
                } else {
                    alert('Please upload or record an audio file before submitting.');
                    return;
                }
            }
            
            if (!audioTitle.trim()) {
                alert('Please enter an audio title.');
                return;
            }
            
            // Show loading state
            const submitBtn = audioForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            
            // Show progress bar
            const progressBar = document.querySelector('.nymia-upload-progress');
            const progressFill = document.querySelector('.nymia-progress-fill');
            const progressText = document.querySelector('.nymia-progress-text');
            const uploadContent = document.querySelector('.nymia-upload-content');
            
            if (progressBar && uploadContent) {
                uploadContent.style.display = 'none';
                progressBar.style.display = 'flex';
                progressFill.style.width = '0%';
                progressText.textContent = '0%';
            }
            
            // Prepare FormData for AJAX
            const uploadFormData = new FormData();
            uploadFormData.append('action', 'nymia_upload_audio');
            uploadFormData.append('nonce', nymiaAjax.nonce);
            uploadFormData.append('audio_title', audioTitle);
            uploadFormData.append('audio_file', file, file.name);
            uploadFormData.append('audio_paid_access', formData.get('audio_paid_access') ? 'yes' : 'no');
            uploadFormData.append('audio_price', formData.get('audio_price') || '0.00');
            
            // Parse category value (format: "category" or "category|subcategory")
            const categoryValue = formData.get('audio_category') || '';
            let audioCategory = '';
            let audioSubcategory = '';
            
            if (categoryValue.includes('|')) {
                const parts = categoryValue.split('|');
                audioCategory = parts[0] || '';
                audioSubcategory = parts[1] || '';
            } else {
                audioCategory = categoryValue;
                audioSubcategory = '';
            }
            
            uploadFormData.append('audio_category', audioCategory);
            uploadFormData.append('audio_subcategory', audioSubcategory);
            uploadFormData.append('audio_language', formData.get('audio_language') || '');
            
            // Add cover image if selected
            const coverImageFile = audioCoverInput ? audioCoverInput.files[0] : null;
            if (coverImageFile) {
                uploadFormData.append('audio_cover_image', coverImageFile);
            }
            
            // Create XMLHttpRequest for upload progress
            const xhr = new XMLHttpRequest();
            
            // Upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    if (progressFill) {
                        progressFill.style.width = percentComplete + '%';
                    }
                    if (progressText) {
                        progressText.textContent = Math.round(percentComplete) + '%';
                    }
                }
            });
            
            // Handle response
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            alert('Audio uploaded successfully!');
                            
                            // Reload page to show new upload
                            window.location.reload();
                        } else {
                            alert('Upload failed: ' + (data.data.message || 'Unknown error'));
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalBtnText;
                            if (progressBar && uploadContent) {
                                progressBar.style.display = 'none';
                                uploadContent.style.display = 'flex';
                            }
                            // Reset recording state
                            recordedAudioFile = null;
                        }
                    } catch (e) {
                        console.error('Response parse error:', e);
                        alert('Upload failed. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalBtnText;
                        if (progressBar && uploadContent) {
                            progressBar.style.display = 'none';
                            uploadContent.style.display = 'flex';
                        }
                        // Reset recording state
                        recordedAudioFile = null;
                    }
                } else {
                    alert('Upload failed. Server error.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                    if (progressBar && uploadContent) {
                        progressBar.style.display = 'none';
                        uploadContent.style.display = 'flex';
                    }
                    // Reset recording state
                    recordedAudioFile = null;
                }
            });
            
            xhr.addEventListener('error', function() {
                console.error('Upload error');
                alert('Upload failed. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                if (progressBar && uploadContent) {
                    progressBar.style.display = 'none';
                    uploadContent.style.display = 'flex';
                }
                // Reset recording state
                recordedAudioFile = null;
            });
            
            // Send request
            xhr.open('POST', nymiaAjax.ajaxurl);
            xhr.send(uploadFormData);
        });
    }
    
    // Join stream buttons (subscribers only enforcement server-side too)
    const joinButtons = document.querySelectorAll('.nymia-join-btn:not([disabled])');
    joinButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const ref = this.getAttribute('data-stream-ref') || '';
            const form = new FormData();
            form.append('action', 'nymia_join_live_stream');
            form.append('nonce', (window.nymiaAjax && window.nymiaAjax.streamNonce) || '');
            form.append('stream_ref', ref);
            fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', { method: 'POST', body: form })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        alert('Joined live stream!');
                    } else {
                        alert((data && data.data && data.data.message) || 'Unable to join stream');
                    }
                })
                .catch(() => alert('Network error. Please try again.'));
        });
    });
    
    // Global audio player
    let currentAudio = null;
    let currentlyPlayingBtn = null;
    
    // Play audio buttons
    const playButtons = document.querySelectorAll('.nymia-play-btn');
    playButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Playing audio...');
        });
    });
    
    // Compact play buttons - Play audio functionality
    const ajaxData = window.nymiaAjax || {};
    const createPageCurrentUserId = ajaxData.currentUserId ? parseInt(ajaxData.currentUserId, 10) : 0;
    const createPagePrivilegedRoles = Array.isArray(ajaxData.paidAudioPrivilegedRoles) ? ajaxData.paidAudioPrivilegedRoles : [];

    function canPlayPaidAudioFromCard(card) {
        if (!card) {
            return true;
        }

        const requiresPayment = card.dataset.paid === 'yes';
        const priceValue = parseFloat(card.dataset.price || '0');

        if (!requiresPayment || !(priceValue > 0)) {
            return true;
        }

        const cardCreatorId = parseInt(card.dataset.creatorId || '0', 10);
        const currentRoles = Array.isArray(ajaxData.currentUserRoles) ? ajaxData.currentUserRoles : [];

        if (createPageCurrentUserId && cardCreatorId && createPageCurrentUserId === cardCreatorId) {
            return true;
        }

        if (currentRoles.length && createPagePrivilegedRoles.length && currentRoles.some(role => createPagePrivilegedRoles.includes(role))) {
            return true;
        }

        alert('<?php echo esc_js(__('This track is premium content. Please purchase to listen.', 'nymia')); ?>');
        return false;
    }

    document.addEventListener('click', function(e) {
        const playBtn = e.target.closest('.nymia-compact-play-btn');
        if (!playBtn) return;
        
        const audioCard = playBtn.closest('.nymia-audio-post-card-compact');
        if (!audioCard) return;

        if (!canPlayPaidAudioFromCard(audioCard)) {
            return;
        }
        
        // Check if there's an associated audio file
        const cardData = audioCard.dataset.audioUrl;
        
        if (cardData) {
            // Check if this is the currently playing audio
            if (currentlyPlayingBtn === playBtn && currentAudio && !currentAudio.paused) {
                // Pause current audio
                currentAudio.pause();
                playBtn.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                `;
                return;
            }
            
            // Stop current audio if playing different audio
            if (currentAudio && currentlyPlayingBtn !== playBtn) {
                currentAudio.pause();
                
                // Reset previous button icon
                if (currentlyPlayingBtn) {
                    currentlyPlayingBtn.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    `;
                }
            }
            
            // Resume paused audio or create new audio
            if (currentAudio && currentlyPlayingBtn === playBtn && currentAudio.paused) {
                currentAudio.play();
            } else {
                // Create and play new audio
                currentAudio = new Audio(cardData);
                currentAudio.play();
            }
            
            // Update button to show pause icon
            playBtn.innerHTML = `
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
            `;
            
            currentlyPlayingBtn = playBtn;
            
            // When audio ends, reset button
            currentAudio.addEventListener('ended', function() {
                playBtn.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                `;
                currentAudio = null;
                currentlyPlayingBtn = null;
            });
        } else {
            // For demo purposes, show alert
            alert('Audio playback will be available when audio files are properly uploaded to the server.');
        }
    });
    
    // Private 1:1 sessions manager
    (function initPrivateSessionsManager(){
        const settingsForm = document.getElementById('nymiaPrivateSettingsForm');
        const singleForm = document.getElementById('nymiaPrivateSingleForm');
        const recurringForm = document.getElementById('nymiaPrivateRecurringForm');
        const slotsContainer = document.getElementById('nymiaPrivateSlots');
        const refreshBtn = document.getElementById('nymiaPrivateRefresh');
        const statusEl = document.querySelector('[data-private-status]');
        if (!settingsForm || !singleForm || !recurringForm || !slotsContainer) {
            return;
        }

        const ajaxUrl = (window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php';
        const nonce = '<?php echo wp_create_nonce('nymia_private_sessions'); ?>';

        function setPrivateStatus(message, type) {
            if (!statusEl) return;
            statusEl.textContent = message || '';
            statusEl.style.color = type === 'error' ? '#f87171' : '#9ca3af';
        }

        function fetchPrivateSlots() {
            const fd = new FormData();
            fd.append('action', 'nymia_private_get_slots');
            fd.append('nonce', nonce);
            slotsContainer.innerHTML = '<p style="color:#9ca3af;text-align:center;padding:16px;"><?php echo esc_js(__('Loading slots…', 'nymia')); ?></p>';
            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.success) {
                        throw new Error((data && data.data && data.data.message) || (data && data.message) || 'Error');
                    }
                    if (settingsForm && data.data?.settings) {
                        settingsForm.price.value = data.data.settings.price || '';
                        if (settingsForm.reminder_default && data.data.settings.reminder_default) {
                            settingsForm.reminder_default.value = data.data.settings.reminder_default;
                        }
                    }
                    renderPrivateSlots(data.data?.slots || []);
                })
                .catch(error => {
                    slotsContainer.innerHTML = `<p style=\"color:#f87171;text-align:center;padding:16px;\">${error.message}</p>`;
                });
        }

        function renderPrivateSlots(slots) {
            if (!slots.length) {
                slotsContainer.innerHTML = '<p style="color:#9ca3af;text-align:center;padding:16px;"><?php echo esc_js(__('No slots yet. Add one above.', 'nymia')); ?></p>';
                return;
            }
            slotsContainer.innerHTML = slots.map(slot => {
                const start = new Date((slot.start_timestamp || 0) * 1000);
                const end = new Date((slot.start_timestamp + (slot.duration || 0) * 60) * 1000);
                const booked = slot.status !== 'available';
                return `
                    <div class="nymia-private-slot-row">
                        <div>
                            <p class="nymia-slot-title">${start.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })} – ${end.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}</p>
                            <small><?php echo esc_js(__('Duration', 'nymia')); ?>: ${slot.duration} <?php echo esc_js(__('min', 'nymia')); ?> · <?php echo esc_js(__('Price', 'nymia')); ?> $${Number(slot.price || 0).toFixed(2)}</small>
                            ${booked ? `<p style="margin:4px 0 0;color:#fbbf24;font-size:0.85rem;"><?php echo esc_js(__('Booked', 'nymia')); ?></p>` : ''}
                        </div>
                        <div class="nymia-slot-actions">
                            <span class="nymia-slot-badge ${slot.status}">${booked ? '<?php echo esc_js(__('Booked', 'nymia')); ?>' : '<?php echo esc_js(__('Available', 'nymia')); ?>'}</span>
                            ${!booked ? `<button type="button" class="nymia-slot-remove" data-slot="${slot.id}"><?php echo esc_js(__('Remove', 'nymia')); ?></button>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            slotsContainer.querySelectorAll('.nymia-slot-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm('<?php echo esc_js(__('Remove this slot?', 'nymia')); ?>')) {
                        return;
                    }
                    const fd = new FormData();
                    fd.append('action', 'nymia_private_delete_slot');
                    fd.append('nonce', nonce);
                    fd.append('slot_id', this.dataset.slot);
                    fetch(ajaxUrl, { method: 'POST', body: fd })
                        .then(response => response.json())
                        .then(data => {
                            if (!data || !data.success) {
                                throw new Error((data && data.data && data.data.message) || (data && data.message) || 'Error');
                            }
                            renderPrivateSlots(data.data?.slots || []);
                        })
                        .catch(err => alert(err.message));
                });
            });
        }

        function submitPrivateForm(form) {
            const fd = new FormData(form);
            fd.append('action', 'nymia_private_add_slots');
            fd.append('nonce', nonce);
            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.success) {
                        throw new Error((data && data.data && data.data.message) || (data && data.message) || 'Error');
                    }
                    renderPrivateSlots(data.data?.slots || []);
                    form.reset();
                    setPrivateStatus('<?php echo esc_js(__('Slots updated.', 'nymia')); ?>');
                })
                .catch(err => alert(err.message));
        }

        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(settingsForm);
            fd.append('action', 'nymia_private_save_settings');
            fd.append('nonce', nonce);
            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.success) {
                        throw new Error((data && data.data && data.data.message) || (data && data.message) || 'Error');
                    }
                    setPrivateStatus('<?php echo esc_js(__('Settings saved.', 'nymia')); ?>');
                })
                .catch(err => setPrivateStatus(err.message, 'error'));
        });

        singleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitPrivateForm(singleForm);
        });

        recurringForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const hasWeekday = recurringForm.querySelector('input[name="weekdays[]"]:checked');
            if (!hasWeekday) {
                alert('<?php echo esc_js(__('Select at least one weekday.', 'nymia')); ?>');
                return;
            }
            submitPrivateForm(recurringForm);
        });

        if (refreshBtn) {
            refreshBtn.addEventListener('click', fetchPrivateSlots);
        }

        fetchPrivateSlots();
    })();
    
    // View All button
    const viewAllBtn = document.querySelector('.nymia-view-all-btn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            alert('Viewing all audio posts...');
        });
    }

    // ========================================
    // AUDIO BOOK FORM HANDLERS
    // ========================================
    
    // Paid access toggle for audio book
    const audiobookPaidAccessToggle = document.getElementById('audiobook_paid_access');
    const audiobookPriceInput = document.getElementById('audiobook_price');
    
    if (audiobookPaidAccessToggle && audiobookPriceInput) {
        audiobookPaidAccessToggle.addEventListener('change', function() {
            audiobookPriceInput.disabled = !this.checked;
            if (this.checked) {
                audiobookPriceInput.focus();
            }
        });
    }

    // Audio book file upload area
    const audiobookUploadArea = document.getElementById('audiobookUploadArea');
    const audiobookFileInput = document.getElementById('audiobookFileInput');
    
    if (audiobookUploadArea && audiobookFileInput) {
        audiobookUploadArea.addEventListener('click', function() {
            audiobookFileInput.click();
        });

        audiobookFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const uploadText = audiobookUploadArea.querySelector('.nymia-upload-text');
                if (uploadText) {
                    uploadText.textContent = file.name;
                }
            }
        });

        // Drag and drop
        audiobookUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#BF4C1A';
        });

        audiobookUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '';
        });

        audiobookUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '';
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                audiobookFileInput.files = files;
                const uploadText = this.querySelector('.nymia-upload-text');
                if (uploadText) {
                    uploadText.textContent = files[0].name;
                }
            }
        });
    }

    // Audio book thumbnail upload
    const audiobookThumbnailBtn = document.getElementById('audiobookThumbnailBtn');
    const audiobookThumbnailInput = document.getElementById('audiobook_thumbnail');
    const audiobookThumbnailPreview = document.getElementById('audiobookThumbnailPreview');
    const audiobookThumbnailImg = document.getElementById('audiobookThumbnailImg');
    const audiobookThumbnailRemove = document.getElementById('audiobookThumbnailRemove');

    if (audiobookThumbnailBtn && audiobookThumbnailInput) {
        audiobookThumbnailBtn.addEventListener('click', function() {
            audiobookThumbnailInput.click();
        });

        audiobookThumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (audiobookThumbnailImg) {
                        audiobookThumbnailImg.src = e.target.result;
                    }
                    if (audiobookThumbnailPreview) {
                        audiobookThumbnailPreview.style.display = 'block';
                    }
                    if (audiobookThumbnailBtn) {
                        audiobookThumbnailBtn.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (audiobookThumbnailRemove) {
        audiobookThumbnailRemove.addEventListener('click', function() {
            if (audiobookThumbnailInput) {
                audiobookThumbnailInput.value = '';
            }
            if (audiobookThumbnailImg) {
                audiobookThumbnailImg.src = '';
            }
            if (audiobookThumbnailPreview) {
                audiobookThumbnailPreview.style.display = 'none';
            }
            if (audiobookThumbnailBtn) {
                audiobookThumbnailBtn.style.display = 'block';
            }
        });
    }

    // Audio book form submission
    const audiobookUploadForm = document.getElementById('audiobookUploadForm');
    if (audiobookUploadForm) {
        audiobookUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'nymia_upload_audiobook');
            formData.append('nonce', document.getElementById('nymia_audiobook_nonce').value);
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = '<?php echo esc_js(__('Uploading...', 'nymia')); ?>';
            }

            fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    const successMsg = document.getElementById('audiobook-success-message');
                    if (successMsg) {
                        successMsg.style.display = 'flex';
                        setTimeout(() => {
                            successMsg.style.display = 'none';
                        }, 5000);
                    }
                    this.reset();
                    if (audiobookThumbnailPreview) {
                        audiobookThumbnailPreview.style.display = 'none';
                    }
                    if (audiobookThumbnailBtn) {
                        audiobookThumbnailBtn.style.display = 'block';
                    }
                    if (audiobookPriceInput) {
                        audiobookPriceInput.disabled = true;
                    }
                    if (audiobookPaidAccessToggle) {
                        audiobookPaidAccessToggle.checked = false;
                    }
                } else {
                    alert(data && data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Error uploading audio book. Please try again.', 'nymia')); ?>');
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    }
});
</script>

<style>
/* Ebook Category Dropdown with Sub-categories */
#ebook_category option {
    font-weight: 400;
    color: rgba(255, 255, 255, 0.85);
    padding: 8px 12px;
}

#ebook_category option[data-subcategory] {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.9rem;
}

#ebook_category option:hover,
#ebook_category option:focus {
    background: rgba(191, 76, 26, 0.2);
}

/* Audio Category Dropdown with Sub-categories */
#audio_category option {
    font-weight: 400;
    color: rgba(255, 255, 255, 0.85);
    padding: 8px 12px;
}

#audio_category option[data-subcategory] {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.9rem;
}

#audio_category option:hover,
#audio_category option:focus {
    background: rgba(191, 76, 26, 0.2);
}

/* Stream Thumbnail Upload Styles */
.stream-thumbnail-upload {
    position: relative;
    margin-top: 8px;
}

.stream-thumbnail-upload-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px dashed rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    width: 100%;
    justify-content: center;
}

.stream-thumbnail-upload-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.3);
    color: #fff;
}

.stream-thumbnail-upload-btn svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.stream-thumbnail-preview {
    position: relative;
    margin-top: 12px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.02);
}

.stream-thumbnail-preview img {
    width: 100%;
    height: auto;
    display: block;
    max-height: 300px;
    object-fit: cover;
}

.stream-thumbnail-remove {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    background: rgba(0, 0, 0, 0.7);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 10;
}

.stream-thumbnail-remove:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}

.stream-thumbnail-remove svg {
    width: 18px;
    height: 18px;
}

/* Online Now Thumbnail Upload Styles */
.online-thumbnail-upload {
    position: relative;
    margin-top: 8px;
}

.online-thumbnail-upload-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px dashed rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    width: 100%;
    justify-content: center;
}

.online-thumbnail-upload-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.3);
    color: #fff;
}

.online-thumbnail-upload-btn svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.online-thumbnail-preview {
    position: relative;
    margin-top: 12px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.02);
}

.online-thumbnail-preview img {
    width: 100%;
    height: auto;
    display: block;
    max-height: 300px;
    object-fit: cover;
}

.online-thumbnail-remove {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    background: rgba(0, 0, 0, 0.7);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 10;
}

.online-thumbnail-remove:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}

.online-thumbnail-remove svg {
    width: 18px;
    height: 18px;
}

/* Schedule Thumbnail Upload Styles */
.schedule-thumbnail-upload {
    position: relative;
    margin-top: 8px;
}

.schedule-thumbnail-upload-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px dashed rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    width: 100%;
    justify-content: center;
}

.schedule-thumbnail-upload-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.3);
    color: #fff;
}

.schedule-thumbnail-upload-btn svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.schedule-thumbnail-preview {
    position: relative;
    margin-top: 12px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.02);
}

.schedule-thumbnail-preview img {
    width: 100%;
    height: auto;
    display: block;
    max-height: 300px;
    object-fit: cover;
}

.schedule-thumbnail-remove {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    background: rgba(0, 0, 0, 0.7);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 10;
}

.schedule-thumbnail-remove:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}

.schedule-thumbnail-remove svg {
    width: 18px;
    height: 18px;
}

/* Edit Button Styles */
.nymia-compact-edit-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(191, 76, 26, 0.9);
    border: none;
    color: white;
    padding: 6px;
    border-radius: 6px;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    transition: all 0.2s ease;
}

.nymia-compact-edit-btn:hover {
    background: rgba(191, 76, 26, 1);
    transform: scale(1.1);
}

.nymia-compact-edit-btn svg {
    width: 14px;
    height: 14px;
}

/* Edit Modal Styles */
.nymia-edit-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.nymia-edit-modal.active {
    display: flex;
}

.nymia-edit-modal-content {
    background: #1E1E1E;
    border-radius: 16px;
    padding: 30px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}

.nymia-edit-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.nymia-edit-modal-header h2 {
    color: #fff;
    font-size: 1.5rem;
    margin: 0;
}

.nymia-edit-modal-close {
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: background 0.2s;
}

.nymia-edit-modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
}

.nymia-edit-modal-close svg {
    width: 24px;
    height: 24px;
}
</style>

<!-- Edit Audio Modal -->
<div class="nymia-edit-modal" id="editAudioModal">
    <div class="nymia-edit-modal-content">
        <div class="nymia-edit-modal-header">
            <h2><?php esc_html_e('Edit Audio', 'nymia'); ?></h2>
            <button class="nymia-edit-modal-close" onclick="closeEditAudioModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form id="editAudioForm" enctype="multipart/form-data">
            <?php wp_nonce_field('nymia_audio_upload', 'nymia_audio_upload_nonce'); ?>
            <input type="hidden" id="editAudioId" name="audio_id" value="">
            <div class="nymia-form-group">
                <label><?php esc_html_e('Title', 'nymia'); ?></label>
                <input type="text" id="editAudioTitle" name="audio_title" required />
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Category', 'nymia'); ?></label>
                <select id="editAudioCategory" name="audio_category">
                    <option value=""><?php esc_html_e('Select Category', 'nymia'); ?></option>
                    <option value="Lifestyle"><?php esc_html_e('Lifestyle', 'nymia'); ?></option>
                    <option value="Sports"><?php esc_html_e('Sports', 'nymia'); ?></option>
                    <option value="Music"><?php esc_html_e('Music', 'nymia'); ?></option>
                    <option value="Education"><?php esc_html_e('Education', 'nymia'); ?></option>
                    <option value="Entertainment"><?php esc_html_e('Entertainment', 'nymia'); ?></option>
                </select>
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Language', 'nymia'); ?></label>
                <select id="editAudioLanguage" name="audio_language">
                    <option value=""><?php esc_html_e('Select Language', 'nymia'); ?></option>
                    <option value="English"><?php esc_html_e('English', 'nymia'); ?></option>
                    <option value="Spanish"><?php esc_html_e('Spanish', 'nymia'); ?></option>
                    <option value="French"><?php esc_html_e('French', 'nymia'); ?></option>
                    <option value="German"><?php esc_html_e('German', 'nymia'); ?></option>
                    <option value="Italian"><?php esc_html_e('Italian', 'nymia'); ?></option>
                </select>
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Cover Image', 'nymia'); ?></label>
                <input type="file" id="editAudioCover" name="audio_cover_image" accept="image/*" />
                <div id="editAudioCoverPreview" style="margin-top: 12px; display: none;">
                    <img src="" alt="Preview" style="max-width: 200px; border-radius: 8px;" />
                </div>
            </div>
            <div class="nymia-form-group">
                <label>
                    <input type="checkbox" id="editAudioPaidAccess" name="audio_paid_access" />
                    <?php esc_html_e('Paid Access', 'nymia'); ?>
                </label>
            </div>
            <div class="nymia-form-group" id="editAudioPriceGroup" style="display: none;">
                <label><?php esc_html_e('Price', 'nymia'); ?></label>
                <input type="number" id="editAudioPrice" name="audio_price" step="0.01" min="0" value="0" />
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" class="nymia-btn-outline" onclick="closeEditAudioModal()" style="flex: 1;">
                    <?php esc_html_e('Cancel', 'nymia'); ?>
                </button>
                <button type="submit" class="nymia-btn-gradient" style="flex: 1;">
                    <?php esc_html_e('Save Changes', 'nymia'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Ebook Modal -->
<div class="nymia-edit-modal" id="editEbookModal">
    <div class="nymia-edit-modal-content">
        <div class="nymia-edit-modal-header">
            <h2><?php esc_html_e('Edit Ebook', 'nymia'); ?></h2>
            <button class="nymia-edit-modal-close" onclick="closeEditEbookModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form id="editEbookForm" enctype="multipart/form-data">
            <?php wp_nonce_field('nymia_upload_ebook', 'nymia_upload_ebook_nonce'); ?>
            <input type="hidden" id="editEbookId" name="ebook_id" value="">
            <div class="nymia-form-group">
                <label><?php esc_html_e('Title', 'nymia'); ?></label>
                <input type="text" id="editEbookTitle" name="ebook_title" required />
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Description', 'nymia'); ?></label>
                <textarea id="editEbookDescription" name="ebook_description" rows="4"></textarea>
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Category', 'nymia'); ?></label>
                <select id="editEbookCategory" name="ebook_category">
                    <option value=""><?php esc_html_e('Select Category', 'nymia'); ?></option>
                    <option value="Fiction"><?php esc_html_e('Fiction', 'nymia'); ?></option>
                    <option value="Non-Fiction"><?php esc_html_e('Non-Fiction', 'nymia'); ?></option>
                    <option value="Education"><?php esc_html_e('Education', 'nymia'); ?></option>
                    <option value="Business"><?php esc_html_e('Business', 'nymia'); ?></option>
                    <option value="Self-Help"><?php esc_html_e('Self-Help', 'nymia'); ?></option>
                </select>
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Language', 'nymia'); ?></label>
                <select id="editEbookLanguage" name="ebook_language">
                    <option value=""><?php esc_html_e('Select Language', 'nymia'); ?></option>
                    <option value="English"><?php esc_html_e('English', 'nymia'); ?></option>
                    <option value="Spanish"><?php esc_html_e('Spanish', 'nymia'); ?></option>
                    <option value="French"><?php esc_html_e('French', 'nymia'); ?></option>
                    <option value="German"><?php esc_html_e('German', 'nymia'); ?></option>
                    <option value="Italian"><?php esc_html_e('Italian', 'nymia'); ?></option>
                </select>
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Thumbnail', 'nymia'); ?></label>
                <input type="file" id="editEbookThumbnail" name="ebook_thumbnail" accept="image/*" />
                <div id="editEbookThumbnailPreview" style="margin-top: 12px; display: none;">
                    <img src="" alt="Preview" style="max-width: 200px; border-radius: 8px;" />
                </div>
            </div>
            <div class="nymia-form-group">
                <label>
                    <input type="checkbox" id="editEbookPaidAccess" name="ebook_paid_access" />
                    <?php esc_html_e('Paid Access', 'nymia'); ?>
                </label>
            </div>
            <div class="nymia-form-group" id="editEbookPriceGroup" style="display: none;">
                <label><?php esc_html_e('Price', 'nymia'); ?></label>
                <input type="number" id="editEbookPrice" name="ebook_price" step="0.01" min="0" value="0" />
            </div>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" class="nymia-btn-outline" onclick="closeEditEbookModal()" style="flex: 1;">
                    <?php esc_html_e('Cancel', 'nymia'); ?>
                </button>
                <button type="submit" class="nymia-btn-gradient" style="flex: 1;">
                    <?php esc_html_e('Save Changes', 'nymia'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Edit Audio Modal Functions
function openEditAudioModal(audioId) {
    const modal = document.getElementById('editAudioModal');
    const form = document.getElementById('editAudioForm');
    const audioIdInput = document.getElementById('editAudioId');
    
    audioIdInput.value = audioId;
    
    // Fetch audio data
    const formData = new FormData();
    formData.append('action', 'nymia_get_single_audio');
    formData.append('audio_id', audioId);
    formData.append('nonce', '<?php echo wp_create_nonce('nymia_audio_upload'); ?>');
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.audio) {
            const audio = data.data.audio;
            document.getElementById('editAudioTitle').value = audio.title || '';
            document.getElementById('editAudioCategory').value = audio.category || '';
            document.getElementById('editAudioLanguage').value = audio.language || '';
            document.getElementById('editAudioPaidAccess').checked = audio.paid_access === 'yes';
            document.getElementById('editAudioPrice').value = audio.price || 0;
            document.getElementById('editAudioPriceGroup').style.display = audio.paid_access === 'yes' ? 'block' : 'none';
            
            if (audio.cover_image) {
                const preview = document.getElementById('editAudioCoverPreview');
                preview.querySelector('img').src = audio.cover_image;
                preview.style.display = 'block';
            }
            
            modal.classList.add('active');
        } else {
            alert('<?php echo esc_js(__('Unable to load audio data.', 'nymia')); ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
    });
}

function closeEditAudioModal() {
    document.getElementById('editAudioModal').classList.remove('active');
    document.getElementById('editAudioForm').reset();
    document.getElementById('editAudioCoverPreview').style.display = 'none';
}

// Edit Ebook Modal Functions
function openEditEbookModal(ebookId) {
    const modal = document.getElementById('editEbookModal');
    const form = document.getElementById('editEbookForm');
    const ebookIdInput = document.getElementById('editEbookId');
    
    ebookIdInput.value = ebookId;
    
    // Fetch ebook data
    const formData = new FormData();
    formData.append('action', 'nymia_get_single_ebook');
    formData.append('ebook_id', ebookId);
    formData.append('nonce', '<?php echo wp_create_nonce('nymia_upload_ebook'); ?>');
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.ebook) {
            const ebook = data.data.ebook;
            document.getElementById('editEbookTitle').value = ebook.title || '';
            document.getElementById('editEbookDescription').value = ebook.description || '';
            document.getElementById('editEbookCategory').value = ebook.category || '';
            document.getElementById('editEbookLanguage').value = ebook.language || '';
            document.getElementById('editEbookPaidAccess').checked = ebook.paid_access === 'yes';
            document.getElementById('editEbookPrice').value = ebook.price || 0;
            document.getElementById('editEbookPriceGroup').style.display = ebook.paid_access === 'yes' ? 'block' : 'none';
            
            if (ebook.thumbnail) {
                const preview = document.getElementById('editEbookThumbnailPreview');
                preview.querySelector('img').src = ebook.thumbnail;
                preview.style.display = 'block';
            }
            
            modal.classList.add('active');
        } else {
            alert('<?php echo esc_js(__('Unable to load ebook data.', 'nymia')); ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
    });
}

function closeEditEbookModal() {
    document.getElementById('editEbookModal').classList.remove('active');
    document.getElementById('editEbookForm').reset();
    document.getElementById('editEbookThumbnailPreview').style.display = 'none';
}

// Form Submissions
document.addEventListener('DOMContentLoaded', function() {
    // Audio Edit Form
    const editAudioForm = document.getElementById('editAudioForm');
    if (editAudioForm) {
        editAudioForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(editAudioForm);
            formData.append('action', 'nymia_edit_audio');
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo esc_js(__('Audio updated successfully!', 'nymia')); ?>');
                    closeEditAudioModal();
                    window.location.reload();
                } else {
                    alert(data.data.message || '<?php echo esc_js(__('Failed to update audio.', 'nymia')); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            });
        });
        
        // Toggle price field
        document.getElementById('editAudioPaidAccess').addEventListener('change', function() {
            document.getElementById('editAudioPriceGroup').style.display = this.checked ? 'block' : 'none';
        });
        
        // Cover image preview
        document.getElementById('editAudioCover').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('editAudioCoverPreview');
                    preview.querySelector('img').src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Ebook Edit Form
    const editEbookForm = document.getElementById('editEbookForm');
    if (editEbookForm) {
        editEbookForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(editEbookForm);
            formData.append('action', 'nymia_edit_ebook');
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo esc_js(__('Ebook updated successfully!', 'nymia')); ?>');
                    closeEditEbookModal();
                    window.location.reload();
                } else {
                    alert(data.data.message || '<?php echo esc_js(__('Failed to update ebook.', 'nymia')); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            });
        });
        
        // Toggle price field
        document.getElementById('editEbookPaidAccess').addEventListener('change', function() {
            document.getElementById('editEbookPriceGroup').style.display = this.checked ? 'block' : 'none';
        });
        
        // Thumbnail preview
        document.getElementById('editEbookThumbnail').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('editEbookThumbnailPreview');
                    preview.querySelector('img').src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php get_footer(); ?>

