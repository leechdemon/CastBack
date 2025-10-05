<?php
function CastBack_Offers( $method, $posts_per_page = null, $AJAX = false ) {
	$output = '';
	if( $method == 'MyOffers' ) {
		$title_url = '/buying/offers';
		$buyerOrSeller = 'customer_id';
		$offersOrders = 'offers';
		
		$filterOrderID = null;
	}
	if( $method == 'MyOrders' ) {
		$title_url = '/selling/my-orders';
		$buyerOrSeller = 'seller_id';
		$offersOrders = 'orders';
		
		$filterOrderID = null;
	}
	
	/* Build $orders as array, with 1 or $orderLimit items */
	if( $posts_per_page == 1 ) {
		$filterOrderID = array( 'p' => $method );
		
		$orders = wc_get_orders( array( 'p' => $method ) );
		
		if( $filterOrderID = get_field( 'customer_id', $filterOrderID ) ) { $buyerOrSeller = 'customer_id'; }
		if( $filterOrderID = get_field( 'seller_id', $filterOrderID ) ) { $buyerOrSeller = 'seller_id'; }
	} else  {
		$args = array(
			// 'status' => $orderStatus,
			// 'status' => 'any',
			// 'post_type'	=> array( 'shop_order_placehold', 'shop_order' ),
			'orderby' => 'date',      // Order by date
			'order'  => 'DESC',  
			'meta_query' => array(
				// 'relation' => 'OR',
				// array(
					// 'key'     => 'customer_id',
					// 'value'   => get_current_user_id(),
					// 'compare' => 'IN', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
				// ),
				// array(
					// 'key'     => 'seller_id',
					// 'value'   => get_current_user_id(),
					// 'compare' => 'IN', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
				// ),
				array(
					'key'     => $buyerOrSeller,
					'value'   => get_current_user_id(),
					'compare' => 'IN', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
				),
			),
		);
		if( isset( $posts_per_page ) ) {
			$orderLimit = $posts_per_page;
			$args['limit'] = $orderLimit;
		} else{
			$posts_per_page = -1;
			// $args['limit'] = 5;
		}
		
		
		$orders = wc_get_orders( $args );
	}
	
	/* Draw $orders */	
	foreach( $orders as $key => $order ) {
		if( !isset( $orderLimit ) || $key < $orderLimit ) {
			$order_id = $order->get_id();
			
			// $offers = get_field( 'offers', $order_id );
			
			$output .= '<h4 style="width: 100%; ">Order #<span id="castback_order_id">'.$order_id.'</span> <span class="castback-orderStatus" style="font-size: smaller;">('.CastBack_Offers_orderStatus_cosmetic( $order_id ).')</span></h4> ';
			$output .= '<div class="castback-order">';
					$listing_id = get_field( 'listing_id', $order_id );
					$output .= '<div class="castback-listing-panel">';
						$output .= '<div style="width: 75%; float: left;">';
							$output .= CastBack_Listings_drawListing( $listing_id, null, false, false );
						$output .= '</div>';
						
						$output .= '<div style="width: 25%; float: right; padding-left: 0.5rem; ">';
							$output .= CastBack_Action_DrawButtonPanel( $order_id );
						$output .= '</div>';
					$output .= '</div>';
			$output .= '</div>';
		}
	}
	if( isset( $orderLimit ) ) { $output .= '<a class="button elementor-button elementor-button-link" href="'.$title_url.'">View More</a>'; }
	if( count($orders) < 1 ) {
		$output .= 'You have no '.$offersOrders.'.';
	}
	
	// Jason broke the AJAX on MyOffers-refresh to fix Elementor in v0.5
	// if($AJAX) { ob_start(); echo $output; return ob_get_clean(); wp_die(); }
	// else {
		ob_start();
		echo $output;
		return ob_get_clean();
	// }
}

