<?php
/**
 * Template part for displaying content when no posts are found
 */
?>

<section class="no-results not-found">
    <header class="page-header">
        <h1 class="page-title">
            <?php 
            // Check if it's a user search
            if (isset($_GET['user_not_found'])) {
                $username = esc_html($_GET['user_not_found']);
                printf(__('User "@%s" not found', 'nymia'), $username);
            } else {
                esc_html_e('Nothing here', 'nymia');
            }
            ?>
        </h1>
    </header>

    <div class="page-content">
        <?php if (isset($_GET['user_not_found'])): ?>
            <p><?php printf(__('The user "@%s" could not be found. Please check the username and try again.', 'nymia'), esc_html($_GET['user_not_found'])); ?></p>
            <p style="margin-top: 16px;">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="nymia-btn-gradient" style="display: inline-block; padding: 12px 24px;">
                    <?php esc_html_e('Go to Home', 'nymia'); ?>
                </a>
            </p>
        <?php else: ?>
            <p><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for.', 'nymia'); ?></p>
        <?php endif; ?>
    </div>
</section>
