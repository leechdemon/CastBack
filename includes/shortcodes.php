<?php  

function TestProductActions() {
	
	// $thisPost = get_post( 2009 );
	// $thisPost = get_post( 1953 );
	// Test( $thisPost );
	// $gallery = get_post_meta( $thisPost->ID, '_product_image_gallery', true );
	// foreach( $gallery as $gal ) {
		// Test($gallery);
	
	// Test( get_field( 'images', $thisPost )[0]['image );
	// $attachment_ids = array( 2405, 2406 );
	// update_post_meta( 1953, '_product_image_gallery', implode( ',', $attachment_ids ) );
	// wp_delete_post( 1953 );
	
	// $skipAll = true;
	if( !$skipAll ) {
		// $filePath = ABSPATH . 'wp-content/uploads/castback';
		// $items = scandir($filePath);
		
		
		$args = array(
			// 'limit'  => 25,
			// 'limit'  => 10,
			'limit'  => -1,
			// 'limit'  => 1,
			'orderby' => 'date',
			'order'  => 'DESC',  
			// 'author'  => $user_id,
			'post_status'  => 'any',
			// 'post_status'  => 'publish',
			// 'meta_query' => array(
				// array(
					// 'key'     => 'seller_id',
					// 'value'   => $user_id,
					// 'compare' => '=', // Optional: canbe 'IN', 'LIKE', 'EXISTS', etc.
				// ),
				// 'relation' => 'AND', // Optional: 'AND' or 'OR' to combine multiple conditions
			// ),
		);
		// echo CastBack_Queries_addFilterButtons();
		$listings = wc_get_products( $args ); 
		

		foreach( $listings as $listing ) {
			// CastBack_Filter_updateListing_imageHandling( $listing->get_id() );
		}
		if( 1 == 2) { /* Skip the rest... */

			// if( $listing_id == 2041 ) {	
				// CastBack_Filter_updateListing_featuredImage( $listing_id );
				// $listingJSON = json_decode( $listing->get_description() );
				
				// $seller_id = get_field( 'seller_id', $listing_id );
			$seller_id = get_post_field( 'post_author', $listing_id );
				// $listing->set_author( $seller_id );
				// $listing->set_author( $seller_id );
				// $listing->save();
			// }
			echo '#'.$listing_id.' - '.$listing->get_name().'<br>';
			$user = get_user_by( 'id', $seller_id );
			$userData = get_userdata( $seller_id );
			echo ' - ' .$seller_id.' - '.$user->display_name .'<br>';
			echo ' - ' . json_encode( $userData->roles ) .'<br>';
			if( in_array( 'seller', $userData->roles ) || in_array( 'administrator', $userData->roles ) ) { echo "It's a vendor.<br>"; }
			else {
				echo "It's NOT a vendor!!!<br>";
				
				$user->set_role( 'seller' );
			}
			
			// update_field( 'seller_id', $seller_id, $listing_id );
			// echo 'update field - seller_id<br>';
			// update_field( 'listing_id', $listing_id, $listing_id );
			// echo 'update field - listing_id<br>';
			echo '<br><br>';

			// $product_data = array(
				// 'ID'          => $listing_id,
				// 'post_author' => get_field( 'seller_id', $listing_id ),
			// );
			// wp_update_post( $product_data );
			
			$skip = true;
			if( !$skip && $listingJSON ) {
				echo '<a href="'.get_the_permalink( $listing_id ).'">' .$listing->get_name(). "</a><br>";
				echo '<div style="color: yellowgreen; background-color: #444; width: 100%; clear: both; padding: 1rem; ">';

				if( $listing->get_status() == 'private' ) { $listing->set_status( 'publish' ); $listing->save(); }

				$activeKeys = array();
				$activeKeys []= 'categoryLevel1';
				$activeKeys []= 'categoryLevel2';
				$activeKeys []= 'categoryLevel3';
				$activeKeys []= 'Brand';
				$activeKeys []= 'brands_garments';
				$activeKeys []= 'Condition';
				$activeKeys []= 'Size';
				$activeKeys []= 'shoe_size';
				$activeKeys []= 'rod_sections';

				$activeKeys []= 'Length';
				$activeKeys []= 'Weight';
				
				/* DumpAtts */
				$activeKeys []= 'Accept_Returns';
				$activeKeys []= 'location';
				$activeKeys []= 'pickupEnabled';
				$activeKeys []= 'shippingEnabled';
				$activeKeys []= 'unitType';
				$activeKeys []= 'listingType';
				$activeKeys []= 'transactionProcessAlias';
				$activeKeys []= 'shippingPriceInSubunitsOneItem';

				$newJSON = $listingJSON;
				foreach( $listingJSON as $key => $val ) {
					if( in_array( $key, $activeKeys ) ) {
						// Test( $listingJSON );
						
						$objectIndex++;
						// echo $key . ": " . $val . "<br>";
						if( is_string($val) ) { echo $key . ": " . $val . "<br>"; }
						else { $keyIsArray = true; }
						
						$success = false;
						$result = false;
						$cancel = false;
						$term = "";
						$childTerm = "";
						$grandchildTerm = "";
						
						if( $key == 'categoryLevel1' ) {
							$term = get_term_by( 'slug', strtolower( $listingJSON->categoryLevel1 ), 'product_cat' );
							if( $term ) {
								/* Removes uncategorized (sort of...) */
								$append = true;
								foreach( wp_get_post_terms( $listing_id, 'product_cat' ) as $term ) {
									if( $term->term_id == 15 ) { $append = false; }
								}
								
								CastBack_Filters_changeAttribute( $listing_id, 'product_cat', $term->slug, $append );
								$success = true;
							}
						} else if ( $key == 'categoryLevel2' ) { 
							$term = get_term_by( 'slug', strtolower( $listingJSON->categoryLevel1 ), 'product_cat' );
							$childTerm = get_term_by( 'slug', strtolower( $listingJSON->categoryLevel2 ), 'product_cat' );
							if( $childTerm ) {
								CastBack_Filters_changeAttribute( $listing_id, 'product_cat', $childTerm->slug, true );
								$success = true;
							}
						} else if ( $key == 'categoryLevel3' ) { 
							$childTerm = get_term_by( 'slug', strtolower( $listingJSON->categoryLevel2 ), 'product_cat' );
							$grandchildTerm = get_term_by( 'slug', strtolower( $listingJSON->categoryLevel3 ), 'product_cat' );
							if( $grandchildTerm ) {
								CastBack_Filters_changeAttribute( $listing_id, 'product_cat', $grandchildTerm->slug, true );
								$success = true;
							}
						} else if ( $key == 'Brand' ) { 
							$brand = get_term_by( 'slug', strtolower( $listingJSON->Brand ), 'product_brand' );
							if( $brand ) {
								CastBack_Filters_changeAttribute( $listing_id, 'product_brand', $brand->slug, false );
								$success = true;
							}
						} else if ( $key == 'brands_garments' ) { 
							$brandsGarments = get_term_by( 'slug', strtolower( $listingJSON->brands_garments), 'product_brand' );
							if( $brandsGarments ) {
								CastBack_Filters_changeAttribute( $listing_id, 'product_brand', $brandsGarments->slug, false );
								$success = true;
							}
						} else if ( $key == 'Condition' ) { /* Other Taxonomies */
							$tax = 'product_condition';
							$term = get_term_by( 'slug', strtolower( $val ), $tax );
							if( $term ) {
								CastBack_Filters_changeAttribute( $listing_id, $tax, $term->slug, false );
								$success = true;
							}
						} else if ( $key == 'Size' || $key == 'shoe_size' ) { 
							$tax = 'product_size';
							
							$term = get_term_by( 'slug', strtolower( $val ), $tax );
							if( $term ) {
								CastBack_Filters_changeAttribute( $listing_id, $tax, $term->slug, false );
								$success = true;
							}
						} else if ( $key == 'shippingPriceInSubunitsOneItem' ) { 
							update_field( $key, $val, $listing_id );
							$success = true;
						} else if ( $key == 'rod_sections' ) { 
							update_field( $key, $val, $listing_id );
							$success = true;
						} else if ( $key == 'Length' ) { 
							$tax = 'product_length';
							
							foreach( $val as $lengthItem ) {
								$term = get_term_by( 'slug', $lengthItem, $tax );
								if( $term ) {
									CastBack_Filters_changeAttribute( $listing_id, $tax, $term->slug, true );
									$success = true;
								}
							}
						} else if ( $key == 'Weight' ) { 
							$tax = 'product_weight';
							
							foreach( $val as $weightItem ) {
								$term = get_term_by( 'slug', $weightItem, $tax );
								if( $term ) {
									CastBack_Filters_changeAttribute( $listing_id, $tax, $term->slug, true);
									$success = true;
								}
							}
						} else { /* Dump Stats... */
							if( 1==1 ) { //Validate, or just assume we drop it?
								$dropKey = true;
							}
						}

						/* If no term, make a term... */
						if( !$success && is_string( $val ) ) {
							if( $key == 'categoryLevel1' ) {
								$args = array(
									'slug'   => strtolower( $val ),
									'parent' => 0,
								);
								$taxonomy = 'product_cat';
							} else if( $key == 'categoryLevel2' ) {
								$args = array(
									'slug'   => strtolower( $val ),
									'parent' => $term->term_id,
								);
								$taxonomy = 'product_cat';
							} else if( $key == 'categoryLevel3' ) {
								$args = array(
									'slug'   => strtolower( $val ),
									'parent' => $childTerm->term_id,
								);
								$taxonomy = 'product_cat';
							} else if( $key == 'Brand' || $key == 'brands_garments' ) {
								$args = array(
									'slug'   => strtolower( $val ),
									'parent' => 0,
								);
								$taxonomy = 'product_brand';
							} else if( $key == 'Condition' ) {
								$args = array(
									'slug'   => strtolower( $val ),
									'parent' => 0,
								);
								$taxonomy = 'product_condition';
							} else if( $key == 'Size' ) {
								$args = array(
									'slug'   => strtolower( $val ),
									'parent' => 0,
								);
								$taxonomy = 'product_size';
							} else {
								$cancel = true;
								$taxonomy = null;
								// Test( $key );
							}
						} else if( $key == "Length" || $key == "Weight" ) {
							if( $key == "Length" ) { $taxonomy = 'product_length'; }
							if( $key == "Weight" ) { $taxonomy = 'product_weight'; }

							foreach( $val as $lengthItem ) {
								$cancel = true;
								$cat_name = str_replace( "-", " ", $lengthItem);
								$cat_name = ucwords( $cat_name, " " );
								$args = array(
									'slug'   => strtolower( $lengthItem ),
									'parent' => 0,
								);

								$result = wp_insert_term(
									$cat_name, // The name of the subcategory
									$taxonomy,         // The taxonomy (use 'category' for standardcategories)
									$args
								);
								
								if ( $result && isset( $result->term_id ) ) { 
									CastBack_Filters_changeAttribute( $listing_id, $taxonomy, $result->slug, true );
									$success = true;
								}
							}
						} else if( $dropKey ) {
							$cancel = true;
						}
						
						/* Make the term? */
						if( !$cancel ) {
							$cat_name = str_replace( "-", " ", $val);
							$cat_name = ucwords( $cat_name, " " );
									
							$result = wp_insert_term(
								$cat_name, // The name of the subcategory
								$taxonomy,         // The taxonomy (use 'category' for standardcategories)
								$args
							);
							
							if ( $result && isset( $result->term_id ) ) { 
								CastBack_Filters_changeAttribute( $listing_id, $taxonomy, $result->slug, true );
							}
						} /* end Make term */
							
						if( $success || $result || $dropKey ) { unset( $newJSON->{$key} ); }
						// $runUnset = true;
						if( $runUnset ) {
							/* Remove from the array, save */
							$listing->set_description( json_encode($newJSON) );
							if( !$listing->save() ) {
								$success = false; echo 'new JSON failed.<br>';
							} else {
								echo '-- CHANGE SAVED: ' .$key.'<br>';
							}
						}
						


					} /* End If(InArray)... */
				} /* End Foreach JSON */
				echo '</div>';

				// echo '<span style="color: green;">Before:</span> <br>';
				// echo json_encode( $listingJSON ) . '<br><br>';

				echo '<span style="color: red;">After:</span> <br>';
				$listing_id = $listing->get_id();
				$listingJSON = json_decode( $listing->get_description() );
				echo json_encode( $newJSON ) . '<br>';
				if( !$runUnset ) { echo '(not actually saved)<br>'; }
				echo '-----------------------------------------<br><br>';
				
			}
			
			// echo '<a href="'. get_the_permalink( $listing_id ) .'">'. $listing->get_name().'</a><br>';
			// $listing_sku = $listing->get_sku();
			// $seller_id = str_replace( "'", '', $listing->get_short_description() );
			// echo '<span style="padding-left: 1rem; color: red;">'. $seller_id .'</span><br>';

			// Test( $filePath );
			if( 1==2 ) {
			// if( $seller_id ) {
				// Test( '--'. $listing_id );
				// Test( $seller_id );

				$args = array(
					'number' => -1,
				);
				
				
				$users = get_users( $args );
				// Test( $users );
				// $users = get_users();

				foreach( $users as $user ) {
					$user_id = str_replace( "'", '', get_user_meta( $user->ID, 'description', true ) );
					if( $user_id ) {
						// Test( $user_id );
						if( $user_id == $seller_id  ) {
							// TEST( "MATCH!" );
							update_field( 'seller_id', $user->ID, $listing_id );
							$listing->set_short_description('');
							$listing->set_author( $user->ID );
							$listing->save();
						}
					}
				}
			}
				
				

			// foreach ( $items as $item ) {
				// $filename = str_replace( '.avif', '', $item );
				// Test( $filename );
				// if( $filename == $listing_sku ) { 
					// Test( 'MATCH!!!!' );
					// echo '<span style="padding-left: 1rem; color: green;">'. $listing_sku .'</span><br>';
				// }
			// }

			// $contents = scandir( $filePath );
			// if( file_exists( $filePath ) ) { 
			// Test( $contents );
			// echo '<span style="padding-left: 1rem; color: green;">'. $listing->get_SKU() .'</span><br>';
			
		
			// echo '<br>';
		} /* End Foreach*/
	}
}
	

