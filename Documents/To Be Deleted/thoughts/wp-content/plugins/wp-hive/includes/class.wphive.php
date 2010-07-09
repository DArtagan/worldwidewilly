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

/**
 * WP Hive Multi-Blog Management Object
 *
 * This is the main API for WP Hive. It handles data abstraction
 * and general management of the sites in the hive.
 *
 * @package WP Hive
 * @since 0.5
 */

class wphive {

/**
 * The version of the WP Hive package
 * "PHP-standardized" version number string
 *
 * @package WP Hive
 * @since 0.5
 * @var string
 */
    var $version = '0.5.3';

    /**
     * Holds the current site object
     *
     * @since 0.5
     * @var object
     */
    var $current_site;

    /**
     * Constructor.
     *
     * @package WP Hive
     * @since 0.5
     *
     */
    function wphive ( ) { }

    /**
     * Gets the installed version of WP Hive
     * @package WP Hive
     * @since 0.5
     * @return The version of WP Hive that is installed
     */
    function get_installed_version() {
        global $wpdb;
        $suppress = $wpdb->suppress_errors();
        $version = $this->get_option('version');
        if (empty($version)) {
            $version = $this->get_option('installed') ? '0.4' : null;
        }
        $wpdb->suppress_errors($suppress);
        return $version;
    }

    /**
     * Determines if a site has subdirectories
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param object $site The site to check
     *
     * @return boolean True if the site has subdirectories
     */
    function site_has_subdirs( $site ) {
        global $wpdb;
        if ($site->path == '/') {
            return $wpdb->get_var($wpdb->prepare("SELECT Count(*) FROM wphive_hosts WHERE host = %s AND NOT path = %s", $site->host, "/")) > 0;
        }
        return false;
    }

