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

function wphive_maybe_install($from_version, $to_version) {
    switch ($from_version) {
        case '':
            return wphive_install();
            break;
        case '0.4':
            wphive_upgrade_0_4($to_version);
            break;
        case '0.5':
            wphive_upgrade_0_5($to_version);
            break;
    }
}

function wphive_install () {
    global $wpdb, $wphive, $table_prefix, $hostname;
    // Create the config table
    $wpdb->query ("CREATE TABLE `wphive_config` (
	`item` varchar (255) NOT NULL,
	`val` varchar (255),
	PRIMARY KEY ( `item` ))");

    // Create the host table
    $wpdb->query ("CREATE TABLE `wphive_hosts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `host` varchar(255) NOT NULL,
        `prefix` varchar(255) NOT NULL,
        `path` varchar(255) NOT NULL,
        PRIMARY KEY (`id`))");

    // Create common directory
    if (! file_exists(WP_CONTENT_DIR . '/wp-hive/') ) {
        mkdir(WP_CONTENT_DIR . '/wp-hive');
    }
    // Set intitial prefix to existing $table_prefix (assume the prefix is an existing WP installation)
    if ($table_prefix != false) {

        $wpdb->insert('wphive_hosts', array('host'=>$hostname, 'prefix'=>$table_prefix, 'path'=>'/'));

        // Create storage directory for the domain
        if ( ! file_exists(WP_CONTENT_DIR . '/wp-hive/' . $hostname . '/' ) ) {
            mkdir(WP_CONTENT_DIR . '/wp-hive/' . $hostname . '/');
        }
        // Clean up special files in the root
        $specialfiles = array("robots.txt", "favicon.ico", "sitemap.xml", "sitemap.xml.gz");
        foreach ($specialfiles as $file) {
            if (file_exists(ABSPATH . '/' . $file) ) {
                rename(ABSPATH . '/' . $file, WP_CONTENT_DIR . '/wp-hive/' . $hostname . '/' . $file);
            }
        }

        // Insert some config values
        $wphive->add_option('version', '0.5');
        $wphive->add_option('default_permalinks', '/archives/%post_id%');
        return $prefix;
    }
}

function wphive_upgrade_0_4($to_version) {
    global $wpdb, $wphive;

    // Update the Hosts Table
    $wpdb->query ("ALTER TABLE `wphive_hosts`
                    ADD COLUMN `id` int NOT NULL AUTO_INCREMENT FIRST,
                    ADD COLUMN `path` varchar (255) NOT NULL AFTER `prefix`,
                    CHANGE `host` `host` varchar (255) NOT NULL,
                    CHANGE `prefix` `prefix` varchar (255) NOT NULL,
                    ADD PRIMARY KEY(`id` )");

    // Fix the path values
    $wpdb->update ('wphive_hosts', array('path'=>'/'), array('path' => '') );

    // Add new options
    $wphive->add_option('version', '0.5');
    $wphive->add_option('default_permalinks', '/archives/%post_id%');

// Depreceated Options
//$wphive->delete_option('installed');
//$wphive->delete_option('allow_new_hosts');
}

function wphive_upgrade_0_5($to_version) {
    global $wphive;
    $wphive->update_option('version', '0.5.1');
}

?>