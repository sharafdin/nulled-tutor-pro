<?php
/**
 * Lesson Controller
 *
 * Manage API for lesson
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
use Tutor\Models\LessonModel;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lesson Controller
 */
class LessonController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $operation = 'lesson';

	/**
	 * Fillable fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'topic_id',
		'lesson_title',
		'lesson_content',
		'thumbnail_id',
		'lesson_author',
		'video',
		'attachments',
		'preview',
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
		'lesson_title',
		'lesson_author',
	);

	/**
	 * Lesson post type
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

		$this->post_type = tutor()->lesson_post_type;

		// Add runtime.
		$this->video_params['runtime'] = array(
			'hours'   => '00',
			'minutes' => '00',
			'seconds' => '00',
		);
	}

	/**
	 * Handle lesson create API request
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
				'lesson_content' => 'wp_kses_post',
			)
		);

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		$params['post_type'] = $this->post_type;

		// Set empty value if required fields not set.
		$this->setup_required_fields( $params, $this->required_fields );

		$params['preview'] = isset( $params['preview'] ) && true == $params['preview'] ? true : false;

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		// Validate video source if user set video.
		$this->validate_video_source( $params, $errors );

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_create,
				__( 'Lesson create failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$lesson_data = array(
			'post_type'      => $this->post_type,
			'post_title'     => $params['lesson_title'],
			'post_name'      => $params['lesson_title'],
			'post_content'   => $params['lesson_content'] ?? '',
			'post_status'    => 'publish',
			'comment_status' => 'open',
			'post_author'    => $params['lesson_author'],
			'post_parent'    => $params['topic_id'],
		);

		try {
			$this->prepare_post_meta( $params );
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Lesson create failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}

		$post_id = wp_insert_post( $lesson_data );
		if ( is_wp_error( $post_id ) ) {
			return $this->response(
				$this->code_create,
				__( 'Lesson create failed', 'tutor-pro' ),
				$post_id->get_error_message(),
				$this->server_error_code
			);
		} elseif ( ! $post_id ) {
			return $this->response(
				$this->code_create,
				__( 'Lesson create failed', 'tutor-pro' ),
				$post_id,
				$this->client_error_code
			);
		} else {
			// Set post thumbnail.
			$this->update_lesson_thumbnail( $post_id, $params );

			return $this->response(
				$this->code_create,
				__( 'Lesson created successfully', 'tutor-pro' ),
				$post_id
			);
		}
	}

	/**
	 * Handle lesson update API request
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
				'lesson_content' => 'wp_kses_post',
			)
		);

		// Extract fillable fields.
		$params       = array_intersect_key( $params, array_flip( $this->fillable_fields ) );
		$params['ID'] = $request->get_param( 'id' );

		$params['preview'] = isset( $params['preview'] ) && true == $params['preview'] ? true : false;

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			$errors = $validation->errors;
		}

		// Validate video source if user set video.
		$this->validate_video_source( $params, $errors );

		if ( ! empty( $errors ) ) {
			return $this->response(
				$this->code_update,
				__( 'Lesson update failed', 'tutor-pro' ),
				$errors,
				$this->client_error_code
			);
		}

		$lesson_data = array(
			'ID' => $params['ID'],
		);

		if ( isset( $params['lesson_title'] ) ) {
			$lesson_data['post_title'] = $params['lesson_title'];
		}
		if ( isset( $params['lesson_content'] ) ) {
			$lesson_data['post_content'] = $params['lesson_content'];
		}
		if ( isset( $params['lesson_author'] ) ) {
			$lesson_data['post_author'] = $params['lesson_author'];
		}
		if ( isset( $params['topic_id'] ) ) {
			$lesson_data['post_parent'] = $params['post_parent'];
		}

		try {
			$this->prepare_post_meta( $params );
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Lesson update failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}

		$post_id = wp_update_post( $lesson_data );
		if ( is_wp_error( $post_id ) ) {
			return $this->response(
				$this->code_update,
				__( 'Lesson update failed', 'tutor-pro' ),
				$post_id->get_error_message(),
				$this->server_error_code
			);
		} else {

			// Update lesson thumb.
			$this->update_lesson_thumbnail( $post_id, $params );

			return $this->response(
				$this->code_update,
				__( 'Lesson update successfully', 'tutor-pro' ),
				$post_id
			);
		}
	}

	/**
	 * Prepare lesson meta data for update
	 *
	 * @since 2.6.0
	 *
	 * @param array $params params.
	 *
	 * @throws Exception Throw new exception.
	 *
	 * @return void
	 */
	private function prepare_post_meta( $params ) {
		if ( isset( $params['video'] ) ) {
			if ( ! empty( $params['video'] ) ) {
				$this->video_params['source'] = $params['video']['source_type'];

				$this->video_params[ 'source_' . $params['video']['source_type'] ] = $params['video']['source'];

				$this->video_params['runtime']['hours'] = $params['video']['runtime']['hours'] ?? 00;
				$this->video_params['runtime']['minutes'] = $params['video']['runtime']['minutes'] ?? 00;
				$this->video_params['runtime']['seconds'] = $params['video']['runtime']['seconds'] ?? 00;

				$_POST['video'] = $this->video_params;
			} else {
				$_POST['video'] = '-1';
			}
		}

		if ( isset( $params['attachments'] ) ) {
			$_POST['tutor_attachments'] = ! empty( $params['attachments'] ) ? $params['attachments'] : array();
		}
		if ( isset( $params['preview'] ) ) {
			$_POST['_is_preview'] = $params['preview'];
		}
	}

	/**
	 * Update lesson thumbnail
	 *
	 * @since 1.7.1
	 *
	 * @param int   $post_id lesson id.
	 * @param array $params params.
	 *
	 * @return void
	 */
	public function update_lesson_thumbnail( int $post_id, array $params ): void {
		// Update lesson thumb.
		if ( isset( $params['thumbnail_id'] ) ) {
			update_post_meta( $post_id, '_thumbnail_id', $params['thumbnail_id'] );
		}
	}

	/**
	 * Delete lesson
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$lesson_id = $request->get_param( 'id' );
		try {
			$delete = wp_delete_post( $lesson_id );
			if ( $delete ) {
				return $this->response(
					$this->code_delete,
					__( 'Lesson deleted successfully', 'tutor-pro' ),
					$lesson_id
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Lesson delete failed', 'tutor-pro' ),
					'',
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Lesson delete failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->server_error_code
			);
		}
	}

	/**
	 * Lesson mark as complete
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function lesson_mark_complete( WP_REST_Request $request ) {
		$params = Input::sanitize_array( $request->get_params() );

		$required_fields = array( 'course_id', 'lesson_id', 'student_id' );

		foreach ( $required_fields as $field ) {
			if ( ! isset( $params[ $field ] ) ) {
				$params[ $field ] = '';
			}
		}

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			return $this->response(
				$this->mark_complete,
				__( 'Lesson mark as complete failed', 'tutor-pro' ),
				$validation->errors,
				$this->client_error_code
			);
		}

		$is_enrolled = tutor_utils()->is_enrolled( $params['course_id'], $params['student_id'] );
		if ( $is_enrolled ) {
			try {
				LessonModel::mark_lesson_complete( $params['lesson_id'], $params['student_id'] );

				return $this->response(
					$this->mark_complete,
					__( 'Lesson mark as completed', 'tutor-pro' ),
				);
			} catch ( \Throwable $th ) {
				return $this->response(
					$this->mark_complete,
					__( 'Lesson mark as complete failed', 'tutor-pro' ),
					$th->getMessage(),
					$this->server_error_code
				);
			}
		} else {
			return $this->response(
				$this->mark_complete,
				__( 'Lesson mark as complete failed', 'tutor-pro' ),
				__( 'Student is not enrolled', 'tutor-pro' ),
				$this->client_error_code
			);
		}
	}

	/**
	 * Check if the video source type is valid
	 *
	 * @since 2.6.0
	 *
	 * @param string $source_type source type.
	 *
	 * @return boolean
	 */
	private function is_valid_video_source_type( string $source_type ): bool {
		// Unset embedded source.
		if ( tutor_is_rest() ) {
			unset( $this->supported_video_sources[4] );
		}

		return in_array( $source_type, $this->supported_video_sources, true );
	}

	/**
	 * Validate video source
	 *
	 * @since 2.6.0
	 *
	 * @param array $params array of params.
	 * @param array $errors array of errors.
	 *
	 * @return void
	 */
	public function validate_video_source( $params, &$errors ) {
		if ( isset( $params['video'] ) ) {
			$video_source_type = isset( $params['video']['source_type'] ) ? $params['video']['source_type'] : '';
			$video_source      = isset( $params['video']['source'] ) ? $params['video']['source'] : '';

			if ( '' === $video_source_type ) {
				$errors['video_source_type'] = __( 'Video source type is required', 'tutor-pro' );
			} else {
				if ( ! $this->is_valid_video_source_type( $video_source_type ) ) {
					$errors['video_source_type'] = __( 'Invalid video source type', 'tutor-pro' );
				}
			}

			if ( '' === $video_source ) {
				$errors['video_source'] = __( 'Video source is required', 'tutor-pro' );
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
			'ID'            => 'required|numeric',
			'course_id'     => 'required|numeric',
			'lesson_id'     => 'required|numeric',
			'student_id'    => 'required|numeric',
			'topic_id'      => "required|numeric|post_type:{$topic_type}",
			'lesson_title'  => 'required',
			'lesson_author' => 'required|user_exists',
			'attachments'   => 'is_array',
			'preview'       => 'boolean',
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

