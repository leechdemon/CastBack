<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Class Recast_Email_submitOffer_sender
 */
class Recast_Email_submitOffer_sender extends WC_Email {

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
    // Email slug we can use to filter other data.
		$this->id          = 'Recast_submitOffer_sender';
		$this->title       = __( 'Recast - submitOffer() - sender', 'recast-email' );
		$this->description = __( 'An email sent to the sender when an offer is submitted.', 'recast-email' );
    // For admin area to let the user know we are sending this email to customers.
		// $this->customer_email = true;
		$this->recipient = 'sender@ofthe.offer';
		
		$this->heading     = __( 'Order #{order_number}: New Offer Recieved', 'recast-email' );
		// translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
		$this->subject     = sprintf( _x( 'Order #{order_number}: New Offer Recieved', 'default email subject for new offers (recieved) once submitted.', 'recast-email' ), '{blogname}' );
    
    // Template paths.
		$this->template_html  = 'Recast-submitOffer-sender.php';
		// $this->template_plain = 'emails/plain/Recast-submitOffer-sender.php';
		$this->template_plain = 'Recast-submitOffer-sender.php';
		$this->template_base  = CASTBACK_EMAIL_PATH . '';
    
    // Action to which we hook onto to send the email.
		// add_action( 'Recast_action_submitOffer', array( $this, 'trigger' ) );

		parent::__construct();
	}

	function trigger( $user_email, $order_id ) {
		$this->order_id = $order_id;
		$this->heading     = __( 'Order #'. $order_id. ': New Offer Sent', 'recast-email' );
		$this->subject     = sprintf( _x( 'Order #'. $order_id. ': New Offer Sent', 'default email subject for New Offer Sent.', 'recast-email' ), 'Recast' );

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