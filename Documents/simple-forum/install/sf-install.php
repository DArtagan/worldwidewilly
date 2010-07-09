<?php
/*
Simple:Press Forum
Main Forum Installer (New Instalations)
$LastChangedDate: 2009-06-21 16:30:58 +0100 (Sun, 21 Jun 2009) $
$Rev: 2090 $
*/

require_once('../sf-config.php');

global $current_user, $wpdb;

$InstallID=get_option("sfInstallID");
set_current_user($InstallID);

# use WP check here since SPF stuff wont be set up
if(!current_user_can('activate_plugins'))
{
    echo (__('Access Denied', 'sforum'));
    die();
}

require_once('sf-upgradesupport.php');
require_once('../admin/sf-adminsupport.php');

if(isset($_GET['phase']))
{
	$phase = sf_syscheckint($_GET['phase']);
	if($phase == 0)
	{
		echo '<h5>'.__("Installing", "sforum").'...</h5>';
	} else {
		echo '<h5>'.__("Phase", "sforum").' - '.$phase.' - ';
	}
	sf_perform_install($phase);
}
die();

function sf_perform_install($phase)
{

	global $wpdb, $current_user;

	switch($phase)
	{
		case 1:
				# CREATE FORUM TABLES ----------------------------------

				# sfforums table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFFORUMS." (
						forum_id bigint(20) NOT NULL auto_increment,
						forum_name text NOT NULL,
						group_id bigint(20) NOT NULL,
						forum_seq int(4) default NULL,
						forum_desc text default NULL,
						forum_status int(4) NOT NULL default '0',
						forum_slug text NOT NULL,
						forum_rss text default NULL,
						forum_icon varchar(25) default NULL,
						post_id bigint(20) default NULL,
						topic_count mediumint(8) default '0',
						forum_rss_private smallint(1) NOT NULL default '0',
						topic_status_set bigint(20) default '0',
						PRIMARY KEY  (forum_id),
						KEY groupf_idx (group_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfgroups table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFGROUPS." (
						group_id bigint(20) NOT NULL auto_increment,
						group_name text NOT NULL,
						group_seq int(4) default NULL,
						group_desc text default NULL,
						group_rss text default NULL,
						group_icon varchar(25) default NULL,
						PRIMARY KEY  (group_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfmembers table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFMEMBERS." (
						user_id bigint(20) NOT NULL default '0',
						display_name varchar(100) default NULL,
						pm smallint(1) NOT NULL default '0',
						moderator smallint(1) NOT NULL default '0',
						editor smallint(1) NOT NULL default '1',
						avatar varchar(50) default NULL,
						signature tinytext default NULL,
						sigimage tinytext default NULL,
						posts int(4) NOT NULL default '0',
						lastvisit datetime default NULL,
						subscribe longtext,
						buddies longtext,
						newposts longtext,
						checktime datetime default NULL,
						admin smallint(1) NOT NULL default '0',
						watches longtext,
						posts_rated longtext,
						admin_options longtext default NULL,
						PRIMARY KEY  (user_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfmemberships table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFMEMBERSHIPS." (
						membership_id mediumint(8) unsigned NOT NULL auto_increment,
						user_id mediumint(8) unsigned NOT NULL default '0',
						usergroup_id mediumint(8) unsigned NOT NULL default '0',
						PRIMARY KEY  (membership_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfmessages table def
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
						message_slug text,
						PRIMARY KEY  (message_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

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

				# sfnotice table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFNOTICE." (
						id varchar(30) NOT NULL,
						item varchar(15) default NULL,
						message longtext,
						ndate datetime NOT NULL,
						PRIMARY KEY  (id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfpermissions table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFPERMISSIONS." (
						permission_id mediumint(8) unsigned NOT NULL auto_increment,
						forum_id mediumint(8) unsigned NOT NULL default '0',
						usergroup_id mediumint(8) unsigned NOT NULL default '0',
						permission_role mediumint(8) unsigned NOT NULL default '0',
						PRIMARY KEY  (permission_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

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

				# sfposts table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFPOSTS." (
						post_id bigint(20) NOT NULL auto_increment,
						post_content text,
						post_date datetime NOT NULL,
						topic_id bigint(20) NOT NULL,
						user_id bigint(20) default NULL,
						forum_id bigint(20) NOT NULL,
						guest_name varchar(20) default NULL,
						guest_email varchar(50) default NULL,
						post_status int(4) NOT NULL default '0',
						post_pinned smallint(1) NOT NULL default '0',
						post_index mediumint(8) default '0',
						post_edit mediumtext,
						poster_ip varchar(15) NOT NULL,
						PRIMARY KEY  (post_id),
						KEY topicp_idx (topic_id),
						KEY forump_idx (forum_id),
						FULLTEXT KEY post_content (post_content)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfroles table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFROLES." (
						role_id mediumint(8) unsigned NOT NULL auto_increment,
						role_name varchar(50) NOT NULL default '',
						role_desc varchar(150) NOT NULL default '',
						role_actions longtext NOT NULL,
						PRIMARY KEY  (role_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfsettings table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFSETTINGS." (
						setting_id bigint(20) NOT NULL auto_increment,
						setting_name varchar(50) NOT NULL,
						setting_value longtext,
						setting_date datetime NOT NULL,
						PRIMARY KEY  (setting_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sftopics table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFTOPICS." (
						topic_id bigint(20) NOT NULL auto_increment,
						topic_name text NOT NULL,
						topic_date datetime NOT NULL,
						topic_status int(4) NOT NULL default '0',
						forum_id bigint(20) NOT NULL,
						user_id bigint(20) default NULL,
						topic_pinned smallint(1) NOT NULL default '0',
						topic_subs longtext,
						topic_sort varchar(4) default NULL,
						topic_opened bigint(20) NOT NULL default '0',
						blog_post_id bigint(20) NOT NULL default '0',
						topic_slug text NOT NULL,
						post_id bigint(20) default NULL,
						post_count mediumint(8) default '0',
						topic_status_flag bigint(20) default '0',
						topic_watches longtext,
						PRIMARY KEY  (topic_id),
						KEY forumt_idx (forum_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sftrack table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFTRACK." (
						id bigint(20) NOT NULL auto_increment,
						trackuserid bigint(20) default '0',
						trackname varchar(50) NOT NULL,
						trackdate datetime NOT NULL,
						PRIMARY KEY  (id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfusergroups table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFUSERGROUPS." (
						usergroup_id mediumint(8) unsigned NOT NULL auto_increment,
						usergroup_name text NOT NULL,
						usergroup_desc text default NULL,
						usergroup_is_moderator tinyint(4) unsigned NOT NULL default '0',
						PRIMARY KEY  (usergroup_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

				# sfwaiting table def
				$sql = "
					CREATE TABLE IF NOT EXISTS ".SFWAITING." (
						topic_id bigint(20) NOT NULL,
						forum_id bigint(20) NOT NULL,
						post_count int(4) NOT NULL,
						post_id bigint(20) NOT NULL default '0',
						user_id bigint(20) default '0',
						PRIMARY KEY  (topic_id)
					) ENGINE=MyISAM ".sf_charset().";";
				$wpdb->query($sql);

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

				echo __("Tables Created", "sforum").'</h5>';
				break;

		case 2:
				# CREATE DEFAULT DATA ----------------------------------

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
				$actions['Can watch topics'] = 0;
				$actions['Can change topic status'] = 0;
				$actions['Can rate posts'] = 0;
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
				$actions['Can watch topics'] = 0;
				$actions['Can change topic status'] = 0;
				$actions['Can rate posts'] = 0;
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
				$actions['Can watch topics'] = 1;
				$actions['Can change topic status'] = 0;
				$actions['Can rate posts'] = 1;
				$actions['Can moderate pending posts'] = 0;
				$role_name = 'Limited Access';
				$role_desc = 'Permission with access to reply and start topics but with limited features.';
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
				$actions['Can bypass post moderation'] = 1;
				$actions['Can bypass post moderation once'] = 1;
				$actions['Can upload images'] = 0;
				$actions['Can use signatures'] = 1;
				$actions['Can use images in signatures'] = 1;
				$actions['Can upload avatars'] = 1;
				$actions['Can use private messaging'] = 1;
				$actions['Can subscribe'] = 1;
				$actions['Can watch topics'] = 1;
				$actions['Can change topic status'] = 0;
				$actions['Can rate posts'] = 1;
				$actions['Can moderate pending posts'] = 0;
				$role_name = 'Standard Access';
				$role_desc = 'Permission with access to reply and start topics with advanced features such as signatures and private messaging.';
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
				$actions['Can watch topics'] = 1;
				$actions['Can change topic status'] = 1;
				$actions['Can rate posts'] = 1;
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
				$actions['Can watch topics'] = 1;
				$actions['Can change topic status'] = 1;
				$actions['Can rate posts'] = 1;
				$actions['Can moderate pending posts'] = 1;
				$role_name = 'Moderator Access';
				$role_desc = 'Permission with access to all Forum features.';
				sfa_create_role_row($role_name, $role_desc, serialize($actions));

				echo __("Permission Data Built", "sforum").'</h5>';
				break;

		case 3:
				# Create default 'Guest' user group data
				$guests = sfa_create_usergroup_row('Guests', 'Default Usergroup for guests of the forum.', '0', false);
				add_option('sfguestsgroup', $guests);

				# Create default 'Members' user group data
				$members = sfa_create_usergroup_row('Members', 'Default Usergroup for registered users of the forum.', '0', false);
				add_option('sfdefgroup', $members);

				# Create default 'Moderators' user group data
				$moderators = sfa_create_usergroup_row('Moderators', 'Default Usergroup for moderators of the forum.', '1', false);

				# ensure all users have a display name set
				sf_check_all_display_names();

				# set up the current userbase into default groups etc
				sf_setup_usergroup_data($members, $moderators, false, '');

				echo __("User Group Data Built", "sforum").'</h5>';
				break;

		case 4:
				# CREATE NEW PAGE FOR FORUM ----------------------------

				# Create the WP oage for forum
				$wpdb->query(
					"INSERT INTO ".$wpdb->prefix."posts (
					 post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status,
					 comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt,
					 post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count
					 ) VALUES (
					 ".$current_user->ID.", now(), now(), '', 'Forum', '', 'publish', 'closed', 'closed', '', 'forum', '', '', now(), now(), '', 0, '', 0, 'page', '', 0 )");

				# Grab the new page id
				$page_id = $wpdb->insert_id;

				# Update the guid for the new page
				$guid = get_permalink($page_id);
				$wpdb->query("UPDATE {$wpdb->prefix}posts SET guid='".$guid."' WHERE ID=".$page_id);

				add_option('sfpage', $page_id);

		echo __("Forum Page Created", "sforum").'</h5>';
		break;

		case 5:
				# CREATE OPTION RECORDS --------------------------------

				# Create Base Option Records (V1)
				add_option('sfslug', 'forum');
				add_option('sfpagedtopics', 12);
				add_option('sfuninstall', false);

				# (V1.2)
				add_option('sfsortdesc', false);

				# (V1.3)
				add_option('sfdates', get_option('date_format'));
				add_option('sftimes', get_option('time_format'));
				add_option('sfzone', 0);

				# (V1.4)
				add_option('sfshowavatars', true);
				add_option('sfuserabove', false);

				# (V1.6)
				add_option('sftopicsort', false);

				# (V1.7)
				add_option('sfavatarsize', 50);

				# (V1.8)
				add_option('sfpermalink', get_permalink(get_option('sfpage')));
				add_option('sfextprofile', true);
//				if(function_exists('site_url')) {
//					add_option('sfhome', site_url());
//				} else {
//					add_option('sfhome', get_option('home'));
//				}
				add_option('sfhome', SFHOME);

				# (V1.9)
				add_option('sfrsscount', 15);
				add_option('sfrsswords', 0);
				add_option('sfpagedposts', 20);
				add_option('sfgravatar', false);
				add_option('sfgmaxrating', 1);
				add_option('sfwpavatar', false);

				$fcols['topics']=true;
				$fcols['posts']=true;
				$fcols['last'] = false;
				add_option('sfforumcols', $fcols);

				$tcols['first']=true;
				$tcols['last']=true;
				$tcols['posts']=true;
				$tcols['views']=true;
				add_option('sftopiccols', $tcols);

				# (V2.0)
				add_option('sfstats', true);

				# (V2.1)
				add_option('sfsearchbar', true);
				add_option('sfshowhome', true);
				add_option('sflockdown', false);
				add_option('sflinkexcerpt', false);
				add_option('sflinkwords', 100);
				add_option('sflinkblogtext', '%ICON% Join the forum discussion on this post');
				add_option('sflinkforumtext', '%ICON% Read original blog post');
				add_option('sflinkabove', false);
				add_option('sfuseannounce', false);
				add_option('sfannouncecount', 8);
				add_option('sfannouncehead', 'Most Recent Forum Posts');
				add_option('sfannounceauto', false);
				add_option('sfannouncetime', 60);
				add_option('sfannouncetext', '%TOPICNAME% posted by %POSTER% in %FORUMNAME% on %DATETIME%');
				add_option('sfannouncelist', false);

				$rankdata['posts'] = 2;
				$rankdata['usergroup'] = 'none';
				$rankdata['image'] = 'none';
				sf_add_sfmeta('forum_rank', 'New Member', serialize($rankdata));
				$rankdata['posts'] = 1000;
				$rankdata['usergroup'] = 'none';
				$rankdata['image'] = 'none';
				sf_add_sfmeta('forum_rank', 'Member', serialize($rankdata));

				# (V3.0)
				add_option('sfimgenlarge', false);
				add_option('sfthumbsize', 100);
				add_option('sfdemocracy', false);
				add_option('sfuppath', '');
				add_option('sfbadwords', '');
				add_option('sfreplacementwords', '');
				add_option('sfpaging', 4);
				add_option('sfpostpaging', 4);
				add_option('sfeditormsg','');
				add_option('sfcheck', true);

				$sftitle = array();
				$sftitle['sfinclude'] = false;
				$sftitle['sfnotitle'] = false;
				$sftitle['sfbanner'] = '';
				add_option('sftitle', $sftitle);

				$pm = array();
				$pm['sfpmemail'] = false;
				$pm['sfpmmax'] = 0;
				add_option('sfpm', $pm);

				$sfmail = array();
				$sfmail['sfmailsender'] = get_bloginfo('name');

				# If WPMU then use the current users email address
				if(function_exists("wpmu_create_blog"))
				{
					if($current_user->email != '')
					{
						$comp=explode('@', $current_user->user_email);
						$sfmail['sfmailfrom'] = $comp[0];
						$sfmail['sfmaildomain'] = preg_replace('#^www\.#', '', strtolower($comp[1]));
					}
				} else {
					$sfmail['sfmailfrom'] = str_replace(' ', '', $current_user->user_login);
					$sfmail['sfmaildomain'] = preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
				}

				$sfmail['sfmailuse'] = true;
				add_option('sfmail', $sfmail);

				$sfmail = array();
				$sfmail['sfusespfreg'] = true;
				$sfmail['sfnewusersubject'] = 'Welcome to %BLOGNAME%';
				$sfmail['sfnewusertext'] = 'Welcome %USERNAME% to %BLOGNAME% %NEWLINE%Please find below your login details: %NEWLINE%Username: %USERNAME% %NEWLINE%Password: %PASSWORD% %NEWLINE%%LOGINURL%';
				add_option('sfnewusermail', $sfmail);

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

				$sfpostmsg = array();
				$sfpostmsg['sfpostmsgtext'] = '';
				$sfpostmsg['sfpostmsgtopic'] = false;
				$sfpostmsg['sfpostmsgpost'] = false;
				add_option('sfpostmsg', $sfpostmsg);

				# global
				$icons = 'Login;1@Register;1@Logout;1@Profile;1@Add a New Topic;1@Forum Locked;1@Reply to Post;1@Topic Locked;1@Quote and Reply;1@Edit Your Post;1@Return to Search Results;1@Subscribe;1@Forum RSS;1@Topic RSS;1@All RSS;1@Search;1@New Posts;1@Group RSS;1@Send PM;1@Return to forum;1@Compose PM;1@Go To Inbox;1@Go To Sentbox;1@Report Post;1@Moderation Queue;1@Lock this Topic;1@Pin this Topic;1@Create Linked Post;1@Pin this Post;1@Edit Timestamp;1@Subscribe to this Topic;1@Close New Post List;1@Review Watched Topics;1@End Topic Watch;1@Watch Topic;1@Members;1';
				add_option('sfshowicon', $icons);

				# (V3.1)
				$sfstyle = array();
				$sfstyle['sfskin'] = 'default';
				$sfstyle['sficon'] = 'default';
				$sfstyle['sfsize'] = '';
				add_option('sfstyle', $sfstyle);

				$sflogin = array();
				$sflogin['sfshowlogin'] = true;
				$sflogin['sfshowreg'] = true;
				$sflogin['sfregmath'] = true;
				$sflogin['sfinlogin'] = true;
				$sflogin['sfregtext'] = false;
				$sflogin['sfregcheck'] = false;
				$sflogin['sfloginskin'] = true;
				add_option('sflogin', $sflogin);

				$sfadminsettings=array();
				$sfadminsettings['sfmodasadmin']=false;
				$sfadminsettings['sfshowmodposts']=true;
				$sfadminsettings['sftools']=true;
				$sfadminsettings['sfqueue']=true;
				$sfadminsettings['sfbaronly']=false;
				$sfadminsettings['sfdashboardposts']=true;
				$sfadminsettings['sfdashboardstats']=true;
				add_option('sfadminsettings', $sfadminsettings);

				$sfauto=array();
				$sfauto['sfautoupdate']=false;
				$sfauto['sfautotime']=300;
				add_option('sfauto', $sfauto);

				$sffilters=array();
				$sffilters['sfnofollow']=false;
				$sffilters['sftarget']=true;
				add_option('sffilters', $sffilters);

				add_option('sfshowbreadcrumbs', true);

				# (V4.0)
				$sfeditor = array();
				$sfeditor['sfeditor'] = 1;
				$sfeditor['sfusereditor'] = false;
				$sfeditor['sfrejectformat'] = false;
				$sfeditor['sfrelative'] = true;
				$sfeditor['sftmcontentCSS'] = 'content.css';
				$sfeditor['sftmuiCSS'] = 'ui.css';
				$sfeditor['sftmdialogCSS'] = 'dialog.css';
				$sfeditor['SFhtmlCSS'] = 'htmlEditor.css';
				$sfeditor['SFbbCSS'] = 'bbcodeEditor.css';
				$sfeditor['sflang'] = 'en';
				$sfeditor['sfrtl'] = false;

				add_option('sfeditor', $sfeditor);

				$sfpostratings = array();
				$sfpostratings['sfpostratings'] = false;
				$sfpostratings['sfratingsstyle'] = 1;
				add_option('sfpostratings', $sfpostratings);

				$sfsmileys = array();
				$sfsmileys['sfsmallow'] = true;
				$sfsmileys['sfsmtype'] = 1;
				add_option('sfsmileys', $sfsmileys);

				$sfusersnewposts = array();
				$sfusersnewposts['sfshownewuser'] = true;
				$sfusersnewposts['sfshownewcount'] = 6;
				$sfusersnewposts['sfshownewabove'] = false;
				$sfusersnewposts['sfsortinforum'] = true;
				add_option('sfusersnewposts', $sfusersnewposts);

				add_option('sflinkcomments', false);
				add_option('sfshoweditdata', true);
				add_option('sfshoweditlast', false);
				add_option('sfavataruploads', true);
				add_option('sfprivatemessaging', true);
				add_option('sfsingleforum', false);
				add_option('sftaggedforum', '');

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
				add_option('sfshowmemberlist', true);
				add_option('sfcbexclusions', '');

				sf_build_tinymce_toolbar_arrays();

				# (V4.0.1)
				add_option('sfcheckformember', true);

				# (V4.0.3)
				add_option('sfblockadmin', false);

				echo __("Default Forum Options Created", "sforum").'</h5>';
				break;

		case 6:
				# CREATE MEMBERS TABLE ---------------------------
				sf_build_members_table('editor', 'install');

				echo __("Members Data Created", "sforum").'</h5>';
				break;

		case 7:
				# CREATE AVATAR FOLDER AND SMILEYS FOLDER IN WP-CONTENT -------------------

				$avatar = sf_relocate_avatars();
				if($avatar != 0)
				{
					$mess = __("INSTALL PROBLEM: Unable to Create Avatar Folder", "sforum").'</h5>';
					add_option('sfinstallav', $avatar);
				} else {
					$mess = __("Avatar Folder Created", "sforum").'</h5>';
				}

				echo $mess;
				break;

		case 8:
				$smiley = sf_relocate_smileys();
				if($smiley != 0)
				{
					$mess = __("INSTALL PROBLEM: Unable to Create Smiley Folder", "sforum").'</h5>';
					add_option('sfinstallsm', $smiley);
				} else {
					$mess=__("Smiley Folder Created", "sforum").'</h5>';
				}

				sf_build_base_smileys();

				echo $mess;
				break;

		case 9:
				# (V3.2)
				# grant spf capabilities to installer
				$user = new WP_User($current_user->ID);
				$user->add_cap('SPF Manage Options');
				$user->add_cap('SPF Manage Forums');
				$user->add_cap('SPF Manage User Groups');
				$user->add_cap('SPF Manage Permissions');
				$user->add_cap('SPF Manage Database');
				$user->add_cap('SPF Manage Components');
				$user->add_cap('SPF Manage Admins');
				$user->add_cap('SPF Manage Users');
				sf_update_member_item($current_user->ID, 'admin', 1);

				echo __("Admin Permission Data Built", "sforum").'</h5>';
				break;

		case 10:
			# UPDATE VERSION/BUILD NUMBERS -------------------------

			update_option('sfversion', SFVERSION);
			update_option('sfbuild', SFBUILD);

			delete_option("sfInstallID");

			# Lets update permalink and force a rewrite rules flush
			sfa_update_permalink();

			echo __("Version Number Updated", "sforum").'</h5>';
			break;

	}

	return;
}

?>