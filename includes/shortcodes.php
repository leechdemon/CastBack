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


function CastBack_MyOffers( $atts, $content = null ) {
	extract(shortcode_atts(array( 'page' => null, 'class' => null ), $atts));
	if( $page ) { $page = $page; }
	else { $page = 'MyOffers'; }


	ob_start();

	if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
	if( !$order_id && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
	
	echo '<div id="CastBack-MyOffers">';
		if( $order_id ) { echo CastBack_offers_draw_order_page( $order_id, 'CastBack-'.$page, false ); }
		else { echo CastBack_Offers( 'MyOffers', $page ); }
	echo '</div>';
	
	return ob_get_clean();
} add_shortcode('CastBack_MyOffers', 'CastBack_MyOffers');
function CastBack_MyOrders( $atts, $content = null ) {
	extract(shortcode_atts(array( 'page' => null, 'class' => null ), $atts));
	if( $page ) { $page = $page; }
	else { $page = 'MyOrders'; }
	
	// acf_form_head();
	
	ob_start();

	if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
	if( !$order_id && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
	
	echo '<div id="CastBack-MyOrders">';
		if( $order_id ) { echo CastBack_offers_draw_order_page( $order_id, 'CastBack-'.$page, false ); }
		else { echo CastBack_Offers( 'MyOrders', $page ); }
	echo '</div>';
	
	return ob_get_clean();
} add_shortcode('CastBack_MyOrders', 'CastBack_MyOrders');
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

function CastBack_MyAccount( $atts, $content = null ) {
	ob_start();

	// if( $_GET['order_id'] ) { echo CastBack_offers_draw_order_page( $_GET['order_id'], false ); }
	// else if( $_GET['listing_id'] ) { echo Castback_edit_listing( $_GET['listing_id'], false ); }
	// else {

		
		echo '<style>
			#CastBack-MyAccount, #CastBack-MyListings, #CastBack-MyOffers, #CastBack-MyOrders, #castback-order { display: inline-block; width:50%; float: left; padding: 1rem; margin: 1rem; background-color: tan; }
			#CastBack-MyAccount { float: left; background: unset; }
			#CastBack-Logout { float: right; width: fit-content; }
		</style>';
		if( is_user_logged_in() ) {
			echo '<div id="CastBack-MyAccount">';
				// echo do_shortcode('[CastBack_Inbox page="MyAccount"]');
				// echo do_shortcode('[CastBack_MyListings page="MyAccount"]');
				echo do_shortcode('[CastBack_MyOffers page="MyAccount"]');
				echo do_shortcode('[CastBack_MyOrders page="MyAccount"]');
			echo '</div>';
			// echo '<div id="CastBack-Logout" class="button"><a class="button" href="/wp-login.php/?action=logout&redirect_to='.get_site_url().'/login">Log out</a></div>';
		
		} else {
			// echo do_shortcode('[CastBack_ForcedLoginPage]');
			echo 'Please <a href="/login">log in</a>.';
		}
		// echo do_shortcode('[dokan_dashboard]');
		
		// dokan_get_template_part( 'dashboard/new-dashboard' );
		// dokan_get_template_part( 'dashboard/dashboard' );
		// dokan_get_template_part( 'dashboard/edit-account' );
		// dokan_get_template_part( 'settings/payment' );
		// dokan_get_template_part( 'settings/store' );
		// dokan_get_template_part( 'orders/orders' );
		
	// }
	
	return ob_get_clean();
} add_shortcode('CastBack_MyAccount', 'CastBack_MyAccount');
// function CastBack_ForcedLoginPage( $atts, $content = null ) {
	// echo '<script>window.location.href="'.get_site_url() . '/login";</script>';

// } add_shortcode('CastBack_ForcedLoginPage', 'CastBack_ForcedLoginPage');
function CastBack_LogoutButton( $atts, $content = null ) {
	ob_start();
	
	if( get_current_user_id() ) {
		echo '<a class="button" href="'.wp_logout_url( get_site_url() . '/login' ).'">Log out</a>';
	}
	
	return ob_get_clean();
} add_shortcode('CastBack_LogoutButton', 'CastBack_LogoutButton');
