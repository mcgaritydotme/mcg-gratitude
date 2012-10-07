<?php
/*
Plugin Name: Gratitudes
Plugin URI: http://gratitude.mcgarity.me/
Description: Adds a Gratitude custom post type to your WordPress.org site, allowing you to maintain a daily gratitude journal.
Author: Matthew McGarity
Version: 0.2
Author URI: http://mcgarity.me
*/

class MCG_Gratitude {

	private static $mcg_gratitude_metabox = array(
		'name' => 'mcg_gratitude_meta',
		'std' => '',
		'title' => 'Gratitudes',
		'description' => 'Today, I am grateful for...' );

	/*
     * Setup Gratitude custom post type
     */
	function setup_cpt() {

		$labels = array(
			'name' => _x( 'Gratitudes', 'post type general name' ),
			'singular_name' => _x( 'Gratitude', 'post type singular name' ),
			'add_new' => _x( 'Add New', 'gratitude' ),
			'add_new_item' => __( 'Add New Gratitude' ),
			'edit_item' => __( 'Edit Gratitude' ),
			'new_item' => __( 'New Gratitude' ),
			'view_item' => __( 'View Gratitude' ),
			'search_items' => __( 'Search Gratitudes' ),
			'not_found' =>  __( 'No gratitudes found' ),
			'not_found_in_trash' => __( 'No gratitudes found in Trash' ),
			'parent_item_colon' => '',
			'menu_name' => 'Gratitudes'
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'rewrite' => array( 'slug' => 'gratitudes' ),
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'comments' ),
		);

