<?php
/**
 * Initialize all custom post types
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\CustomPosts
 */

namespace TutorPro\GoogleMeet\CustomPosts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load dependencies
 */
class InitPostTypes {

	/**
	 * Init posts
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		new TutorGoogleMeet();
	}
}

