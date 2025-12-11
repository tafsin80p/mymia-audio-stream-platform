<?php
/**
 * ========================================
 * NYMIA NOTIFICATION SYSTEM - BACKEND FUNCTIONS
 * ========================================
 * Contains all server-side notification functionality
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// ==========================================
// NOTIFICATION SYSTEM
// ==========================================
/**
 * CREATE NOTIFICATION
 * -------------------
 * Creates a new notification for a user
 * @param int $user_id User ID to notify
 * @param string $type Notification type
 * @param string $message Notification message
 * @param string $link Optional link
 */
function nymia_create_notification($user_id, $type, $message, $link = '', $actor_id = 0) {
    // GET: Current notifications
    $notifications = get_user_meta($user_id, 'nymia_notifications', true);
    if (!is_array($notifications)) {
        $notifications = array();
    }
    
    // GET: Actor avatar if actor_id provided
    $actor_avatar = '';
    if ($actor_id > 0) {
        $actor_avatar = get_avatar_url($actor_id, array('size' => 150));
        $custom_avatar = get_user_meta($actor_id, 'custom_avatar', true);
        if ($custom_avatar) {
            $actor_avatar = esc_url($custom_avatar);
        }
    }
    
    // CREATE: New notification
    $notification = array(
        'id' => uniqid(),
        'type' => $type,
        'message' => $message,
        'link' => $link,
        'read' => false,
        'time' => current_time('mysql'),
        'actor_id' => $actor_id,
        'avatar' => $actor_avatar
    );
    
    // ADD: To beginning of array
    array_unshift($notifications, $notification);
    
    // LIMIT: Keep only last 100 notifications
    if (count($notifications) > 100) {
        $notifications = array_slice($notifications, 0, 100);
    }
    
    // SAVE: Update user meta
    update_user_meta($user_id, 'nymia_notifications', $notifications);
    
    return $notification;
} // END: nymia_create_notification()

/**
 * GET NOTIFICATIONS COUNT (AJAX)
 * -------------------------------
 * Returns the count of unread notifications
 * Hooks into: wp_ajax_nymia_get_notifications_count
 */
function nymia_get_notifications_count_handler() {
    // SECURITY: Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Not logged in.'));
        return;
    }
    
    $user_id = get_current_user_id();
    $notifications = get_user_meta($user_id, 'nymia_notifications', true);
    
    if (!is_array($notifications)) {
        $notifications = array();
    }
    
    // COUNT: Unread notifications
    $unread_count = 0;
    foreach ($notifications as $notification) {
        if (!$notification['read']) {
            $unread_count++;
        }
    }
    
    wp_send_json_success(array(
        'count' => $unread_count,
        'total' => count($notifications)
    ));
} // END: nymia_get_notifications_count_handler()
add_action('wp_ajax_nymia_get_notifications_count', 'nymia_get_notifications_count_handler');

/**
 * GET NOTIFICATIONS (AJAX)
 * ------------------------
 * Returns the list of notifications for the current user
 * Hooks into: wp_ajax_nymia_get_notifications
 */
function nymia_get_notifications_handler() {
    // SECURITY: Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Not logged in.'));
        return;
    }
    
    $user_id = get_current_user_id();
    $notifications = get_user_meta($user_id, 'nymia_notifications', true);
    
    if (!is_array($notifications)) {
        $notifications = array();
    }
    
    // LIMIT: Return last 10 notifications
    $notifications = array_slice($notifications, 0, 10);
    
    // FORMAT: Add human readable time and ensure avatar exists
    foreach ($notifications as &$notification) {
        $notification['time_ago'] = human_time_diff(strtotime($notification['time']), current_time('timestamp'));
        
        // If avatar is not set but actor_id exists, get it
        if (empty($notification['avatar']) && isset($notification['actor_id']) && $notification['actor_id'] > 0) {
            $avatar = get_avatar_url($notification['actor_id'], array('size' => 150));
            $custom_avatar = get_user_meta($notification['actor_id'], 'custom_avatar', true);
            if ($custom_avatar) {
                $avatar = esc_url($custom_avatar);
            }
            $notification['avatar'] = $avatar;
        }
    }
    
    wp_send_json_success(array(
        'notifications' => $notifications
    ));
} // END: nymia_get_notifications_handler()
add_action('wp_ajax_nymia_get_notifications', 'nymia_get_notifications_handler');

/**
 * MARK NOTIFICATION AS READ (AJAX)
 * ---------------------------------
 * Marks a notification as read
 * Hooks into: wp_ajax_nymia_mark_notification_read
 */
function nymia_mark_notification_read_handler() {
    // SECURITY: Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Not logged in.'));
        return;
    }
    
    // GET: Notification ID
    if (!isset($_POST['notification_id'])) {
        wp_send_json_error(array('message' => 'Notification ID required.'));
        return;
    }
    
    $notification_id = sanitize_text_field($_POST['notification_id']);
    $user_id = get_current_user_id();
    $notifications = get_user_meta($user_id, 'nymia_notifications', true);
    
    if (!is_array($notifications)) {
        wp_send_json_error(array('message' => 'No notifications found.'));
        return;
    }
    
    // FIND: And mark as read
    $found = false;
    foreach ($notifications as &$notification) {
        if ($notification['id'] === $notification_id) {
            $notification['read'] = true;
            $found = true;
            break;
        }
    }
    
    if ($found) {
        // SAVE: Updated notifications
        update_user_meta($user_id, 'nymia_notifications', $notifications);
        wp_send_json_success(array('message' => 'Notification marked as read.'));
    } else {
        wp_send_json_error(array('message' => 'Notification not found.'));
    }
} // END: nymia_mark_notification_read_handler()
add_action('wp_ajax_nymia_mark_notification_read', 'nymia_mark_notification_read_handler');

/**
 * MARK ALL NOTIFICATIONS AS READ (AJAX)
 * --------------------------------------
 * Marks all notifications as read
 * Hooks into: wp_ajax_nymia_mark_all_notifications_read
 */
function nymia_mark_all_notifications_read_handler() {
    // SECURITY: Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Not logged in.'));
        return;
    }
    
    $user_id = get_current_user_id();
    $notifications = get_user_meta($user_id, 'nymia_notifications', true);
    
    if (!is_array($notifications)) {
        wp_send_json_success(array('message' => 'No notifications found.'));
        return;
    }
    
    // MARK: All as read
    foreach ($notifications as &$notification) {
        $notification['read'] = true;
    }
    
    // SAVE: Updated notifications
    update_user_meta($user_id, 'nymia_notifications', $notifications);
    
    wp_send_json_success(array('message' => 'All notifications marked as read.'));
} // END: nymia_mark_all_notifications_read_handler()
add_action('wp_ajax_nymia_mark_all_notifications_read', 'nymia_mark_all_notifications_read_handler');

