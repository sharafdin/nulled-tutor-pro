<?php
/**
 * Assignment Controller
 *
 * Manage API for assignment
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
use TUTOR_ASSIGNMENTS\Assignments;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assignment Controller
 */
class AssignmentController extends BaseController {

	/**
	 * Assignment options short hand
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	const ASS_OPT = 'assignment_options';

	/**
	 * Operation codes
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $operation = 'assignment';

	/**
	 * Fillable fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'topic_id',
		'assignment_title',
		'assignment_content',
		'assignment_author',
		'attachments',
		self::ASS_OPT,
	);

	/**
	 * Assignment submit Fillable fields
	 *
	 * @since 2.6.2
	 *
	 * @var array
	 */
	private $submit_fillable_fields = array(
		'update_id',
		'assignment_id',
		'assignment_answer',
		'student_id',
	);

	/**
	 * Required fields for submitting the assignment
	 *
	 * @since 2.6.2
	 *
	 * @var array
	 */
	private $submit_required_fields = array(
		'assignment_id',
		'assignment_answer',
		'student_id',
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
		'assignment_title',
		'assignment_author',
		self::ASS_OPT,
	);

	/**
	 * Assignment options with default value
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $allowed_options = array(
		'time_duration'          => array(
			'value' => 0,
			'time'  => 'weeks',
		),
		'total_mark'             => 10,
		'pass_mark'              => 2,
		'upload_files_limit'     => 1,
		'upload_file_size_limit' => 2,
	);

	/**
	 * Assignment allowed time options
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $allowed_time_options = array( 'weeks', 'days', 'hours' );

	/**
	 * Assignment post type
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

		$this->post_type = tutor()->assignment_post_type;
	}

	/**
	 * Handle assignment create API request
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
				'assignment_content' => 'wp_kses_post',
			)
		);

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		$params['post_type'] = $this->post_type;

		// Set empty value if required fields not set.
		foreach ( $this->required_fields as $field ) {
			if ( ! isset( $params[ $field ] ) ) {
				$params[ $field ] = '';
			}
		}

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment create failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$assignment_data = array(
			'post_type'    => $this->post_type,
			'post_status'  => 'publish',
			'post_author'  => $params['assignment_author'],
			'post_parent'  => $params['topic_id'],
			'post_title'   => $params['assignment_title'],
			'post_name'    => $params['assignment_title'],
			'post_content' => $params['assignment_content'] ?? '',
		);

		$post_id = wp_insert_post( $assignment_data );
		if ( is_wp_error( $post_id ) ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment create failed', 'tutor-pro' ),
				$post_id->get_error_message(),
				$this->server_error_code
			);
		} elseif ( ! $post_id ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment create failed', 'tutor-pro' ),
				$post_id,
				$this->client_error_code
			);
		} else {
			$this->update_post_meta( $post_id, $params );
			return $this->response(
				$this->code_create,
				__( 'Assignment created successfully', 'tutor-pro' ),
				$post_id
			);
		}
	}

	/**
	 * Handle assignment update API request
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
				'assignment_content' => 'wp_kses_post',
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

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_update,
				__( 'Assignment updated failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$assignment_data = array(
			'ID' => $params['ID'],
		);

		if ( isset( $params['assignment_title'] ) ) {
			$assignment_data['post_title'] = $params['assignment_title'];
		}
		if ( isset( $params['assignment_content'] ) ) {
			$assignment_data['post_content'] = $params['assignment_content'];
		}
		if ( isset( $params['assignment_author'] ) ) {
			$assignment_data['post_author'] = $params['assignment_author'];
		}
		if ( isset( $params['topic_id'] ) ) {
			$assignment_data['post_parent'] = $params['post_parent'];
		}

		$post_id = wp_update_post( $assignment_data );
		if ( is_wp_error( $post_id ) ) {
			return $this->response(
				$this->code_update,
				__( 'Assignment update failed', 'tutor-pro' ),
				$post_id->get_error_message(),
				$this->server_error_code
			);
		} else {
			$this->update_post_meta( $post_id, $params );
			return $this->response(
				$this->code_update,
				__( 'Assignment updated successfully', 'tutor-pro' ),
				$post_id
			);
		}
	}

	/**
	 * Prepare assignment meta data for update
	 *
	 * @since 2.6.0
	 *
	 * @param int   $post_id post id.
	 * @param array $params params.
	 *
	 * @throws Exception Throw new exception.
	 *
	 * @return void
	 */
	private function update_post_meta( int $post_id, array $params ) {
		if ( isset( $params[ self::ASS_OPT ] ) ) {
			if ( ! empty( $params[ self::ASS_OPT ] ) ) {
				$this->allowed_options['time_duration']['value'] = $params[ self::ASS_OPT ]['time_duration']['value'] ?? $this->allowed_options['time_duration']['value'];

				$this->allowed_options['time_duration']['time'] = $params[ self::ASS_OPT ]['time_duration']['unit'] ?? $this->allowed_options['time_duration']['unit'];

				$this->allowed_options['total_mark'] = $params[ self::ASS_OPT ]['total_mark'] ?? $this->allowed_options['total_mark'];

				$this->allowed_options['pass_mark'] = $params[ self::ASS_OPT ]['pass_mark'] ?? $this->allowed_options['pass_mark'];

				$this->allowed_options['upload_files_limit'] = $params[ self::ASS_OPT ]['upload_files_limit'] ?? $this->allowed_options['upload_files_limit'];

				$this->allowed_options['upload_file_size_limit'] = $params[ self::ASS_OPT ]['upload_file_size_limit'] ?? $this->allowed_options['upload_file_size_limit'];
			}
		}

		// Update assignment options.
		update_post_meta( $post_id, 'assignment_option', $this->allowed_options );

		update_post_meta( $post_id, '_tutor_assignment_total_mark', $this->allowed_options['total_mark'] );

		update_post_meta( $post_id, '_tutor_assignment_pass_mark', $this->allowed_options['pass_mark'] );

		if ( isset( $params['attachments'] ) ) {
			if ( ! empty( $params['attachments'] ) ) {
				update_post_meta( $post_id, '_tutor_assignment_attachments', $params['attachments'] );
			} else {
				delete_post_meta( $post_id, '_tutor_assignment_attachments' );
			}
		}

		// Get parent of parent.
		$course_id = get_post_parent( get_post_parent( $post_id ) );
		update_post_meta( $post_id, '_tutor_course_id_for_assignments', $course_id );
	}

