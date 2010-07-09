<?php
/*
Simple:Press Forum
Ahah call PM related stuff
$LastChangedDate: 2009-01-04 07:10:09 +0000 (Sun, 04 Jan 2009) $
$Rev: 1126 $
*/

ob_start();
require_once("../../sf-config.php");
require_once("../../messaging/sf-pmdatabase.php");

ob_end_clean();  # Ensure we don't have output from other plugins.
header('Content-Type: text/html; charset='.get_option('blog_charset'));

sf_load_foundation();
sf_initialise_globals();

global $current_user, $wpdb;

if (!$current_user->sfusepm) {
	echo (__('Access Denied', "sforum"));
	die();
}

# display message info --------------------------------------------
if(isset($_GET['pminfo']))
{
	$id = sf_syscheckint($_GET['pminfo']);
	$view = $_GET['pmaction'];

	if($view == 'inbox' ? $field='from_id' : $field='to_id');

	$message = $wpdb->get_row(
			"SELECT message_id, sent_date, from_id, to_id, title, message_status, inbox, sentbox, is_reply, display_name
			 FROM ".SFMESSAGES."
			 LEFT JOIN ".SFMEMBERS." ON ".SFMESSAGES.".".$field." = ".SFMEMBERS.".user_id
			 WHERE message_id=".$id);

	if($message)
	{
		if($view == 'inbox')
		{
			echo __("From", "sforum").": <strong>".$message->display_name.'</strong>';
		} else {
			echo __("To", "sforum").": <strong>".$message->display_name.'</strong>';
		}
	}

	if($view == 'inbox')
	{
		$recipients = $wpdb->get_results(
			"SELECT message_id, sent_date, from_id, to_id, title, message_status, inbox, sentbox, is_reply, display_name
			 FROM ".SFMESSAGES."
			 LEFT JOIN ".SFMEMBERS." ON ".SFMESSAGES.".to_id = ".SFMEMBERS.".user_id
			 WHERE title='".$message->title."' AND sent_date='".$message->sent_date."'");

		if($recipients)
		{
			$many=count($recipients);
			$thisone = 1;

			echo '<br />'.__("To", "sforum").": ";
			foreach($recipients as $recipient)
			{
				echo '<strong>'.$recipient->display_name.'</strong>';
				if($thisone < $many) echo(", ");
				$thisone++;
			}
		}
	}

	die();
}

# display message content -----------------------------------------
if(isset($_GET['pmshow']))
{
	$id = sf_syscheckint($_GET['pmshow']);
	echo '<br />'.sf_filter_content(stripslashes(sf_get_pm_message($id)), '').'<br />';
	# mark it as read in the database record
	if($_GET['pmaction'] == 'inbox')
	{
		sf_pm_set_read($id);
	}
	die();
}

# delete a message thread -----------------------------------------
if(isset($_GET['pmdelthread']))
{
	$box = sf_syscheckstr($_GET['pmaction']);
	$slug = sf_syscheckstr($_GET['pmdelthread']);

	if($box == 'inbox' ? $field='to_id' : $field='from_id');

	$messages = $wpdb->get_results(
			"SELECT message_id
			 FROM ".SFMESSAGES."
			 WHERE ".$field."=".$current_user->ID." AND message_slug='".$slug."'");

	if($messages)
	{
		foreach($messages as $message)
		{
			sf_pm_delete($message->message_id, $box);
		}
	}
	die();
}

# delete a message ------------------------------------------------
if(isset($_GET['pmdelmsg']))
{
	$id = sf_syscheckint($_GET['pmdelmsg']);
	$box = sf_syscheckstr($_GET['pmaction']);
	sf_pm_delete($id, $box);
	die();
}

# delete whole inbox or sentbox -----------------------------------
if(isset($_GET['pmdelall']))
{
	$box = sf_syscheckstr($_GET['pmdelall']);
	$userid = sf_syscheckint($_GET['owner']);

	switch($box)
	{
		case 'inbox':
			$pmlist = sf_get_pm_inbox_idlist($userid);
			break;
		case 'sentbox':
			$pmlist = sf_get_pm_sentbox_idlist($userid);
			break;
	}

	if($pmlist)
	{
		foreach($pmlist as $pm)
		{
			sf_pm_delete($pm->message_id, $box);
		}
	}
	die();
}

# Populate members box --------------------------------------------
if(isset($_GET['pop']))
{

	$out = '<select class="sflistcontrol" tabindex="4" name="pmmemlist" id="pmmemlist" size="9" onchange="sfjaddpmUser(\''.pmmemlist.'\');">'."\n";
	$out.= sf_create_pmuser_select(-1, 'members', $_GET['pop']);
	$out.= '</select>';

	echo $out;

	die();
}

# Add recipients to users buddy list ------------------------------
if(isset($_GET['addbuddy']))
{
	$list = array();
	$list = explode('-', $_GET['addbuddy']);
	if($list)
	{
		foreach($list as $buddy)
		{
			if($buddy != 0)
			{
				sf_add_buddy($buddy);
			}
		}
		$out = '<select class="sflistcontrol" tabindex="5" name="pmbudlist" id="pmbudlist" size="9" onchange="sfjaddpmUser(\''.pmbudlist.'\');">'."\n";
		$out.= sf_create_pmuser_select(-1, 'buddies');
		$out.= '</select>';
		echo $out;
	}
	die();
}


# -----------------------
# support routines
# -----------------------

function sf_get_pm_message($id)
{
	global $wpdb;
	return $wpdb->get_var("SELECT message FROM ".SFMESSAGES." WHERE message_id=".$id);
}

function sf_pm_set_read($id)
{
	global $wpdb;
	$wpdb->query("UPDATE ".SFMESSAGES." SET message_status=1 WHERE message_id=".$id);
	return;
}

function sf_pm_delete($id, $box)
{
	global $wpdb;

	$delete = false;

	# Only delete if both sentbox and inbox have been set to zero - so check first
	$message = $wpdb->get_row("SELECT * FROM ".SFMESSAGES." WHERE message_id = ".$id, ARRAY_A );

	switch($box)
	{
		case 'inbox':
			if($message['sentbox'] == 0) $delete = true;
			break;
		case 'sentbox':
			if($message['inbox'] == 0) $delete = true;
			break;
	}

	if($delete)
	{
		$wpdb->query("DELETE FROM ".SFMESSAGES." WHERE message_id=".$id);
	} else {
		$wpdb->query("UPDATE ".SFMESSAGES." SET ".$box."=0 WHERE message_id=".$id);
	}
	return;
}

?>