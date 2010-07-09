<?php
/*
Simple:Press Forum
Topic View Display
$LastChangedDate: 2009-03-05 11:57:47 +0000 (Thu, 05 Mar 2009) $
$Rev: 1520 $
*/

function sf_render_topic($paramvalue)
{
	global $sfvars, $current_user, $sfglobals, $postNumberOnPage;

	if($sfvars['page'] == 1)
	{
		$postNumberOnPage=0;
	} else {
		$postNumberOnPage=(($sfvars['page']-1) * get_option('sfpagedposts'));
	}

	if(!sf_topic_exists($sfvars['topicid']))
	{
		$sfvars['error'] = true;
		update_sfnotice('sfmessage', '1@'.sprintf(__('Topic %s Not Found', 'sforum'), $sfvars['topicslug']));
		$out = sf_render_queued_message()."\n";
		$out.= '<div class="sfmessagestrip">'."\n";
		$out.= sprintf(__("There is no such topic named %s", "sforum"), $sfvars['topicslug'])."\n";
		$out.= '</div>'."\n";
		return $out;
	}

	if($current_user->sfaccess)
	{
		# Get Topic Record
		$topic=sf_get_combined_topics_and_posts($sfvars['topicid']);
		if($topic)
		{
			# Setup stuff we will need
			$editstrip = false;
			$admintools = false;
			$userposts = 0;

			# == IS FORUM LOCKED OR CAN WE ADD
			if($current_user->sfreply) $showadd = true;
			if($sfglobals['lockdown']) $showadd = false;
			if($current_user->forumadmin) $showadd = true;

			if(($sfglobals['admin']['sftools']) && ($current_user->sftopicicons)) $admintools=true;

			$lastpost = false;

			# Setup more stuff we will need
			$topiclock = false;
			if($topic['topic_status'] || $topic['forum_status']) $topiclock = true;

			# Does this have a link to blog post
			$bloglink = $topic['blog_post_id'];

			# Setup page links for this topic
			$thispagelinks = sf_compile_paged_posts($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['topicid'], $sfvars['page'], $topic['post_count']);

			# Start display
			$out = '';
			$out.= '<div class="sfblock">'."\n";
			$out.= sf_render_main_header_table('topic', 0, $topic['topic_name'], '', '', SFRESOURCES.'topic.png', false, false, $showadd, $topiclock, $topic['blog_post_id'], '', false, 0, $topic['topic_status_set'], $topic['topic_status_flag']);

			# Display top page link navigation
			$out.= sf_render_post_pagelinks($thispagelinks, false, $topiclock, $topic['topic_subs'], $topic['topic_page'], $topic['topic_total_pages']);

			if($topic['posts'])
			{
				$numposts=count($topic['posts']);
				$thispost=1;
				$alt = '';

				# Start the Outer Table
				$out.= '<table class="sfposttable">'."\n";

				# Display post column headers
				$out.= sf_render_topic_column_header_row($admintools);

				foreach($topic['posts'] as $post)
				{
					$editmode = false;
					# Are we in 'edit' mode with this post?
					if(((isset($_POST['useredit'])) && ($_POST['useredit'] == $post['post_id'])) || ((isset($_POST['adminedit'])) && ($_POST['adminedit'] == $post['post_id']))) $editmode=true;

					# Setup even more stuff we will need
					$currentguest = false;
					$currentmember = false;
					$posterstatus = 'user';
					$postcount = '';
					$sig = false;
					$sigimg = '';
					$displaypost = true;
					$approve_text = '';
					if(!$editmode) $postboxclass= 'class="sfpostcontent '.$alt.'"';

					# is this the last post (which can have the edit button)
					if($thispost == $numposts) $lastpost = true;

					# Status of the poster of this post (first check Amdin)
					if (sf_is_forum_admin($post['user_id'])) $posterstatus = 'admin';

					# Or was it posted by the user currently loading the page?
					if(($current_user->member) && ($current_user->ID == $post['user_id'])) $currentmember=true;

					# Prepare Posters name and URL if exists
					$poster = stripslashes($post['display_name']);
					if(sf_check_url($post['user_url']) != '') $poster = '<a href="'.sf_check_url($post['user_url']).'">'.sf_filter_user($post['user_id'], stripslashes($post['display_name'])).'</a>'."\n";
					$username = sf_filter_user($post['user_id'], stripslashes($post['display_name']));

					if(empty($poster))
					{
						# Must be a guest
						$poster = apply_filters('sf_show_post_name', stripslashes($post['guest_name']));
						$posterstatus = 'guest';

						# Was it the guest currently loading the page?
						if((stripslashes($current_user->guestname == $poster)) && (stripslashes($current_user->guestemail == stripslashes($post['guest_email'])))) $currentguest = true;
						$username = $poster;
					}

					# Get post count if poster was a member
					if(!empty($post['user_id']))
					{
						$userposts = sf_get_member_item($post['user_id'], 'posts');
						$postcount = __("posts ", "sforum").$userposts;
					}

					# Setup Signature line if exists
					if($posterstatus != 'guest')
					{
						$sig = stripslashes(html_entity_decode(sf_get_member_item($post['user_id'], 'signature'), ENT_QUOTES));
						$sigimg = stripslashes(html_entity_decode(sf_get_member_item($post['user_id'], 'sigimage'), ENT_QUOTES));
					}

					# Determine approval status of post - is it still awaiting aproval?
					if($post['post_status'] == 1)
					{
						if(($current_user->adminstatus || $current_user->sfapprove) && (!$admintools))
						{
							$approve_text = '<span class="sfalignright">'.sf_render_post_editicons($post['post_id'], $post['post_index'], $post['post_status'], $post['user_email'], $post['guest_email'], $post['post_pinned'], true).'</span>'."\n";
						}
						$approve_text.= '<p><em>'.__("Post Awaiting Approval by Forum Administrator", "sforum").'</em></p>';
						$postboxclass= 'class="sfpostcontent sfmoderate"';
						if($current_user->adminstatus == false || $current_user->sfapprove == false)
						{
							$displaypost = false;
						}
					}

					# Outer table - display post row (in sections)
					$out.= '<tr valign="top">'."\n";

					# Poster Details Cell
					if(get_option('sfuserabove'))
					{
						# Open outer cell (above)
						$out.= '<td class="sfuserinfoabove '.$alt.'">'."\n";
						$out.= sf_render_poster_details_above($post, $posterstatus, $poster, $userposts, $postcount, $admintools, $alt);
							$out.= '<td class="sfuserinfoabove '.$alt.'"></td>'."\n";
					} else {
						# Open outer cell (side)
						$out.= '<td class="sfuserinfoside '.$alt.'">'."\n";
						$out.= sf_render_poster_details_side($post, $posterstatus, $poster, $userposts, $postcount, $admintools, $alt);
					}

					# Close outer cell
					$out.= '</td>'."\n";

					# Close poster detail row and prepare next (post content) if single column mode
					if(get_option('sfuserabove')) $out.= '</tr><tr valign="top">'."\n";

					# Open outer cell
					$out.= '<td class="'.$alt.'">'."\n";
					# Start Inner post cell table
					$out.= '<table class="sfinnerposttable">'."\n";

					$postNumberOnPage++;

					# As we only want the bloglink on the first post reset to zero
					if($postNumberOnPage > 1) $bloglink = 0;

					# Display post icon strip if not in edit mode
					if(!$editmode)
					{
						$out.='<tr>'."\n";
						$out.= sf_render_post_icon_strip($post, $posterstatus, $current_user->ID, $username, $currentguest, $currentmember, $displaypost, $topiclock, $lastpost, $alt, $admintools);
						# Close the inner iconstrip row
						$out.= '</tr>'."\n";
						$editstrip=true;
					}

					# open postcontent row, inner table cell
					$out.= '<tr>'."\n";

					# Display Post Content (Edit/Normal/Moderation modes)
					$out.= '<td '.$postboxclass.'>'."\n";

					if ($admintools)
					{
						# Inner admin tools table
						$out.= '<table class="sfinnertoolstable"><tr>'."\n";
						$out.= '<td width="1px" class="sfmanageicons '.$alt.'">'."\n";
						$out.= sf_render_post_editicons($post['post_id'], $post['post_index'], $post['post_status'], $post['user_email'], $post['guest_email'], $post['post_pinned']);
						$out.='</td><td '.$postboxclass.'>'."\n";
					}

					# Pre Post-content hook
					$hook = '';
					if(function_exists('sf_hook_pre_post'))
					{
						$hook = sf_hook_pre_post($sfvars['topicid'], $post['post_id']);
					}
					if($hook)
					{
						$out.= $hook;
						$hook='';
					}

					$out.= sf_render_post_content($post, $editmode, $displaypost, $paramvalue, $approve_text, $currentguest, $currentmember, $bloglink);

					if ($admintools)
					{
						# Close inner admin tools table
						$out.= '</td></tr></table>'."\n";
					}
					$out.= '</td>'."\n";

					# Close the inner post content row
					$out.= '</tr>'."\n";

					# Display Signature of set
					if(($sig || $sigimg) && (!$editmode))
					{
						$out.= '<tr><td class="sfsignature '.$alt.'">'."\n";
						$out.= sf_render_signature_strip($sig, $sigimg);
						$out.= '</td>'."\n";
						$out.= '</tr>'."\n";
					}

					# Feedburner 'Flare' Hook/first and last post hooks
					$permalink = sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], $post['post_id'], $post['post_index']);
					$hook = '';
					if(function_exists('sf_hook_post_feedflare'))
					{
						$hook = sf_hook_post_feedflare($permalink);
					}
					if(($postNumberOnPage == 1) && (function_exists('sf_hook_first_post')))
					{
						$hook.= sf_hook_first_post($sfvars['forumid'], $sfvars['topicid']);
					}
					if(($lastpost == true)  && (function_exists('sf_hook_last_post')))
					{
						$hook.= sf_hook_last_post($sfvars['forumid'], $sfvars['topicid']);
					}
					if($postNumberOnPage != 1 && $lastpost == false)
					{
						if(function_exists('sf_hook_other_posts'))
						{
							$hook.= sf_hook_other_posts($sfvars['forumid'], $sfvars['topicid']);
						}
					}

					if($hook)
					{
						$out.= '<tr><td class="'.$alt.'">'.$hook.'</td></tr>'."\n";
						$hook='';
					}

					# Post post-content hook
					$hook = '';
					if(function_exists('sf_hook_post_post'))
					{
						$hook = sf_hook_post_post($sfvars['topicid'], $post['post_id']);
					}
					if($hook)
					{
						$out.= '<tr><td class="'.$alt.'">'.$hook.'</td></tr>'."\n";
						$hook='';
					}

					# End Inner post cell table
					$out.= '</table>'."\n";
					# End Outer post cell middle
					$out.= '</td>'."\n";

					# Close outer right cell
					$out.= '</tr>'."\n";

					$thispost++;
					if ($alt == '') $alt = 'sfalt'; else $alt = '';
				}
				# Close outer table
				$out.= '</table>'."\n";
$out.= '<div class="sfdivider"></div>';

				# topic status updater
				if(!empty($topic['topic_status_set']))
				{
					$out.= sf_render_topic_status_updater($topic['topic_status_set'], $topic['topic_status_flag']);
				}

				# Display bottom page link navigation
				$out.= sf_render_post_pagelinks($thispagelinks, true, $topiclock, '', $topic['topic_page'], $topic['topic_total_pages']);

			} else {
				$out.= '<div class="sfmessagestrip">'.__("There are No Posts for this Topic", "sforum").'</div>'."\n";
			}
			$out.= '</div>'."\n";
		}
		# Display Add Post form (hidden)
		if((!$topiclock) || ($current_user->adminstatus))
		{
			if($current_user->sfreply)
			{
				$out.= '<a id="dataform"></a>'."\n";
				$out.= sf_add_post($sfvars['forumid'], $sfvars['topicid'], $topic['topic_name'], $topic['topic_status_set'], $topic['topic_status_flag'], $current_user->ID, $topic['topic_subs']);
			}
		}
	} else {
//		update_sfnotice('sfmessage', '1@'.__("Access Denied", "sforum"))."\n";
//		$out = sf_render_queued_message()."\n";
		$out = 'Access Denied';
	}
	return $out;
}

?>