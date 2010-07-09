<?php
/*
Simple:Press Forum
Topic View Display
$LastChangedDate: 2009-01-11 21:25:25 +0000 (Sun, 11 Jan 2009) $
$Rev: 1182 $
*/

function sf_render_new_post_list_user()
{
	global $sfvars, $current_user, $sfglobals;

	$alt = '';
	$out = '';

	$sfusersnewposts = array();
	$sfusersnewposts = get_option('sfusersnewposts');
	if($sfusersnewposts['sfshownewcount'] == 0) $sfusersnewposts['sfshownewcount'] = 6;

	$sfposts = sf_get_users_new_post_list($sfusersnewposts['sfshownewcount']);
	if($sfposts)
	{
		if($sfusersnewposts['sfsortinforum'])
		{
			$sfposts = sf_sort_new_post_list($sfposts);
		}
		$sfposts = sf_combined_new_posts_list($sfposts);

		# Display section heading
		if($current_user->member)
		{
			$out.= '<div class="sfmessagestrip"><p class="sfsubhead">'.__("Most Recent Topics With Unread Posts", "sforum").'</p></div>'."\n";
		} else {
			$out.= '<div class="sfmessagestrip"><p class="sfsubhead">'.__("Most Recent Posts", "sforum").'</p></div>'."\n";
		}

		$out.= '<div class="sfblock">'."\n";

		$out.= '<table class="sfforumtable">'."\n";
		$out.= '<tr><th colspan="2">'.__("Forum/Topic", "sforum").'</th><th>'.__("Started", "sforum").'</th><th>'.__("Last Post", "sforum").'</th><th>'.__("Posts", "sforum").'</th>'."\n";
		$out.= '</tr>'."\n";
		foreach($sfposts as $sfpost)
		{
			$stats = sf_get_combined_topic_stats($sfpost['topic_id'], $sfpost['post_id'], $sfpost['post_index'], 0);

			# Display topic entry
			$out.= '<tr>'."\n";
			$out.= sf_render_topic_icon($sfpost['topic_id'], $stats[0]['udate'], $alt);

			$out.= '<td><p>' . stripslashes($sfpost['forum_name'])."\n";
			$out.= '<br /><a href="'.sf_build_url($sfpost['forum_slug'], $sfpost['topic_slug'], 0, $stats[0]['post_id'], $sfpost['post_index']).'">'.stripslashes($sfpost['topic_name']).'</a></p>'."\n";

			$out.= '<small>'.sf_render_inline_pagelinks($sfpost['forum_slug'], $sfpost['topic_slug'], $stats[0]['post_index']).'</small></td>'."\n";

			# Display first poster
			$out.= '<td class="sfuserdetails">'."\n";
			$x = ($stats[1] ? 1 : 0);
			$poster = sf_filter_user($stats[$x]['user_id'], stripslashes($stats[$x]['display_name']));
			if(empty($poster)) $poster=stripslashes($stats[$x]['guest_name']);
			$out.= '<p>'.mysql2date(SFDATES, $stats[$x]['post_date'])."-".mysql2date(SFTIMES,$stats[$x]['post_date']).'</p><p>'.__("by", "sforum").' '.$poster.sf_get_post_url($sfpost['forum_slug'], $sfpost['topic_slug'], $stats[$x]['post_id'], $stats[$x]['post_index']).'</p>'."\n";
			$out.='</td>'."\n";

			# Display last poster
			$out.= '<td class="sfuserdetails">'."\n";
			$poster = sf_filter_user($stats[0]['user_id'], stripslashes($stats[0]['display_name']));
			if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($stats[0]['guest_name']));
			$out.= '<p>'.mysql2date(SFDATES, $stats[0]['post_date'])."-".mysql2date(SFTIMES,$stats[0]['post_date']).'</p><p>'.__("by", "sforum").' '.$poster.sf_get_post_url($sfpost['forum_slug'], $sfpost['topic_slug'], $stats[0]['post_id'], $stats[0]['post_index']).'</p>'."\n";
			$out.='</td>'."\n";

			# Dislay post count
			$out.= '<td class="sfcounts">'.$sfpost['post_index'].'</td>'."\n";
			$out.= '</tr>'."\n";
		}
		$out.= '</table></div>'."\n";

	} else {
		$out.='<div class="sfmessagestrip">'.__("There are No Recent Unread Posts", "sforum").'</div>'."\n";
	}
	return $out;
}

?>