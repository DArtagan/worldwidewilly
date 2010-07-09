<?php
/*
Simple:Press Forum
Ahah call for View Member Profile
$LastChangedDate: 2009-01-16 20:14:58 +0000 (Fri, 16 Jan 2009) $
$Rev: 1230 $
*/

require_once("../../sf-config.php");

sf_load_foundation();

global $current_user;
sf_initialise_globals();

if ($current_user->sfprofiles)
{
	if (isset($_GET['u'])) $userid = sf_syscheckint($_GET['u']);
	if (isset($_GET['ug'])) $ugid = sf_syscheckint($_GET['ug']);
	if (isset($_GET['buddy']))$buddy = sf_syscheckstr($_GET['buddy']);
	if (isset($_GET['action'])) $action = sf_syscheckstr($_GET['action']);
	if (!empty($buddy))
	{
		echo sf_delete_buddy($userid, $buddy);
	} else if (!empty($action) && $action == 'memberlist') {
		echo sf_member_profile($userid, $ugid);
	} else {
		echo sf_view_profile($userid);
	}
} else {
	echo (__('Access Denied', "sforum"));
}

die();

function sf_view_profile($userid)
{
	global $wpdb, $current_user;

	$profile=array();

	$out = '';

	$userinfo = $wpdb->get_row("SELECT * FROM ".SFUSERS." WHERE ID=".$userid);
	$display_name = sf_get_member_item($userid, 'display_name');
	$profile['url'] = $userinfo->user_url;
	$email = $userinfo->user_email;
	$profile['registered']=mysql2date(SFDATES, $userinfo->user_registered);

	$profile['first_name']=attribute_escape(get_usermeta($userid, 'first_name'));
	$profile['last_name']=attribute_escape(get_usermeta($userid, 'last_name'));
	$profile['location']=attribute_escape(get_usermeta($userid, 'location'));
	$profile['aim']=attribute_escape(get_usermeta($userid, 'aim'));
	$profile['yim']=attribute_escape(get_usermeta($userid, 'yim'));
	$profile['jabber']=attribute_escape(get_usermeta($userid, 'jabber'));
	$profile['icq']=attribute_escape(get_usermeta($userid, 'icq'));
	$profile['msn']=attribute_escape(get_usermeta($userid, 'msn'));
	$profile['skype']=attribute_escape(get_usermeta($userid, 'skype'));
	$profile['description']=attribute_escape(stripslashes(get_usermeta($userid, 'description')));

	$out.=sprintf(__("Profile Information for: <strong> %s </strong>", "sforum"), stripslashes($display_name))."<br />\n";

	if(get_option('sfshowavatars'))
	{
		$out.= '<br />'.sf_render_avatar('user', $userid, $email, '').'<br />'."\n";
	}
	$out.='<table class="sfshowprofile" cellspacing="0" cellpadding="0" border="0"><tr>'."\n";

	$out.='<tr><td width="30%">'.__("First Name", "sforum").':</td><td>'.stripslashes($profile['first_name']).'</td></tr>'."\n";
	$out.='<tr><td>'.__("Last Name", "sforum").':</td><td>'.stripslashes($profile['last_name']).'</td></tr>'."\n";

	if(!$current_user->forumadmin)
	{
		$out.='<tr><td>'.__("Member Since", "sforum").':</td><td>'.$profile['registered'].'</td></tr>'."\n";
	}

	$out.='<tr><td>'.__("Location", "sforum").':</td><td>'.stripslashes($profile['location']).'</td></tr>'."\n";
	$out.='<tr><td>'.__("Website URL", "sforum").':</td><td>'.$profile['url'].'</td></tr>'."\n";

	$out.='<tr><td valign="top">'.__("Bio", "sforum").':</td><td>'.stripslashes($profile['description']).'</td></tr>'."\n";

	$out.='<tr><td>'.__("AIM", "sforum").':</td><td>'.stripslashes($profile['aim']).'</td></tr>'."\n";
	$out.='<tr><td>'.__("Yahoo IM", "sforum").':</td><td>'.stripslashes($profile['yim']).'</td></tr>'."\n";
	$out.='<tr><td>'.__("Jabber/Google Talk", "sforum").':</td><td>'.stripslashes($profile['jabber']).'</td></tr>'."\n";
	$out.='<tr><td>'.__("ICQ", "sforum").':</td><td>'.stripslashes($profile['icq']).'</td></tr>'."\n";
	$out.='<tr><td>'.__("MSN Messenger", "sforum").':</td><td>'.stripslashes($profile['msn']).'</td></tr>'."\n";
	$out.='<tr><td>'.__("Skype", "sforum").':</td><td>'.stripslashes($profile['skype']).'</td></tr>'."\n";

	$cfields = sf_get_sfmeta('custom_field');
	if ($cfields)
	{
		foreach ($cfields as $x => $cfield)
		{
			$value = get_usermeta($userid, 'sfcustomfield'.$x);
			if ($cfield['meta_value'] == 'checkbox')
			{
				$out.= '<tr><td colspan="2"<input type="checkbox" name="cfield'.$x.'" id="sfcfield'.$x.'" ';
				if ($value == true)
				{
					$out.= "checked='checked' ";
				}
				$out.= 'disabled="disabled" />';
				$out.= '<label for="sfcfield'.$x.'">&nbsp;&nbsp;'.$cfield['meta_key'].'</strong></label></td></tr>';
			} else {
				if ($cfield['meta_value'] == 'input')
				{
					$out.= '<tr><td><label for="cfield'.$x.'">'.$cfield['meta_key'].'</label>: </td>';
					$out.= '<td>'.$value.'</td></tr>';
				}
				if ($cfield['meta_value'] == 'textarea')
				{
					$out.= '<tr><td><label for="cfield'.$x.'">'.$cfield['meta_key'].'</label></td>';
					$out.='<td>'.$value.'</td></tr>';
				}
			}
		}
	}

	$out.='</table>'."\n";

	$out.='<br />'."\n";

	# Display members post topics
	$out.='<form action="'.SF_PLUGIN_URL.'/sf-search.php" method="post" name="search">'."\n";

	$out.='<input type="hidden" name="userid" value="'.$userid.'" />'."\n";
	$out.='<input type="hidden" class="sfhiddeninput" name="searchoption" id="searchoption" value="All Forums" />';

	$out.='&nbsp;&nbsp;&nbsp<input type="submit" class="sfcontrol" name="membersearch" value="'.sf_split_button_label(sprintf(__("List Topics %s Has Posted To", "sforum"), stripslashes($display_name)), 2).'" />'."\n";
	$out.='&nbsp;&nbsp;&nbsp<input type="submit" class="sfcontrol" name="memberstarted" value="'.sf_split_button_label(sprintf(__("List Topics %s Has Started", "sforum"), stripslashes($display_name)), 2).'" />'."\n";
	$out.='</form>'."\n";

	# Add to Buddy list of allowed
	if($current_user->sfusepm && sf_is_pm_user($userid) && $current_user->ID != $userid)
	{
		if(sf_is_buddy($userid))
		{
			$out.='<form action="'.$_SERVER['HTTP_REFERER'].'" method="post" name="delbuddy">'."\n";
			$out.='<input type="hidden" name="oldbuddy" value="'.$userid.'" />'."\n";
			$out.='&nbsp;&nbsp;&nbsp<input type="submit" class="sfcontrol" name="delnewbuddy" value="'.sf_split_button_label(sprintf(__("Remove %s From Buddy List", "sforum"), stripslashes($display_name)), 1).'" />'."\n";
			$out.='</form>'."\n";
		} else {
			$out.='<form action="'.$_SERVER['HTTP_REFERER'].'" method="post" name="addbuddy">'."\n";
			$out.='<input type="hidden" name="newbuddy" value="'.$userid.'" />'."\n";
			$out.='&nbsp;&nbsp;&nbsp<input type="submit" class="sfcontrol" name="addnewbuddy" value="'.sf_split_button_label(sprintf(__("Add %s To Buddy List", "sforum"), stripslashes($display_name)), 2).'" />'."\n";
			$out.='</form>'."\n";
		}
	}

	return $out;
}

