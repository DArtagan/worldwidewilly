<?php
/*
Simple:Press Forum
Post Install Notes
$LastChangedDate: 2009-01-18 01:06:56 +0000 (Sun, 18 Jan 2009) $
$Rev: 1248 $
*/

require_once('../../../sf-config.php');

$avatar = get_option('sfinstallav');
if(empty($avatar)) $avatar = 0;
$smiley = get_option('sfinstallsm');
if(empty($smiley)) $smiley = 0;

?>
	<fieldset class="sffieldset">
		<h2>Simple:Press Forum version <?php echo(SFVERSION); ?> has been installed</h2>
	</fieldset><br />
	<div class="clearboth"></div>
	
	<div class="sfhelptext">

<?php if($avatar) { ?>
	<fieldset>
		<h3>There was a problem installing Avatars</h3>
		<p>During the install, Simple:Press Forum attempts to create a folder for the storage of forum <b>Avatars.</b> 
		This must be located directly under the WordPress folder <b><em>'/wp-content'</em></b> and must be named <b>'/wp-content/forum-avatars'</b>. 
		Finally, the default avatar images supplied in <b><em>'/simple-forum/styles/avatars'</em></b> must be copied to this new folder.</p>
	<?php if($avatar==1) { ?>
		<p>Due to your server permissions, the install was unable to create the new folder and this must be created manually for the successful use of avatars. Following creation, the default avatars must be moved to this new folder. 
	<?php } ?>
	<?php if($avatar==2 || $avatar==3) { ?>
		<p>Due to your server permissions, the install was able to create the new folder but was unable to move the default avatars into it. The default images must be moved to this new folder for the successful use of Avatars. 
	<?php } ?>
		This information is also available in the on-line help.</p>
	</fieldset>
<?php } ?>

<?php if($smiley) { ?>
	<fieldset>
		<h3>There was a problem installing Smileys</h3>
		<p>During the install, Simple:Press Forum attempts to create a folder for the storage of forum <b>Smileys.</b> 
		This must be located directly under the WordPress folder <b><em>'/wp-content'</em></b> and must be named <b>'/wp-content/forum-smileys'</b>. 
		Finally, the default smiley images supplied in <b><em>'/simple-forum/styles/smileys'</em></b> must be copied to this new folder.</p>
	<?php if($smiley==1) { ?>
		<p>Due to your server permissions, the install was unable to create the new folder and this must be created manually for the successful use of smileys. Following creation, the default smileys must be moved to this new folder. 
	<?php } ?>
	<?php if($smiley==2 || $smiley==3) { ?>
		<p>Due to your server permissions, the install was able to create the new folder but was unable to move the default smileys into it. The default images must be moved to this new folder for the successful use of Smileys. 
	<?php } ?>
		This information is also available in the on-line help.</p>
	</fieldset><br />
<?php } ?>

	<fieldset>
	<h3>What to do next...</h3>
		<p>Please spend a little time looking through the various forum <b>Management</b> panels - especially the forum <b>Options</b>. 
		A large number of support queries from new users often come down to an option setting and familiarising yourself with these will often resolve questions.</p>
		<p>If things do not appear to be quite right - there is an extensive <b>On Line Help</b> section available from the forum menu. This has sections on <b>Installation Troubleshooting</b>, 
		<b>WordPress Theme Problems</b>, <b>Tools available to the forum Admin</b>, a <b>How To</b> section and lots more.</p>
		<p>In addition, each form and section of the admin panels has a <b>Help</b> button that will pop up an explanation of how to use a particular option or setting.</p>
	</fieldset><br />
	
	</div>

	<fieldset class="sffieldset">
		<p>If you find Simple:Press Forum useful as an addition to your site - please consider a donation to help it continue to grow into the future. All donations are used for hosting, bandwidth and development costs.</p>
		<p>If however, you wish to remove Simple:Press Forum at any time, please use the proper <b>Uninstall</b> option (see the On-Line Help) which will successfully remove all traces of the forum plugin and all forum data from your database.</p>
	</fieldset>

<?php
	delete_option('sfinstallav');
	delete_option('sfinstallsm');
	delete_option('sfInstallID');

?>