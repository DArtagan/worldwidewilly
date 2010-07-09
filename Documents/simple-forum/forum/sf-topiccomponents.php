<?php
/*
Simple:Press Forum
Topic Rendering Routines (Posts)
$LastChangedDate: 2009-05-22 18:21:31 +0100 (Fri, 22 May 2009) $
$Rev: 1889 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# = CONSTRUCT PAGED POST NAVIGATION TABLE =====
if(!function_exists('sf_compile_paged_posts')):
function sf_compile_paged_posts($forumslug, $topicslug, $topicid, $currentpage, $postcount)
{
	if(!isset($currentpage)) $currentpage = 1;

	$ppaged=get_option('sfpagedposts');

	$totalpages = ($postcount / $ppaged);
	if(!is_int($totalpages)) $totalpages = (intval($totalpages)+1);

	if((isset($_GET['xtp'])) && (sf_syscheckint($_GET['xtp']) <> 1))
	{
		$xtp = '&amp;xtp='.sf_syscheckint($_GET['xtp']);
	} else {
		$xtp = '';
	}

	$out= __("Page:", "sforum").'  ';
	$baseurl = '<a href="'.sf_build_url($forumslug, $topicslug, 0, 0);

	$out.= sf_pn_next($currentpage, '', $totalpages, $baseurl, get_option('sfpostpaging'));
	if ($currentpage > 1)
	{
		$out.= '&nbsp;&nbsp;' . $baseurl. '/page-'.$currentpage . '" class="current">'.$currentpage.'</a>'. '&nbsp;&nbsp;'."\n";
	} else {
		$out.= '&nbsp;&nbsp;' . $baseurl. '" class="current">'.$currentpage.'</a>'. '&nbsp;&nbsp;'."\n";	}
	$out.= sf_pn_previous($currentpage, '', $totalpages, $baseurl, get_option('sfpostpaging'));

	return $out;
}
endif;

# = TOPIC POSTS PAGE NAV STRIP ================
if(!function_exists('sf_render_post_pagelinks')):
function sf_render_post_pagelinks($thispagelinks, $bottom, $topiclock, $subs, $tpage, $tpagecount)
{
	global $current_user, $sfglobals, $sfvars;

	$out = '<table class="sffooter"><tr>'."\n";

	if($bottom)
	{
		if($tpage < $tpagecount)
		{
			$out.= '<td align="center" colspan="4">'."\n";
			$out.= '<a href="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $tpage+1, 0).'"><img src="'.SFRESOURCES.'next.png" alt="" title="'.__("Next Page", "sforum").'" /></a>'."\n";
			$out.= '</td></tr><tr>'."\n";
		}
	}

	$out.= '<td class="sfpagelinks">'.$thispagelinks.'</td>'."\n";

	if(!$bottom)
	{
		$out.= '<td align="right">';
		$out.= '<table>';
		$out.= '<tr><td>'.sf_render_if_subscribed_icon($subs).'</td></tr>'."\n";
		$out.= '<tr><td>'.sf_render_if_watching_icon($sfvars['topicid']).'</td></tr>'."\n";
		$out.= '</table>';
		$out.= '</td>';

		if($tpage > 1)
		{
			$out.= '</tr><tr><td align="center" colspan="4">'."\n";
			$out.= '<a href="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $tpage-1, 0).'"><img src="'.SFRESOURCES.'previous.png" alt="" title="'.__("Previous Page", "sforum").'" /></a>'."\n";
			$out.= '</td>'."\n";
		}
	}

	if($bottom)
	{
		$out.='<td class="sfadditemcell">'."\n";
		# Display Reply to Post if allowed
		if((!$topiclock) || ($current_user->adminstatus))
		{
			if($sfglobals['lockdown'] == false || $current_user->adminstatus)
			{
				if($current_user->offmember)
				{
					$out.= '<a class="sficon" href="'.SFLOGIN.'&amp;redirect_to='.urlencode($_SERVER['REQUEST_URI']).'"><img src="'.SFRESOURCES.'login.png" alt="" title="'.__("Login", "sforum").'"  />'.sf_render_icons("Login").'</a>'."\n";
				} else {
					if ($current_user->sfreply)
					{
						$out.= '<a class="sficon" onclick="sfjtoggleLayer(\'sfpostform\');"><img src="'.SFRESOURCES.'addpost.png" alt="" title="'.__("Reply to Post", "sforum").'" />'.sf_render_icons("Reply to Post").'</a>'."\n";
					}
				}
			}
		}
		$out.= '</td>';
	}
	$out.= '</tr></table>'."\n";
	return $out;
}
endif;

# = TOPIC ROW SUPPORT: SUBSCRIBED ICON ========
if(!function_exists('sf_render_if_subscribed_icon')):
function sf_render_if_subscribed_icon($subs)
{
	global $current_user;

	if($current_user->guest) return;

	$out = '';
	$subtext = '';
	if(!empty($subs))
	{
		$out ='<span class="sficonkey"><small>';

		$sublist = explode('@', $subs);
		foreach($sublist as $i)
		{
			if($i == $current_user->ID) $subtext = '<img src="'. SFRESOURCES .'usersubscribed.png" alt="" />&nbsp;&nbsp;'.__("You are subscribed to this topic", "sforum");
		}
		if(empty($subtext))
		{
			if($current_user->forumadmin) $subtext= '&nbsp;&nbsp;<img src="'. SFRESOURCES .'usersubscribed.png" alt="" />&nbsp;&nbsp;'.__("This topic has User Subscriptions", "sforum");
		}
		$out.=$subtext.'</small></span>';
	}
	return $out;
}
endif;

# = TOPIC ROW SUPPORT: SUBSCRIBED ICON ========
if(!function_exists('sf_render_if_watching_icon')):
function sf_render_if_watching_icon($topicid)
{
	global $wpdb, $current_user;

	if ($current_user->guest) return;

	$out = '';
	$watchtext = '';
	$list = $wpdb->get_var("SELECT topic_watches FROM ".SFTOPICS." WHERE topic_id=".$topicid);
	if (!empty($list))
	{
		$out ='<span class="sficonkey"><small>';
		$watchlist = explode('@', $list);
		foreach ($watchlist as $watch)
		{
			if ($watch == $current_user->ID) $watchtext = '<img src="'. SFRESOURCES .'watchicon.png" alt="" />&nbsp;&nbsp;'.__("You are watching this topic", "sforum");
		}
		if(empty($watchtext))
		{
			if ($current_user->forumadmin) $watchtext = '&nbsp;&nbsp;<img src="'. SFRESOURCES .'watchicon.png" alt="" />&nbsp;&nbsp;'.__("This topic has User Watches", "sforum");
		}
		$out.=$watchtext.'</small></span>';

	}
	return $out;
}
endif;

# = COLUMN HEADER ROW =========================
if(!function_exists('sf_render_topic_column_header_row')):
function sf_render_topic_column_header_row($admintools)
{
	$out = '<tr>'."\n";
	if (get_option('sfuserabove'))
	{
		$out .= '<th>'.__("Post", "sforum").'</th>'."\n";
	} else {
		$out .= '<th>'.__("User", "sforum").'</th><th>'.__("Post", "sforum").'</th>'."\n";
	}

	$out.= '</tr>'."\n";

	return $out;
}
endif;

# = RENDER USER DETAILS CELL ABOVE ============
if(!function_exists('sf_render_poster_details_above')):
function sf_render_poster_details_above($post, $posterstatus, $poster, $userposts, $postcount, $admintools, $alt)
{
	$size = (get_option('sfavatarsize') + 25);
	# Inner poster details table
	$out = '<table  class="sfinnerusertable" cellpadding="5"><tr align="center">';
	$out.= '<td class="'.$alt.'" width="'.$size.'">'.sf_render_avatar($posterstatus, $post['user_id'], $post['user_email'], $post['guest_email']).'</td>'."\n";
	$out.= '<td class='.$alt.'><p><strong>' . $poster . '</strong>  -  '.sf_render_usertype($posterstatus, $post['user_id'], $userposts).'</p>';
	$out.= '<p>'.attribute_escape(get_usermeta($post['user_id'], 'location')).'</p>';
	$out.= '<p>'.mysql2date(SFTIMES, $post['post_date']).' - '.mysql2date(SFDATES, $post['post_date']).'</p>'."\n";
	$out.= '<p>'.$postcount.'</p></td>';
	$out.= '</tr></table>';
	return $out;
}
endif;

# = RENDER USER DETAILS CELL SIDE =============
if(!function_exists('sf_render_poster_details_side')):
function sf_render_poster_details_side($post, $posterstatus, $poster, $userposts, $postcount, $admintools, $alt)
{
	# Inner poster details table
	$out = '<table class="sfinnerusertable" cellpadding="0" cellspacing="0" border="0"><tr align="center">';

	$out.= '<td class="sfposticonstrip '.$alt.'">'."\n";
	$out.= '<p>'.mysql2date(SFTIMES, $post['post_date']).'<br />'.mysql2date(SFDATES, $post['post_date']).'</p>'."\n";
	$out.= '</td></tr><tr>';

	$out.= '<td class="'.$alt.'"><p><br /><strong>' . $poster . '</strong></p>';
	$out.= '<p>'.sf_render_usertype($posterstatus, $post['user_id'], $userposts).'</p></td></tr>';
	$out.= '<tr><td class="'.$alt.'"></td></tr>';
	$out.= '<tr align="center"><td class="'.$alt.'"><p>'.attribute_escape(get_usermeta($post['user_id'], 'location')).'</p></td></tr>';
	$out.= '<tr align="center"><td class="'.$alt.'">'.sf_render_avatar($posterstatus, $post['user_id'], $post['user_email'], $post['guest_email']).'</td></tr>'."\n";
	$out.= '<tr><td class="'.$alt.'"></td></tr>';
	$out.= '<tr align="center"><td class="'.$alt.'"><p>'.$postcount.'</p></td>';
	$out.= '</tr></table>';
	return $out;
}
endif;

# = RENDER POSTERS AVATAR =====================
if(!function_exists('sf_render_avatar')):
function sf_render_avatar($icon, $userid, $useremail, $guestemail, $tag=false, $tagsize=0)
{
	if(get_option('sfshowavatars') == true)
	{
		$image='';

		switch($icon)
		{
			case 'user':
				if(!empty($userid)) $image=sf_get_member_item($userid, 'avatar');
				if(empty($image)) $image='userdefault.png';
				break;

			case 'admin':
				if(!empty($userid)) $image=sf_get_member_item($userid, 'avatar');
				if(empty($image)) $image='admindefault.png';
				break;

			case 'guest':
				$image = 'guestdefault.png';
				break;
		}

		$default = SFAVATARURL.$image;
		$email=$useremail;
		if(empty($email)) $email=$guestemail;
		$size = get_option('sfavatarsize');

		if(get_option('sfwpavatar'))
		{
			$out='<div class="sfuseravatar">';
			$out.= get_avatar($email, $size, '');
			$out.= '</div>';
		} elseif(get_option('sfgravatar')) {

			if (function_exists('gravatar_path'))
			{
				$url=gravatar_path(strtolower($email), $default);
			} else {
				$rating = get_option('sfgmaxrating');
				if ($rating == 1) $grating = 'G';
				if ($rating == 2) $grating = 'PG';
				if ($rating == 3) $grating = 'R';
				if ($rating == 4) $grating = 'X';
				$url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5(strtolower($email))."&amp;default=".urlencode($default)."&amp;size=".$size."&amp;rating=".$grating;
			}
		} else {
			$url = $default;
		}

		if(!$tag)
		{
			# forum
			if(get_option('sfwpavatar') == false)
			{
				$out='<div class="sfuseravatar"><img class="sfavatar" src="'.$url.'" alt="" /></div>'."\n";
			}
		} else {
			# template tag
			$tsize = '';
			if($tagsize != 0)
			{
				$tsize = ' width="'.$tagsize.'" height="'.$tagsize.'" ';
			}
			$out='<img class="sfavatartag" src="'.$url.'"'.$tsize.' alt="" />'."\n";
		}
	}
	return $out;
}
endif;

# = RENDER POSTERS USER TYPE (or RANKING) =====
if(!function_exists('sf_render_usertype')):
function sf_render_usertype($status, $userid, $userposts)
{
	switch($status)
	{
		case 'admin':
			$out= __("Admin", "sforum").' '."\n";
			break;
		case 'user':
			$ismod=false;
			$moderators = sf_get_moderators();
			if($moderators)
			{
				foreach($moderators as $mod)
				{
					if($userid == $mod['id']) $ismod=true;
				}
			}
			if($ismod)
			{
				$out= __("Moderator", "sforum").' '."\n";
			} else {
				$out= sf_render_user_ranking($userposts, __("Member", "sforum")).' '."\n";
			}
			break;
		case 'guest':
			$out= __("Guest", "sforum").' '."\n";
			break;
	}
	return $out;
}
endif;

# = GET POSTERS RANKING =======================
if(!function_exists('sf_render_user_ranking')):
function sf_render_user_ranking($userposts, $default)
{
	$out = $default;

	$rankdata = $GLOBALS['ranks'];
	if ($rankdata)
	{
		# find ranking of current user
		for ($x=0; $x<count($rankdata['posts']); $x++)
		{
			if ($userposts <= $rankdata['posts'][$x])
			{
				$out = $rankdata['title'][$x];
				break;
			}
		}
	}

	return $out;
}
endif;

# = GET ONLINE STATUS =========================
if(!function_exists('sf_render_online_status')):
function sf_render_online_status($userid)
{
	global $wpdb, $current_user;

	if(empty($userid)) return '';
	$status = $wpdb->get_var("SELECT id FROM ".SFTRACK." WHERE trackuserid=".$userid);
	if($status)
	{
		return '<img class="sficon sfonlinestatus" src="'.SFRESOURCES.'online.png" alt="" title="'.__("Member is On-Line", "sforum").'" />';
	} else {
		return '<img class="sficon sfonlinestatus" src="'.SFRESOURCES.'offline.png" alt="" title="'.__("Member is Off-Line", "sforum").'" />';
	}
}
endif;

# = RENDER POST ICON STRIP ====================
if(!function_exists('sf_render_post_icon_strip')):
function sf_render_post_icon_strip($post, $posterstatus, $userid, $username, $currentguest, $currentmember, $displaypost, $topiclock, $lastpost, $alt, $admintools)
{
	global $sfvars, $sfglobals, $current_user, $postNumberOnPage;

	$out = '';

	$out.= '<td class="sfposticonstrip '.$alt.'">';
	$out.= '<div class="sfposticoncontainer">';

	if($admintools)
	{
		$boxname = 'tool'.$post['post_id'];
		$out .= '<div class="sfposticon"><a class="sficon" href="" onclick="return sfjboxOverlay(this, \''.$boxname.'\', \'bottom\');"><img src="'.SFRESOURCES.'tools.png" alt="" title="'.__("show edit tools", "sforum").'" /></a></div>'."\n";
	}

	# Is extended profile option on?
	if((get_option('sfextprofile') && $current_user->sfprofiles) && ($posterstatus != 'guest'))
	{
		$out.= '<div class="sfposticon">'.sf_render_extended_profile_url($post['user_id']).'</div>'."\n";
	}

	$out.= '<div class="sfposticon">'.sf_render_online_status($post['user_id']).'</div>';

	# Do we show Quote and/or Edit icons?
	if((($currentmember) || ($currentguest)) || ($displaypost))
	{
		# Quote Icon
		if(($displaypost) && ($sfglobals['lockdown'] == false))
		{
			if($current_user->sfreply)
			{
				if(!$topiclock) $out.= '<div class="sfposticon">'.sf_render_post_user_quoteicon($post['post_id'], $username).'</div>';
			}

			# Report Post
			if($current_user->sfreport)
			{
				$out.= '<div class="sfposticon">'.sf_render_report_post_icon($post['post_id'], $username).'</div>';
			}
		}

		# Edit Icon
		if((($currentmember) || ($currentguest)) && ($sfglobals['lockdown'] == false))
		{
			if((($current_user->sfstopedit) && ($lastpost)) || ($current_user->sfeditall))
			{
				$out.= '<div class="sfposticon">'.sf_render_post_user_editicon($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], $post['post_id']).'</div>';
			}
		}

		# Send PM Icon
		if(($posterstatus != 'guest') && ($current_user->sfusepm))
		{
			if(($userid != $post['user_id']) && (sf_is_pm_user($post['user_id'])))
			{
				$out.= '<div class="sfposticon">'.sf_render_post_user_sendpmicon($userid, $post['user_id'], $username, $post['post_id']).'</div>';
			}
		}
	}

	# Is this post pinned?
	if($post['post_pinned'] == 1)
	{
		$out.= '<div class="sfposticon"><img class="sficon" src="'.SFRESOURCES.'pin.png" alt="" title="'.__("post pinned", "sforum").'" /></div>'."\n";
	}

	$pptitle = __("Post Permalink", "sforum");
	$out.= '<div class="sfposticon"><a href="" id="sfshowlink'.$post['post_id'].'" onclick="sfjshowPostLink(\''.$pptitle.'\',\''.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], $post['post_id'], $post['post_index']).'\',\''.$post['post_id'].'\');return false;"><img src="'.SFRESOURCES.'link.png" alt="" title="'.__("permalink to this post", "sforum").'" /></a></div>'."\n";
	$out.='<div class="highslide-html-content" id="link-content'.$post['post_id'].'" style="overflow: scroll; width: 600px">';
	$out.='<div class="inline-edit" id="sfpostlink'.$post['post_id'].'"></div>';
	$out.='<input type="button" class="sfcontrol" id="sfclosevalid'.$postNumberOnPage.'" onclick="return hs.close(this)" value="'.__("Close", "sforum").'" />';
	$out.='</div>';

	$out.= '<div style="float:right;padding-left:8px;margin-right:-5px">';
	$out.= '<a href="#forumtop"><img src="'.SFRESOURCES.'top.png" alt="" title="'.__("go to top", "sforum").'" /></a></div>'."\n";
	$out.= '<div class="sfposticon sfpostNumberOnPage">'.$postNumberOnPage.'</div>';

	$out.= sf_render_post_ratings($post);

	$out.= '</div>';

	# Close the inner table cell
	$out.= '</td>';
	return $out;
}
endif;

# = RENDER POST RATINGS =======================
if(!function_exists('sf_render_post_ratings')):
function sf_render_post_ratings($post)
{
	global $current_user, $sfvars;

	$out = '';
	$postid = $post['post_id'];
	$postratings = get_option('sfpostratings');
	if ($postratings['sfpostratings'] && $current_user->sfrateposts)
	{
		$out.= '<div id="sfpostrating-'.$postid.'">';

		if (!isset($post['rating_id']))
		{
			$ratings = 0;
			$votes = 0;
			$voted = false;
		} else {
			$ratings = $post['ratings_sum'];
			$votes = $post['vote_count'];
			if ($current_user->member)
			{
				$members = unserialize($post['members']);
				if ($members)
				{
					$voted = array_search($current_user->ID, $members);
				} else {
					$voted = -1;
				}
			} else {
				$ips = unserialize($post['ips']);
				if ($ips)
				{
					$voted = array_search(getenv("REMOTE_ADDR"), $ips);
				} else {
					$voted = -1;
				}
			}
		}
		if ($postratings['sfratingsstyle'] == 1)  # thumb up/down
		{
			$out.= '<div class="sfpostratingscontainer sfthumbs">';
			$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahpostrating.php?fid=".$sfvars['forumid']."&amp;pid=".$postid."&amp;rate=down";
			$downlink = 'style="cursor: pointer;" onclick="javascript:sfjRatePost(\''.$postid.'\', \''.$site.'\');" ';
			$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahpostrating.php?fid=".$sfvars['forumid']."&amp;pid=".$postid."&amp;rate=up";
			$uplink = 'style="cursor: pointer;" onclick="javascript:sfjRatePost(\''.$postid.'\', \''.$site.'\');" ';
			$downimg = SFRESOURCES.'ratings/ratedown.png';
			$upimg = SFRESOURCES.'ratings/rateup.png';
			$uptext = __("Rate Post Up", "sforum");
			$downtext = __("Rate Post Down", "sforum");
			if (is_numeric($voted))
			{
				$downlink = '';
				$uplink = '';
				$downimg = SFRESOURCES.'ratings/ratedowngrey.png';
				$upimg = SFRESOURCES.'ratings/rateupgrey.png';
				$uptext = __("Post Rating: ", "sforum").$ratings;
				$downtext = $uptext;
			}

			$out.= '<div class="sfposticon sfpostrating">'.$ratings.'</div>';
			$out.= '<div class="sfposticon sfpostratedown"><img src="'.$downimg.'" alt="" title="'.$downtext.'" '.$downlink.'/></div>';
			$out.= '<div class="sfposticon sfpostrateup"><img src="'.$upimg.'" alt="" title="'.$uptext.'" '.$uplink.'/></div>';
		} else {
			$out.= '<div class="sfpostratingscontainer sfstars">';
			$offimg = SFRESOURCES.'ratings/ratestaroff.png';
			$onimg = SFRESOURCES.'ratings/ratestaron.png';
			$overimg = SFRESOURCES.'ratings/ratestarover.png';
			if ($votes)
			{
				$star_rating = round($ratings / $votes, 1);
			} else {
				$star_rating = 0;
			}
			$intrating = floor($star_rating);
			$out.= '<div class="sfposticon sfpostrating">'.$star_rating.'</div>';
			$out.= '<div class="sfposticon sfpoststars">';
		    for ($x = 1; $x <= $intrating; $x++)
			{
				$name = ' id="star-'.$postid.'-'.$x.'"';
				if (is_numeric($voted))
				{
					$link = '';
					$text = __("Post Rating: ", "sforum").$star_rating;
				} else {
					if ($x == 1) $text = __("Rate Post 1 Star", "sforum");
					if ($x == 2) $text = __("Rate Post 2 Stars", "sforum");
					if ($x == 3) $text = __("Rate Post 3 Stars", "sforum");
					if ($x == 4) $text = __("Rate Post 4 Stars", "sforum");
					if ($x == 5) $text = __("Rate Post 5 Stars", "sforum");
					$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahpostrating.php?fid=".$sfvars['forumid']."&amp;pid=".$postid."&amp;rate=".$x;
					$link = 'style="cursor: pointer;" onclick="javascript:sfjRatePost(\''.$postid.'\', \''.$site.'\');" onmouseover="sfjstarhover(\''.$postid.'\', \''.$x.'\', \''.$overimg.'\')" onmouseout="sfjstarunhover(\''.$postid.'\', \''.$intrating.'\', \''.$onimg.'\', \''.$offimg.'\')" ';
				}
				$out.= '<img'.$name.' src="'.$onimg.'" alt="" title="'.$text.'" '.$link.'/>';
			}

		    for ($x = ($intrating+1); $x <= 5; $x++)
			{
				$name = ' id="star-'.$postid.'-'.$x.'"';
				if (is_numeric($voted))
				{
					$link = '';
					$text = __("Post Rating: ", "sforum").$star_rating;
				} else {
					if ($x == 1) $text = __("Rate Post 1 Star", "sforum");
					if ($x == 2) $text = __("Rate Post 2 Stars", "sforum");
					if ($x == 3) $text = __("Rate Post 3 Stars", "sforum");
					if ($x == 4) $text = __("Rate Post 4 Stars", "sforum");
					if ($x == 5) $text = __("Rate Post 5 Stars", "sforum");
					$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahpostrating.php?fid=".$sfvars['forumid']."&amp;pid=".$postid."&amp;rate=".$x;
					$link = 'style="cursor: pointer;" onclick="javascript:sfjRatePost(\''.$postid.'\', \''.$site.'\');" onmouseover="sfjstarhover(\''.$postid.'\', \''.$x.'\', \''.$overimg.'\')" onmouseout="sfjstarunhover(\''.$postid.'\', \''.$intrating.'\', \''.$onimg.'\', \''.$offimg.'\')" ';
				}
				$out.= '<img'.$name.' src="'.$offimg.'" alt="" title="'.$text.'" '.$link.'/>';
			}
			$out.= '</div>';
		}
		$out.= '</div>';
		$out.= '</div>';
	}
	return $out;
}
endif;

# = RENDER POST CONTENT =======================
if(!function_exists('sf_render_post_content')):
function sf_render_post_content($post, $editmode, $displaypost, $paramvalue, $approve_text, $currentguest, $currentmember, $bloglink)
{
	global $sfvars, $sfglobals;

	$out = '<a id="p'.$post['post_id'].'"></a>'."\n";

	if($editmode)
	{
		$postcontent = $post['post_content'];
		if($sfglobals['editor']['sfeditor'] == BBCODE)
		{
			# load the bbcode to html parser
			include_once("parsers/sf-htmltobb.php");
			$postcontent = sf_Html2BCode($postcontent);
		}

		$out.= sf_edit_post($post['post_id'], $postcontent, $sfvars['forumid'], $sfvars['topicid'], $sfvars['page'], $post['post_edit']);
	} else {
		$out.= '<div id="post'.$post['post_id'].'"';

		if($post['post_pinned'] == 1)
		{
			$out.=' class="sfpinned">'."\n";
		} else {
			$out.='>'."\n";
		}

		# display any post edit data
		if(!empty($post['post_edit']) && get_option('sfshoweditdata'))
		{
			$postedit = unserialize($post['post_edit']);
			$out.= '<p><small><i>';
			$x = 0;
			$showlast = get_option('sfshoweditlast');
			$lastedit = (count($postedit)-1);

			foreach($postedit as $edit)
			{
				if (($showlast && ($x==$lastedit)) || ($showlast == false))
				{
					$at = date(SFTIMES, $edit['at']).' - '.date(SFDATES, $edit['at']);
					$by = $edit['by'];
					$out.= sprintf(__("Post edited %s by %s", "sforum"), $at, $by).'<br />';
				}
				$x++;
			}
			$out.= '</i></small></p><hr />';
		}

		if($displaypost)
		{
			$out.= $approve_text.sf_filter_content(stripslashes($post['post_content']), $paramvalue);
		} else {
			$out.= sf_filter_content($approve_text, $paramvalue)."\n";

			if(($currentguest) || ($currentmember))
			{
				$out.= sf_filter_content(stripslashes($post['post_content']), $paramvalue);
			}
		}

		if($bloglink > 0)
		{
			$out.= '<br /><p>'.sf_forum_show_blog_link($bloglink).'</p>';
		}

		$out.= '</div>';
	}
	return $out;
}
endif;

# = RENDER SIGNATURE STRIP ====================
if(!function_exists('sf_render_signature_strip')):
function sf_render_signature_strip($sig, $sigimg)
{
	# force sig to have no follow in links
	$sig = sf_rel_nofollow($sig);

	$out = '<div class="sfsignaturestrip">'."\n";
	if(empty($sigimg))
	{
		$out.= '<p style="margin:auto; text-align:center; vertical-align:middle;"><small>'.$sig.'</small></p>'."\n";
	} else {
		$out.= '<table border="0" cellspacing="0" cellpadding="0"><tr>'."\n";
		$out.= '<td align="right" valign="middle"><img src="'.attribute_escape($sigimg).'" alt="" /></td>'."\n";
		$out.= '<td align="left" valign="middle"><p><small>'.$sig.'</small></p></td>'."\n";
		$out.= '</tr></table>'."\n";
	}
	$out.= '</div>'."\n";
	return $out;
}
endif;

# = RENDER TOPIC STATUS UPDATER ===============
if(!function_exists('sf_render_topic_status_updater')):
function sf_render_topic_status_updater($statusset, $statusflag)
{
	global $current_user;

	$out='';
	if($current_user->moderator)
	{
		$out.= sf_render_topic_statusflag($statusset, $statusflag, 'ts-topic', 'ts-upinline', 'right');
		if($statusflag != 0)
		{
			$out.= '<div class="sfalignright"><label><small>'.__("Change Topic Status", "sforum").':  '.sf_topic_status_select($statusset, $statusflag, true).'</small></label></div>';
		} else {
			$out.= '<div class="sfalignright"><label><small>'.__("Assign Topic Status", "sforum").':  '.sf_topic_status_select($statusset, $statusflag, true).'</small></label></div>';
		}
	}
	return $out;
}
endif;

# = RENDER QUOTE ICON =========================
if(!function_exists('sf_render_post_user_quoteicon')):
function sf_render_post_user_quoteicon($postid, $username)
{
	global $current_user, $sfglobals;

	$out = '';
	if($current_user->sfreply && $current_user->offmember == false)
	{
		$editor = 0;
		if($sfglobals['editor']['sfeditor'] == RICHTEXT) $editor = 1;
				$intro = '&lt;p&gt;'.htmlentities($username, ENT_COMPAT, get_bloginfo('charset')).' '.__("said:", "sforum").'&lt;/p&gt;';
				$out = '<a class="sficon" onclick="sfjquotePost(\'post'.$postid.'\', \''.$intro.'\', '.$editor.');"><img src="'.SFRESOURCES.'quote.png" alt="" title="'.__("Quote and Reply", "sforum").'" />&nbsp;'.sf_render_icons("Quote and Reply").'</a>'."\n";
	}
	return $out;
}
endif;

# = RENDER REPORT POST ICON ===================
if(!function_exists('sf_render_report_post_icon')):
function sf_render_report_post_icon($postid, $author)
{
	global $current_user;

	$out = '';
	$returnurl=SFURL;

	$out.= '<form class="sfhiddenform" action="'.SFURL.'" method="post" name="report'.$postid.'">'."\n";
	$out.= '<input type="hidden" class="sfhiddeninput" name="rpaction" value="report" />'."\n";
	$out.= '<input type="hidden" class="sfhiddeninput" name="rpurl" value="'.$returnurl.'" />'."\n";
	$out.= '<input type="hidden" class="sfhiddeninput" name="rpuser" value="'.$current_user->ID.'" />'."\n";
	$out.= '<input type="hidden" class="sfhiddeninput" name="rppost" value="'.$postid.'" />'."\n";
	$out.= '<input type="hidden" class="sfhiddeninput" name="rpposter" value="'.$author.'" />'."\n";
	$out.= '<a class="sficon" href="javascript:document.report'.$postid.'.submit();"><img src="'.SFRESOURCES.'reportpost.png" alt="" title="'.__("Report Post", "sforum").'" />&nbsp;'.sf_render_icons("Report Post").'</a>'."\n";
	$out.= '</form>'."\n";

	return $out;
}
endif;

# = RENDER SEND PM ICON =======================
if(!function_exists('sf_render_post_user_sendpmicon')):
function sf_render_post_user_sendpmicon($from_user, $to_user, $recipient, $postid)
{
	global $current_user;

	$returnurl=SFURL;

	update_sfsetting($current_user->ID.'@pmurl', $returnurl);

	$url=sf_build_qurl("pmaction=sendpm&amp;pms={$current_user->ID}&amp;pmtoname={$recipient}");

	$out = '<a class="sficon" href="'.$url.'"><img src="'.SFRESOURCES.'sendpm.png" alt="" title="'.__("Send PM", "sforum").'" />&nbsp;'.sf_render_icons("Send PM").'</a>'."\n";

	return $out;
}
endif;

# = RENDER EDIT ICON ==========================
if(!function_exists('sf_render_post_user_editicon')):
function sf_render_post_user_editicon($forumslug, $topicslug, $pageid, $postid)
{
	$out='';
	$out.= '<form class="sfhiddenform" action="'.sf_build_url($forumslug, $topicslug, $pageid, $postid).'" method="post" name="usereditpost'.$postid.'">'."\n";
	$out.= '<input type="hidden" class="sfhiddeninput" name="useredit" value="'.$postid.'" />'."\n";
	$out.= '<a class="sficon" href="javascript:document.usereditpost'.$postid.'.submit();"><img src="'.SFRESOURCES.'useredit.png" alt="" title="'.__("Edit Your Post", "sforum").'" />&nbsp;'.sf_render_icons("Edit Your Post").'</a>'."\n";
	$out.= '</form>'."\n";
	return $out;
}
endif;

# = RENDER POST ADMIN ICONS ===================
if(!function_exists('sf_render_post_editicons')):
function sf_render_post_editicons($postid, $postindex, $poststatus, $useremail, $guestemail, $pinned, $approve_only=false)
{
	global $sfvars, $current_user;

	if($approve_only == false)
	{
		if($pinned)
		{
			$pintext = __("Unpin this Post", "sforum");
		} else {
			$pintext = __("Pin this Post", "sforum");
		}

		$boxname = 'tool'.$postid;
		$out = '<div id="'.$boxname.'" style="display: none;">'."\n";
	}
	if(($poststatus == 1) && ($current_user->sfapprove))
	{
		$out.= '<form action="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], $postid, $postindex).'" method="post" name="postapprove'.$postid.'">'."\n";
		$out.= '<input type="hidden" name="approvepost" value="'.$postid.'" />'."\n";
		$out.= '<a href="javascript:document.postapprove'.$postid.'.submit();"><img src="'.SFRESOURCES.'approve.png" alt="" title="'.__("approve this post", "sforum").'" /></a>';
		if($approve_only == false) $out.='<br />';
		$out.= '</form>'."\n";
	}
	if($approve_only == false)
	{
		if($current_user->sfemail)
		{
			$email=$useremail;
			if(empty($email)) $email=$guestemail;
			$out.= '<form action="">'."\n";

			$out.= '<a href="" id="sfshowmail'.$postid.'" onclick="sfjshowUserMail(\'Users Email Address\',\''.$email.'\',\''.$postid.'\');return false;"><img src="'.SFRESOURCES.'email.png" alt="" title="'.__("show users email address", "sforum").'" /></a><br />'."\n";
			$out.='<div class="highslide-html-content" id="mail-content'.$postid.'" style="width: 300px">';
			$out.='<div class="inline-edit" id="sfmail'.$postid.'"></div>';
			$out.='<input type="button" class="sfcontrol" id="sfclosevalid'.$postid.'" onclick="return hs.close(this)" value="'.__("Close", "sforum").'" />';
			$out.='</div>';
			$out.= '</form>'."\n";
		}

		if($current_user->sfpin)
		{
			$out.= '<form action="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], $postid, $postindex).'" method="post" name="postpin'.$postid.'">'."\n";
			$out.= '<input type="hidden" name="pinpost" value="'.$postid.'" />'."\n";
			$out.= '<input type="hidden" name="pinpostaction" value="'.$pintext.'" />'."\n";
			$out.= '<a href="javascript:document.postpin'.$postid.'.submit();"><img src="'.SFRESOURCES.'pin.png" alt="" title="'.$pintext.'" /></a><br />'."\n";
			$out.= '</form>'."\n";
		}

		if($current_user->sfedit)
		{
			$out.= '<form action="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], $postid, $postindex).'" method="post" name="admineditpost'.$postid.'">'."\n";
			$out.= '<input type="hidden" name="adminedit" value="'.$postid.'" />'."\n";
			$out.= '<a href="javascript:document.admineditpost'.$postid.'.submit();"><img src="'.SFRESOURCES.'edit.png" alt="" title="'.__("edit this post", "sforum").'" /></a><br />'."\n";
			$out.= '</form>'."\n";
		}

		if(sf_user_can_remove_queue($sfvars['topicid']))
		{
			$out.= '<form action="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], 0).'" method="post" name="postkill'.$postid.'">'."\n";
			$out.= '<input type="hidden" name="killpost" value="'.$postid.'" />'."\n";
			$out.= '<input type="hidden" name="killposttopic" value="'.$sfvars['topicid'].'" />'."\n";
			$out.= '<input type="hidden" name="killpostforum" value="'.$sfvars['forumid'].'" />'."\n";
			$out.= '<a href="javascript: if(confirm(\'Are%20you%20sure%20you%20want%20to%20delete%20this%20Post?\')) {document.postkill'.$postid.'.submit();}"><img src="'.SFRESOURCES.'delete.png" alt="" title="'.__("delete this post", "sforum").'" /></a><br />'."\n";
			$out.= '</form>'."\n";
		}

		if($current_user->sfmoveposts)
		{
			$out.= '<form action="">'."\n";
			$site=SF_PLUGIN_URL."/forum/ahah/sf-ahahadmintools.php?action=mp&amp;id=".$sfvars['topicid']."&amp;pid=".$postid;
			$out.= '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFRESOURCES.'move.png" alt="" title="'.__("move this post", "sforum").'" /></a>';
			$out.= '</form>';
		}

		if($current_user->forumadmin)
		{
			$out.= '<form action="">'."\n";
			$site=SF_PLUGIN_URL."/forum/ahah/sf-ahahadmintools.php?action=props&amp;forum=".$sfvars['forumid']."&amp;topic=".$sfvars['topicid']."&amp;post=".$postid;
			$out.= '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFRESOURCES.'properties.png" alt="" title="'.__("post properties", "sforum").'" /></a>';
			$out.= '</form>';
		}

		$out.= '</div>'."\n";
	}
	return $out;
}
endif;

# = RENDER EXTENDED PROFILE ICON ==============
if(!function_exists('sf_render_extended_profile_url')):
function sf_render_extended_profile_url($userid)
{
	$site=SF_PLUGIN_URL."/forum/ahah/sf-ahahprofile.php?u=".$userid;
	$out = '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img class="sficon" src="'.SFRESOURCES.'user.png" alt="" title="'.__("view user profile", "sforum").'" /></a>';

	return $out;
}
endif;

?>