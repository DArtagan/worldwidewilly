<?php
/*
Simple:Press Forum
Forum Topic/Post Saves
$LastChangedDate: 2009-04-15 03:03:46 +0100 (Wed, 15 Apr 2009) $
$Rev: 1712 $
*/

# load up wp and required forum code files
require_once("sf-config.php");

sf_load_foundation();
include_once(SF_PLUGIN_DIR.'/sf-slugs.php');
include_once(SF_PLUGIN_DIR.'/sf-postsupport.php');

# set up required globals
global $wpdb, $current_user, $sfglobals;

# clear out the message buffer
delete_sfnotice('sfmessage');

# Check the pre-save hook
if(function_exists('sf_hook_pre_post_save'))
{
	if(sf_hook_pre_post_save($_POST['postitem']) == false)
	{
		update_sfnotice('sfmessage', '1@'.__('This post has been refused', "sforum"));
		wp_redirect(SFURL);
		die();
	}
}

# set up the main vars -------------------------------------------------------------
$newpost = array();
$newpost['forumid'] = 0;
$newpost['forumslug'] = '';
$newpost['topicid'] = 0;
$newpost['topicslug'] = '';
$newpost['postid'] = 0;
$newpost['submsg'] = '';
$action='';
if(isset($_POST['newtopic'])) $action = 'topic';
if(isset($_POST['newpost']))  $action = 'post';

# Validation checks on post data ---------------------------------------------------
# if the forum is not set then this may be a back door approach
if((!isset($_POST['forumid'])) || (!isset($_POST['forumslug'])))
{
	echo (__('Forum not set - Unable to create post', 'sforum'));
	die();
} else {
	$newpost['forumid'] = $_POST['forumid'];
	$newpost['forumslug'] = $_POST['forumslug'];
}

# if this is an existing topic check id and slug is set
if($action == 'post')
{
	if((!isset($_POST['topicid'])) || (!isset($_POST['topicslug'])))
	{
		echo (__('Topic not set - Unable to create post', 'sforum'));
		die();
	} else {
		$newpost['topicid'] = $_POST['topicid'];
		$newpost['topicslug'] = $_POST['topicslug'];
	}
}

# check that current user is actually allowed to do this ---------------------------
sf_initialise_globals($newpost['forumid']);

if(($action == 'topic' && !$current_user->sfaddnew) || ($action == 'post' && !$current_user->sfreply))
{
	echo (__('Access Denied', "sforum"));
	die();
}

# set up initial url to return to if save fails ------------------------------------
$returnURL = sf_build_url($newpost['forumslug'], $newpost['topicslug'], 0, $newpost['postid']);

# setup and prepare post data ------------------------------------------------------
if($action == 'topic')
{
	# topic specific items
	if(empty($_POST['newtopicname']))
	{
		update_sfnotice('sfmessage', '1@'.__('No Topic Name has been entered! Post can not be saved', "sforum"));
		wp_redirect($returnURL);
		die();
	} else {
		$newpost['topicname'] = $_POST['newtopicname'];
		$newpost['topicname'] = apply_filters('sf_save_topic_title', $newpost['topicname']);
	}
	$newpost['topicslug'] = sf_create_slug($newpost['topicname'], 'topic');
	$newpost['topicname'] = $wpdb->escape($newpost['topicname']);
	$newpost['topiclock']=0;
	$newpost['topicpin']=0;
	$newpost['topicsub']='NULL';
	$newpost['statusflag']='0';
	$newpost['bloglink']='0';
	$newpost['post_category']='NULL';
	if(isset($_POST['topiclock'])) $newpost['topiclock']=1;
	if(isset($_POST['topicpin'])) $newpost['topicpin']=1;
	if(isset($_POST['topicsub'])) $newpost['topicsub']=$_POST['topicsub'];
	if(isset($_POST['statusflag'])) $newpost['statusflag']=$_POST['statusflag'];
	if($_POST['bloglink'] == 'on') $newpost['bloglink']=true;
	if(isset($_POST['post_category'])) $newpost['post_category']=$_POST['post_category'];
}

