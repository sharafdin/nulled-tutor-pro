<?php
/**
 * Frontend meetings template
 *
 * @since v2.1.0
 *
 * @package TutorPro\GoogleMeet\Views
 */

use TutorPro\GoogleMeet\Utilities\Utilities;

?>
<div class="tutor-google-meet-meetings tutor-mt-32" id="tutor-google-meet-meta-box-wrapper">
	<?php
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $filters_template, $filters );
	?>
	<div class="tutor-mt-32">
		<div class="tutor-table-responsive">
			<table class="tutor-table tutor-table-google-meet-meeting">
				<thead>
					<tr>
						<th class="tutor-table-rows-sorting">
							<?php esc_html_e( 'Start Time', 'tutor-pro' ); ?>
							<span class="a-to-z-sort-icon tutor-icon-ordering-z-a"></span>
						</th>
						<th class="">
							<?php esc_html_e( 'Meeting Title', 'tutor-pro' ); ?>
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
								<td class="tutor-td-middle">
									<div class="tutor-d-flex tutor-align-center tutor-justify-end">
										<div class="tutor-d-inline-flex tutor-align-center td-action-btns">
											<?php
											$btn_class = 'tutor-btn-outline-primary';
											if ( 'start_meeting' === $meeting_status ) {
												$btn_class = 'tutor-btn-primary';
											}
											?>
											<a href="<?php echo esc_url( $event_details->html_link ); ?>" class="tutor-btn tutor-btn-md tutor-mr-12 <?php echo esc_attr( $btn_class ); ?>" target="_blank" <?php echo esc_attr( 'expired' === $meeting->meeting_status ? 'disabled' : '' ); ?>><i class="tutor-icon-brand-google-meet tutor-mr-8"></i>
												<?php echo esc_html( Utilities::meeting_status()[ $meeting_status ] ); ?>
											</a>
											<div class="tutor-dropdown-parent">
												<button class="tutor-btn tutor-btn-outline-primary tutor-btn-md" action-tutor-dropdown="toggle">
													<span><?php esc_html_e( 'Info', 'tutor-pro' ); ?></span>
													<span class="tutor-icon-angle-down tutor-fs-7 tutor-ml-4" area-hidden="true"></span>
												</button>

												<ul class="tutor-dropdown" style="width: 280px;">
													<div class="tutor-d-flex tutor-align-center tutor-px-24 tutor-py-12">
														<div>
															<div class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'Meeting Link', 'tutor-pro' ); ?></div>
															<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
																<span id="tutor-google-meet-link-<?php echo esc_attr( $meeting->ID ); ?>" style="display:none;">
																	<?php echo esc_html( $event_details->meet_link ); ?>
																</span>
																<?php echo esc_html( substr( $event_details->meet_link, 0, '23' ) . '...' ); ?>
															</div>
														</div>
														<div class="tutor-ml-auto">
															<button class="tutor-iconic-btn" data-tutor-copy-target="tutor-google-meet-link-<?php echo esc_attr( $meeting->ID ); ?>">
																<span class="tutor-icon-copy-text" area-hidden="true"></span>
															</button>
														</div>
													</div>

													<div class="tutor-hr" area-hidden="true"></div>

													<div class="tutor-px-24 tutor-py-12 tutor-mt-8">
														<div class="tutor-fs-7 tutor-color-muted"><?php esc_html_e( 'Host Email', 'tutor-pro' ); ?></div>
														<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-nowrap-ellipsis" title="">
															<?php echo esc_html( $event_details->organizer->email ); ?>
														</div>
													</div>
												</ul>
											</div>
											<div class="tutor-dropdown-parent">
												<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
													<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
												</button>
												<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
													<li>
														<a href="#" class="tutor-dropdown-item" data-tutor-modal-target="tutor-google-meet-modal-<?php echo esc_attr( $meeting->ID ); ?>">
															<i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
															<spa><?php esc_html_e( 'Edit', 'tutor-pro' ); ?></span>
														</a>
													</li>
													<li>
														<a href="#" class="tutor-dropdown-item tutor-google-meet-list-delete" data-event-id="<?php echo esc_attr( $event_details->id ); ?>" data-meeting-post-id="<?php echo esc_attr( $meeting->ID ); ?>" data-item-reference="tutor-google-meet-list-item-<?php echo esc_attr( $meeting->ID ); ?>" data-tutor-modal-target="tutor-common-confirmation-modal">
															<i class="tutor-icon-trash-can-bold tutor-mr-8 tutor-gm-delete" area-hidden="true"></i>
															<span class="tutor-gm-delete"><?php esc_html_e( 'Delete', 'tutor-pro' ); ?></span>
														</a>
													</li>
												</ul>
											</div>
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
				$pagination_data = array(
					'total_items' => $meetings['total_found'],
					'per_page'    => $posts_per_page,
					'paged'       => $current_page,
				);

				tutor_load_template_from_custom_path(
					tutor()->path . 'templates/dashboard/elements/pagination.php',
					$pagination_data
				);
			}
			?>
		</div>    
		<!-- pagination end -->
		<?php wp_reset_postdata(); ?>
	</div>
</div>
