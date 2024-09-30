<?php
/**
 * Spam Protection Logic
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

use TUTOR\Input;

/**
 * SpamProtection Class.
 *
 * @since 2.1.9
 */
class SpamProtection {
	/**
	 * Input field name for honeypot
	 *
	 * @var string
	 */
	const HONEYPOT_FIELD = '_uuid';

	/**
	 * Register hooks.
	 *
	 * @since 2.1.8
	 *
	 * @return void
	 */
	public function __construct() {
		/**
		 * Login Spam Protection.
		 */
		add_action( 'tutor_before_login_form', array( $this, 'set_pagenow_as_tutor_login' ) );
		add_action( 'login_form', array( $this, 'extend_login_form' ) );
		add_filter( 'authenticate', array( $this, 'auth_check' ), 10, 3 );

		/**
		 * Registration Spam Protection.
		 */
		add_action( 'tutor_before_student_reg_form', array( $this, 'set_pagenow_as_tutor_register' ) );
		add_action( 'tutor_before_instructor_reg_form', array( $this, 'set_pagenow_as_tutor_register' ) );
		add_action( 'register_form', array( $this, 'extend_register_form' ) );
		add_filter( 'registration_errors', array( $this, 'handle_registration_spam_protection' ), 10, 3 );

		/**
		 * Password Reset Form Spam Protection.
		 */
		add_action( 'tutor_lostpassword_form', array( $this, 'extend_lostpassword_form' ) );
		add_filter( 'tutor_before_retrieve_password_form_process', array( $this, 'handle_reset_pass_spam_protection' ) );
	}

	/**
	 * Extend tutor password reset form to add spam protection.
	 *
	 * @since 2.1.10
	 *
	 * @return void
	 */
	public function extend_lostpassword_form() {
		$is_enabled = Settings::is_spam_protection_enabled();
		if ( ! $is_enabled ) {
			return;
		}

		$locations = Settings::get_spam_protection_location();
		if ( in_array( 'tutor_login', $locations ) ) {
			$method = Settings::get_spam_protection_method();
			$this->add_form_content( $method );
		}
	}

