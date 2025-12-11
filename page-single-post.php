<?php
/**
 * Template Name: Single Post
 * 
 * Single Social Post Display Page
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

get_header(); 
get_sidebar();

// Get Post ID from URL
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if (!$post_id) {
    wp_redirect(home_url());
    exit;
}

$post = get_post($post_id);

if (!$post) {
    ?>
    <div class="nymia-container">
        <main class="nymia-main">
            <?php get_template_part('template-parts/header'); ?>
            <div class="nymia-single-post-page" style="padding: 40px 20px; text-align: center;">
                <h2><?php esc_html_e('Post Not Found', 'nymia'); ?></h2>
                <p><?php esc_html_e('The post you are looking for does not exist.', 'nymia'); ?></p>
                <a href="<?php echo esc_url(home_url()); ?>" class="nymia-btn-gradient" style="display: inline-block; margin-top: 20px;"><?php esc_html_e('Back to Home', 'nymia'); ?></a>
            </div>
        </main>
    </div>
    <?php
    get_footer();
    exit;
}

if ($post->post_type !== 'nymia_social_post' || $post->post_status !== 'publish') {
    wp_redirect(home_url());
    exit;
}

$post_data = function_exists('nymia_prepare_social_post_payload') ? nymia_prepare_social_post_payload($post) : array();

if (empty($post_data)) {
    wp_redirect(home_url());
    exit;
}
?>

<div class="nymia-container">
    <main class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <div class="nymia-single-post-page">
            <!-- Back Button -->
            <div class="nymia-post-nav">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="nymia-back-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span><?php esc_html_e('Back to Home', 'nymia'); ?></span>
                </a>
            </div>

            <!-- Post Header - Full Width Background -->
            <div class="nymia-post-header-wrapper">
                <div class="nymia-post-header">
                    <div class="nymia-post-header-overlay"></div>
                    <div class="nymia-post-header-content">
                    <!-- Author Info -->
                    <div class="nymia-post-author-info">
                        <a href="<?php echo esc_url(get_author_posts_url($post_data['author_id'])); ?>" class="nymia-post-author-avatar">
                            <img src="<?php echo esc_url($post_data['avatar']); ?>" alt="<?php echo esc_attr($post_data['author_name']); ?>">
                        </a>
                        <div class="nymia-post-author-details">
                            <h3 class="nymia-post-author-name">
                                <a href="<?php echo esc_url(get_author_posts_url($post_data['author_id'])); ?>">
                                    <?php echo esc_html($post_data['author_name']); ?>
                                </a>
                            </h3>
                            <p class="nymia-post-author-username"><?php echo esc_html($post_data['author_username']); ?></p>
                            <p class="nymia-post-time"><?php echo esc_html($post_data['time_ago']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Post Content and Related Posts Layout -->
            <div class="nymia-single-post-layout">
                <!-- Left Column: Main Post Content -->
                <div class="nymia-single-post-content">
                    <article class="nymia-post-article" data-post-id="<?php echo esc_attr($post_id); ?>">
                        <!-- Post Actions (Edit/Delete) - Only show for post owner -->
                        <?php if (get_current_user_id() == $post_data['author_id']): ?>
                            <div class="nymia-post-actions">
                                <button class="nymia-post-edit-btn" data-post-id="<?php echo esc_attr($post_id); ?>" aria-label="Edit Post">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    <span><?php esc_html_e('Edit', 'nymia'); ?></span>
                                </button>
                                <button class="nymia-post-delete-btn" data-post-id="<?php echo esc_attr($post_id); ?>" aria-label="Delete Post">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    <span><?php esc_html_e('Delete', 'nymia'); ?></span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Post Title -->
                        <h1 class="nymia-post-title"><?php echo esc_html($post_data['title']); ?></h1>

                        <!-- Post Image (if available) -->
                        <?php if (!empty($post_data['image'])): ?>
                            <div class="nymia-post-image-container">
                                <img src="<?php echo esc_url($post_data['image']); ?>" alt="<?php echo esc_attr($post_data['title']); ?>" class="nymia-post-image">
                            </div>
                        <?php endif; ?>

                        <!-- Post Description -->
                        <div class="nymia-post-description">
                            <?php echo wp_kses_post($post_data['description']); ?>
                        </div>

                        <!-- Post Meta -->
                        <div class="nymia-post-meta">
                            <span class="nymia-post-date">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($post_data['date']))); ?>
                            </span>
                        </div>

                        <!-- Post Interactions (Like & Comment) -->
                        <div class="nymia-post-interactions">
                            <button class="nymia-post-like-btn <?php echo !empty($post_data['is_liked']) ? 'liked' : ''; ?>" 
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-liked="<?php echo !empty($post_data['is_liked']) ? '1' : '0'; ?>">
                                <svg class="like-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <svg class="liked-icon" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <span class="nymia-like-count"><?php echo esc_html(!empty($post_data['like_count']) ? $post_data['like_count'] : 0); ?></span>
                            </button><button class="nymia-post-comment-btn" data-post-id="<?php echo esc_attr($post_id); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span class="nymia-comment-count"><?php echo esc_html(!empty($post_data['comment_count']) ? $post_data['comment_count'] : 0); ?></span>
                            </button>
                        </div>

                        <!-- Comments Section -->
                        <div class="nymia-post-comments-section" id="nymiaPostCommentsSection" style="display: none;">
                            <div class="nymia-comments-header">
                                <h3><?php esc_html_e('Comments', 'nymia'); ?></h3>
                            </div>
                            
                            <!-- Comment Form -->
                            <div class="nymia-comment-form-wrapper">
                                <?php if (is_user_logged_in()): ?>
                                    <?php 
                                    $current_user = wp_get_current_user();
                                    $current_avatar = get_template_directory_uri() . '/assets/images/profile.png';
                                    $custom_avatar = get_user_meta(get_current_user_id(), 'custom_avatar', true);
                                    if ($custom_avatar) {
                                        $current_avatar = $custom_avatar;
                                    } else {
                                        $current_avatar = get_avatar_url(get_current_user_id(), array('size' => 64));
                                    }
                                    ?>
                                    <form class="nymia-comment-form" id="nymiaPostCommentForm">
                                        <?php wp_nonce_field('nymia_text_post', 'nymia_text_post_nonce'); ?>
                                        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                        <div class="nymia-comment-form-content">
                                            <img src="<?php echo esc_url($current_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name ?: $current_user->user_login); ?>" class="nymia-comment-form-avatar">
                                            <textarea name="content" placeholder="<?php esc_attr_e('Write a comment...', 'nymia'); ?>" rows="2" required maxlength="2000"></textarea>
                                            <button type="submit" class="nymia-comment-submit-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                                </svg>
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <p class="nymia-comment-login-message">
                                        <a href="<?php echo esc_url(home_url('/login')); ?>"><?php esc_html_e('Log in', 'nymia'); ?></a> <?php esc_html_e('to post a comment.', 'nymia'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Comments List -->
                            <div class="nymia-comments-list" id="nymiaPostCommentsList">
                                <div class="nymia-comments-loading">
                                    <span><?php esc_html_e('Loading comments...', 'nymia'); ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Right Column: Sticky Related Posts Sidebar -->
            <?php
            // Get related posts - first try posts from same author, then other recent posts
            $author_id = $post_data['author_id'];
            $related_posts = array();
            
            // Get posts from the same author (excluding current post)
            $author_query = new WP_Query(array(
                'post_type' => 'nymia_social_post',
                'post_status' => 'publish',
                'author' => $author_id,
                'post__not_in' => array($post_id),
                'posts_per_page' => 10,
                'orderby' => 'date',
                'order' => 'DESC',
            ));
            
            if ($author_query->have_posts()) {
                while ($author_query->have_posts()) {
                    $author_query->the_post();
                    $related_posts[] = function_exists('nymia_prepare_social_post_payload') ? nymia_prepare_social_post_payload(get_post()) : array();
                }
                wp_reset_postdata();
            }
            
            // If we don't have enough posts from same author, add other recent posts
            if (count($related_posts) < 10) {
                $additional_posts = nymia_get_recent_social_posts(15);
                foreach ($additional_posts as $add_post) {
                    if (count($related_posts) >= 10) break;
                    // Skip if already included or is current post
                    if ($add_post['id'] == $post_id) continue;
                    $already_included = false;
                    foreach ($related_posts as $existing) {
                        if ($existing['id'] == $add_post['id']) {
                            $already_included = true;
                            break;
                        }
                    }
                    if (!$already_included) {
                        $related_posts[] = $add_post;
                    }
                }
            }
            
            $total_related_posts = count($related_posts);
            $initial_display = 3;
            
            if (!empty($related_posts)):
            ?>
                <div class="nymia-related-posts-sidebar">
                    <h2 class="nymia-related-posts-title"><?php esc_html_e('Related Posts', 'nymia'); ?></h2>
                    <div class="nymia-related-posts-list">
                        <?php 
                        $index = 0;
                        foreach ($related_posts as $related_post): 
                            $single_post_page = get_page_by_path('single-post');
                            $related_link = $single_post_page ? get_permalink($single_post_page) : home_url('/single-post/');
                            $related_link = add_query_arg('post_id', $related_post['id'], $related_link);
                            $is_hidden = ($index >= $initial_display) ? 'nymia-related-post-hidden' : '';
                            $index++;
                        ?>
                            <a href="<?php echo esc_url($related_link); ?>" class="nymia-related-post-card <?php echo esc_attr($is_hidden); ?>">
                                <div class="nymia-related-post-image">
                                    <?php if (!empty($related_post['image'])): ?>
                                        <img src="<?php echo esc_url($related_post['image']); ?>" alt="<?php echo esc_attr($related_post['title']); ?>">
                                    <?php else: ?>
                                        <div class="nymia-related-post-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <span class="nymia-related-post-badge"><?php esc_html_e('Post', 'nymia'); ?></span>
                                </div>
                                
                                <div class="nymia-related-post-content">
                                    <h3 class="nymia-related-post-title"><?php echo esc_html($related_post['title']); ?></h3>
                                    <div class="nymia-related-post-author">
                                        <img src="<?php echo esc_url($related_post['avatar']); ?>" alt="<?php echo esc_attr($related_post['author_name']); ?>" class="nymia-related-post-avatar">
                                        <span class="nymia-related-post-author-name"><?php echo esc_html($related_post['author_name']); ?></span>
                                    </div>
                                    <p class="nymia-related-post-excerpt"><?php echo esc_html(wp_trim_words(strip_tags($related_post['excerpt']), 10, '...')); ?></p>
                                    <span class="nymia-related-post-time"><?php echo esc_html($related_post['time_ago']); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($total_related_posts > $initial_display): ?>
                        <div class="nymia-related-posts-load-more-wrapper">
                            <button type="button" class="nymia-related-posts-load-more-btn" data-shown="<?php echo $initial_display; ?>" data-total="<?php echo $total_related_posts; ?>">
                                <span class="load-more-text"><?php esc_html_e('Load More', 'nymia'); ?></span>
                                <span class="load-more-count">(<?php echo esc_html($total_related_posts - $initial_display); ?> <?php esc_html_e('more', 'nymia'); ?>)</span>
                                <svg class="load-more-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="nymia-related-posts-sidebar">
                    <h2 class="nymia-related-posts-title"><?php esc_html_e('Related Posts', 'nymia'); ?></h2>
                    <p class="nymia-related-posts-empty"><?php esc_html_e('No related posts available.', 'nymia'); ?></p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
.nymia-single-post-page {
    padding: 0;
    max-width: 100%;
}

.nymia-post-nav {
    padding: 20px 20px 0;
    max-width: 1200px;
    margin: 0 auto;
}

.nymia-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.2s ease;
    padding: 8px 0;
}

.nymia-back-btn:hover {
    color: #fff;
}

.nymia-back-btn svg {
    width: 20px;
    height: 20px;
}

/* Full Width Header Wrapper */
.nymia-post-header-wrapper {
    width: 100%;
    background: linear-gradient(135deg, #BF4C1A 0%, #9F2B1A 100%);
    margin: 20px 0 40px;
    overflow: hidden;
}

.nymia-post-header {
    position: relative;
    min-height: 300px;
    display: flex;
    align-items: flex-end;
    padding: 40px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.nymia-post-header-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.1) 50%, transparent 100%);
}

