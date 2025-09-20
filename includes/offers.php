<?php
function CastBack_Offers( $method, $page = null, $orderLimit = null, $AJAX = false ) {
	$output = '';
	if( $method == 'MyOffers' ) {
		$title = 'My Offers';
		$title_url = '/buying/offers';
		// $orderLimit = -1;
		$buyerOrSeller = 'customer_id';
		$orderStatus = array( 'checkout-draft', 'pending', 'on-hold', 'processing', 'completed' );
		$offersOrders = 'offers';
	}
	if( $method == 'MyOrders' ) {
		$title = 'My Orders';
		$title_url = '/selling/my-orders';
		// $orderLimit = -1;
		$buyerOrSeller = 'seller_id';
		$orderStatus = array( 'checkout-draft', 'pending', 'on-hold', 'processing', 'completed' );
		$offersOrders = 'orders';
	}
if( $orderLimit ) { $orderLimit++; }
	
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
			$output .= '<a class="view_more" href="'.$title_url.'">View More...</a>';
		}
		else {
			if($order) {
				$order_id = $order->get_id();
			
				$offers = get_field( 'offers', $order_id );
				// if( $offers[0]['offer_amount'] || get_field( 'customer_id', $order_id ) == get_current_user_id() ) {
				// if( $offers[0]['offer_amount'] ) {
					$orderCount++;
					$output .= '<a class="item" href="'.get_site_url().$title_url.'/?order_id='.$order_id.'">Order #' .$order_id .' - '.CastBack_offers_orderStatus_cosmetic( $order->get_status() ).'</a>';
					// AJAX "View" URL removed for v0.5 Release
					// echo '<a class="button" href="javascript:CastBack_edit_listing_button(\''.$listing_id.'\', \''.$user_id.'\', \'CastBack-MyListings\');">Edit Listing</a>';

					
					
					// echo '<div class=""><a href="javascript:CastBack_offers_draw_order_page_button('.$order_id.', \'CastBack-'.$page.'\');">.'</div>';
					// echo '<div class=""><a href="javascript:CastBack_offers_draw_order_page_button('.$order_id.', \'CastBack-'.$page.'\');">Order #' .$order_id .'</a> - '.CastBack_offers_orderStatus_cosmetic( $order->get_status() ).'</div>';
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

function CastBack_offers_renameHoldStatus( $order_statuses ) {
    foreach ( $order_statuses as $key => $status ) {
        if ( 'wc-on-hold' === $key ) 
            $order_statuses['wc-on-hold'] = _x( 'Disputed', 'Order status', 'woocommerce' );
    }
    return $order_statuses;
} add_filter( 'wc_order_statuses', 'CastBack_offers_renameHoldStatus' );
function CastBack_offers_orderStatus_determine( $order_id ) {
	if( $order_id ) { 
		$order = wc_get_order( $order_id );	
		if( $order ) { 
			// Fix this!!
			$order->update_status('checkout-draft');
		}
	}
}
function CastBack_offers_orderStatus_cosmetic( $orderStatus ) {
	$orderStatusCosmetic = $orderStatus;
	switch( $orderStatus ) {
		case 'checkout-draft':
			$orderStatusCosmetic = 'Draft';
			break;
		case 'pending':
			$orderStatusCosmetic = 'Pending Payment';
			break;
		case 'on-hold':
			$orderStatusCosmetic = 'Disputed';
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
function CastBack_offers_draw_order_page( $order_id = '', $page = '', $AJAX = true ) {
	if( !$order_id && $_POST['order_id']  ) { $order_id = $_POST['order_id']; }
	if( !$order_id && $_GET['order_id']  ) { $order_id = $_GET['order_id']; }
	if( !$page && $_POST['targetDiv'] ) { $page = $_POST['targetDiv']; }

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
			echo CastBack_offers_draw_sidebar_notes( $order_id, $page, false );
		echo '</div>';
		
		if($AJAX) { echo $output; wp_die(); } else { return $output; }
	} else {
		echo '<div>Order #'.$order_id.' does not exist.</div>';
	}
} add_action( 'wp_ajax_CastBack_offers_draw_order_page', 'CastBack_offers_draw_order_page' );
function CastBack_offers_draw_order( $order_id, $page = '', $AJAX = true ) {
	
	if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
	if( !$order_id && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }

	// ob_start();
	$output = '';
	
	if( $order_id ) {
		$order = wc_get_order( $order_id );	
		if( $order ) {
			/* Display Order Details */
			$output .= '<h3>Order #<span id="castback_order_id" style="">'.$order_id.'</span></h3>';
			$output .= '<div class="castback-order-details">';
				$orderStatus = $order->get_status();
				$waitingOn = get_field( 'waiting_on', $order_id );
				$name = get_userdata( $waitingOn );
				$output .= '<h5 style="margin-left: 1rem;">Order Status: '.CastBack_offers_orderStatus_cosmetic( $orderStatus ).'</h5>';
				if( $name && $orderStatus != 'completed' ) { $output .= '<h5 style="margin-left: 1rem;">Waiting On: '.$name->first_name .' '.$name->last_name.'</h5>'; }
			$output .= '</div>';

			/* Display the Listing */
			$listing_id = get_field( 'listing_id', $order_id );
			$output .= CastBack_listings_draw_listing( $listing_id, '949', $AJAX );

			/* Display Buttons */
			$output .= CastBack_offers_draw_buttons( $order_id, $orderStatus, $page );
		} else {
			$output .=  "Order #".$order_id."not found.";
		}
	} else {
		$output .= "No order_id found.";
	}
	
	// if($order_id) { echo $output; }
	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_offers_draw_order', 'CastBack_offers_draw_order' );
function CastBack_offers_draw_buttons( $order_id, $orderStatus, $page = ''  ) {
    $disputedDate = get_field( 'disputed_date', $order_id );
    $waitingOn = get_field( 'waiting_on', $order_id );
    $order = wc_get_order( $order_id );
    // if( !$page ) { $page = $_POST['targetDiv']; }
		
    if( !$disputedDate ) {
        
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
				// if( !get_field( 'disputed_date', $order_id ) ) { $output .= '<div class="acf_dispute" style="float: left; clear: both;"><a class="button" href="javascript:CastBack_action_dispute_order_button(\''.$page.'\')">Dispute Order</a></div>'; }
			//	}
			}
    }
		if( !get_field( 'disputed_date', $order_id ) ) { $output .= '<div class="acf_dispute" style="float: left; clear: both;"><a class="button" href="javascript:CastBack_action_dispute_order_button(\''.$page.'\')">Dispute Order</a></div>'; }
		else { $output .= '<div class="acf_dispute" style="float: left; clear: both;"><a class="button" href="javascript:CastBack_action_remove_dispute_button(\''.$page.'\')">Remove Dispute</a></div>'; }
		
		return $output;
}
function CastBack_offers_draw_sidebar_notes( $order_id, $page = null, $AJAX = true ) {	
	$output = '';	
	
	// Get all notes for the specified order
	$notes = wc_get_order_notes( array(
			'order_id' => $order_id,
			'type'     => '', // Use 'customer' for customer notes, 'internal' for admin/system notes, or empty for all
			'orderby'  => 'date_created', // Sort by creation date
			'order'    => 'ASC', // Ascending order
	) );
	
	if ( $notes ) {
		/* Display History */
		$output = '';
		$output .= '<h5 style="">Order History';	
			$output .= '<a class="castback-order-refresh" href="javascript:CastBack_offers_draw_order_page_button('.$order_id.', \''.$page.'\');" style="display: block; float: right; width: auto; padding-left: 0.5rem; font-size: small;">(Refresh)</a>';
		$output .= '</h5>';
		$output .= '<div class="order_history">';

    // $output .= '<ul>';

    foreach ( $notes as $note ) {
        $output .= '<div id="note-id-'.$note->id.'" class="order_history_item">';
					$output .= '<strong>Date:</strong> ' . $note->date_created->format( 'Y-m-d H:i:s' ) . '<br>';
					$output .= '<strong>Author:</strong> ' . $note->added_by . '<br>'; // 'system' or user name
					$output .= '<strong>Content:</strong> ' . wpautop( wptexturize( wp_kses_post( make_clickable( $note->content ) ) ) ) . '<br>';
					$output .= '<strong>Type:</strong> ' . $note->type . '<br>'; // 'customer' or 'internal'
        $output .= '</div>';
    }
		
    $output .= '</div';
	} else {
			$output .= '<p>No notes found for Order #' . $order_id . '</p>';
	}

	if($AJAX) { echo $output; wp_die(); } else { return $output; }
}
function CastBack_offers_draw_sidebar( $order_id, $page = null, $AJAX = true ) {	
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	
	$order = wc_get_order( $order_id );
	if( $order ) {

	
		/* Display History */
		$output = '';
		$output .= '<h5 style="">Offer History';	
			$output .= '<a class="castback-order-refresh" href="javascript:CastBack_offers_draw_order_page_button('.$order_id.', \''.$page.'\');" style="display: block; float: right; width: auto; padding-left: 0.5rem; font-size: small;">(Refresh)</a>';
		$output .= '</h5>';
		$output .= '<div class="offer_history">';

			
			/* Display Offers */
			$offers = get_field( 'offers', $order_id  );
			if( $offers ) {
				foreach( $offers as $offer ) {
					if( $offer['offer_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
					else { $customerOrSeller = ' seller'; }
					
					$output .= '<div class="offer_history_item'.$customerOrSeller.'" style="order: '.strtotime( $offer['offer_date'] ).';">';
						$output .= '<div class="offer_history_subitem date">'. $offer['offer_date'] . '</div>';

						$name = get_userdata( $offer['offer_user_id'] );
						if( $offer['offer_expired_date'] ) { $offerExpired = ' offer_expired'; } else { $offerExpired = ''; }
						$output .= '<div class="offer_history__subitem'.$offerExpired.'">'. $name->first_name .' '.$name->last_name . ' made an Offer of $'. $offer['offer_amount'].'</div>';
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
	}
	else { /* do something */ }

	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_offers_draw_sidebar', 'CastBack_offers_draw_sidebar' );