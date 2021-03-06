<?php
/**
 * Pojo Digital Donations API for creating Email template tags
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {download_list}
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: pdd_do_email_tags( $content, payment_id );
 *
 * To add tags, use: pdd_add_email_tag( $tag, $description, $func ). Be sure to wrap pdd_add_email_tag()
 * in a function hooked to the 'pdd_email_tags' action
 *
 * @package     PDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 * @author      Barry Kooij
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PDD_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since 1.9
	 */
	private $tags;

	/**
	 * Payment ID
	 *
	 * @since 1.9
	 */
	private $payment_id;

	/**
	 * Add an email tag
	 *
	 * @since 1.9
	 *
	 * @param string   $tag  Email tag to be replace in email
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}

	/**
	 * Remove an email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all email tags
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content Content to search for email tags
	 * @param int $payment_id The payment id
	 *
	 * @since 1.9
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $payment_id ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->payment_id = $payment_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->payment_id = null;

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use pdd_do_email_tags instead.
	 *
	 * @since 1.9
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->payment_id, $tag );
	}

}

/**
 * Add an email tag
 *
 * @since 1.9
 *
 * @param string   $tag  Email tag to be replace in email
 * @param callable $func Hook to run when email tag is found
 */
function pdd_add_email_tag( $tag, $description, $func ) {
	PDD()->email_tags->add( $tag, $description, $func );
}

/**
 * Remove an email tag
 *
 * @since 1.9
 *
 * @param string $tag Email tag to remove hook from
 */
function pdd_remove_email_tag( $tag ) {
	PDD()->email_tags->remove( $tag );
}

/**
 * Check if $tag is a registered email tag
 *
 * @since 1.9
 *
 * @param string $tag Email tag that will be searched
 *
 * @return bool
 */
function pdd_email_tag_exists( $tag ) {
	return PDD()->email_tags->email_tag_exists( $tag );
}

/**
 * Get all email tags
 *
 * @since 1.9
 *
 * @return array
 */
function pdd_get_email_tags() {
	return PDD()->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since 1.9
 *
 * @return string
 */
function pdd_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = pdd_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

		}

	}

	// Return the list
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 * @param int $payment_id The payment id
 *
 * @since 1.9
 *
 * @return string Content with email tags filtered out.
 */
function pdd_do_email_tags( $content, $payment_id ) {

	// Replace all tags
	$content = PDD()->email_tags->do_tags( $content, $payment_id );

	// Maintaining backwards compatibility
	$content = apply_filters( 'pdd_email_template_tags', $content, pdd_get_payment_meta( $payment_id ), $payment_id );

	// Return content
	return $content;
}

/**
 * Load email tags
 *
 * @since 1.9
 */
function pdd_load_email_tags() {
	do_action( 'pdd_add_email_tags' );
}
add_action( 'init', 'pdd_load_email_tags', -999 );

/**
 * Add default PDD email template tags
 *
 * @since 1.9
 */
function pdd_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'name',
			'description' => __( "The donor's first name", 'pdd' ),
			'function'    => 'pdd_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( "The donor's full name, first and last", 'pdd' ),
			'function'    => 'pdd_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => __( "The donor's user name on the site, if they registered an account", 'pdd' ),
			'function'    => 'pdd_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( "The donor's email address", 'pdd' ),
			'function'    => 'pdd_email_tag_user_email'
		),
		array(
			'tag'         => 'billing_address',
			'description' => __( "The donor's billing address", 'pdd' ),
			'function'    => 'pdd_email_tag_billing_address'
		),
		array(
			'tag'         => 'date',
			'description' => __( "The date of the payment", 'pdd' ),
			'function'    => 'pdd_email_tag_date'
		),
		array(
			'tag'         => 'subtotal',
			'description' => __( "The amount of the payment before taxes", 'pdd' ),
			'function'    => 'pdd_email_tag_subtotal'
		),
		array(
			'tag'         => 'price',
			'description' => __( "The total amount of the payment", 'pdd' ),
			'function'    => 'pdd_email_tag_price'
		),
		array(
			'tag'         => 'payment_id',
			'description' => __( "The unique ID number for this payment", 'pdd' ),
			'function'    => 'pdd_email_tag_payment_id'
		),
		array(
			'tag'         => 'receipt_id',
			'description' => __( "The unique ID number for this payment receipt", 'pdd' ),
			'function'    => 'pdd_email_tag_receipt_id'
		),
		array(
			'tag'         => 'payment_method',
			'description' => __( "The method of payment used for this payment", 'pdd' ),
			'function'    => 'pdd_email_tag_payment_method'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( "Your site name", 'pdd' ),
			'function'    => 'pdd_email_tag_sitename'
		),
		array(
			'tag'         => 'receipt_link',
			'description' => __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'pdd' ),
			'function'    => 'pdd_email_tag_receipt_link'
		),
	);

	// Apply pdd_email_tags filter
	$email_tags = apply_filters( 'pdd_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		pdd_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

}
add_action( 'pdd_add_email_tags', 'pdd_setup_email_tags' );