.nymia-post-header-content {
    position: relative;
    z-index: 1;
    width: 100%;
}

.nymia-post-author-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.nymia-post-author-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(255, 255, 255, 0.9);
    flex-shrink: 0;
}

.nymia-post-author-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.nymia-post-author-details h3 {
    margin: 0 0 4px;
    font-size: 1.5rem;
    color: #fff;
}

.nymia-post-author-details h3 a {
    color: inherit;
    text-decoration: none;
}

.nymia-post-author-details h3 a:hover {
    text-decoration: underline;
}

.nymia-post-author-username {
    margin: 0 0 4px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.95rem;
}

.nymia-post-time {
    margin: 0;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

/* Two Column Layout */
.nymia-single-post-layout {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px 40px;
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 40px;
    align-items: start;
}

.nymia-single-post-content {
    min-width: 0; /* Prevent grid overflow */
}

.nymia-post-article {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 18px;
    padding: 32px;
}

.nymia-post-title {
    font-size: 2rem;
    margin: 0 0 24px;
    color: #fff;
    line-height: 1.3;
    font-weight: 600;
}

.nymia-post-image-container {
    margin: 0 -32px 24px;
    overflow: hidden;
    border-radius: 12px;
}

.nymia-post-image {
    width: 100%;
    height: auto;
    display: block;
    max-height: 600px;
    object-fit: contain;
    background: rgba(0, 0, 0, 0.3);
}

.nymia-post-description {
    font-size: 1.1rem;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 24px;
}

.nymia-post-description p {
    margin: 0 0 16px;
}

.nymia-post-description p:last-child {
    margin-bottom: 0;
}

.nymia-post-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.nymia-post-date {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
}

.nymia-post-date svg {
    width: 16px;
    height: 16px;
}

/* Related Posts Sidebar Styles */
.nymia-related-posts-sidebar {
    position: sticky;
    top: 100px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}

.nymia-related-posts-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
    margin: 0 0 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nymia-related-posts-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.nymia-related-posts-empty {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
    text-align: center;
    padding: 40px 20px;
}

.nymia-related-post-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    overflow: hidden;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.nymia-related-post-card:hover {
    transform: translateY(-2px);
    border-color: rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.04);
}

