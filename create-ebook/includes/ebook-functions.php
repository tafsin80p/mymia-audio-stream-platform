<?php
/**
 * ========================================
 * NYMIA EBOOK SYSTEM - BACKEND FUNCTIONS
 * ========================================
 * Contains all server-side ebook functionality
 * 
 * @package Nymia
 * @version 1.0
 */

// SECURITY: Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// ==========================================
// EBOOK SYSTEM FUNCTIONS
// ==========================================

/**
 * GET USER'S EBOOK POSTS
 * ----------------------
 * Retrieves all ebook uploads for the current user
 * @return array User's ebook posts
 */
function nymia_get_user_ebook_posts() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        $user_id = 0; // Use 0 for guest users
    }
    
    $ebook_posts = get_transient('nymia_user_ebook_' . $user_id);
    
    return $ebook_posts ? $ebook_posts : array();
}

/**
 * GET ALL EBOOKS
 * --------------
 * Retrieves all ebook uploads from all users for the library page
 * @return array All ebook posts
 */
function nymia_get_all_ebooks() {
    $all_ebooks = array();
    
    // Get all ebooks from global transient (if exists)
    $global_ebooks = get_transient('nymia_all_ebooks');
    if ($global_ebooks && is_array($global_ebooks)) {
        $all_ebooks = $global_ebooks;
    }
    
    // Fallback: Get from individual user transients (limited approach)
    // This is a fallback if global transient doesn't exist
    if (empty($all_ebooks)) {
        // Try to get from logged-in users (this is limited)
        $current_user_id = get_current_user_id();
        if ($current_user_id) {
            $user_ebooks = get_transient('nymia_user_ebook_' . $current_user_id);
            if ($user_ebooks && is_array($user_ebooks)) {
                $all_ebooks = $user_ebooks;
            }
        }
    }
    
    // Filter out Secret Room content - only show normal category content
    $filtered_ebooks = array();
    foreach ($all_ebooks as $ebook) {
        $ebook_id = isset($ebook['id']) ? $ebook['id'] : 0;
        
        // Only filter if function exists and we have a valid check
        if (function_exists('nymia_is_content_visible_in_normal')) {
            if (!nymia_is_content_visible_in_normal($ebook_id, $ebook)) {
                continue; // Skip Secret Room content
            }
        }
        
        $filtered_ebooks[] = $ebook;
    }
    
    // Sort by date (most recent first)
    usort($filtered_ebooks, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return $filtered_ebooks;
}

/**
 * HANDLE EBOOK UPLOAD VIA AJAX
 * -----------------------------
 * Processes ebook file uploads from the "Create" page
 * Handles: PDF, EPUB, MOBI, TXT formats
 * Saves files to: /wp-content/uploads/nymia-ebook/
 * Hooks into: wp_ajax_nymia_upload_ebook
 */
function nymia_handle_ebook_upload() {
    // DEBUG: Log upload request
    error_log('nymia_handle_ebook_upload called');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));
    
    // Check if files were uploaded at all
    if (empty($_FILES)) {
        error_log('No FILES array at all - possible PHP upload size limit exceeded');
        wp_send_json_error(array('message' => 'No files received. Check PHP upload_max_filesize and post_max_size settings.'));
        return;
    }
    
    // CHECK: User must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Please log in to upload ebooks'));
        return;
    }
    
    // VALIDATE: Check if file was uploaded
    if (!isset($_FILES['ebook_file'])) {
        error_log('No ebook_file in FILES');
        wp_send_json_error(array('message' => 'No ebook file uploaded'));
        return;
    }
    
    // VALIDATE: Check for upload errors
    if ($_FILES['ebook_file']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = 'Upload error (Code: ' . $_FILES['ebook_file']['error'] . ')';
        error_log('Upload error: ' . $error_msg);
        wp_send_json_error(array('message' => $error_msg));
        return;
    }
    
    // GET: Upload data from form
    $file = $_FILES['ebook_file'];
    $title = sanitize_text_field($_POST['ebook_title']);
    $description = isset($_POST['ebook_description']) ? sanitize_textarea_field($_POST['ebook_description']) : '';
    $paid_access = isset($_POST['ebook_paid_access']) ? 'yes' : 'no';
    $price = floatval($_POST['ebook_price']);
    $category = isset($_POST['ebook_category']) ? sanitize_text_field($_POST['ebook_category']) : '';
    $subcategory = isset($_POST['ebook_subcategory']) ? sanitize_text_field($_POST['ebook_subcategory']) : '';
    $language = isset($_POST['ebook_language']) ? sanitize_text_field($_POST['ebook_language']) : '';
    
    // VALIDATE: Category is required
    if (empty($category) || trim($category) === '') {
        wp_send_json_error(array('message' => 'Please select a category for your ebook'));
        return;
    }
    
    // VALIDATE: Language is required
    if (empty($language) || trim($language) === '') {
        wp_send_json_error(array('message' => 'Please select a language for your ebook'));
        return;
    }
    
    // VALIDATE: Allowed file types
    $allowed_types = array(
        'application/pdf',
        'application/epub+zip',
        'application/x-mobipocket-ebook',
        'application/x-mobi',
        'text/plain'
    );
    $file_type = wp_check_filetype($file['name']);
    $allowed_extensions = array('pdf', 'epub', 'mobi', 'txt');
    
    if (!in_array($file['type'], $allowed_types) && !in_array($file_type['ext'], $allowed_extensions)) {
        wp_send_json_error(array('message' => 'Invalid file type. Please upload PDF, EPUB, MOBI, or TXT'));
        return;
    }
    
    // VALIDATE: File size (max 50MB)
    $max_size = 50 * 1024 * 1024; // 50MB
    if ($file['size'] > $max_size) {
        wp_send_json_error(array('message' => 'File size exceeds maximum limit of 50MB'));
        return;
    }
    
    // PROCESS: Upload the ebook file
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    
    // CREATE: Upload directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $ebook_dir = $upload_dir['basedir'] . '/nymia-ebook';
    
    // Create directory with proper permissions
    if (!file_exists($ebook_dir)) {
        $created = wp_mkdir_p($ebook_dir);
        if (!$created) {
            error_log('Failed to create ebook directory: ' . $ebook_dir);
            wp_send_json_error(array('message' => 'Failed to create upload directory'));
            return;
        }
        error_log('Created ebook directory: ' . $ebook_dir);
    }
    
    // Check if directory is writable
    if (!is_writable($ebook_dir)) {
        error_log('Ebook directory is not writable: ' . $ebook_dir);
        wp_send_json_error(array('message' => 'Upload directory is not writable'));
        return;
    }
    
    // GENERATE: Unique filename to prevent overwrites
    $filename = wp_unique_filename($ebook_dir, $file['name']);
    $destination = $ebook_dir . '/' . $filename;
    
    error_log('Attempting to move file from ' . $file['tmp_name'] . ' to ' . $destination);
    
    // MOVE: Uploaded file to destination
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        error_log('Failed to move uploaded file. PHP error: ' . error_get_last()['message']);
        wp_send_json_error(array('message' => 'Failed to move uploaded file. Check server logs.'));
        return;
    }
    
    error_log('File moved successfully to: ' . $destination);
    

    // CREATE: Upload array similar to wp_handle_upload
    $upload = array(
        'file' => $destination,
        'url' => $upload_dir['baseurl'] . '/nymia-ebook/' . $filename,
        'type' => $file['type']
    );
    
    // VALIDATE: Thumbnail is required
    if (!isset($_FILES['ebook_thumbnail']) || $_FILES['ebook_thumbnail']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(array('message' => 'Thumbnail image is required'));
        return;
    }
    
    // HANDLE: Thumbnail upload
    $thumb = $_FILES['ebook_thumbnail'];
    $thumb_type = wp_check_filetype($thumb['name']);
    $image_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    // Validate image type
    if (strpos($thumb['type'], 'image/') !== 0 && !in_array(strtolower($thumb_type['ext']), $image_exts)) {
        wp_send_json_error(array('message' => 'Invalid thumbnail format. Please upload JPG, PNG, GIF, or WEBP image'));
        return;
    }
    
    // Validate file size (max 5MB for thumbnails)
    $max_thumb_size = 5 * 1024 * 1024; // 5MB
    if ($thumb['size'] > $max_thumb_size) {
        wp_send_json_error(array('message' => 'Thumbnail size exceeds maximum limit of 5MB'));
        return;
    }
    
    // CREATE: Thumbnail directory if it doesn't exist
    $thumb_dir = $ebook_dir . '/thumbnails';
    if (!file_exists($thumb_dir)) {
        $created = wp_mkdir_p($thumb_dir);
        if (!$created) {
            error_log('Failed to create thumbnail directory: ' . $thumb_dir);
            wp_send_json_error(array('message' => 'Failed to create thumbnail directory'));
            return;
        }
        error_log('Created thumbnail directory: ' . $thumb_dir);
    }
    
    // Check if thumbnail directory is writable
    if (!is_writable($thumb_dir)) {
        error_log('Thumbnail directory is not writable: ' . $thumb_dir);
        wp_send_json_error(array('message' => 'Thumbnail directory is not writable'));
        return;
    }
    
    // GENERATE: Unique filename
    $thumb_filename = wp_unique_filename($thumb_dir, $thumb['name']);
    $thumb_destination = $thumb_dir . '/' . $thumb_filename;
    
    error_log('Attempting to move thumbnail from ' . $thumb['tmp_name'] . ' to ' . $thumb_destination);
    
    // MOVE: Uploaded thumbnail
    if (!move_uploaded_file($thumb['tmp_name'], $thumb_destination)) {
        error_log('Failed to move thumbnail. PHP error: ' . error_get_last()['message']);
        wp_send_json_error(array('message' => 'Failed to upload thumbnail'));
        return;
    }
    
    error_log('Thumbnail moved successfully to: ' . $thumb_destination);
    
    $thumb_url = $upload_dir['baseurl'] . '/nymia-ebook/thumbnails/' . $thumb_filename;
    
    // GENERATE: Unique attachment ID
    $attach_id = time() . rand(1000, 9999);
    
    // SAVE: Custom metadata
    update_post_meta($attach_id, '_nymia_ebook_paid_access', $paid_access);
    update_post_meta($attach_id, '_nymia_ebook_price', $price);
    update_post_meta($attach_id, '_nymia_ebook_size', $file['size']);
    update_post_meta($attach_id, '_nymia_ebook_format', $file_type['ext']);
    update_post_meta($attach_id, '_nymia_ebook_category', $category);
    update_post_meta($attach_id, '_nymia_ebook_subcategory', $subcategory);
    if ($thumb_url) {
        update_post_meta($attach_id, '_nymia_ebook_thumbnail', $thumb_url);
    }
    
    // SAVE: Visibility settings
    $visibility_destination = isset($_POST['ebook_visibility_destination']) ? sanitize_text_field($_POST['ebook_visibility_destination']) : 'normal';
    $visibility_subcategory = isset($_POST['ebook_visibility_subcategory']) ? sanitize_text_field($_POST['ebook_visibility_subcategory']) : '';
    if (function_exists('nymia_save_content_visibility')) {
        nymia_save_content_visibility($attach_id, $visibility_destination, $visibility_subcategory);
    }
    
    // STORE: In user's ebook collection (using transients)
    $user_id = get_current_user_id();
    if (!$user_id) {
        $user_id = 0; // Guest users
    }
    
    $ebook_posts = get_transient('nymia_user_ebook_' . $user_id);
    if (!$ebook_posts) {
        $ebook_posts = array();
    }
    
    // GET: Author name
    $author_name = 'Guest User';
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $author_name = $current_user->display_name;
    }
    
    // FORMAT: File size for display
    $file_size = size_format($file['size'], 2);
    
    // Language already sanitized/validated above
    
    // CREATE: Ebook post data structure
    $ebook_post = array(
        'id' => $attach_id,
        'title' => $title,
        'description' => $description,
        'url' => $upload['url'],
        'filename' => basename($upload['file']),
        'size' => $file_size,
        'format' => strtoupper($file_type['ext']),
        'author' => $author_name,
        'views' => 0,
        'downloads' => 0,
        'rating' => 0,
        'date' => current_time('mysql'),
        'paid_access' => $paid_access,
        'price' => $price,
        'category' => $category,
        'subcategory' => $subcategory,
        'language' => $language,
        'thumbnail' => $thumb_url,
        'user_id' => $user_id
    );
    
    // ADD: New ebook to beginning of array
    array_unshift($ebook_posts, $ebook_post);
    
    // LIMIT: Keep only last 20 uploads
    $ebook_posts = array_slice($ebook_posts, 0, 20);
    
    // SAVE: To transient (30 day expiry)
    set_transient('nymia_user_ebook_' . $user_id, $ebook_posts, 30 * DAY_IN_SECONDS);
    
    // UPDATE: Global all ebooks transient
    $all_ebooks = get_transient('nymia_all_ebooks');
    if (!$all_ebooks) {
        $all_ebooks = array();
    }
    array_unshift($all_ebooks, $ebook_post);
    // LIMIT: Keep only last 100 ebooks in global list
    $all_ebooks = array_slice($all_ebooks, 0, 100);
    set_transient('nymia_all_ebooks', $all_ebooks, 30 * DAY_IN_SECONDS);
    
    // RETURN: Success response
    wp_send_json_success(array(
        'message' => 'Ebook uploaded successfully',
        'ebook' => $ebook_post
    ));
}

