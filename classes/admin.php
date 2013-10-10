<?php

class ipt_colorized_admin {
    /**
     * Duplicates the $_POST content and properly process it
     * Holds the typecasted (converted int and floats properly and escaped html) value after the constructor has been called
     * @var array
     */
    var $post = array();

    /**
     * The nonce for admin-post.php
     * Should be set the by extending class
     * @var string
     */
    var $action_nonce;

    /**
     * Holds the hook of this page
     * @var string Pagehook
     * Should be set during the construction
     */
    var $pagehook;

    /**
     * The URL of the admin page icon
     * Should be set by the extending class
     * @var string
     */
    var $icon_url;

    /**
     * This gets passed directly to current_user_can
     * Used for security and should be set by the extending class
     * @var string
     */
    var $capability;


    /**
     * Holds the post result message string
     * Each entry is an associative array with the following options
     *
     * $key : The code of the post_result value =>
     *
     *      'type' => 'update' : The class of the message div update | error
     *
     *      'msg' => '' : The message to be displayed
     *
     * @var array
     */
    var $post_result = array();

    /**
     * The action value to be used for admin-post.php
     * This is generated automatically by appending _post_action to the action_nonce variable
     * @var string
     */
    var $admin_post_action;

    var $op;


    public function __construct() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->post = $_POST;

            array_walk_recursive ($this->post, array($this, 'stripslashes_gpc'));

