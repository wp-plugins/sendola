<?php
/**
Plugin Name: Sendola
Plugin URI: http://www.sendola.com
Description: Send your contact details and location to your customer's phone
Version: 1.0.0
Author: Sendola
Author URI: http://www.sendola.com/
License: A "Slug" license name e.g. GPL2
*/

// Make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
	echo 'I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'SENDOLA_VERSION', '1.0.0' );
define( 'SENDOLA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( is_admin() ) {
	require_once dirname( __FILE__ ) . '/admin.php';
	sendola_get_buttons();
}

function sendola_init() {
	// add sendola icon to the visual editor
	// only if user has at least one Sendola button
	if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) && sendola_has_buttons() ) {
		add_filter( 'mce_buttons', 'sendola_mce_button' );
		add_filter( 'mce_external_plugins', 'sendola_mce_plugin' );
		add_action( 'admin_print_scripts', 'sendola_add_button' );
	}
}
add_action( 'init', 'sendola_init' );

// Add settings link on plugin page
function sendola_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=sendola-options">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'sendola_plugin_settings_link' );

/**
 * Add sendola button to visual editor
 */
function sendola_mce_button( $buttons ) {
	// add a separation before our button, here our button's id is 'sendola';
	array_push( $buttons, '|', 'sendola' );
	return $buttons;
}
function sendola_mce_plugin( $plugins ) {
	// this plugin file will work the magic of our button
	$plugins['sendola'] = SENDOLA_PLUGIN_URL . 'sendola_plugin.js';
	return $plugins;
}

/**
 * Add sendola button to the html editor
 */
function sendola_add_button() {
	wp_enqueue_script( 'my_custom_quicktags', SENDOLA_PLUGIN_URL . 'quicktags.js', array('quicktags')	);
}


/**
 * [sendola id=xxx] shortcode replacement
 */
function sendola_shortcode( $args ) {
	return '<div id="now_send_it_wrapper_' . $args['id'] . '"><script src="http://admin.sendola.com/get/button/' . $args['id'] . '"></script></div>';
}
add_shortcode( 'sendola', 'sendola_shortcode' );

/**
 * Connects to Sendola API to check if such API key exists
 *
 * @param 	string 		API key
 * @return 	string 		valid|invalid
 */
function sendola_verify_key( $api_key ) {
	$exists = json_decode( file_get_contents( 'http://admin.sendola.com/api/wp/exists/' . $api_key ) );
	return $exists == true ? 'valid' : 'invalid';
}

/**
 * Get user's buttons from Sendola API
 *
 * @param  boolean		tells if we should get fresh list of buttons from API
 * @return array 			buttons data (id, image, name, domain, description)
 */
function sendola_get_buttons( $reload = false ) {
	global $sendola_buttons;
	if ( ! empty( $sendola_buttons ) &&  ! $reload) {
		return $sendola_buttons;
	}

	$sendola_api_key = get_option( 'sendola_api_key' );

	// api needs domain name without http and www
	$domain_name =  preg_replace( '/^www\./', '', $_SERVER['SERVER_NAME'] );

	$sendola_buttons = json_decode( file_get_contents( 'http://admin.sendola.com/api/wp/buttons/' . $sendola_api_key . '/' . $domain_name ) );
	return $sendola_buttons;
}

/**
 * Checks if user have any buttons
 *
 * @param boolean
 */
function sendola_has_buttons() {
	global $sendola_buttons;
	
	if ( ! is_array( $sendola_buttons ) ) {
		$sendola_buttons = sendola_get_buttons();
	}

	return ! empty( $sendola_buttons );
}
?>
