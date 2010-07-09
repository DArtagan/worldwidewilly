<?php
/*
Simple:Press Forum
Admin Panels - Component Management
$LastChangedDate: 2009-01-10 02:20:31 +0000 (Sat, 10 Jan 2009) $
$Rev: 1161 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('Access Denied');
}

# Check Whether User Can Manage Components
if(!sf_current_user_can('SPF Manage Components')) {
	die('Access Denied');
}

# Load up support fles and constants and globals needed
define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-admincomponents.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');

include_once('sf-adminsupport.php');
include_once('sf-admin.php');
include_once('sf-tabsupport.php');
include_once('sf-tmtoolbar.php');

global $adminhelpfile;
$adminhelpfile='admin-components';

# Check if plugin update is required
if(sfa_get_system_status() != 'ok')
{
	include_once(SFLOADER);
	die();
}

# Does anythng need Saving - Save/Update checks

if($_POST)
{
	# Editor
	if($_POST['editorops'] == "update") sfa_save_components_editor_options();

	# Toolbar
	if($_POST['tbrestore'] == "update") sfa_restore_toolbar_defaults();
	if(!empty($_POST['delbuttons'])) sfa_remove_toolbar_buttons($_POST['delbuttons']);
	if((!empty($_POST['stan_buttons']) || !empty($_POST['plug_buttons']))) sfa_reorder_toolbar_buttons($_POST['stan_buttons'], $_POST['plug_buttons']);

	# Smileys
	if($_POST['smileys'] == "update") sfa_save_components_smileys();

	# Login/registration
	if($_POST['logincomps'] == "update") sfa_save_components_credentials();

	# Extensions
	if($_POST['extensions'] == "update") sfa_save_components_extensions();

	# Topic Status
	if($_POST['topicstatus'] == "update") sfa_save_components_topicstatus();

	# Forum Ranks
	if($_POST['ranks'] == "update") sfa_save_components_forumranks();

	# Custom Messages
	if($_POST['cmessages'] == "update") sfa_save_components_custommessages();

	# Custom Icons
	if($_POST['cicons'] == "update") sfa_save_components_customicons();

	# Custom Fields
	if($_POST['cfields'] == "update") sfa_save_components_customfields();
}

# Now reload the form/page
$sfcomps = array();
$sfcomps = sfa_get_components();

# Finally display the form
sfa_header(__('SPF Manage Components', 'sforum'), 'icon-components');
sfa_components_form($sfcomps);
sfa_footer();

# = COMPONENTS PAGE ===============================

function sfa_components_form($sfcomps)
{
?>
	<!-- Components Panel -->

<div class="wrap sfatag">

	<div class="sfmaincontainer">

	<form action="<?php echo(SFADMINPATH); ?>" method="post" id="sfcomponentsform" name="sfcomponents" enctype="multipart/form-data">
	<?php echo(sf_create_nonce('forum-adminform_components')); ?>

	<div class="clearboth"></div>

	<div id="sfcomponentstabs" style="display:none">
	<ul>
		<li><a href="#T1"><span><?php _e("Post Editors", "sforum"); ?></span></a></li>
		<li><a href="#T2"><span><?php _e("Smileys", "sforum"); ?></span></a></li>
		<li><a href="#T3"><span><?php _e("Login/Registration", "sforum"); ?></span></a></li>
		<li><a href="#T4"><span><?php _e("Extensions", "sforum"); ?></span></a></li>
		<li><a href="#T5"><span><?php _e("Topic Status", "sforum"); ?></span></a></li>
		<li><a href="#T6"><span><?php _e("Forum Ranks", "sforum"); ?></span></a></li>
		<li><a href="#T7"><span><?php _e("Custom Messages", "sforum"); ?></span></a></li>
		<li><a href="#T8"><span><?php _e("Custom Icons", "sforum"); ?></span></a></li>
		<li><a href="#T9"><span><?php _e("Custom Profile Fields", "sforum"); ?></span></a></li>
	</ul>

<?php

	sfa_paint_options_init();

#== EDITOR Tab ============================================================

	sfa_paint_open_tab("T1");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Post Editing", "sforum"), true, 'post-editing');
				$values = array(__("Rich Text (TinyMCE)", "sforum"), __("HTML (Quicktags)", "sforum"), __("BBCode (Quicktags)", "sforum"), __("Plain Textarea", "sforum"));
				sfa_paint_radiogroup(__("Select Default Editor", "sforum"), 'editor', $values, $sfcomps['sfeditor'], false, true);
				sfa_paint_checkbox(__("Members can Select Editor", "sforum"), "sfusereditor", $sfcomps['sfusereditor']);
				sfa_paint_checkbox(__("Reject Posts with Embedded Formatting and Force Correct use of Paste Options (Rich Text Editor Only)", "sforum"), "sfrejectformat", $sfcomps['sfrejectformat']);
			sfa_paint_close_fieldset();

			sfa_paint_open_fieldset(__("Editor Language (TinyMCE Only)", "sforum"), true, 'editor-language');
				sfa_paint_select_start(__("Select 2 letter Language Code", "sforum"), "sflang", "sflang");
				echo(sfa_create_language_select($sfcomps['sflang']));
				sfa_paint_select_end();
				sfa_paint_checkbox(__("Use Editor Right-to-Left", "sforum"), "sfrtl", $sfcomps['sfrtl']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("CSS Styles (All Editors)", "sforum"), true, 'editor-styles');
				sfa_paint_input(__("TinyMCE Content CSS", "sforum"), "sftmcontentCSS", $sfcomps['sftmcontentCSS']);
				sfa_paint_input(__("TinyMCE UI CSS", "sforum"), "sftmuiCSS", $sfcomps['sftmuiCSS']);
				sfa_paint_input(__("TinyMCE Dialog CSS", "sforum"), "sftmdialogCSS", $sfcomps['sftmdialogCSS']);
				sfa_paint_input(__("Quicktags HTML CSS", "sforum"), "SFhtmlCSS", $sfcomps['SFhtmlCSS']);
				sfa_paint_input(__("Quicktags bbCode CSS", "sforum"), "SFbbCSS", $sfcomps['SFbbCSS']);
			sfa_paint_close_fieldset();

			sfa_paint_open_fieldset(__("Use Relative URL's (TinyMCE Only)", "sforum"), true, 'editor-relative');
				sfa_paint_checkbox(__("Save Internal URL's as 'Relative'", "sforum"), "sfrelative", $sfcomps['sfrelative']);
			sfa_paint_close_fieldset();

		sfa_paint_close_panel();

		echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">'."\n";
		echo '<tr>'."\n";
		echo '<td class="sfabuttonitem sfabgupdate" align="right">'."\n";
		echo '<input type="hidden" class="sfhiddeninput" name="editorops" id="editorops" value="submit" />'."\n";
		echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'editorops\')">'."\n";
		echo sfa_split_heading(__("Update Editor Options", "sforum"), 0);
		echo '</a>'."\n";
		echo '</td>'."\n";
		echo '</tr>'."\n";
		echo '</table>'."\n";

		echo '</td></tr></table>'."\n";

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Remove Editor Toolbar Buttons (TinyMCE)", "sforum"), true, 'remove-editor-buttons', false);
			# Remove buttons
			sfa_render_remove_toolbar();
			sfa_paint_close_fieldset(false);
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Re-order Editor Toolbar Buttons (TinyMCE)", "sforum"), true, 'reorder-editor-buttons', false);
			# Drag/Drop/Sort
			sfa_render_drag_toolbar();
			sfa_paint_close_fieldset(false);

		sfa_paint_close_panel();

	echo '</div>';

#== SMILEYS Tab ============================================================

	sfa_paint_open_tab("T2");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Smiley Options", "sforum"), true, 'smiley-options');
				sfa_paint_checkbox(__("Allow Smileys", "sforum"), "sfsmallow", $sfcomps['sfsmallow'], false, true, true);
				$values = array(__("Custom Smileys (All Editors)", "sforum"), __("TinyMCE Smileys (TinyMCE Editor Only)", "sforum"), __("WordPress Smileys (All Editors)", "sforum"));
				sfa_paint_radiogroup(__("Select Smiley Set", "sforum"), 'smileytype', $values, $sfcomps['sfsmtype'], false, true);
			sfa_paint_close_fieldset();

			sfa_paint_open_fieldset(__("Custom Smiley Upload", "sforum"), true, 'smiley-upload');
				sfa_paint_file(__("Select Smiley File to Upload", "sforum"), 'newsmileyfile', false, true);
				sfa_paint_input(__("Enter Smiley Name", "sforum"), "newsmileyname", '', false, true);
				sfa_paint_input(__("Enter Smiley Code", "sforum"), "newsmileycode", '', false, true);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="smileys" id="smileys" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'smileys\')">';
	echo sfa_split_heading(__("Update Smileys", "sforum"), 0);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Custom Smileys", "sforum"), true, 'custom-smileys', false);
			sfa_paint_custom_smileys();
			sfa_paint_close_fieldset(false);
		sfa_paint_close_panel();

	sfa_paint_close_tab();

#== LOGIN/REGISTRATION Tab =================================================

	sfa_paint_open_tab("T3");

	if (false == get_option('users_can_register'))
	{
		sfa_paint_open_panel();

		sfa_paint_open_fieldset(__("Login and Member Registrations", "sforum"), true, 'no-login', false);
		echo '<br /><div class="sfoptionerror">';
		echo __("Your site is currently not set to allow users to register. Click on the Help icon for details of how to turn this on", "sforum");
		echo '</div><br />';
		sfa_paint_close_fieldset(false);
		sfa_paint_close_panel();

	} else {

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Member Login", "sforum"), true, 'user-login');

				sfa_paint_checkbox(__("Show Login/Logout Link", "sforum"), "sfshowlogin", $sfcomps['sfshowlogin']);
				sfa_paint_checkbox(__("Use In-Line Login Form", "sforum"), "sfinlogin", $sfcomps['sfinlogin']);
				sfa_paint_checkbox(__("Skin Login Forms", "sforum"), "sfloginskin", $sfcomps['sfloginskin']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("User Registration", "sforum"), true, 'user-registration');
				sfa_paint_checkbox(__("Show Register Link", "sforum"), "sfshowreg", $sfcomps['sfshowreg']);
				sfa_paint_checkbox(__("Use Spam Tool on Registration Form", "sforum"), "sfregmath", $sfcomps['sfregmath']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Registration Policy", "sforum"), true, 'registration-policy');
				sfa_paint_checkbox(__("Display Registration Policy", "sforum"), "sfregtext", $sfcomps['sfregtext']);
				sfa_paint_checkbox(__("Force Policy Acceptance (checkbox)", "sforum"), "sfregcheck", $sfcomps['sfregcheck']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Registration Policy Statement", "sforum"), true, 'registration-policy');
				$submessage=__("Enter the text of the site Registration Policy for display (and optional acceptance) prior to the user registration form being displayed.", "sforum");
				sfa_paint_site_policy("sfsitepolicy", $sfcomps['sfsitepolicy'], $submessage);
			sfa_paint_close_fieldset();

		sfa_paint_close_panel();

		echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
		echo '<tr>';
		echo '<td class="sfabuttonitem sfabgupdate" align="right">';
		echo '<input type="hidden" class="sfhiddeninput" name="logincomps" id="logincomps" value="submit" />';
		echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'logincomps\')">';
		echo sfa_split_heading(__("Update Login and Registration", "sforum"), 1);
		echo '</a>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';

	}
	sfa_paint_close_tab();

#== EXTENSIONS Tab ==========================================================

	sfa_paint_open_tab("T4");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Private Messaging", "sforum"), true, 'private-messaging');
				sfa_paint_checkbox(__("Enable the Private Messaging System", "sforum"), "sfprivatemessaging", $sfcomps['sfprivatemessaging']);
				sfa_paint_checkbox(__("Send Email to Message Recipient", "sforum"), "sfpmemail", $sfcomps['sfpmemail']);
				sfa_paint_input(__("Maximum Inbox Size (0=No Limit)", "sforum"), "sfpmmax", $sfcomps['sfpmmax']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Democracy Polls Support", "sforum"), true, 'democracy-polls-support');
				sfa_paint_checkbox(__("Use Democracy", "sforum"), "sfdemocracy", $sfcomps['sfdemocracy']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		if(!function_exists('wpmu_current_site'))
		{
			sfa_paint_open_panel();
				sfa_paint_open_fieldset(__("Image Uploads", "sforum"), true, 'image-uploads');
					if(in_array("gd", get_loaded_extensions()))
					{
						echo('<tr><td><p>&nbsp;<u>'.__("If you are allowing users to upload images", "sforum").':</u></p></td></tr>');
						sfa_paint_input(__("Upload Folder (under wp-content)", "sforum"), "sfuppath", $sfcomps['sfuppath']);

						if(!empty($sfcomps['sfuppath']))
						{
							$path = WP_CONTENT_DIR . '/' . $sfcomps['sfuppath'];
							if(!file_exists($path))
							{
								echo "<tr><td colspan='2'><p class='sfoptionerror'>".sprintf(__("'%s'  - folder does not exist.  - Please correct", "sforum"), $path)."</p></td></tr>";
							}
						}
					} else {
						echo('<tr><td><p class="sflabel">'.__("The php 'GD' graphics library is required for image uploads which is not available", "sforum").':</p></td></tr>');
					}
				sfa_paint_close_fieldset();
			sfa_paint_close_panel();
		}

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Image Enlargement", "sforum"), true, 'image-enlarging');
				sfa_paint_checkbox(__("Use Popup Image Enlargement", "sforum"), "sfimgenlarge", $sfcomps['sfimgenlarge']);
				sfa_paint_input(__("Thumbnail width of images in posts<br />(Minimum 100)", "sforum"), "sfthumbsize", $sfcomps['sfthumbsize']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Post Ratings Support", "sforum"), true, 'post-ratings');
				sfa_paint_checkbox(__("Enable Post Rating", "sforum"), "sfpostratings", $sfcomps['sfpostratings']);
				$values = array(__("Thumbs Up/Down", "sforum"), __("Stars", "sforum"));
				$msg = '<p>'.__("WARNING: Changing the rating styles will reset all of the currently collected ratings data.  Please check the confirm box to indicate that you really want to do this.  The database tables will be reset when the options are saved.", "sfforum").'</p>';
				sfa_paint_radiogroup_confirm(__("Select Post Rating Style", "sforum"), 'ratingsstyle', $values, $sfcomps['sfratingsstyle'], $msg, false, true);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("All In One SEO Pack Integration", "sforum"), true, 'aiosp');
				sfa_paint_checkbox(__("Include Topic Name in WP Page Title", "sforum"), "sfaiosp_topic", $sfcomps['sfaiosp_topic']);
				sfa_paint_checkbox(__("Include Forum Name in WP Page Title", "sforum"), "sfaiosp_forum", $sfcomps['sfaiosp_forum']);
				sfa_paint_input(__("WP Page Title Separator", "sforum"), "sfaiosp_sep", $sfcomps['sfaiosp_sep']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
		echo '<tr>';
		echo '<td class="sfabuttonitem sfabgupdate" align="right">';
		echo '<input type="hidden" class="sfhiddeninput" name="extensions" id="extensions" value="submit" />';
		echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'extensions\')">';
		echo sfa_split_heading(__("Update Extensions", "sforum"), 0);
		echo '</a>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';

	sfa_paint_close_tab();

#== TOPIC STATUS Tab ============================================================

	sfa_paint_open_tab("T5");

	for($x=0; $x<count($sfcomps['topic-status'])+1; $x++)
	{
		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Topic Status Set", "sforum"), !$x, 'topic-status-sets');

				if(isset($sfcomps['topic-status'][$x]))
				{
					sfa_paint_input(__("Topic Status Set Name", "sforum"), "sftopstatname[$x]", stripslashes($sfcomps['topic-status'][$x]['meta_key']), false, false);
					sfa_paint_input(__("Enter status phrases - separate with a comma. Enter them in the order they are to appear in the selection list", "sforum"), "sftopstatwords[$x]", stripslashes($sfcomps['topic-status'][$x]['meta_value']), false, true);
					sfa_paint_hidden_input("sftopstatid[$x]", $sfcomps['topic-status'][$x]['meta_id']);
				} else {
					sfa_paint_input(__("Topic Status Set Name", "sforum"), "sftopstatname[$x]", '', false, false);
					sfa_paint_input(__("Enter status phrases - separate with a comma. Enter them in the order they are to appear in the selection list", "sforum"), "sftopstatwords[$x]", '', false, true);
					sfa_paint_hidden_input("sftopstatid[$x]", '');
				}

				if(isset($sfcomps['topic-status'][$x]['meta_id']))
				{
					echo "<tr valign='top'>\n";
					echo "<td class='sflabel' width='100%' colspan='2'>\n";
					echo "<label for='sftopstatdel-".$x."'>".__("Delete this Topic Status Set", "sforum")."</label>\n";
					echo "<input type='checkbox' tabindex='51' name='sftopstatdel[".$x."]' id='sftopstatdel-".$x."' />\n";
					echo "</td>\n";
					echo "</tr>\n";
				}
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();
	}

	echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="topicstatus" id="topicstatus" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'topicstatus\')">';
	echo sfa_split_heading(__("Update Topic Status", "sforum"), 0);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	sfa_paint_close_tab();

#== Forum Ranks Tab ============================================================

	sfa_paint_open_tab("T6");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Forum Ranks", "sforum"), true, 'forum-ranks');
				sfa_paint_rankings_table($sfcomps['rankings']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="ranks" id="ranks" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'ranks\')">';
	echo sfa_split_heading(__("Update Forum Ranks", "sforum"), 1);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	sfa_paint_close_tab();

#== Custom Messages Tab ============================================================

	sfa_paint_open_tab("T7");
		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Custom Message Above Editor", "sforum"), true, 'editor-message');
				$submessage=__("Text you enter here will be displayed above the editor (New Topic and/or New Post).", "sforum");
				sfa_paint_wide_textarea(__("Custom Message", "sforum"), "sfpostmsgtext", $sfcomps['sfpostmsgtext'], $submessage);
				sfa_paint_checkbox(__("Display for New Topic", "sforum"), "sfpostmsgtopic", $sfcomps['sfpostmsgtopic']);
				sfa_paint_checkbox(__("Display for New Post", "sforum"), "sfpostmsgpost", $sfcomps['sfpostmsgpost']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Custom Intro Text in Editor", "sforum"), true, 'editor-intro');
				$submessage=__("Text you enter here will be displayed inside the editor (New Topic only).", "sforum");
				sfa_paint_wide_textarea(__("Custom Intro Message", "sforum"), "sfeditormsg", $sfcomps['sfeditormsg'], $submessage);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="cmessages" id="cmessages" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'cmessages\')">';
	echo sfa_split_heading(__("Update Custom Messages", "sforum"), 1);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	sfa_paint_close_tab();

#== Custom Icons ============================================================

	sfa_paint_open_tab("T8");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Custom Icon 1", "sforum"), true, 'custom-icon');

				if(!empty($sfcomps['cusicon0']))
				{
					$path = SFCUSTOM.$sfcomps['cusicon0'];
					if(!file_exists($path))
					{
						echo('<tr><td colspan="2"><div class="sfoptionerror">'.sprintf(__("Custom Icon '%s' does not exist", "sforum"), $sfcomps['cusicon0']).'</div></td></tr>');
					}
				}

				sfa_paint_input(__("Display Text (Optional)", "sforum"), "custext0", $sfcomps['custext0'], false, true);
				sfa_paint_input(__("Target URL", "sforum"), "cuslink0", $sfcomps['cuslink0'], false, true);
				sfa_paint_input(__("Custom Icon", "sforum"), "cusicon0", $sfcomps['cusicon0'], false, true);
				sfa_paint_icon($sfcomps['cusicon0']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Custom Icon 2", "sforum"), false, 'custom-icon');

				if(!empty($sfcomps['cusicon1']))
				{
					$path = SFCUSTOM.$sfcomps['cusicon1'];
					if(!file_exists($path))
					{
						echo('<tr><td colspan="2"><div class="sfoptionerror">'.sprintf(__("Custom Icon '%s' does not exist", "sforum"), $sfcomps['cusicon1']).'</div></td></tr>');
					}
				}

				sfa_paint_input(__("Display Text (Optional)", "sforum"), "custext1", $sfcomps['custext1'], false, true);
				sfa_paint_input(__("Target URL", "sforum"), "cuslink1", $sfcomps['cuslink1'], false, true);
				sfa_paint_input(__("Custom Icon", "sforum"), "cusicon1", $sfcomps['cusicon1'], false, true);
				sfa_paint_icon($sfcomps['cusicon1']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Custom Icon 3", "sforum"), false, 'custom-icon');

				if(!empty($sfcomps['cusicon2']))
				{
					$path = SFCUSTOM.$sfcomps['cusicon2'];
					if(!file_exists($path))
					{
						echo('<tr><td colspan="2"><div class="sfoptionerror">'.sprintf(__("Custom Icon '%s' does not exist", "sforum"), $sfcomps['cusicon2']).'</div></td></tr>');
					}
				}

				sfa_paint_input(__("Display Text (Optional)", "sforum"), "custext2", $sfcomps['custext2'], false, true);
				sfa_paint_input(__("Target URL", "sforum"), "cuslink2", $sfcomps['cuslink2'], false, true);
				sfa_paint_input(__("Custom Icon", "sforum"), "cusicon2", $sfcomps['cusicon2'], false, true);
				sfa_paint_icon($sfcomps['cusicon2']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="cicons" id="cicons" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'cicons\')">';
	echo sfa_split_heading(__("Update Custom Icons", "sforum"), 1);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	sfa_paint_close_tab();

#== Custom Profile Fields Tab ============================================================

	sfa_paint_open_tab("T9");
		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Custom Profile Fields", "sforum"), true, 'custom-fields');
				sfa_paint_fields_table($sfcomps['cfields']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgupdate" align="right">';
	echo '<input type="hidden" class="sfhiddeninput" name="cfields" id="cfields" value="submit" />';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'cfields\')">';
	echo sfa_split_heading(__("Update Custom Fields", "sforum"), 1);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	sfa_paint_close_tab();

#== END OF FORM TABS ============================================================

?>
	</div>

	</form>
</div>
</div>
<br /><br />

<?php
	return;
}

#== END OF PAGE DISPLAY ============================================================


# = LOAD COMPONENTS DATA ======================

function sfa_get_components()
{
	global $current_user, $wpdb;

	# Load up component data
	$sfcomps = array();

	# Smileys -----------------------
	$sfsmileys = array();
	$sfsmileys = get_option('sfsmileys');
	$sfcomps['sfsmallow'] = $sfsmileys['sfsmallow'];
	$sfcomps['sfsmtype'] = $sfsmileys['sfsmtype'];

	# Load Private Message options
	$sfcomps['sfprivatemessaging'] = get_option('sfprivatemessaging');
	$sfpm = array();
	$sfpm = get_option('sfpm');
	$sfcomps['sfpmemail'] = $sfpm['sfpmemail'];
	$sfcomps['sfpmmax'] = $sfpm['sfpmmax'];

	# Image Upload Path
	$sfcomps['sfuppath']=get_option('sfuppath');

	# image resizing
	$sfcomps['sfimgenlarge']=get_option('sfimgenlarge');
	$sfcomps['sfthumbsize']=get_option('sfthumbsize');

	# Load Democracy Setting
	$sfcomps['sfdemocracy']=get_option('sfdemocracy');

	# Post Rating
	$sfpostratings = array();
	$sfpostratings = get_option('sfpostratings');
	$sfcomps['sfpostratings'] = $sfpostratings['sfpostratings'];
	$sfcomps['sfratingsstyle'] = $sfpostratings['sfratingsstyle'];

	# Topic Status Sets -------------
	$tsets = sf_get_sfmeta('topic-status', false);
	if($tsets) $sfcomps['topic-status']=$tsets; else $sfcomps['topic-status']=0;

	# Editor standard Options
	$sfeditor = array();
	$sfeditor = get_option('sfeditor');
	$sfcomps['sfeditor']=$sfeditor['sfeditor'];
	$sfcomps['sfusereditor']=$sfeditor['sfusereditor'];
	$sfcomps['sfrejectformat']=$sfeditor['sfrejectformat'];
	$sfcomps['sftmcontentCSS'] = $sfeditor['sftmcontentCSS'];
	$sfcomps['sftmuiCSS'] = $sfeditor['sftmuiCSS'];
	$sfcomps['sftmdialogCSS'] = $sfeditor['sftmdialogCSS'];
	$sfcomps['SFhtmlCSS'] = $sfeditor['SFhtmlCSS'];
	$sfcomps['SFbbCSS'] = $sfeditor['SFbbCSS'];
	$sfcomps['sflang'] = $sfeditor['sflang'];
	$sfcomps['sfrtl'] =  $sfeditor['sfrtl'];
	$sfcomps['sfrelative'] = $sfeditor['sfrelative'];

	# Login/Registration
	$sflogin = array();
	$sflogin = get_option('sflogin');
	$sfcomps['sfshowlogin'] = $sflogin['sfshowlogin'];
	$sfcomps['sfshowreg'] = $sflogin['sfshowreg'];
	$sfcomps['sfregmath'] = $sflogin['sfregmath'];
	$sfcomps['sfinlogin'] = $sflogin['sfinlogin'];
	$sfcomps['sfregtext'] = $sflogin['sfregtext'];
	$sfcomps['sfregcheck'] = $sflogin['sfregcheck'];
	$sfcomps['sfloginskin'] = $sflogin['sfloginskin'];

	$policy = sf_get_sfmeta('registration', 'policy');

	# forum ranks
	$sfcomps['rankings'] = sf_get_sfmeta('forum_rank');

	$sfcomps['sfsitepolicy'] = $policy[0]['meta_value'];

	# custom message for posts
	$sfpostmsg = array();
	$sfpostmsg = get_option('sfpostmsg');
	$sfcomps['sfpostmsgtext'] = stripslashes($sfpostmsg['sfpostmsgtext']);
	$sfcomps['sfpostmsgtopic'] = $sfpostmsg['sfpostmsgtopic'];
	$sfcomps['sfpostmsgpost'] = $sfpostmsg['sfpostmsgpost'];

	# custom editor message
	$sfcomps['sfeditormsg']=stripslashes(get_option('sfeditormsg'));

	# Custom Icons (3)
	$sfcustom = array();
	$sfcustom = get_option('sfcustom');

	$sfcomps['custext0'] = $sfcustom[0]['custext'];
	$sfcomps['cuslink0'] = $sfcustom[0]['cuslink'];
	$sfcomps['cusicon0'] = $sfcustom[0]['cusicon'];
	$sfcomps['custext1'] = $sfcustom[1]['custext'];
	$sfcomps['cuslink1'] = $sfcustom[1]['cuslink'];
	$sfcomps['cusicon1'] = $sfcustom[1]['cusicon'];
	$sfcomps['custext2'] = $sfcustom[2]['custext'];
	$sfcomps['cuslink2'] = $sfcustom[2]['cuslink'];
	$sfcomps['cusicon2'] = $sfcustom[2]['cusicon'];

	# aiosp integration
	$sfaiosp = array();
	$sfaiosp = get_option('sfaiosp');
	$sfcomps['sfaiosp_topic'] = $sfaiosp['sfaiosp_topic'];
	$sfcomps['sfaiosp_forum'] = $sfaiosp['sfaiosp_forum'];
	$sfcomps['sfaiosp_sep'] = $sfaiosp['sfaiosp_sep'];

	# custom editor message
	$sfcomps['cfields'] = get_option('cfields');

	# custom fields
	$sfcomps['cfields'] = sf_get_sfmeta('custom_field');

	return $sfcomps;
}

# = SAVE COMPONENTS ROUTINES =======================

function sfa_save_components_editor_options()
{
	check_admin_referer('forum-adminform_components', 'forum-adminform_components');

	$sfeditor = '';
	$sfeditor['sfeditor']=$_POST['editor'];
	if(isset($_POST['sfusereditor'])) $sfeditor['sfusereditor']=true; else $sfeditor['sfusereditor']=false;
	if(isset($_POST['sfrejectformat'])) $sfeditor['sfrejectformat']=true; else $sfeditor['sfrejectformat']=false;
	$sfeditor['sftmcontentCSS'] = $_POST['sftmcontentCSS'];
	$sfeditor['sftmuiCSS'] = $_POST['sftmuiCSS'];
	$sfeditor['sftmdialogCSS'] = $_POST['sftmdialogCSS'];
	$sfeditor['SFhtmlCSS'] = $_POST['SFhtmlCSS'];
	$sfeditor['SFbbCSS'] = $_POST['SFbbCSS'];
	$sfeditor['sflang'] = $_POST['sflang'];
	if(isset($_POST['sfrtl'])) $sfeditor['sfrtl']=true; else $sfeditor['sfrtl']=false;
	if(isset($_POST['sfrelative'])) $sfeditor['sfrelative']=true; else $sfeditor['sfrelative']=false;

	update_option('sfeditor', $sfeditor);

	$mess = '<br />'.__('Post Editor Options Updated', "sforum");
	sfa_message($mess);

	return;
}

function sfa_save_components_smileys()
{
	global $wpdb;

	check_admin_referer('forum-adminform_components', 'forum-adminform_components');

	$mess= '';

	# save Smiley options -------------------
	$sfsmileys = '';
	if(isset($_POST['sfsmallow'])) $sfsmileys['sfsmallow']=true; else $sfsmileys['sfsmallow']=false;
	$sfsmileys['sfsmtype'] = $_POST['smileytype'];
	update_option('sfsmileys', $sfsmileys);

	# Load the meta detail - may need it.
	$smileys = array();
	$meta = sf_get_sfmeta('smileys', 'smileys');
	$smeta = $meta[0]['meta_value'];
	$smileys = unserialize($smeta);

	# anything to delete
	if(isset($_POST['smileydelete']))
	{
		$list = array();
		$del = false;
		foreach ($smileys as $sname => $sinfo)
		{
			if(array_key_exists($sname, $_POST['smileydelete']))
			{
				$list[$sname]=$sinfo;
			} else {
				$del = true;
			}
		}
		$smileys = $list;
	}

	# any new smileys
	if(($_FILES['newsmileyfile']['error'] != 4) && !empty($_POST['newsmileyname']) && !empty($_POST['newsmileycode']))
	{
		include_once('upload/sf-smileys.php');
		$x=sf_upload_smiley();

		$status=explode('@', $x);
		if($status[0] == 0)
		{
			$smileys[$_POST['newsmileyname']][0] = $_FILES['newsmileyfile']['name'];
			$smileys[$_POST['newsmileyname']][1] = $_POST['newsmileycode'];
		}
		$mess.=$status[1];
	}

	$smeta = serialize($smileys);
	sf_update_sfmeta('smileys', 'smileys', $smeta, $meta[0]['meta_id']);

	$mess .= '<br />'.__('Smileys Component Updated', "sforum");
	sfa_message($mess);

	return;
}

function sfa_save_components_extensions()
{
	global $wpdb;

	check_admin_referer('forum-adminform_components', 'forum-adminform_components');

	$mess= '';

	sfa_update_check_option('sfprivatemessaging');

	# Save Private Message options
	$sfpm = '';
	if(isset($_POST['sfpmemail'])) $sfpm['sfpmemail']=true; else $sfpm['sfpmemail']=false;
	$sfpm['sfpmmax'] = $_POST['sfpmmax'];
	update_option('sfpm', $sfpm);

	# Save Image Upload Path
	if(!empty($_POST['sfuppath']))
	{
		$folder=trim($_POST['sfuppath'], '/');
		$path = WP_CONTENT_DIR . '/' . $folder;
		if(!file_exists($path))
		{
			$mess.= "<br />* ".sprintf(__("'%s' - image upload folder does not exist. Please correct", "sforum"), $path);
		}
		update_option('sfuppath', $folder);
	} else {
		update_option('sfuppath', $_POST['sfuppath']);
	}

	# Save Image resizing
	sfa_update_check_option('sfimgenlarge');
	$thumb = $_POST['sfthumbsize'];
	if($thumb < 100)
	{
		$thumb = 100;
		$mess.= "<br />* ".__("Image Thumbsize reset to Mnimum 100", "sforum");
	}
	update_option('sfthumbsize', $thumb);

	# Save Democracy
	sfa_update_check_option('sfdemocracy');

	# Post Ratings
	$sfpostratings = get_option('sfpostratings');
	if(isset($_POST['sfpostratings'])) $sfpostratings['sfpostratings']=true; else $sfpostratings['sfpostratings']=false;

	# before changing ratings style make sure it was confirmed
	if (isset($_POST['confirm-box-ratingsstyle']))
	{
		# reset post ratings data
		sfa_reset_post_ratings();
		# save new ratings style
		$sfpostratings['sfratingsstyle'] = $_POST['ratingsstyle'];
	}
	update_option('sfpostratings', $sfpostratings);

	# aiosp integration
	$sfaiosp = array();
	if(isset($_POST['sfaiosp_topic'])) $sfaiosp['sfaiosp_topic']=true; else $sfaiosp['sfaiosp_topic']=false;
	if(isset($_POST['sfaiosp_forum'])) $sfaiosp['sfaiosp_forum']=true; else $sfaiosp['sfaiosp_forum']=false;
	$sfaiosp['sfaiosp_sep'] = $_POST['sfaiosp_sep'];
	update_option('sfaiosp', $sfaiosp);

	$mess .= '<br />'.__('Extension Components Updated', "sforum").$mess;
	sfa_message($mess);

	return;
}

function sfa_save_components_topicstatus()
{
	global $wpdb;

	check_admin_referer('forum-adminform_components', 'forum-adminform_components');

	# Topic Status Sets ---------------------
	if(isset($_POST['sftopstatname'][0]))
	{
		for($x=0; $x<count($_POST['sftopstatid']); $x++)
		{
			$type = 'topic-status';
			$key = $_POST['sftopstatname'][$x];
			$value = $_POST['sftopstatwords'][$x];

			if(!empty($_POST['sftopstatid'][$x]))
			{
				if(isset($_POST['sftopstatdel'][$x]) && $_POST['sftopstatdel'][$x] == 'on')
				{
					sf_delete_sfmeta($_POST['sftopstatid'][$x]);
				} else {
					sf_update_sfmeta($type, $key, $value, $_POST['sftopstatid'][$x]);
				}
			} else {
				sf_add_sfmeta($type, $key, $value);
			}
		}
	}

	$mess= __('Topic Status Component Updated', "sforum");
	sfa_message($mess);

	return;
}

function sfa_save_components_credentials()
{
	check_admin_referer('forum-adminform_components', 'forum-adminform_components');

	# login
	$sflogin = array();
	if(isset($_POST['sfshowlogin'])) $sflogin['sfshowlogin']=true; else $sflogin['sfshowlogin']=false;
	if(isset($_POST['sfshowreg'])) $sflogin['sfshowreg']=true; else $sflogin['sfshowreg']=false;
	if(isset($_POST['sfregmath'])) $sflogin['sfregmath']=true; else $sflogin['sfregmath']=false;
	if(isset($_POST['sfinlogin'])) $sflogin['sfinlogin']=true; else $sflogin['sfinlogin']=false;
	if(isset($_POST['sfregtext'])) $sflogin['sfregtext']=true; else $sflogin['sfregtext']=false;
	if(isset($_POST['sfregcheck'])) $sflogin['sfregcheck']=true; else $sflogin['sfregcheck']=false;
	if(isset($_POST['sfloginskin'])) $sflogin['sfloginskin']=true; else $sflogin['sfloginskin']=false;
	update_option('sflogin', $sflogin);

	sf_add_sfmeta('registration', 'policy', $_POST['sfsitepolicy']);

	$mess= __('Login/Registration Component Updated', "sforum");
	sfa_message($mess);

	return;
}

function sfa_save_components_forumranks()
{
	$rankings = array();
	for($x=0; $x<count($_POST['rankdesc']); $x++)
	{
		if(!empty($_POST['rankdesc'][$x]))
		{
			$rankdata['posts'] = $_POST['rankpost'][$x];
			$rankdata['usergroup'] = $_POST['rankug'][$x];   # not implmented yet
			$rankdata['image'] = 'none';       # not implemented yet
			sf_add_sfmeta('forum_rank', $_POST['rankdesc'][$x], serialize($rankdata));
		}
	}
}

function sfa_save_components_custommessages()
{
	# custom message for editor
	$sfpostmsg = array();
	$sfpostmsg['sfpostmsgtext'] = $_POST['sfpostmsgtext'];
	$sfpostmsg['sfpostmsgtopic'] = $_POST['sfpostmsgtopic'];
	$sfpostmsg['sfpostmsgpost'] = $_POST['sfpostmsgpost'];
	update_option('sfpostmsg', $sfpostmsg);

	update_option('sfeditormsg', $_POST['sfeditormsg']);
}

function sfa_save_components_customicons()
{
	$mess='';
	for($x=0; $x<3; $x++)
	{
		$custom='cusicon'.$x;
		if(!empty($_POST[$custom]))
		{
			$icon = $_POST[$custom];
			$path = SFCUSTOM.$icon;
			if(!file_exists($path))
			{
				$mess.= "<br />* ".sprintf(__("Custom Icon '%s' does not exist", "sforum"), $icon);
			}
		}
	}
	if($mess != '') sfa_message($mess);

	# Save Custom Icons (3)
	$sfcustom = array();
	$sfcustom[0]['custext'] = $_POST['custext0'];
	$sfcustom[0]['cuslink'] = $_POST['cuslink0'];
	$sfcustom[0]['cusicon'] = $_POST['cusicon0'];
	$sfcustom[1]['custext'] = $_POST['custext1'];
	$sfcustom[1]['cuslink'] = $_POST['cuslink1'];
	$sfcustom[1]['cusicon'] = $_POST['cusicon1'];
	$sfcustom[2]['custext'] = $_POST['custext2'];
	$sfcustom[2]['cuslink'] = $_POST['cuslink2'];
	$sfcustom[2]['cusicon'] = $_POST['cusicon2'];
	update_option('sfcustom', $sfcustom);
}

function sfa_save_components_customfields()
{
	$fields = array();
	for($x=0; $x<count($_POST['cfieldname']); $x++)
	{
		if (!empty($_POST['cfieldname'][$x]))
		{
			sf_add_sfmeta('custom_field', $_POST['cfieldname'][$x], $_POST['cfieldtype'][$x]);
		}
	}
}

#== SUPPORT ROUTINES ============================================================

# Components support routines

function sfa_paint_custom_smileys()
{
	global $tab;

	# load smiles from sfmeta
	$smileys = array();
	$meta = sf_get_sfmeta('smileys', 'smileys');
	$smeta = $meta[0]['meta_value'];
	$smileys = unserialize($smeta);
	if($smileys)
	{?>

		<table class="form-table" width="99%" cellspacing="0" cellpadding="5">
		<tr>
			<th></th>
			<th><?php _e("Keep", "sforum"); ?></th>
			<th><?php _e("File", "sforum"); ?></th>
			<th><?php _e("Code", "sforum"); ?></th>
		</tr>
	<?php
		$pos=1;
		foreach ($smileys as $sname => $sinfo)
		{ ?>
			<tr>
			<td align="center">
				<img class="sfsmiley" src="<?php echo(SFSMILEYS.$sinfo[0]); ?>" alt="" />
			</td>
			<td align="left">
<?php

echo '<label for="sfsmcb-'.$pos.'" class="sflabel">'.$sname.'</label>'."\n";
echo '<input type="checkbox" name="smileydelete['.$sname.']" id="sfsmcb-'.$pos.'" tabindex="'.$tab.'" checked="checked" />'."\n";
		$pos++;
		$tab++;
?>

			</td>
			<td>
				<?php echo($sinfo[0]); ?>
			</td>
			<td>
				<?php echo($sinfo[1]); ?>
			</td>
			</tr>
		<?php
		} ?>
		</table>
		<?php
	}
}

function sfa_paint_site_policy($textname, $textvalue, $submessage)
{
	global $tab;

	echo "<tr>\n";
	echo "<td class='sflabel'>\n";
	echo "<small><strong>".$submessage."</strong></small>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td class='sflabel'>\n";
	echo __("Policy Statement", "sforum").":\n";
	echo "<div class='sfformcontainer'>\n";
	echo "<textarea rows='11' cols='80' class='sftextarea' tabindex='$tab' name='$textname'>$textvalue</textarea>\n";
	echo "</div>\n";
	echo "</td>\n";
	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_rankings_table($rankings)
{
	global $tab;

	echo "<tr>\n";
	echo "<th width='30%'>".__("Forum Rank Name", "sforum")."</th>\n";
	echo "<th width='30%' style='text-align:center'>".__("# Posts For Rank", "sforum")."</th>\n";
	echo "<th width='30%' style='text-align:center'>".__("User Group Membership Attained", "sforum")."</th>\n";
	echo "<th style='text-align:center'>".__("Remove", "sforum")."</th>\n";
	echo "</tr>\n";
	$usergroups = sfa_get_usergroups_all();

	# sort rankings from lowest to highest
	if ($rankings)
	{
		foreach ($rankings as $x => $info)
		{
			$ranks['title'][$x] = $info['meta_key'];
			$data = unserialize($info['meta_value']);
			$ranks['posts'][$x] = $data['posts'];
			$ranks['usergroup'][$x] = $data['usergroup'];
		}
		array_multisort($ranks['posts'], SORT_ASC, $ranks['title'], $ranks['usergroup']);
	}

	# display rankings info
	for ($x=0; $x<count($rankings); $x++)
	{
		echo '<tr>'."\n";
		echo '<td width="100%" colspan="4" style="border-bottom:0px;padding:0px;">'."\n";
		echo '<div id="rank'.$x.'">'."\n";
		echo '<table width="100%" cellspacing="0">'."\n";
		echo '<tr>'."\n";
		echo "<td width='30%' align='left'>\n";
		echo "<input type='text' class='sfacontrol' size='20' tabindex='$tab' name='rankdesc[]' value='".$ranks['title'][$x]."' />\n";
		$tab++;
		echo "</td>\n";
		echo "<td width='30%' class='sflabel' align='center'>\n";
		echo __("Up to", "sforum")." &#8594;\n";
		echo "<input type='text' class='sfacontrol' size='7' tabindex='$tab' name='rankpost[]' value='".$ranks['posts'][$x]."' />\n";
		$tab++;
		echo " ".__("Posts", "sforum")." \n";
		echo "</td>\n";
		echo "<td width='30%' align='center'>\n";
		echo "<select class='sfacontrol' name='rankug[]'>\n";
		if ($data['usergroup'] == 'none')
		{
			$out = '<option value="none" selected="selected">'.__("No User Group Membership", "sforum").'</option>'."\n";
		} else {
			$out = '<option value="none">'.__("No User Group Membership", "sforum").'</option>'."\n";
		}
		foreach ($usergroups as $usergroup)
		{
			if ($ranks['usergroup'][$x] == $usergroup->usergroup_id)
			{
				$selected = ' SELECTED';
			} else {
				$selected = '';
			}
			$out.='<option value="'.$usergroup->usergroup_id.'"'.$selected.'>'.wp_specialchars($usergroup->usergroup_name).'</option>'."\n";
		}
		echo $out;
		echo "</select>\n";
		$tab++;
		echo "</td>\n";
		echo "<td class='sflabel' align='center' width='50'>\n";
		$gif = SFADMINURL."images/working.gif";
		$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadmincomponents.php?action=del_rank&amp;key=".$ranks['title'][$x];
		?>
		<img onclick="sfjDelRank('<?php echo $site; ?>', '<?php echo $gif; ?>', '1', 'rank<?php echo $x; ?>');" src="<?php echo SFADMINIMAGES; ?>del_cfield.png" title="<?php echo __("Delete Rank", "sforum"); ?>" alt="" />
		<?php
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";
	}

	# always have one empty slot available for new rank
	echo "<tr>\n";
	echo "<td width='30%'>\n";
	echo "<input type='text' class='sfacontrol' size = '20' tabindex='$tab' name='rankdesc[]' value='' />\n";
	$tab++;
	echo "</td>\n";
	echo "<td width='30%' class='sflabel' align='center'>\n";
	echo __("Up to", "sforum")." &#8594;\n";
	echo "<input type='text' class=' sfacontrol' size ='7' tabindex='$tab' name='rankpost[]' value='' />\n";
	$tab++;
	echo " ".__("Posts", "sforum")." \n";
	echo "</td>\n";
	echo "<td width='30%' align='center'>\n";
	echo "<select class='sfacontrol' name='rankug[]'>";
	$out = '<option value="none">'.__("No User Group Membership", "sforum").'</option>'."\n";
	foreach ($usergroups as $usergroup)
	{
		$out.='<option value="'.$usergroup->usergroup_id.'">'.wp_specialchars($usergroup->usergroup_name).'</option>'."\n";
	}
	echo $out;
	echo "</select>\n";
	echo "</td>\n";
	echo "<td>\n";
	echo "</td>\n";
	echo "</tr>\n";
	return;
}

function sfa_paint_fields_table($cfields)
{
	echo "<tr>";
	echo "<th width='200'>".__("Custom Field Name", "sforum")."</th>";
	echo "<th width='200' style='text-align:center'>".__("Custom Field Type", "sforum")."</th>";
	echo "<th width='200' style='text-align:center'>".__("Delete Custom Field", "sforum")."</th>";
	echo "</tr>";

	# organize the custom fields
	if ($cfields)
	{
		foreach ($cfields as $x => $info)
		{
			$fields['id'][$x] = $info['meta_id'];
			$fields['name'][$x] = $info['meta_key'];
			$fields['type'][$x] = $info['meta_value'];
		}
	}

	# display custom field info
	for ($x=0; $x<count($cfields); $x++)
	{
		echo "<tr>";
		echo "<td colspan='3' style='border-bottom:0px;padding:0px;'>";
		echo "<div id='cfield".$x."'>";
		echo '<table width="100%" cellspacing="0">';
		echo '<tr>';
		echo "<td width='200' align='left'>";
		echo "<input type='text' class=' sfacontrol' size='40' name=\"cfieldname[]\" value='".$fields['name'][$x]."' />";
		echo "</td>";
		echo "<td width='200' class='sflabel' align='center'>";
		echo "<select class='sfacontrol' name='cfieldtype[]'>";
		$cselected = '';
		$iselected = '';
		$tselected = '';
		if ($fields['type'][$x] == 'checkbox') $cselected = ' selected';
		if ($fields['type'][$x] == 'input') $iselected = ' selected';
		if ($fields['type'][$x] == 'textarea') $tselected = ' selected';
		echo '<option value="checkbox"'.$cselected.'>'.__("Checkbox", "sforum").'</option>';
		echo '<option value="input"'.$iselected.'>'.__("Input", "sforum").'</option>';
		echo '<option value="textarea"'.$tselected.'>'.__("Textarea", "sforum").'</option>';
		if ($cselected == '' && $iselected == '' && $tselected == '') echo '<option value="error" selected>'.__("Error!", "sforum").'</option>';
		echo "</td>";
		echo "<td width='200' align='center'>";
		$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadmincomponents.php?action=delete-cfield&id=".$fields['id'][$x]."&cfield=".$x;
		$gif = SFADMINURL."images/working.gif";
		?>
		<img onclick="sfjDelCfield('<?php echo $site; ?>', '<?php echo $gif; ?>', 'cfield<?php echo $x; ?>');" src="<?php echo SFADMINIMAGES; ?>del_cfield.png" title="Delete Custom Field" alt="" />&nbsp;
		<?php
		echo '</td>';
		echo '</tr>';
		echo "</table>";
		echo "</div";
		echo "</td>";
		echo "</tr>";
	}

	# always have one empty slot available for new custom field
	echo "<tr>\n";
	echo "<td>\n";
	echo "<input type='text' class=' sfacontrol' size='40' name=\"cfieldname[]\" value='' />";
	echo "</td>\n";
	echo "<td align='center'>\n";
	echo "<select class='sfacontrol' name='cfieldtype[]'>";
	echo '<option value="none">'.__("Select Custom Field Type", "sforum").'</option>';
	echo '<option value="checkbox">'.__("Checkbox", "sforum").'</option>';
	echo '<option value="input">'.__("Input", "sforum").'</option>';
	echo '<option value="textarea">'.__("Textarea", "sforum").'</option>';
	echo "</select>";
	echo "</td>";
	echo "<td></td>";
	echo "</tr>";
	return;
}

?>