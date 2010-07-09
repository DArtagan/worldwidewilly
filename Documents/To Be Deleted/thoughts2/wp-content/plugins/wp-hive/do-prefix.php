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

if ( ! defined('ABSPATH') ) die("Hmmm...");

global $wpdb, $table_prefix, $hostname, $pathname, $wphive;

define('WPHIVE_PATH', dirname(__FILE__));
define('WPHIVE_ADDON_PATH', WPHIVE_PATH.'/add-ons');

require_once ('includes/functions.php');
require_once ('includes/class.wphive.php');

$wphive = new wphive();

// Get and clean up the hostname
if (!$hostname) {
    $orig_hostname = strtolower(addslashes($_SERVER['HTTP_HOST']));
    $hostname = $wphive->clean_hostname($orig_hostname);
}

// Get and clean up the pathname
$orig_pathname = strtolower(addslashes($_SERVER['REQUEST_URI']));
$pathname = $wphive->clean_pathname($orig_pathname);

// Root-Redirect Triggers
if( !defined('WP_ADMIN') && !defined('WP_INSTALLING')) {
    if ( strpos($orig_pathname, $pathname . '/wp-admin') !== false ||
        strpos($orig_pathname, $pathname . '/wp-login.php') !== false ) {
        wphive_set_cookie('wphive_pathname', $pathname);
        $redirect = 'http://' . $orig_hostname . substr($orig_pathname, strlen($pathname)); // Strip path for redirect
        wphive_redirect($redirect);
        exit (0);
    }
}

// Root-Redirect Passthrough
if ( isset ($_COOKIE['wphive_pathname']) ) {
    if (
        isset($_GET['preview']) ||
        strpos( $orig_pathname, '/wp-login.php') !== false ||
        defined('WP_ADMIN') ||
        defined('WP_INSTALLING')) {

        $pathname =  addslashes($_COOKIE['wphive_pathname']);
    }
    else {
    //wphive_delete_cookie('wphive_pathname');
    }
}

// Check if WP Hive is installed, run installation or upgrade as required.
$installed_version = $wphive->get_installed_version();
if ( $installed_version != $wphive->version ) {
    require_once ('includes/install.php');
    $prefix = wphive_maybe_install($installed_version, $wphive->version);
}

// Get the corresponding prefix from the db
if (empty ($prefix)) {
// get all the sites possible for the hostname
    $possible_sites = $wpdb->get_results($wpdb->prepare("SELECT prefix, path FROM wphive_hosts WHERE host = %s", $hostname));
    if (!$possible_sites) {
    // TODO: Premium Feature - Redirect to alternate site.
        wp_die("Unknown Host");
    } else {
        foreach ($possible_sites as $site) {
            if ($site->path == "/") {
            // root path, save this prefix
                $root_prefix = $site->prefix;
            }
            // If it matches, serve that site
            if ($site->path == $pathname) {
                $prefix = $site->prefix;
                break;
            }
        }
        if (empty ($prefix)) {
            if( !empty( $root_prefix ) ) {
            // Assume root site
                $prefix = $root_prefix;
                $pathname = '/';
                unset($orig_pathname);
            }
            else {
            // TODO: Call properly through add-on functionality
                if (file_exists(WPHIVE_ADDON_PATH.'/unknown-root.php')) {
                    require(WPHIVE_ADDON_PATH.'/unknown-root.php');
                }
                else {
                    wp_die("Unknown Host");
                }
            }
        }
    }
}

// This is what it all boils down to:
$table_prefix = $prefix;

// TODO: Inefficient - should be in class. Need to rework wphive class.
$wphive->current_site = $wphive->get_site_by_prefix($prefix);

/* WP Install Override */
// Prevents clearing of .htaccess on subdirectory install
if ( defined('WP_INSTALLING') && WP_INSTALLING === true && $wphive->current_site->path != '/' ) {
    function wp_install($blog_title, $user_name, $user_email, $public, $deprecated='') {
        return wphive_override_install($blog_title, $user_name, $user_email, $public, $deprecated);
    }
}

// Clear the admin cookie if the user visits another site in the hive
if (isset ($_COOKIE['wphive_pathname']) && $_COOKIE['wphive_pathname'] != $wphive->current_site->path) {
    wphive_delete_cookie('wphive_pathname');
}

// Setting up for success - force plugin active. Will be changed in a future version.
// Just remember that you need to delete db.php if you want to deactivate the plugin.
wphive_force_plugin_active();

// Check if this is a fresh install and fix Siteurl for proper redirect
wphive_siteurl_for_install($pathname, $table_prefix, $orig_hostname, $wphive->current_site->host);

?>