<?php
/*
Simple:Press Forum
User Specials
$LastChangedDate: 2009-02-28 16:33:30 +0000 (Sat, 28 Feb 2009) $
$Rev: 1483 $
*/

require_once("../../sf-config.php");
require_once('../../forum/sf-primitives.php');

# Check Whether User Can Manage User Groups
if(!sf_current_user_can('SPF Manage Users')) {
	echo (__('Access Denied', "sforum"));
	die();
}

require_once("../sf-adminsupport.php");

define('SFADMINIMAGES', SF_PLUGIN_URL . '/admin/images/');

global $wpdb, $sfglobals;

$action = sf_syscheckstr($_GET['action']);
if ($action == 'display-groups')
{
	echo "<select style='width:200px' multiple size='10' class='sfacontrol' id='grouplist' name='watchessubsgroups[]'>";
	$groups = sf_get_groups_all();
	if ($groups)
	{
		foreach ($groups as $group)
		{
			echo '<option value="'.$group->group_id.'">'.stripslashes($group->group_name).'</option>';
		}
	}
	echo '</select>';
	echo '<br />';
	echo '<table class="sfabuttontable">';
	echo '<td class="sfabuttonitem sfabgcancel" align="right">';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'select-group\');">';
	echo __("Close", "sforum");
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
}

if ($action == 'display-forums')
{
	echo "<select style='width:200px' multiple size='10' class='sfacontrol' id='forumlist' name='watchessubsforums[]'>";
	$forums = sfa_get_forums_all();
	if ($forums)
	{
		$thisgroup = 0;
		foreach ($forums as $forum)
		{
			if($thisgroup != $forum->group_id)
			{
				if($thisgroup != 0) echo '</optgroup>'."\n";
				echo '<optgroup label="'.stripslashes($forum->group_name).'">'."\n";
				$thisgroup = $forum->group_id;
			}
			echo '<option value="'.$forum->forum_id.'">'.stripslashes($forum->forum_name).'</option>';
		}
	}
	echo '</optgroup></select>';
	echo '<br />';
	echo '<table class="sfabuttontable">';
	echo '<td class="sfabuttonitem sfabgcancel" align="right">';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'select-forum\');">';
	echo __("Close", "sforum");
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
}

