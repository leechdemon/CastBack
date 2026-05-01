<?php
function Recast_filter_listings_status_update( $post_id ) {
	$product = wc_get_product( $post_id );
	if( $product && isset ( $_POST['acf']['field_688ce497d2c30'] ) ) {
		$set_status( $_POST['acf']['field_688ce497d2c30'] );
		$product->save();
	}

	return $post_id;
} add_filter('acf/pre_save_post' , 'Recast_filter_listings_status_update', 10, 1 );

function Recast_Filters_updateListing( $post_id, $taxSlug = null, $termSlug = null, $append = false ) {
	$product = wc_get_product( $post_id );
	if( $product ) {
		/* Auto-Taxonomies */
		if( isset ( $_POST['acf'] ) ) {
			foreach( $_POST['acf'] as $tax => $term ) {
				$taxonomy = get_taxonomy( $tax );
				if( $taxonomy && $term != 0 ) {
					/* Ignore blank fields */
					wp_set_object_terms( $post_id, $term, $tax, $append );
				}
			}
		}
		
		/* Description */
		if( isset ( $_POST['acf']['field_68eec29bf173c'] ) ) { /* Listing > Listing Price */
			update_field( 'description', $_POST['acf']['field_68eec29bf173c'], $post_id );
			$product->set_description( get_field( 'description', $post_id ) );
		}
		
		/* Format Prices */
		if( isset ( $_POST['acf']['field_68964c94355ed'] ) && $_POST['acf']['field_68964c94355ed'] != '' ) { /* Listing > Listing Price */
			update_field( 'listing_price', Recast_Filter_formatPriceField( $_POST['acf']['field_68964c94355ed'] ), $post_id );
			$product->set_regular_price( get_field( 'listing_price', $post_id ) );
		}
		if( isset ( $_POST['acf']['field_68964cb9355ee'] ) && $_POST['acf']['field_68964cb9355ee'] != '' ) { /* Listing > MSRP */
			update_field( 'msrp', Recast_Filter_formatPriceField( $_POST['acf']['field_68964cb9355ee'] ), $post_id );
		}
		if( isset ( $_POST['acf']['field_68e55d2432a2c'] ) && $_POST['acf']['field_68e55d2432a2c'] != '' ) { /* Listing > Shipping Price */
			update_field( 'shipping_price', Recast_Filter_formatPriceField( $_POST['acf']['field_68e55d2432a2c'] ), $post_id );
		}
		
		/* Image Handling */
		Recast_Filter_updateListing_imageHandling( $post_id );

		/* Update Title -> Slug */
		$post = get_post( $post_id );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_name'  => sanitize_title( $post->post_title )
		));

		/* Save */
		$product->save();

		/* Force Crash to Troubleshoot */
		// asdasdasd();
	}    
	return $post_id;
} add_filter('acf/save_post' , 'Recast_Filters_updateListing' );
function Recast_Filter_updateListing_imageHandling( $post_id ) {
	$images = get_field( 'images', $post_id );
	$attachment_ids = array();
	
	/* Assign Featured Image */
	if( $images ) {
		$newImageID = '';
		for( $r = count($images); $r >= 0; $r-- ) { 
				if( $images[$r-1]['image'] ) { $newImageID = $images[$r-1]['image']; }
		}
		if( $newImageID ) {
				set_post_thumbnail( $post_id, $newImageID );
		} else {
			delete_post_thumbnail( $post_id );
		}
	} else {
		delete_post_thumbnail( $post_id );
	}
	
	/* Repopulate Product Image Gallery */
	if( $images ) {
		foreach( $images as $image ) { $attachment_ids []= $image['image']; }
	}
	
	// serialize( $attachment_ids );
	update_post_meta( $post_id, '_product_image_gallery', implode( ',', $attachment_ids ) );
}
function Recast_filter_listings_populate_seller_id($field) {
		// Only run on the front-end
		if (is_admin()) { return $field; }
		$field['value'] = get_current_user_id(); // Set the value from a GET parameter
		
		return $field;
} add_filter('acf/prepare_field/key=field_68c043d8de002', 'Recast_filter_listings_populate_seller_id');
function Recast_filter_listings_populate_listing_id($field) {
		// Only run on the front-end
		if (is_admin()) { return $field; }
		$field['value'] = get_the_ID(); // Set the value from a GET parameter
		
		return $field;
} add_filter('acf/prepare_field/key=field_68d42a88bab0f', 'Recast_filter_listings_populate_listing_id');
function Recast_Filter_formatPriceField( $val = null ) {
	// $field['value'] = number_format( $field['value'], 2 );
	if( (float)$val ) {
		$val = number_format( (float)$val, 2 );
	}
	return $val;
}


