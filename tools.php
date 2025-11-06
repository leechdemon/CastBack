<?php

if (!function_exists('Test')) {
    // PHP script to dump variable into JavaScript console on front-end.
    function Test($output, $with_script_tags = false) {
        $js_code = json_encode($output, JSON_HEX_TAG);
        if ($with_script_tags === false) {
            echo '<script>console.log("Test: " + ' .json_encode($js_code). ');</script>';
        } else {
            echo "<pre>" .var_dump($js_code). "</pre>";
        }
    }
}
function CastBack_editListing($wp_admin_bar) {
    if (!is_admin()) {
        $listing_id = '';
        if (isset($_POST['listing_id'])) {
            $listing_id = $_POST['listing_id'];
        }
        if (!$listing_id && isset($_GET['listing_id'])) {
            $listing_id = $_GET['listing_id'];
        }

        if ($listing_id) {
            $url = get_site_URL() . '/wp-admin/post.php?post='.$listing_id.'&action=edit';
            $args = array(
                'id' => 'edit-listing', // Unique ID for your link
                'title' => 'Edit Listing', // Text displayed in the admin bar
                'href' => $url,
                'meta' => array(
                    'class' => 'my-custom-link-class', // Optional: Add a custom CSS class
                    'title' => 'Links directly to "Edit Product" in Admin', // Optional: Add a tooltip
                ),
            );
            $wp_admin_bar->add_node($args);
        }
    }
} add_action('admin_bar_menu', 'CastBack_editListing', 90);
function CastBack_editOrder($wp_admin_bar) {
    if (!is_admin()) {
        $order_id = '';
        if (isset($_POST['order_id'])) {
            $order_id = $_POST['order_id'];
        }
        if (!$order_id && isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
        }

        if ($order_id) {
            $url = get_site_URL() . '/wp-admin/admin.php?page=wc-orders&action=edit&id='.$order_id.'&action=edit';
            $args = array(
                'id' => 'edit-order', // Unique ID for your link
                'title' => 'Edit Order', // Text displayed in the admin bar
                'href' => $url,
                'meta' => array(
                    'class' => 'my-custom-link-class', // Optional: Add a custom CSS class
                    'title' => 'Links directly to "Edit Order" in Admin', // Optional: Add a tooltip
                ),
            );
            $wp_admin_bar->add_node($args);
        }
    }
} add_action('admin_bar_menu', 'CastBack_editOrder', 90);

function castback_login_redirect($redirect = null, $user = null) {
    // Get the first of all the roles assigned to the user
    // $role = $user->roles[0];
    // $myaccount = '/my-account/';

    // if( $role == '123123123' ) { $redirect = $myaccount; }
    // if( $role == 'administrator' ) { $redirect = $myaccount; }
    // elseif ( $role == 'customer' || $role == 'vendor' || $role == 'dc_vendor' ) { $redirect = $myaccount; }
    // $redirect = '';
    // echo $redirect;
    // $redirect = '123';
    // return $redirect;
    
    

    
    if( $redirect ) { return '/my-account/'; }
    else { wp_safe_redirect( '/my-account/' ); }
} add_action('woocommerce_login_redirect', 'castback_login_redirect', 10, 2);

function CastBack_Tools_hideDokanFields() {	
	/* Populate required fields */
	if( get_current_user_id() ) {
		$current_user = wp_get_current_user();
		/* We're migrating, hide the fields automatically. */
		echo '<script>
		var companyName = document.getElementById("company-name");
		if( companyName ) {
			document.getElementById("company-name").value = "'.$current_user->user_login.'";
			document.getElementById("seller-url").value = "'.$current_user->user_login.'";
		}
		</script>';
	} else {
		echo '<script>
		var companyName = document.getElementById("company-name");
		if( companyName ) {
			var emailField = document.getElementById("reg_email");
			if( emailField ) {
				emailField.addEventListener("change", function( event ) {
					var email = event.target.value;
					
					companyName = email.split(/@./)[0];
					var sellerURL = companyName;
					document.getElementById("company-name").value = companyName;
					document.getElementById("seller-url").value = sellerURL;
				});
			}
		}
		</script>';
	}
	/* All set? Then hide the fields! */
	echo '<style>.form-row.form-group:has( #company-name ) { display: none; }</style>';
	echo '<style>.form-row.form-group:has( #seller-url ) { display: none; }</style>';
} add_action('wp_footer', 'CastBack_Tools_hideDokanFields');

