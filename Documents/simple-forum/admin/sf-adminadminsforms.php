<?php
/*
Simple:Press Forum
Admin Admins Form Rendering
$LastChangedDate: 2009-04-18 15:41:42 +0100 (Sat, 18 Apr 2009) $
$Rev: 1729 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

global $adminhelpfile;
$adminhelpfile='admin-admins';

# function to display the form that allows admins to manage the forum administrators
function sfa_render_admins_index()
{
	global $wpdb;
?>

<div class="wrap sfatag">
	<div class="sfmaincontainer">
	<?php sfa_render_admins_buttonbox(); ?>

	<div id="sfadminstabs" style="display:none">
		<ul>
			<li><a href="#T1"><span><?php _e("Your Admin/Moderator Options", "sforum"); ?></span></a></li>
			<?php if (sf_current_user_can('SPF Manage Admins')) { ?>
				<li><a href="#T2"><span><?php _e("Global Admin Options", "sforum"); ?></span></a></li>
				<li><a href="#T3"><span><?php _e("Manage Admins", "sforum"); ?></span></a></li>
			<?php } ?>
		</ul>
		<?php

		sfa_paint_options_init();

		sfa_paint_open_tab("T1");
			echo '<form action="'.SFADMINPATH.'" method="post" id="sfmyadminoptionsform" name="sfmyadminoptions">';
			echo sf_create_nonce('my-admin_options');
			sfa_paint_open_panel();
				sfa_paint_open_fieldset(__("Your Admin Options", "sforum"), 'true', 'myadmin-options');
				sfa_admin_options();
				sfa_paint_close_fieldset();
			sfa_paint_close_panel();
			echo '<br />';
			echo '<table class="sfabuttontable" style="margin-left:15px;">';
			echo '<tr>';
			echo '<td class="sfabuttonitem sfabgupdate" align="right">';
			echo '<input type="hidden" class="sfhiddeninput" name="myadminoptions" value="submit" />';
			echo '<a class="sfasmallbutton" href="javascript:document.sfmyadminoptions.submit();">';
			echo sfa_split_heading(__("Update My Admin Options", "sforum"), 1);
			echo '</a>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
			echo '</form>';
		sfa_paint_close_tab();

		if (sf_current_user_can('SPF Manage Admins'))
		{
			sfa_paint_open_tab("T2");
				echo '<form action="'.SFADMINPATH.'" method="post" id="sfadminoptionsform" name="sfadminoptions">';
				echo sf_create_nonce('forum-admin_options');
				sfa_paint_open_panel();
					sfa_paint_open_fieldset(__("Global Admin Options", "sforum"), 'true', 'admin-options');
					sfa_admin_options_form();
					sfa_paint_close_fieldset();

				sfa_paint_close_panel();
				
				$sfadminsettings=get_option('sfadminsettings');
				$sfoptions['sfdashboardposts'] = $sfadminsettings['sfdashboardposts'];
				$sfoptions['sfdashboardstats'] = $sfadminsettings['sfdashboardstats'];
				
				sfa_paint_tab_right_cell();
				sfa_paint_open_panel();
					sfa_paint_open_fieldset(__("Dashboard Options", "sforum"), 'true', 'dashboard-options');
			
						sfa_paint_checkbox(__("Display New Forum Posts in the Dashboard", "sforum"), "sfdashboardposts", $sfoptions['sfdashboardposts']);
						sfa_paint_checkbox(__("Display Forum Statistics in the Dashboard", "sforum"), "sfdashboardstats", $sfoptions['sfdashboardstats']);
			
					sfa_paint_close_fieldset();
				sfa_paint_close_panel();

				echo '<br />';
				echo '<table class="sfabuttontable" style="margin-left:15px;">';
				echo '<tr>';
				echo '<td class="sfabuttonitem sfabgupdate" align="right">';
				echo '<input type="hidden" class="sfhiddeninput" name="adminoptions" value="submit" />';
				echo '<a class="sfasmallbutton" href="javascript:document.sfadminoptions.submit();">';
				echo sfa_split_heading(__("Update Admin Options", "sforum"), 1);
				echo '</a>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				echo '</form>';
			sfa_paint_close_tab();

			sfa_paint_open_tab("T3");
				sfa_paint_open_panel();
					sfa_paint_open_fieldset(__("Current Admins", "sforum"), 'true', 'manage-admins', false);
					echo '<div class="clearboth"></div>';
					sfa_current_admins_form();
					sfa_paint_close_fieldset(false);
				sfa_paint_close_panel();

				sfa_paint_open_panel();
					sfa_paint_open_fieldset(__("Add New Admins", "sforum"), false, '', false);
					sfa_new_admins_form();
					sfa_paint_close_fieldset(false);
				sfa_paint_close_panel();

				sfa_paint_open_panel();
					sfa_paint_open_fieldset(__("WP Admins But Not SPF Admins", "sforum"), false, '', false);
					sfa_display_wp_admins();
					sfa_paint_close_fieldset(false);
				sfa_paint_close_panel();
			sfa_paint_close_tab();
		}
?>
	</div>
</div>
</div>
<?php
	return;
}

function sfa_render_admins_buttonbox()
{
	$out = '<br /><div class="clearboth"></div>';
	echo $out;
	return;
}

function sfa_admin_options()
{
	global $current_user;

	# admin settings group
	$sfadminsettings = array();
	$sfadminsettings = get_option('sfadminsettings');

	$sfoptions = sf_get_member_item($current_user->ID, 'admin_options');

	# make sure these options make sense
	if($sfadminsettings['sfqueue'])
	{
		sfa_paint_checkbox(__("Display the Admin Bar", "sforum"), "sfadminbar", $sfoptions['sfadminbar']);
		sfa_paint_checkbox(__("Display Admin Bar Fixed at Top (if shown)", "sforum"), "sfbarfix", $sfoptions['sfbarfix']);
	}
	sfa_paint_checkbox(__("Receive Email Notification on new Topic/Post", "sforum"), "sfnotify", $sfoptions['sfnotify']);
	sfa_paint_checkbox(__("Display Unread Posts on Front Page", "sforum").' '.__("(When Admin Bar is turned off)", "sforum"), "sfshownewadmin", $sfoptions['sfshownewadmin']);

	return;
}

function sfa_current_admins_form()
{
	global $current_user;

	$admins = sf_get_admins();
	if ($admins)
	{
?>
		<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfupdatecaps">
		<?php echo(sf_create_nonce('forum-adminform_sfupdatecaps')); ?>
		<table class="sfsubtable" cellpadding="0" cellspacing="0">
			<tr>
				<th align="center" width="70"><?php _e("User ID", "sforum"); ?></th>
				<th align="left" scope="col"><?php _e("Admin Name", "sforum") ?></th>
				<th align="center" width="10" scope="col"></th>
				<th align="center" width="600" scope="col"><?php _e("Admin Capabilities", "sforum") ?></th>
			</tr>
			<?php
			foreach ($admins as $admin) {
				$user = new WP_User($admin['id']);
				$manage_opts = $user->has_cap('SPF Manage Options') ? 1 : 0;
				$manage_forums = $user->has_cap('SPF Manage Forums') ? 1 : 0;
				$manage_ugs = $user->has_cap('SPF Manage User Groups') ? 1 : 0;
				$manage_perms = $user->has_cap('SPF Manage Permissions') ? 1 : 0;
				$manage_comps = $user->has_cap('SPF Manage Components') ? 1 : 0;
				$manage_db = $user->has_cap('SPF Manage Database') ? 1 : 0;
				$manage_users = $user->has_cap('SPF Manage Users') ? 1 : 0;
				$manage_admins = $user->has_cap('SPF Manage Admins') ? 1 : 0;
			?>
			<tr>
				<td align="center"><?php echo($admin['id']); ?></td>
				<td>
					<strong><?php echo(stripslashes(attribute_escape($admin['display_name']))); ?></strong>
					<input type="hidden" name="uids[]" value="<?php echo($admin['id']); ?>" />
				</td>
				<td align="center"></td>
				<td align="center">
					<table width="100%" class="sfsubsubtable">
						<tr>
							<td>
								<?php sfa_caps_checkbox(__("Manage Options", "sforum"), "manage-opts[".$admin['id']."]", $manage_opts, $admin['id']); ?>
								<input type="hidden" name="old-opts[<?php echo $admin['id'] ?>]" value="<?php echo $manage_opts; ?>" />
							</td>
							<td>
								<?php sfa_caps_checkbox(__("Manage Forums", "sforum"), "manage-forums[".$admin['id']."]", $manage_forums, $admin['id']); ?>
								<input type="hidden" name="old-forums[<?php echo $admin['id'] ?>]" value="<?php echo $manage_forums; ?>" />
							</td>
							<td>
								<?php sfa_caps_checkbox(__("Manage User Groups", "sforum"), "manage-ugs[".$admin['id']."]", $manage_ugs, $admin['id']); ?>
								<input type="hidden" name="old-ugs[<?php echo $admin['id'] ?>]" value="<?php echo $manage_ugs; ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<?php sfa_caps_checkbox(__("Manage Permissions", "sforum"), "manage-perms[".$admin['id']."]", $manage_perms, $admin['id']); ?>
								<input type="hidden" name="old-perms[<?php echo $admin['id'] ?>]" value="<?php echo $manage_perms; ?>" />
							</td>
							<td>
								<?php sfa_caps_checkbox(__("Manage Components", "sforum"), "manage-comps[".$admin['id']."]", $manage_comps, $admin['id']); ?>
								<input type="hidden" name="old-comps[<?php echo $admin['id'] ?>]" value="<?php echo $manage_comps; ?>" />
							</td>
							<td>
								<?php sfa_caps_checkbox(__("Manage Database", "sforum"), "manage-db[".$admin['id']."]", $manage_db, $admin['id']); ?>
								<input type="hidden" name="old-db[<?php echo $admin['id'] ?>]" value="<?php echo $manage_db; ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<?php sfa_caps_checkbox(__("Manage Users", "sforum"), "manage-users[".$admin['id']."]", $manage_users, $admin['id']); ?>
								<input type="hidden" name="old-users[<?php echo $admin['id'] ?>]" value="<?php echo $manage_users; ?>" />
							</td>
							<td>
								<?php 
								if ($admin['id'] == $current_user->id) { ?>
									<img src="<?php echo SF_PLUGIN_URL.'/admin/images/locked.png'; ?>" alt="" style="vertical-align:middle;padding-right:8px;" />
									<input type="hidden" name="manage-admins[<?php echo $admin['id'] ?>]" value="<?php echo $manage_admins; ?>" />

<?php
									echo __("Manage Admins", "sforum");
								} else {
									sfa_caps_checkbox(__("Manage Admins", "sforum"), "manage-admins[".$admin['id']."]", $manage_admins, $admin['id'], $disabled);
								}
?>
								<input type="hidden" name="old-admins[<?php echo $admin['id'] ?>]" value="<?php echo $manage_admins; ?>" />
							</td>
							<td>
							</td>
						</tr>
						</table>
				</td>
			</tr>
			<?php } ?>
			<tr style="height:60px">
				<td colspan="4" align="center">
					<?php
					echo '<table class="sfabuttontable">';
					echo '<tr>';
					echo '<td class="sfabuttonitem sfabgaddusergroup" align="right">';
					echo '<input type="hidden" class="sfhiddeninput" name="updatecaps" value="submit" />';
					echo '<a class="sfasmallbutton" href="javascript:document.sfupdatecaps.submit();">';
					echo sfa_split_heading(__("Update Admin Capabilities", "sforum"), 1);
					echo '</a>';
					echo '</td>';
					echo '</tr>';
					echo '</table>';
			?>
				</td>
			</tr>
		</table>
		</form>
<?php
	}
}

function sfa_new_admins_form()
{
	global $wpdb;
?>
	<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfaddadmins">
	<?php echo(sf_create_nonce('forum-adminform_sfaddadmins')); ?>
	<table align="center" class="forum-table" cellpadding="0" cellspacing="0">
		<tr>
			<th align="center"><?php _e("Select New Admin Users", "sforum"); ?></th>
			<th align="center" width="70" scope="col"></th>
			<th align="center" width="175" scope="col"><?php _e("Select New Admin Capabilities", "sforum") ?></th>
	    </tr>
	    <tr>
	    	<td align="center">
				<select multiple="multiple" class="sfacontrol" name="newadmins[]" size="18">
				<?php
				$users = $wpdb->get_results("SELECT user_id, display_name FROM ".SFMEMBERS." WHERE admin = 0 ORDER BY display_name", ARRAY_A);
				$out = '';
				for ($x=0; $x<count($users); $x++)
				{
					$out.='<option value="'.stripslashes(attribute_escape($users[$x]['user_id'])).'">'.stripslashes(attribute_escape($users[$x]['display_name'])).'</option>'."\n";
				}
				echo $out;
				?>
				</select>
	    	</td>
	    	<td></td>
	    	<td>
	    		<table class="form-table">
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage Options", "sforum"), "add-opts", 0); ?></td></tr>
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage Forums", "sforum"), "add-forums", 0); ?></td></tr>
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage User Groups", "sforum"), "add-ugs", 0); ?></td></tr>
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage Permissions", "sforum"), "add-perms", 0); ?></td></tr>
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage Components", "sforum"), "add-comps", 0); ?></td></tr>
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage Database", "sforum"), "add-db", 0); ?></td></tr>
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage Users", "sforum"), "add-users", 0); ?></td></tr>
					<tr><td class="sflabel"><?php sfa_caps_checkbox(__("Manage Admins", "sforum"), "add-admins", 0); ?></td></tr>
				</table>
			</td>
		</tr>
		<tr style="height:60px">
			<td colspan="3" align="center">
				<?php
				echo '<table class="sfabuttontable">';
				echo '<tr>';
				echo '<td class="sfabuttonitem sfabgaddadmin" align="right">';
				echo '<input type="hidden" class="sfhiddeninput" name="addadmins" value="submit" />';
				echo '<a class="sfasmallbutton" href="javascript:document.sfaddadmins.submit();">';
				echo sfa_split_heading(__("Add New Admins", "sforum"), 1);
				echo '</a>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				?>
			</td>
		</tr>
	</table>
	</form>
<?php
}

function sfa_display_wp_admins()
{
	global $wpdb;
?>
	<table align="center" class="sfmaintable" cellpadding="0" cellspacing="0" style="width:auto">
		<tr>
			<th align="center" width="30" scope="col"></th>
			<th align="center"><?php _e("User ID", "sforum"); ?></th>
			<th align="center" scope="col"><?php _e("Admin Name", "sforum") ?></th>
			<th align="center" width="30" scope="col"></th>
		</tr>
		<?php
		$wp_admins = new WP_User_Search('', '', 'administrator');
		$is_users = false;
		for ($x=0; $x<count($wp_admins->results); $x++)
		{
			$query = "SELECT display_name FROM ".SFMEMBERS." WHERE admin = 0 AND user_id = ".$wp_admins->results[$x];
			$username = $wpdb->get_var($query);
			if ($username)
			{
				echo '<tr>';
				echo '<td></td>';
				echo '<td align="center">';
				echo $wp_admins->results[$x];
				echo '</td>';
				echo '<td>';
				echo stripslashes(attribute_escape($username));
				echo '</td>';
				echo '<td></td>';
				echo '</tr>';
				$is_users = true;
			}
		}
		if (!$is_users)
		{
			echo '<tr>';
			echo '<td></td>';
			echo '<td colspan="2">';
			echo __('No WP administrators that are not SPF admins were found', 'sforum');
			echo '</td>';
			echo '<td></td>';
			echo '</tr>';
		}
		?>
	</table>
<?php
}

function sfa_admin_options_form()
{
	# admin settings group
	$sfadminsettings=array();
	$sfadminsettings=get_option('sfadminsettings');
	$sfoptions['sfmodasadmin'] = $sfadminsettings['sfmodasadmin'];
	$sfoptions['sfshowmodposts'] = $sfadminsettings['sfshowmodposts'];
	$sfoptions['sftools'] = $sfadminsettings['sftools'];
	$sfoptions['sfqueue'] = $sfadminsettings['sfqueue'];
	$sfoptions['sfbaronly'] = $sfadminsettings['sfbaronly'];

	$sfoptions['sfdashboardposts'] = $sfadminsettings['sfdashboardposts'];
	$sfoptions['sfdashboardstats'] = $sfadminsettings['sfdashboardstats'];

	sfa_paint_checkbox(__("Use the Admins New Post Queue", "sforum"), "sfqueue", $sfoptions['sfqueue']);
	sfa_paint_checkbox(__("Force Queue Removal From Admins Bar Only", "sforum"), "sfbaronly", $sfoptions['sfbaronly']);
	sfa_paint_checkbox(__("Allow Moderators to Remove New Posts from Admins Unread queue", "sforum"), "sfmodasadmin", $sfoptions['sfmodasadmin']);
	sfa_paint_checkbox(__("Include Posts by Moderators in list", "sforum"), "sfshowmodposts", $sfoptions['sfshowmodposts']);
	sfa_paint_checkbox(__("Display Admin Tool Icons", "sforum"), "sftools", $sfoptions['sftools']);

	return;
}

function sfa_caps_checkbox($label, $name, $value, $user=0, $disabled=0)
{
	$pos = strpos($name, '[');
	if ($pos) $thisid = substr($name, 0, $pos).$user; else $thisid = $name.$user;
	echo "<label for='sf-$thisid'>$label</label>";
	echo "<input type='checkbox' name='$name' id='sf-$thisid' ";
	if ($value) echo "checked='checked' ";
	if ($disabled) echo "readonly='readonly' ";
	echo '/>';
	return;
}

?>