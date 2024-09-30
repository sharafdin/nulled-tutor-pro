<?php
/**
 * Certificate Addon
 *
 * @package TutorPro/Addons
 * @subpackage Certificate
 * @author Themeum <support@themeum.com>
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defined the tutor main file
 */
define( 'TUTOR_CERT_VERSION', '1.0.0' );
define( 'TUTOR_CERT_FILE', __FILE__ );

add_filter( 'tutor_addons_lists_config', 'tutor_certificate_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_certificate_config( $config ) {
	$new_config = array(
		'name'        => __( 'Certificate', 'tutor-pro' ),
		'description' => __( 'Students will be able to download a certificate after course completion.', 'tutor-pro' ),
	);

	$basic_config = (array) TUTOR_CERT();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_CERT_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_CERT' ) ) {
	/**
	 * Addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_CERT() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_CERT_FILE ),
			'url'          => plugin_dir_url( TUTOR_CERT_FILE ),
			'basename'     => plugin_basename( TUTOR_CERT_FILE ),
			'version'      => TUTOR_CERT_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);
		return (object) $info;
	}
}

require 'classes/init.php';
new TUTOR_CERT\Init();
