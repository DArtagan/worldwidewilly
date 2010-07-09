<?php
/*
Simple:Press Forum
wp-config.php - location support for WP 2.6
$LastChangedDate: 2009-01-14 14:16:46 +0000 (Wed, 14 Jan 2009) $
$Rev: 1201 $
*/

	include_once('sf-user-switches.php');

	if (file_exists(SF_BASEPATH.'/wp-load.php'))
	{
		# WP 2.6 and above
		require_once(SF_BASEPATH.'/wp-load.php');
	} else {
		# Pre 2.6
		require_once(SF_BASEPATH.'/wp-config.php');
	}
?>