<?php

/* Draw & Edit Listings */
function CastBack_Listings( $method, $page = false, $AJAX = true ) {
	if( !$page ) extract(shortcode_atts(array( 'listing_id' => null, 'page' => null ), $atts));
	
	if( $method == 'MyListings' ) {
		$title = 'My Listings';
		$title_url = '/selling/listings';
		$orderLimit = -1;
		// $buyerOrSeller = 'seller_id';
		// $orderStatus = array( 'checkout-draft', 'pending', 'processing', 'completed' );
		// $offersOrders = 'offers';
		$AddNewListing = true;
	}
	if( $page ) {
		$orderLimit = 4;
	} else { $page = $method; }
	
	$output = '';
	
	if( $title_url ) { $output .= '<h3><a href="'.$title_url.'">'.$title.'</a></h3>'; }
	else { $output .= '<h3>'.$title.'</h3>'; }
	
	if( $AddNewListing ) { echo do_shortcode('[CastBack_AddListing_Button]'); }

	if( $AJAX ) { $user_id = $_POST['user_id']; }
	else { $user_id = get_current_user_id(); }
	
	if( $user_id ) {
		$args = array(
			// 'type' => 'variable',
			// 'status' => $orderStatus, // Get completed orders
			'limit'  => $orderLimit,           // Retrieve up to 10 orders
			'orderby' => 'date',      // Order by date
			'order'  => 'DESC',  
			// 'author'  => get_current_user_id(),  
			'author'  => $user_id,  
			// 'meta_query' => array(
				// array(
					// 'key'     => 'seller_id',
					// 'value'   => get_current_user_id(),
					// 'compare' => '=', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
				// ),
				// 'relation' => 'AND', // Optional: 'AND' or 'OR' to combine multiple conditions
			// ),
		);
		$listings = wc_get_products( $args );
		
		/* Draw Listings */	
		$output .= '<div id="CastBack-MyListings">';
		if( count($listings) < 1 ) {
			$output .= 'You have no listings.';
		} else {
			foreach( $listings as $key => $listing ) {
				if( $key+1 == $orderLimit ) {
					$output .= '<span><a class="view_more" style="font-size: smaller;" href="'.$title_url.'">View More...</a></span>';
				}
				else {
					if($listing) {
						$listing_id = $listing->get_id();
						$output .= CastBack_listings_draw_listing( $listing_id, '719', false );
						// $output .= CastBack_listings_draw_listing( $listing_id, '822', false );
					}
				}
			}
		} /* End Draw Listings */
		$output .= '</div>';
	} else {
		$output .= 'Please log in.';
	}
	
	if( $AJAX ) { echo $output; wp_die(); }
	else { return $output; }
}
function CastBack_listings_draw_listing( $listing_id = '', $templateOverride = false, $AJAX = true ) {
	$listingTemplate = '822';
	if( $templateOverride ) { $listingTemplate = $templateOverride; }
	
	ob_start();
	
	if( !$listing_id ) { $listing_id = $_POST['listing_id']; }	
	$args = array(
			'p'							 =>	$listing_id,
			'post_type'      => 'product',
			'posts_per_page' => 1,
	);
	$custom_query = new WP_Query( $args );
	if ( $custom_query->have_posts() ) {
		while ( $custom_query->have_posts() ) {
			$custom_query->the_post();
			// echo '<style>.acf-form, .woocommerce-js div.product { display: inline-block !important;</style>';
				echo apply_filters('the_content', '[elementor-template id="'.$listingTemplate.'"]'); 
				// echo do_shortcode('[elementor-template id="'.$listingTemplate.'"]');
			wp_reset_postdata();
		}
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_listings_draw_listing', 'CastBack_listings_draw_listing' );
function Castback_edit_listing_url($atts, $content = null, $AJAX = false ) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	// if( !$listing_id ) {	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts)); }
	// if( !$listing_id) { $listing_id = get_the_id(); }

	
	// if( $AJAX ) { $user_id = $_POST['user_id']; }
	// else { 
	$user_id = get_current_user_id();
	// }

	return '<a class="button" href="'.get_site_url().'/selling/listings/?listing_id='.$listing_id.'">Edit Listing</a>';
	// AJAX "View" URL removed for v0.5 Release
	// return '<a class="button" href="javascript:CastBack_edit_listing_button(\''.$listing_id.'\', \''.$user_id.'\', \'CastBack-MyListings\');">Edit Listing</a>';
} add_shortcode('Castback_edit_listing_url', 'Castback_edit_listing_url');
function Castback_edit_listing( $listing_id, $AJAX = true ) {		
	ob_start();

	if( !$listing_id && $_POST['listing_id'] ) { $listing_id = $_POST['listing_id']; }
	if( !$listing_id && $_GET['listing_id'] ) { $listing_id = $_GET['listing_id']; }
	
	if( $_POST['user_id'] ) { $user_id = $_POST['user_id']; }
	if( !$user_id ) { $user_id = get_current_user_id(); }
	
	echo '<h3>Listing: '.get_the_title( $listing_id ).'</h3>';
	if( $listing_id && $user_id == get_field( 'seller_id', $listing_id ) ) {
		echo CastBack_listings_draw_listing( $listing_id, '719', false );
		
		// if( $_GET['listing_id'] ) {

				acf_form(array(
				'form_attributes'   => array(
					'method'	=>	'post',
					'class'		=>	'acf-form',
				),
				'post_title'   => true,
				'post_id'   => $listing_id,
				'field_groups' => array(503,),
				'uploader'		=> 'basic',
				'submit_value' => 'Save Listing',
				'return'	=> get_site_url() .'/selling/listings?listing_id='. $listing_id,
			));
		// }

		// Remove this?
		// wp_reset_postdata();
	} else {
		// if( $listing_id  ) { echo CastBack_listings_draw_listing( $listing_id, false, false ); }
		if( $listing_id  ) { echo CastBack_listings_draw_listing( $listing_id, '949', false ); }
		else { echo 'Please check your URL, or log out/in and try again.'; }
	}
	
	// $term = get_term_by( 'name', $_POST['acf']['field_68644913a0ab7'], 'product_cat' );
	// echo $term->term_id;
	
	
	if($AJAX) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_edit_listing', 'CastBack_edit_listing' );





// extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
// $imageURL = get_field( 'images', $listing_id)[0]['image']['url'];
// if( $imageURL == '' ) { $imageURL = 'https://castback.wpenginepowered.com/wp-content/uploads/2025/08/missing_image.jpg'; }
// $url = '<img style="max-width: 100%; height: auto;" src="'.$imageURL.'">';
// $url = '<a style="padding-right: 1rem;" href=""><img style="max-width: 15rem;" src="'.$imageURL.'"></a>';

