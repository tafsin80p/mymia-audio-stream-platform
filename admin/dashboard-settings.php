<?php
/**
 * Dashboard Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('nymia_collect_stripe_charges_period')) {
    function nymia_collect_stripe_charges_period($secret_key, $start = null, $end = null, $args = array()) {
        $defaults = array(
            'max_iterations' => 20,
            'limit'          => 100,
            'status'         => 'succeeded',
        );
        $args = wp_parse_args($args, $defaults);

        if (!$secret_key) {
            return array(
                'count'    => 0,
                'amount'   => 0.0,
                'currency' => '',
            );
        }

        $params = array(
            'limit'  => (int) $args['limit'],
        );

        if (!empty($args['status'])) {
            $params['status'] = $args['status'];
        }

        if ($start !== null) {
            $params['created[gte]'] = max(0, (int) $start);
        }

        if ($end !== null) {
            $params['created[lt]'] = max(0, (int) $end);
        }

        $results = array(
            'count'    => 0,
            'amount'   => 0.0,
            'currency' => '',
        );

        $default_currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
        $iterations = 0;
        $starting_after = null;
        $has_more = false;

        do {
            $iterations++;

            $request_params = $params;
            if ($starting_after) {
                $request_params['starting_after'] = $starting_after;
            }

            $endpoint = add_query_arg($request_params, 'https://api.stripe.com/v1/charges');
            $response = wp_remote_get($endpoint, array(
                'timeout' => 20,
                'headers' => array(
                    'Authorization'   => 'Bearer ' . $secret_key,
                    'Stripe-Version'  => '2023-10-16',
                ),
            ));

            if (is_wp_error($response)) {
                break;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code >= 400) {
                break;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (!is_array($body) || empty($body['data']) || !is_array($body['data'])) {
                break;
            }

            foreach ($body['data'] as $charge) {
                if (!is_array($charge)) {
                    continue;
                }

                $currency = isset($charge['currency']) ? strtoupper($charge['currency']) : '';
                if ($results['currency'] === '') {
                    $results['currency'] = $currency ? $currency : $default_currency;
                }

                if ($currency && $results['currency'] && $currency !== $results['currency']) {
                    continue;
                }

                $amount_captured = isset($charge['amount_captured']) ? (int) $charge['amount_captured'] : (isset($charge['amount']) ? (int) $charge['amount'] : 0);
                $amount_refunded = isset($charge['amount_refunded']) ? (int) $charge['amount_refunded'] : 0;
                $net_amount = ($amount_captured - $amount_refunded) / 100;

                if ($net_amount <= 0) {
                    continue;
                }

                $results['count']++;
                $results['amount'] += $net_amount;
            }

            $has_more = !empty($body['has_more']);
            if ($has_more) {
                $last_item = end($body['data']);
                $starting_after = isset($last_item['id']) ? $last_item['id'] : null;
            }
        } while ($has_more && $iterations < (int) $args['max_iterations']);

        if ($results['currency'] === '') {
            $results['currency'] = $default_currency;
        }

        return $results;
    }
}

if (!function_exists('nymia_get_stripe_dashboard_totals')) {
    function nymia_get_stripe_dashboard_totals($commission_rate = 0.2) {
        $commission_rate = max(0.0, (float) $commission_rate);
        $secret_key = get_option('nymia_stripe_secret_key', '');

        if (empty($secret_key)) {
            return array(
                'success'   => false,
                'currency'  => strtoupper(get_option('nymia_stripe_currency', 'USD')),
                'lifetime'  => array('count' => 0, 'amount' => 0.0),
                'current'   => array('count' => 0, 'amount' => 0.0),
                'previous'  => array('count' => 0, 'amount' => 0.0),
            );
        }

        $cache_key = 'nymia_stripe_totals_' . md5($secret_key . '|' . $commission_rate);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $now_gmt = current_time('timestamp', true);
        $period_length = 30 * DAY_IN_SECONDS;

        $current_period_start = $now_gmt - $period_length;
        $previous_period_start = $current_period_start - $period_length;
        $previous_period_end = $current_period_start;

        $current = nymia_collect_stripe_charges_period($secret_key, $current_period_start, $now_gmt);
        $previous = nymia_collect_stripe_charges_period($secret_key, $previous_period_start, $previous_period_end);
        $lifetime = nymia_collect_stripe_charges_period($secret_key, null, null, array(
            'max_iterations' => 25,
        ));

        $currency = $lifetime['currency'] ?: ($current['currency'] ?: strtoupper(get_option('nymia_stripe_currency', 'USD')));

        $data = array(
            'success'   => true,
            'currency'  => $currency,
            'lifetime'  => $lifetime,
            'current'   => $current,
            'previous'  => $previous,
        );

        set_transient($cache_key, $data, 5 * MINUTE_IN_SECONDS);

        return $data;
    }
}

/**
 * Gather dashboard metrics with filterable defaults
 */
