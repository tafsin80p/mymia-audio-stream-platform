<?php
/**
 * ========================================
 * NYMIA THEME - FINANCIAL & COMMISSION MANAGEMENT
 * ========================================
 * Manage platform finances, creator commissions, and transactions
 * 
 * @package Nymia
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Financial & Commission Management Page
 */
function nymia_financial_management_page() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Handle form submissions
    if (isset($_POST['save_commission_settings']) && check_admin_referer('nymia_financial_settings')) {
        update_option('nymia_global_commission', floatval($_POST['global_commission']));
        
        // Handle per-creator commissions
        if (isset($_POST['creator_commissions']) && is_array($_POST['creator_commissions'])) {
            update_option('nymia_creator_commissions', $_POST['creator_commissions']);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }
    
    if (isset($_POST['save_tipping_settings']) && check_admin_referer('nymia_financial_settings')) {
        update_option('nymia_tipping_enabled', isset($_POST['tipping_enabled']) ? '1' : '0');
        update_option('nymia_commission_on_tips', isset($_POST['commission_on_tips']) ? '1' : '0');
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }
    
    // Get current settings
    $global_commission = get_option('nymia_global_commission', 30);
    $creator_commissions = get_option('nymia_creator_commissions', array());
    $tipping_enabled = get_option('nymia_tipping_enabled', '1');
    $commission_on_tips = get_option('nymia_commission_on_tips', '1');
    ?>
    <div class="wrap nymia-admin-dashboard-wrap">
        <h1 class="nymia-admin-title">Financial & Commission Management</h1>
        <p class="nymia-admin-subtitle">Manage platform finances, creator commissions, transactions, and payouts</p>
        
        <!-- Commission Settings -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Commission Settings</h2>
                <p class="nymia-card-subtitle">Configure global and per-creator commission rates</p>
            </div>
            <div class="nymia-card-body">
                <form method="post" action="">
                    <?php wp_nonce_field('nymia_financial_settings'); ?>
                    
                    <div class="nymia-form-group">
                        <label for="global_commission" class="nymia-label">Global Commission Percentage (Default)</label>
                        <div class="nymia-input-wrapper">
                            <input type="number" id="global_commission" name="global_commission" 
                                   value="<?php echo esc_attr($global_commission); ?>" 
                                   min="0" max="100" step="0.1" class="nymia-input">
                            <span class="nymia-input-suffix">%</span>
                        </div>
                        <p class="nymia-help-text">This is the default commission rate applied to all creators. Individual creators can have custom rates below.</p>
                    </div>
                    
                    <h3 class="nymia-subsection-title">Per-Creator Custom Commission</h3>
                    <p class="nymia-help-text">Override the global commission rate for specific creators.</p>
                    
                    <div class="nymia-per-creator-commissions">
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Select Creator</label>
                                <select class="nymia-select" id="creator_select">
                                    <option value="">-- Select Creator --</option>
                                    <?php
                                    $creators = get_users(array('role__in' => array('author', 'editor')));
                                    foreach ($creators as $creator) {
                                        echo '<option value="' . esc_attr($creator->ID) . '">' . esc_html($creator->display_name) . ' (' . esc_html($creator->user_email) . ')</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="nymia-form-group" style="flex: 0 0 200px;">
                                <label class="nymia-label">Custom Rate (%)</label>
                                <input type="number" id="creator_rate" class="nymia-input" min="0" max="100" step="0.1" placeholder="e.g., 25">
                            </div>
                            <div class="nymia-form-group" style="flex: 0 0 auto; padding-top: 32px;">
                                <button type="button" class="nymia-btn nymia-btn-secondary" id="add_creator_commission">Add</button>
                            </div>
                        </div>
                        
                        <div id="creator_commissions_list" class="nymia-commissions-list">
                            <?php if (!empty($creator_commissions)): ?>
                                <?php foreach ($creator_commissions as $creator_id => $rate): 
                                    $creator = get_userdata($creator_id);
                                    if ($creator):
                                ?>
                                <div class="nymia-commission-item">
                                    <div class="nymia-commission-info">
                                        <strong><?php echo esc_html($creator->display_name); ?></strong>
                                        <span class="nymia-commission-rate"><?php echo esc_html($rate); ?>%</span>
                                    </div>
                                    <input type="hidden" name="creator_commissions[<?php echo esc_attr($creator_id); ?>]" value="<?php echo esc_attr($rate); ?>">
                                    <button type="button" class="nymia-btn-text nymia-btn-remove" data-creator-id="<?php echo esc_attr($creator_id); ?>">Remove</button>
                                </div>
                                <?php endif; endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="nymia-form-actions">
                        <button type="submit" name="save_commission_settings" class="nymia-btn nymia-btn-primary">Save Commission Settings</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tipping System -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Tipping System</h2>
                <p class="nymia-card-subtitle">Configure tipping functionality and commission on tips</p>
            </div>
            <div class="nymia-card-body">
                <form method="post" action="">
                    <?php wp_nonce_field('nymia_financial_settings'); ?>
                    
                    <div class="nymia-form-group">
                        <label class="nymia-checkbox-label">
                            <input type="checkbox" name="tipping_enabled" value="1" <?php checked($tipping_enabled, '1'); ?> class="nymia-checkbox">
                            <span>Enable Tipping Platform-Wide</span>
                        </label>
                        <p class="nymia-help-text">Allow users to tip creators on the platform.</p>
                    </div>
                    
                    <div class="nymia-form-group">
                        <label class="nymia-checkbox-label">
                            <input type="checkbox" name="commission_on_tips" value="1" <?php checked($commission_on_tips, '1'); ?> class="nymia-checkbox">
                            <span>Apply Commission on Tips</span>
                        </label>
                        <p class="nymia-help-text">If enabled, the platform will take a commission from tips. If disabled, creators receive 100% of tips.</p>
                    </div>
                    
                    <div class="nymia-form-actions">
                        <button type="submit" name="save_tipping_settings" class="nymia-btn nymia-btn-primary">Save Tipping Settings</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Vendor Payout Management -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Vendor Payout Management</h2>
                <p class="nymia-card-subtitle">Manage automatic and manual payouts to creators</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-tabs">
                    <button class="nymia-tab-btn active" data-tab="automatic">Automatic Payouts</button>
                    <button class="nymia-tab-btn" data-tab="manual">Manual Payouts</button>
                    <button class="nymia-tab-btn" data-tab="history">Payout History</button>
                </div>
                
                <!-- Automatic Payouts -->
                <div class="nymia-tab-content active" id="tab-automatic">
                    <div class="nymia-form-group">
                        <label class="nymia-label">Payout Schedule</label>
                        <select class="nymia-select">
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Bi-Weekly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="nymia-form-group">
                        <label class="nymia-label">Payment Gateway</label>
                        <select class="nymia-select">
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="nymia-form-group">
                        <label class="nymia-label">Minimum Payout Amount</label>
                        <div class="nymia-input-wrapper">
                            <span class="nymia-input-prefix">$</span>
                            <input type="number" class="nymia-input" value="50" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="nymia-form-actions">
                        <button class="nymia-btn nymia-btn-primary">Save Automatic Payout Settings</button>
                    </div>
                </div>
                
                <!-- Manual Payouts -->
                <div class="nymia-tab-content" id="tab-manual">
                    <div class="nymia-form-group">
                        <label class="nymia-label">Select Creator</label>
                        <select class="nymia-select">
                            <option value="">-- Select Creator --</option>
                            <?php
                            $creators = get_users(array('role__in' => array('author', 'editor')));
                            foreach ($creators as $creator) {
                                echo '<option value="' . esc_attr($creator->ID) . '">' . esc_html($creator->display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nymia-form-group">
                        <label class="nymia-label">Payout Amount</label>
                        <div class="nymia-input-wrapper">
                            <span class="nymia-input-prefix">$</span>
                            <input type="number" class="nymia-input" placeholder="0.00" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="nymia-form-group">
                        <label class="nymia-label">Payment Method</label>
                        <select class="nymia-select">
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="nymia-form-actions">
                        <button class="nymia-btn nymia-btn-primary">Generate Payout Report</button>
                        <button class="nymia-btn nymia-btn-secondary">Initiate Manual Payout</button>
                    </div>
                </div>
                
                <!-- Payout History -->
                <div class="nymia-tab-content" id="tab-history">
                    <div class="nymia-table-wrapper">
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Creator</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="nymia-empty-state">No payout history available</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transaction Management -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Transaction Management</h2>
                <p class="nymia-card-subtitle">View all orders, transactions, and handle refunds</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-filter-bar">
                    <input type="text" class="nymia-input" placeholder="Search transactions..." style="flex: 1;">
                    <select class="nymia-select" style="width: 200px;">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="refunded">Refunded</option>
                    </select>
                    <select class="nymia-select" style="width: 150px;">
                        <option value="">All Methods</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                
                <div class="nymia-table-wrapper" style="margin-top: 20px;">
                    <table class="nymia-admin-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date</th>
                                <th>Buyer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="nymia-empty-state">No transactions found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tax & Invoicing -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Tax & Invoicing</h2>
                <p class="nymia-card-subtitle">Define tax rules and generate invoices</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-tabs">
                    <button class="nymia-tab-btn active" data-tab="tax">Tax Rules</button>
                    <button class="nymia-tab-btn" data-tab="invoice-admin">Admin → Creator Invoices</button>
                    <button class="nymia-tab-btn" data-tab="invoice-creator">Creator → Buyer Invoices</button>
                </div>
                
                <!-- Tax Rules -->
                <div class="nymia-tab-content active" id="tab-tax">
                    <div class="nymia-form-group">
                        <label class="nymia-label">Tax Type</label>
                        <select class="nymia-select">
                            <option value="vat">VAT (Value Added Tax)</option>
                            <option value="sales">Sales Tax</option>
                            <option value="none">No Tax</option>
                        </select>
                    </div>
                    <div class="nymia-form-group">
                        <label class="nymia-label">Tax Rate (%)</label>
                        <div class="nymia-input-wrapper">
                            <input type="number" class="nymia-input" value="0" min="0" max="100" step="0.1">
                            <span class="nymia-input-suffix">%</span>
                        </div>
                    </div>
                    <div class="nymia-form-group">
                        <label class="nymia-label">Tax ID / Registration Number</label>
                        <input type="text" class="nymia-input" placeholder="Enter tax registration number">
                    </div>
                    <div class="nymia-form-actions">
                        <button class="nymia-btn nymia-btn-primary">Save Tax Settings</button>
                    </div>
                </div>
                
                <!-- Admin → Creator Invoices -->
                <div class="nymia-tab-content" id="tab-invoice-admin">
                    <div class="nymia-form-group">
                        <label class="nymia-label">Select Creator</label>
                        <select class="nymia-select">
                            <option value="">-- Select Creator --</option>
                            <?php
                            $creators = get_users(array('role__in' => array('author', 'editor')));
                            foreach ($creators as $creator) {
                                echo '<option value="' . esc_attr($creator->ID) . '">' . esc_html($creator->display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nymia-form-group">
                        <label class="nymia-label">Date Range</label>
                        <div class="nymia-form-row">
                            <input type="date" class="nymia-input" style="flex: 1;">
                            <span style="padding: 0 10px; color: #888;">to</span>
                            <input type="date" class="nymia-input" style="flex: 1;">
                        </div>
                    </div>
                    <div class="nymia-form-actions">
                        <button class="nymia-btn nymia-btn-primary">Generate Invoice</button>
                        <button class="nymia-btn nymia-btn-secondary">Download PDF</button>
                    </div>
                </div>
                
                <!-- Creator → Buyer Invoices -->
                <div class="nymia-tab-content" id="tab-invoice-creator">
                    <div class="nymia-form-group">
                        <label class="nymia-label">Select Transaction</label>
                        <select class="nymia-select">
                            <option value="">-- Select Transaction --</option>
                        </select>
                    </div>
                    <div class="nymia-form-actions">
                        <button class="nymia-btn nymia-btn-primary">Generate Invoice</button>
                        <button class="nymia-btn nymia-btn-secondary">Download PDF</button>
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
        
        .nymia-input-suffix {
            position: absolute;
            right: 16px;
            color: #888;
            font-size: 14px;
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
        
        .nymia-subsection-title {
            color: #ffffff;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px 0;
        }
        
        .nymia-form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(199, 84, 26, 0.1);
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
        
        /* Commissions List */
        .nymia-per-creator-commissions {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 20px;
            margin-top: 15px;
        }
        
        .nymia-commissions-list {
            margin-top: 20px;
        }
        
        .nymia-commission-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #161616;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .nymia-commission-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nymia-commission-rate {
            color: #C7541A;
            font-weight: 600;
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
                parent.querySelector('#tab-' + tabName).classList.add('active');
            });
        });
        
        // Add creator commission
        document.getElementById('add_creator_commission')?.addEventListener('click', function() {
            const creatorSelect = document.getElementById('creator_select');
            const rateInput = document.getElementById('creator_rate');
            const list = document.getElementById('creator_commissions_list');
            
            if (!creatorSelect.value || !rateInput.value) {
                alert('Please select a creator and enter a rate');
                return;
            }
            
            const creatorId = creatorSelect.value;
            const creatorName = creatorSelect.options[creatorSelect.selectedIndex].text.split(' (')[0];
            const rate = rateInput.value;
            
            // Check if already exists
            if (list.querySelector(`input[name*="${creatorId}"]`)) {
                alert('This creator already has a custom commission rate');
                return;
            }
            
            // Create item
            const item = document.createElement('div');
            item.className = 'nymia-commission-item';
            item.innerHTML = `
                <div class="nymia-commission-info">
                    <strong>${creatorName}</strong>
                    <span class="nymia-commission-rate">${rate}%</span>
                </div>
                <input type="hidden" name="creator_commissions[${creatorId}]" value="${rate}">
                <button type="button" class="nymia-btn-text nymia-btn-remove" data-creator-id="${creatorId}">Remove</button>
            `;
            
            list.appendChild(item);
            
            // Clear inputs
            creatorSelect.value = '';
            rateInput.value = '';
            
            // Add remove listener
            item.querySelector('.nymia-btn-remove').addEventListener('click', function() {
                item.remove();
            });
        });
        
        // Remove creator commission
        document.querySelectorAll('.nymia-btn-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.nymia-commission-item').remove();
            });
        });
    </script>
    <?php
}

