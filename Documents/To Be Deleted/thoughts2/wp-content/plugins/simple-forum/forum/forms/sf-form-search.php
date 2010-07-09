<?php
/*
Simple:Press Forum
Search Form Rendering
$LastChangedDate: 2009-03-13 15:49:36 +0000 (Fri, 13 Mar 2009) $
$Rev: 1567 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

function sf_render_searchbox_form($pageview, $statusset=0)
{
	global $sfvars, $current_user;
	
	$out = '';

	$out.='<div id="sfsearchform">'."\n";

	$out.='<form action="'.SF_PLUGIN_URL.'/sf-search.php" method="post" name="search">'."\n";

	$out.='<fieldset>'."\n";
	$out.='<legend>'.__("Search Forums", "sforum").':</legend>'."\n";

	$out.='<div class="sfsearchblock">'."\n";
	$out.='<input type="text" class="sfcontrol" size="20" name="searchvalue" value="" />'."\n";
	$out.='<br /><br />'."\n";
	$out.= '<img src="'.SFRESOURCES.'searchicon.png" alt="" />&nbsp;'."\n";
	$out.='<input type="submit" class="sfcontrol" name="search" value="'.__("Search Forum", "sforum").'" />'."\n";
	$out.='</div>'."\n";

	# all or current forum?
	$out.='<div class="sfsearchblock">'."\n";
	$out.= '<div class="sfradioblock sfalignleft">'."\n";
	$ccheck='checked="checked"';
	$acheck='';
	if(($pageview == 'forum') || ($pageview == 'topic'))
	{
		$out.= '<input type="hidden" name="forumid" value="'.($sfvars['forumslug']).'" />'."\n";
		$out.= '<label class="sfradio" for="sfradio1">&nbsp;&nbsp;'.__("Current Forum", "sforum").'</label><input type="radio" id="sfradio1" name="searchoption" value="Current" '.$ccheck.' /><br />'."\n";
	} else {
		$acheck='checked="checked"';
	}
	$out.= '<label class="sfradio" for="sfradio2">&nbsp;&nbsp;'.__("All Forums", "sforum").'</label><input type="radio" id="sfradio2" name="searchoption" value="All Forums" '.$acheck.' />'."\n";
	$out.= '</div>'."\n";

	# search type?
	$out.= '<div class="sfradioblock sfalignleft">'."\n";
	$out.= '<label class="sfradio" for="sfradio3">&nbsp;&nbsp;'.__("Match Any Word", "sforum").'</label><input type="radio" id="sfradio3" name="searchtype" value="1" checked="checked" /><br />'."\n";
	$out.= '<label class="sfradio" for="sfradio4">&nbsp;&nbsp;'.__("Match All Words", "sforum").'</label><input type="radio" id="sfradio4" name="searchtype" value="2" /><br />'."\n";
	$out.= '<label class="sfradio" for="sfradio5">&nbsp;&nbsp;'.__("Match Phrase", "sforum").'</label><input type="radio" id="sfradio5" name="searchtype" value="3" />'."\n";
	$out.= '</div>'."\n";
	$out.='</div><br />'."\n";

 	$out.='</fieldset>'."\n";

	$out.= '<table><tr>';

	if($pageview == 'forum' && $statusset != 0)
	{
		$out.= '<td><fieldset>'."\n";
		$out.= '<legend>'.__("Topic Status Search (Current Forum)", "sforum").':</legend>'."\n";

		$out.= '<div class="sfsearchblock">'."\n";
		$out.= '<img src="'.SFRESOURCES.'searchicon.png" alt="" />&nbsp;'."\n";

		$out.= sf_topic_status_select($statusset, 0, false, true);
		$out.='<input type="submit" class="sfcontrol" name="statussearch" value="'.__("List Topics With Selected Status", "sforum").'" />'."\n";

		$out.= '</div>'."\n";
		$out.= '</fieldset></td>'."\n";
	}

	if($current_user->member)
	{
		$out.='<td><fieldset>'."\n";
		$out.='<legend>'.__("Member Search (Current or All Forums)", "sforum").':</legend>'."\n";

		$out.='<div class="sfsearchblock">'."\n";
	
		$out.= '<img src="'.SFRESOURCES.'searchicon.png" alt="" />&nbsp;'."\n";
		$out.= '<input type="hidden" name="userid" value="'.$current_user->ID.'" />'."\n";

		$out.='<input type="submit" class="sfcontrol" name="membersearch" value="'.__("List Topics You Have Posted To", "sforum").'" />'."\n";
		$out.='<input type="submit" class="sfcontrol" name="memberstarted" value="'.__("View Topics You Started", "sforum").'" />'."\n";
		$out.= '</div>'."\n";
		$out.='</fieldset></td>'."\n";
	}

	$out.= '</tr></table>';
	$out.='</form>'."\n";
	$out.='</div>'."\n";
	
	return $out;
}

?>