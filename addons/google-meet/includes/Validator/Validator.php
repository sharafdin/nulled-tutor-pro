<?php
/**
 * Check addon is enabled or not
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Validator
 */

namespace TutorPro\GoogleMeet\Validator;

use TutorPro\GoogleMeet\GoogleMeet;

/**
 * Manage security & validation
 */
class Validator {

	/**
	 * Check if addon is enabled, if not enabled then
	 * return
	 *
	 * @since v2.1.0
	 *
	 * @return bool
	 */
	public static function is_addon_enabled() {
		$plugin_data = GoogleMeet::meta_data();
		return tutor_utils()->is_addon_enabled( $plugin_data['basename'] );
	}

	/**
	 * Check if current user can access google meet
	 *
	 * Check If user is administrator or tutor instructor
	 *
	 * @since v2.1.0
	 *
	 * @return bool
	 */
	public static function current_user_has_access() {
		return \current_user_can( 'administrator' ) || \current_user_can( tutor()->instructor_role );
	}
	
}
