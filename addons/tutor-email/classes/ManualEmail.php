<?php
/**
 * Manual E-mail Handler Class
 *
 * @package TutorPro
 * @subpackage Addons\TutorEmail
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.5.0
 */

namespace TUTOR_EMAIL;

use Tutor\Helpers\QueryHelper;
use TUTOR\Input;
use Tutor\Traits\JsonResponse;
use TUTOR\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ManualEmail
 *
 * @since 2.5.0
 */
class ManualEmail {
	use JsonResponse;

	/**
	 * All constant for manual email receiver types
	 *
	 * @since 2.5.0
	 */
	const ALL                           = 'all';
	const ALL_STUDENT                   = 'all_student';
	const ALL_INSTRUCTOR                = 'all_instructor';
	const ALL_ADMIN                     = 'all_admin';
	const STUDENTS_COMPLETED_ANY_COURSE = 'students_completed_any_course';
	const INSTRUCTORS_OF_COURSES        = 'instructors_of_courses';
	const INSTRUCTORS_EXCEPT_COURSES    = 'instructors_except_courses';
	const STUDENTS_OF_COURSES           = 'students_of_courses';
	const STUDENTS_EXCEPT_COURSES       = 'students_except_courses';
	const STUDENTS_COMPLETED_COURSES    = 'students_completed_courses';

	/**
	 * Store draft data for manual email.
	 *
	 * @since 2.5.0
	 */
	const OPTION_KEY = 'tutor_manual_email_data';

	/**
	 * Register hooks.
	 *
	 * @since 2.5.0
	 *
	 * @param bool $reuse reuse the class or not.
	 *
	 * @return mixed
	 */
	public function __construct( $reuse = false ) {
		if ( $reuse ) {
			return;
		}

		add_action( 'wp_ajax_tutor_sent_manual_email', array( $this, 'sent_manual_email' ) );
		add_action( 'wp_ajax_tutor_manual_email_save_draft', array( $this, 'save_as_draft' ) );
		add_action( 'wp_ajax_tutor_manual_email_receiver_count_help_text', array( $this, 'get_receiver_count_help_text' ) );
		add_filter( 'tutor_email_template_colors_fields', array( $this, 'update_email_template_colors' ) );
	}

	/**
	 * Update color value for manual email template colors.
	 *
	 * @since 2.5.0
	 *
	 * @param array $email_color_fields email colors fields.
	 *
	 * @return array
	 */
	public function update_email_template_colors( $email_color_fields ) {
		if ( 'mailer' !== Input::get( 'edit' ) ) {
			return $email_color_fields;
		}

		$mailer_data           = get_option( self::OPTION_KEY );
		$email_template_colors = $mailer_data['email_template_colors'] ?? array();
		foreach ( $email_color_fields as $group_key => &$field_group ) {
			foreach ( $field_group as $key => &$color ) {
				if ( isset( $email_template_colors[ $key ] ) ) {
					$color['value'] = $email_template_colors[ $key ] ?? $color['default'];
				}
			}
		}

		return $email_color_fields;
	}

