<?php
/*
Simple:Press Forum
Forum RSS Feeds
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

global $wpdb;

require_once('sf-includes.php');
require_once('forum/sf-support.php');
require_once('forum/sf-primitives.php');
require_once('forum/sf-database.php');
require_once('forum/sf-permalinks.php');
require_once('forum/sf-filters.php');
require_once('sf-startup.php');

# check installed version is correct
if(sf_get_system_status() != 'ok') 
{
	$out.= '<img style="vertical-align:middle" src="'.SFRESOURCES.'information.png" alt="" />'."\n";
	$out.= '&nbsp;&nbsp;'.__("The forum is temporarily unavailable while being upgraded to a new version", "sforum");
	echo $out;
	return;
}

$limit=get_option('sfrsscount');
if(!isset($limit)) $limit=15;

$feed=$_GET['xfeed'];

switch($feed)
{
	case 'group':
	
		# Get Data
		if(isset($_GET['group']))
		{
			$groupid = sf_syscheckint($_GET['group']);
			if(sf_group_exists($groupid))
			{
				$posts = $wpdb->get_results(
						"SELECT ".SFPOSTS.".post_id, topic_id, ".SFPOSTS.".forum_id, post_content, ".sf_zone_datetime('post_date').", ".SFPOSTS.".user_id, 
						 guest_name, display_name, group_id 
						 FROM (".SFPOSTS." LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id) 
						 LEFT JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id 
						 WHERE ".SFFORUMS.".group_id=".$groupid." AND ".SFFORUMS.".forum_rss_private = 0 AND ".SFPOSTS.".post_status = 0 
						 ORDER BY post_date DESC 
						 LIMIT 0, ".$limit.";");
				
				# Define Channel Elements
				$grouprec = sf_get_group_record($groupid);
				$rssTitle=get_bloginfo('name').' - '.__("Group", "sforum").': '.$grouprec->group_name;
				$rssLink=sf_build_qurl('group='.$groupid);
				$atomLink=sf_get_sfurl_plus_amp(SFURL).'group='.$groupid.'&amp;xfeed=group';
		
				$rssDescription=get_bloginfo('description');
				$rssGenerator=__('Simple:Press Forum Version ', "sforum").SFVERSION;
				
				$rssItem=array();
			
				if($posts)
				{
					foreach($posts as $post)
					{
						$thisforum = sf_get_forum_record($post->forum_id);
						if($post->topic_id && $thisforum)
						{
							# Define Item Elements
							$item = new stdClass;
				
							$poster = sf_filter_user($post->user_id, stripslashes($post->display_name));
							if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($post->guest_name));
							$topic=sf_get_topic_record($post->topic_id);
							
							$item->title=$poster.' '.__('on', "sforum").' '.stripslashes($topic->topic_name);
							$item->link=sf_build_url($thisforum->forum_slug, $topic->topic_slug, 1, $post->post_id);
							$item->pubDate=mysql2date('r', $post->post_date);
							$item->category=$thisforum->forum_name;
							$text=sf_filter_content(stripslashes($post->post_content), '');
							$item->description=sf_rss_excerpt($text);
							$item->guid=sf_build_url($thisforum->forum_slug, $topic->topic_slug, 1, $post->post_id);
			
							$rssItem[]=$item;
						}
					}
				}
			}
		}
		
		break;

	case 'topic':

		# Get Data
		if(isset($_GET['topic']))
		{
			$topicid = sf_get_topic_id(sf_syscheckstr($_GET['topic']));
			if($topicid)
			{
				$topic=sf_get_topic_record($topicid);
				$posts = $wpdb->get_results(
						"SELECT ".SFPOSTS.".post_id, post_content, ".sf_zone_datetime('post_date').", ".SFPOSTS.".user_id, guest_name, display_name, ".SFPOSTS.".forum_id 
						 FROM (".SFPOSTS." LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id)
						 LEFT JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id 
						 WHERE topic_id = ".$topicid." AND ".SFPOSTS.".post_status = 0 AND ".SFFORUMS.".forum_rss_private = 0
						 ORDER BY post_date DESC 
						 LIMIT 0, ".$limit);
				$forumslug = sf_syscheckstr($_GET['forum']);
				
				# Define Channel Elements
				$rssTitle=get_bloginfo('name').' - '.__("Topic", "sforum").': '.stripslashes($topic->topic_name);
				$rssLink=sf_build_url($forumslug, $topic->topic_slug, 0, 0);
				$atomLink=sf_build_qurl($forumslug, $topic->topic_slug, 'xfeed=topic');
				$rssDescription=get_bloginfo('description');
				$rssGenerator=__('Simple:Press Forum Version ', "sforum").SFVERSION;
				
				$rssItem=array();
			
				if($posts)
				{
					foreach($posts as $post)
					{
						# Define Item Elements
						$item = new stdClass;
			
						$poster = sf_filter_user($post->user_id, stripslashes($post->display_name));
						if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($post->guest_name));
			
						$item->title=$poster.' '.__('on', "sforum").' '.stripslashes($topic->topic_name);
						$item->link=sf_build_url($forumslug, $topic->topic_slug, 1, $post->post_id);
						$item->pubDate=mysql2date('r', $post->post_date);
						$item->category=sf_get_forum_name($forumslug);
						$text=sf_filter_content(stripslashes($post->post_content), '');
						$item->description=sf_rss_excerpt($text);
						$item->guid=sf_build_url($forumslug, $topic->topic_slug, 1, $post->post_id);
			
						$rssItem[]=$item;
					}
				}
			}
		}
		
		break;
	
	case 'forum':
	
		# Get Data
		if(isset($_GET['forum']))
		{
			$forumid = sf_get_forum_id(sf_syscheckstr($_GET['forum']));	
			if($forumid)
			{
				$forum=sf_get_forum_record($forumid);
				if($forum == '') exit();
		
				$posts = $wpdb->get_results(
						"SELECT ".SFPOSTS.".post_id, ".SFPOSTS.".topic_id, ".SFPOSTS.".forum_id, post_content, ".sf_zone_datetime('post_date').", ".SFPOSTS.".user_id, guest_name, display_name, ".SFPOSTS.".forum_id 
						 FROM (".SFPOSTS." LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id) 
						 LEFT JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id 
						 WHERE ".SFPOSTS.".forum_id = ".$forumid." AND ".SFPOSTS.".post_status = 0 AND ".SFFORUMS.".forum_rss_private = 0 
						 ORDER BY post_date DESC 
						 LIMIT 0, ".$limit);
				
				# Define Channel Elements
				$rssTitle=get_bloginfo('name').' - '.__("Forum", "sforum").': '.stripslashes($forum->forum_name);
				$rssLink=sf_build_url($forum->forum_slug, '', 0, 0);
				$atomLink=sf_build_qurl($forum->forum_slug, 'xfeed=forum');
				$rssDescription=get_bloginfo('description');
				$rssGenerator=__('Simple:Press Forum Version ', "sforum").SFVERSION;
				
				$rssItem=array();
			
				if($posts)
				{
					foreach($posts as $post)
					{
						# Define Item Elements
						$item = new stdClass;
						if($post->topic_id)
						{
							$poster = sf_filter_user($post->user_id, stripslashes($post->display_name));
							if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($post->guest_name));
							$topic=sf_get_topic_record($post->topic_id);
							
							$item->title=$poster.' '.__('on', "sforum").' '.stripslashes($topic->topic_name);
							$item->link=sf_build_url($forum->forum_slug, $topic->topic_slug, 1, $post->post_id);
							$item->pubDate=mysql2date('r', $post->post_date);
							$item->category=sf_get_forum_name($forum->forum_slug);
							$text=sf_filter_content(stripslashes($post->post_content), '');
							$item->description=sf_rss_excerpt($text);
							$item->guid=sf_build_url($forum->forum_slug, $topic->topic_slug, 1, $post->post_id);
			
							$rssItem[]=$item;
						}
					}
				}
			}
		}
		
		break;
		
	case 'all':
	
		# Get Data
		$posts = $wpdb->get_results(
				"SELECT ".SFPOSTS.".post_id, ".SFPOSTS.".topic_id, ".SFPOSTS.".forum_id, post_content, ".sf_zone_datetime('post_date').", ".SFPOSTS.".user_id, guest_name, display_name 
				 FROM (".SFPOSTS." LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id) 
				 LEFT JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id 
				 WHERE ".SFPOSTS.".post_status = 0 AND ".SFFORUMS.".forum_rss_private = 0 
				 ORDER BY post_date DESC 
				 LIMIT 0, ".$limit);
		
		# Define Channel Elements
		$rssTitle=get_bloginfo('name').' - '.__("All Forums", "sforum");
		$rssLink=SFURL;
		$atomLink=sf_build_qurl('xfeed=all');
		$rssDescription=get_bloginfo('description');
		$rssGenerator=__('Simple:Press Forum Version ', "sforum").SFVERSION;
		
		$rssItem=array();
	
		if($posts)
		{
			foreach($posts as $post)
			{
				$thisforum = sf_get_forum_record($post->forum_id);
				if($post->topic_id && $thisforum)
				{
					# Define Item Elements
					$item = new stdClass;
		
					$poster = sf_filter_user($post->user_id, stripslashes($post->display_name));
					if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($post->guest_name));
					$topic=sf_get_topic_record($post->topic_id);
					
					$item->title=$poster.' '.__('on', "sforum").' '.stripslashes($topic->topic_name);
					$item->link=sf_build_url($thisforum->forum_slug, $topic->topic_slug, 1, $post->post_id);
					$item->pubDate=mysql2date('r', $post->post_date);
					$item->category=sf_get_forum_name($thisforum->forum_slug);
					$text=sf_filter_content(stripslashes($post->post_content), '');
					$item->description=sf_rss_excerpt($text);
					$item->guid=sf_build_url($thisforum->forum_slug, $topic->topic_slug, 1, $post->post_id);
	
					$rssItem[]=$item;
				}
			}
		}

		break;
}

# Send headers and XML
header("HTTP/1.1 200 OK");
header('Content-Type: application/xml');
header("Cache-control: max-age=3600");
header("Expires: ".date('r', time()+3600));
header("Pragma: ");
echo'<?xml version="1.0" ?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
	<title><?php sf_rss_filter($rssTitle) ?></title>
	<link><?php sf_rss_filter($rssLink) ?></link>
	<description><![CDATA[<?php sf_rss_filter($rssDescription) ?>]]></description>
	<generator><?php sf_rss_filter($rssGenerator) ?></generator>
	<atom:link href="<?php sf_rss_filter($atomLink) ?>" rel="self" type="application/rss+xml" />
<?php 
if($rssItem)
{
	foreach($rssItem as $item)
	{
?>
<item>
	<title><?php sf_rss_filter($item->title) ?></title>
	<link><?php sf_rss_filter($item->link) ?></link>
	<category><?php sf_rss_filter($item->category) ?></category>
	<guid isPermaLink="true"><?php sf_rss_filter($item->guid) ?></guid>
	<description><![CDATA[<?php sf_rss_filter($item->description) ?>]]></description>
	<pubDate><?php sf_rss_filter($item->pubDate) ?></pubDate>
</item>
<?php
	}
}
?>
</channel>
</rss>