<?php 

function CastBack_add_new_listing($atts, $content = null) {
	extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
	
	ob_start();
	acf_form_head();
	acf_form(array(
		// 'form_attributes'   => array(
			// 'method'	=>	'post',
			// 'class'		=>	'acf-form',
			// 'class'		=>	'',
		// ),
		'post_id'   => 'new_post',
		'new_post'  => array(
				'post_title'   => 'New Listing',
				'post_type'   => 'product',
				'post_status' => 'publish',
			// 'post_parent' => $parentID,
			// 'page_template' => 'custom-comic.php',
		),
		// 'field_groups' => array('group_687295e704ff8',),
		'uploader'		=> 'basic',
		'submit_value' => 'Create New Listing',
		'return'	=> get_site_url().'/selling/edit?listing_id=%post_id%',
	));
	
	return ob_get_clean();
} add_shortcode('CastBack_add_new_listing', 'CastBack_add_new_listing');
function CastBack_make_offer($atts, $content = null) {
	$listing_id = $_GET['listing_id'];
	$args = array(
			'status'        => 'wc-pending', // Set initial status, e.g., 'wc-pending', 'wc-processing', 'wc-completed'
			'customer_id'   => $customer_id,         // Optional: associate with an existing customer ID
			// 'customer_note' => null,         // Optional: add a customer note
			// Add other arguments as needed, e.g., 'parent', 'created_via', 'cart_hash'
	);
	$order = wc_create_order( $args );
	$order_id = $order->get_id();
	
	/* Set ACF fields */
	$listing_price = get_field( 'listing_price', $listing_id );
	update_field( 'offer_amount', $listing_price, $order_id );

	// $listing_price = get_field( 'listing_price', $listing_id );
	update_field( 'listing_id', $listing_id, $order_id );

	$customer_id = get_current_user_id();
	update_field( 'customer_id', $customer_id, $order_id );

	$seller_id = get_the_author_ID();
	update_field( 'seller_id', $seller_id, $order_id );

	/* Set Order Details */
	$quantity = 1;
	$args = array(
		// 'name'         => $product->get_name(),
		// 'tax_class'    => $product->get_tax_class(),
		// 'product_id'   => $product->is_type( ProductType::VARIATION ) ? $product->get_parent_id() : $product->get_id(),
		// 'variation_id' => $product->is_type( ProductType::VARIATION ) ? $product->get_id() : 0,
		// 'variation'    => $product->is_type( ProductType::VARIATION ) ? $product->get_attributes() : array(),
		'subtotal'     		=> $listing_price,
		'total'						=> $listing_price,
		// 'total'       	 => $total,
		// 'quantity'     => $qty,
	);
	$order->add_product( wc_get_product( $listing_id ), $quantity, $args );

	// Example: Setting billing and shipping addresses
	// $billing_address = array(
			// 'first_name' => 'John',
			// 'last_name'  => 'Doe',
			// 'address_1'  => '123 Main St',
			// 'city'       => 'Anytown',
			// 'state'      => 'CA',
			// 'postcode'   => '12345',
			// 'country'    => 'US',
			// 'email'      => 'john.doe@example.com',
			// 'phone'      => '555-123-4567',
	// );
	// $order->set_address( $billing_address, 'billing' );
	// $order->set_address( $billing_address, 'shipping' ); // Can be different if needed

	// Calculate totals after adding items and setting addresses
	$order->calculate_totals();

	// Save the order to the database
	$order->save();

	ob_start();

	if ( $order ) {
			echo '<script>window.location.href = "'.get_site_url().'/buying/offers/?order_id='.$order_id.'";</script>';
	} else {
			echo "Failed to create new order.";
	}
	
	return ob_get_clean();
} add_shortcode('CastBack_make_offer', 'CastBack_make_offer');


function CastBack_make_offer_URL($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	
	$url = get_site_url().'/buying/make-offer/?listing_id='.$listing_id;
	
	ob_start();
	echo '<a class="button" href="'.$url.'">Make Offer</a>';
	return ob_get_clean();
} add_shortcode('CastBack_make_offer_URL', 'CastBack_make_offer_URL');

