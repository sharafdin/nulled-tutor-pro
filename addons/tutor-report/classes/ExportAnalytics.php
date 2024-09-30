<?php
/**
 * Handle analytics exports logic & queries
 *
 * @package TutorPro\Addons
 * @subpackage Report
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.9.9
 */

namespace TUTOR_REPORT;

defined( 'ABSPATH' ) || exit;
/**
 * Class ExportAnalytics
 *
 * @since 1.9.9
 */
class ExportAnalytics {

	/**
	 * Student data
	 *
	 * @since 1.9.9
	 *
	 * @return mixed
	 */
	protected function students_data() {
		global $wpdb;
		$instructor_id = get_current_user_id();
		$students      = $wpdb->get_results(
			$wpdb->prepare(
				" SELECT DATE_FORMAT(users.user_registered, '%%d %%b %%Y %%T') AS register_date, 
                users.ID, users.display_name,
                users.user_email,
                course.post_title AS enrolled_course, 
                course.ID as course_id
                FROM {$wpdb->posts} AS enrollment
                INNER JOIN {$wpdb->posts} AS course
                    ON enrollment.post_parent=course.ID
                INNER JOIN {$wpdb->users} AS users 
                    ON users.ID = enrollment.post_author
                AND course.post_type = %s
                AND course.post_status = %s
                AND enrollment.post_type = %s
                AND enrollment.post_status = %s
                AND course.post_author = %d
                ORDER BY users.ID DESC
            ",
				tutor()->course_post_type,
				'publish',
				'tutor_enrolled',
				'completed',
				$instructor_id
			)
		);

		$data             = array();
		$course_ids       = array_column( $students, 'course_id' );
		$course_meta_data = tutor_utils()->get_course_meta_data( $course_ids );

		foreach ( $students as $student ) {
			$student_id      = $student->ID;
			$course_id       = (int) $student->course_id;
			$completed_count = tutor_utils()->get_course_completed_percent( $course_id, $student_id );

			$total_lessons = isset( $course_meta_data[ $course_id ] ) ? $course_meta_data[ $course_id ]['lesson'] : 0;

			$completed_lessons = tutor_utils()->get_completed_lesson_count_by_course( $course_id, $student_id );

			$total_assignments    = isset( $course_meta_data[ $course_id ] ) ? $course_meta_data[ $course_id ]['tutor_assignments'] : 0;
			$completed_assignment = tutor_utils()->get_completed_assignment( $course_id, $student_id );

			$total_quiz     = isset( $course_meta_data[ $course_id ] ) ? $course_meta_data[ $course_id ]['tutor_quiz'] : 0;
			$completed_quiz = tutor_utils()->get_completed_quiz( $course_id, $student_id );

			$array = array(
				'register_date'   => $student->register_date,
				'display_name'    => $student->display_name,
				'email'           => $student->user_email,
				'enrolled_course' => $student->enrolled_course,
				'course_progress' => $completed_count . '%',
				'lesson'          => $completed_lessons . '/' . $total_lessons,
				'assignment'      => $completed_assignment . '/' . $total_assignments,
				'quiz'            => $completed_quiz . '/' . $total_quiz,
			);
			array_push( $data, $array );
		}
		return $data;
	}

	/**
	 * Earnings data.
	 *
	 * @since 1.9.9
	 *
	 * @return mixed
	 */
	protected function earnings_data() {
		global $wpdb;
		$instructor_id   = get_current_user_id();
		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status ) . "'";
		//phpcs:disable
        $earnings        = $wpdb->get_results(
			$wpdb->prepare(
				" SELECT  user.display_name AS instructor_name, user.user_email AS instructor_email, course.post_title AS course_title, earning.instructor_amount, DATE_FORMAT(earning.created_at, '%%d %%b %%Y %%T') AS created_at
                FROM {$wpdb->prefix}tutor_earnings AS earning

                INNER JOIN {$wpdb->posts} AS course 
                    ON course.ID = earning.course_id
                INNER JOIN {$wpdb->users} AS user 
                    ON user.ID = earning.user_id
                WHERE 	earning.user_id = %d 
                    AND order_status IN({$complete_status}) 
                ORDER BY created_at ASC
            ",
				$instructor_id
			)
		);
        //phpcs:enable
		return $earnings;
	}

	/**
	 * Discount data.
	 *
	 * @since 1.9.9
	 *
	 * @return mixed
	 */
	protected function discounts_data() {
		global $wpdb;
		$instructor_id    = get_current_user_id();
		$complete_status  = tutor_utils()->get_earnings_completed_statuses();
		$complete_status  = "'" . implode( "','", $complete_status ) . "'";
		$course_post_type = tutor()->course_post_type;
		$discounts        = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT w_order.coupon_amount, DATE_FORMAT(order_details.date_created, '%%d %%b %%Y %%T') AS created_at 
				FROM {$wpdb->posts} AS post
					INNER JOIN {$wpdb->postmeta} as mt1 ON mt1.post_id = post.ID
					INNER JOIN {$wpdb->prefix}wc_order_product_lookup AS w_order 
                        ON w_order.product_id = mt1.meta_value AND w_order.coupon_amount > 0
					INNER JOIN {$wpdb->prefix}wc_order_stats AS order_details 
                        ON order_details.order_id = w_order.order_id
				WHERE post.post_author = %d
					AND mt1.meta_key = %s
					AND post.post_type = %s
					AND post.post_status = %s
					AND order_details.status = %s
            ",
				$instructor_id,
				'_tutor_course_product_id',
				$course_post_type,
				'publish',
				'wc-completed'
			)
		);
		return $discounts;
	}

	/**
	 * Refund data.
	 *
	 * @since 1.9.9
	 *
	 * @return mixed
	 */
	protected function refunds_data() {
		global $wpdb;
		$instructor_id    = get_current_user_id();
		$complete_status  = tutor_utils()->get_earnings_completed_statuses();
		$complete_status  = "'" . implode( "','", $complete_status ) . "'";
		$course_post_type = tutor()->course_post_type;
		$refunds          = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(order_details.date_created, '%%d %%b %%Y %%T') AS order_date, order_details.total_sales AS refund,  post.post_title AS course_title
				FROM {$wpdb->posts} AS post
					INNER JOIN {$wpdb->postmeta} as mt1 ON mt1.post_id = post.ID
					INNER JOIN {$wpdb->prefix}wc_order_product_lookup AS w_order ON w_order.product_id = mt1.meta_value
					INNER JOIN {$wpdb->prefix}wc_order_stats AS order_details ON order_details.order_id = w_order.order_id
				WHERE post.post_author = %d
					AND mt1.meta_key = %s
					AND post.post_type = %s
					AND post.post_status = %s
					AND order_details.status = %s
            ",
				$instructor_id,
				'_tutor_course_product_id',
				$course_post_type,
				'publish',
				'wc-refunded'
			)
		);
		return $refunds;
	}
}
