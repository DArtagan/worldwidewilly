<?php
	// Prevent users from directly loading this theme file
	defined( 'K2_CURRENT' ) or die ( 'Error: This file can not be loaded directly.' );

	// Load localizatons
	load_theme_textdomain('k2_domain');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="template" content="K2 <?php k2info('version'); ?>" />

	<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

	<link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo('template_url'); ?>/style.css" />

	<?php /* Child Themes */ if ( K2_CHILD_THEME ): ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo('stylesheet_url'); ?>" />
	<?php endif; ?>

<link rel="stylesheet" href="/main_style2.css" type="text/css" />
	
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />

	<?php if ( is_singular() ): ?>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php endif; ?>

	<?php wp_head(); ?>

	<?php wp_get_archives('type=monthly&format=link'); ?>
</head>

<body class="<?php k2_body_class(); ?>">

<?php /* K2 Hook */ do_action('template_body_top'); ?>

<div id="skip">
	<a href="#startcontent" accesskey="2"><?php _e('Skip to content','k2_domain'); ?></a>
</div>

<div id="page">

	<?php /* K2 Hook */ do_action('template_before_header'); ?>

	<div id="header" role="banner">

		<?php locate_template( array('blocks/k2-header.php'), true ); ?>

		<?php /* K2 Hook */ do_action('template_header'); ?>

	</div> <!-- #header -->


	
<div id="nav">
	<a href="/" class="nav homenav">Home</a>
	<a href="/resume.html" class="nav resumenav">R&eacute;sum&eacute;</a>
	<a href="/thoughts" class="nav thoughtsnav">Thoughts</a>
	<a href="/projects.html" class="nav projectsnav">Projects</a>
	<a href="/contact.html" class="nav contactnav">Contact</a>
<a href="end.html" class="nav endnav">The End</a>
</div>