// Wrap the upload handler in a try-catch to handle any errors gracefully
function nymia_handle_ebook_upload_wrapper() {
    try {
        nymia_handle_ebook_upload();
    } catch (Exception $e) {
        error_log('Ebook upload error: ' . $e->getMessage());
        wp_send_json_error(array('message' => 'Upload failed: ' . $e->getMessage()));
    }
}
add_action('wp_ajax_nymia_upload_ebook', 'nymia_handle_ebook_upload_wrapper');
add_action('wp_ajax_nopriv_nymia_upload_ebook', 'nymia_handle_ebook_upload_wrapper');

// ==========================================
// EDIT EBOOK POST
// ==========================================
function nymia_edit_ebook_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in to edit ebook.', 'nymia')), 401);
        return;
    }

    check_ajax_referer('nymia_upload_ebook', 'nonce');

    $ebook_id = isset($_POST['ebook_id']) ? sanitize_text_field($_POST['ebook_id']) : '';
    if (empty($ebook_id)) {
        wp_send_json_error(array('message' => __('Invalid ebook ID.', 'nymia')));
        return;
    }

    $user_id = get_current_user_id();
    
    // GET: User's ebook posts
    $ebook_posts = get_transient('nymia_user_ebook_' . $user_id);
    if (!$ebook_posts || !is_array($ebook_posts)) {
        wp_send_json_error(array('message' => __('Ebook post not found.', 'nymia')));
        return;
    }

    // FIND: Ebook post by ID
    $ebook_index = -1;
    foreach ($ebook_posts as $index => $ebook) {
        if (isset($ebook['id']) && (string)$ebook['id'] === (string)$ebook_id) {
            // CHECK: User owns this ebook
            if (isset($ebook['user_id']) && intval($ebook['user_id']) === $user_id) {
                $ebook_index = $index;
                break;
            }
        }
    }

    if ($ebook_index === -1) {
        wp_send_json_error(array('message' => __('Ebook post not found or you do not have permission to edit it.', 'nymia')));
        return;
    }

    // GET: Form data
    $title = isset($_POST['ebook_title']) ? sanitize_text_field(wp_unslash($_POST['ebook_title'])) : '';
    $description = isset($_POST['ebook_description']) ? sanitize_textarea_field(wp_unslash($_POST['ebook_description'])) : '';
    $paid_access = isset($_POST['ebook_paid_access']) ? 'yes' : 'no';
    $price = isset($_POST['ebook_price']) ? floatval($_POST['ebook_price']) : 0;
    $category = isset($_POST['ebook_category']) ? sanitize_text_field($_POST['ebook_category']) : '';
    $subcategory = isset($_POST['ebook_subcategory']) ? sanitize_text_field($_POST['ebook_subcategory']) : '';
    $language = isset($_POST['ebook_language']) ? sanitize_text_field($_POST['ebook_language']) : '';

    if (empty($title)) {
        wp_send_json_error(array('message' => __('Please enter a title.', 'nymia')));
        return;
    }

    // PROCESS: Upload thumbnail if provided
    $thumbnail_url = '';
    if (isset($_FILES['ebook_thumbnail']) && $_FILES['ebook_thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumb = $_FILES['ebook_thumbnail'];
        $allowed_image_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
        $thumb_file_type = wp_check_filetype($thumb['name']);
        $allowed_image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        if (in_array($thumb['type'], $allowed_image_types) || in_array($thumb_file_type['ext'], $allowed_image_extensions)) {
            $upload_dir = wp_upload_dir();
            $thumb_dir = $upload_dir['basedir'] . '/nymia-ebook/thumbnails';
            if (!file_exists($thumb_dir)) {
                wp_mkdir_p($thumb_dir);
            }
            
            $thumb_filename = wp_unique_filename($thumb_dir, $thumb['name']);
            $thumb_destination = $thumb_dir . '/' . $thumb_filename;
            
            if (move_uploaded_file($thumb['tmp_name'], $thumb_destination)) {
                $thumbnail_url = $upload_dir['baseurl'] . '/nymia-ebook/thumbnails/' . $thumb_filename;
            }
        }
    } else {
        // Keep existing thumbnail if not uploading new one
        $thumbnail_url = isset($ebook_posts[$ebook_index]['thumbnail']) ? $ebook_posts[$ebook_index]['thumbnail'] : '';
    }

    // UPDATE: Ebook post data
    $ebook_posts[$ebook_index]['title'] = $title;
    $ebook_posts[$ebook_index]['description'] = $description;
    $ebook_posts[$ebook_index]['paid_access'] = $paid_access;
    $ebook_posts[$ebook_index]['price'] = $price;
    $ebook_posts[$ebook_index]['category'] = $category;
    $ebook_posts[$ebook_index]['subcategory'] = $subcategory;
    $ebook_posts[$ebook_index]['language'] = $language;
    if (!empty($thumbnail_url)) {
        $ebook_posts[$ebook_index]['thumbnail'] = $thumbnail_url;
    }

    // UPDATE: Post meta
    $attach_id = is_numeric($ebook_id) ? intval($ebook_id) : 0;
    if ($attach_id > 0) {
        update_post_meta($attach_id, '_nymia_ebook_paid_access', $paid_access);
        update_post_meta($attach_id, '_nymia_ebook_price', $price);
        update_post_meta($attach_id, '_nymia_ebook_category', $category);
        update_post_meta($attach_id, '_nymia_ebook_subcategory', $subcategory);
        if (!empty($thumbnail_url)) {
            update_post_meta($attach_id, '_nymia_ebook_thumbnail', $thumbnail_url);
        }
    }

    // SAVE: Updated ebook posts
    set_transient('nymia_user_ebook_' . $user_id, $ebook_posts, 30 * DAY_IN_SECONDS);

    // UPDATE: Global all ebooks transient
    $all_ebooks = get_transient('nymia_all_ebooks');
    if ($all_ebooks && is_array($all_ebooks)) {
        foreach ($all_ebooks as $index => $ebook) {
            if (isset($ebook['id']) && (string)$ebook['id'] === (string)$ebook_id) {
                $all_ebooks[$index]['title'] = $title;
                $all_ebooks[$index]['description'] = $description;
                $all_ebooks[$index]['paid_access'] = $paid_access;
                $all_ebooks[$index]['price'] = $price;
                $all_ebooks[$index]['category'] = $category;
                $all_ebooks[$index]['subcategory'] = $subcategory;
                $all_ebooks[$index]['language'] = $language;
                if (!empty($thumbnail_url)) {
                    $all_ebooks[$index]['thumbnail'] = $thumbnail_url;
                }
                break;
            }
        }
        set_transient('nymia_all_ebooks', $all_ebooks, 30 * DAY_IN_SECONDS);
    }

    wp_send_json_success(array(
        'message' => __('Ebook updated successfully!', 'nymia'),
        'ebook' => $ebook_posts[$ebook_index]
    ));
}
add_action('wp_ajax_nymia_edit_ebook', 'nymia_edit_ebook_post');

