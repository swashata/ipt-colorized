jQuery(document).ready(function($) {
    $('ul.menu').superfish({
        hoverClass : 'hover',
        animation : {height: 'show', opacity: 'show'}
    });

    if($('#home_contents').length) {
        $('#home_contents').bxSlider({
            mode : 'vertical',
            slideWidth : 960,
            pager : true,
            auto : true,
            autoHover : true,
            controls : false
        });
    }

    if($('#bxcarousel').length) {
        $('#bxcarousel').bxSlider({
            maxSlides: 3,
            minSlides: 3,
            slideWidth: 296,
            slideMargin: 36,
            auto : true,
            autoHover : true
        });
    }

    if($('#ipt_colorize_bubble').length) {
        var positions = new Array();
        var bubble_init = function() {

            if(!$('#ipt_colorize_bubble').length) {
                return;
            }

            var win_height = $(window).height()/5;
            var win_width = $(window).width()/5;

            if(positions.length == 0) {
                for(var i = 0; i < 5; i++) {
                    for(var j = 0; j < 5; j++) {
                        positions[positions.length] = new Array(i, j);
                    }
                }

                positions.sort(function() {
                    return 0.5 - Math.random();
                });


            }

            //alert(positions.length);
            $('#ipt_colorize_bubble').css({
                'position' : 'fixed',
                'z-index' : '-99999',
                'overflow' : 'hidden',
                'top' : '0',
                'left' : '0',
                'margin' : '0',
                'padding' : '0',
                'height' : $(window).height() + 'px',
                'width' : $(window).width() + 'px'
            });
            $('#ipt_colorize_bubble').show();
            var count = 0;
            //alert('Position ' + positions.length);
            //alert('DOM ' + $('#ipt_colorize_bubble .ipt_colorize_bubble').length);
            $('#ipt_colorize_bubble .ipt_colorize_bubble').each(function() {
                //alert(count);
                var this_pos_x = getRandomInt(positions[count][0] * win_width, (positions[count][0]+1) * win_width);
                var this_pos_y = getRandomInt(positions[count][1] * win_height, (positions[count][1]+1) * win_height);
                count++;

                $(this).css({
                    'left' : this_pos_x + 'px',
                    'top' : this_pos_y + 'px'
                });
                //$(this).html('left: ' + this_pos_x + ' right: ' + this_pos_y);

                $(this).jqFloat({
                    width:getRandomInt(win_width * 3, win_width * 4),
                    height:getRandomInt(win_height * 3, win_height * 4),
                    speed:getRandomInt(parseInt(colBubble.min), parseInt(colBubble.max))
                });
            });
            //alert('Count ' + count);
        }

        $(window).load(function() {
            bubble_init();
        });
        $(window).resize(function() {
            bubble_init();
        })
    }

    function getRandomInt (min, max) {
        if(max < min) { //swap
            var temp = max;
            max = min;
            min = temp;
        }
        if(max == min) {
            return Math.floor(max);
        }
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
});