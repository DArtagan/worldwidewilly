<?php
/*
Simple:Press Forum
Admin ADmins
$LastChangedDate: 2009-04-18 15:41:42 +0100 (Sat, 18 Apr 2009) $
$Rev: 1729 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# Check Whether User Can Manage Admins
global $current_user;
if (!sf_current_user_can('SPF Manage Admins') && !sf_get_member_item($current_user->ID, 'moderator'))
{
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminadmins.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');
define('SFSUPPORT',   SF_PLUGIN_DIR . '/forum/sf-support.php');

include_once ('sf-adminadminsforms.php');
include_once ('sf-adminsupport.php');
include_once('sf-tabsupport.php');
include_once ('sf-admin.php');
include_once (SFSUPPORT);

global $adminhelpfile;
$adminhelpfile='admin-admins';

# make sure we dont need to perform an upgrade
if ( sfa_get_system_status() != 'ok' )
{
    include_once(SFLOADER);
    die();
}

# = ADMIN DISTRUBUTION ========================

global $records;

if (sf_current_user_can('SPF Manage Admins'))
{
	# update existing admin capabilities
	if (isset($_POST['updatecaps']))
	    sfa_update_admin_caps();

	# add new admins
	if (isset($_POST['addadmins']))
	    sfa_add_new_admins();

	# global admin options
	if (isset($_POST['adminoptions']))
	    sfa_update_options();
}

# individual admin options
if (isset($_POST['myadminoptions']))
    sfa_update_myoptions();

sfa_header(__("SPF Manage Admins", "sforum"), 'icon-admins');

sfa_adminadminspage();

sfa_footer();

# = ADMIN PANELS DISTRIBUTION==================

function sfa_adminadminspage()
{
    sfa_render_admins_index();

    return;
}

function sfa_update_admin_caps()
{
    check_admin_referer('forum-adminform_sfupdatecaps', 'forum-adminform_sfupdatecaps');

    $users = $_POST['uids'];

    if (isset($_POST['manage-opts'])) $manage_opts = $_POST['manage-opts']; else $manage_opts = '';
    if (isset($_POST['manage-forums'])) $manage_forums = $_POST['manage-forums']; else $manage_forums = '';
    if (isset($_POST['manage-ugs'])) $manage_ugs = $_POST['manage-ugs']; else $manage_ugs = '';
    if (isset($_POST['manage-perms'])) $manage_perms = $_POST['manage-perms']; else $manage_perms = '';
    if (isset($_POST['manage-comps'])) $manage_comps = $_POST['manage-comps']; else $manage_comps = '';
    if (isset($_POST['manage-db'])) $manage_db = $_POST['manage-db']; else $manage_db = '';
    if (isset($_POST['manage-users'])) $manage_users = $_POST['manage-users']; else $manage_users = '';
    if (isset($_POST['manage-admins'])) $manage_admins = $_POST['manage-admins']; else $manage_admins = '';

    if (isset($_POST['old-opts'])) $old_opts = $_POST['old-opts']; else $old_opts = '';
    if (isset($_POST['old-forums'])) $old_forums = $_POST['old-forums']; else $old_forums = '';
    if (isset($_POST['old-ugs'])) $old_ugs = $_POST['old-ugs']; else $old_ugs = '';
    if (isset($_POST['old-perms'])) $old_perms = $_POST['old-perms']; else $old_perms = '';
    if (isset($_POST['old-comps'])) $old_comps = $_POST['old-comps']; else $old_comps = '';
    if (isset($_POST['old-db'])) $old_db = $_POST['old-db']; else $old_db = '';
    if (isset($_POST['old-users'])) $old_users = $_POST['old-users']; else $old_users = '';
    if (isset($_POST['old-admins'])) $old_admins = $_POST['old-admins']; else $old_admins = '';

	$data_changed = false;
    for ($index = 0; $index < count($users); $index++)
	{
		if ((isset($manage_opts[$users[$index]])   != (isset($old_opts[$users[$index]]) && $old_opts[$users[$index]])) ||
		    (isset($manage_forums[$users[$index]]) != (isset($old_forums[$users[$index]]) && $old_forums[$users[$index]])) ||
		    (isset($manage_ugs[$users[$index]])    != (isset($old_ugs[$users[$index]]) && $old_ugs[$users[$index]])) ||
		    (isset($manage_perms[$users[$index]])  != (isset($old_perms[$users[$index]]) && $old_perms[$users[$index]])) ||
		    (isset($manage_comps[$users[$index]])  != (isset($old_comps[$users[$index]]) && $old_comps[$users[$index]])) ||
		    (isset($manage_db[$users[$index]])     != (isset($old_db[$users[$index]]) && $old_db[$users[$index]])) ||
		    (isset($manage_users[$users[$index]])  != (isset($old_users[$users[$index]]) && $old_users[$users[$index]])) ||
		    (isset($manage_admins[$users[$index]]) != (isset($old_admins[$users[$index]]) && $old_admins[$users[$index]])))
		{
			# Is user still an admin?
			if (!isset($manage_opts[$users[$index]]) &&
			    !isset($manage_forums[$users[$index]]) &&
			    !isset($manage_ugs[$users[$index]]) &&
			    !isset($manage_perms[$users[$index]]) &&
			    !isset($manage_comps[$users[$index]]) &&
			    !isset($manage_db[$users[$index]]) &&
			    !isset($manage_users[$users[$index]]) &&
			    !isset($manage_admins[$users[$index]]))
			{
				sf_update_member_item($users[$index], 'admin', 0);
			}

			$data_changed = true;
			$user = new WP_User($users[$index]);

			if (isset($manage_opts[$users[$index]]))
				$user->add_cap('SPF Manage Options');
			else
				$user->remove_cap('SPF Manage Options');

			if (isset($manage_forums[$users[$index]]))
				$user->add_cap('SPF Manage Forums');
			else
				$user->remove_cap('SPF Manage Forums');

			if (isset($manage_ugs[$users[$index]]))
				$user->add_cap('SPF Manage User Groups');
			else
				$user->remove_cap('SPF Manage User Groups');

			if (isset($manage_perms[$users[$index]]))
				$user->add_cap('SPF Manage Permissions');
			else
				$user->remove_cap('SPF Manage Permissions');

			if (isset($manage_comps[$users[$index]]))
				$user->add_cap('SPF Manage Components');
			else
				$user->remove_cap('SPF Manage Components');

			if (isset($manage_db[$users[$index]]))
				$user->add_cap('SPF Manage Database');
			else
				$user->remove_cap('SPF Manage Database');

			if (isset($manage_users[$users[$index]]))
				$user->add_cap('SPF Manage Users');
			else
				$user->remove_cap('SPF Manage Users');

			if (isset($manage_admins[$users[$index]]))
				$user->add_cap('SPF Manage Admins');
			else
				$user->remove_cap('SPF Manage Admins');
		}
	}

	if ($data_changed)
	    sfa_message(__("Admin Capabilities Updated!", "sforum"));
 	else
	    sfa_message(__("No Data Changed!", "sforum"));

    return;
}

function sfa_add_new_admins()
{
	global $wpdb;

    check_admin_referer('forum-adminform_sfaddadmins', 'forum-adminform_sfaddadmins');

    if (isset($_POST['newadmins']))
	{
		$newadmins = $_POST['newadmins'];
	} else {
	    sfa_message(__("No Users Selected!", "sforum"));

		return;
    }

    if (isset($_POST['add-opts'])) $opts = $_POST['add-opts']; else $opts ='';
    if (isset($_POST['add-forums'])) $forums = $_POST['add-forums']; else $forums ='';
    if (isset($_POST['add-ugs'])) $ugs = $_POST['add-ugs']; else $ugs ='';
    if (isset($_POST['add-perms'])) $perms = $_POST['add-perms']; else $perms ='';
    if (isset($_POST['add-comps'])) $comps = $_POST['add-comps']; else $comps ='';
    if (isset($_POST['add-db'])) $db = $_POST['add-db']; else $db ='';
    if (isset($_POST['add-users'])) $users = $_POST['add-users']; else $users ='';
    if (isset($_POST['add-admins'])) $admins = $_POST['add-admins']; else $admins ='';

	$added = false;
    for ($index = 0; $index < count($newadmins); $index++)
	{
		$user = new WP_User($newadmins[$index]);

		if ($opts == 'on')
			$user->add_cap('SPF Manage Options');

		if ($forums == 'on')
			$user->add_cap('SPF Manage Forums');

		if ($ugs == 'on')
			$user->add_cap('SPF Manage User Groups');

		if ($perms == 'on')
			$user->add_cap('SPF Manage Permissions');

		if ($comps == 'on')
			$user->add_cap('SPF Manage Components');

		if ($db == 'on')
			$user->add_cap('SPF Manage Database');

		if ($users == 'on')
			$user->add_cap('SPF Manage Users');

		if ($admins == 'on')
			$user->add_cap('SPF Manage Admins');

		if ($opts == 'on' || $forums == 'on' || $ugs == 'on' || $perms == 'on' || $comps == 'on' || $db == 'on' || $users == 'on'|| $admins == 'on')
		{
			$added = true;

			# flag as admin and remove moderator flag if set
			sf_update_member_item($newadmins[$index], 'admin', 1);
			sf_update_member_item($newadmins[$index], 'moderator', 0);

			# remove any usergroup permissions
			sfa_delete_user_memberships($newadmins[$index]);

			#update moderator flag
		    sf_update_member_moderator_flag($newadmins[$index]);
		}
	}

	if ($added)
	    sfa_message(__("New Admins Added!", "sforum"));
 	else
	    sfa_message(__("No Data Changed!", "sforum"));

	return;
}

function sfa_update_options()
{
    check_admin_referer('forum-admin_options', 'forum-admin_options');

	# admin settings group
	$sfadminsettings='';
	if (isset($_POST['sfqueue'])) $sfadminsettings['sfqueue']=true; else $sfadminsettings['sfqueue']=false;
	if (isset($_POST['sfmodasadmin'])) $sfadminsettings['sfmodasadmin']=true; else $sfadminsettings['sfmodasadmin']=false;
	if (isset($_POST['sfshowmodposts'])) $sfadminsettings['sfshowmodposts']=true; else $sfadminsettings['sfshowmodposts']=false;
	if (isset($_POST['sftools'])) $sfadminsettings['sftools']=true; else $sfadminsettings['sftools']=false;
	if (isset($_POST['sfbaronly'])) $sfadminsettings['sfbaronly']=true; else $sfadminsettings['sfbaronly']=false;
	if (isset($_POST['sfdashboardposts'])) { $sfadminsettings['sfdashboardposts'] = true; } else { $sfadminsettings['sfdashboardposts'] = false; }
	if (isset($_POST['sfdashboardstats'])) { $sfadminsettings['sfdashboardstats'] = true; } else { $sfadminsettings['sfdashboardstats'] = false; }
	update_option('sfadminsettings', $sfadminsettings);

	$mess = __('Admin Options Updated', "sforum");
	sfa_message($mess);

	return;
}

function sfa_update_myoptions()
{
	global $current_user;

    check_admin_referer('my-admin_options', 'my-admin_options');

	$sfadminsettings = array();
	$sfadminsettings = get_option('sfadminsettings');

	# admin settings group
	$sfadminoptions='';
	if (isset($sfadminsettings['sfqueue']))
	{
		if(isset($_POST['sfadminbar'])) $sfadminoptions['sfadminbar']=true; else $sfadminoptions['sfadminbar']=false;
		if(isset($_POST['sfbarfix'])) $sfadminoptions['sfbarfix']=true; else $sfadminoptions['sfbarfix']=false;
	}
	if (isset($_POST['sfnotify'])) $sfadminoptions['sfnotify']=true; else $sfadminoptions['sfnotify']=false;
	if (isset($_POST['sfshownewadmin'])) $sfadminoptions['sfshownewadmin']=true; else $sfadminoptions['sfshownewadmin']=false;
	sf_update_member_item($current_user->ID, 'admin_options', $sfadminoptions);

	$mess = __('Your Admin Options Updated', "sforum");
	sfa_message($mess);

	return;
}

?>