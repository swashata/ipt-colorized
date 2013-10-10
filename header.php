<?php
/**
 * Header File
 * Colorized Theme
 * Version 1.0
 */

global $theme_op;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
    <head profile="http://gmpg.org/xfn/11">
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
    <title>
        <?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

        if(defined('WPSEO_VERSION')) {
            wp_title('');
        } else {
            wp_title( '|', true, 'right' );

            if(is_home() || is_front_page())
                bloginfo( 'name' );

            // Add the blog name.
            //bloginfo( 'name' );

            // Add the blog description for the home/front page.
            $site_description = get_bloginfo( 'description', 'display' );
            if ( $site_description && ( is_home() || is_front_page() ) )
                    echo " | $site_description";

            // Add a page number if necessary:
            if ( $paged >= 2 || $page >= 2 )
                    echo ' | ' . sprintf( __( 'Page %s', 'colorized' ), max( $paged, $page ) );
        }

	?>
    </title>
    <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>?version=<?php echo get_option('ipt_colorized_version'); ?>" />
    <link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
    <link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <?php
    if($theme_op['bubble'] == 'yes') {
        wp_enqueue_script('ipt_colorize_jfloat');
        wp_localize_script('ipt_colorize_jfloat', 'colBubble', array(
            'max' => $theme_op['bubble_max'] * 1000,
            'min' => $theme_op['bubble_min'] * 1000,
        ));
    }

    if(is_singular() && get_option('thread_comments'))
        wp_enqueue_script('comment-reply');
    wp_head();

    ?>
    <?php
    $header_image_width  = get_custom_header()->width;
    $header_image_height = get_custom_header()->height;
    $background_info = get_theme_mod('background_attachment');
    ?>
    <style type="text/css">
        #header #logo {
            width: <?php echo ($header_image_width/2); ?>px;
        }
        #header #logo img:hover {
            margin-left: -<?php echo ($header_image_width/2); ?>px;
        }
        <?php if($theme_op['table_logo'] != '') : ?>
        #content table {
            background-image: url('<?php echo $theme_op['table_logo']; ?>');
        }
        <?php endif; ?>
        <?php if($background_info == 'fixed') : ?>
        body {
            background-size: cover;
        }
        <?php endif; ?>
    </style>
    </head>
    <body <?php body_class(); ?>>
        <!-- Header -->
        <div id="header">
            <div class="ninesixty">
                <div id="logo">
                    <a href="<?php echo home_url('/'); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">

                        <img src="<?php header_image(); ?>"  width="<?php echo $header_image_width; ?>" height="<?php echo $header_image_height; ?>" alt="" />
                    </a>
                </div>
                <div id="nav">
                    <?php wp_nav_menu(array('container_class' => 'top-nav', 'theme_location' => 'topnav')); ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <!-- End Header -->
