<?php
/*
Simple:Press Forum
Admin Tools - Move Topic/Move Post
$LastChangedDate: 2009-03-09 16:57:06 +0000 (Mon, 09 Mar 2009) $
$Rev: 1549 $
*/

require_once("../../sf-config.php");

sf_load_foundation();
global $sfvars;

# get out of here if no action specified
if (empty($_GET['action'])) die();
$action = sf_syscheckstr($_GET['action']);

if ($action == 'mt')
{
	# move topic form
	sf_move_topic_popup();
}

if ($action == 'mp')
{
	# move post form
	sf_move_post_popup();
}

if ($action == 'ct')
{
	# change topic status form
	sf_reset_topic_status();
}

if ($action == 'ss')
{
	sf_save_new_status();
}

if ($action == 'props')
{
	sf_show_properties();
}

die();

function sf_move_topic_popup()
{
	global $current_user, $wpdb;

	$thistopic = sf_get_topic_record(sf_syscheckint($_GET['topicid']));
	$thisforum = sf_get_forum_record(sf_syscheckint($_GET['forumid']));
	sf_initialise_globals($thisforum->forum_id);
	$groups = sf_get_combined_groups_and_forums();

	if (!$current_user->sfmovetopics)
	{
		echo (__('Access Denied', "sforum"));
		die();
	}
?>
	<div id="sfpostform">
	<fieldset><legend><?php echo(sprintf(__("Select new Forum for Topic: &nbsp;&nbsp;&nbsp; %s", "sforum"), "<br />".stripslashes($thistopic->topic_name))); ?></legend>
		<form action="<?php echo(sf_build_url($thisforum->forum_slug, '', 1, 0)); ?>" method="post" name="movetopicform">
			<input type="hidden" name="currenttopicid" value="<?php echo($thistopic->topic_id); ?>">
			<input type="hidden" name="currentforumid" value="<?php echo($thisforum->forum_id); ?>">
			<br />
<?php
			if($groups)
			{
				sf_build_newforum_select($groups, $thisforum->forum_id);
			}
?>
			<input type="submit" class="sfcontrol" name="maketopicmove" value="<?php _e("Move Topic to Selected Forum", "sforum") ?>" />
			<input type="button" class="sfcontrol" name="cancel" value="<?php _e("Cancel", "sforum") ?>" onclick="hs.close(this);" />
		</form>
	</fieldset>
	<div>
<?php
	return;
}

function sf_move_post_popup()
{
	global $current_user, $wpdb;

	$thispost = sf_syscheckint($_GET['pid']);
	$thistopic = sf_get_topic_record(sf_syscheckint($_GET['id']));
	sf_initialise_globals($thistopic->forum_id);
	$thisforum = sf_get_forum_record($thistopic->forum_id);
	$groups = sf_get_combined_groups_and_forums();

	if (!$current_user->sfmoveposts)
	{
		echo (__('Access Denied', "sforum"));
		die();
	}
?>
	<div id="sfpostform">
	<fieldset><legend><?php echo(sprintf(__("Move Post (ID: %s) to New Topic/Forum", "sforum"), $thispost)); ?></legend>
		<form action="<?php echo(sf_build_url($thisforum->forum_slug, $thistopic->topic_slug, 1, 0)); ?>" method="post" name="movepostform">
			<input type="hidden" name="postid" value="<?php echo($thispost); ?>">
			<input type="hidden" name="oldtopicid" value="<?php echo($thistopic->topic_id); ?>">
			<input type="hidden" name="oldforumid" value="<?php echo($thisforum->forum_id); ?>">
			<br />
<?php
			if($groups)
			{
				sf_build_newforum_select($groups, 0);
			}
?>
			<p><?php _e("New Topic Name", "sforum"); ?></p>
			<input type="text" class="sfcontrol sfpostcontrol" size="80" name="newtopicname" value="" /><br /><br />

			<input type="submit" class="sfcontrol" name="makepostmove" value="<?php _e("Move Post", "sforum") ?>" />
			<input type="button" class="sfcontrol" name="cancel" value="<?php _e("Cancel", "sforum") ?>" onclick="hs.close(this);" />
		</form>
	</fieldset>
	<div>
<?php
	return;
}

