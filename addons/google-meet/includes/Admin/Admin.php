<?php
/**
 * Manage admin dependency
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet
 */

namespace TutorPro\GoogleMeet\Admin;

use TutorPro\GoogleMeet\Validator\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage admin dependency
 */
class Admin {

	/**
	 * Load dependent packages
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		if ( Validator::is_addon_enabled() ) {
			new SubMenu();
		}
	}
}
