<?php
/**
 * Q&A Controller
 *
 * Manage API for Q&A
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.2
 */

namespace TutorPro\RestAPI\Controllers;

use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use TUTOR\Q_And_A;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Q&A Controller
 */
class QAndAController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.2
	 *
	 * @var string
	 */
	public $operation = 'q_and_a';

	/**
	 * Fillable fields
	 *
	 * @since 2.6.2
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'user_id',
		'offset',
		'limit',
		'course_id',
		'question_id',
		'qna_text',
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
		'qna_text',
	);

	/**
	 * Handle Q&A get API request
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function list( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_read,
				__( 'Q&A retrieved failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$user_id = (int) $request->get_param( 'user_id' );
		$offset  = (int) $request->get_param( 'offset' );
		$limit   = ! empty( (int) $request->get_param( 'limit' ) ) ? (int) $request->get_param( 'limit' ) : 10;

		$offset = max( $offset, 0 );
		$limit  = max( $limit, 10 );

		try {
			$args = array();
			if ( $request->get_param( 'course_id' ) ) {
				$args['course_id'] = $request->get_param( 'course_id' );
			}

			$qna_list = tutor_utils()->get_qa_questions( $offset, $limit, '', null, null, $user_id, null, false, $args );

			return $this->response(
				$this->code_read,
				__( 'Q&A retrieved successfully', 'tutor-pro' ),
				$qna_list
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Q&A retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle Q&A create API request
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array( $request->get_params() );

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			return $this->validation_error_response( $validation->errors, $this->code_create );
		}

		$user_id     = (int) $params['user_id'];
		$course_id   = (int) $params['course_id'];
		$question_id = ! empty( $params['question_id'] ) ? (int) $params['question_id'] : 0;
		$qna_text    = $params['qna_text'];
		$date        = gmdate( 'Y-m-d H:i:s', tutor_time() );
		$user        = get_userdata( $user_id );

		$qna_data              = new \stdClass();
		$qna_data->user_id     = $user_id;
		$qna_data->course_id   = $course_id;
		$qna_data->question_id = $question_id;
		$qna_data->qna_text    = $qna_text;
		$qna_data->user        = $user;
		$qna_data->date        = $date;

		try {
			$qna = new Q_And_A( false );

			if ( ! $qna->has_qna_access( $user_id, $course_id ) ) {
				return $this->response(
					$this->code_create,
					__( 'Q&A add failed', 'tutor-pro' ),
					__( 'You are not authorized to perform this action', 'tutor-pro' ),
					$this->server_error_code
				);
			}

			$question_id = $qna->inset_qna( $qna_data );

			if ( $question_id ) {
				return $this->response(
					$this->code_create,
					__( 'Q&A added successfully', 'tutor-pro' ),
				);
			} else {
				return $this->response(
					$this->code_create,
					__( 'Q&A add failed', 'tutor-pro' ),
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Q&A add failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Delete Q&A
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$question_id = (int) $request->get_param( 'id' );
		$user_id     = (int) $request->get_param( 'user_id' );

		$validation = ValidationHelper::validate(
			array( 'id' => 'has_record:comments,comment_ID' ),
			array( 'id' => $question_id )
		);

		if ( ! $validation->success ) {
			return $this->validation_error_response( $validation->errors, $this->code_delete );
		}

		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_delete,
				__( 'User is not valid', 'tutor-pro' ),
			);
		}

		$can_delete = tutor_utils()->can_delete_qa( $user_id, $question_id );

		if ( ! $can_delete ) {
			return $this->response(
				$this->code_delete,
				__( 'Q&A delete failed', 'tutor-pro' ),
				__( 'You are not authorized to perform this action', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		try {
			$qna = new Q_And_A( false );
			$qna->delete_qna_permanently( array( $question_id ) );
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Q&A delete failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}

		return $this->response(
			$this->code_delete,
			__( 'Q&A deleted successfully', 'tutor-pro' ),
		);
	}

	/**
	 * Mark read/unread Q&A
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function mark_read_unread( WP_REST_Request $request ) {
		$question_id = (int) $request->get_param( 'id' );
		$user_id     = (int) $request->get_param( 'user_id' );

		$is_user = get_userdata( tutor_utils()->get_user_id( $user_id ) );

		if ( ! $is_user ) {
			return $this->response(
				$this->code_update,
				__( 'User is not valid', 'tutor-pro' ),
			);
		}

		$can_update = tutor_utils()->can_delete_qa( $user_id, $question_id );

		if ( ! $can_update ) {
			return $this->response(
				$this->code_update,
				__( 'Q&A mark read/unread failed', 'tutor-pro' ),
				__( 'You are not authorized to perform this action', 'tutor-pro' ),
				$this->client_error_code
			);
		}

		try {
			$qna     = new Q_And_A( false );
			$result  = $qna->trigger_qna_action( $question_id, 'read', 'frontend-dashboard-qna-table-student', $user_id );
			$message = $result ? __( 'Q&A marked as read', 'tutor-pro' ) : __( 'Q&A marked as unread', 'tutor-pro' );

			return $this->response(
				$this->code_update,
				$message,
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Q&A mark read/unread failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
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
			'user_id'     => 'required|numeric|user_exists',
			'offset'      => 'numeric',
			'limit'       => 'numeric',
			'question_id' => 'numeric',
			'course_id'   => 'required|numeric',
			'qna_text'    => 'required',
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
