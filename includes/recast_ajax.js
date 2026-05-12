/* Listings */
/* - Security: Does not require security, since all Users can create Listings. */
function Recast_Action_addListing_button() {
	// Listing version?
	// document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Listings_addListing",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
		},
		success: function (data) {
			Recast_Action_refreshListing( listing_id, data );
		}
	});
}
function Recast_Action_markSold( listing_id ) {
	Recast_Action_dimListing();
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Listings_markSold",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id,
		},
		success: function (data) {
			Recast_Action_refreshListing( listing_id, data );
		}
	});
}
function Recast_Action_markUnsold( listing_id ) {
	Recast_Action_dimListing();
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Listings_markUnsold",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id,
		},
		success: function (data) {
			Recast_Action_refreshListing( listing_id, data );
		}
	});
}
function Recast_Action_publishListing( listing_id ) {
	Recast_Action_dimListing();
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Listings_publishListing",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id,
		},
		success: function (data) {
			Recast_Action_refreshListing( listing_id, data );
		}
	});
}
function Recast_Action_hideListing( listing_id ) {
	Recast_Action_dimListing();
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Listings_hideListing",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id,
		},
		success: function (data) {
			Recast_Action_refreshListing( listing_id, data );
		}
	});
}
function Recast_Action_deleteListing( listing_id ) {
	Recast_Action_dimListing();
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Listings_deleteListing",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id,
		},
		success: function (data) {
			Recast_Action_refreshListing( listing_id, data );
		}
	});
}
function Recast_Action_restoreListing( listing_id ) {
	Recast_Action_dimListing();
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Listings_restoreListing",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id,
		},
		success: function (data) {
			Recast_Action_refreshListing( listing_id, data );
		}
	});
}

/* Offers */
/* - Security: Does not require security, since all Users can create Listings. */
function Recast_Action_makeOfferNow_button( listing_id ) {
	var Buttons = document.getElementById( 'Recast-ViewOrderActionButtons' );
	if( Buttons ) { Buttons.style.opacity = "0.5"; }
	else { Buttons = document.getElementById( 'Recast-ViewOfferPanel' ); }
	
	Buttons.style.opacity = "0.5";
	var order_amount = document.getElementById("recast_offer_amount").value;
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_buyNow",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id, 
			"order_amount": order_amount, 
		},
		success: function (data) {
			// console.log( data );
			window.location.href = "/offers/view-offer/?order_id=" + data;
		}
	});
}
function Recast_Action_buyNow_button( listing_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	var order_amount = document.getElementById("recast_offer_amount").value;
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_buyNow",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"listing_id": listing_id, 
			"order_amount": order_amount, 
		},
		success: function (data) {
			console.log( Recast.user_id * 1 );
			console.log( data );
			window.location.href = "/offers/view-offer/?order_id=" + data;
		}
	});
}
function Recast_Action_sendMessage_button( order_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	var new_message = document.getElementById("recast_new_message").value;

	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_sendMessage",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id, 
			"new_message": new_message,
		},
		success: function (data) {
			Recast_Offers_refreshOrder( order_id );
		}
	});
}

/* - Security: Recast_customerSeller() */
function Recast_Action_submitOffer_button( order_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	var order_amount = document.getElementById("recast_offer_amount").value;
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_submitOffer",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id,
			"order_amount": order_amount,
		},
		success: function (data) {
			Recast_Offers_refreshOrder( order_id );
		}
	});
}
function Recast_Action_acceptOffer_button( order_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_acceptOffer",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id, 
		},
		success: function (data) {
			Recast_Offers_refreshOrder( order_id );
		}
	});
}
function Recast_Action_addTracking_button( order_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	var new_tracking_number = document.getElementById("recast_new_tracking_number").value;
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_addTracking",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id, 
			"new_tracking_number": new_tracking_number, 
		},
		success: function (data) {
			Recast_Offers_refreshOrder( order_id );
		}
	});
}
function Recast_Action_completeOrder_button( order_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_completeOrder",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id,
		},
		success: function (data) {
			Recast_Offers_refreshOrder( order_id );
		}
	});
}
function Recast_Action_disputeOrder_button( order_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_disputeOrder",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id,
		},
		success: function (data) {
			Recast_Offers_refreshOrder( order_id );
		}
	});
}
function Recast_Action_removeDispute_button( order_id ) {
	document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Action_removeDispute",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id,
		},
		success: function (data) {
			Recast_Offers_refreshOrder( order_id );
		}
	});
}

/* Refresh Offer Actions */
/* - Security: n/a */
function Recast_Offers_refreshOrder( order_id ) {	
	Recast_Offers_refreshOrderAction_Buttons( order_id );
	// Recast_Offers_refreshOffer_Buttons( order_id );
	Recast_Offers_refreshOrder_Sidebar( order_id );
	/* Add this... */
	// Recast_Offers_refreshOrder_Status( order_id );
}
function Recast_userHasOffers() {	
	var offers = document.getElementsByClassName('recast-notification-customer');
	for( var i = 0; i < offers.length; i++) {
		offers[i].firstChild.innerHTML = "<span style='color: red; font-weight: 800;'>**</span>" + "My Offers";
	}
}
function Recast_userHasOrders() {	
	var orders = document.getElementsByClassName('recast-notification-seller'); 
	for( var i = 0; i < orders.length; i++) {
		orders[i].firstChild.innerHTML = "<span style='color: red; font-weight: 800;'>**</span>" + "Orders";
	}
}
function Recast_Offers_refreshOrderAction_Buttons( order_id ) {	
	document.getElementById( 'Recast-ViewOrderActionButtons').style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Offers_ViewOrderActionButtons",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'Recast-ViewOrderActionButtons' ).innerHTML = data;
			document.getElementById( 'Recast-ViewOrderActionButtons' ).style.opacity = "1.0";
		}
	});
}
function Recast_Offers_refreshOffer_Buttons( order_id ) {	
	document.getElementById( 'Recast-ViewOfferButtons').style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Offers_ViewOfferButtons",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'Recast-ViewOfferButtons' ).innerHTML = data;
			document.getElementById( 'Recast-ViewOfferButtons' ).style.opacity = "1.0";
		}
	});
}
function Recast_Offers_refreshOrder_Sidebar( order_id ) {	
	document.getElementById( 'Recast-ViewOfferSidebar').style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: Recast.url,
		data: {
			"action": "Recast_Offers_ViewOfferSidebar",
			"AJAX": true,
			"user_id": Recast.user_id * 1,
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'Recast-ViewOfferSidebar' ).innerHTML = data;
			document.getElementById( 'Recast-ViewOfferSidebar' ).style.opacity = "1.0";
		}
	});
}
/* Refresh Listing Actions */
/* - Security: n/a */
function Recast_Action_refreshListing( listing_id, data ) {
	if( data ) {
		// console.log( data );
		window.location.href = "/selling/edit-listing/?listing_id="+listing_id;
	} else {
		console.log("AJAX failed. Wrong 'user_id'?");
	}
}
function Recast_Action_dimListing() {
// 	document.getElementById('Recast-EditListing_Details' ).style.opacity = "0.5";
// 	document.getElementById('Recast-EditListing_Attributes' ).style.opacity = "0.5";
	document.getElementById('Recast-EditListing' ).style.opacity = "0.5";
}