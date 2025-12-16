<?php
/**
 * Template Name: Single Ebook
 * 
 * Single Ebook Reader Page
 */

get_header(); 
get_sidebar();

// Get Ebook ID from URL
$ebook_id = isset($_GET['ebook']) ? sanitize_text_field($_GET['ebook']) : '';

// Fetch all ebooks from the upload system
$all_ebooks = function_exists('nymia_get_all_ebooks') ? nymia_get_all_ebooks() : array();

// Find the requested ebook by id
$ebook = null;
$final_ebook_id = '';

if (!empty($ebook_id) && !empty($all_ebooks)) {
	foreach ($all_ebooks as $item) {
		if (!empty($item['id']) && (string)$item['id'] === (string)$ebook_id) {
			$ebook = $item;
			$final_ebook_id = (string)$item['id'];
			break;
		}
	}
}

// If not found, show a graceful message and fallback to first available (if any)
if (!$ebook) {
	$ebook = !empty($all_ebooks) ? $all_ebooks[0] : null;
	if ($ebook && !empty($ebook['id'])) {
		$final_ebook_id = (string)$ebook['id'];
	} elseif (!empty($ebook_id)) {
		$final_ebook_id = (string)$ebook_id;
	}
} else {
	$final_ebook_id = !empty($ebook['id']) ? (string)$ebook['id'] : (string)$ebook_id;
}

$ebook_rating_value = 0;
$ebook_review_count = 0;
$ebook_post_id = !empty($ebook['id']) ? intval($ebook['id']) : 0;

if ($ebook_post_id) {
    $meta_count = intval(get_post_meta($ebook_post_id, '_nymia_ebook_rating_count', true));
    $meta_sum = intval(get_post_meta($ebook_post_id, '_nymia_ebook_rating_sum', true));
    if ($meta_count > 0 && $meta_sum > 0) {
        $ebook_rating_value = round($meta_sum / max(1, $meta_count), 1);
        $ebook_review_count = $meta_count;
    }
}

$ebook_meta_label = $ebook_review_count
    ? sprintf(_n('%d review', '%d reviews', $ebook_review_count, 'nymia'), $ebook_review_count)
    : __('No reviews yet', 'nymia');

$ebook_review_ajax = array(
    'url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nymia_ebook_review_nonce')
);

$ebook_id_string = !empty($ebook['id']) ? (string)$ebook['id'] : '';
$is_bookmarked = function_exists('nymia_is_ebook_bookmarked') ? nymia_is_ebook_bookmarked($ebook_id_string) : false;
$ebook_share_url = !empty($ebook_id_string) ? home_url('/single-ebook/?ebook=' . esc_attr($ebook_id_string)) : '';
$ebook_title_encoded = !empty($ebook['title']) ? urlencode($ebook['title']) : '';
?>

