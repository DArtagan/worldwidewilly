<?php
/*
Simple:Press Forum 
Ahah - Admins NewPosts Dropdown
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

require_once("../../sf-config.php");
require_once("../sf-newadminview.php");

sf_load_foundation();

global $current_user, $sfglobals;

sf_initialise_globals();

if ($current_user->forumadmin || $current_user->moderator) 
{
	# must be loading up the new post list
	$newposts = sf_get_admins_queued_posts();
	$out = sf_render_new_post_list_admin($newposts, true);
	echo $out;
} else {
	echo (__('Access Denied', "sforum"));
}

die();

?>