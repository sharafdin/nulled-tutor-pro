<?php
/**
 * Initialize addon
 *
 * @package TutorPro\SocialLogin
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TutorPro\SocialLogin;

use TutorPro\SocialLogin;
use TutorPro\SocialLogin\Assets\Enqueue;
use TutorPro\SocialLogin\Authentication\Authentication;
use TutorPro\SocialLogin\Authentication\InitAuthentication;
use TutorPro\SocialLogin\Settings\Settings;
use TutorPro\SocialLogin\Utilities\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains initialization data member & methods
 */
class Init {

	/**
	 * Init props, hooks
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		add_filter( 'tutor_addons_lists_config', __CLASS__ . '::register_addon' );
		self::load_all_modules();
	}

	/**
	 * Register on the Addon list
	 *
	 * @since v2.1.0
	 *
	 * @param array $addons  available addons.
	 *
	 * @return array  addons list
	 */
	public static function register_addon( array $addons ): array {
		$new_addon = array(
			'name'        => __( 'Social Login', 'tutor-pro' ),
			'description' => __( 'Let users register & login through social network like Facebook, Google, etc.', 'tutor-pro' ),
		);

		$meta_data = SocialLogin::meta_data();
		$meta_data = array_merge( $new_addon, $meta_data );

		$addons[ $meta_data['basename'] ] = $meta_data;
		return $addons;
	}

	/**
	 * Load all modules
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	private static function load_all_modules(): void {
		if ( Utilities::is_addon_enabled() ) {
			new Settings();
			new InitAuthentication();
			new Enqueue();
			new Authentication();
		}
	}

}