<div class="nymia-container">
    <main class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <div class="nymia-single-ebook-page">
            <!-- Ebook Reader Header -->
            <div class="nymia-ebook-reader-header">
                <a href="<?php echo home_url('/ebook'); ?>" class="nymia-back-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Back to Library</span>
                </a>
                
                <div class="nymia-ebook-actions">
                    <button class="nymia-ebook-action-btn nymia-ebook-share-btn" data-ebook-id="<?php echo esc_attr($ebook_id_string); ?>" data-ebook-url="<?php echo esc_url($ebook_share_url); ?>" data-ebook-title="<?php echo esc_attr($ebook['title'] ?? ''); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="18" cy="5" r="3"></circle>
                            <circle cx="6" cy="12" r="3"></circle>
                            <circle cx="18" cy="19" r="3"></circle>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                        </svg>
                        <span>Share</span>
                    </button>
                    <button class="nymia-ebook-action-btn nymia-ebook-bookmark-btn <?php echo $is_bookmarked ? 'is-bookmarked' : ''; ?>" data-ebook-id="<?php echo esc_attr($ebook_id_string); ?>">
                        <svg class="bookmark-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <svg class="bookmark-icon-filled" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span class="bookmark-text"><?php echo $is_bookmarked ? __('Bookmarked', 'nymia') : __('Bookmark', 'nymia'); ?></span>
                    </button>
                </div>
            </div>

            <!-- Ebook Content Layout -->
            <div class="nymia-ebook-reader-layout">
                <!-- Ebook Viewer -->
                <div class="nymia-ebook-viewer">
			<?php 
			// Determine preview image and format
			$preview_image = !empty($ebook['thumbnail']) ? $ebook['thumbnail'] : (!empty($ebook['image']) ? $ebook['image'] : '');
			$ebook_url = !empty($ebook['url']) ? $ebook['url'] : '';
			$ebook_format = !empty($ebook['format']) ? strtolower($ebook['format']) : '';
			?>
            <?php 
            $is_paid = (!empty($ebook['paid_access']) && $ebook['paid_access'] === 'yes' && (float)($ebook['price'] ?? 0) > 0);
            $has_access = false;
            // Check if user has unlocked access
            if (is_user_logged_in() && function_exists('nymia_user_has_ebook_access')) {
                $current_user_id = get_current_user_id();
                $ebook_identifier = $ebook['id'] ?? $ebook_id;
                if (!empty($ebook_identifier) && nymia_user_has_ebook_access($current_user_id, $ebook_identifier)) {
                    $has_access = true;
                    $is_paid = false; // User has access, so it's not paid for them
                }
            }
            ?>
			<div class="nymia-ebook-preview">
				<?php if ($ebook_format === 'pdf' && $ebook_url && $is_paid && !$has_access): ?>
					<?php if ($preview_image): ?>
						<img src="<?php echo esc_url($preview_image); ?>" alt="<?php echo esc_attr($ebook['title'] ?? 'Ebook'); ?>">
					<?php else: ?>
						<div class="nymia-ebook-cover-placeholder">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
								<polyline points="14 2 14 8 20 8"></polyline>
							</svg>
						</div>
					<?php endif; ?>
					<div class="nymia-ebook-overlay" style="opacity:1;">
						<div style="display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center;">
							<div style="font-weight:600;">Purchase to Read</div>
							<div style="opacity:0.85;">This ebook requires purchase to read inline.</div>
                            <?php if (!empty($ebook['price'])): ?>
                                <div style="font-size:14px; opacity:0.9;">Price: <?php echo esc_html(number_format((float)$ebook['price'], 2)); ?></div>
							<?php endif; ?>
						<button type="button" class="nymia-read-fullscreen-btn nymia-add-to-cart-btn" data-item-type="ebook" data-item-id="<?php echo esc_attr($final_ebook_id); ?>" style="cursor: pointer; border: none; background: inherit; color: inherit; font: inherit; padding: inherit; display: flex; align-items: center; gap: 8px;">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
									<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
								</svg>
								<span>Add to Cart</span>
							</button>
						</div>
					</div>
				<?php else: ?>
					<?php if ($preview_image): ?>
						<img src="<?php echo esc_url($preview_image); ?>" alt="<?php echo esc_attr($ebook['title'] ?? 'Ebook'); ?>">
					<?php else: ?>
						<div class="nymia-ebook-cover-placeholder">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
								<polyline points="14 2 14 8 20 8"></polyline>
							</svg>
						</div>
					<?php endif; ?>
					<div class="nymia-ebook-overlay"<?php echo $is_paid ? ' style="opacity:1;"' : ''; ?>>
						<?php if ($is_paid): ?>
							<div style="display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center;">
								<div style="font-weight:600;">Premium Ebook</div>
								<div style="opacity:0.85;">Unlock to read the full content</div>
                                <?php if (!empty($ebook['price'])): ?>
                                    <div style="font-size:14px; opacity:0.9;">Price: <?php echo esc_html(number_format((float)$ebook['price'], 2)); ?></div>
								<?php endif; ?>
							<a class="nymia-read-fullscreen-btn" href="<?php echo esc_url(home_url('/checkout?type=ebook&id=' . urlencode((string)($ebook['id'] ?? $ebook_id)))); ?>">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
										<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
									</svg>
									<span>Unlock Full Access</span>
								</a>
							</div>
						<?php elseif ($ebook_url && $ebook_format === 'pdf' && ($has_access || !$is_paid)): ?>
							<iframe src="<?php echo esc_url($ebook_url); ?>" style="width:100%; height:100%; border:none; position:absolute; top:0; left:0;"></iframe>
						<?php elseif ($ebook_url && $ebook_format === 'pdf'): ?>
							<a class="nymia-read-fullscreen-btn" href="<?php echo esc_url($ebook_url); ?>" target="_blank" rel="noopener">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
									<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
								</svg>
								<span>Open PDF</span>
							</a>
						<?php elseif ($ebook_url): ?>
							<a class="nymia-read-fullscreen-btn" href="<?php echo esc_url($ebook_url); ?>" target="_blank" rel="noopener">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
									<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
								</svg>
								<span>Open File</span>
							</a>
						<?php else: ?>
							<button class="nymia-read-fullscreen-btn" disabled>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
									<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
								</svg>
								<span>File Not Available</span>
							</button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<script>
			(function(){
				var preview = document.currentScript && document.currentScript.previousElementSibling;
				if (!preview || !preview.classList || !preview.classList.contains('nymia-ebook-preview')) {
					preview = document.querySelector('.nymia-ebook-preview');
				}
				if (!preview) return;
				preview.addEventListener('contextmenu', function(e){ e.preventDefault(); }, { passive: false });
				document.addEventListener('keydown', function(e){
					if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'p')) {
						e.preventDefault();
					}
				}, { passive: false });
				var openBtns = document.querySelectorAll('[data-action="open-checkout"]');
				var modal = null;
				openBtns.forEach(function(btn){
					btn.addEventListener('click', function(e){
						e.preventDefault();
						if (!modal) modal = document.getElementById('nymiaCheckoutModal');
						if (!modal) return;
						var ebookIdInput = modal.querySelector('input[name="ebook_id"]');
						if (ebookIdInput) ebookIdInput.value = this.getAttribute('data-ebook-id') || '';
						modal.classList.add('active');
						document.body.style.overflow = 'hidden';
					});
				});
				var closeEls = [];
				function closeModal(){
					if (!modal) modal = document.getElementById('nymiaCheckoutModal');
					if (!modal) return;
					modal.classList.remove('active');
					document.body.style.overflow = '';
				}
				document.addEventListener('click', function(e){
					if (e.target && e.target.matches('#nymiaCheckoutModal, #nymiaCheckoutModal .nymia-checkout-close')) {
						closeModal();
					}
				});
                var form = document.getElementById('nymiaCheckoutForm');
				if (form) {
					form.addEventListener('submit', function(e){
						e.preventDefault();
                        var btn = form.querySelector('button[type="submit"]');
                        var original = btn ? btn.textContent : '';
                        if (btn) { btn.disabled = true; btn.textContent = 'Processing...'; }
                        var ebookId = form.querySelector('input[name="ebook_id"]').value;
                        var fd = new FormData();
                        fd.append('action', 'nymia_purchase_ebook');
                        fd.append('ebook_id', ebookId);
                        if (typeof nymiaAjax !== 'undefined' && nymiaAjax.purchaseNonce) {
                            fd.append('nonce', nymiaAjax.purchaseNonce);
                        }
                        var ajaxUrl = (typeof nymiaAjax !== 'undefined' && nymiaAjax.ajaxurl) ? nymiaAjax.ajaxurl : '/wp-admin/admin-ajax.php';
                        fetch(ajaxUrl, { method: 'POST', body: fd })
                            .then(function(res){ return res.json(); })
                            .then(function(data){
                                if (btn) { btn.disabled = false; btn.textContent = original || 'Pay Now'; }
                                if (data && data.success) {
                                    closeModal();
                                    // Reload to reflect unlocked state
                                    window.location.reload();
                                } else {
                                    alert((data && data.data && data.data.message) ? data.data.message : 'Purchase failed.');
                                }
                            })
                            .catch(function(){
                                if (btn) { btn.disabled = false; btn.textContent = original || 'Pay Now'; }
                                alert('Network error. Please try again.');
                            });
					});
				}
			})();
			</script>

			<!-- Checkout Modal -->
			<div id="nymiaCheckoutModal" class="nymia-checkout-modal" style="position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); z-index:10000;">
				<div class="nymia-checkout-dialog" style="background:#111; border:1px solid rgba(255,255,255,0.08); width: min(520px, 92vw); border-radius:14px; overflow:hidden; color:#fff;">
					<div style="display:flex; align-items:center; justify-content:space-between; padding:16px 18px; border-bottom:1px solid rgba(255,255,255,0.08);">
						<h3 style="margin:0; font-size:1.1rem; font-weight:700;">Checkout</h3>
						<button class="nymia-checkout-close" aria-label="Close" style="background:transparent; border:none; color:#aaa; cursor:pointer; font-size:20px;">×</button>
					</div>
					<form id="nymiaCheckoutForm" style="padding:18px 18px 20px 18px; display:flex; flex-direction:column; gap:12px;">
						<input type="hidden" name="ebook_id" value="<?php echo esc_attr((string)($ebook['id'] ?? '')); ?>">
						<div style="display:flex; gap:12px;">
							<div style="flex:1;">
								<label style="display:block; font-size:0.85rem; margin-bottom:6px; color:#bbb;">Full Name</label>
								<input type="text" name="name" required style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:#1b1b1b; color:#fff;">
							</div>
						</div>
						<div>
							<label style="display:block; font-size:0.85rem; margin-bottom:6px; color:#bbb;">Email</label>
							<input type="email" name="email" required style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:#1b1b1b; color:#fff;">
						</div>
						<div>
							<label style="display:block; font-size:0.85rem; margin-bottom:6px; color:#bbb;">Card Details</label>
							<input type="text" name="card" placeholder="Card number" required style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:#1b1b1b; color:#fff;">
						</div>
						<div style="display:flex; gap:12px;">
							<input type="text" name="exp" placeholder="MM/YY" required style="flex:1; padding:10px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:#1b1b1b; color:#fff;">
							<input type="text" name="cvc" placeholder="CVC" required style="flex:1; padding:10px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:#1b1b1b; color:#fff;">
						</div>
						<div style="display:flex; align-items:center; gap:10px; font-size:0.85rem; color:#bbb;">
							<input type="checkbox" name="agree" required>
							<span>I agree to the Terms and Privacy Policy</span>
						</div>
						<button type="submit" class="nymia-btn-gradient" style="margin-top:4px; padding:12px 18px; border-radius:10px; border:none; background:linear-gradient(135deg,#BF4C1A,#9F2B1A); color:#fff; font-weight:700; cursor:pointer;">Pay Now</button>
					</form>
				</div>
			</div>
                    
                    <!-- Ebook Controls -->
                    <div class="nymia-ebook-controls">
                        <button class="nymia-control-btn" onclick="alert('Previous page')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <div class="nymia-page-info">
                            <span class="nymia-current-page">1</span>
                            <span class="nymia-page-separator">/</span>
                            <span class="nymia-total-pages"><?php echo esc_html($ebook['pages'] ?? '—'); ?></span>
                        </div>
                        <button class="nymia-control-btn" onclick="alert('Next page')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                        <button class="nymia-control-btn" onclick="alert('Zoom in')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </button>
                        <button class="nymia-control-btn" onclick="alert('Zoom out')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- Ebook Reviews -->
                    <div class="nymia-ebook-reviews" id="nymiaEbookReviews" data-ebook-id="<?php echo esc_attr($ebook_post_id); ?>" data-ajax-url="<?php echo esc_url($ebook_review_ajax['url']); ?>" data-nonce="<?php echo esc_attr($ebook_review_ajax['nonce']); ?>">
                        <div class="nymia-ebook-reviews-header">
                            <div>
                                <p class="nymia-ebook-reviews-label"><?php esc_html_e('Reader Feedback', 'nymia'); ?></p>
                                <div class="nymia-ebook-reviews-average" id="nymiaEbookReviewAverage">
                                    <?php echo $ebook_rating_value > 0 ? esc_html(number_format($ebook_rating_value, 1)) : '—'; ?>
                                </div>
                                <span class="nymia-ebook-reviews-count" id="nymiaEbookReviewCount">
                                    <?php echo esc_html($ebook_meta_label); ?>
                                </span>
                            </div>
                        </div>

                        <?php if (is_user_logged_in()): ?>
                        <form class="nymia-ebook-review-form" id="nymiaEbookReviewForm">
                            <input type="hidden" name="ebook_id" value="<?php echo esc_attr($ebook_post_id); ?>">
                            <input type="hidden" name="rating" id="nymiaEbookReviewRatingValue" value="0">
                            <div class="nymia-review-stars" id="nymiaEbookReviewStars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" class="nymia-review-star" data-rating-value="<?php echo esc_attr($i); ?>" aria-label="<?php printf(esc_attr__('%d star', 'nymia'), $i); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <textarea name="comment" id="nymiaEbookReviewComment" rows="3" placeholder="<?php esc_attr_e('Share your thoughts about this ebook...', 'nymia'); ?>"></textarea>
                            <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Submit Review', 'nymia'); ?></button>
                        </form>
                        <?php else: ?>
                            <p class="nymia-ebook-review-login"><?php esc_html_e('Please log in to leave a review.', 'nymia'); ?></p>
                        <?php endif; ?>

                        <div class="nymia-ebook-review-list" id="nymiaEbookReviewList">
                            <p class="nymia-ebook-review-placeholder"><?php esc_html_e('No reviews yet. Be the first to share something!', 'nymia'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Ebook Information Sidebar -->
                <aside class="nymia-ebook-info-sidebar">
			<div class="nymia-ebook-details-card">
				<h1 class="nymia-ebook-main-title"><?php echo esc_html($ebook['title'] ?? 'Ebook'); ?></h1>
				<p class="nymia-ebook-author-name">by <?php echo esc_html($ebook['author'] ?? 'Unknown'); ?></p>
                        
                        <div class="nymia-ebook-meta">
                            <div class="nymia-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
					<span><?php echo esc_html($ebook['pages'] ?? '—'); ?> pages</span>
                            </div>
                            <div class="nymia-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
					<span><?php echo esc_html($ebook['size'] ?? ''); ?></span>
                            </div>
                            <div class="nymia-meta-item nymia-meta-rating">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <div class="nymia-meta-rating-info">
                                    <span class="nymia-meta-rating-value" id="nymiaEbookMetaAverage">
                                        <?php echo $ebook_rating_value > 0 ? esc_html(number_format($ebook_rating_value, 1)) : '—'; ?>
                                    </span>
                                    <?php
                                    $ebook_meta_label = $ebook_review_count
                                        ? sprintf(_n('%d review', '%d reviews', $ebook_review_count, 'nymia'), $ebook_review_count)
                                        : __('No reviews yet', 'nymia');
                                    ?>
                                    <span class="nymia-meta-rating-count" id="nymiaEbookMetaCount"><?php echo esc_html($ebook_meta_label); ?></span>
                                </div>
                            </div>
				<?php if (!empty($ebook['paid_access']) && $ebook['paid_access'] === 'yes'): ?>
				<div class="nymia-meta-item">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<line x1="12" y1="1" x2="12" y2="23"></line>
						<path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
					</svg>
                        <span><?php echo esc_html(number_format((float)($ebook['price'] ?? 0), 2)); ?></span>
				</div>
				<?php endif; ?>
                        </div>

                        <div class="nymia-ebook-description">
                            <h3>About this Ebook</h3>
					<p><?php echo esc_html($ebook['description'] ?? ''); ?></p>
                        </div>

                        <?php 
                        // Dynamic button based on access status
                        $requires_payment = (!empty($ebook['paid_access']) && $ebook['paid_access'] === 'yes' && (float)($ebook['price'] ?? 0) > 0);
                        $user_has_access = false;
                        if (is_user_logged_in() && function_exists('nymia_user_has_ebook_access')) {
                            $current_user_id = get_current_user_id();
                            $ebook_identifier = $ebook['id'] ?? $ebook_id;
                            if (!empty($ebook_identifier)) {
                                $user_has_access = nymia_user_has_ebook_access($current_user_id, $ebook_identifier);
                            }
                        }
                        ?>
                        <?php if ($requires_payment && !$user_has_access): ?>
                        <button type="button" class="nymia-premium-btn nymia-add-to-cart-btn" data-item-type="ebook" data-item-id="<?php echo esc_attr($final_ebook_id); ?>" style="cursor: pointer; border: none; background: inherit; color: inherit; font: inherit; padding: inherit; display: flex; align-items: center; gap: 8px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <span>Add to Cart</span>
                        </button>
                        <?php elseif ($user_has_access && $ebook_url): ?>
                        <a class="nymia-premium-btn" href="<?php echo esc_url($ebook_url); ?>" target="_blank" rel="noopener" style="background:linear-gradient(135deg,#4CAF50,#45a049);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                            <span>Read Now</span>
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Related Ebooks -->
                    <div class="nymia-related-ebooks">
                        <h3>Related Ebooks</h3>
                        <div class="nymia-related-list">
                            <?php 
                            // Build simple related list from all ebooks
                            $related_list = array_slice($all_ebooks, 0, 5, true);
                            $shown = 0;
                            foreach ($related_list as $rel): 
                                $rel_id = $rel['id'] ?? '';
                                if ((string)$rel_id === (string)$ebook_id) { continue; }
                                if ($shown >= 3) { break; }
                                $rel_image = !empty($rel['thumbnail']) ? $rel['thumbnail'] : (!empty($rel['image']) ? $rel['image'] : '');
                            ?>
                            <a href="<?php echo home_url('/single-ebook/?ebook=' . urlencode((string)$rel_id)); ?>" class="nymia-related-item">
                                <?php if ($rel_image): ?>
                                <img src="<?php echo esc_url($rel_image); ?>" alt="<?php echo esc_attr($rel['title'] ?? 'Ebook'); ?>">
                                <?php endif; ?>
                                <div class="nymia-related-info">
                                    <h4><?php echo esc_html($rel['title'] ?? 'Ebook'); ?></h4>
                                    <p><?php echo esc_html($rel['author'] ?? 'Unknown'); ?></p>
                                </div>
                            </a>
                            <?php $shown++; endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>

<style>
.nymia-meta-rating {
    align-items: flex-start;
}
.nymia-meta-rating-info {
    display: flex;
    flex-direction: column;
    margin-left: 10px;
    line-height: 1.2;
}
.nymia-meta-rating-value {
    font-weight: 700;
    font-size: 1rem;
}
.nymia-meta-rating-count {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
}
.nymia-ebook-reviews {
    margin-top: 32px;
    padding: 24px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(255, 255, 255, 0.02);
}
.nymia-ebook-reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.nymia-ebook-reviews-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
}
.nymia-ebook-reviews-average {
    font-size: 2.4rem;
    font-weight: 600;
}
.nymia-ebook-reviews-count {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.6);
}
.nymia-ebook-review-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}
.nymia-review-stars {
    display: inline-flex;
    gap: 6px;
}
.nymia-review-star {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.35);
    cursor: pointer;
    transition: color 0.2s ease;
}
.nymia-review-star.active {
    color: #fbbf24;
}
.nymia-review-star svg {
    width: 28px;
    height: 28px;
}
.nymia-ebook-review-form textarea {
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    color: #fff;
    padding: 12px;
}
.nymia-ebook-review-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.nymia-ebook-review-item {
    display: flex;
    gap: 12px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    padding: 14px;
}
.nymia-ebook-review-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}
.nymia-ebook-review-body {
    flex: 1;
}
.nymia-ebook-review-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 4px;
}
.nymia-ebook-review-comment {
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}
.nymia-ebook-review-login,
.nymia-ebook-review-placeholder {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.95rem;
}
</style>

