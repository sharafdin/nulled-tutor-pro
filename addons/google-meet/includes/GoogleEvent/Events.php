<?php
/**
 * Manage Calendar events
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\GoogleEvent
 */

namespace TutorPro\GoogleMeet\GoogleEvent;

use TUTOR\Input;
use TUTOR\User;
use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Models\EventsModel;
use TutorPro\GoogleMeet\Settings\Settings;
use TutorPro\GoogleMeet\Utilities\Utilities;
use TutorPro\GoogleMeet\Validator\Validator;
use WP_Query;

/**
 * Manage google events
 */
class Events {

	/**
	 * Store Google Client to make API request
	 *
	 * @since v2.1.0
	 *
	 * @var mixed
	 */
	protected $google_client;

	/**
	 * App unauthorized message
	 *
	 * @since v2.1.0
	 *
	 * @var string
	 */
	protected $unauthorized_msg = 'You app is not authorized, please authorize from set-api page!';

	/**
	 * Register hooks
	 *
	 * @since v2.1.0
	 */
	public function __construct() {
		$this->google_client = new GoogleEvent();
		add_action( 'wp_ajax_tutor_google_meet_new_meeting', array( $this, 'create_meeting' ) );
		add_action( 'wp_ajax_tutor_google_meet_delete', array( $this, 'delete_meeting' ) );
		// add_action( 'wp_ajax_tutor_google_meet_edit', array( $this, 'edit_meeting' ) );
		add_action( 'wp_ajax_tutor_google_meet_reset_cred', array( $this, 'reset_credential' ) );
	}

