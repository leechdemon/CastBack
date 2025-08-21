<?php

function CastBack_make_offer($atts, $content = null) {
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
	$customer_id = get_current_user_id();
	update_field( 'customer_id', $customer_id, $order_id );

	$seller_id = get_the_author_ID();
	update_field( 'seller_id', $seller_id, $order_id );
	
	update_field( 'listing_id', $listing_id, $order_id );
	
	//
	
	$listing_price = get_field( 'listing_price', $listing_id );
	update_field( 'order_amount', $listing_price, $order_id );

	update_field( 'waiting_on', $customer_id, $order_id );

	/* Set Order Details */
	$quantity = 1;
	$args = array(
		// 'name'         => $product->get_name(),
		// 'tax_class'    => $product->get_tax_class(),
		// 'product_id'   => $product->is_type( ProductType::VARIATION ) ? $product->get_parent_id() : $product->get_id(),
		// 'variation_id' => $product->is_type( ProductType::VARIATION ) ? $product->get_id() : 0,
		// 'variation'    => $product->is_type( ProductType::VARIATION ) ? $product->get_attributes() : array(),
		'subtotal'     		=> $listing_price,
		'total'						=> $listing_price,
		// 'total'       	 => $total,
		// 'quantity'     => $qty,
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
	
	$url = get_site_url().'/buying/make-offer/?listing_id='.$listing_id;
	
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
function CastBack_offers($atts, $content = null) {

	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null, 'view' => null ), $atts));

	ob_start();
	
	if( $_GET['action'] ) {
		$order_id = $_GET['order_id'];
		$order = wc_get_order($order_id);

		if( $_GET['action'] == 'submit_offer' ) {
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
			update_field( 'accepted_date', date('F j, Y g:i a'), $order_id );

			// WaitingOnToggle();
			/* force WaitingOn to buyer */
			update_field( 'waiting_on', get_field( 'customer_id', $order_id ), $order_id );
			
			$order->update_status('wc-payment');
		}
		if( $_GET['action'] == 'submit_payment' ) {
			$order_id = $_GET['order_id'];
			update_field( 'payment_date', date('F j, Y g:i a'), $order_id );

			// WaitingOnToggle();
			/* force WaitingOn to seller */
			update_field( 'waiting_on', get_field( 'seller_id', $order_id ), $order_id );
			
			$order->update_status('wc-processing');
		}
		if( $_GET['action'] == 'ship_order' ) {
			$order_id = $_GET['order_id'];
			update_field( 'shipped_date', date('F j, Y g:i a'), $order_id );

			// WaitingOnToggle();
			/* force WaitingOn to seller */
			update_field( 'waiting_on', get_field( 'customer_id', $order_id ), $order_id );
			
			$order->update_status('wc-processing');
		}
		if( $_GET['action'] == 'complete_order' ) {
			$order_id = $_GET['order_id'];
			update_field( 'completed_date', date('F j, Y g:i a'), $order_id );

			// WaitingOnToggle();
			/* force WaitingOn to seller */
			update_field( 'waiting_on', get_field( 'customer_id', $order_id ), $order_id );
			
			$order->update_status('wc-completed');
		}
		
		/* Reload page to drop $_GET */
		echo '<script>window.location.href = location.protocol + "//" + location.host + location.pathname + "?order_id=" + ' .$order_id. ';</script>';
	}
	
	if( $_GET['order_id'] ) {
		
		$order_id = $_GET['order_id'];
		$customer_id = get_field( 'customer_id', $order_id );
		$seller_id = get_field( 'seller_id', $order_id );
		
		/* Display the Order */
		echo '<h3>Order #'.$order_id.'</h3>';
		
		$waitingOn = get_field( 'waiting_on', $order_id );
		$name = get_userdata( $waitingOn );
		$order = wc_get_order( $order_id );
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
		echo '<h5 style="margin-left: 1rem;">Order Status: '.$orderStatusCosmetic.'</h5>';
		if( $name ) { echo '<h5 style="margin-left: 1rem;">Waiting On: '.$name->first_name .' '.$name->last_name.'</h5>'; }
		echo '<br>';

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
				echo '<div style="max-width: 288px; float: right; display: block;">';
				echo do_shortcode('[elementor-template id="822"]');
				echo '</div>';
			}
		} else {
			// No posts found
			echo '<p>No posts found matching your criteria.</p>';
		}
		wp_reset_postdata();

		/* Display Offer History */
		$offers = get_field( 'offers', $_GET['order_id'] );
		if( $offers ) {
			echo '<style>#offer_history { display: table; width: auto; border: solid 1px black; }</style>';
			echo '<style>.offer_history_item:nth-child(odd) { background-color: #d4efef; }</style>';
			echo '<style>.offer_history_item:nth-child(even) { background-color: #aad3d3; }</style>';
			echo '<style>.offer_history_item  { width: 100%; float: left; }</style>';
			echo '<style>.offer_history_subitem  { float: left; text-align: right; padding: 0.5rem; }</style>';

			echo '<div id="offer_history">';
			foreach( $offers as $offer ) {
				echo '<div class="offer_history_item">';
					echo '<div class="offer_history_subitem" style="width: 35%;">'. $offer['offer_date'] . '</div>';
					echo '<div class="offer_history_subitem" style="width: 15%;">$'. $offer['offer_amount'] . '</div>';
					$name = get_userdata( $offer['offer_user_id'] );
					echo '<div class="offer_history_subitem" style="width: auto;">'. $name->first_name .' '.$name->last_name . '</div>';
					// echo '<div style="width: 30%; float: left;">'. $name. '</div>';
				echo '</div>'; // end order history item
			}
			echo '</div>'; // end order history
		}
		
		/* Display Buttons / ACF Form */
		acf_form_head();
		if( get_current_user_id() == $waitingOn && !get_field( 'accepted_date', $order_id ) ) {
			echo '<div style="width: 50%; float: left;">';

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
			acf_form(array(
				'post_id'   => $_GET['order_id'],
				// 'status'    => 'wc-pending', // Set initial status, e.g., 'wc-pending', 'wc-processing', 'wc-completed'
				// 'field_groups' => array('group_689a2f343751f',),
				'uploader'		=> 'basic',
				'submit_value' => 'Accept Offer',
				// 'form' => false,
				'return' => '?order_id='.$_GET['order_id'].'&action=accept_offer'
			));
	
			echo '</div>';
		}		
		if( get_current_user_id() == $waitingOn && get_field( 'accepted_date', $order_id ) && !get_field( 'payment_date', $order_id ) ) {
			echo '<div style="width: 50%; float: left;">';

			/* Accept Offer */
			acf_form(array(
				'post_id'   => $_GET['order_id'],
				// 'status'    => 'wc-processing', // Set initial status, e.g., 'wc-pending', 'wc-processing', 'wc-completed'
				// 'field_groups' => array('group_689a2f343751f',),
				'uploader'		=> 'basic',
				'submit_value' => 'Submit Payment',
				// 'form' => false,
				'return' => '?order_id='.$_GET['order_id'].'&action=submit_payment'
			));
	
			echo '</div>';
		}
		if( get_current_user_id() == $waitingOn && get_field( 'payment_date', $order_id ) && !get_field( 'shipped_date', $order_id ) ) {
			echo '<div style="width: 50%; float: left;">';

			/* Accept Offer */
			acf_form(array(
				'post_id'   => $_GET['order_id'],
				// 'status'    => 'wc-processing', // Set initial status, e.g., 'wc-pending', 'wc-processing', 'wc-completed'
				// 'field_groups' => array('group_689a2f343751f',),
				'uploader'		=> 'basic',
				'submit_value' => 'Ship Order',
				// 'form' => false,
				'return' => '?order_id='.$_GET['order_id'].'&action=ship_order'
			));
	
			echo '</div>';
		}
		if( get_current_user_id() == $waitingOn && get_field( 'shipped_date', $order_id ) && !get_field( 'completed_date', $order_id ) ) {
			echo '<div style="width: 50%; float: left;">';

			/* Accept Offer */
			acf_form(array(
				'post_id'   => $_GET['order_id'],
				// 'status'    => 'wc-processing', // Set initial status, e.g., 'wc-pending', 'wc-processing', 'wc-completed'
				// 'field_groups' => array('group_689a2f343751f',),
				'uploader'		=> 'basic',
				'submit_value' => 'Complete Order',
				// 'form' => false,
				'return' => '?order_id='.$_GET['order_id'].'&action=complete_order'
			));
	
			echo '</div>';
		}
		
	} else {
		/* Display list of Offers for buyer/seller */
		$args = array(
			// 'status' => 'wc-processing', // Get completed orders
			// 'limit'  => 10,           // Retrieve up to 10 orders
			'orderby' => 'date',      // Order by date
			'order'  => 'DESC',  
			// 'customer_id'  => get_current_user_id(),  
		);

		$orders = wc_get_orders( $args );
		foreach( $orders as $order ) {
			$orderCount++;
			$order_id = $order->get_id();
			echo '<div class=""><a href="?order_id='.$order_id.'">Order #' .$order_id .'</a></div>';
			// echo do_shortcode('[elementor-template id="822"]');
		}
		if( $orderCount < 1 ) {
			echo '<div>You have no orders.</div>';
		}
		
	}
	return ob_get_clean();
} add_shortcode('CastBack_offers', 'CastBack_offers');