/**
 * Email template tag: download_list
 * A list of download links for each download purchased
 *
 * @param int $payment_id
 *
 * @return string download_list
 */
function pdd_email_tag_download_list( $payment_id ) {

	$payment_data  = pdd_get_payment_meta( $payment_id );
	$download_list = '<ul>';
	$cart_items    = pdd_get_payment_meta_cart_details( $payment_id );
	$email         = pdd_get_payment_user_email( $payment_id );

	if ( $cart_items ) {
		$show_names = apply_filters( 'pdd_email_show_names', true );

		foreach ( $cart_items as $item ) {

			if ( pdd_use_skus() ) {
				$sku = pdd_get_download_sku( $item['id'] );
			}

			$price_id = pdd_get_cart_item_price_id( $item );

			if ( $show_names ) {

				$title = get_the_title( $item['id'] );

				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'pdd' ) . ': ' . $sku;
				}

				if ( $price_id !== false ) {
					$title .= "&nbsp;&ndash;&nbsp;" . pdd_get_price_option_name( $item['id'], $price_id );
				}

				$download_list .= '<li>' . apply_filters( 'pdd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . '<br/>';
				$download_list .= '<ul>';
			}

			$files = pdd_get_download_files( $item['id'], $price_id );

			if ( $files ) {
				foreach ( $files as $filekey => $file ) {
					$download_list .= '<li>';
					$file_url = pdd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
					$download_list .= '<a href="' . esc_url( $file_url ) . '">' . pdd_get_file_name( $file ) . '</a>';
					$download_list .= '</li>';
				}
			}
			elseif ( pdd_is_bundled_product( $item['id'] ) ) {

				$bundled_products = pdd_get_bundled_products( $item['id'] );

				foreach ( $bundled_products as $bundle_item ) {

					$download_list .= '<li class="pdd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></li>';

					$files = pdd_get_download_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						$download_list .= '<li>';
						$file_url = pdd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
						$download_list .= '<a href="' . esc_url( $file_url ) . '">' . $file['name'] . '</a>';
						$download_list .= '</li>';
					}
				}
			}

			if ( $show_names ) {
				$download_list .= '</ul>';
			}

			if ( '' != pdd_get_product_notes( $item['id'] ) ) {
				$download_list .= ' &mdash; <small>' . pdd_get_product_notes( $item['id'] ) . '</small>';
			}


			if ( $show_names ) {
				$download_list .= '</li>';
			}
		}
	}
	$download_list .= '</ul>';

	return $download_list;
}

/**
 * Email template tag: file_urls
 * A plain-text list of download URLs for each download purchased
 *
 * @param int $payment_id
 *
 * @return string $file_urls
 */
function pdd_email_tag_file_urls( $payment_id ) {

	$payment_data = pdd_get_payment_meta( $payment_id );
	$file_urls    = '';
	$cart_items   = pdd_get_payment_meta_cart_details( $payment_id );
	$email        = pdd_get_payment_user_email( $payment_id );

	foreach ( $cart_items as $item ) {

		$price_id = pdd_get_cart_item_price_id( $item );
		$files    = pdd_get_download_files( $item['id'], $price_id );

		if ( $files ) {
			foreach ( $files as $filekey => $file ) {
				$file_url = pdd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );

				$file_urls .= esc_html( $file_url ) . '<br/>';
			}
		}
		elseif ( pdd_is_bundled_product( $item['id'] ) ) {

			$bundled_products = pdd_get_bundled_products( $item['id'] );

			foreach ( $bundled_products as $bundle_item ) {

				$files = pdd_get_download_files( $bundle_item );
				foreach ( $files as $filekey => $file ) {
					$file_url = pdd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
					$file_urls .= esc_html( $file_url ) . '<br/>';
				}

			}
		}

	}

	return $file_urls;
}

/**
 * Email template tag: name
 * The buyer's first name
 *
 * @param int $payment_id
 *
 * @return string name
 */
function pdd_email_tag_first_name( $payment_id ) {
	$payment_data = pdd_get_payment_meta( $payment_id );
	$email_name   = pdd_get_email_names( $payment_data['user_info'] );
	return $email_name['name'];
}

/**
 * Email template tag: fullname
 * The buyer's full name, first and last
 *
 * @param int $payment_id
 *
 * @return string fullname
 */
function pdd_email_tag_fullname( $payment_id ) {
	$payment_data = pdd_get_payment_meta( $payment_id );
	$email_name   = pdd_get_email_names( $payment_data['user_info'] );
	return $email_name['fullname'];
}

