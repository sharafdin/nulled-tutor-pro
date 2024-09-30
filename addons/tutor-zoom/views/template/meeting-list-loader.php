<?php
/**
 * Zoom meeting expired list at frontend dashboard
 *
 * This file is not actually for only expired meetings. It's reused in multiple place.
 * As a process of code unification we're now using single file for meeting list.
 * File name not changed in favor of frontend dashboard URL structure.
 *
 * @since 1.9.4
 */

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pagination
$per_page = get_tutor_option( 'pagination_per_page', 10 );
$paged    = Input::get( 'paged', 1, Input::TYPE_INT );
$paged    = max( 1, $paged );

// Search Filter
$_search = isset( $_GET['search'] ) ? $_GET['search'] : '';
$_course = isset( $_GET['course-id'] ) ? (int) $_GET['course-id'] : '';
$_date   = isset( $_GET['date'] ) ? $_GET['date'] : '';

$orderby = ( ! isset( $_GET['orderby'] ) || $_GET['orderby'] !== 'post_title' ) ? null : '_meeting.post_title';
$order   = ( ! isset( $_GET['order'] ) || $_GET['order'] !== 'desc' ) ? 'asc' : 'desc';

$user_id = get_current_user_id();
$_filter = isset( $_filter ) ? $_filter : 'expired';

$zoom_object = new \TUTOR_ZOOM\Zoom( false );

// Get meetings for current page listing
$meetings = $zoom_object->get_meetings(
	$per_page,
	$paged,
	$_filter,
	array(
		'author'    => sanitize_text_field( $user_id ),
		'search'    => sanitize_text_field( $_search ),
		'course_id' => sanitize_text_field( $_course ),
		'date'      => sanitize_text_field( $_date ),
		'orderby'   => sanitize_text_field( $orderby ),
		'order'     => sanitize_text_field( $order ),
	)
);

// Get total meeting list count
$total_items = count(
	$zoom_object->get_meetings(
		null,
		null,
		$_filter,
		array(
			'author'    => sanitize_text_field( $user_id ),
			'search'    => sanitize_text_field( $_search ),
			'course_id' => sanitize_text_field( $_course ),
			'date'      => sanitize_text_field( $_date ),
		)
	)
);

// Get course for dropdown select
$courses = get_posts(
	array(
		'author'      => sanitize_text_field( $user_id ),
		'numberposts' => -1,
		'post_type'   => tutor()->course_post_type,
		'post_status' => 'publish',
	)
);
?>

<?php
if ( is_admin() ) {

	$filters = array(
		'bulk_action'   => false,
		'filters'       => true,
		'course_filter' => true,
		'sort_by'       => false,
	);

	$filters_template = tutor()->path . 'views/elements/filters.php';
	tutor_load_template_from_custom_path( $filters_template, $filters );
	
} else {
	?>
		<form action="" method="get" id="tutor-zoom-search-filter-form" class="tutor-wp-dashboard-filter tutor-mb-24">
			<div class="tutor-wp-dashboard-filter-items tutor-d-flex tutor-flex-xl-nowrap tutor-flex-wrap tutor-justify-between">
				<div class="tutor-wp-dashboard-filter-item tutor-col">
					<label class="tutor-form-label">
					<?php _e( 'Search', 'tutor-pro' ); ?>
					</label>
					<div class="tutor-form-wrap">
						<span class="tutor-icon-search tutor-form-icon" area-hidden="true"></span>
						<input name="search" type="search" class="tutor-form-control" value="<?php echo $_search; ?>" autocomplete="off" placeholder="<?php _e( 'Search meeting', 'tutor-pro' ); ?>">
					</div>
				</div>
				<div class="tutor-wp-dashboard-filter-item tutor-col tutor-my-lg-0 tutor-my-12">
					<label class="tutor-form-label">
					<?php _e( 'Course', 'tutor-pro' ); ?>
					</label>
					<select name="course-id" class="tutor-zoom-course tutor-form-select" style="width:100%; max-width:100%;">
						<option value=""><?php _e( 'All', 'tutor-pro' ); ?></option>
					<?php
					if ( ! empty( $courses ) ) {
						foreach ( $courses as $key => $course ) {
							echo '<option ' . ( $_course == $course->ID ? 'selected' : '' ) . ' value="' . $course->ID . '">' . $course->post_title . '</option>';
						}
					}
					?>
					</select>
				</div>
				<div class="tutor-wp-dashboard-filter-item">
					<label class="tutor-form-label">
						<?php _e( 'Date', 'tutor-pro' ); ?>
					</label>
					<div class="tutor-v2-date-picker">
						<input class="tutor-form-control tutor-form-control-fd" name="date" type="text" placeholder="<?php _e( 'dd/mm/yyyy', 'tutor-pro' ); ?>">
					</div>
				</div>
			</div>
		</form>
	<?php
}
?>

<div class="<?php echo is_admin() ? 'tutor-mt-24' : ''; ?>">
	<?php
	if ( ! empty( $meetings ) ) {

		$sort_order = array( 'order' => $order == 'asc' ? 'desc' : 'asc' );

		$time_sort = http_build_query( array_merge( $_GET, ( $orderby == 'datetime' ? $sort_order : array() ), array( 'orderby' => 'datetime' ) ) );
		$name_sort = http_build_query( array_merge( $_GET, ( $orderby == 'post_title' ? $sort_order : array() ), array( 'orderby' => 'post_title' ) ) );

			$time_icon = $orderby == 'datetime' ? ( strtolower( $order ) == 'asc' ? 'tutor-icon-order-up' : 'tutor-icon-order-down' ) : 'tutor-icon-order-up';
			$name_icon = $orderby == 'post_title' ? ( strtolower( $order ) == 'asc' ? 'tutor-icon-order-up' : 'tutor-icon-order-down' ) : 'tutor-icon-order-up';

		// Load reusable table renderer
		$context = 'frontend-expired';
		include dirname( __DIR__ ) . '/template/meeting-list.php';
	} else {
		tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() );
	}

	if ( count( $meetings ) ) {
		$base_url = '';
		if ( is_admin() ) {
			$base_url = str_replace( 1, '%#%', "admin.php?page=tutor_zoom&sub_page=$current_page&paged=%#%" );
		} else {
			$current_page = (isset($_filter) && $_filter=='expired') ? '/expired' : '';
			$url      = esc_url( tutor_utils()->get_tutor_dashboard_page_permalink() . 'zoom'.$current_page.'/?paged=%#%' );
			$base_url = str_replace( 1, '%#%', $url );
		}

		if ( $total_items > $per_page ) {
			$pagination_data     = array(
				'base'        => $base_url,
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'paged'       => $paged,
			);

			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			?>
				<div class="<?php echo is_admin() ? 'tutor-mt-48' : ''; ?>">
				<?php
					tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
				?>
				</div>
			<?php
		}
	}

		do_action( 'tutor_zoom/after/meetings' );
	?>
</div>
