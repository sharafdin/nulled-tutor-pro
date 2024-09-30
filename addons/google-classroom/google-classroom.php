<?php
/**
 * Google Classroom Addon
 *
 * @package TutorPro\Addons
 * @subpackage GoogleClassroom
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
define( 'TUTOR_GC_VERSION', '1.0.0' );
define( 'TUTOR_GC_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_gc_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_gc_config( $config ) {
	$new_config = array(
		'name'        => __( 'Google Classroom Integration', 'tutor-pro' ),
		'description' => __( 'Helps connect Google Classrooms with Tutor LMS courses, allowing you to use features like Classroom streams and files directly from the Tutor LMS course.', 'tutor-pro' ),
	);

	$basic_config = (array) TUTOR_GC();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_GC_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_GC' ) ) {
	/**
	 * Addon helper.
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_GC() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_GC_FILE ),
			'url'          => plugin_dir_url( TUTOR_GC_FILE ),
			'basename'     => plugin_basename( TUTOR_GC_FILE ),
			'version'      => TUTOR_GC_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);
		return (object) $info;
	}
}

require 'classes/init.php';
new TUTOR_GC\init();
