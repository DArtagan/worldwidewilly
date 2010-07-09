<?php
/*
Simple:Press Forum
Forum/Blog Links
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

# = SHOW LINK IN FORUM TOPIC ==================
function sf_forum_show_blog_link($postid)
{
	$text = stripslashes(get_option('sflinkforumtext'));
	$icon = '<img src="'.SFRESOURCES.'bloglink.png" alt=""/>';
	$text = str_replace('%ICON%', $icon, $text);
	$out = '<span class="sfbloglink"><a href="'.get_permalink($postid).'">'.$text.'</a></span>';
	return $out;
}


function sf_blog_links_postmeta($action, $postid, $item)
{
	global $wpdb;

	# seems to sometimes get triggered by other plugins althoug it suggests a core WP bug
	if(!isset($postid)) return;

	if($action == 'save')
	{
		# check if there already...
		$result = $wpdb->get_results("SELECT meta_value FROM ".$wpdb->prefix."postmeta WHERE post_id=".$postid." AND meta_key='forumlink';");
		if($result)	
		{
			$action = 'update';
		} else {
			$sql="INSERT INTO ".$wpdb->prefix."postmeta (post_id, meta_key, meta_value) VALUES (".$postid.", 'forumlink', '".$item."');";
			$wpdb->query($sql);
			return;
		}
	}
	if($action == 'update')
	{
		$sql="UPDATE ".$wpdb->prefix."postmeta SET meta_value='".$item."' WHERE post_id=".$postid." AND meta_key='forumlink';";
		$wpdb->query($sql);
		return;
	}
	if($action == 'read')
	{
		$sql = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE post_id=".$postid." AND meta_key='forumlink';";
		return($wpdb->get_row($sql));
	}
	if($action == 'delete')
	{
		$sql = "DELETE FROM ".$wpdb->prefix."postmeta WHERE post_id=".$postid." AND meta_key='forumlink';";
		return($wpdb->get_row($sql));
	}
}

function sf_break_post_link($topicid, $postid)
{
	global $wpdb;
	
	# remove from postmeta
	sf_blog_links_postmeta('delete', $postid, '');

	# and set blog_oost_id to zero in topic record
	$wpdb->query("UPDATE ".SFTOPICS." SET blog_post_id = 0 WHERE topic_id = ".$topicid.";");
	return;
}

function sf_make_excerpt($postid, $postcontent)
{
	if(get_option('sflinkexcerpt') == false)
	{
		return $postcontent;
	}
	
	# so an excerpt then
	$words = get_option('sflinkwords');
	if((empty($words)) || ($words == 0)) $words = 50;

	$excerpt = '';
		
	$textarray=explode(' ', $postcontent);
	if(count($textarray) <= $words)
	{
		$excerpt = $postcontent;
	} else {
		for($x=0; $x<$words; $x++)
		{
			$excerpt.= $textarray[$x].' ';
		}
		$excerpt.= '...';
	}

	return $excerpt;
}

?>