	/**
	 * Delete assignment
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$assignment_id = $request->get_param( 'id' );
		try {
			$delete = wp_delete_post( $assignment_id, false );
			if ( $delete ) {
				return $this->response(
					$this->code_delete,
					__( 'Assignment deleted successfully', 'tutor-pro' ),
					$assignment_id
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Assignment delete failed', 'tutor-pro' ),
					'',
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Assignment delete failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle assignment submit GET API request for student
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_student_assignment( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'assignment_content' => 'wp_kses_post',
			)
		);

		$this->setup_required_fields( $params, array( 'student_id', 'assignment_id' ) );

		// Validate request.
		$validation = $this->validate( $params );

		if ( ! $validation->success ) {
			return $this->response(
				$this->code_read,
				__( 'Assignment retrieve failed', 'tutor-pro' ),
				$validation->errors,
				$this->client_error_code
			);
		}

		$assignment_id = (int) $params['assignment_id'];
		$student_id    = (int) $params['student_id'];

		try {
			$data       = array();
			$assignment = tutor_utils()->get_single_comment_user_post_id( $assignment_id, $student_id );
			$meta_data  = get_post_custom( $assignment_id );

			$data['assignment'] = $assignment ? $assignment : array();
			$data['meta_data']  = $meta_data;

			return $this->response(
				$this->code_read,
				__( 'Assignment retrieved successfully', 'tutor-pro' ),
				$data
			);
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_read,
				__( 'Assignment retrieved failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle assignment submit API request for student
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function student_assignment_submit( WP_REST_Request $request ) {
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'assignment_content' => 'wp_kses_post',
			)
		);

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->submit_fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->submit_required_fields );

		// Validate request.
		$validation = $this->validate( $params );

		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		$deadline_date = tutor_utils()->get_assignment_deadline_date( (int) $params['assignment_id'], 'Y-m-d H:i:s' );

		if ( ! empty( $deadline_date ) ) {
			// Convert deadline string to a DateTime object.
			$deadline_time = new \DateTime( $deadline_date );
			// Get the current time.
			$current_time = new \DateTime();

			// Compare the deadline time with the current time.
			if ( $deadline_time < $current_time ) {
				// Deadline has expired.
				$errors['assignment'] = __( 'The deadline has expired', 'tutor-pro' );
			}
		}

		// Get if student has any submitted assignment.
		$submitted_assignment = tutor_utils()->get_single_comment_user_post_id( (int) $params['assignment_id'], (int) $params['student_id'] );

		if ( $submitted_assignment ) {
			$errors['assignment'] = __( 'Assignment already submitted', 'tutor-pro' );
		}

		// Total file count.
		$files              = $request->get_file_params();
		$allow_to_upload    = (int) tutor_utils()->get_assignment_option( (int) $params['assignment_id'], 'upload_files_limit' );
		$current_file_count = ! empty( $files ) && isset( $files['attached_assignment_files']['name'] ) ? count( $files['attached_assignment_files']['name'] ) : 0;
		$total_file_count   = $current_file_count;

		// Check if total files exceed the limit.
		if ( $total_file_count > $allow_to_upload ) {
			$errors['upload'] = __( 'File Upload Limit Exceeded', 'tutor-pro' );
		}

		$course_id = tutor_utils()->get_course_id_by( 'assignment', (int) $params['assignment_id'] );

		if ( ! tutor_utils()->is_enrolled( $course_id, (int) $params['student_id'] ) ) {
			$errors['enrollment'] = __( 'You are not enrolled in this course', 'tutor-pro' );
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment submission failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$params['update_id'] = 0;

		$store_data    = $this->prepare_assignment_data( $params );
		$assignment    = new Assignments( false );
		$assignment_id = $assignment->insert_assignment_submit( $store_data );

		if ( $assignment_id > 0 ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment submitted successfully', 'tutor-pro' ),
				$assignment_id
			);
		} else {
			return $this->response(
				$this->code_create,
				__( 'Assignment submission failed', 'tutor-pro' ),
				'',
				$this->server_error_code
			);
		}
	}

	/**
	 * Handle assignment update API request for student
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function student_assignment_update( WP_REST_Request $request ) {
		$errors = array();

		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'assignment_content' => 'wp_kses_post',
			)
		);

		$params['update_id'] = (int) $request->get_param( 'submission_id' ) ?? 0;

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->submit_fillable_fields ) );

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->submit_required_fields );

		// Validate request.
		$validation = $this->validate( $params );

		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		$is_update_exists = get_comment( $params['update_id'] );

		if ( ! $is_update_exists ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment Id not found', 'tutor-pro' ),
				'',
				$this->server_error_code
			);
		}

		$deadline_date = tutor_utils()->get_assignment_deadline_date( (int) $params['assignment_id'], 'Y-m-d H:i:s' );

		if ( ! empty( $deadline_date ) ) {
			// Convert deadline string to a DateTime object.
			$deadline_time = new \DateTime( $deadline_date );
			// Get the current time.
			$current_time = new \DateTime();

			// Compare the deadline time with the current time.
			if ( $deadline_time < $current_time ) {
				// Deadline has expired.
				$errors['assignment'] = __( 'The deadline has expired', 'tutor-pro' );
			}
		}

		// Check if already evaluated.
		$is_evaluated = get_comment_meta( $params['update_id'], 'evaluate_time' );

		if ( ! empty( $is_evaluated ) ) {
			$errors['assignment'] = __( 'Assignment already evaluated', 'tutor-pro' );
		}

		$previous_upload_count = 0;
		// Get previous upload count if any.
		$submitted_attachments = get_comment_meta( $params['update_id'], 'uploaded_attachments' );
		if ( ! empty( $submitted_attachments ) ) {
			$submitted_attachments = json_decode( $submitted_attachments[0] );
			$previous_upload_count = count( $submitted_attachments );
		}

		// Total file count.
		$files              = $request->get_file_params();
		$allow_to_upload    = (int) tutor_utils()->get_assignment_option( (int) $params['assignment_id'], 'upload_files_limit' );
		$current_file_count = ! empty( $files ) && isset( $files['attached_assignment_files']['name'] ) ? count( $files['attached_assignment_files']['name'] ) : 0;
		$total_file_count   = $current_file_count + $previous_upload_count;

		// Check if total files exceed the limit.
		if ( $total_file_count > $allow_to_upload ) {
			$errors['upload'] = __( 'File Upload Limit Exceeded', 'tutor-pro' );
		}

		$course_id = tutor_utils()->get_course_id_by( 'assignment', (int) $params['assignment_id'] );

		if ( ! tutor_utils()->is_enrolled( $course_id, (int) $params['student_id'] ) ) {
			$errors['enrollment'] = __( 'You are not enrolled in this course', 'tutor-pro' );
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment submission failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$store_data    = $this->prepare_assignment_data( $params );
		$assignment    = new Assignments( false );
		$assignment_id = $assignment->update_assignment_submit( $store_data );

		if ( $assignment_id > 0 ) {
			return $this->response(
				$this->code_create,
				__( 'Assignment updated successfully', 'tutor-pro' ),
				$assignment_id
			);
		} else {
			return $this->response(
				$this->code_create,
				__( 'Assignment update failed', 'tutor-pro' ),
				'',
				$this->server_error_code
			);
		}
	}

	/**
	 * Function to prepare assignment data
	 *
	 * @since 2.6.2
	 *
	 * @param array $params params array.
	 *
	 * @return object
	 */
	private function prepare_assignment_data( $params ) {
		$store_data                       = new \stdClass();
		$store_data->update_id            = $params['update_id'];
		$store_data->assignment_id        = (int) $params['assignment_id'];
		$store_data->assignment_answer    = $params['assignment_answer'];
		$store_data->allowed_upload_files = (int) tutor_utils()->get_assignment_option( $store_data->assignment_id, 'upload_files_limit' );
		$store_data->assignment_submit_id = tutor_utils()->is_assignment_submitting( $store_data->assignment_id );
		$store_data->course_id            = tutor_utils()->get_course_id_by( 'assignment', $store_data->assignment_id );
		$store_data->student_id           = (int) $params['student_id'];

		return $store_data;
	}

