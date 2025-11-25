<?php
function CastBack_Offers( $method, $posts_per_page = null, $AJAX = false ) {
	$user_id = get_current_user_id();
	$output = '';
	if( $method == 'MyOffers' ) {
		$title_url = '/buying/offers';
		$buyerOrSeller = 'customer_id';
		$role = 'customer';
		$offersOrders = 'offers';
		
		$filterOrderID = null;
	}
	if( $method == 'MyOrders' ) {
		$title_url = '/selling/my-orders';
		$buyerOrSeller = 'seller_id';
		$role = 'seller';
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
					'value'   => $user_id,
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
			//$output .= $buyerOrSeller;
			$notificationBubble = do_shortcode('[CastBack action="userHasNotification" order_id="'.$order_id.'" method="'.$role.'"]');
			//$notificationBubble = do_shortcode('[CastBack action="userHasNotification" order_id="'.$order_id.'" user_id="'.$user_id.'" method="'.$buyerOrSeller.'"]');
			$output .= '<h4 style="width: 100%; ">'.$notificationBubble.'Order #<span id="castback_order_id">'.$order_id.'</span> <span class="castback-orderStatus" style="font-size: smaller;">('.CastBack_Offers_orderStatus_cosmetic( $order_id ).')</span></h4> ';
			$output .= '<div class="castback-order">';
					$listing_id = get_field( 'listing_id', $order_id );
					$output .= '<div class="castback-listing-panel">';
						$output .= '<div style="width: fit-content; float: left;">';
							$output .= CastBack_Listings_drawListing( $listing_id, null, false, false );
						$output .= '</div>';
						
						$output .= '<div style="width: 25%; float: left; padding-left: 0.5rem; ">';
							$output .= CastBack_Buttons_DrawButtonPanel( $order_id );
						$output .= '</div>';
					$output .= '</div>';
			$output .= '</div>';
		}
	}
	if( isset( $orderLimit ) ) { $output .= '<a class="castback-button elementor-button elementor-button-link" href="'.$title_url.'">View More</a>'; }
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

