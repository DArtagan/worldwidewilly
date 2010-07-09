<?php
/*
Simple:Press Forum
Public Functions to support Actions/filters
$LastChangedDate: 2009-04-23 20:37:06 +0100 (Thu, 23 Apr 2009) $
$Rev: 1771 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# ------------------------------------------------------------------
# sf_track_logout()
#
# Filter Call
# Sets up the last visited upon user logout
# ------------------------------------------------------------------
function sf_call_track_logout()
{
	sf_load_foundation();
	sf_track_logout();
	return;
}

# ------------------------------------------------------------------
# sf_register_math()
#
# Filter Call
# Sets up the spam math on registration form
# ------------------------------------------------------------------
function sf_register_math()
{
	$sflogin = array();
	$sflogin = get_option('sflogin');
	if($sflogin['sfregmath'])
	{
		include_once('forum/sf-primitives.php');

		$spammath = sf_math_spam_build();

		$out ='<input type="hidden" size="30" name="url" value="" /></p>'."\n";
		$out.='<p><strong>'.__("Math Required!", "sforum").'</strong><br />'."\n";
		$out.=sprintf(__("What is the sum of: <strong> %s + %s </strong>", "sforum"), $spammath[0], $spammath[1]).'&nbsp;&nbsp;&nbsp;'."\n";
		$out.='<input type="text" tabindex="3" size="7" id="sfvalue1" name="sfvalue1" value="" /></p>'."\n";
		$out.='<input type="hidden" name="sfvalue2" value="'.$spammath[2].'" />'."\n";
		echo $out;
	}
	return;
}

# ------------------------------------------------------------------
# sf_register_error()
#
# Filter Call
# Sets up the spam math error is required
#	$errors:	registration errors array
# ------------------------------------------------------------------
function sf_register_error($errors)
{
	global $ISFORUM;

	$sflogin = array();
	$sflogin = get_option('sflogin');

	if($sflogin['sfregmath'])
	{
		include_once('forum/sf-primitives.php');

		$spamtest=sf_spamcheck();
		if($spamtest[0] == true)
		{
			$errormsg = str_replace('1@', '<b>ERROR</b>: ', $spamtest[1]);

			if($ISFORUM == false)
			{
				$errors->add('Bad Math', $errormsg);
			} else {
				$errors['math_check'] = $errormsg;
			}
		}
	}
	return $errors;
}

# ------------------------------------------------------------------
# sf_setup_browser_title()
#
# Filter call
# Sets up the browser page title if All In One SEO Pack installed
#	$title		page title
# ------------------------------------------------------------------
function sf_setup_browser_title($title)
{
	# check if alll in one seo pack is installed
	if((class_exists('Platinum_SEO_Pack') || ('All_in_One_SEO_Pack')) && trim($title) == trim(SFPAGETITLE))
	{
		require_once('forum/sf-filters.php');
		require_once('forum/sf-database.php');

		$sfaiosp = get_option('sfaiosp');

		if ($sfaiosp['sfaiosp_topic'])
		{
			$topicslug = get_query_var('sf_topic');
			if ($topicslug) $topictitle = sf_get_topic_name($topicslug);
		}

		if ($sfaiosp['sfaiosp_forum'])
		{
			$forumslug = get_query_var('sf_forum');
			if ($forumslug && $forumslug != 'all') $forumtitle = sf_get_forum_name($forumslug);
		}

		$spftitle = '';
		$sep = $sfaiosp['sfaiosp_sep'];
		if ($topictitle) $spftitle .= $topictitle.' '.$sfaiosp['sfaiosp_sep'].' ';
		if ($forumtitle) $spftitle .= $forumtitle.' '.$sfaiosp['sfaiosp_sep'].' ';
		$title = $spftitle.$title;
	}

	return $title;
}

# ------------------------------------------------------------------
# sf_blog_link_form()
#
# Filter call
# Sets up the forum post linking form in the Post Write screen
# This version for WP systems 2.5 and above
# ------------------------------------------------------------------
function sf_blog_link_form()
{
	global $current_user;

	if(function_exists('add_meta_box'))
	{
		sf_initialise_globals();

		# can the user do this?
		if (!$current_user->sflinkuse) return;

		add_meta_box('sfforumlink', __("Link To Forum", "sforum"), 'sf_populate_post_form', 'post', 'advanced');
		add_meta_box('sfforumlink', __("Link To Forum", "sforum"), 'sf_populate_post_form', 'page', 'advanced');
	}
	return;
}

# ------------------------------------------------------------------
# sf_populate_post_form()
#
# Callback routine for post linking form on WP2.5 and above
# ------------------------------------------------------------------
function sf_populate_post_form()
{
	global $post;

	$forumid = 0;
	$text = '';

	if(isset($post->ID))
	{
		require_once('forum/sf-links.php');

		$islink = sf_blog_links_postmeta('read', $post->ID, '');
		if($islink)
		{
			$islink=explode('@', $islink->meta_value);
			$forumid = $islink[0];
			$text = 'checked="checked"';
		}
	}
	# WP 2.5+ Post Page
	?>
	<label for="sflink" class="selectit">
	<input type="checkbox" <?php echo($text); ?> name="sflink" id="sflink" />
	<?php _e("Create Forum Topic", "sforum"); ?></label><br /><br />
	<label for="sfforum" class="selectit"><?php _e("Select Forum:", "sforum"); ?><br />
	<?php
	echo(sf_blog_links_list($forumid)).'</label>';
	return;
}

# ------------------------------------------------------------------
# sf_save_blog_link()
#
# Filter call
# Called on  a Post Save to create the blog/forum Link
#	$postid		id of the blog post to link to
# ------------------------------------------------------------------
function sf_save_blog_link($postid)
{
	if(isset($_POST['sflink']))
	{
		require_once('forum/sf-links.php');

		# sadly need to check if both items already set (forum @ topic)
		$checkrow = sf_blog_links_postmeta('read', $postid, '');
		if(($checkrow) && (strpos($checkrow->meta_value, '@')))
		{
			# already cooked
			return;
		} else {
			$text = $_POST['sfforum'];
			sf_blog_links_postmeta('save', $postid, $text);
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_publish_blog_link()
#
# Filter call
# Called on  a Post Publish to create the blog/forum Link
#	$postid		id of the blog post to link to
# ------------------------------------------------------------------
function sf_publish_blog_link($postid)
{
	global $wpdb;

include_once(SF_PLUGIN_DIR.'/sf-slugs.php');

	if(isset($_POST['sflink']))
	{
		require_once('forum/sf-links.php');

		# sadly need to check if both items already set (forum @ topic)
		$checkrow = sf_blog_links_postmeta('read', $postid, '');
		if(($checkrow) && (strpos($checkrow->meta_value, '@')))
		{
			# already cooked
			return;
		} else {
			# first - get the post content
			$content = $wpdb->get_row("SELECT post_content, post_title, post_author, post_status FROM ".$wpdb->prefix."posts WHERE ID = ".$postid.";");
			$post_title = apply_filters('sf_save_topic_title', $content->post_title);
			$post_title = $wpdb->escape($post_title);
			$slug = sf_create_slug($post_title, 'topic');

			# now create the topic and post records - it should already be escaped fully.
			$sql = "INSERT INTO ".SFTOPICS." (topic_name, topic_slug, topic_date, forum_id, user_id, post_count, blog_post_id, post_id) VALUES ('".$post_title."', '".$slug."', now(), ".$_POST['sfforum'].", ".$content->post_author.", 1, ".$postid.", ".$postid.");";
			$wpdb->query($sql);

			$topicid = $wpdb->insert_id;

			# check the topic slug and if empty use the topic id
			if(empty($slug))
			{
				$slug = 'topic-'.$topicid;
				$thistopic = $wpdb->query("
					UPDATE ".SFTOPICS."
					SET topic_slug='".$slug."', topic_name='".$slug."'
					WHERE topic_id=".$topicid);
			}

			# Full content or excerpt?
			$postcontent = sf_make_excerpt($postid, $content->post_content);
			$postcontent = $wpdb->escape($postcontent);

			$sql = "INSERT INTO ".SFPOSTS." (post_content, post_date, topic_id, user_id, forum_id) VALUES ('".$postcontent."', now(), ".$topicid.", ".$content->post_author.", ".$_POST['sfforum'].");";
			$wpdb->query($sql);

			# and then update postmeta with forum AND topic
			$text = $_POST['sfforum']."@".$topicid;
			sf_blog_links_postmeta('save', $postid, $text);

			# Update authors forum post count
			$postcount = (sf_get_member_item($content->post_author, 'posts')+1);
			sf_update_member_item($content->post_author, 'posts', $postcount);

			# Update forum, topic and post index data
			sf_build_forum_index($_POST['sfforum']);
			sf_build_post_index($topicid, $slug);
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_blog_show_link()
#
# Filter call
# Adds the user-defined link text to a blog post
#	$content	The content of the target post
# ------------------------------------------------------------------
if(!function_exists('sf_blog_show_link')):
function sf_blog_show_link($content)
{
	global $wp_query;

	require_once('forum/sf-links.php');
	require_once('forum/sf-database.php');
	require_once('forum/sf-permalinks.php');

	$postid = $wp_query->post->ID;
	$out = '';
	$checkrow = sf_blog_links_postmeta('read', $postid, '');

	if($checkrow)
	{
		# link found for this post
		$keys = explode('@', $checkrow->meta_value);

		$text = stripslashes(get_option('sflinkblogtext'));
		$icon = '<img src="'.SFRESOURCES.'bloglink.png" alt="" />';
		$text = str_replace('%ICON%', $icon, $text);

		$postcount = sf_get_posts_count_in_topic($keys[1]);
		if(!$postcount)
		{
			# break the link
			sf_blog_links_postmeta('delete', $postid, '');
			return $content;
		}
		$counttext = ' - ('.$postcount.') '.__("Posts", "sforum");

		$out = '<span class="sfforumlink"><a href="'.sf_build_url(sf_get_forum_slug($keys[0]), sf_get_topic_slug($keys[1]), 1, 0).'">'.$text.'</a>'.$counttext.'</span>';

		if(get_option('sflinkabove'))
		{
			return $out.$content;
		} else {
			return $content.$out;
		}
	} else {
		return $content;
	}
}
endif;

# ------------------------------------------------------------------
# sf_blog_link_delete()
#
# Action call
# Removes forum link if blog post is deleted
#	$postid		ID of the post being deleted
# ------------------------------------------------------------------
function sf_blog_link_delete($postid)
{
	require_once('forum/sf-links.php');

	$islink = sf_blog_links_postmeta('read', $postid, '');
	if($islink)
	{
		$keys = explode('@', $islink->meta_value);
		# Check - this might be a Revision record
		if($keys[1])
		{
			sf_break_post_link($keys[1], $postid);
		}
	}
	return;
}

# ------------------------------------------------------------------
# sf_blog_links_list()
#
# Support Routine
# Lists forums for the post write link box
#	$forumid		ID of the forum if already linked (Edit mode)
# ------------------------------------------------------------------
function sf_blog_links_list($forumid)
{
	$groups = sf_get_combined_groups_and_forums_bloglink();
	if($groups)
	{
		$out = '';
		$out.= '<select id="sfforum" name="sfforum">'."\n";

		foreach($groups as $group)
		{
			$out.= '<optgroup label="'.$group['group_name'].'">'."\n";
			if($group['forums'])
			{
				foreach($group['forums'] as $forum)
				{
					if($forumid == $forum['forum_id'])
					{
						$text = 'selected="selected" ';
					} else {
						$text = '';
					}
					$out.='<option '.$text.'value="'.$forum['forum_id'].'">&nbsp;&nbsp;&nbsp;&nbsp;'.stripslashes($forum['forum_name']).'</option>'."\n";
				}
			}
			$out.='</optgroup>';
		}
		$out.='</select>'."\n";
	}
	return $out;
}

# ------------------------------------------------------------------
# sf_topic_as_comments()
#
# NOT DIRECTLY CALLABLE
# Adds the topic posts to the comments stream
#	$comments	Passed in by the comments_array filter
# ------------------------------------------------------------------
function sf_topic_as_comments($comments)
{
	global $wp_query;

	sf_load_includes();
	sf_initialise_globals();

	if($comments)
	{
		$postid = $comments[0]->comment_post_ID;
	} else {
		$postid = $wp_query->post->ID;
	}
	$link = sf_blog_links_postmeta('read', $postid, '');
	if(!$link) return $comments;

	$link = explode('@', $link->meta_value);

	# quick permission check
 	if(!sf_can_view_forum($link[0])) return $comments;

	$topicid = $link[1];
	$thread = sf_get_thread_for_comments($topicid);
	if($thread)
	{
		$index = count($comments);
		foreach($thread as $post)
		{
			$comments[$index]->comment_ID = 0;
			$comments[$index]->comment_post_ID = $postid;

			if($post['user_id'] == "")
			{
				$comments[$index]->comment_author = stripslashes($post['guest_name']);
				$comments[$index]->comment_author_email = stripslashes($post['guest_email']);
				$comments[$index]->comment_author_url = "";
			} else {
				$comments[$index]->comment_author = stripslashes($post['display_name']);
				$comments[$index]->comment_author_email = stripslashes($post['user_email']);
				$comments[$index]->comment_author_url = sf_check_url($post['user_url']);
			}
			$comments[$index]->comment_author_IP = "";
			$comments[$index]->comment_date = $post['post_date'];
			$comments[$index]->comment_date_gmt = $post['post_date'];
			$comments[$index]->comment_content = sf_filter_content(stripslashes($post['post_content']), "");
			$comments[$index]->comment_karma = 0;
			$comments[$index]->comment_approved = 1;
			$comments[$index]->comment_agent = "";
			$comments[$index]->comment_type = "";
			$comments[$index]->comment_parent = 0;
			$comments[$index]->user_id = $post['user_id'];
			$comments[$index]->comment_subscribe = "N";
			$comments[$index]->comment_reply_ID = 0;

			$index++;
		}
	}
	return $comments;
}

function sf_get_thread_for_comments($topicid)
{
	global $wpdb;

	$records = $wpdb->get_results(
			"SELECT ".SFPOSTS.".post_id, post_content, ".sf_zone_datetime('post_date').", ".SFPOSTS.".user_id, guest_name, guest_email, post_status,
			".SFMEMBERS.".display_name, user_url, user_email
			 FROM ".SFPOSTS."
			 LEFT JOIN ".SFUSERS." ON ".SFPOSTS.".user_id = ".SFUSERS.".ID
			 LEFT JOIN ".SFMEMBERS." ON ".SFPOSTS.".user_id = ".SFMEMBERS.".user_id
			 WHERE topic_id = ".$topicid." AND post_status = 0 AND post_index > 1
			 ORDER BY post_id DESC", ARRAY_A);

	return $records;
}

?>