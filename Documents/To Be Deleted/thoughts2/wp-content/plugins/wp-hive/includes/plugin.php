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

/*
 * Permalinks
 */

function wphive_check_permalinks() {
    global $wp_rewrite, $wphive;
    if (!$wp_rewrite->using_permalinks() && $wphive->site_has_subdirs($wphive->current_site)) {
        echo "<div id='wphive-warning' class='updated fade'><p><strong>WP Hive Warning:</strong><br/>A
subdirectory site is installed using WP Hive, but you are not using pretty permalinks.<br/>Fix the problem now by
<a href='/wp-admin/options-permalink.php'>configuring this site to use pretty permalinks</a>.
</p></div>";
    }
}
if ( isset($_REQUEST['wphive_save_site'])) {
    add_action('wphive_site_added', 'wphive_check_permalinks');
}
elseif ( isset($_REQUEST['wphive_remove_site']) ) {
    add_action('wphive_site_removed', 'wphive_check_permalinks');
}
elseif ( version_compare(get_bloginfo('version'), '2.8', '>=') &&  isset($_REQUEST['permalink_structure']) ) {
    add_action('permalink_structure_changed', 'wphive_check_permalinks');
}
else {
    add_action('admin_notices', 'wphive_check_permalinks');
}

/*
 * Admin
 */

function wphive_admin_hook() {
    if ( current_user_can('manage_options') ) {
        add_menu_page('WP Hive', 'WP Hive', 8, WPHIVE_PATH.'/admin/settings.php', null, get_option('siteurl').'/wp-content/plugins/wp-hive/images/wphive.png');
        add_submenu_page(WPHIVE_PATH.'/admin/settings.php', 'WP Hive > Settings', 'Settings', 8, WPHIVE_PATH.'/admin/settings.php');
        add_submenu_page(WPHIVE_PATH.'/admin/settings.php', 'WP Hive > Edit', 'Edit', 8, WPHIVE_PATH.'/admin/edit.php');
        add_submenu_page(WPHIVE_PATH.'/admin/settings.php', 'WP Hive > Add New', 'Add New', 8, WPHIVE_PATH.'/admin/add.php');
        define('WPHIVE_ADMIN_REL', 'admin.php?page=wp-hive/admin');
    }
}
add_action('admin_menu', 'wphive_admin_hook');

/*
 * Sorting out wp-admin confusion
 * when logging into subdirectories.
 */

function wphive_login_notices() {
    if ( isset($_COOKIE['wphive_pathname']) ) {

        function wphive_login_name($message) {
            return '<p class="message">' . sprintf('Logging into %s', get_bloginfo('title', 'display' )). '</p>';
        }
        add_filter('login_message', 'wphive_login_name' );

        function wphive_admin_name() {
            echo "<div id='wphive-warning' class='updated fade'><p>".sprintf('Administrating %s', get_bloginfo('title', 'display' )).".</p></div>";
        }
        add_action('admin_notices', 'wphive_admin_name');

    }
}
wphive_login_notices();

/*
 * Fix Meta URLs
 */

function wphive_meta_url($meta_url) {
    global $wphive;
    $pathname = $wphive->current_site->path;
    if ('/' != $pathname) {
        $link = str_replace(get_option('siteurl'), get_option('home'), $meta_url);
        return str_replace($pathname.$pathname, $pathname, $link);
    }
    return $meta_url;
}
add_filter('login_url', 'wphive_meta_url');
add_filter('logout_url', 'wphive_meta_url');
add_filter('register', 'wphive_meta_url');
add_filter('wp_admin', 'wphive_meta_url');

/*
 *  Log Out
 */

function wphive_logout() {
    wphive_delete_cookie('wphive_pathname');
    wp_redirect(get_bloginfo('home'));
    exit(0);
}
add_action('wp_logout', 'wphive_logout');

/*
 * Add Ons
 */

include (WPHIVE_ADDON_PATH.'/root-files-rewriter.php');

?>