function sf_build_newforum_select($groups, $forumid)
{
	echo '<p>'.__("Select Forum", "sforum").'</p>';
	echo '<select class="sfquicklinks sfcontrol" name="forumid">'."\n";

	foreach($groups as $group)
	{
		$name = stripslashes($group['group_name']);
		if(strlen($name) > 30) $name = substr($name, 0, 30).'...';

		echo '<optgroup class="sflist" label="&nbsp;&nbsp;'.sf_create_name_extract($group['group_name']).'">'."\n";
		if($group['forums'])
		{
			foreach($group['forums'] as $forum)
			{
				if($forum['forum_id'] != $forumid)
				{
					$name = stripslashes($forum['forum_name']);
					if(strlen($name) > 35) $name = substr($name, 0, 35).'...';
					echo '<option value="'.$forum['forum_id'].'">&nbsp;&nbsp;&nbsp;&nbsp;'.sf_create_name_extract($forum['forum_name']).'</option>'."\n";
				}
			}
		}
		echo '</optgroup>';
	}
	echo '</select><br /><br />'."\n";
	return;
}

function sf_reset_topic_status()
{
	global $current_user, $wpdb;

	$thistopic = sf_get_topic_record(sf_syscheckint($_GET['id']));
	sf_initialise_globals($thistopic->forum_id);
	$thisforum = sf_get_forum_record($thistopic->forum_id);
	$statusset = sf_syscheckint($_GET['set']);
	$statusflag = sf_syscheckint($_GET['flag']);
	$returnpage = sf_syscheckint($_GET['returnpage']);

?>
	<div id="sfpostform">
	<fieldset><legend><?php echo(__("Change Topic Status", "sforum")); ?></legend>
		<form action="<?php echo(sf_build_url($thisforum->forum_slug, '', $returnpage, 0)); ?>" method="post" name="changetopicstatus">
			<input type="hidden" name="id" value="<?php echo($thistopic->topic_id); ?>">
			<br />
			<?php echo sf_topic_status_select($statusset, $statusflag); ?>

			<input type="submit" class="sfcontrol" name="makestatuschange" value="<?php _e("Save Status", "sforum") ?>" />
			<input type="button" class="sfcontrol" name="cancel" value="<?php _e("Cancel", "sforum") ?>" onclick="hs.close(this);" />
		</form>
	</fieldset>
	<div>
<?php
	return;
}

function sf_save_new_status()
{
	$topicid= sf_syscheckint($_GET['id']);
	$statvalue = sf_syscheckint($_GET['newvalue']);

	sf_update_topic_status_flag($statvalue, $topicid);

	echo('<small>'.sf_syscheckstr($_GET['newtext']).'</small>');
	return;
}

