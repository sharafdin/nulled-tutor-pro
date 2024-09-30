<?php

/**
 * Google meet meetings page
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\views
 */

use TUTOR\Input;
use TutorPro\GoogleMeet\GoogleMeet;
use TutorPro\GoogleMeet\Models\EventsModel;
use TutorPro\GoogleMeet\Utilities\Utilities;

global $wp_query;
$query_vars  = $wp_query->query_vars;
$current_tab = Input::get( 'tab', 'active-meeting' );
if ( ! is_admin() ) {
	$current_tab = isset( $query_vars['tutor_dashboard_sub_page'] ) ? $query_vars['tutor_dashboard_sub_page'] : 'active-meeting';
}
$current_page = Input::get( 'paged', 1, Input::TYPE_INT );
if ( ! is_admin() ) {
	$current_page = Input::get( 'current_page', 1, Input::TYPE_INT );
}
$posts_per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset         = ( $posts_per_page * $current_page ) - $posts_per_page;

$context      = 'active-meeting' === $current_tab ? 'active' : 'expired';
$date         = Input::get( 'date', '' );
$sorting_args = array(
	'course_id'   => Input::get( 'course-id', '' ),
	'search_term' => Input::get( 'search', '' ),
	'author_id'   => get_current_user_id(),
	'date'        => '' !== $date ? tutor_get_formated_date( 'Y-m-d', $date ) : '',
);
$paging_args  = array(
	'limit'  => $posts_per_page,
	'offset' => $offset,
);
$meetings     = EventsModel::get( $context, $sorting_args, $paging_args );

$filters = array(
	'bulk_action'   => false,
	'bulk_actions'  => false,
	'filters'       => true,
	'course_filter' => true,
	'sort_by'       => false,
);

$plugin_data = GoogleMeet::meta_data();

