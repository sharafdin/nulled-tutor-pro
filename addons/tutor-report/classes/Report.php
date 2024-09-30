<?php

/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR_REPORT;

use TUTOR\Backend_Page_Trait;
use \TUTOR_REPORT\Analytics;
use TUTOR\Tutor_Base;

class Report extends Tutor_Base
{

	/**
	 * Backend_Page_Trait for inherit common methods
	 * ex: Bulk actions
	 */
	use Backend_Page_Trait;

	public function __construct()
	{
		parent::__construct();

		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('tutor_admin_register', array($this, 'register_menu'));

		/**
		 * Ajax Action
		 */
		add_action('wp_ajax_treport_quiz_atttempt_delete', array($this, 'treport_quiz_atttempt_delete'));

		//Download CSV
		add_action('admin_init', array($this, 'download_course_enrol_csv'));

		add_action('admin_enqueue_scripts', array($this, 'load_backend_scripts'));

		/**
		 * Handle ajax request for total enrollment graph for backend
		 *
		 * @since v.2.0.0
		 */
		add_action('wp_ajax_tutor_report_get_student_enrollments', array(__CLASS__, 'total_enrolled_students'));

		/**
		 * Handle ajax request for bulk action
		 *
		 * @since v.2.0.0
		 */
		add_action('wp_ajax_tutor_admin_student_list_bulk_action', array($this, 'bulk_action'));
	}

	public function admin_scripts($page)
	{
		/**
		 * Scripts
		 */
		if ($page === 'tutor-lms-pro_page_tutor_report') {
			//wp_enqueue_style('tutor-report', TUTOR_REPORT()->url.'assets/css/report.css', array(), TUTOR_REPORT()->version);
			wp_enqueue_script('tutor-cahrt-js', TUTOR_REPORT()->url . 'assets/js/lib/Chart.bundle.min.js', array(), TUTOR_REPORT()->version);
			wp_enqueue_script('tutor-report', TUTOR_REPORT()->url . 'assets/js/report.js', array('tutor-admin'), TUTOR_REPORT()->version, true);
		}
	}

	public function register_menu()
	{
		add_submenu_page('tutor', __('Reports', 'tutor-pro'), __('Reports', 'tutor-pro'), 'manage_tutor', 'tutor_report', array($this, 'tutor_report'));
	}

	public function tutor_report()
	{
		include TUTOR_REPORT()->path . 'views/pages/report.php';
	}

