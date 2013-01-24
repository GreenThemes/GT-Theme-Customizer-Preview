<?php
/*
Plugin Name: GT Theme Customizer Preview for Guests
Plugin URI: http://green.cx
Description: Allows guests to preview theme options
Version: 1
Author: Jason Green
Author URI: http://green.cx/
*/
/*  Copyright 2012 Jason Green (http://green.cx)
  Donncha O Caoimh (http://ocaoimh.ie/) http://ocaoimh.ie/wordpress-mu-sitewide-tags/
    With contributions by Ron Rennick(http://wpmututorials.com/), Thomas Schneider(http://www.im-web-gefunden.de/) and others.

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

$gt_user= new WP_User( null, 'test' );
$gt_user->add_cap('edit_theme_options');
/**
 * Includes and instantiates the WP_Customize_Manager class.
 *
 * Fires when ?wp_customize=on or on wp-admin/customize.php.
 *
 * @since 3.4.0
 */
function _gt_wp_customize_include() {
	if ( ! ( ( isset( $_REQUEST['gt_customize'] ) && 'on' == $_REQUEST['wp_customize'] )
		|| ( 'gt-customize.php' == basename( $_SERVER['PHP_SELF'] ) )
	) )
		return;

	require( ABSPATH . WPINC . '/class-wp-customize-manager.php' );
	// Init Customize class
	$GLOBALS['wp_customize'] = new WP_Customize_Manager;
}
add_action( 'plugins_loaded', '_gt_wp_customize_include' );

/**
 * Adds settings for the customize-loader script.
 *
 * @since 3.4.0
 */
function _gt_wp_customize_loader_settings() {
	global $wp_scripts;

	$admin_origin = parse_url( admin_url() );
	$home_origin  = parse_url( home_url() );
	$cross_domain = ( strtolower( $admin_origin[ 'host' ] ) != strtolower( $home_origin[ 'host' ] ) );

	$browser = array(
		'mobile' => wp_is_mobile(),
		'ios'    => wp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] ),
	);

	$settings = array(
		'url'           => esc_url( plugins_url() . 'includes/gt-customize.php'  ),
		'isCrossDomain' => $cross_domain,
		'browser'       => $browser,
	);

	$script = 'var _wpCustomizeLoaderSettings = ' . json_encode( $settings ) . ';';

	$data = $wp_scripts->get_data( 'customize-loader', 'data' );
	if ( $data )
		$script = "$data\n$script";

	$wp_scripts->add_data( 'customize-loader', 'data', $script );
}
//add_action( 'admin_enqueue_scripts', '_gt_wp_customize_loader_settings' );

/**
 * Returns a URL to load the theme customizer.
 *
 * @since 3.4.0
 *
 * @param string $stylesheet Optional. Theme to customize. Defaults to current theme.
 * 	The theme's stylesheet will be urlencoded if necessary.
 */
function gt_wp_customize_url( $stylesheet = null ) {
	$url = admin_url( 'gt-customize.php' );
	if ( $stylesheet )
		$url .= '?theme=' . urlencode( $stylesheet );
	return esc_url( $url );
}
