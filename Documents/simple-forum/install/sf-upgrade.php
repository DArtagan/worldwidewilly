<?php
/*
Simple:Press Forum
Upgrade Path Routines
$LastChangedDate: 2009-06-21 22:55:21 +0100 (Sun, 21 Jun 2009) $
$Rev: 2094 $
*/

require_once('../sf-config.php');

global $wpdb, $current_user;

$InstallID=get_option("sfInstallID");
set_current_user($InstallID);

# use WP check here since SPF stuff may not be set up
if(!current_user_can('activate_plugins'))
{
    echo (__('Access Denied', 'sforum'));
    die();
}

require_once('sf-upgradesupport.php');
require_once('../admin/sf-adminsupport.php');

if(!isset($_GET['start'])) die();

$checkval = $_GET['start'];
$build = intval($checkval);
$checklen = strlen(strval($build));
if($checklen != strlen($checkval))
{
	die(sprintf(__("An Error has Occurred During the Upgrade. Please contact support quoting build number %d", "sforum"), get_option('sfbuild')));
}

$wpdb->hide_errors();

# Start of Upgrade Routines =============

	if($build < 200)
	{
		# 1.2 =====================================================================================
		add_option('sfsortdesc', false);

		# 1.3 =====================================================================================
		add_option('sfavatars', true);
		add_option('sfshownewadmin', true);
		add_option('sfshownewuser', true);
		add_option('sfshownewcount', 6);
		add_option('sfdates', get_option('date_format'));
		add_option('sftimes', get_option('time_format'));
		add_option('sfzone', 0);

		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (forum_desc varchar(150) default NULL)";
		sf_upgrade_database(SFFORUMS, 'forum_desc', $create_ddl);

		# 1.4 =====================================================================================
		add_option('sfshowavatars', true);
		add_option('sfuserabove', false);
		add_option('sfrte', true);
		add_option('sfskin', 'default');
		add_option('sficon', 'default');

		# 1.6 =====================================================================================
		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (forum_status int(4) NOT NULL default '0')";
		sf_upgrade_database(SFFORUMS, 'forum_status', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFPOSTS. " ADD (post_pinned smallint(1) NOT NULL default '0')";
		sf_upgrade_database(SFPOSTS, 'post_pinned', $create_ddl);

		$postusers = $wpdb->get_results("SELECT user_id, COUNT(post_id) AS numposts FROM ".SFPOSTS." WHERE user_id IS NOT NULL GROUP BY user_id");
		if($postusers)
		{
			foreach($postusers as $postuser)
			{
				update_user_option($postuser->user_id, 'sfposts', $postuser->numposts);
			}
		}

		add_option('sfstopedit', true);
		add_option('sfmodmembers', false);
		add_option('sfmodusers', '');
		add_option('sftopicsort', false);

		sf_check_data_integrity();

		# 1.7 =====================================================================================
		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (topic_subs longtext)";
		sf_upgrade_database(SFTOPICS, 'topic_subs', $create_ddl);

		sf_rebuild_subscriptions();

		add_option('sfavatarsize', 50);

		delete_option('sffilters');
		delete_option('sfrte');

		$create_ddl = "ALTER TABLE ".SFGROUPS. " ADD (group_desc varchar(150) default NULL)";
		sf_upgrade_database(SFGROUPS, 'group_desc', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFGROUPS. " ADD (group_view varchar(20) default 'public')";
		sf_upgrade_database(SFGROUPS, 'group_view', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (forum_view varchar(20) default 'public')";
		sf_upgrade_database(SFFORUMS, 'forum_view', $create_ddl);

		# 1.8 =====================================================================================
		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (topic_sort varchar(4) default NULL)";
		sf_upgrade_database(SFTOPICS, 'topic_sort', $create_ddl);

		add_option('sfspam', true);
		add_option('sfpermalink', get_permalink(get_option('sfpage')));
		add_option('sfextprofile', true);
		add_option('sfusersig', true);
//		if(function_exists('site_url')) {
//			add_option('sfhome', site_url());
//		} else {
//			add_option('sfhome', get_option('home'));
//		}
		add_option('sfhome', SFHOME);

		# 1.9 =====================================================================================
		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (topic_opened bigint(20) NOT NULL default '0')";
		sf_upgrade_database(SFTOPICS, 'topic_opened', $create_ddl);

		$icons='Login;1@Register;1@Logout;1@Profile;1@Add a New Topic;1@Forum Locked;1@Reply to Post;1@Topic Locked;1@Quote and Reply;1@Edit Your Post;1@Return to Search Results;1@Subscribe;1@Forum RSS;1@Topic RSS;1';
		update_option('sfshowicon', $icons);

		add_option('sfrss', true);
		add_option('sfrsscount', 15);
		add_option('sfrsswords', 0);
		add_option('sfpagedposts', 20);
		add_option('sfgravatar', false);
		add_option('sfmodonce', false);
		add_option('sftitle', true);
		add_option('sflang', 'en');

		$fcols['topics']=true;
		$fcols['posts']=true;
		add_option('sfforumcols', $fcols);

		$tcols['first']=true;
		$tcols['last']=true;
		$tcols['posts']=true;
		$tcols['views']=true;
		add_option('sftopiccols', $tcols);

		$sql = "
		CREATE TABLE IF NOT EXISTS ".SFTRACK." (
			id bigint(20) NOT NULL auto_increment,
			trackuserid bigint(20) default 0,
			trackname varchar(25) NOT NULL,
			trackdate datetime NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		update_option('sfbuild', 200);
		echo '200';
		die();
	}

	if($build < 220)
	{
		# 2.0 =====================================================================================
		$create_ddl = "ALTER TABLE ".SFWAITING. " ADD (post_id bigint(20) NOT NULL default '0')";
		sf_upgrade_database(SFWAITING, 'post_id', $create_ddl);

		$sql = "ALTER TABLE ".SFTRACK." MODIFY trackname VARCHAR(50) NOT NULL;";
		$wpdb->query($sql);

		sf_clean_topic_subs();

		$icons=get_option('sfshowicon');
		if(strpos($icons, '@New Posts;') === false)
		{
			$icons.= '@All RSS;1@Search;1@New Posts;1';
			update_option('sfshowicon', $icons);
		}

		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFSETTINGS." (
				setting_id bigint(20) NOT NULL auto_increment,
				setting_name varchar(20) NOT NULL,
				setting_value longtext,
				PRIMARY KEY (setting_id)
		) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFNOTICE." (
				id varchar(30) NOT NULL,
				item varchar(15),
				message longtext,
				PRIMARY KEY (id)
		) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		delete_option('sfsearch');
		delete_option('sfaction');
		delete_option('sfppage');
		delete_option('sftpage');
		delete_option('sfmessage');

		add_option('sfstats', true);
		add_option('sfshownewabove', false);
		add_option('sfshowlogin', true);

		$avatar = sf_relocate_avatars();
		if($avatar != 0)
		{
			add_option('sfinstallav', $avatar);
		}

		update_option('sfbuild', 220);
		echo '220';
		die();
	}

	if($build < 225)
	{
		# 2.1 =====================================================================================
		sf_correct_sflast();

		$wpdb->query("DELETE FROM ".SFSETTINGS." WHERE setting_name <> 'maxonline';");
		$wpdb->query("ALTER TABLE ".SFSETTINGS." MODIFY setting_name VARCHAR(50) NOT NULL;");

		$create_ddl = "ALTER TABLE ".SFSETTINGS." ADD (setting_date datetime NOT NULL);";
		sf_upgrade_database(SFSETTINGS, 'setting_date', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFNOTICE." ADD (ndate datetime NOT NULL);";
		sf_upgrade_database(SFNOTICE, 'ndate', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFWAITING." ADD (user_id bigint(20) default 0);";
		sf_upgrade_database(SFWAITING, 'user_id', $create_ddl);

		$wpdb->query("ALTER TABLE ".SFFORUMS." ADD INDEX groupf_idx (group_id);");
		$wpdb->query("ALTER TABLE ".SFPOSTS." ADD INDEX topicp_idx (topic_id);");
		$wpdb->query("ALTER TABLE ".SFPOSTS." ADD INDEX forump_idx (forum_id);");
		$wpdb->query("ALTER TABLE ".SFTOPICS." ADD INDEX forumt_idx (forum_id);");

		add_option('sfregmath', true);
		add_option('sfsearchbar', true);
		add_option('sfadminspam', true);
		add_option('sfshowhome', true);
		add_option('sflockdown', false);
		add_option('sfshowmodposts', true);

		# Links
		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (blog_post_id bigint(20) NOT NULL default '0')";
		sf_upgrade_database(SFTOPICS, 'blog_post_id', $create_ddl);

		add_option('sflinkuse', false);
		add_option('sflinkexcerpt', false);
		add_option('sflinkwords', 100);
		add_option('sflinkblogtext', '%ICON% Join the forum discussion on this post');
		add_option('sflinkforumtext', '%ICON% Read original blog post');
		add_option('sflinkabove', false);

		# Announce Tag
		add_option('sfuseannounce', false);
		add_option('sfannouncecount', 8);
		add_option('sfannouncehead', 'Most Recent Forum Posts');
		add_option('sfannounceauto', false);
		add_option('sfannouncetime', 60);
		add_option('sfannouncetext', '%TOPICNAME% posted by %POSTER% in %FORUMNAME% on %DATETIME%');
		add_option('sfannouncelist', false);

		# Rankings
		$ranks=array('New Member' => 2, 'Member' => 1000);
		add_option('sfrankings', $ranks);

		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Group RSS;') === false)
		{
			$icons.= '@Group RSS;1';
			update_option('sfshowicon', $icons);
		}

		# New since build 225
		$cols=get_option('sfforumcols');
		$cols['last'] = false;
		update_option('sfforumcols', $cols);

		update_option('sfbuild', 228);
		echo '228';
		die();
	}

	if($build < 236)
	{
		# 3.0 =====================================================================================
		# Pre-create last visit dates for all existing users who don't have one
		sf_precreate_sflast();

		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFMESSAGES." (
				message_id bigint(20) NOT NULL auto_increment,
				sent_date datetime NOT NULL,
				from_id bigint(20) default NULL,
				to_id bigint(20) default NULL,
				title text,
				message text,
				message_status smallint(1) NOT NULL default '0',
				inbox smallint(1) NOT NULL default '1',
				sentbox smallint(1) NOT NULL default '1',
				is_reply smallint(1) NOT NULL default '0',
				PRIMARY KEY (message_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		# Slugs
		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (forum_slug varchar(85) NOT NULL)";
		sf_upgrade_database(SFFORUMS, 'forum_slug', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (topic_slug varchar(110) NOT NULL)";
		sf_upgrade_database(SFTOPICS, 'topic_slug', $create_ddl);

		sf_create_slugs();

		add_option('sfimgenlarge', false);
		add_option('sfthumbsize', 100);
		add_option('sfmodasadmin', false);
		add_option('sfdemocracy', false);
		add_option('sfmemberspam', true);
		add_option('sfuppath', '');

		# email option array
		$adminname = get_usermeta($current_user->ID, 'first_name');
		$sfmail = array();
		$sfmail['sfmailsender'] = get_bloginfo('name');
		$sfmail['sfmailfrom'] = str_replace(' ', '', $adminname);
		$sfmail['sfmaildomain'] = preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
		add_option('sfmail', $sfmail);

		# new user email array
		$sfmail = array();
		$sfmail['sfnewusersubject'] = 'Welcome to %BLOGNAME%';
		$sfmail['sfnewusertext'] = 'Welcome %USERNAME% to %BLOGNAME% %NEWLINE%Please find below your login details: %NEWLINE%Username: %USERNAME% %NEWLINE%Password: %PASSWORD% %NEWLINE%%LOGINURL%';
		add_option('sfnewusermail', $sfmail);

		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Send PM;') === false)
		{
			$icons.= '@Send PM;1@Return to forum;1@Compose PM;1@Go To Inbox;1@Go To Sentbox;1@Report Post;1';
			update_option('sfshowicon', $icons);
		}

		update_option('sfbuild', 250);
		echo '250';
		die();
	}

	if($build < 255)
	{
		# Change usermeta values that previously used table prefix
		$oldkeys[0] = $wpdb->prefix.'sfavatar';
		$oldkeys[1] = $wpdb->prefix.'sfposts';
		$oldkeys[2] = $wpdb->prefix.'sflast';
		$oldkeys[3] = $wpdb->prefix.'sfsubscribe';
		$oldkeys[4] = $wpdb->prefix.'sfadmin';
		$newkeys[0] = 'sfavatar';
		$newkeys[1] = 'sfposts';
		$newkeys[2] = 'sflast';
		$newkeys[3] = 'sfsubscribe';
		$newkeys[4] = 'sfadmin';

		for($x=0; $x<count($oldkeys); $x++)
		{
			$sql = "UPDATE ".SFUSERMETA." SET meta_key = '".$newkeys[$x]."' WHERE meta_key = '".$oldkeys[$x]."'";
			$wpdb->query($sql);
		}

		# Create User Groups table
		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFUSERGROUPS." (
				usergroup_id mediumint(8) unsigned NOT NULL auto_increment,
				usergroup_name varchar(50) NOT NULL default '',
				usergroup_desc varchar(150) NOT NULL default '',
				usergroup_locked tinyint(4) unsigned NOT NULL default '0',
				usergroup_is_moderator tinyint(4) unsigned NOT NULL default '0',
				PRIMARY KEY  (usergroup_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		# Create the Permissions table
		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFPERMISSIONS." (
				permission_id mediumint(8) unsigned NOT NULL auto_increment,
				forum_id mediumint(8) unsigned NOT NULL default '0',
				usergroup_id mediumint(8) unsigned NOT NULL default '0',
				permission_role mediumint(8) unsigned NOT NULL default '0',
				PRIMARY KEY  (permission_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		# Create the Roles table
		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFROLES." (
				role_id mediumint(8) unsigned NOT NULL auto_increment,
				role_name varchar(50) NOT NULL default '',
				role_desc varchar(150) NOT NULL default '',
				role_actions longtext NOT NULL,
				PRIMARY KEY  (role_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		# setup an array of info for the data upgrades
		$keys = array();

		# Create default role data
		$actions = array();
		$actions['Can view forum'] = 0;
		$actions['Can start new topics'] = 0;
		$actions['Can reply to topics'] = 0;
		$actions['Can create linked topics'] = 0;
		$actions['Can break linked topics'] = 0;
		$actions['Can edit topic titles'] = 0;
		$actions['Can pin topics'] = 0;
		$actions['Can move topics'] = 0;
		$actions['Can move posts'] = 0;
		$actions['Can lock topics'] = 0;
		$actions['Can delete topics'] = 0;
		$actions['Can edit own posts forever'] = 0;
		$actions['Can edit own posts until reply'] = 0;
		$actions['Can edit any posts'] = 0;
		$actions['Can delete any posts'] = 0;
		$actions['Can pin posts'] = 0;
		$actions['Can view users email addresses'] = 0;
		$actions['Can view members profiles'] = 0;
		$actions['Can report posts'] = 0;
		$actions['Can sort most recent posts'] = 0;
		$actions['Can bypass spam control'] = 0;
		$actions['Can bypass post moderation'] = 0;
		$actions['Can bypass post moderation once'] = 0;
		$actions['Can upload images'] = 0;
		$actions['Can use signatures'] = 0;
		$actions['Can use images in signatures'] = 0;
		$actions['Can upload avatars'] = 0;
		$actions['Can use private messaging'] = 0;
		$actions['Can subscribe'] = 0;
		$actions['Can moderate pending posts'] = 0;
		$role_name = 'No Access';
		$role_desc = 'Permission with no access to any Forum features.';
		sfa_create_role_row($role_name, $role_desc, serialize($actions));

		$actions = array();
		$actions['Can view forum'] = 1;
		$actions['Can start new topics'] = 0;
		$actions['Can reply to topics'] = 0;
		$actions['Can create linked topics'] = 0;
		$actions['Can break linked topics'] = 0;
		$actions['Can edit topic titles'] = 0;
		$actions['Can pin topics'] = 0;
		$actions['Can move topics'] = 0;
		$actions['Can move posts'] = 0;
		$actions['Can lock topics'] = 0;
		$actions['Can delete topics'] = 0;
		$actions['Can edit own posts forever'] = 0;
		$actions['Can edit own posts until reply'] = 0;
		$actions['Can edit any posts'] = 0;
		$actions['Can delete any posts'] = 0;
		$actions['Can pin posts'] = 0;
		$actions['Can view users email addresses'] = 0;
		$actions['Can view members profiles'] = 0;
		$actions['Can report posts'] = 0;
		$actions['Can sort most recent posts'] = 0;
		$actions['Can bypass spam control'] = 0;
		$actions['Can bypass post moderation'] = 0;
		$actions['Can bypass post moderation once'] = 0;
		$actions['Can upload images'] = 0;
		$actions['Can use signatures'] = 0;
		$actions['Can use images in signatures'] = 0;
		$actions['Can upload avatars'] = 0;
		$actions['Can use private messaging'] = 0;
		$actions['Can subscribe'] = 0;
		$actions['Can moderate pending posts'] = 0;
		$role_name = 'Read Only Access';
		$role_desc = 'Permission with access to only view the Forum.';
		sfa_create_role_row($role_name, $role_desc, serialize($actions));

		$actions = array();
		$actions['Can view forum'] = 1;
		$actions['Can start new topics'] = 1;
		$actions['Can reply to topics'] = 1;
		$actions['Can create linked topics'] = 0;
		$actions['Can break linked topics'] = 0;
		$actions['Can edit topic titles'] = 0;
		$actions['Can pin topics'] = 0;
		$actions['Can move topics'] = 0;
		$actions['Can move posts'] = 0;
		$actions['Can lock topics'] = 0;
		$actions['Can delete topics'] = 0;
		$actions['Can edit own posts forever'] = 0;
		$actions['Can edit own posts until reply'] = 1;
		$actions['Can edit any posts'] = 0;
		$actions['Can delete any posts'] = 0;
		$actions['Can pin posts'] = 0;
		$actions['Can view users email addresses'] = 0;
		$actions['Can view members profiles'] = 1;
		$actions['Can report posts'] = 1;
		$actions['Can sort most recent posts'] = 0;
		$actions['Can bypass spam control'] = 0;
		$actions['Can bypass post moderation'] = 0;
		$actions['Can bypass post moderation once'] = 0;
		$actions['Can upload images'] = 0;
		$actions['Can use signatures'] = 0;
		$actions['Can use images in signatures'] = 0;
		$actions['Can upload avatars'] = 1;
		$actions['Can use private messaging'] = 0;
		$actions['Can subscribe'] = 1;
		$actions['Can moderate pending posts'] = 0;
		$role_name = 'Limited Access';
		$role_desc = 'Permission with access to reply and start topics but with limited features.';
		sfa_create_role_row($role_name, $role_desc, serialize($actions));

		if(get_option('sfallowguests')) $roleid = $wpdb->insert_id;

		# Create default 'Guest' user group data
		$guests = sfa_create_usergroup_row('Guests', 'Default Usergroup for guests of the forum.', '0', false);

		$keys[0]['usergroup'] = $wpdb->insert_id;
		$keys[0]['permission'] = $roleid;

		$actions = array();
		$actions['Can view forum'] = 1;
		$actions['Can start new topics'] = 1;
		$actions['Can reply to topics'] = 1;
		$actions['Can create linked topics'] = 0;
		$actions['Can break linked topics'] = 0;
		$actions['Can edit topic titles'] = 0;
		$actions['Can pin topics'] = 0;
		$actions['Can move topics'] = 0;
		$actions['Can move posts'] = 0;
		$actions['Can lock topics'] = 0;
		$actions['Can delete topics'] = 0;
		$actions['Can edit own posts forever'] = 0;
		$actions['Can edit own posts until reply'] = 1;
		$actions['Can edit any posts'] = 0;
		$actions['Can delete any posts'] = 0;
		$actions['Can pin posts'] = 0;
		$actions['Can view users email addresses'] = 0;
		$actions['Can view members profiles'] = 1;
		$actions['Can report posts'] = 1;
		$actions['Can sort most recent posts'] = 0;
		$actions['Can bypass spam control'] = 0;
		$actions['Can bypass post moderation'] = 1;
		$actions['Can bypass post moderation once'] = 1;
		$actions['Can upload images'] = 0;
		$actions['Can use signatures'] = 1;
		$actions['Can use images in signatures'] = 1;
		$actions['Can upload avatars'] = 1;
		$actions['Can use private messaging'] = 1;
		$actions['Can subscribe'] = 1;
		$actions['Can moderate pending posts'] = 0;
		$role_name = 'Standard Access';
		$role_desc = 'Permission with access to reply and start topics with advanced features such as signatures and private messaging.';
		sfa_create_role_row($role_name, $role_desc, serialize($actions));

		$roleid = $wpdb->insert_id;

		# Create default 'Members' user group data
		$members = sfa_create_usergroup_row('Members', 'Default Usergroup for registered users of the forum.', '0', false);

		$keys[1]['usergroup'] = $members;
		$keys[1]['permission'] = $roleid;

		$actions = array();
		$actions['Can view forum'] = 1;
		$actions['Can start new topics'] = 1;
		$actions['Can reply to topics'] = 1;
		$actions['Can create linked topics'] = 0;
		$actions['Can break linked topics'] = 0;
		$actions['Can edit topic titles'] = 0;
		$actions['Can pin topics'] = 0;
		$actions['Can move topics'] = 0;
		$actions['Can move posts'] = 0;
		$actions['Can lock topics'] = 0;
		$actions['Can delete topics'] = 0;
		$actions['Can edit own posts forever'] = 1;
		$actions['Can edit own posts until reply'] = 1;
		$actions['Can edit any posts'] = 0;
		$actions['Can delete any posts'] = 0;
		$actions['Can pin posts'] = 0;
		$actions['Can view users email addresses'] = 0;
		$actions['Can view members profiles'] = 1;
		$actions['Can report posts'] = 1;
		$actions['Can sort most recent posts'] = 0;
		$actions['Can bypass spam control'] = 1;
		$actions['Can bypass post moderation'] = 1;
		$actions['Can bypass post moderation once'] = 1;
		$actions['Can upload images'] = 1;
		$actions['Can use signatures'] = 1;
		$actions['Can use images in signatures'] = 1;
		$actions['Can upload avatars'] = 1;
		$actions['Can use private messaging'] = 1;
		$actions['Can subscribe'] = 1;
		$actions['Can moderate pending posts'] = 0;
		$role_name = 'Full Access';
		$role_desc = 'Permission with Standard Access features plus image uploading and spam control bypass.';
		sfa_create_role_row($role_name, $role_desc, serialize($actions));

		$actions = array();
		$actions['Can view forum'] = 1;
		$actions['Can start new topics'] = 1;
		$actions['Can reply to topics'] = 1;
		$actions['Can create linked topics'] = 1;
		$actions['Can break linked topics'] = 1;
		$actions['Can edit topic titles'] = 1;
		$actions['Can pin topics'] = 1;
		$actions['Can move topics'] = 1;
		$actions['Can move posts'] = 1;
		$actions['Can lock topics'] = 1;
		$actions['Can delete topics'] = 1;
		$actions['Can edit own posts forever'] = 1;
		$actions['Can edit own posts until reply'] = 1;
		$actions['Can edit any posts'] = 1;
		$actions['Can delete any posts'] = 1;
		$actions['Can pin posts'] = 1;
		$actions['Can view users email addresses'] = 1;
		$actions['Can view members profiles'] = 1;
		$actions['Can report posts'] = 1;
		$actions['Can sort most recent posts'] = 1;
		$actions['Can bypass spam control'] = 1;
		$actions['Can bypass post moderation'] = 1;
		$actions['Can bypass post moderation once'] = 1;
		$actions['Can upload images'] = 1;
		$actions['Can use signatures'] = 1;
		$actions['Can use images in signatures'] = 1;
		$actions['Can upload avatars'] = 1;
		$actions['Can use private messaging'] = 1;
		$actions['Can subscribe'] = 1;
		$actions['Can moderate pending posts'] = 1;
		$role_name = 'Moderator Access';
		$role_desc = 'Permission with access to all Forum features.';
		sfa_create_role_row($role_name, $role_desc, serialize($actions));

		$roleid = $wpdb->insert_id;

		# Create default 'Moderators' user group data
		$moderators = sfa_create_usergroup_row('Moderators', 'Default Usergroup for moderators of the forum.', '1', false);

		$keys[2]['usergroup'] = $moderators;
		$keys[2]['permission'] = $roleid;

		# ensure all users have a display name set
		sf_check_all_display_names();

		# set up the current userbase into default groups etc
		sf_setup_usergroup_data($members, $moderators, true, $keys);

		update_option('sfbuild', 255);
		echo '255';
		die();
	}

	if($build < 257)
	{
		$wpdb->query("ALTER TABLE ".SFGROUPS." DROP group_view;");
		$wpdb->query("ALTER TABLE ".SFFORUMS." DROP forum_view;");

		add_option('sfdefgroup', $members);
		add_option('sfbadwords', '');
		add_option('sfreplacementwords', '');
		add_option('sfpaging', 4);
		add_option('sfadminbar', true);

		# change sftitle
		$sftitle = array();
		$sftitle['sfinclude'] = get_option('sftitle');
		$sftitle['sfnotitle'] = false;
		$sftitle['sfbanner'] = '';
		update_option('sftitle', $sftitle);

		$pm = array();
		$pm['sfpmemail'] = false;
		$pm['sfpmmax'] = 0;
		add_option('sfpm', $pm);

		$sfquicklinks = array();
		$sfquicklinks['sfqlshow'] = true;
		$sfquicklinks['sfqlcount'] = 15;
		add_option('sfquicklinks', $sfquicklinks);

		$sfcustom = array();
		$sfcustom[0]['custext']='';
		$sfcustom[0]['cuslink']='';
		$sfcustom[0]['cusicon']='';
		$sfcustom[1]['custext']='';
		$sfcustom[1]['cuslink']='';
		$sfcustom[1]['cusicon']='';
		$sfcustom[2]['custext']='';
		$sfcustom[2]['cuslink']='';
		$sfcustom[2]['cusicon']='';
		add_option('sfcustom', $sfcustom);

		# remove unwanted options
		delete_option('sfmodusers');
		delete_option('sfsubscriptions');
		delete_option('sfusersig');
		delete_option('sfstopedit');
		delete_option('sfmoderate');
		delete_option('sfmodonce');
		delete_option('sfavatars');
		delete_option('sfspam');
		delete_option('sflinkuse');
		delete_option('sfmodmembers');
		delete_option('sfadminspam');
		delete_option('sfmemberspam');

		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Moderation Queue') === false)
		{
			$icons.= '@Moderation Queue;1';
			update_option('sfshowicon', $icons);
		}

		# RSS feed urls
		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (forum_rss text)";
		sf_upgrade_database(SFFORUMS, 'forum_rss', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFGROUPS. " ADD (group_rss text)";
		sf_upgrade_database(SFGROUPS, 'group_rss', $create_ddl);

		# RSS feed urls
		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (forum_icon varchar(25) default NULL)";
		sf_upgrade_database(SFFORUMS, 'forum_icon', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFGROUPS. " ADD (group_icon varchar(25) default NULL)";
		sf_upgrade_database(SFGROUPS, 'group_icon', $create_ddl);

		# custom message for editor
		$sfpostmsg = array();
		$sfpostmsg['sfpostmsgtext'] = '';
		$sfpostmsg['sfpostmsgtopic'] = false;
		$sfpostmsg['sfpostmsgpost'] = false;
		update_option('sfpostmsg', $sfpostmsg);

		add_option('sfeditormsg','');
		add_option('sfautoupdate');
		add_option('sfcheck', true);

		update_option('sfbuild', 300);
		echo '300';
		die();
	}

	if($build < 319)
	{
		# 3.0.3 =====================================================================================
		# extra icons
		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Lock this Topic;') === false)
		{
			$icons.= '@Lock this Topic;1@Pin this Topic;1@Create Linked Post;1@Pin this Post;1@Edit Timestamp;1';
			update_option('sfshowicon', $icons);
		}

		update_option('sfbuild', 320);
		echo '320';
		die();
	}

	if($build < 321)
	{
		# 3.1  =====================================================================================
		# post id added to forum and topic tables
		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (post_id bigint(20) default NULL)";
		sf_upgrade_database(SFFORUMS, 'post_id', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (topic_count mediumint(8) default '0')";
		sf_upgrade_database(SFFORUMS, 'topic_count', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (post_id bigint(20) default NULL)";
		sf_upgrade_database(SFTOPICS, 'post_id', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (post_count mediumint(8) default '0')";
		sf_upgrade_database(SFTOPICS, 'post_count', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFPOSTS. " ADD (post_index mediumint(8) default '0')";
		sf_upgrade_database(SFPOSTS, 'post_index', $create_ddl);

		sf_build_lastposts();

		# new style array
		$sfstyle = array();
		$sfstyle['sfskin'] = get_option('sfskin');
		$sfstyle['sficon'] = get_option('sficon');
		$sfstyle['sflang'] = get_option('sflang');
		$sfstyle['sfrtl'] = false;
		add_option('sfstyle', $sfstyle);
		delete_option('sfskin');
		delete_option('sficon');
		delete_option('sflang');

		# new login array
		$sflogin = array();
		$sflogin['sfshowlogin'] = get_option('sfshowlogin');
		$sflogin['sfshowreg'] = get_option('sfshowlogin');
		add_option('sflogin', $sflogin);
		delete_option('sfshowlogin');

		$sfadminsettings=array();
		$sfadminsettings['sfnotify']=get_option('sfnotify');
		$sfadminsettings['sfadminbar']=get_option('sfadminbar');
		$sfadminsettings['sfshownewadmin']=get_option('sfshownewadmin');
		$sfadminsettings['sfmodasadmin']=get_option('sfmodasadmin');
		$sfadminsettings['sfshowmodposts']=get_option('sfshowmodposts');
		$sfadminsettings['sftools']=get_option('sfedit');
		$sfadminsettings['sfqueue']=true;
		add_option('sfadminsettings', $sfadminsettings);
		delete_option('sfnotify');
		delete_option('sfadminbar');
		delete_option('sfshownewadmin');
		delete_option('sfmodasadmin');
		delete_option('sfshowmodposts');
		delete_option('sfedit');

		$sfauto=array();
		$sfauto['sfautoupdate']=get_option('sfautoupdate');
		$sfauto['sfautotime']=300;
		add_option('sfauto', $sfauto);
		delete_option('sfautoupdate');

		$sffilters=array();
		$sffilters['sfnofollow']=false;
		$sffilters['sftarget']=true;
		add_option('sffilters', $sffilters);

		add_option('sfshowbreadcrumbs', true);

		update_option('sfbuild', 321);
		echo '321';
		die();
	}

	if($build < 324)
	{
		# sfmembers table def
		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFMEMBERS." (
				user_id bigint(20) NOT NULL default '0',
				display_name varchar(100) default NULL,
				pm smallint(1) NOT NULL default '0',
				moderator smallint(1) NOT NULL default '0',
				quicktags smallint(1) NOT NULL default '0',
				usergroups longtext default NULL,
				avatar varchar(50) default NULL,
				signature tinytext default NULL,
				sigimage tinytext default NULL,
				posts int(4) NOT NULL default '0',
				lastvisit datetime default NULL,
				subscribe longtext,
				buddies longtext,
				newposts longtext,
				checktime datetime default NULL,
				PRIMARY KEY  (user_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		sf_build_members_table('quicktags', 'upgrade');

		update_option('sfbuild', 340);
		echo '340';
		die();
	}

	if($build < 355)
	{
		# 3.1.3  ===================================================================================
		$sql = "UPDATE ".SFUSERGROUPS." SET usergroup_locked = '0'";
		$wpdb->query($sql);

		update_option('sfbuild', 356);
		echo '356';
		die();
	}

	if($build < 359)
	{
		# 4.0  =====================================================================================
		# add new Can view members profiles permission
		sf_upgrade_add_new_role('Can view members profiles', 0, true);
		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (forum_rss_private smallint(1) NOT NULL default '0')";
		sf_upgrade_database(SFFORUMS, 'forum_rss_private', $create_ddl);
		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Subscribe to this Topic;') === false)
		{
			$icons.= '@Subscribe to this Topic;1';
			update_option('sfshowicon', $icons);
		}

		if (get_option('sfrss'))
		{
			$wpdb->query("UPDATE ".SFFORUMS." SET forum_rss_private=0");
		} else {
			$wpdb->query("UPDATE ".SFFORUMS." SET forum_rss_private=1");
		}
		delete_option('sfrss');

		update_option('sfbuild', 360);
		echo '360';
		die();
	}

	if($build < 363)
	{
		# take existing forum admin and grant all new spf capabilities
		$adminid = get_option('sfadmin');
		$user = new WP_User($adminid);
		$user->add_cap('SPF Manage Options');
		$user->add_cap('SPF Manage Forums');
		$user->add_cap('SPF Manage User Groups');
		$user->add_cap('SPF Manage Permissions');
		$user->add_cap('SPF Manage Database');
		$user->add_cap('SPF Manage Components');
		$user->add_cap('SPF Manage Admins');
		$user->add_cap('SPF Manage Users');

		$create_ddl = "ALTER TABLE ".SFMEMBERS. " ADD (admin smallint(1) NOT NULL default '0')";
		sf_upgrade_database(SFMEMBERS, 'admin', $create_ddl);
		sf_update_member_item($adminid, 'admin', 1);

		delete_option('sfadmin');

		# sfmeta table def
		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFMETA." (
				meta_id bigint(20) NOT NULL auto_increment,
				meta_type varchar(20) NOT NULL,
				meta_key varchar(100) default NULL,
				meta_value longtext,
				PRIMARY KEY (meta_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		$create_ddl = "ALTER TABLE ".SFFORUMS. " ADD (topic_status_set bigint(20) default '0')";
		sf_upgrade_database(SFFORUMS, 'topic_status_set', $create_ddl);

		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (topic_status_flag bigint(20) default '0')";
		sf_upgrade_database(SFTOPICS, 'topic_status_flag', $create_ddl);

		# admin bar fixed
		$sfadminsettings=array();
		$sfadminsettings=get_option('sfadminsettings');
		$sfadminsettings['sfbarfix']=false;
		update_option('sfadminsettings', $sfadminsettings);

		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Close New Post List;') === false)
		{
			$icons.= '@Close New Post List;1';
			update_option('sfshowicon', $icons);
		}

		update_option('sfbuild', 365);
		echo '365';
		die();
	}

	if($build < 380)
	{
		# add new columns for tracking watched topics
		$create_ddl = "ALTER TABLE ".SFMEMBERS. " ADD (watches longtext)";
		sf_upgrade_database(SFMEMBERS, 'watches', $create_ddl);

		# add new Can follow topics permission
		sf_upgrade_add_new_role('Can watch topics', 0, true);

		# add new Can change topic status permission
		sf_upgrade_add_new_role('Can change topic status', 0, false, true);

		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Review Watched Topics;') === false)
		{
			$icons.= '@Review Watched Topics;1@End Topic Watch;1@Watch Topic;1';
		}
		update_option('sfshowicon', $icons);

		# remove usergroup locked column that is no longer used
		$wpdb->query("ALTER TABLE ".SFUSERGROUPS." DROP usergroup_locked;");

		# add new columns for tracking watched topics
		$create_ddl = "ALTER TABLE ".SFTOPICS. " ADD (topic_watches longtext)";
		sf_upgrade_database(SFTOPICS, 'topic_watches', $create_ddl);

		add_option('sfpostpaging', 4);

		$qt = get_option('sfquicktags');
		$sfeditor = array();
		if($qt ? $c = 2 : $c = 1);
		$sfeditor['sfeditor'] = $c;
		$sfeditor['sfusereditor'] = false;
		add_option('sfeditor', $sfeditor);
		delete_option('sfquicktags');

		$sfpostratings = array();
		$sfpostratings['sfpostratings'] = false;
		$sfpostratings['sfratingsstyle'] = 1;
		add_option('sfpostratings', $sfpostratings);

		# add new Can rate post permission
		sf_upgrade_add_new_role('Can rate posts', 0, true);

		# change members 'quicktags' to 'editor'
		$sql = "ALTER TABLE ".SFMEMBERS." CHANGE quicktags editor SMALLINT(1) NOT NULL DEFAULT '1'";
		$wpdb->query($sql);
		$sql = "UPDATE ".SFMEMBERS." SET editor=2 WHERE editor=1";
		$wpdb->query($sql);
		$sql = "UPDATE ".SFMEMBERS." SET editor=1 WHERE editor=0";
		$wpdb->query($sql);

		update_option('sfbuild', 380);
		echo '380';
		die();
	}

	if($build < 389)
	{
		# sfpostratings table def
		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFPOSTRATINGS." (
				rating_id bigint(20) NOT NULL auto_increment,
				post_id bigint(20) NOT NULL,
				vote_count bigint(20) NOT NULL,
				ratings_sum bigint(20) NOT NULL,
				ips longtext,
				members longtext,
				PRIMARY KEY  (rating_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		# add new columns for posts users have voted in
		$create_ddl = "ALTER TABLE ".SFMEMBERS." ADD (posts_rated longtext)";
		sf_upgrade_database(SFMEMBERS, 'posts_rated', $create_ddl);

		# sfdefpermissions table def
	    $sql = "
	        CREATE TABLE IF NOT EXISTS ".SFDEFPERMISSIONS." (
	            permission_id mediumint(8) unsigned NOT NULL auto_increment,
	            group_id mediumint(8) unsigned NOT NULL default '0',
	            usergroup_id mediumint(8) unsigned NOT NULL default '0',
	            permission_role mediumint(8) unsigned NOT NULL default '0',
	            PRIMARY KEY  (permission_id)
	        ) ENGINE=MyISAM ".sf_charset().";";
	    $wpdb->query($sql);

		# fill in the default permissions for existing groups
		sf_group_def_perms();

		update_option('sfbuild', 400);
		echo '400';
		die();
	}

	if($build < 439)
	{
		# create new base smiley folder
		$smiley = sf_relocate_smileys();
		if($smiley != 0)
		{
			add_option('sfinstallsm', $smiley);
		}

		sf_build_base_smileys();

		# smileys control options
		$sfsmileys = array();
		$sfsmileys['sfsmallow'] = true;
		$sfsmileys['sfsmtype'] = 1;
		update_option('sfsmileys', $sfsmileys);

		update_option('sfbuild', 480);
		echo '480';
		die();
	}

	if($build < 620)
	{
		# if default Guests user group exists use it as default for guests - otherwise leave blank
		$guests = $wpdb->get_var("SELECT usergroup_id FROM ".SFUSERGROUPS." WHERE usergroup_name='Guests'");
		add_option('sfguestsgroup', $guests);

		$sfusersnewposts = array();
		$sfusersnewposts['sfshownewuser'] = get_option('sfshownewuser');
		$sfusersnewposts['sfshownewcount'] = get_option('sfshownewcount');
		$sfusersnewposts['sfshownewabove'] = get_option('sfshownewabove');
		$sfusersnewposts['sfsortinforum'] = true;
		add_option('sfusersnewposts', $sfusersnewposts);

		delete_option('sfshownewuser');
		delete_option('sfshownewcount');
		delete_option('sfshownewabove');
		delete_option('sfallowguests');

		sf_build_tinymce_toolbar_arrays();

		# add new columns for options for users that are admins
		$create_ddl = "ALTER TABLE ".SFMEMBERS." ADD (admin_options longtext)";
		sf_upgrade_database(SFMEMBERS, 'admin_options', $create_ddl);

		$sfadminsettings = array();
		$sfadminsettings = get_option('sfadminsettings');

		$sfnewadminsettings = array();
		$sfnewadminsettings['sftools'] = $sfadminsettings['sftools'];
		$sfnewadminsettings['sfmodasadmin'] = $sfadminsettings['sfmodasadmin'];
		$sfnewadminsettings['sfshowmodposts'] = $sfadminsettings['sfshowmodposts'];
		$sfnewadminsettings['sfqueue'] = $sfadminsettings['sfqueue'];
		update_option('sfadminsettings', $sfnewadminsettings);

		$sfadminoptions = array();
		$sfadminoptions['sfadminbar'] = $sfadminsettings['sfadminbar'];
		$sfadminoptions['sfbarfix'] = $sfadminsettings['sfbarfix'];
		if (isset($sfadminsettings['sfqueue']))
		{
			$sfadminoptions['sfnotify'] = $sfadminsettings['sfnotify'];
			$sfadminoptions['sfshownewadmin'] = $sfadminsettings['sfshownewadmin'];
		}
		sf_update_member_item($current_user->ID, 'admin_options', $sfadminoptions);

		# Style and Editor array changes
		$sfstyle = array();
		$sfrepstyle = array();
		$sfstyle = get_option('sfstyle');

		$sfrepstyle['sfskin'] = $sfstyle['sfskin'];
		$sfrepstyle['sficon'] = $sfstyle['sficon'];
		$sfrepstyle['sfsize'] = '';
		update_option('sfstyle', $sfrepstyle);

		$sfeditor = array();
		$sfeditor = get_option('sfeditor');
		$sfeditor['sfrejectformat'] = false;
		$sfeditor['sftmcontentCSS'] = 'content.css';
		$sfeditor['sftmuiCSS'] = 'ui.css';
		$sfeditor['sftmdialogCSS'] = 'dialog.css';
		$sfeditor['SFhtmlCSS'] = 'htmlEditor.css';
		$sfeditor['SFbbCSS'] = 'bbcodeEditor.css';

		$sfeditor['sflang'] = $sfstyle['sflang'];
		$sfeditor['sfrtl'] = $sfstyle['sfrtl'];

		update_option('sfeditor', $sfeditor);

		# Login array changes
		$sflogin = array();
		$sflogin = get_option('sflogin');
		$sflogin['sfregmath'] = get_option('sfregmath');
		$sflogin['sfinlogin'] = true;
		$sflogin['sfregtext'] = false;
		$sflogin['sfregcheck'] = false;
		$sflogin['sfloginskin'] = true;
		update_option('sflogin', $sflogin);

		delete_option('sfregmath');

		add_option('sflinkcomments', false);
		add_option('sfshoweditdata', true);
		add_option('sfshoweditlast', false);

		update_option('sfbuild', 620);
		echo '620';
		die();
	}

	if($build < 693)
	{
		# add new columns for storing post-post edits
		$create_ddl = "ALTER TABLE ".SFPOSTS." ADD (post_edit mediumtext)";
		sf_upgrade_database(SFPOSTS, 'post_edit', $create_ddl);

		add_option('sfavataruploads', true);
		add_option('sfprivatemessaging', true);
		add_option('sfsingleforum', false);
		add_option('sftaggedforum', '');

		# transfer forum rankins from options table to our sf meta table
		$rankings = get_option('sfrankings');
		foreach ($rankings as $rank=>$posts)
		{
			$rankdata['posts'] = $posts;
			$rankdata['usergroup'] = 'none';
			$rankdata['image'] = 'none';
			sf_add_sfmeta('forum_rank', $rank, serialize($rankdata));
		}

		delete_option('sfrankings');

		# create new table for user group memberships
		$sql = "
			CREATE TABLE IF NOT EXISTS ".SFMEMBERSHIPS." (
				membership_id mediumint(8) unsigned NOT NULL auto_increment,
				user_id mediumint(8) unsigned NOT NULL default '0',
				usergroup_id mediumint(8) unsigned NOT NULL default '0',
				PRIMARY KEY  (membership_id)
			) ENGINE=MyISAM ".sf_charset().";";
		$wpdb->query($sql);

		# Build the Memberships from the usergroup column in the members table
		sf_build_memberships_table();

		update_option('sfbuild', 693);
		echo '693';
		die();
	}

	if($build < 712)
	{
		# remove usergroups column from members table
		$wpdb->query("ALTER TABLE ".SFMEMBERS." DROP usergroups;");

		$sfeditor = array();
		$sfeditor = get_option('sfeditor');
		$sfeditor['sfrelative'] = true;
		update_option('sfeditor', $sfeditor);

		$sfaiosp = array();
		$sfaiosp['sfaiosp_topic'] = true;
		$sfaiosp['sfaiosp_forum'] = true;
		$sfaiosp['sfaiosp_sep'] = '|';
		add_option('sfaiosp', $sfaiosp);

		$sfsigimagesize = array();
		$sfsigimagesize['sfsigwidth'] = 0;
		$sfsigimagesize['sfsigheight'] = 0;
		add_option('sfsigimagesize', $sfsigimagesize);

		add_option('sfmemberlistperms', true);

		# correct id in sfmembers possible problem
		$found = false;
		$found = false;
		foreach ($wpdb->get_col("DESC ".SFMEMBERS, 0) as $column )
		{
			if ($column == 'id')
			{
				$found = true;
			}
    	}
		if($found)
		{
			$wpdb->query("ALTER TABLE ".SFMEMBERS." DROP id;");
			$wpdb->query("ALTER TABLE ".SFMEMBERS." ADD PRIMARY KEY (user_id);");
		}

		# gravatar rating of G by default
		add_option('sfgmaxrating', 1);

		update_option('sfbuild', 820);
		echo '820';
		die();
	}

	if($build < 828)
	{
		# pm message slugs
		$create_ddl = "ALTER TABLE ".SFMESSAGES. " ADD (message_slug text NOT NULL)";
		sf_upgrade_database(SFMESSAGES, 'message_slug', $create_ddl);

		$wpdb->query("ALTER TABLE ".SFMESSAGES." ADD UNIQUE mslug (message_slug);");

		sf_create_message_slugs();

		update_option('sfbuild', 828);
		echo '828';
		die();
	}

	if($build < 864)
	{
		add_option('sfcbexclusions', '');
		add_option('sfshowmemberlist', true);

		# store ip for posts
		$create_ddl = "ALTER TABLE ".SFPOSTS. " ADD (poster_ip varchar(15) NOT NULL)";
		sf_upgrade_database(SFPOSTS, 'poster_ip', $create_ddl);

		update_option('sfbuild', 873);
		echo '873';
		die();
	}

	if($build < 945)
	{
		# members icon text
		$icons=get_option('sfshowicon');
		if(strpos($icons, '@Members;') === false)
		{
			$icons.= '@Members;1';
			update_option('sfshowicon', $icons);
		}

		# remove name/title and description length limits from groups and forums
		$wpdb->query("ALTER TABLE ".SFFORUMS." DROP INDEX fslug");
		$wpdb->query("ALTER TABLE ".SFTOPICS." DROP INDEX tslug");

		$wpdb->query("ALTER TABLE ".SFGROUPS." CHANGE group_name group_name TEXT NOT NULL");
		$wpdb->query("ALTER TABLE ".SFGROUPS." CHANGE group_desc group_desc TEXT NULL DEFAULT NULL");

		$wpdb->query("ALTER TABLE ".SFFORUMS." CHANGE forum_name forum_name TEXT NOT NULL");
		$wpdb->query("ALTER TABLE ".SFFORUMS." CHANGE forum_desc forum_desc TEXT NULL DEFAULT NULL");
		$wpdb->query("ALTER TABLE ".SFFORUMS." CHANGE forum_slug forum_slug TEXT NOT NULL");

		$wpdb->query("ALTER TABLE ".SFTOPICS." CHANGE topic_name topic_name TEXT NOT NULL");
		$wpdb->query("ALTER TABLE ".SFTOPICS." CHANGE topic_slug topic_slug TEXT NOT NULL");

		# admin bar - force removal from bar only
		$sfadminsettings=array();
		$sfadminsettings=get_option('sfadminsettings');
		$sfadminsettings['sfbaronly']=false;
		update_option('sfadminsettings', $sfadminsettings);

		# add ability to turn off email settings
		$sfmail = array();
		$sfmail = get_option('sfmail');
		$sfmail['sfmailuse']=true;
		update_option('sfmail', $sfmail);

		add_option('sfwpavatar', false);

		update_option('sfbuild', 945);
		echo '945';
		die();
	}

	# 4.0.1  =====================================================================================

	if($build < 1030)
	{
		# remove name/title and description length limits from user groups
		$wpdb->query("ALTER TABLE ".SFUSERGROUPS." CHANGE usergroup_name usergroup_name TEXT NOT NULL");
		$wpdb->query("ALTER TABLE ".SFUSERGROUPS." CHANGE usergroup_desc usergroup_desc TEXT NULL DEFAULT NULL");

		update_option('sfbuild', 1030);
		echo '1030';
		die();
	}

	# 4.0.2  =====================================================================================

	if($build < 1360)
	{
		# Add blockquote to tm toolbar
		sf_update_tmtoolbar_blockquote();

		add_option('sfcheckformember', true);

		# housekeeping routine to clean up duplicate memberships and members
		sf_update_membership_cleanup();

		update_option('sfbuild', 1360);
		echo '1360';
		die();
	}

	# 4.0.3  =====================================================================================

	if($build < 1373)
	{
		# dashboard settngs
		$sfadminsettings=array();
		$sfadminsettings=get_option('sfadminsettings');
		$sfadminsettings['sfdashboardposts']=true;
		$sfadminsettings['sfdashboardstats']=true;
		update_option('sfadminsettings', $sfadminsettings);

		# Optional SPF New User Email
		$sfmail = get_option('sfnewusermail');
		$sfmail['sfusespfreg'] = true;
		update_option('sfnewusermail', $sfmail);

		update_option('sfbuild', 1373);
		echo '1373';
		die();
	}

	# 4.0.4  =====================================================================================

	if($build < 1374)
	{
		# New option to not allow users access to wp admin
		add_option('sfblockadmin', false);
	}

	# Finished Upgrades ===============================================================================
	# EVERYTHING BELOW MUST BE AT THE END

	update_option('sfversion', SFVERSION);
	update_option('sfbuild', SFBUILD);
	echo SFBUILD;

	delete_option("sfInstallID");

	$wpdb->show_errors();

	die();
?>