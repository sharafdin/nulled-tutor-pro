<?php
/**
 * Assignment List
 *
 * @package TutorPro/Addons
 * @subpackage Assignment
 * @author: themeum
 * @author Themeum <support@themeum.com>
 * @since 1.0.0
 */

namespace TUTOR_ASSIGNMENTS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Backend_Page_Trait;

/**
 * Assignments List class
 */
class Assignments_List {

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */
	use Backend_Page_Trait;

	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->page_title = __( 'Assignments', 'tutor-pro' );
	}

	/**
	 * Total points
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_mark( $item ) {
		echo (int) tutor_utils()->get_assignment_option( $item->comment_post_ID, 'total_mark' );
	}

	/**
	 * Passing mark
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_passing_mark( $item ) {
		echo (int) tutor_utils()->get_assignment_option( $item->comment_post_ID, 'pass_mark' );
	}

	/**
	 * Student
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_student( $item ) {
		echo '<div class="tutor-d-flex tutor-align-center">';
		echo tutor_utils()->get_tutor_avatar( $item->user_id, 'sm' ); //phpcs:ignore
		echo '<div class="tutor-ml-12">' . esc_html( $item->comment_author ) . '</div>';
		echo '</div>';
	}

	/**
	 * Assignment title.
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_title( $item ) {
		echo '<a href="' . esc_url( get_the_permalink( $item->comment_post_ID ) ) . '">' . esc_html( get_the_title( $item->comment_post_ID ) ) . '</a>';
		echo '<div class="tutor-fs-7 tutor-fw-normal tutor-color-secondary tutor-mt-8">
				<strong class="tutor-fs-7 tutor-fw-medium">' . esc_html__( 'Course', 'tutor-pro' ) . ': </strong>' . esc_html( $item->post_title ) .
			'</div>';
	}

	/**
	 * Duration
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_duration( $item ) {
		$value = tutor_utils()->get_assignment_option( $item->comment_post_ID, 'time_duration.value' );
		$time  = tutor_utils()->get_assignment_option( $item->comment_post_ID, 'time_duration.time' );
		$time  = trim( $time, 's' );

		echo $value ? ( $value . ' ' . __( $time, 'tutor-pro' ) . ( $value > 1 ? 's' : '' ) ) : __( 'No Limit', 'tutor-pro' );//phpcs:ignore
	}

	/**
	 * Date
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_date( $item ) {
		$format      = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$deadline    = tutor_utils()->get_assignment_deadline_date( $item->comment_post_ID, $format );

		// Deadline.
		if ( $deadline ) {
			?>
			<div class="course-meta" style="margin-top:0">
				<span class="tutor-color-black tutor-fs-7">
					<strong><?php esc_html_e( 'Deadline', 'tutor-pro' ); ?></strong>
					<?php echo esc_html( $deadline ); ?>
				</span>
			</div>
			<?php
		}
		?>
		<div class="course-meta" style="margin-top:0">
			<span class="tutor-color-secondary tutor-fs-7"> 
				<?php esc_html_e( 'Started', 'tutor-pro' ); ?>
				<?php
					echo esc_html( tutor_utils()->convert_date_into_wp_timezone( $item->comment_date_gmt ) );
				?>
			</span>
		</div>
		<?php
	}

	/**
	 * Evaluate action.
	 *
	 * @param object $item item.
	 * @param int    $post_id post id.
	 *
	 * @return void
	 */
	public function column_action_evaluate( $item, $post_id ) {
		$evaluated   = get_comment_meta( $item->comment_ID, 'assignment_mark', true );
		$button_text = $evaluated ? __( 'Details', 'tutor-pro' ) : __( 'Evaluate', 'tutor-pro' );

		echo "<a class='tutor-btn tutor-btn-outline-primary tutor-btn-sm' href='" . esc_url( admin_url( "admin.php?page=tutor-assignments&view_assignment=$item->comment_ID&post-id=$post_id" ) ) . "'>" . esc_html( $button_text ) . '</a>';
	}

	/**
	 * Delete action.
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_action_delete( $item ) {
		echo '<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" data-assignment_id="' . esc_attr( $item->comment_ID ) . '" href="#" data-tutor-modal-target="assignment-' . esc_attr( $item->comment_ID ) . '">' . esc_html( _x( ' Delete', 'assignment delete', 'tutor-pro' ) ) . '</a>';
	}

	/**
	 * Course column.
	 *
	 * @param object $item item.
	 *
	 * @return void
	 */
	public function column_course( $item ) {
		echo '<a href="' . esc_url( get_the_permalink( $item->comment_parent ) ) . '" target="_blank">' . esc_html( get_the_title( $item->comment_parent ) ) . '</a>';
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @param int    $course_id course id.
	 * @param mixed  $date date.
	 * @param string $search search term.
	 *
	 * @return array
	 */
	public function tabs_key_value( $course_id, $date, $search ): array {
		$data = $this->tabs_data( $course_id, $date, $search );
		$tabs = array(
			array(
				'key'   => 'all',
				'title' => __( 'All', 'tutor-pro' ),
				'value' => $data['all'],
				'url'   => '?page=tutor-assignments&data=all',
			),
			array(
				'key'   => 'pass',
				'title' => __( 'Pass', 'tutor-pro' ),
				'value' => $data['pass'],
				'url'   => '?page=tutor-assignments&data=pass',
			),
			array(
				'key'   => 'fail',
				'title' => __( 'Fail', 'tutor-pro' ),
				'value' => $data['fail'],
				'url'   => '?page=tutor-assignments&data=fail',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor-pro' ),
				'value' => $data['pending'],
				'url'   => '?page=tutor-assignments&data=pending',
			),
		);

		return $tabs;
	}

	/**
	 * Provide data for tabs
	 *
	 * @since 2.0.0
	 *
	 * @param int    $course_id course id.
	 * @param mixed  $date date.
	 * @param string $search search term.
	 *
	 * @return array
	 */
	public function tabs_data( $course_id, $date, $search ) {
		/**
		 * If current user is admin then 0 to get all assignments
		 * otherwise just get that belongs to instructor
		 */
		$user_id = current_user_can( 'administrator' ) ? 0 : get_current_user_id();
		$all     = self::count_all( 'all', $course_id, $date, $search, '', '', '', $user_id );
		$pass    = self::count_pass_fail( 'pass', $course_id, $date, $search, '', '', '', $user_id );
		$fail    = self::count_pass_fail( 'fail', $course_id, $date, $search, '', '', '', $user_id );
		$pending = self::count_pending( 'pending', $course_id, $date, $search, '', '', '', $user_id );
		return array(
			'all'     => is_array( $all ) ? count( $all ) : 0,
			'pass'    => is_array( $pass ) ? count( $pass ) : 0,
			'fail'    => is_array( $fail ) ? count( $fail ) : 0,
			'pending' => is_array( $pending ) ? count( $pending ) : 0,
		);
	}

	/**
	 * Listing for All Assignments
	 *
	 * @since 2.0.0
	 *
	 * @param string  $status status.
	 * @param string  $course_id course id.
	 * @param string  $date date.
	 * @param string  $search_term search term.
	 * @param string  $offset offset.
	 * @param string  $limit limit.
	 * @param string  $order order.
	 * @param integer $user_id user id.
	 *
	 * @return array $result
	 */
	public static function assignment_list_all( string $status, $course_id = '', $date = '', $search_term = '', $offset = '', $limit = '', $order = '', $user_id = 0 ) {
		return self::count_all( $status, $course_id, $date, $search_term, $offset, $limit, $order, $user_id );
	}

	/**
	 * Listing for Pending Assignments
	 *
	 * @since 2.0.0
	 *
	 * @param string  $status status.
	 * @param string  $course_id course id.
	 * @param string  $date date.
	 * @param string  $search_term search term.
	 * @param string  $offset offset.
	 * @param string  $limit limit.
	 * @param string  $order order.
	 * @param integer $user_id user id.
	 *
	 * @return array $result
	 */
	public static function assignment_list_pending( string $status, $course_id = '', $date = '', $search_term = '', $offset = '', $limit = '', $order = '', $user_id = 0 ) {
		return self::count_pending( $status, $course_id, $date, $search_term, $offset, $limit, $order, $user_id );
	}

	/**
	 * Listing for Passed and Failed Assignments
	 *
	 * @since 2.0.0
	 *
	 * @param string  $status | required.
	 * @param string  $course_id selected course id | optional.
	 * @param string  $date selected date | optional.
	 * @param string  $search_term search by user name or email | optional.
	 * @param string  $offset offset.
	 * @param string  $limit limit.
	 * @param string  $order order.
	 * @param integer $user_id user id.
	 *
	 * @return array $result
	 */
	public static function assignment_list_pass_fail( string $status, $course_id = '', $date = '', $search_term = '', $offset = '', $limit = '', $order = '', $user_id = 0 ) {
		return self::count_pass_fail( $status, $course_id, $date, $search_term, $offset, $limit, $order, $user_id );
	}

	/**
	 * Count assignments by status & filters
	 * Count pass | fail
	 *
	 * @since 2.0.0
	 *
	 * @param string  $status status.
	 * @param string  $course_id course id.
	 * @param string  $date date.
	 * @param string  $search_term search term.
	 * @param string  $offset offset.
	 * @param string  $limit limit.
	 * @param string  $order order.
	 * @param integer $user_id user id.
	 *
	 * @return int
	 */
	protected static function count_pass_fail( string $status, $course_id = '', $date = '', $search_term = '', $offset = '', $limit = '', $order = '', $user_id = 0 ) {
		global $wpdb;
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );
		$status      = sanitize_text_field( $status );
		$order       = sanitize_sql_orderby( $order );

		$course_query = '';
		// Prepare search query.
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		if ( '' !== $course_id ) {
			$course_id    = (int) $course_id;
			$course_query = "AND course.ID = $course_id ";
		}
		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(post.post_date) = CAST( '$date' AS DATE )";
		}

		$status_query = '';
		if ( 'pass' === $status ) {
			$status_query = 'AND CAST(evaluate_mark.meta_value AS SIGNED) >= CAST(pass_mark.meta_value AS SIGNED)';
		} else {
			$status_query = 'AND CAST(evaluate_mark.meta_value AS SIGNED) < CAST(pass_mark.meta_value AS SIGNED)';
		}

		$offset_limit_query = '';
		if ( '' !== $offset && '' !== $limit ) {
			$offset_limit_query = "LIMIT $offset, $limit";
		}

		$order_query = '';
		if ( '' !== $order ) {
			$order_query = "ORDER BY submit.comment_date {$order}";
		} else {
			$order_query = 'ORDER BY submit.comment_date DESC';
		}
		$user_query = '';
		if ( $user_id ) {
			$user_id    = sanitize_text_field( $user_id );
			$user_query = "AND course.post_author = {$user_id}";
		}

		//phpcs:disable
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->posts} AS post 
				INNER JOIN {$wpdb->posts} AS topic
					ON topic.ID = post.post_parent
				INNER JOIN {$wpdb->posts} AS course
					ON course.ID = topic.post_parent
				INNER JOIN {$wpdb->comments} AS submit
					ON submit.comment_post_ID = post.ID
				INNER JOIN {$wpdb->postmeta} AS total_mark 
					ON total_mark.post_id = post.ID 
					AND total_mark.meta_key = '_tutor_assignment_total_mark'
				INNER JOIN {$wpdb->postmeta} AS pass_mark 
					ON pass_mark.post_id = post.ID 
					AND pass_mark.meta_key = '_tutor_assignment_pass_mark'
				INNER JOIN {$wpdb->commentmeta} AS evaluate_mark 
					ON evaluate_mark.comment_ID = submit.comment_ID 
					AND evaluate_mark.meta_key = 'assignment_mark'
				WHERE post.post_type = %s
					{$status_query}
					{$course_query}
					{$date_query}
					{$user_query}
					AND ( post.post_title LIKE  %s OR course.post_title LIKE %s )
					{$order_query}
					{$offset_limit_query}
			",
				'tutor_assignments',
				$search_term,
				$search_term
			)
		);
		//phpcs:enable

		return $results;
	}

	/**
	 * Count assignments by status & filters count pending.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $status status.
	 * @param string  $course_id course id.
	 * @param string  $date date.
	 * @param string  $search_term search term.
	 * @param string  $offset offset.
	 * @param string  $limit limit.
	 * @param string  $order order.
	 * @param integer $user_id user id.
	 *
	 * @return int
	 */
	protected static function count_pending( string $status, $course_id = '', $date = '', $search_term = '', $offset = '', $limit = '', $order = '', $user_id = 0 ) {
		global $wpdb;
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );
		$status      = sanitize_text_field( $status );
		$order       = sanitize_sql_orderby( $order );

		// Prepare search query.
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$course_query = '';
		if ( '' !== $course_id ) {
			$course_id    = (int) $course_id;
			$course_query = "AND course.ID = $course_id";
		}

		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(post.post_date) = CAST( '$date' AS DATE )";
		}

		$status_query = '';
		if ( 'pending' === $status ) {
			$status_query = 'AND submit.comment_post_ID IS NOT NULL';
		}

		$offset_limit_query = '';
		if ( '' !== $offset && '' !== $limit ) {
			$offset_limit_query = "LIMIT $offset, $limit";
		}

		$order_query = '';
		if ( '' !== $order ) {
			$order_query = "ORDER BY submit.comment_date {$order}";
		} else {
			$order_query = 'ORDER BY submit.comment_date DESC';
		}

		$user_query = '';
		if ( $user_id ) {
			$user_id    = sanitize_text_field( $user_id );
			$user_query = "AND course.post_author = {$user_id}";
		}

		//phpcs:disable
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->posts} AS post 
				INNER JOIN {$wpdb->posts} AS topic
					ON topic.ID = post.post_parent
				INNER JOIN {$wpdb->posts} AS course
					ON course.ID = topic.post_parent
				INNER JOIN {$wpdb->comments} AS submit
				ON submit.comment_post_ID = post.ID
				LEFT JOIN {$wpdb->commentmeta} AS evaluate
					ON evaluate.comment_ID = submit.comment_ID AND evaluate.meta_key = 'assignment_mark'
				WHERE post.post_type = %s
				AND evaluate.meta_value IS NULL
					{$course_query}
					{$date_query}
					{$status_query}
					{$user_query}
				AND ( post.post_title LIKE  %s OR course.post_title LIKE %s ) 
				{$order_query}
				{$offset_limit_query}
			",
				'tutor_assignments',
				$search_term,
				$search_term
			)
		);
		//phpcs:enable

		return $results;
	}

	/**
	 * Count assignments by status & filters Count all
	 *
	 * @param string  $status status.
	 * @param string  $course_id course id.
	 * @param string  $date date.
	 * @param string  $search_term search term.
	 * @param string  $offset offset.
	 * @param string  $limit limit.
	 * @param string  $order order.
	 * @param integer $user_id user id.
	 *
	 * @return int
	 */
	protected static function count_all( string $status, $course_id = '', $date = '', $search_term = '', $offset = '', $limit = '', $order = '', $user_id = 0 ) {
		global $wpdb;
		$course_id   = sanitize_text_field( $course_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );
		$status      = sanitize_text_field( $status );
		$order       = sanitize_sql_orderby( $order );

		// Prepare search query.
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$course_query = '';
		if ( '' !== $course_id ) {
			$course_id    = (int) $course_id;
			$course_query = "AND course.ID = $course_id";
		}

		$date_query = '';
		if ( '' !== $date ) {
			$date_query = "AND DATE(post.post_date) = CAST( '$date' AS DATE )";
		}

		$status_query = '';
		if ( 'all' === $status ) {
			$status_query = 'AND submit.comment_post_ID IS NOT NULL';
		}

		$offset_limit_query = '';
		if ( '' !== $offset && '' !== $limit ) {
			$offset_limit_query = "LIMIT $offset, $limit";
		}

		$order_query = '';
		if ( '' !== $order ) {
			$order_query = "ORDER BY submit.comment_date {$order}";
		} else {
			$order_query = 'ORDER BY submit.comment_date DESC';
		}

		$user_query = '';
		if ( $user_id ) {
			$user_id    = sanitize_text_field( $user_id );
			$user_query = "AND course.post_author = {$user_id}";
		}

		//phpcs:disable
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->posts} AS post 
				INNER JOIN {$wpdb->posts} AS topic
					ON topic.ID = post.post_parent
				INNER JOIN {$wpdb->posts} AS course
					ON course.ID = topic.post_parent
				INNER JOIN {$wpdb->comments} AS submit
					ON submit.comment_post_ID = post.ID
				WHERE post.post_type = %s
					{$course_query}
					{$date_query}
					{$status_query}
					{$user_query}
				AND ( post.post_title LIKE  %s OR course.post_title LIKE %s ) 
				{$order_query}
				{$offset_limit_query}
			",
				'tutor_assignments',
				$search_term,
				$search_term
			)
		);
		//phpcs:enable

		return $results;
	}

	/**
	 * Get student's submitted assignments by assignment id
	 *
	 * @param integer $assignment_id  required argument assignment id.
	 * @param string  $order_filter  optional default value DESC.
	 *
	 * @return array  list of assignments.
	 */
	public static function get_submitted_assignments( int $assignment_id, string $order_filter = 'DESC' ): array {
		global $wpdb;
		$assignments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					* 
			  	FROM 
					{$wpdb->comments} 
			  	WHERE 
					comment_type = 'tutor_assignment' 
					AND comment_post_ID = %d 
			  	ORDER BY 
				  	comment_ID $order_filter", //phpcs:ignore
				$assignment_id
			)
		);
		return is_array( $assignments ) && count( $assignments ) ? $assignments : array();
	}

	/**
	 * Count total comment for an assignment
	 *
	 * @param integer $id assignment id.
	 *
	 * @return integer
	 */
	public static function assignment_comment_count( int $id ): int {
		global $wpdb;
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT
					COUNT(comment_ID)
				FROM 
					{$wpdb->comments}
				WHERE 
					comment_type = 'tutor_assignment'
					AND comment_post_ID = %d",
				$id
			)
		);
		return (int) $count;
	}
}
