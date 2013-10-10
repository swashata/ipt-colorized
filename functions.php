<?php
/**
 * Colorized Theme functions.php
 */

require_once get_stylesheet_directory() . '/classes/loader.php';
require_once get_stylesheet_directory() . '/classes/sponsors-widget.php';

if(is_admin()) {
    require_once get_stylesheet_directory() . '/classes/admin.php';
} else {

}

$colorized_loader = new ipt_colorized_theme_loader(__FILE__, '1.0.0');


if ( ! function_exists( 'colorized_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @since Twenty Ten 1.0
 */
function colorized_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'colorized' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'colorized' ), get_the_author() ) ),
			get_the_author()
		)
	);
}
endif;

if ( ! function_exists( 'colorized_posted_in' ) ) :
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 *
 * @since Twenty Ten 1.0
 */
function colorized_posted_in() {
	// Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted on %5$s by %6$s in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'colorized' );
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = __( 'This entry was posted on %5$s by %6$s in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'colorized' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'colorized' );
	}
	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' ),
                sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'colorized' ), get_the_author() ) ),
			get_the_author()
		)
	);
}
endif;


if(!function_exists('vt_resize')) :
/**
 * Resize images dynamically using wp built in functions
 * Victor Teixeira
 *
 * Modified by Foxinni, 23-07-2012 (Added multisite support)
 *
 * php 5.2+
 *
 * Exemplo de uso:
 *
 * <?php
 * $thumb = get_post_thumbnail_id();
 * $image = vt_resize( $thumb, '', 140, 110, true );
 * ?>
 * <img src="<?php echo $image[url]; ?>" width="<?php echo $image[width]; ?>" height="<?php echo $image[height]; ?>" />
 *
 * @global int $blog_id
 * @param int $attach_id
 * @param string $img_url
 * @param int $width
 * @param int $height
 * @param bool $crop
 * @return array
 */
function vt_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {


       global $blog_id;

       // this is an attachment, so we have the ID
       if ( $attach_id ) {

               $image_src = wp_get_attachment_image_src( $attach_id, 'full' );
               $file_path = get_attached_file( $attach_id );

       // this is not an attachment, let's use the image url
       } else if ( $img_url ) {

               $file_path = parse_url( $img_url );
               $file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];

               //Check for MultiSite blogs and str_replace the absolute image locations
               if(is_multisite()){
                       $blog_details = get_blog_details($blog_id);
                       $file_path = str_replace($blog_details->path . 'files/', '/wp-content/blogs.dir/'. $blog_id .'/files/', $file_path);
               }

               //$file_path = ltrim( $file_path['path'], '/' );
               //$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];

               $orig_size = getimagesize( $file_path );

               $image_src[0] = $img_url;
               $image_src[1] = $orig_size[0];
               $image_src[2] = $orig_size[1];
       }

       $file_info = pathinfo( $file_path );
       $extension = '.'. $file_info['extension'];

       // the image path without the extension
       $no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];

       /* Calculate the eventual height and width for accurate file name */

       if ( $crop == false ) {
               $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
               $width = $proportional_size[0];
               $height = $proportional_size[1];
       }

       $cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;

       // checking if the file size is larger than the target size
       // if it is smaller or the same size, stop right here and return
       if ( $image_src[1] > $width || $image_src[2] > $height ) {

               // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
               if ( file_exists( $cropped_img_path ) ) {

                       $cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );

                       $vt_image = array (
                               'url' => $cropped_img_url,
                               'width' => $width,
                               'height' => $height
                       );

                       return $vt_image;
               }

               // $crop = false
               if ( $crop == false ) {

                       // calculate the size proportionaly
                       $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
                       $resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;

                       // checking if the file already exists
                       if ( file_exists( $resized_img_path ) ) {

                               $resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

                               $vt_image = array (
                                       'url' => $resized_img_url,
                                       'width' => $proportional_size[0],
                                       'height' => $proportional_size[1]
                               );

                               return $vt_image;
                       }
               }

               // no cache files - let's finally resize it
               $new_img_path = image_resize( $file_path, $width, $height, $crop );
               $new_img_size = getimagesize( $new_img_path );
               $new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

               // resized output
               $vt_image = array (
                       'url' => $new_img,
                       'width' => $new_img_size[0],
                       'height' => $new_img_size[1]
               );

               return $vt_image;
       }

       // default output - without resizing
       $vt_image = array (
               'url' => $image_src[0],
               'width' => $image_src[1],
               'height' => $image_src[2]
       );

       return $vt_image;
}
endif;

if ( ! function_exists( 'ipt_colorized_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own colorize_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Ten 1.0
 */
function ipt_colorized_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">
		<div class="comment-author vcard">
			<?php echo get_avatar( $comment, 56 ); ?>
			<?php printf( __( '%s <span class="says">says:</span>', 'colorized' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
		</div><!-- .comment-author .vcard -->
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'colorized' ); ?></em>
		<?php endif; ?>

		<div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __( '%1$s at %2$s', 'colorized' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'colorized' ), ' ' );
			?>
		</div><!-- .comment-meta .commentmetadata -->

		<div class="comment-body"><?php comment_text(); ?></div>

		<div class="reply">
			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		</div><!-- .reply -->
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'colorized' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'colorized' ), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}
endif;
