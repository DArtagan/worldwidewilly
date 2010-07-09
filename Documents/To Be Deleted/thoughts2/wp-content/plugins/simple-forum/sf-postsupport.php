<?php
/*
Simple:Press Forum
Forum Topic/Post New Post SUpport routines
$LastChangedDate: 2009-04-29 03:06:56 +0100 (Wed, 29 Apr 2009) $
$Rev: 1821 $
*/

# ==================================================================================
# NOTIFICATION EMAILS
# ==================================================================================

# Send emails to Admin and Subscribers (if needed) ---------------------------------
function sf_email_notifications($newpost)
{
	global $sfglobals, $wpdb, $current_user;

	$groupname = stripslashes(sf_get_group_name_from_forum($newpost['forumid']));
	$forumname = stripslashes(sf_get_forum_name($newpost['forumslug']));
	$topicname = stripslashes(sf_get_topic_name($newpost['topicslug']));
	$out = '';
	$email_status = array();

	$post_record = $wpdb->get_results("SELECT post_content, post_status FROM ".SFPOSTS." WHERE post_id=".$newpost['postid']);

	$eol = "\r\n";

	$admins_email = '';
	$admins = $wpdb->get_results("SELECT user_id, admin_options FROM ".SFMEMBERS." WHERE admin = 1 OR moderator = 1");
	if ($admins)
	{
		foreach ($admins as $admin)
		{
			if ($admin->user_id != $newpost['userid'])
			{
				$admin_opts = unserialize($admin->admin_options);
				if ($admin_opts['sfnotify'])
				{
					$email = $wpdb->get_var("SELECT user_email FROM ".SFUSERS." WHERE ID = $admin->user_id");
					if ($admins_email != '') $admins_email .= ', ';
					$admins_email .= $email;
				}
			}
		}
	}

	if ($admins_email != '')
	{
		$waiting=sf_get_waiting_numbers();

		# clean up the content for the plain text email
		$post_content = html_entity_decode($post_record[0]->post_content, ENT_QUOTES);
		$post_content = sf_filter_content($post_content, '');
		$post_content = stripslashes($post_content);
		$post_content = str_replace('&nbsp;', ' ', $post_content);

		# admin message
		$eol = "\n";
		$ip = $wpdb->get_var("SELECT poster_ip FROM ".SFPOSTS." WHERE post_id=".$newpost['postid']);

		# remove the html
		$post_content = strip_tags($post_content);

		# create message body
		$msg  = sprintf(__('New forum post on your site: %s', "sforum"), get_option('blogname')).$eol.$eol;
		$msg .= sprintf(__('From:  %s', "sforum"), $newpost['postername'].' ['.$newpost['posteremail'].']') . ', '.sprintf(__('Poster IP: %s', "sforum"), $ip).$eol.$eol;

		$msg .= sprintf(__('Group: %s', "sforum"), $groupname).$eol;
		$msg .= sprintf(__('Forum: %s', "sforum"), $forumname).$eol;
		$msg .= sprintf(__('Topic: %s', "sforum"), $topicname);
		$msg .= ' ('.urldecode($newpost['url']).')'.$eol;
		$msg .= sprintf(__('Post:  %s', "sforum"), $eol.$post_content).$eol.$eol;

		if($sfglobals['admin']['sfqueue'])
		{
			$msg.= sprintf(__('There are currently %s Post(s) in %s Topic(s) Awaiting Review', 'sforum'), $waiting[1], $waiting[0]).$eol;
			$msg.= __('Review All New Posts', 'sforum').': '.sf_build_qurl('newposts=all').$eol;
		}

		# email header
		$header  = "From: \"". get_settings('blogname') . "\" <" . get_settings('admin_email') . ">" . $eol;

		sf_send_email($admins_email, sprintf(__('Forum Post: %s [%s]', "sforum"), substr($topicname,0,30).'...', get_option('blogname')), $msg, '', $header);

		$out = '- '.__('Notified: Administrators/Moderators', "sforum");
	}

	# any subscribers?
	$users=$wpdb->get_var("SELECT topic_subs FROM ".SFTOPICS." WHERE topic_id=".$newpost['topicid']);
	if($users)
	{
		$users=explode('@', $users);
		foreach($users as $user)
		{
			if($user != $newpost['userid'])
			{
				# check they still have permission to this forum
				if(sf_user_can($user, 'Can view forum', $newpost['forumid']))
				{
					# get user email address
					$email = $wpdb->get_var("SELECT user_email FROM ".SFUSERS." WHERE ID=".$user);

					# check if in moderation
					$cstatus = '';
					if($post_record[0]->post_status == 1)
					{
						$cstatus = __("Please Note: This post is currently awating moderation by the forum administrator", "sforum");
					}

					$msg='';

					# subscribers message
					$msg.= sprintf(__('New post on a forum topic you are subscribed to at %s:', "sforum"), get_option('blogname')).$eol.$eol;
					$msg.= sprintf(__('From:  %s', "sforum"), $newpost['postername']).$eol;
					$msg.= sprintf(__('Group: %s', "sforum"), $groupname).$eol;
					$msg.= sprintf(__('Forum: %s', "sforum"), $forumname).$eol;
					$msg.= sprintf(__('Topic: %s', "sforum"), $topicname).$eol.$eol;
					$msg.= $newpost['url'].$eol.$eol;
					$msg.= $cstatus;

					$email_status = sf_send_email($email, sprintf(__('[%s] New Forum Post', "sforum"), get_option('blogname')), $msg);
				}
			}
		}
		if($email_status[0] == true)
		{
			if(empty($out))
			{
				$out = '- '.__('Notified: Subscribers', "sforum");
			} else {
				$out.= ' '.__('and Subscribers', "sforum");
			}
		}
	}
	return $out;
}

