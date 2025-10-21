<?php	
/* Listing Actions */
function CastBack_Action_addListing( $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		$success = true;
		
		ob_start();
		$product = new WC_Product_Simple();
		// $product->set_description( 'This is a detailed description of my new simple product.' );
		// $product->set_short_description( 'A brief summary of the product.' );
		// $product->set_sku( 'MYSIMPLEPROD001' ); // Unique SKU
		// $product->set_price( 25.99 );
		// $product->set_regular_price( 25.99 );
		// $product->set_sale_price( '' ); // Optional: set a sale price
		$product->set_status( 'draft' );
		// $product->set_manage_stock( true );
		// $product->set_stock_quantity( 0 );
		$product->set_stock_status( 'instock' );
		// $product->set_backorders( 'no' );
		// $product->set_reviews_allowed( true );
		// $product->set_sold_individually( false );

		// Set product categories (replace with actual category IDs)
		// $product->set_category_ids( array( 10, 12 ) ); 

		// Save the product
		$listing_id = $product->save();
		$product->set_name( 'Listing #'.$listing_id );
		if( $success && !$listing_id = $product->save() ) { $success = false; }
		
		// $product->set_catalog_visibility( 'hidden' );
		// if( $success && !$listing_id = $product->save() ) { $success = false; }
		
		if( $success && !update_field( 'seller_id', get_current_user_id(), $listing_id ) ) { $success = false; }
		if( $success && !update_field( 'listing_id', $listing_id, $listing_id ) ) { $success = false; }
		
		if( $success ) {
			if( $AJAX ) {
				// success
				// echo CastBack_Listings_drawListing( $listing_id, null, false, $AJAX );
				remove_query_arg( 'listing_id' );
				wp_safe_redirect( esc_url_raw( add_query_arg( 'listing_id', $listing_id, get_site_url(). '/selling/edit-listing/' ) ) );				
				
				wp_die();
			}
			else {
				remove_query_arg( 'listing_id' );
				wp_safe_redirect( esc_url_raw( add_query_arg( 'listing_id', $listing_id, get_site_url(). '/selling/edit-listing/' ) ) );		
				// echo do_shortcode('[CastBack page="MyListings"]');
				// echo ob_get_clean();
			}
		} else {
			// failed
			if( $AJAX ) {
				echo 'Action "addListing" failed. (a45-10012025)';
				wp_die();
			} else {
				return 'Action "addListing" failed. (a45-10012025)';
			}
			
			// if( $AJAX ) { echo ob_get_clean(); wp_die(); }
			// else { return ob_get_clean(); }
		}
		
		
	} add_action( 'wp_ajax_CastBack_Action_addListing', 'CastBack_Action_addListing' );

