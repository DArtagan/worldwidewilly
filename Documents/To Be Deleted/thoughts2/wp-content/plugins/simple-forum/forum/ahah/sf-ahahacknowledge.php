<?php
/*
Simple:Press Forum
Ahah call for acknowledgements
$LastChangedDate: 2009-02-19 14:41:26 +0000 (Thu, 19 Feb 2009) $
$Rev: 1416 $
*/

require_once("../../sf-config.php");

$sfstyle = array();
$sfstyle = get_option('sfstyle');

$out = '';

$out.= '<div id="sfAbout">';
$out.= '<img src="'.SFJSCRIPT.'SPF4-banner.png" alt="" title="" /><br />';

$out.= '<p>Version: <b>'.SFVERSION.'</b> (Build: <b>'.SFBUILD;
if(SFRELEASE != '') $out.= ' / '.SFRELEASE;
$out.= ')</b></p>';
$out.= '<p>&copy; 2005-'.date('Y').' by <a href="http://www.yellowswordfish.com">Andy Staines</a> and <a href="http://cruisetalk.org/"><b>Steve Klasen</b></a><br /></p>';
$out.= '<hr />';

$out.= '<p>'.__("TinyMCE text editor by Moxiecode Systems: ", "sforum").'<a href="http://www.moxiecode.com/">Moxiecode</a></p>';
$out.= '<p>'.__("TinyMCE 'code' plugin by Oliver Seidel: ", "sforum").'<a href="http://www.deliciousdays.com/">Delicious Days</a></p>';
$out.= '<p>'.__("Image Upload Add-On by Phillip Winn: ", "sforum").'<a href="http://blogcritics.org/">Blog Critics</a></p>';
$out.= '<p>'.__("Highslide (Popup Boxes) by Torstein H&oslash;nsi: ", "sforum").'<a href="http://vikjavev.no/highslide/">Vikjavev.no</a><sup>1</sup></p>';
$out.= '<p>'.__("Math Spam Protection based on code by Michael Woehrer: ", "sforum").'<a href="http://sw-guide.de/">Software Guide</a></p>';
$out.= '<p>'.__("Default 'Silk' Icon Set by Mark James: ", "sforum").'<a href="http://www.famfamfam.com/lab/icons/silk/">fam fam fam</a></p>';
$out.= '<p>'.__("Calendar Date Picker by TengYong Ng: ", "sforum").'<a href="http://www.rainforestnet.com">Rain Forest Net</a></p>';
$out.= '<p>'.__("Tabbed Admin uses jQuery ui.tabs by Klaus Hartl: ", "sforum").'<a href="http://stilbuero.de/">Stilb&uuml;ro</a></p>';
$out.= '<p>'.__("Checkbox and Radio Button transformations: ", "sforum").'<a href="http://www.no-margin-for-errors.com/">Stephane Caron</a></p>';

$out.= '<p>'.__("Gravatars2 WordPress Plugin by Kip Bond: ", "sforum").'<a href="http://zenpax.com/gravatars2/">ZenPax</a></p>';
if(get_option('sfdemocracy'))
{
	$out.= '<p>'.__("Democracy Polls by Andrew Sutherland: ", "sforum").'<a href="http://blog.jalenack.com/archives/democracy/">Jalenack</a></p>';
}

$out.= '<hr />';

$out.= '<p>'.__("My thanks to all the people who have aided, abetted, coded, suggested and helped test this plugin", "sforum").'</p><br />'."\n";
$out.= '<p><sup>1</sup>'.__("Please Note: The Highslide popup window routines can freely be used by non-commercial sites. If you intend to use Simple:Press Forum on a commercial site, a license must be purchased.", "sforum").'</p><br />'."\n";
$out.= __("This forum is using the ", "sforum").'<strong> '.$sfstyle['sfskin'].'</strong> '.__("skin and the <strong>", "sforum").' '.$sfstyle['sficon'].'</strong> '.__("icons", "sforum").'<br />'."\n";

$out.= '</div>';
echo $out;

die();

?>