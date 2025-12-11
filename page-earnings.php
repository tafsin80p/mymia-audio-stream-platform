<?php
/**
 * Template Name: Earnings
 * Description: Earnings dashboard with revenue overview and transaction history
 */

get_header();

$current_user_id = get_current_user_id();
$earnings_currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
$now_gmt = current_time('timestamp', true);

$lifetime_totals = nymia_collect_creator_charges($current_user_id);
$monthly_totals = nymia_collect_creator_charges($current_user_id, $now_gmt - 30 * DAY_IN_SECONDS, $now_gmt);
$recent_totals = nymia_collect_creator_charges($current_user_id, $now_gmt - 7 * DAY_IN_SECONDS, $now_gmt);

$total_amount = isset($lifetime_totals['amount']) ? (float) $lifetime_totals['amount'] : 0.0;
$monthly_amount = isset($monthly_totals['amount']) ? (float) $monthly_totals['amount'] : 0.0;
$pending_amount = isset($recent_totals['amount']) ? (float) $recent_totals['amount'] : 0.0;
$pending_amount = min($pending_amount, $total_amount);

$creator_share_percent = nymia_get_creator_share_percentage();
$creator_summary = nymia_get_creator_payout_summary($current_user_id, array(
    'lifetime'       => $lifetime_totals,
    'pending_window' => $recent_totals,
));
$monthly_share = isset($monthly_totals['share_amount']) ? (float) $monthly_totals['share_amount'] : nymia_calculate_creator_share($monthly_amount);

$total_earnings = nymia_format_currency_for_display($creator_summary['total_share'], $earnings_currency);
$this_month = nymia_format_currency_for_display($monthly_share, $earnings_currency);
$pending = nymia_format_currency_for_display($creator_summary['pending_window'], $earnings_currency);
$available = nymia_format_currency_for_display($creator_summary['available'], $earnings_currency);
$pending_requests_display = nymia_format_currency_for_display($creator_summary['pending_requests'], $earnings_currency);
$paid_total_display = nymia_format_currency_for_display($creator_summary['paid_total'], $earnings_currency);

$default_chart_period = 30;
$chart_data = nymia_get_creator_chart_data($current_user_id, $default_chart_period);
$currency_symbol_hint = preg_replace('/[0-9\.,\s]/', '', nymia_format_currency_for_display(0, $earnings_currency));
$chart_payload = array(
    'labels'         => $chart_data['labels'],
    'amounts'        => $chart_data['amounts'],
    'currency'       => isset($chart_data['currency']) ? $chart_data['currency'] : $earnings_currency,
    'currencySymbol' => $currency_symbol_hint ? $currency_symbol_hint : '$',
    'ajaxUrl'        => admin_url('admin-ajax.php'),
    'nonce'          => wp_create_nonce('nymia_creator_earnings_chart'),
    'defaultPeriod'  => $default_chart_period,
);
$transactions = nymia_get_creator_transactions($current_user_id, 10);
$breakdown_data = nymia_get_creator_breakdown($current_user_id);
$breakdown_segments = isset($breakdown_data['segments']) ? $breakdown_data['segments'] : array();

$stripe_profile = function_exists('nymia_get_creator_stripe_profile') ? nymia_get_creator_stripe_profile($current_user_id) : array();
$stripe_profile = wp_parse_args($stripe_profile, array(
    'email'              => '',
    'account_id'         => '',
    'status'             => 'not_connected',
    'status_label'       => __('Not Connected', 'nymia'),
    'status_description' => __('Add your Stripe details to start receiving payouts.', 'nymia'),
    'last_updated'       => 0,
));
$stripe_status_class = 'is-' . str_replace('_', '-', $stripe_profile['status']);
$stripe_last_updated = !empty($stripe_profile['last_updated'])
    ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $stripe_profile['last_updated'])
    : __('Not updated yet', 'nymia');