.nymia-related-post-hidden {
    display: none !important;
    opacity: 0;
    height: 0;
    margin: 0;
    padding: 0;
    overflow: hidden;
}

.nymia-related-post-image {
    position: relative;
    width: 100%;
    aspect-ratio: 16/10;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    flex-shrink: 0;
}

.nymia-related-post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.nymia-related-post-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nymia-related-post-placeholder svg {
    width: 32px;
    height: 32px;
}

.nymia-related-post-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 0.65rem;
    font-weight: 500;
}

.nymia-related-post-content {
    padding: 12px;
}

.nymia-related-post-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
    margin: 0 0 8px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.nymia-related-post-author {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.nymia-related-post-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
}

.nymia-related-post-author-name {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}

.nymia-related-post-excerpt {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.4;
    margin: 0 0 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.nymia-related-post-time {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.5);
    display: block;
    margin-top: auto;
}

.nymia-related-posts-load-more-wrapper {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    opacity: 1;
    transition: opacity 0.3s ease;
}

.nymia-related-posts-load-more-btn {
    width: 100%;
    padding: 10px 14px;
    background: linear-gradient(135deg, rgba(191, 76, 26, 0.15) 0%, rgba(159, 43, 26, 0.1) 100%);
    border: 1px solid rgba(191, 76, 26, 0.3);
    border-radius: 10px;
    color: #fff;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-align: center;
}

.nymia-related-posts-load-more-btn:hover {
    background: linear-gradient(135deg, rgba(191, 76, 26, 0.25) 0%, rgba(159, 43, 26, 0.2) 100%);
    border-color: rgba(191, 76, 26, 0.5);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(191, 76, 26, 0.3);
}

.nymia-related-posts-load-more-btn:active {
    transform: translateY(0);
}

.nymia-related-posts-load-more-btn .load-more-text {
    color: #fff;
}

.nymia-related-posts-load-more-btn .load-more-count {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.75rem;
}

