<?php
/**
 * Manage email verification after registration
 *
 * @package TutorPro\EmailVerification
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TUTOR_PRO;

use TUTOR\Ajax;
use Tutor\Cache\FlashMessage;
use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email verification
 *
 * @since 2.1.9
 */
class EmailVerification {

	const VERIFIED_IDENTIFIER = 'verified';
	const REQUIRED_IDENTIFIER = 'required';

	const EMAIL_FLASH_MSG_KEY         = 'tutor_email_verification_flash_smg';
	const VERIFICATION_REQ_META_KEY   = 'tutor_require_email_verification';
	const VERIFICATION_TOKEN_META_KEY = 'tutor_email_verification_token';

	/**
	 * Flash msg obj
	 *
	 * @var object
	 */
	private static $flash_message;

	/**
	 * Register hooks
	 *
	 * @since 2.1.9
	 */
	public function __construct() {
		add_filter( 'tutor_pro_settings_auth_tab', __CLASS__ . '::config_settings', 1 );
		add_filter( 'tutor_require_email_verification', __CLASS__ . '::is_required' );
		add_action( 'tutor_send_verification_mail', __CLASS__ . '::send_verification_mail', 10, 2 );
		add_filter( 'wp_authenticate_user', __CLASS__ . '::check_status' );
		add_action( 'template_redirect', __CLASS__ . '::verify_email' );
		add_action( 'tutor_before_student_reg_form', __CLASS__ . '::print_flash_message' );
		add_action( 'tutor_before_instructor_reg_form', __CLASS__ . '::print_flash_message' );
		add_action( 'tutor_show_email_verified_badge', __CLASS__ . '::email_verified_badge' );

		self::$flash_message = new FlashMessage();
	}