	/**
	 * Delete assignment attachment
	 *
	 * @since 2.6.2
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_attachment( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'assignment_content' => 'wp_kses_post',
			)
		);

		$assignment_comment_id = $request->get_param( 'submission_id' );
		$file_name             = $request->get_param( 'file_name' );

		// Validate request.
		$validation = $this->validate( $params );

		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Attachment delete failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		try {
			$delete = Assignments::delete_attachment( $assignment_comment_id, $file_name );

			if ( $delete ) {
				return $this->response(
					$this->code_delete,
					__( 'Attachment deleted successfully', 'tutor-pro' ),
					$assignment_comment_id
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Attachment delete failed', 'tutor-pro' ),
					'Invalid Submission ID or File name or something went wrong',
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Attachment delete failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
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
		$topic_type       = tutor()->topics_post_type;
		$validation_rules = array(
			'ID'                 => 'required|numeric',
			'topic_id'           => "required|numeric|post_type:{$topic_type}",
			'assignment_title'   => 'required',
			'assignment_author'  => 'required|user_exists',
			'assignment_options' => 'required|is_array',
			'attachments'        => 'is_array',
			'update_id'          => 'numeric',
			'assignment_id'      => 'required|numeric',
			'assignment_answer'  => 'required',
			'student_id'         => 'required|numeric|user_exists',
			'submission_id'      => 'required|numeric',
			'file_name'          => 'required',
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
