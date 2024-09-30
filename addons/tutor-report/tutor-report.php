<?php
/**
 * Report Addon
 *
 * @package TutorPro\Addons
 * @subpackage Report
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
define( 'TUTOR_REPORT_VERSION', '1.0.0' );
define( 'TUTOR_REPORT_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_report_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_report_config( $config ) {
	$new_config   = array(
		'name'        => __( 'Reports', 'tutor-pro' ),
		'description' => __( 'Check your course performance through Tutor Report stats.', 'tutor-pro' ),
	);
	$basic_config = (array) TUTOR_REPORT();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_REPORT_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_REPORT' ) ) {
	/**
	 * Addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_REPORT() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_REPORT_FILE ),
			'url'          => plugin_dir_url( TUTOR_REPORT_FILE ),
			'basename'     => plugin_basename( TUTOR_REPORT_FILE ),
			'version'      => TUTOR_REPORT_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new TUTOR_REPORT\init();

if ( ! function_exists( 'tutor_report_instance' ) ) {
	/**
	 * Get report addon init instance
	 *
	 * @since 1.9.8
	 * @return \TUTOR_REPORT\init
	 */
	function tutor_report_instance() {
		return \TUTOR_REPORT\init::instance();
	}
}
