<?php
/**
 * Tutor_Calendar for handle calendar logics
 *
 * @since 1.9.10
 *
 * @package Tutor Calendar
 */

namespace TUTOR_PRO_C;

use Tutor\Helpers\QueryHelper;
use TUTOR\Input;
use TutorPro\GoogleMeet\Models\EventsModel;
use TutorPro\GoogleMeet\Validator\Validator;

/**
 * Handle Tutor Calendar logics
 */
class Tutor_Calendar {

	/**
	 * Handle Dependencies Register Hooks
	 *
	 * @since 2.7.0 $register_hooks param added.
	 *
	 * @param bool $register_hooks register hooks or not.
	 */
	public function __construct( $register_hooks = true ) {
		if ( ! $register_hooks ) {
			return;
		}

		add_filter( 'tutor_dashboard/nav_items', array( $this, 'register_calendar_menu' ) );
		add_action( 'load_dashboard_template_part_from_other_location', array( $this, 'load_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_get_calendar_materials', array( $this, 'get_calendar_materials' ) );
	}

	public function register_calendar_menu( $nav_items ) {
		do_action( 'tutor_pro_before_calendar_menu_add', $nav_items );

		$nav_items['calendar'] = array(
			'title' => __( 'Calendar', 'tutor-pro' ),
			'icon'  => 'tutor-icon-calender-line',
		);
		return apply_filters( 'tutor_pro_after_calendar_menu', $nav_items );
	}

	public function load_template( $template ) {
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		if ( isset( $query_vars['tutor_dashboard_page'] ) && 'calendar' === $query_vars['tutor_dashboard_page'] ) {
			$calendar_template = tutor_pro_calendar()->path . 'templates/calendar.php';
			if ( file_exists( $calendar_template ) ) {
				return apply_filters( 'tutor_pro_calendar', $calendar_template );
			}
		}
		return $template;
	}

	public function enqueue_scripts() {
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		if ( isset( $query_vars['tutor_dashboard_page'] ) && 'calendar' === $query_vars['tutor_dashboard_page'] ) {
			wp_enqueue_script(
				'tutor-pro-calendar',
				tutor_pro_calendar()->url . 'assets/js/Calendar.js',
				array(),
				TUTOR_PRO_VERSION,
				true
			);
			wp_enqueue_style(
				'tutor-pro-calendar-css',
				tutor_pro_calendar()->url . 'assets/css/calendar.css',
				'',
				TUTOR_PRO_VERSION
			);
		}
	}

	/**
	 * Check assignment expired or not
	 *
	 * @param  $assignment_id int | required
	 * @return mixed array | false
	 * @since  1.9.10
	 */
	public static function assignment_info( int $assignment_id ) {
		$assignment_id = sanitize_text_field( $assignment_id );
		$time_duration = tutor_utils()->get_assignment_option(
			$assignment_id,
			'time_duration',
			array(
				'time'  => '',
				'value' => 0,
			)
		);
		$unlock_date   = tutor_utils()->get_item_content_drip_settings( $assignment_id, 'unlock_date' );

		$post = get_post( $assignment_id );
		if ( $post && ! is_null( $post ) ) {
			$assignment_created_time = strtotime( $post->post_date_gmt );
			$time_duration_in_sec    = 0;
			if ( isset( $time_duration['value'] ) && isset( $time_duration['time'] ) ) {
				switch ( $time_duration['time'] ) {
					case 'hours':
						$time_duration_in_sec = 3600;
						break;
					case 'days':
						$time_duration_in_sec = 86400;
						break;
					case 'weeks':
						$time_duration_in_sec = 7 * 86400;
						break;
					default:
						$time_duration_in_sec = 0;
						break;
				}
			}
			$time_duration_in_sec = $time_duration_in_sec * (int) $time_duration['value'];
			if ( empty( $unlock_date ) ) {
				$remaining_time = $assignment_created_time + $time_duration_in_sec;
			} else {
				$remaining_time = strtotime( $unlock_date ) + $time_duration_in_sec;
			}
			$now         = time();
			$week_values = array(
				'weeks' => __( 'Weeks', 'tutor-pro' ),
				'days'  => __( 'Days', 'tutor-pro' ),
				'hours' => __( 'Hours', 'tutor-pro' ),
			);
			return array(
				'duration'     => $time_duration['value'] == 0 ? __( 'No Limit', 'tutor-pro' ) : $time_duration['value'] . ' ' . $week_values[ $time_duration['time'] ],
				'is_expired'   => ( $time_duration['value'] == 0 ? false : ( $now > $remaining_time ? true : false ) ),
				'expire_date'  => $time_duration['value'] == 0 ? __( 'No Limit', 'tutor-pro' ) : date( get_option( 'date_format' ), $remaining_time ),
				'expire_month' => $time_duration['value'] == 0 ? __( 'No Limit', 'tutor-pro' ) : date( 'n', $remaining_time ),
				'unlock_date'  => $unlock_date,
			);
		}
		return false;
	}

	/**
	 * Quiz info time_limit|remaining_attempt|is_attempt_available
	 *
	 * @param  $quiz_id int | required
	 * @return array
	 * @since  1.9.10
	 */
	public static function quiz_info( int $quiz_id ): array {
		$quiz_id           = sanitize_text_field( $quiz_id );
		$time_limit        = tutor_utils()->get_quiz_option( $quiz_id, 'time_limit.time_value' );
		$time_type         = tutor_utils()->get_quiz_option( $quiz_id, 'time_limit.time_type' );
		$previous_attempts = tutor_utils()->quiz_attempts( $quiz_id );
		$attempted_count   = is_array( $previous_attempts ) ? count( $previous_attempts ) : 0;

		$attempts_allowed     = tutor_utils()->get_quiz_option( get_the_ID(), 'attempts_allowed', 0 );
		$attempt_remaining    = $attempts_allowed - $attempted_count;
		$is_attempt_available = false;

		if ( $attempts_allowed == 0 ) {
			$is_attempt_available = true;
		} else {
			if ( $attempt_remaining ) {
				$is_attempt_available = true;
			} else {
				$is_attempt_available = false;
			}
		}
		$available_time_types = array(
			'seconds' => __( 'Seconds', 'tutor-pro' ),
			'minutes' => __( 'Minutes', 'tutor-pro' ),
			'weeks'   => __( 'Weeks', 'tutor-pro' ),
			'days'    => __( 'Days', 'tutor-pro' ),
			'hours'   => __( 'Hours', 'tutor-pro' ),
		);
		return array(
			'time_limit'           => $time_limit . ' ' . $available_time_types[ $time_type ],
			'is_attempt_available' => $is_attempt_available,
			'attempt_remaining'    => $attempts_allowed == 0 ? __( 'No Limit', 'tutor-pro' ) : $attempt_remaining,
		);
	}

	/**
	 * Get zoom meeting list by course ids, year, month
	 *
	 * @param array  $course_ids
	 * @param string $year
	 * @param string $month
	 * @return array|object|null
	 *
	 * @since 2.0.7
	 */
	public function get_zoom_meeting_list( array $course_ids, $year, $month ) {
		global $wpdb;

		$ids_str = implode( ',', $course_ids );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM(
            SELECT MONTH(p.post_date) AS month,
            DATE(p.post_date) AS created_at,
            (
              select meta_value from {$wpdb->postmeta}
              where post_id = p.ID AND meta_key = '_tutor_zm_start_datetime'
            ) zoom_meeting_dt,
            (
              select case when NOW() > meta_value  then 1
                    else 0
                    end
              from {$wpdb->postmeta}
              where post_id = p.ID AND meta_key = '_tutor_zm_start_datetime'
            ) is_expired,
            (
              select meta_value from {$wpdb->postmeta}
              where post_id = p.ID AND meta_key = '_tutor_zm_for_topic'
            ) topic_id,
            (
              select post_title
              from {$wpdb->postmeta}
              left join {$wpdb->posts} on {$wpdb->posts}.ID = meta_value
              where post_id = p.ID AND meta_key = '_tutor_zm_for_topic'
            ) topic_title,
            (
              select 
                  case when meta_value > 0 then (select post_parent from {$wpdb->posts} where ID=meta_value)
                  else post_parent
                  end
              from {$wpdb->postmeta} where post_id = p.ID AND meta_key = '_tutor_zm_for_topic'
            ) course_id,
            p.ID, p.post_title, p.post_date, p.post_type, p.guid, p.post_content
          from
          {$wpdb->posts} p
          where
              p.post_type = 'tutor_zoom_meeting'
              AND YEAR(p.post_date)= %d
              AND MONTH(p.post_date) = %d
          ) A 
          WHERE course_id IN ({$ids_str})",
				$year,
				$month
			)
		);

