<?php
/*
Simple:Press Forum
PM Rendering Routines
$LastChangedDate: 2009-04-19 01:07:29 +0100 (Sun, 19 Apr 2009) $
$Rev: 1740 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# ------------------------------------------------------------------
# sf_render_pm_table()
#
# Main rendering loop of PM table
#	$view:			Set to 'inbox' or 'sentbox'
#	$threads		The PM data array
#	$messagecount	Total number of all messages in this view
#	$cansendpm		True/False - can current user send PMs
# ------------------------------------------------------------------
function sf_render_pm_table($view, $threads, $messagecount, $cansendpm)
{
	$out = '';

	# If no messages - we can leave now
	if(!$threads)
	{
		$out.= '<div class="sfmessagestrip">'.sprintf(__("Your %s is empty", "sforum"), sf_localise_boxname($view)).'</div>'."\n";
		return $out;
	}

	# Begin main outer table of threads
	$out.= '<table class="sfforumtable" id="sfmainpmtable">'."\n";
	$out.= sf_render_pm_outer_header_row($view, $messagecount);

	$threadindex = 1;

	foreach($threads as $thread)
	{
			$out.= sf_render_pm_thread($threadindex, $view, $thread, $cansendpm);
			$threadindex = $threadindex + 2;
	}

	# Close inbox table
	$out.= '</table>';

	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_outer_header_row()
#
# Man headings in the 'thread' (outer) table
#	$view:			Set to 'inbox' or 'sentbox'
#	$messagecount	Total number of all messages in this view
# ------------------------------------------------------------------
function sf_render_pm_outer_header_row($view, $messagecount)
{
	global $current_user;

	$delete='';
	if($messagecount > 0)
	{
		$pmitem = sf_localise_boxname($view);
		$msg = sprintf(__("Are you sure you want to empty your %s?", "sforum"), $pmitem);
		$site=SF_PLUGIN_URL."/messaging/ahah/sf-ahahpmexplode.php?pmdelall=".$view."&amp;owner=".$current_user->ID;
		$delete = '<input type="button" class="sfxcontrol" name="deleteall" id="deleteall" tabindex="0" value="'.__("Delete All", "sforum").'" onclick="javascript: if(confirm(\''.$msg.'\')) {sfjdeleteMassPM(\''.$site.'\');}" />';
	}

	if($view == 'inbox')
	{
		$out = '<tr><th width="110" align="left">'.__("From", "sforum").'</th><th align="left">'.__("Title", "sforum").'</th><th width="100">'.__("Last", "sforum").'</th><th width="50">'.__("Thread", "sforum").'</th><th width="50">'.$delete.'</th></tr>'."\n";
	} else {
		$out = '<tr><th width="110" align="left">'.__("To", "sforum").'</th><th align="left">'.__("Title", "sforum").'</th><th width="100">'.__("Last", "sforum").'</th><th width="50">'.__("Thread", "sforum").'</th><th width="50">'.$delete.'</th></tr>'."\n";
	}
	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_thread()
#
# Main rendering loop of individual PM thread
#	$threadindex	The table row index
#	$view			Set to 'inbox' or 'sentbox'
#	$thread			The PM thread data array
#	$cansendpm		True/False - can current user send PMs
# ------------------------------------------------------------------
function sf_render_pm_thread($threadindex, $view, $thread, $cansendpm)
{
	$out = '';

	# Is there an unread (inbox view) and get last message date and sender (inbox) or recopient (sentbox)
	$read_status = 1;
	foreach($thread['messages'] as $message)
	{
		if($message['message_status'] == '0') $read_status = 0;
		$sent_date = $message['sent_date'];
		if($view == 'inbox')
		{
			$sender_id = $message['from_id'];
		} else {
			$sender_id = $message['to_id'];
			$read_status = 1;
		}
		$sender_name = $message['display_name'];
	}

	if($read_status == 0)
	{
		$out.= '<tr class="sfpmunread" id="pm-'.$thread['slug'].'">'."\n";
	} else {
		$out.= '<tr class="sfpmread" id="pm-'.$thread['slug'].'">'."\n";
	}
	$out.= '<td>'.sf_render_pm_sender($sender_id, $sender_name, $read_status, $cansendpm).'</td>'."\n";
	$out.= '<td>'.sf_render_pm_thread_title($threadindex, $thread['title'], $read_status, $thread['slug']).'</td>'."\n";
	$out.= '<td align="center">'.sf_render_sent_date($sent_date).'</td>'."\n";
	$out.= '<td align="center" id="pm-'.$thread['slug'].'count'.'">'.count($thread['messages']).'</td>'."\n";
	$out.= '<td>'.sf_render_pm_delete_thread($threadindex, $view, $thread['slug']).'</td>'."\n";

	$out.= '</tr>'."\n";

	$out.= sf_render_pm_messages($view, $thread, $threadindex, $cansendpm);

	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_messages()
#
# Main rendering loop of individual PM message
#	$view			Set to 'inbox' or 'sentbox'
#	$thread			The PM thread data array
#	$cansendpm		True/False - can current user send PMs
# ------------------------------------------------------------------
function sf_render_pm_messages($view, $thread, $threadindex, $cansendpm)
{
	global $current_user, $wpdb;

	$out = '';

	$out.='<tr class="sfadminrow" id="messagerow-'.$thread['slug'].'">'."\n";
	$out.='<td class="sfadminrow" colspan="5">'."\n";
	$out.='<div id="messagediv-'.$thread['slug'].'" class="inline_edit">'."\n";
	$out.= '<table class="sfpmtable" id="sfmessagetable-'.$thread['slug'].'" cellspacing="0" border="1">'."\n";

	$out.= sf_render_pm_inner_header_row($view);

	$messageindex = 1;

	foreach($thread['messages'] as $message)
	{
		if($message['message_status'] == '0')
		{
			$out.= '<tr class="sfpmunread" id="message-'.$message['message_id'].'">';
		} else {
			$out.= '<tr class="sfpmread" id="message-'.$message['message_id'].'">';
		}

		$out.= '<td valign="middle">'.sf_render_pm_status_icon($message['message_status'], $message['is_reply']).'</td>';
		if($view == 'inbox')
		{
			$out.= '<td valign="middle">'.sf_render_pm_sender($message['from_id'], $message['display_name'], $message['message_status'], $cansendpm).'</td>'."\n";
		} else {
			$out.= '<td valign="middle">'.sf_render_pm_sender($message['to_id'], $message['display_name'], $message['message_status'], $cansendpm).'</td>'."\n";
		}
		$out.= '<td valign="middle">'.sf_render_pm_message_title($thread['title'], $message['message_status'], $message['message_id'], $thread['slug'], $view).'</td>'."\n";
		$out.= '<td  valign="middle" align="center">'.sf_render_sent_date($message['sent_date']).'</td>'."\n";
		if($view == 'inbox')
		{
			$out.= '<td></td>';
		} else {
			$out.= '<td></td>';
		}
		$out.= '<td valign="middle">'.sf_render_pm_delete_message($messageindex, $threadindex, $view, $message['message_id'], $thread['slug']).'</td>';
		$out.= '</tr>';

		$out.= '<tr class="">';
		$out.= '<td colspan="5">';
		$out.= '<div class="sfpmcontent inline_edit" id="sfpminfo'.$message['message_id'].'"></div>';
		$out.= '<div class="sfpmcontent inline_edit" id="sfpm'.$message['message_id'].'"></div>';
		$out.= '</td>';

		if($view == 'inbox')
		{
			# GET ALL RECIPIENTS
			$idlist = '';
			$namelist = '';

			if($cansendpm)
			{
				$allIds = array();
				$allNames = array();

				# Go and get all recipients (for Reply/Quote All buttons)
				$recipients = $wpdb->get_results(
					"SELECT to_id, display_name
					 FROM ".SFMESSAGES."
					 LEFT JOIN ".SFMEMBERS." ON ".SFMESSAGES.".to_id = ".SFMEMBERS.".user_id
					 WHERE message_slug='".$thread['slug']."' AND sent_date='".$message['sent_date']."'");

				if($recipients)
				{
					foreach($recipients as $recipient)
					{
						if($recipient->to_id != $current_user->ID)
						{
							$allIds[]=$recipient->to_id;
							$allNames[]=$recipient->display_name;
						}
					}
				}
				if($allIds)
				{
					$idlist = implode(',', $allIds);
					$namelist = implode(',', $allNames);
				}
			}

			$out.= '<td valign="top" align="center" width="50">';
			$out.= sf_render_pm_reply_message($message['message_id'], $message['from_id'], $message['display_name'], $thread['title'], $message['message_status'], $cansendpm, $idlist, $namelist, $thread['slug'])."\n";
			$out.= sf_render_pm_quote_message($message['message_id'], $message['from_id'], $message['display_name'], $thread['title'], $message['message_status'], $cansendpm, $idlist, $namelist, $thread['slug'])."\n";
			$out.= '</td>';
		} else {
			$out.= '<td></td>';
		}
		$out.= '</tr>';

		$messageindex = $messageindex + 2;
	}

	$out.= '</table>';
	$out.= '</div>';
	$out.= '</td>';
	$out.= '</tr>';

	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_inner_header_row()
#
# Sub-heading of inner message table
#	$view			Set to 'inbox' or 'sentbox'
# ------------------------------------------------------------------
function sf_render_pm_inner_header_row($view)
{
	if($view == 'inbox')
	{
		$out = '<tr><th width="18"></th><th align="left" width="110">'.__("From", "sforum").'</th><th>'.__("Title", "sforum").'</th><th width="100">'.__("Date Sent", "sforum").'</th><th width="50"></th><th width="50"></th></tr>';
	} else {
		$out = '<tr><th width="18"></th><th width="110">'.__("To", "sforum").'</th><th>'.__("Title", "sforum").'</th><th width="100">'.__("Date Sent", "sforum").'</th><th width="50"></th><th width="50"></th></tr>';
	}
	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_sender()
#
# Display sender data and link to 'compose new'
#	$sender_id:			ID of the user who sent the message
#	$sender_name		Display ame of the user who sent the message
#	$read_status		0 = read - 1 = unread
#	$cansendpm			True/False - can current user send PMs
# ------------------------------------------------------------------
function sf_render_pm_sender($sender_id, $sender_name, $read_status, $cansendpm)
{
	$out = '';
	$reply = 0;
	$title = '';
	$class=' sfread';
	if($read_status == 0) $class='';
	if($cansendpm)
	{
		$out.= '<a class="sfpmentry'.$class.'" onclick="sfjsendPMTo(\''.$sender_id.'\', \''.stripslashes($sender_name).'\', \''.$title.'\', \''.$reply.'\');" title="'.__("Send Message To Member", "sforum").'">'.stripslashes($sender_name).'</a>';
	} else {
		$out.= stripslashes($sender_name);
	}
	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_thread_title()
#
# Dislay message title with link to expand thread
#	$threadindex	The table row index
#	$title:			Ttle of the message thread
#	$read_status	0 = read - 1 = unread
#	$slug			ID of messages row to open oin click
# ------------------------------------------------------------------
function sf_render_pm_thread_title($threadindex, $title, $read_status, $slug)
{
	$out = '';
	$class=' sfread';
	if($read_status == 0) $class='';
	$out.= '<a class="sfpmentry'.$class.'" onclick="sfjtoggleThread(this, \'messagediv-'.$slug.'\', \''.$threadindex.'\');" title="'.__("Open/Close Thread", "sforum").'"><img src="'.SFRESOURCES.'pm-open-thread.png" alt="" title="'.__("Open/Close Thread", "sforum").'"/>'.$title.'</a>'."\n";
	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_message_title()
#
# Dislay message title with link to expand thread
#	$title:			Ttle of the message thread
#	$read_status	0 = read - 1 = unread
#	$msgid			ID of the message
#	$slug			ID of messages row to open on click
#	$view			Set to 'inbox' or 'sentbox'
# ------------------------------------------------------------------
function sf_render_pm_message_title($title, $read_status, $msgid, $slug, $view)
{
	$out = '';
	$gif= SFJSCRIPT.'working.gif';
	$site=SF_PLUGIN_URL."/messaging/ahah/sf-ahahpmexplode.php?";
	$class=' sfread';
	if($read_status == 0) $class='';
	$out.= '<a class="sfpmentry'.$class.'" onclick="sfjgetPMText(\''.$gif.'\', \''.$site.'\', \''.$msgid.'\', \''.$view.'\', \''.$read_status.'\')" title="'.__("Open/Close Message", "sforum").'"><img src="'.SFRESOURCES.'pm-open-message.png" alt="" title="'.__("Open/Close Message", "sforum").'" />'.$title.'</a>';
	return $out;
}

# ------------------------------------------------------------------
# sf_render_sent_date()
#
# Dislay message sent date
#	$sent_date		Date message was sent
# ------------------------------------------------------------------
function sf_render_sent_date($sent_date)
{
	return mysql2date(SFDATES, $sent_date);
}

# ------------------------------------------------------------------
# sf_render_pm_status_icon()
#
# Delete thread button
#	$status		unread=0 or read-1
#	$is_reply	true of a reply message
# ------------------------------------------------------------------
function sf_render_pm_status_icon($status, $is_reply)
{
	if($is_reply) $status='2';

	switch($status)
	{
		case '0':
			$icon = 'pmunread.png';
			$title= __("Unread Message", "sforum");
			break;
		case '1':
			$icon = 'pmread.png';
			$title= __("Read Message", "sforum");
			break;
		case '2':
			$icon = 'pmreplied.png';
			$title= __("Replied To Message", "sforum");
			break;
	}
	$out = '<img src="'.SFRESOURCES.$icon.'" alt="" title="'.$title.'" />';
	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_reply_message()
#
# Remders the 'Reply button on inbox view
#	$msgid				ID of the message
#	$sender_id:			ID of the user who sent the message
#	$sender_name		Display ame of the user who sent the message
#	$title				Message title
#	$read_status		0 = read - 1 = unread
#	$cansendpm			True/False - can current user send PMs
# ------------------------------------------------------------------
function sf_render_pm_reply_message($msgid, $sender_id, $sender_name, $title, $read_status, $cansendpm, $idlist, $namelist, $slug)
{
	$out = '';
	$reply = 1;
	if($cansendpm)
	{
		$title = addslashes($title);
		$out.= '<input type="button" id="pmreply-'.$msgid.'" class="sfxcontrol inline_edit" name="pmreply-'.$msgid.'" value="'.__("Reply", "sforum").'" onclick="sfjsendPMTo(\''.$sender_id.'\', \''.stripslashes($sender_name).'\', \''.$title.'\', \''.$reply.'\', \''.$slug.'\');" />';
		if(!empty($idlist))
		{
			$idlist = $idlist.','.$sender_id;
			$namelist = $namelist.','.$sender_name;
			$out.= '<input type="button" id="pmreplyall-'.$msgid.'" class="sfxcontrol inline_edit" name="pmreplyall-'.$msgid.'" value="'.__("Reply All", "sforum").'" onclick="sfjsendPMTo(\''.$idlist.'\', \''.stripslashes($namelist).'\', \''.$title.'\', \''.$reply.'\', \''.$slug.'\');" />';
		}
	}

	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_quote_message()
#
# Remders the 'Reply button on inbox vien
#	$msgid				ID of message being quoted
#	$sender_id:			ID of the user who sent the message
#	$sender_name		Display ame of the user who sent the message
#	$title				Title of the message
#	$read_status		0 = read - 1 = unread
#	$cansendpm			True/False - can current user send PMs
# ------------------------------------------------------------------
function sf_render_pm_quote_message($msgid, $sender_id, $sender_name, $title, $read_status, $cansendpm, $idlist, $namelist, $slug)
{
	global $sfglobals;

	$editor = 0;
	if($sfglobals['editor']['sfeditor'] == RICHTEXT) $editor = 1;

	$out = '';
	$reply = 1;
	if($cansendpm)
	{
		$title = addslashes($title);
		$intro = '&lt;p&gt;'.htmlentities($sender_name, ENT_COMPAT, get_bloginfo('charset')).' '.__("said:", "sforum").'&lt;/p&gt;';
		$out.= '<input type="button" id="pmquote-'.$msgid.'" class="sfxcontrol inline_edit" name="pmquote-'.$msgid.'" value="'.__("Quote", "sforum").'" onclick="sfjquotePM(\''.$sender_id.'\', \'sfpm'.$msgid.'\', \''.$intro.'\', '.$editor.', \''.stripslashes($sender_name).'\', \''.$title.'\', \''.$reply.'\', \''.$slug.'\');" />';
		if(!empty($idlist))
		{
			$idlist = $idlist.','.$sender_id;
			$namelist = $namelist.','.$sender_name;
			$out.= '<input type="button" id="pmquoteall-'.$msgid.'" class="sfxcontrol inline_edit" name="pmquoteall-'.$msgid.'" value="'.__("Quote All", "sforum").'" onclick="sfjquotePM(\''.$idlist.'\', \'sfpm'.$msgid.'\', \''.$intro.'\', '.$editor.', \''.stripslashes($namelist).'\', \''.$title.'\', \''.$reply.'\', \''.$slug.'\');" />';
		}
	}

	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_delete_thread()
#
# Delete thread button
#	$threadindex	Table row index
#	$view			Set to 'inbox' or 'sentbox'
#	$slug			The outer table row slug
# ------------------------------------------------------------------
function sf_render_pm_delete_thread($threadindex, $view, $slug)
{
	$threadurl=SF_PLUGIN_URL."/messaging/ahah/sf-ahahpmexplode.php?pmdelthread=".$slug."&amp;pmaction=".$view;
	$out = '<input type="button" class="sfxcontrol" name="deletethread" id="pm-'.$slug.'delthread'.'" tabindex="0" value="'.__("Delete Thread", "sforum").'" onclick="sfjdeleteThread(this, \''.$threadurl.'\', \''.$threadindex.'\', \'messagediv-'.$slug.'\');" />';
	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_delete_message()
#
# Remders the 'Delete button on inbox vien
#	$
#	$
#	$
#	$
# ------------------------------------------------------------------
function sf_render_pm_delete_message($messageindex, $threadindex, $view, $msgid, $slug)
{
	$messageurl = SF_PLUGIN_URL."/messaging/ahah/sf-ahahpmexplode.php?pmdelmsg=".$msgid."&amp;pmaction=".$view;
	$threadurl = SF_PLUGIN_URL."/messaging/ahah/sf-ahahpmexplode.php?pmdelthread=".$slug."&amp;pmaction=".$view;
	$out = '<input type="button" class="sfxcontrol" name="deletemessage'.$msgid.'" id="deletemessage'.$msgid.'" tabindex="0" value="'.__("Delete Message", "sforum").'" onclick="sfjdeletePM(this, \''.$messageurl.'\', \''.$threadurl.'\', \''.$messageindex.'\', \''.$threadindex.'\', \'messagediv-'.$slug.'\', \''.$slug.'\');" />';

	return $out;
}

# ------------------------------------------------------------------
# sf_render_pm_inbox_warning()
#
# Remders warning message regarding inbox size
#	$message		The appropriate message to display
# ------------------------------------------------------------------
function sf_render_pm_inbox_warning($message)
{
	$out = '<div class="sfmessagestrip sfpmalert"><p>'.$message.'</p></div>';
	return $out;
}

# ------------------------------------------------------------------
# sf_localise_boxname()
#
# Localises box name 'inbox' and 'sentbox' used as parameters
#	$box		The English name of the box
# ------------------------------------------------------------------
function sf_localise_boxname($box)
{
	$box = ucfirst($box);
	if($box == "Inbox")
	{
		return __("Inbox", "sforum");
	} else {
		return __("Sentbox", "sforum");
	}
}

?>