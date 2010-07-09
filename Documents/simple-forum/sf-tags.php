<?php
/*
Simple:Press Forum
Template Tag(s)
$LastChangedDate: 2009-04-29 02:41:47 +0100 (Wed, 29 Apr 2009) $
$Rev: 1819 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

/* 	=====================================================================================

	sf_recent_posts_tag($limit, $forum, $user, $postdate, $listtags, $forumids)

	displays the most recent topics to have received a new post

	parameters:

		$limit			How many items to show in the list		number			5
		$forum			Show the Forum Title					true/false		false
		$user			Show the Users Name						true/false		true
		$postdate		Show date of posting					true/false		false
		$listtags		Wrap in <li> tags (li only)				true/false		true
		$forumids		comma delimited list of forum id's		optional		0
		$posttime		Show time of posting (reqs postdate)	true/false		false

 	===================================================================================*/

function sf_recent_posts_tag($limit=5, $forum=false, $user=true, $postdate=false, $listtags=true, $forumids=0, $posttime=false)
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	$out.'';

	# are we passing forum ID's?
	if($forumids == 0)
	{
		$where = '';
	} else {
		$flist = explode(",", $forumids);
		$where=' WHERE ';
		$x=0;
		for($x; $x<count($flist); $x++)
		{
			$where.= 'forum_id = '.$flist[$x];
			if($x != count($flist)-1) $where.= " OR ";
		}
	}

	$sfposts = $wpdb->get_results("SELECT DISTINCT forum_id, topic_id FROM ".SFPOSTS.$where." ORDER BY post_id DESC LIMIT ".$limit);

	if($sfposts)
	{
		foreach($sfposts as $sfpost)
		{
			if(sf_can_view_forum($sfpost->forum_id))
			{
				$thisforum = sf_get_forum_record($sfpost->forum_id);
				$p=false;
				$postdetails = sf_get_last_post_in_topic($sfpost->topic_id);

				# Start contruction
				if($listtags) $out.="<li class='sftagli'>\n";

				$out.=sf_get_topic_url_newpost($thisforum->forum_slug, $sfpost->topic_id, $postdetails->post_id, $postdetails->post_index);

				if($forum)
				{
					$out.="<p class='sftagp'>".__("posted in forum", "sforum").' '.stripslashes($thisforum->forum_name)."&nbsp;"."\n";
					$p=true;
				}

				if($user)
				{
					if($p == false) $out.="<p class='sftagp'>";
					$poster = sf_filter_user($postdetails->user_id, stripslashes($postdetails->display_name));
					if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($postdetails->guest_name));
					$out.=__("by", "sforum").' '.$poster.' '."\n";
					$p=true;
				}

				if($postdate)
				{
					if($p == false) $out.="<p class='sftagp'>";
					$out.=__("on", "sforum").' '.mysql2date(SFDATES, $postdetails->post_date)."\n";
					if ($posttime)
					{
						$out.=' '.__("at", "sforum").' '.mysql2date(SFTIMES, $postdetails->post_date)."\n";
					}
					$p=true;
				}

				if($p) $out.="</p>\n";

				if($listtags) $out.="</li>\n";
			}
		}
	} else {
		if($listtags) $out.="<li class='sftagli'>\n";
		$out.='<p>'.__("No Topics to Display", "sforum").'</p>'."\n";
		if($listtags) $out.="</li>\n";
	}
	echo($out);
	return;
}

/* 	=====================================================================================

	sf_recent_posts_alt_tag($limit, $forum, $user, $postdate, $listtags, $forumids)

	displays the most recent topics to have received a new post in an alternate method

	parameters:

		$limit			How many items to show in the list		number			5
		$forum			Show the Forum Title					true/false		false
		$user			Show the Users Name						true/false		true
		$postdate		Show date of posting					true/false		false
		$listtags		Wrap in <li> tags (li only)				true/false		true
		$posttime		Show time of posting (reqs postdate)	true/false		false

 	===================================================================================*/

function sf_recent_posts_alt_tag($limit=5, $forum=false, $user=true, $postdate=false, $listtags=true, $posttime=false)
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	$out.'';

	$sfposts = $wpdb->get_results("SELECT DISTINCT forum_id, topic_id FROM ".SFPOSTS." WHERE post_status = '0' ORDER BY post_id DESC LIMIT ".$limit);

	if($sfposts)
	{
		foreach($sfposts as $sfpost)
		{
			if(sf_can_view_forum($sfpost->forum_id))
			{
				$thisforum = sf_get_forum_record($sfpost->forum_id);
				$p=false;

				$postdetails = sf_get_last_post_in_topic($sfpost->topic_id);

				# Start contruction
				if($listtags) $out.="<li class='sftagli'>\n";

				$out .= '<a href="'.sf_build_url($thisforum->forum_slug, sf_get_topic_slug($sfpost->topic_id), 1, $postdetails->post_id, $postdetails->post_index).'">';

				$out.=sf_get_topic_name(sf_get_topic_slug($sfpost->topic_id));

				if($forum)
				{
					$out.=' '.__("posted in", "sforum").' '.stripslashes($thisforum->forum_name);
					$p=true;
				}

				if($user)
				{
					$out.=' '.__("by ", "sforum").' ';
					$poster = sf_filter_user($postdetails->user_id, stripslashes($postdetails->display_name));
					if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($postdetails->guest_name));
					$out.=$poster;
					$p=true;
				}

				if($postdate)
				{
					$out.=' '.__("on", "sforum").' '.mysql2date(SFDATES, $postdetails->post_date);
					if ($posttime)
					{
						$out.=' '.__("at", "sforum").' '.mysql2date(SFTIMES, $postdetails->post_date)."\n";
					}
					$p=true;
				}

				$out.='</a>';
				if($listtags) $out.="</li>\n";
			}
		}
	} else {
		if($listtags) $out.="<li class='sftagli'>\n";
		$out.=__("No Topics to Display", "sforum")."\n";
		if($listtags) $out.="</li>\n";
	}
	echo($out);
	return;
}

/* 	=====================================================================================

	sf_latest_posts($limit)

	displays the most recent topics to have received a new post

	parameters:

		$limit			How many items to show in the list		number			5=default

 	===================================================================================*/

