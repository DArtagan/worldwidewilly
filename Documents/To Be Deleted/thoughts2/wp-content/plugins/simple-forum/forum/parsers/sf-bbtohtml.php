<?php
/*
Simple:Press Forum
bbCode to xhtml parser
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

function sf_BBCode2Html($text) 
{

	$text = trim($text);

	# BBCode [code]
	if (!function_exists('escape')) {
		function escape($s) {
			global $text;
			$text = strip_tags($text);
			return '<code>'.htmlspecialchars($s[1]).'</code>';
		}	
	}
	$text = preg_replace_callback('/\[code\](.*?)\[\/code\]/ms', "escape", $text);
	
	# BBCode to find...
	$in = array( 	 '/\[b\](.*?)\[\/b\]/ms',	
					 '/\[i\](.*?)\[\/i\]/ms',
					 '/\[u\](.*?)\[\/u\]/ms',
					 '/\[img\](.*?)\[\/img\]/ms',
					 '/\[url\="?(.*?)"?\](.*?)\[\/url\]/is',
					 '/\[quote](.*?)\[\/quote\]/ms',
					 '/\[list\=(.*?)\](.*?)\[\/list\]/ms',
					 '/\[list\](.*?)\[\/list\]/ms',
					 '/\[\*\]\s?(.*?)\n/ms'
	);
	# And replace them by...
	$out = array(	 '<strong>\1</strong>',
					 '<em>\1</em>',
					 '<u>\1</u>',
					 '<img src="\1" alt="\1" />',
					 '<a href="\1">\2</a>',
					 '<blockquote>\1</blockquote>',
					 '<ol start="\1">\2</ol>',
					 '<ul>\1</ul>',
					 '<li>\1</li>'
	);
	$text = preg_replace($in, $out, $text);
	
	# paragraphs
	$text = str_replace("\r", "", $text);
	$text = "<p>".ereg_replace("(\n){2,}", "</p><p>", $text)."</p>";
	$text = nl2br($text);
	
	# clean some tags to remain strict
	if (!function_exists('removeBr')) {
		function removeBr($s) {
			return str_replace("<br />", "", $s[0]);
		}
	}	
	$text = preg_replace_callback('/<pre>(.*?)<\/pre>/ms', "removeBr", $text);
	$text = preg_replace('/<p><pre>(.*?)<\/pre><\/p>/ms', "<pre>\\1</pre>", $text);
	
	$text = preg_replace_callback('/<ul>(.*?)<\/ul>/ms', "removeBr", $text);
	$text = preg_replace('/<p><ul>(.*?)<\/ul><\/p>/ms', "<ul>\\1</ul>", $text);
	
	return $text;
}

?>