<?php
/**
 * ========================================
 * NYMIA THEME - PRODUCT MANAGEMENT (ADMIN)
 * ========================================
 * Admin page to manage audio products: list/search/filter and content controls
 * - Filters: status, creator, category, date
 * - Actions: enable/disable, edit, approve/reject
 * - Category & Tag Management
 */

if (!defined('ABSPATH')) { exit; }

// Meta keys for product status
define('NYMIA_PRODUCT_STATUS_META', 'nymia_product_status'); // published|pending|disabled
define('NYMIA_PRODUCT_APPROVAL_META', 'nymia_product_approval'); // approved|pending|rejected

/**
 * Handle admin POST actions for product management
 */
function nymia_handle_product_management_action() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized', 'nymia'));
    }
    
    $action = isset($_POST['nymia_action']) ? sanitize_text_field($_POST['nymia_action']) : '';
    $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
    $product_type = isset($_POST['product_type']) ? sanitize_text_field($_POST['product_type']) : 'audio'; // audio or ebook
    $redirect = isset($_POST['_wp_http_referer']) ? esc_url_raw($_POST['_wp_http_referer']) : admin_url('admin.php?page=nymia-product-management');

    if (!$action || !$product_id || !check_admin_referer('nymia_product_action_'.$product_id)) {
        wp_safe_redirect($redirect);
        exit;
    }

    // Handle different actions
    switch ($action) {
        case 'approve':
            // Update product approval status
            nymia_update_product_status($product_id, $product_type, 'approved', 'published');
            break;
        case 'reject':
            nymia_update_product_status($product_id, $product_type, 'rejected', 'disabled');
            break;
        case 'enable':
            nymia_update_product_status($product_id, $product_type, null, 'published');
            break;
        case 'disable':
            nymia_update_product_status($product_id, $product_type, null, 'disabled');
            break;
        case 'delete':
            nymia_delete_product($product_id, $product_type);
            break;
    }

    wp_safe_redirect(add_query_arg('updated', '1', $redirect));
    exit;
}
add_action('admin_post_nymia_product_action', 'nymia_handle_product_management_action');

/**
 * Handle category actions
 */
function nymia_handle_category_action() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized', 'nymia'));
    }
    
    if (!check_admin_referer('nymia_category_action')) {
        wp_safe_redirect(admin_url('admin.php?page=nymia-product-management'));
        exit;
    }
    
    $action = isset($_POST['category_action']) ? sanitize_text_field($_POST['category_action']) : '';
    $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
    
    $categories = get_transient('nymia_product_categories');
    if (!is_array($categories)) {
        $categories = array();
    }
    
    if ($action === 'add' && !empty($category_name)) {
        if (!in_array($category_name, $categories)) {
            $categories[] = $category_name;
            sort($categories);
            set_transient('nymia_product_categories', $categories, 30 * DAY_IN_SECONDS);
        }
    } elseif ($action === 'delete' && !empty($category_name)) {
        $categories = array_filter($categories, function($cat) use ($category_name) {
            return $cat !== $category_name;
        });
        set_transient('nymia_product_categories', array_values($categories), 30 * DAY_IN_SECONDS);
    }
    
    wp_safe_redirect(admin_url('admin.php?page=nymia-product-management&updated=1'));
    exit;
}
add_action('admin_post_nymia_category_action', 'nymia_handle_category_action');

/**
 * Handle tag actions
 */