# post specific (needed by new topic and new post)
$newpost['postpin']=0;
$newpost['topicsub']='';
$newpost['statvalue']='';
$newpost['posttimestamp'] = 'now()';
if(isset($_POST['postpin'])) $newpost['postpin']=1;
if(isset($_POST['topicsub'])) $newpost['topicsub']=$_POST['topicsub'];
if(isset($_POST['statvalue'])) $newpost['statvalue']=$_POST['statvalue'];
if(!empty($_POST['editTimestamp']))
{
	$yy = $_POST['tsYear'];
	$mm = $_POST['tsMonth'];
	$dd = $_POST['tsDay'];
	$hh = $_POST['tsHour'];
	$mn = $_POST['tsMinute'];
	$ss = $_POST['tsSecond'];
	$dd = ($dd > 31 ) ? 31 : $dd;
	$hh = ($hh > 23 ) ? $hh -24 : $hh;
	$mn = ($mn > 59 ) ? $mn -60 : $mn;
	$ss = ($ss > 59 ) ? $ss -60 : $ss;
	$posttimestamp = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $yy, $mm, $dd, $hh, $mn, $ss );
	$newpost['posttimestamp'] = '"'.( $posttimestamp ).'"';
}

$newpost['poststatus'] = 0;
if(empty($_POST['postitem']))
{
	update_sfnotice('sfmessage', '1@'.__('No Topic Post has been entered! Post can not be saved', "sforum"));
	wp_redirect($returnURL);
	die();
} else {
	$newpost['postcontent'] = $_POST['postitem'];
	$newpost['postcontent'] = apply_filters('sf_save_post_content', $newpost['postcontent']);
	$newpost['postcontent'] = $wpdb->escape($newpost['postcontent']);
}
$newpost['guestname']='';
$newpost['guestemail']='';
if($current_user->guest)
{
	$newpost['guestname'] = apply_filters('sf_save_post_name', $_POST['guestname']);
	$newpost['guestemail'] = apply_filters('sf_save_post_email', $_POST['guestemail']);
	if(empty($newpost['guestname']) || empty($newpost['guestemail']) ||  !is_email($newpost['guestemail']))
	{
		update_sfnotice('sfmessage', '1@'.__('Guest name and valid EMail address required', "sforum"));
		wp_redirect($returnURL);
		die();
	}
	# force maximum lengths
	$newpost['guestname'] = substr($newpost['guestname'], 0, 20);
	$newpost['guestemail'] = substr($newpost['guestemail'], 0, 50);
	$newpost['guestname'] = $wpdb->escape($newpost['guestname']);
	$newpost['guestemail'] = $wpdb->escape($newpost['guestemail']);
	$newpost['postername'] = $newpost['guestname'];
	$newpost['posteremail'] = $newpost['guestemail'];
	$newpost['userid']='';
} else {
	$newpost['postername'] = stripslashes($current_user->display_name);
	$newpost['posteremail'] = $current_user->user_email;
	$newpost['userid'] = $current_user->ID;
}


# Branch to correct routine --------------------------------------------------------
if($action == 'topic')
{
	$newpost = sf_create_topic($newpost, $action);
}
if($action == 'post')
{
	$newpost = sf_create_post($newpost, $action);
}

# reset the url now we should have kosher values and re-direct
$returnURL = sf_build_url($newpost['forumslug'], $newpost['topicslug'], 0, $newpost['postid']);

wp_redirect($returnURL);
die();


# ==================================================================================
# CREATION FUNCTIONS
# ==================================================================================

# Create new Topic and First Post --------------------------------------------------
function sf_create_topic($newpost, $action)
{
	global $wpdb, $current_user;

	# security checks
	check_admin_referer('forum-userform_addtopic', 'forum-userform_addtopic');
	$spamcheck = sf_check_spammath();
	if($spamcheck[0]==true)
	{
		update_sfnotice('sfmessage', $spamcheck[1]);
		return;
	}

	# save the new topic record to db
	$newpost = sf_write_topic($newpost);
	if(!$newpost['db'])
	{
		update_sfnotice('sfmessage', __("Unable to Save New Topic Record", "sforum"));
		return;
	} else {
		# lets grab the new topic id
		$newpost['topicid'] = $wpdb->insert_id;
	}

	# check the topic slug and if empty use the topic id
	if(empty($newpost['topicslug']))
	{
		$newpost['topicslug'] = 'topic-'.$newpost['topicid'];
		$wpdb->query("UPDATE ".SFTOPICS." SET topic_slug='".$newpost['topicslug']."' WHERE topic_id=".$newpost['topicid']);
	}

	# Now save the new post record to db
	$newpost = sf_write_post($newpost);
	if(!$newpost['db'])
	{
		update_sfnotice('sfmessage', __("Unable to Save New Post Message", "sforum"));
		return;
	} else {
		# lets grab the new post id
		$newpost['postid'] = $wpdb->insert_id;
	}

	$wpdb->flush();

	# Post-Save New Post Processing
	$newpost = sf_post_save_processing($newpost, $action);

	# do we need to create a blog link?
	if($newpost['bloglink'])
	{
		$catlist = array();
		if($newpost['post_category'])
		{
			foreach ($newpost['post_category'] as $key=>$value)
			{
				$catlist[] = $value;
			}
		}

		# set up post stuff
		$post_content = $newpost['postcontent'];
		$post_title   = $newpost['topicname'];
		$post_status  = 'publish';
		$post = compact('post_content', 'post_title', 'post_status');
		$blog_post_id = wp_insert_post($post);

		# save categories
		wp_set_post_categories($blog_post_id, $catlist);

		# save postmeta
		$metadata = $newpost['forumid'].'@'.$newpost['topicid'];
		sf_blog_links_postmeta('save', $blog_post_id, $metadata);

		# go back and insert blog_post_id in topic record
		$wpdb->query("UPDATE ".SFTOPICS." SET blog_post_id = ".$blog_post_id." WHERE topic_id = ".$newpost['topicid'].";");
	}

	if($newpost['poststatus'])
	{
		$newpost['submsg'] .= ' - '.__("Placed in Moderation", "sforum").' ';
	}
	update_sfnotice('sfmessage', '0@'.__("New Topic Saved", "sforum").$newpost['submsg'].' '.$newpost['emailmsg']);
	return $newpost;
}

