/* Listings */
function CastBack_Action_addListing_button() {
	// Listing version?
	// document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_addListing",
			"AJAX": true,
			// "user_id": CastBack.user_id,
		},
		success: function (data) {
			// console.log( data );
			window.location.href = "/selling/listings/?listing_id=" + data;
		}
	});
}
// function CastBack_Action_markSold_confirm( listing_id ) {
	// if( window.confirm('Delete Listing #' + listing_id + "? (This cannot be undone!)" ) ) {
		// CastBack_Action_markSold( listing_id );
		// window.location.href = "/selling/listings/delete-listing?listing_id="+ listing_id;
	// }
// }
function CastBack_Action_markSold( listing_id ) {
	// document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
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
			console.log( data );
			window.location.href = "/selling/edit-listing/?listing_id="+listing_id;
		}
	});
}
function CastBack_Action_markUnsold( listing_id ) {
	// document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
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
			console.log( data );
			window.location.href = "/selling/edit-listing/?listing_id="+listing_id;
		}
	});
}
function CastBack_Action_publishListing( listing_id ) {
	// document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
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
			console.log( data );
			window.location.href = "/selling/edit-listing/?listing_id="+listing_id;
		}
	});
}
function CastBack_Action_hideListing( listing_id ) {
	// document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
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
			console.log( data );
			window.location.href = "/selling/edit-listing/?listing_id="+listing_id;
		}
	});
}

/* Offers */
function CastBack_Action_makeOffer_button( listing_id ) {
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Action_makeOffer",
			"AJAX": true,
			// "user_id": CastBack.user_id,
			"listing_id": listing_id, 
		},
		success: function (data) {
			// console.log( data );
			window.location.href = "/buying/offers/?order_id=" + data;
		}
	});
}
function CastBack_Action_sendMessage_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
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
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_Action_submitOffer_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
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
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_Action_acceptOffer_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
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
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_Action_addTracking_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
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
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_Action_completeOrder_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
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
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_Action_disputeOrder_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
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
			CastBack_Offers_drawOrderDetails_button( order_id );
		}
	});
}
function CastBack_Action_removeDispute_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
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
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}

/* Refresh Actions */
function CastBack_Offers_drawOrderDetails_button( order_id ) {	
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_Offers_drawOrderDetails",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_edit_listing_button( listing_id, user_id, targetDiv ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";

	jQuery.ajax({
		type: "POST",
		url: CastBack.url,
		data: {
			"action": "CastBack_edit_listing",
			"AJAX": true,
			"user_id": CastBack.user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			document.getElementById( targetDiv ).innerHTML = data;
			document.getElementById( targetDiv ).style.opacity = "1.0";
		}
	});
}