function nymia_get_dashboard_metrics() {
    $counts = function_exists('count_users') ? count_users() : array('total_users' => 0, 'avail_roles' => array());
    $role_counts = isset($counts['avail_roles']) ? $counts['avail_roles'] : array();

    $creator_count = 0;
    foreach (array('administrator', 'author') as $creator_role) {
        if (isset($role_counts[$creator_role])) {
            $creator_count += (int) $role_counts[$creator_role];
        }
    }

    $buyer_count = isset($role_counts['subscriber']) ? (int) $role_counts['subscriber'] : 0;
    $pending_kyc = 0;

    $user_items = array();
    if (function_exists('get_users')) {
        $current_admin_id = get_current_user_id();
        $can_promote = current_user_can('promote_users');
        $can_delete_users = current_user_can('delete_users');
        $can_edit_users = current_user_can('edit_users');
        $wp_users = get_users(array(
            'number'  => 6,
            'orderby' => 'registered',
            'order'   => 'DESC',
        ));

        foreach ($wp_users as $wp_user) {
            $entry = nymia_format_admin_user_entry($wp_user, $current_admin_id, $can_promote, $can_delete_users, $can_edit_users);
            if (!empty($entry)) {
                $user_items[] = $entry;
            }
        }
    }

    $current_timestamp = current_time('timestamp');
    $format_time_diff = static function ($timestamp, $current_timestamp) {
        if (!$timestamp) {
            return __('Just now', 'nymia');
        }

        $diff = max(0, $current_timestamp - $timestamp);

        if ($diff < MINUTE_IN_SECONDS) {
            return __('Just now', 'nymia');
        }

        if ($diff < HOUR_IN_SECONDS) {
            $minutes = floor($diff / MINUTE_IN_SECONDS);
            return sprintf(_n('%d minute ago', '%d minutes ago', $minutes, 'nymia'), $minutes);
        }

        if ($diff < DAY_IN_SECONDS) {
            $hours = floor($diff / HOUR_IN_SECONDS);
            return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'nymia'), $hours);
        }

        if ($diff < WEEK_IN_SECONDS) {
            $days = floor($diff / DAY_IN_SECONDS);
            return sprintf(_n('%d day ago', '%d days ago', $days, 'nymia'), $days);
        }

        $weeks = floor($diff / WEEK_IN_SECONDS);
        return sprintf(_n('%d week ago', '%d weeks ago', $weeks, 'nymia'), $weeks);
    };

    $activity_feed = array();

    $all_audio = get_transient('nymia_all_audio');
    if (is_array($all_audio)) {
        foreach (array_slice($all_audio, 0, 6) as $audio) {
            $timestamp = isset($audio['date']) ? strtotime($audio['date']) : 0;
            $activity_feed[] = array(
                'type' => 'audio',
                'message' => sprintf(
                    __('New audio upload • “%1$s” by %2$s', 'nymia'),
                    isset($audio['title']) ? $audio['title'] : __('Untitled', 'nymia'),
                    isset($audio['author']) ? $audio['author'] : __('Creator', 'nymia')
                ),
                'time' => $format_time_diff($timestamp, $current_timestamp),
                'timestamp' => $timestamp,
            );
        }
    }

    $all_ebooks = get_transient('nymia_all_ebooks');
    if (is_array($all_ebooks)) {
        foreach (array_slice($all_ebooks, 0, 6) as $ebook) {
            $timestamp = isset($ebook['date']) ? strtotime($ebook['date']) : 0;
            $activity_feed[] = array(
                'type' => 'ebook',
                'message' => sprintf(
                    __('New ebook published • “%1$s” by %2$s', 'nymia'),
                    isset($ebook['title']) ? $ebook['title'] : __('Untitled', 'nymia'),
                    isset($ebook['author']) ? $ebook['author'] : __('Creator', 'nymia')
                ),
                'time' => $format_time_diff($timestamp, $current_timestamp),
                'timestamp' => $timestamp,
            );
        }
    }

    $live_rooms = get_transient('nymia_zego_rooms');
    if (is_array($live_rooms)) {
        foreach ($live_rooms as $room) {
            $timestamp = isset($room['created']) ? (int) $room['created'] : 0;
            $activity_feed[] = array(
                'type' => 'live',
                'message' => sprintf(
                    __('Live stream started • “%1$s” by %2$s', 'nymia'),
                    isset($room['title']) && $room['title'] !== '' ? $room['title'] : __('Live Session', 'nymia'),
                    isset($room['creator_name']) ? $room['creator_name'] : __('Creator', 'nymia')
                ),
                'time' => $format_time_diff($timestamp, $current_timestamp),
                'timestamp' => $timestamp,
            );
        }
    }

    if (!empty($activity_feed)) {
        usort($activity_feed, static function ($a, $b) {
            $a_time = isset($a['timestamp']) ? (int) $a['timestamp'] : 0;
            $b_time = isset($b['timestamp']) ? (int) $b['timestamp'] : 0;
            return $b_time <=> $a_time;
        });

        $activity_feed = array_map(static function ($item) {
            unset($item['timestamp']);
            return $item;
        }, array_slice($activity_feed, 0, 8));
    }

    if (empty($user_items)) {
        $user_items = array(
            array('user_id' => 0, 'avatar' => '', 'name' => 'Ava Bright', 'role' => __('Creator', 'nymia'), 'status' => __('Active', 'nymia'), 'status_slug' => 'active', 'joined' => '2024-09-18', 'earnings' => '$8,912', 'profile_url' => home_url('/profile/'), 'admin_url' => admin_url('users.php'), 'can_manage_status' => false, 'can_delete' => false, 'can_edit' => false, 'is_self' => false),
            array('user_id' => 0, 'avatar' => '', 'name' => 'Liam Chen', 'role' => __('Buyer', 'nymia'), 'status' => __('Active', 'nymia'), 'status_slug' => 'active', 'joined' => '2024-10-02', 'earnings' => '$312', 'profile_url' => home_url('/profile/'), 'admin_url' => admin_url('users.php'), 'can_manage_status' => false, 'can_delete' => false, 'can_edit' => false, 'is_self' => false),
            array('user_id' => 0, 'avatar' => '', 'name' => 'Sofia Martínez', 'role' => __('Creator', 'nymia'), 'status' => __('Pending KYC', 'nymia'), 'status_slug' => 'active', 'joined' => '2024-10-11', 'earnings' => '$0', 'profile_url' => home_url('/profile/'), 'admin_url' => admin_url('users.php'), 'can_manage_status' => false, 'can_delete' => false, 'can_edit' => false, 'is_self' => false),
            array('user_id' => 0, 'avatar' => '', 'name' => 'Admin Team', 'role' => __('Creator', 'nymia'), 'status' => __('Active', 'nymia'), 'status_slug' => 'active', 'joined' => '2024-05-01', 'earnings' => '—', 'profile_url' => home_url('/profile/'), 'admin_url' => admin_url('users.php'), 'can_manage_status' => false, 'can_delete' => false, 'can_edit' => false, 'is_self' => false),
        );
    }

    $pending_requests = get_option('nymia_creator_pending_requests', array());
    $format_currency = static function ($amount, $currency = '') {
        $amount = max(0.0, (float) $amount);
        $currency = $currency ? strtoupper($currency) : strtoupper(get_option('nymia_stripe_currency', 'USD'));

        if (function_exists('wc_price')) {
            $price = wc_price($amount, array('currency' => $currency));
            return strip_tags($price);
        }

        $symbol = '';
        if (function_exists('get_woocommerce_currency_symbol')) {
            $symbol = get_woocommerce_currency_symbol($currency);
        }

        if ($symbol === '' || $symbol === $currency) {
            switch ($currency) {
                case 'USD':
                    $symbol = '$';
                    break;
                case 'EUR':
                    $symbol = '€';
                    break;
                case 'GBP':
                    $symbol = '£';
                    break;
                case 'CAD':
                    $symbol = 'CA$';
                    break;
                case 'AUD':
                    $symbol = 'A$';
                    break;
                default:
                    $symbol = '$';
                    break;
            }
        }

        return $symbol . number_format_i18n($amount, 2);
    };
    $creator_alerts = array();
    $kyc_requests = array();
    if (!empty($pending_requests) && is_array($pending_requests)) {
        foreach ($pending_requests as $request) {
            $name = isset($request['name']) ? $request['name'] : __('A user', 'nymia');
            $submitted_at = isset($request['submitted_at']) ? strtotime($request['submitted_at']) : 0;
            $timeago = $submitted_at ? human_time_diff($submitted_at, current_time('timestamp')) . ' ' . __('ago', 'nymia') : __('Awaiting review', 'nymia');

            $creator_alerts[] = array(
                'severity'    => 'warning',
                'title'       => __('New Creator Application', 'nymia'),
                'description' => sprintf(__('%1$s completed KYC verification. Review their creator request (%2$s).', 'nymia'), $name, $timeago),
            );

            $user_id = isset($request['user_id']) ? (int) $request['user_id'] : 0;
            $kyc_data = $user_id ? get_user_meta($user_id, 'nymia_creator_kyc_data', true) : array();
            $kyc_requests[] = array(
                'user_id'      => $user_id,
                'name'         => $name,
                'email'        => $user_id ? get_the_author_meta('user_email', $user_id) : '',
                'submitted_at' => $submitted_at,
                'submitted_human' => $timeago,
                'submitted_exact' => $submitted_at ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $submitted_at) : __('Not recorded', 'nymia'),
                'status'       => isset($request['kyc_status']) ? $request['kyc_status'] : 'submitted',
                'first_name'   => isset($request['kyc_first_name']) ? $request['kyc_first_name'] : (isset($kyc_data['first_name']) ? $kyc_data['first_name'] : ''),
                'last_name'    => isset($request['kyc_last_name']) ? $request['kyc_last_name'] : (isset($kyc_data['last_name']) ? $kyc_data['last_name'] : ''),
                'id_type'      => isset($request['kyc_id_type']) ? $request['kyc_id_type'] : (isset($kyc_data['id_type']) ? $kyc_data['id_type'] : ''),
                'id_number'    => isset($request['kyc_id_number']) ? $request['kyc_id_number'] : (isset($kyc_data['id_number']) ? $kyc_data['id_number'] : ''),
                'street'       => isset($request['kyc_street']) ? $request['kyc_street'] : (isset($kyc_data['street']) ? $kyc_data['street'] : ''),
                'city'         => isset($request['kyc_city']) ? $request['kyc_city'] : (isset($kyc_data['city']) ? $kyc_data['city'] : ''),
                'country'      => isset($request['kyc_country']) ? $request['kyc_country'] : (isset($kyc_data['country']) ? $kyc_data['country'] : ''),
                'zip_code'     => isset($request['kyc_zip_code']) ? $request['kyc_zip_code'] : (isset($kyc_data['zip_code']) ? $kyc_data['zip_code'] : ''),
                'document'     => isset($request['kyc_document']) ? $request['kyc_document'] : (isset($kyc_data['document']) ? $kyc_data['document'] : ''),
                'photo'        => isset($request['kyc_photo']) ? $request['kyc_photo'] : (isset($kyc_data['photo_capture']) ? $kyc_data['photo_capture'] : ''),
            );
        }
    }
    $pending_kyc = count($kyc_requests);

    $products_table = array();
    $resolve_creator_name = static function ($user_id, $fallback = '') {
        $user_id = intval($user_id);
        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                return $user->display_name ? $user->display_name : $user->user_login;
            }
        }
        return $fallback ? $fallback : __('Unknown Creator', 'nymia');
    };

    $now = current_time('timestamp');
    $current_period_start = $now - 30 * DAY_IN_SECONDS;
    $previous_period_start = $now - 60 * DAY_IN_SECONDS;
    $previous_period_end = $current_period_start;
    $current_products_count = 0;
    $previous_products_count = 0;

    if (is_array($all_audio)) {
        foreach ($all_audio as $audio_item) {
            $timestamp = !empty($audio_item['date']) ? strtotime($audio_item['date']) : 0;
            $creator_name = $resolve_creator_name(isset($audio_item['user_id']) ? $audio_item['user_id'] : 0, isset($audio_item['author']) ? $audio_item['author'] : '');
            $is_paid = !empty($audio_item['paid_access']) && $audio_item['paid_access'] === 'yes';
            $price_value = isset($audio_item['price']) ? floatval($audio_item['price']) : 0;
            $price_label = ($is_paid && $price_value > 0)
                ? sprintf('$%s', number_format_i18n($price_value, 2))
                : __('Free', 'nymia');
            $submitted_label = $timestamp
                ? sprintf(__('%s ago', 'nymia'), human_time_diff($timestamp, $now))
                : __('Unknown', 'nymia');

            if ($timestamp) {
                if ($timestamp >= $current_period_start) {
                    $current_products_count++;
                } elseif ($timestamp >= $previous_period_start && $timestamp < $previous_period_end) {
                    $previous_products_count++;
                }
            }

            $products_table[] = array(
                'title'     => isset($audio_item['title']) && $audio_item['title'] ? $audio_item['title'] : __('Untitled Audio', 'nymia'),
                'creator'   => $creator_name,
                'status'    => $is_paid ? __('Paid Audio', 'nymia') : __('Free Audio', 'nymia'),
                'status_slug' => $is_paid ? 'paid' : 'free',
                'submitted' => $submitted_label,
                'price'     => $price_label,
                'timestamp' => $timestamp ?: 0,
                'is_paid'   => $is_paid,
                'type'      => 'audio',
                'id'        => isset($audio_item['id']) ? (int) $audio_item['id'] : 0,
                'user_id'   => isset($audio_item['user_id']) ? (int) $audio_item['user_id'] : 0,
                'identifier'=> isset($audio_item['url']) ? $audio_item['url'] : '',
            );
        }
    }

    if (is_array($all_ebooks)) {
        foreach ($all_ebooks as $ebook_item) {
            $timestamp = !empty($ebook_item['date']) ? strtotime($ebook_item['date']) : 0;
            $creator_name = $resolve_creator_name(isset($ebook_item['user_id']) ? $ebook_item['user_id'] : 0, isset($ebook_item['author']) ? $ebook_item['author'] : '');
            $is_paid = !empty($ebook_item['paid_access']) && $ebook_item['paid_access'] === 'yes';
            $price_value = isset($ebook_item['price']) ? floatval($ebook_item['price']) : 0;
            $price_label = ($is_paid && $price_value > 0)
                ? sprintf('$%s', number_format_i18n($price_value, 2))
                : __('Free', 'nymia');
            $submitted_label = $timestamp
                ? sprintf(__('%s ago', 'nymia'), human_time_diff($timestamp, $now))
                : __('Unknown', 'nymia');

            if ($timestamp) {
                if ($timestamp >= $current_period_start) {
                    $current_products_count++;
                } elseif ($timestamp >= $previous_period_start && $timestamp < $previous_period_end) {
                    $previous_products_count++;
                }
            }

            $products_table[] = array(
                'title'     => isset($ebook_item['title']) && $ebook_item['title'] ? $ebook_item['title'] : __('Untitled Ebook', 'nymia'),
                'creator'   => $creator_name,
                'status'    => $is_paid ? __('Paid Ebook', 'nymia') : __('Free Ebook', 'nymia'),
                'status_slug' => $is_paid ? 'paid' : 'free',
                'submitted' => $submitted_label,
                'price'     => $price_label,
                'timestamp' => $timestamp ?: 0,
                'is_paid'   => $is_paid,
                'type'      => 'ebook',
                'id'        => isset($ebook_item['id']) ? (int) $ebook_item['id'] : 0,
                'user_id'   => isset($ebook_item['user_id']) ? (int) $ebook_item['user_id'] : 0,
                'identifier'=> isset($ebook_item['url']) ? $ebook_item['url'] : '',
            );
        }
    }

    $products_total_count = count($products_table);
    $paid_products_count = 0;
    if (!empty($products_table)) {
        foreach ($products_table as $product_row) {
            if (!empty($product_row['is_paid'])) {
                $paid_products_count++;
            }
        }
    }

    if (!empty($products_table)) {
        usort($products_table, static function ($a, $b) {
            return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
        });
        $products_table = array_slice($products_table, 0, 50);

        foreach ($products_table as &$product_row) {
            unset($product_row['timestamp'], $product_row['is_paid']);
        }
        unset($product_row);
    } else {
        $paid_products_count = 0;
    }

    $new_products_value = $current_products_count;
    $new_products_change = '0%';
    $new_products_change_type = 'up';

    if ($previous_products_count > 0) {
        $new_products_change_ratio = (($current_products_count - $previous_products_count) / $previous_products_count) * 100;
        $new_products_change = sprintf('%+0.1f%%', $new_products_change_ratio);
        $new_products_change_type = $new_products_change_ratio >= 0 ? 'up' : 'down';
    } elseif ($current_products_count > 0) {
        $new_products_change = __('+100%', 'nymia');
        $new_products_change_type = 'up';
    }

    $deleted_queue = get_option('nymia_deleted_products_queue', array());
    $deleted_items = array();
    $deleted_counts = array(
        'audio' => 0,
        'ebook' => 0,
    );

    if (is_array($deleted_queue) && !empty($deleted_queue)) {
        $current_ts = current_time('timestamp');

        foreach ($deleted_queue as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $type = isset($entry['type']) ? sanitize_key($entry['type']) : 'audio';
            $type_label = $type === 'ebook' ? __('Ebook', 'nymia') : __('Audio', 'nymia');
            if ($type === 'ebook') {
                $deleted_counts['ebook']++;
            } else {
                $deleted_counts['audio']++;
                $type = 'audio';
            }

            $token = isset($entry['token']) ? sanitize_text_field($entry['token']) : '';
            $product_id = isset($entry['product_id']) ? (int) $entry['product_id'] : 0;
            $user_id = isset($entry['user_id']) ? (int) $entry['user_id'] : 0;
            $timestamp = isset($entry['deleted_at']) ? (int) $entry['deleted_at'] : 0;
            $deleted_human = $timestamp ? human_time_diff($timestamp, $current_ts) . ' ' . __('ago', 'nymia') : __('Unknown', 'nymia');
            $deleted_exact = $timestamp ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp) : __('Unknown', 'nymia');

            $data = isset($entry['data']) && is_array($entry['data']) ? $entry['data'] : array();
            $raw_title = isset($entry['title']) && $entry['title'] !== '' ? $entry['title'] : (isset($data['title']) ? $data['title'] : '');
            $title = $raw_title !== '' ? $raw_title : ($type === 'ebook' ? __('Untitled Ebook', 'nymia') : __('Untitled Audio', 'nymia'));
            $title = sanitize_text_field($title);

            $creator_fallback = isset($data['author']) ? $data['author'] : '';
            $creator_name = $resolve_creator_name($user_id, $creator_fallback);

            $note = isset($entry['note']) ? sanitize_text_field($entry['note']) : '';
            $note_preview = $note !== '' ? wp_html_excerpt($note, 140, '&hellip;') : '';

        
            $deleted_items[] = array(
                'token'         => $token,
                'type'          => $type,
                'type_label'    => $type_label,
                'title'         => $title,
                'creator'       => $creator_name,
                'user_id'       => $user_id,
                'product_id'    => $product_id,
                'deleted_human' => $deleted_human,
                'deleted_exact' => $deleted_exact,
                'note'          => $note,
                'note_preview'  => $note_preview,
            );
        }
    }

    $deleted_total = count($deleted_items);

    $total_sales_count = 0;
    $total_sales_change = '0%';
    $total_sales_change_type = 'up';
    $platform_earnings_amount = 0.0;
    $platform_earnings_change = '0%';
    $platform_earnings_change_type = 'up';
    $stripe_currency = strtoupper(get_option('nymia_stripe_currency', 'USD'));
    $commission_rate = max(0.0, (float) apply_filters('nymia_dashboard_commission_rate', 0.2));
    $stripe_metrics = nymia_get_stripe_dashboard_totals($commission_rate);

    $new_users_total = isset($counts['total_users']) ? (int) $counts['total_users'] : 0;
    $new_users_change = '0%';
    $new_users_change_type = 'up';

    if (function_exists('count_users')) {
        global $wpdb;
        $table_users = $wpdb->users;
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_users)) === $table_users) {
            $now = current_time('timestamp', true);
            $current_start = gmdate('Y-m-d H:i:s', $now - 30 * DAY_IN_SECONDS);
            $previous_start = gmdate('Y-m-d H:i:s', $now - 60 * DAY_IN_SECONDS);
            $previous_end = $current_start;

            $current_users = (int) $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(ID) FROM {$table_users} WHERE user_registered >= %s", $current_start)
            );

            $previous_users = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(ID) FROM {$table_users} WHERE user_registered >= %s AND user_registered < %s",
                    $previous_start,
                    $previous_end
                )
            );

            if ($previous_users > 0) {
                $new_users_change_ratio = (($current_users - $previous_users) / $previous_users) * 100;
                $new_users_change = sprintf('%+0.1f%%', $new_users_change_ratio);
                $new_users_change_type = $new_users_change_ratio >= 0 ? 'up' : 'down';
            } elseif ($current_users > 0) {
                $new_users_change = __('+100%', 'nymia');
                $new_users_change_type = 'up';
            }
        }
    }

    if (!empty($stripe_metrics['success'])) {
        $stripe_currency = !empty($stripe_metrics['currency']) ? strtoupper($stripe_metrics['currency']) : $stripe_currency;

        $lifetime_metrics = isset($stripe_metrics['lifetime']) ? $stripe_metrics['lifetime'] : array();
        $current_metrics = isset($stripe_metrics['current']) ? $stripe_metrics['current'] : array();
        $previous_metrics = isset($stripe_metrics['previous']) ? $stripe_metrics['previous'] : array();

        $total_sales_count = isset($lifetime_metrics['count']) ? (int) $lifetime_metrics['count'] : 0;
        $current_sales = isset($current_metrics['count']) ? (int) $current_metrics['count'] : 0;
        $previous_sales = isset($previous_metrics['count']) ? (int) $previous_metrics['count'] : 0;

        if ($previous_sales > 0) {
            $change_ratio = (($current_sales - $previous_sales) / $previous_sales) * 100;
            $total_sales_change = sprintf('%+0.1f%%', $change_ratio);
            $total_sales_change_type = $change_ratio >= 0 ? 'up' : 'down';
        } elseif ($current_sales > 0) {
            $total_sales_change = __('+100%', 'nymia');
            $total_sales_change_type = 'up';
        } else {
            $total_sales_change = '0%';
            $total_sales_change_type = 'up';
        }

        $lifetime_amount = isset($lifetime_metrics['amount']) ? (float) $lifetime_metrics['amount'] : 0.0;
        $current_amount = isset($current_metrics['amount']) ? (float) $current_metrics['amount'] : 0.0;
        $previous_amount = isset($previous_metrics['amount']) ? (float) $previous_metrics['amount'] : 0.0;

        $platform_earnings_amount = $lifetime_amount * $commission_rate;
        $current_platform_earnings = $current_amount * $commission_rate;
        $previous_platform_earnings = $previous_amount * $commission_rate;

        if ($previous_platform_earnings > 0) {
            $earnings_change_ratio = (($current_platform_earnings - $previous_platform_earnings) / $previous_platform_earnings) * 100;
            $platform_earnings_change = sprintf('%+0.1f%%', $earnings_change_ratio);
            $platform_earnings_change_type = $earnings_change_ratio >= 0 ? 'up' : 'down';
        } elseif ($current_platform_earnings > 0) {
            $platform_earnings_change = __('+100%', 'nymia');
            $platform_earnings_change_type = 'up';
        } else {
            $platform_earnings_change = '0%';
            $platform_earnings_change_type = 'up';
        }
    }

    $platform_earnings_display = $format_currency($platform_earnings_amount, $stripe_currency);

    $defaults = array(
        'stats' => array(
            array(
                'label' => __('Total Sales', 'nymia'),
                'value' => number_format_i18n($total_sales_count),
                'change' => $total_sales_change,
                'change_type' => $total_sales_change_type,
            ),
            array(
                'label' => __('Platform Earnings', 'nymia'),
                'value' => $platform_earnings_display,
                'change' => $platform_earnings_change,
                'change_type' => $platform_earnings_change_type,
            ),
            array(
                'label' => __('New Users', 'nymia'),
                'value' => number_format_i18n($new_users_total),
                'change' => $new_users_change,
                'change_type' => $new_users_change_type,
            ),
            array(
                'label' => __('New Products', 'nymia'),
                'value' => number_format_i18n($new_products_value),
                'change' => $new_products_change,
                'change_type' => $new_products_change_type,
            ),
        ),
        'sales_chart' => array(
            'labels' => array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'),
            'sales' => array(4200, 5100, 4900, 5500, 6100, 5800, 6600),
            'earnings' => array(1400, 1600, 1550, 1725, 1940, 1880, 2100),
        ),
        'user_chart' => array(
            'labels' => array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'),
            'creators' => array(24, 30, 28, 36, 40, 38, 45),
            'buyers' => array(310, 340, 360, 390, 420, 440, 470),
        ),
        'top_products' => array(
            array('title' => 'Cinematic Soundscape', 'creator' => 'AudioForge', 'metric' => '12.4K plays', 'trend' => '+12%'),
            array('title' => 'Ambient Focus Pack', 'creator' => 'WavesLab', 'metric' => '10.1K plays', 'trend' => '+8%'),
            array('title' => 'Lo-Fi Sessions', 'creator' => 'NightCity', 'metric' => '8.7K plays', 'trend' => '+5%'),
        ),
        'activity' => !empty($activity_feed) ? $activity_feed : array(
            array('type' => 'audio', 'message' => __('New audio upload • “Cinematic Soundscape” • $39.00', 'nymia'), 'time' => __('Just now', 'nymia')),
            array('type' => 'ebook', 'message' => __('New ebook published • “Ambient Focus Pack”', 'nymia'), 'time' => __('5 minutes ago', 'nymia')),
            array('type' => 'live', 'message' => __('Live stream started • “Deep Chill Vol.2”', 'nymia'), 'time' => __('12 minutes ago', 'nymia')),
        ),
        'alerts' => !empty($creator_alerts)
            ? array_merge($creator_alerts, array(
                array('severity' => 'critical', 'title' => __('Payout Failure', 'nymia'), 'description' => __('Creator #453 payout to PayPal failed. Requires manual review.', 'nymia')),
                array('severity' => 'warning', 'title' => __('Support Ticket', 'nymia'), 'description' => __('New ticket: Buyer unable to download purchase #9832.', 'nymia')),
                array('severity' => 'info', 'title' => __('Suspicious Activity', 'nymia'), 'description' => __('Multiple login attempts flagged for user @mixmaster.', 'nymia')),
            ))
            : array(
                array('severity' => 'critical', 'title' => __('Payout Failure', 'nymia'), 'description' => __('Creator #453 payout to PayPal failed. Requires manual review.', 'nymia')),
                array('severity' => 'warning', 'title' => __('Support Ticket', 'nymia'), 'description' => __('New ticket: Buyer unable to download purchase #9832.', 'nymia')),
                array('severity' => 'info', 'title' => __('Suspicious Activity', 'nymia'), 'description' => __('Multiple login attempts flagged for user @mixmaster.', 'nymia')),
            ),
        'users' => array(
            'summary' => array(
                array('label' => __('Active Creators', 'nymia'), 'value' => number_format_i18n($creator_count), 'status' => 'stable'),
                array('label' => __('Active Buyers', 'nymia'), 'value' => number_format_i18n($buyer_count), 'status' => 'up'),
                array('label' => __('Pending Verifications', 'nymia'), 'value' => number_format_i18n($pending_kyc), 'status' => $pending_kyc ? 'warning' : 'stable'),
            ),
            'table' => $user_items,
            'kyc_requests' => $kyc_requests,
        ),
        'products' => array(
            'total_count' => $products_total_count,
            'paid_count' => $paid_products_count,
            'new_count' => $new_products_value,
            'table' => $products_table,
        ),
        'deleted' => array(
            'total_count' => $deleted_total,
            'audio_count' => $deleted_counts['audio'],
            'ebook_count' => $deleted_counts['ebook'],
            'items'       => $deleted_items,
        ),
        'financials' => array(
            'summary' => array(
                array('label' => __('Commission Rate', 'nymia'), 'value' => '20%', 'description' => __('Default platform commission', 'nymia')),
                array('label' => __('Payout Queue', 'nymia'), 'value' => '$6,540', 'description' => __('Pending next cycle', 'nymia')),
                array('label' => __('Refund Requests', 'nymia'), 'value' => '4', 'description' => __('Awaiting review', 'nymia')),
            ),
            'payouts' => array(
                array('creator' => 'AudioForge', 'amount' => '$1,320', 'method' => 'Stripe', 'status' => 'Scheduled'),
                array('creator' => 'NightCity', 'amount' => '$980', 'method' => 'PayPal', 'status' => 'Manual Review'),
                array('creator' => 'WavesLab', 'amount' => '$1,110', 'method' => 'Stripe', 'status' => 'Processing'),
            ),
        ),
        'moderation' => array(
            array('label' => __('Open Reports', 'nymia'), 'value' => '7'),
            array('label' => __('Reviews Pending', 'nymia'), 'value' => '12'),
            array('label' => __('Audio Checks', 'nymia'), 'value' => '5 pending'),
        ),
        'marketing' => array(
            array('title' => __('Active Discounts', 'nymia'), 'value' => '5', 'action' => __('Manage Coupons', 'nymia')),
            array('title' => __('Featured Slots', 'nymia'), 'value' => '2 of 6 used', 'action' => __('Update Featured', 'nymia')),
            array('title' => __('Newsletter Audience', 'nymia'), 'value' => '9,842 subscribers', 'action' => __('Send Campaign', 'nymia')),
        ),
        'settings' => array(
            array('title' => __('Branding & Identity', 'nymia'), 'description' => __('Logo, colors, legal pages, maintenance mode'), 'action' => __('Open Settings', 'nymia')),
            array('title' => __('Email Templates', 'nymia'), 'description' => __('Transactional messaging and notifications'), 'action' => __('Edit Templates', 'nymia')),
            array('title' => __('Integrations', 'nymia'), 'description' => __('Analytics, marketing, and streaming services'), 'action' => __('Manage Integrations', 'nymia')),
        ),
    );

    return apply_filters('nymia_dashboard_metrics', $defaults);
}

