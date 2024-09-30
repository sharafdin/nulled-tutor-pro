<?php

/**
 * Tutor Push Notification
 * 
 * @package tutor
 */

namespace TUTOR_NOTIFICATIONS;

use \Minishlink\WebPush\WebPush;
use \Minishlink\WebPush\Subscription;

defined( 'ABSPATH' ) || exit;

/**
 * Pusher class
 */
class Pusher extends Push_Notification {

    /**
     * Constructor
     */
    public function __construct() {

        // Check if required extension loaded for WebPush
        foreach ( array( 'curl', 'gmp', 'mbstring', 'openssl' ) as $ext ) {
            if( ! extension_loaded( $ext ) ) {
                return;
            }
        }

        // Check if minimum php version is installed
        if ( ! version_compare( PHP_VERSION, '7.2.5', '>=' ) ) {
            return;
        }

        parent::__construct();

        add_action( 'tutor_after_approved_instructor', array( $this, 'instructor_approval' ) );
        add_action( 'tutor_after_rejected_instructor', array( $this, 'instructor_rejected') );
    
        add_action( 'tutor_new_instructor_after', array( $this, 'new_instructor_application') );
        add_action( 'tutor_add_new_instructor_after', array( $this, 'new_instructor_application') );

		add_action( 'tutor_assignment/evaluate/after', array( $this, 'tutor_after_assignment_evaluate' ), 10, 3 );
        add_action( 'tutor_announcements/after/save', array( $this, 'tutor_announcements_notify_students' ), 10, 3 );
        add_action( 'tutor_after_answer_to_question', array( $this, 'tutor_after_answer_to_question' ) );
        add_action( 'tutor_quiz/attempt/submitted/feedback', array( $this, 'feedback_submitted_for_quiz_attempt' ), 10, 3 );
        add_action( 'tutor_enrollment/after/expired', array( $this, 'tutor_enrollment_after_expired' ), 10, 3 );

		add_action( 'tutor_after_enrolled', array( $this, 'course_enroll_email_to_student' ), 10, 3 );
		add_action( 'tutor_enrollment/before/delete', array( $this, 'tutor_student_remove_from_course' ), 10, 3 );
		add_action( 'tutor_enrollment/after/cancel', array( $this, 'tutor_student_remove_from_course' ), 10, 3 );
    }

    /**
     * Get auth
     * 
     * @return array
     */
    private function get_auth() {

        $vapid = $this->get_vapid_keys();

        if ( $vapid ) {

            $vapid['subject'] = get_home_url();

            return array(
                'VAPID' => $vapid
            );
        }
    }

    /**
     * Broadcast
     * 
     * @param array $user)ids
     * @param string $title
     * @param string $message
     * @param string|bool $url
     */
    private function broadcast( $user_ids = array(), $title = '', $message = '', $url = null ) {

        $this->load_web_push();

        $notifications = array();
        $user_ids = is_array( $user_ids ) ? $user_ids : array( $user_ids );
        $user_ids = array_unique( $user_ids );

        $auth = $this->get_auth();
        if ( ! $auth ) {
            return;
        }

        try {
            $webPush = new WebPush( $auth );
        }
        catch(\Exception $e) {
            return;
        }
        

        $payload = array(
            'title' => $title,
            'body'  => $message,
            'data'  => array( 'url' => $url ),

            'badge' => get_site_icon_url( 96 ),
            'icon'  => get_site_icon_url( 256 ),
            'dir'   => is_rtl() ? 'rtl' : 'ltr',
            'timestamp' => time() * 1000
        );

        
        foreach ( $user_ids as $user_id ) {

            // Assign the recipient user id to match before showing notification.
            $payload['client_id'] = $user_id;

            foreach ( $this->get_subscriptions( $user_id ) as $browser_key => $sub ) {
                $payload['browser_key'] = $browser_key;
                $webPush->queueNotification( Subscription::create( $sub ), json_encode( $payload ) );
            }
        }
        
        foreach ( $webPush->flush() as $report ) {
            $report->isSuccess();
        }
    }

