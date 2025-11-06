<?php	
/* Draft Offers */
	/* - Security: Does not require security, since all Users can create Listings. */
	function CastBack_Action_buyNow( $listing_id = null, $order_amount = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
		if( !isset( $listing_id ) && isset( $_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
		$success = true;
		
		if( isset( $_POST['user_id'] ) ) { $customer_id = $_POST['user_id']; }		
		else { $customer_id = get_current_user_id(); }
		$args = array(
				'status'        => 'wc-checkout-draft',
				'customer_id'   => $customer_id,
		);
		$order = wc_create_order( $args );
		// Save the order to the database
		$order->save();
		$order_id = $order->get_id();
		if( !$order_id ) { $success = false; }


		/* Set ACF fields */
		if( $success && !update_field( 'customer_id', $customer_id, $order_id ) ) { $success = false; }
		if( $success && !update_field( 'seller_id', get_post_field( 'post_author', $listing_id ), $order_id ) ) { $success = false; }
		if( $success && !update_field( 'waiting_on', $customer_id, $order_id ) ) { $success = false; }
		if( $success && !update_field( 'listing_id', $listing_id, $order_id ) ) { $success = false; }
		if( !$order_amount ) { $order_amount = get_field( 'listing_price', $listing_id ); }
		if( $success && !update_field( 'order_amount', $order_amount, $order_id ) ) { $success = false; }
		
		/* buyNow auto-submits an initial offer */
		CastBack_Action_submitOffer( $order_id, $order_amount, $AJAX );
		
		if( $success ) {
			if( $AJAX ) {
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
				echo $order_id; 
				wp_die();
			} else {
				wp_safe_redirect( esc_url_raw( add_query_arg( 'order_id', $order_id, get_site_url(). '/offers/view-offer/' ) ) );
			}
		} else {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
		}
	} add_action( 'wp_ajax_CastBack_Action_buyNow', 'CastBack_Action_buyNow' );

	/* - Security: CastBack_customerSeller() */
	function CastBack_Action_sendMessage( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( isset( $_POST['new_message'] ) ) { $new_message = $_POST['new_message']; }
		
		if( CastBack_customerSeller( $order_id ) ) {

			if( isset( $order_id ) && isset( $new_message ) ) {
				$row = array(
					'message_date' => wp_date('F j, Y g:i:s a' ),
					'message_text' => $new_message,
					'message_user_id' => get_current_user_id(),
				);
				if( add_row( 'messages', $row, $order_id ) ) {
					// echo CastBack_Offers_drawOrderDetails( $order_id ); 
				}
			}
		}
		
		wp_die();
	} add_action( 'wp_ajax_CastBack_Action_sendMessage', 'CastBack_Action_sendMessage' );
	function CastBack_Action_submitOffer( $order_id = null, $order_amount = false, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( isset( $_POST['order_amount'] ) ) { $order_amount = $_POST['order_amount']; }
		
		if( !CastBack_customerSeller( $order_id ) ) { $success = false; }
		else {
			$success = true;

			ob_start();
			
			/* Expire "last offer" BEFORE adding the new row! */
			$success = CastBack_Action_expireOffer( $order_id, $user_id );

			$order_amount = CastBack_Offers_minimumOfferPrice( $order_amount );

			$row = array(
				'offer_date' => wp_date('F j, Y g:i:s a' ),
				'offer_amount' => number_format( $order_amount, 2 ),
				'offer_user_id' => get_current_user_id(),
			);
			if( !add_row( 'offers', $row, $order_id ) ) { $success = false; }
			
			if( $success && !update_field( 'order_amount', number_format( $order_amount, 2 ), $order_id ) ) { $success = false; }

			$customer_id = get_field( 'customer_id', $order_id );
			$seller_id = get_field( 'seller_id', $order_id );
			
			if( get_current_user_id() == $customer_id ) { $waitingOn = $seller_id; }
			else { $waitingOn = $customer_id; }
			
			if( $success && !update_field( 'waiting_on', $waitingOn, $order_id ) ) { $success = false; }
		}
		
		
		if( $success ) {
			if( $AJAX ) {
				echo $order_id;
				// echo '<script>CastBack_Offers_refreshOrder( '.$order_id.' );</script>'; 
				wp_die();
			} else {
				// wp_safe_redirect( esc_url_raw( add_query_arg( 'order_id', $order_id, get_site_url(). '/buying/offers/' ) ) );
			}
		} else {
			if( $AJAX ) {
				echo $order_id;
				wp_die();
			} else {
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			}
		}
	} add_action( 'wp_ajax_CastBack_Action_submitOffer', 'CastBack_Action_submitOffer' );
	function CastBack_Action_acceptOffer( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		
		if( !CastBack_customerSeller( $order_id ) ) { $success = false; }
		else {
			$success = true;

			$offers = get_field( 'offers', $order_id );
			if( !( end($offers)['offer_expired_date'] ) ) {
				$order_amount = number_format( get_field( 'order_amount', $order_id ), 2 );
				update_field( 'accepted_date', wp_date('F j, Y g:i:s a' ), $order_id );

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
				
				
				
				
				/* Add Shipping to Order */
				$shipping_item = new WC_Order_Item_Shipping();
				$shipping_price = get_field( 'shipping_price', $listing_id );
				$shipping_item->set_method_id( 'shipping:0' );
				if( $shipping_price < 0.01 ) { $shipping_item->set_method_title( 'Free Shipping!' ); }
				else { $shipping_item->set_method_title( '"seller shipping costs"' ); }
				$shipping_item->set_total( $shipping_price );
				$shipping_item->save(); /* Don't forget THIS one... */
				$order->add_item( $shipping_item );
				
				
				/* Save order */
				$order->calculate_totals();
				$order->save();
			}
		}
				
		if( $success ) {
			if( $AJAX ) {
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
				echo $order_id;
				wp_die();
			} else {
				wp_safe_redirect( esc_url_raw( add_query_arg( 'order_id', $order_id, get_site_url(). '/buying/offers/' ) ) );
			}
		} else {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
		}
	} add_action( 'wp_ajax_CastBack_Action_acceptOffer', 'CastBack_Action_acceptOffer' );

	/* - Security: CastBack_customerSeller(), is_admin */
	function CastBack_Action_expireOffer( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		$success = false;
		
		if( CastBack_customerSeller( $order_id ) || is_admin() ) {
			$success = true;
			$offers = get_field( 'offers', $order_id );

			if($offers) {
				$row = array(
						'offer_expired_date'	=>	date( 'F j, Y g:i:s a'  ),
				);
				$success = update_row( 'offers', count($offers), $row, $order_id );
			}
		}
		
		return $success;
			// if( $AJAX ) {
				// echo 'success';
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
				// wp_die();
			// } else {
				// echo 'success';
				// wp_safe_redirect( esc_url_raw( add_query_arg( 'order_id', $order_id, get_site_url(). '/buying/offers/' ) ) );
			// }
		// } else {
			// if( $AJAX ) {
				// echo 'failed';
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			// } else {
				// echo 'failed';
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			// }				
		// }
	} add_action( 'wp_ajax_CastBack_Action_expireOffer', 'CastBack_Action_expireOffer' );

/* Processing Orders */
	/* - Security: n/a */
	function CastBack_Action_orderReceived_redirect(){
			/* we need only thank you page */
			if( is_wc_endpoint_url( 'order-received' ) && isset( $_GET['order_id'] ) ) {
				$redirect_url = get_site_url(). '/offers/view-offer/?order_id=' . $_GET['order_id'];
				wp_redirect( $redirect_url );
				exit;
			}
	} add_action( 'template_redirect', 'CastBack_Action_orderReceived_redirect' );

	/* - Security: CastBack_customerSeller() */
	function CastBack_Action_paymentComplete( $order_id = '' ) {
	if( isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
	// if( isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	
	if( !CastBack_customerSeller( $order_id ) ) { $success = false; }
	else {
		$success = true;
		
		update_field( 'waiting_on', get_field( 'seller_id', $order_id ), $order_id );
		update_field( 'payment_date', wp_date('F j, Y g:i:s a' ), $order_id );
		
		$listing_id = get_field( 'listing_id', $order_id );
		$listing = wc_get_product( $listing_id );
		$listing->set_stock_status( 'outofstock' );
		$listing->save();
	}

	// if($AJAX) { echo $output; wp_die(); }
	return $order_id;
} add_action( 'woocommerce_payment_complete', 'CastBack_Action_paymentComplete' );
	function CastBack_Action_addTracking( $order_id = null, $trackingNumber = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( !$trackingNumber ) { $trackingNumber = $_POST['new_tracking_number']; }
		
		if( !CastBack_customerSeller( $order_id ) ) { $success = false; }
		else {
			$success = true;
			if( isset( $order_id ) ) {
				if( $trackingNumber ) {
					$trackingDate = wp_date('F j, Y g:i:s a' );
					$row = array(
						'tracking_date' => $trackingDate,
						'tracking_number' => $trackingNumber,
						'tracking_user_id' => get_current_user_id(),
					);
					if( $success && !add_row( 'tracking', $row, $order_id ) ) { $success = false; }
					if( $success && !update_field( 'new_tracking_number', '', $order_id ) ) { $success = false; }
					
					$shippedDate = get_field( 'shipped_date', $order_id );
					if( !$shippedDate ) {
						if( $success && !update_field( 'shipped_date', $trackingDate, $order_id ) ) { $success = false; }
						if( $success && !update_field( 'waiting_on', get_field( 'customer_id', $order_id ), $order_id ) ) { $success = false; }
						$order = wc_get_order($order_id);
						if( $success && !$order->update_status('wc-processing') ) { $success = false; }
					}
					$customer_id = get_field( 'customer_id', $order_id );
					$seller_id = get_field( 'seller_id', $order_id );
				
					if( get_current_user_id() == $customer_id ) { $waitingOn = $seller_id; }
					else { $waitingOn = $customer_id; }
					
					if( $success && !update_field( 'waiting_on', $waitingOn, $order_id ) ) { $success = false; }
				} else {
					echo 'Missing tracking number. (a327-09302025)';
				}
			} else {
				echo 'Missing order_id. (a329-09302025)';
			}
		}
		
		if( $success ) {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			wp_die();
		} else {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			echo 'failed';
			wp_die();
		}
	} add_action( 'wp_ajax_CastBack_Action_addTracking', 'CastBack_Action_addTracking' );

	/* - Security: CastBack_customerSeller(), is_admin */
	function CastBack_Action_completeOrder( $order_id = null, $AJAX = false ) {
			if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
			if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
			$success = false;

			if( CastBack_customerSeller( $order_id ) || is_admin() ) {
				$success = true;
				if( $success && !update_field( 'completed_date', wp_date('F j, Y g:i:s a' ), $order_id ) ) { $success = false; }

				$order = wc_get_order($order_id);
				if( $success && !$order->update_status('wc-completed') ) { $success = false; }
			}
			
			if( $success ) {
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
				wp_die();
			} else {
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
				echo 'failed';
				wp_die();
			}
		} add_action( 'wp_ajax_CastBack_Action_completeOrder', 'CastBack_Action_completeOrder' );
		
/* Dispute Orders */
	/* - Security: CastBack_customerSeller(), is_admin */
	function CastBack_Action_disputeOrder( $order_id = '', $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( !$order_id  && isset($_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }

		if( CastBack_customerSeller( $order_id ) || is_admin() ) {
			if( $order_id ) {
				$order = wc_get_order($order_id);
				if( $order ) {
					$order->update_status('on-hold');
					update_field( 'disputed_date', wp_date('F j, Y g:i:s a' ), $order_id );
				} else { echo 'Order #'.$order_id.' not found.'; }
			} else { echo 'no order_id found.'; }
		}
		
		if( $AJAX ) { 
			if( is_admin() ) { wp_safe_redirect( admin_url( 'admin.php?page=wc-orders&status=wc-on-hold' ) ); exit; }
			else { echo $output; wp_die(); }
		}	else { return $output; }
	} add_action( 'wp_ajax_CastBack_Action_disputeOrder', 'CastBack_Action_disputeOrder' );
	function CastBack_Action_removeDispute( $order_id = '', $AJAX = true ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( !$order_id && isset($_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
		$output = '';
		
		if( CastBack_customerSeller( $order_id ) || is_admin() ) {
			if( $order_id ) {
				$order = wc_get_order($order_id);
				if( $order ) {
					CastBack_offers_orderStatus_determine( $order_id );
					update_field( 'disputed_date', '', $order_id );
				} else { $output .= 'Order #'.$order_id.' not found.'; }
			} else { $output .= 'no order_id found.'; }
		}

		if( $AJAX ) { 
			if( is_admin() ) { wp_safe_redirect( admin_url( 'admin.php?page=wc-orders&status=wc-on-hold' ) ); exit; }
			else { echo $output; wp_die(); }
		}	else { return $output; }
	} add_action( 'wp_ajax_CastBack_Action_removeDispute', 'CastBack_Action_removeDispute' );

	/* - Security: n/a, wp_admin hook */
	function CastBack_Action_removeDispute_button( $actions, $order ) {
			// Display the button for all orders that have a 'on-hold / disputed' status
			if ( $order->has_status( array( 'on-hold', 'wc-on-hold', 'disputed' ) ) ) {

					// The key slug defined for your action button
					$action_slug = 'CastBack_Action_removeDispute';
					
					// Set the action button
					$actions[$action_slug] = array(
							'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action='.$action_slug.'&order_id=' . $order->get_id() . '&AJAX=true' ), 'woocommerce-'.$action_slug ),
							'name'      => __( 'Remove Dispute', 'woocommerce' ),
							'action'    => 'CastBack_Action_removeDispute',
					);
			}
			return $actions;
	} add_filter( 'woocommerce_admin_order_actions', 'CastBack_Action_removeDispute_button', 10, 2 );
	function CastBack_Action_removeDispute_button_css() {
			$action_slug = "CastBack_Action_removeDispute"; // The key slug defined for your action button
			
			echo '<style>.wc-action-button-'.$action_slug.'::after { font-family: woocommerce !important; content: "\e029" !important; }</style>';
	} add_action( 'admin_head', 'CastBack_Action_removeDispute_button_css' );