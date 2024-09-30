<?php
/**
 * Manage Assets.
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

use TUTOR\Input;

/**
 * Assets Class.
 *
 * @since 2.1.9
 */
class Assets {
	/**
	 * Register hooks.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
	}

	/**
	 * Load CSS and JS
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function load_admin_scripts() {
		if ( is_admin() && 'tutor_settings' === Input::get( 'page' ) ) {
			wp_enqueue_script( 'tutor-pro-auth-settings-js', tutor_auth()->url . 'assets/js/settings.js', array( 'jquery', 'wp-i18n' ), TUTOR_PRO_VERSION, true );
		}

	}
}
