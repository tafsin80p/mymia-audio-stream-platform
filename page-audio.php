<?php
/**
 * ========================================
 * NYMIA THEME - AUDIO LIBRARY PAGE
 * ========================================
 * Displays the audio library with:
 * - Audio creators with avatars and ratings
 * - Filter tabs (All, Trending, New Releases, Most Popular)
 * - Audio file listings for each creator
 * - Trending sidebar
 * 
 * @package Nymia
 * @version 1.0
 */

get_header(); ?>

<div class="nymia-container">
    <?php 
    // Only show sidebar for logged-in users with edit_posts or manage_options capabilities
    // Subscribers and non-logged-in users don't see the sidebar (full width)
    if (is_user_logged_in() && (current_user_can('edit_posts') || current_user_can('manage_options'))) {
        get_sidebar();
    }
    ?>
    
    <div class="nymia-main<?php echo (!is_user_logged_in() || (!current_user_can('edit_posts') && !current_user_can('manage_options'))) ? ' nymia-main-fullwidth' : ''; ?>">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <!-- ======================================== -->
        <!-- AUDIO PAGE CONTAINER -->
        <!-- ======================================== -->
        <div class="nymia-audio-page">
            <?php if (isset($_GET['restricted'])): ?>
                <div class="nymia-audio-notice">
                    <strong><?php esc_html_e('Premium Audio Locked', 'nymia'); ?></strong>
                    <p><?php esc_html_e('That creator’s track requires a purchase before listening. Explore other tracks or upgrade your plan.', 'nymia'); ?></p>
                </div>
            <?php endif; ?>
            <!-- ======================================== -->
            <!-- AUDIO HEADER SECTION -->
            <!-- ======================================== -->
            <div class="nymia-audio-header">
                <div class="nymia-audio-header-content">
                    <h1 class="nymia-audio-main-title">
                        <span class="nymia-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18V5l12-2v13"></path>
                                <circle cx="6" cy="18" r="3"></circle>
                                <circle cx="18" cy="16" r="3"></circle>
                            </svg>
                        </span>
                        Audio Library
                    </h1>
                    <p class="nymia-audio-subtitle">Discover and enjoy premium audio content</p>
                </div>
                
                <!-- Filter Tabs -->
                <div class="nymia-filter-tabs">
                    <button class="nymia-filter-tab active" data-filter="all">
                        <span>All</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="trending">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                        <span>Trending</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="new">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>New Releases</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="popular">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Most Popular</span>
                    </button>
                </div>
            </div>

            <!-- ======================================== -->
            <!-- AUDIO CONTENT LAYOUT -->
            <!-- ======================================== -->
            <div class="nymia-audio-layout">
                <!-- ======================================== -->
                <!-- AUDIO CREATORS GRID -->
                <!-- ======================================== -->
                <div class="nymia-grid nymia-grid-4">
            <?php 
            // ========================================
            // GET ALL CREATORS WITH AUDIO
            // ========================================
            // Dynamically fetch all creators who have uploaded audio files
            $creators_data = nymia_get_all_creators_with_audio();
            // Filter creators to only show users with Author or Administrator roles
            if (!empty($creators_data)) {
                $creators_data = array_values(array_filter($creators_data, function($creator){
                    $uid = isset($creator['user_id']) ? intval($creator['user_id']) : 0;
                    if (!$uid) return false;
                    return user_can($uid, 'administrator') || user_can($uid, 'author');
                }));
            }
            
            // If no creators found, show empty state
            if (empty($creators_data)):
            ?>
                <div class="nymia-empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 64px; height: 64px; margin: 0 auto 20px; opacity: 0.5;">
                        <path d="M9 18V5l12-2v13"></path>
                        <circle cx="6" cy="18" r="3"></circle>
                        <circle cx="18" cy="16" r="3"></circle>
                    </svg>
                    <h3 style="font-size: 1.5rem; margin-bottom: 12px; color: var(--foreground);">No Audio Content Yet</h3>
                    <p style="color: var(--muted-foreground); margin-bottom: 24px;">Be the first to upload audio content and share it with the community!</p>
                    <?php if (is_user_logged_in()): ?>
                        <a href="<?php echo esc_url(home_url('/create?tab=audio')); ?>" class="nymia-btn-continue" style="display: inline-flex; align-items: center; gap: 8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Upload Audio
                        </a>
                    <?php endif; ?>
                </div>
            <?php 
            else:
                foreach ($creators_data as $creator): 
                    $creator_has_access = nymia_can_user_access_paid_audio($creator['user_id']);
            ?>
                <div class="nymia-content-card nymia-creator-card">
                    <div class="nymia-card-image aspect-portrait">
                        <img src="<?php echo esc_url($creator['image']); ?>" alt="<?php echo esc_attr($creator['name']); ?>" />
                        <div class="nymia-card-overlay nymia-creator-overlay">
                            <div class="nymia-creator-info">
                                    <div class="nymia-avatar-shell <?php echo nymia_is_creator_verified($creator['user_id']) ? 'has-creator-badge' : ''; ?>">
                                <img src="<?php echo esc_url($creator['avatar']); ?>" alt="<?php echo esc_attr($creator['name']); ?>" class="nymia-creator-avatar" />
                                    </div>
                                <div class="nymia-creator-details">
                                    <div class="nymia-creator-heading">
                                    <span class="nymia-creator-name"><?php echo esc_html($creator['name']); ?></span>
                                        <?php echo wp_kses_post(nymia_get_user_badge_markup($creator['user_id'], null, 'nymia-creator-badge--inline')); ?>
                                    </div>
                                    <div class="nymia-creator-rating">
                                        <div class="nymia-star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg class="nymia-star <?php echo $i <= floor($creator['rating']) ? 'filled' : ''; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="nymia-rating-score">(<?php echo $creator['rating']; ?>)</span>
                                    </div>
                                    <div class="nymia-creator-category">
                                        <span class="nymia-category-tag"><?php echo esc_html($creator['category']); ?></span>
                                        <span class="nymia-subcategory-tag"><?php echo esc_html($creator['subcategory']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="nymia-creator-views">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                </svg>
                                <span><?php echo esc_html($creator['views']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Audio Files Section -->
                    <div class="nymia-audio-files">
                        <h4 class="nymia-audio-files-title">Audio Files</h4>
                        <?php foreach (array_slice($creator['audio_files'], 0, 3) as $audio): 
                            $audio_is_paid = !empty($audio['paid_access']) && $audio['paid_access'] === 'yes' && !empty($audio['price']);
                            // Get audio ID - try multiple sources
                            $audio_id = '';
                            if (isset($audio['id']) && !empty($audio['id'])) {
                                $audio_id = (string)$audio['id'];
                            } elseif (isset($audio['post_id']) && !empty($audio['post_id'])) {
                                $audio_id = (string)$audio['post_id'];
                            } elseif (!empty($audio['url'])) {
                                // Try to extract ID from URL or use URL as fallback identifier
                                // For now, we'll need to search for the audio by URL in the transient
                                $all_audio = get_transient('nymia_all_audio');
                                if ($all_audio && is_array($all_audio)) {
                                    foreach ($all_audio as $audio_item) {
                                        if (isset($audio_item['url']) && $audio_item['url'] === $audio['url']) {
                                            if (isset($audio_item['id']) && !empty($audio_item['id'])) {
                                                $audio_id = (string)$audio_item['id'];
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        ?>
                            <div class="nymia-audio-file-card" data-audio-url="<?php echo esc_attr(!empty($audio['url']) ? $audio['url'] : ''); ?>" data-paid="<?php echo esc_attr(isset($audio['paid_access']) ? $audio['paid_access'] : 'no'); ?>" data-price="<?php echo esc_attr(isset($audio['price']) ? $audio['price'] : 0); ?>" data-creator-id="<?php echo esc_attr($creator['user_id']); ?>" data-title="<?php echo esc_attr($audio['title']); ?>" data-review-count="<?php echo esc_attr(isset($audio['review_count']) ? intval($audio['review_count']) : 0); ?>" data-rating="<?php echo esc_attr(isset($audio['rating']) ? floatval($audio['rating']) : 0); ?>" data-audio-id="<?php echo esc_attr($audio_id); ?>">
                                <div class="nymia-audio-cover">
                                    <?php if (!empty($audio['cover_image'])): ?>
                                        <img src="<?php echo esc_url($audio['cover_image']); ?>" alt="<?php echo esc_attr($audio['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;" />
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 18V5l12-2v13"></path>
                                            <circle cx="6" cy="18" r="3"></circle>
                                            <circle cx="18" cy="16" r="3"></circle>
                                        </svg>
                                    <?php endif; ?>
                                    <button class="nymia-play-button">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="nymia-audio-info">
                                    <h5 class="nymia-audio-title"><?php echo esc_html($audio['title']); ?></h5>
                                    <div class="nymia-audio-rating">
                                        <div class="nymia-star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg class="nymia-star <?php echo $i <= floor($audio['rating']) ? 'filled' : ''; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="nymia-rating-score">(<?php echo esc_html(number_format((float) ($audio['rating'] ?? 0), 1)); ?>)</span>
                                        <?php
                                        $audio_review_count = isset($audio['review_count']) ? intval($audio['review_count']) : 0;
                                        $audio_review_label = sprintf(_n('%d review', '%d reviews', $audio_review_count, 'nymia'), $audio_review_count);
                                        ?>
                                        <span class="nymia-review-count"><?php echo esc_html($audio_review_label); ?></span>
                                    </div>
                                    <div class="nymia-audio-meta">
                                        <span class="nymia-audio-duration"><?php echo esc_html($audio['duration']); ?></span>
                                        <span class="nymia-audio-category"><?php echo esc_html($creator['category']); ?><?php echo !empty($creator['subcategory']) ? ' - ' . esc_html($creator['subcategory']) : ''; ?></span>
                                        <?php if ($audio_is_paid): ?>
                                            <span class="nymia-audio-price-badge"><?php echo esc_html('$' . number_format($audio['price'], 2)); ?></span>
                                            <?php if (!$creator_has_access): ?>
                                                <button type="button" class="nymia-add-to-cart-btn" data-item-type="audio" data-item-id="<?php echo esc_attr($audio_id); ?>" style="background: linear-gradient(135deg, #BF4C1A, #9F2B1A); border: none; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s ease;">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                                                        <circle cx="9" cy="21" r="1"></circle>
                                                        <circle cx="20" cy="21" r="1"></circle>
                                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                                    </svg>
                                                    <span><?php esc_html_e('Add to Cart', 'nymia'); ?></span>
                                                </button>
                                            <?php else: ?>
                                                <span class="nymia-audio-unlocked"><?php esc_html_e('Unlocked', 'nymia'); ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                endforeach; 
            endif; 
            ?>
        </div>

                <!-- ======================================== -->
                <!-- TRENDING SIDEBAR -->
                <!-- ======================================== -->
                <aside class="nymia-trending-sidebar">
                    <h2 class="nymia-trending-title">Trending Now</h2>
                    
                    <div class="nymia-trending-list">
                        <?php 
                        // Get all audio files from all creators
                        $all_audio_for_trending = array();
                        
                        // Only process if we have creators
                        if (!empty($creators_data)) {
                            foreach ($creators_data as $creator) {
                                if (!empty($creator['audio_files'])) {
                                    foreach ($creator['audio_files'] as $audio) {
                                        $all_audio_for_trending[] = array(
                                            'title' => $audio['title'],
                                            'artist' => $creator['name'],
                                            'plays' => isset($audio['views']) ? intval($audio['views']) : 0,
                                            'image' => !empty($audio['cover_image']) ? $audio['cover_image'] : $creator['avatar'],
                                            'url' => isset($audio['url']) ? $audio['url'] : '',
                                            'paid_access' => isset($audio['paid_access']) ? $audio['paid_access'] : 'no',
                                            'price' => isset($audio['price']) ? $audio['price'] : 0,
                                            'rating' => isset($audio['rating']) ? floatval($audio['rating']) : 0,
                                            'review_count' => isset($audio['review_count']) ? intval($audio['review_count']) : 0,
                                            'creator_id' => isset($audio['user_id']) ? intval($audio['user_id']) : $creator['user_id'],
                                            'id' => isset($audio['id']) ? (string)$audio['id'] : ''
                                        );
                                    }
                                }
                            }
                            
                            // Sort by plays (most viewed first)
                            usort($all_audio_for_trending, function($a, $b) {
                                return $b['plays'] <=> $a['plays'];
                            });
                            
                            // Get top 5 trending
                            $trending_posts = array_slice($all_audio_for_trending, 0, 5);
                        } else {
                            $trending_posts = array();
                        }
                        
                        if (empty($trending_posts)):
                        ?>
                            <div class="nymia-empty-state" style="text-align: center; padding: 40px 20px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5;">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                                <p style="color: var(--muted-foreground); font-size: 0.875rem;">No trending audio yet</p>
                            </div>
                        <?php
                        else:
                            $rank = 1;
                            foreach ($trending_posts as $trending): 
                        ?>
                        <?php $trending_access = nymia_can_user_access_paid_audio(isset($trending['creator_id']) ? $trending['creator_id'] : 0); ?>
                        <div class="nymia-trending-item" data-audio-url="<?php echo esc_attr(!empty($trending['url']) ? $trending['url'] : ''); ?>" data-paid="<?php echo esc_attr(isset($trending['paid_access']) ? $trending['paid_access'] : 'no'); ?>" data-price="<?php echo esc_attr(isset($trending['price']) ? $trending['price'] : 0); ?>" data-creator-id="<?php echo esc_attr(isset($trending['creator_id']) ? $trending['creator_id'] : 0); ?>" data-title="<?php echo esc_attr($trending['title']); ?>">
                            <span class="nymia-trending-rank"><?php echo $rank++; ?></span>
                            
                            <div class="nymia-trending-cover">
                                <img src="<?php echo esc_url($trending['image']); ?>" alt="<?php echo esc_attr($trending['title']); ?>" />
                                <button class="nymia-trending-play">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="nymia-trending-info">
                                <h4 class="nymia-trending-song"><?php echo esc_html($trending['title']); ?></h4>
                                <div class="nymia-trending-artist">
                                    <span class="nymia-trending-artist-name"><?php echo esc_html($trending['artist']); ?></span>
                                    <?php echo wp_kses_post(nymia_get_user_badge_markup(isset($trending['creator_id']) ? intval($trending['creator_id']) : 0, null, 'nymia-creator-badge--inline')); ?>
                                </div>
                                <span class="nymia-trending-plays"><?php 
                                    $plays = $trending['plays'];
                                    $plays_formatted = $plays >= 1000 ? round($plays / 1000, 1) . 'k' : (string)$plays;
                                    echo esc_html($plays_formatted); 
                                ?> plays</span>
                                <div class="nymia-trending-rating">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                    <?php
                                    $trend_rating = isset($trending['rating']) ? floatval($trending['rating']) : 0;
                                    $trend_reviews = isset($trending['review_count']) ? intval($trending['review_count']) : 0;
                                    $trend_review_label = sprintf(_n('%d review', '%d reviews', $trend_reviews, 'nymia'), $trend_reviews);
                                    ?>
                                    <span class="nymia-trending-rating-value"><?php echo $trend_rating > 0 ? esc_html(number_format($trend_rating, 1)) : '—'; ?></span>
                                    <span class="nymia-trending-review-count"><?php echo esc_html($trend_review_label); ?></span>
                                </div>
                                <?php if (!empty($trending['paid_access']) && $trending['paid_access'] === 'yes' && !empty($trending['price'])): ?>
                                    <span class="nymia-trending-price"><?php echo esc_html('$' . number_format($trending['price'], 2)); ?></span>
                                    <?php if (!$trending_access): ?>
                                        <?php $trending_audio_id = isset($trending['id']) ? (string)$trending['id'] : ''; ?>
                                        <button type="button" class="nymia-add-to-cart-btn" data-item-type="audio" data-item-id="<?php echo esc_attr($trending_audio_id); ?>" style="background: linear-gradient(135deg, #BF4C1A, #9F2B1A); border: none; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s ease; margin-left: 12px;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                                                <circle cx="9" cy="21" r="1"></circle>
                                                <circle cx="20" cy="21" r="1"></circle>
                                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                            </svg>
                                            <span><?php esc_html_e('Add to Cart', 'nymia'); ?></span>
                                        </button>
                                    <?php else: ?>
                                        <span class="nymia-audio-unlocked"><?php esc_html_e('Unlocked', 'nymia'); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>

<script>
// Add to Cart Handler for Audio Page
(function() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.nymia-add-to-cart-btn');
        if (!btn) return;
        
        e.preventDefault();
        const itemType = btn.getAttribute('data-item-type');
        const itemId = btn.getAttribute('data-item-id');
        
        if (!itemType || !itemId) {
            console.error('Missing item type or ID:', { itemType, itemId, button: btn });
            alert('<?php echo esc_js(__('Error: Missing item information. Please refresh the page and try again.', 'nymia')); ?>');
            return;
        }
        
        // Disable button
        const originalText = btn.querySelector('span') ? btn.querySelector('span').textContent : btn.textContent;
        btn.disabled = true;
        btn.style.opacity = '0.6';
        if (btn.querySelector('span')) {
            btn.querySelector('span').textContent = '<?php echo esc_js(__('Adding...', 'nymia')); ?>';
        } else {
            btn.textContent = '<?php echo esc_js(__('Adding...', 'nymia')); ?>';
        }
        
        // Debug logging
        console.log('Adding to cart:', { itemType, itemId });
        
        // AJAX request
        const formData = new FormData();
        formData.append('action', 'nymia_add_to_cart');
        formData.append('item_type', itemType);
        formData.append('item_id', itemId);
        formData.append('nonce', '<?php echo wp_create_nonce('nymia_cart'); ?>');
        
        const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Server response (not OK):', text);
                    try {
                        const json = JSON.parse(text);
                        if (json.data && json.data.message) {
                            throw new Error(json.data.message);
                        }
                        throw new Error('Server error: ' + response.status);
                    } catch (parseError) {
                        if (text.includes('<html') || text.includes('<!DOCTYPE')) {
                            throw new Error('Server returned an error page. Status: ' + response.status + '. Please check if you are logged in and try again.');
                        }
                        throw new Error('Network response was not ok. Status: ' + response.status);
                    }
                });
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Unexpected content type:', contentType);
                    throw new Error('Server returned unexpected response format. Please refresh the page and try again.');
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Add to cart response:', data);
            if (data.success) {
                // Show success message
                const message = document.createElement('div');
                message.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #4CAF50; color: #fff; padding: 16px 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 10000; display: flex; align-items: center; gap: 12px; font-weight: 600;';
                message.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><polyline points="20 6 9 17 4 12"></polyline></svg><span>' + (data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Item added to cart!', 'nymia')); ?>') + '</span>';
                document.body.appendChild(message);
                
                setTimeout(() => {
                    message.style.transition = 'opacity 0.3s, transform 0.3s';
                    message.style.opacity = '0';
                    message.style.transform = 'translateX(20px)';
                    setTimeout(() => message.remove(), 300);
                }, 2000);
                
                // Update cart dropdown instead of redirecting
                refreshCartDropdown(data.data);
                
                // Open cart dropdown to show the new item
                const cartDropdown = document.getElementById('nymiaCartDropdown');
                const cartBtn = document.getElementById('nymiaCartBtn');
                if (cartDropdown && cartBtn) {
                    cartDropdown.classList.add('active');
                }
            } else {
                const errorMessage = data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Failed to add item to cart.', 'nymia')); ?>';
                console.error('Add to Cart failed:', errorMessage, data);
                alert(errorMessage);
                btn.disabled = false;
                btn.style.opacity = '1';
                if (btn.querySelector('span')) {
                    btn.querySelector('span').textContent = originalText;
                } else {
                    btn.textContent = originalText;
                }
            }
        })
        .catch(error => {
            console.error('Add to Cart fetch error:', error);
            alert('<?php echo esc_js(__('An error occurred.', 'nymia')); ?> ' + error.message + '. <?php echo esc_js(__('Please try again.', 'nymia')); ?>');
            btn.disabled = false;
            btn.style.opacity = '1';
            if (btn.querySelector('span')) {
                btn.querySelector('span').textContent = originalText;
            } else {
                btn.textContent = originalText;
            }
        });
    });
    
    // Function to refresh cart dropdown
    function refreshCartDropdown(cartData) {
        if (!cartData) return;
        
        const cartBody = document.getElementById('nymiaCartBody');
        const cartCountSpan = document.getElementById('nymiaCartCount');
        const cartTotalAmount = document.querySelector('.nymia-cart-total-amount');
        const cartFooter = document.querySelector('.nymia-cart-footer');
        const cartPage = <?php 
            $cart_page_obj = get_page_by_path('cart');
            echo $cart_page_obj ? 'true' : 'false';
        ?>;
        const cartLink = '<?php 
            $cart_page_obj = get_page_by_path('cart');
            echo esc_url($cart_page_obj ? get_permalink($cart_page_obj) : home_url('/cart/'));
        ?>';
        const currency = '<?php echo esc_js(strtoupper(get_option('nymia_stripe_currency', 'USD'))); ?>';
        
        // Update cart count badge
        if (cartCountSpan) {
            const count = cartData.cart_count || 0;
            if (count > 0) {
                cartCountSpan.textContent = count > 99 ? '99+' : count;
                cartCountSpan.style.display = 'flex';
            } else {
                cartCountSpan.style.display = 'none';
            }
        } else if (cartData.cart_count > 0) {
            // Create cart count badge if it doesn't exist
            const cartBtn = document.getElementById('nymiaCartBtn');
            if (cartBtn) {
                const countSpan = document.createElement('span');
                countSpan.id = 'nymiaCartCount';
                countSpan.className = 'nymia-cart-count';
                countSpan.textContent = cartData.cart_count > 99 ? '99+' : cartData.cart_count;
                cartBtn.appendChild(countSpan);
            }
        }
        
        // Update cart items
        if (cartBody && cartData.cart && Array.isArray(cartData.cart)) {
            if (cartData.cart.length === 0) {
                cartBody.innerHTML = '<p class="nymia-cart-empty-message"><?php esc_html_e('Your cart is empty.', 'nymia'); ?></p>';
                if (cartFooter) cartFooter.style.display = 'none';
            } else {
                let cartHTML = '';
                cartData.cart.forEach(function(item) {
                    const itemTitle = item.title || 'Unknown Item';
                    const itemAuthor = item.author || 'Unknown Author';
                    const itemPrice = parseFloat(item.price || 0);
                    const itemImage = item.image || '';
                    const itemType = item.type || '';
                    const itemId = item.id || '';
                    const formattedPrice = currency === 'USD' ? '$' + itemPrice.toFixed(2) : itemPrice.toFixed(2) + ' ' + currency;
                    
                    cartHTML += '<div class="nymia-cart-item" data-item-type="' + itemType + '" data-item-id="' + itemId + '">';
                    if (itemImage) {
                        cartHTML += '<img src="' + itemImage + '" alt="' + itemTitle + '" class="nymia-cart-item-image">';
                    } else {
                        cartHTML += '<div class="nymia-cart-item-image-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></div>';
                    }
                    cartHTML += '<div class="nymia-cart-item-details">';
                    cartHTML += '<h4 class="nymia-cart-item-title">' + itemTitle + '</h4>';
                    cartHTML += '<p class="nymia-cart-item-author">' + itemAuthor + '</p>';
                    cartHTML += '<div class="nymia-cart-item-price">' + formattedPrice + '</div>';
                    cartHTML += '</div>';
                    cartHTML += '<button type="button" class="nymia-cart-item-remove" data-item-type="' + itemType + '" data-item-id="' + itemId + '" aria-label="<?php esc_attr_e('Remove item', 'nymia'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>';
                    cartHTML += '</div>';
                });
                cartBody.innerHTML = cartHTML;
                
                // Update total
                if (cartTotalAmount) {
                    const total = parseFloat(cartData.cart_total || 0);
                    const formattedTotal = currency === 'USD' ? '$' + total.toFixed(2) : total.toFixed(2) + ' ' + currency;
                    cartTotalAmount.textContent = formattedTotal;
                }
                
                // Show footer
                if (cartFooter) {
                    cartFooter.style.display = 'block';
                    const checkoutBtn = cartFooter.querySelector('.nymia-cart-checkout-btn');
                    if (checkoutBtn) {
                        checkoutBtn.href = cartLink;
                    }
                }
            }
        }
    }
})();
</script>

