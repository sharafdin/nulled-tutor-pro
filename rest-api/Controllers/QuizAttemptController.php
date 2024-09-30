<?php
/**
 * Quiz Attempt Controller
 *
 * Manage API for quiz attempts
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.1
 */

namespace TutorPro\RestAPI\Controllers;

use Exception;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use Tutor\Models\QuizModel;
use TUTOR\Quiz;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Attempt Controller
 */
class QuizAttemptController extends BaseController {

	/**
	 * Available attempt statuses
	 *
	 * @since 2.6.1
	 *
	 * @var string
	 */
	const ATTEMPT_STARTED         = 'attempt_started';
	const ATTEMPT_REVIEW_REQUIRED = 'review_required';
	const ATTEMPT_ENDED           = 'attempt_ended';
	const ATTEMPT_TIMEOUT         = 'attempt_timeout';

	/**
	 * Operation codes
	 *
	 * @since 2.6.1
	 *
	 * @var string
	 */
	public $operation = 'quiz_attempt';

	/**
	 * Quiz fillable fields
	 *
	 * @since 2.6.1
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'course_id',
		'student_id',
		'quiz_id',
		'quiz_question_answers',
	);

	/**
	 * Required fields
	 *
	 * @since 2.6.1
	 *
	 * @var array
	 */
	private $required_fields = array(
		'course_id',
		'student_id',
		'quiz_id',
		'quiz_question_answers',
	);

	/**
	 * Initialize props
	 *
	 * @since 2.6.1
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Handle quiz create API request
	 *
	 * @since 2.6.1
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		global $wpdb;

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

		if ( ! tutor_utils()->is_enrolled( $params['course_id'], $params['student_id'] ) ) {
			$errors['enrollment'] = __( 'You are not enrolled in this course', 'tutor-pro' );
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Quiz attempt failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		// Start transaction.
		$wpdb->query( 'START TRANSACTION' );

		try {
			$attempt_id = Quiz::quiz_attempt(
				$params['course_id'],
				$params['quiz_id'],
				$params['student_id'],
				self::ATTEMPT_STARTED
			);

			if ( $attempt_id ) {
				$attempt = tutor_utils()->get_attempt( $attempt_id );

				$question_answers = $params['quiz_question_answers'];
				$question_ids     = array_column( $question_answers, 'question_id' );
				$answers          = array_column( $question_answers, 'answer' );

				$attempt_answers = array(
					$attempt_id => array(
						'quiz_question_ids' => $question_ids,
						'quiz_question'     => array_combine( $question_ids, $answers ),
					),
				);

				try {
					Quiz::manage_attempt_answers( $attempt_answers, $attempt, $attempt_id, $params['course_id'], $params['student_id'] );

					// Commit transaction.
					$wpdb->query( 'COMMIT' );

					return $this->response(
						$this->code_create,
						__( 'Quiz attempt success', 'tutor-pro' ),
						$attempt_id,
					);
				} catch ( \Throwable $th ) {
					// Rollback transaction.
					$wpdb->query( 'ROLLBACK' );

					return $this->response(
						$this->code_create,
						__( 'Quiz attempt failed', 'tutor-pro' ),
						$th->getMessage(),
						$this->client_error_code
					);
				}
			} else {
				return $this->response(
					$this->code_create,
					__( 'Quiz attempt failed', 'tutor-pro' ),
					'',
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			// Rollback transaction.
			$wpdb->query( 'ROLLBACK' );

			return $this->response(
				$this->code_create,
				__( 'Quiz attempt failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Handle quiz read API request
	 *
	 * @since 2.6.1
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function read( WP_REST_Request $request ) {
		$params = Input::sanitize_array( $request->get_params() );

		$quiz_id    = isset( $params['quiz_id'] ) ? (int) $params['quiz_id'] : 0;
		$student_id = isset( $params['student_id'] ) ? (int) $params['student_id'] : 0;

		if ( 0 === $student_id ) {
			return $this->response(
				$this->code_read,
				__( 'student_id is required', 'tutor-pro' ),
				'',
				$this->client_error_code
			);
		}

		try {
			if ( 0 === $quiz_id ) {
				$attempts = tutor_utils()->get_all_quiz_attempts_by_user( $student_id );
				return $this->response(
					$this->code_read,
					__( 'Quiz attempts fetched successfully', 'tutor-pro' ),
					$attempts
				);
			}

			$attempts = ( new QuizModel() )->quiz_attempts( $quiz_id, $student_id );

			return $this->response(
				$this->code_read,
				__( 'Quiz attempts fetched successfully', 'tutor-pro' ),
				$attempts
			);

		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Quiz attempts fetch failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Handle single quiz read API request
	 *
	 * @since 2.6.1
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function read_one( WP_REST_Request $request ) {
		$params = Input::sanitize_array( $request->get_params() );
		try {
			$attempts = QuizModel::quiz_attempt_details( $params['attempt_id'] );
			return $this->response(
				$this->code_read,
				__( 'Quiz attempts fetched successfully', 'tutor-pro' ),
				$attempts
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Quiz attempts fetch failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Delete quiz
	 *
	 * @since 2.6.1
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$quiz_id = $request->get_param( 'id' );
		try {
			$delete = wp_delete_post( $quiz_id, true );
			if ( $delete ) {
				return $this->response(
					$this->code_delete,
					__( 'Quiz deleted successfully', 'tutor-pro' ),
					$quiz_id
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Quiz delete failed', 'tutor-pro' ),
					'',
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Quiz delete failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}


	/**
	 * Validate data
	 *
	 * @since 2.6.1
	 *
	 * @param array $data form data.
	 *
	 * @return object
	 */
	protected function validate( array $data ): object {
		$topic_type = tutor()->topics_post_type;

		$validation_rules = array(
			'ID'                    => 'required|numeric',
			'course_id'             => 'required|numeric',
			'quiz_id'               => 'required|numeric',
			'student_id'            => 'required|numeric',
			'quiz_question_answers' => 'required|is_array',
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
