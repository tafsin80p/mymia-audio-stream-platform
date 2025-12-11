<?php
/**
 * Template Name: Contact Us
 * 
 * Contact Us page template
 * 
 * @package Nymia
 * @version 1.0
 */

get_header(); ?>

<div class="nymia-container">
    <?php 
    if (is_user_logged_in() && (current_user_can('edit_posts') || current_user_can('manage_options'))) {
        get_sidebar(); 
    }
    ?>
    
    <div class="nymia-main<?php echo (!is_user_logged_in() || (!current_user_can('edit_posts') && !current_user_can('manage_options'))) ? ' nymia-main-fullwidth' : ''; ?>">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-content-wrapper">
            <div class="nymia-content">
                <div class="nymia-contact-page">
                    <div class="nymia-contact-header">
                        <h1><?php esc_html_e('Contact Us', 'nymia'); ?></h1>
                        <p class="nymia-contact-subtitle"><?php esc_html_e('We\'d love to hear from you. Get in touch with us!', 'nymia'); ?></p>
                    </div>

                    <div class="nymia-contact-content">
                        <div class="nymia-contact-form-wrapper">
                            <h2><?php esc_html_e('Send us a Message', 'nymia'); ?></h2>
                            <form id="nymia-contact-form" class="nymia-contact-form">
                                <?php wp_nonce_field('nymia_contact_form', 'nymia_contact_nonce'); ?>
                                
                                <div class="nymia-form-row">
                                    <div class="nymia-form-group">
                                        <label for="contact_name"><?php esc_html_e('Name', 'nymia'); ?> *</label>
                                        <input type="text" id="contact_name" name="name" required>
                                    </div>
                                    
                                    <div class="nymia-form-group">
                                        <label for="contact_email"><?php esc_html_e('Email', 'nymia'); ?> *</label>
                                        <input type="email" id="contact_email" name="email" required>
                                    </div>
                                </div>

                                <div class="nymia-form-group">
                                    <label for="contact_subject"><?php esc_html_e('Subject', 'nymia'); ?> *</label>
                                    <input type="text" id="contact_subject" name="subject" required>
                                </div>

                                <div class="nymia-form-group">
                                    <label for="contact_message"><?php esc_html_e('Message', 'nymia'); ?> *</label>
                                    <textarea id="contact_message" name="message" rows="6" required></textarea>
                                </div>

                                <div id="nymia-contact-message" class="nymia-contact-message" style="display: none;"></div>

                                <button type="submit" class="nymia-btn-gradient"><?php esc_html_e('Send Message', 'nymia'); ?></button>
                            </form>
                        </div>

                        <div class="nymia-contact-info">
                            <h2><?php esc_html_e('Contact Information', 'nymia'); ?></h2>
                            
                            <?php
                            $contact_email = get_option('nymia_contact_email', '');
                            $contact_phone = get_option('nymia_contact_phone', '');
                            $contact_address = get_option('nymia_contact_address', '');
                            ?>
                            
                            <?php if ($contact_email): ?>
                            <div class="nymia-contact-info-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <div>
                                    <h3><?php esc_html_e('Email', 'nymia'); ?></h3>
                                    <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($contact_phone): ?>
                            <div class="nymia-contact-info-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <div>
                                    <h3><?php esc_html_e('Phone', 'nymia'); ?></h3>
                                    <a href="tel:<?php echo esc_attr($contact_phone); ?>"><?php echo esc_html($contact_phone); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($contact_address): ?>
                            <div class="nymia-contact-info-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <div>
                                    <h3><?php esc_html_e('Address', 'nymia'); ?></h3>
                                    <p><?php echo esc_html($contact_address); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php get_template_part('template-parts/sidebar-right'); ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#nymia-contact-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $message = $('#nymia-contact-message');
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.text();
        
        $submitBtn.prop('disabled', true).text('<?php echo esc_js(__('Sending...', 'nymia')); ?>');
        $message.hide().removeClass('success error').html('');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'nymia_send_contact_message',
                nonce: $('#nymia_contact_nonce').val(),
                name: $('#contact_name').val(),
                email: $('#contact_email').val(),
                subject: $('#contact_subject').val(),
                message: $('#contact_message').val()
            },
            success: function(response) {
                if (response.success) {
                    $message.html(response.data.message || '<?php echo esc_js(__('Thank you! Your message has been sent.', 'nymia')); ?>')
                        .addClass('success')
                        .show();
                    $form[0].reset();
                } else {
                    $message.html(response.data.message || '<?php echo esc_js(__('Sorry, there was an error sending your message. Please try again.', 'nymia')); ?>')
                        .addClass('error')
                        .show();
                }
                $submitBtn.prop('disabled', false).text(originalText);
            },
            error: function() {
                $message.html('<?php echo esc_js(__('An unexpected error occurred. Please try again later.', 'nymia')); ?>')
                    .addClass('error')
                    .show();
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

<?php get_footer(); ?>


