<?php
/**
 * Wishlist Controller
 *
 * Manage API for wishlist
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
 * Wishlist Controller
 */
class WishlistController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.2
	 *
	 * @var string
	 */
	public $operation = 'wishlist';

	/**
	 * Wishlist fillable fields
	 *
	 * @since 2.6.2
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'user_id',
		'course_id',
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
	);

	/**
	 * Get wishlist API request
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_wishlist( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		$this->setup_required_fields( $params, array( 'user_id' ) );

		// Validate request.
		$validation = $this->validate( $params );

		if ( ! $validation->success ) {
			return $this->response(
				$this->code_create,
				__( 'Wishlist retrieved failed', 'tutor-pro' ),
				$validation->errors,
				$this->client_error_code
			);
		}

		$user_id = (int) $params['user_id'];

		try {
			$wishlists = tutor_utils()->get_wishlist( $user_id );

			return $this->response(
				$this->code_read,
				__( 'Wishlist retrieved successfully', 'tutor-pro' ),
				$wishlists
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Wishlist retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle wishlist create API request
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

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
				__( 'Wishlist add failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$user_id = (int) $params['user_id'];

		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_create,
				__( 'Wishlist add failed', 'tutor-pro' ),
				__( 'User is not valid', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		if ( tutor_utils()->is_wishlisted( (int) $params['course_id'], $user_id ) ) {
			return $this->response(
				$this->code_create,
				__( 'Wishlist add failed', 'tutor-pro' ),
				__( 'Already added to wishlist', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		try {
			$ajax   = new Ajax( false );
			$result = $ajax->add_or_delete_wishlist( $user_id, (int) $params['course_id'] );

			if ( 'added' === $result ) {
				return $this->response(
					$this->code_create,
					__( 'Successfully added to Wishlist', 'tutor-pro' ),
				);
			} else {
				return $this->response(
					$this->code_create,
					__( 'Wishlist add failed', 'tutor-pro' ),
					__( 'Something went wrong', 'tutor-pro' ),
					$this->server_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Wishlist add failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle wishlist delete API request
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

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
				$this->code_delete,
				__( 'Wishlist delete failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$user_id = (int) $params['user_id'];

		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_delete,
				__( 'User is not valid', 'tutor-pro' ),
			);
		}

		if ( ! tutor_utils()->is_wishlisted( (int) $params['course_id'], $user_id ) ) {
			return $this->response(
				$this->code_delete,
				__( 'Wishlist delete failed', 'tutor-pro' ),
				__( 'Id not found', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		try {
			$ajax   = new Ajax( false );
			$result = $ajax->add_or_delete_wishlist( $user_id, (int) $params['course_id'] );

			if ( 'removed' === $result ) {
				return $this->response(
					$this->code_delete,
					__( 'Successfully removed from Wishlist', 'tutor-pro' ),
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Wishlist delete failed', 'tutor-pro' ),
					__( 'Something went wrong', 'tutor-pro' ),
					$this->server_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Wishlist delete failed', 'tutor-pro' ),
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
			'user_id'   => 'required|numeric|user_exists',
			'course_id' => 'required|numeric',
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
