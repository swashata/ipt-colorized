<?php
/**
 * The library of the loader class
 */

class ipt_colorized_theme_loader {
    static $abspath;
    static $absdir;
    static $version;

    public function __construct($abspath, $version) {
        self::$abspath = $abspath;
        self::$absdir = dirname($abspath);
        self::$version = $version;
        load_theme_textdomain('colorized', self::$absdir . '/translations');
        $this->check_op();

        global $theme_op;
        $theme_op = get_option('ipt_colorized_theme_op');

        if(is_admin()) {
            new ipt_colorized_admin();
        } else {
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_script_style'));
        }

        add_action('init', array(&$this, 'init_hooks'));
        add_action('widgets_init', array(&$this, 'widgets_init'));
        add_action('after_setup_theme', array(&$this, 'after_setup_theme_hooks'));
        add_action('excerpt_length', array(&$this, 'excerpt_length'));
        add_action('excerpt_more', array(&$this, 'excerpt_more'));

        global $content_width;
        if(!isset($content_width))
            $content_width = 920;
    }


    public function init_hooks() {
        register_nav_menu('topnav', __('Primary Navigation', 'colorized'));
        wp_register_script('ipt_colorize_jfloat', get_template_directory_uri() . '/js/jqfloat.min.js', array('jquery'), self::$version);
    }

