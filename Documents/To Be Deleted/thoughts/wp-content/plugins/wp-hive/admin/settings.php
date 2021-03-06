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

/* ------ *
 * Output *
 * ------ */
?>
<div class="wrap">
    <h2><?php echo wp_specialchars( $title ); ?></h2>    
    <p>No settings to change<br/>
        Except sites: <a href="<?php echo WPHIVE_ADMIN_REL.'/add.php'; ?>">add</a>
        and <a href="<?php echo WPHIVE_ADMIN_REL.'/edit.php'; ?>">edit</a>.<br/>
        Better still, <a href="http://wp-hive.com/donate">donate</a></p>
</div>
