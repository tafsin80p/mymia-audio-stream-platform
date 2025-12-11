<?php
/**
 * ========================================
 * NYMIA THEME - FILTERS COMPONENT
 * ========================================
 * Reusable filter pills component for navigation
 * 
 * @package Nymia
 * @version 1.0
 */

$filter_buttons = array('All', 'Live Audio', 'E-Books', 'Audio Book', 'Audio Creator', 'Online Now', 'Secret Room');
?>

<!-- ======================================== -->
<!-- FILTER PILLS -->
<!-- ======================================== -->
<div class="nymia-filters">
    <?php foreach ($filter_buttons as $filter): ?>
        <?php if ($filter === 'E-Books') : ?>
            <?php 
            $ebook_page = get_page_by_path('ebook');
            $ebook_link = $ebook_page ? get_permalink($ebook_page) : site_url('/ebook/');
            ?>
            <a class="nymia-filter-btn" href="<?php echo esc_url($ebook_link); ?>">
                <?php echo esc_html($filter); ?>
            </a>
        <?php elseif ($filter === 'Audio Creator') : ?>
            <?php 
            $audio_page = get_page_by_path('audio');
            $audio_link = $audio_page ? get_permalink($audio_page) : site_url('/audio/');
            ?>
            <a class="nymia-filter-btn" href="<?php echo esc_url($audio_link); ?>">
                <?php echo esc_html($filter); ?>
            </a>
        <?php elseif ($filter === 'Audio Book') : ?>
            <?php 
            $audiobook_page = get_page_by_path('audiobook');
            $audiobook_link = $audiobook_page ? get_permalink($audiobook_page) : site_url('/audiobook/');
            ?>
            <a class="nymia-filter-btn" href="<?php echo esc_url($audiobook_link); ?>">
                <?php echo esc_html($filter); ?>
            </a>
        <?php elseif ($filter === 'Live Audio') : ?>
            <?php 
            $live_streams_page = get_page_by_path('live-streams');
            $live_streams_link = $live_streams_page ? get_permalink($live_streams_page) : site_url('/live-streams/');
            ?>
            <a class="nymia-filter-btn" href="<?php echo esc_url($live_streams_link); ?>">
                <?php echo esc_html($filter); ?>
            </a>
        <?php elseif ($filter === 'Online Now') : ?>
            <?php 
            $online_now_page = get_page_by_path('online-now');
            $online_now_link = $online_now_page ? get_permalink($online_now_page) : site_url('/online-now/');
            ?>
            <a class="nymia-filter-btn" href="<?php echo esc_url($online_now_link); ?>">
                <?php echo esc_html($filter); ?>
            </a>
        <?php elseif ($filter === 'Secret Room') : ?>
            <button class="nymia-filter-btn" data-filter="secret-room" id="nymia-secret-room-filter-btn">
                <?php echo esc_html($filter); ?>
            </button>
        <?php else: ?>
            <button class="nymia-filter-btn <?php echo $filter === 'All' ? 'active' : ''; ?>" data-filter="<?php echo esc_attr(strtolower(str_replace(' ', '-', $filter))); ?>">
                <?php echo esc_html($filter); ?>
            </button>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