// ==========================================
// GET SINGLE EBOOK POST (for edit modal)
// ==========================================
function nymia_get_single_ebook_post() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Please log in.', 'nymia')), 401);
        return;
    }

    check_ajax_referer('nymia_upload_ebook', 'nonce');

    $ebook_id = isset($_POST['ebook_id']) ? sanitize_text_field($_POST['ebook_id']) : '';
    if (empty($ebook_id)) {
        wp_send_json_error(array('message' => __('Invalid ebook ID.', 'nymia')));
        return;
    }

    $user_id = get_current_user_id();
    $ebook_posts = get_transient('nymia_user_ebook_' . $user_id);
    
    if (!$ebook_posts || !is_array($ebook_posts)) {
        wp_send_json_error(array('message' => __('Ebook post not found.', 'nymia')));
        return;
    }

    foreach ($ebook_posts as $ebook) {
        if (isset($ebook['id']) && (string)$ebook['id'] === (string)$ebook_id) {
            if (isset($ebook['user_id']) && intval($ebook['user_id']) === $user_id) {
                wp_send_json_success(array('ebook' => $ebook));
                return;
            }
        }
    }

    wp_send_json_error(array('message' => __('Ebook post not found.', 'nymia')));
}
add_action('wp_ajax_nymia_get_single_ebook', 'nymia_get_single_ebook_post');

// (Rating AJAX removed in revert)

