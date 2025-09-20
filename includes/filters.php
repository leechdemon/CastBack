<?php
function CastBack_filter_listings_status_update( $post_id ) {
	$product = wc_get_product( $post_id );
	if( $product ) {
		$product->set_status( $_POST['acf']['field_688ce497d2c30'] );
		// $product->set_name( 'Listing ' . $_POST['acf']['field_688ce497d2c30'] );
		$product->save();
	}

	return $post_id;
} add_filter('acf/pre_save_post' , 'CastBack_filter_listings_status_update', 10, 1 );
function CastBack_filter_listings_featured_image( $post_id ) {
	$images = get_field( 'images', $post_id );
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
	return $post_id;
} add_filter('acf/save_post' , 'CastBack_filter_listings_featured_image', 10, 1 );
function CastBack_filter_listings_populate_seller_id($field) {
		// Only run on the front-end
		if (is_admin()) { return $field; }
		$field['value'] = get_current_user_id(); // Set the value from a GET parameter
		
		return $field;
} add_filter('acf/prepare_field/key=field_68c043d8de002', 'CastBack_filter_listings_populate_seller_id');
function CastBack_action_acf_formatPriceFields($field) {
	$field['value'] = number_format( $field['value'], 2 );
	
	return $field;
}
add_filter('acf/prepare_field/key=acf-field_68964c94355ed', 'CastBack_action_acf_formatPriceFields');
add_filter('acf/prepare_field/key=acf-field-68964cb9355ee', 'CastBack_action_acf_formatPriceFields');