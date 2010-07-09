<?php
/*
Simple:Press Forum
Admin Panels - Forum Management
$LastChangedDate: 2009-05-20 03:41:36 +0100 (Wed, 20 May 2009) $
$Rev: 1874 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# Check Whether User Can Manage Forums
if(!sf_current_user_can('SPF Manage Forums')) {
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminforums.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');

include_once ('sf-adminforumforms.php');
include_once ('sf-adminsupport.php');
include_once ('sf-admin.php');

if(sfa_get_system_status() != 'ok')
{
	include_once(SFLOADER);
	die();
}

# = ADMIN DISTRUBUTION ========================

# creating a group
if (isset($_POST['newgroup']))
    sfa_create_group();

# creating a forum
if (isset($_POST['newforum']))
    sfa_create_forum();

# updating a group
if (isset($_POST['updategroup']))
    sfa_update_group();

# updating a forum
if (isset($_POST['updateforum']))
    sfa_update_forum();

# deleting a group
if (isset($_POST['deletegroup']))
    sfa_remove_group();

# deleting a forum
if (isset($_POST['deleteforum']))
    sfa_remove_forum();

# updating a permission set
if (isset($_POST['updatepermission']))
    sfa_update_permission();

# deleting a permission set
if (isset($_POST['deletepermission']))
    sfa_remove_permission();

# add a new group wide permission set
if (isset($_POST['newgrouppermission']))
    sfa_add_group_permission();

# add a new permission set
if (isset($_POST['newpermission']))
    sfa_add_permission();

# add a new global permission set
if (isset($_POST['newglobalpermission']))
    sfa_add_global_permission();

# deleting all permission sets
if (isset($_POST['deleteallpermissions']))
    sfa_remove_all_permissions();

# setting a replacement RSS url
if(isset($_POST['setRSSurl']))
	sfa_replace_rss_url();

# setting a custom icon
if((isset($_POST['setIcon'])) || (isset($_POST['removeIcon'])))
	sfa_replace_icon();

# setting a topic status set
if(isset($_POST['savetopstatset']))
	sfa_save_topic_status_set();

sfa_forumspage();
sfa_footer();

# = ADMIN PANELS DISTRIBUTION==================

function sfa_forumspage()
{
 	sfa_header(__('SPF Manage Forums', 'sforum'), 'icon-forums');
    sfa_render_forum_index();

    return;
}

# = ADMIN PANELS SAVE/UPDATE/DELETE ===========

function sfa_create_group()
{
	global $wpdb;

    check_admin_referer('forum-adminform_groupnew', 'forum-adminform_groupnew');

    $ug_list = $_POST['usergroup_id'];
    $perm_list = $_POST['role'];

    # fail if any user groups arent assigned a permission
	for( $x=0; $x<count($perm_list); $x++)
	{
		if ($perm_list[$x] == -1)
		{
	        sfa_message(__("All User Groups Must Be Asssigned A Default Permission", "sforum"));
    	    return;
		}
	}

    $seq = (sfa_next_group_seq() + 1);
    $groupdata = array();

    if (empty($_POST['group_name']))
    {
        $groupdata['group_name'] = __("New Forum Group", "sforum");
    } else
    {
        $groupdata['group_name'] = $_POST['group_name'];
    }
    if (empty($_POST['group_seq']))
    {
        $groupdata['group_seq'] = $seq;
    } else
    {
    	if (is_numeric($_POST['group_seq']))
    	{
 	       $groupdata['group_seq'] = $_POST['group_seq'];
    	} else {
	        sfa_message(__("New Group Creation Failed - Sequence Must Be An Integer!", "sforum"));
    		return;
    	}
    }

    $groupdata['group_desc'] = $_POST['group_desc'];

    # check if we need to shuffle sequence numbers
    if ($groupdata['group_seq'] < $seq)
    {
        $groups = sf_get_groups_all();
        foreach ($groups as $group)
        {
            if ($group->group_seq >= $groupdata['group_seq'])
            {
                sfa_bump_group_seq($group->group_id, ($group->group_seq + 1));
            }
        }
    }

    # create the group
    $success = sfa_create_group_row($groupdata);
    $group_id = $wpdb->insert_id;

 	# save the default permissions for the group
	for( $x=0; $x<count($ug_list); $x++)
	{
		sfa_add_defpermission_row($group_id, $ug_list[$x], $perm_list[$x]);
	}

    if ($success == false)
    {
        sfa_message(__("New Group Creation Failed!", "sforum"));
    } else
    {
        sfa_message(__("New Forum Group Created", "sforum"));
    }

    return;
}

function sfa_create_forum()
{
	global $wpdb;

    check_admin_referer('forum-adminform_forumnew', 'forum-adminform_forumnew');

    $seq = (sfa_next_forum_seq($_POST['group_id']) + 1);
    $forumdata = array();
    $forumdata['group_id'] = $_POST['group_id'];
    $forumdata['forum_desc'] = $_POST['forum_desc'];

    $forumdata['forum_status'] = 0;
    if (isset($_POST['forum_status']))
        $forumdata['forum_status'] = 1;

    $forumdata['forum_rss_private'] = 0;
    if (isset($_POST['forum_private']))
        $forumdata['forum_rss_private'] = 1;

    if (empty($_POST['forum_name']))
    {
        $forumdata['forum_name'] = __("New Forum", "sforum");
    } else
    {
        $forumdata['forum_name'] = $_POST['forum_name'];
    }
    if (empty($_POST['forum_seq']))
    {
        $forumdata['forum_seq'] = $seq;
    } else
    {
    	if (is_numeric($_POST['forum_seq']))
    	{
        	$forumdata['forum_seq'] = $_POST['forum_seq'];
    	} else {
	        sfa_message(__("New Forum Creation Failed - Sequence Must Be An Integer!", "sforum"));
    		return;
    	}
    }

    # check if we need to shuffle sequence numbers
    if ($forumdata['forum_seq'] < $seq)
    {
        $forums = sfa_get_forums_in_group($forumdata['group_id']);
        foreach ($forums as $forum)
        {
            if ($forum->forum_seq >= $forumdata['forum_seq'])
            {
                sfa_bump_forum_seq($forum->forum_id, ($forum->forum_seq + 1));
            }
        }
    }

    # create the forum
    $success = sfa_create_forum_row($forumdata);
    $forum_id = $wpdb->insert_id;

    # add the user group permission sets
    $usergroup_id_list = $_POST['usergroup_id'];
    $role_list = $_POST['role'];
	$perm_prob = false;
	for( $x=0; $x<count($usergroup_id_list); $x++)
	{
		$usergroup_id = $usergroup_id_list[$x];
		$role = $role_list[$x];
   		if ($role == -1)
	    {
			$defrole = sfa_get_defpermissions_role($forumdata['group_id'], $usergroup_id);
			if ($defrole == '')
			{
		    	$perm_prob = true;
		    } else {
				sfa_add_permission_data($forum_id, $usergroup_id, $defrole);
		    }
    	} else {
			sfa_add_permission_data($forum_id, $usergroup_id, $role);
		}
    }

	# if the forum was created, signal success - doesnt check user group permission set though
    if ($success == false)
    {
        sfa_message(__("New Forum Creation Failed!", "sforum"));
    } else
    {
		if ($perm_prob) {
        	sfa_message(__("New Forum Created - Permission Sets Not Properly Set For All User Groups!", "sforum"));
	    } else {
        	sfa_message(__("New Forum Created!", "sforum"));
		}
    }

    return;
}

function sfa_update_group()
{
    check_admin_referer('forum-adminform_groupedit', 'forum-adminform_groupedit');

    $groupdata = array();
    $group_id = $_POST['group_id'];
    $groupdata['group_name'] = $_POST['group_name'];
    $groupdata['group_seq'] = $_POST['group_seq'];
    $groupdata['group_desc'] = $_POST['group_desc'];

    $ug_list = $_POST['usergroup_id'];
    $perm_list = $_POST['role'];

    # fail if any user groups arent assigned a permission
	for( $x=0; $x<count($perm_list); $x++)
	{
		if ($perm_list[$x] == -1)
		{
	        sfa_message(__("All User Groups Must Be Asssigned A Default Permission", "sforum"));
    	    return;
		}
	}

	# save the default permissions for the group
	for( $x=0; $x<count($ug_list); $x++)
	{
		if (sfa_get_defpermissions_role($group_id, $ug_list[$x]))
		{
			sfa_update_defpermission_row($group_id, $ug_list[$x], $perm_list[$x]);
		} else {
			sfa_add_defpermission_row($group_id, $ug_list[$x], $perm_list[$x]);
		}
	}

    if ($groupdata['group_name'] == $_POST['cgroup_name'] && $groupdata['group_seq'] ==
        $_POST['cgroup_seq'] && $groupdata['group_desc'] == $_POST['cgroup_desc'])
    {
        sfa_message(__("Forum Group Record Updated", "sforum"));
    } else {
	    # has the sequence changed?
	    if ($groupdata['group_seq'] != $_POST['cgroup_seq'])
	    {
	        # need to iterate through the groups to change sequence number
	        $groups = sfa_get_other_groups($group_id);
	        $cnt = count($groups);
	        for ($i = 0; $i < $cnt; $i++)
	        {
	            if (($i + 1) < $groupdata['group_seq'])
	            {
	                sfa_bump_group_seq($groups[$i]->group_id, ($i + 1));
	            } else
	            {
	                sfa_bump_group_seq($groups[$i]->group_id, ($i + 2));
	            }
	        }
	    }

	    $success = sfa_update_group_row($group_id, $groupdata);
	    if ($success == false)
	    {
	        sfa_message(__("Update Failed!", "sforum"));
	    } else
	    {
	        sfa_message(__("Forum Group Record Updated", "sforum"));
	    }
    }

    return;
}

function sfa_update_forum()
{
    check_admin_referer('forum-adminform_forumedit', 'forum-adminform_forumedit');

    $forumdata = array();
    $forum_id = $_POST['forum_id'];
    $forumdata['forum_name'] = $_POST['forum_name'];
    $forumdata['forum_slug'] = $_POST['forum_slug'];
    $forumdata['forum_desc'] = $_POST['forum_desc'];
    $forumdata['forum_seq'] = $_POST['forum_seq'];
    $forumdata['group_id'] = $_POST['group_id'];

    $forumdata['forum_status'] = 0;
    if (isset($_POST['forum_status']))
        $forumdata['forum_status'] = 1;

    $forumdata['forum_rss_private'] = 0;
    if (isset($_POST['forum_private']))
        $forumdata['forum_rss_private'] = 1;

    if (($forumdata['forum_name'] == $_POST['cforum_name']) &&
		($forumdata['forum_slug'] == $_POST['cforum_slug']) &&
		($forumdata['forum_seq'] == $_POST['cforum_seq']) &&
		($forumdata['group_id'] == $_POST['cgroup_id']) &&
		($forumdata['forum_status'] == $_POST['cforum_status']) &&
		($forumdata['forum_rss_private'] == $_POST['cforum_rss_private'])  &&
		($forumdata['forum_desc'] == $_POST['cforum_desc']))
    {
        sfa_message(__("No Data Changed", "sforum"));
        return;
    }

    # has the forum changed to a new group
    if ($forumdata['group_id'] != $_POST['cgroup_id'])
    {
        # let's resequence old group list first
        $forums = sfa_get_other_forums($_POST['cgroup_id'], $forum_id);
        $cnt = count($forums);
        for ($i = 0; $i < $cnt; $i++)
        {
            sfa_bump_forum_seq($forums[$i]->forum_id, ($i + 1));
        }

        # now we can make room in new group
        $seq = (sfa_next_forum_seq($forumdata['group_id']) + 1);
        if ($forumdata['forum_seq'] < $seq)
        {
            $forums = sfa_get_forums_in_group($forumdata['group_id']);
            foreach ($forums as $forum)
            {
                if ($forum->forum_seq >= $forumdata['forum_seq'])
                {
                    sfa_bump_forum_seq($forum->forum_id, ($forum->forum_seq + 1));
                }
            }
        }
    } else
    {
        # same group but has the seq changed?
        if ($forumdata['forum_seq'] != $_POST['cforum_seq'])
        {
            $forums = sfa_get_other_forums($_POST['cgroup_id'], $forum_id);
            $cnt = count($forums);
            for ($i = 0; $i < $cnt; $i++)
            {
                if (($i + 1) < $forumdata['forum_seq'])
                {
                    sfa_bump_forum_seq($forums[$i]->forum_id, ($i + 1));
                } else
                {
                    sfa_bump_forum_seq($forums[$i]->forum_id, ($i + 2));
                }
            }
        }
    }

    # Finally - we can save the updated forum record!
    $success = sfa_update_forum_row($forum_id, $forumdata);
    if ($success == false)
    {
        sfa_message(__("Update Failed!", "sforum"));
    } else
    {
        sfa_message(__("Forum Record Updated", "sforum"));
    }

    return;
}

function sfa_remove_group()
{
    check_admin_referer('forum-adminform_groupdelete', 'forum-adminform_groupdelete');

    $group_id = $_POST['group_id'];
    $cseq = $_POST['cgroup_seq'];

	# remove permissions for each forum in group
	$forums = sfa_get_forums_in_group($group_id);
	if ($forums) {
		foreach ($forums as $forum) {
			# remove permissions for this forum
			$perms = sfa_get_forum_permissions($forum->forum_id);
			if ($perms) {
				foreach ($perms as $perm) {
					sfa_remove_permission_data($perm->permission_id);
				}
			}
		}
	}

    sfa_remove_group_data($group_id);
	sfa_rebuild_members_pm();

    # need to iterate through the groups
    $groups = sf_get_groups_all();
    foreach ($groups as $group)
    {
        if ($group->group_seq > $cseq)
        {
            sfa_bump_group_seq($group->group_id, ($group->group_seq - 1));
        }
    }

	# remove the default permissions for the group being deleted
	sfa_remove_defpermissions($group_id);

    sfa_message(__("Forum Group Deleted", "sforum"));

    return;
}

function sfa_remove_forum()
{
    check_admin_referer('forum-adminform_forumdelete', 'forum-adminform_forumdelete');

    $group_id = $_POST['group_id'];
    $forum_id = $_POST['forum_id'];
    $cseq = $_POST['cforum_seq'];

    sfa_remove_forum_data($forum_id);

	# remove permissions for this forum
	$perms = sfa_get_forum_permissions($forum_id);
	if ($perms) {
		foreach ($perms as $perm) {
			sfa_remove_permission_data($perm->permission_id);
		}
	}

	sfa_rebuild_members_pm();

    # need to iterate through the groups
    $forums = sfa_get_forums_in_group($group_id);
    foreach ($forums as $forum)
    {
        if ($forum->forum_seq > $cseq)
        {
            sfa_bump_forum_seq($forum->forum_id, ($forum->forum_seq - 1));
        }
    }

    return;
}

# function to update an existing permission set for a forum
function sfa_update_permission()
{
    check_admin_referer('forum-adminform_permissionedit', 'forum-adminform_permissionedit');

    $permissiondata = array();
    $permission_id = $_POST['permission_id'];
    $permissiondata['permission_role'] = $_POST['role'];

    # dont do anything if the permission set wasnt actually updated
    if ($permissiondata['permission_role'] == $_POST['ugroup_perm'])
    {
        sfa_message(__("No Data Changed", "sforum"));
        return;
    }

	# save the updated permission set info
    $success = sfa_update_permission_row($permission_id, $permissiondata);
    if ($success == false)
    {
        sfa_message(__("Permission Set Update Failed!", "sforum"));
    } else
    {
        sfa_message(__("Permission Set Updated", "sforum"));
		sfa_rebuild_members_pm();
    }

    return;
}

# function to delete an existing permission set for a forum
function sfa_remove_permission()
{
    check_admin_referer('forum-adminform_permissiondelete', 'forum-adminform_permissiondelete');

    $permission_id = $_POST['permission_id'];

	# remove the permission set from the forum
    $success = sfa_remove_permission_data($permission_id);
    if ($success == false)
    {
        sfa_message(__("Permission Set Delete Failed!", "sforum"));
    } else
    {
        sfa_message(__("Permission Set Deleted", "sforum"));
		sfa_rebuild_members_pm();
    }

    return;
}

# function to add a permission set to every forum within a group
function sfa_add_group_permission()
{
    check_admin_referer('forum-adminform_grouppermissionnew', 'forum-adminform_grouppermissionnew');

	if(isset($_POST['group_id']) && $_POST['usergroup_id'] != -1 && $_POST['role'] != -1)
	{
	    $group_id = $_POST['group_id'];
	    $usergroup_id = $_POST['usergroup_id'];
	    $permission = $_POST['role'];

		# call the helpe function to add the permission set to every forum in group
	    sfa_set_group_permission($group_id, $usergroup_id, $permission);
		sfa_rebuild_members_pm();
	} else {
		sfa_message(__("Adding User Group Permission Set Failed!", "sforum"));
	}
	return;
}

# helper function to loop through all forum in a group and add a permission set
function sfa_set_group_permission($group_id, $usergroup_id, $permission)
{
    $forums = sfa_get_forums_in_group($group_id);

    if ($forums)
    {
        foreach ($forums as $forum)
        {
            # If user group has a current permission set for this forum, remove the old one before adding the new one
            $current = sfa_get_current_permission_data($forum->forum_id, $usergroup_id);

            if ($current)
            {
                sfa_remove_permission_data($current->permission_id);
            }

            # add the new permission set
            $success = sfa_add_permission_data($forum->forum_id, $usergroup_id, $permission);

            if ($success == false)
            {
                sfa_message($forum->forum_name . ": ". __("Adding User Group Permission Set Failed!", "sforum"));
            } else
            {
                sfa_message($forum->forum_name . ": ". __("User Group Permission Set Added to Forum", "sforum"));
            }
        }
    } else
    {
        sfa_message(__("Group has no Members!  No Permission Sets Added!", "sforum"));
    }
    return;
}

# function to add a new permission set to a forum
function sfa_add_permission()
{
    check_admin_referer('forum-adminform_permissionnew', 'forum-adminform_permissionnew');

	if(isset($_POST['forum_id']) && $_POST['usergroup_id'] != -1 && $_POST['role'] != -1)
	{
		$usergroup_id = $_POST['usergroup_id'];
		$forum_id = $_POST['forum_id'];
		$permission = $_POST['role'];

		# If user group has a current permission set for this forum, remove the old one before adding the new one
		$current = sfa_get_current_permission_data($forum_id, $usergroup_id);

		if ($current)
		{
			sfa_remove_permission_data($current->permission_id);
		}

		# add the new permission set
		$success = sfa_add_permission_data($forum_id, $usergroup_id, $permission);
		if ($success == false)
		{
			sfa_message(__("Adding User Group Permission Set Failed!", "sforum"));
		} else
		{
			sfa_message(__("User Group Permission Set Added to Forum", "sforum"));
			sfa_rebuild_members_pm();
		}
	} else {
		sfa_message(__("Adding User Group Permission Set Failed!", "sforum"));
	}
    return;
}

# function to add a permission set globally to all forum
function sfa_add_global_permission()
{
    check_admin_referer('forum-adminform_globalpermissionnew', 'forum-adminform_globalpermissionnew');

	if($_POST['usergroup_id'] != -1 && $_POST['role'] != -1)
	{
	    $usergroup_id = $_POST['usergroup_id'];
    	$permission = $_POST['role'];

		# loop through all the groups
		$groups = sf_get_groups_all();
  	  	if ($groups)
  	  	{
        	foreach ($groups as $group)
        	{
            	# use group permission set helper function to actually set the permission set
            	$current = sfa_set_group_permission($group->group_id, $usergroup_id, $permission);
            }
			sfa_rebuild_members_pm();
        } else {
        	sfa_message(__("There are no Groups or Forum!  No Permission Set Added!", "sforum"));
    	}
	} else {
		sfa_message(__("Adding User Group Permission Set Failed!", "sforum"));
	}
    return;
}

# function to remove all permission set from all forum
function sfa_remove_all_permissions()
{
    check_admin_referer('forum-adminform_allpermissionsdelete', 'forum-adminform_allpermissionsdelete');

	# remove all permission set
    sfa_remove_all_permission_data();
	sfa_rebuild_members_pm();

    sfa_message(__("All Permission Sets Removed.", "sforum"));

    return;
}

?>