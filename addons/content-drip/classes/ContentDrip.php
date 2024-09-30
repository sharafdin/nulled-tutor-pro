<?php
/**
 * ContentDrip class
 *
 * @author themeum
 * @link https://themeum.com
 * @package TutorPro\ContentDrip
 * @since 1.4.1
 */

namespace TUTOR_CONTENT_DRIP;

use TUTOR\Input;
use Tutor\Models\QuizModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ContentDrip {

	private $unlock_timestamp  = false;
	private $unlock_message    = null;
	private $drip_type         = null;
	private $mail_log_meta_key = '_tutor_pro_content_drip_mail_log';
	private $sent_mail_log     = array();
	private $send_limit        = 5;
	private $quiz_pass_req     = false;
	/**
	 * Exclude Zoom, Meet meeting when sequential mode enabled
	 *
	 * @var array
	 */
	private $exclude_type = array( 'tutor_zoom_meeting', 'tutor-google-meet' );

	private $quiz_manual_review_required = false;

	private $singular_post_type;

	public function __construct() {

		/**
		 * add meta box for lesson post type
		 * add support content drip on single lesson
		 *
		 * @since 1.8.9
		*/
		// Commented for now showing content drip metabox in Lesson WP Editor
		// add_action( 'add_meta_boxes', array( $this, 'register_content_drip_meta_box' ) );

		add_filter( 'tutor_course_settings_tabs', array( $this, 'settings_attr' ) );

		add_action( 'tutor_lesson_edit_modal_after_video', array( $this, 'content_drip_lesson_metabox' ), 10, 0 );
		add_action( 'tutor_quiz_edit_modal_settings_tab_after_max_allowed_questions', array( $this, 'content_drip_lesson_metabox' ), 10, 0 );
		add_action( 'tutor_assignment_edit_modal_form_after_attachments', array( $this, 'content_drip_lesson_metabox' ), 10, 0 );

		add_action( 'tutor/lesson_update/after', array( $this, 'lesson_updated' ) );
		add_action( 'tutor_quiz_settings_updated', array( $this, 'lesson_updated' ) );
		add_action( 'tutor_assignment_updated', array( $this, 'lesson_updated' ) );
		add_action( 'tutor_assignment_created', array( $this, 'lesson_updated' ) );

		add_action( 'tutor_quiz_builder_settings_tab_passing_grade_before', array( $this, 'render_quiz_pass_required_field' ), 10, 2 );

		/**
		 * on save lesson update content drip meta
		 *
		 * @since 1.8.9
		*/
		add_action( 'save_post_' . tutor()->lesson_post_type, array( $this, 'lesson_updated' ), 10, 1 );

		add_action( 'tutor/lesson_list/right_icon_area', array( $this, 'show_content_drip_icon' ) );
		add_filter( 'tutor_course/single/content/show_permalink', array( $this, 'alter_lqaz_show_permalink' ), 10, 2 );

		add_filter( 'tutor_lesson/single/content', array( $this, 'drip_content_protection' ) );
		add_filter( 'tutor_quiz/single/wrapper', array( $this, 'drip_content_protection' ) );
		add_filter( 'tutor_assignment/single/content', array( $this, 'drip_content_protection' ) );

		// Lesson-Quiz-Assignment onPublish Mailing
		add_action( 'init', array( $this, 'execute_content_drip_publish_hook' ) );
		add_filter( 'tutor_emails/dashboard/list', array( $this, 'register_email_list' ), 11 );

		add_action( 'wp_ajax_tutor_content_drip_state_update', array( $this, 'tutor_content_drip_state_update' ) );
		/**
		 * On mobile view mark as complete disable if lesson is locked
		 *
		 * @since 2.1.9
		 */
		add_filter( 'tutor_lesson_show_mark_as_complete', array( $this, 'check_if_lesson_is_locked' ) );
	}

	public function check_if_lesson_is_locked( $status ) {
		return $this->is_lock_lesson( get_the_ID() ) ? false : $status;
	}

	public function tutor_content_drip_state_update() {
		tutor_utils()->checking_nonce();
		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );
		if ( ! tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id ) ) {
			wp_send_json_error();
		}

		$course = get_post( $course_id );

		do_action( 'tutor_save_course_settings', $course_id, $course );
		wp_send_json_success();
	}

	public function settings_attr( $args ) {
		$args['contentdrip'] = array(
			'label'      => __( 'Content Drip', 'tutor-pro' ),
			'desc'       => __( 'Tutor Content Drip allow you to schedule publish topics / lesson', 'tutor-pro' ),
			'icon_class' => 'tutor-icon-clock-line-o',
			'callback'   => '',
			'fields'     => array(
				'_tutor_course_settings[enable_content_drip]' => array(
					'id'      => 'content_drip',
					'type'    => 'checkbox',
					'label'   => '',
					'desc'    => __( 'Enable / Disable content drip', 'tutor-pro' ),
					'options' => array(
						array(
							'label_title' => __( 'Enable', 'tutor-pro' ),
							'checked'     => (bool) tutor_utils()->get_course_settings( get_the_ID(), 'enable_content_drip' ),
							'value'       => 1,
						),
					),
				),
				'line_break_key' => array(
					'type' => 'line_break',
				),
				'_tutor_course_settings[content_drip_type]' => array(
					'type'        => 'radio',
					'label'       => __( 'Content Drip Type', 'tutor-pro' ),
					'is_vertical' => true,
					'value'       => tutor_utils()->get_course_settings( get_the_ID(), 'content_drip_type', 'unlock_by_date' ),
					'options'     => array(
						'unlock_by_date'                => __( 'Schedule course contents by date', 'tutor-pro' ),
						'specific_days'                 => __( 'Content available after X days from enrollment', 'tutor-pro' ),
						'unlock_sequentially'           => __( 'Course content available sequentially', 'tutor-pro' ),
						'after_finishing_prerequisites' => __( 'Course content unlocked after finishing prerequisites', 'tutor-pro' ),
					),
					'desc'        => __( 'You can schedule your course content using the above content drip options.', 'tutor-pro' ),
				),
			),
		);
		return $args;
	}

	/**
	 * Render quiz pass required toggle field in quiz builder settings tab
	 *
	 * @param int $course_id
	 * @param int $quiz_id
	 * @return void
	 *
	 * @since 2.1.0
	 */
	public function render_quiz_pass_required_field( $course_id, $quiz_id ) {
		$content_drip_enabled    = (bool) get_tutor_course_settings( $course_id, 'enable_content_drip' );
		$content_drip_type       = get_tutor_course_settings( $course_id, 'content_drip_type', 'unlock_sequentially' );
		$content_drip_sequential = $content_drip_type === 'unlock_sequentially';

		if ( $content_drip_enabled && $content_drip_sequential ) {
			include TUTOR_CONTENT_DRIP()->path . 'views/quiz-pass-required-field.php';
		}
	}

	public function content_drip_lesson_metabox() {
		include TUTOR_CONTENT_DRIP()->path . 'views/content-drip-lesson.php';
	}

	public function lesson_updated( $lesson_id ) {
		$content_drip_settings = tutor_utils()->array_get( 'content_drip_settings', $_POST );
		if ( tutor_utils()->count( $content_drip_settings ) ) {
			update_post_meta( $lesson_id, '_content_drip_settings', $content_drip_settings );
		}
	}

	/**
	 * @param $post
	 *
	 * Show lock icon based on condition
	 */
	public function show_content_drip_icon( $post ) {
		$is_lock = $this->is_lock_lesson( $post->ID );

		if ( $is_lock ) {
			echo '<i class="tutor-icon-lock-line tutor-fs-7 tutor-color-muted tutor-mr-4" area-hidden="true"></i>';
		}
	}

	public function alter_lqaz_show_permalink( $bool, $id ) {
		if ( ! $bool ) {
			return $bool;
		}

		return $this->is_lock_lesson( $id ) ? null : $bool;
	}

	public function is_lock_lesson( $content_id ) {
		$content_type     = get_post_field( 'post_type', $content_id );
		$lesson_post_type = tutor()->lesson_post_type;
		$course_id        = tutor_utils()->get_course_id_by_content( $content_id );

		$enable = (bool) get_tutor_course_settings( $course_id, 'enable_content_drip' );
		if ( ! $enable ) {
			return false;
		}

		$drip_type       = get_tutor_course_settings( $course_id, 'content_drip_type', 'unlock_by_date' );
		$this->drip_type = $drip_type;

		$courseObg                = get_post_type_object( $content_type );
		$this->singular_post_type = empty( $courseObg->labels->singular_name ) ? '' : $courseObg->labels->singular_name;

		if ( $drip_type === 'unlock_by_date' ) {
			$unlock_timestamp = strtotime( get_item_content_drip_settings( $content_id, 'unlock_date' ) );
			if ( $unlock_timestamp ) {
				$unlock_date          = date_i18n( get_option( 'date_format' ), $unlock_timestamp );
				$this->unlock_message = sprintf( __( 'This %1$s will be available from %2$s', 'tutor-pro' ), $this->singular_post_type, $unlock_date );

				return $unlock_timestamp > current_time( 'timestamp' );
			}
		} elseif ( $drip_type === 'specific_days' ) {
			$days = (int) get_item_content_drip_settings( $content_id, 'after_xdays_of_enroll' );

			if ( $days > 0 ) {
				$enroll       = tutor_utils()->is_course_enrolled_by_lesson( $content_id );
				$enroll_date  = tutor_utils()->array_get( 'post_date', $enroll );
				$enroll_date  = date( 'Y-m-d', strtotime( $enroll_date ) );
				$days_in_time = 60 * 60 * 24 * $days;

				$unlock_timestamp = strtotime( $enroll_date ) + $days_in_time;

				$unlock_date          = date_i18n( get_option( 'date_format' ), $unlock_timestamp );
				$this->unlock_message = sprintf( __( 'This %1$s will be available for you from %2$s', 'tutor-pro' ), $this->singular_post_type, $unlock_date );

				return $unlock_timestamp > current_time( 'timestamp' );
			}
		}

		if ( $drip_type === 'unlock_sequentially' && ! tutor_utils()->has_user_course_content_access( get_current_user_id(), $course_id ) ) {
			/**
			 * Lock exclude Zoom, Meet meeting when sequential mode enabled
			 */
			$previous_id = tutor_utils()->get_course_previous_content_id( $content_id, $this->exclude_type );

			if ( $previous_id ) {
				$previous_content = get_post( $previous_id );

				$obj = get_post_type_object( $previous_content->post_type );

				// Lesson
				if ( $previous_content->post_type === $lesson_post_type ) {
					$is_lesson_complete = tutor_utils()->is_completed_lesson( $previous_id );
					if ( ! $is_lesson_complete ) {
						$this->unlock_message = sprintf( __( 'Please complete previous %s first', 'tutor-pro' ), $obj->labels->singular_name );
						return true;
					}
				}

				// Assignment
				if ( $previous_content->post_type === 'tutor_assignments' ) {
					$is_submitted = tutor_utils()->is_assignment_submitted( $previous_id );
					if ( ! $is_submitted ) {
						$this->unlock_message = sprintf( __( 'Please submit previous %s first', 'tutor-pro' ), $obj->labels->singular_name );
						return true;
					}
				}

				// Quiz
				if ( $previous_content->post_type === 'tutor_quiz' ) {
					$attempts = tutor_utils()->quiz_ended_attempts( $previous_id );

					// Need to reset it first.
					$this->quiz_manual_review_required = false;
					$this->quiz_pass_req               = false;

					if ( ! $attempts ) {
						$this->unlock_message = sprintf( __( 'Please complete previous %s first', 'tutor-pro' ), $obj->labels->singular_name );
						return true;
					}

					/**
					 * Previous quiz pass required to access next course content
					 *
					 * @since 2.1.0
					 */
					$previous_pass_required = tutor_utils()->get_quiz_option( $previous_id, 'pass_is_required' );
					$passed_previous_quiz   = QuizModel::is_quiz_passed( $previous_id, get_current_user_id() );
					$is_retry_mode          = 'retry' === tutor_utils()->get_quiz_option( $previous_id, 'feedback_mode', 'default' );
					if ( $is_retry_mode && $previous_pass_required && ! $passed_previous_quiz ) {
						$this->unlock_message              = sprintf( __( 'To access this %s you have to pass the quiz', 'tutor-pro' ), $courseObg->labels->singular_name ) . '<a href="' . esc_url( get_permalink( $previous_id ) ) . '" style="color:#3E64DE" >`' . esc_html( $previous_content->post_title ) . '`</a>';
						$this->quiz_pass_req               = true;
						$this->quiz_manual_review_required = QuizModel::is_manual_review_required( $previous_id );

						return true;
					}
				}
			}
		} elseif ( $drip_type === 'after_finishing_prerequisites' ) {
			$prerequisites = (array) get_item_content_drip_settings( $content_id, 'prerequisites' );
			$prerequisites = array_filter( $prerequisites );

			if ( tutor_utils()->count( $prerequisites ) ) {
				$required_finish = array();

				foreach ( $prerequisites as $id ) {
					$item = get_post( $id );

					if ( ! $item || ! is_object( $item ) ) {
						continue;
					}

					if ( $item->post_type === $lesson_post_type ) {
						$is_lesson_complete = tutor_utils()->is_completed_lesson( $id );
						if ( ! $is_lesson_complete ) {
							$required_finish[] = "<a href='" . get_permalink( $item ) . "' target='_blank'>{$item->post_title}</a>";
						}
					}
					if ( $item->post_type === 'tutor_assignments' ) {
						$is_submitted = tutor_utils()->is_assignment_submitted( $id );
						if ( ! $is_submitted ) {
							$required_finish[] = "<a href='" . get_permalink( $item ) . "' target='_blank'>{$item->post_title}</a>";
						}
					}
					if ( $item->post_type === 'tutor_quiz' ) {
						$attempts = tutor_utils()->quiz_ended_attempts( $id );
						if ( ! $attempts ) {
							$required_finish[] = "<a href='" . get_permalink( $item ) . "' target='_blank'>{$item->post_title}</a>";
						}
					}
				}

				if ( tutor_utils()->count( $required_finish ) ) {
					$output  = the_title( '<div class="tutor-assignment-title tutor-fs-4 tutor-fw-medium tutor-color-black">', '</div>', false );
					$output .= '<h4>' . sprintf( __( 'You can take this %s after finishing the following prerequisites:', 'tutor-pro' ), $this->singular_post_type ) . '</h4>';
					$output .= '<ul>';
					foreach ( $required_finish as $required_finish_item ) {
						$output .= "<li>{$required_finish_item}</li>";
					}
					$output .= '</ul>';

					$this->unlock_message = $output;
					return true;
				}
			}
		}

		return false;
	}

	public function drip_content_protection( $html ) {
		$content_id = get_the_ID();

		if ( $this->is_lock_lesson( $content_id ) ) {
			$course_id   = tutor_utils()->get_course_id_by_subcontent( $content_id );
			$previous_id = tutor_utils()->get_course_previous_content_id( $content_id, $this->exclude_type );

			$header = '';
			if ( get_post_field( 'post_type', $content_id ) != 'tutor_quiz' ) {
				ob_start();
				tutor_load_template(
					'single.common.header',
					array(
						'course_id'        => $course_id,
						'mark_as_complete' => false,
					)
				);
				$header = ob_get_clean();
			}

			if ( $this->drip_type === 'after_finishing_prerequisites' ) {
				$img_url = trailingslashit( TUTOR_CONTENT_DRIP()->url ) . 'assets/images/traffic-light.svg';

				$output = "<div class='content-drip-message-wrap content-drip-wrap-flex'> <div class='content-drip-left'><img src='{$img_url}' alt='' /> </div> <div class='content-drip-right'>{$this->unlock_message}</div> </div>";

				$output = apply_filters( 'tutor/content_drip/unlock_message', $output );
				return $header . "<div class='tutor-lesson-content-drip-wrap'> {$output} </div>";

			} elseif ( $this->drip_type == 'unlock_sequentially' && $previous_id ) {

				$post_types = array(
					'lesson'             => __( 'Lesson', 'tutor-pro' ),
					'tutor_quiz'         => __( 'Quiz', 'tutor-pro' ),
					'tutor_assignments'  => __( 'Assignment', 'tutor-pro' ),
					'tutor_zoom_meeting' => __( 'Meeting', 'tutor-pro' ),
				);

				$previous_title        = get_the_title( $previous_id );
				$previous_permalink    = get_permalink( $previous_id );
				$previous_content_type = get_post_type( $previous_id );
				$previous_content_type = isset( $post_types[ $previous_content_type ] ) ? $post_types[ $previous_content_type ] : $previous_content_type;

				ob_start();
				require dirname( __DIR__ ) . '/views/restrict-sequence.php';
				return $header . apply_filters( 'tutor/content_drip/unlock_message', ob_get_clean() );

			} else {
				$output = apply_filters( 'tutor/content_drip/unlock_message', "<div class='content-drip-message-wrap tutor-alert'> {$this->unlock_message}</div>" );
				return $header . "<div class='tutor-lesson-content-drip-wrap'> {$output} </div>";
			}
		}

		return $html;
	}


	// Register list for emails in dashboard
	public function register_email_list( $emails ) {

		$lqa = '{site_url}, {site_name}, {student_username}, {lqa_type}, {course_title}, ';

		$emails['email_to_students.new_lesson_published']     = array( __( 'Content Drip: New Lesson Published', 'tutor-pro' ), $lqa . '{lesson_title}' );
		$emails['email_to_students.new_quiz_published']       = array( __( 'Content Drip: New Quiz Published', 'tutor-pro' ), $lqa . '{quiz_title}' );
		$emails['email_to_students.new_assignment_published'] = array( __( 'Content Drip: New Assignment Published', 'tutor-pro' ), $lqa . '{assignment_title}' );

		return $emails;
	}

	// List all the courses where content drip enabled
	private function get_mailing_courses() {

		global $wpdb;

		$drip_enabled = esc_sql( 's:19:"enable_content_drip";s:1:"1";' );

		// Get courses that is published and content drip enabled
		$courses = $wpdb->get_results(
			"SELECT {$wpdb->posts}.ID, {$wpdb->posts}.post_title, {$wpdb->postmeta}.meta_value
			FROM {$wpdb->posts} LEFT JOIN {$wpdb->postmeta}
			ON {$wpdb->posts}.ID={$wpdb->postmeta}.post_id
			WHERE {$wpdb->posts}.post_status='publish'
				AND {$wpdb->postmeta}.meta_key='_tutor_course_settings'
				AND {$wpdb->postmeta}.meta_value LIKE '%{$drip_enabled}%'"
		);

		$courses = array_map(
			function( $element ) {
				$element->meta_value = unserialize( $element->meta_value );
				return $element;
			},
			$courses
		);

		return $courses;
	}

	// Get all the lesson, quizzes and assignments by course ID
	private function get_mailing_course_children( $course_id ) {

		global $wpdb;
		$topic_ids     = "SELECT ID FROM {$wpdb->posts} WHERE post_parent={$course_id} AND post_type='topics'";
		$content_types = "'lesson', 'tutor_quiz', 'tutor_assignments'";

		return $wpdb->get_results(
			"SELECT ID, post_title, post_type
			FROM {$wpdb->posts}
			WHERE post_parent IN ({$topic_ids})
				AND post_type IN ({$content_types})"
		);
	}

	// Check if mail sent to specific user for specific lesson-quiz-assignment publish
	private function is_mail_sent( $student_id, $content_id, $time_stamp ) {

		if ( ! isset( $this->sent_mail_log[ $student_id ] ) ) {
			$log                                = get_user_meta( $student_id, $this->mail_log_meta_key, true );
			$this->sent_mail_log[ $student_id ] = is_array( $log ) ? $log : array();
		}

		$log = array_key_exists( $content_id, $this->sent_mail_log[ $student_id ] ) ? $this->sent_mail_log[ $student_id ][ $content_id ] : array();

		if ( in_array( $time_stamp, $log ) ) {
			return true;
		}

		$log[] = $time_stamp;
		$this->sent_mail_log[ $student_id ][ $content_id ] = $log;
	}

	// Get the timestamp when the LQA should be considered as published
	private function get_content_publish_timestamp( $content_id, $enroll_date, bool $unlock_by_date ) {
		$timestamp = null;

		if ( $unlock_by_date ) {
			$timestamp = (int) strtotime( get_item_content_drip_settings( $content_id, 'unlock_date' ) );
		} else {
			$days = (int) get_item_content_drip_settings( $content_id, 'after_xdays_of_enroll' );

			if ( $days > 0 ) {
				$enroll_date  = date( 'Y-m-d', strtotime( $enroll_date ) );
				$days_in_time = 60 * 60 * 24 * $days;

				$timestamp = strtotime( $enroll_date ) + $days_in_time;
			}
		}

		return $timestamp;
	}

	// Get enrollment list of published course by course ID
	private function get_mailing_enrollments( $course_id ) {

		global $wpdb;

		$enrollments = $wpdb->get_results(
			"SELECT {$wpdb->posts}.ID as enrolment_id,
				{$wpdb->posts}.post_date AS enroll_date,
				{$wpdb->users}.ID as student_id,
				{$wpdb->users}.user_email,
				{$wpdb->users}.display_name
			FROM {$wpdb->posts}
			LEFT JOIN {$wpdb->users} ON {$wpdb->posts}.post_author={$wpdb->users}.ID
			WHERE {$wpdb->posts}.post_parent={$course_id}
				AND {$wpdb->posts}.post_type='tutor_enrolled'
				AND {$wpdb->posts}.post_status='completed'"
		);

		return $enrollments;
	}

	// Initialize content drip publication hooks
	public function execute_content_drip_publish_hook() {

		$last_call = get_option( 'tutor_cd_last_call_time', null );

		if ( ! $last_call || $last_call < ( time() - 3600 ) ) {
			update_option( 'tutor_cd_last_call_time', time(), true );
		} else {
			return;
		}

		$mail_enable_status = array();

		// Loop through published courses
		$courses = $this->get_mailing_courses();
		foreach ( $courses as $course ) {

			$drip_type = isset( $course->meta_value['content_drip_type'] ) ? $course->meta_value['content_drip_type'] : null;

			if ( ! $drip_type || ( $drip_type !== 'unlock_by_date' && $drip_type !== 'specific_days' ) ) {
				// No need to send mail for other drip types. Or no drip defined
				continue;
			}

			$students = $this->get_mailing_enrollments( $course->ID );
			$contents = $this->get_mailing_course_children( $course->ID );

			// Loop through lesson, quiz and assignments
			foreach ( $contents as $content ) {

				$event = trim( $content->post_type, 'tutor_' ); // lesson, quiz or assignments;
				$event = trim( $event, 's' ); // lesson, quiz or assignment;

				if ( ! array_key_exists( $event, $mail_enable_status ) ) {
					$mail_enable_status[ $event ] = tutor_utils()->get_option( 'email_to_students.new_' . $event . '_published' );
				}

				if ( ! $mail_enable_status[ $event ] ) {
					continue;
				}

				foreach ( $students as $student ) {

					$unlocK_timestamp = $this->get_content_publish_timestamp( $content->ID, $student->enroll_date, $drip_type == 'unlock_by_date' );

					if ( ! $unlocK_timestamp || $unlocK_timestamp > time() ) {
						// Check if publish time passed
						continue;
					}

					if ( ! $this->is_mail_sent( $student->student_id, $content->ID, $unlocK_timestamp ) ) {

						$arg = array(
							'student'  => $student,
							'lqa'      => $content,
							'course'   => $course,
							'lqa_type' => ucfirst( $event ),
						);

						do_action( 'tutor-pro/content-drip/new_' . $event . '_published', $arg );
					}
				}
			}
		}

		foreach ( $this->sent_mail_log as $user_id => $log ) {
			update_user_meta( $user_id, $this->mail_log_meta_key, $log );
		}
	}

	/**
	 * register meta box on single lesson screen
	 *
	 * @since 1.8.9
	 */
	public function register_content_drip_meta_box() {
		tutor_meta_box_wrapper(
			'tutor-content-drip-single-lesson',
			__( 'Content Drip Settings', 'tutor-pro' ),
			array( $this, 'content_drip_lesson_metabox' ),
			tutor()->lesson_post_type,
			'advanced',
			'default',
			'tutor-admin-post-meta'
		);
	}
}
