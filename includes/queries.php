<?php

function custom_query_shop( $query ) {
	/* WARNING */
	/* DO NOT DELETE */
	/* "/Shop/" page query. */
	/* */
	
	$query->set( 'post_type', 'product' );
	$query->set( 'post_status', 'publish' );

	$query->set( 'meta_query', '_stock_status' );
	$query->set( 'meta_value', 'instock' );
	
	// $query->set( 'post_category', $_POST['acf'][''] );
} add_action( 'elementor/query/shop' , 'custom_query_shop'  ); 

function CastBack_Queries_addFilterButtons() {
	ob_start();
	
		/* Post Status */
	echo '<div style="margin-bottom: 1.25rem;">';
		if( isset( $_GET['listing_status'] ) ) {
			foreach( [ 'publish', 'draft', 'all' ] as $var ) {
				if( $_GET['listing_status'] == $var ) { $active['listing_status'][$var] = ' active'; }
			}
		} else { $active['listing_status']['publish'] = ' active'; }
		
		echo '<a class="castback-button'.$active['listing_status']['publish'].'" href="'.esc_url_raw( add_query_arg( 'listing_status', 'publish', get_the_permalink() ) ).'">Active</a>';
		echo '<a class="castback-button'.$active['listing_status']['draft'].'" href="'.esc_url_raw( add_query_arg( 'listing_status', 'draft', get_the_permalink() ) ).'">Hidden</a>';
		echo '<a class="castback-button'.$active['listing_status']['all'].'" href="'.esc_url_raw( add_query_arg( 'listing_status', 'all', get_the_permalink() ) ).'">Show All</a>';
	echo '</div>';
	
	/* Stock Status */
	echo '<div style="margin-bottom: 1.25rem;">';
		if( isset( $_GET['stock_status'] ) ) {
			foreach( [ 'instock', 'outofstock' ] as $var ) {
				if( $_GET['stock_status'] == $var ) { $active['stock_status'][$var] = ' active'; }
			}
		} else { $active['stock_status']['instock'] = ' active'; }
		
		echo '<a class="castback-button'.$active['stock_status']['instock'].'" href="'.esc_url_raw( add_query_arg( 'stock_status', 'instock', get_the_permalink() ) ).'">Unsold</a>';
		echo '<a class="castback-button'.$active['stock_status']['outofstock'].'" href="'.esc_url_raw( add_query_arg( 'stock_status', 'outofstock', get_the_permalink() ) ).'">Sold</a>';
		echo '<a class="castback-button'.$active['stock_status']['all'].'" href="'.esc_url_raw( add_query_arg( 'stock_status', 'all', get_the_permalink() ) ).'">Show All</a>';
	echo '</div>';
	
	return ob_get_clean();
}
function CastBack_Queries_processFilters( $args ) {
	
	// remove_query_arg( 'listing_id' );
	// wp_safe_redirect( esc_url_raw( add_query_arg( 'listing_id', $listing_id, get_site_url(). '/selling/listings/' ) ) );
	// wp_safe_redirect( esc_url_raw( add_query_arg( 'listing_status', $listing_id, get_site_url(). '/selling/listings/' ) ) );
	
	if( isset( $_GET['listing_status'] ) ) { $args['post_status'] = $_GET['listing_status']; }
	// else { $args['post_status'] = 'instock'; }
	if( $args['post_status'] == 'all' ) { $args['post_status'] = null; }
	
	if( isset( $_GET['stock_status'] ) ) { $args['stock_status'] = $_GET['stock_status']; }
	else { $args['stock_status'] = 'instock'; }
	if( $args['stock_status'] == 'all' ) { $args['stock_status'] = null; }

	
	return $args;
}

/* Buying/Selling Subpages */
// function custom_query_recent( $query ) {
	// $query->set( 'post_type', 'product' );
	// $query->set( 'posts_per_page', '-1' );
	
	// $query->set( 'meta_key', 'post_status' );
	// $query->set( 'meta_value', 'publish' );

	// $userid = wp_get_current_user();
	// $query->set( 'author__not_in', $userid->ID );

// } add_action( 'elementor/query/recent' , 'custom_query_recent'  ); 
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
function CastBack_Query_MyOffers( $query ) {
	/* v0.5 - 9-26-2025 */
	/* I think this is the only querry that is "doing" anything, but is unused? Confirm, then remove this whole file.... */
	/* v0.5 - 9-26-2025 */
	
	
	
	// $query->set( 'post_type', 'shop_order' );
	// $query->set( 'post_status', 'any' );
	
	if( 1==1 ) { $query->set( 'author', get_current_user_id() ); }
	else { 	$query->set( 'author', '-123' ); }
	
	// $query->set( 'post_status', 'wc-processing' ); 
	// $query->set( 'posts_per_page', '-1' );
	$query->set( 'posts_per_page', '5' );
	
	// echo json_encode($query);
} add_action( 'elementor/query/woocommerce_orders' , 'CastBack_Query_MyOffers'  ); 
function CastBack_Query_MyOrders( $query ) {
	
	// $query->set( 'post_type', 'shop_order' );
	// $query->set( 'posts_per_page', '-1' );
} add_action( 'elementor/query/orders' , 'CastBack_Query_MyOrders'  ); 

// function custom_query_mylistings( $query ) {
	// $query->set( 'posts_per_page', -1 );
	// $query->set( 'post_type', 'product' );
	

	// $userid = wp_get_current_user();
	// $query->set( 'author', $userid->ID );
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
// }
//This is breaking stuff. Deleted 9/16/25 for Stripe API Testing
// if( !$_GET['listing_id'] ) { add_action( 'elementor/query/mylistings' , 'custom_query_mylistings'  ); }