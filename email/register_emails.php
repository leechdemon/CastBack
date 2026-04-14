<?php 

// function Recast_Emails_order_meta_fields( $fields, $sent_to_admin, $order ) {
	// $fields['Recast_emailIntro'] = array(
		// 'label' => __( 'Email Introduction' ),
		// 'value' => get_post_meta( $order->id, 'Recast_emailIntro', true ),
		// 'value' => '',
	// );
	// $fields['Recast_emailNextSteps'] = array(
		// 'label' => __( 'Next Steps' ),
		// 'value' => get_post_meta( $order->id, 'Recast_emailNextSteps', true ),
		// 'value' => '',
	// );
	// return $fields;
// } add_filter( 'woocommerce_email_order_meta_fields', 'Recast_Emails_order_meta_fields', 10, 3 );

function Recast_getEmailTemplateFields( $filename ) {
	foreach( get_field( 'email_template', 'option' ) as $template ) {
		if( $template['email_id'] == $filename ) {
			return $template;
		}
	}
}


class Recast_Email {
	public function __construct() {
    // Filtering the emails and adding our own email.
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
    // Absolute path to the plugin folder.
		// Test( plugin_dir_path( __FILE__ ) .'templates/' );
		define( 'CASTBACK_EMAIL_PATH', plugin_dir_path( __FILE__ ) .'templates/' );

	}
	public function register_email( $emails ) {
		/* Listing Actions */
		require_once plugin_dir_path(__FILE__) . 'class-Recast-publishListing.php';
		$emails['Recast_publishListing'] = new Recast_Email_publishListing();

		/* Order Actions */
		require_once plugin_dir_path(__FILE__) . 'class-Recast-sendMessage-recipient.php';
		$emails['Recast_sendMessage_recipient'] = new Recast_Email_sendMessage_recipient();
		require_once plugin_dir_path(__FILE__) . 'class-Recast-sendMessage-sender.php';
		$emails['Recast_sendMessage_sender'] = new Recast_Email_sendMessage_sender();

		require_once plugin_dir_path(__FILE__) . 'class-Recast-submitOffer-recipient.php';
		$emails['Recast_submitOffer_recipient'] = new Recast_Email_submitOffer_recipient();
		require_once plugin_dir_path(__FILE__) . 'class-Recast-submitOffer-sender.php';
		$emails['Recast_submitOffer_sender'] = new Recast_Email_submitOffer_sender();
		
		require_once plugin_dir_path(__FILE__) . 'class-Recast-acceptOffer-seller.php';
		$emails['Recast_acceptOffer_seller'] = new Recast_Email_acceptOffer_seller();
		require_once plugin_dir_path(__FILE__) . 'class-Recast-acceptOffer-buyer.php';
		$emails['Recast_acceptOffer_buyer'] = new Recast_Email_acceptOffer_buyer();
		
		require_once plugin_dir_path(__FILE__) . 'class-Recast-addTracking-recipient.php';
		$emails['Recast_addTracking_recipient'] = new Recast_Email_addTracking_recipient();
		
		/* automations */
		require_once plugin_dir_path(__FILE__) . 'class-Recast-autocompleteShippedOrder-buyer.php';
		$emails['Recast_autocompleteShippedOrder_buyer'] = new Recast_Email_autocompleteShippedOrder_buyer();
		
		require_once plugin_dir_path(__FILE__) . 'class-Recast-autocancelUnpaidOrder-buyer.php';
		$emails['Recast_autocancelUnpaidOrder_buyer'] = new Recast_Email_autocancelUnpaidOrder_buyer();
		require_once plugin_dir_path(__FILE__) . 'class-Recast-autocancelUnpaidOrder-seller.php';
		$emails['Recast_autocancelUnpaidOrder_seller'] = new Recast_Email_autocancelUnpaidOrder_seller();
		
		require_once plugin_dir_path(__FILE__) . 'class-Recast-autorefundUnshippedOrder-buyer.php';
		$emails['Recast_autorefundUnshippedOrder_buyer'] = new Recast_Email_autorefundUnshippedOrder_buyer();
		require_once plugin_dir_path(__FILE__) . 'class-Recast-autorefundUnshippedOrder-seller.php';
		$emails['Recast_autorefundUnshippedOrder_seller'] = new Recast_Email_autorefundUnshippedOrder_seller();
		
		return $emails;
	}
}

new Recast_Email();

