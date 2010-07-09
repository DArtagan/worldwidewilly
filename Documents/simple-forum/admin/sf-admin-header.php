<?php
/*
Simple:Press Forum
Admin Header Routines
$LastChangedDate: 2009-06-22 01:15:51 +0100 (Mon, 22 Jun 2009) $
$Rev: 2095 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

global $ISFORUMADMIN, $apage;

# set to current admin page being served
$apage = sf_extract_admin_page();
# WP Hooks - Admin Boot
add_action('after_plugin_row', 'sfa_check_plugin_version' );
add_filter('plugin_action_links', 'sf_add_plugin_action', 10, 2);
add_action('admin_head', 'sfa_admin_header', 1);
add_action('in_admin_footer', 'sfa_admin_footer');
add_action('admin_init', 'sfa_update_permalink');
add_filter('contextual_help', 'sf_add_slider_help');

# = SETUP ADMIN MENU ==========================
function sfa_admin_menu()
{
	global $current_user;

	$status = sf_get_system_status();
	if ($status == 'ok')
	{
		$parent = 'simple-forum/admin/sf-adminforums.php';

		if (sf_current_user_can('SPF Manage Options') ||
		    sf_current_user_can('SPF Manage Forums') ||
		    sf_current_user_can('SPF Manage Components') ||
		    sf_current_user_can('SPF Manage User Groups') ||
		    sf_current_user_can('SPF Manage Permissions') ||
		    sf_current_user_can('SPF Manage Database') ||
		    sf_current_user_can('SPF Manage Users') ||
		    sf_current_user_can('SPF Manage Admins') ||
			sf_get_member_item($current_user->ID, 'moderator'))

		{
			if(function_exists('add_object_page'))
			{
				add_object_page('Simple:Press Forum', __('Forum', 'sforum'), 8, $parent, '', 'div');
			} else {
				add_menu_page('Simple:Press Forum', __('Forum', 'sforum'), 8, $parent, '');
			}

			if (sf_current_user_can('SPF Manage Forums'))
			{
				add_submenu_page($parent, __('Forums', 'sforum'), __('Forums', 'sforum'), 0, 'simple-forum/admin/sf-adminforums.php');
			}
			if (sf_current_user_can('SPF Manage Options'))
			{
				add_submenu_page($parent, __('Options', 'sforum'), __('Options', 'sforum'), 0, 'simple-forum/admin/sf-adminoptions.php');
			}
			if (sf_current_user_can('SPF Manage Components'))
			{
				add_submenu_page($parent, __('Components', 'sforum'), __('Components', 'sforum'), 0, 'simple-forum/admin/sf-admincomponents.php');
			}
			if (sf_current_user_can('SPF Manage User Groups'))
			{
				add_submenu_page($parent, __('Usergroups', 'sforum'), __('User Groups', 'sforum'), 0, 'simple-forum/admin/sf-adminusergroups.php');
			}
			if (sf_current_user_can('SPF Manage Permissions'))
			{
				add_submenu_page($parent, __('Permissions', 'sforum'), __('Permission Sets', 'sforum'), 0, 'simple-forum/admin/sf-adminpermissions.php');
			}
			if (sf_current_user_can('SPF Manage Users'))
			{
				add_submenu_page($parent, __('Users', 'sforum'), __('Users', 'sforum'), 0, 'simple-forum/admin/sf-adminusers.php');
			}
			if (sf_current_user_can('SPF Manage Admins') || sf_get_member_item($current_user->ID, 'moderator'))
			{
				add_submenu_page($parent, __('Admins', 'sforum'), __('Admins', 'sforum'), 0, 'simple-forum/admin/sf-adminadmins.php');
			}
			if (sf_current_user_can('SPF Manage Database'))
			{
				add_submenu_page($parent, __('Database', 'sforum'), __('Database', 'sforum'), 0, 'simple-forum/admin/sf-admindatabase.php');
			}

			add_submenu_page($parent, __('Online Help', 'sforum'), __('Online Help', 'sforum'), 0, 'simple-forum/admin/sf-adminpopuphelp.php');
		}

	} else {

		$parent = 'simple-forum/sf-loader.php';

		if(function_exists('add_object_page'))
		{
			add_object_page('Simple:Press Forum', __("Forum", "sforum"), 'activate_plugins', $parent, '', 'div');
		} else {
			add_menu_page('Simple:Press Forum', __("Forum", "sforum"), 'activate_plugins', $parent, '');
		}

		if($status == 'Install')
		{
			add_submenu_page($parent, __("Install Simple:Press Forum", "sforum"), __("Install Simple:Press Forum", "sforum"), 'activate_plugins', 'simple-forum/sf-loader.php');
		} else {
			add_submenu_page($parent, __("Upgrade Simple:Press Forum", "sforum"), __("Upgrade Simple:Press Forum", "sforum"), 'activate_plugins', 'simple-forum/sf-loader.php');
		}

	}
}

# = SETUP ADMIN JS => WP 2.5 and above =========
function sfa_admin_load_js()
{
	global $apage;

	wp_enqueue_script('jquery');

	if(strpos('options components users admins', $apage) !== false)
	{
		wp_enqueue_script('jquery.ui.core', '/wp-includes/js/jquery/ui.core.js', array('jquery'));
		wp_enqueue_script('jquery.ui.tabs', '/wp-includes/js/jquery/ui.tabs.js', array('jquery'));
	}
	if($apage == 'components')
	{
		wp_enqueue_script('jquery.ui.sortable', '/wp-includes/js/jquery/ui.sortable.js', array('jquery'));
	}
	if(SF_USE_PRETTY_CBOX)
	{
		wp_enqueue_script('jquery.checkboxes', SFJSCRIPT.'prettyCheckboxes.js', array('jquery'));
	}

	wp_enqueue_script('highslide', SFJSCRIPT.'highslide/highslide.js');
	wp_enqueue_script('sfadmin', SFADMINURL.'jscript/sf-admin.js');
	if($apage == 'database')
	{
		wp_enqueue_script('sfjs.calendar', SFADMINURL.'jscript/sf-calendar.js');
	}

	?>
	<script type='text/javascript'>
	var pcbExclusions = new Array(
		"sfcbdummy"
	);
	</script>
	<?php

	return;
}

# = SETUP ADMIN HEADER => WP 2.5 and above =====
function sfa_admin_header()
{
	global $ISFORUMADMIN, $apage;

	 ?>
		<link rel="stylesheet" type="text/css" href="<?php echo(SFADMINURL);?>css/sf-menu.css" />
	<?php

	if($ISFORUMADMIN == false) return;

	?>
	<link rel="stylesheet" type="text/css" href="<?php echo(SFADMINURL);?>css/sf-admin.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo(SFJSCRIPT);?>highslide/highslide.css" />

	<script type="text/javascript">
		hs.graphicsDir = "<?php echo(SFJSCRIPT); ?>highslide/graphics/";
		hs.outlineType = "rounded-white";
		hs.outlineWhileAnimating = true;
		hs.cacheAjax = false;
		hs.showCredits = false;
		hs.lang = {
			closeText : '',
			closeTitle : '<?php _e("Close", "sforum");?>',
			moveText  : '',
			moveTitle : '<?php _e("Move", "sforum");?>',
			loadingText  : '<?php _e("Loading", "sforum");?>'
		};
	</script>

	<script type="text/javascript">
		jQuery.noConflict();
		jQuery(document).ready(function()
		{
			<?php if(SF_USE_PRETTY_CBOX) { ?>
			jQuery('input[type=checkbox],input[type=radio]').prettyCheckboxes();
			<?php } ?>
		<?php
			if(strpos('options components users admins', $apage) !== false) {
				if(version_compare(SFWPVERSION, '2.8', '<') ? $jqtabs='#sf'.$apage.'tabs > ul' : $jqtabs='#sf'.$apage.'tabs'); ?>
				jQuery("<?php echo($jqtabs); ?>").tabs({ fx: { opacity: 'toggle' } });
				var tabid;
				tabid=document.getElementById('sf<?php echo($apage); ?>tabs');
				if(tabid != null)
				{
					tabid.style.display="block";
				}
		<?php }
			if($apage == 'components') { ?>
			jQuery("#sftbarstan").sortable({
				handle : '.handle',
				update : function () {
					jQuery("input#stan_buttons").val(jQuery("#sftbarstan").sortable('serialize'));
				}
			});
			jQuery("#sftbarplug").sortable({
				handle : '.handle',
				update : function () {
					jQuery("input#plug_buttons").val(jQuery("#sftbarplug").sortable('serialize'));
				}
			});
		<?php } ?>
		});

	</script>

	<?php

	return;
}

# = ADD FOOTER CREDIT ================
function sfa_admin_footer()
{
	global $ISFORUMADMIN;
	if($ISFORUMADMIN) printf(__('%1$s | Version %2$s', 'sforum').'<br />', SFPLUGHOME, SFVERSION);
	return;
}

# = PLUGIN VERSION CHECK ON PLUGINS PAGE ======
function sfa_check_plugin_version($plugin)
{
 	if($plugin == 'simple-forum/sf-control.php')
 	{
 		$msg='';

 		if(sf_get_system_status() == 'upgrade')
 		{
 			$msg = __("Select the Forum Menu to complete the upgrade of your Simple:Press Forum", "sforum");
 		} else {
			$checkfile = SFVERCHECK;

			$vcheck = wp_remote_fopen($checkfile);
			if($vcheck)
			{
				$installed_version = get_option('sfversion');
				$installed_build = get_option('sfbuild');

				if(empty($installed_version)) return;

				$status = explode('@', $vcheck);
				$home_version = $status[1];
				$home_build   = $status[3];
				$home_message = $status[5];

				if((version_compare($home_version, $installed_version, '>') == 1) || (version_compare($home_build, $installed_build, '>') == 1))
				{
					$msg = __("Latest version available:", "sforum").' <strong>'.$home_version.'</strong> - '.__("Build:", "sforum").' <strong>'.$home_build.'</strong> - '.$home_message;
					$msg.= __("For details and to download please visit", "sforum").': '.SFPLUGHOME;
					$msg.= ' ('.__("Please Note: Automatic Upgrade is not available", "sforum").')';
				}
			}
		}
		if($msg) echo '<td colspan="5" class="plugin-update"><div class="update-message">'.$msg.'</div></td>';
	}
	return;
}

function sf_add_plugin_action($links, $plugin)
{
	if($plugin == 'simple-forum/sf-control.php')
	{
		$sysstatus = sfa_get_system_status();
		if($sysstatus != "ok")
		{
			if(function_exists('admin_url'))
			{
				$base = admin_url();
			} else {
//				$base = get_option('siteurl');
//				if(defined(WP_SITEURL))
//				{
//					$base=WP_SITEURL;
//				} else {
//					$base=get_option('siteurl');
//				}
				$base=SFHOME;
			}
			$actionlink = '<a href="'.$base.'admin.php?page=simple-forum/sf-loader.php">'.$sysstatus.'</a>';
			array_unshift( $links, $actionlink );
		}
	}
	return $links;
}

function sfa_get_system_status()
{
	$current_version = get_option('sfversion');
	$current_build = get_option('sfbuild');

	# Has the systen been installed?
	if(version_compare($current_version, '1.0', '<'))
	{
		return 'Install';
	}

	# Base already installed - check Version and Build Number
	if(($current_build < SFBUILD) || ($current_version > SFVERSION))
	{
		return 'Upgrade';
	}
	return 'ok';
}

function sf_extract_admin_page()
{
	$apage = strrchr($_SERVER['QUERY_STRING'] , '/');
	if(strpos($apage, '/sf-', 0) === false) return 'none';
	$apage = substr($apage, 9, 25);
	$apage = reset(explode(".php", $apage));
	return $apage;
}

function sf_add_slider_help($help)
{
	global $ISFORUMADMIN;

	if(!$ISFORUMADMIN) return $help;

	$out.= '<h5>'.__("Simple:Press Forum - Help Options", "sforum").'</h5>';
	$out.= '<div class="metabox-prefs">';
	$out.= '<p>'.sprintf(__("For contextual help with Simple:Press Forum, click on the %s links", "sforum"), __("Help", "sforum")).'<br />';
	$out.= __("For troubleshooting, how-tos, template tags, program hooks and more, use the", "sforum").' <a href="/wp-admin/admin.php?page=simple-forum/admin/sf-adminpopuphelp.php">'.__("Online Help", "sforum").'</a><br />';
	$out.= __("If you cannot find your answer and need extra help, please visit our", "sforum").' <a href="'.SFHOMESITE.'/support-forum">'.__("Support Forums", "sforum").'</a></p>';
	$out.= '</div>';

	return $out;
}

?>