# Create new Post in existing Topic ------------------------------------------------
function sf_create_post($newpost, $action)
{
	global $wpdb, $current_user;

	check_admin_referer('forum-userform_addpost', 'forum-userform_addpost');
	$spamcheck = sf_check_spammath();
	if($spamcheck[0]==true)
	{
		update_sfnotice('sfmessage', $spamcheck[1]);
		return;
	}
	# Write the post
	$newpost = sf_write_post($newpost);
	if(!$newpost['db'])
	{
		update_sfnotice('sfmessage', __("Unable to Save New Post Message", "sforum"));
		return;
	}

	$wpdb->flush();

	# Post-Save New Post Processing
	$newpost = sf_post_save_processing($newpost, $action);

	# Is there a topic status flag to save?
	if(!empty($newpost['statvalue']))
	{
		sf_update_topic_status_flag($newpost['statvalue'], $newpost['topicid']);
	}

	if($newpost['poststatus'])
	{
		$newpost['submsg'] .= ' - '.__("Placed in Moderation", "sforum").' ';
	}

	update_sfnotice('sfmessage', '0@'.__("New Post Saved", "sforum").$newpost['submsg'].' '.$newpost['emailmsg']);
	return $newpost;
}


# ==================================================================================
# DATABASE WRITE FUNCTIONS
# ==================================================================================

# Save new Topic to database -------------------------------------------------------
function sf_write_topic($newpost)
{
	global $wpdb;

	$sql =  "INSERT INTO ".SFTOPICS;
	$sql .= " (topic_name, topic_slug, topic_date, forum_id, topic_status, topic_pinned, topic_status_flag, user_id) ";
	$sql .= "VALUES (";
	$sql .= "'".$newpost['topicname']."', ";
	$sql .= "'".$newpost['topicslug']."', ";
	$sql .= "now(), ";
	$sql .= $newpost['forumid'].", ";
	$sql .= $newpost['topiclock'].", ";
	$sql .= $newpost['topicpin'].", ";
	$sql .= $newpost['statusflag'].", ";
	if('' == $newpost['userid'])
	{
		$sql .= "NULL);";
	} else {
		$sql .= $newpost['userid'].");";
	}

	if($wpdb->query($sql) === false)
	{
		$newpost['db'] = false;
	} else {
		$newpost['db'] = true;
	}
	return $newpost;
}

