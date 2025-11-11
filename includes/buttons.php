<?php
/* WARNING */
/* Do NOT call buttons directly! */
/* Button security lives in DrawButtonPanel() */


/* Draw Button Panel */
function CastBack_Buttons_DrawButtonPanel( $post_id, $user_id = null, $button = null ) {	
	// if( !$post_id && isset( $_GET['order_id'] ) ) { $post_id = $_GET['order_id']; }
	if( !$post_id && isset( $_POST['order_id'] ) ) { $post_id = $_POST['order_id']; }
	
	// if( !$post_id && isset( $_GET['listing_id'] ) ) { $post_id = $_GET['listing_id']; }
	// if( !$post_id && isset( $_POST['listing_id'] ) ) { $post_id = $_POST['listing_id']; }

	$user_id = get_current_user_id();
	if( !$user_id && is_user_logged_in() ) { $user_id = get_current_user_id(); } 
	$author_id = get_post_field( 'post_author', $post_id );

	ob_start();

	if( $post_id ) {
		if( $button ) {
			/* Not currently used in Shortcodes... */
			
			/* Draw Buttons*/
			if( $button == 'drawButtonPanel' ) {
				echo CastBack_Buttons_DrawButtonPanel_togglePublish( $post_id );
				echo CastBack_Buttons_DrawButtonPanel_toggleSold( $post_id );
			}
			if( $button == 'buyNow' ) { echo CastBack_Buttons_DrawButtonPanel_buyNow( $post_id ); }
			if( $button == 'makeOffer' ) { echo CastBack_Buttons_DrawButtonPanel_makeOffer( $post_id ); }
			if( $button == 'makeOffer_amount' ) { echo CastBack_Buttons_DrawButtonPanel_makeOffer_amount( $post_id ); }
			if( $button == 'makeOfferNow' ) { echo CastBack_Buttons_DrawButtonPanel_makeOfferNow( $post_id ); }
			if( $button == 'editListing' ) { echo CastBack_Buttons_DrawButtonPanel_editListing( $post_id ); }
			if( $button == 'wishlistAdd' ) { echo CastBack_Buttons_DrawButtonPanel_wishlistAdd( $post_id ); }
			if( $button == 'deleteListing' ) { echo CastBack_Buttons_DrawButtonPanel_deleteListing( $post_id ); }
			
			// else {'Button not found. ("'.$button.'", b28-09292025)'; }
		} else if( get_post_type( $post_id ) == 'shop_order_placehold' ) {
			
			/* Draw Order */
			if( $user_id ) {
				if( $user_id == get_field( 'customer_id', $post_id ) ) { $isCustomer = true; }
				if( $user_id == get_field( 'seller_id', $post_id ) ) { $isSeller = true; }
					
				if( $isCustomer ) { echo CastBack_Buttons_DrawButtonPanel_viewOffer( $post_id ); }
				else if( $isSeller ) { echo CastBack_Buttons_DrawButtonPanel_viewOffer( $post_id ); }
				// else { echo 'This is not your order. (a27-09272025)'; }
			} else { echo 'Please log in. (bo25-09272025)'; }
		}
	} else { echo 'Missing data. b51-09262025'; }
	
	return ob_get_clean();
}


/* Listing Buttons */
function CastBack_Buttons_DrawButtonPanel_editListing( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\''.get_site_url().'/selling/edit-listing/?listing_id='.$post_id.'\'">Edit Listing</button>';
}
function CastBack_Buttons_DrawButtonPanel_wishlistAdd( $post_id ) {
	return do_shortcode('[yith_wcwl_add_to_wishlist]');
}
function CastBack_Buttons_DrawButtonPanel_toggleSold( $post_id ) {
	$product = wc_get_product( $post_id );
	if( $product->is_in_stock() ) {
		$method = 'markSold';
		$buttonText = 'Mark Unavailable';
	} else {
		$method = 'markUnsold';
		$classes .= 'castback-button-important';
		$buttonText = 'Mark Available';
	}
	if( $product->get_status() == 'trash' ) { $classes .= ' d-none'; }
	
	return '<button class="'.$classes.'" style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="javascript:CastBack_Action_'.$method.'('.$post_id.');">'.$buttonText.'</button>';
}
function CastBack_Buttons_DrawButtonPanel_togglePublish( $post_id ) {
	$product = wc_get_product( $post_id );
	if( $product->get_status() == 'publish' ) {
		$method = 'hideListing';
		$buttonText =  'Hide Listing';
	} else if( $product->get_status() == 'draft' ) {
		$method = 'publishListing';
		$buttonText =  'Publish Listing';
		$classes .= 'castback-button-important';
	}
	if( $product->get_status() == 'trash' ) { $classes .= ' d-none'; }
	
	return '<button class="'.$classes.'" style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="javascript:CastBack_Action_'.$method.'('.$post_id.');">'.$buttonText.'</button>';
}
function CastBack_Buttons_DrawButtonPanel_deleteListing( $post_id ) {
	$product = wc_get_product( $post_id );
	if( $product->get_status() == 'trash' ) {
		$method = 'restoreListing';
		$buttonText =  'Restore Listing';
		$classes .= 'castback-button-important';
	} else {
		$method = 'deleteListing';
		$buttonText =  'Archive Listing';
	}
	
	return '<button class="'.$classes.'" style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="javascript:CastBack_Action_'.$method.'('.$post_id.');">'.$buttonText.'</button>';
}

