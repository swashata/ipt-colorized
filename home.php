<?php get_header(); ?>
<?php global $theme_op; ?>
        <?php if($theme_op['show_piecemaker'] == 'yes') : ?>
        <!-- Piecemaker -->
        <div id="piecemaker">
            <div class="ninesixty">
                <div id="flashcontent">
                    <p>You need to <a href="http://www.adobe.com/products/flashplayer/" target="_blank">upgrade your Flash Player</a> to version 10 or newer.</p>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/piecemaker/swfobject/swfobject.js"></script>

        <script type="text/javascript">
            var flashvars = {};
            flashvars.xmlSource = "<?php bloginfo('template_url'); ?>/piecemaker/piecemaker.xml.php?nonsense=file.xml";
            flashvars.cssSource = "<?php bloginfo('template_url'); ?>/piecemaker/piecemaker.css";

            var params = {};
            params.play = "true";
            params.menu = "false";
            params.scale = "showall";
            params.wmode = "transparent";
            params.allowfullscreen = "true";
            params.allowscriptaccess = "always";
            params.allownetworking = "all";
            swfobject.embedSWF("<?php bloginfo('template_url'); ?>/piecemaker/piecemaker.swf", "flashcontent", "960", "450", "10", null, flashvars, params, null);
        </script>

        <!-- End Piecemaker -->
        <?php endif; ?>

        <!-- Main -->
        <div id="main">
            <div class="ninesixty">
                <div id="home_title">
                    <?php global $theme_op; ?>
                    <div id="breadcrumb">
                        <p><?php echo htmlspecialchars_decode($theme_op['breadcrumb']); ?></p>
                    </div>
                    <?php if($theme_op['countdown']['enabled'] == true) : ?>
                    <div id="countdown">
                        <?php echo date('F j, Y', strtotime($theme_op['countdown']['end'])); ?>
                    </div>
                    <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        var until = new Date('<?php echo date('F j, Y H:i:s', strtotime($theme_op['countdown']['end'])); ?>');
                        //alert(until);
                        $('#countdown').countdown({until: until, format: 'DHMS'});
                    });
                    </script>
                    <?php endif; ?>
                    <div id="home_title_content">
                        <h2><?php echo htmlspecialchars_decode($theme_op['title']); ?></h2>
                        <div id="home_description_content">
                            <?php echo wpautop(wptexturize($theme_op['description'])); ?>
                        </div>
                    </div>
                </div>

                <div id="home_content">
                    <?php if(have_posts()) : ?>
                    <div id="home_contents">
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
