<?php
/*
Simple:Press Forum Admin
Ahah call for Amdin tools (from options)
$LastChangedDate: 2009-02-19 19:41:15 +0000 (Thu, 19 Feb 2009) $
$Rev: 1426 $
*/

require_once("../../sf-config.php");
require_once('../../forum/sf-primitives.php');

# Check Whether User Can Manage Options
	if (!sf_current_user_can('SPF Manage Options') &&
	    !sf_current_user_can('SPF Manage Forums') && 
	    !sf_current_user_can('SPF Manage Components') && 
	    !sf_current_user_can('SPF Manage User Groups') &&
	    !sf_current_user_can('SPF Manage Permissions') &&
	    !sf_current_user_can('SPF Manage Database') &&
	    !sf_current_user_can('SPF Manage Admins') &&
		!sf_get_member_item($current_user->ID, 'moderator'))
	{
		echo (__('Access Denied', "sforum"));
		die();
}

if (isset($_GET['item']))
{
	$item = sf_syscheckstr($_GET['item']);
	if($item == 'upperm') sfa_update_permalink_tool();
	if($item == 'upcheck') sfa_sf_check_for_updates(get_option('sfversion'), get_option('sfbuild'), false);
	if($item == 'inlinecheck') sfa_sf_check_for_updates(get_option('sfversion'), get_option('sfbuild'), true);
}

die();

function sfa_update_permalink_tool()
{
	global $wpdb, $wp_rewrite;
	
	$slug = get_option('sfslug');	
	$pageid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$slug."'");
	if ($pageid)
	{
		update_option('sfpage', $pageid);
		$perm = get_permalink($pageid);
		if(get_option('page_on_front') == $pageid && get_option('show_on_front') == 'page')
		{
			$perm.= '/'.$slug;
		}
		update_option('sfpermalink', $perm);
	}
	$wp_rewrite->flush_rules();

	echo '<strong>&nbsp;'.$perm.'</strong>';
	die();
}

function sfa_sf_check_for_updates($version, $build, $inline=false)
{
	$checkfile = SFVERCHECK;
	
	$vcheck = wp_remote_fopen($checkfile);
	if($vcheck)
	{
		$status = explode('@', $vcheck);
		if(isset($status[1]))
		{
			$theVersion = $status[1];
			$theBuild   = $status[3];
			$theMessage = $status[5];
	
			if((version_compare(floatval($theVersion), floatval($version), '>') == 1) || (version_compare(intval($theBuild), intval($build), '>') == 1))
			{
				if($inline)
				{
					$msg = __("Latest version available:", "sforum").' <strong>'.$theVersion.'</strong> '.__("Build:", "sforum").' <strong>'.$theBuild.'</strong> - '.$theMessage;
				} else {
					$msg = __("Latest version available:", "sforum").' <br /><strong>'.$theVersion.'</strong><br />';
					$msg.= __("Build:", "sforum").' <strong>'.$theBuild.'</strong><br />';
					$msg.= $theMessage;
				}
				if($inline)
				{
					echo '<span style="float: left; border:1px solid silver; background: #FFFFCC; padding: 4px 4px 0px 4px;margin: 5px 12px 0px 0px;">'.$msg.'</span>';
					echo '<div class="clearboth"></div>';
				} else {
					echo $msg;
				}
			} else {
				if($inline) return;
				$msg = __("Your system is up to date", "sforum");
				echo $msg;
			}
		}
	} else {
		if(!$inline)
		{
			echo __("Unable to check - your host has disabled reading remote files", "sforum");
		}
	}
	
	return;
}

die();

?>