<?php get_header(); ?>

        <!-- Main -->
        <div id="main">
            <div class="ninesixty">
                <div id="archive_content">
                    <?php if(have_posts()) : ?>
                    <div id="archive_contents">
                        <?php while(have_posts()) : the_post(); ?>
                        <?php get_template_part('entry', 'home'); ?>
                        <?php endwhile; ?>
                    </div>
                    <?php else : ?>
                    <?php get_template_part('no-results', 'home'); ?>
                    <?php endif; ?>
                </div>
                <?php if (  $wp_query->max_num_pages > 1 ) : ?>
                <div id="nav-below" class="navigation bottom-navigation">
                        <div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'colorized' ) ); ?></div>
                        <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'colorized' ) ); ?></div>
                </div><!-- #nav-below -->
                <?php endif; ?>
            </div>
        </div>
        <!-- End Main -->
<?php get_footer(); ?>
