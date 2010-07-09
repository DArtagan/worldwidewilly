<?php
/*
Simple:Press Forum
wp-user-switches.php - user definable switches
$LastChangedDate: 2009-04-08 16:26:36 +0100 (Wed, 08 Apr 2009) $
$Rev: 1699 $
*/

	# --------------------------------------------------------
	# With WP 2.6 it is possible to relocate the wp-config.php
	# file. Simple:Press Forum should be able to find it.
	# However, if it does not, then you will need to change
	# the SF_BASEPATH constant below to point to the path of:
	#
	# for WP 2.5:
	# 		your wp-config.php file
	#
	# for WP 2.6 and above:
	#		your wp-load.php file
	# --------------------------------------------------------

	define('SF_BASEPATH', dirname(dirname(dirname(dirname(__FILE__)))));


	# --------------------------------------------------------
	# Script concatenation is introduced in WP version 2.8.
	# Sadly, out if the box, some of the jQuery code throws
	# an error.
	# This is turned off for the forum admin and should
	# not effect settings for WP or other plugins
	# --------------------------------------------------------

	define('CONCATENATE_SCRIPTS', false);


	# --------------------------------------------------------
	# A small javascript program has been used to replace
	# checkboxes and radio buttons with more appealing
	# graphics. A small number of users ave experienced a
	# conflict with this js library. If you have this problem
	# please set SF_USE_PRETTY_CBOX to false.
	# --------------------------------------------------------

	define('SF_USE_PRETTY_CBOX', true);

?>