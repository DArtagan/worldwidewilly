<?php
/*
Simple:Press Forum
New PM Form Rendering
$LastChangedDate: 2009-04-22 22:48:20 +0100 (Wed, 22 Apr 2009) $
$Rev: 1762 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

function sf_render_add_pm_form()
{
	global $current_user, $sfglobals;

	$out = '';

	$editor="TM";
	if($sfglobals['editor']['sfeditor'] != RICHTEXT) $editor="QT";

	$valmsg0 = __("Incomplete Entry! Please correct and re-save", "sforum");
	$valmsg1 = __("No Recipients Selected", "sforum");
	$valmsg2 = __("No Message Title Entered", "sforum");
	$valmsg3 = __("No Message Text Entered", "sforum");

	$out.='<br /><br />'."\n";
	$out.='<div id="sfpostform">'."\n";
	$out.='<fieldset>'."\n";
	$out.='<legend>'.__("Compose Private Message", "sforum").'</legend>'."\n";

	$out.= '<form action="'.SF_PLUGIN_URL.'/sf-pmpost.php" method="post" name="addpm" onsubmit="return sfjvalidatePMForm(this, \''.$editor.'\', \''.$valmsg0.'\', \''.$valmsg1.'\', \''.$valmsg2.'\', \''.$valmsg3.'\')">'."\n";

	$out.= sf_create_nonce('forum-userform_addpm');

	$out.= '<input type="hidden" tabindex="0" name="pmaction" id="pmaction" value="savepm" />'."\n";
	$out.= '<input type="hidden" tabindex="0" name="pmuser" id="pmuser" value="'.$current_user->ID.'" />'."\n";
	$out.= '<input type="hidden" tabindex="0" name="pmslug" id="pmslug" value="" />'."\n";

	# Recipient Selection
	$out.= '<fieldset><legend>'.__("Select Message Recipients", "sforum").'</legend>';
	$out.= '<table cellspacing="0" cellpadding="0" border="0" width="80%">';

	# Header row
	$out.= '<tr valign="top">'."\n";
	$out.= '<th width="30%">'.__("Select From Member List", "sforum").'</th><th width="5%"></th><th width="30%">'.__("Select From Buddy List", "sforum").'</th><th width="5%"></th><th width="30%">'.__("Message To", "sforum").'</th>'."\n";
	$out.= '</tr>'."\n";

	# List Box row
	$out.= '<tr valign="top">';
	$out.= '<td><div id="pmmembers"><select class="sflistcontrol" tabindex="4" name="pmmemlist" id="pmmemlist" size="9" onchange="sfjaddpmUser(\'pmmemlist\');">'."\n";
	$out.= sf_create_pmuser_select(-1, 'members', 'a');
	$out.= '</select></div></td>'."\n";
	$out.= '<td></td>';

	$out.= '<td><div id="pmbuddies"><select class="sflistcontrol" tabindex="5" name="pmbudlist" id="pmbudlist" size="9" onchange="sfjaddpmUser(\'pmbudlist\');">'."\n";
	$out.= sf_create_pmuser_select(-1, 'buddies');
	$out.= '</select></div></td>'."\n";
	$out.= '<td><img class="sfalignright" src="'.SFRESOURCES.'arrowr.png" alt="" /></td>';

	$out.= '<td><select class="sflistcontrol" tabindex="6" name="pmtonamelist" id="pmtonamelist" multiple="multiple" size="9" onclick="sfjremovepmUser();">'."\n";
	# define an empty option for HTML validation!
	$out.= '<option></option>'."\n";
	$out.= '</select></td>'."\n";
	$out.= '</tr>';

	# Function row
	$out.= '<tr valign="top">';
	$out.= '<td>';
	$out.= '<img src="'.SFRESOURCES.'arrowd.png" alt="" /><br />';
	$out.= '<fieldset><legend>'.__("Filter Members by Name", "sforum").'</legend>';
	$out.= '<label for="asearch">'.__("Starting", "sforum").':&nbsp;</label>';
	$out.= '<input type="text" class="sfcontrol sfpostcontrol" maxlength="3" tabindex="1" name="asearch" id="asearch" size="2" />';
	$site=SF_PLUGIN_URL."/messaging/ahah/sf-ahahpmexplode.php?";
	$out.= '<input type="button" class="sfxcontrol" name="goasearch" id="goasearch" tabindex="2" value="'.__("Find", "sforum").'" onclick="sfpopulateMembers(\''.$site.'\',\'search\');" />';
	$out.= '<input type="button" class="sfxcontrol" name="loadall" id="loadall" tabindex="3" value="'.__("All", "sforum").'" onclick="sfpopulateMembers(\''.$site.'\',\'all\');" />';
	$out.= '</fieldset></td>';

	$out.= '<td></td><td></td><td></td>';

	$out.= '<td>';
	$out.= '<img src="'.SFRESOURCES.'arrowd.png" alt="" /><br />';
	$out.= '<fieldset><legend>'.__("Add to Buddy List", "sforum").'</legend>';
	$out.= '<label for="addbuddy">'.__("Add to Buddies", "sforum").':&nbsp;</label>';
	$out.= '<input type="button" class="sfxcontrol" name="addbuddy" id="addbuddy" tabindex="2" value="'.__("Add", "sforum").'" onclick="sfaddBuddies(\''.$site.'\');" />';
	$out.= '</fieldset></td>';

	$out.= '</tr></table>'."\n";
	$out.= '</fieldset>';

	$out.= '<br />'."\n";
	$dummy = "";
	$out.= '<input type="hidden" tabindex="0" size="45" name="pmtoidlist" id="pmtoidlist" value="'.$dummy.'" />'."\n";
	$out.= '<input type="hidden" tabindex="0" name="pmreply" id="pmreply" value="" />'."\n";
	$out.='<p>'.__("Title", "sforum").':</p>'."\n";
	$out.='<input type="text" tabindex="7" class="sfcontrol sfpostcontrol" size="45" name="pmtitle" id="pmtitle" value="" />'."\n";

	$out.='<p>'.__("Message", "sforum").':</p>'."\n";

	$out.='<div class="sfformcontainer">'."\n";

	$out.= sf_setup_editor(5);

	$out.='</div>'."\n";

	$out.='<br />'."\n";

	# Send/Smileys/
	$out.= '<table class="sfpostsavetable">'."\n";
	$out.= '<tr>'."\n";
	$out.= '<th>'.__("Send Message", "sforum").'</th>';

	# Do we show the Smileys cell
	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']==1)
	{
		# Yes we do 
		$out.= '<th>'.__("Smileys", "sforum").'</th>';
	}
	
	$out.= '</tr><tr>';
	# Send/Cancel
	$out.= '<td valign="top">'."\n";

	$out.='<input type="submit" tabindex="8" class="sfcontrol" name="newpm" id="sfsave" value="'.__("Send Message", "sforum").'" />'."\n";
	$out.='&nbsp;<input type="button" tabindex="9" class="sfcontrol" name="cancel" value="'.__("Cancel", "sforum").'" onclick="sfjtoggleLayer(\'sfpostform\');" />'."\n";

	$out.='<div class="highslide-html-content" id="my-content" style="width: 200px">'."\n";
	$out.='<div class="inline-edit" id="sfvalid"></div>'."\n";
	$out.='<input type="button" class="sfcontrol" id="sfclosevalid" onclick="return hs.close(this)" value="'.__("Close", "sforum").'" />'."\n";
	$out.='</div>'."\n";

	$out.= '</td>'."\n";
	
	# Smileys
	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']==1)
	{
		$out.= '<td valign="top">'."\n";
		$out.= sf_render_smileys();
		$out.= '</td>'."\n";
	}
	
	$out.= '</tr>'."\n";
	$out.= '</table>'."\n";

	$out.='</form>'."\n";
	$out.='</fieldset>'."\n";
	$out.='</div>'."\n";

	return $out;
}

?>