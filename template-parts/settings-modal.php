<?php
/**
 * ========================================
 * NYMIA THEME - SETTINGS MODAL
 * ========================================
 * Settings modal popup for user account settings
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

// Check if user is a creator
$kyc_status = get_user_meta($user_id, 'nymia_creator_kyc_status', true);
$is_creator = ($kyc_status === 'approved') || current_user_can('edit_posts') || current_user_can('manage_options');
?>

<div id="nymiaSettingsModal" class="nymia-modal">
    <div class="nymia-modal-content">
        <div class="nymia-modal-header">
            <h2><?php esc_html_e('Settings', 'nymia'); ?></h2>
            <button type="button" class="nymia-modal-close" id="closeSettingsModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="nymia-settings-modal-content">
            <!-- Account Settings Section -->
            <div class="nymia-settings-section">
                <h3><?php esc_html_e('Account Settings', 'nymia'); ?></h3>
                <div class="nymia-settings-item">
                    <div class="nymia-settings-item-info">
                        <h4><?php esc_html_e('Profile Information', 'nymia'); ?></h4>
                        <p><?php esc_html_e('Update your profile information and personal details', 'nymia'); ?></p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/profile')); ?>" class="nymia-btn-outline" onclick="document.getElementById('nymiaSettingsModal').classList.remove('active'); document.body.style.overflow = '';">
                        <?php esc_html_e('Edit Profile', 'nymia'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Privacy & Security Section -->
            <div class="nymia-settings-section">
                <h3><?php esc_html_e('Privacy & Security', 'nymia'); ?></h3>
                <div class="nymia-settings-item">
                    <div class="nymia-settings-item-info">
                        <h4><?php esc_html_e('Change Password', 'nymia'); ?></h4>
                        <p><?php esc_html_e('Update your password to keep your account secure', 'nymia'); ?></p>
                    </div>
                    <button type="button" class="nymia-btn-outline" id="changePasswordBtn">
                        <?php esc_html_e('Change Password', 'nymia'); ?>
                    </button>
                </div>
            </div>
            
            <?php if ($is_creator): ?>
            <!-- Creator Settings Section -->
            <div class="nymia-settings-section">
                <h3><?php esc_html_e('Creator Settings', 'nymia'); ?></h3>
                <div class="nymia-settings-item">
                    <div class="nymia-settings-item-info">
                        <h4><?php esc_html_e('Wallet & Earnings', 'nymia'); ?></h4>
                        <p><?php esc_html_e('Manage your earnings and payout settings', 'nymia'); ?></p>
                    </div>
                    <?php 
                    $earnings_page = get_page_by_path('earnings');
                    $earnings_link = $earnings_page ? get_permalink($earnings_page) : home_url('/earnings/');
                    ?>
                    <a href="<?php echo esc_url($earnings_link); ?>" class="nymia-btn-outline" onclick="document.getElementById('nymiaSettingsModal').classList.remove('active'); document.body.style.overflow = '';">
                        <?php esc_html_e('View Wallet', 'nymia'); ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Account Management Section -->
            <div class="nymia-settings-section nymia-settings-danger">
                <h3><?php esc_html_e('Account Management', 'nymia'); ?></h3>
                <div class="nymia-settings-item nymia-settings-item-danger">
                    <div class="nymia-settings-item-info">
                        <h4><?php esc_html_e('Delete Account', 'nymia'); ?></h4>
                        <p><?php esc_html_e('Permanently delete your account and all associated data. This action cannot be undone.', 'nymia'); ?></p>
                    </div>
                    <button type="button" class="nymia-btn-danger" id="deleteAccountBtn">
                        <?php esc_html_e('Delete Account', 'nymia'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div id="nymiaDeleteAccountModal" class="nymia-modal">
    <div class="nymia-modal-content nymia-modal-content-small">
        <div class="nymia-modal-header">
            <h2><?php esc_html_e('Delete Account', 'nymia'); ?></h2>
            <button type="button" class="nymia-modal-close" id="closeDeleteAccountModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="nymia-delete-account-content">
            <div class="nymia-delete-account-warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h3><?php esc_html_e('Are you sure?', 'nymia'); ?></h3>
            <p><?php esc_html_e('This will permanently delete your account and all associated data including:', 'nymia'); ?></p>
            <ul>
                <li><?php esc_html_e('Your profile and personal information', 'nymia'); ?></li>
                <li><?php esc_html_e('All your content (posts, audio, ebooks, etc.)', 'nymia'); ?></li>
                <li><?php esc_html_e('Your followers and following relationships', 'nymia'); ?></li>
                <li><?php esc_html_e('All your account settings and preferences', 'nymia'); ?></li>
            </ul>
            <p class="nymia-delete-account-final-warning">
                <strong><?php esc_html_e('This action cannot be undone.', 'nymia'); ?></strong>
            </p>
            <div class="nymia-delete-account-form">
                <label for="deleteAccountConfirm">
                    <?php esc_html_e('Type "DELETE" to confirm:', 'nymia'); ?>
                </label>
                <input type="text" id="deleteAccountConfirm" placeholder="DELETE" autocomplete="off" />
            </div>
            <div class="nymia-modal-footer">
                <button type="button" class="nymia-btn-outline" id="cancelDeleteAccount">
                    <?php esc_html_e('Cancel', 'nymia'); ?>
                </button>
                <button type="button" class="nymia-btn-danger" id="confirmDeleteAccount" disabled>
                    <?php esc_html_e('Delete My Account', 'nymia'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('nymiaSettingsModal');
    const openBtn = document.getElementById('openSettingsModal');
    const closeBtn = document.getElementById('closeSettingsModal');
    
    // Open modal
    if (openBtn) {
        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
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
    
    // Delete Account Modal
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteAccountModal = document.getElementById('nymiaDeleteAccountModal');
    const closeDeleteAccountModal = document.getElementById('closeDeleteAccountModal');
    const cancelDeleteAccount = document.getElementById('cancelDeleteAccount');
    const confirmDeleteAccount = document.getElementById('confirmDeleteAccount');
    const deleteAccountConfirm = document.getElementById('deleteAccountConfirm');
    
    // Open delete account modal
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function() {
            if (modal) {
                modal.classList.remove('active');
            }
            if (deleteAccountModal) {
                deleteAccountModal.classList.add('active');
                document.body.style.overflow = 'hidden';
                deleteAccountConfirm.value = '';
                confirmDeleteAccount.disabled = true;
            }
        });
    }
    
    // Close delete account modal
    function closeDeleteModal() {
        if (deleteAccountModal) {
            deleteAccountModal.classList.remove('active');
            document.body.style.overflow = '';
            deleteAccountConfirm.value = '';
            confirmDeleteAccount.disabled = true;
        }
    }
    
    if (closeDeleteAccountModal) {
        closeDeleteAccountModal.addEventListener('click', closeDeleteModal);
    }
    
    if (cancelDeleteAccount) {
        cancelDeleteAccount.addEventListener('click', closeDeleteModal);
    }
    
    // Close delete modal when clicking outside
    if (deleteAccountModal) {
        deleteAccountModal.addEventListener('click', function(e) {
            if (e.target === deleteAccountModal) {
                closeDeleteModal();
            }
        });
    }
    
    // Enable/disable confirm button based on confirmation text
    if (deleteAccountConfirm && confirmDeleteAccount) {
        deleteAccountConfirm.addEventListener('input', function() {
            if (this.value.trim().toUpperCase() === 'DELETE') {
                confirmDeleteAccount.disabled = false;
            } else {
                confirmDeleteAccount.disabled = true;
            }
        });
    }
    
    // Handle account deletion
    if (confirmDeleteAccount) {
        confirmDeleteAccount.addEventListener('click', function() {
            if (this.disabled) return;
            
            const confirmText = deleteAccountConfirm.value.trim().toUpperCase();
            if (confirmText !== 'DELETE') {
                alert('<?php echo esc_js(__('Please type "DELETE" to confirm', 'nymia')); ?>');
                return;
            }
            
            // Show loading state
            this.disabled = true;
            this.textContent = '<?php echo esc_js(__('Deleting...', 'nymia')); ?>';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'nymia_delete_account');
            formData.append('nonce', '<?php echo wp_create_nonce('nymia_delete_account'); ?>');
            
            // Send AJAX request
            const ajaxUrl = (window.nymiaAjax && window.nymiaAjax.ajaxurl) || '/wp-admin/admin-ajax.php';
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo esc_js(__('Your account has been deleted successfully.', 'nymia')); ?>');
                    window.location.href = '<?php echo esc_url(home_url('/')); ?>';
                } else {
                    alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
                    this.disabled = false;
                    this.textContent = '<?php echo esc_js(__('Delete My Account', 'nymia')); ?>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'nymia')); ?>');
                this.disabled = false;
                this.textContent = '<?php echo esc_js(__('Delete My Account', 'nymia')); ?>';
            });
        });
    }
});
</script>

<style>
.nymia-settings-modal-content {
    padding: 24px;
    max-height: 70vh;
    overflow-y: auto;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.nymia-settings-modal-content::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

.nymia-settings-section {
    margin-bottom: 32px;
}

.nymia-settings-section:last-child {
    margin-bottom: 0;
}

.nymia-settings-section h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--foreground);
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nymia-settings-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 16px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.nymia-settings-item-info {
    flex: 1;
}

.nymia-settings-item-info h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--foreground);
    margin: 0 0 4px 0;
}

.nymia-settings-item-info p {
    font-size: 0.875rem;
    color: var(--muted-foreground);
    margin: 0;
}

.nymia-settings-danger {
    border-top: 2px solid rgba(220, 53, 69, 0.3);
    padding-top: 24px;
    margin-top: 24px;
}

.nymia-settings-danger h3 {
    color: #dc3545;
    border-bottom-color: rgba(220, 53, 69, 0.2);
}

.nymia-settings-item-danger {
    background: rgba(220, 53, 69, 0.05);
    border-color: rgba(220, 53, 69, 0.2);
}

.nymia-btn-danger {
    background: #dc3545;
    color: white;
    border: 1px solid #dc3545;
    padding: 10px 20px;
    border-radius: 24px;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nymia-btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.nymia-btn-danger:disabled {
    background: #6c757d;
    border-color: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.nymia-modal-content-small {
    max-width: 500px;
}

.nymia-delete-account-content {
    padding: 24px;
    text-align: center;
}

.nymia-delete-account-warning {
    width: 64px;
    height: 64px;
    margin: 0 auto 20px;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nymia-delete-account-warning svg {
    width: 32px;
    height: 32px;
    color: #dc3545;
}

.nymia-delete-account-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0 0 16px 0;
}

.nymia-delete-account-content > p {
    font-size: 1rem;
    color: var(--muted-foreground);
    margin: 0 0 16px 0;
    text-align: left;
}

.nymia-delete-account-content ul {
    text-align: left;
    margin: 0 0 20px 0;
    padding-left: 24px;
    color: var(--muted-foreground);
}

.nymia-delete-account-content ul li {
    margin-bottom: 8px;
}

.nymia-delete-account-final-warning {
    background: rgba(220, 53, 69, 0.1);
    border-left: 3px solid #dc3545;
    padding: 12px 16px;
    border-radius: 6px;
    margin: 20px 0;
    text-align: left;
}

.nymia-delete-account-final-warning strong {
    color: #dc3545;
}

.nymia-delete-account-form {
    margin: 24px 0;
    text-align: left;
}

.nymia-delete-account-form label {
    display: block;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--foreground);
    margin-bottom: 8px;
}

.nymia-delete-account-form input {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: var(--foreground);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.nymia-delete-account-form input:focus {
    outline: none;
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    background: rgba(255, 255, 255, 0.08);
}

.nymia-delete-account-form input::placeholder {
    color: var(--muted-foreground);
}

@media (max-width: 768px) {
    .nymia-settings-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .nymia-settings-item .nymia-btn-outline,
    .nymia-settings-item .nymia-btn-danger {
        width: 100%;
    }
    
    .nymia-modal-content-small {
        max-width: 90%;
    }
}
</style>

