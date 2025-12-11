<?php
/**
 * Template Name: Ebook
 * 
 * Ebook Library Page Template
 */

get_header(); 
get_sidebar();
?>

<div class="nymia-container">
    <main class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
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
                                $ebook_item_id = isset($ebook['id']) ? intval($ebook['id']) : 0;
                                $ebook_rating_value = isset($ebook['rating']) ? (float)$ebook['rating'] : 0;
                                $ebook_review_count = isset($ebook['review_count']) ? intval($ebook['review_count']) : 0;
                                if ($ebook_item_id) {
                                    if (!$ebook_review_count) {
                                        $ebook_review_count = intval(get_post_meta($ebook_item_id, '_nymia_ebook_rating_count', true));
                                    }
                                    if ((!$ebook_rating_value || $ebook_rating_value <= 0) && $ebook_review_count > 0) {
                                        $rating_sum_meta = intval(get_post_meta($ebook_item_id, '_nymia_ebook_rating_sum', true));
                                        if ($rating_sum_meta > 0) {
                                            $ebook_rating_value = round($rating_sum_meta / max(1, $ebook_review_count), 1);
                                        }
                                    }
                                }
                                $ebook_review_label = $ebook_review_count ? sprintf(_n('%d review', '%d reviews', $ebook_review_count, 'nymia'), $ebook_review_count) : __('No reviews yet', 'nymia');
                                
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
                                    <div class="nymia-ebook-rating-summary">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                        <span class="nymia-ebook-rating-value">
                                            <?php echo $ebook_rating_value > 0 ? esc_html(number_format($ebook_rating_value, 1)) : '—'; ?>
                                        </span>
                                        <span class="nymia-ebook-rating-count"><?php echo esc_html($ebook_review_label); ?></span>
                                    </div>
                                    
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
                        // Get popular ebooks (sorted by downloads/views)
                        $popular_ebooks = $ebook_posts;
                        // Sort by downloads if available, otherwise by views
                        usort($popular_ebooks, function($a, $b) {
                            $a_downloads = isset($a['downloads']) ? (int)$a['downloads'] : 0;
                            $b_downloads = isset($b['downloads']) ? (int)$b['downloads'] : 0;
                            return $b_downloads - $a_downloads;
                        });
                        
                        // Get top 5
                        $popular_ebooks = array_slice($popular_ebooks, 0, 5);
                        
                        if (empty($popular_ebooks)): ?>
                            <div class="nymia-ebook-sidebar-empty">
                                <p>No ebooks yet</p>
                            </div>
                        <?php else:
                            $rank = 1;
                            foreach ($popular_ebooks as $popular): 
                                $downloads_display = !empty($popular['downloads']) ? number_format($popular['downloads']) : '0';
                                $popular_id = isset($popular['id']) ? intval($popular['id']) : 0;
                                $popular_rating = isset($popular['rating']) ? (float)$popular['rating'] : 0;
                                $popular_review_count = isset($popular['review_count']) ? intval($popular['review_count']) : 0;
                                if ($popular_id) {
                                    if (!$popular_review_count) {
                                        $popular_review_count = intval(get_post_meta($popular_id, '_nymia_ebook_rating_count', true));
                                    }
                                    if ((!$popular_rating || $popular_rating <= 0) && $popular_review_count > 0) {
                                        $popular_sum = intval(get_post_meta($popular_id, '_nymia_ebook_rating_sum', true));
                                        if ($popular_sum > 0) {
                                            $popular_rating = round($popular_sum / max(1, $popular_review_count), 1);
                                        }
                                    }
                                }
                                $popular_review_label = $popular_review_count ? sprintf(_n('%d review', '%d reviews', $popular_review_count, 'nymia'), $popular_review_count) : __('No reviews yet', 'nymia');
                        ?>
                        <div class="nymia-ebook-sidebar-item">
                            <div class="nymia-ebook-rank"><?php echo $rank; ?></div>
                            <div class="nymia-ebook-sidebar-info">
                                <h4 class="nymia-ebook-sidebar-name"><?php echo esc_html($popular['title']); ?></h4>
                                <p class="nymia-ebook-sidebar-author"><?php echo esc_html($popular['author'] ?? 'Unknown'); ?></p>
                                <span class="nymia-ebook-downloads">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <?php echo esc_html($downloads_display); ?> downloads
                                </span>
                                <div class="nymia-ebook-trending-rating">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                    <span class="nymia-ebook-trending-rating-value">
                                        <?php echo $popular_rating > 0 ? esc_html(number_format($popular_rating, 1)) : '—'; ?>
                                    </span>
                                    <span class="nymia-ebook-trending-rating-count"><?php echo esc_html($popular_review_label); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php 
                            $rank++;
                            endforeach;
                        endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>


<style>
.nymia-ebook-rating-summary,
.nymia-ebook-trending-rating {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.75);
    margin-top: 8px;
}

.nymia-ebook-rating-summary svg,
.nymia-ebook-trending-rating svg {
    width: 16px;
    height: 16px;
    color: #fbbf24;
}

.nymia-ebook-rating-value,
.nymia-ebook-trending-rating-value {
    font-weight: 600;
}

.nymia-ebook-rating-count,
.nymia-ebook-trending-rating-count {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.6);
}
</style>