function sf_delete_buddy($uid, $bid)
{
	$list = sf_get_member_item($uid, 'buddies');
	if (!empty($list))
	{
		$newlist = array();
		foreach($list as $user)
		{
			if ($user != $bid)
			{
				$newlist[] = $user;
			}
		}
		sf_update_member_item($uid, 'buddies', $newlist);
	}
}

function sf_member_profile($uid, $ugid)
{
	global $sfglobals, $current_user;

	$user = new WP_User($uid);

	$editorOK = false;
	if ($sfglobals['editor']['sfusereditor']) $editorOK = true;

	$profile=array();
	$profile['display_name'] = $user->display_name;

	$ext = get_option('sfextprofile');

	if (isset($user->aim)) $profile['aim'] = attribute_escape($user->aim); else $profile['aim'] = '';
	if (isset($user->yim)) $profile['yim'] = attribute_escape($user->yim); else $profile['yim'] = '';
	if (isset($user->jabber)) $profile['jabber'] = attribute_escape($user->jabber); else $profile['jabber']= '' ;
	if (isset($user->description)) $profile['description'] = wp_specialchars(stripslashes($user->description)); else $profile['description'] = '';

	if ($ext)
	{
		if (isset($user->msn)) $profile['msn'] = attribute_escape($user->msn); else $profile['msn'] = '';
		if (isset($user->skype)) $profile['skype'] = attribute_escape($user->skype); else $profile['skype'] = '';
		if (isset($user->icq)) $profile['icq'] = attribute_escape($user->icq); else $profile['icq'] = '';
		$profile['location'] = attribute_escape($user->location);
	}

	$out = '';
	$out.= sprintf(__("Profile Information for: <strong> %s </strong>", "sforum"), stripslashes($profile['display_name']));
	$out.= '<table class="sfshowprofile" width="99%" border="0">';
	$out.= '<tr><td width="50%" valign="top" style="padding:0;background:transparent;">';

	if (sf_user_can($user->ID, 'Can upload avatars') && (get_option('sfshowavatars')))
	{
		$icon = 'user';
		if (sf_is_forum_admin($user->ID)) $icon = 'admin';
		$out.= '<div style="margin-left:15px"><br />'.sf_render_avatar($icon, $user->ID, $user->user_email, '').'<br /></div>';
	}

	$out.= '<p><strong>'.__("Name", "sforum").': </strong>'.stripslashes($profile['display_name']).'</p>';
	$out.= '<p><strong>'.__("Website URL", "sforum").': </strong>'.$user->user_url.'</p>';
	if ($ext)
	{
		$out.= '<p><strong>'.__("Location", "sforum").':</strong>'.$profile["location"].'</p>';
	}
	$out.= '</td>'."\n";

	$out.= '<td width="50%" valign="top" style="padding:0;background:transparent;"><br />'."\n";

	$out.= '<p><strong>'.__("AIM", "sforum").': </strong>'.$profile["aim"].'</p>';
	$out.= '<p><strong>'.__("Yahoo IM", "sforum").': </strong>'.$profile["yim"].'<p>';
	$out.= '<p><strong>'.__("Jabber/Google Talk", "sforum").': </strong>'.$profile["jabber"].'</p>';

	if ($ext)
	{
		$out.= '<p><strong>'.__("ICQ", "sforum").': </strong>'.$profile["icq"].'</p>';
		$out.= '<p><strong>'.__("MSN Messenger", "sforum").': </strong>'.$profile["msn"].'</p>';
		$out.= '<p><strong>'.__("Skype", "sforum").': </strong>'.$profile["skype"].'</p>';
	}

	$out.= '</td></tr></table>'."\n";

	$cfields = sf_get_sfmeta('custom_field');
	if ($cfields)
	{
		$out.= '<p><strong>'.__("Custom Fields", "sforum").'</strong></p>';
		foreach ($cfields as $x => $cfield)
		{
			$value = get_usermeta($user->ID, 'sfcustomfield'.$x);
			if ($cfield['meta_value'] == 'checkbox')
			{
				$out.= '<p><input type="checkbox" name="cfield'.$x.'" id="sfcfield'.$x.'" ';
				if ($value == true)
				{
					$out.= "checked='checked' ";
				}
				$out.= 'disabled="disabled" />';
				$out.= '<label for="sfcfield'.$x.'">&nbsp;&nbsp;<strong>'.$cfield['meta_key'].'</strong></label></p>';
			} else {
				if ($cfield['meta_value'] == 'input')
				{
					$out.= '<p><strong><label for="cfield'.$x.'">'.$cfield['meta_key'].'</label></strong>: ';
					$out.= $value.'</p>';
				}
				if ($cfield['meta_value'] == 'textarea')
				{
					$out.= '<p><strong><label for="cfield'.$x.'">'.$cfield['meta_key'].'</label></strong></p>';
					$out.='<p class="sfacontrol" style="margin-left:15px !important;">'.$value.'&nbsp;</p>';
				}
			}
		}
	}

	$bio = $profile['description'];
	$out.='<p><strong>'.__("Biographical Note", "sforum").': </strong></p>';
	$out.='<p class="sfacontrol" style="margin-left:15px !important;">'.$bio.'&nbsp;</p>';

	$out.= '<br />';
	$out.= '&nbsp;<input type="button" class="sfcontrol" name="cancel" value="'.__("Close Profile", "sforum").'" onclick="sfjtoggleLayer(\'memberprofile-'.$ugid.'-'.$user->ID.'\');" />';
	$out.= '<br /><br />';
	echo $out;
}
?>