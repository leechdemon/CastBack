/* Listings */
/* - Security: Does not require security, since all Users can create Listings. */
function CastBack_Action_addListing_button() {
	// Listing version?
	// document.getElementById( 'CastBack-ViewOfferButtons' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Listings_addListing",
			"AJAX": true,
			"user_id": CastBack.user_id,
		},
		success: function (data) {
			CastBack_Action_refreshListing( listing_id, data );
		}
	});
}
function CastBack_Action_markSold( listing_id ) {
	document.getElementById( 'CastBack-EditListing' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Listings_markSold",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			CastBack_Action_refreshListing( listing_id, data );
		}
	});
}
function CastBack_Action_markUnsold( listing_id ) {
	document.getElementById( 'CastBack-EditListing' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Listings_markUnsold",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			CastBack_Action_refreshListing( listing_id, data );
		}
	});
}
function CastBack_Action_publishListing( listing_id ) {
	document.getElementById( 'CastBack-EditListing' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Listings_publishListing",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			CastBack_Action_refreshListing( listing_id, data );
		}
	});
}
function CastBack_Action_hideListing( listing_id ) {
	document.getElementById( 'CastBack-EditListing' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Listings_hideListing",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			CastBack_Action_refreshListing( listing_id, data );
		}
	});
}
function CastBack_Action_deleteListing( listing_id ) {
	document.getElementById( 'CastBack-EditListing' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Listings_deleteListing",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			CastBack_Action_refreshListing( listing_id, data );
		}
	});
}
function CastBack_Action_restoreListing( listing_id ) {
	document.getElementById( 'CastBack-EditListing' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Listings_restoreListing",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			CastBack_Action_refreshListing( listing_id, data );
		}
	});
}

/* Offers */
/* - Security: Does not require security, since all Users can create Listings. */
function CastBack_Action_makeOfferNow_button( listing_id ) {
	var Buttons = document.getElementById( 'CastBack-ViewOrderActionButtons' );
	if( Buttons ) { Buttons.style.opacity = "0.5"; }
	else { Buttons = document.getElementById( 'CastBack-ViewOfferPanel' ); }
	
	Buttons.style.opacity = "0.5";
	var order_amount = document.getElementById("castback_offer_amount").value;
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_buyNow",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id, 
			"order_amount": order_amount, 
		},
		success: function (data) {
			// console.log( data );
			window.location.href = "/offers/view-offer/?order_id=" + data;
		}
	});
}
function CastBack_Action_buyNow_button( listing_id ) {
	document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "0.5";
	var order_amount = document.getElementById("castback_offer_amount").value;
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_buyNow",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id, 
			"order_amount": order_amount, 
		},
		success: function (data) {
			console.log( data );
			window.location.href = "/offers/view-offer/?order_id=" + data;
		}
	});
}
function CastBack_Action_sendMessage_button( order_id ) {
	document.getElementById( 'CastBack-ViewOfferButtons' ).style.opacity = "0.5";
	var new_message = document.getElementById("castback_new_message").value;

	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_sendMessage",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id, 
			"new_message": new_message,
		},
		success: function (data) {
			CastBack_Offers_refreshOrder( order_id );
		}
	});
}

/* - Security: CastBack_customerSeller() */
function CastBack_Action_submitOffer_button( order_id ) {
	document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "0.5";
	var order_amount = document.getElementById("castback_offer_amount").value;
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_submitOffer",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
			"order_amount": order_amount,
		},
		success: function (data) {
			CastBack_Offers_refreshOrder( order_id );
		}
	});
}
function CastBack_Action_acceptOffer_button( order_id ) {
	document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_acceptOffer",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id, 
		},
		success: function (data) {
			CastBack_Offers_refreshOrder( order_id );
		}
	});
}
function CastBack_Action_addTracking_button( order_id ) {
	document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "0.5";
	var new_tracking_number = document.getElementById("castback_new_tracking_number").value;
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_addTracking",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id, 
			"new_tracking_number": new_tracking_number, 
		},
		success: function (data) {
			CastBack_Offers_refreshOrder( order_id );
		}
	});
}
function CastBack_Action_completeOrder_button( order_id ) {
	document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_completeOrder",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
		},
		success: function (data) {
			CastBack_Offers_refreshOrder( order_id );
		}
	});
}
function CastBack_Action_disputeOrder_button( order_id ) {
	document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_disputeOrder",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
		},
		success: function (data) {
			CastBack_Offers_refreshOrder( order_id );
		}
	});
}
function CastBack_Action_removeDispute_button( order_id ) {
	document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_removeDispute",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
		},
		success: function (data) {
			CastBack_Offers_refreshOrder( order_id );
		}
	});
}

/* Refresh Offer Actions */
/* - Security: n/a */
function CastBack_Offers_refreshOrder( order_id ) {	
	CastBack_Offers_refreshOrderAction_Buttons( order_id );
	// CastBack_Offers_refreshOffer_Buttons( order_id );
	CastBack_Offers_refreshOrder_Sidebar( order_id );
	/* Add this... */
	// CastBack_Offers_refreshOrder_Status( order_id );
}
function CastBack_Offers_refreshOrderAction_Buttons( order_id ) {	
	document.getElementById( 'CastBack-ViewOrderActionButtons').style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Offers_ViewOrderActionButtons",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'CastBack-ViewOrderActionButtons' ).innerHTML = data;
			document.getElementById( 'CastBack-ViewOrderActionButtons' ).style.opacity = "1.0";
		}
	});
}
function CastBack_Offers_refreshOffer_Buttons( order_id ) {	
	document.getElementById( 'CastBack-ViewOfferButtons').style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Offers_ViewOfferButtons",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'CastBack-ViewOfferButtons' ).innerHTML = data;
			document.getElementById( 'CastBack-ViewOfferButtons' ).style.opacity = "1.0";
		}
	});
}
function CastBack_Offers_refreshOrder_Sidebar( order_id ) {	
	document.getElementById( 'CastBack-ViewOfferSidebar').style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Offers_ViewOfferSidebar",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'CastBack-ViewOfferSidebar' ).innerHTML = data;
			document.getElementById( 'CastBack-ViewOfferSidebar' ).style.opacity = "1.0";
		}
	});
}
/* Refresh Listing Actions */
/* - Security: n/a */
function CastBack_Action_refreshListing( listing_id, data ) {
	if( data ) {
		// console.log( data );
		window.location.href = "/selling/edit-listing/?listing_id="+listing_id;
	} else {
		console.log("AJAX failed. Wrong 'user_id'?");
	}
}