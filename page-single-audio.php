<?php
/**
 * ========================================
 * NYMIA THEME - SINGLE AUDIO CREATOR PAGE
 * ========================================
 * Displays individual audio creator page with:
 * - Creator profile header
 * - Audio player controls
 * - Track listing
 * - Playback functionality
 * 
 * @package Nymia
 * @version 1.0
 */

$incoming_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$incoming_creator_name = isset($_GET['creator_name']) ? sanitize_text_field($_GET['creator_name']) : '';
$incoming_track_id = isset($_GET['track_id']) ? intval($_GET['track_id']) : 0; // Specific track to auto-play

// IMPORTANT: Check for redirect BEFORE any WordPress functions that might send headers
// This must be the very first check after getting GET parameters
if (!is_page('single-audio') && empty($incoming_user_id) && empty($incoming_creator_name) && empty($incoming_track_id)) {
    // No valid parameters, redirect to home
    wp_redirect(home_url('/'));
    exit;
}

$creator_user = null;

// If track_id is provided, find the creator from the track
if ($incoming_track_id > 0) {
    // Search for the track in all audio transients to find the creator
    $all_audio = get_transient('nymia_all_audio');
    if ($all_audio && is_array($all_audio)) {
        foreach ($all_audio as $audio_item) {
            if (isset($audio_item['id']) && intval($audio_item['id']) === $incoming_track_id) {
                $track_creator_id = isset($audio_item['user_id']) ? intval($audio_item['user_id']) : 0;
                if ($track_creator_id > 0) {
                    $creator_user = get_user_by('id', $track_creator_id);
                    $incoming_user_id = $track_creator_id; // Update user_id from track
                    break;
                }
            }
        }
    }
    
    // If not found in all_audio, try to find in user-specific transients
    if (!$creator_user && $incoming_user_id > 0) {
        $user_audio = get_transient('nymia_user_audio_' . $incoming_user_id);
        if ($user_audio && is_array($user_audio)) {
            foreach ($user_audio as $audio_item) {
                if (isset($audio_item['id']) && intval($audio_item['id']) === $incoming_track_id) {
                    $creator_user = get_user_by('id', $incoming_user_id);
                    break;
                }
            }
        }
    }
}

// Fallback: use user_id if track_id didn't find creator
if (!$creator_user && $incoming_user_id) {
    $creator_user = get_user_by('id', $incoming_user_id);
}

if (!$creator_user && !empty($incoming_creator_name)) {
                            $user_query = new WP_User_Query(array(
        'search' => $incoming_creator_name,
                                'search_columns' => array('display_name', 'user_nicename', 'user_login'),
                                'number' => 1,
                            ));
                            $users_found = $user_query->get_results();
                            if (!empty($users_found)) {
                                $creator_user = $users_found[0];
                            }
                        }

$creator_id = $creator_user ? intval($creator_user->ID) : 0;
$creator_display_name = $creator_user ? ($creator_user->display_name ?: $creator_user->user_login) : __('Creator', 'nymia');

// If no valid creator found but user_id was provided, show error instead of redirect
if ($creator_id <= 0 && !empty($incoming_user_id)) {
    // Don't redirect - show the page with error message instead
    $creator_audio_posts = array();
} else {
    $creator_audio_posts = $creator_id ? get_transient('nymia_user_audio_' . $creator_id) : array();
    if (!$creator_audio_posts || !is_array($creator_audio_posts)) {
        $creator_audio_posts = array();
    }
}

$creator_profile_image = '';
if ($creator_id) {
    $custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
    $creator_profile_image = $custom_avatar ?: get_avatar_url($creator_id, array('size' => 300));
}
$hero_cover_image = '';
if (!empty($creator_audio_posts) && !empty($creator_audio_posts[0]['cover_image'])) {
    $hero_cover_image = $creator_audio_posts[0]['cover_image'];
}
$hero_default_cover = $creator_profile_image ?: get_template_directory_uri() . '/assets/images/audio-placeholder.jpg';
if (empty($hero_cover_image)) {
    $hero_cover_image = $hero_default_cover;
}

$current_user_id = get_current_user_id();
$show_follow_button = false;
if (!$current_user_id) {
    $show_follow_button = (bool) $creator_id;
} elseif ($creator_id && $current_user_id !== $creator_id) {
    if (function_exists('nymia_is_following')) {
        $show_follow_button = !nymia_is_following($current_user_id, $creator_id);
    } else {
        $show_follow_button = true;
    }
}

$total_views = 0;
foreach ($creator_audio_posts as $ap) {
    $total_views += isset($ap['views']) ? intval($ap['views']) : 0;
}
$views_formatted = $total_views >= 1000 ? round($total_views / 1000, 1) . 'k' : (string) $total_views;

$audio_tracks = array();
$track_index = 1;
foreach ($creator_audio_posts as $post) {
    $date_formatted = !empty($post['date']) ? date_i18n('M j, Y', strtotime($post['date'])) : date_i18n('M j, Y');
    $price_value = isset($post['price']) ? floatval($post['price']) : 0;
    $paid_access_flag = isset($post['paid_access']) ? $post['paid_access'] : 'no';
    $is_paid_track = ($paid_access_flag === 'yes' && $price_value > 0);
    $rating_value = isset($post['rating']) ? floatval($post['rating']) : 0;
    $review_count_value = isset($post['review_count']) ? intval($post['review_count']) : 0;
    if (!empty($post['id'])) {
        $stored_count = intval(get_post_meta($post['id'], '_nymia_audio_rating_count', true));
        $stored_sum = intval(get_post_meta($post['id'], '_nymia_audio_rating_sum', true));
        if (!$review_count_value && $stored_count) {
            $review_count_value = $stored_count;
        }
        if ((!$rating_value || $rating_value <= 0) && $stored_count > 0) {
            $rating_value = $stored_count ? round($stored_sum / $stored_count, 1) : 0;
        }
    }

    $audio_tracks[] = array(
        'id' => !empty($post['id']) ? intval($post['id']) : 0,
        'number' => $track_index++,
        'title' => isset($post['title']) ? $post['title'] : __('Untitled Track', 'nymia'),
        'artist' => $creator_display_name,
        'date' => $date_formatted,
        'duration' => !empty($post['duration']) ? $post['duration'] : '0:00',
        'url' => !empty($post['url']) ? $post['url'] : '',
        'cover_image' => !empty($post['cover_image']) ? $post['cover_image'] : '',
        'paid_access' => $paid_access_flag,
        'price' => $price_value,
        'is_paid' => $is_paid_track,
        'rating' => $rating_value,
        'review_count' => $review_count_value,
    );
}

if (empty($audio_tracks)) {
    $audio_tracks[] = array(
        'number' => 1,
        'title' => __('No audio uploaded yet', 'nymia'),
        'artist' => $creator_display_name,
        'date' => date_i18n('M j, Y'),
        'duration' => '0:00',
        'url' => '',
        'cover_image' => '',
        'paid_access' => 'no',
        'price' => 0,
        'is_paid' => false,
    );
}

$primary_title = !empty($creator_audio_posts) ? $creator_audio_posts[0]['title'] : __('Audio Collection', 'nymia');
$audio_review_ajax = array(
    'url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nymia_audio_review_nonce')
);

$requires_purchase = false;
foreach ($audio_tracks as $track_check) {
    if (!empty($track_check['is_paid'])) {
        $requires_purchase = true;
        break;
    }
}

// Check if specific track requires purchase and user doesn't have access
// For now, allow page to load but block playback of paid tracks
// Individual track access will be checked per track

