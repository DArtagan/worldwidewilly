<?php
/*
Simple:Press Forum
Admin Permissions Form Rendering
$LastChangedDate: 2009-01-24 21:55:00 +0000 (Sat, 24 Jan 2009) $
$Rev: 1311 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

#=== ROLES RELATED

function sfa_render_roles_index()
{
	global $sfactions;
?>
<div class="wrap sfatag">

	<div class="sfmaincontainer">
<?php
		sfa_render_roles_buttonbox();

			# the following function call displays the create new permission set form
			# however, its hidden until the create permission set link is clicked
			# the form will be displayed at above the permission set list
			sfa_add_permission_form();

		$roles = sfa_get_all_roles();
		if($roles)
		{
			# display the permission set roles in table format
?>
			<table class="sfsubtable" cellpadding="0" cellspacing="0">
				<tr>
					<th align="center" width="9%" scope="col"><?php _e("Permission Set ID", "sforum") ?></th>
					<th align="left" scope="col"><?php _e("Permission Set Name", "sforum") ?></th>
					<th align="center" width="5%" scope="col"></th>
					<th align="center" width="15%" scope="col"></th>
				</tr>
<?php
				foreach($roles as $role)
				{
?>
				<tr>
					<td align="center"><?php echo($role->role_id); ?></td>
					<td align="left"><strong><?php echo(stripslashes($role->role_name)); ?></strong><br /><small><?php echo($role->role_desc); ?></small></td>
					<td align="center"></td>
					<td align="center">
						<?php
						echo '<table class="sfabuttontable" align="right">';
						echo '<tr>';
						echo '<td class="sfabuttonitem sfabgedit" align="right">';
						echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eperm-'.$role->role_id.'\');">';
						echo __("Edit", "sforum");
						echo '</a>';
						echo '</td>';
						echo '<td />';
						echo '<td class="sfabuttonitem sfabgdelete" align="right">';
						echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dperm-'.$role->role_id.'\');">';
						echo __("Delete", "sforum");
						echo '</a>';
						echo '</td>';
						echo '</tr>';
						echo '</table>';
						?>
					</td>
				</tr>
				<tr> <!-- This row will hold hidden forms for the current permission set -->
				  	<td class="sfinline-form" colspan="5">
<?php
						# the following function call displays the edit permission set form
						# however, its hidden until the edit permission set link is clicked
						# the form will be displayed below the current permission set information
						sfa_edit_permission_form($role);

						# the following function call displays the delete permission set form
						# however, its hidden until the delete permission set link is clicked
						# the form will be displayed below the current permission set information
						sfa_delete_permission_form($role);
?>
					</td>
				</tr>
<?php	} ?>
		</table>
		<br />
<?php
	} else {
		echo('<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.__("There are no Permission Sets defined.", "sforum").'</div>');
	}
?>
	</div>
</div>
<?php
	return;
}

function sfa_render_roles_buttonbox()
{
	$out = '<table class="sfaactiontable" border="0"><tr><td class="sfamenuitem sfabgnewperms" align="right"><a class="sfasmallbutton" href="#" onclick="sfjtoggleLayer(\'cperm\');"><small>'.sfa_split_heading(__("Add Permission Set", "sforum"),0).'</small></a></td></tr></table>';
	echo $out;
?>
	<br /><div class="clearboth"></div>
<?php
	return;
}

function sfa_add_permission_form()
{
	global $sfactions;
?>
			<div id="cperm" class="inline_edit">
				<table class="sfmaincontainer" cellpadding="0" cellspacing="0">
					<tr>
						<td width="100%">
							<fieldset class="sffieldset"><legend><?php _e("Create New Permission Set", "sforum") ?></legend>
								<?php echo(sfa_paint_help('create-new-permission-set', 'admin-permissions')); ?>
								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfrolenew">
									<?php echo(sf_create_nonce('forum-adminform_rolenew')); ?>

									<table class="form-table">
										<tr>
											<td class="sflabel"><?php _e("Permission Set Name", "sforum") ?>:&nbsp;&nbsp;<br />
											<input type="text" class="sfacontrol" size="45" name="role_name" value="" /></td>
											<td class="sflabel"><?php _e("Permission Set Description", "sforum") ?>:&nbsp;&nbsp;<br/>
											<input type="text" class="sfacontrol" size="85" name="role_desc" value="" /></td>
										</tr>
									</table>

									<br /><p><strong><?php _e("Permission Set Actions", "sforum") ?>:</strong></p>
									<?php
									echo '<p><img src="'.SFADMINURL.'images/guestperm.png" alt="" width="16" height="16" align="top" />';
									echo '<small>&nbsp;'.__("Note: Action settings displaying this icon will be ignored for Guest Users", "sforum").'</small>';
									echo '&nbsp;&nbsp;&nbsp;<img src="'.SFADMINURL.'images/globalperm.png" alt="" width="16" height="16" align="top" />';
									echo '<small>&nbsp;'.__("Note: Action settings displaying this icon require enabling to use", "sforum").'</small></p>';
									?>
									<table class="outershell" width="100%" border="0" cellspacing="3">
									<tr>
<?php
										$items = count($sfactions['action']);
										$cols = 3;
										$rows  = ($items / $cols);
										$lastrow = $rows;
										$lastcol = $cols;
										$curcol = 0;
										if (!is_int($rows))
										{
											$rows = (intval($rows) + 1);
											$lastrow = $rows - 1;
											$lastcol = ($items % $cols);
										}
										$thisrow = 0;

										foreach ($sfactions["action"] as $index => $action)
										{
											$button = 'b-'.$index;
											$ptype = $sfactions["members"][$index];

											if ($sfactions["members"][$index] != 0) {
												$span = '';
											} else {
												$span = ' colspan="2" ';
											}

											if ($thisrow == 0)
											{
												$curcol++;
?>
												<td width="33%" style="vertical-align:top">
												<table class="form-table">
<?php
											}
?>
												<tr>
													<td class="sflabel"<?php echo($span); ?>><label for="sf<?php echo $button; ?>" class="sflabel"><?php _e($action, "sforum"); ?></label>
													<input type="checkbox" name="<?php echo $button; ?>" id="sf<?php echo $button; ?>"  />
													<input type="hidden" name="action_name[]" value="<?php echo $action; ?>" /></td>
													<?php if ($span == '')
													{ ?>
														<td align="center">
													<?php } ?>
<?php
													if ($span == '') {
														if($ptype == 2)
														{
															echo '<img src="'.SFADMINURL.'images/globalperm.png" alt="" width="16" height="16" title="'.__("Requires Enabling", "sforum").'" />';
														}
														echo '<img src="'.SFADMINURL.'images/guestperm.png" alt="" width="16" height="16" title="'.__("Ignored for Guests", "sforum").'" />';
														echo '</td>';
													}
													?>

												</tr>
<?php
											$thisrow++;
											if (($curcol <= $lastcol && $thisrow == $rows) || ($curcol > $lastcol && $thisrow == $lastrow))
											{
?>
												</table>
												</td>
<?php
												$thisrow = 0;
											}
										} ?>
										</tr></table><br />

										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgaddpermission" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="newrole" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfrolenew.submit();">';
										echo sfa_split_heading(__("Create Permission", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'cperm\');">';
										echo __("Cancel", "sforum");
										echo '</a>';
										echo '</td>';
										echo '</tr>';
										echo '</table>';
										?>
									</form>
								</fieldset>
						</td>
					</tr>
				</table>
			</div>
<?php
}

# function to display the edit permission set form.  It is hidden until the edit permission set link is clicked
function sfa_edit_permission_form($role)
{
	global $sfactions;
?>
			<div id="eperm-<?php echo $role->role_id; ?>" class="inline_edit">
				<table class="sfmaincontainer" cellpadding="0" cellspacing="0">
					<tr>
						<td width="100%">
							<fieldset class="sffieldset"><legend><?php _e("Update Permission Set", "sforum") ?></legend>
								<?php echo(sfa_paint_help('edit-master-permission-set', 'admin-permissions')); ?>
								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfroleedit<?php echo $role->role_id; ?>">
									<?php echo(sf_create_nonce('forum-adminform_roleedit')); ?>
									<input type="hidden" name="role_id" value="<?php echo $role->role_id; ?>" />

									<table class="form-table">
										<tr>
											<td class="sflabel"><?php _e("Permission Set Name", "sforum") ?>:&nbsp;&nbsp;<br />
											<input type="text" class="sfacontrol" size="45" name="role_name" value="<?php echo $role->role_name; ?>" /></td>
											<td class="sflabel"><?php _e("Permission Set Description", "sforum") ?>:&nbsp;&nbsp;<br/>
											<input type="text" class="sfacontrol" size="85" name="role_desc" value="<?php echo $role->role_desc; ?>" /></td>
										</tr>
									</table>

									<br /><p><strong><?php _e("Permission Set Actions", "sforum") ?>:</strong></p>
									<?php
									echo '<p><img src="'.SFADMINURL.'images/guestperm.png" alt="" width="16" height="16" align="top" />';
									echo '<small>&nbsp;'.__("Note: Action settings displaying this icon will be ignored for Guest Users", "sforum").'</small>';
									echo '&nbsp;&nbsp;&nbsp;<img src="'.SFADMINURL.'images/globalperm.png" alt="" width="16" height="16" align="top" />';
									echo '<small>&nbsp;'.__("Note: Action settings displaying this icon require enabling to use", "sforum").'</small></p>';
									?>

									<table class="outershell" width="100%" border="0" cellspacing="3">
									<tr>
<?php
										$actions = maybe_unserialize($role->role_actions);
										$items = count($sfactions['action']);
										$cols = 3;
										$rows  = ($items / $cols);
										$lastrow = $rows;
										$lastcol = $cols;
										$curcol = 0;
										if (!is_int($rows))
										{
											$rows = (intval($rows) + 1);
											$lastrow = $rows - 1;
											$lastcol = ($items % $cols);
										}
										$thisrow = 0;

										foreach ($sfactions["action"] as $index => $action)
										{
											$button = 'b-'.$index;
											$checked="";
											if (isset($actions[$action]) && $actions[$action] == 1)
//											if ($actions[$action] == 1)
											{
												$checked= ' checked="checked"';
											}
											$ptype = $sfactions["members"][$index];
											if($sfactions["members"][$index] != 0) {
												$span = '';
											} else {
												$span = ' colspan="2" ';
											}

											if($thisrow == 0)
											{
												$curcol++;
?>
												<td width="33%" style="vertical-align:top">
												<table class="form-table">
<?php
											}
?>
												<tr>
													<td class="sflabel"<?php echo($span); ?>><label for="sfR<?php echo $role->role_id.$button; ?>" class="sflabel"><?php _e($action, "sforum"); ?></label>
													<input type="checkbox" name="<?php echo $button; ?>" id="sfR<?php echo $role->role_id.$button; ?>"<?php echo($checked); ?>  />
													<input type="hidden" name="action_name[]" value="<?php echo $action; ?>" /></td>
													<?php if ($span == '') { ?>
													<td align="center">
													<?php } ?>
<?php
													if ($span == '') {
														if($ptype == 2)
														{
															echo '<img src="'.SFADMINURL.'images/globalperm.png" alt="" width="16" height="16" title="'.__("Requires Enabling", "sforum").'" />';
														}
														echo '<img src="'.SFADMINURL.'images/guestperm.png" alt="" width="16" height="16" title="'.__("Ignored for Guests", "sforum").'" />';
														echo '</td>';
													}
													?>
												</tr>
<?php
											$thisrow++;
											if (($curcol <= $lastcol && $thisrow == $rows) || ($curcol > $lastcol && $thisrow == $lastrow))
											{
?>
												</table>
												</td>
<?php
												$thisrow=0;
											}
										} ?>
										</tr></table><br />

<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgupdate" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="editrole" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfroleedit'.$role->role_id.'.submit();">';
										echo sfa_split_heading(__("Update Permission", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eperm-'.$role->role_id.'\');">';
										echo __("Cancel", "sforum");
										echo '</a>';
										echo '</td>';
										echo '</tr>';
										echo '</table>';
?>
									</form>
								</fieldset>
						</td>
					</tr>
				</table>
			</div>

<?php
}

# function to display the delete permission set form.  It is hidden until the delete permission set link is clicked
function sfa_delete_permission_form($role)
{
	global $sfactions;
?>
						<div id="dperm-<?php echo $role->role_id; ?>"  class="inline_edit">
								<fieldset class="sffieldset"><legend><?php _e("Delete Permission Set", "sforum") ?></legend>
									<?php echo(sfa_paint_help('delete-master-permission-set', 'admin-permissions')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfroledel<?php echo $role->role_id; ?>">
										<?php echo(sf_create_nonce('forum-adminform_roledelete')); ?>

										<input type="hidden" name="role_id" value="<?php echo($role->role_id); ?>" />

										<p><?php _e("Click <strong>Confirm Deletion</strong> to completely remove this Permission Set.<br />This will remove the Permission Set and remove it from ALL Forum that used this Permission Set.<br /><br />Please note that this action <strong>can NOT be reversed</strong>.", "sforum") ?></p>

										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgconfirm" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="deleterole" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfroledel'.$role->role_id.'.submit();">';
										echo sfa_split_heading(__("Confirm Deletion", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dperm-'.$role->role_id.'\');">';
										echo __("Cancel", "sforum");
										echo '</a>';
										echo '</td>';
										echo '</tr>';
										echo '</table>';
										?>
									</form>
								</fieldset>
						</div>
<?php
}

?>