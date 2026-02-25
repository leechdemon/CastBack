<?php

if (!function_exists('Test')) {
    // PHP script to dump variable into JavaScript console on front-end.
    function Test($output, $with_script_tags = false) {
        $js_code = json_encode($output, JSON_HEX_TAG);
        if ($with_script_tags === false) {
            echo '<script>console.log(' .json_encode($js_code). ');</script>';
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

function CastBack_sendEmailNotification( $order_id, $emailTemplate, $recipient_id = null ) {
	if( $recipient_id ) {
		// if( is_int( (int)$recipient_id ) ) {
			$user = get_user_by( 'ID', $recipient_id );
			if( $user ) { $user_email = $user->user_email; }
		// }
		// else $recipient_id ) ) { 
			/* $recpient_id IS a string... */
		// }
		// else {
			// $user_email = $recipient_id;
			// echo $T95;
			// error_log( print_r( 'CastBack T95 - recipient_id is not a string or an int.', true ) );
		// }
	}
		
	if( $user_email ) {
		$email_new_message = WC()->mailer()->get_emails()[ $emailTemplate ];
		$response = $email_new_message->trigger( $user_email, $order_id );

		return $response;
	}
	else {
		// echo "T104";
		// echo $T104; /* <-- ..wtf?! */
		// error_log( print_r( 'CastBack T104 - No user_email found.', true ) );
	}
}

/* User functions */
function CastBack_getAddress( $user_id = null, $method = null, $output = false ) {
	if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	if( !$user_id ) { $user_id = get_current_user_id(); }

	/* Should this run customerSeller, specifically? Only for AJAX, which the page should protect... */

	$user = get_user_by( 'ID', $user_id );
	if( $user && $method ) {
		$first_name = get_user_meta( $user_id, $method.'_first_name', true );
		$last_name = get_user_meta( $user_id, $method.'_last_name', true );
		$address_1 = get_user_meta( $user_id, $method.'_address_1', true ); 
		$address_2 = get_user_meta( $user_id, $method.'_address_2', true );
		$city = get_user_meta( $user_id, $method.'_city', true );
		$state = get_user_meta( $user_id, $method.'_state', true );
		$postcode = get_user_meta( $user_id, $method.'_postcode', true );
		// $country = get_user_meta( $user_id, $method.'_country', true );
		// $email = get_user_meta( $user_id, $method.'_email', true );
		// $phone = get_user_meta( $user_id, $method.'_phone', true );
		
		$theAddress = new stdClass();
		$theAddress->first_name = $first_name;
		$theAddress->last_name = $last_name;
		$theAddress->address_1 = $address_1;
		$theAddress->address_2 = $address_2;
		$theAddress->city = $city;
		$theAddress->state = $state;
		$theAddress->postcode = $postcode;
		// $theAddress->country = $country;
		// $theAddress->email = $email;
		// $theAddress->phone = $phone;
		//Test( $theAddress );
	}
	if( $first_name && $last_name && $address_1 && $city && $state && $postcode ) { $hasAddress = true; }
	
	if( $output === true ) {
		echo '<div style="padding: 1rem;">';
			if( $first_name && $last_name ) {
				echo $first_name .' '. $last_name .'<br>'; 
			} else {
				if( $first_name ) { echo $first_name; }
				if( $last_name ) { echo $last_name; }
			}
			if( $address_1 ) { echo $address_1 .'<br>'; }
			if( $address_2 ) { echo $address_2 .'<br>'; }
			
			if( $city && ( $state || $postcode ) ) { echo $city .', '; }
			else if ( $city ) { echo $city .'<br>'; }

			if( $state ) { echo $state .' '; }
			if( $postcode ) { echo $postcode; }
		echo '</div>';		
	}
	else if( $output == 'return' ) { return $theAddress; }
	else { return $hasAddress; }
}
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
function CastBack_customerSeller( $post_id, $user_id = null, $method = 'any' ) {
	if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	if( !$user_id ) { $user_id = get_current_user_id(); }
	
	if( $user_id) {
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
	}

	if( $method == 'customer' || $method == 'customer_id' ) { return ( $customerSeller[ 'ids' ]['customer_id'] == $user_id ); }
	else if( $method == 'seller' || $method == 'seller_id' ) { return ( $customerSeller[ 'ids' ]['seller_id'] == $user_id ); }
	else if( $method == 'any' ) { return $customerSeller[ 'any' ]; }
	else { return $customerSeller[ $method ]; }
}
function CastBack_userCanPurchase( $user_id = null ) {
	if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	if( !$user_id ) { $user_id = get_current_user_id(); }

	$userCanPurchase = true;
	if( $userCanPurchase && $user_id == 0 ) {
		$userCanPurchase = false;
		$reason = 'user_id';
	}
	if( $userCanPurchase && !CastBack_getAddress( $user_id, 'shipping' ) ) {
		$userCanPurchase = false;
		$reason = 'shipping';
	}

	if( $userCanPurchase ) {
		// if($AJAX) {
			// echo $userCanPurchase;
			// wp_die();
		// } else {
			return $userCanPurchase;
		// }
	}
	else {
		// if($AJAX) {
			// echo CastBack_userRegistrationPrompt( $reason );
			// wp_die();
		// } else {
			// return CastBack_userRegistrationPrompt( $reason );
			// return $userCanPurchase;
			return $reason;
		// }
	}
} add_action( 'wp_ajax_CastBack_userCanPurchase', 'CastBack_userCanPurchase' );
function CastBack_userIsStripeConnected( $user_id = null ) {
	if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }
	if( !$user_id ) { $user_id = get_current_user_id(); }
	
	if( $user_id ) {
		$dokan_settings = get_user_meta( $user_id, 'dokan_profile_settings', true );
		// Test( $dokan_settings );
		// Test( $dokan_settings['profile_completion'] );
		// Test( $dokan_settings['profile_completion']['progress'] );
		
		// return true;
		if( isset( $dokan_settings['profile_completion']['progress'] ) && $dokan_settings['profile_completion']['progress'] > 30 ) {
			return true;
		} else {
			return false;
		}
	} else { return false; }
} add_action( 'wp_ajax_CastBack_userIsStripeConnected', 'CastBack_userIsStripeConnected' );

function CastBack_userHasCurrentOffer( $listing_id, $user_id = null ) {
	if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }		
	if( !$user_id && is_user_logged_in() ) { $user_id = get_current_user_id(); }
	$userHasCurrentOffer = null;
	
	/* Get Current Offers */
	$orders = get_field( 'current_orders', $user_id ); 
	
	/* Decide if Offers are a match */
	if( $orders ) {
		foreach( $orders as $order ) { 
			if( $order['listing_id'] == $listing_id ) { $userHasCurrentOffer = $order['order_id']; }
		}
	}
	
	return( $userHasCurrentOffer );
}
function CastBack_userHasNotification( $order_id, $user_id = null, $method = null ) {
	if( !$user_id && isset( $_POST['user_id'] ) ) { $user_id = $_POST['user_id']; }		
	if( !$user_id && is_user_logged_in() ) { $user_id = get_current_user_id(); }
		
	$orders = get_field( 'current_orders', $user_id ); 
	foreach( $orders as $order ) { 
		if( $order['order_id'] == $order_id ) {
			if( $order['role'] == $method ) {
				$revision_date = get_field( 'revision_date', $order_id );
				if( $revision_date > $order['last_viewed'] ) { $userHasNotification = true; }
			}
		}
	}
	
	return( $userHasNotification );
}
function CastBack_userRegistrationPrompt( $reason = null ) {
	$output = '<div style="width: 100%; text-align: center; padding: 1rem 0.5rem; border: solid 2px; border-radius: 0.5rem;">';

	if( $reason == "shipping" ) {
		$cosmeticReason = 'Please update your shipping address.';
		$url = '/my-account/edit-address';
		$urlLabel = 'Edit Address';
	} else {
		$cosmeticReason = 'Please create an account to continue.';
		$url = '/my-account/';
		$urlLabel = 'Log In / Register';
	}
	
	$output .= '<h5>'.$cosmeticReason.'</h5><br>';
	$output .= '<a href="'.$url.'" class="castback-button castback-button-important" target="_blank">'.$urlLabel.'</a>';
	$output .= '</div>';

	return $output;
}
function CastBack_vendorRegistrationPrompt( $url = null ) {
	$label = 'Vendor Registration';
	if( $url ) { $label = 'Complete Registration Wizard'; }
	else { $url = '/my-account/account-migration/'; }
	
	$output = "";
	$output .= '<div style="width: 100%; text-align: center; padding: 1rem 0.5rem; border: solid 2px; border-radius: 0.5rem;">';
	$output .= '<h5>Please complete Vendor Registration.</h5>';
	$output .= '<a href="/about/why-register/" class="castback-button" target="_blank">Why Register?</a>';
	$output .= '<a href="'.$url.'" class="castback-button castback-button-important" target="_blank">'.$label.'</a>';
	$output .= '</div>';

	return $output;
}
function CastBack_matchWCShopFields( $store_id, $dokan_settings ) { /* Unused, original version - 2/11/2026, JE */
	/* Set Dokan Phone */
	if( isset( $_POST['billing_phone'] ) ) {
			$dokan_settings['phone'] = sanitize_text_field( $_POST['billing_phone'] );
	} else {
		$field = get_user_meta( $store_id, 'billing_phone', true );
		if( $field ) { $dokan_settings['phone'] = $field; }
	}
	
	/* Build Dokan Address Object */
	$dokan_address = $dokan_settings["address"];
	if( isset( $_POST['billing_address_1'] ) ) {
			$dokan_address['street_1'] = sanitize_text_field( $_POST['billing_address_1'] );
	} else {
		$field = get_user_meta( $store_id, 'billing_address_1', true );
		if( $field ) { $dokan_address['street_1'] = $field; }
	}
	if( isset( $_POST['billing_address_2'] ) ) {
			$dokan_address['street_2'] = sanitize_text_field( $_POST['billing_address_2'] );
	} else {
		$field = get_user_meta( $store_id, 'billing_address_2', true );
		if( $field ) { $dokan_address['street_2'] = $field; }
	}
	if( isset( $_POST['billing_city'] ) ) {
			$dokan_address['city'] = sanitize_text_field( $_POST['billing_city'] );
	} else {
		$field = get_user_meta( $store_id, 'billing_city', true );
		if( $field ) { $dokan_address['city'] = $field; }
	}
	if( isset( $_POST['billing_postcode'] ) ) {
			$dokan_address['postcode'] = sanitize_text_field( $_POST['billing_postcode'] );
	} else {
		$field = get_user_meta( $store_id, 'billing_postcode', true );
		if( $field ) { $dokan_address['postcode'] = $field; }
	}
	if( isset( $_POST['billing_country'] ) ) {
			$dokan_address['country'] = sanitize_text_field( $_POST['billing_country'] );
	} else {
		$field = get_user_meta( $store_id, 'billing_country', true );
		if( $field ) { $dokan_address['country'] = $field; }
	}
	if( isset( $_POST['billing_state'] ) ) {
			$dokan_address['state'] = sanitize_text_field( $_POST['billing_state'] );
	} else {
		$field = get_user_meta( $store_id, 'billing_state', true );
		if( $field ) { $dokan_address['state'] = $field; }
	}

	/* Set Dokan Address */
	$dokan_settings['address'] = $dokan_address;
	update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
	
	/* Set WC Billing Address, Phone */
	if( isset( $_POST['address[street_1]'] ) ) {
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
}
function CastBack_matchDokanShopFields( $store_id, $dokan_settings ) {
	/* Set Dokan Phone */
	// if( isset( $_POST['billing_phone'] ) ) {
			// $dokan_settings['phone'] = sanitize_text_field( $_POST['billing_phone'] );
	// } else {
		// $field = get_user_meta( $store_id, 'billing_phone', true );
		// if( $field ) { $dokan_settings['phone'] = $field; }
	// }
	
	/* Build Dokan Address Object */
	// $dokan_address = $dokan_settings["address"];
	// if( isset( $_POST['billing_address_1'] ) ) {
			// $dokan_address['street_1'] = sanitize_text_field( $_POST['billing_address_1'] );
	// } else {
		// $field = get_user_meta( $store_id, 'billing_address_1', true );
		// if( $field ) { $dokan_address['street_1'] = $field; }
	// }
	// if( isset( $_POST['billing_address_2'] ) ) {
			// $dokan_address['street_2'] = sanitize_text_field( $_POST['billing_address_2'] );
	// } else {
		// $field = get_user_meta( $store_id, 'billing_address_2', true );
		// if( $field ) { $dokan_address['street_2'] = $field; }
	// }
	// if( isset( $_POST['billing_city'] ) ) {
			// $dokan_address['city'] = sanitize_text_field( $_POST['billing_city'] );
	// } else {
		// $field = get_user_meta( $store_id, 'billing_city', true );
		// if( $field ) { $dokan_address['city'] = $field; }
	// }
	// if( isset( $_POST['billing_postcode'] ) ) {
			// $dokan_address['postcode'] = sanitize_text_field( $_POST['billing_postcode'] );
	// } else {
		// $field = get_user_meta( $store_id, 'billing_postcode', true );
		// if( $field ) { $dokan_address['postcode'] = $field; }
	// }
	// if( isset( $_POST['billing_country'] ) ) {
			// $dokan_address['country'] = sanitize_text_field( $_POST['billing_country'] );
	// } else {
		// $field = get_user_meta( $store_id, 'billing_country', true );
		// if( $field ) { $dokan_address['country'] = $field; }
	// }
	// if( isset( $_POST['billing_state'] ) ) {
			// $dokan_address['state'] = sanitize_text_field( $_POST['billing_state'] );
	// } else {
		// $field = get_user_meta( $store_id, 'billing_state', true );
		// if( $field ) { $dokan_address['state'] = $field; }
	// }

	/* Set Dokan Address */
	// $dokan_settings['address'] = $dokan_address;
	// update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
	
	/* Set WC Billing Address, Phone */
	if( isset( $dokan_settings['address']['street_1'] ) ) {
		if( !get_user_meta( $store_id, 'billing_address_1', true ) ) {
			update_user_meta( $store_id, 'billing_address_1', $dokan_settings['address']['street_1'] );
		}
		if( !get_user_meta( $store_id, 'shipping_address_1', true ) ) {
			update_user_meta( $store_id, 'shipping_address_1', $dokan_settings['address']['street_1'] );
		}
	}
	if( isset( $dokan_settings['address']['street_2'] ) ) {
		if( !get_user_meta( $store_id, 'billing_address_2', true ) ) {
			update_user_meta( $store_id, 'billing_address_2', $dokan_settings['address']['street_2'] );
		}
		if( !get_user_meta( $store_id, 'shipping_address_2', true ) ) {
			update_user_meta( $store_id, 'shipping_address_2', $dokan_settings['address']['street_2'] );
		}
	}
	if( isset( $dokan_settings['address']['city'] ) ) {
		if( !get_user_meta( $store_id, 'billing_city', true ) ) {
			update_user_meta( $store_id, 'billing_city', $dokan_settings['address']['city'] );
		}
		if( !get_user_meta( $store_id, 'shipping_city', true ) ) {
			update_user_meta( $store_id, 'shipping_city', $dokan_settings['address']['city'] );
		}
	}
	if( isset( $dokan_settings['address']['zip'] ) ) {
		if( !get_user_meta( $store_id, 'billing_postcode', true ) ) {
			update_user_meta( $store_id, 'billing_postcode', $dokan_settings['address']['zip'] );
		}
		if( !get_user_meta( $store_id, 'shipping_postcode', true ) ) {
			update_user_meta( $store_id, 'shipping_postcode', $dokan_settings['address']['zip'] );
		}
	}
	if( isset( $dokan_settings['address']['country'] ) ) {
		if( !get_user_meta( $store_id, 'billing_country', true ) ) {
			update_user_meta( $store_id, 'billing_country', $dokan_settings['address']['country'] );
		}
		if( !get_user_meta( $store_id, 'shipping_country', true ) ) {
			update_user_meta( $store_id, 'shipping_country', $dokan_settings['address']['country'] );
		}
	}
	if( isset( $dokan_settings['address']['state'] ) ) {
		if( !get_user_meta( $store_id, 'billing_state', true ) ) {
			update_user_meta( $store_id, 'billing_state', $dokan_settings['address']['state'] );
		}
		if( !get_user_meta( $store_id, 'shipping_state', true ) ) {
			update_user_meta( $store_id, 'shipping_state', $dokan_settings['address']['state'] );
		}
	}
	
	$first = get_user_meta( $store_id, 'first_name', true);
	if( $first ) {
		if( !get_user_meta( $store_id, 'billing_first_name', true ) ) {
			update_user_meta( $store_id, 'billing_first_name', $first );
		}
		if( !get_user_meta( $store_id, 'shipping_first_name', true ) ) {
			update_user_meta( $store_id, 'shipping_first_name', $first );
		}
	}

	$last = get_user_meta( $store_id, 'last_name', true);
	if( $last ) {
		if( !get_user_meta( $store_id, 'billing_last_name', true ) ) {
			update_user_meta( $store_id, 'billing_last_name', $last );
		}
		if( !get_user_meta( $store_id, 'shipping_last_name', true ) ) {
			update_user_meta( $store_id, 'shipping_last_name', $last );
		}
	}

	// $dokan_settings = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
	// $phone = $dokan_settings['phone'];
	// if( $phone ) {
		// update_user_meta( $store_id, 'billing_phone', $phone);

		// /* If there's no shipping info, prefil that too. */
		// if( !get_user_meta( $store_id, 'shipping_phone', true ) ) {
			// update_user_meta( $store_id, 'shipping_phone', $phone );
		// }
	// }


}
/* Called by plugin "WP Webhooks Pro" - 2/11/26, JE */
// add_action( 'dokan_store_profile_saved', 'CastBack_matchDokanShopFields', 10, 2 );

function CastBack_updateUserFields( $user_id, $old_user_data, $userdata ) {
	$dokan_settings = dokan_get_store_info( $user_id );
	CastBack_matchDokanShopFields( $user_id, $dokan_settings );
} add_action( 'profile_update', 'CastBack_updateUserFields', 10, 3 );
// add_action( 'woocommerce_created_customer', 'CastBack_updateUserFields', 10, 2 );