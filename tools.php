<?php 

if(!function_exists('Test')) { // PHP script to dump variable into JavaScript console on front-end.
	function Test($output, $with_script_tags = false) {
		$js_code = json_encode($output, JSON_HEX_TAG);
		if ($with_script_tags===false) { echo '<script>console.log("Test: " + ' .json_encode($js_code). ');</script>'; }
		else { echo "<pre>" .var_dump($js_code). "</pre>"; }
	 }
}
function CastBack_CSS() {
	echo '<style>
	#castback-order { display: inline-block; width: fit-content; float: left; margin-bottom: 1rem; }
	#castback-sidebar { width: 35%; display: inline-block; float: right; margin-bottom: 1rem; }
	#castback-order .acf-form-submit { margin: 0 1rem 1rem 0; }
	
	.castback-order-listing { max-width: 575px; float: left; padding: 1rem; padding: 1rem; }
	.castback-order-details { width: fit-content; }
	a.castback-order-refresh { display: block; float: right; width: auto; padding-left: 0.5rem; font-size: small; }

	
	.castback-order-listing .listing { background-color: #DDDDDD;  }
	.castback-order-listing .listing-pricebox { flex-direction: row !important; padding: 0; }
	.castback-order-listing .listing-tags { flex-direction: row !important; padding: 0; }
	.castback-order-listing .listing-info { display: block; }
	.castback-order-listing .listing-buttons { display: none; }
	.castback-order-listing .listing-details { background-color: white; }
	.e-con-full .e-flex:has(> .listing-details) { flex-direction: row; }
	.castback-order-listing .listing-details .e-con-inner { display: block; }
	
	.order_history { display: flex; flex-direction: column; border: solid 1px black; float: right; margin-top: 1rem; }
	.order_history_item  { width: 100%; background-color: #dddddd; float: left; padding: 0.5rem; }
	.order_history_item.customer { background-color: #d4efef; }
	.order_history_item.seller { background-color: #aad3d3; }
	.order_history_subitem { width: 100%; float: left; }
	.order_history_subitem.date { font-size: small; }
	// .order_history_subitem.offer_expired { text-decoration: line-through; }
	.order_history_subitem.offer_expired:after { content: " (expired)"; color: red; font-size: smaller; }
	.customer .order_history_subitem { text-align: left; }
	.seller .order_history_subitem { text-align: right; }
	
	.acf_offers, .acf_messages, .acf_shipping, .acf_dispute { width: fit-content; clear: both; float: left; margin: 1rem 0; }
	.acf-field-68a0f1c63178a, .acf-field-68af2cd79c27b, .acf-field-68b1c614b344d { display: none; }
	
	.acf_offers .acf-field-68a0f1c63178a { display: block; width: 100% !important; }
	.acf_messages .acf-field-68af2cd79c27b { display: block; width: 100% !important; }
	.acf_shipping .acf-field-68b1c614b344d { display: block; width: 100% !important; }	
</style>';
} add_action( 'wp_head', 'CastBack_CSS', 90 );
function castback_admin_edit_listing( $wp_admin_bar ) {
		if( $_GET['listing_id'] ) {
			$url = get_site_URL() . '/wp-admin/post.php?post='.$_GET['listing_id'].'&action=edit';
			$args = array(
					'id'    => 'edit-listing', // Unique ID for your link
					'title' => 'Edit Listing', // Text displayed in the admin bar
					'href'  => $url,
					'meta'  => array(
							'class' => 'my-custom-link-class', // Optional: Add a custom CSS class
							'title' => 'Links directly to "Edit Product" in Admin', // Optional: Add a tooltip
					),
			);
			$wp_admin_bar->add_node( $args );
		}
} add_action( 'admin_bar_menu', 'castback_admin_edit_listing', 90 );
function castback_admin_edit_order( $wp_admin_bar ) {
		if( $_GET['order_id'] ) {
			$url = get_site_URL() . '/wp-admin/admin.php?page=wc-orders&action=edit&id='.$_GET['order_id'].'&action=edit';
			$args = array(
					'id'    => 'edit-order', // Unique ID for your link
					'title' => 'Edit Order', // Text displayed in the admin bar
					'href'  => $url,
					'meta'  => array(
							'class' => 'my-custom-link-class', // Optional: Add a custom CSS class
							'title' => 'Links directly to "Edit Order" in Admin', // Optional: Add a tooltip
					),
			);
			$wp_admin_bar->add_node( $args );
		}
} add_action( 'admin_bar_menu', 'castback_admin_edit_order', 90 );

function castback_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( is_a( $user, 'WP_User' ) && $user->has_cap( 'administrator' ) ) {
				return admin_url(); // Redirect to the admin dashboard
		} else {
				$url = get_site_URL() . '/my-account';
				return $url; // Redirect to the homepage for other users
		}
} add_filter( 'login_redirect', 'castback_login_redirect', 10, 3 );

function castback_cron_noOffers( $AJAX = true ) {
	
	$args = array(
		'status' => 'wc-checkout-draft', // Get completed orders
		'limit'  => -1,           // Retrieve up to 10 orders
		'orderby' => 'date',      // Order by date
		'order'  => 'DESC',  
		// 'customer_id'  => get_current_user_id(),  
		// 'meta_query' => array(
			// array(
					// 'key'     => 'offers_0_offer_amount',
					// 'value'   => 'example_value',
					// 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
			// ),
		// ),
	);

	$orders = wc_get_orders( $args );
	foreach( $orders as $order ) {
		
		$order_id = $order->get_id();				
		
		$offers = get_field( 'offers', $order_id );
		if( $offers) {
			if( end($offers)['offer_expired_date'] ) { $order_date = end($offers)['offer_expired_date']; }
		}
		else { $order_date = $order->get_date_created()->format('F j, Y g:i a'); }

		if( $order_date ) {
			// $cron_no_offer_expired_date_days = get_field( 'cron_no_offer_expired_date_days', 'option' );
			// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date_days.' days', strtotime( $offer['offer_date'] ) ) );

			$cron_no_dispute_completed_date_days = 5;
			$offer_expired_date = date('F j, Y g:i a',
					strtotime( '+'.$cron_no_dispute_completed_date_days.' minutes',
							strtotime( $order_date )
					)
			);
			
			$offer_expired = strtotime( '+5 minutes',
				strtotime( $order_date )
			);
			$currentTime = strtotime( date('F j, Y g:i a') );
			
			if( $currentTime > $offer_expired ) {
					CastBack_action_complete_order( $order_id, $AJAX );
			}
		}
		
	}
	
	// if($AJAX) { wp_die(); }
} add_action( 'castback_cron', 'castback_cron_noOffers' );
function castback_cron_noExpiredDate( $AJAX = false ) {
	$args = array(
		'status' => 'wc-checkout-draft', // Get completed orders
		'limit'  => -1,           // Retrieve up to 10 orders
		'orderby' => 'date',      // Order by date
		'order'  => 'DESC',  
		// 'customer_id'  => get_current_user_id(),  
		// 'meta_query' => array(
			// array(
					// 'key'     => 'offers_0_offer_amount',
					// 'value'   => 'example_value',
					// 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
			// ),
		// ),
	);

	$orders = wc_get_orders( $args );
	foreach( $orders as $order ) {
		$order_id = $order->get_id();				
		$offers = get_field( 'offers', $order_id );
    	foreach( $offers as $key => $offer ) {
				if( !$offer['offer_expired_date'] ) {
					// $cron_no_offer_expired_date_days = get_field( 'cron_no_offer_expired_date_days', 'option' );
					// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date_days.' days', strtotime( $offer['offer_date'] ) ) );
					$cron_no_offer_expired_date_days = 5;
					$offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date_days.' minutes', strtotime( $offer['offer_date'] ) ) );
					
					
					$offer_expired = strtotime( '+5 minutes', strtotime( $offer['offer_date'] ) );
					$currentTime = strtotime( date('F j, Y g:i a') );
					if( $currentTime > $offer_expired ) { castback_offer_expiration( $order_id, $key ); }
				}
    	}
	}
	
	// if($AJAX) { wp_die(); }
} add_action( 'castback_cron', 'castback_cron_noExpiredDate' );
function castback_cron_noShippedDate( $AJAX = false ) {
	
	$args = array(
		'status' => 'processing', // Get completed orders
		'limit'  => -1,           // Retrieve up to 10 orders
		'orderby' => 'date',      // Order by date
		'order'  => 'DESC',  
		// 'customer_id'  => get_current_user_id(),  
		// 'meta_query' => array(
			// array(
					// 'key'     => 'offers_0_offer_amount',
					// 'value'   => 'example_value',
					// 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
			// ),
		// ),
	);

	$orders = wc_get_orders( $args );
	foreach( $orders as $order ) {
		$order_id = $order->get_id();				
		if( get_field( 'shipped_date', $order_id ) == '' ) {
			
    		// $cron_no_offer_expired_date_days = get_field( 'cron_no_offer_expired_date_days', 'option' );
	    	// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date_days.' days', strtotime( $offer['offer_date'] ) ) );

				$cron_no_shipping_refund_date_days = 5;
    		$offer_expired_date = date('F j, Y g:i a',
    		    strtotime( '+'.$cron_no_shipping_refund_date_days.' minutes',
        		    strtotime( get_field( 'payment_date', $order_id ) )
    		    )
    		);
    		
    		$offer_expired = strtotime( '+5 minutes', strtotime( get_field( 'payment_date', $order_id ) ) );
    		$currentTime = strtotime( date('F j, Y g:i a') );
    		
    		if( $currentTime > $offer_expired ) {
    		    update_field( 'disputed_date', $currentTime, $order_id );
    		}
		}
	}
	
	// if($AJAX) { wp_die(); }
} add_action( 'castback_cron', 'castback_cron_noShippedDate' );
function castback_cron_noCompletedDate( $AJAX = false ) {
	
	$args = array(
		'status' => 'processing', // Get completed orders
		'limit'  => -1,           // Retrieve up to 10 orders
		'orderby' => 'date',      // Order by date
		'order'  => 'DESC',  
		// 'customer_id'  => get_current_user_id(),  
		// 'meta_query' => array(
			// array(
					// 'key'     => 'offers_0_offer_amount',
					// 'value'   => 'example_value',
					// 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
			// ),
		// ),
	);

	$orders = wc_get_orders( $args );
	foreach( $orders as $order ) {
		$order_id = $order->get_id();				
		if( get_field( 'completed_date', $order_id ) == '' && get_field( 'disputed_date', $order_id ) == '' ) {
			
    		// $cron_no_offer_expired_date_days = get_field( 'cron_no_offer_expired_date_days', 'option' );
	    	// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date_days.' days', strtotime( $offer['offer_date'] ) ) );

				$cron_no_dispute_completed_date_days = 5;
    		$offer_expired_date = date('F j, Y g:i a',
    		    strtotime( '+'.$cron_no_dispute_completed_date_days.' minutes',
        		    strtotime( get_field( 'shipped_date', $order_id ) )
    		    )
    		);
    		
    		$offer_expired = strtotime( '+5 minutes', strtotime( get_field( 'shipped_date', $order_id ) ) );
    		$currentTime = strtotime( date('F j, Y g:i a') );
    		
    		if( $currentTime > $offer_expired ) {
    		    CastBack_action_complete_order( $order_id, $AJAX );
    		}
		}
	}
	
	// if($AJAX) { wp_die(); }
} add_action( 'castback_cron', 'castback_cron_noCompletedDate' );
