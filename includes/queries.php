<?php
function custom_query_shop( $query ) {
	$query->set( 'post_type', 'product' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'order', 'DESC' );
	$query->set( 'orderby', 'date' );
	$query->set( 'meta_query', '_stock_status' );
	$query->set( 'meta_value', 'instock' );
	
	
	if( isset( $_GET['order_id'] ) ) { $listing_id = get_field( 'listing_id', $_GET['order_id'] ); }
	if( isset( $_GET['listing_id'] ) ) { $listing_id = get_field( 'listing_id', $_GET['listing_id'] ); } 	

	if( $listing_id || is_admin() ) {
		$query->set( 'p', $listing_id );
		$query->set( 'posts_per_page', '1' );
	} else { $query->set( 'posts_per_page', '20' ); }
	
} add_action( 'elementor/query/castback-shop' , 'custom_query_shop'  ); 
function custom_query_offer( $query ) {
	// $args = array( 'object_type' => array( 'product' ) );
	
	$query->set( 'post_type', 'product' );
	$query->set( 'post_status', 'publish' );
	// $query->set( 'order', 'DESC' );
	// $query->set( 'orderby', 'date' );
	// $query->set( 'meta_query', '_stock_status' );
	// $query->set( 'meta_value', 'instock' );
	
	// if( isset( $_GET['order_id'] ) ) {
		// $query->set( 'p', get_field( 'listing_id', $_GET['order_id'] ) );
		// $query->set( 'posts_per_page', '1' );
	// }
	// else if( isset( $_GET['listing_id'] ) ) {
		// $query->set( 'p', $_GET['listing_id'] );
		// $query->set( 'posts_per_page', '1' );
	// }
	// else { $query->set( 'posts_per_page', '20' ); }
} add_action( 'elementor/query/castback-offer' , 'custom_query_offer'  ); 
function custom_query_edit( $query ) {	
	if( isset( $_GET['listing_status'] ) ) { $listing_status = $_GET['listing_status']; }
	else { $listing_status = array( 'draft', 'publish' ); } 
	if( isset( $_GET['stock_status'] ) ) { $stock_status = $_GET['stock_status']; }
	else { $stock_status = array( 'instock', 'outofstock' ); }
	
	$query->set( 'post_type', 'product' );
	$query->set( 'posts_per_page', -1 );
	// $query->set( 'limit', -1 );
	
	if( isset( $_GET['listing_id'] ) ) {
		$query->set( 'posts_per_page', 1 );
		$query->set( 'p', $_GET['listing_id'] );
		$listing_status = array( 'draft', 'publish', 'trash' );
	} else {
		$query->set( 'author', get_current_user_id() );
	}
	
	// if( !$listing_status || $listing_status == 'all' ) { $listing_status = array('instock','outofstock'); }
	// if( $listing_status ) { 
		// if( $listing_status == '' ) 
	// }
	// else { $post_status = array('publish','draft'); }
	// else { $post_status = array('publish','draft'); }
	$query->set( 'post_status', $listing_status );

	// if( !$stock_status ) { $stock_status = array('instock','outofstock'); }
	// if( !$stock_status || $stock_status == 'all' ) { $stock_status = array('instock','outofstock'); }
	$query->set( 'meta_query', '_stock_status' );
	$query->set( 'meta_value', $stock_status );
	
	// Test( $stock_status );
	// Test( $listing_status );
	
	// $query->set( 'post_category', $_POST['acf'][''] );
} add_action( 'elementor/query/castback-edit' , 'custom_query_edit'  ); 

