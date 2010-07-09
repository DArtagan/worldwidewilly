<?php
/*
Simple:Press Forum
Admin Forum Form Rendering
$LastChangedDate: 2009-01-16 17:02:24 +0000 (Fri, 16 Jan 2009) $
$Rev: 1223 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

#=== GROUP RELATED

# render the group/forum management page

function sfa_render_forum_index()
{ ?>
<div class="wrap sfatag">
	<div class="sfmaincontainer">

<?php
	$groups = sf_get_groups_all();
	?>
	<?php

    sfa_render_forum_buttonbox($groups);

	# the following function call displays the add global permission set form
	# however, its hidden until the add global permission set link is clicked
	# the form will be displayed at above the group / forum list
	sfa_add_global_permission_form();

	# the following function call displays the remove all permission set form
	# however, its hidden until the remove all permission set link is clicked
	# the form will be displayed at above the group / forum list
	sfa_remove_all_permissions_form();

	# the following function call displays the create a new group form
	# however, its hidden until the create new group link is clicked
	# the form will be displayed at above the group / forum list
	sfa_create_group_form();

	# the following function call displays the create a new forum form
	# however, its hidden until the create new forum link is clicked
	# the form will be displayed at above the group / forum list
	sfa_create_forum_form();

	if ($groups)
	{
		foreach ($groups as $group)
		{
			# display the current group information in table format
?>
			<table class="sfmaintable" cellpadding="0" cellspacing="0">
				<tr> <!-- display group table header information -->
					<th align="center" width="40"><?php _e("Icon", "sforum"); ?></th>
					<th align="center" width="31"><?php _e("ID", "sforum"); ?></th>
					<th align="left" scope="col"><?php _e("Group Name", "sforum") ?></th>
					<th align="center" width="20" scope="col"></th>
					<th align="center" width="60" scope="col"><?php _e("Sequence", "sforum") ?></th>
					<th align="center" width="30%" scope="col"></th>
					<th align="center" width="35" scope="col"><?php _e("Status", "sforum"); ?></th>

					<th align="center" width="35" scope="col"><?php _e("RSS", "sforum"); ?></th>
				</tr>
				<tr> <!-- display group information for each group -->
					<?php
						if(empty($group->group_icon))
						{
							$icon = SFRESOURCES.'group.png';
						} else {
							$icon = SFRESOURCES.'custom/'.$group->group_icon;
							if (!file_exists($icon))
							{
								$icon = SFRESOURCES.'group.png';
							}
						}
					?>
					<td align="center">
						<?php
						$site=SFADMINURL."ahah/sf-ahahadmin.php?action=icon&amp;item=Group&amp;id=".$group->group_id."&amp;deficon=".$group->group_icon;
						echo '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.$icon.'" alt="" title="'.__("Set Custom Icon", "sforum").'" /></a>';
						?>
					</td>
					<td align="center"><?php echo($group->group_id); ?></td>
					<td align="left"><strong><?php echo(stripslashes($group->group_name)); ?></strong><br /><small><?php echo(stripslashes($group->group_desc)); ?></small></td>
					<td></td>
					<td align="center"><?php echo($group->group_seq); ?></td>
					<td align="center">
						<?php
						echo '<table class="sfabuttontable" align="right">';
						echo '<tr>';
						echo '<td class="sfabuttonitem sfabgupdate" align="right">';
						echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'gperm-'.$group->group_id.'\');">';
						echo sfa_split_heading(__("Add Group Permission", "sforum"), 1);
						echo '</a>';
						echo '</td>';
						?>
						<?php
						echo '<td />';
						echo '<td class="sfabuttonitem sfabgedit" align="right">';
						echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'egroup-'.$group->group_id.'\');">';
						echo __("Edit", "sforum");
						echo '</a>';
						echo '</td>';
						?>
						<?php
						echo '<td />';
						echo '<td class="sfabuttonitem sfabgdelete" align="right">';
						echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dgroup-'.$group->group_id.'\');">';
						echo __("Delete", "sforum");
						echo '</a>';
						echo '</td>';
						echo '</tr>';
						echo '</table>';
						?>
					</td>

					<td align="center">
						<?php
						$site=SFADMINURL."ahah/sf-ahahadmin.php?action=tss&amp;item=Group&amp;id=".$group->group_id;
                        echo '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFADMINURL.'images/status_unset.png" alt="" title="'.__("Assign Topic Status Set", "sforum").'" /></a>';
						?>
					</td>

					<td align="center">
						<?php
						$site=SFADMINURL."ahah/sf-ahahadmin.php?action=rss&amp;item=Group&amp;id=".$group->group_id."&amp;url=".$group->group_rss;
						if(empty($group->group_rss))
						{
							$icon = 'feed.png';
						} else {
							$icon = 'feed_custom.png';
						}
						echo '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFADMINURL.'images/'.$icon.'" alt="" title="'.__("Set RSS Feed URL", "sforum").'" /></a>';
						?>
					</td>
				</tr>
				<tr>  <!-- This row will hold hidden forms for the current group -->
				  	<td class="sfinline-form" colspan="10">
<?php
					# the following function call displays the add group global permission set form
					# however, its hidden until the add group permission set link is clicked
					# the form will be displayed below the current group information
					sfa_add_group_permission_form($group);

					# the following function call displays the edit group form
					# however, its hidden until the edit group link is clicked
					# the form will be displayed below the current group information
					sfa_edit_group_form($group);

					# the following function call displays the delete group form
					# however, its hidden until the delete group link is clicked
					# the form will be displayed below the current group information
					sfa_delete_group_form($group);
?>
					</td>
				</tr>
			</table>
<?php
			$forums = sfa_get_forums_in_group($group->group_id);
			if ($forums)
			{
				# display the current forum information for each forum in table format
?>
				<table  class="sfsubtable" cellpadding="0" cellspacing="0">
					<tr> <!-- display forum table header information -->
						<th align="center" width="40"></th>
						<th align="center" width="31"><?php _e("ID", "sforum"); ?></th>
						<th align="left" scope="col"><?php _e("Forum Name", "sforum") ?></th>
						<th align="center" width="20" scope="col"></th>
						<th align="center" width="60" scope="col"><?php _e("Sequence", "sforum") ?></th>
						<th align="center" width="30%" scope="col"></th>
						<th align="center" width="35" scope="col"></th>
						<th align="center" width="35" scope="col"></th>
					</tr>
<?php
					foreach ($forums as $forum)
					{
						$locked = '';
						if ($forum->forum_status) $locked=__("Locked", "sforum");
?>
						<tr> <!-- display forum information for each forum -->
							<?php
								if(empty($forum->forum_icon))
								{
									$icon = SFRESOURCES.'forum.png';
								} else {
									$icon = SFRESOURCES.'custom/'.$forum->forum_icon;
									if (!file_exists($icon))
									{
										$icon = SFRESOURCES.'forum.png';
									}
								}
							?>
							<td align="center">
								<?php
								$site=SFADMINURL."ahah/sf-ahahadmin.php?action=icon&amp;item=Forum&amp;id=".$forum->forum_id."&amp;deficon=".$forum->forum_icon;
								echo '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.$icon.'" alt="" title="'.__("Set Custom Icon", "sforum").'" /></a>';
								?>
							</td>
							<td align="center"><?php echo($forum->forum_id); ?></td>
							<td align="left"><strong><?php echo(stripslashes($forum->forum_name)); ?></strong><br /><small><?php echo(stripslashes($forum->forum_desc)); ?></small></td>

							<td align="center"><?php if ($forum->forum_status) echo('<img src="'.SFADMINURL.'images/locked.png" alt="" />'); ?></td>

							<td align="center"><?php echo($forum->forum_seq); ?></td>

							<td align="center">
								<?php
								echo '<table class="sfabuttontable" align="right">';
								echo '<tr>';
								echo '<td class="sfabuttonitem sfabgpermissions" align="right">';
								echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'fperm-'.$forum->forum_id.'\');">';
								echo sfa_split_heading(__("View Forum Permissions", "sforum"), 1);
								echo '</a>';
								echo '</td>';
								?>
								<?php
								echo '<td />';
								echo '<td class="sfabuttonitem sfabgedit" align="right">';
								echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eforum-'.$forum->forum_id.'\');">';
								echo __("Edit", "sforum");
								echo '</a>';
								echo '</td>';
								?>
								<?php
								echo '<td />';
								echo '<td class="sfabuttonitem sfabgdelete" align="right">';
								echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dforum-'.$forum->forum_id.'\');">';
								echo __("Delete", "sforum");
								echo '</a>';
								echo '</td>';
								echo '</tr>';
								echo '</table>';
								?>
							</td>

							<td align="center">
								<?php
								$site=SFADMINURL."ahah/sf-ahahadmin.php?action=tss&amp;item=Forum&amp;id=".$forum->forum_slug."&amp;tset=".$forum->topic_status_set;
								$icon = 'status_unset.png';
								if($forum->topic_status_set != 0)
								{
									$icon = 'status_set.png';
								}
								echo '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFADMINURL.'images/'.$icon.'" alt="" title="'.__("Assign Topic Status Set", "sforum").'" /></a>';
								?>
							</td>

							<td align="center">
								<?php
								$site=SFADMINURL."ahah/sf-ahahadmin.php?action=rss&amp;item=Forum&amp;id=".$forum->forum_slug."&amp;url=".$forum->forum_rss."&amp;pvt=".$forum->forum_rss_private;
								if(empty($forum->forum_rss))
								{
									$icon = 'feed.png';
								} else {
									$icon = 'feed_custom.png';
								}
								if($forum->forum_rss_private) $icon='feed_off.png';

								echo '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFADMINURL.'images/'.$icon.'" alt="" title="'.__("Set RSS Feed URL", "sforum").'" /></a>';
								?>
							</td>

						</tr>
						<tr> <!-- This row will hold hidden forms for the current forum -->
						  	<td class="sfinline-form" colspan="10">
<?php
								# the following function call displays the permission set for the current forum
								# however, its hidden until the forum permission set link is clicked
								# the permission set information will be displayed below the current forum information
								# additional forms to edit, add or delete these permission sets are hidden below this information
								sfa_display_forum_permissions($forum);

								# the following function call displays the edit forum  form
								# however, its hidden until the edit forum link is clicked
								# the form will be displayed below the current forum information
								sfa_edit_forum_form($forum);

								# the following function call displays the delete forum  form
								# however, its hidden until the delete forum link is clicked
								# the form will be displayed below the current forum information
								sfa_delete_forum_form($forum);
?>
							</td>
						</tr>
<?php 				} ?>
				</table>
				<br /><br />
<?php
			} else {
				echo('<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.__("There are No Forums defined in this Group", "sforum").'</div>');
			}
		}
		?>

		<table class="sfmaintable" cellpadding="0" cellspacing="0">
			<tr>
				<td><strong><?php _e("All Groups RSS", "sforum"); ?></strong></td>
				<td align="center" width="35">
					<?php
					$site=SFADMINURL."ahah/sf-ahahadmin.php?action=rss&amp;item=All&amp;id=All&amp;url=".get_option('sfallRSSurl');
					echo '<a href="'.$site.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} )"><img src="'.SFADMINURL.'images/feed.png" alt="" title="'.__("Set RSS Feed URL", "sforum").'" /></a>';
					?>
				</td>
			</tr>
		</table><br /><br />

		<?php
	} else {
		echo('<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.__("There are No Groups defined", "sforum").'</div>');
	}
?>
	</div>
</div>
<?php
	return;
}

# function to display right hand side link box for the group / forum management page
function sfa_render_forum_buttonbox($groups)
{
	$out = '<table class="sfaactiontable" border="0"><tr><td class="sfamenuitem sfabgnewgroup" align="right"><a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'cgroup\');"><small>'.sfa_split_heading(__("Create New Group", "sforum"),1).'</small></a></td></tr></table>';

	if($groups)
	{
		$out.= '<table class="sfaactiontable" border="0"><tr><td class="sfamenuitem sfabgnewforum" align="right"><a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'cforum\');"><small>'.sfa_split_heading(__("Create New Forum", "sforum"),1).'</small></a></td></tr></table>';
		$out.= '<table class="sfaactiontable" border="0"><tr><td class="sfamenuitem sfabgaddperms" align="right"><a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'globalperm\');"><small>'.sfa_split_heading(__("Add Global Permission Set", "sforum"),1).'</small></a></td></tr></table>';
		$out.= '<table class="sfaactiontable" border="0"><tr><td class="sfamenuitem sfabgremperms" align="right"><a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'rperm\');"><small>'.sfa_split_heading(__("Remove All Permission Sets", "sforum"),1).'</small></a></td></tr></table>';
	}
	$out.= '<br /><div class="clearboth"></div>';
	echo $out;
	return;
}

# function to display the add global permission set form. It is hidden until user clicks the add global permission set link
function sfa_add_global_permission_form()
{ ?>
			<div id="globalperm" class="inline_edit">
				<table cellpadding="5" cellspacing="3">
					<tr>
						<td>
							<div class="wrap">
								<fieldset class="sffieldset"><legend><?php _e("Add a User Group Permission Set to All Forums", "sforum") ?></legend>
									<?php echo(sfa_paint_help('add-a-user-group-permission-set-to-all-forums', 'admin-forums')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfnewglobalpermission">
										<?php echo(sf_create_nonce('forum-adminform_globalpermissionnew')); ?>

										<table class="form-table">
											<tr>
												<td class="sflabel"><?php sfa_display_usergroup_select(); ?></td>
											</tr><tr>
												<td class="sflabel"><?php sfa_display_permission_select(); ?></td>
											</tr>
										</table>
										<div class="clearboth"></div>
										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgupdate" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="newglobalpermission" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfnewglobalpermission.submit();">';
										echo sfa_split_heading(__("Add Global Permission", "sforum"), 1);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'globalperm\');">';
										echo __("Cancel", "sforum");
										echo '</a>';
										echo '</td>';
										echo '</tr>';
										echo '</table>';
										?>
										<p><?php _e("Caution:  Any current Permission Sets for the selected User Group for ANY Forum will be overwritten.", "sforum") ?></p>
									</form>
								</fieldset>
							</div>
						</td>
					</tr>
				</table>
			</div>
<?php
}

# function to display the remove all permission set form.  It is hidden until the remove all permission set link is clicked
function sfa_remove_all_permissions_form()
{ ?>
			<div id="rperm" class="inline_edit">
				<table cellpadding="5" cellspacing="3">
					<tr>
						<td>
							<div class="wrap">
								<fieldset class="sffieldset"><legend><?php _e("Delete All Forum Permission Sets", "sforum") ?></legend>
									<?php echo(sfa_paint_help('delete-all-forum-permission-sets', 'admin-forums')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfallpermissionsdel">
										<?php echo(sf_create_nonce('forum-adminform_allpermissionsdelete')); ?>
										<p><?php _e("Click <strong>Confirm Deletion</strong> to completely remove ALL Permission Sets.<br />This will remove ALL Permission Sets for all Groups/Forum.<br /><br />Please note that this action <strong>can NOT be reversed</strong>.", "sforum") ?></p>

										<div class="clearboth"></div>
										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgconfirm" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="deleteallpermissions" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfallpermissionsdel.submit();">';
										echo sfa_split_heading(__("Confirm Deletion", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'rperm\');">';
										echo __("Cancel", "sforum");
										echo '</a>';
										echo '</td>';
										echo '</tr>';
										echo '</table>';
										?>
									</form>
								</fieldset>
							</div>
						</td>
					</tr>
				</table>
			</div>
<?php
}

# function to display the create new group form. It is hidden until user clicks on the create new group link
function sfa_create_group_form()
{ ?>
			<div id="cgroup" class="inline_edit">
				<table cellpadding="5" cellspacing="3">
					<tr>
						<td>
							<div class="wrap">
								<fieldset class="sffieldset"><legend><?php _e("Create New Forum Group", "sforum") ?></legend>
									<?php echo(sfa_paint_help('create-new-forum-group', 'admin-forums')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfgroupnew">
										<?php echo(sf_create_nonce('forum-adminform_groupnew')); ?>
										<table class="form-table">
											<tr>
												<td class="sflabel"><?php _e("Group Name", "sforum") ?>:</td>
												<td><input type="text" class="sfacontrol" size="45" name="group_name" value="" /></td>
											</tr><tr>
												<td class="sflabel"><?php _e("Display Sequence", "sforum") ?>:</td>
		<?php									$seq = sfa_next_group_seq() + 1; ?>
												<td><input type="text" class="sfacontrol" size="5" name="group_seq" value="<?php echo($seq); ?>" /></td>
											</tr><tr>
												<td class="sflabel"><?php _e("Description", "sforum") ?>:&nbsp;</td>
												<td><input type="text" class="sfacontrol" size="85" name="group_desc" value="" /></td>
											</tr>
										</table>
										<div class="clearboth"></div>
										<br /><br />
										<?php _e("Set Default User Group Permission Sets", "sforum") ?>
										<br /><br />
										<?php _e("Note - This will not change or define any current permissions. It's only a default setting for forums that get created in this Group. You will have the chance to explicitly set each permission when creating a forum in this group.", "sforum") ?>
										<table class="form-table">
											<?php
											$usergroups = sfa_get_usergroups_all();
											foreach ($usergroups as $usergroup)
											{
											?>
											<tr>
												<td class="sflabel"><?php echo $usergroup->usergroup_name; ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
												<td><input type="hidden" name="usergroup_id[]" value="<?php echo($usergroup->usergroup_id); ?>" /></td>
	      										<?php $roles = sfa_get_all_roles(); ?>
												<td><select style="width:165px" class='sfacontrol' name='role[]'>
	<?php
													$out = '';
													$out = '<option value="-1">'.__("Select Permission Set", "sforum").'</option>';
													foreach($roles as $role)
													{
														$out.='<option value="'.$role->role_id.'">'.wp_specialchars($role->role_name).'</option>'."\n";
													}
													echo $out;
	?>
													</select>
												</td>
											</tr>
											<?php } ?>
										</table><br />
										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgupdate" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="newgroup" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfgroupnew.submit();">';
										echo sfa_split_heading(__("Create Group", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'cgroup\');">';
										echo __("Cancel", "sforum");
										echo '</a>';
										echo '</td>';
										echo '</tr>';
										echo '</table>';
										?>
									</form>
								</fieldset>
							</div>
						</td>
					</tr>
				</table>
			</div>
<?php
}

# function to display the create new forum forum.  It is hidden until the create new forum link is clicked
function sfa_create_forum_form()
{ ?>
			<div id="cforum" class="inline_edit">
				<table cellpadding="5" cellspacing="3">
					<tr>
						<td>
							<div class="wrap">
								<fieldset class="sffieldset"><legend><?php _e("Create New Forum", "sforum") ?></legend>
									<?php echo(sfa_paint_help('create-new-forum', 'admin-forums')); ?>
									<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfforumnew">
										<?php echo(sf_create_nonce('forum-adminform_forumnew')); ?>
										<table class="form-table">
											<tr>
												<td class="sflabel"><?php _e("Select Group", "sforum") ?>:</td>
												<td><select style="width:190px" class="sfacontrol" name="group_id">
													<?php echo(sfa_create_group_select()); ?>
												</select></td>
											</tr><tr>
												<td class="sflabel"><?php _e("Forum Name", "sforum") ?>:</td>
												<td><input type="text" class="sfacontrol" size="45" name="forum_name" value="" /></td>
											</tr><tr>
												<td class="sflabel"><?php _e("Display Sequence", "sforum") ?>:</td>
												<td><input type="text" class="sfacontrol" size="5" name="forum_seq" value="" /></td>
											</tr><tr>
												<td class="sflabel" colspan="2"><label for="sfforum_status"><?php _e("Locked", "sforum") ?></label></td>
												<td><input type="checkbox" id="sfforum_status" name="forum_status" /></td>
											</tr><tr>
												<td class="sflabel" colspan="2"><label for="sfforum_private"><?php _e("Disable Forum RSS Feed (Feed will not be generated):", "sforum") ?></label></td>
												<td><input type="checkbox" id="sfforum_private" name="forum_private" /></td>
											</tr><tr>
												<td class="sflabel"><?php _e("Description", "sforum") ?>:&nbsp;&nbsp;</td>
												<td><input type="text" class="sfacontrol" size="85" name="forum_desc" value="" /></td>
											</tr>
										</table>
										<br /><br />
<?php
										$usergroups = sfa_get_usergroups_all();
										if ($usergroups) {
?>
											<?php _e("Add User Group Permission Sets", "sforum") ?>
											<br /><br />
											<?php _e("You can selectively set the permission sets for the forum below.  If you want to use the default permissions for the selected group, then don't select anything.", "sforum") ?>
											<table class="form-table">
											<?php foreach ($usergroups as $usergroup) { ?>
												<tr>
													<td class="sflabel"><?php echo $usergroup->usergroup_name; ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
													<td><input type="hidden" name="usergroup_id[]" value="<?php echo($usergroup->usergroup_id); ?>" /></td>
	          										<?php $roles = sfa_get_all_roles(); ?>
													<td class="sflabel"><select style="width:165px" class='sfacontrol' name='role[]'>
<?php
														$out = '';
														$out = '<option value="-1">'.__("Select Permission Set", "sforum").'</option>';
														foreach($roles as $role)
														{
															$out.='<option value="'.$role->role_id.'">'.wp_specialchars($role->role_name).'</option>'."\n";
														}
														echo $out;
?>
														</select>
													</td>
												</tr>
											<?php } ?>
											</table><br />
										<?php } ?>
										<div class="clearboth"></div>
										<?php
										echo '<table class="sfabuttontable">';
										echo '<tr>';
										echo '<td class="sfabuttonitem sfabgupdate" align="right">';
										echo '<input type="hidden" class="sfhiddeninput" name="newforum" value="submit" />';
										echo '<a class="sfasmallbutton" href="javascript:document.sfforumnew.submit();">';
										echo sfa_split_heading(__("Create Forum", "sforum"), 0);
										echo '</a>';
										echo '</td>';
										echo '<td />';
										echo '<td class="sfabuttonitem sfabgcancel" align="right">';
										echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'cforum\');">';
										echo __("Cancel", "sforum");
										echo '</a>';
										echo '</td>';
										echo '</tr>';
										echo '</table>';
										?>
									</form>
								</fieldset>
							</div>
						</td>
					</tr>
				</table>
			</div>
<?php
}

# function to display the add group permission set form.  It is hidden until the add group permission set link is clicked
function sfa_add_group_permission_form($group)
{ ?>
						<div id="gperm-<?php echo $group->group_id; ?>" class="inline_edit">
							<fieldset class="sffieldset"><legend><?php _e("Add a User Group Permission Set to an Entire Group", "sforum") ?></legend>
								<?php echo(sfa_paint_help('add-a-user-group-permission-set-to-an-entire-group', 'admin-forums')); ?>
								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfgrouppermnew<?php echo $group->group_id; ?>">
									<?php echo(sf_create_nonce('forum-adminform_grouppermissionnew')); ?>
									<?php sprintf(__("Set a User Group Permission Set for ALL Forum in a Group: %s", "sforum"), $group->group_name); ?>
							<table class="form-table">
								<tr>
									<td class="sflabel"><?php sfa_display_usergroup_select(); ?></td>
								</tr><tr>
									<td class="sflabel"><?php sfa_display_permission_select(); ?></td>
								</tr>
							</table>

									<input type="hidden" name="group_id" value="<?php echo($group->group_id); ?>" />

									<div class="clearboth"></div>
									<?php
									echo '<table class="sfabuttontable">';
									echo '<tr>';
									echo '<td class="sfabuttonitem sfabgupdate" align="right">';
									echo '<input type="hidden" class="sfhiddeninput" name="newgrouppermission" value="submit" />';
									echo '<a class="sfasmallbutton" href="javascript:document.sfgrouppermnew'.$group->group_id.'.submit();">';
									echo sfa_split_heading(__("Add Group Permission", "sforum"), 1);
									echo '</a>';
									echo '</td>';
									echo '<td />';
									echo '<td class="sfabuttonitem sfabgcancel" align="right">';
									echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'gperm-'.$group->group_id.'\');">';
									echo __("Cancel", "sforum");
									echo '</a>';
									echo '</td>';
									echo '</tr>';
									echo '</table>';
									?>
									<p><?php _e("Caution:  Any current Permission Set for the selected User Group for ANY Forum in this Group will be overwritten.", "sforum") ?></p>
								</form>
							</fieldset>
						</div>
<?php
}

# function to display the edit group information form.  It is hidden until the edit group link is clicked
function sfa_edit_group_form($group)
{ ?>
						<div id="egroup-<?php echo $group->group_id; ?>"  class="inline_edit">
							<fieldset class="sffieldset"><legend><?php _e("Edit Forum Group", "sforum") ?></legend>
								<?php echo(sfa_paint_help('edit-forum-group', 'admin-forums')); ?>
								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfgroupedit<?php echo $group->group_id; ?>">
									<?php echo(sf_create_nonce('forum-adminform_groupedit')); ?>

									<input type="hidden" name="group_id" value="<?php echo($group->group_id); ?>" />
									<input type="hidden" name="cgroup_name" value="<?php echo(stripslashes($group->group_name)); ?>" />
									<input type="hidden" name="cgroup_desc" value="<?php echo(stripslashes($group->group_desc)); ?>" />
									<input type="hidden" name="cgroup_seq" value="<?php echo($group->group_seq); ?>" />
									<table class="form-table">
										<tr>
											<td class="sflabel"><?php _e("Group Name", "sforum") ?>:</td>
											<td><input type="text" class=" sfacontrol" size="45" name="group_name" value="<?php echo(stripslashes($group->group_name)); ?>" /></td>
										</tr><tr>
											<td class="sflabel"><?php _e("Display Sequence", "sforum") ?>:</td>
											<td><input type="text" class=" sfacontrol" size="5" name="group_seq" value="<?php echo($group->group_seq); ?>" /></td>
										</tr><tr>
											<td class="sflabel"><?php _e("Description", "sforum") ?>:&nbsp;</td>
											<td><input type="text" class=" sfacontrol" size="85" name="group_desc" value="<?php echo(stripslashes($group->group_desc)); ?>" /></td>
										</tr>
									</table>
									<br /><br />
									<?php _e("Set Default User Group Permission Sets for this Group", "sforum") ?>
									<br /><br />
									<?php _e("Note - This will not will add or modify any current permissions. It's only a default setting for future forums created in this group.  Existing default User Group settings will be shown in the drop down menus.", "sforum") ?>
										<table class="form-table">
											<?php
											$usergroups = sfa_get_usergroups_all();
											foreach ($usergroups as $usergroup)
											{
											?>
											<tr>
												<td class="sflabel"><?php echo $usergroup->usergroup_name; ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
												<td><input type="hidden" name="usergroup_id[]" value="<?php echo($usergroup->usergroup_id); ?>" /></td>
	      										<?php $roles = sfa_get_all_roles(); ?>
												<td class="sflabel"><select style="width:165px" class='sfacontrol' name='role[]'>
	<?php
													$defrole = sfa_get_defpermissions_role($group->group_id, $usergroup->usergroup_id);
													$out = '';
													if ($defrole == -1 || $defrole == '')
													{
														$out = '<option value="-1">'.__("Select Permission Set", "sforum").'</option>';
													}
													foreach($roles as $role)
													{
														$selected = '';
														if ($defrole == $role->role_id)
														{
															$selected = 'selected="selected" ';
														}
														$out.='<option '.$selected.'value="'.$role->role_id.'">'.wp_specialchars($role->role_name).'</option>'."\n";
													}
													echo $out;
	?>
													</select>
												</td>
											</tr>
											<?php } ?>
										</table><br />
									<div class="clearboth"></div>
									<?php
									echo '<table class="sfabuttontable">';
									echo '<tr>';
									echo '<td class="sfabuttonitem sfabgupdate" align="right">';
									echo '<input type="hidden" class="sfhiddeninput" name="updategroup" value="submit" />';
									echo '<a class="sfasmallbutton" href="javascript:document.sfgroupedit'.$group->group_id.'.submit();">';
									echo sfa_split_heading(__("Update Group", "sforum"), 0);
									echo '</a>';
									echo '</td>';
									echo '<td />';
									echo '<td class="sfabuttonitem sfabgcancel" align="right">';
									echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'egroup-'.$group->group_id.'\');">';
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

# function to display the delete group form.  It is hidden until the delete group link is clicked
function sfa_delete_group_form($group)
{ ?>
						<div id="dgroup-<?php echo $group->group_id; ?>"  class="inline_edit">
							<fieldset class="sffieldset"><legend><?php _e("Delete Forum Group", "sforum") ?></legend>
								<?php echo(sfa_paint_help('delete-forum-group', 'admin-forums')); ?>
								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfgroupdel<?php echo $group->group_id; ?>">
									<?php echo(sf_create_nonce('forum-adminform_groupdelete')); ?>

									<input type="hidden" name="group_id" value="<?php echo($group->group_id); ?>" />
									<input type="hidden" name="cgroup_seq" value="<?php echo($group->group_seq); ?>" />

									<p><?php _e("Click <strong>Confirm Deletion</strong> to completely remove this forum group.<br />This will remove ALL Forums, Topics and Posts contained in this Group.<br /><br />Please note that this action <strong>can NOT be reversed</strong>.", "sforum") ?></p>

									<div class="clearboth"></div>
									<?php
									echo '<table class="sfabuttontable">';
									echo '<tr>';
									echo '<td class="sfabuttonitem sfabgconfirm" align="right">';
									echo '<input type="hidden" class="sfhiddeninput" name="deletegroup" value="submit" />';
									echo '<a class="sfasmallbutton" href="javascript:document.sfgroupdel'.$group->group_id.'.submit();">';
									echo sfa_split_heading(__("Confirm Deletion", "sforum"), 0);
									echo '</a>';
									echo '</td>';
									echo '<td />';
									echo '<td class="sfabuttonitem sfabgcancel" align="right">';
									echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dgroup-'.$group->group_id.'\');">';
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

# function to display the current forum permission set.  It is hidden until the permission set link is clicked.
# additional forms to add, edit or delete these permission set are further hidden belwo the permission set information
function sfa_display_forum_permissions($forum)
{ ?>
						<div id="fperm-<?php echo $forum->forum_id; ?>" class="inline_edit">
<?php
							$perms = sfa_get_forum_permissions($forum->forum_id);
							if ($perms)
							{
?>
								<table class="sfmaintable" cellpadding="5" cellspacing="3">
									<tr>
										<td align="center" colspan="3"><strong><?php _e("Current Permission Set For Forum ", "sforum"); echo $forum->forum_name; ?></strong></td>
									</tr>
<?php
								foreach ($perms as $perm)
								{
									$usergroup = sfa_get_usergroups_row($perm->usergroup_id);
									$role = sfa_get_role_row($perm->permission_role);
?>
									<tr>
										<td class="sflabel"><?php echo($usergroup->usergroup_name); ?> => <?php echo($role->role_name); ?></td>
										<td align="center">
											<?php
											echo '<table class="sfabuttontable">';
											echo '<tr>';
											echo '<td class="sfabuttonitem sfabgedit" align="right">';
											echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eperm-'.$perm->permission_id.'\');">';
											echo __("Edit", "sforum");
											echo '</a>';
											echo '</td>';
											echo '<td />';
											?>
											<?php
											echo '<td class="sfabuttonitem sfabgdelete" align="right">';
											echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dperm-'.$perm->permission_id.'\');">';
											echo __("Delete", "sforum");
											echo '</a>';
											echo '</td>';
											echo '</tr>';
											echo '</table>';
											?>
										</td>
						   			</tr>
									<tr> <!-- This row will hold hidden forms for the current forum permission set -->
									  	<td class="sfinline-form" colspan="3">
<?php
											# the following function call displays the edit permission set form
											# however, its hidden until the edit forum permission set link is clicked
											# the form will be displayed below the forum permission set information
											sfa_edit_permission_form($perm);

											# the following function call displays the delete permission set form
											# however, its hidden until the delete forum permission set link is clicked
											# the form will be displayed below the forum permission set information
											sfa_delete_permission_form($perm);
?>
										</td>
									</tr>
<?php 							} ?>
<?php						} else { ?>
								<table class="sfmaintable" cellpadding="5" cellspacing="3">
									<tr>
										<td>
											<?php _e("No Permission Sets for any User Group", "sforum"); ?>
										</td>
									</tr>
<?php 						} ?>
						   			<tr>
						   				<td colspan="3" align="center">
						   					<div class="clearboth"></div>
											<?php
											echo '<table class="sfabuttontable">';
											echo '<tr>';
											echo '<td class="sfabuttonitem sfabgedit" align="right">';
											echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'aperm-'.$forum->forum_id.'\');">';
											echo sfa_split_heading(__("Add Permission", "sforum"), 0);
											echo '</a>';
											echo '</td>';
											echo '<td />';
											echo '<td class="sfabuttonitem sfabgcancel" align="right">';
											echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'fperm-'.$forum->forum_id.'\');">';
											echo __("Cancel", "sforum");
											echo '</a>';
											echo '</td>';
											echo '</tr>';
											echo '</table>';
											?>
						   				</td>
									</tr>
									<tr> <!-- This row will hold hidden forms for adding a new forum permission set -->
									  	<td class="sfinline-form" colspan="3">
<?php
											# the following function call displays the add permission set form
											# however, its hidden until the add forum permission set link is clicked
											# the form will be displayed below the forum permission set information
											sfa_add_permission_form($forum);
?>
										</td>
									</tr>
								</table>
						</div>
<?php
}

# function to display the edit form information form.  It is hidden until the edit forum link is clicked
function sfa_edit_forum_form($forum)
{ ?>
						<div id="eforum-<?php echo $forum->forum_id; ?>" class="inline_edit">
							<fieldset class="sffieldset"><legend><?php _e("Edit Forum", "sforum") ?></legend>
								<?php echo(sfa_paint_help('edit-forum', 'admin-forums')); ?>
								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfforumedit<?php echo $forum->forum_id; ?>">
									<?php echo(sf_create_nonce('forum-adminform_forumedit')); ?>

									<input type="hidden" name="forum_id" value="<?php echo($forum->forum_id); ?>" />
									<input type="hidden" name="cgroup_id" value="<?php echo($forum->group_id); ?>" />
									<input type="hidden" name="cforum_name" value="<?php echo(stripslashes($forum->forum_name)); ?>" />
									<input type="hidden" name="cforum_slug" value="<?php echo(stripslashes($forum->forum_slug)); ?>" />
									<input type="hidden" name="cforum_seq" value="<?php echo($forum->forum_seq); ?>" />
									<input type="hidden" name="cforum_desc" value="<?php echo(stripslashes($forum->forum_desc)); ?>" />
									<input type="hidden" name="cforum_status" value="<?php echo($forum->forum_status); ?>" />
									<input type="hidden" name="cforum_rss_private" value="<?php echo($forum->forum_rss_private); ?>" />
							<table class="form-table">
								<tr>
									<td class="sflabel"><?php _e("Select Group", "sforum") ?>:</td>
									<td class="sflabel"><select class="sfacontrol" name="group_id">
										<?php echo(sfa_create_group_select($forum->group_id)); ?>
									</select></td>
								</tr><tr>
									<td class="sflabel"><?php _e("Forum Name", "sforum") ?>:</td>
									<td><input type="text" class=" sfacontrol" size="45" name="forum_name" value="<?php echo(stripslashes($forum->forum_name)); ?>" /></td>
								</tr><tr>
									<td class="sflabel"><?php _e("Forum Slug", "sforum") ?>:</td>
									<td><input type="text" class=" sfacontrol" size="45" name="forum_slug" value="<?php echo(stripslashes($forum->forum_slug)); ?>" /></td>
								</tr><tr>
									<td class="sflabel"><?php _e("Display Sequence", "sforum") ?>:</td>
									<td><input type="text" class=" sfacontrol" size="5" name="forum_seq" value="<?php echo($forum->forum_seq); ?>" /></td>
								</tr><tr>
									<td class="sflabel" colspan="2"><label for="sfforum_status_<?php echo($forum->forum_id); ?>"><?php _e("Locked", "sforum") ?></label></td>
									<td><input type="checkbox" id="sfforum_status_<?php echo($forum->forum_id); ?>" name="forum_status"
									<?php if ($forum->forum_status == TRUE) {?> checked="checked" <?php } ?> /></td>
								</tr><tr>
									<td class="sflabel" colspan="2"><label for="sfforum_private_<?php echo($forum->forum_id); ?>"><?php _e("Disable Forum RSS Feed (Feed will not be generated):", "sforum") ?></label></td>
									<td><input type="checkbox" id="sfforum_private_<?php echo($forum->forum_id); ?>" name="forum_private"
										<?php if ($forum->forum_rss_private == TRUE) {?> checked="checked" <?php } ?> /></td>
								</tr><tr>
									<td class="sflabel"><?php _e("Description", "sforum") ?>:&nbsp;&nbsp;</td>
									<td><input type="text" class=" sfacontrol" size="85" name="forum_desc" value="<?php echo(stripslashes($forum->forum_desc)); ?>" /></td>
								</tr>
							</table>
									<div class="clearboth"></div>
									<?php
									echo '<table class="sfabuttontable">';
									echo '<tr>';
									echo '<td class="sfabuttonitem sfabgupdate" align="right">';
									echo '<input type="hidden" class="sfhiddeninput" name="updateforum" value="submit" />';
									echo '<a class="sfasmallbutton" href="javascript:document.sfforumedit'.$forum->forum_id.'.submit();">';
									echo sfa_split_heading(__("Update Forum", "sforum"), 0);
									echo '</a>';
									echo '</td>';
									echo '<td />';
									echo '<td class="sfabuttonitem sfabgcancel" align="right">';
									echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eforum-'.$forum->forum_id.'\');">';
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

# function to display the delete forum form.  It is hidden until the delete forum link is clicked
function sfa_delete_forum_form($forum)
{ ?>
						<div id="dforum-<?php echo $forum->forum_id; ?>"  class="inline_edit">
							<fieldset class="sffieldset"><legend><?php _e("Delete Forum", "sforum") ?></legend>
								<?php echo(sfa_paint_help('delete-forum', 'admin-forums')); ?>
								<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfforumdel<?php echo $forum->forum_id; ?>">
									<?php echo(sf_create_nonce('forum-adminform_forumdelete')); ?>

									<input type="hidden" name="group_id" value="<?php echo($forum->group_id); ?>" />
									<input type="hidden" name="forum_id" value="<?php echo($forum->forum_id); ?>" />
									<input type="hidden" name="cforum_seq" value="<?php echo($forum->forum_seq); ?>" />

									<p><?php _e("Click <strong>Confirm Deletion</strong> to completely remove this Forum.<br />This will remove ALL Topics and Posts contained in this Forum.<br /><br />Please note that this action <strong>can NOT be reversed</strong>.", "sforum") ?></p>

									<div class="clearboth"></div>
									<?php
									echo '<table class="sfabuttontable">';
									echo '<tr>';
									echo '<td class="sfabuttonitem sfabgconfirm" align="right">';
									echo '<input type="hidden" class="sfhiddeninput" name="deleteforum" value="submit" />';
									echo '<a class="sfasmallbutton" href="javascript:document.sfforumdel'.$forum->forum_id.'.submit();">';
									echo sfa_split_heading(__("Confirm Deletion", "sforum"), 0);
									echo '</a>';
									echo '</td>';
									echo '<td />';
									echo '<td class="sfabuttonitem sfabgcancel" align="right">';
									echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dforum-'.$forum->forum_id.'\');">';
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

# function to display the edit forum permission set form.  It is hidden until the edit permission set link is clicked
function sfa_edit_permission_form($perm)
{ ?>
											<div id="eperm-<?php echo $perm->permission_id; ?>" class="inline_edit">
												<fieldset class="sffieldset"><legend><?php _e("Edit Permission Set", "sforum") ?></legend>
													<?php echo(sfa_paint_help('edit-permission-set', 'admin-forums')); ?>
													<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfpermissionedit<?php echo $perm->permission_id; ?>">
														<?php echo(sf_create_nonce('forum-adminform_permissionedit')); ?>

														<input type="hidden" name="permission_id" value="<?php echo($perm->permission_id); ?>" />
														<input type="hidden" name="ugroup_perm" value="<?php echo($perm->permission_role) ?>" />
												<table class="form-table">
													<tr>
														<td class="sflabel"><?php sfa_display_permission_select($perm->permission_role); ?></td>
													</tr>
												</table>
												<div class="clearboth"></div>
												<?php
												echo '<table class="sfabuttontable">';
												echo '<tr>';
												echo '<td class="sfabuttonitem sfabgupdate" align="right">';
												echo '<input type="hidden" class="sfhiddeninput" name="updatepermission" value="submit" />';
												echo '<a class="sfasmallbutton" href="javascript:document.sfpermissionedit'.$perm->permission_id.'.submit();">';
												echo sfa_split_heading(__("Update Permissions", "sforum"), 0);
												echo '</a>';
												echo '</td>';
												echo '<td />';
												echo '<td class="sfabuttonitem sfabgcancel" align="right">';
												echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'eperm-'.$perm->permission_id.'\');">';
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

# function to display the delete forum permission set form.  It is hidden until the delete permission set link is clicked
function sfa_delete_permission_form($perm)
{ ?>
											<div id="dperm-<?php echo $perm->permission_id; ?>" class="inline_edit">
												<fieldset class="sffieldset"><legend><?php _e("Delete Permission Set", "sforum") ?></legend>
													<?php echo(sfa_paint_help('delete-permission-set', 'admin-forums')); ?>
													<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfpermissiondel<?php echo $perm->permission_id; ?>">
														<?php echo(sf_create_nonce('forum-adminform_permissiondelete')); ?>

														<input type="hidden" name="permission_id" value="<?php echo($perm->permission_id); ?>" />

														<p><?php _e("Click <strong>Confirm Deletion</strong> to completely remove this Permission Set.<br />This will remove ALL access to this Forum for this User Group.<br /><br />Please note that this action <strong>can NOT be reversed</strong>.", "sforum") ?></p>

														<div class="clearboth"></div>
														<?php
														echo '<table class="sfabuttontable">';
														echo '<tr>';
														echo '<td class="sfabuttonitem sfabgconfirm" align="right">';
														echo '<input type="hidden" class="sfhiddeninput" name="deletepermission" value="submit" />';
														echo '<a class="sfasmallbutton" href="javascript:document.sfpermissiondel'.$perm->permission_id.'.submit();">';
														echo sfa_split_heading(__("Confirm Deletion", "sforum"), 0);
														echo '</a>';
														echo '</td>';
														echo '<td />';
														echo '<td class="sfabuttonitem sfabgcancel" align="right">';
														echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'dperm-'.$perm->permission_id.'\');">';
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

# function to display the add new forum permission set form.  It is hidden until the add permission set link is clicked
function sfa_add_permission_form($forum)
{ ?>
											<div id="aperm-<?php echo $forum->forum_id; ?>" class="inline_edit">
												<fieldset class="sffieldset"><legend><?php _e("Add User Group Permission Set", "sforum") ?></legend>
													<?php echo(sfa_paint_help('add-user-group-permission-set', 'admin-forums')); ?>
													<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfpermissionnew<?php echo $forum->forum_id; ?>">
														<?php echo(sf_create_nonce('forum-adminform_permissionnew')); ?>

														<p><?php _e("Add User Group Permission Set for Forum: ".$forum->forum_name, "sforum") ?></p>
												<table class="form-table">
													<tr>
														<td class="sflabel"><?php sfa_display_usergroup_select(true, $forum->forum_id); ?></td>
													</tr><tr>
														<td class="sflabel"><?php sfa_display_permission_select(); ?></td>
													</tr>
												</table>
														<input type="hidden" name="forum_id" value="<?php echo($forum->forum_id); ?>" />
														<div class="clearboth"></div>
														<?php
														echo '<table class="sfabuttontable">';
														echo '<tr>';
														echo '<td class="sfabuttonitem sfabgupdate" align="right">';
														echo '<input type="hidden" class="sfhiddeninput" name="newpermission" value="submit" />';
														echo '<a class="sfasmallbutton" href="javascript:document.sfpermissionnew'.$forum->forum_id.'.submit();">';
														echo sfa_split_heading(__("Add Permission", "sforum"), 0);
														echo '</a>';
														echo '</td>';
														echo '<td />';
														echo '<td class="sfabuttonitem sfabgcancel" align="right">';
														echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjtoggleLayer(\'aperm-'.$forum->forum_id.'\');">';
														echo __("Cancel", "sforum");
														echo '</a>';
														echo '</td>';
														echo '</tr>';
														echo '</table>';
														?>
														<p><?php _e("Caution:  Any current Permission Set for this User Group for this Forum will be overwritten.", "sforum") ?></p>
													</form>
												</fieldset>
											</div>
<?php
}

?>