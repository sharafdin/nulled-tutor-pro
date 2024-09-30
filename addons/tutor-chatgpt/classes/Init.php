<?php
/**
 * Handle ChatGPT integration
 *
 * @package TutorPro\ChatGPT
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.8
 */

namespace TutorPro\ChatGPT;

/**
 * Init Class
 *
 * @since 2.1.8
 */
class Init {

	/**
	 * Register hooks and dependencies.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$this->include_files();

		add_action( 'admin_footer', array( $this, 'load_modal' ) );
		add_action( 'wp_footer', array( $this, 'load_modal' ) );

		new Assets();
		new Ajax();
		new Settings();
	}

	/**
	 * Include files.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	private function include_files() {
		include_once TUTOR_CHATGPT_DIR . 'includes/functions.php';
	}

	/**
	 * Load ChatGPT prompt modal.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function load_modal() {
		$is_frontend_builder = tutils()->is_tutor_frontend_dashboard( 'create-course' );
		$is_backend_courses  = is_admin() && isset( get_current_screen()->post_type ) && 'courses' === get_current_screen()->post_type;
		if ( $is_frontend_builder || $is_backend_courses ) {
			include_once tutor_chatgpt()->views . 'prompt-modal.php';
			include_once tutor_chatgpt()->views . 'api-key-modal.php';
		}
	}

}
