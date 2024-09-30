<?php
/**
 * Rest request authentication
 *
 * @package TutorPro\RestAPI
 * @author  Themum<support@themeum.com>
 * @link    https://themeum.com
 * @since   2.6.0
 */

namespace TutorPro\RestAPI\Traits;

use Tutor\Helpers\QueryHelper;
use TUTOR\RestAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Request validation trait
 */
trait RequestValidation {

	/**
	 * Validate rest request before processing.
	 *
	 * @since 2.6.0
	 *
	 * @return boolean
	 */
	public function validate_write_request() {
		return $this->validate_request( array( RestAuth::WRITE, RestAuth::READ_WRITE, RestAuth::ALL ) );
	}

	/**
	 * Validate rest request before processing.
	 *
	 * @since 2.6.0
	 *
	 * @return boolean
	 */
	public function validate_read_request() {
		return $this->validate_request( array( RestAuth::READ, RestAuth::READ_WRITE, RestAuth::ALL ) );
	}

	/**
	 * Validate rest request before processing.
	 *
	 * @since 2.6.0
	 *
	 * @return boolean
	 */
	public function validate_delete_request() {
		return $this->validate_request( array( RestAuth::DELETE, RestAuth::ALL ) );
	}

	/**
	 * Validate rest request before processing.
	 *
	 * Check if the request is privileged for making create request
	 *
	 * @since 2.6.0
	 *
	 * @param array $permissions Array of permissions.
	 *
	 * @return boolean
	 */
	public function validate_request( array $permissions = array() ): bool {
		$credentials = $this->get_credentials();
		if ( is_null( $credentials['key'] ) || is_null( $credentials['secret'] ) ) {
			return false;
		}

		// Check whether the request is privileged for making create request.
		if ( ! in_array( $credentials['permission'], $permissions ) ) {
			return false;
		}

		// Validate api key and secret.
		return RestAuth::validate_api_key_secret( $credentials['key'], $credentials['secret'] );
	}

	/**
	 * Get api key, secret & permission from request headers
	 *
	 * @since 2.6.0
	 *
	 * @return array [key => key|null, secret => secret|null]
	 */
	public function get_credentials() {
		global $wpdb;

		$credentials = array(
			'key'        => null,
			'secret'     => null,
			'permission' => null,
		);

		$headers = tutor_getallheaders();

		if ( isset( $headers['Authorization'] ) ) {
			$authorization_header = $headers['Authorization'];

			if ( strpos( $authorization_header, 'Basic' ) !== false ) {
				$base_64_credentials = str_replace( 'Basic ', '', $authorization_header );
				$api_key_secrets     = base64_decode( $base_64_credentials );

				list($api_key, $api_secret) = explode( ':', $api_key_secrets );

				$credentials['key']    = $api_key;
				$credentials['secret'] = $api_secret;

				$api_key_secrets = QueryHelper::get_all(
					$wpdb->usermeta,
					array(
						'meta_key' => RestAuth::KEYS_USER_META_KEY,
					),
					'umeta_id'
				);

				if ( is_array( $api_key_secrets ) && count( $api_key_secrets ) ) {
					foreach ( $api_key_secrets as $value ) {
						$meta_value = json_decode( $value->meta_value );
						try {
							if ( $credentials['key'] === $meta_value->key && $credentials['secret'] === $meta_value->secret ) {
								$credentials['permission'] = $meta_value->permission;
								break;
							}
						} catch ( \Throwable $th ) {
							tutor_log( $th->getMessage() );
						}
					}
				}
			}
		}

		return $credentials;
	}
}
