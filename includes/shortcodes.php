<?php
/**
 * Shortcodes
 *
 * @package     PDD
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Purchase Link Shortcode
 *
 * Retrieves a download and displays the purchase form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string Fully formatted purchase link
 */
function pdd_camp_shortcode( $atts, $content = null ) {
	global $post, $pdd_options;

	$atts = shortcode_atts( array(
		'id' 	        => $post->ID,
		'sku'			=> '',
		'price'         => '1',
		'paypal_direct' => '0',
		'text'	        => isset( $pdd_options[ 'add_to_cart_text' ] )  && $pdd_options[ 'add_to_cart_text' ]    != '' ? $pdd_options[ 'add_to_cart_text' ] : __( 'Purchase', 'pdd' ),
		'style'         => isset( $pdd_options[ 'button_style' ] ) 	 	? $pdd_options[ 'button_style' ] 		: 'button',
		'color'         => isset( $pdd_options[ 'checkout_color' ] ) 	? $pdd_options[ 'checkout_color' ] 		: 'blue',
		'class'         => 'pdd-submit',
		'form_id'       => ''
	),
	$atts, 'donate_link' );

	// Override color if color == inherit
	if ( isset( $atts['color'] )	)
		$atts['color'] = ( $atts['color'] == 'inherit' ) ? '' : $atts['color'];

	if ( isset( $atts['id'] ) ) {
		// Edd_get_donate_link() expects the ID to be download_id since v1.3
		$atts['download_id'] = $atts['id'];

		$download = pdd_get_download( $atts['download_id'] );
	} elseif ( isset( $atts['sku'] ) ) {
		$download = pdd_get_download_by( 'sku', $atts['sku'] );

		$atts['download_id'] = $download->ID;
	}

	if ( $download ) {
		return pdd_get_donate_link( $atts );
	}
}
add_shortcode( 'donate_link', 'pdd_camp_shortcode' );

/**
 * Download History Shortcode
 *
 * Displays a user's download history.
 *
 * @since 1.0
 * @return string
 */
function pdd_camp_history() {
	if ( is_user_logged_in() ) {
		ob_start();
		pdd_get_template_part( 'history', 'campaigns' );
		return ob_get_clean();
	}
}
add_shortcode( 'download_history', 'pdd_camp_history' );

/**
 * Purchase History Shortcode
 *
 * Displays a user's purchase history.
 *
 * @since 1.0
 * @return string
 */
function pdd_purchase_history() {
	if ( is_user_logged_in() ) {
		ob_start();
		pdd_get_template_part( 'history', 'purchases' );
		return ob_get_clean();
	}
}
add_shortcode( 'purchase_history', 'pdd_purchase_history' );

/**
 * Checkout Form Shortcode
 *
 * Show the checkout form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function pdd_checkout_form_shortcode( $atts, $content = null ) {
	return pdd_checkout_form();
}
add_shortcode( 'donation_checkout', 'pdd_checkout_form_shortcode' );

/**
 * Download Cart Shortcode
 *
 * Show the shopping cart.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function pdd_cart_shortcode( $atts, $content = null ) {
	return pdd_shopping_cart();
}
add_shortcode( 'donation_cart', 'pdd_cart_shortcode' );

/**
 * Login Shortcode
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the pdd_login_form function to display the login form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses pdd_login_form()
 * @return string
 */
function pdd_login_form_shortcode( $atts, $content = null ) {
	$args = shortcode_atts( array(
		'redirect' => '',
	), $atts, 'pdd_login' );
	return pdd_login_form( $args['redirect'] );
}
add_shortcode( 'pdd_login', 'pdd_login_form_shortcode' );

/**
 * Register Shortcode
 *
 * Shows a registration form allowing users to users to register for the site
 *
 * @since 2.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses pdd_register_form()
 * @return string
 */
function pdd_register_form_shortcode( $atts, $content = null ) {
	$args = shortcode_atts( array(
		'redirect' => '',
	), $atts, 'pdd_register' );
	return pdd_register_form( $args['redirect'] );
}
add_shortcode( 'pdd_register', 'pdd_register_form_shortcode' );

