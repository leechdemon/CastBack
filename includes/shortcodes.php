<?php  



function CastBack_purchases() {
	ob_start();
	echo 'purchases page soon...';
	return ob_get_clean();
} add_shortcode('CastBack_purchases', 'CastBack_purchases');


function print_menu_shortcode($atts, $content = null) {
	extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
	return wp_nav_menu( array( 'menu' => $name, 'menu_class' => 'myclass', 'echo' => false ) );
} add_shortcode('menu', 'print_menu_shortcode');
