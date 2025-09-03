function CastBack_action_make_offer_button( listing_id ) {
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_make_offer",
			"listing_id": listing_id, 
		},
		success: function (data) {
			console.log( data );
			window.location.href = "/buying/offers/?order_id=" + data;
		}
	});
}
function CastBack_action_submit_offer_button() {
	var order_id = document.getElementById("castback_order_id").innerHTML;
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
			window.location.reload();
		}
	});
}
function CastBack_action_accept_offer_button() {
	var order_id = document.getElementById("castback_order_id").innerHTML;
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_accept_offer",
			"order_id": order_id, 
		},
		success: function (data) {
			window.location.reload();
		}
	});
}
function CastBack_action_add_tracking_button() {
	var order_id = document.getElementById("castback_order_id").innerHTML;
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
			window.location.reload();
		}
	});
}
function CastBack_action_complete_order_button() {
	var order_id = document.getElementById("castback_order_id").innerHTML;
	
	jQuery.ajax({
		type: "POST",
		url: castback_object.url,
		data: {
			"action": "CastBack_action_complete_order",
			"order_id": order_id,
		},
		success: function (data) {
			window.location.reload();
		}
	});
}