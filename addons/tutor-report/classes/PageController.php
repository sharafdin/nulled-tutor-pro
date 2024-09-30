<?php
/**
 * Report Page Controller
 *
 * @package TutorPro\Addons
 * @subpackage Reports
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR_REPORT;

use TUTOR\Input;
use TUTOR_REPORT\Report;
use TUTOR_REPORT\Analytics;

/**
 * Class PageController
 *
 * @since 2.0.5
 */
class PageController {

	/**
	 * Show overview page
	 *
	 * @return void
	 */
	public function handle_overview_page() {
		$course_post_type = tutor()->course_post_type;
		$lesson_type      = tutor()->lesson_post_type;

		$totalCourse         = tutor_utils()->get_total_course();
		$totalCourseEnrolled = tutor_utils()->get_total_enrolled_course();
		$totalLesson         = tutor_utils()->get_total_lesson();
		$totalQuiz           = tutor_utils()->get_total_quiz();
		$totalQuestion       = tutor_utils()->get_total_question();
		$totalInstructor     = tutor_utils()->get_total_instructors( $search_filter = '', array( 'approved' ) );
		$totalStudents       = tutor_utils()->get_total_students();
		$totalReviews        = tutor_utils()->get_total_review();

		$most_popular_courses  = tutor_utils()->most_popular_courses( $limit = 5 );
		$last_enrolled_courses = Analytics::get_last_enrolled_courses();
		$reviews               = Analytics::get_reviews();
		$students              = Analytics::get_students();
		$teachers              = Analytics::get_teachers();
		$questions             = tutor_utils()->get_qa_questions();

		$time_period = $active = Input::get( 'period', '' );
		$start_date  = Input::has( 'start_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'start_date' ) ) : '';
		$end_date    = Input::has( 'end_date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'end_date' ) ) : '';

		$add_30_days  = tutor_utils()->sub_days_with_today( '30 days' );
		$add_90_days  = tutor_utils()->sub_days_with_today( '90 days' );
		$add_365_days = tutor_utils()->sub_days_with_today( '365 days' );

		$current_frequency = Input::get( 'period', 'last30days' );
		$frequencies       = tutor_utils()->report_frequencies();

		include TUTOR_REPORT()->path . 'views/pages/overview.php';
	}

	/**
	 * Show single course report page
	 *
	 * @return void
	 */
	public function handle_single_course_page() {
		global $wpdb;
		$course_type = tutor()->course_post_type;

		// Single.
		$all_data = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} 
            WHERE post_type ='{$course_type}' 
            AND post_status = 'publish' "
		);

		$current_id = Input::has( 'course_id' ) ? Input::get( 'course_id' ) : ( isset( $all_data[0] ) ? $all_data[0]->ID : '' );

		$total_count = (int) $wpdb->get_var(
			"SELECT COUNT(ID) FROM {$wpdb->posts} 
            WHERE post_parent = {$current_id} 
            AND post_type = 'tutor_enrolled';"
		);

		$per_page     = 50;
		$total_items  = $total_count;
		$current_page = Input::get( 'paged', 0, Input::TYPE_INT );
		$start        = max( 0, ( $current_page - 1 ) * $per_page );

		$lesson_type = tutor()->lesson_post_type;

		$course_completed = $wpdb->get_results(
			"SELECT ID, post_author from {$wpdb->posts} 
            WHERE post_type = 'tutor_enrolled' 
            AND post_parent = {$current_id} 
            AND post_status = 'completed' 
            ORDER BY ID DESC LIMIT {$start},{$per_page};"
		);

		$complete_data = 0;
		$course_single = array();
		if ( is_array( $course_completed ) && ! empty( $course_completed ) ) {
			$complete = 0;
			foreach ( $course_completed as $data ) {
				$var             = array();
				$var['post_id']  = $current_id;
				$var['complete'] = tutor_utils()->is_completed_course( $current_id, $data->post_author );
				$var['user_id']  = $data->post_author;
				$course_single[] = $var;
				if ( $var['complete'] ) {
					$complete_data++;
				}
			}
		} else {
			$complete_data = 0;
		}

		$per_student   = tutor_utils()->get_option( 'pagination_per_page' );
		$student_page  = Input::get( 'lp', 0, Input::TYPE_INT );
		$start_student = max( 0, ( $student_page - 1 ) * $per_student );

		$student_items = tutils()->count_enrolled_users_by_course( $current_id );
		$student_list  = tutils()->get_students( $start_student, $per_student, '', $current_id );
		$instructors   = tutor_utils()->get_instructors_by_course( $current_id );

		$per_review    = tutor_utils()->get_option( 'pagination_per_page' );
		$review_page   = Input::get( 'rp', 0, Input::TYPE_INT );
		$review_start  = max( 0, ( $review_page - 1 ) * $per_review );
		$review_items  = tutor_utils()->get_course_reviews( $current_id, null, null, true );
		$total_reviews = tutor_utils()->get_course_reviews( $current_id, $review_start, $per_review );

		/**
		 * Query params
		 */
		$time_period = $active = Input::get( 'period', '' );
		$start_date  = Input::get( 'start_date', '' );
		$end_date    = Input::get( 'end_date', '' );
		if ( '' !== $start_date ) {
			$start_date = tutor_get_formated_date( 'Y-m-d', $start_date );
		}
		if ( '' !== $end_date ) {
			$end_date = tutor_get_formated_date( 'Y-m-d', $end_date );
		}
		$add_30_days  = tutor_utils()->sub_days_with_today( '30 days' );
		$add_90_days  = tutor_utils()->sub_days_with_today( '90 days' );
		$add_365_days = tutor_utils()->sub_days_with_today( '365 days' );

		$current_frequency = Input::get( 'period', 'last30days' );
		$frequencies       = tutor_utils()->report_frequencies();

		include TUTOR_REPORT()->path . 'views/pages/courses/course-single.php';
	}