    public function widgets_init() {
        register_widget('ipt_colorized_widget_sponsors');
        register_sidebar(array(
            'name' => __('Footer', 'colorized'),
            'id' => 'footer-sidebar',
            'description' => __('Primarily for the Sponsors Widget', 'colorized'),
            'before_widget' => '<div id="%1$s" class="widget-container %2$s">',
            'after_widget' => '<div class="clear"></div></div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ));
    }

    /**
     * After theme setup hooks
     */
    public function after_setup_theme_hooks() {
        // This theme uses post thumbnails
	add_theme_support('post-thumbnails');

        add_image_size('colorized-entries', 380, 230, true);

	// Add default posts and comments RSS feed links to head
	add_theme_support('automatic-feed-links');

        //Allow custom background to be added
        add_theme_support('custom-background', array(
            'default-color'          => 'f09139',
            'default-image'          => get_stylesheet_directory_uri() . '/images/background.jpg',
            'background-repeat'      => 'no-repeat',
            'background-position-y'    => 'center',
            'background-position-x'  => 'center',
            'background-attachment' => 'fixed',
        ));

        add_theme_support('custom-header', array(
            'default-image' => '%s/images/logo.png',
            'width'         => 466,
            'height'        => 125,
            'flex-height'   => false,
            'flex-width'    => true,
            'header-text'   => false,
        ));

        add_editor_style();
    }

    public function excerpt_length($length) {
        return 40;
    }

    public function excerpt_more($more) {
        return '&hellip;';
    }

    public function admin_enqueue_script_style() {


    }

    public function enqueue_script_style() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('colorized-sf', get_stylesheet_directory_uri() . '/js/superfish.js', array('jquery'), '1.0.0');
        wp_enqueue_script('colorized-cd', get_stylesheet_directory_uri() . '/js/jquery.countdown.min.js', array('jquery'), '1.0.0');
        wp_enqueue_script('colorized-bx', get_stylesheet_directory_uri() . '/js/jquery.bxSlider.min.js', array('jquery'), '1.0.0');

        wp_enqueue_script('colorized-custom', get_stylesheet_directory_uri() . '/js/colorized.js', array('jquery'), '1.0.0');

    }

    private function check_op() {
        if(!get_option('ipt_colorized_theme_op')) {
            $ipt_colorized_theme_op = array(
                'breadcrumb' => __('My Site', 'colorized'),
                'countdown' => array(
                    'enabled' => true,
                    'end' => '',
                ),
                'title' => __('My Awesome Site', 'colorized'),
                'description' => __('A catch line goes here', 'colorized'),
                'footer' => __('&copy; Copyright 2012 - All rights reserved', 'colorized'),
                'bubble' => 'yes',
                'bubble_max' => '15',
                'bubble_min' => '10',
                'featured_image' => get_template_directory_uri() . '/images/no-preview.png',
                'default_icon' => get_template_directory_uri() . '/images/no-icon.png',
                'table_logo' => get_template_directory_uri() . '/images/table-logo.png',
                'show_credit' => 'no',
                'show_piecemaker' => 'yes',
            );

            add_option('ipt_colorized_theme_op', $ipt_colorized_theme_op);
            set_theme_mod('background_attachment', 'fixed');
        }

        if(!get_option('ipt_colorized_piecemaker_op')) {
            $ipt_colorized_piecemaker_op = array(
                'contents' => array(),
                'transitions' => array(),
            );

            for($i = 0; $i < 10; $i++) {
                $ipt_colorized_piecemaker_op['contents'][$i] = array(
                    'type' => 'Image',
                    'source' => '',
                    'image' => '',
                    'title' => '',
                    'text' => '',
                    'url' => '',
                );
                $ipt_colorized_piecemaker_op['transitions'][$i] = array(
                    'Pieces' => '9',
                    'Time' => '1.2',
                    'Transition' => 'easeInOutBack',
                    'Delay' => '0.1',
                    'DepthOffset' => '300',
                    'CubeDistance' => '30',
                );
            }

            add_option('ipt_colorized_piecemaker_op', $ipt_colorized_piecemaker_op);
        }

        if(!get_option('ipt_colorized_piecemaker_settings')) {
            $ipt_colorized_piecemaker_settings = array(
                'LoaderColor' => '0x333333',
                'InnerSideColor' => '0x222222',
                'SlideShadowAlpha' => '0.8',
                'DropShadowAlpha' => '0.7',
                'DropShadowDistance' => '25',
                'DropShadowScale' => '0.95',
                'DropShadowBlurX' => '40',
                'DropShadowBlurY' => '4',
                'MenuDistanceX' => '20',
                'MenuDistanceY' => '50',
                'MenuColor1' => '0x999999',
                'MenuColor2' => '0x333333',
                'MenuColor3' => '0xFFFFFF',
                'ControlSize' => '100',
                'ControlDistance' => '20',
                'ControlColor1' => '0X222222',
                'ControlColor2' => '0xFFFFFF',
                'ControlAlpha' => '0.8',
                'ControlAlphaOver' => '0.95',
                'ControlsX' => '400',
                'ControlsY' => '280',
                'ControlsAlign' => 'center',
                'TooltipHeight' => '31',
                'TooltipColor' => '0x222222',
                'TooltipTextY' => '5',
                'TooltipTextStyle' => 'P-Italic',
                'TooltipTextColor' => '0xFFFFFF',
                'TooltipMarginLeft' => '5',
                'TooltipMarginRight' => '7',
                'TooltipTextSharpness' => '50',
                'TooltipTextThickness' => '-100',
                'InfoWidth' => '400',
                'InfoBackground' => '0XFFFFFF',
                'InfoBackgroundAlpha' => '0.95',
                'InfoMargin' => '15',
                'InfoSharpness' => '0',
                'InfoThickness' => '0',
                'Autoplay' => '10',
                'FieldOfView' => '45',
            );

            add_option('ipt_colorized_piecemaker_settings', $ipt_colorized_piecemaker_settings);
        }

        if(!get_option('ipt_colorized_frontpage_op')) {
            $ipt_colorized_frontpage_op = array();

            for($i = 0; $i < 10; $i++) {
                $ipt_colorized_frontpage_op[$i] = array(
                    'title' => '',
                    'icon' => '',
                    'text' => '',
                    'url' => '',
                );
            }

            add_option('ipt_colorized_frontpage_op', $ipt_colorized_frontpage_op);
        }

        if(!get_option('ipt_colorized_version')) {
            add_option('ipt_colorized_version', self::$version);
        } else {
            $old_version = get_option('ipt_colorized_version');
            if($old_version != self::$version) {
                //update
                switch($old_version) {
                    default:
                    case '1.0.0' :
                        //nothing necessary
                        break;
                }

                update_option('ipt_colorized_version', self::$version);
            }
        }
    }
}