function CastBack_Queries_shopFilterButtons( $method = null ) {
	$args = array( 'object_type' => array( 'product' ) );
	$taxonomies = get_taxonomies( $args, 'objects' );
	
	$drawTax = array();
	if( have_posts() ) { /* Determine which Taxonomies are active */
		while( have_posts() ) {
			the_post();
			
			foreach( $taxonomies as $tax ) {
				/* always EXCLUDE these taxonomies... */
				if( !in_array( $tax->name, array( 'product_type', 'product_tag', 'weight' ) ) ) {
					/* If it has_term(), assign it to be drawn */
					if ( has_term( '', $tax->name ) ) { $drawTax[ $tax->name ] = true; }
					else if ( is_tax( $tax->name ) ) { $drawTax[ $tax->name ] = true; }
				}
			}
		}
		wp_reset_postdata();
	}

	echo '<div style="display: inline-grid;">';
		foreach( $taxonomies as $tax ) {			
			if( isset( $drawTax[ $tax->name ] ) ) { CastBack_Queries_shopFilterButtons_drawTax( $tax, $method ); }
			else if( is_tax( $tax->name ) ) { CastBack_Queries_shopFilterButtons_drawTax( $tax, $method ); }
			else if( isset( $_GET[ $tax->name ] ) ) { CastBack_Queries_shopFilterButtons_drawTax( $tax, $method ); }
		}
	echo '</div>';
	
}
function CastBack_Queries_shopFilterButtons_drawTax( $tax, $method ) {
	
	Test( $tax->name );
	Test( is_tax( 'product_cat', 'flies-fly-tying' ) );
	// Test( is_post_type_archive( 'product' ) );
	
	
	
	if( is_tax( $tax->name ) ) {
		$order = ' style="order: 0;"';
		$isActive = ' active';
	}
	else {
		if( $tax->name == 'product_cat' ) { $order = ' style="order: 1;"'; }
		else if( $tax->name == 'product_condition' ) { $order = ' style="order: 2;"'; }
		else if( $tax->name == 'product_brand' ) {
			$order = ' style="order: 3;"';
			
			// Test( is_tax( $tax->name ) );
			if( is_tax( 'product_cat', 'flies-fly-tying' ) ) { $order = 'style="display: none;"'; }
		}
		// else if( $tax->name == 'product_condition' ) { $order = ' style="order: 4;"'; }
		else { $order = ' style="order: 9;"'; }
	}
	
	echo '<div class="CastBack-shopFilter-taxLabel'.$isActive.'"'.$order.'>'; /* start div */
	
	echo '<h3>'.$tax->labels->singular_name.'</h3>';
	echo '<div style="padding: 0.25rem;">';
	
	$args = array(
		'hide_empty' => true,
		'taxonomy' => $tax->name,
		'orderby' => 'name',
		// 'fields'   => count,
		'parent'   => 0,
		'tax_query' => array(
			array(
				'taxonomy' => $tax->name, // Replace 'recipe_tx' with your custom taxonomy slug
				'field'    => 'slug',      // Can be 'slug', 'term_id', or 'name'
				'terms'    => $term->slug,  // The slug of the term you want to query
			),
    ),
	);
	$terms = get_terms( $args );
	foreach( $terms as $term ) {
		$active = false;
		
		if( isset( $_GET[ $tax->name ] ) ) {
			$activeLabel = ' disabled';
			if( $_GET[ $tax->name ] == $term->slug ) { $active = true; $activeLabel = ' active'; }
		}
		else if( is_tax( $tax->name ) ) {
			if( is_tax( $tax->name, $term->slug ) ) { $active = 'archive'; $activeLabel = ' active'; }
			else { $activeLabel = ' disabled'; }
		}
		else {
			$activeLabel = ' d-?';
			
			$args = array(
				'post_type' => 'product', // or your custom post type, e.g., 'product'
				'tax_query' => array(
					array(
						'taxonomy' => $tax->name, // or your custom taxonomy slug, e.g., 'product_category'
						'field'    => 'slug',    // or 'term_id', 'name'
						'terms'    => $term->slug,    // or an array of terms, e.g., array('news', 'updates')
						'operator' => 'IN',      // 'IN' (default), 'NOT IN', 'AND', 'EXISTS', 'NOT EXISTS'
					),
				),
			);
			$termQuery = new WP_Query( $args );
			
			$activeLabel = ' d-none';
			if( $termQuery->have_posts() ) {
				while( $termQuery->have_posts() ) {
					$termQuery->the_post(); // Sets up the post data for the current post
						
					if( has_term( $term->slug, $tax->name ) ) { $activeLabel = ' d-!'; }
				}
				wp_reset_postdata(); // Restore original post data after the custom loop
			}
			
		}
		
		
		// Test( $method );
		if( $method == 'shop' ) {
			echo '<a class="CastBack-shopFilter-termLabel castback-button'.$activeLabel.'" href="'.esc_url_raw( add_query_arg( $_GET, get_term_link( $term ) ) ).'">'.$term->name.'</a>';
		}
		else if( $method == 'archive' ) {
			if( is_tax( $tax->name ) ) {
				if( is_tax( $tax->name, $term->slug ) ) {
					echo '<a class="CastBack-shopFilter-termLabel castback-button'.$activeLabel.'" href="'. remove_query_arg( $tax->name, add_query_arg( $_GET, '/shop' ) ) .'">'.$term->name.'</a>'; 
				}	
				else {
					echo '<a class="CastBack-shopFilter-termLabel castback-button'.$activeLabel.'" href="'. remove_query_arg( $tax->name, add_query_arg( $_GET, get_term_link( $term ) ) ) .'">'.$term->name.'</a>'; 
				}
			}
			else { /* If this is a secondary term... */
				if( $active == true ) { echo '<a class="CastBack-shopFilter-termLabel castback-button'.$activeLabel.'" href="'.esc_url_raw( remove_query_arg( $tax->name ) ).'">'.$term->name.'</a>'; }
				else { echo '<a class="CastBack-shopFilter-termLabel castback-button'.$activeLabel.'" href="'.esc_url_raw( add_query_arg( $tax->name, $term->slug ) ).'">'.$term->name.'</a>'; }
			}
		}
	}
	
	echo "</div>";
	echo "</div>"; /* end div */
}

