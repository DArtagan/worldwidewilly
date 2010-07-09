<?php
/*
Simple:Press Forum
Set RSS Feed to external source (i.e., Feedburner)
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

require_once("../../sf-config.php");
require_once("../sf-adminsupport.php");
require_once('../../forum/sf-primitives.php');

# Check Whether User Can Manage Forums
if(!sf_current_user_can('SPF Manage Forums')) {
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminforums.php');

$action = sf_syscheckstr($_GET['action']);
if ($action == 'rss')
{
	sfa_admin_rss_popup();
}
if ($action == 'icon')
{
	sfa_admin_icon_popup();
}
if ($action == 'tss')
{
	sfa_admin_topic_status_popup();
}

die();


function sfa_admin_rss_popup()
{
	if(isset($_GET['item'])) $item = sf_syscheckstr($_GET['item']);
	if(isset($_GET['id'])) $id = sf_syscheckstr($_GET['id']);
	if(isset($_GET['url'])) $current_url= sf_syscheckstr($_GET['url']);
	if(isset($_GET['pvt'])) $private = sf_syscheckint($_GET['pvt']);

	if ($item == 'Group')
	{
		$def_rssurl = sf_get_sfurl_plus_amp(SFURL).'group='.$id.'&amp;xfeed=group';
		$head = __("Group", "sforum").': '.$id;
		$pvt_text = __("Enable/Disable All Forum RSS Feeds in this Group (Leave blank for no changes):", "sforum");
	}
	if ($item == 'Forum')
	{
		$def_rssurl = sf_build_qurl('forum='.$id, 'xfeed=forum');
		$head = __("Forum", "sforum").': '.$id;
		$pvt_text = __("Disable Forum RSS Feed (Feed will not be generated):", "sforum");
	}
	if ($item == "All")
	{
		$def_rssurl = sf_build_qurl('xfeed=all');
		$head = __("All Groups", "sforum");
		$pvt_text = __("Enable/Disable All Forum RSS Feeds (Leave blank for no changes):", "sforum");
	}
	?>
		<fieldset class="sffieldset"><legend><?php _e("RSS Feed Configuration", "sforum") ?></legend>
			<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfrssedit">
				<?php echo(sf_create_nonce('forum-adminform_rssedit')); ?>		
	
				<p><strong><?php echo($head); ?></strong></p>
				
				<input type="hidden" name="item" value="<?php echo($item); ?>" />
				<input type="hidden" name="id" value="<?php echo($id); ?>" />
				
				<p><?php _e('Default RSS URL:', 'sforum') ?><br />
				<input class="sfacontrol" type="text" name="defurl" size="47" value="<?php echo($def_rssurl); ?>" /></p>
				
				<p><?php _e('Replacement External RSS URL:', 'sforum') ?><br />
				<input class="sfacontrol" type="text" name="newRSSurl" size="47" value="<?php echo($current_url); ?>" /></p>

				<?php if ($item == 'Forum') { ?>
					<?php if ($private) $checked='checked'; else $checked=''; ?>
					<p><label>
					<input class="sfacontrol" type="checkbox" id="sfcheckbox1" <?php echo $checked; ?> name="newRSSpvt" size="47" />
					<?php echo $pvt_text; ?>
					</label></p>
				<?php } else { ?>
					<p><?php echo $pvt_text; ?></p>
					<p><label for="sfradio1">
					<input class="sfacontrol" id="sfradio1" type="radio" value="set" name="setallRSSpvt" size="47" />
					<?php _e("Disable", "sforum"); ?>
					</label></p>
					<p><label for="sfradio2">
					<input class="sfacontrol" id="sfradio2" type="radio" value="clear" name="setallRSSpvt" size="47" />
					<?php _e("Enable", "sforum"); ?>
					</label></p>
				<?php } ?>
	
				<input type="submit" class="sfacontrol" name="setRSSurl" value="<?php _e("Save Configuration", "sforum") ?>" />
				<input type="button" class="sfacontrol" name="cancel" value="<?php _e("Cancel", "sforum") ?>" onclick="hs.close(this);" />

			</form>
		</fieldset>
	<?php
	return;
}

function sfa_admin_icon_popup()
{
	$item = sf_syscheckstr($_GET['item']);
	$id = sf_syscheckstr($_GET['id']);
	$current = sf_syscheckstr($_GET['deficon']);
	
	if($item == 'Group') $head = __("Set Custom Icon for Group", "sforum").' '.$id;	
	if($item == 'Forum') $head = __("Set Custom Icon for Forum", "sforum").' '.$id;	

	$note = __("ALL Custom Icons must be located in your Icon Sets 'custom' folder", "sforum");
	?>
		<fieldset class="sffieldset"><legend><?php _e("Set Custom Icon", "sforum") ?></legend>
			<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sficonedit">
				<?php echo(sf_create_nonce('forum-adminform_iconedit')); ?>
	
				<p><strong><?php echo($head); ?></strong></p>
				<p><?php echo($note); ?></p>
				
				<input type="hidden" name="item" value="<?php echo($item); ?>" />
				<input type="hidden" name="id" value="<?php echo($id); ?>" />

				<p><?php _e('Custom Icon (Filename Only):', 'sforum') ?><br />
				<input class="sfacontrol" type="text" name="cusicon" size="47" value="<?php echo($current); ?>" /></p>
				
				<input type="submit" class="sfacontrol" name="setIcon" value="<?php _e("Set Custom Icon", "sforum") ?>" />
				<?php if(!empty($current)) { ?>
	 				<input type="submit" class="sfacontrol" name="removeIcon" value="<?php _e("Remove Custom Icon", "sforum") ?>" />
	 			<?php } ?>
				<input type="button" class="sfacontrol" name="cancel" value="<?php _e("Cancel", "sforum") ?>" onclick="hs.close(this);" />

			</form>
		</fieldset>
	<?php
	return;
}

function sfa_admin_topic_status_popup()
{
	$item = sf_syscheckstr($_GET['item']);
	$current='';
	
	if($item == 'Group')
	{
		$id = sf_syscheckstr($_GET['id']);
		$head = __("Asign a Topic Status Set to all Forums in Group", "sforum").' '.$id;	
	}
	if($item == "Forum")
	{
		$id = sf_syscheckstr($_GET['id']);
		$current = sf_syscheckstr($_GET['tset']);
		$head = __("Asign a Topic Status Set to Forum", "sforum").'<br />'.$id;	
	}

	?>
		<fieldset class="sffieldset"><legend><?php _e("Set Topic Status Set", "sforum") ?></legend>
			<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sftopstatset">
				<?php echo(sf_create_nonce('forum-adminform_topstatset')); ?>
	
				<p><strong><?php echo($head); ?></strong></p>
				
				<input type="hidden" name="item" value="<?php echo($item); ?>" />
				<input type="hidden" name="id" value="<?php echo($id); ?>" />

				<?php echo(sfa_create_topic_status_select($current)); ?>
				<br />
	 			<input type="submit" class="sfacontrol" name="savetopstatset" value="<?php _e("Save Status Assignment", "sforum") ?>" />
				<input type="button" class="sfacontrol" name="cancel" value="<?php _e("Cancel", "sforum") ?>" onclick="hs.close(this);" />

			</form>
		</fieldset>
	<?php

	return;
}

?>