	/**
	 * Create or update google meeting
	 *
	 * @since v2.1.0
	 *
	 * @return void  wp_json response
	 */
	public function create_meeting() {
		tutor_utils()->checking_nonce();

		// Check if current user is privileged to make this request.
		if ( ! Validator::current_user_has_access() ) {
			wp_send_json_error( __( 'You are not authorized to make this request', 'tutor-pro' ) );
		}

		// Check if app is authorized to make this request.
		if ( ! $this->google_client->is_app_permitted() ) {
			wp_send_json_error( __( $this->unauthorized_msg, 'tutor-pro' ) );
		}

		$plugin_data      = GoogleMeet::meta_data();
		$validation_error = array();
		// Sanitize post field.
		$post = array_map(
			function( $value ) {
				return sanitize_text_field( $value );
			},
			$_POST
		);

		$settings = maybe_unserialize( get_user_meta( get_current_user_id(), Settings::META_KEY, true ) );
		// Check action type.
		$is_update = isset( $post['post-id'] ) ? true : false;

		// Check event type under course or topic.
		$event_type = get_post_type( $post['course_id'] );

		$attendees = array();
		// Get enrolled students.

		if ( 'Yes' === $post['attendees'] ) {
			$course_id = $post['course_id'];
			/**
			 * If not course id then it topic id
			 * get topic parent id & set as course id.
			 */
			if ( get_post_type( $course_id ) !== tutor()->course_post_type ) {
				$course    = get_post_parent( $course_id );
				$course_id = $course->ID;
			}
			$students = tutor_utils()->get_students_data_by_course_id( $course_id, 'ID', true );
			foreach ( $students as $student ) {
				array_push(
					$attendees,
					array(
						'displayName'    => '' === $student->display_name ? $student->username : $student->display_name,
						'email'          => $student->user_email,
						'responseStatus' => 'needsAction',
					)
				);
			}
		}

		$timezone   = new \DateTimeZone( $post['meeting_timezone'] );
		$start_date = \tutor_get_formated_date( 'Y-m-d', $post['meeting_start_date'] );
		$end_date   = \tutor_get_formated_date( 'Y-m-d', $post['meeting_end_date'] );

		$start_date_time = \date_create_from_format( 'Y-m-d h:i A', $start_date . ' ' . $post['meeting_start_time'], $timezone );
		$end_date_time   = \date_create_from_format( 'Y-m-d h:i A', $end_date . ' ' . $post['meeting_end_time'], $timezone );

		$iso_start_datetime = '';
		$iso_end_datetime   = '';

		if ( $start_date && $start_date_time ) {
			$iso_start_datetime = $start_date_time->format( 'c' );
		} else {
			$validation_error[] = __( 'Invalid start date time', 'tutor-pro' );
		}

		if ( $end_date && $end_date_time ) {
			$iso_end_datetime = $end_date_time->format( 'c' );
		} else {
			$validation_error[] = __( 'Invalid end date time', 'tutor-pro' );
		}

		if ( count( $validation_error ) ) {
			wp_send_json_error( $validation_error );
		} else {
			// Make the create request.
			$event = new \Google_Service_Calendar_Event(
				array(
					'summary'        => $post['meeting_title'],
					'description'    => $post['meeting_summary'],
					'start'          => array(
						'dateTime' => $iso_start_datetime,
						'timeZone' => $post['meeting_timezone'],
					),
					'end'            => array(
						'dateTime' => $iso_end_datetime,
						'timeZone' => $post['meeting_timezone'],
					),
					'attendees'      => $attendees,
					'reminders'      => array(
						'useDefault' => false,
						'overrides'  => array(
							array(
								'method'  => 'email',
								'minutes' => $settings['reminder_time'] ?? 30,
							),
							array(
								'method'  => 'popup',
								'minutes' => $settings['reminder_time'] ?? 30,
							),
						),
					),
					'sendUpdates'    => $settings['send_updates'] ?? 'all',
					'transparency'   => $settings['transparency'] ?? 'transparent',
					'visibility'     => $settings['event_visibility'] ?? 'public',
					'status'         => $settings['event_status'] ?? 'confirmed',
					'conferenceData' => array(
						'createRequest' => array(
							'requestId' => 'meet_demo_' . microtime( true ),
						),
					),
				)
			);

			try {
				if ( isset( $post['event-id'] ) ) {
					$event = $this->update_meeting(
						$post['event-id'],
						$post['meeting_title'],
						$post['meeting_summary'],
						$iso_start_datetime,
						$iso_end_datetime,
						$post['meeting_timezone'],
						$attendees
					);
				} else {
					$event = $this->google_client->service->events->insert( $this->google_client->current_calendar, $event, array( 'conferenceDataVersion' => 1 ) );
				}

				$event_details = array(
					'id'             => $event->id,
					'kind'           => $event->kind,
					'event_type'     => $event->eventType,
					'html_link'      => $event->htmlLink,
					'organizer'      => $event->organizer,
					'recurrence'     => $event->recurrence,
					'reminders'      => $event->reminders,
					'status'         => $event->status,
					'transparency'   => $event->transparency,
					'visibility'     => $event->visibility,
					'meet_link'      => $event->hangoutLink,
					'start_datetime' => $start_date_time->format( 'Y-m-d H:i:s' ),
					'end_datetime'   => $end_date_time->format( 'Y-m-d H:i:s' ),
					'attendees'      => $post['attendees'],
					'timezone'       => $post['meeting_timezone'],
				);
				// Prepare post & meta data.
				$event_data = array(
					'post_title'   => $post['meeting_title'],
					'post_content' => $post['meeting_summary'],
					'post_parent'  => $post['course_id'],
					'post_type'    => EventsModel::POST_TYPE,
					'post_status'  => 'publish',
					'meta_input'   => array(
						EventsModel::POST_META_KEYS[0] => $start_date_time->format( 'Y-m-d H:i:s' ),
						EventsModel::POST_META_KEYS[1] => $end_date_time->format( 'Y-m-d H:i:s' ),
						EventsModel::POST_META_KEYS[2] => json_encode( $event_details ),
						EventsModel::POST_META_KEYS[3] => $event->hangoutLink,
					),
				);

				// If set post ID then update.
				if ( $is_update ) {
					$event_data['ID'] = $post['post-id'];
					$insert_event     = EventsModel::update( $event_data );
				} else {
					$insert_event = EventsModel::insert( $event_data );
				}

				if ( is_wp_error( $insert_event ) ) {
					wp_send_json_error( $insert_event->get_error_message() );
				} else {
					$editable_info  = array(
						'meeting_title'   => $event_data['post_title'],
						'meeting_summary' => $event_data['post_content'],
						'start_date'      => tutor_get_formated_date( get_option( 'date_format' ), $event_details['start_datetime'] ),
						'start_time'      => tutor_get_formated_date( get_option( 'time_format' ), $event_details['start_datetime'] ),
						'end_date'        => tutor_get_formated_date( get_option( 'date_format' ), $event_details['end_datetime'] ),
						'end_time'        => tutor_get_formated_date( get_option( 'time_format' ), $event_details['end_datetime'] ),
						'post_id'         => $insert_event,
						'event_id'        => $event_details['id'],
						'attendees'       => $event_details['attendees'],
						'timezone'        => $event_details['timezone'],
						'start_datetime'  => $event_details['start_datetime'],
						'end_datetime'    => $event_details['end_datetime'],
						'html_link'       => $event_details['html_link'],
					);
					$meta_box_table = $plugin_data['views'] . 'metabox/table.php';
					$content        = '';
					ob_start();
					if ( tutor()->course_post_type === $event_type ) {
						if ( file_exists( $meta_box_table ) ) {
							tutor_load_template_from_custom_path(
								$meta_box_table,
								$editable_info,
								false
							);
							$content = ob_get_clean();
						}
					} else {
						// load topic content.
						$topic_event = $plugin_data['views'] . 'topic/content.php';
						tutor_load_template_from_custom_path(
							$topic_event,
							array(
								'event'         => get_post( $insert_event ),
								'counter_index' => '',
							),
							false
						);
						$content = ob_get_clean();
					}
					$response = array(
						'selector'         => '',
						'content'          => $content,
						'date_time_markup' => Utilities::date_time_markup(),
					);

					/**
					 * Set select, this will be used to replace content
					 */
					if ( tutor()->course_post_type === $event_type ) {
						if ( $is_update ) {
							$response['selector'] = "#tutor-google-meet-list-item-{$insert_event}";
						} else {
							$response['selector'] = '.tutor-course-builder-google-meet-list';
						}
					} else {
						// Set topic selector.
						if ( $is_update ) {
							$response['selector'] = "#tutor-google-meet-lesson-{$insert_event}";
						} else {
							$response['selector'] = "#tutor-topics-{$post['course_id']} .tutor-lessons.ui-sortable ";
						}
					}

					wp_send_json_success( $response );
				}
			} catch ( \Throwable $th ) {
				$response = array(
					'error' => $th->getMessage(),
					'type'  => 'exception',
				);
				wp_send_json_error( $response );
			}
		}

	}