?>
<div id="tutor-google-meet-meta-box-wrapper">
	<?php if ( is_admin() ) : ?>
	<div class="tutor-google-meet-meetings tutor-mt-32">
		<?php
			$filters_template = tutor()->path . 'views/elements/filters.php';
			tutor_load_template_from_custom_path( $filters_template, $filters );
		?>
		<div class="tutor-admin-body tutor-mt-32">
			<div class="tutor-table-responsive">
				<table class="tutor-table tutor-table-google-meet-meeting">
					<thead>
						<tr>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Start Time', 'tutor-pro' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-z-a"></span>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'End Time', 'tutor-pro' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-z-a"></span>
							</th>
							<th class="">
								<?php esc_html_e( 'Meeting Title', 'tutor-pro' ); ?>
							</th>
							<th class="">
								<?php esc_html_e( 'Meeting Link', 'tutor-pro' ); ?>
							</th>
							<th class=""></th>
						</tr>
					</thead>

					<tbody>
						<?php if ( is_array( $meetings['meetings'] ) && count( $meetings['meetings'] ) ) : ?>
							<?php foreach ( $meetings['meetings'] as $meeting ) : ?>
								<?php
								$event_details = json_decode( $meeting->event_details );
								if ( ! is_object( $event_details ) ) {
									continue;
								}
								$start_date     = tutor_i18n_get_formated_date( $event_details->start_datetime, get_option( 'date_format' ) );
								$start_time     = tutor_i18n_get_formated_date( $event_details->start_datetime, get_option( 'time_format' ) );
								$end_date       = tutor_i18n_get_formated_date( $event_details->end_datetime, get_option( 'date_format' ) );
								$end_time       = tutor_i18n_get_formated_date( $event_details->end_datetime, get_option( 'time_format' ) );
								$meeting_status = $meeting->meeting_status;
								?>
								<tr class="tutor-google-meet-meeting-item" id="tutor-google-meet-list-item-<?php echo esc_attr( $meeting->ID ); ?>">
									<td>
										<div class="tutor-fs-7">
											<span><?php echo esc_html( $start_date ); ?>,</span>
											<div class="tutor-fw-normal tutor-color-muted">
												<?php echo esc_html( $start_time ); ?>
											</div>
										</div>
									</td>
									<td>
										<div class="tutor-fs-7">
											<span><?php echo esc_html( $end_date ); ?>,</span>
											<div class="tutor-fw-normal tutor-color-muted">
												<?php echo esc_html( $end_time ); ?>
											</div>
										</div>
									</td>
									<td>
										<div>
											<?php echo esc_html( $meeting->post_title ); ?>
										</div>
										<div class="tutor-meta tutor-mt-8">
											<div>
												<?php esc_html_e( 'Course:', 'tutor-pro' ); ?>
												<span class="tutor-meta-value">
													<?php
													$post_type = get_post_type( $meeting->post_parent );
													$course    = '';
													if ( $post_type === tutor()->course_post_type ) {
														$course = get_the_title( $meeting->post_parent );
													} else {
														// Get topic title.
														$course_id = get_post_parent( $meeting->post_parent );
														$course    = get_the_title( $course_id );
													}
													echo esc_html( $course );
													?>
												</span>
											</div>
										</div>
									</td>
									<td>
										<div>
											<?php echo esc_html( $event_details->meet_link ); ?>
										</div>
									</td>
									<td>
										<div class="tutor-d-flex tutor-align-center tutor-justify-end">
											<div class="tutor-d-inline-flex tutor-align-center td-action-btns">
												<?php
												$btn_class = 'tutor-btn-outline-primary';
												if ( 'start_meeting' === $meeting_status ) {
													$btn_class = 'tutor-btn-primary tutor-ws-nowrap';
												}
												?>
												<a href="<?php echo esc_url( $event_details->html_link ); ?>" class="tutor-btn tutor-btn-md tutor-mr-12 <?php echo esc_attr( $btn_class ); ?>" target="_blank" <?php echo esc_attr( 'expired' === $meeting_status ? 'disabled' : '' ); ?>><i class="tutor-icon-brand-google-meet tutor-mr-8"></i>
													<?php echo esc_html( Utilities::meeting_status()[ $meeting_status ] ); ?>
												</a>
												<a href="#" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-4" data-tutor-modal-target="tutor-google-meet-modal-<?php echo esc_attr( $meeting->ID ); ?>">
													<?php esc_html_e( 'Edit', 'tutor-pro' ); ?>
												</a>
												<a href="#" class="tutor-iconic-btn tutor-google-meet-list-delete" data-event-id="<?php echo esc_attr( $event_details->id ); ?>" data-meeting-post-id="<?php echo esc_attr( $meeting->ID ); ?>" data-item-reference="tutor-google-meet-list-item-<?php echo esc_attr( $meeting->ID ); ?>" data-tutor-modal-target="tutor-common-confirmation-modal">
													<i class="tutor-icon-trash-can-line" area-hidden="true"></i>
												</a>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
								<?php else : ?>
									<tr>
										<td colspan="100%">
											<?php
												tutor_utils()->tutor_empty_state(
													__( 'No records found', 'tutor-pro' )
												);
											?>
										</td>
									</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<!-- pagination  -->
			<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
				<?php
				/**
				 * Prepare pagination data & load template
				 */
				if ( $meetings['total_found'] > $posts_per_page ) {
					$pagination_data     = array(
						'total_items' => $meetings['total_found'],
						'per_page'    => $posts_per_page,
						'paged'       => $current_page,
					);
					$pagination_template = tutor()->path . 'views/elements/pagination.php';
					tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
				}
				?>
			</div>    
			<!-- pagination end -->
			<?php wp_reset_postdata(); ?>
		</div>
	</div>
	<?php else : ?>
		<?php require 'frontend-meetings.php'; ?>
	<?php endif; ?>
	<?php
	// Edit/delete modal.
	if ( is_array( $meetings['meetings'] ) && count( $meetings['meetings'] ) ) {
		foreach ( $meetings['meetings'] as $meeting ) {
			tutor_load_template_from_custom_path(
				$plugin_data['views'] . 'modal/dynamic-modal-content.php',
				array(
					'post-id'  => $meeting->ID,
					'modal_id' => 'tutor-google-meet-modal-' . $meeting->ID,
				),
				false
			);
		}
	}

	// Delete confirmation modal.
	tutor_load_template_from_custom_path(
		tutor()->path . 'views/elements/common-confirm-popup.php',
		array(
			'message'           => __(
				'Do you want to delete? Google event will be deleted permanently.',
				'tutor-pro'
			),
			'additional_fields' => array(
				'event-id',
				'item-reference',
			),
			'disable_action_field' => true,
		),
		false
	);
	?>

</div>
