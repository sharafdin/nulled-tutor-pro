<?php
/**
 * Handle Ajax Request.
 *
 * @package TutorPro\ChatGPT
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.8
 */

namespace TutorPro\ChatGPT;

use TUTOR\Input;

/**
 * Ajax Class.
 *
 * @since 2.1.8
 */
class Ajax {
	/**
	 * Register hooks.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_tutor_pro_chatgpt', array( $this, 'handle_chatgpt' ) );
		add_action( 'wp_ajax_tutor_pro_chatgpt_save_settings', array( $this, 'save_settings' ) );
	}

	/**
	 * Save ChatGPT settings from frontend.
	 * Only admin can set API key from frontend.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function save_settings() {
		tutor_utils()->checking_nonce();

		if ( false === current_user_can( 'administrator' ) ) {
			$this->send_error_message( __( 'Permission denined', 'tutor-pro' ) );
		}

		$chatgpt_enable = Input::post( 'chatgpt_enable', true, Input::TYPE_BOOL );
		$api_key        = Input::post( 'api_key', '' );

		if ( $chatgpt_enable && empty( $api_key ) ) {
			$this->send_error_message( __( 'API key required', 'tutor-pro' ) );
		}

		$options        = get_option( 'tutor_option' );
		$chatgpt_enable = $chatgpt_enable ? 'on' : 'off';
		if ( false === $options ) {
			$options = array(
				Settings::CHATGPT_API_KEY => $api_key,
				Settings::CHATGPT_ENABLE  => $chatgpt_enable,
			);
		}

		$options[ Settings::CHATGPT_API_KEY ] = $api_key;
		$options[ Settings::CHATGPT_ENABLE ]  = $chatgpt_enable;

		update_option( 'tutor_option', $options );
		wp_send_json_success( array( 'message' => __( 'API key saved successfully!', 'tutor-pro' ) ) );

	}

	/**
	 * Send error message as response
	 *
	 * @since 2.1.8
	 *
	 * @param string $message error message.
	 *
	 * @return void JSON response.
	 */
	private function send_error_message( $message ) {
		wp_send_json_error( array( 'message' => $message ) );
	}

	/**
	 * Handle ChatGPT prompt.
	 *
	 * @since 2.1.8
	 *
	 * @return void JSON response.
	 */
	public function handle_chatgpt() {
		tutor_utils()->checking_nonce();

		$has_permission = current_user_can( 'tutor_instructor' ) || current_user_can( 'administrator' );
		if ( ! $has_permission ) {
			$this->send_error_message( __( 'Permission denined', 'tutor-pro' ) );
		}

		$chatgpt_enable = (bool) tutils()->get_option( Settings::CHATGPT_ENABLE, true );
		if ( false === $chatgpt_enable ) {
			$this->send_error_message( __( 'Invalid Request', 'tutor-pro' ) );
		};

		$prompt = Input::post( 'prompt' );
		if ( empty( trim( $prompt ) ) ) {
			$this->send_error_message( __( 'Prompt is required', 'tutor-pro' ) );
		}

		$api_key = get_tutor_option( Settings::CHATGPT_API_KEY, '' );
		if ( empty( trim( $api_key ) ) ) {
			$this->send_error_message( __( 'API key is required', 'tutor-pro' ) );
		}

		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $api_key,
		);

		$data = array(
			'prompt'      => $prompt,
			'max_tokens'  => 1024,
			'temperature' => 0.7,
		);

		$args = array(
			'method'  => 'POST',
			'body'    => wp_json_encode( $data ),
			'timeout' => 60,
			'headers' => $headers,
		);

		/**
		 * Model `text-davinci-003` deprecated on Jan 4, 24
		 *
		 * @since 2.6.1 model updated.
		 * @see https://stackoverflow.com/questions/77789886/openai-api-error-the-model-text-davinci-003-has-been-deprecated
		 */
		$model    = 'gpt-3.5-turbo-instruct';
		$url      = 'https://api.openai.com/v1/engines/' . $model . '/completions';
		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			$this->send_error_message( $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$res_body    = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 === $status_code ) {
			$gpt_res = tutils()->avalue_dot( 'choices.0.text', $res_body );
			wp_send_json_success( array( 'text' => trim( $gpt_res ) ) );
		} else {
			$error = null;
			switch ( $status_code ) {
				case 400:
					$error = __( 'Bad Request', 'tutor-pro' );
					break;
				case 401:
					$error = __( 'Invalid ChatGPT API key', 'tutor-pro' );
					break;
				case 403:
					$error = __( 'Forbidden', 'tutor-pro' );
					break;
				case 404:
					$error = __( 'Resource not found', 'tutor-pro' );
					break;
				case 429:
					$error = __( 'You exceeded your current ChatGPT usage quota', 'tutor-pro' );
					break;
				default:
					$error = tutils()->avalue_dot( 'error.message', $res_body );
					break;
			}

			$this->send_error_message( $error );
		}
	}
}
