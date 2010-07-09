<?php
/*
Simple:Press Forum
List Rendering Routines
$LastChangedDate: 2009-01-24 01:24:11 +0000 (Sat, 24 Jan 2009) $
$Rev: 1286 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# ------------------------------------------------------------------
# sf_render_list()
#
# Main rendering routine for list views
# ------------------------------------------------------------------
function sf_render_list()
{
	# Maybe a membership list
	if (sf_syscheckstr($_GET['list']) == 'members') return sf_list_membership();

	return;
}

# ------------------------------------------------------------------
# sf_list_membership()
#
# Display forum membership list for the current user
# ------------------------------------------------------------------
function sf_list_membership()
{
	global $sfvars, $current_user;

	# to display member lists must be an admin or a member with display membership lists turned on
	if ($current_user->member && ($current_user->forumadmin || get_option('sfshowmemberlist')))
	{
		if (isset($_GET['page'])) $sfvars['page'] = sf_syscheckint($_GET['page']);
		$out = '';

		# filtering?
		$search = '';
		if (isset($_POST['msearch'])) $search = sf_syscheckstr($_POST['msearch']);
		if (isset($_GET['search'])) $search = sf_syscheckstr($_GET['search']);

		# get the member list data
		$data = sf_get_memberlists($sfvars['page'], $search);
		$memberships = $data->records;
		$count = $data->count;

		# Filtering function
		$out.= '<br />';
		$out.= '<div id="sfpostform" style="display:block">';
		$out.= '<table><tr><td>';
		$out.= '<form action="'.SFMEMBERLIST.'" method="post" name="searchmembers">';
		$out.= '<fieldset style="165px;"><legend>'.__("Search Members List by Name", "sforum").'</legend><br />';
		$out.= '<label for="msearch">'.__("Search String", "sforum").':&nbsp;</label>';
		$out.= '<input type="text" class="sfcontrol sfpostcontrol" tabindex="1" name="msearch" id="msearch" size="30" value="'.$search.'" />';
		$out .= '<input type="submit" class="sfcontrol" name="membersearch" id="membersearch" value="'.__("Search Members", "sforum").'" />';
		$out.= '<a href="'.SFMEMBERLIST.'"><input type="button" class="sfcontrol" name="allmembers" id="allmembers" tabindex="2" value="'.__("All Members", "sforum").'" /></a>';
		$out.= '<br /><br /><strong>'.__('Wilcard Usage', 'sforum').':</strong><br />%&nbsp;&nbsp;&nbsp;&nbsp;'.__('matches any number of characters', 'sforum').'<br />&nbsp;_&nbsp;&nbsp;&nbsp;&nbsp;'.__('matches exactly one character', 'sforum');
		$out.= '</fieldset></form></td></tr></table></div>';

		$icon = SFRESOURCES.'members-list.png';
		if ($memberships)
		{
			# set up paging
			$out.= '<br />';
			$thispagelinks = sf_compile_paged_topics('list', 0, $sfvars['page'], false, $search, $count, false, true);

			# Display page links
			$out.= sf_render_topic_pagelinks($thispagelinks, false, false, false, false);

			# var to track user group change
			$oldug = 0;
			foreach ($memberships as $membership)
			{
				if ($membership['usergroup_id'] != $oldug)
				{
					if ($oldug != 0)
					{
						# except for first new ug, close the previous table and sfblock div
						$out.= '</table>';
						$out.= '</div>';
					}

					# save new ug id
					$oldug = $membership['usergroup_id'];

					# Any members in this user group?
					$emptyug = false;
					if (sf_get_membership_count($membership['usergroup_id']) == 0) $emptyug = true;

					# put out header table
					$out.= '<div class="sfblock">';
					$out.= sf_render_main_header_table('list', $membership['usergroup_id'], $membership['usergroup_name'], $membership['usergroup_desc'], '', $icon);
					$out.= '<table class="sfforumtable">'."\n";
					$out.= '<tr>';
					$out.= '<th>'.__("Member", "sforum").'</th>';
					$out.= '<th width="75">'.__("Posts", "sforum").'</th>';
					$out.= '<th width="200">'.__("Last Visit", "sforum").'</th>';
					$out.= '<th width="100">'.__("Rank", "sforum").'</th>';
					$out.= '<th width="100">'.__("Info", "sforum").'</th>';
					$out.= '</tr>';
				}

				# If no members in this user group, output such a message
				if ($emptyug)
				{
					$out.= '<tr>';
					$out.= '<td colspan="5">';
					$out.= __('There are no Members in this User Group.', 'sform');
					$out.= '</td>';
					$out.= '</tr>';
				} else { # user group has members
 					$status = 'user';
					if (sf_is_forum_admin($membership['user_id'])) $status = 'admin';
					$membership['rank'] = sf_render_usertype($status, $membership['user_id'], $membership['posts']);
					$out.= '<tr>';
					$out.= '<td>'.stripslashes($membership['display_name']).'</td>';
					$out.= '<td align="center">'.$membership['posts'].'</td>';
					$out.= '<td align="center">'.$membership['lastvisit'].'</td>';
					$out.= '<td align="center">'.$membership['rank'].'</td>';
					$out.= '<td align="center">';
					$param['forum'] = 'all';
					$param['value'] = urlencode('sf%members%2%user'.$membership['user_id']);
					$param['search'] = 1;
					$url = add_query_arg($param, SFURL);
					$url = sf_filter_wp_ampersand($url);
					$out.= '<a href="'.$url.'"><img src="'.SFRESOURCES.'topics-started.png" title="'.__("List Topics User Started", "sforum").'" alt="" /></a>';
					$param['value'] = urlencode('sf%members%1%user'.$membership['user_id']);
					$url = add_query_arg($param, SFURL);
					$url = sf_filter_wp_ampersand($url);
					$out.= '&nbsp;<a href="'.$url.'"><img src="'.SFRESOURCES.'topics-posted-in.png" title="'.__("List Topics User Has Posted In", "sforum").'" alt="" /></a>';
					$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahprofile.php?action=memberlist&amp;u=".$membership['user_id']."&ug=".$membership['usergroup_id'];
					$gif = '';
					$out.= '&nbsp;<img onclick="sfjShowProfile(\''.$site.'\', \''.$gif.'\', \'memberprofile-'.$membership['usergroup_id'].'-'.$membership['user_id'].'\');" src="'.SFRESOURCES.'user.png" title="'.__("View Member Profile", "sforum").'" alt="" />';
					$out.= '</td>';
					$out.= '</tr>';
					$out.= '<tr>';
					$out.= '<td colspan="5" style="padding:0">';
					$out.= '<div id="memberprofile-'.$membership['usergroup_id'].'-'.$membership['user_id'].'" class="inline_edit sfnewpostforum">';
					$out.= '</div>';
					$out.= '</td>';
					$out.= '</tr>';
				}
			}
			# close last table and sfblock div
			$out.= '</table>';
			$out.= '</div>';

			# Display page links
			$out.= sf_render_topic_pagelinks($thispagelinks, false, false, false, false);
		} else {
			$out.= '<div class="sfmessagestrip">'.__("There are no members that matched the criteria!", "sforum").'</div>';
		}
	} else {
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
	}

	return $out;
}

?>