<?php
/*
Simple:Press Forum
Topic View Display
$LastChangedDate: 2009-03-05 12:58:31 +0000 (Thu, 05 Mar 2009) $
$Rev: 1522 $
*/

function sf_render_new_post_list_admin($newposts, $top)
{
	global $sfvars, $current_user, $sfglobals;

	$out = '';
	$alt = '';
	$nourl = '';
	$load = __("Loading Topic", "sforum");

	if($newposts)
	{
		$index=array();
		foreach($newposts as $newpost)
		{
			$forumid=$newpost['forum_id'];
			$index[$forumid]=count($newpost['topics']);
		}

		# Display section heading
		$out.= '<div class="sfmessagestrip">'."\n";
		$out.= '<a id="newpoststop"></a>'."\n";

		if(($current_user->forumadmin) || ($current_user->moderator && $sfglobals['admin']['sfmodasadmin']))
		{
			$out.= '<form class="sfhiddenform sfsubhead" action="'.SFURL.'" method="post" name="removequeue">'."\n";
			$out.= '<input type="hidden" class="sfhiddeninput" name="doqueue" value="1" />'."\n";
			$out.= '<a class="sficon" href="javascript:document.removequeue.submit();"><img src="'.SFRESOURCES.'delete.png" alt="" title="'.__("delete unread post list", "sforum").'" /></a>'."\n";

			$out.= __("New/Unread Posts - Management View", "sforum")."\n";
			$out.= '</form>'."\n";
			$removal = true;
			$canremove = '1';
		} else {
			$out.= __("New/Unread Posts - Management View", "sforum")."\n";
			if($current_user->moderator && $sfglobals['admin']['sfmodasadmin']==false)
			{
				$out.='<br />'. __("While you are a Moderator and may view and approve these posts, the forum Admin reserves the right to remove them from the Admins 'New Post' queue", "sforum")."\n";
				$removal = false;
				$canremove = '0';
			}
		}
		$out.= '</div>'."\n";

		# Start actual listing display
		$out.= '<div class="inline_edit" id="sfmsgspot"></div>';

		$out.= '<div class="sfblock">'."\n";

		# Display new posts heading
		$out.= '<table class="sfforumtable">'."\n";
		$out.= '<tr><th colspan="2">'.__("Forums and Topics", "sforum").'</th></tr>'."\n";

		# Start with main forum header
		foreach($newposts as $newpost)
		{
			# Display forum name
			$out.= '<tr id="forumrow'.$newpost['forum_id'].'">'."\n";
			$out.= '<td class="sfnewposticoncell" align="center"><img src="'. SFRESOURCES .'forum.png" alt="" /></td>'."\n";
			$out.= '<td class="sfnewpostforum"><p>'.sf_get_forum_url_newposts($newpost['forum_slug'], $newpost['forum_name']).'</p>'."\n";
			$out.= '<input type="hidden" id="tcount'.$newpost['forum_id'].'" value="'.$index[$newpost['forum_id']].'" />'."\n";
			$out.= '</td>'."\n";
			$out.= '</tr>'."\n";

			# Now for each topic with new posts
			foreach($newpost['topics'] as $topic)
			{
				$topicid = $topic['topic_id'];
				$postcount = $topic['post_count'];
				$postcountmod = 0;
				$postcountord = 0;

				# Display topics in forum
				$out.='<tr id="topicrow'.$topic['topic_id'].'">'."\n";
				$out.='<td id="open'.$topicid.'"  align="center">'."\n";
				$out.="<input type='button' name='openicon".$topicid."' class='sfxcontrol' value='".__('Open', 'sforum')."' onclick='sfjtoggleLayer(\"thistopic".$topic['topic_id']."\");' />";

				# a quick first pass to load the post count variables
				foreach($topic['posts'] as $post)
				{
					if($post['post_status'] == 1 ? $postcountmod++ : $postcountord++);
					$lastpost_id = $post['post_id'];
				}

				$out.= '<input type="hidden" id="pcount'.$topic['topic_id'].'" value="'.$postcount.'" />'."\n";

				$out.= '<input type="hidden" id="pcountmod'.$topic['topic_id'].'" value="'.$postcountmod.'" />'."\n";
				$out.= '<input type="hidden" id="pcountord'.$topic['topic_id'].'" value="'.$postcountord.'" />'."\n";


				$out.='</td>'."\n";
				$out.= '<td class="'.$alt.'"><p>'.sf_get_topic_url_newpost($newpost['forum_slug'], $topicid, $lastpost_id, 0)."\n";

				# Set up some values we need
				$thistopic = sf_get_topic_record($topicid, true);
				$topicslug = $thistopic['topic_slug'];

				$statusset = $newpost['topic_status_set'];
				$statusflag= $thistopic['topic_status_flag'];

				$nourel='';
				if($postcount == 1)
				{
					$note = __("There is 1 new post in this topic", "sforum")."\n";
				} else {
					$note = sprintf(__("There are %s new posts in this topic", "sforum"), $postcount)."\n";
				}
				$out.= '<br /><small>'.$note.'</small><br /></p></td>'."\n";

				$out.= '</tr>'."\n";

				# Start display of post information
				$out.='<tr class="sfadminrow" id="modpostrow'.$topicid.'">';
				$out.='<td class="sfadminrow" colspan="3">'."\n";
				$out.='<div id="thistopic'.$topicid.'" class="sfadminslist inline_edit">'."\n";
				$out.= '<table class="sfadmintable" cellspacing="0" border="1">'."\n";
				$out.= '<tr><th width="15%">'.__("Poster", "sforum").'</th><th>'.__("Post Text", "sforum").'</th></tr>'."\n";

				$pindex=0;
				$mod_required = false;

				# Start the post display loop
				foreach($topic['posts'] as $post)
				{
					$pindex++;
					if($pindex == $postcount ? $lastpost=true : $lastpost=false);
					if($post['post_status'] == 1) $mod_required = true;

					$out.='<tr id="thispost'.$post['post_id'].'" valign="top">'."\n";
					$out.='<td><b>'.$post['display_name'].'</b><br /><small>'.$post['user_type'].'</small>';
					if ($post['post_status'] == 1)
					{
						$out.= '<div class="sfadminmod">'.sf_split_label(__("Awaiting Moderation", "sforum"), 0).'</div>';
					}
					$out.= '<br /><small>'.sprintf(__("Post %s in Topic", "sforum"), $post['post_index']).'</small>'."\n";
					$out .='</td>'."\n";
					$out.='<td>'.sf_filter_content(stripslashes($post['post_content']), '').'</td>'."\n";

					$out.='</tr>'."\n";

					# Set up the ahah base url
					$basesite=SF_PLUGIN_URL."/forum/ahah/sf-ahahmoderation.php?pid=".$post['post_id']."&amp;tid=".$topicid."&amp;fid=".$newpost['forum_id'];

					$out.='<tr id="thispostcon'.$post['post_id'].'">';
					$out.='<td colspan="3"">'."\n";

					if($postcount == 1)
					{
						$label = __("This Post", "sforum");
					} else {
						$label = __("All Posts", "sforum");
					}

					if($lastpost)
					{
						$site=$basesite.'&amp;action=0&amp;canremove='.$canremove;

						$p = sf_determine_page($topicslug, $post['post_id'], 0);

						if ($mod_required)
						{
							if(sf_user_can($current_user->ID, 'Can moderate pending posts', $newpost['forum_id']))
							{
								$posturl = sf_build_url($newpost['forum_slug'], $topicslug, $p, 0);
								$posturl = sf_get_sfqurl($posturl);
								$posturl .= 'mod=1/#'.$post['post_id'];
							}
						} else {
//							$posturl = sf_build_url($newpost['forum_slug'], $topicslug, $p, $post['post_id']);
							$posturl = sf_build_url($newpost['forum_slug'], $topicslug, $p, 0);
							$posturl = sf_get_sfqurl($posturl);
							$posturl .= 'mark=1/#'.$post['post_id'];
						}

						if($mod_required)
						{
							$out.= '<input type="button" class="sfxcontrol" name="g0-'.$post['post_id'].'" value="'.sf_split_button_label(sprintf(__("Mark %s Approved and go to Topic", "sforum"), $label), 2).'" onclick="sfjmoderatePost(\''.$load.'\', \''.$posturl.'\', \''.$site.'\', \''.$removal.'\', \''.$post['post_id'].'\', \''.$newpost['forum_id'].'\', \''.$topicid.'\', \''.$post['post_status'].'\', \'9\');" />'."\n";
							$out.= '<input type="button" class="sfxcontrol" name="a0-'.$post['post_id'].'" value="'.sf_split_button_label(sprintf(__("Mark %s Approved and Close", "sforum"), $label), 2).'" onclick="sfjmoderatePost(\''.$load.'\', \''.$nourl.'\', \''.$site.'\', \''.$removal.'\', \''.$post['post_id'].'\', \''.$newpost['forum_id'].'\', \''.$topicid.'\', \''.$post['post_status'].'\', \'0\');" />'."\n";
							$out.= '<input type="button" class="sfxcontrol" name="q0-'.$post['post_id'].'" value="'.sf_split_button_label(sprintf(__("Mark %s Approved and Quick Reply", "sforum"), $label), 2).'" onclick="sfjtoggleLayer(\'sfqform'.$topicid.'\');" />'."\n";
							$qaction=0;

						} else {

							$site=$basesite.'&amp;action=1&amp;canremove='.$canremove;

							$out.= '<input type="button" class="sfxcontrol" name="g1-'.$post['post_id'].'" value="'.sf_split_button_label(sprintf(__("Mark %s as Read and go to Topic", "sforum"), $label), 2).'" onclick="sfjmoderatePost(\''.$load.'\', \''.$posturl.'\', \''.$site.'\', \''.$removal.'\', \''.$post['post_id'].'\', \''.$newpost['forum_id'].'\', \''.$topicid.'\', \''.$post['post_status'].'\', \'1\');" />'."\n";
							$out.= '<input type="button" class="sfxcontrol" name="a1-'.$post['post_id'].'" value="'.sf_split_button_label(sprintf(__("Mark %s as Read and Close", "sforum"), $label), 2).'" onclick="sfjmoderatePost(\''.$load.'\', \''.$nourl.'\', \''.$site.'\', \''.$removal.'\', \''.$post['post_id'].'\', \''.$newpost['forum_id'].'\', \''.$topicid.'\', \''.$post['post_status'].'\', \'1\');" />'."\n";
							$out.= '<input type="button" class="sfxcontrol" name="a1-'.$post['post_id'].'" value="'.sf_split_button_label(sprintf(__("Mark %s as Read and Quick Reply", "sforum"), $label), 2).'" onclick="sfjtoggleLayer(\'sfqform'.$topicid.'\');" />'."\n";
							$qaction=1;
						}
					}

					if($removal)
					{
						$remsite=$basesite.'&amp;action=2&amp;canremove='.$canremove;
						$msg = __("Are you sure you want to delete this Post?", "sforum");

						$out.= '<input type="button" class="sfxcontrol" name="a2-'.$post['post_id'].'" value="'.sf_split_button_label(__("Delete this Post", "sforum"), 0).'" onclick="javascript: if(confirm(\''.$msg.'\')) {sfjmoderatePost(\''.$load.'\', \''.$nourl.'\', \''.$remsite.'\', \''.$removal.'\', \''.$post['post_id'].'\', \''.$newpost['forum_id'].'\', \''.$topicid.'\', \''.$post['post_status'].'\', \'2\');}" />'."\n";
					}

					# Quick Reply Form
					if($lastpost)
					{
						$qsavesite=SF_PLUGIN_URL."/forum/ahah/sf-ahahquickreply.php?tid=".$topicid."&amp;fid=".$newpost['forum_id'];

						$out.='<div id="sfqform'.$topicid.'" class="inline_edit">';
						$out.='<form action="'.SFURL.'" method="post" name="addpost'.$topicid.'" onsubmit="return sfjsaveQuickReply(this, \''.$qsavesite.'\', \''.$site.'\', \''.$post['post_id'].'\', \''.$newpost['forum_id'].'\', \''.$topicid.'\', \''.$post['post_status'].'\', \''.$qaction.'\')">'."\n";

						$out.='<textarea  tabindex="1" class="sfquickreply" name="postitem'.$topicid.'" id="postitem'.$topicid.'" cols="60" rows="8"></textarea>'."\n";

						$out.='<br /><input type="submit" tabindex="2" class="sfxcontrol" id="sfsave'.$topicid.'" name="newpost'.$topicid.'" value="'.__("Save New Post", "sforum").'" />'."\n";

						if($statusset !=0)
						{
							if($statusflag == 0 ? $tsmsg=__("Assign Topic Status", "sforum") : $tsmsg=__("Change Topic Status", "sforum"));
							$out.= '&nbsp;&nbsp;<label><small>'.$tsmsg.':  '.sf_topic_status_select($statusset, $statusflag, false).'</small></label>';
						} else {
							$out.= '<input type="hidden" name="statvalue" id="statvalue" value="0" />';
						}

						$out.='<label for="sfwatchtopic'.$topicid.'"><small>'.__("Watch this Topic", "sforum").'</small>&nbsp;</label>';
						$out.='<input type="checkbox" id="sfwatchtopic'.$topicid.'" name="watchtopic'.$topicid.'" />';
						$out.='</form><br /></div>';
					}

					$out.='</td>'."\n";
					$out.='</tr>'."\n";
				}
				$out.='</table>'."\n";
				$out.='</div>'."\n";
				$out.='</td></tr>'."\n";

			}
		}
		$out.= '</table></div>'."\n";
	} else {
		$out.='<div class="sfmessagestrip">';
		$out.= __("There are No Unread Posts", "sforum").'</div>'."\n";

		$out.= '<div class="inline_edit" id="sfmsgspot"></div>';
	}
	return $out;
}

?>