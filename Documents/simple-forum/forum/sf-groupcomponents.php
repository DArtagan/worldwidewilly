<?php
/*
Simple:Press Forum
Group Rendering Routines (Forums)
$LastChangedDate: 2009-01-03 19:29:58 +0000 (Sat, 03 Jan 2009) $
$Rev: 1122 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# = COLUMN HEADER ROW =========================
if(!function_exists('sf_render_group_column_header_row')):
function sf_render_group_column_header_row($cols)
{
	$out = '<tr><th colspan="2">'.__("Forums", "sforum").'</th>'."\n";
	if($cols['last']) $out.= '<th>'.__("Last Post", "sforum").'</th>'."\n";
	if($cols['topics']) $out.= '<th>'.__("Topics", "sforum").'</th>'."\n";
	if($cols['posts']) $out.= '<th>'.__("Posts", "sforum").'</th>'."\n";
	$out.= '</tr>'."\n";
	return $out;
}
endif;

# = FORUM ENTRY ROW ===========================
if(!function_exists('sf_render_forum_entry_row')):
function sf_render_forum_entry_row($forum, $cols, $stats, $alt)
{
	if(!empty($forum['forum_icon']))
	{
		$icon = SFRESOURCES.'custom/'.$forum['forum_icon'];
	} else {
		$icon = SFRESOURCES.'forum.png';
	}

	$out = '<tr>'."\n";
	$out.= '<td class="sficoncell '.$alt.'"><img src="'.$icon.'" alt="" /></td>'."\n";
	$out.= '<td class="'.$alt.'"><p>'.sf_get_forum_url($forum['forum_slug'], $forum['forum_name'], $forum['forum_status'], $stats['udate'])."\n";
	$out.= '<small>'.sf_render_forum_pagelinks($forum['forum_slug'], $stats['topic_count']).'</small>'."\n";
	$out.= '<br /><small>'.stripslashes($forum['forum_desc']).'</small></p>'."\n";
	if(function_exists('sf_hook_post_forum'))
	{
		$out.= sf_hook_post_forum($forum['forum_id']);
	}
	$out.= '</td>'."\n";

	# Display last post, topic and post counts if required
	if($cols['last'])
	{
		if($stats && $stats['topic_count'] > 0)
		{
			$topicslug = sf_get_topic_slug($stats['topic_id']);
			$out.= sf_render_first_last_post_cell($forum['forum_slug'], $topicslug, $stats, $alt);
		} else {
			$out.='<td align="center" class="'.$alt.'">-</td>'."\n";
		}
	}
	if($cols['topics']) $out.= '<td class="sfcounts '.$alt.'">'.$stats['topic_count'].'</td>'."\n";
	if($cols['posts']) $out.= '<td class="sfcounts '.$alt.'">'.$stats['post_count'].'</td>'."\n";
	$out.= '</tr>'."\n";
	return $out;
}
endif;

# = RENDER INLINE PAGE LINKS ===========================
if(!function_exists('sf_render_forum_pagelinks')):
function sf_render_forum_pagelinks($forumslug, $topiccount)
{
	$topicpage=get_option('sfpagedtopics');
	if($topicpage >= $topiccount) return '';

	$out = '&nbsp;&nbsp;('.__("Page:", "sforum").' ';

	$totalpages=($topiccount / $topicpage);
	if(!is_int($totalpages)) $totalpages=intval($totalpages)+1;
	if($totalpages > 4)
	{
		$maxcount=4;
	} else {
		$maxcount=$totalpages;
	}

	$sep='';
	for($x = 1; $x <= $maxcount; $x++)
	{
		$out.= '<a href="'.sf_build_url($forumslug, '', $x, 0).'">'.$sep.$x.'</a>'."\n";
		$sep = '| ';
	}

	if($totalpages > 4)
	{
		$out.= '&rarr;<a href="'.sf_build_url($forumslug, '', $totalpages, 0).'">'.$totalpages.' </a>'."\n";
	}
	return $out.')';
}
endif;

?>