            array_walk_recursive ($this->post, array($this, 'htmlspecialchar_ify'));
        }

        $this->post_result = array(
            1 => array(
                'type' => 'update',
                'msg' => __('Successfully saved the options', 'colorized'),
            ),
            2 => array(
                'type' => 'error',
                'msg' => __('Either you have not changed anything or some error has occured. Please contact the developer', 'colorized'),
            ),
            3 => array(
                'type' => 'update',
                'msg' => __('The Master Reset was successful', 'colorized'),
            ),
            4 => array(
                'type' => 'error',
                'msg' => __('Some errors has occured uploading the files. Please check.', 'colorized'),
            ),
            5 => array(
                'type' => 'update',
                'msg' => __('Theme Options reset was successful', 'colorized'),
            ),
            6 => array(
                'type' => 'update',
                'msg' => __('Piecemaker Settings reset was successful', 'colorized'),
            ),
            7 => array(
                'type' => 'update',
                'msg' => __('Piecemaker Content reset was successful', 'colorized'),
            ),
            8 => array(
                'type' => 'update',
                'msg' => __('Static Frontpage Carousel reset was successful', 'colorized'),
            ),
        );

        $this->admin_post_action = $this->action_nonce . '_post_action';
        $this->icon_url = get_stylesheet_directory_uri() . '/admin-static/images/colorized.png';
        $this->capability = 'manage_options';

        //register admin_menu hook
        add_action('admin_menu', array(&$this, 'admin_menu'));

        //register admin-post.php hook
        add_action('admin_post_' . $this->admin_post_action, array(&$this, 'save_post'));

        //register post meta
        add_action('load-post.php', array(&$this, 'colorize_metabox_setup'));
        add_action('load-post-new.php', array(&$this, 'colorize_metabox_setup'));

        $this->op = get_option('ipt_colorized_theme_op');

    }

    /*____________________________________META BOXES___________________________________________*/
    public function colorize_metabox_setup() {
        add_action('add_meta_boxes', array(&$this, 'colorize_meta_init'));
        add_action('save_post', array(&$this, 'colorize_meta_save'), 10, 2);
    }
    public function colorize_meta_init() {
        add_meta_box('colorize-meta', __('Colorize Title & Registration Shortcuts', 'colorized'), array(&$this, 'colorize_meta'), 'post', 'normal', 'default');
    }
    public function colorize_meta($object, $box) {
        wp_nonce_field('colorize_meta', 'colorize_meta_nonce');
        ?>
<p>
    <label for="colorize_h_thumb"><?php _e('Title Thubmnail URL', 'colorize'); ?></label><br />
    <?php $this->print_input_text('colorize[h_thumb]', get_post_meta($object->ID, 'h_thumb', true), 'large-text code'); ?>
</p>
<p>
    <label for="colorize_h_reg"><?php _e('Registration URL', 'colorize'); ?></label><br />
    <?php $this->print_input_text('colorize[h_reg]', get_post_meta($object->ID, 'h_reg', true), 'large-text code'); ?>
</p>
<p>
    <label for="colorize_h_thumb"><?php _e('Registration Description', 'colorize'); ?></label><br />
    <?php $this->print_input_text('colorize[h_reg_d]', get_post_meta($object->ID, 'h_reg_d', true), 'large-text'); ?>
</p>
        <?php
    }
    public function colorize_meta_save($post_id, $post) {
        if(!wp_verify_nonce($_POST['colorize_meta_nonce'], 'colorize_meta'))
            return $post_id;

        foreach(array('h_thumb', 'h_reg', 'h_reg_d') as $key)
            update_post_meta($post_id, $key, $this->post['colorize'][$key]);
    }

    /*______________________________________SYSTEM METHODS______________________________________*/

    /**
     * Hook to the admin menu
     * Should be overriden and also the hook should be saved in the $this->pagehook
     * In the end, the parent::admin_menu() should be called for load to hooked properly
     */
    public function admin_menu() {
        $this->pagehook = add_theme_page(__('Colorized Theme Option', 'colorized'), __('Theme Options', 'colorized'), 'manage_options', 'ipt_colorized', array(&$this, 'index'));
        add_action('load-' . $this->pagehook, array(&$this, 'on_load_page'));
        //$this->pagehook = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
        //do the above or similar in the overriden callback function
    }

    /**
     * Use this to generate the admin page
     * always call parent::index() so the save post is called
     * also call $this->index_foot() after the generation of page (the last line of this function)
     * to give some compatibility (mainly with the metaboxes)
     * @access public
     */
    public function index() {
        $this->index_head(__('Colorized Theme Options', 'colorized'));
        ?>
<div id="dashboard-widgets">
    <div class="metabox-holder">
        <?php $this->print_metabox_containers('normal'); ?>
        <?php $this->print_metabox_containers('side'); ?>
    </div>
    <div class="clear"></div>
</div>
        <?php
        $this->index_foot();
    }

    public function meta_theme() {
        ?>
<ul class="metabox-tabs">
    <li class="tab colorized-theme-op-home">
        <a href="javascript:void(null);" class="active"><?php _e('Dynamic Home Page', 'colorized'); ?></a>
    </li>
    <li class="tab colorized-theme-op-layout">
        <a href="javascript:void(null);"><?php _e('Layout', 'colorized'); ?></a>
    </li>
    <li class="tab colorized-theme-op-footer">
        <a href="javascript:void(null);"><?php _e('Footer', 'colorized'); ?></a>
    </li>
</ul>
<div class="colorized-theme-op-home">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="op_show_piecemaker"><?php _e('Show Piecemaker', 'colorized'); ?></label>
                </th>
                <td>
                    <select name="op[show_piecemaker]" id="op_show_piecemaker">
                        <?php $this->print_select_op(array('yes' => __('Yes', 'colorized'), 'no' => __('No', 'colorized')), $this->op['show_piecemaker'], true); ?>
                    </select>
                </td>
                <td>
                    <span class="help">
                        <?php _e('If you wish to show piecemaker on homepage, then select yes.', 'colorized'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_title"><?php _e('Home Page Title', 'colorized'); ?></label>
                </th>
                <td>
                    <?php $this->print_input_text('op[title]', $this->op['title']); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('Enter the title that will be shown on the home page.', 'colorized'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_description"><?php _e('Home Page Description', 'colorized'); ?></label>
                </th>
                <td>
                    <?php wp_editor($this->op['description'], 'op_description', array(
                        'media_buttons' => false,
                        'textarea_name' => 'op[description]',
                        'teeny' => false,
                        'textarea_rows' => 5,
                    )); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('Enter the short description that will be shown on the home page', 'colorized'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_countdown"><?php _e('Countdown Timer', 'colorized'); ?></label>
                </th>
                <td>
                    <label for="op_countdown_enabled">
                        <?php $this->print_checkbox('op[countdown][enabled]', 'true', $this->op['countdown']['enabled']); ?>
                        <?php _e('Enabled?', 'colorized'); ?>
                    </label>
                    <br />
                    <?php $this->print_datetimepicker('op[countdown][end]', $this->op['countdown']['end']); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('If you wish to show countdown timer on front page, then put it here', 'colorized'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_breadcrumb"><?php _e('BreadCrumb Prefix', 'colorized'); ?></label>
                </th>
                <td>
                    <?php $this->print_input_text('op[breadcrumb]', $this->op['breadcrumb']); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('If you wish to show anything before the breadcrumb, then put it here', 'colorized'); ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="colorized-theme-op-layout">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="op_bubble"><?php _e('Show Bubble on Background', 'colorized'); ?></label>
                </th>
                <td>
                    <select name="op[bubble]" id="op_bubble">
                        <?php $this->print_select_op(array('yes' => __('Yes', 'colorized'), 'no' => __('No', 'colorized')), $this->op['bubble'], true); ?>
                    </select>
                </td>
                <td>
                    <span class="help">
                        <?php _e('If you wish to show floating bubbles on the background image, then select yes.', 'colorized'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_bubble_max"><?php _e('Travelling Time Range', 'colorized'); ?></label>
                </th>
                <td>
                    <?php $this->print_ui_slider_range('op[bubble_min]', 'op[bubble_max]', array($this->op['bubble_min'], $this->op['bubble_max']), 50, 10, 1); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('The amount of time in second which will be maximum a bubble will take to traverse it\'s path. Should be more than ', 'colorized') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_featured_image"><?php _e('Default Preview Image (380X230)', 'colorized') ?></label>
                </th>
                <td>
                    <?php $this->print_uploadbutton('op[featured_image]', $this->op['featured_image']); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('This is the image that will be used if the post has got no featured image set. The size is 380px (width) X 230px (height).') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_default_icon"><?php _e('Default Icon (50X50)', 'colorized') ?></label>
                </th>
                <td>
                    <?php $this->print_uploadbutton('op[default_icon]', $this->op['default_icon']); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('This is the image that will be used if the post has got no icon image set. The size is 50px (width) X 50px (height).') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_table_logo"><?php _e('Table Logo (124X124)', 'colorized') ?></label>
                </th>
                <td>
                    <?php $this->print_uploadbutton('op[table_logo]', $this->op['table_logo']); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('This is the image that will be used behind every table. The size is 124px (width) X 124px (height).') ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="colorized-theme-op-footer">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="op_footer"><?php _e('Footer Text', 'colorized'); ?></label>
                </th>
                <td>
                    <?php wp_editor($this->op['footer'], 'op_footer', array(
                        'media_buttons' => false,
                        'textarea_name' => 'op[footer]',
                        'teeny' => false,
                        'textarea_rows' => 5,
                    )); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('Put your custom footer text here', 'colorized'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="op_show_credit"><?php _e('Show Credits', 'colorized'); ?></label>
                </th>
                <td>
                    <select name="op[show_credit]" id="op_show_credit">
                        <?php $this->print_select_op(array('yes', 'no'), $this->op['show_credit']); ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<br class="clear" />
<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save All Settings', 'colorized'); ?>" />
    <input type="submit" class="button-secondary" value="<?php _e('Restore Defaults', 'colorized'); ?>" name="ipt_colorized_restore_theme_op" />
</p>
<br class="clear" />
        <?php
    }

    public function meta_piecemaker_settings() {
        $op = get_option('ipt_colorized_piecemaker_settings');
        ?>
<div style="overflow: auto; max-height: 300px;">
    <table class="form-table">
        <tbody>
            <?php foreach($op as $s_key => $s_val) : ?>
            <tr>
                <th scope="row">
                    <label for="piecemaker_settings_<?php echo $s_key; ?>"><?php echo $s_key; ?></label>
                </th>
                <td>
                    <?php $this->print_input_text('piecemaker_settings[' . $s_key . ']', $s_val, 'regular-text code'); ?>
                </td>
                <td>
                    <span class="help">
                        <?php printf(__('Please see the %sOnline Documentation%s', 'colorized'), '<a href="http://www.modularweb.net/downloads/documentation_piecemaker2.pdf" target="_blank">', '</a>'); ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br class="clear" />
<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save All Settings', 'colorized'); ?>" />
    <input type="submit" class="button-secondary" value="<?php _e('Restore Defaults', 'colorized'); ?>" name="ipt_colorized_restore_piecemaker_settings" />
</p>
<br class="clear" />
        <?php
    }

    public function meta_piecemaker_contents() {
        $op = get_option('ipt_colorized_piecemaker_op');
        $transitions = array('linear');
        $t_types = array('Sine', 'Quad', 'Cubic', 'Quart', 'Quint', 'Expo', 'Circ', 'Elastic', 'Back', 'Bounce');
        foreach($t_types as $type) {
            $transitions[] = 'easeIn' . $type;
            $transitions[] = 'easeOut' . $type;
            $transitions[] = 'easeInOut' . $type;
            $transitions[] = 'easeOutIn' . $type;
        }
        ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.ipt_colorized_piecemaker_type').change(function() {
        var $target = $('tr.' + $(this).attr('id') + '_tr');
        if($(this).val() == 'Image') {
            $target.hide();
        } else {
            $target.show();
        }
    });
});
</script>
<ul class="metabox-tabs">
    <?php for($i = 0; $i < 10; $i++) : ?>
    <li class="tab ipt-colorize-piecemaker-tab-<?php echo $i; ?>">
        <a href="javascript:void(null);" class="<?php if($i == 0) echo 'active' ?>"><?php echo __('Item ', 'colorized') . ($i+1); ?></a>
    </li>
    <?php endfor; ?>
</ul>
<?php for($i = 0; $i < 10; $i++) : ?>
<div class="ipt-colorize-piecemaker-tab-<?php echo $i; ?>">
    <table class="form-table">
        <tbody>
            <tr>
                <td>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="piecemaker_contents_<?php echo $i; ?>_type"><?php _e('Content Type', 'colorized'); ?></label></th>
                                <td>
                                    <select class="ipt_colorized_piecemaker_type" name="piecemaker[contents][<?php echo $i; ?>][type]" id="piecemaker_contents_<?php echo $i; ?>_type">
                                        <?php $this->print_select_op(array(
                                            'Image', 'Video', 'Flash',
                                        ), $op['contents'][$i]['type']); ?>
                                    </select>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('Please select the type of the content.') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_source_<?php echo $i; ?>"><?php _e('Upload Source File (800X356)', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <input type="file" name="piecemaker_source_<?php echo $i; ?>" id="piecemaker_source_<?php echo $i; ?>" />
                                    <?php if('' != $op['contents'][$i]['source']) : ?>
                                    <br />
                                    <a href="<?php echo get_template_directory_uri() . '/piecemaker/files/' . $op['contents'][$i]['source']; ?>" target="_blank"><strong><?php _e('View Previous', 'colorized'); ?></strong></a>
                                    <br />
                                    <label for="premsource_<?php echo $i; ?>">
                                        <input type="checkbox" name="premsource[<?php echo $i; ?>]" id="premsource_<?php echo $i; ?>" value="yes" /> <?php _e('Remove', 'colorized'); ?>
                                    </label>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('You can upload your content here. Uploading a new content will remove the older one. Also, you can manually remove the existing one by checking the option.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr class="piecemaker_contents_<?php echo $i; ?>_type_tr" style="display: <?php echo ($op['contents'][$i]['type'] == 'Image' ? 'none' : 'table-row'); ?>">
                                <th scope="row">
                                    <label for="piecemaker_image_<?php echo $i; ?>"><?php _e('Upload Preview Image (800X356)', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <input type="file" name="piecemaker_image_<?php echo $i; ?>" id="piecemaker_image_<?php echo $i; ?>" />
                                    <?php if('' != $op['contents'][$i]['image']) : ?>
                                    <br />
                                    <a href="<?php echo get_template_directory_uri() . '/piecemaker/files/' . $op['contents'][$i]['image']; ?>" target="_blank"><strong><?php _e('View Previous', 'colorized'); ?></strong></a>
                                    <br />
                                    <label for="premimage_<?php echo $i; ?>">
                                        <input type="checkbox" name="premimage[<?php echo $i; ?>]" id="premimage_<?php echo $i; ?>" value="yes" /> <?php _e('Remove', 'colorized'); ?>
                                    </label>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('You can upload your preview image here. Uploading a new content will remove the older one. Also, you can manually remove the existing one by checking the option.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_contents_<?php echo $i; ?>_title"><?php _e('Title', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php $this->print_input_text('piecemaker[contents][' . $i . '][title]', $op['contents'][$i]['title'], 'large-text code'); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('Put the content title.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_contents_<?php echo $i; ?>_text"><?php _e('Description', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php wp_editor($op['contents'][$i]['text'], 'piecemaker_contents_' . $i . '_text', array(
                                        'media_buttons' => false,
                                        'textarea_name' => 'piecemaker[contents][' . $i . '][text]',
                                        'teeny' => false,
                                        'textarea_rows' => 5,
                                    )); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('Put the content text. Can be HTML', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_contents_<?php echo $i; ?>_title"><?php _e('Link', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php $this->print_input_text('piecemaker[contents][' . $i . '][url]', $op['contents'][$i]['url'], 'large-text code'); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('If you wish to link the item to some URL then put it here.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_transitions_<?php echo $i; ?>_Pieces"><?php _e('Pieces', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php $this->print_ui_slider('piecemaker[transitions][' . $i . '][Pieces]', $op['transitions'][$i]['Pieces'], 50, 2, 1); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('Number of pieces to which the image is sliced.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_transitions_<?php echo $i; ?>_Time"><?php _e('Time', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php $this->print_ui_slider('piecemaker[transitions][' . $i . '][Time]', $op['transitions'][$i]['Time'], 10, 0.5, 0.1); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('Time for one cube to turn in seconds.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_transitions_<?php echo $i; ?>_Transition"><?php _e('Transition type', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <select name="<?php echo 'piecemaker[transitions][' . $i . '][Transition]'; ?>" id="piecemaker_transitions_<?php echo $i; ?>_Transition">
                                        <?php $this->print_select_op($transitions, $op['transitions'][$i]['Transition']); ?>
                                    </select>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php printf(__('Select the transition type. Please see here for %sworking demo%s', 'colorized'), '<a href="http://hosted.zeh.com.br/tweener/docs/en-us/misc/transitions.html" target="_blank">', '</a>'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_transitions_<?php echo $i; ?>_Delay"><?php _e('Delay', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php $this->print_ui_slider('piecemaker[transitions][' . $i . '][Delay]', $op['transitions'][$i]['Delay'], 5, 0.05, 0.01); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('Delay between the start of one cube to the start of the next cube.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_transitions_<?php echo $i; ?>_DepthOffset"><?php _e('Depth Offset', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php $this->print_ui_slider('piecemaker[transitions][' . $i . '][DepthOffset]', $op['transitions'][$i]['DepthOffset'], 1000, 100, 10); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('The offset during transition on the z-axis.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="piecemaker_transitions_<?php echo $i; ?>_CubeDistance"><?php _e('Cube Distance', 'colorized'); ?></label>
                                </th>
                                <td>
                                    <?php $this->print_ui_slider('piecemaker[transitions][' . $i . '][CubeDistance]', $op['transitions'][$i]['CubeDistance'], 100, 5, 1); ?>
                                </td>
                                <td>
                                    <span class="help">
                                        <?php _e('The distance between the cubes during transition. Values between 5 and 50 are recommended. But go for experiments.', 'colorized'); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endfor; ?>
<br class="clear" />
<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save All Settings', 'colorized'); ?>" />
    <input type="submit" class="button-secondary" value="<?php _e('Restore Defaults', 'colorized'); ?>" name="ipt_colorized_restore_piecemaker_op" />
</p>
<br class="clear" />
        <?php
    }

    public function meta_frontpage() {
        $op = get_option('ipt_colorized_frontpage_op');
        ?>
<ul class="metabox-tabs">
    <?php for($i = 0; $i < 10; $i++) : ?>
    <li class="tab ipt-colorize-carousel-tab-<?php echo $i; ?>">
        <a href="javascript:void(null);" class="<?php if($i == 0) echo 'active' ?>"><?php echo __('Carousel ', 'colorized') . ($i+1); ?></a>
    </li>
    <?php endfor; ?>
</ul>
<?php for($i = 0; $i < 10; $i++) : ?>
<div class="ipt-colorize-carousel-tab-<?php echo $i; ?>">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="frontpage_<?php echo $i; ?>_title"><?php _e('Title', 'colorized'); ?></label>
                </th>
                <td>
                    <?php $this->print_input_text('frontpage[' . $i . '][title]', $op[$i]['title'], 'large-text'); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('Enter the title of the element.') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="frontpage_<?php echo $i; ?>_icon"><?php _e('Icon', 'colorized'); ?></label>
                </th>
                <td>
                    <?php $this->print_uploadbutton('frontpage[' . $i . '][icon]', $op[$i]['icon']); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('Enter the icon of the element.') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="frontpage_<?php echo $i; ?>_url"><?php _e('Link to', 'colorized'); ?></label>
                </th>
                <td>
                    <?php $this->print_input_text('frontpage[' . $i . '][url]', $op[$i]['url'], 'large-text code'); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('If you want to hyperlink the carousel item, then enter the URL here.') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="frontpage_<?php echo $i; ?>_text"><?php _e('Text', 'colorized'); ?></label>
                </th>
                <td>
                    <?php $this->print_textarea('frontpage[' . $i . '][text]', $op[$i]['text'], 'large-text'); ?>
                </td>
                <td>
                    <span class="help">
                        <?php _e('Enter the description for the element.') ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endfor; ?>
<br class="clear" />
<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save All Settings', 'colorized'); ?>" />
    <input type="submit" class="button-secondary" value="<?php _e('Restore Defaults', 'colorized'); ?>" name="ipt_colorized_restore_frontpage_op" />
</p>
<br class="clear" />
        <?php
    }

    protected function index_head($title = '', $print_form = true) {
        $this->print_form = $print_form;
        ?>
<style type="text/css">
    <?php echo '#' . $this->pagehook; ?>-widgets .meta-box-sortables {
        margin: 0 8px;
    }
</style>
<div class="wrap" id="<?php echo $this->pagehook; ?>-widgets">
    <div class="icon32">
        <img src="<?php echo $this->icon_url; ?>" height="32" width="32" alt="icon" />
    </div>
    <h2><?php echo $title; ?></h2>
    <?php
        if(isset($_GET['post_result'])) {
            $msg = $this->post_result[(int) $_GET['post_result']];
            if(!empty($msg)) {
                if($msg['type'] == 'update' || $msg['type'] == 'updated')
                    $this->print_update($msg['msg']);
                else
                    $this->print_error($msg['msg'], false, $this->get_json_message());
            }
        }
    ?>
    <?php if($this->print_form) : ?>
    <form method="post" action="admin-post.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $this->admin_post_action; ?>" />
        <?php wp_nonce_field($this->action_nonce, $this->action_nonce); ?>
        <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
        <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
    <?php endif; ?>
        <?php
    }

    /**
     * Include this to the end of index function so that metaboxes work
     */
    protected function index_foot($submit = true, $text = 'Save Changes') {
        ?>
    <?php if($this->print_form) : ?>
        <?php if(true == $submit) : ?>
        <div class="clear" />
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e($text, 'colorized'); ?>" name="submit" />&nbsp;
            <input type="reset" class="button-secondary" value="<?php _e('Reset', 'colorized'); ?>" name="reset" />
        </p>
        <?php endif; ?>
    </form>
    <?php endif; ?>
</div>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
        // close postboxes that should be closed
        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        // postboxes setup
        postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
});
//]]>
</script>
        <?php
    }

    private function upload_piecemaker_file(&$file, $type) {
        $dir = get_template_directory() . '/piecemaker/files';

        //check for size
        if(!($file['size'] > 0)) {
            return array('upload' => 'failure', 'error' => __('File is empty.', 'colorized'));
        }

        //check for properly uploaded
        if(! @is_uploaded_file($file['tmp_name'])) {
            return array('upload' => 'failure', 'error' => __('Failed upload test', 'colorized'));
        }

        //Check mime
        $filetype = wp_check_filetype($file['name'], array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'swf' => 'application/x-shockwave-flash',
            'flv|f4v' => 'video/x-flv',
            'mp4' => 'video/mp4',
        ));

        if(false == $filetype['type']) {
            return array('upload' => 'failure', 'error' => __('Invalid file type', 'colorized'));
        }

        //Check if given type matches with calculated type
        $cal_type = 'Image';
        if(in_array($filetype['ext'], array('flv', 'f4v', 'mp4'))) {
            $cal_type = 'Video';
        } else if(in_array($filetype['ext'], array('swf'))) {
            $cal_type = 'Flash';
        }
        if($cal_type != $type) {
            return array('upload' => 'failure', 'error' => __('The uploaded file is not a valid format of the given type.', 'colorized'));
        }

        //upload it
        $filename = wp_unique_filename($dir, $file['name']);
        $new_file = $dir . '/' . $filename;

        if(false === @move_uploaded_file($file['tmp_name'], $new_file)) {
            return array('upload' => 'failure', 'error' => __('The uploaded file could not be moved.', 'colorized'));
        }

        //Set correct file permissions
        $stat = stat( dirname( $new_file ));
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );

        return array(
            'upload' => 'success',
            'name' => $filename,
            'path' => $new_file,
        );

    }

    private function del_piecemaker_file($file) {
        $filename = get_template_directory() . '/piecemaker/files/' . $file;
        if('' != $file && file_exists($filename)) {
            @unlink($filename);
        }
    }

    /**
     * Override to manage the save_post
     * This should be written by all the classes extending this
     *
     *
     * * General Template
     *
     * //process here your on $_POST validation and / or option saving
     *
     * //lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
     * wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
     *
     *
     */
    public function save_post($check_referer = true) {
        //user permission check
        if (!current_user_can($this->capability))
            wp_die(__('Cheatin&#8217; uh?'));
        //check nonce
        if($check_referer) {
            if(!wp_verify_nonce($_POST[$this->action_nonce], $this->action_nonce))
                wp_die(__('Cheatin&#8217; uh?'));
        }

        //check for resets
        if(isset($this->post['ipt_colorized_restore_theme_op'])) {
            delete_option('ipt_colorized_theme_op');
            wp_redirect(add_query_arg(array('post_result' => 5), $_POST['_wp_http_referer']));
            die();
        }
        if(isset($this->post['ipt_colorized_restore_piecemaker_settings'])) {
            delete_option('ipt_colorized_piecemaker_settings');
            wp_redirect(add_query_arg(array('post_result' => 6), $_POST['_wp_http_referer']));
            die();
        }
        if(isset($this->post['ipt_colorized_restore_piecemaker_op'])) {
            delete_option('ipt_colorized_piecemaker_op');
            wp_redirect(add_query_arg(array('post_result' => 7), $_POST['_wp_http_referer']));
            die();
        }
        if(isset($this->post['ipt_colorized_restore_frontpage_op'])) {
            delete_option('ipt_colorized_frontpage_op');
            wp_redirect(add_query_arg(array('post_result' => 8), $_POST['_wp_http_referer']));
            die();
        }

        //theme options
        $op = $this->post['op'];
        $op['countdown']['enabled'] = isset($op['countdown']['enabled']) ? true : false;
        $op['footer'] = htmlspecialchars_decode($op['footer']);
        $op['description'] = htmlspecialchars_decode($op['description']);

        update_option('ipt_colorized_theme_op', $op);

        //piecemaker settings
        $piecemaker_settings = $this->post['piecemaker_settings'];
        update_option('ipt_colorized_piecemaker_settings', $piecemaker_settings);

        //piecemaker contents
        $piecemaker = array();
        $piecemaker['transitions'] = $this->post['piecemaker']['transitions'];
        $piecemaker['contents'] = array();
        //gather the contents
        $old_piecemaker = get_option('ipt_colorized_piecemaker_op');
        $upload_error = array();
        for($i = 0; $i < 10; $i++) {
            $piecemaker['contents'][$i] = $old_piecemaker['contents'][$i];
            //check the source
            if('yes' == $this->post['premsource'][$i]) { //reset
                //delete the old files
                $this->del_piecemaker_file($old_piecemaker['contents'][$i]['source']);

                $piecemaker['contents'][$i] = array(
                    'type' => 'Image',
                    'source' => '',
                );
            } else {
                $file = $_FILES['piecemaker_source_' . $i];
                //var_dump($file);
                if(!empty($file['tmp_name'])) { //new upload
                    //upload the source file
                    $source = $this->upload_piecemaker_file($file, $this->post['piecemaker']['contents'][$i]['type']);

                    if($source['upload'] == 'success') { //valid upload
                        $this->del_piecemaker_file($old_piecemaker['contents'][$i]['source']);
                        $piecemaker['contents'][$i]['source'] = $source['name'];
                        $piecemaker['contents'][$i]['type'] = $this->post['piecemaker']['contents'][$i]['type'];
                    } else { //upload fail
                        $upload_error[$i] = __('Error on piecemaker source item #', 'colorized') . ($i + 1) . ' ' . $source['error'];
                    }
                }
            }

            //check the preview image
            if('yes' == $this->post['premimage'][$i]) { //reset
                $this->del_piecemaker_file($old_piecemaker['contents'][$i]['image']);
                $piecemaker['contents'][$i]['image'] = '';
            } else {
                $file = $_FILES['piecemaker_image_' . $i];
                //var_dump($file);
                if(!empty($file['tmp_name'])) { //new upload
                    //upload the source file
                    $source = $this->upload_piecemaker_file($file, 'Image');

                    if($source['upload'] == 'success') { //valid upload
                        $this->del_piecemaker_file($old_piecemaker['contents'][$i]['image']);
                        $piecemaker['contents'][$i]['image'] = $source['name'];
                    } else { //upload fail
                        $upload_error[$i] = __('Error on piecemaker image item #', 'colorized') . ($i + 1) . ' ' . $source['error'];
                    }
                }
            }

            //copy other data
            $piecemaker['contents'][$i]['title'] = trim(strip_tags(htmlspecialchars_decode($this->post['piecemaker']['contents'][$i]['title'])));
            $piecemaker['contents'][$i]['text'] = trim(htmlspecialchars_decode($this->post['piecemaker']['contents'][$i]['text']));
            $piecemaker['contents'][$i]['url'] = trim(strip_tags(htmlspecialchars_decode($this->post['piecemaker']['contents'][$i]['url'])));
        }
        update_option('ipt_colorized_piecemaker_op', $piecemaker);

        //frontpage options
        $frontpage = $this->post['frontpage'];
        update_option('ipt_colorized_frontpage_op', $frontpage);

        if(empty($upload_error)) {
            wp_redirect(add_query_arg(array('post_result' => 1), $_POST['_wp_http_referer']));
        } else {
            wp_redirect(add_query_arg(array(
                'post_result' => 4,
                'errors' => urlencode(json_encode((object) $upload_error)),
            ), $_POST['_wp_http_referer']));
        }

        die();

        //process here your on $_POST validation and / or option saving

        //lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
        //wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
        //The above should be done by the extending after calling parent::save_post and processing post
    }

    protected function get_json_message() {
        if(!isset($_GET['errors']))
            return '';
        $errors = json_decode(urldecode($_GET['errors']));
        if(NULL == $errors)
            return '';

        $return = '<ul class="ul-square">';
        foreach($errors as $error) {
            $return .= '<li>' . $error . '</li>';
        }
        $return .= '</ul>';
        return $return;
    }

    /**
     * Hook to the load plugin page
     * This should be overriden
     * Also call parent::on_load_page() for screenoptions
     * @uses add_meta_box
     */
    public function on_load_page() {
        //add screen helps
        get_current_screen()->add_help_tab(array(
            'id' => 'overview',
            'title' => __('Overview', 'colorized'),
            'content' =>
                '<p>' . __('Thank you for your interest in our theme. Colorized Theme holds the following key features.', 'colorized') . '</p>' .
                '<ul>' .
                    '<li>' . __('<strong>Multiple Homepage Layout:</strong> You can have either a dynamic homepage with piecemaker or static homepage with carousel contents.', 'colorized') . '</li>' .
                    '<li>' . __('<strong>Piecemaker:</strong> Successful implementation of piecemaker 2 on your dynamic homepage. You can have images, videos or flash contents.', 'colorized') . '</li>' .
                    '<li>' . __('<strong>Frontpage Carousel:</strong> Custom carousel on your static frontpage.', 'colorized') . '</li>' .
                    '<li>' . __('<strong>Sponsors Widget:</strong> A good looking sponsors widget for the footer.', 'colorized') . '</li>' .
                '</ul>' .
                '<p>' . __('Colorized theme is intended mainly for showcase or fests related websites. However you can get creative and use it anyway you like. Be sure to mail us at <a href="http://www.ipanelthemes.com/contact">iPanelThemes</a> to show your creativity.', 'colorized') . '</p>'
        ));

        get_current_screen()->add_help_tab(array(
            'id' => 'piecemaker',
            'title' => __('Piecemaker', 'colorized'),
            'content' =>
                '<p>' . __('Piecemaker is shown on the very home page. If you are showing your frontpage as the latest posts, then on your home page piecemaker will show up along with your latest posts.', 'colorized') . '</p>' .
                '<p>' . __('To change the piecemaker items, use this theme options area &raquo; Piecemaker Contents &amp; Transitions. Also play with the piecemaker settings to get the ultimate tuning.', 'colorized') . '</p>',
        ));

        get_current_screen()->add_help_tab(array(
            'id' => 'carousel',
            'title' => __('Carousel', 'colorized'),
            'content' =>
                '<p>' . __('Carousel is an element of your static frontpage. Use theme options &raquo; Carousels to add carousel elements.', 'colorized') . '</p>' .
                '<p>' . __('The content of the page you use for static frontpage, will be used as the frontpage title.', 'colorized') . '</p>',
        ));

        get_current_screen()->add_help_tab(array(
            'id' => 'sponsors',
            'title' => __('Sponsors', 'colorized'),
            'content' =>
                '<p>' . __('Colorized Theme comes with a nice looking sponsors widget.', 'colorized') . '</p>' .
                '<p>' . __('Navigate to <a href="widgets.php">Widgets</a> and select the Colorized Sponsors widget. Drop it to the footer area and get started.', 'colorized') . '</p>',
        ));

        get_current_screen()->add_help_tab(array(
            'id' => 'thumbnails',
            'title' => __('Thumbnails', 'colorized'),
            'content' =>
                '<p>' . __('The featured image is set automatically as the preview thumbnail on archive pages and homepage.', 'colorized') . '</p>' .
                '<p>' . __('You can also set a small 50X50 icon for posts to show up on specified pages before the page title.', 'colorized') . '</p>',
        ));

        get_current_screen()->add_help_tab(array(
            'id' => 'registration',
            'title' => __('Registration', 'colorized'),
            'content' =>
                '<p>' . __('You can link to some registration page by simply entering registration URL while editing creating any posts.', 'colorized') . '</p>' .
                '<p>' . __('A nice looking image with your link and text will appear below the post if you have entered the URL.', 'colorized') . '</p>' .
                '<p>' . __('You can use our <a href="http://codecanyon.net/item/wp-feedback-survey-quiz-manager-pro/3180835?ref=iPanelThemes">FSQM Pro</a> plugin to power up your registration.', 'colorized') . '</p>',
        ));

        get_current_screen()->add_help_tab(array(
            'id' => 'credits',
            'title' => __('Credits', 'colorized'),
            'content' =>
                '<p>' . __('This is a Free <a href="http://wordpress.org/extend/themes/">WordPress Theme</a> designed specifically for our college fest website.', 'colorized') . '</p>' .
                '<p>' . __('The theme uses a few free and/or open source products, which are:', 'colorized') . '</p>' .
                '<ul>' .
                        '<li>' . __('<strong><a href="http://www.google.com/webfonts/">Google WebFont</a></strong> : To make the theme look better.', 'colorized') . '</li>' .
                        '<li>' . __('<strong><a href="http://bxslider.com/">BXSlider</a></strong> : For the carousel and homepage slider.', 'colorized') . '</li>' .
                        '<li>' . __('<strong><a href="http://www.inwebson.com/jquery/jqfloat-js-a-floating-effect-with-jquery/">jqFloat</a></strong> : For the floating bubbles.', 'colorized') . '</li>' .
                        '<li>' . __('<strong><a href="http://www.modularweb.net/en/portfolio/piecemaker2">Piecemaker 2</a></strong> : For the homepage flash slider.', 'colorized') . '</li>' .
                        '<li>' . __('<strong><a href="http://keith-wood.name/countdown.html">jQuery Countdown</a></strong> : For the homepage countdown.', 'colorized') . '</li>' .
                        '<li>' . __('<strong><a href="http://icons.mysitemyway.com/">MySiteMyWay</a></strong> : For the navigation icons.', 'colorized') . '</li>' .
                '</ul>' .
                '<p>' . __('Designed by <a href="http://en.gravatar.com/akashghosh">Akash Ghosh</a>, Developed by <a href="http://www.swashata.com/">Swashata Ghosh</a>.', 'colorized') . '</p>',
        ));

        get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . sprintf(__('<a href="%s" target="_blank">Documentation</a>', 'colorized'), 'http://ipanelthemes.com/colorized/category/documentation/') . '</p>' .
			'<p>' . sprintf(__('<a href="%s" target="_blank">Support Forums</a>', 'fbsr'), 'http://support.ipanelthemes.com/viewforum.php?f=8') . '</p>'
		);

        add_screen_option('layout_columns', array(
            'max' => 1,
            'default' => 1,
        ));

        add_meta_box('ipt-colorized-theme', __('General Theme Settings', 'colorized'), array(&$this, 'meta_theme'), $this->pagehook, 'normal', 'default');
        add_meta_box('ipt-colorized-piecemaker-contents', __('Piecemaker Contents &amp; Transitions', 'colorized'), array(&$this, 'meta_piecemaker_contents'), $this->pagehook, 'normal', 'default');
        add_meta_box('ipt-colorized-piecemaker-settings', __('Piecemaker Settings', 'colorized'), array(&$this, 'meta_piecemaker_settings'), $this->pagehook, 'normal', 'default');
        add_meta_box('ipt-colorized-frontpage', __('Static FrontPage Carousel Settings', 'colorized'), array(&$this, 'meta_frontpage'), $this->pagehook, 'normal', 'default');

        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

        /**
         * MetaBox Tab like wp-stat
         * @link  http://developersmind.com/2011/04/05/wordpress-tabbed-metaboxes/
         */
        wp_enqueue_style('jf-metabox-tabs', get_template_directory_uri() . '/admin-static/css/metabox-tabs.css');
        wp_enqueue_script('jf-metabox-tabs', get_template_directory_uri() . '/admin-static/js/metabox-tabs.js', array( 'jquery' ) );


        wp_enqueue_script('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-progressbar');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('ColorPicker', get_template_directory_uri() . '/admin-static/js/colorpicker.js', array('jquery'), null, true);
        wp_enqueue_script('jquery-ui-timepicker', get_template_directory_uri() . '/admin-static/js/jquery-ui-timepicker-addon.js', array('jquery'), null, true);
        wp_enqueue_script('colorized-admin-script', get_template_directory_uri() . '/admin-static/js/admin.js', array('jquery'), null, true);

        wp_enqueue_style('colorized-admin-style', get_template_directory_uri() . '/admin-static/css/admin.css', array());
        wp_enqueue_style('thickbox');
        wp_enqueue_style('ColorPicker', get_template_directory_uri() . '/admin-static/css/colorpicker.css', array());
        wp_enqueue_style('jQuery-UI-style', get_template_directory_uri() . '/admin-static/css/jquery-ui-1.10.3.custom.min.css', array());
    }

    /**
     * Prints the metaboxes of a custom context
     * Should atleast pass the $context, others are optional
     *
     * The screen defaults to the $this->pagehook so make sure it is set before using
     * This should be the return value given by add_admin_menu or similar function
     *
     * The function automatically checks the screen layout columns and prints the normal/side columns accordingly
     * If screen layout column is 1 then even if you pass with context side, it will be hidden
     * Also if screen layout is 1 and you pass with context normal, it will get full width
     *
     * @param string $context The context of the metaboxes. Depending on this HTML ids are generated. Valid options normal | side
     * @param string $container_classes (Optional) The HTML class attribute of the container
     * @param string $container_style (Optional) The RAW inline CSS style of the container
     */
    public function print_metabox_containers($context = 'normal', $container_classes = '', $container_style = '') {
        global $screen_layout_columns;
        $style = 'width: 50%;';

        //check to see if only one column has to be shown

        if(isset($screen_layout_columns) && $screen_layout_columns == 1) {
            //normal?
            if('normal' == $context) {
                $style = 'width: 100%;';
            } else if ('side' == $context) {
                $style = 'display: none;';
            }
        }

        //override for the special debug area (1 column)
        if('debug' == $context) {
            $style = 'width: 100%;';
            $container_classes .= ' debug-metabox';
        }
        ?>
<div class="postbox-container <?php echo $container_classes; ?>" style="<?php echo $style . $container_style; ?>" id="<?php echo (('normal' == $context)? 'postbox-container-1' : 'postbox-container-2'); ?>">
    <?php do_meta_boxes($this->pagehook, $context, ''); ?>
</div>
        <?php
    }


    /*______________________________________INTERNAL METHODS______________________________________*/

    /**
     * Prints error msg in WP style
     * @param string $msg
     */
    protected function print_error($msg = '', $echo = true, $add = '') {
        $output = '<div class="error fade"><p>' . $msg . '</p>' . $add . '</div>';
        if($echo)
            echo $output;
        else
            return $output;
    }

    protected function print_update($msg = '', $echo = true) {
        $output = '<div class="updated fade"><p>' . $msg . '</p></div>';
        if($echo)
            echo $output;
        else
            return $output;
    }

    protected function print_p_error($msg = '', $echo = true) {
        $output = '<div class="p-message red"><p>' . $msg . '</p></div>';
        if($echo)
            echo $output;
        return $output;
    }

    protected function print_p_update($msg = '', $echo = true) {
        $output = '<div class="p-message yellow"><p>' . $msg . '</p></div>';
        if($echo)
            echo $output;
        return $output;
    }

    protected function print_p_okay($msg = '', $echo = true) {
        $output = '<div class="p-message green"><p>' . $msg . '</p></div>';
        if($echo)
            echo $output;
        return $output;
    }

    /**
     * stripslashes gpc
     * Strips Slashes added by magic quotes gpc thingy
     * @access protected
     * @param string $value
     */
    protected function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }

    protected function htmlspecialchar_ify(&$value) {
        $value = htmlspecialchars($value);
    }

    /*______________________________________SHORTCUT HTML METHODS______________________________________*/

    /**
     * Shortens a string to a specified character length.
     * Also removes incomplete last word, if any
     * @param string $text The main string
     * @param string $char Character length
     * @param string $cont Continue character(…)
     * @return string
     */
    public function shorten_string($text, $char, $cont = '…') {
        $text = strip_tags(strip_shortcodes($text));
        $text = substr($text, 0, $char); //First chop the string to the given character length
        if(substr($text, 0, strrpos($text, ' '))!='') $text = substr($text, 0, strrpos($text, ' ')); //If there exists any space just before the end of the chopped string take upto that portion only.
        //In this way we remove any incomplete word from the paragraph
        $text = $text.$cont; //Add continuation ... sign
        return $text; //Return the value
    }

    /**
     * Get the first image from a string
     * @param string $html
     * @return mixed string|bool The src value on success or boolean false if no src found
     */
    public function get_first_image($html) {
        $matches = array();
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $html, $matches);
        if(!$output) {
            return false;
        }
        else {
            $src = $matches[1][0];
            return trim($src);
        }
    }

    /**
     * Wrap a RAW JS inside <script> tag
     * @param String $string The JS
     * @return String The wrapped JS to be used under HTMl document
     */
    public function js_wrap( $string ) {
            return "\n<script type='text/javascript'>\n" . $string . "\n</script>\n";
    }

    /**
     * Wrap a RAW CSS inside <style> tag
     * @param String $string The CSS
     * @return String The wrapped CSS to be used under HTMl document
     */
    public function css_wrap( $string ) {
            return "\n<style type='text/css'>\n" . $string . "\n</style>\n";
    }

    public function print_datetimepicker($name, $value, $dateonly = false) {
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        ?>
<input type="text" class="regular-text code <?php echo ($dateonly ? 'datepicker' : 'datetimepicker'); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>" />
        <?php
    }

    /**
     * Prints options of a selectbox
     *
     * @param array $ops Should pass either an array of string ('label1', 'label2') or associative array like array('val' => 'val1', 'label' => 'label1'),...
     * @param string $key The key in the haystack, if matched a selected="selected" will be printed
     */
    public function print_select_op($ops, $key, $inner = false) {
        foreach((array) $ops as $k => $op) : ?>
        <?php if(!is_array($op)) : if(!$inner) $op = array('val' => $op, 'label' => ucfirst ($op)); else $op = array('val' => $k, 'label' => $op); endif; ?>
<option value="<?php echo esc_attr($op['val']); ?>"<?php if($key == $op['val']) echo ' selected="selected"'; ?>><?php echo $op['label']; ?></option>
        <?php endforeach;
    }

    /**
     * Prints a set of checkboxes for a single HTML name
     *
     * @param string $name The HTML name of the checkboxes
     * @param array $items The associative array of items array('val' => 'value', 'label' => 'label'),...
     * @param array $checked The array of checked items. It matches with the 'val' of the haystack array
     * @param string $sep (Optional) The seperator, HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
     */
    public function print_checkboxes($name, $items, $checked, $sep = '&nbsp;&nbsp;') {
        if(!is_array($checked))
            $checked = (array) $checked;
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        foreach((array) $items as $item) : ?>
<label for="<?php echo esc_attr($id . '_' . $item['val']); ?>">
    <input type="checkbox" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id . '_' . $item['val']); ?>" value="<?php echo esc_attr($item['val']); ?>"<?php if(in_array($item['val'], $checked)) echo ' checked="checked"'; ?> /> <?php echo $item['label']; ?>
</label>
        <?php echo $sep;
        endforeach;
    }

    /**
     * Prints a set of radioboxes for a single HTML name
     *
     * @param string $name The HTML name of the checkboxes
     * @param array $items The associative array of items array('val' => 'value', 'label' => 'label'),...
     * @param string $checked The value of checked radiobox. It matches with the val of the haystack
     * @param string $sep (Optional) The seperator, two HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
     */
    public function print_radioboxes($name, $items, $checked, $sep = '&nbsp;&nbsp;') {
        if(!is_string($checked))
            $checked = (string) $checked;
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        foreach((array) $items as $item) : ?>
<label for="<?php echo esc_attr($id . '_' . $item['val']); ?>">
    <input type="radio" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id . '_' . $item['val']); ?>" value="<?php echo esc_attr($item['val']); ?>"<?php if($checked == $item['val']) echo ' checked="checked"'; ?> /> <?php echo $item['label']; ?>
</label>
        <?php echo $sep;
        endforeach;
    }

    /**
     * Print a single checkbox
     * Useful for printing a single checkbox like for enable/disable type
     *
     * @param string $name The HTML name
     * @param string $value The value attribute
     * @param mixed (string|bool) $checked Can be true or can be equal to the $value for adding checked attribute. Anything else and it will not be added.
     */
    public function print_checkbox($name, $value, $checked) {
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        ?>
<input type="checkbox" name="<?php echo esc_attr($name); ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr($value); ?>"<?php if($value == $checked || true == $checked) echo ' checked="checked"'; ?> />
        <?php
    }

    /**
     * Prints a input[type="text"]
     * All attributes are escaped except the value
     * @param string $name The HTML name attribute
     * @param string $value The value of the textbox
     * @param string $class (Optional) The css class defaults to regular-text
     */
    public function print_input_text($name, $value, $class = 'regular-text') {
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        ?>
<input type="text" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo $value; ?>" class="<?php echo esc_attr($class); ?>" />
        <?php
    }

    /**
     * Prints a <textarea> with custom attributes
     * All attributes are escaped except the value
     * @param string $name The HTML name attribute
     * @param string $value The value of the textbox
     * @param string $class (Optional) The css class defaults to regular-text
     * @param int $rows (Optional) The number of rows in the rows attribute
     * @param int $cols (Optional) The number of columns in the cols attribute
     */
    public function print_textarea($name, $value, $class = 'regular-text', $rows = 3, $cols = 20) {
        $id = str_replace(array('[', ']'), array('_', ''), $name);
        ?>
<textarea name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($class); ?>" rows="<?php echo (int) $rows; ?>" cols="<?php echo (int) $cols; ?>"><?php echo $value; ?></textarea>
        <?php
    }


    /**
     * Displays a jQuery UI Slider to the page
     * @param string $name The HTML name of the input box
     * @param int $value The initial/saved value of the input box
     * @param int $max The maximum of the range
     * @param int $min The minimum of the range
     * @param int $step The step value
     */
    public function print_ui_slider($name, $value, $max = 100, $min = 0, $step = 1) {
        ?>
<div class="slider"></div>
<input type="text" class="small-text code slider-text" max="<?php echo $max; ?>" min="<?php echo $min; ?>" step="<?php echo $step; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
        <?php
    }

    /**
     * Displays a jQuery UI Slider to the page
     * @param string $name The HTML name of the input box
     * @param int $value The initial/saved value of the input box
     * @param int $max The maximum of the range
     * @param int $min The minimum of the range
     * @param int $step The step value
     */
    public function print_ui_slider_range($name_min, $name_max, $value, $max = 100, $min = 0, $step = 1) {
        ?>
<div class="slider_range"></div>
<div class="slider_range_inputs">
    <input type="text" class="small-text code slider-text-min" max="<?php echo $max; ?>" min="<?php echo $min; ?>" step="<?php echo $step; ?>" name="<?php echo $name_min; ?>" value="<?php echo $value[0]; ?>" /> -
    <input type="text" class="small-text code slider-text-max" name="<?php echo $name_max; ?>" value="<?php echo $value[1]; ?>" />
</div>
        <?php
    }

    /**
     * Prints a ColorPicker
     *
     * @param string $name The HTML name of the input box
     * @param string $value The HEX color code
     */
    public function print_cpicker($name, $value) {
        $value = ltrim($value, '#');
        ?>
<input type="text" class="small-text color-picker code" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
        <?php
    }

    /**
     * Prints a input box with an attached upload button
     *
     * @param string $name The HTML name of the input box
     * @param string $value The value of the input box
     */
    public function print_uploadbutton($name, $value) {
        ?>
<input type="text" class="regular-text code" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />&nbsp;
<input class="upload-button" type="button" value="<?php _e('Upload'); ?>" />
        <?php
    }
}
