<?php
/*
Plugin Name: Simple:Press Forum
Version: 4.0.4
Plugin URI: http://simplepressforum.com
Description: The WordPress Forum Plugin
Author: Andy Staines & Steve Klasen
Author URI: http://simplepressforum.com
WordPress Versions: 2.5 and above
For full acknowledgements click on the copyright/version strip
at the bottom of forum pages
*/

/*  Copyright 2006/2009  Andy Staines & Steve Klasen
	Please read the 'License' supplied with this plugin (goto Admin > Forum > Online Help > License
	and abide by it's few simple requests. Note that the Highslide JS library is free to use on non-commercial sites.
	Commercial sites should seek a license for a small fee of about $29US.

$LastChangedDate: 2009-07-10 10:06:09 +0100 (Fri, 10 Jul 2009) $
$Rev: 2193 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# Bootstrap
global $ISFORUM, $ISFORUMADMIN;
$ISFORUM = false;
$ISFORUMADMIN = false;

include_once('sf-startup.php');
include_once('sf-includes.php');
include_once('sf-common.php');

register_activation_hook( __FILE__, 'sfa_update_permalink' );

add_action('wp_print_scripts', 'sf_boot_forum');
add_action('admin_menu', 'sf_boot_forum_admin');

# ------------------------------------------------------------------
# sf_boot_forum()
#
# Checks if a forum page (front and back) and loads the required
# javascripts
# ------------------------------------------------------------------
function sf_boot_forum()
{
	global $ISFORUM, $ISFORUMADMIN, $wp_query;

	if (is_admin())
	{
		if ((isset($_GET['page'])) && (stristr($_GET['page'], 'simple-forum')) !== false)
		{
			sfa_admin_load_js();
			$ISFORUMADMIN=true;
		}
	} else {
		if ((is_page()) && ($wp_query->post->ID == get_option('sfpage')))
		{
			sf_load_front_js();
			$ISFORUM=true;
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_boot_forum_admin()
#
# Starts up the back end loading up the admin menus
# ------------------------------------------------------------------
function sf_boot_forum_admin()
{
	include_once('admin/sf-admin-header.php');
	require_once('forum/sf-primitives.php');
	sfa_admin_menu();
	return;
}

# required Simple:Press Forum code files
if(file_exists(SF_PLUGIN_DIR.'/forum/sf-pluggable.php'))
{
	include_once('forum/sf-pluggable.php');
}
include_once('sf-public.php');
include_once('sf-tags.php');

#==================================
//include_once('debug/sf-debug.php');
#==================================

add_filter('wp_head', 'sf_check_header');
add_filter('the_content', 'sf_setup_forum', 1);

# ------------------------------------------------------------------
# sf_check_header()
#
# Checks if this is the forum page loading and sets up the incudes
# and the page header
# Called by wp_head filter
# ------------------------------------------------------------------
function sf_check_header()
{
	global $ISFORUM;

	if($ISFORUM)
	{
		sf_load_foundation();
		sf_setup_header();
	} else {
		if(get_option('sfannounceauto'))
		{
			echo '<script type="text/javascript" src="'.SFJSCRIPT.'sf.js"></script>' . "\n";
		}
	}
}

# ------------------------------------------------------------------
# sf_setup_forum()
#
# Central Control of forum rendering
# Called by the_content filter
#	$content:	The page content
# ------------------------------------------------------------------
function sf_setup_forum($content)
{
	global $ISFORUM, $sfvars, $current_user, $sfglobals, $wpdb;

	# dont expose password protect forum info
	if ($ISFORUM && !post_password_required(get_post(get_option('sfpage'))))
	{
		# check installed version is correct (needed even though the same call is in startup!)
		if(sf_get_system_status() != 'ok') return sf_forum_unavailable();

		sf_clean_settings();
		sf_clean_sfnotice();

		$paramtype='';
		$paramvalue='';

		# deal with stuff that doesn't need sfvars (like PMs)
		# Is it a private message action?
		if (isset($_GET['pmaction']))
		{
			sf_extend_current_user();
			$pmview = "inbox";
			if($_GET['pmaction']=='viewoutpm')
			{
				$pmview = "sentbox";
			}
			if(function_exists('sf_hook_pre_content'))
			{
				$content = sf_hook_pre_content() . $content;
			}
			if(function_exists('sf_hook_post_content'))
			{
				$content .= sf_hook_post_content();
			}
			$content.= sf_message_control($pmview);

			return $content;
		}

		# set up the sfvars array for eveything else
		sf_populate_query_vars();

		# If single mode and user can just see one forum
		if(get_option('sfsingleforum'))
		{
			$fid=sf_single_forum_user();
			if($fid)
			{
				$sfvars['forumid']=$fid;
				$sfvars['forumslug']=sf_get_forum_slug($fid);
				$sfvars['forumname']=sf_get_forum_name($sfvars['forumslug']);
			}
		}

        # make sure a user exists in members table
        global $current_user;
        if (($current_user->ID != '') && ($current_user->ID != 0))
        {
        	$user = $wpdb->get_row("SELECT user_id FROM ".SFMEMBERS." WHERE user_id=".$current_user->ID);
            if (empty($user))
            {
            	include_once('forum/sf-database.php');
             	sf_create_member_data($current_user->ID);
            }
       	}

		# Load up the globals that we are going to need for this pageview/task
		sf_extend_current_user($sfvars['forumid']);

# ---------------------------------------------------------

# ---------------------------------------------------------


		# Maybe a profile edit or first time logged in?
		# If user has made no posts yet suggest they change their password
		$newuser = false;
		$sfvars['newuser'] = false;
		if ($current_user->member)
		{
			$userposts = sf_get_member_item($current_user->ID, 'posts');
			if ($userposts == -1)
			{
				$sfvars['newuser'] = true;
				sf_update_member_item($current_user->ID, 'posts', 0);
			}
		}

		# Work out just what we are rendering
		if (!empty($sfvars['forumslug']))
		{
			$pageview = 'forum';
		} else {
			$pageview = 'group';
		}
		if (!empty($sfvars['topicslug']))
		{
			$pageview = 'topic';
		}
		if (isset($_GET['profile']) || $sfvars['newuser'])
		{
			$pageview = 'profile';
		}
		if (isset($_GET['list']))
		{
			$pageview = 'list';
		}
		if(isset($_GET['policy'])) return sf_policy_form();

		if (isset($_GET['newposts'])) $pageview = 'newposts';
		if (isset($_GET['watchedtopics'])) $pageview = 'watchedtopics';

		# Approving a post and displaying it
		if(isset($_GET['mod']))
		{
			sf_approve_post(true, 0, $sfvars['topicid']);
		}

		# removing a post from admins queue
		if(isset($_GET['mark']))
		{
			sf_remove_from_waiting(true, $sfvars['topicid'], 0);
		}

		# Edit post from manage
		if (isset($_POST['editpost'])) sf_save_edited_post();

		# Edit topic from manage
		if (isset($_POST['edittopic'])) sf_save_edited_topic();

		# How about a search
		if (isset($_GET['search']))
		{
			$paramvalue=attribute_escape($_GET['value']);
			if($sfvars['forumslug'] == 'all')
			{
				$paramtype = 'SA';
			} else {
				$paramtype = 'S';
			}
		}

		if (isset($_POST['icontoggle'])) sf_icon_toggle();

		# Manage topic admin icons
		if (isset($_POST['locktopic'])) sf_lock_topic_toggle($_POST['locktopic']);
		if (isset($_POST['pintopic'])) sf_pin_topic_toggle($_POST['pintopic']);
		if (isset($_POST['sorttopic'])) sf_sort_topic_toggle($_POST['sorttopic']);
		if (isset($_POST['killtopic'])) sf_delete_topic($_POST['killtopic']);

		if (isset($_POST['linkbreak'])) sf_break_post_link($_POST['linkbreak'], $_POST['blogpost']);

		if (isset($_POST['maketopicmove'])) sf_move_topic();
		if (isset($_POST['makepostmove'])) sf_move_post();
		if (isset($_POST['makestatuschange'])) sf_change_topic_status();

		# Manage post admin icons
		if (isset($_POST['approvepost'])) sf_approve_post(false, $_POST['approvepost'], $sfvars['topicid']);
		if (isset($_POST['pinpost'])) sf_pin_post_toggle($_POST['pinpost']);
		if (isset($_POST['killpost'])) sf_delete_post($_POST['killpost'], $_POST['killposttopic'], $_POST['killpostforum']);

		# Maybe a call to rebuild indices
		if(isset($_POST['rebuildforum']) || isset($_POST['rebuildtopic']))
		{
			sf_build_forum_index($_POST['forumid'], false);
			sf_build_post_index($_POST['topicid'], $_POST['topicslug'], true);
		}

		# Maybe a subscription call?
		if (isset($_GET['subscribe']))
		{
			sf_save_subscription(sf_syscheckint($_GET['topic']), $current_user->ID, true);
			return sf_subscription_form();
		}

		# Maybe a watch call?
		if (isset($_GET['watch']))
		{
			sf_save_watch(sf_syscheckint($_GET['topic']), $current_user->ID, true);
		}

		# Maybe an end watch call?
		if (isset($_GET['endwatch']))
		{
			sf_end_watch(sf_syscheckint($_GET['topic']), $current_user->ID, true);
		}

		# Is it a call to remove unread post list?
		if (isset($_POST['doqueue'])) sf_remove_waiting_queue();

		# Add someone to Buddy List?
		if (isset($_POST['newbuddy']))
		{
			sf_add_buddy($_POST['newbuddy']);
		}

		# Remove someone from Buddy List?
		if (isset($_POST['oldbuddy']))
		{
			sf_remove_buddy($_POST['oldbuddy']);
		}

		# Is it a call to report a post to admin?
		if (isset($_POST['rpaction'])) return sf_report_post_form();
		# Or mail a report in?
		if (isset($_POST['sendrp'])) sf_report_post_send();

		# Now display forum page
		if(function_exists('sf_hook_pre_content'))
		{
			$content = sf_display_banner() . sf_hook_pre_content();
		} else {
			$content = sf_display_banner() . $content;
		}
		if(function_exists('sf_hook_post_content'))
		{
			$content .= sf_hook_post_content();
		}
		$content .= sf_js_check();

		$content .= sf_render_page($pageview, $paramtype, $paramvalue);
	}
	return $content;
}

if(!function_exists('post_password_required')):
function post_password_required( $post = null ) {
	$post = get_post($post);

	if ( empty($post->post_password) )
		return false;

	if ( !isset($_COOKIE['wp-postpass_' . COOKIEHASH]) )
		return true;

	if ( $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password )
		return true;

	return false;
}
endif;

?>