function CastBack_Offers_renameHoldStatus( $order_statuses ) {
    foreach ( $order_statuses as $key => $status ) {
        if ( 'wc-on-hold' === $key ) 
            $order_statuses['wc-on-hold'] = _x( 'Disputed', 'Order status', 'woocommerce' );
    }
    return $order_statuses;
} add_filter( 'wc_order_statuses', 'CastBack_Offers_renameHoldStatus' );
function CastBack_Offers_orderStatus_determine( $order_id ) {
	if( $order_id ) { 
		$order = wc_get_order( $order_id );	
		if( $order ) { 
			// Fix this!!
			$order->update_status('checkout-draft');
		}
	}
}
function CastBack_Offers_orderStatus_cosmetic( $order_id ) {
	$order = wc_get_order( $order_id );	
	switch( $order->get_status() ) {
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

function CastBack_Offers_drawOrderDetails( $order_id, $AJAX = false ) {
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }

	ob_start();
	
	$order = wc_get_order( $order_id );	
	if( $order ) {
		echo '<div id="CastBack-Order-'.$order_id.'">';
			/* replace this... */
			echo CastBack_Offers_drawButtons( $order_id  );

			/* Display Sidebar */
			echo '<div id="castback-sidebar" style="width: 35%; display: inline-block; float: right; margin-bottom: 1rem;">';
				echo CastBack_Offers_drawSidebar( $order_id, false );
				// echo CastBack_Offers_drawSidebarNotes( $order_id, false );
			echo '</div>';
		
		echo '</div>';
	} else {
		echo '<div>Order #<span id="castback_order_id">'.$order_id.'</span> does not exist.</div>';
	}
	
	$output = ob_get_clean();
	if($AJAX) { echo $output; wp_die(); } else { return $output; }
	// if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_Offers_drawOrderDetails', 'CastBack_Offers_drawOrderDetails' );

function CastBack_Offers_draw_order_UNUSED( $order_id, $AJAX = true ) {
	
	if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
	if( !$order_id && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }

	// ob_start();
	$output = '';
	
	if( $order_id ) {
		$order = wc_get_order( $order_id );	
		if( $order ) {
			/* Display Order Details */
			$output .= '<h3>Order #<span id="castback_order_id">'.$order_id.'</span></h3>';
			$output .= '<div class="castback-order-details">';
				$orderStatus = $order->get_status();
				$waitingOn = get_field( 'waiting_on', $order_id );
				$name = get_userdata( $waitingOn );
				$output .= '<h5 style="margin-left: 1rem;">Order Status: '.CastBack_Offers_orderStatus_cosmetic( $order_id ).'</h5>';
				if( $name && $orderStatus != 'completed' ) { $output .= '<h5 style="margin-left: 1rem;">Waiting On: '.$name->first_name .' '.$name->last_name.'</h5>'; }
			$output .= '</div>';

			/* Display the Listing */			
			if( !$buttonPanelEnabled ) { $disabled = ' disabled'; }
			$output .= '<div class="castback-listing'.$disabled.'">';
				/* Listing Panel */

			$output .= '</div>';
		} else {
			$output .=  'Order #<span id="castback_order_id">'.$order_id.'</span> not found.';
		}
	} else {
		$output .= "No order_id found.";
	}
	
	// if($order_id) { echo $output; }
	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_Offers_draw_order', 'CastBack_Offers_draw_order' );

function CastBack_Offers_drawButtons( $order_id ) {
    $disputedDate = get_field( 'disputed_date', $order_id );
    $waitingOn = get_field( 'waiting_on', $order_id );
    $order = wc_get_order( $order_id );
		$orderStatus = $order->get_status();
		
    if( !$disputedDate ) {
        
			/* Accept / Submit Offer */
    	if( $orderStatus == 'checkout-draft' && get_current_user_id() == $waitingOn ) {
				$output = '';
				$output .= '<div class="acf_offers" style="float: left; clear: both;">';
					$output .= '<input style="width: 100px;"id="castback_offer_amount" type="number" value="'.get_field( 'order_amount', $order_id ).'">';
					$output .= '<a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_submitOffer_button(\''.$order_id.'\')">Submit Offer</a>';
					if( get_field( 'offers', $order_id ) ) { $output .= '<a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_acceptOffer_button(\''.$order_id.'\')">Accept Offer</a>'; }
				$output .= '</div>';
			}		
			/* Submit Payment */
			if( $orderStatus == 'pending' && get_current_user_id() == $waitingOn ) {
				$output .= '<div class="acf_offers" style="float: left; clear: both;">';
					$output .= '<a class="button elementor-button elementor-button-link" href="'. $order->get_checkout_payment_url() .'" target="_blank">Pay Order</a>';
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
						$output .= '<a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_addTracking_button(\''.$order_id.'\')">Add Tracking Order</a>';
					$output .= '</div>';
				}
				
				/* Complete Order */
				if( get_current_user_id() == $waitingOn && get_field( 'shipped_date', $order_id ) ) {
					$output .= '<div class="acf_offers" style="float: left; clear: both;">';
						$output .= '<a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_completeOrder_button(\''.$order_id.'\')">Complete Order</a>';
					$output .= '</div>';
				}
		//	if( get_current_user_id() == get_field( 'customer_id', $order_id ) ) {
				// if( !get_field( 'disputed_date', $order_id ) ) { $output .= '<div class="acf_dispute" style="float: left; clear: both;"><a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_disputeOrder_button(\''.$order_id.'\')">Dispute Order</a></div>'; }
			//	}
			}
    }
		// if( !get_field( 'disputed_date', $order_id ) ) { $output .= '<div class="acf_dispute" style="float: left; clear: both;"><a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_disputeOrder_button(\''.$order_id.'\')">Dispute Order</a></div>'; }
		// else { $output .= '<div class="acf_dispute" style="float: left; clear: both;"><a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_removeDispute_button(\''.$order_id.'\')">Remove Dispute</a></div>'; }
		
		return $output;
}

function CastBack_Offers_drawSidebarNotes( $order_id, $AJAX = true ) {	
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
			$output .= '<a class="castback-order-refresh button button-elementor button-elementor-link" href="javascript:CastBack_Offers_drawOrderDetails_button('.$order_id.');" style="display: block; float: right; width: auto; padding-left: 0.5rem; font-size: small;">(Refresh)</a>';
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
			$output .= '<p>No notes found for Order #<span id="castback_order_id">'.$order_id.'</span></p>';
	}

	if($AJAX) { echo $output; wp_die(); } else { return $output; }
}
function CastBack_Offers_drawSidebar( $order_id, $AJAX = true ) {	
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	
	$order = wc_get_order( $order_id );
	if( $order ) {

	
		/* Display History */
		$output = '';
		$output .= '<h5 style="">Offer History';	
			$output .= '<a class="castback-order-refresh" href="javascript:CastBack_Offers_drawOrderDetails_button('.$order_id.');" style="display: block; float: right; width: auto; padding-left: 0.5rem; font-size: small;">(Refresh)</a>';
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
			$output .= '<a class="button elementor-button elementor-button-link" href="javascript:CastBack_Action_sendMessage_button(\''.$order_id.'\')">Send Message</a>';
		
		$output .= '</div>';
	}
	else { /* do something */ }

	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_Offers_drawSidebar', 'CastBack_Offers_drawSidebar' );

function CastBack_Offers_customerSeller( $order_id ) {
	$user_id = get_current_user_id();
	if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	
	$ids = array();
	$ids['user_id'] = $user_id;
	$ids['customer_id'] = get_field( 'customer_id', $order_id );
	$ids['seller_id'] = get_field( 'seller_id', $order_id );
	
	$customerSeller = array();
	$customerSeller['ids'] = $ids;
	$customerSeller['any'] = false;
	if( $ids['user_id'] == $ids['customer_id'] ) { $customerSeller['any'] = true; }
	if( $ids['user_id'] == $ids['seller_id'] ) { $customerSeller['any'] = true; }
	
	// if( !$customerSeller['any'] ) { Test( $customerSeller ); }
	// echo json_encode( $customerSeller );
	// return $customerSeller;
	return $customerSeller['any'];
}