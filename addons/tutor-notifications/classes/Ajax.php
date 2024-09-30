<?php
/**
 * Handle ajax request for notifications.
 *
 * @package TutorPro\Addons
 * @subpackage Notification
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.9.10
 */

namespace TUTOR_NOTIFICATIONS;

use TUTOR\Input;
use \TUTOR_NOTIFICATIONS\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Ajax class
 */
class Ajax {

	/**
	 * Utils class instance.
	 *
	 * @var Utils $utils_function
	 */
	public $utils_function;

	/**
	 * Register hooks
	 */
	public function __construct() {

		$this->utils_function = new Utils();

		add_action( 'wp_ajax_tutor_get_all_notifications', array( $this, 'tutor_get_all_notifications' ) );
		add_action( 'wp_ajax_toggle_all_notifications_status_as_read', array( $this, 'toggle_all_notifications_status_as_read' ) );
		add_action( 'wp_ajax_toggle_single_notification_status_as_read', array( $this, 'toggle_single_notification_status_as_read' ) );
		add_action( 'wp_ajax_tutor_mark_all_notifications_as_unread', array( $this, 'tutor_mark_all_notifications_as_unread' ) );
	}

	/**
	 * Get all notifications
	 *
	 * @return void
	 */
	public function tutor_get_all_notifications() {

		tutor_utils()->checking_nonce();

		$all_notifications = $this->utils_function->get_all_notifications_by_current_user();
		wp_send_json_success(
			array(
				'notifications' => $all_notifications,
			)
		);
	}

	/**
	 * Toggle notifications status as read
	 *
	 * @return void
	 */
	public function toggle_all_notifications_status_as_read() {

		tutor_utils()->checking_nonce();

		$toggle_status = Input::post( 'mark_as_read', false, Input::TYPE_BOOL );

		if ( $toggle_status ) {
			$this->utils_function->mark_all_notifications_as_read();
			wp_send_json_success(
				array(
					'notifications' => $this->utils_function->get_all_notifications_by_current_user(),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Something went wrong. Please try again later', 'tutor-pro' ),
				)
			);
		}
	}

	/**
	 * Toggle single notification status as unread
	 *
	 * @return void
	 */
	public function toggle_single_notification_status_as_read() {

		tutor_utils()->checking_nonce();

		$notification_id = Input::post( 'notification_id', 0, Input::TYPE_INT );

		if ( $notification_id ) {
			$this->utils_function->mark_single_notification_as_read( $notification_id );
			wp_send_json_success(
				array(
					'notifications' => $this->utils_function->get_all_notifications_by_current_user(),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Something went wrong. Please try again later', 'tutor-pro' ),
				)
			);
		}
	}

	/**
	 * Delete all notifications
	 *
	 * @return void
	 */
	public function tutor_mark_all_notifications_as_unread() {

		tutor_utils()->checking_nonce();

		$mark_as_unread = Input::post( 'mark_as_unread', false, Input::TYPE_BOOL );

		if ( $mark_as_unread ) {
			$this->utils_function->mark_all_notifications_as_unread();
			wp_send_json_success(
				array(
					'notifications' => $this->utils_function->get_all_notifications_by_current_user(),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Something went wrong. Please try again later', 'tutor-pro' ),
				)
			);
		}
	}
}
