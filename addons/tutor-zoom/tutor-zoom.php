<?php
/**
 * Tutor Zoom Integration
 *
 * @package TutorPro\Addons
 * @subpackage Zoom
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defined the tutor main file
 */
define( 'TUTOR_ZOOM_VERSION', '1.0.0' );
define( 'TUTOR_ZOOM_FILE', __FILE__ );
define( 'TUTOR_ZOOM_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );


add_filter( 'tutor_addons_lists_config', 'tutor_zoom_config' );
/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_zoom_config( $config ) {
	$new_config   = array(
		'name'        => __( 'Zoom Integration', 'tutor-pro' ),
		'description' => __( 'Connect Tutor LMS with Zoom to host live online classes. Students can attend live classes right from the lesson page.', 'tutor-pro' ),
	);
	$basic_config = (array) TUTOR_ZOOM();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_ZOOM_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_ZOOM' ) ) {
	/**
	 * Tutor zoom helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_ZOOM() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_ZOOM_FILE ),
			'url'          => plugin_dir_url( TUTOR_ZOOM_FILE ),
			'basename'     => plugin_basename( TUTOR_ZOOM_FILE ),
			'version'      => TUTOR_ZOOM_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'includes/helper.php';
require 'classes/Init.php';

\TUTOR_ZOOM\Init::instance();


if ( ! function_exists( 'tutor_zoom_instance' ) ) {
	/**
	 * Get instance
	 *
	 * @since 1.9.3
	 *
	 * @return TUTOR_ZOOM\Init instance.
	 */
	function tutor_zoom_instance() {
		return \TUTOR_ZOOM\Init::instance();
	}
}
