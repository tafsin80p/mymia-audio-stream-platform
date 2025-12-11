<?php
/**
 * Template Name: Audio Book
 * 
 * Audio Book Library Page Template
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
            <!-- Audio Book Header -->
            <div class="nymia-ebook-header">
                <div class="nymia-ebook-header-content">
                    <h1 class="nymia-ebook-main-title">
                        <div class="nymia-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18V5l12-2v13"></path>
                                <circle cx="6" cy="18" r="3"></circle>
                                <circle cx="18" cy="16" r="3"></circle>
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            </svg>
                        </div>
                        Audio Book Library
                    </h1>
                    <p class="nymia-ebook-subtitle">Browse and listen to premium Audio Book content</p>
                </div>

                <!-- Filter Tabs -->
                <div class="nymia-filter-tabs">
					<?php 
					// Build category list once for tabs
					$__audiobooks_for_tabs = function_exists('nymia_get_all_audiobooks') ? nymia_get_all_audiobooks() : array();
					$__category_names = array();
					foreach ($__audiobooks_for_tabs as $__audiobook_for_tab) {
						$__cat = !empty($__audiobook_for_tab['category']) ? trim($__audiobook_for_tab['category']) : '';
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
                    // Get all audio books from the upload system
                    $audiobook_posts = function_exists('nymia_get_all_audiobooks') ? nymia_get_all_audiobooks() : array();
                    
                    if (empty($audiobook_posts)): ?>
                        <div class="nymia-ebook-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18V5l12-2v13"></path>
                                <circle cx="6" cy="18" r="3"></circle>
                                <circle cx="18" cy="16" r="3"></circle>
                            </svg>
                            <h3>No Audio Books Available</h3>
                            <p>Start creating audio books to see them here!</p>
                        </div>
                    <?php else:
                        // Group audio books by category
                        $audiobooks_by_category = array();
                        $uncategorized = array();
                        
                        foreach ($audiobook_posts as $audiobook) {
                            $category = !empty($audiobook['category']) ? $audiobook['category'] : 'Uncategorized';
                            if ($category === 'Uncategorized' || empty($category)) {
                                $uncategorized[] = $audiobook;
                            } else {
                                if (!isset($audiobooks_by_category[$category])) {
                                    $audiobooks_by_category[$category] = array();
                                }
                                $audiobooks_by_category[$category][] = $audiobook;
                            }
                        }
                        
                        // If there are uncategorized audio books, add them
                        if (!empty($uncategorized)) {
                            $audiobooks_by_category['Uncategorized'] = $uncategorized;
                        }
                        
                        // Display each category section
                        foreach ($audiobooks_by_category as $category_name => $category_audiobooks):
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
                                <span class="nymia-ebook-count">(<?php echo count($category_audiobooks); ?>)</span>
                            </h2>
                        </div>
                        <div class="nymia-ebook-grid" data-category="<?php echo esc_attr($filter_category); ?>">
                            <?php 
                            $audiobook_index = 0;
                            foreach ($category_audiobooks as $audiobook): 
                                // Determine category for filtering
                                $filter_category_lower = strtolower(str_replace(' ', '-', $category_name));
                                
                                // Get thumbnail or cover image
                                $thumbnail = !empty($audiobook['cover_image']) ? $audiobook['cover_image'] : (!empty($audiobook['thumbnail']) ? $audiobook['thumbnail'] : '');
                                
                                // Get author name
                                $author_name = !empty($audiobook['author']) ? $audiobook['author'] : 'Unknown';
                                if (empty($author_name) && !empty($audiobook['user_id'])) {
                                    $user = get_user_by('id', intval($audiobook['user_id']));
                                    if ($user) {
                                        $author_name = $user->display_name ?: $user->user_login;
                                    }
                                }
                                
                                // Show first 4, hide rest
                                $is_hidden = $audiobook_index >= 4;
                                
                                // Build link to single audio book page
                                $audiobook_id = isset($audiobook['id']) ? $audiobook['id'] : '';
                                $audiobook_url = '';
                                if ($audiobook_id) {
                                    $single_audiobook_page = get_page_by_path('single-audiobook');
                                    if ($single_audiobook_page) {
                                        $audiobook_url = get_permalink($single_audiobook_page);
                                        $audiobook_url = add_query_arg('audiobook', $audiobook_id, $audiobook_url);
                                    } else {
                                        // Fallback to direct URL
                                        $audiobook_url = home_url('/single-audiobook/?audiobook=' . urlencode($audiobook_id));
                                    }
                                }
                            ?>
                            <div class="nymia-audiobook-card<?php echo $is_hidden ? ' nymia-ebook-hidden' : ''; ?>" data-category="<?php echo esc_attr($filter_category_lower); ?>" data-audiobook-index="<?php echo $audiobook_index; ?>">
                                <?php if ($audiobook_url): ?>
                                    <a href="<?php echo esc_url($audiobook_url); ?>" style="text-decoration: none; display: block; color: inherit;">
                                <?php endif; ?>
                                <!-- Cover Image (Angled) -->
                                <div class="nymia-audiobook-cover-wrapper">
                                    <?php if ($thumbnail): ?>
                                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($audiobook['title']); ?>" class="nymia-audiobook-cover" />
                                    <?php else: ?>
                                        <div class="nymia-audiobook-cover-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 18V5l12-2v13"></path>
                                                <circle cx="6" cy="18" r="3"></circle>
                                                <circle cx="18" cy="16" r="3"></circle>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Card Content -->
                                <div class="nymia-audiobook-content">
                                    <!-- Title -->
                                    <h3 class="nymia-audiobook-title"><?php echo esc_html($audiobook['title']); ?></h3>
                                    
                                    <!-- Author -->
                                    <p class="nymia-audiobook-author">by <?php echo esc_html($author_name); ?></p>
                                    
                                    <!-- Category -->
                                    <?php if (!empty($audiobook['category'])): ?>
                                    <p class="nymia-audiobook-category"><?php echo esc_html($audiobook['category']); ?></p>
                                    <?php endif; ?>
                                    
                                    <!-- Rating Stars -->
                                    <div class="nymia-audiobook-rating">
                                        <?php 
                                        $rating = isset($audiobook['rating']) && $audiobook['rating'] > 0 ? floatval($audiobook['rating']) : 5.0; // Default to 5 stars if no rating
                                        $full_stars = floor($rating);
                                        $has_half = ($rating - $full_stars) >= 0.5;
                                        for ($i = 1; $i <= 5; $i++): 
                                            if ($i <= $full_stars): ?>
                                                <svg class="nymia-star-filled" viewBox="0 0 24 24" width="16" height="16">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                            <?php elseif ($i == $full_stars + 1 && $has_half): ?>
                                                <svg class="nymia-star-half" viewBox="0 0 24 24" width="16" height="16">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="nymia-star-empty" viewBox="0 0 24 24" width="16" height="16">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="none" stroke="currentColor" stroke-width="1.5"></polygon>
                                                </svg>
                                            <?php endif;
                                        endfor; ?>
                                    </div>
                                    
                                    <!-- Bottom Actions -->
                                    <div class="nymia-audiobook-actions">
                                        <?php if ($audiobook_url): ?>
                                        <a href="<?php echo esc_url($audiobook_url); ?>" class="nymia-audiobook-clip-btn">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                            </svg>
                                            <span>Clip</span>
                                        </a>
                                        <?php else: ?>
                                        <button type="button" class="nymia-audiobook-clip-btn" disabled>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                            </svg>
                                            <span>Clip</span>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <span class="nymia-audiobook-free-label">
                                            <?php 
                                            $has_price = isset($audiobook['price']);
                                            $price_val = $has_price ? (float)$audiobook['price'] : 0;
                                            echo $price_val > 0 ? esc_html('$' . number_format($price_val, 2)) : 'free audiobook';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($audiobook_url): ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php 
                                $audiobook_index++;
                                endforeach; 
                                
                                // Show "Show More" button if there are more than 4 audio books
                                if (count($category_audiobooks) > 4):
                            ?>
                            <div class="nymia-ebook-show-more-wrapper" data-category-section="<?php echo esc_attr($filter_category); ?>">
                                <button type="button" class="nymia-ebook-show-more-btn" data-category="<?php echo esc_attr($filter_category); ?>" data-shown="4" data-total="<?php echo count($category_audiobooks); ?>">
                                    <span class="show-more-text"><?php esc_html_e('Show More', 'nymia'); ?></span>
                                    <span class="show-more-count"><?php echo esc_html(count($category_audiobooks) - 4); ?> <?php esc_html_e('more', 'nymia'); ?></span>
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
                    <h3 class="nymia-ebook-sidebar-title">🎧 Popular Audio Books</h3>
                    <div class="nymia-ebook-sidebar-list">
                        <?php 
                        // Get popular audio books (sorted by rating/review count)
                        $popular_audiobooks = $audiobook_posts;
                        
                        // Sort by date (most recent first) as default
                        usort($popular_audiobooks, function($a, $b) {
                            $date_a = isset($a['created']) ? strtotime($a['created']) : (isset($a['date']) ? strtotime($a['date']) : 0);
                            $date_b = isset($b['created']) ? strtotime($b['created']) : (isset($b['date']) ? strtotime($b['date']) : 0);
                            return $date_b - $date_a;
                        });
                        
                        // Get all popular audio books (no limit)
                        $total_popular_audiobooks = count($popular_audiobooks);
                        $initial_display = 3;
                        
                        if (empty($popular_audiobooks)): ?>
                            <div class="nymia-ebook-sidebar-empty">
                                <p>No audio books yet</p>
                            </div>
                        <?php else:
                            $rank = 1;
                            foreach ($popular_audiobooks as $index => $popular): 
                                $popular_id = isset($popular['id']) ? $popular['id'] : '';
                                $popular_audiobook_url = '';
                                if ($popular_id) {
                                    $single_audiobook_page = get_page_by_path('single-audiobook');
                                    if ($single_audiobook_page) {
                                        $popular_audiobook_url = get_permalink($single_audiobook_page);
                                        $popular_audiobook_url = add_query_arg('audiobook', $popular_id, $popular_audiobook_url);
                                    } else {
                                        $popular_audiobook_url = home_url('/single-audiobook/?audiobook=' . urlencode($popular_id));
                                    }
                                }
                                
                                $author_name = !empty($popular['author']) ? $popular['author'] : 'Unknown';
                                if (empty($author_name) && !empty($popular['user_id'])) {
                                    $user = get_user_by('id', intval($popular['user_id']));
                                    if ($user) {
                                        $author_name = $user->display_name ?: $user->user_login;
                                    }
                                }
                                
                                $is_hidden = ($index >= $initial_display) ? 'nymia-ebook-sidebar-item-hidden' : '';
                        ?>
                        <a href="<?php echo esc_url($popular_audiobook_url ?: '#'); ?>" class="nymia-ebook-sidebar-item <?php echo esc_attr($is_hidden); ?>" style="text-decoration: none; display: block; cursor: pointer;" data-rank="<?php echo $rank; ?>">
                            <div class="nymia-ebook-sidebar-thumbnail">
                                <?php 
                                $popular_thumbnail = !empty($popular['cover_image']) ? $popular['cover_image'] : (!empty($popular['thumbnail']) ? $popular['thumbnail'] : '');
                                if ($popular_thumbnail): ?>
                                    <img src="<?php echo esc_url($popular_thumbnail); ?>" alt="<?php echo esc_attr($popular['title']); ?>">
                                <?php else: ?>
                                    <div class="nymia-ebook-sidebar-thumbnail-placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 18V5l12-2v13"></path>
                                            <circle cx="6" cy="18" r="3"></circle>
                                            <circle cx="18" cy="16" r="3"></circle>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="nymia-ebook-rank"><?php echo $rank; ?></div>
                            </div>
                            <div class="nymia-ebook-sidebar-info">
                                <h4 class="nymia-ebook-sidebar-name"><?php echo esc_html($popular['title']); ?></h4>
                                <p class="nymia-ebook-sidebar-author">by <?php echo esc_html($author_name); ?></p>
                            </div>
                        </a>
                        <?php 
                            $rank++;
                            endforeach;
                            
                            // Show Load More button if there are more than 3 audio books
                            if ($total_popular_audiobooks > $initial_display):
                        ?>
                        <div class="nymia-ebook-sidebar-load-more-wrapper">
                            <button type="button" class="nymia-ebook-sidebar-load-more-btn" data-shown="<?php echo $initial_display; ?>" data-total="<?php echo $total_popular_audiobooks; ?>">
                                <span class="load-more-text"><?php esc_html_e('Load More', 'nymia'); ?></span>
                                <span class="load-more-count">(<?php echo esc_html($total_popular_audiobooks - $initial_display); ?> <?php esc_html_e('more', 'nymia'); ?>)</span>
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
    // POPULAR AUDIO BOOKS SIDEBAR LOAD MORE
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
/* Audio Book Card Styles */
.nymia-audiobook-card {
    background: #1E1E1E;
    border-radius: 16px;
    overflow: hidden;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.05);
    cursor: pointer;
    min-height: 100%;
}

