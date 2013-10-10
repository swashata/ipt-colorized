<?php

class ipt_colorized_widget_sponsors extends WP_Widget {

    protected function is_url($url) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('ipt_colorized_sponsors', __('Colorized Sponsors', 'colorized'), array(
            'description' => __('Sponsors Widgets, for the footer area only.', 'colorized'),
        ), array(
            'width' => 500,
        ));
    }

    /**
     * The output of the widget
     */
    public function widget($args, $instance) {
        extract($args);

        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;

        if(!empty($title)) {
            echo $before_title . $title . $after_title;
        }
        $pos_left = 0;
        $pos_top = 0;
        $left_incr = 144;
        $top_incr = 63;
        ?>
<div class="colorized-sponsors">
    <ul class="sponsor">
        <?php for($i = 0; $i < 10; $i++) : ?>
        <?php if($instance[$i . '_title'] == '' || $instance[$i . '_link'] == '' || $instance[$i . '_image'] == '') continue; ?>
        <?php
        if($instance[$i . '_type'] == 'big') {
            if($pos_top != 0) {
                //we need to step ahead
                $pos_left += $left_incr;
                $pos_top = 0;
            }
        }
        ?>
        <li class="<?php echo $instance[$i . '_type']; ?>" style="left: <?php echo $pos_left; ?>px; top: <?php echo $pos_top ?>px">
            <a target="_blank" rel="nofollow" href="<?php echo $instance[$i . '_link'] ?>" title="<?php echo $instance[$i . '_img_title']; ?>">
                <img src="<?php echo $instance[$i . '_image']; ?>" />
            </a>
            <span>
                <a target="_blank" rel="nofollow" href="<?php echo $instance[$i . '_link'] ?>" title="<?php echo $instance[$i . '_img_title']; ?>">
                    <?php echo $instance[$i . '_title']; ?>
                </a>
            </span>
        </li>
        <?php
        //calculate new position for a small element
        $pos_top = $pos_top == 0 ? $top_incr : 0;
        $pos_left = $pos_top == 0 ? $pos_left + $left_incr : $pos_left;
        //override the position if this element was big
        if($instance[$i . '_type'] == 'big') {
            $pos_left = $pos_top != 0 ? $pos_left + $left_incr : $pos_left;
            $pos_top = 0;
        }
        ?>
        <?php endfor; ?>
    </ul>
</div>
        <?php
        echo $after_widget;
    }


    /**
     * Save widget options
     */
    public function update($new_instance, $old_instance) {
        $instance = $new_instance;
        $instance['title'] = strip_tags($instance['title']);

        for($i = 0; $i < 10; $i++) {
            $instance[$i . '_title'] = trim(strip_tags($instance[$i . '_title']));
            $instance[$i . '_img_title'] = trim(strip_tags($instance[$i . '_img_title']));
            $instance[$i . '_image'] = trim(strip_tags($instance[$i . '_image']));
            $instance[$i . '_link'] = trim(strip_tags($instance[$i . '_link']));
            $instance[$i . '_type'] = in_array($instance[$i . '_type'], array('small', 'big')) ? $instance[$i . '_type'] : 'small';

            $instance[$i . '_image'] = $this->is_url($instance[$i . '_image']) ? $instance[$i . '_image'] : '';
            $instance[$i . '_link'] = $this->is_url($instance[$i . '_link']) ? $instance[$i . '_link'] : '';
        }

        return $instance;
    }

    /**
     * Widget setup form
     */
    public function form($instance) {
        //Register the instance
        $default = array();

        $default['title'] = '';

        for($i = 0; $i < 10; $i++) {
            $default[$i . '_title'] = '';
            $default[$i . '_img_title'] = '';
            $default[$i . '_image'] = '';
            $default[$i . '_link'] = '';
            $default[$i . '_type'] = 'small';
        }

        $instance = wp_parse_args((array) $instance, $default);
        ?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title of Widget: ', 'colorized'); ?></label>
    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
</p>
<hr />
<?php for($i = 0; $i < 10; $i++) : ?>
<p>
    <label for="<?php echo $this->get_field_id($i . '_title'); ?>"><?php _e('Title of Sponsor:', 'colorized') ?></label>
    <input type="text" id="<?php echo $this->get_field_id($i . '_title'); ?>" name="<?php echo $this->get_field_name($i . '_title'); ?>" value="<?php echo $instance[$i . '_title']; ?>" class="widefat" />
</p>
<p>
    <label for="<?php echo $this->get_field_id($i . '_img_title'); ?>"><?php _e('Title of Image:', 'colorized') ?></label>
    <input type="text" id="<?php echo $this->get_field_id($i . '_img_title'); ?>" name="<?php echo $this->get_field_name($i . '_img_title'); ?>" value="<?php echo $instance[$i . '_img_title']; ?>" class="widefat" />
</p>
<p>
    <label for="<?php echo $this->get_field_id($i . '_type'); ?>"><?php _e('Type of Sponsor:', 'colorized') ?></label>
    <select name="<?php echo $this->get_field_name($i . '_type'); ?>" id="<?php echo $this->get_field_id($i . '_type'); ?>">
        <option value="big"<?php if($instance[$i . '_type'] == 'big') echo ' selected="selected"'; ?>><?php _e('Big', 'colorized'); ?></option>
        <option value="small"<?php if($instance[$i . '_type'] == 'small') echo ' selected="selected"'; ?>><?php _e('Small', 'colorized'); ?></option>
    </select>
</p>
<p>
    <span class="description">
        <?php _e('For small type sponsors, image size is 80X25 and for big type, the size is 80X80.', 'colorized'); ?>
    </span>
</p>
<p>
    <label for="<?php echo $this->get_field_id($i . '_image'); ?>"><?php _e('Image of Sponsor:', 'colorized') ?></label>
    <input type="text" id="<?php echo $this->get_field_id($i . '_image'); ?>" name="<?php echo $this->get_field_name($i . '_image'); ?>" value="<?php echo $instance[$i . '_image']; ?>" class="widefat" />
</p>
<p>
    <label for="<?php echo $this->get_field_id($i . '_link'); ?>"><?php _e('Link of Sponsor:', 'colorized') ?></label>
    <input type="text" id="<?php echo $this->get_field_id($i . '_link'); ?>" name="<?php echo $this->get_field_name($i . '_link'); ?>" value="<?php echo $instance[$i . '_link']; ?>" class="widefat" />
</p>
<hr />
<?php endfor; ?>
        <?php
    }
}
