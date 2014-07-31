<?php
/**
 * Install Function
 *
 * @package     PDD
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'campaigns' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the PDD Welcome
 * screen.
 *
 * @since 1.0
 * @global $wpdb
 * @global $pdd_options
 * @global $wp_version
 * @return void
 */
function pdd_install() {
	global $wpdb, $pdd_options, $wp_version;

	// Setup the Downloads Custom Post Type
	pdd_setup_pdd_post_types();

	// Setup the Download Taxonomies
	pdd_setup_download_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules();

	// Add Upgraded From Option
	$current_version = get_option( 'pdd_version' );
	if ( $current_version ) {
		update_option( 'pdd_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();

	// Checks if the purchase page option exists
	if ( ! isset( $pdd_options['purchase_page'] ) ) {
	  // Checkout Page
		$checkout = wp_insert_post(
			array(
				'post_title'     => __( 'Checkout', 'pdd' ),
				'post_content'   => '[donation_checkout]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Purchase Confirmation (Success) Page
		$success = wp_insert_post(
			array(
				'post_title'     => __( 'Purchase Confirmation', 'pdd' ),
				'post_content'   => __( 'Thank you for your purchase! [pdd_receipt]', 'pdd' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $checkout,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Failed Purchase Page
		$failed = wp_insert_post(
			array(
				'post_title'     => __( 'Transaction Failed', 'pdd' ),
				'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'pdd' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		// Purchase History (History) Page
		$history = wp_insert_post(
			array(
				'post_title'     => __( 'Purchase History', 'pdd' ),
				'post_content'   => '[purchase_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['purchase_page']         = $checkout;
		$options['success_page']          = $success;
		$options['failure_page']          = $failed;
		$options['purchase_history_page'] = $history;

		// Add a temporary option to note that PDD pages have been created
		set_transient( '_pdd_installed', $options, 30 );

	}

	// Populate some default values
	foreach( pdd_get_registered_settings() as $tab => $settings ) {

		foreach ( $settings as $option ) {

			if( 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
				$options[ $option['id'] ] = '1';
			}

		}

	}

	update_option( 'pdd_settings', array_merge( $pdd_options, $options ) );
	update_option( 'pdd_version', PDD_VERSION );

	// Create wp-content/uploads/pdd/ folder and the .htaccess file
	pdd_create_protection_files( true );

	// Create PDD shop roles
	$roles = new PDD_Roles;
	$roles->add_roles();
	$roles->add_caps();

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Add the transient to redirect
	//set_transient( '_pdd_activation_redirect', true, 30 );
}
register_activation_hook( PDD_PLUGIN_FILE, 'pdd_install' );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * pdd_after_install hook.
 *
 * @since 1.7
 * @return void
 */
function pdd_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$pdd_options = get_transient( '_pdd_installed' );

	// Exit if not in admin or the transient doesn't exist
	if ( false === $pdd_options ) {
		return;
	}

	// Delete the transient
	delete_transient( '_pdd_installed' );

	do_action( 'pdd_after_install', $pdd_options );
}
add_action( 'admin_init', 'pdd_after_install' );

/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when PDD is network activation so we need to create them during admin_init
 *
 * @since 1.9
 * @return void
 */
function pdd_install_roles_on_network() {

	global $wp_roles;

	if( ! is_object( $wp_roles ) ) {
		return;
	}

	if( ! in_array( 'shop_manager', $wp_roles->roles ) ) {

		// Create PDD shop roles
		$roles = new PDD_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}

}
add_action( 'admin_init', 'pdd_install_roles_on_network' );
