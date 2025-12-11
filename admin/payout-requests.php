<?php
if (!defined('ABSPATH')) {
    exit;
}

function nymia_creator_payments_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    nymia_render_admin_settings_styles();

    $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $paged  = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
    $per_page = 20;
    $offset = ($paged - 1) * $per_page;

    $display_roles = apply_filters('nymia_creator_payment_roles', array('creator', 'administrator', 'author'));
    $user_query_args = array(
        'role__in'   => $display_roles,
        'number'     => $per_page,
        'offset'     => $offset,
        'orderby'    => 'registered',
        'order'      => 'DESC',
        'fields'     => array('ID', 'user_login', 'display_name', 'user_email', 'user_registered'),
    );

    if (!empty($search)) {
        $user_query_args['search'] = '*' . $search . '*';
        $user_query_args['search_columns'] = array('user_login', 'user_email', 'user_nicename', 'display_name');
    }

    $user_query = new WP_User_Query($user_query_args);
    $creators = $user_query->get_results();
    $total_creators = (int) $user_query->get_total();
    $total_pages = $total_creators > 0 ? ceil($total_creators / $per_page) : 1;

    $base_url = admin_url('admin.php?page=nymia-creator-payments');
    if (!empty($search)) {
        $base_url = add_query_arg('s', rawurlencode($search), $base_url);
    }
    ?>
    <div class="wrap nymia-stripe-wrap nymia-dashboard-overview nymia-payments-wrap">
        <header class="nymia-payments-header">
            <div class="nymia-payments-header-text">
                <p class="nymia-payments-eyebrow"><?php esc_html_e('Payments · Stripe', 'nymia'); ?></p>
                <h1><?php esc_html_e('Creator Payments', 'nymia'); ?></h1>
                <p><?php esc_html_e('Monitor every creator connected to Stripe. Search by name or email to quickly review their payout status.', 'nymia'); ?></p>
            </div>
            <form method="get" class="nymia-admin-search">
                <input type="hidden" name="page" value="nymia-creator-payments" />
                <div class="nymia-admin-search__wrap">
                    <div class="nymia-admin-search__input">
                        <span class="dashicons dashicons-search"></span>
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search creators…', 'nymia'); ?>" />
                    </div>
                    <button type="submit" class="nymia-cta-btn"><?php esc_html_e('Search', 'nymia'); ?></button>
                    <?php if (!empty($search)) : ?>
                        <a class="nymia-ghost-btn" href="<?php echo esc_url(admin_url('admin.php?page=nymia-creator-payments')); ?>"><?php esc_html_e('Reset', 'nymia'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </header>

        <div class="nymia-payments-card">
            <div class="nymia-payments-card-header">
                <h2><?php esc_html_e('Creators & Stripe Accounts', 'nymia'); ?></h2>
                <p><?php esc_html_e('Only creators with connected Stripe accounts are eligible for payouts. Keep an eye on onboarding status.', 'nymia'); ?></p>
            </div>
            <div class="nymia-table-wrap">
                <table class="nymia-payments-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Creator', 'nymia'); ?></th>
                            <th><?php esc_html_e('Email', 'nymia'); ?></th>
                            <th><?php esc_html_e('Stripe Account ID', 'nymia'); ?></th>
                            <th><?php esc_html_e('Status', 'nymia'); ?></th>
                            <th><?php esc_html_e('Last Updated', 'nymia'); ?></th>
                            <th><?php esc_html_e('Profile', 'nymia'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($creators)) : ?>
                            <?php foreach ($creators as $creator) :
                                $profile = function_exists('nymia_get_creator_stripe_profile') ? nymia_get_creator_stripe_profile($creator->ID) : array();
                                $account_id = !empty($profile['account_id']) ? $profile['account_id'] : get_user_meta($creator->ID, 'stripe_account_no', true);
                                $account_id = $account_id ? $account_id : __('Not connected', 'nymia');
                                $status = isset($profile['status']) ? sanitize_key($profile['status']) : 'not_connected';
                                $status_label = isset($profile['status_label']) ? $profile['status_label'] : __('Not Connected', 'nymia');
                                $status_class = 'is-' . sanitize_html_class($status);
                                $last_updated = !empty($profile['last_updated'])
                                    ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $profile['last_updated'])
                                    : __('Never', 'nymia');
                                $edit_link = get_edit_user_link($creator->ID);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($creator->display_name ?: $creator->user_login); ?></strong><br>
                                        <span class="nymia-meta-label"><?php printf(__('User ID: %d', 'nymia'), $creator->ID); ?></span>
                                    </td>
                                    <td><?php echo esc_html($creator->user_email); ?></td>
                                    <td><code><?php echo esc_html($account_id); ?></code></td>
                                    <td><span class="nymia-status-pill <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></td>
                                    <td><?php echo esc_html($last_updated); ?></td>
                                    <td>
                                        <a class="button button-small" href="<?php echo esc_url($edit_link); ?>">
                                            <?php esc_html_e('View', 'nymia'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">
                                    <div class="nymia-empty-state">
                                        <p><?php esc_html_e('No creators found. Try adjusting your search.', 'nymia'); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1) :
                $pagination = paginate_links(array(
                    'base'      => add_query_arg('paged', '%#%', $base_url),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'type'      => 'array',
                ));
                if (!empty($pagination)) :
            ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php foreach ($pagination as $link) : ?>
                            <?php echo wp_kses_post($link); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; endif; ?>
        </div>
    </div>
    <?php
}
