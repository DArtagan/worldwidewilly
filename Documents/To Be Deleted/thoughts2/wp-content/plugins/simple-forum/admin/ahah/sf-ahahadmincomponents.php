<?php
/*
Simple:Press Forum
Component Specials
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

require_once("../../sf-config.php");
require_once('../../forum/sf-primitives.php');

# Check Whether User Can Manage Components
if(!sf_current_user_can('SPF Manage Components')) {
	echo (__('Access Denied', "sforum"));
	die();
}

require_once("../sf-adminsupport.php");

define('SFADMINIMAGES', SF_PLUGIN_URL . '/admin/images/');

global $wpdb;

$action = sf_syscheckstr($_GET['action']);
if ($action == 'delete-cfield')
{
	$id = sf_syscheckint($_GET['id']);
	$cfield = sf_syscheckint($_GET['cfield']);
	
	# remove the custom field
	sf_delete_sfmeta($id);
	
	# remove any usermeta for the custom field
	sfa_del_custom_field($cfield);
}

if ($action == 'del_rank')
{
	$key = sf_syscheckstr($_GET['key']);

	# remove the forum rank
	$sql = "DELETE FROM ".SFMETA." WHERE meta_type='forum_rank' AND meta_key='".$key."'";
	$wpdb->query($sql);
}

die();
?>