$stripe_nonce = wp_create_nonce('nymia_save_stripe_payout');
$stripe_ajax_url = admin_url('admin-ajax.php');
$payout_history = nymia_prepare_payout_history_payload($current_user_id);
$payout_config = array(
    'ajaxUrl'          => admin_url('admin-ajax.php'),
    'nonce'            => wp_create_nonce('nymia_request_payout'),
    'currency'         => $earnings_currency,
    'minAmount'        => (float) apply_filters('nymia_minimum_payout_amount', 10.0),
    'sharePercent'     => $creator_share_percent,
    'summary'          => $creator_summary,
    'history'          => $payout_history,
    'stripeProfile'    => $stripe_profile,
    'onboardingNonce'  => wp_create_nonce('nymia_start_stripe_onboarding'),
);
?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-earnings-container">
            <!-- Earnings Header -->
            <div class="nymia-earnings-header">
                <div>
                    <h1><?php esc_html_e('Earnings Dashboard', 'nymia'); ?></h1>
                    <p class="nymia-earnings-subtitle"><?php esc_html_e('Track your revenue and manage payouts', 'nymia'); ?></p>
                    <p class="nymia-earnings-note"><?php printf(esc_html__('All amounts include your %s%% share after the platform commission.', 'nymia'), esc_html($creator_share_percent)); ?></p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="nymia-earnings-stats">
                <div class="nymia-stat-card">
                    <div class="nymia-stat-card-icon total">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="nymia-stat-card-content">
                        <p class="nymia-stat-label"><?php esc_html_e('Total Earnings', 'nymia'); ?></p>
                        <h3 class="nymia-stat-value" id="nymia-total-earnings" data-amount="<?php echo esc_attr($creator_summary['total_share']); ?>"><?php echo esc_html($total_earnings); ?></h3>
                        <span class="nymia-stat-change positive">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                            +12.5%
                        </span>
                    </div>
                </div>

                <div class="nymia-stat-card">
                    <div class="nymia-stat-card-icon month">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="nymia-stat-card-content">
                        <p class="nymia-stat-label"><?php esc_html_e('This Month', 'nymia'); ?></p>
                        <h3 class="nymia-stat-value" id="nymia-monthly-earnings" data-amount="<?php echo esc_attr($monthly_share); ?>"><?php echo esc_html($this_month); ?></h3>
                        <span class="nymia-stat-change positive">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                            +8.2%
                        </span>
                    </div>
                </div>

                <div class="nymia-stat-card">
                    <div class="nymia-stat-card-icon pending">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="nymia-stat-card-content">
                        <p class="nymia-stat-label"><?php esc_html_e('Pending', 'nymia'); ?></p>
                        <h3 class="nymia-stat-value" id="nymia-pending-window" data-amount="<?php echo esc_attr($creator_summary['pending_window']); ?>"><?php echo esc_html($pending); ?></h3>
                        <span class="nymia-stat-info"><?php esc_html_e('Processing', 'nymia'); ?></span>
                    </div>
                </div>

                <div class="nymia-stat-card">
                    <div class="nymia-stat-card-icon available">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                    </div>
                    <div class="nymia-stat-card-content">
                        <p class="nymia-stat-label"><?php esc_html_e('Available', 'nymia'); ?></p>
                        <h3 class="nymia-stat-value" id="nymia-available-earnings" data-amount="<?php echo esc_attr($creator_summary['available']); ?>"><?php echo esc_html($available); ?></h3>
                        <span class="nymia-stat-info"><?php esc_html_e('Ready to withdraw', 'nymia'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Chart and Breakdown -->
            <div class="nymia-earnings-grid">
                <!-- Earnings Chart -->
                <div class="nymia-earnings-card">
                    <div class="nymia-earnings-card-header">
                        <h2><?php esc_html_e('Revenue Overview', 'nymia'); ?></h2>
                        <select class="nymia-period-select">
                            <option value="7"><?php esc_html_e('Last 7 days', 'nymia'); ?></option>
                            <option value="30" selected><?php esc_html_e('Last 30 days', 'nymia'); ?></option>
                            <option value="90"><?php esc_html_e('Last 3 months', 'nymia'); ?></option>
                            <option value="365"><?php esc_html_e('Last year', 'nymia'); ?></option>
                        </select>
                    </div>
                    <div class="nymia-chart-container">
                        <canvas id="earningsChart"></canvas>
                    </div>
                </div>

                <!-- Earnings Breakdown -->
                <div class="nymia-earnings-card">
                    <div class="nymia-earnings-card-header">
                        <h2><?php esc_html_e('Earnings Breakdown', 'nymia'); ?></h2>
                        <p style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6); margin-top: 4px;">
                            <?php esc_html_e('Shows items sold, prices, and one-to-one session minutes', 'nymia'); ?>
                        </p>
                    </div>
                    <div class="nymia-breakdown-list">
                        <?php if (!empty($breakdown_segments)) : ?>
                            <?php foreach ($breakdown_segments as $segment) : ?>
                                <div class="nymia-breakdown-item">
                                    <div class="nymia-breakdown-icon <?php echo esc_attr($segment['icon']); ?>">
                                        <?php
                                        switch ($segment['icon']) :
                                            case 'live':
                                                ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                                </svg>
                                                <?php
                                                break;
                                            case 'tips':
                                                ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                                </svg>
                                                <?php
                                                break;
                                            case 'ebook':
                                                ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                                    <path d="M4 4.5A2.5 2.5 0 0 1 6.5 7H20"></path>
                                                    <path d="M6.5 7v10"></path>
                                                    <path d="M20 22V2"></path>
                                                </svg>
                                                <?php
                                                break;
                                            case 'payout':
                                                ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 22h14"></path>
                                                    <path d="M5 2h14"></path>
                                                    <path d="M5 6h14"></path>
                                                    <path d="M5 18h14"></path>
                                                    <path d="M5 10h14"></path>
                                                    <path d="M5 14h14"></path>
                                                </svg>
                                                <?php
                                                break;
                                            case 'audio':
                                            default:
                                                ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                                </svg>
                                                <?php
                                                break;
                                        endswitch;
                                        ?>
                                    </div>
                                    <div class="nymia-breakdown-content">
                                        <div class="nymia-breakdown-top">
                                            <span class="nymia-breakdown-label"><?php echo esc_html($segment['label']); ?></span>
                                            <span class="nymia-breakdown-value"><?php echo esc_html($segment['amount_display']); ?></span>
                                        </div>
                                        <?php if (!empty($segment['count_label'])) : ?>
                                            <span class="nymia-breakdown-sub">
                                                <?php echo esc_html($segment['count_label']); ?>
                                                <?php if (!empty($segment['details_label'])) : ?>
                                                    <span style="color: rgba(255, 255, 255, 0.5); margin-left: 8px;">• <?php echo esc_html($segment['details_label']); ?></span>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="nymia-breakdown-progress" role="presentation">
                                            <span class="nymia-breakdown-progress-fill" style="width: <?php echo esc_attr(min(100, max(0, $segment['percent']))); ?>%;"></span>
                                        </div>
                                    </div>
                                    <div class="nymia-breakdown-percent">
                                        <strong><?php echo esc_html($segment['percent']); ?>%</strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="nymia-empty-state">
                                <p><?php esc_html_e('No earnings recorded yet. Once you make sales, you’ll see how they break down here.', 'nymia'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="nymia-earnings-card">
                <div class="nymia-earnings-card-header">
                    <h2><?php esc_html_e('Recent Transactions', 'nymia'); ?></h2>
                    <button type="button" class="nymia-btn-outline-small" id="nymia-view-all-transactions" data-transactions-nonce="<?php echo esc_attr(wp_create_nonce('nymia_creator_transactions')); ?>">
                        <?php esc_html_e('View All', 'nymia'); ?>
                    </button>
                </div>
                <div class="nymia-transactions-table">
                    <table>
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Date', 'nymia'); ?></th>
                                <th><?php esc_html_e('Description', 'nymia'); ?></th>
                                <th><?php esc_html_e('Type', 'nymia'); ?></th>
                                <th><?php esc_html_e('Status', 'nymia'); ?></th>
                                <th class="text-right"><?php esc_html_e('Amount', 'nymia'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transactions)) : ?>
                                <?php foreach ($transactions as $transaction) : ?>
                                    <tr>
                                        <td>
                                            <span class="nymia-date" title="<?php echo esc_attr($transaction['datetime']); ?>">
                                                <?php echo esc_html($transaction['date_display']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($transaction['description']); ?></td>
                                        <td>
                                            <span class="nymia-badge <?php echo esc_attr($transaction['type_slug']); ?>">
                                                <?php echo esc_html($transaction['type_label']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="nymia-status <?php echo esc_attr($transaction['status_slug']); ?>">
                                                <?php echo esc_html($transaction['status_label']); ?>
                                            </span>
                                        </td>
                                        <td class="text-right amount-<?php echo esc_attr($transaction['amount_sign']); ?>">
                                            <?php echo $transaction['amount_sign'] === 'positive' ? '+' : '-'; ?>
                                            <?php echo esc_html($transaction['amount_display']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="nymia-empty-state">
                                            <p><?php esc_html_e('No transactions found yet. Complete a sale to see it here.', 'nymia'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bank Account -->
            <div class="nymia-earnings-card">
                <div class="nymia-earnings-card-header">
                    <h2><?php esc_html_e('Bank Account', 'nymia'); ?></h2>
                </div>
                <?php
                $bank_name = get_user_meta($current_user_id, 'nymia_bank_name', true);
                $account_holder_name = get_user_meta($current_user_id, 'nymia_bank_account_holder_name', true);
                $account_number = get_user_meta($current_user_id, 'nymia_bank_account_number', true);
                $routing_number = get_user_meta($current_user_id, 'nymia_bank_routing_number', true);
                $swift_bic = get_user_meta($current_user_id, 'nymia_bank_swift_bic', true);
                $account_type = get_user_meta($current_user_id, 'nymia_bank_account_type', true);
                $bank_country = get_user_meta($current_user_id, 'nymia_bank_country', true);
                $bank_account_updated = get_user_meta($current_user_id, 'nymia_bank_account_updated', true);
                $has_bank_account = !empty($bank_name) && !empty($account_holder_name) && !empty($account_number);
                $bank_account_nonce = wp_create_nonce('nymia_save_bank_account');
                ?>
                
                <?php if ($has_bank_account): ?>
                <div id="nymia-saved-bank-account" class="nymia-saved-bank-account" style="background-color: var(--input); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px; color: #4CAF50;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <div>
                            <strong style="color: var(--foreground); font-size: 1rem;"><?php echo esc_html($bank_name); ?></strong>
                            <span style="display: block; font-size: 0.875rem; color: var(--muted-foreground); margin-top: 2px;">
                                <?php echo esc_html(substr($account_number, 0, 4) . str_repeat('*', max(0, strlen($account_number) - 8)) . substr($account_number, -4)); ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($bank_account_updated): ?>
                        <span style="font-size: 0.75rem; color: var(--muted-foreground);">
                            <?php echo esc_html(date_i18n(get_option('date_format'), $bank_account_updated)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <form id="nymia-bank-account-form" class="nymia-stripe-form" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr($bank_account_nonce); ?>">
                    <div class="nymia-stripe-form-grid">
                        <div class="nymia-form-group">
                            <label for="nymia-bank-name"><?php esc_html_e('Bank Name', 'nymia'); ?> <span style="color: #C7541A;">*</span></label>
                            <input type="text" id="nymia-bank-name" name="bank_name" placeholder="<?php esc_attr_e('Enter bank name', 'nymia'); ?>" value="<?php echo esc_attr($bank_name); ?>" required>
                        </div>
                        <div class="nymia-form-group">
                            <label for="nymia-account-holder-name"><?php esc_html_e('Account Holder Name', 'nymia'); ?> <span style="color: #C7541A;">*</span></label>
                            <input type="text" id="nymia-account-holder-name" name="account_holder_name" placeholder="<?php esc_attr_e('Enter account holder name', 'nymia'); ?>" value="<?php echo esc_attr($account_holder_name); ?>" required>
                        </div>
                        <div class="nymia-form-group">
                            <label for="nymia-account-number"><?php esc_html_e('Account Number', 'nymia'); ?> <span style="color: #C7541A;">*</span></label>
                            <input type="text" id="nymia-account-number" name="account_number" placeholder="<?php esc_attr_e('Enter account number', 'nymia'); ?>" value="<?php echo esc_attr($account_number); ?>" required>
                        </div>
                        <div class="nymia-form-group">
                            <label for="nymia-routing-number"><?php esc_html_e('Routing Number / Sort Code', 'nymia'); ?></label>
                            <input type="text" id="nymia-routing-number" name="routing_number" placeholder="<?php esc_attr_e('Enter routing number', 'nymia'); ?>" value="<?php echo esc_attr($routing_number); ?>">
                        </div>
                        <div class="nymia-form-group">
                            <label for="nymia-swift-bic"><?php esc_html_e('SWIFT / BIC Code', 'nymia'); ?></label>
                            <input type="text" id="nymia-swift-bic" name="swift_bic" placeholder="<?php esc_attr_e('Enter SWIFT/BIC code', 'nymia'); ?>" value="<?php echo esc_attr($swift_bic); ?>">
                        </div>
                        <div class="nymia-form-group">
                            <label for="nymia-account-type"><?php esc_html_e('Account Type', 'nymia'); ?></label>
                            <select id="nymia-account-type" name="account_type" class="nymia-select">
                                <option value=""><?php esc_html_e('Select account type', 'nymia'); ?></option>
                                <option value="checking" <?php selected($account_type, 'checking'); ?>><?php esc_html_e('Checking', 'nymia'); ?></option>
                                <option value="savings" <?php selected($account_type, 'savings'); ?>><?php esc_html_e('Savings', 'nymia'); ?></option>
                            </select>
                        </div>
                        <div class="nymia-form-group">
                            <label for="nymia-bank-country"><?php esc_html_e('Country', 'nymia'); ?></label>
                            <input type="text" id="nymia-bank-country" name="country" placeholder="<?php esc_attr_e('Enter country', 'nymia'); ?>" value="<?php echo esc_attr($bank_country); ?>">
                        </div>
                    </div>
                    <div class="nymia-stripe-actions">
                        <button type="submit" class="nymia-btn-outline-small"><?php esc_html_e('Save Bank Account', 'nymia'); ?></button>
                        <div id="nymia-bank-account-message" class="nymia-stripe-message" role="status" aria-live="polite"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Simple Chart using Chart.js -->
<script>
window.nymiaPayoutConfig = <?php echo wp_json_encode($payout_config); ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const nymiaEarningsChartConfig = <?php echo wp_json_encode($chart_payload); ?>;
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let earningsChartInstance = null;
    const chartConfig = typeof nymiaEarningsChartConfig !== 'undefined' ? nymiaEarningsChartConfig : null;
    // Earnings Chart
    const ctx = document.getElementById('earningsChart');
    if (ctx && chartConfig) {
        const formatCurrency = (value) => {
            const symbol = chartConfig.currencySymbol || '$';
            return `${symbol}${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        };

        earningsChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartConfig.labels || [],
                datasets: [{
                    label: '<?php echo esc_js(__('Earnings', 'nymia')); ?>',
                    data: chartConfig.amounts || [],
                    borderColor: '#C7541A',
                    backgroundColor: 'rgba(199, 84, 26, 0.12)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#C7541A',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.parsed.y || 0);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#999',
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#999'
                    }
                }
            }
        }
        });

        const periodSelect = document.querySelector('.nymia-period-select');
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                const period = parseInt(this.value, 10) || chartConfig.defaultPeriod || 30;
                const formData = new FormData();
                formData.append('action', 'nymia_get_creator_earnings_chart');
                formData.append('nonce', chartConfig.nonce);
                formData.append('period', period);

                fetch(chartConfig.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success && data.data) {
                        const payload = data.data;
                        earningsChartInstance.data.labels = payload.labels || [];
                        earningsChartInstance.data.datasets[0].data = payload.amounts || [];
                        if (payload.currency && payload.currency !== chartConfig.currency) {
                            chartConfig.currency = payload.currency;
                        }
                        if (payload.currencySymbol) {
                            chartConfig.currencySymbol = payload.currencySymbol;
                        }
                        earningsChartInstance.update();
                    }
                })
                .catch(() => {
                    console.warn('Unable to fetch earnings data.');
                });
            });
        }
    }

    const payoutConfig = typeof window.nymiaPayoutConfig !== 'undefined' ? window.nymiaPayoutConfig : null;
    if (payoutConfig && typeof payoutConfig.stripeProfile === 'undefined') {
        payoutConfig.stripeProfile = {};
    }
    const onboardingAjaxUrl = (payoutConfig && payoutConfig.ajaxUrl) ? payoutConfig.ajaxUrl : '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
    let onboardingInProgress = false;
    const stripeStatusPill = document.getElementById('nymia-stripe-status-pill');
    const stripeStatusDescription = document.getElementById('nymia-stripe-status-description');
    const stripeEmailDisplay = document.getElementById('nymia-stripe-email-display');
    const stripeAccountDisplay = document.getElementById('nymia-stripe-account-display');
    const stripeUpdatedDisplay = document.getElementById('nymia-stripe-last-updated');
    // Bank Account Form Handler
    const bankAccountForm = document.getElementById('nymia-bank-account-form');
    const bankAccountMessage = document.getElementById('nymia-bank-account-message');
    const notSetText = '<?php echo esc_js(__('Not set', 'nymia')); ?>';
    const notUpdatedText = '<?php echo esc_js(__('Not updated yet', 'nymia')); ?>';
    const payoutForm = null;
    const payoutAmountInput = null;
    const payoutNoteInput = null;
    const payoutMessageEl = null;
    const payoutHistoryEl = null;
    let payoutHistoryEmpty = null;
    const payoutAvailableEl = null;
    const payoutPendingEl = null;
    const payoutPaidEl = null;
    const totalEarningsEl = document.getElementById('nymia-total-earnings');
    const pendingWindowEl = document.getElementById('nymia-pending-window');
    const availableEarningsEl = document.getElementById('nymia-available-earnings');
    const refreshPayoutBtn = document.getElementById('nymia-refresh-payouts');

    const payoutFormatter = (value) => {
        const amount = Number(value || 0);
        try {
            return new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency: (payoutConfig && payoutConfig.currency) || 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(amount);
        } catch (err) {
            return ((payoutConfig && payoutConfig.currency) || '$') + amount.toFixed(2);
        }
    };

    function updateAmountElement(element, value) {
        if (!element) {
            return;
        }
        element.dataset.amount = value;
        element.textContent = payoutFormatter(value);
    }

    function isStripeConnected() {
        if (!payoutConfig || !payoutConfig.stripeProfile) {
            return false;
        }
        return payoutConfig.stripeProfile.status === 'connected';
    }


    function refreshPayoutHistory() {}

    // Add Payout Method Button
    const addPayoutBtn = document.getElementById('addPayoutMethod');
    if (addPayoutBtn) {
        addPayoutBtn.addEventListener('click', function() {
            alert('Add payout method functionality!\n\nThis would open a form to add a new bank account or payment method.');
        });
    }

    if (bankAccountForm) {
        bankAccountForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const ajaxUrl = bankAccountForm.dataset.ajaxUrl || '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
            const nonceField = bankAccountForm.querySelector('input[name="nonce"]');
            const formData = new FormData(bankAccountForm);
            formData.append('action', 'nymia_save_bank_account');
            formData.append('nonce', nonceField ? nonceField.value : '');

            const submitBtn = bankAccountForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.dataset.originalText || submitBtn.textContent;
                submitBtn.textContent = '<?php echo esc_js(__('Saving…', 'nymia')); ?>';
            }
            if (bankAccountMessage) {
                bankAccountMessage.textContent = '';
                bankAccountMessage.classList.remove('is-success', 'is-error');
            }

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (!data || !data.success) {
                    const errorMessage = (data && data.data && data.data.message) || (data && data.message) || '';
                    throw new Error(errorMessage || '<?php echo esc_js(__('Unable to save bank account details.', 'nymia')); ?>');
                }
                if (bankAccountMessage) {
                    const successMessage = (data.data && data.data.message) ? data.data.message : '<?php echo esc_js(__('Bank account details saved successfully.', 'nymia')); ?>';
                    bankAccountMessage.textContent = successMessage;
                    bankAccountMessage.classList.remove('is-error');
                    bankAccountMessage.classList.add('is-success');
                }
                // Reload page to show saved account details
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                if (bankAccountMessage) {
                    bankAccountMessage.textContent = (error && error.message) ? error.message : '<?php echo esc_js(__('Unable to save bank account details.', 'nymia')); ?>';
                    bankAccountMessage.classList.remove('is-success');
                    bankAccountMessage.classList.add('is-error');
                }
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.dataset.originalText || '<?php echo esc_js(__('Save Bank Account', 'nymia')); ?>';
                }
            });
        });
    }

    const viewAllBtn = null;

    const url = new URL(window.location.href);
    if (url.searchParams.has('stripe_onboard')) {
        url.searchParams.delete('stripe_onboard');
        const cleanedUrl = url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : '') + url.hash;
        window.history.replaceState({}, '', cleanedUrl);
    }
});
</script>

<?php get_footer(); ?>

