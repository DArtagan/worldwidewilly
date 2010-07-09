<?php
/*
Simple:Press Forum
Admin Moderation and mark as read control
$LastChangedDate: 2009-01-16 20:14:58 +0000 (Fri, 16 Jan 2009) $
$Rev: 1230 $
*/

require_once("../../sf-config.php");

sf_load_foundation();

global $wpdb, $current_user;

if (isset($_GET['action'])) $action = sf_syscheckint($_GET['action']);
if (isset($_GET['pid'])) $postid = sf_syscheckint($_GET['pid']);
if (isset($_GET['tid'])) $topicid = sf_syscheckint($_GET['tid']);
if (isset($_GET['fid'])) $forumid = sf_syscheckint($_GET['fid']);

sf_initialise_globals($forumid);

if(!$current_user->sfapprove)
{
	echo (__('Access Denied', "sforum"));
	die();
}

# actions:
#	0 = approve
#	1 = mark as read
#	2 = delete

switch($action)
{
	case 0:
		sf_approve_post(true, 0, $topicid, false);
		_e("All Topic Posts Marked as Approved", "sforum");
		break;

	case 1:
		sf_remove_from_waiting(true, $topicid);
		_e("All Topic Posts Marked as Read", "sforum");
		break;

	case 2:
		sf_delete_post($postid, $topicid, $forumid, false);
		_e("Post Deleted", "sforum");
		break;
}

# we always need to remove from users new posts list
sf_remove_users_newposts($topicid);

die();

?>