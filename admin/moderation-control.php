<?php
/**
 * ========================================
 * NYMIA THEME - MODERATION & QUALITY CONTROL
 * ========================================
 * Maintain platform integrity, quality, and compliance
 * 
 * @package Nymia
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Moderation & Quality Control Page
 */
function nymia_moderation_control_page() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap nymia-admin-dashboard-wrap">
        <h1 class="nymia-admin-title">Moderation & Quality Control</h1>
        <p class="nymia-admin-subtitle">Maintain platform integrity, quality, and compliance through reviews, reports, and quality checks</p>
        
        <!-- Review Management -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Review Management</h2>
                <p class="nymia-card-subtitle">Approve, edit, or delete user reviews</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-filter-bar">
                    <input type="text" class="nymia-input" placeholder="Search reviews..." style="flex: 1;">
                    <select class="nymia-select" style="width: 150px;">
                        <option value="">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select class="nymia-select" style="width: 150px;">
                        <option value="">All Products</option>
                        <option value="audio">Audio</option>
                        <option value="ebook">Ebook</option>
                    </select>
                </div>
                
                <div class="nymia-table-wrapper" style="margin-top: 20px;">
                    <table class="nymia-admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Reviewer</th>
                                <th>Rating</th>
                                <th>Review Text</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="nymia-empty-state">No reviews found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="nymia-card-actions" style="margin-top: 20px;">
                    <button class="nymia-btn nymia-btn-secondary">Bulk Approve Selected</button>
                    <button class="nymia-btn nymia-btn-secondary">Bulk Delete Selected</button>
                </div>
            </div>
        </div>
        
        <!-- Reporting System -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Reporting System</h2>
                <p class="nymia-card-subtitle">Handle reports on inappropriate products, comments, or profiles</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-tabs">
                    <button class="nymia-tab-btn active" data-tab="pending">Pending Reports</button>
                    <button class="nymia-tab-btn" data-tab="resolved">Resolved Reports</button>
                    <button class="nymia-tab-btn" data-tab="rejected">Rejected Reports</button>
                </div>
                
                <!-- Pending Reports -->
                <div class="nymia-tab-content active" id="tab-pending">
                    <div class="nymia-reports-list">
                        <div class="nymia-report-card">
                            <div class="nymia-report-header">
                                <div class="nymia-report-info">
                                    <strong class="nymia-report-type">🚩 Product Report</strong>
                                    <span class="nymia-report-date">2 hours ago</span>
                                </div>
                                <span class="nymia-badge nymia-badge-warning">Pending</span>
                            </div>
                            <div class="nymia-report-content">
                                <p><strong>Reported by:</strong> user@example.com</p>
                                <p><strong>Product:</strong> Sample Audio Track</p>
                                <p><strong>Reason:</strong> Inappropriate Content</p>
                                <p><strong>Description:</strong> The product contains content that violates community guidelines...</p>
                            </div>
                            <div class="nymia-report-actions">
                                <button class="nymia-btn nymia-btn-primary">Review Details</button>
                                <button class="nymia-btn nymia-btn-secondary">Warn User</button>
                                <button class="nymia-btn nymia-btn-secondary">Remove Content</button>
                                <button class="nymia-btn nymia-btn-danger">Suspend User</button>
                                <button class="nymia-btn nymia-btn-text">Reject Report</button>
                            </div>
                        </div>
                        
                        <div class="nymia-report-card">
                            <div class="nymia-report-header">
                                <div class="nymia-report-info">
                                    <strong class="nymia-report-type">💬 Comment Report</strong>
                                    <span class="nymia-report-date">5 hours ago</span>
                                </div>
                                <span class="nymia-badge nymia-badge-warning">Pending</span>
                            </div>
                            <div class="nymia-report-content">
                                <p><strong>Reported by:</strong> user2@example.com</p>
                                <p><strong>Comment:</strong> "This is spam..."</p>
                                <p><strong>Reason:</strong> Spam</p>
                            </div>
                            <div class="nymia-report-actions">
                                <button class="nymia-btn nymia-btn-primary">Review Details</button>
                                <button class="nymia-btn nymia-btn-secondary">Delete Comment</button>
                                <button class="nymia-btn nymia-btn-text">Reject Report</button>
                            </div>
                        </div>
                        
                        <div class="nymia-report-card">
                            <div class="nymia-report-header">
                                <div class="nymia-report-info">
                                    <strong class="nymia-report-type">👤 Profile Report</strong>
                                    <span class="nymia-report-date">1 day ago</span>
                                </div>
                                <span class="nymia-badge nymia-badge-warning">Pending</span>
                            </div>
                            <div class="nymia-report-content">
                                <p><strong>Reported by:</strong> user3@example.com</p>
                                <p><strong>User:</strong> creator@example.com</p>
                                <p><strong>Reason:</strong> Fake Profile</p>
                                <p><strong>Description:</strong> This profile appears to be impersonating another creator...</p>
                            </div>
                            <div class="nymia-report-actions">
                                <button class="nymia-btn nymia-btn-primary">Review Details</button>
                                <button class="nymia-btn nymia-btn-secondary">Suspend User</button>
                                <button class="nymia-btn nymia-btn-text">Reject Report</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resolved Reports -->
                <div class="nymia-tab-content" id="tab-resolved">
                    <div class="nymia-table-wrapper">
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Reported By</th>
                                    <th>Target</th>
                                    <th>Reason</th>
                                    <th>Action Taken</th>
                                    <th>Resolved Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="nymia-empty-state">No resolved reports</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Rejected Reports -->
                <div class="nymia-tab-content" id="tab-rejected">
                    <div class="nymia-table-wrapper">
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Reported By</th>
                                    <th>Target</th>
                                    <th>Reason</th>
                                    <th>Rejection Reason</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="nymia-empty-state">No rejected reports</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Audio Quality Tools -->
        <div class="nymia-admin-card">
            <div class="nymia-card-header">
                <h2 class="nymia-card-title">Audio Quality Tools</h2>
                <p class="nymia-card-subtitle">Check technical standards (format, bitrate, etc.) and flag low-quality files</p>
            </div>
            <div class="nymia-card-body">
                <div class="nymia-quality-settings">
                    <h3 class="nymia-subsection-title">Quality Standards</h3>
                    
                    <div class="nymia-form-group">
                        <label class="nymia-label">Minimum Bitrate (kbps)</label>
                        <input type="number" class="nymia-input" value="128" min="64" max="320">
                        <p class="nymia-help-text">Audio files below this bitrate will be flagged for review.</p>
                    </div>
                    
                    <div class="nymia-form-group">
                        <label class="nymia-label">Allowed Formats</label>
                        <div class="nymia-checkbox-group">
                            <label class="nymia-checkbox-label">
                                <input type="checkbox" class="nymia-checkbox" checked>
                                <span>MP3</span>
                            </label>
                            <label class="nymia-checkbox-label">
                                <input type="checkbox" class="nymia-checkbox" checked>
                                <span>WAV</span>
                            </label>
                            <label class="nymia-checkbox-label">
                                <input type="checkbox" class="nymia-checkbox" checked>
                                <span>FLAC</span>
                            </label>
                            <label class="nymia-checkbox-label">
                                <input type="checkbox" class="nymia-checkbox">
                                <span>AAC</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="nymia-form-group">
                        <label class="nymia-label">Maximum File Size (MB)</label>
                        <input type="number" class="nymia-input" value="50" min="1" max="500">
                        <p class="nymia-help-text">Files exceeding this size will be rejected.</p>
                    </div>
                    
                    <div class="nymia-form-actions">
                        <button class="nymia-btn nymia-btn-primary">Save Quality Settings</button>
                        <button class="nymia-btn nymia-btn-secondary">Run Quality Check on All Files</button>
                    </div>
                </div>
                
                <div class="nymia-quality-results" style="margin-top: 40px;">
                    <h3 class="nymia-subsection-title">Low-Quality Files</h3>
                    <div class="nymia-table-wrapper">
                        <table class="nymia-admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Creator</th>
                                    <th>Format</th>
                                    <th>Bitrate</th>
                                    <th>File Size</th>
                                    <th>Issue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="nymia-empty-state">No low-quality files detected</td>
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
            margin-right: 20px;
        }
        
        .nymia-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            accent-color: #C7541A;
            cursor: pointer;
        }
        
        .nymia-checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
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
        
        .nymia-card-actions {
            display: flex;
            gap: 10px;
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
        
        .nymia-btn-danger {
            background: #ff4444;
            color: #ffffff;
        }
        
        .nymia-btn-danger:hover {
            background: #ff6666;
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
        
        /* Reports */
        .nymia-reports-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .nymia-report-card {
            background: #1a1a1a;
            border: 1px solid rgba(199, 84, 26, 0.2);
            border-radius: 12px;
            padding: 20px;
        }
        
        .nymia-report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .nymia-report-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nymia-report-type {
            color: #ffffff;
            font-size: 16px;
        }
        
        .nymia-report-date {
            color: #888;
            font-size: 13px;
        }
        
        .nymia-report-content {
            margin-bottom: 15px;
        }
        
        .nymia-report-content p {
            color: #cccccc;
            font-size: 14px;
            margin: 8px 0;
        }
        
        .nymia-report-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Badges */
        .nymia-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .nymia-badge-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nymia-admin-dashboard-wrap {
                padding: 20px 0;
            }
            
            .nymia-filter-bar {
                flex-direction: column;
            }
            
            .nymia-report-actions {
                flex-direction: column;
            }
            
            .nymia-card-actions {
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
    </script>
    <?php
}

