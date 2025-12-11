<?php
/**
 * ========================================
 * NYMIA FOLLOWERS SYSTEM - BACKEND FUNCTIONS
 * ========================================
 * Contains all server-side followers/following functionality
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// ==========================================
// FOLLOWERS SYSTEM
// ==========================================
/**
 * CHECK IF FOLLOWING USER
 * ------------------------
 * Checks if one user is following another user
 * 
 * @param int $follower_id User ID of follower
 * @param int $following_id User ID of user being followed
 * @return bool True if following, false otherwise
 */
if (!function_exists('nymia_is_following')) {
function nymia_is_following($follower_id, $following_id) {
    if (!$follower_id || !$following_id || $follower_id === $following_id) {
        return false;
    }
    
    // GET: List of users this user is following
    $following_list = get_user_meta($follower_id, 'nymia_following', true);
    
    // CHECK: If list exists and contains the user ID
    if (is_array($following_list)) {
        return in_array($following_id, $following_list);
    }
    
    return false;
}
}

/**
 * GET FOLLOWERS COUNT
 * -------------------
 * Returns the number of followers for a user
 * 
 * @param int $user_id User ID
 * @return int Number of followers
 */
if (!function_exists('nymia_get_followers_count')) {
function nymia_get_followers_count($user_id) {
    $followers_list = get_user_meta($user_id, 'nymia_followers', true);
    if (!is_array($followers_list)) {
        return 0;
    }
    return count($followers_list);
}
}

/**
 * GET FOLLOWING COUNT
 * -------------------
 * Returns the number of users this user is following
 * 
 * @param int $user_id User ID
 * @return int Number of users being followed
 */
if (!function_exists('nymia_get_following_count')) {
function nymia_get_following_count($user_id) {
    $following_list = get_user_meta($user_id, 'nymia_following', true);
    if (!is_array($following_list)) {
        return 0;
    }
    return count($following_list);
}
}

/**
 * TOGGLE FOLLOW STATUS (AJAX)
 * ----------------------------
 * AJAX handler to follow/unfollow a user
 */
if (!function_exists('nymia_toggle_follow_handler')) {
function nymia_toggle_follow_handler() {
    // VERIFY: Nonce for security
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if (!wp_verify_nonce($nonce, 'nymia_follow_action')) {
        wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.'));
        return;
    }
    
    // CHECK: User is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'User not logged in'));
        return;
    }
    
    // GET: Current user
    $current_user_id = get_current_user_id();
    
    // GET: Target user ID
    $target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    // VALIDATE: Target user exists
    if (!$target_user_id || $target_user_id === $current_user_id) {
        wp_send_json_error(array('message' => 'Invalid user ID'));
        return;
    }
    
    $target_user = get_user_by('ID', $target_user_id);
    if (!$target_user) {
        wp_send_json_error(array('message' => 'User not found'));
        return;
    }
    
    // GET: Current following list
    $following_list = get_user_meta($current_user_id, 'nymia_following', true);
    if (!is_array($following_list)) {
        $following_list = array();
    }
    
    // CHECK: If already following
    $is_following = in_array($target_user_id, $following_list);
    
    if ($is_following) {
        // UNFOLLOW: Remove from list
        $following_list = array_diff($following_list, array($target_user_id));
        $action = 'unfollowed';
    } else {
        // FOLLOW: Add to list
        $following_list[] = $target_user_id;
        $action = 'followed';
    }
    
    // UPDATE: Save new following list
    update_user_meta($current_user_id, 'nymia_following', array_values($following_list));
    
    // UPDATE: Follower count for target user
    $target_followers = get_user_meta($target_user_id, 'nymia_followers', true);
    if (!is_array($target_followers)) {
        $target_followers = array();
    }
    
    if ($is_following) {
        // REMOVE: Follower
        $target_followers = array_diff($target_followers, array($current_user_id));
    } else {
        // ADD: Follower
        if (!in_array($current_user_id, $target_followers)) {
            $target_followers[] = $current_user_id;
        }
        
        // CREATE: Notification for the user being followed
        $current_user = get_userdata($current_user_id);
        $current_user_name = $current_user ? $current_user->display_name : 'Someone';
        $profile_url = home_url('/profile/?user_id=' . $current_user_id);
        $notification_message = $current_user_name . ' started following you';
        
        // Check if notification function exists before calling
        if (function_exists('nymia_create_notification')) {
            nymia_create_notification($target_user_id, 'follow', $notification_message, $profile_url, $current_user_id);
        }
    }
    
    update_user_meta($target_user_id, 'nymia_followers', array_values($target_followers));
    
    // RESPONSE: Send success with new status
    wp_send_json_success(array(
        'action' => $action,
        'is_following' => !$is_following,
        'followers_count' => count($target_followers),
        'following_count' => count($following_list)
    ));
}
add_action('wp_ajax_nymia_toggle_follow', 'nymia_toggle_follow_handler');
}