<?php get_footer(); ?>

<style>
.nymia-audio-price-badge,
.nymia-trending-price {
    margin-right: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    background: rgba(230, 116, 68, 0.12);
    color: #e67444;
    border: 1px solid rgba(230, 116, 68, 0.25);
    margin-left: 8px;
}

.nymia-review-count {
    margin-left: 8px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
}

.nymia-trending-rating {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.75);
    margin-top: 6px;
}

.nymia-trending-rating svg {
    width: 16px;
    height: 16px;
    color: #fbbf24;
}

.nymia-trending-rating-value {
    font-weight: 600;
}

.nymia-trending-review-count {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.6);
}

.nymia-trending-item[data-paid="yes"] .nymia-trending-cover,
.nymia-audio-file-card[data-paid="yes"] .nymia-audio-cover {
    position: relative;
}

.nymia-audio-file-card[data-paid="yes"] .nymia-play-button::after,
.nymia-trending-item[data-paid="yes"] .nymia-trending-play::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 1px solid rgba(230, 116, 68, 0.35);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.nymia-audio-access-btn {
    margin-left: 8px;
    padding: 4px 12px;
    border-radius: 999px;
    border: none;
    background: #e67444;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.nymia-audio-access-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(230, 116, 68, 0.35);
}

.nymia-audio-unlocked {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: #34d399;
    background: rgba(52, 211, 153, 0.16);
    border: 1px solid rgba(52, 211, 153, 0.32);
    margin-left: 8px;
}

.nymia-audio-file-card[data-paid="yes"] .nymia-play-button:hover::after,
.nymia-trending-item[data-paid="yes"] .nymia-trending-play:hover::after {
    opacity: 1;
}

.nymia-audio-notice {
    margin-bottom: 24px;
    padding: 18px 20px;
    border-radius: 12px;
    border: 1px solid rgba(230, 116, 68, 0.3);
    background: rgba(230, 116, 68, 0.12);
    color: #e67444;
}

.nymia-audio-notice p {
    margin: 8px 0 0;
    color: rgba(255, 255, 255, 0.85);
    font-size: 14px;
}
</style>