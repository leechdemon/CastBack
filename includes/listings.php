<?php

/* Draw & Edit Listings */
function CastBack_Listings( $listing_id = null, $posts_per_page = null, $AJAX = false ) {
	// if( !isset( $listing_id ) && isset( $_POST['listing_id'] ) ){ $listing_id = $_POST['listing_id']; }
	// if( !$listing_id && isset( $_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
	if( $listing_id ) { $posts_per_page = 1; }
	else { $posts_per_page = 50; }

	$title_url = '/selling/listings';
	$output = '';

	if( $AJAX ) { $user_id = $_POST['user_id']; }
	else { $user_id = get_current_user_id(); }
	
	// $user_id = null;
	// $listing_id = null;
	
	if( $user_id ) {
		/* Build Product List */
		$args = array(
			'limit'  => $posts_per_page,	// Retrieve up to 10 orders
			'orderby' => 'date',
			'order'  => 'DESC',  
			'author'  => $user_id,
			'post_status'  => 'publish',
			// 'meta_query' => array(
				// array(
					// 'key'     => 'seller_id',
					// 'value'   => $user_id,
					// 'compare' => '=', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
				// ),
				// 'relation' => 'AND', // Optional: 'AND' or 'OR' to combine multiple conditions
			// ),
		);
		echo CastBack_Queries_addFilterButtons();
		$listings = wc_get_products( CastBack_Queries_processFilters( $args ) ); 

		// $output .= '<div style="padding: 1.25rem;"><a class="elementor-button elementor-button-link" href="javascript:CastBack_Action_addListing_button();">Add Listing</a></div>';
		
		/* Draw Listings */	
		if ( count($listings) >= 1 ) {
			
			foreach( $listings as $key => $listing ) {
				if( $listing && ($key+1 != $posts_per_page) ) {
					$listing_id = $listing->get_id();
					$output .= CastBack_Listings_drawListing( $listing_id, null, true, false );
				} else {
					$output .= '<span><a class="view_more" style="font-size: smaller;" href="'.$title_url.'">View More...</a></span>';
				}
			}
		} else {
			$output .= 'You have no listings. (l61-09272025)';
		} /* End Draw Listings */
	} else {
		$output .= 'Please log in. (L56-10102025)';
	}

	
	echo $output;
	
	// if( $AJAX ) { echo $output; wp_die(); }
	// else { return $output; }
}
function CastBack_Listings_editListing_ACF( $listing_id, $listingTemplate = null, $buttonPanelEnabled, $AJAX = false ) {
	
	if( $listing_id ) {
		$args = array(
				'p'	=>	$listing_id,
				'post_type'      => 'product',
				'posts_per_page' => 1,
		);
		$custom_query = new WP_Query( $args );
		
		if ( $custom_query->have_posts() ) {
			while ( $custom_query->have_posts() ) {
				$custom_query->the_post();
					if( is_user_logged_in() ) {
						if( get_current_user_id() == get_field( 'seller_id', $listing_id ) ) {
							acf_form_head();
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
								'return'	=> get_site_url() .'/selling/edit-listing/?listing_id='. $listing_id,
							));
						}
					}
			}
		}
		
		wp_reset_postdata();
	}
}
function CastBack_Listings_drawListing( $listing_id, $listingTemplate = null, $buttonPanelEnabled, $AJAX = false ) {
	// if( !$listingTemplate ) { $listingTemplate = 822; } /* V0.5.4 */
	if( !$listingTemplate ) { $listingTemplate = 2429; } /* V1.0 */
	
	ob_start();	
	if( $listing_id ) {
		$args = array(
				'p'	=>	$listing_id,
				'post_type'      => 'product',
				'posts_per_page' => 1,
		);
		$custom_query = new WP_Query( $args );
		if ( $custom_query->have_posts() ) {
			while ( $custom_query->have_posts() ) {
				$custom_query->the_post();
				// echo do_shortcode('[elementor-template id="'.$listingTemplate.'"]');

				$disabled = '';
				if( !$buttonPanelEnabled ) { $disabled = ' disabled'; }
				else { echo '<h4 style="width: 100%; ">'.get_the_title( $listing_id ).'</h4>'; }
				
				echo '<div class="castback-listing'.$disabled.'">';
					/* Listing Panel */
					echo '<div class="castback-listing-panel">';
						// echo apply_filters('the_content', '[elementor-template id="'.$listingTemplate.'"]');
						echo do_shortcode('[elementor-template id="'.$listingTemplate.'"]');
					echo '</div>';
							
					if( $buttonPanelEnabled ) {
						echo '<div style="width: 25%; float: right; padding-left: 0.5rem;">';
							echo CastBack_Action_DrawButtonPanel( $listing_id );
						echo '</div>';
					}
				echo '</div>';

				if( $buttonPanelEnabled && isset( $_GET['listing_id'] ) ) {
					if( is_user_logged_in() ) {
						if( get_current_user_id() == get_field( 'seller_id', $listing_id ) ) {
							acf_form_head();
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
						}
					}
				}
				
			}
			wp_reset_postdata();
		}
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_drawListing', 'CastBack_Listings_drawListing' );
function CastBack_Listings_markSold( $listing_id = null ) {
	if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
	
	$listing = wc_get_product( $listing_id );
	
	echo json_encode( $listing );
	
	// if( isset( $listing ) ) { echo 'Deleting Listing #' .$listing_id. '...<br><br>'; }
	// else { 'Listing #'.$listing_id.' not found. (L133-10012025)'; }
	$listing->set_stock_status( 'outofstock' );
	$listing->save();
	// if( !$listing->save() ) { echo 'Something went wrong. Please try again. (L143-10012025)'; }

	// echo '<br><br><a class="button button-elementor button-elementor-link" href="/selling/listings/">View My Listings</a>';
} add_action( 'wp_ajax_CastBack_Listings_markSold', 'CastBack_Listings_markSold' );