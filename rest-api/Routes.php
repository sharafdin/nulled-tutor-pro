<?php
/**
 * Register Routes
 *
 * @package TutorPro\RestAPI
 * @author  Themum<support@themeum.com>
 * @link    https://themeum.com
 * @since   2.6.0
 */

namespace TutorPro\RestAPI;

use TUTOR\RestAuth;
use TutorPro\RestAPI\Controllers\CourseController;
use TutorPro\RestAPI\Controllers\LessonController;
use TutorPro\RestAPI\Controllers\TopicController;
use TutorPro\RestAPI\Controllers\AssignmentController;
use TutorPro\RestAPI\Controllers\EnrollmentController;
use TutorPro\RestAPI\Controllers\QAndAController;
use TutorPro\RestAPI\Controllers\QuizAttemptController;
use TutorPro\RestAPI\Controllers\QuizController;
use TutorPro\RestAPI\Controllers\QuizQuestionController;
use TutorPro\RestAPI\Controllers\ReviewController;
use TutorPro\RestAPI\Controllers\UserProfileController;
use TutorPro\RestAPI\Controllers\StudentController;
use TutorPro\RestAPI\Controllers\WishlistController;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Register supported routes
 */
class Routes {

	/**
	 * Route namespace
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public static $route_namespace = 'tutor/v1';

	/**
	 * Register hooks
	 *
	 * @since 2.6.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', __CLASS__ . '::register_routes', 100 );
		add_filter( 'tutor_rest_api_permissions', __CLASS__ . '::add_permissions' );
	}

	/**
	 * Register the available routes
	 *
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public static function register_routes() {
		foreach ( self::endpoints() as $endpoint ) {
			// Set default args.
			$endpoint_args = array(
				'methods'             => $endpoint['method'],
				'callback'            => $endpoint['callback'],
				'permission_callback' => $endpoint['permission_callback'],
			);

			// Set endpoint validation args if available.
			if ( isset( $endpoint['args'] ) ) {
				$endpoint_args['args'] = $endpoint['args'];
			}

			$url_params = isset( $endpoint['url_params'] ) ? $endpoint['url_params'] : '';
			$url        = isset( $endpoint['endpoint'] ) ? $endpoint['endpoint'] : '';

			register_rest_route(
				self::$route_namespace,
				$url . $url_params,
				$endpoint_args
			);
		}
	}

	/**
	 * Get available endpoints
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	public static function endpoints() {
		$course_controller        = new CourseController();
		$topic_controller         = new TopicController();
		$lesson_controller        = new LessonController();
		$assignment_controller    = new AssignmentController();
		$quiz_controller          = new QuizController();
		$quiz_question_controller = new QuizQuestionController();
		$enrollment_controller    = new EnrollmentController();
		$quiz_attempt_controller  = new QuizAttemptController();
		$q_and_a_controller       = new QAndAController();
		$review_controller        = new ReviewController();
		$wishlist_controller      = new WishlistController();
		$user_profile_controller  = new UserProfileController();
		$student_controller       = new StudentController();

		return array(
			array(
				'endpoint'            => 'courses',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $course_controller, 'create' ),
				'permission_callback' => array( $course_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'courses',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $course_controller, 'update' ),
				'permission_callback' => array( $course_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'courses',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $course_controller, 'delete' ),
				'permission_callback' => array( $course_controller, 'validate_delete_request' ),
			),
			array(
				'endpoint'            => 'course-mark-complete',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $course_controller, 'course_mark_complete' ),
				'permission_callback' => array( $course_controller, 'validate_write_request' ),
			),
			// Topic routes.
			array(
				'endpoint'            => 'topics',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $topic_controller, 'create' ),
				'permission_callback' => array( $topic_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'topics',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $topic_controller, 'update' ),
				'permission_callback' => array( $topic_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'topics',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $topic_controller, 'delete' ),
				'permission_callback' => array( $topic_controller, 'validate_delete_request' ),
			),
			// Lesson routes.
			array(
				'endpoint'            => 'lessons',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $lesson_controller, 'create' ),
				'permission_callback' => array( $lesson_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'lessons',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $lesson_controller, 'update' ),
				'permission_callback' => array( $lesson_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'lessons',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $lesson_controller, 'delete' ),
				'permission_callback' => array( $lesson_controller, 'validate_delete_request' ),
			),
			array(
				'endpoint'            => 'lesson-mark-complete',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $lesson_controller, 'lesson_mark_complete' ),
				'permission_callback' => array( $lesson_controller, 'validate_write_request' ),
			),
			// Assignment routes.
			array(
				'endpoint'            => 'assignments',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $assignment_controller, 'create' ),
				'permission_callback' => array( $assignment_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'assignments',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $assignment_controller, 'update' ),
				'permission_callback' => array( $assignment_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'assignments',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $assignment_controller, 'delete' ),
				'permission_callback' => array( $assignment_controller, 'validate_delete_request' ),
			),
			array(
				'endpoint'            => 'assignments',
				'url_params'          => '',
				'method'              => WP_REST_Server::READABLE,
				'callback'            => array( $assignment_controller, 'get_student_assignment' ),
				'permission_callback' => array( $assignment_controller, 'validate_read_request' ),
			),
			array(
				'endpoint'            => 'assignment-submit',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $assignment_controller, 'student_assignment_submit' ),
				'permission_callback' => array( $assignment_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'assignment-submit',
				'url_params'          => '/(?P<submission_id>\d+)',
				'args'                => array(
					'submission_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $assignment_controller, 'student_assignment_update' ),
				'permission_callback' => array( $assignment_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'assignment-attachment',
				'url_params'          => '/(?P<submission_id>\d+)',
				'args'                => array(
					'submission_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $assignment_controller, 'delete_attachment' ),
				'permission_callback' => array( $assignment_controller, 'validate_delete_request' ),
			),
			// Quiz routes.
			array(
				'endpoint'            => 'quizzes',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $quiz_controller, 'create' ),
				'permission_callback' => array( $quiz_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'quizzes',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $quiz_controller, 'update' ),
				'permission_callback' => array( $quiz_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'quizzes',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $quiz_controller, 'delete' ),
				'permission_callback' => array( $quiz_controller, 'validate_delete_request' ),
			),
			array(
				'endpoint'            => 'quiz-questions',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $quiz_question_controller, 'create' ),
				'permission_callback' => array( $quiz_question_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'quiz-questions',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $quiz_question_controller, 'update' ),
				'permission_callback' => array( $quiz_question_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'quiz-questions',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $quiz_question_controller, 'delete' ),
				'permission_callback' => array( $quiz_question_controller, 'validate_delete_request' ),
			),
			array(
				'endpoint'            => 'quiz-attempts',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $quiz_attempt_controller, 'create' ),
				'permission_callback' => array( $quiz_attempt_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'quiz-attempts',
				'args'                => array(
					'quiz_id'    => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
					'student_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::READABLE,
				'callback'            => array( $quiz_attempt_controller, 'read' ),
				'permission_callback' => array( $quiz_attempt_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'quiz-attempts',
				'url_params'          => '/(?P<attempt_id>\d+)',
				'args'                => array(
					'attempt_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::READABLE,
				'callback'            => array( $quiz_attempt_controller, 'read_one' ),
				'permission_callback' => array( $quiz_attempt_controller, 'validate_write_request' ),
			),
			// Enrollments.
			array(
				'endpoint'            => 'enrollments',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $enrollment_controller, 'do_enrollment' ),
				'permission_callback' => array( $enrollment_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'enrollments',
				'url_params'          => '/(?P<status>[a-zA-Z]+)',
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $enrollment_controller, 'update_enrollment' ),
				'permission_callback' => array( $enrollment_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'enrollments',
				'method'              => WP_REST_Server::READABLE,
				'args'                => array(
					'course_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'callback'            => array( $enrollment_controller, 'get_enrollment_list' ),
				'permission_callback' => array( $enrollment_controller, 'validate_read_request' ),
			),
			// Q&A.
			array(
				'endpoint'            => 'qna',
				'method'              => WP_REST_Server::READABLE,
				'callback'            => array( $q_and_a_controller, 'list' ),
				'permission_callback' => array( $q_and_a_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'qna',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $q_and_a_controller, 'create' ),
				'permission_callback' => array( $q_and_a_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'qna',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $q_and_a_controller, 'delete' ),
				'permission_callback' => array( $q_and_a_controller, 'validate_delete_request' ),

			),
			// Reviews.
			array(
				'endpoint'            => 'reviews',
				'url_params'          => '',
				'method'              => WP_REST_Server::READABLE,
				'callback'            => array( $review_controller, 'list' ),
				'permission_callback' => array( $review_controller, 'validate_read_request' ),
			),
			array(
				'endpoint'            => 'reviews',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $review_controller, 'create' ),
				'permission_callback' => array( $review_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'reviews',
				'url_params'          => '/(?P<review_id>\d+)',
				'args'                => array(
					'review_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $review_controller, 'update' ),
				'permission_callback' => array( $review_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'reviews',
				'url_params'          => '/(?P<review_id>\d+)',
				'args'                => array(
					'review_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $review_controller, 'delete' ),
				'permission_callback' => array( $review_controller, 'validate_delete_request' ),
			),
			array(
				'endpoint'            => 'qna-mark-read-unread',
				'url_params'          => '/(?P<id>\d+)',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $q_and_a_controller, 'mark_read_unread' ),
				'permission_callback' => array( $q_and_a_controller, 'validate_write_request' ),

			),
			// Wishlist.
			array(
				'endpoint'            => 'wishlists',
				'url_params'          => '',
				'method'              => WP_REST_Server::READABLE,
				'callback'            => array( $wishlist_controller, 'get_wishlist' ),
				'permission_callback' => array( $wishlist_controller, 'validate_read_request' ),
			),
			array(
				'endpoint'            => 'wishlists',
				'url_params'          => '',
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $wishlist_controller, 'create' ),
				'permission_callback' => array( $wishlist_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'wishlists',
				'url_params'          => '',
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $wishlist_controller, 'delete' ),
				'permission_callback' => array( $wishlist_controller, 'validate_delete_request' ),
			),
			// Student.
			array(
				'endpoint'            => 'students',
				'url_params'          => '/(?P<user_id>\d+)/(?P<sub_resource>[a-zA-Z-]+)',
				'args'                => array(
					'user_id'      => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
					'sub_resource' => array(
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),

				'method'              => WP_REST_Server::READABLE,
				'callback'            => array( $student_controller, 'get' ),
				'permission_callback' => array( $student_controller, 'validate_read_request' ),

			),
			// User Profile.
			array(
				'endpoint'            => 'profile',
				'url_params'          => '/(?P<user_id>\d+)',
				'method'              => WP_REST_Server::READABLE,
				'args'                => array(
					'user_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),
				),
				'callback'            => array( $user_profile_controller, 'get_user_profile' ),
				'permission_callback' => array( $user_profile_controller, 'validate_read_request' ),
			),
			array(
				'endpoint'            => 'update-profile',
				'url_params'          => '',
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $user_profile_controller, 'update_user_profile' ),
				'permission_callback' => array( $user_profile_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'upload-photo',
				'url_params'          => '/(?P<photo_type>[a-z-A-Z]+)',
				'args'                => array(
					'photo_type' => array(
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
				),
				'method'              => WP_REST_Server::CREATABLE,
				'callback'            => array( $user_profile_controller, 'set_profile_photo' ),
				'permission_callback' => array( $user_profile_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'update-password',
				'url_params'          => '',
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $user_profile_controller, 'update_user_password' ),
				'permission_callback' => array( $user_profile_controller, 'validate_write_request' ),
			),
			array(
				'endpoint'            => 'delete-photo',
				'url_params'          => '/(?P<photo_type>[a-z-A-Z]+)/(?P<user_id>\d+)',
				'args'                => array(
					'photo_type' => array(
						'validate_callback' => function ( $param ) {
							return is_string( $param );
						},
					),
					'user_id'    => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),

				),
				'method'              => WP_REST_Server::DELETABLE,
				'callback'            => array( $user_profile_controller, 'remove_profile_photo' ),
				'permission_callback' => array( $user_profile_controller, 'validate_delete_request' ),
			),
			// Become Instructor.
			array(
				'endpoint'            => 'become-instructor',
				'url_params'          => '/(?P<user_id>\d+)',
				'args'                => array(
					'user_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && absint( $param ) > 0;
						},
					),

				),
				'method'              => WP_REST_Server::EDITABLE,
				'callback'            => array( $student_controller, 'apply_for_instructor' ),
				'permission_callback' => array( $student_controller, 'validate_write_request' ),
			),
		);
	}

	/**
	 * Add pro permission for the REST API
	 *
	 * @since 2.6.0
	 *
	 * @param array $default_permissions default permissions.
	 *
	 * @return array
	 */
	public static function add_permissions( $default_permissions ) {
		$permissions = array(
			array(
				'value' => RestAuth::WRITE,
				'label' => __( 'Write', 'tutor' ),
			),
			array(
				'value' => RestAuth::READ_WRITE,
				'label' => __( 'Read/Write', 'tutor' ),
			),
			array(
				'value' => RestAuth::DELETE,
				'label' => __( 'Delete', 'tutor' ),
			),
			array(
				'value' => RestAuth::ALL,
				'label' => __( 'All', 'tutor' ),
			),
		);

		return array_merge( $default_permissions, $permissions );
	}
}
