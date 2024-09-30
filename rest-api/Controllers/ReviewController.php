<?php
/**
 * Review Controller
 *
 * Manage API for review
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.2
 */

namespace TutorPro\RestAPI\Controllers;

use TUTOR\Ajax;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Review Controller
 */
class ReviewController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.2
	 *
	 * @var string
	 */
	public $operation = 'review';

	/**
	 * Fillable fields
	 *
	 * @since 2.6.2
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'user_id',
		'course_id',
		'rating',
		'review',
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
		'course_id',
		'rating',
		'review',
	);

	/**
	 * Handle Review get API request
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function list( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Extract fillable fields.
		$params  = array_intersect_key( $params, array_flip( $this->fillable_fields ) );
		$user_id = (int) $request->get_param( 'user_id' );

		try {
			$reviews = tutor_utils()->get_reviews_by_user( $user_id );

			return $this->response(
				$this->code_read,
				__( 'Reviews retrieved successfully', 'tutor-pro' ),
				$reviews
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Reviews retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle Review create API request
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Extract fillable fields.
		$params  = array_intersect_key( $params, array_flip( $this->fillable_fields ) );
		$user_id = (int) $request->get_param( 'user_id' );
		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_create,
				__( 'Review create failed', 'tutor-pro' ),
				__( 'User is not valid', 'tutor-pro' ),
				$this->client_error_code
			);
		}

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
				__( 'Review create failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$course_id = (int) $params['course_id'];
		$rating    = (int) $params['rating'];
		$review    = $params['review'];

		$rating <= 0 ? $rating = 1 : 0;
		$rating > 5 ? $rating  = 5 : 0;

		if ( ! tutor_utils()->has_enrolled_content_access( 'course', $course_id, $user_id ) ) {
			return $this->response(
				$this->code_create,
				__( 'Access denied', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		$reviews = tutor_utils()->get_reviews_by_user( $user_id, 0, 0, true, $course_id, array( 'approved', 'hold' ) );

		if ( ! empty( $reviews->results ) ) {
			return $this->response(
				$this->code_create,
				__( 'Review add failed', 'tutor-pro' ),
				__( 'Course already reviewed', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		try {
			$ajax   = new Ajax( false );
			$result = $ajax->add_or_update_review( $user_id, $course_id, $rating, $review );

			if ( 'created' === $result ) {
				return $this->response(
					$this->code_create,
					__( 'Review added successfully', 'tutor-pro' ),
				);
			} else {
				return $this->response(
					$this->code_create,
					__( 'Review add failed', 'tutor-pro' ),
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Review add failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle Review update API request
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		$review_id = (int) $request->get_param( 'review_id' );
		$user_id   = (int) $request->get_param( 'user_id' );
		$is_user   = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_update,
				__( 'Review update failed', 'tutor-pro' ),
				__( 'User is not valid', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_update,
				__( 'Review update failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$course_id = (int) $params['course_id'];
		$rating    = (int) $params['rating'];
		$review    = $params['review'];

		$rating <= 0 ? $rating = 1 : 0;
		$rating > 5 ? $rating  = 5 : 0;

		if ( ! tutor_utils()->has_enrolled_content_access( 'course', $course_id, $user_id ) ) {
			return $this->response(
				$this->code_update,
				__( 'Access denied', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		$reviews   = tutor_utils()->get_reviews_by_user( $user_id, 0, 0, true, $course_id );
		$id_exists = false;

		if ( ! empty( $reviews->results ) ) {
			foreach ( $reviews->results as $result ) {
				if ( (int) $result->comment_ID === $review_id ) {
					$id_exists = true;
					break;
				}
			}

			if ( ! $id_exists ) {
				return $this->response(
					$this->code_create,
					__( 'Review update failed', 'tutor-pro' ),
					__( 'Review Id not found', 'tutor-pro' ),
					$this->client_error_code
				);
			}
		}

		try {
			$ajax   = new Ajax( false );
			$result = $ajax->add_or_update_review( $user_id, $course_id, $rating, $review, $review_id );

			if ( 'updated' === $result ) {
				return $this->response(
					$this->code_update,
					__( 'Review updated successfully', 'tutor-pro' ),
				);
			} else {
				return $this->response(
					$this->code_update,
					__( 'Review update failed', 'tutor-pro' ),
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Review update failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Delete review
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		$review_id = (int) $request->get_param( 'review_id' );
		$user_id   = (int) $request->get_param( 'user_id' );

		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_create,
				__( 'User is not valid', 'tutor-pro' ),
			);
		}

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_delete,
				__( 'Review delete failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		try {
			$_POST['review_id'] = $review_id;
			$ajax               = new Ajax( false );
			$delete             = $ajax->delete_tutor_review( $user_id );

			if ( $delete ) {
				return $this->response(
					$this->code_delete,
					__( 'Review deleted successfully', 'tutor-pro' )
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Review delete failed', 'tutor-pro' ),
					'',
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Review delete failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
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
			'review_id' => 'required|numeric',
			'course_id' => 'required|numeric',
			'user_id'   => 'required|numeric',
			'rating'    => 'required|numeric',
			'review'    => 'required',
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