function CastBack_customerSeller( $post_id ) {
	if( isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	else { $user_id = get_current_user_id(); }
	
	$ids = array();
	$ids['user_id'] = $user_id;
	$ids['customer_id'] = get_field( 'customer_id', $post_id );
	$ids['seller_id'] = get_field( 'seller_id', $post_id );
	
	$customerSeller = array();
	$customerSeller['ids'] = $ids;
	$customerSeller['any'] = false;
	if( $ids['user_id'] == $ids['customer_id'] ) { $customerSeller['any'] = true; }
	if( $ids['user_id'] == $ids['seller_id'] ) { $customerSeller['any'] = true; }
	
	// if( !$customerSeller['any'] ) { Test( $customerSeller ); }
	// echo json_encode( $customerSeller );
	// return $customerSeller;
	// Test( $customerSeller['any'] );
	return $customerSeller['any'];
}
function CastBack_userIsStripeConnected( $user_id = null ) {
	if( !$user_id && is_user_logged_in() ) { $user_id == get_current_user_id(); }
	
	$dokan_settings = get_user_meta( $user_id, 'dokan_profile_settings', true );
	return $dokan_settings['profile_completion']['dokan_stripe_express'];
}
function CastBack_vendorRegistrationPrompt() {
	$output = "";
	$output .= '<div style="width: 100%; text-align: center; padding: 1rem 0.5rem;">';
	$output .= '<h5>Please complete <a href="/my-account/vendor/settings/payment-manage-dokan_stripe_express/" class="castback-button castback-button-important" target="_blank">Vendor Registration</a> to continue.</h5>';
	$output .= '</div>';

	return $output;
}
function CastBack_matchDokanShopFields( $store_id, $dokan_settings ) {
	/* Set Dokan Phone */
	if( isset( $_POST['billing_phone'] ) ) {
		// if( $_POST['billing_phone'] ) {
			$dokan_settings['phone'] = sanitize_text_field( $_POST['billing_phone'] );
		// }
	} else {
		$field = get_user_meta( $store_id, 'billing_phone', true );
		if( $field ) { $dokan_settings['phone'] = $field; }
	}
	
	/* Build Dokan Address Object */
	$dokan_address = $dokan_settings["address"];
	if( isset( $_POST['billing_address_1'] ) ) {
		// if( $_POST['billing_address_1'] ) {
			$dokan_address['street_1'] = sanitize_text_field( $_POST['billing_address_1'] );
		// }
	} else {
		$field = get_user_meta( $store_id, 'billing_address_1', true );
		if( $field ) { $dokan_address['street_1'] = $field; }
	}
	if( isset( $_POST['billing_address_2'] ) ) {
		// if( $_POST['billing_address_2'] ) {
			$dokan_address['street_2'] = sanitize_text_field( $_POST['billing_address_2'] );
		// }
	} else {
		$field = get_user_meta( $store_id, 'billing_address_2', true );
		if( $field ) { $dokan_address['street_2'] = $field; }
	}
	if( isset( $_POST['billing_city'] ) ) {
		// if( $_POST['billing_city'] ) {
			$dokan_address['city'] = sanitize_text_field( $_POST['billing_city'] );
		// }
	} else {
		$field = get_user_meta( $store_id, 'billing_city', true );
		if( $field ) { $dokan_address['city'] = $field; }
	}
	if( isset( $_POST['billing_postcode'] ) ) {
		// if( $_POST['billing_postcode'] ) {
			$dokan_address['postcode'] = sanitize_text_field( $_POST['billing_postcode'] );
		// }
	} else {
		$field = get_user_meta( $store_id, 'billing_postcode', true );
		if( $field ) { $dokan_address['postcode'] = $field; }
	}
	if( isset( $_POST['billing_country'] ) ) {
		// if( $_POST['billing_country'] ) {
			$dokan_address['country'] = sanitize_text_field( $_POST['billing_country'] );
		// }
	} else {
		$field = get_user_meta( $store_id, 'billing_country', true );
		if( $field ) { $dokan_address['country'] = $field; }
	}
	if( isset( $_POST['billing_state'] ) ) {
		// if( $_POST['billing_state'] ) {
			$dokan_address['state'] = sanitize_text_field( $_POST['billing_state'] );
		// }
	} else {
		$field = get_user_meta( $store_id, 'billing_state', true );
		if( $field ) { $dokan_address['state'] = $field; }
	}

	/* Set Dokan Address */
	$dokan_settings['address'] = $dokan_address;
	update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
	
	/* Set WC Billing Address, Phone */
	if( !isset( $_POST['billing_address_1'] ) && isset( $_POST['address[street_1]'] ) ) {
		update_user_meta( $store_id, 'billing_address_1', $_POST['address[street_1]'] );
	}
	if( !isset( $_POST['billing_address_2'] ) && isset( $_POST['address[street_2]'] ) ) {
		update_user_meta( $store_id, 'billing_address_2', $_POST['address[street_2]'] );
	}
	if( !isset( $_POST['billing_city'] ) && isset( $_POST['address[city]'] ) ) {
		update_user_meta( $store_id, 'billing_city', $_POST['address[city]'] );
	}
	if( !isset( $_POST['billing_postcode'] ) && isset( $_POST['address[postcode]'] ) ) {
		update_user_meta( $store_id, 'billing_postcode', $_POST['address[postcode]'] );
	}
	if( !isset( $_POST['billing_country'] ) && isset( $_POST['address[country]'] ) ) {
		update_user_meta( $store_id, 'billing_country', $_POST['address[country]'] );
	}
	if( !isset( $_POST['billing_state'] ) && isset( $_POST['address[state]'] ) ) {
		update_user_meta( $store_id, 'billing_state', $_POST['address[state]'] );
	}
	if( !isset( $_POST['billing_phone'] ) && isset( $_POST['phone'] ) ) {
		update_user_meta( $store_id, 'billing_phone', $_POST['phone'] );
	}
	
} add_action( 'dokan_store_profile_saved', 'CastBack_matchDokanShopFields', 10, 2 );
function CastBack_updateUserFields( $user_id, $old_user_data, $userdata ) {
	$dokan_settings = dokan_get_store_info( $user_id );
	CastBack_matchDokanShopFields( $user_id, $dokan_settings );
} add_action( 'profile_update', 'CastBack_updateUserFields', 10, 3 );
// add_action( 'woocommerce_created_customer', 'CastBack_updateUserFields', 10, 2 );

function castback_cron_noOffers($AJAX = true) {
    if (get_field('run_automations', 'option')) {
        $args = array(
            'status' => 'wc-checkout-draft', // Get completed orders
            'limit' => -1, // Retrieve up to 10 orders
            'orderby' => 'date', // Order by date
            'order' => 'DESC',
            // 'customer_id'  => get_current_user_id(),
            // 'meta_query' => array(
            // array(
            // 'key'     => 'offers_0_offer_amount',
            // 'value'   => 'example_value',
            // 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
            // ),
            // ),
        );

        $orders = wc_get_orders($args);
        foreach ($orders as $order) {

            $order_id = $order->get_id();

            $offers = get_field('offers', $order_id);
            if ($offers) {
                if (end($offers)['offer_expired_date']) {
                    $order_date = end($offers)['offer_expired_date'];
                }
            } else {
                $order_date = $order->get_date_created()->format('F j, Y g:i a');
            }

            if ($order_date) {
                // $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
                // $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );

                $cron_no_dispute_completed_date = 5;
                $offer_expired_date = date('F j, Y g:i a',
                    strtotime('+'.$cron_no_dispute_completed_date.' minutes',
                        strtotime($order_date)
                    )
                );

                $offer_expired = strtotime('+5 minutes',
                    strtotime($order_date)
                );
                $currentTime = strtotime(date('F j, Y g:i a'));

                if ($currentTime > $offer_expired) {
                    CastBack_Action_completeOrder($order_id, $AJAX);
                }
            }

        }

        // if($AJAX) { wp_die(); }
    }
}
// add_action( 'castback_cron', 'castback_cron_noOffers' );
function castback_cron_noExpiredDate($AJAX = false) {
    if (get_field('run_automations', 'option')) {
        $args = array(
            'status' => 'wc-checkout-draft', // Get completed orders
            'limit' => -1, // Retrieve up to 10 orders
            'orderby' => 'date', // Order by date
            'order' => 'DESC',
            // 'customer_id'  => get_current_user_id(),
            // 'meta_query' => array(
            // array(
            // 'key'     => 'offers_0_offer_amount',
            // 'value'   => 'example_value',
            // 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
            // ),
            // ),
        );

        $orders = wc_get_orders($args);
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            $offers = get_field('offers', $order_id);
            foreach ($offers as $key => $offer) {
                if (!$offer['offer_expired_date']) {
                    // $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
                    // $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );
                    $cron_no_offer_expired_date = 5;
                    $offer_expired_date = date('F j, Y g:i a', strtotime('+'.$cron_no_offer_expired_date.' minutes', strtotime($offer['offer_date'])));


                    $offer_expired = strtotime('+5 minutes', strtotime($offer['offer_date']));
                    $currentTime = strtotime(date('F j, Y g:i a'));
                    if ($currentTime > $offer_expired) {
                        CastBack_Action_expireOffer($order_id, $key);
                    }
                }
            }
        }

        // if($AJAX) { wp_die(); }
    }
}
// add_action( 'castback_cron', 'castback_cron_noExpiredDate' );
function castback_cron_noShippedDate($AJAX = false) {
    if (get_field('run_automations', 'option')) {
        $args = array(
            'status' => 'processing', // Get completed orders
            'limit' => -1, // Retrieve up to 10 orders
            'orderby' => 'date', // Order by date
            'order' => 'DESC',
            // 'customer_id'  => get_current_user_id(),
            // 'meta_query' => array(
            // array(
            // 'key'     => 'offers_0_offer_amount',
            // 'value'   => 'example_value',
            // 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
            // ),
            // ),
        );


        $orders = wc_get_orders($args);
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            if (get_field('shipped_date', $order_id) == '') {

                // $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
                // $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );

                $cron_no_shipping_refund_date = 5;
                $offer_expired_date = date('F j, Y g:i a',
                    strtotime('+'.$cron_no_shipping_refund_date.' minutes',
                        strtotime(get_field('payment_date', $order_id))
                    )
                );

                $offer_expired = strtotime('+5 minutes', strtotime(get_field('payment_date', $order_id)));
                $currentTime = strtotime(date('F j, Y g:i a'));

                if ($currentTime > $offer_expired) {
                    update_field('disputed_date', $currentTime, $order_id);
                }
            }
        }

        // if($AJAX) { wp_die(); }
    }
}
// add_action( 'castback_cron', 'castback_cron_noShippedDate' );
function castback_cron_noCompletedDate($AJAX = false) {
    if (get_field('run_automations', 'option')) {
        $args = array(
            'status' => 'processing', // Get completed orders
            'limit' => -1, // Retrieve up to 10 orders
            'orderby' => 'date', // Order by date
            'order' => 'DESC',
            // 'customer_id'  => get_current_user_id(),
            // 'meta_query' => array(
            // array(
            // 'key'     => 'offers_0_offer_amount',
            // 'value'   => 'example_value',
            // 'compare' => 'EXISTS', // Optional: can be 'IN', 'LIKE', 'EXISTS', etc.
            // ),
            // ),
        );

        $orders = wc_get_orders($args);
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            if (get_field('completed_date', $order_id) == '' && get_field('disputed_date', $order_id) == '') {

                // $cron_no_offer_expired_date = get_field( 'cron_no_offer_expired_date', 'option' );
                // $offer_expired_date = date('F j, Y g:i a', strtotime( '+'.$cron_no_offer_expired_date.' days', strtotime( $offer['offer_date'] ) ) );

                $cron_no_dispute_completed_date = 5;
                $offer_expired_date = date('F j, Y g:i a',
                    strtotime('+'.$cron_no_dispute_completed_date.' minutes',
                        strtotime(get_field('shipped_date', $order_id))
                    )
                );

                $offer_expired = strtotime('+5 minutes', strtotime(get_field('shipped_date', $order_id)));
                $currentTime = strtotime(date('F j, Y g:i a'));

                if ($currentTime > $offer_expired) {
                    CastBack_Action_completeOrder($order_id, $AJAX);
                }
            }
        }

        // if($AJAX) { wp_die(); }
    }
}
// add_action( 'castback_cron', 'castback_cron_noCompletedDate' );