/**
 * Purchase Collection Shortcode
 *
 * Displays a collection purchase link for adding all items in a taxonomy term
 * to the cart.
 *
 * @since 1.0.6
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function pdd_purchase_collection_shortcode( $atts, $content = null ) {
	global $pdd_options;

	$args = shortcode_atts( array(
		'taxonomy'	=> '',
		'terms'		=> '',
		'text'		=> __('Purchase All Items', 'pdd'),
		'style'		=> isset( $pdd_options['button_style'] ) ? $pdd_options['button_style'] : 'button',
		'color'		=> isset( $pdd_options['checkout_color'] ) ? $pdd_options['checkout_color'] : 'blue',
		'class'		=> 'pdd-submit'
	), $atts, 'purchase_collection' );

	$button_display = implode( ' ', array( $args['style'], $args['color'], $args['class'] ) );

	return '<a href="' . add_query_arg( array( 'pdd_action' => 'purchase_collection', 'taxonomy' => $args['taxonomy'], 'terms' => $args['terms'] ) ) . '" class="' . $button_display . '">' . $args['text'] . '</a>';
}
add_shortcode( 'purchase_collection', 'pdd_purchase_collection_shortcode' );

/**
 * Downloads Shortcode
 *
 * This shortcodes uses the WordPress Query API to get downloads with the
 * arguments specified when using the shortcode. A list of the arguments
 * can be found from the PDD Dccumentation. The shortcode will take all the
 * parameters and display the downloads queried in a valid HTML <div> tags.
 *
 * @since 1.0.6
 * @internal Incomplete shortcode
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string $display Output generated from the downloads queried
 */
function pdd_camps_query( $atts, $content = null ) {
	
	$atts = shortcode_atts( array(
		'category'         => '',
		'exclude_category' => '',
		'tags'             => '',
		'exclude_tags'     => '',
		'relation'         => 'AND',
		'number'           => 10,
		'price'            => 'no',
		'excerpt'          => 'yes',
		'full_content'     => 'no',
		'buy_button'       => 'yes',
		'columns'          => 3,
		'thumbnails'       => 'true',
		'orderby'          => 'post_date',
		'order'            => 'DESC',
		'ids'              => ''
	), $atts, 'campaigns' );

	$query = array(
		'post_type'      => 'pdd_camp',
		'posts_per_page' => (int) $atts['number'],
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order']
	);

	if ( $query['posts_per_page'] < -1 ) {
		$query['posts_per_page'] = abs( $query['posts_per_page'] );
	}

	switch ( $atts['orderby'] ) {
		case 'price':
			$atts['orderby']   = 'meta_value';
			$query['meta_key'] = 'pdd_price';
			$query['orderby']  = 'meta_value_num';
		break;

		case 'title':
			$query['orderby'] = 'title';
		break;

		case 'id':
			$query['orderby'] = 'ID';
		break;

		case 'random':
			$query['orderby'] = 'rand';
		break;

		default:
			$query['orderby'] = 'post_date';
		break;
	}

	if ( $atts['tags'] || $atts['category'] || $atts['exclude_category'] || $atts['exclude_tags'] ) {
		$query['tax_query'] = array(
			'relation'     => $atts['relation']
		);

		if ( $atts['tags'] ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'camp_tag',
				'terms'    => explode( ',', $atts['tags'] ),
				'field'    => 'slug'
			);
		}

		if ( $atts['category'] ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'camp_category',
				'terms'    => explode( ',', $atts['category'] ),
				'field'    => 'slug'
			);
		}

		if ( $atts['exclude_category'] ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'camp_category',
				'terms'    => explode( ',', $atts['exclude_category'] ),
				'field'    => 'slug',
				'operator' => 'NOT IN',
			);
		}

		if ( $atts['exclude_tags'] ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'camp_tag',
				'terms'    => explode( ',', $atts['exclude_tags'] ),
				'field'    => 'slug',
				'operator' => 'NOT IN',
			);
		}
	}

	if( ! empty( $atts['ids'] ) )
		$query['post__in'] = explode( ',', $atts['ids'] );

	if ( get_query_var( 'paged' ) )
		$query['paged'] = get_query_var('paged');
	else if ( get_query_var( 'page' ) )
		$query['paged'] = get_query_var( 'page' );
	else
		$query['paged'] = 1;

	switch( intval( $atts['columns'] ) ) :
	    case 0:
	        $column_width = 'inherit'; break;
		case 1:
			$column_width = '100%'; break;
		case 2:
			$column_width = '50%'; break;
		case 3:
			$column_width = '33%'; break;
		case 4:
			$column_width = '25%'; break;
		case 5:
			$column_width = '20%'; break;
		case 6:
			$column_width = '16.6%'; break;
	endswitch;

	// Allow the query to be manipulated by other plugins
	$query = apply_filters( 'pdd_camps_query', $query, $atts );

	$downloads = new WP_Query( $query );
	if ( $downloads->have_posts() ) :
		$i = 1;
		$wrapper_class = 'pdd_camp_columns_' . $atts['columns'];
		ob_start(); ?>
		<div class="pdd_camps_list <?php echo apply_filters( 'pdd_camps_list_wrapper_class', $wrapper_class, $atts ); ?>">
			<?php while ( $downloads->have_posts() ) : $downloads->the_post(); ?>
				<div itemscope itemtype="http://schema.org/Product" class="<?php echo apply_filters( 'pdd_camp_class', 'pdd_camp', get_the_ID(), $atts, $i ); ?>" id="pdd_camp_<?php echo get_the_ID(); ?>" style="width: <?php echo $column_width; ?>; float: left;">
					<div class="pdd_camp_inner">
						<?php

						do_action( 'pdd_camp_before' );

						if ( 'false' != $atts['thumbnails'] ) :
							pdd_get_template_part( 'shortcode', 'content-image' );
						endif;

						pdd_get_template_part( 'shortcode', 'content-title' );

						if ( $atts['excerpt'] == 'yes' && $atts['full_content'] != 'yes' )
							pdd_get_template_part( 'shortcode', 'content-excerpt' );
						else if ( $atts['full_content'] == 'yes' )
							pdd_get_template_part( 'shortcode', 'content-full' );

						if ( $atts['price'] == 'yes' )
							pdd_get_template_part( 'shortcode', 'content-price' );

						if ( $atts['buy_button'] == 'yes' )
							pdd_get_template_part( 'shortcode', 'content-cart-button' );

						do_action( 'pdd_camp_after' );

						?>
					</div>
				</div>
				<?php if ( $atts['columns'] != 0 && $i % $atts['columns'] == 0 ) { ?><div style="clear:both;"></div><?php } ?>
			<?php $i++; endwhile; ?>

			<div style="clear:both;"></div>

			<?php wp_reset_postdata(); ?>

			<div id="pdd_camp_pagination" class="navigation">
				<?php
				if ( is_single() ) {
					echo paginate_links( array(
						'base'    => get_permalink() . '%#%',
						'format'  => '?paged=%#%',
						'current' => max( 1, $query['paged'] ),
						'total'   => $downloads->max_num_pages
					) );
				} else {
					$big = 999999;
					echo paginate_links( array(
						'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format'  => '?paged=%#%',
						'current' => max( 1, $query['paged'] ),
						'total'   => $downloads->max_num_pages
					) );
				}
				?>
			</div>

		</div>
		<?php
		$display = ob_get_clean();
	else:
		$display = sprintf( _x( 'No %s found', 'download post type name', 'pdd' ), pdd_get_label_plural() );
	endif;

	return apply_filters( 'downloads_shortcode', $display, $atts, $atts['buy_button'], $atts['columns'], $column_width, $downloads, $atts['excerpt'], $atts['full_content'], $atts['price'], $atts['thumbnails'], $query );
}
add_shortcode( 'campaigns', 'pdd_camps_query' );

