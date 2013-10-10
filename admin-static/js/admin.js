/**
 * The main javascript file for iPanel Admin
 * @package Colorized Theme
 * @subpackage Admin area JS
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @version 1.0.0
 */

jQuery(document).ready(function($) {

    if(jQuery('.upload-button').length) {
        var uploadID = ''; /*setup the var in a global scope*/
        jQuery('.upload-button').button().bind('click', function() {
            uploadID = jQuery(this).prev('input'); /*set the uploadID variable to the value of the input before the upload button*/
            formfield = jQuery('.upload').attr('name');
            tb_show('', 'media-upload.php?type=image&ampTB_iframe=true');
            return false;
        });

        window.send_to_editor = function(html) {
            imgurl = jQuery('img',html).attr('src');
            uploadID.val(imgurl); /*assign the value of the image src to the input*/
            tb_remove();
        };
    }

    if($('.sortable_ul').length) {
        $('.sortable_ul li').button({icons : {primary : 'ui-icon-arrow-4-diag'} });
        $('.sortable_ul').sortable({
            update : function() {
                var new_pos = $(this).sortable('toArray');
                var h_id = $(this).attr('data-value');
                for(var pos in new_pos) {
                    $('#' + h_id + '_' + pos).attr('value', $('#' + new_pos[pos]).attr('data-value'));
                }
            }
        });
    }

    if($('.accordion').length) {
        $('.accordion').accordion({header: 'h3.acc_head', autoHeight: false});
    }

    if($("#tabs").length) {
        $("#tabs").tabs({
            fx: {opacity: 'toggle', speed: 'fast'}
        });
    }

    if($('span.help').length) {
        var dialog_content;
        var title = 'Help';
        $('span.help').click(function(e) {
            e.preventDefault();
            var title = 'Help!';

            if(undefined != $(this).attr('title')) {
                title = $(this).attr('title');
            } else {
                var temp;
                if(undefined != (temp = $(this).parent().siblings('th').find('label').html()))
                    title = temp;
                else
                    title = 'Help!';
            }

            var dialog_content = $('<div><div style="padding: 10px;">' + '<p class="' + $(this).attr('class') + '">' + $(this).html() + '</p></div></div>');
            dialog_content.dialog({
                autoOpen: true,
                buttons: {"Ok": function() {$(this).dialog("close");}},
                modal: true,
                minWidth: 400,
                closeOnEscape: true,
                title: title
            });
            return false;
        });
//        $('span.help').each(function(e) {
//            //var button = $('<a href="#" class="help_button" title="Click to display a quick help!"></a>').insertAfter($(this));
//            var button = $(this);
//
//            button.click(function(e) {
//
//            });
//
//        });
    }

    if($('.slider').length) {
        $('.slider').each(function() {
            var target = $(this).next('input');
            target.attr('readonly', true);

            var step = ((target.attr('step'))? parseFloat(target.attr('step')) : 1);
            if(isNaN(step))
                step = 1;

            var value = parseFloat(target.val());
            if(isNaN(value))
                value = 0;

            var min = parseFloat(target.attr('min'));
            if(isNaN(min))
                min = 1;

            var max = parseFloat(target.attr('max'));
            if(isNaN(max))
                max = 9999;

            var slider = $(this).slider({
                value: value,
                min: min,
                max: max,
                step: step,
                slide: function(event, ui) {
                    target.val(ui.value);
                }
            });

            target.change(function() {
                slider.slider({value: parseFloat(target.val())});
            });
        });
    }

    if($('.slider_range').length) {
        $('.slider_range').each(function() {
            var target = $(this).next('.slider_range_inputs').find('input.slider-text-min');
            target.attr('readonly', true);

            var target_max = target.next('input.slider-text-max');
            target_max.attr('readonly', true);

            var step = ((target.attr('step'))? parseFloat(target.attr('step')) : 1);
            if(isNaN(step))
                step = 1;

            var value = new Array();
            value[0] = parseFloat(target.val());
            if(isNaN(value[0]))
                value[0] = 0;
            value[1] = parseFloat(target_max.val());
            if(isNaN(value[1]))
                value[1] = 0;

            var min = parseFloat(target.attr('min'));
            if(isNaN(min))
                min = 1;

            var max = parseFloat(target.attr('max'));
            if(isNaN(max))
                max = 9999;

            var slider = $(this).slider({
                range : true,
                values: value,
                min: min,
                max: max,
                step: step,
                slide: function(event, ui) {
                    target.val(ui.values[0]);
                    target_max.val(ui.values[1]);
                }
            });

            target.change(function() {
                slider.slider({
                    values: [parseFloat(target.val()), parseFloat(target_max.val())]
                });
            });
            target_max.change(function() {
                slider.slider({
                    values: [parseFloat(target.val()), parseFloat(target_max.val())]
                });
            });
        });
    }

    if($('.color-picker').length) {
        $('.color-picker').each(function() {
            var elem = this;
            $(elem).ColorPicker({
                onBeforeShow : function() {
                    $(this).ColorPickerSetColor(this.value);
                    $(this).css('backgroundColor', '#' + this.value);
                },
                onShow: function(colpkr) {
                    $(colpkr).fadeIn('fast');
                    return false;
                },
                onHide: function(colpkr) {
                    $(colpkr).fadeOut('normal');
                    return false;
                },
                onChange: function(hsb, hex, rgb) {
                    $(elem).css('backgroundColor', '#' + hex);
                    $(elem).val(hex);
                },
                onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val(hex);
                    $(el).css('backgroundColor', '#' + hex);
                    $(el).ColorPickerHide();
                }
            })
        });
    }

    if($('.datetimepicker').length) {
        $('.datetimepicker').datetimepicker({timeFormat : "hh:mm:ss"});
    }
    if($('.datepicker').length) {
        $('.datepicker').datepicker({dateFormat: "yy-mm-dd"});
    }

    if($('fieldset.widefat legend input[type="checkbox"]').length) {

        $('fieldset.widefat legend input[type="checkbox"]').change(function() {
            var target = $(this).parent().siblings('div.toggle');
            if(this.checked) {
                target.stop(true, true).animate({opacity: 'toggle', height: 'toggle'}, 'fast');
            } else {
                target.stop(true, true).animate({opacity: 'toggle', height: 'toggle'}, 'fast');
            }
        })
    }

    //fix thickbox dynamic height/width adjustment
    tb_position = function() {
        var tbWindow = $('#TB_window'), width = $(window).width(), H = $(window).height(), W = ( 1024 < width ) ? 1024 : width, adminbar_height = 0;

        if ( $('body.admin-bar').length )
                adminbar_height = 28;

        if ( tbWindow.size() ) {
                tbWindow.width( W - 50 ).height( H - 45 - adminbar_height );
                $('#TB_iframeContent').width( W - 50 ).height( H - 75 - adminbar_height );
                $('#TB_ajaxContent').width( W - 80 ).height( H - 95 - adminbar_height );
                tbWindow.css({'margin-left': '-' + parseInt((( W - 50 ) / 2),10) + 'px'});
                if ( typeof document.body.style.maxWidth != 'undefined' )
                        tbWindow.css({'top': 20 + adminbar_height + 'px','margin-top':'0'});
        };

        return $('a.thickbox').each( function() {
                var href = $(this).attr('href');
                if ( ! href ) return;
                href = href.replace(/&width=[0-9]+/g, '');
                href = href.replace(/&height=[0-9]+/g, '');
                $(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 - adminbar_height ) );
        });
    };

    $(window).resize(function(){ tb_position(); });

    $('a.delete, a.trash').click(function() {
        return confirm('Are you sure? This can not be undone');
    });

});

