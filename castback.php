<?php
/*
Plugin Name: CastBack
Description: A Wordpress plugin to manage Listings, Offers, and other CastBack tools. Creates Shortcodes for use with Elementor.
Author: Leechdemon
Version: 0.4.1.0917
*/

require_once plugin_dir_path(__FILE__) . 'tools.php';

require_once plugin_dir_path(__FILE__) . 'includes/queries.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/listings.php';
require_once plugin_dir_path(__FILE__) . 'includes/offers.php';

function CastBack_enqueue_scripts() {
	// $version = rand(0,9999999999);
	$version = "0.4.1";
	wp_enqueue_script( 'castback_ajax', plugins_url() . '/castback-plugin/includes/castback_ajax.js', array(), $version, true );
	wp_enqueue_style( 'castback', plugins_url() . '/castback-plugin/includes/castback.css', array(), $version, 'all' );
	
	$data_to_pass = array(
			'url' => admin_url( 'admin-ajax.php' ),
			// 'nonce'    => wp_create_nonce(  ),
			// 'message'  => __( 'Hello from PHP!', 'text-domain' ),
	);
	wp_localize_script( 'castback_ajax', 'castback_object', $data_to_pass );
	// wp_localize_script( 'castback', 'castback_css', array() );
} add_action( 'wp_enqueue_scripts', 'CastBack_enqueue_scripts' );

function enqueue_elementor_assets_for_shortcode() {
    // if ( is_page_template('your-template-name.php') ) {
        // Enqueue Elementor's frontend styles
        wp_enqueue_style( 'elementor-frontend' );
        wp_enqueue_style( 'elementor-post-' . get_the_ID() ); // Enqueue CSS for the current post/page
    // }
}
/* Unused? 9/17
// add_action( 'wp_enqueue_scripts', 'enqueue_elementor_assets_for_shortcode' );