// Now we can safely call get_header() after all redirects are handled
get_header(); ?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php
        add_filter('nymia_show_header_page_title', '__return_false');
        add_filter('nymia_header_page_title', '__return_empty_string');
        get_template_part('template-parts/header');
        remove_filter('nymia_show_header_page_title', '__return_false');
        remove_filter('nymia_header_page_title', '__return_empty_string');
        ?>
        
        <!-- ======================================== -->
        <!-- SINGLE AUDIO PAGE CONTAINER -->
        <!-- ======================================== -->
        <div class="nymia-single-audio-page" data-creator-id="<?php echo esc_attr($creator_id); ?>" data-current-user-id="<?php echo esc_attr($current_user_id); ?>">
            <!-- ======================================== -->
            <!-- AUDIO PLAYER HEADER -->
            <!-- ======================================== -->
            <div class="nymia-audio-player-header">
                <div class="nymia-audio-player-bg"></div>
                
                <div class="nymia-audio-player-content">
                    <!-- Creator Profile Image -->
                    <div class="nymia-audio-creator-image <?php echo nymia_is_creator_verified($creator_id) ? 'has-creator-badge' : ''; ?>" data-default-cover="<?php echo esc_attr($hero_default_cover); ?>">
                        <img src="<?php echo esc_url($hero_cover_image); ?>" alt="<?php echo esc_attr($creator_display_name); ?>" />
                    </div>
                    
                    <!-- Creator Info -->
                    <div class="nymia-audio-creator-info">
                        <h1 class="nymia-audio-title"><?php echo esc_html($primary_title); ?></h1>
                        <div class="nymia-creator-heading">
                        <p class="nymia-audio-creator-name"><?php echo esc_html($creator_display_name); ?></p>
                            <?php echo wp_kses_post(nymia_get_user_badge_markup($creator_id, null, 'nymia-creator-badge--inline')); ?>
                        </div>
                        <?php if ($show_follow_button): ?>
                            <button class="nymia-follow-btn" id="singleFollowBtn" data-creator-id="<?php echo esc_attr($creator_id); ?>"><?php esc_html_e('Follow', 'nymia'); ?></button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Engagement Stats -->
                    <div class="nymia-audio-stats">
                        <div class="nymia-stats-number" id="audioStatsNumber"><?php echo esc_html($views_formatted ?: '0'); ?></div>
                        <div class="nymia-stats-label"><?php esc_html_e('People Enjoy this', 'nymia'); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- ======================================== -->
            <!-- AUDIO PLAYER CONTROLS -->
            <!-- ======================================== -->
            <div class="nymia-audio-player-controls">
                <div class="nymia-player-main-controls">
                    <button class="nymia-player-btn nymia-prev-btn" id="prevBtn">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/>
                        </svg>
                    </button>
                    
                    <button class="nymia-player-btn nymia-main-play-btn" id="mainPlayBtn">
                        <svg class="play-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg class="pause-icon" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>
                    
                    <button class="nymia-player-btn nymia-next-btn" id="nextBtn">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
                        </svg>
                    </button>
                </div>
                
                <div class="nymia-player-progress-section">
                    <div class="nymia-current-track-info">
                        <div class="nymia-current-track-title">Select a track to play</div>
                        <div class="nymia-current-track-artist">Artist</div>
                    </div>
                    
                    <div class="nymia-player-progress-container">
                        <span class="nymia-current-time">0:00</span>
                        <div class="nymia-player-progress-bar" id="mainProgressBar">
                            <div class="nymia-player-progress-fill" id="mainProgressFill"></div>
                        </div>
                        <span class="nymia-total-time">0:00</span>
                    </div>
                </div>
                
                <div class="nymia-player-volume-section">
                    <button class="nymia-player-btn nymia-volume-btn" id="volumeBtn">
                        <svg class="volume-high" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
                        </svg>
                        <svg class="volume-mute" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                            <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
                        </svg>
                    </button>
                    
                    <div class="nymia-volume-slider-container">
                        <input type="range" class="nymia-volume-slider" id="volumeSlider" min="0" max="100" value="70">
                    </div>
                </div>
            </div>
            
            <!-- ======================================== -->
            <!-- AUDIO TRACK LIST -->
            <!-- ======================================== -->
            <div class="nymia-audio-track-list">
                <?php 
                // Build tracks dynamically from creator's uploaded audio
                $audio_tracks = array();
                $index = 1;
                foreach ($creator_audio_posts as $post) {
                    $date_formatted = !empty($post['date']) ? date_i18n('M j, Y', strtotime($post['date'])) : '';
                    $cover_image_value = !empty($post['cover_image']) ? $post['cover_image'] : '';
                    if (empty($cover_image_value) && !empty($post['id'])) {
                        $meta_cover = get_post_meta($post['id'], '_nymia_audio_cover_image', true);
                        if (!empty($meta_cover)) {
                            $cover_image_value = $meta_cover;
                        }
                    }
                    $rating_value = isset($post['rating']) ? floatval($post['rating']) : 0;
                    $review_count_value = isset($post['review_count']) ? intval($post['review_count']) : 0;
                    if (!empty($post['id'])) {
                        $stored_count = intval(get_post_meta($post['id'], '_nymia_audio_rating_count', true));
                        $stored_sum = intval(get_post_meta($post['id'], '_nymia_audio_rating_sum', true));
                        if (!$review_count_value && $stored_count) {
                            $review_count_value = $stored_count;
                        }
                        if ((!$rating_value || $rating_value <= 0) && $stored_count > 0) {
                            $rating_value = $stored_count ? round($stored_sum / $stored_count, 1) : 0;
                        }
                    }
                $audio_tracks[] = array(
                    'id' => !empty($post['id']) ? intval($post['id']) : 0,
                    'number' => $index++,
                    'title' => $post['title'],
                    'artist' => $creator_display_name,
                    'date' => $date_formatted,
                    'duration' => !empty($post['duration']) ? $post['duration'] : '0:00',
                    'url' => !empty($post['url']) ? $post['url'] : '',
                    'cover_image' => $cover_image_value,
                    'paid_access' => !empty($post['paid_access']) ? $post['paid_access'] : 'no',
                    'price' => isset($post['price']) ? floatval($post['price']) : 0,
                    'rating' => $rating_value,
                    'review_count' => $review_count_value,
                );
                }
                
                // Fallback demo track if none exist to preserve layout
                if (empty($audio_tracks)) {
                $audio_tracks[] = array(
                    'number' => 1,
                    'title' => 'No audio uploaded yet',
                    'artist' => $creator_display_name,
                    'date' => date_i18n('M j, Y'),
                    'duration' => '0:00',
                    'url' => '',
                    'cover_image' => '',
                    'paid_access' => 'no',
                    'price' => 0,
                    'rating' => 0,
                    'review_count' => 0,
                );
                }
                
                foreach ($audio_tracks as $track): ?>
                <?php 
                // Determine if this track requires payment
                // Check paid_access flag and price (same logic as ebook)
                $paid_access_flag = isset($track['paid_access']) ? $track['paid_access'] : 'no';
                $price_value = isset($track['price']) ? floatval($track['price']) : 0;
                $track_is_paid = ($paid_access_flag === 'yes' && $price_value > 0);
                
                // Check if current user is the creator (creators can always access their own tracks)
                $is_creator = ($current_user_id > 0 && $current_user_id === $creator_id);
                
                // Determine if current user has access to this track
                // Free tracks are accessible to everyone
                $track_has_access = !$track_is_paid;
                
                // If track is paid, check access (same logic as ebook)
                if ($track_is_paid) {
                    // Creator can always access their own paid tracks
                    if ($is_creator) {
                        $track_has_access = true;
                    } 
                    // Check if logged-in user has purchased this specific track
                    elseif (is_user_logged_in() && !empty($track['id']) && $track['id'] > 0 && function_exists('nymia_user_has_audio_access')) {
                        $track_has_access = nymia_user_has_audio_access($current_user_id, $track['id']);
                        // Debug: Also check if access was just granted (purchased=1 in URL)
                        if (!$track_has_access && isset($_GET['purchased']) && $_GET['purchased'] == '1') {
                            // Force refresh access check after purchase
                            $track_has_access = nymia_user_has_audio_access($current_user_id, $track['id']);
                        }
                    }
                    // Logged-out users or users without purchase have NO access
                    else {
                        $track_has_access = false;
                    }
                }
                
                // Hide audio URL if track is paid and user doesn't have access
                $display_audio_url = isset($track['url']) ? $track['url'] : '';
                if ($track_is_paid && !$track_has_access) {
                    $display_audio_url = ''; // Hide URL for paid tracks without access
                }
                ?>
                <div class="nymia-audio-track-item" data-track="<?php echo esc_attr($track['number']); ?>" data-audio-id="<?php echo esc_attr(isset($track['id']) ? $track['id'] : 0); ?>" data-title="<?php echo esc_attr($track['title']); ?>" data-artist="<?php echo esc_attr($track['artist']); ?>" data-duration="<?php echo esc_attr($track['duration']); ?>" data-audio-url="<?php echo esc_attr($display_audio_url); ?>" data-cover-image="<?php echo esc_attr(!empty($track['cover_image']) ? $track['cover_image'] : ''); ?>" data-paid="<?php echo esc_attr($track['paid_access']); ?>" data-price="<?php echo esc_attr($track['price']); ?>" data-creator-id="<?php echo esc_attr($creator_id); ?>" data-has-access="<?php echo $track_has_access ? '1' : '0'; ?>" data-rating="<?php echo esc_attr(isset($track['rating']) ? $track['rating'] : 0); ?>" data-review-count="<?php echo esc_attr(isset($track['review_count']) ? $track['review_count'] : 0); ?>">
                    <div class="nymia-track-number"><?php echo esc_html($track['number']); ?></div>
                    
                    <div class="nymia-track-thumbnail">
                        <?php if (!empty($track['cover_image'])): ?>
                            <img src="<?php echo esc_url($track['cover_image']); ?>" alt="<?php echo esc_attr($track['title']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;" />
                        <?php else: ?>
                        <div class="nymia-dummy-track-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="nymia-track-info">
                        <h4 class="nymia-track-title"><?php echo esc_html($track['title']); ?></h4>
                        <p class="nymia-track-artist"><?php echo esc_html($track['artist']); ?></p>
                        <div class="nymia-track-rating-summary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <span class="nymia-track-rating-value">
                                <?php echo isset($track['rating']) && $track['rating'] > 0 ? esc_html(number_format((float)$track['rating'], 1)) : '—'; ?>
                            </span>
                            <?php
                            $review_count_display = isset($track['review_count']) ? intval($track['review_count']) : 0;
                            $review_label = sprintf(_n('%d review', '%d reviews', $review_count_display, 'nymia'), $review_count_display);
                            ?>
                            <span class="nymia-track-review-count"><?php echo esc_html($review_label); ?></span>
                        </div>
                        <?php if ($track_is_paid): ?>
                            <div class="nymia-track-cta">
                                <span class="nymia-track-badge <?php echo $track_has_access ? 'nymia-track-badge-unlocked' : 'nymia-track-badge-locked'; ?>">
                                    <?php if ($track_has_access): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                        <span><?php esc_html_e('Unlocked', 'nymia'); ?></span>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        <span><?php echo esc_html('$' . number_format($track['price'], 2)); ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="nymia-track-date"><?php echo esc_html($track['date']); ?></div>
                    
                    <div class="nymia-track-controls">
                        <?php if ($track_is_paid && !$track_has_access): ?>
                            <!-- Lock Overlay for Paid Tracks without Access -->
                            <div class="nymia-track-lock-overlay">
                                <div class="nymia-track-lock-content">
                                    <button type="button" class="nymia-track-lock-btn" data-action="open-audio-checkout" data-audio-id="<?php echo esc_attr($track['id']); ?>" data-audio-title="<?php echo esc_attr($track['title']); ?>" data-audio-price="<?php echo esc_attr($track['price'] ?? 0); ?>" data-audio-cover="<?php echo esc_attr($track['cover_image'] ?? ''); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        <span><?php esc_html_e('Unlock Track', 'nymia'); ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="nymia-clock-icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.9L16.2,16.2Z"/>
                            </svg>
                        </div>
                        
                        <div class="nymia-progress-time">0:00</div>
                        
                        <div class="nymia-track-progress">
                            <div class="nymia-progress-bar">
                                <div class="nymia-progress-fill"></div>
                            </div>
                        </div>
                        
                        <button class="nymia-play-btn" data-track="<?php echo esc_attr($track['number']); ?>">
                            <svg class="play-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <svg class="pause-icon" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                            </svg>
                        </button>
                        
                        <div class="nymia-track-duration"><?php echo esc_html($track['duration']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ======================================== -->
            <!-- AUDIO REVIEWS SECTION -->
            <!-- ======================================== -->
            <div class="nymia-audio-reviews" id="nymiaAudioReviews" data-ajax-url="<?php echo esc_url($audio_review_ajax['url']); ?>" data-nonce="<?php echo esc_attr($audio_review_ajax['nonce']); ?>">
                <div class="nymia-audio-reviews-header">
                    <div>
                        <p class="nymia-reviews-label"><?php esc_html_e('Track Rating', 'nymia'); ?></p>
                        <div class="nymia-reviews-average" id="nymiaReviewAverage">—</div>
                        <span class="nymia-reviews-count" id="nymiaReviewCount"><?php esc_html_e('Select a track to view reviews.', 'nymia'); ?></span>
                    </div>
                </div>

                <?php if (is_user_logged_in()): ?>
                <form class="nymia-review-form" id="nymiaReviewForm">
                    <input type="hidden" name="audio_id" id="nymiaReviewAudioId" value="">
                    <input type="hidden" name="rating" id="nymiaReviewRatingValue" value="0">
                    <div class="nymia-review-stars" id="nymiaReviewStars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="nymia-review-star" data-rating-value="<?php echo esc_attr($i); ?>" aria-label="<?php printf(esc_attr__('%d star', 'nymia'), $i); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <textarea name="comment" id="nymiaReviewComment" rows="3" placeholder="<?php esc_attr_e('Share your thoughts about this track...', 'nymia'); ?>"></textarea>
                    <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Submit Review', 'nymia'); ?></button>
                </form>
                <?php else: ?>
                    <p class="nymia-review-login-hint"><?php esc_html_e('Log in to leave a review.', 'nymia'); ?></p>
                <?php endif; ?>

                <div class="nymia-review-list" id="nymiaReviewList">
                    <p class="nymia-review-placeholder"><?php esc_html_e('Select a track to see listener feedback.', 'nymia'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Audio Element for Real Playback -->
<audio id="audioPlayer" preload="none">
    <source src="" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>

<style>
.nymia-track-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.nymia-track-badge svg {
    width: 14px;
    height: 14px;
}

.nymia-track-badge-locked {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, rgba(230, 116, 68, 0.15), rgba(191, 76, 26, 0.1));
    color: #e67444;
    border: 1px solid rgba(230, 116, 68, 0.3);
    margin-top: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(230, 116, 68, 0.15);
}

.nymia-track-badge-locked svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

.nymia-track-badge-unlocked {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, rgba(52, 211, 153, 0.2), rgba(16, 185, 129, 0.1));
    color: #34d399;
    border: 1px solid rgba(52, 211, 153, 0.4);
    margin-top: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(52, 211, 153, 0.15);
}