	public function treport_quiz_atttempt_delete()
	{
		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html( tutor_utils()->error_message() ) );
		}
		global $wpdb;

		$attempt_id = (int) sanitize_text_field($_POST['attempt_id']);

		$wpdb->delete($wpdb->comments, array('comment_ID' => $attempt_id));
		$wpdb->delete($wpdb->commentmeta, array('comment_id' => $attempt_id));

		wp_send_json_success();
	}

	public function download_course_enrol_csv()
	{
		if (empty($_GET['tutor_report_action']) || $_GET['tutor_report_action'] !== 'download_course_enrol_csv') {
			return;
		}
		global $wpdb;

		$time_period = 'this_year';
		$course_id = false;
		if (!empty($_GET['time_period'])) {
			$time_period = sanitize_text_field($_GET['time_period']);
		}
		if (!empty($_GET['course_id'])) {
			$course_id = (int) sanitize_text_field($_GET['course_id']);
		}
		if (!empty($_GET['date_range_from']) && !empty($_GET['date_range_to'])) {
			$time_period = 'date_range';
		}

		$chartData = array();

		$single_course_query = '';
		if (!empty($_GET['course_id'])) {
			$course_id = (int) sanitize_text_field($_GET['course_id']);
			if ($course_id) {
				$single_course_query = "AND post_parent = {$course_id}";
			}
		}

		switch ($time_period) {
			case 'this_year';

				$currentYear = date('Y');

				$enrolledQuery = $wpdb->get_results("
	              SELECT COUNT(ID) as total_enrolled, 
	              MONTHNAME(post_date)  as month_name 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND YEAR(post_date) = {$currentYear} 
	              {$single_course_query}
	              GROUP BY MONTH (post_date) 
	              ORDER BY MONTH(post_date) ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$months = wp_list_pluck($enrolledQuery, 'month_name');
				$monthWiseEnrolled = array_combine($months, $total_enrolled);

				$emptyMonths = array();
				for ($m = 1; $m <= 12; $m++) {
					$emptyMonths[date('F', mktime(0, 0, 0, $m, 1, date('Y')))] = 0;
				}
				$chartData = array_merge($emptyMonths, $monthWiseEnrolled);

				break;
			case 'last_year';

				$lastYear = date('Y', strtotime('-1 year'));

				$enrolledQuery = $wpdb->get_results("
	              SELECT COUNT(ID) as total_enrolled, 
	              MONTHNAME(post_date)  as month_name 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND YEAR(post_date) = {$lastYear} 
	              {$single_course_query}
	              GROUP BY MONTH (post_date) 
	              ORDER BY MONTH(post_date) ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$months = wp_list_pluck($enrolledQuery, 'month_name');
				$monthWiseEnrolled = array_combine($months, $total_enrolled);

				$emptyMonths = array();
				for ($m = 1; $m <= 12; $m++) {
					$emptyMonths[date('F', mktime(0, 0, 0, $m, 1, date('Y')))] = 0;
				}
				$chartData = array_merge($emptyMonths, $monthWiseEnrolled);

				break;
			case 'last_month';

				$start_date = date("Y-m", strtotime('-1 month'));
				$start_date = $start_date . '-1';
				$end_date = date("Y-m-t", strtotime($start_date));

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_date);
				$end = new \DateTime($end_date . ' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results("
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_date}' AND '{$end_date}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount) {
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}

				break;
			case 'this_month';

				$start_week = date("Y-m-01");
				$end_week = date("Y-m-t");

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week . ' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results("
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount) {
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}

				break;
			case 'last_week';

				$previous_week = strtotime("-1 week +1 day");
				$start_week = strtotime("last sunday midnight", $previous_week);
				$end_week = strtotime("next saturday", $start_week);
				$start_week = date("Y-m-d", $start_week);
				$end_week = date("Y-m-d", $end_week);

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week . ' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results("
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount) {
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}


				break;
			case 'this_week';

				$start_week = date("Y-m-d", strtotime("last sunday midnight"));
				$end_week = date("Y-m-d", strtotime("next saturday"));
				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week . ' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results("
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount) {
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}

				break;
			case 'date_range';

				$start_week = sanitize_text_field($_GET['date_range_from']);
				$end_week = sanitize_text_field($_GET['date_range_to']);

				/**
				 * Format Date Name
				 */
				$begin = new \DateTime($start_week);
				$end = new \DateTime($end_week . ' + 1 day');
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);

				$datesPeriod = array();
				foreach ($period as $dt) {
					$datesPeriod[$dt->format("Y-m-d")] = 0;
				}

				$enrolledQuery = $wpdb->get_results("
	              SELECT COUNT(ID) as total_enrolled, 
	              DATE(post_date)  as date_format 
	              from {$wpdb->posts} 
	              WHERE post_type = 'tutor_enrolled' 
	              AND (post_date BETWEEN '{$start_week}' AND '{$end_week}')
	              {$single_course_query}
	              GROUP BY date_format
	              ORDER BY post_date ASC ;");

				$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
				$queried_date = wp_list_pluck($enrolledQuery, 'date_format');
				$dateWiseEnrolled = array_combine($queried_date, $total_enrolled);

				$chartData = array_merge($datesPeriod, $dateWiseEnrolled);
				foreach ($chartData as $key => $enrolledCount) {
					unset($chartData[$key]);
					$formatDate = date('d M', strtotime($key));
					$chartData[$formatDate] = $enrolledCount;
				}
				break;
		}

		$this->download_send_headers("tutor_report_course_enroll_" . date("Y-m-d") . ".csv");

		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys($chartData));
		fputcsv($df, $chartData);
		fclose($df);
		echo ob_get_clean();
		die();
	}

	public function download_send_headers($filename)
	{
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}

	/**
	 * Get total enrolled students
	 * handle ajax request
	 * 
	 * @since v2.0.0
	 */
	public static function total_enrolled_students()
	{
		$period 	= isset($_POST['period']) ? $_POST['period'] : '';
		$start_date = isset($_POST['start_date']) && '' !== $_POST['start_date'] ? tutor_get_formated_date('Y-m-d', $_POST['start_date']) : '';
		$end_date 	= isset($_POST['end_date']) && '' !== $_POST['end_date'] ? tutor_get_formated_date('Y-m-d', $_POST['end_date']) : '';

		$enrollments = Analytics::get_total_students_by_user(0, $period, $start_date, $end_date);
		wp_send_json($enrollments);
		exit;
	}

	/**
	 * Backend report css
	 *
	 * @since v2.0.0
	 */
	public function load_backend_scripts()
	{

		if (isset($_GET['page']) && $_GET['page'] === 'tutor_report') {
			wp_enqueue_style(
				'tutor-pro-analytics',
				TUTOR_REPORT()->url . 'assets/css/analytics.css',
				'',
				TUTOR_PRO_VERSION
			);
			wp_enqueue_style(
				'tutor-pro-report',
				TUTOR_REPORT()->url . 'assets/css/report.css',
				'',
				TUTOR_PRO_VERSION
			);

			wp_enqueue_script(
				'tutor-pro-analytics',
				TUTOR_REPORT()->url . 'assets/js/analytics.js',
				array('jquery'),
				TUTOR_PRO_VERSION,
				true
			);

			wp_add_inline_script(
				'tutor-pro-analytics',
				'const _tutor_analytics = ' . json_encode(self::chart_dependent_data()),
				'before'
			);
		}
	}

	/**
	 * Get chart data for admin overview page
	 *
	 * @since v2.0.0
	 * @return array
	 */
	public static function chart_dependent_data(): array
	{
		$time_period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '';
		$start_date  = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
		$end_date 	 = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
		if ('' !== $start_date) {
			$start_date = tutor_get_formated_date('Y-m-d', $start_date);
		}
		if ('' !== $end_date) {
			$end_date = tutor_get_formated_date('Y-m-d', $end_date);
		}
		$current_page 	= isset($_GET['page']) ? $_GET['page'] : '';
		$sub_page 		= isset($_GET['sub_page']) ? $_GET['sub_page'] : 'overview';

		/**
		 * If the sub_page is overview or it is course details page (admin side)
		 * then get graph data
		 *
		 * @since v2.0.0
		 */
		$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;
		if ('overview' === $sub_page || ('courses' === $sub_page && '' != $course_id)) {
			$overview_graph = array(
				array(
					'id'	=> 'ta_total_earnings',
					'label'	=> __('Earning', 'tutor-pro'),
					'data'	=> Analytics::get_earnings_by_user(0, $time_period, $start_date, $end_date, $course_id)['earnings']
				),
				array(
					'id'	=> 'ta_total_course_enrolled',
					'label'	=> __('Enrolled', 'tutor-pro'),
					'data'	=> Analytics::get_total_students_by_user(0, $time_period, $start_date, $end_date, $course_id)['enrollments']
				),
				array(
					'id'	=> 'ta_total_refund',
					'label'	=> __('Refund', 'tutor-pro'),
					'data'	=> Analytics::get_refunds_by_user(0, $time_period, $start_date, $end_date, $course_id)['refunds']
				),
				array(
					'id'	=> 'ta_total_discount',
					'label'	=> __('Discount', 'tutor-pro'),
					'data'	=> Analytics::get_discounts_by_user(0, $time_period, $start_date, $end_date, $course_id)['discounts']
				)
			);
			return $overview_graph;
		}
		return array();
	}

	/**
	 * Get sales list
	 *
	 * @param int $offset, to set offset in query | optional.
	 * @param int $limit, to get limited item | optional.
	 * @param string $course_id, to sort item course wise | optional.
	 * @param string $date, to sort item date wise | YYYY-MM-DD | optional.
	 * @param string $search, to sort item as course title | optional.
	 * @since v2.0.0
	 * @return array
	 */
	public static function sales_list(int $offset = 0, int $limit = 10, $course_id = '', $date = '', $order = '', $search = ''): array
	{
		global $wpdb;

		$offset     = sanitize_text_field($offset);
		$limit      = sanitize_text_field($limit);
		$course_id  = sanitize_text_field($course_id);
		$date       = sanitize_text_field($date);
		$order      = sanitize_sql_orderby($order);
		$search 	= sanitize_text_field($search);

		$search_term = '%' . $wpdb->esc_like($search) . '%';

		// Add course id in where clause.
		$course_query = '';
		if ('' !== $course_id) {
			$course_query = "AND course.ID = $course_id";
		}

		// Add date in where clause.
		$date_query = '';
		if ('' !== $date) {
			$date_query = "AND DATE(post.post_date) = CAST('$date' AS DATE) ";
		}

		// Order query
		$order_query = '';
		if ('' !== $order) {
			$order_query = "ORDER BY post.ID {$order}";
		} else {
			$order_query = "ORDER BY post.ID DESC";
		}

		$sales = $wpdb->get_results($wpdb->prepare(
			"SELECT post.ID, post.post_parent, post.post_author, post.post_status, post.post_date, meta.meta_value as order_id 
				FROM {$wpdb->posts} AS post
				INNER JOIN {$wpdb->posts} AS course 
					ON course.ID = post.post_parent
				JOIN {$wpdb->postmeta} meta 
					ON post.ID = meta.post_id
				WHERE meta.meta_key = %s 
					AND post.post_type = %s
					{$course_query}
					{$date_query}
					AND (course.post_title LIKE %s )
				{$order_query}
				LIMIT %d, %d
			",
			'_tutor_enrolled_by_order_id',
			'tutor_enrolled',
			$search_term,
			$offset,
			$limit
		));

		return array(
			'list'	=> $sales,
			'total'	=> self::count_total_sales($course_id, $date, $search)
		);
	}

	/**
	 * Count total sales item
	 *
	 * @since v2.0.0
	 * @return int
	 */
	public static function count_total_sales($course_id = '', $date = '', $search = ''): int
	{
		global $wpdb;
		$course_id  = sanitize_text_field($course_id);
		$date       = sanitize_text_field($date);
		$search 	= sanitize_text_field($search);
		$search_term = '%' . $wpdb->esc_like($search) . '%';

		$course_query = '';
		if ('' !== $course_id) {
			$course_query = "AND course.ID = $course_id";
		}
		// Add date in where clause.
		$date_query = '';
		if ('' !== $date) {
			$date_query = "AND DATE(post.post_date) = CAST('$date' AS DATE) ";
		}

		$total_items = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*)
				FROM {$wpdb->posts} AS post
				INNER JOIN {$wpdb->posts} AS course 
					ON course.ID = post.post_parent
				JOIN {$wpdb->postmeta} meta 
					ON post.ID = meta.post_id
				WHERE meta.meta_key = %s 
					AND post.post_type = %s
					{$course_query}
					{$date_query}
					AND (course.post_title LIKE %s )
			",
			'_tutor_enrolled_by_order_id',
			'tutor_enrolled',
			$search_term
		));
		return $total_items ? $total_items : 0;
	}

	/**
	 * Get available bulk actions for student list
	 *
	 * @since v.2.0.0
	 * @return array
	 */
	public function student_list_bulk_actions()
	{
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete()
		);
		return apply_filters('tutor_admin_student_list_bulk_action', $actions);
	}

	/**
	 * Handle bulk action
	 *
	 * @return json response
	 * @since v2.0.0
	 */
	public function bulk_action()
	{
		tutor_utils()->checking_nonce();
		$bulk_action = isset($_POST['bulk-action']) ? $_POST['bulk-action'] : '';
		$bulk_ids 	 = $_POST['bulk-ids'];

		if ('delete' === $bulk_action) {
			return self::delete_students($bulk_ids);
		}
		exit;
	}

	/**
	 * Delete student
	 *
	 * @param string $student_ids, ids that need to delete.
	 * @param int $reassign_id, reassign to other user.
	 * @return bool
	 * @since v2.0.0
	 */
	public static function delete_students(string $student_ids, $reassign_id = NULL): bool
	{
		$student_ids = explode(',', $student_ids);
		foreach ($student_ids as $id) {
			if (NULL === $reassign_id) {
				wp_delete_user($id);
			} else {
				wp_delete_user($id, $reassign_id);
			}
		}
		return true;
	}
}