		foreach ( $results as $meeting ) {
			// Format date.
			$meeting->post_date     = \tutor_get_formated_date( get_option( 'date_format' ), $meeting->post_date );
			$meeting->zm_start_date = \tutor_get_formated_date( get_option( 'date_format' ), $meeting->zoom_meeting_dt );

			$meeting->meta_info = array(
				'expire_date'          => $meeting->zoom_meeting_dt,
				'expire_date_readable' => tutor_utils()->get_human_readable_time( $meeting->zoom_meeting_dt, null, '%ad:%hh:%im' ),
				'is_expired'           => $meeting->is_expired === '1' ? true : false,
			);
		}

		return $results;
	}

	/**
	 * Handle ajax post request
	 *
	 * Merge assignment info with assignment post data
	 *
	 * @param int $month month in number.
	 * @param int $year the year.
	 * @param int $user_id user id.
	 *
	 * @return void|array
	 *
	 * @since 1.9.10
	 * @since 2.6.2 Added params $month, $year & user_id.
	 */
	public function get_calendar_materials( $month = 0, $year = 0, $user_id = 0 ) {
		if ( ! tutor_is_rest() ) {
			tutor_utils()->checking_nonce();
			$year  = Input::post( 'year', 0, Input::TYPE_INT );
			$month = Input::post( 'month', 0, Input::TYPE_INT );
			$month = 1 + $month;
		}

		$response = '';
		$user_id  = tutor_utils()->get_user_id( $user_id );
		global $wpdb;

		$enrolled_courses    = tutor_utils()->get_enrolled_courses_by_user( $user_id );
		$enrolled_course_ids = tutor_utils()->get_enrolled_courses_ids_by_user( $user_id );

		$post_types = array(
			tutor()->assignment_post_type,
		);

		// If google meet addon is enabled then include meet post type.
		$is_enabled_gm = false;
		if ( class_exists( 'TutorPro\GoogleMeet\Validator\Validator' ) && Validator::is_addon_enabled() ) {
			$is_enabled_gm = true;
			$post_types[]  = tutor()->meet_post_type;
		}

		// Check zoom addon enabled or not.
		$is_enabled_zm = tutor_utils()->is_addon_enabled( TUTOR_ZOOM()->basename );

		// Check content drip addon enabled or not.
		// If enabled then include lesson and quiz post type.
		$is_enabled_cd = tutor_utils()->is_addon_enabled( TUTOR_CONTENT_DRIP()->basename );
		if ( $is_enabled_cd ) {
			$post_types[] = tutor()->lesson_post_type;
			$post_types[] = tutor()->quiz_post_type;
		}

		$in_clause = QueryHelper::prepare_in_clause( $post_types );

		if ( false === $enrolled_courses ) {
			$data = array();
		} else {
			$data = array( 0 );
			foreach ( $enrolled_courses->posts as $key => $course ) {
				$topics = tutor_utils()->get_topics( $course->ID );
				foreach ( $topics->posts as $topic ) {
					$data[] = $topic->ID;
				}
			}

			// If google meet enabled then merge course ids with topic ids.
			// To get meeting that is under course.
			if ( $is_enabled_gm ) {
				$data = array_merge( $data, $enrolled_course_ids );
			}

			$data = implode( ',', $data );

			$query = "SELECT 
							ID,
							DATE (post_date) AS post_date, 
							MONTH(post_date) AS month, 
							DATE(post_date) AS created_at, 
							post_title, 
							post_content,
							post_parent, 
							guid, 
							post_type 
                        FROM {$wpdb->posts} 
                        WHERE post_parent IN  ({$data})
                            AND post_type IN ($in_clause)
                            AND post_status = %s
                            
                            AND YEAR(post_date) = %d
                        GROUP BY post_date
                        ORDER BY post_date ASC
                    ";

			$results = $wpdb->get_results(
				$wpdb->prepare(
					$query,//phpcs:ignore
					'publish',
					$year
				)
			);

			$response = array();

			foreach ( $results as $key => $result ) {
				$result->post_date = \tutor_get_formated_date( get_option( 'date_format' ), $result->post_date );

				if ( tutor()->assignment_post_type === $result->post_type ) {
					$result->meta_info = self::assignment_info( $result->ID );
				} elseif ( tutor()->lesson_post_type === $result->post_type || tutor()->quiz_post_type === $result->post_type ) {

					$course_id = $result->post_parent;
					if ( tutor()->topics_post_type === get_post_type( $course_id ) ) {
						$course_id = wp_get_post_parent_id( $course_id );
					}

					$unlock_date = self::get_unlock_date( $result->ID, $course_id );
					if ( $unlock_date['unlock_date'] ) {
						$is_unlocked                = time() > strtotime( $unlock_date['unlock_date'] );
						$unlock_date['is_unlocked'] = $is_unlocked;
						$result->meta_info          = $unlock_date;
					} else {
						// Remove from events if there is no unlock date.
						unset( $results[ $key ] );
					}
				} elseif ( tutor()->meet_post_type === $result->post_type ) {
					if ( class_exists( 'TutorPro\GoogleMeet\Models\EventsModel' ) ) {
						$start_datetime = get_post_meta( $result->ID, EventsModel::POST_META_KEYS[0], true );
						$end_datetime   = get_post_meta( $result->ID, EventsModel::POST_META_KEYS[1], true );

						$result->meta_info = array(
							'gm_start_date'        => \tutor_get_formated_date( get_option( 'date_format' ), $start_datetime ),
							'expire_date'          => \tutor_get_formated_date( get_option( 'date_format' ), $start_datetime ),
							'expire_date_readable' => tutor_utils()->get_human_readable_time( $end_datetime, null, '%ad:%hh:%im' ),
							'is_expired'           => time() > strtotime( $end_datetime ) ? true : false,
						);
					} else {
						// Remove meet.
						unset( $results[ $key ] );
					}
				}
			}

			$overdue  = 0;
			$upcoming = 0;
			foreach ( $results as $r ) {
				( isset( $r->meta_info['is_expired'] ) && $r->meta_info['is_expired'] ) ? $overdue++ : $upcoming++;
				if ( $r->month == $month || ( isset( $r->meta_info['expire_month'] ) && $r->meta_info['expire_month'] == $month ) ) {
					array_push( $response, $r );
				}
			}

			// zoom meetings.
			$meeting_list = $is_enabled_zm ? $this->get_zoom_meeting_list( $enrolled_course_ids, $year, $month ) : array();
			$response     = array_merge( $response, $meeting_list );

			$data = array(
				'response' => $response,
				'overdue'  => $overdue,
				'upcoming' => $upcoming,
			);
		}

		if ( ! tutor_is_rest() ) {
			wp_send_json_success( $data );
			exit;
		} else {
			return $data;
		}
	}

	/**
	 * Get post unlock date
	 *
	 * Basically the unlock date of lesson, quiz & assignment
	 *
	 * @since 2.4.0
	 *
	 * @param integer $post_id post id.
	 *
	 * @return array
	 */
	public static function get_unlock_date( int $post_id, int $course_id ) {
		$res = array(
			'unlock_date' => null,
			'is_unlocked' => false,
		);

		$enable = (bool) get_tutor_course_settings( $course_id, 'enable_content_drip' );

		// If content drip not enabled then return null date.
		if ( ! $enable ) {
			return $res;
		}

		$drip_type = get_tutor_course_settings( $course_id, 'content_drip_type', 'unlock_by_date' );

		if ( 'unlock_by_date' === $drip_type || 'specific_days' === $drip_type ) {

			$unlock_date = null;

			if ( 'unlock_by_date' === $drip_type ) {
				$unlock_date = get_item_content_drip_settings( $post_id, 'unlock_date' );
			}

			if ( 'specific_days' === $drip_type ) {
				$days = (int) get_item_content_drip_settings( $post_id, 'after_xdays_of_enroll' );

				if ( $days > 0 ) {
					$enroll       = tutor_utils()->is_course_enrolled_by_lesson( $post_id );
					$enroll_date  = tutor_utils()->array_get( 'post_date', $enroll );
					$enroll_date  = gmdate( 'Y-m-d', strtotime( $enroll_date ) );
					$days_in_time = 60 * 60 * 24 * $days;

					$unlock_timestamp = strtotime( $enroll_date ) + $days_in_time;

					$unlock_date = date_i18n( get_option( 'date_format' ), $unlock_timestamp );
				}
			}

			$res['unlock_date'] = $unlock_date;
		}

		return $res;
	}
}
