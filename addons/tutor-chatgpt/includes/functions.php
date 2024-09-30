<?php
/**
 * Helper functions for ChatGPT addon.
 *
 * @package TutorPro\ChatGPT
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.8
 */

if ( ! function_exists( 'tutor_chatgpt_addon_info' ) ) {
	/**
	 * Get ChatGPT addon info
	 *
	 * @since 2.1.8
	 *
	 * @return array
	 */
	function tutor_chatgpt_addon_info() {
		$addon = array(
			'name'         => __( 'ChatGPT', 'tutor-pro' ),
			'description'  => __( 'Generate content using ChatGPT', 'tutor-pro' ),
			'url'          => plugin_dir_url( TUTOR_CHATGPT_FILE ),
			'path'         => plugin_dir_path( TUTOR_CHATGPT_FILE ),
			'basename'     => plugin_basename( TUTOR_CHATGPT_FILE ),
			'assets'       => trailingslashit( plugin_dir_url( TUTOR_CHATGPT_FILE ) . 'assets' ),
			'templates'    => trailingslashit( plugin_dir_path( TUTOR_CHATGPT_FILE ) . 'templates' ),
			'views'        => trailingslashit( plugin_dir_path( TUTOR_CHATGPT_FILE ) . 'views' ),
			'version'      => '1.0.0',
			'nonce_action' => 'tutor_nonce_action',
			'nonce'        => '_wpnonce',
		);

		return $addon;
	}
}

if ( ! function_exists( 'tutor_chatgpt' ) ) {
	/**
	 * ChatGPT addon info as object.
	 *
	 * @since 2.1.8
	 *
	 * @return object
	 */
	function tutor_chatgpt() {
		return (object) tutor_chatgpt_addon_info();
	}
}

