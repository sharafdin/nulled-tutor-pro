<?php
/**
 * Course Details Template
 *
 * @package Report
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Models\CourseModel;
use Tutor\Models\QuizModel;
use TUTOR_REPORT\Analytics;

?>

<div id="tutor-report-courses-details-wrap">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
		<?php echo esc_html( get_the_title( $current_id ) ); ?>
	</div>

	<div class="tutor-row tutor-align-center tutor-mt-8">
		<div class="tutor-col-lg tutor-mb-12 tutor-mb-lg-0">
			<div class="tutor-meta">
				<span>
					<?php esc_html_e( 'Created', 'tutor-pro' ); ?>:
					<span class="tutor-meta-value"><?php echo esc_html( get_the_date( get_option( 'date_format' ), $current_id ) ); ?></span>
				</span>

				<span>
					<span class="tutor-icon-refresh tutor-meta-icon"></span>
					<?php esc_html_e( 'Last Update', 'tutor-pro' ); ?>:
					<span class="tutor-meta-value"><?php echo esc_html( get_the_modified_date( get_option( 'date_format' ), $current_id ) ); ?></span>
				</span>
			</div>
		</div>

		<div class="tutor-col-lg-auto">
			<a href="<?php echo esc_url( get_edit_post_link( $current_id ) ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-mr-16" target="_blank">
				<?php esc_html_e( 'Edit with Builder', 'tutor-pro' ); ?>
			</a>
			<a href="<?php echo esc_url( get_the_permalink( $current_id ) ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-md" target="_blank">
				<?php esc_html_e( 'View Course', 'tutor-pro' ); ?>
			</a>
		</div>
	</div>

	<div class="tutor-card tutor-course-report-stats-card tutor-mt-32 tutor-mb-24">
		<div class="tutor-card-list tutor-card-list-horizontal">
			<div class="tutor-card-list-item tutor-p-16">
				<div class="tutor-fs-5 tutor-fw-bold tutor-color-black">
					<?php
						$info_lesson = tutor_utils()->get_lesson_count_by_course( $current_id );
						echo esc_html( $info_lesson );
					?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary">
					<?php esc_html_e( 'Lessons', 'tutor-pro' ); ?>
				</div>
			</div>

			<div class="tutor-card-list-item tutor-p-16">
				<div class="tutor-fs-5 tutor-fw-bold tutor-color-black">
					<?php
						$info_quiz = '';
					if ( $current_id ) {
						$info_quiz = QuizModel::get_quiz_count_by_course( $current_id );
					}
						echo esc_html( $info_quiz );
					?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary">
					<?php esc_html_e( 'Quizzes', 'tutor-pro' ); ?>
				</div>
			</div>

			<div class="tutor-card-list-item tutor-p-16">
				<div class="tutor-fs-5 tutor-fw-bold tutor-color-black">
					<?php
						$info_assignment = tutor_utils()->get_assignments_by_course( $current_id )->count;
						echo esc_html( $info_assignment );
					?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary">
					<?php esc_html_e( 'Assignments', 'tutor-pro' ); ?>
				</div>
			</div>
			
			<div class="tutor-card-list-item tutor-p-16">
				<div class="tutor-fs-5 tutor-fw-bold tutor-color-black">
					<?php
						$info_students = tutor_utils()->count_enrolled_users_by_course( $current_id );
						echo esc_html( $info_students );
					?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary">
					<?php esc_html_e( 'Students', 'tutor-pro' ); ?>
				</div>
			</div>
			
			<div class="tutor-card-list-item tutor-p-16">
				<div class="tutor-fs-5 tutor-fw-bold tutor-color-black">
					<?php echo esc_html( $complete_data ); ?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary">
					<?php esc_html_e( 'Courses Completed', 'tutor-pro' ); ?>
				</div>
			</div>
			
			<div class="tutor-card-list-item tutor-p-16">
				<div class="tutor-fs-5 tutor-fw-bold tutor-color-black">
					<?php
						$total_student = tutor_utils()->count_enrolled_users_by_course( $current_id );
						echo esc_html( $total_student - $complete_data );
					?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary">
					<?php esc_html_e( 'Courses in Progress', 'tutor-pro' ); ?>
				</div>
			</div>

			<div class="tutor-card-list-item tutor-p-16">
				<div class="tutor-fs-5 tutor-fw-bold tutor-color-black">
					<?php
						$course_rating = tutor_utils()->get_course_rating( $current_id );
						tutor_utils()->star_rating_generator( $course_rating->rating_avg );
					?>
				</div>
				<div class="tutor-fs-7 tutor-color-secondary">
					<?php echo esc_html( number_format( $course_rating->rating_avg, 2 ) ); ?>
					(<?php printf( _n( '%s Rating', '%s Ratings', $course_rating->rating_count, 'tutor-pro' ), $course_rating->rating_count ); ?>)
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-analytics-wrapper tutor-analytics-graph tutor-mt-12">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-d-flex tutor-align-center tutor-justify-between tutor-mb-24">
			<div>
				<?php esc_html_e( 'Earning graph', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-admin-report-frequency-wrapper" style="min-width: 260px;">
				<?php tutor_load_template_from_custom_path( TUTOR_REPORT()->path . 'templates/elements/frequency.php' ); ?>
				<div class="tutor-v2-date-range-picker inactive" style="width: 305px; position:absolute; z-index: 99;"></div>
			</div>
		</div>
		<div class="tutor-overview-month-graph">
			<?php
				/**
				 * Get analytics data
				 * sending user_id 0 for getting all data
				 *
				 * @since 1.9.9
				 */
				$user_id     = get_current_user_id();
				$course_id   = isset( $_GET['course_id'] ) ? $_GET['course_id'] : null;
				$earnings    = Analytics::get_earnings_by_user( 0, $time_period, $start_date, $end_date, $course_id );
				$enrollments = Analytics::get_total_students_by_user( 0, $time_period, $start_date, $end_date, $course_id );
				$discounts   = Analytics::get_discounts_by_user( 0, $time_period, $start_date, $end_date, $course_id );
				$refunds     = Analytics::get_refunds_by_user( 0, $time_period, $start_date, $end_date, $course_id );
				/* translators: %s: frequencies */
				$content_title  = sprintf( __( 'for %s', 'tutor-pro' ), $frequencies[ $current_frequency ] );
				$graph_tabs     = array(
					array(
						'tab_title'     => __( 'Total Earning', 'tutor-pro' ),
						'tab_value'     => $earnings['total_earnings'],
						'data_attr'     => 'ta_total_earnings',
						'active'        => ' is-active',
						'price'         => true,
						/* translators: %s: content title */
						'content_title' => sprintf( __( 'Earnings Chart %s', 'tutor-pro' ), $content_title ),
					),
					array(
						'tab_title'     => __( 'Course Enrolled', 'tutor-pro' ),
						'tab_value'     => $enrollments['total_enrollments'],
						'data_attr'     => 'ta_total_course_enrolled',
						'active'        => '',
						'price'         => false,
						/* translators: %s: content title */
						'content_title' => sprintf( __( 'Course Enrolled Chart %s', 'tutor-pro' ), $content_title ),
					),
					array(
						'tab_title'     => __( 'Total Refund', 'tutor-pro' ),
						'tab_value'     => $refunds['total_refunds'],
						'data_attr'     => 'ta_total_refund',
						'active'        => '',
						'price'         => true,
						/* translators: %s: content title */
						'content_title' => sprintf( __( 'Refund Chart %s', 'tutor-pro' ), $content_title ),
					),
					array(
						'tab_title'     => __( 'Total Discount', 'tutor-pro' ),
						'tab_value'     => $discounts['total_discounts'],
						'data_attr'     => 'ta_total_discount',
						'active'        => '',
						'price'         => true,
						/* translators: %s: content title */
						'content_title' => sprintf( __( 'Discount Chart %s', 'tutor-pro' ), $content_title ),
					),
				);
				$graph_template = TUTOR_REPORT()->path . 'templates/elements/graph.php';
				tutor_load_template_from_custom_path( $graph_template, $graph_tabs );
				?>
		</div>
	</div>

	<?php
	tutor_load_template_from_custom_path(
		TUTOR_REPORT()->path . 'templates/elements/course-students.php',
		array(
			'course_id'    => $course_id,
			'student_list' => $student_list,
			'details_url'  => admin_url( 'admin.php?page=tutor_report&sub_page=students&student_id=' ),
			'pagination'   => array(
				'base'        => 'admin.php?page=tutor_report&sub_page=courses&course_id=' . $course_id . '&lp=%#%',
				'per_page'    => tutils()->get_option( 'pagination_per_page' ),
				'paged'       => max( 1, $student_page ),
				'total_items' => tutils()->count_enrolled_users_by_course( $course_id ),
			),
		)
	);
	?>

	<div id="tutor-course-details-instructor-list" class="tutor-mb-48">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
			<?php esc_html_e( 'Instructors', 'tutor-pro' ); ?>
		</div>
		<div class="tutor-course-details-instructor-list-table">
			<?php if ( is_array( $instructors ) && count( $instructors ) ) : ?>
				<div class="tutor-table-responsive">
					<table class="tutor-table tutor-table-middle table-students">
						<thead>
							<tr>
								<th width="20%">
									<?php esc_html_e( 'Teacher', 'tutor-pro' ); ?>
								</th>
								<th width="20%">
									<?php esc_html_e( 'Courses', 'tutor-pro' ); ?>
								</th>
								<th width="20%">
									<?php esc_html_e( 'Students', 'tutor-pro' ); ?>
								</th>
								<th width="25%">
									<?php esc_html_e( 'Rating', 'tutor-pro' ); ?>
								</th>
								<th></th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ( $instructors as $instructor ) : ?>
								<?php
									$crown     = false;
									$user_info = get_userdata( $instructor->ID );
								if ( ! $user_info ) {
									continue;
								}
								if ( get_post_field( 'post_author', $instructor->ID ) == $instructor->ID ) {
									$crown = true;
								}
								?>
								<tr>
									<td>
										<div class="tutor-d-flex tutor-align-center tutor-gap-2">
											<?php echo tutor_utils()->get_tutor_avatar( $instructor->ID ); ?>
											<div>
												<div class="tutor-d-flex">
													<?php echo esc_html( $instructor->display_name ); ?>
													<?php if ( $crown ) : ?>
														<a class="tutor-ml-4 tutor-d-flex">
															<span class="tutor-icon-crown tutor-color-warning"></span>
														</a>
													<?php endif; ?>
													<a href="<?php echo esc_url( tutor_utils()->profile_url( $user_info->ID, true ) ); ?>" class="tutor-iconic-btn tutor-ml-4">
														<span class="tutor-icon-external-link"></span>
													</a>
												</div>
												<div class="tutor-fs-7 tutor-fw-normal tutor-color-muted">
													<?php echo esc_html( $user_info->user_email ); ?>
												</div>
											</div>
										</div>
									</td>
									<td>
										<?php echo esc_html( CourseModel::get_course_count_by_instructor( $instructor->ID ) ); ?>
									</td>
									<td>
										<?php echo esc_html( tutor_utils()->get_total_students_by_instructor( $instructor->ID ) ); ?>
									</td>
									<td>
										<div class="tutor-ratings">
											<?php
												$rating = tutor_utils()->get_instructor_ratings( $instructor->ID );
												tutor_utils()->star_rating_generator( $rating->rating_avg );
											?>
											<div class="tutor-ratings-count">
												<?php echo esc_html( number_format( $rating->rating_avg, 2 ) ); ?>
											</div>
										</div>
									</td>
									<td>
										<div class="tutor-text-right">
											<a href="<?php echo esc_url( tutor_utils()->profile_url( $instructor->ID, true ) ); ?>" class="tutor-btn tutor-btn-primary" target="_blank">
												<?php _e( 'View Profile', 'tutor-pro' ); ?>
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
	</div>

	<div id="tutor-course-details-review-section" class="tutor-mb-48">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
			<?php esc_html_e( 'Reviews', 'tutor-pro' ); ?>
		</div>
		<?php if ( is_array( $total_reviews ) && count( $total_reviews ) ) : ?>
			<div class="tutor-table-responsive tutor-mb-48">
				<table id="tutor-admin-reviews-table" class="tutor-table tutor-table-middle">
					<thead>
						<tr>
							<th width="20%">
								<?php esc_html_e( 'Student', 'tutor-pro' ); ?>
							</th>
							<th width="20%">
								<?php esc_html_e( 'Date', 'tutor-pro' ); ?>
							</th>
							<th width="45%">
								<?php esc_html_e( 'Feedback', 'tutor-pro' ); ?>
							</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ( $total_reviews as $review ) : ?>
							<tr>
								<td>
									<div class="tutor-d-flex tutor-align-center tutor-gap-2">
										<?php echo tutor_utils()->get_tutor_avatar( $review->user_id ); ?>
										<div>
											<div class="tutor-d-flex">
												<?php echo esc_html( $review->display_name ); ?>
												<a href="<?php echo esc_url( tutor_utils()->profile_url( $review->user_id, false ) ); ?>" class="tutor-iconic-btn tutor-ml-4">
													<span class="tutor-icon-external-link"></span>
												</a>
											</div>
											<div class="tutor-fs-7 tutor-fw-normal tutor-color-muted">
												<?php echo esc_html( $user_info->user_email ); ?>
											</div>
										</div>
									</div>
								</td>
								<td>
									<?php echo esc_html( tutor_i18n_get_formated_date( $review->comment_date, get_option( 'date_format' ) ) ); ?>,
									<div class="tutor-fs-7 tutor-color-muted"><?php echo esc_html( tutor_i18n_get_formated_date( $review->comment_date, get_option( 'time_format' ) ) ); ?></div>
								</td>
								<td>
									<div class="tutor-ratings">
										<?php tutor_utils()->star_rating_generator( $review->rating ); ?>
										<div class="tutor-ratings-count tutor-ml-12">
											<?php echo esc_html( number_format( $review->rating, 2 ) ); ?>
										</div>
									</div>
									<div class="tutor-fs-7 tutor-fw-normal tutor-color-muted">
										<?php echo wp_kses_post( $review->comment_content ); ?>
									</div>
								</td>
								<td class="tutor-text-right">
									<a data-tutor-modal-target="tutor-common-confirmation-modal" class="tutor-btn tutor-btn-outline-primary tutor-delete-recent-reviews" data-id="<?php echo esc_attr( $review->comment_ID ); ?>">
										<?php esc_html_e( 'Delete', 'tutor-pro' ); ?>
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

		<?php
			$review_pagination_data = array(
				'base'        => 'admin.php?page=tutor_report&sub_page=courses&course_id=' . $current_id . '&rp=%#%',
				'per_page'    => $per_review,
				'paged'       => max( 1, $review_page ),
				'total_items' => $review_items,
			);
			tutor_load_template_from_custom_path( tutor()->path . 'views/elements/pagination.php', $review_pagination_data, false );
			?>
	</div>
</div>
<?php
/**
 * Add delete confirmation modal
 *
 * @since 2.1.8
 */
tutor_load_template_from_custom_path( tutor()->path . 'views/elements/common-confirm-popup.php' );
?>
