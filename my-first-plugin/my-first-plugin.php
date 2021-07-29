<?php
/**
 * Plugin Name: Plugin Teste
 * Description: Criação de Plugin para MADEIRA NLINCS
 * Version: 1.0
 * Author: João David
 */


//security issues
if ( ! defined('ABSPATH')){
    die;
}

require_once(plugin_dir_path(__FILE__) . 'includes\add-admin-page.php');
require_once(plugin_dir_path(__FILE__) . 'includes\register-researcher-type.php');
require_once(plugin_dir_path(__FILE__) . 'includes\register-publication-type.php');
require_once(plugin_dir_path(__FILE__) . 'includes\shortcodes.php');



function register_stylesheet(){
    wp_enqueue_style('mypluginstylefrontend',plugins_url('/stylesheet/frontend.css',__FILE__));
    //wp_enqueue_style('jquerycss','https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

}

add_action('wp_enqueue_scripts','register_stylesheet');
//add_action('admin_enqueue_scripts','register_stylesheet');

function register_backend(){
    wp_enqueue_style('mypluginstylebackend',plugins_url('/stylesheet/backend.css',__FILE__));
}

add_action('admin_enqueue_scripts','register_backend');


?>




