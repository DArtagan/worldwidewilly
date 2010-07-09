<?php
/*
Simple:Press Forum
Admin Permissions
$LastChangedDate: 2009-01-16 14:53:29 +0000 (Fri, 16 Jan 2009) $
$Rev: 1219 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# Check Whether User Can Manage Permissions
if(!sf_current_user_can('SPF Manage Permissions')) {
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminpermissions.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');

include_once ('sf-adminsupport.php');
include_once ('sf-admin.php');
include_once ('sf-adminpermissionsforms.php');

if(sfa_get_system_status() != 'ok')
{
	include_once(SFLOADER);
	die();
}

# = ADMIN DISTRUBUTION ========================

# create new role
if (isset($_POST['newrole']))
    sfa_create_role();

# delete role
if (isset($_POST['deleterole']))
    sfa_remove_role();

# edit role
if (isset($_POST['editrole']))
    sfa_update_role();

sfa_rolespage();
sfa_footer();

# = ADMIN PANELS DISTRIBUTION==================

function sfa_rolespage()
{
    sfa_header(__('SPF Manage Permissions', 'sforum'), 'icon-permissions');
    sfa_render_roles_index();

    return;
}

# function to create a new permission set role
function sfa_create_role()
{
    global $sfactions;

    check_admin_referer('forum-adminform_rolenew', 'forum-adminform_rolenew');

    $action_name = $_POST['action_name'];

    foreach ($sfactions["action"] as $index => $action)
    {
    	if(isset($_POST['b-'.$index]) ? $thisperm = '1' : $thisperm = '0');
    	$actions[$action_name[$index]] = $thisperm;
    }
	$actions = maybe_serialize($actions);

    $role_name = $_POST['role_name'];
    $role_desc = $_POST['role_desc'];

    # force max size
    $role_name = substr($role_name, 0, 50);
    $role_desc = substr($role_desc, 0, 150);

    # create the permission set
    $success = sfa_create_role_row($role_name, $role_desc, $actions, true);
    if ($success == false)
    {
        sfa_message(__("New Permission Set Creation Failed!", "sforum"));
    } else
    {
        sfa_message(__("New Permission Set Created", "sforum"));
    }

    return;
}

# function to remove a permission set role
function sfa_remove_role()
{
    check_admin_referer('forum-adminform_roledelete', 'forum-adminform_roledelete');

    $role_id = $_POST['role_id'];

    # remove all permission set that use the role we are deleting
    $permissions = sfa_get_role_permissions($role_id);
    if ($permissions)
    {
        foreach ($permissions as $permission)
        {
            sfa_remove_permission_data($permission->permission_id);
        }
    }

    # remove the permission set role
    $success = sfa_remove_role_data($role_id);
    if ($success == false)
    {
        sfa_message(__("Permission Set Deletion Failed!", "sforum"));
    } else
    {
        sfa_message(__("Permission Set Deleted", "sforum"));
		sfa_rebuild_members_pm();
    }

    return;
}

# function to update a current permission set role
function sfa_update_role()
{
    global $sfactions;

    check_admin_referer('forum-adminform_roleedit', 'forum-adminform_roleedit');

    $role_id = $_POST['role_id'];
    $role_name = $_POST['role_name'];
    $role_desc = $_POST['role_desc'];

    $action_name = $_POST['action_name'];

	# get old permissions to check for pm role changes
	$old_roles = sfa_get_role_row($role_id);
	$old_actions = unserialize($old_roles->role_actions);

    foreach ($sfactions["action"] as $index => $action)
    {
    	if(isset($_POST['b-'.$index]) ? $thisperm = '1' : $thisperm = '0');
    	$actions[$action_name[$index]] = $thisperm;
    }
    $new_actions = $actions;

    # save for later user before serializing
	$actions = maybe_serialize($actions);

    $roledata = array();
    $roledata['role_name'] = $role_name;
    $roledata['role_desc'] = $role_desc;

    # force max size
    $roledata['role_name'] = substr($roledata['role_name'], 0, 50);
    $roledata['role_desc'] = substr($roledata['role_desc'], 0, 150);

    # save the permission set role updated information
    $success = sfa_update_role_row($role_id, $roledata, $actions);
    if ($success == false)
    {
        sfa_message(__("Permission Set Update Failed!", "sforum"));
    } else
    {
        sfa_message(__("Permission Set Updated", "sforum"));
		if ($old_actions['Can use private messaging'] != $new_actions['Can use private messaging'])
		{
			sfa_rebuild_members_pm();
		}
    }

    return;
}

?>