<?php global $theme_op; ?>
<div class="archive-excerpt-wrap">
    <div <?php post_class('archive-excerpt'); ?> id="post-<?php the_ID(); ?>">
        <div class="thumbnail">
            <a href="<?php the_permalink(); ?>">
            <?php if(has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('colorized-entries'); ?>
            <?php else : ?>
            <img src="<?php echo $theme_op['featured_image']; ?>" />
            <?php endif; ?>
            </a>
        </div>
        <div class="title">
            <?php $h_thumb = get_post_meta(get_the_ID(), 'h_thumb', true); ?>
            <h2 class="entry-title">
                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php _e('Permalink to: ', 'colorized'); echo esc_attr(get_the_title()); ?>">
                    <?php if($h_thumb) : ?>
                    <img src="<?php echo $h_thumb; ?>" class="h_title_img" />
                    <?php else : ?>
                    <img src="<?php echo $theme_op['default_icon']; ?>" class="h_title_img" />
                    <?php endif; ?>
                    <?php the_title(); ?>
                </a>
            </h2>
        </div>

        <div class="excerpt">
            <?php the_excerpt(); ?>
        </div>
        <div class="read-more">
            <a href="<?php the_permalink(); ?>"><?php _e('Read More', 'colorized'); ?></a>
        </div>
        <?php if(($reg = get_post_meta(get_the_ID(), 'h_reg', true)) && ($reg_d = get_post_meta(get_the_ID(), 'h_reg_d', true))) : ?>
        <div class="register">
            <a href="<?php echo esc_attr($reg); ?>"><?php echo $reg_d; ?></a>
        </div>
        <?php endif; ?>
        <div class="clear"></div>
    </div>
</div>
