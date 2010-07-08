<?php
/*  Feed2JS : RSS feed to JavaScript src file

	VERSION 2.0 (2008 mar 28)
	
	ABOUT
	This PHP script will take an RSS feed as a value of src="...."
	and return a JavaScript file that can be linked 
	remotely from any other web page. Output includes
	site title, link, and description as well as item site, link, and
	description with these outouts contolled by extra parameters.
	
	Developed by Alan Levine initially released 13.may.2004
	http://cogdogblog.com/
	
	PRIMARY SITE:
	http://feed2js.org/
	 
	USAGE:
	See http://eduforge.org/projects/feed2js/
     
	Feed2JS makes use of the SimplePie RSS from
	 http://simplepie.org/
	
   ------------- small print ---------------------------------------
	GNU General Public License 
	Copyright (C) 2004-2005 Alan Levine
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details
	http://www.gnu.org/licenses/gpl.html
	------------- small print ---------------------------------------

*/

// ERROR CHECKING FOR NO SOURCE -------------------------------

$script_msg = '';
$src = (isset($_GET['src'])) ? $_GET['src'] : '';

// Strip slashes if magic quotes is enabled (which automatically escapes certain characters)
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
	{
		$src = stripslashes($src);
	}
	

// trap for missing src param for the feed, use a dummy one so it gets displayed.
if (!$src or strpos($src, 'http://')!=0) $src=  'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/nosource.rss';

// test for malicious use of script tages
if (strpos($src, '<script>')) {
	$src = preg_replace("/(\<script)(.*?)(script>)/si", "SCRIPT DELETED", "$src");
	die("Warning! Attempt to inject javascript detected. Aborted and tracking log updated.");
}


// PARSER SETUP ----------------------------------------------------
// access configuration settings
require_once('feed2js_config.php');

//  check for utf encoding type
$utf = (isset($_GET['utf'])) ? $_GET['utf'] : 'n';

if ($utf == 'y') {
	define('OUTPUT_ENCODING', 'UTF-8');
}

// GET VARIABLES ---------------------------------------------
// retrieve values from posted variables

// flag to show channel info
$chan = (isset($_GET['chan'])) ? $_GET['chan'] : 'n';

// variable to limit number of displayed items; default = 0 (show all, 100 is a safe bet to list a big list of feeds)

$num = (isset($_GET['num'])) ? $_GET['num'] : 0;
if ($num==0) $num = 100;

// indicator to show item description,  0 = no; 1=all; n>1 = characters to display
// values of -1 indicate to displa item without the title as a link
// (default=0)
$desc = (isset($_GET['desc'])) ? $_GET['desc'] : 0;

// flag to show date of posts, values: no/yes (default=no)
$date = (isset($_GET['date'])) ? $_GET['date'] : 'n';

// time zone offset for making local time, 
// e.g. +7, =-10.5; 'feed' = print the time string in the RSS w/o conversion
$tz = (isset($_GET['tz'])) ? $_GET['tz'] : 'feed';


// flag to open target window in new window; n = same window, y = new window,
// other = targeted window, 'popup' = call JavaScript function popupfeed() to display
// in new window (default is n)

$targ = (isset($_GET['targ'])) ? $_GET['targ'] : 'n';
if ($targ == 'n') {
	$target_window = ' target="_self"';
} elseif ($targ == 'y' ) {
	$target_window = ' target="_blank"';
} elseif ($targ == 'popup') {
	$target_window = ' onClick="popupfeed(this.href);return false"';
} else {
	$target_window = ' target="' . $targ . '"';
}

// flag to show feed as full html output rather than JavaScript, used for alternative
// views for JavaScript-less users. 
//     y = display html only for non js browsers (NO LONGER USED)
//     n = default (JavaScript view)
//     a = display javascript output but allow HTML 
//     p  = display text only items but convert linefeeds to BR tags

// default setting for no conversion of linebreaks
$html = (isset($_GET['html'])) ? $_GET['html'] : 'n';

$br = ' ';
if ($html == 'a') {
	$desc = 1;
} elseif ($html == 'p') {
	$br = '<br />';
}

// optional parameter to use different class for the CSS container
$rss_box_id = (isset($_GET['css'])) ? '-' . $_GET['css'] : '';

// optional parameter to use different class for the CSS container
$play_podcast = (isset($_GET['pc'])) ? $_GET['pc'] : 'n';


// PARSE FEED and GENERATE OUTPUT -------------------------------
// This is where it all happens!

// Create a new instance of the SimplePie object
$feed = new SimplePie();

$feed->set_feed_url($src);


// set utf encoding
if ($utf=='y') {
	$feed->set_output_encoding(OUTPUT_ENCODING);
}

// Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and 
// all that other good stuff.  The feed's information will not be available to SimplePie before 
// this is called.
$success = $feed->init();

// We'll make sure that the right content type and character encoding gets set automatically.
// This function will grab the proper character encoding, as well as set the content type to text/html.
$feed->handle_content_type();

// begin javascript output string for channel info
$str= "document.write('<div class=\"rss-box" . $rss_box_id . "\">');\n";