	/**
	 * Update Meeting
	 *
	 * @param string $event_id  event id to update.
	 * @param string $title  event title.
	 * @param string $summary  event summary.
	 * @param string $start_datetime  event start date time.
	 * @param string $end_datetime  event end date time.
	 * @param string $tz  event timezone.
	 * @param array  $attendees  attendees to include.
	 *
	 * @return mixed
	 */
	public function update_meeting( $event_id, $title, $summary, $start_datetime, $end_datetime, $tz, $attendees ) {
		$event = $this->google_client->service->events->get( $this->google_client->current_calendar, $event_id );
		$event->setSummary( $title );
		$event->setDescription( $summary );
		$date_time = new \Google_Service_Calendar_EventDateTime();
		// $date_time->setDateTime( strtotime( $start_datetime ) );
		$date_time->setDateTime( $start_datetime );
		$date_time->setTimeZone( $tz );

		$event->setStart( $date_time );

		$date_time = new \Google_Service_Calendar_EventDateTime();
		$date_time->setDateTime( $end_datetime );
		$date_time->setTimeZone( $tz );
		$event->setEnd( $date_time );

		$event->setAttendees( $attendees );

		return $this->google_client->service->events->update( $this->google_client->current_calendar, $event->getId(), $event );
	}

	/**
	 * Get meeting lists
	 *
	 * @since v2.1.0
	 *
	 * @param array $args WP_Query arguments.
	 * @param bool  $raw pass to true to get raw query.
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_query
	 *
	 * @return mixed
	 */
	public static function get_meetings( $args = array(), $raw = false ) {
		if ( Validator::current_user_has_access() ) {
			$default_args = array(
				'post_type'      => EventsModel::POST_TYPE,
				'post_status'    => 'publish',
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'posts_per_page' => tutor_utils()->get_option( 'pagination_per_page' ),
			);
			$args         = wp_parse_args( $args, $default_args );
			$the_query    = new WP_Query( $args );
			if ( $raw ) {
				return $the_query;
			}
			if ( $the_query->have_posts() ) {
				return $the_query->posts;
			}
			return array();
		}
		return false;
	}

