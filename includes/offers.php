<?php
function CastBack_Offers( $method, $page = null, $AJAX = false ) {
	$output = '';
	if( $method == 'MyOffers' ) {
		$title = 'My Offers';
		$title_url = '/buying/offers';
		$orderLimit = -1;
		$buyerOrSeller = 'customer_id';
		$orderStatus = array( 'checkout-draft', 'pending', 'processing', 'completed' );
		$offersOrders = 'offers';
	}
	if( $method == 'MyOrders' ) {
		$title = 'My Orders';
		$title_url = '/selling/my-orders';
		$orderLimit = -1;
		$buyerOrSeller = 'seller_id';
		$orderStatus = array( 'checkout-draft', 'pending', 'processing', 'completed' );
		$offersOrders = 'orders';
	}
	if( $page ) {
		if( $page == 'MyAccount' ) {
			$orderLimit = 4;
			// $buyerOrSeller = 'seller_id';
			// $orderStatus = array( 'checkout-draft', 'pending', 'processing', 'completed' );
			// $offersOrders = 'orders';
		}
	} 
	// else { $page = 'CastBack-'.$method; }
	
	$args = array(
		'status' => $orderStatus, // Get completed orders
		'limit'  => $orderLimit,           // Retrieve up to 10 orders
		'orderby' => 'date',      // Order by date
		'order'  => 'DESC',  
		'meta_query' => array(
			array(
				'key'     => $buyerOrSeller,
				'value'   => get_current_user_id(),
				'compare' => 'IN', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
			),
		),
	);
	$orders = wc_get_orders( $args );
	
	/* Draw Results */	
	if( $title_url ) { $output .= '<h3><a href="'.$title_url.'">'.$title.'</a></h3>'; }
	else { $output .= '<h3>'.$title.'</h3>'; }
	
	foreach( $orders as $key => $order ) {
		if( $key+1 == $orderLimit ) {
			$output .= '<span><a style="font-size: smaller;" href="'.$title_url.'">View More...</a></span>';
		}
		else {
			if($order) {
				$order_id = $order->get_id();
			
				$offers = get_field( 'offers', $order_id );
				// if( $offers[0]['offer_amount'] || get_field( 'customer_id', $order_id ) == get_current_user_id() ) {
				// if( $offers[0]['offer_amount'] ) {
					$orderCount++;
					$output .= '<a style="display: flex;" href="'.get_site_url().$title_url.'/?order_id='.$order_id.'">Order #' .$order_id .' - '.CastBack_orderStatusCosmetic( $order->get_status() ).'</a>';
					// AJAX "View" URL removed for v0.5 Release
					// echo '<a class="button" href="javascript:CastBack_edit_listing_button(\''.$listing_id.'\', \''.$user_id.'\', \'CastBack-MyListings\');">Edit Listing</a>';

					
					
					// echo '<div class=""><a href="javascript:CastBack_offers_draw_order_page_button('.$order_id.', \'CastBack-'.$page.'\');">.'</div>';
					// echo '<div class=""><a href="javascript:CastBack_offers_draw_order_page_button('.$order_id.', \'CastBack-'.$page.'\');">Order #' .$order_id .'</a> - '.CastBack_orderStatusCosmetic( $order->get_status() ).'</div>';
				// }
			}
		}
	}
	if( $orderCount < 1 ) {
		$output .= 'You have no '.$offersOrders.'.';
	}
	
	if($AJAX) { echo ob_get_clean(); wp_die(); }
	else {
		ob_start();
		echo $output;
		return ob_get_clean();
	}
}

