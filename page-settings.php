<?php
/**
 * ========================================
 * NYMIA THEME - SETTINGS PAGE
 * ========================================
 * User settings page template
 * Displays: Account settings, preferences, privacy settings
 * 
 * @package Nymia
 * @version 1.0
 */

// CHECK: If user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/'));
    exit;
}

// GET: Current logged-in user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
?>
<?php get_header(); ?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/back-button'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-settings-container">
            <div class="nymia-settings-header">
                <h1><?php esc_html_e('Settings', 'nymia'); ?></h1>
                <p><?php esc_html_e('Manage your account settings and preferences', 'nymia'); ?></p>
            </div>
            
            <div class="nymia-settings-content">
                <div class="nymia-settings-section">
                    <h2><?php esc_html_e('Account Settings', 'nymia'); ?></h2>
                    <div class="nymia-settings-card">
                        <div class="nymia-settings-item">
                            <div class="nymia-settings-item-info">
                                <h3><?php esc_html_e('Profile Information', 'nymia'); ?></h3>
                                <p><?php esc_html_e('Update your profile information and personal details', 'nymia'); ?></p>
                            </div>
                            <a href="<?php echo esc_url(home_url('/profile')); ?>" class="nymia-btn-outline">
                                <?php esc_html_e('Edit Profile', 'nymia'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="nymia-settings-section">
                    <h2><?php esc_html_e('Privacy & Security', 'nymia'); ?></h2>
                    <div class="nymia-settings-card">
                        <div class="nymia-settings-item">
                            <div class="nymia-settings-item-info">
                                <h3><?php esc_html_e('Change Password', 'nymia'); ?></h3>
                                <p><?php esc_html_e('Update your password to keep your account secure', 'nymia'); ?></p>
                            </div>
                            <button type="button" class="nymia-btn-outline" id="changePasswordBtn">
                                <?php esc_html_e('Change Password', 'nymia'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php 
                // Check if user is a creator
                $kyc_status = get_user_meta($user_id, 'nymia_creator_kyc_status', true);
                $is_creator = ($kyc_status === 'approved') || current_user_can('edit_posts') || current_user_can('manage_options');
                
                if ($is_creator): 
                ?>
                <div class="nymia-settings-section">
                    <h2><?php esc_html_e('Creator Settings', 'nymia'); ?></h2>
                    <div class="nymia-settings-card">
                        <div class="nymia-settings-item">
                            <div class="nymia-settings-item-info">
                                <h3><?php esc_html_e('Wallet & Earnings', 'nymia'); ?></h3>
                                <p><?php esc_html_e('Manage your earnings and payout settings', 'nymia'); ?></p>
                            </div>
                            <?php 
                            $earnings_page = get_page_by_path('earnings');
                            $earnings_link = $earnings_page ? get_permalink($earnings_page) : home_url('/earnings/');
                            ?>
                            <a href="<?php echo esc_url($earnings_link); ?>" class="nymia-btn-outline">
                                <?php esc_html_e('View Wallet', 'nymia'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.nymia-settings-container {
    padding: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.nymia-settings-header {
    margin-bottom: 32px;
}

.nymia-settings-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0 0 8px 0;
}

.nymia-settings-header p {
    font-size: 1rem;
    color: var(--muted-foreground);
    margin: 0;
}

.nymia-settings-content {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.nymia-settings-section h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--foreground);
    margin: 0 0 16px 0;
}

.nymia-settings-card {
    background: var(--background-card);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 24px;
}

.nymia-settings-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}

.nymia-settings-item-info {
    flex: 1;
}

.nymia-settings-item-info h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--foreground);
    margin: 0 0 4px 0;
}

.nymia-settings-item-info p {
    font-size: 0.9375rem;
    color: var(--muted-foreground);
    margin: 0;
}

@media (max-width: 768px) {
    .nymia-settings-container {
        padding: 24px 16px;
    }
    
    .nymia-settings-item {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php get_footer(); ?>