	/**
	 * Delete event from Google calendar & database
	 *
	 * @return void
	 */
	public function delete_meeting() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged to make this request.
		if ( ! Validator::current_user_has_access() ) {
			wp_send_json_error(
				__( 'You are not authorized to make this request', 'tutor-pro' )
			);
		}

		// Check is app is authorized.
		if ( ! $this->google_client->is_app_permitted() ) {
			wp_send_json_error(
				__( $this->unauthorized_msg, 'tutor-pro' )
			);
		}

		$event_id       = Input::post( 'event-id' );
		$post_id        = Input::post( 'post-id' );
		$failed_msg     = __( 'Delete failed, try refreshing the page!', 'tutor-pro' );
		$success_msg    = __( 'Google meet deleted successfully!', 'tutor-pro' );
		$validation_msg = __( 'Post ID & Event ID is required!', 'tutor-pro' );

		if ( '' === $event_id || '' === $post_id ) {
			wp_send_json_error( $validation_msg );
		} else {
			if ( ! is_numeric( $post_id ) ) {
				wp_send_json_error( __( 'Invalid Post ID', 'tutor-pro' ) );
			}
		}

		try {

			$delete_event = $this->google_client->service->events->delete(
				$this->google_client->current_calendar,
				$event_id
			);

			if ( $delete_event ) {
				do_action( 'tutor_google_meet_deleted', $event_id, $post_id );

				// Delete from db.
				if ( EventsModel::delete( $post_id ) ) {
					do_action( 'tutor_google_meet_local_data_deleted', $event_id, $post_id );
					wp_send_json_success( $success_msg );
				} else {
					$failed_msg = __( 'Post delete failed, try refreshing the page!', 'tutor-pro' );
					wp_send_json_error( $failed_msg );
				}
			} else {
				wp_send_json_error( $failed_msg );
			}
		} catch ( \Throwable $th ) {
			$response = array(
				'error' => $th->getMessage(),
				'type'  => 'exception',
			);
			wp_send_json_error( $response );
		}
	}

	/**
	 * Load edit template
	 *
	 * @return void
	 */
	public function edit_meeting() {
		$post_id  = Input::post( 'post-id', 0, Input::TYPE_INT );
		$event_id = Input::post( 'event-id', 0, Input::TYPE_INT );

		if ( ! $post_id || ! $event_id ) {
			wp_send_json_error( __( 'Invalid post or event ID', 'tutor-pro' ) );
		}
		ob_start();
		$plugin_data = GoogleMeet::meta_data();
		tutor_load_template_from_custom_path(
			$plugin_data['views'] . 'modal/dynamic-modal-content.php',
			array(
				'post-id'  => $post_id,
				'event-id' => $event_id,
			),
			false
		);
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Unlink existing credential
	 *
	 * @since 2.1.3
	 *
	 * @return void send wp_json response
	 */
	public function reset_credential() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$file_name = md5( \wp_get_current_user()->user_login ) . '-credential.json';
		$file_path = \trailingslashit( wp_upload_dir()['basedir'] ) . 'tutor-json/' . $file_name;
		tutor_log( $file_path );
		if ( file_exists( $file_path ) ) {
			if ( unlink( $file_path ) ) {
				wp_send_json_success( __( 'Credential reset successfully!', 'tutor-pro' ) );
			} else {
				wp_send_json_error( __( 'Credential reset failed!', 'tutor-pro' ) );
			}
		} else {
			wp_send_json_error( __( 'Credential not exists!', 'tutor-pro' ) );
		}
	}
}
