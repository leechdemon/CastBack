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
		echo '<a class="button" href="'. get_site_url().'/login">Make Offer (login)</a>';
	}

	return ob_get_clean();
} add_shortcode('CastBack_MakeOffer_Button', 'CastBack_MakeOffer_Button');


function CastBack_MyOffers( $page, $posts_per_page ) {
	if( isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
	if( !isset( $order_id ) && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
	
	$output = '<div id="CastBack-'.$page.'">';
		if( $order_id ) { $output .= CastBack_offers_draw_order_page( $order_id, 'CastBack-'.$page, false ); }
		else {  $output .= CastBack_Offers( $page, $page, $posts_per_page ); }
	$output .= '</div>';
	return $output;
}
function CastBack_MyOrders( $page, $post_per_page ) {
	return CastBack_MyOffers( $page, $post_per_page );
}

function CastBack_ShortcodeHandler( $atts, $content = null ) {
		global $castbackVersion;
		extract(shortcode_atts(array( 'page' => null, 'listing_id' => null, 'order_id' => null, 'featuredImage' => null, 'class' => null, 'setQuery' => null, 'posts_per_page' => null, 'location' => null ), $atts));
		

		
		ob_start();
		wp_enqueue_style( 'CastBack' );
	
		if( get_current_user_id() ) {
			if( $page ) {
				// if( $setQuery == 'true' ) {
					
				// }
				if( $page == 'LogOut' ) { 
					echo '<button onclick="window.location.href=\''.esc_url( wp_logout_url( get_site_url() .'/login' ) ).'\'">Log out</button>';
				} else if( $page == 'MyNotifications' ) {
					/* $location unused? */
					echo CastBack_MyNotifications( $page, $location );
				} else if( $page == 'DrawListing' ) {
					$template_id = '949';
					echo CastBack_listings_draw_listing( $listing_id, $template_id, false );
				} else if( function_exists('CastBack_'.$page) ) {
					/* MyOffers, MyOrders, MyAccount, MyListings(?) */
					echo call_user_func('CastBack_'.$page, $page, $posts_per_page);
				} else { echo 'function "'.$page.'" not "page" found. (s74-09232025)'; }
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





// function custom_shortcode_styles() {
    // global $post;
    // if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'CastBack') ) {
        // wp_enqueue_style( 'CastBack-MyOffers', plugins_url() . '/castback.css', array(), $castbackVersion, 'all' );
        // wp_enqueue_style( 'CastBack' );
    // }
// } add_action( 'wp_enqueue_scripts', 'custom_shortcode_styles');




function CastBack_MyListings( $atts, $content = null ) {
	extract(shortcode_atts(array( 'listing_id' => null, 'page' => null, 'class' => null ), $atts));
	if( $page ) { $page = 'CastBack-'.$page; }
	
	acf_form_head();
	
	ob_start();
	
	if( !$listing_id ) { $listing_id = $_GET['listing_id']; }
	if( $listing_id ) { 
		echo Castback_edit_listing( $listing_id, false );
	}
	else {
		echo CastBack_Listings( 'MyListings', $page, false );
	}
	
	ob_get_flush();
} add_shortcode('CastBack_MyListings', 'CastBack_MyListings');

/* Not in use? IDK... 9/23/25 */

// function CastBack_ForcedLoginPage( $atts, $content = null ) {
	// echo '<script>window.location.href="'.get_site_url() . '/login";</script>';

// } add_shortcode('CastBack_ForcedLoginPage', 'CastBack_ForcedLoginPage');
