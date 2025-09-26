<?php
/* Listing Actions */
	function CastBack_action_add_listing($atts, $content = null, $AJAX = true ) {
		extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
		
		ob_start();
		
		$product = new WC_Product_Simple();
		$product->set_name( 'My New Listing' );
		$product->set_status( 'draft' ); // 'publish', 'draft', 'pending', etc.
		// $product->set_catalog_visibility( 'visible' ); // 'visible', 'catalog', 'search', 'hidden'
		// $product->set_description( 'This is a detailed description of my new simple product.' );
		// $product->set_short_description( 'A brief summary of the product.' );
		// $product->set_sku( 'MYSIMPLEPROD001' ); // Unique SKU
		// $product->set_price( 25.99 );
		// $product->set_regular_price( 25.99 );
		// $product->set_sale_price( '' ); // Optional: set a sale price
		// $product->set_manage_stock( true ); // Enable stock management
		// $product->set_stock_quantity( 100 );
		// $product->set_stock_status( 'instock' ); // 'instock', 'outofstock'
		// $product->set_backorders( 'no' ); // 'no', 'notify', 'yes'
		// $product->set_reviews_allowed( true );
		// $product->set_sold_individually( false );

		// Set product categories (replace with actual category IDs)
		// $product->set_category_ids( array( 10, 12 ) ); 

		// Save the product
		$product_id = $product->save();
		update_field( 'seller_id', get_current_user_id(), $product_id );
		// if ( $product_id ) {
				// echo "Product '{$product->get_name()}' created successfully with ID: {$product_id}";
		// } else {
				// echo "Error creating product.";
		// }
		
		echo $product_id;
		if($AJAX) { wp_die(); }
	}
add_shortcode('CastBack_action_add_listing', 'CastBack_action_add_listing');
add_action( 'wp_ajax_CastBack_action_add_listing', 'CastBack_action_add_listing' );

/* Order Actions */
	/* Draft Offers */
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
	function CastBack_action_submit_offer( ) {
		if( isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
		if( isset( $_POST['order_amount'] ) ) { $order_amount = $_POST['order_amount']; }
		

		ob_start();
		
		/* Expire "last offer" BEFORE adding the new row! */
		CastBack_action_expire_offer( $order_id, $AJAX );

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
		if($AJAX) { echo ob_get_clean(); wp_die(); }
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
	function CastBack_action_expire_offer( $order_id = null, $AJAX = true ) {
		if( !isset($order_id) ) { $order_id = $_POST['order_id']; }
		
		
		if( $order_id ) {
			$offers = get_field( 'offers', $order_id );
			if($offers) {
				$row = array(
						'offer_expired_date'	=>	date( 'F j, Y g:i a' ),
				);
				update_row( 'offers', count($offers), $row, $order_id );
			}
		}
		
		if($AJAX) { echo $output; wp_die(); }
		else { return $output; }
	} add_action( 'wp_ajax_CastBack_action_expire_offer', 'CastBack_action_expire_offer' );

	/* Processing Orders */
	function CastBack_action_payment_complete( $order_id = '', $AJAX = true ){
		if( !$order_id ) { $order_id = $_POST['order_id']; }
		
		if( $order_id ) {
			update_field( 'waiting_on', get_field( 'seller_id', $order_id ), $order_id );
			update_field( 'payment_date', date('F j, Y g:i a'), $order_id );
		}
			
		if($AJAX) { echo $output; wp_die(); } 
	} add_action( 'woocommerce_payment_complete', 'CastBack_action_payment_complete' );
	function CastBack_action_dispute_order( $order_id = '', $AJAX = true ) {
		if( !$order_id  && isset($_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
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
	} add_action( 'wp_ajax_CastBack_action_dispute_order', 'CastBack_action_dispute_order' );
	function CastBack_action_remove_dispute( $order_id = '', $AJAX = true ) {
		if( !$order_id && isset($_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
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
	} add_action( 'wp_ajax_CastBack_action_remove_dispute', 'CastBack_action_remove_dispute' );
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

/* Admin Actions */
function CastBack_action_remove_dispute_button( $actions, $order ) {
    // Display the button for all orders that have a 'on-hold / disputed' status
    if ( $order->has_status( array( 'on-hold', 'wc-on-hold', 'disputed' ) ) ) {

        // The key slug defined for your action button
        $action_slug = 'CastBack_action_remove_dispute';
        
        // Set the action button
        $actions[$action_slug] = array(
            'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action='.$action_slug.'&order_id=' . $order->get_id() . '&AJAX=true' ), 'woocommerce-'.$action_slug ),
            'name'      => __( 'Remove Dispute', 'woocommerce' ),
            'action'    => 'CastBack_action_remove_dispute',
        );
    }
    return $actions;
} add_filter( 'woocommerce_admin_order_actions', 'CastBack_action_remove_dispute_button', 10, 2 );
function CastBack_action_remove_dispute_button_css() {
    $action_slug = "CastBack_action_remove_dispute"; // The key slug defined for your action button
    
    echo '<style>.wc-action-button-'.$action_slug.'::after { font-family: woocommerce !important; content: "\e029" !important; }</style>';
} add_action( 'admin_head', 'CastBack_action_remove_dispute_button_css' );