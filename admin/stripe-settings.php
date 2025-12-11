<?php
/**
 * Stripe Payments Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

function nymia_stripe_settings_page() {
    $mode               = get_option('nymia_stripe_mode', 'test');
    $publishable_key    = get_option('nymia_stripe_publishable_key', '');
    $secret_key         = get_option('nymia_stripe_secret_key', '');
    $webhook_secret     = get_option('nymia_stripe_webhook_secret', '');
    $success_url        = get_option('nymia_stripe_success_url', home_url('/checkout'));
    $cancel_url         = get_option('nymia_stripe_cancel_url', home_url('/checkout'));
    $connect_client_id  = get_option('nymia_stripe_connect_client_id', '');
    $currency_raw       = get_option('nymia_stripe_currency', 'usd');
    $currency           = strtoupper($currency_raw); // Display in uppercase
    $statement_prefix   = get_option('nymia_stripe_statement_descriptor', '');

    $is_configured = !empty($publishable_key) && !empty($secret_key);
    $is_live = $mode === 'live';
    nymia_render_admin_settings_styles();
    ?>
    <div class="wrap nymia-stripe-wrap">
        <div class="nymia-stripe-header">
            <div>
                <h1><?php esc_html_e('Stripe Payments', 'nymia'); ?></h1>
                <p><?php esc_html_e('Connect your marketplace to Stripe for secure payments, payouts, and subscriptions.', 'nymia'); ?></p>
            </div>
            <div class="nymia-stripe-actions">
                <a class="nymia-secondary-btn" href="https://dashboard.stripe.com/<?php echo $is_live ? '' : 'test/'; ?>developers" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Open Stripe Dashboard', 'nymia'); ?>
                </a>
            </div>
        </div>

        <nav class="nymia-dashboard-nav">
            <a href="?page=nymia-theme-settings"><?php esc_html_e('Dashboard Overview', 'nymia'); ?></a>
            <a href="?page=nymia-general-settings"><?php esc_html_e('General Settings', 'nymia'); ?></a>
            <a href="?page=nymia-social-login-settings"><?php esc_html_e('Social Login', 'nymia'); ?></a>
            <a href="?page=nymia-zegocloud-settings"><?php esc_html_e('ZEGO Cloud', 'nymia'); ?></a>
            <a class="active" href="?page=nymia-stripe-settings"><?php esc_html_e('Stripe Payments', 'nymia'); ?></a>
        </nav>

        <div class="nymia-grid nymia-stripe-grid">
            <section class="nymia-card nymia-stripe-status">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Connection Status', 'nymia'); ?></h2>
                    <span class="nymia-status-pill <?php echo $is_configured ? 'is-success' : 'is-warning'; ?>">
                        <?php echo $is_configured ? esc_html__('Connected', 'nymia') : esc_html__('Action Needed', 'nymia'); ?>
                    </span>
                </header>
                <p class="nymia-card-subtitle">
                    <?php
                    if ($is_configured) {
                        echo $is_live
                            ? esc_html__('Live payments are active. Funds will flow into your Stripe account.', 'nymia')
                            : esc_html__('Test mode enabled. Use Stripe test cards to simulate purchases.', 'nymia');
                    } else {
                        esc_html_e('Enter your Stripe API keys to enable payments across the marketplace.', 'nymia');
                    }
                    ?>
                </p>
                <div class="nymia-status-meta">
                    <div>
                        <span class="nymia-meta-label"><?php esc_html_e('Mode', 'nymia'); ?></span>
                        <span class="nymia-meta-value"><?php echo $is_live ? esc_html__('Live', 'nymia') : esc_html__('Test', 'nymia'); ?></span>
                    </div>
                    <div>
                        <span class="nymia-meta-label"><?php esc_html_e('Publishable Key', 'nymia'); ?></span>
                        <span class="nymia-meta-value"><?php echo $publishable_key ? esc_html(substr($publishable_key, 0, 12) . '••••') : esc_html__('Not set', 'nymia'); ?></span>
                    </div>
                    <div>
                        <span class="nymia-meta-label"><?php esc_html_e('Currency', 'nymia'); ?></span>
                        <span class="nymia-meta-value"><?php echo esc_html(strtoupper($currency)); ?></span>
                    </div>
                </div>
            </section>

            <section class="nymia-card nymia-stripe-form-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('API Credentials', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Copy your keys from Stripe Dashboard → Developers → API Keys.', 'nymia'); ?></p>
                </header>

                <form method="post" action="options.php" class="nymia-stripe-form">
                    <?php
                        settings_fields('nymia_stripe_settings');
                        do_settings_sections('nymia_stripe_settings');
                    ?>

                    <div class="nymia-form-grid">
                        <div class="nymia-form-field">
                            <label for="nymia_stripe_mode"><?php esc_html_e('Environment', 'nymia'); ?></label>
                            <select id="nymia_stripe_mode" name="nymia_stripe_mode">
                                <option value="test" <?php selected($mode, 'test'); ?>><?php esc_html_e('Test Mode (Sandbox)', 'nymia'); ?></option>
                                <option value="live" <?php selected($mode, 'live'); ?>><?php esc_html_e('Live Mode (Production)', 'nymia'); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e('Switch to Live once you are ready to accept real payments.', 'nymia'); ?></p>
                        </div>

                        <div class="nymia-form-field">
                            <label for="nymia_stripe_currency"><?php esc_html_e('Default Currency', 'nymia'); ?></label>
                            <input type="text" id="nymia_stripe_currency" name="nymia_stripe_currency" value="<?php echo esc_attr($currency); ?>" maxlength="3" placeholder="USD" pattern="[A-Z]{3}" style="text-transform: uppercase;">
                            <p class="description"><?php esc_html_e('3-letter ISO code, e.g. USD, EUR, GBP. Enter in uppercase.', 'nymia'); ?></p>
                        </div>
                    </div>

                    <div class="nymia-form-field">
                        <label for="nymia_stripe_publishable_key"><?php esc_html_e('Publishable Key', 'nymia'); ?></label>
                        <input type="text" id="nymia_stripe_publishable_key" name="nymia_stripe_publishable_key" value="<?php echo esc_attr($publishable_key); ?>" placeholder="pk_test_XXXX">
                    </div>

                    <div class="nymia-form-field">
                        <label for="nymia_stripe_secret_key"><?php esc_html_e('Secret Key', 'nymia'); ?></label>
                        <input type="password" id="nymia_stripe_secret_key" name="nymia_stripe_secret_key" value="<?php echo esc_attr($secret_key); ?>" placeholder="sk_test_XXXX">
                    </div>

                    <div class="nymia-form-field">
                        <label for="nymia_stripe_webhook_secret"><?php esc_html_e('Webhook Signing Secret', 'nymia'); ?></label>
                        <input type="text" id="nymia_stripe_webhook_secret" name="nymia_stripe_webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" placeholder="whsec_XXXX">
                        <p class="description"><?php esc_html_e('Generated when you create a webhook endpoint in Stripe.', 'nymia'); ?></p>
                    </div>

                    <div class="nymia-form-grid">
                        <div class="nymia-form-field">
                            <label for="nymia_stripe_success_url"><?php esc_html_e('Success URL', 'nymia'); ?></label>
                            <input type="url" id="nymia_stripe_success_url" name="nymia_stripe_success_url" value="<?php echo esc_url($success_url); ?>" placeholder="https://example.com/checkout/success">
                        </div>
                        <div class="nymia-form-field">
                            <label for="nymia_stripe_cancel_url"><?php esc_html_e('Cancel URL', 'nymia'); ?></label>
                            <input type="url" id="nymia_stripe_cancel_url" name="nymia_stripe_cancel_url" value="<?php echo esc_url($cancel_url); ?>" placeholder="https://example.com/checkout/cancelled">
                        </div>
                    </div>

                    <div class="nymia-form-field">
                        <label for="nymia_stripe_connect_client_id"><?php esc_html_e('Stripe Connect Client ID (optional)', 'nymia'); ?></label>
                        <input type="text" id="nymia_stripe_connect_client_id" name="nymia_stripe_connect_client_id" value="<?php echo esc_attr($connect_client_id); ?>" placeholder="ca_XXXX">
                        <p class="description"><?php esc_html_e('Required if creators receive payouts directly (Stripe Connect).', 'nymia'); ?></p>
                    </div>

                    <div class="nymia-form-field">
                        <label for="nymia_stripe_statement_descriptor"><?php esc_html_e('Statement Descriptor (optional)', 'nymia'); ?></label>
                        <input type="text" id="nymia_stripe_statement_descriptor" name="nymia_stripe_statement_descriptor" value="<?php echo esc_attr($statement_prefix); ?>" maxlength="22" placeholder="NYMIA*CREATOR AUDIO">
                        <p class="description"><?php esc_html_e('Appears on customer bank statements. Up to 22 characters, capital letters.', 'nymia'); ?></p>
                    </div>

                    <?php submit_button(__('Save Stripe Settings', 'nymia'), 'primary', 'submit', false, array('class' => 'nymia-primary-btn')); ?>
                </form>
            </section>

            <section class="nymia-card nymia-stripe-guide">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Getting Started Checklist', 'nymia'); ?></h2>
                </header>
                <ol class="nymia-guide-list">
                    <li><?php esc_html_e('Create a Stripe account and activate it for live payments.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Generate API keys in Stripe Dashboard → Developers → API Keys.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Create a webhook endpoint (e.g. /wp-json/nymia/v1/stripe/webhook) and copy the signing secret.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Set the success and cancel URLs to match your checkout flow.', 'nymia'); ?></li>
                    <li><?php esc_html_e('Switch to Live mode and test a real transaction when ready.', 'nymia'); ?></li>
                </ol>
                <p class="nymia-card-subtitle"><?php esc_html_e('Need help integrating Stripe in the frontend? Visit the developer documentation for code samples.', 'nymia'); ?></p>
                <a class="nymia-link-btn" href="https://stripe.com/docs" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Stripe Documentation', 'nymia'); ?>
                </a>
            </section>

            <section class="nymia-card nymia-stripe-webhook">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Webhook Events to Enable', 'nymia'); ?></h2>
                </header>
                <ul class="nymia-pill-list">
                    <li>checkout.session.completed</li>
                    <li>payment_intent.succeeded</li>
                    <li>payment_intent.payment_failed</li>
                    <li>invoice.payment_succeeded</li>
                    <li>invoice.payment_failed</li>
                    <li>account.updated</li>
                    <li>payout.paid</li>
                </ul>
                <p class="description"><?php esc_html_e('Enable these events when setting up the webhook endpoint inside Stripe so the platform can react to payments, subscriptions, and payouts.', 'nymia'); ?></p>
            </section>
        </div>
    </div>
    <?php
}


