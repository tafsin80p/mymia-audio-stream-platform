<?php
/**
 * ========================================
 * NYMIA THEME - USER MANAGEMENT (ADMIN)
 * ========================================
 * Admin page to manage users: list/search/filter and account controls
 * - Filters: role, status, registration date
 * - Actions: suspend/enable, ban, delete, role change, KYC approve/reject
 */

if (!defined('ABSPATH')) { exit; }

// Meta keys
define('NYMIA_USER_STATUS_META', 'nymia_user_status'); // active|suspended|banned
define('NYMIA_KYC_STATUS_META', 'nymia_kyc_status');   // pending|approved|rejected

/**
 * Handle admin POST actions for user management
 */
function nymia_handle_user_management_action() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized', 'nymia'));
    }
    $action = isset($_POST['nymia_action']) ? sanitize_text_field($_POST['nymia_action']) : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $redirect = isset($_POST['_wp_http_referer']) ? esc_url_raw($_POST['_wp_http_referer']) : admin_url('admin.php?page=nymia-user-management');

    if (!$action || !$user_id || !check_admin_referer('nymia_user_action_'.$user_id)) {
        wp_safe_redirect($redirect);
        exit;
    }

    // Protect against locking yourself out
    if (get_current_user_id() === $user_id && in_array($action, array('ban','suspend','delete','role_change'), true)) {
        wp_safe_redirect(add_query_arg('msg', 'cannot_modify_self', $redirect));
        exit;
    }

    switch ($action) {
        case 'suspend':
            update_user_meta($user_id, NYMIA_USER_STATUS_META, 'suspended');
            break;
        case 'enable':
            update_user_meta($user_id, NYMIA_USER_STATUS_META, 'active');
            break;
        case 'ban':
            update_user_meta($user_id, NYMIA_USER_STATUS_META, 'banned');
            break;
        case 'delete':
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user($user_id);
            break;
        case 'role_change':
            $new_role = isset($_POST['new_role']) ? sanitize_text_field($_POST['new_role']) : '';
            if ($new_role) {
                $user_obj = new WP_User($user_id);
                $user_obj->set_role($new_role);
            }
            break;
        case 'kyc_approve':
            update_user_meta($user_id, NYMIA_KYC_STATUS_META, 'approved');
            break;
        case 'kyc_reject':
            update_user_meta($user_id, NYMIA_KYC_STATUS_META, 'rejected');
            break;
        case 'kyc_reset':
            update_user_meta($user_id, NYMIA_KYC_STATUS_META, 'pending');
            break;
    }

    wp_safe_redirect(add_query_arg('updated', '1', $redirect));
    exit;
}
add_action('admin_post_nymia_user_action', 'nymia_handle_user_management_action');

/**
 * Handle CSV Export for Creators
 */