# Save to Admins Queue if needed ---------------------------------------------------
function sf_add_to_waiting($topicid, $forumid, $postid, $userid)
{
	global $wpdb, $current_user, $sfglobals;

	if(($current_user->moderator) && ($sfglobals['admin']['sfshowmodposts'] == false)) return;

	# are we using the admin queue?
	if($sfglobals['admin']['sfqueue'] == false)	return;

	if($current_user->guest) $userid=0;

	# first is this topic already in waiting?
	$result = $wpdb->get_row("SELECT * FROM ".SFWAITING." WHERE topic_id = ".$topicid);
	if($result)
	{
		# add one to post count
		$pcount = ($result->post_count + 1);
		$sql = 'UPDATE '.SFWAITING.' SET ';
		$sql.= 'post_count='.$pcount." ".', user_id='.$userid.' ';
		$sql.= 'WHERE topic_id='.$topicid.';';
		$wpdb->query($sql);
	} else {
		# else a new record but do not add if the poster os an admin_options
		if($current_user->forumadmin) return;

		$pcount = 1;
		$sql =  "INSERT INTO ".SFWAITING." ";
		$sql .= "(topic_id, forum_id, post_id, user_id, post_count) ";
		$sql .= "VALUES (";
		$sql .= $topicid.", ";
		$sql .= $forumid.", ";
		$sql .= $postid.", ";
		$sql .= $userid.", ";
		$sql .= $pcount.");";
		$wpdb->query($sql);
	}
	return;
}

# = SPAM MATH CHECK ===========================
function sf_check_spammath()
{
	global $current_user;

	# Spam Check
	$spamtest=array();
	$spamtest[0] = false;

	$usemath = true;
	if($current_user->sfspam) $usemath = false;

	if($usemath)
	{
		$spamtest=sf_spamcheck();
	}
	return $spamtest;
}

# = GET UNREAD WAITING NUMBERS ================
function sf_get_waiting_numbers()
{
	global $wpdb;

	$wait=array();
	$total=0;
	$unread=$wpdb->get_results("SELECT * FROM ".SFWAITING);
	if($unread)
	{
		foreach($unread as $entry)
		{
			$total += $entry->post_count;
		}
	}
	$wait[0]=count($unread);
	$wait[1]=$total;
	return $wait;
}

?>