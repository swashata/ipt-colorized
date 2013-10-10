<?php global $theme_op; ?>
        <!-- Footer -->
        <div id="footer_wrap" class="<?php echo (is_front_page() && !is_home() ? 'static-front' : ''); ?>">
            <div id="footer_inner">
                <div id="footer" class="ninesixty">
                    <div id="footer_one">
                        <?php dynamic_sidebar('footer-sidebar'); ?>
                    </div>
                    <div id="footer_two">
                        <?php global $theme_op; ?>
                        <?php echo wpautop(wptexturize($theme_op['footer'])); ?>
                        <?php if('yes' == $theme_op['show_credit']) : ?>
                        <p class="credit">
                            Powered By - <a href="http://wordpress.org">WordPress</a><br />
                            Designed By - <a href="http://www.ipanelthemes.com">iPanelThemes</a>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="clear"></div>
                    <?php wp_footer(); ?>
                </div>
            </div>
        </div>
        <!-- ENd Footer -->
        <?php
        if($theme_op['bubble'] == 'yes') {
            ?>
        <div style="height: 2px; width: 100%; overflow: hidden; clear: both"></div>
        <div id="ipt_colorize_bubble" style="display: none">
            <?php for($i = 1; $i <= 5; $i++) : ?>
                <?php for($j = 1; $j <= 5; $j++) : ?>
            <div class="ipt_colorize_bubble type_<?php echo $i; ?> num_<?php echo $j; ?>"></div>
                <?php endfor; ?>
            <?php endfor; ?>
        </div>
        <div id="ipt_colorize_bubble_overflow"></div>
            <?php
        }
        ?>
    </body>
</html>