function sf_latest_posts($limit=5)
{
	global $wpdb, $current_user;
	sf_load_includes();
	sf_initialise_globals();

	$out='';

	$posts = $wpdb->get_results(
			"SELECT post_id, topic_id, forum_id, post_content, post_index, ".sf_zone_datetime('post_date').",
			 ".SFPOSTS.".user_id, guest_name, ".SFMEMBERS.".display_name FROM ".SFPOSTS."
			 LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			 WHERE ".SFPOSTS.".post_status = 0
			 ORDER BY post_date DESC
			 LIMIT ".$limit);

	$out.='<div class="sf-latest">';

	if ($posts) {
		foreach ($posts as $post)
		{
			if(sf_can_view_forum($post->forum_id))
			{
				$thisforum = sf_get_forum_record($post->forum_id);
				$poster = sf_filter_user($post->user_id, stripslashes($post->display_name));
				if (empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($post->guest_name));
				$topic = sf_get_topic_record($post->topic_id);
				$out.='<div class="sf-latest-header">';
				$out.=$poster.__(' posted ', "sforum");
				$out.='<a href="'.sf_build_url($thisforum->forum_slug, $topic->topic_slug, 1, $post->post_id, $post->post_index).'">';
				$out.=stripslashes($topic->topic_name).'</a>';
				$out.=__(' in ', "sforum");
				$out.='<a href="'.sf_build_url($thisforum->forum_slug, '', 1, 0).'">'.sf_get_forum_name($thisforum->forum_slug).'</a>';
				$out.='<br />'.mysql2date(SFDATES, $post->post_date);
				$out.='</div>';
				$out.='<div class="sf-latest-content">';
				$text=sf_filter_content(stripslashes($post->post_content), '');
				$text=sf_rss_excerpt($text);
				$out.=$text;
				$out.=sf_get_post_url($thisforum->forum_slug, $topic->topic_slug, $post->post_id, $post->post_index);
				$out.='</div>';
				$out.='<br />';
			}
		}
	} else {
		$out.='<div class="sf-latest-header">';
		$out.='<p>'.__("No Topics to Display", "sforum").'</p>'."\n";
		$out.='</div>';
	}

	$out.='</div>';

	echo($out);
	return;
}

/* 	=====================================================================================

	sf_new_post_announce()

	displays the latest forum post in  the sidebar - updated every XX seconds

	parameters: None

	The option to use this tag MUST be turned on in the forum options

 	===================================================================================*/

function sf_new_post_announce()
{
	if(get_option('sfuseannounce'))
	{
		$url=SF_PLUGIN_URL."/forum/ahah/sf-ahahannounce.php?target=announce";

		if(get_option('sfannounceauto'))
		{
			$timer = (get_option('sfannouncetime') * 1000);
			echo '<script type="text/javascript">';
			echo 'sfjNewPostCheck("'.$url.'", "sfannounce", "'.$timer.'");';
			echo '</script>';
		}
		echo '<div id="sfannounce">';
		sf_new_post_announce_display();
		echo '</div>';
	}
	return;
}

function sf_new_post_announce_display()
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	$aslist = get_option('sfannouncelist');
	$out = '';

	$sfposts = sf_get_users_new_post_list(get_option('sfannouncecount'));

	if($sfposts)
	{
		$sfposts = sf_combined_new_posts_list($sfposts);
		if($aslist)
		{
			$out = '<ul><li>'.stripslashes(get_option('sfannouncehead')).'<ul>';
		} else {
			$out = '<p>'.stripslashes(get_option('sfannouncehead')).'<br /></p>';
			$out.= '<table id="sfannouncetable" cellpadding="4" cellspacing="0" border="0">';
		}
		foreach($sfposts as $sfpost)
		{
			# GET LAST POSTER DETAILS
			$last = sf_get_last_post_in_topic($sfpost['topic_id']);

			$poster = sf_filter_user($last->user_id, stripslashes($last->display_name));
			if(empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($last->guest_name));

			if(!$aslist)
			{
				$out.= '<tr><td class="sfannounceicon" valign="top" align="left">';
				# DISPLAY TOPIC ENTRY
				$topicicon = 'announceold.png';
				if($current_user->member && $current_user->ID != $sfpost['user_id'])
				{
					if(sf_is_in_users_newposts($sfpost['topic_id'])) $topicicon = 'announcenew.png';
				} else {
					if(($current_user->lastvisit > 0) && ($current_user->lastvisit < $last->udate)) $topicicon = 'announcenew.png';
				}
				$out.= '<img src="'. SFRESOURCES . $topicicon. '" alt="" />'."\n";
			}

			if($aslist)
			{
				$out.= '<li>';
			} else {
				$out.='</td><td class="sfannounceentry" valign="top">';
			}
			$out.= '<a href="'.sf_build_url($sfpost['forum_slug'], $sfpost['topic_slug'], 1, $last->post_id, $last->post_index).'">'.sf_format_announce_tag($sfpost['forum_name'], $sfpost['topic_name'], $poster, $last->post_date).'</a>';

			if($aslist)
			{
				$out.= '</li>';
			} else {
				$out.='</td></tr>';
			}
		}
		if($aslist)
		{
			$out.= '</ul></li></ul>';
		} else {
			$out.='</table>';
		}
	}
	echo $out;
	return;
}

function sf_format_announce_tag($forumname, $topicname, $poster, $postdate)
{
	$text=stripslashes(get_option('sfannouncetext'));

	$text = str_replace('%TOPICNAME%', stripslashes($topicname), $text);
	$text = str_replace('%FORUMNAME%', stripslashes($forumname), $text);
	$text = str_replace('%POSTER%', stripslashes($poster), $text);
	$text = str_replace('%DATETIME%', mysql2date(SFDATES, $postdate)." - ".mysql2date(SFTIMES,$postdate), $text);
	return $text;
}

/* 	=====================================================================================

	sf_author_posts($author_id, $showforum=true, $showdate=true)

	displays all the posts for the specified author id - forum visability rules apply

	parameters:

		$author_id			author to show the posts for
		$showforum			show the forum name							true/false
		$showdate			show the date of the latest post			true/false
		$limit				number of posts to return					0 (all)

 	===================================================================================*/

function sf_author_posts($author_id, $showforum=true, $showdate=true, $limit=0)
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	$posts = 0;

	$out = '<div class="sf-authortopics">';

	if ($limit > 0)
	{
		$limit = 'LIMIT '.$limit;
	} else {
		$limit = '';
	}

	$sql = "SELECT DISTINCT post_id, forum_id, topic_id, post_date, post_index FROM ".SFPOSTS." WHERE user_id = $author_id ORDER BY post_date DESC $limit";
	$sfposts = $wpdb->get_results($sql);

	if ($sfposts) {
		foreach ($sfposts as $sfpost)
		{
			if(sf_can_view_forum($sfpost->forum_id))
			{
				$forum = $wpdb->get_row("SELECT forum_name, forum_slug FROM ".SFFORUMS." WHERE forum_id = $sfpost->forum_id");
				$posts = 1;
				if ($showforum)
				{
					$out .= '<div class="sf-authorforum">';
					$out .= $forum->forum_name;
					$out .= '</div>';
				}

				$out .= '<div class="sf-authorlink">';
				$out .= sf_get_topic_url_newpost($forum->forum_slug, $sfpost->topic_id, $sfpost->post_id, $sfpost->post_index);
				$out .= '</div>';

				if ($showdate)
				{
					$out .= '<div class="sf-authordate">';
					$out .= mysql2date(SFDATES, $sfpost->post_date);
					$out .= '</div>';
				}
			}
		}
	}

	if (!$posts) {
		$out .= __('No posts by this author', 'sforum');
	}

	$out .= '</div>';
	echo $out;
	return;
}

