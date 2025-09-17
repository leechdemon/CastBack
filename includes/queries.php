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
function custom_query_mylistings( $query ) {
	$query->set( 'posts_per_page', -1 );
	$query->set( 'post_type', 'product' );
	

	$userid = wp_get_current_user();
	$query->set( 'author', $userid->ID );
	// Add your meta query conditions
	// if ( ! $query->get( 'meta_query' ) ) { $query->set( 'meta_query', array() ); }
	// $meta_query = $query->get( 'meta_query' );
	// $meta_query[] = [
			// 'key'     => 'seller_id', // Replace with your ACF field's name
			// 'key'     => 'field_68c043d8de002', // Replace with your ACF field's name
			// 'value'   => wp_get_current_user(),   // Replace with the value to filter by
			// 'compare' => '=',                    // Comparison operator (e.g., '=', 'LIKE', 'EXISTS')
	// ];
	// $query->set( 'meta_query', $meta_query );
	// $query->set( 'meta_key', 'seller_id' );
	// $query->set( 'meta_value', wp_get_current_user() );
	
	// $user_id = wp_get_current_user();
	// if( $user_id == 0 ) { $user_id = 'anonymous'; }
	// $query->set( 'meta_key', 'seller_id' );
	// $query->set( 'meta_value', $user_id );
	
	// $query->set( 'meta_key', 'post_status' );
	// $query->set( 'meta_value', array('draft', 'publish') );
}
//This is breaking stuff. Deleted 9/16/25 for Stripe API Testing
// if( !$_GET['listing_id'] ) { add_action( 'elementor/query/mylistings' , 'custom_query_mylistings'  ); }