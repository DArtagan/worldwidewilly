<?php
/*
Simple:Press Forum 
Private Messaging control
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

function sf_message_control($view)
{
	global $current_user, $sfglobals, $sfvars, $wpdb;

	$targetuser = false;
	
	$returnurl=SFURL;

	# Check and validate user and ensure PMs are allowed
	if(($current_user->ID != sf_syscheckint($_GET['pms'])) || ($current_user->sfusepm == false))
	{
		update_sfnotice('sfmessage', '1@'.__('Access Denied', "sforum"));
		$out = sf_render_queued_message();
		$out.= '<a href="'.$returnurl.'" />Return</a>';
		return $out;
	}

	# Did we enter this with a user id to message to? (Fromn the forum)
	if(isset($_GET['pmtoname']))
	{
		$targetuser = sf_get_user_id(sf_syscheckstr($_GET['pmtoname']));
		$targetname = sf_syscheckstr($_GET['pmtoname']);
	}

	# Setup some stuff we need	
	$lastvisit = sf_track_online();

	# Load up the data we need
	if($view == 'inbox')
	{
		$messagebox = sf_get_pm_inbox($current_user->ID);
	} else {
		$messagebox = sf_get_pm_sentbox($current_user->ID);
	}

	# Grab message count
	$messagecount = $wpdb->get_var("SELECT FOUND_ROWS()");

	# Load the max box size if set
	$sfpm = get_option('sfpm');
	$maxsize = $sfpm['sfpmmax'];
	$cansendpm = true;
	$boxmsg = '';

	# Prepare the messages if box size exceeded...	
	if($maxsize > 0)
	{
		$boxsize = sf_get_pm_boxcount($current_user->ID);
		if($boxsize > $maxsize)
		{
			$boxmsg = sprintf(__("Your Inbox/Sentbox (%s messages) has exceeded the Maximum Allowed (%s) - Please delete some messages", "sforum"), $boxsize, $maxsize);
			$cansendpm = false;
		} elseif($boxsize == $maxsize)
		{
			$boxmsg = sprintf(__("Your Inbox/Sentbox (%s messages) has reached the Maximum Allowed (%s) - Please delete some messages", "sforum"), $boxsize, $maxsize);
			$cansendpm = false;
		} elseif($boxsize > ($maxsize-5)) 
		{
			$boxmsg = sprintf(__("Your Inbox/Sentbox (%s messages) is approaching the Maximum Allowed (%s) - Please delete some messages", "sforum"), $boxsize, $maxsize);
		}
	}

	# Top of the pm (same as forum) - Display starts here
	$out = sf_render_queued_message();

	# Start Display
	$out.= '<div class="inline_edit" id="sfdummy"></div>';
	$out.= '<div class="inline_edit" id="pmview">'.$view.'</div>';
	$out.= '<div id="sforum">'."\n";
	$out.= '<a id="forumtop"></a>'."\n";
	$out.= '<div id="sflogininfo"></div>';

	if($sfglobals['admin']['sfadminbar'])
	{
		# Check if admin and if any new posts waiting 
		$newposts='';
		if($current_user->adminstatus || $current_user->moderator) 
		{
			$newposts = sf_get_admins_queued_posts();
		}
		$out.= sf_render_admin_strip('inbox', 'inbox', $newposts);	
	}

	$out.= sf_render_login_strip('pm', 'pm', 'forum', $returnurl, $view);
	$ql = get_option('sfquicklinks');
	if(get_option('sfsearchbar') && $ql['sfqlshow'])
	{
		$out.= sf_render_searchbar('inbox', '', '');
	}

	$out.= '<div class="sfblock">'."\n";
	$out.= sf_render_main_header_table('pm', '', '', '', 0, SFRESOURCES.'inbox.png', false, false, false, false, 0, $view, $cansendpm, 0, 0, 0, $messagecount); 

	if($boxmsg != '')
	{
		$out.= sf_render_pm_inbox_warning($boxmsg);
	}

	# Paint the table
	$out.= '<div id="pmcontainer">';
	$out.= sf_render_pm_table($view, $messagebox, $messagecount, $cansendpm);
	$out.= '</div>';
	
	$out.= sf_render_bottom_iconstrip($view, $current_user->ID, '');

	# Close sfblock div
	$out.= '</div>'; 

	if($cansendpm)
	{
		$out.= '<a id="dataform"></a>'."\n";
		$out.= sf_add_pm();

		# Did we enter this from the forum with a message to write?
		if($targetuser)
		{
			$title = '';
			$reply = 0;
			$out.= '<script type="text/javascript">'."\n";
			$out.= 'sfjsendPMTo(\''.$targetuser.'\', \''.$targetname.'\', \''.$title.'\', \''.$reply.'\');';
			$out.= '</script>'."\n"."\n";
		}
	} else {
		$out.= sf_render_pm_inbox_warning("You will be unable to send any further messages until your Inbox/Sentbox size is reduced");
	}
	
	$out.= sf_render_stats();
	if(function_exists('sf_hook_footer_inside'))
	{
		$out.= sf_hook_footer_inside();
	}
	$out.= sf_render_version_strip();
	$out.= '<a id="forumbottom"></a>'."\n";
	$out.= '</div>'."\n";
	if(function_exists('sf_hook_footer_outside'))
	{
		$out.= sf_hook_footer_outside();
	}
	$sfauto=array();
	$sfauto=get_option('sfauto');
	if($sfauto['sfautoupdate'])
	{
		$out.= sf_start_auto_update($sfauto['sfautotime'] * 1000);
	}

	return $out;
}

?>