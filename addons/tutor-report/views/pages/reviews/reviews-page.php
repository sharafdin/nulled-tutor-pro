<?php
	$available_status = array(
		'approved' => array(__( 'Published', 'tutor' ), 'select-success'),
		'hold' => array(__( 'Unpublished', 'tutor' ), 'select-warning'),
	);
?>

<div id="tutor-report-reviews" class="tutor-report-common">
	<?php if ( is_array( $reviews ) && count( $reviews ) ) : ?>
		<div class="tutor-table-responsive">
			<table class="tutor-table tutor-table-top tutor-table-report-tab-review" id="tutor-admin-reviews-table">
				<thead>
					<tr>
						<th class="tutor-table-rows-sorting" width="15%">
							<?php esc_html_e( 'Student', 'tutor-pro' ); ?>
							<span class="tutor-icon-ordering-a-z a-to-z-sort-icon"></span>
						</th>
						<th width="10%">
							<?php esc_html_e( 'Date', 'tutor-pro' ); ?>
						</th>
						<th width="20%">
							<?php esc_html_e( 'Course', 'tutor-pro' ); ?>
						</th>
						<th width="40%">
							<?php esc_html_e( 'Feedback', 'tutor-pro' ); ?>
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $reviews as $review ) : ?>
						<tr>
							<td>
								<div class="tutor-d-flex tutor-align-center tutor-gap-2">
									<?php echo tutor_utils()->get_tutor_avatar( $review->user_id ); ?>
									<?php echo esc_html( $review->display_name ); ?>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor_report&sub_page=students&student_id=' . $review->user_id ) ); ?>" class="tutor-iconic-btn">
										<span class="tutor-icon-external-link"></span>
									</a>
								</div>
							</td>
							
							<td>
								<div class="tutor-fs-7">
									<?php echo esc_html( tutor_i18n_get_formated_date( $review->comment_date, get_option( 'date_format' ) ) ); ?>,
									<div class="tutor-fw-normal tutor-color-muted"><?php echo esc_html( tutor_i18n_get_formated_date( $review->comment_date, get_option( 'time_format' ) ) ); ?></div>
								</div>
							</td>
							<td>
								<?php echo esc_html( get_the_title( $review->comment_post_ID ) ); ?>
							</td>
							<td>
								<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true ); ?>
								<div class="tutor-fw-normal tutor-color-secondary tutor-mt-8">
									<?php echo wp_unslash( $review->comment_content ); ?>
								</div>
							</td>
							<td>
								<div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-2">
									<div class="tutor-form-select-with-icon <?php echo $available_status[$review->comment_status][1]; ?>">
										<select title="<?php esc_attr_e( 'Update review status', 'tutor-pro' ); ?>" class="tutor-table-row-status-update" data-id="<?php echo esc_attr( $review->comment_ID ); ?>" data-status="<?php echo esc_attr( $review->comment_status ); ?>" data-status_key="status" data-action="tutor_change_review_status">
											<?php foreach ( $available_status as $key => $value ) : ?>
												<option data-status_class="<?php echo esc_attr( $value[1] ); ?>" value="<?php echo $key; ?>" <?php selected( $key, $review->comment_status, 'selected' ); ?>>
													<?php echo esc_html( $value[0] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
										<i class="icon1 tutor-icon-eye-bold"></i>
										<i class="icon2 tutor-icon-angle-down"></i>
									</div>
									<div class="tutor-dropdown-parent">
										<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
											<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
										</button>
										<div id="table-dashboard-review-list-<?php echo esc_attr( $review->comment_ID ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
											<a class="tutor-dropdown-item" href="<?php echo esc_url( get_permalink( $review->comment_post_ID ) ); ?>" target="_blank">
												<i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
												<span><?php esc_html_e( 'Preview', 'tutor' ); ?></span>
											</a>
											<a data-tutor-modal-target="tutor-common-confirmation-modal" class="tutor-dropdown-item tutor-admin-review-delete tutor-delete-recent-reviews" data-id="<?php echo esc_attr( $review->comment_ID ); ?>">
												<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
												<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
											</a>
										</div>
									</div>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>

	<div class="tutor-report-courses-data-table-pagination tutor-report-content-common-pagination tutor-mt-32">
		<?php
			if( $total_items > $per_page ){
				$pagination_data = array(
					'base'        => str_replace( $current_page, '%#%', 'admin.php?page=tutor_report&sub_page=reviews&paged=%#%' ),
					'total_items' => $total_items,
					'paged'       => max( 1, $current_page ),
					'per_page'    => $per_page,
				);

				tutor_load_template_from_custom_path( tutor()->path . 'views/elements/pagination.php', $pagination_data );
			}
		?>
	</div>
</div>

<?php tutor_load_template_from_custom_path( tutor()->path . 'views/elements/common-confirm-popup.php' ); ?>