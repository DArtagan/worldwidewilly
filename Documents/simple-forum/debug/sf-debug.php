<?php
/*
Simple:Press Forum
Debug
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('Access Denied'); 
}

# ------------------------------------------
# display a formatted array
# ------------------------------------------
function ashow($what)
{
	echo('<div class="sfdebug">');
	echo('<pre>');
	print_r($what);
	echo('</pre>');
	echo('</div>');
	return;
}

# ------------------------------------------
# display an individual variable
# ------------------------------------------
function vshow($what="HERE")
{
	echo('<div class="sfdebug">');
	echo('---'.$what.'---<br />');
	echo('</div>');
	return;
}

# ------------------------------------------
# starts debug mode at forum display start
# ------------------------------------------
function start_debug()
{
	if(defined('SHOWDEBUG'))
	{
		if(SHOWDEBUG)
		{
			global $wpdb, $sfqueries;

			$sfqueries = $wpdb->num_queries;
		}
	}
	return;
}

# ------------------------------------------
# ends debug mode at forum end & displays
# ------------------------------------------
function end_debug()
{
	if(defined('SHOWDEBUG'))
	{
		if(SHOWDEBUG)
		{
			global $sfqueries;
			$out = '<div class="sfdebug">';
			$out.= (get_num_queries() - $sfqueries).' queries | ' . timer_stop(0).' seconds'.'<br />';
			if(isset($GLOBALS['sfcount'])) $out.='Query Count: '.$GLOBALS['sfcount'].'<br />';
			$out.= showglobal();
			$out.= '</div>';
		}
		return $out;
	}
	return;
}

# ------------------------------------------
# places a value in global 'sfdebug'
# ------------------------------------------
function addglobal($data)
{
	$GLOBALS['sfdebug'] = $GLOBALS['sfdebug'].$data.'<br />';
	return;
}

# ------------------------------------------
# returns global 'sfdebug' for display
# ------------------------------------------
function showglobal()
{
	return $GLOBALS['sfdebug'];
}

# ------------------------------------------
# starts partial query count
# ------------------------------------------
function start_count()
{
	global $wpdb, $sfpartqueries;

	$sfpartqueries = get_num_queries();
	return;
}

# ------------------------------------------
# ends partial query count
# ------------------------------------------
function end_count()
{
	global $wpdb, $sfpartqueries;

	$GLOBALS['sfcount'] = (get_num_queries() - $sfpartqueries);
	return;
}

# ------------------------------------------
# display SPF files included
# ------------------------------------------
function show_includes()
{
	echo('<div class="sfdebug">');

	$filelist = get_included_files();
	foreach($filelist as $f)
	{
		if(strpos($f, 'simple-forum'))
		{
			echo $f.'<br />';
		}
	}
	echo('</div>');
	return;
}

?>