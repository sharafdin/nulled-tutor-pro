<?php
/**
 * Service Class for Twitter Oauth V1.0
 *
 * @package TutorPro\SocialLogin\Authentication
 * @author Themeum <support@themeum.com>
 * @since 2.1.10
 */

namespace TutorPro\SocialLogin\Lib;

/**
 * Class TwitterOauthService
 *
 * @since 2.1.10
 */
class TwitterOauthService {

	/**
	 * Consumer Key
	 *
	 * @var string
	 */
	private $consumer_key;

	/**
	 * Consumer Secret
	 *
	 * @var string
	 */
	private $consumer_secret;

	/**
	 * Callback URL
	 *
	 * @var string
	 */
	private $oauth_callback;

	/**
	 * Signature method
	 *
	 * @var string
	 */
	private $signature_method = 'HMAC-SHA1';

	/**
	 * Oauth version
	 *
	 * @var string
	 */
	private $oauth_version = '1.0';

	/**
	 * HTTP status
	 *
	 * @var string
	 */
	private $http_status = '';

	/**
	 * Constrict function
	 *
	 * @param string $consumer_key App key.
	 * @param string $consumer_secret App secret key.
	 * @param string $oauth_callback Redirect URL.
	 *
	 * @return void
	 */
	public function __construct( string $consumer_key, string $consumer_secret, string $oauth_callback ) {
		$this->consumer_key    = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->oauth_callback  = $oauth_callback;
	}

	/**
	 * Get Oauth Verify
	 *
	 * @return string
	 */
	public function get_oauth_verifier() {
		$request_response = $this->get_request_token();
		$auth_url         = 'https://api.twitter.com/oauth/authenticate';
		$redirect_url     = $auth_url . '?oauth_token=' . $request_response['request_token'];

		return $redirect_url;
	}

	/**
	 * Get Request Token
	 *
	 * @return array
	 */
	public function get_request_token() {
		$url = 'https://api.twitter.com/oauth/request_token';

		$params = array(
			'oauth_callback'         => $this->oauth_callback,
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_nonce'            => $this->get_token( 42 ),
			'oauth_signature_method' => $this->signature_method,
			'oauth_timestamp'        => time(),
			'oauth_version'          => $this->oauth_version,
		);

		$params['oauth_signature'] = $this->create_signature( 'POST', $url, $params );

		$oauth_header = $this->generate_oauth_header( $params );

		$response = $this->curl_http( 'POST', $url, $oauth_header );

		$response_variables = array();
		parse_str( $response, $response_variables );

		$token_response = array();

		$token_response['request_token']        = $response_variables['oauth_token'] ?? '';
		$token_response['request_token_secret'] = $response_variables['oauth_token_secret'] ?? '';

		if ( ! session_id() ) {
			session_start();
		}

		$_SESSION['oauth_token']        = $token_response['request_token'];
		$_SESSION['oauth_token_secret'] = $token_response['request_token_secret'];
		session_write_close();

		return $token_response;
	}

	/**
	 * Get Access Token
	 *
	 * @param string $oauth_verifier Oauth verifier.
	 * @param string $oauth_token Oauth token.
	 * @param string $oauth_token_secret Oauth token secret.
	 *
	 * @return array
	 */
	public function get_access_token( $oauth_verifier, $oauth_token, $oauth_token_secret ) {
		$url = 'https://api.twitter.com/oauth/access_token';

		$oauth_post_data = array(
			'oauth_verifier' => $oauth_verifier,
		);

		$params = array(
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_nonce'            => $this->get_token( 42 ),
			'oauth_signature_method' => $this->signature_method,
			'oauth_timestamp'        => time(),
			'oauth_token'            => $oauth_token,
			'oauth_version'          => $this->oauth_version,
		);

		$params['oauth_signature'] = $this->create_signature( 'POST', $url, $params, $oauth_token_secret );

		$oauth_header = $this->generate_oauth_header( $params );

		$response = $this->curl_http( 'POST', $url, $oauth_header, $oauth_post_data );

		$response_variables = array();
		parse_str( $response, $response_variables );

		$token_response                        = array();
		$token_response['access_token']        = $response_variables['oauth_token'] ?? '';
		$token_response['access_token_secret'] = $response_variables['oauth_token_secret'] ?? '';

		return $token_response;
	}

