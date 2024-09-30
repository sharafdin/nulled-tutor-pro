<?php
/**
 * Topic Controller
 *
 * Manage API for topic
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.0
 */

namespace TutorPro\RestAPI\Controllers;

use Tutor\Helpers\ValidationHelper;
use TUTOR\Input;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Topic Controller
 */
class TopicController extends BaseController {

	/**
	 * Operation codes
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $operation = 'topic';

	/**
	 * Fillable fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $fillable_fields = array(
		'topic_course_id',
		'topic_title',
		'topic_summary',
		'topic_author',
	);

	/**
	 * Required fields
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	private $required_fields = array(
		'topic_course_id',
		'topic_title',
		'topic_author',
	);

	/**
	 * Topic post type
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

		$this->post_type = tutor()->topics_post_type;
	}

	/**
	 * Handle topic create API request
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'topic_summary' => 'esc_textarea',
			)
		);

		// Extract fillable fields.
		$params = array_intersect_key( $params, array_flip( $this->fillable_fields ) );

		$params['post_type'] = $this->post_type;

		// Set empty value if required fields are not set.
		$this->setup_required_fields( $params, $this->required_fields );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			return $this->response(
				$this->code_create,
				__( 'Topic create failed', 'tutor-pro' ),
				$validation->errors,
				$this->client_error_code
			);
		}

		// Create topics.
		try {
			$post_arr = array(
				'post_type'    => $this->post_type,
				'post_title'   => $params['topic_title'],
				'post_content' => $params['topic_summary'],
				'post_status'  => 'publish',
				'post_author'  => $params['topic_author'],
				'post_parent'  => $params['topic_course_id'],
			);

			$post_id = wp_insert_post( $post_arr );
			if ( $post_id ) {
				return $this->response(
					$this->code_create,
					__( 'Topic created successfully', 'tutor-pro' ),
					$post_id
				);
			} else {
				return $this->response(
					$this->code_create,
					__( 'Topic create failed', 'tutor-pro' ),
					$post_id,
					$this->client_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_create,
				__( 'Topic create failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Handle topic update API request
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request request obj.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update( WP_REST_Request $request ) {

		// Get params and sanitize it.
		$params = Input::sanitize_array(
			$request->get_params(),
			array(
				'topic_summary' => 'esc_textarea',
			)
		);

		// Extract fillable fields.
		$params       = array_intersect_key( $params, array_flip( $this->fillable_fields ) );
		$params['ID'] = $request->get_param( 'id' );

		// Validate request.
		$validation = $this->validate( $params );
		if ( ! $validation->success ) {
			return $this->response(
				$this->code_update,
				__( 'Topic update failed', 'tutor-pro' ),
				$validation->errors,
				$this->client_error_code
			);
		}

		// Topic meta fields.
		try {
			$post_arr = array(
				'ID' => $params['ID'],
			);

			// Set post field to update.
			if ( isset( $params['topic_title'] ) ) {
				$post_arr['post_title'] = $params['topic_title'];
			}
			if ( isset( $params['topic_summary'] ) ) {
				$post_arr['post_content'] = $params['topic_summary'];
			}
			if ( isset( $params['topic_author'] ) ) {
				$post_arr['post_author'] = $params['topic_author'];
			}
			if ( isset( $params['topic_course_id'] ) ) {
				$post_arr['post_parent'] = $params['topic_course_id'];
			}

			$post_id = wp_update_post( $post_arr );
			if ( is_wp_error( $post_id ) ) {
				return $this->response(
					$this->code_update,
					__( 'Topic update failed', 'tutor-pro' ),
					$post_id->get_error_message(),
					$this->server_error_code
				);
			} elseif ( ! $post_id ) {
				return $this->response(
					$this->code_update,
					__( 'Topic update failed', 'tutor-pro' ),
					$post_id,
					$this->server_error_code
				);
			} else {
				return $this->response(
					$this->code_update,
					__( 'Topic update successfully', 'tutor-pro' ),
					$post_id
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_update,
				__( 'Topic update failed', 'tutor-pro' ),
				$th->getMessage(),
				$this->client_error_code
			);
		}
	}

	/**
	 * Delete topic
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request params.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		$topic_id = $request->get_param( 'id' );
		try {
			$delete = wp_delete_post( $topic_id );
			if ( $delete ) {
				return $this->response(
					$this->code_delete,
					__( 'Topic deleted successfully', 'tutor-pro' ),
					$delete
				);
			} else {
				return $this->response(
					$this->code_delete,
					__( 'Topic delete failed', 'tutor-pro' ),
					$topic_id,
					$this->server_error_code
				);
			}
		} catch ( \Throwable $th ) {
			return $this->response(
				$this->code_delete,
				__( 'Topic trash failed', 'tutor-pro' ),
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
		$validation_rules = array(
			'ID'              => 'required|numeric',
			'topic_course_id' => 'required|numeric',
			'topic_title'     => 'required',
			'topic_author'    => 'required|user_exists',
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