/**
 * Email template tag: username
 * The buyer's user name on the site, if they registered an account
 *
 * @param int $payment_id
 *
 * @return string username
 */
function pdd_email_tag_username( $payment_id ) {
	$payment_data = pdd_get_payment_meta( $payment_id );
	$email_name   = pdd_get_email_names( $payment_data['user_info'] );
	return $email_name['username'];
}

/**
 * Email template tag: user_email
 * The buyer's email address
 *
 * @param int $payment_id
 *
 * @return string user_email
 */
function pdd_email_tag_user_email( $payment_id ) {
	return pdd_get_payment_user_email( $payment_id );
}

/**
 * Email template tag: billing_address
 * The buyer's billing address
 *
 * @param int $payment_id
 *
 * @return string billing_address
 */
function pdd_email_tag_billing_address( $payment_id ) {

	$user_info    = pdd_get_payment_meta_user_info( $payment_id );
	$user_address = ! empty( $user_info['address'] ) ? $user_info['address'] : array( 'line1' => '', 'line2' => '', 'city' => '', 'country' => '', 'state' => '', 'zip' => '' );

	$return = $user_address['line1'] . "\n";
	if( ! empty( $user_address['line2'] ) ) {
		$return .= $user_address['line2'] . "\n";
	}
	$return .= $user_address['city'] . ' ' . $user_address['zip'] . ' ' . $user_address['state'] . "\n";
	$return .= $user_address['country'];

	return $return;
}

/**
 * Email template tag: date
 * Date of purchase
 *
 * @param int $payment_id
 *
 * @return string date
 */
function pdd_email_tag_date( $payment_id ) {
	$payment_data = pdd_get_payment_meta( $payment_id );
	return date_i18n( get_option( 'date_format' ), strtotime( $payment_data['date'] ) );
}

/**
 * Email template tag: subtotal
 * Price of purchase before taxes
 *
 * @param int $payment_id
 *
 * @return string subtotal
 */
function pdd_email_tag_subtotal( $payment_id ) {
	$subtotal = pdd_currency_filter( pdd_format_amount( pdd_get_payment_subtotal( $payment_id ) ) );
	return html_entity_decode( $subtotal, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: tax
 * The taxed amount of the purchase
 *
 * @param int $payment_id
 *
 * @return string tax
 */
function pdd_email_tag_tax( $payment_id ) {
	$tax = pdd_currency_filter( pdd_format_amount( pdd_get_payment_tax( $payment_id ) ) );
	return html_entity_decode( $tax, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: price
 * The total price of the purchase
 *
 * @param int $payment_id
 *
 * @return string price
 */
function pdd_email_tag_price( $payment_id ) {
	$price = pdd_currency_filter( pdd_format_amount( pdd_get_payment_amount( $payment_id ) ) );
	return html_entity_decode( $price, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: payment_id
 * The unique ID number for this purchase
 *
 * @param int $payment_id
 *
 * @return int payment_id
 */
function pdd_email_tag_payment_id( $payment_id ) {
	return pdd_get_payment_number( $payment_id );
}

/**
 * Email template tag: receipt_id
 * The unique ID number for this purchase receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function pdd_email_tag_receipt_id( $payment_id ) {
	return pdd_get_payment_key( $payment_id );
}

/**
 * Email template tag: payment_method
 * The method of payment used for this purchase
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function pdd_email_tag_payment_method( $payment_id ) {
	return pdd_get_gateway_checkout_label( pdd_get_payment_gateway( $payment_id ) );
}

/**
 * Email template tag: sitename
 * Your site name
 *
 * @param int $payment_id
 *
 * @return string sitename
 */
function pdd_email_tag_sitename( $payment_id ) {
	return get_bloginfo( 'name' );
}

/**
 * Email template tag: receipt_link
 * Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly
 *
 * @param $int payment_id
 *
 * @return string receipt_link
 */
function pdd_email_tag_receipt_link( $payment_id ) {
	return sprintf( __( '%1$sView it in your browser.%2$s', 'pdd' ), '<a href="' . add_query_arg( array( 'payment_key' => pdd_get_payment_key( $payment_id ), 'pdd_action' => 'view_receipt' ), home_url() ) . '">', '</a>' );
}

/**
 * Email template tag: discount_codes
 * Adds a list of any discount codes applied to this purchase
 *
 * @param $int payment_id
 * @since 2.0
 * @return string $discount_codes
 */
function pdd_email_tag_discount_codes( $payment_id ) {
	$user_info = pdd_get_payment_meta_user_info( $payment_id );

	$discount_codes = '';

	if( isset( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) {
		$discount_codes = $user_info['discount'];
	}

	return $discount_codes;
}