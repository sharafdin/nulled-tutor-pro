<?php
/**
 * Quiz Controller
 *
 * Manage API for quiz
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.0
 */

namespace TutorPro\RestAPI\Controllers;

use Exception;
use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Controller
 */
class QuizQuestionController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $operation = 'quiz_question';

	/**
	 * Quiz fillable fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'quiz_id',
		'question_title',
		'question_type',
		'answer_required',
		'randomize_question',
		'question_mark',
		'show_question_mark',
		'answer_explanation',
		'question_description',
		'correct_answer',
		'options',
		'question',
	);

	/**
	 * Required fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $required_fields = array(
		'quiz_id',
		'question_title',
		'question_type',
		'answer_required',
		'randomize_question',
		'question_mark',
		'show_question_mark',
		'answer_explanation',
		'question_description',
		'correct_answer',
	);

	/**
	 * Allowed question types
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $question_types;

	/**
	 * Question table name
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	private $question_table;

	/**
	 * Answer table name
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	private $answer_table;

	/**
	 * Initialize props
	 *
	 * @since 2.6.0
	 */
	public function __construct() {
		global $wpdb;

		parent::__construct();

		$this->question_table = $wpdb->prefix . 'tutor_quiz_questions';
		$this->answer_table   = $wpdb->prefix . 'tutor_quiz_question_answers';

		$this->question_types = tutor_utils()->get_question_types();
	}

	/**
	 * Handle quiz create API request
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		global $wpdb;
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'quiz_description'   => 'wp_kses_post',
				'answer_explanation' => 'wp_kses_post',
			)
		);

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Unset correct answer as required field for the open_ended & short_answer.
		$question_type = $params['question_type'];
		if ( in_array( $question_type, array( 'open_ended', 'short_answer' ), true ) ) {
			unset( $params['correct_answer'] );
		}

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Question create failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$question_data = self::prepare_question_data( $params );

		// Start transaction.
		$wpdb->query( 'START TRANSACTION' );

		// Create question.
		$question_id = QueryHelper::insert( $this->question_table, $question_data );
		if ( ! $question_id ) {
			return $this->response(
				$this->code_create,
				__( 'Question create failed', 'tutor-pro' ),
				$question_id,
				$this->client_error_code
			);
		} else {
			// Prepare answers.
			$answers = $this->prepare_answer( $question_id, $params );
			try {
				$this->save_answers( $question_id, $question_data['question_type'], $answers );
			} catch ( \Throwable $th ) {
				$wpdb->query( 'ROLLBACK' );

				return $this->response(
					$this->code_create,
					__( 'Quiz create failed', 'tutor-pro' ),
					$th->getMessage(),
					$this->client_error_code
				);
			}

			// Commit.
			$wpdb->query( 'COMMIT' );

			return $this->response(
				$this->code_create,
				__( 'Quiz create successfully', 'tutor-pro' ),
				$question_id
			);
		}
	}

	/**
	 * Handle quiz update API request
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update( WP_REST_Request $request ) {
		global $wpdb;
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'quiz_description'   => 'wp_kses_post',
				'answer_explanation' => 'wp_kses_post',
			)
		);

		$question_id = $params['id'];

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_update,
				__( 'Question update failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$question_data = $this->prepare_question_data_to_update( $question_id, $params );

		// Start transaction.
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Update question.
			QueryHelper::update( $this->question_table, $question_data, array( 'question_id' => $question_id ) );
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Question update failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}

		// Prepare answers.
		if ( isset( $params['options'] ) || isset( $params['correct_answer'] ) ) {
			// Delete existing answers to avoid duplication.
			$this->delete_question_answers( $question_id );

			$answers = $this->prepare_answer( $question_id, $params );
			try {
				$this->save_answers( $question_id, $question_data['question_type'], $answers );
			} catch ( \Throwable $th ) {
				$wpdb->query( 'ROLLBACK' );

				return $this->response(
					$this->code_update,
					__( 'Quiz update failed', 'tutor-pro' ),
					$th->getMessage(),
					$this->client_error_code
				);
			}
		}

		// Commit.
		$wpdb->query( 'COMMIT' );

		return $this->response(
			$this->code_update,
			__( 'Quiz updated successfully', 'tutor-pro' ),
			$question_id
		);
	}

	/**
	 * Prepare question's option/answers
	 *
	 * @since 2.6.0
	 *
	 * @param int   $question_id question id.
	 * @param array $params request params.
	 *
	 * @return array
	 */
	public function prepare_answer( $question_id, $params ): array {
		$answers        = array();
		$options        = $params['options'] ?? array();
		$correct_answer = $params['correct_answer'] ?? '';

		switch ( $params['question_type'] ) {
			case 'true_false':
				$ans_true  = array(
					'answer_title' => __( 'True', 'tutor-pro' ),
					'is_correct'   => 'true' === $correct_answer ? 1 : 0,
				);
				$ans_false = array(
					'answer_title' => __( 'False', 'tutor-pro' ),
					'is_correct'   => 'false' === $correct_answer ? 1 : 0,
				);
				array_push( $answers, $ans_true );
				array_push( $answers, $ans_false );
				break;
			case 'single_choice':
			case 'multiple_choice':
				foreach ( $options as $option ) {
					$is_correct = is_array( $correct_answer ) ? in_array( $option, $correct_answer, true ) : $option === $correct_answer;
					$answer     = array(
						'answer_title' => $option,
						'is_correct'   => (int) $is_correct,
					);
					array_push( $answers, $answer );
				}
				break;
			case 'fill_in_the_blank':
				$answer = array(
					'answer_title'         => $params['question'],
					'answer_two_gap_match' => $params['correct_answer'],
					'is_correct'           => null,
				);
				array_push( $answers, $answer );
				break;
			default:
				break;
		}
		return $answers;
	}

	/**
	 * Save answers
	 *
	 * @since 2.6.0
	 *
	 * @param integer $question_id question id.
	 * @param string  $question_type question type.
	 * @param array   $answers answers options.
	 *
	 * @return void
	 */
	public function save_answers( int $question_id, string $question_type, array $answers ) {
		$answer_data = array();

		foreach ( $answers as $key => $answer ) {
			$option = array(
				'belongs_question_id'   => $question_id,
				'belongs_question_type' => $question_type,
				'answer_title'          => $answer['answer_title'],
				'is_correct'            => $answer['is_correct'],
				'answer_order'          => $key + 1,
				'answer_two_gap_match'  => $answer['answer_two_gap_match'] ?? null,
			);
			array_push( $answer_data, $option );
		}

		// Insert questions answers.
		if ( count( $answer_data ) ) {
			QueryHelper::insert_multiple_rows( $this->answer_table, $answer_data );
		}
	}

	/**
	 * Prepare quiz question data
	 *
	 * @since 2.6.0
	 *
	 * @param array $params request params.
	 *
	 * @return array
	 */
	public function prepare_question_data( array $params ): array {
		$question_data = array(
			'quiz_id'              => $params['quiz_id'],
			'question_title'       => $params['question_title'],
			'question_description' => $params['question_description'] ?? '',
			'answer_explanation'   => $params['answer_explanation'] ?? '',
			'question_type'        => $params['question_type'],
			'question_mark'        => $params['question_mark'] ?? 1,
			'question_order'       => $params['question_order'] ?? 1,
		);

		$question_settings = array(
			'question_mark'      => $params['question_mark'] ?? 1,
			'question_type'      => $params['question_type'],
			'answer_required'    => $params['answer_required'] ?? 1,
			'show_question_mark' => $params['show_question_mark'] ?? 1,
			'randomize_question' => $params['randomize_question'] ?? 0,
		);

		$question_data['question_settings'] = maybe_serialize( $question_settings );

		return $question_data;
	}

	/**
	 * Prepare quiz question data
	 *
	 * @since 2.6.0
	 *
	 * @param int   $question_id question id.
	 * @param array $params request params.
	 *
	 * @return array
	 */
	public function prepare_question_data_to_update( int $question_id, array $params ): array {

		$question = QueryHelper::get_row(
			$this->question_table,
			array(
				'question_id' => $question_id,
			),
			'question_id'
		);

		$question_settings = maybe_unserialize( $question->question_settings );

		$question_data = array(
			'question_title'       => $params['question_title'] ?? $question->question_title,
			'question_description' => $params['question_description'] ?? $question->question_description,
			'answer_explanation'   => $params['answer_explanation'] ?? $question->answer_explanation,
			'question_type'        => $params['question_type'] ?? $question->question_type,
			'question_mark'        => $params['question_mark'] ?? $question->question_mark,
			'question_order'       => $params['answer_required'] ?? $question->answer_required,
		);

		$question_settings = array(
			'question_mark'      => $params['question_mark'] ?? $question->question_mark,
			'question_type'      => $params['question_type'] ?? $question->question_type,
			'answer_required'    => $params['answer_required'] ?? $question->answer_required,
			'show_question_mark' => $params['show_question_mark'] ?? $question_settings['show_question_mark'],
			'randomize_question' => $params['randomize_question'] ?? $question_settings['randomize_question'],
		);

		$question_data['question_settings'] = maybe_serialize( $question_settings );

		return $question_data;
	}

	/**
	 * Update quiz meta
	 *
	 * @since 2.6.0
	 *
	 * @param int   $quiz_id quiz id. params.
	 * @param array $params params.
	 *
	 * @throws Exception Throw new exception.
	 *
	 * @return void
	 */
	private function update_post_meta( $quiz_id, $params ) {
		update_post_meta( $quiz_id, 'tutor_quiz_option', $params['quiz_options'] );
	}

	/**
	 * Delete quiz
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$question_id = $request->get_param( 'id' );
		try {
			$delete = QueryHelper::delete( $this->question_table, array( 'question_id' => $question_id ) );
			if ( $delete ) {
				// Delete answers.
				$this->delete_question_answers( $question_id );
				return $this->response(
					$this->code_delete,
					__( 'Question deleted successfully', 'tutor-pro' ),
					$question_id
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Question delete failed', 'tutor-pro' ),
					'',
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Question delete failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Delete question answers
	 *
	 * @since 2.6.0
	 *
	 * @param integer $question_id question id.
	 *
	 * @return bool
	 */
	public function delete_question_answers( int $question_id ) {
		return QueryHelper::delete( $this->answer_table, array( 'belongs_question_id' => $question_id ) );
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
		$question_types = implode( ',', array_keys( $this->question_types ) );

		$validation_rules = array(
			'ID'                 => 'required|numeric',
			'quiz_id'            => 'required|numeric',
			'question_title'     => 'required',
			'question_type'      => "required|match_string:{$question_types}",
			'answer_required'    => 'match_string:1,0',
			'randomize_question' => 'match_string:1,0',
			'question_mark'      => 'required|numeric',
			'show_question_mark' => 'match_string:1,0',
			'correct_answer'     => 'required',
			'options'            => 'is_array',
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