.nymia-track-badge-unlocked svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

.nymia-track-rating-summary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.65);
    margin-top: 6px;
}

.nymia-track-rating-summary svg {
    width: 16px;
    height: 16px;
    color: #fbbf24;
}

.nymia-track-rating-value {
    font-weight: 700;
}

.nymia-track-review-count {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.55);
}

.nymia-audio-reviews {
    margin-top: 40px;
    padding: 24px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
}

.nymia-audio-reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.nymia-reviews-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
}

.nymia-reviews-average {
    font-size: 2.5rem;
    font-weight: 600;
    margin: 0;
}

.nymia-reviews-count {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.6);
}

.nymia-review-form {
    margin-bottom: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.nymia-review-stars {
    display: inline-flex;
    gap: 6px;
}

.nymia-review-star {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.35);
    transition: color 0.2s ease;
}

.nymia-review-star.active {
    color: #fbbf24;
}

.nymia-review-star svg {
    width: 28px;
    height: 28px;
}

.nymia-review-form textarea {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    padding: 12px;
    color: #fff;
    resize: vertical;
}

.nymia-review-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.nymia-review-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.04);
}

.nymia-review-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.nymia-review-body {
    flex: 1;
}

.nymia-review-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 4px;
}

.nymia-review-comment {
    margin: 0 0 8px 0;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
}

.nymia-review-actions {
    display: flex;
    gap: 16px;
    align-items: center;
    margin-top: 8px;
}

.nymia-review-like-btn,
.nymia-review-reply-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
    border-radius: 16px;
}

.nymia-review-like-btn:hover,
.nymia-review-reply-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    color: rgba(255, 255, 255, 0.9);
}

.nymia-review-like-btn.liked {
    color: #ef4444;
}

.nymia-review-like-btn.liked:hover {
    background: rgba(239, 68, 68, 0.1);
}

.nymia-review-like-btn svg,
.nymia-review-reply-btn svg {
    width: 16px;
    height: 16px;
}

.nymia-review-like-count {
    font-size: 0.8125rem;
    font-weight: 500;
}

.nymia-review-reply-form {
    margin-top: 12px;
    margin-left: 60px;
}

.nymia-review-reply-form-content {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.nymia-review-reply-form textarea {
    flex: 1;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #fff;
    font-size: 0.875rem;
    font-family: inherit;
    resize: vertical;
    min-height: 40px;
}

.nymia-review-reply-form textarea:focus {
    outline: none;
    border-color: rgba(191, 76, 26, 0.5);
    background: rgba(255, 255, 255, 0.08);
}

.nymia-review-reply-submit-btn {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #BF4C1A, #9F2B1A);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.nymia-review-reply-submit-btn:hover {
    background: linear-gradient(135deg, #D14619, #8B2A0F);
    transform: scale(1.1);
}

.nymia-review-reply-submit-btn svg {
    width: 18px;
    height: 18px;
}

.nymia-review-replies {
    margin-top: 16px;
    margin-left: 60px;
    padding-left: 16px;
    border-left: 2px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.nymia-review-reply-item {
    display: flex;
    gap: 12px;
}

.nymia-review-placeholder,
.nymia-review-login-hint {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.95rem;
}

.nymia-audio-track-item[data-paid="yes"] .nymia-play-btn {
    position: relative;
}

.nymia-audio-track-item[data-paid="yes"] .nymia-play-btn::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 1px solid rgba(230, 116, 68, 0.35);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.nymia-audio-track-item[data-paid="yes"] .nymia-play-btn:hover::after {
    opacity: 1;
}

.nymia-track-cta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    flex-wrap: wrap;
}

/* Track Lock Overlay - Modern Clean Design */
.nymia-track-lock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px) saturate(180%);
    -webkit-backdrop-filter: blur(8px) saturate(180%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    opacity: 1;
    transition: all 0.3s ease;
    border: 1px solid rgba(230, 116, 68, 0.2);
}

.nymia-track-lock-overlay:hover {
    background: rgba(0, 0, 0, 0.9);
    border-color: rgba(230, 116, 68, 0.3);
}

.nymia-track-lock-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
    width: 100%;
    box-sizing: border-box;
    position: relative;
    z-index: 1;
    min-height: 100%;
}

.nymia-track-lock-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #e67444, #BF4C1A);
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 12px rgba(230, 116, 68, 0.3);
    position: relative;
    overflow: hidden;
    width: auto;
    min-width: 160px;
    white-space: nowrap;
}

.nymia-track-lock-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nymia-track-lock-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(230, 116, 68, 0.5);
    color: #ffffff;
    background: linear-gradient(135deg, #f08050, #CF3B2A);
}

.nymia-track-lock-btn:hover::before {
    opacity: 1;
}

.nymia-track-lock-btn:active {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(230, 116, 68, 0.4);
}

.nymia-track-lock-btn svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    display: block;
}

.nymia-track-lock-btn span {
    font-weight: 600;
    letter-spacing: 0.01em;
}

.nymia-track-controls {
    position: relative;
    min-height: 60px;
    overflow: visible;
}

