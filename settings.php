<?php

function Recast_Settings_CreateMenus() {
	/* Create Recast Menu */
	acf_add_options_page( array(
		'page_title' => 'Recast',
		'menu_slug' => 'recast',
		'menu_title' => 'Recast',
		'position' => 0,
		'redirect' => 'recast-settings',
	) );

	/* Create Settings Subpage */
	acf_add_options_page( array(
		'page_title' => 'Recast - Settings',
		'menu_slug' => 'recast-settings',
		'menu_title' => 'Settings',
		'parent_slug' => 'recast',
		'position' => 0,
		'redirect' => false,
	) );
	/* Create Email Subpage */
	acf_add_options_page( array(
		'page_title' => 'Recast - Emails',
		'menu_slug' => 'recast-emails',
		'menu_title' => 'Emails',
		'parent_slug' => 'recast',
		'position' => 50,
		'redirect' => false,
	) );	

	// add_submenu_page(
		// 'recast' //parent_slug
		// , 'Recast - FAQ' //page_title
		// , 'FAQ' //menu_title
		// , 'manage_options' //capability
		// , 'recast-faq' //menu_slug
		// , 'Recast_Settings_FAQ' //callback
		// , 90 //position
	// )
	
	/* Create Tools Subpage */
	acf_add_options_page( array(
		'page_title' => 'Recast - LD Tools',
		'menu_slug' => 'ld-tools',
		'menu_title' => 'Tools',
		'parent_slug' => 'recast',
		'position' => 90,
		'redirect' => false,
	) );

	/* Create Support Subpage */
	acf_add_options_page( array(
		'page_title' => 'Recast - Support',
		'menu_slug' => 'recast-support',
		'menu_title' => 'Recast Support',
		'parent_slug' => 'recast',
		'position' => 99,
		'redirect' => false,
	) );
	
} add_action('admin_menu', 'Recast_Settings_CreateMenus');

