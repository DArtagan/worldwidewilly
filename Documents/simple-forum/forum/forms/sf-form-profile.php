<?php
/*
Simple:Press Forum
Profile Form Rendering
$LastChangedDate: 2009-04-17 17:56:15 +0100 (Fri, 17 Apr 2009) $
$Rev: 1719 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

function sf_render_profile_form($newuser)
{
	global $wpdb, $current_user, $sfglobals;

	$out='';

	$out.= sf_render_queued_message();

	# grab editor status
	$editorOK = false;
	if($sfglobals['editor']['sfusereditor']) $editorOK = true;

	if(!$current_user->member)
	{
		$out.= '<div id="sforum">'."\n";
		$out.='&nbsp;<input type="button" class="sfcontrol" name="button1" value="'.__("Return to Forum", "sforum").'" onclick="sfjreDirect(\''.SFURL.'\');" />'."\n";
		$out.= '</div>';
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		$out.= sf_render_queued_message();
		return $out;
	}

	$profile=array();

	$profile['first_name']=attribute_escape($current_user->first_name);
	$profile['last_name']=attribute_escape($current_user->last_name);
	$profile['display_name']=attribute_escape(sf_get_member_item($current_user->ID, 'display_name'));

	$profile['signature']=attribute_escape(sf_get_member_item($current_user->ID, 'signature'));
	$profile['sigimage']=attribute_escape(sf_get_member_item($current_user->ID, 'sigimage'));

	if($editorOK) $profile['myeditor']=sf_get_member_item($current_user->ID, 'editor');

	$ext = get_option('sfextprofile');

	if (isset($current_user->aim)) $profile['aim'] = attribute_escape($current_user->aim); else $profile['aim'] = '';
	if (isset($current_user->yim)) $profile['yim'] = attribute_escape($current_user->yim); else $profile['yim'] = '';
	if (isset($current_user->jabber)) $profile['jabber'] = attribute_escape($current_user->jabber); else $profile['jabber']= '' ;

	if (isset($current_user->description)) $profile['description'] = wp_specialchars(stripslashes($current_user->description)); else $profile['description'] = '';

	if($ext)
	{
		if (isset($current_user->msn)) $profile['msn'] = attribute_escape($current_user->msn); else $profile['msn'] = '';
		if (isset($current_user->skype)) $profile['skype'] = attribute_escape($current_user->skype); else $profile['skype'] = '';
		if (isset($current_user->icq)) $profile['icq'] = attribute_escape($current_user->icq); else $profile['icq'] = '';
		$profile['location'] = attribute_escape($current_user->location);
	}

	# Start Display
	# header
	$out.='<br />';

	$out.='<div id="sfstandardform">'."\n";

	# pre-profile hook
	if(function_exists('sf_hook_pre_profile'))
	{
		$out .= sf_hook_pre_profile();
	}

	$out.='<div class="sfheading">';
	$out.='<table><tr>'."\n";
	$out.='<td class="sficoncell">'.sf_render_avatar('user', $current_user->ID, $current_user->user_email, '').'</td>';
	$out.='<td><p>'.sprintf(__("Profile Information for:%s", "sforum"), '<br />'.$current_user->user_login.' ('.stripslashes($profile['display_name']).')').'</p></td>'."\n";
	$out.='<td><img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" /><a class="sficon sfalignright" href="'.SFURL.'"><img src="'.SFRESOURCES.'goforum.png" alt="" title="'.__("Return to forum", "sforum").'" />'.sf_render_icons("Return to forum").'</a></td>'."\n";
	$out.='</tr></table>';
	$out.='</div>';

	# if new user display message and update posts count to zero so this doesn't show up again for them.
	if($newuser)
	{
		$out.='<p><strong>'.__("Welcome to the forum. As a new member who has not yet posted, it is recommended that you change your password. Please also consider entering some personal information about yourself.", "sforum").'</strong></p><br />';
	}
	$out.='<p><strong>'.__("Note: Except for the email address - all other information may be made public", "sforum").'</strong></p><br />'."\n";

	# start of form
	$out.='<form action="'.SFPROFILE.'" method="post" name="upprofile" id="upprofile" enctype="multipart/form-data">'."\n";
	$out.= sf_create_nonce('forum-userform_profile');
	$out.='<input type="text" class="checkcontrol" size="45" name="username" id="username" value="'.$current_user->user_login.'" />'."\n";

	$out.= '<table width="99%" border="0" cellpadding="10" cellspacing="0"><tr>';
	$out.='<td width="50%" valign="top">'."\n";

	$out.='<fieldset><legend>'.__("Personal", "sforum").'</legend>';

	$out.='<p><b>'.__("Forum Display Name", "sforum").':</b></p><input type="text" class="sfcontrol" size="22" name="display_name" id="display_name" value="'.stripslashes($profile['display_name']).'" />'."\n";
	$out.='<p><b>'.__("First Name", "sforum").':</b></p><input type="text" class="sfcontrol" size="22" name="first_name" id="first_name" value="'.$profile["first_name"].'" />'."\n";
	$out.='<p><b>'.__("Last Name", "sforum").':</b></p><input type="text" class="sfcontrol" size="22" name="last_name" id="last_name" value="'.$profile["last_name"].'" />'."\n";
	$out.='<p><b>'.__("Email Address (Required)", "sforum").':</b></p><input type="text" class="sfcontrol" size="22" name="email" id="email" value="'.$current_user->user_email.'" />'."\n";
	$out.='<p><b>'.__("Website URL", "sforum").':</b></p><input type="text" class="sfcontrol" size="22" name="url" id="url" value="'.$current_user->user_url.'" />'."\n";
	if($ext)
	{
		$out.='<p><b>'.__("Location", "sforum").':</b></p><input type="text" class="sfcontrol" size="22" name="location" id="location" value="'.$profile["location"].'" />'."\n";
	}

	$out.='</fieldset>';
		$out.='</td><td width="50%" valign="top">';

		$out.='<fieldset><legend>'.__("User IDs", "sforum").'</legend>';
		$out.='<p><b>'.__("AIM", "sforum").':</b></p><input type="text" class="sfcontrol" size="16" name="aim" id="aim" value="'.$profile["aim"].'" />'."\n";
		$out.='<p><b>'.__("Yahoo IM", "sforum").':</b></p><input type="text" class="sfcontrol" size="16" name="yim" id="yim" value="'.$profile["yim"].'" />'."\n";
		$out.='<p><b>'.__("Jabber/Google Talk", "sforum").':</b></p><input type="text" class="sfcontrol" size="16" name="jabber" id="jabber" value="'.$profile["jabber"].'" />'."\n";
		if($ext)
		{
			$out.='<p><b>'.__("ICQ", "sforum").':</b></p><input type="text" class="sfcontrol" size="16" name="icq" id="icq" value="'.$profile["icq"].'" />'."\n";
			$out.='<p><b>'.__("MSN Messenger", "sforum").':</b></p><input type="text" class="sfcontrol" size="16" name="msn" id="msn" value="'.$profile["msn"].'" />'."\n";
			$out.='<p><b>'.__("Skype", "sforum").':</b></p><input type="text" class="sfcontrol" size="16" name="skype" id="skype" value="'.$profile["skype"].'" />'."\n";
		}
	$out.='</fieldset>';

	$out.='</td></tr>';
	$out.='<tr><td width="50%" valign="top">';

		$out.='<fieldset><legend>'.__("Biography", "sforum").'</legend>';
			$out.='<p><b>'.__("Short Biographical Note (Please keep it brief)", "sforum").':</b></p>'."\n";
			$out.='<textarea class="sfsmalltextarea" rows="3" cols="40" name="description" id="description" >'.$profile["description"].'</textarea>'."\n";
		$out.='</fieldset>';

	$out.='</td><td width="50%" valign="top">';

	$cfields = sf_get_sfmeta('custom_field');
	if ($cfields)
	{
		$out.='<fieldset><legend>'.__("Extra Information", "sforum").'</legend>';
			$out.= '<input type="hidden" name="cfcount" value="'.count($cfields).'" />';
			foreach ($cfields as $x => $cfield)
			{
				$value = get_usermeta($current_user->ID, 'sfcustomfield'.$x);
				if ($cfield['meta_value'] == 'checkbox')
				{
					$out.= '<p><input type="checkbox" name="cfield'.$x.'" id="sfcfield'.$x.'" ';
					if ($value == true)
					{
						$out.= "checked='checked' ";
					}
					$out.= '/>';
					$out.= '<label for="sfcfield'.$x.'">&nbsp;&nbsp;<b>'.$cfield['meta_key'].'</b></label></p><br />';
				} else {
					$out.= '<p><b><label for="cfield'.$x.'">'.$cfield['meta_key'].': </label></b></p>';
					if ($cfield['meta_value'] == 'input')
					{
						$out.= '<p><input type="text" class="sfcontrol" size="16" name="cfield'.$x.'" id="cfield'.$x.'" value="'.$value.'" /></p>';
					}
					if ($cfield['meta_value'] == 'textarea')
					{
						$out.='<p><textarea class="sfsmalltextarea" rows="4" cols="16" name="cfield'.$x.'" id="cfield'.$x.'" >'.$value.'</textarea></p>';
					}
				}
			}
		$out.='</fieldset>';
	}

	$out.='</td></tr>';
	$out.='<tr><td width="50%" valign="top">';

	$show_password_fields = apply_filters('show_password_fields', true);
	if($show_password_fields)
	{
		$out.='<fieldset><legend>'.__("Change Password", "sforum").'</legend>';
			$out.='<p><b>'.__("New Password", "sforum").':</b></p><input type="password" class="sfcontrol" size="20" name="newone1" id="newone1" autocomplete="off" value="" />'."\n";
			$out.='<p><b>'.__("Repeat New Password", "sforum").':</b></p><input type="password" class="sfcontrol" size="20" name="newone2" id="newone2" autocomplete="off" value="" />'."\n";
		$out.='</fieldset>';
	}
	$out.='</td><td width="50%" valign="top">';

	if($editorOK)
	{
		$out.='<fieldset><legend>'.__("Select Preferred Editor", "sforum").'</legend>';

			if($profile['myeditor'] == 1 ? $checked='checked="checked"' : $checked='');
			$out.='<input type="radio" id="sfradio-1" name="editor" value="1" '.$checked.'  />'."\n";
			$out.='<label class="sfradio" for="sfradio-1">&nbsp;&nbsp;<b>'.__("Rich Text (TinyMCE)", "sforum").'</b></label><br />'."\n";

			if($profile['myeditor'] == 2 ? $checked='checked="checked"' : $checked='');
			$out.='<input type="radio" id="sfradio-2" name="editor" value="2" '.$checked.'  />'."\n";
			$out.='<label class="sfradio" for="sfradio-2">&nbsp;&nbsp;<b>'.__("HTML (Quicktags)", "sforum").'</b></label><br />'."\n";

			if($profile['myeditor'] == 3 ? $checked='checked="checked"' : $checked='');
			$out.='<input type="radio" id="sfradio-3" name="editor" value="3" '.$checked.'  />'."\n";
			$out.='<label class="sfradio" for="sfradio-3">&nbsp;&nbsp;<b>'.__("BBCode (Quicktags)", "sforum").'</b></label><br />'."\n";

			if($profile['myeditor'] == 4 ? $checked='checked="checked"' : $checked='');
			$out.='<input type="radio" id="sfradio-4" name="editor" value="4" '.$checked.'  />'."\n";
			$out.='<label class="sfradio" for="sfradio-4">&nbsp;&nbsp;<b>'.__("Plain Textarea", "sforum").'</b></label><br />'."\n";

		$out.='</fieldset>';
	}

	$out.='</td></tr>';
	$out.='</table>';

	$out.= '<table width="99%" border="0" cellpadding="10" cellspacing="0">';

	if(($current_user->sfavatars) && (get_option('sfshowavatars')))
	{
		$out.='<tr><td>';
		$out.='<fieldset><legend>'.__("Avatar", "sforum").'</legend>';
			if(get_option('sfgravatar'))
			{
				$out.='<p>'.__("This site supports Gravatars but also allows for uploading an avatar if you do not have a gravatar and to use when gravatars are not available", "sforum").'</p>'."\n";
			}
			$maxsize = get_option('sfavatarsize');
			$out.='<p><strong>'.__("Upload Avatar", "sforum").':</strong></p><br />'."\n";
			$out.='<table><tr><td valign="top">'."\n";
			$out.= sf_render_avatar('user', $current_user->ID, $current_user->user_email, '')."\n";
			$out.='</td><td>'."\n";
			$out.='<p>'.sprintf(__("Files accepted: GIF, PNG, JPG and JPEG<br />Maximum size accepted: %s x %s pixels", "sforum"), '<b>'.$maxsize.'</b>', '<b>'.$maxsize.'</b>').'</p><br />'."\n";
			$out.='</td></tr></table>'."\n";

			$out.='<input type="file" class="sfcontrol" size="39" name="avatar" id="avatar" />'."\n";
		$out.='</fieldset>';
		$out.='</td></tr>';
	}

	if($current_user->sfusersig)
	{
		$out.='<tr><td>';
		$out.='<fieldset><legend>'.__("Signature", "sforum").'</legend>';
			if($current_user->sfsigimage)
			{
				$out.='<p><b>'.__("Signature", "sforum").':</b></p>';
			} else {
				$out.='<p><b>'.__("Signature (Images Not Allowed)", "sforum").':</b></p>';
			}
			$out.='<textarea class="sfsmalltextarea" rows="4" cols="40" name="signature" id="signature">'.$profile['signature'].'</textarea>'."\n";

			if($current_user->sfsigimage)
			{
				$out.='<p><b>'.__("Signature Image Location (url)", "sforum").': </b><br />';
				$sfsigimagesize = get_option('sfsigimagesize');
				$sigwidth = __('width - none', 'sforum').', ';
				$sigheight = __('height - none', 'sforum');
				if ($sfsigimagesize['sfsigwidth'] > 0) $sigwidth = __('width - ', 'sforum').$sfsigimagesize['sfsigwidth'].', ';
				if ($sfsigimagesize['sfsigheight'] > 0) $sigheight = __('height - ', 'sforum').$sfsigimagesize['sfsigheight'];
				$out.= __("Signature Image Size Limits (pixels): ", "sforum").'<br />'.$sigwidth.$sigheight.'</p>';
				$out.='<textarea class="sfsmalltextarea" rows="2" cols="40" name="sigimage" id="sigimage">'.$profile['sigimage'].'</textarea><br />'."\n";

				if($profile['sigimage'])
				{
					global $gis_error;
					set_error_handler('sf_gis_error');

					$size = getimagesize($profile['sigimage']);
					restore_error_handler();
					if ($size)
					{
						$out.= '<br/>';
						$sigwidth = '';
						$sigheight = '';
						$sigwarn = '<br />';
						if ($sfsigimagesize['sfsigwidth'] > 0 && $size[0] > $sfsigimagesize['sfsigwidth'])
						{
							$sigwidth = ' width="'.$sfsigimagesize['sfsigwidth'].'"';
							$sigwarn .= __('Warning - Sig Image Width exceeds forum limit!', 'sforum').'<br />';
						}
						if ($sfsigimagesize['sfsigheight'] > 0 && $size[1] > $sfsigimagesize['sfsigheight'])
						{
							$sigheight = ' height="'.$sfsigimagesize['sfsigheight'].'"';
							$sigwarn .= __('Warning - Sig Image Height exceeds forum limit!', 'sforum').'<br />';
						}
						$out.= "<img src='".$profile['sigimage']."' alt='' ".$sigwidth.$sigheight." />"."\n";
						$out.= $sigwarn;
					} else {
						if($gis_error) $out.= '<strong>'.$gis_error.'</strong>';
						$gis_error = '';
					}
				}
			}
		$out.='</fieldset>';
		$out.='</td></tr>';
	}

	$out.= '</table>'."\n";

	# post-profile hook
	if(function_exists('sf_hook_post_profile'))
	{
		$out .= sf_hook_post_profile();
	}

	$out.='<br /><hr />'."\n";

	$out.='<input type="submit" class="sfcontrol" name="subprofile" id="subprofile" value="'.__("Update Profile", "sforum").'" />'."\n";
	$out.='&nbsp;<input type="button" class="sfcontrol" name="button1" id="button1" value="'.__("Return to Forum", "sforum").'" onclick="sfjreDirect(\''.SFURL.'\');" />'."\n";
	$out.='<br />';
	$out.='<br /><hr />'."\n";
	$out.='</div><br />'."\n";

	$out.= '<table border="0"><tr><td>';

	if($current_user->sfsubscriptions)
	{
		$out.='<input type="submit" class="sfcontrol" name="mansubs" id="mansubs" value="'.sf_split_button_label(__("Manage Subscriptions", "sforum"), 0).'" />'."\n";
	}

	if ($current_user->sfusepm)
	{
		$out.='<input type="submit" class="sfcontrol" name="manbuddy" value="'.sf_split_button_label(__("Manage Buddy List", "sforum"), 0).'" />'."\n";
	}

	$out.='<input type="submit" class="sfcontrol" name="viewperms" value="'.sf_split_button_label(__("View Permissions", "sforum"), 0).'" />'."\n";

	$out.='</form>'."\n";

	$out.='</td><td>';
	$out.='<form class="sfalignright" action="'.SF_PLUGIN_URL.'/sf-search.php" method="post" name="search">'."\n";
	$out.='<input type="hidden" class="sfhiddeninput" name="userid" id="userid" value="'.$current_user->ID.'" />';
	$out.='<input type="hidden" class="sfhiddeninput" name="searchoption" id="searchoption" value="All Forums" />';
	$out.='<input type="submit" class="sfcontrol" name="membersearch" id="membersearch" value="'.sf_split_button_label(__("View Topics You Have Posted To", "sforum"), 2).'" />'."\n";
	$out.='<input type="submit" class="sfcontrol" name="memberstarted" id="memberstarted" value="'.sf_split_button_label(__("View Topics You Started", "sforum"), 1).'" />'."\n";
	$out.='</form>'."\n";
	$out.='</td></tr></table>';

	return $out;
}

function sf_render_subscription_form()
{
	global $current_user;

	$out = '';

	$out.= sf_render_queued_message();

	# header
	$out.='<br />';
	$out.='<div id="sfstandardform">'."\n";
	$out.='<div class="sfheading">';
	$out.='<table><tr>'."\n";
	$out.='<td class="sficoncell">'.sf_render_avatar('user', $current_user->ID, $current_user->user_email, '').'</td>';
	$out.='<td><p>'.sprintf(__("Current Topic Subscriptions for:%s", "sforum"), '<br />'.$current_user->user_login.' ('.stripslashes($current_user->display_name).')').'</p></td>'."\n";
	$out.='<td><img class="sfalignright" src="'. SFRESOURCES .'spacer.png" alt="" /><a class="sficon sfalignright" href="'.SFURL.'"><img src="'.SFRESOURCES.'goforum.png" alt="" title="'.__("Return to forum", "sforum").'" />'.sf_render_icons("Return to forum").'</a></td>'."\n";
	$out.='</tr></table>';
	$out.='</div>';

	$out.='<fieldset>'."\n";
	$out.='<legend>'.sprintf(__("Current Topic Subscriptions", "sforum"), $current_user->user_login.' ('.stripslashes($current_user->display_name).')').'</legend>'."\n";

	$out.='<form action="'.SFPROFILE.'" method="post" name="upsubs" id="upsubs">'."\n";

	$out.= sf_create_nonce('forum-userform_subs');

	$list = sf_get_member_item($current_user->ID, 'subscribe');
	if(empty($list))
	{
		$out.= '<p>'.__("You are currently subscribed to No Topics", "sforum").'</p><br />'."\n";
	} else {
		$out.= '<p>'.__("To Unsubscribe, uncheck topic", "sforum").'</p><br />'."\n";
		$list = explode('@', $list);
		foreach($list as $topicid)
		{
			if(!empty($topicid))
			{
				$out.= '<input name="topic[]" type="checkbox" id="sf-'.$topicid.'" value="'.$topicid.'" checked="checked" />'."\n";
				$out.= '<label for="sf-'.$topicid.'">&nbsp;&nbsp;&nbsp;'.sf_get_topic_name(sf_get_topic_slug($topicid)).'</label><br />'."\n";
			}
		}
	}
	$out.='</fieldset>'."\n";

	$out.='<br /><hr /><input type="submit" class="sfcontrol" name="uptopsubs" value="'.__("Update Subscriptions", "sforum").'" />'."\n";
	$out.='&nbsp;<input type="button" class="sfcontrol" name="button1" value="'.__("Return to Profile", "sforum").'" onclick="sfjreDirect(\''.SFPROFILE.'\');" />'."\n";
	$out.='&nbsp;<input type="button" class="sfcontrol" name="button3" value="'.__("Return to Forum", "sforum").'" onclick="sfjreDirect(\''.SFURL.'\');" />'."\n";

	$out.='</form>'."\n";

	$out.='</div><br />'."\n";

	return $out;
}

?>