		register_post_type( 'mcg-gratitude' , $args );

	}

	function mcg_gratitude_metabox_display() {

		//global $post, self::$mcg_gratitude_metabox;
		global $post;

		echo '<p>' . self::$mcg_gratitude_metabox['description'] . '</p>';

		for ( $i = 1; $i <= 3; $i++ ) {

			echo'<input type="hidden" name="'.self::$mcg_gratitude_metabox['name'].$i.'_noncename" id="'.self::$mcg_gratitude_metabox['name'].$i.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
			echo'<p><label for="'.self::$mcg_gratitude_metabox['name'].$i.'_value">Gratitude #'.$i.'</label>';
			echo'<input type="text" name="'.self::$mcg_gratitude_metabox['name'].$i.'_value" value="'. get_post_meta( $post->ID, self::$mcg_gratitude_metabox['name'].$i.'_value', true ) .'" size="55" /><br /></p>';

		}

	}

	function mcg_gratitude_metabox_create() {

		add_meta_box( 'mcg_gratitude_meta', 'Gratitudes', array( $this, 'mcg_gratitude_metabox_display' ), 'mcg-gratitude', 'normal', 'high' );

	}

	function mcg_save_gratitudes( $post_id ) {

		global $post;

		for ( $i = 1; $i <= 3; $i++ ) {

			if ( !wp_verify_nonce( $_POST[self::$mcg_gratitude_metabox['name'].$i.'_noncename'], plugin_basename( __FILE__ ) ) ) {
				return $post_id;
			}

			if ( 'page' == $_POST['post_type'] ) {
				if ( !current_user_can( 'edit_page', $post_id ) )
					return $post_id;
			} else {
				if ( !current_user_can( 'edit_post', $post_id ) )
					return $post_id;
			}

			$data = $_POST[self::$mcg_gratitude_metabox['name'].$i.'_value'];

			if ( get_post_meta( $post_id, self::$mcg_gratitude_metabox['name'].$i.'_value' ) == "" )
				add_post_meta( $post_id, self::$mcg_gratitude_metabox['name'].$i.'_value', $data, true );
			elseif ( $data != get_post_meta( $post_id, self::$mcg_gratitude_metabox['name'].$i.'_value', true ) )
				update_post_meta( $post_id, self::$mcg_gratitude_metabox['name'].$i.'_value', $data );
			elseif ( $data == "" )
				delete_post_meta( $post_id, self::$mcg_gratitude_metabox['name'].$i.'_value', get_post_meta( $post_id, self::$mcg_gratitude_metabox['name'].$i.'_value', true ) );

		}

	}

	/*
     * Include Gratitudes within loops
     * Adapted from code sample on Bajada.net (http://bit.ly/oiPk5X)
     */
	function mcg_include_gratitudes_in_loop( $query ) {

		global $wp_query;

		/*
         * Don't break admin or preview pages. This is also a good place to exclude
         * feed with !is_feed() if desired.
         */
		if ( !is_preview() && !is_admin() && !is_singular() ) {

			$post_types = array( 'post', 'mcg-gratitude' );

			if ( $query->is_feed ) {
				/*
             * Do feed processing here if you did not exclude it previously. This
             * if/else is not necessary if you want custom post types included in
             * your feed.
             */
			} else {
				$my_post_type = get_query_var( 'post_type' );
				if ( empty( $my_post_type ) )
					$query->set( 'post_type' , $post_types );
			}
		}

		return $query;

	}

	function mcg_display_gratitude_meta( $content ) {

		global $post;

		if ( get_post_type( $post ) == 'mcg-gratitude' ) {

			$mcg_gratitude_return = '<p>On this date, I was grateful for...</p>';
			$mcg_gratitude_return = $mcg_gratitude_return . '<ol>' . "\n";
			$mcg_gratitude_return = $mcg_gratitude_return . '<li id="' . $post->ID . '-gratitude-1">' . get_post_meta( $post->ID, 'mcg_gratitude_meta1_value', true ) . '</li>' . "\n";
			$mcg_gratitude_return = $mcg_gratitude_return . '<li id="' . $post->ID . '-gratitude-2">' . get_post_meta( $post->ID, 'mcg_gratitude_meta2_value', true ) . '</li>' . "\n";
			$mcg_gratitude_return = $mcg_gratitude_return . '<li id="' . $post->ID . '-gratitude-3">' . get_post_meta( $post->ID, 'mcg_gratitude_meta3_value', true ) . '</li>' . "\n";
			$mcg_gratitude_return = $mcg_gratitude_return . '</ol>';

		}

		return $content . $mcg_gratitude_return;

	}

	/*
     * Default Gratitude's post title with current date
     * Adapted from code sample by John Kolbert (http://bit.ly/qF4qK0)
     */
	function mcg_change_default_title_1( $title ) {

		$screen = get_current_screen();

		if  ( 'mcg-gratitude' == $screen->post_type ) {
			// 0.2 Fix issue with date not accounting for GMT offset
			//$title = date( get_option('date_format') );
			$title = date_i18n( get_option( 'date_format' ), time() + ( get_option( 'gmt_offset' ) * 3600 ) );
		}

		return $title;
	}

	/*
     * Set Gratitude's post title equal to current date, if user does not override it
     */
	function mcg_change_default_title_2( $title ) {

		if ( $title == '' ) {
			// 0.2 Fix issue with date not accounting for GMT offset
			//$title = date( get_option('date_format') );
			$title = date_i18n( get_option( 'date_format' ), time() + ( get_option( 'gmt_offset' ) * 3600 ) );
		}

		return $title;
	}

	/*
     * Define an icon for use in Mobile Safari (when saving bookmarks to iOS home screens)
     */
	function mcg_apple_touch_icon() { ?>
            <link rel="apple-touch-icon-precomposed" href="<?php echo plugins_url(); ?>/mcg-gratitude/images/mcg-gratitude-apple-touch-icon.png" />
    <?php }

	/*
     * Define a custom icon for the Gratitude item in WP Admin menu
     * Adapted from code sample and icons by Randy Jensen (http://bit.ly/oe8AvE)
     */
	function mcg_admin_menu_icon() {
?>
        <style type="text/css" media="screen">
            #menu-posts-mcg-gratitude .wp-menu-image {
                background: url(<?php echo plugins_url(); ?>/mcg-gratitude/images/book-open-bookmark.png) no-repeat 6px -17px !important;
            }
            #menu-posts-mcg-gratitude:hover .wp-menu-image, #menu-posts-mcg-gratitude.wp-has-current-submenu .wp-menu-image {
                background-position:6px 7px!important;
            }
        </style>
    <?php }

	/*
     * Include Gratitude custom post types within main site feeds
     * Adapted from code sample by Andrew Wilson (http://bit.ly/nLITQP)
     */
	function mcg_include_gratitudes_in_feed( $qv ) {

		if ( isset( $qv['feed'] ) && !isset( $qv['post_type'] ) ) {
			$qv['post_type'] = array();
			$qv['post_type'] = get_post_types( $args = array(
					'public'   => true,
					'_builtin' => false
				) );
			$qv['post_type'][] = 'post';
		}

		return $qv;

	}

} // End MCG_Gratitude

if ( ! $MCG_Gratitude ) {
	$MCG_Gratitude = new MCG_Gratitude();
}

add_action( 'init',             array( $MCG_Gratitude, 'setup_cpt' ) );
add_action( 'admin_menu',       array( $MCG_Gratitude, 'mcg_gratitude_metabox_create' ) );
add_action( 'save_post',        array( $MCG_Gratitude, 'mcg_save_gratitudes' ) );
add_filter( 'pre_get_posts',    array( $MCG_Gratitude, 'mcg_include_gratitudes_in_loop' ) );
add_filter( 'the_content',      array( $MCG_Gratitude, 'mcg_display_gratitude_meta' ) );
add_filter( 'enter_title_here', array( $MCG_Gratitude, 'mcg_change_default_title_1' ) );
add_filter( 'title_save_pre',   array( $MCG_Gratitude, 'mcg_change_default_title_2' ) );
add_action( 'admin_head',       array( $MCG_Gratitude, 'mcg_apple_touch_icon' ) );
add_action( 'admin_head',       array( $MCG_Gratitude, 'mcg_admin_menu_icon' ) );
add_filter( 'request',          array( $MCG_Gratitude, 'mcg_include_gratitudes_in_feed' ) );
