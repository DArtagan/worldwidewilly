<?php
/*
Simple:Press Forum
Ahah call for Announce New Posts tag
$LastChangedDate: 2009-01-16 20:14:58 +0000 (Fri, 16 Jan 2009) $
$Rev: 1230 $
*/

require_once("../../sf-config.php");

sf_load_foundation();

global $current_user;

# get out of here if no target specified
if (empty($_GET['target'])) die();
$target = sf_syscheckstr($_GET['target']);

if($target == "announce")
{
	sf_new_post_announce_display();
}

die();

?>