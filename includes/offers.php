<?php

function CastBack_make_offer_URL($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	ob_start();
	
	if( get_current_user_id() ) {
		$url = get_site_url().'/buying/offers/';
		echo '<a class="button" href="javascript:CastBack_action_make_offer_button('.$listing_id.');">Make Offer</a>';
	} else {
		echo '<a class="button" href="'. get_site_url().'/wp-login.php">Make Offer</a>';
	}

	return ob_get_clean();
} add_shortcode('CastBack_make_offer_URL', 'CastBack_make_offer_URL');

function CastBack_offers($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null, 'view' => null ), $atts));
	if ( !$order_id ) { $order_id = $_GET['order_id']; }

	ob_start();

	if( $order_id ) {
		$customer_id = get_field( 'customer_id', $order_id );
		
		echo '<div id="castback-order-page">'.CastBack_offers_draw_order_page( $order_id, false ).'</div>';
	
	} else { /* Display list of Offers for buyer/seller */
		$args = array(
			// 'status' => 'wc-processing', // Get completed orders
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
			if( $offers[0]['offer_amount'] || get_field( 'customer_id', $order_id ) == get_current_user_id() ) {
				$orderCount++;
				echo '<div class=""><a href="?order_id='.$order_id.'">Order #' .$order_id .'</a> - '.CastBack_orderStatusCosmetic( $order->get_status() ).'</div>';
			}
		}
		if( $orderCount < 1 ) {
			echo '<div>You have no orders.</div>';
		}
	}
	
	return ob_get_clean();
} add_shortcode('CastBack_offers', 'CastBack_offers');
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
function CastBack_offers_draw_order_page( $order_id = '', $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	
	/* Display Order */
	$output = '';
	$output .= '<div id="castback-order">';
		$output .= CastBack_offers_draw_order( $order_id, false );
	$output .= '</div>';
	
	/* Display Sidebar */
	$output .= '<div id="castback-sidebar">';
		$output .= CastBack_offers_draw_sidebar( $order_id, false );
	$output .= '</div>';
	
	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_offers_draw_order_page', 'CastBack_offers_draw_order_page' );
function CastBack_offers_draw_order( $order_id, $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }

	$order = wc_get_order( $order_id );	
	if( $order ) {
		/* Display Order Details */
		$output .= '';
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
		$args = array(
				'p'							 =>	$listing_id,
				'post_type'      => 'product',
				'posts_per_page' => 1,
		);
		$custom_query = new WP_Query( $args );
		if ( $custom_query->have_posts() ) {
			while ( $custom_query->have_posts() ) {
				$custom_query->the_post();
				$output .= '<div class="castback-order-listing">'. do_shortcode('[elementor-template id="822"]') .'</div>';
				wp_reset_postdata();
			}
		}

		/* Display Buttons */
		$output .= CastBack_offers_draw_buttons( $order_id, $orderStatus );	
	} else {
		$output .=  "order not found.";
	}

	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_offers_draw_order', 'CastBack_offers_draw_order' );
function CastBack_offers_draw_buttons( $order_id, $orderStatus ) {
    $disputedDate = get_field( 'disputed_date', $order_id );
    $waitingOn = get_field( 'waiting_on', $order_id );
    $order = wc_get_order( $order_id );
    
    if( $disputedDate == '' ) {
        
			/* Accept / Submit Offer */
    	if( $orderStatus == 'checkout-draft' && get_current_user_id() == $waitingOn ) {
				$output = '';
				$output .= '<div class="acf_offers">';
					$output .= '<input id="castback_offer_amount" type="number" value="'.get_field( 'order_amount', $order_id ).'">';
					$output .= '<a class="button" href="javascript:CastBack_action_submit_offer_button()">Submit Offer</a>';
					if( get_field( 'offers', $order_id ) ) { $output .= '<a class="button" href="javascript:CastBack_action_accept_offer_button()">Accept Offer</a>'; }
				$output .= '</div>';
			}		
			/* Submit Payment */
			if( $orderStatus == 'pending' && get_current_user_id() == $waitingOn ) {
				$output .= '<div class="acf_offers">';
					$output .= '<a class="button" href="'. $order->get_checkout_payment_url() .'" target="_blank">Pay Order</a>';
				$output .= '</div>';
			}
			
    	/* Shipping */
			if( $orderStatus == 'processing' ) {
				/* Ship Order */
				if( get_current_user_id() == get_field( 'seller_id', $order_id ) ) { $displayShipping = true; }
				else if( get_field( 'shipped_date', $order_id ) ) { $displayShipping = true; }
				
				if( $displayShipping ) {
					$output .= '<div class="acf_offers">';
						$output .= '<input id="castback_new_tracking_number" type="text">';
						$output .= '<a class="button" href="javascript:CastBack_action_add_tracking_button()">Add Tracking Order</a>';
					$output .= '</div>';
				}
				
				/* Complete Order */
				if( get_current_user_id() == $waitingOn && get_field( 'shipped_date', $order_id ) ) {
					$output .= '<div class="acf_offers">';
						$output .= '<a class="button" href="javascript:CastBack_action_complete_order_button()">Complete Order</a>';
					$output .= '</div>';
				}
		//	if( get_current_user_id() == get_field( 'customer_id', $order_id ) ) {
				$output .= '<div class="acf_dispute"><a class="button" href="javascript:CastBack_action_dispute_order_button()">Dispute Order</a></div>';
			//	}
			}
    }
		
		return $output;
}
function CastBack_offers_draw_sidebar( $order_id, $AJAX = true ) {
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	$order = wc_get_order( $order_id );
	
	/* Display History */
	$output = '';
	$output .= '<h5 style="">Order History';	
		$output .= '<a class="castback-order-refresh" href="javascript:CastBack_offers_draw_order_page_button('.$order_id.');">(Refresh)</a>';
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
		$output .= '<input id="castback_new_message" type="text-area">';
		$output .= '<a class="button" href="javascript:CastBack_action_send_message_button()">Send Message</a>';
	
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
 
