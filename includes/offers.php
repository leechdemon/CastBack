<?php
function CastBack_make_offer($atts, $content = null) {
	$customer_id = get_current_user_id();
	$listing_id = $_GET['listing_id'];
	$args = array(
			'status'        => 'wc-checkout-draft', // Set initial status, e.g., 'wc-pending', 'wc-processing', 'wc-completed'
			'customer_id'   => $customer_id,         // Optional: associate with an existing customer ID
			// 'customer_note' => null,         // Optional: add a customer note
			// Add other arguments as needed, e.g., 'parent', 'created_via', 'cart_hash'
	);
	$order = wc_create_order( $args );
	$order_id = $order->get_id();
	
	/* Set ACF fields */
	update_field( 'customer_id', $customer_id, $order_id );

	$seller_id = get_the_author_ID();
	update_field( 'seller_id', $seller_id, $order_id );
	
	update_field( 'listing_id', $listing_id, $order_id );
	
	//
	
	$listing_price = get_field( 'listing_price', $listing_id );
	update_field( 'order_amount', $listing_price, $order_id );

	update_field( 'waiting_on', $customer_id, $order_id );

	/* Set Order Details */
// $quantity = 1;
// $args = array(
		// 'name'         => $product->get_name(),
		// 'tax_class'    => $product->get_tax_class(),
		// 'product_id'   => $product->is_type( ProductType::VARIATION ) ? $product->get_parent_id() : $product->get_id(),
		// 'variation_id' => $product->is_type( ProductType::VARIATION ) ? $product->get_id() : 0,
		// 'variation'    => $product->is_type( ProductType::VARIATION ) ? $product->get_attributes() : array(),
	// 'subtotal'     		=> $listing_price,
	// 'total'						=> $listing_price,
		// 'total'       	 => $total,
		// 'quantity'     => $qty,
// );
// $order->add_product( wc_get_product( $listing_id ), $quantity, $args );

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

	ob_start();

	if ( $order ) {
			echo '<script>window.location.href = "'.get_site_url().'/buying/offers/?order_id='.$order_id.'";</script>';
	} else {
			echo "Failed to create new order.";
	}
	
	return ob_get_clean();
} add_shortcode('CastBack_make_offer', 'CastBack_make_offer');
function CastBack_make_offer_URL($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	
	if( get_current_user_id() ) { $url = get_site_url().'/buying/make-offer/?listing_id='.$listing_id; }
	else { $url = get_site_url().'/wp-login.php'; }
	
	ob_start();
	echo '<a class="button" href="'.$url.'">Make Offer</a>';
	return ob_get_clean();
} add_shortcode('CastBack_make_offer_URL', 'CastBack_make_offer_URL');
function WaitingOnToggle() {
	$customer_id = get_field( 'customer_id', $_GET['order_id'] );
	$seller_id = get_field( 'seller_id', $_GET['order_id'] );
	
	if( get_current_user_id() == $customer_id ) { $waitingOn = $seller_id; }
	else { $waitingOn = $customer_id; }
	
	update_field( 'waiting_on', $waitingOn, $_GET['order_id'] );
}
function CastBack_offers_actions() {
    $order_id = $_GET['order_id'];
		$order = wc_get_order($order_id);
		
		$new_message = get_field( 'new_message', $order_id );
		if( $new_message ) {
			$row = array(
				'message_date' => date('F j, Y g:i a'),
				'message_text' => $new_message,
				'message_user_id' => get_current_user_id(),
			);
			add_row( 'messages', $row, $order_id );
			update_field( 'new_message', '', $order_id );
		}

		if( $_GET['action'] == 'submit_offer' ) {			
			/* Expire "last offer" AFTER adding the new row! */
			castback_offer_expiration( $order_id );

			$order_amount = number_format( get_field( 'order_amount', $order_id ), 2 );
			$row = array(
				'offer_date' => date('F j, Y g:i a'),
				'offer_amount' => $order_amount,
				'offer_user_id' => get_current_user_id(),
			);
			add_row( 'offers', $row, $order_id );
			update_field( 'order_amount', $order_amount, $order_id );
			WaitingOnToggle();
		}
		if( $_GET['action'] == 'accept_offer' ) {
			$offers = get_field( 'offers', $order_id );
			if( !( end($offers)['offer_expired_date'] ) ) {
				$order_amount = number_format( get_field( 'order_amount', $order_id ), 2 );
				update_field( 'accepted_date', date('F j, Y g:i a'), $order_id );

				// WaitingOnToggle();
				/* force WaitingOn to buyer */
				update_field( 'waiting_on', get_field( 'customer_id', $order_id ), $order_id );
				
				
				
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
		if( $_GET['action'] == 'send_message' ) {
			/* Handled in "action is set" wrapper */
		}
		if( $_GET['action'] == 'submit_payment' ) {
			// $order_id = $_GET['order_id'];
			// update_field( 'payment_date', date('F j, Y g:i a'), $order_id );

			// WaitingOnToggle();
			/* force WaitingOn to seller */
			// update_field( 'waiting_on', get_field( 'seller_id', $order_id ), $order_id );
			
			// $order->update_status('wc-processing');
		}
		if( $_GET['action'] == 'add_tracking' ) {
			$order_id = $_GET['order_id'];
			$trackingNumber = get_field( 'new_tracking_number', $order_id );
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
					$order->update_status('wc-processing');
				}

				// echo '';				
			} else {
				// do nothing
			}
		}
		if( $_GET['action'] == 'complete_order' ) {
			$order_id = $_GET['order_id'];
			Castback_tools_complete_order( $order_id );
		}
		if( $_GET['action'] == 'dispute_order' ) {
			$order_id = $_GET['order_id'];
			update_field( 'disputed_date', date('F j, Y g:i a'), $order_id );
		}
		
		/* Reload page to drop $_GET */
		echo '<script>window.location.href = location.protocol + "//" + location.host + location.pathname + "?order_id=" + ' .$order_id. ';</script>';
}
function CastBack_offers($atts, $content = null) {

	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null, 'view' => null ), $atts));

	ob_start();
	
	/* Replace this block with AJAX methods */
	if( $_GET['action'] ) { CastBack_offers_actions(); }
	if( $_GET['order_id'] ) { /* Display the Order */
		$order_id = $_GET['order_id'];
		$customer_id = get_field( 'customer_id', $order_id );
		$seller_id = get_field( 'seller_id', $order_id );
		
		echo '<style>
		#castback-order { display: inline-block; width: fit-content; float: left; margin-bottom: 1rem; }
		#castback-sidebar { width: 35%; display: inline-block; float: right; margin-bottom: 1rem; }
		#castback-order .acf-form-submit { margin: 0 1rem 1rem 0; }
		
		.castback-order-listing { float: left; padding: 1rem; padding: 1rem; }
		.castback-order-details { width: fit-content; }
		
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
		
		echo '<h3>Order #'.$order_id.'</h3>';
		echo '<div id="castback-order">'; /* Open Order Block */
			echo '<div class="castback-order-details">';
				
				$waitingOn = get_field( 'waiting_on', $order_id );
				$name = get_userdata( $waitingOn );
				
				$order = wc_get_order( $order_id );
				if( $order ) { /* If null, don't display it... */
					$orderStatus = $order->get_status();
				
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
								$orderStatusCosmetic = 'Processing (Awaiting Shipment)';
							}
							break;
						case 'completed':
							$orderStatusCosmetic = 'Complete';
							break;
					}
					
					$disputedDate = get_field( 'disputed_date', $order_id );
					if( $disputedDate ) { $orderStatusCosmetic = 'Order Disputed'; }
					
					echo '<h5 style="margin-left: 1rem;">Order Status: '.$orderStatusCosmetic.'</h5>';
					if( $name && $orderStatus != 'completed' ) { echo '<h5 style="margin-left: 1rem;">Waiting On: '.$name->first_name .' '.$name->last_name.'</h5>'; }

					/* Display the Listing */
					$listing_id = get_field( 'listing_id', $order_id );
					$args = array(
							'p'							 =>	$listing_id,
							'post_type'      => 'product', // or 'page', 'custom_post_type'
							'posts_per_page' => 1,
					);
					$custom_query = new WP_Query( $args );
					if ( $custom_query->have_posts() ) {
						while ( $custom_query->have_posts() ) {
							$custom_query->the_post();
							echo '<div class="castback-order-listing">';
							echo do_shortcode('[elementor-template id="822"]');
							echo '</div>';
						}
					} else {
						// No posts found
						echo '<p>No posts found matching your criteria.</p>';
					}
					wp_reset_postdata();
		
					/* Display Buttons / ACF Form */
					CastBack_offers_acf_buttons( $order_id, $orderStatus );
	
				echo '</div>';
				echo '</div>';/* close castback-order */
				
			} else {
				echo  "</div>";
				echo  "order not found.";
			}

		CastBack_offers_sidebar( $order_id );
	}
	else { /* Display list of Offers for buyer/seller */
		$args = array(
			// 'status' => 'wc-processing', // Get completed orders
			// 'limit'  => 10,           // Retrieve up to 10 orders
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
			if( $offers[0]['offer_amount'] ) {
				$orderCount++;
				echo '<div class=""><a href="?order_id='.$order_id.'">Order #' .$order_id .'</a></div>';
			}
		}
		if( $orderCount < 1 ) {
			echo '<div>You have no orders.</div>';
		}
		
	}
	
	return ob_get_clean();
} add_shortcode('CastBack_offers', 'CastBack_offers');

