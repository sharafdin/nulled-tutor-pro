<?php
/**
 * Overview tempate
 *
 * @since 1.9.9
 */

use Tutor\Models\CourseModel;
use \TUTOR_REPORT\Analytics;

// global variables
$user        = wp_get_current_user();
$time_period = $active = isset( $_GET['period'] ) ? $_GET['period'] : '';
$start_date  = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
$end_date    = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
if ( '' !== $start_date ) {
	$start_date = tutor_get_formated_date( 'Y-m-d', $start_date );
}
if ( '' !== $end_date ) {
	$end_date = tutor_get_formated_date( 'Y-m-d', $end_date );
}
?>
<div class="tutor-analytics-overview">

	<?php
		/**
		 * Overview card info
		 *
		 * @since 1.9.9
		 */
		$card_template = TUTOR_REPORT()->path . 'templates/elements/box-card.php';
		$user          = wp_get_current_user();
		$data          = array(
			array(
				'icon'      => 'tutor-icon-mortarboard-o',
				'title'     => count( CourseModel::get_courses_by_instructor( $user->ID ) ),
				'sub_title' => __( 'Total Course', 'tutor-pro' ),
				'price'     => false,
			),
			array(
				'icon'      => 'tutor-icon-add-member',
				'title'     => tutor_utils()->get_total_students_by_instructor( $user->ID ),
				'sub_title' => __( 'Total Student', 'tutor-pro' ),
				'price'     => false,
			),
			array(
				'icon'      => 'tutor-icon-star-bold',
				'title'     => tutor_utils()->get_reviews_by_instructor( $user->ID )->count,
				'sub_title' => __( 'Reviews', 'tutor-pro' ),
				'price'     => false,
			),
		);

		tutor_load_template_from_custom_path( $card_template, $data );
		?>
	<!--filter buttons tabs-->
	<?php
		/**
		 * Prepare filter period buttons
		 *
		 * Array structure is required as below
		 *
		 * @since 1.9.8
		 */
		$filter_period = array(
			array(
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'analytics?period=today' ),
				'title' => __( 'Today', 'tutor-pro' ),
				'class' => 'tutor-analytics-period-button',
				'type'  => 'today',
			),
			array(
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'analytics?period=monthly' ),
				'title' => __( 'Monthly', 'tutor-pro' ),
				'class' => 'tutor-analytics-period-button',
				'type'  => 'monthly',
			),
			array(
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'analytics?period=yearly' ),
				'title' => __( 'Yearly', 'tutor-pro' ),
				'class' => 'tutor-analytics-period-button',
				'type'  => 'yearly',
			),
		);

		/**
		 * Calendar date buttons
		 *
		 * Array structure is required as below
		 *
		 * @since 1.9.8
		 */

		$filter_period_calendar = array(
			'filter_period'   => $filter_period,
			'filter_calendar' => true,
		);

		$filter_period_calendar_template = TUTOR_REPORT()->path . 'templates/elements/period-calendar.php';
		tutor_load_template_from_custom_path( $filter_period_calendar_template, $filter_period_calendar );
		?>

	<?php
		/**
		 * Get analytics data
		 *
		 * @since 1.9.9
		 */
		$earnings      = Analytics::get_earnings_by_user( $user->ID, $time_period, $start_date, $end_date );
		$enrollments   = Analytics::get_total_students_by_user( $user->ID, $time_period, $start_date, $end_date );
		$discounts     = Analytics::get_discounts_by_user( $user->ID, $time_period, $start_date, $end_date );
		$refunds       = Analytics::get_refunds_by_user( $user->ID, $time_period, $start_date, $end_date );
		$content_title = '';

	if ( 'today' === $time_period ) {
		$day = tutor_utils()->translate_dynamic_text( strtolower( gmdate( 'l' ) ) );
		/* translators: %s: day */
		$content_title = sprintf( __( 'for today (%s)', 'tutor-pro' ), $day );
	} elseif ( 'monthly' === $time_period ) {
		$month = tutor_utils()->translate_dynamic_text( strtolower( gmdate( 'F' ) ) );
		/* translators: %s: month */
		$content_title = sprintf( __( 'for this month (%s)', 'tutor-pro' ), $month );
	} elseif ( 'yearly' === $time_period ) {
		$current_year = gmdate( 'Y' );
		/* translators: %s: year */
		$content_title = sprintf( __( 'for this year (%s)', 'tutor-pro' ), $current_year );
	}

		$graph_tabs = array(
			array(
				'tab_title'     => __( 'Total Earning', 'tutor-pro' ),
				'tab_value'     => $earnings['total_earnings'],
				'data_attr'     => 'ta_total_earnings',
				'active'        => ' is-active',
				'price'         => true,
				/* translators: %s: content title */
				'content_title' => sprintf( __( 'Earnings chart %s', 'tutor-pro' ), $content_title ),
			),
			array(
				'tab_title'     => __( 'Course Enrolled', 'tutor-pro' ),
				'tab_value'     => $enrollments['total_enrollments'],
				'data_attr'     => 'ta_total_course_enrolled',
				'active'        => '',
				'price'         => false,
				/* translators: %s: content title */
				'content_title' => sprintf( __( 'Course enrolled Chart %s', 'tutor-pro' ), $content_title ),
			),
			array(
				'tab_title'     => __( 'Total Refund', 'tutor-pro' ),
				'tab_value'     => $refunds['total_refunds'],
				'data_attr'     => 'ta_total_refund',
				'active'        => '',
				'price'         => true,
				/* translators: %s: content title */
				'content_title' => sprintf( __( 'Refund chart %s', 'tutor-pro' ), $content_title ),
			),
			array(
				'tab_title'     => __( 'Total Discount', 'tutor-pro' ),
				'tab_value'     => $discounts['total_discounts'],
				'data_attr'     => 'ta_total_discount',
				'active'        => '',
				'price'         => true,
				/* translators: %s: content title */
				'content_title' => sprintf( __( 'Discount chart %s', 'tutor-pro' ), $content_title ),
			),
		);

		$graph_template = TUTOR_REPORT()->path . 'templates/elements/graph.php';
		tutor_load_template_from_custom_path( $graph_template, $graph_tabs );

		$popular_courses = tutor_utils()->most_popular_courses( 7, get_current_user_id() );
		$reviews         = tutor_utils()->get_reviews_by_instructor( $user->ID, $offset = 0, 7 );
		?>

	<?php if ( count( $popular_courses ) ) : ?>
		<div class="tutor-analytics-widget tutor-analytics-widget-popular-courses tutor-mb-32">
			<div class="tutor-analytics-widget-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
				<?php esc_html_e( 'Most Popular Courses', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-analytics-widget-body">
				<div class="tutor-table-responsive">
					<table class="tutor-table">
						<thead>
							<th width="70%">
								<?php esc_html_e( 'Course Name', 'tutor-pro' ); ?>
							</th>
							<th width="15%">
								<?php esc_html_e( 'Total Enrolled', 'tutor-pro' ); ?>
							</th>
							<th width="15%">
								<?php esc_html_e( 'Rating', 'tutor-pro' ); ?>
							</th>
						</thead>

						<tbody>
							<?php foreach ( $popular_courses as $course ) : ?>
								<tr>
									<td>
										<?php esc_html_e( $course->post_title ); ?>
									</td>
									<td>
										<?php esc_html_e( $course->total_enrolled ); ?>
									</td>
									<td>
										<?php
											$rating     = tutor_utils()->get_course_rating( $course->ID );
											$avg_rating = ! is_null( $rating ) ? $rating->rating_avg : 0;
										?>
										<?php tutor_utils()->star_rating_generator_v2( $avg_rating, null, true ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( is_array( $reviews->results ) && count( $reviews->results ) ) : ?>
		<div class="tutor-analytics-widget tutor-analytics-widget-reviews tutor-mb-32">
			<div class="tutor-analytics-widget-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16">
				<?php esc_html_e( 'Recent Reviews', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-analytics-widget-body">
				<div class="tutor-table-responsive">
					<table class="tutor-table">
						<thead>
							<th width="25%">
								<?php esc_html_e( 'Student', 'tutor-pro' ); ?>
							</th>
							<th width="25%">
								<?php esc_html_e( 'Date', 'tutor-pro' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Feedback', 'tutor-pro' ); ?>
							</th>
						</thead>

						<tbody>
							<?php foreach ( $reviews->results as  $key => $review ) : ?>
								<tr>
									<td class="tutor-td-top">
										<div class="tutor-d-flex tutor-align-center">
											<?php echo tutor_utils()->get_tutor_avatar( $review->user_id ); ?>
											<div class="tutor-ml-16">
												<?php esc_html_e( $review->display_name ); ?>
											</div>
										</div>
									</td>
									<td class="tutor-td-top">
										<?php
											$date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
											$date             = tutor_get_formated_date( $date_time_format, $review->comment_date );
											esc_html_e( $date );
										?>
									</td>
									<td class="tutor-td-top">
										<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true ); ?>
										<div class="tutor-fs-6 tutor-fw-normal tutor-color-muted tutor-mt-8">
											<?php echo esc_textarea( $review->comment_content ); ?>
										</div>
										<div class="tutor-fs-7 tutor-color-secondary tutor-mt-16">
											<span class="tutor-fs-8 tutor-fw-medium"><?php esc_html_e( 'Course', 'tutor' ); ?>:</span>&nbsp;
											<span data-href="<?php echo esc_url( get_the_permalink( $review->comment_post_ID ) ); ?>">
												<?php echo esc_html( get_the_title( $review->comment_post_ID ) ); ?>
											</span>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
