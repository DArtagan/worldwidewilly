<?php
/* Feed2JS : RSS feed to JavaScript Configuration include

	Use this include to establish server specific paths
	and other common functions used by the feed2js.php
	
	See main script for all the gory details
	
	created 10.sep.2004
	updated 28.mar.2007
*/


// SIMPLEPIE SET UP ---------------------------------------------

define('CACHE_DIR',  './cache');

// Simple pie library
include_once('./simplepie.inc');

// OTHER SETTIINGS ----------------------------------------------
// Output spec for item date string if used
// see http://www.php.net/manual/en/function.date.php
$date_format = "F d, Y h:i:s a";


// server time zone offset from GMT
// If this line generates errors (common on Windoze servers,
//   then figure out your time zone offset from GMT and enter
//   manually, e.g. $tz_offset = -7;

$tz_offset = gmmktime(0,0,0,1,1,1970) - mktime(0,0,0,1,1,1970);

// ERROR Handling ------------------------------------------------

// Report all errors except E_NOTICE
// This is the default value set in php.ini for Apache but often not Windows
// We recommend changing the value to 0 once your scripts are working
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL^ E_NOTICE);


// Utility to remove return characters from strings that might
// pollute JavaScript commands. While we are at it, substitute 
// valid single quotes as well and get rid of any escaped quote
// characters
function strip_returns ($text, $linefeed=" ") {
	$subquotes = ereg_replace("&apos;", "'", stripslashes($text));
	return ereg_replace("(\r\n|\n|\r)", $linefeed, $subquotes);
}

?>