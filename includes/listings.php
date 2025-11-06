<?php
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
			// 'post_status'  => 'any',
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
					$output .= CastBack_Listings_drawListing( $listing_id, null, false, false );
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

/* - Security: Public */
function CastBack_Listings_drawListing( $listing_id, $listingTemplate = null, $buttonPanelEnabled = false, $AJAX = false ) {
	// if( !$listingTemplate ) { $listingTemplate = 822; } /* V0.5.4 */
	// if( !$listingTemplate ) { $listingTemplate = 2429; } /* V0.5.6 */
	if( !$listingTemplate ) { $listingTemplate = 2628; } /* V1.0 */
	
	
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
							
				echo '</div>';
				
			}
			wp_reset_postdata();
		}
	} else {
		echo 'This is not your order. Please try again. ("2856", L103-11052025)';
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_drawListing', 'CastBack_Listings_drawListing' );

/* Listing Actions, AJAX-controlled */
/* - Security: Does not require security, since all Users can create Listings. */
function CastBack_Listings_addListing( $AJAX = false ) { /* Currently, method only available via Endpoint( URL->Shortcode ). AJAX disabled. */
	// if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	// if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	if( !$user_id ) { $user_id = get_current_user_id(); }
	$success = true;
	
	ob_start();
	if( $user_id ) {
		
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
		
	}		
} /* add_action( 'wp_ajax_CastBack_Listings_addListing', 'CastBack_Listings_addListing' ); */
function CastBack_Listings_editListing_ACF( $listing_id, $listingTemplate = null, $buttonPanelEnabled = false, $AJAX = false ) {
	
	if( $listing_id ) {
		$args = array(
				'p'	=>	$listing_id,
				'post_type'      => 'product',
				'post_status'      => array('publish', 'draft'),
				'posts_per_page' => 1,
		);
		$custom_query = new WP_Query( $args );
		
		if ( $custom_query->have_posts() ) {
			while ( $custom_query->have_posts() ) {
				$custom_query->the_post();
				if( is_user_logged_in() ) {
					if( get_current_user_id() == get_field( 'seller_id', $listing_id ) || current_user_can( 'manage_options' ) ) {
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

/* - Security: CastBack_customerSeller() */
function CastBack_Listings_publishListing( $listing_id = null ) {
	if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	
	ob_start();	
	if( CastBack_customerSeller( $listing_id ) ) {
		echo '1';
		$user = wp_get_current_user();
		echo '2';
		if ( !in_array( 'customer', (array) $user->roles ) ) {
			$listing = wc_get_product( $listing_id );
			
			$listing->set_status( 'publish' );
			echo $listing->save();
		} else {
			echo '0';
		}
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_publishListing', 'CastBack_Listings_publishListing' );
function CastBack_Listings_hideListing( $listing_id = null ) {
	if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	
	ob_start();	
	if( CastBack_customerSeller( $listing_id ) ) {
		$listing = wc_get_product( $listing_id );
		
		$listing->set_status( 'draft' );
		echo $listing->save();
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_hideListing', 'CastBack_Listings_hideListing' );
function CastBack_Listings_markSold( $listing_id = null ) {
	if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	
	ob_start();	
	if( CastBack_customerSeller( $listing_id ) ) {
		$listing = wc_get_product( $listing_id );
		
		$listing->set_stock_status( 'outofstock' );
		echo $listing->save();
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_markSold', 'CastBack_Listings_markSold' );
function CastBack_Listings_markUnsold( $listing_id = null ) {
	if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	
	ob_start();	
	if( CastBack_customerSeller( $listing_id ) ) {
		$listing = wc_get_product( $listing_id );
		
		$listing->set_stock_status( 'instock' );
		echo $listing->save();
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_markUnsold', 'CastBack_Listings_markUnsold' );
function CastBack_Listings_deleteListing( $listing_id = null ) {
	if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	
	ob_start();	
	if( CastBack_customerSeller( $listing_id ) ) {
		$listing = wc_get_product( $listing_id );
		
		$listing->set_status( 'trash' );
		echo $listing->save();
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_deleteListing', 'CastBack_Listings_deleteListing' );
function CastBack_Listings_restoreListing( $listing_id = null ) {
	if( !$listing_id && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
	if( !$AJAX && isset( $_POST['AJAX'] ) ) { $AJAX = $_POST['AJAX']; }
	
	ob_start();	
	if( CastBack_customerSeller( $listing_id ) ) {
		$listing = wc_get_product( $listing_id );
		
		$listing->set_status( 'draft' );
		echo $listing->save();
	}
	
	if( $AJAX ) { echo ob_get_clean(); wp_die(); } else { return ob_get_clean(); }
} add_action( 'wp_ajax_CastBack_Listings_restoreListing', 'CastBack_Listings_restoreListing' );
