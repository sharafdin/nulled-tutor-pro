<?php
/**
 * Email Notification
 *
 * @package TutorPro
 * @subpackage Addons\TutorEmail
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR_EMAIL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use Tutor\Models\CourseModel;
use TUTOR_CERT\Certificate;
use TUTOR\User;

/**
 * Class EmailNotification
 *
 * @since 1.0.0
 */
class EmailNotification {

	const INACTIVE_REMINDED_META = 'tutor_inactive_reminded';
	const TO_STUDENTS            = 'email_to_students';
	const TO_TEACHERS            = 'email_to_teachers';
	const TO_ADMIN               = 'email_to_admin';

	/**
	 * Queue table
	 *
	 * @var string
	 */
	private $queue_table;

	/**
	 * Email logo
	 *
	 * @var string
	 */
	public $email_logo;

	/**
	 * Email options
	 *
	 * @var mixed
	 */
	public $email_options;

	/**
	 * Default mail data
	 *
	 * @var mixed
	 */
	public $default_mail_data;

	/**
	 * Register hooks
	 *
	 * @since 2.5.0 $register_hooks param added to reuse the class methods.
	 *
	 * @param bool $register_hooks register hooks or not.
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		global $wpdb;
		$this->queue_table = $wpdb->tutor_email_queue;

		if ( ! $register_hooks ) {
			return;
		}

		add_action( 'tutor_quiz/attempt_ended', array( $this, 'quiz_finished_send_email_to_student' ), 10, 1 );
		add_action( 'tutor_finish_quiz_attempt', array( $this, 'quiz_finished_send_email_to_student' ), 10, 1 );

		/**
		 * Lesson, quiz & assignment mail handler
		 *
		 * @since v2.0.4
		 */
		add_action( 'tutor/lesson/created', array( $this, 'tutor_create_or_update_lesson' ), 10, 1 );
		add_action( 'tutor_assignment_created', array( $this, 'tutor_create_or_update_lesson' ), 10, 1 );
		add_action( 'tutor_initial_quiz_created', array( $this, 'tutor_create_or_update_lesson' ), 10, 1 );

		add_action( 'tutor_quiz/attempt_ended', array( $this, 'quiz_finished_send_email_to_instructor' ), 10, 1 );
		add_action( 'tutor_finish_quiz_attempt', array( $this, 'quiz_finished_send_email_to_instructor' ), 10, 1 );

		add_action( 'tutor_course_complete_after', array( $this, 'course_complete_email_to_student' ), 10, 1 );
		add_action( 'tutor_course_complete_after', array( $this, 'course_complete_email_to_teacher' ), 10, 1 );

		add_action( 'tutor_after_enrolled', array( $this, 'course_enroll_email_to_teacher' ), 10, 3 );
		add_action( 'tutor_after_enrolled', array( $this, 'course_enroll_email_to_student' ), 10, 3 );

		add_action( 'tutor_after_student_signup', array( $this, 'welcome_email_to_student' ), 10, 3 );
		add_action( 'tutor_reply_lesson_comment_thread', array( $this, 'lesson_comment_to_student' ), 10, 3 );
		add_action( 'tutor_new_comment_added', array( $this, 'lesson_comment_to_instructor' ), 10, 3 );
		add_action( 'tutor_after_add_question', array( $this, 'tutor_after_add_question' ), 10, 2 );
		add_action( 'tutor_lesson_completed_email_after', array( $this, 'tutor_lesson_completed_email_after' ), 10, 1 );

		add_action( 'tutor_add_new_instructor_after', array( $this, 'tutor_new_instructor_signup' ), 10, 2 );
		// Adding hook for instructor register.
		add_action( 'tutor_new_instructor_after', array( $this, 'tutor_new_instructor_signup' ), 10, 2 );

		add_action( 'tutor_after_student_signup', array( $this, 'tutor_new_student_signup' ), 10, 2 );
		add_action( 'draft_to_pending', array( $this, 'tutor_course_pending' ), 10, 3 );
		add_action( 'auto-draft_to_pending', array( $this, 'tutor_course_pending' ), 10, 3 );
		add_action( 'draft_to_publish', array( $this, 'tutor_course_published' ), 10, 3 );
		add_action( 'auto-draft_to_publish', array( $this, 'tutor_course_published' ), 10, 3 );
		add_action( 'pending_to_publish', array( $this, 'tutor_course_published' ), 10, 3 );
		add_action( 'save_post_' . tutor()->course_post_type, array( $this, 'tutor_course_updated' ), 10, 3 );
		add_action( 'wp_ajax_save_email_template', array( $this, 'save_email_template' ) );
		add_action( 'wp_ajax_send_test_email_ajax', array( $this, 'send_test_email_ajax' ) );

		add_action( 'wp', array( $this, 'inactive_student_email_to_student' ) );
		add_action( 'wp_login', array( $this, 'reset_inactive_reminded_meta' ), 10, 2 );
		/**
		 * Send mail to instructor if their course accepted or rejected
		 *
		 * @since 1.9.8
		 */
		add_action( 'save_post_' . tutor()->course_post_type, array( $this, 'tutor_course_update_notification' ), 20, 3 );
		add_action( 'tutor_assignment/after/submitted', array( $this, 'tutor_assignment_after_submitted' ), 10, 3 );
		add_action( 'tutor_assignment/evaluate/after', array( $this, 'tutor_after_assignment_evaluate' ), 10, 3 );

		add_action( 'tutor_enrollment/after/delete', array( $this, 'tutor_student_remove_from_course' ), 10, 3 );
		add_action( 'tutor_enrollment/after/cancel', array( $this, 'tutor_student_remove_from_course' ), 10, 3 );
		add_action( 'tutor_enrollment/after/expired', array( $this, 'tutor_enrollment_after_expired' ), 10, 3 ); // @since 1.8.1

		add_action( 'tutor_announcements/after/save', array( $this, 'tutor_announcements_notify_students' ), 10, 3 );

		add_action( 'tutor_after_asked_question', array( $this, 'tutor_after_asked_question' ), 10, 1 );
		add_action( 'tutor_quiz/attempt/submitted/feedback', array( $this, 'feedback_submitted_for_quiz_attempt' ), 10, 3 );
		add_action( 'tutor_course_complete_after', array( $this, 'tutor_course_complete_after' ), 10, 3 );

		/**
		 * Added.
		 *
		 * @since 1.7.4
		 */
		add_action( 'tutor_after_approved_instructor', array( $this, 'instructor_application_approved' ), 10 );
		add_action( 'tutor_after_rejected_instructor', array( $this, 'instructor_application_rejected' ), 10 );
		add_action( 'tutor_after_approved_withdraw', array( $this, 'withdrawal_request_approved' ), 10 );
		add_action( 'tutor_after_rejected_withdraw', array( $this, 'withdrawal_request_rejected' ), 10 );
		add_action( 'tutor_insert_withdraw_after', array( $this, 'withdrawal_request_placed' ), 10 );

		add_action( 'tutor-pro/content-drip/new_lesson_published', array( $this, 'new_lqa_published' ), 10 );
		add_action( 'tutor-pro/content-drip/new_quiz_published', array( $this, 'new_lqa_published' ), 10 );
		add_action( 'tutor-pro/content-drip/new_assignment_published', array( $this, 'new_lqa_published' ), 10 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_email_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_js_translation' ), 100 );

		// Assign email variables.
		add_action( 'init', array( $this, 'save_recipient_data' ) );

		$this->email_logo        = esc_url( TUTOR_EMAIL()->url . 'assets/images/tutor-logo.png' );
		$this->email_options     = get_option( 'email_template_data' );
		$this->default_mail_data = ( new EmailData() )->get_recipients();
	}

	/**
	 * Get trigger saved data with fallback default data support.
	 *
	 * @since 2.5.0
	 *
	 * @param string $to_key to key like email_to_students, email_to_teachers, email_to_admin.
	 * @param string $trigger_key trigger name.
	 *
	 * @return array
	 */
	public function get_option_data( $to_key, $trigger_key ) {
		return isset( $this->email_options[ $to_key ][ $trigger_key ] )
				? $this->email_options[ $to_key ][ $trigger_key ]
				: $this->default_mail_data[ $to_key ][ $trigger_key ];
	}

	/**
	 * Send email after course content created or updated
	 * Supported contents are: lesson, assignment & quiz
	 *
	 * @param int $course_content_id  could be lesson, assignment or quiz id.
	 *
	 * @return void
	 */
	public function tutor_create_or_update_lesson( int $course_content_id ): void {
		$is_enabled_lesson_mail     = (bool) tutor_utils()->get_option( 'email_to_students.new_lesson_published' );
		$is_enabled_quiz_mail       = (bool) tutor_utils()->get_option( 'email_to_students.new_quiz_published' );
		$is_enabled_assignment_mail = (bool) tutor_utils()->get_option( 'email_to_students.new_assignment_published' );

		if ( ! $is_enabled_lesson_mail && ! $is_enabled_quiz_mail && ! $is_enabled_assignment_mail ) {
			return;
		}

		$content_type  = get_post_type( $course_content_id );
		$template_name = '';
		$option_data   = '';
		$email_heading = '';
		$subject       = '';
		$footer_text   = '';
		$message       = '';

		if ( 'lesson' === $content_type ) {
			$template_name = 'to_student_new_lesson_published';
			$option_data   = $this->email_options['email_to_students']['new_lesson_published'];
			$email_heading = $this->default_mail_data['email_to_students']['new_lesson_published']['heading'];
			$subject       = $this->default_mail_data['email_to_students']['new_lesson_published']['subject'];
			$footer_text   = $this->default_mail_data['email_to_students']['new_lesson_published']['footer_text'];
			$message       = $this->default_mail_data['email_to_students']['new_lesson_published']['message'];
		}

		if ( 'tutor_quiz' === $content_type ) {
			$template_name = 'to_student_new_quiz_published';
			$option_data   = $this->email_options['email_to_students']['new_quiz_published'];
			$email_heading = $this->default_mail_data['email_to_students']['new_quiz_published']['heading'];
			$subject       = $this->default_mail_data['email_to_students']['new_quiz_published']['subject'];
			$footer_text   = $this->default_mail_data['email_to_students']['new_quiz_published']['footer_text'];
			$message       = $this->default_mail_data['email_to_students']['new_quiz_published']['message'];
		}

		if ( 'tutor_assignments' === $content_type ) {
			$template_name = 'to_student_new_assignment_published';
			$option_data   = $this->email_options['email_to_students']['new_assignment_published'];
			$email_heading = $this->default_mail_data['email_to_students']['new_assignment_published']['heading'];
			$subject       = $this->default_mail_data['email_to_students']['new_assignment_published']['subject'];
			$footer_text   = $this->default_mail_data['email_to_students']['new_assignment_published']['footer_text'];
			$message       = $this->default_mail_data['email_to_students']['new_assignment_published']['message'];
		}

		if ( isset( $option_data['heading'] ) && '' != $option_data['heading'] ) {
			$email_heading = $option_data['heading'];
		}

		if ( isset( $option_data['subject'] ) && '' != $option_data['subject'] ) {
			$subject = $option_data['subject'];
		}

		if ( isset( $option_data['footer_text'] ) && '' != $option_data['footer_text'] ) {
			$footer_text = $option_data['footer_text'];
		}

		$topic_id  = (int) wp_get_post_parent_id( $course_content_id );
		$course_id = (int) wp_get_post_parent_id( $topic_id );
		if ( ! $course_id ) {
			return;
		}

		$students = tutor_utils()->get_students_data_by_course_id( $course_id, 'ID', true );
		if ( $is_enabled_lesson_mail && 'lesson' === $content_type ) {
			$this->course_content_mail_to_student( $students, $course_id, $course_content_id, $subject, $email_heading, $message, $footer_text, $template_name, $option_data );
		}
		if ( $is_enabled_quiz_mail && 'tutor_quiz' === $content_type ) {
			$this->course_content_mail_to_student( $students, $course_id, $course_content_id, $subject, $email_heading, $message, $footer_text, $template_name, $option_data );
		}
		if ( $is_enabled_assignment_mail && 'tutor_assignments' === $content_type ) {
			$this->course_content_mail_to_student( $students, $course_id, $course_content_id, $subject, $email_heading, $message, $footer_text, $template_name, $option_data );
		}
		return;
	}

	/**
	 * Course content mail trigger method
	 * send mail for new created or updated lesson | quiz | assignment
	 *
	 * @since v2.0.4
	 *
	 * @param array  $students students.
	 * @param int    $course_id course id.
	 * @param int    $course_content_id course content id.
	 * @param string $subject subject.
	 * @param string $email_heading email heading.
	 * @param string $message message.
	 * @param string $footer_text footer text.
	 * @param string $template_name template name.
	 * @param array  $option_data option data.
	 *
	 * @return void
	 */
	public function course_content_mail_to_student( $students, $course_id, $course_content_id, $subject, $email_heading, $message, $footer_text, $template_name, $option_data ): void {

		$email_heading = str_replace( '{course_name}', get_the_title( $course_id ), $email_heading );
		$subject       = str_replace( '{course_name}', get_the_title( $course_id ), $subject );
		if ( is_array( $students ) && count( $students ) ) {
			foreach ( $students as $key => $student ) {

				$student_name = tutor_utils()->get_user_name( get_userdata( $student->ID ) );
				$site_url     = get_bloginfo( 'url' );
				$site_name    = get_bloginfo( 'name' );
				$header       = 'Content-Type: ' . $this->get_content_type() . "\r\n";

				$replacable['{dashboard_url}'] = tutor_utils()->tutor_dashboard_url();

				$replacable['{course_url}']           = get_the_permalink( $course_id );
				$replacable['{testing_email_notice}'] = '';
				$replacable['{user_name}']            = $student_name;
				$replacable['{course_name}']          = get_the_title( $course_id );
				$replacable['{lesson_title}']         = get_the_title( $course_content_id );
				$replacable['{quiz_title}']           = get_the_title( $course_content_id );
				$replacable['{assignment_title}']     = get_the_title( $course_content_id );

				$replacable['{site_url}']      = $site_url;
				$replacable['{site_name}']     = $site_name;
				$replacable['{logo}']          = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
				$replacable['{email_heading}'] = $this->get_replaced_text( $email_heading, array_keys( $replacable ), array_values( $replacable ) );
				$replacable['{before_button}'] = $this->get_replaced_text( $option_data['before_button'], array_keys( $replacable ), array_values( $replacable ) );
				$replacable['{footer_text}']   = $footer_text;
				$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
				ob_start();
				$this->tutor_load_email_template( $template_name, $replacable );
				$email_tpl = apply_filters( 'tutor_email_course_content', ob_get_clean(), $course_content_id );
				$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

				$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
				/**
				 * Note: Force queue set true, as number of students
				 * could be a lot more than expected.
				 */
				$this->send( $student->user_email, $subject, $message, $header, array(), true );
			}
		}
		return;
	}