/* - Security: Public */
function CastBack_Offers_orderStatus_determine( $order_id ) {
	if( $order_id ) { 
		$order = wc_get_order( $order_id );	
		if( $order ) { 
			// Fix this!!
			$order->update_status('checkout-draft');
		}
	}
}
function CastBack_Offers_orderStatus_cosmetic( $order_id = null, $display = false ) {
	if( $order_id ) {		
		$order = wc_get_order( $order_id );	
		if( $order ) {
			switch( $order->get_status() ) {
				case 'checkout-draft':
					$orderStatusCosmetic = 'Offer Pending';
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
		}
		
		$disputedDate = get_field( 'disputed_date', $order_id );
		if( $disputedDate ) { $orderStatusCosmetic = 'Order Disputed'; }
		
		
		if( $display ) { $orderStatusCosmetic = '('.$orderStatusCosmetic.')'; }
		return $orderStatusCosmetic;
	}
	else { return 0; }
}
function CastBack_Offers_minimumOfferPrice( $order_amount = null ) {
	$MOT = get_field( 'minimum_offer_total', 'option' );
	
	if( !$order_amount ) { return $MOT; }
	else {
		if( !$listing_id && isset( $_GET['order_id'] ) ) { $listing_id = get_field( 'listing_id', $_GET['order_id'] ); }
		if( !$listing_id && isset( $_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
		if( !$listing_id && isset( $_POST['order_id'] ) ) { $listing_id = get_field( 'listing_id', $_POST['order_id'] ); }
		if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
		
		if( $listing_id ) {
			$shipping_price = get_field( 'shipping_price', $listing_id );
			if( !$shipping_price ) { $shipping_price = 0; }
						
			if( ($order_amount + $shipping_price) < $MOT ) { $order_amount = $MOT - $shipping_price; }
				
			return $order_amount;
		}
	}
}

/* - Security: Contained in child functions */
function CastBack_Offers_ViewOfferPanel( $order_id = null, $AJAX = false ) {
	$output .= do_shortcode('[elementor-template id="2725"]');
	
	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_Offers_ViewOfferPanel', 'CastBack_Offers_ViewOfferPanel' );
function CastBack_Offers_ViewOrderActionButtons( $order_id = null, $AJAX = false ) {
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }

	$disputedDate = get_field( 'disputed_date', $order_id );
	$waitingOn = get_field( 'waiting_on', $order_id );
	$order = wc_get_order( $order_id );
	$orderStatus = $order->get_status();
	if( $AJAX && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	else { $user_id = get_current_user_id(); }
	
	if( !$disputedDate ) {
		/* Accept / Submit Offer */
		if( $orderStatus == 'checkout-draft' && $user_id == $waitingOn ) {		
			$reason = CastBack_userCanPurchase( get_current_user_id() );
			if( $reason !== true ) { return $reason; }
			else {
				if( get_field( 'offers', $order_id ) ) {
					$output .= '<a class="castback-button elementor-button elementor-button-link" href="javascript:CastBack_Action_acceptOffer_button(\''.$order_id.'\')" style="width: 100%;">Accept Offer</a>';
				}

				$output .= '<p style="width: 100%; text-align: center;">(You may also make a counter-offer below)</p>';
				
				$output .= CastBack_Offers_ViewOfferPanel( $order_id );
				$output .= '<div class="acf_offers" style="float: left; clear: both; width: 100%;">';
					// $output .= '<input style="width: 100px;"id="castback_offer_amount" type="number" value="'.get_field( 'order_amount', $order_id ).'">';
					$output .= '<a class="castback-button elementor-button elementor-button-link" href="javascript:CastBack_Action_submitOffer_button(\''.$order_id.'\')" style="width: 100%;">Submit Offer</a>';
				$output .= '</div>';
			}
		}		
		/* Submit Payment */
		if( $orderStatus == 'pending' && get_current_user_id() == $waitingOn ) {
			$output .= '<div class="acf_offers" style="float: left; clear: both;">';
				$output .= '<a class="castback-button elementor-button elementor-button-link" href="'. $order->get_checkout_payment_url() .'">Pay Order</a>';
			$output .= '</div>';
		}
		
		/* Shipping */
		if( $orderStatus == 'processing' ) {
			/* Ship Order */
			if( get_current_user_id() == get_field( 'seller_id', $order_id ) ) { $displayShipping = true; }
			else if( get_field( 'shipped_date', $order_id ) ) { $displayShipping = true; }
			
			if( $displayShipping ) {
				$output .= do_shortcode('[CastBack field="customerAddress"]');
				$output .= '<div class="acf_offers" style="float: left; clear: both;">';
					$output .= '<h6 style="width: fit-content;">Tracking Number:</h6>';
					$output .= '<input style="width: 100%; margin: 0.5rem 0;" id="castback_new_tracking_number" type="text">';
					$output .= '<a class="castback-button elementor-button elementor-button-link" href="javascript:CastBack_Action_addTracking_button(\''.$order_id.'\')">Add Tracking Number</a>';
				$output .= '</div>';
			}
			
			/* Complete Order */
			if( get_current_user_id() == $waitingOn && get_field( 'shipped_date', $order_id ) ) {
				$output .= '<div class="acf_offers" style="float: left; clear: both;">';
					$output .= '<a class="castback-button elementor-button elementor-button-link" href="javascript:CastBack_Action_completeOrder_button(\''.$order_id.'\')">Complete Order</a>';
				$output .= '</div>';
			}
		}
	}

	if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_Offers_ViewOrderActionButtons', 'CastBack_Offers_ViewOrderActionButtons' );

/* - Security: CastBack_customerSeller() */
function CastBack_Offers_ViewOfferSidebar( $order_id, $AJAX = true ) {	
	if( !$order_id ) { $order_id = $_POST['order_id']; }
	
	if( CastBack_customerSeller( $order_id ) || is_user_admin() ) {
		$order = wc_get_order( $order_id );
		if( $order ) {

			
			
			/* Display History */
			$output = '';
			$output .= '<h5 style="">Offer History';	
				$output .= '<a class="castback-order-refresh" href="javascript:CastBack_Offers_refreshOrder('.$order_id.');" style="display: block; float: right; width: auto; padding-left: 0.5rem; font-size: small;">(Refresh)</a>';
			$output .= '</h5>';
			$output .= '<div class="offer_history">';

				
				/* Display Offers */
				$offers = get_field( 'offers', $order_id  );
				if( $offers ) {
					foreach( $offers as $offer ) {
						if( $offer['offer_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
						else { $customerOrSeller = ' seller'; }
						
						$output .= '<div class="offer_history_item'.$customerOrSeller.'" style="order: '.( (int)strtotime( $offer['offer_date'] ) +1 ).';">';
							$output .= '<div class="offer_history_subitem date">'. $offer['offer_date'] . '</div>';

							$name = get_userdata( $offer['offer_user_id'] );
							if( $name->first_name || $name->last_name ) {
								$displayName = $name->first_name .' ';
								$displayName .= $name->last_name;
							}
							if( !$displayName ) { $displayName = '(User #' . $offer['offer_user_id'].')'; }
							
							if( $offer['offer_expired_date'] ) { $offerExpired = ' offer_expired'; } else { $offerExpired = ''; }
							$output .= '<div class="offer_history__subitem'.$offerExpired.'">'. $displayName . ' made an Offer of $'. CastBack_Filter_formatPriceField( $offer['offer_amount'] ).'</div>';
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
				$offers = get_field( 'offers', $order_id );
				$dateCreated = $offers[0]['offer_date'];
				$output .= '<div class="order_history_item customer" style="order: '.strtotime( $dateCreated ).';">';
					$output .= '<div class="order_history_subitem date">'. $dateCreated . '</div>';
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
					$output .= '<div class="order_history_item customer" style="order: '.( (int)strtotime( $paymentDate )  - 0 ).';">';
						$output .= '<div class="order_history_subitem date">'. $paymentDate . '</div>';
						$output .= '<div class="order_history_subitem">Order Paid</div>';
					$output .= '</div>';
				}
				$shippedDate = get_field( 'shipped_date', $order_id );
				if( $shippedDate ) {
					$output .= '<div class="order_history_item seller" style="order: '.( (int)strtotime( $shippedDate )  - 0 ).';">';
						$output .= '<div class="order_history_subitem date">'. $shippedDate . '</div>';
						$output .= '<div class="order_history_subitem">Order Shipped</div>';
					$output .= '</div>';
				}
				$completedDate = get_field( 'completed_date', $order_id );
				if( $completedDate ) {
					$output .= '<div class="order_history_item customer" style="order: '.( (int)strtotime( $completedDate )  - 0 ).';">';
						$output .= '<div class="order_history_subitem date">'. $completedDate . '</div>';
						$output .= '<div class="order_history_subitem">Order Completed</div>';
					$output .= '</div>';
				}
				$disputedDate = get_field( 'disputed_date', $order_id );
				if( $disputedDate ) {
					$output .= '<div class="order_history_item customer" style="order: '.( (int)strtotime( $disputedDate )  - 0 ).';">';
						$output .= '<div class="order_history_subitem date">'. $disputedDate . '</div>';
						$output .= '<div class="order_history_subitem">Order was Disputed. CastBack support will be in touch soon...</div>';
					$output .= '</div>';
			}
			$output .= '</div>'; // end order history					

			/* Display Messaging Window */
			$output .= '<div class="acf_messages">';

				/* Send Message */
				$output .= '<input style="width: 100px;"id="castback_new_message" type="text-area">';
				$output .= '<a class="castback-button elementor-button elementor-button-link" href="javascript:CastBack_Action_sendMessage_button(\''.$order_id.'\')">Send Message</a>';
			
			$output .= '</div>';
		}
		else { /* do something */ }
	} else { $output .= 'This is not your order. Please log in and try again. ("'.$order_id.'", O471, 11052025)'; }

		if($AJAX) { echo $output; wp_die(); } else { return $output; }
} add_action( 'wp_ajax_CastBack_Offers_ViewOfferSidebar', 'CastBack_Offers_ViewOfferSidebar' );

function CastBack_Offers_refreshRevisionDate( $order_id ) {
	if( CastBack_customerSeller( $order_id ) ) {
		update_field( 'revision_date', wp_date('F j, Y g:i:s a'), $order_id );
	}
}