<script>
(function(){
    var reviewRoot = document.getElementById('nymiaEbookReviews');
    if (!reviewRoot) { return; }

    var ajaxUrl = reviewRoot.getAttribute('data-ajax-url');
    var nonce = reviewRoot.getAttribute('data-nonce');
    var ebookId = reviewRoot.getAttribute('data-ebook-id');
    var averageEl = document.getElementById('nymiaEbookReviewAverage');
    var countEl = document.getElementById('nymiaEbookReviewCount');
    var metaAvgEl = document.getElementById('nymiaEbookMetaAverage');
    var metaCountEl = document.getElementById('nymiaEbookMetaCount');
    var listEl = document.getElementById('nymiaEbookReviewList');
    var formEl = document.getElementById('nymiaEbookReviewForm');
    var ratingInput = document.getElementById('nymiaEbookReviewRatingValue');
    var starButtons = reviewRoot.querySelectorAll('#nymiaEbookReviewStars .nymia-review-star');

    var strings = <?php echo wp_json_encode(array(
        'loading' => __('Loading reviews…', 'nymia'),
        'error' => __('Unable to load reviews. Please try again.', 'nymia'),
        'noReviews' => __('No reviews yet. Be the first to share something!', 'nymia'),
        'ratingRequired' => __('Select a star rating before submitting.', 'nymia'),
        'submitError' => __('Unable to submit your review. Please try again.', 'nymia'),
        'thanks' => __('Thanks for sharing your thoughts!', 'nymia'),
        'reviewSingular' => __('review', 'nymia'),
        'reviewPlural' => __('reviews', 'nymia'),
    )); ?>;

    function getLabel(count) {
        if (!count || count <= 0) { return strings.noReviews; }
        return count === 1 ? '1 ' + strings.reviewSingular : count + ' ' + strings.reviewPlural;
    }

    function updateSummary(data) {
        var avg = (data && data.count && data.average) ? parseFloat(data.average).toFixed(1) : '—';
        var label = data ? getLabel(parseInt(data.count || 0, 10)) : strings.noReviews;
        if (averageEl) averageEl.textContent = avg;
        if (countEl) countEl.textContent = label;
        if (metaAvgEl) metaAvgEl.textContent = avg;
        if (metaCountEl) metaCountEl.textContent = label;
    }

    function renderList(reviews) {
        if (!listEl) return;
        if (!reviews || !reviews.length) {
            listEl.innerHTML = '<p class="nymia-ebook-review-placeholder">' + strings.noReviews + '</p>';
            return;
        }
        var fragment = document.createDocumentFragment();
        reviews.forEach(function(review){
            var item = document.createElement('div');
            item.className = 'nymia-ebook-review-item';

            var avatar = document.createElement('img');
            avatar.className = 'nymia-ebook-review-avatar';
            avatar.src = review.avatar || '<?php echo esc_js(get_template_directory_uri() . '/assets/images/profile.png'); ?>';
            avatar.alt = review.display_name || 'Reader';
            item.appendChild(avatar);

            var body = document.createElement('div');
            body.className = 'nymia-ebook-review-body';
            var meta = document.createElement('div');
            meta.className = 'nymia-ebook-review-meta';
            var name = document.createElement('span');
            name.textContent = review.display_name || 'Reader';
            meta.appendChild(name);
            if (review.rating) {
                var stars = document.createElement('span');
                stars.textContent = review.rating + '/5';
                meta.appendChild(stars);
            }
            if (review.time_human) {
                var time = document.createElement('span');
                time.textContent = review.time_human;
                meta.appendChild(time);
            }
            body.appendChild(meta);
            if (review.comment) {
                var comment = document.createElement('p');
                comment.className = 'nymia-ebook-review-comment';
                comment.textContent = review.comment;
                body.appendChild(comment);
            }
            item.appendChild(body);
            fragment.appendChild(item);
        });
        listEl.innerHTML = '';
        listEl.appendChild(fragment);
    }

    function loadReviews(isSilent) {
        if (!ebookId) return;
        var fd = new FormData();
        fd.append('action', 'nymia_fetch_ebook_reviews');
        fd.append('ebook_id', ebookId);
        fd.append('nonce', nonce);
        if (listEl && !isSilent) {
            listEl.innerHTML = '<p class="nymia-ebook-review-placeholder">' + strings.loading + '</p>';
        }
        fetch(ajaxUrl, { method: 'POST', body: fd })
            .then(function(res){ return res.json(); })
            .then(function(data){
                if (!data || !data.success) {
                    if (listEl) listEl.innerHTML = '<p class="nymia-ebook-review-placeholder">' + strings.error + '</p>';
                    return;
                }
                updateSummary(data.data);
                renderList(data.data.reviews);
            })
            .catch(function(){
                if (listEl) listEl.innerHTML = '<p class="nymia-ebook-review-placeholder">' + strings.error + '</p>';
            });
    }

    if (starButtons.length) {
        starButtons.forEach(function(button){
            button.addEventListener('click', function(){
                var value = parseInt(this.getAttribute('data-rating-value'), 10);
                if (ratingInput) {
                    ratingInput.value = value;
                }
                starButtons.forEach(function(btn){
                    var btnValue = parseInt(btn.getAttribute('data-rating-value'), 10);
                    if (value && btnValue <= value) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            });
        });
    }

    if (formEl) {
        formEl.addEventListener('submit', function(e){
            e.preventDefault();
            var ratingValue = ratingInput ? parseInt(ratingInput.value, 10) : 0;
            if (!ratingValue || ratingValue < 1) {
                alert(strings.ratingRequired);
                return;
            }
            var submitBtn = formEl.querySelector('button[type="submit"]');
            var original = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = strings.loading;
            }
            var fd = new FormData(formEl);
            fd.append('action', 'nymia_submit_ebook_review');
            fd.append('nonce', nonce);
            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(function(res){ return res.json(); })
                .then(function(data){
                    if (!data || !data.success) {
                        throw new Error(strings.submitError);
                    }
                    ratingInput.value = 0;
                    starButtons.forEach(function(btn){ btn.classList.remove('active'); });
                    if (formEl.querySelector('#nymiaEbookReviewComment')) {
                        formEl.querySelector('#nymiaEbookReviewComment').value = '';
                    }
                    updateSummary(data.data);
                    renderList(data.data.reviews);
                    alert(strings.thanks);
                    loadReviews(true);
                })
                .catch(function(){
                    alert(strings.submitError);
                })
                .finally(function(){
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = original || '<?php echo esc_js(__('Submit Review', 'nymia')); ?>';
                    }
                });
        });
    }

    loadReviews(false);
})();
</script>

