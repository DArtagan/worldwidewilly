<?php
/*
Simple:Press Forum
tinymce init
$LastChangedDate: 2009-04-08 13:20:58 +0100 (Wed, 08 Apr 2009) $
$Rev: 1697 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}
	if($sfglobals['editor']['sfrtl'] ? $SFDIR='rtl' : $SFDIR='ltr');

	echo '<script type="text/javascript" src="'.SFEDITORURL.'tinymce/tiny_mce.js"></script>'. "\n";
?>
	<script type="text/javascript">
	tinyMCE.init({
		mode : "exact",
		elements : "postitem",
		theme : "advanced",
		theme_advanced_layout_manager : "SimpleLayout",
		skin : "o2k7",
		content_css : "<?php echo(SFEDSTYLE . 'tinymce/'.$sfglobals["editor"]["sftmcontentCSS"]); ?>",
		popup_css : "<?php echo(SFEDSTYLE . 'tinymce/'.$sfglobals["editor"]["sftmdialogCSS"]); ?>",
		editor_css : "<?php echo(SFEDSTYLE . 'tinymce/'.$sfglobals["editor"]["sftmuiCSS"]); ?>",
		remove_trailing_nbsp : true,
		relative_urls : false,
		<?php if(!$sfglobals['editor']['sfrelative'])
		{ ?>
		convert_urls : false,
		<?php } ?>
		language : "<?php echo($sfglobals['editor']['sflang']); ?>",
		directionality : "<?php echo($SFDIR); ?>",
		auto_reset_designmode : true,
		width : "100%",
		height: "300",
		<?php if(($current_user->sfuploads == true) && (get_option('sfuppath') != NULL) && (!function_exists('wpmu_current_site')))
		{ ?>
		file_browser_callback : "UploadCallBack",
		<?php } ?>
		extended_valid_elements: "code",
		apply_source_formatting : true,
		entity_encoding : "named",
		force_p_newlines : true,
		force_br_newlines : false,
		paste_convert_middot_lists : true,
		paste_remove_spans : true,
		paste_remove_styles : true,
		paste_convert_headers_to_strong : true,
		paste_strip_class_attributes : "mso",
		invalid_elements: "script,applet,iframe,h1,h2,h3,h4,h5,h6,style,font",
		plugins : "<?php echo(sf_build_tb_plugins()); ?>",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location: "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resizing_use_cookie : false,
		theme_advanced_buttons1 : "<?php echo(sf_build_tb_buttons()); ?>",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons1_add : "<?php echo(sf_build_tb_buttons_add()); ?>"
	});
	<?php if(($current_user->sfuploads == true) && (get_option('sfuppath') != NULL) && (!function_exists('wpmu_current_site')))
	{ ?>
		function UploadCallBack(field_name, url, type, win) { 
		var connector = "<?php echo(SF_PLUGIN_URL); ?>/forum/uploader/sf-uploader.php?id=<?php echo($forumid); ?>";
		plw_field = field_name;
		plw_win = win;
		window.open(connector, "plw", "modal,width=320,height=500");
		}
	<?php } ?>
	</script>
<?php


# Toolbar Support Routines

function sf_build_tb_plugins()
{
	global $sfglobals;
	
	$tb = implode(",", $sfglobals['toolbar']['tbar_plugins']);
	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']!=2)
	{
		$tb = str_replace('emotions,', '', $tb);
		$tb = str_replace('emotions', '', $tb);
	}
	return $tb;
}

function sf_build_tb_buttons()
{
	global $sfglobals;
	
	return implode(",", $sfglobals['toolbar']['tbar_buttons']);
}

function sf_build_tb_buttons_add()
{
	global $sfglobals;
	
	$tb = implode(",", $sfglobals['toolbar']['tbar_buttons_add']);
	if($sfglobals['smileyoptions']['sfsmallow'] && $sfglobals['smileyoptions']['sfsmtype']!=2)
	{
		$tb = str_replace('emotions,', '', $tb);
		$tb = str_replace('emotions', '', $tb);
	}
	return $tb;
}

?>