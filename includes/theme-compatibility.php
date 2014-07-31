<?php
/**
 * Theme Compatibility
 *
 * Functions for compatibility with specific themes.
 *
 * @package     PDD
 * @subpackage  Functions/Compatibility
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove the "download" post class from single Download pages
 *
 * The Responsive theme applies special styling the .download class resulting in really terrible display.
 *
 * @since 1.4.3
 * @param array $classes Post classes
 * @param string $class
 * @param int $post_id Post ID
 * @return array
 */
function pdd_responsive_download_post_class( $classes = array(), $class = '', $post_id = 0 ) {
	if (
		! is_singular( 'pdd_camp' ) &&
		! is_post_type_archive( 'pdd_camp' ) &&
		! is_tax( 'camp_category' ) &&
		! is_tax( 'camp_tag' )
	)
		return $classes;

	if ( ( $key = array_search( 'pdd_camp', $classes ) ) )
		unset( $classes[ $key ] );

	return $classes;
}
add_filter( 'post_class', 'pdd_responsive_download_post_class', 999, 3 );