.nymia-related-posts-load-more-btn .load-more-icon {
    width: 14px;
    height: 14px;
    transition: transform 0.3s ease;
}

.nymia-related-posts-load-more-btn:hover .load-more-icon {
    transform: translateY(2px);
}

.nymia-related-posts-load-more-btn.loaded .load-more-text {
    display: none;
}

.nymia-related-posts-load-more-btn.loaded .load-more-count {
    display: none;
}

.nymia-related-posts-load-more-btn.loaded::after {
    content: 'All Loaded';
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8125rem;
}

@media (max-width: 768px) {
    .nymia-post-nav {
        padding: 16px 16px 0;
    }

    .nymia-post-header-wrapper {
        margin: 16px 0 32px;
    }

    .nymia-post-header {
        min-height: 250px;
        padding: 24px 16px;
    }

    .nymia-post-author-avatar {
        width: 48px;
        height: 48px;
    }

    .nymia-post-author-details h3 {
        font-size: 1.25rem;
    }

    .nymia-single-post-layout {
        grid-template-columns: 1fr;
        gap: 32px;
        padding: 0 16px 32px;
    }

    .nymia-related-posts-sidebar {
        position: relative;
        top: 0;
        max-height: none;
    }

    .nymia-post-article {
        padding: 24px;
    }

    .nymia-post-title {
        font-size: 1.5rem;
    }

    .nymia-post-image-container {
        margin: 0 -24px 20px;
    }

    .nymia-related-posts-title {
        font-size: 1.25rem;
        margin-bottom: 20px;
    }
}

/* Post Actions (Edit/Delete) */
.nymia-post-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nymia-post-edit-btn,
.nymia-post-delete-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    font-weight: 500;
}

.nymia-post-edit-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.nymia-post-delete-btn:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: #ef4444;
    transform: translateY(-2px);
}

.nymia-post-edit-btn svg,
.nymia-post-delete-btn svg {
    width: 16px;
    height: 16px;
}

/* Edit Post Modal */
.nymia-edit-post-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.nymia-edit-post-modal.active {
    display: flex;
}

.nymia-edit-post-modal-content {
    background: #1a1a1a;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 32px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}

.nymia-edit-post-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.nymia-edit-post-modal-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.nymia-edit-post-modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.nymia-edit-post-modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: rotate(90deg);
}

.nymia-edit-post-modal-close svg {
    width: 18px;
    height: 18px;
}

.nymia-edit-post-form .nymia-form-group {
    margin-bottom: 20px;
}

.nymia-edit-post-form label {
    display: block;
    margin-bottom: 8px;
    color: #fff;
    font-weight: 500;
    font-size: 0.875rem;
}

.nymia-edit-post-form input[type="text"],
.nymia-edit-post-form textarea {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: #fff;
    font-size: 0.9375rem;
    font-family: inherit;
}

.nymia-edit-post-form input[type="text"]:focus,
.nymia-edit-post-form textarea:focus {
    outline: none;
    border-color: rgba(191, 76, 26, 0.5);
    background: rgba(255, 255, 255, 0.08);
}

.nymia-edit-post-form textarea {
    resize: vertical;
    min-height: 120px;
}

.nymia-edit-post-char-count {
    display: block;
    text-align: right;
    margin-top: 6px;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
}

.nymia-edit-post-form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.nymia-edit-post-form-actions button {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nymia-edit-post-cancel-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
}

.nymia-edit-post-cancel-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.nymia-edit-post-save-btn {
    background: linear-gradient(135deg, #BF4C1A, #9F2B1A);
    color: #fff;
}

.nymia-edit-post-save-btn:hover {
    background: linear-gradient(135deg, #D14619, #8B2A0F);
    transform: translateY(-2px);
}

.nymia-edit-post-save-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

    .nymia-post-article {
        padding: 24px;
    }

    .nymia-post-title {
        font-size: 1.5rem;
    }

    .nymia-post-image-container {
        margin: 0 -24px 20px;
    }
}

/* Post Interactions (Like & Comment) */
.nymia-post-interactions {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 20px;
    padding: 24px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 32px;
    align-items: center;
    justify-content: flex-start;
}

.nymia-post-like-btn,
.nymia-post-comment-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 20px;
    margin-right: 20px;
    padding: 8px 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    font-weight: 500;
    flex-shrink: 0;
    white-space: nowrap;
    width: auto;
    min-width: auto;
}

