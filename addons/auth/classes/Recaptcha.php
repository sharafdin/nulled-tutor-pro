<?php
/**
 * Spam protection by Google reCAPTCHA
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

/**
 * Recaptcha Class.
 *
 * @since 2.1.9
 */
class Recaptcha {
	const VERSION_V2 = 'v2';
	const VERSION_V3 = 'v3';
	const ERROR_CODE = 'tutor_recaptcha_error';

	/**
	 * Load reCAPTCHA form based on version and site key.
	 *
	 * @since 2.1.9
	 *
	 * @param string $version reCAPTCHA version.
	 * @param string $site_key site key.
	 *
	 * @return void
	 */
	public static function form_content( $version, $site_key ) {
		if ( empty( $site_key ) ) {
			return;
		}

		/**
		 * reCAPTCHA v2 form content
		 */
		if ( self::VERSION_V2 === $version ) {
			?>
			<script src='https://www.google.com/recaptcha/api.js' async defer></script>
			<div class="g-recaptcha" 
				 style="margin-bottom: 15px;transform: scale(0.89);transform-origin:0 0;" 
				 data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
			<?php
		}

		/**
		 * reCAPTCHA v3 form content
		 */
		if ( self::VERSION_V3 === $version ) {
			?>
			<script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr( $site_key ); ?>"></script>
			
			<input type="hidden" id="recaptcha_token" name="recaptcha_token">
			<script>
				grecaptcha.ready(function() {
					grecaptcha.execute('<?php echo esc_attr( $site_key ); ?>', {action: 'form_submit'}).then(function(token) {
						document.getElementById('recaptcha_token').value = token;
					});
				});
			</script>
			<?php
		}
	}

	/**
	 * Verify reCAPTCHA response.
	 *
	 * @since 2.1.9
	 *
	 * @param string $response    captcha response.
	 * @param string $secret_key  secret key.
	 *
	 * @return void|\WP_Error
	 */
	public static function verify( $response, $secret_key ) {
		$url = 'https://www.google.com/recaptcha/api/siteverify';

		$data = array(
			'secret'   => $secret_key,
			'response' => $response,
		);

		$options = array(
			'http' => array(
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'method'  => 'POST',
				'content' => http_build_query( $data ),
			),
		);

		$context = stream_context_create( $options );
		$result  = file_get_contents( $url, false, $context );

		if ( $result === false ) {
			return new \WP_Error( self::ERROR_CODE, __( 'Something went wrong', 'tutor-pro' ) );
		}

		$result = json_decode( $result );
		if ( ! $result->success ) {
			return new \WP_Error( self::ERROR_CODE, __( 'Spam request catched by Google reCAPTCHA', 'tutor-pro' ) );
		}
	}
}
