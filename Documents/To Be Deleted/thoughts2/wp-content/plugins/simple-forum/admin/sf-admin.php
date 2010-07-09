<?php
/*
Simple:Press Forum
Admin Panels
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINIMAGES', SF_PLUGIN_URL . '/admin/images/');

define('SFADMINFORUM',       	'admin.php?page=simple-forum/admin/sf-adminforums.php');
define('SFADMINOPTION',      	'admin.php?page=simple-forum/admin/sf-adminoptions.php');
define('SFADMINCOMPONENT',   	'admin.php?page=simple-forum/admin/sf-admincomponents.php');
define('SFADMINUSERGROUPS',		'admin.php?page=simple-forum/admin/sf-adminusergroups.php');
define('SFADMINPERMISSIONS', 	'admin.php?page=simple-forum/admin/sf-adminpermissions.php');
define('SFADMINDATABASE',  	  	'admin.php?page=simple-forum/admin/sf-admindatabase.php');
define('SFADMINUSERS',      	'admin.php?page=simple-forum/admin/sf-adminusers.php');
define('SFADMINADMINS',      	'admin.php?page=simple-forum/admin/sf-adminadmins.php');
define('SFADMINHELP',        	'admin.php?page=simple-forum/admin/sf-adminpopuphelp.php');

function sfa_message($message)
{
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	return;
}

function sfa_header($title, $icon)
{
	global $apage;

	$current_user = wp_get_current_user();

	# display warning message if no user groups exist
	sfa_check_warnings();

?>
	<!-- Common wrapper and header -->
	<div class="wrap sftoolbar">
		<div id="sfupdate"></div>
<?php
		if(function_exists('add_object_page'))
		{ ?>
			<div class="mainicon <?php echo($icon); ?>"></div><h2><?php echo($title); ?></h2><div class="clearboth"></div>
<?php	} ?>

		<table class="sfamenutable" border="0">
			<tr>
<?php
				$selected = '<img class="sfmenucurrent" src="'.SFADMINIMAGES.'selected.png" alt="" /><br /><br />';
				$out = '';
				if (sf_current_user_can('SPF Manage Forums'))
				{

					if ($apage == 'forums')
					{
						$current=$selected;
					} else
					{
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgforum" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINFORUM.'"><small>'.sfa_split_heading(__("Manage Forums", "sforum"),0).'</small></a></td>';
					$sep = 1;
				}
				if ($sep) $out.= '<td width="20" class="sfamenusep"></td>';

				$sep = 0; # determine if separator needed for this block of icons
				if (sf_current_user_can('SPF Manage Options'))
				{
					if ($apage == 'options')
					{
						$current=$selected;
					} else {
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgoptions" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINOPTION.'"><small>'.sfa_split_heading(__("Manage Options", "sforum"),0).'</small></a></td>';
					$sep = 1;
				}
				if (sf_current_user_can('SPF Manage Components'))
				{
					if ($apage == 'components')
					{
						$current=$selected;
					} else {
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgcomps" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINCOMPONENT.'"><small>'.sfa_split_heading(__("Manage Components", "sforum"),0).'</small></a></td>';
					$sep = 1;
				}

				if ($sep) $out.= '<td width="20" class="sfamenusep"></td>';

				$sep = 0; # determine if separator needed for this block of icons
				if (sf_current_user_can('SPF Manage User Groups'))
				{
					if ($apage == 'usergroups')
					{
						$current=$selected;
					} else {
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgusergroups" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINUSERGROUPS.'"><small>'.sfa_split_heading(__("Manage User Groups", "sforum"),0).'</small></a></td>';
					$sep = 1;
				}
				if (sf_current_user_can('SPF Manage Permissions'))
				{
					if ($apage == 'permissions')
					{
						$current=$selected;
					} else {
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgperms" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINPERMISSIONS.'"><small>'.sfa_split_heading(__("Manage Permissions", "sforum"),0).'</small></a></td>';
					$sep = 1;
				}
				if (sf_current_user_can('SPF Manage Users'))
				{
					if ($apage == 'users')
					{
						$current=$selected;
					} else {
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgusers" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINUSERS.'"><small>'.sfa_split_heading(__("Manage Users", "sforum"),0).'</small></a></td>';
					$sep = 1;
				}
				if (sf_current_user_can('SPF Manage Admins') || sf_get_member_item($current_user->ID, 'moderator'))
				{
					if ($apage == 'admins')
					{
						$current=$selected;
					} else {
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgadmins" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINADMINS.'"><small>'.sfa_split_heading(__("Manage Admins", "sforum"),0).'</small></a></td>';
					$sep = 1;
				}

				if ($sep) $out.= '<td width="20" class="sfamenusep"></td>';

				if (sf_current_user_can('SPF Manage Database'))
				{
					if ($apage == 'database')
					{
						$current=$selected;
					} else {
						$current='';
					}
					$out.= '<td class="sfamenuitem sfabgdbase" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINDATABASE.'"><small>'.sfa_split_heading(__("Manage Database", "sforum"),0).'</small></a></td>';
				}

				if ($sep) $out.= '<td width="60"></td>';

				if ($apage == 'popuphelp')
				{
					$current=$selected;
				} else {
					$current='';
				}
				$out.= '<td class="sfamenuitem sfabghelp" align="center">'.$current.'<a class="sfabutton" href="'.SFADMINHELP.'"><small>'.sfa_split_heading(__("Online Help", "sforum"), 0).'</small></a></td>';

				if ($sep) $out.= '<td width="20" class="sfamenusep"></td>';

				$out.= '<td class="sfamenuitem sfabgsupport" align="center"><a class="sfabutton" href="'.SFHOMESITE.'/support-forum/"><small>'.sfa_split_heading(__("Support Forum", "sforum"),0).'</small></a></td>';

				$site = SF_PLUGIN_URL . "/forum/ahah/sf-ahahacknowledge.php";
				$out.= '<td class="sfamenuitem sfabgabout" align="center"><a class="sfabutton" href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true, reflow: true, width: 650} )"><small>'.__("About", "sforum").'</small></a></td>';

				$out.= '<td class="sfamenuitem sfabgpaypal" align="center"><a class="sfabutton" href="'.SFHOMESITE.'/donation/"><small>'.sfa_split_heading(__("Make Donation", "sforum"),0).'</small></a></td>';

				if ($sep) $out.= '<td width="20" class="sfamenusep"></td>';

				$out.= '<td class="sfamenuitem sfabggoto" align="center"><a class="sfabutton" href="'.get_option("sfpermalink").'"><small>'.sfa_split_heading(__("Go To Forum", "sforum"),1).'</small></a></td>';

				echo $out;
?>
			</tr>
		</table>
	</div>
	<div class="clearboth"></div>
<?php

	return;
}

function sfa_footer()
{
	if(get_option('sfcheck'))
	{
		$site=SFADMINURL."ahah/sf-ahahadmintoolbox.php?item=inlinecheck";
		$target='sfupdate';
		$gif= '';
		echo '<script type="text/javascript">'."\n";
		echo 'sfjadminTool("'.$site.'", "'.$target.'","'.$gif.'");';
		echo '</script>'."\n"."\n";
	}
	return;
}

function sfa_split_heading($heading, $pos=7)
{
	$label=array();
	$label=explode(' ', $heading);
	$label[$pos].='<br />';
	$heading=implode(' ', $label);
	return $heading;
}

?>