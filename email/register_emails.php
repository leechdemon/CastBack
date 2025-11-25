<?php 

class CastBack_Email {
	public function __construct() {
    // Filtering the emails and adding our own email.
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );
    // Absolute path to the plugin folder.
		// Test( plugin_dir_path( __FILE__ ) .'templates/' );
		define( 'CASTBACK_EMAIL_PATH', plugin_dir_path( __FILE__ ) .'templates/' );

	}
	public function register_email( $emails ) {
		require_once plugin_dir_path(__FILE__) . 'class-castback-publish-listing.php';
		$emails['CastBack_publishListing'] = new CastBack_Email_publishListing();
		
		require_once plugin_dir_path(__FILE__) . 'class-castback-submit-offer.php';
		$emails['CastBack_submitOffer'] = new CastBack_Email_submitOffer();
		
		require_once plugin_dir_path(__FILE__) . 'class-castback-accept-offer.php';
		$emails['CastBack_acceptOffer'] = new CastBack_Email_acceptOffer();
		
		require_once plugin_dir_path(__FILE__) . 'class-castback-send-message.php';
		$emails['CastBack_sendMessage'] = new CastBack_Email_sendMessage();
		
		return $emails;
	}
}

new CastBack_Email();