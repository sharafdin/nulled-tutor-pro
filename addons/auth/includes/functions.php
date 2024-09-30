<?php
/**
 * Helper functions for Auth.
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

 if ( ! function_exists( 'tutor_auth_addon_info' ) ) {
	/**
	 * Get Auth addon info
	 *
	 * @since 2.1.9
	 *
	 * @return array
	 */
	function tutor_auth_addon_info() {
		$addon = array(
			'name'         => __( 'Authentication', 'tutor-pro' ),
			'description'  => __( 'Manage authentication', 'tutor-pro' ),
			'url'          => plugin_dir_url( TUTOR_AUTH_FILE ),
			'path'         => plugin_dir_path( TUTOR_AUTH_FILE ),
			'basename'     => plugin_basename( TUTOR_AUTH_FILE ),
			'assets'       => trailingslashit( plugin_dir_url( TUTOR_AUTH_FILE ) . 'assets' ),
			'templates'    => trailingslashit( plugin_dir_path( TUTOR_AUTH_FILE ) . 'templates' ),
			'views'        => trailingslashit( plugin_dir_path( TUTOR_AUTH_FILE ) . 'views' ),
			'version'      => '1.0.0',
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return $addon;
	}
}

if ( ! function_exists( 'tutor_auth' ) ) {
	/**
	 * Auth addon info as object.
	 *
	 * @since 2.1.9
	 *
	 * @return object
	 */
	function tutor_auth() {
		return (object) tutor_auth_addon_info();
	}
}