.nymia-audiobook-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    border-color: rgba(255, 255, 255, 0.1);
}

/* Cover Image Wrapper - Angled */
.nymia-audiobook-cover-wrapper {
    width: 100%;
    padding-bottom: 100%;
    position: relative;
    overflow: visible;
    margin-bottom: 12px;
}

.nymia-audiobook-cover {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    transform: rotate(3deg);
    transition: transform 0.3s ease;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
}

.nymia-audiobook-card:hover .nymia-audiobook-cover {
    transform: rotate(2deg) scale(1.02);
}

.nymia-audiobook-cover-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #2A2A2A 0%, #1A1A1A 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(3deg);
    color: rgba(255, 255, 255, 0.3);
}

.nymia-audiobook-cover-placeholder svg {
    width: 48px;
    height: 48px;
}

/* Card Content */
.nymia-audiobook-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.nymia-audiobook-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    margin: 0 0 4px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.nymia-audiobook-author {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 4px 0;
    font-weight: 400;
}

.nymia-audiobook-category {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
    margin: 0 0 8px 0;
    font-weight: 400;
    text-transform: capitalize;
}

/* Rating Stars */
.nymia-audiobook-rating {
    display: flex;
    align-items: center;
    gap: 3px;
    margin: 8px 0 12px 0;
}

