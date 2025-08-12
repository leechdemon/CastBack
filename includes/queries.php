<?php

function custom_query_shop( $query ) {
	$query->set( 'post_type', 'product' );
	$query->set( 'posts_per_page', '-1' );
	
	$query->set( 'meta_key', 'post_status' );
	$query->set( 'meta_value', 'publish' );
	
	// $query->set( 'post_category', $_POST['acf'][''] );
} add_action( 'elementor/query/shop' , 'custom_query_shop'  ); 

/* Buying/Selling Subpages */
function custom_query_recent( $query ) {
	$query->set( 'post_type', 'product' );
	$query->set( 'posts_per_page', '-1' );
	
	$query->set( 'meta_key', 'post_status' );
	$query->set( 'meta_value', 'publish' );

	$userid = wp_get_current_user();
	$query->set( 'author__not_in', $userid->ID );

} add_action( 'elementor/query/recent' , 'custom_query_recent'  ); 
// function custom_query_purchases( $query ) {
	// $args = array(
		// 'status' => 'wc-completed', // Get completed orders
		// 'limit'  => 10,           // Retrieve up to 10 orders
		// 'orderby' => 'date',      // Order by date
		// 'order'  => 'DESC',  
	// ) );

	 // $orders = wc_get_orders( $args );
	
	// $query->set( 'post_type', 'shop_order' );
	// $query->set( 'post_status', 'wc-completed' ); 
	// $query->set( 'post_status', 'wc-processing' ); 

	// $query->set( 'orderby', 'date' );
	// $query->set( 'order', 'DESC' );
	// $query->set( 'posts_per_page', '-1' );
	
	// $query->set( 'meta_key', 'post_status' );
	// $query->set( 'meta_value', 'wc-processing' );

	// $userid = wp_get_current_user();
	// $query->set( 'author__not_in', $userid->ID );

// } 

// add_action( 'elementor/query/purchases' , 'custom_query_purchases'  ); 
// function custom_query_watchlist( $query ) {
	// $query->set( 'post_type', 'product' );
	// $query->set( 'posts_per_page', '1' );
// }
function custom_query_offers( $query ) {
	$query->set( 'post_type', 'shop_order' );
	$query->set( 'posts_per_page', '1' );
} add_action( 'elementor/query/offers' , 'custom_query_offers'  ); 

// function custom_query_new( $query ) {
	
// }
function custom_query_drafts( $query ) {
	$query->set( 'posts_per_page', -1 );
	$query->set( 'post_type', 'product' );
	$userid = wp_get_current_user();
	$query->set( 'author', $userid->ID );
	
	$query->set( 'meta_key', 'post_status' );
	$query->set( 'meta_value', 'draft' );
} add_action( 'elementor/query/drafts' , 'custom_query_drafts'  ); 
function custom_query_mylistings( $query ) {
	$query->set( 'posts_per_page', -1 );
	$query->set( 'post_type', 'product' );
	$userid = wp_get_current_user();
	$query->set( 'author', $userid->ID );
	
	$query->set( 'meta_key', 'post_status' );
	$query->set( 'meta_value', 'publish' );

	$userid = wp_get_current_user();
	$query->set( 'author', $userid->ID );
	// $query->set( 'posts_per_page', 2 );
} add_action( 'elementor/query/mylistings' , 'custom_query_mylistings'  ); 
// function custom_query_payments( $query ) {
	// $query->set( 'post_type', 'product' );
	// $query->set( 'posts_per_page', '1' );
// }
// function custom_query_shipping( $query ) {
	// $query->set( 'post_type', 'product' );
	// $query->set( 'posts_per_page', '1' );
// }
// Assign the Subpage
// if( isset($_GET['subpage']) ) {
	
	// add_action( 'elementor/query/' .$_GET['subpage'] , 'custom_query_' .$_GET['subpage']  ); 
// }
/* end Buying/Selling Subpages */
