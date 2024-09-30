<?php
/**
 * Tutor Email Notification Addon
 *
 * @package TutorPro\Addons
 * @subpackage Email
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Constants.
define( 'TUTOR_EMAIL_VERSION', '1.0.0' );
define( 'TUTOR_EMAIL_FILE', __FILE__ );

/**
 * Showing config for addons central lists
 */
add_filter( 'tutor_addons_lists_config', 'tutor_email_config' );
/**
 * Tutor email config.
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_email_config( $config ) {
	$new_config   = array(
		'name'        => __( 'Email', 'tutor-pro' ),
		'description' => __( 'Send email on various tutor events', 'tutor-pro' ),
	);
	$basic_config = (array) TUTOR_EMAIL();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_EMAIL_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_EMAIL' ) ) {
	//phpcs:ignore
	function TUTOR_EMAIL() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_EMAIL_FILE ),
			'url'          => plugin_dir_url( TUTOR_EMAIL_FILE ),
			'basename'     => plugin_basename( TUTOR_EMAIL_FILE ),
			'version'      => TUTOR_EMAIL_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		$info['default_bg'] = $info['url'] . 'assets/images/heading.png';

		return (object) $info;
	}
}

require 'classes/Init.php';
$tutor = new TUTOR_EMAIL\Init();
$tutor->run();