function CastBack_orderStatusCosmetic( $orderStatus ) {
	$orderStatusCosmetic = $orderStatus;
	switch( $orderStatus ) {
		case 'checkout-draft':
			$orderStatusCosmetic = 'Draft';
			break;
		case 'pending':
			$orderStatusCosmetic = 'Pending Payment';
			break;
		case 'processing':
			if( get_field( 'shipped_date', $order_id ) ) {  
				$orderStatusCosmetic = 'Processing (Shipped)';
			}
			else {
				$orderStatusCosmetic = 'Processing';
			}
			break;
		case 'completed':
			$orderStatusCosmetic = 'Complete';
			break;
	}
	
	$disputedDate = get_field( 'disputed_date', $order_id );
	if( $disputedDate ) { $orderStatusCosmetic = 'Order Disputed'; }
	
	return $orderStatusCosmetic;
}
function CastBack_offers_draw_order_page( $order_id = '', $page = null, $AJAX = true ) {
	if( !$order_id ) { $order_id = $_GET['order_id']; }
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	if( !$page ) { $page = $_POST['targetDiv']; }

	ob_start();
	
	$order = wc_get_order( $order_id );	
	if( $order ) {
		/* Display Order */
		// $output = '';
		
		echo '<div id="castback-display-order" style="display: inline-block; width: 65%;">';
			echo CastBack_offers_draw_order( $order_id, $page, false );
		echo '</div>';
		
		/* Display Sidebar */
		echo '<div id="castback-sidebar" style="width: 35%; display: inline-block; float: right; margin-bottom: 1rem;">';
			echo CastBack_offers_draw_sidebar( $order_id, $page, false );
		echo '</div>';
		
		if($AJAX) { echo $output; wp_die(); } else { return $output; }
	} else {
		echo '<div>Order #'.$order_id.' does not exist.</div>';
	}
} add_action( 'wp_ajax_CastBack_offers_draw_order_page', 'CastBack_offers_draw_order_page' );
function CastBack_offers_draw_order( $order_id, $page = null, $AJAX = true ) {
	
	if( !$order_id ) { $order_id = $_POST['order_id']; }

	// ob_start();
	$output = '';
	
	$order = wc_get_order( $order_id );	
	if( $order ) {
		/* Display Order Details */
		$output .= '<h3>Order #<span id="castback_order_id" style="">'.$order_id.'</span></h3>';
		$output .= '<div class="castback-order-details">';
			$orderStatus = $order->get_status();
			$waitingOn = get_field( 'waiting_on', $order_id );
			$name = get_userdata( $waitingOn );
			$output .= '<h5 style="margin-left: 1rem;">Order Status: '.CastBack_orderStatusCosmetic( $orderStatus ).'</h5>';
			if( $name && $orderStatus != 'completed' ) { $output .= '<h5 style="margin-left: 1rem;">Waiting On: '.$name->first_name .' '.$name->last_name.'</h5>'; }
		$output .= '</div>';

		/* Display the Listing */
		$listing_id = get_field( 'listing_id', $order_id );
		$output .= CastBack_listings_draw_listing( $listing_id, '949', $AJAX );

		/* Display Buttons */
		$output .= CastBack_offers_draw_buttons( $order_id, $orderStatus, $page );
	} else {
		$output .=  "order not found.";
	}
	
	// if($order_id) { echo $output; }
	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_offers_draw_order', 'CastBack_offers_draw_order' );
function CastBack_offers_draw_buttons( $order_id, $orderStatus, $page = null  ) {
    $disputedDate = get_field( 'disputed_date', $order_id );
    $waitingOn = get_field( 'waiting_on', $order_id );
    $order = wc_get_order( $order_id );
    // if( !$page ) { $page = $_POST['targetDiv']; }
		
    if( $disputedDate == '' ) {
        
			/* Accept / Submit Offer */
    	if( $orderStatus == 'checkout-draft' && get_current_user_id() == $waitingOn ) {
				$output = '';
				$output .= '<div class="acf_offers" style="float: left; clear: both;">';
					$output .= '<input style="width: 100px;"id="castback_offer_amount" type="number" value="'.get_field( 'order_amount', $order_id ).'">';
					$output .= '<a class="button" href="javascript:CastBack_action_submit_offer_button(\''.$page.'\')">Submit Offer</a>';
					if( get_field( 'offers', $order_id ) ) { $output .= '<a class="button" href="javascript:CastBack_action_accept_offer_button(\''.$page.'\')">Accept Offer</a>'; }
				$output .= '</div>';
			}		
			/* Submit Payment */
			if( $orderStatus == 'pending' && get_current_user_id() == $waitingOn ) {
				$output .= '<div class="acf_offers" style="float: left; clear: both;">';
					$output .= '<a class="button" href="'. $order->get_checkout_payment_url() .'" target="_blank">Pay Order</a>';
				$output .= '</div>';
			}
			
    	/* Shipping */
			if( $orderStatus == 'processing' ) {
				/* Ship Order */
				if( get_current_user_id() == get_field( 'seller_id', $order_id ) ) { $displayShipping = true; }
				else if( get_field( 'shipped_date', $order_id ) ) { $displayShipping = true; }
				
				if( $displayShipping ) {
					$output .= '<div class="acf_offers" style="float: left; clear: both;">';
						$output .= '<input style="width: 100px;"id="castback_new_tracking_number" type="text">';
						$output .= '<a class="button" href="javascript:CastBack_action_add_tracking_button(\''.$page.'\')">Add Tracking Order</a>';
					$output .= '</div>';
				}
				
				/* Complete Order */
				if( get_current_user_id() == $waitingOn && get_field( 'shipped_date', $order_id ) ) {
					$output .= '<div class="acf_offers" style="float: left; clear: both;">';
						$output .= '<a class="button" href="javascript:CastBack_action_complete_order_button(\''.$page.'\')">Complete Order</a>';
					$output .= '</div>';
				}
		//	if( get_current_user_id() == get_field( 'customer_id', $order_id ) ) {
				$output .= '<div class="acf_dispute" style="float: left; clear: both;"><a class="button" href="javascript:CastBack_action_dispute_order_button(\''.$page.'\')">Dispute Order</a></div>';
			//	}
			}
    }
		
		return $output;
}
function CastBack_offers_draw_sidebar( $order_id, $page = null, $AJAX = true ) {	
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	$order = wc_get_order( $order_id );

	
	/* Display History */
	$output = '';
	$output .= '<h5 style="">Order History';	
		$output .= '<a class="castback-order-refresh" href="javascript:CastBack_offers_draw_order_page_button('.$order_id.', \''.$page.'\');" style="display: block; float: right; width: auto; padding-left: 0.5rem; font-size: small;">(Refresh)</a>';
	$output .= '</h5>';
	$output .= '<div class="order_history">';

		/* Display Offers */
		$offers = get_field( 'offers', $order_id  );
		if( $offers ) {
			foreach( $offers as $offer ) {
				if( $offer['offer_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
				else { $customerOrSeller = ' seller'; }
				
				$output .= '<div class="order_history_item'.$customerOrSeller.'" style="order: '.strtotime( $offer['offer_date'] ).';">';
					$output .= '<div class="order_history_subitem date">'. $offer['offer_date'] . '</div>';

					$name = get_userdata( $offer['offer_user_id'] );
					if( $offer['offer_expired_date'] ) { $offerExpired = ' offer_expired'; } else { $offerExpired = ''; }
					$output .= '<div class="order_history_subitem'.$offerExpired.'">'. $name->first_name .' '.$name->last_name . ' made an Offer of $'. $offer['offer_amount'].'</div>';
				$output .= '</div>'; // end order history item
			}
		}
				
		/* Display Message History */
		$messages = get_field( 'messages', $order_id  );
		if( $messages ) {
			foreach( $messages as $message ) {
				if( $message['message_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
				else { $customerOrSeller = ' seller'; }
				
				$output .= '<div class="order_history_item'.$customerOrSeller.'" style="order: '.strtotime( $message['message_date'] ).';">';
					$output .= '<div class="order_history_subitem date">'. $message['message_date'] . '</div>';

					$name = get_userdata( $message['message_user_id'] );
					$output .= '<div class="order_history_subitem">'. $name->first_name .' '.$name->last_name . ': "'.$message['message_text'].'"</div>';
				$output .= '</div>'; // end order history item
			}
		}

		/* Display Tracking History */
		$trackingLabels = get_field( 'tracking', $order_id  );
		if( $trackingLabels ) {
			foreach( $trackingLabels as $tracking ) {
				if( $tracking['tracking_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
				else { $customerOrSeller = ' seller'; }
				
				$output .= '<div class="order_history_item'.$customerOrSeller.'" style="order: '.strtotime( $tracking['tracking_date'] ).';">';
					$output .= '<div class="order_history_subitem date">'. $tracking['tracking_date'] . '</div>';

					$name = get_userdata( $tracking['tracking_user_id'] );
					$output .= '<div class="order_history_subitem">'. $name->first_name .' '.$name->last_name . ' added Tracking #<a href="'.get_site_url().'/?tracking='.$tracking['tracking_number'].'">'.$tracking['tracking_number'].'</a></div>';
				$output .= '</div>'; // end order history item
			}
		}

		/* Display Status Changes */
		$dateCreated = $order->get_date_created();
		$output .= '<div class="order_history_item customer" style="order: '.strtotime( $dateCreated ).';">';
			$output .= '<div class="order_history_subitem date">'. $dateCreated->format('F j, Y g:i a') . '</div>';
			$output .= '<div class="order_history_subitem">Order Created</div>';
		$output .= '</div>';
		$acceptedDate = get_field( 'accepted_date', $order_id );
		if( $acceptedDate ) {
			$output .= '<div class="order_history_item seller" style="order: '.strtotime( $acceptedDate ).';">';
				$output .= '<div class="order_history_subitem date">'. $acceptedDate . '</div>';
				$output .= '<div class="order_history_subitem">Offer Accepted</div>';
			$output .= '</div>';
		}
		$paymentDate = get_field( 'payment_date', $order_id );
		if( $paymentDate ) {
			$output .= '<div class="order_history_item customer" style="order: '.strtotime( $paymentDate ).';">';
				$output .= '<div class="order_history_subitem date">'. $paymentDate . '</div>';
				$output .= '<div class="order_history_subitem">Order Paid</div>';
			$output .= '</div>';
		}
		$shippedDate = get_field( 'shipped_date', $order_id );
		if( $shippedDate ) {
			$output .= '<div class="order_history_item seller" style="order: '.strtotime( $shippedDate ).';">';
				$output .= '<div class="order_history_subitem date">'. $shippedDate . '</div>';
				$output .= '<div class="order_history_subitem">Order Shipped</div>';
			$output .= '</div>';
		}
		$completedDate = get_field( 'completed_date', $order_id );
		if( $completedDate ) {
			$output .= '<div class="order_history_item customer" style="order: '.strtotime( $completedDate ).';">';
				$output .= '<div class="order_history_subitem date">'. $completedDate . '</div>';
				$output .= '<div class="order_history_subitem">Order Completed</div>';
			$output .= '</div>';
		}
		$disputedDate = get_field( 'disputed_date', $order_id );
		if( $disputedDate ) {
			$output .= '<div class="order_history_item customer" style="order: '.strtotime( $disputedDate ).';">';
				$output .= '<div class="order_history_subitem date">'. $disputedDate . '</div>';
				$output .= '<div class="order_history_subitem">Order was Disputed. CastBack support will be in touch soon...</div>';
			$output .= '</div>';
	}
	$output .= '</div>'; // end order history					

	/* Display Messaging Window */
	$output .= '<div class="acf_messages">';

		/* Send Message */
		$output .= '<input style="width: 100px;"id="castback_new_message" type="text-area">';
		$output .= '<a class="button" href="javascript:CastBack_action_send_message_button(\''.$page.'\')">Send Message</a>';
	
	$output .= '</div>';

	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_offers_draw_sidebar', 'CastBack_offers_draw_sidebar' );


function castback_offer_expiration( $order_id ) {
	$offers = get_field( 'offers', $order_id );
	if($offers) {
		$row = array(
				'offer_expired_date'	=>	date( 'F j, Y g:i a' ),
		);
		update_row( 'offers', count($offers), $row, $order_id );
	}
}

/* Actions / Filters */
function so_payment_complete( $order_id ){
    // $order = wc_get_order( $order_id );
		update_field( 'waiting_on', get_field( 'seller_id', $order_id ), $order_id );
		update_field( 'payment_date', date('F j, Y g:i a'), $order_id );

		
		
} add_action( 'woocommerce_payment_complete', 'so_payment_complete' );
function castback_order_disputed_columns($columns) {
	$columns["disputed"] = "Disputed";
	return $columns;
} add_filter('manage_woocommerce_page_wc-orders_columns', 'castback_order_disputed_columns');
add_filter('manage_woocommerce_page_wc-orders_columns_sortable', 'castback_order_disputed_columns');

/* WP Admin - Order Tables */
function castback_order_columns_sort_disputed($vars) {
	if(array_key_exists('orderby', $vars)) {
		if('Disputed' == $vars['orderby']) {
			$vars['orderby'] = 'meta_value';
			$vars['meta_key'] = 'disputed_date';
		}
	}
	return $vars;
} add_filter('request', 'castback_order_columns_sort_disputed');
function castback_order_view_disputed( $views ) {
	$view = "<a href=\"http://example.com/wp-admin/edit-comments.php?comment_status=watch\">Disputed</a>";
	$views['disputed'] = $view;
	return $views;
} add_filter('views_wc-orders', 'castback_order_view_disputed');
function castback_order_disputed_column($colname, $order) {
	if ($colname == 'disputed') {
	    $disputedDate = get_field( 'disputed_date' , $order->get_id() );
	    if( $disputedDate ) { echo $disputedDate; }
	    else { echo '--'; }
	}
		
} add_action('manage_woocommerce_page_wc-orders_custom_column', 'castback_order_disputed_column', 10, 2);


/* AJAX Actions */
function CastBack_action_make_offer( $listing_id = '', $AJAX = true ) {
	if( !$listing_id ) { $listing_id = $_POST['listing_id']; }
	$customer_id = get_current_user_id();
	
	$args = array(
			'status'        => 'wc-checkout-draft',
			'customer_id'   => $customer_id,
	);
	$order = wc_create_order( $args );
	// Save the order to the database
	$order->save();
	$order_id = $order->get_id();
	


	/* Set ACF fields */
	update_field( 'customer_id', $customer_id, $order_id );
	update_field( 'seller_id', get_post_field( 'post_author', $listing_id ), $order_id );
	update_field( 'waiting_on', $customer_id, $order_id );
	update_field( 'listing_id', $listing_id, $order_id );
	update_field( 'order_amount', get_field( 'listing_price', $listing_id ), $order_id );

	// ob_start();

	// if ( $order ) {
			// $output .= '<script>window.location.href = "'.get_site_url().'/buying/offers/?order_id='.$order_id.'";</script>';
	// } else {
			// $output .= "Failed to create new order.";
	// }
	
	$output .= $order_id;
	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_action_make_offer', 'CastBack_action_make_offer' );
function CastBack_action_submit_offer( $order_id = '', $order_amount = '', $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	if( !$order_amount ) { $order_amount = $_POST['order_amount']; }
	
	/* Expire "last offer" BEFORE adding the new row! */
	castback_offer_expiration( $order_id );

	$row = array(
		'offer_date' => date('F j, Y g:i a'),
		'offer_amount' => number_format( $order_amount, 2 ),
		'offer_user_id' => get_current_user_id(),
	);
	add_row( 'offers', $row, $order_id );
	update_field( 'order_amount', number_format( $order_amount, 2 ), $order_id );

	$customer_id = get_field( 'customer_id', $order_id );
	$seller_id = get_field( 'seller_id', $order_id );
	
	if( get_current_user_id() == $customer_id ) { $waitingOn = $seller_id; }
	else { $waitingOn = $customer_id; }
	
	update_field( 'waiting_on', $waitingOn, $order_id );
	
	if($AJAX) { wp_die(); }
} add_action( 'wp_ajax_CastBack_action_submit_offer', 'CastBack_action_submit_offer' );
function CastBack_action_accept_offer( $order_id = '', $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	
	$offers = get_field( 'offers', $order_id );
	if( !( end($offers)['offer_expired_date'] ) ) {
		$order_amount = number_format( get_field( 'order_amount', $order_id ), 2 );
		update_field( 'accepted_date', date('F j, Y g:i a'), $order_id );

		// WaitingOnToggle();
		/* force WaitingOn to buyer */
		update_field( 'waiting_on', get_field( 'customer_id', $order_id ), $order_id );
		
		
		
		
		$order = wc_get_order($order_id);
		$order->update_status('wc-payment');

		$listing_id = get_field( 'listing_id', $order_id );
		/* Set Order Details */
		$quantity = 1;
		$args = array(
			'name'       		  => get_the_title( $listing_id ),
			// 'tax_class'    => $product->get_tax_class(),
			'product_id'   		=> $listing_id,
			// 'variation_id' => $product->is_type( ProductType::VARIATION ) ? $product->get_id() : 0,
			'variation'    		=> $listing_id,
			'subtotal'     		=> $order_amount,
			'total'						=> $order_amount,
			'price'						=> $order_amount,
		);
		$order->add_product( wc_get_product( $listing_id ), $quantity, $args );

		// Example: Setting billing and shipping addresses
		// $billing_address = array(
				// 'first_name' => 'John',
				// 'last_name'  => 'Doe',
				// 'address_1'  => '123 Main St',
				// 'city'       => 'Anytown',
				// 'state'      => 'CA',
				// 'postcode'   => '12345',
				// 'country'    => 'US',
				// 'email'      => 'john.doe@example.com',
				// 'phone'      => '555-123-4567',
		// );
		// $order->set_address( $billing_address, 'billing' );
		// $order->set_address( $billing_address, 'shipping' ); // Can be different if needed

		// Calculate totals after adding items and setting addresses
		$order->calculate_totals();

		// Save the order to the database
		$order->save();
		
		/* "Waiting On" is toggled via "woocommerce_payment_complete" */
	}
	
	// echo '';
	if($AJAX) { wp_die(); }
} add_action( 'wp_ajax_CastBack_action_accept_offer', 'CastBack_action_accept_offer' );
function CastBack_action_send_message( $order_id = '', $new_message = '', $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	if( !$new_message ) { $new_message = $_POST['new_message']; }
	// $new_message = get_field( 'new_message', $order_id );
	
	if( $new_message ) {
		$row = array(
			'message_date' => date('F j, Y g:i a'),
			'message_text' => $new_message,
			'message_user_id' => get_current_user_id(),
		);
		add_row( 'messages', $row, $order_id );
		// update_field( 'new_message', '', $order_id );
	}
	
	// echo '';
	if($AJAX) { wp_die(); }
} add_action( 'wp_ajax_CastBack_action_send_message', 'CastBack_action_send_message' );
function CastBack_action_add_tracking( $order_id = '', $trackingNumber = '', $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	if( !$trackingNumber ) { $trackingNumber = $_POST['new_tracking_number']; }
	
	if( $trackingNumber ) {
		$trackingDate = date('F j, Y g:i a');
		$row = array(
			'tracking_date' => $trackingDate,
			'tracking_number' => $trackingNumber,
			'tracking_user_id' => get_current_user_id(),
		);
		add_row( 'tracking', $row, $order_id );			
		update_field( 'new_tracking_number', '', $order_id );
		
		$shippedDate = get_field( 'shipped_date', $order_id );
		if( !$shippedDate ) {
			update_field( 'shipped_date', $trackingDate, $order_id );
			update_field( 'waiting_on', get_field( 'customer_id', $order_id ), $order_id );
			$order = wc_get_order($order_id);
			$order->update_status('wc-processing');
		}	$customer_id = get_field( 'customer_id', $_GET['order_id'] );
		$seller_id = get_field( 'seller_id', $_GET['order_id'] );
	
		if( get_current_user_id() == $customer_id ) { $waitingOn = $seller_id; }
		else { $waitingOn = $customer_id; }
		
		update_field( 'waiting_on', $waitingOn, $_GET['order_id'] );
	} else {
		// do nothing
	}
	
	// echo '';
	if($AJAX) { wp_die(); }
} add_action( 'wp_ajax_CastBack_action_add_tracking', 'CastBack_action_add_tracking' );
function CastBack_action_complete_order( $order_id = '', $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	
	// echo $order_id;
	
	update_field( 'completed_date', date('F j, Y g:i a'), $order_id );

	$order = wc_get_order($order_id);
	$order->update_status('wc-completed');
	
	// echo '';
	if($AJAX) { wp_die(); }
} add_action( 'wp_ajax_CastBack_action_complete_order', 'CastBack_action_complete_order' );
function CastBack_action_dispute_order( $order_id = '', $AJAX = true ) {
	update_field( 'disputed_date', date('F j, Y g:i a'), $order_id );
	
	// echo '';
	if($AJAX) { wp_die(); }
} add_action( 'wp_ajax_CastBack_action_dispute_order', 'CastBack_action_dispute_order' );
 
