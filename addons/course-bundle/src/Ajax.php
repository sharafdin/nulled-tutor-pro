<?php
/**
 * Handle Ajax Request.
 *
 * @package TutorPro\CourseBundle
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

namespace TutorPro\CourseBundle;

use TUTOR\Input;
use Tutor\Models\CourseModel;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Frontend\BundleBuilder as FrontendBundleBuilder;
use TutorPro\CourseBundle\MetaBoxes\BundleBuilder;
use TutorPro\CourseBundle\MetaBoxes\BundlePrice;
use TutorPro\CourseBundle\Models\BundleModel;

/**
 * Ajax Class.
 *
 * @since 2.2.0
 */
class Ajax {
	/**
	 * Register hooks.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_tutor_get_course_bundle_data', __CLASS__ . '::get_course_bundle_data' );
		add_action( 'wp_ajax_tutor_add_new_draft_bundle', __CLASS__ . '::add_new_draft_bundle' );
	}

	/**
	 * Get course bundle data
	 *
	 * All the course bundle related data will be returned.
	 *
	 * @since 2.2.0
	 *
	 * @return void send wp_json response
	 */
	public static function get_course_bundle_data() {
		// Validate nonce.
		tutor_utils()->checking_nonce();

		// Check user permission.
		if ( ! current_user_can( 'administrator' ) && ! current_user_can( tutor()->instructor_role ) ) {
			$res = array(
				'error_title' => __( 'Unauthorized', 'tutor-pro' ),
				'error_msg'   => tutor_utils()->error_message(),
			);
			wp_send_json_error( $res );
		}

		// Post data.
		$bundle_id   = Input::post( 'bundle_id', 0, Input::TYPE_INT );
		$course_id   = Input::post( 'course_id', 0, Input::TYPE_INT );
		$user_action = Input::post( 'user_action', '', Input::TYPE_STRING );

		if ( ! $bundle_id || CourseBundle::POST_TYPE !== get_post_type( $bundle_id ) ) {
			$res = array(
				'error_title' => __( 'Something went wrong', 'tutor-pro' ),
				'error_msg'   => __( 'Invalid  Course Bundle ID.', 'tutor-pro' ),
			);

			wp_send_json_error( $res );
		}

		// Course id  to add on the bundle.
		if ( $course_id ) {

			// Remove course from bundle if user action is remove.
			if ( 'remove_course' === $user_action ) {
				BundleModel::remove_course_from_bundle( $course_id, $bundle_id );
			} else {
				$course_ids = BundleModel::get_bundle_course_ids( $bundle_id );
				if ( ! in_array( $course_id, $course_ids ) ) {
					// Add course to the bundle.
					$course_ids[] = $course_id;
					$update       = BundleModel::update_bundle_course_ids( $bundle_id, $course_ids );

					// If bundle course update failed.
					if ( ! $update ) {
						$res = array(
							'error_title' => __( 'Course add failed', 'tutor-pro' ),
							'error_msg'   => __( 'Course could not added to the bundle.', 'tutor-pro' ),
						);
						wp_send_json_error( $res );
					}

					// Do action.
					do_action( 'tutor_course_bundle_course_added', $bundle_id, $course_id );
				} else {
					$res = array(
						'error_title' => __( 'Course already added', 'tutor-pro' ),
						'error_msg'   => __( 'Course already added to the bundle.', 'tutor-pro' ),
					);
					wp_send_json_error( $res );
				}
			}
		}

		$course_ids = BundleModel::get_bundle_course_ids( $bundle_id );

		// Get course bundle sidebar and content.
		$overview_html = count( $course_ids ) ? BundleBuilder::get_bundle_overview_html( $bundle_id ) : '';
		$authors_html  = count( $course_ids ) ? BundleBuilder::get_bundle_authors_html( $bundle_id ) : '';

		$course_list_html = BundleBuilder::get_bundle_course_list_html( $bundle_id );

		$subtotal_price = count( $course_ids ) ? BundlePrice::get_bundle_regular_price( $bundle_id ) : 0;

		$data = array(
			'overview'           => $overview_html,
			'authors'            => $authors_html,
			'course_list'        => $course_list_html,
			'subtotal_price'     => tutor_utils()->tutor_price( $subtotal_price ),
			'subtotal_raw_price' => $subtotal_price,
			'course_ids'         => $course_ids,
		);

		wp_send_json_success( $data );
	}

	/**
	 * Add new draft bundle.
	 *
	 * @since 2.2.0
	 *
	 * @return void send wp_json response
	 */
	public static function add_new_draft_bundle() {
		tutor_utils()->checking_nonce();

		$can_publish_course = (bool) current_user_can( 'tutor_instructor' ) || current_user_can( 'administrator' );

		if ( $can_publish_course ) {
			$post_type = CourseBundle::POST_TYPE;
			$bundle_id = wp_insert_post(
				array(
					'post_title'  => __( 'New bundle', 'tutor-pro' ),
					'post_type'   => $post_type,
					'post_status' => 'draft',
					'post_name'   => 'new-bundle',
				)
			);
			if ( $bundle_id ) {
				$url = admin_url() . 'post.php?post=' . $bundle_id . '&action=edit';
				if ( 'frontend' === Input::post( 'source' ) ) {
					$url = FrontendBundleBuilder::get_edit_link( $bundle_id );
				}

				$response = array(
					'bundle_id' => $bundle_id,
					'url'       => $url,
				);
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( __( 'Bundle creation failed, please try again.', 'tutor-pro' ) );
			}
		} else {
			wp_send_json_error( tutor_utils()->error_message() );
		}

	}
}
