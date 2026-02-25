<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Class CastBack_Email_submitOffer_recipient
 */
class CastBack_Email_submitOffer_recipient extends WC_Email {

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
    // Email slug we can use to filter other data.
		$this->id          = 'CastBack_submitOffer_recipient';
		$this->title       = __( 'CastBack - submitOffer() - recipient', 'castback-email' );
		$this->description = __( 'An email sent to the recipient when an offer is submitted.', 'castback-email' );
    // For admin area to let the user know we are sending this email to customers.
		// $this->customer_email = true;
		$this->recipient = 'recipient@ofthe.offer';
		
		$this->heading     = __( 'Order #{order_number}: New Offer Recieved', 'castback-email' );
		// translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
		$this->subject     = sprintf( _x( 'Order #{order_number}: New Offer Recieved', 'default email subject for new offers (recieved) once submitted.', 'castback-email' ), '{blogname}' );
    
    // Template paths.
		$this->template_html  = 'CastBack-submitOffer-recipient.php';
		// $this->template_plain = 'emails/plain/CastBack-submitOffer-recipient.php';
		$this->template_plain = 'CastBack-submitOffer-recipient.php';
		$this->template_base  = CASTBACK_EMAIL_PATH . '';
    
    // Action to which we hook onto to send the email.
		// add_action( 'CastBack_action_submitOffer', array( $this, 'trigger' ) );

		parent::__construct();
	}

	function trigger( $user_email, $order_id ) {
		$this->order_id = $order_id;
		$this->heading     = __( 'Order #'. $order_id. ': New Offer Received', 'castback-email' );
		$this->subject     = sprintf( _x( 'Order #'. $order_id. ': New Offer Received', 'default email subject for New Offer Received.', 'castback-email' ), 'CastBack' );

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->send( $user_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => wc_get_order( $order_id ),
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
			'order'         => wc_get_order( $order_id ),
			'order_id'      => $this->order_id,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
	}
}