	/**
	 * Get all receiver types.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public static function get_receiver_types() {
		$types = array(
			self::ALL                           => __( 'Everyone (students, instructors, and admins)', 'tutor-pro' ),
			self::ALL_STUDENT                   => __( 'All students', 'tutor-pro' ),
			self::ALL_INSTRUCTOR                => __( 'All instructors', 'tutor-pro' ),
			self::ALL_ADMIN                     => __( 'All admins', 'tutor-pro' ),
			self::STUDENTS_COMPLETED_ANY_COURSE => __( 'All students who completed any course', 'tutor-pro' ),
			self::INSTRUCTORS_OF_COURSES        => __( 'All instructors of selected courses', 'tutor-pro' ),
			self::INSTRUCTORS_EXCEPT_COURSES    => __( 'All instructors except selected courses', 'tutor-pro' ),
			self::STUDENTS_OF_COURSES           => __( 'All students of selected courses', 'tutor-pro' ),
			self::STUDENTS_EXCEPT_COURSES       => __( 'All students except selected courses', 'tutor-pro' ),
			self::STUDENTS_COMPLETED_COURSES    => __( 'All students who completed the selected courses', 'tutor-pro' ),
		);

		return apply_filters( 'tutor_manual_email_receiver_types', $types );
	}

	/**
	 * Save manual email draft data.
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function save_as_draft() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			$this->response_fail( tutor_utils()->error_message() );
		}

		$data = array(
			'receiver_type'         => Input::post( 'receiver_type' ),
			'course_ids'            => Input::post( 'course_ids', array(), Input::TYPE_ARRAY ),
			'email_heading'         => Input::post( 'email_heading' ),
			'email_subject'         => Input::post( 'email_subject' ),
			'email_body'            => Input::post( 'email_body', '', Input::TYPE_KSES_POST ),
			'email_footer'          => Input::post( 'email_footer' ),
			'email_template_colors' => Input::post( 'email_template_colors', array(), Input::TYPE_ARRAY ),
			'email_action_button'   => Input::post( 'email_action_button' ),
			'email_action_label'    => Input::post( 'email_action_label' ),
			'email_action_link'     => Input::post( 'email_action_link' ),
			'email_action_position' => Input::post( 'email_action_position' ),
		);

		update_option( self::OPTION_KEY, $data );

		$this->response_success( __( 'Saved successfully.', 'tutor-pro' ) );
	}

	/**
	 * Get admin list
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	private static function get_admins() {
		return get_users(
			array(
				'role'   => User::ADMIN,
				'fields' => array(
					'display_name',
					'user_email',
				),
			)
		);
	}

	/**
	 * Get student list
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	private static function get_students() {
		global $wpdb;
		$list = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT u.display_name, u.user_email
				FROM {$wpdb->users} u
				LEFT JOIN {$wpdb->posts} p ON p.post_author = u.ID
				WHERE p.post_type= %s 
				AND p.post_status = %s",
				'tutor_enrolled',
				'completed'
			)
		);

		return $list;
	}

	/**
	 * Get instructor list
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	private static function get_instructors() {
		global $wpdb;
		$list = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT u.display_name, u.user_email
				FROM {$wpdb->users} u
				LEFT JOIN {$wpdb->usermeta} um ON um.user_id = u.ID
				WHERE um.meta_key = %s AND um.meta_value = %s",
				'_tutor_instructor_status',
				'approved'
			)
		);

		return $list;
	}

	/**
	 * Get email receiver list
	 *
	 * @since 2.5.0
	 *
	 * @param string $receiver_type receiver type.
	 * @param array  $course_ids course ids.
	 * @param bool   $count_only only count value or list.
	 *
	 * @return array|int
	 */
	public static function get_receiver_list( $receiver_type, $course_ids = array(), $count_only = false ) {
		global $wpdb;

		$list    = array();
		$ids_str = count( $course_ids ) ? QueryHelper::prepare_in_clause( $course_ids ) : "''";

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		switch ( $receiver_type ) {
			/**
			 * All user's related to tutor - students, instructors and admin
			 */
			case self::ALL:
				$admins      = self::get_admins();
				$students    = self::get_students();
				$instructors = self::get_instructors();

				$all = array_merge( $admins, $students, $instructors );
				// Unique list.
				$list = array_values( array_map( 'unserialize', array_unique( array_map( 'serialize', $all ) ) ) );
				break;

			case self::ALL_STUDENT:
				$list = self::get_students();
				break;

			case self::ALL_INSTRUCTOR:
				$list = self::get_instructors();
				break;

			case self::ALL_ADMIN:
				$list = self::get_admins();
				break;

			/**
			 * All instructor (with co-instructor) of including/excluding selected course
			 */
			case self::INSTRUCTORS_OF_COURSES:
			case self::INSTRUCTORS_EXCEPT_COURSES:
				$in_clause = self::INSTRUCTORS_OF_COURSES === $receiver_type ? "IN ({$ids_str})" : "NOT IN ({$ids_str})";

				$list = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT u.display_name, u.user_email
						FROM {$wpdb->users} u
						LEFT JOIN {$wpdb->usermeta} um1 ON um1.user_id = u.ID
						LEFT JOIN {$wpdb->usermeta} um2 ON um2.user_id = u.ID
						WHERE 
							(um1.meta_key = %s AND um1.meta_value = %s)
							AND
							(um2.meta_key = %s AND um2.meta_value {$in_clause})",
						'_tutor_instructor_status',
						'approved',
						'_tutor_instructor_course_id'
					)
				);
				break;

			/**
			 * All students of including/excluding selected course
			 */
			case self::STUDENTS_OF_COURSES:
			case self::STUDENTS_EXCEPT_COURSES:
				$in_clause = self::STUDENTS_OF_COURSES === $receiver_type ? "IN ({$ids_str})" : "NOT IN ({$ids_str})";

