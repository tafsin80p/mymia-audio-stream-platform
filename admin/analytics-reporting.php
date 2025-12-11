<?php
/**
 * ========================================
 * NYMIA THEME - ANALYTICS & REPORTING
 * ========================================
 * Enable data-driven decisions through comprehensive reports
 * 
 * @package Nymia
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analytics & Reporting Page
 */
function nymia_analytics_reporting_page() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap nymia-admin-dashboard-wrap">
        <h1 class="nymia-admin-title">Analytics & Reporting</h1>
        <p class="nymia-admin-subtitle">Enable data-driven decisions through comprehensive sales, earnings, user, and creator performance reports</p>
        
        <!-- Quick Stats Overview -->
        <div class="nymia-stats-grid" style="margin-bottom: 30px;">
            <div class="nymia-stat-card">
                <div class="nymia-stat-header">
                    <h3 class="nymia-stat-label">Total Revenue</h3>
                    <span class="nymia-stat-icon">💰</span>
                </div>
                <div class="nymia-stat-value">$12,450.00</div>
                <p class="nymia-stat-description">Last 30 days</p>
            </div>
            
            <div class="nymia-stat-card">
                <div class="nymia-stat-header">
                    <h3 class="nymia-stat-label">Platform Earnings</h3>
                    <span class="nymia-stat-icon">💵</span>
                </div>
                <div class="nymia-stat-value">$3,735.00</div>
                <p class="nymia-stat-description">30% commission</p>
            </div>
            
            <div class="nymia-stat-card">
                <div class="nymia-stat-header">
                    <h3 class="nymia-stat-label">Active Users</h3>
                    <span class="nymia-stat-icon">👥</span>
                </div>
                <div class="nymia-stat-value">1,234</div>
                <p class="nymia-stat-description">This month</p>
            </div>
            
            <div class="nymia-stat-card">
                <div class="nymia-stat-header">
                    <h3 class="nymia-stat-label">Top Creator</h3>
                    <span class="nymia-stat-icon">⭐</span>
                </div>
                <div class="nymia-stat-value">Creator Name</div>
                <p class="nymia-stat-description">$2,500 revenue</p>
            </div>
        </div>
        
        <!-- Reports Section -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Reports</h2>
                <p class="nymia-card-subtitle">Generate detailed reports by category</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-tabs">
                    <button class="nymia-tab-btn active" data-tab="sales">Sales Reports</button>
                    <button class="nymia-tab-btn" data-tab="earnings">Platform Earnings</button>
                    <button class="nymia-tab-btn" data-tab="users">User Reports</button>
                    <button class="nymia-tab-btn" data-tab="creators">Creator Performance</button>
                </div>
                
                <!-- Sales Reports -->
                <div class="nymia-tab-content active" id="tab-sales">
                    <form class="nymia-report-filters">
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Date Range</label>
                                <div class="nymia-form-row" style="margin-top: 0;">
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                    <span style="padding: 0 10px; color: #888; align-self: center;">to</span>
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                </div>
                            </div>
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Filter By</label>
                                <select class="nymia-select">
                                    <option value="">All</option>
                                    <option value="creator">Creator</option>
                                    <option value="product">Product</option>
                                    <option value="category">Category</option>
                                </select>
                            </div>
                            <div class="nymia-form-group" style="flex: 0 0 auto; padding-top: 32px;">
                                <button type="button" class="nymia-btn nymia-btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="nymia-chart-container" style="margin-top: 30px;">
                        <div class="nymia-chart-placeholder">
                            <p style="text-align: center; color: #888; padding: 60px 20px;">📊 Sales Chart Visualization<br><small>Chart would be rendered here</small></p>
                        </div>
                    </div>
                    
                    <div class="nymia-table-wrapper" style="margin-top: 30px;">
                        <div class="nymia-table-header">
                            <h3 class="nymia-subsection-title">Detailed Sales Data</h3>
                            <button class="nymia-btn nymia-btn-secondary">Export CSV</button>
                        </div>
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Creator</th>
                                    <th>Buyer</th>
                                    <th>Price</th>
                                    <th>Commission</th>
                                    <th>Creator Payout</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="nymia-empty-state">Select date range and generate report</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Platform Earnings Reports -->
                <div class="nymia-tab-content" id="tab-earnings">
                    <form class="nymia-report-filters">
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Date Range</label>
                                <div class="nymia-form-row" style="margin-top: 0;">
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                    <span style="padding: 0 10px; color: #888; align-self: center;">to</span>
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                </div>
                            </div>
                            <div class="nymia-form-group" style="flex: 0 0 auto; padding-top: 32px;">
                                <button type="button" class="nymia-btn nymia-btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="nymia-earnings-summary" style="margin-top: 30px;">
                        <div class="nymia-summary-cards">
                            <div class="nymia-summary-card">
                                <div class="nymia-summary-label">Total Commission</div>
                                <div class="nymia-summary-value">$3,735.00</div>
                            </div>
                            <div class="nymia-summary-card">
                                <div class="nymia-summary-label">From Tips</div>
                                <div class="nymia-summary-value">$450.00</div>
                            </div>
                            <div class="nymia-summary-card">
                                <div class="nymia-summary-label">Avg. Commission Rate</div>
                                <div class="nymia-summary-value">30%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="nymia-table-wrapper" style="margin-top: 30px;">
                        <div class="nymia-table-header">
                            <h3 class="nymia-subsection-title">Earnings Breakdown</h3>
                            <button class="nymia-btn nymia-btn-secondary">Export CSV</button>
                        </div>
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction Type</th>
                                    <th>Amount</th>
                                    <th>Commission Rate</th>
                                    <th>Platform Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="nymia-empty-state">Generate report to view earnings</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- User Reports -->
                <div class="nymia-tab-content" id="tab-users">
                    <form class="nymia-report-filters">
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Date Range</label>
                                <div class="nymia-form-row" style="margin-top: 0;">
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                    <span style="padding: 0 10px; color: #888; align-self: center;">to</span>
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                </div>
                            </div>
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">User Type</label>
                                <select class="nymia-select">
                                    <option value="">All Users</option>
                                    <option value="buyers">Buyers Only</option>
                                    <option value="creators">Creators Only</option>
                                </select>
                            </div>
                            <div class="nymia-form-group" style="flex: 0 0 auto; padding-top: 32px;">
                                <button type="button" class="nymia-btn nymia-btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="nymia-user-metrics" style="margin-top: 30px;">
                        <div class="nymia-metrics-grid">
                            <div class="nymia-metric-card">
                                <div class="nymia-metric-label">New Signups</div>
                                <div class="nymia-metric-value">245</div>
                                <div class="nymia-metric-change positive">+12% vs last period</div>
                            </div>
                            <div class="nymia-metric-card">
                                <div class="nymia-metric-label">Active Users</div>
                                <div class="nymia-metric-value">1,234</div>
                                <div class="nymia-metric-change positive">+8% vs last period</div>
                            </div>
                            <div class="nymia-metric-card">
                                <div class="nymia-metric-label">User Retention</div>
                                <div class="nymia-metric-value">72%</div>
                                <div class="nymia-metric-change positive">+5% vs last period</div>
                            </div>
                            <div class="nymia-metric-card">
                                <div class="nymia-metric-label">Avg. Session Duration</div>
                                <div class="nymia-metric-value">18 min</div>
                                <div class="nymia-metric-change negative">-3% vs last period</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="nymia-table-wrapper" style="margin-top: 30px;">
                        <div class="nymia-table-header">
                            <h3 class="nymia-subsection-title">User Acquisition & Engagement</h3>
                            <button class="nymia-btn nymia-btn-secondary">Export CSV</button>
                        </div>
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>New Signups</th>
                                    <th>Active Users</th>
                                    <th>Purchases</th>
                                    <th>Retention Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="nymia-empty-state">Generate report to view user data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Creator Performance Reports -->
                <div class="nymia-tab-content" id="tab-creators">
                    <form class="nymia-report-filters">
                        <div class="nymia-form-row">
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Date Range</label>
                                <div class="nymia-form-row" style="margin-top: 0;">
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                    <span style="padding: 0 10px; color: #888; align-self: center;">to</span>
                                    <input type="date" class="nymia-input" style="flex: 1;">
                                </div>
                            </div>
                            <div class="nymia-form-group" style="flex: 1;">
                                <label class="nymia-label">Sort By</label>
                                <select class="nymia-select">
                                    <option value="revenue">Revenue (High to Low)</option>
                                    <option value="sales">Sales Count</option>
                                    <option value="products">Product Count</option>
                                    <option value="rating">Average Rating</option>
                                </select>
                            </div>
                            <div class="nymia-form-group" style="flex: 0 0 auto; padding-top: 32px;">
                                <button type="button" class="nymia-btn nymia-btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="nymia-creators-ranking" style="margin-top: 30px;">
                        <h3 class="nymia-subsection-title">Top Performing Creators</h3>
                        <div class="nymia-creators-list">
                            <div class="nymia-creator-rank-card">
                                <div class="nymia-rank-number">1</div>
                                <div class="nymia-creator-info">
                                    <strong>Creator Name</strong>
                                    <span>15 products • 450 sales</span>
                                </div>
                                <div class="nymia-creator-stats">
                                    <div class="nymia-stat-item">
                                        <span class="nymia-stat-label-small">Revenue</span>
                                        <span class="nymia-stat-value-small">$2,500</span>
                                    </div>
                                    <div class="nymia-stat-item">
                                        <span class="nymia-stat-label-small">Rating</span>
                                        <span class="nymia-stat-value-small">4.8 ⭐</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="nymia-creator-rank-card">
                                <div class="nymia-rank-number">2</div>
                                <div class="nymia-creator-info">
                                    <strong>Another Creator</strong>
                                    <span>12 products • 320 sales</span>
                                </div>
                                <div class="nymia-creator-stats">
                                    <div class="nymia-stat-item">
                                        <span class="nymia-stat-label-small">Revenue</span>
                                        <span class="nymia-stat-value-small">$1,890</span>
                                    </div>
                                    <div class="nymia-stat-item">
                                        <span class="nymia-stat-label-small">Rating</span>
                                        <span class="nymia-stat-value-small">4.6 ⭐</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="nymia-creator-rank-card">
                                <div class="nymia-rank-number">3</div>
                                <div class="nymia-creator-info">
                                    <strong>Third Creator</strong>
                                    <span>8 products • 280 sales</span>
                                </div>
                                <div class="nymia-creator-stats">
                                    <div class="nymia-stat-item">
                                        <span class="nymia-stat-label-small">Revenue</span>
                                        <span class="nymia-stat-value-small">$1,650</span>
                                    </div>
                                    <div class="nymia-stat-item">
                                        <span class="nymia-stat-label-small">Rating</span>
                                        <span class="nymia-stat-value-small">4.7 ⭐</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="nymia-table-wrapper" style="margin-top: 30px;">
                        <div class="nymia-table-header">
                            <h3 class="nymia-subsection-title">Creator Performance Details</h3>
                            <button class="nymia-btn nymia-btn-secondary">Export CSV</button>
                        </div>
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Creator</th>
                                    <th>Products</th>
                                    <th>Total Sales</th>
                                    <th>Revenue</th>
                                    <th>Avg. Rating</th>
                                    <th>Products Stats</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="nymia-empty-state">Generate report to view creator performance</td>
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
        
        /* Stats Grid */
        .nymia-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .nymia-stat-card {
            background: #161616;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .nymia-stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .nymia-stat-label {
            color: #888;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }
        
        .nymia-stat-icon {
            font-size: 24px;
        }
        
        .nymia-stat-value {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #D14619, #8B2A0F);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .nymia-stat-description {
            color: #888;
            font-size: 13px;
            margin: 0;
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
        .nymia-select {
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
        .nymia-select:focus {
            outline: none;
            border-color: #C7541A;
            box-shadow: 0 0 0 3px rgba(199, 84, 26, 0.1);
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
            margin: 0;
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
        
        /* Chart */
        .nymia-chart-container {
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 12px;
            padding: 20px;
        }
        
        .nymia-chart-placeholder {
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Tables */
        .nymia-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
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
        
        /* Earnings Summary */
        .nymia-summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .nymia-summary-card {
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .nymia-summary-label {
            color: #888;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .nymia-summary-value {
            color: #C7541A;
            font-size: 24px;
            font-weight: 700;
        }
        
        /* User Metrics */
        .nymia-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .nymia-metric-card {
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 12px;
            padding: 20px;
        }
        
        .nymia-metric-label {
            color: #888;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .nymia-metric-value {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .nymia-metric-change {
            font-size: 12px;
            font-weight: 600;
        }
        
        .nymia-metric-change.positive {
            color: #4caf50;
        }
        
        .nymia-metric-change.negative {
            color: #f44336;
        }
        
        /* Creator Rankings */
        .nymia-creators-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .nymia-creator-rank-card {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 12px;
            padding: 20px;
        }
        
        .nymia-rank-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #D14619, #8B2A0F);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            flex-shrink: 0;
        }
        
        .nymia-creator-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .nymia-creator-info strong {
            color: #ffffff;
            font-size: 16px;
        }
        
        .nymia-creator-info span {
            color: #888;
            font-size: 13px;
        }
        
        .nymia-creator-stats {
            display: flex;
            gap: 30px;
        }
        
        .nymia-stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        
        .nymia-stat-label-small {
            color: #888;
            font-size: 12px;
        }
        
        .nymia-stat-value-small {
            color: #C7541A;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nymia-admin-dashboard-wrap {
                padding: 20px 0;
            }
            
            .nymia-form-row {
                flex-direction: column;
            }
            
            .nymia-stats-grid,
            .nymia-summary-cards,
            .nymia-metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .nymia-table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
    </script>
    <?php
}