// no feed found by magpie, return error statement
if  (!$success) {
	$str.= "document.write('<p class=\"rss-item\">$script_msg<em>Error:</em> Feed failed! Causes may be (1) No data  found for RSS feed $src; (2) There are no items are available for this feed; (3) The RSS feed does not validate.<br /><br /> Please verify that the URL <a href=\"$src\">$src</a> works first in your browser and that the feed passes a <a href=\"http://feedvalidator.org/check.cgi?url=" . urlencode($src) . "\">validator test</a>.</p></div>');\n";


} else {

	// for LAB versions only!
	$str.= "document.write('<h2>DEV VERSION ONLY DO NOT USE FOR REAL SITES</h2>');\n";
	// set cache location
	$feed->set_cache_location(CACHE_DIR); 

	// Create CONNECTION CONFIRM
	// create output string for local javascript variable to let 
	// browser know that the server has been contacted
	$feedcheck_str = "feed2js_ck = true;\n\n";

	// we have a feed, so let's process
	if ($chan == 'y') {
	
		// double check the feed provides a link, some still mess this up
		if ($feed->get_link()) {
		// output channel title and description	
			$str.= "document.write('<p class=\"rss-title\"><a class=\"rss-title\" href=\"" . trim($feed->get_link()) . '"' . $target_window . ">" . addslashes(strip_returns($feed->get_title())) . "</a><br /><span class=\"rss-item\">" . addslashes(strip_returns(strip_tags($feed->get_description()))) . "</span></p>');\n";
		} else {
			$str.= "document.write('<p class=\"rss-title\">" .  addslashes(strip_returns($feed->get_title())) . "<br /><span class=\"rss-item\">" . addslashes(strip_returns(strip_tags($feed->get_description()))) . "</span></p>');\n";	
		}
	
	} elseif ($chan == 'title') {
		// output title only
		$str.= "document.write('<p class=\"rss-title\"><a class=\"rss-title\" href=\"" . trim($feed->get_link()) . '"' . $target_window . ">" . addslashes(strip_returns($feed->get_title())) . "</a></p>');\n";
	
	}	
	
	// begin item listing
	$str.= "document.write('<ul class=\"rss-items\">');\n";
		
	// Walk the items and process each one
	$all_items = array_slice($feed->get_items(), 0, $num);
	
	
	foreach ( $all_items as $item ) {
		
		if ($item->get_permalink()) {
			// link url
			$my_url = addslashes($item->get_permalink());
		}
		
		
		if ($desc < 0) {
			$str.= "document.write('<li class=\"rss-item\">');\n";
			
		} elseif ($item->get_title()) {
			// format item title
			$my_title = addslashes(strip_returns($item->get_title()));
						
			// create a title attribute. thanks Seb!
			$title_str = substr(addslashes(strip_returns(strip_tags((htmlspecialchars($item->get_description()
))))), 0, 255) . '...'; 

			// write the title strng
			$str.= "document.write('<li class=\"rss-item\"><a class=\"rss-item\" href=\"" . trim($my_url) . "\" title=\"$title_str\"". $target_window . '>' . $my_title . "</a><br />');\n";

		} else {
			// if no title, build a link to tag on the description
			$str.= "document.write('<li class=\"rss-item\">');\n";
			$more_link = " <a class=\"rss-item\" href=\"" . trim($my_url) . '"' . $target_window . ">&laquo;details&raquo;</a>";
		}
	
		// print out date if option indicated

		if ($date == 'y') {
					
			if ($tz == 'feed') {
				$pretty_date = $item->get_date($date_format);
			} else {
				// convert to local time via conversion to GMT + offset
				// adjust local server time to GMT and then adjust time according to user
				// entered offset.
				
				$pretty_date = date($date_format, strtotime($item->get_date())  - $tz_offset + $tz * 3600);
			
			}
	
			$str.= "document.write('<span class=\"rss-date\">$pretty_date</span><br />');\n"; 
		}

		// link to podcast media if availavle
		
		if ($play_podcast == 'y' and $enclosure = $item->get_enclosure(0)) {
			$str.= "document.write('<div class=\"pod-play-box\">');\n";
			foreach ($item->get_enclosures() as $enclosure) {
					$str.= "document.write('<a class=\"pod-play\" href=\"" . trim($enclosure->get_link()) . "\" title=\"Play Now\" target=\"_blank\"><em>Play</em> <span> " .  substr(trim($enclosure->get_link()), -3)  . "</span></a> ');\n";
				//}
			}
			
			$str.= "document.write('</div>');\n";
		
		}

	
		// output description of item if desired
		if ($desc) {
			
			$my_blurb = $item->get_content() ;
	
			// strip html
			if ($html != 'a') $my_blurb = strip_tags($my_blurb);
			
			// trim descriptions
			if ($desc > 1) {
			
				// display specified substring numbers of chars;
				//   html is stripped to prevent cut off tags
				$my_blurb = substr($my_blurb, 0, $desc) . '...';
			}
	
		
			$str.= "document.write('" . addslashes(strip_returns($my_blurb, $br)) . "');\n"; 
			
		}
			
		$str.= "document.write('$more_link</li>');\n";	
	}


	$str .= "document.write('</ul></div>');\n";

}

// Render as JavaScript
// START OUTPUT
// headers to tell browser this is a JS file
if ($rss) header("Content-type: application/x-javascript"); 

// Spit out the results as the series of JS statements
echo $feedcheck_str . $str;


?>
