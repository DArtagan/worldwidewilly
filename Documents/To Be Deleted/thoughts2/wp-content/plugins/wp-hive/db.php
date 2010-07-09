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

/* Put this file into the wp-content directory
 * WP Hive Plugin - Database Hook
 */

if ( ! defined('ABSPATH') ) die("Hmmm...");

require_once( ABSPATH . WPINC . '/wp-db.php' );

if ( ! defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

if ( file_exists(WP_PLUGIN_DIR . '/wp-hive/do-prefix.php')) 
    require_once( WP_PLUGIN_DIR . '/wp-hive/do-prefix.php');

?>