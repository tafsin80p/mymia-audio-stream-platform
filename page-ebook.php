<?php
/**
 * Template Name: Ebook
 * 
 * Ebook Library Page Template
 */

get_header(); 
?>

<div class="nymia-container">
    <?php 
    // Only show sidebar for logged-in users with appropriate capabilities
    if (is_user_logged_in() && (current_user_can('edit_posts') || current_user_can('manage_options'))) {
        get_sidebar(); 
    }
    ?>
    
    <main class="nymia-main<?php echo (!is_user_logged_in() || (!current_user_can('edit_posts') && !current_user_can('manage_options'))) ? ' nymia-main-fullwidth' : ''; ?>">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-ebook-page">
            <!-- Ebook Header -->
            <div class="nymia-ebook-header">
                <div class="nymia-ebook-header-content">
                    <h1 class="nymia-ebook-main-title">
                        <div class="nymia-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        Ebook Library
                    </h1>
                    <p class="nymia-ebook-subtitle">Browse and read premium Ebook content</p>
                </div>

                <!-- Filter Tabs -->
                <div class="nymia-filter-tabs">
					<?php 
					// Build category list once for tabs
					$__ebooks_for_tabs = function_exists('nymia_get_all_ebooks') ? nymia_get_all_ebooks() : array();
					$__category_names = array();
					foreach ($__ebooks_for_tabs as $__ebook_for_tab) {
						$__cat = !empty($__ebook_for_tab['category']) ? trim($__ebook_for_tab['category']) : '';
						if ($__cat !== '') { $__category_names[$__cat] = true; }
					}
					$__category_names = array_keys($__category_names);
					sort($__category_names);
					?>
                    <button class="nymia-filter-tab active" data-filter="all">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <span>All</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="recent">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Recent</span>
                    </button>
                    <button class="nymia-filter-tab" data-filter="popular">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Popular</span>
                    </button>
					<?php if (!empty($__category_names)): ?>
					<button class="nymia-filter-tab" data-filter="categories">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<rect x="3" y="3" width="7" height="7"></rect>
							<rect x="14" y="3" width="7" height="7"></rect>
							<rect x="14" y="14" width="7" height="7"></rect>
							<rect x="3" y="14" width="7" height="7"></rect>
						</svg>
						<span>Categories</span>
					</button>
					<?php foreach ($__category_names as $__cat_name):
						$__cat_slug = strtolower(str_replace(' ', '-', $__cat_name));
					?>
						<button class="nymia-filter-tab" data-filter="<?php echo esc_attr($__cat_slug); ?>">
							<span><?php echo esc_html($__cat_name); ?></span>
						</button>
					<?php endforeach; ?>
					<?php endif; ?>
                </div>
            </div>

            <!-- Content Layout -->
            <div class="nymia-ebook-layout">
                <!-- Main Grid Content -->
                <div class="nymia-ebook-categories">
                    <?php 
                    // Get all ebooks from the upload system
                    $ebook_posts = nymia_get_all_ebooks();
                    
                    if (empty($ebook_posts)): ?>
                        <div class="nymia-ebook-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <h3>No Ebooks Available</h3>
                            <p>Start creating ebooks to see them here!</p>
                        </div>
                    <?php else:
                        // Group ebooks by category
                        $ebooks_by_category = array();
                        $uncategorized = array();
                        
                        foreach ($ebook_posts as $ebook) {
                            $category = !empty($ebook['category']) ? $ebook['category'] : 'Uncategorized';
                            if ($category === 'Uncategorized' || empty($category)) {
                                $uncategorized[] = $ebook;
                            } else {
                                if (!isset($ebooks_by_category[$category])) {
                                    $ebooks_by_category[$category] = array();
                                }
                                $ebooks_by_category[$category][] = $ebook;
                            }
                        }
                        
                        // If there are uncategorized ebooks, add them
                        if (!empty($uncategorized)) {
                            $ebooks_by_category['Uncategorized'] = $uncategorized;
                        }
                        
                        // Display each category section
                        foreach ($ebooks_by_category as $category_name => $category_ebooks):
                            $filter_category = strtolower(str_replace(' ', '-', $category_name));
                    ?>
                    <div class="nymia-ebook-category-section" data-category-section="<?php echo esc_attr($filter_category); ?>">
                        <div class="nymia-ebook-category-header">
                            <h2 class="nymia-ebook-category-title">
                                <span class="nymia-category-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                </span>
                                <?php echo esc_html($category_name); ?>
                                <span class="nymia-ebook-count">(<?php echo count($category_ebooks); ?>)</span>
                            </h2>
                        </div>
                        <div class="nymia-ebook-grid" data-category="<?php echo esc_attr($filter_category); ?>">
                            <?php 
                            $ebook_index = 0;
                            foreach ($category_ebooks as $ebook): 
                                // Determine category for filtering
                                $filter_category_lower = strtolower(str_replace(' ', '-', $category_name));
                                
                                // Get thumbnail or use placeholder
                                $thumbnail = !empty($ebook['thumbnail']) ? $ebook['thumbnail'] : '';
                                
                                // Show first 4, hide rest
                                $is_hidden = $ebook_index >= 4;
                            ?>
                            <div class="nymia-ebook-card<?php echo $is_hidden ? ' nymia-ebook-hidden' : ''; ?>" data-category="<?php echo esc_attr($filter_category_lower); ?>" data-ebook-index="<?php echo $ebook_index; ?>">
                                <div class="nymia-ebook-cover">
                                    <?php if ($thumbnail): ?>
                                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($ebook['title']); ?>" />
                                    <?php else: ?>
                                        <div class="nymia-ebook-cover-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Ebook Badge -->
                                    <div class="nymia-ebook-badge">
                                        <?php 
                                        $has_price = isset($ebook['price']);
                                        $price_val = $has_price ? (float)$ebook['price'] : 0;
                                        ?>
                                        <span class="nymia-ebook-badge-price" style="margin-left:8px; font-weight:700;">
                                            <?php echo $price_val > 0 ? esc_html('$' . number_format($price_val, 2)) : 'Free'; ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Read Button -->
                                    <a href="<?php echo home_url('/single-ebook/?ebook=' . esc_attr($ebook['id'])); ?>" class="nymia-ebook-read-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                        </svg>
                                        <span>Read Now</span>
                                    </a>
                                    
                                    <!-- Size Info -->
                                    <div class="nymia-ebook-info-overlay">
                                        <div class="nymia-ebook-size">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                            <span><?php echo esc_html($ebook['size'] ?? 'N/A'); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($ebook['paid_access']) && $ebook['paid_access'] === 'yes'): ?>
                                        <div class="nymia-ebook-price">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                            </svg>
                                            <span><?php echo esc_html('$' . number_format($ebook['price'] ?? 0, 2)); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="nymia-ebook-details">
                                    <h3 class="nymia-ebook-title"><?php echo esc_html($ebook['title']); ?></h3>
                                    <p class="nymia-ebook-author">by <?php echo esc_html($ebook['author'] ?? 'Unknown'); ?></p>
                                </div>
                            </div>
                            <?php 
                                $ebook_index++;
                                endforeach; 
                                
                                // Show "Show More" button if there are more than 4 ebooks
                                if (count($category_ebooks) > 4):
                            ?>
                            <div class="nymia-ebook-show-more-wrapper" data-category-section="<?php echo esc_attr($filter_category); ?>">
                                <button type="button" class="nymia-ebook-show-more-btn" data-category="<?php echo esc_attr($filter_category); ?>" data-shown="4" data-total="<?php echo count($category_ebooks); ?>">
                                    <span class="show-more-text"><?php esc_html_e('Show More', 'nymia'); ?></span>
                                    <span class="show-more-count"><?php echo esc_html(count($category_ebooks) - 4); ?> <?php esc_html_e('more', 'nymia'); ?></span>
                                    <svg class="show-more-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; 
                    endif; ?>
                </div>

                <!-- Trending Sidebar -->
                <aside class="nymia-ebook-sidebar">
                    <h3 class="nymia-ebook-sidebar-title">📚 Popular Ebooks</h3>
                    <div class="nymia-ebook-sidebar-list">
                        <?php 
                        // Get popular ebooks (sorted by rating/review count)
                        $popular_ebooks = $ebook_posts;
                        
                        // Calculate rating and review count for each ebook
                        foreach ($popular_ebooks as &$ebook) {
                            $ebook_id = isset($ebook['id']) ? intval($ebook['id']) : 0;
                            $rating = isset($ebook['rating']) ? (float)$ebook['rating'] : 0;
                            $review_count = isset($ebook['review_count']) ? intval($ebook['review_count']) : 0;
                            
                            // Get from post meta if not in transient
                            if ($ebook_id) {
                                if (!$review_count) {
                                    $review_count = intval(get_post_meta($ebook_id, '_nymia_ebook_rating_count', true));
                                }
                                if ((!$rating || $rating <= 0) && $review_count > 0) {
                                    $rating_sum = intval(get_post_meta($ebook_id, '_nymia_ebook_rating_sum', true));
                                    if ($rating_sum > 0) {
                                        $rating = round($rating_sum / max(1, $review_count), 1);
                                    }
                                }
                            }
                            
                            $ebook['_popular_rating'] = $rating;
                            $ebook['_popular_review_count'] = $review_count;
                        }
                        unset($ebook);
                        
                        // Sort by rating (descending), then by review count
                        usort($popular_ebooks, function($a, $b) {
                            $a_rating = isset($a['_popular_rating']) ? (float)$a['_popular_rating'] : 0;
                            $b_rating = isset($b['_popular_rating']) ? (float)$b['_popular_rating'] : 0;
                            $a_reviews = isset($a['_popular_review_count']) ? (int)$a['_popular_review_count'] : 0;
                            $b_reviews = isset($b['_popular_review_count']) ? (int)$b['_popular_review_count'] : 0;
                            
                            // Sort by rating first
                            if ($b_rating != $a_rating) {
                                return $b_rating <=> $a_rating;
                            }
                            // Then by review count
                            return $b_reviews <=> $a_reviews;
                        });
                        
                        // Get all popular ebooks (no limit)
                        $total_popular_ebooks = count($popular_ebooks);
                        $initial_display = 3;
                        
                        if (empty($popular_ebooks)): ?>
                            <div class="nymia-ebook-sidebar-empty">
                                <p>No ebooks yet</p>
                            </div>
                        <?php else:
                            $rank = 1;
                            foreach ($popular_ebooks as $index => $popular): 
                                $popular_id = isset($popular['id']) ? intval($popular['id']) : 0;
                                $popular_rating = isset($popular['_popular_rating']) ? (float)$popular['_popular_rating'] : 0;
                                $popular_review_count = isset($popular['_popular_review_count']) ? intval($popular['_popular_review_count']) : 0;
                                $popular_ebook_url = $popular_id ? home_url('/single-ebook/?ebook=' . $popular_id) : '#';
                                $popular_review_label = $popular_review_count ? sprintf(_n('%d review', '%d reviews', $popular_review_count, 'nymia'), $popular_review_count) : __('No reviews yet', 'nymia');
                                $is_hidden = ($index >= $initial_display) ? 'nymia-ebook-sidebar-item-hidden' : '';
                        ?>
                        <a href="<?php echo esc_url($popular_ebook_url); ?>" class="nymia-ebook-sidebar-item <?php echo esc_attr($is_hidden); ?>" style="text-decoration: none; display: block; cursor: pointer;" data-rank="<?php echo $rank; ?>">
                            <div class="nymia-ebook-sidebar-thumbnail">
                                <?php 
                                $popular_thumbnail = !empty($popular['thumbnail']) ? $popular['thumbnail'] : '';
                                if ($popular_thumbnail): ?>
                                    <img src="<?php echo esc_url($popular_thumbnail); ?>" alt="<?php echo esc_attr($popular['title']); ?>">
                                <?php else: ?>
                                    <div class="nymia-ebook-sidebar-thumbnail-placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="nymia-ebook-rank"><?php echo $rank; ?></div>
                            </div>
                            <div class="nymia-ebook-sidebar-info">
                                <h4 class="nymia-ebook-sidebar-name"><?php echo esc_html($popular['title']); ?></h4>
                                <p class="nymia-ebook-sidebar-author">by <?php echo esc_html($popular['author'] ?? 'Unknown'); ?></p>
                                <div class="nymia-ebook-trending-rating">
                                    <div class="nymia-ebook-rating-group">
                                        <span class="nymia-ebook-trending-stars">
                                            <?php 
                                            $full_stars = floor($popular_rating);
                                            $has_half = ($popular_rating - $full_stars) >= 0.5;
                                            for ($i = 1; $i <= 5; $i++): 
                                                if ($i <= $full_stars): ?>
                                                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                    </svg>
                                                <?php elseif ($i == $full_stars + 1 && $has_half): ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                    </svg>
                                                <?php endif;
                                            endfor; ?>
                                        </span>
                                        <span class="nymia-ebook-trending-rating-value">
                                            <?php echo $popular_rating > 0 ? esc_html(number_format($popular_rating, 1)) : '—'; ?>
                                        </span>
                                    </div>
                                    <span class="nymia-ebook-trending-rating-count"><?php echo esc_html($popular_review_label); ?></span>
                                </div>
                            </div>
                        </a>
                        <?php 
                            $rank++;
                            endforeach;
                            
                            // Show Load More button if there are more than 3 ebooks
                            if ($total_popular_ebooks > $initial_display):
                        ?>
                        <div class="nymia-ebook-sidebar-load-more-wrapper">
                            <button type="button" class="nymia-ebook-sidebar-load-more-btn" data-shown="<?php echo $initial_display; ?>" data-total="<?php echo $total_popular_ebooks; ?>">
                                <span class="load-more-text"><?php esc_html_e('Load More', 'nymia'); ?></span>
                                <span class="load-more-count">(<?php echo esc_html($total_popular_ebooks - $initial_display); ?> <?php esc_html_e('more', 'nymia'); ?>)</span>
                                <svg class="load-more-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                        <?php endif;
                        endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<script>
