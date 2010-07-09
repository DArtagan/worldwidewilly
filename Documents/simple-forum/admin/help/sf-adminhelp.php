<?php
/*
Simple:Press Forum
Admin Help
$LastChangedDate: 2009-01-15 13:03:20 +0000 (Thu, 15 Jan 2009) $
$Rev: 1209 $
*/

require_once("../../sf-config.php");

if(!isset($_GET['file']))
{
	die();
}

$file = sf_syscheckstr($_GET['file']);
$tag = sf_syscheckstr($_GET['item']);
$tag='['.$tag.']';

if (isset($_GET['source']))
{
	$folder="panels/";
} else {
	$folder="";
}

# Formatting and Display of Help Panel
$helptext = wpautop(sf_retrieve_help($file, $tag, $folder), false);

echo '<div class="sfhelptext">';
echo '<div class="sfhelptag"><p>'.sf_convert_tag($tag).'</p></div>';
echo '<fieldset>';
echo $helptext;
echo '</fieldset>';
echo '<div class="sfhelptextlogo">';
echo '<img src="'.SF_PLUGIN_URL.'/admin/images/SPF-badge-125.png" width="128" height="38" alt="" title="" />';
echo '</div></div>';

die();

function sf_retrieve_help($file, $tag, $folder)
{
	$path = SF_PLUGIN_DIR . '/admin/help/'.$folder;
	$note = '';
	$lang = WPLANG;
	if(empty($lang)) $lang = 'en';
	$helpfile = $path.$file.'.'.$lang;

	if(file_exists($helpfile) == false)
	{
		$helpfile = $path.$file.'.en';
		if(file_exists($helpfile) == false)
		{
			return __("No Help File can be located", "sforum");
		} else {
			$note = __("Sorry - A Help File can not be found in your language", "sforum");
		}
	}	

	$fh = fopen($helpfile, 'r');

	do {
		$theData = fgets($fh);
		if(feof($fh))
		{
			break;
		}
	} while((substr($theData, 0, strlen($tag)) != $tag));

	$theData = '';
	$theEnd = false;
	do {
		if(feof($fh))
		{
			break;
		}
		$theLine = fgets($fh);
		if(substr($theLine, 0, 5) == '[end]') 
		{
			$theEnd = true;
		} else {
			$theData.= $theLine;
		}
	} while($theEnd == false);

	fclose($fh);
	
	return $note.'<br /><br />'.$theData;
}

function sf_convert_tag($tag)
{
	$tag = str_replace ('[', '', $tag);
	$tag = str_replace (']', '', $tag);
	$tag = str_replace ('-', ' ', $tag);
	$tag = str_replace ('_', ' ', $tag);
	return ucwords($tag);
}


?>