.nymia-audiobook-rating svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.nymia-audiobook-rating .nymia-star-filled {
    fill: #FFD700;
    color: #FFD700;
}

.nymia-audiobook-rating .nymia-star-half {
    fill: #FFD700;
    color: #FFD700;
    opacity: 0.6;
}

.nymia-audiobook-rating .nymia-star-empty {
    fill: none;
    stroke: #FFD700;
    opacity: 0.3;
    color: transparent;
}

/* Bottom Actions */
.nymia-audiobook-actions {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 16px;
    margin-top: auto;
    padding-top: 12px;
}

.nymia-audiobook-clip-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #BF4C1A, #9F2B1A);
    color: #ffffff;
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(191, 76, 26, 0.3);
}

.nymia-audiobook-clip-btn:hover {
    background: linear-gradient(135deg, #CF5C2A, #AF3B2A);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(191, 76, 26, 0.4);
}

.nymia-audiobook-clip-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.nymia-audiobook-clip-btn svg {
    width: 14px;
    height: 14px;
    color: #ffffff;
    flex-shrink: 0;
}

.nymia-audiobook-free-label {
    font-size: 0.8125rem;
    color: #BF4C1A;
    font-weight: 500;
    text-transform: lowercase;
    white-space: nowrap;
    margin-left: auto;
}

/* Grid Layout */
.nymia-ebook-grid .nymia-audiobook-card {
    width: 100%;
}

/* Responsive */
@media (max-width: 768px) {
    .nymia-audiobook-card {
        padding: 16px;
    }
    
    .nymia-audiobook-title {
        font-size: 1rem;
    }
    
    .nymia-audiobook-author {
        font-size: 0.8125rem;
    }
    
    .nymia-audiobook-clip-btn {
        padding: 6px 12px;
        font-size: 0.8125rem;
    }
}
</style>

<?php get_footer(); ?>

