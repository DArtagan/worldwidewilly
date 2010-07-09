<?php
/*
Simple:Press Forum
Installer/Upgrader
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

require_once("sf-config.php");
require_once('sf-includes.php');
require_once('forum/sf-primitives.php');

# get current version  and build from database
$current_version = get_option('sfversion');
$current_build = get_option('sfbuild');

# check if we are coming back in with post values to install
if(isset($_POST['goinstall']))
{
	sf_go_install();
	return;
}

# check if we are coming back in with post values to upgrade
if(isset($_POST['goupgrade']))
{
	sf_go_upgrade($current_version, $current_build);
	return;
}

# Has the systen been installed?
if(version_compare($current_version, '1.0', '<'))
{
	sf_install_required();
	return;
}

# Base already installed - check Version and Build Number
if(($current_build < SFBUILD) || ($current_version > SFVERSION))
{
	sf_upgrade_required();
	return;
}

# set up install
function sf_install_required()
{
	?>
	<div class="wrap"><br />
		<?php $bad = sf_version_checks();
		if($bad != '') die($bad);
		?>
		<h2><?php _e("Install Simple:Press Forum Version", "sforum"); ?> <?php echo(SFVERSION); ?> - <?php _e("Build", "sforum"); ?> <?php echo(SFBUILD); ?></h2>
			<form name="sfinstall" method="post" action="admin.php?page=simple-forum/sf-loader.php"><br />
				<input type="submit" class="button-secondary" id="sbutton" name="goinstall" value="<?php _e('Perform Installation', 'sforum'); ?>" />
			</form>
	</div>
	<?php
}

# set up upgrade
function sf_upgrade_required()
{
	?>
	<div class="wrap"><br />
		<?php $bad = sf_version_checks();
		if($bad != '') die($bad);
		?>
		<h2><?php echo sprintf(__("Upgrade Simple:Press Forum From Version %s to %s", "sforum"), get_option('sfversion'), SFVERSION); ?><br />(<?php _e("Build", "sforum"); ?> <?php echo(SFBUILD); ?>)</h2>
			<form name="sfupgrade" method="post" action="admin.php?page=simple-forum/sf-loader.php"><br />
				<input type="submit" class="button-secondary" id="sbutton" name="goupgrade" value="<?php _e('Perform Upgrade', 'sforum'); ?>" />
			</form>
	</div>
	<?php
}

# perform install
function sf_go_install()
{
	global $current_user;
	
	add_option('sfInstallID', $current_user->ID);
	
	$phpfile = SF_PLUGIN_URL . "/install/sf-install.php?";
	$image = SFADMINURL."images/working.gif";

	?>
	<div class="wrap"><br />
		<h2><?php _e("Simple:Press Forum is being installed", "sforum"); ?></h2>
		<div style="clear: both"></div>
		<br />
		<div class="wrap sfatag">		
			<div id="imagezone"></div>
		</div>
		<div style="clear: both"></div>
		<table boder="0" cellspacing="6" cellpadding="2">
			<tr><td><div id="zone0"><?php __("Installing", "sforum"); ?>...</div></td></tr>
			<tr><td><div id="zone1"></div></td></tr>
			<tr><td><div id="zone2"></div></td></tr>
			<tr><td><div id="zone3"></div></td></tr>
			<tr><td><div id="zone4"></div></td></tr>
			<tr><td><div id="zone5"></div></td></tr>
			<tr><td><div id="zone6"></div></td></tr>
			<tr><td><div id="zone7"></div></td></tr>
			<tr><td><div id="zone8"></div></td></tr>
			<tr><td><div id="zone9"></div></td></tr>
			<tr><td><div id="zone10"></div></td></tr>
		</table>
		<br />
		<div id="finishzone"></div>
		
<?php
		$pass = 10;
		$curr = 0;
		$messages = __("Go to Forum Admin", "sforum")."@".__("Installation is in progress - please wait", "sforum")."@".__("Installation Completed", "sforum");
		$out = '<script type="text/javascript">'."\n";
		$out.= 'sfjPerformInstall("'.$phpfile.'", "'.$pass.'", "'.$curr.'", "'.$image.'", "'.$messages.'");'."\n";
		$out.= '</script>'."\n";
		echo $out;
?>
	</div>
	<?php
	return;
}

# perform upgrade
function sf_go_upgrade($current_version, $current_build)
{
	global $current_user;
	
	add_option('sfInstallID', $current_user->ID);

	$phpfile = SF_PLUGIN_URL . "/install/sf-upgrade.php?";
	$image = SFADMINURL."images/working.gif";
	$imageblock = SFADMINURL."images/block.gif";

	$targetbuild = SFBUILD;
	?>
	<div class="wrap"><br />
		<h2><?php _e("Simple:Press Forum is being upgraded", "sforum"); ?></h2>
		<br />
		<div class="wrap sfatag">		
			<div id="imagezone"></div>
		</div><br />
		<div id="zonecount" class="inline_edit">0</div><br />
		<div class="wrap sfatag">		
			<div id="finishzone"></div><br />
		</div><br />
	
<?php
		$messages = __("Go to Forum Admin", "sforum")."@".__("Upgrade is in progress - please wait", "sforum")."@".__("Upgrade Completed", "sforum").'@'.__("Upgrade Aborted", "sforum");
		$out = '<script type="text/javascript">'."\n";
		$out.= 'sfjPerformUpgrade("'.$phpfile.'", "'.$current_build.'", "'.$targetbuild.'", "'.$imageblock.'", "'.$image.'", "'.$messages.'");'."\n";
		$out.= '</script>'."\n";
		echo $out;
?>
	</div>
	<?php
	return;
}

# Perform a MySQL server version check
function sf_version_checks()
{
	global $wp_version;
	
	if(function_exists("mysql_get_server_info"))
	{
		$message = '';
	
		# MySQL version check (4.1.21)
		$server = mysql_get_server_info();
		$mysql = true;
		$ver = explode(".", $server);
		if(!isset($ver[2])) $ver[2] = 0;
		if(intval($ver[0]) < 4) $mysql = false;
		if((intval($ver[0]) == 4) && (intval($ver[1]) < 1)) $mysql = false;
		if((intval($ver[0]) == 4) && (intval($ver[1]) == 1) && (intval($ver[2]) < 21)) $mysql = false;
		if(!$mysql)
		{
			$message = "<br /></div><div class='wrap sfatag'><p><b>".__("MySQL Version", "sforum")." ".$server.":</b><br /> ".sprintf(__("Your version of MySQL is not supported by Simple:Press Forum %s. MySQL version 4.1.21 or above is required", "sforum"), SFVERSION)."</p>";
		}

		# WordPress (2.5)
		if(version_compare('2.5', $wp_version) == 1)
		{
			$message.= "<br /></div><div class='wrap sfatag'><p><b>".__("WordPress Version", "sforum")." ".$wp_version.":</b><br /> ".sprintf(__("Your version of WordPress is not supported by Simple:Press Forum %s. WordPress version 2.5 or above is required", "sforum"), SFVERSION)."</p>";
		}
	}
	return $message;
}

?>