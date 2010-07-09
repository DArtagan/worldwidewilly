<?php
/*
Simple:Press Forum
Post Install Notes
$LastChangedDate: 2009-06-25 01:12:31 +0100 (Thu, 25 Jun 2009) $
$Rev: 2108 $
*/

define("THIS_VERSION", '4.0.4');

require_once('../../../sf-config.php');

$avatar = get_option('sfinstallav');
if(empty($avatar)) $avatar = 0;
$smiley = get_option('sfinstallsm');
if(empty($smiley)) $smiley = 0;

?>
	<fieldset class="sffieldset">
		<h2>Simple:Press Forum version has been upgraded to version <?php echo(THIS_VERSION); ?></h2>
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
	</fieldset>
<?php } ?>

	<fieldset>
	<h3>New in this version...</h3>
		<p>Version <?php echo(THIS_VERSION); ?> contains support for WordPress version 2.8.1 along with two or three bug fixes, including the block admin issue with the flash uploader.</p>
		<br /><br />
		<p>For a complete list of tickets resolved in <?php echo(THIS_VERSION); ?> you can always visit our <a href="http://dev.simplepressforum.com/mantis/search.php?project_id=1&sticky_issues=on&target_version=4.0.4&sortby=status%2Cpriority&dir=ASC%2CDESC&per_page=50&hide_status_id=-2" >Tracker Site</a></p>.
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