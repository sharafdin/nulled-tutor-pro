<?php
/**
 * Base Controller
 *
 * @package TutorPro\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.6.0
 */

namespace TutorPro\RestAPI\Controllers;

use TutorPro\RestAPI\Traits\RequestValidation;
use TutorPro\RestAPI\Traits\RestResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Controller
 */
class BaseController {

	/**
	 * Request validation trait
	 *
	 * @since 2.6.0
	 */
	use RequestValidation;

	/**
	 * Rest response trait
	 *
	 * @since 2.6.0
	 */
	use RestResponse;

	/**
	 * Operation code that would be resolved from the inherited class
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $operation = '';

	/**
	 * Operation create code
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $code_create = 'tutor_create_';

	/**
	 * Operation read code
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $code_read = 'tutor_read_';

	/**
	 * Operation update code
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $code_update = 'tutor_update_';

	/**
	 * Operation delete code
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $code_delete = 'tutor_delete_';

	/**
	 * Course or content mark as complete code
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $mark_complete = 'tutor_{}_mark_complete';

	/**
	 * Video sources
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $supported_video_sources = array(
		'external_url',
		'shortcode',
		'youtube',
		'vimeo',
		'embedded',
	);

	/**
	 * Video params
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $video_params = array(
		'source'              => '',
		'source_video_id'     => '',
		'poster'              => '',
		'source_external_url' => '',
		'source_shortcode'    => '',
		'source_youtube'      => '',
		'source_vimeo'        => '',
		'source_embedded'     => '',
	);

	/**
	 * Resolve dependencies
	 *
	 * @since 2.6.0
	 */
	public function __construct() {
		$this->code_create   = $this->code_create . $this->operation;
		$this->code_read     = $this->code_read . $this->operation;
		$this->code_update   = $this->code_update . $this->operation;
		$this->code_delete   = $this->code_delete . $this->operation;
		$this->mark_complete = str_replace( '{}', $this->operation, $this->mark_complete );
	}

	/**
	 * Setup post thumbnail
	 *
	 * @since 2.6.0
	 *
	 * @param int   $post_id Post ID.
	 * @param array $params Params. Need to set thumbnail_id key in the param for setting up thumbnail.
	 */
	public function setup_post_thumbnail( int $post_id, array $params ) {
		if ( isset( $params['thumbnail_id'] ) && $params['thumbnail_id'] > 0 ) {
			set_post_thumbnail( $post_id, $params['thumbnail_id'] );
		}
	}

	/**
	 * Setup required fields on the request params
	 *
	 * @since 2.6.0
	 *
	 * @param array $params Reference request params.
	 * @param array $required_fields Required fields.
	 *
	 * @return void
	 */
	public function setup_required_fields( array &$params, $required_fields ) {
		foreach ( $required_fields as $field ) {
			if ( ! isset( $params[ $field ] ) ) {
				$params[ $field ] = '';
			}
		}
	}

	/**
	 * Send validation error response
	 *
	 * @since 2.7.0
	 *
	 * @param array  $errors errors.
	 * @param mixed  $code code.
	 * @param string $message message.
	 *
	 * @return mixed
	 */
	public function validation_error_response( $errors, $code, $message = null ) {
		$default_message = _n( 'Invalid input', 'Invalid inputs', count( $errors ), 'tutor-pro' );
		return $this->response(
			$code,
			$message ? $message : $default_message,
			$errors,
			$this->client_error_code
		);
	}
}

