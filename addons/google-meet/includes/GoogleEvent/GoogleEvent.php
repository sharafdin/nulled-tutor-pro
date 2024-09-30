<?php
/**
 * Manage Google Event operations
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\GoogleEvent
 */

namespace TutorPro\GoogleMeet\GoogleEvent;

use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Validator\Validator;

/**
 * Manage google events
 */
class GoogleEvent {

	/**
	 * API credential path
	 *
	 * @var string
	 */
	private $credential_path = null;

	/**
	 * Access token path
	 *
	 * @var string
	 */
	private $token_path = null;

	/**
	 * Access token path
	 *
	 * @var string
	 */
	public $upload_dir = null;

	/**
	 * Init google service
	 *
	 * @var mixed
	 */
	public $service;

	/**
	 * Authorized client
	 *
	 * @var mixed
	 */
	public $client;

	/**
	 * App name
	 *
	 * @var string
	 */
	private $app_name;

	/**
	 * Redirect URI
	 *
	 * @var string
	 */
	public $google_callback_url;

	/**
	 * Set current username
	 *
	 * @var string
	 */
	public $username;

	/**
	 * Scopes required to make API request
	 *
	 * @var array
	 */
	private $required_scopes = array(
		\Google_Service_Calendar::CALENDAR,
		\Google_Service_Calendar::CALENDAR_EVENTS,
		// 'https://www.googleapis.com/auth/userinfo.email',
	);

	/**
	 * Current calendar type
	 *
	 * @var string
	 */
	public $current_calendar;

	/**
	 * Init props & resolve dependencies
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		$owner_id               = null;
		$current_calendar       = 'primary';
		$this->current_calendar = $current_calendar;
		$uploads_dir            = trailingslashit( wp_upload_dir()['basedir'] ) . 'tutor-json/';
		wp_mkdir_p( $uploads_dir );
		$this->upload_dir = $uploads_dir;

		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include ABSPATH . 'wp-includes/pluggable.php';
		}

		$this->username = md5( \wp_get_current_user()->user_login );

		$credential_path = $this->upload_dir . "{$this->username}-credential.json";
		$token_path      = $this->upload_dir . "{$this->username}-token.json";

		// Create index.php file to restrict direct access.
		if ( ! file_exists( $this->upload_dir . 'index.php' ) ) {
			file_put_contents( $this->upload_dir . 'index.php', '<?php //silence is golden' );
		}

		if ( file_exists( $credential_path ) ) {
			$this->credential_path = $credential_path;
		}
		if ( file_exists( $token_path ) ) {
			$this->token_path = $token_path;
		}

		$this->google_callback_url = admin_url() . 'admin.php?page=google-meet&tab=set-api';

		if ( $this->is_credential_loaded() ) {
			try {
				$this->validate_json_service_account_file( $credential_path );

				$this->client = new \Google_Client();
				$this->client->setApplicationName( $this->app_name );
				$this->client->setAuthConfig( $this->credential_path );
				$this->client->setRedirectUri( $this->google_callback_url );
				$this->client->addScope( $this->required_scopes );
				$this->client->setAccessType( 'offline' );
				$this->client->setApprovalPrompt( 'force' );
				$assigned = ! ( $this->assign_token_to_client() === false );

				if ( $assigned ) {
					// Create service if the token assigned.
					$this->service = new \Google_Service_Calendar( $this->client );
				}
			} catch ( \Throwable $th ) {
				if ( file_exists( $this->credential_path ) ) {
					unlink( $this->credential_path );
				}

				if ( is_admin() ) {
					add_action(
						'admin_notices',
						function() use ( $th ) {
							printf(
								'<div class="%1$s"><p>%2$s</p></div>',
								esc_attr( 'notice notice-error is-dismissible' ),
								esc_html( $th->getMessage() )
							);
						}
					);
				}
			}
		}

		add_action( 'wp_ajax_tutor_pro_google_meet_credential_upload', array( $this, 'upload_credentials' ) );
	}

	/**
	 * Check valid service account JSON config file
	 *
	 * @param string $file_path file path.
	 * @return boolean
	 * @throws \Exception If invalid file or file does not exist.
	 *
	 * @since 2.1.3
	 */
	public function validate_json_service_account_file( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			throw new \Exception( __( 'File does not exist', 'tutor-pro' ) );
		}