				$list = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT u.display_name,u.user_email 
						FROM {$wpdb->users} u
						LEFT JOIN {$wpdb->posts} p ON p.post_author = u.ID
						WHERE p.post_type = %s
						AND p.post_status = %s
						AND p.post_parent {$in_clause}",
						'tutor_enrolled',
						'completed'
					)
				);
				break;

			/**
			 * All students who are completed selected courses.
			 */
			case self::STUDENTS_COMPLETED_COURSES:
				$list = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT u.display_name,u.user_email 
						FROM {$wpdb->users} u
						LEFT JOIN {$wpdb->comments} c ON c.user_id = u.ID
						WHERE c.comment_type = %s
						AND c.comment_agent = %s
						AND c.comment_post_ID IN ({$ids_str})",
						'course_completed',
						'TutorLMSPlugin'
					)
				);
				break;

			/**
			 * All students who are completed any course.
			 */
			case self::STUDENTS_COMPLETED_ANY_COURSE:
				$list = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT u.display_name,u.user_email 
						FROM {$wpdb->users} u
						LEFT JOIN {$wpdb->comments} c ON c.user_id = u.ID
						WHERE c.comment_type = %s
						AND c.comment_agent = %s",
						'course_completed',
						'TutorLMSPlugin'
					)
				);
				break;
		}
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $count_only ? count( $list ) : $list;
	}

	/**
	 * Get receiver count help text.
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function get_receiver_count_help_text() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			$this->response_fail( tutor_utils()->error_message() );
		}

		$receiver_type = Input::post( 'receiver_type' );
		$course_ids    = Input::post( 'course_ids', array(), Input::TYPE_ARRAY );

		$count = self::get_receiver_list( $receiver_type, $course_ids, true );

		/* translators: %s: number of receiver. */
		$receiver_text = sprintf( _n( '%s receiver', '%s receivers', $count < 1 ? 1 : $count, 'tutor-pro' ), number_format_i18n( $count ) );
		/* translators: %s: receiver placeholder. */
		$message = sprintf( __( '%s found with selected criteria.', 'tutor-pro' ), $receiver_text );

		$this->response_data(
			array(
				'message' => $message,
				'count'   => $count,
			)
		);
	}

	/**
	 * Sent manual email
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function sent_manual_email() {
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			$this->response_fail( tutor_utils()->error_message() );
		}

		$mailer_data = get_option( self::OPTION_KEY );
		if ( false === $mailer_data ) {
			$this->response_fail( __( 'Required data not found to sent this email', 'tutor-pro' ) );
		}

		$receiver_type = $mailer_data['receiver_type'] ?? '';
		$course_ids    = $mailer_data['course_ids'] ?? array();

		$receiver_list = self::get_receiver_list( $receiver_type, $course_ids );

		if ( 0 === count( $receiver_list ) ) {
			$this->response_fail( __( 'No receiver found with selected criteria to sent this email', 'tutor-pro' ) );
		}

		$email_notification = new EmailNotification( false );

		$header        = 'Content-Type: ' . $email_notification->get_content_type() . "\r\n";
		$subject       = $mailer_data['email_subject'] ?? '';
		$email_heading = $mailer_data['email_heading'] ?? '';
		$email_body    = $mailer_data['email_body'] ?? '';
		$email_footer  = $mailer_data['email_footer'] ?? '';

		$placeholders['{testing_email_notice}'] = '';
		$placeholders['{current_year}']         = gmdate( 'Y' );
		$placeholders['{site_url}']             = get_bloginfo( 'url' );
		$placeholders['{site_name}']            = get_bloginfo( 'name' );

		$placeholders['{email_heading}'] = $email_notification->get_replaced_text( $email_heading, array_keys( $placeholders ), array_values( $placeholders ) );
		$placeholders['{email_body}']    = $email_notification->get_replaced_text( $email_body, array_keys( $placeholders ), array_values( $placeholders ) );
		$placeholders['{footer_text}']   = $email_notification->get_replaced_text( $email_footer, array_keys( $placeholders ), array_values( $placeholders ) );

		$subject    = $email_notification->get_replaced_text( $subject, array_keys( $placeholders ), array_values( $placeholders ) );
		$email_list = array_column( $receiver_list, 'user_email' );

		ob_start();
		$email_notification->tutor_load_email_template( 'mailer' );
		$email_tpl = ob_get_clean();

		$message = $email_notification->get_message( $email_tpl, array_keys( $placeholders ), array_values( $placeholders ) );

		$email_batch_number = time();
		$email_notification->send( $email_list, $subject, $message, $header, array(), true, $email_batch_number );

		$this->response_success( __( 'Successfully initiated the process of sending bulk emails based on your configured tutor email cron settings.', 'tutor-pro' ) );

	}
}