    /**
     * Instructor approval
     * 
     * @param int $instructor_id
     */
    public function instructor_approval( $instructor_id ) {
        
		$send_notification = tutor_utils()->get_option( 'tutor_pn_to_instructors.instructor_application_accepted' );
		if ( ! $send_notification ) {
			return;
		}

        if ( ! tutor_utils()->is_instructor( $instructor_id ) ) {
            return;
        }

        $user = get_userdata( $instructor_id) ;
        $name = $user->display_name;

        $message = sprintf( __( 'Congratulations %s! You are now an instructor.', 'tutor-pro' ), $name );

        $this->broadcast( $instructor_id, __( 'Instructor Approval', 'tutor-pro' ), $message, tutor_utils()->tutor_dashboard_url() );
    }

    /**
     * Instructor rejected
     * 
     * @param int $instructor_id
     */
    public function instructor_rejected( $instructor_id ) {

		$send_notification = tutor_utils()->get_option( 'tutor_pn_to_instructors.instructor_application_rejected' );
		if ( ! $send_notification ) {
			return;
		}

        $user = get_userdata( $instructor_id );
        $name = $user->display_name;

        $message = sprintf( __( 'Hello %s, your instructorship has been rejected.', 'tutor-pro' ), $name );

        $this->broadcast( $instructor_id, __( 'Instructor Rejection', 'tutor-pro' ), $message, tutor_utils()->tutor_dashboard_url() );
    }

    /**
     * New instructor application
     * 
     * @param int $instructor_id
     */
    public function new_instructor_application( $instructor_id ) {

		$send_notification = tutor_utils()->get_option( 'tutor_pn_to_admin.instructor_application_received' );
		if ( ! $send_notification ) {
			return;
		}

        $admin_users         = get_users( array( 'role__in' => array( 'administrator' ) ) );
        $instructor          = get_userdata( $instructor_id );
        $instructor_name     = $instructor->display_name;
        $instructor_page_url = admin_url( 'admin.php?page=' . \TUTOR\Instructors_List::INSTRUCTOR_LIST_PAGE );
        
        $message = sprintf(__( '%s wants to become an instructor.', 'tutor-pro' ), $instructor_name );

        foreach ( $admin_users as $admin ) {
            $this->broadcast( $admin->ID, __( 'New Instructor Request', 'tutor-pro' ), $message, $instructor_page_url );
        }
    }

    /**
     * Announcement notifications
     * 
     * @param int $announcement_id
     * @param array|object $announcement
     * @param string $action_type
     */
    public function tutor_announcements_notify_students( $announcement_id, $announcement, $action_type ) {
		$send_notification = tutor_utils()->get_option( 'tutor_pn_to_students.new_announcement_posted' );
		if ( ! isset( $_POST['tutor_push_notify_students'] ) || ! $_POST['tutor_push_notify_students'] || ! $send_notification ) {
			return;
		}

        $student_ids = tutor_utils()->get_students_data_by_course_id( $announcement->post_parent, 'ID' );
		$course_name = get_the_title( $announcement->post_parent );

        $title = $action_type == 'create' ? __( 'New Announcement Posted', 'tutor-pro' ) : __( 'Announcement Updated', 'tutor-pro' );
        $title = $title . ' : ' . $course_name;
        
        $message = $announcement->post_title;
        $assignment_url = get_permalink( $announcement->post_parent ) . 'announcements/';

        $this->broadcast( $student_ids, $title, $message, $assignment_url );
    }

    /**
     * After answering questions
     * 
     * @param int $answer_id
     * @return void
     */
    public function tutor_after_answer_to_question( $answer_id ) {
        $send_notification = tutor_utils()->get_option( 'tutor_pn_to_students.after_question_answered' );
        
		if ( ! $send_notification ) {
			return;
		}

		$answer = tutor_utils()->get_qa_answer_by_answer_id( $answer_id );

		$course_name = get_the_title( $answer->comment_post_ID );
        $qa_url      = tutor_utils()->tutor_dashboard_url( 'question-answer?question_id=' . $answer->question_id );

        $title = __( 'One of Your Questions Has Been Answered.', 'tutor-pro' ) . ' : ' . $course_name;

        $this->broadcast( $answer->question_by, $title, $answer->comment_content, $qa_url );
    }

