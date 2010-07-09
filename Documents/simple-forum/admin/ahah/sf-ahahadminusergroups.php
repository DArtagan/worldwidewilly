<?php
/*
Simple:Press Forum
User Group Specials
$LastChangedDate: 2009-05-30 07:25:31 +0100 (Sat, 30 May 2009) $
$Rev: 1960 $
*/

require_once("../../sf-config.php");
require_once('../../forum/sf-primitives.php');

# Check Whether User Can Manage User Groups
if (!sf_current_user_can('SPF Manage User Groups')) {
	echo (__('Access Denied', "sforum"));
	die();
}

require_once("../sf-adminsupport.php");

if (isset($_GET['ug']))
{
	$usergroup_id = sf_syscheckint($_GET['ug']);
	$members = sfa_get_usergroup_memberships($usergroup_id);
	echo sfa_display_member_roll($members);
	die();
}

if (isset($_GET['add']))
{
	$usergroup_id = sf_syscheckint($_GET['add']);
	echo sfa_populate_add_members_list($usergroup_id);
	die();
}

if (isset($_GET['del']))
{
	$usergroup_id = sf_syscheckint($_GET['del']);
	echo sfa_populate_del_members_list($usergroup_id);
	die();
}

function sfa_populate_add_members_list($usergroup_id)
{
	global $wpdb;

	$out = '';

	$out.= '<select class="sfacontrol" multiple size="10" name="member_id[]">';

	$empty = true;
	$users = $wpdb->get_results("
		SELECT user_id, display_name, admin
		FROM ".SFMEMBERS."
		WHERE NOT EXISTS (SELECT null FROM ".SFMEMBERSHIPS." WHERE usergroup_id = ".$usergroup_id." AND user_id = ".SFMEMBERS.".user_id)
		ORDER BY display_name"
	);

	foreach ($users as $user)
	{
		if (!$user->admin)
		{
			$empty = false;
			$out.='<option value="'.$user->user_id.'">'.stripslashes(wp_specialchars($user->display_name)).'</option>'."\n";
		}
		$default='';
	}

	if ($empty) $out .= '<option disabled="disabled" value="-1">'.__("No Members To Add", "sforum").'</option>';
	$out.= '</select>';

	return $out;
}

function sfa_populate_del_members_list($usergroup_id)
{
	$out='';

	$out.= '<select class="sfacontrol" multiple size="10" name="member_id[]">';

	$memberlist = sfa_get_usergroup_memberships($usergroup_id);
	if ($memberlist) {
		for( $x=0; $x<count($memberlist); $x++)
		{
			$out.='<option value="'.$memberlist[$x]->user_id.'">'.wp_specialchars($memberlist[$x]->display_name).'</option>'."\n";
			$default='';
		}
	} else {
		$out .= '<option disabled="disabled" value="-1">'.__("No Members To Delete", "sforum").'</option>';
	}
	$out.= '</select>';
	return $out;
}

function sfa_display_member_roll($members)
{
	$out='';
	if($members)
	{
		$out.= '<ul class="memberlist">';
		for($x=0; $x<count($members); $x++)
		{
			$out.= '<li>'.stripslashes(wp_specialchars($members[$x]->display_name)).'</li>';
		}
		$out.= '</ul>';
	} else {
		$out.= __("No Members in this User Group.", "sforum");
	}
	return $out;
}

?>