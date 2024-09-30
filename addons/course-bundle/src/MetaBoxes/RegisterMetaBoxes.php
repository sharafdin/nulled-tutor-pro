<?php
/**
 * Register meta boxes
 *
 * @package TutorPro\CourseBundle\MetaBoxes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle\MetaBoxes;

use TutorPro\CourseBundle\CustomPosts\CourseBundle;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta boxes
 */
class RegisterMetaBoxes {

	/**
	 * Register hooks
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', __CLASS__ . '::register' );
	}

	/**
	 * Register meta box
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public static function register() {

		// Create instance of meta boxes.
		$meta_boxes = array(
			new BundleBuilder(),
			new BundlePrice(),
			new BundleAdditionalData(),
		);

		foreach ( $meta_boxes as $meta_box ) {
			tutor_meta_box_wrapper(
				$meta_box->get_id(),
				$meta_box->get_title(),
				array( $meta_box, 'callback' ),
				$meta_box->get_screen(),
				$meta_box->get_context(),
				$meta_box->get_priority(),
			);
		}

		// TODO Certificate will be used later on.
		//add_filter( 'tutor_certificate_template_post_type', __CLASS__ . '::register_certificate_meta_box' );

	}

	/**
	 * Register certificate meta box
	 *
	 * @since 2.2.0
	 *
	 * @param string $post_type post type.
	 *
	 * @return string
	 */
	public static function register_certificate_meta_box( $post_type ) {
		$current_post_type = get_post_type();

		if ( CourseBundle::POST_TYPE === $current_post_type ) {
			$post_type = CourseBundle::POST_TYPE;
		}
		return $post_type;
	}
}
