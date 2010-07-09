<?php
/*
Simple:Press Forum
Forum PM Saves
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

require_once("sf-config.php");
require_once("sf-slugs.php");

sf_load_foundation();

global $wpdb, $current_user;

sf_initialise_globals();

if (!$current_user->sfusepm) {
	echo (__('Access Denied', "sforum"));
	die();
}

# clear out the message buffer
delete_sfnotice('sfmessage');

# new pm post creation
sf_save_pm();

$url=sf_build_qurl("pmaction=viewinpm&pms={$current_user->ID}");

wp_redirect($url);

die();


# = SAVE NEW PM ===============================
function sf_save_pm()
{
	global $wpdb, $current_user;

	check_admin_referer('forum-userform_addpm', 'forum-userform_addpm');
	
	if($current_user->ID != $_POST['pmuser'])
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		return;
	}
	
	# Data Checks
	$title=$_POST['pmtitle'];
	if(empty($title)) $title = 'Untitled';

	$messagecontent = $_POST['postitem'];
	if(empty($messagecontent))
	{
		update_sfnotice('sfmessage', '1@'.__('No message was entered', "sforum"));
		return;
	}

	$messsagecontent = apply_filters('sf_save_post_content', $messagecontent);
	$title = apply_filters('sf_save_topic_title', $title);
	$title = htmlspecialchars($title);

	$reply = $_POST['pmreply'];
	if($reply != 1) $reply = '0';

	if($reply=='1')
	{
		$slug = $_POST['pmslug'];
	} else {
		$slug = sf_create_slug($title, 'pm');
	}
	if(empty($slug)) $slug = sf_create_slug($title, 'pm');
	$sentbox = '1';
	
	$tolist = explode('-', $_POST['pmtoidlist']);
	if(!$tolist)
	{	
		update_sfnotice('sfmessage', '1@'.__('No message recipients were set', "sforum"));
		return;
	}

	# Are we messaging All Members?
	$all = false;
	if(in_array('0', $tolist))
	{
		# Get the ID's in a form we can handle
		$all = true;
		$tolist = $wpdb->get_col("SELECT user_id FROM ".SFMEMBERS);
	}

	foreach($tolist as $recipient)
	{
		$recipient = trim($recipient);

		if($all) $sentbox = '0';
		if(($all) && ($current_user->ID == $recipient)) $sentbox = '1';

		$sql  = "INSERT INTO ".SFMESSAGES;
		$sql .= " (sent_date, from_id, to_id, title, message, sentbox, message_slug, is_reply) ";
		$sql .= "VALUES (";
		$sql .= "now(), ";
		$sql .= $current_user->ID.", ";
		$sql .= $recipient.", ";
		$sql .= "'".$wpdb->escape($title)."', ";
		$sql .= "'".$wpdb->escape($messagecontent)."', ";
		$sql .= $sentbox.", ";
		$sql .= "'".$slug."', ";
		$sql .= $reply.");";
		
		if($wpdb->query($sql) === false)
		{	
			update_sfnotice('sfmessage', '1@'.__("Unable to Save New Post Message", "sforum"));
			return;
		}
	}

	$sfpm = get_option('sfpm');
	if($sfpm['sfpmemail'])
	{
		foreach($tolist as $recipient)		
		{
			$emailmsg = sf_pm_send_email(stripslashes($current_user->display_name), $recipient, $wpdb->escape($title));
		}
	}

	update_sfnotice('sfmessage', '0@'.__("Message Posted", "sforum").' - '.$emailmsg);
	return;
}

function sf_pm_send_email($sender, $recipient, $title)
{
	global $wpdb, $siteurl;
	global $wp_rewrite;

	$eol = "\r\n";
	
	# get user email address
	$email = $wpdb->get_var("SELECT user_email FROM ".SFUSERS." WHERE ID=".$recipient);

	$msg = '';
	$title = sf_htmlspecialchars_decode($title);
	$title = stripslashes($title);
	
	# recipient message
	if ($wp_rewrite->using_permalinks())
	{
		$url = SFURL.'?pmaction=viewinpm&pms='.$recipient;
	} else {
		$url = untrailingslashit(SFURL).'&pmaction=viewinpm&pms='.$recipient;		
	}
	
	$msg.= sprintf(__('There is a New Private Message for you on the forum at: %s', "sforum"), $url.$eol.$eol);
	$msg.= sprintf(__('From:  %s', "sforum"), $sender).$eol;
	$msg.= sprintf(__('Title:  %s', "sforum"), $title).$eol.$eol;
	$msg.= SFURL.$eol;

	$email_status = sf_send_email($email, sprintf(__('[%s] New Private Message', "sforum"), get_option('blogname')), $msg);
	return $email_status[1];
}

?>