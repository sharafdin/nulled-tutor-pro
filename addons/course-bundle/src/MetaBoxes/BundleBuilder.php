<?php
/**
 * Course Bundle Builder meta boxes
 *
 * @package TutorPro\CourseBundle\MetaBoxes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\MetaBoxes;

use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta boxes
 */
class BundleBuilder implements MetaBoxInterface {

	/**
	 * Meta box id
	 *
	 * @var string
	 */
	const META_BOX_ID = 'tutor-course-bundle-builder';

	/**
	 * Get meta box id
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_id(): string {
		return self::META_BOX_ID;
	}

	/**
	 * Get title
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Bundle Courses', 'tutor-pro' );
	}

	/**
	 * Get screen
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_screen() {
		return CourseBundle::POST_TYPE;
	}

	/**
	 * Get context
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_context(): string {
		return 'advanced';
	}

	/**
	 * Get priority
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_priority(): string {
		return 'high';
	}

	/**
	 * Get args
	 *
	 * Args to pass to the callback func
	 *
	 * @since 2.2.0
	 *
	 * @return mixed
	 */
	public function get_args() {

	}

	/**
	 * Meta box callback
	 *
	 * Render bundle builder section
	 *
	 * @return void
	 */
	public function callback() {
		$view_file = Utils::view_path( 'backend/bundle-builder.php' );
		tutor_load_template_from_custom_path( $view_file, array(), false );
	}

	/**
	 * Get course selection html
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id Bundle id.
	 *
	 * @return string
	 */
	public static function get_bundle_course_selection_html( int $bundle_id = 0 ) {
		ob_start();
		$course_selection_html = Utils::view_path( 'backend/components/bundle-course-selection.php' );

		tutor_load_template_from_custom_path( $course_selection_html, array( 'bundle_id' => $bundle_id ) );

		return apply_filters(
			'tutor_pro_course_bundle_course_selection_html',
			ob_get_clean()
		);
	}

	/**
	 * Get course bundle overview
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return string
	 */
	public static function get_bundle_overview_html( int $bundle_id = 0 ) {
		ob_start();
		$course_selection_html = Utils::view_path( 'backend/components/bundle-overview.php' );

		tutor_load_template_from_custom_path(
			$course_selection_html,
			array( 'bundle_id' => $bundle_id ),
			false
		);

		return apply_filters(
			'tutor_pro_course_bundle_overview_html',
			ob_get_clean()
		);
	}

	/**
	 * Get course bundle authors
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return string
	 */
	public static function get_bundle_authors_html( int $bundle_id = 0 ) {
		ob_start();
		$course_selection_html = Utils::view_path( 'backend/components/bundle-authors.php' );

		tutor_load_template_from_custom_path(
			$course_selection_html,
			array( 'bundle_id' => $bundle_id ),
			false
		);

		return apply_filters(
			'tutor_pro_course_bundle_authors_html',
			ob_get_clean()
		);
	}

	/**
	 * Get bundle course list
	 *
	 * @since 2.2.0
	 *
	 * @param int $bundle_id bundle id.
	 *
	 * @return string
	 */
	public static function get_bundle_course_list_html( int $bundle_id = 0 ) {
		ob_start();
		$course_selection_html = Utils::view_path( 'backend/components/bundle-course-list.php' );

		tutor_load_template_from_custom_path(
			$course_selection_html,
			array( 'bundle_id' => $bundle_id ),
			false
		);

		return apply_filters(
			'tutor_pro_bundle_course_list_html',
			ob_get_clean()
		);
	}
}
