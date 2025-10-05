<?php  

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
				} else if( $page == 'MyAccount' ) {
						echo 'CastBack_MyAccount'; 
						// echo CastBack_MyAccount( $page, $posts_per_page ); 
				} else if( $page == 'MyListings' ) {
						echo CastBack_Listings( $listing_id, $posts_per_page ); 
				} else if( $page == 'DrawListing' ) {
					if( isset( $listing_id ) ) {
						echo CastBack_Listings_drawListing( $listing_id, null, true, false );
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
								echo CastBack_Offers( $order_id, 1 );
								echo CastBack_Offers_drawOrderDetails( $order_id );
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