(function() {
    'use strict';
    
    // ========================================
    // EBOOK SHARE FUNCTIONALITY (Library Page) - Removed as per user request
    // ========================================
    const shareBtns = document.querySelectorAll('.nymia-ebook-card-share-btn');
    
    shareBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const ebookUrl = btn.getAttribute('data-ebook-url') || '';
            const ebookTitle = btn.getAttribute('data-ebook-title') || '';
            
            // Create share modal/dropdown
            const shareModal = document.createElement('div');
            shareModal.className = 'nymia-share-modal';
            shareModal.innerHTML = `
                <div class="nymia-share-modal-content">
                    <h3>Share Ebook</h3>
                    <div class="nymia-share-options">
                        <button class="nymia-share-option" data-platform="facebook" data-url="${ebookUrl}" data-title="${ebookTitle}">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                            <span>Facebook</span>
                        </button>
                        <button class="nymia-share-option" data-platform="twitter" data-url="${ebookUrl}" data-title="${ebookTitle}">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                            <span>Twitter</span>
                        </button>
                        <button class="nymia-share-option" data-platform="copy" data-url="${ebookUrl}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            <span>Copy Link</span>
                        </button>
                    </div>
                    <button class="nymia-share-close">Close</button>
                </div>
            `;
            
            // Add to page
            document.body.appendChild(shareModal);
            
            // Show modal
            setTimeout(function() {
                shareModal.classList.add('show');
            }, 10);
            
            // Handle share options
            shareModal.querySelectorAll('.nymia-share-option').forEach(function(option) {
                option.addEventListener('click', function() {
                    const platform = this.getAttribute('data-platform');
                    const url = this.getAttribute('data-url');
                    const title = this.getAttribute('data-title') || '';
                    
                    if (platform === 'copy') {
                        // Copy to clipboard
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(url).then(function() {
                                alert('<?php echo esc_js(__('Link copied to clipboard!', 'nymia')); ?>');
                                shareModal.remove();
                            });
                        } else {
                            // Fallback for older browsers
                            const textarea = document.createElement('textarea');
                            textarea.value = url;
                            textarea.style.position = 'fixed';
                            textarea.style.opacity = '0';
                            document.body.appendChild(textarea);
                            textarea.select();
                            try {
                                document.execCommand('copy');
                                alert('<?php echo esc_js(__('Link copied to clipboard!', 'nymia')); ?>');
                                shareModal.remove();
                            } catch (err) {
                                alert('<?php echo esc_js(__('Unable to copy link. Please copy manually.', 'nymia')); ?>');
                            }
                            document.body.removeChild(textarea);
                        }
                    } else if (platform === 'facebook') {
                        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank', 'width=600,height=400');
                        shareModal.remove();
                    } else if (platform === 'twitter') {
                        window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title), '_blank', 'width=600,height=400');
                        shareModal.remove();
                    }
                });
            });
            
            // Close modal
            shareModal.querySelector('.nymia-share-close').addEventListener('click', function() {
                shareModal.classList.remove('show');
                setTimeout(function() {
                    shareModal.remove();
                }, 300);
            });
            
            // Close on overlay click
            shareModal.addEventListener('click', function(e) {
                if (e.target === shareModal) {
                    shareModal.classList.remove('show');
                    setTimeout(function() {
                        shareModal.remove();
                    }, 300);
                }
            });
        });
    });
    
    // ========================================
    // POPULAR EBOOKS SIDEBAR LOAD MORE
    // ========================================
    const loadMoreBtn = document.querySelector('.nymia-ebook-sidebar-load-more-btn');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const shown = parseInt(this.getAttribute('data-shown')) || 3;
            const total = parseInt(this.getAttribute('data-total')) || 0;
            const items = document.querySelectorAll('.nymia-ebook-sidebar-item.nymia-ebook-sidebar-item-hidden');
            const loadMoreWrapper = document.querySelector('.nymia-ebook-sidebar-load-more-wrapper');
            
            if (items.length === 0) {
                return;
            }
            
            // Show all hidden items with animation
            items.forEach(function(item, index) {
                setTimeout(function() {
                    item.classList.remove('nymia-ebook-sidebar-item-hidden');
                    item.style.display = 'flex';
                    item.style.opacity = '0';
                    item.style.height = 'auto';
                    item.style.margin = '';
                    item.style.padding = '18px';
                    
                    // Animate in
                    setTimeout(function() {
                        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 10);
                }, index * 50); // Stagger animation
            });
            
            // Update button state
            this.classList.add('loaded');
            this.setAttribute('data-shown', total);
            
            // Hide button after all items are shown
            setTimeout(function() {
                if (loadMoreWrapper) {
                    loadMoreWrapper.style.opacity = '0';
                    loadMoreWrapper.style.transition = 'opacity 0.3s ease';
                    setTimeout(function() {
                        loadMoreWrapper.style.display = 'none';
                    }, 300);
                }
            }, (items.length * 50) + 300);
        });
    }
})();
</script>

