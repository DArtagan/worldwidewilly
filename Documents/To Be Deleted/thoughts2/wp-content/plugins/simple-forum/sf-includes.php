<?php
/*
Simple:Press Forum
Includes, Constants, Filters and Actions
$LastChangedDate: 2009-06-21 21:49:37 +0100 (Sun, 21 Jun 2009) $
$Rev: 2093 $
*/

	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
		die('Access Denied');
	}

# ------------------------------------------------------------------
# Base Constants, Non-forum Filters & Actions
#
# Globally Required (Front/Back Ends)
# ------------------------------------------------------------------

	global $wpdb, $wp_version, $sfglobals;

	# GLOBAL DEFINES ===============================================

	# set up some global constants
	define('SFPLUGNAME', 'Simple:Press Forum');
	define('SFVERSION', '4.0.4');
	define('SFBUILD',    1376);
	define('SFRELEASE', '');

	define('SFWPVERSION', substr($wp_version, 0, 3));

	define('SFPLUGHOME', '<a href="http://simplepressforum.com">'.SFPLUGNAME.'</a>');
	define('SFVERCHECK', 'http://simplepressforum.com/downloads/ForumVersion.chk');
	define('SFHOMESITE', 'http://simplepressforum.com');

	if(defined(WP_SITEURL))
	{
		define('SFHOME', trailingslashit(WP_SITEURL));
	} else {
		if(function_exists('site_url')) {
			define('SFHOME', trailingslashit(site_url()));
		} else {
			define('SFHOME', trailingslashit(get_option('siteurl')));
		}
	}

	sf_load_locations(SFHOME);

	include_once(SF_PLUGIN_DIR.'/sf-user-switches.php');

	# setup some values needed for the defines
	$SFSTYLE=array();
	$SFSTYLE=get_option('sfstyle');
	$SFICONPATH = $SFSTYLE['sficon'];
	if(empty($SFICONPATH)) $SFICONPATH='default';

	define('SFURL', 	trailingslashit(get_option('sfpermalink')));
	define('SFQURL',	sf_get_sfqurl(SFURL));

	define('SFGROUPS',      	$wpdb->prefix.'sfgroups');
	define('SFFORUMS',      	$wpdb->prefix.'sfforums');
	define('SFTOPICS',    	  	$wpdb->prefix.'sftopics');
	define('SFPOSTS',     	  	$wpdb->prefix.'sfposts');
	define('SFMESSAGES',  	  	$wpdb->prefix.'sfmessages');
	define('SFWAITING',     	$wpdb->prefix.'sfwaiting');
	define('SFTRACK',     	  	$wpdb->prefix.'sftrack');
	define('SFSETTINGS',    	$wpdb->prefix.'sfsettings');
	define('SFNOTICE',      	$wpdb->prefix.'sfnotice');
	define('SFUSERGROUPS',  	$wpdb->prefix.'sfusergroups');
	define('SFPERMISSIONS', 	$wpdb->prefix.'sfpermissions');
	define('SFDEFPERMISSIONS', 	$wpdb->prefix.'sfdefpermissions');
	define('SFROLES',       	$wpdb->prefix.'sfroles');
	define('SFMEMBERS',     	$wpdb->prefix.'sfmembers');
	define('SFMEMBERSHIPS',     $wpdb->prefix.'sfmemberships');
	define('SFMETA', 	    	$wpdb->prefix.'sfmeta');
	define('SFPOSTRATINGS', 	$wpdb->prefix.'sfpostratings');

	if (defined('CUSTOM_USER_TABLE')) {
		define('SFUSERS',		CUSTOM_USER_TABLE);
	} else {
		define('SFUSERS',		$wpdb->users);
	}
	if (defined('CUSTOM_USER_META_TABLE')) {
		define('SFUSERMETA',	CUSTOM_USER_META_TABLE);
	} else {
		define('SFUSERMETA',	$wpdb->usermeta);
	}

	define('SFADMINURL',    SF_PLUGIN_URL . '/admin/');
	define('SFRESOURCES',   SF_PLUGIN_URL . '/styles/icons/'.$SFICONPATH.'/');
	define('SFCUSTOM',      SF_PLUGIN_DIR . '/styles/icons/'.$SFICONPATH.'/custom/');
	define('SFJSCRIPT',     SF_PLUGIN_URL . '/jscript/');

	define('SFSMILEYS',		WP_CONTENT_URL . '/forum-smileys/');
	define('SFAVATARURL',	WP_CONTENT_URL . '/forum-avatars/');

	define('SFDATES',       get_option('sfdates'));
	define('SFTIMES',       get_option('sftimes'));

	define('SFLOGIN',       SFHOME.'wp-login.php?action=login&amp;view=forum');
	define('SFLOGINEMAIL',  SFHOME.'wp-login.php?action=login&view=forum');
	define('SFLOGOUT',      SFHOME.'wp-login.php?action=logout&amp;redirect_to='.SFURL);
	define('SFREGISTER',    SFHOME.'wp-login.php?action=register&amp;view=forum');
	define('SFLOSTPASS',    SFHOME.'wp-login.php?action=lostpassword&amp;view=forum');
	define('SFRESETPASS',   SFHOME.'wp-login.php?action=resetpass&amp;view=forum');

	define('SFPROFILE',     sf_get_sfurl_plus_amp(SFURL).'profile=user');
	define('SFMEMBERLIST',  sf_get_sfurl_plus_amp(SFURL).'list=members');
	define('SFPOLICY',     	sf_get_sfurl_plus_amp(SFURL).'policy=reg');

	# editor defs
	define('RICHTEXT',	1);
	define('HTML',		2);
	define('BBCODE',	3);
	define('PLAIN',		4);

	# hack to get around wp_list_pages() bug
	$wpdb->hide_errors();
	$t = $wpdb->get_var("SELECT post_title FROM ".$wpdb->prefix."posts WHERE ID=".get_option('sfpage'));
	define('SFPAGETITLE', $t);
	$wpdb->show_errors();

	# GLOBAL ACTIONS/FILTERS =======================================

	# localisation and javascript
	add_action('init', 'sf_localisation');

	# wp admin access
	if (get_option('sfblockadmin') && sf_get_system_status() == 'ok') {
		add_action('init', 'sf_block_admin');
	}

	# Rewrite Rules
	add_filter('page_rewrite_rules', 'sf_set_rewrite_rules');

	add_filter('query_vars', 'sf_set_query_vars');

	# linked blog/topic posts
	add_filter('the_content', 'sf_blog_show_link');
	add_action('save_post', 'sf_save_blog_link');
	add_action('publish_post', 'sf_publish_blog_link');
	add_action('publish_page', 'sf_publish_blog_link');
	add_action('delete_post', 'sf_blog_link_delete');
	if(get_option('sflinkcomments'))
	{
		add_filter('comments_array', 'sf_topic_as_comments');
	}
	if (sf_get_system_status() == 'ok')
	{
		add_action('admin_init', 'sf_blog_link_form');
	}

	# RSS feeds
	add_action('template_redirect', 'sf_feed');
	# 404
	add_action('template_redirect', 'sfg_404');

	# user registrations
	if (function_exists('wpmu_create_user'))
	{
		add_action('wpmu_new_user', 'sf_create_member_data');
	} else {
		add_action('user_register', 'sf_create_member_data');
	}
	add_action('delete_user', 'sf_delete_member_data');
	add_action('wp_logout', 'sf_call_track_logout');
	add_action('register_form', 'sf_register_math', 1);
	add_filter('registration_errors', 'sf_register_error');

	# Email
	$sfmail=array();
	$sfmail = get_option('sfmail');
	if($sfmail['sfmailuse'])
	{
		add_filter('wp_mail_from', 'sf_mail_filter_from', 100);
		add_filter('wp_mail_from_name', 'sf_mail_filter_name', 100);
	}

	# Login/registration: has to be at root level
	include_once('credentials/sf-credentials.php');
	$sfmail=array();
	$sfmail = get_option('sfnewusermail');
	if($sfmail['sfusespfreg'])
	{
		include_once('credentials/sf-newuseremail.php');
	}

	# Credential Actions/Filters
	if (sf_get_system_status() != 'Install')
	{
		add_action('login_head', 'sf_login_header');
		add_filter('login_headerurl', 'sf_login_url');
		add_filter('login_headertitle', 'sf_login_title');
		add_action('login_form', 'sf_login_form_action');
		add_action('register_form', 'sf_login_form_action', 100);
		add_action('lostpassword_form', 'sf_login_form_action');
		add_action('register_form', 'sf_register_as_forum');
		add_action('lostpassword_form', 'sf_register_as_forum');
		add_action('wp_login', 'sf_post_login_check');

		# Dashboard notifications
		if(version_compare(SFWPVERSION, '2.7', '<')) {
			add_action('activity_box_end', 'sf_announce', 1);
		} else {
			add_action('wp_dashboard_setup', 'sf_dashboard_27_setup', 1 );
		}
	}

	# browser title
	add_filter('wp_title', 'sf_setup_browser_title');

	# Deactivating and Removal
	add_action('deactivate_simple-forum/sf-control.php', 'sf_remove_data');

