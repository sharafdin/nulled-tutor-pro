<?php
/**
 * Enrollment Addon
 *
 * @package TutorPro\Addons
 * @subpackage Enrollment
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
define( 'TUTOR_ENROLLMENTS_VERSION', '1.0.0' );
define( 'TUTOR_ENROLLMENTS_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_enrollments_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_enrollments_config( $config ) {
	$new_config = array(
		'name'        => __( 'Enrollment', 'tutor-pmpro' ),
		'description' => __( 'Take advanced control on enrollment. Enroll the student manually.', 'tutor-pro' ),
	);

	$basic_config = (array) TUTOR_ENROLLMENTS();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_ENROLLMENTS_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_ENROLLMENTS' ) ) {
	/**
	 * Enrollment addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_ENROLLMENTS() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_ENROLLMENTS_FILE ),
			'url'          => plugin_dir_url( TUTOR_ENROLLMENTS_FILE ),
			'basename'     => plugin_basename( TUTOR_ENROLLMENTS_FILE ),
			'version'      => TUTOR_ENROLLMENTS_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new \TUTOR_ENROLLMENTS\init();
