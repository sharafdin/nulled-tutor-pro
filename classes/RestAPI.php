<?php
/**
 * Initialize Rest API
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.0
 */

namespace TUTOR_PRO;

use TutorPro\RestAPI\Routes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rest API Init
 */
class RestAPI {

	/**
	 * Init Rest API
	 *
	 * @since 2.6.0
	 */
	public function __construct() {
		require_once tutor_pro()->path . '/vendor/autoload.php';
		new Routes();
	}
}