# ------------------------------------------------------------------
# sf_load_foundation()
#
# Forum specific Constants, Filters, Actions and Includes
# Required by forum
# ------------------------------------------------------------------
function sf_load_foundation()
{
	global $sfglobals, $current_user;

	# setup some values needed for the defines
//	if(function_exists('site_url')) {
//		$SITEURL = trailingslashit(site_url());
//	} else {
//		$SITEURL = trailingslashit(get_option('siteurl'));
//	}

	sf_load_locations(SFHOME);

	$SFSTYLE=array();
	$SFSTYLE=get_option('sfstyle');
	$SFCSSPATH = $SFSTYLE['sfskin'];
	if(empty($SFCSSPATH)) $SFCSSPATH='default';

	define('SFSKINCSS',		SF_PLUGIN_URL . '/styles/skins/'.$SFCSSPATH.'/'.$SFCSSPATH.'.css');
	define('SFEDITORDIR',	SF_PLUGIN_DIR . '/editors/');
	define('SFEDITORURL',	SF_PLUGIN_URL . '/editors/');
	define('SFEDSTYLE',		SF_PLUGIN_URL . '/styles/editors/');
	define('SFSIZE',		$SFSTYLE['sfsize']);

	include_once('forum/sf-globals.php');
	include_once('forum/sf-primitives.php');
	include_once('forum/sf-database.php');
	include_once('forum/sf-support.php');
	include_once('forum/sf-permalinks.php');
	include_once('forum/sf-permissions.php');
	if(file_exists(SF_PLUGIN_DIR.'/forum/sf-pluggable.php'))
	{
		include_once('forum/sf-pluggable.php');
	}
	include_once('forum/sf-pagecomponents.php');
	include_once('forum/sf-page.php');
	include_once('forum/sf-forms.php');
	include_once('forum/sf-filters.php');
	include_once('forum/sf-links.php');
	if(file_exists(SF_PLUGIN_DIR.'/forum/hooks/sf-hook-template.php'))
	{
		include_once('forum/hooks/sf-hook-template.php');
	}
	include_once('messaging/sf-pmcomponents.php');
	include_once('messaging/sf-pmdatabase.php');
	include_once('messaging/sf-pmcontrol.php');

	# define $sfglobals now...(and not in the globals file)
	# needed to load correct editor at startup
	$sfglobals = array();
	$editor=array();
	$editor=get_option('sfeditor');
	if(empty($editor['sflang'])) $editor['sflang']='en';
	$sfglobals['editor'] = $editor;
	if($editor['sfusereditor'] && $current_user->ID)
	{
		$sfglobals['editor']['sfeditor'] = sf_get_member_item($current_user->ID, 'editor');
	}

	# WP Page Title
	add_filter('the_title', 'sf_setup_page_title');

	# Content Filters (Save and Display)
	add_filter('sf_save_post_content', 'sf_parse_bbcode', 1);
	add_filter('sf_save_post_content', 'balanceTags', 30);
	add_filter('sf_save_post_content', 'sf_filter_nbsp');
	add_filter('sf_save_post_content', 'sf_convert_code', 100);
	add_filter('sf_save_post_content', 'sf_profanity_check');
	add_filter('sf_save_post_content', 'sf_qt_filter', 1);
	add_filter('sf_save_post_content', 'sf_package_links', 2);

	$sffilters = array();
	$sffilters = get_option('sffilters');
	if($sffilters['sfnofollow'])
	{
		add_filter('sf_save_post_content', 'sf_rel_nofollow');
	}
	if($sffilters['sftarget'])
	{
		add_filter('sf_save_post_content', 'sf_target_blank');
	}

	if((get_option('sfdemocracy')) && (function_exists('jal_insert_poll')))
	{
		add_filter('sf_show_post_content', 'jal_insert_poll', 110);
		add_filter('sf_save_post_content', 'jal_add_dem_div');
	}
	if(get_option('sfimgenlarge'))
	{
		add_filter('sf_show_post_content', 'sf_show_image_thumbnail', 100);
	}
	add_filter('sf_save_topic_title', 'balanceTags', 30);
	add_filter('sf_save_topic_title', 'sf_filter_nohtml_kses');
	add_filter('sf_save_topic_title', 'sf_filter_square_brackets');
	add_filter('sf_save_topic_title', 'sf_profanity_check');
	add_filter('sf_save_post_name', 'strip_tags');
	add_filter('sf_save_post_name', 'trim');
	add_filter('sf_save_post_name', 'wp_specialchars', 30);
	add_filter('sf_save_post_name', 'wp_filter_kses');
	add_filter('sf_save_post_email', 'trim');
	add_filter('sf_save_post_email', 'sanitize_email');
	add_filter('sf_save_post_email', 'wp_filter_kses');
	add_filter('sf_show_post_content', 'convert_chars');
	add_filter('sf_show_post_content', 'make_clickable');
	add_filter('sf_show_post_content', 'wpautop', 30);
	add_filter('sf_show_post_content', 'sf_filter_nbsp',31);
	add_filter('sf_show_post_name', 'wptexturize');
	add_filter('sf_show_post_name', 'convert_chars');
	add_filter('sf_show_post_name', 'wp_specialchars');
	add_filter('sf_show_topic_title', 'sf_filter_square_brackets');

	return;
}