function CastBack_offers($atts, $content = null) {
	ob_start();
	
	if( $_GET['order_id'] ) {
		$order_id = $_GET['order_id'];
		$listing_id = get_field( 'listing_id', $_GET['order_id'] );

		$args = array(
				'p'							 =>	$listing_id,
        'post_type'      => 'product', // or 'page', 'custom_post_type'
        'posts_per_page' => 1,
        // 'category_name'  => 'news',
        // 'order'          => 'DESC',
        // 'orderby'        => 'date',
    );
		$custom_query = new WP_Query( $args );
		if ( $custom_query->have_posts() ) {
			while ( $custom_query->have_posts() ) {
				$custom_query->the_post();
				echo do_shortcode('[elementor-template id="822"]');
				// echo the_title();
			}
    } else {
			// No posts found
			echo '<p>No posts found matching your criteria.</p>';
    }
		wp_reset_postdata();
		/* Order ID query returns a Listing Block */
		// add_action( 'elementor/query/orderid', function 'custom_query_orderid_'.$order_id.( $query ) {
			// $listing_id = get_field( 'listing_id', $_GET['order_id'] );
			// $query->set( 'p', $listing_id );
			// $query->set( 'post_type', 'product' );
			// $query->set( 'posts_per_page', '1' );
		// } 
 // );
		
		
		
			// echo do_shortcode('[elementor-template id="822" post-id="'.get_field('listing_id', $order_id).'"]');
			// echo do_shortcode('[elementor-template id="822"]');

			//How do I use "elementor-template" on a specific post??
			// I'm trying to use it to display the order before it's displayed
		

		// $listing_id = get_field( 'listing_id', $order_id );
		// $imageURL = get_the_post_thumbnail_url( $listing_id );
		
		// echo '<div class="width: 100%; display: block;">';
			// echo '<div style="width: 25%;"><img src="'.$imageURL.'"></div>';
			// echo '<div style="width: 50%;">'.get_the_title( $listing_id ).'</div>';
			// echo '<div style="width: 25%;"></div>';

		
		// echo '</div>';
		
		acf_form_head();
		acf_form(array(
			// 'form_attributes'   => array(
				// 'method'	=>	'post',
				// 'class'		=>	'acf-form',
				// 'class'		=>	'',
			// ),
			// 'post_title'   => true,
			'post_id'   => $order_id,
			// 'new_post'  => array(
				// 'post_title'   => 'Test '.$comicNumber,
				// 'post_type'   => 'shop_order',
			'post_status' => 'publish',
			// 'product_cat' => $_POST['acf']['field_68644913a0ab7'],
			// 'post_parent' => $parentID,
				// 'page_template' => 'custom-comic.php',
			// ),
			'field_groups' => array('group_689a2f343751f',),
			// 'field_groups' => array(503,),
			'uploader'		=> 'basic',
			'submit_value' => 'Save Offer',
			// 'return'	=> get_site_url() .'/selling/edit?listing=%post_id%',
		));
		
	} else {
		$args = array(
			// 'status' => 'wc-processing', // Get completed orders
			// 'limit'  => 10,           // Retrieve up to 10 orders
			'orderby' => 'date',      // Order by date
			'order'  => 'DESC',  
			// 'customer_id'  => get_current_user_id(),  
		);

		$orders = wc_get_orders( $args );
		foreach( $orders as $order ) {
			 $order_id = $order->get_id();
			 echo '<div class=""><a href="?order_id='.$order_id.'">Order #' .$order_id .'</a></div>';
			 // echo do_shortcode('[elementor-template id="822"]');
		}
	}
	return ob_get_clean();
} add_shortcode('CastBack_offers', 'CastBack_offers');
function CastBack_purchases() {
	ob_start();
	echo 'purchases page soon...';
	return ob_get_clean();
} add_shortcode('CastBack_purchases', 'CastBack_purchases');

function CastBack_edit_offer($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	
	
	// if( $_GET['offer_id'] > 0 ) {
		// acf_form_head();
		// acf_form(array(
			// 'form_attributes'   => array(
				// 'method'	=>	'post',
				// 'class'		=>	'acf-form',
				// 'class'		=>	'',
			// ),
			// 'post_title'   => true,
			// 'post_id'   => $listing_id,
			// 'new_post'  => array(
				// 'post_title'   => 'Test '.$comicNumber,
				// 'post_type'   => 'shop_order',
			// 'post_status' => 'publish',
			// 'product_cat' => $_POST['acf']['field_68644913a0ab7'],
			// 'post_parent' => $parentID,
				// 'page_template' => 'custom-comic.php',
			// ),
			// 'field_groups' => array('group_687295e704ff8',),
			// 'field_groups' => array(503,),
			// 'uploader'		=> 'basic',
			// 'submit_value' => 'Save Offer',
			// 'return'	=> get_site_url() .'/selling/edit?listing=%post_id%',
		// ));
	// } else {
		// echo 'This is not your Listing. Please check your URL, or log out/in and try again.';
	// }
	
	// $term = get_term_by( 'name', $_POST['acf']['field_68644913a0ab7'], 'product_cat' );
	// echo $term->term_id;
	
	return;
	// return ob_get_clean();
} add_shortcode('CastBack_edit_offer', 'CastBack_edit_offer');

function Castback_edit_listing_url($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	return '<a class="button" href="'.get_site_url().'/selling/edit?listing_id='.$listing_id.'">Edit Listing</a>';
} add_shortcode('Castback_edit_listing_url', 'Castback_edit_listing_url');
function Castback_edit_listing($atts, $content = null) {
	extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	
	ob_start();
	
	// echo '<strong>'. get_the_terms( $listing_id, 'product_cat' ) .'</strong><br>';
	// echo get_the_terms( $listing_id, 'product_cat' )[0]->name;
	
	if( get_current_user_id() == get_post_field( 'post_author', $listing_id ) ) {
		acf_form_head();
		acf_form(array(
			// 'form_attributes'   => array(
				// 'method'	=>	'post',
				// 'class'		=>	'acf-form',
				// 'class'		=>	'',
			// ),
			'post_title'   => true,
			'post_id'   => $listing_id,
			// 'new_post'  => array(
				// 'post_title'   => 'Test '.$comicNumber,
				// 'post_type'   => 'product',
			'post_status' => 'publish',
			// 'product_cat' => $_POST['acf']['field_68644913a0ab7'],
			// 'post_parent' => $parentID,
				// 'page_template' => 'custom-comic.php',
			// ),
			// 'field_groups' => array('group_687295e704ff8',),
			// 'field_groups' => array(503,),
			'uploader'		=> 'basic',
			'submit_value' => 'Save Listing',
			// 'return'	=> get_site_url() .'/selling/edit?listing=%post_id%',
		));
	} else {
		echo 'This is not your Listing. Please check your URL, or log out/in and try again.';
	}
	
	// $term = get_term_by( 'name', $_POST['acf']['field_68644913a0ab7'], 'product_cat' );
	// echo $term->term_id;
	
	return ob_get_clean();
} add_shortcode('Castback_edit_listing', 'Castback_edit_listing');
function print_menu_shortcode($atts, $content = null) {
	extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
	return wp_nav_menu( array( 'menu' => $name, 'menu_class' => 'myclass', 'echo' => false ) );
} add_shortcode('menu', 'print_menu_shortcode');