# Save new Post to database --------------------------------------------------------
function sf_write_post($newpost)
{
	global $wpdb, $current_user;

	# If a Guest posting...
	if((($current_user->sfmoderated) || ($current_user->sfmodonce)) && ($current_user->guest))
	{
		$newpost['poststatus'] = 1;
		# unless mod once is on and they have posted before...
		if(($current_user->sfmodonce == true) && ($current_user->sfmoderated == false))
		{
			$prior=$wpdb->get_row("SELECT post_id FROM ".SFPOSTS." WHERE guest_name='".$newpost['guestname']."' AND guest_email='".$newpost['guestemail']."' AND post_status=0 LIMIT 1");
			if($prior) $newpost['poststatus']=0;
		}
	}

	# If a Member posting...
	if((($current_user->sfmoderated) || ($current_user->sfmodonce)) && ($current_user->member))
	{
		$newpost['poststatus'] = 1;
		# unless mod once is on and they have posted before...
		if(($current_user->sfmodonce == true) && ($current_user->sfmoderated == false))
		{
			$prior=$wpdb->get_row("SELECT post_id FROM ".SFPOSTS." WHERE user_id=".$newpost['userid']." AND post_status=0 LIMIT 1");
			if($prior) $newpost['poststatus']=0;
		}
	}

	# Double check forum id is correct - it has been known for a topic to have just been moved!
	$newpost['forumid'] = sf_get_topics_forum_id($newpost['topicid']);

	# Get post count in topic to enable index setting
	$index=$wpdb->get_var("SELECT COUNT(post_id) FROM ".SFPOSTS." WHERE topic_id = ".$newpost['topicid']);
	$index++;

	# grab poster IP address and store in db
	$ip = $_SERVER['REMOTE_ADDR'];

	$sql =  "INSERT INTO ".SFPOSTS;
	$sql .= " (post_content, post_date, topic_id, forum_id, user_id, guest_name, guest_email, post_pinned, post_index, post_status, poster_ip) ";
	$sql .= "VALUES (";
	$sql .= "'".$newpost['postcontent']."', ";
	$sql .= $newpost['posttimestamp'].", ";
	$sql .= $newpost['topicid'].", ";
	$sql .= $newpost['forumid'].", ";
	if('' == $newpost['userid'])
	{
		$sql .= "NULL, ";
	} else {
		$sql .= $newpost['userid'].", ";
	}
	$sql .= "'".$newpost['guestname']."', ";
	$sql .= "'".$newpost['guestemail']."', ";
	$sql .= $newpost['postpin']. ", ";
	$sql .= $index.", ";
	$sql .= $newpost['poststatus'].", ";
	$sql .= "'".$ip."');";

	if($wpdb->query($sql) === false)
	{
		$newpost['db'] = false;
	} else {
		$newpost['db'] = true;
		$newpost['postid'] = $wpdb->insert_id;

		if($current_user->guest)
		{
			sf_write_guest_cookie($newpost['guestname'], $newpost['guestemail']);
		} else {
			$postcount = sf_get_member_item($newpost['userid'], 'posts');
			$postcount++;
			sf_update_member_item($newpost['userid'], 'posts', $postcount);

			# see if postcount qualifies member for new user group membership
			# get rankings information
			if (!$current_user->forumadmin)  # ignore for admins as they dont belong to user groups
			{
				$rankdata = sf_get_sfmeta('forum_rank');
				if ($rankdata)
				{
					# put into arrays to make easy to sort
					foreach ($rankdata as $x => $info)
					{
						$ranks['title'][$x] = $info['meta_key'];
						$data = unserialize($info['meta_value']);
						$ranks['posts'][$x] = $data['posts'];
						$ranks['usergroup'][$x] = $data['usergroup'];
					}
					# sort rankings highest to lowest
					array_multisort($ranks['posts'], SORT_DESC, $ranks['title'], $ranks['usergroup']);

					# check for new ranking
					for ($x=0; $x<count($rankdata); $x++)
					{
						if ($postcount > $ranks['posts'][$x])
						{
							# if a user group is tied to forum rank add member to the user group
							if ($ranks['usergroup'][$x] != 'none')
							{
								$check = sf_check_membership($ranks['usergroup'][$x], $newpost['userid']);
								if (empty($check))
								{
									sf_add_membership($ranks['usergroup'][$x], $newpost['userid']);
								    sf_update_member_moderator_flag($newpost['userid']);
									sf_rebuild_members_pm();
									break;  # only update highest rank
								}
							}
						}
					}
				}
			}
		}
	}
	return $newpost;
}

# ==================================================================================
# POST-SAVE NEW POST ROUTINES
# ==================================================================================

# Post-Save New Post processing ----------------------------------------------------
function sf_post_save_processing($newpost, $action)
{
	global $current_user;

	# construct new url
	$newpost['url']=sf_build_url($newpost['forumslug'], $newpost['topicslug'], 0, $newpost['postid']);

	if ($current_user->sfsubscriptions && !empty($newpost['topicsub']))
	{
		sf_save_subscription($newpost['topicid'], $newpost['userid'], true);
		$newpost['submsg'] = ' '.__('and Subscribed', 'sforum');
	}

	# save hook
	if(function_exists('sf_hook_post_save'))
	{
		sf_hook_post_save($newpost, $action);
	}

	# add to admins new post queue
	sf_add_to_waiting($newpost['topicid'], $newpost['forumid'], $newpost['postid'], $newpost['userid']);

	# send out email notifications
	$newpost['emailmsg']='';
	$newpost['emailmsg'] = sf_email_notifications($newpost);

	# Update forum, topic and post index data
	sf_build_forum_index($newpost['forumid']);
	sf_build_post_index($newpost['topicid'], $newpost['topicslug']);

	return $newpost;
}

?>