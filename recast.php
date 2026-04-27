<?php
/*
Plugin Name: Recast
Description: A Wordpress plugin to manage Listings, Offers, and other Recast tools. Creates Shortcodes for use with Elementor.
Author: <a href="https://www.leechdemon.com" target="_blank">Leechdemon</a>
Version: v1.2.5
*/
global $recastVersion;
$recastVersion = "1.2.5";

require_once plugin_dir_path(__FILE__) . 'tools.php';
require_once plugin_dir_path(__FILE__) . 'settings.php';

require_once plugin_dir_path(__FILE__) . 'includes/actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/buttons.php';
require_once plugin_dir_path(__FILE__) . 'includes/filters.php';
require_once plugin_dir_path(__FILE__) . 'includes/queries.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/listings.php';
require_once plugin_dir_path(__FILE__) . 'includes/offers.php';

require_once plugin_dir_path(__FILE__) . 'email/register_emails.php';

function Recast_enqueue_scripts() {
	global $recastVersion;

	wp_enqueue_script( 'recast_ajax', plugins_url() . '/recast/includes/recast_ajax.js', array(), $recastVersion, true );

	/* Also do CSS, which is Preregistered */
	// wp_enqueue_style( 'Recast' );
	
	// $current_user = wp_get_current_user();
	
	$data_to_pass = array(
		/* variables to pass to JS... */ 
		'url' => admin_url( 'admin-ajax.php' ),
		'user_id' => get_current_user_id(),
		// 'user_login' => $current_user->user_login,
		// 'MOT' => get_field( 'minimum_offer_total', 'option' ),
		// 'nonce'    => wp_create_nonce(  ),
		// 'message'  => __( 'Hello from PHP!', 'text-domain' ),
	);
	wp_localize_script( 'recast_ajax', 'Recast', $data_to_pass );
} add_action( 'wp_enqueue_scripts', 'Recast_enqueue_scripts' );
 add_action( 'dokan_enqueue_scripts', 'Recast_enqueue_scripts' );
 
function Recast_register_styles() {
	global $recastVersion;
	
	wp_register_style( 'Recast', plugins_url().'/recast/includes/recast.css', array(), $recastVersion, 'all' );
} add_action( 'init', 'Recast_register_styles' );
add_action( 'dokan_enqueue_scripts', 'Recast_register_styles' );