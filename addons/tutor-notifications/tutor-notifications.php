<?php
/**
 * Notifications related to different tutor actions.
 *
 * @package TutorPro\Addons
 * @subpackage Notification
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.9.10
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defined tutor notifications main file
 */
define( 'TUTOR_NOTIFICATIONS_VERSION', '1.0.0' );
define( 'TUTOR_NOTIFICATIONS_FILE', __FILE__ );

/**
 * Showing config for addons central lists
 */
add_filter( 'tutor_addons_lists_config', 'tutor_notifications_config' );

/**
 * Tutor notifications config
 *
 * @param  array $config config.
 *
 * @return array config
 */
function tutor_notifications_config( $config ) {
	$new_config = array(
		'name'        => __( 'Notifications', 'tutor-pro' ),
		'description' => __( 'Get notifications on frontend dashboard for specified tutor events.', 'tutor-pro' ),
	);

	$basic_config = (array) tutor_notifications();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_NOTIFICATIONS_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'tutor_notifications' ) ) {
	/**
	 * Tutor notifications
	 *
	 * @return object $info
	 */
	function tutor_notifications() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_NOTIFICATIONS_FILE ),
			'url'          => plugin_dir_url( TUTOR_NOTIFICATIONS_FILE ),
			'basename'     => plugin_basename( TUTOR_NOTIFICATIONS_FILE ),
			'version'      => TUTOR_NOTIFICATIONS_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);
		return (object) $info;
	}
}

require 'classes/Init.php';
$tutor_notifications = new TUTOR_NOTIFICATIONS\Init();
$tutor_notifications->run();