		$data = file_get_contents( $file_path );
		$json = json_decode( $data );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \Exception( __( 'Invalid JSON file', 'tutor-pro' ) );
		}

		if ( ! isset( $json->web ) ) {
			throw new \Exception( __( 'Invalid config file', 'tutor-pro' ) );
		}

		$required_key = array(
			'client_id',
			'client_secret',
			'project_id',
			'auth_uri',
			'token_uri',
		);

		$config_arr = (array) $json->web;

		foreach ( $required_key as $key ) {
			if ( ! array_key_exists( $key, $config_arr ) ) {
				throw new \Exception( $key . __( ' does not exist in your JSON file', 'tutor-pro' ) );
			}
		}

		return true;
	}

	/**
	 * Return consent screen url
	 *
	 * @since v2.1.0
	 *
	 * @return string  consent screen URL
	 */
	public function get_consent_screen_url() {
		return $this->client->createAuthUrl();
	}

	/**
	 * Save JSON credentials
	 *
	 * @since v2.1.0
	 *
	 * @param string $file  $_FILES.
	 *
	 * @return void
	 */
	public function upload_credentials( $file ) {
		if ( Validator::current_user_has_access() ) {

			$credential_path = $this->upload_dir . "{$this->username}-credential.json";

			try {
				$file   = $_FILES['file'];
				$upload = move_uploaded_file( $file['tmp_name'], $credential_path );

				$this->validate_json_service_account_file( $credential_path );

				if ( $upload ) {
					wp_send_json_success( __( 'Credential uploaded successfully!', 'tutor-pro' ) );
				} else {
					wp_send_json_error( __( 'Credential upload failed, please try again!', 'tutor-pro' ) );
				}
			} catch ( \Throwable $th ) {

				if ( file_exists( $credential_path ) ) {
					unlink( $credential_path );
				}

				wp_send_json_error( $th->getMessage() );
			}
		} else {
			wp_send_json_error( __( 'You are not authorized to perform this operation', 'tutor-pro' ) );
		}
	}

	/**
	 * Check if credentials available
	 *
	 * @since v2.1.0
	 *
	 * @return bool
	 */
	public function is_credential_loaded() {
		return file_exists( $this->upload_dir . "{$this->username}-credential.json" );
	}

	/**
	 * Assign the existing token, or try to refresh if expired
	 *
	 * @since v2.1.0
	 *
	 * @return mixed
	 */
	public function assign_token_to_client() {
		try {
			if ( isset( $this->token_path ) && file_exists( $this->token_path ) ) {
				$access_token = json_decode( file_get_contents( $this->token_path ), true );
				$this->client->setAccessToken( $access_token );
			}
			// Check if token expired.
			if ( $this->client->isAccessTokenExpired() ) {
				$refresh_token = $this->client->getRefreshToken();

				if ( ! $refresh_token ) {
					return false;
				}

				$new_token = null;

				try {
					$new_token = $this->client->fetchAccessTokenWithRefreshToken( $refresh_token );
				} catch ( \Exception $e ) {
					if ( $e ) {
						return false;
					}
				}
				return $this->save_token( null, $new_token );
			}
		} catch ( \Throwable $th ) {
			return false;
		}
	}


	/**
	 * Save token provided by google
	 *
	 * @since v2.1.0
	 *
	 * @param mixed $code  google after authenticated.
	 * @param mixed $token access token.
	 */
	public function save_token( $code = null, $token = null ) {
		if ( Validator::current_user_has_access() ) {
			try {
				if ( ! $token ) {
					$token = $this->client->fetchAccessTokenWithAuthCode( $code );
					$this->client->setAccessToken( $token );
					$token = $this->client->getAccessToken();
				}
				file_put_contents( $this->upload_dir . "{$this->username}-token.json", json_encode( $token ) );
			} catch ( \Throwable $th ) {
				return false;
			}
		}
		return false;
	}


	/**
	 * Check if the app is permitted by user via consent screen
	 *
	 * @since v2.1.0
	 *
	 * @return bool
	 */
	public function is_app_permitted() {
		if ( is_null( $this->credential_path ) || is_null( $this->token_path ) ) {
			return false;
		}
		return $this->assign_token_to_client() === false ? false : true;
	}
}