	/**
	 * Ready the e-mail message
	 * Used unslash and trim (") from front and end of the message
	 *
	 * @param string $message message.
	 * @return string
	 */
	public function prepare_message( $message ) {
		return wp_unslash( json_decode( $message ) );
	}

	/**
	 * Save if email template data not found.
	 *
	 * @return void
	 */
	public function save_recipient_data() {
		$option_data    = get_option( 'email_template_data' );
		$recipient_data = ( new EmailData() )->get_recipients();
		if ( isset( $option_data ) && empty( $option_data ) ) {
			update_option( 'email_template_data', $recipient_data );
		}
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_email_scripts() {
		if ( 'tutor_settings' === Input::get( 'page' ) ) {
			wp_enqueue_style( 'tutor-pro-email-styles', tutor_pro()->url . 'addons/tutor-email/assets/css/email-manage.css', array(), true, null );
		}

		if ( 'email_notification' === Input::get( 'tab_page' ) && Input::has( 'edit' ) ) {
			wp_enqueue_script( 'tutor-pro-email-template', tutor_pro()->url . 'addons/tutor-email/assets/js/email-template.js', array( 'jquery' ), TUTOR_PRO_VERSION, true );
		}
	}

	/**
	 * JS translation load
	 *
	 * @since 2.6.0
	 */
	public function load_js_translation() {
		wp_set_script_translations( 'tutor-pro-email-template', 'tutor-pro', tutor_pro()->languages );
	}

	/**
	 * Get email current template data.
	 *
	 * @param mixed $localize_data localized data.
	 *
	 * @return array
	 */
	public function email_current_template_data( $localize_data ) {
		$email_data['get_email_data'] = ( new EmailData() )->get_recipients();
		return $email_data;
	}

	/**
	 * Save e-mail template
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_email_template() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$to                  = Input::post( 'to' );
		$key                 = Input::post( 'key' );
		$subject             = Input::post( 'email-subject' );
		$heading             = Input::post( 'email-heading' );
		$email_footer_text   = Input::post( 'email-footer-text', null, Input::TYPE_TEXTAREA );
		$email_block_heading = Input::post( 'email-block-heading', null, Input::TYPE_TEXTAREA );
		$email_block_content = Input::post( 'email-block-content', null, Input::TYPE_TEXTAREA );
		$email_before_button = Input::post( 'email-before-button' );
		$inactive_days       = Input::post( 'inactive-days', 0, Input::TYPE_INT );

		$tutor_email_options = array();
		$tutor_email_options = get_option( 'email_template_data' );
		$tutor_options       = get_option( 'tutor_option' );
		$message             = json_encode( Input::post( 'email-additional-message', '', Input::TYPE_KSES_POST ) );

		$email_option_data = ! empty( $tutor_email_options ) ? $tutor_email_options : array();

		$tutor_options[ $to ][ $key ] = $_POST['tutor_option'][ $to ][ $key ]; //phpcs:ignore

		$email_request[ $to ][ $key ] = array(
			'subject'       => $subject,
			'heading'       => $heading,
			'message'       => $message,
			'footer_text'   => $email_footer_text,
			'block_heading' => $email_block_heading,
			'block_content' => $email_block_content,
			'before_button' => wp_kses_post( wp_unslash( $email_before_button ) ),
			'inactive_days' => $inactive_days,

		);

		if ( ! empty( $email_option_data ) ) {

			foreach ( $email_option_data as $key => $email_data ) {
				if ( isset( $email_request[ $key ] ) ) {
					$email_output[ $key ] = array_merge( $email_option_data[ $key ], $email_request[ $key ] );
				} else {
					$email_output[ $key ] = $email_option_data[ $key ];
				}
			}

			$tutor_email_options = ( ! array_key_exists( $to, $email_output ) ) ? array_merge( $email_output, $email_request ) : $email_output;

		} else {
			$tutor_email_options = array_merge( $email_option_data, $email_request );
		}

		update_option( 'email_template_data', $tutor_email_options );
		update_option( 'tutor_option', $tutor_options );

		wp_send_json_success( $message );
	}

	/**
	 * Load email template.
	 *
	 * @param string  $template template.
	 * @param boolean $pro is pro.
	 * @param array   $extra extra data.
	 *
	 * @return void
	 */
	public function tutor_load_email_template( $template, $pro = true, $extra = array() ) {
		extract( $extra ); //phpcs:ignore
		include tutor_get_template( 'email.' . $template, $pro );
	}

	/**
	 * Load email template preview.
	 *
	 * @param string  $template template.
	 * @param boolean $pro is pro.
	 *
	 * @return void
	 */
	public static function tutor_load_email_preview( $template, $pro = true ) {
		include tutor_get_template( 'email.' . $template, $pro );
	}

	/**
	 * Load email template preview with an iFrame.
	 *
	 * @since 2.5.0 load with iframe.
	 *
	 * @param string $template template.
	 *
	 * @return void
	 */
	public static function load_iframe_preview( $template ) {
		$url = site_url() . '?page=tutor-email-preview&template=' . $template;
		echo '<iframe src="' . esc_url( $url ) . '" frameborder="0" width="100%" height="800"></iframe>';
	}

	/**
	 * Send E-Mail Notification for Tutor Event.
	 *
	 * @param string $to to address.
	 * @param string $subject email subject.
	 * @param string $message message.
	 * @param mixed  $headers headers.
	 * @param array  $attachments attachments.
	 * @param bool   $force_enqueue force enqueue.
	 * @param int    $batch batch number, default false.
	 *
	 * @return void
	 */
	public function send( $to, $subject, $message, $headers, $attachments = array(), $force_enqueue = false, $batch = false ) {
		$message = apply_filters( 'tutor_mail_content', $message );
		$this->enqueue_email( $to, $subject, $message, $headers, $attachments, $force_enqueue, $batch );
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_name() {
		$email_from_name = tutor_utils()->get_option( 'email_from_name' );
		$from_name       = apply_filters( 'tutor_email_from_name', $email_from_name );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_address() {
		$email_from_address = tutor_utils()->get_option( 'email_from_address' );
		$from_address       = apply_filters( 'tutor_email_from_address', $email_from_address );
		return sanitize_email( $from_address );
	}

	/**
	 * Get content type
	 *
	 * @return string
	 */
	public function get_content_type() {
		return apply_filters( 'tutor_email_content_type', 'text/html' );
	}

	/**
	 * Get message.
	 *
	 * @param string $message message.
	 * @param array  $search search.
	 * @param array  $replace replace.
	 *
	 * @return string
	 */
	public function get_message( $message = '', $search = array(), $replace = array() ) {
		$email_footer_text = tutor_utils()->get_option( 'email_footer_text' );

		$placeholders = array(
			'{site_name}'    => get_bloginfo( 'name' ),
			'{site_url}'     => site_url(),
			'{current_year}' => gmdate( 'Y' ),
		);

		$email_footer_text = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_footer_text );
		$message           = str_replace( $search, $replace, $message );
		if ( $email_footer_text ) {
			$message .= '<div class="tutor-email-footer-content">' . wp_unslash( json_decode( $email_footer_text ) ) . '</div>';
		}
		return $message;
	}

	/**
	 * Function to replace and return
	 *
	 * @since 2.6.1
	 *
	 * Conditionally replacing message to avoid deprecation error what
	 * was added on the PHP 8 version
	 *
	 * @param  mixed $message message.
	 * @param  mixed $search search.
	 * @param  mixed $replace replace.
	 *
	 * @return string
	 */
	public function get_replaced_text( $message = '', $search = array(), $replace = array() ) {
		return $message ? str_replace( $search, $replace, $message ) : $message;
	}

	/**
	 * Generate email address.
	 *
	 * @param string $string string email address.
	 *
	 * @return string
	 */
	public function _generate_email( $string ) {
		$username = strtolower( str_replace( array( ' ', '_' ), '', $string ) );
		return esc_attr( $username . '@' . parse_url( home_url() )['host'] );
	}

	/**
	 * Generate username.
	 *
	 * @param string $string username string.
	 *
	 * @return string
	 */
	public function _generate_username( $string ) {
		return strtolower( str_replace( array( ' ', '.', '_', '-' ), '', $string ) );
	}

	/**
	 * Sent test mail
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function send_test_email_ajax() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$header       = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$user_id      = get_current_user_id();
		$site_url     = get_bloginfo( 'url' );
		$current_user = get_userdata( $user_id );

		$testing_email = array( $current_user->user_email );
		if ( Input::has( 'testing_email' ) ) {
			$testing_email = explode( ',', Input::post( 'testing_email' ) );
			$testing_email = array_map( 'sanitize_email', $testing_email );
			$testing_email = array_filter(
				$testing_email,
				function( $email ) {
					return ! empty( $email );
				}
			);
		}

		$notice_icon                          = TUTOR_EMAIL()->url . 'assets/images/warning.png';
		$replacable['{testing_email_notice}'] = '<div class="tutor-email-warning"><img src="' . $notice_icon . '" alt="notice"><span><span class="no-res">This is a</span> test mail</span></div>';

		$test_type     = Input::post( 'test_type' );
		$email_subject = '';
		if ( 'trigger_template' === $test_type ) {
			$email_data                    = get_option( 'email_template_data' );
			$recipient_data                = $email_data[ get_request( 'email_to' ) ][ get_request( 'email_key' ) ];
			$tempData       = $email_data[ get_request( 'email_to' ) ][ get_request( 'email_key' ) ]; //phpcs:ignore
			$email_subject                 = wp_kses_post( $recipient_data['subject'] );
			$replacable['{email_heading}'] = isset( $recipient_data['heading'] ) ? $recipient_data['heading'] : '';
			$replacable['{email_message}'] = isset( $recipient_data['message'] ) ? $this->prepare_message( $recipient_data['message'] ) : '';
			$replacable['{footer_text}']   = isset( $recipient_data['footer_text'] ) ? $recipient_data['footer_text'] : '';
		}

		if ( 'email_settings' === $test_type ) {
			$email_subject               = __( 'Email Default Configuration Subject', 'tutor-pro' );
			$replacable['{footer_text}'] = __( 'Example of a no-reply or instructional footnote', 'tutor-pro' );
		}

		if ( 'mailer' === $test_type ) {
			$mailer_data = get_option( ManualEmail::OPTION_KEY );
			if ( $mailer_data ) {
				$email_subject                 = $mailer_data['email_subject'] ?? '';
				$replacable['{email_heading}'] = $mailer_data['email_heading'] ?? '';
				$replacable['{email_body}']    = $mailer_data['email_body'] ?? '';
				$replacable['{footer_text}']   = $mailer_data['email_footer'] ?? '';
			}
		}

		$student_name           = __( 'Sample Student Name', 'tutor-pro' );
		$instructor_name        = __( 'Sample Instructor Name', 'tutor-pro' );
		$instructor_description = __( 'Sample Instructor Description', 'tutor-pro' );
		$email_template         = get_request( 'email_template' );
		$tutor_url              = 'https://www.themeum.com/product/tutor-lms';
		$approved_url           = sprintf( admin_url( 'admin.php?page=%s&action=%s' ), 'tutor_withdraw_requests', 'approved' );
		$rejected_url           = sprintf( admin_url( 'admin.php?page=%s&action=%s' ), 'tutor_withdraw_requests', 'rejected' );
		$course_title           = __( 'Sample Course Title', 'tutor-pro' );
		$lesson_title           = __( 'Sample Lesson Title', 'tutor-pro' );
		$quiz_title             = __( 'Sample Quiz Title?', 'tutor-pro' );
		$assignment_name        = __( 'Sample Assignment Name', 'tutor-pro' );
		$total_amount           = 100;
		$earned_amount          = 80;
		$instructor_avatar      = get_avatar_url( wp_get_current_user()->ID );
		$lorem_date             = the_time( 'l, F jS, Y' );
		$announcement_title     = 'Sample announcement title';
		$announcement_content   = 'Sample announcement content.';
		$assignment_content     = 'Sample assignment content.';
		$lorem_content_sm       = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam et fermentum dui. Ut orci quam, ornare sed lorem sed, hendrerit auctor dolor?';

		$replacable['{current_year}']           = gmdate( 'Y' );
		$replacable['{earned_marks}']           = 8;
		$replacable['{total_marks}']            = 10;
		$replacable['{attempt_result}']         = '<span class="tutor-badge-label label-success">Pass</span>';
		$replacable['{student_name}']           = $student_name;
		$replacable['{student_username}']       = $this->_generate_username( $student_name );
		$replacable['{user_name}']              = tutor_utils()->get_user_name( $current_user );
		$replacable['{admin_name}']             = $current_user->display_name;
		$replacable['{admin_user}']             = $current_user->display_name;
		$replacable['{student_email}']          = $current_user->user_email;
		$replacable['{site_url}']               = $site_url;
		$replacable['{tutor_url}']              = $tutor_url;
		$replacable['{dashboard_url}']          = tutor_utils()->get_tutor_dashboard_page_permalink();
		$replacable['{site_name}']              = get_bloginfo( 'name' );
		$replacable['{course_url}']             = $site_url;
		$replacable['{profile_url}']            = $site_url;
		$replacable['{student_url}']            = get_author_posts_url( $user_id );
		$replacable['{course_title}']           = $course_title;
		$replacable['{course_name}']            = $course_title;
		$replacable['{total_amount}']           = $total_amount;
		$replacable['{earned_amount}']          = $earned_amount;
		$replacable['{lesson_title}']           = $lesson_title;
		$replacable['{lesson_name}']            = $lesson_title;
		$replacable['{quiz_name}']              = $quiz_title;
		$replacable['{quiz_title}']             = $quiz_title;
		$replacable['{question}']               = $lorem_content_sm;
		$replacable['{enroll_time}']            = isset( $recipient_data['enroll_time'] ) ? $recipient_data['enroll_time'] : '';
		$replacable['{instructor_username}']    = $instructor_name;
		$replacable['{instructor_avatar}']      = $instructor_avatar;
		$replacable['{instructor_description}'] = $instructor_description;
		$replacable['{logo}']                   = TUTOR_EMAIL()->url . 'assets/images/tutor-logo.png';
		$replacable['{answer_by}']              = $student_name;
		$replacable['{answer_date}']            = $lorem_date;
		$replacable['{username}']               = $current_user->user_nicename;
		$replacable['{instructor_email}']       = $this->_generate_email( $instructor_name );
		$replacable['{student_email}']          = $this->_generate_email( $student_name );
		$replacable['{instructor_name}']        = $instructor_name;
		$replacable['{block_heading}']          = isset( $recipient_data['block_heading'] ) ? $recipient_data['block_heading'] : '';
		$replacable['{block_content}']          = isset( $recipient_data['block_content'] ) ? $recipient_data['block_content'] : '';
		$replacable['{withdraw_amount}']        = isset( $recipient_data['withdraw_amount'] ) ? $recipient_data['withdraw_amount'] : '';
		$replacable['{assignment_name}']        = $assignment_name;
		$replacable['{assignment_score}']       = isset( $recipient_data['assignment_score'] ) ? $recipient_data['assignment_score'] : '';
		$replacable['{assignment_max_mark}']    = isset( $recipient_data['assignment_max_mark'] ) ? $recipient_data['assignment_max_mark'] : '';
		$replacable['{approved_url}']           = $approved_url;
		$replacable['{rejected_url}']           = $rejected_url;
		$replacable['{announcement_title}']     = $announcement_title;
		$replacable['{announcement_content}']   = $announcement_content;
		$replacable['{announcement_date}']      = $lorem_date;
		$replacable['{author_fullname}']        = $student_name;
		$replacable['{assignment_comment}']     = $assignment_content;
		$replacable['{attempt_url}']            = 'javascript:void(0)';
		$replacable['{inactive_days}']          = 10;
		$replacable['{before_button}']          = $this->get_replaced_text( $recipient_data['before_button'] ?? '', array_keys( $replacable ), array_values( $replacable ) );

		// Keep this below of all replaceable string to generate dynamic subject.
		$subject = __( '[Test]', 'tutor-pro' ) . ' ' . $this->get_replaced_text( $email_subject, array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( $email_template );
		$email_tpl = apply_filters( 'tutor_email_tpl/testing_emails', ob_get_clean() );
		$message   = $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) );

		foreach ( $testing_email as $email ) {
			$this->send( $email, $subject, $message, $header );
		}

		wp_send_json_success();
	}


	/**
	 * Function to send course_complete_email_to_student
	 *
	 * @param  int $course_id is related to course .
	 * @return string
	 */
	public function course_complete_email_to_student( $course_id ) {
		$course_completed_to_student = tutor_utils()->get_option( 'email_to_students.completed_course' );

		if ( ! $course_completed_to_student ) {
			return;
		}

		$user_id                = get_current_user_id();
		$course                 = get_post( $course_id );
		$student                = get_userdata( $user_id );
		$teacher                = get_userdata( $course->post_author );
		$instructor_avatar      = get_avatar_url( $teacher->ID );
		$instructor_email       = $teacher->user_email;
		$instructor_description = $teacher->user_description;
		$completion_time        = tutor_utils()->is_completed_course( $course_id );
		$completion_time        = $completion_time ? $completion_time : tutor_time();
		$completion_time_format = date_i18n( get_option( 'date_format' ), $completion_time ) . ' ' . date_i18n( get_option( 'time_format' ), $completion_time );
		$site_url               = get_bloginfo( 'url' );
		$site_name              = get_bloginfo( 'name' );
		$option_data            = $this->get_option_data( self::TO_STUDENTS, 'completed_course' );
		$header                 = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header                 = apply_filters( 'student_course_completed_email_header', $header, $course_id );

		$certificate_url = '';
		if ( tutils()->is_addon_enabled( TUTOR_CERT()->basename ) ) {
			$certificate_url = ( new Certificate( true ) )->get_certificate( $course_id );
		}

		$replacable['{testing_email_notice}']   = '';
		$replacable['{instructor_username}']    = $teacher->display_name;
		$replacable['{user_name}']              = tutor_utils()->get_user_name( $student );
		$replacable['{course_name}']            = $course->post_title;
		$replacable['{completion_time}']        = $completion_time_format;
		$replacable['{course_url}']             = get_the_permalink( $course_id );
		$replacable['{site_url}']               = $site_url;
		$replacable['{site_name}']              = $site_name;
		$replacable['{instructor_avatar}']      = $instructor_avatar;
		$replacable['{instructor_email}']       = $instructor_email;
		$replacable['{instructor_description}'] = $instructor_description;
		$replacable['{logo}']                   = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{before_button}']          = $this->get_replaced_text( $option_data['before_button'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']            = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_heading}']          = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']          = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                                = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template(
			'to_student_course_completed',
			true,
			array(
				'course_id'       => $course_id,
				'certificate_url' => $certificate_url,
			)
		);
		$email_tpl = apply_filters( 'tutor_email_tpl/course_completed', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $student->user_email, $subject, $message, $header );

	}

	/**
	 * Course complete email to teacher.
	 *
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function course_complete_email_to_teacher( $course_id ) {
		$course_completed_to_teacher = tutor_utils()->get_option( 'email_to_teachers.a_student_completed_course' );

		if ( ! $course_completed_to_teacher ) {
			return;
		}

		$user_id                = get_current_user_id();
		$student                = get_userdata( $user_id );
		$course                 = get_post( $course_id );
		$teacher                = get_userdata( $course->post_author );
		$completion_time        = tutor_utils()->is_completed_course( $course_id );
		$completion_time        = $completion_time ? $completion_time : tutor_time();
		$completion_time_format = date_i18n( get_option( 'date_format' ), $completion_time ) . ' ' . date_i18n( get_option( 'time_format' ), $completion_time );
		$site_url               = get_bloginfo( 'url' );
		$site_name              = get_bloginfo( 'name' );
		$option_data            = $this->get_option_data( self::TO_TEACHERS, 'a_student_completed_course' );
		$header                 = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header                 = apply_filters( 'student_course_completed_email_header', $header, $course_id );
		$dashboard_url          = tutor_utils()->tutor_dashboard_url();
		$student_report_url     = $dashboard_url . 'analytics/student-details?student_id=' . $user_id;

		$replacable['{testing_email_notice}'] = '';
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $teacher );
		$replacable['{student_name}']         = $student->display_name;
		$replacable['{student_username}']     = $student->display_name;
		$replacable['{student_email}']        = $student->user_email;
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{completion_time}']      = $completion_time_format;
		$replacable['{course_url}']           = get_the_permalink( $course_id );
		$replacable['{site_url}']             = $site_url;
		$replacable['{student_report_url}']   = $student_report_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{before_button}']        = $this->get_replaced_text( $option_data['before_button'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_course_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/course_completed', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $teacher->user_email, $subject, $message, $header );

	}


	/**
	 * Quiz finished email to student.
	 *
	 * @param int $attempt_id attempt id.
	 *
	 * @return void.
	 */
	public function quiz_finished_send_email_to_student( $attempt_id ) {
		$quiz_completed = tutor_utils()->get_option( 'email_to_students.quiz_completed' );
		if ( ! $quiz_completed ) {
			return;
		}

		$attempt = tutor_utils()->get_attempt( $attempt_id );

		$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
		$passing_grade     = (int) tutor_utils()->get_quiz_option( $attempt->quiz_id, 'passing_grade', 0 );

		if ( 'review_required' === $attempt->attempt_status ) {
			$attempt_result = '<span class="tutor-badge-label label-warning">' . esc_attr( 'Pending' ) . '</span>';
		} else {
			$attempt_result = $earned_percentage >= $passing_grade ?
															'<span class="tutor-badge-label label-success">' . esc_attr( 'Pass' ) . '</span>' :
															'<span class="tutor-badge-label label-danger">' . esc_attr( 'Fail' ) . '</span>';
		}

		$attempt_info           = tutor_utils()->quiz_attempt_info( $attempt_id );
		$submission_time        = tutor_utils()->avalue_dot( 'submission_time', $attempt_info );
		$submission_time        = $submission_time ? $submission_time : tutor_time();
		$quiz_id                = tutor_utils()->avalue_dot( 'comment_post_ID', $attempt );
		$quiz_name              = get_the_title( $quiz_id );
		$course                 = CourseModel::get_course_by_quiz( $quiz_id );
		$course_id              = tutor_utils()->avalue_dot( 'ID', $course );
		$course_title           = get_the_title( $course_id );
		$submission_time_format = date_i18n( get_option( 'date_format' ), $submission_time ) . ' ' . date_i18n( get_option( 'time_format' ), $submission_time );
		$quiz_url               = get_the_permalink( $quiz_id );
		$user                   = get_userdata( tutor_utils()->avalue_dot( 'user_id', $attempt ) );
		$site_url               = get_bloginfo( 'url' );
		$site_name              = get_bloginfo( 'name' );
		$option_data            = $this->get_option_data( self::TO_STUDENTS, 'quiz_completed' );
		$header                 = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header                 = apply_filters( 'student_quiz_completed_email_header', $header, $attempt_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $user );
		$replacable['{total_marks}']          = $attempt->total_marks;
		$replacable['{earned_marks}']         = $attempt->earned_marks;
		$replacable['{attempt_result}']       = $attempt_result;
		$replacable['{attempt_url}']          = tutor_utils()->tutor_dashboard_url() . 'my-quiz-attempts/?view_quiz_attempt_id=' . $attempt_id;

		$replacable['{quiz_name}']       = $quiz_name;
		$replacable['{course_name}']     = $course_title;
		$replacable['{submission_time}'] = $submission_time_format;
		$replacable['{quiz_url}']        = "<a href='{$quiz_url}'>{$quiz_url}</a>";
		$replacable['{site_url}']        = $site_url;
		$replacable['{site_name}']       = $site_name;
		$replacable['{logo}']            = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']   = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']   = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                         = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_quiz_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/quiz_completed', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $user->user_email, $subject, $message, $header );

	}

	/**
	 * Quiz finished email to instructor.
	 *
	 * @param int $attempt_id attempt id.
	 *
	 * @return void
	 */
	public function quiz_finished_send_email_to_instructor( $attempt_id ) {
		$is_enable = tutor_utils()->get_option( 'email_to_teachers.student_submitted_quiz' );
		if ( ! $is_enable ) {
			return;
		}

		$attempt = tutor_utils()->get_attempt( $attempt_id );

		$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
		$passing_grade     = (int) tutor_utils()->get_quiz_option( $attempt->quiz_id, 'passing_grade', 0 );

		if ( 'review_required' === $attempt->attempt_status ) {
			$attempt_result = '<span class="tutor-badge-label label-warning">' . esc_attr( 'Review Required' ) . '</span>';
		} else {
			$attempt_result = $earned_percentage >= $passing_grade ?
															'<span class="tutor-badge-label label-success">' . esc_attr( 'Pass' ) . '</span>' :
															'<span class="tutor-badge-label label-danger">' . esc_attr( 'Fail' ) . '</span>';
		}

		$attempt_info           = tutor_utils()->quiz_attempt_info( $attempt_id );
		$submission_time        = tutor_utils()->avalue_dot( 'submission_time', $attempt_info );
		$submission_time        = $submission_time ? $submission_time : tutor_time();
		$quiz_id                = tutor_utils()->avalue_dot( 'comment_post_ID', $attempt );
		$quiz_name              = get_the_title( $quiz_id );
		$course                 = CourseModel::get_course_by_quiz( $quiz_id );
		$course_id              = tutor_utils()->avalue_dot( 'ID', $course );
		$course_title           = get_the_title( $course_id );
		$submission_time_format = date_i18n( get_option( 'date_format' ), $submission_time ) . ' ' . date_i18n( get_option( 'time_format' ), $submission_time );
		$attempt_url            = tutor_utils()->get_tutor_dashboard_page_permalink( 'quiz-attempts/quiz-reviews/?attempt_id=' . $attempt_id );
		$user                   = get_userdata( tutor_utils()->avalue_dot( 'user_id', $attempt ) );
		$teacher                = get_userdata( $course->post_author );
		$site_url               = get_bloginfo( 'url' );
		$site_name              = get_bloginfo( 'name' );
		$option_data            = $this->get_option_data( self::TO_TEACHERS, 'student_submitted_quiz' );
		$header                 = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header                 = apply_filters( 'student_quiz_completed_to_instructor_email_header', $header, $attempt_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $teacher );
		$replacable['{total_marks}']          = $attempt->total_marks;
		$replacable['{earned_marks}']         = $attempt->earned_marks;
		$replacable['{attempt_result}']       = $attempt_result;
		$replacable['{student_name}']         = $user->display_name;
		$replacable['{quiz_name}']            = $quiz_name;
		$replacable['{course_name}']          = $course_title;
		$replacable['{submission_time}']      = $submission_time_format;
		$replacable['{quiz_review_url}']      = "<a href='{$attempt_url}'>{$attempt_url}</a>";
		$replacable['{site_url}']             = $site_url;
		$replacable['{attempt_url}']          = $site_url . '/wp-admin/admin.php?page=tutor_quiz_attempts&view_quiz_attempt_id=' . $attempt_id;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_quiz_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/quiz_completed/to_instructor', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $teacher->user_email, $subject, $message, $header );

	}

	/**
	 * E-Mail to teacher when success enrol.
	 *
	 * @param int $course_id course id.
	 * @param int $student_id student id.
	 * @param int $enrol_id enrol id.
	 * @param int $status_to status to.
	 *
	 * @return void.
	 */
	public function course_enroll_email_to_teacher( $course_id, $student_id, $enrol_id, $status_to = 'completed' ) {
		$enroll_notification = tutor_utils()->get_option( 'email_to_teachers.a_student_enrolled_in_course' );

		if ( ! $enroll_notification || 'completed' !== $status_to ) {
			return;
		}

		$student            = get_userdata( $student_id );
		$course             = tutor_utils()->get_course_by_enrol_id( $enrol_id );
		$teacher            = get_userdata( $course->post_author );
		$enroll_time        = tutor_time();
		$enroll_time_format = date_i18n( get_option( 'date_format' ), $enroll_time ) . ' ' . date_i18n( get_option( 'time_format' ), $enroll_time );
		$profile_url        = tutor_utils()->profile_url( $student_id, false );
		$amount_data        = tutor_utils()->get_earning_sum( $teacher->ID );

		$total_amount  = $amount_data->balance;
		$earned_amount = $amount_data->instructor_amount;

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_TEACHERS, 'a_student_enrolled_in_course' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'to_instructor_course_enrolled_email_header', $header, $course->ID );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $teacher );
		$replacable['{student_username}']     = $student->display_name;
		$replacable['{student_email}']        = $student->user_email;
		$replacable['{profile_url}']          = $profile_url;
		$replacable['{dashboard_url}']        = tutor_utils()->get_tutor_dashboard_page_permalink();
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{total_amount}']         = $total_amount;
		$replacable['{earned_amount}']        = $earned_amount;
		$replacable['{enroll_time}']          = $enroll_time_format;
		$replacable['{course_url}']           = get_the_permalink( $course->ID );
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_course_enrolled' );
		$email_tpl = apply_filters( 'tutor_email_tpl/to_teacher_course_enrolled', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $teacher->user_email, $subject, $message, $header );

	}

	/**
	 * Welcome e-Mail to student.
	 *
	 * @param int $student_id student id.
	 *
	 * @return void.
	 */
	public function welcome_email_to_student( $student_id ) {
		$welcome_notification = tutor_utils()->get_option( 'email_to_students.welcome_student' );

		if ( ! $welcome_notification ) {
			return;
		}

		$student = get_userdata( $student_id );
		// If student not found return.
		if ( false === $student ) {
			return;
		}

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_STUDENTS, 'welcome_student' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'student_welcome_email_header', $header );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $student );
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{dashboard_url}']        = tutor_utils()->get_tutor_dashboard_page_permalink();
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_welcome' );
		$email_tpl = apply_filters( 'tutor_email_tpl/student_welcome', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $student->user_email, $subject, $message, $header );

	}

	/**
	 * E-Mail to student when success enrol.
	 *
	 * @param int $course_id course id.
	 * @param int $student_id student id.
	 * @param int $enrol_id enrol id.
	 * @param int $status_to status to.
	 *
	 * @return void.
	 */
	public function course_enroll_email_to_student( $course_id, $student_id, $enrol_id, $status_to = 'completed' ) {
		$enroll_notification = tutor_utils()->get_option( 'email_to_students.course_enrolled' );

		if ( ! $enroll_notification || 'completed' !== $status_to ) {
			return;
		}

		$student = get_userdata( $student_id );
		// If student not found return.
		if ( false === $student ) {
			return;
		}

		$course             = tutor_utils()->get_course_by_enrol_id( $enrol_id );
		$enroll_time        = tutor_time();
		$enroll_time_format = date_i18n( get_option( 'date_format' ), $enroll_time ) . ' ' . date_i18n( get_option( 'time_format' ), $enroll_time );
		$course_start_url   = tutor_utils()->get_course_first_lesson( $course_id );
		$site_url           = get_bloginfo( 'url' );
		$site_name          = get_bloginfo( 'name' );
		$option_data        = $this->get_option_data( self::TO_STUDENTS, 'course_enrolled' );
		$header             = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header             = apply_filters( 'student_course_enrolled_email_header', $header, $enrol_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $student );
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{enroll_time}']          = $enroll_time_format;
		$replacable['{course_url}']           = get_the_permalink( $course->ID );
		$replacable['{course_start_url}']     = $course_start_url;
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_course_enrolled' );
		$email_tpl = apply_filters( 'tutor_email_tpl/student_course_enrolled', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $student->user_email, $subject, $message, $header );

	}

	/**
	 * E-Mail to student when not active.
	 *
	 * @return void.
	 */
	public function inactive_student_email_to_student() {

		$inactive_notification = tutor_utils()->get_option( 'email_to_students.inactive_student' );

		if ( ! $inactive_notification ) {
			return;
		}

		$transient_key      = 'tutor_inactive_user_mail_sent';
		$has_transient_data = get_transient( $transient_key );

		if ( false === $has_transient_data ) {

			$site_url    = get_bloginfo( 'url' );
			$site_name   = get_bloginfo( 'name' );
			$option_data = $this->get_option_data( self::TO_STUDENTS, 'inactive_student' );
			$days        = $option_data['inactive_days'];
			$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
			$header      = apply_filters( 'to_inactive_student_email_header', $header );

			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => '_is_tutor_student',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'tutor_last_login',
					'compare' => 'EXISTS',
				),
			);

			// Get users with the specified meta query.
			$users = get_users(
				array(
					'meta_query' => $meta_query,
				)
			);

			foreach ( $users as $user ) {
				$student = get_userdata( $user->ID );
				// If student not found return.
				if ( false === $student ) {
					return;
				}

				$last_login_timestamp = get_user_meta( $user->ID, User::LAST_LOGIN_META, true );
				$reminded_user_meta   = get_user_meta( $user->ID, self::INACTIVE_REMINDED_META, true );

				if ( true == $reminded_user_meta ) {
					return;
				}

				if ( ! empty( $last_login_timestamp ) ) {
					$inactive_seconds = time() - $last_login_timestamp;
					// Convert seconds to days.
					$inactive_days = floor( $inactive_seconds / ( 60 * 60 * 24 ) );

					if ( $inactive_days >= $days ) {

						$replacable['{testing_email_notice}'] = '';
						$replacable['{user_name}']            = tutor_utils()->get_user_name( $student );
						$replacable['{dashboard_url}']        = tutor_utils()->get_tutor_dashboard_page_permalink();
						$replacable['{site_url}']             = $site_url;
						$replacable['{site_name}']            = $site_name;
						$replacable['{inactive_days}']        = $inactive_days;
						$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
						$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
						$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
						$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

						ob_start();
						$this->tutor_load_email_template( 'to_student_inactive_student' );
						$email_tpl = apply_filters( 'tutor_email_tpl/to_inactive_student_email_header', ob_get_clean() );
						$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

						$this->send( $student->user_email, $subject, $message, $header );

						add_user_meta( $user->ID, self::INACTIVE_REMINDED_META, true );
						set_transient( $transient_key, time(), 12 * HOUR_IN_SECONDS );

					}
				}
			}
		}
	}

	/**
	 * Student after reply comment thread.
	 *
	 * @param int   $comment_id comment id.
	 * @param array $comment_data comment data.
	 *
	 * @return void
	 */
	public function lesson_comment_to_student( $comment_id, $comment_data ) {
		$comment_notification = tutor_utils()->get_option( 'email_to_students.lesson_comment_replied' );
		if ( ! $comment_notification ) {
			return;
		}
		$comment_parent     = $comment_data['comment_parent'];
		$comment_lesson_id  = $comment_data['comment_post_ID'];
		$lesson_title       = get_the_title( $comment_lesson_id );
		$comment_details    = get_comment( $comment_parent, OBJECT );
		$user_id            = get_current_user_id();
		$student            = get_userdata( $user_id );
		$course_id          = tutor_utils()->get_course_id_by_lesson( $comment_lesson_id );
		$course             = get_post( $course_id );
		$teacher            = get_userdata( $course->post_author );
		$get_comment        = $comment_data['comment_content'];
		$site_url           = get_bloginfo( 'url' );
		$site_name          = get_bloginfo( 'name' );
		$option_data        = $this->get_option_data( self::TO_STUDENTS, 'lesson_comment_replied' );
		$header             = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header             = apply_filters( 'to_instructor_commented', $header, $course_id );
		$users              = self::get_thread_users( $comment_details, $comment_data, 'comment' );
		$replier            = get_userdata( $comment_details->user_id );
		$replier_name       = isset( $comment_details->user_id ) ? tutor_utils()->display_name( $comment_details->user_id ) : '';
		$replier_email      = $replier->user_email;
		$current_user       = wp_get_current_user();
		$current_user_email = $current_user->user_email;

		// Remove replier from users list.
		if ( is_array( $users ) && count( $users ) ) {
			$users = array_filter(
				$users,
				function( $user ) use ( $current_user_email ) {
					if ( $user->user_email !== $current_user_email ) {
						return $user;
					}
				}
			);
		}

		// Send mail to all users who are on the reply thread.
		foreach ( $users as $user ) {
			$receiver_email = $user->user_email;
			$receiver_name  = tutor_utils()->display_name( $user->ID );

			$replacable['{testing_email_notice}'] = '';
			$replacable['{site_url}']             = $site_url;
			$replacable['{site_name}']            = $site_name;
			$replacable['{user_name}']            = $receiver_name;
			$replacable['{lesson_title}']         = $lesson_title;
			$replacable['{comment_by}']           = $student->display_name;
			$replacable['{course_name}']          = $course->post_title;
			$replacable['{course_url}']           = get_the_permalink( $course_id );
			$replacable['{comment}']              = $get_comment;
			$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
			$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{before_button}']        = $this->get_replaced_text( $option_data['before_button'], array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
			$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );
			ob_start();
			$this->tutor_load_email_template( 'to_student_comment_thread' );
			$email_tpl = apply_filters( 'tutor_email_tpl/to_student_comment_thread', ob_get_clean() );
			$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

			$this->send( $receiver_email, $subject, $message, $header );
		}
	}

	/**
	 * Tutor after add question email.
	 *
	 * @param int $course_id course id.
	 * @param int $comment_id comment id.
	 *
	 * @return void
	 */
	public function tutor_after_add_question( $course_id, $comment_id ) {
		$enroll_notification = tutor_utils()->get_option( 'email_to_teachers.a_student_placed_question' );
		if ( ! $enroll_notification ) {
			return;
		}

		$user_id            = get_current_user_id();
		$student            = get_userdata( $user_id );
		$course             = get_post( $course_id );
		$teacher            = get_userdata( $course->post_author );
		$get_comment        = tutor_utils()->get_qa_question( $comment_id );
		$question           = $get_comment->comment_content;
		$question_title     = substr( $get_comment->comment_content, 0, 40 );
		$enroll_time        = tutor_time();
		$enroll_time_format = date_i18n( get_option( 'date_format' ), $enroll_time ) . ' ' . date_i18n( get_option( 'time_format' ), $enroll_time );
		$site_url           = get_bloginfo( 'url' );
		$site_name          = get_bloginfo( 'name' );
		$user_id            = get_current_user_id();
		$student            = get_userdata( $user_id );
		$course             = get_post( $course_id );
		$teacher            = get_userdata( $course->post_author );
		$option_data        = $this->get_option_data( self::TO_TEACHERS, 'a_student_placed_question' );
		$header             = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header             = apply_filters( 'to_teacher_asked_question_by_student_email_header', $header, $course_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $teacher );
		$replacable['{student_username}']     = $student->display_name;
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{course_url}']           = get_the_permalink( $course_id );
		$replacable['{enroll_time}']          = $enroll_time_format;
		$replacable['{question_title}']       = $question_title;
		$replacable['{question}']             = wpautop( stripslashes( $question ) );
		$replacable['{question_url}']         = tutor_utils()->tutor_dashboard_url() . 'question-answer';
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_asked_question_by_student' );
		$email_tpl = apply_filters( 'tutor_email_tpl/to_teacher_asked_question_by_student', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $teacher->user_email, $subject, $message, $header );

	}

	/**
	 * Lesson complete email to instructor.
	 *
	 * @param int $lesson_id lesson id.
	 *
	 * @return void
	 */
	public function tutor_lesson_completed_email_after( $lesson_id ) {
		$course_completed_to_teacher = tutor_utils()->get_option( 'email_to_teachers.a_student_completed_lesson' );

		if ( ! $course_completed_to_teacher ) {
			return;
		}

		$site_url               = get_bloginfo( 'url' );
		$site_name              = get_bloginfo( 'name' );
		$user_id                = get_current_user_id();
		$student                = get_userdata( $user_id );
		$course_id              = tutor_utils()->get_course_id_by( 'lesson', $lesson_id );
		$lesson                 = get_post( $lesson_id );
		$course                 = get_post( $course_id );
		$teacher                = get_userdata( $course->post_author );
		$completion_time        = tutor_time();
		$completion_time_format = date_i18n( get_option( 'date_format' ), $completion_time ) . ' ' . date_i18n( get_option( 'time_format' ), $completion_time );
		$option_data            = $this->get_option_data( self::TO_TEACHERS, 'a_student_completed_lesson' );
		$header                 = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header                 = apply_filters( 'student_lesson_completed_email_header', $header, $lesson_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $teacher );
		$replacable['{student_name}']         = $student->display_name;
		$replacable['{student_email}']        = $student->user_email;
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{lesson_name}']          = $lesson->post_title;
		$replacable['{completion_time}']      = $completion_time_format;
		$replacable['{lesson_url}']           = get_the_permalink( $lesson_id );
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = isset( $option_data['footer_text'] ) ? $option_data['footer_text'] : '';

		$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                       = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_lesson_completed' );
		$email_tpl = apply_filters( 'tutor_email_tpl/lesson_completed', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $teacher->user_email, $subject, $message, $header );

	}

	/**
	 * After instructor successfully signup.
	 *
	 * @since 1.6.9
	 *
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function tutor_new_instructor_signup( $user_id ) {

		$new_instructor_signup = tutor_utils()->get_option( 'email_to_admin.new_instructor_signup' );

		if ( ! $new_instructor_signup ) {
			return;
		}
		$instructor_review_url = add_query_arg(
			array(
				'page'       => 'tutor-instructors',
				'action'     => 'review',
				'instructor' => $user_id,
			),
			admin_url( 'admin.php' )
		);

		$instructor_id      = tutor_utils()->get_user_id( $user_id );
		$instructor         = get_userdata( $instructor_id );
		$site_url           = get_bloginfo( 'url' );
		$site_name          = get_bloginfo( 'name' );
		$signup_time        = tutor_time();
		$signup_time_format = date_i18n( get_option( 'date_format' ), $signup_time ) . ' ' . date_i18n( get_option( 'time_format' ), $signup_time );
		$admin_users        = get_users( array( 'role__in' => array( 'administrator' ) ) );
		$option_data        = $this->get_option_data( self::TO_ADMIN, 'new_instructor_signup' );
		$header             = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header             = apply_filters( 'instructor_signup_email_header', $header, $instructor_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{instructor_name}']      = $instructor->display_name;
		$replacable['{review_url}']           = $instructor_review_url;
		$replacable['{instructor_email}']     = $instructor->user_email;
		$replacable['{signup_time}']          = $signup_time_format;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );

		$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                       = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_admin_new_instructor_signup' );
		$email_tpl = apply_filters( 'tutor_email_tpl/new_instructor_signup', ob_get_clean() );

		foreach ( $admin_users as $admin_user ) {
			$replacable['{user_name}'] = tutor_utils()->get_user_name( $admin_user );
			$message                   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $admin_user->user_email, $subject, $message, $header );
		}

		$this->instructor_application_received( $instructor );

	}

	/**
	 * Instructor application received email.
	 *
	 * @param object $instructor instructor data.
	 *
	 * @return void
	 */
	private function instructor_application_received( $instructor ) {

		$send_received = tutor_utils()->get_option( 'email_to_teachers.instructor_application_received' );

		if ( ! $send_received ) {
			return;
		}

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_TEACHERS, 'instructor_application_received' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'instructor_application_received_email_header', $header, $instructor->ID );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $instructor );
		$replacable['{instructor_username}']  = $instructor->display_name;
		$replacable['{instructor_email}']     = $instructor->user_email;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_become_application_received' );
		$email_tpl = apply_filters( 'tutor_email_tpl/instructor_application_received', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $instructor->user_email, $subject, $message, $header );
	}


	/**
	 * After student successfully signup
	 *
	 * @since 1.6.9
	 *
	 * @param int $user_id user id.
	 *
	 * @return void
	 */
	public function tutor_new_student_signup( $user_id ) {
		$new_student_signup = tutor_utils()->get_option( 'email_to_admin.new_student_signup' );

		if ( ! $new_student_signup ) {
			return;
		}
		$student_id         = tutor_utils()->get_user_id( $user_id );
		$student            = get_userdata( $student_id );
		$site_url           = get_bloginfo( 'url' );
		$site_name          = get_bloginfo( 'name' );
		$signup_time        = tutor_time();
		$signup_time_format = date_i18n( get_option( 'date_format' ), $signup_time ) . ' ' . date_i18n( get_option( 'time_format' ), $signup_time );
		$admin_users        = get_users( array( 'role__in' => array( 'administrator' ) ) );
		$option_data        = $this->get_option_data( self::TO_ADMIN, 'new_student_signup' );
		$header             = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header             = apply_filters( 'student_signup_email_header', $header, $student_id );
		$profile_url        = tutor_utils()->profile_url( $student_id, false );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{student_name}']         = $student->display_name;
		$replacable['{student_email}']        = $student->user_email;
		$replacable['{signup_time}']          = $signup_time_format;
		$replacable['{profile_url}']          = $profile_url;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );

		$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                       = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();

		$this->tutor_load_email_template( 'to_admin_new_student_signup' );
		$email_tpl = apply_filters( 'tutor_email_tpl/new_student_signup', ob_get_clean() );

		foreach ( $admin_users as $admin_user ) {
			$replacable['{user_name}'] = tutor_utils()->get_user_name( $admin_user );
			$message                   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $admin_user->user_email, $subject, $message, $header );
		}

	}

	/**
	 * After new course submit for review
	 *
	 * @since 1.6.9
	 *
	 * @param object $post post.
	 *
	 * @return mixed
	 */
	public function tutor_course_pending( $post ) {

		if ( tutor()->course_post_type !== $post->post_type ) {
			return true;
		}

		$new_course_submitted = tutor_utils()->get_option( 'email_to_admin.new_course_submitted' );

		if ( ! $new_course_submitted ) {
			return;
		}

		$site_url              = get_bloginfo( 'url' );
		$site_name             = get_bloginfo( 'name' );
		$submitted_time        = tutor_time();
		$submitted_time_format = date_i18n( get_option( 'date_format' ), $submitted_time ) . ' ' . date_i18n( get_option( 'time_format' ), $submitted_time );
		$instructor_name       = get_the_author_meta( 'display_name', $post->post_author );
		$admin_users           = get_users( array( 'role__in' => array( 'administrator' ) ) );
		$option_data           = $this->get_option_data( self::TO_ADMIN, 'new_course_submitted' );
		$header                = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header                = apply_filters( 'course_updated_email_header', $header, $post->ID );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{course_name}']          = $post->post_title;
		$replacable['{course_url}']           = get_the_permalink( $post->ID );
		$replacable['{course_edit_url}']      = get_edit_post_link( $post->ID );
		$replacable['{instructor_name}']      = $instructor_name;
		$replacable['{submitted_time}']       = $submitted_time_format;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );

		$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                       = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_admin_new_course_submitted_for_review' );
		$email_tpl = apply_filters( 'tutor_email_tpl/new_course_submitted', ob_get_clean() );

		foreach ( $admin_users as $admin_user ) {
			$replacable['{user_name}'] = tutor_utils()->get_user_name( $admin_user );
			$message                   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $admin_user->user_email, $subject, $message, $header );
		}

	}

	/**
	 * After new course published
	 *
	 * @since 1.6.9
	 *
	 * @param object $post post.
	 *
	 * @return mixed
	 */
	public function tutor_course_published( $post ) {

		if ( tutor()->course_post_type !== $post->post_type ) {
			return true;
		}

		$new_course_published = tutor_utils()->get_option( 'email_to_admin.new_course_published' );

		if ( ! $new_course_published ) {
			return;
		}

		$site_url              = get_bloginfo( 'url' );
		$site_name             = get_bloginfo( 'name' );
		$published_time        = tutor_time();
		$published_time_format = date_i18n( get_option( 'date_format' ), $published_time ) . ' ' . date_i18n( get_option( 'time_format' ), $published_time );
		$instructor_name       = get_the_author_meta( 'display_name', $post->post_author );
		$admin_users           = get_users( array( 'role__in' => array( 'administrator' ) ) );
		$option_data           = $this->get_option_data( self::TO_ADMIN, 'new_course_published' );
		$header                = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header                = apply_filters( 'course_updated_email_header', $header, $post->ID );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{course_name}']          = $post->post_title;
		$replacable['{course_url}']           = get_the_permalink( $post->ID );
		$replacable['{course_edit_url}']      = get_edit_post_link( $post->ID );
		$replacable['{instructor_name}']      = $instructor_name;
		$replacable['{published_time}']       = $published_time_format;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );

		$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                       = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_admin_new_course_published' );
		$email_tpl = apply_filters( 'tutor_email_tpl/new_course_published', ob_get_clean() );

		foreach ( $admin_users as $admin_user ) {
			$replacable['{user_name}'] = tutor_utils()->get_user_name( $admin_user );
			$message                   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $admin_user->user_email, $subject, $message, $header );
		}

	}

	/**
	 * After course updated/edited
	 *
	 * @since 1.6.9
	 *
	 * @param int    $course_id course id.
	 * @param object $course course.
	 * @param bool   $update update or not.
	 *
	 * @return void.
	 */
	public function tutor_course_updated( $course_id, $course, $update = false ) {
		$course_updated = tutor_utils()->get_option( 'email_to_admin.course_updated' );
		$tutor_ajax     = Input::post( 'tutor_ajax_action' );
		$auto_save      = 'tutor_course_builder_draft_save' === $tutor_ajax;

		if ( ! $course_updated || ! $update || 'pending' !== $course->post_status || $auto_save ) {
			return;
		}
		if ( 'publish' === $course->post_status ) {
			return;
		}
		if ( 'Publish' === Input::post( 'original_publish' ) ) {
			return;
		}

		$site_url            = get_bloginfo( 'url' );
		$site_name           = get_bloginfo( 'name' );
		$updated_time        = tutor_time();
		$updated_time_format = date_i18n( get_option( 'date_format' ), $updated_time ) . ' ' . date_i18n( get_option( 'time_format' ), $updated_time );
		$instructor_name     = get_the_author_meta( 'display_name', $course->post_author );
		$admin_users         = get_users( array( 'role__in' => array( 'administrator' ) ) );
		$option_data         = $this->get_option_data( self::TO_ADMIN, 'course_updated' );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters( 'course_updated_email_header', $header, $course_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_title}']           = $site_name;
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{course_url}']           = get_the_permalink( $course_id );
		$replacable['{instructor_name}']      = $instructor_name;
		$replacable['{updated_time}']         = $updated_time_format;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );

		$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                       = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_admin_course_updated' );
		$email_tpl = apply_filters( 'tutor_email_tpl/course_updated', ob_get_clean() );

		foreach ( $admin_users as $admin_user ) {
			$replacable['{user_name}'] = tutor_utils()->get_user_name( $admin_user );
			$message                   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $admin_user->user_email, $subject, $message, $header );
		}

	}

	/**
	 * After assignment submitted
	 *
	 * @since 1.6.9
	 *
	 * @param int $assignment_submit_id assignment submit id.
	 *
	 * @return void
	 */
	public function tutor_assignment_after_submitted( $assignment_submit_id ) {
		// Get post id by comment.
		$assignment_post_id = $this->get_comment_post_id_by_comment_id( $assignment_submit_id );

		// Get assignment autor and course author.
		$authors = $this->get_assignment_and_course_authors( $assignment_post_id );

		$student_submitted_assignment = tutor_utils()->get_option( 'email_to_teachers.student_submitted_assignment' );

		if ( ! $student_submitted_assignment ) {
			return;
		}

		$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submit_id );
		$student_name         = get_the_author_meta( 'display_name', $submitted_assignment->user_id );
		$course_name          = get_the_title( $submitted_assignment->comment_parent );
		$course_url           = get_the_permalink( $submitted_assignment->comment_parent );
		$author_id            = get_post_field( 'post_author', $submitted_assignment->comment_parent );

		$instructor_name = tutor_utils()->get_user_name( get_userdata( $author_id ) );
		$assignment_name = get_the_title( $submitted_assignment->comment_post_ID );
		$submitted_url   = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/submitted' );
		$review_link     = esc_url( $submitted_url . '?assignment=' . $submitted_assignment->comment_post_ID );
		$site_url        = get_bloginfo( 'url' );
		$site_name       = get_bloginfo( 'name' );
		$option_data     = $this->get_option_data( self::TO_TEACHERS, 'student_submitted_assignment' );
		$header          = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header          = apply_filters( 'student_submitted_assignment_email_header', $header, $assignment_submit_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{student_name}']         = $student_name;
		$replacable['{course_name}']          = $course_name;
		$replacable['{user_name}']            = $instructor_name;
		$replacable['{assignment_name}']      = $assignment_name;
		$replacable['{review_link}']          = $review_link;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{before_button}']        = $this->get_replaced_text( $option_data['before_button'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_student_submitted_assignment' );
		$email_tpl = apply_filters( 'tutor_email_tpl/student_submitted_assignment', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$author_emails = $to_emails = array(); //phpcs:ignore

		foreach ( $authors as $author ) {
			$author_emails[] = $author;
		}

		$to_emails = array_unique( $author_emails );

		$this->send( $to_emails, $subject, $message, $header );
	}

	/**
	 * After assignment evaluate
	 *
	 * @since 1.6.9
	 *
	 * @param int $assignment_submit_id assignment submit id.
	 *
	 * @return void
	 */
	public function tutor_after_assignment_evaluate( $assignment_submit_id ) {

		$assignment_graded = tutor_utils()->get_option( 'email_to_students.assignment_graded' );

		if ( ! $assignment_graded ) {
			return;
		}

		$site_url             = get_bloginfo( 'url' );
		$site_name            = get_bloginfo( 'name' );
		$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submit_id );
		$student_email        = get_the_author_meta( 'user_email', $submitted_assignment->user_id );
		$student_name         = tutor_utils()->get_user_name( get_userdata( $submitted_assignment->user_id ) );
		$course_name          = get_the_title( $submitted_assignment->comment_parent );
		$course_url           = get_the_permalink( $submitted_assignment->comment_parent );
		$assignment_max_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
		$assignment_name      = get_the_title( $submitted_assignment->comment_post_ID );
		$assignment_url       = get_the_permalink( $submitted_assignment->comment_post_ID );
		$assignment_score     = get_comment_meta( $assignment_submit_id, 'assignment_mark', true );
		$assignment_comment   = get_comment_meta( $assignment_submit_id, 'instructor_note', true );
		$option_data          = $this->get_option_data( self::TO_STUDENTS, 'assignment_graded' );
		$header               = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header               = apply_filters( 'assignment_evaluate_email_header', $header, $assignment_submit_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{course_name}']          = $course_name;
		$replacable['{course_url}']           = $course_url;
		$replacable['{user_name}']            = $student_name;
		$replacable['{assignment_name}']      = $assignment_name;
		$replacable['{assignment_url}']       = $assignment_url;
		$replacable['{assignment_max_mark}']  = $assignment_max_mark;
		$replacable['{assignment_score}']     = $assignment_score;
		$replacable['{assignment_comment}']   = $assignment_comment;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_assignment_evaluate' );
		$email_tpl = apply_filters( 'tutor_email_tpl/assignment_evaluate', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $student_email, $subject, $message, $header );

	}

	/**
	 * After remove student from course
	 *
	 * @since 1.6.9
	 *
	 * @param int $enrol_id enrol id.
	 *
	 * @return void
	 */
	public function tutor_student_remove_from_course( $enrol_id ) {
		$remove_from_course = tutor_utils()->get_option( 'email_to_students.remove_from_course' );

		if ( ! $remove_from_course ) {
			return;
		}

		$enrolment = tutor_utils()->get_enrolment_by_enrol_id( $enrol_id );
		if ( ! $enrolment ) {
			return;
		}

		$site_url      = get_bloginfo( 'url' );
		$site_name     = get_bloginfo( 'name' );
		$course_name   = $enrolment->course_title;
		$course_url    = get_the_permalink( $enrolment->course_id );
		$student_email = $enrolment->user_email;
		$student_id    = $enrolment->ID;
		$option_data   = $this->get_option_data( self::TO_STUDENTS, 'remove_from_course' );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters( 'remove_from_course_email_header', $header, $enrol_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( get_userdata( $student_id ) );
		$replacable['{course_name}']          = $course_name;
		$replacable['{course_url}']           = $course_url;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_remove_from_course' );
		$email_tpl = apply_filters( 'tutor_email_tpl/remove_from_course', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $student_email, $subject, $message, $header );

	}

	/**
	 * Enrollment After Expired
	 *
	 * @since 1.8.1
	 *
	 * @param int $enrol_id enrol id.
	 *
	 * @return void
	 */
	public function tutor_enrollment_after_expired( $enrol_id ) {
		$enrollment_expired = tutor_utils()->get_option( 'email_to_students.enrollment_expired' );

		if ( ! $enrollment_expired ) {
			return;
		}

		$enrolment = tutor_utils()->get_enrolment_by_enrol_id( $enrol_id );
		if ( ! $enrolment ) {
			return;
		}

		$site_url      = get_bloginfo( 'url' );
		$site_name     = get_bloginfo( 'name' );
		$course_name   = $enrolment->course_title;
		$course_url    = get_the_permalink( $enrolment->course_id );
		$student_name  = tutor_utils()->get_user_name( get_userdata( $enrolment->ID ) );
		$student_email = $enrolment->user_email;
		$option_data   = $this->get_option_data( self::TO_STUDENTS, 'enrollment_expired' );
		$header        = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header        = apply_filters( 'enrollment_expired_email_header', $header, $enrol_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{user_name}']            = $student_name;
		$replacable['{course_name}']          = $course_name;
		$replacable['{course_url}']           = $course_url;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( isset( $option_data['footer_text'] ) ? $option_data['footer_text'] : '', array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_enrollment_expired' );
		$email_tpl = apply_filters( 'tutor_email_tpl/to_student_enrollment_expired', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $student_email, $subject, $message, $header );

	}

	/**
	 * After save new announcement
	 *
	 * @since 1.6.9
	 *
	 * @param int    $announcement_id announcement id.
	 * @param object $announcement announcement.
	 * @param string $action_type action type.
	 *
	 * @return void
	 */
	public function tutor_announcements_notify_students( $announcement_id, $announcement, $action_type = '' ) {

		$new_announcement_posted = tutor_utils()->get_option( 'email_to_students.new_announcement_posted' );
		$announcement_updated    = tutor_utils()->get_option( 'email_to_students.announcement_updated' );

		if ( ! $new_announcement_posted && ! $announcement_updated ) {
			return;
		}

		$site_url             = get_bloginfo( 'url' );
		$site_name            = get_bloginfo( 'name' );
		$course_name          = get_the_title( $announcement->post_parent );
		$course_url           = get_the_permalink( $announcement->post_parent );
		$announcement_title   = $announcement->post_title;
		$announcement_content = $announcement->post_content;
		$announcement_author  = $announcement->post_author;
		$announcement_date    = $announcement->post_date;
		$author_fullname      = get_the_author_meta( 'display_name', $announcement_author );

		$option_data_create = $this->get_option_data( self::TO_STUDENTS, 'new_announcement_posted' );
		$option_data_update = $this->get_option_data( self::TO_STUDENTS, 'announcement_updated' );
		$header             = 'Content-Type: ' . $this->get_content_type() . "\r\n";

		$replacable['{author_fullname}']      = $author_fullname;
		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{course_name}']          = $course_name;
		$replacable['{course_url}']           = $course_url;
		$replacable['{announcement_title}']   = $announcement_title;
		$replacable['{announcement_content}'] = $announcement_content;
		$replacable['{announcement_date}']    = $announcement_date;

		$enrolled_students = tutor_utils()->get_students_all_data_by_course_id( $announcement->post_parent );

		foreach ( $enrolled_students as $enrolled_student ) {
			$replacable['{user_name}'] = tutor_utils()->get_user_name( get_userdata( $enrolled_student->ID ) );

			if ( 'create' === $action_type ) {
				if ( ! $new_announcement_posted ) {
					return;
				}

				$replacable['{logo}']          = isset( $option_data_create['logo'] ) ? $option_data_create['logo'] : $this->email_logo;
				$replacable['{email_heading}'] = $this->get_replaced_text( $option_data_create['heading'], array_keys( $replacable ), array_values( $replacable ) );
				$replacable['{footer_text}']   = $option_data_create['footer_text'];
				$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data_create['message'] ), array_keys( $replacable ), array_values( $replacable ) );
				$subject                       = $this->get_replaced_text( $option_data_create['subject'], array_keys( $replacable ), array_values( $replacable ) );
				$template                      = 'to_student_new_announcement_posted';

			} elseif ( 'update' === $action_type ) {
				if ( ! $announcement_updated ) {
					return;
				}

				$replacable['{logo}']          = isset( $option_data_update['logo'] ) ? $option_data_update['logo'] : $this->email_logo;
				$replacable['{email_heading}'] = $this->get_replaced_text( $option_data_update['heading'], array_keys( $replacable ), array_values( $replacable ) );
				$replacable['{footer_text}']   = $option_data_update['footer_text'];
				$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data_update['message'] ), array_keys( $replacable ), array_values( $replacable ) );
				$subject                       = $this->get_replaced_text( $option_data_update['subject'], array_keys( $replacable ), array_values( $replacable ) );
				$template                      = 'to_student_announcement_updated';
			}

			ob_start();
			$this->tutor_load_email_template( $template );
			$email_tpl = apply_filters( 'tutor_email_tpl/' . $template, ob_get_clean() );
			$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

			$this->send( $enrolled_student->user_email, $subject, $message, $header );
		}

	}


	/**
	 * Send mail to student after question answered
	 *
	 * @since 2.1.8
	 *
	 * Send email to all who are connected to the question thread
	 *
	 * @param object $student_details  student info who opened the thread.
	 * @param array  $reply_details  reply details.
	 * @param object $question_details  question details.
	 * @param object $course  course details.
	 *
	 * @return void
	 */
	public function question_answered_by_instructor( object $student_details, array $reply_details, object $question_details, object $course ) {
		$after_question_answered = tutor_utils()->get_option( 'email_to_students.after_question_answered' );

		if ( ! $after_question_answered ) {
			return;
		}

		// Select all user who are connected with the thread.
		$users = self::get_thread_users( $question_details, $reply_details );

		$replier       = get_userdata( $reply_details['user_id'] );
		$replier_name  = isset( $reply_details['user_id'] ) ? tutor_utils()->display_name( $reply_details['user_id'] ) : '';
		$replier_email = $replier->user_email;

		// Remove replier from users list.
		if ( is_array( $users ) && count( $users ) ) {
			$users = array_filter(
				$users,
				function( $user ) use ( $replier_email ) {
					if ( $user->user_email !== $replier_email ) {
						return $user;
					}
				}
			);
		}

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_STUDENTS, 'after_question_answered' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'question_answered_email_header', $header, $reply_details );

		$subject = "{$replier_name} replied to this question";

		$is_replier_instructor = user_can( $reply_details['user_id'], tutor()->instructor_role );
		if ( $is_replier_instructor ) {
			$subject = 'The instructor has replied to this question';
		}

		$email_heading = 'Q&A message answered';

		// Send mail to all users who are on the reply thread.
		foreach ( $users as $user ) {
			$receiver_email = $user->user_email;
			$receiver_name  = tutor_utils()->display_name( $user->ID );
			// Get instructor info.
			$replacable['{testing_email_notice}'] = '';
			$replacable['{answer}']               = $reply_details['comment_content'];
			$replacable['{answer_by}']            = $replier_name;
			$replacable['{user_name}']            = $receiver_name;

			$replacable['{answer_date}']       = $reply_details['comment_date'];
			$replacable['{question}']          = $question_details->comment_content;
			$replacable['{course_name}']       = $course->post_title;
			$replacable['{course_url}']        = get_the_permalink( $course->ID );
			$replacable['{answer_url}']        = tutor_utils()->tutor_dashboard_url() . 'question-answer';
			$replacable['{site_url}']          = $site_url;
			$replacable['{site_name}']         = $site_name;
			$replacable['{logo}']              = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
			$replacable['{email_heading}']     = $this->get_replaced_text( $email_heading, array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{instructor_avatar}'] = get_avatar( $reply_details['user_id'] );
			$replacable['{before_button}']     = 'Please click on this link to reply to the question.';

			if ( isset( $option_data['before_button'] ) ) {
				$replacable['{before_button}'] = $this->get_replaced_text( $option_data['before_button'], array_keys( $replacable ), array_values( $replacable ) );
			}

			if ( isset( $option_data['heading'] ) ) {
				$replacable['{email_heading}'] = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
			}

			if ( isset( $option_data['footer_text'] ) ) {
				$replacable['{footer_text}'] = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
			}

			if ( $is_replier_instructor && isset( $option_data['message'] ) ) {
				$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
			} else {
				$replacable['{email_message}'] = $replier_name . ' has replied the question on the course ';
			}

			if ( $is_replier_instructor && isset( $option_data['subject'] ) ) {
				$subject = $option_data['subject'];
			}

			ob_start();
			$this->tutor_load_email_template( 'to_student_question_answered' );
			$email_tpl = apply_filters( 'tutor_email_tpl/question_answered', ob_get_clean() );
			$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

			$this->send( $receiver_email, $subject, $message, $header );
		}
	}

	/**
	 * Send email to instructor after asked question
	 * Send email to student if instructor reply
	 *
	 * @since v2.0.2
	 *
	 * @param array $question_details question details.
	 *
	 * @return void
	 */
	public function tutor_after_asked_question( array $question_details ) {
		if ( ! is_array( $question_details ) ) {
			return;
		}

		$is_enabled_email_to_teacher = tutor_utils()->get_option( 'email_to_teachers.a_student_placed_question' );
		$is_enabled_email_to_student = tutor_utils()->get_option( 'email_to_students.after_question_answered' );

		if ( ! $is_enabled_email_to_teacher && ! $is_enabled_email_to_student ) {
			return;
		}

		$course      = get_post( $question_details['comment_post_ID'] );
		$course_name = get_the_title( $course->ID );

		// Get instructor info.
		$instructor_data = get_userdata( $course->post_author );
		$instructor_name = tutor_utils()->get_user_name( $instructor_data );

		$course_url = get_the_permalink( $course->ID );

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_TEACHERS, 'a_student_placed_question' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'tutor_email_header_to_instructor_asked_question_by_student', $header );

		// Get student info.
		$student_details = get_userdata( $question_details['user_id'] );
		$student_name    = tutor_utils()->get_user_name( $student_details );

		$subject = "New Question from $student_name on $course_name ";
		$to      = $instructor_data->user_email;

		// If has comment parent then it is reply thread hence send mail to student.
		if ( $question_details['comment_parent'] ) {
			$parent_question_details = get_comment( $question_details['comment_parent'] );
			if ( false === is_a( $parent_question_details, 'WP_Comment' ) ) {
				return;
			}

			// If student reply himself then don't need to send mail.
			if ( $parent_question_details->user_id === $question_details['user_id'] ) {
				return;
			}

			// Mail to student.
			$student_details = get_userdata( $parent_question_details->user_id );
			$this->question_answered_by_instructor( $student_details, $question_details, $parent_question_details, $course );

			return;
		}

		// Prepare placeholder value.
		$replacable['{testing_email_notice}'] = '';
		$replacable['{student_name}']         = $student_name;
		$replacable['{question_date}']        = $question_details['comment_date'];
		$replacable['{user_name}']            = $instructor_name;
		$replacable['{question_title}']       = $question_details['comment_content'];
		$replacable['{question_url}']         = tutor_utils()->tutor_dashboard_url() . 'question-answer';
		$replacable['{course_name}']          = $course_name;
		$replacable['{course_url}']           = $course_url;
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{student_avatar}']       = get_avatar( $question_details['user_id'] );

		if ( isset( $option_data['footer_text'] ) ) {
			$replacable['{footer_text}'] = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		}

		if ( isset( $option_data['message'] ) ) {
			$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		}

		if ( isset( $option_data['subject'] ) ) {
			$subject = $subject;
		}

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_asked_question_by_student' );
		$email_tpl = apply_filters( 'tutor_email_student_asked_question', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $to, $subject, $message, $header );
	}

	/**
	 * Instructor mail after comment.
	 *
	 * @param int $comment_data comment data.
	 *
	 * @return void
	 */
	public function lesson_comment_to_instructor( $comment_data ) {
		$comment_notification_ins = tutor_utils()->get_option( 'email_to_teachers.new_lesson_comment_posted' );
		if ( ! $comment_notification_ins ) {
			return;
		}
		$comment_lesson_id = $comment_data['comment_post_ID'];

		$user_id      = get_current_user_id();
		$student      = get_userdata( $user_id );
		$course_id    = tutor_utils()->get_course_id_by_lesson( $comment_lesson_id );
		$course       = get_post( $course_id );
		$teacher      = get_userdata( $course->post_author );
		$get_comment  = $comment_data['comment_content'];
		$lesson_title = get_the_title( $comment_lesson_id );
		$site_url     = get_bloginfo( 'url' );
		$site_name    = get_bloginfo( 'name' );
		$option_data  = $this->get_option_data( self::TO_TEACHERS, 'new_lesson_comment_posted' );
		$header       = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header       = apply_filters( 'to_instructor_commented', $header, $course_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $teacher );
		$replacable['{comment_by}']           = $student->display_name;
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{lesson_title}']         = $lesson_title;
		$replacable['{course_url}']           = get_the_permalink( $course_id );
		$replacable['{comment}']              = $get_comment;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{before_button}']        = $this->get_replaced_text( $option_data['before_button'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_commented_student' );
		$email_tpl = apply_filters( 'tutor_email_tpl/to_instructor_commented', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $teacher->user_email, $subject, $message, $header );

	}


	/**
	 * After quiz attempts feedback
	 *
	 * @since 1.6.9
	 *
	 * @param int $attempt_id attempt id.
	 *
	 * @return void
	 */
	public function feedback_submitted_for_quiz_attempt( $attempt_id ) {
		$feedback_submitted_for_quiz = tutor_utils()->get_option( 'email_to_students.feedback_submitted_for_quiz' );

		if ( ! $feedback_submitted_for_quiz ) {
			return;
		}

		$attempt             = tutor_utils()->get_attempt( $attempt_id );
		$quiz_title          = get_post_field( 'post_title', $attempt->quiz_id );
		$course              = get_post( $attempt->course_id );
		$instructor_name     = get_the_author_meta( 'display_name', $course->post_author );
		$instructor_feedback = get_post_meta( $attempt_id, 'instructor_feedback', true );
		$user_email          = get_the_author_meta( 'user_email', $attempt->user_id );
		$student_fullname    = tutor_utils()->get_user_name( get_userdata( $attempt->user_id ) );
		$site_url            = get_bloginfo( 'url' );
		$site_name           = get_bloginfo( 'name' );
		$option_data         = $this->get_option_data( self::TO_STUDENTS, 'feedback_submitted_for_quiz' );
		$block_heading       = $option_data['block_heading'];
		$block_content       = $option_data['block_content'];
		$header              = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header              = apply_filters( 'feedback_submitted_for_quiz_email_header', $header, $attempt_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{quiz_name}']            = $quiz_title;
		$replacable['{total_marks}']          = $attempt->total_marks;
		$replacable['{earned_marks}']         = $attempt->earned_marks;
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{instructor_name}']      = $instructor_name;
		$replacable['{user_name}']            = $student_fullname;
		$replacable['{instructor_feedback}']  = $instructor_feedback;
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{block_heading}']        = $block_heading;
		$replacable['{block_content}']        = $block_content;
		$replacable['{review_url}']           = tutor_utils()->tutor_dashboard_url() . 'my-quiz-attempts/?view_quiz_attempt_id=' . $attempt_id;

		$replacable['{logo}']          = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}'] = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']   = $this->get_replaced_text( isset( $option_data['footer_text'] ) ? $option_data['footer_text'] : '', array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}'] = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                       = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_feedback_submitted_for_quiz' );
		$email_tpl = apply_filters( 'tutor_email_tpl/feedback_submitted_for_quiz', ob_get_clean() );
		$message   = $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) );

		$this->send( $user_email, $subject, $message, $header );

	}

	/**
	 * After course completed
	 * this method not used yet, but future it can be
	 *
	 * @since 1.6.9
	 *
	 * @param int $course_id course id.
	 *
	 * @return void
	 */
	public function tutor_course_complete_after( $course_id ) {
		$rate_course_and_instructor = tutor_utils()->get_option( 'email_to_students.rate_course_and_instructor' );

		if ( ! $rate_course_and_instructor ) {
			return;
		}

		$site_url         = get_bloginfo( 'url' );
		$site_name        = get_bloginfo( 'name' );
		$course           = get_post( $course_id );
		$course_url       = get_the_permalink( $course_id );
		$instructor_url   = tutor_utils()->profile_url( $course->post_author, true );
		$user_id          = get_current_user_id();
		$user_email       = get_the_author_meta( 'user_email', $user_id );
		$student_fullname = tutor_utils()->get_user_name( get_userdata( $user_id ) );
		$option_data      = $this->email_options['email_to_students']['rate_course_and_instructor'];
		$header           = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header           = apply_filters( 'rate_course_and_instructor_email_header', $header, $course_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{user_name}']            = $student_fullname;
		$replacable['{course_name}']          = $course->post_title;
		$replacable['{course_url}']           = $course_url;
		$replacable['{instructor_url}']       = $instructor_url;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_student_rate_course_and_instructor' );
		$email_tpl = apply_filters( 'tutor_email_tpl/rate_course_and_instructor', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $user_email, $subject, $message, $header );

	}


	/**
	 * Comment post id by comment id.
	 *
	 * @param int $comment_id comment id.
	 *
	 * @return int
	 */
	public function get_comment_post_id_by_comment_id( $comment_id ) {
		global $wpdb;

		$query = $wpdb->get_row(
			$wpdb->prepare( "SELECT comment_post_ID FROM {$wpdb->comments} WHERE comment_ID = %d", $comment_id )
		);

		return $query->comment_post_ID;
	}


	/**
	 * Get assignment and course authors
	 *
	 * @param int $assignment_post_id assignment post id.
	 *
	 * @return array
	 */
	public function get_assignment_and_course_authors( $assignment_post_id ) {
		// Get course id of assignment.
		$course_id = tutor_utils()->get_course_id_by( 'assignment', $assignment_post_id );

		$course_author     = $this->get_author_by_post_id( $course_id );
		$assignment_author = $this->get_author_by_post_id( $assignment_post_id );

		$authors = array();
		if ( false !== $course_author ) {
			$authors[] = $course_author->user_email;
		}
		if ( false !== $assignment_author ) {
			$authors[] = $assignment_author->user_email;
		}

		return array_unique( $authors );
	}


	/**
	 * Get author by post id.
	 *
	 * @param int $post_id post id.
	 *
	 * @return mixed
	 */
	public function get_author_by_post_id( $post_id ) {
		global $wpdb;
		// get author for associate course.
		$author = $wpdb->get_row(
			$wpdb->prepare( "SELECT u.ID,u.user_email FROM {$wpdb->users} u JOIN {$wpdb->posts} p ON p.post_author = u.ID WHERE p.ID = %d", $post_id )
		);
		return $author ? $author : false;
	}

	/**
	 * Email to instructor when application is approved.
	 *
	 * @param int $instructor_id instructor id.
	 *
	 * @return void
	 */
	public function instructor_application_approved( $instructor_id ) {

		$send_accepted = tutor_utils()->get_option( 'email_to_teachers.instructor_application_accepted' );
		if ( ! $send_accepted ) {
			return;
		}

		$user_info   = get_userdata( $instructor_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_TEACHERS, 'instructor_application_accepted' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'instructor_application_approved_email_header', $header, $user_info->ID );

		$replacable['{dashboard_url}']        = tutor_utils()->get_tutor_dashboard_page_permalink();
		$replacable['{testing_email_notice}'] = '';
		$replacable['{instructor_username}']  = $user_info->display_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $user_info );
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_become_application_approved' );
		$email_tpl = apply_filters( 'tutor_email_tpl/instructor_application_approved', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $user_info->user_email, $subject, $message, $header );

	}

	/**
	 * Email to instructor when application is rejected.
	 *
	 * @param int $instructor_id instructor id.
	 *
	 * @return void
	 */
	public function instructor_application_rejected( $instructor_id ) {

		$send_rejected = tutor_utils()->get_option( 'email_to_teachers.instructor_application_rejected' );
		if ( ! $send_rejected ) {
			return;
		}

		$user_info   = get_userdata( $instructor_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_TEACHERS, 'instructor_application_rejected' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'instructor_application_rejected_email_header', $header, $user_info->ID );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{instructor_username}']  = $user_info->display_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $user_info );
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_become_application_rejected' );
		$email_tpl = apply_filters( 'tutor_email_tpl/instructor_application_rejected', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $user_info->user_email, $subject, $message, $header );

	}

	/**
	 * Get instructor by withdraw id.
	 *
	 * @param int $withdrawal_id withdrawal id.
	 *
	 * @return object
	 */
	private function get_instructor_by_witdrawal( $withdrawal_id ) {

		global $wpdb;

		$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}tutor_withdraws WHERE withdraw_id = %d", $withdrawal_id ) );

		return get_userdata( $user_id );
	}

	/**
	 * Email to instructor when withdrawal request is approved.
	 *
	 * @param int $withdrawal_id withdrawal id.
	 *
	 * @return void
	 */
	public function withdrawal_request_approved( $withdrawal_id ) {

		$option_status = tutor_utils()->get_option( 'email_to_teachers.withdrawal_request_approved' );
		if ( ! $option_status ) {
			return;
		}

		$instructor = $this->get_instructor_by_witdrawal( $withdrawal_id );

		$withdrawal            = $this->get_witdrawal_by_id( $withdrawal_id );
		$withdraw_method       = maybe_unserialize( $withdrawal->method_data )['withdraw_method_name'];
		$approve_time          = $withdrawal->created_at;
		$withdraw_approve_time = date_i18n( get_option( 'date_format' ), $approve_time ) . ' ' . date_i18n( get_option( 'time_format' ), $approve_time );
		$withdraw_amount       = $withdrawal->amount;
		$currency              = get_option( 'woocommerce_currency' );

		$total_amount = tutor_utils()->get_earning_sum( $instructor->ID )->balance;

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_TEACHERS, 'withdrawal_request_approved' );
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header      = apply_filters( 'withdrawal_request_approved_email_header', $header, $withdrawal_id );

		$replacable['{testing_email_notice}']  = '';
		$replacable['{instructor_username}']   = $instructor->display_name;
		$replacable['{admin_user}']            = wp_get_current_user()->display_name;
		$replacable['{user_name}']             = tutor_utils()->get_user_name( $instructor );
		$replacable['{withdraw_amount}']       = $withdraw_amount . ' ' . $currency;
		$replacable['{withdraw_method_name}']  = $withdraw_method;
		$replacable['{withdraw_approve_time}'] = $withdraw_approve_time;
		$replacable['{total_amount}']          = $total_amount . ' ' . $currency;
		$replacable['{site_url}']              = $site_url;
		$replacable['{site_name}']             = $site_name;
		$replacable['{logo}']                  = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']         = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']           = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']         = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                               = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_withdrawal_request_approved' );
		$email_tpl = apply_filters( 'tutor_email_tpl/withdrawal_request_approved', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $instructor->user_email, $subject, $message, $header );

	}

	/**
	 * Email to instructor when withdrawal request is rejected.
	 *
	 * @param int $withdrawal_id withdrawal id.
	 *
	 * @return void
	 */
	public function withdrawal_request_rejected( $withdrawal_id ) {

		$instructor    = $this->get_instructor_by_witdrawal( $withdrawal_id );
		$option_status = tutor_utils()->get_option( 'email_to_teachers.withdrawal_request_rejected' );
		if ( ! $option_status ) {
			return;
		}

		$site_url             = get_bloginfo( 'url' );
		$site_name            = get_bloginfo( 'name' );
		$option_data          = $this->get_option_data( self::TO_TEACHERS, 'withdrawal_request_rejected' );
		$withdrawal           = $this->get_witdrawal_by_id( $withdrawal_id );
		$withdraw_method      = maybe_unserialize( $withdrawal->method_data )['withdraw_method_name'];
		$reject_time          = $withdrawal->created_at;
		$withdraw_reject_time = date_i18n( get_option( 'date_format' ), $reject_time ) . ' ' . date_i18n( get_option( 'time_format' ), $reject_time );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters( 'withdrawal_request_rejected_email_header', $header, $withdrawal_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{admin_user}']           = wp_get_current_user()->display_name;
		$replacable['{instructor_username}']  = $instructor->display_name;
		$replacable['{withdraw_amount}']      = $withdrawal->amount;
		$replacable['{withdraw_method_name}'] = $withdraw_method;
		$replacable['{withdraw_reject_time}'] = $withdraw_reject_time;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $instructor );
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_withdrawal_request_rejected' );
		$email_tpl = apply_filters( 'tutor_email_tpl/withdrawal_request_rejected', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $instructor->user_email, $subject, $message, $header );

	}

	/**
	 * Get withdrawal data.
	 *
	 * @param int $withdrawal_id withdrawal id.
	 *
	 * @return object
	 */
	private function get_witdrawal_by_id( $withdrawal_id ) {

		global $wpdb;

		$withdraw_request = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_withdraws WHERE withdraw_id = %d", $withdrawal_id ) );

		return $withdraw_request;
	}

	/**
	 * E-mail when withdrawal.
	 *
	 * @param int $withdrawal_id withdrawal id.
	 *
	 * @return void
	 */
	public function withdrawal_request_placed( $withdrawal_id ) {

		$admin_withdrawal_request_status  = tutor_utils()->get_option( 'email_to_admin.new_withdrawal_request' );
		$teacher_withdrawl_request_status = tutor_utils()->get_option( 'email_to_teachers.withdrawal_request_received' );

		if ( ! $admin_withdrawal_request_status && ! $teacher_withdrawl_request_status ) {
			return;
		}

		$instructor      = $this->get_instructor_by_witdrawal( $withdrawal_id );
		$withdraw        = $this->get_witdrawal_by_id( $withdrawal_id );
		$withdraw_method = maybe_unserialize( $withdraw->method_data )['withdraw_method_name'];
		$withdraw_amount = $withdraw->amount;
		$request_time    = $withdraw->created_at;

		$admin_users = get_users( array( 'role__in' => array( 'administrator' ) ) );

		$approved_url = add_query_arg(
			array(
				'page'        => 'tutor_withdraw_requests',
				'action'      => 'approve',
				'withdraw_id' => $withdrawal_id,
			),
			admin_url( 'admin.php' )
		);
		$rejected_url = add_query_arg(
			array(
				'page'        => 'tutor_withdraw_requests',
				'action'      => 'reject',
				'withdraw_id' => $withdrawal_id,
			),
			admin_url( 'admin.php' )
		);

		//phpcs:ignore
		$subject  = __( 'New withdrawal request from ' . $instructor->display_name . ' for ' . $instructor->amount, 'tutor-pro' );
		$currency = get_option( 'woocommerce_currency' );

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->get_option_data( self::TO_ADMIN, 'new_withdrawal_request' );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters( 'new_withdrawal_request_email_header', $header, $withdrawal_id );

		$replacable['{testing_email_notice}'] = '';
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{instructor_username}']  = $instructor->display_name;
		$replacable['{instructor_email}']     = $instructor->user_email;
		$replacable['{withdraw_amount}']      = $withdraw_amount . ' ' . $currency;
		$replacable['{withdraw_method_name}'] = $withdraw_method;
		$replacable['{request_time}']         = $request_time;
		$replacable['{approved_url}']         = $approved_url;
		$replacable['{rejected_url}']         = $rejected_url;
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_admin_new_withdrawal_request' );
		$email_tpl = apply_filters( 'tutor_email_tpl/new_withdrawal_request', ob_get_clean() );

		foreach ( $admin_users as $admin_user ) {
			$replacable['{user_name}'] = tutor_utils()->get_user_name( $admin_user );
			$message                   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $admin_user->user_email, $subject, $message, $header );
		}

		if ( $teacher_withdrawl_request_status ) {
			$this->withdrawal_received_to_instructor( $instructor, $withdrawal_id );
			return;
		}
	}

	/**
	 * Email to instructor when withdrawal request placed.
	 *
	 * @param object $instructor instructor.
	 * @param int    $withdrawal_id withdrawal id.
	 *
	 * @return void
	 */
	private function withdrawal_received_to_instructor( $instructor, $withdrawal_id ) {

		$option_status = tutor_utils()->get_option( 'email_to_teachers.withdrawal_request_received' );
		if ( ! $option_status ) {
			return;
		}

		$withdraw        = $this->get_witdrawal_by_id( $withdrawal_id );
		$withdraw_amount = $withdraw->amount;
		$currency        = get_option( 'woocommerce_currency' );

		$site_url        = get_bloginfo( 'url' );
		$site_name       = get_bloginfo( 'name' );
		$option_data     = $this->email_options['email_to_teachers']['withdrawal_request_received'];
		$header          = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header          = apply_filters( 'withdrawal_request_received_email_header', $header, $instructor->ID );
		$withdrawal      = $this->get_witdrawal_by_id( $withdrawal_id );
		$withdraw_method = maybe_unserialize( $withdrawal->method_data )['withdraw_method_name'];
		$reject_time     = $withdrawal->created_at;
		$withdraw_time   = date_i18n( get_option( 'date_format' ), $reject_time ) . ' ' . date_i18n( get_option( 'time_format' ), $reject_time );

		$total_amount = tutor_utils()->get_earning_sum()->balance;

		$replacable['{testing_email_notice}'] = '';
		$replacable['{instructor_username}']  = $instructor->display_name;
		$replacable['{user_name}']            = tutor_utils()->get_user_name( $instructor );
		$replacable['{total_amount}']         = $total_amount . ' ' . $currency;
		$replacable['{withdraw_amount}']      = $withdraw_amount . ' ' . $currency;
		$replacable['{withdraw_method}']      = $withdraw_method;
		$replacable['{withdraw_time}']        = $withdraw_time;
		$replacable['{site_url}']             = $site_url;
		$replacable['{site_name}']            = $site_name;
		$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
		$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

		ob_start();
		$this->tutor_load_email_template( 'to_instructor_withdrawal_request_received' );
		$email_tpl = apply_filters( 'tutor_email_tpl/withdrawal_request_received', ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$this->send( $instructor->user_email, $subject, $message, $header );

	}

	/**
	 * Email to student when LQA published.
	 *
	 * @param mixed $lqa lesson question answer.
	 *
	 * @return void
	 */
	public function new_lqa_published( $lqa ) {
		$lqa_type      = $lqa['lqa_type'];
		$option_status = tutor_utils()->get_option( 'email_to_students.new_' . $lqa_type . '_published' );
		if ( ! $option_status ) {
			return;
		}

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$option_data = $this->email_options['email_to_students'][ 'new_' . $lqa_type . '_published' ];
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";

		$replacable['{testing_email_notice}']      = '';
		$replacable['{student_username}']          = $lqa['student']->display_name;
		$replacable['{user_name}']                 = tutor_utils()->get_user_name( get_userdata( $lqa['student']->ID ) );
		$replacable[ '{' . $lqa_type . '_title}' ] = $lqa['lqa']->post_title;
		$replacable['{course_title}']              = $lqa['course']->post_title;
		$replacable['{site_url}']                  = $site_url;
		$replacable['{site_name}']                 = $site_name;
		$replacable['{logo}']                      = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
		$replacable['{email_heading}']             = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{footer_text}']               = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
		$replacable['{email_message}']             = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );

		$subject   = sprintf( __( 'New %s Published', 'tutor-pro' ), __( $lqa_type, 'tutor-pro' ) ); //phpcs:ignore
		$hook_name = 'new_' . strtolower( $lqa_type ) . '_published';

		ob_start();
		$this->tutor_load_email_template( 'to_student_new_' . $lqa['lqa_type'] . '_published' );
		$email_tpl = apply_filters( 'tutor_email_tpl/' . $hook_name, ob_get_clean() );
		$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters( $hook_name . '_email_header', $header, $lqa['lqa']->ID );

		$this->send( $lqa['student']->user_email, $subject, $message, $header, array(), true );
	}

	/**
	 * Email enqueue.
	 *
	 * @param string  $to to.
	 * @param string  $subject subject.
	 * @param string  $message message.
	 * @param mixed   $headers headers.
	 * @param array   $attachments attachments.
	 * @param boolean $force_enqueue force enqueue.
	 * @param int     $batch batch number. default false.
	 *
	 * @return void
	 */
	private function enqueue_email( $to, $subject, $message, $headers, $attachments = array(), $force_enqueue = false, $batch = false ) {
		global $wpdb;

		if ( ! $batch ) {
			$batch = time();
		}

		$data = array(
			'mail_to' => $to,
			'subject' => $subject,
			'message' => $message,
			'headers' => serialize( $headers ),
			'batch'   => $batch,
		);

		if ( is_string( $to ) && ! $force_enqueue ) {
			// Send email instantly in case single recipient.
			$this->send_mail( array( $data ) );
			return;
		}

		! is_array( $to ) ? $to = array( $to ) : 0;

		foreach ( $to as $email ) {
			$insert_data = array_merge( $data, array( 'mail_to' => $email ) );
			$wpdb->insert( $this->queue_table, $insert_data );
		}
	}

	/**
	 * Sent email.
	 *
	 * @param array $mails list of mail address.
	 *
	 * @return void
	 */
	public function send_mail( $mails ) {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		foreach ( $mails as $mail ) {
			$mail['headers'] = unserialize( $mail['headers'] );
			wp_mail( $mail['mail_to'], $mail['subject'], $mail['message'], $mail['headers'] );
		}

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Send course update notification mail to instructor
	 *
	 * Event course publish | trash
	 *
	 * @since 1.9.8
	 *
	 * @param int    $post_id post id.
	 * @param object $post post.
	 * @param mixed  $update update.
	 *
	 * @return void.
	 */
	public function tutor_course_update_notification( $post_id, $post, $update ) {
		// Check if author is tutor instructor.
		$course                 = $post;
		$course_status          = $course->post_status;
		$is_enable_publish_mail = tutor_utils()->get_option( 'email_to_teachers.instructor_course_publish' );
		$is_enable_reject_mail  = tutor_utils()->get_option( 'email_to_teachers.a_instructor_course_rejected' );
		$ins_can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );

		if ( $ins_can_publish_course ) {
			return;
		}

		if ( $is_enable_reject_mail && tutor_utils()->is_instructor( $course->post_author ) && 'trash' === $course_status ) {
			$site_url        = get_bloginfo( 'url' );
			$site_name       = get_bloginfo( 'name' );
			$option_data     = $this->get_option_data( self::TO_TEACHERS, 'a_instructor_course_rejected' );
			$header          = 'Content-Type: ' . $this->get_content_type() . "\r\n";
			$header          = apply_filters( 'to_instructor_course_update_subject', $header, $course->ID );
			$instructor_name = tutor_utils()->get_user_name( get_userdata( $course->post_author ) );
			$course_url      = get_post_permalink( $course->ID );
			$course_edit_url = get_edit_post_link( $course->ID );
			$course_title    = $course->post_title;
			$author_email    = get_the_author_meta( 'user_email', $course->post_author );

			$replacable['{testing_email_notice}'] = '';
			$replacable['{course_name}']          = $course_title;
			$replacable['{site_url}']             = $site_url;
			$replacable['{site_name}']            = $site_name;
			$replacable['{user_name}']            = $instructor_name;
			$replacable['{course_url}']           = $course_url;
			$replacable['{course_edit_url}']      = $course_edit_url;
			$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
			$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'] ?? '', array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
			$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

			ob_start();
			$this->tutor_load_email_template( 'to_instructor_course_rejected' );
			$email_tpl = apply_filters( 'to_instructor_course_rejected', ob_get_clean() );
			$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $author_email, $subject, $message, $header );

		}

		if ( $is_enable_publish_mail && tutor_utils()->is_instructor( $course->post_author ) && 'publish' === $course_status ) {

			$site_url        = get_bloginfo( 'url' );
			$site_name       = get_bloginfo( 'name' );
			$option_data     = $this->email_options['email_to_teachers']['instructor_course_publish'];
			$header          = 'Content-Type: ' . $this->get_content_type() . "\r\n";
			$header          = apply_filters( 'to_instructor_course_update_subject', $header, $course->ID );
			$instructor_name = tutor_utils()->get_user_name( get_userdata( $course->post_author ) );
			$course_url      = get_post_permalink( $course->ID );
			$course_edit_url = get_edit_post_link( $course->ID );
			$course_title    = $course->post_title;
			$author_email    = get_the_author_meta( 'user_email', $course->post_author );

			$replacable['{testing_email_notice}'] = '';
			$replacable['{course_name}']          = $course_title;
			$replacable['{site_url}']             = $site_url;
			$replacable['{site_name}']            = $site_name;
			$replacable['{user_name}']            = $instructor_name;
			$replacable['{course_url}']           = $course_url;
			$replacable['{course_edit_url}']      = $course_edit_url;
			$replacable['{logo}']                 = isset( $option_data['logo'] ) ? $option_data['logo'] : '';
			$replacable['{email_heading}']        = $this->get_replaced_text( $option_data['heading'], array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{footer_text}']          = $this->get_replaced_text( $option_data['footer_text'], array_keys( $replacable ), array_values( $replacable ) );
			$replacable['{email_message}']        = $this->get_replaced_text( $this->prepare_message( $option_data['message'] ), array_keys( $replacable ), array_values( $replacable ) );
			$subject                              = $this->get_replaced_text( $option_data['subject'], array_keys( $replacable ), array_values( $replacable ) );

			ob_start();
			$this->tutor_load_email_template( 'to_instructor_course_accepted' );
			$email_tpl = apply_filters( 'to_instructor_course_accepted', ob_get_clean() );
			$message   = html_entity_decode( $this->get_message( $email_tpl, array_keys( $replacable ), array_values( $replacable ) ) );
			$this->send( $author_email, $subject, $message, $header, array(), true );

			update_post_meta( $post_id, 'tutor_instructor_course_publish', true );
		}
	}

	/**
	 * Facilitate tutor_course_update_notification method
	 *
	 * @param string $author_email author email.
	 * @param string $template template.
	 * @param mixed  $file_tpl_variable file tpl variable.
	 * @param mixed  $replace_data replace data.
	 * @param object $course course.
	 * @param string $subject subject.
	 *
	 * @return bool
	 */
	public function tutor_send_course_update_notification( $author_email, $template, $file_tpl_variable, $replace_data, $course, $subject ) {
		if ( '' !== $template ) {
			$to_emails = array( $author_email );
			ob_start();
			$this->tutor_load_email_template( $template );
			$email_tpl = apply_filters( 'tutor_email_tpl/to_instructor_course_update', ob_get_clean() );

			$message = $this->get_message( $email_tpl, $file_tpl_variable, $replace_data );

			$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
			$header = apply_filters( 'to_instructor_course_update_subject', $header, $course->ID );

			$this->send( array_unique( $to_emails ), $subject, $message, $header );
			return true;
		}
		return false;
	}

	/**
	 * Get connected users on the question thread
	 *
	 * @since 2.1.8
	 *
	 * @param object $question_details question object typically wp_comment obj.
	 * @param array  $reply_details it is also a comment in array format.
	 * @param string $comment_type it is comment type.
	 *
	 * @return wp::get_results
	 */
	public static function get_thread_users( object $question_details, array $reply_details, string $comment_type = 'tutor_q_and_a' ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT
				DISTINCT u.ID,u.user_email
			FROM {$wpdb->comments} AS c
			INNER JOIN {$wpdb->users} AS u
				ON u.ID = c.user_id
			WHERE c.comment_parent = %d
				OR c.comment_ID = %d
				AND c.comment_type = %s
			",
			$question_details->comment_ID,
			$question_details->comment_ID,
			$comment_type
		);
		return $wpdb->get_results( $query ); //phpcs:ignore
	}

	/**
	 * Reset inactive meta
	 *
	 * @since 2.5.0
	 *
	 * @param string   $user_login active user name.
	 * @param \WP_User $user User object data.
	 *
	 * @return void
	 */
	public static function reset_inactive_reminded_meta( $user_login, \WP_User $user ) {
		$existing_meta = get_user_meta( $user->ID, self::INACTIVE_REMINDED_META, true );
		if ( ! empty( $existing_meta ) ) {
			delete_user_meta( $user->ID, self::INACTIVE_REMINDED_META );
		}
	}

}