	/**
	 * Config settings
	 *
	 * @since 2.1.9
	 *
	 * @param array $attr array attrs.
	 *
	 * @return array
	 */
	public static function config_settings( array $attr ): array {
		/**
		 * Email verification section under auth settings tab
		 */
		$settings = array(
			'label'      => __( 'Email Verification', 'tutor-pro' ),
			'slug'       => 'email_verification',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'         => 'enable_email_verification',
					'type'        => 'toggle_switch',
					'label'       => __( 'Enable', 'tutor-pro' ),
					'label_title' => '',
					'default'     => 'off',
					'desc'        => __( 'Toggle to enable email verification for students and instructor signup', 'tutor-pro' ),
				),
			),
		);

		array_push( $attr['authentication']['blocks'], $settings );
		return $attr;
	}
	/**
	 * Check if email verification is required
	 *
	 * @return boolean
	 */
	public static function is_required() {
		return tutor_utils()->get_option( 'enable_email_verification' );
	}

	/**
	 * Send verification mail
	 *
	 * @since 2.1.9
	 *
	 * @param object $user user data WP_User.
	 * @param string $attempt enroll attempt field value.
	 *
	 * @return void
	 */
	public static function send_verification_mail( $user, $attempt = '' ) {
		if ( is_a( $user, 'WP_User' ) ) {
			$send = self::send_mail( $user, $attempt );
			if ( $send ) {
				$msg = __( 'A verification mail has been sent, please check your email.', 'tutor-pro' );

				// Add email verification meta data.
				update_user_meta( $user->ID, self::VERIFICATION_REQ_META_KEY, self::REQUIRED_IDENTIFIER );
				update_user_meta( $user->ID, self::VERIFICATION_TOKEN_META_KEY, md5( $user->user_email ) );

				if ( 'instructor-registration' === $attempt ) {
					update_user_meta( $user->ID, '_is_tutor_instructor', tutor_time() );
					update_user_meta( $user->ID, '_tutor_instructor_status', apply_filters( 'tutor_initial_instructor_status', 'pending' ) );

					do_action( 'tutor_new_instructor_after', $user->ID );
				}

				self::$flash_message->data = array(
					'alert'   => 'success',
					'message' => $msg,
				);

				self::$flash_message->set_cache();
			} else {
				/**
				 * Verification mail send failed.
				 */
				add_filter( 'tutor_registration_done', '__return_false' );
				add_filter( 'tutor_student_register_validation_errors', __CLASS__ . '::add_email_send_fail_error' );
				add_filter( 'tutor_instructor_register_validation_errors', __CLASS__ . '::add_email_send_fail_error' );
			}
		}
	}

	/**
	 * Add email sent fail error.
	 *
	 * @since 2.1.9
	 *
	 * @param array $errors errors.
	 * @return array
	 */
	public static function add_email_send_fail_error( $errors ) {
		$errors[] = __( 'Registration failed! Verification e-mail could not sent, please contact with site Administrator', 'tutor-pro' );
		return $errors;
	}

	/**
	 * Send verification email
	 *
	 * @since 2.1.9
	 *
	 * @param object $user WP_User.
	 * @param string $attempt an attempt to recognize what
	 * user wanted to do.
	 *
	 * @return bool true on success otherwise false
	 */
	public static function send_mail( $user, $attempt = '' ) {
		$token = md5( $user->user_email );
		$link  = trailingslashit( home_url() ) . "?email={$user->user_email}&token={$token}";

		if ( '' !== $attempt ) {
			$link .= "&attempt={$attempt}";
		}

		$user = get_user_by( 'email', $user->user_email );

		$template = tutor_pro()->path . 'templates/email/to_user_email_verification.php';

		$data = array(
			'{site_url}'             => home_url(),
			'{testing_email_notice}' => '',
			'{link}'                 => $link,
			'{email_heading}'        => __( 'Email Verification', 'tutor-pro' ),
			'{email_message}'        => __( 'Thank you for signing up for our website! To complete your account sign-up: please click on the button below to verify your email address.', 'tutor-pro' ),
			'{footer_text}'          => __( 'This is an automatically generated email. Please do not reply to this email. ', 'tutor-pro' ),
			'{additional_text}'      => __( 'If the button is unresponsive, please follow the link below and verify your email address.', 'tutor-pro' ),
			'{user_name}'            => _x( 'Hi ', 'Email verification', 'tutor-pro' ) . tutor_utils()->display_name( $user->ID ),
		);

		$subject = __( 'Verify your Email', 'tutor-pro' );

		$email_body  = Mailer::prepare_template( $template, $data );
		$email_body .= tutor_pro_email_global_footer();

		do_action( 'tutor_pro_before_send_verification_email', $user );

		$response = Mailer::send_mail( $user->user_email, $subject, $email_body );

		do_action( 'tutor_pro_after_send_verification_email', $user, $response );

		return $response;
	}

	/**
	 * Verify mail through verification link
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function verify_email() {
		$email = Input::get( 'email', '' );
		$token = Input::get( 'token', '' );
		$url   = tutor_utils()->tutor_dashboard_url();
		// If email or token not set return.
		if ( '' === $email || '' === $token ) {
			return;
		} else {
			$userdata = get_user_by( 'email', $email );
			if ( is_a( $userdata, 'WP_User' ) ) {
				$existing_token = get_user_meta( $userdata->ID, self::VERIFICATION_TOKEN_META_KEY, true );
				if ( $token === $existing_token ) {

					do_action( 'tutor_after_student_signup', $userdata->ID );

					update_user_meta( $userdata->ID, self::VERIFICATION_REQ_META_KEY, self::VERIFIED_IDENTIFIER );

					self::logged_in( $userdata );

					// Delete token.
					delete_user_meta( $userdata->ID, self::VERIFICATION_TOKEN_META_KEY );

					wp_safe_redirect( $url );
					exit;
				} else {
					$msg = array( __( 'Token expired', 'tutor-pro' ) );

					\set_transient( Ajax::LOGIN_ERRORS_TRANSIENT_KEY, $msg );

					wp_safe_redirect( $url );
					exit;
				}
			} else {
				$msg = __( 'Invalid email', 'tutor-pro' );

				\set_transient( Ajax::LOGIN_ERRORS_TRANSIENT_KEY, $msg );

				wp_safe_redirect( $url );
				exit;
			}
		}
	}

	/**
	 * Before login check if user has verified their email
	 *
	 * @since 2.1.9
	 *
	 * @param object $wp_user authenticated user.
	 *
	 * @return mixed
	 */
	public static function check_status( $wp_user ) {
		// If email verification required.
		if ( ! is_wp_error( $wp_user ) && self::is_required() ) {

			// Check if email verification required for this user.
			$email_verification = get_user_meta( $wp_user->ID, self::VERIFICATION_REQ_META_KEY, true );

			if ( self::REQUIRED_IDENTIFIER === $email_verification ) {
				$message = esc_html__( 'Please verify your email address', 'tutor-pro' );
				return new \WP_Error( 'tutor_email_not_verified', $message );
			}
		}
		return $wp_user;
	}

	/**
	 * Print flash message after registration
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public static function print_flash_message() {
		self::$flash_message->show();
	}

	/**
	 * Let the user log-in
	 *
	 * @since 2.1.9
	 *
	 * @param \WP_User $userdata user info.
	 *
	 * @return void
	 */
	public static function logged_in( \WP_User $userdata ) {
		wp_set_current_user( $userdata->ID, $userdata->user_login );
		wp_set_auth_cookie( $userdata->ID );

		apply_filters( 'authenticate', $userdata, $userdata->user_login, '' );
		do_action( 'wp_login', $userdata->user_login, $userdata );
	}

	/**
	 * Check if user'e mail is verified
	 *
	 * @since 2.1.10
	 *
	 * @param integer $user_id user id whose email to check.
	 *
	 * @return boolean
	 */
	public static function is_email_verified( int $user_id ) {
		$user_meta = get_user_meta( $user_id, self::VERIFICATION_REQ_META_KEY, true );
		return self::VERIFIED_IDENTIFIER === $user_meta;
	}

	/**
	 * Check if user's email is verified
	 *
	 * @since 2.1.10
	 *
	 * @param integer $user_id user id.
	 *
	 * @return void
	 */
	public static function email_verified_badge( int $user_id ) {
		if ( self::is_email_verified( $user_id ) ) {
			?>
			<span class="tutor-icon-circle-mark-o tutor-color-primary" title="<?php esc_html_e( 'Email Verified', 'tutor-pro' ); ?>"></span>
			<?php
		}
	}

}
