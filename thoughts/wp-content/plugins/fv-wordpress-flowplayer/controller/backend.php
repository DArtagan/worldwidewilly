<?php

/**
 * Needed includes
 */
include dirname( __FILE__ ) . '/../models/flowplayer.php';
include dirname( __FILE__ ) . '/../models/flowplayer-backend.php';

/**
 * Create the flowplayer_backend object
 */
$fp = new flowplayer_backend();

/**
 * WP Hooks
 */
add_action('admin_head', 'flowplayer_head');
add_action('admin_menu', 'flowplayer_admin');
/**
 * END WP Hooks
 */


/**
 * Administrator environment function.
 */
function flowplayer_admin () {
	
	// if we are in administrator environment
	if (function_exists('add_submenu_page')) {
		add_options_page(
							'FV Wordpress Flowplayer', 
							'FV Wordpress Flowplayer', 
							8, 
							basename(__FILE__), 
							'flowplayer_page'
						);
	}
}

/**
 * Outputs HTML code for bool options based on arg passed.
 * @param string Currently selected value ('true' or 'false').
 * @return string HTML code
 */
function flowplayer_bool_select($current) {
	switch($current) {
		 		case "true":
		 			$html = '<option selected="selected" value="true">true</option><option value="false">false</option>';
		 			break;
		 		case "false":
		 			$html = '<option value="true" >true</option><option selected="selected" value="false">false</option>';
		 			break;
		 		default:
		 			$html = '<option value="true">true</option><option selected="selected" value="false">false</option>';
		 			break;
		 	}
		 return $html;
}

/**
 * Displays administrator menu with configuration.
 */
function flowplayer_page() {
	//initialize the class:
	$fp = new flowplayer();
	include dirname( __FILE__ ) . '/../view/admin.php';
}

/**
 * Checks for errors regarding access to configuration file. Displays errors if any occur.
 * @param object $fp Flowplayer class object.
 */
function flowplayer_check_errors($fp){
	$html = '';
	// config file checks, exists, readable, writeable
	$conf_file = realpath(dirname(__FILE__)).'/wpfp.conf';
	if(!file_exists($conf_file)){
		$html .= '<h3 style="font-weight: bold; color: #ff0000">'.$conf_file.' Does not exist please create it</h3>';
	} elseif(!is_readable($conf_file)){
		$html .= '<h3 style="font-weight: bold; color: #ff0000">'.$conf_file.' is not readable please check file permissions</h3>';
	} elseif(!is_writable($conf_file)){
		$html .= '<h3 style="font-weight: bold; color: #ff0000">'.$conf_file.' is not writable please check file permissions</h3>';
	}
}


?>
