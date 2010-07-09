<?php
/*
Simple:Press Forum
New Topic Form Rendering
$LastChangedDate: 2009-06-22 19:41:59 +0100 (Mon, 22 Jun 2009) $
$Rev: 2097 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

function sf_render_add_topic_form($forumid, $forumname, $statusset, $userid)
{
	global $sfvars, $current_user, $sfglobals;

	$out='';
	$msg=sf_validation_messages('topic');

	$editor="TM";
	if($sfglobals['editor']['sfeditor'] != RICHTEXT) $editor="QT";

	$out.='<div id="sfpostform">'."\n";
	$out.='<br /><br />'."\n";
	$out.='<fieldset>'."\n";
	$out.='<legend>'.sprintf(__("Add New Topic to: <strong>%s</strong>", "sforum"), stripslashes($forumname)).'</legend>'."\n";

	$sfpostmsg=get_option('sfpostmsg');
	if($sfpostmsg['sfpostmsgtopic'])
	{
		$out.='<div id="sfeditormsg">'.stripslashes($sfpostmsg['sfpostmsgtext']).'</div>';
	}

	$out.= '<form action="'.SF_PLUGIN_URL.'/sf-post.php" method="post" name="addtopic" onsubmit="return sfjvalidatePostForm(this, \''.$editor.'\', \''.$msg[0].'\', \''.$msg[1].'\', \''.$msg[2].'\', \''.$msg[3].'\', \''.$msg[4].'\', \''.$msg[5].'\', \''.$msg[6].'\')">'."\n";

	$out.= sf_create_nonce('forum-userform_addtopic');

	$out.='<input type="hidden" name="forumid" value="'.$forumid.'" />'."\n";
	$out.='<input type="hidden" name="forumslug" value="'.$sfvars['forumslug'].'" />'."\n";

	if(empty($userid))
	{
		if($current_user->sfmoderated)
		{
			$out.='<p><strong>'.__("NOTE: New Posts are subject to administrator approval before being displayed", "sforum").'</strong></p>'."\n";
		}
		$out.='<p>'.__("Guest Name (Required)", "sforum").':</p>'."\n";
		$out.='<input type="text" tabindex="1" class="sfcontrol sfpostcontrol" size="45" name="guestname" value="'.stripslashes($current_user->guestname).'" />'."\n";

		$out.='<p>'.__("Guest EMail (Required)", "sforum").':</p>'."\n";
		$out.='<input type="text"  tabindex="2" class="sfcontrol sfpostcontrol" size="45" name="guestemail" value="'.stripslashes($current_user->guestemail).'" />'."\n";
	}

	$out.='<p>'.__("Topic Name", "sforum").':</p>'."\n";
	$out.='<input type="text"  tabindex="3" class="sfcontrol sfpostcontrol" size="55" name="newtopicname" value="" />'."\n";

	# Grab in-editor message if one
	$ineditor = stripslashes(get_option('sfeditormsg'));

	$out.='<p>'.__("Topic Message", "sforum").':</p>'."\n";

	$out.='<div class="sfformcontainer">'."\n";

	$out.= sf_setup_editor(4);

	# Save/Smileys/Options
	$out.= '<table class="sfpostsavetable">'."\n";
	$out.= '<tr>'."\n";
	$out.= '<th>'.__("Save New Topic", "sforum").'</th>';

	# Do we show the Smileys cell
	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']==1)
	{
		# Yes we do
		$out.= '<th>'.__("Smileys", "sforum").'</th>';
	}

	# Do we show the Options cell?
	$showoptioncell = false;
	if($current_user->sfsubscriptions || $current_user->sflock || $current_user->sfpin || $current_user->forumadmin || ($current_user->sflinkuse && current_user_can('publish_posts')))
	{
		# Yes we do!
		$out.= '<th>'.__("Options", "sforum").'</th>';
		$showoptioncell = true;
	}

	$out.= '</tr><tr>';

	# Save/Cancel/Topic Status
	$out.= '<td valign="top">'."\n";

	if($statusset !=0)
	{
		$out.= '<input type="hidden" name="statusflag" value="1" />'."\n";
		$out.= sf_render_topic_statusflag($statusset, 1, 'ts-addtform', 'ts-tform', 'left');
		$out.= '<div class="sfclear"></div><br />';
	}


	# Start Spam Measures
	if($current_user->sfspam ? $usemath = false : $usemath = true);
	$enabled=' ';
	if($usemath)
	{
		$enabled = ' disabled="disabled" ';
		$out.='<div id="sfhide">'."\n";
		$out.='<p>'.__("Guest URL (Required)", "sforum").'<br />'."\n";
		$out.='<input type="text" class="yscontrol" size="30" name="url" value="" /></p>'."\n";
		$out.='</div>'."\n";

		$spammath = sf_math_spam_build();

		$out.='<p><strong>'.__("Math Required!", "sforum").'</strong><br />'."\n";
		$out.=sprintf(__("What is the sum of: <strong> %s + %s </strong>", "sforum"), '<br />'.$spammath[0], $spammath[1]).'&nbsp;&nbsp;&nbsp;'."\n";
		$out.='<input type="text" tabindex="5" class="sfcontrol" size="4" name="sfvalue1" value="" onkeyup="sfjsetTopicButton(this, '.$spammath[0].', '.$spammath[1].', \''.addslashes(__("Save New Topic", "sforum")).'\', \''.addslashes(__("Do Math To Save", "sforum")).'\')" /></p>'."\n";
		$out.='<input type="hidden" name="sfvalue2" value="'.$spammath[2].'" />'."\n";
	}
	# End Spam Measures

	$buttontext = addslashes(__("Save New Topic", "sforum"));
	if($usemath) $buttontext = addslashes(__("Do Math To Save", "sforum"));

	$out.='<input type="submit"'.$enabled.'tabindex="6" class="sfcontrol" name="newtopic" id="sfsave" value="'.$buttontext.'" />'."\n";
	$out.='&nbsp;<input type="button" tabindex="7" class="sfcontrol" name="cancel" value="'.__("Cancel", "sforum").'" onclick="sfjtoggleLayer(\'sfpostform\');" />'."\n";

	$out.='<div class="highslide-html-content" id="my-content" style="width: 200px">';
	$out.='<div class="inline-edit" id="sfvalid"></div>';
	$out.='<input type="button" class="sfcontrol" id="sfclosevalid" onclick="return hs.close(this)" value="Close" />';
	$out.='</div>';

	$out.= '</td>'."\n";

	# Smileys
	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']==1)
	{
		$out.= '<td valign="top">'."\n";
		$out.= sf_render_smileys();
		$out.= '</td>'."\n";
	}

	# Post Options

	if($showoptioncell)
	{
		$out.= '<td width="40%" valign="top">'."\n";

		$out.='<table class="sfcheckoptions" cellspacing="4" cellpadding="4">';
		if($current_user->sfsubscriptions)
		{
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'usersubscribed.png" alt="" title="'.__("Subscribe to this Topic", "sforum").'" /></td><td><input type="checkbox" name="topicsub" id="sftopicsub" tabindex="8" /><label for="sftopicsub">&nbsp;&nbsp;'.sf_render_icons("Subscribe to this Topic").'</label></td></tr>'."\n";
		}
		if($current_user->sflock)
		{
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'locked.png" alt="" title="'.__("Lock this Topic", "sforum").'" /></td><td><input type="checkbox" name="topiclock" id="sftopiclock" tabindex="9" /><label for="sftopiclock">&nbsp;&nbsp;'.sf_render_icons("Lock this Topic").'</label></td></tr>'."\n";
		}
		if($current_user->sfpin)
		{
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'pin.png" alt="" title="'.__("Pin this Topic", "sforum").'" /></td><td><input type="checkbox" name="topicpin" id="sftopicpin" tabindex="10"  /><label for="sftopicpin">&nbsp;&nbsp;'.sf_render_icons("Pin this Topic").'</label></td></tr>'."\n";
		}
		if($current_user->forumadmin)
		{
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'clock.png" alt="" title="'.__("Edit Topic Timestamp", "sforum").'" /></td><td><input type="checkbox" tabindex="11" id="sfeditTimestamp" name="editTimestamp" onclick="sfjtoggleLayer(\'sftimestamp\');"/><label for="sfeditTimestamp">&nbsp;&nbsp;'.sf_render_icons("Edit Timestamp").'</label></td></tr>'."\n";
		}
		if(($current_user->sflinkuse) && (current_user_can('publish_posts')))
		{
			$gif= SFJSCRIPT.'working.gif';
			$site=SF_PLUGIN_URL."/forum/ahah/sf-ahahcategories.php?forum=".$forumid;
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'createlink.png" alt="" title="'.__("Link Topic to Blog", "sforum").'" /></td><td><input type="checkbox" name="bloglink" id="sfbloglink" tabindex="12" onchange="sfjgetCategories(\''.$gif.'\', \''.$site.'\', this.checked)" /><label for="sfbloglink">&nbsp;&nbsp;'.sf_render_icons("Create Linked Post").'</label></td></tr>'."\n";
		}
		$out.='</table>';

		$out.='<div id="sfcats"></div>';

		if($current_user->forumadmin)
		{
			global $wp_locale, $month;
			$time_adj = time() + (get_option( 'gmt_offset' ) * 3600 );
			$dd = gmdate( 'd', $time_adj );
			$mm = gmdate( 'm', $time_adj );
			$yy = gmdate( 'Y', $time_adj );
			$hh = gmdate( 'H', $time_adj );
			$mn = gmdate( 'i', $time_adj );
			$ss = gmdate( 's', $time_adj );

			$out.='<div id="sftimestamp">'."\n";
			$out.='<select tabindex="13" name="tsMonth" onchange="editTimestamp.checked=true">'."\n";
			for ( $i = 1; $i < 13; $i = $i +1 ){
				$out.= "\t\t\t<option value=\"$i\"";
				if ( $i == $mm ) $out.= ' selected="selected"';
				if(class_exists('WP_Locale'))
				{
					$out.= '>' . $wp_locale->get_month( $i ) . "</option>\n";
				} else {
					$out.= '>' . $month[$i] . "</option>\n";
				}
			}
			$out.='</select>'."\n";

			$out.='<input class="sfcontrolTS" tabindex="14" type="text" id="tsDay" name="tsDay" value="'.$dd.'" size="2" maxlength="2"/>'."\n";
			$out.='<input class="sfcontrolTS" tabindex="15" type="text" id="tsYear" name="tsYear" value="'.$yy.'" size="4" maxlength="5"/>@'."\n";
			$out.='<input class="sfcontrolTS" tabindex="16" type="text" id="tsHour" name="tsHour" value="'.$hh.'" size="2" maxlength="2"/> :'."\n";
			$out.='<input class="sfcontrolTS" tabindex="17" type="text" id="tsMinute" name="tsMinute" value="'.$mn.'" size="2" maxlength="2"/>'."\n";
			$out.='<input class="sfcontrolTS" tabindex="18" type="hidden" id="tsSecond" name="tsSecond" value="'.$ss.'" size="2" maxlength="2"/>'."\n";
			$out.='</div>'."\n";
		}

		$out.= '</td>'."\n";
	}

	$out.= '</tr>'."\n";
	$out.= '</table>'."\n";
	$out.='</div>'."\n";
	$out.='<br />'."\n";
	$out.='</form>'."\n";
	$out.='</fieldset>'."\n";
	$out.='</div>'."\n";

	return $out;
}

function sf_render_edit_topic_title_form($topicid, $topicname, $forumid)
{
	global $sfvars;

	$topicslug=sf_get_topic_slug($topicid);

	$out = '<a id="topicedit"></a>'."\n";
	$out.='<form action="'.sf_build_url($sfvars['forumslug'], '', $sfvars['page'], 0).'" method="post" name="edittopicform">'."\n";
	$out.='<input type="hidden" name="tid" value="'.$topicid.'" />'."\n";
	$out.='<td>';

	$out.= __('Topic Title', 'sforum').':<br />';
	$out.='<textarea class="sftextarea" name="topicname" rows="2">'.attribute_escape($topicname).'</textarea>'."\n";

	$out.='<br />'.__('Topic Slug', 'sforum').':<br />';
	$out.='<textarea class="sftextarea" name="topicslug" rows="2">'.$topicslug.'</textarea></td>'."\n";


	$out.='<td><input type="submit" class="sfcontrol" name="edittopic" value="'.__("Save", "sforum").'" /></td>'."\n";
	$out.='<td><input type="submit" class="sfcontrol" name="cancel" value="'.__("Cancel", "sforum").'" /></td>'."\n";
	$out.= '</form>'."\n";

	return $out;
}

?>