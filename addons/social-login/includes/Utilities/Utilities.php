<?php
/**
 * Utility helper methods
 *
 * @package TutorPro\SocialLogin\Utilities
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\SocialLogin\Utilities;

use TutorPro\SocialLogin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provide static helper methods
 */
class Utilities {

	/**
	 * Check if addon is enabled, if not enabled then
	 * return
	 *
	 * @since v2.1.0
	 *
	 * @return bool
	 */
	public static function is_addon_enabled() {
		$plugin_data = SocialLogin::meta_data();
		return tutor_utils()->is_addon_enabled( $plugin_data['basename'] );
	}

	/**
	 * Check if current user is privileged
	 *
	 * Check If user is administrator or tutor instructor
	 *
	 * @since 2.1.9
	 *
	 * @return bool
	 */
	public static function current_user_has_access() {
		return \current_user_can( 'administrator' ) || \current_user_can( tutor()->instructor_role );
	}
}
