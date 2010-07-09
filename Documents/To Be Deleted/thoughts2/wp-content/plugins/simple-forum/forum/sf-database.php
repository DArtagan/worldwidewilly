<?php
/*
Simple:Press Forum
Main database routines
$LastChangedDate: 2009-05-30 16:11:57 +0100 (Sat, 30 May 2009) $
$Rev: 1961 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# ******************************************************************
# GROUP/FORUM VIEW AND GENERAL DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_combined_groups_and_forums($groupid)
#
# Grabs all groups and forums. Note that the group data is repeated.
# Used to populate 'Select Forum Quicklinks' and Front Main page
# of forum (Group/Forum View)
#	$groupid:		Optional id to display just a single group
# ------------------------------------------------------------------
function sf_get_combined_groups_and_forums($groupid = null)
{
	global $wpdb;

	If(is_null($groupid) ? $where='' : $where = " WHERE ".SFGROUPS.".group_id=".$groupid." ");

	# retrieve group and forum records
	$records = $wpdb->get_results(
			"SELECT ".SFGROUPS.".group_id, group_name, group_desc, group_rss, group_icon,
			 forum_id, forum_name, forum_slug, forum_desc, forum_status, forum_icon, forum_rss_private, post_id
			 FROM ".SFGROUPS."
			 LEFT JOIN ".SFFORUMS." ON ".SFGROUPS.".group_id = ".SFFORUMS.".group_id
			 ".$where."
			 ORDER BY group_seq, forum_seq;");

	# rebuild into an array grabbing permissions on the way
	$groups=array();

	# Set initially to Access Denied in case current user can view no forums
	$groups[0]['group_id'] = "Access Denied";
	$gindex=-1;
	$findex=0;
	if($records)
	{
		foreach($records as $record)
		{
			$groupid=$record->group_id;
			$forumid=$record->forum_id;

			if(sf_can_view_forum($forumid))
			{
				if($gindex == -1 || $groups[$gindex]['group_id'] != $groupid)
				{
					$gindex++;
					$findex=0;
					$groups[$gindex]['group_id']=$record->group_id;
					$groups[$gindex]['group_name']=stripslashes($record->group_name);
					$groups[$gindex]['group_desc']=stripslashes($record->group_desc);
					$groups[$gindex]['group_rss']=$record->group_rss;
					$groups[$gindex]['group_icon']=$record->group_icon;
				}
				if(isset($record->forum_id))
				{
					$groups[$gindex]['forums'][$findex]['forum_id']=$record->forum_id;
					$groups[$gindex]['forums'][$findex]['forum_name']=stripslashes($record->forum_name);
					$groups[$gindex]['forums'][$findex]['forum_slug']=$record->forum_slug;
					$groups[$gindex]['forums'][$findex]['forum_desc']=stripslashes($record->forum_desc);
					$groups[$gindex]['forums'][$findex]['forum_status']=$record->forum_status;
					$groups[$gindex]['forums'][$findex]['forum_icon']=$record->forum_icon;
					$groups[$gindex]['forums'][$findex]['forum_rss_private']=$record->forum_rss_private;
					$groups[$gindex]['forums'][$findex]['post_id']=$record->post_id;
					$findex++;
				}
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
				$groups[$gindex]['group_rss']=$record->group_rss;
				$groups[$gindex]['group_icon']=$record->group_icon;
				$gindex++;
			}
		}
	}

	return $groups;
}

# ------------------------------------------------------------------
# sf_get_combined_groups_and_forums_bloglink()
#
# Grabs all groups and forums. Soecial cut down version for
# populating the blog link add post drop down
# ------------------------------------------------------------------
function sf_get_combined_groups_and_forums_bloglink()
{
	global $wpdb, $current_user;

	# retrieve group and forum records
	$records = $wpdb->get_results(
			"SELECT ".SFGROUPS.".group_id, group_name,
			 forum_id, forum_name
			 FROM ".SFGROUPS."
			 LEFT JOIN ".SFFORUMS." ON ".SFGROUPS.".group_id = ".SFFORUMS.".group_id
			 ".$where."
			 ORDER BY group_seq, forum_seq;");

	# rebuild into an array grabbing permissions on the way
	$groups=array();
	$gindex=-1;
	$findex=0;
	if($records)
	{
		foreach($records as $record)
		{
			$groupid=$record->group_id;
			$forumid=$record->forum_id;

			if (sf_user_can($current_user->ID, 'Can create linked topics', $forumid) && sf_user_can($current_user->ID, 'Can start new topics', $forumid))
			{
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
					$findex++;
				}
			}
		}
	}

	return $groups;
}

# ------------------------------------------------------------------
# sf_get_combined_forum_stats($forumid)
#
# Returns most recent post data, topic count and post count in forum
# 	$forumid:		forum data to return
#	$postid:		last post to be made in forum
#	$show:			array of which columns to show
# ------------------------------------------------------------------
function sf_get_combined_forum_stats($forumid, $postid, $show)
{
	global $wpdb;

	if(!isset($postid))
	{
		$record=array();
		$record['topic_count'] = '0';
		$record['post_count'] = '0';
		$record['udate'] = '';

		return $record;
	}

	$record = $wpdb->get_row(
 			"SELECT ".SFPOSTS.".post_id, topic_id, ".SFPOSTS.".forum_id, ".sf_zone_datetime('post_date').", UNIX_TIMESTAMP(post_date) as udate, guest_name, ".SFPOSTS.".user_id, ".SFMEMBERS.".display_name, post_index, ".SFFORUMS.".topic_count
			 FROM ".SFPOSTS."
			 LEFT JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id
			 LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			 WHERE ".SFPOSTS.".post_id = ".$postid.";", ARRAY_A);

	$record['post_count'] = $wpdb->get_var("SELECT COUNT(post_id) FROM ".SFPOSTS." WHERE forum_id=".$forumid);

	return $record;
}

# ------------------------------------------------------------------
# sf_get_group_record()
#
# Returns a single group row
# 	$groupid:		group_id of group to return
#	$asArray:		return as an array if true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_group_record($groupid, $asArray=false)
{
	global $wpdb;

	$sql=(
			"SELECT *
			 FROM ".SFGROUPS."
			 WHERE group_id=".$groupid.";");
	if($asArray) return $wpdb->get_row($sql, ARRAY_A);
	return $wpdb->get_row($sql);
}

# ------------------------------------------------------------------
# sf_get_groups_all()
#
# Return ALL group records - no permission checking
#	$id_only:		Optionsal = return just ids
#	$asArray:		Optional - return as array
# ------------------------------------------------------------------
function sf_get_groups_all($id_only=false, $asArray=false)
{
	global $wpdb;

	if($id_only ? $FROM='group_id' : $FROM='*');

	$sql=("SELECT ".$FROM." FROM ".SFGROUPS." ORDER BY group_seq");
	if($asArray) return $wpdb->get_results($sql, ARRAY_A);
	return $wpdb->get_results($sql);
}

# ------------------------------------------------------------------
# sf_group_exists()
#
# Check the existence of a group by id
# 	$groupid:		group to check for
# ------------------------------------------------------------------
function sf_group_exists($groupid)
{
	global $wpdb;

	if(empty($groupid)) return false;
	if($wpdb->get_var(
			"SELECT group_name
			 FROM ".SFGROUPS."
			 WHERE group_id=".$groupid))
	{
		return true;
	}
	return false;
}

# ------------------------------------------------------------------
# sf_get_group_rss_url()
#
# Returns the RSS feed URL for a Group (custom or standard)
# 	$groupid:		group to return
# ------------------------------------------------------------------
function sf_get_group_rss_url($groupid)
{
	global $wpdb;

	if(empty($groupid)) return '';
	$url = $wpdb->get_var(
			"SELECT group_rss FROM ".SFGROUPS." WHERE group_id=".$groupid);
	if(empty($url)) $url = sf_get_sfurl_plus_amp(SFURL).'group='.$groupid.'&amp;xfeed=group';
	return $url;
}

# ------------------------------------------------------------------
# sf_get_group_name_from_forum()
#
# Returns the Group Name when only the forum id is known
# 	$forumid:		forum to lookup for group name
# ------------------------------------------------------------------
function sf_get_group_name_from_forum($forumid)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT ".SFGROUPS.".group_name
			 FROM ".SFGROUPS."
			 LEFT JOIN ".SFFORUMS." ON ".SFFORUMS.".group_id = ".SFGROUPS.".group_id
			 WHERE ".SFFORUMS.".forum_id=".$forumid);
}

# ******************************************************************
# FORUM/TOPIC VIEW AND GENERAL DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_combined_forums_and_topics($forumid)
#
# Grabs all forums and their topics. Note that the forum data is
# repeated. Used to populate Topic Listing page of topics
# (Forum/Topics View)
#	$forumid:			forum id to display
#	$currentpage:		index to paging
#	$searchvalue:		Search value if in search mode
#	$currentsearchpage:	index to search paging
# ------------------------------------------------------------------
function sf_get_combined_forums_and_topics($forumid, $currentpage, $searchvalue, $currentsearchpage)
{
	global $wpdb, $sfvars;

	# rebuild into an array
	$forums=array();

	# Set initially to Access Denied in case current user can view no forums
	$forums[0]['forum_id']="Access Denied";

	# quick permission check
	if(!sf_can_view_forum($forumid)) return $forums;

	# some setup vars
	$startlimit = 0;

	# how many topics per page?
	$tpaged=get_option('sfpagedtopics');
	if($tpaged < 1) $tpaged=12;

	# setup where we are in the topic list (paging)
 	if(empty($searchvalue))
	{
		if($currentpage != 1)
		{
			$startlimit = ((($currentpage-1) * $tpaged));
		}
	} else {
		if($currentsearchpage == 1)
		{
			$currentpage = 1;
		} else {
			$startlimit = ((($currentsearchpage-1) * $tpaged));
		}
	}

	$LIMIT = " LIMIT ".$startlimit.', '.$tpaged;

 	if(empty($searchvalue))
	{
		if(get_option('sftopicsort'))
		{
			$ORDER = " ORDER BY topic_pinned DESC, ".SFTOPICS.".post_id DESC";
		} else {
			$ORDER = " ORDER BY topic_pinned DESC, ".SFTOPICS.".post_id ASC";
		}
	} else {
		$ORDER = " ORDER BY ".SFTOPICS.".post_id DESC";
	}

	if(empty($searchvalue))
	{
		# standar forum view
		$SELECT = "SELECT ";
		$POSTJOIN= "";
		$MATCH = "";
		$ANDWHERE = "";
	} else {
		$searchvalue=urldecode($searchvalue);

		# what sort of search is it?
		if(substr($searchvalue, 0, 10) == 'statusflag')
		{
			# topic status search
			$temp=array();
			$temp=explode('%', $searchvalue);
			$flag=$temp[1];
			$SELECT = "SELECT SQL_CALC_FOUND_ROWS DISTINCT ";
			$POSTJOIN= "";
			$MATCH = "";
			$ANDWHERE = " AND topic_status_flag=".$flag." ";
		} elseif(substr($searchvalue, 0, 12) == "sf%members%1")
		{
			# users posts in sepcified forum search
			$temp=array();
			$temp=explode('%', $searchvalue);
			$userid = substr($temp[3], 4, 25);
			$SELECT = "SELECT SQL_CALC_FOUND_ROWS DISTINCT ";
			$POSTJOIN = "JOIN ".SFPOSTS." ON ".SFTOPICS.".topic_id = ".SFPOSTS.".topic_id ";
			$MATCH = "";
			$ANDWHERE = " AND ".SFPOSTS.".user_id=".$userid." ";
		} elseif(substr($searchvalue, 0, 12) == "sf%members%2")
		{
			# users posts in sepcified forum search
			$temp=array();
			$temp=explode('%', $searchvalue);
			$userid = substr($temp[3], 4, 25);
			$SELECT = "SELECT SQL_CALC_FOUND_ROWS DISTINCT ";
			$POSTJOIN = "JOIN ".SFPOSTS." ON ".SFTOPICS.".topic_id = ".SFPOSTS.".topic_id ";
			$MATCH = "";
			$ANDWHERE = " AND ".SFTOPICS.".user_id=".$userid." ";
		} else {
			# general keyword search
			$searchterm = sf_construct_search_term($searchvalue);
			$SELECT = "SELECT SQL_CALC_FOUND_ROWS DISTINCT ";
			$POSTJOIN = "JOIN ".SFPOSTS." ON ".SFTOPICS.".topic_id = ".SFPOSTS.".topic_id ";
			$MATCH = "MATCH(".SFPOSTS.".post_content) AGAINST ('".$searchterm."' IN BOOLEAN MODE) AND ";
			$ANDWHERE = "";
		}
	}

	# retrieve forum and topic records
	$records = $wpdb->get_results(
			$SELECT.SFFORUMS.".forum_id, forum_slug, forum_name, forum_status, group_id, topic_count, forum_icon, forum_desc, topic_status_set,
			 ".SFTOPICS.".topic_id, topic_slug, topic_name, ".sf_zone_datetime('topic_date').",
			 topic_status, topic_pinned, topic_sort, topic_opened, topic_subs, topic_status_flag,
			 blog_post_id, ".SFTOPICS.".post_id, post_count
			 FROM ".SFFORUMS."
			 JOIN ".SFTOPICS." ON ".SFFORUMS.".forum_id = ".SFTOPICS.".forum_id
			 ".$POSTJOIN."
			 WHERE ".$MATCH.SFFORUMS.".forum_id=".$forumid.$ANDWHERE.$ORDER.$LIMIT.";");

 	if(!empty($searchvalue))
	{
		$totalrows = $wpdb->get_var("SELECT FOUND_ROWS()");
		add_sfsetting($searchvalue, $totalrows);
	}

	$findex=-1;
	$tindex=0;

	if($records)
	{
		foreach($records as $record)
		{
			$forumid=$record->forum_id;

			if($findex == -1 || $forums[$findex]['forum_id'] != $forumid)
			{
				$findex++;
				$tindex=0;
				$forums[$findex]['forum_id']=$record->forum_id;
				$forums[$findex]['forum_slug']=$record->forum_slug;
				$forums[$findex]['forum_name']=stripslashes($record->forum_name);
				$forums[$findex]['forum_desc']=stripslashes($record->forum_desc);
				$forums[$findex]['forum_status']=$record->forum_status;
				$forums[$findex]['group_id']=$record->group_id;
				$forums[$findex]['topic_count']=$record->topic_count;
				$forums[$findex]['forum_icon']=$record->forum_icon;
				$forums[$findex]['topic_status_set']=$record->topic_status_set;
			}
			$forums[$findex]['topics'][$tindex]['topic_id']=$record->topic_id;
			$forums[$findex]['topics'][$tindex]['topic_slug']=$record->topic_slug;
			$forums[$findex]['topics'][$tindex]['topic_name']=stripslashes($record->topic_name);
			$forums[$findex]['topics'][$tindex]['topic_date']=$record->topic_date;
			$forums[$findex]['topics'][$tindex]['topic_status']=$record->topic_status;
			$forums[$findex]['topics'][$tindex]['topic_pinned']=$record->topic_pinned;
			$forums[$findex]['topics'][$tindex]['topic_sort']=$record->topic_sort;
			$forums[$findex]['topics'][$tindex]['topic_opened']=$record->topic_opened;
			$forums[$findex]['topics'][$tindex]['topic_subs']=$record->topic_subs;
			$forums[$findex]['topics'][$tindex]['topic_status_flag']=$record->topic_status_flag;
			$forums[$findex]['topics'][$tindex]['blog_post_id']=$record->blog_post_id;
			$forums[$findex]['topics'][$tindex]['post_id']=$record->post_id;
			$forums[$findex]['topics'][$tindex]['post_count']=$record->post_count;
			$tindex++;
		}
	} else {
		$record = sf_get_forum_record($forumid, false);
		if($record)
		{
			$forums[0]['forum_id']=$record->forum_id;
			$forums[0]['forum_slug']=$record->forum_slug;
			$forums[0]['forum_name']=stripslashes($record->forum_name);
			$forums[0]['forum_desc']=stripslashes($record->forum_desc);
			$forums[0]['forum_status']=$record->forum_status;
			$forums[0]['topic_count']=$record->topic_count;
			$forums[0]['forum_icon']=$record->forum_icon;
		}
	}
	return $forums;
}

# ------------------------------------------------------------------
# sf_get_combined_topic_stats()
#
# Returns the first and last post data for a topic
#	$topicid:		topic id from new posts list
#	$postid:		post id from new posts list
#	$postindex:		number of post (should be last one in topic)
#	$show			-
# ------------------------------------------------------------------
function sf_get_combined_topic_stats($topicid, $postid, $postindex, $show)
{
	global $wpdb;

	$record = $wpdb->get_results(
			"SELECT post_id, topic_id, forum_id, ".sf_zone_datetime('post_date').", UNIX_TIMESTAMP(post_date) as udate, guest_name, ".SFPOSTS.".user_id, post_index, post_status, ".SFMEMBERS.".display_name
			 FROM ".SFPOSTS."
			 LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			 WHERE topic_id = ".$topicid." AND post_index = ".$postindex." OR topic_id = ".$topicid." AND post_index=1
			 ORDER BY post_id DESC;", ARRAY_A);

	return $record;
}

# ******************************************************************
# FORUM/TOPIC VIEW AND GENERAL DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_watched_topics($currentpagge)
#
# Grabs all watched topics
#	$currentpage:		index to paging
# ------------------------------------------------------------------
function sf_get_watched_topics($currentpage)
{
	global $wpdb, $current_user;

	# quick permission check
	if (!$current_user->sfwatch) return '';

	# how many topics per page?
	$startlimit = 0;
	$tpaged=get_option('sfpagedtopics');
	if ($tpaged < 1) $tpaged=12;

	if ($currentpage != 1)
	{
		$startlimit = ((($currentpage-1) * $tpaged));
	}
	$limit = " LIMIT ".$startlimit.', '.$tpaged;

	# get watched topics
	$list = sf_get_member_item($current_user->ID, 'watches');
	if (empty($list)) return '';

	# create where clause of watched topics
	$list = explode('@', $list);
	$where = " WHERE topic_id IN (" . implode(",", $list) . ")";

	# retrieve watched topic records
	$query = "SELECT topic_id, topic_slug, topic_name, ".sf_zone_datetime('topic_date').",
			 topic_status, topic_pinned, topic_sort, topic_opened, topic_subs, topic_status_flag,
			 blog_post_id, ".SFTOPICS.".forum_id, ".SFTOPICS.".post_id, post_count,
			 forum_slug, forum_name, topic_status_set, group_id
			 FROM ".SFTOPICS."
			 LEFT JOIN ".SFFORUMS." ON ".SFFORUMS.".forum_id = ".SFTOPICS.".forum_id ".
			 $where." ORDER BY topic_date DESC ".$limit;
	$records = $wpdb->get_results($query, ARRAY_A);

	$watched['records'] = $records;
	$watched['count'] = count($list);

	return $watched;
}

# ------------------------------------------------------------------
# sf_get_memberlists()
#
# Builds viewable member lists for current user
# ------------------------------------------------------------------
function sf_get_memberlists($currentpage, $search)
{
	global $wpdb, $current_user;

	if ($current_user->member && ($current_user->forumadmin || get_option('sfshowmemberlist')))
	{
		if ($current_user->forumadmin || !get_option('sfmemberlistperms'))
		{
			# for admins, or if display option is set, return all user groupgs
			$where = '';
		} else {
			# for moderators and users limit to user group limited results
			$forums = sf_get_viewable_forums($current_user->ID);

			#if no forums are visible, then no user groups are visible either
			if ($forums)
			{
				$forum_ids = '';
				foreach ($forums as $forum)
				{
					if (sf_can_view_forum($forum->forum_id))
					{
						$forum_ids[] = $forum->forum_id;
					}
				}

				# if no visible forums, dont return any search results
				if (empty($forum_ids)) return;
			} else {
				return;
			}

			# create where clause based on forums that current user can view
			$list = explode('@', $list);
			$where = " WHERE ".SFPERMISSIONS.".forum_id IN (" . implode(",", $forum_ids) . ") ";
		}

		if ($search != '') $where .= ' AND display_name LIKE "'.$search.'%"';

		# how many members per page?
		$startlimit = 0;
		$tpaged=get_option('sfpagedtopics');
		if ($tpaged < 1) $tpaged=12;

		if ($currentpage != 1)
		{
			$startlimit = ((($currentpage-1) * $tpaged));
		}
		$limit = " LIMIT ".$startlimit.', '.$tpaged;

		# retrieve members list records
		$query = "SELECT DISTINCT ".SFPERMISSIONS.".usergroup_id, ".SFMEMBERSHIPS.".user_id, display_name, posts, lastvisit, usergroup_name, usergroup_desc
			FROM ".SFPERMISSIONS."
			INNER JOIN ".SFUSERGROUPS." ON ".SFUSERGROUPS.".usergroup_id = ".SFPERMISSIONS.".usergroup_id
			INNER JOIN ".SFMEMBERSHIPS." ON ".SFMEMBERSHIPS.".usergroup_id = ".SFPERMISSIONS.".usergroup_id
			INNER JOIN ".SFMEMBERS." ON ".SFMEMBERS.".user_id = ".SFMEMBERSHIPS.".user_id
			".$where."
			ORDER BY usergroup_id, display_name "
			.$limit;
		$records = $wpdb->get_results($query, ARRAY_A);
		$data->records = $records;

		# retrieve number of records
		$query = "SELECT DISTINCT ".SFPERMISSIONS.".usergroup_id, ".SFMEMBERSHIPS.".user_id, display_name, posts, lastvisit, usergroup_name, usergroup_desc
			FROM ".SFPERMISSIONS."
			INNER JOIN ".SFUSERGROUPS." ON ".SFUSERGROUPS.".usergroup_id = ".SFPERMISSIONS.".usergroup_id
			INNER JOIN ".SFMEMBERSHIPS." ON ".SFMEMBERSHIPS.".usergroup_id = ".SFPERMISSIONS.".usergroup_id
			INNER JOIN ".SFMEMBERS." ON ".SFMEMBERS.".user_id = ".SFMEMBERSHIPS.".user_id
			".$where."
			ORDER BY usergroup_id, display_name ";
		$records = $wpdb->get_results($query, ARRAY_A);
		$data->count = count($records);

		return ($data);
	}
}

function sf_get_viewable_forums($user_id)
{
	global $wpdb, $current_user;

	if ($current_user->guest)
	{
		$guests = get_option('sfguestsgroup');
		$sql = "SELECT forum_id
			FROM ".SFPERMISSIONS."
			WHERE usergroup_id=".$guests;
	} else {
		$sql = "SELECT forum_id
			FROM ".SFPERMISSIONS."
			LEFT JOIN ".SFMEMBERSHIPS." ON ".SFPERMISSIONS.".usergroup_id = ".SFMEMBERSHIPS.".usergroup_id
			WHERE user_id=".$user_id;
	}
	return $wpdb->get_results($sql);
}

function sf_get_membership_count($usergroup_id)
{
	global $wpdb;

	$sql = "SELECT COUNT(*)
			FROM ".SFMEMBERSHIPS."
			WHERE ".SFMEMBERSHIPS.".usergroup_id=".$usergroup_id;
	return $wpdb->get_var($sql);
}

# ------------------------------------------------------------------
# sf_get_last_post_in_topic()
#
# Returns post details of the latest post in the requested topic
#	$topicid:		requested topic
# NOTE: This one remains as used in a template tag
# ------------------------------------------------------------------
function sf_get_last_post_in_topic($topicid)
{
	global $wpdb;
	return $wpdb->get_row(
			"SELECT post_id, topic_id, forum_id, post_status, post_index, ".sf_zone_datetime('post_date').", UNIX_TIMESTAMP(post_date) as udate, guest_name, ".SFPOSTS.".user_id, ".SFMEMBERS.".display_name
			 FROM ".SFPOSTS."
			 LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			 WHERE topic_id = ".$topicid."
			 ORDER BY post_id DESC LIMIT 1");
}

# ------------------------------------------------------------------
# sf_get_postratings()
#
# Returns post ratings
# 	$postid:		post_id of post to return
#	$asArray:		return as an array if true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_postratings($postid, $asArray=false)
{
	global $wpdb;

	$sql=(
			"SELECT *
			 FROM ".SFPOSTRATINGS."
			 WHERE post_id=".$postid.";");
	if($asArray) return $wpdb->get_row($sql, ARRAY_A);
	return $wpdb->get_row($sql);
}

# ------------------------------------------------------------------
# sf_update_postratings()
#
# Upates post ratings
# 	$postid:		post_id
# 	$count:			number of votes
# 	$sum:			ratings sum
# 	$ips:			array of ips voted for guests
# 	$members:		members that have voted
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_update_postratings($postid, $count, $sum, $ips, $members)
{
	global $wpdb;

	$sql=(
			"UPDATE ".SFPOSTRATINGS."
			 SET vote_count=$count, ratings_sum=$sum, ips='".$ips."', members='".$members."'
			 WHERE post_id=".$postid.";");
	$wpdb->query($sql);
	return;
}

# ------------------------------------------------------------------
# Add post ratings
# 	$postid:		post_id
# 	$count:			number of votes
# 	$sum:			ratings sum
# 	$ips:			array of ips voted for guests
# 	$members:		members that have voted
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_add_postratings($postid, $count, $sum, $ips, $members)
{
	global $wpdb;

	$sql=(
		 	"INSERT INTO ".SFPOSTRATINGS." (post_id, vote_count, ratings_sum, ips, members)
			 VALUES ($postid, $count, $sum, '".$ips."', '".$members."');");
	$wpdb->query($sql);
	return;
}

# ------------------------------------------------------------------
# sf_get_forum_record()
#
# Returns a single forum row
# 	$forumid:		forum_id of forum to return
#	$asArray:		return as an array if true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_forum_record($forumid, $asArray=false)
{
	global $wpdb;

	$sql=(
			"SELECT *
			 FROM ".SFFORUMS."
			 WHERE forum_id=".$forumid.";");
	if($asArray) return $wpdb->get_row($sql, ARRAY_A);
	return $wpdb->get_row($sql);
}

# ------------------------------------------------------------------
# sf_get_forum_record_from_slug()
#
# Returns a single forum row
# 	$forumslug:		forum_slug of forum to return
#	$asArray:		return as an array if true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_forum_record_from_slug($forumslug, $asArray=false)
{
	global $wpdb;

	$sql=(
			"SELECT *
			 FROM ".SFFORUMS."
			 WHERE forum_slug='".$forumslug."';");
	if($asArray) return $wpdb->get_row($sql, ARRAY_A);
	return $wpdb->get_row($sql);
}

# ------------------------------------------------------------------
# sf_get_forums_all()
#
# Returns complete recordset of forums
# 	$id_only:		limit recordset to forum_id and slug only
#	$asArray:		return results as an array
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_forums_all($id_only=false, $asArray=false)
{
	global $wpdb;

	if($id_only ? $FROM='forum_id, forum_slug' : $FROM='*');
	$sql=("SELECT ".$FROM." FROM ".SFFORUMS." ORDER BY forum_seq");
	if($asArray) return $wpdb->get_results($sql, ARRAY_A);
	return $wpdb->get_results($sql);
}

# ------------------------------------------------------------------
# sf_forum_exists()
#
# Check the existence of a forum by id
# 	$forumid:		forum to check for
# ------------------------------------------------------------------
function sf_forum_exists($forumid)
{
	global $wpdb;

	if(empty($forumid)) return false;
	if($wpdb->get_var(
			"SELECT forum_name
			 FROM ".SFFORUMS."
			 WHERE forum_id=".$forumid))
	{
		return true;
	}
	return false;
}

# ------------------------------------------------------------------
# sf_get_forum_rss_url()
#
# Returns the RSS URL for a forum (custom or standard)
# 	$forumid:		forum to return
#	$forumslug:		slug for the url
# ------------------------------------------------------------------
function sf_get_forum_rss_url($forumid, $forumslug)
{
	global $wpdb;

	if(empty($forumid)) return '';
	$url = $wpdb->get_var(
			"SELECT forum_rss FROM ".SFFORUMS." WHERE forum_id=".$forumid);
	if(empty($url)) $url = sf_build_qurl('forum='.$forumslug, 'xfeed=forum');
	return $url;
}

# ------------------------------------------------------------------
# sf_get_topic_status_set()
#
# Returns the topic status set name for a forum
# 	$forumid:		forum to return
# ------------------------------------------------------------------
function sf_get_topic_status_set($forumid)
{
	global $wpdb;

	if(empty($forumid)) return '';
	return $wpdb->get_var(
			"SELECT topic_status_set FROM ".SFFORUMS." WHERE forum_id=".$forumid);
}

function sf_get_topic_status_from_forum($forumid, $statusflag)
{
	global $wpdb;

	$flag='';
	$set=sf_get_topic_status_set($forumid);
	if($set != 0)
	{
		$flag=sf_get_topic_status_flag($set, $statusflag);
	}
	return $flag;
}

# ------------------------------------------------------------------
# sf_find_user_in_topic()
#
# Searches a topics posts to see if user has ever posted in it for
# the forums topic list icon
# 	$topicid:		topic to search
#	$userid:		user to look for
# %%FUTURE OPTIMISE%%
# ------------------------------------------------------------------
function sf_find_user_in_topic($topicid, $userid)
{
	global $wpdb;
	return $wpdb->get_col(
			"SELECT user_id
			 FROM ".SFPOSTS."
			 WHERE topic_id=".$topicid."
			 AND user_id=".$userid);
}

# ------------------------------------------------------------------
# sf_get_forum_from_topic()
#
# returng the firum id when only the topic is known
# 	$topicid:		topic to search
# ------------------------------------------------------------------

function sf_get_forum_from_topic($topicid)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT forum_id
			 FROM ".SFTOPICS."
			 WHERE topic_id=".$topicid);
}

# ------------------------------------------------------------------
# sf_get_forum_name()
#
# Returns forum name when only the slug is known
# 	$forumslug:		forum to return
# ------------------------------------------------------------------
function sf_get_forum_name($forumslug)
{
	global $wpdb;
	return stripslashes($wpdb->get_var(
			"SELECT forum_name
			 FROM ".SFFORUMS."
			 WHERE forum_slug='".$forumslug."'"));
}

# ------------------------------------------------------------------
# sf_get_forum_slug()
#
# Returns forum slug when only the id is known
# 	$forumid:		forum to return
# ------------------------------------------------------------------
function sf_get_forum_slug($forumid)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT forum_slug
			 FROM ".SFFORUMS."
			 WHERE forum_id=".$forumid);
}

# ------------------------------------------------------------------
# sf_get_forum_id()
#
# Returns forum id when only the slug is known
# 	$forumslug:		forum to return
# ------------------------------------------------------------------

function sf_get_forum_id($forumslug)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT forum_id
			 FROM ".SFFORUMS."
			 WHERE forum_slug='".$forumslug."'");
}

# ------------------------------------------------------------------
# sf_get_topics_forum_id()
#
# Returns forum id when only the topic id is known
# 	$topicid:		forum to return from topic record
# ------------------------------------------------------------------
function sf_get_topics_forum_id($topicid)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT forum_id
			 FROM ".SFTOPICS."
			 WHERE topic_id=".$topicid);
}

# ******************************************************************
# TOPIC/POST VIEW AND GENERAL DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_combined_topics_and_posts()
#
# Returns a page of posts for specified topic
# 	$topicid:		topic and posts to load
# ------------------------------------------------------------------
function sf_get_combined_topics_and_posts($topicid)
{
	global $wpdb, $sfvars;

	# sadly have to grab the topic row first because we need obverride sort order if set
	$topic = $wpdb->get_row(
			"SELECT topic_id, topic_slug, ".SFTOPICS.".forum_id, topic_name, topic_sort, post_count, topic_subs, topic_status, blog_post_id,
			 forum_slug, forum_status, topic_status_set, topic_status_flag
			 FROM ".SFTOPICS."
			 JOIN ".SFFORUMS." ON ".SFTOPICS.".forum_id = ".SFFORUMS.".forum_id
			 WHERE topic_id = ".$topicid.";", ARRAY_A);

	# quick permission check
 	if(!sf_can_view_forum($topic['forum_id'])) return '';

	# now for the posts
	$ORDER="ASC"; # default
	if(get_option('sfsortdesc')) $ORDER="DESC"; # global override
	if(!is_null($topic['topic_sort'])) $ORDER=$topic['topic_sort']; # topic override

	$ppaged=get_option('sfpagedposts');
	if($ppaged < 1) $ppaged=20;

	if($sfvars['page'] == 1 ? $startlimit = 0 : $startlimit = ((($sfvars['page']-1) * $ppaged)));

	$topic['topic_page'] = $sfvars['page'];

	$tpages = ($topic['post_count'] / $ppaged);
	if(!is_int($tpages))
	{
		$tpages = intval($topic['post_count'] / $ppaged) +1;
	}
	$topic['topic_total_pages'] = $tpages;

	$LIMIT = ' LIMIT '.$startlimit.', '.$ppaged;

	$records = $wpdb->get_results(
			"SELECT ".SFPOSTS.".post_id, post_content, ".sf_zone_datetime('post_date').", ".SFPOSTS.".user_id, guest_name, guest_email, post_status, post_pinned, post_index, post_edit,
			".SFMEMBERS.".display_name, user_url, user_email, rating_id, vote_count, ratings_sum, ips, members
			 FROM ".SFPOSTS."
			 LEFT JOIN ".SFUSERS." ON ".SFPOSTS.".user_id = ".SFUSERS.".ID
			 LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			 LEFT JOIN ".SFPOSTRATINGS." ON ".SFPOSTRATINGS.".post_id = ".SFPOSTS.".post_id
			 WHERE topic_id = ".$topicid."
			 ORDER BY post_pinned DESC, ".SFPOSTS.".post_id ".$ORDER.$LIMIT, ARRAY_A);

	$topic['posts'] = $records;

	return $topic;
}

# ------------------------------------------------------------------
# sf_get_topic_record()
#
# Returns a single topic row
# 	$topicid:		topic_id of topic to return
#	$asArray:		returnb as an arrau of true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_topic_record($topicid, $asArray=false)
{
	global $wpdb;

	$sql=(
			"SELECT *
			 FROM ".SFTOPICS."
			 WHERE topic_id=".$topicid.";");
	if($asArray) return $wpdb->get_row($sql, ARRAY_A);
	return $wpdb->get_row($sql);
}

# ------------------------------------------------------------------
# sf_get_topic_record_from_slug()
#
# Returns a single topic row
# 	$topicslug:		topic_slug of topic to return
#	$asArray:		return as an array if true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_topic_record_from_slug($topicslug, $asArray=false)
{
	global $wpdb;

	$sql=(
			"SELECT *
			 FROM ".SFTOPICS."
			 WHERE topic_slug='".$topicslug."';");
	if($asArray) return $wpdb->get_row($sql, ARRAY_A);
	return $wpdb->get_row($sql);
}

# ------------------------------------------------------------------
# sf_get_topics_all()
#
# Returns complete recordset of topics
# 	$id_only:		limit recordset to topic_id and slug only
#	$asArray:		returb list as an aray
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sf_get_topics_all($id_only=false, $asArray=false)
{
	global $wpdb;

	if($id_only ? $FROM='topic_id, topic_slug' : $FROM='*');

	$sql=("SELECT ".$FROM." FROM ".SFTOPICS);
	if($asArray) return $wpdb->get_results($sql, ARRAY_A);
	return $wpdb->get_results($sql);
}

# ------------------------------------------------------------------
# sf_topic_exists()
#
# Check the existence of a topic by id
# 	$topicid:		forum to check for
# ------------------------------------------------------------------
function sf_topic_exists($topicid)
{
	global $wpdb;

	if(empty($topicid)) return false;
	if($wpdb->get_var(
			"SELECT topic_name
			 FROM ".SFTOPICS."
			 WHERE topic_id=".$topicid))
	{
		return true;
	}
	return false;
}

# ------------------------------------------------------------------
# sf_get_topic_sort()
#
# This one is a pain in the backside! I allowed an individual topic
# to sort its posts against the default so it needs to be looked up
# 	$topicslug:		return sort override option if set for topic
# ------------------------------------------------------------------
function sf_get_topic_sort($topicslug)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT topic_sort
			 FROM ".SFTOPICS."
			 WHERE topic_slug='".$topicslug."'");
}

# ------------------------------------------------------------------
# sf_get_topic_name()
#
# Returns topic name when only the topic slug is known
# 	$topicslug:		Topic to lookup
# ------------------------------------------------------------------
function sf_get_topic_name($topicslug)
{
	global $wpdb;
	return stripslashes($wpdb->get_var(
			"SELECT topic_name
			 FROM ".SFTOPICS."
			 WHERE topic_slug='".$topicslug."'"));
}

# ------------------------------------------------------------------
# sf_get_topic_slug()
#
# Returns topic slug when only the topic id is known
# 	$topicid:		Topic to lookup
# ------------------------------------------------------------------
function sf_get_topic_slug($topicid)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT topic_slug
			 FROM ".SFTOPICS."
			 WHERE topic_id=".$topicid);
}

# ------------------------------------------------------------------
# sf_get_topic_id()
#
# Returns topic id when only the topic slug is known
# 	$topicslug:		Topic to lookup
# ------------------------------------------------------------------
function sf_get_topic_id($topicslug)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT topic_id
			 FROM ".SFTOPICS."
			 WHERE topic_slug='".$topicslug."'");
}

# ------------------------------------------------------------------
# sf_get_slugs_from_postid()
#
# Returns forum and topic slugs when only the post id is known
# 	$postid:		Post to lookup
# ------------------------------------------------------------------
function sf_get_slugs_from_postid($postid)
{
	global $wpdb;
	return $wpdb->get_row(
			"SELECT forum_slug, topic_slug, post_index
			 FROM ".SFPOSTS."
			 JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id
			 JOIN ".SFTOPICS." ON ".SFPOSTS.".topic_id = ".SFTOPICS.".topic_id
			 WHERE ".SFPOSTS.".post_id=".$postid.";");
}

# ------------------------------------------------------------------
# sf_get_posts_count_in_topic()
#
# Returns the post count from topic record
# 	$topicid:		Topic to lookup
# ------------------------------------------------------------------

function sf_get_posts_count_in_topic($topicid)
{
	global $wpdb;
	if(empty($topicid)) return 0;
	return $wpdb->get_var(
			"SELECT post_count
			 FROM ".SFTOPICS."
			 WHERE topic_id=".$topicid);
}


function sf_update_topic_status_flag($statvalue, $topicid)
{
	global $wpdb;
	return $wpdb->query("UPDATE ".SFTOPICS." SET topic_status_flag=".$statvalue." WHERE topic_id=".$topicid);
}

# ******************************************************************
# NEW/UNREAD POST VIEWS DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_admins_queued_posts()
#
# Returns the admins new post view
# ------------------------------------------------------------------
function sf_get_admins_queued_posts()
{
	global $wpdb;

	$newposts = '';

	$records = $wpdb->get_results(
			"SELECT ".SFWAITING.".forum_id, forum_slug, forum_name, topic_status_set, topic_id, ".SFWAITING.".post_count, ".SFWAITING.".post_id
			 FROM ".SFWAITING."
			 LEFT JOIN ".SFFORUMS." ON ".SFWAITING.".forum_id = ".SFFORUMS.".forum_id
			 ORDER BY forum_id;");

	if($records)
	{
		$newposts = array();
		$findex=-1;
		$pindex=0;
		$tindex=0;

		foreach($records as $record)
		{
			$forumid=$record->forum_id;
			if($findex == -1 || $newposts[$findex]['forum_id'] != $forumid)
			{
				$findex++;
				$tindex=0;
				$pindex=0;
				$newposts[$findex]['forum_id']=$record->forum_id;
				$newposts[$findex]['forum_name']=stripslashes($record->forum_name);
				$newposts[$findex]['forum_slug']=$record->forum_slug;
				$newposts[$findex]['topic_status_set']=$record->topic_status_set;
			}

			$newposts[$findex]['topics'][$tindex]['topic_id']=$record->topic_id;
			$newposts[$findex]['topics'][$tindex]['post_id']=$record->post_id;

			$postrecords = $wpdb->get_results(
					"SELECT post_content, post_index, post_id, post_status, ".SFPOSTS.".user_id, ".SFMEMBERS.".display_name, guest_name
					 FROM ".SFPOSTS."
					 LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
					 WHERE topic_id = ".$record->topic_id." AND post_id >= ".$record->post_id."
					 ORDER BY post_id;");

			if($postrecords)
			{
				$newposts[$findex]['topics'][$tindex]['post_count']=count($postrecords);
				$pindex=0;
				foreach($postrecords as $postrecord)
				{
					$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['post_id']=$postrecord->post_id;
					$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['post_status']=$postrecord->post_status;
					$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['post_index']=$postrecord->post_index;
					$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['post_content']=$postrecord->post_content;
					$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['user_id']=$postrecord->user_id;
					if(empty($postrecord->user_id))
					{
						$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['display_name']=stripslashes($postrecord->guest_name);
						$thisuser = 'Guest';
					} else {
						$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['display_name']=stripslashes($postrecord->display_name);
						$thisuser = 'Member';
					}
					$newposts[$findex]['topics'][$tindex]['posts'][$pindex]['user_type']=$thisuser;
					$pindex++;
				}
			}
			$tindex++;
		}
	}

	# if no new posts then housekeep sfwaiting
	if(!$newposts) $wpdb->query("TRUNCATE ".SFWAITING);

	return $newposts;
}

# ------------------------------------------------------------------
# sf_combined_new_posts_list()
#
# Extend new post list to include all required data
#	$posts:		Posts array (forumid and topicid)
# ------------------------------------------------------------------
function sf_combined_new_posts_list($posts)
{
	global $wpdb;
	foreach($posts as $post)
	{
		if(sf_can_view_forum($post->forum_id))
		{
			$record = $wpdb->get_row(
					"SELECT ".SFFORUMS.".forum_id, forum_name, forum_slug,
					 topic_name, topic_slug, ".SFTOPICS.".post_id, ".SFTOPICS.".topic_id, topic_status_set,
					 post_status, UNIX_TIMESTAMP(".SFPOSTS.".post_date) as udate, post_index, ".SFPOSTS.".user_id
					 FROM ".SFFORUMS."
					 JOIN ".SFTOPICS." ON ".SFFORUMS.".forum_id = ".SFTOPICS.".forum_id
					 JOIN ".SFPOSTS." ON ".SFTOPICS.".post_id = ".SFPOSTS.".post_id
					 WHERE ".SFTOPICS.".forum_id=".$post->forum_id." AND ".SFTOPICS.".topic_id=".$post->topic_id.";", ARRAY_A);
		}
		if($record)
		{
			$postlist[]=$record;
		}
	}
	return $postlist;
}

# ------------------------------------------------------------------
# sf_get_users_new_post_list()
#
# Returns recordset of current users new-post-list
# 	$limit:		limit to x number of records
# ------------------------------------------------------------------
function sf_get_users_new_post_list($limit)
{
	global $current_user;

	if($current_user->member)
	{
		$newpostlist=sf_get_member_item($current_user->ID, 'newposts');
		$newpostlist=sf_update_users_newposts($newpostlist);
		# we have a live user so construct SQL if anything in newpostslist
		if($newpostlist[0] != 0)
		{
			$wanted = $limit;
			$where = ' WHERE';
			if(count($newpostlist) < $limit) $limit = count($newpostlist);
			for($x=0; $x<$limit; $x++)
			{
				$where.= " topic_id=".$newpostlist[$x];
				if($x != $limit-1) $where.= " OR";
			}

			$recordset1 = sf_get_users_new_post_list_db($where, '');

			# try and marry the extra count if not enough to satisfy $limit
			if($limit < $wanted)
			{
				$limit = " LIMIT ".$wanted;
				$where = " WHERE post_status = 0 ";
				$recordset2 = sf_get_users_new_post_list_db($where, $limit);
				if($recordset2)
				{
					for($x=0; $x<count($recordset2); $x++)
					{
						if(!in_array($recordset2[$x]->topic_id, $newpostlist))
						{
							$recordset1[]=$recordset2[$x];
						}
						if(count($recordset1) == $wanted) break;
					}
				}
			}
			return sf_filter_new_post_list($recordset1);
		}
	}
	# but if not a member, empty post list of members query didnlt reach limit...
	if($current_user->guest || $newpostlist[0] == 0)
	{
		$limit = " LIMIT ".$limit;
		$where = " WHERE post_status = 0 ";
		$recordset1 = sf_get_users_new_post_list_db('', $limit);
		return sf_filter_new_post_list($recordset1);
	}
}

# ------------------------------------------------------------------
# sf_get_users_new_post_list_db()
#
# Support: Returns recordset of current users new-post-list
#	$where:		Option where clause on topic id
# 	$limit:		limit to x number of records
# ------------------------------------------------------------------
function sf_get_users_new_post_list_db($where, $limit)
{
	global $wpdb;

	$records = $wpdb->get_results(
			"SELECT DISTINCT forum_id, topic_id
			 FROM ".SFPOSTS
			 .$where."
			 ORDER BY post_id DESC"
			 .$limit.";");

	return $records;
}

# ------------------------------------------------------------------
# sf_filter_new_post_list()
#
# Support: Returns filtered list that current user has permissions to
#	$recordset:	Full list of forum/topics
# ------------------------------------------------------------------
function sf_filter_new_post_list($recordset)
{
	$rlist = array();
	$x = 0;

	foreach($recordset as $record)
	{
		if(sf_can_view_forum($record->forum_id))
		{
			$rlist[$x]->forum_id=$record->forum_id;
			$rlist[$x]->topic_id=$record->topic_id;
			$x++;
		}
	}
	return $rlist;
}

# ******************************************************************
# NEW POSTS FROM WAITING - DASHBOARD
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_unread_forums()
#
# Returns list from the waiting table (Admins queue)
# ------------------------------------------------------------------
function sf_get_unread_forums()
{
	global $wpdb;
	return $wpdb->get_results(
			"SELECT topic_id, ".SFWAITING.".forum_id, forum_slug, forum_name, group_id, post_count, ".SFWAITING.".post_id, topic_status_set
			 FROM ".SFFORUMS."
			 LEFT JOIN ".SFWAITING." ON ".SFFORUMS.".forum_id = ".SFWAITING.".forum_id
			 WHERE post_count > 0
			 ORDER BY forum_id, topic_id");
}

# ------------------------------------------------------------------
# sf_get_awaiting_approval()
#
# Count of posts currently awaiting moderation
# ------------------------------------------------------------------
function sf_get_awaiting_approval()
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT COUNT(post_id) AS cnt
			 FROM ".SFPOSTS."
			 WHERE post_status=1");
}

# ------------------------------------------------------------------
# sf_topic_in_queue()
#
# returns true if the topic is in the admin queue
# 	$topicid		topic being tested
# ------------------------------------------------------------------
function sf_topic_in_queue($topicid)
{
	global $wpdb;

	return $wpdb->get_var(
			"SELECT post_count
			 FROM ".SFWAITING."
			 WHERE topic_id=".$topicid);
}

# ******************************************************************
# STATISTICS VIEWS DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_stats_counts()
#
# Returns stats on group/forum/topic/post count
# ------------------------------------------------------------------
function sf_get_stats_counts()
{
	global $wpdb;

	$cnt->groups = 0;
	$cnt->forums = 0;
	$cnt->topics = 0;
	$cnt->posts = 0;

	$groupid='';

	$forums = $wpdb->get_results("SELECT group_id, forum_id, topic_count FROM ".SFFORUMS." ORDER BY group_id");
	if($forums)
	foreach($forums as $forum)
	{
		if(sf_can_view_forum($forum->forum_id))
		{
			if($forum->group_id != $groupid)
			{
				$groupid=$forum->group_id;
				$cnt->groups++;
			}
			$cnt->forums++;
			$cnt->topics+=$forum->topic_count;
			$cnt->posts+=$wpdb->get_var("SELECT SUM(post_count) FROM ".SFTOPICS." WHERE forum_id=".$forum->forum_id);
		}
	}
	return $cnt;
}

# ------------------------------------------------------------------
# sf_get_member_post_count()
#
# Returns stats on members
# ------------------------------------------------------------------
function sf_get_member_post_count()
{
	global $wpdb;
	$results = $wpdb->get_results(
			"SELECT SQL_CALC_FOUND_ROWS user_id, display_name, posts
			 FROM ".SFMEMBERS."
			 WHERE moderator=0 AND admin=0
			 ORDER BY posts DESC
			 LIMIT 0,6;");

	$totalrows = $wpdb->get_var("SELECT FOUND_ROWS()");
	update_sfsetting('membercount', $totalrows);
	return $results;
}

# ------------------------------------------------------------------
# sf_get_admins_post_count()
#
# Returns stats on admins and moderators
# ------------------------------------------------------------------
function sf_get_admin_post_count()
{
	global $wpdb;
	$results = $wpdb->get_results(
			"SELECT display_name, posts
			 FROM ".SFMEMBERS."
			 WHERE admin=1
			 ORDER BY display_name");
	return $results;
}

# ------------------------------------------------------------------
# sf_get_mods_post_count()
#
# Returns stats on admins and moderators
# ------------------------------------------------------------------
function sf_get_mods_post_count()
{
	global $wpdb;
	$results = $wpdb->get_results(
			"SELECT display_name, posts
			 FROM ".SFMEMBERS."
			 WHERE moderator=1
			 ORDER BY display_name");
	return $results;
}

# ------------------------------------------------------------------
# sf_get_guest_count()
#
# Returns stats on number of guest posters
# ------------------------------------------------------------------
function sf_get_guest_count()
{
	global $wpdb;
	$guests = $wpdb->get_col("SELECT DISTINCT guest_name FROM ".SFPOSTS." WHERE guest_name IS NOT NULL");
	return count($guests);
}

# ------------------------------------------------------------------
# sf_get_admin_count()
#
# Returns stats on number of admins
# ------------------------------------------------------------------
function sf_get_admin_count()
{
	global $wpdb;
	$admins = $wpdb->get_var("SELECT COUNT(display_name) FROM ".SFMEMBERS." WHERE admin=1");
	return $admins;
}

# ------------------------------------------------------------------
# sf_get_moderator_count()
#
# Returns stats on number of moderators
# ------------------------------------------------------------------
function sf_get_moderator_count()
{
	global $wpdb;
	$moderators = $wpdb->get_var("SELECT COUNT(display_name) FROM ".SFMEMBERS." WHERE moderator=1");
	return $moderators;
}

# ------------------------------------------------------------------
# sf_update_max_online()
#
# Updates max online setting if exceeded
# ------------------------------------------------------------------
function sf_update_max_online($current)
{
	$max = get_sfsetting('maxonline');
	if(empty($max)) $max = 0;

	if($current > $max)
	{
		update_sfsetting('maxonline', $current);
	}
	return;
}

# ******************************************************************
# FULL TOPIC SEARCH VIEW DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_combined_full_topic_search()
#
# Grabs all forums and their topics in the search result.
# Also performs the full search for a members postings
#	$searchvalue:		Search value if in search mode
#	$currentsearchpage:	index to search paging
# ------------------------------------------------------------------
function sf_get_combined_full_topic_search($searchvalue, $currentsearchpage)
{
	global $wpdb, $current_user;

	# how many topics per page?
	$tpaged=get_option('sfpagedtopics');
	if($tpaged < 1) $tpaged=12;

	if($currentsearchpage == 1)
	{
		$startlimit = 0;
	} else {
		$startlimit = ((($currentsearchpage-1) * $tpaged));
	}

	$LIMIT = ' LIMIT '.$startlimit.', '.$tpaged;
	$ORDER = ' ORDER BY topic_id DESC';

	# for admins search all forums, for users check permissions
	if ($current_user->forumadmin)
	{
		$where2 = '';
	} else {
		# limit to viewable forums based on permissions
		$forums = sf_get_viewable_forums($current_user->ID);

		# build a list of forum ids for the query
		if ($forums)
		{
			$forum_ids = '';
			foreach ($forums as $forum)
			{
				$forum_ids[] = $forum->forum_id;
			}
		} else {
			return;
		}

		# create where clause based on forums that current user can view
		$list = explode('@', $list);
		$where2 = " AND ".SFTOPICS.".forum_id IN (" . implode(",", $forum_ids) . ") ";
	}

	$searchvalue=urldecode($searchvalue);
	if(substr($searchvalue, 0, 12) == "sf%members%1")
	{
		$items=explode('%', $searchvalue);
		$userid = substr($items[3], 4, 25);

		$records = $wpdb->get_results(
				"SELECT SQL_CALC_FOUND_ROWS DISTINCT
				 ".SFTOPICS.".topic_id, topic_name, topic_slug, ".SFTOPICS.".forum_id, ".sf_zone_datetime('topic_date').", topic_status, topic_status_flag, ".SFTOPICS.".post_id, post_count,
				 forum_name, forum_slug
				 FROM ".SFTOPICS."
				 JOIN ".SFFORUMS." ON ".SFTOPICS.".forum_id = ".SFFORUMS.".forum_id
				 JOIN ".SFPOSTS." ON ".SFTOPICS.".topic_id = ".SFPOSTS.".topic_id
				 WHERE ".SFPOSTS.".user_id = ".$userid.$where2.$ORDER.$LIMIT.";", ARRAY_A);
	} else if(substr($searchvalue, 0, 12) == "sf%members%2")
	{
		$items=explode('%', $searchvalue);
		$userid = substr($items[3], 4, 25);

		$records = $wpdb->get_results(
				"SELECT SQL_CALC_FOUND_ROWS DISTINCT
				 ".SFTOPICS.".topic_id, topic_name, topic_slug, ".SFTOPICS.".forum_id, ".sf_zone_datetime('topic_date').", topic_status, topic_status_flag, ".SFTOPICS.".post_id, post_count,
				 forum_name, forum_slug
				 FROM ".SFTOPICS."
				 JOIN ".SFFORUMS." ON ".SFTOPICS.".forum_id = ".SFFORUMS.".forum_id
				 WHERE ".SFTOPICS.".user_id = ".$userid.$where2.$ORDER.$LIMIT.";", ARRAY_A);

	} else {
		$searchterm = sf_construct_search_term($searchvalue);

		$records = $wpdb->get_results(
				"SELECT SQL_CALC_FOUND_ROWS DISTINCT
				 ".SFTOPICS.".topic_id, topic_name, topic_slug, ".SFTOPICS.".forum_id, ".sf_zone_datetime('topic_date').", topic_status, topic_status_flag, ".SFTOPICS.".post_id, post_count,
				 forum_name, forum_slug
				 FROM ".SFTOPICS."
				 JOIN ".SFFORUMS." ON ".SFTOPICS.".forum_id = ".SFFORUMS.".forum_id
				 JOIN ".SFPOSTS." ON ".SFTOPICS.".topic_id = ".SFPOSTS.".topic_id
				 WHERE MATCH(".SFPOSTS.".post_content) AGAINST ('".$searchterm."' IN BOOLEAN MODE)
				 ".$where2.$ORDER.$LIMIT.";", ARRAY_A);
	}

	$totalrows = $wpdb->get_var("SELECT FOUND_ROWS()");
	add_sfsetting($searchvalue, $totalrows);

	return $records;
}

# ******************************************************************
# USER RELATED DB FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_create_member_data()
#
# Filter Call
# On user registration sets up the new 'members' data row
#	$userid:		Passed in to filter
# ------------------------------------------------------------------
function sf_create_member_data($userid)
{
	global $wpdb;

	# display name - get new user record and if no dislay name update it with login name
	$u = $wpdb->get_row("SELECT user_login, display_name FROM ".SFUSERS." WHERE ID=".$userid);
	if($u->display_name ? $display_name = stripslashes($u->display_name) : $display_name = stripslashes($u->user_login));
	$display_name = addslashes($display_name);

	# assign user group default
	$ug = get_option('sfdefgroup');
	$wpdb->query("
			INSERT INTO ".SFMEMBERSHIPS."
			(user_id, usergroup_id)
			VALUES (".$userid.", ".$ug.")");

	# moderator status
	$moderator = '0';
	$mod = $wpdb->get_var("SELECT usergroup_is_moderator FROM ".SFUSERGROUPS." WHERE usergroup_id = ".$ug);
	if($mod) $moderator = '1';

	# pm status
	$pm = '0';
	$rids = $wpdb->get_results("SELECT permission_role FROM ".SFPERMISSIONS." WHERE usergroup_id='".$ug."'");
	foreach ($rids as $rid)
	{
		$role_actions = $wpdb->get_var("SELECT role_actions FROM ".SFROLES." WHERE role_id='".$rid->permission_role."'");
		$actions = maybe_unserialize($role_actions);
		if ($actions['Can use private messaging'] == 1) $pm = '1';
	}

	# save initial record
	$sql ="INSERT INTO ".SFMEMBERS." (user_id, display_name, pm, moderator, posts, lastvisit) ";
	$sql.="VALUES ({$userid}, '{$display_name}', {$pm}, {$moderator}, -1, now());";
	$wpdb->query($sql);

	return;
}

# ------------------------------------------------------------------
# sf_delete_member_data()
#
# Filter Call
# On user deletion remove 'members' data row
#	$userid:		Passed in to filter
# ------------------------------------------------------------------
function sf_delete_member_data($userid)
{
	global $wpdb;

	# 1: get users email address
	$user_email = $wpdb->get_var("SELECT user_email FROM ".SFUSERS." WHERE ID=".$userid);

	# 2: get the users display name from members table
	$display_name = sf_get_member_item($userid, 'display_name');

	# 3: Set user name and email to guest name and meail in all of their posts
	$wpdb->query("UPDATE ".SFPOSTS." SET user_id=NULL, guest_name='".$display_name."', guest_email='".$user_email."' WHERE user_id=".$userid);

	# 4: Remove PM messages
	$wpdb->query("DELETE FROM ".SFMESSAGES." WHERE to_id=".$userid." OR from_id=".$userid);

	# 5: Remove subscriptions
	$subs = sf_get_member_item($userid, 'subscribe');
	if($subs)
	{
		$subs = explode('@', $subs);
		foreach($subs as $sub)
		{
			sf_remove_subscription($sub, $userid);
		}
	}

	# 6: Remove watches
	$watches = sf_get_member_item($userid, 'watches');
	if ($watches)
	{
		$watches = explode('@', $watches);
		foreach ($watches as $watch)
		{
			sf_remove_watch($watch, $userid);
		}
	}

	# 7: Remove posts rated
	$ratings = sf_get_member_item($userid, 'posts_rated');
	if ($ratings)
	{
		$ratings = explode('@', $ratings);
		foreach ($ratings as $postid)
		{
			sf_remove_postrated($postid, $userid);
		}
	}

	# 8: Remove from Members table
	$wpdb->query("DELETE FROM ".SFMEMBERS." WHERE user_id=".$userid);

	# 9: Remove user group memberships
	$wpdb->query("DELETE FROM ".SFMEMBERSHIPS." WHERE user_id=".$userid);

	return;
}

# ------------------------------------------------------------------
# sf_get_user_display_name()
#
# returns the display name of a user
#	$userid:		User to lookup
# ------------------------------------------------------------------
function sf_get_user_display_name($userid)
{
	return stripslashes(sf_get_member_item($userid, 'display_name'));
}

# ------------------------------------------------------------------
# sf_track_logout()
#
# Filter Call
# Sets up the last visited upon user logout
# ------------------------------------------------------------------
function sf_track_logout()
{
	global $wpdb, $current_user;

	# re-use this for updating lastvisit (time at logout)
	sf_set_last_visited($current_user->ID);
	$wpdb->query("DELETE FROM ".SFTRACK." WHERE trackuserid=".$current_user->ID);
	sf_destroy_users_newposts($current_user->ID);
	return;
}

# ------------------------------------------------------------------
# sf_get_user_id()
#
# returns the id of a user
#	$display_name:		User to lookup
# ------------------------------------------------------------------
function sf_get_user_id($display_name)
{
	global $wpdb;
	return $wpdb->get_var(
			"SELECT user_id
			 FROM ".SFMEMBERS."
			 WHERE display_name='".$display_name."'");
}

# ------------------------------------------------------------------
# sf_is_subscribed()
#
# determine if user already subscribed to topic
#	$userid:		User being looked up
#	$topicid:		Topic subscribed to
# ------------------------------------------------------------------
function sf_is_subscribed($userid, $topicid)
{
	global $wpdb;

	$list=$wpdb->get_var("SELECT topic_subs FROM ".SFTOPICS." WHERE topic_id=".$topicid);

	if(empty($list))
	{
		return false;
	}
	$found = false;
	$list = explode('@', $list);
	foreach($list as $i)
	{
		if($i == $userid) $found=true;
	}
	return $found;
}

# ------------------------------------------------------------------
# sf_is_watching()
#
# determine if user already watcing a topic
#	$userid:		User being looked up
#	$topicid:		Topic watching
# ------------------------------------------------------------------
function sf_is_watching($userid, $topicid)
{
	global $wpdb;

	$list = sf_get_member_item($userid, 'watches');
	if(empty($list))
	{
		return false;
	}

	$found = false;
	$list = explode('@', $list);
	foreach ($list as $i)
	{
		if ($i == $topicid) $found = true;
	}
	return $found;
}

# ******************************************************************
# SAVE ITEM FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_save_edited_post()
#
# Saves a forum post following an edit in the UI
# Values in POST variables
# ------------------------------------------------------------------
function sf_save_edited_post()
{
	global $wpdb, $current_user;
	# post content
	$postcontent = $_POST['postitem'];
	$postcontent = apply_filters('sf_save_post_content', $postcontent);
	$postcontent = $wpdb->escape($postcontent);

	# post edit array
	$postedit = array();
	$pedit = stripslashes($_POST['pedit']);
	if(!empty($pedit))
	{
		$postedit = unserialize($pedit);
	}
	$x = count($postedit);
	$postedit[$x]['by'] = $current_user->display_name;
	$postedit[$x]['at'] = time();
	$postedit = serialize($postedit);

	$sql = "UPDATE ".SFPOSTS." SET post_content='".$postcontent."', post_edit='".$postedit."' WHERE post_id=".$_POST["pid"];

	if($wpdb->query($sql) === false)
	{
		update_sfnotice('sfmessage', '1@'.__("Update Failed!", "sforum"));
	} else {
		update_sfnotice('sfmessage', '0@'.__("Updated Post Saved", "sforum"));
	}
	return;
}

# ------------------------------------------------------------------
# sf_save_edited_topic()
#
# Saves a topic title following an edit in the UI
# Values in POST variables
# ------------------------------------------------------------------
function sf_save_edited_topic()
{
	global $wpdb;

	$topicname = apply_filters('sf_save_topic_title', $_POST['topicname']);
	$topicname = $wpdb->escape($topicname);

	if(empty($_POST['topicslug']))
	{
		include_once(SF_PLUGIN_DIR.'/sf-slugs.php');
		$topicslug = sf_create_slug($_POST['topicname'], 'topic');
		if(empty($topicslug)) $topicslug = 'topic-'.$_POST['tid'];
	} else {
		$topicslug = $_POST['topicslug'];
	}

	$sql = 'UPDATE '.SFTOPICS.' SET topic_name="'.$topicname.'", topic_slug="'.$topicslug.'" WHERE topic_id='.$_POST['tid'];

	if($wpdb->query($sql) === false)
	{
		update_sfnotice('sfmessage', '1@'.__("Update Failed!", "sforum"));
	} else {
		update_sfnotice('sfmessage', '0@'.__("Updated Topic Title Saved", "sforum"));
	}
	return;
}

# ------------------------------------------------------------------
# sf_save_profile()
#
# Saves a user profile following an edit in the UI
# Values in POST variables
# ------------------------------------------------------------------
function sf_save_profile()
{
	global $wpdb, $current_user;

	include_once('avatars/sf-avatars.php');

	check_admin_referer('forum-userform_profile', 'forum-userform_profile');

	$inc_pw = false;

	# if check field has a value in it return gracefully
	if($_POST['username'] != $current_user->user_login)
	{
		update_sfnotice('sfmessage', '1@'.__('Profile Update Aborted', "sforum"));
		return;
	}
	if(empty($_POST['email']))
	{
		update_sfnotice('sfmessage', '1@'.__('Email Address is Required', "sforum"));
		return;
	}
	if(!is_email($_POST['email']))
	{
		update_sfnotice('sfmessage', '1@'.sprintf(__('%s is an invalid email address', "sforum"), $_POST['email']));
		return;
	} else {
		$email = $wpdb->escape($_POST['email']);
	}
	update_usermeta($current_user->ID, 'first_name', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['first_name']))));
	update_usermeta($current_user->ID, 'last_name', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['last_name']))));

	$current_user->first_name = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['first_name'])));
	$current_user->last_name = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['last_name'])));

	# only the email is required so save the other bits first
	if(get_option('sfextprofile'))
	{
		update_usermeta($current_user->ID, 'aim', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['aim']))));
		update_usermeta($current_user->ID, 'yim', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['yim']))));
		update_usermeta($current_user->ID, 'jabber', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['jabber']))));
		update_usermeta($current_user->ID, 'msn', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['msn']))));
		update_usermeta($current_user->ID, 'skype', wp_specialchars(sf_filter_nohtml_kses($_POST['skype'])));
		update_usermeta($current_user->ID, 'icq', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['icq']))));
		update_usermeta($current_user->ID, 'description', wp_specialchars(sf_filter_nohtml_kses(clean_pre(trim($_POST['description'])))));
		update_usermeta($current_user->ID, 'location', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['location']))));

		$current_user->aim = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['aim'])));
		$current_user->yim = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['yim'])));
		$current_user->jabber = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['jabber'])));
		$current_user->msn = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['msn'])));
		$current_user->skype = wp_specialchars(sf_filter_nohtml_kses($_POST['skype']));
		$current_user->icq = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['icq'])));
		$current_user->description = wp_specialchars(sf_filter_nohtml_kses(clean_pre(trim($_POST['description']))));
		$current_user->location = wp_specialchars(sf_filter_nohtml_kses(trim($_POST['location'])));
	}

	if($current_user->sfusersig)
	{
		global $allowedtags;
		$savedtags = $allowedtags;
		$allowedtags['a'] = array(
			'class' => array (),
			'href' => array (),
			'id' => array (),
			'title' => array (),
			'target' => array());
		$allowedtags['br'] = array ('class' => array ());
		$allowedtags['font'] = array('color' => array ());
		$allowedtags['p'] = array();
		sf_update_member_item($current_user->ID, 'signature', wp_filter_kses(trim($_POST['signature'])));
		$allowedtags = $savedtags;
	}
	if($current_user->sfsigimage)
	{
		if($_POST['sigimage'] != '')
		{
			global $gis_error;
			set_error_handler('sf_gis_error');

			$size = getimagesize($_POST['sigimage']);
			restore_error_handler();
			if ($size)
			{
				$sfsigimagesize = get_option('sfsigimagesize');
				if (($sfsigimagesize['sfsigwidth'] == 0 || $size[0] <= $sfsigimagesize['sfsigwidth']) && ($sfsigimagesize['sfsigheight'] == 0 || $size[1] <= $sfsigimagesize['sfsigheight']))
				{
					sf_update_member_item($current_user->ID, 'sigimage', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['sigimage']))));
				} else {
					$message = '';
					if ($sfsigimagesize['sfsigwidth'] != 0 && $size[0] > $sfsigimagesize['sfsigwidth'])
					{
						$message = sprintf(__('Signature Width Exceeds Forum Limit of %s ', 'sforum'), $sfsigimagesize['sfsigwidth']);
					}
					if ($sfsigimagesize['sfsigheight'] != 0 && $size[1] > $sfsigimagesize['sfsigheight'])
					{
						if ($message) $message.= ' '.__('and', "sforum").' ';
						$message.= sprintf(__('Signature Height Exceeds Forum Limit of %s ', 'sforum'), $sfsigimagesize['sfsigheight']);
					}
					update_sfnotice('sfmessage', '1@'.$message);
					return;
				}
			} else {
				if($gis_error)
				{
					$mess = ' * '.$gis_error.' * ';
					$gis_error='';
					sf_update_member_item($current_user->ID, 'sigimage', wp_specialchars(sf_filter_nohtml_kses(trim($_POST['sigimage']))));
				} else {
					update_sfnotice('sfmessage', '1@'.__('Invalid Signature Image File!', "sforum"));
				}
			}
		} else {
			sf_update_member_item($current_user->ID, 'sigimage', '');
		}
	}
	if(isset($_POST['editor']))
	{
		sf_update_member_item($current_user->ID, 'editor', $_POST['editor']);
	}

	if(!empty($_POST['url']))
	{
		$url=clean_url($_POST['url']);
		$url=$wpdb->escape($_POST['url']);
	}
	$display_name = stripslashes(wp_specialchars(sf_filter_nohtml_kses(trim($_POST['display_name']))));
	if(empty($display_name)) $display_name = stripslashes($current_user->user_login);

	if(!empty($_POST['newone1']))
	{
		if((empty($_POST['newone1'])) || (empty($_POST['newone2'])))
		{
			update_sfnotice('sfmessage', '1@'.__('New Password must be Entered Twice', "sforum"));
			return;
		}
		if($_POST['newone1'] != $_POST['newone2'])
		{
			update_sfnotice('sfmessage', '1@'.__('The Two New Passwords entered are Not the Same!', "sforum"));
			return;
		}
		# OK to save new pw
		$newp = wp_hash_password($_POST['newone1']);
		$inc_pw = true;
	}

	$sql = 'UPDATE '.SFUSERS.' SET ';
	$sql.= 'user_url="'.$url.'", ';
	$sql.= 'user_email="'.$email.'" ';
	if($inc_pw)
	{
		$sql.= ', user_pass="'.$newp.'" ';
	}
	$sql.= 'WHERE ID='.$current_user->ID.';';

	$wpdb->query($sql);

	sf_update_member_item($current_user->ID, 'display_name', $display_name);

	$current_user->user_url = $url;
	$current_user->user_email = $email;
	$current_user->dislay_name = $display_name;

	# update custom fields if they exist
	if (isset($_POST['cfcount'])) $cfcount = $_POST['cfcount']; else $cfcount = 0;
	for( $x=0; $x<$cfcount; $x++)
	{
		update_usermeta($current_user->ID, 'sfcustomfield'.$x, $_POST['cfield'.$x]);
	}

	# save hook
	if(function_exists('sf_hook_profile_save'))
	{
		sf_hook_profile_save($current_user->ID);
	}

	$mess = __("Profile Updated. ", "sforum");
	if($_FILES['avatar']['error'] == 4)
	{
		update_sfnotice('sfmessage', '0@'.__('Profile Record: ', "sforum").$mess);
	} else {
		update_sfnotice('sfmessage', sf_upload_avatar($current_user->ID).__(' - Profile Record: ', "sforum").$mess);
	}

	return;
}

# ------------------------------------------------------------------
# sf_save_subscription()
#
# Saves a user subscription following an edit in the UI
# 	$topicid:		The topic being subscribed to
#	$userid:		The user (passed because used in upgrade flow)
#	$retmessage:	True/False: Whether to retrun message (for UI)
# ------------------------------------------------------------------
function sf_save_subscription($topicid, $userid, $retmessage)
{
	global $wpdb, $current_user;

	if(($current_user->guest) || ($current_user->sfsubscriptions == false)) return;

	# is user already subscribed to this topic?
	if(sf_is_subscribed($userid, $topicid))
	{
		if($retmessage)
		{
			update_sfnotice('sfmessage', '1@'.__('You are already subscribed to this topic', "sforum"));
			return;
		}
	}

	# OK  -subscribe them to the topic
	$list=$wpdb->get_var("SELECT topic_subs FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if(empty($list))
	{
		$list = $userid;
	} else {
		$list.= '@'.$userid;
	}
	$wpdb->query("UPDATE ".SFTOPICS." SET topic_subs = '".$list."' WHERE topic_id=".$topicid);

	# plus note the topic against their usermeta record
	$list = sf_get_member_item($userid, 'subscribe');

	if(empty($list))
	{
		$list = $topicid;
	} else {
		$list.= '@'.$topicid;
	}
	sf_update_member_item($userid, 'subscribe', $list);

	if($retmessage)
	{
		update_sfnotice('sfmessage', '0@'.__('Subscription added', "sforum"));
	}
	return;
}

# ------------------------------------------------------------------
# sf_update_subscriptions()
#
# Update subscriptions from the UI - current user
# Values in POST variables
# ------------------------------------------------------------------
function sf_update_subscriptions()
{
	global $current_user;

	check_admin_referer('forum-userform_subs', 'forum-userform_subs');

	# do it the easy way - remove everything and then rebuild list
	$list = sf_get_member_item($current_user->ID, 'subscribe');
	if(!empty($list))
	{
		$list = explode('@', $list);
		foreach($list as $topic)
		{
			sf_remove_subscription($topic, $current_user->ID);
		}
		sf_update_member_item($current_user->ID, 'subscribe', '');
	}
	if(!empty($_POST['topic']))
	{
		foreach($_POST['topic'] as $topic)
		{
			sf_save_subscription($topic, $current_user->ID, false);
		}
	}
	update_sfnotice('sfmessage', '0@'.__('Subscriptions Updated', "sforum"));
	return;
}

# ------------------------------------------------------------------
# sf_save_watch()
#
# Saves a user watch topic request following an edit in the UI
# 	$topicid:		The topic being watched
#	$userid:		The user (passed because used in upgrade flow)
#	$retmessage:	True/False: Whether to retrun message (for UI)
# ------------------------------------------------------------------
function sf_save_watch($topicid, $userid, $retmessage)
{
	global $wpdb, $current_user;

	if (($current_user->guest) || ($current_user->sfwatch == false)) return;

	# is user already watching this topic?
	if (sf_is_watching($userid, $topicid))
	{
		if ($retmessage)
		{
			update_sfnotice('sfmessage', '1@'.__('You are already watching this topic', "sforum"));
			return;
		}
	}

	# OK - watch the topic (in members table)
	$list = sf_get_member_item($userid, 'watches');
	if (empty($list))
	{
		$list = $topicid;
	} else {
		$list.= '@'.$topicid;
	}
	sf_update_member_item($userid, 'watches', $list);

	# OK  -subscribe them to the topic
	$list = $wpdb->get_var("SELECT topic_watches FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if (empty($list))
	{
		$list = $userid;
	} else {
		$list.= '@'.$userid;
	}
	$wpdb->query("UPDATE ".SFTOPICS." SET topic_watches = '".$list."' WHERE topic_id=".$topicid);

	if ($retmessage)
	{
		update_sfnotice('sfmessage', '0@'.__('Topic watch added', "sforum"));
	}

	return;
}

# ------------------------------------------------------------------
# sf_end_watch()
#
# Update watches from the UI - current user
# Values in POST variables
# ------------------------------------------------------------------
function sf_end_watch()
{
	global $current_user, $wpdb;

	$topic = sf_syscheckint($_GET['topic']);

	# remove current watch from member
	$list = sf_get_member_item($current_user->ID, 'watches');
	if (!empty($list))
	{
		$newlist = '';
		$list = explode('@', $list);
		foreach($list as $topicid)
		{
			if ($topic != $topicid)
			{
				if (empty($newlist))
				{
					$newlist = $topicid;
				} else {
					$newlist.= '@'.$topicid;
				}
			}
		}
		sf_update_member_item($current_user->ID, 'watches', $newlist);
	}

	#remove the topic subscription
	$list = $wpdb->get_var("SELECT topic_watches FROM ".SFTOPICS." WHERE topic_id=".$topic);
	if (!empty($list))
	{
		$newlist = '';
		$list = explode('@', $list);
		foreach ($list as $userid)
		{
			if ($current_user->ID != $userid)
			{
				if (empty($newlist))
				{
					$newlist = $userid;
				} else {
					$newlist.= '@'.$userid;
				}
			}
		}
		$list = $wpdb->query("UPDATE ".SFTOPICS." SET topic_watches ='".$newlist."' WHERE topic_id=".$topic);
	}

	update_sfnotice('sfmessage', '0@'.__('Watches Updated', "sforum"));

	return;
}

# ------------------------------------------------------------------
# sf_remove_watch($topic, $userid)
#
# removes the topic watch for the specified user
# $topic			topic to be removed
# $userid			user to have watche removed
# ------------------------------------------------------------------
function sf_remove_watch($topic, $userid)
{
	global $wpdb;

	#remove the topic subscription
	$list = $wpdb->get_var("SELECT topic_watches FROM ".SFTOPICS." WHERE topic_id=".$topic);
	if (!empty($list))
	{
		$newlist = '';
		$list = explode('@', $list);
		foreach ($list as $user)
		{
			if ($userid != $user)
			{
				if (empty($newlist))
				{
					$newlist = $user;
				} else {
					$newlist.= '@'.$user;
				}
			}
		}
		$list = $wpdb->query("UPDATE ".SFTOPICS." SET topic_watches ='".$newlist."' WHERE topic_id=".$topic);
	}

	return;
}

# ------------------------------------------------------------------
# sf_add_postrating_vote()
#
# Saves a user watch topic request following an edit in the UI
# 	$postid:		The post being voted on
# ------------------------------------------------------------------
function sf_add_postrating_vote($postid)
{
	global $current_user;

	# record the post as voted (in members table)
	$list = sf_get_member_item($current_user->ID, 'posts_rated');
	if (empty($list))
	{
		$list = $postid;
	} else {
		$list.= '@'.$postid;
	}
	sf_update_member_item($current_user->ID, 'posts_rated', $list);

	return;
}

# ------------------------------------------------------------------
# sf_remove_postrated($topic, $userid)
#
# removes the post rated id for the specified user
# $postid			postid to be removed
# $userid			user to have watche removed
# ------------------------------------------------------------------
function sf_remove_postrated($postid, $userid)
{
	global $wpdb;

	#remove the member id from post rated
	$list = $wpdb->get_var("SELECT members FROM ".SFPOSTRATINGS." WHERE post_id=".$postid);
	if (!empty($list))
	{
		$newlist = null;
		$list = unserialize($list);
		foreach ($list as $user)
		{
			if ($userid != $user)
			{
				$newlist[] = $user;
			}
		}
		if ($newlist) $newlist = serialize($newlist);
		$list = $wpdb->query("UPDATE ".SFPOSTRATINGS." SET members ='".$newlist."' WHERE post_id=".$postid);
	}

	return;
}

# ------------------------------------------------------------------
# sf_move_topic()
#
# Move topic from one forum to another
# Values in POST variables
# ------------------------------------------------------------------
function sf_move_topic()
{
	# done
	global $wpdb, $current_user;

	if(!$current_user->sfmovetopics)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}

	if(empty($_POST['forumid']))
	{
		update_sfnotice('sfmessage', '1@'.__('Destination Forum not Selected', "sforum"));
		return;
	}

	$currentforumid = sf_syscheckint($_POST['currentforumid']);
	$currenttopicid = sf_syscheckint($_POST['currenttopicid']);
	$targetforumid  = sf_syscheckint($_POST['forumid']);

	# change topic record to new forum id
	$wpdb->query("UPDATE ".SFTOPICS." SET forum_id = ".$targetforumid." WHERE topic_id=".$currenttopicid);

	if($wpdb === false)
	{
		update_sfnotice('sfmessage', '1@'.__("Topic Move Failed", "sforum"));
		return;
	}

	# check in 'waiting' to see if there is an unread post in there and change forum id if there is
	$wpdb->query("UPDATE ".SFWAITING." SET forum_id = ".$targetforumid." WHERE topic_id=".$currenttopicid);

	# change posts record(s) to new forum
	$wpdb->query("UPDATE ".SFPOSTS." SET forum_id = ".$targetforumid." WHERE topic_id=".$currenttopicid);

	# rebuild forum counts for old and new forums
	sf_build_forum_index($currentforumid);
	sf_build_forum_index($targetforumid);

	# Ok - do not like doing this but....
	# There seems to have been times when a new post is made to the old forum id so we will now double check...
	$checkposts = $wpdb->get_results("SELECT post_id FROM ".SFPOSTS." WHERE forum_id=".$currentforumid." AND topic_id=".$currenttopicid);
	if($checkposts)
	{
		# made after most were moved
		sf_move_topic();
	} else {
		if($wpdb === false)
		{
			update_sfnotice('sfmessage', '1@'.__("Topic Move Failed", "sforum"));
		} else {
			update_sfnotice('sfmessage', '0@'.__("Topic Moved", "sforum"));
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_move_post()
#
# Move post from one topic and create new topic -same/another forum
# Values in POST variables
# ------------------------------------------------------------------
function sf_move_post()
{
	global $wpdb, $current_user;

	include_once(SF_PLUGIN_DIR.'/sf-slugs.php');

	if(!$current_user->sfmoveposts)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}
	if(empty($_POST['forumid']))
	{
		update_sfnotice('sfmessage', '1@'.__("Post move abandoned - No Forum selected!", "sforum"));
		return;
	}
	if(empty($_POST['newtopicname']))
	{
		update_sfnotice('sfmessage', '1@'.__("Post move abandoned - No Topic Defined!", "sforum"));
		return;
	}
	# extract data from POST
	$postid     = $_POST['postid'];
	$oldtopicid = $_POST['oldtopicid'];
	$oldforumid = $_POST['oldforumid'];
	$newforumid = $_POST['forumid'];
	$newtopicname  = sf_syscheckstr($_POST['newtopicname']);
	$newtopicname = apply_filters('sf_save_topic_title', $newtopicname);
	$newtopicname = $wpdb->escape($newtopicname);

	# start with creating the new topic
	$newtopicslug = sf_create_slug($newtopicname, 'topic');

	# now create the topic and post records
	$wpdb->query(
		"INSERT INTO ".SFTOPICS."
		 (topic_name, topic_slug, topic_date, forum_id, post_count, post_id)
		 VALUES
		 ('".$newtopicname."', '".$newtopicslug."', now(), ".$newforumid.", 1, ".$postid.");");

	if($wpdb === false)
	{
		update_sfnotice('sfmessage', '1@'.__("Post Move Failed", "sforum"));
		return;
	}
	$newtopicid = $wpdb->insert_id;

	# check the topic slug and if empty use the topic id
	if(empty($newtopicslug))
	{
		$newtopicslug = 'topic-'.$newtopicid;
		$thistopic = $wpdb->query("UPDATE ".SFTOPICS." SET topic_slug='".$slug."' WHERE topic_id=".$newtopicid);
	}

	# now check if old topic had just the one post and if so remove it
	$check = $wpdb->get_var("SELECT post_count FROM ".SFTOPICS." WHERE topic_id=".$oldtopicid);
	if($check == 1)
	{
		$wpdb->query("DELETE FROM ".SFTOPICS." WHERE topic_id=".$oldtopicid);
	}

	# update post record
	$wpdb->query(
		"UPDATE ".SFPOSTS."
		 SET topic_id=".$newtopicid.", forum_id=".$newforumid.", post_index=1, post_status=0
		 WHERE post_id=".$postid);

	# if old topic was in the admin queue then remove it. Assume it's read
	sf_remove_from_waiting(true, $oldtopicid, 0);

	# rebuild forum counts for old and new forums
	sf_build_forum_index($oldforumid);
	sf_build_forum_index($newforumid);
	sf_build_post_index($oldtopicid, sf_get_topic_slug($oldtopicid));

	if($wpdb == false)
	{
		update_sfnotice('sfmessage', '1@'.__("Post Move Failed", "sforum"));
	} else {
		update_sfnotice('sfmessage', '0@'.__("Post Moved", "sforum"));
	}
	return;
}


function sf_change_topic_status()
{
	global $wpdb, $current_user;

	if(!$current_user->sfedit)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}
	$topicid= $_POST['id'];
	$statvalue = $_POST['statvalue'];

	sf_update_topic_status_flag($statvalue, $topicid);

	update_sfnotice('sfmessage', '0@'.__("Topic Status Changed", "sforum"));
	return;
}

# ------------------------------------------------------------------
# sf_update_opened()
#
# Updates the number of times a topic is viewed
# 	$topicid:		The topic being opened for view
# ------------------------------------------------------------------
function sf_update_opened($topicid)
{
	global $wpdb, $sfvars;

	if(empty($topicid)) return;

	$ref=array();
	$ref=explode('/', $_SERVER['HTTP_REFERER']);
	$ref_topic = $ref[count($ref)-3];
	if(substr($ref_topic,0,5) == 'page-') $ref_topic = $ref[count($ref)-4];
	if($ref_topic == $sfvars['topicslug']) return;

	$current=$wpdb->get_var("SELECT topic_opened FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	$current++;
	$wpdb->query("UPDATE ".SFTOPICS." SET topic_opened = ".$current." WHERE topic_id=".$topicid);
	return;
}

# ******************************************************************
# DELETE ITEM FUNCTIONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_delete_topic()
#
# Delete a topic and all it;s posts
# 	$topicid:		The topic being subscribed to
#	$show:			True/False: Whether to return message (for UI)
# ------------------------------------------------------------------
function sf_delete_topic($topicid, $show=true)
{
	global $wpdb, $current_user;

	if(!$current_user->sfdelete)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}

	# We need to check for subscriptions on this topic
	$subs = $wpdb->get_var("SELECT topic_subs FROM ".SFTOPICS." WHERE  topic_id = ".$topicid);

	# Any subscriptions to remopve from user records?
	if($subs)
	{
		$userlist = explode('@', $subs);
		foreach($userlist as $user)
		{
			$subslist = sf_get_member_item($user, 'subscribe');
			if(!empty($subslist))
			{
				$newlist = array();
				$topiclist = explode('@', $subslist);
				foreach($topiclist as $topic)
				{
					if($topic != $topicid) $newlist[]=$topic;
				}
				$newlist = implode('@', $newlist);
				sf_update_member_item($user, 'subscribe', $newlist);
			}
		}
	}

	# Any watches to remove?
	$watches = $wpdb->get_var("SELECT topic_watches FROM ".SFTOPICS." WHERE  topic_id = ".$topicid);
	if ($watches)
	{
		$userlist = explode('@', $watches);
		foreach ($userlist as $user)
		{
			$list = sf_get_member_item($user, 'watches');
			if (!empty($list))
			{
				$newlist = array();
				$topiclist = explode('@', $list);
				foreach ($topiclist as $topic)
				{
					if ($topic != $topicid) $newlist[] = $topic;
				}
				$newlist = implode('@', $newlist);
				sf_update_member_item($user, 'watches', $newlist);
			}
		}
	}

	# check of there is a post link to it?
	$row = $wpdb->get_row("SELECT blog_post_id, forum_id FROM ".SFTOPICS." WHERE topic_id = ".$topicid);
	if($row->blog_post_id != 0)
	{
		# break the link
		sf_blog_links_postmeta('delete', $row->blog_post_id, '');
	}

	# delete from waiting just in case
	$wpdb->query("DELETE FROM ".SFWAITING." WHERE topic_id=".$topicid);

	# now delete from topic
	$wpdb->query("DELETE FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if($wpdb === false)
	{
		if($show) update_sfnotice('sfmessage', '1@'.__("Deletion Failed", "sforum"));
		return;
	}

	# topic delete hook
	if(function_exists('sf_hook_topic_delete'))
	{
		# grab the forum id
		$forumid = $wpdb->get_var("SELECT forum_id FROM ".SFTOPICS." WHERE  topic_id = ".$topicid);
		sf_hook_topic_delete($topicid, $forumid);
	}

	# now delete all the posts on the topic
	$wpdb->query("DELETE FROM ".SFPOSTS." WHERE topic_id=".$topicid);
	if($wpdb == false)
	{
		if($show) update_sfnotice('sfmessage', '1@'.__("Deletion of Posts in Topic Failed", "sforum"));
	} else {
		if($show) update_sfnotice('sfmessage', '0@'.__("Topic Deleted", "sforum"));
	}

	# delete from forums topic count
	sf_build_forum_index($row->forum_id);

	return;
}

# ------------------------------------------------------------------
# sf_delete_post()
#
# Delete a post
#	$postid:		The post to be deleted
# 	$topicid:		The topic post belongs to
#	$forumid:		The forum post belongs to
# ------------------------------------------------------------------
function sf_delete_post($postid, $topicid, $forumid, $show=true)
{
	global $wpdb, $current_user;

	if(!$current_user->sfdelete)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}

	# if just one post then remove topic as well
	if(sf_get_posts_count_in_topic($topicid) == 1)
	{
		sf_delete_topic($topicid);
	} else {
		$wpdb->query("DELETE FROM ".SFPOSTS." WHERE post_id=".$postid);
		if($wpdb === false)
		{
			if($show) update_sfnotice('sfmessage', '1@'.__("Deletion Failed", "sforum"));
		} else {
			if($show) update_sfnotice('sfmessage', '0@'.__("Post Deleted", "sforum"));
		}
		# re number post index
		sf_build_post_index($topicid, sf_get_topic_slug($topicid));
		sf_build_forum_index($forumid);
	}

	# post delete hook
	if(function_exists('sf_hook_post_delete'))
	{
		sf_hook_post_delete($postid, $topicid, $forumid);
	}

	# need to look in sfwaiting to see if it's in there...
	sf_remove_from_waiting(true, $topicid, $postid);

	return;
}

# ------------------------------------------------------------------
# sf_remove_subscription()
#
# Removes a user subscription following edit or topic delete
# 	$topicid:		The topic being unsubscribed from
#	$userid:		The user (passed because used in upgrade flow)
# ------------------------------------------------------------------
function sf_remove_subscription($topicid, $userid)
{
	global $wpdb;

	$list = $wpdb->get_var("SELECT topic_subs FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if($list == $userid)
	{
		$newlist = '';
	} else {
		$list = explode('@', $list);
		foreach($list as $i)
		{
			if($i != $userid)
			{
				$newlist = $i.'@';
			}
		}
		$newlist = substr($newlist, 0, strlen($newlist)-1);
	}
	$wpdb->query("UPDATE ".SFTOPICS." SET topic_subs = '".$newlist."' WHERE topic_id=".$topicid);
	return;
}

# ******************************************************************
# ADMIN TOOL ICONS
# ******************************************************************

# ------------------------------------------------------------------
# sf_icon_toggle()
#
# Toggle Tool Icon State
# ------------------------------------------------------------------
function sf_icon_toggle()
{
	global $sfglobals;

	$sfadminsettings=array();
	$sfadminsettings=get_option('sfadminsettings');
	$state=$sfadminsettings['sftools'];
	if($state ? $state=false : $state=true);
	$sfadminsettings['sftools'] = $state;
	update_option('sfadminsettings', $sfadminsettings);
	$sfglobals['admin']['sftools']=$state;
	return;
}

# ------------------------------------------------------------------
# sf_lock_topic_toggle()
#
# Toggle Topic Lock
#	Topicid:		Topic to lock/unlock
# ------------------------------------------------------------------
function sf_lock_topic_toggle($topicid)
{
	global $wpdb, $current_user;

	if(!$current_user->sflock)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}

	if($_POST['locktopicaction'].$topicid == get_sfsetting('sfaction')) return;

	$status = $wpdb->get_var("SELECT topic_status FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if($status == 1 ? $status=0 : $status=1);

	$wpdb->query("UPDATE ".SFTOPICS." SET topic_status = ".$status." WHERE topic_id=".$topicid);
	if($wpdb == false)
	{
		update_sfnotice('sfmessage', '1@'.__("Topic Lock Toggle Failed", "sforum"));
	} else {
		update_sfnotice('sfmessage', '0@'.__("Topic Lock Toggled", "sforum"));
		update_sfsetting('sfaction', $_POST['locktopicaction'].$topicid);
	}
	return;
}

# ------------------------------------------------------------------
# sf_pin_topic_toggle()
#
# Toggle Topic Pin
#	Topicid:		Topic to pin/unpin
# ------------------------------------------------------------------
function sf_pin_topic_toggle($topicid)
{
	global $wpdb, $current_user;

	if(!$current_user->sfpin)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}

	if($_POST['pintopicaction'].$topicid == get_sfsetting('sfaction')) return;

	$status = $wpdb->get_var("SELECT topic_pinned FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if($status == 1 ? $status=0 : $status=1);

	$wpdb->query("UPDATE ".SFTOPICS." SET topic_pinned = ".$status." WHERE topic_id=".$topicid);
	if($wpdb == false)
	{
		update_sfnotice('sfmessage', '1@'.__("Topic Pin Toggle Failed", "sforum"));
	} else {
		update_sfnotice('sfmessage', '0@'.__("Topic Pin Toggled", "sforum"));
		update_sfsetting('sfaction', $_POST['pintopicaction'].$topicid);
	}
	return;
}

# ------------------------------------------------------------------
# sf_sort_topic_toggle()
#
# Toggle Topic Sort
#	Topicid:		Topic to switch sort
# ------------------------------------------------------------------
function sf_sort_topic_toggle($topicid)
{
	global $wpdb, $current_user;

	if(!$current_user->sfsort)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}
	if($_POST['sorttopicaction'].$topicid == get_sfsetting('sfaction')) return;

	$currentsort='ASC';
	if(get_option('sfsortdesc')) $currentsort='DESC';
	$overridesort=$wpdb->get_var("SELECT topic_sort FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if(!is_null($overridesort)) $currentsort=$overridesort;

	if($currentsort == 'ASC' ? $newsort='DESC' : $newsort='ASC');
	$wpdb->query("UPDATE ".SFTOPICS." SET topic_sort = '".$newsort."' WHERE topic_id=".$topicid);
	if($wpdb == false)
	{
		update_sfnotice('sfmessage', '1@'.__("Topic Sort Toggle Failed", "sforum"));
	} else {
		update_sfnotice('sfmessage', '0@'.__("Topic Sort Toggled", "sforum"));
		update_sfsetting('sfaction', $_POST['sorttopicaction'].$topicid);
	}
	return;
}

# ------------------------------------------------------------------
# sf_pin_post_toggle()
#
# Toggle Post Pin
#	postid:		Post to pin/unpin
# ------------------------------------------------------------------
function sf_pin_post_toggle($postid)
{
	global $wpdb, $current_user;

	if(!$current_user->sfpin)
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}

	if($_POST['pinpostaction'].$postid == get_sfsetting('sfaction')) return;

	$status = $wpdb->get_var("SELECT post_pinned FROM ".SFPOSTS." WHERE post_id=".$postid);
	if($status == 1 ? $status = 0 : $status = 1);

	$wpdb->query("UPDATE ".SFPOSTS." SET post_pinned = ".$status." WHERE post_id=".$postid);
	if($wpdb == false)
	{
		update_sfnotice('sfmessage', '1@'.__("Post Pin Toggle Failed", "sforum"));
	} else {
		update_sfnotice('sfmessage', '0@'.__("Post Pin Toggled", "sforum"));
		update_sfsetting('sfaction', $_POST['pinpostaction'].$postid);
	}
	return;
}

# ******************************************************************
# DATA INTEGRITY MANAGEMENT
# ******************************************************************

# ------------------------------------------------------------------
# sf_build_post_index()
#
# Rebuilds the post index column (post sequence) and also sets the
# last post id and post count into the parent topic record
#	$topicid:		topic whose posts are being re-indexed
#	$topicslug:		slug to check sort order
# ------------------------------------------------------------------
function sf_build_post_index($topicid, $topicslug, $returnmsg=false)
{
	global $wpdb;

	# get topic posts is their display order
	$posts=$wpdb->get_results("SELECT post_id, post_index FROM ".SFPOSTS." WHERE topic_id = ".$topicid." ORDER BY post_pinned DESC, post_id ASC");
	if($posts)
	{
		$index = 1;
		foreach($posts as $post)
		{
			# update the post_index for each post to set display order
			$wpdb->query("UPDATE ".SFPOSTS." SET post_index = ".$index." WHERE post_id = ".$post->post_id);
			$index++;
		}
		$lastpost = $post->post_id;
	} else {
		$lastpost = 'NULL';
	}
	# update the topic with the last post id and the post count
	$wpdb->query("UPDATE ".SFTOPICS." SET post_id=".$lastpost.", post_count=".($index-1)." WHERE topic_id=".$topicid);

	if($returnmsg) update_sfnotice('sfmessage', '0@'.__("Verification Complete", "sforum"));

	return;
}

# ------------------------------------------------------------------
# sf_build_forum_index()
#
# Rebuilds the topic count and last post id in a forum record
#	$forumid:		forum needing updating
# ------------------------------------------------------------------
function sf_build_forum_index($forumid, $returnmsg=false)
{
	global $wpdb;

	# get the topic count for this forum
	$topiccount = $wpdb->get_var("SELECT COUNT(topic_id) FROM ".SFTOPICS." WHERE forum_id=".$forumid);
	# get the last post that appeared in a topic within this forum
	$postid =  $wpdb->get_var("SELECT post_id FROM ".SFPOSTS." WHERE forum_id = ".$forumid." ORDER BY post_id DESC LIMIT 1");

	if(!$topiccount) $topiccount = 0;
	if(!isset($postid)) $postid = 'NULL';

	# update forum record
	$wpdb->query("UPDATE ".SFFORUMS." SET post_id=".$postid.", topic_count=".$topiccount." WHERE forum_id=".$forumid);

	if($returnmsg) update_sfnotice('sfmessage', '0@'.__("Verification Complete", "sforum"));

	return;
}

function sf_get_user_memberships($user_id)
{
	global $wpdb;

	$sql = "SELECT ".SFMEMBERSHIPS.".usergroup_id, usergroup_name, usergroup_desc
			FROM ".SFMEMBERSHIPS."
			LEFT JOIN ".SFUSERGROUPS." ON ".SFUSERGROUPS.".usergroup_id = ".SFMEMBERSHIPS.".usergroup_id
			WHERE user_id=".$user_id;
	return $wpdb->get_results($sql, ARRAY_A);
}

function sf_add_membership($usergroup_id, $user_id)
{
	global $wpdb;


	$sql ="INSERT INTO ".SFMEMBERSHIPS." (user_id, usergroup_id) ";
	$sql.="VALUES ('".$user_id."', '".$usergroup_id."');";

	return $wpdb->query($sql);
}

function sf_check_membership($usergroup_id, $user_id)
{
	global $wpdb;

	$sql = "SELECT usergroup_id
			FROM ".SFMEMBERSHIPS."
			WHERE user_id=".$user_id." AND usergroup_id=".$usergroup_id;
	return $wpdb->get_results($sql, ARRAY_A);
}

function sf_rebuild_members_pm($userid='')
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
				$canpm = '0';
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
								$canpm = '1';
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

# ******************************************************************
# COMPLETE TABLE AND DATA REMOVAL
# ******************************************************************

# ------------------------------------------------------------------
# sf_remove_data()
#
# Removes all forum data prior to deactivation
# ------------------------------------------------------------------
function sf_remove_data()
{
	global $wpdb;

	if(get_option('sfuninstall'))
	{
		# remove any admin capabilities
		$admins = $wpdb->get_results("SELECT user_id FROM ".SFMEMBERS." WHERE admin=1");
		foreach ($admins as $admin)
		{
			$user = new WP_User($admin->user_id);
			$user->remove_cap('SPF Manage Options');
			$user->remove_cap('SPF Manage Forums');
			$user->remove_cap('SPF Manage User Groups');
			$user->remove_cap('SPF Manage Permissions');
			$user->remove_cap('SPF Manage Database');
			$user->remove_cap('SPF Manage Components');
			$user->remove_cap('SPF Manage Admins');
			$user->remove_cap('SPF Manage Users');
		}

		# First remove tables
		$wpdb->query("DROP TABLE IF EXISTS ".SFGROUPS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFFORUMS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFTOPICS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFPOSTS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFWAITING);
		$wpdb->query("DROP TABLE IF EXISTS ".SFTRACK);
		$wpdb->query("DROP TABLE IF EXISTS ".SFSETTINGS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFNOTICE);
		$wpdb->query("DROP TABLE IF EXISTS ".SFMESSAGES);
		$wpdb->query("DROP TABLE IF EXISTS ".SFUSERGROUPS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFPERMISSIONS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFROLES);
		$wpdb->query("DROP TABLE IF EXISTS ".SFMEMBERS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFMEMBERSHIPS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFMETA);
		$wpdb->query("DROP TABLE IF EXISTS ".SFPOSTRATINGS);
		$wpdb->query("DROP TABLE IF EXISTS ".SFDEFPERMISSIONS);

		# Remove the Page record
		$sfpage = get_option('sfpage');
		if(!empty($sfpage))
		{
			$wpdb->query("DELETE FROM ".$wpdb->prefix."posts WHERE ID=".get_option('sfpage'));
			$wpdb->query("DELETE FROM ".$wpdb->prefix."postmeta WHERE post_id=".get_option('sfpage'));
		}

		# And remove option records
		$optionlist = array('sfversion', 'sfbuild', 'sfpage', 'sfslug', 'sfedit', 'sfsearch', 'sfsmilies', 'sfnotify', 'sfpagedtopics', 'sfuninstall', 'sfsortdesc', 'sfmessage', 'sfshownewadmin', 'sfdates', 'sftimes', 'sfzone', 'sfshowavatars', 'sfuserabove', 'sfskin', 'sficon', 'sfshowicon', 'sftopicsort', 'sfavatarsize', 'sfquicktags', 'sfpermalink', 'sfextprofile', 'sfhome', 'sftpage', 'sfaction', 'sfrsscount', 'sfrsswords', 'sfpagedposts', 'sfforumcols', 'sftopiccols', 'sfppage', 'sftitle', 'sfgravatar', 'sfstats', 'sfshowlogin', 'sfsearchbar', 'sflinkexcerpt', 'sflinkwords', 'sflinkblogtext', 'sflinkforumtext', 'sflinkabove', 'sfuseannounce', 'sfannouncecount', 'sfannouncehead', 'sfannounceauto', 'sfannouncetime', 'sfannouncetext', 'sfannouncelist', 'sfshowhome', 'sfshowbreadcrumbs', 'sflockdown', 'sfshowmodposts', 'sfimgenlarge', 'sfthumbsize', 'sfmodasadmin', 'sfdemocracy', 'sfuppath', 'sfmail', 'sfnewusermail', 'sfdefgroup', 'sfbadwords', 'sfreplacementwords', 'sfpm', 'sfpaging','sfpostpaging', 'sfcustom', 'sfadminbar', 'sfeditormsg', 'sfpostmsg', 'sfquicklinks', 'sfautoupdate', 'sfcheck', 'sfstyle', 'sflogin', 'sfadminsettings', 'sfauto', 'sffilters', 'sfpmemail', 'sfpmmax', 'sfpostpaging', 'sfeditor', 'sfsmileys', 'sfusersnewposts', 'sfguestsgroup', 'sfpostratings', 'sfinstallsm', 'sfinstallav', 'sflinkcomments', 'sfshoweditdata', 'sfavataruploads', 'sfprivatemessaging', 'sfshoweditlast', 'sfsingleforum', 'sftaggedforum', 'sfaiosp', 'sfsigimagesize', 'sfmemberlistperms', 'sfgmaxrating', 'sfcbexclusions', 'sfshowmemberlist', 'sfwpavatar', 'sfcheckformember');
		foreach($optionlist as $option)
		{
			delete_option($option);
		}

		# Now remove user meta data
		$optionlist = array("sfadmin", "location", "msn", "skype", "icq", "sfuse_quicktags", "signature", "sigimage");
		foreach($optionlist as $option)
		{
			$wpdb->query("DELETE FROM ".SFUSERMETA." WHERE meta_key='".$option."';");
		}
	}
	return;
}

?>