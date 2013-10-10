<?php get_header(); ?>
<div id="main">
    <div class="ninesixty">
        <div id="content">
            <div id="content_inner">
                <div id="post_content">
                    <?php if(have_posts()) : the_post(); ?>
                    <div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                        <h1 class="post-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h1>
                        <div class="entry-content">
                            <?php the_content(); ?>
                            <div class="clear"></div>
                            <?php if(($reg = get_post_meta(get_the_ID(), 'h_reg', true)) && ($reg_d = get_post_meta(get_the_ID(), 'h_reg_d', true))) : ?>
                            <div class="register">
                                <a href="<?php echo esc_attr($reg); ?>"><?php echo $reg_d; ?></a>
                            </div>
                            <?php endif; ?>
                            <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'colorized' ), 'after' => '</div>' ) ); ?>
                        </div>
                        <div class="entry-utility">
                                <?php colorized_posted_in(); ?>
                                <?php edit_post_link( __( 'Edit', 'colorized' ), '<span class="edit-link">', '</span>' ); ?>
                        </div><!-- .entry-utility -->
                    </div>
                    <div id="nav-below" class="navigation">
                        <div class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'colorized' ) . '</span> %title' ); ?></div>
                        <div class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'colorized' ) . '</span>' ); ?></div>
                    </div><!-- #nav-below -->
                    <?php comments_template( '', true ); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
