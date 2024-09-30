<?php
/**
 * Handle registering all notifications
 *
 * @package TutorPro\Addons
 * @subpackage Notification
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.9.10
 */

namespace TUTOR_NOTIFICATIONS;

use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

/**
 * Notifications class
 */
class Notifications {

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_after_approved_instructor', array( $this, 'instructor_approval' ) );
		add_action( 'tutor_after_rejected_instructor', array( $this, 'instructor_rejected' ) );

		add_action( 'tutor_new_instructor_after', array( $this, 'new_instructor_application' ) );

		add_action( 'tutor_assignment/evaluate/after', array( $this, 'tutor_after_assignment_evaluated' ), 10, 3 );
		add_action( 'tutor_announcements/after/save', array( $this, 'tutor_announcements_notify_students' ), 10, 3 );
		add_action( 'tutor_after_answer_to_question', array( $this, 'tutor_after_answer_to_question' ) );
		add_action( 'tutor_quiz/attempt/submitted/feedback', array( $this, 'feedback_submitted_for_quiz_attempt' ) );

		add_action( 'tutor_after_enrolled', array( $this, 'tutor_student_course_enrolled' ), 10, 3 );
		add_action( 'tutor_enrollment/after/cancel', array( $this, 'tutor_student_remove_from_course' ), 10, 1 );
	}

	/**
	 * Instructor Approval
	 *
	 * @param int $instructor_id instructor id.
	 *
	 * @return void
	 */
	public function instructor_approval( $instructor_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_instructors.instructor_application_accepted' );
		if ( ! $notification_enabled ) {
			return;
		}

		$user_data    = get_userdata( $instructor_id );
		$display_name = $user_data->display_name;

		$message_type   = 'Instructorship';
		$message_status = 'UNREAD';
		$message_title  = __( 'Instructorship', 'tutor-pro' );

		$translated_string1 = _x( 'Congratulations', 'instructorship-approved-text', 'tutor-pro' );
		$translated_string2 = _x( 'your application to be an instructor has been approved.', 'instructorship-approved-text', 'tutor-pro' );

		$message_content  = '<span class="tutor-color-secondary">';
		$message_content .= $translated_string1;
		$message_content .= '</span> ' . ucfirst( $display_name ) . ', <span class="tutor-color-secondary">';
		$message_content .= $translated_string2;
		$message_content .= '</span>';

		$data = array(
			'type'        => $message_type,
			'title'       => $message_title,
			'content'     => $message_content,
			'status'      => $message_status,
			'receiver_id' => (int) $instructor_id,
			'post_id'     => null,
			'topic_url'   => null,
		);

		Utils::save_notification_data( $data );
	}

	/**
	 * Instructor Rejected
	 *
	 * @param int $instructor_id instructor id.
	 *
	 * @return void
	 */
	public function instructor_rejected( $instructor_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_instructors.instructor_application_rejected' );
		if ( ! $notification_enabled ) {
			return;
		}

		$user_data    = get_userdata( $instructor_id );
		$display_name = $user_data->display_name;

		$message_type   = 'Instructorship';
		$message_status = 'UNREAD';
		$message_title  = __( 'Instructorship', 'tutor-pro' );

		$translated_string = _x( 'your instructorship application has been declined.', 'instructorship-rejected-text', 'tutor-pro' );

		$message_content  = ucfirst( $display_name ) . ', <span class="tutor-color-secondary">';
		$message_content .= $translated_string;
		$message_content .= '</span>';

		$data = array(
			'type'        => $message_type,
			'title'       => $message_title,
			'content'     => $message_content,
			'status'      => $message_status,
			'receiver_id' => (int) $instructor_id,
			'post_id'     => null,
			'topic_url'   => null,
		);

		Utils::save_notification_data( $data );
	}

	/**
	 * New Instructor Application
	 *
	 * @param int $instructor_id instructor id.
	 *
	 * @return void
	 */
	public function new_instructor_application( $instructor_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_admin.instructor_application_received' );
		if ( ! $notification_enabled ) {
			return;
		}

		$admin_users  = get_users( array( 'role__in' => array( 'administrator' ) ) );
		$user_data    = get_userdata( $instructor_id );
		$display_name = $user_data->display_name;

		$message_type   = 'Instructorship';
		$message_status = 'UNREAD';
		$message_title  = __( 'Instructorship', 'tutor-pro' );

		$admin_records = array();
		foreach ( $admin_users as $admin ) {
			$data = array(
				'type'        => $message_type,
				'title'       => $message_title,
				'status'      => $message_status,
				'receiver_id' => (int) $admin->ID,
				'post_id'     => null,
				'topic_url'   => null,
			);

			$translated_string1 = _x( 'you have received a new application from', 'instructor-application-received', 'tutor-pro' );
			$translated_string2 = _x( 'for Instructorship.', 'instructor-application-received', 'tutor-pro' );

			$message_content  = ucfirst( $admin->display_name ) . ', <span class="tutor-color-secondary">';
			$message_content .= $translated_string1;
			$message_content .= '</span> ' . ucfirst( $display_name ) . ' <span class="tutor-color-secondary">';
			$message_content .= $translated_string2;
			$message_content .= '</span>';

			$data['content'] = $message_content;

			array_push( $admin_records, $data );
		}

		foreach ( $admin_records as $admin_record ) {
			Utils::save_notification_data( $admin_record );
		}
	}

	/**
	 * Assignment Graded
	 *
	 * @param int $assignment_submission_id assignment submission id.
	 *
	 * @return void
	 */
	public function tutor_after_assignment_evaluated( $assignment_submission_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_students.assignment_graded' );
		if ( ! $notification_enabled ) {
			return;
		}

		$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submission_id );
		$assignment_name      = get_the_title( $submitted_assignment->comment_post_ID );
		$assignment_comment   = get_comment_meta( $assignment_submission_id, 'instructor_note', true );
		$assignment_url       = get_permalink( $submitted_assignment->comment_post_ID );

		$user_data    = get_userdata( $submitted_assignment->user_id );
		$display_name = $user_data->display_name;

		$message_type   = 'Assignments';
		$message_status = 'UNREAD';
		$message_title  = __( 'Assignments', 'tutor-pro' );

		$translated_string1 = _x( 'Hi', 'grades-submitted-text', 'tutor-pro' );
		$translated_string2 = _x( 'your', 'grades-submitted-text', 'tutor-pro' );
		$translated_string3 = _x( 'has been graded. Check it out.', 'grades-submitted-text', 'tutor-pro' );

		$message_content  = '<span class="tutor-color-secondary">';
		$message_content .= $translated_string1;
		$message_content .= '</span> ' . ucfirst( $display_name ) . ', <span class="tutor-color-secondary">';
		$message_content .= $translated_string2;
		$message_content .= '</span> ' . $assignment_name . ' <span class="tutor-color-secondary">';
		$message_content .= $translated_string3;
		$message_content .= '</span>';

		$data = array(
			'type'        => $message_type,
			'title'       => $message_title,
			'content'     => $message_content,
			'status'      => $message_status,
			'receiver_id' => (int) $submitted_assignment->user_id,
			'post_id'     => (int) $submitted_assignment->comment_post_ID,
			'topic_url'   => $assignment_url,
		);

		Utils::save_notification_data( $data );
	}

	/**
	 * Announcement Notifications
	 *
	 * @param int    $announcement_id announcement id.
	 * @param object $announcement announcement.
	 * @param string $action_type type.
	 *
	 * @return void
	 */
	public function tutor_announcements_notify_students( $announcement_id, $announcement, $action_type ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_students.new_announcement_posted' );

		if ( ! $notification_enabled || 'on' !== Input::post( 'tutor_notify_all_students' ) ) {
			return;
		}

		$student_ids = tutor_utils()->get_students_data_by_course_id( $announcement->post_parent, 'ID' );
		$course_name = get_the_title( $announcement->post_parent );
		$author      = get_userdata( $announcement->post_author );
		$author_name = $author->display_name;

		$message_type   = 'Announcements';
		$message_status = 'UNREAD';
		$message_title  = __( 'Announcements', 'tutor-pro' );

		$translated_string1 = 'create' === $action_type ? _x( 'A new announcement has been posted by', 'announcement-text', 'tutor-pro' ) : _x( 'An announcement has been updated by', 'announcement-text', 'tutor-pro' );
		$translated_string2 = _x( 'of', 'announcement-text', 'tutor-pro' );

		$message_content  = '<span class="tutor-color-secondary">';
		$message_content .= $translated_string1;
		$message_content .= '</span> ' . ucfirst( $author_name ) . ' <span class="tutor-color-secondary">';
		$message_content .= $translated_string2;
		$message_content .= '</span> ' . $course_name;

		$announcement_url = get_permalink( $announcement->post_parent );

		$announcement_records = array();

		// Loop through $student_ids to send announcements for each of them.
		foreach ( $student_ids as $key => $value ) {
			$announcement_records [] = array(
				'type'        => $message_type,
				'title'       => $message_title,
				'content'     => $message_content,
				'status'      => $message_status,
				'receiver_id' => (int) $value,
				'post_id'     => (int) $announcement->post_parent,
				'topic_url'   => $announcement_url,
			);
		}

		foreach ( $announcement_records as $announcement ) {
			Utils::save_notification_data( $announcement );
		}
	}

	/**
	 * After Answering Questions
	 *
	 * @param int $answer_id answer id.
	 *
	 * @return void
	 */
	public function tutor_after_answer_to_question( $answer_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_students.after_question_answered' );
		if ( ! $notification_enabled ) {
			return;
		}

		$answer          = tutor_utils()->get_qa_answer_by_answer_id( $answer_id );
		$course_name     = get_the_title( $answer->comment_post_ID );
		$comment_author  = 'tutor_q_and_a' === get_comment_type( $answer_id ) ? get_comment_author( $answer_id ) : 0;
		$question_author = $answer->question_by;

		$message_type   = 'Q&A';
		$message_status = 'UNREAD';
		$message_title  = __( 'Q&A', 'tutor-pro' );

		$translated_string1 = _x( 'A new answer has been posted by', 'qa-answer-posted', 'tutor-pro' );
		$translated_string2 = _x( 'in', 'qa-answer-posted', 'tutor-pro' );
		$translated_string3 = _x( '\'s Q&A.', 'qa-answer-posted', 'tutor-pro' );

		$message_content  = '<span class="tutor-color-secondary">';
		$message_content .= $translated_string1;
		$message_content .= '</span> ' . ucfirst( $comment_author ) . ' <span class="tutor-color-secondary">';
		$message_content .= $translated_string2;
		$message_content .= '</span> ' . $course_name;
		$message_content .= $translated_string3;

		$qa_url = tutor_utils()->tutor_dashboard_url( 'question-answer?question_id=' . $answer->question_id );

		$data = array(
			'type'        => $message_type,
			'title'       => $message_title,
			'content'     => $message_content,
			'status'      => $message_status,
			'receiver_id' => (int) $question_author,
			'post_id'     => (int) $answer->comment_post_ID,
			'topic_url'   => $qa_url,
		);

		Utils::save_notification_data( $data );
	}

	/**
	 * Feedback submitted for quizzes
	 *
	 * @param int $attempt_id attempt id.
	 *
	 * @return void
	 */
	public function feedback_submitted_for_quiz_attempt( $attempt_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_students.feedback_submitted_for_quiz' );
		if ( ! $notification_enabled ) {
			return;
		}

		$attempt    = tutor_utils()->get_attempt( $attempt_id );
		$quiz_title = get_post_field( 'post_title', $attempt->quiz_id );
		$course     = get_post( $attempt->course_id );
		$feedback   = get_post_meta( $attempt_id, 'instructor_feedback', true );

		$message_type   = 'Quiz';
		$message_status = 'UNREAD';
		$message_title  = __( 'Quiz', 'tutor-pro' );
		//phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		$message_content = sprintf( _x( '<span class="tutor-color-secondary">Your quiz result for</span> %1$s <span class="tutor-color-secondary">of</span> %2$s <span class="tutor-color-secondary">has been published.</span>', 'quiz-attempt-text', 'tutor-pro' ), $quiz_title, $course->post_title );

		$translated_string1 = _x( 'Your quiz result for', 'quiz-attempt-text', 'tutor-pro' );
		$translated_string2 = _x( 'of', 'quiz-attempt-text', 'tutor-pro' );
		$translated_string3 = _x( 'has been published.', 'quiz-attempt-text', 'tutor-pro' );

		$message_content  = '<span class="tutor-color-secondary">';
		$message_content .= $translated_string1;
		$message_content .= '</span> ' . $quiz_title . ' <span class="tutor-color-secondary">';
		$message_content .= $translated_string2;
		$message_content .= '</span> ' . $course->post_title . ' <span class="tutor-color-secondary">';
		$message_content .= $translated_string3;
		$message_content .= '</span>';

		$quiz_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'my-quiz-attempts' );

		$data = array(
			'type'        => $message_type,
			'title'       => $message_title,
			'content'     => $message_content,
			'status'      => $message_status,
			'receiver_id' => (int) $attempt->user_id,
			'post_id'     => (int) $course->ID,
			'topic_url'   => $quiz_url,
		);

		Utils::save_notification_data( $data );
	}

	/**
	 * Notification on course enrolled.
	 *
	 * @param int $course_id course id.
	 * @param int $user_id user id.
	 * @param int $enrollment_id enrollment id.
	 *
	 * @return void
	 */
	public function tutor_student_course_enrolled( $course_id, $user_id, $enrollment_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_students.course_enrolled' );
		if ( ! $notification_enabled ) {
			return;
		}

		$user_data    = get_userdata( $user_id );
		$display_name = $user_data->display_name;
		$course       = tutor_utils()->get_course_by_enrol_id( $enrollment_id );
		$course_title = $course->post_title;
		$course_url   = get_permalink( $course_id );

		$message_type   = 'Enrollments';
		$message_status = 'UNREAD';
		$message_title  = __( 'Enrollment', 'tutor-pro' );

		$translated_string = _x( 'Congratulations, you have been successfully enrolled in', 'got-enrolled-text', 'tutor-pro' );

		$message_content  = '<span class="tutor-color-secondary">';
		$message_content .= $translated_string;
		$message_content .= '</span> ' . $course_title;

		$data = array(
			'type'        => $message_type,
			'title'       => $message_title,
			'content'     => $message_content,
			'status'      => $message_status,
			'receiver_id' => (int) $user_id,
			'post_id'     => (int) $course_id,
			'topic_url'   => $course_url,
		);

		Utils::save_notification_data( $data );
	}

	/**
	 * Enrollment Cancelled
	 *
	 * @param int $enrollment_id  enrollement id.
	 *
	 * @return void
	 */
	public function tutor_student_remove_from_course( $enrollment_id ) {
		$notification_enabled = tutor_utils()->get_option( 'tutor_notifications_to_students.remove_from_course' );
		if ( ! $notification_enabled ) {
			return;
		}

		$course = tutor_utils()->get_enrolment_by_enrol_id( $enrollment_id );

		if ( ! $course ) {
			return;
		}

		$display_name = $course->display_name;
		$course_title = $course->course_title;
		$course_url   = get_permalink( $course->course_id );

		$message_type   = 'Enrollments';
		$message_status = 'UNREAD';
		$message_title  = __( 'Enrollment', 'tutor-pro' );

		//phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
		$message_content = sprintf( _x( '%1$s, <span class="tutor-color-secondary">your enrollment request for</span> %2$s <span class="tutor-color-secondary">has been declined.</span>', 'enrollment-cancelled-text', 'tutor-pro' ), ucfirst( $display_name ), $course_title );

		$translated_string1 = _x( 'your enrollment request for', 'enrollment-cancelled-text', 'tutor-pro' );
		$translated_string2 = _x( 'has been declined.', 'enrollment-cancelled-text', 'tutor-pro' );

		$message_content  = ucfirst( $display_name ) . ', <span class="tutor-color-secondary">';
		$message_content .= $translated_string1;
		$message_content .= '</span> ' . $course_title . ' <span class="tutor-color-secondary">';
		$message_content .= $translated_string2;
		$message_content .= '</span>';

		$data = array(
			'type'        => $message_type,
			'title'       => $message_title,
			'content'     => $message_content,
			'status'      => $message_status,
			'receiver_id' => (int) $course->ID,
			'post_id'     => (int) $course->course_id,
			'topic_url'   => $course_url,
		);

		Utils::save_notification_data( $data );
	}
}
