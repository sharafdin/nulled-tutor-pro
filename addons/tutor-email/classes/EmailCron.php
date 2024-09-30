<?php
/**
 * Handle Email Queue with Crons
 *
 * @package TutorPro
 * @subpackage Addons\TutorEmail
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.5.0
 */

namespace TUTOR_EMAIL;

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EmailCron
 *
 * @since 2.5.0
 */
class EmailCron {

	/**
	 * Tutor email cron scheduler name.
	 *
	 * @var string
	 */
	const CRON_NAME = 'tutor_email_scheduler_cron';

	/**
	 * Transient name to check email cron is running or not.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const CRON_RUNNING_TRANSIENT = 'tutor_email_cron_running';


	/**
	 * Register hooks
	 *
	 * @since 2.5.0
	 *
	 * @param bool $register_hooks register hooks or not.
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		// Cron register/deregister.
		add_filter( 'cron_schedules', array( $this, 'tutor_cron_schedules' ) );
		add_action( self::CRON_NAME, array( $this, 'run_scheduler' ) );
		add_action( 'tutor_addon_before_disable_tutor-pro/addons/tutor-email/tutor-email.php', array( $this, 'deregister_scheduler' ) );
		register_deactivation_hook( TUTOR_PRO_FILE, array( $this, 'deregister_scheduler' ) );

		add_action( 'init', array( $this, 'run_with_http_request' ) );

		add_action(
			'tutor_option_save_after',
			function() {
				// Set schedule again based on new interval setting.
				$this->register_scheduler( true );
			}
		);

		// Register scheduler as normal procedure.
		$this->register_scheduler();

	}

	/**
	 * Run with HTTP query param request.
	 *
	 * @return void
	 */
	public function run_with_http_request() {
		$is_os_native = '1' === Input::get( 'tutor_cron' );

		if ( $is_os_native ) {
			$this->run_scheduler();
			exit;
		}
	}

	/**
	 * Cron schedule.
	 *
	 * @param array $schedules schedules.
	 *
	 * @return array
	 */
	public function tutor_cron_schedules( $schedules ) {

		$intervals = array( 300, 900, 1800, 3600 );
		$user_interval = (int) tutor_utils()->get_option( 'tutor_email_cron_frequency' );

		if ( $user_interval > 0 && ! in_array( $user_interval, $intervals, true ) ) {
			$intervals[] = $user_interval;
		}

		foreach ( $intervals as $second ) {

			$hook = $second . 'second';

			if ( ! isset( $schedules[ $hook ] ) ) {
				$schedules[ $hook ] = array(
					'interval' => $second,
					'display'  => $second . ' ' . __( 'second', 'tutor-pro' ),
				);
			}
		}

		return $schedules;
	}

	/**
	 * Run scheduler.
	 *
	 * @return void
	 */
	public function run_scheduler() {
		$is_os_native = '1' === Input::get( 'tutor_cron' );

		$is_running = get_transient( self::CRON_RUNNING_TRANSIENT );
		if ( $is_running ) {
			if ( $is_os_native ) {
				exit( wp_json_encode( array( 'running' => 'true' ) ) );
			}
			return;
		}

		global $wpdb;

		$limit = (int) tutor_utils()->get_option( 'tutor_bulk_email_limit', 10 );
		$limit = $limit <= 0 ? 10 : $limit;

		// Data fetched as first come first server in email queue table.
		$mails = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->tutor_email_queue} ORDER BY id ASC LIMIT %d",
				$limit
			)
		);

		$mail_count = is_array( $mails ) ? count( $mails ) : 0;

		if ( ! $mail_count ) {
			$is_os_native ? exit( wp_json_encode( array( 'call_again' => 'no' ) ) ) : 0;
			return;
		}

		$mails = array_map(
			function( $mail ) {
				return (array) $mail;
			},
			$mails
		);

		set_transient( self::CRON_RUNNING_TRANSIENT, true, HOUR_IN_SECONDS );

		// Send the mails.
		$email_notification = new EmailNotification( false );
		$email_notification->send_mail( $mails );

		// Delete from queue.
		$ids = implode( ',', array_column( $mails, 'id' ) );
		$wpdb->query( "DELETE FROM {$wpdb->tutor_email_queue} WHERE id IN ({$ids})" ); //phpcs:ignore

		delete_transient( self::CRON_RUNNING_TRANSIENT );

		if ( $is_os_native ) {
			$call = $mail_count >= $limit ? 'yes' : 'no';
			exit( wp_json_encode( array( 'call_again' => $call ) ) );
		}
	}

	/**
	 * Make batch group with same batch name.
	 *
	 * TODO will implement later
	 *
	 * @since 2.5.0
	 *
	 * @param array $mails mail list.
	 *
	 * @return array
	 */
	public function make_batch_group( $mails ) {
		$batches = array();
		foreach ( $mails as $item ) {
			$batch = $item['batch'] ?? time();

			// If the batch key doesn't exist in the result array, create it.
			if ( ! array_key_exists( $batch, $batches ) ) {
				$batches[ $batch ] = array();
			}

			// Append the current item to the batch in the result array.
			$batches[ $batch ][] = $item;
		}

		return $batches;
	}


	/**
	 * Send cron queued mail with batch.
	 *
	 * TODO will implement later
	 *
	 * @since 2.5.0
	 *
	 * @param array $batch email queue batch with same subject, body, headers.
	 *
	 * @return void
	 */
	public function send_batch_mail( $batch ) {
		if ( count( $batch ) <= 0 ) {
			return;
		};

		$email_notification = new EmailNotification( false );

		add_filter( 'wp_mail_from', array( $email_notification, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $email_notification, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $email_notification, 'get_content_type' ) );

		$to = array_column( $batch, 'mail_to' );

		$headers   = array();
		$headers[] = maybe_unserialize( $batch[0]['headers'] );

		if ( count( $to ) > 1 ) {
			$headers[] = 'Bcc: ' . implode( ', ', $to );
			$to        = '';
		}

		$subject = $batch[0]['subject'];
		$message = $batch[0]['message'];

		wp_mail( $to, $subject, $message, $headers );

		remove_filter( 'wp_mail_from', array( $email_notification, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $email_notification, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $email_notification, 'get_content_type' ) );
	}


	/**
	 * Register scheduler.
	 *
	 * @param boolean $override_old override old schedule or not.
	 *
	 * @return void
	 */
	public function register_scheduler( $override_old = false ) {
		if ( $override_old ) {
			$this->deregister_scheduler();
		}

		$event_timestamp = wp_next_scheduled( self::CRON_NAME );

		if ( false === $event_timestamp ) {
			// Register scheduler if not already.
			$is_enabled = (bool) tutor_utils()->get_option( 'tutor_email_disable_wpcron' );

			if ( $is_enabled ) {
				$interval = (int) tutor_utils()->get_option( 'tutor_email_cron_frequency' );
				$interval = $interval <= 0 ? 900 : $interval;
				wp_schedule_event( time(), $interval . 'second', self::CRON_NAME );
			}
		}
	}

	/**
	 * Deregister scheduler.
	 *
	 * @return void
	 */
	public function deregister_scheduler() {
		wp_clear_scheduled_hook( self::CRON_NAME );
	}
}