/* 	=====================================================================================

	sf_stats_tag($onlinestats=true, $forumstats=true, $memberstats=true, $topstats=true)

	displays the most recent forum stats in sidebar format

	parameters:

		$onlinestats		show the users online						true/false
		$forumstats			show the group, forum, topic, post stats	true/false
		$memberstats		show the member stats						true/false
		$topstats			show the top poster stats					true/false

 	===================================================================================*/

function sf_stats_tag($onlinestats=true, $forumstats=true, $memberstats=true, $topstats=true)
{
	global $wpdb;

	sf_load_includes();
	sf_initialise_globals();

	# do the header
	$out = '';

	# if requested, output the users online stats
	if ($onlinestats) {
		$guests = 0;
		$label=' '.__("Guests", "sforum");

		$online = $wpdb->get_results("SELECT trackuserid, trackname FROM ".SFTRACK." ORDER BY trackuserid");
		if ($online)
		{
			sf_update_max_online(count($online));
			$out.='<ul class="sfstatonline"><h3>'.__("Currently Online", "sforum").': </h3>'."\n";
			foreach ($online as $user)
			{
				if ($user->trackuserid == 0)
				{
					$guests++;
				} else {
					if (sf_is_forum_admin($user->trackuserid))
					{
						$out.= '<li class="sfstatadmin">'.$user->trackname.'</li>'."\n";
					} else {
						$out.= '<li class="sfstatuser">'.$user->trackname.'</li>'."\n";
					}
				}
			}
			if( $guests > 0)
			{
				if ($guests == 1) $label=' '.__("Guest", "sforum");
				$out.= '<li class="sfstatguest">'.$guests.$label.'</li>'."\n";
			}
			$out.='<li class="sfstatmax">'.__("Maximum Online", "sforum").': '.get_sfsetting('maxonline').'</li>'."\n";
			$out.='</ul>'."\n";
		}
	}

	# if requested, output the forum stats
	if ($forumstats) {
		$cnt = sf_get_stats_counts();
		$out.= '<ul class="sfstatforums"><h3>'.__("Forum Stats", "sforum").': </h3>'."\n";
		$out.= '<li class="sfstatforum">'.__("Forums: ", "sforum").'</li>'."\n";
		$out.= '<li class="sfstatgroup">'.__("Groups: ", "sforum").$cnt->groups.'</li>'."\n";
		$out.= '<li class="sfstatforum">'.__("Forums: ", "sforum").$cnt->forums.'</li>'."\n";
		$out.= '<li class="sfstattopic">'.__("Topics: ", "sforum").$cnt->topics.'</li>'."\n";
		$out.= '<li class="sfstatpost">'.__("Posts: ", "sforum").$cnt->posts.'</li>'."\n";
		$out.='</ul>'."\n";
	}

	# if requested, output the members stats
	if ($memberstats) {
		$members = sf_get_member_post_count();
		$guests = sf_get_guest_count();

		$out.= '<ul class="sfstatmembers"><h3>'.__("Members", "sforum").': </h3>'."\n";
		if ($members)
		{
			$membercount = get_sfsetting('membercount');
		} else {
			$membercount = 0;
		}
		$out.='<li class="sfstatmembernum">'.sprintf(__("There are %s members", "sforum"), $membercount).'</li>'."\n";
		if ($guests)
		{
			$out.='<li class="sfstatguestnum">'.sprintf(__("There are %s guests", "sforum"), $guests).'</li>'."\n";
		}
		if ($members)
		{
			foreach ($members as $member)
			{
				if (sf_is_forum_admin($member->ID))
				{
					$out.='<li class="sfstatadminnum">'.sprintf(__("%s has made %s posts", "sforum"), $member->display_name, $member->posts).'</li>'."\n";
				}
			}
		}
		$out.='</ul>'."\n";
	}

	# if requested, output the top posters
	if ($topstats) {
		$members = sf_get_member_post_count();
		if ($members)
		{
			$out.='<ul class="sfstattop"><h3>'.__("Top Posters:", "sforum").'</h3>'."\n";
			foreach( $members as $member)
			{
				if (sf_is_forum_admin($member->ID))
				{
					$out.='<li class="sfstattopname">'.stripslashes($member->display_name).' - '.$member->posts.'</li>'."\n";
				}
			}
		$out.='</ul>'."\n";
		}
	}
	echo $out;
	return;
}

/* 	=====================================================================================

	sf_group_link($groupid, $linktext, $listtags)

	displays a link to a specific forum group if current user has access privilege

	parameters:

		$groupid		ID of the group to display				Required
		$linktext		Text for link - leave as empty string to use group name
		$listtags		Wrap in <li> tags (li only)				true/false		true

 	===================================================================================*/

function sf_group_link($groupid, $linktext, $listtags=true)
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	if(empty($groupid)) return '';
	$out='';
	$forums = $wpdb->get_results("SELECT forum_id FROM ".SFFORUMS." WHERE group_id='".$groupid."'");
	if ($forums)
	{
		foreach ($forums as $forum)
		{
			if (sf_can_view_forum($forum->forum_id)) {
				$grouprec=sf_get_group_record($groupid);
				if(empty($linktext)) $linktext=stripslashes($grouprec->group_name);
				if($listtags) $out.="<li>\n";
				$out.= '<a href="'.sf_build_qurl('group='.$groupid).'">'.$linktext.'</a>'."\n";
				if($listtags) $out.="</li>\n";
				break;
			}
		}
	} else {
		$out=printf(__('Group %s Not Found', 'sforum'), $groupid)."\n";
	}
	echo $out;
	return;
}

/* 	=====================================================================================

	sf_forum_link($forumid, $linktext, $listtags)

	displays a link to a specific forum topic listing if current user has access privilege

	parameters:

		$forumid		ID of the forum to display				Required
		$linktext		Text for link - leave as empty string to use forum name
		$listtags		Wrap in <li> tags (li only)				true/false		true

 	===================================================================================*/

function sf_forum_link($forumid, $linktext, $listtags=true)
{
	global $current_user;

	sf_load_includes();
	sf_initialise_globals($forumid);

	if(empty($forumid)) return '';
	$out='';
	if(sf_can_view_forum($forum_id))
	{
		$forumrec=sf_get_forum_record($forumid);

		$forumslug = $forumrec->forum_slug;
		if(empty($linktext)) $linktext=stripslashes($forumrec->forum_name);
		if($listtags) $out.="<li>\n";
		$out.= '<a href="'.sf_build_url($forumslug, '', 0, 0).'">'.$linktext.'</a>'."\n";
		if($listtags) $out.="</li>\n";
	} else {
		$out=printf(__('Forum %s Not Found', 'sforum'), $forumid)."\n";
	}
	echo $out;
	return;
}