.nymia-post-like-btn:hover,
.nymia-post-comment-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.nymia-post-like-btn.liked {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.nymia-post-like-btn.liked:hover {
    background: rgba(239, 68, 68, 0.15);
}

.nymia-post-like-btn svg,
.nymia-post-comment-btn svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.nymia-post-like-btn.liked .like-icon {
    display: none;
}

.nymia-post-like-btn.liked .liked-icon {
    display: block !important;
    fill: #ef4444;
}

.nymia-post-like-btn:not(.liked) .liked-icon {
    display: none;
}

.nymia-like-count,
.nymia-comment-count {
    font-weight: 600;
}

/* Comments Section */
.nymia-post-comments-section {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.nymia-comments-header {
    margin-bottom: 24px;
}

.nymia-comments-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

/* Comment Form */
.nymia-comment-form-wrapper {
    margin-bottom: 32px;
}

.nymia-comment-form-content {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.nymia-comment-form-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
}

.nymia-comment-form textarea {
    flex: 1;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #fff;
    font-size: 0.9375rem;
    font-family: inherit;
    resize: vertical;
    min-height: 48px;
}

.nymia-comment-form textarea:focus {
    outline: none;
    border-color: rgba(191, 76, 26, 0.5);
    background: rgba(255, 255, 255, 0.08);
}

.nymia-comment-submit-btn {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #BF4C1A, #9F2B1A);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.nymia-comment-submit-btn:hover {
    background: linear-gradient(135deg, #D14619, #8B2A0F);
    transform: scale(1.1);
}

.nymia-comment-submit-btn svg {
    width: 20px;
    height: 20px;
}

.nymia-comment-login-message {
    text-align: center;
    padding: 24px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9375rem;
}

.nymia-comment-login-message a {
    color: #BF4C1A;
    text-decoration: none;
}

.nymia-comment-login-message a:hover {
    text-decoration: underline;
}

/* Comments List */
.nymia-comments-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.nymia-comments-loading {
    text-align: center;
    padding: 32px;
    color: rgba(255, 255, 255, 0.5);
}

.nymia-comment-item {
    display: flex;
    gap: 12px;
}

.nymia-comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
}

.nymia-comment-content {
    flex: 1;
}

.nymia-comment-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}

.nymia-comment-author {
    font-weight: 600;
    color: #fff;
    font-size: 0.9375rem;
}

.nymia-comment-author-username {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.875rem;
}

.nymia-comment-time {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.8125rem;
    margin-left: auto;
}

.nymia-comment-text {
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
    margin-bottom: 8px;
    font-size: 0.9375rem;
}

.nymia-comment-actions {
    display: flex;
    gap: 16px;
    align-items: center;
}

.nymia-comment-like-btn,
.nymia-comment-reply-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.8125rem;
    border-radius: 16px;
}

.nymia-comment-like-btn:hover,
.nymia-comment-reply-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    color: rgba(255, 255, 255, 0.9);
}

.nymia-comment-like-btn.liked {
    color: #ef4444;
}

.nymia-comment-like-btn.liked:hover {
    background: rgba(239, 68, 68, 0.1);
}

.nymia-comment-like-btn svg,
.nymia-comment-reply-btn svg {
    width: 16px;
    height: 16px;
}

.nymia-comment-like-count {
    font-size: 0.8125rem;
    font-weight: 500;
}

