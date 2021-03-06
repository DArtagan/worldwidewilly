<?php
/*
Simple:Press Forum
Display Catgories for Post Linking
$LastChangedDate: 2009-01-16 20:14:58 +0000 (Fri, 16 Jan 2009) $
$Rev: 1230 $
*/

require_once("../../sf-config.php");

sf_load_foundation();

global $current_user;

# get out of here if no forum id specified
if (empty($_GET['forum'])) die();
$fid = sf_syscheckint($_GET['forum']);
sf_initialise_globals($fid);

if ($current_user->sflinkuse)
{
	global $catlist;

	$catlist ='<br /><br /><fieldset><legend>'.__("Select Catgories for Post", "sforum").'</legend>'.sf_write_nested_categories(sf_get_nested_categories(), 1).'</fieldset><br />';
	echo $catlist;
} else {
	echo (__('Access Denied', "sforum"));
}

die();

function sf_write_nested_categories($categories, $level)
{
	global $catlist;

	foreach ( $categories as $category )
	{
		for($x=0; $x<$level; $x++)
		{
			$catlist.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		$catlist.= '<label class="sfcatlist" for="in-category-'.$category["cat_ID"].'"><input value="'.$category['cat_ID'].'" type="checkbox" name="post_category[]" id="in-category-'.$category['cat_ID'].'"/>&nbsp;'.wp_specialchars($category['cat_name']).'</label><br />';

		if ( $category['children'] )
		{
			$level++;
			sf_write_nested_categories( $category['children'], $level );
			$level--;
		}
	}
	return $catlist;
}

function sf_get_nested_categories( $default = 0, $parent = 0 ) {

	$cats = sf_return_categories_list( $parent);
	$result = array ();

	if ( is_array( $cats ) ) {
		foreach ( $cats as $cat) {
			$result[$cat]['children'] = sf_get_nested_categories( $default, $cat);
			$result[$cat]['cat_ID'] = $cat;
			$result[$cat]['cat_name'] = get_the_category_by_ID( $cat);
		}
	}
	return $result;
}

function sf_return_categories_list( $parent = 0 ) {
	global $wpdb, $wp_version;

	$args=array();
	$args['parent']=$parent;
	$args['hide_empty']=false;
	$cats = get_categories($args);

	if($cats)
	{
		$catids=array();
		foreach($cats as $cat)
		{
			$catids[] = $cat->term_id;
		}
		return $catids;
	}
	return;
}

?>