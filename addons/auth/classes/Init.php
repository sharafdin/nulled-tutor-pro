<?php
/**
 * Auth Logic Init
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

/**
 * Init Class
 *
 * @since 2.1.9
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

		new Assets();
		new Settings();
		new Ajax();
		new SpamProtection();
		new _2FA();
	}

	/**
	 * Include files.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	private function include_files() {
		include_once TUTOR_AUTH_DIR . 'includes/functions.php';
	}

}