/* Replies Section */
.nymia-comment-replies {
    margin-left: 52px;
    margin-top: 16px;
    padding-left: 16px;
    border-left: 2px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.nymia-reply-form {
    margin-left: 52px;
    margin-top: 12px;
    display: none;
}

.nymia-reply-form.active {
    display: block;
}

.nymia-reply-form-content {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.nymia-reply-form textarea {
    flex: 1;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #fff;
    font-size: 0.875rem;
    font-family: inherit;
    resize: vertical;
    min-height: 40px;
}

.nymia-reply-form textarea:focus {
    outline: none;
    border-color: rgba(191, 76, 26, 0.5);
    background: rgba(255, 255, 255, 0.08);
}

.nymia-reply-submit-btn {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #BF4C1A, #9F2B1A);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.nymia-reply-submit-btn:hover {
    background: linear-gradient(135deg, #D14619, #8B2A0F);
    transform: scale(1.1);
}

.nymia-reply-submit-btn svg {
    width: 18px;
    height: 18px;
}
</style>

<!-- Edit Post Modal -->
<div class="nymia-edit-post-modal" id="nymiaEditPostModal">
    <div class="nymia-edit-post-modal-content">
        <div class="nymia-edit-post-modal-header">
            <h2><?php esc_html_e('Edit Post', 'nymia'); ?></h2>
            <button class="nymia-edit-post-modal-close" id="nymiaEditPostModalClose" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form class="nymia-edit-post-form" id="nymiaEditPostForm" enctype="multipart/form-data">
            <?php wp_nonce_field('nymia_text_post', 'nymia_text_post_nonce'); ?>
            <input type="hidden" id="nymiaEditPostId" name="post_id" value="">
            <div class="nymia-form-group">
                <label for="nymiaEditPostTitle"><?php esc_html_e('Post Title', 'nymia'); ?></label>
                <input type="text" id="nymiaEditPostTitle" name="title" required maxlength="140" />
            </div>
            <div class="nymia-form-group">
                <label for="nymiaEditPostDescription"><?php esc_html_e('Description', 'nymia'); ?></label>
                <textarea id="nymiaEditPostDescription" name="description" rows="5" required maxlength="2000"></textarea>
                <span class="nymia-edit-post-char-count" id="nymiaEditPostCharCount">0/2000</span>
            </div>
            <div class="nymia-form-group">
                <label><?php esc_html_e('Image (optional)', 'nymia'); ?></label>
                <div class="nymia-text-image-upload">
                    <input type="file" id="nymiaEditPostImage" name="image" accept="image/*" hidden />
                    <button type="button" id="nymiaEditPostImageBtn" class="nymia-btn-outline"><?php esc_html_e('Change Image', 'nymia'); ?></button>
                    <div class="nymia-text-image-preview" id="nymiaEditPostImagePreview" style="display:none;">
                        <img src="" alt="<?php esc_attr_e('Preview', 'nymia'); ?>">
                        <button type="button" id="nymiaEditPostImageRemove" aria-label="<?php esc_attr_e('Remove image', 'nymia'); ?>">×</button>
                    </div>
                </div>
            </div>
            <div class="nymia-edit-post-form-actions">
                <button type="button" class="nymia-edit-post-cancel-btn" id="nymiaEditPostCancelBtn"><?php esc_html_e('Cancel', 'nymia'); ?></button>
                <button type="submit" class="nymia-edit-post-save-btn" id="nymiaEditPostSaveBtn"><?php esc_html_e('Save Changes', 'nymia'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('nymiaEditPostModal');
    const editForm = document.getElementById('nymiaEditPostForm');
    const editPostId = document.getElementById('nymiaEditPostId');
    const editPostTitle = document.getElementById('nymiaEditPostTitle');
    const editPostDescription = document.getElementById('nymiaEditPostDescription');
    const editPostCharCount = document.getElementById('nymiaEditPostCharCount');
    const editPostImage = document.getElementById('nymiaEditPostImage');
    const editPostImageBtn = document.getElementById('nymiaEditPostImageBtn');
    const editPostImagePreview = document.getElementById('nymiaEditPostImagePreview');
    const editPostImagePreviewImg = editPostImagePreview ? editPostImagePreview.querySelector('img') : null;
    const editPostImageRemove = document.getElementById('nymiaEditPostImageRemove');
    const editModalClose = document.getElementById('nymiaEditPostModalClose');
    const editModalCancel = document.getElementById('nymiaEditPostCancelBtn');
    const editPostSaveBtn = document.getElementById('nymiaEditPostSaveBtn');
    
    const deleteBtns = document.querySelectorAll('.nymia-post-delete-btn');
    const editBtns = document.querySelectorAll('.nymia-post-edit-btn');
    
    // Open edit modal
    function openEditModal(postId) {
        const formData = new FormData();
        formData.append('action', 'nymia_get_single_post');
        formData.append('post_id', postId);
        formData.append('nonce', '<?php echo wp_create_nonce('nymia_text_post'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && data.data) {
                const post = data.data;
                editPostId.value = post.id;
                editPostTitle.value = post.title || '';
                // Get plain text description (remove HTML tags and entities)
                let descriptionText = post.description || '';
                // Create a temporary div to decode HTML entities
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = descriptionText;
                descriptionText = tempDiv.textContent || tempDiv.innerText || '';
                editPostDescription.value = descriptionText;
                if (editPostCharCount) editPostCharCount.textContent = descriptionText.length + '/2000';
                
                if (post.image && editPostImagePreviewImg) {
                    editPostImagePreviewImg.src = post.image;
                    editPostImagePreview.style.display = 'block';
                }
                
                editModal.classList.add('active');
            } else {
                alert('<?php echo esc_js(__('Unable to load post data.', 'nymia')); ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
        });
    }
    
    // Close edit modal
    function closeEditModal() {
        editModal.classList.remove('active');
        editForm.reset();
        if (editPostImagePreview) editPostImagePreview.style.display = 'none';
        if (editPostCharCount) editPostCharCount.textContent = '0/2000';
    }
    
    // Edit button handlers
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            if (postId) openEditModal(postId);
        });
    });
    
    // Delete button handlers
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            if (!postId) return;
            
            if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this post? This action cannot be undone.', 'nymia')); ?>')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'nymia_delete_text_post');
            formData.append('post_id', postId);
            formData.append('nonce', '<?php echo wp_create_nonce('nymia_text_post'); ?>');
            
            if (editPostSaveBtn) editPostSaveBtn.disabled = true;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    alert('<?php echo esc_js(__('Post deleted successfully.', 'nymia')); ?>');
                    window.location.href = '<?php echo esc_url(home_url('/')); ?>';
                } else {
                    alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Unable to delete post.', 'nymia')); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            })
            .finally(() => {
                if (editPostSaveBtn) editPostSaveBtn.disabled = false;
            });
        });
    });
    
    // Modal close handlers
    if (editModalClose) {
        editModalClose.addEventListener('click', closeEditModal);
    }
    if (editModalCancel) {
        editModalCancel.addEventListener('click', closeEditModal);
    }
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) closeEditModal();
        });
    }
    
    // Character counter
    if (editPostDescription && editPostCharCount) {
        editPostDescription.addEventListener('input', function() {
            editPostCharCount.textContent = this.value.length + '/2000';
        });
    }
    
    // Image upload
    if (editPostImageBtn && editPostImage) {
        editPostImageBtn.addEventListener('click', function() {
            editPostImage.click();
        });
    }
    
    if (editPostImage && editPostImagePreview && editPostImagePreviewImg) {
        editPostImage.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (!file.type.startsWith('image/')) {
                    alert('<?php echo esc_js(__('Please select an image file.', 'nymia')); ?>');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    editPostImagePreviewImg.src = e.target.result;
                    editPostImagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (editPostImageRemove && editPostImagePreview) {
        editPostImageRemove.addEventListener('click', function() {
            if (editPostImage) editPostImage.value = '';
            editPostImagePreview.style.display = 'none';
            if (editPostImagePreviewImg) editPostImagePreviewImg.src = '';
        });
    }
    
    // Form submission
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(editForm);
            formData.append('action', 'nymia_edit_text_post');
            
            if (editPostSaveBtn) editPostSaveBtn.disabled = true;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    alert('<?php echo esc_js(__('Post updated successfully!', 'nymia')); ?>');
                    window.location.reload();
                } else {
                    alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Unable to update post.', 'nymia')); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            })
            .finally(() => {
                if (editPostSaveBtn) editPostSaveBtn.disabled = false;
            });
        });
    }

    // ========================================
    // RELATED POSTS LOAD MORE
    // ========================================
    const relatedLoadMoreBtn = document.querySelector('.nymia-related-posts-load-more-btn');
    
    if (relatedLoadMoreBtn) {
        relatedLoadMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const shown = parseInt(this.getAttribute('data-shown')) || 3;
            const total = parseInt(this.getAttribute('data-total')) || 0;
            const items = document.querySelectorAll('.nymia-related-post-card.nymia-related-post-hidden');
            const loadMoreWrapper = document.querySelector('.nymia-related-posts-load-more-wrapper');
            
            if (items.length === 0) {
                return;
            }
            
            // Show all hidden items with animation
            items.forEach(function(item, index) {
                setTimeout(function() {
                    item.classList.remove('nymia-related-post-hidden');
                    item.style.display = 'flex';
                    item.style.opacity = '0';
                    item.style.height = 'auto';
                    item.style.margin = '';
                    item.style.padding = '';
                    
                    // Animate in
                    setTimeout(function() {
                        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 10);
                }, index * 50);
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

    // ========================================
    // POST LIKES & COMMENTS SYSTEM
    // ========================================
    const likeBtn = document.querySelector('.nymia-post-like-btn');
    const commentBtn = document.querySelector('.nymia-post-comment-btn');
    const commentsSection = document.getElementById('nymiaPostCommentsSection');
    const commentForm = document.getElementById('nymiaPostCommentForm');
    const commentsList = document.getElementById('nymiaPostCommentsList');
    const postId = <?php echo esc_js($post_id); ?>;
    const textPostNonce = '<?php echo wp_create_nonce('nymia_text_post'); ?>';
    
    // Toggle Like
    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('action', 'nymia_toggle_post_like');
            formData.append('post_id', postId);
            formData.append('nonce', textPostNonce);
            
            const likeCountEl = this.querySelector('.nymia-like-count');
            const isLiked = this.classList.contains('liked');
            
            // Optimistic update
            this.classList.toggle('liked');
            let currentCount = parseInt(likeCountEl.textContent) || 0;
            likeCountEl.textContent = isLiked ? (currentCount - 1) : (currentCount + 1);
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    likeCountEl.textContent = data.data.like_count;
                    if (data.data.is_liked) {
                        this.classList.add('liked');
                        this.setAttribute('data-liked', '1');
                    } else {
                        this.classList.remove('liked');
                        this.setAttribute('data-liked', '0');
                    }
                } else {
                    // Revert on error
                    this.classList.toggle('liked');
                    likeCountEl.textContent = currentCount;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert on error
                this.classList.toggle('liked');
                likeCountEl.textContent = currentCount;
            });
        });
    }
    
    // Toggle Comments Section
    if (commentBtn && commentsSection) {
        commentBtn.addEventListener('click', function() {
            const isVisible = commentsSection.style.display !== 'none';
            commentsSection.style.display = isVisible ? 'none' : 'block';
            
            if (!isVisible && commentsList) {
                loadComments();
            }
        });
    }
    
    // Load Comments
    function loadComments() {
        if (!commentsList) return;
        
        commentsList.innerHTML = '<div class="nymia-comments-loading"><span><?php echo esc_js(__('Loading comments...', 'nymia')); ?></span></div>';
        
        const formData = new FormData();
        formData.append('action', 'nymia_get_post_comments');
        formData.append('post_id', postId);
        formData.append('nonce', textPostNonce);
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && data.data.comments) {
                renderComments(data.data.comments);
            } else {
                commentsList.innerHTML = '<div class="nymia-comments-loading"><span><?php echo esc_js(__('No comments yet.', 'nymia')); ?></span></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            commentsList.innerHTML = '<div class="nymia-comments-loading"><span><?php echo esc_js(__('Unable to load comments.', 'nymia')); ?></span></div>';
        });
    }
    
    // Render Comments
    function renderComments(comments) {
        if (!commentsList) return;
        
        if (comments.length === 0) {
            commentsList.innerHTML = '<div class="nymia-comments-loading"><span><?php echo esc_js(__('No comments yet. Be the first to comment!', 'nymia')); ?></span></div>';
            return;
        }
        
        commentsList.innerHTML = comments.map(comment => renderComment(comment)).join('');
        
        // Attach event listeners
        attachCommentListeners();
    }
    
    // Render Single Comment
    function renderComment(comment) {
        const repliesHtml = comment.replies && comment.replies.length > 0 
            ? '<div class="nymia-comment-replies">' + comment.replies.map(reply => renderReply(reply, comment.id)).join('') + '</div>'
            : '';
            
        return `
            <div class="nymia-comment-item" data-comment-id="${escapeHtml(comment.id)}">
                <img src="${escapeHtml(comment.avatar)}" alt="${escapeHtml(comment.author_name)}" class="nymia-comment-avatar">
                <div class="nymia-comment-content">
                    <div class="nymia-comment-header">
                        <span class="nymia-comment-author">${escapeHtml(comment.author_name)}</span>
                        <span class="nymia-comment-author-username">${escapeHtml(comment.author_username)}</span>
                        <span class="nymia-comment-time">${escapeHtml(comment.time_ago)}</span>
                    </div>
                    <div class="nymia-comment-text">${escapeHtml(comment.content)}</div>
                    <div class="nymia-comment-actions">
                        <button class="nymia-comment-like-btn ${comment.is_liked ? 'liked' : ''}" 
                                data-comment-id="${escapeHtml(comment.id)}"
                                data-is-reply="0">
                            <svg viewBox="0 0 24 24" fill="${comment.is_liked ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <span class="nymia-comment-like-count">${comment.like_count || 0}</span>
                        </button>
                        <button class="nymia-comment-reply-btn" data-comment-id="${escapeHtml(comment.id)}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 10a10 10 0 1 0 3.73 7.77L10 16l-3.73 3.73A10 10 0 0 0 3 10z"></path>
                            </svg>
                            <span><?php echo esc_js(__('Reply', 'nymia')); ?></span>
                        </button>
                    </div>
                    <form class="nymia-reply-form" data-parent-comment-id="${escapeHtml(comment.id)}">
                        <input type="hidden" name="post_id" value="${postId}">
                        <input type="hidden" name="parent_comment_id" value="${escapeHtml(comment.id)}">
                        <div class="nymia-reply-form-content">
                            <textarea name="content" placeholder="<?php echo esc_js(__('Write a reply...', 'nymia')); ?>" rows="2" required maxlength="2000"></textarea>
                            <button type="submit" class="nymia-reply-submit-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </form>
                    ${repliesHtml}
                </div>
            </div>
        `;
    }
    
    // Render Reply
    function renderReply(reply, parentId) {
        return `
            <div class="nymia-comment-item" data-comment-id="${escapeHtml(reply.id)}">
                <img src="${escapeHtml(reply.avatar)}" alt="${escapeHtml(reply.author_name)}" class="nymia-comment-avatar">
                <div class="nymia-comment-content">
                    <div class="nymia-comment-header">
                        <span class="nymia-comment-author">${escapeHtml(reply.author_name)}</span>
                        <span class="nymia-comment-author-username">${escapeHtml(reply.author_username)}</span>
                        <span class="nymia-comment-time">${escapeHtml(reply.time_ago)}</span>
                    </div>
                    <div class="nymia-comment-text">${escapeHtml(reply.content)}</div>
                    <div class="nymia-comment-actions">
                        <button class="nymia-comment-like-btn ${reply.is_liked ? 'liked' : ''}" 
                                data-comment-id="${escapeHtml(reply.id)}"
                                data-parent-comment-id="${escapeHtml(parentId)}"
                                data-is-reply="1">
                            <svg viewBox="0 0 24 24" fill="${reply.is_liked ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            <span class="nymia-comment-like-count">${reply.like_count || 0}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Escape HTML helper
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
    // Attach Comment Listeners
    function attachCommentListeners() {
        // Reply buttons
        const replyBtns = document.querySelectorAll('.nymia-comment-reply-btn');
        replyBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const replyForm = document.querySelector(`.nymia-reply-form[data-parent-comment-id="${commentId}"]`);
                if (replyForm) {
                    replyForm.classList.toggle('active');
                    if (replyForm.classList.contains('active')) {
                        const textarea = replyForm.querySelector('textarea');
                        if (textarea) textarea.focus();
                    }
                }
            });
        });
        
        // Comment like buttons
        const commentLikeBtns = document.querySelectorAll('.nymia-comment-like-btn');
        commentLikeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const isReply = this.dataset.isReply === '1';
                const parentCommentId = this.dataset.parentCommentId || '';
                
                const formData = new FormData();
                formData.append('action', 'nymia_toggle_comment_like');
                formData.append('post_id', postId);
                formData.append('comment_id', commentId);
                formData.append('is_reply', isReply ? '1' : '0');
                if (parentCommentId) {
                    formData.append('parent_comment_id', parentCommentId);
                }
                formData.append('nonce', textPostNonce);
                
                const likeCountEl = this.querySelector('.nymia-comment-like-count');
                const isLiked = this.classList.contains('liked');
                
                // Optimistic update
                this.classList.toggle('liked');
                let currentCount = parseInt(likeCountEl.textContent) || 0;
                likeCountEl.textContent = isLiked ? (currentCount - 1) : (currentCount + 1);
                const svg = this.querySelector('svg');
                if (svg) {
                    svg.setAttribute('fill', !isLiked ? 'currentColor' : 'none');
                }
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        likeCountEl.textContent = data.data.like_count;
                        if (data.data.is_liked) {
                            this.classList.add('liked');
                            if (svg) svg.setAttribute('fill', 'currentColor');
                        } else {
                            this.classList.remove('liked');
                            if (svg) svg.setAttribute('fill', 'none');
                        }
                    } else {
                        // Revert on error
                        this.classList.toggle('liked');
                        likeCountEl.textContent = currentCount;
                        if (svg) svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert on error
                    this.classList.toggle('liked');
                    likeCountEl.textContent = currentCount;
                    if (svg) svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
                });
            });
        });
        
        // Reply form submissions
        const replyForms = document.querySelectorAll('.nymia-reply-form');
        replyForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                formData.append('action', 'nymia_submit_post_comment');
                formData.append('nonce', textPostNonce);
                
                const submitBtn = form.querySelector('.nymia-reply-submit-btn');
                if (submitBtn) submitBtn.disabled = true;
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        form.reset();
                        form.classList.remove('active');
                        loadComments(); // Reload comments
                    } else {
                        alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Unable to post reply.', 'nymia')); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        });
    }
    
    // Comment Form Submission
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(commentForm);
            formData.append('action', 'nymia_submit_post_comment');
            formData.append('nonce', textPostNonce);
            
            const submitBtn = commentForm.querySelector('.nymia-comment-submit-btn');
            if (submitBtn) submitBtn.disabled = true;
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    commentForm.reset();
                    loadComments(); // Reload comments
                    
                    // Update comment count
                    const commentCountEl = commentBtn ? commentBtn.querySelector('.nymia-comment-count') : null;
                    if (commentCountEl) {
                        const currentCount = parseInt(commentCountEl.textContent) || 0;
                        commentCountEl.textContent = currentCount + 1;
                    }
                } else {
                    alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Unable to post comment.', 'nymia')); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }
});
</script>

<?php get_footer(); ?>

