<?php
/**
 * Analytics for instructor
 *
 * @since 1.9.8
 */
namespace TUTOR_REPORT;

defined( 'ABSPATH' ) || exit;

use TUTOR_REPORT\ExportAnalytics;
class Analytics extends ExportAnalytics {
    /**
     * Resolve dependencies
     *
     * @since 1.9.8
     */
	public $current_page;

    public function __construct()
    {
		add_filter( 'tutor_dashboard/instructor_nav_items', array( $this, 'tutor_analytics_register_menu' ), 20 );
		add_filter( 'load_dashboard_template_part_from_other_location' , array( $this, 'tutor_analytics_frontend' ) ) ;

		/**
		 * Enqueue styles for analytics
		 *
		 * @since 1.9.8
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'tutor_analytics_scripts' ) );
		add_action( 'wp_ajax_view_progress', array( $this, 'view_progress' ) );
		add_action( 'wp_ajax_export_analytics', array( $this, 'export_analytics' ) );
    }

	/**
	 * Analytics frontend nav items
	 *
	 * @since 1.9.8
	 */
	public function tutor_analytics_register_menu($nav_items) {
		do_action( 'before_register_frontend_report_nav_item' );

		$nav_items['analytics'] = array(
			'title' => __('Analytics', 'tutor-pro'),
			'icon' => 'tutor-icon-chart-pie',
			'auth_cap'	=> tutor()->instructor_role,
		);
		return apply_filters( 'after_register_frontend_report_nav_item', $nav_items );
	}

	/**
	 * Load frontend report template
	 *
	 * @since 1.9.8
	 */
	public function tutor_analytics_frontend($template) {
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		if ( $query_vars['tutor_dashboard_page'] && $query_vars['tutor_dashboard_page'] === 'analytics' ) {
			//set current page
			if ( isset( $query_vars['tutor_dashboard_sub_page'] ) ) {
				$this->current_page = $query_vars['tutor_dashboard_sub_page'];
			} else {
				$this->current_page = 'overview';
			}
			$new_template = TUTOR_REPORT()->path.'templates/frontend_analytics.php';
			if ( file_exists($new_template) ) {
				return apply_filters( 'tutor_frontend_report_template', $new_template );
			}
		}
		return $template;
	}

	/**
	 * Analytics sub pages
	 *
	 * @return array
	 *
	 * @since 1.9.8
	 */
	public function sub_pages(): array {
		$sub_pages = array(
			'overview'      => array(
				'title' => __( 'Overview', 'tutor-pro' ),
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url().'analytics' )
			),
			'courses'       => array(
				'title' => __( 'Courses', 'tutor-pro' ),
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url().'analytics/courses' )
			),
			'earnings'      => array(
				'title' => __( 'Earnings', 'tutor-pro' ),
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url().'analytics/earnings')
			),
			'statements'      => array(
				'title' => __( 'Statements', 'tutor-pro' ),
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url().'analytics/statements')
			),
			'students'      => array(
				'title' => __( 'Students', 'tutor-pro' ),
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url().'analytics/students')
			),
			'export'      => array(
				'title' => __( 'Export', 'tutor-pro' ),
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url().'analytics/export')
			),
		);
		return $sub_pages;
	}

	/**
	 * Load sub page
	 *
	 * @param $page string
	 *
	 * @since 1.9.8
	 */
	public function load_sub_page(string $page) {
		$file = TUTOR_REPORT()->path.'templates/'.$page.'.php';
		if ( file_exists( $file ) ) {
			ob_start();
			include_once $file;
			return apply_filters( 'tutor-analytics-sub-page', ob_get_clean());
		} else {
			echo __( 'Content Not Found!', 'tutor-pro' );
		}
	}

	/**
	 * Get earnings of current user
	 *
	 * @param $period ( today | monthly| yearly ) required | string
	 *
	 * @return mixed
	 *
	 * @since 1.9.8
	 */
	public function get_total_earnings_by_period ( string $period, int $user_id ) {
		global $wpdb;
		$period 	= sanitize_text_field( $period );
		$user_id 	= sanitize_text_field( $user_id );

		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status) . "'";

		$period_filter = '';
		if ( 'today' === $period ) {
			$period_filter = "AND DATE(created_at) = CURDATE()";
		}
		if ( 'monthly' === $period ) {
			$period_filter = "AND MONTH(created_at) = MONTH(CURDATE()) ";
		}
		if ( 'yearly' === $period ) {
			$period_filter = "AND YEAR(created_at) = YEAR(CURDATE()) ";
		}

		$earnings = $wpdb->get_results( $wpdb->prepare(
			"SELECT instructor_amount
				FROM {$wpdb->prefix}tutor_earnings
				WHERE user_id = %d
					AND order_status IN({$complete_status})
					{$period_filter}
				ORDER BY created_at ASC;
			",
			$user_id
		) );

		return $earnings;
	}

	/**
	 * Get total enrolled number from all courses
	 *
	 * this method depends on get_courses_by_period method to
	 *
	 * get all courses of a user
	 *
	 * @return total count enrolled users | int
	 *
	 * @since 1.9.8
	 */
	public static function get_courses_with_search_by_user( int $user_id, string $search = '' ): int {
		global $wpdb;
		$user_id 	= sanitize_text_field( $user_id );
		$search 	= sanitize_text_field( $search );

		$course_post_type 	= tutor()->course_post_type;
		$search_term   		= '%' . $wpdb->esc_like( $search ) . '%';

		$total_item = $wpdb->get_var( $wpdb->prepare(
			"SELECT count(ID)
				FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_status = %s
				AND post_author = %d
				AND (post_title LIKE %s)
			",
			$course_post_type,
			'publish',
			$user_id,
			$search_term
		));

		return $total_item;
	}

	/**
	 * Get all courses of user, period wise ( today | monthly | yearly )
	 *
	 * @param $period | string required, @param $user_id | int required
	 *
	 * @return array of courses
	 *
	 * @since 1.9.8
	 */
	public static function get_courses_with_total_enroll_earning ( int $user_id, string $order = '', string $order_by ='', int $offset = 0, int $limit = 10, string $search = '' ) {
		global $wpdb;
		$post_type 	= tutor()->course_post_type;
		$user_id 	= sanitize_text_field( $user_id );
		$order 		= sanitize_text_field( $order );
		$order_by 	= sanitize_text_field( $order_by );
		$offset 	= sanitize_text_field( $offset );
		$limit 		= sanitize_text_field( $limit );
		$search 	= sanitize_text_field( $search );
		//if there is search then make offset 0 to get result
		if ( '' !== $search ) {
			$offset = 0;
		}
		$search_term     = '%' . $wpdb->esc_like( $search ) . '%';
		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status) . "'";

		$courses = $wpdb->get_results(
			$wpdb->prepare(
				" SELECT post.ID , post.post_title, post.guid, count(enrollment.ID) AS learner
					FROM {$wpdb->posts} AS post
						LEFT JOIN {$wpdb->posts} AS enrollment ON enrollment.post_parent = post.ID
							AND enrollment.post_type = %s
							AND enrollment.post_status = %s
					WHERE post.post_type = %s
						AND post.post_author = %d
						AND post.post_status = %s
						AND ( post.post_title LIKE %s)
					GROUP BY post.ID
					ORDER BY {$order_by} {$order}
					LIMIT %d, %d
				",
				"tutor_enrolled",
				"completed",
				$post_type,
				$user_id,
				'publish',
				$search_term,
				$offset,
				$limit
			)
		);
		return $courses;
	}

	/**
	 * Get total earning from a course for admin
	 *
	 * @param int $course_id | required.
	 * @return total sales
	 */
	public static function get_earnings_by_course( int $course_id ) {
		global $wpdb;

		$total_sales = 0;
		// if (!in_array('woocommerce_order_items', $wpdb->tables)) {
		// 	return $total_sales;
		// }

        $product_id = get_post_meta($course_id, '_tutor_course_product_id', true);
        if($product_id){
            $total_sales = $wpdb->get_var( "SELECT SUM( order_item_meta__line_total.meta_value) as order_item_amount
            FROM {$wpdb->posts} AS posts
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__line_total ON (order_items.order_item_id = order_item_meta__line_total.order_item_id)
                AND (order_item_meta__line_total.meta_key = '_line_total')
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__product_id_array ON order_items.order_item_id = order_item_meta__product_id_array.order_item_id
            WHERE posts.post_type IN ( 'shop_order' )
            AND posts.post_status IN ( 'wc-completed' ) AND ( ( order_item_meta__product_id_array.meta_key IN ('_product_id','_variation_id')
            AND order_item_meta__product_id_array.meta_value IN ('{$product_id}') ) );" );
        }
        if(function_exists('wc_price')){
            $total_sales = wc_price($total_sales);
        }
		return $total_sales;
	}

	/**
	 * Get earning by user_id, optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get earnings from date range
	 *
	 * If period or date range not pass then it will return all time earnings
	 *
	 * Optionally can set course_id for getting specific course data
	 *
	 * @param $user_id int | required
	 *
	 * @param $period string | optional
	 *
	 * @param $start_date string | optional | yy-mm-dd
	 *
	 * @param $end_date string | optional | yy-mm-dd
	 *
	 * @param $course_id int | optional
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public static function get_earnings_by_user( int $user_id, string $period = '', string $start_date = '', string $end_date = '', int $course_id = null ): array {
		global $wpdb;
		$user_id 	= sanitize_text_field( $user_id );
		$period 	= sanitize_text_field( $period );
		$start_date = sanitize_text_field( $start_date );
		$end_date 	= sanitize_text_field( $end_date );

		$period_query = '';
		$group_query  = " GROUP BY DATE(date_format) ";
		$course_query = '';
		//set additional query for period or date range if condition not meet then get all time data
		if ( '' !== $period ) {
			switch ( $period ) {
				case 'today':
					$period_query = " AND  DATE(created_at) = CURDATE() ";
					break;
				case 'monthly':
					$period_query = " AND  MONTH(created_at) = MONTH(CURDATE()) ";
					break;
				case 'yearly':
					$period_query = " AND  YEAR(created_at) = YEAR(CURDATE()) ";
					break;
				case 'last30days':
					$period_query = " AND  DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ";
					break;
				case 'last90days':
					$period_query = " AND  DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE() ";
					break;
				case 'last365days':
					$period_query = " AND  DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND CURDATE() ";
					break;
				default:
					break;
			}
		}

		if ( 'today' !== $period ) {
			$group_query  = " GROUP BY MONTH(date_format) ";
		}

		if ( $course_id ) {
			$course_query = " AND course_id = $course_id ";
		}

		if ( '' !== $start_date AND '' !== $end_date ) {
			$period_query = " AND  DATE(created_at) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
			$group_query  = " GROUP BY DATE(date_format) ";
		}

		/**
		 * Author query added to use same query for admin as well
		 * pass user_id value 0 to get all data for admin
		 *
		 * @since v2.0.0
		 */
		$author_query = '';
		if ( $user_id ) {
			$author_query = "AND user_id = {$user_id}";
		}
		// Get statuses
		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status) . "'";

		$amount_type = is_admin() ? 'admin_amount' : 'instructor_amount';
		$earnings = $wpdb->get_results(
			"SELECT  SUM($amount_type) AS total,
					DATE(created_at) AS date_format
			FROM	{$wpdb->prefix}tutor_earnings
			WHERE 	order_status IN({$complete_status})
					{$author_query}
					{$course_query}
					{$period_query}
					{$group_query}
			ORDER BY created_at ASC;
			"
		);

		$total_earnings = 0;
		foreach( $earnings as $earning ) {
			$total_earnings += $earning->total;
		}

		return array(
			'earnings' 		 => $earnings,
			'total_earnings' => $total_earnings
		);
	}

	/**
	 * Get total enrollment / students by user_id, optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get enrollment list from date range
	 *
	 * If period or date range not pass then it will return all time enrollment list
	 *
	 * @param $user_id int | required
	 *
	 * @param $period string | optional
	 *
	 * @param $start_date string | optional | yy-mm-dd
	 *
	 * @param $end_date string | optional | yy-mm-dd
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public static function get_total_students_by_user( int $user_id, string $period = '', $start_date = '', string $end_date = '', int $course_id = null ): array {
		global $wpdb;

		$course_post_type 	= tutor()->course_post_type;
		$user_id 			= sanitize_text_field( $user_id );
		$period 			= sanitize_text_field( $period );
		$start_date 		= sanitize_text_field( $start_date );
		$end_date 			= sanitize_text_field( $end_date );

		empty($period)	? $period = 'last30days' : 0;
		$period_query 	= '';
		$group_query 	= " GROUP BY DATE(date_format) ";

		//set additional query for period or date range
		if ( '' !== $period ) {
			switch ( $period ) {
				case 'today':
					$period_query = " AND  DATE(enrollment.post_date) = CURDATE() ";
					break;
				case 'monthly':
					$period_query = " AND  MONTH(enrollment.post_date) = MONTH(CURDATE()) ";
					break;
				case 'yearly':
					$period_query = " AND  YEAR(enrollment.post_date) = YEAR(CURDATE()) ";
					break;
				case 'last30days':
					$period_query = " AND  DATE(enrollment.post_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ";
					break;
				case 'last90days':
					$period_query = " AND  DATE(enrollment.post_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE() ";
					break;
				case 'last365days':
					$period_query = " AND  DATE(enrollment.post_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND CURDATE() ";
					break;
				default:
					break;
			}
		}
		// Period query.
		if ( 'today' !== $period ) {
			$group_query  = " GROUP BY MONTH(date_format) ";
		}

		if ( '' !== $start_date AND '' !== $end_date ) {
			$period_query = " AND  DATE(enrollment.post_date) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
			$group_query  = " GROUP BY DATE(date_format) ";
		}

		/**
		 * Author query added to use same query for admin as well
		 * pass user_id value 0 to get all data for admin
		 *
		 * @since v2.0.0
		 */
		$author_query = '';
		if ( $user_id ) {
			$author_query = "AND course.post_author = {$user_id}";
		}

		$course_query = '';
		if ( $course_id ) {
			$course_query = " AND course.ID = {$course_id} ";
		}
		$enrollments = $wpdb->get_results( $wpdb->prepare(
			"SELECT COUNT(enrollment.ID) AS total, enrollment.post_date AS date_format
			FROM 	{$wpdb->posts} enrollment
					INNER  JOIN {$wpdb->posts} course
							ON enrollment.post_parent=course.ID
			WHERE 	course.post_type = %s
					AND course.post_status = %s
					AND enrollment.post_type = %s
					AND enrollment.post_status = %s
					{$course_query}
					{$author_query}
					{$period_query}
					{$group_query}
			",
			$course_post_type,
			'publish',
			'tutor_enrolled',
			'completed'
		) );
		$total_enrollments = 0;
		foreach( $enrollments as $enroll ) {
			$total_enrollments += $enroll->total;
		}
		return array(
			'total_enrollments'	=> $total_enrollments,
			'enrollments'	=> $enrollments
		);
	}

	/**
	 * Get total refunds by user_id (instructor), optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get enrollment list from date range
	 *
	 * If period or date range not pass then it will return all time enrollment list
	 *
	 * @param $user_id int | required
	 *
	 * @param $period string | optional
	 *
	 * @param $start_date string | optional | yy-mm-dd
	 *
	 * @param $end_date string | optional | yy-mm-dd
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public static function get_refunds_by_user( int $user_id, string $period = '', $start_date = '', string $end_date = '', int $course_id = null): array {
		global $wpdb;
		$course_post_type = tutor()->course_post_type;
		$user_id 	      = sanitize_text_field( $user_id );
		$period 		  = sanitize_text_field( $period );
		$start_date 	  = sanitize_text_field( $start_date );
		$end_date 		  = sanitize_text_field( $end_date );

		$course_query = '';
		$period_query = '';
		$group_query  = " GROUP BY DATE(order_details.date_created) ";
		//set additional query for period or date range
		if ( '' !== $period ) {
			switch ( $period ) {
				case 'today':
					$period_query = " AND  DATE(order_details.date_created) = CURDATE() ";
					break;
				case 'monthly':
					$period_query = " AND  MONTH(order_details.date_created) = MONTH(CURDATE()) ";
					break;
				case 'yearly':
					$period_query = " AND  YEAR(order_details.date_created) = YEAR(CURDATE()) ";
					break;
				case 'last30days':
					$period_query = " AND  DATE(order_details.date_created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ";
					break;
				case 'last90days':
					$period_query = " AND  DATE(order_details.date_created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE() ";
					break;
				case 'last365days':
					$period_query = " AND  DATE(order_details.date_created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND CURDATE() ";
					break;
				default:
					break;
			}
		}
		//period query
		if ( 'today' !== $period ) {
			$group_query  = " GROUP BY MONTH(order_details.date_created) ";
		}

		if ( '' !== $start_date AND '' !== $end_date ) {
			$period_query = " AND  DATE(order_details.date_created) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
			$group_query  = " GROUP BY DATE(order_details.date_created) ";
		}

		if ( $course_id ) {
			$course_query = " AND post.ID = $course_id ";
		}

		/**
		 * Author query added to use same query for admin as well
		 * pass user_id value 0 to get all data for admin
		 *
		 * @since v2.0.0
		 */
		$author_query = '';
		if ( $user_id ) {
			$author_query = "AND post.post_author = {$user_id}";
		}

		/**
		 * If admin side then deduct instructor commission
		 * If front side then deduct admin commission
		 *
		 * @since v2.0.9
		 */
		$commission = (int) tutor_utils()->get_option( is_admin() ? 'earning_instructor_commission' : 'earning_admin_commission' );

		$refunds = $wpdb->get_results( $wpdb->prepare(
			"SELECT (SUM(order_details.total_sales) - SUM(order_details.total_sales) * $commission / 100 ) AS total, order_details.date_created AS date_format
				FROM {$wpdb->posts} AS post
					INNER JOIN {$wpdb->postmeta} as mt1 ON mt1.post_id = post.ID
					INNER JOIN {$wpdb->prefix}wc_order_product_lookup AS w_order ON w_order.product_id = mt1.meta_value
					INNER JOIN {$wpdb->prefix}wc_order_stats AS order_details ON order_details.order_id = w_order.order_id
				WHERE mt1.meta_key = %s
					AND post.post_type = %s
					AND post.post_status = %s
					AND order_details.status = %s
					{$author_query}
					{$course_query}
					{$period_query}
					{$group_query}",
			'_tutor_course_product_id',
			$course_post_type,
			'publish',
			'wc-refunded'
		));
		$total_refunds = 0;

		foreach( $refunds as $refund ) {
			$total_refunds += $refund->total;
		}

		return array(
			'refunds'		=> $refunds,
			'total_refunds'	=> $total_refunds
		);
	}

	/**
	 * Get total discounts by user_id (instructor), optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get enrollment list from date range
	 *
	 * If period or date range not pass then it will return all time enrollment list
	 *
	 * @param $user_id int | required
	 *
	 * @param $period string | optional
	 *
	 * @param $start_date string | optional | yy-mm-dd
	 *
	 * @param $end_date string | optional | yy-mm-dd
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public static function get_discounts_by_user( int $user_id, string $period = '', string $start_date = '', string $end_date = '',  int $course_id = null): array {
		global $wpdb;
		$course_post_type = tutor()->course_post_type;
		$user_id 		  = sanitize_text_field( $user_id );
		$period 		  = sanitize_text_field( $period );
		$start_date 	  = sanitize_text_field( $start_date );
		$end_date 		  = sanitize_text_field( $end_date );

		$period_query = '';
		$group_query  = " GROUP BY order_details.date_created ";
		$course_query = '';
		//set additional query for period or date range
		if ( '' !== $period ) {
			switch ( $period ) {
				case 'today':
					$period_query = " AND  DATE(order_details.date_created) = CURDATE() ";
					break;
				case 'monthly':
					$period_query = " AND  MONTH(order_details.date_created) = MONTH(CURDATE()) ";
					break;
				case 'yearly':
					$period_query = " AND  YEAR(order_details.date_created) = YEAR(CURDATE()) ";
					break;
				case 'last30days':
					$period_query = " AND  DATE(order_details.date_created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ";
					break;
				case 'last90days':
					$period_query = " AND  DATE(order_details.date_created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE() ";
					break;
				case 'last365days':
					$period_query = " AND  DATE(order_details.date_created) BETWEEN DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND CURDATE() ";
					break;
				default:
					break;
			}
		}

		//period query
		if ( 'today' !== $period ) {
			$group_query  = " GROUP BY MONTH(order_details.date_created) ";
		}

		if ( $course_id ) {
			$course_query = " AND  post.ID = $course_id ";
		}

		if ( '' !== $start_date AND '' !== $end_date ) {
			$period_query = " AND  DATE(order_details.date_created) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
			$group_query  = " GROUP BY DATE(order_details.date_created) ";
		}

		/**
		 * Author query added to use same query for admin as well
		 * pass user_id value 0 to get all data for admin
		 *
		 * @since v2.0.0
		 */
		$author_query = '';
		if ( $user_id ) {
			$author_query = "AND post.post_author = {$user_id}";
		}

		$discounts = $wpdb->get_results( $wpdb->prepare(
			"SELECT SUM(w_order.coupon_amount) AS total, order_details.date_created AS date_format
				FROM {$wpdb->posts} AS post
					INNER JOIN {$wpdb->postmeta} as mt1 ON mt1.post_id = post.ID
					INNER JOIN {$wpdb->prefix}wc_order_product_lookup AS w_order ON w_order.product_id = mt1.meta_value
					INNER JOIN {$wpdb->prefix}wc_order_stats AS order_details ON order_details.order_id = w_order.order_id
				WHERE  mt1.meta_key = %s
					AND post.post_type = %s
					AND post.post_status = %s
					AND order_details.status = %s
					{$author_query}
					{$course_query}
					{$period_query}
					{$group_query}",
			'_tutor_course_product_id',
			$course_post_type,
			'publish',
			'wc-completed'
		) );
		$total_discounts = 0;

		foreach( $discounts as $discount ) {
			$total_discounts += $discount->total;
		}

		return array(
			'discounts'			=> $discounts,
			'total_discounts'	=> $total_discounts
		);
	}

	/**
	 * Get total number of sales by user_id (instructor), optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get enrollment list from date range
	 *
	 * If period or date range not pass then it will return all time enrollment list
	 *
	 * @param $user_id int | required
	 *
	 * @param $period string | optional
	 *
	 * @param $start_date string | optional | yy-mm-dd
	 *
	 * @param $end_date string | optional | yy-mm-dd
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public static function number_of_sales( int $user_id, string $period, string $start_date, string $end_date): array {
		global $wpdb;

		$user_id 		  = sanitize_text_field( $user_id );
		$period 		  = sanitize_text_field( $period );
		$start_date 	  = sanitize_text_field( $start_date );
		$end_date 		  = sanitize_text_field( $end_date );

		$period_query = '';
		$group_query  = " GROUP BY DATE(date_format) ";
		$course_query = '';
		//set additional query for period or date range
		if ( '' !== $period ) {
			if ( 'today' === $period ) {
				$period_query = " AND  DATE(created_at) = CURDATE() ";
			} else if ( 'monthly' === $period ) {
				$period_query = " AND  MONTH(created_at) = MONTH(CURDATE()) ";
			} else {
				$period_query = " AND  YEAR(created_at) = YEAR(CURDATE()) ";
				$group_query  = " GROUP BY MONTH(date_format) ";
			}
		}

		//period query
		if ( '' === $period || 'yearly' ===  $period ) {
			$group_query  = " GROUP BY MONTH(date_format) ";
		}

		if ( '' !== $start_date AND '' !== $end_date ) {
			$period_query = " AND  DATE(created_at) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
			$group_query  = " GROUP BY DATE(date_format) ";
		}

		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status ) . "'";

		$sales = $wpdb->get_results( $wpdb->prepare(
			"SELECT COUNT(*) AS total, DATE(created_at) AS date_format
            	FROM {$wpdb->prefix}tutor_earnings
            	WHERE 	user_id = %d
					AND order_status IN({$complete_status})
					{$period_query}
					{$group_query}
			",
			$user_id
		) );

		// Count total sales
		$total_by_group = array_column($sales, 'total');
		$total_sale = array_sum($total_by_group);

		return array(
			'sales'			=> $sales,
			'total_sales'	=> $total_sale
		);
	}

	/**
	 * Get total number of sales by user_id (instructor), optionally can set period ( today | monthly| yearly )
	 *
	 * Optionally can set start date & end date to get enrollment list from date range
	 *
	 * If period or date range not pass then it will return all time enrollment list
	 *
	 * @param $user_id int | required
	 *
	 * @param $period string | optional
	 *
	 * @param $start_date string | optional | yy-mm-dd
	 *
	 * @param $end_date string | optional | yy-mm-dd
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public static function commission_fees_by_user( int $user_id, string $period, string $start_date, string $end_date): array {
		global $wpdb;

		$user_id 		  = sanitize_text_field( $user_id );
		$period 		  = sanitize_text_field( $period );
		$start_date 	  = sanitize_text_field( $start_date );
		$end_date 		  = sanitize_text_field( $end_date );

		$period_query = '';
		$group_query  = " GROUP BY DATE(date_format) ";

		//set additional query for period or date range
		if ( '' !== $period ) {
			if ( 'today' === $period ) {
				$period_query = " AND  DATE(created_at) = CURDATE() ";
			} else if ( 'monthly' === $period ) {
				$period_query = " AND  MONTH(created_at) = MONTH(CURDATE()) ";
			} else {
				$period_query = " AND  YEAR(created_at) = YEAR(CURDATE()) ";
				$group_query  = " GROUP BY MONTH(date_format) ";
			}
		}

		//period query
		if ( '' === $period || 'yearly' ===  $period ) {
			$group_query  = " GROUP BY MONTH(date_format) ";
		}

		if ( '' !== $start_date AND '' !== $end_date ) {
			$period_query = " AND  DATE(created_at) BETWEEN CAST('$start_date' AS DATE) AND CAST('$end_date' AS DATE) ";
			$group_query  = " GROUP BY DATE(date_format) ";
		}

		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status ) . "'";

		$commission_fees = $wpdb->get_results( $wpdb->prepare(
			"SELECT SUM(admin_amount) AS total, SUM(deduct_fees_amount) AS fees, DATE(created_at) AS date_format
            	FROM {$wpdb->prefix}tutor_earnings
            	WHERE 	user_id = %d
					AND order_status IN({$complete_status})
					{$period_query}
					{$group_query}
			",
			$user_id
		) );
		$total_commissions = 0;
		$total_fees		   = 0;
		$currency_symbol   = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';
		foreach( $commission_fees as $cf ) {
			$total_commissions += $cf->total;
			$total_fees += $cf->fees;
		}
		$total = 0;
		if ( $total_commissions) {
			$total = $total_commissions.'-'.$currency_symbol.$total_fees;
		}
		return array(
			'commission_fees' 	=> $commission_fees,
			'total'				=> $total,
		);
	}

	/**
	 * Get statements user_id (instructor)
	 *
	 *
	 * @param $user_id int | required
	 *
	 * @param $offset int | required
	 *
	 * @param $limit int | required
	 *
	 * @param $course_id string | optional
	 *
	 * @param $date_filter string | optional (yy-mm-dd)
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	public static function get_statements_by_user( int $user_id, int $offset, int $limit, $course_id = '', $date_filter = '' ): array {
		global $wpdb;
		$course_post_type = tutor()->course_post_type;

		$user_id 		= sanitize_text_field( $user_id );
		$limit 	 		= sanitize_text_field( $limit );
		$offset  		= sanitize_text_field( $offset );
		$course_id 		= sanitize_text_field( $course_id );
		$date_filter 	= sanitize_text_field( $date_filter );

		$course_query = '';
		if ( '' !== $course_id ) {
			$course_query = " AND course.ID = $course_id ";
		}

		$date_query = '';
		if ( '' !== $date_filter ) {
			$date_query = " AND DATE(statements.created_at) = CAST( '$date_filter' AS DATE ) ";
		}

		$statements = $wpdb->get_results( $wpdb->prepare(
			"SELECT statements.*, course.post_title AS course_title
				FROM {$wpdb->prefix}tutor_earnings AS statements
					INNER JOIN {$wpdb->posts} AS course ON course.ID = statements.course_id AND course.post_type = %s
				WHERE statements.user_id = %d
				{$course_query}
				{$date_query}
				ORDER BY statements.created_at DESC
				LIMIT %d, %d
			",
			$course_post_type,
			$user_id,
			$offset,
			$limit
		) );

		$total_statements = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*)
				FROM {$wpdb->prefix}tutor_earnings AS statements
					INNER JOIN {$wpdb->posts} AS course ON course.ID = statements.course_id AND course.post_type = %s
				WHERE statements.user_id = %d
				{$course_query}
				{$date_query}
			",
			$course_post_type,
			$user_id
		) );

		return array(
			'statements'		=> $statements,
			'total_statements'	=> $total_statements
		);
	}

	/**
	 * Get current user
	 *
	 * @return object
	 *
	 * @since 1.9.8
	 */
	public static function current_user() {
		$user = wp_get_current_user();
		return $user;
	}

	/**
	 * Enqueue styles
	 *
	 * @since 1.9.8
	 */
	public function tutor_analytics_scripts() {
		global $wp_query;
		$query_vars = $wp_query->query_vars;

		if ( isset( $query_vars['tutor_dashboard_page']) && $query_vars['tutor_dashboard_page'] == 'analytics' ) {
			$current_page   = isset( $query_vars['tutor_dashboard_sub_page'] ) ? $query_vars['tutor_dashboard_sub_page'] : 'overview';

				//enqueue scripts
				wp_enqueue_script(
					'tutor-pro-line-chart',
					TUTOR_REPORT()->url . 'assets/js/lib/Chart.bundle.min.js',
					array( ),
					TUTOR_PRO_VERSION
				);
				wp_enqueue_script(
					'tutor-pro-analytics',
					TUTOR_REPORT()->url . 'assets/js/analytics.js',
					array( 'jquery' ),
					TUTOR_PRO_VERSION,
					true
				);
				wp_add_inline_script(
					'tutor-pro-analytics',
					'const _tutor_analytics = '.json_encode(self::chart_dependent_data()),
					'before'
				);

				//export js
				if ( 'export' === $current_page ) {
					wp_enqueue_script(
						'tutor-pro-jszip',
						TUTOR_REPORT()->url . 'assets/js/lib/jszip.min.js',
						array( ),
						TUTOR_PRO_VERSION,
						true
					);
					wp_enqueue_script(
						'tutor-pro-file-saver',
						TUTOR_REPORT()->url . 'assets/js/FileSaver.min.js',
						array( 'jquery' ),
						TUTOR_PRO_VERSION,
						true
					);
					wp_enqueue_script(
						'tutor-pro-export',
						TUTOR_REPORT()->url . 'assets/js/export.js',
						array( 'jquery' ),
						TUTOR_PRO_VERSION,
						true
					);
				}

			//enqueue styles
			wp_enqueue_style(
				'tutor-pro-analytics',
				TUTOR_REPORT()->url.'assets/css/analytics.css',
				'',
				time()
			);

		}

	}

	/**
	 * Get dependent data to make chart
	 *
	 * It will return data as per query vars for Earnings | Enrollment list | Refunds | Discounts
	 *
	 * @return array
	 *
	 * @since 1.9.9
	 */
	protected static function chart_dependent_data() {
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		$user_id	= self::current_user()->ID;
		$analytics 	= array();

		if ( isset( $query_vars['tutor_dashboard_page']) && $query_vars['tutor_dashboard_page'] == 'analytics' ) {
			$time_period = isset( $_GET['period']) ? sanitize_text_field( $_GET['period'] ) : '';
			$start_date  = isset( $_GET['start_date']) ? sanitize_text_field( $_GET['start_date'] ) : '';
			$end_date 	 = isset( $_GET['end_date']) ? sanitize_text_field( $_GET['end_date'] ) : '';
			if ( '' !== $start_date ) {
				$start_date = tutor_get_formated_date( 'Y-m-d', $start_date);
			}
			if ( '' !== $end_date ) {
				$end_date = tutor_get_formated_date( 'Y-m-d', $end_date);
			}


			if ( !isset( $query_vars['tutor_dashboard_sub_page'] ) || ( isset( $query_vars['tutor_dashboard_sub_page'] ) && $query_vars['tutor_dashboard_sub_page'] === 'overview' ) ) {
				$analytics = array(
					array(
						'id'	=> 'ta_total_earnings',
						'label'	=> __( 'Earning', 'tutor-pro' ),
						'data'	=> self::get_earnings_by_user($user_id, $time_period, $start_date, $end_date)['earnings']
					),
					array(
						'id'	=> 'ta_total_course_enrolled',
						'label'	=> __( 'Enrolled', 'tutor-pro' ),
						'data'	=> self::get_total_students_by_user($user_id, $time_period, $start_date, $end_date)['enrollments']
					),
					array(
						'id'	=> 'ta_total_discount',
						'label'	=> __( 'Discount', 'tutor-pro' ),
						'data'	=> self::get_discounts_by_user($user_id, $time_period, $start_date, $end_date)['discounts']
					),
					array(
						'id'	=> 'ta_total_refund',
						'label'	=> __( 'Refund', 'tutor-pro' ),
						'data'	=> self::get_refunds_by_user($user_id, $time_period, $start_date, $end_date)['refunds']
					)
				);
			}

			//course details graph
			if ( isset( $query_vars['tutor_dashboard_sub_page'] ) && $query_vars['tutor_dashboard_sub_page'] === 'course-details' ) {
				$course_id = isset( $_GET['course_id'] ) ? $_GET['course_id'] : 0;
				$analytics = array(
					array(
						'id'     => 'ta_total_earnings',
						'label'  => __( 'Total Earning', 'tutor-pro' ),
						'data'   => self::get_earnings_by_user($user_id, $time_period, $start_date, $end_date, $course_id)['earnings']
					),
					array(
						'id'     => 'ta_total_discount',
						'label'  => __( 'Discount', 'tutor-pro' ),
						'data'   => self::get_discounts_by_user($user_id, $time_period, $start_date, $end_date, $course_id)['discounts']
					),
					array(
						'id'     => 'ta_total_refund',
						'label'  => __( 'Refund', 'tutor-pro' ),
						'data'   => self::get_refunds_by_user($user_id, $time_period, $start_date, $end_date, $course_id)['refunds']
					)
				);
			}
			//earning graph
			if ( isset( $query_vars['tutor_dashboard_sub_page'] ) && $query_vars['tutor_dashboard_sub_page'] === 'earnings' ) {
				$analytics = array(
					array(
						'id'     => 'ta_total_earnings',
						'label'  => __( 'Total Earning', 'tutor-pro' ),
						'data'   => self::get_earnings_by_user($user_id, $time_period, $start_date, $end_date)['earnings']
					),
					array(
						'id'     => 'ta_total_course_enrolled',
						'label'  => __( 'Number of Sales', 'tutor-pro' ),
						'data'   => self::number_of_sales($user_id, $time_period, $start_date, $end_date)['sales']
					),
					array(
						'id'     => 'ta_total_refund',
						'label'  => __( 'Commission', 'tutor-pro' ),
						'label2' => __( 'Fees', 'tutor-pro' ),
						'data'   => self::commission_fees_by_user($user_id, $time_period, $start_date, $end_date)['commission_fees']
					)
				);
			}

			return $analytics;
		}
		return $analytics;
	}

	public function view_progress() {
		tutor_utils()->checking_nonce();
		ob_start();
		include_once TUTOR_REPORT()->path.'templates/course_progress.php';
		echo ob_get_clean();
		exit;
	}

	/**
	 * Get analytics data for export based on current user
	 *
	 * @since 1.9.10
	 *
	 * @return array
	 */
	public function analytics_data():array {
		$arr =  array(
			'students'	=> $this->students_data(),
			'earnings'	=> $this->earnings_data(),
			'discounts'	=> $this->discounts_data(),
			'refunds'	=> $this->refunds_data(),
		);
		return $arr;
	}

	public function export_analytics() {
		tutor_utils()->checking_nonce();
		$arr =  $this->analytics_data();
		wp_send_json_success($arr);
	}

	/**
	 * Get last enrolled courses
	 *
	 * @param integer $limit
	 * @return mixed
	 * @since 2.0.2
	 */
	public static function get_last_enrolled_courses( int $limit = 5 ) {
		global $wpdb;
		
		$sql = "SELECT MAX(enrolled.post_date) as enrolled_time, enrolled.guid, enrolled.post_parent, course.ID, course.post_title
		FROM {$wpdb->posts} enrolled
		LEFT JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
		WHERE enrolled.post_type = %s 
		AND enrolled.post_status = %s 
		AND course.post_type = %s
		GROUP BY enrolled.post_parent
		ORDER BY enrolled_time DESC LIMIT 0,%d ;";

		return $wpdb->get_results( $wpdb->prepare( $sql, 'tutor_enrolled', 'completed', tutor()->course_post_type, $limit ) );
	}

	/**
	 * Get reviews
	 *
	 * @param integer $limit
	 * @return mixed
	 * @since 2.0.2
	 */
	public static function get_reviews( int $limit = 5 ){
		global $wpdb;

		$sql = "select {$wpdb->comments}.comment_ID,
		{$wpdb->comments}.comment_post_ID,
		{$wpdb->comments}.comment_author,
		{$wpdb->comments}.comment_author_email,
		{$wpdb->comments}.comment_date,
		{$wpdb->comments}.comment_content,
		{$wpdb->comments}.user_id,
		{$wpdb->commentmeta}.meta_value as rating,
		{$wpdb->users}.display_name
		FROM {$wpdb->comments}
		INNER JOIN {$wpdb->commentmeta}
		ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
		INNER  JOIN {$wpdb->users}
		ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
		AND meta_key = %s 
		ORDER BY comment_ID DESC LIMIT 0,%d ;";

		return $wpdb->get_results( $wpdb->prepare( $sql, 'tutor_rating', $limit ) );
	}

	/**
	 * Get students
	 *
	 * @param integer $limit
	 * @return mixed
	 * @since 2.0.2
	 */
	public static function get_students( int $limit = 5 ){
		global $wpdb;

		$sql = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->users}.* ,
		{$wpdb->usermeta}.meta_value as registered_timestamp
		FROM {$wpdb->users}
		INNER JOIN {$wpdb->usermeta}
		ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id )
		WHERE 1 = %d
		AND ( {$wpdb->usermeta}.meta_key = %s )
		ORDER BY {$wpdb->usermeta}.meta_value DESC
		LIMIT 0,%d ";

		return $wpdb->get_results( $wpdb->prepare( $sql, 1, '_is_tutor_student', $limit ) );
	}

	/**
	 * Get teachers
	 *
	 * @param integer $limit
	 * @return mixed
	 * @since 2.0.2
	 */
	public static function get_teachers( int $limit = 5 ){
		global $wpdb;

		$sql = "SELECT SQL_CALC_FOUND_ROWS {$wpdb->users}.* , meta_role.meta_value as registered_timestamp
		FROM {$wpdb->users}
		INNER JOIN {$wpdb->usermeta} meta_role ON ( {$wpdb->users}.ID = meta_role.user_id )
		INNER JOIN {$wpdb->usermeta} meta_status ON ( {$wpdb->users}.ID = meta_status.user_id )
		WHERE meta_role.meta_key = %s
			AND meta_status.meta_key = %s
			AND meta_status.meta_value = %s
		ORDER BY meta_role.meta_value DESC
		LIMIT 0,%d ";

		return $wpdb->get_results( $wpdb->prepare( $sql, '_is_tutor_instructor', '_tutor_instructor_status', 'approved', $limit ) );
	}

}