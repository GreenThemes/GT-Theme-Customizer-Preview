<?php
/*
Plugin Name: GT Theme Customizer Preview for Guests
Plugin URI: http://greenthe.me
Description: Allows guests to preview theme options
Version: 1.5
Author: Jason Green
Author URI: http://greenthe.me/

    Copyright 2015 Jason Green (http://green.cx)

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

	Changelog

	1.05 - Updated for compability with WP 4.3.1
	1.04 - Updated to match WP 3.6 core Accordian javascripts. 3.6 Now Required for this verison.
*/

//This section executes outside of wordpress to create users, roles, login, etc.
//This section requires no input.
if (isset($_GET['gtlo'])) {

	//Climb dirs till we find wp-blog-header (Varies depending on wordpress install)
        while (! file_exists('wp-blog-header.php') )
        chdir('..');

    //Needed for user functions
        require ("wp-blog-header.php");

	//The username of the "guest" or "test" account we are going to manage
		$gt_user_login = 'Guest';

	//If username already exists jump to the customizer
	 if ( username_exists( $gt_user_login ) ) {
			 	//Now  Login as the new test user
				$user = get_user_by('login', $gt_user_login);
				$user_id = $user->ID;
				wp_set_current_user($user_id, $gt_user_login);
				wp_set_auth_cookie($user_id);
				do_action('wp_login', $gt_user_login);
                    //Username Exists. But are they a user of this blog on multisite? Lets check first...
                        global $current_user, $blog_id;
                        if( !is_user_member_of_blog() ) {
                            add_user_to_blog($blog_id, $current_user->ID, 'theme_options_preview');
                        }
                //Ok, we're all done making sure everything works, let's take the Guest to their customizer
				wp_redirect(plugins_url('/includes/gt-customize.php' , __FILE__ ));
				exit; }
	else {
		// else create the account and give them permission to theme_options
			wp_create_user( $gt_user_login, 'SomeReallyLongForgettablePassworderp1234567654321', 'Guest@' . preg_replace('/^www\./','',$_SERVER['SERVER_NAME']) );
			$gt_user= new WP_User( null, $gt_user_login );
				//Let's create a new role for this type of user to manage permissions more adequately
				$result = add_role('theme_options_preview', 'Theme Options Preview', array(
				    'read' => true,
				    'edit_posts' => false,
				    'delete_posts' => false, // Use false to explicitly deny
				    'edit_theme_options' => true, //This is the magic
				));
						$gt_user->set_role('theme_options_preview'); // Assign that role to the new user
	}
}

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
add_action( 'admin_enqueue_scripts', '_gt_wp_customize_loader_settings' );

/**
 * Returns a URL to load the theme customizer.
 *
 * @since 3.4.0
 *
 * @param string $stylesheet Optional. Theme to customize. Defaults to current theme.
 * 	The theme's stylesheet will be urlencoded if necessary.
 */
function gt_wp_customize_url( $stylesheet = null ) {
	$url = plugins_url('/includes/gt-customize.php' , __FILE__ );
	if ( $stylesheet )
		$url .= '?theme=' . urlencode( $stylesheet );
	return esc_url( $url );
}

/**
 *	Expose the Customizer Preview by adding a link in the admin bar.
 */
add_action ('admin_bar_menu', 'gt_customize_menu');
function gt_customize_menu($admin_bar) {
	$admin_bar->add_menu( array (
	'id' => 'customizer-preview',
	'title' => 'Customizer Preview',
	'href' => plugins_url('/gt-custom.php?gtlo' , __FILE__ ),
	'meta' => array(
		'title' => __('Greenth.me Customizer Preview'),
		),
	));
}

//IF the test user tries to view admin, take them back home
function gt_restrict_admin_with_redirect() {

		function endswith($string, $test) {
		    $strlen = strlen($string);
		    $testlen = strlen($test);
		    if ($testlen > $strlen) return false;
		    return substr_compare($string, $test, -$testlen) === 0;
		}
    //Get current user's role
	global $current_user;
 	$user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);

	if( is_admin() &&
        $user_role == 'theme_options_preview'
		&& !endswith($_SERVER['PHP_SELF'], '/wp-admin/admin-ajax.php')
		&& !endswith($_SERVER['PHP_SELF'], '/includes/gt-customize.php') ) {
		wp_redirect(site_url() ); exit;
	}
}
add_action('init', 'gt_restrict_admin_with_redirect');

//Create Shortcode to drop the login and redirect link
// Usage: [GTCustomizer]Preview Theme[/GTCustomizer]
    function gt_autologin_link($atts, $content = null) {
    	extract(shortcode_atts(array('link' => plugins_url('/gt-custom.php?gtlo' , __FILE__ )), $atts));
    	return '<a class="button" href="'.$link.'"><span>' . do_shortcode($content) . '</span></a>';
    }
    add_shortcode('GTCustomizer' , 'gt_autologin_link');

//Remove WPBar if the user's role is Theme_Options_Preview
add_filter( 'get_user_metadata', 'gt_remove_admin_bar', 10, 3 );
function gt_remove_admin_bar( $null, $user_id, $key )
{
    global $current_user;
    if ($current_user != null) {
     	$user_roles = $current_user->roles;
        $user_role = array_shift($user_roles);
    }
    if( 'show_admin_bar_front' != $key ) return null;
    if( $user_role == 'theme_options_preview' ) return 0;
    return null;
}