/* 	=====================================================================================

	sf_topic_link($forumid, $topicid, $linktext, $listtags)

	displays a link to a specific topic post listing if current user has access privilege

	parameters:

		$forumid		ID of the forum topic belongs to		Required
		$topicid		ID of the topic to display posts of		Required
		$linktext		Text for link - leave as empty string to use topic name
		$listtags		Wrap in <li> tags (li only)				true/false		true

 	===================================================================================*/

function sf_topic_link($forumid, $topicid, $linktext, $listtags=true)
{
	global $current_user;

	sf_load_includes();
	sf_initialise_globals($forumid);

	if(empty($forumid)) return '';
	if(empty($topicid)) return '';
	$out='';
	if(sf_topic_exists($topicid))
	{
		if(sf_can_view_forum($forum_id))
		{
			$forumslug = sf_get_forum_slug($forumid);
			$topicrec = sf_get_topic_record($topicid);
			$topicslug = $topicrec->topic_slug;

			if(empty($linktext)) $linktext=stripslashes($topicrec->topic_name);
			if($listtags) $out.="<li>\n";
			$out.= '<a href="'.sf_build_url($forumslug, $topicslug, 1, 0).'">'.$linktext.'</a>'."\n";
			if($listtags) $out.="</li>\n";
		}
	} else {
		$out=printf(__('Topic %s Not Found', 'sforum'), $topicid)."\n";
	}
	echo $out;
	return;
}

/* 	=====================================================================================

	sf_forum_dropdown($forumids)

	displays a dropdown of links to forums

	parameters:

		$forumids		ID's of forums (comma delimited in quotes)		Required

 	===================================================================================*/

function sf_forum_dropdown($forumid = 0)
{
	global $current_user;

	sf_load_includes();
	sf_initialise_globals($forumid);

	$out='';

	if($forumid == 0) return;

	$forums=explode(',', $forumid);
	$out.= '<select name="forumselect" class="sfcontrol" onChange="javascript:sfjchangeURL(this)">'."\n";
	$out.= '<option>'.__("Select Forum", "sforum").'</option>'."\n";
	foreach($forums as $forum)
	{
		if(sf_can_view_forum($forum))
		{
			$forumrec = sf_get_forum_record($forum);
			$forumslug = $forumrec->forum_slug;
			$out.='<option value="'.sf_build_url($forumslug, '', 0, 0).'">--'.stripslashes($forumrec->forum_name).'</option>'."\n";
		}
	}
	$out.='</select>'."\n";
	echo $out;
	return;
}

/* 	=====================================================================================

	sf_show_avatar($size=0)

	displays avatar of current user

	parameters:
		$size:			Size to display avatar (applied to Height AND width) Leave as 0
						to use size of graphic.

	returns:		<img> class = 'sfavatartag'
 	===================================================================================*/


function sf_show_avatar($size=0)
{
	global $current_user;

	sf_load_includes();
	sf_extend_current_user();

	if($current_user->guest) $icon='guest';
	if($current_user->member) $icon='user';
	if($current_user->forumadmin) $icon='admin';

	echo sf_render_avatar($icon, $current_user->ID, $current_user->user_email, $current_user->guestemail, true, $size);
	return;
}

/* 	=====================================================================================

	sf_show_members_avatar($userid, $size=0)

	displays avatar of current user

	parameters:
		$userid:		Requires the userid whose avatar is being requested.
		$size:			Size to display avatar (applied to Height AND width) Leave as 0
						to use size of graphic.

	returns:		<img> class = 'sfavatartag'
 	===================================================================================*/

function sf_show_members_avatar($userid, $size=0)
{
	global $wpdb;

	if(empty($userid)) return;

	sf_load_includes();

	$user = $wpdb->get_row("SELECT user_email FROM ".SFUSERS." WHERE ID = ".$userid);
	if ($user)
	{
		if (sf_is_forum_admin($userid) ? $icon='admin' : $icon='user');
		echo sf_render_avatar($icon, $userid, $user->user_email, '', true, $size);
	}
	return;
}

/* 	=====================================================================================

	sf_show_forum_avatar($email, $size=0)

	displays avatar of current user or guest oulled form the forum

	parameters:
		$email:			Requires the email address whose avatar is being requested.
		$size:			Size to display avatar (applied to Height AND width) Leave as 0
						to use size of graphic.

	returns:		<img> class = 'sfavatartag'
 	===================================================================================*/

function sf_show_forum_avatar($email, $size=0)
{
	global $wpdb;

	sf_load_includes();

	$userid = $wpdb->get_var("SELECT ID FROM ".SFUSERS." WHERE user_email = '".$email."'");
	if ($userid)
	{
		$icon = 'user';
		if (sf_is_forum_admin($userid)) $icon='admin';
		echo sf_render_avatar($icon, $userid, $email, '', true, $size);
	} else {
		$icon = 'guest';
		echo sf_render_avatar($icon, 0, '', $email, true, $size);
	}
	return;
}