/**
 * Enqueue assets for the dashboard overview page
 */
function nymia_dashboard_admin_assets($hook) {
    $supported = array(
        'toplevel_page_nymia-theme-settings',
        'nymia-theme-settings_page_nymia-general-settings',
        'nymia-theme-settings_page_nymia-social-login-settings',
        'nymia-theme-settings_page_nymia-zegocloud-settings',
        'nymia-theme-settings_page_nymia-stripe-settings'
    );

    if (!in_array($hook, $supported, true)) {
        return;
    }

    wp_enqueue_style(
        'nymia-dashboard-overview',
        get_template_directory_uri() . '/admin/css/dashboard-overview.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'nymia-admin-nav',
        get_template_directory_uri() . '/admin/js/admin-nav.js',
        array('jquery'),
        '1.0.0',
        true
    );

    if ($hook === 'toplevel_page_nymia-theme-settings') {
        wp_enqueue_script(
            'nymia-chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js',
            array(),
            '4.4.6',
            true
        );

        wp_enqueue_script(
            'nymia-dashboard-overview',
            get_template_directory_uri() . '/admin/js/dashboard-overview.js',
            array('nymia-chart-js'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'nymia-dashboard-overview',
            'nymiaDashboardData',
            nymia_get_dashboard_metrics()
        );

        wp_localize_script(
            'nymia-dashboard-overview',
            'nymiaDashboardConfig',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('nymia_admin_review_kyc'),
                'userActionNonce' => wp_create_nonce('nymia_admin_user_actions'),
                'userSearchNonce' => wp_create_nonce('nymia_admin_user_search'),
                'productDeleteNonce' => wp_create_nonce('nymia_admin_delete_product'),
                'categoryNonce' => wp_create_nonce('nymia_category_action'),
                'ebookCategoryNonce' => wp_create_nonce('nymia_ebook_category_action'),
                'productRestoreNonce' => wp_create_nonce('nymia_admin_restore_product'),
                'strings' => array(
                    'processing' => __('Processing...', 'nymia'),
                    'approved'   => __('Approved', 'nymia'),
                    'rejected'   => __('Rejected', 'nymia'),
                    'error'      => __('Something went wrong. Please try again.', 'nymia'),
                    'pendingSingle' => __('%d pending review', 'nymia'),
                    'pendingPlural' => __('%d pending reviews', 'nymia'),
                    'pendingEmpty'  => __('No pending reviews', 'nymia'),
                    'empty'         => __('No pending KYC verifications. All creator applications are up to date.', 'nymia'),
                ),
                'userActions' => array(
                    'confirmSuspend'  => __('Suspend %s? They will be unable to log in until reactivated.', 'nymia'),
                    'confirmBan'       => __('Ban %s permanently? They will be blocked from logging in.', 'nymia'),
                    'confirmActivate'  => __('Activate %s and restore full access?', 'nymia'),
                    'confirmDelete'    => __('Delete %s and remove all associated data? This cannot be undone.', 'nymia'),
                    'noteLabel'        => __('Add a note for the user (sent via email)', 'nymia'),
                    'notePlaceholder'  => __('Explain why you are taking this action and how they can reach out.', 'nymia'),
                    'notePlaceholderSuspend' => __('Let the user know why they are suspended and how to appeal.', 'nymia'),
                    'notePlaceholderBan' => __('Explain why the account was banned and whether they can contact support.', 'nymia'),
                    'notePlaceholderDelete' => __('Share any final details before removing the account.', 'nymia'),
                    'notePlaceholderActivate' => __('Optionally welcome the user back or share next steps.', 'nymia'),
                    'noteHint'         => __('This note will be emailed to the user.', 'nymia'),
                    'noteHintBan'      => __('Banned users cannot reply through the platform, but they can email support.', 'nymia'),
                    'noteHintDelete'   => __('Deleting the user is permanent. Double-check before confirming.', 'nymia'),
                    'noteHintSuspend'  => __('Suggest steps the user can take to reactivate their account.', 'nymia'),
                    'noteRequired'     => __('Please provide a note before continuing.', 'nymia'),
                    'titleSuspend'     => __('Suspend Account', 'nymia'),
                    'titleBan'         => __('Ban Account', 'nymia'),
                    'titleActivate'    => __('Activate Account', 'nymia'),
                    'titleDelete'      => __('Delete Account', 'nymia'),
                    'warningBan'       => __('Banning %s blocks all future sign-ins.', 'nymia'),
                    'warningDelete'    => __('Deleting %s removes their content permanently. This cannot be undone.', 'nymia'),
                    'confirmLabelSuspend' => __('Suspend', 'nymia'),
                    'confirmLabelBan'     => __('Ban', 'nymia'),
                    'confirmLabelActivate'=> __('Activate', 'nymia'),
                    'confirmLabelDelete'  => __('Delete', 'nymia'),
                    'durationLabel'       => __('Suspend for', 'nymia'),
                    'durationLabelSuspend'=> __('Suspend for', 'nymia'),
                    'durationHint'        => __('Leave blank to suspend indefinitely.', 'nymia'),
                    'durationHintSuspend' => __('Leave blank to suspend indefinitely. The account will reactivate automatically after the selected period.', 'nymia'),
                    'durationDefaultUnit' => 'days',
                    'durationUnits'       => array(
                        array('value' => 'days',   'label' => __('Days', 'nymia')),
                        array('value' => 'weeks',  'label' => __('Weeks', 'nymia')),
                        array('value' => 'months', 'label' => __('Months', 'nymia')),
                        array('value' => 'years',  'label' => __('Years', 'nymia')),
                    ),
                    'fallbackMessage'  => __('Are you sure you want to continue?', 'nymia'),
                    'errorGeneric'     => __('Unable to process the request. Please try again.', 'nymia'),
                ),
                'searchStrings' => array(
                    'placeholder' => __('Search users…', 'nymia'),
                    'empty'       => __('No users match your search.', 'nymia'),
                    'error'       => __('Unable to search users. Please try again.', 'nymia'),
                    'view'        => __('View', 'nymia'),
                    'manage'      => __('Manage', 'nymia'),
                    'suspend'     => __('Suspend', 'nymia'),
                    'ban'         => __('Ban', 'nymia'),
                    'activate'    => __('Activate', 'nymia'),
                    'delete'      => __('Delete', 'nymia'),
                ),
                'productStrings' => array(
                    'dialogTitle'   => __('Delete product', 'nymia'),
                    'dialogMessage' => __('This action will permanently remove the selected product.', 'nymia'),
                    'dialogWarning' => __('This cannot be undone.', 'nymia'),
                    'confirmLabel'  => __('Delete', 'nymia'),
                    'deleteLabel'   => __('Delete', 'nymia'),
                    'totalLabel'    => __('%d total products', 'nymia'),
                    'paidLabel'     => __('%d monetized items', 'nymia'),
                    'noteLabel'     => __('Notify creator (optional)', 'nymia'),
                    'notePlaceholder' => __('Share why this product is being removed or if action is required.', 'nymia'),
                    'noteHint'      => __('The note will be emailed to the creator along with the removal notice.', 'nymia'),
                    'deletedMessage'=> __('“%s” was removed.', 'nymia'),
                    'deletedFallbackName' => __('This product', 'nymia'),
                    'restoreLabel'  => __('Restore', 'nymia'),
                    'restoreMessage'=> __('Product removed. You can restore it within the next few minutes.', 'nymia'),
                    'restoreSuccess'=> __('Product restored successfully.', 'nymia'),
                    'deletedTotalLabel'   => __('%d deleted items', 'nymia'),
                    'deletedAudioLabel'   => __('%d audio', 'nymia'),
                    'deletedEbookLabel'   => __('%d ebooks', 'nymia'),
                    'deletedEmptyAll'     => __('No deleted content yet.', 'nymia'),
                    'deletedEmptyFiltered'=> __('No deleted content matches your filters.', 'nymia'),
                    'emptyTable'    => __('No products have been uploaded yet.', 'nymia'),
                    'error'         => __('Unable to delete the product. Please try again.', 'nymia'),
                ),
            )
        );
    }
}
add_action('admin_enqueue_scripts', 'nymia_dashboard_admin_assets');

/**
 * Theme settings page (Dashboard)
 */