	/**
	 * Handle reset pass spam protection.
	 * 
	 * @since 2.1.10
	 *
	 * @return void|\WP_Error
	 */
	public function handle_reset_pass_spam_protection() {
		$is_enabled = Settings::is_spam_protection_enabled();
		if ( ! $is_enabled ) {
			return;
		}

		$locations = Settings::get_spam_protection_location();
		if ( in_array( 'tutor_login', $locations ) ) {
			$method = Settings::get_spam_protection_method();
			$result = $this->do_spam_protect( $method );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
	}

	/**
	 * Set value to global pagenow key.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function set_pagenow_as_tutor_login() {
		$GLOBALS['pagenow'] = 'tutor_login';
	}

	/**
	 * Set value to global pagenow key.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function set_pagenow_as_tutor_register() {
		$GLOBALS['pagenow'] = 'tutor_registration';
	}

	/**
	 * Add form content based on protection method.
	 *
	 * @since 2.1.9
	 *
	 * @param string $method honeypot, recaptcha_v2, recaptcha_v3.
	 *
	 * @return void
	 */
	public function add_form_content( $method ) {
		if ( Settings::METHOD_HONEYPOT === $method ) {
			HoneyPot::form_content( self::HONEYPOT_FIELD );
		}

		if ( Settings::METHOD_RECAPTCHA_V2 === $method ) {
			$site_key = tutils()->get_option( Settings::RECAPTCHA_V2_SITE_KEY, '' );
			Recaptcha::form_content( Recaptcha::VERSION_V2, $site_key );
		}

		if ( Settings::METHOD_RECAPTCHA_V3 === $method ) {
			$site_key = tutils()->get_option( Settings::RECAPTCHA_V3_SITE_KEY, '' );
			Recaptcha::form_content( Recaptcha::VERSION_V3, $site_key );
		}
	}

	/**
	 * Do spam protection by method.
	 *
	 * @since 2.1.9
	 *
	 * @param string $method method name like honypot, reCAPTCHA etc.
	 *
	 * @return void|\WP_Error
	 */
	public function do_spam_protect( $method ) {
		/**
		 * For HoneyPot
		 */
		if ( Settings::METHOD_HONEYPOT === $method ) {
			return HoneyPot::verify( self::HONEYPOT_FIELD );
		}

		/**
		 * For reCAPTCHA v2, v3
		 */
		if ( in_array( $method, array( Settings::METHOD_RECAPTCHA_V2, Settings::METHOD_RECAPTCHA_V3 ) ) ) {
			$token      = '';
			$secret_key = '';
			if ( Settings::METHOD_RECAPTCHA_V2 === $method && Input::has( 'g-recaptcha-response' ) ) {
				$secret_key = tutils()->get_option( Settings::RECAPTCHA_V2_SECRET_KEY, '' );
				$token      = Input::post( 'g-recaptcha-response' );
			}

			if ( Settings::METHOD_RECAPTCHA_V3 === $method && Input::has( 'recaptcha_token' ) ) {
				$secret_key = tutils()->get_option( Settings::RECAPTCHA_V3_SECRET_KEY, '' );
				$token      = Input::post( 'recaptcha_token' );
			}

			if ( ! empty( $secret_key ) ) {
				$result = Recaptcha::verify( $token, $secret_key );
				return $result;
			}
		}
	}

	/**
	 * Extend WP, Tutor registration form to add reCAPTCHA/HoneyPot fields.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function extend_register_form() {
		$is_enabled = Settings::is_spam_protection_enabled();
		if ( ! $is_enabled ) {
			return;
		}

		$locations = Settings::get_spam_protection_location();

		$page_now = $GLOBALS['pagenow'];

		$current_reg_page = '';
		if ( 'wp-login.php' === $page_now ) {
			$current_reg_page = 'wp_registration';
		}
		if ( 'tutor_registration' === $page_now ) {
			$current_reg_page = 'tutor_registration';
		}

		if ( ! in_array( $current_reg_page, $locations ) ) {
			return;
		}

		$method = Settings::get_spam_protection_method();
		$this->add_form_content( $method );
	}

	/**
	 * Check spam protection during registration.
	 *
	 * @since 2.1.9
	 *
	 * @param \WP_Error $errors                error object.
	 * @param string    $sanitized_user_login  username.
	 * @param string    $user_email            user email.
	 *
	 * @return \WP_Error
	 */
	public function handle_registration_spam_protection( $errors, $sanitized_user_login, $user_email ) {
		$is_enabled = Settings::is_spam_protection_enabled();
		if ( ! $is_enabled ) {
			return $errors;
		}

		$locations = Settings::get_spam_protection_location();

		if ( ( in_array( 'tutor_registration', $locations ) && Input::has( 'tutor_action' ) )
			|| ( in_array( 'wp_registration', $locations ) && Utils::is_request_from_wp_login() ) ) {

			$method = Settings::get_spam_protection_method();
			$result = $this->do_spam_protect( $method );
			if ( is_wp_error( $result ) ) {
				$errors->add( $result->get_error_code(), $result->get_error_message() );
			}
		}

		return $errors;
	}

	/**
	 * Extend login form to add reCAPTCHA/HoneyPot field.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function extend_login_form() {
		$is_enabled = Settings::is_spam_protection_enabled();
		if ( ! $is_enabled ) {
			return;
		}

		$locations = Settings::get_spam_protection_location();

		$page_now = $GLOBALS['pagenow'];

		$current_login_page = '';
		if ( 'wp-login.php' === $page_now ) {
			$current_login_page = 'wp_login';
		}
		if ( 'tutor_login' === $page_now ) {
			$current_login_page = 'tutor_login';
		}

		if ( ! in_array( $current_login_page, $locations ) ) {
			return;
		}

		$method = Settings::get_spam_protection_method();

		$this->add_form_content( $method );

	}

	/**
	 * Check spam protection logic during user login
	 * based on spam protection method set in tutor setttings > authentication
	 *
	 * @since 2.1.9
	 *
	 * @param mixed       $user       $user value can be null, object or wp error.
	 * @param null|string $username   username.
	 * @param null|string $password   user provided password.
	 *
	 * @return mixed user null, object or wp error.
	 */
	public function auth_check( $user, $username, $password ) {
		if ( Input::has( 'wp-submit' ) || Input::has( 'tutor_action' ) ) {
			$is_enabled = Settings::is_spam_protection_enabled();
			if ( ! $is_enabled ) {
				return $user;
			}

			$locations = Settings::get_spam_protection_location();

			if ( ( in_array( 'tutor_login', $locations ) && Utils::is_request_from_tutor() )
			|| ( in_array( 'wp_login', $locations ) && Utils::is_request_from_wp_login() ) ) {

				$method = Settings::get_spam_protection_method();
				$result = $this->do_spam_protect( $method );
				if ( is_wp_error( $result ) ) {
					remove_all_filters( 'authenticate' );
					return $result;
				}
			}
		}

		return $user;
	}
}
