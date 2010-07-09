<?php
/*
Simple:Press Forum
Group View Display
$LastChangedDate: 2009-01-22 20:24:43 +0000 (Thu, 22 Jan 2009) $
$Rev: 1275 $
*/

function sf_render_group()
{
	global $sfvars, $current_user, $sfglobals;

	$out='';
	
	# Setup stuff we will need
	# columns to display
	$cols=get_option('sfforumcols');

	# Check if group id is passed in query var (url)
	if(isset($_GET['group'])) 
	{
		$groupid = sf_syscheckint($_GET['group']);
		if(!sf_group_exists($groupid))
		{
			$sfvars['error'] = true;
			update_sfnotice('sfmessage', '1@'.sprintf(__('Group %s Not Found', 'sforum'), $groupid));
			$out = sf_render_queued_message();
			$out.= '<div class="sfmessagestrip">'."\n";
			$out.= sprintf(__("There is no such group with ID %s", "sforum"), $groupid)."\n";
			$out.= '</div>'."\n";
			return $out;
		}
	} else {
		$groupid = Null;
	}
	
	# Get group records
	$groups = sf_get_combined_groups_and_forums($groupid);

	# If No Access to anything then return access denied flag
	if($groups[0]['group_id'] == "Access Denied") return 'Access Denied';

	if($groups)
	{
		foreach($groups as $group)
		{
			# Setup group icon
			if(!empty($group['group_icon']))
			{
				$icon = SFRESOURCES.'custom/'.$group['group_icon'];
			} else {
				$icon = SFRESOURCES.'group.png';
			}
			$alt = '';

			# Start Display
			# Display group header
			$out.= '<div class="sfblock">'."\n";
			$out.= '<a id="g'.$group['group_id'].'"></a>'."\n";
			$out.= sf_render_main_header_table('group', $group['group_id'], $group['group_name'], $group['group_desc'], '', $icon,'','','','','','','',$group['forums']);
			
			# Get list of forums in current group
			$out.= '<table class="sfforumtable">'."\n";

			if($group['forums'])
			{
				# Display forum column headers
				$out.= sf_render_group_column_header_row($cols);

				foreach($group['forums'] as $forum)
				{
					$stats = sf_get_combined_forum_stats($forum['forum_id'], $forum['post_id'], $cols);
	
					# Display current forum row
					$out.= sf_render_forum_entry_row($forum, $cols, $stats, $alt);
					if ($alt == '') $alt = 'sfalt'; else $alt = '';
				}
			} else {
				$out.= '<div class="sfmessagestrip">'.__("There are No Forums defined in this Group", "sforum").'</div>'."\n";			
			}
			$out.= '</table>'."\n";
			$out.= '</div>'."\n";

		}
		if(function_exists('sf_hook_post_group'))
		{
			$out.= sf_hook_post_group($group['group_id']);
		}
	} else {
		$out.= '<div class="sfmessagestrip">'.__("There are No Groups defined", "sforum").'</div>'."\n";
	}
	return $out;	
}

?>