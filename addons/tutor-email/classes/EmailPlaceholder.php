<?php
/**
 * Email placeholder variables manage
 *
 * @package TutorPro
 * @subpackage Addons\TutorEmail
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.5.0
 */

namespace TUTOR_EMAIL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EmailPlaceholder
 *
 * @since 2.5.0
 */
class EmailPlaceholder {
	/**
	 * Get available email placeholder variable list.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public static function all() {

		$arr = array(
			'site_name'              => array(
				'placeholder' => '{site_name}',
				'label'       => __( 'Site Name', 'tutor-pro' ),
			),
			'site_url'               => array(
				'placeholder' => '{site_url}',
				'label'       => __( 'Site URL', 'tutor-pro' ),
			),
			'current_year'           => array(
				'placeholder' => '{current_year}',
				'label'       => __( 'Current Year', 'tutor-pro' ),
			),
			'user_name'              => array(
				'placeholder' => '{user_name}',
				'label'       => __( 'Username', 'tutor-pro' ),
			),
			'inactive_days'          => array(
				'placeholder' => '{inactive_days}',
				'label'       => __( 'Inactive Days', 'tutor-pro' ),
			),
			'course_name'            => array(
				'placeholder' => '{course_name}',
				'label'       => __( 'Course Name', 'tutor-pro' ),
			),
			'dashboard_url'          => array(
				'placeholder' => '{dashboard_url}',
				'label'       => __( 'Dashboard URL', 'tutor-pro' ),
			),
			'course_url'             => array(
				'placeholder' => '{course_url}',
				'label'       => __( 'Course URL', 'tutor-pro' ),
			),
			'lesson_title'           => array(
				'placeholder' => '{lesson_title}',
				'label'       => __( 'Lesson Title', 'tutor-pro' ),
			),
			'quiz_title'             => array(
				'placeholder' => '{quiz_title}',
				'label'       => __( 'Quiz Title', 'tutor-pro' ),
			),
			'assignment_title'       => array(
				'placeholder' => '{assignment_title}',
				'label'       => __( 'Assignment Title', 'tutor-pro' ),
			),
			'earned_marks'           => array(
				'placeholder' => '{earned_marks}',
				'label'       => __( 'Earned Marks', 'tutor-pro' ),
			),
			'total_marks'            => array(
				'placeholder' => '{total_marks}',
				'label'       => __( 'Total Marks', 'tutor-pro' ),
			),
			'attempt_result'         => array(
				'placeholder' => '{attempt_result}',
				'label'       => __( 'Attempt Result', 'tutor-pro' ),
			),
			'student_name'           => array(
				'placeholder' => '{student_name}',
				'label'       => __( 'Student Name', 'tutor-pro' ),
			),
			'student_username'       => array(
				'placeholder' => '{student_username}',
				'label'       => __( 'Student Username', 'tutor-pro' ),
			),
			'admin_name'             => array(
				'placeholder' => '{admin_name}',
				'label'       => __( 'Admin Name', 'tutor-pro' ),
			),
			'admin_user'             => array(
				'placeholder' => '{admin_user}',
				'label'       => __( 'Amin Username', 'tutor-pro' ),
			),
			'student_email'          => array(
				'placeholder' => '{student_email}',
				'label'       => __( 'Student Email', 'tutor-pro' ),
			),
			'tutor_url'              => array(
				'placeholder' => '{tutor_url}',
				'label'       => __( 'Tutor URL', 'tutor-pro' ),
			),
			'profile_url'            => array(
				'placeholder' => '{profile_url}',
				'label'       => __( 'Profile URL', 'tutor-pro' ),
			),
			'student_url'            => array(
				'placeholder' => '{student_url}',
				'label'       => __( 'Student URL', 'tutor-pro' ),
			),
			'course_title'           => array(
				'placeholder' => '{course_title}',
				'label'       => __( 'Course Title', 'tutor-pro' ),
			),
			'total_amount'           => array(
				'placeholder' => '{total_amount}',
				'label'       => __( 'Total Amount', 'tutor-pro' ),
			),
			'earned_amount'          => array(
				'placeholder' => '{earned_amount}',
				'label'       => __( 'Earned Amount', 'tutor-pro' ),
			),
			'lesson_name'            => array(
				'placeholder' => '{lesson_name}',
				'label'       => __( 'Lesson Name', 'tutor-pro' ),
			),
			'quiz_name'              => array(
				'placeholder' => '{quiz_name}',
				'label'       => __( 'Quiz Name', 'tutor-pro' ),
			),
			'question'               => array(
				'placeholder' => '{question}',
				'label'       => __( 'Question', 'tutor-pro' ),
			),
			'enroll_time'            => array(
				'placeholder' => '{enroll_time}',
				'label'       => __( 'Enroll Time', 'tutor-pro' ),
			),
			'instructor_username'    => array(
				'placeholder' => '{instructor_username}',
				'label'       => __( 'Instructor Username', 'tutor-pro' ),
			),
			'instructor_avatar'      => array(
				'placeholder' => '{instructor_avatar}',
				'label'       => __( 'Instructor Avatar', 'tutor-pro' ),
			),
			'instructor_description' => array(
				'placeholder' => '{instructor_description}',
				'label'       => __( 'Instructor Description', 'tutor-pro' ),
			),
			'answer_by'              => array(
				'placeholder' => '{answer_by}',
				'label'       => __( 'Answer By', 'tutor-pro' ),
			),
			'answer_date'            => array(
				'placeholder' => '{answer_date}',
				'label'       => __( 'Answer Date', 'tutor-pro' ),
			),
			'before_button'          => array(
				'placeholder' => '{before_button}',
				'label'       => __( 'Before Button', 'tutor-pro' ),
			),
			'username'               => array(
				'placeholder' => '{username}',
				'label'       => __( 'Username', 'tutor-pro' ),
			),
			'instructor_email'       => array(
				'placeholder' => '{instructor_email}',
				'label'       => __( 'Instructor Email', 'tutor-pro' ),
			),
			'instructor_name'        => array(
				'placeholder' => '{instructor_name}',
				'label'       => __( 'Instructor Name', 'tutor-pro' ),
			),
			'withdraw_amount'        => array(
				'placeholder' => '{withdraw_amount}',
				'label'       => __( 'Withdraw Amount', 'tutor-pro' ),
			),
			'assignment_name'        => array(
				'placeholder' => '{assignment_name}',
				'label'       => __( 'Assignment Name', 'tutor-pro' ),
			),
			'assignment_score'       => array(
				'placeholder' => '{assignment_score}',
				'label'       => __( 'Assignment Score', 'tutor-pro' ),
			),
			'assignment_max_mark'    => array(
				'placeholder' => '{assignment_max_mark}',
				'label'       => __( 'Assignment Maximum Mark', 'tutor-pro' ),
			),
			'approved_url'           => array(
				'placeholder' => '{approved_url}',
				'label'       => __( 'Approved URL', 'tutor-pro' ),
			),
			'rejected_url'           => array(
				'placeholder' => '{rejected_url}',
				'label'       => __( 'Rejected URL', 'tutor-pro' ),
			),
			'announcement_title'     => array(
				'placeholder' => '{announcement_title}',
				'label'       => __( 'Announcement Title', 'tutor-pro' ),
			),
			'announcement_content'   => array(
				'placeholder' => '{announcement_content}',
				'label'       => __( 'Announcement Content', 'tutor-pro' ),
			),
			'announcement_date'      => array(
				'placeholder' => '{announcement_date}',
				'label'       => __( 'Announcement Date', 'tutor-pro' ),
			),
			'author_fullname'        => array(
				'placeholder' => '{author_fullname}',
				'label'       => __( 'Author Full Name', 'tutor-pro' ),
			),
			'assignment_comment'     => array(
				'placeholder' => '{assignment_comment}',
				'label'       => __( 'Assignment Comment', 'tutor-pro' ),
			),
			'attempt_url'            => array(
				'placeholder' => '{attempt_url}',
				'label'       => __( 'Attempt Url', 'tutor-pro' ),
			),
			'completion_time'        => array(
				'placeholder' => '{completion_time}',
				'label'       => __( 'Completion Time', 'tutor-pro' ),
			),
			'student_report_url'     => array(
				'placeholder' => '{student_report_url}',
				'label'       => __( 'Student Report URL', 'tutor-pro' ),
			),
			'submission_time'        => array(
				'placeholder' => '{submission_time}',
				'label'       => __( 'Submission Time', 'tutor-pro' ),
			),
			'quiz_url'               => array(
				'placeholder' => '{quiz_url}',
				'label'       => __( 'Quiz Url', 'tutor-pro' ),
			),
			'question_url'           => array(
				'placeholder' => '{question_url}',
				'label'       => __( 'Question Url', 'tutor-pro' ),
			),
			'answer_url'             => array(
				'placeholder' => '{answer_url}',
				'label'       => __( 'Answer Url', 'tutor-pro' ),
			),
			'quiz_review_url'        => array(
				'placeholder' => '{quiz_review_url}',
				'label'       => __( 'Quiz Review URL', 'tutor-pro' ),
			),
			'course_start_url'       => array(
				'placeholder' => '{course_start_url}',
				'label'       => __( 'Course Start URL', 'tutor-pro' ),
			),
			'question_title'         => array(
				'placeholder' => '{question_title}',
				'label'       => __( 'Question Title', 'tutor-pro' ),
			),
			'lesson_url'             => array(
				'placeholder' => '{lesson_url}',
				'label'       => __( 'Lesson URL', 'tutor-pro' ),
			),
			'review_url'             => array(
				'placeholder' => '{review_url}',
				'label'       => __( 'Review URL', 'tutor-pro' ),
			),
			'signup_time'            => array(
				'placeholder' => '{signup_time}',
				'label'       => __( 'Signup Time', 'tutor-pro' ),
			),
			'course_edit_url'        => array(
				'placeholder' => '{course_edit_url}',
				'label'       => __( 'Course Edit URL', 'tutor-pro' ),
			),
			'submitted_time'         => array(
				'placeholder' => '{submitted_time}',
				'label'       => __( 'Submitted Time', 'tutor-pro' ),
			),
			'published_time'         => array(
				'placeholder' => '{published_time}',
				'label'       => __( 'Published Time', 'tutor-pro' ),
			),
			'site_title'             => array(
				'placeholder' => '{site_title}',
				'label'       => __( 'Site Title', 'tutor-pro' ),
			),
			'updated_time'           => array(
				'placeholder' => '{updated_time}',
				'label'       => __( 'Updated Time', 'tutor-pro' ),
			),
			'review_link'            => array(
				'placeholder' => '{review_link}',
				'label'       => __( 'Review Link', 'tutor-pro' ),
			),
			'assignment_url'         => array(
				'placeholder' => '{assignment_url}',
				'label'       => __( 'Assignment URL', 'tutor-pro' ),
			),
			'answer'                 => array(
				'placeholder' => '{answer}',
				'label'       => __( 'Answer', 'tutor-pro' ),
			),
			'question_date'          => array(
				'placeholder' => '{question_date}',
				'label'       => __( 'Question Date', 'tutor-pro' ),
			),
			'student_avatar'         => array(
				'placeholder' => '{student_avatar}',
				'label'       => __( 'Student Avatar', 'tutor-pro' ),
			),
			'instructor_feedback'    => array(
				'placeholder' => '{instructor_feedback}',
				'label'       => __( 'Instructor Feedback', 'tutor-pro' ),
			),
			'instructor_url'         => array(
				'placeholder' => '{instructor_url}',
				'label'       => __( 'Instructor URL', 'tutor-pro' ),
			),
			'withdraw_method_name'   => array(
				'placeholder' => '{withdraw_method_name}',
				'label'       => __( 'Withdraw Method Name', 'tutor-pro' ),
			),
			'withdraw_approve_time'  => array(
				'placeholder' => '{withdraw_approve_time}',
				'label'       => __( 'Withdraw Approve Time', 'tutor-pro' ),
			),
			'withdraw_reject_time'   => array(
				'placeholder' => '{withdraw_reject_time}',
				'label'       => __( 'Withdraw Reject Time', 'tutor-pro' ),
			),
			'request_time'           => array(
				'placeholder' => '{request_time}',
				'label'       => __( 'Request Time', 'tutor-pro' ),
			),
			'withdraw_method'        => array(
				'placeholder' => '{withdraw_method}',
				'label'       => __( 'Withdraw Method', 'tutor-pro' ),
			),
			'withdraw_time'          => array(
				'placeholder' => '{withdraw_time}',
				'label'       => __( 'Withdraw Time', 'tutor-pro' ),
			),

		);
		return apply_filters( 'tutor_pro_email_placeholders', $arr );
	}

	/**
	 * Get selected placeholders.
	 *
	 * @since 2.5.0
	 *
	 * @param array $arr selected placeholders.
	 *
	 * @return array
	 */
	public static function only( array $arr ) {
		$result = array();
		$all    = self::all();
		foreach ( $arr as $key ) {
			if ( isset( $all[ $key ] ) ) {
				$result[ $key ] = $all[ $key ];
			}
		}
		return $result;
	}

	/**
	 * Get all placeholders except selected placeholders.
	 *
	 * @since 2.5.0
	 *
	 * @param array $arr selected placeholders.
	 *
	 * @return array
	 */
	public static function except( array $arr ) {
		$all = self::all();

		foreach ( $arr as $key ) {
			if ( isset( $all[ $key ] ) ) {
				unset( $all[ $key ] );
			}
		}

		return $all;
	}
}
