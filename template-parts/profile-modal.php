<?php
/**
 * ========================================
 * NYMIA THEME - PROFILE MODAL
 * ========================================
 * Profile modal popup that displays user profile
 * 
 * @package Nymia
 * @version 1.0
 */

// CHECK: If user is logged in
if (!is_user_logged_in()) {
    return;
}

// GET: Current logged-in user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// GET: User data
$user_avatar = get_template_directory_uri() . '/assets/images/profile.png';
$avatar_url = get_avatar_url($user_id, array('size' => 120));
$custom_avatar = get_user_meta($user_id, 'custom_avatar', true);
if ($custom_avatar) {
    $user_avatar = esc_url($custom_avatar);
} else {
    $user_avatar = $avatar_url;
}

$full_name = $current_user->display_name;
$user_email = $current_user->user_email;
$user_login = $current_user->user_login;
$user_bio = get_user_meta($user_id, 'description', true) ?: get_user_meta($user_id, 'bio', true) ?: '';
$user_location = get_user_meta($user_id, 'location', true);
$user_website = $current_user->user_url;
$user_cover_image = get_user_meta($user_id, 'cover_image', true);
?>

<div id="nymiaProfileModal" class="nymia-modal">
    <div class="nymia-modal-content nymia-profile-modal-content">
        <div class="nymia-modal-header">
            <h2><?php esc_html_e('Profile', 'nymia'); ?></h2>
            <button type="button" class="nymia-modal-close" id="closeProfileModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="nymia-profile-modal-body">
            <!-- Profile Cover -->
            <?php if ($user_cover_image): ?>
            <div class="nymia-profile-modal-cover" style="background-image: url('<?php echo esc_url($user_cover_image); ?>');">
            </div>
            <?php else: ?>
            <div class="nymia-profile-modal-cover" style="background: linear-gradient(135deg, #BF4C1A, #9F2B1A);">
            </div>
            <?php endif; ?>
            
            <!-- Profile Info -->
            <div class="nymia-profile-modal-info">
                <div class="nymia-profile-modal-avatar">
                    <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($full_name); ?>" onerror="this.onerror=null; this.src='<?php echo get_template_directory_uri(); ?>/assets/images/profile.png';" />
                </div>
                
                <div class="nymia-profile-modal-details">
                    <h3><?php echo esc_html($full_name); ?></h3>
                    <p class="nymia-profile-modal-username">@<?php echo esc_html($user_login); ?></p>
                    
                    <?php if ($user_bio): ?>
                    <p class="nymia-profile-modal-bio"><?php echo esc_html($user_bio); ?></p>
                    <?php endif; ?>
                    
                    <div class="nymia-profile-modal-meta">
                        <?php if ($user_location): ?>
                        <div class="nymia-profile-modal-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span><?php echo esc_html($user_location); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($user_website): ?>
                        <div class="nymia-profile-modal-meta-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" y1="14" x2="21" y2="3"></line>
                            </svg>
                            <a href="<?php echo esc_url($user_website); ?>" target="_blank" rel="noopener"><?php echo esc_html($user_website); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="nymia-profile-modal-actions">
                        <button type="button" class="nymia-btn-gradient" id="openEditProfileFromModal" onclick="document.getElementById('nymiaProfileModal').classList.remove('active'); document.body.style.overflow = '';">
                            <?php esc_html_e('Edit Profile', 'nymia'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('nymiaProfileModal');
    const openBtn = document.getElementById('openProfileModal');
    const closeBtn = document.getElementById('closeProfileModal');
    
    // Open modal
    if (openBtn) {
        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Close profile menu first
            const profileMenu = document.getElementById('nymiaProfileMenu');
            if (profileMenu) {
                profileMenu.style.display = 'none';
            }
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    }
    
    // Close modal
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Open edit profile modal from profile modal
    const openEditProfileBtn = document.getElementById('openEditProfileFromModal');
    if (openEditProfileBtn) {
        openEditProfileBtn.addEventListener('click', function() {
            // Close profile modal
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Open edit profile modal
            const editModal = document.getElementById('editProfileModal');
            const openEditBtn = document.getElementById('openEditModal');
            
            if (editModal) {
                editModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else if (openEditBtn) {
                // If modal doesn't exist, try clicking the edit button
                openEditBtn.click();
            } else {
                // Fallback: navigate to profile page
                window.location.href = '<?php echo esc_url(home_url('/profile')); ?>';
            }
        });
    }
});
</script>

<style>
.nymia-profile-modal-content {
    max-width: 600px;
    padding: 0;
    overflow: hidden;
}

.nymia-profile-modal-body {
    max-height: 80vh;
    overflow-y: auto;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.nymia-profile-modal-body::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

.nymia-profile-modal-cover {
    width: 100%;
    height: 150px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.nymia-profile-modal-info {
    padding: 24px;
    position: relative;
}

.nymia-profile-modal-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--card);
    margin-top: -50px;
    margin-bottom: 16px;
    background: var(--card);
}

.nymia-profile-modal-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.nymia-profile-modal-details h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0 0 4px 0;
}

.nymia-profile-modal-username {
    font-size: 1rem;
    color: var(--muted-foreground);
    margin: 0 0 12px 0;
}

.nymia-profile-modal-bio {
    font-size: 0.9375rem;
    color: var(--foreground);
    line-height: 1.6;
    margin: 0 0 16px 0;
}

.nymia-profile-modal-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

.nymia-profile-modal-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    color: var(--muted-foreground);
}

.nymia-profile-modal-meta-item svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.nymia-profile-modal-meta-item a {
    color: var(--primary);
    text-decoration: none;
}

.nymia-profile-modal-meta-item a:hover {
    text-decoration: underline;
}

.nymia-profile-modal-actions {
    margin-top: 20px;
}

.nymia-profile-modal-actions .nymia-btn-gradient {
    width: 100%;
    text-align: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .nymia-profile-modal-content {
        max-width: 90%;
    }
    
    .nymia-profile-modal-cover {
        height: 120px;
    }
    
    .nymia-profile-modal-avatar {
        width: 80px;
        height: 80px;
        margin-top: -40px;
    }
}
</style>

