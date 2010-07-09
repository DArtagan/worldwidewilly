<?php
/*
Simple:Press Forum
Forum Search url creation
$LastChangedDate: 2009-01-01 03:25:57 +0000 (Thu, 01 Jan 2009) $
$Rev: 1093 $
*/

require_once("sf-config.php");

sf_load_foundation();

$url = $_SERVER['HTTP_REFERER'];

$param=array();

if(isset($_POST['statussearch'])) 
{
	$param['forum']=$_POST['forumid'];
	$param['value']=urlencode('statusflag%'.$_POST['statvalue']);
	$param['search']=1;
	$url=add_query_arg($param, SFURL);
} elseif(isset($_POST['membersearch'])) 
{
	if($_POST['searchoption'] == 'All Forums')
	{
		$param['forum']='all';
	} else {
		$param['forum']=$_POST['forumid'];
	}			
	$id=$_POST['userid'];
	$param['value']=urlencode('sf%members%1%user'.$id);
	$param['search']=1;
	$url=add_query_arg($param, SFURL);
} elseif(isset($_POST['memberstarted'])) 
{
	if($_POST['searchoption'] == 'All Forums')
	{
		$param['forum']='all';
	} else {
		$param['forum']=$_POST['forumid'];
	}			
	$id=$_POST['userid'];
	$param['value']=urlencode('sf%members%2%user'.$id);
	$param['search']=1;
	$url=add_query_arg($param, SFURL);
} else {
	if(isset($_POST['searchvalue']))
	{
		$searchvalue=trim(stripslashes($_POST['searchvalue']));
		$searchvalue=trim($searchvalue, '"');
		$searchvalue=trim($searchvalue, "'");
		$param=array();
		if($_POST['searchoption'] == 'All Forums')
		{
			$param['forum']='all';
		} else {
			$param['forum']=$_POST['forumid'];
		}			
		$param['value']=urlencode(sf_construct_search_parameter($searchvalue, $_POST['searchtype']));
		$param['search']=1;
		$url=add_query_arg($param, SFURL);
	}
}
wp_redirect($url);

?>