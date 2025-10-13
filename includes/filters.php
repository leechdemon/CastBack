<?php
function CastBack_filter_listings_status_update( $post_id ) {
	$product = wc_get_product( $post_id );
	if( $product && isset ( $_POST['acf']['field_688ce497d2c30'] ) ) {
		$product->set_status( $_POST['acf']['field_688ce497d2c30'] );
		$product->save();
	}

	return $post_id;
} add_filter('acf/pre_save_post' , 'CastBack_filter_listings_status_update', 10, 1 );

// function CastBack_Filters_userUploadCSV( $username, $email, $password, $custom_field_value ) {
    // $userdata = array(
        // 'user_login' => $username,
        // 'user_pass'  => $password,
        // 'user_email' => $email,
        // 'role'       => 'subscriber', // Or your desired role
    // );

    // $user_id = wp_insert_user( $userdata );

    // if ( ! is_wp_error( $user_id ) ) {
        // update_user_meta( $user_id, 'my_custom_field', $custom_field_value );
        // return "User {$username} created with custom meta.";
    // } else {
        // return "Error creating user {$username}: " . $user_id->get_error_message();
    // }
// }


function CastBack_Filters_changeAttribute( $post_id, $tax = null, $slug = null, $append = false ) {
	$product = wc_get_product( $post_id );
	if( $product ) {
			if( isset ( $_POST['acf']['field_68644913a0ab7'] ) ) { /* Cat */
				$tax = 'product_cat';
				$slug = $_POST['acf']['field_68644913a0ab7'];
			}
			// if( isset ( $_POST['acf']['field_68644913a0ab7'] ) ) { /* Subcat */
				// $tax = 'product_cat';
				// $slug = $_POST['acf']['field_68644913a0ab7'];
			// }
			// if( isset ( $_POST['acf']['field_68644913a0ab7'] ) ) { /* Sub-Subcat */
					// $tax = 'product_cat';
				// $slug = $_POST['acf']['field_68644913a0ab7'];
			// }
			// if( isset ( $_POST['acf']['field_68644913a0ab7'] ) ) { /* Brand */
				// $tax = 'product_brand';
				// $slug = $_POST['acf']['field_68644913a0ab7'];
			// }
		
		if( isset( $slug ) ) {
			wp_set_object_terms( $post_id, $slug, $tax, $append );
		}
	}

	return $post_id;
} add_filter('acf/pre_save_post' , 'CastBack_Filters_changeAttribute', 10, 1 );

function CastBack_Filter_updateListing_imageHandling( $post_id ) {
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
	
	return $post_id;
} add_filter('acf/save_post' , 'CastBack_Filter_updateListing_imageHandling', 10, 1 );
function CastBack_filter_listings_populate_seller_id($field) {
		// Only run on the front-end
		if (is_admin()) { return $field; }
		$field['value'] = get_current_user_id(); // Set the value from a GET parameter
		
		return $field;
} add_filter('acf/prepare_field/key=field_68c043d8de002', 'CastBack_filter_listings_populate_seller_id');
function CastBack_filter_listings_populate_listing_id($field) {
		// Only run on the front-end
		if (is_admin()) { return $field; }
		$field['value'] = get_the_ID(); // Set the value from a GET parameter
		
		return $field;
} add_filter('acf/prepare_field/key=field_68d42a88bab0f', 'CastBack_filter_listings_populate_listing_id');
function CastBack_action_acf_formatPriceFields($field) {
	$field['value'] = number_format( $field['value'], 2 );
	
	return $field;
}
add_filter('acf/prepare_field/key=acf-field_68964c94355ed', 'CastBack_action_acf_formatPriceFields');
add_filter('acf/prepare_field/key=acf-field-68964cb9355ee', 'CastBack_action_acf_formatPriceFields');



/* Orders */
function CastBack_filter_offers_populate_offer_id($field) {
		// Only run on the front-end
		if (is_admin()) { return $field; }
		$field['value'] = get_the_ID(); // Set the value from a GET parameter
		
		return $field;
} add_filter('acf/prepare_field/key=field_68d429cd0734e', 'CastBack_filter_offers_populate_offer_id');