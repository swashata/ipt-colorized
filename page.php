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
                            <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'colorized' ), 'after' => '</div>' ) ); ?>
                        </div>
                        <div class="entry-utility">
                                <?php edit_post_link( __( 'Edit', 'colorized' ), '<span class="edit-link">', '</span>' ); ?>
                        </div><!-- .entry-utility -->
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