    /**
     * Feedback submitted for quiz
     * 
     * @param int $attempt_id
     */
    public function feedback_submitted_for_quiz_attempt( $attempt_id ) {
        $send_notification = tutor_utils()->get_option( 'tutor_pn_to_students.feedback_submitted_for_quiz' );
        
		if ( ! $send_notification ) {
			return;
		}

		$attempt = tutor_utils()->get_attempt( $attempt_id );
		$quiz_title = get_post_field( 'post_title', $attempt->quiz_id );
		$course = get_post( $attempt->course_id );
		$instructor_feedback = get_post_meta( $attempt_id, 'instructor_feedback', true );

        $title = sprintf( __( 'Your Quiz Results Are Up', 'tutor-pro' ), $quiz_title ) . ' : ' . $course->post_title;

        $this->broadcast( $attempt->user_id, $title, $instructor_feedback, tutor_utils()->get_tutor_dashboard_page_permalink( 'my-quiz-attempts' ) );
    }

    /**
     * Enrolment Expired
     * 
     * @param int $enroll_id
     */
    public function tutor_enrollment_after_expired( $enrol_id ) {
        $send_notification = tutor_utils()->get_option( 'tutor_pn_to_students.enrollment_expired' );
        
		if ( ! $send_notification ) {
			return;
		}

		$enrolment = tutor_utils()->get_enrolment_by_enrol_id( $enrol_id );
		if ( ! $enrolment ) {
			return;
		}

		$course_name = $enrolment->course_title;
		$course_url = get_the_permalink( $enrolment->course_id );

        $title = __( 'Your Enrollment Has Expired.', 'tutor-pro' );

        $this->broadcast( $enrolment->student_id, $title, $course_name, $course_url );
    }

    /**
     * Email to student when enrolled in course
     * 
     * @param int $course_id
     * @param int $student_id
     * @param int $enroll_id
     * @param string $status_to
     */
    public function course_enroll_email_to_student( $course_id, $student_id, $enrol_id, $status_to='completed' ) {
        $send_notification = tutor_utils()->get_option( 'tutor_pn_to_students.course_enrolled' );
        
		if ( ! $send_notification || $status_to !== 'completed' ) {
			return;
		}

		$course = tutor_utils()->get_course_by_enrol_id( $enrol_id );
        $course_url = tutor_utils()->get_course_first_lesson( $course_id );
	
        $title = __( 'You Just Got Added to a Course! Go Check It Out.', 'tutor-pro' );

        $this->broadcast( $student_id, $title, $course->post_title, $course_url );
    }

    /**
     * Student removed from course
     * 
     * @param int $enroll_id
     */
    public function tutor_student_remove_from_course( $enrol_id ) {
        $send_notification = tutor_utils()->get_option( 'tutor_pn_to_students.remove_from_course' );
        
		if ( ! $send_notification ) {
			return;
		}

		$enrolment = tutor_utils()->get_enrolment_by_enrol_id( $enrol_id );
		if ( ! $enrolment ) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$course_name = $enrolment->course_title;
		$course_url = get_the_permalink( $enrolment->course_id );

        $title = $enrolment->status == 'cancel' ? __( 'Enrollment has been cancelled.', 'tutor-pro' ) : __( 'You\'ve Been Removed From a Course.', 'tutor-pro' );

        $this->broadcast( $enrolment->student_id, $title, $course_name, $course_url );
    }

    /**
     * Assignment graded
     * 
     * @param int $assignment_submit_id
     */
    public function tutor_after_assignment_evaluate( $assignment_submit_id ) {
        $send_notification = tutor_utils()->get_option( 'tutor_pn_to_students.assignment_graded' );
        
		if ( ! $send_notification ) {
			return;
		}

		$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submit_id );
		$assignment_name = get_the_title( $submitted_assignment->comment_post_ID );
		$assignment_comment = get_comment_meta( $assignment_submit_id, 'instructor_note', true );
        $assignment_url = get_permalink( $submitted_assignment->comment_post_ID );

        $title = sprintf( __( 'Your Grades for %s Were Just Submitted.', 'tutor-pro' ), $assignment_name );

        $this->broadcast( $submitted_assignment->user_id, $title, $assignment_comment, $assignment_url );
    }
}