# ------------------------------------------------------------------
# sf_load_includes()
#
# Includes to satisfy Template Tags
# ------------------------------------------------------------------
function sf_load_includes()
{
	include_once('forum/sf-globals.php');
	include_once('forum/sf-primitives.php');
	include_once('forum/sf-database.php');
	include_once('forum/sf-support.php');
	include_once('forum/sf-permalinks.php');
	include_once('forum/sf-permissions.php');
	if(file_exists(SF_PLUGIN_DIR.'/forum/sf-pluggable.php'))
	{
		include_once('forum/sf-pluggable.php');
	}
	include_once('forum/sf-topiccomponents.php');
	include_once('forum/sf-filters.php');
	include_once('forum/sf-newuserview.php');
    include_once('forum/sf-forumcomponents.php');
    include_once('forum/sf-pagecomponents.php');

	return;
}

# ------------------------------------------------------------------
# sf_load_locations()
#
# Use new WP 2.6 wp-content and wp-plugin defines or create them
# if not using 2.6
# ------------------------------------------------------------------

function sf_load_locations($SITEURL)
{
	# check if already defined
	if(defined('SF_PLUGIN_DIR')) return;

	if (!defined('WP_CONTENT_DIR')) define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
	if (!defined('WP_CONTENT_URL')) define('WP_CONTENT_URL', $SITEURL . 'wp-content');

	if (defined('WP_PLUGIN_DIR'))
	{
		define('SF_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
	} else {
		define('SF_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins/' . basename(dirname(__FILE__)));
	}

	if (defined('WP_PLUGIN_URL'))
	{
		define('SF_PLUGIN_URL', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));
	} else {
		define('SF_PLUGIN_URL', WP_CONTENT_URL . '/plugins/' . basename(dirname(__FILE__)));
	}

	return;
}

?>