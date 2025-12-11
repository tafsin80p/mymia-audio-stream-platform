<?php
/**
 * Template for displaying Live Audio Streaming page
 * Automatically joins ZegoCloud room after payment
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

get_header();

// Get room_id from URL
$room_id = isset($_GET['room_id']) ? sanitize_text_field($_GET['room_id']) : '';
$is_scheduled = isset($_GET['scheduled']) ? true : false;
$is_instant_call = isset($_GET['instant_call']) ? true : false;
$booked = isset($_GET['booked']) ? true : false;

$current_user = wp_get_current_user();
$current_user_id = get_current_user_id();

// Get ZegoCloud settings
$zego_app_id = get_option('nymia_zego_app_id', '');
$zego_server_secret = get_option('nymia_zego_server_secret', '');
$zego_env = get_option('nymia_zego_env', 'production');

// Get user info
$user_name = $current_user->display_name ?: $current_user->user_login;
$user_avatar = get_template_directory_uri() . '/assets/images/profile.png';
$custom_avatar = get_user_meta($current_user_id, 'custom_avatar', true);
if ($custom_avatar) {
    $user_avatar = esc_url($custom_avatar);
} else {
    $avatar_url = get_avatar_url($current_user_id, array('size' => 150));
    if ($avatar_url) {
        $user_avatar = esc_url($avatar_url);
    }
}

// Determine user role (Host or Audience)
$is_host = false;
$creator_id = 0;

if ($room_id) {
    // Check if user is the creator of this room
    if (strpos($room_id, 'scheduled_') === 0) {
        // Scheduled room format: scheduled_{creator_id}_{schedule_id}
        $parts = explode('_', $room_id, 3);
        if (count($parts) >= 3) {
            $creator_id = intval($parts[1]);
            $is_host = ($creator_id === $current_user_id);
        }
    } elseif (strpos($room_id, 'instant_') === 0) {
        // Instant call room format: instant_{creator_id}_{timestamp}_{random}
        $parts = explode('_', $room_id, 4);
        if (count($parts) >= 2) {
            $creator_id = intval($parts[1]);
            $is_host = ($creator_id === $current_user_id);
        }
    } else {
        // Regular room - check from Zego rooms
        $rooms = get_transient('nymia_zego_rooms');
        if (is_array($rooms) && isset($rooms[$room_id])) {
            $room_data = $rooms[$room_id];
            $creator_id = isset($room_data['creator']) ? intval($room_data['creator']) : (isset($room_data['creator_id']) ? intval($room_data['creator_id']) : 0);
            $is_host = ($creator_id === $current_user_id);
        }
    }
}

// Verify user has access to this room
$has_access = false;
if ($is_host) {
    $has_access = true; // Creator always has access
} else {
    // Check if this is a secret room and if it's free
    $is_secret_room = false;
    $room_price = 0;
    if (!$has_access) {
        $rooms = get_transient('nymia_zego_rooms');
        if (is_array($rooms) && isset($rooms[$room_id])) {
            $room_data = $rooms[$room_id];
            $is_secret_room = isset($room_data['is_secret']) && ($room_data['is_secret'] === true || $room_data['is_secret'] === '1' || $room_data['is_secret'] === 1);
            $room_price = isset($room_data['price']) ? floatval($room_data['price']) : 0;
        }
        
        // Allow free secret rooms to be joined without booking
        if ($is_secret_room && $room_price == 0) {
            $has_access = true;
        }
    }
    
    // Check if user has booked this room
    $booking_session_id = '';
    $is_per_minute_booking = false;
    if (!$has_access) {
        $bookings = get_user_meta($current_user_id, 'nymia_live_bookings', true);
        if (is_array($bookings)) {
            foreach ($bookings as $booking) {
                if (isset($booking['room_id']) && $booking['room_id'] === $room_id) {
                    // Check if booking is still valid
                    $payment_type = isset($booking['payment_type']) ? $booking['payment_type'] : 'full';
                    $expires_at = isset($booking['expires_at']) ? $booking['expires_at'] : '';
                    
                    // Full session bookings never expire - always allow access
                    if ($payment_type === 'full') {
                        $has_access = true;
                        $booking_session_id = isset($booking['session_id']) ? $booking['session_id'] : '';
                        break;
                    }
                    
                    // For per-minute bookings, check remaining minutes based on actual usage
                    if ($payment_type === 'per_minute') {
                        $minutes_purchased = isset($booking['minutes_purchased']) ? intval($booking['minutes_purchased']) : (isset($booking['minutes']) ? intval($booking['minutes']) : 0);
                        $minutes_used = isset($booking['minutes_used']) ? intval($booking['minutes_used']) : 0;
                        
                        // Also check if there's an active session that hasn't been closed
                        if (isset($booking['join_sessions']) && is_array($booking['join_sessions'])) {
                            $current_time = current_time('timestamp');
                            foreach ($booking['join_sessions'] as $session) {
                                if (isset($session['start']) && (!isset($session['end']) || $session['end'] == 0)) {
                                    // Active session - add current time to used minutes
                                    $active_session_seconds = $current_time - $session['start'];
                                    $active_session_minutes = ceil($active_session_seconds / 60);
                                    $minutes_used += $active_session_minutes;
                                }
                            }
                        }
                        
                        // Calculate remaining minutes
                        $remaining_minutes = $minutes_purchased - $minutes_used;
                        
                        // Allow access if there are remaining minutes
                        if ($remaining_minutes > 0) {
                            $has_access = true;
                            $booking_session_id = isset($booking['session_id']) ? $booking['session_id'] : '';
                            $is_per_minute_booking = true;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    // Also check instant calls
    $instant_call_session_id = '';
    if (!$has_access) {
        $calls = get_user_meta($current_user_id, 'nymia_instant_calls', true);
        if (is_array($calls)) {
            foreach ($calls as $call) {
                if (isset($call['room_id']) && $call['room_id'] === $room_id) {
                    // Allow access if status is pending_start or active
                    if (isset($call['status']) && ($call['status'] === 'active' || $call['status'] === 'pending_start')) {
                        $has_access = true;
                        $instant_call_session_id = isset($call['session_id']) ? $call['session_id'] : '';
                        break;
                    }
                }
            }
        }
    }
}

// If no room_id or no access, redirect
if (empty($room_id) || !$has_access) {
    wp_redirect(home_url('/'));
    exit;
}

// Get room title
$room_title = __('Live Audio Stream', 'nymia');
if ($is_instant_call) {
    $room_title = __('Instant Call', 'nymia');
} elseif ($is_scheduled) {
    $room_title = __('Scheduled Stream', 'nymia');
}

// Get creator info if not host
$creator_name = $user_name;
$creator_avatar = $user_avatar;
if (!$is_host && $creator_id > 0) {
    $creator_user = get_user_by('id', $creator_id);
    if ($creator_user) {
        $creator_name = $creator_user->display_name ?: $creator_user->user_login;
        $creator_custom_avatar = get_user_meta($creator_id, 'custom_avatar', true);
        if ($creator_custom_avatar) {
            $creator_avatar = esc_url($creator_custom_avatar);
        } else {
            $creator_avatar_url = get_avatar_url($creator_id, array('size' => 150));
            if ($creator_avatar_url) {
                $creator_avatar = esc_url($creator_avatar_url);
            }
        }
    }
}
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <div class="nymia-live-audio-wrapper">
            <!-- Stream Header -->
            <div class="nymia-stream-header">
                <div class="nymia-host-info">
                    <div class="nymia-host-avatar">
                        <img src="<?php echo esc_url($creator_avatar); ?>" alt="<?php echo esc_attr($creator_name); ?>" />
                    </div>
                    <div class="nymia-host-details">
                        <p class="nymia-host-label"><?php echo $is_host ? esc_html__('You are hosting', 'nymia') : esc_html__('Hosted by', 'nymia'); ?></p>
                        <h2 class="nymia-host-name"><?php echo esc_html($creator_name); ?></h2>
                    </div>
                </div>
            </div>

            <!-- ZegoCloud Container -->
            <div class="nymia-live-content">
                <div class="nymia-video-area">
                    <h1 class="nymia-stream-title"><?php echo esc_html($room_title); ?></h1>
                    
                    <!-- ZegoCloud will be mounted here -->
                    <div id="zego-live-container" style="width: 100%; min-height: 600px; background: #000; border-radius: 12px; overflow: hidden;"></div>
                    
                    <?php if ($booked): ?>
                    <div style="margin-top: 16px; padding: 12px; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 8px; color: #22c55e; text-align: center;">
                        <?php esc_html_e('Payment successful! Joining the call...', 'nymia'); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomID = '<?php echo esc_js($room_id); ?>';
    const userID = '<?php echo esc_js((string)$current_user_id); ?>';
    const userName = '<?php echo esc_js($user_name); ?>';
    const userAvatar = '<?php echo esc_js($user_avatar); ?>';
    const isHost = <?php echo $is_host ? 'true' : 'false'; ?>;
    const zegoAppId = '<?php echo esc_js($zego_app_id); ?>';
    const zegoEnv = '<?php echo esc_js($zego_env); ?>';
    const isInstantCall = <?php echo $is_instant_call ? 'true' : 'false'; ?>;
    const instantCallSessionId = '<?php echo esc_js($instant_call_session_id); ?>';
    const isPerMinuteBooking = <?php echo $is_per_minute_booking ? 'true' : 'false'; ?>;
    const bookingSessionId = '<?php echo esc_js($booking_session_id); ?>';
    let callStartTracked = false;
    let callEndTracked = false;
    let perMinuteJoinTracked = false;
    let perMinuteLeaveTracked = false;
    
    if (!roomID) {
        alert('<?php echo esc_js(__('Room ID is missing.', 'nymia')); ?>');
        window.location.href = '<?php echo esc_url(home_url('/')); ?>';
        return;
    }
    
    // Track call start for instant calls
    function trackCallStart() {
        if (!isInstantCall || !instantCallSessionId || callStartTracked) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'nymia_track_call_start');
        formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.trackCallNonce) || '');
        formData.append('session_id', instantCallSessionId);
        formData.append('room_id', roomID);
        
        fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                callStartTracked = true;
                console.log('Call start tracked');
            } else {
                console.error('Failed to track call start:', data);
            }
        })
        .catch(err => {
            console.error('Error tracking call start:', err);
        });
    }
    
    // Track call end for instant calls
    function trackCallEnd() {
        if (!isInstantCall || !instantCallSessionId || callEndTracked) {
            return;
        }
        
        callEndTracked = true; // Prevent duplicate calls
        
        const formData = new FormData();
        formData.append('action', 'nymia_track_call_end');
        formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.trackCallNonce) || '');
        formData.append('session_id', instantCallSessionId);
        formData.append('room_id', roomID);
        
        // Use sendBeacon for reliability on page unload
        if (navigator.sendBeacon) {
            const blob = new Blob([new URLSearchParams(formData).toString()], {
                type: 'application/x-www-form-urlencoded'
            });
            navigator.sendBeacon((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', blob);
        } else {
            // Fallback to fetch
            fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                keepalive: true
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    console.log('Call end tracked. Minutes:', data.data?.minutes_used, 'Amount:', data.data?.final_amount);
                } else {
                    console.error('Failed to track call end:', data);
                }
            })
            .catch(err => {
                console.error('Error tracking call end:', err);
            });
        }
    }
    
    // Track per-minute booking join
    function trackPerMinuteJoin() {
        if (!isPerMinuteBooking || !bookingSessionId || perMinuteJoinTracked) {
            return;
        }
        
        perMinuteJoinTracked = true;
        
        const formData = new FormData();
        formData.append('action', 'nymia_track_per_minute_join');
        formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.trackCallNonce) || '');
        formData.append('session_id', bookingSessionId);
        formData.append('room_id', roomID);
        
        fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                console.log('Per-minute join tracked');
            } else {
                console.error('Failed to track per-minute join:', data);
            }
        })
        .catch(err => {
            console.error('Error tracking per-minute join:', err);
        });
    }
    
    // Track per-minute booking leave
    function trackPerMinuteLeave() {
        if (!isPerMinuteBooking || !bookingSessionId || perMinuteLeaveTracked) {
            return;
        }
        
        perMinuteLeaveTracked = true;
        
        const formData = new FormData();
        formData.append('action', 'nymia_track_per_minute_leave');
        formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.trackCallNonce) || '');
        formData.append('session_id', bookingSessionId);
        formData.append('room_id', roomID);
        
        // Use sendBeacon for reliability on page unload
        if (navigator.sendBeacon) {
            const blob = new Blob([new URLSearchParams(formData).toString()], {
                type: 'application/x-www-form-urlencoded'
            });
            navigator.sendBeacon((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', blob);
        } else {
            fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                keepalive: true
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    console.log('Per-minute leave tracked. Remaining:', data.data?.remaining_minutes, 'minutes');
                } else {
                    console.error('Failed to track per-minute leave:', data);
                }
            })
            .catch(err => {
                console.error('Error tracking per-minute leave:', err);
            });
        }
    }
    
    // Track call end on page unload (backup)
    window.addEventListener('beforeunload', function() {
        trackCallEnd();
        trackPerMinuteLeave();
    });
    
    // Get ZegoCloud token
    const formData = new FormData();
    formData.append('action', 'nymia_zego_get_token');
    formData.append('nonce', (window.nymiaAjax && window.nymiaAjax.zegoNonce) || '');
    formData.append('roomId', roomID);
    formData.append('userId', userID);
    
    fetch((window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data || !data.success) {
            throw new Error(data?.data?.message || '<?php echo esc_js(__('Failed to get room access.', 'nymia')); ?>');
        }
        
        const appID = data.data.appId;
        const token = data.data.token;
        const serverSecret = data.data.serverSecret || '';
        
        if (!window.ZegoUIKitPrebuilt) {
            alert('<?php echo esc_js(__('ZEGO SDK not loaded. Please refresh the page.', 'nymia')); ?>');
            return;
        }
        
        // Generate kit token
        const kitToken = (zegoEnv === 'test' && serverSecret)
            ? window.ZegoUIKitPrebuilt.generateKitTokenForTest(appID, serverSecret, roomID, userID, userName)
            : window.ZegoUIKitPrebuilt.generateKitTokenForProduction(appID, token, roomID, userID, userName);
        
        // Create ZegoUIKit instance
        const zp = window.ZegoUIKitPrebuilt.create(kitToken);
        const mount = document.getElementById('zego-live-container');
        
        if (!mount) {
            alert('<?php echo esc_js(__('Container not found.', 'nymia')); ?>');
            return;
        }
        
        // Set responsive height
        function setZegoHeight() {
            const width = window.innerWidth;
            if (width <= 480) {
                mount.style.height = '400px';
            } else if (width <= 768) {
                mount.style.height = '500px';
            } else if (width <= 1024) {
                mount.style.height = '600px';
            } else {
                mount.style.height = '700px';
            }
        }
        setZegoHeight();
        
        window.addEventListener('resize', function() {
            setTimeout(setZegoHeight, 150);
        });
        
        // Join room
        zp.joinRoom({
            container: mount,
            scenario: { 
                mode: window.ZegoUIKitPrebuilt.LiveStreaming, 
                config: { role: isHost ? 'Host' : 'Audience' } 
            },
            showScreenSharingButton: isHost,
            turnOnCameraWhenJoining: false,
            turnOnMicrophoneWhenJoining: isHost, // Only host starts with mic on
            showPreJoinView: false,
            showTextChat: true,
            showUserList: true,
            showLeavingView: true,
            sharedLinks: [{ 
                name: '<?php echo esc_js(__('Join Stream', 'nymia')); ?>', 
                url: window.location.origin + '/live-audio/?room_id=' + roomID 
            }],
            onJoinRoom: function() {
                // Track call start when room is successfully joined
                if (isInstantCall) {
                    setTimeout(trackCallStart, 1000); // Small delay to ensure room is fully joined
                }
                // Track per-minute booking join
                if (isPerMinuteBooking) {
                    setTimeout(trackPerMinuteJoin, 1000);
                }
            },
            onLeaveRoom: function() {
                // Track call end before redirecting
                trackCallEnd();
                // Track per-minute booking leave
                trackPerMinuteLeave();
                // Small delay to ensure tracking request is sent
                setTimeout(function() {
                    window.location.href = '<?php echo esc_url(home_url('/')); ?>';
                }, 500);
            }
        });
        
        // Inject user avatars
        const observer = new MutationObserver(function() {
            const avatars = mount.querySelectorAll('[class*="avatar"], [class*="Avatar"] img, [class*="user"] img');
            avatars.forEach(function(img) {
                if (!img.src || img.src.includes('default') || img.src === '' || img.src.includes('data:image/svg')) {
                    img.src = userAvatar;
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '50%';
                }
            });
        });
        observer.observe(mount, { childList: true, subtree: true });
        
    })
    .catch(err => {
        console.error('Error joining room:', err);
        alert(err.message || '<?php echo esc_js(__('Failed to join the call. Please try again.', 'nymia')); ?>');
        setTimeout(function() {
            window.location.href = '<?php echo esc_url(home_url('/')); ?>';
        }, 2000);
    });
});
</script>

<?php get_footer(); ?>
