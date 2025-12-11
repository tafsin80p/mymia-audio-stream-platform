<?php
/**
 * ========================================
 * NYMIA THEME - MARKETING & PROMOTIONS
 * ========================================
 * Boost platform growth and engagement through coupons, featured products, and newsletters
 * 
 * @package Nymia
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Marketing & Promotions Page
 */
function nymia_marketing_promotions_page() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Handle form submissions
    if (isset($_POST['create_coupon']) && check_admin_referer('nymia_marketing_settings')) {
        // Handle coupon creation (placeholder)
        echo '<div class="notice notice-success is-dismissible"><p>Coupon created successfully!</p></div>';
    }
    
    if (isset($_POST['save_featured_products']) && check_admin_referer('nymia_marketing_settings')) {
        // Handle featured products save (placeholder)
        echo '<div class="notice notice-success is-dismissible"><p>Featured products updated successfully!</p></div>';
    }
    ?>
    <div class="wrap nymia-admin-dashboard-wrap">
        <h1 class="nymia-admin-title">Marketing & Promotions</h1>
        <p class="nymia-admin-subtitle">Boost platform growth and engagement through coupons, featured products, and newsletters</p>
        
        <!-- Coupons & Discounts -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Coupons & Discounts</h2>
                <p class="nymia-card-subtitle">Create and manage discount codes for products, creators, or platform-wide</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-tabs">
                    <button class="nymia-tab-btn active" data-tab="create-coupon">Create New Coupon</button>
                    <button class="nymia-tab-btn" data-tab="manage-coupons">Manage Coupons</button>
                </div>
                
                <!-- Create Coupon -->
                <div class="nymia-tab-content active" id="tab-create-coupon">
                    <form method="post" action="">
                        <?php wp_nonce_field('nymia_marketing_settings'); ?>
                        
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Coupon Code</label>
                                <input type="text" name="coupon_code" class="nymia-input" placeholder="SUMMER2024" required>
                                <p class="nymia-help-text">Enter a unique coupon code (uppercase, numbers, no spaces)</p>
                            </div>
                            <div class="nymia-form-group" style="flex: 0 0 150px;">
                                <button type="button" class="nymia-btn nymia-btn-secondary" id="generate_code" style="margin-top: 32px;">Generate</button>
                            </div>
                        </div>
                        
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Discount Type</label>
                                <select name="discount_type" class="nymia-select" required>
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount ($)</option>
                                </select>
                            </div>
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Discount Value</label>
                                <input type="number" name="discount_value" class="nymia-input" placeholder="10" min="0" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-label">Apply To</label>
                            <select name="apply_to" class="nymia-select" required>
                                <option value="global">Global (All Products)</option>
                                <option value="product">Specific Product(s)</option>
                                <option value="creator">Specific Creator(s)</option>
                                <option value="category">Category</option>
                            </select>
                        </div>
                        
                        <div class="nymia-form-group" id="product_selection" style="display: none;">
                            <label class="nymia-label">Select Products</label>
                            <select name="products[]" class="nymia-select" multiple style="min-height: 100px;">
                                <option value="">-- Select Products --</option>
                            </select>
                            <p class="nymia-help-text">Hold Ctrl/Cmd to select multiple products</p>
                        </div>
                        
                        <div class="nymia-form-group" id="creator_selection" style="display: none;">
                            <label class="nymia-label">Select Creators</label>
                            <select name="creators[]" class="nymia-select" multiple style="min-height: 100px;">
                                <option value="">-- Select Creators --</option>
                                <?php
                                $creators = get_users(array('role__in' => array('author', 'editor')));
                                foreach ($creators as $creator) {
                                    echo '<option value="' . esc_attr($creator->ID) . '">' . esc_html($creator->display_name) . '</option>';
                                }
                                ?>
                            </select>
                            <p class="nymia-help-text">Hold Ctrl/Cmd to select multiple creators</p>
                        </div>
                        
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Valid From</label>
                                <input type="datetime-local" name="valid_from" class="nymia-input">
                            </div>
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Valid Until</label>
                                <input type="datetime-local" name="valid_until" class="nymia-input">
                            </div>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-label">Usage Limits</label>
                            <div class="nymia-form-row">
                                <div class="nymia-form-group" style="flex: 1;">
                                    <input type="number" name="usage_limit" class="nymia-input" placeholder="Unlimited" min="0">
                                    <p class="nymia-help-text">Total usage limit (leave empty for unlimited)</p>
                                </div>
                                <div class="nymia-form-group" style="flex: 1;">
                                    <input type="number" name="usage_per_user" class="nymia-input" placeholder="1" min="1">
                                    <p class="nymia-help-text">Usage limit per user</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-label">Minimum Purchase Amount</label>
                            <div class="nymia-input-wrapper">
                                <span class="nymia-input-prefix">$</span>
                                <input type="number" name="min_purchase" class="nymia-input" placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-checkbox-label">
                                <input type="checkbox" name="coupon_active" value="1" checked class="nymia-checkbox">
                                <span>Active (Enable this coupon)</span>
                            </label>
                        </div>
                        
                        <div class="nymia-form-actions">
                            <button type="submit" name="create_coupon" class="nymia-btn nymia-btn-primary">Create Coupon</button>
                            <button type="button" class="nymia-btn nymia-btn-secondary">Preview</button>
                        </div>
                    </form>
                </div>
                
                <!-- Manage Coupons -->
                <div class="nymia-tab-content" id="tab-manage-coupons">
                    <div class="nymia-filter-bar">
                        <input type="text" class="nymia-input" placeholder="Search coupons..." style="flex: 1;">
                        <select class="nymia-select" style="width: 150px;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    
                    <div class="nymia-table-wrapper" style="margin-top: 20px;">
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Coupon Code</th>
                                    <th>Discount</th>
                                    <th>Apply To</th>
                                    <th>Usage</th>
                                    <th>Valid Until</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="nymia-empty-state">No coupons created yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Featured Products Management -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Featured Products Management</h2>
                <p class="nymia-card-subtitle">Manually select items for homepage or special sections</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-form-group">
                    <label class="nymia-label">Featured Section</label>
                    <select id="featured_section" class="nymia-select">
                        <option value="homepage">Homepage Featured</option>
                        <option value="trending">Trending Section</option>
                        <option value="new-releases">New Releases</option>
                        <option value="editor-pick">Editor's Pick</option>
                    </select>
                </div>
                
                <div class="nymia-form-group">
                    <label class="nymia-label">Select Products</label>
                    <div class="nymia-products-grid">
                        <div class="nymia-product-checkbox-card">
                            <input type="checkbox" id="prod1" class="nymia-checkbox">
                            <label for="prod1" class="nymia-product-label">
                                <div class="nymia-product-preview" style="background: linear-gradient(135deg, #D14619, #8B2A0F);"></div>
                                <div class="nymia-product-info">
                                    <strong>Sample Audio Track</strong>
                                    <span>by Creator Name</span>
                                    <span class="nymia-product-price">$9.99</span>
                                </div>
                            </label>
                        </div>
                        <div class="nymia-product-checkbox-card">
                            <input type="checkbox" id="prod2" class="nymia-checkbox">
                            <label for="prod2" class="nymia-product-label">
                                <div class="nymia-product-preview" style="background: linear-gradient(135deg, #1a1a1a, #2a2a2a);"></div>
                                <div class="nymia-product-info">
                                    <strong>Another Track</strong>
                                    <span>by Another Creator</span>
                                    <span class="nymia-product-price">$12.99</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <p class="nymia-help-text">Select products to feature in the chosen section</p>
                </div>
                
                <div class="nymia-form-group">
                    <label class="nymia-label">Display Order</label>
                    <select class="nymia-select">
                        <option value="manual">Manual (Drag to reorder)</option>
                        <option value="recent">Most Recent First</option>
                        <option value="popular">Most Popular First</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="price-low">Price: Low to High</option>
                    </select>
                </div>
                
                <div class="nymia-form-actions">
                    <form method="post" action="" style="display: inline;">
                        <?php wp_nonce_field('nymia_marketing_settings'); ?>
                        <button type="submit" name="save_featured_products" class="nymia-btn nymia-btn-primary">Save Featured Products</button>
                    </form>
                    <button class="nymia-btn nymia-btn-secondary">Preview Section</button>
                </div>
            </div>
        </div>
        
        <!-- Newsletters & Communications -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Newsletters & Communications</h2>
                <p class="nymia-card-subtitle">Send bulk emails to all users or segmented groups</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-tabs">
                    <button class="nymia-tab-btn active" data-tab="send-email">Send Email</button>
                    <button class="nymia-tab-btn" data-tab="email-templates">Email Templates</button>
                    <button class="nymia-tab-btn" data-tab="email-history">Email History</button>
                </div>
                
                <!-- Send Email -->
                <div class="nymia-tab-content active" id="tab-send-email">
                    <form method="post" action="">
                        <?php wp_nonce_field('nymia_marketing_settings'); ?>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-label">Recipient Group</label>
                            <select name="recipient_group" class="nymia-select" required>
                                <option value="all">All Users</option>
                                <option value="buyers">Buyers Only</option>
                                <option value="creators">Creators Only</option>
                                <option value="custom">Custom Selection</option>
                            </select>
                        </div>
                        
                        <div class="nymia-form-group" id="custom_recipients" style="display: none;">
                            <label class="nymia-label">Select Users</label>
                            <select name="custom_users[]" class="nymia-select" multiple style="min-height: 150px;">
                                <?php
                                $users = get_users();
                                foreach ($users as $user) {
                                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</option>';
                                }
                                ?>
                            </select>
                            <p class="nymia-help-text">Hold Ctrl/Cmd to select multiple users</p>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-label">Email Subject</label>
                            <input type="text" name="email_subject" class="nymia-input" placeholder="Enter email subject" required>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-label">Email Template</label>
                            <select name="email_template" class="nymia-select">
                                <option value="custom">Custom (Write below)</option>
                                <option value="newsletter">Newsletter Template</option>
                                <option value="promotion">Promotion Template</option>
                                <option value="announcement">Announcement Template</option>
                            </select>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-label">Email Content</label>
                            <textarea name="email_content" class="nymia-textarea" rows="15" placeholder="Write your email content here..." required></textarea>
                            <p class="nymia-help-text">You can use HTML formatting. Use [name] for personalization.</p>
                        </div>
                        
                        <div class="nymia-form-group">
                            <label class="nymia-checkbox-label">
                                <input type="checkbox" name="send_test" value="1" class="nymia-checkbox">
                                <span>Send test email to admin first</span>
                            </label>
                        </div>
                        
                        <div class="nymia-form-actions">
                            <button type="submit" name="send_newsletter" class="nymia-btn nymia-btn-primary">Send Email</button>
                            <button type="button" class="nymia-btn nymia-btn-secondary">Preview Email</button>
                            <button type="button" class="nymia-btn nymia-btn-secondary">Save as Draft</button>
                        </div>
                    </form>
                </div>
                
                <!-- Email Templates -->
                <div class="nymia-tab-content" id="tab-email-templates">
                    <div class="nymia-templates-list">
                        <div class="nymia-template-card">
                            <div class="nymia-template-header">
                                <h3>Newsletter Template</h3>
                                <div class="nymia-template-actions">
                                    <button class="nymia-btn-text">Edit</button>
                                    <button class="nymia-btn-text">Duplicate</button>
                                    <button class="nymia-btn-text nymia-btn-remove">Delete</button>
                                </div>
                            </div>
                            <p class="nymia-template-description">Standard newsletter template for regular updates</p>
                        </div>
                        
                        <div class="nymia-template-card">
                            <div class="nymia-template-header">
                                <h3>Promotion Template</h3>
                                <div class="nymia-template-actions">
                                    <button class="nymia-btn-text">Edit</button>
                                    <button class="nymia-btn-text">Duplicate</button>
                                    <button class="nymia-btn-text nymia-btn-remove">Delete</button>
                                </div>
                            </div>
                            <p class="nymia-template-description">Template for promotional campaigns and sales</p>
                        </div>
                        
                        <div class="nymia-template-card">
                            <div class="nymia-template-header">
                                <h3>Announcement Template</h3>
                                <div class="nymia-template-actions">
                                    <button class="nymia-btn-text">Edit</button>
                                    <button class="nymia-btn-text">Duplicate</button>
                                    <button class="nymia-btn-text nymia-btn-remove">Delete</button>
                                </div>
                            </div>
                            <p class="nymia-template-description">For important platform announcements</p>
                        </div>
                    </div>
                    
                    <div class="nymia-form-actions" style="margin-top: 20px;">
                        <button class="nymia-btn nymia-btn-primary">Create New Template</button>
                    </div>
                </div>
                
                <!-- Email History -->
                <div class="nymia-tab-content" id="tab-email-history">
                    <div class="nymia-table-wrapper">
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Date Sent</th>
                                    <th>Subject</th>
                                    <th>Recipients</th>
                                    <th>Status</th>
                                    <th>Opens</th>
                                    <th>Clicks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="nymia-empty-state">No emails sent yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
        
        /* Form Elements */
        .nymia-form-group {
            margin-bottom: 25px;
        }
        
        .nymia-label {
            display: block;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .nymia-input,
        .nymia-select,
        .nymia-textarea {
            width: 100%;
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .nymia-input:focus,
        .nymia-select:focus,
        .nymia-textarea:focus {
            outline: none;
            border-color: #C7541A;
            box-shadow: 0 0 0 3px rgba(199, 84, 26, 0.1);
        }
        
        .nymia-textarea {
            resize: vertical;
            font-family: inherit;
        }
        
        .nymia-input-wrapper {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .nymia-input-prefix {
            position: absolute;
            left: 16px;
            color: #888;
            font-size: 14px;
        }
        
        .nymia-input-wrapper .nymia-input {
            padding-left: 30px;
        }
        
        .nymia-help-text {
            color: #888;
            font-size: 13px;
            margin-top: 6px;
        }
        
        .nymia-checkbox-label {
            display: flex;
            align-items: center;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
        }
        
        .nymia-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            accent-color: #C7541A;
            cursor: pointer;
        }
        
        .nymia-form-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        
        .nymia-form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(199, 84, 26, 0.1);
            display: flex;
            gap: 10px;
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
        
        .nymia-btn-text {
            background: none;
            border: none;
            color: #C7541A;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .nymia-btn-text:hover {
            color: #E85A2A;
        }
        
        .nymia-btn-remove {
            color: #ff4444;
        }
        
        .nymia-btn-remove:hover {
            color: #ff6666;
        }
        
        /* Tabs */
        .nymia-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(199, 84, 26, 0.2);
        }
        
        .nymia-tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            color: #888;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            margin-bottom: -1px;
        }
        
        .nymia-tab-btn:hover {
            color: #ffffff;
        }
        
        .nymia-tab-btn.active {
            color: #C7541A;
            border-bottom-color: #C7541A;
        }
        
        .nymia-tab-content {
            display: none;
        }
        
        .nymia-tab-content.active {
            display: block;
        }
        
        /* Filter Bar */
        .nymia-filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
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
        
        /* Product Grid */
        .nymia-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .nymia-product-checkbox-card {
            position: relative;
        }
        
        .nymia-product-checkbox-card .nymia-checkbox {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
            width: 24px;
            height: 24px;
        }
        
        .nymia-product-label {
            display: block;
            background: #1a1a1a;
            border: 2px solid rgba(199, 84, 26, 0.2);
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .nymia-product-checkbox-card input:checked + .nymia-product-label {
            border-color: #C7541A;
            box-shadow: 0 0 0 3px rgba(199, 84, 26, 0.2);
        }
        
        .nymia-product-preview {
            width: 100%;
            height: 120px;
            background: #1a1a1a;
        }
        
        .nymia-product-info {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .nymia-product-info strong {
            color: #ffffff;
            font-size: 14px;
        }
        
        .nymia-product-info span {
            color: #888;
            font-size: 12px;
        }
        
        .nymia-product-price {
            color: #C7541A !important;
            font-weight: 600;
        }
        
        /* Templates */
        .nymia-templates-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .nymia-template-card {
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 12px;
            padding: 20px;
        }
        
        .nymia-template-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .nymia-template-header h3 {
            color: #ffffff;
            font-size: 16px;
            margin: 0;
        }
        
        .nymia-template-actions {
            display: flex;
            gap: 10px;
        }
        
        .nymia-template-description {
            color: #888;
            font-size: 14px;
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nymia-admin-dashboard-wrap {
                padding: 20px 0;
            }
            
            .nymia-form-row {
                flex-direction: column;
            }
            
            .nymia-filter-bar {
                flex-direction: column;
            }
            
            .nymia-products-grid {
                grid-template-columns: 1fr;
            }
            
            .nymia-form-actions {
                flex-direction: column;
            }
        }
    </style>
    
    <script>
        // Tab switching
        document.querySelectorAll('.nymia-tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                const parent = this.closest('.nymia-admin-card');
                
                // Remove active from all tabs and contents
                parent.querySelectorAll('.nymia-tab-btn').forEach(b => b.classList.remove('active'));
                parent.querySelectorAll('.nymia-tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active to clicked tab and corresponding content
                this.classList.add('active');
                parent.querySelector('#tab-' + tabName.replace('_', '-')).classList.add('active');
            });
        });
        
        // Coupon code generator
        document.getElementById('generate_code')?.addEventListener('click', function() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let code = '';
            for (let i = 0; i < 8; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.querySelector('input[name="coupon_code"]').value = code;
        });
        
        // Show/hide product/creator selection
        document.querySelector('select[name="apply_to"]')?.addEventListener('change', function() {
            document.getElementById('product_selection').style.display = this.value === 'product' ? 'block' : 'none';
            document.getElementById('creator_selection').style.display = this.value === 'creator' ? 'block' : 'none';
        });
        
        // Show/hide custom recipients
        document.querySelector('select[name="recipient_group"]')?.addEventListener('change', function() {
            document.getElementById('custom_recipients').style.display = this.value === 'custom' ? 'block' : 'none';
        });
    </script>
    <?php
}

