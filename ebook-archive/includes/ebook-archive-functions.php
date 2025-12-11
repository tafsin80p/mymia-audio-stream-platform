<?php
// ==========================================
// EBOOK ARCHIVE FUNCTIONS
// ==========================================

if (!defined('ABSPATH')) { exit; }

/**
 * Check if user has unlocked access to an ebook
 */
if (!function_exists('nymia_user_has_ebook_access')) {
function nymia_user_has_ebook_access($user_id, $ebook_id) {
    if (!$user_id || !$ebook_id) {
        return false;
    }
    $unlocked = get_user_meta($user_id, 'nymia_ebooks_unlocked', true);
    if (!is_array($unlocked)) {
        return false;
    }
    $ebook_id_str = (string)$ebook_id;
    foreach ($unlocked as $id) {
        if ((string)$id === $ebook_id_str) {
            return true;
        }
    }
    return false;
}}

/**
 * AJAX: Mark ebook as purchased/unlocked for current user
 */
if (!function_exists('nymia_purchase_ebook_handler')) {
function nymia_purchase_ebook_handler() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Please log in to purchase'));
    }
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'nymia_purchase_ebook')) {
        wp_send_json_error(array('message' => 'Security verification failed'));
    }
    $ebook_id = isset($_POST['ebook_id']) ? sanitize_text_field($_POST['ebook_id']) : '';
    if (empty($ebook_id)) {
        wp_send_json_error(array('message' => 'Invalid ebook'));
    }
    $user_id = get_current_user_id();
    $unlocked = get_user_meta($user_id, 'nymia_ebooks_unlocked', true);
    if (!is_array($unlocked)) { $unlocked = array(); }
    $exists = false;
    foreach ($unlocked as $id) {
        if ((string)$id === (string)$ebook_id) { $exists = true; break; }
    }
    if (!$exists) {
        $unlocked[] = (string)$ebook_id;
        update_user_meta($user_id, 'nymia_ebooks_unlocked', array_values($unlocked));
    }
    wp_send_json_success(array('message' => 'Ebook unlocked', 'ebook_id' => $ebook_id));
}}
add_action('wp_ajax_nymia_purchase_ebook', 'nymia_purchase_ebook_handler');


