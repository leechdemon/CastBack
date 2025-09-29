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
} add_shortcode('CastBack_AddListing_Button', 'CastBack_AddListing_Button');
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
} add_shortcode('CastBack_MakeOffer_Button', 'CastBack_MakeOffer_Button');

function CastBack_ShortcodeHandler( $atts, $content = null ) {
		global $castbackVersion;
		extract(shortcode_atts(array( 'page' => null, 'listing_id' => null, 'order_id' => null, 'featuredImage' => null, 'class' => null, 'setQuery' => null, 'posts_per_page' => null, 'location' => null ), $atts));
		

		if( !isset( $listing_id ) && isset( $_GET['listing_id'] ) ) { $listing_id = $_GET['listing_id']; }
		// if( !isset( $listing_id ) && isset( $_POST['listing_id'] ) ) { $listing_id = $_POST['listing_id']; }
		if( !isset( $listing_id ) && get_field( 'listing_id' ) ) { $listing_id = get_field( 'listing_id' ); }

		if( !isset( $order_id ) && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
		
		ob_start();
		wp_enqueue_style( 'CastBack' );
	
		if( get_current_user_id() ) {
			if( $page ) {
				echo '<div id="'.$page.'">';
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
						echo CastBack_Listings_drawListing( $listing_id, null, true, false );
				} else if( $page == 'MyOffers' ) { 
						if( isset( $order_id ) ) {
							Test('A');
							echo CastBack_Offers( $order_id, 1 );
							echo CastBack_Offers_drawOrderPage( $order_id, 'MyOffers', false );
						} else {
							Test('B');
							echo CastBack_Offers( $page, $posts_per_page );
						}
				} else if( $page == 'MyOrders' ) { 
						if( isset( $order_id ) ) {
							Test('C');
							echo CastBack_Offers( $order_id, 1 );
							echo CastBack_Offers_drawOrderPage( $order_id, 'MyOrders', false );
						} else {
							Test('D');
							echo CastBack_Offers( $page, $posts_per_page );
						}
				} else { echo 'function "'.$page.'" not "page" found. (s74-09232025)'; }
				echo '</div>'; // close <div id="$page">
			} else if( isset( $featuredImage ) ) {
				echo '<img src="'.get_field( 'featuredImage', $listing_id ).'">';
			} else { echo 'nothing found. ("'.get_the_ID().'", s75-09232025)'; }
		} else {
			if( $page != 'MyNotifications' ) {
				echo 'please log in. (s76-09232025)';
				// wp_redirect( get_site_url('/login').'?redirect_to_'.get_site_url( get_the_permalink() ).  );
				// die();
			}
		}
			
		return ob_get_clean();
} add_shortcode('CastBack', 'CastBack_ShortcodeHandler');