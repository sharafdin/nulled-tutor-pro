<?php
/**
 * Handle 2FA Login logic.
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

use Tutor\Helpers\SessionHelper;
use TUTOR\Input;

/**
 * Two Factor Auth Class.
 *
 * @since 2.1.9
 */
class _2FA {

	const MINUTE_IN_SECONDS = 60;

	/**
	 * Register hooks.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function __construct() {
		/**
		 * Hook `template_redirect` to `template_include`
		 * for elementor custom header footer support.
		 *
		 * @since 2.4.0
		 */
		add_filter( 'template_include', array( $this, 'get_login_otp_page' ), 999 );

		add_filter( 'wp_authenticate_user', array( $this, 'check_login' ), 11, 2 );
		add_action( 'wp_ajax_nopriv_tutor_verify_login_otp', array( $this, 'verify_login_otp' ) );
	}

	/**
	 * OTP verify page
	 *
	 * @since 2.1.9
	 *
	 * @param string $template template path.
	 *
	 * @return string template path.
	 */
	public function get_login_otp_page( $template ) {
		if ( 'tutor-2fa' === Input::get( 'step' ) && null !== SessionHelper::get( 'tutor_login_otp' ) ) {
			$template = tutor_auth()->views . 'login-otp.php';
			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		return $template;
	}

	/**
	 * Get OTP page URL.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function get_login_otp_page_url() {
		return get_home_url() . '?step=tutor-2fa';
	}

	/**
	 * E-mail OTP handler.
	 *
	 * @since 2.1.9
	 *
	 * @param \WP_User $user user object.
	 *
	 * @return void
	 */
	private function handle_email_otp( \WP_User $user ) {
		$otp = Utils::generate_otp();

		$data           = new \stdClass();
		$data->code     = $otp;
		$data->user     = $user;
		$data->remember = Input::has( 'rememberme' );

		SessionHelper::set( 'tutor_login_otp', $data );
		SessionHelper::set( 'resent_otp_at', time() + self::MINUTE_IN_SECONDS );
		Utils::sent_login_otp( $user->user_email, $otp );

		wp_safe_redirect( $this->get_login_otp_page_url() );
		exit;
	}

	/**
	 * Check login.
	 *
	 * @since 2.1.9
	 *
	 * @param mixed  $user      user object or WP Error.
	 * @param string $password  provided password.
	 *
	 * @return void
	 */
	public function check_login( $user, $password ) {

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		if ( wp_check_password( $password, $user->user_pass ) ) {
			$enabled = Settings::is_2fa_enabled();
			if ( ! $enabled ) {
				return $user;
			}

			$location = Settings::get_2fa_location();
			$method   = Settings::get_2fa_method();

			if ( 'email' === $method ) {
				if ( 'both' === $location ) {
					return $this->handle_email_otp( $user );
				}

				if ( 'wp_login' === $location && Utils::is_request_from_wp_login() ) {
					return $this->handle_email_otp( $user );
				}

				if ( 'tutor_login' === $location && Utils::is_request_from_tutor() ) {
					return $this->handle_email_otp( $user );
				}
			}
		}

		return $user;
	}

	/**
	 * Do login
	 *
	 * @since 2.1.9
	 *
	 * @param \WP_User $user     WP_User object.
	 * @param boolean  $remember  remember.
	 *
	 * @return void
	 */
	private function do_login( \WP_User $user, bool $remember = false ) {
		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID, $remember );

		apply_filters( 'authenticate', $user, $user->user_login, '' );
		do_action( 'wp_login', $user->user_login, $user );
	}

	/**
	 * Verify login OTP.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function verify_login_otp() {
		tutor_utils()->checking_nonce();

		if ( false === Input::has( 'otp' ) ) {
			wp_send_json_error( array( 'message' => __( 'OTP code required', 'tutor-pro' ) ) );
		}

		$input_otp = Input::post( 'otp', 0, Input::TYPE_INT );
		$otp_data  = SessionHelper::get( 'tutor_login_otp' );

		if ( isset( $otp_data->code ) && $input_otp === $otp_data->code ) {
			$this->do_login( $otp_data->user, $otp_data->remember );
			SessionHelper::unset( 'tutor_login_otp' );

			$url = tutor_utils()->tutor_dashboard_url();
			if ( current_user_can( 'administrator' ) ) {
				$url = get_admin_url();
			}

			wp_send_json_success(
				array(
					'message'      => __( 'OTP matched. Redirecting...', 'tutor-pro' ),
					'redirect_url' => $url,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'OTP not matched.', 'tutor-pro' ) ) );
		}

	}

}
