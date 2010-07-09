-- Simple:Press Forum Version 4.0 Database Script
-- --------------------------------------------------------

-- ========================================================
-- NOTE: This script is set to use the default prefix wp_
-- ========================================================

--
-- Table structure for table `wp_sfdefpermissions`
--

DROP TABLE IF EXISTS `wp_sfdefpermissions`;
CREATE TABLE `wp_sfdefpermissions` (
  `permission_id` mediumint(8) unsigned NOT NULL auto_increment,
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `usergroup_id` mediumint(8) unsigned NOT NULL default '0',
  `permission_role` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`permission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfforums`
--

DROP TABLE IF EXISTS `wp_sfforums`;
CREATE TABLE `wp_sfforums` (
  `forum_id` bigint(20) NOT NULL auto_increment,
  `forum_name` text NOT NULL,
  `group_id` bigint(20) NOT NULL,
  `forum_seq` int(4) default NULL,
  `forum_desc` text,
  `forum_status` int(4) NOT NULL default '0',
  `forum_slug` text NOT NULL,
  `forum_rss` text,
  `forum_icon` varchar(25) default NULL,
  `post_id` bigint(20) default NULL,
  `topic_count` mediumint(8) default '0',
  `forum_rss_private` smallint(1) NOT NULL default '0',
  `topic_status_set` bigint(20) default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `groupf_idx` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfgroups`
--

DROP TABLE IF EXISTS `wp_sfgroups`;
CREATE TABLE `wp_sfgroups` (
  `group_id` bigint(20) NOT NULL auto_increment,
  `group_name` text NOT NULL,
  `group_seq` int(4) default NULL,
  `group_desc` text,
  `group_rss` text,
  `group_icon` varchar(25) default NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfmembers`
--

DROP TABLE IF EXISTS `wp_sfmembers`;
CREATE TABLE `wp_sfmembers` (
  `user_id` bigint(20) NOT NULL default '0',
  `display_name` varchar(100) default NULL,
  `pm` smallint(1) NOT NULL default '0',
  `moderator` smallint(1) NOT NULL default '0',
  `avatar` varchar(50) default NULL,
  `signature` tinytext,
  `sigimage` tinytext,
  `editor` smallint(1) NOT NULL default '1',
  `posts` int(4) NOT NULL default '0',
  `lastvisit` datetime default NULL,
  `subscribe` longtext,
  `buddies` longtext,
  `newposts` longtext,
  `checktime` datetime default NULL,
  `admin` smallint(1) NOT NULL default '0',
  `watches` longtext,
  `posts_rated` longtext,
  `admin_options` longtext,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfmemberships`
--

DROP TABLE IF EXISTS `wp_sfmemberships`;
CREATE TABLE `wp_sfmemberships` (
  `membership_id` mediumint(8) unsigned NOT NULL auto_increment,
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `usergroup_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`membership_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfmessages`
--

DROP TABLE IF EXISTS `wp_sfmessages`;
CREATE TABLE `wp_sfmessages` (
  `message_id` bigint(20) NOT NULL auto_increment,
  `sent_date` datetime NOT NULL,
  `from_id` bigint(20) default NULL,
  `to_id` bigint(20) default NULL,
  `title` text,
  `message` text,
  `message_status` smallint(1) NOT NULL default '0',
  `inbox` smallint(1) NOT NULL default '1',
  `sentbox` smallint(1) NOT NULL default '1',
  `is_reply` smallint(1) NOT NULL default '0',
  `message_slug` text NOT NULL,
  PRIMARY KEY  (`message_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfmeta`
--

DROP TABLE IF EXISTS `wp_sfmeta`;
CREATE TABLE `wp_sfmeta` (
  `meta_id` bigint(20) NOT NULL auto_increment,
  `meta_type` varchar(20) NOT NULL,
  `meta_key` varchar(100) default NULL,
  `meta_value` longtext,
  PRIMARY KEY  (`meta_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfnotice`
--

DROP TABLE IF EXISTS `wp_sfnotice`;
CREATE TABLE `wp_sfnotice` (
  `id` varchar(30) NOT NULL,
  `item` varchar(15) default NULL,
  `message` longtext,
  `ndate` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfpermissions`
--

DROP TABLE IF EXISTS `wp_sfpermissions`;
CREATE TABLE `wp_sfpermissions` (
  `permission_id` mediumint(8) unsigned NOT NULL auto_increment,
  `forum_id` mediumint(8) unsigned NOT NULL default '0',
  `usergroup_id` mediumint(8) unsigned NOT NULL default '0',
  `permission_role` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`permission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfpostratings`
--

DROP TABLE IF EXISTS `wp_sfpostratings`;
CREATE TABLE `wp_sfpostratings` (
  `rating_id` bigint(20) NOT NULL auto_increment,
  `post_id` bigint(20) NOT NULL,
  `vote_count` bigint(20) NOT NULL,
  `ratings_sum` bigint(20) NOT NULL,
  `ips` longtext,
  `members` longtext,
  PRIMARY KEY  (`rating_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfposts`
--

DROP TABLE IF EXISTS `wp_sfposts`;
CREATE TABLE `wp_sfposts` (
  `post_id` bigint(20) NOT NULL auto_increment,
  `post_content` text,
  `post_date` datetime NOT NULL,
  `topic_id` bigint(20) NOT NULL,
  `user_id` bigint(20) default NULL,
  `forum_id` bigint(20) NOT NULL,
  `guest_name` varchar(20) default NULL,
  `guest_email` varchar(50) default NULL,
  `post_status` int(4) NOT NULL default '0',
  `post_pinned` smallint(1) NOT NULL default '0',
  `post_index` mediumint(8) default '0',
  `post_edit` mediumtext,
  `poster_ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`post_id`),
  KEY `topicp_idx` (`topic_id`),
  KEY `forump_idx` (`forum_id`),
  FULLTEXT KEY `post_content` (`post_content`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfroles`
--

DROP TABLE IF EXISTS `wp_sfroles`;
CREATE TABLE `wp_sfroles` (
  `role_id` mediumint(8) unsigned NOT NULL auto_increment,
  `role_name` varchar(50) NOT NULL default '',
  `role_desc` varchar(150) NOT NULL default '',
  `role_actions` longtext NOT NULL,
  PRIMARY KEY  (`role_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfsettings`
--

DROP TABLE IF EXISTS `wp_sfsettings`;
CREATE TABLE `wp_sfsettings` (
  `setting_id` bigint(20) NOT NULL auto_increment,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` longtext,
  `setting_date` datetime NOT NULL,
  PRIMARY KEY  (`setting_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sftopics`
--

DROP TABLE IF EXISTS `wp_sftopics`;
CREATE TABLE `wp_sftopics` (
  `topic_id` bigint(20) NOT NULL auto_increment,
  `topic_name` text NOT NULL,
  `topic_date` datetime NOT NULL,
  `topic_status` int(4) NOT NULL default '0',
  `forum_id` bigint(20) NOT NULL,
  `user_id` bigint(20) default NULL,
  `topic_pinned` smallint(1) NOT NULL default '0',
  `topic_subs` longtext,
  `topic_sort` varchar(4) default NULL,
  `topic_opened` bigint(20) NOT NULL default '0',
  `blog_post_id` bigint(20) NOT NULL default '0',
  `topic_slug` text NOT NULL,
  `post_id` bigint(20) default NULL,
  `post_count` mediumint(8) default '0',
  `topic_status_flag` bigint(20) default '0',
  `topic_watches` longtext,
  PRIMARY KEY  (`topic_id`),
  KEY `forumt_idx` (`forum_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sftrack`
--

DROP TABLE IF EXISTS `wp_sftrack`;
CREATE TABLE `wp_sftrack` (
  `id` bigint(20) NOT NULL auto_increment,
  `trackuserid` bigint(20) default '0',
  `trackname` varchar(50) NOT NULL,
  `trackdate` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfusergroups`
--

DROP TABLE IF EXISTS `wp_sfusergroups`;
CREATE TABLE `wp_sfusergroups` (
  `usergroup_id` mediumint(8) unsigned NOT NULL auto_increment,
  `usergroup_name` text NOT NULL,
  `usergroup_desc` text,
  `usergroup_is_moderator` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`usergroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sfwaiting`
--

DROP TABLE IF EXISTS `wp_sfwaiting`;
CREATE TABLE `wp_sfwaiting` (
  `topic_id` bigint(20) NOT NULL,
  `forum_id` bigint(20) NOT NULL,
  `post_count` int(4) NOT NULL,
  `post_id` bigint(20) NOT NULL default '0',
  `user_id` bigint(20) default '0',
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
