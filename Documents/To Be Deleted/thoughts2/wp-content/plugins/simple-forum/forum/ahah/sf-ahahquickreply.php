<?php
/*
Simple:Press Forum
Admin 'Quick Reply' Save
$LastChangedDate: 2009-04-22 10:36:54 +0100 (Wed, 22 Apr 2009) $
$Rev: 1758 $
*/

require_once("../../sf-config.php");
global $wpdb, $current_user;

sf_load_foundation();

include_once(SF_PLUGIN_DIR.'/sf-postsupport.php');

sf_initialise_globals($forumid);

if(empty($_GET['postitem'])) die();

$topicid=sf_syscheckint($_GET['tid']);
$forumid=sf_syscheckint($_GET['fid']);

$newpost = array();
$newpost['forumid']			= $forumid;
$newpost['topicid']			= $topicid;
$newpost['postcontent']		= $_GET['postitem'];  # not sanitized because it occurs two lines below

# decode it from the encoded javascript string
$newpost['postcontent']		= urldecode($newpost['postcontent']);

$newpost['postcontent'] 	= apply_filters('sf_save_post_content', $newpost['postcontent']);
$newpost['postcontent'] 	= $wpdb->escape($newpost['postcontent']);
$newpost['postpin']			= 0;
$newpost['topicsub']		= '';
$newpost['statvalue']		= '';
$newpost['posttimestamp'] 	= 'now()';
$newpost['userid']			= $current_user->ID;
$newpost['poststatus']		= 0;

$newpost['forumslug'] 		= sf_get_forum_slug($forumid);
$newpost['topicslug']		= sf_get_topic_slug($topicid);

$newpost['postername'] 		= stripslashes($current_user->display_name);
$newpost['posteremail'] 	= $current_user->user_email;

$ip = $_SERVER['REMOTE_ADDR'];

# Get post count in topic to enable index setting
$index=$wpdb->get_var("SELECT COUNT(post_id) FROM ".SFPOSTS." WHERE topic_id = ".$newpost['topicid']);
$index++;

$sql =  "INSERT INTO ".SFPOSTS;
$sql .= " (post_content, post_date, topic_id, forum_id, user_id, guest_name, guest_email, post_pinned, post_index, post_status, poster_ip) ";
$sql .= "VALUES (";
$sql .= "'".$newpost['postcontent']."', ";
$sql .= $newpost['posttimestamp'].", ";
$sql .= $newpost['topicid'].", ";
$sql .= $newpost['forumid'].", ";
$sql .= $newpost['userid'].", ";
$sql .= "'', ";
$sql .= "'', ";
$sql .= $newpost['postpin']. ", ";
$sql .= $index.", ";
$sql .= $newpost['poststatus'].", ";
$sql .= "'".$ip."');";
	
$wpdb->query($sql);
$newpost['postid'] = $wpdb->insert_id;

$postcount = sf_get_member_item($newpost['userid'], 'posts');
$postcount++;
sf_update_member_item($newpost['userid'], 'posts', $postcount);

# construct new url
$newpost['url']=sf_build_url($newpost['forumslug'], $newpost['topicslug'], 0, $newpost['postid']);

# save hook
if(function_exists('sf_hook_post_save'))
{
	sf_hook_post_save($newpost['url'], $newpost['postcontent']);
}

# send out email notifications
$newpost['emailmsg'] = sf_email_notifications($newpost);

# Update forum, topic and post index data
sf_build_forum_index($newpost['forumid']);
sf_build_post_index($newpost['topicid'], $newpost['topicslug']);

# Maybe a watch call?

if (isset($_GET['watch']) && sf_syscheckstr($_GET['watch']) == 'true')
{
	sf_save_watch($topicid, $current_user->ID, false);
}

# Maybe a topic status?
if (isset($_GET['status']) && sf_syscheckint($_GET['status']))
{
	sf_update_topic_status_flag($_GET['status'], $topicid);
}

_e("Quick Reply Saved", "sforum");

die();

?>