<style>
/* Share Modal Styles */
.nymia-share-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    z-index: 10002;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nymia-share-modal.show {
    opacity: 1;
}

.nymia-share-modal-content {
    background: linear-gradient(135deg, #1a1a1a 0%, #141414 100%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    padding: 32px;
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.nymia-share-modal-content h3 {
    color: #fff;
    font-size: 1.5rem;
    margin: 0 0 24px 0;
}

.nymia-share-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.nymia-share-option {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 16px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.nymia-share-option:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(191, 76, 26, 0.6);
}

.nymia-share-option svg {
    width: 24px;
    height: 24px;
}

.nymia-share-close {
    background: rgba(191, 76, 26, 0.2);
    border: 1px solid rgba(191, 76, 26, 0.4);
    border-radius: 8px;
    padding: 10px 24px;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.nymia-share-close:hover {
    background: rgba(191, 76, 26, 0.4);
}

/* Bookmark Button Styles */
.nymia-ebook-card-bookmark-btn.is-bookmarked .bookmark-icon {
    display: none;
}

.nymia-ebook-card-bookmark-btn.is-bookmarked .bookmark-icon-filled {
    display: block !important;
    fill: rgba(191, 76, 26, 1);
}

.nymia-ebook-card-bookmark-btn.is-bookmarked {
    color: rgba(191, 76, 26, 1);
}

</style>

<?php get_footer(); ?>


