<?php
/*
Simple:Press Forum
HTMl init
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	echo (__('Access Denied', "sforum"));
	die();
}
	echo '<link rel="stylesheet" type="text/css" href="'.SFEDSTYLE.'html/'.$sfglobals["editor"]["SFhtmlCSS"].'" />'."\n";
	echo '<script type="text/javascript" src="'.SFEDITORURL.'html/htmlEditor.js"></script>'. "\n";
?>
	<script type='text/javascript'>
/* <![CDATA[ */
	quicktagsL10n = {
		quickLinks: "<?php _e("Quick Links", "sforum"); ?>",
		closeAllOpenTags: "<?php _e("Close all open tags", "sforum"); ?>",
		closeTags: "<?php _e("close tags", "sforum"); ?>",
		enterURL: "<?php _e("Enter the URL", "sforum"); ?>",
		enterImageURL: "<?php _e("Enter the URL of the image", "sforum"); ?>",
		enterImageDescription: "<?php _e("Enter a description of the image", "sforum"); ?>"
	}
	/* ]]> */
	</script>
<?php
?>