	/**
	 * Get User Data
	 *
	 * @param string $oauth_verifier Oauth verifier.
	 * @param string $oauth_token Oauth token.
	 * @param string $oauth_token_secret Oauth token secret.
	 *
	 * @return string
	 */
	public function get_user_data( $oauth_verifier, $oauth_token, $oauth_token_secret ) {
		$access_token_response = $this->get_access_token( $oauth_verifier, $oauth_token, $oauth_token_secret );

		$url           = 'https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true';
		$signature_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';

		$params = array(
			'include_email'          => 'true',
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_nonce'            => $this->get_token( 42 ),
			'oauth_signature_method' => $this->signature_method,
			'oauth_timestamp'        => time(),
			'oauth_token'            => $access_token_response['access_token'],
			'oauth_version'          => $this->oauth_version,
		);

		$params['oauth_signature'] = $this->create_signature( 'GET', $signature_url, $params, $access_token_response['access_token_secret'] );

		$oauth_header = $this->generate_oauth_header( $params );

		$response = $this->curl_http( 'GET', $url, $oauth_header );

		return $response;
	}

	/**
	 * Curl HTTP function
	 *
	 * @param string $http_request_method Request method.
	 * @param string $url Request URL.
	 * @param string $oauth_header Oauth header.
	 * @param mixed  $post_data Post data.
	 *
	 * @return mixed
	 */
	public function curl_http( $http_request_method, $url, $oauth_header, $post_data = null ) {

		$ch = curl_init();

		$headers = array(
			'Authorization: OAuth ' . $oauth_header,
		);

		$options = array(
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_HEADER         => false,
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
		);
		if ( 'POST' === $http_request_method ) {
			$options[ CURLOPT_POST ] = true;
		}
		if ( ! empty( $post_data ) ) {
			$options[ CURLOPT_POSTFIELDS ] = $post_data;
		}
		curl_setopt_array( $ch, $options );
		$response = curl_exec( $ch );

		$this->http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		return $response;
	}

	/**
	 * Generate Oauth header
	 *
	 * @param array $params Parameters.
	 *
	 * @return string
	 */
	public function generate_oauth_header( $params ) {
		foreach ( $params as $k => $v ) {

			$oauth_param_array[] = $k . '="' . rawurlencode( $v ) . '"';
		}
		$oauth_header = implode( ', ', $oauth_param_array );

		return $oauth_header;
	}

	/**
	 * Create Signature
	 *
	 * @param string $http_request_method Request method.
	 * @param string $url Request URL.
	 * @param array  $params Parameters.
	 * @param string $token_secret Token secret.
	 *
	 * @return string
	 */
	public function create_signature( $http_request_method, $url, $params, $token_secret = '' ) {
		$str_params = rawurlencode( http_build_query( $params ) );

		$base_string = $http_request_method . '&' . rawurlencode( $url ) . '&' . $str_params;

		$sign_key        = $this->generate_signature_key( $token_secret );
		$oauth_signature = base64_encode( hash_hmac( 'sha1', $base_string, $sign_key, true ) );

		return $oauth_signature;
	}

	/**
	 * Generate Signature Key
	 *
	 * @param string $token_secret Token secret.
	 *
	 * @return string
	 */
	public function generate_signature_key( $token_secret ) {
		$sign_key = rawurlencode( $this->consumer_secret ) . '&';
		if ( ! empty( $token_secret ) ) {
			$sign_key = $sign_key . rawurlencode( $token_secret );
		}
		return $sign_key;
	}

	/**
	 * Get Token
	 *
	 * @param int $length Token length.
	 *
	 * @return string
	 */
	public function get_token( $length ) {
		$token          = '';
		$code_alphabet  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code_alphabet .= 'abcdefghijklmnopqrstuvwxyz';
		$code_alphabet .= '0123456789';
		$max            = strlen( $code_alphabet ) - 1;
		for ( $i = 0; $i < $length; $i ++ ) {
			$token .= $code_alphabet[ $this->crypto_rand_secure( 0, $max ) ];
		}
		return $token;
	}

	/**
	 * Crypto rand secure
	 *
	 * @param mixed $min Min value.
	 * @param mixed $max Max value.
	 *
	 * @return mixed
	 */
	public function crypto_rand_secure( $min, $max ) {
		$range = $max - $min;
		if ( $range < 1 ) {
			return $min; // not so random...
		}
		$log    = ceil( log( $range, 2 ) );
		$bytes  = (int) ( $log / 8 ) + 1; // length in bytes.
		$bits   = (int) $log + 1; // length in bits.
		$filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1.
		do {
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter; // discard irrelevant bits.
		} while ( $rnd >= $range );
		return $min + $rnd;
	}
}
