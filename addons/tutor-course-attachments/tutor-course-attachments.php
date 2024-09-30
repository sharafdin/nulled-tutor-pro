<?php
/**
 * Course Attachment Addon
 *
 * @package TutorPro/Addons
 * @subpackage CourseAttachment
 * @author Themeum <support@themeum.com>
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defined the tutor main file
 */
define( 'TUTOR_CA_VERSION', '1.0.0' );
define( 'TUTOR_CA_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_course_attachments_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_course_attachments_config( $config ) {
	$new_config = array(
		'name'        => __( 'Course Attachments', 'tutor-pro' ),
		'description' => __( 'Add unlimited attachments/ private files to any Tutor course', 'tutor-pro' ),
	);

	$basic_config = (array) TUTOR_CA();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_CA_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_CA' ) ) {
	/**
	 * Addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_CA() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_CA_FILE ),
			'url'          => plugin_dir_url( TUTOR_CA_FILE ),
			'basename'     => plugin_basename( TUTOR_CA_FILE ),
			'version'      => TUTOR_CA_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new TUTOR_CA\Init();
