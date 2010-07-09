<?php
/*
Simple:Press Forum
Admin Panels - Online Help
$LastChangedDate: 2009-01-15 13:03:20 +0000 (Thu, 15 Jan 2009) $
$Rev: 1209 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

define('SFADMINPATH', 'admin.php?page=simple-forum/admin/sf-adminoptions.php');
define('SFLOADER',    SF_PLUGIN_DIR . '/sf-loader.php');

include_once('sf-adminsupport.php');
include_once('sf-admin.php');

if(sfa_get_system_status() != 'ok')
{
	include_once(SFLOADER);
	die();
}

sfa_header(__('SPF Online Help', 'sforum'), 'icon-help');

?>
<div class="wrap sfatag">

	<div class="sfmaincontainer">

		<table class="sfhelptable" width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>		
				<td>
					<!-- HOW TO -->
					<?php sfa_index_help("howto-index"); ?>
				</td>
			</tr>
		</table>
		
		<table class="sfhelptable" width="100%" border="0" cellpadding="10" cellspacing="0">
			<tr>		
				<td width="33%" valign="top">
					<!-- INSTALLATION -->
					<?php sfa_index_help("installation-index"); ?>
					<!-- THEMES -->
					<?php sfa_index_help("themes-index"); ?>
					<!-- ADMIN TOOLS -->
					<?php sfa_index_help("admin-tools-index"); ?>
					<!-- NEW POSTS -->
					<?php sfa_index_help("new-posts-index"); ?>
					<!-- SLUGS -->
					<?php sfa_index_help("slugs-index"); ?>
				</td>			
				
				<td width="33%" valign="top">
					<!-- CUSTOM -->
					<?php sfa_index_help("custom-index"); ?>
					<!-- PROGRAM HOOKS -->
					<?php sfa_index_help("program-hooks-index"); ?>
					<!-- PLUGGABLE FUNCTIONS -->
					<?php sfa_index_help("pluggable-index"); ?>
				</td>
				
				<td width="33%" valign="top">
					<!-- FILTERS -->
					<?php sfa_index_help("filters-index"); ?>
					<!-- TEMPLATE TAGS -->
					<?php sfa_index_help("template-tags-index"); ?>
					<!-- LICENSE -->
					<?php sfa_index_help("license-index"); ?>				
				</td>
			</tr>
		</table>

	</div>
	
</div>	


<?php

sfa_footer();

# SUPPORT ROUTINES =====================

function sfa_index_help($index)
{
	$path = SF_PLUGIN_DIR . '/admin/help/';
	$lang = WPLANG;
	if(empty($lang)) $lang = 'en';

	$indexfile = $path . $index . '-' . $lang . '.php';

	if(file_exists($indexfile) == false)
	{
		$indexfile = $path . $index . '-en' . '.php';
		if(file_exists($indexfile) == false)
		{
			return;
		}
	}	
	include($indexfile);
	return;
}

function sfa_tag_help($tag, $file)
{
	$helpurl=SFADMINURL."help/sf-adminhelp.php";
	$onclick = "return hs.htmlExpand(this, { objectType: 'ajax', preserveContent: true, width: 550} );";
	
	$htag=str_replace(' ','_', $tag);

	$helpurl.="?file=$file&amp;item=$htag";
	echo '<li><a href="'.$helpurl.'" onclick="'.$onclick.'">'.$tag.'</a></li>'."\n";
	return;
}

?>