function nymia_export_creators_csv() {
    // Prevent any output before headers
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized', 'nymia'));
    }
    
    check_admin_referer('nymia_export_creators_csv');
    
    // Get creator roles
    $creator_roles = apply_filters('nymia_creator_roles', array('administrator', 'author'));
    
    // Check if specific user IDs are provided
    $user_ids = isset($_POST['user_ids']) && is_array($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : array();
    
    // Get creators
    $args = array(
        'role__in' => $creator_roles,
        'number' => -1, // Get all
        'orderby' => 'registered',
        'order' => 'DESC',
    );
    
    // If specific user IDs provided, filter by them
    if (!empty($user_ids)) {
        $args['include'] = $user_ids;
    }
    
    $creators = get_users($args);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nymia-creators-' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output UTF-8 BOM for Excel compatibility
    echo "\xEF\xBB\xBF";
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    $headers = array(
        'ID',
        'Username',
        'Email',
        'Display Name',
        'First Name',
        'Last Name',
        'Phone',
        'Location',
        'Registered Date',
        'Role',
        'Status',
        'KYC Status',
        'Stripe Account ID',
        'Stripe Payout Email',
        'Stripe Payout Status',
        'Bank Name',
        'Account Holder Name',
        'Account Number',
        'Routing Number',
        'SWIFT/BIC Code',
        'Account Type',
        'Bank Country',
        'Bank Account Updated',
        'Website',
        'Bio'
    );
    
    fputcsv($output, $headers);
    
    // Export creator data
    foreach ($creators as $creator) {
        $user_id = $creator->ID;
        
        // Get user meta
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $phone = get_user_meta($user_id, 'phone', true);
        $location = get_user_meta($user_id, 'location', true);
        $user_status = get_user_meta($user_id, NYMIA_USER_STATUS_META, true) ?: 'active';
        $kyc_status = get_user_meta($user_id, NYMIA_KYC_STATUS_META, true) ?: 'pending';
        $stripe_account_id = get_user_meta($user_id, 'nymia_stripe_account_id', true);
        if (empty($stripe_account_id)) {
            $stripe_account_id = get_user_meta($user_id, 'stripe_account_no', true);
        }
        $stripe_payout_email = get_user_meta($user_id, 'nymia_stripe_payout_email', true);
        $stripe_payout_status = get_user_meta($user_id, 'nymia_stripe_payout_status', true) ?: 'not_connected';
        $bank_name = get_user_meta($user_id, 'nymia_bank_name', true);
        $account_holder_name = get_user_meta($user_id, 'nymia_bank_account_holder_name', true);
        $account_number = get_user_meta($user_id, 'nymia_bank_account_number', true);
        $routing_number = get_user_meta($user_id, 'nymia_bank_routing_number', true);
        $swift_bic = get_user_meta($user_id, 'nymia_bank_swift_bic', true);
        if (empty($swift_bic)) {
            $swift_bic = get_user_meta($user_id, 'nymia_swift_bic', true);
        }
        $account_type = get_user_meta($user_id, 'nymia_bank_account_type', true);
        $bank_country = get_user_meta($user_id, 'nymia_bank_country', true);
        $bank_account_updated = get_user_meta($user_id, 'nymia_bank_account_updated', true);
        $bank_account_updated_date = $bank_account_updated ? date('Y-m-d H:i:s', $bank_account_updated) : '';
        $bio = get_user_meta($user_id, 'description', true);
        
        // Parse display name for first/last name if not set
        if (empty($first_name) && empty($last_name) && !empty($creator->display_name)) {
            $name_parts = explode(' ', $creator->display_name, 2);
            $first_name = isset($name_parts[0]) ? $name_parts[0] : '';
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        }
        
        $row = array(
            $user_id,
            $creator->user_login,
            $creator->user_email,
            $creator->display_name,
            $first_name,
            $last_name,
            $phone,
            $location,
            $creator->user_registered,
            implode(', ', $creator->roles),
            ucfirst($user_status),
            ucfirst($kyc_status),
            $stripe_account_id,
            $stripe_payout_email,
            ucfirst($stripe_payout_status),
            $bank_name,
            $account_holder_name,
            $account_number,
            $routing_number,
            $swift_bic,
            $account_type,
            $bank_country,
            $bank_account_updated_date,
            $creator->user_url,
            $bio
        );
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
add_action('admin_post_nymia_export_creators_csv', 'nymia_export_creators_csv');

/**
 * Handle CSV Export for Customers
 */
function nymia_export_customers_csv() {
    // Prevent any output before headers
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized', 'nymia'));
    }
    
    check_admin_referer('nymia_export_customers_csv');
    
    // Get customer roles (subscribers)
    $customer_roles = apply_filters('nymia_customer_roles', array('subscriber'));
    
    // Check if specific user IDs are provided
    $user_ids = isset($_POST['user_ids']) && is_array($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : array();
    
    // Get customers
    $args = array(
        'role__in' => $customer_roles,
        'number' => -1, // Get all
        'orderby' => 'registered',
        'order' => 'DESC',
    );
    
    // If specific user IDs provided, filter by them
    if (!empty($user_ids)) {
        $args['include'] = $user_ids;
    }
    
    $customers = get_users($args);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nymia-customers-' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output UTF-8 BOM for Excel compatibility
    echo "\xEF\xBB\xBF";
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    $headers = array(
        'ID',
        'Username',
        'Email',
        'Display Name',
        'First Name',
        'Last Name',
        'Phone',
        'Location',
        'Registered Date',
        'Status',
        'Website',
        'Bio'
    );
    
    fputcsv($output, $headers);
    
    // Export customer data
    foreach ($customers as $customer) {
        $user_id = $customer->ID;
        
        // Get user meta
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $phone = get_user_meta($user_id, 'phone', true);
        $location = get_user_meta($user_id, 'location', true);
        $user_status = get_user_meta($user_id, NYMIA_USER_STATUS_META, true) ?: 'active';
        $bio = get_user_meta($user_id, 'description', true);
        
        // Parse display name for first/last name if not set
        if (empty($first_name) && empty($last_name) && !empty($customer->display_name)) {
            $name_parts = explode(' ', $customer->display_name, 2);
            $first_name = isset($name_parts[0]) ? $name_parts[0] : '';
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        }
        
        $row = array(
            $user_id,
            $customer->user_login,
            $customer->user_email,
            $customer->display_name,
            $first_name,
            $last_name,
            $phone,
            $location,
            $customer->user_registered,
            ucfirst($user_status),
            $customer->user_url,
            $bio
        );
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
add_action('admin_post_nymia_export_customers_csv', 'nymia_export_customers_csv');

/**
 * AJAX Handler: Search users for export
 */
function nymia_ajax_search_users_for_export() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Unauthorized', 'nymia')));
        return;
    }
    
    check_ajax_referer('nymia_search_users_export', 'nonce');
    
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'creator';
    
    if (empty($query) || strlen($query) < 2) {
        wp_send_json_error(array('message' => __('Query too short', 'nymia')));
        return;
    }
    
    // Determine roles based on type
    if ($type === 'creator') {
        $roles = apply_filters('nymia_creator_roles', array('administrator', 'author'));
    } else {
        $roles = apply_filters('nymia_customer_roles', array('subscriber'));
    }
    
    // Search users
    $args = array(
        'role__in' => $roles,
        'search' => '*' . esc_attr($query) . '*',
        'search_columns' => array('user_login', 'user_nicename', 'user_email', 'display_name'),
        'number' => 20, // Limit results
        'orderby' => 'registered',
        'order' => 'DESC',
    );
    
    $users = get_users($args);
    
    $results = array();
    foreach ($users as $user) {
        $results[] = array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name ?: $user->user_login,
        );
    }
    
    wp_send_json_success(array('users' => $results));
}
add_action('wp_ajax_nymia_search_users_for_export', 'nymia_ajax_search_users_for_export');

/**
 * Render User Management page
 */
function nymia_user_management_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Filters
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $role = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : '';
    $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

    // Build WP_User_Query args
    $args = array(
        'number' => 50,
        'paged' => isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1,
        'search' => $search ? '*'.esc_attr($search).'*' : '',
        'search_columns' => array('user_login','user_nicename','user_email','display_name'),
        'role' => $role ? $role : '',
        'meta_query' => array(),
        'orderby' => 'registered',
        'order' => 'DESC',
    );

    if ($status) {
        $args['meta_query'][] = array(
            'key' => NYMIA_USER_STATUS_META,
            'value' => $status,
            'compare' => '=',
        );
    }

    if ($date_from || $date_to) {
        $date_query = array();
        if ($date_from) { $date_query['after'] = $date_from; }
        if ($date_to) { $date_query['before'] = $date_to; }
        $args['date_query'] = array($date_query);
    }

    $query = new WP_User_Query($args);
    $users = $query->get_results();
    $total = $query->get_total();
    $per_page = $args['number'];
    $paged = $args['paged'];
    $total_pages = $per_page > 0 ? ceil($total / $per_page) : 1;

    // Roles list
    global $wp_roles;
    $all_roles = $wp_roles->roles;

    ?>
    <div class="wrap nymia-admin-dashboard-wrap">
        <h1 class="nymia-admin-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p class="nymia-admin-subtitle">Manage all users (creators/vendors, buyers, admins) with filters, search, and account controls</p>

        <!-- Header Filters -->
        <div class="nymia-admin-card" style="margin-bottom: 30px;">
            <div class="nymia-card-body">
                <form method="get" action="" class="nymia-toolbar-form">
                    <input type="hidden" name="page" value="nymia-user-management" />
                    <div class="nymia-filter-bar">
                        <input type="text" class="nymia-input" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search users by name or email..." style="flex: 1;">
                        <select name="role" class="nymia-select">
                            <option value="">All Roles</option>
                            <?php foreach ($all_roles as $role_key => $role_obj): ?>
                                <option value="<?php echo esc_attr($role_key); ?>" <?php selected($role, $role_key); ?>><?php echo esc_html($role_obj['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="status" class="nymia-select">
                            <option value="">All Statuses</option>
                            <option value="active" <?php selected($status,'active'); ?>>Active</option>
                            <option value="suspended" <?php selected($status,'suspended'); ?>>Suspended</option>
                            <option value="banned" <?php selected($status,'banned'); ?>>Banned</option>
                        </select>
                        <input type="date" class="nymia-input" name="date_from" value="<?php echo esc_attr($date_from); ?>" placeholder="From" style="width: 150px;">
                        <input type="date" class="nymia-input" name="date_to" value="<?php echo esc_attr($date_to); ?>" placeholder="To" style="width: 150px;">
                        <button type="submit" class="nymia-btn nymia-btn-primary">Apply Filters</button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=nymia-user-management')); ?>" class="nymia-btn nymia-btn-secondary">Reset</a>
                    </div>
                    <div class="nymia-pills" style="margin-top: 15px;">
                        <a class="nymia-pill" href="<?php echo esc_url(add_query_arg(array('status'=>'active'))); ?>">Active (<?php echo count(get_users(array('meta_query' => array(array('key' => NYMIA_USER_STATUS_META, 'value' => 'active'))))); ?>)</a>
                        <a class="nymia-pill" href="<?php echo esc_url(add_query_arg(array('status'=>'suspended'))); ?>">Suspended (<?php echo count(get_users(array('meta_query' => array(array('key' => NYMIA_USER_STATUS_META, 'value' => 'suspended'))))); ?>)</a>
                        <a class="nymia-pill" href="<?php echo esc_url(add_query_arg(array('status'=>'banned'))); ?>">Banned (<?php echo count(get_users(array('meta_query' => array(array('key' => NYMIA_USER_STATUS_META, 'value' => 'banned'))))); ?>)</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Actions -->
        <div class="nymia-admin-card" style="margin-bottom: 30px;">
            <div class="nymia-card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h3 style="margin: 0 0 8px 0; color: #fff; font-size: 18px;">Export Data</h3>
                        <p style="margin: 0; color: #888; font-size: 14px;">Export creator and customer data to CSV files</p>
                    </div>
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 0;">
                            <?php wp_nonce_field('nymia_export_creators_csv'); ?>
                            <input type="hidden" name="action" value="nymia_export_creators_csv" />
                            <button type="submit" class="nymia-btn nymia-btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                Export Creators CSV
                            </button>
                        </form>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 0;">
                            <?php wp_nonce_field('nymia_export_customers_csv'); ?>
                            <input type="hidden" name="action" value="nymia_export_customers_csv" />
                            <button type="submit" class="nymia-btn nymia-btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                Export Customers CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">User List</h2>
                <p class="nymia-card-subtitle">Total: <?php echo intval($total); ?> users
                    <?php if ($status): ?>
                        | Status: <strong><?php echo esc_html(ucfirst($status)); ?></strong>
                    <?php endif; ?>
                    <?php if ($role): ?>
                        | Role: <strong><?php echo esc_html($role); ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-table-wrapper">
                    <table class="nymia-admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>KYC</th>
                            <th>Registered</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="7" class="nymia-empty-state" style="text-align: center; padding: 40px 20px; color: #888; font-size: 14px;">No users found</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): 
                                $user_status = get_user_meta($user->ID, NYMIA_USER_STATUS_META, true);
                                if (!$user_status) { $user_status = 'active'; }
                                $kyc_status = get_user_meta($user->ID, NYMIA_KYC_STATUS_META, true);
                                if (!$kyc_status) { $kyc_status = 'pending'; }
                                $roles = implode(', ', array_map('esc_html', $user->roles));
                                $avatar = get_avatar_url($user->ID, array('size' => 64));
                            ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img src="<?php echo esc_url($avatar); ?>" alt="" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid rgba(199,84,26,0.3);" />
                                        <div>
                                            <strong style="display:block; color:#fff;"><?php echo esc_html($user->display_name ?: $user->user_login); ?></strong>
                                            <span style="display:inline-block; font-size:11px; color:rgba(255,255,255,0.6);">@<?php echo esc_html($user->user_login); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td><?php echo $roles ? $roles : '—'; ?></td>
                                <td>
                                    <span class="nymia-badge nymia-badge-<?php echo esc_attr($user_status); ?>"><?php echo esc_html(ucfirst($user_status)); ?></span>
                                </td>
                                <td>
                                    <span class="nymia-badge nymia-badge-kyc-<?php echo esc_attr($kyc_status); ?>"><?php echo esc_html(ucfirst($kyc_status)); ?></span>
                                </td>
                                <td><?php echo esc_html(date_i18n('Y-m-d', strtotime($user->user_registered))); ?></td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <div class="nymia-action-group">
                                    <a class="button nymia-btn small" href="<?php echo esc_url(admin_url('user-edit.php?user_id='.$user->ID)); ?>">View/Edit</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <?php wp_nonce_field('nymia_user_action_'.$user->ID); ?>
                                        <input type="hidden" name="action" value="nymia_user_action" />
                                        <input type="hidden" name="user_id" value="<?php echo intval($user->ID); ?>" />
                                        <input type="hidden" name="nymia_action" value="<?php echo $user_status==='suspended' ? 'enable' : 'suspend'; ?>" />
                                        <button class="button nymia-btn outline small" type="submit"><?php echo $user_status==='suspended' ? 'Enable' : 'Suspend'; ?></button>
                                    </form>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <?php wp_nonce_field('nymia_user_action_'.$user->ID); ?>
                                        <input type="hidden" name="action" value="nymia_user_action" />
                                        <input type="hidden" name="user_id" value="<?php echo intval($user->ID); ?>" />
                                        <input type="hidden" name="nymia_action" value="ban" />
                                        <button class="button nymia-btn danger small" type="submit">Ban</button>
                                    </form>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                        <?php wp_nonce_field('nymia_user_action_'.$user->ID); ?>
                                        <input type="hidden" name="action" value="nymia_user_action" />
                                        <input type="hidden" name="user_id" value="<?php echo intval($user->ID); ?>" />
                                        <input type="hidden" name="nymia_action" value="delete" />
                                        <button class="button nymia-btn danger outline small" type="submit">Delete</button>
                                    </form>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <?php wp_nonce_field('nymia_user_action_'.$user->ID); ?>
                                        <input type="hidden" name="action" value="nymia_user_action" />
                                        <input type="hidden" name="user_id" value="<?php echo intval($user->ID); ?>" />
                                        <input type="hidden" name="nymia_action" value="role_change" />
                                        <select name="new_role" class="nymia-select small">
                                            <?php foreach ($all_roles as $r_key => $r_obj): ?>
                                                <option value="<?php echo esc_attr($r_key); ?>"><?php echo esc_html($r_obj['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="button nymia-btn small" type="submit">Change Role</button>
                                    </form>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <?php wp_nonce_field('nymia_user_action_'.$user->ID); ?>
                                        <input type="hidden" name="action" value="nymia_user_action" />
                                        <input type="hidden" name="user_id" value="<?php echo intval($user->ID); ?>" />
                                        <input type="hidden" name="nymia_action" value="<?php echo $kyc_status==='approved' ? 'kyc_reset' : 'kyc_approve'; ?>" />
                                        <button class="button nymia-btn small" type="submit"><?php echo $kyc_status==='approved' ? 'Reset KYC' : 'Approve KYC'; ?></button>
                                    </form>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <?php wp_nonce_field('nymia_user_action_'.$user->ID); ?>
                                        <input type="hidden" name="action" value="nymia_user_action" />
                                        <input type="hidden" name="user_id" value="<?php echo intval($user->ID); ?>" />
                                        <input type="hidden" name="nymia_action" value="kyc_reject" />
                                        <button class="button nymia-btn outline small" type="submit">Reject KYC</button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <?php echo paginate_links(array(
                                'base' => add_query_arg('paged','%#%'),
                                'format' => '',
                                'prev_text' => __('«'),
                                'next_text' => __('»'),
                                'total' => $total_pages,
                                'current' => $paged
                            )); ?>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ======================================== -->
    <!-- NYMIA BRAND STYLES -->
    <!-- ======================================== -->
    <style>
        /* Dashboard Wrapper - Brand Dark Theme */
        .nymia-admin-dashboard-wrap {
            background: #121212;
            padding: 30px 0;
            margin: 0;
            min-height: calc(100vh - 32px);
            max-width: 100%;
        }
        
        .nymia-admin-title {
            color: #ffffff;
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
            background: linear-gradient(135deg, #D14619, #8B2A0F);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nymia-admin-subtitle {
            color: #888;
            font-size: 16px;
            margin: 0 0 30px 0;
        }
        
        /* Admin Cards */
        .nymia-admin-card {
            background: #161616;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .nymia-card-header {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(199, 84, 26, 0.1);
        }
        
        .nymia-card-title {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 5px 0;
        }
        
        .nymia-card-subtitle {
            color: #888;
            font-size: 14px;
            margin: 0;
        }
        
        .nymia-card-body {
            padding: 0;
        }
        
        /* Filter Bar */
        .nymia-filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        /* Form Elements */
        .nymia-input,
        .nymia-select {
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .nymia-input:focus,
        .nymia-select:focus {
            outline: none;
            border-color: #C7541A;
            box-shadow: 0 0 0 3px rgba(199, 84, 26, 0.1);
        }
        
        /* Pills */
        .nymia-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .nymia-pill {
            display: inline-block;
            padding: 8px 12px;
            color: #ffffff;
            text-decoration: none;
            border-radius: 999px;
            border: 1px solid rgba(199, 84, 26, 0.5);
            background: rgba(199, 84, 26, 0.08);
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .nymia-pill:hover {
            background: rgba(199, 84, 26, 0.15);
            border-color: rgba(199, 84, 26, 0.7);
        }
        
        /* Buttons */
        .nymia-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .nymia-btn-primary {
            background: linear-gradient(135deg, #D14619, #8B2A0F);
            color: #ffffff;
        }
        
        .nymia-btn-primary:hover {
            background: linear-gradient(135deg, #E85A2A, #A63312);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(209, 70, 25, 0.4);
        }
        
        .nymia-btn-secondary {
            background: #1a1a1a;
            color: #ffffff;
            border: 1px solid rgba(199, 84, 26, 0.3);
        }
        
        .nymia-btn-secondary:hover {
            border-color: #C7541A;
            background: #1f1f1f;
        }
        
        .nymia-btn.small {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .nymia-btn.outline {
            background: transparent;
            border: 1px solid rgba(199, 84, 26, 0.7);
            color: #ffffff;
        }
        
        .nymia-btn.outline:hover {
            background: rgba(199, 84, 26, 0.1);
        }
        
        .nymia-btn.danger {
            background: linear-gradient(135deg, #dc3545, #8B1A22);
        }
        
        .nymia-btn.danger:hover {
            background: linear-gradient(135deg, #f55, #a22);
        }
        
        .nymia-btn.danger.outline {
            background: transparent;
            border: 1px solid rgba(220, 53, 69, 0.7);
            color: #ffffff;
        }
        
        /* Tables */
        .nymia-table-wrapper {
            overflow-x: auto;
        }
        
        .nymia-admin-table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .nymia-admin-table thead {
            background: #161616;
        }
        
        .nymia-admin-table th {
            padding: 15px;
            text-align: left;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            border-bottom: 1px solid rgba(199, 84, 26, 0.2);
        }
        
        .nymia-admin-table td {
            padding: 15px;
            color: #cccccc;
            font-size: 14px;
            border-bottom: 1px solid rgba(199, 84, 26, 0.1);
        }
        
        .nymia-admin-table tbody tr:hover {
            background: #1f1f1f;
        }
        
        .nymia-empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
            font-size: 14px;
        }
        
        /* Action Group */
        .nymia-action-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        
        /* Badges */
        .nymia-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        
        .nymia-badge:before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        
        .nymia-badge-active {
            background: rgba(0, 163, 42, 0.15);
            color: #00a32a;
        }
        
        .nymia-badge-suspended {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .nymia-badge-banned {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .nymia-badge-kyc-approved {
            background: rgba(0, 163, 42, 0.15);
            color: #00a32a;
        }
        
        .nymia-badge-kyc-rejected {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .nymia-badge-kyc-pending {
            background: rgba(199, 84, 26, 0.15);
            color: #C7541A;
        }
        
        /* Pagination */
        .tablenav {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(199, 84, 26, 0.1);
        }
        
        .tablenav-pages {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .tablenav-pages a,
        .tablenav-pages span {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid rgba(199, 84, 26, 0.3);
            background: #1a1a1a;
            transition: all 0.3s ease;
        }
        
        .tablenav-pages a:hover {
            background: rgba(199, 84, 26, 0.2);
            border-color: #C7541A;
        }
        
        .tablenav-pages .current {
            background: linear-gradient(135deg, #D14619, #8B2A0F);
            border-color: #C7541A;
            color: #ffffff;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nymia-admin-dashboard-wrap {
                padding: 20px 0;
            }
            
            .nymia-filter-bar {
                flex-direction: column;
            }
            
            .nymia-filter-bar .nymia-input,
            .nymia-filter-bar .nymia-select,
            .nymia-filter-bar .nymia-btn {
                width: 100%;
            }
            
            .nymia-action-group {
                gap: 6px;
            }
            
            .nymia-admin-table {
                font-size: 12px;
            }
            
            .nymia-admin-table th,
            .nymia-admin-table td {
                padding: 10px;
            }
        }
    </style>
<?php
}