@keyframes nymiaPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<script>
// Run after DOM is ready and after other scripts (like main.js) have loaded
(function() {
    // Use setTimeout to ensure this runs after main.js
    setTimeout(function() {
        // Get the main audio player element
        var audioPlayer = document.getElementById('audioPlayer');
        if (!audioPlayer) return;
        
        var currentPlayingTrack = null;
        var currentTrackElement = null;
        var bgEl = document.querySelector('.nymia-audio-player-bg');
        var trackedViews = {}; // Track which audio files have already had views incremented
        var pageElement = document.querySelector('.nymia-single-audio-page');
        var creatorId = pageElement ? parseInt(pageElement.getAttribute('data-creator-id') || '0', 10) : 0;
        var statsNumberEl = document.getElementById('audioStatsNumber');
        var heroImageWrapper = document.querySelector('.nymia-audio-creator-image');
        var heroImageEl = heroImageWrapper ? heroImageWrapper.querySelector('img') : null;
        var heroDefaultCover = heroImageWrapper ? heroImageWrapper.getAttribute('data-default-cover') : '';
        var reviewSection = document.getElementById('nymiaAudioReviews');
        var reviewForm = document.getElementById('nymiaReviewForm');
        var reviewAverageEl = document.getElementById('nymiaReviewAverage');
        var reviewCountEl = document.getElementById('nymiaReviewCount');
        var reviewListEl = document.getElementById('nymiaReviewList');
        var reviewStarsWrapper = document.getElementById('nymiaReviewStars');
        var reviewRatingInput = document.getElementById('nymiaReviewRatingValue');
        var reviewCommentInput = document.getElementById('nymiaReviewComment');
        var reviewAudioIdInput = document.getElementById('nymiaReviewAudioId');
        var currentAudioId = null;
        var reviewAjaxConfig = reviewSection ? {
            url: reviewSection.getAttribute('data-ajax-url'),
            nonce: reviewSection.getAttribute('data-nonce')
        } : null;
        var reviewStrings = <?php echo wp_json_encode(array(
            'selectTrack' => __('Select a track to view reviews.', 'nymia'),
            'loading' => __('Loading reviews…', 'nymia'),
            'error' => __('Unable to load reviews. Please try again.', 'nymia'),
            'noReviews' => __('No reviews yet. Be the first to share your thoughts.', 'nymia'),
            'noReviewsShort' => __('No reviews yet', 'nymia'),
            'loginRequired' => __('Log in to leave a review.', 'nymia'),
            'ratingRequired' => __('Please select a star rating before submitting.', 'nymia'),
            'submitError' => __('Unable to submit your review. Please try again.', 'nymia'),
            'thanks' => __('Thanks for sharing your feedback!', 'nymia'),
            'reviewSingular' => __('review', 'nymia'),
            'reviewPlural' => __('reviews', 'nymia'),
            'buttonSubmitting' => __('Submitting…', 'nymia')
        )); ?>;
        
        // Check if purchase was just completed - reload page to refresh access status
        (function(){
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('purchased') === '1') {
                // Show success message briefly, then reload page without purchased parameter
                setTimeout(function(){
                    var cleanUrl = window.location.pathname;
                    var newParams = new URLSearchParams(window.location.search);
                    newParams.delete('purchased');
                    var newQuery = newParams.toString();
                    if (newQuery) {
                        cleanUrl += '?' + newQuery;
                    }
                    if (window.location.hash) {
                        cleanUrl += window.location.hash;
                    }
                    // Reload page to refresh access status from database
                    window.location.href = cleanUrl;
                }, 1000); // Wait 1 second to ensure access was saved
                return; // Exit early
            }
        })();
        
        // Auto-play specific track if track_id is in URL (only if track has access or is free)
        var incomingTrackId = <?php echo json_encode($incoming_track_id); ?>;
        if (incomingTrackId > 0) {
            // Wait for tracks to be rendered, then find and play the track
            setTimeout(function() {
                var trackElement = document.querySelector('.nymia-audio-track-item[data-audio-id="' + incomingTrackId + '"]');
                if (trackElement) {
                    // Check if track has access before auto-playing
                    var trackHasAccess = trackElement.getAttribute('data-has-access') === '1';
                    var requiresPayment = trackElement.getAttribute('data-paid') === 'yes';
                    var priceValue = parseFloat(trackElement.getAttribute('data-price') || '0');
                    var audioUrl = trackElement.getAttribute('data-audio-url');
                    
                    // Scroll track into view
                    trackElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Highlight the track briefly
                    trackElement.style.transition = 'background-color 0.3s ease';
                    trackElement.style.backgroundColor = 'rgba(191, 76, 26, 0.2)';
                    setTimeout(function() {
                        trackElement.style.backgroundColor = '';
                    }, 2000);
                    
                    // Only auto-play if track is free OR user has access AND audio URL exists
                    if ((!requiresPayment || priceValue <= 0 || trackHasAccess) && audioUrl && audioUrl.trim() !== '') {
                        // Auto-play the track
                        setTimeout(function() {
                            playTrack(trackElement);
                        }, 500);
                    } else {
                        // Track requires purchase - don't auto-play, just highlight it
                        console.log('Track requires purchase or no access. Auto-play skipped.');
                    }
                }
            }, 1000);
        }
        function highlightSelectedStars(value) {
            if (!reviewStarsWrapper) return;
            var stars = reviewStarsWrapper.querySelectorAll('.nymia-review-star');
            stars.forEach(function(star) {
                var starValue = parseInt(star.getAttribute('data-rating-value') || '0', 10);
                if (starValue <= value) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        function setReviewFormState(isEnabled) {
            if (!reviewForm) return;
            var controls = reviewForm.querySelectorAll('textarea, button');
            controls.forEach(function(ctrl) {
                ctrl.disabled = !isEnabled;
                if (!isEnabled) {
                    ctrl.classList.add('disabled');
                } else {
                    ctrl.classList.remove('disabled');
                }
            });
            if (!isEnabled) {
                if (reviewRatingInput) reviewRatingInput.value = 0;
                if (reviewCommentInput) reviewCommentInput.value = '';
                highlightSelectedStars(0);
            }
        }

        function resetReviewSection() {
            if (reviewAverageEl) reviewAverageEl.textContent = '—';
            if (reviewCountEl) reviewCountEl.textContent = reviewStrings.selectTrack;
            if (reviewListEl) {
                reviewListEl.innerHTML = '<p class="nymia-review-placeholder">' + reviewStrings.selectTrack + '</p>';
            }
        }

        function renderReviewList(reviews) {
            if (!reviewListEl) return;
            if (!reviews || reviews.length === 0) {
                reviewListEl.innerHTML = '<p class="nymia-review-placeholder">' + reviewStrings.noReviews + '</p>';
                return;
            }

            var fragment = document.createDocumentFragment();
            reviews.forEach(function(review) {
                var item = document.createElement('div');
                item.className = 'nymia-review-item';

                var avatar = document.createElement('img');
                avatar.className = 'nymia-review-avatar';
                avatar.src = review.avatar;
                avatar.alt = review.display_name;
                avatar.onerror = function() {
                    this.onerror = null;
                    this.src = '<?php echo esc_js(get_template_directory_uri() . '/assets/images/profile.png'); ?>';
                };

                var body = document.createElement('div');
                body.className = 'nymia-review-body';

                var meta = document.createElement('div');
                meta.className = 'nymia-review-meta';
                var nameSpan = document.createElement('strong');
                nameSpan.textContent = review.display_name;
                var ratingSpan = document.createElement('span');
                ratingSpan.textContent = '• ' + review.rating + '/5';
                var timeSpan = document.createElement('span');
                timeSpan.textContent = '• ' + review.time_human;
                meta.appendChild(nameSpan);
                meta.appendChild(ratingSpan);
                meta.appendChild(timeSpan);

                var commentP = document.createElement('p');
                commentP.className = 'nymia-review-comment';
                commentP.textContent = review.comment;

                body.appendChild(meta);
                body.appendChild(commentP);

                item.appendChild(avatar);
                item.appendChild(body);
                fragment.appendChild(item);
            });

            reviewListEl.innerHTML = '';
            reviewListEl.appendChild(fragment);
        }

        function updateTrackRatingSummary(audioId, average, count) {
            if (!audioId) return;
            var selector = '.nymia-audio-track-item[data-audio-id="' + audioId + '"]';
            var trackEl = document.querySelector(selector);
            if (!trackEl) return;

            if (typeof average !== 'undefined' && average !== null) {
                trackEl.setAttribute('data-rating', average);
            }
            if (typeof count !== 'undefined' && count !== null) {
                trackEl.setAttribute('data-review-count', count);
            }

            var ratingValueEl = trackEl.querySelector('.nymia-track-rating-value');
            var reviewCountEl = trackEl.querySelector('.nymia-track-review-count');

            if (ratingValueEl) {
                if (count && average) {
                    ratingValueEl.textContent = parseFloat(average).toFixed(1);
                } else {
                    ratingValueEl.textContent = '—';
                }
            }

            if (reviewCountEl) {
                if (!count || count <= 0) {
                    reviewCountEl.textContent = reviewStrings.noReviewsShort;
                } else {
                    reviewCountEl.textContent = (count === 1 ? '1 ' + reviewStrings.reviewSingular : count + ' ' + reviewStrings.reviewPlural);
                }
            }
        }

        function updateReviewUI(data) {
            if (!data) return;
            if (reviewAverageEl) {
                reviewAverageEl.textContent = data.count && data.average ? parseFloat(data.average).toFixed(1) : '—';
            }
            if (reviewCountEl) {
                if (data.count > 0) {
                    reviewCountEl.textContent = (data.count === 1 ? '1 ' + reviewStrings.reviewSingular : data.count + ' ' + reviewStrings.reviewPlural);
                } else {
                    reviewCountEl.textContent = reviewStrings.noReviews;
                }
            }
            renderReviewList(data.reviews);
            updateTrackRatingSummary(data.audio_id, data.average, data.count);
        }

        function loadAudioReviews(audioId) {
            if (!reviewAjaxConfig || !audioId) {
                resetReviewSection();
                return;
            }

            if (reviewAudioIdInput) {
                reviewAudioIdInput.value = audioId;
            }

            if (reviewListEl) {
                reviewListEl.innerHTML = '<p class="nymia-review-placeholder">' + reviewStrings.loading + '</p>';
            }

            var formData = new FormData();
            formData.append('action', 'nymia_fetch_audio_reviews');
            formData.append('audio_id', audioId);
            formData.append('nonce', reviewAjaxConfig.nonce);

            fetch(reviewAjaxConfig.url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) ? payload.data.message : reviewStrings.error);
                }
                updateReviewUI(payload.data);
            })
            .catch(function(error) {
                console.error('Review fetch failed:', error);
                if (reviewListEl) {
                    reviewListEl.innerHTML = '<p class="nymia-review-placeholder">' + reviewStrings.error + '</p>';
                }
            });
        }

        function handleReviewSubmit(event) {
            if (!reviewForm || !reviewAjaxConfig) return;
            event.preventDefault();
            if (!currentAudioId) {
                alert(reviewStrings.selectTrack);
                return;
            }
            var ratingValue = reviewRatingInput ? parseInt(reviewRatingInput.value || '0', 10) : 0;
            if (!ratingValue) {
                alert(reviewStrings.ratingRequired);
                return;
            }

            var formData = new FormData(reviewForm);
            formData.append('action', 'nymia_submit_audio_review');
            formData.append('nonce', reviewAjaxConfig.nonce);

            var submitButton = reviewForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = reviewStrings.buttonSubmitting;
            }

            fetch(reviewAjaxConfig.url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) ? payload.data.message : reviewStrings.submitError);
                }
                if (reviewCommentInput) reviewCommentInput.value = '';
                highlightSelectedStars(ratingValue);
                updateReviewUI(payload.data);
                alert(reviewStrings.thanks);
            })
            .catch(function(error) {
                console.error('Review submit failed:', error);
                alert(error.message || reviewStrings.submitError);
            })
            .finally(function() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = '<?php echo esc_js(__('Submit Review', 'nymia')); ?>';
                }
            });
        }

        if (reviewForm) {
            reviewForm.addEventListener('submit', handleReviewSubmit);
        }

        if (reviewStarsWrapper && reviewRatingInput) {
            reviewStarsWrapper.addEventListener('click', function(e) {
                var starBtn = e.target.closest('.nymia-review-star');
                if (!starBtn) return;
                var value = parseInt(starBtn.getAttribute('data-rating-value') || '0', 10);
                reviewRatingInput.value = value;
                highlightSelectedStars(value);
            });
        }

        if (reviewForm) {
            setReviewFormState(false);
        } else {
            resetReviewSection();
        }
        
        if (!statsNumberEl) {
            console.warn('Stats number element not found!');
        }
        if (!creatorId) {
            console.warn('Creator ID not found!');
        }
        
        // Remove any existing event listeners from main.js by removing and re-adding buttons
        // Or better: override the handlers with our own
        
        // Function to increment audio views
        function incrementAudioViews(audioUrl) {
            console.log('incrementAudioViews called with:', { creatorId: creatorId, audioUrl: audioUrl, alreadyTracked: trackedViews[audioUrl] });
            
            if (!creatorId || !audioUrl) {
                console.warn('Cannot increment views: missing creatorId or audioUrl');
                return; // Invalid parameters
            }
            
            if (trackedViews[audioUrl]) {
                console.log('Views already tracked for this audio:', audioUrl);
                return; // Already tracked
            }
            
            // Mark as tracked immediately to prevent duplicate calls
            trackedViews[audioUrl] = true;
            
            // Get AJAX URL and nonce
            var ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';
            var ajaxNonce = (typeof nymiaAjax !== 'undefined' && nymiaAjax.nonce) ? nymiaAjax.nonce : '';
            
            if (!ajaxNonce) {
                console.error('AJAX nonce not found');
                delete trackedViews[audioUrl];
                return;
            }
            
            console.log('Sending AJAX request to increment views...');
            
            // Call AJAX to increment views
            var formData = new FormData();
            formData.append('action', 'nymia_increment_audio_views');
            formData.append('creator_id', creatorId);
            formData.append('audio_url', audioUrl);
            formData.append('nonce', ajaxNonce);
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                console.log('AJAX response status:', response.status);
                return response.json();
            })
            .then(function(data) {
                console.log('AJAX response data:', data);
                if (data && data.success) {
                    if (data.data && data.data.views_formatted) {
                        // Find stats element (in case it wasn't found initially)
                        var statsEl = document.getElementById('audioStatsNumber');
                        if (statsEl) {
                            // Update stats display with new formatted views
                            statsEl.textContent = data.data.views_formatted;
                            console.log('Stats updated to:', data.data.views_formatted);
                        } else {
                            console.error('Stats number element still not found when trying to update');
                        }
                    } else {
                        console.warn('No views_formatted in response');
                    }
                } else {
                    console.error('AJAX returned error:', data);
                    // Remove from tracked views on error so it can be retried
                    delete trackedViews[audioUrl];
                }
            })
            .catch(function(error) {
                console.error('Error incrementing audio views:', error);
                // Remove from tracked views on error so it can be retried
                delete trackedViews[audioUrl];
            });
        }
        
        // Function to stop all audio and reset UI
        function stopAllAudio() {
            if (audioPlayer) {
                audioPlayer.pause();
                audioPlayer.currentTime = 0;
                audioPlayer.src = '';
            }
            
            // Reset all track UI states
            var allTracks = document.querySelectorAll('.nymia-audio-track-item');
            allTracks.forEach(function(track) {
                track.classList.remove('active', 'playing');
                var playBtn = track.querySelector('.nymia-play-btn');
                if (playBtn) {
                    var playIcon = playBtn.querySelector('.play-icon');
                    var pauseIcon = playBtn.querySelector('.pause-icon');
                    if (playIcon) playIcon.style.display = 'block';
                    if (pauseIcon) pauseIcon.style.display = 'none';
                }
                
                // Reset progress
                var progressFill = track.querySelector('.nymia-progress-fill');
                if (progressFill) progressFill.style.width = '0%';
                var progressTime = track.querySelector('.nymia-progress-time');
                if (progressTime) progressTime.textContent = '0:00';
            });
            
            // Reset main play button
            var mainPlayBtn = document.getElementById('mainPlayBtn');
            if (mainPlayBtn) {
                var mainPlayIcon = mainPlayBtn.querySelector('.play-icon');
                var mainPauseIcon = mainPlayBtn.querySelector('.pause-icon');
                if (mainPlayIcon) mainPlayIcon.style.display = 'block';
                if (mainPauseIcon) mainPauseIcon.style.display = 'none';
            }
            
            // Reset main progress
            var mainProgressFill = document.getElementById('mainProgressFill');
            if (mainProgressFill) mainProgressFill.style.width = '0%';
            var currentTimeEl = document.querySelector('.nymia-current-time');
            if (currentTimeEl) currentTimeEl.textContent = '0:00';
            
            currentPlayingTrack = null;
            currentTrackElement = null;
        }
    
    // Function to play a specific track
    function playTrack(trackElement) {
        if (!trackElement || !audioPlayer) {
            console.error('Track element or audio player not found');
            return;
        }
        
        // FIRST: Check if user has access to this specific track (BEFORE checking audio URL)
        var trackHasAccess = trackElement.getAttribute('data-has-access') === '1';
        var requiresPayment = trackElement.getAttribute('data-paid') === 'yes';
        var priceValue = parseFloat(trackElement.getAttribute('data-price') || '0');
        var audioId = trackElement.getAttribute('data-audio-id');
        var audioUrl = trackElement.getAttribute('data-audio-url');
        
        // Block playback if track is paid and user doesn't have access
        if (requiresPayment && priceValue > 0 && !trackHasAccess) {
            // Check if purchase was just completed (purchased=1 in URL)
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('purchased') === '1') {
                // Reload page to refresh access status
                var cleanUrl = window.location.pathname + window.location.search.replace(/[?&]purchased=1/, '').replace(/[?&]$/, '');
                if (cleanUrl.indexOf('?') === -1 && window.location.search.indexOf('&') > -1) {
                    cleanUrl = cleanUrl.replace('&', '?');
                }
                window.location.href = cleanUrl;
                return;
            }
            
            // Check if we have a valid audio ID before redirecting to checkout
            if (!audioId || audioId === '0' || audioId === '') {
                alert('<?php echo esc_js(__('Invalid track. Unable to proceed with purchase.', 'nymia')); ?>');
                return;
            }
            
            // Redirect to checkout immediately
            var checkoutUrl = '<?php echo esc_url(home_url('/checkout?type=audio&id=')); ?>' + encodeURIComponent(audioId);
            var message = '<?php echo esc_js(__('This track requires purchase. Would you like to purchase it now?', 'nymia')); ?>';
            if (confirm(message)) {
                // Redirect to checkout page
                window.location.href = checkoutUrl;
            } else {
                alert('<?php echo esc_js(__('This track is premium content. Please purchase to listen.', 'nymia')); ?>');
            }
            return;
        }
        
        // Also check if audio URL is empty (this means track is locked)
        if (!audioUrl || audioUrl.trim() === '') {
            if (requiresPayment && priceValue > 0 && !trackHasAccess) {
                // Check if we have a valid audio ID
                if (audioId && audioId !== '0' && audioId !== '') {
                    // Redirect to checkout
                    var checkoutUrl2 = '<?php echo esc_url(home_url('/checkout?type=audio&id=')); ?>' + encodeURIComponent(audioId);
                    var message2 = '<?php echo esc_js(__('This track requires purchase. Would you like to purchase it now?', 'nymia')); ?>';
                    if (confirm(message2)) {
                        window.location.href = checkoutUrl2;
                    }
                } else {
                    alert('<?php echo esc_js(__('Invalid track. Unable to proceed with purchase.', 'nymia')); ?>');
                }
            } else {
                alert('<?php echo esc_js(__('Audio file not available for this track.', 'nymia')); ?>');
            }
            return;
        }

        var audioIdAttr = parseInt(audioId || '0', 10);
        currentAudioId = audioIdAttr > 0 ? audioIdAttr : null;
        if (currentAudioId) {
            if (reviewForm) {
                setReviewFormState(true);
            }
            loadAudioReviews(currentAudioId);
        } else {
            if (reviewForm) {
                setReviewFormState(false);
            }
            resetReviewSection();
        }
        
        console.log('Playing track:', trackElement.getAttribute('data-title'), 'URL:', audioUrl);
        
        // Stop any currently playing audio FIRST
        if (audioPlayer && !audioPlayer.paused) {
            audioPlayer.pause();
        }
        audioPlayer.currentTime = 0;
        audioPlayer.src = '';
        
        // Reset all UI states
        var allTracks = document.querySelectorAll('.nymia-audio-track-item');
        allTracks.forEach(function(track) {
            track.classList.remove('active', 'playing');
            var playBtn = track.querySelector('.nymia-play-btn');
            if (playBtn) {
                var playIcon = playBtn.querySelector('.play-icon');
                var pauseIcon = playBtn.querySelector('.pause-icon');
                if (playIcon) playIcon.style.display = 'block';
                if (pauseIcon) pauseIcon.style.display = 'none';
            }
            var progressFill = track.querySelector('.nymia-progress-fill');
            if (progressFill) progressFill.style.width = '0%';
            var progressTime = track.querySelector('.nymia-progress-time');
            if (progressTime) progressTime.textContent = '0:00';
        });
        
        // Double-check access before setting audio source (extra security)
        if (requiresPayment && priceValue > 0 && !trackHasAccess) {
            var checkoutUrl3 = '<?php echo esc_url(home_url('/checkout?type=audio&id=')); ?>' + encodeURIComponent(audioId || '');
            var message3 = '<?php echo esc_js(__('This track requires purchase. Would you like to purchase it now?', 'nymia')); ?>';
            if (confirm(message3)) {
                window.location.href = checkoutUrl3;
            }
            alert('<?php echo esc_js(__('This track is premium content. Please purchase to listen.', 'nymia')); ?>');
            return;
        }
        
        // Set current track
        currentPlayingTrack = trackElement.getAttribute('data-track');
        currentTrackElement = trackElement;
        
        // FINAL CHECK: Verify access before setting audio source (CRITICAL SECURITY CHECK)
        if (requiresPayment && priceValue > 0 && !trackHasAccess) {
            console.error('SECURITY: Attempted to play paid track without access', {
                trackId: audioId,
                hasAccess: trackHasAccess,
                requiresPayment: requiresPayment,
                price: priceValue
            });
            var checkoutUrl4 = '<?php echo esc_url(home_url('/checkout?type=audio&id=')); ?>' + encodeURIComponent(audioId || '');
            var message4 = '<?php echo esc_js(__('This track requires purchase. Redirecting to checkout...', 'nymia')); ?>';
            alert(message4);
            window.location.href = checkoutUrl4;
            return;
        }
        
        // Update audio source (only if access is granted AND URL is available)
        if (!audioUrl || audioUrl.trim() === '') {
            if (requiresPayment && priceValue > 0 && !trackHasAccess) {
                var checkoutUrl5 = '<?php echo esc_url(home_url('/checkout?type=audio&id=')); ?>' + encodeURIComponent(audioId || '');
                window.location.href = checkoutUrl5;
            } else {
                alert('<?php echo esc_js(__('Audio file not available for this track.', 'nymia')); ?>');
            }
            return;
        }
        
        // Set audio source only if we have confirmed access
        audioPlayer.src = audioUrl;
        audioPlayer.load();
        
        // Update UI immediately
        trackElement.classList.add('active', 'playing');
        var playBtn = trackElement.querySelector('.nymia-play-btn');
        if (playBtn) {
            var playIcon = playBtn.querySelector('.play-icon');
            var pauseIcon = playBtn.querySelector('.pause-icon');
            if (playIcon) playIcon.style.display = 'none';
            if (pauseIcon) pauseIcon.style.display = 'block';
        }
        
        // Update main play button
        var mainPlayBtn = document.getElementById('mainPlayBtn');
        if (mainPlayBtn) {
            var mainPlayIcon = mainPlayBtn.querySelector('.play-icon');
            var mainPauseIcon = mainPlayBtn.querySelector('.pause-icon');
            if (mainPlayIcon) mainPlayIcon.style.display = 'none';
            if (mainPauseIcon) mainPauseIcon.style.display = 'block';
        }
        
        // Update current track info in player
        updateCurrentTrackInfo(trackElement);
        
        // Update background image
        if (bgEl) {
            updateBackgroundFromTrack(trackElement);
        }
        
        // Store audioUrl for use in callbacks
        var currentAudioUrl = audioUrl;
        
        // Error handler for audio format and loading errors
        var audioErrorHandler = function() {
            if (audioPlayer.error) {
                var errorCode = audioPlayer.error.code;
                var errorMessage = 'Unable to play audio. ';
                
                switch(errorCode) {
                    case audioPlayer.error.MEDIA_ERR_ABORTED:
                        errorMessage += 'Playback was aborted.';
                        break;
                    case audioPlayer.error.MEDIA_ERR_NETWORK:
                        errorMessage += 'Network error. Please check your connection.';
                        break;
                    case audioPlayer.error.MEDIA_ERR_DECODE:
                        errorMessage += 'Audio file is corrupted or unsupported format.';
                        break;
                    case audioPlayer.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                        // Get file extension for better error message
                        var ext = '';
                        if (audioUrl) {
                            var parts = audioUrl.split('.');
                            if (parts.length > 1) {
                                ext = parts.pop().split('?')[0].toUpperCase();
                            }
                        }
                        errorMessage += 'Audio format (' + (ext || 'unknown') + ') is not supported by your browser. Please use MP3 format for best compatibility.';
                        break;
                    default:
                        errorMessage += 'Please check the audio file and try again.';
                }
                
                console.error('Audio error:', {
                    code: errorCode,
                    message: audioPlayer.error.message,
                    url: audioUrl
                });
                
                alert(errorMessage);
                stopAllAudio();
            }
        };
        
        // Remove any existing error handler and add new one
        audioPlayer.removeEventListener('error', audioErrorHandler);
        audioPlayer.addEventListener('error', audioErrorHandler, { once: true });
        
        // Wait for audio to load, then play
        audioPlayer.addEventListener('loadeddata', function playAfterLoad() {
            audioPlayer.removeEventListener('loadeddata', playAfterLoad);
            
            // Check for errors before attempting to play
            if (audioPlayer.error) {
                audioErrorHandler();
                return;
            }
            
            // Play the audio
            var playPromise = audioPlayer.play();
            if (playPromise !== undefined) {
                playPromise.then(function() {
                    console.log('Audio started playing successfully, URL:', currentAudioUrl);
                    // Increment views when audio successfully starts playing
                    if (currentAudioUrl) {
                        incrementAudioViews(currentAudioUrl);
                    } else {
                        console.warn('No audio URL to track views');
                    }
                }).catch(function(error) {
                    console.error('Error playing audio:', error);
                    
                    // Check if it's a format error
                    if (audioPlayer.error) {
                        audioErrorHandler();
                        return;
                    }
                    
                    // Handle autoplay policy errors
                    if (error.name === 'NotAllowedError' || error.name === 'NotSupportedError') {
                        console.log('Autoplay blocked - user interaction required');
                        // Reset UI but don't show error for autoplay blocks
                        trackElement.classList.remove('playing');
                        var playBtn2 = trackElement.querySelector('.nymia-play-btn');
                        if (playBtn2) {
                            var playIcon2 = playBtn2.querySelector('.play-icon');
                            var pauseIcon2 = playBtn2.querySelector('.pause-icon');
                            if (playIcon2) playIcon2.style.display = 'block';
                            if (pauseIcon2) pauseIcon2.style.display = 'none';
                        }
                        var mainPlayBtn2 = document.getElementById('mainPlayBtn');
                        if (mainPlayBtn2) {
                            var mainPlayIcon2 = mainPlayBtn2.querySelector('.play-icon');
                            var mainPauseIcon2 = mainPlayBtn2.querySelector('.pause-icon');
                            if (mainPlayIcon2) mainPlayIcon2.style.display = 'block';
                            if (mainPauseIcon2) mainPauseIcon2.style.display = 'none';
                        }
                        return;
                    }
                    
                    alert('Unable to play audio. Please check the audio file or try again.');
                    stopAllAudio();
                });
            }
        }, { once: true });
        
        // Fallback: if loadeddata doesn't fire, try playing after a timeout
        setTimeout(function() {
            // Check for errors first
            if (audioPlayer.error) {
                audioErrorHandler();
                return;
            }
            
            if (audioPlayer.readyState >= 2 && audioPlayer.src) { // HAVE_CURRENT_DATA
                var playPromise = audioPlayer.play();
                if (playPromise !== undefined) {
                    playPromise.then(function() {
                        console.log('Audio started playing (fallback), URL:', currentAudioUrl);
                        // Increment views in fallback case too
                        if (currentAudioUrl) {
                            incrementAudioViews(currentAudioUrl);
                        }
                    }).catch(function(error) {
                        console.error('Error playing audio (fallback):', error);
                        
                        // Check for audio element errors
                        if (audioPlayer.error) {
                            audioErrorHandler();
                            return;
                        }
                        
                        // Don't show alert for autoplay blocks in fallback
                        if (error.name === 'NotAllowedError' || error.name === 'NotSupportedError') {
                            console.log('Autoplay blocked (fallback)');
                            return;
                        }
                    });
                }
            } else if (audioPlayer.error) {
                // Error occurred during loading
                audioErrorHandler();
            }
        }, 500);
    }
    
    // Function to pause audio
    function pauseAudio() {
        if (audioPlayer) {
            audioPlayer.pause();
        }
        
        if (currentTrackElement) {
            currentTrackElement.classList.remove('playing');
            var playBtn = currentTrackElement.querySelector('.nymia-play-btn');
            if (playBtn) {
                var playIcon = playBtn.querySelector('.play-icon');
                var pauseIcon = playBtn.querySelector('.pause-icon');
                if (playIcon) playIcon.style.display = 'block';
                if (pauseIcon) pauseIcon.style.display = 'none';
            }
        }
        
        // Update main play button
        var mainPlayBtn = document.getElementById('mainPlayBtn');
        if (mainPlayBtn) {
            var mainPlayIcon = mainPlayBtn.querySelector('.play-icon');
            var mainPauseIcon = mainPlayBtn.querySelector('.pause-icon');
            if (mainPlayIcon) mainPlayIcon.style.display = 'block';
            if (mainPauseIcon) mainPauseIcon.style.display = 'none';
        }
    }
    
    // Function to resume audio
    function resumeAudio() {
        if (audioPlayer && currentTrackElement) {
            audioPlayer.play().catch(function(error) {
                console.error('Error resuming audio:', error);
            });
            currentTrackElement.classList.add('playing');
            
            var playBtn = currentTrackElement.querySelector('.nymia-play-btn');
            if (playBtn) {
                var playIcon = playBtn.querySelector('.play-icon');
                var pauseIcon = playBtn.querySelector('.pause-icon');
                if (playIcon) playIcon.style.display = 'none';
                if (pauseIcon) pauseIcon.style.display = 'block';
            }
            
            // Update main play button
            var mainPlayBtn = document.getElementById('mainPlayBtn');
            if (mainPlayBtn) {
                var mainPlayIcon = mainPlayBtn.querySelector('.play-icon');
                var mainPauseIcon = mainPlayBtn.querySelector('.pause-icon');
                if (mainPlayIcon) mainPlayIcon.style.display = 'none';
                if (mainPauseIcon) mainPauseIcon.style.display = 'block';
            }
        }
    }
    
    // Function to update current track info in player controls
    function updateCurrentTrackInfo(trackElement) {
        var title = trackElement.getAttribute('data-title') || '';
        var artist = trackElement.getAttribute('data-artist') || '';
        var duration = trackElement.getAttribute('data-duration') || '0:00';
        
        // Update main header title
        var mainTitleEl = document.querySelector('.nymia-audio-title');
        if (mainTitleEl) {
            mainTitleEl.textContent = title;
        }
        
        // Update player controls
        var titleEl = document.querySelector('.nymia-current-track-title');
        var artistEl = document.querySelector('.nymia-current-track-artist');
        var totalTimeEl = document.querySelector('.nymia-total-time');
        
        if (titleEl) titleEl.textContent = title;
        if (artistEl) artistEl.textContent = artist;
        if (totalTimeEl) totalTimeEl.textContent = duration;
    }
    
    // Remove all existing event listeners from play buttons and add our own
    var allPlayButtons = document.querySelectorAll('.nymia-audio-track-item .nymia-play-btn');
    allPlayButtons.forEach(function(playBtn) {
        // Clone the button to remove all event listeners
        var newBtn = playBtn.cloneNode(true);
        playBtn.parentNode.replaceChild(newBtn, playBtn);
        
        // Add our own event listener
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Prevent other handlers
            
            var trackElement = this.closest('.nymia-audio-track-item');
            if (!trackElement) return;
            
            var trackNumber = trackElement.getAttribute('data-track');
            var isPaid = trackElement.getAttribute('data-paid') === 'yes';
            var audioId = trackElement.getAttribute('data-audio-id');
            var audioUrl = trackElement.getAttribute('data-audio-url');
            
            // FIRST: Check if user has access to this specific track (BEFORE checking audio URL)
            var trackHasAccess = trackElement.getAttribute('data-has-access') === '1';
            var priceValue = parseFloat(trackElement.getAttribute('data-price') || '0');
            
            // Block playback if track is paid and user doesn't have access
            if (isPaid && priceValue > 0 && !trackHasAccess) {
                var checkoutUrl = '<?php echo esc_url(home_url('/checkout?type=audio&id=')); ?>' + encodeURIComponent(audioId || '');
                var message = '<?php echo esc_js(__('This track requires purchase. Would you like to purchase it now?', 'nymia')); ?>';
                if (confirm(message)) {
                    window.location.href = checkoutUrl;
                } else {
                    alert('<?php echo esc_js(__('This track is premium content. Please purchase to listen.', 'nymia')); ?>');
                }
                return;
            }
            
            // Check if audio URL is empty (hidden for paid tracks without access)
            if (!audioUrl || audioUrl.trim() === '') {
                if (isPaid && priceValue > 0 && !trackHasAccess) {
                    // Redirect to checkout
                    var checkoutUrl2 = '<?php echo esc_url(home_url('/checkout?type=audio&id=')); ?>' + encodeURIComponent(audioId || '');
                    var message2 = '<?php echo esc_js(__('This track requires purchase. Would you like to purchase it now?', 'nymia')); ?>';
                    if (confirm(message2)) {
                        window.location.href = checkoutUrl2;
                    } else {
                        alert('<?php echo esc_js(__('This track is premium content. Please purchase to listen.', 'nymia')); ?>');
                    }
                } else {
                    alert('<?php echo esc_js(__('Audio file not available for this track.', 'nymia')); ?>');
                }
                return;
            }
            
            // If clicking the same track, toggle play/pause
            if (currentPlayingTrack === trackNumber && audioPlayer && !audioPlayer.paused) {
                pauseAudio();
            } else if (currentPlayingTrack === trackNumber && audioPlayer && audioPlayer.paused) {
                resumeAudio();
            } else {
                // Play new track
                playTrack(trackElement);
            }
        }, true); // Use capture phase to run before other handlers
    });
    
    // Handle main play button
    var mainPlayBtn = document.getElementById('mainPlayBtn');
    if (mainPlayBtn) {
        mainPlayBtn.addEventListener('click', function() {
            if (!currentTrackElement) {
                // No track selected, play first available track
                var allTracks = document.querySelectorAll('.nymia-audio-track-item');
                for (var i = 0; i < allTracks.length; i++) {
                    var track = allTracks[i];
                    var trackHasAccess = track.getAttribute('data-has-access') === '1';
                    var isPaid = track.getAttribute('data-paid') === 'yes';
                    var priceValue = parseFloat(track.getAttribute('data-price') || '0');
                    var audioUrl = track.getAttribute('data-audio-url');
                    
                    // Skip paid tracks without access
                    if (isPaid && priceValue > 0 && !trackHasAccess) {
                        continue;
                    }
                    
                    // Skip tracks without audio URL
                    if (!audioUrl || audioUrl.trim() === '') {
                        continue;
                    }
                    
                    // Play first available track
                    playTrack(track);
                    return;
                }
                alert('<?php echo esc_js(__('No playable tracks available. Some tracks may require purchase.', 'nymia')); ?>');
            } else if (audioPlayer.paused) {
                resumeAudio();
            } else {
                pauseAudio();
            }
        });
    }
    
    // Handle prev/next buttons
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    
    // Helper function to find next/prev playable track
    function findPlayableTrack(direction) {
        if (!currentTrackElement) return null;
        
        var allTracks = Array.from(document.querySelectorAll('.nymia-audio-track-item'));
        var currentIndex = allTracks.indexOf(currentTrackElement);
        var step = direction === 'next' ? 1 : -1;
        var startIndex = currentIndex + step;
        
        // Wrap around if needed
        if (startIndex < 0) startIndex = allTracks.length - 1;
        if (startIndex >= allTracks.length) startIndex = 0;
        
        // Search for playable track
        for (var i = 0; i < allTracks.length; i++) {
            var checkIndex = (startIndex + (step * i) + allTracks.length) % allTracks.length;
            var track = allTracks[checkIndex];
            var trackHasAccess = track.getAttribute('data-has-access') === '1';
            var isPaid = track.getAttribute('data-paid') === 'yes';
            var priceValue = parseFloat(track.getAttribute('data-price') || '0');
            var audioUrl = track.getAttribute('data-audio-url');
            
            // Skip paid tracks without access
            if (isPaid && priceValue > 0 && !trackHasAccess) {
                continue;
            }
            
            // Skip tracks without audio URL
            if (!audioUrl || audioUrl.trim() === '') {
                continue;
            }
            
            return track;
        }
        
        return null;
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            var prevTrack = findPlayableTrack('prev');
            if (prevTrack) {
                playTrack(prevTrack);
            } else {
                alert('<?php echo esc_js(__('No previous playable track available.', 'nymia')); ?>');
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            var nextTrack = findPlayableTrack('next');
            if (nextTrack) {
                playTrack(nextTrack);
            } else {
                alert('<?php echo esc_js(__('No next playable track available.', 'nymia')); ?>');
            }
        });
    }
    
    // Progress bar seeking functionality
    var mainProgressBar = document.getElementById('mainProgressBar');
    if (mainProgressBar && audioPlayer) {
        mainProgressBar.addEventListener('click', function(e) {
            if (!audioPlayer.duration || !currentTrackElement) return;
            
            var rect = mainProgressBar.getBoundingClientRect();
            var clickX = e.clientX - rect.left;
            var percent = (clickX / rect.width) * 100;
            
            if (percent < 0) percent = 0;
            if (percent > 100) percent = 100;
            
            var newTime = (percent / 100) * audioPlayer.duration;
            audioPlayer.currentTime = newTime;
            
            // Update progress fill immediately
            var mainProgressFill = document.getElementById('mainProgressFill');
            if (mainProgressFill) {
                mainProgressFill.style.width = percent + '%';
            }
            
            // Update time displays
            var currentTimeEl = document.querySelector('.nymia-current-time');
            if (currentTimeEl) {
                var minutes = Math.floor(newTime / 60);
                var seconds = Math.floor(newTime % 60);
                currentTimeEl.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            }
            
            // Update track progress if exists
            var progressFill = currentTrackElement.querySelector('.nymia-progress-fill');
            if (progressFill) {
                progressFill.style.width = percent + '%';
            }
            var progressTime = currentTrackElement.querySelector('.nymia-progress-time');
            if (progressTime) {
                var minutes = Math.floor(newTime / 60);
                var seconds = Math.floor(newTime % 60);
                progressTime.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            }
        });
    }
    
    // Volume control functionality
    var volumeSlider = document.getElementById('volumeSlider');
    var volumeBtn = document.getElementById('volumeBtn');
    var savedVolume = 70; // Default volume
    
    // Initialize volume
    if (audioPlayer && volumeSlider) {
        audioPlayer.volume = savedVolume / 100;
        volumeSlider.value = savedVolume;
    }
    
    // Volume slider handler
    if (volumeSlider && audioPlayer) {
        volumeSlider.addEventListener('input', function() {
            var volume = parseInt(this.value, 10);
            audioPlayer.volume = volume / 100;
            savedVolume = volume;
            
            // Update volume button icon
            if (volumeBtn) {
                var volumeHigh = volumeBtn.querySelector('.volume-high');
                var volumeMute = volumeBtn.querySelector('.volume-mute');
                
                if (volume === 0) {
                    if (volumeHigh) volumeHigh.style.display = 'none';
                    if (volumeMute) volumeMute.style.display = 'block';
                } else {
                    if (volumeHigh) volumeHigh.style.display = 'block';
                    if (volumeMute) volumeMute.style.display = 'none';
                }
            }
        });
        
        // Prevent slider from changing volume when dragging on progress bar
        volumeSlider.addEventListener('mousedown', function(e) {
            e.stopPropagation();
        });
    }
    
    // Volume button (mute/unmute) handler
    if (volumeBtn && audioPlayer) {
        volumeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var volumeHigh = this.querySelector('.volume-high');
            var volumeMute = this.querySelector('.volume-mute');
            
            if (audioPlayer.volume > 0) {
                // Mute
                savedVolume = parseInt(volumeSlider.value, 10);
                audioPlayer.volume = 0;
                volumeSlider.value = 0;
                if (volumeHigh) volumeHigh.style.display = 'none';
                if (volumeMute) volumeMute.style.display = 'block';
            } else {
                // Unmute (restore previous volume or default to 70)
                var restoreVolume = savedVolume > 0 ? savedVolume : 70;
                audioPlayer.volume = restoreVolume / 100;
                volumeSlider.value = restoreVolume;
                if (volumeHigh) volumeHigh.style.display = 'block';
                if (volumeMute) volumeMute.style.display = 'none';
            }
        });
    }
    
    // Update progress bar and time as audio plays
    if (audioPlayer) {
        audioPlayer.addEventListener('timeupdate', function() {
            if (currentTrackElement && audioPlayer.duration > 0) {
                var current = audioPlayer.currentTime;
                var duration = audioPlayer.duration;
                
                // Update progress bar
                var progressFill = currentTrackElement.querySelector('.nymia-progress-fill');
                if (progressFill && duration > 0) {
                    var percent = (current / duration) * 100;
                    progressFill.style.width = percent + '%';
                }
                
                // Update time display
                var timeDisplay = currentTrackElement.querySelector('.nymia-progress-time');
                if (timeDisplay) {
                    var minutes = Math.floor(current / 60);
                    var seconds = Math.floor(current % 60);
                    timeDisplay.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                }
                
                // Update main player time
                var currentTimeEl = document.querySelector('.nymia-current-time');
                if (currentTimeEl) {
                    var minutes = Math.floor(current / 60);
                    var seconds = Math.floor(current % 60);
                    currentTimeEl.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                }
                
                // Update main progress bar
                var mainProgressFill = document.getElementById('mainProgressFill');
                if (mainProgressFill && duration > 0) {
                    var percent = (current / duration) * 100;
                    mainProgressFill.style.width = percent + '%';
                }
            }
        });
        
        // When audio ends, stop and reset
        audioPlayer.addEventListener('ended', function() {
            stopAllAudio();
            
            // Auto-play next track if available
            if (currentTrackElement) {
                var nextTrack = currentTrackElement.nextElementSibling;
                if (nextTrack && nextTrack.classList.contains('nymia-audio-track-item')) {
                    setTimeout(function() {
                        playTrack(nextTrack);
                    }, 500);
                }
            }
        });
    }
    
    // Function to update background image from track element
        function updateBackgroundFromTrack(trackElement) {
        if (!trackElement) return;
        
        var coverImage = trackElement.getAttribute('data-cover-image') || '';
        
        if (coverImage && bgEl) {
            var img = new Image();
            img.onload = function() {
                bgEl.style.backgroundImage = 'url(' + coverImage + ')';
                bgEl.style.backgroundSize = 'cover';
                bgEl.style.backgroundPosition = 'center';
                bgEl.style.backgroundRepeat = 'no-repeat';
                bgEl.style.opacity = '1';
                bgEl.style.transition = 'opacity 0.5s ease-in-out';
            };
            img.onerror = function() {
                bgEl.style.opacity = '0.3';
            };
            img.src = coverImage;
        } else if (bgEl) {
            bgEl.style.opacity = '0.3';
        }
        
        if (heroImageEl) {
            if (coverImage) {
                heroImageEl.src = coverImage;
            } else if (heroDefaultCover) {
                heroImageEl.src = heroDefaultCover;
            }
        }
    }
    
        // Utility: format seconds to M:SS
        function formatTime(seconds) {
            if (!isFinite(seconds) || seconds < 0) return '0:00';
            var mins = Math.floor(seconds / 60);
            var secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }

        // Preload and fill durations for each track from metadata
        function preloadDurations() {
            var tracks = document.querySelectorAll('.nymia-audio-track-item');
            tracks.forEach(function(track) {
                var url = track.getAttribute('data-audio-url');
                var existing = track.getAttribute('data-duration');
                if (!url) return;
                if (existing && existing !== '0:00') {
                    var existingEl = track.querySelector('.nymia-track-duration');
                    if (existingEl) existingEl.textContent = existing;
                    return;
                }
                try {
                    var tmp = new Audio();
                    tmp.preload = 'metadata';
                    tmp.src = url;
                    tmp.addEventListener('loadedmetadata', function() {
                        var d = tmp.duration || 0;
                        var dStr = formatTime(d);
                        var durEl = track.querySelector('.nymia-track-duration');
                        if (durEl) durEl.textContent = dStr;
                        track.setAttribute('data-duration', dStr);
                        // Release
                        tmp.src = '';
                    }, { once: true });
                    tmp.addEventListener('error', function() {
                        // ignore load errors for metadata
                    }, { once: true });
                } catch (e) {}
            });
        }

        // Update duration displays when the main audio loads metadata
        if (audioPlayer) {
            audioPlayer.addEventListener('loadedmetadata', function() {
                if (!currentTrackElement) return;
                var d = audioPlayer.duration || 0;
                var dStr = formatTime(d);
                var totalTimeEl = document.querySelector('.nymia-total-time');
                if (totalTimeEl) totalTimeEl.textContent = dStr;
                var durEl = currentTrackElement.querySelector('.nymia-track-duration');
                if (durEl) durEl.textContent = dStr;
                currentTrackElement.setAttribute('data-duration', dStr);
            });
        }

        // Run initializers
        preloadDurations();

        // Set initial background from first track if available
        var firstTrack = document.querySelector('.nymia-audio-track-item');
        if (firstTrack && bgEl) {
            updateBackgroundFromTrack(firstTrack);
        }

        // Follow button: toggle follow and hide on success
        try {
            var followBtn = document.getElementById('singleFollowBtn');
            if (followBtn) {
                followBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var creatorId = parseInt(followBtn.getAttribute('data-creator-id') || '0', 10);
                    if (!creatorId) return;

                    var ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';
                    var followNonce = (typeof nymiaAjax !== 'undefined' && nymiaAjax.followNonce) ? nymiaAjax.followNonce : '';

                    var originalText = followBtn.textContent;
                    followBtn.disabled = true;
                    followBtn.textContent = 'Following...';

                    fetch(ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'nymia_toggle_follow',
                            user_id: creatorId,
                            nonce: followNonce
                        })
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (data && data.success && data.data && data.data.is_following) {
                            // Smoothly hide button on successful follow
                            followBtn.style.transition = 'all 0.25s ease';
                            followBtn.style.opacity = '0';
                            followBtn.style.transform = 'translateY(-4px)';
                            setTimeout(function(){
                                followBtn.style.display = 'none';
                            }, 250);
                        } else {
                            followBtn.disabled = false;
                            followBtn.textContent = originalText;
                            if (data && data.data && data.data.message) {
                                alert(data.data.message);
                            }
                        }
                    })
                    .catch(function(err){
                        console.error('Follow error', err);
                        followBtn.disabled = false;
                        followBtn.textContent = originalText;
                        alert('Unable to follow right now. Please try again.');
                    });
                });
            }
        } catch (err) { console.error(err); }
    }, 100); // Run after 100ms to ensure main.js has loaded
})();
</script>

