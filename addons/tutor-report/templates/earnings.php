<?php
/**
 * Earnings template
 *
 * @since 1.9.9
 */
use TUTOR_REPORT\Analytics;
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
<div class="tutor-analytics-earnings">
		
	<!--analytics graph -->
	<?php
		/**
		 * Earnings card info
		 *
		 * @since 1.9.9
		 */
		$card_template = TUTOR_REPORT()->path . 'templates/elements/box-card.php';
		$user          = wp_get_current_user();
		$earnings      = tutor_utils()->get_earning_sum( $user->ID );

		$data = array(
			array(
				'icon'      => 'tutor-icon-wallet',
				'title'     => $earnings->instructor_amount,
				'sub_title' => __( 'Total Earning', 'tutor-pro' ),
				'price'     => true,
			),
			array(
				'icon'      => 'tutor-icon-chart-pie',
				'title'     => $earnings->balance,
				'sub_title' => __( 'Current Balance', 'tutor-pro' ),
				'price'     => true,
			),
			array(
				'icon'      => 'tutor-icon-coins',
				'title'     => $earnings->withdraws_amount,
				'sub_title' => __( 'Total Withdraws', 'tutor-pro' ),
				'price'     => true,
			),
			array(
				'icon'      => 'tutor-icon-dollar-slot',
				'title'     => $earnings->course_price_total,
				'sub_title' => __( 'Total Sale', 'tutor-pro' ),
				'price'     => true,
			),
			array(
				'icon'      => 'tutor-icon-filter-dollar',
				'title'     => $earnings->admin_amount,
				'sub_title' => __( 'Deducted Commissions', 'tutor-pro' ),
				'price'     => true,
			),
			array(
				'icon'      => 'tutor-icon-badge-discount',
				'title'     => $earnings->deduct_fees_amount,
				'sub_title' => __( 'Deducted Fees', 'tutor-pro' ),
				'price'     => true,
			),
		);

		tutor_load_template_from_custom_path( $card_template, $data );
		?>

	<!--card info end -->

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
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'analytics/earnings?period=today' ),
				'title' => __( 'Today', 'tutor-pro' ),
				'class' => 'tutor-analytics-period-button',
				'type'  => 'today',
			),
			array(
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'analytics/earnings?period=monthly' ),
				'title' => __( 'Monthly', 'tutor-pro' ),
				'class' => 'tutor-analytics-period-button',
				'type'  => 'monthly',
			),
			array(
				'url'   => esc_url( tutor_utils()->tutor_dashboard_url() . 'analytics/earnings?period=yearly' ),
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

	<!--filter button tabs end-->

	<!--analytics graph -->
	<?php
		/**
		 * Get analytics data
		 *
		 * @since 1.9.9
		 */
		$commission_fees = Analytics::commission_fees_by_user( $user->ID, $time_period, $start_date, $end_date );
		$content_title   = '';
	if ( 'today' === $time_period ) {
		$day = gmdate( 'l' );
		/* translators: %s: day */
		$content_title = sprintf( __( 'for today (%s)', 'tutor-pro' ), $day );
	} elseif ( 'monthly' === $time_period ) {
		$month = gmdate( 'F' );
		/* translators: %s: month */
		$content_title = sprintf( __( 'for this month (%s)', 'tutor-pro' ), $month );
	} elseif ( 'yearly' === $time_period ) {
		$current_year = gmdate( 'Y' );
		/* translators: %s: year */
		$content_title = sprintf( __( 'for this year (%s)', 'tutor-pro' ), $current_year );
	}
		$graph_tabs     = array(
			array(
				'tab_title'     => __( 'Total Earning', 'tutor-pro' ),
				'tab_value'     => Analytics::get_earnings_by_user( $user->ID, $time_period, $start_date, $end_date )['total_earnings'],
				'data_attr'     => 'ta_total_earnings',
				'active'        => ' is-active',
				'price'         => true,
				/* translators: %s: content title */
				'content_title' => sprintf( __( 'Earning chart %s', 'tutor-pro' ), $content_title ),
			),
			array(
				'tab_title'     => __( 'Number of Sales', 'tutor-pro' ),
				'tab_value'     => Analytics::number_of_sales( $user->ID, $time_period, $start_date, $end_date )['total_sales'],
				'data_attr'     => 'ta_total_course_enrolled',
				'active'        => '',
				'price'         => false,
				/* translators: %s: content title */
				'content_title' => sprintf( __( 'Sales chart %s', 'tutor-pro' ), $content_title ),
			),
			array(
				'tab_title'     => __( 'Commission', 'tutor-pro' ),
				'tab_value'     => $commission_fees['total'],
				'data_attr'     => 'ta_total_refund',
				'active'        => '',
				'price'         => true,
				/* translators: %s: content title */
				'content_title' => sprintf( __( 'Commission & fess chart %s', 'tutor-pro' ), $content_title ),
			),
		);
		$graph_template = TUTOR_REPORT()->path . 'templates/elements/graph.php';
		tutor_load_template_from_custom_path( $graph_template, $graph_tabs );
		?>
	<!--analytics graph end -->    
</div>
