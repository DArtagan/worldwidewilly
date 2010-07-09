<?php
/*
Simple:Press Forum
Admin Panels - Option Management
$LastChangedDate: 2009-06-06 20:57:42 +0100 (Sat, 06 Jun 2009) $
$Rev: 2002 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# Check Whether User Can Manage Options
if(!sf_current_user_can('SPF Manage Options')) {
	echo (__('Access Denied', "sforum"));
	die();
}

# Have we come gere dorect from a new install or upgrade?
if(isset($_POST['goforuminstall'])) sf_display_post_install();
if(isset($_POST['goforumupgrade'])) sf_display_post_upgrade();

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminoptions.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');

include_once('sf-adminsupport.php');
include_once('sf-admin.php');
include_once('sf-tabsupport.php');

global $adminhelpfile;
$adminhelpfile='admin-options';

# Are we setting the uninstall flag?
if(isset($_POST['sfremove']) && $_POST['sfremove'] == 'update')
{
	sfa_update_check_option('sfuninstall');
	if($_POST['sfuninstall'])
	{
		$mess.= "<br />* ".__("Simple:Press Forum will be removed when de-activated", "sforum");
		sfa_message($mess);
	}
} else {
	# Are we saving Options?
	if(isset($_POST['sfoptions'])) sfa_save_options();
}

if(sfa_get_system_status() != 'ok')
{
	include_once(SFLOADER);
	die();
}

sfa_get_options();

# = PREPARE OPTIONS DATA ======================
function sfa_get_options()
{
	global $current_user, $wpdb;

	sfa_header(__('SPF Manage Options', 'sforum'), 'icon-options');

	# prepare options data
	$sfoptions = array();
	$sfoptions['sfslug']=get_option('sfslug');
	$sfoptions['sfpagedtopics']=get_option('sfpagedtopics');
	$sfoptions['sfuninstall']=get_option('sfuninstall');
	$sfoptions['sfsortdesc']=get_option('sfsortdesc');

	# Page Title
	$sftitle = array();
	$sftitle = get_option('sftitle');
	$sfoptions['sfinclude'] = $sftitle['sfinclude'];
	$sfoptions['sfnotitle'] = $sftitle['sfnotitle'];
	$sfoptions['sfbanner'] = $sftitle['sfbanner'];

	$sfoptions['sfdates']=get_option('sfdates');
	$sfoptions['sftimes']=get_option('sftimes');
	$sfoptions['sfzone']=get_option('sfzone');
	$sfoptions['sfshowavatars']=get_option('sfshowavatars');
	$sfoptions['sfuserabove']=get_option('sfuserabove');
	$sfoptions['sftopicsort']=get_option('sftopicsort');
	$sfoptions['sfavatarsize']=get_option('sfavatarsize');
	$sfoptions['sfextprofile']=get_option('sfextprofile');
	$sfoptions['sfhome']=get_option('sfhome');
	$sfoptions['sfrsscount']=get_option('sfrsscount');
	$sfoptions['sfrsswords']=get_option('sfrsswords');
	$sfoptions['sfpagedposts']=get_option('sfpagedposts');
	$sfoptions['sfshoweditdata']=get_option('sfshoweditdata');
	$sfoptions['sfshoweditlast']=get_option('sfshoweditlast');
	$sfoptions['sfgravatar']=get_option('sfgravatar');
	$sfoptions['sfgmaxrating']=get_option('sfgmaxrating');
	$sfoptions['sfwpavatar']=get_option('sfwpavatar');
	$sfoptions['sfstats']=get_option('sfstats');
	$sfoptions['sfsearchbar']=get_option('sfsearchbar');
	$sfoptions['sflinkexcerpt']=get_option('sflinkexcerpt');
	$sfoptions['sflinkwords']=get_option('sflinkwords');
	$sfoptions['sflinkblogtext']=stripslashes(get_option('sflinkblogtext'));
	$sfoptions['sflinkforumtext']=stripslashes(get_option('sflinkforumtext'));
	$sfoptions['sflinkabove']=get_option('sflinkabove');
	$sfoptions['sflinkcomments']=get_option('sflinkcomments');
	$sfoptions['sfuseannounce']=get_option('sfuseannounce');
	$sfoptions['sfannouncecount']=get_option('sfannouncecount');
	$sfoptions['sfannouncehead']=stripslashes(get_option('sfannouncehead'));
	$sfoptions['sfannounceauto']=get_option('sfannounceauto');
	$sfoptions['sfannouncetime']=get_option('sfannouncetime');
	$sfoptions['sfannouncetext']=stripslashes(get_option('sfannouncetext'));
	$sfoptions['sfannouncelist']=get_option('sfannouncelist');
	$sfoptions['sfshowhome']=get_option('sfshowhome');
	$sfoptions['sfshowbreadcrumbs']=get_option('sfshowbreadcrumbs');
	$sfoptions['sflockdown']=get_option('sflockdown');
	$sfoptions['sfpermalink']=get_option('sfpermalink');
	$sfoptions['sfnewusermail']=get_option('sfnewusermail');
	$sfoptions['sfdefgroup']=get_option('sfdefgroup');
	$sfoptions['sfguestsgroup']=get_option('sfguestsgroup');
	$sfoptions['sfpaging']=get_option('sfpaging');
	$sfoptions['sfpostpaging']=get_option('sfpostpaging');
	$sfoptions['sfcheck']=get_option('sfcheck');
	$sfoptions['sfavataruploads']=get_option('sfavataruploads');
	$sfoptions['sfsingleforum']=get_option('sfsingleforum');
	$sfoptions['sftaggedforum']=get_option('sftaggedforum');
	$sfoptions['sfcbexclusions']=get_option('sfcbexclusions');

	# get options for bad word filtering
	$sfoptions['sfbadwords']=stripslashes(get_option('sfbadwords'));
	$sfoptions['sfreplacementwords']=stripslashes(get_option('sfreplacementwords'));

	if(empty($sfoptions['sfdates'])) $sfoptions['sfdates']='j F Y';
	if(empty($sfoptions['sftimes'])) $sfoptions['sftimes']='g:i a';
	if(empty($sfoptions['sfzone'])) $sfoptions['sfzone']='0';

	# only required for display
	$sfoptions['adminlogin'] = $current_user->user_login;

	# Load icon List
	$icons = array();
	$list = explode('@', get_option('sfshowicon'));

	foreach($list as $i)
	{
		$temp=explode(';', $i);
		$icons[$temp[0]] = $temp[1];
	}

	# Load View Column Settings
	$cols=get_option('sfforumcols');
	$sfoptions['fc_topics']=$cols['topics'];
	$sfoptions['fc_posts']=$cols['posts'];
	$sfoptions['fc_last']=$cols['last'];

	$cols=get_option('sftopiccols');
	$sfoptions['tc_first']=$cols['first'];
	$sfoptions['tc_last']=$cols['last'];
	$sfoptions['tc_posts']=$cols['posts'];
	$sfoptions['tc_views']=$cols['views'];

	# Load Email Filter Options
	$sfmail = array();
	$sfmail = get_option('sfmail');
	$sfoptions['sfmailsender']=$sfmail['sfmailsender'];
	$sfoptions['sfmailfrom']=$sfmail['sfmailfrom'];
	$sfoptions['sfmaildomain']=$sfmail['sfmaildomain'];
	$sfoptions['sfmailuse']=$sfmail['sfmailuse'];

	# Load New User Email details
	$sfmail=array();
	$sfmail = get_option('sfnewusermail');
	$sfoptions['sfusespfreg']=$sfmail['sfusespfreg'];
	$sfoptions['sfnewusersubject']=stripslashes($sfmail['sfnewusersubject']);
	$sfoptions['sfnewusertext']=stripslashes($sfmail['sfnewusertext']);

	# Load Quicklinks options
	$sfquicklinks = array();
	$sfquicklinks = get_option('sfquicklinks');
	$sfoptions['sfqlshow'] = $sfquicklinks['sfqlshow'];
	$sfoptions['sfqlcount'] = $sfquicklinks['sfqlcount'];

	# style settings
	$sfstyle = array();
	$sfstyle = get_option('sfstyle');
	$sfoptions['sfskin'] = $sfstyle['sfskin'];
	$sfoptions['sficon'] = $sfstyle['sficon'];
	$sfoptions['sfsize'] = $sfstyle['sfsize'];

	# auto update
	$sfauto=array();
	$sfauto=get_option('sfauto');
	$sfoptions['sfautoupdate']=$sfauto['sfautoupdate'];
	$sfoptions['sfautotime']=$sfauto['sfautotime'];

	# link filters
	$sffilters=array();
	$sffilters=get_option('sffilters');
	$sfoptions['sfnofollow']=$sffilters['sfnofollow'];
	$sfoptions['sftarget']=$sffilters['sftarget'];

	# users new post list
	$sfusersnewposts = array();
	$sfusersnewposts = get_option('sfusersnewposts');
	$sfoptions['sfshownewuser'] = $sfusersnewposts['sfshownewuser'];
	$sfoptions['sfshownewcount'] = $sfusersnewposts['sfshownewcount'];
	$sfoptions['sfshownewabove'] = $sfusersnewposts['sfshownewabove'];
	$sfoptions['sfsortinforum'] = $sfusersnewposts['sfsortinforum'];

	$sfsigimagesize = array();
	$sfsigimagesize = get_option('sfsigimagesize');
	$sfoptions['sfsigwidth'] = $sfsigimagesize['sfsigwidth'];
	$sfoptions['sfsigheight'] = $sfsigimagesize['sfsigheight'];

	$sfoptions['sfmemberlistperms'] = get_option('sfmemberlistperms');
	$sfoptions['sfshowmemberlist'] = get_option('sfshowmemberlist');
	$sfoptions['sfcheckformember'] = get_option('sfcheckformember');

	$sfoptions['sfblockadmin'] = get_option('sfblockadmin');

	# Finally display the form
	sfa_options_form($sfoptions, $icons);

	sfa_footer();

	return;
}

# = SAVE OPTION RECORDS =======================
function sfa_save_options()
{
	global $wpdb;

	check_admin_referer('forum-adminform_options', 'forum-adminform_options');

	$mess = '';

	$endmsg = __(" - Unable to determine forum permalink without it", "sforum");

	$pageslug = trim($_POST['sfslug'], '/');
	update_option('sfslug', $pageslug);

	if(empty($pageslug))
	{
		$mess.= "<br />* ".__("Page Slug Missing", "sforum").$endmsg;
	} else {
		$pageslug = explode('/', $pageslug);
		$thispage = $pageslug[count($pageslug)-1];
		$pageid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$thispage."'");
		if ($pageid)
		{
			update_option('sfpage', $pageid);
			update_option('sfpermalink', get_permalink($pageid));
		} else {
			$mess.= "<br />* ".__("Page Slug Does Not Exist", "sforum").$endmsg;
		}
	}

	# Page Title
	$sftitle = '';
	if(isset($_POST['sfinclude'])) $sftitle['sfinclude']=true; else $sftitle['sfinclude']=false;
	if(isset($_POST['sfnotitle'])) $sftitle['sfnotitle']=true; else $sftitle['sfnotitle']=false;
	$sftitle['sfbanner'] = $_POST['sfbanner'];
	update_option('sftitle', $sftitle);

	update_option('sfpagedtopics', $_POST['sfpagedtopics']);
	update_option('sfdates', $_POST['sfdates']);
	update_option('sftimes', $_POST['sftimes']);
	update_option('sfzone', $_POST['sfzone']);
	update_option('sfavatarsize', $_POST['sfavatarsize']);
	update_option('sfhome', $_POST['sfhome']);
	update_option('sfrsscount', $_POST['sfrsscount']);
	update_option('sfrsswords', $_POST['sfrsswords']);
	update_option('sfpagedposts', $_POST['sfpagedposts']);
	update_option('sflinkwords', $_POST['sflinkwords']);
	update_option('sflinkblogtext', $_POST['sflinkblogtext']);
	update_option('sflinkforumtext', $_POST['sflinkforumtext']);
	update_option('sfannouncecount', $_POST['sfannouncecount']);
	update_option('sfannouncehead', $_POST['sfannouncehead']);
	update_option('sfannouncetime', $_POST['sfannouncetime']);
	update_option('sfannouncetext', $_POST['sfannouncetext']);
	update_option('sftaggedforum', $_POST['sftaggedforum']);

	update_option('sfdefgroup', $_POST['sfdefgroup']);
	update_option('sfguestsgroup', $_POST['sfguestsgroup']);
	update_option('sfbadwords', $_POST['sfbadwords']);
	update_option('sfreplacementwords', $_POST['sfreplacementwords']);
	update_option('sfpaging', $_POST['sfpaging']);
	update_option('sfpostpaging', $_POST['sfpostpaging']);
	update_option('sfcbexclusions', $_POST['sfcbexclusions']);

	sfa_update_check_option('sfsortdesc');
	sfa_update_check_option('sfshowavatars');
	sfa_update_check_option('sfuserabove');
	sfa_update_check_option('sftopicsort');
	sfa_update_check_option('sfextprofile');
	sfa_update_check_option('sfgravatar');
	sfa_update_check_option('sfwpavatar');
	sfa_update_check_option('sfstats');
	sfa_update_check_option('sfsearchbar');
	sfa_update_check_option('sflinkexcerpt');
	sfa_update_check_option('sflinkabove');
	sfa_update_check_option('sflinkcomments');
	sfa_update_check_option('sfuseannounce');
	sfa_update_check_option('sfannounceauto');
	sfa_update_check_option('sfannouncelist');
	sfa_update_check_option('sfshowhome');
	sfa_update_check_option('sfshowbreadcrumbs');
	sfa_update_check_option('sflockdown');
	sfa_update_check_option('sfautoupdate');
	sfa_update_check_option('sfcheck');
	sfa_update_check_option('sfshoweditdata');
	sfa_update_check_option('sfshoweditlast');
	sfa_update_check_option('sfavataruploads');
	sfa_update_check_option('sfsingleforum');
	sfa_update_check_option('sfmemberlistperms');
	sfa_update_check_option('sfshowmemberlist');
	sfa_update_check_option('sfcheckformember');
	sfa_update_check_option('sfblockadmin');

	# Save View Column Settings
	$fcols='';
	if(isset($_POST['fc_topics'])) $fcols['topics']=true; else $fcols['topics']=false;
	if(isset($_POST['fc_posts'])) $fcols['posts']=true; else $fcols['posts']=false;
	if(isset($_POST['fc_last'])) $fcols['last']=true; else $fcols['last']=false;
	update_option('sfforumcols', $fcols);

	$tcols='';
	if(isset($_POST['tc_first'])) $tcols['first']=true; else $tcols['first']=false;
	if(isset($_POST['tc_last'])) $tcols['last']=true; else $tcols['last']=false;
	if(isset($_POST['tc_posts'])) $tcols['posts']=true; else $tcols['posts']=false;
	if(isset($_POST['tc_views'])) $tcols['views']=true; else $tcols['views']=false;
	update_option('sftopiccols', $tcols);

	# Save Icon String
	$icons = array();
	$list = explode('@', get_option('sfshowicon'));

	foreach($list as $i)
	{
		$temp=explode(';', $i);
		$icons[$temp[0]] = $temp[1];
	}

	$x = 0;
	$list='';
	foreach($icons as $key=>$value)
	{
		$list .= $key.';';
		if(isset($_POST['icon'.$x]))
		{
			$list .= '1@';
		} else {
			$list .= '0@';
		}
		$x++;
	}
	$list = substr($list, 0, strlen($list)-1);
	update_option('sfshowicon', $list);

	# Save Email Options
	# Thanks to Andrew Hamilton for these routines (mail-from plugion)
	# Remove any illegal characters and convert to lowercase both the user name and domain name
	$domain_input_errors = array('http://', 'https://', 'ftp://', 'www.');
	$domainname = strtolower($_POST['sfmaildomain']);
	$domainname = str_replace ($domain_input_errors, "", $domainname);
	$domainname = preg_replace('/[^0-9a-z\-\.]/i','',$domainname);

	$illegal_chars_username = array('(', ')', '<', '>', ',', ';', ':', '\\', '"', '[', ']', '@', ' ');
	$username = strtolower($_POST['sfmailfrom']);
	$username = str_replace ($illegal_chars_username, "", $username);

	$sfmail = '';
	$sfmail['sfmailsender']=$_POST['sfmailsender'];
	$sfmail['sfmailfrom']=$username;
	$sfmail['sfmaildomain']=$domainname;
	if(isset($_POST['sfmailuse'])) $sfmail['sfmailuse']=true; else $sfmail['sfmailuse']=false;
	update_option('sfmail', $sfmail);

	# Save new user mail options
	$sfmail = array();
	$sfmail['sfusespfreg']=$_POST['sfusespfreg'];
	$sfmail['sfnewusersubject']=$_POST['sfnewusersubject'];
	$sfmail['sfnewusertext']=$_POST['sfnewusertext'];
	update_option('sfnewusermail', $sfmail);

	# save Quicklinks options
	$sfquicklinks = array();
	if(isset($_POST['sfqlshow'])) $sfquicklinks['sfqlshow']=true; else $sfquicklinks['sfqlshow']=false;
	$sfquicklinks['sfqlcount'] = $_POST['sfqlcount'];
	update_option('sfquicklinks', $sfquicklinks);

	# Save Private Message options
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

	# style settings
	$sfstyle = array();
	$sfstyle['sfskin'] = $_POST['sfskin'];
	$sfstyle['sficon'] = $_POST['sficon'];
	$sfstyle['sfsize'] = $_POST['sfsize'];
	update_option('sfstyle', $sfstyle);

	# auto update
	$sfauto='';
	if(isset($_POST['sfautoupdate'])) $sfauto['sfautoupdate']=true; else $sfauto['sfautoupdate']=false;
	$sfauto['sfautotime']=$_POST['sfautotime'];
	if(empty($sfauto['sfautotime']) || $sfauto['sfautotime']==0) $sfauto['sfautotime']=300;
	update_option('sfauto', $sfauto);

	# link filters
	$sffilters=array();
	if(isset($_POST['sfnofollow'])) $sffilters['sfnofollow']=true; else $sffilters['sfnofollow']=false;
	if(isset($_POST['sftarget'])) $sffilters['sftarget']=true; else $sffilters['sftarget']=false;
	update_option('sffilters', $sffilters);

	# users new post list
	$sfusersnewposts = '';
	if(isset($_POST['sfshownewuser'])) $sfusersnewposts['sfshownewuser']=true; else $sfusersnewposts['sfshownewuser']=false;
	$sfusersnewposts['sfshownewcount'] = $_POST['sfshownewcount'];
	if(isset($_POST['sfshownewabove'])) $sfusersnewposts['sfshownewabove']=true; else $sfusersnewposts['sfshownewabove']=false;
	if(isset($_POST['sfsortinforum'])) $sfusersnewposts['sfsortinforum']=true; else $sfusersnewposts['sfsortinforum']=false;
	update_option('sfusersnewposts', $sfusersnewposts);

	# Some minor data integrity checks
	if(get_option('sfshowavatars') == false)
	{
		update_option('sfgravatar', false);
		update_option('sfavatarsize', 0);
	}

	if (get_option('sfgravatar'))
	{
		update_option('sfgmaxrating', $_POST['sfgmaxrating']);
	}

	$sfsigimagesize = array();
	$sfsigimagesize['sfsigwidth'] = $_POST['sfsigwidth'];
	$sfsigimagesize['sfsigheight'] = $_POST['sfsigheight'];
	update_option('sfsigimagesize', $sfsigimagesize);

	# build number update
	update_option('sfbuild', $_POST['sfbuild']);

	if(get_option('sfuninstall'))
	{
		$mess.= "<br />* ".__("Simple:Press Forum will be removed when de-activated", "sforum");
	}

	$mess = __('Options Updated', "sforum").$mess;

	sfa_message($mess);

	return;
}

# = OPTION PAGE ===============================
function sfa_options_form($sfoptions, $icons)
{
	global $wpdb;
?>
	<!-- Options Panel -->

<div class="wrap sfatag">
	<div class="sfmaincontainer">

	<form action="<?php echo(SFADMINPATH); ?>" method="post" id="sfoptionsform" name="sfoptions">
	<?php echo(sf_create_nonce('forum-adminform_options')); ?>

	<div class="clearboth"></div>

	<div id="sfoptionstabs" style="display:none">
	<ul>
		<li><a href="#T1"><span><?php _e("Global", "sforum"); ?></span></a></li>
		<li><a href="#T2"><span><?php _e("Members", "sforum"); ?></span></a></li>
		<li><a href="#T3"><span><?php _e("EMail", "sforum"); ?></span></a></li>
		<li><a href="#T4"><span><?php _e("Forums", "sforum"); ?></span></a></li>
		<li><a href="#T5"><span><?php _e("Topics", "sforum"); ?></span></a></li>
		<li><a href="#T6"><span><?php _e("Posts", "sforum"); ?></span></a></li>
		<li><a href="#T7"><span><?php _e("Links", "sforum"); ?></span></a></li>
		<li><a href="#T8"><span><?php _e("Tags", "sforum"); ?></span></a></li>
		<li><a href="#T9"><span><?php _e("Style", "sforum"); ?></span></a></li>
		<li><a href="#T10"><span><?php _e("Uninstall", "sforum"); ?></span></a></li>
		<li><a href="#T11"><span><?php _e("Toolbox", "sforum"); ?></span></a></li>
	</ul>

<?php

	sfa_paint_options_init();

#== GLOBAL Tab ============================================================

	sfa_paint_open_tab("T1");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Forum Page Details", "sforum"), true, 'forum-page-details');

				$pageslug = explode("/", $sfoptions['sfslug']);
				$thisslug = $pageslug[count($pageslug)-1];
				$pageid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$thisslug."'");
				if(!$pageid)
				{
					echo('<tr><td colspan="2"><div class="sfoptionerror">'.__('ERROR: The Page Slug is either missing or incorrect. The forum will not display until this is corrected', 'sforum').'</div></td></tr>');
				}
				sfa_paint_input(__("Forum Page Slug", "sforum"), "sfslug", $sfoptions['sfslug']);
				sfa_paint_checkbox(__("Show Forum/Topic in Page Title", "sforum"), "sfinclude", $sfoptions['sfinclude']);
				sfa_paint_checkbox(__("Remove Page Title Completely", "sforum"), "sfnotitle", $sfoptions['sfnotitle']);
				sfa_paint_input(__("Graphic Replacement URL", "sforum"), "sfbanner", $sfoptions['sfbanner'], false, true);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Lock Down Forum", "sforum"), true, 'lock-down-forum');
				sfa_paint_checkbox(__("Lock the entire forum (read only)", "sforum"), "sflockdown", $sfoptions['sflockdown']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Auto Update", "sforum"), true, 'auto-update');
				sfa_paint_checkbox(__("Use Auto Update", "sforum"), "sfautoupdate", $sfoptions['sfautoupdate']);
				sfa_paint_input(__("How many seconds before refresh", "sforum"), "sfautotime", $sfoptions['sfautotime']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("WP Admin Pages Access", "sforum"), true, 'block-admin');
				sfa_paint_checkbox(__("Block User Access to WP Admin Pages", "sforum"), "sfblockadmin", $sfoptions['sfblockadmin']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Breadcrumb Links", "sforum"), true, 'breadcrumb-home-link');
				sfa_paint_checkbox(__("Show Breadcrumbs", "sforum"), "sfshowbreadcrumbs", $sfoptions['sfshowbreadcrumbs'], false, true);
				sfa_paint_checkbox(__("Show Home Link", "sforum"), "sfshowhome", $sfoptions['sfshowhome'], false, true);
				sfa_paint_input(__("Home", "sforum"), "sfhome", $sfoptions['sfhome'], false, true);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Display Forum Components", "sforum"), true, 'display-forum-statistics');
				sfa_paint_checkbox(__("Display Forum Statistics", "sforum"), "sfstats", $sfoptions['sfstats']);
				sfa_paint_checkbox(__("Display Search Bar", "sforum"), "sfsearchbar", $sfoptions['sfsearchbar']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("QuickLinks Dropdowns", "sforum"), true, 'quicklinks-dropdowns');
				sfa_paint_checkbox(__("Show 'QuickLinks' dropdowns", "sforum"), "sfqlshow", $sfoptions['sfqlshow'], false, false);
				sfa_paint_input(__("Number of New Posts to show", "sforum"), "sfqlcount", $sfoptions['sfqlcount'], false, false);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Avatars", "sforum"), true, 'avatars');
				sfa_paint_checkbox(__("Display Avatars", "sforum"), "sfshowavatars", $sfoptions['sfshowavatars']);
				sfa_paint_checkbox(__("Use WP Avatar Settings", "sforum"), "sfwpavatar", $sfoptions['sfwpavatar']);
				sfa_paint_checkbox(__("Enable Avatar Uploading", "sforum"), "sfavataruploads", $sfoptions['sfavataruploads']);
				sfa_paint_checkbox(__("Use Gravatars by default", "sforum"), "sfgravatar", $sfoptions['sfgravatar']);
				$values = array(__('G - Suitable for all audiences', 'sforum'), __('PG - Possibly offensive, usually for audiences 13 and above', 'sforum'), __('R - Intended for adult audiences above 17', 'sforum'),__('X - Even more mature than above', 'sforum'));
				sfa_paint_radiogroup(__("Gravatar Max Rating (when enabled)", "sforum"), 'sfgmaxrating', $values, $sfoptions['sfgmaxrating'], false, true);
				sfa_paint_input(__("Maximum Avatar Width/Height (pixels)", "sforum"), "sfavatarsize", $sfoptions['sfavatarsize'], false, true);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Signature Image Size", "sforum"), true, 'sig-images');
				echo('<tr><td colspan="2">&nbsp;<u>'.__("If you are allowing Signature Images (zero = not limited)", "sforum").':</u></td></tr>');
				sfa_paint_input(__("Maximum Signature Width (pixels)", "sforum"), "sfsigwidth", $sfoptions['sfsigwidth']);
				sfa_paint_input(__("Maximum Signature Height (pixels)", "sforum"), "sfsigheight", $sfoptions['sfsigheight']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("RSS Feeds", "sforum"), true, 'rss-feeds');
				sfa_paint_input(__("Number of Recent Posts to feed", "sforum"), "sfrsscount", $sfoptions['sfrsscount']);
				sfa_paint_input(__("Limit to Number of Words (0=all)", "sforum"), "sfrsswords", $sfoptions['sfrsswords']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== MEMBERS Tab ============================================================

	sfa_paint_open_tab("T2");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Member Profiles", "sforum"), true, 'users-and-registration');
				sfa_paint_checkbox(__("Collect and Display Extended Profile", "sforum"), "sfextprofile", $sfoptions['sfextprofile']);
				sfa_paint_checkbox(__("Display Membership Lists", "sforum"), "sfshowmemberlist", $sfoptions['sfshowmemberlist']);
				sfa_paint_checkbox(__("Limit Membership Lists display to User Groups with access to the same Forum(s) as the Member (when Membership Lists allowed)", "sforum"), "sfmemberlistperms", $sfoptions['sfmemberlistperms']);
				sfa_paint_checkbox(__("Disallow Members Not Logged In To Post As Guests", "sforum"), "sfcheckformember", $sfoptions['sfcheckformember']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("User Permissions", "sforum"), true, 'user-permissions');
				sfa_paint_select_start(__("Default User Group for New Members", "sforum"), "sfdefgroup", 'sfdefgroup');
				echo(sfa_create_usergroup_select($sfoptions['sfdefgroup']));
				sfa_paint_select_end();
				sfa_paint_select_start(__("Default User Group for Guests", "sforum"), "sfguestsgroup", 'sfguestsgroup');
				echo(sfa_create_usergroup_select($sfoptions['sfguestsgroup']));
				sfa_paint_select_end();
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== EMAIL Tab ============================================================

	sfa_paint_open_tab("T3");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("New User Email", "sforum"), true, 'new-user-email');
				sfa_paint_checkbox(__("Use the SPF New User Email Version", "sforum"), "sfusespfreg", $sfoptions['sfusespfreg']);
				sfa_paint_new_user_email("sfnewusersubject", "sfnewusertext", $sfoptions['sfnewusersubject'], $sfoptions['sfnewusertext']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("EMail Address Settings", "sforum"), true, 'email-address-settings');
				sfa_paint_checkbox(__("Use the following Email Settings", "sforum"), "sfmailuse", $sfoptions['sfmailuse']);
				sfa_paint_input(__("The Senders Name", "sforum"), "sfmailsender", $sfoptions['sfmailsender'], false, true);
				sfa_paint_input(__("The EMail From Name", "sforum"), "sfmailfrom", $sfoptions['sfmailfrom'], false, true);
				sfa_paint_input(__("The EMail Domain Name", "sforum"), "sfmaildomain", $sfoptions['sfmaildomain'], false, true);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== FORUMS Tab ============================================================

	sfa_paint_open_tab("T4");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Forum View Formatting", "sforum"), true, 'forum-view-formatting');
				sfa_paint_checkbox(__("Display Recent Posts on Front Page", "sforum"), "sfshownewuser", $sfoptions['sfshownewuser']);
				sfa_paint_input(__("Number of Recent Posts to Display", "sforum"), "sfshownewcount", $sfoptions['sfshownewcount']);
				sfa_paint_checkbox(__("Display Recent Posts Above Groups", "sforum"), "sfshownewabove", $sfoptions['sfshownewabove']);
				sfa_paint_checkbox(__("Sort New Posts Within Forums", "sforum"), "sfsortinforum", $sfoptions['sfsortinforum']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Single Forum Sites", "sforum"), true, 'single-forum-sites');
				sfa_paint_checkbox(__("Skip 'Group' View on Single Forum Sites", "sforum"), "sfsingleforum", $sfoptions['sfsingleforum']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Forum View Columns", "sforum"), true, 'forum-view-columns');
				sfa_paint_checkbox(__("Show the Last Post Column", "sforum"), "fc_last", $sfoptions['fc_last']);
				sfa_paint_checkbox(__("Show the Topic Count Column", "sforum"), "fc_topics", $sfoptions['fc_topics']);
				sfa_paint_checkbox(__("Show the Post Count Column", "sforum"), "fc_posts", $sfoptions['fc_posts']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== TOPIC Tab ============================================================

	sfa_paint_open_tab("T5");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Topic View Formatting", "sforum"), true, 'topic-view-formatting');
				sfa_paint_input(__("Topics to Display Per Page", "sforum"), "sfpagedtopics", $sfoptions['sfpagedtopics']);
				sfa_paint_input(__("Number of Topic Paging Links to show", "sforum"), "sfpaging", $sfoptions['sfpaging'], false, false);
				sfa_paint_checkbox(__("Sort Topics by Most recent Postings (newest first)", "sforum"), "sftopicsort", $sfoptions['sftopicsort']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Topic View Columns", "sforum"), true, 'topic-view-columns');
				sfa_paint_checkbox(__("Show the Topic Started Column", "sforum"), "tc_first", $sfoptions['tc_first']);
				sfa_paint_checkbox(__("Show the Last Post Column", "sforum"), "tc_last", $sfoptions['tc_last']);
				sfa_paint_checkbox(__("Show the Post Count Column", "sforum"), "tc_posts", $sfoptions['tc_posts']);
				sfa_paint_checkbox(__("Show the Views Count Column", "sforum"), "tc_views", $sfoptions['tc_views']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== POSTS Tab ============================================================

	sfa_paint_open_tab("T6");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Post View Formatting", "sforum"), true, 'post-view-formatting');
				sfa_paint_input(__("Posts to Display Per Page", "sforum"), "sfpagedposts", $sfoptions['sfpagedposts']);
				sfa_paint_input(__("Number of Post Paging Links to show", "sforum"), "sfpostpaging", $sfoptions['sfpostpaging'], false, false);
				sfa_paint_checkbox(__("Display User Info Above Post", "sforum"), "sfuserabove", $sfoptions['sfuserabove']);
				sfa_paint_checkbox(__("Sort Posts Newest to Oldest", "sforum"), "sfsortdesc", $sfoptions['sfsortdesc']);
				sfa_paint_checkbox(__("Display Subsequent Post-Edit Details", "sforum"), 'sfshoweditdata', $sfoptions['sfshoweditdata']);
				sfa_paint_checkbox(__("If Showing Edits - Show Last Edit Only", "sforum"), 'sfshoweditlast', $sfoptions['sfshoweditlast']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Date/Time Formatting", "sforum"), true, 'date-time-formatting');
				sfa_paint_input(__("Date Display Format", "sforum"), "sfdates", $sfoptions['sfdates']);
				sfa_paint_input(__("Time Display Format", "sforum"), "sftimes", $sfoptions['sftimes']);
				sfa_paint_input(__("+/- Hours From Server", "sforum"), "sfzone", $sfoptions['sfzone']);
				sfa_paint_link("http://codex.wordpress.org/Formatting_Date_and_Time", __("Date/Time Help", "sforum"));
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Post Links Filtering", "sforum"), true, 'post-links-filtering');
				sfa_paint_checkbox(__("Add 'nofollow' to links", "sforum"), "sfnofollow", $sfoptions['sfnofollow']);
				sfa_paint_checkbox(__("Open links in new tab/window", "sforum"), "sftarget", $sfoptions['sftarget']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Profanity Filter", "sforum"), true, 'profanity-filter');
				$submessage=__("Enter profanities one word per line - there must be a corresponding entry in replacement words.", "sforum");
				sfa_paint_textarea(__("Profanity Word List - Words to Filter from Post", "sforum"), "sfbadwords", $sfoptions['sfbadwords'], $submessage);
				$submessage=__("Enter replacement words one word per line - there must be a corresponding entry in profanities.", "sforum");
				sfa_paint_textarea(__("Replacement Word List - Words to Replace in Post", "sforum"), "sfreplacementwords", $sfoptions['sfreplacementwords'], $submessage);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== LINKS Tab ============================================================

	sfa_paint_open_tab("T7");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Link Display Text", "sforum"), true, 'link-text-display');
				sfa_paint_checkbox(__("Display Link Above Post Content", "sforum"), "sflinkabove", $sfoptions['sflinkabove']);
				$submessage=__("Text can include HTML, class name and the placeholder %ICON%", "sforum");
				sfa_paint_wide_textarea(__("Blog Post - Link Text to Display", "sforum"), "sflinkblogtext", $sfoptions['sflinkblogtext'], $submessage);
				sfa_paint_wide_textarea(__("Forum Post - Link Text to Display", "sforum"), "sflinkforumtext", $sfoptions['sflinkforumtext'], $submessage);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Post Linking", "sforum"), true, 'post-linking');
				echo('<tr><td colspan="2">&nbsp;<u>'.__("If you are using Post Linking", "sforum").':</u></td></tr>');
				sfa_paint_checkbox(__("Post Excerpt only to Forum", "sforum"), "sflinkexcerpt", $sfoptions['sflinkexcerpt']);
				sfa_paint_input(__("If Excerpt - How many Words", "sforum"), "sflinkwords", $sfoptions['sflinkwords']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Show Topic Posts as Comments", "sforum"), true, 'show-as-comments');
				echo('<tr><td colspan="2">&nbsp;<u>'.__("If you are using Post Linking", "sforum").':</u></td></tr>');
				sfa_paint_checkbox(__("Append Topic Posts to Blog Post Comments", "sforum"), "sflinkcomments", $sfoptions['sflinkcomments']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== TAGS Tab ============================================================

	sfa_paint_open_tab("T8");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Announce Template Tag", "sforum"), true, 'announce-template-tag');
				sfa_paint_checkbox(__("Enable Announce Tag", "sforum"), "sfuseannounce", $sfoptions['sfuseannounce']);
				sfa_paint_checkbox(__("Display as Unordered List (default=Table)", "sforum"), "sfannouncelist", $sfoptions['sfannouncelist']);
				sfa_paint_input(__("How many most recent posts to display", "sforum"), "sfannouncecount", $sfoptions['sfannouncecount']);
				sfa_paint_input(__("Tag display Heading", "sforum"), "sfannouncehead", $sfoptions['sfannouncehead']);
				$submessage=__("Text can include the following placeholders: %FORUMNAME%, %TOPICNAME%, %POSTER% and %DATETIME%", "sforum");
				sfa_paint_wide_textarea(__("Text format of tag post link", "sforum"), "sfannouncetext", $sfoptions['sfannouncetext'], $submessage);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Announce Auto Refresh", "sforum"), true, 'announce-auto-refresh');
				sfa_paint_checkbox(__("Enable Auto-Refresh", "sforum"), "sfannounceauto", $sfoptions['sfannounceauto']);
				sfa_paint_input(__("How many seconds before refresh", "sforum"), "sfannouncetime", $sfoptions['sfannouncetime']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Add Topic Tag", "sforum"), true, 'add-topic-tag');
				sfa_paint_select_start(__("Select Forum for 'Add Topic' Tag", "sforum"), "sftaggedforum", "sftaggedforum");
				echo(sfa_create_forum_select($sfoptions['sftaggedforum']));
				sfa_paint_select_end();
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_optionupdate();

	sfa_paint_close_tab();

#== STYLE Tab ============================================================

	sfa_paint_open_tab("T9");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Forum Skin", "sforum"), true, 'forum-skin-icons');
				sfa_paint_select_start(__("Select Skin", "sforum"), "sfskin", "sfskin");
				echo(sfa_create_skin_select($sfoptions['sfskin']));
				sfa_paint_select_end();
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Adjust Font Size", "sforum"), true, 'adjust-font-size');
				sfa_paint_input(__("Enter a Base Font Size (or Leave Empty)", "sforum"), "sfsize", $sfoptions['sfsize']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Forum Icons", "sforum"), true, 'forum-skin-icons');
				sfa_paint_select_start(__("Select Icon Set", "sforum"), "sficon", "sficon");
				echo(sfa_create_icon_select($sfoptions['sficon']));
				sfa_paint_select_end();
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Display Icon Text", "sforum"), true, 'display-icon-text', false);

				echo '<div id="checkboxset">';
				$i = count($icons);
				$x = 0;

				$rows  = ($i/4);
				if(!is_int($rows)) $rows=(intval($rows)+1);
				$thisrow = 0;
				$closed=false;
				echo '<table class="outsershell" width="100%" border="0"><tr valign="top">';


				foreach($icons as $key=>$value)
				{

					if($thisrow == 0)
					{
						echo '<td width="25%" valign="top">';
						echo '<table class="form-table">';
						$closed = false;
					}
					sfa_paint_checkbox(__($key, "sforum"), "icon$x", $value, false, false, false);
					$x++;

					$thisrow++;
					if($thisrow == $rows)
					{
						echo '</table>';
						echo '</td>';
						$thisrow = 0;
						$closed=true;
					}
				}

				if(!$closed)
				{
					echo '</table></td>';
				}
				echo '</tr></table>';
				echo '</div>';

				echo '<br /><div class="clearboth"></div>';
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

			sfa_paint_close_fieldset(false);
		sfa_paint_close_panel();

	sfa_paint_optionupdate();
	echo "</div>\n";

#== UNINSTALL Tab ==========================================================

	sfa_paint_open_tab("T10");

		sfa_paint_open_panel();

			sfa_paint_open_fieldset(__("Removing Simple:Press Forum", "sforum"), true, 'uninstall', false);
				echo '<br /><div class="sfoptionerror">';
				echo __("Should you, at any time, decide to remove Simple:Press Forum, check the option below and then deactivate the plugin in the normal way.<br />THIS WILL REMOVE ALL DATA AND CAN NOT BE REVERSED", "sforum");
				echo '</div><br />';
			sfa_paint_close_fieldset(false);

			sfa_paint_open_fieldset(__("Uninstall", "sforum"), true, 'uninstall');
				sfa_paint_checkbox(__("Completely Remove Forum", "sforum"), "sfuninstall", $sfoptions['sfuninstall']);
			sfa_paint_close_fieldset();

		sfa_paint_close_panel();

		echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">';
		echo '<tr>';
		echo '<td class="sfabuttonitem sfabgupdate" align="right">';
		echo '<input type="hidden" class="sfhiddeninput" name="sfremove" id="sfremove" value="submit" />';
		echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjSubmit(\'sfremove\')">';
		echo sfa_split_heading(__("Save and Prepare to Remove", "sforum"), 2);
		echo '</a>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';

	sfa_paint_close_tab();


#== TOOLBOX Tab ============================================================

	sfa_paint_open_tab("T11");

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Update Forum Permalink", "sforum"), true, 'forum-permalink', false);
				echo('<p>&nbsp;'.__("Current Permalink:", "sforum").'<br /></p><div id="adminupresult"><p>&nbsp;'.$sfoptions["sfpermalink"].'</p></div><br />');
				echo "<table class='form-table' width='100%'>\n";
				sfa_paint_update_permalink();
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Check for Updates", "sforum"), true, 'check-for-updates');
				sfa_paint_update_check();
				sfa_paint_checkbox(__("Auto Check for Updates", "sforum"), "sfcheck", $sfoptions['sfcheck']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

	sfa_paint_tab_right_cell();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Exclude Check Boxes/Radio Buttons", "sforum"), true, 'exclude-check-box');
				sfa_paint_input(__("Exclude ID List", "sforum"), "sfcbexclusions", $sfoptions['sfcbexclusions']);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_open_panel();
			sfa_paint_open_fieldset(__("Modify Build Number", "sforum"), true, 'modify-build-number');
				echo('<tr><td colspan="2"><div class="sfoptionerror">'.__('WARNING: This value should not be changed unless requested by the Simple:Press Forum team in the support forum as it may cause the install/upgrade script to be re-run.', 'sforum').'</div></td></tr>');
				sfa_paint_input(__("Build Number", "sforum"), "sfbuild", get_option('sfbuild'), false, false);
			sfa_paint_close_fieldset();
		sfa_paint_close_panel();

		sfa_paint_optionupdate();

	sfa_paint_close_tab();

?>
	</div><br />
	</form>
</div>
</div>
<br /><br />

<?php
	return;
}

function sfa_paint_new_user_email($subjectname, $textname, $subjectvalue, $textvalue)
{
	global $tab;

	echo "<tr>\n";
	echo "<td class='sflabel'>\n";
	echo "<small><strong>".__("The following placeholders are available: %USERNAME%, %PASSWORD%, %BLOGNAME%, %SITEURL%, %LOGINURL%", "sforum")."</strong></small>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td class='sflabel'>\n";
	echo __("Email Subject Line", "sforum").":\n";
	echo "<input type='text' class='sfpostcontrol' tabindex='$tab' name='$subjectname' value='$subjectvalue' />\n";
	echo "</td>\n";
	echo "</tr>\n";
	$tab++;
	echo "<tr>\n";
	echo "<td class='sflabel'>\n";
	echo __("Email Message", "sforum").":\n";
	echo "<div class='sfformcontainer'>\n";
	echo "<textarea rows='9' cols='80' class='sftextarea' tabindex='$tab' name='$textname'>$textvalue</textarea>\n";
	echo "</div>\n";
	echo "</td>\n";
	echo "</tr>\n";
	$tab++;
	return;
}

function sfa_paint_update_permalink()
{
	echo "<tr valign='top'>\n";
	echo "<td width='50%'>\n";
	$site = SFADMINURL."ahah/sf-ahahadmintoolbox.php?item=upperm";
	$target = 'adminupresult';
	$gif = SFADMINURL."images/working.gif";
	echo '<table class="sfabuttontable">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgpermalink" align="right">';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjadminTool(\''.$site.'\', \''.$target.'\', \''.$gif.'\')">';
	echo sfa_split_heading(__('Update Forum Permalink', 'sforum'), 1);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	echo '</tr>';

	return;
}

function sfa_paint_update_check()
{
	$buttontext=__("Check for Updates", "sforum");
	$site=SFADMINURL."ahah/sf-ahahadmintoolbox.php?item=upcheck";
	$target='adminucresult';
	$gif= SFADMINURL."images/working.gif";

	echo "<tr valign='top'>\n";
	echo "<td width='50%'>\n";
	$version = __("Version:", "sforum").'&nbsp;<strong>'.get_option('sfversion').'</strong>';
	$build = __("Build:  ", "sforum").'&nbsp;&nbsp;&nbsp;&nbsp;<strong>'.get_option('sfbuild').'</strong>';

	?>
		<p><?php _e("The Installed Version", "sforum") ?>:<br />
		<?php echo($version); ?><br />
		<?php echo($build); ?><br />
		</p><br />
	<?php
	echo '<table class="sfabuttontable">';
	echo '<tr>';
	echo '<td class="sfabuttonitem sfabgcheckupdate" align="right">';
	echo '<a class="sfasmallbutton" href="javascript:void(0);" onclick="sfjadminTool(\''.$site.'\', \''.$target.'\', \''.$gif.'\')">';
	echo sfa_split_heading(__('Check For Updates', 'sforum'), 1);
	echo '</a>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo "</td>\n";
	echo "<td align='center' valign='middle'>\n";
	echo "<div id='adminucresult'>\n";
	echo "</div>\n";
	echo "</td>\n";
	echo "</tr>\n";

	return;
}

function sfa_paint_optionupdate()
{
	echo '<table class="sfabuttontable" align="right" style="margin: 20px 13px 0 0;">'."\n";
	echo '<tr>'."\n";
	echo '<td class="sfabuttonitem sfabgupdate" align="right">'."\n";
	echo '<input type="hidden" class="sfhiddeninput" name="sfoptions" value="submit" />'."\n";
	echo '<a class="sfasmallbutton" href="javascript:document.sfoptions.submit();">'."\n";
	echo sfa_split_heading(__("Update All Options", "sforum"), 1)."\n";
	echo '</a>'."\n";
	echo '</td>'."\n";
	echo '</tr>'."\n";
	echo '</table>'."\n";

	return;
}

# Display a help panel immediately after install
function sf_display_post_install()
{
	$source = SF_PLUGIN_URL."/admin/help/install/sf-postinstall-help.php";
	echo '<a id="autoload" href="'.$source.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true, reflow: true, targetX: \'sfoptionsform\', width: 1000} )"></a>';

	?>
	<script type="text/javascript">
	hs.addEventListener(window, "load", function() {
	   /* click the element virtually: */
	   document.getElementById("autoload").onclick();
	});
	</script>
<?php
	return;
}

# Display a help panel immediately after upgrade
function sf_display_post_upgrade()
{
	$source = SF_PLUGIN_URL."/admin/help/install/sf-postupgrade-help.php";
	echo '<a id="autoload" href="'.$source.'" onclick="return hs.htmlExpand(this, { objectType: \'ajax\', preserveContent: true, reflow: true, targetX: \'sfoptionsform\', width: 1000} )"></a>';

	?>
	<script type="text/javascript">
	hs.addEventListener(window, "load", function() {
	   /* click the element virtually: */
	   document.getElementById("autoload").onclick();
	});
	</script>
<?php
	return;
}

?>