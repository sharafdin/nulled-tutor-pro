<?php
/**
 * Content Drip
 *
 * @package TutorPro\Addons
 * @subpackage ContentDrip
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
define( 'TUTOR_CONTENT_DRIP_VERSION', '1.0.0' );
define( 'TUTOR_CONTENT_DRIP_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_content_drip_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_content_drip_config( $config ) {
	$new_config   = array(
		'name'        => __( 'Content Drip', 'tutor-pro' ),
		'description' => __( 'Unlock lessons by schedule or when the student meets specific condition.', 'tutor-pro' ),
	);

	$basic_config = (array) TUTOR_CONTENT_DRIP();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_CONTENT_DRIP_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_CONTENT_DRIP' ) ) {
	/**
	 * Content drip addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_CONTENT_DRIP() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_CONTENT_DRIP_FILE ),
			'url'          => plugin_dir_url( TUTOR_CONTENT_DRIP_FILE ),
			'basename'     => plugin_basename( TUTOR_CONTENT_DRIP_FILE ),
			'version'      => TUTOR_CONTENT_DRIP_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new \TUTOR_CONTENT_DRIP\init();