if ($action == 'del_inbox' || $action == 'del_sentbox' || $action == 'del_pms')
{
	$uid = sf_syscheckint($_GET['id']);
	$name = stripslashes(attribute_escape(sf_syscheckstr($_GET['name'])));
	$pm = sf_syscheckint($_GET['pm']);
	$inbox = sf_syscheckint($_GET['inbox']);
	$unread = sf_syscheckint($_GET['unread']);
	$sentbox = sf_syscheckint($_GET['sentbox']);
	$eid = sf_syscheckint($_GET['eid']);

	if ($action == 'del_inbox')
	{
		$wpdb->query("UPDATE ".SFMESSAGES." SET inbox=0, message_status=1 WHERE to_id=".$uid." AND inbox=1");
		$wpdb->query("DELETE FROM ".SFMESSAGES." WHERE to_id=".$uid." AND (inbox=0 AND sentbox=0)");
		$inbox = 0;
		$unread = 0;
	}

	if ($action == 'del_sentbox')
	{
		$wpdb->query("UPDATE ".SFMESSAGES." SET sentbox=0 WHERE from_id=".$uid." AND sentbox=1");
		$wpdb->query("DELETE FROM ".SFMESSAGES." WHERE from_id=".$uid." AND (inbox=0 AND sentbox=0)");
		$sentbox = 0;
	}

	if ($action == 'del_pms')
	{
		$wpdb->query("DELETE FROM ".SFMESSAGES." WHERE to_id=".$uid." OR from_id=".$uid);
		$inbox = 0;
		$sentbox = 0;
		$unread = 0;
	}

	$total = $inbox + $sentbox;
	if ($total > 0)
	{
?>
	<table width="100%" cellspacing="0">
		<tr>
			<td align="center" width="90" style="padding:5px 0px;"><?php echo $uid; ?></td>
			<td align="left" style="padding:2px 0px;"><?php echo $name; ?></td>
			<td align="center" width="20" style="padding:5px 0px;"></td>
			<td align="center" width="50" style="padding:5px 0px;"><?php if ($pm) echo __("Yes", "sforum"); else echo __("No", "sforum"); ?></td>
			<td align="center" width="20" style="padding:5px 0px;"></td>
			<td align="center" width="90" style="padding:5px 0px;"><?php echo $total; ?></td>
			<td align="center" width="90" style="padding:5px 0px;"><?php echo $inbox; ?></td>
			<td align="center" width="90" style="padding:5px 0px;"><?php echo $unread; ?></td>
			<td align="center" width="90" style="padding:5px 0px;"><?php echo $sentbox; ?></td>
			<td align="center" width="20" style="padding:5px 0px;"></td>
			<td align="center" width="80" style="padding:5px 0px;">
				<?php $site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_inbox&id=".$uid."&name=".$name."&pm=".$pm."&inbox=".$inbox."&unread=".$unread."&sentbox=".$sentbox."&eid=".$eid; ?>
				<?php $gif = SFADMINURL."images/working.gif"; ?>
				<?php if ($sentbox == 0) $fade = 1; else $fade = 0; ?>
				<img onclick="sfjDelPMs('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $fade; ?>', pmdata<?php echo $eid; ?>');" src="<?php echo SFADMINIMAGES; ?>inbox_pm.png" title="<?php _e("Delete Inbox PMs", "sforum"); ?>"/>&nbsp;
				<?php $site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_sentbox&id=".$uid."&name=".$name."&pm=".$pm."&inbox=".$inbox."&unread=".$unread."&sentbox=".$sentbox."&eid=".$eid; ?>
				<?php if ($inbox == 0) $fade = 1; else $fade = 0; ?>
				<img onclick="sfjDelPMs('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $fade; ?>', pmdata<?php echo $eid; ?>');" src="<?php echo SFADMINIMAGES; ?>sentbox_pm.png" title="<?php _e("Delete Sentbox PMs", "sforum"); ?>"/>&nbsp;
				<?php $site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_pms&id=".$uid."&name=".$name."&pm=".$pm."&inbox=".$inbox."&unread=".$unread."&sentbox=".$sentbox."&eid=".$eid; ?>
				<img onclick="sfjDelPMs('<?php echo $site; ?>', '<?php echo $gif; ?>', '1', pmdata<?php echo $eid; ?>');" src="<?php echo SFADMINIMAGES; ?>all_pm.png" title="<?php _e("Delete All PMs", "sforum"); ?>"/>
			</td>
			<td align="center" width="20" style="padding:5px 0px;"></td>
		</tr>
	</table>
<?php
	}
}

