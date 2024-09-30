<?php
/**
 * Assignment Addon
 *
 * @package TutorPro/Addons
 * @subpackage Assignment
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defined the tutor main file
 */
define( 'TUTOR_ASSIGNMENTS_VERSION', '1.0.0' );
define( 'TUTOR_ASSIGNMENTS_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_tutor_assignments_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_tutor_assignments_config( $config ) {
	$new_config   = array(
		'name'        => __( 'Assignments', 'tutor-pro' ),
		'description' => __( 'Tutor assignments is a great way to assign tasks to students.', 'tutor-pro' ),
	);
	$basic_config = (array) TUTOR_ASSIGNMENTS();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_ASSIGNMENTS_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_ASSIGNMENTS' ) ) {
	/**
	 * Addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_ASSIGNMENTS() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_ASSIGNMENTS_FILE ),
			'url'          => plugin_dir_url( TUTOR_ASSIGNMENTS_FILE ),
			'basename'     => plugin_basename( TUTOR_ASSIGNMENTS_FILE ),
			'version'      => TUTOR_ASSIGNMENTS_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new TUTOR_ASSIGNMENTS\Init();
