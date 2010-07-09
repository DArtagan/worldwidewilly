<?php
/*
Simple:Press Forum
AHAH routine displaying member subscriptions and watches
$LastChangedDate: 2009-01-14 02:05:53 +0000 (Wed, 14 Jan 2009) $
$Rev: 1199 $
*/

require_once("../../sf-config.php");
require_once("../sf-adminsupport.php");
require_once('../../forum/sf-primitives.php');

define('SFADMINIMAGES', SF_PLUGIN_URL . '/admin/images/');

# Check Whether User Can Manage Forums
if(!sf_current_user_can('SPF Manage Forums')) {
	echo (__('Access Denied', "sforum"));
	die();
}

$error = "";

if(isset($_GET['action']) && sf_syscheckstr($_GET['action']) == 'swlist')
{
	
	if(isset($_GET['groups']) && sf_syscheckstr($_GET['groups']) == 'error')
	{
		$error="Group";
	}
	if(isset($_GET['forums']) && sf_syscheckstr($_GET['forums']) == 'error')
	{
		$error="Forums";
	}
	if($error)
	{
		echo sprintf(__("You elected to filter by %s but selected no %s items", "sforum"), $error, $error);
		die();
	}

	sfa_render_subswatches();
	die();
} else {
	die();
}

function sfa_render_subswatches()
{
    $subs = sf_syscheckstr($_GET['showsubs']);
    $watches = sf_syscheckstr($_GET['showwatches']);
    $filter = sf_syscheckstr($_GET['filter']);
    if (isset($_GET['groups']))
    {
    	$groups = explode('-', sf_syscheckstr($_GET['groups']));
    } else {
		$groups[0] = -1;
	}
    if (isset($_GET['forums']))
    {
    	$forums = explode('-', sf_syscheckstr($_GET['forums']));
    } else {
		$forums[0] = -1;
	}
	$records = sfa_get_watches_subs($subs, $watches, $filter, $groups, $forums);

	if ($subs || $watches)
	{
		echo '<table class="sfsubtable" cellpadding="0" cellspacing="0">';
		if ($records)
		{
			echo '<tr>';
			echo '<th align="left" width="175"><small>'.__("Group", "sforum").'</small></th>';
			echo '<th align="left" width="175"><small>'.__("Forum", "sforum").'</small></th>';
			echo '<th align="left" width="175"><small>'.__("Topic", "sforum").'</small></th>';
			echo '<th align="left"><small>'.__("Watches/Subscriptions", "sforum").'</small></th>';
			echo '<th align="center" width="60"><small>'.__("Manage", "sforum").'</small></th>';
			echo '</tr>';
			foreach ($records as $index => $record)
			{
				echo '<tr>';
				echo '<td colspan="5" style="border-bottom:0px;padding:0;">';
				echo '<div id="subswatches'.$index.'">';
				echo '<table width="100%" cellspacing="0">';
				echo '<tr>';
				echo '<td width="175" style="padding:4px 0 4px 5px;">'.stripslashes($record->group_name).'</td>';
				echo '<td width="175" style="padding:4px 0 4px 5px;">'.stripslashes($record->forum_name).'</td>';
				$url = sf_build_url($record->forum_slug, $record->topic_slug, 1, 0);
				echo '<td width="175" style="padding:4px 0 4px 5px;"><a href="'.$url.'">'.stripslashes($record->topic_name).'</a></td>';
				echo '<td style="padding:4px 0 4px 5px;">';
				$have_subs = 0;
				if ($subs) # subs
				{
					if ($record->topic_subs)
					{
						$have_subs = 1;
						$first = true;
						$list = explode('@', $record->topic_subs);
						for ($x=0; $x<count($list); $x++)
						{
							$user = sf_get_member_row($list[$x]);
							if ($first)
							{
								echo __("Subscriptions", "sforum").":<br />";
								echo $user['display_name'];
								$first = false;
							} else {
								echo ', '.stripslashes(attribute_escape($user['display_name']));
							}
						}
						if ($record->topic_watches) echo '<br /><br />';
					}
				}
				$have_watches = 0;
				if ($watches) # watches
				{
					if ($record->topic_watches)
					{
						$have_watches = 1;
						$first = true;
						$list = explode('@', $record->topic_watches);
						for ($x=0; $x<count($list); $x++)
						{
							$user = sf_get_member_row($list[$x]);
							if ($first)
							{
								echo __("Watches", "sforum").":<br />";
								echo $user['display_name'];
								$first = false;
							} else {
								echo ', '.stripslashes(attribute_escape($user['display_name']));
							}
						}
					}
				}
				echo '</td>';
				echo '<td width="30" align="center" style="padding:4px 0 4px 5px;">';
				$gif = SFADMINURL."images/working.gif";
				if ($subs && $record->topic_subs)
				{
					$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_subs&id=".$record->topic_id."&watches=".$have_watches."&subs=0&group=".$record->group_name."&forum=".$record->forum_name."&topic=".$record->topic_name."&slug=".$record->topic_slug."&eid=".$index;
					if ($have_watches) $fade = 0; else $fade = 1;
					?>
					<img onclick="sfjDelWatchesSubs('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $fade; ?>', 'subswatches<?php echo $index; ?>');" src="<?php echo SFADMINIMAGES; ?>del_sub.png" title="<?php _e("Delete Subscriptions", "sforum"); ?>"/>&nbsp;
					<?php
				}
				echo '</td>';
				echo '<td width="30" align="center" style="padding:4px 0 4px 5px;">';
				if ($watches && $record->topic_watches)
				{
					if ($have_subs) $fade = 0; else $fade = 1;
					$site = SF_PLUGIN_URL."/admin/ahah/sf-ahahadminuser.php?action=del_watches&id=".$record->topic_id."&subs=".$have_subs."&watches=0&group=".$record->group_name."&forum=".$record->forum_name."&topic=".$record->topic_name."&slug=".$record->topic_slug."&eid=".$index;
					?>
					<img onclick="sfjDelWatchesSubs('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $fade; ?>', 'subswatches<?php echo $index; ?>');" src="<?php echo SFADMINIMAGES; ?>del_watch.png" title="<?php _e("Delete Watches", "sforum"); ?>"/>&nbsp;
					<?php
				}
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				echo '</div>';
				echo '</td>';
				echo '</tr>';
			}	
		} else {
			echo '<tr>';
			echo '<td>';
			echo __("No Watches or Subscriptions Found!", "sforum");
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo '<tr>';
		echo '<td>';
		echo __("You must select Show Subscriptions and/or Show Watches to get results!", "sforum");
		echo '</td>';
		echo '</tr>';			
	}
	return;
}

function sfa_get_watches_subs($subs, $watches, $filter, $groups, $forums)
{
	global $wpdb;

	# create the where based on watches and/or subscriptions
	if (($subs==1) && ($watches==0))  # subscriptions only
	{
		$where2 = ' AND topic_subs != ""';
	} else if (($subs==0) && ($watches==1)) # watches only
	{
		$where2 = ' AND topic_watches != ""';		
	} else if (($subs==1) && ($watches==1)) # both subscriptions and watches
	{ 
		$where2 = ' AND (topic_subs != "" OR topic_watches != "")';		
	} else # neither selected return empty result
	{
		return '';
	}

	# create the join based on all, group or forum filter
	if ($filter == 'groups' && $groups[0] != -1) # filter by groups
	{
		$where1 = " WHERE ".SFGROUPS.".group_id IN (".implode(",", $groups).")";
	} else if ($filter == 'forums' && $forums[0] != -1) # filter by forums
	{ 
		$where1 = " WHERE ".SFFORUMS.".forum_id IN (".implode(",", $forums).")";
	} else { # all groups/forums
		$where1 = " WHERE 1";		
	}
			
	# retrieve watched topic records
	$query = "SELECT topic_id, topic_name, topic_slug, group_name, forum_name, forum_slug, topic_subs, topic_watches 
			 FROM ".SFTOPICS."
			 LEFT JOIN ".SFFORUMS." ON ".SFFORUMS.".forum_id = ".SFTOPICS.".forum_id  
			 LEFT JOIN ".SFGROUPS." ON ".SFGROUPS.".group_id = ".SFFORUMS.".group_id ".
			 $where1.$where2;
	$records = $wpdb->get_results($query);
	
	return $records;
}

?>