/**
 * Price Shortcode
 *
 * Shows the price of a download.
 *
 * @since 1.1.3.3
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function pdd_camp_price_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'id' => NULL,
	), $atts, 'pdd_price' );

	if ( is_null( $atts['id'] ) ) {
		$id = get_the_ID();
	}

	return pdd_price( $atts['id'], false );
}
add_shortcode( 'pdd_price', 'pdd_camp_price_shortcode' );

/**
 * Receipt Shortcode
 *
 * Shows an order receipt.
 *
 * @since 1.4
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function pdd_receipt_shortcode( $atts, $content = null ) {
	global $pdd_receipt_args;

	$pdd_receipt_args = shortcode_atts( array(
		'error'           => __( 'Sorry, trouble retrieving payment receipt.', 'pdd' ),
		'price'           => true,
		'discount'        => true,
		'products'        => true,
		'date'            => true,
		'notes'           => true,
		'payment_key'     => false,
		'payment_method'  => true,
		'payment_id'      => true
	), $atts, 'pdd_receipt' );

	$session = pdd_get_purchase_session();
	if ( isset( $_GET[ 'payment_key' ] ) ) {
		$payment_key = urldecode( $_GET[ 'payment_key' ] );
	} elseif ( $pdd_receipt_args['payment_key'] ) {
		$payment_key = $pdd_receipt_args['payment_key'];
	} else if ( $session ) {
		$payment_key = $session[ 'purchase_key' ];
	}

	// No key found
	if ( ! isset( $payment_key ) )
		return $pdd_receipt_args[ 'error' ];

	$pdd_receipt_args[ 'id' ] = pdd_get_purchase_id_by_key( $payment_key );
	$customer_id              = pdd_get_payment_user_id( $pdd_receipt_args[ 'id' ] );

	/*
	 * Check if the user has permission to view the receipt
	 *
	 * If user is logged in, user ID is compared to user ID of ID stored in payment meta
	 *
	 * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
	 *
	 * Or if user is logged in and the user can view sensitive shop data
	 *
	 */

	$user_can_view = ( is_user_logged_in() && $customer_id == get_current_user_id() ) || ( ( $customer_id == 0 || $customer_id == '-1' ) && ! is_user_logged_in() && pdd_get_purchase_session() ) || current_user_can( 'view_shop_sensitive_data' );

	if ( ! apply_filters( 'pdd_user_can_view_receipt', $user_can_view, $pdd_receipt_args ) ) {
		return $pdd_receipt_args[ 'error' ];
	}

	ob_start();

	pdd_get_template_part( 'shortcode', 'receipt' );

	$display = ob_get_clean();

	return $display;
}
add_shortcode( 'pdd_receipt', 'pdd_receipt_shortcode' );

