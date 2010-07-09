<?php
/*
Simple:Press Forum
Topic View Display
$LastChangedDate: 2009-01-04 07:10:09 +0000 (Sun, 04 Jan 2009) $
$Rev: 1126 $
*/

function sf_render_watched_topics()
{
	global $sfvars, $current_user, $sfglobals;

	$out = '';

	# Display Header
	$out.= '<div class="sfblock">'."\n";
	$out.= sf_render_main_header_table('forum', 0, __('Watched Topics', 'sforum'), __('List of topics that you are currently watching.', 'sforum'), '', SFRESOURCES.'watchicon.png');

	# grab the records for this watched topic page
	if (isset($_GET['page']))
		$sfvars['page'] = sf_syscheckint($_GET['page']);

	$watched = sf_get_watched_topics($sfvars['page']);
	$topics = $watched['records'];
	$count = $watched['count'];

	if ($topics)
	{
		if(($sfglobals['admin']['sftools'] && $current_user->sfforumicons) ? $admintools=true : $admintools=false);

		# Fet some stuff we need
		$cols=get_option('sftopiccols');
		$thispagelinks = sf_compile_paged_topics('watchedtopics', 0, $sfvars['page'], false, '', $count, true);

		# Display page links
		$out.= sf_render_topic_pagelinks($thispagelinks, false, false, false, false);

		# Start table display
		$out.= '<table class="sfforumtable">'."\n";
		$out.= sf_render_forum_column_header_row($cols, '', $admintools);
		$alt = '';

		foreach($topics as $topic)
		{
			# Get what we need
			$stats = sf_get_combined_topic_stats($topic['topic_id'], $topic['post_id'], $topic['post_count'], $cols);
			$forum = array();
			$forum['group_id'] = $topic['group_id'];
			$forum['forum_id'] = $topic['forum_id'];
			$forum['forum_slug'] = $topic['forum_slug'];
			$forum['forum_name'] = $topic['forum_name'];
			$forum['topic_status_set'] = $topic['topic_status_set'];

			$value['search'] = '';
			$value['searchpage'] = '';
			$value['paramvalue'] = '';
			$value['paramtype'] = '';
			$value['forumlock'] = '';
			$value['admintools'] = $admintools;
			$value['watches'] = true;

			# Display current topic row
			$sfvars['forumslug'] = $topic['forum_slug'];
			$out.= sf_render_topic_entry_row($forum, $topic, $cols, $stats, $value, $alt);
			if ($alt == '') $alt = 'sfalt'; else $alt = '';
		}
		$out.= '</table>'."\n";

		# Display page links
		$out.= sf_render_topic_pagelinks($thispagelinks, true, false, false, false);
		$out.= '</div><br />'."\n";
	} else {
		$out.='<br /><div class="sfmessagestrip">'.__("You are not currently watching any topics!", "sforum").'</div>'."\n";
		$out.='</div>'."\n";
	}

	$out.= '<div class="sfloginstrip">'."\n";
	$out.= '<table align="center" cellpadding="0" cellspacing="0"><tr><td width="45%"></td>'."\n";
	$out.= '<td><a href="#forumtop"><img class="sfalignright" src="'.SFRESOURCES.'top.png" alt="" title="'.__("go to top", "sforum").'" /></a></td>'."\n";
	$out.= '<td width="45%"></td>'."\n";
	$out.= '</tr></table></div>';

	return $out;
}

?>