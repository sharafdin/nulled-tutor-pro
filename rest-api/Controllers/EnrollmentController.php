<?php
/**
 * Enrollment Controller
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.0
 */

namespace TutorPro\RestAPI\Controllers;

use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use WP_Query;
use WP_REST_Request;

/**
 * Enrollment Controller
 *
 * Manage course enrollments
 */
class EnrollmentController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $operation = 'enrollment';

	/**
	 * Fillable fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'course_id',
		'user_id',
		'status',
		'enrollment_id',
	);

	/**
	 * Required fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $required_fields = array(
		'course_id',
		'user_id',
	);

	/**
	 * Allowed enrollment status
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $enrollment_status = array(
		'cancel',
		'completed',
		'expired',
		'failed',
		'on-hold',
		'pending',
		'processing',
		'refunded',
	);


	/**
	 * Handle new enrollment
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function do_enrollment( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			return $this->response(
				$this->code_create,
				__( 'Enrollment create failed', 'tutor-pro' ),
				$validation->errors,
				$this->client_error_code
			);
		}

		try {
			$enroll = tutor_utils()->do_enroll( $params['course_id'], 0, $params['user_id'] );
			if ( $enroll ) {
				return $this->response(
					$this->code_create,
					__( 'User enrolled successfully', 'tutor-pro' ),
					array(
						'enrollment_id' => $enroll,
					)
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Enrollment failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Get enrollments for a course
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request params array.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_enrollment_list( WP_REST_Request $request ) {

		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Setup required fields.
		$this->setup_required_fields( $params, array( 'course_id' ) );

		$validation = $this->validate( $params );

		if ( ! $validation->success ) {

			return $this->validation_error_response( $validation->errors, $this->code_read );
		}

		try {
			$course_id   = $params['course_id'];
			$enrollments = tutor_utils()->get_enrolments( 'approved', 0, 10, '', $course_id );

			if ( is_array( $enrollments ) && count( $enrollments ) ) {
				return $this->response(
					$this->code_read,
					__( 'Enrolled user list obtained successfully', 'tutor-pro' ),
					$enrollments,
				);
			}
			return $this->response(
				$this->code_read,
				__( 'No enrollments found under this course', 'tutor-pro' ),
				'',
				$this->client_error_code
			);

		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Error getting enrollment list', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Update enrollment status
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_enrollment( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, array( 'status', 'enrollment_id' ) );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			return $this->response(
				$this->code_update,
				__( 'Enrollment update failed', 'tutor-pro' ),
				$validation->errors,
				$this->client_error_code
			);
		}

		try {
			$update = wp_update_post(
				array(
					'ID'          => $params['enrollment_id'],
					'post_status' => $params['status'],
				)
			);
			if ( ! is_wp_error( $update ) ) {
				return $this->response(
					$this->code_update,
					__( 'User\'s enrollment status updated', 'tutor-pro' ),
				);
			} else {
				return $this->response(
					$this->code_update,
					__( 'Enrollment update failed', 'tutor-pro' ),
					$update->get_error_message(),
					$this->server_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Enrollment update failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Validate data
	 *
	 * @since 2.6.0
	 *
	 * @param array $data form data.
	 *
	 * @return object
	 */
	protected function validate( array $data ): object {
		$enrollment_status = implode( ',', $this->enrollment_status );

		$validation_rules = array(
			'course_id'     => 'required|numeric',
			'user_id'       => 'required|numeric|user_exists',
			'status'        => "required|match_string:{$enrollment_status}",
			'enrollment_id' => 'required|numeric',
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