function nymia_handle_tag_action() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized', 'nymia'));
    }
    
    if (!check_admin_referer('nymia_tag_action')) {
        wp_safe_redirect(admin_url('admin.php?page=nymia-product-management'));
        exit;
    }
    
    $action = isset($_POST['tag_action']) ? sanitize_text_field($_POST['tag_action']) : '';
    $tag_name = isset($_POST['tag_name']) ? sanitize_text_field($_POST['tag_name']) : '';
    
    $tags = get_transient('nymia_product_tags');
    if (!is_array($tags)) {
        $tags = array();
    }
    
    if ($action === 'add' && !empty($tag_name)) {
        if (!in_array($tag_name, $tags)) {
            $tags[] = $tag_name;
            sort($tags);
            set_transient('nymia_product_tags', $tags, 30 * DAY_IN_SECONDS);
        }
    } elseif ($action === 'delete' && !empty($tag_name)) {
        $tags = array_filter($tags, function($tag) use ($tag_name) {
            return $tag !== $tag_name;
        });
        set_transient('nymia_product_tags', array_values($tags), 30 * DAY_IN_SECONDS);
    }
    
    wp_safe_redirect(admin_url('admin.php?page=nymia-product-management&updated=1'));
    exit;
}
add_action('admin_post_nymia_tag_action', 'nymia_handle_tag_action');

/**
 * Update product status
 */
function nymia_update_product_status($product_id, $product_type, $approval = null, $status = null) {
    $transient_key = ($product_type === 'ebook') ? 'nymia_all_ebooks' : 'nymia_all_audio';
    $all_products = get_transient($transient_key);
    
    if (!is_array($all_products)) {
        // Get from user transients
        $all_products = array();
        $users = get_users();
        foreach ($users as $user) {
            $user_key = ($product_type === 'ebook') ? 'nymia_user_ebooks_' . $user->ID : 'nymia_user_audio_' . $user->ID;
            $user_products = get_transient($user_key);
            if (is_array($user_products)) {
                $all_products = array_merge($all_products, $user_products);
            }
        }
    }
    
    foreach ($all_products as &$product) {
        if (isset($product['id']) && (string)$product['id'] === (string)$product_id) {
            if ($approval !== null) {
                $product['approval_status'] = $approval;
            }
            if ($status !== null) {
                $product['status'] = $status;
            }
            break;
        }
    }
    
    set_transient($transient_key, $all_products, 30 * DAY_IN_SECONDS);
}

/**
 * Delete product
 */
function nymia_delete_product($product_id, $product_type) {
    $users = get_users();
    foreach ($users as $user) {
        $user_key = ($product_type === 'ebook') ? 'nymia_user_ebooks_' . $user->ID : 'nymia_user_audio_' . $user->ID;
        $user_products = get_transient($user_key);
        if (is_array($user_products)) {
            $user_products = array_filter($user_products, function($product) use ($product_id) {
                return isset($product['id']) && (string)$product['id'] !== (string)$product_id;
            });
            set_transient($user_key, array_values($user_products), 30 * DAY_IN_SECONDS);
        }
    }
}

/**
 * Get all products (audio and ebook)
 */
function nymia_get_all_products($type = 'all') {
    $products = array();
    $users = get_users();
    
    foreach ($users as $user) {
        // Get audio products
        if ($type === 'all' || $type === 'audio') {
            $audio_key = 'nymia_user_audio_' . $user->ID;
            $audio_posts = get_transient($audio_key);
            if (is_array($audio_posts)) {
                foreach ($audio_posts as $audio) {
                    $audio['type'] = 'audio';
                    $audio['creator_id'] = $user->ID;
                    $audio['creator_name'] = $user->display_name;
                    $products[] = $audio;
                }
            }
        }
        
        // Get ebook products
        if ($type === 'all' || $type === 'ebook') {
            $ebook_key = 'nymia_user_ebooks_' . $user->ID;
            $ebook_posts = get_transient($ebook_key);
            if (is_array($ebook_posts)) {
                foreach ($ebook_posts as $ebook) {
                    $ebook['type'] = 'ebook';
                    $ebook['creator_id'] = $user->ID;
                    $ebook['creator_name'] = $user->display_name;
                    $products[] = $ebook;
                }
            }
        }
    }
    
    return $products;
}

/**
 * Render Product Management page
 */
