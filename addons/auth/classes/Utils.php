<?php
/**
 * Utils class
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

use TUTOR\Input;
use TUTOR_PRO\Mailer;

/**
 * Utils Class.
 *
 * @since 2.1.9
 */
class Utils {
	/**
	 * Check is request from WP login screen.
	 *
	 * @since 2.1.9
	 *
	 * @return boolean
	 */
	public static function is_request_from_wp_login() {
		return isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false;
	}

	/**
	 * Check is request from tutor form - login, registration
	 *
	 * @since 2.1.9
	 *
	 * @return boolean
	 */
	public static function is_request_from_tutor() {
		return Input::has( 'tutor_action' );
	}

	/**
	 * Generate 6 digit random OTP.
	 *
	 * @since 2.1.9
	 *
	 * @return int
	 */
	public static function generate_otp() {
		return rand( 100000, 999999 );
	}

	/**
	 * Sent OTP e-mail.
	 *
	 * @since 2.1.9
	 *
	 * @param string $email email address.
	 * @param int    $otp OTP code.
	 *
	 * @return bool  true if mail sent otherwise false.
	 */
	public static function sent_login_otp( $email, $otp ) {
		$email_tpl = tutor_auth()->templates . 'email/login-otp.php';
		$subject   = __( 'Tutor - login OTP', 'tutor-pro' );

		$data = array(
			'{login_otp}'            => $otp,
			'{testing_email_notice}' => '',
			'{footer_text}'          => tutor_pro_email_global_footer(),
			'{additional_footer}'    => __( 'This is an automatically generated email. Please do not reply to this email.', 'tutor-pro' ),
		);

		$message = Mailer::prepare_template( $email_tpl, $data );
		return Mailer::send_mail( $email, $subject, $message );
	}

	/**
	 * Get hint of an e-mail address.
	 *
	 * @since 2.1.9
	 *
	 * @param string $email e-mail address.
	 *
	 * @return string
	 */
	public static function get_email_hint( $email ) {
		return preg_replace_callback(
			'/(\w)(.*?)(\w)(@.*?)$/s',
			function ( $matches ) {
				return $matches[1] . preg_replace( '/\w/', '*', $matches[2] ) . $matches[3] . $matches[4];
			},
			$email
		);
	}

}
