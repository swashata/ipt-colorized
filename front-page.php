<?php
/**
 * Template for static type front page
 */
if(is_home()) {
    get_template_part('home');
    return;
}
?>
<?php get_header(); ?>
<?php global $theme_op; ?>
<?php $carousels = get_option('ipt_colorized_frontpage_op'); ?>
<!-- Main -->
        <div id="main" class="static-front">
            <div class="ninesixty">
                <?php if(have_posts()) : the_post(); ?>
                <?php remove_filter('the_content', 'wpautop'); ?>
                <div id="page-title">
                    <h1><?php the_content(); ?></h1>
                </div>
                <?php add_filter('the_content', 'wpautop'); ?>
                <?php endif; ?>
                <div id="bxcarousel-wrap">
                    <ul id="bxcarousel">
                        <?php foreach($carousels as $carousel) : ?>
                        <?php if('' != $carousel['title'] && '' != $carousel['icon']) : ?>
                        <li><div>
                            <?php if('' != $carousel['url']) : ?>
                            <a href="<?php echo $carousel['url'] ?>" target="_blank"><img src="<?php echo $carousel['icon'] ?>" /></a>
                            <h2><a href="<?php echo $carousel['url'] ?>" target="_blank"><?php echo $carousel['title'] ?></a></h2>
                            <?php else : ?>
                            <img src="<?php echo $carousel['icon'] ?>" />
                            <h2><?php echo $carousel['title'] ?></h2>
                            <?php endif; ?>
                            <?php if('' != $carousel['text']) : ?>
                            <p><?php echo $carousel['text']; ?></p>
                            <?php endif; ?>
                        </div></li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Main -->

<?php get_footer(); ?>