function nymia_product_management_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Filters
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $creator = isset($_GET['creator']) ? intval($_GET['creator']) : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    $product_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'all';
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

    // Get all products
    $all_products = nymia_get_all_products($product_type);

    // Apply filters
    $filtered_products = array();
    foreach ($all_products as $product) {
        // Search filter
        if ($search) {
            $search_lower = strtolower($search);
            $title_match = isset($product['title']) && stripos(strtolower($product['title']), $search_lower) !== false;
            $author_match = isset($product['author']) && stripos(strtolower($product['author']), $search_lower) !== false;
            if (!$title_match && !$author_match) continue;
        }
        
        // Status filter
        if ($status) {
            $product_status = isset($product['status']) ? $product['status'] : 'published';
            if ($product_status !== $status) continue;
        }
        
        // Creator filter
        if ($creator && isset($product['creator_id']) && (int)$product['creator_id'] !== $creator) continue;
        
        // Category filter
        if ($category && isset($product['category']) && $product['category'] !== $category) continue;
        
        // Date filter
        if ($date_from && isset($product['date']) && strtotime($product['date']) < strtotime($date_from)) continue;
        if ($date_to && isset($product['date']) && strtotime($product['date']) > strtotime($date_to . ' 23:59:59')) continue;
        
        $filtered_products[] = $product;
    }
    
    // Get unique creators for filter
    $creators = array();
    foreach ($all_products as $product) {
        if (isset($product['creator_id']) && isset($product['creator_name'])) {
            $creators[$product['creator_id']] = $product['creator_name'];
        }
    }
    
    // Get unique categories for filter
    $categories = array();
    foreach ($all_products as $product) {
        if (isset($product['category']) && !empty($product['category'])) {
            $categories[$product['category']] = $product['category'];
        }
    }
    sort($categories);

    // Pagination
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $total = count($filtered_products);
    $total_pages = ceil($total / $per_page);
    $offset = ($current_page - 1) * $per_page;
    $paginated_products = array_slice($filtered_products, $offset, $per_page);

    ?>
    <div class="wrap nymia-admin-dashboard-wrap">
        <h1 class="nymia-admin-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p class="nymia-admin-subtitle">Manage all audio files and products uploaded by creators</p>

        <!-- Header Filters -->
        <div class="nymia-admin-toolbar nymia-admin-card" style="margin-bottom: 24px;">
            <div class="nymia-card-body">
                <form method="get" action="" class="nymia-toolbar-form">
                    <input type="hidden" name="page" value="nymia-product-management" />
                    <div class="nymia-toolbar-row">
                        <div class="nymia-toolbar-search">
                            <input type="text" class="nymia-input" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search products by title or creator..." />
                        </div>
                        <div class="nymia-toolbar-filters">
                            <select name="type" class="nymia-select">
                                <option value="all" <?php selected($product_type, 'all'); ?>>All Types</option>
                                <option value="audio" <?php selected($product_type, 'audio'); ?>>Audio</option>
                                <option value="ebook" <?php selected($product_type, 'ebook'); ?>>Ebook</option>
                            </select>
                            <select name="status" class="nymia-select">
                                <option value="">All Statuses</option>
                                <option value="published" <?php selected($status, 'published'); ?>>Published</option>
                                <option value="pending" <?php selected($status, 'pending'); ?>>Pending Review</option>
                                <option value="disabled" <?php selected($status, 'disabled'); ?>>Disabled</option>
                            </select>
                            <select name="creator" class="nymia-select">
                                <option value="">All Creators</option>
                                <?php foreach ($creators as $creator_id => $creator_name): ?>
                                    <option value="<?php echo intval($creator_id); ?>" <?php selected($creator, $creator_id); ?>><?php echo esc_html($creator_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="category" class="nymia-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo esc_attr($cat); ?>" <?php selected($category, $cat); ?>><?php echo esc_html($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" class="nymia-input" name="date_from" value="<?php echo esc_attr($date_from); ?>" placeholder="From" />
                            <input type="date" class="nymia-input" name="date_to" value="<?php echo esc_attr($date_to); ?>" placeholder="To" />
                            <button type="submit" class="button nymia-btn">Apply</button>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=nymia-product-management')); ?>" class="button nymia-btn outline">Reset</a>
                        </div>
                    </div>
                    <div class="nymia-pills" style="margin-top: 12px;">
                        <a class="nymia-pill" href="<?php echo esc_url(add_query_arg(array('status'=>'published'))); ?>">Published</a>
                        <a class="nymia-pill" href="<?php echo esc_url(add_query_arg(array('status'=>'pending'))); ?>">Pending Review</a>
                        <a class="nymia-pill" href="<?php echo esc_url(add_query_arg(array('status'=>'disabled'))); ?>">Disabled</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Products (<?php echo intval($total); ?>)</h2>
                <p class="nymia-card-subtitle">Manage audio files and products</p>
            </div>
            <div class="nymia-card-body">
                <table class="nymia-products-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Creator</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginated_products)): ?>
                            <tr><td colspan="8" class="nymia-empty-state">No products found</td></tr>
                        <?php else: ?>
                            <?php foreach ($paginated_products as $product): 
                                $product_status = isset($product['status']) ? $product['status'] : 'published';
                                $approval_status = isset($product['approval_status']) ? $product['approval_status'] : 'pending';
                                $product_type_display = isset($product['type']) ? ucfirst($product['type']) : 'Audio';
                            ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <?php if (isset($product['cover_image']) && !empty($product['cover_image'])): ?>
                                            <img src="<?php echo esc_url($product['cover_image']); ?>" alt="" style="width:48px; height:48px; border-radius:8px; object-fit:cover; border:2px solid rgba(199,84,26,0.3);" />
                                        <?php else: ?>
                                            <div style="width:48px; height:48px; border-radius:8px; background:rgba(199,84,26,0.2); display:flex; align-items:center; justify-content:center; font-size:20px;"><?php echo $product_type_display === 'Audio' ? '🎵' : '📚'; ?></div>
                                        <?php endif; ?>
                                        <div>
                                            <strong style="display:block; color:#fff;"><?php echo esc_html(isset($product['title']) ? $product['title'] : 'Untitled'); ?></strong>
                                            <span style="display:inline-block; font-size:11px; color:rgba(255,255,255,0.6);"><?php echo esc_html(isset($product['author']) ? $product['author'] : 'Unknown'); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="nymia-badge nymia-badge-type"><?php echo esc_html($product_type_display); ?></span></td>
                                <td><?php echo esc_html(isset($product['creator_name']) ? $product['creator_name'] : (isset($product['author']) ? $product['author'] : 'Unknown')); ?></td>
                                <td><?php echo esc_html(isset($product['category']) ? $product['category'] : '—'); ?></td>
                                <td>
                                    <?php if (isset($product['paid_access']) && $product['paid_access'] === 'yes'): ?>
                                        <span class="nymia-price">$<?php echo esc_html(number_format(isset($product['price']) ? $product['price'] : 0, 2)); ?></span>
                                    <?php else: ?>
                                        <span class="nymia-price-free">Free</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($approval_status === 'pending'): ?>
                                        <span class="nymia-badge nymia-badge-pending">Pending Review</span>
                                    <?php elseif ($product_status === 'published'): ?>
                                        <span class="nymia-badge nymia-badge-active">Published</span>
                                    <?php elseif ($product_status === 'disabled'): ?>
                                        <span class="nymia-badge nymia-badge-disabled">Disabled</span>
                                    <?php else: ?>
                                        <span class="nymia-badge nymia-badge-pending"><?php echo esc_html(ucfirst($product_status)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(isset($product['date']) ? date_i18n('Y-m-d', strtotime($product['date'])) : '—'); ?></td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <div class="nymia-action-group">
                                        <?php if ($approval_status === 'pending'): ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <?php wp_nonce_field('nymia_product_action_'.$product['id']); ?>
                                                <input type="hidden" name="action" value="nymia_product_action" />
                                                <input type="hidden" name="product_id" value="<?php echo esc_attr($product['id']); ?>" />
                                                <input type="hidden" name="product_type" value="<?php echo esc_attr(isset($product['type']) ? $product['type'] : 'audio'); ?>" />
                                                <input type="hidden" name="nymia_action" value="approve" />
                                                <button class="button nymia-btn small" type="submit">Approve</button>
                                            </form>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <?php wp_nonce_field('nymia_product_action_'.$product['id']); ?>
                                                <input type="hidden" name="action" value="nymia_product_action" />
                                                <input type="hidden" name="product_id" value="<?php echo esc_attr($product['id']); ?>" />
                                                <input type="hidden" name="product_type" value="<?php echo esc_attr(isset($product['type']) ? $product['type'] : 'audio'); ?>" />
                                                <input type="hidden" name="nymia_action" value="reject" />
                                                <button class="button nymia-btn danger outline small" type="submit">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($product_status === 'published'): ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <?php wp_nonce_field('nymia_product_action_'.$product['id']); ?>
                                                <input type="hidden" name="action" value="nymia_product_action" />
                                                <input type="hidden" name="product_id" value="<?php echo esc_attr($product['id']); ?>" />
                                                <input type="hidden" name="product_type" value="<?php echo esc_attr(isset($product['type']) ? $product['type'] : 'audio'); ?>" />
                                                <input type="hidden" name="nymia_action" value="disable" />
                                                <button class="button nymia-btn outline small" type="submit">Disable</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <?php wp_nonce_field('nymia_product_action_'.$product['id']); ?>
                                                <input type="hidden" name="action" value="nymia_product_action" />
                                                <input type="hidden" name="product_id" value="<?php echo esc_attr($product['id']); ?>" />
                                                <input type="hidden" name="product_type" value="<?php echo esc_attr(isset($product['type']) ? $product['type'] : 'audio'); ?>" />
                                                <input type="hidden" name="nymia_action" value="enable" />
                                                <button class="button nymia-btn small" type="submit">Enable</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                            <?php wp_nonce_field('nymia_product_action_'.$product['id']); ?>
                                            <input type="hidden" name="action" value="nymia_product_action" />
                                            <input type="hidden" name="product_id" value="<?php echo esc_attr($product['id']); ?>" />
                                            <input type="hidden" name="product_type" value="<?php echo esc_attr(isset($product['type']) ? $product['type'] : 'audio'); ?>" />
                                            <input type="hidden" name="nymia_action" value="delete" />
                                            <button class="button nymia-btn danger outline small" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav" style="margin-top: 20px;">
                        <div class="tablenav-pages">
                            <?php echo paginate_links(array(
                                'base' => add_query_arg('paged','%#%'),
                                'format' => '',
                                'prev_text' => __('«'),
                                'next_text' => __('»'),
                                'total' => $total_pages,
                                'current' => $current_page
                            )); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Category & Tag Management -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Category & Tag Management</h2>
                <p class="nymia-card-subtitle">Create, edit, or delete product categories and tags</p>
            </div>
            <div class="nymia-card-body">
                <!-- Categories Section -->
                <div style="margin-bottom: 32px;">
                    <h3 style="color:#fff; font-size:1rem; font-weight:600; margin-bottom:16px;">Categories</h3>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <!-- Add Category -->
                        <div>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nymia-form-inline">
                                <?php wp_nonce_field('nymia_category_action'); ?>
                                <input type="hidden" name="action" value="nymia_category_action" />
                                <input type="hidden" name="category_action" value="add" />
                                <div style="display:flex; gap:10px;">
                                    <input type="text" name="category_name" class="nymia-input" placeholder="New category name" style="flex:1;" required />
                                    <button type="submit" class="button nymia-btn small">Add Category</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Category List -->
                        <div>
                            <div class="nymia-tag-list">
                                <?php 
                                $existing_categories = get_transient('nymia_product_categories');
                                if (!is_array($existing_categories)) {
                                    $existing_categories = array_unique($categories);
                                    set_transient('nymia_product_categories', $existing_categories, 30 * DAY_IN_SECONDS);
                                }
                                foreach ($existing_categories as $cat): 
                                ?>
                                <div class="nymia-tag-item">
                                    <span><?php echo esc_html($cat); ?></span>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                        <?php wp_nonce_field('nymia_category_action'); ?>
                                        <input type="hidden" name="action" value="nymia_category_action" />
                                        <input type="hidden" name="category_action" value="delete" />
                                        <input type="hidden" name="category_name" value="<?php echo esc_attr($cat); ?>" />
                                        <button type="submit" class="nymia-tag-delete" onclick="return confirm('Delete this category?');">×</button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags Section -->
                <div>
                    <h3 style="color:#fff; font-size:1rem; font-weight:600; margin-bottom:16px;">Tags</h3>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <!-- Add Tag -->
                        <div>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nymia-form-inline">
                                <?php wp_nonce_field('nymia_tag_action'); ?>
                                <input type="hidden" name="action" value="nymia_tag_action" />
                                <input type="hidden" name="tag_action" value="add" />
                                <div style="display:flex; gap:10px;">
                                    <input type="text" name="tag_name" class="nymia-input" placeholder="New tag name" style="flex:1;" required />
                                    <button type="submit" class="button nymia-btn small">Add Tag</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Tag List -->
                        <div>
                            <div class="nymia-tag-list">
                                <?php 
                                $existing_tags = get_transient('nymia_product_tags');
                                if (!is_array($existing_tags)) {
                                    $existing_tags = array();
                                    set_transient('nymia_product_tags', $existing_tags, 30 * DAY_IN_SECONDS);
                                }
                                foreach ($existing_tags as $tag): 
                                ?>
                                <div class="nymia-tag-item">
                                    <span><?php echo esc_html($tag); ?></span>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                        <?php wp_nonce_field('nymia_tag_action'); ?>
                                        <input type="hidden" name="action" value="nymia_tag_action" />
                                        <input type="hidden" name="tag_action" value="delete" />
                                        <input type="hidden" name="tag_name" value="<?php echo esc_attr($tag); ?>" />
                                        <button type="submit" class="nymia-tag-delete" onclick="return confirm('Delete this tag?');">×</button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($existing_tags)): ?>
                                    <p style="color:rgba(255,255,255,0.5); font-size:0.875rem;">No tags yet. Add your first tag above.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Include all styles from user-management.php and general-settings.php */
        .nymia-admin-dashboard-wrap { 
            background:#121212; 
            padding:30px 0; 
            margin:0;
            max-width: 100%;
            min-height: calc(100vh - 32px);
        }
        
        .nymia-admin-title { 
            color:#fff; 
            font-size:2rem; 
            font-weight:700; 
            margin-bottom:8px; 
        }
        
        .nymia-admin-subtitle {
            color: rgba(255,255,255,0.7);
            font-size:0.9375rem;
            margin-bottom:32px;
        }
        
        .nymia-admin-card { 
            background:#161616; 
            border:1px solid rgba(255,255,255,0.1); 
            border-radius:16px; 
            box-shadow:0 2px 8px rgba(0,0,0,0.2);
            margin-bottom:24px;
        }
        
        .nymia-card-header { 
            padding:24px 32px; 
            border-bottom:1px solid rgba(255,255,255,0.1); 
            background: rgba(199,84,26,0.05); 
        }
        
        .nymia-card-title { 
            color:#fff; 
            font-size:1.25rem; 
            font-weight:600; 
            margin:0; 
        }
        
        .nymia-card-subtitle {
            color: rgba(255,255,255,0.6);
            font-size:0.875rem;
            margin:4px 0 0 0;
        }
        
        .nymia-card-body { 
            padding:32px; 
        }
        
        .nymia-admin-toolbar .nymia-card-body { padding: 16px 20px; }
        .nymia-toolbar-form { display:block; }
        .nymia-toolbar-row { display:flex; align-items:center; gap:16px; }
        .nymia-toolbar-search { flex: 1; }
        .nymia-toolbar-filters { display:flex; align-items:center; gap:10px; flex-wrap: wrap; }
        
        .nymia-pills { display:flex; flex-wrap:wrap; gap:8px; }
        .nymia-pill { display:inline-block; padding:8px 12px; color:#fff; text-decoration:none; border-radius:999px; border:1px solid rgba(199,84,26,0.5); background: rgba(199,84,26,0.08); font-size:12px; font-weight:600; }
        .nymia-pill:hover { background: rgba(199,84,26,0.15); }
        
        .nymia-input, .nymia-select { background:#2a2a2a; border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px; padding:10px 12px; }
        .nymia-input:focus, .nymia-select:focus { outline:none; border-color:#C7541A; box-shadow:0 0 0 3px rgba(199,84,26,0.15); }
        
        .nymia-btn { background: linear-gradient(135deg, #D14619, #8B2A0F); color:#fff; border:none; border-radius:24px; padding:8px 14px; font-weight:600; box-shadow:0 4px 12px rgba(209,70,25,0.25); }
        .nymia-btn.small { padding:6px 10px; font-size:12px; }
        .nymia-btn.outline { background:transparent; border:1.5px solid rgba(199,84,26,0.7); color:#fff; box-shadow:none; }
        .nymia-btn.danger { background: linear-gradient(135deg, #dc3545, #8B1A22); box-shadow:0 4px 12px rgba(220,53,69,0.3); }
        .nymia-btn.danger.outline { background:transparent; border:1.5px solid rgba(220,53,69,0.7); color:#fff; }
        .nymia-btn:hover { transform: translateY(-1px); filter: brightness(1.05); }
        
        .nymia-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; letter-spacing:0.2px; }
        .nymia-badge:before { content:''; display:inline-block; width:6px; height:6px; border-radius:50%; background: currentColor; }
        .nymia-badge-active { background: rgba(0,163,42,0.15); color:#00a32a; }
        .nymia-badge-pending { background: rgba(255,193,7,0.15); color:#ffc107; }
        .nymia-badge-disabled { background: rgba(220,53,69,0.15); color:#dc3545; }
        .nymia-badge-type { background: rgba(199,84,26,0.15); color:#C7541A; }
        
        .nymia-products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .nymia-products-table thead {
            background: rgba(199, 84, 26, 0.05);
        }
        
        .nymia-products-table th {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.8125rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nymia-products-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #ffffff;
            font-size: 0.9375rem;
        }
        
        .nymia-products-table tbody tr:hover {
            background: rgba(199, 84, 26, 0.05);
        }
        
        .nymia-price {
            color: #C7541A;
            font-weight: 600;
        }
        
        .nymia-price-free {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .nymia-action-group { display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
        
        .nymia-empty-state {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            padding: 40px;
            font-size: 0.9375rem;
        }
        
        /* Tag Management */
        .nymia-tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .nymia-tag-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(199, 84, 26, 0.1);
            border: 1px solid rgba(199, 84, 26, 0.3);
            border-radius: 20px;
            color: #fff;
            font-size: 0.875rem;
        }
        
        .nymia-tag-delete {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .nymia-tag-delete:hover {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        
        .nymia-form-inline {
            display: block;
        }
        
        @media (max-width: 1200px) {
            .nymia-toolbar-row { flex-direction: column; align-items: stretch; }
            .nymia-toolbar-filters { justify-content: flex-start; }
        }
        
        @media (max-width: 782px) {
            .nymia-admin-dashboard-wrap {
                padding: 20px 0;
            }
            
            .nymia-card-header,
            .nymia-card-body {
                padding:20px;
            }
        }
    </style>
    <?php
}

