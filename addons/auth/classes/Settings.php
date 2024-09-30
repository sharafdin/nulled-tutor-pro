<?php
/**
 * Manage Settings.
 *
 * @package TutorPro\Auth
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\Auth;

/**
 * Settings Class.
 *
 * @since 2.1.9
 */
class Settings {

	const ENABLE_2FA   = 'enable_2fa';
	const METHOD_2FA   = 'method_2fa';
	const LOCATION_2FA = 'location_2fa';

	const METHOD_HONEYPOT     = 'honeypot';
	const METHOD_RECAPTCHA_V2 = 'recaptcha_v2';
	const METHOD_RECAPTCHA_V3 = 'recaptcha_v3';

	const ENABLE_SPAM_PROTECTION   = 'enable_spam_protection';
	const SPAM_PROTECTION_LOCATION = 'spam_protection_location';
	const SPAM_PROTECTION_METHOD   = 'spam_protection_method';

	const RECAPTCHA_V2_SITE_KEY   = 'recaptcha_v2_site_key';
	const RECAPTCHA_V2_SECRET_KEY = 'recaptcha_v2_secret_key';

	const RECAPTCHA_V3_SITE_KEY   = 'recaptcha_v3_site_key';
	const RECAPTCHA_V3_SECRET_KEY = 'recaptcha_v3_secret_key';

