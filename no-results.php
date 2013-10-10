<?php global $theme_op; ?>
<div class="archive-excerpt-wrap">
    <div class="archive-excerpt" id="post-not-found">
        <div class="thumbnail">
            <img src="<?php echo $theme_op['featured_image']; ?>" />
        </div>
        <div class="title">
            <h2 class="entry-title">
                <img src="<?php echo $theme_op['default_icon']; ?>" class="h_title_img" />
                <?php _e('No Results Found', 'colorized'); ?>
            </h2>
        </div>

        <div class="excerpt">
            <p><?php _e('We Could Not found what you were looking for. Please use the search form below', 'colorized'); ?>.</p>
            <?php get_search_form(); ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
