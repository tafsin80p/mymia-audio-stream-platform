<?php
/**
 * Footer Menu Settings Page
 * 
 * Admin panel for managing footer menu links
 * 
 * @package Nymia
 * @version 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render Footer Menu Settings Page
 */
function nymia_footer_menu_settings_page() {
    // Handle footer menu form submission
    if (isset($_POST['nymia_save_footer_menu']) && check_admin_referer('nymia_footer_menu_settings', 'nymia_footer_menu_nonce')) {
        // Save footer menu columns
        if (isset($_POST['footer_menu_columns'])) {
            $columns = array();
            foreach ($_POST['footer_menu_columns'] as $column_index => $column_data) {
                $columns[] = array(
                    'title' => sanitize_text_field($column_data['title']),
                    'links' => isset($column_data['links']) ? array_map(function($link) {
                        return array(
                            'text' => sanitize_text_field($link['text']),
                            'url' => esc_url_raw($link['url'])
                        );
                    }, $column_data['links']) : array()
                );
            }
            update_option('nymia_footer_menu_columns', $columns);
        }
        
        // Save footer description
        update_option('nymia_footer_description', sanitize_textarea_field($_POST['nymia_footer_description'] ?? ''));
        
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Footer menu settings saved successfully!', 'nymia') . '</p></div>';
    }
    
    // Handle contact information form submission
    if (isset($_POST['nymia_save_contact_info']) && check_admin_referer('nymia_contact_info_settings', 'nymia_contact_info_nonce')) {
        // Save contact information
        update_option('nymia_contact_email', sanitize_email($_POST['nymia_contact_email'] ?? ''));
        update_option('nymia_contact_phone', sanitize_text_field($_POST['nymia_contact_phone'] ?? ''));
        update_option('nymia_contact_address', sanitize_textarea_field($_POST['nymia_contact_address'] ?? ''));
        
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Contact information saved successfully!', 'nymia') . '</p></div>';
    }

    // Get saved settings
    $footer_columns = get_option('nymia_footer_menu_columns', array(
    array(
        'title' => 'Platform',
        'links' => array(
            array('text' => 'Home', 'url' => home_url('/')),
            array('text' => 'Audio Library', 'url' => home_url('/audio/')),
            array('text' => 'Create Content', 'url' => home_url('/create/')),
            array('text' => 'My Profile', 'url' => home_url('/profile/')),
            array('text' => 'Earnings', 'url' => home_url('/earnings/')),
            array('text' => 'Live Audio', 'url' => home_url('/live-audio/')),
        )
    ),
    array(
        'title' => 'Support',
        'links' => array(
            array('text' => 'Help Center', 'url' => '#'),
            array('text' => 'Contact Us', 'url' => home_url('/contact/')),
            array('text' => 'FAQ', 'url' => '#'),
            array('text' => 'Community Guidelines', 'url' => '#'),
            array('text' => 'Report Issue', 'url' => '#'),
        )
    ),
    array(
        'title' => 'Legal',
        'links' => array(
            array('text' => 'Privacy Policy', 'url' => home_url('/policies/')),
            array('text' => 'Terms & Conditions', 'url' => home_url('/policies/')),
            array('text' => 'Cookie Policy', 'url' => '#'),
            array('text' => 'DMCA Policy', 'url' => '#'),
            array('text' => 'Refund Policy', 'url' => '#'),
        )
    )
));

    $contact_email = get_option('nymia_contact_email', '');
    $contact_phone = get_option('nymia_contact_phone', '');
    $contact_address = get_option('nymia_contact_address', '');
    $footer_description = get_option('nymia_footer_description', 'Design amazing digital experiences that create more happy in the world.');
    
    nymia_render_admin_settings_styles();
    ?>
    <div class="wrap nymia-stripe-wrap nymia-footer-menu-wrap">
        <div class="nymia-stripe-header">
            <div>
                <h1><?php esc_html_e('Footer Menu Settings', 'nymia'); ?></h1>
                <p><?php esc_html_e('Manage footer navigation links, description, and contact information displayed across your site.', 'nymia'); ?></p>
            </div>
        </div>

        <nav class="nymia-dashboard-nav">
            <a href="?page=nymia-theme-settings"><?php esc_html_e('Dashboard Overview', 'nymia'); ?></a>
            <a href="?page=nymia-general-settings"><?php esc_html_e('General Settings', 'nymia'); ?></a>
            <a href="?page=nymia-social-login-settings"><?php esc_html_e('Social Login', 'nymia'); ?></a>
            <a href="?page=nymia-zegocloud-settings"><?php esc_html_e('ZEGO Cloud', 'nymia'); ?></a>
            <a href="?page=nymia-stripe-settings"><?php esc_html_e('Stripe Payments', 'nymia'); ?></a>
            <a class="active" href="?page=nymia-footer-menu-settings"><?php esc_html_e('Footer Menu', 'nymia'); ?></a>
        </nav>

        <form method="post" action="" class="nymia-grid nymia-stripe-grid">
            <?php wp_nonce_field('nymia_footer_menu_settings', 'nymia_footer_menu_nonce'); ?>
            
            <section class="nymia-card nymia-footer-description-card">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Footer Description', 'nymia'); ?></h2>
                    <p><?php esc_html_e('This text appears below the logo in the footer.', 'nymia'); ?></p>
                </header>
                <div class="nymia-form-field">
                    <label for="nymia_footer_description"><?php esc_html_e('Description Text', 'nymia'); ?></label>
                    <textarea id="nymia_footer_description" name="nymia_footer_description" rows="2" class="nymia-form-input"><?php echo esc_textarea($footer_description); ?></textarea>
                    <p class="description"><?php esc_html_e('Enter a brief description that will appear in the footer below your logo.', 'nymia'); ?></p>
                </div>
            </section>

            <section class="nymia-card nymia-footer-columns-card" style="grid-column: span 2;">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Footer Menu Columns', 'nymia'); ?></h2>
                    <p><?php esc_html_e('Manage footer menu columns and links. You can add, remove, and reorder columns and links.', 'nymia'); ?></p>
                </header>
                
                <div id="nymia-footer-columns" class="nymia-footer-columns-list">
                    <?php foreach ($footer_columns as $col_index => $column): ?>
                    <div class="nymia-footer-column-item" data-column-index="<?php echo esc_attr($col_index); ?>">
                        <div class="nymia-footer-column-header">
                            <div class="nymia-form-field" style="flex: 1; margin: 0;">
                                <input type="text" 
                                       name="footer_menu_columns[<?php echo esc_attr($col_index); ?>][title]" 
                                       value="<?php echo esc_attr($column['title']); ?>" 
                                       placeholder="<?php esc_attr_e('Column Title', 'nymia'); ?>" 
                                       class="nymia-form-input" 
                                       required>
                            </div>
                            <button type="button" class="nymia-btn-danger nymia-remove-column">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 4L12 12M12 4L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <?php esc_html_e('Remove', 'nymia'); ?>
                            </button>
                        </div>
                        
                        <div class="nymia-footer-links-list">
                            <?php if (!empty($column['links'])): ?>
                                <?php foreach ($column['links'] as $link_index => $link): ?>
                                <div class="nymia-footer-link-item">
                                    <div class="nymia-form-field" style="flex: 1; margin: 0;">
                                        <input type="text" 
                                               name="footer_menu_columns[<?php echo esc_attr($col_index); ?>][links][<?php echo esc_attr($link_index); ?>][text]" 
                                               value="<?php echo esc_attr($link['text']); ?>" 
                                               placeholder="<?php esc_attr_e('Link Text', 'nymia'); ?>" 
                                               class="nymia-form-input" 
                                               required>
                                    </div>
                                    <div class="nymia-form-field" style="flex: 1; margin: 0;">
                                        <input type="url" 
                                               name="footer_menu_columns[<?php echo esc_attr($col_index); ?>][links][<?php echo esc_attr($link_index); ?>][url]" 
                                               value="<?php echo esc_attr($link['url']); ?>" 
                                               placeholder="<?php esc_attr_e('Link URL', 'nymia'); ?>" 
                                               class="nymia-form-input" 
                                               required>
                                    </div>
                                    <button type="button" class="nymia-btn-danger nymia-remove-link">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.5 3.5L10.5 10.5M10.5 3.5L3.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="nymia-btn-secondary nymia-add-link" data-column-index="<?php echo esc_attr($col_index); ?>">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php esc_html_e('Add Link', 'nymia'); ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="nymia-btn-secondary nymia-add-column">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <?php esc_html_e('Add Column', 'nymia'); ?>
                </button>
            </section>

            <div class="nymia-form-actions">
                <?php submit_button(__('Save Footer Menu Settings', 'nymia'), 'primary', 'nymia_save_footer_menu', false); ?>
            </div>
        </form>

        <form method="post" action="" class="nymia-grid nymia-stripe-grid nymia-contact-info-form">
            <?php wp_nonce_field('nymia_contact_info_settings', 'nymia_contact_info_nonce'); ?>
            
            <section class="nymia-card" style="grid-column: span 2;">
                <header class="nymia-card-header">
                    <h2><?php esc_html_e('Contact Information', 'nymia'); ?></h2>
                    <p><?php esc_html_e('This information will be displayed on the Contact Us page.', 'nymia'); ?></p>
                </header>
                
                <div class="nymia-form-field">
                    <label for="nymia_contact_email_contact"><?php esc_html_e('Email Address', 'nymia'); ?></label>
                    <input type="email" 
                           id="nymia_contact_email_contact" 
                           name="nymia_contact_email" 
                           value="<?php echo esc_attr($contact_email); ?>" 
                           class="nymia-form-input"
                           placeholder="<?php esc_attr_e('info@example.com', 'nymia'); ?>">
                    <p class="description"><?php esc_html_e('Primary contact email address.', 'nymia'); ?></p>
                </div>
                
                <div class="nymia-form-field">
                    <label for="nymia_contact_phone_contact"><?php esc_html_e('Phone Number', 'nymia'); ?></label>
                    <input type="text" 
                           id="nymia_contact_phone_contact" 
                           name="nymia_contact_phone" 
                           value="<?php echo esc_attr($contact_phone); ?>" 
                           class="nymia-form-input"
                           placeholder="<?php esc_attr_e('+1 (123) 456-7890', 'nymia'); ?>">
                    <p class="description"><?php esc_html_e('Contact phone number with country code.', 'nymia'); ?></p>
                </div>
                
                <div class="nymia-form-field">
                    <label for="nymia_contact_address_contact"><?php esc_html_e('Physical Address', 'nymia'); ?></label>
                    <textarea id="nymia_contact_address_contact" 
                              name="nymia_contact_address" 
                              rows="3" 
                              class="nymia-form-input"
                              placeholder="<?php esc_attr_e('123 Street Name, City, State, ZIP Code, Country', 'nymia'); ?>"><?php echo esc_textarea($contact_address); ?></textarea>
                    <p class="description"><?php esc_html_e('Full physical address for your business or organization.', 'nymia'); ?></p>
                </div>
                
                <div class="nymia-form-actions">
                    <?php submit_button(__('Save Contact Information', 'nymia'), 'primary', 'nymia_save_contact_info', false); ?>
                </div>
            </section>
        </form>
    </div>

<script>
jQuery(document).ready(function($) {
    let columnIndex = <?php echo count($footer_columns); ?>;
    let linkIndices = {};
    
    <?php foreach ($footer_columns as $col_index => $column): ?>
    linkIndices[<?php echo esc_js($col_index); ?>] = <?php echo !empty($column['links']) ? count($column['links']) : 0; ?>;
    <?php endforeach; ?>
    
    // Add new column
    $('.nymia-add-column').on('click', function() {
        const columnHtml = `
            <div class="nymia-footer-column-item" data-column-index="${columnIndex}">
                <div class="nymia-footer-column-header">
                    <div class="nymia-form-field" style="flex: 1; margin: 0;">
                        <input type="text" 
                               name="footer_menu_columns[${columnIndex}][title]" 
                               value="" 
                               placeholder="<?php esc_attr_e('Column Title', 'nymia'); ?>" 
                               class="nymia-form-input" 
                               required>
                    </div>
                    <button type="button" class="nymia-btn-danger nymia-remove-column">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 4L12 12M12 4L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <?php esc_html_e('Remove', 'nymia'); ?>
                    </button>
                </div>
                <div class="nymia-footer-links-list"></div>
                <button type="button" class="nymia-btn-secondary nymia-add-link" data-column-index="${columnIndex}">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <?php esc_html_e('Add Link', 'nymia'); ?>
                </button>
            </div>
        `;
        $('#nymia-footer-columns').append(columnHtml);
        linkIndices[columnIndex] = 0;
        columnIndex++;
    });
    
    // Remove column
    $(document).on('click', '.nymia-remove-column', function() {
        if (confirm('<?php echo esc_js(__('Are you sure you want to remove this column?', 'nymia')); ?>')) {
            $(this).closest('.nymia-footer-column-item').remove();
        }
    });
    
    // Add link
    $(document).on('click', '.nymia-add-link', function() {
        const colIndex = $(this).data('column-index');
        const linkIndex = linkIndices[colIndex] || 0;
        const linkHtml = `
            <div class="nymia-footer-link-item">
                <div class="nymia-form-field" style="flex: 1; margin: 0;">
                    <input type="text" 
                           name="footer_menu_columns[${colIndex}][links][${linkIndex}][text]" 
                           value="" 
                           placeholder="<?php esc_attr_e('Link Text', 'nymia'); ?>" 
                           class="nymia-form-input" 
                           required>
                </div>
                <div class="nymia-form-field" style="flex: 1; margin: 0;">
                    <input type="url" 
                           name="footer_menu_columns[${colIndex}][links][${linkIndex}][url]" 
                           value="" 
                           placeholder="<?php esc_attr_e('Link URL', 'nymia'); ?>" 
                           class="nymia-form-input" 
                           required>
                </div>
                <button type="button" class="nymia-btn-danger nymia-remove-link">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.5 3.5L10.5 10.5M10.5 3.5L3.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        `;
        $(this).siblings('.nymia-footer-links-list').append(linkHtml);
        linkIndices[colIndex] = linkIndex + 1;
    });
    
    // Remove link
    $(document).on('click', '.nymia-remove-link', function() {
        $(this).closest('.nymia-footer-link-item').remove();
    });
});
</script>

<style>
.nymia-footer-menu-wrap {
    max-width: 100%;
}

.nymia-form-input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    box-sizing: border-box;
}

