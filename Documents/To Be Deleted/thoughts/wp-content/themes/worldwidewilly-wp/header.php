<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<link rel="shortcut icon" href="/images/favicon.ico" />
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

<link rel="stylesheet" href="/main_style2.css" type="text/css" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<!--<style type="text/css" media="screen">

<?php
// Checks to see whether it needs a sidebar or not
if ( empty($withcomments) && !is_single() ) {
?>
	#page { background: url("<?php bloginfo('stylesheet_directory'); ?>/images/kubrickbg-<?php bloginfo('text_direction'); ?>.jpg") repeat-y top; border: none; }
<?php } else { // No sidebar ?>
	#page { background: url("<?php bloginfo('stylesheet_directory'); ?>/images/kubrickbgwide.jpg") repeat-y top; border: none; }
<?php } ?>

</style>-->

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

<?php wp_head(); ?>
</head>
<body id="thoughts" <?php body_class(); ?>>
<div id="page">


<div id="header" role="banner">
	<div id="headerimg">
    <h1><a href="http://www.worldwidewilly.immortalkeep.com"><img src="/images/The_W_Logo3.png" class="logo" /><?php bloginfo('name'); ?></a></h1>
		<!--<div class="description"><?php bloginfo('description'); ?></div>-->
	</div>
</div>

<div id="nav">
	<a href="/" class="nav homenav">Home</a>
	<a href="/resume.html" class="nav resumenav">R&eacute;sum&eacute;</a>
	<a href="/thoughts" class="nav thoughtsnav">Thoughts</a>
	<a href="/projects.html" class="nav projectsnav">Projects</a>
	<a href="/contact.html" class="nav contactnav">Contact</a>
<a href="end.html" class="nav endnav">The End</a>
</div>