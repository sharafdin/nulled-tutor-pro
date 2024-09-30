<?php
/**
 * Manage Frontend Menu
 *
 * @package TutorPro\CourseBundle
 * @subpackage Frontend
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DashboardMenu Class
 *
 * @since 2.2.0
 */
class DashboardMenu {
	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_after_instructor_menu_my_courses', array( $this, 'add_menu_after_my_courses' ) );
	}

	/**
	 * Add bundle menu to dashboard after my courses.
	 *
	 * @since 2.2.0
	 *
	 * @param array $menus nav items.
	 *
	 * @return array
	 */
	public function add_menu_after_my_courses( array $menus ) {
		$menus['my-bundles'] = array(
			'title'    => __( 'My Bundles', 'tutor-pro' ),
			'auth_cap' => tutor()->instructor_role,
			'icon'     => 'tutor-icon-layer',
		);

		return $menus;
	}
}
