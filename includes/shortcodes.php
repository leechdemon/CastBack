<?php  


function print_menu_shortcode($atts, $content = null) {
	extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
	return wp_nav_menu( array( 'menu' => $name, 'menu_class' => 'myclass', 'echo' => false ) );
} add_shortcode('menu', 'print_menu_shortcode');


function CastBack_MyOffers( $atts, $content = null ) {
	extract(shortcode_atts(array( 'page' => null, 'class' => null ), $atts));
	if( $page ) { $page = 'CastBack-'.$page; }

	ob_start();

	echo '<div id="CastBack-MyOffers">';
		if( $_GET['order_id'] ) { echo CastBack_offers_draw_order_page( $_GET[ 'order_id' ], false ); }
		else { echo CastBack_Offers( 'MyOffers', $page ); }
	echo '</div>';
	
	return ob_get_clean();
} add_shortcode('CastBack_MyOffers', 'CastBack_MyOffers');
function CastBack_MyOrders( $atts, $content = null ) {
	extract(shortcode_atts(array( 'page' => null, 'class' => null ), $atts));
	if( $page ) { $page = 'CastBack-'.$page; }
	
	// acf_form_head();
	
	ob_start();

	echo '<div id="CastBack-MyOrders">';
		if( $_GET['order_id'] ) { echo CastBack_offers_draw_order_page( $_GET[ 'order_id' ], false ); }
		else { echo CastBack_Offers( 'MyOrders', $page ); }
	echo '</div>';
	
	return ob_get_clean();
} add_shortcode('CastBack_MyOrders', 'CastBack_MyOrders');
function CastBack_MyListings( $atts, $content = null ) {
	extract(shortcode_atts(array( 'page' => null, 'class' => null ), $atts));
	if( $page ) { $page = 'CastBack-'.$page; }
	
	acf_form_head();
	
	ob_start();
	
	if( $_GET['listing_id'] ) { 
		echo Castback_edit_listing( $_GET['listing_id'], false );
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
		if( get_current_user_id() ) {
			echo '<div id="CastBack-MyAccount">';
				// echo do_shortcode('[CastBack_Inbox page="MyAccount"]');
				// echo do_shortcode('[CastBack_MyListings page="MyAccount"]');
				echo do_shortcode('[CastBack_MyOffers page="MyAccount"]');
				echo do_shortcode('[CastBack_MyOrders page="MyAccount"]');
			echo '</div>';
			echo '<div id="CastBack-Logout" class="button"><a href="/wp-login.php?action=logout">Log out</a></div>';
		
		} else {
			echo 'Please <a href="/login">log in</a>.';
		}
		
	// }
	
	return ob_get_clean();
} add_shortcode('CastBack_MyAccount', 'CastBack_MyAccount');