function CastBack_offers_sidebar( $order_id ) {
	echo '<div id=castback-sidebar>';
	
	/* Display History */
	echo '<h5 style="">Order History</h5>';
	/* Display Offer History */
	echo '<div class="order_history">';

	$offers = get_field( 'offers', $order_id  );
	if( $offers ) {

		foreach( $offers as $offer ) {
			if( $offer['offer_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
			else { $customerOrSeller = ' seller'; }
			
			echo '<div class="order_history_item'.$customerOrSeller.'" style="order: '.strtotime( $offer['offer_date'] ).';">';
				echo '<div class="order_history_subitem date">'. $offer['offer_date'] . '</div>';

				$name = get_userdata( $offer['offer_user_id'] );
				if( $offer['offer_expired_date'] ) { $offerExpired = ' offer_expired'; } else { $offerExpired = ''; }
				echo '<div class="order_history_subitem'.$offerExpired.'">'. $name->first_name .' '.$name->last_name . ' made an Offer of $'. $offer['offer_amount'].'</div>';
			echo '</div>'; // end order history item
		}
			
			/* Display Message History */
			$messages = get_field( 'messages', $order_id  );
			if( $messages ) {
				foreach( $messages as $message ) {
					if( $message['message_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
					else { $customerOrSeller = ' seller'; }
					
					echo '<div class="order_history_item'.$customerOrSeller.'" style="order: '.strtotime( $message['message_date'] ).';">';
						echo '<div class="order_history_subitem date">'. $message['message_date'] . '</div>';

						$name = get_userdata( $message['message_user_id'] );
						echo '<div class="order_history_subitem">'. $name->first_name .' '.$name->last_name . ': "'.$message['message_text'].'"</div>';
					echo '</div>'; // end order history item
				}
			}

			/* Display Tracking History */
			$trackingLabels = get_field( 'tracking', $order_id  );
			if( $trackingLabels ) {
				foreach( $trackingLabels as $tracking ) {
					if( $tracking['tracking_user_id'] == get_field( 'customer_id', $order_id ) ) { $customerOrSeller = ' customer'; }
					else { $customerOrSeller = ' seller'; }
					
					echo '<div class="order_history_item'.$customerOrSeller.'" style="order: '.strtotime( $tracking['tracking_date'] ).';">';
						echo '<div class="order_history_subitem date">'. $tracking['tracking_date'] . '</div>';

						$name = get_userdata( $tracking['tracking_user_id'] );
						echo '<div class="order_history_subitem">'. $name->first_name .' '.$name->last_name . ' added Tracking #<a href="'.get_site_url().'/?tracking='.$tracking['tracking_number'].'">'.$tracking['tracking_number'].'</a></div>';
					echo '</div>'; // end order history item
				}
			}

			/* Display Status Changes */
			$acceptedDate = get_field( 'accepted_date', $order_id );
			if( $acceptedDate ) {
				echo '<div class="order_history_item seller" style="order: '.strtotime( $acceptedDate ).';">';
					echo '<div class="order_history_subitem date">'. $acceptedDate . '</div>';
					echo '<div class="order_history_subitem">Offer Accepted</div>';
				echo '</div>';
			}
			$paymentDate = get_field( 'payment_date', $order_id );
			if( $paymentDate ) {
				echo '<div class="order_history_item customer" style="order: '.strtotime( $paymentDate ).';">';
					echo '<div class="order_history_subitem date">'. $paymentDate . '</div>';
					echo '<div class="order_history_subitem">Order Paid</div>';
				echo '</div>';
			}
			$shippedDate = get_field( 'shipped_date', $order_id );
			if( $shippedDate ) {
				echo '<div class="order_history_item seller" style="order: '.strtotime( $shippedDate ).';">';
					echo '<div class="order_history_subitem date">'. $shippedDate . '</div>';
					echo '<div class="order_history_subitem">Order Shipped</div>';
				echo '</div>';
			}
			$completedDate = get_field( 'completed_date', $order_id );
			if( $completedDate ) {
				echo '<div class="order_history_item customer" style="order: '.strtotime( $completedDate ).';">';
					echo '<div class="order_history_subitem date">'. $completedDate . '</div>';
					echo '<div class="order_history_subitem">Order Completed</div>';
				echo '</div>';
			}
			$disputedDate = get_field( 'disputed_date', $order_id );
			if( $disputedDate ) {
				echo '<div class="order_history_item customer" style="order: '.strtotime( $disputedDate ).';">';
					echo '<div class="order_history_subitem date">'. $disputedDate . '</div>';
					echo '<div class="order_history_subitem">Order was Disputed. CastBack support will be in touch soon...</div>';
				echo '</div>';
			}
			echo '</div>'; // end order history					

			/* Display Messaging Window */
			/* (Only with existing offers) */
			acf_form_head();
			echo '<div class="acf_messages">';

			/* Send Message */
			acf_form(array(
				'post_id'   => $order_id,
				'field_groups' => array('group_689a2f343751f',),
				'uploader'		=> 'basic',
				'submit_value' => 'Send Message',
				// 'form' => false,
				'return' => '?order_id='.$order_id.'&action=send_message'
			));
		}
		
		echo '</div>';
		echo '</div>'; // end Sidebar
}
function CastBack_offers_acf_buttons( $order_id, $orderStatus ) {
    $disputedDate = get_field( 'disputed_date', $order_id );
    $waitingOn = get_field( 'waiting_on', $order_id );
    $order = wc_get_order( $order_id );
    
    if( $disputedDate == '' ) {
        
        /* Accept / Submit Offer */
    	if( $orderStatus == 'checkout-draft' && get_current_user_id() == $waitingOn ) {
				 acf_form_head(); 
				 echo '<div class="acf_offers">';

    		/* Submit Offer */
    	    acf_form(array(
	    		'post_id'   => $_GET['order_id'],
	    		'field_groups' => array('group_689a2f343751f',),
	    		'uploader'		=> 'basic',
	    		'submit_value' => 'Submit Offer',
	    		// 'form' => false,
	    		'return' => '?order_id='.$_GET['order_id'].'&action=submit_offer'
	    	));
    		/* Accept Offer */
    		$offers = get_field( 'offers', $order_id  );
	    	if( $offers ) {
            	acf_form(array(
	    			'post_id'   => $_GET['order_id'],
	    			'uploader'		=> 'basic',
    				'submit_value' => 'Accept Offer',
	    			// 'form' => false,
	    			'return' => '?order_id='.$_GET['order_id'].'&action=accept_offer'
	    		));
    		}

    		echo '</div>';
    	}		
    	/* Submit Payment */
    	if( $orderStatus == 'pending' && get_current_user_id() == $waitingOn ) {
    		echo '<div class="acf_offers">';
					echo '<a class="button" href="'. $order->get_checkout_payment_url() .'">Pay Order</a>';
				echo '</div>';
    	}
			
    	/* Ship Order */
    	if( $orderStatus == 'processing' ) {
        	if( get_current_user_id() == get_field( 'seller_id', $order_id ) ) {
    			$displayShipping = true;
        	}
    		else if( get_field( 'shipped_date', $order_id ) ) { $displayShipping = true; }
    		
	    	if( $displayShipping ) {
	    		echo '<div class="acf_shipping">';
					acf_form_head();

    			acf_form(array(
	    			'post_id'   => $_GET['order_id'],
	    			'uploader'		=> 'basic',
	    			'submit_value' => 'Add Tracking',
	    			'field_groups' => array('group_689a2f343751f',),
                    'return' => '?order_id='.$_GET['order_id'].'&action=add_tracking'
	    		));
				
    			echo '</div>';
    		}
       	    /* Complete Order */
    	    if( get_current_user_id() == $waitingOn && get_field( 'shipped_date', $order_id ) ) {
    			echo '<div class="acf_offers">';
	
    			acf_form(array(
    				'post_id'   => $_GET['order_id'],
    				'uploader'		=> 'basic',
	    			'submit_value' => 'Complete Order',
	    			'return' => '?order_id='.$order_id.'&action=complete_order'
	    		));
				
	    	   	echo '</div>';
    		}
    	//	if( get_current_user_id() == get_field( 'customer_id', $order_id ) ) {
    			echo '<div class="acf_dispute">';
		        // acf_form_head();
		
    			acf_form(array(
	    			'post_id'   => $_GET['order_id'],
                    'uploader'		=> 'basic',
	    			'submit_value' => 'Dispute Order',
                    'return' => '?order_id='.$order_id.'&action=dispute_order'
	    		));
	    			
    			echo '</div>';
	    //	}
        }
    }
}
function castback_offer_expiration( $order_id ) {
	$offers = get_field( 'offers', $order_id );
	$row = array(
	    'offer_expired_date'	=>	date( 'F j, Y g:i a' ),
	);
	update_row( 'offers', count($offers), $row, $order_id );
}

function so_payment_complete( $order_id ){
    // $order = wc_get_order( $order_id );
		update_field( 'waiting_on', get_field( 'seller_id', $order_id ), $order_id );
		update_field( 'payment_date', date('F j, Y g:i a'), $order_id );

		
		
} add_action( 'woocommerce_payment_complete', 'so_payment_complete' );