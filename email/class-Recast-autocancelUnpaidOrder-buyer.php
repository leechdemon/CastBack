<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Class Recast_Email_autocancelUnpaidOrder_buyer
 */
class Recast_Email_autocancelUnpaidOrder_buyer extends WC_Email {

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
    // Email slug we can use to filter other data.
		$this->id          = 'Recast_autocancelUnpaidOrder_buyer';
		$this->title       = __( 'Recast - autocancelUnpaidOrder() - buyer', 'recast-email' );
		$this->description = __( 'An email sent to the buyer when an offer has been accepted, but not paid.', 'recast-email' );
    // For admin area to let the user know we are sending this email to customers.
		$this->customer_email = true;
		// $this->recipient = 'vendor@ofthe.product';
		
		$this->heading     = __( 'Order #{order_number}: Autocancel Unpaid Order', 'recast-email' );
		$this->subject     = sprintf( _x( 'Order #{order_number}: Autocancel Unpaid Order', 'An email sent to the buyer when an offer has been accepted, but not paid.', 'recast-email' ), 'Recast' );
    
    // Template paths.
		$this->template_html  = 'Recast-autocancelUnpaidOrder-buyer.php';
		// $this->template_plain = 'emails/plain/Recast-autocancelUnpaidOrder-buyer.php';
		$this->template_plain = 'Recast-autocancelUnpaidOrder-buyer.php';
		$this->template_base  = CASTBACK_EMAIL_PATH;
		
		// Test( $order_id );
    
    // Action to which we hook onto to send the email.
		//add_action( 'Recast_Action_autocancelUnpaidOrder_buyer_emailTrigger', array( $this, 'trigger' ) );

		parent::__construct();
	}

	function trigger( $user_email, $order_id ) {
		$this->object = wc_get_order( $order_id );
		$this->heading     = __( 'Order #'.$order_id.': Autocancel Unpaid Order', 'recast-email' );
		$this->subject     = sprintf( _x( 'Order #'.$order_id.': Autocancel Unpaid Order', 'An email sent to the buyer when an offer has been accepted, but not paid.', 'recast-email' ), 'Recast' );
		
		if ( ! $this->is_enabled() || ! $user_email ) {
			return;
		}

		$this->send( $user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
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
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
	}
}