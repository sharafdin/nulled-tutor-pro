<?php
/**
 * Student Controller
 *
 * Manage API for student
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.2
 */

namespace TutorPro\RestAPI\Controllers;

use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use TUTOR_PRO_C\Tutor_Calendar;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Student Controller
 */
class StudentController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.2
	 *
	 * @var string
	 */
	public $operation = 'student';

	/**
	 * Student fillable fields
	 *
	 * @since 2.6.2
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'user_id',
		'sub_resource',
	);

	/**
	 * Required fields
	 *
	 * @since 2.6.2
	 *
	 * @var array
	 */
	private $required_fields = array(
		'user_id',
		'sub_resource',
	);

	/**
	 * Handle Get API request
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		$user_id = (int) $params['user_id'];

		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_create,
				__( 'Data retrieved failed', 'tutor-pro' ),
				__( 'User is not valid', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		$sub_resource = $params['sub_resource'];

		if ( 'dashboard' === $sub_resource ) {
			return $this->get_dashboard( $params );
		} elseif ( 'courses' === $sub_resource ) {
			return $this->get_courses( $params );
		} elseif ( 'order-histories' === $sub_resource ) {
			return $this->get_order_histories( $params );
		} elseif ( 'calendar' === $sub_resource ) {
			return $this->get_calendar( $params );
		} else {
			return $this->response(
				$this->code_create,
				__( 'Data retrieved failed', 'tutor-pro' ),
				__( 'Invalid endpoint', 'tutor-pro' ),
				$this->client_error_code
			);
		}
	}

	/**
	 * Get dashboard API request
	 *
	 * @since 2.6.2
	 *
	 * @param array $params request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_dashboard( $params ) {
		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Dashboard data retrieved failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$user_id = (int) $params['user_id'];

		try {
			$data = new \stdClass();

			$data->is_instructor = tutor_utils()->is_instructor( $user_id, true );

			$enrolled_courses  = tutor_utils()->get_enrolled_courses_by_user( $user_id );
			$completed_courses = tutor_utils()->get_completed_courses_ids_by_user( $user_id );
			$active_courses    = tutor_utils()->get_active_courses_by_user( $user_id );

			$data->enrolled_course_count  = $enrolled_courses ? $enrolled_courses->post_count : 0;
			$data->completed_course_count = count( $completed_courses );
			$data->active_course_count    = is_object( $active_courses ) && $active_courses->have_posts() ? $active_courses->post_count : 0;

			$courses_in_progress = tutor_utils()->get_active_courses_by_user( $user_id );

			$data->courses_in_progress = $courses_in_progress->get_posts();

			if ( count( $data->courses_in_progress ) > 0 ) {
				foreach ( $data->courses_in_progress as $course ) {
					$stats                               = tutor_utils()->get_course_completed_percent( $course->ID, $user_id, true );
					$course->course_completed_percentage = $stats['completed_percent'] . '%';
				}
			}

			return $this->response(
				$this->code_read,
				__( 'Dashboard data retrieved successfully', 'tutor-pro' ),
				$data
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Dashboard data retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Get student courses API request
	 *
	 * @since 2.6.2
	 *
	 * @param array $params request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_courses( $params ) {
		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Courses retrieved failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$user_id = (int) $params['user_id'];

		try {
			$data = new \stdClass();

			$enrolled_courses  = tutor_utils()->get_enrolled_courses_by_user( $user_id, array( 'private', 'publish' ) );
			$active_courses    = tutor_utils()->get_active_courses_by_user( $user_id );
			$completed_courses = tutor_utils()->get_courses_by_user( $user_id );

			$data->enrolled_courses  = is_object( $enrolled_courses ) ? $enrolled_courses->get_posts() : $enrolled_courses;
			$data->active_courses    = is_object( $active_courses ) ? $active_courses->get_posts() : $active_courses;
			$data->completed_courses = is_object( $completed_courses ) ? $completed_courses->get_posts() : $completed_courses;

			if ( count( $data->enrolled_courses ) > 0 ) {
				foreach ( $data->enrolled_courses as $course ) {
					$stats                               = tutor_utils()->get_course_completed_percent( $course->ID, $user_id, true );
					$course->course_completed_percentage = $stats['completed_percent'] . '%';
				}
			}

			return $this->response(
				$this->code_read,
				__( 'Courses retrieved successfully', 'tutor-pro' ),
				$data
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Courses retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Get student order histories API request
	 *
	 * @since 2.6.2
	 *
	 * @param array $params request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_order_histories( $params ) {
		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			return $this->validation_error_response( $validation->errors, $this->code_read );
		}

		$user_id     = (int) $params['user_id'];
		$time_period = ! empty( $params['time_period'] ) ? $params['time_period'] : '';
		$start_date  = ! empty( $params['start_date'] ) ? $params['start_date'] : '';
		$end_date    = ! empty( $params['end_date'] ) ? $params['end_date'] : '';
		$offset      = ! empty( $params['offset'] ) ? (int) $params['offset'] : 0;
		$per_page    = ! empty( $params['per_page'] ) ? (int) $params['per_page'] : 10;

		try {
			$order_histories = tutor_utils()->get_orders_by_user_id( $user_id, $time_period, $start_date, $end_date, $offset, $per_page );

			foreach ( $order_histories as &$order ) {
				$courses        = tutor_utils()->get_course_enrolled_ids_by_order_id( $order->ID );
				$order->courses = array();
				foreach ( $courses as $course ) {
					$order->courses[] = get_the_title( $course['course_id'] );
				}
			}

			return $this->response(
				$this->code_read,
				__( 'Order History retrieved successfully', 'tutor-pro' ),
				$order_histories
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Order History retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Get student calendar API request
	 *
	 * @since 2.6.2
	 *
	 * @param array $params request array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_calendar( $params ) {
		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Courses retrieved failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$user_id = (int) $params['user_id'];
		$month   = ! empty( $params['month'] ) ? $params['month'] : wp_date( 'm' );
		$year    = ! empty( $params['year'] ) ? $params['year'] : wp_date( 'Y' );

		try {
			$calendar = new Tutor_Calendar( false );
			$data     = $calendar->get_calendar_materials( $month, $year, $user_id );

			return $this->response(
				$this->code_read,
				__( 'Calendar data retrieved successfully', 'tutor-pro' ),
				$data
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Calendar data retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Request for tutor student to become instructor
	 *
	 * @since 2.7.0
	 * @param WP_Rest_Request $request the params passed with request endpoint.
	 * @return WP_Rest_Response|WP_Error
	 */
	public function apply_for_instructor( $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, array( 'user_id' ) );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$this->validation_error_response( $validation->errors, $this->code_update );
		}

		$user_id = (int) $params['user_id'];
		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_create,
				__( 'User is not valid', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		$is_instructor = tutor_utils()->is_instructor( $user_id, true );

		if ( $is_instructor ) {
			return $this->response(
				$this->code_update,
				__( 'User is already an instructor', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}
		$instructor_status = tutor_utils()->instructor_status( $user_id );
		if ( 'Pending' === $instructor_status ) {
			return $this->response(
				$this->code_update,
				__( 'Already applied for instructor', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		if ( 'Blocked' === $instructor_status ) {
			return $this->response(
				$this->code_update,
				__( 'Instructor approval is blocked for this user', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		update_user_meta( $user_id, '_is_tutor_instructor', tutor_time() );
		update_user_meta( $user_id, '_tutor_instructor_status', apply_filters( 'tutor_initial_instructor_status', 'pending' ) );

		do_action( 'tutor_new_instructor_after', $user_id );

		return $this->response(
			$this->code_update,
			__( 'Applied for instructor successfully. Please wait for review', 'tutor-pro' )
		);
	}

	/**
	 * Validate data
	 *
	 * @since 2.6.2
	 *
	 * @param array $data form data.
	 *
	 * @return object
	 */
	protected function validate( array $data ): object {
		$validation_rules = array(
			'user_id'      => 'required|numeric',
			'sub_resource' => 'required',
			'offset'       => 'numeric',
			'per_page'     => 'numeric',
			'month'        => 'numeric',
			'year'         => 'numeric',
		);

		// Skip validation rules for not available fields in data.
		foreach ( $validation_rules as $key => $value ) {
			if ( ! array_key_exists( $key, $data ) ) {
				unset( $validation_rules[ $key ] );
			}
		}

		return ValidationHelper::validate( $validation_rules, $data );
	}
}
