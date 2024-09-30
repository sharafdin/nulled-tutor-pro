<?php
/**
 * Course List Template.
 *
 * @package Course List
 */

use TUTOR\Course_List;
use TUTOR_REPORT\Analytics;
use TUTOR_REPORT\CourseAnalytics;

$courses = new Course_List();

?>
<div class="tutor-admin-page-wrapper" id="tutor-report-courses-wrap">
	<div class="tutor-mx-n20">
		<?php
		/**
		 * Load Templates with data.
		 */
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $filters_template, $filters );
		?>
	</div>

	<div class="tutor-report-courses-data-table tutor-mt-24">
		<?php if ( $the_query->have_posts() ) : ?>
			<div class="tutor-table-responsive">
				<table class="tutor-table table-popular-courses">
					<thead>
						<tr>
							<th width="40%">
								<?php esc_html_e( 'Course', 'tutor-pro' ); ?>
							</th>
							<th width="12%">
								<?php esc_html_e( 'Lesson', 'tutor-pro' ); ?>
							</th>
							<th width="12%">
								<?php esc_html_e( 'Assignment', 'tutor-pro' ); ?>
							</th>
							<th width="12%">
								<?php esc_html_e( 'Total Learners', 'tutor-pro' ); ?>
							</th>
							<th width="12%">
								<?php esc_html_e( 'Earnings', 'tutor-pro' ); ?>
							</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ( $the_query->posts as $course ) : ?>
							<?php
								$count_lesson     = tutor_utils()->get_lesson_count_by_course( $course->ID );
								$count_assignment = tutor_utils()->get_assignments_by_course( $course->ID )->count;
								$student_details  = CourseAnalytics::course_enrollments_with_student_details( $course->ID );
								$total_student    = $student_details['total_enrollments'];
								$earnings         = Analytics::get_earnings_by_user( 0, '', '', '', $course->ID )['total_earnings'];
							?>
							<tr>
								<td>
									<a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>">
										<?php echo esc_html( $course->post_title ); ?>
									</a>
								</td>
								<td>
									<?php echo esc_html( $count_lesson ); ?>
								</td>
								<td>
									<?php echo esc_html( $count_assignment ); ?>
								</td>
								<td>
									<?php echo esc_html( $total_student ); ?>
								</td>
								<td>
									<?php echo wp_kses_post( tutor_utils()->tutor_price( $earnings ) ); ?>
								</td>
								<td>
									<div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-1">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor_report&sub_page=courses&course_id=' . $course->ID ) ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
											<?php esc_html_e( 'Details', 'tutor-pro' ); ?>
										</a>
										<a href="<?php echo esc_url( get_permalink( $course->course_id ) ); ?>" class="tutor-iconic-btn" target="_blank">
											<span class="tutor-icon-external-link"></span>
										</a>
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
	</div>

	<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
		<?php
		/**
		 * Prepare pagination data & load template
		 */
		if ( $the_query->found_posts > $limit ) {
			$pagination_data     = array(
				'total_items' => $the_query->found_posts,
				'per_page'    => $limit,
				'paged'       => $paged_filter,
			);
			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
		}
		?>
	</div>
</div>