<script>
(function() {
    'use strict';
    
    // ========================================
    // EBOOK BOOKMARK FUNCTIONALITY
    // ========================================
    const bookmarkBtns = document.querySelectorAll('.nymia-ebook-bookmark-btn');
    
    bookmarkBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!window.nymiaAjax || !window.nymiaAjax.isLoggedIn) {
                alert('<?php echo esc_js(__('Please log in to bookmark ebooks.', 'nymia')); ?>');
                return;
            }
            
            const ebookId = btn.getAttribute('data-ebook-id');
            if (!ebookId) {
                alert('<?php echo esc_js(__('Invalid ebook ID.', 'nymia')); ?>');
                return;
            }
            
            const iconOutline = btn.querySelector('.bookmark-icon');
            const iconFilled = btn.querySelector('.bookmark-icon-filled');
            const textSpan = btn.querySelector('.bookmark-text');
            const isCurrentlyBookmarked = btn.classList.contains('is-bookmarked');
            
            // Optimistic UI update
            if (isCurrentlyBookmarked) {
                btn.classList.remove('is-bookmarked');
                if (iconOutline) iconOutline.style.display = 'block';
                if (iconFilled) iconFilled.style.display = 'none';
                if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmark', 'nymia')); ?>';
            } else {
                btn.classList.add('is-bookmarked');
                if (iconOutline) iconOutline.style.display = 'none';
                if (iconFilled) iconFilled.style.display = 'block';
                if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmarked', 'nymia')); ?>';
            }
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('action', 'nymia_toggle_ebook_bookmark');
            formData.append('ebook_id', ebookId);
            formData.append('nonce', window.nymiaAjax.ebookBookmarkNonce || '');
            
            fetch(window.nymiaAjax.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (!data || !data.success) {
                    // Revert optimistic update on error
                    if (isCurrentlyBookmarked) {
                        btn.classList.add('is-bookmarked');
                        if (iconOutline) iconOutline.style.display = 'none';
                        if (iconFilled) iconFilled.style.display = 'block';
                        if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmarked', 'nymia')); ?>';
                    } else {
                        btn.classList.remove('is-bookmarked');
                        if (iconOutline) iconOutline.style.display = 'block';
                        if (iconFilled) iconFilled.style.display = 'none';
                        if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmark', 'nymia')); ?>';
                    }
                    alert(data && data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('Unable to update bookmark. Please try again.', 'nymia')); ?>');
                } else {
                    // Update UI based on response
                    if (data.data && typeof data.data.is_bookmarked !== 'undefined') {
                        if (data.data.is_bookmarked) {
                            btn.classList.add('is-bookmarked');
                            if (iconOutline) iconOutline.style.display = 'none';
                            if (iconFilled) iconFilled.style.display = 'block';
                            if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmarked', 'nymia')); ?>';
                        } else {
                            btn.classList.remove('is-bookmarked');
                            if (iconOutline) iconOutline.style.display = 'block';
                            if (iconFilled) iconFilled.style.display = 'none';
                            if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmark', 'nymia')); ?>';
                        }
                    }
                }
            })
            .catch(function(error) {
                console.error('Bookmark error:', error);
                // Revert optimistic update
                if (isCurrentlyBookmarked) {
                    btn.classList.add('is-bookmarked');
                    if (iconOutline) iconOutline.style.display = 'none';
                    if (iconFilled) iconFilled.style.display = 'block';
                    if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmarked', 'nymia')); ?>';
                } else {
                    btn.classList.remove('is-bookmarked');
                    if (iconOutline) iconOutline.style.display = 'block';
                    if (iconFilled) iconFilled.style.display = 'none';
                    if (textSpan) textSpan.textContent = '<?php echo esc_js(__('Bookmark', 'nymia')); ?>';
                }
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
            });
        });
    });
    
    // ========================================
    // EBOOK SHARE FUNCTIONALITY
    // ========================================
    const shareBtns = document.querySelectorAll('.nymia-ebook-share-btn');
    
    shareBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const ebookUrl = btn.getAttribute('data-ebook-url') || window.location.href;
            const ebookTitle = btn.getAttribute('data-ebook-title') || '<?php echo esc_js($ebook['title'] ?? 'Ebook'); ?>';
            
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
.nymia-ebook-bookmark-btn.is-bookmarked .bookmark-icon {
    display: none;
}