function CastBack_Queries_listingFilterButtons() {
	ob_start();
	
	/* Stock Status */
	echo '<div style="margin-bottom: 1.25rem;">';
		if( isset( $_GET['stock_status'] ) ) {
			foreach( [ 'instock', 'outofstock' ] as $var ) {
				if( $_GET['stock_status'] == $var ) { $active['stock_status'][$var] = ' active'; }
			}
		} else { $active['stock_status']['all'] = ' active'; }
		
		echo '<a class="castback-button'.$active['stock_status']['all'].'" href="'.esc_url_raw( remove_query_arg( 'stock_status', get_the_permalink() ) ).'">Show All</a>';
		echo '<a class="castback-button'.$active['stock_status']['instock'].'" href="'.esc_url_raw( add_query_arg( 'stock_status', 'instock', get_the_permalink() ) ).'">Available</a>';
		echo '<a class="castback-button'.$active['stock_status']['outofstock'].'" href="'.esc_url_raw( add_query_arg( 'stock_status', 'outofstock', get_the_permalink() ) ).'">Not Available</a>';
	echo '</div>';
	
		/* Post Status */
	echo '<div style="margin-bottom: 1.25rem;">';
		if( isset( $_GET['listing_status'] ) ) {
			foreach( [ 'draft', 'publish', 'trash', 'all' ] as $var ) {
				if( $_GET['listing_status'] == $var ) { $active['listing_status'][$var] = ' active'; }
			}
		} else {
			$active['listing_status']['draft'] = ' active';
			$active['listing_status']['publish'] = ' active';
		}
		
		echo '<a class="castback-button'.$active['listing_status']['draft'].'" href="'.esc_url_raw( add_query_arg( 'listing_status', 'draft', get_the_permalink() ) ).'">Draft</a>';
		echo '<a class="castback-button'.$active['listing_status']['publish'].'" href="'.esc_url_raw( add_query_arg( 'listing_status', 'publish', get_the_permalink() ) ).'">Active</a>';
		echo '<a class="castback-button'.$active['listing_status']['trash'].'" href="'.esc_url_raw( add_query_arg( 'listing_status', 'trash', get_the_permalink() ) ).'">Trash</a>';
		echo '<a class="castback-button'.$active['listing_status']['all'].'" href="'.esc_url_raw( add_query_arg( 'listing_status', 'all', get_the_permalink() ) ).'">Show All</a>';
	echo '</div>';
	
	
	return ob_get_clean();
}
function CastBack_Queries_processFilters( $args ) { /* Only used by CastBack_Listings()?? Remove? 10/23/25 */
	
	// remove_query_arg( 'listing_id' );
	// wp_safe_redirect( esc_url_raw( add_query_arg( 'listing_id', $listing_id, get_site_url(). '/selling/listings/' ) ) );
	// wp_safe_redirect( esc_url_raw( add_query_arg( 'listing_status', $listing_id, get_site_url(). '/selling/listings/' ) ) );
	
	if( isset( $_GET['listing_status'] ) ) { $args['post_status'] = $_GET['listing_status']; }
	// else { $args['post_status'] = 'instock'; }
	if( $args['post_status'] == 'all' ) { $args['post_status'] = null; }
	
	if( isset( $_GET['stock_status'] ) ) { $args['stock_status'] = $_GET['stock_status']; }
	else { $args['stock_status'] = 'instock'; }
	if( $args['stock_status'] == 'all' ) { $args['stock_status'] = null; }

	
	return $args;
}