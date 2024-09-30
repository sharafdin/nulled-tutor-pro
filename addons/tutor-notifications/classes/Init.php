<?php
/**
 * Handles initialization of Tutor Notifications
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
 * Init class
 */
class Init {

	/**
	 * Version number
	 *
	 * @var $version
	 */
	public $version = TUTOR_NOTIFICATIONS_VERSION;

	/**
	 * Path
	 *
	 * @var $path
	 */
	public $path;

	/**
	 * URL
	 *
	 * @var $url
	 */
	public $url;

	/**
	 * Basename
	 *
	 * @var $basename
	 */
	public $basename;

	/**
	 * Notifications
	 *
	 * @var $notifications
	 */
	public $notifications;

	/**
	 * Notifications
	 *
	 * @var $tutor_notifications
	 */
	public $tutor_notifications;

	/**
	 * All notification
	 *
	 * @var $get_all_notifications
	 */
	public $get_all_notifications;

	/**
	 * Push notification
	 *
	 * @var $push_notification
	 */
	public $push_notifications;

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'tutor' ) ) {
			return;
		}

		$is_enabled = tutils()->is_addon_enabled( TUTOR_NOTIFICATIONS()->basename );

		if ( ! $is_enabled ) {
			return;
		}

		$this->path     = plugin_dir_path( TUTOR_NOTIFICATIONS_FILE );
		$this->url      = plugin_dir_url( TUTOR_NOTIFICATIONS_FILE );
		$this->basename = plugin_basename( TUTOR_NOTIFICATIONS_FILE );

		$this->load_tutor_notifications();
	}

	/**
	 * Load Tutor Notifications
	 *
	 * @return void
	 */
	public function load_tutor_notifications() {

		/**
		 * Loading Autoloader
		 */
		spl_autoload_register( array( $this, 'loader' ) );

		$this->create_notifications_db_table();

		$this->tutor_notifications   = new Tutor_Notifications();
		$this->notifications         = new Notifications();
		$this->get_all_notifications = new Ajax();
		$this->push_notifications    = new Pusher();
	}

	/**
	 * Auto Load class and the files
	 *
	 * @param string $class_name class name.
	 *
	 * @return void
	 */
	private function loader( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			$class_name = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$class_name
			);

			$class_name = str_replace( 'TUTOR_NOTIFICATIONS' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $class_name );
			$file_name  = $this->path . $class_name . '.php';

			if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Run the plugin with activation hook
	 */
	public function run() {
		register_activation_hook( TUTOR_NOTIFICATIONS_FILE, array( $this, 'tutor_notifications_activate' ) );
	}

	/**
	 * Do stuff during plugin activation
	 */
	public function tutor_notifications_activate() {
		$version = get_option( 'tutor_notifications_version' );

		// Save Option.
		if ( ! $version ) {
			update_option( 'tutor_notifications_version', TUTOR_NOTIFICATIONS_VERSION );
		}
	}

	/**
	 * Create Database Table for Notifications
	 */
	private function create_notifications_db_table() {
		global $wpdb;
		$wpdb->tutor_notifications = $wpdb->prefix . 'tutor_notifications';

		$charset_collate = $wpdb->get_charset_collate();

		$notifications_table = "CREATE TABLE {$wpdb->tutor_notifications} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`type` varchar(255),
			title tinytext,
			content text,
			`status` enum('READ','UNREAD'),
			receiver_id bigint(20) unsigned,
			post_id bigint(20) unsigned NULL,
			topic_url varchar(255) NULL,
			created_at datetime,
			PRIMARY KEY (ID)
		) $charset_collate;";

		// Require the upgrade file.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $notifications_table );
	}
}
