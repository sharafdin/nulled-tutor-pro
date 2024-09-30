<?php
/**
 * Utility helper for notification addon
 *
 * @package TutorPro\Addons
 * @subpackage Notification
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.9.10
 */

namespace TUTOR_NOTIFICATIONS;

defined( 'ABSPATH' ) || exit;

/**
 * Utils class
 */
class Utils {

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( file_exists( 'pluggable.php' ) ) {
			include ABSPATH . 'wp-includes/pluggable.php';
		}
	}

	/**
	 * Save onsite notification data.
	 *
	 * @since 2.2.5
	 *
	 * @param array $data notification data.
	 *
	 * @return void
	 */
	public static function save_notification_data( array $data ) {
		/**
		 * Save GMT - datetime in mysql format 'Y-m-d H:i:s'
		 */
		$data['created_at'] = current_time( 'mysql', true );

		global $wpdb;

		$data = apply_filters( 'tutor_before_insert_notification_data', $data );
		$wpdb->insert( $wpdb->tutor_notifications, $data );
		do_action( 'tutor_after_insert_notification_data', $wpdb->insert_id );
	}

	/**
	 * Get all notifications of current user
	 *
	 * @return array $notifications
	 */
	public function get_all_notifications_by_current_user() {
		global $wpdb;
		$current_user_id = absint( get_current_user_id() );

		$notifications = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->tutor_notifications
				WHERE receiver_id = %d
				ORDER BY created_at DESC",
				$current_user_id
			)
		);

		$notifications = array_map(
			function( $row ) {
				$current_date_obj      = new \DateTime( current_time( 'mysql' ) );
				$notification_date_obj = new \DateTime( $row->created_at );
				$interval              = $current_date_obj->diff( $notification_date_obj );

				if ( $interval->days >= 1 ) {
					$row->created_at_readable = tutor_utils()->convert_date_into_wp_timezone( $row->created_at, get_option( 'date_format' ) );
				} else {
					/* translators: The placeholder is a human_time_diff */
					$row->created_at_readable = sprintf( __( '%s ago', 'tutor-pro' ), human_time_diff( strtotime( $row->created_at ) ) );
				}

				return $row;
			},
			$notifications
		);

		return $notifications;
	}

	/**
	 * Mark all notifications status as read
	 *
	 * @return bool
	 */
	public function mark_all_notifications_as_read() {
		global $wpdb;

		$current_user_id = absint( get_current_user_id() );
		$tablename       = $wpdb->tutor_notifications;
		$updated_status  = array(
			'status' => 'READ',
		);

		$where_clause = array(
			'receiver_id' => $current_user_id,
			'status'      => 'UNREAD',
		);

		$status_updated = $wpdb->update( $tablename, $updated_status, $where_clause );

		return $status_updated;
	}

	/**
	 * Mark all notifications status as unread
	 *
	 * @return bool
	 */
	public function mark_all_notifications_as_unread() {
		global $wpdb;

		$current_user_id = absint( get_current_user_id() );
		$tablename       = $wpdb->tutor_notifications;
		$updated_status  = array(
			'status' => 'UNREAD',
		);

		$where_clause = array(
			'receiver_id' => $current_user_id,
			'status'      => 'READ',
		);

		$status_updated = $wpdb->update( $tablename, $updated_status, $where_clause );

		return $status_updated;
	}

	/**
	 * Mark a single notification status as read
	 *
	 * @param int $notification_id notification id.
	 *
	 * @return bool
	 */
	public function mark_single_notification_as_read( $notification_id ) {
		global $wpdb;

		$current_user_id = absint( get_current_user_id() );
		$tablename       = $wpdb->tutor_notifications;
		$updated_status  = array(
			'status' => 'READ',
		);

		$where_clause = array(
			'ID'          => (int) $notification_id,
			'receiver_id' => $current_user_id,
			'status'      => 'UNREAD',
		);

		$status_updated = $wpdb->update( $tablename, $updated_status, $where_clause );

		return $status_updated;
	}

	/**
	 * Delete all notifications
	 *
	 * @return void
	 */
	public function delete_all_notifications_by_user() {
		global $wpdb;

		$current_user_id = absint( get_current_user_id() );
		$tablename       = $wpdb->tutor_notifications;

		$where_clause = array(
			'receiver_id' => $current_user_id,
		);

		$wpdb->delete( $tablename, $where_clause );
	}
}