/**
 * Profile Editor Shortcode
 *
 * Outputs the PDD Profile Editor to allow users to amend their details from the
 * front-end. This function uses the PDD templating system allowing users to
 * override the default profile editor template. The profile editor template is located
 * under templates/profile-editor.php, however, it can be altered by creating a
 * file called profile-editor.php in the pdd_template directory in your active theme's
 * folder. Please visit the PDD Documentation for more information on how the
 * templating system is used.
 *
 * @since 1.4
 *
 * @author Sunny Ratilal
 *
 * @param      $atts Shortcode attributes
 * @param null $content
 * @return string Output generated from the profile editor
 */
function pdd_profile_editor_shortcode( $atts, $content = null ) {
	ob_start();

	pdd_get_template_part( 'shortcode', 'profile-editor' );

	$display = ob_get_clean();

	return $display;
}
add_shortcode( 'pdd_profile_editor', 'pdd_profile_editor_shortcode' );

/**
 * Process Profile Updater Form
 *
 * Processes the profile updater form by updating the necessary fields
 *
 * @since 1.4
 * @author Sunny Ratilal
 * @param array $data Data sent from the profile editor
 * @return void
 */
function pdd_process_profile_editor_updates( $data ) {
	// Profile field change request
	if ( empty( $_POST['pdd_profile_editor_submit'] ) && !is_user_logged_in() )
		return false;

	// Nonce security
	if ( ! wp_verify_nonce( $data['pdd_profile_editor_nonce'], 'pdd-profile-editor-nonce' ) )
		return false;

	$user_id       = get_current_user_id();
	$old_user_data = get_userdata( $user_id );

	$display_name = sanitize_text_field( $data['pdd_display_name'] );
	$first_name   = sanitize_text_field( $data['pdd_first_name'] );
	$last_name    = sanitize_text_field( $data['pdd_last_name'] );
	$email        = sanitize_email( $data['pdd_email'] );
	$line1        = ( isset( $data['pdd_address_line1'] ) ? sanitize_text_field( $data['pdd_address_line1'] ) : '' );
	$line2        = ( isset( $data['pdd_address_line2'] ) ? sanitize_text_field( $data['pdd_address_line2'] ) : '' );
	$city         = ( isset( $data['pdd_address_city'] ) ? sanitize_text_field( $data['pdd_address_city'] ) : '' );
	$state        = ( isset( $data['pdd_address_state'] ) ? sanitize_text_field( $data['pdd_address_state'] ) : '' );
	$zip          = ( isset( $data['pdd_address_zip'] ) ? sanitize_text_field( $data['pdd_address_zip'] ) : '' );
	$country      = ( isset( $data['pdd_address_country'] ) ? sanitize_text_field( $data['pdd_address_country'] ) : '' );

	$userdata = array(
		'ID'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $display_name,
		'user_email'   => $email
	);


	$address = array(
		'line1'    => $line1,
		'line2'    => $line2,
		'city'     => $city,
		'state'    => $state,
		'zip'      => $zip,
		'country'  => $country
	);

	do_action( 'pdd_pre_update_user_profile', $user_id, $userdata );

	// New password
	if ( ! empty( $data['pdd_new_user_pass1'] ) ) {
		if ( $data['pdd_new_user_pass1'] !== $data['pdd_new_user_pass2'] ) {
			pdd_set_error( 'password_mismatch', __( 'The passwords you entered do not match. Please try again.', 'pdd' ) );
		} else {
			$userdata['user_pass'] = $data['pdd_new_user_pass1'];
		}
	}

	// Make sure the new email doesn't belong to another user
	if( $email != $old_user_data->user_email ) {
		if( email_exists( $email ) ) {
			pdd_set_error( 'email_exists', __( 'The email you entered belongs to another user. Please use another.', 'pdd' ) );
		}
	}

	// Check for errors
	$errors = pdd_get_errors();

	if( $errors ) {
		// Send back to the profile editor if there are errors
		wp_redirect( $data['pdd_redirect'] );
		pdd_die();
	}

	// Update the user
	$meta    = update_user_meta( $user_id, '_pdd_user_address', $address );
	$updated = wp_update_user( $userdata );

	if ( $updated ) {
		do_action( 'pdd_user_profile_updated', $user_id, $userdata );
		wp_redirect( add_query_arg( 'updated', 'true', $data['pdd_redirect'] ) );
		pdd_die();
	}
}
add_action( 'pdd_edit_user_profile', 'pdd_process_profile_editor_updates' );
