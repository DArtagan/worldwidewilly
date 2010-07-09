<?php
/*
Simple:Press Forum
Admin Users Form Rendering
$LastChangedDate: 2009-04-21 22:20:30 +0100 (Tue, 21 Apr 2009) $
$Rev: 1755 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# function to display the form that allows admins to manage the forum administrators
function sfa_render_users_index()
{
	global $wpdb;
?>

<div class="wrap sfatag">
	<div class="sfmaincontainer">
	<?php sfa_render_users_buttonbox(); ?>

	<div id="sfuserstabs" style="display:none">
		<ul>
			<li><a href="#T1"><span><?php _e("Member Info", "sforum"); ?></span></a></li>
			<li><a href="#T2"><span><?php _e("Subscriptions/Watches", "sforum"); ?></span></a></li>
			<li><a href="#T3"><span><?php _e("PM Stats", "sforum"); ?></span></a></li>
			<li><a href="#T4"><span><?php _e("Spam Registrations", "sforum"); ?></span></a></li>
		</ul>
		<?php

		sfa_paint_options_init();

		# Member Profiles
		sfa_paint_open_tab("T1");
		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Member Info", "sforum"), true, 'users-info', false);
				sfa_display_members();
			sfa_paint_close_fieldset(false);
		sfa_paint_close_panel();
		sfa_paint_close_tab();

		# Subscriptions and Watches
		sfa_paint_open_tab("T2");
		$site = SF_PLUGIN_URL . "/admin/ahah/sf-ahahsubswatches.php?action=swlist";
		$gif = SFADMINURL."images/working.gif";
		echo '<form action="'.SFADMINPATH.'" method="post" name="sfwatchessubs" id="sfwatchessubs" onsubmit="return sfjshowSubsList(this, \''.$site.'\', \''.$gif.'\');" >';
		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Select Format and Filters", "sforum"), true, 'users-watches-subs');
				sfa_watches_subs_form();
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();
	echo '<br />';
	echo '<table class="sfabuttontable" style="margin-left:15px;">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgshowsubs" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="watchessubs" value="submit" />';
	$site = SF_PLUGIN_URL . "/admin/ahah/sf-ahahsubswatches.php?action=swlist";
	$gif = SFADMINURL."images/working.gif";
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjshowSubsList(\'sfwatchessubs\', \''.$site.'\', \''.$gif.'\');">';
	echo sfa_split_heading(__("Show Watches and Subscriptions", "sforum"), 1);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
		echo '<br /><br /><div id="subsdisplayspot" style="margin:0 15px;"></div>';
		echo '</form><br />';
		sfa_paint_close_tab();

		# PM Stats
		sfa_paint_open_tab("T3");
		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Member PM Stats", "sforum"), true, 'users-pm-stats', false);
				sfa_display_pm_stats();
			sfa_paint_close_fieldset(false);
		sfa_paint_close_panel();
		sfa_paint_close_tab();

		sfa_paint_open_tab("T4");
			sfa_paint_open_panel();
				sfa_paint_open_fieldset(__("Remove Spam Registrations", "sforum"), true, 'remove-spam-registrations', false);
					sfa_paint_remove_spam_users();
				sfa_paint_close_fieldset(false);
			sfa_paint_close_panel();
		sfa_paint_close_tab();
?>
	</div>
</div>
</div>
<?php
	return;
}

function sfa_render_users_buttonbox()
{
	$out = "<br />\n";
	echo $out;
	return;
}

function sfa_watches_subs_form()
{
		$subs = isset($_POST['showsubs']);
		$watches = isset($_POST['showwatches']);
		if (!$subs && !$watches) # when form not submitted, default both to checked
		{
			$subs = true;
			$watches = true;
		}
		sfa_paint_checkbox(__("Show Subscriptions", "sforum"), "showsubs", $subs, false, true);
		sfa_paint_checkbox(__("Show Watches", "sforum"), "showwatches", $watches, false, true);
		echo "<tr valign='top'>";
		echo "<td width='30%' class='sflabel'>";
		echo __("Filter by All, Groups or Forums", "sforum");
		echo ":</td>";
		echo "<td style='background:#eaf3fa'>";
		echo "<table width='100%'><tr>";
		echo "<td width='125' class='sflabel' style='background:#eaf3fa'>";
		if (isset($_POST['watchessubsfilter'])) $filter = $_POST['watchessubsfilter'];
		if (!isset($filter)) $filter = 'All';
		$check = '';
		if ($filter == 'All') $check = " checked='checked'";
		echo "<input type='radio' id='sffilterall' name='watchessubsfilter' value='All'".$check." />";
		echo "<label class='sfradio' for='sffilterall'>&nbsp;".__('All', 'sforum')."</label><br />";
		$check = '';
		if ($filter == 'Groups') $check = " checked='checked'";
		$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=display-groups";
		$gif = SFADMINURL."images/working.gif";
		$string =__("Show Groups", "sforum");
		echo '<input type="radio" id="sffiltergroups" name="watchessubsfilter" value="Groups"'.$check.' onclick="sfjshowGroupList(\''.$site.'\', \''.$gif.'\');" />';
		echo "<label class='sfradio' for='sffiltergroups'>&nbsp;".__('Groups', 'sforum')."</label><br />";
		$check = '';
		if ($filter == 'Forums') $check = " checked='checked'";
		$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=display-forums";
		$gif = SFADMINURL."images/working.gif";
		$string =__("Show Forums", "sforum");
		echo '<input type="radio" id="sffilterforums" name="watchessubsfilter" value="Forums"'.$check.' onclick="sfjshowForumList(\''.$site.'\', \''.$gif.'\');" />';
		echo "<label class='sfradio' for='sffilterforums'>&nbsp;".__('Forums', 'sforum')."</label>";
		echo "</td>";
		echo "<td align='left' style='background:#eaf3fa'>";
		echo '<div id="select-group"  class="inline_edit" style="margin: 3px; padding: 2px;">';
		echo '<p>'.__("Select Groups", "sforum").'</p>';
		echo '<div id="selectgroup"></div>';
		echo '<div class="clearboth"></div>';
		echo '</div>';
		echo "</td>";
		echo "<td align='left' style='background:#eaf3fa'>";
		echo '<div id="select-forum"  class="inline_edit" style="margin: 3px; padding: 2px;">';
		echo '<p>'.__("Select Forums", "sforum").'</p>';
		echo '<div id="selectforum"></div>';
		echo '<div class="clearboth"></div>';
		echo '</div>';
		echo "</td>";
		echo "</tr></table>";
		echo "</td>";
		echo "</tr>";
}

function sfa_display_pm_stats()
{
	global $wpdb;

	if (isset($_POST['pmsearch'])) $term = $_POST['pmsearch']; else $term = '';
	if (isset($_GET['userspage'])) $page = sf_syscheckint($_GET['userspage']); else $page = '';
	$pm_search = new WP_User_Search($term, $page);
?>
	<form id="pmsearch-filter" name="pmsearchfilter" action="<?php echo SFADMINPATH; ?>" method="post">
		<div class="tablenav">
			<?php if ( $pm_search->results_are_paged() ) : ?>
				<div class="tablenav-pages">
					<?php
					$args = array();
					if( ! empty($pm_search->search_term) )
					{
						$args['usersearch'] = urlencode($pm_search->search_term);
					}
					$pm_search->paging_text = paginate_links( array(
						'total' => ceil($pm_search->total_users_for_query / $pm_search->users_per_page),
						'current' => $pm_search->page,
						'base' => 'admin.php?page=simple-forum/admin/sf-adminusers.php&amp;%_%',
						'format' => 'userspage=%#%',
						'add_args' => $args) );
					echo $pm_search->page_links();
					?>
				</div>
			<?php endif; ?>
			<div class="alignleft">
				<label class="hidden" for="pm-search-input"><?php _e('Search Members', 'sforum'); ?>:</label>
				<input type="text" class="sfacontrol" id="pm-search-input" name="pmsearch" value="<?php echo attribute_escape($pm_search->search_term); ?>" />
				<?php
				echo '<table class="sfabuttontable" style="margin:-32px 0 0 155px;">';
				echo '<tr>';
				echo '<td class="sfabuttonitem sfabgshowmembers" align="right">';
				echo '<a class="sfasmallbutton" href="javascript:document.pmsearchfilter.submit();">';
				echo sfa_split_heading(__("Search Members", "sforum"), 0);
				echo '</a>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				?>
 			</div>
			<br class="clear" />
		</div>
		<br class="clear" />
		<?php if ( $pm_search->get_results() ) : ?>
			<?php if ( $pm_search->is_search() ) : ?>
				<p><a href="<?php echo SFADMINPATH; ?>"><?php _e('&laquo; Back to All Members', 'sforum'); ?></a></p>
			<?php endif; ?>

			<table align="center" class="sfsubtable" cellpadding="0" cellspacing="0">
				<tr>
					<th align="center" width="90" scope="col" style="padding:5px 0px;"><?php _e("User ID", "sforum"); ?></th>
					<th align="left" style="padding:2px 0px;"><?php _e("User Name", "sforum"); ?></th>
					<th align="center" width="20" scope="col" style="padding:5px 0px;"></th>
					<th align="center" width="50" scope="col" style="padding:5px 0px;"><?php _e("Can PM", "sforum") ?></th>
					<th align="center" width="20" scope="col" style="padding:5px 0px;"></th>
					<th align="center" width="90" scope="col" style="padding:5px 0px;"><?php _e("Total PMs", "sforum") ?></th>
					<th align="center" width="90" scope="col" style="padding:5px 0px;"><?php _e("Inbox PMs", "sforum") ?></th>
					<th align="center" width="90" scope="col" style="padding:5px 0px;"><?php _e("Unread PMs", "sforum") ?></th>
					<th align="center" width="90" scope="col" style="padding:5px 0px;"><?php _e("Sentbox PMs", "sforum") ?></th>
					<th align="center" width="20" scope="col" style="padding:5px 0px;"></th>
					<th align="center" width="80" scope="col" style="padding:5px 0px;"><?php _e("Manage PMs", "sforum") ?></th>
					<th align="center" width="20" scope="col" style="padding:5px 0px;"></th>
				</tr>
				<?php
				if ($pm_search)
				{
					foreach ($pm_search->get_results() as $userid)
					{
						$pmdata = sfa_get_user_pm_data($userid);
//						if ($pmdata[$userid]['id'] == '')
						if (!isset($pmdata[$userid]))
						{
							$data = sf_get_member_row($userid);
							$pmdata[$userid]['id'] = $userid;
							$pmdata[$userid]['name'] = $data['display_name'];
							$pmdata[$userid]['pm'] = $data['pm'];
							$pmdata[$userid]['inbox'] = 0;
							$pmdata[$userid]['unread'] = 0;
							$pmdata[$userid]['sentbox'] = 0;
						}
				?>
					<tr>
						<td colspan="12" style="border-bottom:0px;padding:0px;">
							<div id="pmdata<?php echo $userid; ?>">
								<table width="100%" cellspacing="0">
									<tr>
										<td align="center" width="90" style="padding:5px 0px;"><?php echo $userid; ?></td>
										<td align="left" style="padding:2px 0px;"><?php echo stripslashes(attribute_escape($pmdata[$userid]['name'])); ?></td>
										<td align="center" width="20" style="padding:5px 0px;"></td>
										<td align="center" width="50" style="padding:5px 0px;"><?php if ($pmdata[$userid]['pm']) echo __("Yes", "sforum"); else echo __("No", "sforum"); ?></td>
										<td align="center" width="20" style="padding:5px 0px;"></td>
										<td align="center" width="90" style="padding:5px 0px;"><?php echo ($pmdata[$userid]['inbox'] + $pmdata[$userid]['sentbox']); ?></td>
										<td align="center" width="90" style="padding:5px 0px;"><?php echo $pmdata[$userid]['inbox']; ?></td>
										<td align="center" width="90" style="padding:5px 0px;"><?php echo $pmdata[$userid]['unread']; ?></td>
										<td align="center" width="90" style="padding:5px 0px;"><?php echo $pmdata[$userid]['sentbox']; ?></td>
										<td align="center" width="20" style="padding:5px 0px;"></td>
										<td align="center" width="80" style="padding:5px 0px;">
											<?php if ($pmdata[$userid]['inbox'] > 0 || $pmdata[$userid]['sentbox'] > 0)
											{ ?>
												<?php $site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_inbox&amp;id=".$pmdata[$userid]['id']."&amp;name=".$pmdata[$userid]['name']."&amp;pm=".$pmdata[$userid]['pm']."&amp;inbox=".$pmdata[$userid]['inbox']."&amp;unread=".$pmdata[$userid]['unread']."&amp;sentbox=".$pmdata[$userid]['sentbox']."&amp;eid=".$userid ?>
												<?php $gif = SFADMINURL."images/working.gif"; ?>
												<?php if ($pmdata[$userid]['sentbox'] == 0) $fade = 1; else $fade = 0; ?>
												<img onclick="sfjDelPMs('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $fade; ?>', 'pmdata<?php echo $userid; ?>');" src="<?php echo SFADMINIMAGES; ?>inbox_pm.png" title="<?php _e("Delete Inbox PMs", "sforum"); ?>" alt="" />&nbsp;
												<?php $site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_sentbox&amp;id=".$pmdata[$userid]['id']."&amp;name=".$pmdata[$userid]['name']."&amp;pm=".$pmdata[$userid]['pm']."&amp;inbox=".$pmdata[$userid]['inbox']."&amp;unread=".$pmdata[$userid]['unread']."&amp;sentbox=".$pmdata[$userid]['sentbox']."&amp;eid=".$userid; ?>
												<?php if ($pmdata[$userid]['inbox'] == 0) $fade = 1; else $fade = 0; ?>
												<img onclick="sfjDelPMs('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $fade; ?>', 'pmdata<?php echo $userid; ?>');" src="<?php echo SFADMINIMAGES; ?>sentbox_pm.png" title="<?php _e("Delete Sentbox PMs", "sforum"); ?>" alt="" />&nbsp;
												<?php $site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_pms&amp;id=".$pmdata[$userid]['id']."&amp;name=".$pmdata[$userid]['name']."&amp;pm=".$pmdata[$userid]['pm']."&amp;inbox=".$pmdata[$userid]['inbox']."&amp;unread=".$pmdata[$userid]['unread']."&amp;sentbox=".$pmdata[$userid]['sentbox']."&amp;eid=".$userid; ?>
												<img onclick="sfjDelPMs('<?php echo $site; ?>', '<?php echo $gif; ?>', '1', 'pmdata<?php echo $userid; ?>');" src="<?php echo SFADMINIMAGES; ?>all_pm.png" title="<?php _e("Delete All PMs", "sforum"); ?>" alt=""/>
											<?php } ?>
										</td>
										<td align="center" width="20" style="padding:5px 0px;"></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
					<?php } ?>
					<tr style="height:50px">
						<td colspan="12" style="padding-left:10px;padding-right:10px;"><?php echo __("Note: Removing all PMs for a user may result in invalid data being displayed for other users until the page is refreshed.", "sforum"); ?></td>
					</tr>
				<?php } else { ?>
				<tr style="height:50px">
					<td colspan="12" style="padding-left:10px;padding-right:10px;"><?php echo __("There currently are not any stored PMs.", "sforum"); ?></td>
				</tr>
				<?php }?>
			</table>
			<div class="tablenav">
				<?php if ( $pm_search->results_are_paged() ) : ?>
					<div class="tablenav-pages"><?php $pm_search->page_links(); ?></div>
				<?php endif; ?>
				<br class="clear" />
			</div>
		<?php endif; ?>
	</form>
<?php

}

function sfa_display_members()
{
	if (isset($_POST['usersearch'])) $term = $_POST['usersearch']; else $term = '';
	if (isset($_GET['userspage'])) $page = sf_syscheckint($_GET['userspage']); else $page = '';
	$wp_user_search = new WP_User_Search($term, $page);
?>
	<form id="posts-filter" name="searchfilter" action="<?php echo SFADMINPATH; ?>" method="post">
		<div class="tablenav">
			<?php if ( $wp_user_search->results_are_paged() ) : ?>
				<div class="tablenav-pages">
					<?php
					$args = array();
					if( ! empty($wp_user_search->search_term) )
					{
						$args['usersearch'] = urlencode($wp_user_search->search_term);
					}
					$wp_user_search->paging_text = paginate_links( array(
						'total' => ceil($wp_user_search->total_users_for_query / $wp_user_search->users_per_page),
						'current' => $wp_user_search->page,
						'base' => 'admin.php?page=simple-forum/admin/sf-adminusers.php&amp;%_%',
						'format' => 'userspage=%#%',
						'add_args' => $args) );
					echo $wp_user_search->page_links();
					?>
				</div>
			<?php endif; ?>
			<div class="alignleft">
				<label class="hidden" for="post-search-input"><?php _e('Search Members', 'sforum'); ?>:</label>
				<input type="text" class="sfacontrol" id="post-search-input" name="usersearch" value="<?php echo attribute_escape($wp_user_search->search_term); ?>" />
				<?php
				echo '<table class="sfabuttontable" style="margin:-32px 0 0 155px;">';
				echo '<tr>';
				echo '<td class="sfabuttonitem sfabgshowmembers" align="right">';
				echo '<a class="sfasmallbutton" href="javascript:document.searchfilter.submit();">';
				echo sfa_split_heading(__("Search Members", "sforum"), 0);
				echo '</a>';
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				?>
 			</div>
			<br class="clear" />
		</div>
		<br class="clear" />
		<?php if ( $wp_user_search->get_results() ) : ?>
			<?php if ( $wp_user_search->is_search() ) : ?>
				<p><a href="<?php echo SFADMINPATH; ?>"><?php _e('&laquo; Back to All Members', 'sforum'); ?></a></p>
			<?php endif; ?>
			<table class="sfsubtable">
				<thead>
					<tr class="thead">
						<th width="10"></th>
						<th align="left"><?php _e('Username', 'sforum') ?></th>
						<th align="center"><?php _e('First Post', 'sforum') ?></th>
						<th align="center"><?php _e('Last Post', 'sforum') ?></th>
						<th align="center" class="num"><?php _e('Posts', 'sforum') ?></th>
						<th align="center"><?php _e('Last Visit', 'sforum') ?></th>
						<th align="left"><?php _e('Memberships', 'sforum') ?></th>
						<th align="left"><?php _e('Rank', 'sforum') ?></th>
						<th align="center"><?php _e('Actions', 'sforum') ?></th>
					</tr>
				</thead>
				<tbody id="users" class="list:user user-list">
					<?php
					$style = '';
					foreach ($wp_user_search->get_results() as $userid)
					{
						$data = sfa_get_members_info($userid);
						$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
						?>
						<tr<?php echo $style; ?>>
							<td></td>
							<td align="left"><?php echo stripslashes(attribute_escape($data['display_name'])); ?></td>
							<td align="center"><?php echo $data['first']; ?></td>
							<td align="center"><?php echo $data['last']; ?></td>
							<td align="center"><?php echo $data['posts']; ?></td>
							<td align="center"><?php echo mysql2date(SFDATES, $data['lastvisit']); ?></td>
							<td align="left"><?php echo $data['memberships']; ?></td>
							<td align="left"><?php echo $data['rank']; ?></td>
							<td align="center">
								<?php
									$param['forum']='all';
									$param['value']=urlencode('sf%members%1%user'.$userid);
									$param['search']=1;
									$url=add_query_arg($param, SFURL);
									$url=sf_filter_wp_ampersand($url);
								?>
								<a href="<?php echo $url; ?>"><img src="<?php echo SFADMINIMAGES; ?>topic_list.png" title="<?php _e("List Topics User Has Posted In", "sforum"); ?>" alt="" /></a>
								<?php $site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=show_profile&amp;id=".$userid?>
								<?php $gif = SFADMINURL."images/working.gif"; ?>
								<img onclick="sfjShowProfile('<?php echo $site; ?>', '<?php echo $gif; ?>', 'adminmemberprofile<?php echo $userid; ?>');" src="<?php echo SFADMINIMAGES; ?>profile.png" title="<?php _e("View Member Profile", "sforum");?>" alt="" />
							</td>
						</tr>
						<tr>
							<td colspan="9" style="padding:0">
								<div id="adminmemberprofile<?php echo $userid; ?>" class="inline_edit" style="background:#eeeeee">
								</div>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>

			<div class="tablenav">
				<?php if ( $wp_user_search->results_are_paged() ) : ?>
					<div class="tablenav-pages"><?php $wp_user_search->page_links(); ?></div>
				<?php endif; ?>
				<br class="clear" />
			</div>
		<?php endif; ?>
	</form>
<?php
}

function sfa_paint_remove_spam_users()
{
	$site = SFADMINURL."ahah/sf-ahahadminuser.php?action=spam_reg";
	$target = 'adminspresult';
	$gif = SFADMINURL."images/working.gif";
	$msg = __("Please Wait - Loading Users to Remove", "sforum").'&lt;br /&gt;'.__("This may take some time", "sforum");

	?>
		<p><?php _e("This option should be used with great care! It will remove ALL user registrations that", "sforum") ?>:</p>
		<ul>
			<li><?php _e("are now over 7 days old", "sforum") ?></li>
			<li><?php _e("where the user has never posted to the forum", "sforum") ?></li>
			<li><?php _e("where the user has never authored a post", "sforum") ?></li>
			<li><?php _e("where the user has never left a comment", "sforum") ?></li>
		</ul>
		<p><?php _e("Use at your own risk!", "sforum") ?></p>
	<?php
	echo '<table class="sfabuttontable" style="margin-left:13px">'."\n";
	echo '<tr>'."\n";
	echo '<td class="sfabuttonitem sfabgspamusers" align="right">'."\n";
	echo '<input type="hidden" class="sfhiddeninput" name="addadmins" value="submit" />'."\n";
	echo '<a class="sfasmallbutton" href="'.$site.'" onfocus="sfjadminMsg(\''.$target.'\', \''.$gif.'\', \''.$msg.'\');" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true} );">'."\n";
	echo sfa_split_heading(__("Manage Spam Users", "sforum"), 1);
	echo '</a>'."\n";
	echo '</td>'."\n";
	echo '</tr>'."\n";
	echo '</table>'."\n";
	echo "<div id='adminspresult' class='inline_edit'></div>"."\n";

	return;
}

?>