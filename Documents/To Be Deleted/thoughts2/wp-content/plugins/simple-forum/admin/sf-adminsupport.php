<?php
/*
Simple:Press Forum
Admin Support Routines
$LastChangedDate: 2009-05-30 17:21:25 +0100 (Sat, 30 May 2009) $
$Rev: 1963 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

function sfa_get_other_groups($group_id)
{
	global $wpdb;
	return $wpdb->get_results("SELECT group_id, group_seq FROM ".SFGROUPS." WHERE group_id <> ".$group_id." ORDER BY group_seq;");
}

function sfa_get_other_forums($group_id, $forum_id)
{
	global $wpdb;
	return $wpdb->get_results("SELECT forum_id, forum_seq FROM ".SFFORUMS." WHERE group_id=".$group_id." AND forum_id <> ".$forum_id." ORDER BY forum_seq;");
}

function sfa_get_group_row($group_id)
{
	global $wpdb;
	return $wpdb->get_row("SELECT * FROM ".SFGROUPS." WHERE group_id=".$group_id);
}

function sfa_get_forums_in_group($groupid)
{
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM ".SFFORUMS." WHERE group_id=".$groupid." ORDER BY forum_seq");
}

function sfa_get_forums_all()
{
	global $wpdb;

	return $wpdb->get_results(
		"SELECT forum_id, forum_name, ".SFGROUPS.".group_id, group_name
		 FROM ".SFFORUMS."
		 LEFT JOIN ".SFGROUPS." ON ".SFFORUMS.".group_id = ".SFGROUPS.".group_id
		 ORDER BY group_seq, forum_seq");
}

function sfa_get_topic_count($forumid)
{
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(topic_id) AS cnt FROM ".SFTOPICS." WHERE forum_id=".$forumid);
}

function sfa_update_group_row($group_id, $groupdata)
{
	global $wpdb;

	$groupname = $wpdb->escape($groupdata['group_name']);
	$groupname = sf_filter_nohtml_kses($groupname);
	$groupdesc = $wpdb->escape($groupdata['group_desc']);

	$sql = "UPDATE ".SFGROUPS." SET ";
	$sql.= 'group_name="'.$groupname.'", ';
	$sql.= 'group_desc="'.$groupdesc.'", ';
	$sql.= 'group_seq='.$groupdata['group_seq']." ";
	$sql.= "WHERE group_id=".$group_id.";";

	return $wpdb->query($sql);
}

function sfa_update_forum_row($forum_id, $forumdata)
{
	global $wpdb;

	$forumname = $wpdb->escape($forumdata['forum_name']);
	$forumname = sf_filter_nohtml_kses($forumname);
	$forumdesc = $wpdb->escape($forumdata['forum_desc']);

	if(empty($forumdata['forum_slug']))
	{
		include_once(SF_PLUGIN_DIR.'/sf-slugs.php');

		$forumslug = sf_create_slug($forumdata['forum_name'], 'forum');
		if(empty($forumslug)) $forumslug = 'forum-'.$forum_id;
	} else {
		$forumslug = $forumdata['forum_slug'];
	}

	$sql = "UPDATE ".SFFORUMS." SET ";
	$sql.= 'forum_name="'.$forumname.'", ';
	$sql.= 'forum_slug="'.$forumslug.'", ';
	$sql.= 'forum_desc="'.$forumdesc.'", ';
	$sql.= 'group_id='.$forumdata['group_id'].', ';
	$sql.= 'forum_status='.$forumdata['forum_status'].', ';
	$sql.= 'forum_rss_private='.$forumdata['forum_rss_private'].', ';
	$sql.= 'forum_seq='.$forumdata['forum_seq']." ";
	$sql.= "WHERE forum_id=".$forum_id.";";

	return $wpdb->query($sql);
}

function sfa_create_group_row($groupdata)
{
	global $wpdb;

	$groupname = $wpdb->escape($groupdata['group_name']);
	$groupname = sf_filter_nohtml_kses($groupname);
	$groupdesc = $wpdb->escape($groupdata['group_desc']);

	$sql ="INSERT INTO ".SFGROUPS." (group_name, group_desc, group_seq) ";
	$sql.="VALUES ('".$groupname."', '".$groupdesc."', ".$groupdata['group_seq'].");";

	return $wpdb->query($sql);
}

function sfa_create_forum_row($forumdata)
{
	global $wpdb;

include_once(SF_PLUGIN_DIR.'/sf-slugs.php');

	$forumname = $wpdb->escape($forumdata['forum_name']);
	$forumname = sf_filter_nohtml_kses($forumname);
	$forumdesc = $wpdb->escape($forumdata['forum_desc']);

	# create slug
	$forumslug = sf_create_slug(stripslashes($forumname), 'forum');
	$sql ="INSERT INTO ".SFFORUMS." (forum_name, forum_slug, forum_desc, group_id, forum_status, forum_seq, forum_rss_private) ";
	$sql.="VALUES ('".$forumname."', '".$forumslug."', '".$forumdesc."', ".$forumdata['group_id'].", ".$forumdata['forum_status'].", ".$forumdata['forum_seq'].", ".$forumdata['forum_rss_private'].");";
	$thisforum = $wpdb->query($sql);

	# now check the slug was populated and if not replace with forum id
	if(empty($forumslug))
	{
		$forumid = $wpdb->insert_id;
		$forumslug = 'forum-'.$forumid;
		$thisforum = $wpdb->query("UPDATE ".SFFORUMS." SET forum_slug='".$forumslug."' WHERE forum_id=".$forumid);
	}
	return $thisforum;
}

function sfa_bump_group_seq($id, $seq)
{
	global $wpdb;

	$sql = "UPDATE ".SFGROUPS." SET ";
	$sql.= 'group_seq='.$seq." ";
	$sql.= "WHERE group_id=".$id.";";

	$wpdb->query($sql);
	return;
}

function sfa_bump_forum_seq($id, $seq)
{
	global $wpdb;

	$sql = "UPDATE ".SFFORUMS." SET ";
	$sql.= 'forum_seq='.$seq." ";
	$sql.= "WHERE forum_id=".$id.";";

	$wpdb->query($sql);
	return;
}

function sfa_remove_group_data($group_id)
{
	global $wpdb;

	# select all the forums in the group
	$forums = sfa_get_forums_in_group($group_id);
	# remove the topics and posts in each forum
	foreach($forums as $forum)
	{
		$wpdb->query("DELETE FROM ".SFPOSTS." WHERE forum_id=".$forum->forum_id);
		$wpdb->query("DELETE FROM ".SFTOPICS." WHERE forum_id=".$forum->forum_id);
	}
	#now remove the forums themselves
	$wpdb->query("DELETE FROM ".SFFORUMS." WHERE group_id=".$group_id);
	# and finaly remove the group
	$wpdb->query("DELETE FROM ".SFGROUPS." WHERE group_id=".$group_id);
	return;
}

function sfa_remove_forum_data($forum_id)
{
	global $wpdb;

	$wpdb->query("DELETE FROM ".SFPOSTS." WHERE forum_id=".$forum_id);
	$wpdb->query("DELETE FROM ".SFTOPICS." WHERE forum_id=".$forum_id);
	$wpdb->query("DELETE FROM ".SFFORUMS." WHERE forum_id=".$forum_id);
	return;
}

function sfa_next_group_seq()
{
	global $wpdb;
	return $wpdb->get_var("SELECT MAX(group_seq) FROM ".SFGROUPS);
}

function sfa_next_forum_seq($groupid)
{
	global $wpdb;
	return $wpdb->get_var("SELECT MAX(forum_seq) FROM ".SFFORUMS." WHERE group_id=".$groupid);
}

function sfa_create_group_select($groupid = 0)
{
	$groups = sf_get_groups_all();
	$out='';
	$default='';
	foreach($groups as $group)
	{
		if($group->group_id == $groupid)
		{
			$default = 'selected="selected" ';
		} else {
			$default - null;
		}
		$out.='<option '.$default.'value="'.$group->group_id.'">'.stripslashes($group->group_name).'</option>'."\n";
		$default='';
	}
	return $out;
}

function sfa_create_forum_select($forumid = 0)
{
	$forums = sf_get_forums_all();
	$out='';
	$default='';
	foreach($forums as $forum)
	{
		if($forum->forum_id == $forumid)
		{
			$default = 'selected="selected" ';
		} else {
			$default - null;
		}
		$out.='<option '.$default.'value="'.$forum->forum_id.'">'.stripslashes($forum->forum_name).'</option>'."\n";
		$default='';
	}
	return $out;
}

function sfa_create_skin_select($skin)
{
	$path = SF_PLUGIN_DIR . '/styles/skins';

	$out='';
	$default='';
	$dlist = opendir($path);

	while (false !== ($file = readdir($dlist)))
	{
		if (($file != "." && $file != "..") && (is_dir($path.'/'.$file)))
		{
			if($file == $skin)
			{
				$default = 'selected="selected" ';
			} else {
				$default - null;
			}
			$out.='<option '.$default.'value="'.$file.'">'.$file.'</option>'."\n";
			$default='';
		}
	}
	closedir($dlist);
	return $out;
}

function sfa_create_icon_select($icon)
{
	$path = SF_PLUGIN_DIR . '/styles/icons';

	$out='';
	$default='';
	$dlist = opendir($path);

	while (false !== ($file = readdir($dlist)))
	{
		if (($file != "." && $file != "..") && (is_dir($path.'/'.$file)))
		{
			if($file == $icon)
			{
				$default = 'selected="selected" ';
			} else {
				$default - null;
			}
			$out.='<option '.$default.'value="'.$file.'">'.$file.'</option>'."\n";
			$default='';
		}
	}
	closedir($dlist);
	return $out;
}

function sfa_create_language_select($lang="en")
{
	$path = SF_PLUGIN_DIR . '/editors/tinymce/langs';

	$out='';
	$default='';
	$dlist = opendir($path);

	while (false !== ($file = readdir($dlist)))
	{
		if ($file != "." && $file != "..")
		{
			$langcode=explode(".", $file);
			$langcode=$langcode[0];
			if($langcode == $lang)
			{
				$default = 'selected="selected" ';
			} else {
				$default - null;
			}
			$out.='<option '.$default.'value="'.$langcode.'">'.$langcode.'</option>'."\n";
			$default='';
		}
	}
	closedir($dlist);
	return $out;
}

function sfa_create_usergroup_select($sfdefgroup)
{
	$usergroups = sfa_get_usergroups_all();
	$default='';
	foreach ($usergroups as $usergroup)
	{
		if($usergroup->usergroup_id == $sfdefgroup)
		{
			$default = 'selected="selected" ';
		} else {
			$default - null;
		}
		$out.='<option '.$default.'value="'.$usergroup->usergroup_id.'">'.wp_specialchars($usergroup->usergroup_name).'</option>'."\n";
		$default='';
	}
	return $out;
}


function sfa_create_topic_status_select($current = '')
{
	global $wpdb;

	$sets = $wpdb->get_results("SELECT meta_id, meta_key FROM ".SFMETA." WHERE meta_type='topic-status'");
	$out='';
	$out.= '<select class="sfquicklinks sfacontrol" name="topstatsetselect">'."\n";
	if($sets)
	{
		$out.= '<option value="">'.__("Select Status Set:", "sforum").'</option>';
		$default='';
		foreach($sets as $set)
		{
			if($set->meta_id == $current)
			{
				$default = 'selected="selected" ';
			} else {
				$default - null;
			}
			$out.='<option '.$default.'value="'.$set->meta_id.'">'.stripslashes($set->meta_key).'</option>'."\n";
			$default='';
		}
	}

	if($current)
	{
		$out.= '<option value="%remove%">'.__("Remove Current Assignment", "sforum").'</option>';
	}

	$out.='</select>';
	return $out;
}

function sfa_update_check_option($key)
{
	if(isset($_POST[$key]))
	{
		update_option($key, true);
	} else {
		update_option($key, false);
	}
	return;
}

$sfactions = array(
    "action" => array(
	'Can view forum',
	'Can start new topics',
	'Can reply to topics',
	'Can break linked topics',
	'Can edit topic titles',
	'Can pin topics',
	'Can move topics',
	'Can move posts',
	'Can lock topics',
	'Can delete topics',
	'Can edit own posts forever',
	'Can edit own posts until reply',
	'Can edit any posts',
	'Can delete any posts',
	'Can pin posts',
	'Can view users email addresses',
	'Can view members profiles',
	'Can report posts',
	'Can sort most recent posts',
	'Can bypass spam control',
	'Can bypass post moderation',
	'Can bypass post moderation once',
	'Can moderate pending posts',
	'Can create linked topics',
	'Can upload images',
	'Can use signatures',
	'Can use images in signatures',
	'Can upload avatars',
	'Can use private messaging',
	'Can subscribe',
	'Can watch topics',
	'Can change topic status',
	'Can rate posts'
	),
	"members" => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,2,2,1,1,1,1)
);

function sfa_check_warnings()
{
	global $wpdb;

	# output warning if no SPF admins are defined
	if (sf_get_admins() == '')
	{
			sfa_message(__('Warning - There are no SPF Admins defined!  All WP admins now have SPF backend access!  No users have front end Forum Access!', 'sforum'));
	}

	# check for unreachable forums because of permissions
	$done = 0;
	$guests = get_option('sfguestsgroup');
	$usergroups = $wpdb->get_results("SELECT usergroup_id FROM ".SFUSERGROUPS);
	if ($usergroups)
	{
		$has_members = false;
		foreach ($usergroups as $usergroup)
		{
			$members = $wpdb->get_row("SELECT user_id FROM ".SFMEMBERSHIPS." WHERE usergroup_id=".$usergroup->usergroup_id." LIMIT 1");
			if ($members || $usergroup->usergroup_id == $guests)
			{
				$has_members = true;
				break;
			}
		}

		if (!$has_members)
		{
			sfa_message(__('Warning - There are no User Groups that have Members!  All Forums may only visible to Admin!', 'sforum'));
			$done = 1;
		}
	} else {
		sfa_message(__('Warning - There are no User Groups defined!  All Forums may only visible to Admin!', 'sforum'));
		$done = 1;
	}

	$roles = sfa_get_all_roles();
	if (!$roles)
	{
		sfa_message(__('Warning - There are no Permission Sets defined!  All Forums may only visible to Admin!', 'sforum'));
		$done = 1;
	}

	# dont duplicate forum warnings if there are no user groups, no user groups with members or no permission sets
	if ($done) return;

	$forums = $wpdb->get_results("SELECT forum_id, forum_name FROM ".SFFORUMS);
	if ($forums)
	{
		foreach ($forums as $forum)
		{
			$has_members = false;
			$permissions = sfa_get_forum_permissions($forum->forum_id);
			if ($permissions)
			{
				foreach ($permissions as $permission)
				{
					$members = $wpdb->get_row("SELECT user_id FROM ".SFMEMBERSHIPS." WHERE usergroup_id = $permission->usergroup_id LIMIT 1");
					if ($members || $usergroup->usergroup_id == $guests)
					{
						$has_members = true;
						break;
					}
				}
			}

			if (!$has_members)
			{
				sfa_message(__('Warning - There are no User Groups with Members that have Permissions to Forum: '.$forum->forum_name.'.  This Forum may be only visible to Admin!', 'sforum'));
			}
		}
	}

	return;
}

function sfa_get_usergroups_all($usergroupid=Null)
{
	global $wpdb;
	$where='';
	if(!is_null($usergroupid)) $where=" WHERE usergroup_id=".$usergroupid;
	return $wpdb->get_results("SELECT * FROM ".SFUSERGROUPS.$where);
}

function sfa_get_usergroups_row($usergroup_id)
{
	global $wpdb;

	return $wpdb->get_row("SELECT * FROM ".SFUSERGROUPS." WHERE usergroup_id=".$usergroup_id);
}

function sfa_update_usergroup_row($usergroup_id, $usergroupdata)
{
	global $wpdb;

	$usergroupname = $wpdb->escape($usergroupdata['usergroup_name']);
	$usergroupdesc = $wpdb->escape($usergroupdata['usergroup_desc']);
	$usergroupismod = $wpdb->escape($usergroupdata['usergroup_is_moderator']);

	$sql = "UPDATE ".SFUSERGROUPS." SET ";
	$sql.= 'usergroup_name="'.$usergroupname.'", ';
	$sql.= 'usergroup_desc="'.$usergroupdesc.'", ';
	$sql.= 'usergroup_is_moderator="'.$usergroupismod.'" ';
	$sql.= 'WHERE usergroup_id="'.$usergroup_id.'";';

	return $wpdb->query($sql);
}

function sfa_create_usergroup_row($usergroupname, $usergroupdesc, $usergroupismod, $report_failure=false)
{
	global $wpdb;

	$usergroupname = $wpdb->escape($usergroupname);
	$usergroupdesc = $wpdb->escape($usergroupdesc);
	$usergroupismod = $wpdb->escape($usergroupismod);

	# first check to see if user group name exists
	$exists = $wpdb->get_var("SELECT usergroup_id FROM ".SFUSERGROUPS." WHERE usergroup_name='".$usergroupname."'");
	if($exists)
	{
		if($report_failure == true)
		{
			return false;
		} else {
			return $exists;
		}
	}

	# go on and create the new user group
	$sql ="INSERT INTO ".SFUSERGROUPS." (usergroup_name, usergroup_desc, usergroup_is_moderator) ";
	$sql.="VALUES ('".$usergroupname."', '".$usergroupdesc."', '".$usergroupismod."');";

	if($wpdb->query($sql))
	{
		return $wpdb->insert_id;
	} else {
		return false;
	}
}

function sfa_remove_usergroup_data($usergroup_id)
{
	global $wpdb;

	# remove the group
   	$wpdb->query("DELETE FROM ".SFMEMBERSHIPS." WHERE usergroup_id=".$usergroup_id);
	return $wpdb->query("DELETE FROM ".SFUSERGROUPS." WHERE usergroup_id=".$usergroup_id);
}

function sfa_get_users_all()
{
	global $wpdb;

	return $wpdb->get_results("SELECT user_id, display_name FROM ".SFMEMBERS." ORDER BY display_name");
}

function sfa_get_forum_permissions($forum_id)
{
	global $wpdb;

	return $wpdb->get_results("SELECT * FROM ".SFPERMISSIONS." WHERE forum_id=".$forum_id." ORDER BY permission_role");
}

function sfa_get_permission_row($permission_id)
{
	global $wpdb;

	return $wpdb->get_row("SELECT * FROM ".SFPERMISSIONS." WHERE permission_id=".$permission_id);
}

function sfa_get_current_permission_data($forum_id, $usergroup_id)
{
	global $wpdb;

	return $wpdb->get_row("SELECT * FROM ".SFPERMISSIONS." WHERE forum_id=".$forum_id." AND usergroup_id=".$usergroup_id);
}

function sfa_get_usergroup_permissions($usergroup_id)
{
	global $wpdb;

	return $wpdb->get_results("SELECT permission_id FROM ".SFPERMISSIONS." WHERE usergroup_id=".$usergroup_id);
}

function sfa_add_membership($usergroup_id, $user_id)
{
	global $wpdb;


	$sql ="INSERT INTO ".SFMEMBERSHIPS." (user_id, usergroup_id) ";
	$sql.="VALUES ('".$user_id."', '".$usergroup_id."');";

	return $wpdb->query($sql);
}

function sfa_delete_membership($usergroup_id, $user_id)
{
	global $wpdb;

	return $wpdb->query("DELETE FROM ".SFMEMBERSHIPS." WHERE user_id=".$user_id." AND usergroup_id=".$usergroup_id);
}

function sfa_get_usergroup_memberships($usergroup_id)
{
	global $wpdb;

	$sql = "SELECT ".SFMEMBERSHIPS.".user_id, display_name
			FROM ".SFMEMBERSHIPS."
			LEFT JOIN ".SFMEMBERS." ON ".SFMEMBERS.".user_id = ".SFMEMBERSHIPS.".user_id
			WHERE ".SFMEMBERSHIPS.".usergroup_id=".$usergroup_id."
			ORDER BY display_name";
	return $wpdb->get_results($sql);
}

function sfa_get_user_memberships($user_id)
{
	global $wpdb;

	return $wpdb->get_results("SELECT usergroup_id FROM ".SFMEMBERSHIPS." WHERE user_id=".$user_id, ARRAY_A);
}

function sfa_delete_usergroup_memberships($usergroup_id)
{
	global $wpdb;

	return $wpdb->query("DELETE FROM ".SFMEMBERSHIPS." WHERE usergroup_id=".$usergroup_id);
}

function sfa_delete_user_memberships($user_id)
{
	global $wpdb;

	return $wpdb->query("DELETE FROM ".SFMEMBERSHIPS." WHERE user_id=".$user_id);
}

function sfa_add_permission_data($forum_id, $usergroup_id, $permission)
{
	global $wpdb;

	$forumid = $wpdb->escape($forum_id);
	$usergroupid = $wpdb->escape($usergroup_id);
	$perm = $wpdb->escape($permission);

	$sql ="INSERT INTO ".SFPERMISSIONS." (forum_id, usergroup_id, permission_role) ";
	$sql.="VALUES ('".$forumid."', '".$usergroupid."', '".$perm."');";

	return $wpdb->query($sql);
}

function sfa_update_permission_row($permission_id, $permission)
{
	global $wpdb;

	$sql = "UPDATE ".SFPERMISSIONS." SET ";
	$sql.= 'permission_role="'.$permission['permission_role'].'" ';
	$sql.= "WHERE permission_id=".$permission_id.";";

	return $wpdb->query($sql);
}

function sfa_remove_permission_data($permission_id)
{
	global $wpdb;

	return $wpdb->query("DELETE FROM ".SFPERMISSIONS." WHERE permission_id=".$permission_id);
}

function sfa_remove_all_permission_data()
{
	global $wpdb;

	return $wpdb->query("TRUNCATE TABLE ".SFPERMISSIONS);
}

function sfa_get_all_roles()
{
	global $wpdb;

	return $wpdb->get_results("SELECT * FROM ".SFROLES." ORDER BY role_id");
}

function sfa_create_role_row($role_name, $role_desc, $actions, $report_failure=false)
{
	global $wpdb;

	$rolename = $wpdb->escape($role_name);
	$roledesc = $wpdb->escape($role_desc);

	# first check to see if rolename exists
	$exists = $wpdb->get_var("SELECT role_id FROM ".SFROLES." WHERE role_name='".$role_name."'");
	if($exists)
	{
		if($report_failure == true)
		{
			return false;
		} else {
			return $exists;
		}
	}

	# go on and create the new role
	$sql ="INSERT INTO ".SFROLES." (role_name, role_desc, role_actions) ";
	$sql.="VALUES ('".$rolename."', '".$roledesc."', '".$actions."');";

	if($wpdb->query($sql))
	{
		return $wpdb->insert_id;
	} else {
		return false;
	}
}

function sfa_get_role_row($role_id)
{
	global $wpdb;

	return $wpdb->get_row("SELECT * FROM ".SFROLES." WHERE role_id=".$role_id);
}

function sfa_remove_role_data($role_id)
{
	global $wpdb;

	return $wpdb->query("DELETE FROM ".SFROLES." WHERE role_id=".$role_id);
}

function sfa_update_role_row($role_id, $roledata, $actions)
{
	global $wpdb;

	$rolename = $wpdb->escape($roledata['role_name']);
	$roledesc = $wpdb->escape($roledata['role_desc']);
	$actions = $wpdb->escape($actions);

	$sql = "UPDATE ".SFROLES." SET ";
	$sql.= 'role_name="'.$rolename.'", ';
	$sql.= 'role_desc="'.$roledesc.'", ';
	$sql.= 'role_actions="'.$actions.'" ';
	$sql.= "WHERE role_id=".$role_id.";";

	return $wpdb->query($sql);
}

function sfa_get_defpermissions($group_id)
{
	global $wpdb;

	return $wpdb->get_results("
		SELECT permission_id, ".SFUSERGROUPS.".usergroup_id, permission_role, usergroup_name
		FROM ".SFDEFPERMISSIONS."
		LEFT JOIN ".SFUSERGROUPS." ON ".SFDEFPERMISSIONS.".usergroup_id = ".SFUSERGROUPS.".usergroup_id
		WHERE group_id=".$group_id);
}

function sfa_get_defpermissions_role($group_id, $usergroup_id)
{
	global $wpdb;

	return $wpdb->get_var("
		SELECT permission_role
		FROM ".SFDEFPERMISSIONS."
		WHERE group_id=".$group_id." AND usergroup_id=".$usergroup_id);
}

function sfa_update_defpermission_row($group_id, $usergroup_id, $role)
{
	global $wpdb;

	$sql = "
		UPDATE ".SFDEFPERMISSIONS."
		SET permission_role=$role
		WHERE group_id=$group_id AND usergroup_id=$usergroup_id";

	return $wpdb->query($sql);
}

function sfa_add_defpermission_row($group_id, $usergroup_id, $role)
{
	global $wpdb;

	$sql = "
		INSERT INTO ".SFDEFPERMISSIONS."
		(group_id, usergroup_id, permission_role)
		VALUES
		($group_id, $usergroup_id, $role)";

	return $wpdb->query($sql);
}

function sfa_remove_defpermissions($group_id)
{
	global $wpdb;

	return $wpdb->query("DELETE FROM ".SFDEFPERMISSIONS." WHERE group_id=".$group_id);
}

function sfa_remove_usergroup_defpermissions($usergroup_id)
{
	global $wpdb;

	return $wpdb->query("DELETE FROM ".SFDEFPERMISSIONS." WHERE usergroup_id=".$usergroup_id);
}

function sfa_reset_post_ratings()
{
	global $wpdb;

	return $wpdb->query("TRUNCATE ".SFPOSTRATINGS);

}

function sfa_get_role_permissions($role_id)
{
	global $wpdb;

	return $wpdb->get_results("SELECT * FROM ".SFPERMISSIONS." WHERE permission_role=".$role_id);
}

function sfa_get_all_forum()
{
	global $wpdb;

	return $wpdb->get_results("SELECT * FROM ".SFFORUMS." ORDER BY forum_id");
}

function sfa_del_custom_field($cfield)
{
	global $wpdb;

	$wpdb->query("DELETE FROM ".SFUSERMETA." WHERE meta_key='sfcustomfield".$cfield."'");
}

function sfa_get_first_topic_date($forum_id)
{
	global $wpdb;

	return $wpdb->get_var("SELECT topic_date FROM ".SFTOPICS." WHERE forum_id='".$forum_id."' ORDER BY topic_date ASC LIMIT 1");
}

function sfa_get_last_topic_date($forum_id)
{
	global $wpdb;

	return $wpdb->get_var("SELECT topic_date FROM ".SFTOPICS." WHERE forum_id='".$forum_id."' ORDER BY topic_date DESC LIMIT 1");
}

function sfa_display_usergroup_select($filter = false, $forum_id = 0)
{ ?>
										<?php $usergroups = sfa_get_usergroups_all(); ?>
										<p><?php _e("Select User Group", "sforum") ?>:&nbsp;&nbsp;
										<select style="width:145px" class='sfacontrol' name='usergroup_id'>
<?php
											$out = '<option value="-1">'.__("Select User Group", "sforum").'</option>';
											if ($filter) $perms = sfa_get_forum_permissions($forum_id);
											foreach ($usergroups as $usergroup)
											{
												$disabled = '';
												if ($filter ==1 and $perms) {
													foreach ($perms as $perm) {
														if ($perm->usergroup_id == $usergroup->usergroup_id) {
															$disabled = 'disabled="disabled" ';
															continue;
														}
													}
												}
												$out.='<option '.$disabled.'value="'.$usergroup->usergroup_id.'">'.wp_specialchars($usergroup->usergroup_name).'</option>'."\n";
												$default='';
											}
											echo $out;
?>
										</select></p>
<?php
}

function sfa_display_permission_select($cur_perm = 0)
{ ?>
	          												<?php $roles = sfa_get_all_roles(); ?>
															<p><?php _e("Select Permission Set", "sforum") ?>:&nbsp;&nbsp;
															<select style="width:165px" class='sfacontrol' name='role'>
<?php
																$out = '';
																if ($cur_perm == 0) $out='<option value="-1">'.__("Select Permission Set", "sforum").'</option>';
																foreach($roles as $role)
																{
																	$selected = '';
																	if ($cur_perm == $role->role_id) $selected = 'selected = "selected" ';
																	$out.='<option '.$selected.'value="'.$role->role_id.'">'.wp_specialchars($role->role_name).'</option>'."\n";
																}
																echo $out;
?>
															</select></p>
<?php
}

function sfa_get_last_insert_id()
{
	global $wpdb;

	return $wpdb->get_var("SELECT LAST_INSERT_ID()");
}

# function to replace the default RSS URL with abn external one (Feedburner)
function sfa_replace_rss_url()
{
	global $wpdb;

	check_admin_referer('forum-adminform_rssedit', 'forum-adminform_rssedit');

	$item = $_POST['item'];
	$id = $_POST['id'];

	if(!empty($_POST['newRSSurl']))
	{
		sfa_update_rss($item, $id, $_POST['newRSSurl']);
		sfa_message(sprintf(__("RSS URL Replaced for: %s - %s", "sforum"), $item, $id));
	}

	# check for making forums private
	if ($item == 'Forum')
	{
		$private = isset($_POST['newRSSpvt']) ? 1 : 0;
		$wpdb->query("UPDATE ".SFFORUMS." SET forum_rss_private=$private WHERE forum_slug = '".$id."'");
	} else {
		if (isset($_POST['setallRSSpvt']))
		{
			$private = ($_POST['setallRSSpvt'] == 'clear') ? 0 : 1;
			if ($item == 'Group')
			{
				$wpdb->query("UPDATE ".SFFORUMS." SET forum_rss_private=$private WHERE group_id = ".$id);
			} else {
				# all forums
				$wpdb->query("UPDATE ".SFFORUMS." SET forum_rss_private=$private");
			}
		}
	}

	return;
}

function sfa_update_rss($item, $id, $url)
{
	global $wpdb;

	switch($item)
	{
		case 'Group':
			$wpdb->query("UPDATE ".SFGROUPS." SET group_rss='".$url."' WHERE group_id = ".$id);
			break;
		case 'Forum':
			$wpdb->query("UPDATE ".SFFORUMS." SET forum_rss='".$url."' WHERE forum_slug = '".$id."'");
			break;
		case 'All':
			update_option('sfallRSSurl', $url);
			break;
	}
	return;
}

# function to save a custom icon setting for groups and forums
function sfa_replace_icon()
{
	global $wpdb;

	check_admin_referer('forum-adminform_iconedit', 'forum-adminform_iconedit');

	if(((!empty($_POST['cusicon'])) && (isset($_POST['setIcon']))) || (isset($_POST['removeIcon'])))
	{
		if(isset($_POST['setIcon']))
		{
			# Check new icon exists
			$icon = $_POST['cusicon'];
			$path = SFCUSTOM.$icon;
			if(!file_exists($path))
			{
				sfa_message(sprintf(__("Custom Icon '%s' does not exist", "sforum"), $icon));
				return;
			}
		} else {
			$icon = NULL;
		}

		$item=$_POST['item'];
		$id = $_POST['id'];

		if($item == "Group")
		{
			$wpdb->query("UPDATE ".SFGROUPS." SET group_icon='".$icon."' WHERE group_id = ".$id);
		} else {
			$wpdb->query("UPDATE ".SFFORUMS." SET forum_icon='".$icon."' WHERE forum_id = ".$id);
		}
		sfa_message(sprintf(__("Custom Icon has been Updated for: %s - %s", "sforum"), $item, $id));
	}
	return;
}

function sfa_get_users_pm_data()
{
	global $wpdb;

	$records = array();
	$users = $wpdb->get_results("
			SELECT user_id, display_name, pm, to_id, from_id, message_status, inbox, sentbox
			FROM ".SFMEMBERS."
			LEFT JOIN ".SFMESSAGES." ON (".SFMEMBERS.".user_id = ".SFMESSAGES.".from_id OR ".SFMEMBERS.".user_id = ".SFMESSAGES.".to_id)
			WHERE inbox=1 OR sentbox=1
			ORDER BY display_name ASC");

	if ($users)
	{
		foreach ($users as $user)
		{
			if (($user->user_id == $user->from_id && $user->sentbox == 1) ||
			    ($user->user_id == $user->to_id && $user->inbox == 1))
			{
				if ($records[$user->user_id]['id'] == null)
				{
					$records[$user->user_id]['unread'] = 0;
					$records[$user->user_id]['inbox'] = 0;
					$records[$user->user_id]['sentbox'] = 0;
					$first = 0;
				}
				$records[$user->user_id]['id'] = $user->user_id;
				$records[$user->user_id]['name'] = $user->display_name;
				$records[$user->user_id]['pm'] = $user->pm;
				if ($user->to_id == $user->user_id && $user->message_status == 0) $records[$user->user_id]['unread']++;
				if ($user->to_id == $user->user_id && $user->inbox == 1) $records[$user->user_id]['inbox']++;
				if ($user->from_id == $user->user_id && $user->sentbox == 1) $records[$user->user_id]['sentbox']++;
			}
		}
	}

	return $records;
}

function sfa_get_user_pm_data($userid)
{
	global $wpdb;

	$records = array();
	$users = $wpdb->get_results("
			SELECT user_id, display_name, pm, to_id, from_id, message_status, inbox, sentbox
			FROM ".SFMEMBERS."
			LEFT JOIN ".SFMESSAGES." ON (".SFMEMBERS.".user_id = ".SFMESSAGES.".from_id OR ".SFMEMBERS.".user_id = ".SFMESSAGES.".to_id)
			WHERE (inbox=1 OR sentbox=1) AND (to_id=".$userid." OR from_id=".$userid.")
			ORDER BY display_name ASC");

	if ($users)
	{
		foreach ($users as $user)
		{
			if (($user->user_id == $user->from_id && $user->sentbox == 1) ||
			    ($user->user_id == $user->to_id && $user->inbox == 1))
			{
				$records[$user->user_id] = '';
				if (isset($records[$user->user_id]))
				{
					$records[$user->user_id]['unread'] = 0;
					$records[$user->user_id]['inbox'] = 0;
					$records[$user->user_id]['sentbox'] = 0;
					$first = 0;
				}
				$records[$user->user_id]['id'] = $user->user_id;
				$records[$user->user_id]['name'] = $user->display_name;
				$records[$user->user_id]['pm'] = $user->pm;
				if ($user->to_id == $user->user_id && $user->message_status == 0) $records[$user->user_id]['unread']++;
				if ($user->to_id == $user->user_id && $user->inbox == 1) $records[$user->user_id]['inbox']++;
				if ($user->from_id == $user->user_id && $user->sentbox == 1) $records[$user->user_id]['sentbox']++;
			}
		}
	}

	return $records;
}

function sfa_save_topic_status_set()
{
	global $wpdb;

	check_admin_referer('forum-adminform_topstatset', 'forum-adminform_topstatset');

	if(empty($_POST['topstatsetselect'])) return;

	$item=$_POST['item'];
	$id = $_POST['id'];
	$value = $_POST['topstatsetselect'];

	if($value == '%remove%')
	{
		$wpdb->query("UPDATE ".SFFORUMS." SET topic_status_set=0 WHERE forum_slug = '".$id."'");

		# And remlove from all topics in the forum as well
		$forumid = sf_get_forum_id($id);
		$wpdb->query("UPDATE ".SFTOPICS." SET topic_status_flag=0 WHERE forum_id=".$forumid);

	} else {
		if($item == "Group")
		{
			$wpdb->query("UPDATE ".SFFORUMS." SET topic_status_set=$value WHERE group_id=".$id);
		} else {
			$wpdb->query("UPDATE ".SFFORUMS." SET topic_status_set=$value WHERE forum_slug = '".$id."'");
		}
	}
	sfa_message(sprintf(__("Topic Status Set updated for: %s - %s", "sforum"), $item, $id));

	return;
}

function sfa_btn_size($string, $size=50)
{
	return max($size, (strlen($string) - intval(strlen($string) / 9)) * 6);
}

function sfa_rebuild_members_pm($userid='')
{
	global $wpdb;

	# grab all users from the members table
	$where = '';
	if ($userid != '')
	{
		$where = " WHERE user_id=".$userid;

	}
	$members = $wpdb->get_results("SELECT user_id, pm, admin FROM ".SFMEMBERS.$where);
	if($members)
	{
		foreach($members as $member)
		{
			if ($member->admin)
			{
				continue;
			} else {
				$canpm = 0;
				$ugs = array();
				$ugs = sf_get_user_memberships($member->user_id);
				if($ugs)
				{
					foreach($ugs as $ug)
					{
						$rids = $wpdb->get_results("SELECT permission_role FROM ".SFPERMISSIONS." WHERE usergroup_id='".$ug['usergroup_id']."'");
						foreach ($rids as $rid)
						{
							$role_actions = $wpdb->get_var("SELECT role_actions FROM ".SFROLES." WHERE role_id='".$rid->permission_role."'");
							$actions = maybe_unserialize($role_actions);
							if ($actions['Can use private messaging'] == 1)
							{
								$canpm = 1;
								break 2;
							}
						}
					}
				}
			}
			sf_update_member_item($member->user_id, 'pm', $canpm);
		}
	}

	return;
}

function sfa_paint_help($name, $helpfile, $show=true)
{
	$site=SFADMINURL."help/sf-adminhelp.php?file=".$helpfile."&amp;item=".$name."&amp;source=panels";
	$out = '';

	$out.= '<div class="sfhelplink">';
	if($show)
	{
		$out.= '<a class="sfalignright" href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true, width: 550} )">';
	}
	$out.= '<span class="sfhelpright"></span>';
	$out.= '<span class="sfhelpmiddle">';
	$out.= '<span class="sfhelpflag">&nbsp;'.__("Help", "sforum").'&nbsp;</span></span>';
	$out.= '<span class="sfhelpleft"></span>';
	if($show)
	{
		$out.= '</a>'."\n";
	}
	$out.= '</div>';

	return $out;
}

function sfa_remove_watches($tid)
{
	global $wpdb;

	$query = "SELECT topic_watches FROM ".SFTOPICS." WHERE topic_id=$tid";
	$list = $wpdb->get_var($query);
	$list = explode('@', $list);
	for ($x=0; $x<count($list); $x++)
	{
		# remove watch from member
		$topics = sf_get_member_item($list[$x], 'watches');
		if (!empty($topics))
		{
			$newlist = '';
			$topics = explode('@', $topics);
			foreach($topics as $topic)
			{
				if ($topic != $tid)
				{
					if (empty($newlist))
					{
						$newlist = $topic;
					} else {
						$newlist.= '@'.$topic;
					}
				}
			}
			sf_update_member_item($list[$x], 'watches', $newlist);
		}
	}
	$query = "UPDATE ".SFTOPICS." SET topic_watches='' WHERE topic_id=$tid";
	$wpdb->get_var($query);
}

function sfa_remove_subs($tid)
{
	global $wpdb;

	$query = "SELECT topic_subs FROM ".SFTOPICS." WHERE topic_id=$tid";
	$list = $wpdb->get_var($query);
	$list = explode('@', $list);
	for ($x=0; $x<count($list); $x++)
	{
		# remove subscriptions from member
		$topics = sf_get_member_item($list[$x], 'subscribe');
		if (!empty($topics))
		{
			$newlist = '';
			$topics = explode('@', $topics);
			foreach($topics as $topic)
			{
				if ($topic != $tid)
				{
					if (empty($newlist))
					{
						$newlist = $topic;
					} else {
						$newlist.= '@'.$topic;
					}
				}
			}
			sf_update_member_item($list[$x], 'subscribe', $newlist);
		}
	}
	$query = "UPDATE ".SFTOPICS." SET topic_subs='' WHERE topic_id=$tid";
	$wpdb->get_var($query);
}

function sfa_get_members_info($userid)
{
	global $wpdb;

	$data = sf_get_member_row($userid);

	$first = $wpdb->get_row("
			SELECT ".SFPOSTS.".forum_id, forum_name, forum_slug, ".SFPOSTS.".topic_id, topic_name, topic_slug, ".sf_zone_datetime('post_date')."
			FROM ".SFPOSTS."
			LEFT JOIN ".SFTOPICS." ON ".SFTOPICS.".topic_id = ".SFPOSTS.".topic_id
			LEFT JOIN ".SFFORUMS." ON ".SFFORUMS.".forum_id = ".SFPOSTS.".forum_id
			WHERE ".SFPOSTS.".user_id=$userid
			ORDER BY post_date ASC
			LIMIT 1");
	if ($first)
	{
		$url = '<a href="'.sf_build_url($first->forum_slug, $first->topic_slug, 1, 0).'">'.htmlentities(stripslashes($first->topic_name), ENT_COMPAT, get_bloginfo('charset')).'</a>';
		$data['first'] = $first->forum_name.'<br />'.$url .'<br />'.mysql2date(SFDATES, $first->post_date);
	} else {
		$data['first'] = __('No Posts', 'sforum');
	}

	$last = $wpdb->get_row("
			SELECT ".SFPOSTS.".forum_id, forum_name, forum_slug, ".SFPOSTS.".topic_id, topic_name, topic_slug, ".sf_zone_datetime('post_date')."
			FROM ".SFPOSTS."
			LEFT JOIN ".SFTOPICS." ON ".SFTOPICS.".topic_id = ".SFPOSTS.".topic_id
			LEFT JOIN ".SFFORUMS." ON ".SFFORUMS.".forum_id = ".SFPOSTS.".forum_id
			WHERE ".SFPOSTS.".user_id=$userid
			ORDER BY post_date DESC
			LIMIT 1");
	if ($last)
	{
		$url = '<a href="'.sf_build_url($last->forum_slug, $last->topic_slug, 1, 0).'">'.htmlentities(stripslashes($last->topic_name), ENT_COMPAT, get_bloginfo('charset')).'</a>';
		$data['last'] = $last->forum_name.'<br />'.$url .'<br />'.mysql2date(SFDATES, $last->post_date);
	} else {
		$data['last'] = __('No Posts', 'sforum');
	}

	if ($data['admin'])
	{
		$user_memberships = 'Admin';
		$status = 'admin';
		$start = 0;
	} else {
		$status = 'user';
		$start = 1;
	}

	$memberships = sfa_get_user_memberships($userid);
	if ($memberships)
	{
		foreach ($memberships as $membership)
		{
			$name = $wpdb->get_var("SELECT usergroup_name FROM ".SFUSERGROUPS." WHERE usergroup_id=".$membership['usergroup_id']);
			if ($start)
			{
				$user_memberships = $name;
				$start = 0;
			} else {
				$user_memberships.= ', '.$name;
			}
		}
	} else if ($start) {
		$user_memberships = 'No Memberships';
	}
	$data['memberships'] = $user_memberships;
	$data['rank'] = sf_render_usertype($status, $userid, $data['posts']);
	return $data;
}

function sfa_get_database()
{
	global $wpdb;

	# retrieve group and forum records
	$records = $wpdb->get_results(
			"SELECT ".SFGROUPS.".group_id, group_name, forum_id, forum_name, topic_count
			 FROM ".SFGROUPS."
			 LEFT JOIN ".SFFORUMS." ON ".SFGROUPS.".group_id = ".SFFORUMS.".group_id
			 ORDER BY group_seq, forum_seq;");

	# rebuild into an array
	$groups=array();
	$gindex=-1;
	$findex=0;
	if($records)
	{
		foreach($records as $record)
		{
			$groupid=$record->group_id;
			$forumid=$record->forum_id;

			if($gindex == -1 || $groups[$gindex]['group_id'] != $groupid)
			{
				$gindex++;
				$findex=0;
				$groups[$gindex]['group_id']=$record->group_id;
				$groups[$gindex]['group_name']=stripslashes($record->group_name);
			}
			if(isset($record->forum_id))
			{
				$groups[$gindex]['forums'][$findex]['forum_id']=$record->forum_id;
				$groups[$gindex]['forums'][$findex]['forum_name']=stripslashes($record->forum_name);
				$groups[$gindex]['forums'][$findex]['topic_count']=$record->topic_count;
				$findex++;
			}
		}
	} else {
		$records = sf_get_groups_all(false, false);
		if($records)
		{
			foreach($records as $record)
			{
				$groups[$gindex]['group_id']=$record->group_id;
				$groups[$gindex]['group_name']=stripslashes($record->group_name);
				$groups[$gindex]['group_desc']=stripslashes($record->group_desc);
				$gindex++;
			}
		}
	}
	return $groups;
}
?>