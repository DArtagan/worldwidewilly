<?php
/*
Simple:Press Forum
Global defs
$LastChangedDate: 2009-05-17 14:08:39 +0100 (Sun, 17 May 2009) $
$Rev: 1870 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# ------------------------------------------------------
# sf_setup_globals()
#
# some global system level defs used here and there
# NOTE: This array is initisliased in sf-includes
# ------------------------------------------------------
function sf_setup_globals()
{
	global $sfglobals, $current_user;

	# Main admin options
	$sfglobals['admin'] = get_option('sfadminsettings');

	if($current_user->ID != 0 && $current_user->ID != '')
	{
		if (sf_get_member_item($current_user->ID, 'admin') || sf_get_member_item($current_user->ID, 'moderator'))
		{
			$sfmyoptions = sf_get_member_item($current_user->ID, 'admin_options');
			$sfglobals['admin']['sfadminbar'] = $sfmyoptions['sfadminbar'];
			$sfglobals['admin']['sfbarfix'] = $sfmyoptions['sfbarfix'];
			$sfglobals['admin']['sfnotify'] = $sfmyoptions['sfnotify'];
			$sfglobals['admin']['sfshownewadmin'] = $sfmyoptions['sfshownewadmin'];
		}
	}

	$sfglobals['lockdown'] = get_option('sflockdown');
	$sfglobals['custom'] = get_option('sfcustom');

	# Load icon List
	$icons = get_option('sfshowicon');
	$list = explode('@', $icons);

	$icons = array();
	foreach($list as $i)
	{
		$temp=explode(';', $i);
		$icons[$temp[0]] = $temp[1];
	}
	$sfglobals['icons'] = $icons;

	# Load smiley options
	$sfsmileys = array();
	$sfsmileys = get_option('sfsmileys');
	$sfglobals['smileyoptions']=$sfsmileys;

	# Load smileys if custom being used
	if($sfsmileys['sfsmallow'] && $sfsmileys['sfsmtype']==1)
	{
		$meta = sf_get_sfmeta('smileys', 'smileys');
		$smeta = $meta[0]['meta_value'];
		$sfglobals['smileys'] = unserialize($meta[0]['meta_value']);
	}

	# Load tinymce toolbar if in use
	if(isset($sfglobals['editor']))
	{
		if($sfglobals['editor']['sfeditor'] == RICHTEXT)
		{
			$tbmeta = sf_get_sfmeta('tinymce_toolbar', 'user');
			$sfglobals['toolbar'] = unserialize($tbmeta[0]['meta_value']);
		}
	}
	return;
}

# ------------------------------------------------------
# sf_extend_current_user()
#
# extends the WP class $current_user object
# these are the base/global permission settings
#	$pageview:		i.e. 'group', 'forum' etc
#	$forumid:		forumd if relevant
# ------------------------------------------------------
function sf_extend_current_user($forumid='')
{
	global $sfvars, $current_user;

	if($current_user->ID == 0)
	{
		$current_user->ID = '';
	} else {
		$member = sf_get_member_row($current_user->ID);
	}

	# start with some specials
	$current_user->forumadmin = 0;
	$current_user->moderator = 0;
	$current_user->adminstatus = 0;
	$current_user->offmember = 0;
	$current_user->member = 0;
	$current_user->guest = 0;
	$current_user->guestname = '';
	$current_user->guestemail = '';
	$current_user->lastvisit = 0;

	if ($current_user->ID == '' || $current_user->ID == 0)
	{
		$current_user->offmember = sf_check_unlogged_user();
		$current_user->guest = 1;
		sf_get_guest_cookie();
	} else {
		if ($member['admin'] || (function_exists('is_site_admin') && (is_site_admin())))
		{
			$current_user->forumadmin = 1;
			$current_user->adminstatus = 1;
			$current_user->moderator = 1;
		} else {
			if(sf_is_forum_moderator($sfvars['forumid']))
			{
				$current_user->moderator = 1;
			}
		}
		$current_user->display_name = $member['display_name'];
		$current_user->member = 1;
		$current_user->lastvisit = strtotime($member['lastvisit']);
	}
	$current_user->display_name = stripslashes($current_user->display_name);

	# now get all the permissions
	$they = sf_get_global_permissions($forumid);

	# save the permissions to global current_user variable
	$current_user->sfaccess = $they['Can view forum'];
	$current_user->sfaddnew = $they['Can start new topics'];
	$current_user->sfreply = $they['Can reply to topics'];
	$current_user->sflinkuse = $they['Can create linked topics'];
	$current_user->sfbreaklink = $they['Can break linked topics'];
	$current_user->sfpin = $they['Can pin topics'];
	$current_user->sfmovetopics = $they['Can move topics'];
	$current_user->sfmoveposts = $they['Can move posts'];
	$current_user->sflock = $they['Can lock topics'];
	$current_user->sfdelete = $they['Can delete topics'];
	$current_user->sfeditall = $they['Can edit own posts forever'];
	$current_user->sfstopedit = $they['Can edit own posts until reply'];
	$current_user->sfedit = $they['Can edit any posts'];
	$current_user->sfdelete = $they['Can delete any posts'];
	$current_user->sfpin = $they['Can pin posts'];
	$current_user->sfemail = $they['Can view users email addresses'];
	$current_user->sfprofiles = $they['Can view members profiles'];
	$current_user->sfreport = $they['Can report posts'];
	$current_user->sfsort = $they['Can sort most recent posts'];
	$current_user->sfspam = $they['Can bypass spam control'];
	if ($they['Can bypass post moderation']) {
		$current_user->sfmoderated = 0;
	} else {
		$current_user->sfmoderated = 1;
	}
	if ($they['Can bypass post moderation once']) {
		$current_user->sfmodonce = 0;
	} else {
		$current_user->sfmodonce = 1;
	}
	$current_user->sfuploads = $they['Can upload images'];
	$current_user->sfusersig = $they['Can use signatures'];
	$current_user->sfsigimage = $they['Can use images in signatures'];
	$current_user->sfsubscriptions = $they['Can subscribe'];
	$current_user->sfwatch = $they['Can watch topics'];
	$current_user->sftopicstatus = $they['Can change topic status'];
	$current_user->sfrateposts = $they['Can rate posts'];
	$current_user->sfapprove = $they['Can moderate pending posts'];
	$current_user->sfforumicons = false;
	$current_user->sfforumicons = false;
	if($current_user->sfbreaklink || $current_user->sfedit || $current_user->sfdelete || $current_user->sfpin || $current_user->sfmovetopics || $current_user->sflock || $current_user->sfsort)
	{
		$current_user->sfforumicons = true;
	}
	$current_user->sftopicicons = false;
	if($current_user->sfapprove || $current_user->sfemail || $current_user->sfpin || $current_user->sfedit || $current_user->sfdelete)
	{
		$current_user->sftopicicons = true;
	}

	if(get_option('sfprivatemessaging'))
	{
		$current_user->sfusepm = $they['Can use private messaging'];
	} else {
		$current_user->sfusepm = false;
	}

	if(get_option('sfavataruploads'))
	{
		$current_user->sfavatars = $they['Can upload avatars'];
	} else {
		$current_user->sfavatars = false;
	}

	# regardless of the permissions, the following are overriden for guests
	if($current_user->guest)
	{
		$current_user->sflinkuse = false;
		$current_user->sfusersig = false;
		$current_user->sfsigimage = false;
		$current_user->sfavatars = false;
		$current_user->sfusepm = false;
		$current_user->sfsubscriptions = false;
		$current_user->sfwatch = false;
		$current_user->sftopicstatus = false;
		$current_user->sfapprove = false;
		$current_user->sfforumicons = false;
		$current_user->sftopicicons = false;
		$current_user->sfrateposts = false;
	}
}

# ------------------------------------------------------
# sf_get_guest_cookie()
#
# load cookie data if a returning guest
# ------------------------------------------------------
function sf_get_guest_cookie()
{
	global $current_user;

	if(isset($_COOKIE['guestname_'.COOKIEHASH])) $current_user->guestname = $_COOKIE['guestname_'.COOKIEHASH];
	if(isset($_COOKIE['guestemail_'.COOKIEHASH])) $current_user->guestemail = $_COOKIE['guestemail_'.COOKIEHASH];
	if(isset($_COOKIE['sflast_'.COOKIEHASH])) $current_user->lastvisit = $_COOKIE['sflast_'.COOKIEHASH];

	$current_user->display_name = $current_user->guestname;
	return;
}

# ------------------------------------------------------
# sf_build_membership_cache()
#
# load usergroup memberships for current user into cache
# ------------------------------------------------------
function sf_build_membership_cache()
{
	global $current_user, $wpdb;

	if (sf_is_forum_admin($current_user->ID)) return;

	$memberships = array();

	if (($current_user->ID != '') && ($current_user->ID != 0))
	{
		# get the usergroup memberships for the current user
		$memberships = sf_get_user_memberships($current_user->ID);
	}
	if (empty($memberships) || (($current_user->ID == '') || ($current_user->ID == 0)))
	{
		# user is a guest or unassigned member so get the global permissions from the guest usergroup
		$memberships[] = $wpdb->get_row("SELECT usergroup_id, usergroup_name, usergroup_desc FROM ".SFUSERGROUPS." WHERE usergroup_id=".get_option('sfguestsgroup'), ARRAY_A);
	}

	# put in the cache
	$GLOBALS['memberships'] = $memberships;

	return;
}

# ------------------------------------------------------
# sf_build_permissions_cache()
#
# load permissions table into cache
# ------------------------------------------------------
function sf_build_permissions_cache()
{
	global $wpdb;

	$GLOBALS['permissions'] = $wpdb->get_results("SELECT forum_id, usergroup_id, permission_role FROM ".SFPERMISSIONS." ORDER BY permission_id");

	return;
}

# ------------------------------------------------------
# sf_build_roles_cache()
#
# load roles table into cache
# ------------------------------------------------------
function sf_build_roles_cache()
{
	global $wpdb;

	$roles = $wpdb->get_results("SELECT role_id, role_actions FROM ".SFROLES." ORDER BY role_id");
	if($roles)
	{
		foreach($roles as $role)
		{
			$GLOBALS['roles'][$role->role_id]=unserialize($role->role_actions);
		}
	}

	return;
}

# ------------------------------------------------------
# sf_build_ranks_cache()
#
# load forum ranks into cache
# ------------------------------------------------------
function sf_build_ranks_cache()
{
	$GLOBALS['ranks'] = array();

	# get rankings information
	$rankdata = sf_get_sfmeta('forum_rank');
	if ($rankdata)
	{
		# put into arrays to make easy to sort
		foreach ($rankdata as $x => $info)
		{
			$GLOBALS['ranks']['title'][$x] = $info['meta_key'];
			$data = unserialize($info['meta_value']);
			$GLOBALS['ranks']['posts'][$x] = $data['posts'];
		}
		# sort rankings highest to lowest
		array_multisort($GLOBALS['ranks']['posts'], SORT_ASC, $GLOBALS['ranks']['title']);
	}

	return;
}

# ------------------------------------------------------
# sf_initialise_globals()
#
# calls routines necessary to have loaded when using
# any forum code outside of the actual forum page
# ------------------------------------------------------
function sf_initialise_globals($forumid='')
{
	sf_setup_globals();
	sf_build_membership_cache();
	sf_build_permissions_cache();
	sf_build_roles_cache();
	sf_build_ranks_cache();
	sf_extend_current_user($forumid);

	return;
}

?>