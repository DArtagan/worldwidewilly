<?php
/*
Simple:Press Forum
Admin User Group Form Rendering
$LastChangedDate: 2009-01-03 22:11:29 +0000 (Sat, 03 Jan 2009) $
$Rev: 1123 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# == USER GROUP RELATED

function sfa_render_usergroups_index()
{ ?>
<div class="wrap sfatag">
	<div class="sfmaincontainer">

<?php
    sfa_render_usergroup_buttonbox();

	# the following function call displays the create new usergroup form
	# however, its hidden until the create usergroup link is clicked
	# the form will be displayed above the usergroup list
	sfa_create_usergroup_form();

	$usergroups = sfa_get_usergroups_all(Null);
	if($usergroups)
	{
		foreach ($usergroups as $usergroup)
		{
			# display the current usergroup information in table format
?>
			<table class="sfmaintable" cellpadding="0" cellspacing="0">
				<tr>
					<th align="center" width="7%" scope="col"><?php _e("User Group ID", "sforum") ?></th>
					<th align="left" scope="col"><?php _e("User Group Name", "sforum") ?></th>
					<th align="center" width="8%" scope="col"><?php _e("Moderator", "sforum") ?></th>
					<th align="center" width="15%" scope="col"></th>
					<th align="center" width="15%" scope="col"></th>
				</tr>
				<tr>
					<td align="center"><?php echo($usergroup->usergroup_id); ?></td>
					<td align="left"><strong><?php echo(stripslashes($usergroup->usergroup_name)); ?></strong><br /><small><?php echo(stripslashes($usergroup->usergroup_desc)); ?></small></td>
					<td align="center"><?php if ($usergroup->usergroup_is_moderator == 1) echo _e("Yes", "sforum"); else echo _e("No", "sforum"); ?></td>
					<td align="center" colspan="2">
						<?php
						echo '<table class="sfabuttontable" align="right">';
						echo '<tr>';
						echo '<td class="sfabuttonitem sfabgedit" align="right">';
						echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eusergroup-'.$usergroup->usergroup_id.'\');">';
						echo sfa_split_heading(__("Edit", "sforum"), 0);
						echo '</a>';
						echo '</td>';
						echo '<td />';
						echo '<td class="sfabuttonitem sfabgdelete" align="right">';
						echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dusergroup-'.$usergroup->usergroup_id.'\');">';
						echo __("Delete", "sforum");
						echo '</a>';
						echo '</td>';
						echo '</tr>';
						echo '</table>';
						?>
					</td>
				</tr>
				<tr> <!-- This row will hold hidden forms for the current user group -->
				  	<td class="sfinline-form" colspan="5">
<?php
						# the following function call displays the create new user group form
						# however, its hidden until the create user group link is clicked
						# the form will be displayed below the current user group information
						sfa_edit_usergroup_form($usergroup);

						# the following function call displays the delete new user group form
						# however, its hidden until the delete user group link is clicked
						# the form will be displayed below the current user group information
						sfa_delete_usergroup_form($usergroup);
?>
					</td>
				</tr>
				<tr class="sfsubtable">
					<td align="center" valign="top"><small><?php _e("Members<br />in this<br />User Group:", "sforum") ?></small></td>
					<td align="left" valign="top" colspan="2">
<?php
					$site = SF_PLUGIN_URL . "/admin/ahah/sf-ahahadminusergroups.php?ug=".$usergroup->usergroup_id;
					$gif= SFADMINURL."images/working.gif";
					$showtext = __("Show Members", "sforum");
					$hidetext = __("Hide Members", "sforum");
					echo '<table class="sfabuttontable">'."\n";
					echo '<tr>'."\n";
					echo '<td class="sfabuttonitem sfabgshowmembers" align="right">'."\n";
					echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjshowMemberList(\''.$site.'\', \''.$gif.'\', \''.$usergroup->usergroup_id.'\', \''.$showtext.'\', \''.$hidetext.'\');">'."\n";
					echo '<span id="show'.$usergroup->usergroup_id.'">'.$showtext.'</span>'."\n";
					echo '</a>'."\n";
					echo '</td>'."\n";
					echo '</tr>'."\n";
					echo '</table>'."\n";
?>
					<div  id="ugrouplist<?php echo($usergroup->usergroup_id); ?>"></div>
					</td>
					<td align="center" valign="top">
<?php
					$site = SF_PLUGIN_URL . "/admin/ahah/sf-ahahadminusergroups.php?add=".$usergroup->usergroup_id;
					$gif= SFADMINURL."images/working.gif";
					echo '<table class="sfabuttontable">'."\n";
					echo '<tr>'."\n";
					echo '<td class="sfabuttonitem sfabgaddmember" align="right">'."\n";
					echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjshowAddMemberList(\''.$site.'\', \''.$gif.'\', \''.$usergroup->usergroup_id.'\');">'."\n";
					echo '<span id="add'.$usergroup->usergroup_id.'">'.sfa_split_heading(__('Add Members', 'sforum'), 0).'</span>'."\n";
					echo '</a>'."\n";
					echo '</td>'."\n";
					echo '</tr>'."\n";
					echo '</table>'."\n";
					sfa_add_member_form($usergroup);
?>
					</td>
					<td align="center" valign="top">
<?php
					$site = SF_PLUGIN_URL . "/admin/ahah/sf-ahahadminusergroups.php?del=".$usergroup->usergroup_id;
					$gif= SFADMINURL."images/working.gif";
					echo '<table class="sfabuttontable">'."\n";
					echo '<tr>'."\n";
					echo '<td class="sfabuttonitem sfabgdelmember" align="right">'."\n";
					echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjshowDelMemberList(\''.$site.'\', \''.$gif.'\', \''.$usergroup->usergroup_id.'\');">'."\n";
					echo '<span id="remove'.$usergroup->usergroup_id.'">'.sfa_split_heading(__('Move Or Delete Members', 'sforum'), 1).'</span>'."\n";
					echo '</a>'."\n";
					echo '</td>'."\n";
					echo '</tr>'."\n";
					echo '</table>'."\n";
					sfa_delete_member_form($usergroup);
?>
					</td>
				</tr>
				<tr> <!-- This row will hold hidden forms for the current user group membership-->
				  	<td class="sfinline-form" colspan="5">
					</td>
				</tr>
			</table>
<?php 	} ?>
<?php
	} else {
		echo('<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.__("There are no User Groups defined", "sforum").'</div>');
	}
?>
	</div>
</div>
<?php
	return;
}

function sfa_render_usergroup_buttonbox()
{
	$out = '<table class="sfaactiontable" border="0"><tr><td class="sfamenuitem sfabgnewuserg" align="right"><a class="sfasmallbutton" href="#" onclick="sfjtoggleLayer(\'cusergroup\');"><small>'.sfa_split_heading(__("Create User Group", "sforum"),0).'</small></a></td></tr></table>';
	echo $out;
?>
	<br /><div class="clearboth"></div>
<?php
	return;
}

# function to display the create user group form.  It is hidden until the create user group link is clicked
function sfa_create_usergroup_form()
{ ?>
			<div id="cusergroup" class="inline_edit">
				<table cellpadding="5" cellspacing="3">
					<tr>
						<td>
								<fieldset class="sffieldset"><legend><?php _e("Create New User Group", "sforum") ?></legend>
									<?php echo(sfa_paint_help('create-new-user-group', 'admin-usergroups')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfusergroupnew">
										<?php echo(sf_create_nonce('forum-adminform_usergroupnew')); ?>
								<table class="form-table">
									<tr>
										<td class="sflabel"><?php _e("User Group Name", "sforum") ?>:</td>
										<td><input type="text" class="sfacontrol" size="45" name="usergroup_name" value="" /></td>
									</tr><tr>
										<td class="sflabel"><?php _e("User Group Description", "sforum") ?>:&nbsp;&nbsp;</td>
										<td><input type="text" class="sfacontrol" size="85" name="usergroup_desc" value="" /></td>
									</tr><tr>
					<td class="sflabel" colspan="2"><label for="sfusergroup_is_moderator" class="sflabel"><?php _e("Is Moderator", "sforum") ?>&nbsp;&nbsp;</label>
										<input type="checkbox" name="usergroup_is_moderator" id="sfusergroup_is_moderator" value="1" />
										<?php _e("(Indicates that members of this User Group are considered moderators)", "sforum") ?></td>
									</tr>
								</table>
									<div class="clearboth"></div>
									<?php
									echo '<table class="sfabuttontable">';
									echo '<tr>';
									echo '<td class="sfabuttonitem sfabgaddusergroup" align="right">';
									echo '<input type="hidden" class="sfhiddeninput" name="newusergroup" value="submit" />';
									echo '<a class="sfasmallbutton" href="javascript:document.sfusergroupnew.submit();">';
									echo sfa_split_heading(__("Add User Group", "sforum"), 0);
									echo '</a>';
									echo '</td>';
									echo '<td />';
									echo '<td class="sfabuttonitem sfabgcancel" align="right">';
									echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'cusergroup\');">';
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

# function to display the edit user group form.  It is hidden until the edit user group link is clicked
function sfa_edit_usergroup_form($usergroup)
{ ?>
						<div id="eusergroup-<?php echo $usergroup->usergroup_id; ?>"  class="inline_edit">
								<fieldset class="sffieldset"><legend><?php _e("Edit User Group", "sforum") ?></legend>
									<?php echo(sfa_paint_help('edit-user-group', 'admin-usergroups')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>">
										<?php echo(sf_create_nonce('forum-adminform_usergroupedit')); ?>
										<input type="hidden" name="usergroup_id" value="<?php echo($usergroup->usergroup_id); ?>" />
										<input type="hidden" name="ugroup_name" value="<?php echo(stripslashes($usergroup->usergroup_name)); ?>" />
										<input type="hidden" name="ugroup_desc" value="<?php echo(stripslashes($usergroup->usergroup_desc)); ?>" />
										<input type="hidden" name="ugroup_ismod" value="<?php echo(stripslashes($usergroup->usergroup_is_moderator)); ?>" />
										<table class="form-table">
											<tr>
												<td class="sflabel"><?php _e("User Group Name", "sforum") ?>:</td>
												<td><input type="text" class="sfacontrol" size="45" name="usergroup_name" value="<?php echo(stripslashes($usergroup->usergroup_name)); ?>" /></td>
											</tr><tr>
												<td class="sflabel"><?php _e("User Group Description", "sforum") ?>:&nbsp;</td>
												<td><input type="text" class="sfacontrol" size="85" name="usergroup_desc" value="<?php echo(stripslashes($usergroup->usergroup_desc)); ?>" /></td>
											</tr><tr>
										<td class="sflabel" colspan="2"><label for="sfusergroup_is_moderator_<?php echo($usergroup->usergroup_id); ?>"><?php _e("Is Moderator", "sforum") ?>&nbsp;&nbsp;</label>
									<input type="checkbox" name="usergroup_is_moderator" id="sfusergroup_is_moderator_<?php echo($usergroup->usergroup_id); ?>" value="1" <?php if ($usergroup->usergroup_is_moderator == 1) echo 'checked="checked"'; ?>/>
												<?php _e("(Indicates that members of this User Group are considered Moderators)", "sforum") ?></td>
											</tr>
										</table>
										<div class="clearboth"></div>
										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgupdate" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="updateusergroup" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfusergroupedit'.$usergroup->usergroup_id.'.submit();">';
										echo sfa_split_heading(__("Update User Group", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eusergroup-'.$usergroup->usergroup_id.'\');">';
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

# function to display the delete user group form.  It is hidden until the delete user group link is clicked
function sfa_delete_usergroup_form($usergroup)
{ ?>
						<div id="dusergroup-<?php echo $usergroup->usergroup_id; ?>"  class="inline_edit">
								<fieldset class="sffieldset"><legend><?php _e("Delete User Group", "sforum") ?></legend>
									<?php echo(sfa_paint_help('delete-user-group', 'admin-usergroups')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfusergroupdel<?php echo $usergroup->usergroup_id; ?>">
										<?php echo(sf_create_nonce('forum-adminform_usergroupdelete')); ?>

										<input type="hidden" name="usergroup_id" value="<?php echo($usergroup->usergroup_id); ?>" />

										<p><?php _e("Click <strong>Confirm Deletion</strong> to completely remove this User Group.<br />This will remove ALL user memberships contained in this User Group.<br /><br />Please note that this action <strong>can NOT be reversed</strong>.", "sforum") ?></p>

										<div class="clearboth"></div>
										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgconfirm" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="deleteusergroup" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfusergroupdel'.$usergroup->usergroup_id.'.submit();">';
										echo sfa_split_heading(__("Delete User Group", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dusergroup-'.$usergroup->usergroup_id.'\');">';
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

# function to display the add members form.  It is hidden until the add members link is clicked
function sfa_add_member_form($usergroup)
{ ?>
						<div id="amember-<?php echo $usergroup->usergroup_id; ?>"  class="inline_edit" style="margin: 3px; padding: 2px;">
								<fieldset class="sffieldset" style="min-width:185px;"><legend><?php _e("Add Members", "sforum") ?></legend>
									<?php echo(sfa_paint_help('add-members', 'admin-usergroups')); ?>
	  								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfmembernew<?php echo $usergroup->usergroup_id; ?>">
<?php									echo(sf_create_nonce('forum-adminform_membernew')); ?>

										<input type="hidden" name="usergroup_id" value="<?php echo($usergroup->usergroup_id); ?>" />
										<p><?php _e("Select Members To Add (use CONTROL for multiple users)", "sforum") ?></p>
										<div id="selectadd<?php echo($usergroup->usergroup_id); ?>">
										</div>
										<div class="clearboth"></div>
<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgaddmember" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="membernew" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfmembernew'.$usergroup->usergroup_id.'.submit();">';
										echo sfa_split_heading(__("Add Members", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'amember-'.$usergroup->usergroup_id.'\');">';
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

# function to display the delete members form.  It is hidden until the delete members link is clicked
function sfa_delete_member_form($usergroup)
{ ?>
						<div id="dmember-<?php echo $usergroup->usergroup_id; ?>"  class="inline_edit" style="margin: 3px; padding: 2px;">
								<fieldset class="sffieldset" style="min-width:185px;"><legend><?php _e("Move / Delete Members", "sforum") ?></legend>
									<?php echo(sfa_paint_help('move-delete-members', 'admin-usergroups')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfmemberdel<?php echo $usergroup->usergroup_id; ?>">
<?php									echo(sf_create_nonce('forum-adminform_memberdel')); ?>

										<input type="hidden" name="usergroupid" value="<?php echo($usergroup->usergroup_id); ?>" />
										<p><?php _e("Select Members To Move / Delete (use CONTROL for multiple users)", "sforum") ?></p>
										<div id="selectdel<?php echo($usergroup->usergroup_id); ?>">
										</div>

										<p><?php _e("To Move Members, Select New User Group", "sforum") ?></p>
										<?php sfa_display_usergroup_select() ?>
										<div class="clearboth"></div>
										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgdelmember" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="memberdel" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfmemberdel'.$usergroup->usergroup_id.'.submit();">';
										echo sfa_split_heading(__("Move or Delete Members", "sforum"), 1);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dmember-'.$usergroup->usergroup_id.'\');">';
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