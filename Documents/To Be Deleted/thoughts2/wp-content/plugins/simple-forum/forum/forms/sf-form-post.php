<?php
/*
Simple:Press Forum
Post Form Rendering
$LastChangedDate: 2009-06-22 19:41:59 +0100 (Mon, 22 Jun 2009) $
$Rev: 2097 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

function sf_render_add_post_form($forumid, $topicid, $topicname, $statusset, $statusflag, $userid, $subs)
{
	global $sfvars, $current_user, $sfglobals;

	$out='';
	$msg=sf_validation_messages('post');

	$editor="TM";
	if($sfglobals['editor']['sfeditor'] != RICHTEXT) $editor="QT";

	$out.='<div id="sfpostform">'."\n";
	$out.='<br /><br />'."\n";
	$out.='<fieldset>'."\n";
	$out.='<legend>'.sprintf(__("Reply to Topic: <strong> %s </strong>", "sforum"), stripslashes($topicname)).'</legend>'."\n";

	$sfpostmsg=get_option('sfpostmsg');
	if($sfpostmsg['sfpostmsgpost'])
	{
		$out.='<div id="sfeditormsg">'.stripslashes($sfpostmsg['sfpostmsgtext']).'</div>';
	}

	$out.= '<form action="'.SF_PLUGIN_URL.'/sf-post.php" method="post" name="addpost" onsubmit="return sfjvalidatePostForm(this, \''.$editor.'\', \''.$msg[0].'\', \''.$msg[1].'\', \''.$msg[2].'\', \''.$msg[3].'\', \''.$msg[4].'\', \''.$msg[5].'\', \''.$msg[6].'\')">'."\n";

	$out.= sf_create_nonce('forum-userform_addpost');

	$out.='<input type="hidden" name="forumid" value="'.$forumid.'" />'."\n";
	$out.='<input type="hidden" name="forumslug" value="'.$sfvars['forumslug'].'" />'."\n";
	$out.='<input type="hidden" name="topicid" value="'.$topicid.'" />'."\n";
	$out.='<input type="hidden" name="topicslug" value="'.$sfvars['topicslug'].'" />'."\n";

	if(empty($userid))
	{
		if($current_user->sfmoderated)
		{
			$out.='<p><strong>'.__("NOTE: New Posts are subject to administrator approval before being displayed", "sforum").'</strong></p>'."\n";
		}
		$out.='<p>'.__("Guest Name (Required)", "sforum").':</p>'."\n";
		$out.='<input type="text" tabindex="1" class="sfcontrol sfpostcontrol" size="45" name="guestname" value="'.stripslashes($current_user->guestname).'" />'."\n";

		$out.='<p>'.__("Guest EMail (Required)", "sforum").':</p>'."\n";
		$out.='<input type="text" tabindex="2" class="sfcontrol sfpostcontrol" size="45" name="guestemail" value="'.stripslashes($current_user->guestemail).'" />'."\n";
	}

	$out.='<p>'.__("Topic Reply", "sforum").':</p>'."\n";

	$out.='<div class="sfformcontainer">'."\n";

	$out.= sf_setup_editor(3);

	$subscribed = false;
	if($current_user->sfsubscriptions)
	{
		if($subs)
		{
			$sublist = explode('@', $subs);
			foreach($sublist as $i)
			{
				if($i == $current_user->ID)
				{
					$subscribed = true;
				}
			}
		}
	}

	# Save/Smileys/Options
	$out.= '<table class="sfpostsavetable">'."\n";
	$out.= '<tr>'."\n";
	$out.= '<th>'.__("Save New Post", "sforum").'</th>';

	# Do we show the Smileys cell
	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']==1)
	{
		# Yes we do
		$out.= '<th>'.__("Smileys", "sforum").'</th>';
	}

	# Do we show the Options cell?
	$showoptioncell = false;
	if(($statusset !=0 && $current_user->sftopicstatus) || (!$subscribed && $current_user->sfsubscriptions) || ($current_user->forumadmin))
	{
		# Yes we do!
		$out.= '<th>'.__("Options", "sforum").'</th>';
		$showoptioncell = true;
	}

	$out.= '</tr><tr>';
	# Save/Cancel/Topic Status
	$out.= '<td valign="top">'."\n";

	# Start Spam Measures
	if($current_user->sfspam ? $usemath = false : $usemath = true);
	$enabled=' ';
	if($usemath)
	{
		$enabled = ' disabled="disabled" ';
		$out.='<div id="sfhide">'."\n";
		$out.='<p>Guest URL (required)<br />'."\n";
		$out.='<input type="text" class="yscontrol" size="30" name="url" value="" /></p>'."\n";
		$out.='</div>'."\n";

		$spammath = sf_math_spam_build();

		$out.='<p><strong>'.__("Math Required!", "sforum").'</strong><br />'."\n";
		$out.=sprintf(__("What is the sum of: <strong> %s + %s </strong>", "sforum"), '<br />'.$spammath[0], $spammath[1]).'&nbsp;&nbsp;&nbsp;'."\n";
		$out.='<input type="text" tabindex="4" class="sfcontrol" size="4" name="sfvalue1" id="sfvalue1" value="" onkeyup="sfjsetPostButton(this, '.$spammath[0].', '.$spammath[1].', \''.addslashes(__("Save New Post", "sforum")).'\', \''.addslashes(__("Do Math To Save", "sforum")).'\')" /></p>'."\n";
		$out.='<input type="hidden" name="sfvalue2" id ="sfvalue2" value="'.$spammath[2].'" />'."\n";
	}
	# End Spam Measures

	if($statusset !=0 && $current_user->sftopicstatus)
	{
		$out.= sf_render_topic_statusflag($statusset, $statusflag, 'ts-addpform', 'ts-pform', 'left');
		$out.= '<div class="sfclear"></div><br />';
	}

	$buttontext = addslashes(__("Save New Post", "sforum"));
	if($usemath) $buttontext = (__("Do Math To Save", "sforum"));

	$out.='<input type="submit"'.$enabled.'tabindex="5" class="sfcontrol" id="sfsave" name="newpost" value="'.$buttontext.'" />'."\n";
	$out.='&nbsp;<input type="button" tabindex="6" class="sfcontrol" name="cancel" value="'.__("Cancel", "sforum").'" onclick="sfjtoggleLayer(\'sfpostform\');" />'."\n";

	$out.='<div class="highslide-html-content" id="my-content" style="width: 200px">';
	$out.='<div class="inline-edit" id="sfvalid"></div>';
	$out.='<input type="button" class="sfcontrol" id="sfclosevalid" onclick="return hs.close(this)" value="Close" />';
	$out.='</div>';

	if($subscribed)
	{
		$out.= '<br /><br /><p><small>'.__("You are subscribed to this topic", "sforum").'</small></p>';
	} else {
		if($subs && $current_user->forumadmin)
		{
			$out.= '<p><small><br />'.__("This topic has User Subscriptions", "sforum").'</small></p>';
		}
	}

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

		if($statusset !=0 && $current_user->sftopicstatus)
		{
			if($statusflag == 0 ? $tsmsg=__("Assign Topic Status", "sforum") : $tsmsg=__("Change Topic Status", "sforum"));
			$out.= '&nbsp;&nbsp;<label><small>'.$tsmsg.':  '.sf_topic_status_select($statusset, $statusflag, true).'</small></label>';
			$out.= '<div class="sfclear"></div><br />';
		}

		$out.='<table class="sfcheckoptions" cellspacing="4" cellpadding="4">';

		if (!$subscribed && $current_user->sfsubscriptions)
		{
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'usersubscribed.png" alt="" title="'.__("Subscribe to this Topic", "sforum").'" /></td><td><input type="checkbox" name="topicsub" id="sftopicsub" tabindex="7" /><label for="sftopicsub">&nbsp;&nbsp;'.sf_render_icons("Subscribe to this Topic").'&nbsp;&nbsp;&nbsp;</label></td></tr>'."\n";
		}

		if($current_user->forumadmin)
		{
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'pin.png" alt="" title="'.__("Pin this Post", "sforum").'" /></td><td><input type="checkbox" name="postpin" id="sfpostpin" tabindex="8" /><label for="sfpostpin">&nbsp;&nbsp;'.sf_render_icons("Pin this Post").'&nbsp;&nbsp;&nbsp;</label></td></tr>'."\n";
			$out.='<tr><td width="20"><img src="'.SFRESOURCES.'clock.png" alt="" title="'.__("Edit Post Timestamp", "sforum").'" /></td><td><input type="checkbox" tabindex="9" id="sfeditTimestamp" name="editTimestamp" onclick="sfjtoggleLayer(\'sftimestamp\');"/><label for="sfeditTimestamp">&nbsp;&nbsp;'.sf_render_icons("Edit Timestamp").'</label></td></tr>'."\n";
		}
		$out.= '</table>';

		if($current_user->forumadmin)
		{

			global $wp_locale;
			$time_adj = time() + (get_option( 'gmt_offset' ) * 3600 );
			$dd = gmdate( 'd', $time_adj );
			$mm = gmdate( 'm', $time_adj );
			$yy = gmdate( 'Y', $time_adj );
			$hh = gmdate( 'H', $time_adj );
			$mn = gmdate( 'i', $time_adj );
			$ss = gmdate( 's', $time_adj );

			$out.='<div id="sftimestamp">'."\n";
			$out.='<select tabindex="10" name="tsMonth" onchange="editTimestamp.checked=true">'."\n";
			for ( $i = 1; $i < 13; $i = $i +1 ){
				$out.= "\t\t\t<option value=\"$i\"";
				if ( $i == $mm )
					$out.= ' selected="selected"';
				if(class_exists('WP_Locale'))
				{
					$out.= '>' . $wp_locale->get_month( $i ) . "</option>\n";
				} else {
					$out.= '>' . $month[$i] . "</option>\n";
				}
			}
			$out.='</select>'."\n";

			$out.='<input class="sfcontrolTS" tabindex="11" type="text" id="tsDay" name="tsDay" value="'.$dd.'" size="2" maxlength="2"/>'."\n";
			$out.='<input class="sfcontrolTS" tabindex="12" type="text" id="tsYear" name="tsYear" value="'.$yy.'" size="4" maxlength="5"/>@'."\n";
			$out.='<input class="sfcontrolTS" tabindex="13" type="text" id="tsHour" name="tsHour" value="'.$hh.'" size="2" maxlength="2"/> :'."\n";
			$out.='<input class="sfcontrolTS" tabindex="14" type="text" id="tsMinute" name="tsMinute" value="'.$mn.'" size="2" maxlength="2"/>'."\n";
			$out.='<input class="sfcontrolTS" tabindex="15" type="hidden" id="tsSecond" name="tsSecond" value="'.$ss.'" size="2" maxlength="2"/>'."\n";
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

function sf_render_edit_post_form($postid, $postcontent, $forumid, $topicid, $page, $postedit)
{
	global $sfvars, $current_user, $sfglobals;

	$out = '<a id="postedit"></a>'."\n";
	$out.='<form action="'.sf_build_url($sfvars['forumslug'], $sfvars['topicslug'], $sfvars['page'], $postid).'" method="post" name="editpostform">'."\n";

	$out.= sf_setup_editor(1, str_replace('&', '&amp;', stripslashes($postcontent)));
	$out.= '<br />';
	$out.='<input type="hidden" name="pid" value="'.$postid.'" />'."\n";
	$out.="<input type='hidden' name='pedit' value='".$postedit."' />"."\n";

	$out.='<input type="submit" class="sfcontrol" name="editpost" value="'.__("Save Edited Post", "sforum").'" />'."\n";
	$out.='&nbsp;<input type="submit" class="sfcontrol" name="cancel" value="'.__("Cancel", "sforum").'" />'."\n";

	$out.='</form>'."\n";

	return $out;
}

?>