function nymia_theme_settings_page() {
    $metrics = nymia_get_dashboard_metrics();
    nymia_render_admin_settings_styles();
    $deleted_metrics = isset($metrics['deleted']) ? $metrics['deleted'] : array(
        'total_count' => 0,
        'audio_count' => 0,
        'ebook_count' => 0,
        'items'       => array(),
    );
    ?>
    <div class="wrap nymia-stripe-wrap nymia-dashboard-wrap nymia-dashboard-overview">
        <div class="nymia-dashboard-header nymia-stripe-header">
            <div>
                <h1><?php echo esc_html__('Dashboard Overview', 'nymia'); ?></h1>
                <p><?php echo esc_html__('Monitor real-time activity, manage users & products, and keep the marketplace running smoothly.', 'nymia'); ?></p>
            </div>
            <div class="nymia-dashboard-actions">
                <a href="?page=nymia-general-settings" class="nymia-secondary-btn"><?php esc_html_e('Platform Settings', 'nymia'); ?></a>
                <button type="button" class="nymia-primary-btn" id="nymia-refresh-dashboard">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh Data', 'nymia'); ?>
                </button>
            </div>
        </div>

        <nav class="nymia-dashboard-nav">
            <a class="active" href="?page=nymia-theme-settings"><?php esc_html_e('Dashboard Overview', 'nymia'); ?></a>
            <a href="?page=nymia-general-settings"><?php esc_html_e('General Settings', 'nymia'); ?></a>
            <a href="?page=nymia-social-login-settings"><?php esc_html_e('Social Login', 'nymia'); ?></a>
            <a href="?page=nymia-zegocloud-settings"><?php esc_html_e('ZEGO Cloud', 'nymia'); ?></a>
            <a href="?page=nymia-stripe-settings"><?php esc_html_e('Stripe Payments', 'nymia'); ?></a>
        </nav>

        <div class="nymia-tab-loader" id="nymia-tab-loader" hidden aria-hidden="true">
            <div class="nymia-tab-loader-spinner">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div class="nymia-dashboard-grid">
            <?php foreach ($metrics['stats'] as $stat) : ?>
                <div class="nymia-stat-card <?php echo esc_attr('is-' . $stat['change_type']); ?>">
                    <span class="nymia-stat-label"><?php echo esc_html($stat['label']); ?></span>
                    <span class="nymia-stat-value"><?php echo esc_html($stat['value']); ?></span>
                    <span class="nymia-stat-change"><?php echo esc_html($stat['change']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="nymia-flex-grid">
            <div class="nymia-card nymia-activity-card">
                <div class="nymia-card-head">
                    <h2><?php esc_html_e('Recent Activity', 'nymia'); ?></h2>
                    <button type="button" class="nymia-link-btn"><?php esc_html_e('View full log', 'nymia'); ?></button>
                </div>
                <ul class="nymia-activity-list">
                    <?php foreach ($metrics['activity'] as $item) : ?>
                        <li class="nymia-activity-item nymia-activity-<?php echo esc_attr($item['type']); ?>">
                            <span class="nymia-activity-indicator"></span>
                            <div>
                                <p><?php echo esc_html($item['message']); ?></p>
                                <span class="nymia-activity-time"><?php echo esc_html($item['time']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="nymia-card nymia-alert-card">
                <div class="nymia-card-head">
                    <h2><?php esc_html_e('Alerts & Notifications', 'nymia'); ?></h2>
                    <button type="button" class="nymia-link-btn"><?php esc_html_e('Open center', 'nymia'); ?></button>
                </div>
                <ul class="nymia-alert-list">
                    <?php foreach ($metrics['alerts'] as $alert) : ?>
                        <li class="nymia-alert-item nymia-alert-<?php echo esc_attr($alert['severity']); ?>">
                            <div>
                                <h3><?php echo esc_html($alert['title']); ?></h3>
                                <p><?php echo esc_html($alert['description']); ?></p>
                            </div>
                            <button type="button" class="nymia-tertiary-btn"><?php esc_html_e('Resolve', 'nymia'); ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="nymia-module-tabs" role="tablist" aria-label="Dashboard modules">
            <button type="button" id="nymia-tab-users-btn" class="nymia-module-tab-button is-active" role="tab" aria-selected="true" aria-controls="nymia-tab-users" data-nymia-tab-target="nymia-tab-users"><?php esc_html_e('User Management', 'nymia'); ?></button>
            <button type="button" id="nymia-tab-products-btn" class="nymia-module-tab-button" role="tab" aria-selected="false" aria-controls="nymia-tab-products" data-nymia-tab-target="nymia-tab-products"><?php esc_html_e('Product Management', 'nymia'); ?></button>
            <button type="button" id="nymia-tab-deleted-btn" class="nymia-module-tab-button" role="tab" aria-selected="false" aria-controls="nymia-tab-deleted" data-nymia-tab-target="nymia-tab-deleted"><?php esc_html_e('Deleted Content', 'nymia'); ?></button>
            <button type="button" id="nymia-tab-financial-btn" class="nymia-module-tab-button" role="tab" aria-selected="false" aria-controls="nymia-tab-financial" data-nymia-tab-target="nymia-tab-financial"><?php esc_html_e('Financial & Commission Management', 'nymia'); ?></button>
            <button type="button" id="nymia-tab-moderation-btn" class="nymia-module-tab-button" role="tab" aria-selected="false" aria-controls="nymia-tab-moderation" data-nymia-tab-target="nymia-tab-moderation"><?php esc_html_e('Moderation & Quality Control', 'nymia'); ?></button>
            <button type="button" id="nymia-tab-marketing-btn" class="nymia-module-tab-button" role="tab" aria-selected="false" aria-controls="nymia-tab-marketing" data-nymia-tab-target="nymia-tab-marketing"><?php esc_html_e('Marketing & Promotions', 'nymia'); ?></button>
            <button type="button" id="nymia-tab-settings-btn" class="nymia-module-tab-button" role="tab" aria-selected="false" aria-controls="nymia-tab-settings" data-nymia-tab-target="nymia-tab-settings"><?php esc_html_e('General Platform Settings', 'nymia'); ?></button>
        </div>

        <section class="nymia-section nymia-module-panel is-active" id="nymia-tab-users" role="tabpanel" aria-labelledby="nymia-tab-users-btn">
            <div class="nymia-section-head">
                <h2><?php esc_html_e('User Management', 'nymia'); ?></h2>
                <div class="nymia-section-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=nymia-user-management')); ?>" class="nymia-secondary-btn" style="text-decoration: none; display: inline-block;"><?php esc_html_e('Open Users', 'nymia'); ?></a>
                    <button type="button" class="nymia-tertiary-btn" id="nymia-export-csv-btn" style="cursor: pointer;"><?php esc_html_e('Export CSV', 'nymia'); ?></button>
                </div>
            </div>
            <div class="nymia-user-summary">
                <?php foreach ($metrics['users']['summary'] as $summary) : ?>
                    <div class="nymia-user-summary-card nymia-status-<?php echo esc_attr($summary['status']); ?>">
                        <span class="nymia-user-summary-label"><?php echo esc_html($summary['label']); ?></span>
                        <span class="nymia-user-summary-value"><?php echo esc_html($summary['value']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="nymia-user-search">
                <div class="nymia-user-search-row">
                    <label class="screen-reader-text" for="nymia-user-search-input"><?php esc_html_e('Search users', 'nymia'); ?></label>
                    <div class="nymia-user-search-field" id="nymia-user-search-field">
                        <span class="dashicons dashicons-search" aria-hidden="true"></span>
                        <input type="search" id="nymia-user-search-input" placeholder="<?php esc_attr_e('Search users…', 'nymia'); ?>" autocomplete="off" />
                    </div>
                    <label class="screen-reader-text" for="nymia-user-filter"><?php esc_html_e('Filter users by role', 'nymia'); ?></label>
                    <select id="nymia-user-filter" class="nymia-user-filter">
                        <option value="all"><?php esc_html_e('All roles', 'nymia'); ?></option>
                        <option value="creator"><?php esc_html_e('Creators', 'nymia'); ?></option>
                        <option value="buyer"><?php esc_html_e('Buyers', 'nymia'); ?></option>
                    </select>
                </div>
            </div>
            <div class="nymia-table-wrap">
                <table class="nymia-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('User', 'nymia'); ?></th>
                            <th><?php esc_html_e('Role', 'nymia'); ?></th>
                            <th><?php esc_html_e('Status', 'nymia'); ?></th>
                            <th><?php esc_html_e('Joined', 'nymia'); ?></th>
                            <th><?php esc_html_e('Lifetime Earnings', 'nymia'); ?></th>
                            <th><?php esc_html_e('Actions', 'nymia'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($metrics['users']['table'] as $user) : ?>
                            <tr
                                data-user-id="<?php echo esc_attr($user['user_id']); ?>"
                                data-user-status="<?php echo esc_attr($user['status_slug']); ?>"
                                data-is-self="<?php echo !empty($user['is_self']) ? '1' : '0'; ?>"
                                data-can-status="<?php echo !empty($user['can_manage_status']) ? '1' : '0'; ?>"
                                data-can-delete="<?php echo !empty($user['can_delete']) ? '1' : '0'; ?>"
                            >
                                <td>
                                    <div class="nymia-user-cell">
                                        <?php if (!empty($user['avatar'])) : ?>
                                            <span class="nymia-avatar" style="background-image: url('<?php echo esc_url($user['avatar']); ?>');"></span>
                                        <?php else : ?>
                                            <span class="nymia-avatar"></span>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo esc_html($user['name']); ?></strong>
                                            <span><?php echo esc_html($user['role']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo esc_html($user['role']); ?></td>
                                <td>
                                    <span class="nymia-status-pill <?php echo esc_attr('is-' . $user['status_slug']); ?>" data-user-status-label>
                                        <?php echo esc_html($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($user['joined']); ?></td>
                                <td><?php echo esc_html($user['earnings']); ?></td>
                                <td>
                                    <div class="nymia-user-actions">
                                        <?php if (!empty($user['profile_url'])) : ?>
                                            <a class="nymia-link-btn" href="<?php echo esc_url($user['profile_url']); ?>" target="_blank" rel="noopener">
                                                <?php esc_html_e('View', 'nymia'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($user['can_edit']) && !empty($user['admin_url'])) : ?>
                                            <a class="nymia-link-btn" href="<?php echo esc_url($user['admin_url']); ?>" target="_blank" rel="noopener">
                                                <?php esc_html_e('Manage', 'nymia'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($user['can_manage_status']) || !empty($user['can_delete'])) : ?>
                                            <?php if (!empty($user['can_manage_status'])) : ?>
                                            <button
                                                type="button"
                                                class="nymia-chip-btn is-warning"
                                                data-user-action="suspend"
                                                data-visible-for="active"
                                                data-user-id="<?php echo esc_attr($user['user_id']); ?>"
                                                data-user-name="<?php echo esc_attr($user['name']); ?>"
                                            >
                                                <?php esc_html_e('Suspend', 'nymia'); ?>
                                            </button>
                                            <button
                                                type="button"
                                                class="nymia-chip-btn is-danger"
                                                data-user-action="ban"
                                                data-visible-for="active,suspended"
                                                data-user-id="<?php echo esc_attr($user['user_id']); ?>"
                                                data-user-name="<?php echo esc_attr($user['name']); ?>"
                                            >
                                                <?php esc_html_e('Ban', 'nymia'); ?>
                                            </button>
                                            <button
                                                type="button"
                                                class="nymia-chip-btn is-success"
                                                data-user-action="activate"
                                                data-visible-for="suspended,banned"
                                                data-user-id="<?php echo esc_attr($user['user_id']); ?>"
                                                data-user-name="<?php echo esc_attr($user['name']); ?>"
                                            >
                                                <?php esc_html_e('Activate', 'nymia'); ?>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (!empty($user['can_delete'])) : ?>
                                            <button
                                                type="button"
                                                class="nymia-chip-btn is-muted"
                                                data-user-action="delete"
                                                data-visible-for="active,suspended,banned"
                                                data-user-id="<?php echo esc_attr($user['user_id']); ?>"
                                                data-user-name="<?php echo esc_attr($user['name']); ?>"
                                            >
                                                <?php esc_html_e('Delete', 'nymia'); ?>
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $kyc_requests = isset($metrics['users']['kyc_requests']) ? $metrics['users']['kyc_requests'] : array();
            ?>
            <div class="nymia-kyc-review">
                <div class="nymia-card-head">
                    <div>
                        <h3><?php esc_html_e('KYC Verification Queue', 'nymia'); ?></h3>
                        <p><?php esc_html_e('Review documents and approve creators once verification is complete.', 'nymia'); ?></p>
                    </div>
                    <span class="nymia-pill <?php echo !empty($kyc_requests) ? 'nymia-pill-warning' : 'nymia-pill-success'; ?>">
                        <?php
                        printf(
                            esc_html(_n('%d pending review', '%d pending reviews', count($kyc_requests), 'nymia')),
                            intval(count($kyc_requests))
                        );
                        ?>
                    </span>
                </div>

                <?php if (!empty($kyc_requests)) : ?>
                    <div class="nymia-kyc-grid">
                        <?php foreach ($kyc_requests as $request) : ?>
                            <article class="nymia-kyc-card" data-kyc-card="<?php echo esc_attr($request['user_id']); ?>">
                                <div class="nymia-kyc-card-head">
                                    <div>
                                        <h4><?php echo esc_html($request['name']); ?></h4>
                                        <?php if (!empty($request['email'])) : ?>
                                            <span class="nymia-kyc-email"><?php echo esc_html($request['email']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="nymia-pill nymia-kyc-status nymia-pill-warning">
                                        <?php esc_html_e('Pending', 'nymia'); ?>
                                    </span>
                                </div>

                                <dl class="nymia-kyc-meta">
                                    <div>
                                        <dt><?php esc_html_e('Submitted', 'nymia'); ?></dt>
                                        <dd><?php echo esc_html($request['submitted_exact']); ?> <span class="nymia-kyc-meta-muted">(<?php echo esc_html($request['submitted_human']); ?>)</span></dd>
                                    </div>
                                    <?php if (!empty($request['id_type'])) : ?>
                                    <div>
                                        <dt><?php esc_html_e('ID Type', 'nymia'); ?></dt>
                                        <dd><?php echo esc_html(ucwords(str_replace('_', ' ', $request['id_type']))); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($request['id_number'])) : ?>
                                    <div>
                                        <dt><?php esc_html_e('ID Number', 'nymia'); ?></dt>
                                        <dd><?php echo esc_html($request['id_number']); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($request['address'])) : ?>
                                    <div>
                                        <dt><?php esc_html_e('Address', 'nymia'); ?></dt>
                                        <dd><?php echo esc_html($request['address']); ?></dd>
                                    </div>
                                    <?php endif; ?>
                                </dl>

                                <div class="nymia-kyc-actions">
                                    <div class="nymia-kyc-links">
                                        <?php if (!empty($request['document'])) : ?>
                                            <a class="nymia-link-btn" href="<?php echo esc_url($request['document']); ?>" target="_blank" rel="noopener">
                                                <?php esc_html_e('View Document', 'nymia'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($request['photo'])) : ?>
                                            <a class="nymia-link-btn" href="<?php echo esc_url($request['photo']); ?>" target="_blank" rel="noopener">
                                                <?php esc_html_e('View Capture', 'nymia'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="nymia-kyc-decisions">
                                        <button type="button" class="nymia-secondary-btn" data-kyc-action="approve" data-kyc-user="<?php echo esc_attr($request['user_id']); ?>">
                                            <?php esc_html_e('Approve', 'nymia'); ?>
                                        </button>
                                        <button type="button" class="nymia-tertiary-btn" data-kyc-action="reject" data-kyc-user="<?php echo esc_attr($request['user_id']); ?>">
                                            <?php esc_html_e('Reject', 'nymia'); ?>
                                        </button>
                                    </div>
                                </div>

                                <div class="nymia-kyc-feedback" aria-live="polite"></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="nymia-kyc-empty">
                        <p><?php esc_html_e('No pending KYC verifications. All creator applications are up to date.', 'nymia'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        </section>

        <section class="nymia-section nymia-module-panel" id="nymia-tab-products" role="tabpanel" aria-labelledby="nymia-tab-products-btn" hidden>
            <div class="nymia-section-head">
                <h2><?php esc_html_e('Product Management', 'nymia'); ?></h2>
                <div class="nymia-section-actions">
                    <span class="nymia-pill nymia-pill-info" data-product-count-total><?php printf(esc_html__('%d total products', 'nymia'), intval($metrics['products']['total_count'])); ?></span>
                    <?php if (!empty($metrics['products']['paid_count'])) : ?>
                        <span class="nymia-pill nymia-pill-warning" data-product-count-paid><?php printf(esc_html__('%d monetized items', 'nymia'), intval($metrics['products']['paid_count'])); ?></span>
                    <?php endif; ?>
                    <button type="button" class="nymia-secondary-btn"><?php esc_html_e('Open Products', 'nymia'); ?></button>
                </div>
            </div>
            <div class="nymia-table-wrap">
                <table class="nymia-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Title', 'nymia'); ?></th>
                            <th><?php esc_html_e('Creator', 'nymia'); ?></th>
                            <th><?php esc_html_e('Status', 'nymia'); ?></th>
                            <th><?php esc_html_e('Submitted', 'nymia'); ?></th>
                            <th><?php esc_html_e('Price', 'nymia'); ?></th>
                            <th><?php esc_html_e('Actions', 'nymia'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="nymia-products-table-body">
                        <?php if (!empty($metrics['products']['table'])) : ?>
                            <?php foreach ($metrics['products']['table'] as $product) : ?>
                            <tr
                                data-product-type="<?php echo esc_attr(isset($product['type']) ? $product['type'] : ''); ?>"
                                data-product-id="<?php echo esc_attr(isset($product['id']) ? $product['id'] : ''); ?>"
                                data-product-user="<?php echo esc_attr(isset($product['user_id']) ? $product['user_id'] : ''); ?>"
                                data-product-title="<?php echo esc_attr(isset($product['title']) ? $product['title'] : ''); ?>"
                            >
                                <td><strong><?php echo esc_html($product['title']); ?></strong></td>
                                <td><?php echo esc_html($product['creator']); ?></td>
                                <td>
                                    <span class="nymia-status-pill <?php echo !empty($product['status_slug']) ? 'is-' . esc_attr($product['status_slug']) : ''; ?>">
                                        <?php echo esc_html($product['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($product['submitted']); ?></td>
                                <td><?php echo esc_html($product['price']); ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="nymia-chip-btn is-danger"
                                        data-product-action="delete"
                                        data-product-title="<?php echo esc_attr($product['title']); ?>"
                                        data-product-creator="<?php echo esc_attr($product['creator']); ?>"
                                    >
                                        <?php esc_html_e('Delete', 'nymia'); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr class="nymia-users-empty">
                                <td colspan="6"><?php esc_html_e('No products have been uploaded yet.', 'nymia'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="nymia-product-undo" aria-live="polite"></div>
        </section>
        <section class="nymia-section nymia-module-panel" id="nymia-tab-deleted" role="tabpanel" aria-labelledby="nymia-tab-deleted-btn" hidden>
            <div class="nymia-section-head">
                <h2><?php esc_html_e('Deleted Content', 'nymia'); ?></h2>
                <div class="nymia-section-actions">
                    <span class="nymia-pill nymia-pill-info" data-deleted-count-total>
                        <?php printf(esc_html__('%d deleted items', 'nymia'), intval($deleted_metrics['total_count'])); ?>
                    </span>
                    <span class="nymia-pill nymia-pill-warning<?php echo empty($deleted_metrics['audio_count']) ? ' is-hidden' : ''; ?>" data-deleted-count-audio>
                        <?php printf(esc_html__('%d audio', 'nymia'), intval($deleted_metrics['audio_count'])); ?>
                    </span>
                    <span class="nymia-pill nymia-pill-danger<?php echo empty($deleted_metrics['ebook_count']) ? ' is-hidden' : ''; ?>" data-deleted-count-ebook>
                        <?php printf(esc_html__('%d ebooks', 'nymia'), intval($deleted_metrics['ebook_count'])); ?>
                    </span>
                </div>
            </div>
            <div class="nymia-user-search">
                <div class="nymia-user-search-row">
                    <label class="screen-reader-text" for="nymia-deleted-search-input"><?php esc_html_e('Search deleted content', 'nymia'); ?></label>
                    <div class="nymia-user-search-field" id="nymia-deleted-search-field">
                        <span class="dashicons dashicons-search" aria-hidden="true"></span>
                        <input type="search" id="nymia-deleted-search-input" placeholder="<?php esc_attr_e('Search deleted content…', 'nymia'); ?>" autocomplete="off" />
                    </div>
                    <label class="screen-reader-text" for="nymia-deleted-filter"><?php esc_html_e('Filter deleted content by type', 'nymia'); ?></label>
                    <select id="nymia-deleted-filter" class="nymia-user-filter">
                        <option value="all"><?php esc_html_e('All types', 'nymia'); ?></option>
                        <option value="audio"><?php esc_html_e('Audio', 'nymia'); ?></option>
                        <option value="ebook"><?php esc_html_e('Ebook', 'nymia'); ?></option>
                    </select>
                </div>
            </div>
            <div class="nymia-table-wrap">
                <table class="nymia-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Title', 'nymia'); ?></th>
                            <th><?php esc_html_e('Type', 'nymia'); ?></th>
                            <th><?php esc_html_e('Creator', 'nymia'); ?></th>
                            <th><?php esc_html_e('Deleted', 'nymia'); ?></th>
                            <th><?php esc_html_e('Note', 'nymia'); ?></th>
                            <th><?php esc_html_e('Actions', 'nymia'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="nymia-deleted-table-body">
                        <?php if (!empty($deleted_metrics['items'])) : ?>
                            <?php foreach ($deleted_metrics['items'] as $item) : ?>
                                <tr
                                    data-product-type="<?php echo esc_attr(isset($item['type']) ? $item['type'] : ''); ?>"
                                    data-product-id="<?php echo esc_attr(isset($item['product_id']) ? $item['product_id'] : ''); ?>"
                                    data-product-user="<?php echo esc_attr(isset($item['user_id']) ? $item['user_id'] : ''); ?>"
                                    data-product-title="<?php echo esc_attr(isset($item['title']) ? $item['title'] : ''); ?>"
                                >
                                    <td><strong><?php echo esc_html($item['title']); ?></strong></td>
                                    <td><?php echo esc_html($item['type_label']); ?></td>
                                    <td><?php echo esc_html($item['creator']); ?></td>
                                    <td><span class="nymia-deleted-date" title="<?php echo esc_attr($item['deleted_exact']); ?>"><?php echo esc_html($item['deleted_human']); ?></span></td>
                                    <td>
                                        <?php if (!empty($item['note'])) : ?>
                                            <span class="nymia-deleted-note" title="<?php echo esc_attr($item['note']); ?>"><?php echo esc_html($item['note_preview']); ?></span>
                                        <?php else : ?>
                                            <span class="nymia-deleted-note is-empty">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="nymia-chip-btn is-success"
                                            data-product-restore-token="<?php echo esc_attr(isset($item['token']) ? $item['token'] : ''); ?>"
                                        >
                                            <?php esc_html_e('Restore', 'nymia'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr class="nymia-users-empty">
                                <td colspan="6"><?php esc_html_e('No deleted content yet.', 'nymia'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="nymia-section nymia-module-panel" id="nymia-tab-financial" role="tabpanel" aria-labelledby="nymia-tab-financial-btn" hidden>
            <div class="nymia-section-head">
                <h2><?php esc_html_e('Financial & Commission Management', 'nymia'); ?></h2>
                <div class="nymia-section-actions">
                    <button type="button" class="nymia-secondary-btn"><?php esc_html_e('Payout Settings', 'nymia'); ?></button>
                </div>
            </div>
            <div class="nymia-financial-grid">
                <?php foreach ($metrics['financials']['summary'] as $finance) : ?>
                    <div class="nymia-finance-card">
                        <span class="nymia-finance-label"><?php echo esc_html($finance['label']); ?></span>
                        <span class="nymia-finance-value"><?php echo esc_html($finance['value']); ?></span>
                        <p><?php echo esc_html($finance['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="nymia-table-wrap">
                <table class="nymia-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Creator', 'nymia'); ?></th>
                            <th><?php esc_html_e('Amount', 'nymia'); ?></th>
                            <th><?php esc_html_e('Method', 'nymia'); ?></th>
                            <th><?php esc_html_e('Status', 'nymia'); ?></th>
                            <th><?php esc_html_e('Actions', 'nymia'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metrics['financials']['payouts'] as $payout) : ?>
                            <tr>
                                <td><?php echo esc_html($payout['creator']); ?></td>
                                <td><?php echo esc_html($payout['amount']); ?></td>
                                <td><?php echo esc_html($payout['method']); ?></td>
                                <td><span class="nymia-status-pill"><?php echo esc_html($payout['status']); ?></span></td>
                                <td>
                                    <button type="button" class="nymia-link-btn"><?php esc_html_e('Details', 'nymia'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="nymia-section nymia-module-panel" id="nymia-tab-moderation" role="tabpanel" aria-labelledby="nymia-tab-moderation-btn" hidden>
            <div class="nymia-section-head">
                <h2><?php esc_html_e('Moderation & Quality Control', 'nymia'); ?></h2>
                <button type="button" class="nymia-secondary-btn"><?php esc_html_e('Open Moderation Center', 'nymia'); ?></button>
            </div>
            <div class="nymia-moderation-grid">
                <?php foreach ($metrics['moderation'] as $item) : ?>
                    <div class="nymia-moderation-card">
                        <span class="nymia-moderation-value"><?php echo esc_html($item['value']); ?></span>
                        <p><?php echo esc_html($item['label']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="nymia-section nymia-module-panel" id="nymia-tab-marketing" role="tabpanel" aria-labelledby="nymia-tab-marketing-btn" hidden>
            <div class="nymia-section-head">
                <h2><?php esc_html_e('Marketing & Promotions', 'nymia'); ?></h2>
                <button type="button" class="nymia-secondary-btn"><?php esc_html_e('Create Campaign', 'nymia'); ?></button>
            </div>
            <div class="nymia-marketing-grid">
                <?php foreach ($metrics['marketing'] as $marketing) : ?>
                    <div class="nymia-marketing-card">
                        <div>
                            <h3><?php echo esc_html($marketing['title']); ?></h3>
                            <p><?php echo esc_html($marketing['value']); ?></p>
                        </div>
                        <button type="button" class="nymia-link-btn"><?php echo esc_html($marketing['action']); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="nymia-section nymia-module-panel" id="nymia-tab-settings" role="tabpanel" aria-labelledby="nymia-tab-settings-btn" hidden>
            <div class="nymia-section-head">
                <h2><?php esc_html_e('General Platform Settings', 'nymia'); ?></h2>
                <button type="button" class="nymia-secondary-btn"><?php esc_html_e('Open Settings', 'nymia'); ?></button>
            </div>
            <div class="nymia-settings-grid">
                <?php foreach ($metrics['settings'] as $setting) : ?>
                    <div class="nymia-settings-card">
                        <h3><?php echo esc_html($setting['title']); ?></h3>
                        <p><?php echo esc_html($setting['description']); ?></p>
                        <button type="button" class="nymia-link-btn"><?php echo esc_html($setting['action']); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Audio Category Management -->
            <div class="nymia-card" style="margin-top: 32px;">
                <div class="nymia-card-head">
                    <div>
                        <h2><?php esc_html_e('Audio Categories Management', 'nymia'); ?></h2>
                        <p><?php esc_html_e('Create and manage categories for audio content. These categories will appear in the Audio upload form for creators.', 'nymia'); ?></p>
                    </div>
                </div>
                <div class="nymia-card-body">
                    <!-- Add Category Form -->
                    <div style="margin-bottom: 24px;">
                        <form id="nymia-add-audio-category-form" style="display: flex; gap: 12px; align-items: flex-end;">
                            <?php wp_nonce_field('nymia_category_action', 'nymia_category_nonce'); ?>
                            <div style="flex: 1; display: flex; flex-direction: column;">
                                <label for="nymia-audio-category-name" style="display: block; margin-bottom: 8px; color: var(--nymia-text-primary); font-weight: 500; font-size: 14px;">
                                    <?php esc_html_e('Category Name', 'nymia'); ?>
                                </label>
                                <input 
                                    type="text" 
                                    id="nymia-audio-category-name" 
                                    name="category_name" 
                                    class="nymia-input" 
                                    placeholder="<?php esc_attr_e('e.g., Music, Technology, Education', 'nymia'); ?>" 
                                    style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-primary); height: 44px; box-sizing: border-box;" 
                                    required 
                                />
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                                <label style="display: block; margin-bottom: 8px; color: transparent; font-weight: 500; font-size: 14px; user-select: none; pointer-events: none;">
                                    &nbsp;
                                </label>
                                <button type="submit" id="nymia-add-audio-category-btn" class="nymia-primary-btn" style="padding: 12px 24px; white-space: nowrap; height: 44px; display: flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;">
                                    <span class="dashicons dashicons-plus-alt" style="font-size: 18px; width: 18px; height: 18px; line-height: 1;"></span>
                                    <span class="btn-text"><?php esc_html_e('Add Category', 'nymia'); ?></span>
                                </button>
                            </div>
                        </form>
                        <div id="nymia-audio-category-message" style="margin-top: 12px; display: none;"></div>
                    </div>
                    
                    <!-- Category List -->
                    <div id="nymia-audio-categories-container">
                        <h3 style="color: var(--nymia-text-primary); font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                            <?php esc_html_e('Existing Categories', 'nymia'); ?>
                            <span id="nymia-audio-category-count" style="color: var(--nymia-text-muted); font-weight: 400; font-size: 14px; margin-left: 8px;">
                                (<?php 
                                $existing_categories = function_exists('nymia_get_audio_categories') ? nymia_get_audio_categories() : array();
                                $category_count = is_array($existing_categories) ? count($existing_categories) : 0;
                                printf(esc_html(_n('%d category', '%d categories', $category_count, 'nymia')), $category_count);
                                ?>)
                            </span>
                        </h3>
                        <div id="nymia-audio-categories-list">
                            <?php 
                            $existing_categories = function_exists('nymia_get_audio_categories') ? nymia_get_audio_categories() : array();
                            if (!is_array($existing_categories) || empty($existing_categories)): 
                            ?>
                                <div id="nymia-audio-empty-categories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);">
                                    <p style="margin: 0;"><?php esc_html_e('No categories created yet. Add your first category above.', 'nymia'); ?></p>
                                </div>
                            <?php else: ?>
                                <div id="nymia-audio-categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                                    <?php 
                                    sort($existing_categories);
                                    foreach ($existing_categories as $cat): 
                                    ?>
                                    <div class="nymia-category-item" data-category="<?php echo esc_attr($cat); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">
                                        <span style="color: var(--nymia-text-primary); font-weight: 500;"><?php echo esc_html($cat); ?></span>
                                        <button 
                                            type="button" 
                                            class="nymia-delete-category-btn nymia-delete-audio-category-btn" 
                                            data-category="<?php echo esc_attr($cat); ?>"
                                            style="color: var(--nymia-danger); background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;"
                                            onmouseover="this.style.opacity='1'"
                                            onmouseout="this.style.opacity='0.7'"
                                            title="<?php esc_attr_e('Delete Category', 'nymia'); ?>"
                                        >
                                            ×
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audio Sub-Categories Management -->
            <div class="nymia-card" style="margin-top: 32px;">
                <div class="nymia-card-head">
                    <div>
                        <h2><?php esc_html_e('Audio Sub-Categories Management', 'nymia'); ?></h2>
                        <p><?php esc_html_e('Create and manage sub-categories for audio content. Sub-categories are organized under parent categories.', 'nymia'); ?></p>
                    </div>
                </div>
                <div class="nymia-card-body">
                    <!-- Add Sub-Category Form -->
                    <div style="margin-bottom: 24px;">
                        <form id="nymia-add-audio-subcategory-form" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                            <?php wp_nonce_field('nymia_subcategory_action', 'nymia_subcategory_nonce'); ?>
                            <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column;">
                                <label for="nymia-audio-parent-category" style="display: block; margin-bottom: 8px; color: var(--nymia-text-primary); font-weight: 500; font-size: 14px;">
                                    <?php esc_html_e('Parent Category', 'nymia'); ?>
                                </label>
                                <select 
                                    id="nymia-audio-parent-category" 
                                    name="parent_category" 
                                    class="nymia-input" 
                                    style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-primary); height: 44px; box-sizing: border-box;" 
                                    required
                                >
                                    <option value=""><?php esc_html_e('Select a category', 'nymia'); ?></option>
                                    <?php 
                                    $audio_categories = function_exists('nymia_get_audio_categories') ? nymia_get_audio_categories() : array();
                                    foreach ($audio_categories as $cat): 
                                    ?>
                                        <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column;">
                                <label for="nymia-audio-subcategory-name" style="display: block; margin-bottom: 8px; color: var(--nymia-text-primary); font-weight: 500; font-size: 14px;">
                                    <?php esc_html_e('Sub-Category Name', 'nymia'); ?>
                                </label>
                                <input 
                                    type="text" 
                                    id="nymia-audio-subcategory-name" 
                                    name="subcategory_name" 
                                    class="nymia-input" 
                                    placeholder="<?php esc_attr_e('e.g., Rock, Jazz, Classical', 'nymia'); ?>" 
                                    style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-primary); height: 44px; box-sizing: border-box;" 
                                    required 
                                />
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                                <label style="display: block; margin-bottom: 8px; color: transparent; font-weight: 500; font-size: 14px; user-select: none; pointer-events: none;">
                                    &nbsp;
                                </label>
                                <button 
                                    type="submit" 
                                    id="nymia-add-audio-subcategory-btn" 
                                    class="nymia-primary-btn"
                                    style="padding: 12px 24px; white-space: nowrap; height: 44px; display: flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;"
                                >
                                    <span class="dashicons dashicons-plus-alt" style="font-size: 18px; width: 18px; height: 18px; line-height: 1;"></span>
                                    <span class="btn-text"><?php esc_html_e('Add Sub-Category', 'nymia'); ?></span>
                                </button>
                            </div>
                        </form>
                        <div id="nymia-audio-subcategory-message" style="margin-top: 12px; display: none;"></div>
                    </div>

                    <!-- Sub-Categories List -->
                    <div>
                        <h3 style="color: var(--nymia-text-primary); font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                            <?php esc_html_e('Sub-Categories', 'nymia'); ?>
                            <span id="nymia-audio-subcategory-count" style="color: var(--nymia-text-muted); font-weight: 400; font-size: 14px; margin-left: 8px;">
                                (<?php 
                                $subcategories = function_exists('nymia_get_audio_subcategories') ? nymia_get_audio_subcategories() : array();
                                $total_count = 0;
                                foreach ($subcategories as $parent => $subs) {
                                    $total_count += count($subs);
                                }
                                printf(esc_html(_n('%d sub-category', '%d sub-categories', $total_count, 'nymia')), $total_count);
                                ?>)
                            </span>
                        </h3>
                        <div id="nymia-audio-subcategories-list">
                            <?php 
                            $subcategories = function_exists('nymia_get_audio_subcategories') ? nymia_get_audio_subcategories() : array();
                            if (empty($subcategories)): 
                            ?>
                                <div id="nymia-audio-empty-subcategories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);">
                                    <p style="margin: 0;"><?php esc_html_e('No sub-categories created yet. Add your first sub-category above.', 'nymia'); ?></p>
                                </div>
                            <?php else: ?>
                                <?php 
                                ksort($subcategories);
                                foreach ($subcategories as $parent_cat => $subs): 
                                    if (empty($subs)) continue;
                                    sort($subs);
                                ?>
                                    <div style="margin-bottom: 24px;">
                                        <h4 style="color: var(--nymia-text-primary); margin-bottom: 12px; font-size: 1rem; font-weight: 600; padding-bottom: 8px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                            <?php echo esc_html($parent_cat); ?>
                                        </h4>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                                            <?php foreach ($subs as $sub): ?>
                                                <div class="nymia-subcategory-item" data-parent="<?php echo esc_attr($parent_cat); ?>" data-subcategory="<?php echo esc_attr($sub); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">
                                                    <span style="color: var(--nymia-text-primary); font-weight: 500; font-size: 0.9rem;"><?php echo esc_html($sub); ?></span>
                                                    <button 
                                                        type="button" 
                                                        class="nymia-delete-subcategory-btn" 
                                                        data-parent="<?php echo esc_attr($parent_cat); ?>"
                                                        data-subcategory="<?php echo esc_attr($sub); ?>"
                                                        style="color: var(--nymia-danger); background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;"
                                                        onmouseover="this.style.opacity='1'"
                                                        onmouseout="this.style.opacity='0.7'"
                                                        title="<?php esc_attr_e('Delete Sub-Category', 'nymia'); ?>"
                                                    >
                                                        ×
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ebook Category Management -->
            <div class="nymia-card" style="margin-top: 32px;">
                <div class="nymia-card-head">
                    <div>
                        <h2><?php esc_html_e('Ebook Categories Management', 'nymia'); ?></h2>
                        <p><?php esc_html_e('Create and manage categories for ebook uploads. These options will be available on the ebook tab of the Create page.', 'nymia'); ?></p>
                    </div>
                </div>
                <div class="nymia-card-body">
                    <!-- Add Ebook Category Form -->
                    <div style="margin-bottom: 24px;">
                        <form id="nymia-add-ebook-category-form" style="display: flex; gap: 12px; align-items: flex-end;">
                            <?php wp_nonce_field('nymia_ebook_category_action', 'nymia_ebook_category_nonce'); ?>
                            <div style="flex: 1; display: flex; flex-direction: column;">
                                <label for="nymia-ebook-category-name" style="display: block; margin-bottom: 8px; color: var(--nymia-text-primary); font-weight: 500; font-size: 14px;">
                                    <?php esc_html_e('Category Name', 'nymia'); ?>
                                </label>
                                <input 
                                    type="text" 
                                    id="nymia-ebook-category-name" 
                                    name="category_name" 
                                    class="nymia-input" 
                                    placeholder="<?php esc_attr_e('e.g., Fiction, Business, Technology', 'nymia'); ?>" 
                                    style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-primary); height: 44px; box-sizing: border-box;" 
                                    required 
                                />
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                                <label style="display: block; margin-bottom: 8px; color: transparent; font-weight: 500; font-size: 14px; user-select: none; pointer-events: none;">
                                    &nbsp;
                                </label>
                                <button type="submit" id="nymia-add-ebook-category-btn" class="nymia-primary-btn" style="padding: 12px 24px; white-space: nowrap; height: 44px; display: flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;">
                                    <span class="dashicons dashicons-plus-alt" style="font-size: 18px; width: 18px; height: 18px; line-height: 1;"></span>
                                    <span class="btn-text"><?php esc_html_e('Add Category', 'nymia'); ?></span>
                                </button>
                            </div>
                        </form>
                        <div id="nymia-ebook-category-message" style="margin-top: 12px; display: none;"></div>
                    </div>

                    <!-- Ebook Category List -->
                    <div id="nymia-ebook-categories-container">
                        <h3 style="color: var(--nymia-text-primary); font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                            <?php esc_html_e('Existing Ebook Categories', 'nymia'); ?>
                            <span id="nymia-ebook-category-count" style="color: var(--nymia-text-muted); font-weight: 400; font-size: 14px; margin-left: 8px;">
                                (<?php 
                                $existing_ebook_categories = function_exists('nymia_get_ebook_categories') ? nymia_get_ebook_categories() : array();
                                $ebook_category_count = is_array($existing_ebook_categories) ? count($existing_ebook_categories) : 0;
                                printf(esc_html(_n('%d category', '%d categories', $ebook_category_count, 'nymia')), $ebook_category_count);
                                ?>)
                            </span>
                        </h3>
                        <div id="nymia-ebook-categories-list">
                            <?php 
                            $existing_ebook_categories = function_exists('nymia_get_ebook_categories') ? nymia_get_ebook_categories() : array();
                            if (!is_array($existing_ebook_categories) || empty($existing_ebook_categories)): 
                            ?>
                                <div id="nymia-ebook-empty-categories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);">
                                    <p style="margin: 0;"><?php esc_html_e('No ebook categories yet. Add your first category above.', 'nymia'); ?></p>
                                </div>
                            <?php else: ?>
                                <div id="nymia-ebook-categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                                    <?php 
                                    sort($existing_ebook_categories);
                                    foreach ($existing_ebook_categories as $cat): 
                                    ?>
                                    <div class="nymia-category-item" data-category="<?php echo esc_attr($cat); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">
                                        <span style="color: var(--nymia-text-primary); font-weight: 500;"><?php echo esc_html($cat); ?></span>
                                        <button 
                                            type="button" 
                                            class="nymia-delete-category-btn nymia-delete-ebook-category-btn" 
                                            data-category="<?php echo esc_attr($cat); ?>"
                                            style="color: var(--nymia-danger); background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;"
                                            onmouseover="this.style.opacity='1'"
                                            onmouseout="this.style.opacity='0.7'"
                                            title="<?php esc_attr_e('Delete Category', 'nymia'); ?>"
                                        >
                                            ×
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ebook Sub-Categories Management -->
            <div class="nymia-card" style="margin-top: 32px;">
                <div class="nymia-card-head">
                    <div>
                        <h2><?php esc_html_e('Ebook Sub-Categories Management', 'nymia'); ?></h2>
                        <p><?php esc_html_e('Create and manage sub-categories for ebook content. Sub-categories are organized under parent categories.', 'nymia'); ?></p>
                    </div>
                </div>
                <div class="nymia-card-body">
                    <!-- Add Sub-Category Form -->
                    <div style="margin-bottom: 24px;">
                        <form id="nymia-add-ebook-subcategory-form" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                            <?php wp_nonce_field('nymia_ebook_subcategory_action', 'nymia_ebook_subcategory_nonce'); ?>
                            <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column;">
                                <label for="nymia-ebook-parent-category" style="display: block; margin-bottom: 8px; color: var(--nymia-text-primary); font-weight: 500; font-size: 14px;">
                                    <?php esc_html_e('Parent Category', 'nymia'); ?>
                                </label>
                                <select 
                                    id="nymia-ebook-parent-category" 
                                    name="parent_category" 
                                    class="nymia-input" 
                                    style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-primary); height: 44px; box-sizing: border-box;" 
                                    required
                                >
                                    <option value=""><?php esc_html_e('Select a category', 'nymia'); ?></option>
                                    <?php 
                                    $ebook_categories = function_exists('nymia_get_ebook_categories') ? nymia_get_ebook_categories() : array();
                                    foreach ($ebook_categories as $cat): 
                                    ?>
                                        <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column;">
                                <label for="nymia-ebook-subcategory-name" style="display: block; margin-bottom: 8px; color: var(--nymia-text-primary); font-weight: 500; font-size: 14px;">
                                    <?php esc_html_e('Sub-Category Name', 'nymia'); ?>
                                </label>
                                <input 
                                    type="text" 
                                    id="nymia-ebook-subcategory-name" 
                                    name="subcategory_name" 
                                    class="nymia-input" 
                                    placeholder="<?php esc_attr_e('e.g., Mystery, Romance, Thriller', 'nymia'); ?>" 
                                    style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-primary); height: 44px; box-sizing: border-box;" 
                                    required 
                                />
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                                <label style="display: block; margin-bottom: 8px; color: transparent; font-weight: 500; font-size: 14px; user-select: none; pointer-events: none;">
                                    &nbsp;
                                </label>
                                <button 
                                    type="submit" 
                                    id="nymia-add-ebook-subcategory-btn" 
                                    class="nymia-primary-btn"
                                    style="padding: 12px 24px; white-space: nowrap; height: 44px; display: flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;"
                                >
                                    <span class="dashicons dashicons-plus-alt" style="font-size: 18px; width: 18px; height: 18px; line-height: 1;"></span>
                                    <span class="btn-text"><?php esc_html_e('Add Sub-Category', 'nymia'); ?></span>
                                </button>
                            </div>
                        </form>
                        <div id="nymia-ebook-subcategory-message" style="margin-top: 12px; display: none;"></div>
                    </div>

                    <!-- Sub-Categories List -->
                    <div>
                        <h3 style="color: var(--nymia-text-primary); font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                            <?php esc_html_e('Sub-Categories', 'nymia'); ?>
                            <span id="nymia-ebook-subcategory-count" style="color: var(--nymia-text-muted); font-weight: 400; font-size: 14px; margin-left: 8px;">
                                (<?php 
                                $ebook_subcategories = function_exists('nymia_get_ebook_subcategories') ? nymia_get_ebook_subcategories() : array();
                                $total_count = 0;
                                foreach ($ebook_subcategories as $parent => $subs) {
                                    $total_count += count($subs);
                                }
                                printf(esc_html(_n('%d sub-category', '%d sub-categories', $total_count, 'nymia')), $total_count);
                                ?>)
                            </span>
                        </h3>
                        <div id="nymia-ebook-subcategories-list">
                            <?php 
                            $ebook_subcategories = function_exists('nymia_get_ebook_subcategories') ? nymia_get_ebook_subcategories() : array();
                            if (empty($ebook_subcategories)): 
                            ?>
                                <div id="nymia-ebook-empty-subcategories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);">
                                    <p style="margin: 0;"><?php esc_html_e('No sub-categories created yet. Add your first sub-category above.', 'nymia'); ?></p>
                                </div>
                            <?php else: ?>
                                <?php 
                                ksort($ebook_subcategories);
                                foreach ($ebook_subcategories as $parent_cat => $subs): 
                                    if (empty($subs)) continue;
                                    sort($subs);
                                ?>
                                    <div style="margin-bottom: 24px;">
                                        <h4 style="color: var(--nymia-text-primary); margin-bottom: 12px; font-size: 1rem; font-weight: 600; padding-bottom: 8px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                            <?php echo esc_html($parent_cat); ?>
                                        </h4>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                                            <?php foreach ($subs as $sub): ?>
                                                <div class="nymia-ebook-subcategory-item" data-parent="<?php echo esc_attr($parent_cat); ?>" data-subcategory="<?php echo esc_attr($sub); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">
                                                    <span style="color: var(--nymia-text-primary); font-weight: 500; font-size: 0.9rem;"><?php echo esc_html($sub); ?></span>
                                                    <button 
                                                        type="button" 
                                                        class="nymia-delete-ebook-subcategory-btn" 
                                                        data-parent="<?php echo esc_attr($parent_cat); ?>"
                                                        data-subcategory="<?php echo esc_attr($sub); ?>"
                                                        style="color: var(--nymia-danger); background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;"
                                                        onmouseover="this.style.opacity='1'"
                                                        onmouseout="this.style.opacity='0.7'"
                                                        title="<?php esc_attr_e('Delete Sub-Category', 'nymia'); ?>"
                                                    >
                                                        ×
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ebook Language Management -->
            <div class="nymia-card" style="margin-top: 32px;">
                <div class="nymia-card-head">
                    <div>
                        <h2><?php esc_html_e('Ebook Languages Management', 'nymia'); ?></h2>
                        <p><?php esc_html_e('Control which languages creators can select when uploading ebooks.', 'nymia'); ?></p>
                    </div>
                </div>
                <div class="nymia-card-body">
                    <div style="margin-bottom: 24px;">
                        <form id="nymia-add-ebook-language-form" style="display: flex; gap: 12px; align-items: flex-end;">
                            <?php wp_nonce_field('nymia_ebook_language_action', 'nymia_ebook_language_nonce'); ?>
                            <div style="flex: 1; display: flex; flex-direction: column;">
                                <label for="nymia-ebook-language-name" style="display: block; margin-bottom: 8px; color: var(--nymia-text-primary); font-weight: 500; font-size: 14px;">
                                    <?php esc_html_e('Language Name', 'nymia'); ?>
                                </label>
                                <input 
                                    type="text" 
                                    id="nymia-ebook-language-name" 
                                    name="language_name" 
                                    class="nymia-input" 
                                    placeholder="<?php esc_attr_e('e.g., English, Spanish, Hindi', 'nymia'); ?>" 
                                    style="width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-primary); height: 44px; box-sizing: border-box;" 
                                    required 
                                />
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                                <label style="display: block; margin-bottom: 8px; color: transparent; font-weight: 500; font-size: 14px; user-select: none; pointer-events: none;">
                                    &nbsp;
                                </label>
                                <button type="submit" id="nymia-add-ebook-language-btn" class="nymia-primary-btn" style="padding: 12px 24px; white-space: nowrap; height: 44px; display: flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;">
                                    <span class="dashicons dashicons-plus-alt" style="font-size: 18px; width: 18px; height: 18px; line-height: 1;"></span>
                                    <span class="btn-text"><?php esc_html_e('Add Language', 'nymia'); ?></span>
                                </button>
                            </div>
                        </form>
                        <div id="nymia-ebook-language-message" style="margin-top: 12px; display: none;"></div>
                    </div>

                    <div id="nymia-ebook-languages-container">
                        <h3 style="color: var(--nymia-text-primary); font-size: 16px; font-weight: 600; margin-bottom: 16px;">
                            <?php esc_html_e('Available Ebook Languages', 'nymia'); ?>
                            <span id="nymia-ebook-language-count" style="color: var(--nymia-text-muted); font-weight: 400; font-size: 14px; margin-left: 8px;">
                                (<?php 
                                $existing_ebook_languages = function_exists('nymia_get_ebook_languages') ? nymia_get_ebook_languages() : array();
                                $ebook_language_count = is_array($existing_ebook_languages) ? count($existing_ebook_languages) : 0;
                                printf(esc_html(_n('%d language', '%d languages', $ebook_language_count, 'nymia')), $ebook_language_count);
                                ?>)
                            </span>
                        </h3>
                        <div id="nymia-ebook-languages-list">
                            <?php 
                            $existing_ebook_languages = function_exists('nymia_get_ebook_languages') ? nymia_get_ebook_languages() : array();
                            if (!is_array($existing_ebook_languages) || empty($existing_ebook_languages)): 
                            ?>
                                <div id="nymia-ebook-empty-languages" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);">
                                    <p style="margin: 0;"><?php esc_html_e('No languages added yet. Add your first language above.', 'nymia'); ?></p>
                                </div>
                            <?php else: ?>
                                <div id="nymia-ebook-languages-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                                    <?php 
                                    sort($existing_ebook_languages);
                                    foreach ($existing_ebook_languages as $lang): 
                                    ?>
                                    <div class="nymia-category-item" data-category="<?php echo esc_attr($lang); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">
                                        <span style="color: var(--nymia-text-primary); font-weight: 500;"><?php echo esc_html($lang); ?></span>
                                        <button 
                                            type="button" 
                                            class="nymia-delete-category-btn nymia-delete-ebook-language-btn" 
                                            data-category="<?php echo esc_attr($lang); ?>"
                                            style="color: var(--nymia-danger); background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;"
                                            onmouseover="this.style.opacity='1'"
                                            onmouseout="this.style.opacity='0.7'"
                                            title="<?php esc_attr_e('Delete Language', 'nymia'); ?>"
                                        >
                                            ×
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Export CSV Modal -->
    <div class="nymia-admin-dialog" id="nymia-export-csv-modal" role="dialog" aria-modal="true" aria-hidden="true" hidden>
        <div class="nymia-admin-dialog-card" style="max-width: 600px;">
            <div>
                <h3 data-export-title><?php esc_html_e('Export CSV', 'nymia'); ?></h3>
                <p data-export-message><?php esc_html_e('Select user type and search for users to export', 'nymia'); ?></p>
            </div>
            
            <!-- User Type Selection -->
            <div class="nymia-export-type-selector" style="display: flex; gap: 12px; margin-bottom: 20px;">
                <button type="button" class="nymia-export-type-btn active" data-export-type="creator" style="flex: 1; padding: 12px; border-radius: 8px; background: rgba(191, 76, 26, 0.2); border: 2px solid rgba(191, 76, 26, 0.4); color: #fff; cursor: pointer; font-weight: 600;">
                    <?php esc_html_e('Creators', 'nymia'); ?>
                </button>
                <button type="button" class="nymia-export-type-btn" data-export-type="customer" style="flex: 1; padding: 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.7); cursor: pointer; font-weight: 600;">
                    <?php esc_html_e('Customers', 'nymia'); ?>
                </button>
            </div>
            
            <!-- Search Field -->
            <div style="margin-bottom: 20px;">
                <input type="text" id="nymia-export-search" placeholder="<?php esc_attr_e('Search by name, username, or email...', 'nymia'); ?>" style="width: 100%; padding: 12px 14px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #fff; font-size: 14px;" />
            </div>
            
            <!-- Selected Users List -->
            <div id="nymia-export-selected" style="margin-bottom: 20px; min-height: 40px; max-height: 150px; overflow-y: auto; padding: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">
                <div style="color: rgba(255, 255, 255, 0.5); font-size: 13px; text-align: center; padding: 10px;">
                    <?php esc_html_e('No users selected. Search and select users to export.', 'nymia'); ?>
                </div>
            </div>
            
            <!-- Search Results -->
            <div id="nymia-export-results" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; display: none;">
                <!-- Results will be populated here -->
            </div>
            
            <div class="nymia-admin-dialog-actions">
                <button type="button" class="nymia-dialog-btn-cancel" data-export-cancel><?php esc_html_e('Cancel', 'nymia'); ?></button>
                <button type="button" class="nymia-dialog-btn-confirm" id="nymia-export-confirm-btn" disabled><?php esc_html_e('Export CSV', 'nymia'); ?></button>
            </div>
        </div>
    </div>
    
    <div class="nymia-admin-dialog" id="nymia-admin-dialog" role="dialog" aria-modal="true" aria-hidden="true" hidden>
        <div class="nymia-admin-dialog-card">
            <div>
                <h3 data-dialog-title><?php esc_html_e('Confirm Action', 'nymia'); ?></h3>
                <p data-dialog-message><?php esc_html_e('Are you sure you want to continue?', 'nymia'); ?></p>
            </div>
            <div class="nymia-admin-dialog-note" data-dialog-note-wrapper>
                <label data-dialog-note-label for="nymia-dialog-note"><?php esc_html_e('Add a note for the user (sent via email)', 'nymia'); ?></label>
                <textarea id="nymia-dialog-note" data-dialog-note rows="4" placeholder=""></textarea>
                <span class="nymia-dialog-note-hint" data-dialog-note-hint></span>
            </div>
            <div class="nymia-admin-dialog-duration" data-dialog-duration-wrapper>
                <label data-dialog-duration-label for="nymia-dialog-duration-value"><?php esc_html_e('Suspend for', 'nymia'); ?></label>
                <div class="nymia-duration-row">
                    <input type="number" id="nymia-dialog-duration-value" data-dialog-duration-value min="1" inputmode="numeric" />
                    <select id="nymia-dialog-duration-unit" data-dialog-duration-unit></select>
                </div>
                <span class="nymia-dialog-duration-hint" data-dialog-duration-hint></span>
            </div>
            <div class="nymia-admin-dialog-meta">
                <span data-dialog-user></span>
                <span data-dialog-warning></span>
            </div>
            <div class="nymia-admin-dialog-actions">
                <button type="button" class="nymia-dialog-btn-cancel" data-dialog-cancel><?php esc_html_e('Cancel', 'nymia'); ?></button>
                <button type="button" class="nymia-dialog-btn-confirm" data-dialog-confirm><?php esc_html_e('Confirm', 'nymia'); ?></button>
            </div>
        </div>
    </div>
    
    <script>
    (function($) {
        'use strict';

        const categoryManagers = [
            {
                form: '#nymia-add-audio-category-form',
                input: '#nymia-audio-category-name',
                button: '#nymia-add-audio-category-btn',
                message: '#nymia-audio-category-message',
                listWrapper: '#nymia-audio-categories-list',
                countSelector: '#nymia-audio-category-count',
                deleteButtonSelector: '.nymia-delete-audio-category-btn',
                deleteButtonClassName: 'nymia-delete-category-btn nymia-delete-audio-category-btn',
                addAction: 'nymia_add_category',
                deleteAction: 'nymia_delete_category',
                nonceField: '#nymia_category_nonce',
                nonceKey: 'categoryNonce',
                emptyStateHtml: '<div id="nymia-audio-empty-categories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);"><p style="margin: 0;"><?php echo esc_js(__('No categories created yet. Add your first category above.', 'nymia')); ?></p></div>',
                messages: {
                    required: '<?php echo esc_js(__('Please enter a category name', 'nymia')); ?>',
                    adding: '<?php echo esc_js(__('Adding...', 'nymia')); ?>',
                    addIdle: '<?php echo esc_js(__('Add Category', 'nymia')); ?>',
                    addFail: '<?php echo esc_js(__('Failed to add category', 'nymia')); ?>',
                    deleteFail: '<?php echo esc_js(__('Failed to delete category', 'nymia')); ?>',
                    ajaxError: '<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>',
                    deleteConfirm: '<?php echo esc_js(__('Are you sure you want to delete the audio category "%s"? This will not remove existing audio files, but creators will no longer be able to select this category.', 'nymia')); ?>',
                    deleteLabel: '<?php echo esc_js(__('Delete Category', 'nymia')); ?>',
                    addSuccess: '<?php echo esc_js(__('Category added successfully', 'nymia')); ?>',
                    deleteSuccess: '<?php echo esc_js(__('Category deleted successfully', 'nymia')); ?>',
                    countSingle: '<?php echo esc_js(__('1 category', 'nymia')); ?>',
                    countPlural: '<?php echo esc_js(__('%d categories', 'nymia')); ?>'
                },
                deleteButtonTitle: '<?php echo esc_js(__('Delete Category', 'nymia')); ?>',
                paramName: 'category_name'
            },
            {
                form: '#nymia-add-ebook-category-form',
                input: '#nymia-ebook-category-name',
                button: '#nymia-add-ebook-category-btn',
                message: '#nymia-ebook-category-message',
                listWrapper: '#nymia-ebook-categories-list',
                countSelector: '#nymia-ebook-category-count',
                deleteButtonSelector: '.nymia-delete-ebook-category-btn',
                deleteButtonClassName: 'nymia-delete-category-btn nymia-delete-ebook-category-btn',
                addAction: 'nymia_add_ebook_category',
                deleteAction: 'nymia_delete_ebook_category',
                nonceField: '#nymia_ebook_category_nonce',
                nonceKey: 'ebookCategoryNonce',
                emptyStateHtml: '<div id="nymia-ebook-empty-categories" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);"><p style="margin: 0;"><?php echo esc_js(__('No ebook categories yet. Add your first category above.', 'nymia')); ?></p></div>',
                messages: {
                    required: '<?php echo esc_js(__('Please enter a category name', 'nymia')); ?>',
                    adding: '<?php echo esc_js(__('Adding...', 'nymia')); ?>',
                    addIdle: '<?php echo esc_js(__('Add Category', 'nymia')); ?>',
                    addFail: '<?php echo esc_js(__('Failed to add category', 'nymia')); ?>',
                    deleteFail: '<?php echo esc_js(__('Failed to delete category', 'nymia')); ?>',
                    ajaxError: '<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>',
                    deleteConfirm: '<?php echo esc_js(__('Are you sure you want to delete the ebook category "%s"? This will not remove existing ebooks, but creators will no longer be able to select this category.', 'nymia')); ?>',
                    deleteLabel: '<?php echo esc_js(__('Delete Category', 'nymia')); ?>',
                    addSuccess: '<?php echo esc_js(__('Category added successfully', 'nymia')); ?>',
                    deleteSuccess: '<?php echo esc_js(__('Category deleted successfully', 'nymia')); ?>',
                    countSingle: '<?php echo esc_js(__('1 category', 'nymia')); ?>',
                    countPlural: '<?php echo esc_js(__('%d categories', 'nymia')); ?>'
                },
                deleteButtonTitle: '<?php echo esc_js(__('Delete Category', 'nymia')); ?>',
                paramName: 'category_name'
            },
            {
                form: '#nymia-add-ebook-language-form',
                input: '#nymia-ebook-language-name',
                button: '#nymia-add-ebook-language-btn',
                message: '#nymia-ebook-language-message',
                listWrapper: '#nymia-ebook-languages-list',
                countSelector: '#nymia-ebook-language-count',
                deleteButtonSelector: '.nymia-delete-ebook-language-btn',
                deleteButtonClassName: 'nymia-delete-category-btn nymia-delete-ebook-language-btn',
                addAction: 'nymia_add_ebook_language',
                deleteAction: 'nymia_delete_ebook_language',
                nonceField: '#nymia_ebook_language_nonce',
                nonceKey: 'ebookLanguageNonce',
                emptyStateHtml: '<div id="nymia-ebook-empty-languages" style="padding: 24px; text-align: center; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--nymia-text-muted);"><p style="margin: 0;"><?php echo esc_js(__('No languages added yet. Add your first language above.', 'nymia')); ?></p></div>',
                messages: {
                    required: '<?php echo esc_js(__('Please enter a language name', 'nymia')); ?>',
                    adding: '<?php echo esc_js(__('Adding...', 'nymia')); ?>',
                    addIdle: '<?php echo esc_js(__('Add Language', 'nymia')); ?>',
                    addFail: '<?php echo esc_js(__('Failed to add language', 'nymia')); ?>',
                    deleteFail: '<?php echo esc_js(__('Failed to delete language', 'nymia')); ?>',
                    ajaxError: '<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>',
                    deleteConfirm: '<?php echo esc_js(__('Are you sure you want to delete the ebook language "%s"? This will not remove existing ebooks, but creators will no longer be able to select this language.', 'nymia')); ?>',
                    deleteLabel: '<?php echo esc_js(__('Delete Language', 'nymia')); ?>',
                    addSuccess: '<?php echo esc_js(__('Language added successfully', 'nymia')); ?>',
                    deleteSuccess: '<?php echo esc_js(__('Language deleted successfully', 'nymia')); ?>',
                    countSingle: '<?php echo esc_js(__('1 language', 'nymia')); ?>',
                    countPlural: '<?php echo esc_js(__('%d languages', 'nymia')); ?>'
                },
                deleteButtonTitle: '<?php echo esc_js(__('Delete Language', 'nymia')); ?>',
                paramName: 'language_name'
            }
        ];

        categoryManagers.forEach(function(config) {
            setupCategoryManager(config);
        });

        function setupCategoryManager(config) {
            const form = $(config.form);
            if (!form.length) {
                return;
            }

            const input = $(config.input);
            const button = $(config.button);
            const messageDiv = $(config.message);

            form.on('submit', function(e) {
                e.preventDefault();

                const categoryName = input.val().trim();

                if (!categoryName) {
                    showMessage(messageDiv, config.messages.required, 'error');
                    return;
                }

                button.prop('disabled', true);
                button.find('.btn-text').text(config.messages.adding);
                messageDiv.hide();

                const addPayload = {
                    action: config.addAction,
                    nonce: getNonceValue(config)
                };
                addPayload[config.paramName || 'category_name'] = categoryName;

                $.ajax({
                    url: getAjaxUrl(),
                    type: 'POST',
                    data: addPayload,
                    success: function(response) {
                        if (response.success) {
                            const normalized = normalizeItems(response.data);
                            input.val('');
                            showMessage(messageDiv, response.data.message || config.messages.addSuccess, 'success');
                            updateCategoryList(config, normalized);
                            updateCategoryCount(config, normalized.length);
                        } else {
                            var addErrorMsg = (response.data && response.data.message) ? response.data.message : config.messages.addFail;
                            showMessage(messageDiv, addErrorMsg, 'error');
                        }
                    },
                    error: function() {
                        showMessage(messageDiv, config.messages.ajaxError, 'error');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        button.find('.btn-text').text(config.messages.addIdle);
                    }
                });
            });

            $(config.listWrapper).on('click', config.deleteButtonSelector, function(e) {
                e.preventDefault();

                const btn = $(this);
                const categoryName = btn.data('category');

                if (!categoryName) {
                    return;
                }

                btn.prop('disabled', true);
                
                showConfirmationDialog(
                    config.messages.deleteConfirm.replace('%s', categoryName),
                    config.messages.deleteLabel || '<?php echo esc_js(__('Delete', 'nymia')); ?>'
                ).then(function() {
                    const deletePayload = {
                        action: config.deleteAction,
                        nonce: getNonceValue(config)
                    };
                    deletePayload[config.paramName || 'category_name'] = categoryName;
                    
                    $.ajax({
                        url: getAjaxUrl(),
                        type: 'POST',
                        data: deletePayload,
                        success: function(response) {
                            if (response.success) {
                                const normalized = normalizeItems(response.data);
                                updateCategoryList(config, normalized);
                                updateCategoryCount(config, normalized.length);
                                showMessage(messageDiv, response.data.message || config.messages.deleteSuccess, 'success');
                            } else {
                                var deleteErrorMsg = (response.data && response.data.message) ? response.data.message : config.messages.deleteFail;
                                showMessage(messageDiv, deleteErrorMsg, 'error');
                                btn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            showMessage(messageDiv, config.messages.ajaxError, 'error');
                            btn.prop('disabled', false);
                        }
                    });
                }).catch(function() {
                    btn.prop('disabled', false);
                });
            });
        }

        function getAjaxUrl() {
            if (typeof ajaxurl !== 'undefined') {
                return ajaxurl;
            }
            if (typeof nymiaDashboardConfig !== 'undefined' && nymiaDashboardConfig.ajaxUrl) {
                return nymiaDashboardConfig.ajaxUrl;
            }
            return '/wp-admin/admin-ajax.php';
        }

        function getNonceValue(config) {
            if (typeof nymiaDashboardConfig !== 'undefined' && config.nonceKey && nymiaDashboardConfig[config.nonceKey]) {
                return nymiaDashboardConfig[config.nonceKey];
            }
            if (config.nonceField && $(config.nonceField).length) {
                return $(config.nonceField).val();
            }
            return '';
        }

        function updateCategoryList(config, categories) {
            const container = $(config.listWrapper);
            if (!container.length) {
                return;
            }

            if (!categories || !categories.length) {
                container.html(config.emptyStateHtml);
                return;
            }

            categories.sort();

            let gridHtml = '<div class="nymia-category-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">';
            categories.forEach(function(cat) {
                const safe = escapeHtml(cat);
                gridHtml += '<div class="nymia-category-item" data-category="' + safe + '" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;">' +
                    '<span style="color: var(--nymia-text-primary); font-weight: 500;">' + safe + '</span>' +
                    '<button type="button" class="' + config.deleteButtonClassName + '" data-category="' + safe + '" style="color: var(--nymia-danger); background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1; opacity: 0.7; transition: opacity 0.2s;" title="' + config.deleteButtonTitle + '">×</button>' +
                    '</div>';
            });
            gridHtml += '</div>';

            container.html(gridHtml);
        }

        function updateCategoryCount(config, count) {
            const countSpan = $(config.countSelector);
            if (!countSpan.length) {
                return;
            }

            const countText = count === 1 ? config.messages.countSingle : config.messages.countPlural.replace('%d', count);
            countSpan.text('(' + countText + ')');
        }

        function showMessage(div, message, type) {
            if (!div || !div.length) {
                return;
            }

            const bgColor = type === 'success' ? 'rgba(52, 211, 153, 0.1)' : 'rgba(248, 113, 113, 0.1)';
            const borderColor = type === 'success' ? 'rgba(52, 211, 153, 0.3)' : 'rgba(248, 113, 113, 0.3)';
            const textColor = type === 'success' ? 'var(--nymia-success)' : 'var(--nymia-danger)';

            div.css({
                padding: '12px 16px',
                background: bgColor,
                border: '1px solid ' + borderColor,
                'border-radius': '8px',
                color: textColor,
                'font-size': '14px'
            }).text(message).fadeIn(300);

            setTimeout(function() {
                div.fadeOut(300);
            }, 3000);
        }

        function normalizeItems(data) {
            if (!data) {
                return [];
            }
            if (Array.isArray(data.categories)) {
                return data.categories;
            }
            if (Array.isArray(data.languages)) {
                return data.languages;
            }
            if (Array.isArray(data.items)) {
                return data.items;
            }
            return [];
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        function showConfirmationDialog(message, confirmLabel) {
            const dialog = $('#nymia-admin-dialog');
            if (!dialog.length) {
                return new Promise(function(resolve, reject) {
                    if (window.confirm(message)) {
                        resolve();
                    } else {
                        reject();
                    }
                });
            }
            
            const titleEl = dialog.find('[data-dialog-title]');
            const messageEl = dialog.find('[data-dialog-message]');
            const noteWrapper = dialog.find('[data-dialog-note-wrapper]');
            const durationWrapper = dialog.find('[data-dialog-duration-wrapper]');
            const confirmBtn = dialog.find('[data-dialog-confirm]');
            const cancelBtn = dialog.find('[data-dialog-cancel]');
            
            noteWrapper.hide();
            durationWrapper.hide();
            titleEl.text('<?php echo esc_js(__('Confirm Action', 'nymia')); ?>');
            messageEl.text(message);
            confirmBtn.text(confirmLabel || '<?php echo esc_js(__('Confirm', 'nymia')); ?>');
            
            dialog.attr('aria-hidden', 'false').removeAttr('hidden').addClass('is-visible');
            
            return new Promise(function(resolve, reject) {
                const cleanup = function() {
                    dialog.attr('aria-hidden', 'true').attr('hidden', true).removeClass('is-visible');
                    confirmBtn.off('click.nymiaDialog');
                    cancelBtn.off('click.nymiaDialog');
                    dialog.off('click.nymiaDialogBackdrop');
                };
                
                confirmBtn.on('click.nymiaDialog', function() {
                    cleanup();
                    resolve();
                });
                
                cancelBtn.on('click.nymiaDialog', function() {
                    cleanup();
                    reject();
                });
                
                dialog.on('click.nymiaDialogBackdrop', function(event) {
                    if ($(event.target).is(dialog)) {
                        cleanup();
                        reject();
                    }
                });
            });
        }

        // Audio Sub-Categories Management
        const subcategoryForm = $('#nymia-add-audio-subcategory-form');
        const subcategoryMessage = $('#nymia-audio-subcategory-message');
        const subcategoryList = $('#nymia-audio-subcategories-list');
        const subcategoryCount = $('#nymia-audio-subcategory-count');

        if (subcategoryForm.length) {
            subcategoryForm.on('submit', function(e) {
                e.preventDefault();
                
                const parentCategory = $('#nymia-audio-parent-category').val();
                const subcategoryName = $('#nymia-audio-subcategory-name').val().trim();
                const nonce = $('#nymia_subcategory_nonce').val();
                const btn = $('#nymia-add-audio-subcategory-btn');

                if (!parentCategory || !subcategoryName) {
                    showMessage(subcategoryMessage, '<?php echo esc_js(__('Please fill in all fields.', 'nymia')); ?>', 'error');
                    return;
                }

                btn.prop('disabled', true).find('.btn-text').text('<?php echo esc_js(__('Adding...', 'nymia')); ?>');
                subcategoryMessage.hide();

                $.ajax({
                    url: getAjaxUrl(),
                    type: 'POST',
                    data: {
                        action: 'nymia_add_audio_subcategory',
                        nonce: nonce,
                        parent_category: parentCategory,
                        subcategory_name: subcategoryName
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(subcategoryMessage, response.data.message || '<?php echo esc_js(__('Sub-category added successfully', 'nymia')); ?>', 'success');
                            $('#nymia-audio-subcategory-name').val('');
                            loadSubcategoriesList();
                        } else {
                            showMessage(subcategoryMessage, response.data.message || '<?php echo esc_js(__('An error occurred.', 'nymia')); ?>', 'error');
                        }
                    },
                    error: function() {
                        showMessage(subcategoryMessage, '<?php echo esc_js(__('Network error. Please try again.', 'nymia')); ?>', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).find('.btn-text').text('<?php echo esc_js(__('Add Sub-Category', 'nymia')); ?>');
                    }
                });
            });
        }

        // Delete Sub-Category Handler
        $(document).on('click', '.nymia-delete-subcategory-btn', function() {
            const btn = $(this);
            const parentCategory = btn.data('parent');
            const subcategoryName = btn.data('subcategory');
            const nonce = $('#nymia_subcategory_nonce').val();

            showConfirmationDialog(
                '<?php echo esc_js(__('Are you sure you want to delete the sub-category "%s" under "%s"?', 'nymia')); ?>'.replace('%s', subcategoryName).replace('%s', parentCategory),
                '<?php echo esc_js(__('Delete', 'nymia')); ?>'
            ).then(function() {
                btn.prop('disabled', true);

                $.ajax({
                    url: getAjaxUrl(),
                    type: 'POST',
                    data: {
                        action: 'nymia_delete_audio_subcategory',
                        nonce: nonce,
                        parent_category: parentCategory,
                        subcategory_name: subcategoryName
                    },
                    success: function(response) {
                        if (response.success) {
                            loadSubcategoriesList();
                        } else {
                            alert(response.data.message || '<?php echo esc_js(__('An error occurred.', 'nymia')); ?>');
                            btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('Network error. Please try again.', 'nymia')); ?>');
                        btn.prop('disabled', false);
                    }
                });
            }).catch(function() {
                btn.prop('disabled', false);
            });
        });

        // Load Sub-Categories List
        function loadSubcategoriesList() {
            const nonce = $('#nymia_subcategory_nonce').val();
            $.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'nymia_get_audio_subcategories',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        subcategoryList.html(response.data.html);
                        const countText = response.data.count === 1 ? 
                            '<?php echo esc_js(__('1 sub-category', 'nymia')); ?>' : 
                            '<?php echo esc_js(__('%d sub-categories', 'nymia')); ?>'.replace('%d', response.data.count);
                        subcategoryCount.text('(' + countText + ')');
                    }
                }
            });
        }

        // Ebook Sub-Categories Management
        const ebookSubcategoryForm = $('#nymia-add-ebook-subcategory-form');
        const ebookSubcategoryMessage = $('#nymia-ebook-subcategory-message');
        const ebookSubcategoryList = $('#nymia-ebook-subcategories-list');
        const ebookSubcategoryCount = $('#nymia-ebook-subcategory-count');

        if (ebookSubcategoryForm.length) {
            ebookSubcategoryForm.on('submit', function(e) {
                e.preventDefault();
                
                const parentCategory = $('#nymia-ebook-parent-category').val();
                const subcategoryName = $('#nymia-ebook-subcategory-name').val().trim();
                const nonce = $('#nymia_ebook_subcategory_nonce').val();
                const btn = $('#nymia-add-ebook-subcategory-btn');

                if (!parentCategory || !subcategoryName) {
                    showMessage(ebookSubcategoryMessage, '<?php echo esc_js(__('Please fill in all fields.', 'nymia')); ?>', 'error');
                    return;
                }

                btn.prop('disabled', true).find('.btn-text').text('<?php echo esc_js(__('Adding...', 'nymia')); ?>');
                ebookSubcategoryMessage.hide();

                $.ajax({
                    url: getAjaxUrl(),
                    type: 'POST',
                    data: {
                        action: 'nymia_add_ebook_subcategory',
                        nonce: nonce,
                        parent_category: parentCategory,
                        subcategory_name: subcategoryName
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(ebookSubcategoryMessage, response.data.message || '<?php echo esc_js(__('Sub-category added successfully', 'nymia')); ?>', 'success');
                            $('#nymia-ebook-subcategory-name').val('');
                            loadEbookSubcategoriesList();
                        } else {
                            showMessage(ebookSubcategoryMessage, response.data.message || '<?php echo esc_js(__('An error occurred.', 'nymia')); ?>', 'error');
                        }
                    },
                    error: function() {
                        showMessage(ebookSubcategoryMessage, '<?php echo esc_js(__('Network error. Please try again.', 'nymia')); ?>', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).find('.btn-text').text('<?php echo esc_js(__('Add Sub-Category', 'nymia')); ?>');
                    }
                });
            });
        }

        // Delete Ebook Sub-Category Handler
        $(document).on('click', '.nymia-delete-ebook-subcategory-btn', function() {
            const btn = $(this);
            const parentCategory = btn.data('parent');
            const subcategoryName = btn.data('subcategory');
            const nonce = $('#nymia_ebook_subcategory_nonce').val();

            showConfirmationDialog(
                '<?php echo esc_js(__('Are you sure you want to delete the sub-category "%s" under "%s"?', 'nymia')); ?>'.replace('%s', subcategoryName).replace('%s', parentCategory),
                '<?php echo esc_js(__('Delete', 'nymia')); ?>'
            ).then(function() {
                btn.prop('disabled', true);

                $.ajax({
                    url: getAjaxUrl(),
                    type: 'POST',
                    data: {
                        action: 'nymia_delete_ebook_subcategory',
                        nonce: nonce,
                        parent_category: parentCategory,
                        subcategory_name: subcategoryName
                    },
                    success: function(response) {
                        if (response.success) {
                            loadEbookSubcategoriesList();
                        } else {
                            alert(response.data.message || '<?php echo esc_js(__('An error occurred.', 'nymia')); ?>');
                            btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('Network error. Please try again.', 'nymia')); ?>');
                        btn.prop('disabled', false);
                    }
                });
            }).catch(function() {
                btn.prop('disabled', false);
            });
        });

        // Load Ebook Sub-Categories List
        function loadEbookSubcategoriesList() {
            const nonce = $('#nymia_ebook_subcategory_nonce').val();
            $.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'nymia_get_ebook_subcategories',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        ebookSubcategoryList.html(response.data.html);
                        const countText = response.data.count === 1 ? 
                            '<?php echo esc_js(__('1 sub-category', 'nymia')); ?>' : 
                            '<?php echo esc_js(__('%d sub-categories', 'nymia')); ?>'.replace('%d', response.data.count);
                        ebookSubcategoryCount.text('(' + countText + ')');
                    }
                }
            });
        }

        // Export CSV Modal Functionality
        const exportModal = $('#nymia-export-csv-modal');
        const exportTypeBtns = $('.nymia-export-type-btn');
        const exportSearch = $('#nymia-export-search');
        const exportResults = $('#nymia-export-results');
        const exportSelected = $('#nymia-export-selected');
        const exportConfirmBtn = $('#nymia-export-confirm-btn');
        const exportCsvBtn = $('#nymia-export-csv-btn');
        
        let currentExportType = 'creator';
        let selectedUsers = [];
        let searchTimeout = null;
        
        // Open export modal
        if (exportCsvBtn.length) {
            exportCsvBtn.on('click', function() {
                selectedUsers = [];
                exportSearch.val('');
                exportResults.hide().empty();
                updateSelectedUsersDisplay();
                exportModal.attr('aria-hidden', 'false').removeAttr('hidden').addClass('is-visible');
            });
        }
        
        // Close modal
        exportModal.on('click', '[data-export-cancel]', function() {
            closeExportModal();
        });
        
        exportModal.on('click', function(e) {
            if ($(e.target).is(exportModal)) {
                closeExportModal();
            }
        });
        
        function closeExportModal() {
            exportModal.attr('aria-hidden', 'true').attr('hidden', true).removeClass('is-visible');
            selectedUsers = [];
            exportSearch.val('');
            exportResults.hide().empty();
        }
        
        // User type selection
        exportTypeBtns.on('click', function() {
            const type = $(this).data('export-type');
            currentExportType = type;
            
            exportTypeBtns.removeClass('active').css({
                'background': 'rgba(255, 255, 255, 0.05)',
                'border-color': 'rgba(255, 255, 255, 0.1)',
                'color': 'rgba(255, 255, 255, 0.7)'
            });
            
            $(this).addClass('active').css({
                'background': 'rgba(191, 76, 26, 0.2)',
                'border-color': 'rgba(191, 76, 26, 0.4)',
                'color': '#fff'
            });
            
            selectedUsers = [];
            exportSearch.val('');
            exportResults.hide().empty();
            updateSelectedUsersDisplay();
        });
        
        // Search functionality
        exportSearch.on('input', function() {
            const query = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                exportResults.hide().empty();
                return;
            }
            
            searchTimeout = setTimeout(function() {
                searchUsers(query, currentExportType);
            }, 300);
        });
        
        function searchUsers(query, type) {
            exportResults.html('<div style="padding: 20px; text-align: center; color: rgba(255, 255, 255, 0.5);"><?php echo esc_js(__('Searching...', 'nymia')); ?></div>').show();
            
            $.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'nymia_search_users_for_export',
                    nonce: '<?php echo wp_create_nonce('nymia_search_users_export'); ?>',
                    query: query,
                    type: type
                },
                success: function(response) {
                    if (response.success && response.data.users) {
                        displaySearchResults(response.data.users);
                    } else {
                        exportResults.html('<div style="padding: 20px; text-align: center; color: rgba(255, 255, 255, 0.5);"><?php echo esc_js(__('No users found', 'nymia')); ?></div>');
                    }
                },
                error: function() {
                    exportResults.html('<div style="padding: 20px; text-align: center; color: rgba(248, 113, 113, 0.8);"><?php echo esc_js(__('Error searching users', 'nymia')); ?></div>');
                }
            });
        }
        
        function displaySearchResults(users) {
            if (!users || users.length === 0) {
                exportResults.html('<div style="padding: 20px; text-align: center; color: rgba(255, 255, 255, 0.5);"><?php echo esc_js(__('No users found', 'nymia')); ?></div>');
                return;
            }
            
            let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';
            users.forEach(function(user) {
                const isSelected = selectedUsers.some(function(u) { return u.id === user.id; });
                html += '<label style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; cursor: pointer; transition: background 0.2s;">' +
                    '<input type="checkbox" value="' + user.id + '" ' + (isSelected ? 'checked' : '') + ' style="width: 18px; height: 18px; cursor: pointer;" />' +
                    '<div style="flex: 1;">' +
                    '<div style="font-weight: 600; color: #fff; margin-bottom: 4px;">' + escapeHtml(user.display_name || user.username) + '</div>' +
                    '<div style="font-size: 12px; color: rgba(255, 255, 255, 0.6);">' + escapeHtml(user.email) + '</div>' +
                    '</div>' +
                    '</label>';
            });
            html += '</div>';
            
            exportResults.html(html);
            
            // Handle checkbox changes
            exportResults.find('input[type="checkbox"]').on('change', function() {
                const userId = parseInt($(this).val());
                const user = users.find(function(u) { return u.id === userId; });
                
                if ($(this).is(':checked')) {
                    if (!selectedUsers.some(function(u) { return u.id === userId; })) {
                        selectedUsers.push(user);
                    }
                } else {
                    selectedUsers = selectedUsers.filter(function(u) { return u.id !== userId; });
                }
                
                updateSelectedUsersDisplay();
            });
        }
        
        function updateSelectedUsersDisplay() {
            if (selectedUsers.length === 0) {
                exportSelected.html('<div style="color: rgba(255, 255, 255, 0.5); font-size: 13px; text-align: center; padding: 10px;"><?php echo esc_js(__('No users selected. Search and select users to export.', 'nymia')); ?></div>');
                exportConfirmBtn.prop('disabled', true);
            } else {
                let html = '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
                selectedUsers.forEach(function(user) {
                    html += '<div style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: rgba(191, 76, 26, 0.2); border: 1px solid rgba(191, 76, 26, 0.4); border-radius: 20px; font-size: 13px;">' +
                        '<span style="color: #fff;">' + escapeHtml(user.display_name || user.username) + '</span>' +
                        '<button type="button" class="nymia-remove-user-btn" data-user-id="' + user.id + '" style="background: none; border: none; color: rgba(255, 255, 255, 0.7); cursor: pointer; font-size: 16px; line-height: 1; padding: 0; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">×</button>' +
                        '</div>';
                });
                html += '</div>';
                exportSelected.html(html);
                exportConfirmBtn.prop('disabled', false);
                
                // Handle remove button
                exportSelected.find('.nymia-remove-user-btn').on('click', function() {
                    const userId = parseInt($(this).data('user-id'));
                    selectedUsers = selectedUsers.filter(function(u) { return u.id !== userId; });
                    updateSelectedUsersDisplay();
                    // Uncheck in search results
                    exportResults.find('input[type="checkbox"][value="' + userId + '"]').prop('checked', false);
                });
            }
        }
        
        // Export button click
        exportConfirmBtn.on('click', function() {
            if (selectedUsers.length === 0) {
                return;
            }
            
            const userIds = selectedUsers.map(function(u) { return u.id; });
            const exportType = currentExportType;
            
            // Create form and submit
            const form = $('<form>', {
                method: 'POST',
                action: '<?php echo esc_url(admin_url('admin-post.php')); ?>',
                style: 'display: none;'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: exportType === 'creator' ? 'nymia_export_creators_csv' : 'nymia_export_customers_csv'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_wpnonce',
                value: exportType === 'creator' ? '<?php echo wp_create_nonce('nymia_export_creators_csv'); ?>' : '<?php echo wp_create_nonce('nymia_export_customers_csv'); ?>'
            }));
            
            userIds.forEach(function(userId) {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'user_ids[]',
                    value: userId
                }));
            });
            
            $('body').append(form);
            form.submit();
            
            closeExportModal();
        });

    })(jQuery);
    </script>
    
    <?php
}