	/**
	 * Register hooks.
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor/options/extend/attr', array( $this, 'add_auth_settings_option' ) );
	}

	/**
	 * Add settings.
	 *
	 * @since 2.1.9
	 *
	 * @param array $attr existing settings attributes.
	 *
	 * @return array
	 */
	public function add_auth_settings_option( $attr ) {

		$methods_2fa = array(
			'email' => __( 'E-mail', 'tutor-pro' ),
		);

		$locations_2fa = array(
			'tutor_login' => __( 'Tutor Login', 'tutor-pro' ),
			'wp_login'    => __( 'WP Login', 'tutor-pro' ),
			'both'        => __( 'Tutor & WP Login', 'tutor-pro' ),
		);

		$spam_protection_methods = array(
			'honeypot'     => __( 'HoneyPot', 'tutor-pro' ),
			'recaptcha_v2' => __( 'Google reCAPTCHA V2', 'tutor-pro' ),
			'recaptcha_v3' => __( 'Google reCAPTCHA V3', 'tutor-pro' ),
		);

		$spam_protection_locations = array(
			'tutor_login'        => __( 'Tutor Login', 'tutor-pro' ),
			'tutor_registration' => __( 'Tutor Registration', 'tutor-pro' ),
			'wp_login'           => __( 'WP Login', 'tutor-pro' ),
			'wp_registration'    => __( 'WP Registration', 'tutor-pro' ),
		);

		/**
		 * 2FA section under auth settings tab
		 */
		$section_2fa = array(
			'label'      => __( 'Two-Factor Authentication', 'tutor-pro' ),
			'slug'       => '2fa',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'           => self::ENABLE_2FA,
					'type'          => 'toggle_switch',
					'label'         => __( 'Enable 2FA', 'tutor-pro' ),
					'label_title'   => '',
					'default'       => 'off',
					'desc'          => '',
					'toggle_fields' => 'method_2fa,location_2fa',
				),
				array(
					'key'     => self::METHOD_2FA,
					'type'    => 'select',
					'label'   => __( 'Method', 'tutor-pro' ),
					'default' => 'email',
					'options' => $methods_2fa,
					'desc'    => __( 'Choose method for 2FA', 'tutor-pro' ),
				),
				array(
					'key'     => self::LOCATION_2FA,
					'type'    => 'select',
					'label'   => __( 'Location', 'tutor-pro' ),
					'default' => 'tutor_login',
					'options' => $locations_2fa,
					'desc'    => __( 'Choose location for 2FA', 'tutor-pro' ),
				),
			),
		);

		/**
		 * Spam protection section under auth settings tab
		 */
		$section_spam_protection = array(
			'label'      => __( 'Fraud Protection', 'tutor-pro' ),
			'slug'       => 'spam_protection',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'           => self::ENABLE_SPAM_PROTECTION,
					'type'          => 'toggle_switch',
					'label'         => __( 'Enable Fraud Protection', 'tutor-pro' ),
					'label_title'   => '',
					'default'       => 'off',
					'desc'          => '',
					'toggle_fields' => 'spam_protection_method,spam_protection_location,recaptcha_v2_site_key,recaptcha_v2_secret_key,recaptcha_v3_site_key,recaptcha_v3_secret_key',
				),
				array(
					'key'     => 'spam_protection_method',
					'type'    => 'select',
					'label'   => __( 'Method', 'tutor-pro' ),
					'default' => 'honeypot',
					'options' => $spam_protection_methods,
					'desc'    => __( 'Choose method for Fraud Protection', 'tutor-pro' ),
				),
				array(
					'key'     => self::RECAPTCHA_V2_SITE_KEY,
					'type'    => 'text',
					'label'   => __( 'v2 Site Key', 'tutor-pro' ),
					'default' => '',
					'desc'    => __( 'Enter reCAPTCHA v2 Site Key', 'tutor-pro' ),
				),
				array(
					'key'     => self::RECAPTCHA_V2_SECRET_KEY,
					'type'    => 'text',
					'label'   => __( 'v2 Secret Key', 'tutor-pro' ),
					'default' => '',
					'desc'    => __( 'Enter reCAPTCHA v2 Secret Key', 'tutor-pro' ),
				),
				array(
					'key'     => self::RECAPTCHA_V3_SITE_KEY,
					'type'    => 'text',
					'label'   => __( 'v3 Site Key', 'tutor-pro' ),
					'default' => '',
					'desc'    => __( 'Enter reCAPTCHA v3 Site Key', 'tutor-pro' ),
				),
				array(
					'key'     => self::RECAPTCHA_V3_SECRET_KEY,
					'type'    => 'text',
					'label'   => __( 'v3 Secret Key', 'tutor-pro' ),
					'default' => '',
					'desc'    => __( 'Enter reCAPTCHA v3 Secret Key', 'tutor-pro' ),
				),
				array(
					'key'     => 'spam_protection_location',
					'type'    => 'checkbox_horizontal',
					'label'   => __( 'Location', 'tutor-pro' ),
					'desc'    => __( 'Choose location for Fraud Protection', 'tutor-pro' ),
					'default' => array( 'tutor_login', 'tutor_registration' ),
					'options' => $spam_protection_locations,
				),
			),
		);

		$auth_tab = array(
			'authentication' => array(
				'label'    => __( 'Authentication', 'tutor-pro' ),
				'slug'     => 'authentication',
				'desc'     => __( 'Authentication Settings', 'tutor-pro' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-privacy',
				'blocks'   => array(
					$section_2fa,
					$section_spam_protection,
				),
			),
		);

		$auth_tab = apply_filters( 'tutor_pro_settings_auth_tab', $auth_tab );

		return $attr + $auth_tab;
	}

	/**
	 * Check 2FA is enabled
	 *
	 * @since 2.1.9
	 * 
	 * @return boolean
	 */
	public static function is_2fa_enabled() {
		return tutils()->get_option( self::ENABLE_2FA, false );
	}

	/**
	 * Get active 2FA method
	 * 
	 * @since 2.1.9
	 * 
	 * @return string
	 */
	public static function get_2fa_method() {
		return tutils()->get_option( self::METHOD_2FA, 'email' );
	}

	/**
	 * Get active 2FA location like login, registration etc
	 *
	 * @since 2.1.9
	 * 
	 * @return string
	 */
	public static function get_2fa_location() {
		return tutils()->get_option( self::LOCATION_2FA, 'both' );
	}

	/**
	 * Check spam protection enabled.
	 * 
	 * @since 2.1.9
	 * 
	 * @return boolean
	 */
	public static function is_spam_protection_enabled() {
		return tutils()->get_option( self::ENABLE_SPAM_PROTECTION, false );
	}

	/**
	 * Get spam protection method
	 *
	 * @since 2.1.9
	 * 
	 * @return string
	 */
	public static function get_spam_protection_method() {
		return tutils()->get_option( self::SPAM_PROTECTION_METHOD, self::METHOD_HONEYPOT );
	}

	/**
	 * Get spam protection location.
	 * 
	 * @since 2.1.9
	 *
	 * @return array
	 */
	public static function get_spam_protection_location() {
		return tutils()->get_option( self::SPAM_PROTECTION_LOCATION, array() );
	}
}
