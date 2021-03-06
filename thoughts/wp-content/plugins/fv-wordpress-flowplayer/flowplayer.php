<?PHP
/*
Plugin Name: FV Wordpress Flowplayer
Plugin URI: http://foliovision.com/seo-tools/wordpress/plugins/fv-wordpress-flowplayer
Description: Embed videos (FLV, H.264, and MP4) into posts or pages. Uses modified version of flowplayer (with removed FP logo and copyright notice). 
Version: 0.9.15.
Author: Foliovision
Author URI: http://foliovision.com/
*/


if(is_admin()) {
	/**
	 * If administrator is logged, loads the controller for backend.
	 */
	include( dirname( __FILE__ ) . '/controller/backend.php' );
} else {
	/**
	 * If administrator is not logged, loads the controller for frontend.
	 */
	include( dirname( __FILE__ ) . '/controller/frontend.php' );
}


?>
