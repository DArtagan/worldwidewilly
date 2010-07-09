<?php
/*
Simple:Press Forum
Forum Page Rendering
$LastChangedDate: 2009-02-20 12:57:33 +0000 (Fri, 20 Feb 2009) $
$Rev: 1429 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

function sf_render_page($pageview, $paramtype, $paramvalue)
{
	global $sfvars, $current_user, $sfglobals;

	$out = '';

	sf_track_online();

	if(isset($_GET['search']))
	{
		if($sfvars['forumslug'] == 'all')
		{
			$paramtype='SA';
			$pageview = 'searchall';
		} else {
			$paramtype='S';
		}
		if(empty($paramvalue)) $paramvalue=attribute_escape($_GET['value']);
	} else {
		$paramtype = '';
		$paramvalue = '';
	}

	# Top of the forum - Display starts here
	$out.= sf_render_queued_message();

	$out.= "\n\n".'<!-- Start of SPF Container (sforum) -->'."\n\n";
	$out.= '<div id="sforum">'."\n";
	$out.= '<a id="forumtop"></a>'."\n";
	$out.= '<div id="sflogininfo"></div>'."\n";

	switch ($pageview)
	{
		case 'group':
			if(function_exists('sf_hook_group_header'))
			{
				$out.= sf_hook_group_header();
			}
			break;
		case 'forum':
			if(function_exists('sf_hook_forum_header'))
			{
				$out.= sf_hook_forum_header();
			}
			break;
		case 'topic':
			if(function_exists('sf_hook_topic_header'))
			{
				$out.= sf_hook_topic_header();
			}
			break;
	}

	# reduce unread counts if necessary
	if($pageview == 'topic')
	{
		sf_remove_from_waiting(false, $sfvars['topicid']);
		sf_remove_users_newposts($sfvars['topicid']);
		sf_update_opened($sfvars['topicid']);
	}


	$showqueueatbottom = false;
	$showstandardbottom = false;
	$showstandardtop = false;

	if($current_user->adminstatus || $current_user->moderator)
	{
		$usertype = 'admin';
		if(($sfglobals['admin']['sfqueue']) && (($sfglobals['admin']['sfshownewadmin'] && $pageview == 'group') || $sfglobals['admin']['sfadminbar']))
		{
			$newposts=array();
			$newposts = sf_get_admins_queued_posts();

			if($sfglobals['admin']['sfadminbar'])
			{
				$out.= sf_render_admin_strip('forum', $pageview, $newposts);	
			} else {
				if($sfglobals['admin']['sfshownewadmin'] && $pageview == 'group')
				{
					$showqueueatbottom = true;
				} else {
					$showstandardbottom = true;
				}
			}
		} else {
			if($sfglobals['admin']['sfshownewadmin'] && $pageview == 'group')
			{
				$showstandardbottom = true;
			}
		}
	} else {
		$usertype = 'user';
		if($pageview == 'group')
		{
			$sfusersnewposts = array();
			$sfusersnewposts = get_option('sfusersnewposts');
			if($sfusersnewposts['sfshownewuser'])
			{
				if($sfusersnewposts['sfshownewabove'])
				{
					$showstandardtop = true;
				} else {
					$showstandardbottom = true;
				}
			}
		}
	}

	if($sfglobals['lockdown']) $out.= sf_render_lockdown();
	$out.= sf_render_login_strip('forum', $pageview, 'inbox', '', 'inbox');
	$out.= sf_render_login_form();

	# Hive off the display so far in case we need it - i.e., user has NO access to anything
	$header_display = $out;
	
	if(get_option('sfsearchbar'))
	{
		$searchcache = sf_render_searchbar($pageview, $paramtype, $paramvalue);
		$out.= $searchcache;

		$searchcache = substr($searchcache, 0, strpos($searchcache, '<div id="sfsearchform">'));
		$searchcache = str_replace('<div id="sfqlposts">', '<div id="sfqlpostsbottom">', $searchcache);
		$searchcache = str_replace('id="sfquicklinksPost"', 'id="sfquicklinksPostBottom"', $searchcache);
	}
	
	switch ($pageview)
	{
		case 'group':
			include_once('sf-groupcomponents.php');
			include_once('sf-groupview.php');
			
			$out.= sf_render_breadcrumbs('', '', 0);

			if($showstandardtop)
			{
				include_once('sf-forumcomponents.php');
				include_once('sf-newuserview.php');				
				$out.= sf_render_new_post_list_user();
			}
			
			$group_out = sf_render_group();
			if($group_out == "Access Denied")
			{
				$out = $header_display;
				$out.= sf_render_version_strip();
				$out.= '</div>'."\n";
				return $out;
			} else {
				$out.= $group_out;
				$group_out = '';
				$header_display = '';
			}
			
			if($showstandardbottom)
			{
				include_once('sf-forumcomponents.php');
				include_once('sf-newuserview.php');				
				$out.= sf_render_new_post_list_user();
			}
			
			if($showqueueatbottom)
			{
				include_once('sf-newadminview.php');
				$out.= sf_render_new_post_list_admin($newposts, false);
			}				

			$out.= sf_render_bottom_iconstrip('all', $current_user->ID);
			break;
		case 'forum':
			include_once('sf-forumcomponents.php');
			include_once('sf-forumview.php');
			$out.= sf_render_breadcrumbs($sfvars['forumslug'], '', $sfvars['page']);

			$forum_out = sf_render_forum($paramtype, $paramvalue);
			if($forum_out == "Access Denied")
			{
				$out = $header_display;
				$out.= sf_render_version_strip();
				$out.= '</div>'."\n";
				return $out;
			} else {
				$out.= $forum_out;
				$forum_out = '';
				$header_display = '';
			}

			$out.= sf_render_bottom_iconstrip('forum', $current_user->ID);
			break;
		case 'topic':
			include_once('sf-topiccomponents.php');
			include_once('sf-topicview.php');
			$out.= sf_render_breadcrumbs($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page']);

			$topic_out = sf_render_topic($paramvalue);
			if($topic_out == "Access Denied")
			{
				$out = $header_display;
				$out.= sf_render_version_strip();
				$out.= '</div>'."\n";
				return $out;
			} else {
				$out.= $topic_out;
				$top1c_out = '';
				$header_display = '';
			}

			$out.= sf_render_bottom_iconstrip('topic', $current_user->ID);
			break;
		case 'newposts':
			include_once('sf-newadminview.php');
			$out.= sf_render_breadcrumbs(0, 0, 0);
			$out.=  sf_render_new_post_list_admin($newposts, false);
			break;
		case 'watchedtopics':
			include_once('sf-forumcomponents.php');
			include_once('sf-watchview.php');
			$out.= sf_render_breadcrumbs(0, 0, 0);
			$out.= sf_render_watched_topics();
			break;
		case 'searchall':
			include_once('sf-forumcomponents.php');
			include_once('sf-searchview.php');
			$out.= sf_render_breadcrumbs(0, 0, 0);
			$out.= sf_render_search_all($paramtype, $paramvalue);
			break;
		case 'profile':
			include_once('sf-profilecomponents.php');
			$out.= sf_render_breadcrumbs(0, 0, 0);
			$out.= sf_render_profile();
			break;
		case 'list':
			include_once('sf-listcomponents.php');
			$out.= sf_render_breadcrumbs(0, 0, 0);
			$out.= sf_render_list();
			break;
	}
	if (get_option('sfsearchbar')) $out.= $searchcache;
	$out.= sf_render_stats();
	
	if(function_exists('sf_hook_footer_inside'))
	{
		$out.= sf_hook_footer_inside();
	}
	$out.= sf_render_version_strip();
	$out.= '<a id="forumbottom"></a>'."\n";

	$out.= "\n\n".'<!-- End of SPF Container (sforum) -->'."\n\n";
	$out.= '</div>'."\n";
	
	if(function_exists('sf_hook_footer_outside'))
	{
		$out.= sf_hook_footer_outside();
	}
	$sfauto=array();
	$sfauto=get_option('sfauto');
	if($sfauto['sfautoupdate'])
	{
		$out.= sf_start_auto_update($sfauto['sfautotime'] * 1000);
	}

	return $out;
}

?>