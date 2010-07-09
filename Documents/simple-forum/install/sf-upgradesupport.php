<?php
/*
Simple:Press Forum
Install & Upgrade Support Routines
$LastChangedDate: 2009-03-03 17:11:44 +0000 (Tue, 03 Mar 2009) $
$Rev: 1517 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

include_once(SF_PLUGIN_DIR.'/sf-slugs.php');

# = UPGRADE DB FUNCTION ===================================

function sf_upgrade_database($table_name, $column_name, $create_ddl)
{
	global $wpdb;
	foreach ($wpdb->get_col("DESC $table_name", 0) as $column )
	{
		if ($column == $column_name)
		{
			return true;
		}
    }
	# didn't find it try to create it.
    $q = $wpdb->query($create_ddl);

	# we cannot directly tell that whether this succeeded!
	foreach ($wpdb->get_col("DESC $table_name", 0) as $column )
	{
		if ($column == $column_name)
		{
			return true;
		}
	}
	die(sprintf(__("DATABASE ERROR: Unable to ALTER the %s to create new column %s", "sforum"), $table_name, $column));
}

function sf_charset()
{
	global $wpdb;

	$charset_collate = '';

	if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') )
	{
		if ( ! empty($wpdb->charset) )
		{
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty($wpdb->collate) )
		{
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}

	return $charset_collate;
}

# Called by 1.6 to clear up previous deletion orphans
function sf_check_data_integrity()
{
	global $wpdb;

	$topiclist = array();
	$postlist = array();

	# to be run against a 1.5 install to clean up orphaned posts
	# Step 1: Loop through topics in case forum is gone and remove
	$topics = $wpdb->get_results("SELECT topic_id, forum_id FROM ".SFTOPICS);
	if($topics)
	{
		foreach($topics as $topic)
		{
			$test=$wpdb->get_col("SELECT forum_id FROM ".SFFORUMS." WHERE forum_id=".$topic->forum_id);
			if(!$test)
			{
				$topiclist[]=$topic->topic_id;
			}
		}
		if($topiclist)
		{
			foreach($topiclist as $topic)
			{
				$wpdb->query("DELETE FROM ".SFTOPICS." WHERE topic_id=".$topic);
			}
		}
	}

	# Step 2: Loop through posts in case topic is gone and remove
	$posts = $wpdb->get_results("SELECT post_id, topic_id FROM ".SFPOSTS);
	if($posts)
	{
		foreach($posts as $post)
		{
			$test=$wpdb->get_col("SELECT topic_id FROM ".SFTOPICS." WHERE topic_id=".$post->topic_id);
			if(!$test)
			{
				$postlist[]=$post->post_id;
			}
		}
		if($postlist)
		{
			foreach($postlist as $post)
			{
				$wpdb->query("DELETE FROM ".SFPOSTS." WHERE post_id=".$post);
			}
		}
	}
	return;
}

# Called by 1.7 to re-route subscriptions from usermeta to topics
function sf_rebuild_subscriptions()
{
	global $wpdb;

	# Build a list of users with subscribe set
	$users = $wpdb->get_col("SELECT user_id FROM ".SFUSERMETA." WHERE meta_key='".$wpdb->prefix."sfsubscribe'");
	if($users)
	{
		# clear out the old sfsubcribe values ready for the new
		$wpdb->query("DELETE FROM ".SFUSERMETA." WHERE meta_key='".$wpdb->prefix."sfsubscribe'");

		foreach($users as $user)
		{
			# now build the list of topics into which each user has posted
			$topics = $wpdb->get_col("SELECT DISTINCT topic_id FROM ".SFPOSTS." WHERE user_id=".$user);
			if($topics)
			{
				foreach($topics as $topic)
				{
					sf_save_subscription($topic, $user, false);
				}
			}
		}
	}
	return;
}

# Called by 2.0 to clean up the topic subs lists where duplicates have crept in
function sf_clean_topic_subs()
{
	global $wpdb;

	# build list of topics with subscriptions
	$topics = $wpdb->get_results("SELECT topic_id, topic_subs FROM ".SFTOPICS." WHERE topic_subs IS NOT NULL;");
	if(!$topics) return;

	foreach($topics as $topic)
	{
		$nvalues = array();
		$cvalues = explode('@', $topic->topic_subs);
		$nvalues[0] = $cvalues[0];
		foreach($cvalues as $cvalue)
		{
			$notfound = true;
			foreach($nvalues as $nvalue)
			{
				if($nvalue == $cvalue) $notfound = false;
			}
			if($notfound) $nvalues[]=$cvalue;
		}
		$nvaluelist = implode('@', $nvalues);
		$wpdb->query("UPDATE ".SFTOPICS." SET topic_subs='".$nvaluelist."' WHERE topic_id=".$topic->topic_id);
	}
	return;
}

function sf_relocate_avatars()
{
	$success = 0;
	$newpath = WP_CONTENT_DIR . '/forum-avatars';
	$oldpath = SF_PLUGIN_DIR . '/styles/avatars';

	# check if new folder does not exist - which it shouldn't!
	if(!is_dir($newpath))
	{
		if(!is_writable(WP_CONTENT_DIR) || !($dir = mkdir($newpath, 0777)))
		{
			$success = 1;
			return $success;
		}
		if (!is_writable($newpath))
		{
			$success = 2;
			return $success;
		}
		if(is_dir($newpath))
		{
			$avlist = opendir($oldpath);
			while (false !== ($file = readdir($avlist)))
			{
				if ($file != "." && $file != "..")
				{
					if(!file_exists($newpath.'/'.$file))
					{
						if(@rename($oldpath.'/'.$file, $newpath.'/'.$file) == false)
						{
							$success = 3;
							break;
						}
					}
				}
			}
			closedir($avlist);
			@rmdir($oldpath);
		}
	}
	return $success;
}

# Called by 2.1 to correct old timestamp in usermeta (sflast)
function sf_correct_sflast()
{
	global $wpdb;

	$sql = "UPDATE ".SFUSERMETA." SET meta_value=now() WHERE meta_key = '".$wpdb->prefix."sflast' AND meta_value < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);";
	$wpdb->query($sql);
	return;
}

# Called by 2.1 Patch 2 to pre-create last visited date for all existing users who don't have one - Corrects the zero problem
function sf_precreate_sflast()
{
	global $wpdb;

	$users = $wpdb->get_results("SELECT ID FROM ".SFUSERS);
	if($users)
	{
		foreach($users as $user)
		{
			$check = $wpdb->get_var("SELECT umeta_id FROM ".SFUSERMETA." WHERE meta_key='".$wpdb->prefix."sflast' AND user_id=".$user->ID);
			if(!$check)
			{
				sf_set_last_visited($user->ID);
			}
		}
	}
	return;
}

# Called by 3.0 to create forum and topic slugs
function sf_create_slugs()
{
	global $wpdb;

	# forums
	$records=$wpdb->get_results("SELECT forum_id, forum_name, forum_slug FROM ".SFFORUMS);
	if($records)
	{
		foreach($records as $record)
		{
			$title = sf_create_slug($record->forum_name, 'forum');
			if(empty($title))
			{
				$title = 'forum-'.$record->forum_id;
			}
			$wpdb->query("UPDATE ".SFFORUMS." SET forum_slug='".$title."' WHERE forum_id=".$record->forum_id);
		}
	}

	# topics
	$records=$wpdb->get_results("SELECT topic_id, topic_name, topic_slug FROM ".SFTOPICS);
	if($records)
	{
		foreach($records as $record)
		{
			$title = sf_create_slug($record->topic_name, 'topic');
			if(empty($title))
			{
				$title = 'topic-'.$record->topic_id;
			}
			$wpdb->query("UPDATE ".SFTOPICS." SET topic_slug='".$title."' WHERE topic_id=".$record->topic_id);
		}
	}
	return;
}

# Called by 3 to ensure all users have a display name set
function sf_check_all_display_names()
{
	global $wpdb;

	$users = $wpdb->get_results("SELECT ID, user_login, display_name FROM ".SFUSERS." WHERE display_name=''");
	if($users)
	{
		foreach($users as $user)
		{
			$wpdb->query("UPDATE ".SFUSERS." SET display_name='".$user->login_name."' WHERE ID=".$user->ID);
		}
	}
	return;
}

# Called by 3.0 to set up all users into default usergroups
# And then set all 3 usergroups to all forums by default
function sf_setup_usergroup_data($membergroup, $moderatorgroup, $upgrade, $keys)
{
	global $wpdb, $current_user;

	# if upgrade check if any moderators
	$modusers = '';
	if($upgrade) $modusers = get_option('sfmodusers');
	if(!empty($modusers))
	{
		$modusers = explode(';',get_option('sfmodusers'));
	}

	# get the list of users and do the stuff
	$userlist = $wpdb->get_results("SELECT ID FROM ".SFUSERS." ORDER BY display_name ASC;");
	if($userlist)
	{
		foreach($userlist as $user)
		{
			# check it's not the admin
			if($user->ID != $current_user->ID)
			{
				$target = $membergroup;
				# is user a moderator?
				if(!empty($modusers))
				{
					if(in_array($user->ID, $modusers)) $target = $moderatorgroup;
				}
				$memberships = get_usermeta($user->ID, 'sfusergroup');
				$memberships[] = $target;
				update_usermeta($user->ID, 'sfusergroup', $memberships);
			}
		}
	}

	# Now to assign all 3 default usergroups to all forums
	if(($keys) && ($upgrade))
	{
		$forums = $wpdb->get_results("SELECT forum_id FROM ".SFFORUMS.";");
		if($forums)
		{
			foreach($forums as $forum)
			{
				for($x=0; $x<count($keys); $x++)
				{
					$group = $keys[$x]['usergroup'];
					$perm  = $keys[$x]['permission'];

					$sql ="INSERT INTO ".SFPERMISSIONS." (forum_id, usergroup_id, permission_role) ";
					$sql.="VALUES (".$forum->forum_id.", ".$group.", ".$perm.");";
					$wpdb->query($sql);
				}
			}
		}
	}
	return;
}

# called by 3.1 (?) to build new last post columns, topic post count and post index
function sf_build_lastposts()
{
	global $wpdb;

	$forums = sf_get_forums_all(true);
	if($forums)
	{
		foreach($forums as $forum)
		{
			sf_build_forum_index($forum->forum_id);
		}
	}

	$topics = sf_get_topics_all(true);
	if($topics)
	{
		foreach($topics as $topic)
		{
			sf_build_post_index($topic->topic_id, $topic->topic_slug);
		}
	}
	return;
}

# called by 3.1 (and initial install) to build members table
# should also now work correctly for individual wpmu blogs
# sends $editor_column as install and upgrade have different column names (don't ask!)
function sf_build_members_table($editor_column, $type)
{
	global $wpdb, $current_user;

	# extract the table prefix (for MU purposes)
	if(function_exists("wpmu_create_blog"))
	{
		# this is an MU install
		$tname = array();
		$tname = explode('_', $wpdb->prefix);
		$tprefix='';
		if(count($tname) == 2)
		{
			$tprefix = $tname[0].'_';
		} else {
			for($x=0; $x<count($tname)-2; $x++)
			{
				$tprefix.=$tname[$x].'_';
			}
		}
	} else {
		# standard WP system
		$tprefix = $wpdb->prefix;
	}

	# select all users
	$members = $wpdb->get_results(
		"SELECT ID, display_name, user_login FROM ".$tprefix."users
		 RIGHT JOIN ".$tprefix."usermeta ON ".$tprefix."users.ID = ".$tprefix."usermeta.user_id
		 WHERE meta_key = '".$wpdb->prefix."capabilities'
		 ORDER BY ID;");

	if($members)
	{
		# grab the user groups so we can ensure the users settings are coprrect and groups exist
		$ugs = $wpdb->get_col("SELECT usergroup_id FROM ".SFUSERGROUPS);
		foreach($members as $member)
		{
			# Check ID exists and is not zero
			if(is_numeric($member->ID) && $member->ID > 0)
			{
				$usergroups = array();
				$usergroups = get_usermeta($member->ID, 'sfusergroup');

				# user group handling - check groups exist
				$newgrouplist=array();
				if($usergroups)
				{
					foreach($usergroups as $group)
					{
						if(in_array($group, $ugs))
						{
							$newgrouplist[] = (string) $group;
						}
					}
				} else {
					$newgrouplist[] = (string) get_option('sfdefgroup');
				}
				$usergroups = serialize($newgrouplist);

				# admins dont get user groups
				# forum admin not set up yet for installs
				if ($type == 'upgrade')
				{
					if (sf_is_forum_admin($member->ID)) $usergroups = '';
				} else {
					if ($current_user->ID == $member->ID) $usergroups = '';
				}

				# remaining data items
				$display_name = stripslashes($member->display_name);
				if(empty($display_name))
				{
					$display_name = stripslashes($member->user_login);
				}
				$display_name = addslashes($display_name);

				$buddies = array();
				$avatar     = get_usermeta($member->ID, 'sfavatar');
				$signature  = attribute_escape(get_usermeta($member->ID, 'signature'));
				$sigimage   = attribute_escape(get_usermeta($member->ID, 'sigimage'));
				$posts      = get_usermeta($member->ID, 'sfposts');
				$lastvisit  = get_usermeta($member->ID, 'sflast');
				$subscribe  = get_usermeta($member->ID, 'sfsubscribe');
				$buddies    = get_usermeta($member->ID, 'sfbuddies');
				$pm         = sf_get_user_pm_status($member->ID, $newgrouplist);
				$moderator	= sf_get_user_mod_status($member->ID, $newgrouplist);

				$signature = wp_filter_kses(trim($signature));
				$sigimage = wp_specialchars(sf_filter_nohtml_kses(trim($sigimage)));

				$buddies    = serialize($buddies);
				if(!$posts) $posts = '0';

				$editor_setting = get_usermeta($member->ID, 'sfuse_quicktags');
				if(empty($editor_setting))
				{
					if($editor_column == 'quicktags') $editor_setting = 0;
					if($editor_column == 'editor') $editor_setting = 1;
				}

				if ($type == 'upgrade')
				{
					$sql ="INSERT INTO ".SFMEMBERS." (user_id, display_name, pm, moderator, {$editor_column}, usergroups, avatar, signature, sigimage, posts, lastvisit, subscribe, buddies) ";
					$sql.="VALUES ({$member->ID}, '{$display_name}', {$pm}, {$moderator}, {$editor_setting}, '{$usergroups}', '{$avatar}', '{$signature}', '{$sigimage}', {$posts}, '{$lastvisit}', '{$subscribe}', '{$buddies}');";
				} else {
					$sql ="INSERT INTO ".SFMEMBERS." (user_id, display_name, pm, moderator, {$editor_column}, avatar, signature, sigimage, posts, lastvisit, subscribe, buddies, newposts, checktime, admin, watches, posts_rated, admin_options) ";
					$sql.="VALUES ({$member->ID}, '{$display_name}', {$pm}, {$moderator}, {$editor_setting}, '{$avatar}', '{$signature}', '{$sigimage}', {$posts}, now(), '{$subscribe}', '{$buddies}', '', now(), 0, '', '', '');";

					$memberships = unserialize($usergroups);
					if ($memberships)   # will be empty for admin
					{
						foreach ($memberships as $membership)
						{
							sfa_add_membership($membership, $member->ID);
						}
					}
				}
				$wpdb->query($sql);

				# now remove the old userfmeta entries for the current member
				$optionlist = array("sfavatar", "sfposts", "sfsubscribe", "sflast", "sfnewposts", "sfchecktime", "sfbuddies", "sfusergroup", "signature", "sigimage", "sfuse_quicktags");
				foreach($optionlist as $option)
				{
					$wpdb->query("DELETE FROM ".$tprefix."usermeta WHERE meta_key='".$option."' AND user_id=".$member->ID.";");
				}
			}
		}
	}
	return;
}

# support function
function sf_get_user_pm_status($user_id, $usergroups)
{
	global $wpdb;

	if (sf_is_forum_admin($user_id)) return '1';
	if (empty($usergroups)) return '0';

	foreach ($usergroups as $usergroup)
	{
		$rids = $wpdb->get_results("SELECT permission_role FROM ".SFPERMISSIONS." WHERE usergroup_id='".$usergroup."'");
		foreach ($rids as $rid) {
			$role_actions = $wpdb->get_var("SELECT role_actions FROM ".SFROLES." WHERE role_id='".$rid->permission_role."'");
			$actions = maybe_unserialize($role_actions);
			if ($actions['Can use private messaging'] == 1)
			{
				return '1';
			}
		}
	}
	return '0';
}

# support function
function sf_get_user_mod_status($user_id, $usergroups)
{
    global $wpdb;

	if (sf_is_forum_admin($user_id)) return '1';
	if (empty($usergroups)) return '0';

	foreach ($usergroups as $usergroup)
	{
		$mod = $wpdb->get_var("SELECT usergroup_is_moderator FROM ".SFUSERGROUPS." WHERE usergroup_id = ".$usergroup);
		if($mod) return '1';
	}
	return '0';
}

# support function for adding new role.  use firt two params for fixed value.  third param will override second
function sf_upgrade_add_new_role($newaction, $perm, $limit_access=false, $mods_only=false)
{
	global $wpdb;

	$roles = $wpdb->get_results("SELECT * FROM ".SFROLES." ORDER BY role_id");
	if ($roles)
	{
		foreach ($roles as $role)
		{
			if ($limit_access)
			{
				$perm = 1;
				if ($role->role_name == 'No Access' || $role->role_name == 'Read Only Access')
				{
					$perm = 0;
				}
			}
			if ($mods_only)
			{
				$perm = 0;
				if ($role->role_name == 'Moderators')
				{
					$perm = 1;
				}
			}
			$actions = unserialize($role->role_actions);
			$actions[$newaction] = $perm;
			$actions = maybe_serialize($actions);
			$sql = "UPDATE ".SFROLES." SET ";
			$sql.= 'role_name="'.$role->role_name.'", ';
			$sql.= 'role_desc="'.$role->role_desc.'", ';
			$sql.= 'role_actions="'.$wpdb->escape($actions).'" ';
			$sql.= "WHERE role_id=".$role->role_id.";";
			$wpdb->query($sql);
		}
	}
}

# function to set up default group permissions
function sf_group_def_perms()
{
	global $wpdb;

	# grab the "default" permissions if they exist
	$noaccess = $wpdb->get_var("SELECT role_id FROM ".SFROLES." WHERE role_name='No Access'");
	if (!$noaccess) $noaccess = -1;
	$readonly = $wpdb->get_var("SELECT role_id FROM ".SFROLES." WHERE role_name='Read Only Access'");
	if (!$readonly) $readonly = -1;
	$standard = $wpdb->get_var("SELECT role_id FROM ".SFROLES." WHERE role_name='Standard Access'");
	if (!$standard) $standard = -1;
	$moderator = $wpdb->get_var("SELECT role_id FROM ".SFROLES." WHERE role_name='Moderator Access'");
	if (!$moderator) $moderator = -1;

	$usergroups = $wpdb->get_results("SELECT * FROM ".SFUSERGROUPS);
	$groups = $wpdb->get_results("SELECT group_id FROM ".SFGROUPS);
	if ($groups && $usergroups)
	{
		foreach ($groups as $group)
		{
			foreach ($usergroups as $usergroup)
			{
				if ($usergroup->usergroup_name == 'Guests')
				{
					$rid = $readonly;
				} else if ($usergroup->usergroup_name == 'Members')
				{
					$rid = $standard;
				} else if ($usergroup->usergroup_name == 'Moderators')
				{
					$rid = $moderator;
				} else {
					$rid = $noaccess;
				}
				$wpdb->query("
					INSERT INTO ".SFDEFPERMISSIONS."
					(group_id, usergroup_id, permission_role)
					VALUES
					($group->group_id, $usergroup->usergroup_id, $rid)");
			}
		}
	}
}

# Called by 3.2 to create new forum-smileys folder and content
function sf_relocate_smileys()
{
	$success = 0;
	$newpath = WP_CONTENT_DIR . '/forum-smileys';
	$oldpath = SF_PLUGIN_DIR . '/styles/smileys';

	# check if new folder does not exist - which it shouldn't!
	if(!is_dir($newpath))
	{
		if(!is_writable(WP_CONTENT_DIR) || !($dir = mkdir($newpath, 0777)))
		{
			$success = 1;
			return $success;
		}
		if (!is_writable($newpath))
		{
			$success = 2;
			return $success;
		}
		if(is_dir($newpath))
		{
			$avlist = opendir($oldpath);
			while (false !== ($file = readdir($avlist)))
			{
				if ($file != "." && $file != "..")
				{
					if(!file_exists($newpath.'/'.$file))
					{
						if(@rename($oldpath.'/'.$file, $newpath.'/'.$file) == false)
						{
							$success = 3;
							break;
						}
					}
				}
			}
			closedir($avlist);
			@rmdir($oldpath);
		}
	}
	return $success;
}

# Called by 3.2 to build smiley array
function sf_build_base_smileys()
{
	$smileys = array(
	"Confused" => 	array (	0 => "sf-confused.gif",		1 => ":???:",),
	"Cool" =>		array (	0 => "sf-cool.gif",			1 => ":cool:"),
	"Cry" =>		array (	0 => "sf-cry.gif",			1 => ":cry:",),
	"Embarassed" =>	array (	0 => "sf-embarassed.gif",	1 => ":oops:",),
	"Frown" =>		array (	0 => "sf-frown.gif",		1 => ":frown:",),
	"Kiss" =>		array (	0 => "sf-kiss.gif",			1 => ":kiss:",),
	"Laugh" =>		array (	0 => "sf-laugh.gif",		1 => ":lol:",),
	"Smile" =>		array (	0 => "sf-smile.gif",		1 => ":smile:",),
	"Surprised" =>	array (	0 => "sf-surprised.gif",	1 => ":eek:",),
	"Wink" =>		array (	0 => "sf-wink.gif",			1 => ":wink:",),
	"Yell" =>		array (	0 => "sf-yell.gif",			1 => ":yell:",)
	);

	sf_add_sfmeta('smileys', 'smileys', serialize($smileys));

	return;
}

# Called by 3.2 to add tinymce editor toolbar/plugin arrays
function sf_build_tinymce_toolbar_arrays()
{
	$tbar_buttons=array('bold','italic','underline','|','bullist','numlist','|','blockquote','outdent','indent','|','link','unlink','|','undo','redo','forecolor','charmap','|','image');
	$tbar_buttons_add=array('media','|','ddcode','|','emotions','|','pastetext','pasteword','|','selectall','preview','code','|','spellchecker');
	$tbar_plugins=array('inlinepopups','safari','media','preview','emotions','ddcode','spellchecker','paste');

	$tinymce_toolbar = array();
	$tinymce_toolbar['tbar_buttons'] = $tbar_buttons;
	$tinymce_toolbar['tbar_buttons_add'] = $tbar_buttons_add;
	$tinymce_toolbar['tbar_plugins'] = $tbar_plugins;

	sf_add_sfmeta('tinymce_toolbar', 'default', serialize($tinymce_toolbar));
	sf_add_sfmeta('tinymce_toolbar', 'user', serialize($tinymce_toolbar));

	return;
}

# Called by 4.0 and install to build memberships table
function sf_build_memberships_table()
{
	global $wpdb;

	$users = $wpdb->get_results("SELECT user_id, usergroups FROM ".SFMEMBERS);
	if ($users)
	{
		foreach ($users as $user)
		{
			$memberships = maybe_unserialize($user->usergroups);
			if ($memberships)
			{
				for ($x=0; $x<count($memberships); $x++)
				{
					$sql ="INSERT INTO ".SFMEMBERSHIPS." (user_id, usergroup_id) ";
					$sql.="VALUES ('".$user->user_id."', '".$memberships[$x]."');";
					$wpdb->query($sql);
				}
			}
		}
	}
}

# Called by 4.0 to create pm message slugs
function sf_create_message_slugs()
{
	global $wpdb;

	# remove all single quotes
	$messages = $wpdb->get_results("SELECT message_id, title FROM ".SFMESSAGES);
	if($messages)
	{
		foreach($messages as $message)
		{
			$title = stripslashes($message->title);
			$title = str_replace ("'", "", $title);
			$wpdb->query("UPDATE ".SFMESSAGES." SET title = '".$title."' WHERE message_id=".$message->message_id);
		}
	}

	# perform slug creation
	$found = true;
	while($found)
	{
		$message = sf_grab_slugless_messages();
		if($message)
		{
			$slug = sf_create_slug($message->title, 'pm');
			# if not created force change of title
			if($slug)
			{
				$wpdb->query("UPDATE ".SFMESSAGES." SET message_slug = '".$slug."' WHERE title='".$message->title."';");
			} else {
				$wpdb->query("UPDATE ".SFMESSAGES." SET title = 'Untitled' WHERE message_id = ".$message->message_id);
			}
		} else {
			$found = false;
		}
	}

	return;
}

function sf_grab_slugless_messages()
{
	global $wpdb;

	return $wpdb->get_row("SELECT * FROM ".SFMESSAGES." WHERE message_slug='' LIMIT 1;");
}

# Called by 4.0.1 to add blockquote to tinymce toolbar
function sf_update_tmtoolbar_blockquote()
{
	$tbrow = array();
	$tbrow[0]='default';
	$tbrow[1]='user';
	foreach($tbrow as $tb)
	{
		$tbmeta = sf_get_sfmeta('tinymce_toolbar', $tb);
		$buttons = unserialize($tbmeta[0]['meta_value']);
		$newbuttons = array();

		$found = false;
		
		# double check not already there...
		foreach($buttons['tbar_buttons'] as $button)
		{
			if($button == 'blockquote') $found=true;
		}
		
		if(!$found)
		{
			foreach($buttons['tbar_buttons'] as $button)
			{
				if($button == 'outdent')
				{
					$newbuttons[]='blockquote';
				}
				$newbuttons[]=$button;
			}
			$buttons['tbar_buttons']=$newbuttons;
			sf_update_sfmeta('tinymce_toolbar', $tb, serialize($buttons), $tbmeta[0]['meta_id']);
		}
	}
	return;
}

function sf_update_membership_cleanup()
{
	global $wpdb;

	#remove any duplicate memberships
	$memberships = $wpdb->get_results("SELECT * FROM ".SFMEMBERSHIPS);
	if ($memberships)
	{
		$test = array();
		foreach ($memberships as $membership)
		{
			if ($test[$membership->usergroup_id][$membership->user_id] == 1)
			{
				$wpdb->query("DELETE FROM ".SFMEMBERSHIPS." WHERE membership_id=".$membership->membership_id);
			} else {
				$test[$membership->usergroup_id][$membership->user_id] = 1;
			}
		}
	}
}
?>