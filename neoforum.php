<?php
/*
Plugin Name: NeoForum
Description: Forum engine for Wordpress
Version: 1.0
Author: Dmitrij Mogil
Text Domain: neoforum
Domain Path: /nf-languages
*/

/*  Copyright 2019  Dmitrij Mogil  

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


   
function neoforum_fixslash($path){
        $sep = DIRECTORY_SEPARATOR;
        if( $sep == '/' ){
                $fix_sep = '\\';
        }elseif( $sep == '\\' ){
                $fix_sep = '/';
        }
        $path = str_replace($sep,$fix_sep,$path);
        return $path;
}
define ("neoforum_DIR", rtrim( plugin_dir_path( __FILE__ ), '/' ));
define ("neoforum_URL", plugin_dir_url( __FILE__ ));

function neoforum_load_text_domain() {
    load_plugin_textdomain( 'neoforum', false, basename( dirname( __FILE__ ) ) . '/nf-languages/');
}

add_action( 'plugins_loaded', 'neoforum_load_text_domain' );

function neoforum_uninstall(){
    if ($_GET['action']=='nf-uninstall')
        {
            include( neoforum_DIR . '/nf-includes/uninstall.php' );
            die;
        }
}

function neoforum_activation_links($links){
    $links[]='<a href="'.site_url().'/wp-admin/index.php?action=neoforum-uninstall" style="color:#a00;">'.__("Uninstall", "neoforum").'</a>';
    return $links;
}

function neoforum_includes() {
    include( neoforum_DIR . '/nf-admin/admin-panel.php' );
    include( neoforum_DIR . '/nf-admin/settings.php' );
    include( neoforum_DIR . '/nf-includes/activate.php' );
    include( neoforum_DIR . '/nf-includes/forums.php' );
    include( neoforum_DIR . '/nf-includes/topics.php' );
    include( neoforum_DIR . '/nf-includes/posts.php' );
    include( neoforum_DIR . '/nf-includes/users.php' );
    include( neoforum_DIR . '/nf-includes/template.php' );
    include( neoforum_DIR . '/nf-includes/rewrites.php' );

    }

neoforum_includes();

add_shortcode("neoforum", "neoforum_shortcode_handler");
register_activation_hook( __FILE__, 'neoforum_activate' );
register_activation_hook( __FILE__, 'neoforum_rewrite_activation');
register_deactivation_hook( __FILE__, 'neoforum_deactivate' );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'neoforum_activation_links' );
add_action('in_admin_header', 'neoforum_uninstall');


//JS admin enqueue
$neoforum_globals=array('plugin_url'=>neoforum_URL, 'site_url'=>get_site_url());

function neoforum_enqueue_admin_js(){
    global $neoforum_globals;
    if ($_GET['neoforum_admin']=='neoforum_reports'){
        wp_register_script(
        'neoforum_mainpage_js',
        plugins_url( 'nf-admin/js/main-page.js', __FILE__ ),
        array( 'wp-i18n' ),
        '0.0.1'
        );
        wp_set_script_translations( 'neoforum_mainpage_js', 'neoforum' );
        wp_enqueue_script( "neoforum_mainpage_js");
        wp_localize_script('neoforum_mainpage_js', 'neoforum_globals', $neoforum_globals);
    }
    if ($_GET['page']=='neoforum_forums'){
        wp_register_script(
        'neoforum_forums_js',
        plugins_url( 'nf-admin/js/forums.js', __FILE__ ),
        array( 'wp-i18n' ),
        '0.0.1'
        );
        //wp_set_script_translations( 'neoforum_forums_js', 'neoforum' );
        wp_enqueue_script( "neoforum_forums_js");
        wp_localize_script('neoforum_forums_js', 'neoforum_globals', $neoforum_globals);
    }
    if ($_GET['page']=='neoforum_trash'){
        wp_register_script(
        'neoforum_trash_js',
        plugins_url( 'nf-admin/js/trash.js', __FILE__ ),
        array( 'wp-i18n' ),
        '0.0.1'
        );
        wp_set_script_translations( 'neoforum_trash_js', 'neoforum' );
        wp_enqueue_script( "neoforum_trash_js");
        wp_localize_script('neoforum_trash_js', 'neoforum_globals', $neoforum_globals);
    }
    if ($_GET['page']=='neoforum_moderation'){
        wp_register_script(
        'neoforum_users_js',
        plugins_url( 'nf-admin/js/users.js', __FILE__ ),
        array( 'wp-i18n' ),
        '0.0.1'
        );
        wp_set_script_translations( 'neoforum_users_js', 'neoforum' );
        wp_enqueue_script( "neoforum_users_js");
        wp_localize_script('neoforum_users_js', 'neoforum_globals', $neoforum_globals);
    }
    if ($_GET['page']=='neoforum_reports'){
        wp_register_script(
        'neoforum_reports_js',
        plugins_url( 'nf-admin/js/reports.js', __FILE__ ),
        array( 'wp-i18n' ),
        '0.0.1'
        );
        wp_set_script_translations( 'neoforum_reports_js', 'neoforum' );
        wp_enqueue_script( "neoforum_reports_js");
        wp_localize_script('neoforum_reports_js', 'neoforum_globals', $neoforum_globals);
    }

}
add_action( 'admin_enqueue_scripts', 'neoforum_enqueue_admin_js' );
?>
