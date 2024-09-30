<?php
/**
 * Enqueue scripts
 *
 * @package TutorPro\SocialLogin\Assets
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\SocialLogin\Assets;

use TutorPro\SocialLogin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue scripts for social login
 */
class Enqueue {

	/**
	 * Register hooks
	 *
	 * @since 2.1.9
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_facebook_lib', 1 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::load_frontend_scripts' );

	}


	/**
	 * Enqueue frontend scripts
	 *
	 * @return void
	 */
	public static function load_frontend_scripts(): void {
		// If user logged in then return.
		if ( get_current_user_id() ) {
			return;
		}

		$plugin_data = SocialLogin::meta_data();

		// Social authentication custom scripts.
		wp_enqueue_script(
			'tutor-pro-social-authentication',
			$plugin_data['assets'] . 'js/scripts.js',
			array( 'jquery', 'wp-i18n' ),
			filemtime( $plugin_data['path'] . 'assets/js/scripts.js' ),
			true
		);

		// Google authentication library.
		if ( tutor_utils()->get_option( 'enable_google_login' ) ) {
			wp_enqueue_script(
				'tutor-pro-google-auth',
				$plugin_data['assets'] . 'lib/google-client.js',
				array(),
				filemtime( $plugin_data['path'] . 'assets/lib/google-client.js' ),
				true
			);
		}

		// Add inline script data.
		wp_add_inline_script(
			'tutor-pro-social-authentication',
			'var tutorProSocialLogin = ' . wp_json_encode( self::inline_script_data() ),
			'before'
		);
	}

	/**
	 * Facebook lib enqueue
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function enqueue_facebook_lib() {
		// Load SDK when facebook login enabled & user not logged in.
		if ( tutor_utils()->get_option( 'enable_facebook_login' ) && ! get_current_user_id() ) {
			$plugin_data = SocialLogin::meta_data();
			wp_enqueue_script(
				'tutor-pro-fb-sdk',
				$plugin_data['assets'] . 'lib/fb.js',
				array( 'wp-i18n' ),
				$plugin_data['path'] . 'assets/lib/fb.js',
				false
			);

			// Add inline script data.
			wp_add_inline_script(
				'tutor-pro-fb-sdk',
				'var tutorProSocialLogin = ' . wp_json_encode( self::inline_script_data() ),
				'before'
			);
		}
	}

	/**
	 * Inline script data
	 *
	 * @since 2.1.9
	 *
	 * @return array
	 */
	private static function inline_script_data(): array {
		return apply_filters(
			'tutor_pro_social_login_script_data',
			array(
				'is_enabled_google_login'   => tutor_utils()->get_option( 'enable_google_login' ),
				'google_client_id'          => tutor_utils()->get_option( 'google_client_ID' ),
				'is_enabled_facebook_login' => tutor_utils()->get_option( 'enable_facebook_login' ),
				'facebook_app_id'           => tutor_utils()->get_option( 'facebook_app_ID' ),
				'page_name'                 => get_query_var( 'pagename' ),
			)
		);
	}
}
