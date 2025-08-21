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


	// extract(shortcode_atts(array( 'listing_id' => null, 'class' => null ), $atts));
	// $imageURL = get_field( 'images', $listing_id)[0]['image']['url'];
	// if( $imageURL == '' ) { $imageURL = 'https://castback.wpenginepowered.com/wp-content/uploads/2025/08/missing_image.jpg'; }
	// $url = '<img style="max-width: 100%; height: auto;" src="'.$imageURL.'">';
	// $url = '<a style="padding-right: 1rem;" href=""><img style="max-width: 15rem;" src="'.$imageURL.'"></a>';