function sf_show_properties()
{
	global $wpdb;

	$thisforum = sf_get_forum_record(sf_syscheckint($_GET['forum']));
	$thistopic = sf_get_topic_record(sf_syscheckint($_GET['topic']));

	if(isset($_GET['post']))
	{
		$thisgroup = sf_get_group_record(sf_syscheckint($thisforum->group_id));
	} else {
		$thisgroup = sf_get_group_record(sf_syscheckint($_GET['group']));
	}

	$posts = $wpdb->get_col("SELECT post_id FROM ".SFPOSTS." WHERE topic_id=".$thistopic->topic_id." ORDER BY post_id");
	If($posts)
	{
		$first = $posts[0];
		$last  = $posts[count($posts)-1];
	}

?>
	<table border="1" cellspacing="0" cellpadding="5">
		<tr><td><?php _e("Group ID", "sforum"); ?></td><td colspan="2"><?php echo($thisgroup->group_id); ?></td></tr>
		<tr><td><?php _e("Group Title", "sforum"); ?></td><td colspan="2"><?php echo(stripslashes($thisgroup->group_name)); ?></td></tr>
		<tr><td><?php _e("Forum ID", "sforum"); ?></td><td><?php echo($thisforum->forum_id); ?></td><td><?php echo sf_rebuild_forum_form($thisforum->forum_id, $thistopic->topic_id, $thisforum->forum_slug, $thistopic->topic_slug); ?></td></tr>
		<tr><td><?php _e("Forum Title", "sforum"); ?></td><td colspan="2"><?php echo(stripslashes($thisforum->forum_name)); ?></td></tr>
		<tr><td><?php _e("Forum Slug", "sforum"); ?></td><td colspan="2"><?php echo(stripslashes($thisforum->forum_slug)); ?></td></tr>
		<tr><td><?php _e("Topics in Forum", "sforum"); ?></td><td colspan="2"><?php echo($thisforum->topic_count); ?></td></tr>
		<tr><td><?php _e("Topic ID", "sforum"); ?></td><td><?php echo($thistopic->topic_id); ?></td><td><?php echo sf_rebuild_topic_form($thisforum->forum_id, $thistopic->topic_id, $thisforum->forum_slug, $thistopic->topic_slug); ?></td></tr>
		<tr><td><?php _e("Topic Title", "sforum"); ?></td><td colspan="2"><?php echo(stripslashes($thistopic->topic_name)); ?></td></tr>
		<tr><td><?php _e("Topic Slug", "sforum"); ?></td><td colspan="2"><?php echo(stripslashes($thistopic->topic_slug)); ?></td></tr>
		<tr><td><?php _e("Posts in Topic", "sforum"); ?></td><td colspan="2"><?php echo($thistopic->post_count); ?></td></tr>
		<tr><td><?php _e("Topic Started", "sforum"); ?></td><td colspan="2"><?php echo(mysql2date(SFDATES, $thistopic->topic_date)); ?></td></tr>
		<tr><td><?php _e("First Post ID", "sforum"); ?></td><td colspan="2"><?php echo($first); ?></td></tr>
		<tr><td><?php _e("Last Post ID", "sforum"); ?></td><td colspan="2"><?php echo($last); ?></td></tr>
<?php
		if(isset($_GET['post']))
		{
			$postid = sf_syscheckint($_GET['post']);
			$ip = $wpdb->get_var("SELECT poster_ip FROM ".SFPOSTS." WHERE post_id=".$postid);
?>
			<tr><td><?php _e("This Post ID", "sforum"); ?></td><td colspan="2"><?php echo($postid); ?></td></tr>
			<tr><td><?php _e("Poster IP", "sforum"); ?></td><td colspan="2"><?php echo($ip); ?></td></tr>
<?php
		}
?>
	</table>
<?php
	return;
}

function sf_rebuild_forum_form($forumid, $topicid, $forumslug, $topicslug)
{
	$out = '<form action="'.sf_build_url($forumslug, $topicslug, 1, 0).'" method="post" name="forumrebuild">'."\n";
	$out.= '<input type="hidden" name="forumid" value="'.$forumid.'" />'."\n";
	$out.= '<input type="hidden" name="topicid" value="'.$topicid.'" />'."\n";
	$out.= '<input type="hidden" name="forumslug" value="'.$forumslug.'" />'."\n";
	$out.= '<input type="hidden" name="topicslug" value="'.$topicslug.'" />'."\n";
	$out.= '<input type="submit" class="sfxcontrol" name="rebuildforum" value="'.__("Verify", "sforum").'" />';
	$out.= '</form>'."\n";

	return $out;
}

function sf_rebuild_topic_form($forumid, $topicid, $forumslug, $topicslug)
{
	$out = '<form action="'.sf_build_url($forumslug, $topicslug, 1, 0).'" method="post" name="topicrebuild">'."\n";
	$out.= '<input type="hidden" name="forumid" value="'.$forumid.'" />'."\n";
	$out.= '<input type="hidden" name="topicid" value="'.$topicid.'" />'."\n";
	$out.= '<input type="hidden" name="forumslug" value="'.$forumslug.'" />'."\n";
	$out.= '<input type="hidden" name="topicslug" value="'.$topicslug.'" />'."\n";
	$out.= '<input type="submit" class="sfxcontrol" name="rebuildtopic" value="'.__("Verify", "sforum").'" />';
	$out.= '</form>'."\n";

	return $out;
}

?>