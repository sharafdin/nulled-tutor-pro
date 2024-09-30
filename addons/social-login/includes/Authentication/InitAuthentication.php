<?php
/**
 * Initialize authentication module
 *
 * @package TutorPro\SocialLogin\Authentication
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\SocialLogin\Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Init authentication modules
 */
class InitAuthentication {

	/**
	 * Load dependencies
	 *
	 * @since 2.1.9
	 */
	public function __construct() {
		new Placeholder();
	}
}
