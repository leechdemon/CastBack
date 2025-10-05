<?php
/*
Plugin Name: CastBack
Description: A Wordpress plugin to manage Listings, Offers, and other CastBack tools. Creates Shortcodes for use with Elementor.
Author: Leechdemon
Version: 0.5.3
*/
global $castbackVersion;
$castbackVersion = "0.5.3";

require_once plugin_dir_path(__FILE__) . 'tools.php';

require_once plugin_dir_path(__FILE__) . 'includes/actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/buttons.php';
require_once plugin_dir_path(__FILE__) . 'includes/filters.php';

	/* v0.5 - 9-26-2025 */
		require_once plugin_dir_path(__FILE__) . 'includes/queries.php';
	/* Remove ?? */
	
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/listings.php';
require_once plugin_dir_path(__FILE__) . 'includes/offers.php';

function CastBack_enqueue_scripts() {
	global $castbackVersion;

	wp_enqueue_script( 'castback_ajax', plugins_url() . '/castback-plugin/includes/castback_ajax.js', array(), $castbackVersion, true );

	/* Also do CSS, which is Preregistered */
	// wp_enqueue_style( 'CastBack' );
	
	$data_to_pass = array(
		/* variables to pass to JS... */ 
		'url' => admin_url( 'admin-ajax.php' ),
		'user_id' => get_current_user_id(),
		// 'nonce'    => wp_create_nonce(  ),
		// 'message'  => __( 'Hello from PHP!', 'text-domain' ),
	);
	wp_localize_script( 'castback_ajax', 'CastBack', $data_to_pass );
} add_action( 'wp_enqueue_scripts', 'CastBack_enqueue_scripts' );
function CastBack_register_styles() {
	global $castbackVersion;
	wp_register_style( 'CastBack', plugins_url().'/castback-plugin/includes/castback.css', array(), $castbackVersion, 'all' );
} add_action( 'init', 'CastBack_register_styles' );