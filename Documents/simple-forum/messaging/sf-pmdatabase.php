<?php
/*
Simple:Press Forum
Main PM database routines
$LastChangedDate: 2009-01-04 17:19:22 +0000 (Sun, 04 Jan 2009) $
$Rev: 1131 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# ------------------------------------------------------------------
# sf_get_pm_inbox()
#
# Select all Inbox messages for current user
#	$userid:		Current User
# ------------------------------------------------------------------
function sf_get_pm_inbox($userid)
{
	global $wpdb;

	# Get sorted lst of pm inbox titles first
	$titles = $wpdb->get_results(
			"SELECT DISTINCT title, message_slug
			 FROM ".SFMESSAGES."
			 LEFT JOIN ".SFMEMBERS." ON ".SFMESSAGES.".from_id = ".SFMEMBERS.".user_id
			 WHERE to_id = ".$userid." AND inbox=1
			 ORDER BY sent_date DESC");
	if(!$titles) return;

	# Now grab the full records
	$pms = $wpdb->get_results(
			"SELECT SQL_CALC_FOUND_ROWS message_id, sent_date, from_id, to_id, title, message_status, inbox, sentbox, is_reply, message_slug, display_name
			 FROM ".SFMESSAGES."
			 LEFT JOIN ".SFMEMBERS." ON ".SFMESSAGES.".from_id = ".SFMEMBERS.".user_id
			 WHERE to_id = ".$userid." AND inbox=1
			 ORDER BY message_id ASC");

	return sf_sort_pms($titles, $pms);
}

# ------------------------------------------------------------------
# sf_get_pm_sentbox()
#
# Select all Sentbox messages for current user
#	$userid:		Current User
# ------------------------------------------------------------------
function sf_get_pm_sentbox($userid)
{
	global $wpdb;

	# Get sorted lst of pm inbox titles first
	$titles = $wpdb->get_results(
			"SELECT DISTINCT title, message_slug
			 FROM ".SFMESSAGES."
			 LEFT JOIN ".SFMEMBERS." ON ".SFMESSAGES.".from_id = ".SFMEMBERS.".user_id
			 WHERE from_id = ".$userid." AND sentbox=1
			 ORDER BY sent_date DESC");

	if(!$titles) return;

	# Now grab the full records
	$pms = $wpdb->get_results(
			"SELECT SQL_CALC_FOUND_ROWS message_id, sent_date, from_id, to_id, title, message_status, inbox, sentbox, is_reply, message_slug, display_name
			 FROM ".SFMESSAGES."
			 LEFT JOIN ".SFMEMBERS." ON ".SFMESSAGES.".to_id = ".SFMEMBERS.".user_id
			 WHERE from_id = ".$userid." AND sentbox=1
			 ORDER BY message_id ASC");

	return sf_sort_pms($titles, $pms);
}

# ------------------------------------------------------------------
# sf_sort_pms()
#
# Sort query results into required object format
#	$pmlist:		Data Query results
# ------------------------------------------------------------------
function sf_sort_pms($titlelist, $pmlist)
{
	$messages = array();

	$row = 0;
	foreach($titlelist as $this_title)
	{
		$title = $this_title->title;
		$messages[$row]['slug'] = $this_title->message_slug;
		$messages[$row]['title'] = stripslashes($title);

		$index = 0;
		foreach($pmlist as $pm)
		{
			if($pm->message_slug == $this_title->message_slug)
			{
				$messages[$row]['messages'][$index]['message_id'] = $pm->message_id;
				$messages[$row]['messages'][$index]['sent_date'] = $pm->sent_date;
				$messages[$row]['messages'][$index]['from_id'] = $pm->from_id;
				$messages[$row]['messages'][$index]['to_id'] = $pm->to_id;
				$messages[$row]['messages'][$index]['message_status'] = $pm->message_status;
				$messages[$row]['messages'][$index]['inbox'] = $pm->inbox;
				$messages[$row]['messages'][$index]['sentbox'] = $pm->sentbox;
				$messages[$row]['messages'][$index]['is_reply'] = $pm->is_reply;
				$messages[$row]['messages'][$index]['display_name'] = stripslashes($pm->display_name);
				$index++;
			}
		}
		$row++;
	}

	return $messages;
}

# ------------------------------------------------------------------
# sf_create_pmuser_select()
#
# Populate the option list of PM users
#	$user_id:		If passed this user is pre-selected
#	$type:			Set to 'members' or 'buddies'
# ------------------------------------------------------------------
function sf_create_pmuser_select($userid = -1, $type, $action="all")
{
	global $current_user, $wpdb;

	$out = '';

	if($type == 'members')
	{
		if($action == 'all')
		{
			$users = $wpdb->get_results("SELECT user_id AS ID, display_name, admin, moderator FROM ".SFMEMBERS." WHERE pm = 1 ORDER BY admin DESC, moderator DESC, display_name");
		} else {
			$where = 'AND display_name LIKE "'.$action.'%"';
			$users = $wpdb->get_results("SELECT user_id AS ID, display_name, admin, moderator FROM ".SFMEMBERS." WHERE pm = 1 ".$where." ORDER BY admin DESC, moderator DESC, display_name");
		}

		$groups = 3;
	} else {
		$users = sf_get_pm_buddies();
		$groups = 1;
	}
	if($users)
	{
		if ($current_user->forumadmin && $type == 'members')
		{
			$out ='<option value="0">'.__("All Members", "sforum").'</option>'."\n";
		}

		for($x=1; $x<($groups+1); $x++)
		{
			switch($x)
			{
				case 1:
				if($type=='members' ? $tag=__('Administrators', 'sforum') : $tag=__('Buddies', 'sforum'));
				break;

				case 2:
				$tag = __('Moderators', 'sforum');
				break;

				case 3:
				$tag = __('Members', 'sforum');
				break;
			}

			if($action != 'all')
			{
				$tag .= ' ('.sprintf(__("starting '%s'", "sforum"), $action).') -';
			}

			$gstart = '<optgroup class="sflist" label="'.$tag.'">'."\n";
			$gclose = '';

			foreach($users as $user)
			{
				if($user->ID != $current_user->ID)
				{
					$donext = true;
					if($type=='members')
					{
						if(($x==1) && (!$user->admin)) $donext=false;
						if(($x==2) && (!$user->moderator)) $donext=false;
						if(($x==3) && ($user->admin || $user->moderator)) $donext=false;
					}

					if($donext)
					{
						if($user->ID == $userid)
						{
							$default = 'selected="selected" ';
						} else {
							$default = null;
						}
						$out.= $gstart;
						$out.='<option '.$default.'value="'.$user->ID.'">'.stripslashes(attribute_escape($user->display_name)).'</option>'."\n";
						$default='';
						$gstart = '';
						$gclose = '</optgroup>';
					}
				}
			}

			$out.= $gclose;
		}
	}
	return $out;
}


function sf_get_pm_inbox_idlist($userid)
{
	global $wpdb;
	return $wpdb->get_results("SELECT message_id FROM ".SFMESSAGES." WHERE to_id = ".$userid." AND inbox=1");
}


function sf_get_pm_inbox_new_count($userid)
{
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(*) FROM ".SFMESSAGES." WHERE to_id = ".$userid." AND inbox=1 AND message_status=0");
}


function sf_get_pm_sentbox_idlist($userid)
{
	global $wpdb;
	return $wpdb->get_results("SELECT message_id FROM ".SFMESSAGES." WHERE from_id = ".$userid." AND sentbox=1");
}

function sf_get_pm_boxcount($userid)
{
	global $wpdb;
	return $wpdb->get_var("SELECT COUNT(message_id) AS cnt FROM ".SFMESSAGES." WHERE (to_id = ".$userid." AND inbox=1) OR (from_id = ".$userid." AND sentbox=1);");
}

function sf_get_pm_buddies()
{
	global $wpdb, $current_user;

	$buddylist = array();
	$buddies = 	sf_get_member_item($current_user->ID, 'buddies');

	if($buddies)
	{
		$x=0;
		foreach($buddies as $buddy)
		{
			$buddylist[$x]->ID = $buddy;
			$buddylist[$x]->display_name = stripslashes(sf_get_member_item($buddy, 'display_name'));
			$x++;
		}
	}
	return $buddylist;
}


function sf_add_buddy($id)
{
	global $current_user;

	# Put member into buddy list if not there
	$buddies = array();
	$buddies = sf_get_member_item($current_user->ID, 'buddies');

	if($buddies)
	{
		if(!in_array($id, $buddies))
		{
			$buddies[] = $id;
		}
	} else {
		$buddies[] = $id;
	}

	sf_update_member_item($current_user->ID, 'buddies', $buddies);
	update_sfnotice('sfmessage', '0@'.__("New Buddy Added", "sforum"));
	return;
}

function sf_remove_buddy($id)
{
	global $current_user;

	$buddies = array();
	$ewbuddies = array();
	$buddies = sf_get_member_item($current_user->ID, 'buddies');
	if($buddies)
	{
		foreach($buddies as $buddy)
		{
			if($buddy != $id) $newbuddies[]=$buddy;
		}
		sf_update_member_item($current_user->ID, 'buddies', $newbuddies);
		update_sfnotice('sfmessage', '0@'.__("Buddy Removed", "sforum"));
	}
	return;
}

function sf_is_buddy($id)
{
	global $current_user;

	# is member ($id) in current users buddy list?
	$buddies = array();
	$buddies = sf_get_member_item($current_user->ID, 'buddies');
	if($buddies)
	{
		if(in_array($id, $buddies))
		{
			return true;
		}
	} else {
		return false;
	}
}

?>