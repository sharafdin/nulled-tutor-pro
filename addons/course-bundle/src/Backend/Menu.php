<?php
/**
 * Manage Course Bundle admin sub menu
 *
 * @package TutorPro\CourseBundle\Backend\Menu
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Backend;

use TutorPro\CourseBundle\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Class
 *
 * @since 2.2.0
 */
class Menu {

	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_after_courses_menu', __CLASS__ . '::register_submenu' );
	}

	/**
	 * Register submenu
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function register_submenu() {
		add_submenu_page(
			'tutor',
			__( 'Course Bundles', 'tutor' ),
			__( 'Course Bundles', 'tutor' ),
			'manage_tutor_instructor',
			'course-bundle',
			__CLASS__ . '::bundle_list_page',
			null
		);

		do_action( 'tutor_pro_after_course_bundle_submenu' );
	}

	/**
	 * Bundle List
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function bundle_list_page() {
		include Utils::view_path( 'backend/bundle-list.php' );
	}
}
