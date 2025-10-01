/* Listings */
function CastBack_action_add_listing_button() {
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_add_listing",
		},
		success: function (data) {
			// console.log( data );
			window.location.href = "/selling/listings/?listing_id=" + data;
		}
	});
}

/* Offers */
function CastBack_action_make_offer_button( listing_id ) {
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_make_offer",
			"listing_id": listing_id, 
		},
		success: function (data) {
			// console.log( data );
			window.location.href = "/buying/offers/?order_id=" + data;
		}
	});
}
function CastBack_action_send_message_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	var new_message = document.getElementById("castback_new_message").value;

	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_send_message",
			"order_id": order_id, 
			"new_message": new_message,
		},
		success: function (data) {
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_action_submit_offer_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	var order_amount = document.getElementById("castback_offer_amount").value;
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_submit_offer",
			"order_id": order_id, 
			"order_amount": order_amount,
		},
		success: function (data) {
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_action_accept_offer_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_accept_offer",
			"order_id": order_id, 
		},
		success: function (data) {
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_action_add_tracking_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	var new_tracking_number = document.getElementById("castback_new_tracking_number").value;
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_add_tracking",
			"order_id": order_id, 
			"new_tracking_number": new_tracking_number, 
		},
		success: function (data) {
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_action_complete_order_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_complete_order",
			"order_id": order_id,
		},
		success: function (data) {
			document.getElementById( 'CastBack-Order-' + order_id ).innerHTML = data;
			document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "1.0";
		}
	});
}
function CastBack_action_dispute_order_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_dispute_order",
			"order_id": order_id,
		},
		success: function (data) {
			CastBack_Offers_drawOrderDetails_button( order_id );
		}
	});
}
function CastBack_action_remove_dispute_button( order_id ) {
	document.getElementById( 'CastBack-Order-' + order_id ).style.opacity = "0.5";
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_remove_dispute",
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
		url: castback_object.url,
		data: {
			"action": "CastBack_Offers_drawOrderDetails",
			"AJAX": true,
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
		url: castback_object.url,
		data: {
			"action": "CastBack_edit_listing",
			"user_id": user_id,
			"listing_id": listing_id,
		},
		success: function (data) {
			document.getElementById( targetDiv ).innerHTML = data;
			document.getElementById( targetDiv ).style.opacity = "1.0";
		}
	});
}