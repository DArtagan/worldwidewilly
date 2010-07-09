<?php
/*
Simple:Press Forum
Buddy List Display
$LastChangedDate: 2009-01-14 02:05:53 +0000 (Wed, 14 Jan 2009) $
$Rev: 1199 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

# let user view their permissions
function sf_render_buddylist_form()
{
	global $current_user;
	
	$out = '';

	$out.= sf_render_queued_message();
	
	$out.='<br />';
	$out.='<div id="sfstandardform">'."\n";
	$out.='<div class="sfheading">';
	$out.='<table><tr>'."\n";
	$out.='<td class="sficoncell">'.sf_render_avatar('user', $current_user->ID, $current_user->user_email, '').'</td>';
	$out.='<td><p>'.sprintf(__("Current Buddies List for:%s", "sforum"), '<br />'.$current_user->user_login.' ('.stripslashes($current_user->display_name).')').'</p></td>'."\n";
	$out.='<td><img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" /><a class="sficon sfalignright" href="'.SFURL.'"><img src="'.SFRESOURCES.'goforum.png" alt="" title="'.__("Return to forum", "sforum").'" />'.sf_render_icons("Return to forum").'</a></td>'."\n";
	$out.='</tr></table>';
	$out.='</div>';

	$buddies = sf_get_member_item($current_user->ID, 'buddies');
	if ($buddies)
	{
		$out.= '<table class="sfforumtable">';
		$out.= '<tr>';
		$out.= '<th align="left">'.__("Buddy Name", "sforum").'</th>';
		$out.= '<th align="center" width="200">'.__("Manage", "sforum").'</th>';
		$out.= '</tr>';

		foreach ($buddies as $buddy)
		{
			$buddy_name = stripslashes(sf_get_member_item($buddy, "display_name"));
			$out.= '<tr>';
			$out.= '<td colspan="2" style="border-bottom:0px;padding:0px;">';
			$out.= '<div id="buddy'.$buddy.'">';
			$out.= '<table width="100%" cellspacing="0">';
			$out.= '<tr>';
			$out.= '<td align="left">'.$buddy_name.'</td>';
			$out.= '<td align="center" width="100">';
			$url = sf_build_qurl("pmaction=sendpm&amp;pms={$current_user->ID}&amp;pmtoname={$buddy_name}");
			$out.= '<a class="sficon" href="'.$url.'"><img src="'.SFRESOURCES.'sendpm-small.png" alt="" title="'.__("Send PM to Buddy", "sforum").'" />&nbsp;'.sf_render_icons("Send PM").'</a>';
			$out.= '</td>';
			$out.= '<td align="center" width="100">';
			$text = __("Remove from Buddy List", "sforum");
			$site = SF_PLUGIN_URL."/forum/ahah/sf-ahahprofile.php?u=".$current_user->ID."&buddy=".$buddy;
			$out.= '<img onclick="sfjremoveBuddy(\''.$site.'\', \'buddy'.$buddy.'\');" src="'.SFRESOURCES.'pmdelete.png" alt="" title="'.$text.'"/>';
			$out.= '</td>';
			$out.= '</tr>';
			$out.= '</table>';
			$out.= '</div>';
			$out.= '</td>';
			$out.= '</tr>';
		}
		$out.= '</table>';
		$out.= '<br />';
	} else {
		$out.= '<br />';
		$out.='<div class="sfmessagestrip">'.__("Sorry, you do not have any members in your Buddy List.", "sforum").'</div>';
		$out.= '<br />';
	}
	$out.= '<hr />';	
	$out.= '&nbsp;<input type="button" class="sfcontrol" name="button1" value="'.__("Return to Profile", "sforum").'" onclick="sfjreDirect(\''.SFPROFILE.'\');" />'."\n";
	$out.= '&nbsp;<input type="button" class="sfcontrol" name="button2" value="'.__("Return to Forum", "sforum").'" onclick="sfjreDirect(\''.SFURL.'\');" />'."\n";
	$out.= '<br /><br />';

	$out.= '</div><br />';
	
	return $out;
}

?>