<?php
/**
 * Manage social login settings
 *
 * @package TutorPro\SocialLogin\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */

namespace TutorPro\SocialLogin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings management
 */
class Settings {

	/**
	 * Register hooks
	 *
	 * @since 2.1.9
	 */
	public function __construct() {
		add_filter( 'tutor_pro_settings_auth_tab', __CLASS__ . '::configure_settings' );
	}

	/**
	 * Add settings configuration to the Tutor's settings
	 *
	 * @since 2.1.9
	 *
	 * @param array $attr settings attrs.
	 *
	 * @return array
	 */
	public static function configure_settings( $attr ): array {
		$copy_text = __( 'Copy Redirect URL', 'tutor-pro' );
		$copy_btn  = "<p>
		<a class='tutor-btn tutor-btn-outline-primary tutor-btn-sm'><span class='tutor-icon-copy tutor-mr-8'></span><span class='tutor-copy-text' data-text='" . tutor_utils()->tutor_dashboard_url() . "'>{$copy_text}</span></a>
		</p>";

		$twitter_copy_btn = "<p>
		<a class='tutor-btn tutor-btn-outline-primary tutor-btn-sm'><span class='tutor-icon-copy tutor-mr-8'></span><span class='tutor-copy-text' data-text='" . rtrim( tutor_utils()->tutor_dashboard_url(), '/' ) . "?tutor_twitter_login=true'>{$copy_text}</span></a>
		</p>";

		$social_settings = apply_filters(
			'tutor_pro_social_settings',
			array(
				'label'      => __( 'Social Login', 'tutor-pro' ),
				'slug'       => 'social-login',
				'block_type' => 'uniform',
				'fields'     => array(
					array(
						'key'           => 'enable_google_login',
						'type'          => 'toggle_switch',
						'label'         => __( 'Google', 'tutor-pro' ),
						'label_title'   => '',
						'default'       => 'off',
						'desc'          => __( 'Enable Google Login', 'tutor-pro' ),
						'toggle_fields' => 'google_client_ID',
					),
					array(
						'key'         => 'google_client_ID',
						'type'        => 'text',
						'label'       => __( 'Client ID', 'tutor-pro' ),
						'desc'        => __( 'Enter your <a href="https://docs.themeum.com/tutor-lms/tutorials/get-google-client-id/" target="_blank">Google Client ID</a> here.' . $copy_btn, 'tutor-pro' ),
						'placeholder' => __( 'Enter your Google Client ID here', 'tutor-pro' ),
					),
				),
			)
		);

		$facebook = array(
			'slug'       => 'social-login',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'           => 'enable_facebook_login',
					'type'          => 'toggle_switch',
					'label'         => __( 'Facebook', 'tutor-pro' ),
					'label_title'   => '',
					'default'       => 'off',
					'desc'          => __( 'Enable Facebook Login', 'tutor-pro' ),
					'toggle_fields' => 'facebook_app_ID',
				),
				array(
					'key'         => 'facebook_app_ID',
					'type'        => 'text',
					'label'       => __( 'App ID', 'tutor-pro' ),
					'desc'        => __( 'Enter your <a href="https://docs.themeum.com/tutor-lms/tutorials/get-facebook-app-id/" target="_blank">Facebook App ID</a> here.' . $copy_btn, 'tutor-pro' ),
					'placeholder' => __( 'Enter your Facebook App ID here', 'tutor-pro' ),
				),
			),
		);

		$twitter = array(
			'slug'       => 'social-login',
			'block_type' => 'uniform',
			'fields'     => array(
				array(
					'key'           => 'enable_twitter_login',
					'type'          => 'toggle_switch',
					'label'         => __( 'Twitter', 'tutor-pro' ),
					'label_title'   => '',
					'default'       => 'off',
					'desc'          => __( 'Enable Twitter Login', 'tutor-pro' ),
					'toggle_fields' => 'twitter_app_key, twitter_app_key_secret',
				),
				array(
					'key'         => 'twitter_app_key',
					'type'        => 'text',
					'label'       => __( 'App Key', 'tutor-pro' ),
					'desc'        => __( 'Enter your <a href="https://docs.themeum.com/tutor-lms/tutorials/how-to-get-twitter-api-key/" target="_blank">Twitter App Key</a> here.', 'tutor-pro' ),
					'placeholder' => __( 'Enter your Twitter App Key here', 'tutor-pro' ),
				),
				array(
					'key'         => 'twitter_app_key_secret',
					'type'        => 'text',
					'label'       => __( 'App Key Secret', 'tutor-pro' ),
					'desc'        => __( 'Enter your <a href="https://docs.themeum.com/tutor-lms/tutorials/how-to-get-twitter-api-key/" target="_blank">Twitter App Key Secret</a> here.' . $twitter_copy_btn, 'tutor-pro' ),
					'placeholder' => __( 'Enter your Twitter App Key Secret here', 'tutor-pro' ),
				),
			),
		);

		array_push( $attr['authentication']['blocks'], $social_settings );
		array_push( $attr['authentication']['blocks'], $facebook );
		array_push( $attr['authentication']['blocks'], $twitter );
		return $attr;
	}
}