function Recast_Settings_AddFieldGroups() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) { 
		return;
	}

	/* Add Recast Settings field group */
	// acf_add_local_field_group( array(
	// 	'key' => 'group_68b37c50860b0',
	// 	'title' => 'Recast - Settings',
	// 	'fields' => array(
	// 		array(
	// 			'key' => 'field_68c56e4197be2',
	// 			'label' => 'Run Automations (Cron Jobs)',
	// 			'name' => 'run_automations',
	// 			'aria-label' => '',
	// 			'type' => 'true_false',
	// 			'instructions' => '',
	// 			'required' => 0,
	// 			'conditional_logic' => 0,
	// 			'wrapper' => array(
	// 				'width' => '',
	// 				'class' => '',
	// 				'id' => '',
	// 			),
	// 			'message' => '',
	// 			'default_value' => 0,
	// 			'allow_in_bindings' => 0,
	// 			'ui' => 0,
	// 			'ui_on_text' => '',
	// 			'ui_off_text' => '',
	// 		),
	// 		array(
	// 			'key' => 'field_68b37c53386dc',
	// 			'label' => 'Cron - No Offer Expired Date (Minutes)',
	// 			'name' => 'cron_no_offer_expired_date',
	// 			'aria-label' => '',
	// 			'type' => 'number',
	// 			'instructions' => '',
	// 			'required' => 0,
	// 			'conditional_logic' => 0,
	// 			'wrapper' => array(
	// 				'width' => '25',
	// 				'class' => '',
	// 				'id' => '',
	// 			),
	// 			'default_value' => 3,
	// 			'min' => '',
	// 			'max' => '',
	// 			'allow_in_bindings' => 0,
	// 			'placeholder' => '',
	// 			'step' => '',
	// 			'prepend' => '',
	// 			'append' => '',
	// 		),
	// 		array(
	// 			'key' => 'field_68b37cd4386dd',
	// 			'label' => 'Cron - No Shipped Refund Date (Minutes)',
	// 			'name' => 'cron_no_shipped_refund_date',
	// 			'aria-label' => '',
	// 			'type' => 'number',
	// 			'instructions' => '',
	// 			'required' => 0,
	// 			'conditional_logic' => 0,
	// 			'wrapper' => array(
	// 				'width' => '25',
	// 				'class' => '',
	// 				'id' => '',
	// 			),
	// 			'default_value' => 14,
	// 			'min' => '',
	// 			'max' => '',
	// 			'allow_in_bindings' => 0,
	// 			'placeholder' => '',
	// 			'step' => '',
	// 			'prepend' => '',
	// 			'append' => '',
	// 		),
	// 		array(
	// 			'key' => 'field_68b37cd5386de',
	// 			'label' => 'Cron - No Dispute Completed Date (Minutes)',
	// 			'name' => 'cron_no_dispute_completed_date',
	// 			'aria-label' => '',
	// 			'type' => 'number',
	// 			'instructions' => '',
	// 			'required' => 0,
	// 			'conditional_logic' => 0,
	// 			'wrapper' => array(
	// 				'width' => '25',
	// 				'class' => '',
	// 				'id' => '',
	// 			),
	// 			'default_value' => 14,
	// 			'min' => '',
	// 			'max' => '',
	// 			'allow_in_bindings' => 0,
	// 			'placeholder' => '',
	// 			'step' => '',
	// 			'prepend' => '',
	// 			'append' => '',
	// 		),
	// 		array(
	// 			'key' => 'field_68c570427d944',
	// 			'label' => 'Cron - ? Missing One...',
	// 			'name' => 'cron_missing_one',
	// 			'aria-label' => '',
	// 			'type' => 'number',
	// 			'instructions' => '',
	// 			'required' => 0,
	// 			'conditional_logic' => 0,
	// 			'wrapper' => array(
	// 				'width' => '25',
	// 				'class' => '',
	// 				'id' => '',
	// 			),
	// 			'default_value' => 14,
	// 			'min' => '',
	// 			'max' => '',
	// 			'allow_in_bindings' => 0,
	// 			'placeholder' => '',
	// 			'step' => '',
	// 			'prepend' => '',
	// 			'append' => '',
	// 		),
	// 		array(
	// 			'key' => 'field_690a2611641c9',
	// 			'label' => 'Minimum Offer Total',
	// 			'name' => 'minimum_offer_total',
	// 			'aria-label' => '',
	// 			'type' => 'number',
	// 			'instructions' => 'If a value is set, Offers will be adjusted if their total (including shipping) does not meet this amount.',
	// 			'required' => 0,
	// 			'conditional_logic' => 0,
	// 			'wrapper' => array(
	// 				'width' => '25',
	// 				'class' => '',
	// 				'id' => '',
	// 			),
	// 			'default_value' => '',
	// 			'min' => 0,
	// 			'max' => '',
	// 			'allow_in_bindings' => 0,
	// 			'placeholder' => '',
	// 			'step' => '',
	// 			'prepend' => '',
	// 			'append' => '',
	// 		),
	// 	),
	// 	'location' => array(
	// 		array(
	// 			array(
	// 				'param' => 'options_page',
	// 				'operator' => '==',
	// 				'value' => 'recast-settings',
	// 			),
	// 		),
	// 	),
	// 	'menu_order' => 0,
	// 	'position' => 'normal',
	// 	'style' => 'default',
	// 	'label_placement' => 'top',
	// 	'instruction_placement' => 'label',
	// 	'hide_on_screen' => '',
	// 	'active' => true,
	// 	'description' => '',
	// 	'show_in_rest' => 0,
	// 	'display_title' => '',
	// 	'allow_ai_access' => false,
	// 	'ai_description' => '',
	// 	'no_values_message' => '',
	// ) );

	/* Add Recast Support Ticket field group */
	

	/* Add Recast Emails field group */
	acf_add_local_field_group( array(
		'key' => 'group_6927164ec3bb8',
		'title' => 'Recast - Email Fields',
		'fields' => array(
			array(
				'key' => 'field_692718f27e97d',
				'label' => 'Email Template',
				'name' => 'email_template',
				'aria-label' => '',
				'type' => 'repeater',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'layout' => 'block',
				'pagination' => 0,
				'min' => 0,
				'max' => 0,
				'collapsed' => '',
				'button_label' => 'Add Email Template Fields',
				'rows_per_page' => 20,
				'sub_fields' => array(
					array(
						'key' => 'field_692719aeb7a8b',
						'label' => 'Email ID',
						'name' => 'email_id',
						'aria-label' => '',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '100',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Recast-(orderAction)-(seller).php',
						'maxlength' => '',
						'allow_in_bindings' => 0,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'parent_repeater' => 'field_692718f27e97d',
					),
					array(
						'key' => 'field_69271f73e5d61',
						'label' => '(view fields)',
						'name' => '',
						'aria-label' => '',
						'type' => 'accordion',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'open' => 0,
						'multi_expand' => 0,
						'endpoint' => 0,
						'parent_repeater' => 'field_692718f27e97d',
					),
					array(
						'key' => 'field_69271963c96c0',
						'label' => 'Email - Introduction',
						'name' => 'recast_emailintro',
						'aria-label' => '',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '30',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Hi there,
						
						There has been an update to your order.',
						'allow_in_bindings' => 0,
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 1,
						'delay' => 0,
						'parent_repeater' => 'field_692718f27e97d',
					),
					array(
						'key' => 'field_6927197fc96c1',
						'label' => 'Email - Next Steps',
						'name' => 'recast_emailnextsteps',
						'aria-label' => '',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '30',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'For next steps, please check the FAQ on our website.',
						'allow_in_bindings' => 0,
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 1,
						'delay' => 0,
						'parent_repeater' => 'field_692718f27e97d',
					),
					array(
						'key' => 'field_692719157e97f',
						'label' => 'Email - Conclusion',
						'name' => 'recast_emailoutro',
						'aria-label' => '',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '30',
							'class' => '',
							'id' => '',
						),
						'default_value' => 'Thanks!',
						'allow_in_bindings' => 0,
						'tabs' => 'all',
						'toolbar' => 'full',
						'media_upload' => 1,
						'delay' => 0,
						'parent_repeater' => 'field_692718f27e97d',
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'recast-emails',
				),
			),
		),
		'menu_order' => 10,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
		'display_title' => '',
		'allow_ai_access' => false,
		'ai_description' => '',
		'no_values_message' => '',
	) );
} add_action( 'acf/include_fields', 'Recast_Settings_AddFieldGroups' );

