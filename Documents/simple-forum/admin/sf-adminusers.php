<?php
/*
Simple:Press Forum 
Admin Users
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	echo (__('Access Denied', "sforum"));
	die();
}

# Check Whether User Can Manage Admins
if(!sf_current_user_can('SPF Manage Users')) {
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminusers.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');
define('SFSUPPORT',   SF_PLUGIN_DIR . '/forum/sf-support.php');

include_once ('sf-adminusersforms.php');
include_once ('sf-adminsupport.php');
include_once('sf-tabsupport.php');
include_once ('sf-admin.php');
include_once (SFSUPPORT);

global $adminhelpfile;
$adminhelpfile='admin-users';

# make sure we dont need to perform an upgrade
if ( sfa_get_system_status() != 'ok' )
{
    include_once(SFLOADER);
    die();
}

# Are we wiping out spam reg?
if(isset($_POST['killSpam']))
{
	if($_POST['kill'])
	{
		sfa_kill_spamreg();
	}
}




# = ADMIN DISTRUBUTION ========================

global $records;

sfa_header(__('SPF Manage Users', 'sforum'), 'icon-users');

sfa_adminuserspage();

sfa_footer();

# = ADMIN PANELS DISTRIBUTION==================

function sfa_adminuserspage()
{
    sfa_render_users_index();

    return;
}

function sfa_kill_spamreg()
{
	global $wpdb;

	$x=0;	
	foreach ($_POST['kill'] as $key=>$value)
	{
		$wpdb->query("DELETE FROM ".SFUSERS." WHERE ID=".$key);
		$wpdb->query("DELETE FROM ".SFUSERMETA." WHERE user_id=".$key);
		$wpdb->query("DELETE FROM ".SFMEMBERS." WHERE user_id=".$key);
		sfa_delete_user_memberships($key);
		$x++;
	}

	$mess= __('Spam Registrants Removed', "sforum");
	sfa_message($mess);

	return;
}

?>