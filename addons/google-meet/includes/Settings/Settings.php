<?php
/**
 * Manage google meet settings
 *
 * User meta is used for each users settings
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Settings
 */

namespace TutorPro\GoogleMeet\Settings;

use TUTOR\Input;
use TUTOR\Tutor_Base;
use TutorPro\GoogleMeet\Validator\Validator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage settings for each users
 */
class Settings extends Tutor_Base {

	/**
	 * User meta key that holds settings
	 *
	 * @since v2.1.0
	 *
	 * @var string
	 */
	const META_KEY = 'tutor_google_meet_settings';

	/**
	 * Do necessary things on init this class
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', __CLASS__ . '::initial_setup' );
		add_action( 'wp_ajax_tutor_update_google_meet_settings', __CLASS__ . '::handle_update' );
		add_filter( 'post_type_link', array( $this, 'change_meet_single_url' ), 1, 2 );
	}

	/**
	 * Change meet meeting single URL
	 *
	 * @since 2.6.0
	 *
	 * @param string  $post_link post link.
	 * @param integer $id id.
	 *
	 * @return string
	 */
	public function change_meet_single_url( $post_link, $id = 0 ) {
		$post = get_post( $id );

		if ( is_object( $post ) && 'tutor-google-meet' === $post->post_type ) {
			$course_id = tutor_utils()->get_course_id_by( 'lesson', $post->ID );
			$course    = get_post( $course_id );

			if ( is_object( $course ) ) {
				return home_url( "/{$this->course_base_permalink}/{$course->post_name}/meet-lessons/" . $post->post_name . '/' );
			} else {
				return home_url( "/{$this->course_base_permalink}/sample-course/meet-lessons/" . $post->post_name . '/' );
			}
		}

		return $post_link;
	}

	/**
	 * Get default settings of google meet
	 *
	 * @since v2.1.0
	 *
	 * @return array default settings
	 */
	public static function default_settings(): array {
		return apply_filters(
			'tutor_pro_google_meet_default_settings',
			array(
				array(
					'name'          => 'meeting_timezone',
					'label'         => __( 'Default Timezone', 'tutor-pro' ),
					'help_text'     => __( 'Set the default timezone for Google Meet', 'tutor-pro' ),
					'type'          => 'dropdown',
					'default_value' => function_exists( 'wp_timezone_string' ) ? wp_timezone_string() : '',
					'options'       => tutor_global_timezone_lists(),
				),
				array(
					'name'          => 'reminder_time',
					'label'         => __( 'Default Reminder Time', 'tutor-pro' ),
					'help_text'     => __( 'Set a default reminder time to get an email notification', 'tutor-pro' ),
					'type'          => 'radio',
					'options'       => array(
						array(
							'value' => '5',
							'label' => _x( '5 Minutes Before', 'Tutor google meet reminder', 'tutor-pro' ),
						),
						array(
							'value' => '15',
							'label' => _x( '15 Minutes Before', 'Tutor google meet reminder', 'tutor-pro' ),
						),
						array(
							'value' => '30',
							'label' => _x( '30 Minutes Before', 'Tutor google meet reminder', 'tutor-pro' ),
						),
					),
					'default_value' => '30',
				),
				array(
					'name'          => 'event_status',
					'label'         => __( 'Set Default Event Status', 'tutor-pro' ),
					'help_text'     => __( 'Set a default status for Google Meet event', 'tutor-pro' ),
					'type'          => 'radio',
					'options'       => array(
						array(
							'value' => 'confirmed',
							'label' => _x( 'Confirmed', 'Tutor google meet status', 'tutor-pro' ),
						),
						array(
							'value' => 'tentative',
							'label' => _x( 'Tentative', 'Tutor google meet status', 'tutor-pro' ),
						),
					),
					'default_value' => 'confirmed',
				),
				array(
					'name'          => 'send_updates',
					'label'         => __( 'Send Updates', 'tutor-pro' ),
					'help_text'     => __( 'Select how to send notifications about the creation of the new event. Note that some emails might still be sent.', 'tutor-pro' ),
					'type'          => 'radio',
					'options'       => array(
						array(
							'value' => 'all',
							'label' => __( 'All', 'tutor-pro' ),
						),
						array(
							'value' => 'externalOnly',
							'label' => __( 'External Only', 'tutor-pro' ),
						),
						array(
							'value' => 'none',
							'label' => __( 'None', 'tutor-pro' ),
						),
					),
					'default_value' => 'all',
				),
				array(
					'name'          => 'transparency',
					'label'         => __( 'Transparency', 'tutor-pro' ),
					'help_text'     => __( 'Select if the events block time on the calendar by default.', 'tutor-pro' ),
					'type'          => 'radio',
					'options'       => array(
						array(
							'value' => 'opaque',
							'label' => __( 'Opaque, Blocks Time on the Calendar', 'tutor-pro' ),
						),
						array(
							'value' => 'transparent',
							'label' => __( 'Transparent, does not Blocks Time', 'tutor-pro' ),
						),
					),
					'default_value' => 'opaque',
				),
				array(
					'name'          => 'event_visibility',
					'label'         => __( 'Visibility on Calendar', 'tutor-pro' ),
					'help_text'     => __( 'Set the default visibility of the event on the calendar.', 'tutor-pro' ),
					'type'          => 'radio',
					'options'       => array(
						array(
							'value' => 'default',
							'label' => __( 'Default', 'tutor-pro' ),
						),
						array(
							'value' => 'public',
							'label' => __( 'Public', 'tutor-pro' ),
						),
						array(
							'value' => 'private',
							'label' => __( 'Private', 'tutor-pro' ),
						),
					),
					'default_value' => 'default',
				),
			)
		);
	}

