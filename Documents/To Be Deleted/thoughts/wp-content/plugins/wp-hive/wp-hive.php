<?php
/*
 Plugin Name: WP Hive
 Plugin URI: http://wp-hive.com/
 Description: Run multiple blogs using a single installation of WordPress.
 Version: 0.5.3
 Author: ikailo
 Author URI: http://ikailo.com
 */

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

/*
 * Activate
 */
if (!function_exists('wphive_activate')) {
    function wphive_activate() {
        return copy( dirname(__FILE__) . '/db.php', WP_CONTENT_DIR . '/db.php' );
    }
    register_activation_hook(__FILE__,'wphive_activate');
}

/*
 * Deactivate
 */
if (!function_exists('wphive_deactivate')) {
    function wphive_deactivate() {
        if (file_exists( WP_CONTENT_DIR . '/db.php')) {
            wphive_delete_cookie('wphive_pathname');
            return unlink( WP_CONTENT_DIR . '/db.php' );
        }
    }
    register_deactivation_hook(__FILE__,'wphive_deactivate');
}

// Load the plugin

if ( defined('WPHIVE_PATH') ) {
// Engage Plugin
    require_once(WPHIVE_PATH.'/includes/plugin.php');
}
else {
// db.php is not in place. Must deactivate this plugin.
    $current = get_option('active_plugins');
    $plugin_name = 'wp-hive/wp-hive.php';
    $i = array_search($plugin_name, $current);
    if ($i !== false) {
        array_splice($current, $i, 1 );
        update_option('active_plugins', $current);
    }
}

?>