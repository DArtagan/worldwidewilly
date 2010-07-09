<?php
/*
Simple:Press Forum
Admin User Groups
$LastChangedDate: 2009-05-30 16:11:57 +0100 (Sat, 30 May 2009) $
$Rev: 1961 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# Check Whether User Can Manage User Groups
if(!sf_current_user_can('SPF Manage User Groups')) {
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminusergroups.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');

include_once ('sf-adminusergroupforms.php');
include_once ('sf-adminsupport.php');
include_once ('sf-admin.php');

if(sfa_get_system_status() != 'ok')
{
	include_once(SFLOADER);
	die();
}

# = ADMIN DISTRUBUTION ========================

# creating a user group
if (isset($_POST['newusergroup']))
    sfa_create_usergroup();

# updating a
if (isset($_POST['updateusergroup']))
    sfa_update_usergroup();

# delete a user group
if (isset($_POST['deleteusergroup']))
    sfa_remove_usergroup();

# update user group with new member
if (isset($_POST['membernew']))
    sfa_membership_add();

# delete member from user group
if (isset($_POST['memberdel']))
    sfa_membership_delete();

sfa_usergroupspage();
sfa_footer();

# = ADMIN PANELS DISTRIBUTION ==================

function sfa_usergroupspage()
{
    sfa_header(__('SPF Manage User Groups', 'sforum'), 'icon-usergroups');
    sfa_render_usergroups_index();

    return;
}

# = ADMIN PANELS ===========

# function to create a new user group
function sfa_create_usergroup()
{
    check_admin_referer('forum-adminform_usergroupnew', 'forum-adminform_usergroupnew');

    # if no usergroup name supplied use a default name
    if (empty($_POST['usergroup_name']))
    {
        $usergroupname = __("New User Group", "sforum");
    } else
    {
        $usergroupname = $_POST['usergroup_name'];
    }

    $usergroupdesc = $_POST['usergroup_desc'];
    if (isset($_POST['usergroup_is_moderator'])) $usergroupismod = 1; else $usergroupismod = 0;

    # create the usergroup
    $success = sfa_create_usergroup_row($usergroupname, $usergroupdesc, $usergroupismod, true);

    if ($success == false)
    {
        sfa_message(__("New User Group Creation Failed!", "sforum"));
    } else
    {
        sfa_message(__("New User Group Created", "sforum"));
    }
    return;
}

# function to update an existing user group
function sfa_update_usergroup()
{
    check_admin_referer('forum-adminform_usergroupedit', 'forum-adminform_usergroupedit');

    $usergroupdata = array();
    $usergroup_id = $_POST['usergroup_id'];
    $usergroupdata['usergroup_name'] = $_POST['usergroup_name'];
    $usergroupdata['usergroup_desc'] = $_POST['usergroup_desc'];
    if (isset($_POST['usergroup_is_moderator'])) $usergroupdata['usergroup_is_moderator'] = 1; else $usergroupdata['usergroup_is_moderator'] = 0;

    # ensure that something has actually changed
    if ($usergroupdata['usergroup_name'] == $_POST['ugroup_name'] && $usergroupdata['usergroup_desc'] ==
        $_POST['ugroup_desc'] && $usergroupdata['usergroup_is_moderator'] == $_POST['ugroup_ismod'])
    {
        sfa_message(__("No Data Changed", "sforum"));
        return;
    }

    # update the user group info
    $success = sfa_update_usergroup_row($usergroup_id, $usergroupdata);
    if ($success == false)
    {
        sfa_message(__("User Group Update Failed!", "sforum"));
    } else
    {
        sfa_message(__("User Group Record Updated", "sforum"));
    }

    return;
}

function sfa_remove_usergroup()
{
    check_admin_referer('forum-adminform_usergroupdelete', 'forum-adminform_usergroupdelete');

    $usergroup_id = $_POST['usergroup_id'];

    # dont allow updates to the default user groups
    $usergroup = sfa_get_usergroups_row($usergroup_id);
    if ($usergroup->usergroup_locked)
    {
        sfa_message(__("Sorry, the default User Groups cannot be deleted.", "sforum"));
        return;
    }

    # remove all memberships for this user group
    sfa_delete_usergroup_memberships($usergroup_id);

	# remove any permission sets using this user group
	$permissions = sfa_get_usergroup_permissions($usergroup_id);
	if ($permissions)
	{
		foreach ($permissions as $permission)
		{
			sfa_remove_permission_data($permission->permission_id);
		}
	}

	# remove any group default permissions using this user group
	sfa_remove_usergroup_defpermissions($usergroup_id);

    # remove the user group
    $success = sfa_remove_usergroup_data($usergroup_id);
    if ($success == false)
    {
        sfa_message(__("User Group Delete Failed!", "sforum"));
    } else
    {
        sfa_message(__("User Group Deleted", "sforum"));
		sfa_rebuild_members_pm();
    }

    return;
}

function sfa_membership_add()
{
    check_admin_referer('forum-adminform_membernew', 'forum-adminform_membernew');

    # add the users to the user group membership
    $usergroup_id = $_POST['usergroup_id'];
    $user_id_list = $_POST['member_id'];

    if (!isset($user_id_list)){
	    sfa_message(__("No Data Changed!", "sforum"));
		return;
	}
	for( $x=0; $x<count($user_id_list); $x++)
	{
		$user_id = $user_id_list[$x];
		$check = sf_check_membership($usergroup_id, $user_id);
		if (empty($check))
		{
			$success = sfa_add_membership($usergroup_id, $user_id);
		}
 	   	if ($success == false)
		{
	    	sfa_message(__("Member Add Failed!", "sforum"));
	    	return;
		}

 	   	sf_update_member_moderator_flag($user_id);
		sfa_rebuild_members_pm($user_id);
	}

    sfa_message(__("Member(s) Added to User Group", "sforum"));

    return;
}

function sfa_membership_delete()
{
    check_admin_referer('forum-adminform_memberdel', 'forum-adminform_memberdel');

    $usergroup_id = $_POST['usergroupid'];
    $new_usergroup_id = $_POST['usergroup_id'];
    $user_id_list = $_POST['member_id'];

	# make sure not moving to same user group
	if (!isset($user_id_list) || $usergroup_id == $new_usergroup_id)
	{
	    sfa_message(__("No Data Changed!", "sforum"));
		return;
	}

    # remove the users from the user group membership
	for( $x=0; $x<count($user_id_list); $x++)
	{
		$user_id = $user_id_list[$x];
		sfa_delete_membership($usergroup_id, $user_id);
	    if ($new_usergroup_id != -1)
	    {
			$check = sf_check_membership($new_usergroup_id, $user_id);
			if (empty($check))
			{
				$success = sfa_add_membership($new_usergroup_id, $user_id);
			}
	    }
	    sf_update_member_moderator_flag($user_id);
		sfa_rebuild_members_pm($user_id);
	}

    if ($new_usergroup_id != -1)
    {
	    sfa_message(__("Member(s) Moved", "sforum"));
	} else {
	    sfa_message(__("Member(s) Deleted From User Group", "sforum"));
	}

    return;
}

?>