// /* Offer Buttons */
function CastBack_Buttons_DrawButtonPanel_buyNow( $post_id ) {
	// if( !CastBack_userIsStripeConnected() ) { return CastBack_vendorRegistrationPrompt(); }
	// else {
		return '<button class="castback-button-important" style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="javascript:CastBack_Action_buyNow_button('.$post_id.')">Buy Now</button>';
	// }
}
function CastBack_Buttons_DrawButtonPanel_makeOffer_amount( $post_id ) {
	if( !$order_id && isset( $_GET['order_id'] ) ) { $order_id = $_GET['order_id']; }
	if( !$order_id && isset( $_POST['order_id'] ) ) { $order_id = $_POST['order_id']; }
	
	if( get_post_type( $post_id ) == 'product' ) { /* post is Listing.*/
		$listing_price = get_field( 'listing_price', $post_id );
		$offer_amount = $listing_price;
		$shipping_price = get_field( 'shipping_price', $post_id );
	}
	else { /* post is Order, get Listing ID */
		$listing_id = get_field( 'listing_id', $post_id );
		// $listing_price = get_field( 'listing_price', $listing_id );
		$offer_amount = get_field( 'order_amount', $post_id );
		$shipping_price = get_field( 'shipping_price', $listing_id );
	}

	$output .= '<div class="castback_offer_amount_wrapper">';
		$output .= '<input id="castback_offer_amount" type="number" value="'.$offer_amount.'">';
		$output .= '<span style="display:none;" id="shipping_price">'.$shipping_price.'</span>';
		if( $shipping_price && $offer_amount ) { $total_price = (float)$shipping_price + (float)$offer_amount; }
		
		if( $total_price < CastBack_Offers_minimumOfferPrice() ) { $castback_MOT_flag_display = 'flex'; }
		else { $castback_MOT_flag_display = 'none'; }
		
		$output .= '<span id="castback_MOT_flag" style="color: red; display: '.$castback_MOT_flag_display.';">*</span>';
		
		$output .= "<script>function CastBack_Offers_updateTotalPrice( price ) {			
			var shippingPrice = document.getElementById('shipping_price').innerHTML;
			if( !parseFloat( shippingPrice ) ) { shippingPrice = 0; }
			else { shippingPrice = parseFloat( shippingPrice ); }
			
			var newAmount = parseFloat( document.getElementById('castback_offer_amount').value ) + shippingPrice;
				
			if( newAmount < ".CastBack_Offers_minimumOfferPrice()." ) {
				newAmount = ".CastBack_Offers_minimumOfferPrice().";
				document.getElementById('castback_MOT_flag').style.display = 'flex';
				document.getElementById('castback_MOT_container').style.display = 'flex';
			} else {
				document.getElementById('castback_MOT_flag').style.display = 'none';
				document.getElementById('castback_MOT_container').style.display = 'none';
			}
			document.getElementById('castback_total_price').innerHTML = '<strong>$'+newAmount.toFixed(2)+'</strong>';
		}</script>";
		
		$output .= "<script>
			const castback_offer_amount = document.getElementById('castback_offer_amount');
			castback_offer_amount.addEventListener( 'change', CastBack_Offers_updateTotalPrice );
		</script>";	
	$output .= '</div>';
	return $output;
}
function CastBack_Buttons_DrawButtonPanel_makeOffer( $post_id ) {
	return '<button class="castback-button-important" style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="javascript:CastBack_Action_makeOffer_button('.$post_id.')">Make Offer</button>';
}
function CastBack_Buttons_DrawButtonPanel_makeOfferNow( $post_id ) {
	// if( !CastBack_userIsStripeConnected() ) { return CastBack_vendorRegistrationPrompt(); }
	// else {
		return '<button class="castback-button-important" style="margin-bottom: 0.5rem; width: 60%;" type="reset" onclick="javascript:CastBack_Action_makeOfferNow_button('.$post_id.')">Make Offer Now!</button>';
	// }
}
function CastBack_Buttons_DrawButtonPanel_viewOrder( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\'/selling/my-orders/?order_id=' .$post_id.'\'">View Order</button>';
}
function CastBack_Buttons_DrawButtonPanel_viewOffer( $post_id ) {
		return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\'/offers/view-offer/?order_id=' .$post_id.'\'">View Offer</button>';
}