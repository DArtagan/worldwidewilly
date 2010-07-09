<?php
/*
Simple:Press Forum
Support Routines
$LastChangedDate: 2009-03-13 15:49:36 +0000 (Fri, 13 Mar 2009) $
$Rev: 1567 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# ******************************************************************
# USER TRACKING AND USER NEW POST LIST MANAGEMENT
# ******************************************************************

# ------------------------------------------------------------------
# sf_track_online()
#
# Tracks online users. Creates their new-post-list when they first
# appear through to savibng their ast visit date when they go again
# (either logout ot times out - 15 minutes)
# ------------------------------------------------------------------
function sf_track_online()
{
	global $wpdb, $current_user;

	$lastvisit = 0;

	if($current_user->member)
	{
		# it's a member
		$trackuserid = $current_user->ID;
		$trackname = $current_user->user_login;

		$lastvisit = $current_user->lastvisit;
		if((is_null($lastvisit)) || ($lastvisit == 0))
		{
			$lastvisit = time();
		}

	} elseif(!empty($current_user->guestname)) {
		# it's a returning guest
		$trackuserid=0;
		$trackname = $current_user->guestname.$_SERVER['REMOTE_ADDR'];
		$lastvisit = $current_user->lastvisit;
		if(is_null($lastvisit)) $lastvisit=0;

	} else {
		# Unklnown guest
		$trackuserid=0;
		$trackname = $_SERVER['REMOTE_ADDR'];
	}

	# Update tracking
	$id=$wpdb->get_var("SELECT id FROM ".SFTRACK." WHERE trackname='".$trackname."'");
	if($id)
	{
		# they are still here
		$wpdb->query("UPDATE ".SFTRACK." SET trackdate=now() WHERE id=".$id);
	} else {
		# newly arrived
		$wpdb->query("INSERT INTO ".SFTRACK." (trackuserid, trackname, trackdate) VALUES (".$trackuserid.", '".$trackname."', now())");
		if($current_user->member)
		{
			sf_construct_users_newposts();
		}
	}

	# Check for expired tracking - so may have left the scene
	$expired=$wpdb->get_results("SELECT * FROM ".SFTRACK." WHERE trackdate	< DATE_SUB(now(), INTERVAL 20 MINUTE)");
	if($expired)
	{
		# if any Members expired - update user meta
		foreach($expired as $expire)
		{
			if($expire->trackuserid > 0)
			{
				sf_set_last_visited($expire->trackuserid);
				sf_destroy_users_newposts($expire->trackuserid);
			}
		}
		# finally delete them
		$wpdb->query("DELETE FROM ".SFTRACK." WHERE trackdate < DATE_SUB(now(), INTERVAL 20 MINUTE)");
	}
	return;
}

# ------------------------------------------------------------------
# sf_construct_users_newposts()
#
# Constructs the new users personalised new/unread posts list when
# they first appear on the system and creates the timestamp for
# their creation
# ------------------------------------------------------------------
function sf_construct_users_newposts()
{
	global $wpdb, $current_user, $sfglobals;

	$topics=$wpdb->get_col("SELECT DISTINCT topic_id FROM ".SFPOSTS." WHERE post_status = 0 AND UNIX_TIMESTAMP(post_date) > '".$current_user->lastvisit."' AND user_id != ".$current_user->ID." ORDER BY topic_id DESC;");
	if(!$topics) $topics[0] = 0;

	sf_update_member_item($current_user->ID, 'newposts', $topics);
	sf_update_member_item($current_user->ID, 'checktime', 0);

	return;
}

# ------------------------------------------------------------------
# sf_set_last_visited()
#
# Set the last visited timestamp after user has disappeared
#	$userid:		Users ID
# ------------------------------------------------------------------
function sf_set_last_visited($userid)
{
	sf_update_member_item($userid, 'lastvisit', 0);

	return;
}

# ------------------------------------------------------------------
# sf_destroy_users_newposts()
#
# Destroy users new-post-list now they have departed
#	$userid:		Users ID
# ------------------------------------------------------------------
function sf_destroy_users_newposts($userid)
{
	$empty[0] = 0;
	sf_update_member_item($userid, 'newposts', $empty);
	return;
}

# ------------------------------------------------------------------
# sf_update_users_newposts()
#
# Updates a users new-post-list on subsequent page loads
#	$newpostlist:		new-post-list
# ------------------------------------------------------------------
function sf_update_users_newposts($newpostlist)
{
	global $wpdb, $current_user, $sfglobals;

	if($newpostlist[0]==0)
	{
		$newpostlist='';
		$newpostlist=array();
	}
	$checktime=strtotime(sf_get_member_item($current_user->ID, 'checktime'));

	$newpostlist=array_reverse($newpostlist);

	$topics=$wpdb->get_col("SELECT DISTINCT topic_id FROM ".SFPOSTS." WHERE post_status = 0 AND UNIX_TIMESTAMP(post_date) > '".$checktime."' AND user_id != ".$current_user->ID." ORDER BY topic_id DESC;");
	if($topics)
	{
		foreach($topics as $topic)
		{
			if(!in_array($topic, $newpostlist))
			{
				$newpostlist[] = $topic;
			}
		}
	}

	$newpostlist=array_reverse($newpostlist);

	if(count($newpostlist) == 0)
	{
		$newpostlist[0]=0;
	}
	sf_update_member_item($current_user->ID, 'newposts', $newpostlist);
	sf_update_member_item($current_user->ID, 'checktime', 0);

	return $newpostlist;
}

# ------------------------------------------------------------------
# sf_remove_users_newposts()
#
# Removes items from users new-post-list upon viewing then
#	$topicid:		the topic to remove from new-post-list
# ------------------------------------------------------------------
function sf_remove_users_newposts($topicid)
{
	global $current_user;

	if($current_user->member)
	{
		$newpostlist=sf_get_member_item($current_user->ID, 'newposts');
		if(($newpostlist) && ($newpostlist[0] != 0))
		{
			if((count($newpostlist) == 1) && ($newpostlist[0] == $topicid))
			{
				sf_destroy_users_newposts($current_user->ID);
			} else {
				$remove = -1;
				for($x=0; $x < count($newpostlist); $x++)
				{
					if($newpostlist[$x] == $topicid)
					{
						$remove = $x;
					}
				}
				if($remove != -1)
				{
					array_splice($newpostlist, $remove, 1);
					sf_update_member_item($current_user->ID, 'newposts', $newpostlist);
				}
			}
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_is_in_users_newposts()
#
# Determines if toopic is in current users new-post-list
#	$topicid:		the topic to look for
# ------------------------------------------------------------------
function sf_is_in_users_newposts($topicid)
{
	global $current_user;

	if($current_user->member)
	{
		$newpostlist=sf_get_member_item($current_user->ID, 'newposts');
	}

	$found = false;
	if(($newpostlist) && ($newpostlist[0] != 0))
	{
		for($x=0; $x < count($newpostlist); $x++)
		{
			if($newpostlist[$x] == $topicid) $found=true;
		}
	}
	return $found;
}

# ------------------------------------------------------------------
# sf_sort_new_post_list()
#
# Sorts User New Posts Recordset to group forums
#	$posts:		the recordset of posts
# ------------------------------------------------------------------
function sf_sort_new_post_list($posts)
{
	# build an index of forums
	$index = array();
	$newpostlist = array();
	$pos = 0;

	foreach($posts as $post)
	{
		if(!in_array($post->forum_id, $index)) $index[] = $post->forum_id;
	}

	# rebuild main array
	for($x=0; $x<count($index); $x++)
	{
		foreach($posts as $post)
		{
			if($post->forum_id == $index[$x])
			{
				$newpostlist[$pos]->forum_id = $post->forum_id;
				$newpostlist[$pos]->topic_id = $post->topic_id;
				$pos++;
			}
		}
	}
	return $newpostlist;
}

# ******************************************************************
# ADMINS NEW POST QUEUE MANAGEMENT
# ******************************************************************

# ------------------------------------------------------------------
# sf_approve_post()
#
# Approve a post and take it out of moderation and the queue (if allowed)
# if postid is set then work on just that post and if topicid is set
# as well, then check with waiting for removal of the one post.
# if postid is zero and topicid is set - approve all in topic.
#	$fromBar		Set to true if called from Admins Bar
#	$postid:		the post to approve
#	$topicid		the topic to approve (if set then 'all')
#	$show			true if no return message is required
# ------------------------------------------------------------------
function sf_approve_post($fromBar, $postid=0, $topicid=0, $show=true)
{
	global $wpdb, $sfvars, $current_user, $sfglobals;

	if($postid == 0 && $topicid == 0) return;

	if(!$current_user->sfapprove)
	{
		if($show) update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}

	if($postid != 0)
	{
		$wpdb->query("UPDATE ".SFPOSTS." SET post_status = 0 WHERE post_id=".$postid);
	}

	if($postid == 0 && $topicid != 0)
	{
		$wpdb->query("UPDATE ".SFPOSTS." SET post_status = 0 WHERE topic_id=".$topicid);
	}

	if($wpdb === false)
	{
		if($show) update_sfnotice('sfmessage', '1@'.__("Post Approval Failed", "sforum"));
	} else {
		if($show) update_sfnotice('sfmessage', '0@'.__("Post Approved", "sforum"));
		if($topicid == 0) $topicid = $sfvars['topicid'];

		if(($sfglobals['admin']['sfbaronly']==true && $fromBar==true) || ($sfglobals['admin']['sfbaronly'] == false))
		{
			sf_remove_from_waiting($fromBar, $topicid, $postid);
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_remove_from_waiting()
#
# Removes an item from admins queue when it is viewed (or from Bar)
#	$fromBar		Set to true if called from Admins Bar
#	$topicid:		the topic to remove (all posts is postid is 0)
#	$postid:		if specified removed the one post from topic
# ------------------------------------------------------------------
function sf_remove_from_waiting($fromBar, $topicid, $postid=0)
{
	global $wpdb, $current_user, $sfglobals;

	if(empty($topicid) || $topicid==0) return;

	$remove = false;

	if(($current_user->adminstatus) || ($current_user->moderator && $sfglobals['admin']['sfmodasadmin']))
	{
		if(($sfglobals['admin']['sfbaronly']==true && $fromBar==true) || ($sfglobals['admin']['sfbaronly'] == false))
		{
			$remove = true;
		}
	} else {
		# if moderator and mods posts are to be shown get out quick
		if($current_user->forumadmin == false &&  $current_user->moderator && $sfglobals['admin']['sfshowmodposts'])
		{
			return;
		}
	}

	if($remove == true)
	{
		# are we removing the whole topic?
		if($postid == 0)
		{
			# first check there are no posts still to be moderated in this topic...
			$rows = $wpdb->get_col("SELECT post_status FROM ".SFPOSTS." WHERE topic_id=".$topicid." AND post_status=1");
			If($rows)
			{
				return;
			} else {
				$wpdb->query("DELETE FROM ".SFWAITING." WHERE topic_id=".$topicid);
			}
		} else {
			# get the current row to see if the postid matches - and the post count is more than 1)
			$current = $wpdb->get_row("SELECT * FROM ".SFWAITING." WHERE topic_id=".$topicid);
			if($current)
			{
				# if post count is 1 may as well delete the row
				if($current->post_count == 1)
				{
					$wpdb->query("DELETE FROM ".SFWAITING." WHERE topic_id=".$topicid);
				} elseif($current->post_id != $postid)
				{
					$wpdb->query("UPDATE ".SFWAITING." SET post_count = ".($current->post_count-1)." WHERE topic_id=".$topicid);
				} else {
					$newpostid = $wpdb->get_var("SELECT post_id FROM ".SFPOSTS." WHERE topic_id=".$topicid." AND post_id > ".$postid." ORDER BY post_id DESC LIMIT 1");
					if($newpostid)
					{
						$wpdb->query("UPDATE ".SFWAITING." SET post_count = ".($current->post_count-1).", post_id = ".$newpostid." WHERE topic_id=".$topicid);
					} else {
						$wpdb->query("DELETE FROM ".SFWAITING." WHERE topic_id=".$topicid);
					}
				}
			}
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_remove_waiting_queue()
#
# Removes the admin queue unless a post is awaiting approval
# ------------------------------------------------------------------

function sf_remove_waiting_queue()
{
	global $wpdb;

	$rows = $wpdb->get_col("SELECT topic_id FROM ".SFWAITING);
	if($rows)
	{
		$queued = array();
		foreach($rows as $row)
		{
			$queued[]=$row;
		}
		foreach($queued as $topic)
		{
			sf_remove_from_waiting(true, $topic);
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_get_waiting_url()
#
# Creates the new post urls and counts in the Admin Bar
#	$postlist:		array from the admin queue of posts
# ------------------------------------------------------------------
function sf_get_waiting_url($postlist, $pageview, $shownew)
{
	global $sfvars, $current_user, $sfglobals;

	# check if topic in url - if yes and it is in postlist - remove it.
	$newposts = array();
	$index = 0;
	$modcount = 0;
	$readcount = 0;
	$fixed = '0';
	if($sfglobals['admin']['sfbarfix']) $fixed = '1';

	if($postlist)
	{
		if(!empty($sfvars['topicid']))
		{
			$topicid=$sfvars['topicid'];

			foreach($postlist as $forum)
			{
				foreach($forum['topics'] as $topic)
				{
					foreach($topic['posts'] as $post)
					{
						$readcount++;
						if(!isset($post['topic_id']) || $post['topic_id'] != $topicid)
						{
							$newposts[$index]->post_id=$post['post_id'];
							# increment mod count for this user
							if ($post['post_status'] == 1) $modcount++;
							$index++;
						} else {
							if ($post['post_status'] == 1)
							{
								$modcount++;
								$newposts[$index]->post_id=$post['post_id'];
								$index++;
							}
						}
					}
				}
			}
		} else {
			$newposts = $postlist;
			foreach($postlist as $forum)
			{
				if(isset($forum['topics']))
				{
					foreach($forum['topics'] as $topic)
					{
						foreach($topic['posts'] as $post)
						{
							$readcount++;
							# increment mod count for this user
							if ($post['post_status'] == 1) $modcount++;
						}
					}
				}
			}
		}
	}
	if($newposts)
	{
		$readcount = $readcount - $modcount;
	} else {
		$readcount = 0;
		$modcount = 0;
	}
	$unreadclass='sfrednumber';
	$needmodclass='sfbluenumber';
	if($readcount == 0) $unreadclass='sfrednumberzero';
	if($modcount == 0) $needmodclass='sfbluenumberzero';

	$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahnewposts.php";
	$numbersurl = SF_PLUGIN_URL."/forum/ahah/sf-ahahautoupdate.php";

	$href = 'javascript:void(0)';
	$jscall = ' onclick="sfjgetNewPostList(\''.$site.'\', \''.$numbersurl.'\', \''.$fixed.'\' )"';
	$spinner = '<img class="inline_edit" id="sfbarspinner" src="'.SFJSCRIPT.'working.gif" alt="" />';
	$out = '<span><a class="sficon" href="'.$href.'" '.$jscall.'><img class="sfalignleft" src="'. SFRESOURCES .'newpost.png" alt="" title="'.__("New Posts", "sforum").'" /><span id="sfunread" class="'.$unreadclass.' sfalignleft" title="'.__("New Posts", "sforum").'">'.$readcount.'</span><span id="sfmod" class="'.$needmodclass.' sfalignleft" title="'.__("Awaiting Approval", "sforum").'">'.$modcount.'</span>&nbsp;'.sf_render_icons("New Posts").'</a></span>'.$spinner."\n";

	return $out;
}

# ******************************************************************
# URL GENERATION
# ******************************************************************

# ------------------------------------------------------------------
# sf_get_forum_url_newposts()
#
# Builds the admin new post url
#	$forumslug:		forum slug
#	$forumname:		forum name
# ------------------------------------------------------------------
function sf_get_forum_url_newposts($forumslug, $forumname)
{
	$out = '<a href="'.sf_build_url($forumslug, '', 1, 0).'">Forum: '.$forumname.'</a>'."\n";
	return $out;
}

# ------------------------------------------------------------------
# sf_get_topic_url_newpost()
#
# Builds the admin new post url
#	$forumslug:		forum slug
#	$topicid:		id of topic
#	$postid:		if of post
#	$postindex:		index of post if known
# ------------------------------------------------------------------
function sf_get_topic_url_newpost($forumslug, $topicid, $postid, $postindex=0)
{
	$topicslug=sf_get_topic_slug($topicid);
	$out = '<a href="'.sf_build_url($forumslug, $topicslug, 1, $postid, $postindex).'">'.sf_get_topic_name($topicslug).'</a>'."\n";
	return $out;
}

# ------------------------------------------------------------------
# sf_get_forum_url()
#
# Builds forum url
#	$forumslug:		forum slug for url
#	$forumname:		forum name for url
#	$forumstatus:	is the forum locked (icon display)
# ------------------------------------------------------------------
function sf_get_forum_url($forumslug, $forumname, $forumstatus, $lastudate)
{
	global $current_user;

	$lockicon='';
	$newicon = '';

	if($forumstatus == 1)
	{
		$lockicon = '<img class="sfstatusicon" src="'.SFRESOURCES.'locked.png" alt="" title="'.__("Forum Locked", "sforum").'" />';
	}


	if(($current_user->lastvisit > 0) && ($current_user->lastvisit < $lastudate))
	{
		$newicon = '<img class="sfstatusicon" src="'.SFRESOURCES.'forumnew.png" alt="" title="'.__("New Posts", "sforum").'" />';
	}

	$out = $lockicon.$newicon.'<a href="'.sf_build_url($forumslug, '', 1, 0).'">'.stripslashes($forumname).'</a>'."\n";
	return $out;
}

# ------------------------------------------------------------------
# sf_get_topic_url()
#
# Builds a topic url including all icons etc
#	$forumslug:		forum slug for url
#	$topicslug:		topic slug for url
#	etc.
# ------------------------------------------------------------------
function sf_get_topic_url($forumslug, $topicslug, $page, $topicname, $topicstatus, $topicpinned, $search, $searchpage, $paramvalue, $forumlock, $blogpostid)
{
	$searchtext='';
	$lockicon='';
	$pinicon='';
	$bloglink='';

	$topicname=apply_filters('sf_show_topic_title', $topicname);

	if($search == true)
	{
		$searchtext = $searchpage;
		$searchvalue=$paramvalue;
	}

	if(($topicstatus == 1) || ($forumlock))
	{
		$lockicon = '<img  class="sfstatusicon" src="'.SFRESOURCES.'locked.png" alt="" title="'.__("Topic Locked", "sforum").'" />';
	}

	if($topicpinned == 1)
	{
		$pinicon = '<img  class="sfstatusicon" src="'.SFRESOURCES.'pin.png" alt="" title="'.__("Forum Pinned", "sforum").'" />';
	}

	if($blogpostid != 0)
	{
		$bloglink = '<a href="'.get_permalink($blogpostid).'"><img  class="sfstatusicon" src="'.SFRESOURCES.'bloglink.png" alt="" /></a>';
	}

	if($search == true)
	{
		$out.= $pinicon.$lockicon.$bloglink.'<a href="'.sf_build_url($forumslug, $topicslug, 1, 0);
		if(strpos(SFURL, '?') === false)
		{
			$out.= '?value';
		} else {
			$out.= '&amp;value';
		}
		$out.= '='.urlencode($paramvalue).'&amp;search='.$searchpage.'">'.stripslashes($topicname).'</a>'."\n";
	} else {
		$out = $pinicon.$lockicon.$bloglink.'<a href="'.sf_build_url($forumslug, $topicslug, 1, 0, $page).'">'.stripslashes($topicname).'</a>'."\n";
	}
	return $out;
}

# ------------------------------------------------------------------
# sf_get_post_url()
#
# Builds a post url including all icons etc
#	$forumslug:		forum slug for url
#	$topicslug:		topic slug for url
#	$postid			id of the post
#	$postindex:		position of post within the topic (for paging)
# ------------------------------------------------------------------
function sf_get_post_url($forumslug, $topicslug, $postid, $postindex=0)
{
	$out = '<a href="'.sf_build_url($forumslug, $topicslug, 1, $postid, $postindex).'">&nbsp;<img src="'. SFRESOURCES .'gopost.png" alt="" title="'.__("Go to Post", "sforum").'" /></a>';
	return $out;
}

# ------------------------------------------------------------------
# sf_get_forum_search_url()
#
# Builds a forum search url with the query vars
#	$forumid:		forum id for url
#	$searchpage		page of the search results
#	$searchvalue	Pas the value inbto the url
# ------------------------------------------------------------------
function sf_get_forum_search_url($forumid, $searchpage, $searchvalue)
{
	$out = '<a class="sficon" href="'.sf_build_qurl('forum='.$forumid, 'value='.$searchvalue, 'search='.$searchpage).'">'."\n";
	return $out;
}

# ------------------------------------------------------------------
# sf_get_topic_url_dashboard()
#
# Builds a new post url for the dashboard notification section
#	$forumslug:		forum slug for url
#	$topicslug:		topic slug for url
# ------------------------------------------------------------------
function sf_get_topic_url_dashboard($forumslug, $topicslug)
{
	$out = '<a href="'.sf_build_url($forumslug, $topicslug, 1, 0).'"><img src="'. SFRESOURCES .'announcenew.png" alt="" />&nbsp;&nbsp;'.sf_get_topic_name($topicslug).'</a>'."\n";
	return $out;
}

# ******************************************************************
# MISCELLANEOUS ROUTINES
# ******************************************************************

# ------------------------------------------------------------------
# sf_push_topic_page()
#
# called on forum display to note current topic page user is viewing.
#	$forumid:
#	$page:
# ------------------------------------------------------------------
function sf_push_topic_page($forumid, $page)
{
	update_sfsetting($_SERVER['REMOTE_ADDR'], $forumid.'@'.$page);
	return;
}

# ------------------------------------------------------------------
# sf_pop_topic_page()
#
# called on topic display to set breadcrumb to correct page
# if same forum
#	$forumid:
# ------------------------------------------------------------------
function sf_pop_topic_page($forumid)
{
	$page = 1;
	$check = get_sfsetting($_SERVER['REMOTE_ADDR']);
	# if no record then resprt to page 1
	if($check == -1) return $page;
	$check = explode('@', $check);
	# is it the same forum?
	if($check[0] == $forumid)
	{
		$page = $check[1];
	}
	return $page;
}

# ------------------------------------------------------------------
# sf_create_name_extract()
#
# truncates a forum or topic name for display in Quicklinks
#	$name:		name of forum or topic
# ------------------------------------------------------------------
function sf_create_name_extract($name)
{
	$name=apply_filters('sf_show_topic_title', $name);
	$name = stripslashes($name);
	if(strlen($name) > 35) $name = substr($name, 0, 35).'...';
	return $name;
}

# ------------------------------------------------------------------
# sf_display_banner()
#
# displays optional banner instead of page title
# ------------------------------------------------------------------
function sf_display_banner()
{
	$sftitle = get_option('sftitle');
	if(!empty($sftitle['sfbanner']))
	{
		return '<img id="sfbanner" src="'.$sftitle['sfbanner'].'" alt="" />';
	}
	return '';
}

# ------------------------------------------------------------------
# sf_render_icons()
#
# displays an icon text if not turned off in the options
#	$icontext:		text to display if needed
# ------------------------------------------------------------------
function sf_render_icons($icontext)
{
	global $sfglobals;

	if($sfglobals['icons'][$icontext] == 1)
	{
		return __($icontext, "sforum");
	} else {
		return '';
	}
}

# ------------------------------------------------------------------
# sf_get_topic_status_flag()
#
# Returns status entry $pos for forums set $statusset
# 	$statusset:		stats set name used in forum
#	$pos:			position in list (base 1 so take 1 off)
# ------------------------------------------------------------------
function sf_get_topic_status_flag($statusset, $pos)
{
	$list=sf_get_sfmeta('topic-status', false, $statusset);
	$states = array();
	$states = explode(',', stripslashes($list[0]['meta_value']));
	return trim($states[$pos-1]);
}

# ------------------------------------------------------------------
# sf_topic_status_select()
#
# Returns status entry $pos for forums set $statusset
# 	$statusset:		stats set name used in forum
#	$current:		current position in list (base 1 so take 1 off)
# ------------------------------------------------------------------
function sf_topic_status_select($statusset, $current = -1, $inline = false, $search = false)
{
	global $wpdb, $sfvars, $current_user;

	if(!$search)
	{
		if(!$current_user->sftopicstatus) return '';
	}
	
	$set = sf_get_sfmeta('topic-status', false, $statusset);

	if($set)
	{
		$list = stripslashes($set[0]['meta_value']);
		$list = explode(',', $list);

		$out='';

		if($inline)
		{
			$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahadmintools.php?action=ss&amp;id=".$sfvars['topicid']."&amp;set=".$statusset;
			$out.= '<select class="sfcontrol" name="statvalue" onchange="javascript:sfjsetStatus(this,  \''.$site.'\')" >'."\n";
		} else {
			$out.= '<select class="sfquicklinks sfcontrol" name="statvalue">'."\n";
		}

		$out.= '<option value="0">'.__("Select Status:", "sforum").'</option>'."\n";
		$default='';
		for($x=0; $x<count($list); $x++)
		{
			if(($current-1) == $x)
			{
				$default = 'selected="selected" ';
			} else {
				$default - null;
			}
			$out.='<option '.$default.'value="'.($x+1).'">'.trim($list[$x]).'</option>'."\n";
			$default='';
		}
	}
	$out.='</select>';
	return $out;
}

# ------------------------------------------------------------------
# sf_check_unlogged_user()
#
# checks if 'guest' is a user not logged in and returns their name
# ------------------------------------------------------------------
function sf_check_unlogged_user()
{
	if(isset($_COOKIE['sforum_'.COOKIEHASH]) && get_option('sfcheckformember'))
	{
		# Yes it is - a user not logged in
		$username = $_COOKIE['sforum_'.COOKIEHASH];
		return $username;
	}
	return 0;
}

# ------------------------------------------------------------------
# sf_report_post_send()
#
# Send 'report post' email to forum admin
# ------------------------------------------------------------------
function sf_report_post_send()
{
	global $current_user;

	$eol = "\r\n";
	$msg = '';

	# clean up the content for the plain text email
	$post_content = html_entity_decode($_POST['postcontent']);
	$post_content = sf_filter_content($post_content, '');
	$post_content = sf_filter_nohtml_kses($post_content);
	$post_content = stripslashes($post_content);

	if($current_user->guest && $current_user->guestname='')
	{
		$reporter = __('A Guest Visitor', 'sforum');
	} else {
		$reporter = __('Member', 'sforum').' '.$current_user->display_name;
	}

	$msg.= sprintf(__("%s has reported the following post as questionable", "sforum"), stripslashes($reporter)).$eol.$eol;
	$msg.= $_POST['posturl'].$eol;
	$msg.= $_POST['postauthor'].$eol;
	$msg.= $post_content.$eol.$eol;
	$msg.= __("Comments", "sforum").$eol;
	$msg.= $_POST['postreport'].$eol;

	$email_sent = sf_send_email(get_option('admin_email'), sprintf(__('[%s] Questionable Post Report', "sforum"), get_option('blogname')), $msg);

	if($email_sent[0])
	{
		$returnmsg = '0@';
	} else {
		$returnmsg = '1@';
	}
	update_sfnotice('sfmessage', $returnmsg.$email_sent[1]);
	return;
}

# ------------------------------------------------------------------
# sf_zone_datetime()
#
# Sets date time for sql queries based on user options
#	$datefield:		sql field being queried
# ------------------------------------------------------------------
function sf_zone_datetime($datefield)
{
	$zone = get_option('sfzone');
	if($zone == 0) return $datefield;
	if($zone < 0)
	{
		$out='DATE_SUB('.$datefield.', INTERVAL '.abs($zone).' HOUR) as '.$datefield;
	} else {
		$out='DATE_ADD('.$datefield.', INTERVAL '.abs($zone).' HOUR) as '.$datefield;
	}
	return $out;
}

# ------------------------------------------------------------------
# sf_check_url()
#
# Check url has http (else browser will assume relative link
#	$url:		URL to be checked
# ------------------------------------------------------------------
function sf_check_url($url)
{
	$url = clean_url($url);
	if($url == 'http://' || $url == 'https://')
	{
		$url='';
	}
	return $url;
}

# ------------------------------------------------------------------
# sf_htmlspecialchars_decode()
#
# Home grown decode function for php4 users
#	$string:		text to be parsed
#	$style:			Quotes directive
# ------------------------------------------------------------------
function sf_htmlspecialchars_decode($string, $style=ENT_COMPAT)
{
	$translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $style));
	if($style === ENT_QUOTES)
	{
		$translation['&amp;#039;'] = '\'';
	}
	return strtr($string,$translation);
}

# ******************************************************************
# POST & USER DISPLAY AND FILTERS AND USERS EDITOR CHOICE
# ******************************************************************

# ------------------------------------------------------------------
# sf_filter_user()
#
# Checks if user is admin and cleans the name
#	$userid:		id of the user
#	$username:		name of the user or guest
# ------------------------------------------------------------------
function sf_filter_user($userid, $username)
{
	return stripslashes($username);
}

# ------------------------------------------------------------------
# sf_filter_wp_ampersand()
#
# Replace & with &amp; in urls
#	$url:		url to be filtered
# ------------------------------------------------------------------
function sf_filter_wp_ampersand($url)
{
	return str_replace('&', '&amp;', $url);
}

# ******************************************************************
# AUTO UPDATING OF NEW POST, INBOX COUNTS & QUICKLINKS
# ******************************************************************

# ------------------------------------------------------------------
# sf_start_auto_update()
#
# Starts ther auto update timer (2 minutes)
# ------------------------------------------------------------------
function sf_start_auto_update($timer)
{
	$url=SF_PLUGIN_URL."/forum/ahah/sf-ahahautoupdate.php";
	$out = '<script type="text/javascript">';
	$out.= 'sfjAutoUpdate("'.$url.'", "'.$timer.'");';
	$out.= '</script>';
	return $out;
}

?>