	/**
	 * Show course table page
	 *
	 * @return void
	 */
	public function handle_course_table_page() {
		global $wpdb;
		$course_type = tutor()->course_post_type;

		/**
		 * Short able params
		 */
		$course_id     = Input::get( 'course-id', '' );
		$order_filter  = Input::get( 'order', 'DESC' );
		$date          = Input::get( 'date', '' );
		$search_filter = Input::get( 'search', '' );
		$category_slug = Input::get( 'category', '' );

		/**
		 * Determine active tab
		 */
		$active_tab = esc_html__( Input::get( 'data', 'all' ) );

		/**
		 * Pagination data
		 */
		$paged_filter = Input::get( 'paged', 1, Input::TYPE_INT );
		$limit        = tutor_utils()->get_option( 'pagination_per_page' );
		$offset       = ( $limit * $paged_filter ) - $limit;

		/**
		 * Navbar data to make nav menu
		 */
		$add_course_url = esc_url( admin_url( 'post-new.php?post_type=' . tutor()->course_post_type ) );

		/**
		 * Bulk action & filters
		 */
		$filters = array(
			'bulk_action'     => false,
			'filters'         => true,
			'category_filter' => true,
		);

		$args = array(
			'post_type'      => tutor()->course_post_type,
			'orderby'        => 'ID',
			'order'          => $order_filter,
			'paged'          => $paged_filter,
			'offset'         => $offset,
			'posts_per_page' => tutor_utils()->get_option( 'pagination_per_page' ),
		);

		/**
		 * For admin report course list will show only published course.
		 */
		$args['post_status'] = array( 'publish' );

		if ( 'mine' === $active_tab ) {
			$args['author'] = get_current_user_id();
		}

		$date_filter = Input::get( 'date', '' );

		$year  = date( 'Y', strtotime( $date_filter ) );
		$month = date( 'm', strtotime( $date_filter ) );
		$day   = date( 'd', strtotime( $date_filter ) );
		// Add date query.
		if ( '' !== $date_filter ) {
			$args['date_query'] = array(
				array(
					'year'  => $year,
					'month' => $month,
					'day'   => $day,
				),
			);
		}

		if ( '' !== $course_id ) {
			$args['p'] = $course_id;
		}
		// Add author param.
		if ( 'mine' === $active_tab ) {
			$args['author'] = get_current_user_id();
		}
		// Search filter.
		if ( '' !== $search_filter ) {
			$args['s'] = $search_filter;
		}
		// Category filter.
		if ( '' !== $category_slug ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'course-category',
					'field'    => 'slug',
					'terms'    => $category_slug,
				),
			);
		}

		$the_query = new \WP_Query( $args );

		$available_status = array(
			'publish' => __( 'Publish', 'tutor' ),
			'pending' => __( 'Pending', 'tutor' ),
			'draft'   => __( 'Draft', 'tutor' ),
		);

		include TUTOR_REPORT()->path . 'views/pages/courses/course-table.php';
	}

	/**
	 * Show reviews page
	 *
	 * @return void
	 */
	public function handle_review_page() {
		global $wpdb;

		$reviewsCount = (int) $wpdb->get_var(
			"SELECT COUNT(_review.comment_ID) 
            	FROM {$wpdb->comments} _review
            	INNER JOIN {$wpdb->commentmeta} _review_meta
            		ON _review.comment_ID = _review_meta.comment_id
            	INNER  JOIN {$wpdb->users}
            		ON _review.user_id = {$wpdb->users}.ID
				INNER JOIN {$wpdb->posts} _course
					ON _course.ID=_review.comment_post_ID
            AND meta_key = 'tutor_rating'
			AND _review.comment_approved IN ('hold', 'approved')"
		);

		$per_page     = tutor_utils()->get_option( 'pagination_per_page' );
		$total_items  = $reviewsCount;
		$current_page = Input::get( 'paged', 0, Input::TYPE_INT );
		$start        = max( 0, ( $current_page - 1 ) * $per_page );

		$course_query = '';
		if ( Input::has( 'course_id' ) ) {
			$course_id    = Input::get( 'course_id' );
			$course_query = 'AND _review.comment_post_ID =' . $course_id;
		}
		$user_query = '';
		if ( Input::has( 'user_id' ) ) {
			$user_id    = Input::get( 'user_id' );
			$user_query = 'AND _review.user_id =' . $user_id;
		}

		$reviews = $wpdb->get_results(
			"SELECT _review.comment_ID, 
				_review.comment_post_ID, 
				_review.comment_author, 
				_review.comment_author_email, 
				_review.comment_date, 
				_review.comment_content, 
				_review.comment_approved AS comment_status, 
				_review.user_id, 
				_review_meta.meta_value as rating,
				{$wpdb->users}.display_name 
            FROM {$wpdb->comments} _review
            	INNER JOIN {$wpdb->commentmeta} _review_meta
            		ON _review.comment_ID = _review_meta.comment_id {$course_query} {$user_query}
            	INNER  JOIN {$wpdb->users}
            		ON _review.user_id = {$wpdb->users}.ID
				INNER JOIN {$wpdb->posts} _course
					ON _course.ID=_review.comment_post_ID
            AND meta_key = 'tutor_rating' 
			AND _review.comment_approved IN ('hold', 'approved')
			ORDER BY comment_date 
			DESC LIMIT {$start},{$per_page} ;"
		);

		include TUTOR_REPORT()->path . 'views/pages/reviews/reviews-page.php';
	}

	/**
	 * Show student table listing page
	 *
	 * @return void
	 */
	public function handle_student_table_page() {
		$report = new Report();

		$course_id = Input::get( 'course-id', '' );
		$order     = Input::get( 'order', 'DESC' );
		$date      = Input::get( 'date', '' );
		$search    = Input::get( 'search', '' );

		$current_page  = Input::get( 'paged', 1, Input::TYPE_INT );
		$item_per_page = tutor_utils()->get_option( 'pagination_per_page' );
		$offset        = ( $item_per_page * $current_page ) - $item_per_page;

		$sales_list  = tutor_utils()->get_students_by_instructor( 0, $offset, $item_per_page, $search, $course_id, $date, $order_by = '', $order );
		$lists       = $sales_list['students'];
		$total_items = $sales_list['total_students'];

		$filters          = array(
			'bulk_action'   => true,
			'bulk_actions'  => $report->student_list_bulk_actions(),
			'ajax_action'   => 'tutor_admin_student_list_bulk_action',
			'filters'       => true,
			'course_filter' => true,
		);
		$filters_template = tutor()->path . 'views/elements/filters.php';

		include TUTOR_REPORT()->path . 'views/pages/students/student-table.php';
	}

	/**
	 * Show student details page
	 *
	 * @return void
	 */
	public function handle_student_profile_page() {
		global $wpdb;

		$student_id = Input::get( 'student_id', '' );
		if ( '' === $student_id ) {
			die( esc_html_e( 'Invalid student id', 'tutor-pro' ) );
		}

		$user_info       = get_userdata( $student_id );
		$enrolled_course = tutor_utils()->get_enrolled_courses_by_user( $user_info->ID );

		// Review List.
		$count         = 0;
		$item_per_page = tutor_utils()->get_option( 'pagination_per_page' );
		$review_page   = Input::get( 'rp', 0, Input::TYPE_INT );
		$offset        = max( 0, ( $review_page - 1 ) * $item_per_page );
		$reviews       = tutor_utils()->get_reviews_by_user( $user_info->ID );

		$total_discussion = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(comment_ID) FROM {$wpdb->comments}
            WHERE comment_author = %s AND comment_type = 'tutor_q_and_a'",
				$user_info->user_login
			)
		);

		$total_assignments = 0;
		$assignment_ids    = array();
		if ( ! empty( $courses_id ) ) {
			$assignment_ids    = tutor_utils()->get_course_content_ids_by( 'tutor_assignments', tutor()->course_post_type, $courses_id );
			$total_assignments = count( $assignment_ids );
		}

		// Total lesson
		$lesson     = 0;
		$courses_id = tutor_utils()->get_enrolled_courses_ids_by_user( $user_info->ID );
		foreach ( $courses_id as $course ) {
			$lesson += tutor_utils()->get_lesson_count_by_course( $course );
		}

		include TUTOR_REPORT()->path . 'views/pages/students/student-profile.php';
	}

	/**
	 * Show sales tab page
	 *
	 * @return void
	 */
	public function handle_sales_page() {
		$course_id = Input::get( 'course-id', '' );
		$order     = Input::get( 'order', 'DESC' );
		$date      = Input::get( 'date', '' );
		$search    = Input::get( 'search', '' );

		$current_page  = Input::get( 'paged', 1, Input::TYPE_INT );
		$item_per_page = tutor_utils()->get_option( 'pagination_per_page' );
		$offset        = ( $item_per_page * $current_page ) - $item_per_page;

		$sales_list  = Report::sales_list( $offset, $item_per_page, $course_id, $date, $order, $search );
		$lists       = $sales_list['list'];
		$total_items = $sales_list['total'];

		$filters = array(
			'bulk_action'   => false,
			'filters'       => true,
			'course_filter' => true,
		);

		$filters_template = tutor()->path . 'views/elements/filters.php';

		include TUTOR_REPORT()->path . 'views/pages/sales/sales-page.php';
	}
}