/* Orders */
function Recast_filter_offers_populate_offer_id($field) {
		/* Populates $order_id on new orders */
		// Only run on the front-end
		if (is_admin()) { return $field; }
		$field['value'] = get_the_ID(); // Set the value from a GET parameter
		
		return $field;
} add_filter('acf/prepare_field/key=field_68d429cd0734e', 'Recast_filter_offers_populate_offer_id');
function Recast_Filter_renameHoldStatus( $order_statuses ) {
    foreach ( $order_statuses as $key => $status ) {
        if ( 'wc-on-hold' === $key ) {
					$order_statuses['wc-on-hold'] = _x( 'Disputed', 'Order status', 'woocommerce' );
				}
        if ( 'checkout-draft' === $key ) {
					$order_statuses['checkout-draft'] = _x( 'Offer Pending', 'Order status', 'woocommerce' );
				}
    }
    return $order_statuses;
} add_filter( 'wc_order_statuses', 'Recast_Filter_renameHoldStatus' );

/* Users */
function Recast_Filter_shopAddress( $user_id ) {
	// $shop_address_1 = get_user_meta( $user_id, 'dokan_store_address[street_1]', true );
	// $shop_address_2 = get_user_meta( $user_id, 'dokan_store_address[street_2]', true );
	// $shop_city = get_user_meta( $user_id, 'dokan_store_address[city]', true );
	// $shop_state = get_user_meta( $user_id, 'dokan_store_address[state]', true );
	// $shop_country = get_user_meta( $user_id, 'dokan_store_address[country]', true );
	// $shop_postcode = get_user_meta( $user_id, 'dokan_store_address[zip]', true );
	// $shop_phone = get_user_meta( $user_id, 'dokan_store_phone', true );
	
	// if( !$shop_address_1 ) { $shop_address_1 = get_user_meta( $user_id, 'billing_address_1', true ); }
	// if( !$shop_address_2 ) { $shop_address_2 = get_user_meta( $user_id, 'billing_address_2', true ); }
	// if( !$shop_city ) { $shop_city = get_user_meta( $user_id, 'billing_city', true ); }
	// if( !$shop_state ) { $shop_state = get_user_meta( $user_id, 'billing_state', true ); }
	// if( !$shop_country ) { $shop_country = get_user_meta( $user_id, 'billing_country', true ); }
	// if( !$shop_postcode ) { $shop_postcode = get_user_meta( $user_id, 'billing_postcode', true ); }
	// if( !$shop_phone ) { $shop_phone = get_user_meta( $user_id, 'billing_phone', true ); }
	// $shop_address_1 = 
	// $shop_address_2 = get_user_meta( $user_id, 'billing_address_2' );
	// $shop_city =
	// $shop_state = get_user_meta( $user_id, 'billing_state' );
	// $shop_country = get_user_meta( $user_id, 'billing_country' );
	// $shop_postcode = get_user_meta( $user_id, 'billing_postcode' );
	// $shop_phone = get_user_meta( $user_id, 'billing_phone' );

	// update_user_meta( $user_id, 'shop_address_1', $shop_address_1 );
	// update_user_meta( $user_id, 'shop_address_2', $shop_address_2 );
	// update_user_meta( $user_id, 'shop_city', $shop_city );
	// update_user_meta( $user_id, 'shop_state', $shop_state );
	// update_user_meta( $user_id, 'shop_country', $shop_country);
	// update_user_meta( $user_id, 'shop_postcode', $shop_postcode );
	
	$address_data = array(
    'street_1'    => get_user_meta( $user_id, 'billing_address_1' ),
    'street_2'    => get_user_meta( $user_id, 'billing_address_2' ),
    'city'        => get_user_meta( $user_id, 'billing_city' ),
    'zip'         => get_user_meta( $user_id, 'billing_postcode' ),
    'country'     => get_user_meta( $user_id, 'billing_country' ),
	);

	update_user_meta( $user_id, 'dokan_address', $address_data );
	// update_user_meta( $user_id, 'shop_phone', $shop_phone );
} 
// add_action( 'profile_update', 'Recast_Filter_shopAddress' );


