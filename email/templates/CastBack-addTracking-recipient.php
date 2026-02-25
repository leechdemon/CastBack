<?php
/**
 * CastBack - addTracking - recipient
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/castback-addTracking-recipient.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 9.8.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

?>


<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php $template = CastBack_getEmailTemplateFields( basename(__FILE__) ); ?>
<?php echo '<p>'. $template['castback_emailintro'] . '</p>'; ?>
<?php echo '<p style="display: flex; margin: 2rem 0; width: 100%;"><a style="width: fit-content; text-decoration: none; background-color: #1E293B; padding: 8px 16px; margin: auto; border-radius: 0.25rem; color: white; font-weight: 800; text-transform: uppercase; font-size: smaller; letter-spacing: 0.1rem;" href="'.get_site_URL() . '/offers/view-offer/?order_id='.$order_id.'">View Order</a></p>'; ?>
<?php echo '<p>'. $template['castback_emailnextsteps'] . '</p>'; ?>
<?php echo '<p>'. $template['castback_emailoutro'] . '</p>'; ?>

<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
