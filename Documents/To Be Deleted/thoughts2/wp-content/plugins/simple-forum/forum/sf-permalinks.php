<?php
/*
Simple:Press Forum
Forum Permalink Functions
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

# --------------------------------------------------------------
# sf_build_url()
#
# Main URL building routine
# To use pass forum and topic slugs. Page if not known should
# always be 1. If a post id is passed (else use zero), the
# routine will go and get the correct page number for the post
# within the topic
#	$forumslug:		forum link
#	$topicslug:		topic link
#	$pageid:		page (if know - if post will calculate)
#	$postid:		post link if relevant (or zero)
#	$postindex:		sequence number of post if relevant
# --------------------------------------------------------------

function sf_build_url($forumslug, $topicslug, $pageid, $postid, $postindex=0)
{
	if ($postid != 0)
	{
		$pageid = sf_determine_page($topicslug, sf_syscheckint($postid), $postindex);
	}
	
	$url = SFURL;
	if ($forumslug) 	$url.= $forumslug;
	if ($topicslug) 	$url.='/'.$topicslug;
	if ($pageid > 1)   	$url.='/page-'.$pageid;
	if ($postid)    	$url.='/#p'.$postid;
	
	return $url;
}

# --------------------------------------------------------------
# sf_build_qurl()
#
# Main Query String URL building routine
# Must have at least one parameter of 'var=value' string
# Up to three can be passed in.
# --------------------------------------------------------------

function sf_build_qurl($param1, $param2='', $param3='')
{
	$url = rtrim(SFURL, '/');

	# first does it need the ?
	if(strpos($url, '?') === false)
	{
		$url .= '?';
		$and = '';
	} else {
		$and = "&";
	}
	
	$url.= $and.$param1;
	$and = "&";
	if(!empty($param2)) $url.= $and.$param2;
	if(!empty($param3)) $url.= $and.$param3;

	return $url;
}

# --------------------------------------------------------------
# sf_permalink_from_forumid()
#
# Returns permalink for forum from the forum id
# --------------------------------------------------------------

function sf_permalink_from_forumid($forumid)
{
	$url = '';
	if(!empty($forumid))
	{
		$url = sf_build_url(sf_get_forum_slug($forumid), '', 0, 0);
	}
	return $url;
}

# --------------------------------------------------------------
# sf_permalink_from_topicid()
#
# Returns permalink for topic from the topic id
# --------------------------------------------------------------

function sf_permalink_from_topicid($topicid)
{
	$url = '';
	if(!empty($topicid))
	{
		$forumid = sf_get_forum_from_topic($topicid);
		$url = sf_build_url(sf_get_forum_slug($forumid), sf_get_topic_slug($topicid), 0, 0);
	}
	return $url;
}

# --------------------------------------------------------------
# sf_permalink_from_forumid_and_topicid()
#
# Returns permalink for topic from both forum and topic ids
# --------------------------------------------------------------

function sf_permalink_from_forumid_and_topicid($forumid, $topicid)
{
	$url = '';
	if(!empty($topicid) && !empty($forumid))
	{
		$url = sf_build_url(sf_get_forum_slug($forumid), sf_get_topic_slug($topicid), 0, 0);
	}
	return $url;
}

# --------------------------------------------------------------
# sf_permalink_from_postid()
#
# Returns permalink for topic from only the post id
# --------------------------------------------------------------

function sf_permalink_from_postid($postid)
{
	$url = '';
	if(!empty($postid))
	{
		$slugs = sf_get_slugs_from_postid($postid);
		$url = sf_build_url($slugs->forum_slug, $slugs->topic_slug, 0, $postid, $slugs->post_index);
	}
	return $url;
}

# --------------------------------------------------------------
# sf_determine_page()
#
# Determines the correct page with a topic that the post
# will be displayed on based on current settings
#	$topicslug:		to look up topic id if needed
#	$postid:		the post to calculate page for
#	$postindex:		post sequence ig known
# --------------------------------------------------------------

function sf_determine_page($topicslug, $postid, $postindex)
{
	global $wpdb;

	$ppaged=get_option('sfpagedposts');
	
	if($postindex > 0)
	{
		$page = ($postindex/$ppaged);
		if(!is_int($page))
		{
			$page=intval(($page)+1);
		}
	} else {
		$order="ASC"; # default
		if(get_option('sfsortdesc')) $order="DESC"; # global override
		$torder=sf_get_topic_sort($topicslug);
		if(!is_null($torder)) $order=$torder; # topic override
		
		$x = 1;
		$posts=$wpdb->get_results("SELECT post_id FROM ".SFPOSTS." WHERE topic_id = ".sf_get_topic_id($topicslug)." ORDER BY post_pinned DESC, post_id ".$order);
	
		foreach($posts as $post)
		{
			if($post->post_id == $postid)
			{
				# Do the setting of page and return it
				$page = ($x/$ppaged);
				if(!is_int($page))
				{
					$page=intval(($page)+1);
				}
				break;
			} else {
				$x++;
			}
		}
	}
	return $page;
}

function sf_add_get()
{
	global $wp_rewrite;
	
	if($wp_rewrite->using_permalinks())
	{
		return '?';
	} else {
		return '&amp;';
	}
}

?>