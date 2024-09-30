<?php
/**
 * Ensure rest response
 *
 * @package TutorPro\RestAPI
 * @author  Themum<support@themeum.com>
 * @link    https://themeum.com
 * @since   2.6.0
 */

namespace TutorPro\RestAPI\Traits;

use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait RestResponse {

	/**
	 * Success status code
	 *
	 * @var integer
	 */
	public $success_code = 200;

	/**
	 * Client side error status code
	 *
	 * @var integer
	 */
	public $client_error_code = 400;

	/**
	 * Server side error status code
	 *
	 * @var integer
	 */
	public $server_error_code = 500;

	/**
	 * Validate rest request before processing.
	 *
	 * @since 2.6.0
	 *
	 * @param string $code operation code name.
	 * @param string $message response message.
	 * @param mixed  $data response data.
	 * @param int    $status_code response status code.
	 *
	 * @return rest_ensure_response
	 */
	public function response( $code, string $message, $data = '', int $status_code = 200 ) {
		if ( $this->client_error_code === $status_code || $this->server_error_code === $status_code ) {
			$response = new WP_REST_Response(
				array(
					'code'    => $code,
					'message' => $message,
					'data'    => array(
						'status'  => $status_code,
						'details' => $data,
					),
				),
				$status_code
			);
		} else {
			$response = new WP_REST_Response(
				array(
					'code'    => $code,
					'message' => $message,
					'data'    => $data,
				),
				$status_code
			);
		}

		return rest_ensure_response( apply_filters( 'tutor_rest_api_response', $response ) );
	}
}
