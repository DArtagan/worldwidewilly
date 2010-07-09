<?php
/*
Simple:Press Forum
Forum View Display
$LastChangedDate: 2009-02-20 12:57:33 +0000 (Fri, 20 Feb 2009) $
$Rev: 1429 $
*/

function sf_render_forum($paramtype, $paramvalue)
{
	global $sfvars, $current_user, $sfglobals;

	$forumid = $sfvars['forumid'];
	
	if(!sf_forum_exists($forumid))
	{
		$sfvars['error'] = true;
		update_sfnotice('sfmessage', '1@'.sprintf(__('Forum %s Not Found', 'sforum'), $sfvars['forumslug']));
		$out = sf_render_queued_message();
		$out.= '<div class="sfmessagestrip">'."\n";
		$out.= sprintf(__("There is no such forum named %s", "sforum"), $sfvars['forumslug'])."\n";
		$out.= '</div>'."\n";
		return $out;
	}

	# Setup stuff we will need for the query
	if(empty($sfvars['page'])) ($sfvars['page'] = 1);
	if(empty($paramvalue) ? $search = false : $search = true);
	if(isset($_GET['search']) ? $searchpage = sf_syscheckint($_GET['search']) : $searchpage = '');
	$showadd = false;
	
	# grab the records for this page
	$forums = sf_get_combined_forums_and_topics($sfvars['forumid'], $sfvars['page'], $paramvalue, $searchpage);

	# If No Access to anything then return access denied flag
	if($forums[0]['forum_id'] == "Access Denied") return 'Access Denied';
	
	if($forums)
	{
		foreach($forums as $forum)
		{
			# setup more vars settings later
			if($forum['forum_status'] ?  $forumlock=true : $forumlock=false);
			if(($sfglobals['admin']['sftools'] && $current_user->sfforumicons) ? $admintools=true : $admintools=false);

			# == IS FORUM LOCKED OR CAN WE ADD
			if($current_user->sfaddnew) $showadd = true;
			if($forumlock) $showadd = false;
			if($sfglobals['lockdown']) $showadd = false;
			if($current_user->forumadmin) $showadd = true;
	
			# Setup more stuff we will need
			$coldisplaytext = sf_render_search_heading($search);

			# columns to display
			$cols=get_option('sftopiccols');

			# Setup page links for this topic
			$thispagelinks = sf_compile_paged_topics($sfvars['forumslug'], $sfvars['forumid'], $sfvars['page'], $search, $searchpage, $forum['topic_count']);

			# Setup forum icon
			if(!empty($forum['forum_icon']))
			{
				$icon = SFRESOURCES.'custom/'.$forum['forum_icon'];
			} else {
				$icon = SFRESOURCES.'forum.png';
			}

			# Start display
			$out = '';	
			# Display forum header
			$out.= '<div class="sfblock">'."\n";
			$out.= sf_render_main_header_table('forum', '', $forum['forum_name'], $forum['forum_desc'], $paramvalue, $icon, $forumlock, $search, $showadd);

			if($forum['topics'])
			{
				# Display top page link navigation
				$out.= sf_render_topic_pagelinks($thispagelinks, false, $showadd, $forumlock);
				$out.= '<table class="sfforumtable">'."\n";
			
				# Display topic column headers
				$out.= sf_render_forum_column_header_row($cols, $coldisplaytext, $admintools);
			
				$alt = '';
				foreach($forum['topics'] as $topic)
				{			
					# get the topic stats (first/last post etc)
					$stats = sf_get_combined_topic_stats($topic['topic_id'], $topic['post_id'], $topic['post_count'], $cols);
					$value['search']=$search;
					$value['searchpage']=$searchpage;
					$value['paramvalue']=$paramvalue;
					$value['paramtype']=$paramtype;
					$value['forumlock']=$forumlock;
					$value['admintools']=$admintools;
				
					# Display current topic row
					$out.= sf_render_topic_entry_row($forum, $topic, $cols, $stats, $value, $alt);
					if ($alt == '') $alt = 'sfalt'; else $alt = '';
				}
				$out.= '</table>'."\n";

				# Display bottom page link navigation
				$out.= sf_render_topic_pagelinks($thispagelinks, true, $showadd, $forumlock);

				# Store the topic page so that we can get back to it later
				sf_push_topic_page($sfvars['forumid'], $sfvars['page']);

			} else {
				if($search)
				{
					$out.= '<div class="sfmessagestrip">'.__("The Search found No Results", "sforum").'</div>'."\n";
					delete_sfsetting($paramvalue);
				} else {
					$out.= '<div class="sfmessagestrip">'.__("There are No Topics defined in this Forum", "sforum").'</div>'."\n";
				}
			}
		}
	}
	$out.= '</div>'."\n";

	# Display new (hidden) topic form
	if($showadd)
	{
		$out.= '<a id="dataform"></a>'."\n";
		$out.= sf_add_topic($sfvars['forumid'], $forum['forum_name'], $forum['topic_status_set'], $current_user->ID);
		
		if(isset($_GET['new']) && sf_syscheckstr($_GET['new']) == 'topic')
		{
			$out.= '<script type="text/javascript">'."\n";
			$out.= 'sfjtoggleLayer("sfpostform");'."\n";
			$out.= '</script>'."\n"."\n";
		}		
	}
	return $out;
}

?>