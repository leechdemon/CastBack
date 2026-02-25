<?php 

// function CastBack_Emails_order_meta_fields( $fields, $sent_to_admin, $order ) {
	// $fields['CastBack_emailIntro'] = array(
		// 'label' => __( 'Email Introduction' ),
		// 'value' => get_post_meta( $order->id, 'CastBack_emailIntro', true ),
		// 'value' => '',
	// );
	// $fields['CastBack_emailNextSteps'] = array(
		// 'label' => __( 'Next Steps' ),
		// 'value' => get_post_meta( $order->id, 'CastBack_emailNextSteps', true ),
		// 'value' => '',
	// );
	// return $fields;
// } add_filter( 'woocommerce_email_order_meta_fields', 'CastBack_Emails_order_meta_fields', 10, 3 );

function CastBack_getEmailTemplateFields( $filename ) {
	foreach( get_field( 'email_template', 'option' ) as $template ) {
		if( $template['email_id'] == $filename ) {
			return $template;
		}
	}
}


class CastBack_Email {
	public function __construct() {
    // Filtering the emails and adding our own email.
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
    // Absolute path to the plugin folder.
		// Test( plugin_dir_path( __FILE__ ) .'templates/' );
		define( 'CASTBACK_EMAIL_PATH', plugin_dir_path( __FILE__ ) .'templates/' );

	}
	public function register_email( $emails ) {
		/* Listing Actions */
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-publishListing.php';
		$emails['CastBack_publishListing'] = new CastBack_Email_publishListing();

		/* Order Actions */
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-sendMessage-recipient.php';
		$emails['CastBack_sendMessage_recipient'] = new CastBack_Email_sendMessage_recipient();
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-sendMessage-sender.php';
		$emails['CastBack_sendMessage_sender'] = new CastBack_Email_sendMessage_sender();

		require_once plugin_dir_path(__FILE__) . 'class-CastBack-submitOffer-recipient.php';
		$emails['CastBack_submitOffer_recipient'] = new CastBack_Email_submitOffer_recipient();
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-submitOffer-sender.php';
		$emails['CastBack_submitOffer_sender'] = new CastBack_Email_submitOffer_sender();
		
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-acceptOffer-seller.php';
		$emails['CastBack_acceptOffer_seller'] = new CastBack_Email_acceptOffer_seller();
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-acceptOffer-buyer.php';
		$emails['CastBack_acceptOffer_buyer'] = new CastBack_Email_acceptOffer_buyer();
		
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-addTracking-recipient.php';
		$emails['CastBack_addTracking_recipient'] = new CastBack_Email_addTracking_recipient();
		
		/* automations */
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-autocompleteShippedOrder-buyer.php';
		$emails['CastBack_autocompleteShippedOrder_buyer'] = new CastBack_Email_autocompleteShippedOrder_buyer();
		
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-autocancelUnpaidOrder-buyer.php';
		$emails['CastBack_autocancelUnpaidOrder_buyer'] = new CastBack_Email_autocancelUnpaidOrder_buyer();
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-autocancelUnpaidOrder-seller.php';
		$emails['CastBack_autocancelUnpaidOrder_seller'] = new CastBack_Email_autocancelUnpaidOrder_seller();
		
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-autorefundUnshippedOrder-buyer.php';
		$emails['CastBack_autorefundUnshippedOrder_buyer'] = new CastBack_Email_autorefundUnshippedOrder_buyer();
		require_once plugin_dir_path(__FILE__) . 'class-CastBack-autorefundUnshippedOrder-seller.php';
		$emails['CastBack_autorefundUnshippedOrder_seller'] = new CastBack_Email_autorefundUnshippedOrder_seller();
		
		return $emails;
	}
}

new CastBack_Email();

