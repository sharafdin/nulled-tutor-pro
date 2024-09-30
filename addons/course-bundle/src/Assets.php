<?php
/**
 * Manage Assets.
 *
 * @package TutorPro\CourseBundle
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle;

use TUTOR\Input;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;

/**
 * Assets Class.
 *
 * @since 2.2.0
 */
class Assets {
	/**
	 * Register hooks.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_backend_assets' ) );

		// Common assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'load_common_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_common_assets' ) );
	}

	/**
	 * Load assets for frontend.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function load_frontend_assets() {
		wp_enqueue_style( 'tutor-course-bundle-frontend', Utils::asset_url( 'css/frontend.css' ), array(), TUTOR_VERSION );
	}

	/**
	 * Load assets for backend.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function load_backend_assets() {
		if ( is_admin() && ( CourseBundle::POST_TYPE === Input::get( 'page', '' ) || CourseBundle::POST_TYPE === Input::get( 'post_type', '' ) || CourseBundle::POST_TYPE === get_post_type( get_the_ID() ) ) ) {
			wp_enqueue_style( 'tutor-course-bundle-backend', Utils::asset_url( 'css/backend.css' ), array(), TUTOR_VERSION );
			wp_enqueue_script( 'tutor-course-bundle-backend', Utils::asset_url( 'js/backend.js' ), array( 'jquery' ), TUTOR_VERSION, true );
		}
	}

	/**
	 * Load common assets.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function load_common_assets() {
		wp_enqueue_style( 'tutor-course-bundle-common', Utils::asset_url( 'css/common.css' ), array(), TUTOR_VERSION );
		wp_enqueue_script( 'tutor-course-bundle-common', Utils::asset_url( 'js/common.js' ), array( 'jquery' ), TUTOR_VERSION, true );

		wp_add_inline_script(
			'tutor-course-bundle-common',
			'var tutorProCourseBundle = ' . wp_json_encode( self::inline_script_data() ),
			'high'
		);
	}

	/**
	 * Inline script data to use in js
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	public static function inline_script_data(): array {
		$is_bundle_editor = false;

		// For frontend bundle editor.
		if ( tutils()->is_tutor_frontend_dashboard() && Input::get( 'bundle-id', 0, Input::TYPE_INT ) ) {
			$is_bundle_editor = true;
		}

		// For backend bundle editor.
		if ( is_admin() && ( CourseBundle::POST_TYPE === Input::get( 'post_type', '' ) || CourseBundle::POST_TYPE === get_post_type( get_the_ID() ) ) ) {
				$is_bundle_editor = true;
		}

		$data = array(
			'is_course_bundle_editor'     => $is_bundle_editor,
			'course_bundle_list_page_url' => admin_url( 'admin.php?page=' . CourseBundle::POST_TYPE ),
			'course_bundle_post_type'     => CourseBundle::POST_TYPE,
		);

		return apply_filters( 'tutor_pro_course_bundle_inline_data', $data );
	}


}