.nymia-form-input:focus {
    border-color: rgba(191, 76, 26, 0.6);
    box-shadow: 0 0 0 3px rgba(191, 76, 26, 0.2);
    outline: none;
}

.nymia-form-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.nymia-footer-description-card .nymia-card-header {
    margin-bottom: 12px;
}

.nymia-footer-description-card .nymia-form-field {
    margin-top: 0;
}

.nymia-footer-description-card textarea.nymia-form-input {
    min-height: 80px;
    resize: vertical;
}

.nymia-footer-description-card {
    grid-column: span 2;
}

.nymia-footer-columns-card {
    grid-column: span 2;
    margin-top: 0;
}

.nymia-footer-columns-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin-top: 24px;
}

.nymia-footer-column-item {
    background: linear-gradient(145deg, rgba(24, 24, 28, 0.6), rgba(15, 15, 17, 0.6));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.2s ease;
}

.nymia-footer-column-item:hover {
    border-color: rgba(255, 255, 255, 0.12);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.nymia-footer-column-header {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 20px;
}

.nymia-footer-links-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}

.nymia-footer-link-item {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 10px;
    transition: all 0.2s ease;
}

.nymia-footer-link-item:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
}

.nymia-btn-secondary,
.nymia-btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.nymia-btn-secondary {
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.nymia-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.18);
    transform: translateY(-1px);
}

.nymia-btn-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.nymia-btn-danger:hover {
    background: rgba(239, 68, 68, 0.25);
    color: #fee2e2;
    border-color: rgba(239, 68, 68, 0.35);
    transform: translateY(-1px);
}

.nymia-btn-secondary svg,
.nymia-btn-danger svg {
    flex-shrink: 0;
}

.nymia-form-actions {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.nymia-form-actions .button-primary {
    margin: 0;
}

.nymia-contact-info-form {
    margin-top: 48px;
}

@media (max-width: 768px) {
    .nymia-footer-column-header {
        flex-direction: column;
        gap: 12px;
    }
    
    .nymia-footer-link-item {
        flex-direction: column;
        align-items: stretch;
    }
    
    .nymia-footer-link-item .nymia-form-field {
        width: 100%;
    }
}
</style>
<?php
}

