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

if ( !defined('ABSPATH') ) die("Hmmm...");

global $wphive;

if ( !$action ) $action = $_REQUEST['action'];

/* -------- *
 * Postback *
 * -------- */

if ( isset($_REQUEST['wphive_save_site'])) {
    check_admin_referer('wphive_manage_site');
    if (isset($_REQUEST['site'])) {
        $site_id = addslashes($_REQUEST['site']);
    }
    $hostname = $wphive->clean_hostname($_POST['site_host']);
    $pathname = $wphive->clean_pathname($_POST['site_path']);
    $prefix = addslashes($_POST['site_prefix']);
    // TODO: Ensure Prefix is unique
    $current_site = array(
        'id' => $site_id,
        'host' => $hostname,
        'path' => $pathname,
        'prefix' => $prefix
    );

    $success = $wphive->upsert_site($current_site);
    if ( $success ) {
        $message = "Site Saved.";
        $current_site = $wphive->get_site_by_prefix($prefix);
        $site_id = $current_site->id;
    }
    else {
        $message = "There was an error saving the site.";
    }
    do_action('wphive_site_added');
    ?>
<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></div>
<?php

}
elseif ( isset($_REQUEST['wphive_remove_site'])) {
    check_admin_referer('wphive_manage_site');
    $success = $wphive->remove_site(addslashes($_REQUEST['site']));
    if ($success) {
        $message = "Site was removed from WP Hive.";
        $action ='';

    }
    else {
        $message = "There was a problem removing the site from WP Hive.";
    }
    do_action('wphive_site_removed');
    ?>
<div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></div>
<?php
}


/* ------ *
 * Output *
 * ------ */

if ( ('edit' == $action) || ('add' == $action) ) {
    if (isset($_REQUEST['site']) && ( !$current_site) ) {
        $current_site = $wphive->get_site_by_id(addslashes($_REQUEST['site']));
        $site_id = $current_site->id;
    }
    ?>
<div class="wrap">
    <h2><?php echo wp_specialchars( $title ); ?></h2>
    <p>&lt; <a href="<?php echo WPHIVE_ADMIN_REL . '/edit.php' ?>">All Sites</a></p>
        <?php $siteq = $site_id ? '&site='.$site_id : ''; ?>
    <form method="post" action="<?php echo WPHIVE_ADMIN_REL . '/edit.php&action=edit'.$siteq ?>">
            <?php
            if ( function_exists('wp_nonce_field') )
                wp_nonce_field('wphive_manage_site');
            if ($site_id) { ?>
        <input id="site" type="hidden" value="<?php echo $current_site->id; ?>" name="site" />
            <?php } ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="site_hostname">Domain Name:</label>
                    </th>
                    <td><input id="site_host" class="regular-text" type="text"
                               value="<?php echo stripslashes($current_site->host); ?>"
                               name="site_host" /></td>
                    <td>Eg: <strong>example.com</strong></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="site_path">Sub Directory:</label></th>
                    <td><input id="site_path" class="regular-text" type="text"
                               value="<?php echo stripslashes($current_site->path); ?>" name="site_path" /></td>
                    <td>Eg: <strong>/subdirectory</strong><br/>Use <strong>/</strong> for none</td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="site_prefix">Prefix:</label></th>
                    <td><input id="site_prefix" class="regular-text" type="text"
                               value="<?php echo stripslashes($current_site->prefix); ?>"
                               name="site_prefix" /></td>
                    <td>A short unique identifier, usually ends with underscore. Eg: <strong>wp_</strong></td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input class="button-primary" type="submit"
                                 value="Save Changes" name="wphive_save_site" id="wphive_save_site" />
                                     <?php if ('edit' == $action) {?>
            <input class="button-primary" type="submit"
                   value="Remove Site" name="wphive_remove_site" id="wphive_remove_site" />
                           <?php if ($wphive->is_site_installed($current_site->id)) {?>
            <input class="button-primary" type="submit"
                   value="Visit Site" name="wphive_visit_site" id="wphive_visit_site" onclick="window.location='<?php echo 'http://'.$current_site->host.$current_site->path ?>';return false;"/>
                           <?php } else { ?>
            <input class="button-primary" type="submit"
                   value="Install Site" name="wphive_install_site" id="wphive_install_site" onclick="window.location='<?php echo 'http://'.$current_site->host.$current_site->path ?>';return false;"/>
                           <?php }
                       }
                       do_action('wphive_edit_buttons');
                       ?>
        </p>
    </form>
</div>
<?php
}
elseif ( empty($action) ) {
    ?>
<div class="wrap">
    <h2><?php echo wp_specialchars( $title ); ?></h2>
    <div class="clear"></div>
    <table class="widefat fixed" cellspacing="0">

        <thead>
            <tr>

                <th scope="col" id="host" class="manage-column column-host"
                    style="">Site Name</th>
                <th scope="col" id="prefix" class="manage-column column-prefix"
                    style="">Prefix</th>
                <th scope="col" id="id" class="manage-column column-id"
                    style="">Site ID</th>
                <th scope="col" id="visit" class="manage-column column-visit"
                    style="">&nbsp;</th>
            </tr>
        </thead>

        <tbody>
                <?php
                $all_sites = $wphive->get_all_sites();
                $alt = 0;
                foreach ($all_sites as $site) {
                    $style = ($alt % 2) ? '' : ' class="alternate"';
                    ++ $alt;
                    ?>
            <tr id="link-<?php echo $site->host; ?>" valign="middle"
                        <?php echo $style; ?>>
                            <?php
                            echo "<td class='column-name'><strong><a class='row-title'
href ='".WPHIVE_ADMIN_REL."/edit.php&amp;action=edit&amp;site=$site->id'
title='Edit $site->host$site->path'>$site->host$site->path</a></strong></td>";
                            ?>
                <td><?php echo $site->prefix ?></td>
                <td><?php echo $site->id ?></td>
                <?php $label = $wphive->is_site_installed($site->id) ? "Visit Site" : "Install Site"; ?>
                <td><a href="http://<?php echo $site->host.$site->path; ?>" title="<?php echo $label; ?>"><em><?php echo $label; ?></em></a></td>
            </tr>
                <?php } ?>
        </tbody>
    </table>
    <form method="post" action="<?php echo WPHIVE_ADMIN_REL . '/add.php'?>" >
        <p class="submit">
            <input id="wphive_add_site" class="button-secondary action" type="submit" name="wphive_add_site" value="Add New Site"/>
        </p>
    </form>
    <br class="clear" />
</div>
<?php } ?>