.nymia-ebook-bookmark-btn.is-bookmarked .bookmark-icon-filled {
    display: block !important;
    fill: rgba(191, 76, 26, 1);
}

.nymia-ebook-bookmark-btn.is-bookmarked {
    color: rgba(191, 76, 26, 1);
}

/* Card Actions Styles */
.nymia-ebook-card-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.nymia-ebook-card-share-btn,
.nymia-ebook-card-bookmark-btn {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    padding: 8px 12px;
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
}

.nymia-ebook-card-share-btn:hover,
.nymia-ebook-card-bookmark-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(191, 76, 26, 0.6);
    color: #fff;
}

.nymia-ebook-card-bookmark-btn.is-bookmarked {
    color: rgba(191, 76, 26, 1);
    border-color: rgba(191, 76, 26, 0.4);
}

.nymia-ebook-card-share-btn svg,
.nymia-ebook-card-bookmark-btn svg {
    width: 18px;
    height: 18px;
}
</style>

<script>
// Add to Cart Handler
(function() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.nymia-add-to-cart-btn');
        if (!btn) return;
        
        e.preventDefault();
        const itemType = btn.getAttribute('data-item-type');
        const itemId = btn.getAttribute('data-item-id');
        
        if (!itemType || !itemId) {
            console.error('Missing item type or ID:', { itemType, itemId, button: btn });
            alert('Error: Missing item information. Please refresh the page and try again.');
            return;
        }
        
        // Disable button
        const originalText = btn.querySelector('span') ? btn.querySelector('span').textContent : btn.textContent;
        btn.disabled = true;
        btn.style.opacity = '0.6';
        if (btn.querySelector('span')) {
            btn.querySelector('span').textContent = 'Adding...';
        } else {
            btn.textContent = 'Adding...';
        }
        
        // Debug logging
        console.log('Adding to cart:', { itemType, itemId });
        
        // AJAX request
        const formData = new FormData();
        formData.append('action', 'nymia_add_to_cart');
        formData.append('item_type', itemType);
        formData.append('item_id', itemId);
        
        const nonce = '<?php echo wp_create_nonce('nymia_cart'); ?>';
        formData.append('nonce', nonce);
        
        const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        console.log('AJAX URL:', ajaxUrl);
        console.log('Form data:', {
            action: 'nymia_add_to_cart',
            item_type: itemType,
            item_id: itemId,
            nonce: nonce
        });
        
        // Log FormData contents for debugging
        console.log('FormData entries:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Log response status
            console.log('Response status:', response.status, response.statusText);
            console.log('Response headers:', response.headers);
            
            // Try to get response text first to see what the error is
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Server response (not OK):', text);
                    console.error('Response status:', response.status);
                    console.error('Response URL:', response.url);
                    
                    // For 400 errors, provide more specific message
                    if (response.status === 400) {
                        console.error('400 Bad Request - This usually means:');
                        console.error('1. The AJAX action is not registered');
                        console.error('2. The nonce is invalid or expired');
                        console.error('3. Required parameters are missing');
                        console.error('4. WordPress rejected the request before our handler');
                    }
                    
                    // Try to parse as JSON first
                    try {
                        const json = JSON.parse(text);
                        if (json.data && json.data.message) {
                            throw new Error(json.data.message);
                        }
                        throw new Error('Server error: ' + response.status);
                    } catch (parseError) {
                        // If not JSON, show the text (might be HTML error page)
                        if (text.length > 0) {
                            // Check if it's an HTML error page
                            if (text.includes('<html') || text.includes('<!DOCTYPE')) {
                                throw new Error('Server returned an error page. Status: ' + response.status + '. Please check if you are logged in and try again.');
                            }
                            // Try to extract error message from text
                            const errorMatch = text.match(/error[^<]*/i);
                            if (errorMatch) {
                                throw new Error(errorMatch[0].substring(0, 200));
                            }
                            throw new Error('Server error: ' + response.status + '. ' + text.substring(0, 100));
                        }
                        throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                    }
                });
            }
            
            // Check content type
            const contentType = response.headers.get('content-type');
            if (contentType && !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Unexpected content type:', contentType);
                    console.error('Response text:', text.substring(0, 500));
                    throw new Error('Server returned unexpected response format. Please refresh the page and try again.');
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Add to cart response:', data);
            console.log('Cart data in response:', data.data && data.data.cart ? data.data.cart : 'No cart data');
            if (data.success) {
                // Show success message
                const message = document.createElement('div');
                message.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #4CAF50; color: #fff; padding: 16px 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 10000; display: flex; align-items: center; gap: 12px; font-weight: 600;';
                message.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><polyline points="20 6 9 17 4 12"></polyline></svg><span>' + (data.data && data.data.message ? data.data.message : 'Item added to cart!') + '</span>';
                document.body.appendChild(message);
                
                setTimeout(() => {
                    message.style.transition = 'opacity 0.3s, transform 0.3s';
                    message.style.opacity = '0';
                    message.style.transform = 'translateX(20px)';
                    setTimeout(() => message.remove(), 300);
                }, 2000);
                
                // Redirect to cart after a short delay to ensure cart is saved
                setTimeout(() => {
                    console.log('Redirecting to cart page...');
                    window.location.href = '<?php echo esc_url(home_url('/cart')); ?>';
                }, 1000);
            } else {
                const errorMsg = data.data && data.data.message ? data.data.message : 'Failed to add item to cart';
                console.error('Add to cart failed:', errorMsg, data);
                alert(errorMsg);
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
            console.error('Add to cart error:', error);
            const errorMessage = error.message || 'Network error. Please check your connection and try again.';
            alert(errorMessage);
            btn.disabled = false;
            btn.style.opacity = '1';
            if (btn.querySelector('span')) {
                btn.querySelector('span').textContent = originalText;
            } else {
                btn.textContent = originalText;
            }
        });
    });
})();
</script>

<?php get_footer(); ?>


