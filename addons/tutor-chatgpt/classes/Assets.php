<?php
/**
 * Manage Assets.
 *
 * @package TutorPro\ChatGPT
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.8
 */

namespace TutorPro\ChatGPT;

/**
 * Assets Class.
 *
 * @since 2.1.8
 */
class Assets {
	/**
	 * Register hooks.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
	}

	/**
	 * Load CSS and JS for ChatGPT addon
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function load_scripts() {
		$chatgpt_enable = (bool) tutils()->get_option( Settings::CHATGPT_ENABLE, true );

		if ( false === $chatgpt_enable ) {
			return;
		};

		$is_frontend_builder = tutils()->is_tutor_frontend_dashboard( 'create-course' );

		if ( $is_frontend_builder || ( is_admin() && 'courses' === get_current_screen()->post_type ) ) {

			$has_api_key = strlen( trim( get_tutor_option( Settings::CHATGPT_API_KEY, '' ) ) ) > 0;

			/**
			 * Instructor will not get bubble if API key not set by admin.
			 */
			$is_admin = current_user_can( 'administrator' );
			if ( false === $is_admin && false === $has_api_key ) {
				return;
			}

			wp_enqueue_style( 'tutor-pro-chatgpt-style-css', tutor_chatgpt()->url . 'assets/css/style.css', array(), TUTOR_PRO_VERSION );
			wp_enqueue_script( 'tutor-pro-chatgpt-js', tutor_chatgpt()->url . 'assets/js/chatgpt.js', array( 'jquery', 'wp-i18n' ), TUTOR_PRO_VERSION, true );

			$data = array(
				'bubble_position' => get_tutor_option( Settings::CHATGPT_BUBBLE_POSITION ),
				'has_api_key'     => $has_api_key,
			);

			wp_localize_script(
				'tutor-pro-chatgpt-js',
				'_tutor_chatgpt',
				$data
			);
		}
	}
}
