<?php
/*
Simple:Press Forum
Ahah call for Auto Update
$LastChangedDate: 2009-01-16 20:14:58 +0000 (Fri, 16 Jan 2009) $
$Rev: 1230 $
*/

require_once("../../sf-config.php");

sf_load_foundation();

global $current_user, $sfglobals, $wpdb;

# get out of here if no target specified
if (empty($_GET['target'])) die();
$target = sf_syscheckstr($_GET['target']);

# First do check to see if user is logged in

if($target == 'checkuser')
{
	$thisuser = sf_syscheckint($_GET['thisuser']);
	if($current_user->ID == 0 || $current_user->ID == '')
	{
		if($thisuser != 0 || $thisuser != '')
		{
			$out = '<div style="border: 1px solid #666666; padding: 10px; font-weight: bold;">';
			$out.= '<p>'.__("Your Session has Expired - ", "sforum");
			$out.= '<a style="text-decoration: underline;" href="'.SFLOGIN.'">'.__("Log Back In", "sforum").'</a></div>';
			echo $out;
		}
	}
	die();
}

sf_initialise_globals();

# Update the new post Quicklinks
if($target == 'quicklinks')
{
	$ql = get_option('sfquicklinks');
	if($ql['sfqlshow'] && $ql['sfqlcount'] > 0)
	{
		echo sf_render_newpost_quicklinks($ql['sfqlcount']);
	}
	die();
}

# Update the Inbox Count
if($target == 'inbox')
{
	$out='';
	$out.= sf_render_watch_count();
	$out.= sf_render_inbox_count();
	echo $out;
	die();
}

# Update the New Post Counts
if($target == 'newposts')
{
	if($current_user->adminstatus || $current_user->moderator)
	{
		$newposts = sf_get_admins_queued_posts();
		echo sf_get_waiting_url($newposts, '', false);
	}
	die();
}

# Update Inbox/Sentbox lists
if($target == 'pmview')
{
	$box = $_GET['show'];

	# Load up the data we need
	if($box == 'inbox')
	{
		$messagebox = sf_get_pm_inbox($current_user->ID);
	} else {
		$messagebox = sf_get_pm_sentbox($current_user->ID);
	}

	# Grab message count
	$messagecount = $wpdb->get_var("SELECT FOUND_ROWS()");

	echo sf_render_pm_table($box, $messagebox, $messagecount, true);

	die();
}

	die();
?>