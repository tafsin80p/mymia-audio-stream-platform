<?php
/**
 * Template for displaying pages
 */

get_header(); ?>

<div class="nymia-container">
    <?php get_sidebar(); ?>
    
    <div class="nymia-main">
        <?php get_template_part('template-parts/header'); ?>
        
        <?php get_template_part('template-parts/filters'); ?>
        
        <div class="nymia-content-wrapper">
            <div class="nymia-content">
                <?php
                while (have_posts()) :
                    the_post();
                    get_template_part('template-parts/content', 'page');
                endwhile;
                ?>
            </div>
            
            <?php get_template_part('template-parts/sidebar-right'); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
