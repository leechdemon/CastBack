<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Class Recast_Email_addTracking_recipient
 */
class Recast_Email_addTracking_recipient extends WC_Email {

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
    // Email slug we can use to filter other data.
		$this->id          = 'Recast_addTracking_recipient';
		$this->title       = __( 'Recast - addTracking() - recipient', 'recast-email' );
		$this->description = __( 'An email sent to the recipient when a Tracking Number has been sent.', 'recast-email' );
    // For admin area to let the user know we are sending this email to customers.
		// $this->customer_email = true;
		$this->recipient = 'recipient@ofthe.trackingNumber';
		
		$this->heading     = __( 'Order #{order_number}: New Tracking Number Received', 'recast-email' );
		$this->subject     = sprintf( _x( 'Order #{order_number}: New Tracking Number Received', 'default email subject for New Tracking Number Received.', 'recast-email' ), 'Recast' );
    
    // Template paths.
		$this->template_html  = 'Recast-addTracking-recipient.php';
		// $this->template_plain = 'emails/plain/Recast-addTracking-recipient.php';
		$this->template_plain = 'Recast-addTracking-recipient.php';
		$this->template_base  = CASTBACK_EMAIL_PATH;
		
		// Test( $order_id );
    
    // Action to which we hook onto to send the email.
		//add_action( 'Recast_Action_addTracking_emailTrigger', array( $this, 'trigger' ) );

		parent::__construct();
	}

	function trigger( $user_email, $order_id ) {
		// $this->object = wc_get_order( $order_id );
		$this->order_id = $order_id;
		$this->heading     = __( 'Order #'. $order_id. ': New Tracking Number Received', 'recast-email' );
		$this->subject     = sprintf( _x( 'Order #'. $order_id. ': New Tracking Number Received', 'default email subject for New Tracking Number Received.', 'recast-email' ), 'Recast' );
		
		if ( ! $this->is_enabled() || ! $user_email ) {
			return;
		}

		$this->send( $user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			// 'order'         => wc_get_order( $order_id ),
			'order_id'      => $this->order_id,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'			=> $this
		), '', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			// 'order'         => wc_get_order( $order_id ),
			'order_id'      => $this->order_id,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
	}
}