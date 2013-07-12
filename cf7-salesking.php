<?php
/*
Plugin Name: Contact Form 7 - SalesKing Addon
Plugin URI: http://www.salesking.eu/wordpress-contributions/salesking-addon-for-contact-form-7/
Description: Add the power of SalesKing to Contact Form 7
Author URI: http://www.salesking.eu
Version: 1.0.0
*/

/*  Copyright 2013 SalesKing GmbH (email: support@salesking.eu)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'WPCF7_SK_VERSION', '1.0.0' );

// first check if it is good to go?
if (!version_compare( PHP_VERSION, '5.3.0', '>=' )) {
  deactivate_plugins( __FILE__ );
  wp_die( wp_sprintf( __( 'Sorry, This plugin has taken a bold step in requiring PHP 5.3.0+. Your server is currently running PHP %2s, Please bug your host to upgrade to a recent version of PHP which is less bug-prone.', 'wpcf7-sk' ), PHP_VERSION ) );
}
if (!in_array('curl', get_loaded_extensions())) {
  deactivate_plugins( __FILE__ );
  wp_die( __( 'Sorry, This plugin requires the curl extension for PHP which isn\'t available on you server. Please contact your host.', 'wpcf7-sk' ));
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// contact form is present and active
if (!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
  deactivate_plugins( __FILE__ );
  wp_die('Contact Form 7 plugin is required to configure this SalesKing Addon plugin. Please install and activate Contact Form 7 plugin before activating SalesKing');
}

if ( ! defined( 'WPCF7_SK_PLUGIN_BASENAME' ) )
  define( 'WPCF7_SK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require('models/sk_cf.class.php');
require('models/sk_cf_admin.class.php');
require('models/sk_rest.class.php');