# ===== RECENT FOUM POST WIDGET WP >= 2.8  ======================================================================
if (class_exists('WP_Widget'))
{
	sf_load_includes();
	class WP_Widget_SPF extends WP_Widget {
		function WP_Widget_SPF()
		{
			$widget_ops = array('classname' => 'widget_spf', 'description' => __('A Widget to display the latest Simple:Press Forum posts'));
			$this->WP_Widget('spf', __('Recent Forum Posts', 'sforum'), $widget_ops);
		}

		function widget($args, $instance)
		{
			extract($args);
			$title = empty($instance['title']) ? __("Recent Forum Posts", "sforum") : $instance['title'];
			$limit = empty($instance['limit']) ? 5 : $instance['limit'];
			$forum = empty($instance['forum']) ? 0 : $instance['forum'];
			$user = empty($instance['user']) ? 0 : $instance['user'];
			$postdate = empty($instance['postdate']) ? 0 : $instance['postdate'];
			$posttime = empty($instance['posttime']) ? 0 : $instance['posttime'];
			$idlist = empty($instance['idlist']) ? 0 : $instance['idlist'];

			# generate output
			echo $before_widget . $before_title . $title . $after_title . "<ul class='sftagul'>";
			sf_recent_posts_tag($limit, $forum, $user, $postdate, true, $idlist, $posttime);
			echo "</ul>".$after_widget;
		}

		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;
			$instance['title'] = strip_tags(stripslashes($new_instance['title']));
			$instance['limit'] = strip_tags(stripslashes($new_instance['limit']));
			if (isset($new_instance['forum']))
			{
				$instance['forum'] = 1;
			} else {
				$instance['forum'] = 0;
			}
			if (isset($new_instance['user']))
			{
				$instance['user'] = 1;
			} else {
				$instance['user'] = 0;
			}
			if (isset($new_instance['postdate']))
			{
				$instance['postdate'] = 1;
			} else {
				$instance['postdate'] = 0;
			}
			if (isset($new_instance['posttime']))
			{
				$instance['posttime'] = 1;
			} else {
				$instance['posttime'] = 0;
			}
			$instance['idlist'] = strip_tags(stripslashes($new_instance['idlist']));
			return $instance;
		}

		function form($instance)
		{
			global $wpdb;
			$instance = wp_parse_args((array) $instance, array('title' => __('Recent Forum Posts', 'sforum'), 'limit' => 5, 'forum' => 1, 'user' => 1, 'postdate' => 1, 'idlist' => 0, 'posttime' => 1 ));
			$title = htmlspecialchars($instance['title'], ENT_QUOTES);
			$limit = htmlspecialchars($instance['limit'], ENT_QUOTES);
			$forum = $instance['forum'];
			$user = $instance['user'];
			$postdate = $instance['postdate'];
			$posttime = $instance['posttime'];
			$idlist = htmlspecialchars($instance['idlist'], ENT_QUOTES);
	?>
			<!--title-->
			<p style="text-align:right;">
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sforum')?>
				<input style="width: 200px;" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title?>"/>
			</label></p>

			<!--how many to show -->
			<p style="text-align:right;">
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('List how many posts:', 'sforum')?>
				<input style="width: 50px;" type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $limit?>"/>
			</label></p>

			<!--include forum name-->
			<p style="text-align:right;">
			<label for="sfforum-<?php echo $this->get_field_id('forum'); ?>"><?php _e('Show forum name:', 'sforum')?>
				<input type="checkbox" id="sfforum-<?php echo $this->get_field_id('forum'); ?>" name="<?php echo $this->get_field_name('forum'); ?>"
				<?php if($instance['forum'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--include user name-->
			<p style="text-align:right;">
			<label for="sfforum-<?php echo $this->get_field_id('user'); ?>"><?php _e('Show users name:', 'sforum')?>
				<input type="checkbox" id="sfforum-<?php echo $this->get_field_id('user'); ?>" name="<?php echo $this->get_field_name('user'); ?>"
				<?php if($instance['user'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--include post date-->
			<p style="text-align:right;">
			<label for="sfforum-<?php echo $this->get_field_id('postdate'); ?>"><?php _e('Show date of post:', 'sforum')?>
				<input type="checkbox" id="sfforum-<?php echo $this->get_field_id('postdate'); ?>" name="<?php echo $this->get_field_name('postdate'); ?>"
				<?php if($instance['postdate'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--include post time-->
			<p style="text-align:right;">
			<label for="sfforum<?php echo $this->get_field_id('posttime'); ?>"><?php _e('Show time of post (requires post date):', 'sforum')?>
				<input type="checkbox" id="sfforum-<?php echo $this->get_field_id('posttime'); ?>" name="<?php echo $this->get_field_name('posttime'); ?>"
				<?php if($instance['posttime'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--forum id list (comma separated)-->
			<p style="text-align:right;">
			<label for="<?php echo $this->get_field_id('idlist'); ?>"><?php _e('Forum IDs:', 'sforum')?>
				<input style="width: 100px;" type="text" id="<?php echo $this->get_field_id('idlist'); ?>" name="<?php echo $this->get_field_name('idlist'); ?>" value="<?php echo $idlist ?>"/>
			</label></p>
			<small><?php _e("If specified, Forum ID's must be separated by commas. To use ALL forums, enter a value of zero", 'sforum')?></small>
<?php
		}
	}

	add_action('widgets_init', 'widget_sf_init', 5);
	function widget_sf_init()
	{
		new WP_Widget_SPF();
		register_widget('WP_Widget_SPF');
	}
} else {
	# ===== RECENT FOUM POST WIDGET PRE WP 2.8 ======================================================================

	add_action('widgets_init', 'sf_post_widget_init');

	function sf_post_widget_init()
	{
		# Check for the required plugin functions.
		if(!function_exists('register_sidebar_widget'))
		{
			return;
		}

		sf_load_includes();

		function sf_post_widget($args)
		{
			# $args: before_widget, before_title, after_widget, after_title are the array keys. Default tags: li and h2.
			extract($args);

			$options = get_option('widget_sforum');
			$title = empty($options['title']) ? __("Recent Forum Posts", "sforum") : $options['title'];
			$limit = empty($options['limit']) ? 5 : $options['limit'];
			$forum = empty($options['forum']) ? 0 : $options['forum'];
			$user = empty($options['user']) ? 0 : $options['user'];
			$postdate = empty($options['postdate']) ? 0 : $options['postdate'];
			$posttime = empty($options['posttime']) ? 0 : $options['posttime'];
			$idlist = empty($options['idlist']) ? 0 : $options['idlist'];

			# generate output
			echo $before_widget . $before_title . $title . $after_title . "<ul class='sftagul'>";
			sf_recent_posts_tag($limit, $forum, $user, $postdate, true, $idlist, $posttime);
			echo "</ul>".$after_widget;
		}

		function sf_post_widget_control()
		{
			# Get our options and see if we're handling a form submission.
			$options = get_option('widget_sforum');
			if(!is_array($options))
			{
				$options = array('title'=>'', 'limit'=>0, 'forum'=>0, 'user'=>0, 'postdate'=>0, 'posttime'=>0, 'idlist'=>0);
			}

			if ($_POST['sfpostwidget-submit'])
			{
				$options['title'] = strip_tags(stripslashes($_POST['forum-title']));
				$options['limit'] = strip_tags(stripslashes($_POST['forum-limit']));
				if(isset($_POST['forum-forum']))
				{
					$options['forum'] = 1;
				} else {
					$options['forum'] = 0;
				}
				if(isset($_POST['forum-user']))
				{
					$options['user'] = 1;
				} else {
					$options['user'] = 0;
				}
				if(isset($_POST['forum-postdate']))
				{
					$options['postdate'] = 1;
				} else {
					$options['postdate'] = 0;
				}
				if(isset($_POST['forum-posttime']))
				{
					$options['posttime'] = 1;
				} else {
					$options['posttime'] = 0;
				}
				$options['idlist'] = strip_tags(stripslashes($_POST['forum-idlist']));

				update_option('widget_sforum', $options);
			}

			$title = htmlspecialchars($options['title'], ENT_QUOTES);
			$limit = htmlspecialchars($options['limit'], ENT_QUOTES);
			$forum = $options['forum'];
			$user = $options['user'];
			$postdate = $options['postdate'];
			$posttime = $options['posttime'];
			$idlist = htmlspecialchars($options['idlist'], ENT_QUOTES);

			# The option form
			?>

			<!--title-->
			<p style="text-align:right;">
			<label for="forum-title"><?php _e('Title:', 'sforum')?>
				<input style="width: 200px;" type="text" id="forum-title" name="forum-title" value="<?php echo $title?>"/>
			</label></p>

			<!--how many to show -->
			<p style="text-align:right;">
			<label for="forum-limit"><?php _e('List how many posts:', 'sforum')?>
				<input style="width: 50px;" type="text" id="forum-limit" name="forum-limit" value="<?php echo $limit?>"/>
			</label></p>

			<!--include forum name-->
			<p style="text-align:right;">
			<label for="sfforum-forum"><?php _e('Show forum name:', 'sforum')?>
				<input type="checkbox" id="sfforum-forum" name="forum-forum"
				<?php if($options['forum'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--include user name-->
			<p style="text-align:right;">
			<label for="sfforum-user"><?php _e('Show users name:', 'sforum')?>
				<input type="checkbox" id="sfforum-user" name="forum-user"
				<?php if($options['user'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--include post date-->
			<p style="text-align:right;">
			<label for="sfforum-postdate"><?php _e('Show date of post:', 'sforum')?>
				<input type="checkbox" id="sfforum-postdate" name="forum-postdate"
				<?php if($options['postdate'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--include post time-->
			<p style="text-align:right;">
			<label for="sfforum-posttime"><?php _e('Show time of post (requires post date):', 'sforum')?>
				<input type="checkbox" id="sfforum-posttime" name="forum-posttime"
				<?php if($options['posttime'] == TRUE) {?> checked="checked" <?php } ?> />
			</label></p>

			<!--forum id list (comma separated)-->
			<p style="text-align:right;">
			<label for="forum-idlist"><?php _e('Forum IDs:', 'sforum')?>
				<input style="width: 100px;" type="text" id="forum-idlist" name="forum-idlist" value="<?php echo $idlist?>"/>
			</label></p>
			<small><?php _e("If specified, Forum ID's must be separated by commas. To use ALL forums, enter a value of zero", 'sforum')?></small>

			<input type="hidden" id="sfpostwidget-submit" name="sfpostwidget-submit" value="1" />
			<?php
		}

		$name = "Simple:Press Forum";

	    # Register the widget
	    register_sidebar_widget(array($name, 'widgets'), 'sf_post_widget');

	    # Registers the widget control form
	    register_widget_control(array($name, 'widgets'), 'sf_post_widget_control', 300, 230);
	}
}

/* 	=====================================================================================

	sf_recent_posts_expanded($limit=5)

	displays the most recent topics to have received a new post in full expanded view

	Displays the number of items as set in the forum options for users new post list

	NOTE: This is not an ordinary tag. It replictes the new post list from the forum.
	For proper results you need to include the forum CSS file.

 	===================================================================================*/

function sf_recent_posts_expanded()
{
	sf_load_includes();
	sf_initialise_globals();

	echo(sf_render_new_post_list_user());
	return;
}

/* 	=====================================================================================

	sf_pm_tag($display)

	template tag to display number of new PMs in the current user inbox.  This tag includes
	default text that is output with the pm count data and inbox hyperlink.   This text can
	be supressed by setting $display to false. 	If supressed, the new PM count and hyperlink
	are returned to the call in an array.  A -1 count and empty url will be returned for
	guests or user that do not have PM permissions.  Additionally, if the default text is used,
	the no permissions for pm default text can be supressed or those without permissions.

	parameters:

		$display		Determines whether to display pm count plus informational text
		$usersonly		If $display is true, only display pm text for users with pm permissions

 	===================================================================================*/

function sf_pm_tag($display=true, $usersonly=false)
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	$pm = array();
	if ($current_user->sfusepm)
	{
		$pm['count'] = $wpdb->get_var("SELECT COUNT(message_id) AS cnt FROM ".SFMESSAGES." WHERE (to_id = ".$current_user->ID." AND message_status = 0 AND inbox=1)");
		$pm['url'] = sf_build_qurl("pmaction=viewinpm&amp;pms={$current_user->ID}");
	} else {
		$pm['count'] = -1;
		$pm['url'] = '';
	}

	if ($display)
	{
		$out = '';
		if ($current_user->sfusepm)
		{
			$out .= '<p class="sfpmcount">';
			$out .= __("You have ", "sforum").$pm['count'].__(" PM(s) in your ", "sforum").'<a href="'.$pm['url'].'">'.__("inbox", "sforum").'</a>.';
			$out .= '</p>';
		} else if (!$usersonly){
			$out .= '<p class="sfpmcount">';
			$out .= __("You do not have PM permissions.", "sforum");
			$out .= '</p>';
		}
		echo $out;
	}
	return $pm;
}

/* 	=====================================================================================

	sf_sendpm_tag($userid, $text)

	template tag to send a pm to a user.  Default text will be used for the link unless the
	optional $text argument is sent.  If you specify the $text argument, you need to specify
	where in the string you want the link inserted by the sequence %%.  For example:

	$text = '<a href="%%" title="Send PM">Send PM</a>';

	If the person viewing the site is not a registered member or does not have PM permissions,
	then an empty string is returned.

	parameters:

		$userid		user to send a PM to
		$text		optional parameter to specify text, img or html for the link

 	===================================================================================*/

function sf_sendpm_tag($userid, $text='')
{
	global $current_user;

	sf_load_includes();
	sf_initialise_globals();

	# dont display tag if not a registered user
	if ($current_user->ID == '' || !$current_user->sfusepm) return;

	$out = '';
	if ($userid)
	{
		$buddy_name = stripslashes(sf_get_member_item($userid, "display_name"));
		$url = sf_build_qurl("pmaction=sendpm&amp;pms={$current_user->ID}&amp;pmtoname={$buddy_name}");
		if ($text == '')
		{
			$out.= '<a class="sfsendpmtag" href="'.$url.'"><img src="'.SFRESOURCES.'sendpm-small.png" title="'.__("Send PM", "sforum").'" />&nbsp;'.sf_render_icons("Send PM").'</a>';
		} else {
			$out.= str_replace('%%', $url, $text);
		}
	}

	echo $out;

	return;
}

/* 	=====================================================================================

	sf_highest_rated_posts($limit, $forum, $user, $postdate, $listtags, $forumids)

	displays the highest rated posts

	parameters:

		$limit			How many items to show in the list		number			10
		$forum			Show the Forum Title					true/false		false
		$user			Show the Users Name						true/false		true
		$postdate		Show date of posting					true/false		false
		$listtags		Wrap in <li> tags (li only)				true/false		true
		$forumids		comma delimited list of forum id's		optional		0

 	===================================================================================*/

function sf_highest_rated_posts($limit=10, $forum=true, $user=true, $postdate=true, $listtags=true, $forumids=0)
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	$out.'';

	$postratings = get_option('sfpostratings');
	if (!$postratings['sfpostratings'])
	{
		if ($listtags) $out.= "<li class='sftagli'>\n";
		$out.= __("Post Rating is not Enabled!", "sforum")."\n";
		if ($listtags) $out.= "</li>\n";
		return;
	}

	# are we passing forum ID's?
	if ($forumids == 0)
	{
		$where = '';
	} else {
		$flist = explode(",", $forumids);
		$where=' WHERE ';
		$x=0;
		for($x; $x<count($flist); $x++)
		{
			$where.= SFPOSTS.".forum_id = ".$flist[$x];
			if($x != count($flist)-1) $where.= " OR ";
		}
	}

	# how to order
	if ($postratings['sfratingsstyle'] == 1)  # thumb up/down
	{
		$order = "ORDER BY ratings_sum DESC";
	} else {
		$order = "ORDER BY (ratings_sum / vote_count) DESC";
	}

	$sfposts = $wpdb->get_results(
			"SELECT ".SFPOSTRATINGS.".post_id, ratings_sum, vote_count, ".SFPOSTS.".topic_id, ".SFPOSTS.".forum_id, ".SFPOSTS.".user_id, post_date, post_index, topic_slug, topic_name, forum_slug, forum_name, display_name, guest_name
			FROM ".SFPOSTRATINGS."
			LEFT JOIN ".SFPOSTS." ON ".SFPOSTRATINGS.".post_id = ".SFPOSTS.".post_id
			LEFT JOIN ".SFTOPICS." ON ".SFPOSTS.".topic_id = ".SFTOPICS.".topic_id
			LEFT JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id
			LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			".$where."
			".$order."
			LIMIT ".$limit);

	if ($sfposts)
	{
		foreach ($sfposts as $sfpost)
		{
			if (sf_can_view_forum($sfpost->forum_id))
			{
				# Start contruction
				if ($listtags) $out.= "<li class='sftagli'>\n";

				$out .= '<a href="'.sf_build_url($sfpost->forum_slug, $sfpost->topic_slug, 1, $sfpost->post_id, $sfpost->post_index).'">';

				$out.= $sfpost->topic_name;
				if ($forum)
				{
					$out.= ' '.__("posted in", "sforum").' '.stripslashes($sfpost->forum_name);
					$p = true;
				}

				if ($user)
				{
					$out.= ' '.__("by", "sforum").' ';
					$poster = sf_filter_user($sfpost->user_id, stripslashes($sfpost->display_name));
					if (empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($sfpost->guest_name));
					$out.= $poster;
					$p = true;
				}

				if ($postdate)
				{
					$out.= ' '.__("on", "sforum").mysql2date(SFDATES, $sfpost->post_date);
					$p=true;
				}

				$out.='</a>';
				if ($listtags) $out.= "</li>\n";
			}
		}
	} else {
		if ($listtags) $out.= "<li class='sftagli'>\n";
		$out.= __("No Rated Posts to Display", "sforum")."\n";
		if ($listtags) $out.= "</li>\n";
	}
	echo ($out);
	return;
}

/* 	=====================================================================================

	sf_most_rated_posts($limit, $forum, $user, $postdate, $listtags, $forumids)

	displays the highest rated posts

	parameters:

		$limit			How many items to show in the list		number			10
		$forum			Show the Forum Title					true/false		false
		$user			Show the Users Name						true/false		true
		$postdate		Show date of posting					true/false		false
		$listtags		Wrap in <li> tags (li only)				true/false		true
		$forumids		comma delimited list of forum id's		optional		0

 	===================================================================================*/

function sf_most_rated_posts($limit=10, $forum=true, $user=true, $postdate=true, $listtags=true, $forumids=0)
{
	global $wpdb, $current_user;

	sf_load_includes();
	sf_initialise_globals();

	$out.'';

	$postratings = get_option('sfpostratings');
	if (!$postratings['sfpostratings'])
	{
		if ($listtags) $out.= "<li class='sftagli'>\n";
		$out.= __("Post Rating is not Enabled!", "sforum")."\n";
		if ($listtags) $out.= "</li>\n";
		return;
	}

	# are we passing forum ID's?
	if ($forumids == 0)
	{
		$where = '';
	} else {
		$flist = explode(",", $forumids);
		$where=' WHERE ';
		$x=0;
		for($x; $x<count($flist); $x++)
		{
			$where.= SFPOSTS.".forum_id = ".$flist[$x];
			if($x != count($flist)-1) $where.= " OR ";
		}
	}

	$sfposts = $wpdb->get_results(
			"SELECT ".SFPOSTRATINGS.".post_id, ratings_sum, vote_count, ".SFPOSTS.".topic_id, ".SFPOSTS.".forum_id, ".SFPOSTS.".user_id, post_date, post_index, topic_slug, topic_name, forum_slug, forum_name, display_name, guest_name
			FROM ".SFPOSTRATINGS."
			LEFT JOIN ".SFPOSTS." ON ".SFPOSTRATINGS.".post_id = ".SFPOSTS.".post_id
			LEFT JOIN ".SFTOPICS." ON ".SFPOSTS.".topic_id = ".SFTOPICS.".topic_id
			LEFT JOIN ".SFFORUMS." ON ".SFPOSTS.".forum_id = ".SFFORUMS.".forum_id
			LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			".$where."
			ORDER BY vote_count DESC
			LIMIT ".$limit);

	if ($sfposts)
	{
		foreach ($sfposts as $sfpost)
		{
			if (sf_can_view_forum($sfpost->forum_id))
			{
				# Start contruction
				if ($listtags) $out.= "<li class='sftagli'>\n";

				$out .= '<a href="'.sf_build_url($sfpost->forum_slug, $sfpost->topic_slug, 1, $sfpost->post_id, $sfpost->post_index).'">';

				$out.= $sfpost->topic_name;
				if ($forum)
				{
					$out.= ' '.__("posted in", "sforum").' '.stripslashes($sfpost->forum_name);
					$p = true;
				}

				if ($user)
				{
					$out.= ' '.__("by", "sforum").' ';
					$poster = sf_filter_user($sfpost->user_id, stripslashes($sfpost->display_name));
					if (empty($poster)) $poster = apply_filters('sf_show_post_name', stripslashes($sfpost->guest_name));
					$out.= $poster;
					$p = true;
				}

				if ($postdate)
				{
					$out.= ' '.__("on", "sforum").' '.mysql2date(SFDATES, $sfpost->post_date);
					$p=true;
				}

				$out.='</a>';
				if ($listtags) $out.= "</li>\n";
			}
		}
	} else {
		if ($listtags) $out.= "<li class='sftagli'>\n";
		$out.= __("No Rated Posts to Display", "sforum")."\n";
		if ($listtags) $out.= "</li>\n";
	}
	echo ($out);
	return;
}

/* 	=====================================================================================

	sf_linked_topic_post_count()

	displays the number of topic posts in the currently displayed linked blog post

	parameters: None

	For use in the comments theme template

 	===================================================================================*/

function sf_linked_topic_post_count()
{
	global $wp_query;

	$result = '';
	$postid = $wp_query->post->ID;
	$checkrow = sf_blog_links_postmeta('read', $postid, '');

	if($checkrow)
	{
		# link found for this post
		$keys = explode('@', $checkrow->meta_value);
		$result = sf_get_posts_count_in_topic($keys[1]);
	}
	echo $result;
	return;
}

/* 	=====================================================================================

	sf_add_new_topic_tag($linktext, $beforelink, $afterlink, $beforetext, $aftertext)

	Creates a link for a user to go directly to a designated forum (set in tag options)
	and to an open Add Topic form.

	parameters:

		$linktext		textual content of link					text
			defaults to "Add new topic in the %FORUMNAME% forum"
			where placeholder %FORUMNAME% is eplaced by designated forum name

		$beforelink		before link text/HTML					''
		$afterlink		after link text/html					''
		$beforetext		before text text/HTML					''
		$aftertext		after text text/html					''

 	===================================================================================*/

function sf_add_new_topic_tag($linktext="Add new topic in the %FORUMNAME% forum", $beforelink='', $afterlink='', $beforetext='', $aftertext='')
{
	global $current_user;

	$forumid = get_option('sftaggedforum');
	if(empty($forumid)) return;

	sf_load_includes();
	sf_initialise_globals($forumid);

	if(sf_can_view_forum($forum_id))
	{
		$forum=sf_get_forum_record($forumid, true);
		$linktext = str_replace("%FORUMNAME%", $forum['forum_name'], $linktext);
		$url = trailingslashit(sf_build_url($forum['forum_slug'], '', 0, 0));
		$url = sf_get_sfurl_plus_amp($url).'new=topic';
		$out = $beforelink.'<a href="'.$url.'">'.$beforetext.$linktext.$aftertext.'</a>'.$afterlink;
		echo $out;
	}
	return;
}

/* 	=====================================================================================

	sf_sidedash_tag()

	Allows display of a common SPF dashboard on pages

	parameters:

		show_avatar		display user avatar						true/false								true
		show_pm			display pm template tag					true/false								true
		redirect		controls login/logout redirection		1=home, 2=admin, 3=cur page, 4=forum 	4
		show_admin_link	display link to admin dashboard			true/false								true
		show_login_link	display login form and lost pw link		true/false								true
 	===================================================================================*/

function sf_sidedash_tag($show_avatar=true, $show_pm=true, $redirect=4, $show_admin_link=true, $show_login_link=true)
{
	global $current_user;

	sf_initialise_globals();

//	if(function_exists(site_url()))
//	{
//		$siteurl = site_url();
//	} else {
//		$siteurl = get_option('siteurl');
//	}
	$siteurl=SFHOME;
	
	if ($redirect == 1)
	{
		$redirect_to = $siteurl;
	} else if ($redirect == 2) {
		$redirect_to = $siteurl.'wp-admin';
	} else if ($redirect == 3) {
		$redirect_to = $_SERVER['REQUEST_URI'];
	} else {
		$redirect_to = SFURL;
	}

	if($current_user->guest)
	{
	    # are we showing login form and lost password
		if ($show_login_link)
		{
			# display login form
			echo '<form action="'.$siteurl.'/wp-login.php?action=login" method="post">'."\n";
			echo '<div class="sftagusername"><label for="sftaglog">'.__("Username: ", "sforum").'<input type="text" name="log" id="sftaglog" value="" size="15" /></label></div>'."\n";
			echo '<div class="sftagpassword"><label for="sftagpwd">'.__("Password: ", "sforum").'<input type="password" name="pwd" id="sftagpwd" value="" size="15"  /></label></div>'."\n";
			echo '<div class="sftagremember"><input type="checkbox" id="rememberme" name="rememberme" value="forever" /><label for="rememberme">'.__("Remember me", "sforum").'</label></div>';
			echo '<input type="submit" name="submit" id="submit" value="'.__("Login", "sforum").'" />'."\n";
			echo '<input type="hidden" name="redirect_to" value="'.wp_specialchars($redirect_to).'" />'."\n";
			echo '</form>'."\n";
			echo '<p class="sftagguest"><a href="'.SFLOSTPASS.'">'.__("Lost Password", "sforum").'</a>'."\n";

		    # if registrations allowed, display register link
			if (get_option('users_can_register') == TRUE)
			{
				echo '<br /><a href="'.SFREGISTER.'">'.__("Register", "sforum").'</a></p>'."\n";
			}
		}
	} else {
		echo '<div class="sftagavatar">'.sf_show_avatar().'</div>';
		echo '<p class="sftag-loggedin">'.__("Logged in as", "sforum").' <strong>'.sf_filter_user($current_user->ID, stripslashes($current_user->display_name)).'</strong></p>'."\n";
		sf_pm_tag(true, false);
		if ($show_admin_link)
		{
			echo '<p class="sftag-admin"><a href="'.$siteurl.'/wp-admin'.'">'.__('Dashboard', "sforum").'</a></p>';
		}
		echo '<p class="sftag-logout"><a href="'.wp_nonce_url($siteurl.'/wp-login.php?action=logout&amp;redirect_to='.wp_specialchars($redirect_to), 'log-out').'">'.__('Logout', "sforum").'</a></p>'."\n";
	}
}

/* 	=====================================================================================

	sf_blog_linked_tag($postid, $show_img=true)

	Allows display of forum topic link for blog linked post outside of the post content

	parameters:

		$postid			id of the blog post					number				required
		$show_img		display blog linked image			true/fase			true
 	===================================================================================*/

function sf_blog_linked_tag($postid, $show_img=true)
{
	sf_initialise_globals();

	include_once('forum/sf-links.php');

    $checkrow = sf_blog_links_postmeta('read', $postid, '');
    if ($checkrow)
    {
        $keys = explode('@', $checkrow->meta_value);

		$text = stripslashes(get_option('sflinkblogtext'));
        $icon = '<img src="'.SFRESOURCES.'bloglink.png" alt="" />';
        if ($show_img)
        {
        	$text = str_replace('%ICON%', $icon, $text);
       	} else {
        	$text = str_replace('%ICON%', '', $text);
		}

        $postcount = sf_get_posts_count_in_topic($keys[1]);
        $counttext = ' - ('.$postcount.') '.__("Posts", "sforum");
        echo '<span class="sfforumlink"><a href="'.sf_build_url(sf_get_forum_slug($keys[0]), sf_get_topic_slug($keys[1]), 1, 0).'">'.$text.'</a>'.$counttext.'</span>';
    }
}

?>