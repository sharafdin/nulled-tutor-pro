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
class BundleAdditionalData implements MetaBoxInterface {

	/**
	 * Meta box id
	 *
	 * @var string
	 */
	const META_BOX_ID = 'tutor-course-bundle-additional-data';

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
		return __( 'Additional Data', 'tutor-pro' );
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
		return 'default';
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
	 * Render meta box view
	 *
	 * @return void
	 */
	public function callback() {
		$view_file = Utils::view_path( 'backend/bundle-additional-data.php' );
		tutor_load_template_from_custom_path( $view_file, array(), false );
	}

}
