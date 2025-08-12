<?php

function acf_pre_save_post($post_id) {
	// Front-end works 100%
	// Back-end does nothing
	
	// if( ! is_admin() ) {
		if ( empty($_POST['acf']) ) { return; }

		/* If we have POST data, set the variable. If not, set the variable from existing ACF. */
		$MSRP = $_POST['acf']['field_68964cb9355ee'];
		$ListingPrice = $_POST['acf']['field_68964c94355ed'];

		/* Handle Variables*/
		if( $MSRP ) { $MSRP = number_format( $MSRP, 2 ); }
		if( $ListingPrice ) { $ListingPrice = number_format( $ListingPrice, 2 ); }
		// if( $MSRP < $ListingPrice ) { $MSRP = $ListingPrice; }

		/* Set Fields from variables */
		update_post_meta( $post_id, '_regular_price', $ListingPrice );
		// update_post_meta( $post_id, '_sale_price', $ListingPrice );
		
		/* Resync ACF from variables */ 
		$_POST['acf']['field_68964cb9355ee'] = $MSRP;
		$_POST['acf']['field_68964c94355ed'] = $ListingPrice;
		


		/* Set Category from Listing Type */
		if ( !empty( $_POST['acf']['field_68644913a0ab7'] ) ) {
			$term = get_term_by( 'slug', $_POST['acf']['field_68644913a0ab7'], 'product_cat' );
			wp_set_post_terms( $post_id, $term->term_id, 'product_cat', false );
		}
		
		/* Image Handling */
		set_post_thumbnail( $post_id, get_field( 'images', $post_id )[0]['image']['id'] );
		
		
		return $post_id;
	// }
} add_action('acf/pre_save_post', 'acf_pre_save_post', 10 );


	// extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	// $imageURL = get_field( 'images', $listing_id)[0]['image']['url'];
	// if( $imageURL == '' ) { $imageURL = 'https://castback.wpenginepowered.com/wp-content/uploads/2025/08/missing_image.jpg'; }
	// $url = '<img style="max-width: 100%; height: auto;" src="'.$imageURL.'">';
	// $url = '<a style="padding-right: 1rem;" href=""><img style="max-width: 15rem;" src="'.$imageURL.'"></a>';
