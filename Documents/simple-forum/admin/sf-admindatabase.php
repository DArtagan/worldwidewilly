<?php
/*
Simple:Press Forum
Admin Database
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	echo (__('Access Denied', "sforum"));
	die();
}

# Check Whether User Can Manage Database
if(!sf_current_user_can('SPF Manage Database')) {
	echo (__('Access Denied', "sforum"));
	die();
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-admindatabase.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');
define('SFSUPPORT',   SF_PLUGIN_DIR . '/forum/sf-support.php');

include_once ('sf-admindatabaseforms.php');
include_once ('sf-adminsupport.php');
include_once ('sf-admin.php');
include_once (SFSUPPORT);

# make sure we dont need to perform an upgrade
if ( sfa_get_system_status() != 'ok' )
{
    include_once(SFLOADER);
    die();
}

# = ADMIN DISTRUBUTION ========================

sfa_header(__('SPF Manage Database', 'sforum'), 'icon-database');

# filter topics to prune
if (isset($_POST['dbfiltertopics']))
    sfa_filter_topics();

# prune the selected topics
if (isset($_POST['dbprunetopics']))
    sfa_prune_topics();

sfa_databasepage();
sfa_footer();

# = ADMIN PANELS DISTRIBUTION==================

function sfa_databasepage()
{
    sfa_render_database_index();

    return;
}

# function to create an sql query for a list of topics based on the filter criteria
# these topics then get displayed in another form for the admin to mark the topics for pruning
function sfa_filter_topics()  {

    check_admin_referer('forum-adminform_filtertopics', 'forum-adminform_filtertopics');

    $topicdata = array();

	$gcount = $_POST['gcount'];
	$fcount = $_POST['fcount'];

	$first = true;
	for( $x=0; $x<$gcount; $x++) {
		for( $y=0; $y<$fcount[$x]; $y++) {
			if (isset($_POST['group'.$x.'forum'.$y])) {
				if ($first) {
					$forum_ids = ' AND (forum_id='.$_POST['group'.$x.'forum'.$y];
					$first = false;
				} else {
					$forum_ids .= ' OR forum_id='.$_POST['group'.$x.'forum'.$y];
				}
			}
		}
	}
	if ($first) {
        sfa_message(__("No Forum(s) Specified!", "sforum"));

		return;
	} else {
		$forum_ids .= ')';
	}
	$topicdata['id'] = $forum_ids;

	$date = getdate(strtotime($_POST['date']));
	$filterdate = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' 23:59:59';
	$topicdata['date'] = $filterdate;

	sfa_database_prune_form($topicdata);

	return;
}

# function to delete the topics that were selected from the filtered list of topics
function sfa_prune_topics()
{
	global $wpdb, $current_user;

    check_admin_referer('forum-adminform_prunetopics', 'forum-adminform_prunetopics');

	# current user extensions not loaded for admin functions so give self topic delete rights
	$current_user->sfdelete = 1;

	# loop through all of the filtered topics to see which ones we want to delete
	$tcount = $_POST['tcount'];
	for( $x=0; $x<$tcount; $x++) {
		if (isset($_POST['topic'.$x])) {
			# call core function to remove topics/posts/subscriptions etc
			include_once(SF_PLUGIN_DIR.'/forum/sf-links.php');
			sf_delete_topic(sf_syscheckint($_POST['topic'.$x]), false);
		}
	}

    sfa_message(__("Database Pruned!", "sforum"));

    return;
}

?>