function CastBack_ShortcodeHandler( $atts, $content = null ) {
		global $castbackVersion;
		extract(shortcode_atts(array( 'page' => null, 'action' => null, 'button' => null, 'listing_id' => null, 'order_id' => null, 'featuredImage' => null, 'class' => null, 'setQuery' => null, 'posts_per_page' => null, 'location' => null, 'post_status' => null ), $atts));
		

		if( !isset( $listing_id ) && isset( $_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
		// if( !isset( $listing_id ) && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
		if( !isset( $listing_id ) && get_field( 'listing_id' ) ) { $listing_id = get_field( 'listing_id' ); }

		if( !isset( $order_id ) && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
		
		ob_start();
		wp_enqueue_style( 'CastBack' );
	
		if( $page ) {
			
			/* We only show pages to logged-in users... */
			/* ... or 'DrawListing'... */
			if( is_user_logged_in() || $page == 'DrawListing' ) {
				echo '<div id="CastBack-'.$page.'">';
				// if( $page == 'LogOut' ) { 
					// echo '<button onclick="window.location.href=\''.esc_url( wp_logout_url( get_site_url() .'/login' ) ).'\'">Log out</button>';
				if( $page == 'MyNotifications' ) {
					/* $location unused? */
					/* logged-in header, but hidden */
					// echo CastBack_MyNotifications( $page, $location );
				} else if( $page == 'TestProductActions' ) {
					TestProductActions();
				} else if( $page == 'MyAccount' ) {
						echo 'CastBack_MyAccount'; 
						// echo CastBack_MyAccount( $page, $posts_per_page ); 
				} else if( $page == 'EditListing_ACF' ) {
					if( $listing_id ) {
						echo CastBack_Listings_editListing_ACF( $listing_id, null, false, false );
					}
				} else if( $page == 'MyListings' ) {
						echo CastBack_Listings( $listing_id, $posts_per_page ); 
				} else if( $page == 'DrawListing' ) {
					if( isset( $listing_id ) ) {
						echo CastBack_Listings_drawListing( $listing_id, null, false, false );
					}
				} else if( $page == 'MarkSold' ) {
					// echo CastBack_Listings_drawListing( $listing_id, null, true, false );
					echo CastBack_Action_DrawButtonPanel_markSold( $listing_id );
				} else if( $page == 'MyOffers' ) { 
					// if( isset( $listing_id ) ) {
						// echo CastBack_Listings_drawListing( $listing_id, null, true, false );
						// $order_id = CastBack_Action_makeOffer( $listing_id, false );
					// } else {
						if( isset( $order_id ) ) {
							if( CastBack_Offers_customerSeller( $order_id ) ) {
								echo 'TITLE<br>';
								echo CastBack_Listings_drawListing( get_field( 'listing_id', $order_id ), null, true, false );
								// echo CastBack_Offers( $order_id, 1 );
								// echo CastBack_Offers_drawOrderDetails( $order_id );
							} else {
								echo 'This is not your order. Please try again. (S51-10022025).';
							}
						} else { echo CastBack_Offers( $page, $posts_per_page ); }
					// }
				} else if( $page == 'MyOrders' ) {
					if( isset( $order_id ) ) {

						if( CastBack_Offers_customerSeller( $order_id ) ) {
							echo CastBack_Offers( $order_id, 1 );
							echo CastBack_Offers_drawOrderDetails( $order_id );
						} else {
							echo 'This is not your order. Please try again. (S51-10022025).';
						}
					} else { echo CastBack_Offers( $page, $posts_per_page ); }
				} else {
					echo 'function "'.$page.'" not found. (s74-09232025)';
				}
				echo '</div>'; // close <div id="$page">
			} else {
				echo 'Please log in. (s90-09292025)';
			}
		// } else if( $button == 'drawButtonPanel' ) {
			// echo CastBack_Action_DrawButtonPanel( $listing_id );
		} else if( $button ) {
			
			// echo CastBack_Action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
			if( $button == 'editListing' ) {
				echo CastBack_Action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			}
			// if( $button == 'makeOffer' ) {
				// echo 'javascript:CastBack_Action_makeOffer_button("'.$post_id.'")';
				// echo CastBack_Action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
			// if( $button == ['sendMessage', 'submitOffer', 'acceptOffer', 'expireOffer', 'paymentComplete', 'disputeOrder', 'removeDispute', 'addTracking', 'completeOrder', ] ) {
				// echo CastBack_Action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
			// if( $button == [ 'remove_dispute' ] ) {
				// echo CastBack_Action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
		} else if( $action ) {
			if( is_user_logged_in() ) {
				if( $action == "addListing" ) {
					if( isset( $listing_id ) && $listing_id == $action ) {
						CastBack_Action_addListing( false );
					} else {
						echo 'Wrong listing_id set. (s85-10012025)';
					}
				} else if( $action == "makeOffer" ) {
					if( isset( $listing_id ) ) { CastBack_Action_makeOffer( $listing_id ); }
					else { echo 'No Listing ID found. (s95-09302025)'; }
				} else {
					echo 'Action "'.$action.' not found". (s98-09302025)';
				}
			} else {
				echo 'Please log in. (s98-09302025)';
			}
		} else {
			echo 'no shortcode found. ("'.get_the_ID().'", s75-09232025)';
		}
			
		return ob_get_clean();
} add_shortcode('CastBack', 'CastBack_ShortcodeHandler');