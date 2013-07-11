<?php
/*
Plugin Name: Contact Form 7 - SalesKing Addon
Plugin URI: http://www.salesking.eu/wordpress-contributions/salesking-addon-for-contact-form-7/
Description: Add the power of SalesKing to Contact Form 7
Author URI: http://www.salesking.eu
Version: 0.1.0
*/
define( 'WPCF7_SK_VERSION', '0.1.0' );
/*  
MIT Licence
*/
// first check if it is good to go?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

require('models/sk_cf7.class.php');
require('models/sk_rest.class.php');


$plugin_name = 'contact-form-7/wp-contact-form-7.php';
$is_active = is_plugin_active($plugin_name);
if ($is_active == '1') {
} else {
  echo '<br /><br /><div class="alert alert-error">Contact Form 7 plugin is required to configure this SalesKing Addon plugin. Please install and activate Contact Form 7 plugin and then return to this page.</div>';
  exit;
}
function activationHook() {
    if (!version_compare( PHP_VERSION, '5.3.0', '>=' )) {
        deactivate_plugins( __FILE__ );
        wp_die( wp_sprintf( __( 'Sorry, This plugin has taken a bold step in requiring PHP 5.3.0+. Your server is currently running PHP %2s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone.', 'wpcf7-sk' ), PHP_VERSION ) );
    }
    if (!in_array('curl', get_loaded_extensions())) {
        deactivate_plugins( __FILE__ );
        wp_die( __( 'Sorry, This plugin requires the curl extension for PHP which isn\'t available on you server. Please contact your host.', 'wpcf7-sk' ));
    }
}

// contact form is present and active
// lets do our stuff



if ( ! defined( 'WPCF7_SK_PLUGIN_BASENAME' ) )
    define( 'WPCF7_SK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );


add_action( 'wpcf7_admin_notices', 'add_sk_meta' );
function add_sk_meta (){
    if ( wpcf7_admin_has_edit_cap() ) {
        add_meta_box(
          'cf7skdiv',
          __( 'SalesKing', 'wpcf7' ),
          'wpcf7_sk_add_salesking',
          'cfseven',
          'cf7_sk',
          'core',
          array( 'id' => 'wpcf7-cf7',
                 'name' => 'cf7_sk',
                 'use' => __( 'Use SalesKing', 'wpcf7' ) ) );
    }
}

function wpcf7_sk_add_salesking($cf){
  $sk = new SkCf7();
  $sk->show_form($cf);
}

add_action( 'wpcf7_admin_after_mail_2', 'show_sk_metabox' );
function show_sk_metabox($cf){
    do_meta_boxes( 'cfseven', 'cf7_sk', $cf );
}

add_action( 'wpcf7_after_save', 'wpcf7_sk_save_salesking' );
function wpcf7_sk_save_salesking($cf){
  $sk = new SkCf7();
  $sk->save_form($cf);
}

add_action( 'admin_print_scripts', 'wpcf7_sk_admin_enqueue_scripts' );
function wpcf7_sk_admin_enqueue_scripts ()
{
    global $plugin_page;

    if ( ! isset( $plugin_page ) || 'wpcf7' != $plugin_page )
        return;

    wp_enqueue_script( 'wpcf7-sk-admin', wpcf7_sk_plugin_url( 'scripts.js' ),
        array( 'jquery', 'wpcf7-admin' ), WPCF7_SK_VERSION, true );
}

function strip_unprocessed_tags_or_null($val1, $ret_val2, $begin_tag_indicator= "[", $end_tag_indicator = "]"){
    // 
    $begin_pos = strpos($val1, $begin_tag_indicator);
    $end_pos = strpos($ret_val2, $end_tag_indicator);
    // values are unprocessed, and there seems to be a tag left
    if (($val1 == $ret_val2) && ( $begin_pos <> $end_pos)){
        $ret_val2 = "";
    }
    return $ret_val2;
}

/**
 * Sends form data to salesking, first substitutes placeholders in SK contact form setting fields
 * @param $obj WPCF7_ContactForm
 */
function wpcf7_sk_submit($obj){
  $sk = new SkCf7();
  $sk->submit($obj);
}
add_action( 'wpcf7_before_send_mail', 'wpcf7_sk_submit' );

function wpcf7_sk_plugin_url( $path = '' ) {
    return plugins_url( $path, WPCF7_SK_PLUGIN_BASENAME );
}

// settings link goes to cf page
add_filter( 'plugin_action_links', 'wpcf7_sk_plugin_action_links', 10, 2 );

function wpcf7_sk_plugin_action_links( $links, $file ) {

    if ( $file != plugin_basename( __FILE__ ) )
        return $links;

    $settings_link = '<a href="' . menu_page_url( 'wpcf7', false ) . '">'
        . esc_html( __( 'Settings', 'wpcf7' ) ) . '</a>';

    array_unshift( $links, $settings_link );
    return $links;
}
