<?php
/**
 * Restrict Content PRO integration Addon
 *
 * @package TutorPro/Addons
 * @subpackage RestrictContentPro
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defined the tutor main file
 */
define( 'TUTOR_RC_VERSION', '1.0.0' );
define( 'TUTOR_RC_FILE', __FILE__ );

/**
 * Showing config for addons central lists
 */
add_filter( 'tutor_addons_lists_config', 'tutor_restrict_content_config' );

/**
 * Showing config for addons central lists
 *
 * @param array $config config.
 *
 * @return array
 */
function tutor_restrict_content_config( $config ) {
	$new_config = array(
		'name'           => __( 'Restrict Content Pro', 'tutor-pro' ),
		'description'    => __( 'Unlock Course depending on Restrict Content Permission.', 'tutor-pro' ),
		'depend_plugins' => array(
			'restrict-content-pro/restrict-content-pro.php' => 'Restrict Content Pro',
		),
	);

	$basic_config = (array) TUTOR_RC();
	$new_config   = array_merge( $new_config, $basic_config );

	$config[ plugin_basename( TUTOR_RC_FILE ) ] = $new_config;
	return $config;
}

if ( ! function_exists( 'TUTOR_RC' ) ) {
	/**
	 * Addon helper
	 *
	 * @return object
	 */
	//phpcs:ignore
	function TUTOR_RC() {
		$info = array(
			'path'         => plugin_dir_path( TUTOR_RC_FILE ),
			'url'          => plugin_dir_url( TUTOR_RC_FILE ),
			'basename'     => plugin_basename( TUTOR_RC_FILE ),
			'version'      => TUTOR_RC_VERSION,
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);
		return (object) $info;
	}
}

require 'classes/init.php';
new TUTOR_RC\Init();
