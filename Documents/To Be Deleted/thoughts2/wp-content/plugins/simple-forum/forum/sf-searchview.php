<?php
/*
Simple:Press Forum
Topic View Display
$LastChangedDate: 2009-01-04 20:29:21 +0000 (Sun, 04 Jan 2009) $
$Rev: 1138 $
*/

function sf_render_search_all($paramtype, $paramvalue)
{
	global $sfvars, $current_user, $sfglobals;

	$searchpage = sf_syscheckint($_GET['search']);
	$search=true;

	$out = '';

	# Display Header
	$out.= '<div class="sfblock">'."\n";
	$out.= sf_render_main_header_table('searchall', 0, '', '', $paramvalue, SFRESOURCES.'searchicon.png', false, true);

	# Get topic list
	$topics = sf_get_combined_full_topic_search($paramvalue, $searchpage);

	if($topics)
	{
		# Fet some stuff we need
		$cols=get_option('sftopiccols');
		$thispagelinks = sf_compile_paged_topics('all', 0, $sfvars['page'], true, $searchpage, 0);

		# Display page links
		$out.= sf_render_topic_pagelinks($thispagelinks, false, false, false);

		# Start table display
		$out.= '<table class="sfforumtable">'."\n";
		$out.= sf_render_searchall_column_header_row($cols);
		$alt = '';

		foreach($topics as $topic)
		{
			# Get what we need
			$stats = sf_get_combined_topic_stats($topic['topic_id'], $topic['post_id'], $topic['post_count'], $cols);

			# Let's package up some of these values to aid the paramater list!
			$value['searchpage']=$searchpage;
			$value['paramvalue']=$paramvalue;
			$value['paramtype']=$paramtype;

			# Display result row
			$out.= sf_render_searchall_entry_row($topic, $cols, $stats, $value, $alt);
			if ($alt == '') $alt = 'sfalt'; else $alt = '';
		}
		$out.= '</table>'."\n";

		# Display page links
		$out.= sf_render_topic_pagelinks($thispagelinks, false, false, false);
		$out.= '</div><br />'."\n";

	} else {

		$out.='<br /><div class="sfmessagestrip">'.__("No Matches Found", "sforum").'</div>'."\n";
		$out.='</div>'."\n";
		delete_sfsetting($paramvalue);
	}
	$out.= '<div class="sfloginstrip">'."\n";
	$out.= '<table align="center" cellpadding="0" cellspacing="0"><tr><td width="45%"></td>'."\n";
	$out.= '<td><a href="#forumtop"><img class="sfalignright" src="'.SFRESOURCES.'top.png" alt="" title="'.__("go to top", "sforum").'" /></a></td>'."\n";
	$out.= '<td width="45%"></td>'."\n";
	$out.= '</tr></table></div>';

	return $out;
}

?>