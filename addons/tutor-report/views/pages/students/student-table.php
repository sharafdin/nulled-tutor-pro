<?php
/**
 * Student list template
 *
 * @package Report
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="tutor-report-students" class="tutor-report-common">
	<div class="tutor-mx-n20">
		<?php tutor_load_template_from_custom_path( $filters_template, $filters ); ?>
	</div>

	<div class="tutor-report-students-data-table tutor-mt-24">
		<?php if ( is_array( $lists ) && count( $lists ) ) : ?>
			<div class="tutor-table-responsive">
				<table class="tutor-table tutor-table-middle table-dashboard-course-list">
					<thead>
						<tr>
							<th>
								<div class="tutor-d-flex">
									<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
								</div>
							</th>
							<th class="tutor-table-rows-sorting" width="40%">
								<?php esc_html_e( 'Name', 'tutor-pro' ); ?>
								<span class="tutor-icon-ordering-a-z a-to-z-sort-icon tutor-color-secondary"></span>
							</th>
							<th>
								<?php esc_html_e( 'Email', 'tutor-pro' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Registration Date', 'tutor-pro' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Course Taken', 'tutor-pro' ); ?>
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $lists as $student ) : ?>
							<tr>
								<td>
									<div class="td-checkbox tutor-d-flex ">
										<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" value="<?php echo esc_attr( $student->ID ); ?>"/>
									</div>
								</td>
								<td>
									<div class="tutor-d-flex tutor-align-center tutor-gap-2">
										<?php echo tutor_utils()->get_tutor_avatar( $student->ID ); ?>
										<?php echo esc_html( $student->display_name ); ?>
										<a href="<?php echo esc_url( tutor_utils()->profile_url( $student->ID, false ) ); ?>" class="tutor-iconic-btn" target="_blank">
											<span class="tutor-icon-external-link" area-hidden="true"></span>
										</a>
									</div>
								</td>
								<td>
									<span class="tutor-fs-7"><?php echo esc_html( $student->user_email ); ?></span>
								</td>
								<td>
									<div class="tutor-fs-7">
										<?php echo esc_html( tutor_i18n_get_formated_date( $student->user_registered, get_option( 'date_format' ) ) ); ?>,
										<div class="tutor-color-muted"><?php echo esc_html( tutor_i18n_get_formated_date( $student->user_registered, get_option( 'time_format' ) ) ); ?></div>
									</div>
								</td>
								<td>
									<?php echo esc_html( $student->course_taken ); ?>
								</td>
								<td>
									<div class="tutor-text-right">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor_report&sub_page=students&student_id=' . $student->ID ) ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
											<?php _e("Details", "tutor-pro"); ?>
										</a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>
	<div class="tutor-report-students-data-table-pagination tutor-report-content-common-pagination tutor-mt-32">
		<?php
		if($total_items > $item_per_page) {
			$pagination_data     = array(
				'base'        => str_replace( 1, '%#%', 'admin.php?page=tutor_report&sub_page=students&paged=%#%' ),
				'per_page'    => $item_per_page,
				'paged'       => $current_page,
				'total_items' => $total_items,
			);
			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
		}
		?>
	</div> <!-- tutor-report-overview-data-table-pagination  -->
</div> <!-- tutor-report-sales -->


