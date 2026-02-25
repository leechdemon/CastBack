<?php
/*
Plugin Name: CastBack
Description: A Wordpress plugin to manage Listings, Offers, and other CastBack tools. Creates Shortcodes for use with Elementor.
Author: <a href="https://www.leechdemon.com" target="_blank">Leechdemon</a>
Version: v1.0.9.0211
*/
global $castbackVersion;
$castbackVersion = "1.0.9.0211";

require_once plugin_dir_path(__FILE__) . 'tools.php';

require_once plugin_dir_path(__FILE__) . 'includes/actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/buttons.php';
require_once plugin_dir_path(__FILE__) . 'includes/filters.php';
require_once plugin_dir_path(__FILE__) . 'includes/queries.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/listings.php';
require_once plugin_dir_path(__FILE__) . 'includes/offers.php';

require_once plugin_dir_path(__FILE__) . 'email/register_emails.php';

function CastBack_enqueue_scripts() {
	global $castbackVersion;

	wp_enqueue_script( 'castback_ajax', plugins_url() . '/castback/includes/castback_ajax.js', array(), $castbackVersion, true );

	/* Also do CSS, which is Preregistered */
	// wp_enqueue_style( 'CastBack' );
	
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
	wp_localize_script( 'castback_ajax', 'CastBack', $data_to_pass );
} add_action( 'wp_enqueue_scripts', 'CastBack_enqueue_scripts' );
 add_action( 'dokan_enqueue_scripts', 'CastBack_enqueue_scripts' );
 
function CastBack_register_styles() {
	global $castbackVersion;
	
	wp_register_style( 'CastBack', plugins_url().'/castback/includes/castback.css', array(), $castbackVersion, 'all' );
} add_action( 'init', 'CastBack_register_styles' );
add_action( 'dokan_enqueue_scripts', 'CastBack_register_styles' );