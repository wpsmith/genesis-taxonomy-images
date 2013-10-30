<?php
/*
Plugin Name: Genesis Taxonomy Images
Plugin URI: http://www.studiograsshopper.ch/projects/genesis-taxonomy-images
Version: 0.8.0
Author: Ade Walker
Contributors: studiograsshopper
Author URI: http://www.studiograsshopper.ch/
Description: Create and manage Taxonomy Images for the Genesis theme framework

License:

Copyright 2013 Ade Walker (info@studiograsshopper.ch)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
VERSIONS
		
0.8.0	- Public release

		
*/

// Prevent direct access to the plugin
if ( ! defined( 'ABSPATH' ) ) {
	exit( _( 'Sorry, you are not allowed to access this page directly.' ) );
}


// Define some constants
define( 'GTAXI_URL',			plugins_url( 'genesis-taxonomy-images' ) );
define( 'GTAXI_DIR', 			plugin_dir_path( __FILE__ ) );
define( 'GTAXI_LIB_DIR', 		GTAXI_DIR . '/lib' );
define( 'GTAXI_VER', 			'0.8.0' );
define( 'GTAXI_WP_VER_REQ', 	'3.6' );
define( 'GTAXI_GEN_MIN_VER',	'2.0.0' );


// Load files
require_once( GTAXI_LIB_DIR . '/genesis-taxonomy-image-functions.php' );


register_activation_hook( __FILE__, 'gtaxi_activation' );
/**
 * Check the environment when plugin is activated
 *
 * Requirements:
 * - Genesis (min version) must be current theme 'Template'
 *
 * Note: register_activation_hook() isn't run after auto or manual upgrade, only on activation
 *
 * @since 0.8.0
 *
 * @return void.
 */
function gtaxi_activation() {

	$message = '';
	
	// Check that Genesis min version is installed
	if ( version_compare( PARENT_THEME_VERSION, GTAXI_GEN_MIN_VER, '<' ) ) {
	
		$message .= sprintf( __( '<br /><br />Install and activate <a href="%s">Genesis Framework %s</a> or greater', 'gtaxi' ), 'http://my.studiopress.com/downloads/genesis', GTAXI_GEN_MIN_VER );
	
	}

	// Display messages if necessary
	if ( ! empty( $message ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself

		$message = __( 'Sorry! In order to use the Genesis Taxonomy Images plugin you need to do the following:', 'gtaxi' ) . $message;

		wp_die( $message, 'Genesis Taxonomy Images', array( 'back_link' => true ) );

	}
}