	/**
	 * Update user's meet settings
	 *
	 * @param array $settings  key value pair of settings.
	 *
	 * @return boolean true on success, false on failure
	 */
	public static function update_settings( array $settings ): bool {
		$update = update_user_meta(
			get_current_user_id(),
			self::META_KEY,
			maybe_serialize( $settings )
		);
		return $update ? true : false;
	}

	/**
	 * Set settings value as initial setup only if
	 * user's meta key/value not exists
	 *
	 * @since v2.1.0
	 *
	 * @return void
	 */
	public static function initial_setup() {
		if ( Validator::current_user_has_access() ) {
			$user_meta = get_user_meta( get_current_user_id(), self::META_KEY, true );
			if ( '' === $user_meta ) {
				$settings = self::default_settings();

				$keys   = array_column( $settings, 'name' );
				$values = array_column( $settings, 'default_value' );

				$data = array_combine( $keys, $values );
				self::update_settings( $data );
			}
		}
	}

	/**
	 * Handle manual settings update
	 *
	 * @since v2.1.0
	 *
	 * @return void  send wp_json response
	 */
	public static function handle_update() {
		tutor_utils()->checking_nonce();
		if ( Validator::current_user_has_access() ) {
			$post = Input::sanitize( $_POST, array(), Input::TYPE_ARRAY );
			unset( $post['action'] );
			unset( $post['_tutor_nonce'] );
			unset( $post['_wp_http_referer'] );

			if ( self::update_settings( $post ) ) {
				wp_send_json_success(
					__( 'Settings updated successfully!', 'tutor-pro' )
				);
			} else {
				wp_send_json_error(
					__( 'Settings update failed!', 'tutor-pro' )
				);
			}
		}
	}

	/**
	 * Get a particular settings value by key
	 *
	 * @since v2.1.0
	 *
	 * @param string $key  key name.
	 *
	 * @return mixed  settings key value on success, false if key not found
	 */
	public static function get_settings( string $key ) {
		$settings = maybe_unserialize( get_user_meta( get_current_user_id(), self::META_KEY, true ) );
		return $key && isset( $settings[ $key ] ) ? $settings[ $key ] : false;
	}
}
