<?php
/**
 * Tutor Calendar
 *
 * @package TutorPro\Addons
 * @subpackage Calendar
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
define( 'TUTOR_C_VERSION', '1.0.0' );
define( 'TUTOR_C_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_pro_calendar_config' );
/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_pro_calendar_config( $config ) {
	$new_config = array(
		'name'        => __( 'Calendar', 'tutor-pro' ),
		'description' => __( 'Allow students to see everything in a calendar view.', 'tutor-pro' ),
	);

	$basic_config = (array) tutor_pro_calendar();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_C_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'tutor_pro_calendar' ) ) {
	/**
	 * Calendar addon helper
	 *
	 * @return object
	 */
	function tutor_pro_calendar() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_C_FILE ),
			'url'          => plugin_dir_url( TUTOR_C_FILE ),
			'assets'       => plugin_dir_url( TUTOR_C_FILE . 'assets/' ),
			'basename'     => plugin_basename( TUTOR_C_FILE ),
			'version'      => TUTOR_C_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);
		return (object) $info;
	}
}

require 'classes/Init.php';
new TUTOR_PRO_C\Init();
