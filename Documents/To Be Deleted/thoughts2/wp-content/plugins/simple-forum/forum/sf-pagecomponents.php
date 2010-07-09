<?php
/*
Simple:Press Forum
Forum General Page Rendering Routines
$LastChangedDate: 2009-04-28 00:35:09 +0100 (Tue, 28 Apr 2009) $
$Rev: 1813 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# = SUCCESS/FAILURE MESSAGING==================
if(!function_exists('sf_render_queued_message')):
function sf_render_queued_message()
{
	$out = '';
	$message = get_sfnotice('sfmessage');
	if(!empty($message))
	{
		$out = sf_message($message);
		delete_sfnotice('sfmessage', '');
	}
	return $out;
}
endif;

# = FORUM LOCKDOWN STRIP ======================
if(!function_exists('sf_render_lockdown')):
function sf_render_lockdown()
{
	$out = '<div class="sfmessagestrip">';
	$out.= '<img src="'.SFRESOURCES.'locked.png" alt="" />'."\n";
	$out.= __("This forum is currently locked - access is read only", "sforum").'</div>'."\n";
	return $out;
}
endif;

# = ADMIN STRIP ===============================
if(!function_exists('sf_render_admin_strip')):
function sf_render_admin_strip($source, $pageview, $newposts)
{
	global $current_user, $sfvars, $sfglobals;

	$out = '';
	if($current_user->adminstatus || $current_user->moderator)
	{
		$fixed = $sfglobals['admin']['sfbarfix'];

		if($fixed)
		{
			$out.= '<div id="sfadminstripfixed">';
		} else {
			$out.= '<div id="sfadminstrip">';
		}
		if($source == 'forum')
		{
			if($pageview != 'group')
			{
				$state='on';
				if($sfvars['forumslug'] != 'all')
				{
					if(($pageview == 'forum' && $current_user->sfforumicons) || ($pageview == 'topic' && $current_user->sftopicicons))
					{
						if($sfglobals['admin']['sftools']) $state='off';
						$out.= '<form class="sfhiddenform sfalignright" action="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], 0).'" method="post" name="toggleicons">'."\n";
						$out.= '<input class="sfhiddeninput" type="hidden" name="icontoggle" value="1" />'."\n";
						$out.= '<a class="sficon sfquickadmin" href="javascript:document.toggleicons.submit();"><img src="'.SFRESOURCES.$state.'.png" alt="" title="'.__("toggle admin icons", "sforum").'" /></a>'."\n";
						$out.= '</form>'."\n";
						$out.= '<img class="sficon sfalignright sfquickadmin" src="'.SFRESOURCES.'separator.png" alt="" />';
					}
				}
			}
		}

		if($current_user->forumadmin)
		{
			$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-adminpopuphelp.php"><img src="'.SFRESOURCES.'sf-adminhelp.png" alt="" title="'.__("Online Help", "sforum").'" /></a>'."\n";

			$out.= '<img class="sficon sfalignright sfquickadmin" src="'.SFRESOURCES.'separator.png" alt="" />';

			$sep = 0; # determine if separator needed for this block of icons
			if (sf_current_user_can('SPF Manage Database'))
			{
				$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-admindatabase.php"><img src="'.SFRESOURCES.'sf-admindatabase.png" alt="" title="'.__("administer database", "sforum").'" /></a>'."\n";
				$sep = 1;
			}

			if ($sep) $out.= '<img class="sficon sfalignright sfquickadmin" src="'.SFRESOURCES.'separator.png" alt="" />';

			$sep = 0; # determine if separator needed for this block of icons
			if (sf_current_user_can('SPF Manage Users'))
			{
				$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-adminusers.php"><img src="'.SFRESOURCES.'sf-adminusers.png" alt="" title="'.__("administer users", "sforum").'" /></a>'."\n";
				$sep = 1;
			}
			if (sf_current_user_can('SPF Manage Permissions'))
			{
				$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-adminpermissions.php"><img src="'.SFRESOURCES.'sf-adminpermissions.png" alt="" title="'.__("administer permission sets", "sforum").'" /></a>'."\n";
				$sep = 1;
			}
			if (sf_current_user_can('SPF Manage User Groups'))
			{
				$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-adminusergroups.php"><img src="'.SFRESOURCES.'sf-adminusergroups.png" alt="" title="'.__("administer user groups", "sforum").'" /></a>'."\n";
				$sep = 1;
			}

			if ($sep) $out.= '<img class="sficon sfalignright sfquickadmin" src="'.SFRESOURCES.'separator.png" alt="" />';

			$sep = 0; # determine if separator needed for this block of icons
			if (sf_current_user_can('SPF Manage Components'))
			{
				$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-admincomponents.php"><img src="'.SFRESOURCES.'sf-admincomponents.png" alt="" title="'.__("administer components", "sforum").'" /></a>'."\n";
				$sep = 1;
			}
			if (sf_current_user_can('SPF Manage Options'))
			{
				$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-adminoptions.php"><img src="'.SFRESOURCES.'sf-adminoptions.png" alt="" title="'.__("administer options", "sforum").'" /></a>'."\n";
				$sep = 1;
			}

			if ($sep) $out.= '<img class="sficon sfalignright sfquickadmin" src="'.SFRESOURCES.'separator.png" alt="" />';

			if (sf_current_user_can('SPF Manage Forums'))
			{
				$out.= '<a class="sficon sfalignright sfquickadmin" href="'.SFHOME.'wp-admin/admin.php?page=simple-forum/admin/sf-adminforums.php"><img src="'.SFRESOURCES.'sf-adminforums.png" alt="" title="'.__("administer forums", "sforum").'" /></a>'."\n";
			}
		}

		if(($source == 'forum' || $source == 'inbox') && $sfglobals['admin']['sfqueue'])
		{
			$out.= '<div id="sfpostnumbers">';
			$out.= sf_get_waiting_url($newposts, $pageview, $sfglobals['admin']['sfshownewadmin']);
			$out.= '</div>';
		}

		# need to close this div differently depending on fixed bar or not - this is for fixed = false
		if(!$fixed)
		{
			$out.= '</div>';
		}
	}

	if($fixed)
	{
		$out.= '<div id="sfadminpostlistfixed"></div>';
		# This closes div if bar is fixed
		$out.= '</div>';
	} else {
		$out.= '<div id="sfadminpostlist"></div>';
	}

	return $out;
}
endif;

# = LOGIN STRIP ===============================
if(!function_exists('sf_render_login_strip')):
function sf_render_login_strip($source, $pageview, $button, $url, $pmview)
{
	global $sfvars, $current_user, $sfglobals;

	$sflogin = array();
	$sflogin = get_option('sflogin');
	$out = '';

	$out.= '<div class="sfloginstrip">'."\n";
$out.= '<div class="inline_edit" id="sfthisuser">'.$current_user->ID.'</div>';

	$out.= '<table cellpadding="1" cellspacing="0"><tr>'."\n";
	$out.= '<td class="sfusercell">'."\n";

	# User Name
	if($current_user->guest)
	{
		# need to check posting permissions in all forums to decide on guest posting message for group pageview
		if ($pageview == 'group')
		{
			if ($GLOBALS['permissions'])
			{
				$checked = array();  # set up array to only check each forum id once to save time
				foreach ($GLOBALS['permissions'] as $perm)
				{
					if (!isset($checked[$perm->forum_id]))
					{
						$permissions = sf_get_permissions(array('Can start new topics', 'Can reply to topics'), $perm->forum_id);
						$current_user->sfaddnew |= $permissions['Can start new topics'];
						$current_user->sfreply |= $permissions['Can reply to topics'];
						$checked[$perm->forum_id] = 1;   # mark this forum id as checked
					}
				}
			}
		}

		# Not logged in - might be a guest - so, do we allow guest posters?
		if(!$current_user->sfaddnew && !$current_user->sfreply)
		{
			$out.= '<strong>'.__("You must be logged in to post", "sforum").'</strong>'."\n";
		} else {
			# So - Guests are allowed but could this be a registered user not yet logged in?
			if($current_user->offmember)
			{
				$out.= sprintf(__('Welcome back <strong>%s</strong>', "sforum"), $current_user->offmember);
				$textbelow = __("Please login if you intend posting", "sforum");
			} else {
				# So a genuine Guest - have they been here before?
				$out.= __("Current User: <strong>Guest</strong>", "sforum")."\n";
				if(!empty($current_user->guestname))
				{
					$out.= ': <strong>'.stripslashes($current_user->guestname).'</strong>'."\n";
					if((!empty($current_user->lastvisit)) && ($current_user->lastvisit > 0))
					{
						$textbelow = __("Last Post", "sforum").': '.date_i18n(SFDATES, $current_user->lastvisit)."\n";
					}
				} else {
					if($sflogin['sfshowreg'])
					{
						$textbelow = '<strong>'.__("Please consider registering", "sforum").'</strong>'."\n";
					}
				}
			}
		}
	} else {
		$out.= sprintf(__("Logged in as <strong> %s </strong>", "sforum"), sf_filter_user($current_user->ID, stripslashes($current_user->display_name)))."\n";
		if(!empty($current_user->lastvisit)) $textbelow = __("Last Visit", "sforum").': '.date_i18n(SFDATES, $current_user->lastvisit)."\n";
	}
	$out.= '</td>'."\n";

	# Login/Register icons
	$out.= '<td class="sflogincell">'."\n";

	$out.= sf_render_custom_icons();

	if($current_user->guest)
	{
		if($sflogin['sfshowlogin'])
		{
			if($sflogin['sfinlogin'])
			{
				$out.= '<a class="sficon" onclick="sfjtoggleLayer(\'sfloginform\');"><img src="'.SFRESOURCES.'login.png" alt="" title="'.__("Login", "sforum").'" />'.sf_render_icons("Login").'</a>'."\n";
			} else {
				$out.= '<a class="sficon" href="'.SFLOGIN.'");"><img src="'.SFRESOURCES.'login.png" alt="" title="'.__("Login", "sforum").'" />'.sf_render_icons("Login").'</a>'."\n";
			}
			if ((TRUE == get_option('users_can_register')) && ($sfglobals['lockdown'] == false))
			{
				if($sflogin['sfshowreg'])
				{
					if($sflogin['sfregtext'])
					{
						$out.= '<a class="sficon" href="'.SFPOLICY.'"><img src="'.SFRESOURCES.'register.png" alt="" title="'.__("Register", "sforum").'" />'.sf_render_icons("Register").'</a>'."\n";
					} else {
						$out.= '<a class="sficon" href="'.SFREGISTER.'"><img src="'.SFRESOURCES.'register.png" alt="" title="'.__("Register", "sforum").'" />'.sf_render_icons("Register").'</a>'."\n";
					}
				}
			}
		}
	} else {
		if($sflogin['sfshowlogin'])
		{
			$out.= '<a class="sficon" href="'.wp_nonce_url(SFLOGOUT, 'log-out').'"><img src="'.SFRESOURCES.'logout.png" alt="" title="'.__("Logout", "sforum").'" />'.sf_render_icons("Logout").'</a>'."\n";
  		}
  		if ($sfglobals['lockdown'] == false && ($current_user->forumadmin || get_option('sfshowmemberlist')))
  		{
			$out.= '<a class="sficon" href="'.SFMEMBERLIST.'"><img src="'.SFRESOURCES.'members-display.png" alt="" title="'.__("Membership List", "sforum").'" />'.sf_render_icons("Members").'</a>'."\n";
		}
  		if ($sfglobals['lockdown'] == false)
  		{
			$out.= '<a class="sficon" href="'.SFPROFILE.'"><img src="'.SFRESOURCES.'profile.png" alt="" title="'.__("Profile", "sforum").'" />'.sf_render_icons("Profile").'</a>'."\n";
		}
	}
	$out.= '</td></tr><tr>';
	$out.= '<td class="sfusercell">'.$textbelow.'</td>';
	$out.= '<td class="sflogincell">'."\n";

	$out.='<div id="sfinboxcount">';
	$out.= sf_render_watch_count();
	$out.= sf_render_inbox_count();
	$out.= '</div>';

	if($button == 'forum')
	{
		$out.= '<img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" /><a class="sficon sfalignright" href="'.SFURL.'"><img src="'.SFRESOURCES.'goforum.png" alt="" title="'.__("Return to forum", "sforum").'" />'.sf_render_icons("Return to forum").'</a>'."\n";
	}

	$out.= '</td>'."\n";
	$out.= '</tr></table>'."\n";
	$out.= '</div>'."\n";

	if(function_exists('sf_hook_post_loginstrip'))
	{
		$out.= sf_hook_post_loginstrip();
	}
	return $out;
}
endif;

# = LOGIN FORM ==+=============================
if(!function_exists('sf_render_login_form')):
function sf_render_login_form()
{
	global $current_user;

	$sflogin=array();
	$sflogin=get_option('sflogin');

	$out = '';
	if($current_user->guest && $sflogin['sfshowlogin'] && $sflogin['sfinlogin'])
	{
		$out.= sf_inline_login_form();
	}
	return $out;
}
endif;

# = WATCH COUNT ==============================
if(!function_exists('sf_render_watch_count')):
function sf_render_watch_count()
{
	global $current_user, $sfglobals;

	$out='';

	# Watched Count and Button
	if(($sfglobals['lockdown'] == false && $current_user->sfwatch))
	{
		$wcount = 0;
		$unreadclass='sfrednumberzero';

		$list = sf_get_member_item($current_user->ID, 'watches');
		if(!empty($list))
		{
			$topics=explode('@', $list);
			foreach($topics as $topicid)
			{
				if(sf_is_in_users_newposts($topicid))
				{
					$wcount++;
					$unreadclass='sfrednumber';
				}
			}
		}
		$out.= '<span><a class="sficon" href="'.sf_build_qurl('watchedtopics=all').'"><img class="sfalignright" src="'. SFRESOURCES .'watching.png" alt="" title="'.__("Review Watched Topics", "sforum").'" /><span id="sfunreadwatch" class="'.$unreadclass.' sfalignright" title="'.__("New Posts in Watched Topics", "sforum").'">'.$wcount.'</span></a></span>';
	}
	return $out;
}
endif;

# = WATCH COUNT ==============================
if(!function_exists('sf_render_inbox_count')):
function sf_render_inbox_count()
{
	global $current_user, $sfglobals;

	$out = '';

	#Inbox Count and Button
	if(($current_user->sfusepm) && ($sfglobals['lockdown'] == false))
	{

		$new = sf_get_pm_inbox_new_count($current_user->ID);
		if(!$new) $new=0;

		# Do we show inbox icon?
		$unreadclass='sfrednumber';
		if($new == 0) $unreadclass='sfrednumberzero';
		$spacerimg = '<img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" />';

		$url=sf_build_qurl("pmaction=viewinpm&amp;pms={$current_user->ID}");
		$out.= '<span><a class="sficon" href="'.$url.'">'.$spacerimg.'<img class="sfalignright" src="'. SFRESOURCES .'goinbox.png" alt="" title="'.__("Go To Inbox", "sforum").'" /><span id="sfunreadpm" class="'.$unreadclass.' sfalignright" title="'.__("New PMs", "sforum").'">'.$new.'</span></a></span>';
	}
	return $out;
}
endif;

# = CUSTOM ICONS ==============================
if(!function_exists('sf_render_custom_icons')):
function sf_render_custom_icons()
{
	global $sfglobals;

	$out = '';

	for($x=0; $x<3; $x++)
	{
		if(!empty($sfglobals['custom'][$x]['cuslink']))
		{
			$out.= '<a class="sficon" href="'.$sfglobals["custom"][$x]["cuslink"].'"><img src="'.SFRESOURCES.'/custom/'.$sfglobals["custom"][$x]["cusicon"].'" alt="" />'.$sfglobals["custom"][$x]["custext"].'</a>'."\n";
		}
	}

	return $out;
}
endif;

# = SEARCH BAR ================================
if(!function_exists('sf_render_searchbar')):
function sf_render_searchbar($pageview, $paramtype, $paramvalue)
{
	global $sfvars;

	$out ='<div class="sfmessagestrip">'."\n";
	$out.='<table><tr>'."\n";
	$out.= '<td width="105">';
	if($pageview != 'inbox')
	{
		$out.= '<a class="sficon" onclick="sfjtoggleLayer(\'sfsearchform\');"><img class="sficon" src="'.SFRESOURCES.'search.png" alt="" title="'.__("Search", "sforum").'" />'.sf_render_icons("Search").'&nbsp;</a>'."\n";
		$out.= '</td>'."\n";

		if($pageview == 'topic')
		{
			# If search mode - display link to return to search results
			if(isset($_GET['search']))
			{
				$forumid = $sfvars['forumslug'];
				if(isset($_GET['ret'])) $forumid='all';

				$paramvalue=urldecode($paramvalue);
				$out.= '<td>'.sf_get_forum_search_url($forumid, sf_syscheckint($_GET['search']), urlencode($paramvalue)).'<img class="sficon" src="'.SFRESOURCES.'results.png" alt="" title="'.__("Return to Search Results", "sforum").'" />'.sf_render_icons("Return to Search Results").'</a>'."\n";
				$out.= '</td>'."\n";
			}
		}
	} else {
		$out.= '<a class="sficon" href="#forumbottom"><img src="'.SFRESOURCES.'bottom.png" alt="" title="'.__("go to bottom", "sforum").'" /></a>'."\n";
		$out.= '</td>'."\n";
	}
	$out.= '<td>'."\n";

	# QuickLinks
	$ql = get_option('sfquicklinks');
	if($ql['sfqlshow'])
	{
		$out.= sf_render_forum_quicklinks();
		if ($ql['sfqlcount'] > 0)
		{
			$out.= '<div id="sfqlposts">';
			$out.= sf_render_newpost_quicklinks($ql['sfqlcount']);
			$out.= '</div>';
		}
	}
	$out.= '</td>'."\n";

	$out.='</tr></table></div>'."\n";

	# get status set if forum
	$statusset = '';
	if($pageview == 'forum')
	{
		$statusset = sf_get_topic_status_set($sfvars['forumid']);
	}
	# Dislay search bar
	$out.= sf_searchbox($pageview, $statusset);
	return $out;
}
endif;

# = BREADCRUMB STRIP ==========================
if(!function_exists('sf_render_breadcrumbs')):
function sf_render_breadcrumbs($forumslug, $topicslug, $page)
{
	global $sfvars;

	$out = '';
	if (get_option('sfshowbreadcrumbs'))
	{
		$arr = '<img src="'.SFRESOURCES.'arrowr.png" alt="" />'."\n";
		$out.= '<div class="sfmessagestrip sfbreadcrumbs">'."\n";
		$out.= '<table><tr><td valign="middle"><p>'."\n";

		$out.= '<a class="sficon" href="#forumbottom"><img src="'.SFRESOURCES.'bottom.png" alt="" title="'.__("go to bottom", "sforum").'" /></a>'."\n";
		if (get_option('sfshowhome'))
		{
			$out.= '<a class="sficon sfpath" href="'.get_option('sfhome').'">'.__("Home", "sforum").'</a>'."\n";
		}
		$out.= '<a class="sficon sfpath" href="'.trailingslashit(SFURL).'">'.$arr.__("Forums", "sforum").'</a>'."\n";
		if(!empty($sfvars['forumslug']) && $sfvars['forumslug'] != 'all')
		{
			# if showing a topic then check the return page of forum in sfsettings...
			$returnpage = 1;
			if(!empty($sfvars['topicslug'])) $returnpage = sf_pop_topic_page($sfvars['forumid']);
			$forumname = $sfvars['forumname'];
			$out.= '<a class="sficon sfpath" href="'.sf_build_url($sfvars['forumslug'], '', $returnpage, 0).'">'.$arr.stripslashes($forumname).'</a>'."\n";
		}
		if(!empty($sfvars['topicslug']))
		{
			$topicname = $sfvars['topicname'];
			$out.= '<a class="sficon sfpath" href="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], 1, 0).'">'.$arr.stripslashes($topicname).'</a>'."\n";
		}

		$out.= '</p></td></tr></table>'."\n";
		$out.= '</div>'."\n";
		if(function_exists('sf_hook_post_breadcrumbs'))
		{
			$out.= sf_hook_post_breadcrumbs();
		}
	}
	return $out;
}
endif;



# = PAGED TOPIC NAVIGATION SUPPORT ============
if(!function_exists('sf_pn_next')):
function sf_pn_next($cpage, $search, $totalpages, $baseurl, $pnshow, $watch=false, $list=false)
{
	if($pnshow == 0) $pnshow=4;
	$start = ($cpage - $pnshow);
	if($start < 1) $start = 1;
	$end = ($cpage - 1);
	$out='';

	if($start > 1)
	{
		$out.= sf_pn_url($cpage, 1, $search, $baseurl, 'None', $watch, $list);
		$out.= sf_pn_url($cpage, $cpage-1, $search, $baseurl, 'Previous', $watch, $list);
	}

	if($end > 0)
	{
		for($i = $start; $i <= $end; $i++)
		{
			$out.= sf_pn_url($cpage, $i, $search, $baseurl, 'None', $watch, $list);
		}
	} else {
		$end = 0;
	}
	return $out;
}
endif;

# = PAGED TOPIC NAVIGATION SUPPORT ============
if(!function_exists('sf_pn_previous')):
function sf_pn_previous($cpage, $search, $totalpages, $baseurl, $pnshow, $watch=false, $list=false)
{
	if($pnshow == 0) $pnshow=4;
	$start = ($cpage + 1);
	$end = ($cpage + $pnshow);
	if($end > $totalpages) $end = $totalpages;
	$out='';

	if($start <= $totalpages)
	{
		for($i = $start; $i <= $end; $i++)
		{
			$out.= sf_pn_url($cpage, $i, $search, $baseurl, 'None', $watch, $list);
		}
		if($end < $totalpages)
		{
			$out.= sf_pn_url($cpage, $cpage+1, $search, $baseurl, 'Next', $watch, $list);
			$out.= sf_pn_url($cpage, $totalpages, $search, $baseurl, 'None', $watch, $list);
		}
	} else {
		$start = 0;
	}
	return $out;
}
endif;

# = PAGED TOPIC NAVIGATION SUPPORT ============
if(!function_exists('sf_pn_url')):
function sf_pn_url($cpage, $thispage, $search, $baseurl, $arrow='None', $watch=false, $list=false)
{
	$out='';

	if ($search)
	{
		$out.= $baseurl . '&amp;search='.$thispage;
	} else if ($watch || $list)
	{
		$out.= $baseurl . '&amp;page='.$thispage;
	} else {
		if ($thispage > 1)
		{
			$out.= $baseurl . '/page-'.$thispage;
		} else {
			$out.= $baseurl;
		}
	}

	Switch ($arrow)
	{
		case 'None':
			$out.= '">'.$thispage.'</a>';
			break;
		case 'Previous':
			$out.= '" class="sfpointer"><img src="'.SFRESOURCES.'arrowl.png" alt="" /></a>'."\n";
			break;
		case 'Next':
			$out.= '" class="sfpointer"><img src="'.SFRESOURCES.'arrowr.png" alt="" /></a>'."\n";
			break;
	}
	return $out;
}
endif;

# = VIEW HEADER TABLE =========================
if(!function_exists('sf_render_main_header_table')):
function sf_render_main_header_table($view, $itemid, $title, $desc, $paramvalue, $icon, $forumlock=false, $search=false, $showadd=false, $topiclock=false, $blogpostid=0, $pmview='inbox', $cansendpm=true, $forums=0, $statusset=0, $statusflag=0, $messagecount=0)
{
	global $sfvars, $current_user, $sfglobals;

	$out = '<div class="sfheading">';
	$out.= '<table><tr>'."\n";
	$out.= '<td class="sficoncell"><img class="" src="'.$icon.'" alt="" /></td>'."\n";
	switch($view)
	{
		case 'group':
			$title=apply_filters('sf_show_topic_title', $title);
			$out.= '<td><p>'.stripslashes($title).'<br /><small>'.stripslashes($desc).'</small></p></td>'."\n";
			# dont display group rss icon if all forum rss feeds are disabled
			$rss_display = 0;
			if ($forums)
			{
				foreach ($forums as $forum)
				{
					if ($forum['forum_rss_private'] == 0)
					{
						$rss_display = 1;
						break;
					}
				}
			}

			if ($rss_display)
			{
				$rssurl= sf_get_group_rss_url($itemid);
				$out.= '<td class="sfadditemcell"><a class="sficon" href="'.$rssurl.'"><img src="'.SFRESOURCES.'feedgroup.png" alt="" title="'.__("Group RSS", "sforum").'" />'.sf_render_icons('Group RSS').'&nbsp;</a></td>'."\n";
			}
			break;

		case 'forum':
			$title=apply_filters('sf_show_topic_title', $title);
			$out.= '<td><p>'.stripslashes($title)."\n";
			if($forumlock)
			{
				$out.='<img src="'.SFRESOURCES.'locked.png" alt="" title="'.__("Forum Locked", "sforum").'" />'."\n";
			}
			if($search)
			{
				$out.=' ('.__("Search Results", "sforum").': '.stripslashes(sf_deconstruct_search_parameter($paramvalue)).')'."\n";
			}
			$out.= '<br /><small>'.stripslashes($desc).'</small></p></td>'."\n";

			$out.= '<td class="sfadditemcell">'."\n";
			if($showadd)
			{
				if($current_user->offmember)
				{
					$out.= '<a class="sficon" href="'.SFLOGIN.'&amp;redirect_to='.urlencode($_SERVER['REQUEST_URI']).'"><img src="'.SFRESOURCES.'login.png" alt="" title="'.__("Login", "sforum").'" />'.sf_render_icons("Login").'</a>'."\n";
				} else {
					$out.= '<a class="sficon" onclick="sfjtoggleLayer(\'sfpostform\');"><img src="'.SFRESOURCES.'addtopic.png" alt="" title="'.__("Add a New Topic", "sforum").'" />'.sf_render_icons("Add a New Topic").'</a>'."\n";
				}
			} else {
				if($forumlock)
				{
					$out.= '<img class="sficon" src="'.SFRESOURCES.'locked.png" alt="" title="'.__("Forum Locked", "sforum").'" />'.sf_render_icons("Forum Locked")."\n";
				}
			}
			$out.= '</td>'."\n";
			break;

		case 'topic':
			$title=apply_filters('sf_show_topic_title', $title);

			$out.= '<td><p>'.stripslashes($title);
			if($blogpostid != 0)
			{
				$out.= '<br />'.sf_forum_show_blog_link($blogpostid);
			}
			$out.= '</p>'."\n";
			$out.= sf_render_topic_statusflag($statusset, $statusflag, 'ts-header', 'ts-upheader', 'left');
			$out.= '</td>';
			$out.= '<td class="sfadditemcell">'."\n";

			# Display reply to post link if alowed - or locked icon if topic locked
			if((!$topiclock) || ($current_user->adminstatus))
			{
				if ($showadd)
				{
					if($current_user->offmember)
					{
						$out.= '<a class="sficon" href="'.SFLOGIN.'&amp;redirect_to='.urlencode($_SERVER['REQUEST_URI']).'"><img src="'.SFRESOURCES.'login.png" alt="" title="'.__("Login", "sforum").'" />'.sf_render_icons("Login").'</a>'."\n";
					} else {
						if($current_user->sfaddnew)
						{
							$url = sf_build_url($sfvars['forumslug'], '', 1, '').sf_add_get().'new=topic';
							$out.= '<a class="sficon" href="'.$url.'"><img src="'.SFRESOURCES.'addtopic.png" alt="" title="'.__("Add a New Topic", "sforum").'" />'.sf_render_icons("Add a New Topic").'</a>'."\n";
						}
						if ($current_user->sfreply)
						{
							$out.= '<a class="sficon" onclick="sfjtoggleLayer(\'sfpostform\');"><img src="'.SFRESOURCES.'addpost.png" alt="" title="'.__("Reply to Post", "sforum").'" />'.sf_render_icons("Reply to Post").'</a>'."\n";
						}
					}
				}
			} else {
				$out.= '<img class="sficon" src="'.SFRESOURCES.'locked.png" alt="" title="'.__("Topic Locked", "sforum").'" />'.sf_render_icons("Topic Locked")."\n";
			}

			$out.= '</td>'."\n";
			break;

		case 'searchall':
			$out.= '<td><p>'.__("Search All Forums", "sforum").' - ('.stripslashes(sf_deconstruct_search_for_display($paramvalue)).')</p></td>'."\n";
			break;

		case 'pm':
			if($pmview == 'inbox')
			{
				$heading = __("Inbox", "sforum");
			} else {
				$heading = __("Sentbox", "sforum");
			}
			$out.= '<td><p>'.$heading.'<br /><small>'.stripslashes(wp_specialchars($current_user->display_name)).'</small></p></td>'."\n";
			$out.= '<td class="sfadditemcell">';
			if($cansendpm)
			{
				$out.= '<a class="sficon sfalignright" onclick="sfjtoggleLayer(\'sfpostform\');"><img src="'.SFRESOURCES.'compose.png" alt="" title="'.__("Compose PM", "sforum").'" />'.sf_render_icons("Compose PM").'</a>';
			}

			if($pmview == 'sentbox')
			{
				$url=sf_build_qurl("pmaction=viewinpm&amp;pms={$current_user->ID}");
				$out.= '<a class="sficon sfalignright" href="'.$url.'"><img src="'.SFRESOURCES.'goinbox.png" alt="" title="'.__("Go To Inbox", "sforum").'" />&nbsp;'.sf_render_icons("Go To Inbox").'</a>'."\n";
			} else {
				$url=sf_build_qurl("pmaction=viewoutpm&amp;pms={$current_user->ID}");
				$out.= '<a class="sficon sfalignright" href="'.$url.'"><img src="'.SFRESOURCES.'gosentbox.png" alt="" title="'.__("Go To Sentbox", "sforum").'" />&nbsp;'.sf_render_icons("Go To Sentbox").'</a>'."\n";
			}

			$out.= '</td>'."\n";
			break;

		case 'list':
			$title=apply_filters('sf_show_topic_title', $title);
			$out.= '<td><p>'.stripslashes($title).'<br /><small>'.stripslashes($desc).'</small></p></td>'."\n";
			$out.= '<td><img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" /><a class="sficon sfalignright" href="'.SFURL.'"><img src="'.SFRESOURCES.'goforum.png" alt="" title="'.__("Return to forum", "sforum").'" />'.sf_render_icons("Return to forum").'</a></td>'."\n";
			break;
	}
	$out.= '</tr></table></div>'."\n";
	return $out;
}
endif;

# = RENDER PAGE LINKS (IN ENTRIES) ============
if(!function_exists('sf_render_inline_pagelinks')):
function sf_render_inline_pagelinks($forumslug, $topicslug, $postcount, $searchpage=0, $paramvalue='', $paramtype='')
{
	$sep = '';
	$postpage=get_option('sfpagedposts');
	if($postpage >= $postcount) return '';

	$out = '&nbsp;&nbsp;('.__("Page:", "sforum").' ';

	$totalpages=($postcount / $postpage);
	if(!is_int($totalpages)) $totalpages=intval($totalpages)+1;

	$sfpnshow = get_option('sfpaging');
	if($sfpnshow == 0) $sfpnshow = 5;

	if($totalpages > $sfpnshow)
	{
		$maxcount=$sfpnshow;
	} else {
		$maxcount=$totalpages;
	}

	if($searchpage != 0)
	{
		if($paramtype == 'SA')
		{
			$ret = '&amp;ret=all';
		} else {
			$ret = '';
		}
	}

	if((isset($_GET['page'])) && (sf_syscheckint($_GET['page']) <> 1))
	{
		$xtp = '&amp;xtp='.sf_syscheckint($_GET['page']);
	} else {
		$xtp = '';
	}

	for($x = 1; $x <= $maxcount; $x++)
	{
		$out.= '<a href="'.sf_build_url($forumslug, $topicslug, $x, 0);
		if($paramvalue != '')
		{
			if(strpos(SFURL, '?') === false)
			{
				$out.= '?value';
			} else {
				$out.= '&amp;value';
			}

			$out.= '='.$paramvalue.'&amp;search='.$searchpage.$ret;
		}
		$out.= '">'.$sep.$x.'</a>'."\n";
		$sep = '| ';
	}

	if($totalpages > $sfpnshow)
	{
		$out.= '&rarr;<a href="'.sf_build_url($forumslug, $topicslug, $totalpages, 0);
		if($paramvalue != '')
		{
			if(strpos(SFURL, '?') === false)
			{
				$out.= '?value';
			} else {
				$out.= '&amp;value';
			}

			$out.= '='.$paramvalue.'&amp;search='.$searchpage.$ret;
		}
		$out.= '">'.$totalpages.'</a>'."\n";
	}

	return $out.')';
}
endif;

if(!function_exists('sf_render_topic_statusflag')):
function sf_render_topic_statusflag($statusset, $statusflag, $boxid, $updateid, $alignment)
{
	$out='';
	if($statusflag != 0)
	{
		# topic status is active
		$topicstatusflag = sf_get_topic_status_flag($statusset, $statusflag);
		$style = '';
	} else {
		# hide it
		$topicstatusflag = '';
		$style = ' style="display:none" ';
	}

	if($alignment == 'left')
	{
		$out.= '<div class="sfalignleft" id="'.$boxid.'"'.$style.'>';
		$out.= '<div class="sfstatusleft sfalignleft"></div>';
		$out.= '<div class="sfstatusmiddle sfalignleft">';
		$out.= '<p id="'.$updateid.'" class="sftopicstatusflag">'.$topicstatusflag.'</p></div>';
		$out.= '<div class="sfstatusright sfalignleft"></div>';
	} else {
		$out.= '<div class="sfalignright" id="'.$boxid.'"'.$style.'>';
		$out.= '<div class="sfstatusright sfalignright"></div>';
		$out.= '<div class="sfstatusmiddle sfalignright">';
		$out.= '<p id="'.$updateid.'" class="sftopicstatusflag">'.$topicstatusflag.'</p></div>';
		$out.= '<div class="sfstatusleft sfalignright"></div>';
	}
	$out.= '</div>';
	return $out;
}
endif;

# = FIRST/LAST POST CELLS =====================
if(!function_exists('sf_render_first_last_post_cell')):
function sf_render_first_last_post_cell($forumslug, $topicslug, $item, $alt)
{
	$poster = sf_filter_user($item['user_id'], stripslashes($item['display_name']));
	if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($item['guest_name']));
	$out = '<td class="sfuserdetails '.$alt.'">';
	$out.= '<p>'.mysql2date(SFDATES, $item['post_date'])."-".mysql2date(SFTIMES,$item['post_date']).'</p>';
	$out.= '<p>'.__("by", "sforum").' '.$poster.sf_get_post_url($forumslug, $topicslug, $item['post_id'], $item['post_index']).'</p></td>'."\n";
	return $out;
}
endif;

# = BOTTOM ICON (RRS) STRIP ===================
if(!function_exists('sf_render_bottom_iconstrip')):
function sf_render_bottom_iconstrip($view, $user='')
{
	global $sfvars, $current_user;

	$out = '<div class="sfmessagestrip">'."\n";
	$out.= '<table cellpadding="0" cellspacing="0"><tr><td width="45%">'."\n";

	# RSS
	$rss_display = 1;
	if ($view != 'inbox' && $view !='sentbox')
	{
		switch($view)
		{
			case 'all':
				# dont display icon for private rss feeds
				$forums = sf_get_forums_all();
				if ($forums)
				{
					$rss_display = 0;
					foreach ($forums as $forum)
					{
						if ($forum->forum_rss_private == 0)
						{
							$rss_display = 1;
							break;
						}
					}
				}

				$url = get_option('sfallRSSurl');
				if(empty($url))
				{
					$url = sf_build_qurl('xfeed=all');
				}
				$icon='feedall.png';
				$text= __('All RSS', 'sforum');
				break;

			case 'forum':
				if($sfvars['error']==false)
				{
					# dont display icon for private rss feeds
					$forum = sf_get_forum_record($sfvars['forumid']);
					if ($forum->forum_rss_private) $rss_display = 0;

					$url = sf_get_forum_rss_url($sfvars['forumid'], $sfvars['forumslug']);
					$icon='feedforum.png';
					$text=__('Forum RSS', 'sforum');
				}
				break;

			case 'topic':
				if($sfvars['error']==false)
				{
					# dont display icon for private rss feeds
					$forum = sf_get_forum_record($sfvars['forumid']);
					if ($forum->forum_rss_private) $rss_display = 0;

					$url = sf_build_qurl('forum='.$sfvars['forumslug'], 'topic='.$sfvars['topicslug'], 'xfeed=topic');
					$icon='feedtopic.png';
					$text=__('Topic RSS', 'sforum');
				}
				break;
		}

		if($sfvars['error']==false && $rss_display)
		{
			$out.= '<a class="sficon sfalignleft" href="'.$url.'"><img src="'.SFRESOURCES.$icon.'" alt="" title="'.$text.'" />'.sf_render_icons($text).'&nbsp;</a>'."\n";
		}
	}

	# Subscribe
	if($sfvars['error']==false && $view != 'inbox')
	{
		if(($current_user->member) && ($current_user->sfsubscriptions) && ($view=='topic'))
		{
			$out.= '<a class="sficon sfalignleft" href="'.sf_build_qurl('forum='.$sfvars['forumid'], 'topic='.$sfvars['topicid'],'subscribe=user').'"><img src="'.SFRESOURCES.'subscribe.png" alt="" title="'.__("Subscribe", "sforum").'" />'.sf_render_icons("Subscribe").'</a>'."\n";
		}
	}

	# Follow
	if($sfvars['error']==false && $view != 'inbox')
	{
		if(($current_user->member) && ($current_user->sfwatch) && ($view=='topic'))
		{
			if (sf_is_watching($current_user->ID, $sfvars['topicid']))
			{
				$out.= '<a class="sficon sfalignleft" href="'.sf_build_qurl('forum='.$sfvars['forumid'], 'topic='.$sfvars['topicid'],'endwatch=user').'"><img src="'.SFRESOURCES.'watchoff.png" alt="" title="'.__("End Topic Watch", "sforum").'" />'.sf_render_icons("End Topic Watch").'</a>'."\n";
			} else {
				$out.= '<a class="sficon sfalignleft" href="'.sf_build_qurl('forum='.$sfvars['forumid'], 'topic='.$sfvars['topicid'],'watch=user').'"><img src="'.SFRESOURCES.'watchon.png" alt="" title="'.__("Watch Topic", "sforum").'" />'.sf_render_icons("Watch Topic").'</a>'."\n";
			}
		}
	}

	$out.='</td><td align="center">';

	# Go to top button
	$out.= '<a href="#forumtop"><img class="sficon" src="'.SFRESOURCES.'top.png" alt="" title="'.__("go to top", "sforum").'" /></a><br />'."\n";

	# Dummy cell here - might be of some use in the future!
	$out.='</td><td class="sflogincell" width="45%">';

	# PM buttons on bottom strip
	if($view == 'inbox' || $view == 'sentbox')
	{
		$out.= '<a class="sficon sfalignright" onclick="sfjtoggleLayer(\'sfpostform\');"><img src="'.SFRESOURCES.'compose.png" alt="" title="'.__("Compose PM", "sforum").'" />'.sf_render_icons("Compose PM").'</a>';
		if($view == 'sentbox')
		{
			$url=sf_build_qurl("pmaction=viewinpm&amp;pms={$current_user->ID}");
			$out.= '<a class="sficon sfalignright" href="'.$url.'"><img src="'.SFRESOURCES.'goinbox.png" alt="" title="'.__("Go To Inbox", "sforum").'" />&nbsp;'.sf_render_icons("Go To Inbox").'</a>'."\n";
		} else {
			$url=sf_build_qurl("pmaction=viewoutpm&amp;pms={$current_user->ID}");
			$out.= '<a class="sficon sfalignright" href="'.$url.'"><img src="'.SFRESOURCES.'gosentbox.png" alt="" title="'.__("Go To Sentbox", "sforum").'" />&nbsp;'.sf_render_icons("Go To Sentbox").'</a>'."\n";
		}
		$out.= '<img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" /><a class="sficon sfalignright" href="'.SFURL.'"><img src="'.SFRESOURCES.'goforum.png" alt="" title="'.__("Return to forum", "sforum").'" />'.sf_render_icons("Return to forum").'</a>'."\n";
	}
	$out.='</td></tr></table>';
	$out.='</div>'."\n";
	return $out;
}
endif;

# = STATISTICS STRIP ==========================
if(!function_exists('sf_render_stats')):
function sf_render_stats()
{
	if(get_option('sfstats'))
	{
		$out = '<br /><table id="sfstatstrip">'."\n";
		$out.= '<tr><th colspan="4"><p>'.sprintf(__("About the %s forum", "sforum"), get_bloginfo('name')).'</p></th></tr>'."\n";
		$out.= '<tr>'."\n";
		$out.= sf_render_online_stats();
		$out.= sf_render_forum_stats();
		$out.= sf_render_member_stats();
		$out.= '</tr><tr><td colspan="4">';
		$out.= sf_render_ownership();
		$out.= '</td></tr></table><br />'."\n";
	}
	return $out;
}
endif;

# = STATISTICS STRIP SUPPORT ROUTINE===========
if(!function_exists('sf_render_online_stats')):
function sf_render_online_stats()
{
	global $wpdb;

	$guests = 0;
	$label = ' '.__("Guests", "sforum");

	$online = $wpdb->get_results("SELECT trackuserid, trackname FROM ".SFTRACK." ORDER BY trackuserid");
	if ($online)
	{
		sf_update_max_online(count($online));
		$out = '<td width="25%">'."\n";
		$out.= '<p><strong>'.__("Most Users Ever Online", "sforum").': </strong></p>'."\n";
		$out.= '<p>'.get_sfsetting('maxonline').'</p><br />'."\n";
		$out.= '<p><strong>'.__("Currently Online", "sforum").': </strong></p>'."\n";
		foreach ($online as $user)
		{
			if ($user->trackuserid == 0)
			{
				$guests++;
			} else {
				$out.= '<p>'.sf_get_user_display_name($user->trackuserid).'</p>'."\n";
			}
		}
		if ($guests > 0)
		{
			if ($guests == 1) $label = ' '.__("Guest", "sforum");
			$out.= '<p>'.$guests.$label.'</p>'."\n";
		}
		$out.= '</td>'."\n";
	}
	return $out;
}
endif;

# = STATISTICS STRIP SUPPORT ROUTINE===========
if(!function_exists('sf_render_forum_stats')):
function sf_render_forum_stats()
{
	$cnt = sf_get_stats_counts();
	$out = '<td width="25%">'."\n";
	$out.= '<p><strong>'.__("Forum Stats: ", "sforum").'</strong></p>'."\n";
	$out.= '<p>'.__("Groups: ", "sforum").$cnt->groups.'</p>'."\n";
	$out.= '<p>'.__("Forums: ", "sforum").$cnt->forums.'</p>'."\n";

	$out.= '<p>'.__("Topics: ", "sforum").$cnt->topics.'</p>'."\n";
	$out.= '<p>'.__("Posts: ", "sforum").$cnt->posts.'</p>'."\n";
	$out.= '</td>'."\n";
	return $out;
}
endif;

# = STATISTICS STRIP SUPPORT ROUTINE===========
if(!function_exists('sf_render_member_stats')):
function sf_render_member_stats()
{
	$members = sf_get_member_post_count();
	$guests = sf_get_guest_count();
	$mods = sf_get_moderator_count();
	$admins = sf_get_admin_count();

	$out = '<td width="25%">'."\n";
	$out.= '<p><strong>'.__("Membership:", "sforum").'</strong></p>'."\n";
	if ($members)
	{
		$membercount = get_sfsetting('membercount');
	} else {
		$membercount = 0;
	}
	if ($membercount == 1)
	{
		$out.= '<p>'.__("There is 1 Member", "sforum").'</p>'."\n";
	} else {
		$out.= '<p>'.sprintf(__("There are %s Members", "sforum"), $membercount).'</p>'."\n";
	}
	if ($guests)
	{
		if ($guests == 1)
		{
			$out.='<p>'.__("There has been 1 Guest", "sforum").'</p>'."\n";
		} else {
			$out.='<p>'.sprintf(__("There have been %s Guests", "sforum"), $guests).'</p>'."\n";
		}
	}

	if (!$admins) $admins = 0;
	if ($admins == 1)
	{
		$out.='<p>'.__("There is 1 Admin", "sforum").'</p>'."\n";
	} else {
		$out.='<p>'.sprintf(__("There are %s Admins", "sforum"), $admins).'</p>'."\n";
	}

	if (!$mods) $mods = 0;
	if ($mods == 1)
	{
		$out.='<p>'.__("There is 1 Moderator", "sforum").'</p>'."\n";
	} else {
		$out.='<p>'.sprintf(__("There are %s Moderators", "sforum"), $mods).'</p>'."\n";
	}

	if ($members)
	{
		$out.= '</td><td width="25%">'."\n";
		$out.= '<p><strong>'.__("Top Posters:", "sforum").'</strong></p>'."\n";
		foreach ($members as $member)
		{
			$out.= '<p>'.stripslashes($member->display_name).' - '.$member->posts.'</p>'."\n";
		}
	}
	$out.= '</td>'."\n";
	return $out;
}
endif;

# = STATISTICS STRIP ADMIN/MODS================
if(!function_exists('sf_render_ownership')):
function sf_render_ownership()
{
	$admins = sf_get_admin_post_count();
	if ($admins)
	{
		$x=1;
		$out = '<p><strong>'.__("Administrators: ", "sforum").'</strong>';
		foreach( $admins as $admin)
		{
			if (!$admin->posts) $admin->posts = 0;
			$label = __("Posts", "sforum");
			if ($admin->posts == 1) $label = __("Post", "sforum");
			$out.= stripslashes($admin->display_name).' ('.$admin->posts.' '.$label.')';
			if ($x != count($admins)) $out.= ',&nbsp;';
			$x++;
		}
		$out.='</p>';
	}

	$moderators = sf_get_mods_post_count();
	if ($moderators)
	{
		$x=1;
		$out.= '<p><strong>'.__("Moderators: ", "sforum").'</strong>';
		foreach ($moderators as $mod)
		{
			if (!$mod->posts) $mod->posts = 0;
			$label = __("Posts", "sforum");
			if ($mod->posts == 1) $label = __("Post", "sforum");
			$out.= stripslashes($mod->display_name).' ('.$mod->posts.' '.$label.')';
			if ($x != count($moderators)) $out.= ',&nbsp;';
			$x++;
		}
		$out.= '</p>';
	}
	return $out;
}
endif;

# = FORUM QUICKLINKS ==========================
if(!function_exists('sf_render_forum_quicklinks')):
function sf_render_forum_quicklinks()
{

	$groups = sf_get_combined_groups_and_forums();
	if($groups[0]['group_id'] == "Access Denied") return;

	if($groups)
	{
		$out = '<select class="sfquicklinks sfcontrol" name="sfquicklinks" onchange="javascript:sfjchangeURL(this)">'."\n";
		$out.= '<option>&nbsp;&nbsp;'.__("Select Forum", "sforum").'</option>'."\n";

		foreach($groups as $group)
		{
			$out.= '<optgroup class="sflist" label="&nbsp;&nbsp;'.sf_create_name_extract($group['group_name']).'">'."\n";
			if($group['forums'])
			{
				foreach($group['forums'] as $forum)
				{
					$out.='<option value="'.sf_build_url($forum['forum_slug'], '', 1, 0).'">&nbsp;&nbsp;&nbsp;&nbsp;'.sf_create_name_extract($forum['forum_name']).'</option>'."\n";
				}
			}
			$out.= '</optgroup>';
		}
		$out.='</select>'."\n";
	}

	return $out;
}
endif;

# = LATEST POST QUICKLINKS ====================
if(!function_exists('sf_render_newpost_quicklinks')):
function sf_render_newpost_quicklinks($show)
{
	global $current_user;

	$sfposts = sf_get_users_new_post_list($show);

	if($sfposts)
	{
		$sfposts = sf_sort_new_post_list($sfposts);
		$sfposts = sf_combined_new_posts_list($sfposts);

		$out = '<select class="sfquicklinks sfcontrol" name="sfquicklinksPost" id="sfquicklinksPost" onchange="javascript:sfjchangeURL(this)">'."\n";
		$out.= '<option>&nbsp;&nbsp;'.__("New/Recently Updated Topics", "sforum").'</option>'."\n";
		$thisforum = 0;
		$group = false;

		foreach($sfposts as $sfpost)
		{
			if($sfpost['forum_id'] != $thisforum)
			{
				if($group)
				{
					$out.= '</optgroup>';
				}
				$name = stripslashes($sfpost['forum_name']);
				if(strlen($name) > 35) $name = substr($name, 0, 35).'...';
				$out.= '<optgroup class="sflist" label="&nbsp;&nbsp;'.sf_create_name_extract($sfpost['forum_name']).'">'."\n";
				$thisforum = $sfpost['forum_id'];
				$group = true;
			}
			$topicicon = '&nbsp;&nbsp;';
			$class = '';

			if($current_user->member && $current_user->ID != $sfpost['user_id'])
			{
				if($current_user->forumadmin || $current_user->moderator)
				{
					if($sfpost['post_status'] == 1)
					{
						$topicicon = '&bull;';
						$class = 'class="sfmod"';
					} elseif(sf_is_in_users_newposts($sfpost['topic_id']))
					{
						$topicicon = '&bull;';
						$class = 'class="sfnew"';
					}
				} else {
					if($current_user->member)
					{
						if(sf_is_in_users_newposts($sfpost['topic_id']))
						{
							$topicicon = '&bull;';
							$class = 'class="sfnew"';
						}
					} else {
						if(($current_user->lastvisit > 0) && ($current_user->lastvisit < $sfpost['udate']))
						{
							$topicicon = '&bull;';
							$class = 'class="sfnew"';
						}
					}
				}
			}

			$name = stripslashes($sfpost['topic_name']);
			if(strlen($name) > 35) $name = substr($name, 0, 35).'...';

			$out.='<option '.$class.' value="'.sf_build_url($sfpost['forum_slug'], $sfpost['topic_slug'], 1, $sfpost['post_id'], $sfpost['post_index']).'">'.$topicicon.'&nbsp;&nbsp;&nbsp;&nbsp;'.sf_create_name_extract($sfpost['topic_name']).'</option>'."\n";
		}
		$out.= '</optgroup>';
		$out.='</select>'."\n";
	}
	return $out;
}
endif;

# = VERSION/ACKNOWLEDGEMENT STRIP =============
if(!function_exists('sf_render_version_strip')):
function sf_render_version_strip()
{

/*--------------------------------------------------------------------------------------------
	This plugin is provided free for anyone to use. We have spent an enormous amount of my time
	and, indeed, money, creating, maintaining, and continuing development of this software.
	The least that you, the user, could do to recognise that, is to leave this copyright strip
	in place which at the very least enables a link back to our site. Thank you.
--------------------------------------------------------------------------------------------*/

	$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahacknowledge.php";
	$out = '<br /><div id="sfversion">&copy; '.SFPLUGHOME;
	$out.= '&nbsp;&nbsp;<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true, reflow: true, width: 650} )"><img class="sficon" src="'.SFRESOURCES.'information.png" alt="" title="'.__("acknowledgements", "sforum").'" /></a>';

	$out.= '</div><br />'."\n";
	return $out;
}
endif;

?>