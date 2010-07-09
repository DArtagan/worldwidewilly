<?php
/*
Simple:Press Forum
wp-common.php - common/shared routines between back and front ends
$LastChangedDate: 2009-02-19 19:41:15 +0000 (Thu, 19 Feb 2009) $
$Rev: 1426 $
*/

# ------------------------------------------------------------------
# sfa_update_permalink()
#
# Updates the forum permalink. Called from plugin activation and
# upon each display of a forum admin page. If the permalink is 
# found to have changed the rewrite rules are also flushed
# ------------------------------------------------------------------
function sfa_update_permalink()
{
	global $wpdb, $wp_rewrite;

	$slug = get_option('sfslug');
	
	if($slug)
	{
		$sfperm = get_option('sfpermalink');	
		
		$pageid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$slug."'");
		if ($pageid)
		{
			update_option('sfpage', $pageid);
			$perm = get_permalink($pageid);
			if(get_option('page_on_front') == $pageid && get_option('show_on_front') == 'page')
			{
				$perm.= '/'.$slug;
			}
			# only update it if base permalink has been changed
			if($sfperm != $perm)
			{
				update_option('sfpermalink', $perm);
			}
		}
	}
	$wp_rewrite->flush_rules();

	return;
}

?>