/* Order Actions */
	/* Draft Offers */
	function CastBack_Action_makeOffer( $listing_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
		if( !isset( $listing_id ) && isset( $_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
		$success = true;
		
		$customer_id = get_current_user_id();
				
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
		if( $success && !update_field( 'order_amount', get_field( 'listing_price', $listing_id ), $order_id ) ) { $success = false; }


		// ob_start();

		// if ( $order ) {
				// $output .= '<script>window.location.href = "'.get_site_url().'/buying/offers/?order_id='.$order_id.'";</script>';
		// } else {
				// $output .= "Failed to create new order.";
		// }
		
		if( $success ) {
			if( $AJAX ) {
				echo CastBack_Offers_drawOrderDetails( $order_id ); 
				wp_die();
			} else {
				wp_safe_redirect( esc_url_raw( add_query_arg( 'order_id', $order_id, get_site_url(). '/buying/offers/' ) ) );
			}
		} else {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
		}
	} add_action( 'wp_ajax_CastBack_Action_makeOffer', 'CastBack_Action_makeOffer' );
	function CastBack_Action_sendMessage( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( isset( $_POST['new_message'] ) ) { $new_message = $_POST['new_message']; }
		
		if( isset( $order_id ) && isset( $new_message ) ) {
			$row = array(
				'message_date' => date('F j, Y g:i a'),
				'message_text' => $new_message,
				'message_user_id' => get_current_user_id(),
			);
			if( add_row( 'messages', $row, $order_id ) ) {
				echo CastBack_Offers_drawOrderDetails( $order_id ); 
			}
		}
		
		wp_die();
	} add_action( 'wp_ajax_CastBack_Action_sendMessage', 'CastBack_Action_sendMessage' );
	function CastBack_Action_submitOffer( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( isset( $_POST['order_amount'] ) ) { $order_amount = $_POST['order_amount']; }
		
		if( isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
		if( !CastBack_Offers_customerSeller( $order_id, $user_id ) ) { $success = false; }
		else {
			$success = true;

			ob_start();
			
			/* Expire "last offer" BEFORE adding the new row! */
			$success = CastBack_Action_expireOffer( $order_id, $user_id );

			$row = array(
				'offer_date' => date('F j, Y g:i a'),
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
				echo CastBack_Offers_drawOrderDetails( $order_id ); 
				wp_die();
			} else {
				// wp_safe_redirect( esc_url_raw( add_query_arg( 'order_id', $order_id, get_site_url(). '/buying/offers/' ) ) );
			}
		} else {
			if( $AJAX ) {
				echo CastBack_Offers_drawOrderDetails( $order_id ); 
				wp_die();
			} else {
				// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			}
		}
	} add_action( 'wp_ajax_CastBack_Action_submitOffer', 'CastBack_Action_submitOffer' );
	function CastBack_Action_acceptOffer( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		
		if( isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
		if( !CastBack_Offers_customerSeller( $order_id, $user_id ) ) { $success = false; }
		else {
			$success = true;

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
		}
				
		if( $success ) {
			if( $AJAX ) {
				echo CastBack_Offers_drawOrderDetails( $order_id ); 
				wp_die();
			} else {
				wp_safe_redirect( esc_url_raw( add_query_arg( 'order_id', $order_id, get_site_url(). '/buying/offers/' ) ) );
			}
		} else {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
		}
	} add_action( 'wp_ajax_CastBack_Action_acceptOffer', 'CastBack_Action_acceptOffer' );
	function CastBack_Action_expireOffer( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }

		
		if( isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
		if( !CastBack_Offers_customerSeller( $order_id, $user_id ) ) { $success = false; }
		else {
			$success = true;
			$offers = get_field( 'offers', $order_id );

			if($offers) {
				$row = array(
						'offer_expired_date'	=>	date( 'F j, Y g:i a' ),
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
	function CastBack_Action_paymentComplete( $order_id = '' ) {
		if( isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		// if( isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		update_field( 'waiting_on', get_field( 'seller_id', $order_id ), $order_id );
		update_field( 'payment_date', date('F j, Y g:i a'), $order_id );
		
		$listing_id = get_field( 'listing_id', $order_id );
		$listing = wc_get_product( $listing_id );
		$listing->set_stock_status( 'outofstock' );
		$listing->save();
	
		// if($AJAX) { echo $output; wp_die(); }
		return $order_id;
	} add_action( 'woocommerce_payment_complete', 'CastBack_Action_paymentComplete' );
	function CastBack_Action_disputeOrder( $order_id = '', $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( !$order_id  && isset($_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
		
		if( $order_id ) {
			$order = wc_get_order($order_id);
			if( $order ) {
				$order->update_status('on-hold');
				update_field( 'disputed_date', date('F j, Y g:i a'), $order_id );
			} else { echo 'Order #'.$order_id.' not found.'; }
		} else { echo 'no order_id found.'; }
		
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
		
		if( $order_id ) {
			$order = wc_get_order($order_id);
			if( $order ) {
				CastBack_offers_orderStatus_determine( $order_id );
				update_field( 'disputed_date', '', $order_id );
			} else { $output .= 'Order #'.$order_id.' not found.'; }
		} else { $output .= 'no order_id found.'; }
		

		if( $AJAX ) { 
			if( is_admin() ) { wp_safe_redirect( admin_url( 'admin.php?page=wc-orders&status=wc-on-hold' ) ); exit; }
			else { echo $output; wp_die(); }
		}	else { return $output; }
	} add_action( 'wp_ajax_CastBack_Action_removeDispute', 'CastBack_Action_removeDispute' );
	function CastBack_Action_addTracking( $order_id = null, $trackingNumber = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( !$trackingNumber ) { $trackingNumber = $_POST['new_tracking_number']; }
		$success = true;
		
		if( isset( $order_id ) ) {
			if( $trackingNumber ) {
				$trackingDate = date('F j, Y g:i a');
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
		
		if( $success ) {
			echo CastBack_Offers_drawOrderDetails( $order_id ); 
			wp_die();
		} else {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			echo 'failed';
			wp_die();
		}
	} add_action( 'wp_ajax_CastBack_Action_addTracking', 'CastBack_Action_addTracking' );
	function CastBack_Action_completeOrder( $order_id = null, $AJAX = false ) {
		if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
		if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		$success = true;
		
		if( $success && !update_field( 'completed_date', date('F j, Y g:i a'), $order_id ) ) { $success = false; }

		$order = wc_get_order($order_id);
		if( $success && !$order->update_status('wc-completed') ) { $success = false; }
		
		if( $success ) {
			echo CastBack_Offers_drawOrderDetails( $order_id ); 
			wp_die();
		} else {
			// echo CastBack_Offers_drawOrderDetails( $order_id ); 
			echo 'failed';
			wp_die();
		}
	} add_action( 'wp_ajax_CastBack_Action_completeOrder', 'CastBack_Action_completeOrder' );

/* Admin Actions */
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