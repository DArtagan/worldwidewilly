<?php
/*
Simple:Press Forum
Profile Rendering Routines
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

function sf_profile($newuser)
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-profile.php');
	return sf_render_profile_form($newuser);
}

function sf_view_permissions()
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-permissions.php');
	return sf_render_permissions_form();
}

function sf_view_buddylist()
{
	include_once(SF_PLUGIN_DIR.'/forum/forms/sf-form-buddylist.php');
	return sf_render_buddylist_form();
}

function sf_render_profile()
{
	global $sfvars, $current_user;

	# Maybe a view permissions
	if (isset($_POST['viewperms'])) return sf_view_permissions();

	# Maybe a view buddy list
	if (isset($_POST['manbuddy'])) return sf_view_buddylist();

	# Maybe a profile save
	if (isset($_POST['subprofile']))
	{
		sf_save_profile();
	}

	# Subscription manage request
	if (isset($_POST['mansubs'])) return sf_subscription_form();

	# Save subscriptions
	if (isset($_POST['uptopsubs'])) {
		sf_update_subscriptions();
		return sf_subscription_form();
	}
	
	return sf_profile($sfvars['newuser']);
}
?>