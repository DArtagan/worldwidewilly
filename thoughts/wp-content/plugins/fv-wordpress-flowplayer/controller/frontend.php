<?php

/**
 * Needed includes
 */
include dirname( __FILE__ ) . '/../models/flowplayer.php';
include dirname( __FILE__ ) . '/../models/flowplayer-frontend.php';

/**
 * WP Hooks
 */
add_action('wp_head', 'flowplayer_head');
add_action('the_content', 'flowplayer_content');
add_action('wp_footer','flowplayer_display_scripts');
//	Addition for 0.9.15
add_action('widget_text','flowplayer_content');

/**
 * END WP Hooks
 */
 
$GLOBALS['scripts'] = array();

/**
 * Replaces the flowplayer tags in post content by players and fills the $GLOBALS['scripts'] array.
 * @param string Content to be parsed
 * @return string Modified content string
 */
function flowplayer_content( $content ) {
	
	$content_matches = array();
	preg_match_all('/\[flowplayer\ [a-z0-9\:\.\-\&\_\/\,\=\ \<\>\"\'\@\;\-\n\(\)\%\!]+\]/i', $content, $content_matches);
		
	// process all found tags
	foreach ($content_matches[0] as $tag) {
	
		// temporarily replace spaces between quotes with "_"
		$newtag = $tag;
		$in_quote = false;
		for ($i = 0; $i<strlen($tag); $i++) {
			if ($tag[$i] == "'") {
				if ($in_quote == false) {
					$in_quote = true;
				} else {
					$in_quote = false;
				}
			}
			if ($in_quote == true) {
				if (($tag[$i] == ' ')) $newtag[$i] = '_';
				if (($tag[$i] == "\n")) $newtag[$i] = '';
			}
			
		}
		
	
		// split submitted tag into individual arguments (delimited by spaces and/or commas)
		$submitted_args = preg_split("/[\n\s,\[\]]+/",$newtag,-1,PREG_SPLIT_NO_EMPTY);

		// decode the arguments (key and value are separated by "=")		
		$media = '';
		$arguments = array();
		foreach ($submitted_args as $a) {
			$arg = explode("=",$a,2);
			if (count($arg) == 2) {
				if ($arg[0] == 'src') {
					$media = $arg[1];
				} else {
					$arguments[$arg[0]] = $arg[1];
				}
			}
		}
		
		if (trim($media) != '') {
			// build new player
			$fp = new flowplayer_frontend();
         		$new_player = $fp->build_min_player($media,$arguments);
			$content = str_replace($tag, $new_player['html'],$content);
			$GLOBALS['scripts'][] = $new_player['script'];
		}
	}
	
	return $content;
}


/**
 * Prints flowplayer javascript content to the bottom of the page.
 */
function flowplayer_display_scripts() {
	if (!empty($GLOBALS['scripts'])) {
		echo "\n<script defer=\"defer\" type=\"text/javascript\">\n<!--\n\n";
		foreach ($GLOBALS['scripts'] as $scr) {
			echo $scr;
		}
		echo "\n\n//-->\n</script>\n";
	}
}

/**
 * This is the template tag. Use the standard Flowplayer shortcodes
 */

function flowplayer($shortcode) {
	echo apply_filters('the_content',$shortcode);
}








?>
