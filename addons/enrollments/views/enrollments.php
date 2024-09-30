<?php
/**
 * Enrollment List Template.
 *
 * @package Enrollment List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use TUTOR_ENROLLMENTS\Enrollments_List;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;

$enrollments = new Enrollments_List();

/**
 * Short able params
 */
$course_id = Input::get( 'course-id', '' );
$order     = Input::get( 'order', 'DESC' );
$date      = Input::get( 'date', '' );
$search    = Input::get( 'search', '' );

/**
 * Determine active tab
 */
$active_tab = Input::get( 'data', 'all' );

/**
 * Pagination data
 */
$paged    = Input::get( 'paged', 1, Input::TYPE_INT );
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * abs( $paged ) ) - $per_page;

$enrollments_list = tutor_utils()->get_enrolments( $active_tab, $offset, $per_page, $search, $course_id, $date, $order );
$total            = tutor_utils()->get_total_enrolments( $active_tab, $search, $course_id, $date );

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title'   => $enrollments->page_title,
	'tabs'         => $enrollments->tabs_key_value( $course_id, $date, $search ),
	'active'       => $active_tab,
	'add_button'   => true,
	'button_title' => __( 'Enroll a student', 'tutor-pro' ),
	'modal_target' => 'enrollment-modal',
	'button_url'   => '#',
);

$filters = array(
	'bulk_action'   => $enrollments->bulk_action,
	'bulk_actions'  => $enrollments->prpare_bulk_actions(),
	'ajax_action'   => 'tutor_enrollment_bulk_action',
	'filters'       => true,
	'course_filter' => true,
);

if ( 'cancelled' === $active_tab ) {
	$filters['bulk_actions'][] = array(
		'value'  => 'delete',
		'option' => __( 'Delete Permanently', 'tutor-pro' ),
	);
}
?>
<div class="tutor-admin-wrap">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = tutor()->path . 'views/elements/navbar.php';
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
	?>
	<div class="tutor-admin-body">
		<div class="tutor-admin-page-wrapper tutor-admin-enrollment-wrapper">
			<div class="tutor-mt-24">
				<?php if ( is_array( $enrollments_list ) && count( $enrollments_list ) ) : ?>
					<div class="tutor-table-responsive">
						<table class="tutor-table tutor-table-with-checkbox">
							<thead>
								<tr>
									<th style="width: 5%;">
										<div class="tutor-d-flex">
											<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input">
										</div>
									</th>
									<th width="15%">
										<?php esc_html_e( 'Date', 'tutor-pro' ); ?>
									</th>
									<th>
										<?php esc_html_e( 'Course', 'tutor-pro' ); ?>
									</th>
									<th class="tutor-table-rows-sorting" width="10%">
										<?php esc_html_e( 'Name', 'tutor-pro' ); ?>
										<span class="a-to-z-sort-icon tutor-icon-ordering-a-z"></span>
									</th>
									<th>
										<?php esc_html_e( 'Status', 'tutor-pro' ); ?>
									</th>
								</tr>
							</thead>

							<tbody>
								<?php
								foreach ( $enrollments_list as $list ) :
									$alert = '';
									if ( 'cancel' === $list->status || 'cancelled' === $list->status || 'canceled' === $list->status ) {
										$alert = 'danger';
									} elseif ( 'completed' === $list->status ) {
										$alert = 'success';
									} elseif ( 'processing' === $list->status ) {
										$alert = 'processing';
									} elseif ( 'on-hold' === $list->status || 'onhold' === $list->status ) {
										$alert = 'onhold';
									} else {
										$alert = 'default';
									}

									$translate_status  = __( 'Approved', 'tutor-pro' );
									$enrollment_status = 'completed' === $list->status ? $translate_status : tutor_utils()->translate_dynamic_text( $list->status );
									if ( CourseBundle::POST_TYPE === get_post_type( $list->course_id ) ) {
										continue;
									}
									?>
									<tr>
										<td>
											<div class="tutor-d-flex">
												<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $list->enrol_id ); ?>">
											</div>
										</td>
										<td>
											<div class="tutor-fs-7">
											<?php esc_html_e( tutor_i18n_get_formated_date( $list->enrol_date ) ); ?>,
											</div>
										</td>
										<td>
											<div class="tutor-d-flex tutor-align-center tutor-gap-2">
											<?php echo esc_html( $list->course_title ); ?>
												
												<a href="<?php echo esc_url( get_permalink( $list->course_id ) ); ?>" class="tutor-iconic-btn" target="_blank">
													<span class="tutor-icon-external-link" area-hidden="true"></span>
												</a>
											</div>
										</td>

										<td>
										<?php echo esc_html( $list->display_name ); ?>
											<div class="tutor-fs-8 tutor-color-muted tutor-mt-4">
											<?php echo esc_html( $list->user_email ); ?>
											</div>
										</td>

										<td>
											<span class="tutor-badge-label label-<?php echo esc_attr( $alert ); ?> tutor-m-4">
											<?php echo esc_html( $enrollment_status ); ?>
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
				<?php endif; ?>
			</div>
			
			<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
				<?php
					/**
					 * Prepare pagination data & load template
					 */
				if ( $total > $per_page ) {
					$pagination_data     = array(
						'total_items' => $total,
						'per_page'    => $per_page,
						'paged'       => $paged,
					);
					$pagination_template = tutor()->path . 'views/elements/pagination.php';
					tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
				}
				?>
			</div>
		</div>
	</div>
</div>

<?php require TUTOR_ENROLLMENTS()->path . '/views/enrollment-success-modal.php'; ?>
<?php require TUTOR_ENROLLMENTS()->path . '/views/enrollment-modal.php'; ?>
