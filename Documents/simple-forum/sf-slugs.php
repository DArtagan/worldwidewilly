<?php
/*
Simple:Press Forum
Forum/Topic Slug Creation
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

# = BASE SLUG CREATION ========================

# ------------------------------------------------------------------
# sf_create_slug()
# 
# Create a new slug
#	$itle:		Forum or Topic title
#	$type:		'forum', 'topic' or 'pm'
# ------------------------------------------------------------------
function sf_create_slug($title, $type) 
{
	$title = stripslashes($title);
	$title = str_replace('\\', '', $title);
	$title = strip_tags($title);
	$title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
	$title = str_replace('%', '', $title);
	$title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
	$title = remove_accents($title);
	if (seems_utf8($title)) 
	{
		if (function_exists('mb_strtolower')) 
		{
			$title = mb_strtolower($title, 'UTF-8');
		}
	}
	$title = strtolower($title);
	$title = preg_replace('/&.+?;/', '', $title); # kill entities
	$title = preg_replace('/[\?&!=]*/', '', $title); # remove ampersand, question mark and equals
	$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
	$title = preg_replace('/\s+/', '-', $title);
	$title = preg_replace('|-+|', '-', $title);
	$title = trim($title, '-');
	$title = str_replace("'", "", $title);
	$title = str_replace('"', '', $title);

	$title = sf_check_slug_unique($title, $type);
	
	return $title;
}

# ------------------------------------------------------------------
# sf_check_slug_unique()
# 
# Check new slug is unique and not used. Add numeric suffix if
# exists. If slug receved is empty then return empty.
#	$itle:		Forum or Topic title new slug
#	$type:		'forum' or 'topic' or 'pm'
# ------------------------------------------------------------------
function sf_check_slug_unique($title, $type)
{
	global $wpdb;

	if(empty($title)) return '';
	
	$exists = true;
	$suffix = 1;
	$testtitle = $title;
	while($exists)
	{
		if($type == 'forum') {
			$check = $wpdb->get_var("SELECT forum_slug FROM ".SFFORUMS." WHERE forum_slug='".$testtitle."'");
		} elseif($type == 'topic') {
			$check = $wpdb->get_var("SELECT topic_slug FROM ".SFTOPICS." WHERE topic_slug='".$testtitle."'");
		} elseif($type == 'pm') {
			$check = $wpdb->get_var("SELECT message_slug FROM ".SFMESSAGES." WHERE message_slug='".$testtitle."'");
		}
		if($check)
		{
			$testtitle = $title.'-'.$suffix;
			$suffix++;
		} else {
			$exists = false;
		}
	}
	return $testtitle;
}

?>