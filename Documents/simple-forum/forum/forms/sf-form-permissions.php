<?php
/*
Simple:Press Forum
User Permissions Display
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

# let user view their permissions
function sf_render_permissions_form()
{
	global $current_user;
	
	# need access to the roles and they are defined in admin
	include_once(SF_PLUGIN_DIR.'/admin/sf-adminsupport.php');

	$out = '';

	$out.= sf_render_queued_message();
	
	$out.='<br />';
	$out.='<div id="sfstandardform">'."\n";
	$out.='<div class="sfheading">';
	$out.='<table><tr>'."\n";
	$out.='<td class="sficoncell">'.sf_render_avatar('user', $current_user->ID, $current_user->user_email, '').'</td>';
	$out.='<td><p>'.sprintf(__("Current Permission Settings for:%s", "sforum"), '<br />'.$current_user->user_login.' ('.stripslashes($current_user->display_name).')').'</p></td>'."\n";
	$out.='<td><img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" /><a class="sficon sfalignright" href="'.SFURL.'"><img src="'.SFRESOURCES.'goforum.png" alt="" title="'.__("Return to forum", "sforum").'" />'.sf_render_icons("Return to forum").'</a></td>'."\n";
	$out.='</tr></table>';
	$out.='</div><br />';

	$groups = sf_get_combined_groups_and_forums();
	if ($groups)
	{
		foreach ($groups as $group)
		{
			$out.= '<div class="sfheading">';
			$out.= '<table>';
			$out.= '<tr>';
			$out.= '<td><p>'.stripslashes($group['group_name']).'<br /><small>'.stripslashes($group['group_desc']).'</small></p></td>';
			$out.= '</tr>';
			$out.= '</table>';
			$out.= '</div>';
			$out.= '<table class="sfforumtable">';
			if($group['forums'])
			{
				foreach($group['forums'] as $forum)
				{
					$out.= '<tr>';
					$out.= '<td><p>'.stripslashes($forum['forum_name']).'<br /><small>'.stripslashes($forum['forum_desc']).'</small></p></td>';
					$out.= '<td align="center" width="200px">';
					$out.= '<input style="width:150px" type=button class="sfcontrol" value="'.__("View Permissions", "sforum").'" onClick="sfjtoggleLayer(\'perm'.$forum['forum_id'].'\');">';

					$out.= '</td>';
					$out.= '</tr>';
					$out.= '<tr>';
					$out.= '<td colspan="2">';
					$out.= '<div id="perm'.$forum['forum_id'].'" class="inline_edit">';
					$out.= '<table class="sfposttable" border="0" cellspacing="5">';
					$out.= '<tr>';
					$items = count($sfactions['action']);
					$cols = 2;
					$rows  = ($items / $cols);
					$lastrow = $rows;
					$lastcol = $cols;
					$curcol = 0;
					if (!is_int($rows)) 
					{
						$rows = (intval($rows) + 1);
						$lastrow = $rows - 1; 
						$lastcol = ($items % $cols);
					}						
					$thisrow = 0;
					foreach ($sfactions["action"] as $index => $action)
					{
						$button = 'b-'.$index; 
						if ($thisrow == 0)
						{
							$out.= '<td width="50%" class="sfpostcontent">';
							$out.= '<table class="form-table">';
							$curcol++;
						}
						$out.= '<tr>';
						$out.= '<td>';
						if (sf_user_can($current_user->ID, $action, $forum['forum_id']))
						{
							$out.= '<img src="'.SFRESOURCES.'success.png" />&nbsp;&nbsp;'.__($action, "sforum");				
						} else {
							$out.= '<img src="'.SFRESOURCES.'failure.png" />&nbsp;&nbsp;'.__($action, "sforum");										}
						$out.= '</td>';
						$out.= '</tr>';
						$thisrow++;
						if (($curcol <= $lastcol && $thisrow == $rows) || ($curcol > $lastcol && $thisrow == $lastrow))
						{											
							$out.= '</table>';
							$out.= '</td>';
							$thisrow = 0;
						}				
					}
					$out.= '</tr>';
					$out.= '<tr style="height"40">';
					$out.= '<td colspan="2">';
					$string = __("Close", "sforum");
					$out.= '<input style="width:50px" type="button" class="sfcontrol" name="cancel" value="'.$string.'" onclick="sfjtoggleLayer(\'perm'.$forum['forum_id'].'\');" /><br /><br />';
					$out.= '</td>';
					$out.= '</tr>';
					$out.= '</table>';
					$out.= '</div>';
					$out.= '</td>';
					$out.= '</tr>';
				}
			} else {
				$out.= '<div class="sfmessagestrip">'.__("There are No Forums defined in this Group", "sforum").'</div>'."\n";	
			}
			$out.= '</table>';
			$out.= '<br />';
		}
	} else {
		$out.= '<br />';
		$out.='<div class="sfmessagestrip">'.__("Sorry, you do not have permissions to any Groups of Forums.", "sforum").'</div>';
		$out.= '<br /><hr />';
	}
	
	$out.='&nbsp;<input type="button" class="sfcontrol" name="button1" value="'.__("Return to Profile", "sforum").'" onclick="sfjreDirect(\''.SFPROFILE.'\');" />'."\n";
	$out.='&nbsp;<input type="button" class="sfcontrol" name="button2" value="'.__("Return to Forum", "sforum").'" onclick="sfjreDirect(\''.SFURL.'\');" />'."\n";

	$out.= '</div><br />';
	
	return $out;
}

?>