function Recast_Settings_FAQ() {
	if( $_GET['page'] == 'ld-tools' ) { 
		echo '<style>
			.ldtools_tooltip::before { content: "(explain this...)"; }
			.ldtools_tooltip {
				position: relative;
				display: inline-block;
				cursor: pointer;
				background-color: tan;
				color: white;
				padding: 0.25rem 0.5rem;
				border-radius: 1rem;
			}

			#ldtools_tooltip_faq {
				visibility: hidden;
				border-radius: 6px;
				padding: 5px 0;
				position: absolute;
				z-index: 1;
				animation-duration: 3s;
				background-color: tan;
				color: white;
				padding: 0.25rem 0.5rem;
				border-radius: 1rem;
			}

			.ldtools_tooltip:hover #ldtools_tooltip_faq {
				visibility: visible;
			}

		</style>';

		// echo '<script>document.getElementById("faq_info").innerHTML = "REPLACED THE CONTENT!!!";</script>';
		$videoURL = 'https://www.youtube.com/embed/u3CKgkyc7Qo?si=ZrtPmpRN5fhWD14q';
		$divID = 'ldtools_tooltip_faq';
		echo '<script>document.getElementById("'.$divID.'").innerHTML = "<iframe width=\"560\" height=\"315\" src=\"'.$videoURL.'\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>";</script>';
		
		$divID = 'ldtools_tooltip_advanced';
		echo '<script>document.getElementById("'.$divID.'").innerHTML = "<iframe width=\"560\" height=\"315\" src=\"'.$videoURL.'\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>";</script>';
	}
} add_action('admin_footer', 'Recast_Settings_FAQ');

function Recast_Settings_TechSupport_SubmitTicket( $post_id ) {
	if( isset( $_GET['page'] ) ) { $page = $_GET['page']; }

	extract( get_field('new_ticket', 'option' ) );
	if( $post_id == 'options' && $page == 'recast-support' && $ticket_type != 'none' ) {
		

		$message = 'A new Support ticket has been created by '.get_bloginfo('name').'.<br><br>';
		$message .= '<strong>Ticket Type</strong>: '. $ticket_type .'<br>';
		$message .= '<strong>Ticket Subject</strong>: '. $ticket_subject .'<br>';
		$message .= '<strong>Ticket Description</strong>: '. $ticket_description .'<br>';
		$order = wc_get_order( $order_number );
		if( !$order ) { $message .= '<strong>Order Number</strong>: '. $order_number .'<br>'; }
		else { $message .= '<strong>Order Number</strong>: <a href="'.get_site_url().'/wp-admin/admin.php?page=wc-orders&action=edit&id='.$order_number.'">'. $order_number .'</a><br>'; }

		/* Images */
		$message .= '<strong>Images</strong>: <br>';
		foreach( $images as $image ) { 
			$message .= '<img style="max-width: 500px;" src="'. $image['image'] .'">';
		 }
		
		$ticket_number = get_field( 'ticket_number', 'option' );
		$ticket_number++;
		
		/* Send Ticket Email */
		wp_mail( 'jason@leechdemon.com', 'Recast - New Ticket #' .$ticket_number, $message );

		/* Reset Fields */
		update_field( 'new_ticket', ['ticket_type' => 'none', 'ticket_subject' => '', 'ticket_description' => '', 'order_number' => '' , 'images' => '' ], 'option' );
		update_field( 'ticket_number', $ticket_number, 'option' );
	}
} add_action('acf/save_post', 'Recast_Settings_TechSupport_SubmitTicket', 20);

function LD_Tools_HideMenus() {
	$hideMenus = get_field( 'hide_menus', 'options' );

	foreach( $hideMenus as $hideMenuItem ) {
		$success =  remove_menu_page( $hideMenuItem );
		// Test( $success );
		if( $success ) { Test( 'LD Tools: '.$success[0].' menu hidden.' ); }
		// else { Test( 'LD Tools: '. $success ) }
	}
} add_action('admin_menu', 'LD_Tools_HideMenus');