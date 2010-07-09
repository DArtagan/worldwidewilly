<?php
/*
Simple:Press Forum
Filters
$LastChangedDate: 2009-06-21 22:55:21 +0100 (Sun, 21 Jun 2009) $
$Rev: 2094 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# ------------------------------------------------------------------
# sf_mail_filter_from()
#
# Filter Call
# Sets up the 'from' email options
#	$from:		Passed in to filter
# ------------------------------------------------------------------
function sf_mail_filter_from($from)
{
	$sfmail = get_option('sfmail');
	$mailfrom = $sfmail['sfmailfrom'];
	$maildomain = $sfmail['sfmaildomain'];
	if((!empty($mailfrom)) && (!empty($maildomain)))
	{
		$from = $mailfrom.'@'.$maildomain;
	}
	return $from;
}

# ------------------------------------------------------------------
# sf_mail_filter_name()
#
# Filter Call
# Sets up the 'from' email options
#	$from:		Passed in to filter
# ------------------------------------------------------------------
function sf_mail_filter_name($from)
{
	$sfmail = get_option('sfmail');
	$mailsender = $sfmail['sfmailsender'];
	if(!empty($mailsender))
	{
		$from = $mailsender;
	}
	return $from;
}

# ------------------------------------------------------------------
# sf_setup_page_title()
#
# Filter Call
# Sets up the page title option
#	$title:	Page title
# ------------------------------------------------------------------
function sf_setup_page_title($title)
{
	if(trim($title) == trim(SFPAGETITLE))
	{
		$sftitle = get_option('sftitle');

		if(!empty($sftitle['sfbanner'])) return '';
		if($sftitle['sfnotitle']) return '';
		if($sftitle['sfinclude']) return sf_setup_title($title, ' : ');
	}
	return $title;
}

# ------------------------------------------------------------------
# sf_setup_title()
#
# Support Routine
# Sets up the page title option
# ------------------------------------------------------------------
function sf_setup_title($title, $sep)
{
	global $current_user;

	$topicslug = get_query_var('sf_topic');
	if(!empty($topicslug))
	{
		$title.=$sep.sf_get_topic_name($topicslug);
		return $title;
	}

	$forumslug = get_query_var('sf_forum');
	if(!empty($forumslug))
	{
		if($forumslug != 'all')
		{
			$title.=$sep.sf_get_forum_name($forumslug);
			return $title;
		}
	}

	if(isset($_GET['pmaction']))
	{
		$pmview = "inbox";
		if(sf_syscheckstr($_GET['pmaction']) == 'viewoutpm')
		{
			$pmview = "sentbox";
		}
		return $title.=$sep.sprintf(__("Private Messaging (%s)", "sforum"), $pmview);
	}
	if (isset($_POST['viewperms'])) return $title.=$sep.__("Members Permissions", "sforum").' - '.$current_user->display_name;
	if (isset($_POST['mansubs']) || isset($_POST['uptopsubs']) || isset($_GET['subscribe'])) return  $title.=$sep.__("Members Subscriptions", "sforum").' - '.$current_user->display_name;
	if (isset($_POST['manbuddy'])) return $title.=$sep.__("Members Buddy List", "sforum").' - '.$current_user->display_name;
	if (isset($_POST['rpaction'])) return $title.=$sep.__("Report Post", "sforum");
	if (isset($_GET['policy'])) return $title.=$sep.__("Site Policy", "sforum");
	if (isset($_GET['list']) && $_GET['list'] = 'members') return $title.=$sep.__("List of Members", "sforum");
	if(isset($_GET['profile'])) return $title.=$sep.__("Members Profile", "sforum").' - '.$current_user->display_name;

	return $title;
}

# ------------------------------------------------------------------
# sf_dashboard_27_setup()
#
# Filter Call
# Sets up the forum advisory in the dashboard for WP2.7 +
# ------------------------------------------------------------------
function sf_dashboard_27_setup()
{
	global $sfglobals;

	sf_initialise_globals();

	if(($sfglobals['admin']['sfdashboardposts'] || $sfglobals['admin']['sfdashboardstats']) || (get_option('sfbuild') < 1319))
	{
	    wp_add_dashboard_widget('sf_announce', __('Forums','sforum' ), 'sf_announce');
	}
}

# ------------------------------------------------------------------
# sf_announce()
#
# Filter Call
# Sets up the forum advisory in the dashboard
# ------------------------------------------------------------------
function sf_announce()
{
	global $sfglobals, $current_user;

	if(!$current_user->adminstatus) return;

	$out='';

	# check we have an installed version
	if(sf_get_system_status() != 'ok')
	{
		$out.= '<div style="border: 1px solid #666666; padding: 10px; font-weight: bold;">'."\n";
		$out.= '<img class="sfalignleft" src="'.SFRESOURCES.'information.png" alt="" />'."\n";
		$out.= '<p>&nbsp;&nbsp;'.__("The forum is temporarily unavailable while being upgraded to a new version", "sforum").'</p>';

		if($current_user->forumadmin)
		{
			define('SFADMINFORUM', 'admin.php?page=simple-forum/admin/sf-adminforums.php');
			$out.= '&nbsp;&nbsp;<a style="text-decoration: underline;" href="'.trailingslashit(get_option('siteurl')).'wp-admin/'.SFADMINFORUM.'">'.__("Perform Upgrade", "sforum").'</a>';
		}
		$out.= '</div>';
		echo $out;
		return;
	}

	$out.= '<div id="sf-dashboard">';

	# If this is a pre 2.7 system then requires a heading
	if(version_compare(SFWPVERSION, '2.7', '<'))
	{
		$out = '<h3>'.__("Forums", "sforum").'</h3>';
	}

	# New/Unread Admin Post List
	if($sfglobals['admin']['sfdashboardposts'])
	{
		$unreads = sf_get_unread_forums();
		if($unreads)
		{
			$out.= '<p>'.__("New Forum Posts", "sforum").'</p>';
			$out.='<table class="sfdashtable">';
			foreach($unreads as $unread)
			{
				$out.='<tr>';
				if($unread->post_count == 1)
				{
					$mess = sprintf(__("There is %s new post", "sforum"), $unread->post_count);
				} else {
					$mess = sprintf(__("There are %s new posts", "sforum"), $unread->post_count);
				}
				$out.= '<td>'.$mess." ".__("in the forum topic", "sforum").'</td><td>'.sf_get_topic_url_dashboard(sf_get_forum_slug($unread->forum_id), sf_get_topic_slug($unread->topic_id))."</td>";
				$out.='</tr>';
			}
			$out.='</table>';
		} else {
			$out.='<p>'. __("There are no new forum posts", "sforum")."</p>";
		}
		$waiting = sf_get_awaiting_approval();
		if($waiting == 1)
		{
			$out.= '<table  class="sfdashtable><tr><td>'. __("There is 1 post awaiting approval", "sforum")."</td></tr></table>";
		}
		if($waiting > 1)
		{
			$out.= '<table class="sfdashtable><tr><td>'.sprintf(__("There are %s posts awaiting approval", "sforum"), $waiting)."</td></tr></table>";
		}
	}

	if($sfglobals['admin']['sfdashboardstats'])
	{
		include_once (SF_PLUGIN_DIR.'/forum/sf-pagecomponents.php');
		$out.= '<br /><table class="sfdashtable">'."\n";
		$out.= '<tr>'."\n";
		$out.= sf_render_online_stats();
		$out.= sf_render_forum_stats();
		$out.= sf_render_member_stats();
		$out.= '</tr><tr><td colspan="4">';
		$out.= sf_render_ownership();
		$out.= '</td></tr></table><br />'."\n";
	}

	$out.= '</div>';
	echo($out);

	return;
}

# ------------------------------------------------------------------
# sf_filter_content()
#
# Main post content filter routine
#	$content:		Unfiltered post content
#	$searchvalue:	If search - value can be highlighted for display
# ------------------------------------------------------------------
function sf_filter_content($content, $searchvalue)
{
	# do custom smileys first if in use
	$content = sf_convert_custom_smileys($content);
	# do all the defined filters
	$content = apply_filters('sf_show_post_content', $content);
	# run it through the wp smileys - may pick up a stray...
	$content = convert_smilies($content);

	return $content."\n";
}

# ------------------------------------------------------------------
# sf_convert_code()
#
# Try and change code tags to our code divs
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sf_convert_code($content)
{
	global $current_user, $sfglobals;

	if($sfglobals['editor']['sfeditor'] != RICHTEXT)
	{
		$content = str_replace('<code>', '<div class=\"sfcode\">', $content);
		$content = str_replace('</code>', '</div>', $content);
	} else {
		$content = str_replace('&lt;code&gt;', '<div class=\"sfcode\">', $content);
		$content = str_replace('&lt;/code&gt;', '</div>', $content);
	}

	return $content;
}

# ------------------------------------------------------------------
# sf_filter_nbsp()
#
# Remove tinymce constructs
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sf_filter_nbsp($content)
{
	# trim unwanted empty space
	$content = trim($content);

	# change tiny blank line to a br tag
	$content = str_replace("<p>&nbsp;</p>", "<br />", $content);

	# same for blank line with p tags
	$content = str_replace("<p></p>", "<br />", $content);

	# and p tags with br tags embedded
	$content = str_replace("<p><br /></p>", "<br />", $content);

	# change 2 br's to one (has to be done twice of course)
	$content = str_replace("<br />\n<br />", "<br />", $content);
	$content = str_replace("<br />\n<br />", "<br />", $content);

	return $content;
}

# ------------------------------------------------------------------
# sf_filter_square_brackets()
#
# Remove square brackets from titles
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sf_filter_square_brackets($content)
{
	$content = str_replace('[', '&#091;', $content);
	$content = str_replace(']', '&#093;', $content);

	return $content;
}

# ------------------------------------------------------------------
# sf_filter_nohtml_kses()
#
# Remove unwanted html (mainly form emails)
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sf_filter_nohtml_kses($content)
{
	return addslashes (wp_kses(stripslashes($content), array()));
}

# ------------------------------------------------------------------
# sf_show_image_thumbnail() and support functions
#
# Change large inages to small thumbnails and embed
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sf_show_image_thumbnail($content)
{
	return sf_swap_IMGs($content);
}

function sf_swap_IMGs($post)
{
	$newpost = preg_replace_callback('/<img[^>]*>/', 'sf_check_width' , $post);
	return $newpost;
}

function sf_check_width($match)
{
	$thumb = get_option('sfthumbsize');
	if((empty($thumb)) || ($thumb < 100)) $thumb=100;

	preg_match('/title="(.*?)"/', $match[0], $title);
	preg_match('/width="(.*?)"/', $match[0], $width);
	preg_match('/src="(.*?)"/', $match[0], $src);

	if((strpos($src[1], 'plugins/emotions')) || (strpos($src[1], 'images/smilies')) || (strpos($src[1], 'wp-content/forum-smileys')))
	{
		return $match[0];
	}

	# figure out whether its relative path (save server) or a url
    $parsed = parse_url($src[1]);
    if (array_key_exists('scheme', $parsed))
    {
    	$srcfile = $src[1];  # url, so leave it alone
    } else {
  		$srcfile = $_SERVER['DOCUMENT_ROOT'].$src[1];  # relative path, so add DOCUMENT_ROOT to path
  	}

	global $gis_error;
	set_error_handler('sf_gis_error');

	$size = getimagesize($srcfile);
	restore_error_handler();
	if($gis_error == '')
	{
		if ($size[0])
		{
			$width[1] = $size[0];
		} else {
			return '['.__('Image Removed by User', 'sforum').']';
		}
	}

	if(!isset($width[1])) return $match[0];

	if (((int)$width[1] > (int)$thumb) || (!isset($width[1])))
	{
		return '<a rel="highslide" class="highslide" href="'.$src[1].'" title="'.$title[1].'"><img src="'.$src[1].'" class="sfalignleft" border="0" width="'.$thumb.'" alt="" /><img src="'.SFRESOURCES.'mouse.png" alt="" /></a>';
	} else {
		return $match[0];
	}
}

# ------------------------------------------------------------------
# sf_profanity_check()
#
# Swaps any unwanted words for alternatives in post content
#	$postcontent:		Unfiltered post content
# ------------------------------------------------------------------
function sf_profanity_check($postcontent)
{
	$badwords = explode("\n", stripslashes(get_option('sfbadwords')));
	$replacementwords = explode("\n", stripslashes(get_option('sfreplacementwords')));

	# need to add in delimiter for preg replace
	foreach ($badwords as $index => $badword)
	{
		$badwords[$index] = '/\b'.trim($badword).'\b/i';
		$replacementwords[$index] = trim($replacementwords[$index]);
	}

	# filter the bad words
	$postcontent = preg_replace($badwords, $replacementwords, $postcontent);

	return $postcontent;
}

# ------------------------------------------------------------------
# sf_rel_nofollow()
#
# Adds nofollow to links at save post time
#	$postcontent:		Unfiltered post content
# ------------------------------------------------------------------

function sf_rel_nofollow($postcontent)
{
	$postcontent = preg_replace_callback('|<a (.+?)>|i', 'sf_rel_nofollow_callback', $postcontent);
	return $postcontent;
}

function sf_rel_nofollow_callback($matches)
{
	$text = $matches[1];
	$text = str_replace(array(' rel=\\"nofollow\\"', " rel=\\'nofollow\\'", 'rel=\\"nofollow\\"', "rel=\\'nofollow\\'"), '', $text);
	return "<a $text rel=\"nofollow\">";
}

# ------------------------------------------------------------------
# sf_target_blank()
#
# Forces target _blank to links at save post time
#	$postcontent:		Unfiltered post content
# ------------------------------------------------------------------
function sf_target_blank($postcontent)
{
	$postcontent = preg_replace_callback('|<a (.+?)>|i', 'sf_target_blank_callback', $postcontent);
	return $postcontent;
}

function sf_target_blank_callback($matches)
{
	$text = $matches[1];
	$text = str_replace(array(' target=\\"_blank\\"', " target=\\'_blank\\'", 'target=\\"_blank\\"', "target=\\'_blank\\'"), '', $text);
	return "<a $text target=\"_blank\">";
}

# ------------------------------------------------------------------
# sf_qt_filter()
#
# Filters html content in post content if quicktgs editor is used
#	$postcontent:		Unfiltered post content
# ------------------------------------------------------------------
function sf_qt_filter($postcontent)
{
	global $current_user, $sfglobals;

	if($sfglobals['editor']['sfeditor'] != RICHTEXT)
	{
		$postcontent = wp_filter_post_kses($postcontent);
	}
	return $postcontent;
}

# ------------------------------------------------------------------
# sf_parse_bbcode()
#
# Filters bbcode content in post content if bbcode editor is used
#	$postcontent:		Unfiltered post content
# ------------------------------------------------------------------
function sf_parse_bbcode($postcontent)
{
	global $sfglobals;

	if($sfglobals['editor']['sfeditor'] == BBCODE)
	{
		# load the bbcode to html parser
		include_once("parsers/sf-bbtohtml.php");
		$postcontent = sf_BBCode2Html(" ".stripslashes($postcontent));
	}
	return $postcontent;
}

# ------------------------------------------------------------------
# sf_convert_custom_smileys()
#
# Swaps codes for smileys if using custom images
#	$postcontent:		Unfiltered post content
# ------------------------------------------------------------------
function sf_convert_custom_smileys($postcontent)
{
	global $sfglobals;

	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']==1)
	{
		if($sfglobals['smileys'])
		{
			foreach ($sfglobals['smileys'] as $sname => $sinfo)
			{
				$postcontent = str_replace($sinfo[1], '<img src="'.SFSMILEYS.$sinfo[0].'" title="'.$sname.'" alt="'.$sname.'" />', $postcontent);
			}
		}
	}
	return $postcontent;
}

# ------------------------------------------------------------------
# sf_package_links()
#
# Turns urtls in posts to clickable links with shortened text
#	$postcontent:		Unfiltered post content
# Thanks to Peter at http://www.theblog.ca/shorten-urls for this
# ------------------------------------------------------------------
function sf_package_links($ret)
{
	$ret = make_clickable($ret);

	# pad it with a space
	$ret = ' ' . $ret;
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "$1<a href='$2' rel='nofollow'>$2</a>", $ret);
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "$1<a href='http://$2' rel='nofollow'>$2</a>", $ret);
	# chunk those long urls
	sf_package_links_chunk($ret);
	$ret = preg_replace("#(\s)([a-z0-9\-_.]+)@([^,< \n\r]+)#i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $ret);
	# Remove our padding..
	$ret = substr($ret, 1);
	return($ret);
}

function sf_package_links_chunk(&$ret)
{
	$links = explode('<a', $ret);
	$countlinks = count($links);
	for ($i = 0; $i < $countlinks; $i++)
	{
		$link = $links[$i];
		$link = (preg_match('#(.*)(href=")#is', $link)) ? '<a' . $link : $link;
		$begin = strpos($link, '>') + 1;
		$end = strpos($link, '<', $begin);
		$length = $end - $begin;
		$urlname = substr($link, $begin, $length);

		# We chunk urls that are longer than 50 characters. Just change
		# '50' to a value that suits your taste. We are not chunking the link
		# text unless if begins with 'http://', 'ftp://', or 'www.'
		$chunked = (strlen($urlname) > 40 && preg_match('#^(http://|ftp://|www\.)#is', $urlname)) ? substr_replace($urlname, '.....', 30, -10) : $urlname;
		$ret = str_replace('>' . $urlname . '<', '>' . $chunked . '<', $ret);
	}
}

# ------------------------------------------------------------------
# sf_block_admin()
#
# Blocks normal users from accessing WP admin area
# ------------------------------------------------------------------
function sf_block_admin()
{
	global $current_user;

	# Is this the admin interface?
	if (strstr(strtolower($_SERVER['REQUEST_URI']),'/wp-admin/') && !strstr(strtolower($_SERVER['REQUEST_URI']),'async-upload.php'))
	{
		$is_moderator = sf_get_member_item($current_user->ID, 'moderator');
		if (!sf_current_user_can('SPF Manage Options') &&
		    !sf_current_user_can('SPF Manage Forums') &&
		    !sf_current_user_can('SPF Manage Components') &&
		    !sf_current_user_can('SPF Manage User Groups') &&
		    !sf_current_user_can('SPF Manage Permissions') &&
		    !sf_current_user_can('SPF Manage Database') &&
		    !sf_current_user_can('SPF Manage Users') &&
		    !sf_current_user_can('SPF Manage Admins') &&
			!$is_moderator &&
		    !current_user_can('level_10'))
		{
			wp_redirect(SFURL, 302);
		}
	}
}

?>