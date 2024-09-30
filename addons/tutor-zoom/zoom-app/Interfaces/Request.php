<?php

/**
 * @copyright  https://github.com/UsabilityDynamics/zoom-api-php-client/blob/master/LICENSE
 */
namespace Zoom\Interfaces;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use TUTOR_ZOOM\Zoom;

class Request {

	/**
	 * API key
	 *
	 * @var string
	 */
	protected $apiKey;

	/**
	 * API secret
	 *
	 * @var string
	 */
	protected $apiSecret;

	/**
	 * Guzzle Client
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * @var string
	 */
	public $apiPoint = 'https://api.zoom.us/v2/';

	/**
	 * Access token transient key
	 *
	 * Store access token as transient data.
	 *
	 * Key will be appended by user id to make it unique
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	const ACCESS_TOKEN_KEY = 'tutor_zoom_access_token';

	/**
	 * Access token expire time in seconds
	 *
	 * @var string
	 *
	 * @since 2.2.0
	 */
	const TOKEN_EXP_TIME = 60 * 50; // 50min

	/**
	 * Request constructor.
	 *
	 * @param string $apiKey api key.
	 * @param string $apiSecret api secret.
	 */
	public function __construct( $apiKey, $apiSecret ) {
		$this->apiKey = $apiKey;

		$this->apiSecret = $apiSecret;

		$this->client = new Client();
	}

	/**
	 * Headers
	 *
	 * JWT token replaced by server to server oauth
	 * access token
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	protected function headers(): array {

		$user_id = get_current_user_id();

		$access_token = $this->get_access_token( $user_id );

		return array(
			'Authorization' => 'Bearer ' . $access_token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		);
	}


	/**
	 * Get
	 *
	 * @param string $method api endpoint.
	 * @param array  $fields form fields.
	 *
	 * @return array|mixed
	 */
	protected function get( $method, $fields = array() ) {
		try {
			$response = $this->client->request(
				'GET',
				$this->apiPoint . $method,
				array(
					'query'   => $fields,
					'headers' => $this->headers(),
				)
			);

			return $this->result( $response );

		} catch ( ClientException $e ) {

			return (array) json_decode( $e->getResponse()->getBody()->getContents() );
		}
	}

	/**
	 * Post
	 *
	 * @param string $method api endpoint.
	 * @param array  $fields form fields.
	 *
	 * @return array|mixed
	 */
	protected function post( $method, $fields ) {
		$body = \json_encode( $fields, JSON_PRETTY_PRINT );

		try {
			$response = $this->client->request(
				'POST',
				$this->apiPoint . $method,
				array(
					'body'    => $body,
					'headers' => $this->headers(),
				)
			);

			return $this->result( $response );

		} catch ( ClientException $e ) {

			return (array) json_decode( $e->getResponse()->getBody()->getContents() );
		}
	}

	/**
	 * Patch
	 *
	 * @param string $method api endpoint.
	 * @param array  $fields form fields.
	 *
	 * @return array|mixed
	 */
	protected function patch( $method, $fields ) {
		$body = \json_encode( $fields, JSON_PRETTY_PRINT );

		try {
			$response = $this->client->request(
				'PATCH',
				$this->apiPoint . $method,
				array(
					'body'    => $body,
					'headers' => $this->headers(),
				)
			);

			return $this->result( $response );

		} catch ( ClientException $e ) {

			return (array) json_decode( $e->getResponse()->getBody()->getContents() );
		}
	}

	/**
	 * Put
	 *
	 * @param string $method api endpoint.
	 * @param array  $fields form fields.
	 *
	 * @return array|mixed
	 */
	protected function put( $method, $fields ) {
		$body = \json_encode( $fields, JSON_PRETTY_PRINT );

		try {
			$response = $this->client->request(
				'PUT',
				$this->apiPoint . $method,
				array(
					'body'    => $body,
					'headers' => $this->headers(),
				)
			);

			return $this->result( $response );

		} catch ( ClientException $e ) {

			return (array) json_decode( $e->getResponse()->getBody()->getContents() );
		}
	}

	/**
	 * Delete
	 *
	 * @param string $method variable name is method but it's taking api endpoint string.
	 * @param array  $fields form fields array.
	 *
	 * @return array|mixed
	 */
	protected function delete( $method, $fields = array() ) {
		$body = \json_encode( $fields, JSON_PRETTY_PRINT );

		try {
			$response = $this->client->request(
				'DELETE',
				$this->apiPoint . $method,
				array(
					'body'    => $body,
					'headers' => $this->headers(),
				)
			);

			return $this->result( $response );

		} catch ( ClientException $e ) {

			return (array) json_decode( $e->getResponse()->getBody()->getContents() );
		}
	}

	/**
	 * Result
	 *
	 * @param Response $response API response.
	 *
	 * @return mixed
	 */
	protected function result( Response $response ) {
		$result = json_decode( (string) $response->getBody(), true );

		$result['code'] = $response->getStatusCode();

		return $result;
	}

	/**
	 * Get user's access token
	 *
	 * It will first try to get existing token if token not exists then
	 * it will generate a new token
	 *
	 * @since 2.2.0
	 *
	 * @param integer $user_id user id.
	 *
	 * @return string
	 */
	protected function get_access_token( int $user_id ) {
		$access_token = get_transient( self::ACCESS_TOKEN_KEY . $user_id );

		// If access token not exists generate one.
		if ( false === $access_token ) {
			$generate_token = $this->generate_access_token();

			if ( is_object( $generate_token ) && property_exists( $generate_token, 'access_token' ) ) {
				$access_token = $generate_token->access_token;

				// Set token in transient data.
				set_transient( self::ACCESS_TOKEN_KEY . $user_id, $access_token, self::TOKEN_EXP_TIME );
			}
		}
		return $access_token;
	}

	/**
	 * Since Zoom JWT Authenticated has become deprecated(from Jun 2023)
	 *
	 * To authenticate API request this method added. It will create
	 * server to server Oauth access token.
	 *
	 * Access token is valid for 1 hour & there is no refresh token. Once
	 * token expire it need to generate again.
	 *
	 * @since 2.2.0
	 *
	 * @return mixed array on success | false on failure
	 */
	private function generate_access_token() {
		$zoom_obj = new Zoom( false );

		$url = 'https://zoom.us/oauth/token?grant_type=account_credentials';

		$account_id = $zoom_obj->get_api( 'account_id' );

		$client_id     = $this->apiKey;
		$client_secret = $this->apiSecret;

		if ( ! $account_id || ! $client_id || ! $client_secret ) {
			return false;
		}

		$url = $url . '&account_id=' . $account_id;

		$encode   = base64_encode( $client_id . ':' . $client_secret );
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'ContentType'   => 'application/x-www-form-urlencoded',
					'Authorization' => 'Basic' . $encode,
				),
				'timeout' => 60,
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$res_body = json_decode( $response['body'] );
			return $res_body;
		}
		return false;
	}
}
