<?php  
function CastBack_ShortcodeHandler( $atts, $content = null ) {
		global $castbackVersion;
		extract(shortcode_atts(array( 'page' => null, 'action' => null, 'field' => null, 'button' => null, 'listing_id' => null, 'order_id' => null, 'featuredImage' => null, 'class' => null, 'setQuery' => null, 'posts_per_page' => null, 'location' => null, 'post_status' => null, 'method' => null, 'user_id' => null ), $atts));
		

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
				if( $page == 'TestProductActions' ) { /* TestProductActions(); */ }
				else if( $page == 'MyAccount' ) {
					echo 'CastBack_MyAccount'; 
					// echo CastBack_MyAccount( $page, $posts_per_page ); 
				}
				else if( $page == 'EditListing_ACF' ) { echo CastBack_Listings_editListing_ACF( $listing_id, null, false, false ); }
				else if( $page == 'MyListings' ) { echo CastBack_Listings( $listing_id, $posts_per_page ); }
				else if( $page == 'DrawListing' ) { echo CastBack_Listings_drawListing( $listing_id, null, false, false ); }
				else if( $page == 'DrawListingForOrder' ) {
					if( CastBack_customerSeller( $order_id ) ) {
						echo CastBack_Listings_drawListing( get_field( 'listing_id', $order_id ), null, false, false );
					}
				}
				else if( $page == 'MarkSold' ) {
					// echo CastBack_Listings_drawListing( $listing_id, null, true, false );
					echo CastBack_Buttons_DrawButtonPanel_markSold( $listing_id );
				}
				else if( $page == 'MyOffers' ) {  echo CastBack_Offers( $page, $posts_per_page ); }
				else if( $page == 'MyOrders' ) { echo CastBack_Offers( $page, $posts_per_page ); }
				else {
					echo 'function "'.$page.'" not found. (s74-09232025)';
				}
				
				echo '</div>'; // close <div id="$page">
			} else {
				echo 'Please log in. (s90-09292025)';
			}
		}
		else if( $button ) {
			if( $button == 'drawButtonPanel' ) {
				$user_id = get_current_user_id();
				if( !CastBack_userIsStripeConnected( $user_id ) ) {
					$user = wp_get_current_user();
					if( in_array( 'seller', (array) $user->roles ) ) { Test("A"); return CastBack_vendorRegistrationPrompt('/?page=dokan-seller-setup'); }
					else { Test("B"); return CastBack_vendorRegistrationPrompt(); }
				}
				else { echo CastBack_Buttons_DrawButtonPanel( $listing_id, $user_id, $button ); }
			}
			if( $button == 'deleteListing' ) {
				echo CastBack_Buttons_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			}
			if( $button == 'editListing' ) {
				echo CastBack_Buttons_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			}
			if( $button == 'shopFilters' ) { echo CastBack_Queries_shopFilterButtons( 'shop' ); }
			if( $button == 'archiveFilters' ) { echo CastBack_Queries_shopFilterButtons( 'archive' ); }
			if( $button == 'listingQueryFilterButtons' ) { echo CastBack_Queries_listingFilterButtons(); }
			if( $button == 'makeOffer' ) {
				// echo 'javascript:CastBack_Action_makeOffer_button("'.$post_id.'")';
				echo CastBack_Buttons_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			}
			
			// if( $button == ['sendMessage', 'submitOffer', 'acceptOffer', 'expireOffer', 'paymentComplete', 'disputeOrder', 'removeDispute', 'addTracking', 'completeOrder', ] ) {
				// echo CastBack_Buttons_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
			// if( $button == [ 'remove_dispute' ] ) {
				// echo CastBack_Buttons_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
		}
		else if( $action ) {
			if( $action == "isUserLoggedIn" ) {
				if( is_user_logged_in() ) { /* do nothing */ }
				else { echo 'd-none'; }
			}
			else if( $action == "userUpdate_viewOrder" ) {
				$user_id = get_current_user_id();
				if( have_rows('current_orders', $user_id ) ) {
					while( have_rows ('current_orders', $user_id) ) {
						the_row();
						if( get_sub_field( 'order_id' ) == $order_id ) {
							update_sub_field('last_viewed', wp_date('F j, Y g:i:s a' ) );
						}
					}
				}
				
				$order = wc_get_order( $order_id );	
				if( $order ) {
					if( !$order->get_user_id() ) {
						// $customer_id = get_field( 'customer_id', $order_id );
						// $customer_id = get_field( 'customer_id', $order_id );
						// $order->set_customer_id( $customer_id );
						// $order->save();
						// update_field( 'vendor', get_field( 'seller_id', $order_id ), $order_id );
					}
					
					$customer = $order->get_user_id();
					// Test( $customer );
					$vendor = get_field( 'vendor', $order_id );
					// Test( $vendor );
				}
			}
			else if( $action == "userIsAuthor" ) {
				if( get_current_user_id() == get_post( $listing_id )->post_author || current_user_can( 'administrator' ) ) { /* do nothing*/ }
				else { echo 'd-none'; }
			}
			else if( $action == "userCanPurchase" ) {
				if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
				if( !$user_id ) { $user_id = get_current_user_id(); }

				if( CastBack_userCanPurchase( $user_id ) === true ) { /*  */ }
				else { echo 'd-none'; }
			}
			else if( $action == "userHasCurrentOffer_SHOW" ) {
				$listing_id = get_the_ID();
				$order_id = CastBack_userHasCurrentOffer( $listing_id );
				if( $order_id ) { echo '/offers/view-offer/?order_id='.$order_id; }
				else { echo 'd-none'; }
			}
			else if( $action == "userHasCurrentOffer" ) {
				$listing_id = get_the_ID();
				if( CastBack_userHasCurrentOffer( $listing_id ) ) { echo 'd-none'; }
			}
			else if( $action == "userHasNotification_anywhere" ) {
				
			}
			else if( $action == "userHasNotification" ) {
				if( $order_id ) {
					if( CastBack_userHasNotification( $order_id, $user_id, $method ) ) {
						$bubble = "<span style='color: red; font-weight: 800;'>**</span>";
						
						echo $bubble;
						if( $method == 'customer' ) {
							echo '<script>var offers = document.getElementsByClassName("castback-notification-customer");
								for( var i = 0; i < offers.length; i++) {
									offers[i].firstChild.innerHTML = "'.$bubble.'" + "My Offers";
								}</script>'; }
						if( $method == 'seller' ) {
							echo '<script>var orders = document.getElementsByClassName("castback-notification-seller"); 
								for( var i = 0; i < orders.length; i++) {
									orders[i].firstChild.innerHTML = "'.$bubble.'" + "Orders";
								}</script>'; }
					}
				}
			}
			else if( $action == "userIsStripeConnected" ) {
				if( is_user_logged_in() && CastBack_userIsStripeConnected( get_current_user_id() ) ) { /* do nothing */ }
				else { echo 'd-none'; }
			}
			else if( $action == "displayDokanVendorDashboard" ) {
				if( !is_admin() ) {
					$redirect = true;
					$user = wp_get_current_user();
					if ( in_array( 'seller', (array) $user->roles ) ) { $redirect = false; }
					if ( in_array( 'administrator', (array) $user->roles ) ) { $redirect = false; }
					
					if ( $redirect ) { wp_safe_redirect( '/about/why-register/' ); }
					else { wp_safe_redirect( '/my-account/vendor/settings/payment-manage-dokan_stripe_express/' ); }
				}
			}
			else if( is_user_logged_in() ) {
				/* Customer / Vendor actions below this point */
				if( $action == "addListing" ) {
					if( isset( $listing_id ) && $listing_id == $action ) { CastBack_Listings_addListing(); }
					else { echo 'Wrong listing_id set. (s85-10012025)'; }
				}
				else if( $action == "isPublished" ) {
					if( isset( $listing_id ) ) {
							$listing = wc_get_product( $listing_id );
							if( $listing->get_status() != 'publish' ) { echo 'd-none'; }
					}
					else { echo 'No Listing ID found. (s90-11052025)'; }
				}
				else if( $action == "buyNow" ) {
					if( isset( $listing_id ) ) { CastBack_Action_buyNow( $listing_id ); }
					else { echo 'No Listing ID found. (s671-10212025)'; }
				}
				else if( $action == "makeOffer" ) {
					if( isset( $listing_id ) ) { CastBack_Action_makeOffer( $listing_id ); }
					else { echo 'No Listing ID found. (s95-09302025)'; }
				}
				else {
					echo 'Action "'.$action.' not found". (s98-09302025)';
				}
			} else {
				echo 'Please log in. (S98-09302025)';
			}
		}
		else if( $field ) {
			echo '<div id="'.$field.'" style="text-align: right;">';
			/* Added for Offer Amount on Listing/Offer pages */
			if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
			if( !$listing_id && isset( $_POST['order_id'] ) ) { $listing_id = get_field( 'listing_id', $_POST['order_id'] ); }
			if( !$listing_id && $order_id ) { $listing_id = get_field( 'listing_id', $order_id ); }
			
			// Test($listing_id);
			
			if( $field == 'ListingPrice' ) {
				echo '$' . get_field( 'listing_price', $listing_id );
			}
			else if( $field == 'getUserStatus' ) {
				if( in_array( get_current_user_id(), array(1,2,3) ) ) {
					if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
					if( !$user_id ) { $user_id = get_current_user_id(); }

					if( $user_id != 0 ) { $userIsLoggedIn = ', userIsLoggedIn'; }
					if( CastBack_userCanPurchase( $user_id ) === true ) { $userCanPurchase = ', userCanPurchase'; }
					if( CastBack_userIsStripeConnected( $user_id ) ) { $userIsStripeConnected = ', userIsStripeConnected'; }
					if( $order_id && CastBack_customerSeller( $order_id, $user_id ) ) { $customerSeller = ', userIsCustomerOrSeller'; }
					echo ( 'user_id: '. $user_id.$userIsLoggedIn.$userCanPurchase.$userIsStripeConnected.$customerSeller );
				}
			}
			else if( $field == 'test' ) {
				if( in_array( get_current_user_id(), array( 1, 2, 3, 466, 538 ) ) ) {
					
					// $listing_id = 2496; /* Pickle Balls */
					$customer_id = get_current_user_id();
					// $orders = get_field( 'current_orders', $customer_id );
					// $x = 0;
					// foreach( $orders as $order ) {
						// $x++;
						//	// if( $order['order_id'] == 3551 ) { delete_row( 'current_orders', $x, $customer_id ); }
						//	// echo var_dump( $order );
						// $order_id = $order['order_id'];
						// $lastViewed = $order['last_viewed'];
						// echo '<a href="/offers/view-offer/?order_id='.$order_id.'">'.$order_id.'</a> - Last Viewed: '. $lastViewed .'<br>';
					// }
					
					// if (CastBack_Action_autorefundUnshippedOrder( 3817, $customer_id )) { echo 'yay'; }
					// else { echo 'boo'; }

				}
			}
			else if( $field == 'shippingAddress' ) {
				echo CastBack_getAddress( $user_id );
			}
			else if( $field == 'customerAddress' ) {
				echo '<div style="text-align: left;">';
					echo '<h6>Customer Shipping Address:</h6>';
					
					echo CastBack_getAddress( get_field( 'customer_id', $order_id ), 'shipping', true );


				echo '</div>';
			}
			else if( $field == 'sellerAddress' ) {
				// Do something
			}
			else if( $field == 'order_status' ) {
				echo CastBack_Offers_orderStatus_cosmetic( $order_id, true );
			}
			else if( $field == 'makeOffer_amount' ) {
				if( $order_id ) { echo CastBack_Buttons_DrawButtonPanel( $order_id, get_current_user_id(), $field ); }
				else if( $listing_id ) { echo CastBack_Buttons_DrawButtonPanel( $listing_id, get_current_user_id(), $field ); }
				else {
					echo 'No ID found. (s693-10292025)';
				}
			}
			else if( $field == 'makeOfferNow' ) {
				echo CastBack_Buttons_DrawButtonPanel( $listing_id, get_current_user_id(), $field );
				}
			else if( $field == 'ShippingPrice' ) {
				$shipping_price = get_field( 'shipping_price', $listing_id );
				echo '<div id="shipping_price" style="display: none;">'.$shipping_price.'</div>';
				
				if( $shipping_price > 0 ) { echo '$'.$shipping_price; }
				else { echo 'Free Shipping!!'; }
			}
			else if( $field == 'MOT' ) {
				$MOT = CastBack_Filter_formatPriceField( CastBack_Offers_minimumOfferPrice() );
				
				echo '<strong id="castback_MOT" style="color: red;">$'.$MOT.'</strong>';
			}
			else if( $field == 'TotalPrice' ) {
				$shipping_price = get_field( 'shipping_price', $listing_id );
				$listing_price = get_field( 'listing_price', $listing_id );
				$order_amount = get_field( 'order_amount', $order_id );	
				
				// Test( $shipping_price );
				// Test( $listing_price );
				// Test( $order_amount );
				if( $order_id ) { $new_offer_amount = $order_amount; } 
				else { $new_offer_amount = $listing_price; } 
				
				// Test( $new_offer_amount );
				$total_price = (float)$shipping_price + (float)$new_offer_amount;

				$total_price = CastBack_Offers_minimumOfferPrice( $total_price );				
				
				echo '<div id="castback_total_price"><strong>$'.$total_price.'</strong></div>';
				// $output .= '<span class="castback_offer_amount_plusShipping">(+$<span id="offer_amount_plusShipping_value">'.$total_price.'</span> Shipping)</span>';
				echo '<script>CastBack_Offers_updateTotalPrice();</script>';
			}
			else if( $field == 'ViewOrderActionButtons' ) {
				if( CastBack_customerSeller( $order_id ) || is_user_admin() ) {
					echo '<div id="CastBack-ViewOrderActionButtons">'.CastBack_Offers_ViewOrderActionButtons( $order_id ).'</div>';
				} else { echo 'This is not your order. Please try again. (" '.$order_id.' ", " '.$listing_id.' ", S627-10282025).'; }
			}
			else if( $field == 'ViewOfferPanel' ) {
				echo '<div id="CastBack-ViewOfferPanel">'.CastBack_Offers_ViewOfferPanel( $order_id ).'</div>';
			}
			else if( $field == 'ViewOfferSidebar' ) {
				echo '<div id="CastBack-ViewOfferSidebar">'.CastBack_Offers_ViewOfferSidebar( $order_id, false ).'</div>';
			}
			else {
				/* Do something? */
				echo 'No valid "field" found, "'.$field.'" given. ('.get_the_ID().', s714-10282025)';
			}
			echo '</div>';
		}
		else {
			echo 'no shortcode found. ("'.get_the_ID().'", s75-09232025)';
		}
			
		return ob_get_clean();
} add_shortcode('CastBack', 'CastBack_ShortcodeHandler');