    /**
     * Gets a site from the WP Hive database
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $id
     *
     * @return An object representing the site record
     */
    function get_site_by_id ( $id ) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM wphive_hosts WHERE id = %d ", $id) );
    }

    /**
     * Gets a site from the WP Hive database
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $prefix
     *
     * @return An object representing the site record
     */
    function get_site_by_prefix ( $prefix ) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM wphive_hosts WHERE prefix = %s ", $prefix));
    }

    /**
     * Removes a site record from the WP Hive Hosts table.
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param int $id
     *
     * @return Result of the database query
     */
    function remove_site ( $id ) {
        global $wpdb;
        return $wpdb->query($wpdb->prepare("DELETE FROM wphive_hosts WHERE id = %d", $id ));
    }

    /**
     * Checks if a site is installed
     *
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param int $id Default is current site
     *
     * @return boolean true if site is active
     */
    function is_site_installed ( $id = -1 ) {
        global $wpdb;
        $id = $id == -1 ? $current_site->id : $id;
        $prefix = $this->get_prefix_by_id($id);
        $suppress = $wpdb->suppress_errors();
        $installed = $wpdb->get_var("SELECT option_value FROM ".$prefix."options WHERE option_name = 'siteurl'");
        $wpdb->suppress_errors($suppress);
        return !empty($installed);
    }

    /**
     * Get a site Prefix
     *
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param int $id ID of site to check
     *
     * @return string $prefix
     */
    function get_prefix_by_id ( $id ) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT prefix FROM wphive_hosts WHERE id = %s", $id));
    }

    /**
     * Gets an array containing the site prefixes in the WP Hive database
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param bool $include_inactive Include sites listed as inactive.
     *
     * @return An array containing the site prefixes
     */
    function get_all_prefixes( $only_active = false ) {
        global $wpdb;
        $where_string = $only_active ? " WHERE is_active = 1" : "";
        return $wpdb->get_results("SELECT prefix FROM wphive_hosts" . $where_string);
    }

    /**
     * Gets an array containing the infomation for all the sites in the WP Hive database
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param bool $include_inactive Include sites listed as inactive.
     *
     * @return An array containing the information for all the sites
     */
    function get_all_sites ( $only_active = false ) {
        global $wpdb;
        $where_string = $only_active ? " WHERE is_active = 1" : "";
        return $wpdb->get_results("SELECT * FROM wphive_hosts" . $where_string);
    }
    /**
     *
     * Adds an option to the WP Hive Database
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $key The option name
     * @param string $value The option value
     *
     * @return Result of the database query
     */
    function add_option($key, $value) {
        global $wpdb;
        return $wpdb->query($wpdb->prepare("INSERT INTO wphive_config (item, val) VALUES (%s, %s)", $key, $value ) );
    }

    /**
     * Updates or Inserts a site in the WP Hive database
     * If an 'id' key exists and can be matched to an existing site,
     * it will be updated, otherwise a new new record will be added
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param array $site An array representing the site to be updated
     *
     * @return Result of the database query
     */
    function upsert_site ( $site ) {
        global $wpdb;
        if ( isset($site['id']) && (!empty($site['id'])) ) {
            $site_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM wphive_hosts WHERE id = %d", $site['id']));
        }
        unset($site['id']);
        if ($site_id) {
            return $wpdb->update('wphive_hosts', $site, array('id'=>$site_id));
        }
        else {
            return $wpdb->insert('wphive_hosts', $site);
        }
    }

    /**
     * Updates an option in the WP Hive Database
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $key The option name
     * @param string $value The option value
     *
     * @return Result of the database query
     */
    function update_option($key, $value) {
        global $wpdb;
        return $wpdb->query($wpdb->prepare("UPDATE wphive_config SET val=%s WHERE item=%s", $value, $key));
    }

    /**
     * Retrieve a WP Hive option value based on setting name.
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $key Name of option to retrieve.
     *
     * @return mixed Value set for the option. False if the option is empty
     */
    function get_option($key) {
        global $wpdb;
        // TODO: Verify the wphive_options table exists
        $result = $wpdb->get_var($wpdb->prepare("SELECT val FROM wphive_config WHERE item=%s", $key));
        if (empty($result)) { return false; }
        else { return $result; }
    }

    /**
     * Remove option value based on setting name.
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $key Option name to remove.
     *
     * @return mixed Result of Query
     */
    function delete_option($key) {
        global $wpdb;
        return $wpdb->query($wpdb->prepare("DELETE FROM wphive_config WHERE item=%s", $key ));
    }

    /**
     * Updates and option if it exists, otherwise add it.
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $key The option name
     * @param string $value The option value
     *
     * @return mixed Result of Query
     */
    function upsert_option($key, $value) {
        if ($this->option_exists($key)) {
            return $this->update_option($key, $value);
        }
        else {
            return $this->add_option($key, $value);
        }

    }

    /**
     * Checks if a WP Hive option exists in the database
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $key Name of option to check
     *
     * @return boolean True if the option exists
     */
    function option_exists($key) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM wphive_config WHERE item=%s", $key)) > 0;

    }

    /**
     * Generates a unique prefix to be used for a set of WordPress tables
     *
     * Note, this checks the WP Hive Hosts table only, does not account
     * for standalone prefixes not part of the hive.
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $hostname The domain name that the prefix is based on
     *
     * @return string A unique prefix
     */
    function get_unique_prefix($hostname) {

    // Create a prefix from the hostname
        $prefix = substr($hostname, 0, 3) . "_";
        str_replace('.' , '_', $prefix); // fix small subdomain prefixes

        // Ensure prefix is unique
        $i = 0;
        while ($this->prefix_exists($prefix)) {
            $prefix = substr($prefix, 0, 3) . $i . "_";
            $i++;
        }
        return $prefix;
    }

    /**
     * Determines if a a prefix exists in the WP Hive Hosts table
     *
     * Note, this checks the WP Hive Hosts table only, does not account
     * for standalone prefixes not part of the hive.
     *
     * @package WP Hive
     * @since 0.5
     *
     * @global $wpdb
     *
     * @param string $prefix The prefix to check
     *
     * @return boolean True if the prefix exists
     */
    function prefix_exists($prefix) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM wphive_hosts WHERE prefix=%s", $prefix)) > 0;
    }

    /**
     * Sanitizes hostname and removes unnecessary characters
     *
     * @package WP Hive
     * @since 0.5
     *
     * @param string $hostname The value to sanitize
     *
     * @return string Sanitized hostname
     */
    function clean_hostname( $hostname ) {
        if (substr($hostname, 0, 8) == "https://") { $hostname = substr($hostname, 8); } // Strip https://
        if (substr($hostname, 0, 7) == "http://") { $hostname = substr($hostname, 7); } // Strip http://
        if (substr($hostname, 0, 4) == "www.") { $hostname = substr($hostname, 4); } // Strip www
        $hostname = preg_replace('/:.*$/', '', $hostname); // Strip port
        $hostname = trim($hostname, "."); // Strip trailing dots
        return $hostname;
    }

    /**
     * Sanitizes pathname and removes unnecessary characters
     *
     * @package WP Hive
     * @since 0.5
     *
     * @param string $patname The value to sanitize
     *
     * @return string Sanitized pathname
     */
    function clean_pathname( $pathname ) {
        $pathname = preg_replace('|([a-z0-9-]+.php.*)|', '', $pathname); // Ignore calling php file (eg: index.php)
        if (substr($pathname, 0, 9) == "/wp-admin") { $pathname = '/'; } // Ignore wp-admin, assume root path
        $pathname = preg_replace('|(/[a-z0-9-]+?/).*|', '$1', $pathname); // Get the first subdirectory
        $pathname = "/" . trim($pathname, "/"); // Fix slashes

        return $pathname;
    }
}
?>