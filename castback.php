<?php
/*
Plugin Name: CastBack
Description: A Wordpress plugin to manage Listings, Offers, and other CastBack tools. Creates Shortcodes for use with Elementor.
Author: Leechdemon
Version: 0.4.2
*/

require_once plugin_dir_path(__FILE__) . 'tools.php';

require_once plugin_dir_path(__FILE__) . 'includes/actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/filters.php';

	/* v0.5 - 9-26-2025 */
		require_once plugin_dir_path(__FILE__) . 'includes/queries.php';
	/* Remove ?? */
	
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/listings.php';
require_once plugin_dir_path(__FILE__) . 'includes/offers.php';

global $castbackVersion;
$castbackVersion = "0.4.2";

function CastBack_enqueue_scripts() {
	global $castbackVersion;

	wp_enqueue_script( 'castback_ajax', plugins_url() . '/castback-plugin/includes/castback_ajax.js', array(), $castbackVersion, true );

	/* Also do CSS, which is Preregistered */
	// wp_enqueue_style( 'CastBack' );
	
	$data_to_pass = array(
			'url' => admin_url( 'admin-ajax.php' ),
			// 'nonce'    => wp_create_nonce(  ),
			// 'message'  => __( 'Hello from PHP!', 'text-domain' ),
	);
	wp_localize_script( 'castback_ajax', 'castback_object', $data_to_pass );
} add_action( 'wp_enqueue_scripts', 'CastBack_enqueue_scripts' );
function CastBack_register_styles() {
	global $castbackVersion;
	wp_register_style( 'CastBack', plugins_url().'/castback-plugin/includes/castback.css', array(), $castbackVersion, 'all' );
	// wp_register_style( 'CastBack-MyOffers', plugins_url().'/castback-plugin/includes/castback.css', array(), $castbackVersion, 'all' );
	// wp_register_style( 'CastBack-MyOffers2', plugins_url().'/castback-plugin/includes/castback.css', array(), $castbackVersion, 'all' );
} add_action( 'init', 'CastBack_register_styles' );
		
// function enqueue_elementor_assets_for_shortcode() {
    // if ( is_page_template('your-template-name.php') ) {
        // Enqueue Elementor's frontend styles
        // wp_enqueue_style( 'elementor-frontend' );
        // wp_enqueue_style( 'elementor-post-' . get_the_ID() ); // Enqueue CSS for the current post/page
    // }
// }
/* Unused? 9/17 */
// add_action( 'wp_enqueue_scripts', 'enqueue_elementor_assets_for_shortcode' );