<!-- Audio Checkout Modal -->
<div id="nymiaAudioCheckoutModal" class="nymia-checkout-modal" style="position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:10000;">
    <div class="nymia-checkout-dialog" style="background:linear-gradient(135deg, #1a1a1a 0%, #141414 100%); border:1px solid rgba(255,255,255,0.1); width: min(520px, 92vw); border-radius:16px; overflow:hidden; color:#fff; box-shadow:0 25px 80px rgba(0,0,0,0.5);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.1);">
            <h3 style="margin:0; font-size:1.25rem; font-weight:700; color:#fff;">Unlock Audio Track</h3>
            <button class="nymia-checkout-close" aria-label="Close" style="background:transparent; border:none; color:#aaa; cursor:pointer; font-size:24px; line-height:1; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:6px; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='#aaa';">×</button>
        </div>
        <div id="nymiaAudioCheckoutContent" style="padding:24px;">
            <!-- Content will be populated by JavaScript -->
            <div style="text-align:center; padding:40px 20px;">
                <div style="width:48px; height:48px; margin:0 auto 16px; border:3px solid rgba(191,76,26,0.3); border-top-color:#BF4C1A; border-radius:50%; animation:spin 1s linear infinite;"></div>
                <p style="color:#bbb; margin:0;">Loading checkout...</p>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // Get Stripe publishable key from WordPress
    var stripePublishableKey = '<?php echo esc_js(get_option('nymia_stripe_publishable_key', '')); ?>';
    
    // Audio Checkout Modal Handler
    var audioCheckoutModal = document.getElementById('nymiaAudioCheckoutModal');
    var audioCheckoutContent = document.getElementById('nymiaAudioCheckoutContent');
    
    function openAudioCheckoutModal(audioId, audioTitle, audioPrice, audioCover) {
        if (!audioCheckoutModal || !audioCheckoutContent) return;
        
        // Get currency
        var currency = '<?php echo esc_js(strtoupper(get_option('nymia_stripe_currency', 'USD'))); ?>';
        var price = parseFloat(audioPrice || 0);
        var formattedPrice = '';
        
        // Format price based on currency
        var currencySymbol = '$';
        switch(currency) {
            case 'EUR': currencySymbol = '€'; break;
            case 'GBP': currencySymbol = '£'; break;
            case 'CAD': currencySymbol = 'CA$'; break;
            case 'AUD': currencySymbol = 'A$'; break;
            default: currencySymbol = '$';
        }
        formattedPrice = currencySymbol + price.toFixed(2);
        
        // Build modal content
        var modalHTML = '<div style="display:flex; flex-direction:column; gap:20px;">';
        
        // Track info
        modalHTML += '<div style="display:flex; gap:16px; align-items:center; padding:16px; background:rgba(255,255,255,0.05); border-radius:12px;">';
        if (audioCover) {
            modalHTML += '<img src="' + (audioCover || '') + '" alt="' + (audioTitle || 'Audio Track') + '" style="width:80px; height:80px; object-fit:cover; border-radius:8px;">';
        } else {
            modalHTML += '<div style="width:80px; height:80px; background:rgba(255,255,255,0.1); border-radius:8px; display:flex; align-items:center; justify-content:center;">';
            modalHTML += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:40px; height:40px; color:#666;">';
            modalHTML += '<path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"></path>';
            modalHTML += '</svg></div>';
        }
        modalHTML += '<div style="flex:1;">';
        modalHTML += '<h4 style="margin:0 0 6px 0; font-size:1.1rem; font-weight:600; color:#fff;">' + (audioTitle || 'Audio Track') + '</h4>';
        modalHTML += '<p style="margin:0; font-size:1.5rem; font-weight:700; color:#fff;">' + formattedPrice + '</p>';
        modalHTML += '</div></div>';
        
        // Purchase button
        if (!stripePublishableKey || stripePublishableKey === '') {
            modalHTML += '<div style="background:rgba(255,153,51,0.15); border:1px solid rgba(255,153,51,0.3); border-radius:12px; padding:16px; text-align:center;">';
            modalHTML += '<p style="margin:0; color:#ff9933; font-size:0.9rem;">Payment gateway is not configured. Please contact the administrator.</p>';
            modalHTML += '</div>';
        } else {
            modalHTML += '<div style="display:flex; align-items:start; gap:12px; padding:16px; background:rgba(255,255,255,0.05); border-radius:12px; margin-bottom:8px;">';
            modalHTML += '<input type="checkbox" id="audioCheckoutAgree" required style="margin-top:4px; width:20px; height:20px; cursor:pointer; accent-color:#BF4C1A;">';
            modalHTML += '<label for="audioCheckoutAgree" style="margin:0; color:#bbb; font-size:0.9rem; cursor:pointer; line-height:1.5;">';
            modalHTML += 'I agree to the <a href="<?php echo esc_url(home_url('/policies')); ?>" target="_blank" style="color:#BF4C1A; text-decoration:underline;">Terms and Privacy Policy</a>';
            modalHTML += '</label></div>';
            modalHTML += '<button type="button" id="nymiaAudioCheckoutBtn" class="nymia-btn-gradient" style="width:100%; padding:16px; border-radius:12px; border:none; background:linear-gradient(135deg,#BF4C1A,#9F2B1A); color:#fff; font-weight:700; font-size:1rem; cursor:pointer; transition:all 0.3s ease; display:flex; align-items:center; justify-content:center; gap:10px;">';
            modalHTML += '<span id="audioCheckoutBtnText">Pay with Stripe</span>';
            modalHTML += '<span id="audioCheckoutBtnLoading" style="display:none;">';
            modalHTML += '<svg style="width:20px; height:20px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
            modalHTML += '<circle cx="12" cy="12" r="10"></circle>';
            modalHTML += '<path d="M12 6v6l4 2"></path>';
            modalHTML += '</svg> Processing...</span>';
            modalHTML += '</button>';
        }
        
        modalHTML += '</div>';
        
        audioCheckoutContent.innerHTML = modalHTML;
        audioCheckoutModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Setup Stripe checkout button
        if (stripePublishableKey && stripePublishableKey !== '') {
            var checkoutBtn = document.getElementById('nymiaAudioCheckoutBtn');
            var checkoutBtnText = document.getElementById('audioCheckoutBtnText');
            var checkoutBtnLoading = document.getElementById('audioCheckoutBtnLoading');
            var agreeCheckbox = document.getElementById('audioCheckoutAgree');
            
            if (checkoutBtn) {
                checkoutBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    
                    if (!agreeCheckbox || !agreeCheckbox.checked) {
                        alert('Please agree to the Terms and Privacy Policy to continue.');
                        return;
                    }
                    
                    checkoutBtn.disabled = true;
                    if (checkoutBtnText) checkoutBtnText.style.display = 'none';
                    if (checkoutBtnLoading) checkoutBtnLoading.style.display = 'inline';
                    
                    var formData = new FormData();
                    formData.append('action', 'nymia_create_checkout_session');
                    formData.append('item_type', 'audio');
                    formData.append('item_id', audioId);
                    
                    if (typeof nymiaAjax !== 'undefined' && nymiaAjax.checkoutNonce) {
                        formData.append('nonce', nymiaAjax.checkoutNonce);
                    }
                    
                    var ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';
                    
                    fetch(ajaxUrl, { method: 'POST', body: formData })
                        .then(function(res){ return res.json(); })
                        .then(function(data){
                            if (data && data.success && data.data && data.data.sessionId) {
                                // Load Stripe.js if not loaded
                                if (typeof Stripe === 'undefined') {
                                    var script = document.createElement('script');
                                    script.src = 'https://js.stripe.com/v3/';
                                    script.onload = function(){
                                        var stripe = Stripe(stripePublishableKey);
                                        stripe.redirectToCheckout({ sessionId: data.data.sessionId })
                                            .then(function(result){
                                                if (result.error) {
                                                    alert(result.error.message);
                                                    checkoutBtn.disabled = false;
                                                    if (checkoutBtnText) checkoutBtnText.style.display = 'inline';
                                                    if (checkoutBtnLoading) checkoutBtnLoading.style.display = 'none';
                                                }
                                            });
                                    };
                                    document.head.appendChild(script);
                                } else {
                                    var stripe = Stripe(stripePublishableKey);
                                    stripe.redirectToCheckout({ sessionId: data.data.sessionId })
                                        .then(function(result){
                                            if (result.error) {
                                                alert(result.error.message);
                                                checkoutBtn.disabled = false;
                                                if (checkoutBtnText) checkoutBtnText.style.display = 'inline';
                                                if (checkoutBtnLoading) checkoutBtnLoading.style.display = 'none';
                                            }
                                        });
                                }
                            } else {
                                checkoutBtn.disabled = false;
                                if (checkoutBtnText) checkoutBtnText.style.display = 'inline';
                                if (checkoutBtnLoading) checkoutBtnLoading.style.display = 'none';
                                var errorMsg = (data && data.data && data.data.message) ? data.data.message : 'Failed to initiate checkout. Please try again.';
                                alert(errorMsg);
                            }
                        })
                        .catch(function(error){
                            checkoutBtn.disabled = false;
                            if (checkoutBtnText) checkoutBtnText.style.display = 'inline';
                            if (checkoutBtnLoading) checkoutBtnLoading.style.display = 'none';
                            console.error('Checkout session creation error:', error);
                            alert('Network error. Please check your connection and try again.');
                        });
                });
            }
        }
    }
    
    function closeAudioCheckoutModal() {
        if (!audioCheckoutModal) return;
        audioCheckoutModal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Open modal on button click
    document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-action="open-audio-checkout"]');
        if (btn) {
            e.preventDefault();
            var audioId = btn.getAttribute('data-audio-id');
            var audioTitle = btn.getAttribute('data-audio-title') || 'Audio Track';
            var audioPrice = btn.getAttribute('data-audio-price') || '0';
            var audioCover = btn.getAttribute('data-audio-cover') || '';
            openAudioCheckoutModal(audioId, audioTitle, audioPrice, audioCover);
        }
        
        // Close modal
        if (e.target && (e.target === audioCheckoutModal || e.target.closest('.nymia-checkout-close'))) {
            closeAudioCheckoutModal();
        }
    });
    
    // Close on ESC key
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && audioCheckoutModal && audioCheckoutModal.style.display === 'flex') {
            closeAudioCheckoutModal();
        }
    });
    
    // Add spin animation
    if (!document.getElementById('nymiaSpinAnimation')) {
        var style = document.createElement('style');
        style.id = 'nymiaSpinAnimation';
        style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }
})();
</script>

<style>
.nymia-checkout-modal {
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.nymia-checkout-dialog {
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<?php get_footer(); ?>
