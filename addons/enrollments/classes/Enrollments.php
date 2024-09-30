<?php
/**
 * Enrollments class
 *
 * @author: themeum
 * @link https://themeum.com
 * @package TutorPro\Addons
 * @subpackage Enrollments
 * @since 1.4.0
 */

namespace TUTOR_ENROLLMENTS;

use Tutor\Helpers\QueryHelper;
use TUTOR\Input;
use TUTOR\User;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;
use TutorPro\CourseBundle\Models\BundleModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enrollments Class
 *
 * @since 2.0.6
 */
class Enrollments {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_admin_register', array( $this, 'register_menu' ) );

		add_action( 'wp_ajax_tutor_json_search_students', array( $this, 'tutor_json_search_students' ) );
		add_action( 'tutor_action_enrol_student', array( $this, 'enrol_student' ) );
		add_action( 'wp_ajax_tutor_search_students', array( $this, 'tutor_search_students' ) );
		add_action( 'wp_ajax_tutor_enroll_bulk_student', array( $this, 'tutor_enroll_bulk_student' ) );
	}

	/**
	 * Register Enrollment Menu
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_menu() {
		add_submenu_page( 'tutor', __( 'Enrollment', 'tutor-pro' ), __( 'Enrollment', 'tutor-pro' ), 'manage_tutor', 'enrollments', array( $this, 'enrollments' ) );
	}

	/**
	 * Manual Enrollment Page
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function enrollments() {
		include TUTOR_ENROLLMENTS()->path . 'views/enrollments.php';
	}

	/**
	 * Student advance search
	 *
	 * @since 2.0.6
	 *
	 * @return void JSON response
	 */
	public function tutor_search_students() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		global $wpdb;

		$term        = Input::post( 'term', '' );
		$course_id   = Input::post( 'course_id', 0, Input::TYPE_INT );
		$term        = '%' . $term . '%';
		$shortlisted = Input::post( 'shortlisted', array(), Input::TYPE_ARRAY );

		// Validate each shortlisted item is numeric value.
		$shortlisted = array_filter(
			$shortlisted,
			function( $id ) {
				return is_numeric( $id );
			}
		);

		if ( Input::has( 'term' ) ) {
			$sql = "SELECT * FROM {$wpdb->users} WHERE ( display_name LIKE %s OR user_email LIKE %s )";

			$enrolled_student_ids = tutor_utils()->get_students_data_by_course_id( $course_id, 'ID' );
			$exclude_arr          = array_merge( $enrolled_student_ids, $shortlisted );

			if ( count( $exclude_arr ) ) {
				$excluded_ids = QueryHelper::prepare_in_clause( $exclude_arr );
				$sql         .= " AND ID NOT IN ({$excluded_ids})";
			}

			$sql .= ' LIMIT 6';

			$student_res = $wpdb->get_results( $wpdb->prepare( $sql, $term, $term ) ); //phpcs:ignore
			$students    = array();

			if ( tutor_utils()->count( $student_res ) ) {
				foreach ( $student_res as $student ) {
					$students[] = array(
						'id'    => $student->ID,
						'name'  => $student->display_name,
						'email' => $student->user_email,
						'photo' => get_avatar_url( $student->ID ),
					);
				}
			}

			$search_result = '';
			if ( is_array( $student_res ) && count( $student_res ) ) {
				foreach ( $student_res as $row ) {
					ob_start();
					include TUTOR_ENROLLMENTS()->path . '/views/search-result-item.php';
					$search_result .= ob_get_clean();
				}
			} else {
				$search_result .= '<div class="tutor-text-center tutor-mb-20"> <span>' . __( 'No student found!', 'tutor' ) . '</span> </div>';
			}

			wp_send_json(
				array(
					'success' => true,
					'data'    => $search_result,
				)
			);
		}

		if ( Input::has( 'selection' ) ) {
			$shortlisted_html = '';
			foreach ( $shortlisted as $id ) {
				$user = get_userdata( $id );
				ob_start();
				include TUTOR_ENROLLMENTS()->path . '/views/search-selected-item.php';
				$shortlisted_html .= ob_get_clean();
			}

			$shortlisted_html = '<div class="tutor-fs-6 tutor-fw-medium tutor-color-secondary tutor-mb-16">' . __( 'Selected Student', 'tutor-pro' ) . '</div>
								<div class="tutor-d-flex tutor-flex-wrap tutor-gap-2">' . $shortlisted_html . '</div>';
			wp_send_json(
				array(
					'success' => true,
					'data'    => $shortlisted_html,
				)
			);
		}

	}

	/**
	 * Enroll multiple student by course ID
	 *
	 * @return void
	 *
	 * @since 2.0.6
	 */
	public function tutor_enroll_bulk_student() {

		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		// Comma separated string id.
		$student_ids = Input::post( 'student_ids' );
		$student_ids = array_filter(
			explode( ',', $student_ids ),
			function( $id ) {
				return is_numeric( $id );
			}
		);

		/**
		 * This can be course/bundle_id
		 *
		 * @var int $selected_id
		 */
		$selected_id = Input::post( 'course_id', 0, Input::TYPE_INT );
		$post        = get_post( $selected_id );

		// Check all selected student are not enrolled before.
		$flag = false;
		foreach ( $student_ids as $student_id ) {
			$is_enrolled = tutor_utils()->is_enrolled( $selected_id, $student_id );
			if ( false !== $is_enrolled ) {
				$flag = $student_id;
				break;
			}
		}

		if ( false !== $flag ) {
			$type = 'course';
			if ( CourseBundle::POST_TYPE === $post->post_type ) {
				$type = 'bundle';
			}

			$student = get_userdata( $student_id );
			wp_send_json(
				array(
					'success' => false,
					'message' => __(
						"{$student->display_name} is already enrolled for selected {$type}", //phpcs:ignore
						'tutor-pro'
					),
				)
			);
		}

		$is_paid_course   = tutor_utils()->is_course_purchasable( $selected_id );
		$monetize_by      = tutor_utils()->get_option( 'monetize_by' );
		$generate_invoice = tutor_utils()->get_option( 'tutor_woocommerce_invoice' );

		// Now enroll each student for selected course/bundle.
		foreach ( $student_ids as $student_id ) {
			$order_id = 0;
			/**
			 * Check generate invoice settings along with monetize by
			 *
			 * @since 2.1.4
			 */
			if ( $is_paid_course && 'wc' === $monetize_by && $generate_invoice ) {
				// Make an manual order for student with this course.
				$product_id = tutor_utils()->get_course_product_id( $selected_id );
				$order      = wc_create_order();

				$order->add_product( wc_get_product( $product_id ), 1 );
				$order->set_customer_id( $student_id );
				$order->calculate_totals();
				$order->update_status( 'Pending payment', __( 'Manual Enrollment Order', 'tutor-pro' ), true );

				$order_id = $order->get_id();

				/**
				 * Set transient for showing modal in view enrollment-success-modal.php
				 */
				set_transient( 'tutor_manual_enrollment_success', $post );
			}

			/**
			 * If user disable generate invoice from tutor settings these will be happen.
			 * 1. Paid course enrollment will automatically completed without generate a WC order.
			 * 2. Earning data will not reflect on report.
			 *
			 * @since 2.1.4
			 */
			if ( ! $generate_invoice && $is_paid_course && 'wc' === $monetize_by ) {
				add_filter(
					'tutor_enroll_data',
					function( $data ) {
						$data['post_status'] = 'completed';
						return $data;
					}
				);
			}

			// Enroll to course/bundle.
			tutor_utils()->do_enroll( $selected_id, $order_id, $student_id );

			/**
			 * Enrol to bundle courses when WC order create disabled from tutor settings.
			 *
			 * @since 2.2.2
			 */
			if ( CourseBundle::POST_TYPE === $post->post_type && ! $generate_invoice && $is_paid_course && 'wc' === $monetize_by ) {
				BundleModel::enroll_to_bundle_courses( $selected_id, $student_id );
			}
		}

		wp_send_json(
			array(
				'success' => true,
				'message' => __(
					'Enrollment done for selected students',
					'tutor-pro'
				),
			)
		);

	}

}
