<?php
/**
 * Dashboard class
 *
 * @author: themeum
 * @link: https://themeum.com
 * @package TutorPro
 * @since 2.1.0
 */

namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use Tutor\Models\CourseModel;

/**
 * Dashboard Class
 * Used for handle frontend dashboard for PRO user
 *
 * @since 2.1.0
 */
class Dashboard {
	/**
	 * Constructor
	 *
	 * @param boolean $register_hooks pass false value to reuse.
	 */
	public function __construct( $register_hooks = true ) {
		if ( $register_hooks ) {
			add_action( 'wp_loaded', array( $this, 'handle_course_status' ) );
		}

	}

	/**
	 * Handler for change course status
	 * 
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function handle_course_status() {
		$action = Input::get( 'tutor_action' );
		$id     = Input::get( 'course_id', 0, Input::TYPE_INT );
		$status = Input::get( 'status' );

		if ( 'update_course_status' !== $action
			|| 0 === $id
			|| ! in_array( $status, array( CourseModel::STATUS_DRAFT, CourseModel::STATUS_PENDING ), true )
			|| ! get_post( $id )
			) {
			// Invalid request for status update.
			return;
		}

		tutor_utils()->checking_nonce( 'GET' );

		if ( ! tutor_utils()->can_user_manage( 'course', $id ) ) {
			return;
		}

		$can_publish_course = current_user_can( 'administrator' ) || (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );

		if ( $can_publish_course === true && CourseModel::STATUS_PENDING === $status ) {
			$status = CourseModel::STATUS_PUBLISH;
		}

		$args = array(
			'ID'          => $id,
			'post_status' => $status,
		);

		wp_update_post( $args );

		$link          = wp_get_referer();
		$flash_message = null;
		
		if ( CourseModel::STATUS_PUBLISH === $status ) {
			$flash_message = __( 'Course successfully published', 'tutor-pro' );
			$link          = tutor_utils()->tutor_dashboard_url( 'my-courses' );
		}

		if ( CourseModel::STATUS_PENDING === $status ) {
			$flash_message = __( 'Course submitted for review', 'tutor-pro' );
			$link          = tutor_utils()->tutor_dashboard_url( 'my-courses/pending-courses' );
		}

		if ( CourseModel::STATUS_DRAFT === $status ) {
			$flash_message = __( 'Course moved to draft', 'tutor-pro' );
			$link          = tutor_utils()->tutor_dashboard_url( 'my-courses/draft-courses' );
		}

		tutor_utils()->redirect_to( $link, $flash_message );
	}
}
