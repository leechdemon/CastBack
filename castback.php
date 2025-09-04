<?php
/*
Plugin Name: CastBack
Description: A Wordpress plugin to manage Listings, Offers, and other CastBack tools. Creates Shortcodes for use with Elementor.
Author: Leechdemon
Version: 0.3.3
*/

require_once plugin_dir_path(__FILE__) . 'tools.php';

require_once plugin_dir_path(__FILE__) . 'includes/queries.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/listings.php';
require_once plugin_dir_path(__FILE__) . 'includes/offers.php';

function CastBack_enqueue_scripts() {
	// $version = rand(0,9999999999);
	$version = "0001";
	wp_enqueue_script( 'castback_ajax', plugins_url() . '/castback-plugin/includes/castback_ajax.js', array(), $version, true );

	$data_to_pass = array(
			'url' => admin_url( 'admin-ajax.php' ),
			// 'nonce'    => wp_create_nonce(  ),
			// 'message'  => __( 'Hello from PHP!', 'text-domain' ),
	);
	wp_localize_script( 'castback_ajax', 'castback_object', $data_to_pass );
} add_action( 'wp_enqueue_scripts', 'CastBack_enqueue_scripts' );