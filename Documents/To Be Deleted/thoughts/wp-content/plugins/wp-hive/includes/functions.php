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

function wphive_force_plugin_active() {
    global $wpdb, $table_prefix;
    if (function_exists('apply_filters')) {
        $active_plugins = get_option('active_plugins');
        $plugin_name = 'wp-hive/wp-hive.php';
        if ( !in_array($plugin_name, $active_plugins) ) {
            $active_plugins[] = $plugin_name;
            sort($active_plugins);
            update_option('active_plugins', $active_plugins);
        }
    }
    else {
    // Too early. Gotta hack it.
    // TODO: Slow. Need to use cache / better algorithm
        $suppress = $wpdb->suppress_errors();
        $active_plugins = $wpdb->get_var("SELECT option_value FROM ".$table_prefix."options WHERE option_name = 'active_plugins' LIMIT 1");
        $active_plugins = unserialize($active_plugins);
        if ( empty($active_plugins) ) {$active_plugins = array();}
        $plugin_name = 'wp-hive/wp-hive.php';
        if ( !in_array($plugin_name, $active_plugins) ) {
            $active_plugins[] = $plugin_name;
            sort($active_plugins);
            $active_plugins = serialize($active_plugins);
            $wpdb->query( $wpdb->prepare( "UPDATE ".$table_prefix."options SET option_value = %s WHERE option_name = %s", $active_plugins, 'active_plugins' ) );
        }
        $wpdb->suppress_errors($suppress);
    }
}

function wphive_set_cookie($name, $value, $expire = 0, $path = '/') {
    setcookie($name, $value, $expire, $path);
}

function wphive_delete_cookie($name) {
    setcookie($name, ' ', time() - 31536000, '/');
}

function wphive_redirect($location, $status = 302) {
    $location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%]|i', '', $location);
    $strip = array('%0d', '%0a');
    $found = true;
    while($found) {
        $found = false;
        foreach($strip as $val) {
            while(strpos($location, $val) !== false) {
                $found = true;
                $location = str_replace($val, '', $location);
            }
        }
    }

    $is_IIS = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) ? true : false;
    if ( $is_IIS ) {

        header("Refresh: 0;url=$location");
    } else {
        if ( php_sapi_name() != 'cgi-fcgi' ) {
            status_header($status); // This causes problems on IIS and some FastCGI setups
        }
        header("Location: $location");
    }
}

// Hack to make wp-install redirect properly, also checks the home option
// Unfortunately we need to make a call to the database. This can be fixed if we 'cache' some
// details about the sites and their statuses.
function wphive_siteurl_for_install($pathname, $table_prefix, $orig_hostname, $hostname) {
    global $wpdb;
    if (!defined('WP_INSTALLING')) {
        $suppress = $wpdb->suppress_errors();
        // The core method to check if the site is installed is to check 'siteurl'.
        // In essence of speed, we will check 'home' instead, since we will validate it in the next procedure.
        $home = $wpdb->get_var("SELECT option_value FROM ".$table_prefix."options WHERE option_name = 'home'");
        $wpdb->suppress_errors($suppress);
        if (empty ($home)) {
        //FIXME: orig_hostname not defined
            $siteurl = substr($orig_hostname, 0, 4) == "www." ? 'http://www.' . $hostname : 'http://' . $hostname;
            $siteurl .= $pathname == "/" ? '' : $pathname;
            define('WP_SITEURL', $siteurl);
        }
        elseif ( strpos($home, $pathname, strlen($orig_hostname) ) === false ) {
            $siteurl = $wpdb->get_var("SELECT option_value FROM ".$table_prefix."options WHERE option_name = 'siteurl'");
            $home = trim($siteurl, '/').$pathname;
            $wpdb->query($wpdb->prepare("UPDATE ".$table_prefix."options SET option_value=%s WHERE option_name='home'", $home));
        }
    }
}

// Override, fix permalink issue
function wphive_override_install($blog_title, $user_name, $user_email, $public, $deprecated='') {
    global $wp_rewrite;

    wp_check_mysql_version();
    wp_cache_flush();
    make_db_current_silent();
    populate_options();
    populate_roles();

    update_option('blogname', $blog_title);
    update_option('admin_email', $user_email);
    update_option('blog_public', $public);

    $guessurl = wp_guess_url();

    update_option('siteurl', $guessurl);

    // If not a public blog, don't ping.
    if ( ! $public )
        update_option('default_pingback_flag', 0);

    // Create default user.  If the user already exists, the user tables are
    // being shared among blogs.  Just set the role in that case.
    $user_id = username_exists($user_name);
    if ( !$user_id ) {
        $random_password = wp_generate_password();
        $message = __('<strong><em>Note that password</em></strong> carefully! It is a <em>random</em> password that was generated just for you.');
        $user_id = wp_create_user($user_name, $random_password, $user_email);
        update_usermeta($user_id, 'default_password_nag', true);
    } else {
        $random_password = '';
        $message =  __('User already exists.  Password inherited.');
    }

    $user = new WP_User($user_id);
    $user->set_role('administrator');

    wp_install_defaults($user_id);

    if (!isset($_COOKIE['wphive_pathname'])) {
        $wp_rewrite->flush_rules();
    }

    wp_new_blog_notification($blog_title, $guessurl, $user_id, $random_password);

    wp_cache_flush();

    return array('url' => $guessurl, 'user_id' => $user_id, 'password' => $random_password, 'password_message' => $message);
}
?>