<?php 

if(!function_exists('Test')) { // PHP script to dump variable into JavaScript console on front-end.
	function Test($output, $with_script_tags = false) {
		$js_code = json_encode($output, JSON_HEX_TAG);
		if ($with_script_tags===false) { echo '<script>console.log("Test: " + ' .json_encode($js_code). ');</script>'; }
		else { echo "<pre>" .var_dump($js_code). "</pre>"; }
	 }
}

//if(!function_exists('OutputTemplateSlug')) {
//	function OutputTemplateSlug() {	
//		$Display = false;	
//		if($Display) {
//			global $template;
//			echo '<script>console.log("Template: ' .basename($template).'");</script>';
//		}
//	} add_action( 'wp_head', 'OutputTemplateSlug' );
//}

function castback_admin_edit_listing( $wp_admin_bar ) {
		if( $_GET['listing_id'] ) {
			$url = get_site_URL() . '/wp-admin/post.php?post='.$_GET['listing_id'].'&action=edit';
			$args = array(
					'id'    => 'edit-listing', // Unique ID for your link
					'title' => 'Edit Listing', // Text displayed in the admin bar
					'href'  => $url,
					'meta'  => array(
							'class' => 'my-custom-link-class', // Optional: Add a custom CSS class
							'title' => 'Links directly to "Edit Product" in Admin', // Optional: Add a tooltip
					),
			);
			$wp_admin_bar->add_node( $args );
		}
} add_action( 'admin_bar_menu', 'castback_admin_edit_listing', 90 );

function castback_admin_edit_order( $wp_admin_bar ) {
		if( $_GET['order_id'] ) {
			$url = get_site_URL() . '/wp-admin/admin.php?page=wc-orders&action=edit&id='.$_GET['order_id'].'&action=edit';
			$args = array(
					'id'    => 'edit-order', // Unique ID for your link
					'title' => 'Edit Order', // Text displayed in the admin bar
					'href'  => $url,
					'meta'  => array(
							'class' => 'my-custom-link-class', // Optional: Add a custom CSS class
							'title' => 'Links directly to "Edit Order" in Admin', // Optional: Add a tooltip
					),
			);
			$wp_admin_bar->add_node( $args );
		}
} add_action( 'admin_bar_menu', 'castback_admin_edit_order', 90 );