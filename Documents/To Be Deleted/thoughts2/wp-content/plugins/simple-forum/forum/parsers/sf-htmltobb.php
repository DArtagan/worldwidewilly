<?php
/*
Simple:Press Forum
xhtml to bbCode parser
$LastChangedDate: 2009-05-27 19:16:01 +0100 (Wed, 27 May 2009) $
$Rev: 1953 $
*/

function sf_Html2BCode($text)
{
	$text = trim($text);
	$text = stripslashes($text);

	# Tags to Find
	$htmltags = array(
		'/\<b\>(.*?)\<\/b\>/is',
		'/\<em\>(.*?)\<\/em\>/is',
		'/\<u\>(.*?)\<\/u\>/is',
		'/\<ul\>(.*?)\<\/ul\>/is',
		'/\<li\>(.*?)\<\/li\>/is',
		'/\<img(.*?) src=\"(.*?)\" (.*?)\>/is',
		'/\<blockquote\>(.*?)\<\/blockquote\>/is',
//		'/\<br(.*?)\>/is',
		'/\<strong\>(.*?)\<\/strong\>/is',
		'/\<a href=\"(.*?)\"(.*?)\>(.*?)\<\/a\>/is',
	);

	# Replace with
	$bbtags = array(
		'[b]$1[/b]',
		'[i]$1[/i]',
		'[u]$1[/u]',
		'[list]$1[/list]',
		'[*]$1',
		'[img]$2[/img]',
		'[quote]$1[/quote]',
//		'\n',
		'[b]$1[/b]',
		'[url=$1]$3[/url]',
	);

	# Replace $htmltags in $text with $bbtags
	$text = preg_replace ($htmltags, $bbtags, $text);




/*
	$in = array( 	'/\<strong\>(.*?)\<\/strong\>/ms',
					'/\<em\>(.*?)\<\/em\>/ms',
					'/\<u\>(.*?)\<\/u\>/ms',
					'/\<img\s+.*src="([^"]+)".*\/\>/ms',
					'/\<a\s+.*href="([^"]+)".*\>(.+)\<\/a\s*\>/ms',
					'/\<blockquote\>(.*?)\<\/blockquote\>/ms',
					'/\<ul\>(.*?)\<\/ul\>/ms',
				 	'/\<li\>\s?(.*?)<\/li\>/ms'
	);
	# And replace them by...
	$out = array(	'[b]\1[/b]',
					'[i]\1[/i]',
					'[u]\1[/u]',
					'[img]\1[/img]',
					'[url="\1"]\2[/url]',
					'[quote]\1[/quote]',
					'[list]\1[/list]',
					'[*]\1'
	);
	$text = preg_replace($in, $out, $text);
*/

	$text = str_replace ("<p>", "", $text);
	$text = str_replace ("</p>", "\r\r", $text);
	$text = str_replace("<br />", "\r", $text);

	$text = str_replace ("<div class=\"sfcode\">", "[code]", $text);
	$text = str_replace ("</div>", "[/code]", $text);

	# Strip all other HTML tags
	$text = strip_tags($text);


	return $text;
}

?>