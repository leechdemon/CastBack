<?php  


function print_menu_shortcode($atts, $content = null) {
	extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
	return wp_nav_menu( array( 'menu' => $name, 'menu_class' => 'myclass', 'echo' => false ) );
} add_shortcode('menu', 'print_menu_shortcode');


function CastBack_AddListing_Button($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	ob_start();
	
	if( is_user_logged_in() ) {
		$url = get_site_url().'/selling/listings/';
		echo '<a class="button" href="javascript:CastBack_action_add_listing_button();">Add Listing</a>';
	} else {
		// echo '<a class="button" href="'. get_site_url().'/login?redirect_to=javascript:CastBack_action_make_offer_button('.$listing_id.');">Add Listing (login)</a>';
		echo '<a class="button" href="'. get_site_url().'/login">Add Listing (login)</a>';
	}

	return ob_get_clean();
} 
// add_shortcode('CastBack_AddListing_Button', 'CastBack_AddListing_Button');
function CastBack_MakeOffer_Button($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	ob_start();
	
	if( is_user_logged_in() ) {
		$url = get_site_url().'/buying/offers/';
		echo '<a class="button" href="javascript:CastBack_action_make_offer_button('.$listing_id.');">Make Offer</a>';
	} else {
		// echo '<a class="button" href="'. get_site_url().'/login?redirect_to=javascript:CastBack_action_make_offer_button('.$listing_id.');">Make Offer (login)</a>';
		echo '<button" href="'. get_site_url().'/login">Make Offer (login)</a>';
	}

	return ob_get_clean();
} 
// add_shortcode('CastBack_MakeOffer_Button', 'CastBack_MakeOffer_Button');

function CastBack_ShortcodeHandler( $atts, $content = null ) {
		global $castbackVersion;
		extract(shortcode_atts(array( 'page' => null, 'action' => null, 'button' => null, 'listing_id' => null, 'order_id' => null, 'featuredImage' => null, 'class' => null, 'setQuery' => null, 'posts_per_page' => null, 'location' => null ), $atts));
		

		if( !isset( $listing_id ) && isset( $_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
		// if( !isset( $listing_id ) && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
		if( !isset( $listing_id ) && get_field( 'listing_id' ) ) { $listing_id = get_field( 'listing_id' ); }

		if( !isset( $order_id ) && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
		
		ob_start();
		wp_enqueue_style( 'CastBack' );
	
		if( $page ) {
			/* We only show pages to logged-in users... */
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
				} else if( $page == 'MyOffers' ) { 
						if( isset( $listing_id ) ) {
							echo CastBack_Listings_drawListing( $listing_id, null, true, false );
							$order_id = CastBack_action_make_offer( $listing_id, false );
						} else {
							if( isset( $order_id ) ) {
								echo CastBack_Offers( $order_id, 1 );
								echo CastBack_Offers_drawOrderDetails( $order_id );
							} else {
								echo CastBack_Offers( $page, $posts_per_page );
							}
						}
				} else if( $page == 'MyOrders' ) { 
						if( isset( $order_id ) ) {
							echo CastBack_Offers( $order_id, 1 );
							echo CastBack_Offers_drawOrderDetails( $order_id );
						} else {
							echo CastBack_Offers( $page, $posts_per_page );
						}
				} else {
					echo 'function "'.$page.'" not "page" found. (s74-09232025)';
				}
				echo '</div>'; // close <div id="$page">
			} else {
				echo 'Please log in. (s90-09292025)';
			}
		// } else if( $button == 'drawButtonPanel' ) {
			// echo CastBack_action_DrawButtonPanel( $listing_id );
		} else if( $button ) {
			// echo CastBack_action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
			// if( $button == 'makeOffer' ) {
				// echo 'javascript:CastBack_action_make_offer_button("'.$post_id.'")';
				// echo CastBack_action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
			// if( $button == ['sendMessage', 'submitOffer', 'acceptOffer', 'expireOffer', 'paymentComplete', 'disputeOrder', 'removeDispute', 'addTracking', 'completeOrder', ] ) {
				// echo CastBack_action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
			// if( $button == [ 'remove_dispute' ] ) {
				// echo CastBack_action_DrawButtonPanel( $listing_id, get_current_user_id(), $button );
			// }
		} else if( $action ) {
			if( $action == "makeOffer" ) {
				if( is_user_logged_in() ) {
					if( isset( $listing_id ) ) {
						CastBack_action_make_offer( $listing_id );
					} else {
						echo 'No Listing ID found. (s121-09302025)';
					}
				} else {
					echo 'Please log in. (s118-09302025)';
				}
			}
			
		} else {
			echo 'nothing found. ("'.get_the_ID().'", s75-09232025)';
		}
			
		return ob_get_clean();
} add_shortcode('CastBack', 'CastBack_ShortcodeHandler');