/**
 * GET USER LIST (FOLLOWERS OR FOLLOWING) (AJAX)
 * AJAX handler to fetch the list of followers or following users
 */
if (!function_exists('nymia_get_user_list_handler')) {
function nymia_get_user_list_handler() {
    // SECURITY: Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to view user lists.'));
        return;
    }
    
    // GET: Parameters
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'followers';
    
    // VALIDATE: User ID and type
    if (!$user_id || !in_array($type, array('followers', 'following'))) {
        wp_send_json_error(array('message' => 'Invalid request.'));
        return;
    }
    
    // VALIDATE: User exists
    $target_user = get_user_by('ID', $user_id);
    if (!$target_user) {
        wp_send_json_error(array('message' => 'User not found.'));
        return;
    }
    
    // GET: User IDs based on type
    if ($type === 'followers') {
        $user_ids = get_user_meta($user_id, 'nymia_followers', true);
    } else {
        $user_ids = get_user_meta($user_id, 'nymia_following', true);
    }
    
    // VALIDATE: If array exists
    if (!is_array($user_ids)) {
        wp_send_json_success(array('users' => array()));
        return;
    }
    
    // BUILD: User list
    $users = array();
    foreach ($user_ids as $follower_id) {
        $follower = get_user_by('ID', $follower_id);
        if ($follower) {
            // GET: Avatar
            $avatar_url = get_avatar_url($follower_id, array('size' => 150));
            $custom_avatar = get_user_meta($follower_id, 'custom_avatar', true);
            if ($custom_avatar) {
                $avatar_url = esc_url($custom_avatar);
            }
            
            $users[] = array(
                'id' => $follower->ID,
                'name' => $follower->display_name ?: $follower->user_login,
                'username' => $follower->user_login,
                'avatar' => $avatar_url
            );
        }
    }
    
    // RETURN: User list
    wp_send_json_success(array('users' => $users));
}
add_action('wp_ajax_nymia_get_user_list', 'nymia_get_user_list_handler');
}

/**
 * REMOVE FOLLOWER (AJAX)
 * Allows a user to remove a follower from their followers list
 */
if (!function_exists('nymia_remove_follower_handler')) {
function nymia_remove_follower_handler() {
    // SECURITY: Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in.'));
        return;
    }
    
    // GET: Current user
    $current_user_id = get_current_user_id();
    
    // GET: Follower ID to remove
    $follower_id = isset($_POST['follower_id']) ? intval($_POST['follower_id']) : 0;
    
    // VALIDATE: Follower ID
    if (!$follower_id) {
        wp_send_json_error(array('message' => 'Invalid request.'));
        return;
    }
    
    // VALIDATE: Follower exists
    $follower = get_user_by('ID', $follower_id);
    if (!$follower) {
        wp_send_json_error(array('message' => 'User not found.'));
        return;
    }
    
    // GET: Current user's followers list
    $followers_list = get_user_meta($current_user_id, 'nymia_followers', true);
    
    if (!is_array($followers_list)) {
        $followers_list = array();
    }
    
    // CHECK: If the user is actually in the followers list
    if (!in_array($follower_id, $followers_list)) {
        wp_send_json_error(array('message' => 'User is not following you.'));
        return;
    }
    
    // REMOVE: Follower from list
    $followers_list = array_diff($followers_list, array($follower_id));
    $followers_list = array_values($followers_list);
    
    // UPDATE: Save new followers list
    update_user_meta($current_user_id, 'nymia_followers', $followers_list);
    
    // UPDATE: Remove yourself from their following list
    $follower_following = get_user_meta($follower_id, 'nymia_following', true);
    if (is_array($follower_following)) {
        $follower_following = array_diff($follower_following, array($current_user_id));
        $follower_following = array_values($follower_following);
        update_user_meta($follower_id, 'nymia_following', $follower_following);
    }
    
    // RETURN: Success
    wp_send_json_success(array(
        'message' => 'Follower removed successfully.',
        'followers_count' => count($followers_list)
    ));
}
add_action('wp_ajax_nymia_remove_follower', 'nymia_remove_follower_handler');
}

