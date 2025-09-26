<?php 

if(!function_exists('Test')) { // PHP script to dump variable into JavaScript console on front-end.
	function Test($output, $with_script_tags = false) {
		$js_code = json_encode($output, JSON_HEX_TAG);
		if ($with_script_tags===false) { echo '<script>console.log("Test: " + ' .json_encode($js_code). ');</script>'; }
		else { echo "<pre>" .var_dump($js_code). "</pre>"; }
	 }
}
function castback_admin_edit_listing( $wp_admin_bar ) {
	if( !is_admin() ) {
		$listing_id = '';
		if( isset($_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
		if( !$listing_id && isset($_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
				
		if( $listing_id ) {
				$url = get_site_URL() . '/wp-admin/post.php?post='.$listing_id.'&action=edit';
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
	}
}
add_action( 'admin_bar_menu', 'castback_admin_edit_listing', 90 );
function castback_admin_edit_order( $wp_admin_bar ) {
	if( !is_admin() ) {
		$order_id = '';
		if( isset($_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( !$order_id && isset($_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
		
		if( $order_id ) {
			$url = get_site_URL() . '/wp-admin/admin.php?page=wc-orders&action=edit&id='.$order_id.'&action=edit';
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
	}
} 
add_action( 'admin_bar_menu', 'castback_admin_edit_order', 90 );

// function castback_login_redirect( $redirect, $user ) {
  // Get the first of all the roles assigned to the user
  // $role = $user->roles[0];
  // $myaccount = '/my-account/';
	
  // if( $role == '123123123' ) { $redirect = $myaccount; }
  // if( $role == 'administrator' ) { $redirect = $myaccount; }
	// elseif ( $role == 'customer' || $role == 'vendor' || $role == 'dc_vendor' ) { $redirect = $myaccount; }
	// $redirect = '';
	// echo $redirect;
	// $redirect = '123';
  // return $redirect;
// } add_filter( 'woocommerce_login_redirect', 'castback_login_redirect', 10, 2 ); 

function CastBack_MyNotifications( $page = null, $location = null ) {
	return;
}

function castback_cron_noOffers( $AJAX = true ) {
	if( get_field( 'run_automations', 'option' ) ) { 
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
				// $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
				// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );

				$cron_no_dispute_completed_date = 5;
				$offer_expired_date = date('F j, Y g:i a',
						strtotime( '+'.$cron_no_dispute_completed_date.' minutes',
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
	}
}
// add_action( 'castback_cron', 'castback_cron_noOffers' );
function castback_cron_noExpiredDate( $AJAX = false ) {
	if( get_field( 'run_automations', 'option' ) ) { 
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
						// $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
						// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );
						$cron_no_offer_expired_date = 5;
						$offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' minutes', strtotime( $offer['offer_date'] ) ) );
						
						
						$offer_expired = strtotime( '+5 minutes', strtotime( $offer['offer_date'] ) );
						$currentTime = strtotime( date('F j, Y g:i a') );
						if( $currentTime > $offer_expired ) { CastBack_action_expire_offer( $order_id, $key ); }
					}
				}
		}
		
		// if($AJAX) { wp_die(); }
	}
}
// add_action( 'castback_cron', 'castback_cron_noExpiredDate' );
function castback_cron_noShippedDate( $AJAX = false ) {
	if( get_field( 'run_automations', 'option' ) ) { 
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
				
					// $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
					// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );

					$cron_no_shipping_refund_date = 5;
					$offer_expired_date = date('F j, Y g:i a',
							strtotime( '+'.$cron_no_shipping_refund_date.' minutes',
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
	}
}
// add_action( 'castback_cron', 'castback_cron_noShippedDate' );
function castback_cron_noCompletedDate( $AJAX = false ) {
	if( get_field( 'run_automations', 'option' ) ) { 
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
				
					// $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
					// $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );

					$cron_no_dispute_completed_date = 5;
					$offer_expired_date = date('F j, Y g:i a',
							strtotime( '+'.$cron_no_dispute_completed_date.' minutes',
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
	}
}
// add_action( 'castback_cron', 'castback_cron_noCompletedDate' );