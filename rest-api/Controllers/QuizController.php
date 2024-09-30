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
use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use TUTOR\Quiz;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Controller
 */
class QuizController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $operation = 'quiz';

	/**
	 * Quiz fillable fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'topic_id',
		'quiz_title',
		'quiz_description',
		'quiz_author',
		'quiz_options',
	);

	/**
	 * Required fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $required_fields = array(
		'topic_id',
		'quiz_title',
		'quiz_author',
		'quiz_options',
	);

	/**
	 * Allowed time units
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $time_units;

	/**
	 * Allowed modes
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $modes;

	/**
	 * Allowed time units
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $question_layouts;

	/**
	 * Allowed time units
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $questions_orders;

	/**
	 * Quiz post type
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Initialize props
	 *
	 * @since 2.6.0
	 */
	public function __construct() {
		parent::__construct();

		$this->post_type = tutor()->quiz_post_type;

		$this->time_units       = Quiz::quiz_time_units();
		$this->modes            = array_column( Quiz::quiz_modes(), 'key' );
		$this->question_layouts = Quiz::quiz_question_layouts();
		$this->questions_orders = Quiz::quiz_question_orders();
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
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'quiz_description' => 'wp_kses_post',
			)
		);

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		$params['post_type'] = $this->post_type;

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		try {
			$this->validate_quiz_options( $params, $errors );
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Quiz create failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Quiz create failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$quiz_data = array(
			'post_type'      => $this->post_type,
			'post_title'     => $params['quiz_title'],
			'post_name'      => $params['quiz_title'],
			'post_content'   => $params['quiz_description'] ?? '',
			'post_status'    => 'publish',
			'comment_status' => 'open',
			'post_author'    => $params['quiz_author'],
			'post_parent'    => $params['topic_id'],
		);

		$post_id = wp_insert_post( $quiz_data );
		if ( is_wp_error( $post_id ) ) {
			return $this->response(
				$this->code_create,
				__( 'Quiz create failed', 'tutor-pro' ),
				$post_id->get_error_message(),
				$this->server_error_code
			);
		} elseif ( ! $post_id ) {
			return $this->response(
				$this->code_create,
				__( 'Quiz create failed', 'tutor-pro' ),
				$post_id,
				$this->client_error_code
			);
		} else {
			// Update post meta.
			$this->update_post_meta( $post_id, $params );

			return $this->response(
				$this->code_create,
				__( 'Quiz created successfully', 'tutor-pro' ),
				$post_id
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
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'quiz_description' => 'wp_kses_post',
			)
		);

		// Extract fillable fields.
		$params       = array_intersect_key( $params, array_flip( $this->fillable_fields ) );
		$params['ID'] = $request->get_param( 'id' );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		// Validate video source if user set video.
		try {
			$this->validate_quiz_options( $params, $errors );
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Quiz update failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_update,
				__( 'Quiz update failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$quiz_data = array(
			'ID' => $params['ID'],
		);

		if ( isset( $params['quiz_title'] ) ) {
			$quiz_data['post_title'] = $params['quiz_title'];
		}
		if ( isset( $params['quiz_description'] ) ) {
			$quiz_data['post_content'] = $params['quiz_description'];
		}
		if ( isset( $params['quiz_author'] ) ) {
			$quiz_data['post_author'] = $params['quiz_author'];
		}
		if ( isset( $params['topic_id'] ) ) {
			$quiz_data['post_parent'] = $params['post_parent'];
		}

		$post_id = wp_update_post( $quiz_data );
		if ( is_wp_error( $post_id ) ) {
			return $this->response(
				$this->code_update,
				__( 'Quiz update failed', 'tutor-pro' ),
				$post_id->get_error_message(),
				$this->server_error_code
			);
		} else {
			// Update quiz thumb.
			$this->update_post_meta( $post_id, $params );

			return $this->response(
				$this->code_update,
				__( 'Quiz update successfully', 'tutor-pro' ),
				$post_id
			);
		}
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
	 * Validate quiz options
	 *
	 * @since 2.6.0
	 *
	 * @param array $params request params.
	 * @param array $errors errors.
	 *
	 * @return void
	 */
	public function validate_quiz_options( array $params, array &$errors ) {
		$quiz_options = $params['quiz_options'] ?? null;
		if ( $quiz_options ) {
			if ( isset( $quiz_options['time_limit'] ) ) {
				$time_unit = $quiz_options['time_limit']['time_type'] ?? '';
				if ( ! in_array( $time_unit, array_keys( $this->time_units ) ) ) {
					$errors['quiz_option'][] = __( 'Invalid time type', 'tutor-pro' );
				}
			} else {
				$errors['quiz_option'][] = __( 'Time limit is required', 'tutor-pro' );
			}

			if ( isset( $quiz_options['feedback_mode'] ) ) {
				$feedback_mode = $quiz_options['feedback_mode'] ?? '';
				if ( ! in_array( $feedback_mode, $this->modes ) ) {
					$errors['quiz_option'][] = __( 'Invalid feedback mode', 'tutor-pro' );
				}
			} else {
				$errors['quiz_option'][] = __( 'Feedback mode is required', 'tutor-pro' );
			}

			if ( isset( $quiz_options['question_layout_view'] ) ) {
				$question_layout_view = $quiz_options['question_layout_view'] ?? '';
				if ( ! in_array( $question_layout_view, array_keys( $this->question_layouts ) ) ) {
					$errors['quiz_option'][] = __( 'Invalid question layout view', 'tutor-pro' );
				}
			} else {
				$errors['quiz_option'][] = __( 'Question layout view is required', 'tutor-pro' );
			}

			if ( isset( $quiz_options['questions_order'] ) ) {
				$questions_order = $quiz_options['questions_order'] ?? '';
				if ( ! in_array( $questions_order, array_keys( $this->questions_orders ) ) ) {
					$errors['quiz_option'][] = __( 'Invalid question order', 'tutor-pro' );
				}
			} else {
				$errors['quiz_option'][] = __( 'Question order is required', 'tutor-pro' );
			}
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
		$topic_type = tutor()->topics_post_type;

		$validation_rules = array(
			'ID'           => 'required|numeric',
			'topic_id'     => "required|numeric|post_type:{$topic_type}",
			'quiz_title'   => 'required',
			'quiz_author'  => 'required|user_exists',
			'quiz_options' => 'required|is_array',
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