if ($action == 'del_watches' || $action == 'del_subs')
{
	$tid = sf_syscheckint($_GET['id']);
	$subs = sf_syscheckint($_GET['subs']);
	$watches = sf_syscheckint($_GET['watches']);
	$group = sf_syscheckstr($_GET['group']);
	$forum = sf_syscheckstr($_GET['forum']);
	$topic = sf_syscheckstr($_GET['topic']);
	$slug = sf_syscheckstr($_GET['slug']);
	$eid = sf_syscheckint($_GET['eid']);

	if ($action == 'del_watches')
	{
		sfa_remove_watches($tid);
	}

	if ($action == 'del_subs')
	{
		sfa_remove_subs($tid);
	}

	if ($subs || $watches)
	{
		echo '<table width="100%" cellspacing="0">';
		echo '<tr>';
		echo '<td width="175" style="padding:4px 0 4px 5px;">'.stripslashes($group).'</td>';
		echo '<td width="175" style="padding:4px 0 4px 5px;">'.stripslashes($forum).'</td>';
		$url = sf_build_url($forum, $slug, 1, 0);
		echo '<td width="175" style="padding:4px 0 4px 5px;"><a href="'.$url.'">'.stripslashes($topic).'</a></td>';
		echo '<td style="padding:4px 0 4px 5px;">';
		if ($subs) # subs
		{
			$query = "SELECT topic_subs FROM ".SFTOPICS." WHERE topic_id=$tid";
			$record = $wpdb->get_var($query);
			if ($record)
			{
				$first = true;
				$list = explode('@', $record);
				for ($x=0; $x<count($list); $x++)
				{
					$user = sf_get_member_row($list[$x]);
					if ($first)
					{
						echo __("Subscriptions", "sforum").":<br />";
						echo $user['display_name'];
						$first = false;
					} else {
						echo ', '.$user['display_name'];
					}
				}
			}
		}
		if ($watches) # watches
		{
			$query = "SELECT topic_watches FROM ".SFTOPICS." WHERE topic_id=$tid";
			$record = $wpdb->get_var($query);
			if ($record)
			{
				$first = true;
				$list = explode('@', $record);
				for ($x=0; $x<count($list); $x++)
				{
					$user = sf_get_member_row($list[$x]);
					if ($first)
					{
						echo "Watches:<br />";
						echo $user['display_name'];
						$first = false;
					} else {
						echo ', '.$user['display_name'];
					}
				}
			}
		}
		echo '</td>';
		echo '<td width="30" align="center" style="padding:4px 0 4px 5px;">';
		$gif = SFADMINURL."images/working.gif";
		if ($subs)
		{
			$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_subs&id=".$tid."&watches=0&subs=0&eid=".$eid;
			?>
			<img onclick="sfjDelWatchesSubs('<?php echo $site; ?>', '<?php echo $gif; ?>', '1', 'subswatches<?php echo $index; ?>');" src="<?php echo SFADMINIMAGES; ?>del_sub.png" title="<?php _e("Delete Subscriptions", "sforum"); ?>"/>&nbsp;
			<?php
		}
		echo '</td>';
		echo '<td width="30" align="center" style="padding:4px 0 4px 5px;">';
		if ($watches)
		{
			$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_watches&id=".$tid."&watches=0&subs=0&eid=".$eid;
			?>
			<img onclick="sfjDelWatchesSubs('<?php echo $site; ?>', '<?php echo $gif; ?>', '1', 'subswatches<?php echo $index; ?>');" src="<?php echo SFADMINIMAGES; ?>del_watch.png" title="<?php _e("Delete Watches", "sforum"); ?>"/>&nbsp;
			<?php
		}
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
}

if ($action == 'show_profile')
{
	$uid = sf_syscheckint($_GET['id']);
	$user = new WP_User($uid);

	# grab editor status
	$editorOK = false;

	if ($sfglobals['editor']['sfusereditor']) $editorOK = true;
	$profile=array();
	$profile['first_name'] = attribute_escape($user->first_name);
	$profile['last_name'] = attribute_escape($user->last_name);
	$profile['display_name'] = $user->display_name;
	$profile['signature'] = attribute_escape(sf_get_member_item($user->ID, 'signature'));
	$profile['sigimage'] = attribute_escape(sf_get_member_item($user->ID, 'sigimage'));
	if ($editorOK) $profile['myeditor'] = sf_get_member_item($user->ID, 'editor');
	$ext = get_option('sfextprofile');
	if ($ext)
	{
		$profile['aim'] = attribute_escape($user->aim);
		$profile['yim'] = attribute_escape($user->yim);
		$profile['jabber'] = attribute_escape($user->jabber);
		$profile['msn'] = attribute_escape($user->msn);
		$profile['skype'] = attribute_escape($user->skype);
		$profile['icq'] = attribute_escape($user->icq);
		$profile['description'] = wp_specialchars(stripslashes($user->description));
		$profile['location'] = attribute_escape($user->location);
	}

	$out = '';
	$out.= sprintf(__("Profile Information for: <strong> %s </strong>", "sforum"), $user->user_login.' ('.stripslashes($profile['display_name']).')');
	$out.= '<table width="99%" border="0">';
	$out.= '<tr><td width="50%" valign="top" style="padding:0;">';

	$out.= '<p><strong>'.__("Login Name", "sforum").': </strong>'.$user->user_login.'</p>';
	$out.= '<p><strong>'.__("Display Name", "sforum").': </strong>'.stripslashes($profile['display_name']).'</p>';
	$out.= '<p><strong>'.__("Email Address", "sforum").': </strong>'.$user->user_email.'</p>';
	$out.= '<p><strong>'.__("Website URL", "sforum").': </strong>'.$user->user_url.'</p>';
	if ($ext)
	{
		$out.= '<p><strong>'.__("Location", "sforum").':</strong>'.$profile["location"].'</p>';
	}

	$out.= '<p><strong>'.__("User Avatar", "sforum").': </strong></p>';
	if (sf_user_can($user->ID, 'Can upload avatars') && (get_option('sfshowavatars')))
	{
		$icon = 'user';
		if (sf_is_forum_admin($user->ID)) $icon='admin';
		$out.= '<div style="margin-left:15px">'.sf_render_avatar($icon, $user->ID, $user->user_email, '').'</div>';
	} else {
		$out.='<p>'.__("Avatars Not Permitted.", "sforum").'</p>';
	}
	if ($editorOK)
	{
		$out.= '<p><strong>'.__("Editor", "sforum").': </strong>';
		if ($profile['myeditor'] == 1) $out.= __("Rich Text (TinyMCE)", "sforum");
		if ($profile['myeditor'] == 2) $out.= __("HTML (Quicktags)", "sforum");
		if ($profile['myeditor'] == 3) $out.= __("BBCode (Quicktags)", "sforum");
		if ($profile['myeditor'] == 4) $out.= __("Plain Textarea", "sforum");
		$out.= '</p>';
	}
	$out.= '</td>'."\n";

	$out.= '<td width="50%" valign="top" style="padding:0;">'."\n";
	$out.= '<p><strong>'.__("First Name", "sforum").': </strong>'.$profile["first_name"].'</p>';
	$out.= '<p><strong>'.__("Last Name", "sforum").': </strong>'.$profile["last_name"].'</p>';

	if ($ext)
	{
		$out.= '<p><strong>'.__("AIM", "sforum").': </strong>'.$profile["aim"].'</p>';
		$out.= '<p><strong>'.__("Yahoo IM", "sforum").': </strong>'.$profile["yim"].'<p>';
		$out.= '<p><strong>'.__("Jabber/Google Talk", "sforum").': </strong>'.$profile["jabber"].'</p>';
		$out.= '<p><strong>'.__("ICQ", "sforum").':</p>'.$profile["icq"].'</p>';
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
				$out.= '<p><input type="checkbox" name="cfield'.$x.'" id="cfield'.$x.'" ';
				if ($value == true)
				{
					$out.= "checked='checked' ";
				}
				$out.= 'disabled="disabled" />';
				$out.= '<label for="cfield'.$x.'">&nbsp;&nbsp;<strong>'.$cfield['meta_key'].'</strong></label></p>';
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

	if ($ext)
	{
		$bio = $profile['description'];
		$out.='<p><strong>'.__("Biographical Note", "sforum").': </strong></p>';
		$out.='<p class="sfacontrol" style="margin-left:15px !important;">'.$bio.'&nbsp;</p>';
	}

	if (sf_user_can($user->ID, 'Can use signatures'))
	{
		if(sf_user_can($user->ID, 'Can use images in signatures'))
		{
			$out.='<p><strong>'.__("Signature", "sforum").': </strong></p>';
		} else {
			$out.='<p><strong>'.__("Signature Images Not Allowed.", "sforum").': </strong></p>';
		}

		$sig = $profile['signature'];
		$out.='<p class="sfacontrol" style="margin-left:15px !important;">'.$sig.'&nbsp;</p>';

		if (sf_user_can($user->ID, 'Can use images in signatures'))
		{
			$out.='<p><strong>'.__("Signature Image Location", "sforum").': </strong></p>';
			$sigimg = $profile['sigimage'];
			$out.='<p class="sfacontrol" style="margin-left:15px !important;">'.$sigimg.'&nbsp;</p>';
		}
	} else {
		$out.='<p><strong>'.__("Signature", "sforum").': </strong></p>';
		$out.='<p class="sfacontrol" style="margin-left:15px !important;">'.__("Signatures Not Permitted.", "sforum").'</p>';
	}

	$out.= '<br />';
	$out.= '<table class="sfabuttontable" style="margin-left:15px;">';
	$out.= '<td class="sfabuttonitem sfabgcancel" align="right">';
	$out.= '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'adminmemberprofile'.$user->ID.'\');">';
	$out.= __("Close", "sforum");
	$out.= '</a>';
	$out.= '</td>';
	$out.= '</tr>';
	$out.= '</table>';
	echo $out;
}

if ($action == 'spam_reg')
{
	global $wpdb;

	define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminusers.php');
?>
	<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfspamkill">
	<?php echo(sf_create_nonce('forum-adminform_spamkill')); ?>

	<table align="center" class="sfsubtable" cellpadding="0" cellspacing="0">
		<tr>
			<th align="center" width="90" scope="col" style="padding:5px 0px;"><?php _e("User ID", "sforum"); ?></th>
			<th align="left" style="padding:2px 0px;"><?php _e("User Name", "sforum"); ?></th>
			<th align="center"><?php _e("Delete", "sforum"); ?></th>
		</tr>
<?php

	$numspam = 0;

	# first out select users registered more than X days ago
	$registrations = $wpdb->get_results("SELECT ID, user_registered FROM ".SFUSERS." WHERE user_registered < DATE_SUB(CURDATE(), INTERVAL 5 DAY);");
	if($registrations)
	{
		# second select all users who have never posted to the forum
		$badusers = $wpdb->get_results("SELECT user_id, display_name FROM ".SFMEMBERS." WHERE posts = 0 ORDER BY display_name;");
		if($badusers)
		{
			$candelete = false;
			$found = false;

			foreach($badusers as $baduser)
			{
				# OK so they have never posted but are they in the old registrations list?
				foreach($registrations as $registration)
				{
					if($baduser->user_id == $registration->ID)
					{
						$found = true;
						$candelete = true;
					}
				}
				# if they were then have they ever authored a post?
				if($found)
				{
					$found = $wpdb->get_results("SELECT ID FROM ".$wpdb->prefix."posts WHERE post_author = ".$baduser->user_id);
					if($found)
					{
						$candelete = false;
					} else {
						# if no - what about left a comment?
						$found = $wpdb->get_results("SELECT comment_id FROM ".$wpdb->prefix."comments WHERE user_id = ".$baduser->user_id);
						if($found)
						{
							$candelete = false;
						}
					}
				}
				# so? can we delete them?
				if($candelete)
				{
					# do NOT remove an admin that does not post
					if (!sf_is_forum_admin($baduser->user_id))
					{
?>
						<tr>
							<td align="center"><?php echo($baduser->user_id); ?></td>
							<td><?php echo($baduser->display_name); ?></td>
							<td align="center">
							<label for="sfkill-<?php echo($baduser->user_id); ?>"></label>
							<input type="checkbox" name="kill[<?php echo($baduser->user_id); ?>]" id="sfkill-<?php echo($baduser->user_id); ?>" checked="checked" />
							</td>
						</tr>
<?php
						$numspam++;
					}
				}
			}
		}
	}
	echo '</table>';
	echo('<p><small><strong>'.$numspam.__(" registered users eligable for removal", "sforum").'</strong></small></p>');

	if($numspam != 0)
	{
?>
		<br />
		<input type="submit" class="sfacontrol" name="killSpam" value="<?php _e("Remove Checked Users", "sforum") ?>" />
<?php } ?>
		<input type="button" class="sfacontrol" name="cancel" value="<?php _e("Cancel", "sforum") ?>" onclick="hs.close(this);" />
	</form>
<?php
}

die();
?>