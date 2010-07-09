<?php
/*
Simple:Press Forum
Admin Database Form Rendering
$LastChangedDate: 2009-02-19 16:11:53 +0000 (Thu, 19 Feb 2009) $
$Rev: 1417 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# function to display the form that allows admins to select filter criteria for topics before pruning
function sfa_render_database_index()
{
	global $wpdb;
?>
<div class="wrap sfatag">

<div class="clearboth"></div>

	<div class="sfmaincontainer">
<?php
		# make sure we have some groups/forums/topics in order to be able to prune
		$groups = sfa_get_database();
		if ($groups)
		{
?>
			<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sffiltertopics">
				<?php echo(sf_create_nonce('forum-adminform_filtertopics')); ?>
				<fieldset class="sffieldset"><legend><?php _e("Database Topic Pruning", "sforum") ?></legend>
					<table width="100%" cellpadding="2" cellspacing="3" border="0">
						<tr>
							<td valign="top" width="20%">
								<fieldset style="background:#eeeeee;" class="sffieldset"><legend><?php _e("Select Topic Filter Date", "sforum") ?></legend>
									<?php echo(sfa_paint_help('select-topic-filter-date', 'admin-database')); ?>
								    <!-- Display a popup calendar for pruning date entry -->
									<p align="center">
									<input name="date" id="cal" type="text" class="sfacontrol" size="15" value="<?php echo date('M d Y'); ?>" />
									<a href="javascript:sfjNewCal('cal','MMMddyyyy')">
										<img src="<?php echo(SFADMINURL); ?>images/cal.gif" width="16" height="16" border="0" alt="Pick a Filter Date" />
									</a>
									</p>
									<p><?php _e("Select Topic Filter Date Above.", "sforum"); ?></p>
									<p><?php _e("All topics prior to the date selected above will be available for pruning. If no date is specified, todays date will be used.", "sforum") ?></p>
								</fieldset>
							</td>
							<td valign="top" width="80%">
								<fieldset style="width:95%" class="sffieldset"><legend><?php _e("Select Group(s) / Forum(s) To Prune", "sforum") ?></legend>
									<?php echo(sfa_paint_help('select-group-forum-to-prune', 'admin-database')); ?>
	<?php
									$gcount = 0;
									foreach ($groups as $group)
									{
										# display separate fieldset for each group and forum within that group
	?>
										<fieldset style="margin-left:15px;width:95%" class="sffieldset"><legend><?php echo stripslashes($group['group_name']); ?></legend><br />
							<div id="container<?php echo($group['group_id']); ?>">
										<table  class="sfsubtable" cellpadding="0" cellspacing="0">
										  	<tr>
										  		<th width="5%" align="center"><?php _e("Filter", "sforum") ?></th>
										  		<th align="left"><?php _e("Forum Name", "sforum") ?></th>
										  		<th width="10%" align="center"><?php _e("Topic Count", "sforum") ?></th>
										  		<th width="20%" align="center"><?php _e("Earliest Topic", "sforum") ?></th>
										  		<th width="20%" align="center"><?php _e("Latest Topic", "sforum") ?></th>
										  	</tr>
	<?php
										if ($group['forums'])
										{
											$fcount = 0;
											foreach($group['forums'] as $forum)
											{
												$id = 'group'.$gcount.'forum';
	?>
												<tr>
													<td class="sflabel" align="left" colspan="2">
														<label for="sf<?php echo($id.$fcount); ?>"><?php echo stripslashes($forum['forum_name']); ?></label>
														<input type="checkbox" name="<?php echo $id.$fcount; ?>" id="sf<?php echo $id.$fcount; ?>" value="<?php echo $forum['forum_id']; ?>" />
													</td>
													<td align="center">
														<?php echo $forum['topic_count']; ?>
													</td>
													<td align="center">
	<?php
														$date = sfa_get_first_topic_date($forum['forum_id']);
														echo mysql2date(SFDATES, $date);
	?>
													</td>
													<td align="center">
	<?php
														$date = sfa_get_last_topic_date($forum['forum_id']);
														echo mysql2date(SFDATES, $date);
	?>
													</td>
												</tr>
	<?php
												$fcount++;
											}
	?>
											</table>
										</div>

											<?php
											$checkcontainer = '#container'.$group['group_id'];
											echo '<br />';
											echo '<table class="sfabuttontable" align="left">';
											echo '<tr>';
											echo '<td class="sfabuttonitem sfabgcheckall" align="right">';
											echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjcheckAll(jQuery(\''.$checkcontainer.'\'))">';
											echo sfa_split_heading(__("Check All", "sforum"), 0);
											echo '</a>';
											echo '</td>';
											echo '<td />';
											echo '<td class="sfabuttonitem sfabguncheckall" align="right">';
											echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjuncheckAll(jQuery(\''.$checkcontainer.'\'))">';
											echo sfa_split_heading(__("Uncheck All", "sforum"), 0);
											echo '</a>';
											echo '</td>';
											echo '</tr>';
											echo '</table>';
											?>
	<?php
										}
	?>
											<input type="hidden" name="fcount[]" value="<?php echo($fcount); ?>" />
										</fieldset>
	<?php
										$gcount++;
									}
	?>
									<p><?php echo __('<strong>Warning:</strong>  The filtering process can be cpu intensive.  It is recommended to select a minimal number of forums (based on number of posts) to filter at once, especially if you are on shared hosting.', 'sforum'); ?></p>
								</fieldset>
							</td>
						</tr>
					</table>

					<input type="hidden" name="gcount" value="<?php echo($gcount); ?>" />

						<?php
						echo '<table class="sfabuttontable">';
						echo '<tr>';
						echo '<td class="sfabuttonitem sfabgaddusergroup" align="right">';
						echo '<input type="hidden" class="sfhiddeninput" name="dbfiltertopics" value="submit" />';
						echo '<a class="sfasmallbutton" href="javascript:document.sffiltertopics.submit();">';
						echo sfa_split_heading(__("Filter Topics", "sforum"), 0);
						echo '</a>';
						echo '</td>';
						echo '</tr>';
						echo '</table>';
						?>

				</fieldset>
			</form>
<?php
		} else {
			sfa_message(__("There is Nothing to Prune as there are no Topics.", "sforum"));
		}
?>
	</div>
</div>
<?php
	return;
}

# function to display topics that meet the pruning filter critera.  Individual topics or all topics can be selected for pruning
function sfa_database_prune_form($topicdata)
{
	global $wpdb;
?>
<div class="wrap sfatag">
	<div class="sfmaincontainer">
<?php
		# grabe the topics that meet the filter critera
		$date = $topicdata['date'];
		$forum_id = $topicdata['id'];
		$sql = "SELECT * FROM ".SFTOPICS.
			   " WHERE topic_date <= '".$date."'".$forum_id.
			   " ORDER BY topic_date, forum_id ASC";
		$topics = $wpdb->get_results($sql);
		# display the list of topics if any met the criteria
		if ($topics) {
?>
			<form action="<?php echo(SFADMINPATH); ?>" method="post" name="sfprunetopics">
			<?php echo(sf_create_nonce('forum-adminform_prunetopics')); ?>
			<fieldset class="sffieldset"><legend><?php _e("Select Topics To Prune", "sforum") ?></legend>
				<?php echo(sfa_paint_help('select-topics-to-prune', 'admin-database')); ?>
				<div class="clearboth"></div>

				<div id="checkboxset">
				<table  class="sfsubtable" cellpadding="0" cellspacing="0">
					<tr>
						<th width="5%" align="center"><?php _e("Delete", "sforum") ?></th>
						<th width="5%" align="center"><?php _e("Topic ID", "sforum") ?></th>
						<th width="20%" align="center"><?php _e("Topic Date", "sforum") ?></th>
						<th width="20%" align="center"><?php _e("Forum", "sforum") ?></th>
						<th align="left"><?php _e("Topic Title", "sforum") ?></th>
					</tr>
<?php
					$tcount = 0;
					foreach ($topics as $topic) {
?>
						<tr>
				<td class="sflabel" align="center" colspan="2">
				<label for="sftopic<?php echo $tcount; ?>"><?php echo $topic->topic_id; ?></label>
			<input type="checkbox" id="sftopic<?php echo $tcount; ?>" name="topic<?php echo $tcount; ?>" value="<?php echo $topic->topic_id; ?>" /></td>
							<td align="center"><?php echo mysql2date(SFDATES, $topic->topic_date); ?></td>
							<?php $forum_name = $wpdb->get_var("SELECT forum_name FROM ".SFFORUMS." WHERE forum_id='".$topic->forum_id."'"); ?>
							<td><?php echo stripslashes($forum_name); ?></td>
							<td><?php echo stripslashes($topic->topic_name); ?></td>
						</tr>
<?php
						$tcount++;
					}
?>
					<input type="hidden" name="tcount" value="<?php echo($tcount); ?>" />
				</table>
				</div>

					<?php
					echo '<table class="sfabuttontable" align="left">';
					echo '<tr>';
					echo '<td class="sfabuttonitem sfabgcheckall" align="right">';
					echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjcheckAll(jQuery(\'#checkboxset\'))">';
					echo sfa_split_heading(__("Check All", "sforum"), 0);
					echo '</a>';
					echo '</td>';
					echo '<td />';
					echo '<td class="sfabuttonitem sfabguncheckall" align="right">';
					echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjuncheckAll(jQuery(\'#checkboxset\'))">';
					echo sfa_split_heading(__("Uncheck All", "sforum"), 0);
					echo '</a>';
					echo '</td>';
					echo '</tr>';
					echo '</table>';
					?>

				<div class="clearboth"></div>
					<?php
					echo '<table class="sfabuttontable">';
					echo '<tr>';
					echo '<td class="sfabuttonitem sfabgprunetopics" align="right">';
					echo '<input type="hidden" class="sfhiddeninput" name="dbprunetopics" value="submit" />';
					echo '<a class="sfasmallbutton" href="javascript:document.sfprunetopics.submit();">';
					echo sfa_split_heading(__("Prune Topics", "sforum"), 0);
					echo '</a>';
					echo '</td>';
					echo '<td />';
					echo '<td class="sfabuttonitem sfabgcancel" align="right">';
					echo '<a class="sfasmallbutton" href="javascript:document.sfprunetopics.submit();">';
					echo sfa_split_heading(__("Cancel", "sforum"), 0);
					echo '</a>';
					echo '</td>';
					echo '</tr>';
					echo '</table>';
					?>

			</fieldset>
			</form>
<?php
		} else {
    		sfa_message(__("No Topics Found using the Specified Filter Criteria.", "sforum"));
		}
?>
	</div>
</div>
<?php
	return;
}

?>