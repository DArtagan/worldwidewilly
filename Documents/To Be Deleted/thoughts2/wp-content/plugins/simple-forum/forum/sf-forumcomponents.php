<?php
/*
Simple:Press Forum
Forum Rendering Routines (Topics)
$LastChangedDate: 2009-01-04 20:29:21 +0000 (Sun, 04 Jan 2009) $
$Rev: 1138 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# = SEARCH HEADING TEXT =======================
if(!function_exists('sf_render_search_heading')):
function sf_render_search_heading($search)
{
	# Display search text if in search mode
	if($search)
	{
		return __("Topics Matching Search", "sforum");
	} else {
		return __("Topics", "sforum");
	}
}
endif;

# = CONSTRUCT PAGED TOPIC NAVIGATION TABLE ====
if(!function_exists('sf_compile_paged_topics')):
function sf_compile_paged_topics($forumslug, $forumid, $currentpage, $search, $currentsearchpage, $topiccount, $watch=false, $list=false)
{
	$tpaged=get_option('sfpagedtopics');
	if(!isset($currentpage)) $currentpage = 1;
	if(($search) && (!isset($currentsearchpage))) $currentsearchpage = 1;
	$cpage=$currentpage;
	if($search)
	{
		$cpage=$currentsearchpage;
		$searchvalue = attribute_escape($_GET['value']);
		$searchvalue=urldecode($searchvalue);
	}
	$baseurl = '<a href="'.sf_build_url($forumslug, '', 0, 0);

	if($forumslug == 'all')
	{
		$topiccount = get_sfsetting($searchvalue);
		delete_sfsetting($searchvalue);
		$baseurl = '<a href="'.sf_build_qurl('forum='.$forumslug);
	} else if ($forumslug == 'watchedtopics') {
		$baseurl = '<a href="'.sf_build_qurl('watchedtopics=all');
	} else if ($forumslug == 'list') {
		$baseurl = '<a href="'.sf_build_qurl('list=members&search='.$currentsearchpage);
	} else {
		if($search)
		{
			$topiccount = get_sfsetting($searchvalue);
			delete_sfsetting($searchvalue);
			$baseurl = '<a href="'.sf_build_qurl('forum='.$forumslug);
		}
	}
	$totalpages = ($topiccount / $tpaged);
	if(!is_int($totalpages)) $totalpages = (intval($totalpages)+1);

	if($search) $baseurl.= '&amp;value='.urlencode($searchvalue);

	$out= __("Page:", "sforum").' ';

	$out.= sf_pn_next($cpage, $search, $totalpages, $baseurl, get_option('sfpaging'), $watch, $list);
	if ($search)
	{
		$out.= '&nbsp;&nbsp;' . $baseurl. '&amp;search='.$cpage . '" class="current">'.$cpage.'</a>'. '&nbsp;&nbsp;'."\n";
	} else if ($watch || $list)
	{
		$out.= '&nbsp;&nbsp;' . $baseurl. '&amp;page='.$cpage . '" class="current">'.$cpage.'</a>'. '&nbsp;&nbsp;'."\n";
	} else {
		if ($cpage > 1)
		{
			$out.= '&nbsp;&nbsp;' . $baseurl. '/page-'.$cpage . '" class="current">'.$cpage.'</a>'. '&nbsp;&nbsp;'."\n";
		} else {
			$out.= '&nbsp;&nbsp;' . $baseurl. '" class="current">'.$cpage.'</a>'. '&nbsp;&nbsp;'."\n";
		}
	}
	$out.= sf_pn_previous($cpage, $search, $totalpages, $baseurl, get_option('sfpaging'), $watch, $list);

	return $out;
}
endif;

# = FORUM TOPICS PAGE NAV STRIP ===============
if(!function_exists('sf_render_topic_pagelinks')):
function sf_render_topic_pagelinks($thispagelinks, $bottom, $showadd, $forumlock, $showlegend=true)
{
	global $current_user;

	$out = '<table class="sffooter"><tr>'."\n";
	$out.= '<td class="sfpagelinks">'.$thispagelinks.'</td>'."\n";

	if($bottom)
	{
		$out.= '<td class="sfadditemcell">'."\n";
		if($showadd)
		{
			if($current_user->offmember)
			{
				$out.= '<a class="sficon" href="'.SFLOGIN.'&amp;redirect_to='.urlencode($_SERVER['REQUEST_URI']).'"><img src="'.SFRESOURCES.'login.png" alt="" title="'.__("Login", "sforum").'" />'.sf_render_icons("Login").'</a>'."\n";
			} else {
				$out.= '<a class="sficon" onclick="sfjtoggleLayer(\'sfpostform\');"><img src="'.SFRESOURCES.'addtopic.png" alt="" title="'.__("Add a New Topic", "sforum").'" />'.sf_render_icons("Add a New Topic").'</a>'."\n";
			}
		} else {
			if($forumlock)
			{
				$out.= '<img class="sficon" src="'.SFRESOURCES.'locked.png" alt="" title="'.__("Forum Locked", "sforum").'"/>'.sf_render_icons("Forum Locked")."\n";
			}
		}
		$out.= '</td>';
	} else {
		if ($showlegend)
		{
			$out.= '<td align="right">'.sf_render_forum_icon_legend().'</td>';
		}
	}
	$out.= '</tr></table>'."\n";
	return $out;
}
endif;

# = LAST VISIT/POST ICON LEGEND ===============
if(!function_exists('sf_render_forum_icon_legend')):
function sf_render_forum_icon_legend()
{
	global $current_user;

	$out ='<span class="sficonkey"><small>';
	if($current_user->lastvisit > 0)
	{
		if($current_user->member)
		{
			$mess=__("since your last visit", "sforum");
		} else {
			$mess=__("since you last posted", "sforum");
		}
		$out.= '<img src="'. SFRESOURCES .'topickey.png" alt="" />&nbsp;&nbsp;'.__("New Posts", "sforum").'&nbsp;'.$mess.'&nbsp;&nbsp;'."\n";
	}
	if($current_user->member)
	{
		$out.= '<img src="'. SFRESOURCES .'topickeyuser.png" alt="" />&nbsp;&nbsp;'.__("Topics you have posted in", "sforum")."\n";
	}
	$out.='</small></span>';
	return $out;
}
endif;

# = COLUMN HEADER ROW =========================
if(!function_exists('sf_render_forum_column_header_row')):
function sf_render_forum_column_header_row($cols, $coldisplaytext, $admintools)
{
	$out = '<tr><th colspan="2">'.$coldisplaytext.'</th>'."\n";
	if($cols['first']) $out.= '<th>'.__("Started", "sforum").'</th>'."\n";
	if($cols['last']) $out.= '<th>'.__("Last Post", "sforum").'</th>'."\n";
	if($cols['posts']) $out.= '<th>'.__("Posts", "sforum").'</th>'."\n";
	if($cols['views']) $out.= '<th>'.__("Views", "sforum").'</th>'."\n";
	if($admintools)
	{
		$out.= '<th width="16"></th></tr>'."\n";
	} else {
		$out.= '</tr>'."\n";
	}
	return $out;
}
endif;

# = TOPIC ENTRY ROW ===========================
if(!function_exists('sf_render_topic_entry_row')):
function sf_render_topic_entry_row($forum, $topic, $cols, $stats, $value, $alt)
{
	global $current_user, $sfvars;

	if($topic['topic_pinned'])
	{
		$out = '<tr class="sfpinnedtopic" id="sfpinnedtopic-'.$topic['topic_id'].'">'."\n";
	} else {
		$out = '<tr>'."\n";
	}

	# Different icon depending on who and lastvisit etc.
	$out.= sf_render_topic_icon($topic['topic_id'], $stats[0]['udate'], $alt);

	if((isset($_POST['topicedit'])) && ($_POST['topicedit'] == $topic['topic_id']))
	{
		$out.= sf_edit_topic_title($topic['topic_id'], $topic['topic_name'], $forum['forum_id']);
	} else {
		$out.= '<td class="'.$alt.'"><p>'.sf_render_user_subscribed_icon($current_user->ID, $topic['topic_subs']);
		$out.= sf_render_user_watching_icon($current_user->ID, $topic['topic_id']);
		if(isset($value['watches']))
		{
			if ($value['watches'])
			{
				$out.=stripslashes($forum['forum_name']).'<br />';
			}
		}
		$out.= sf_get_topic_url($forum['forum_slug'], $topic['topic_slug'], $sfvars['page'], $topic['topic_name'], $topic['topic_status'], $topic['topic_pinned'], $value['search'], $value['searchpage'], urlencode($value['paramvalue']), $value['forumlock'], $topic['blog_post_id'])."\n";

		$out.= '<small>'.sf_render_inline_pagelinks($forum['forum_slug'], $topic['topic_slug'], $topic['post_count'], $value['searchpage'], urlencode($value['paramvalue']), $value['paramtype']).'</small></p>'."\n";

		$out.= sf_render_topic_statusflag($forum['topic_status_set'], $topic['topic_status_flag'], 'ts-forum'.$topic['topic_id'], 'ts-fview'.$topic['topic_id'], 'left');

		if(function_exists('sf_hook_post_topic'))
		{
			$out.= sf_hook_post_topic($forum['forum_id'], $topic['topic_id']);
		}
		$out.= '</td>'."\n";

		# Display first post column if option set
		if($cols['first'])
		{
			if(isset($stats[1]) ? $x=1 : $x=0);
			{
				$out.= sf_render_first_last_post_cell($forum['forum_slug'], $topic['topic_slug'], $stats[$x], $alt);
			}
		}

		# Display last post column if option set
		if($cols['last'])
		{
			$out.= sf_render_first_last_post_cell($forum['forum_slug'], $topic['topic_slug'], $stats[0], $alt);
		}

		# Display post count and views if options set
		if($cols['posts']) $out.= '<td class="sfcounts '.$alt.'">'.$topic['post_count'].'</td>'."\n";
		if($cols['views']) $out.= '<td class="sfcounts '.$alt.'">'.$topic['topic_opened'].'</td>'."\n";

		# Display admin icons if admin and option set
		if($value['admintools'])
		{
			$out.= '<td class="sfmanageicons '.$alt.'">'.sf_render_topic_editicons($topic, $forum, $sfvars['page']).'</td></tr>'."\n";
		} else {
			$out.= '</tr>'."\n";
		}
	}
	return $out;
}
endif;

# = TOPIC ROW SUPPORT: TOPIC ICON =============
if(!function_exists('sf_render_topic_icon')):
function sf_render_topic_icon($topicid, $lastudate, $alt)
{
	global $current_user;

	$icon = 1;
	if($current_user->guest)
	{
		if(($current_user->lastvisit > 0) && ($current_user->lastvisit < $lastudate)) $icon = 2;
	} else {
		if(sf_is_in_users_newposts($topicid)) $icon = 2;
		if(($icon == 1) && (sf_find_user_in_topic($topicid, $current_user->ID))) $icon = 3;
		if(($icon == 2) && (sf_find_user_in_topic($topicid, $current_user->ID))) $icon = 4;
	}
	switch($icon)
	{
		case 1:
			$topicicon = 'topic.png';
			break;
		case 2:
			$topicicon = 'topicnew.png';
			break;
		case 3:
			$topicicon = 'topicuser.png';
			break;
		case 4:
			$topicicon = 'topicnewuser.png';
			break;
	}
	return '<td class="sficoncell '.$alt.'"><img src="'. SFRESOURCES . $topicicon. '" alt="" /></td>'."\n";
}
endif;

# = TOPIC ROW SUPPORT: SUBSCRIBED ICON ========
if(!function_exists('sf_render_user_subscribed_icon')):
function sf_render_user_subscribed_icon($userid, $subs)
{
	global $current_user;

	if($current_user->guest) return;

	$out = '';
	if(!empty($subs))
	{
		$sublist = explode('@', $subs);
		foreach($sublist as $i)
		{
			if($i == $userid) $out = '<img class="sfstatusicon" src="'. SFRESOURCES .'usersubscribed.png" alt="" title="'.__("You are subscribed to this topic", "sforum").'" />';
		}
		if($current_user->forumadmin) $out = '<img class="sfstatusicon" src="'. SFRESOURCES .'usersubscribed.png" alt="" title="'.__("This topic has User Subscriptions", "sforum").'" />';
	}
	return $out;
}
endif;

# = TOPIC ROW SUPPORT: SUBSCRIBED ICON ========
if(!function_exists('sf_render_user_watching_icon')):
function sf_render_user_watching_icon($userid, $topicid)
{
	global $wpdb, $current_user;

	if ($current_user->guest) return;

	$out = '';
	$list = $wpdb->get_var("SELECT topic_watches FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if (!empty($list))
	{
		$watchlist = explode('@', $list);
		foreach ($watchlist as $watch)
		{
			if ($watch == $userid) $out = '<img class="sfstatusicon" src="'. SFRESOURCES .'watchicon.png" alt="" title="'.__("You are watching this topic", "sforum").'" />';
		}
		if ($current_user->forumadmin) $out = '<img class="sfstatusicon" src="'. SFRESOURCES .'watchicon.png" alt="" title="'.__("This topic has User Watches", "sforum").'" />';
	}

	return $out;
}
endif;

# = TOPIC ROW SUPPORT: ADMIN ICON TOOLS =======
if(!function_exists('sf_render_topic_editicons')):
function sf_render_topic_editicons($topic, $forum, $page)
{
	global $sfvars, $current_user;

	$locktext=__("Lock this Topic", "sforum");
	if($topic['topic_status']) $locktext=__("Unlock this Topic", "sforum");
	$pintext=__("Pin this Topic", "sforum");
	if($topic['topic_pinned']) $pintext=__("Unpin this Topic", "sforum");

	$order="ASC"; # default
	if(get_option('sfsortdesc')) $order="DESC"; # global override
	if($topic['topic_sort']) $order=$topic['topic_sort']; # topic override
	if($order == "ASC")
	{
		$sorttext=__("Sort Most Recent Posts to Top", "sforum");
	} else {
		$sorttext=__("Sort Most Recent Posts to Bottom", "sforum");
	}

	$boxname = 'tool'.$topic['topic_id'];
	$out = '<a class="sficon" href="" onclick="return sfjboxOverlay(this, \''.$boxname.'\', \'bottom\');"><img src="'.SFRESOURCES.'tools.png" alt="" title="'.__("show edit tools", "sforum").'" /></a>'."\n";
	$out.= '<div id="'.$boxname.'" style="display: none;">'."\n";

	if($current_user->sflock)
	{
		$out.= '<div class="sfspacer">'."\n";
		$out.= '<form action="'.sf_build_url($sfvars['forumslug'], '', $sfvars['page'], 0).'" method="post" name="topiclock'.$topic['topic_id'].'">'."\n";
		$out.= '<input type="hidden" name="locktopic" value="'.$topic['topic_id'].'" />'."\n";
		$out.= '<input type="hidden" name="locktopicaction" value="'.$locktext.'" />'."\n";
		$out.= '<a href="javascript:document.topiclock'.$topic['topic_id'].'.submit();"><img src="'.SFRESOURCES.'locked.png" alt="" title="'.$locktext.'" /></a><br />'."\n";
		$out.= '</form></div>'."\n";
	}

	if($current_user->sfpin)
	{
		$out.= '<div class="sfspacer">'."\n";
		$out.= '<form action="'.sf_build_url($sfvars['forumslug'], '', $sfvars['page'], 0).'" method="post" name="topicpin'.$topic['topic_id'].'">'."\n";
		$out.= '<input type="hidden" name="pintopic" value="'.$topic['topic_id'].'" />'."\n";
		$out.= '<input type="hidden" name="pintopicaction" value="'.$pintext.'" />'."\n";
		$out.= '<a href="javascript:document.topicpin'.$topic['topic_id'].'.submit();"><img src="'.SFRESOURCES.'pin.png" alt="" title="'.$pintext.'" /></a><br />'."\n";
		$out.= '</form></div>'."\n";
	}

	if($current_user->sfedit)
	{
		$out.= '<div class="sfspacer">'."\n";
		$out.= '<form action="'.sf_build_url($sfvars['forumslug'], '', $sfvars['page'], 0).'#topicedit" method="post" name="edittopic'.$topic['topic_id'].'">'."\n";
		$out.= '<input type="hidden" name="topicedit" value="'.$topic['topic_id'].'" />'."\n";
		$out.= '<a href="javascript:document.edittopic'.$topic['topic_id'].'.submit();"><img src="'.SFRESOURCES.'edit.png" alt="" title="'.__("edit this topic title", "sforum").'" /></a><br />'."\n";
		$out.= '</form></div>'."\n";
	}

	if(($current_user->sftopicstatus) && ($topic['topic_status_flag'] != 0))
	{
		$out.= '<div class="sfspacer"><form action="">'."\n";
		$site=SF_PLUGIN_URL."/forum/ahah/sf-ahahadmintools.php?action=ct&amp;id=".$topic['topic_id']."&amp;flag=".$topic['topic_status_flag']."&amp;set=".$forum['topic_status_set']."&amp;returnpage=".$sfvars['page'];
		$out.= '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFRESOURCES.'topicstatus.png" alt="" title="'.__("Change Topic Status", "sforum").'" /></a>';
		$out.= '</form></div>';
	}

	if($current_user->sfsort)
	{
		$out.= '<div class="sfspacer">'."\n";
		$out.= '<form action="'.sf_build_url($sfvars['forumslug'], '', $sfvars['page'], 0).'" method="post" name="topicsort'.$topic['topic_id'].'">'."\n";
		$out.= '<input type="hidden" name="sorttopic" value="'.$topic['topic_id'].'" />'."\n";
		$out.= '<input type="hidden" name="sorttopicaction" value="'.$sorttext.'" />'."\n";
		$out.= '<a href="javascript:document.topicsort'.$topic['topic_id'].'.submit();"><img src="'.SFRESOURCES.'sort.png" alt="" title="'.$sorttext.'" /></a><br />'."\n";
		$out.= '</form></div>'."\n";
	}

	if(sf_user_can_remove_queue($topic['topic_id']))
	{
		$out.= '<div class="sfspacer">'."\n";
		$out.= '<form action="'.sf_build_url($sfvars['forumslug'], '', $sfvars['page'], 0).'" method="post" name="topickill'.$topic['topic_id'].'">'."\n";
		$out.= '<input type="hidden" name="killtopic" value="'.$topic['topic_id'].'" />'."\n";
		$msg = __("Are you sure you want to delete this Topic?", "sforum");
		$out.= '<a href="javascript: if(confirm(\''.$msg.'\')) {document.topickill'.$topic['topic_id'].'.submit();}"><img src="'.SFRESOURCES.'delete.png" alt="" title="'.__("delete this topic", "sforum").'" /></a><br />'."\n";
		$out.= '</form></div>'."\n";
	}

	if($current_user->sfmovetopics)
	{
		$out.= '<div class="sfspacer"><form action="">'."\n";
		$site=SF_PLUGIN_URL."/forum/ahah/sf-ahahadmintools.php?action=mt&amp;topicid=".$topic['topic_id']."&amp;forumid=".$forum['forum_id'];
		$out.= '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFRESOURCES.'move.png" alt="" title="'.__("move this topic", "sforum").'" /></a>';
		$out.= '</form></div>';
	}

	if(($topic['blog_post_id'] != 0) && ($current_user->sfbreaklink))
	{
		$out.= '<div class="sfspacer">'."\n";
		$out.= '<form action="'.sf_build_url($sfvars['forumslug'], '', $sfvars['page'], 0).'" method="post" name="breaklink'.$topic['topic_id'].'">'."\n";
		$out.= '<input type="hidden" name="linkbreak" value="'.$topic['topic_id'].'" />'."\n";
		$out.= '<input type="hidden" name="blogpost" value="'.$topic['blog_post_id'].'" />'."\n";
		$out.= '<a href="javascript:document.breaklink'.$topic['topic_id'].'.submit();"><img src="'.SFRESOURCES.'breaklink.png" alt="" title="'.__("break topic link to blog post", "sforum").'" /></a><br />'."\n";
		$out.= '</form></div>'."\n";
	}

	if($current_user->forumadmin)
	{
		$out.= '<div class="sfspacer"><form action="">'."\n";
		$site=SF_PLUGIN_URL."/forum/ahah/sf-ahahadmintools.php?action=props&amp;group=".$forum['group_id']."&amp;forum=".$forum['forum_id']."&amp;topic=".$topic['topic_id'];
		$out.= '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFRESOURCES.'properties.png" alt="" title="'.__("topic properties", "sforum").'" /></a>';
		$out.= '</form></div>';
	}

	$out.= "</div>"."\n";

	return $out;
}
endif;

# = DISPLAY SEARCH ALL COLUMN HEADS ===========
if(!function_exists('sf_render_searchall_column_header_row')):
function sf_render_searchall_column_header_row($cols)
{
	$out = '<tr><th colspan="2">'.__("Forum/Topic", "sforum").'</th>'."\n";
	if($cols['first']) $out.= '<th>'.__("Started", "sforum").'</th>'."\n";
	if($cols['last']) $out.= '<th>'.__("Last Post", "sforum").'</th>'."\n";
	if($cols['posts']) $out.= '<th>'.__("Posts", "sforum").'</th>'."\n";
	$out.= '</tr>'."\n";
	return $out;
}
endif;

# = DISPLAY SEARCH ALL ROWS ===================
if(!function_exists('sf_render_searchall_entry_row')):
function sf_render_searchall_entry_row($topic, $cols, $stats, $value, $alt)
{
	# Display the topic entry
	$out = '<tr>'."\n";
	$out.= sf_render_topic_icon($topic['topic_id'], $stats[0]['udate'], $alt);
	$out.= '<td class="'.$alt.'"><p>' . stripslashes($topic['forum_name'])."\n";
	$out.= '<br /><a href="'.sf_build_url($topic['forum_slug'], $topic['topic_slug'], 1, 0);

	if(strpos(SFURL, '?') === false)
	{
		$out.= '?value';
	} else {
		$out.= '&amp;value';
	}

	$out.= '='.urlencode($value['paramvalue']).'&amp;search='.$value['searchpage'].'&amp;ret=all">'.stripslashes($topic['topic_name']).'</a>'."\n";

	$out.= '<small>'.sf_render_inline_pagelinks($topic['forum_slug'], $topic['topic_slug'], $topic['post_count'], $value['searchpage'], urlencode($value['paramvalue']), $value['paramtype']).'</small></p></td>'."\n";

	# Display first post column if option set
	if($cols['first'])
	{
		if(isset($stats[1]) ? $x=1 : $x=0);
		{
			$out.= sf_render_first_last_post_cell($topic['forum_slug'], $topic['topic_slug'], $stats[$x], $alt);
		}
	}

	# Display last post column if option set
	if($cols['last'])
	{
		$out.= sf_render_first_last_post_cell($topic['forum_slug'], $topic['topic_slug'], $stats[0], $alt);
	}

	# Display post count and views if options set
	if($cols['posts']) $out.= '<td class="sfcounts '.$alt.'">'.$topic['post_count'].'</td>'."\n";

	$out.= '</tr>'."\n";

	return $out;
}
endif;

?>