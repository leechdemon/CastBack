<?php
/* WARNING */
/* Do NOT call buttons directly! */
/* Button security lives in DrawButtonPanel() */


/* Draw Button Panel */
function CastBack_Action_DrawButtonPanel( $post_id, $user_id = null, $button = null ) {	
	$user_id = get_current_user_id();
	if( !$user_id && is_user_logged_in() ) { $user_id = get_current_user_id(); } 
	$author_id = get_post_field( 'post_author', $post_id );

	ob_start();

	if( $post_id ) {
		if( $button ) {
			/* Not currently used in Shortcodes... */
			
			/* Draw Buttons*/
			// if( $button == 'viewListing' ) { echo CastBack_Action_DrawButtonPanel_viewListing( $post_id ); }
			// else if( $button == 'makeOffer' ) { echo CastBack_Action_DrawButtonPanel_makeOffer( $post_id ); }
			// else if( $button == 'editListing' ) { echo CastBack_Action_DrawButtonPanel_editListing( $post_id ); }
			// else if( $button == 'wishlistAdd' ) { echo CastBack_Action_DrawButtonPanel_wishlistAdd( $post_id ); }
			
			// else {'Button not found. ("'.$button.'", b28-09292025)'; }
		} else if( get_post_type( $post_id ) == 'product' ) {
			/* I THINK this is all unused in current (Elementor) Listing views. */
			
			/* Draw Listing */	
			echo CastBack_Action_DrawButtonPanel_viewListing( $post_id );
			
			if( $user_id ) {
				if( $user_id == $author_id ) {
					echo CastBack_Action_DrawButtonPanel_editListing( $post_id );
					echo CastBack_Action_DrawButtonPanel_markSold_confirmationButton( $post_id );
				}
				else {
					echo CastBack_Action_DrawButtonPanel_wishlistAdd( $post_id );
					echo CastBack_Action_DrawButtonPanel_makeOffer( $post_id );
				}
			} else { echo 'Please log in. (bl25-09272025)'; }
		} else if( get_post_type( $post_id ) == 'shop_order_placehold' ) {
			
			/* Draw Order */
			if( $user_id ) {
				if( $user_id == get_field( 'customer_id', $post_id ) ) { $isCustomer = true; }
				if( $user_id == get_field( 'seller_id', $post_id ) ) { $isSeller = true; }
					
				if( $isCustomer ) { echo CastBack_Action_DrawButtonPanel_viewOffer( $post_id ); }
				else if( $isSeller ) { echo CastBack_Action_DrawButtonPanel_viewOffer( $post_id ); }
				// else { echo 'This is not your order. (a27-09272025)'; }
			} else { echo 'Please log in. (bo25-09272025)'; }
		}
	} else { echo 'Missing data. b51-09262025'; }
	
	return ob_get_clean();
}


/* Listing Buttons */
function CastBack_Action_DrawButtonPanel_viewListing( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\'' .get_the_permalink( $post_id ).'\'">View Listing</button>';
}
function CastBack_Action_DrawButtonPanel_markSold( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="javascript:CastBack_Action_markSold('.$post_id.');">Confirm Mark as Sold?</button>';
}
function CastBack_Action_DrawButtonPanel_markSold_confirmationButton( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\''.get_site_url().'/selling/listings/mark-sold/?listing_id='.$post_id.'\'">Mark as Sold</button>';
}
function CastBack_Action_DrawButtonPanel_editListing( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\''.get_site_url().'/selling/listings/?listing_id='.$post_id.'\'">Edit Listing</button>';
}
function CastBack_Action_DrawButtonPanel_wishlistAdd( $post_id ) {
	return do_shortcode('[yith_wcwl_add_to_wishlist]');
}

/* Offer Buttons */
function CastBack_Action_DrawButtonPanel_makeOffer( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="javascript:CastBack_Action_makeOffer_button('.$post_id.')">Make Offer</button>';
}
function CastBack_Action_DrawButtonPanel_viewOrder( $post_id ) {
	return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\'/selling/my-orders/?order_id=' .$post_id.'\'">View Order</button>';
}
function CastBack_Action_DrawButtonPanel_viewOffer( $post_id ) {
		return '<button style="margin-bottom: 0.5rem; width: 100%;" type="reset" onclick="location.href=\'/buying/offers/?order_id=' .$post_id.'\'">View Offer</button>';
}