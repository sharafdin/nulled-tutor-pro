<?php
/**
 * Buddypress Integration
 *
 * @package TutorPro\Addons
 * @subpackage Buddypress
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
define( 'TUTOR_BP_VERSION', '1.0.0' );
define( 'TUTOR_BP_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_bp_config' );
/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_bp_config( $config ) {
	$buddy_press     = ABSPATH . 'wp-content/plugins/buddypress/bp-loader.php';
	$buddy_boss      = ABSPATH . 'wp-content/plugins/buddyboss-platform/bp-loader.php';
	$required_plugin = 'buddypress/bp-loader.php';

	if ( ! file_exists( $buddy_press ) && file_exists( $buddy_boss ) ) {
		$required_plugin = 'buddyboss-platform/bp-loader.php';
	}

	$new_config = array(
		'name'           => __( 'BuddyPress', 'tutor-pro' ),
		'description'    => __( 'Discuss about course and share your knowledge with your friends through BuddyPress', 'tutor-pro' ),
		'depend_plugins' => array( $required_plugin => 'BuddyPress' ),
	);

	$basic_config = (array) TUTOR_BP();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_BP_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_BP' ) ) {
	/**
	 * Buddypress addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_BP() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_BP_FILE ),
			'url'          => plugin_dir_url( TUTOR_BP_FILE ),
			'basename'     => plugin_basename( TUTOR_BP_FILE ),
			'version'      => TUTOR_BP_VERSION,
			'nonce_action' => 'nonce_action',
			'nonce'        => '_wpnonce',
		);

		return (object) $info;
	}
}

require 'classes/init.php';
new \TUTOR_BP\init();
