<?php
/*
 WP Hive is a Wordpress Plugin that allows a single Wordpress installation to service multiple blogs.
 This file is part of WP Hive.

 WP Hive is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 WP Hive is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with WP Hive.  If not, see <http://www.gnu.org/licenses/>.

 Copyright (C) 2009 John Sessford - john@ikailo.com
 */

/* --------
 * Robots.txt Manager
 * Don't forget to save the robots file as /robottxt/[db_prefix]robot.txt.
 * Also, you MUST ensure that no robot.txt file exists in the root directory.
 */
function hive_robots_check() {
	global $wpdb, $wphive;

	$robotsfile = WP_CONTENT_DIR . '/wp-hive/'. $wphive->current_site->host . '/robots.txt';
	if ( file_exists ( $robotsfile ) ) {
		readfile( $robotsfile );
		exit(0);
	}
}
add_action('do_robotstxt', 'hive_robots_check');


/* --------
 * Sitemap.xml Manager
 * Don't forget to set the sitemap generator to save the file as /sitemaps/[db_prefix]sitemap.xml.
 * Also, you MUST ensure that no sitemap.xml file exists in the root directory.
 */
function hive_sitemap_check() {
	global $wpdb, $wp_query, $wphive;

	if ( $wp_query->get('sitemap') == '1') {
		$sitemapfile = WP_CONTENT_DIR . '/wp-hive/'. $wphive->current_site->host . '/sitemap.xml';
		if ( file_exists ( $sitemapfile ) ) {
			header('Content-type: application/xml; charset="utf-8"');
			readfile( $sitemapfile );
			exit(0);
		}
		else {
			status_header('404');
			include ( TEMPLATEPATH . '/404.php' );
			exit(0);
		}
	}
	elseif ( $wp_query->get('sitemapgz') == '1' ) {
		$sitemapgzfile = WP_CONTENT_DIR . '/wp-hive/'. $wphive->current_site->host .'/sitemap.xml.gz';
		if ( file_exists ( $sitemapgzfile ) ) {
			header('Content-type: application/x-gzip');
			readfile( $sitemapgzfile );
			exit(0);
		}
		else {
			status_header('404');
			include ( TEMPLATEPATH . '/404.php' );
			exit(0);
		}
	}
}
add_action('template_redirect', 'hive_sitemap_check');

// add sitemap as an allowed query var
function hive_sitemap_query_var($vars){
	array_push($vars, 'sitemap', 'sitemapgz');
	return $vars;
}
add_filter('query_vars','hive_sitemap_query_var');

// add sitemap rewrite rules
function hive_sitemap_intercept($rewrite_rules) {

	$sitemap_rules = array (
		'sitemap.xml$' => 'index.php?sitemap=1',
		'sitemap.xml.gz$' => 'index.php?sitemapgz=1'
		);

		return ( $rewrite_rules + $sitemap_rules );
}
add_filter( 'root_rewrite_rules', 'hive_sitemap_intercept' );


/* --------
 * Favicon.ico Manager
 * Don't forget to save your favicon.ico file as /favicons/[db_prefix]favicons.ico.
 * Also, you MUST ensure that no favicon.ico file exists in the root directory.
 */
function hive_favicon_check() {
	global $wpdb, $wp_query, $wphive;

	if ( $wp_query->get('favicon') == '1') {
		$faviconfile = WP_CONTENT_DIR . '/wp-hive/'. $wphive->current_site->host . '/favicon.ico';
		if ( file_exists ( $faviconfile ) ) {
			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$fmime = finfo_file($finfo, $faviconfile);
				finfo_close($finfo);
			}
			else {
				require_once( WPHIVE_PATH . '/resources/mime-content-type.php' );
				$fmime = mime_content_type($faviconfile);
			}
			header('Content-type: ' . $fmime); // Send the actual MIME type
			readfile( $faviconfile );
			exit(0);
		}
		else {
			status_header('404');
			echo "File Does Not Exist";
			exit(0);
		}
	}
}
add_action('template_redirect', 'hive_favicon_check');

// add favicon as an allowed query var
function hive_favicon_query_var($vars){
	array_push($vars, 'favicon');
	return $vars;
}
add_filter('query_vars','hive_favicon_query_var');

// add favicon rewrite rules
function hive_favicon_intercept($rewrite_rules) {

	$favicon_rules = array (
		'favicon.ico$' => 'index.php?favicon=1'
		);

		return ( $rewrite_rules + $favicon_rules );
}
add_filter( 'root_rewrite_rules', 'hive_favicon_intercept' );
?>