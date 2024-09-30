<?php
/**
 * Gradebook Addon
 *
 * @package TutorPro\Addons
 * @subpackage Gradebook
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
define( 'TUTOR_GB_VERSION', '1.0.0' );
define( 'TUTOR_GB_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_gradebook_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_gradebook_config( $config ) {
	$new_config   = array(
		'name'        => __( 'Gradebook', 'tutor-multi-instructors' ),
		'description' => __( 'Shows student progress from assignment and quiz', 'tutor-pro' ),
	);
	$basic_config = (array) TUTOR_GB();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_GB_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_GB' ) ) {
	/**
	 * Addon helper.
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_GB() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_GB_FILE ),
			'url'          => plugin_dir_url( TUTOR_GB_FILE ),
			'basename'     => plugin_basename( TUTOR_GB_FILE ),
			'version'      => TUTOR_GB_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new TUTOR_GB\Init();
