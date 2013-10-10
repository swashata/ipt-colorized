<?php
include_once '../../../../wp-load.php';

//if(!wp_verify_nonce($_GET['_wpnonce'], 'ipt_colorize_piecemaker')) {
//    wp_die('Cheating?');
//}

if(!function_exists('ipt_colorized_xml_shortcut')) {
    function ipt_colorized_xml_shortcut($arr) {
        if(!is_array($arr))
            return '';

        $return = array();

        foreach($arr as $key => $val) {
            $return[] = $key . '="' . esc_attr($val) . '"';
        }

        return implode(' ', $return);
    }
}

if(!function_exists('ipt_colorized_xml_content')) {
    function ipt_colorized_xml_content($content, $return) {
        if(!is_array($content))
            return false;

        if(!isset($content['source']) || '' == $content['source'] || !isset($content['type']) || !in_array($content['type'], array('Image', 'Video', 'Flash')))
            return false;

        if($content['type'] != 'Image' && '' == $content['image'])
            return false;

        //all set, calc the attributes
        $attributes = array(
            'Source' => get_template_directory_uri() . '/piecemaker/files/' . $content['source']
        );
        if('' != $content['title'])
            $attributes['Title'] = $content['title'];

        if('Video' == $content['type']) {
            $attributes['Width'] = '800';
            $attributes['Height'] = '356';
            $attributes['Autoplay'] = 'true';
        }

        //Now start the output
        $attribute = ipt_colorized_xml_shortcut($attributes);
        echo "<{$content['type']} {$attribute}>";

        if('Image' != $content['type']) {
            echo '<Image Source="' . esc_attr(get_template_directory_uri() . '/piecemaker/files/' . $content['image']) . '" />';
        }

        if('' != $content['text']) {
            echo '<Text>';
            echo htmlspecialchars(str_replace(array("\n", "\r"), array('', ''), wpautop($content['text'])));
            echo '</Text>';
        }

        if('' != $content['url']) {
            echo '<Hyperlink URL="' . esc_attr($content['url']) . '" Target="_blank" />';
        }

        echo '</' . $content['type'] . '>';

        return $return;
    }
}

$piecemakers = get_option('ipt_colorized_piecemaker_op');

$settings = ipt_colorized_xml_shortcut(get_option('ipt_colorized_piecemaker_settings'));

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header('Content-Type: application/xml');

$active_piecemakers = array();
?>
<?php echo '﻿<?xml version="1.0" encoding="utf-8"?>'; ?>
<Piecemaker>
    <Contents>
        <?php foreach($piecemakers['contents'] as $key => $content) :
            $active_piecemakers[] = ipt_colorized_xml_content($content, $key);
        endforeach; ?>
    </Contents>
    <Settings ImageWidth="800" ImageHeight="356" <?php echo $settings; ?>></Settings>
    <Transitions>
        <?php foreach($active_piecemakers as $pk) :
            if($pk !== false) :
                $t_settings = ipt_colorized_xml_shortcut($piecemakers['transitions'][$pk]);
                echo "<Transition {$t_settings}></Transition>";
            endif;
        endforeach; ?>
    </Transitions>
</Piecemaker>

