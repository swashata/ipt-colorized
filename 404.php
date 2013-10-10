<?php get_header(); ?>

<div class="ninesixty" style="text-align: center; margin: 20px auto;">
    <a style="display: block;" title="<?php _e('The page you are looking for no longer exists. Please head back to the homepage by clicking this link', 'colorized'); ?>" href="<?php echo home_url('/'); ?>">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/404.png" />
    </a>
</div>

<?php get_footer(); ?>
