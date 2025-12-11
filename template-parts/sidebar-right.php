<?php
/**
 * ========================================
 * NYMIA THEME - RIGHT SIDEBAR TEMPLATE
 * ========================================
 * Displays suggestions for users to follow
 * Shows real WordPress users with their profiles
 * 
 * @package Nymia
 * @version 1.0
 */

// GET: Dashboard data including suggestions
$dashboard_data = nymia_get_dashboard_data();

// GET: Current user to check follow status
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
?>

<!-- ======================================== -->
<!-- RIGHT SIDEBAR - USER SUGGESTIONS -->
<!-- ======================================== -->
<aside class="nymia-sidebar-right">
    <h3>Suggestions</h3>
    
    <?php if (empty($dashboard_data['suggestions'])): ?>
        <p style="color: rgba(255, 255, 255, 0.6); text-align: center; padding: 20px; font-size: 0.875rem;">
            <?php esc_html_e('No suggestions available at this time.', 'nymia'); ?>
        </p>
    <?php else: ?>
        <div class="nymia-suggestions-list">
        <?php foreach ($dashboard_data['suggestions'] as $suggestion): ?>
            <?php if ($suggestion['id'] > 0): ?>
        <!-- REAL USER SUGGESTION -->
        <?php 
        // CHECK: If current user is following this user (only for logged-in users)
        $is_following = is_user_logged_in() ? nymia_is_following($current_user_id, $suggestion['id']) : false;
        $follow_status = $is_following ? 'Following' : 'Follow';
        $follow_class = $is_following ? 'following' : '';
        ?>
        <a href="<?php echo nymia_get_user_profile_url($suggestion['id']); ?>" class="nymia-suggestion-card">
            <div class="nymia-suggestion-image">
                <img src="<?php echo esc_url($suggestion['image']); ?>" alt="<?php echo esc_attr($suggestion['name']); ?>" />
                <div class="nymia-suggestion-overlay"></div>
            </div>
            
            <div class="nymia-suggestion-content">
                <div class="nymia-suggestion-info">
                    <img src="<?php echo esc_url($suggestion['avatar']); ?>" alt="<?php echo esc_attr($suggestion['name']); ?>" class="nymia-avatar" />
                    <div>
                        <p><?php echo esc_html($suggestion['username']); ?></p>
                        <?php if (isset($suggestion['rating']) && $suggestion['rating'] > 0): ?>
                            <div class="nymia-suggestion-rating" style="display: flex; align-items: center; gap: 4px; margin-top: 4px;">
                                <div class="nymia-star-rating" style="display: flex; gap: 2px;">
                                    <?php 
                                    $rating = floatval($suggestion['rating']);
                                    for ($i = 1; $i <= 5; $i++): 
                                        $filled = $i <= floor($rating) || ($i - 0.5 <= $rating && $i > floor($rating));
                                    ?>
                                        <svg class="nymia-star <?php echo $filled ? 'filled' : ''; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px; color: <?php echo $filled ? '#FFC107' : 'rgba(255,255,255,0.3)'; ?>;">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <span style="font-size: 0.75rem; color: rgba(255,255,255,0.7);"><?php echo esc_html(number_format($rating, 1)); ?></span>
                                <?php if (isset($suggestion['rating_count']) && $suggestion['rating_count'] > 0): ?>
                                    <span style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">(<?php echo esc_html($suggestion['rating_count']); ?>)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (is_user_logged_in()): ?>
                <button class="nymia-suggestion-btn <?php echo esc_attr($follow_class); ?>" 
                        data-user-id="<?php echo esc_attr($suggestion['id']); ?>" 
                        onclick="event.preventDefault(); event.stopPropagation(); nymiaToggleFollow(this, <?php echo esc_attr($suggestion['id']); ?>); return false;">
                    <?php echo esc_html($follow_status); ?>
                </button>
                <?php endif; ?>
            </div>
        </a>
        <?php else: ?>
        <!-- PLACEHOLDER SUGGESTION -->
        <div class="nymia-suggestion-card">
            <div class="nymia-suggestion-image">
                <img src="<?php echo esc_url($suggestion['image']); ?>" alt="<?php echo esc_attr($suggestion['name']); ?>" />
                <div class="nymia-suggestion-overlay"></div>
            </div>
            
            <div class="nymia-suggestion-content">
                <div class="nymia-suggestion-info">
                    <img src="<?php echo esc_url($suggestion['avatar']); ?>" alt="<?php echo esc_attr($suggestion['name']); ?>" class="nymia-avatar" />
                    <div>
                        <p><?php echo esc_html($suggestion['username']); ?></p>
                    </div>
                </div>
                <button class="nymia-suggestion-btn" disabled><?php esc_html_e('Coming Soon', 'nymia'); ?></button>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
        </div>
    <?php endif; ?>
</aside>
