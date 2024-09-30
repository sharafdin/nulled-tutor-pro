<?php
/**
 * Tutor Report Overview page
 *
 * @package TutorPro\Report
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 */

use \TUTOR_REPORT\Analytics;

?>

<div class="tutor-report-overview-wrap">
	<div class="tutor-row tutor-gx-4">
		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-mortarboard-o" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalCourse; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Published Courses', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-add-member" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalCourseEnrolled; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Course Enrolled', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-book-open" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalLesson; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Lessons', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-quiz" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalQuiz; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Quiz', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-question" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalQuestion; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Questions', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	
		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-user-bold" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalInstructor; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Instructors', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-user-graduate" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalStudents; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Students', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
			<div class="tutor-card tutor-card-secondary tutor-p-24">
				<div class="tutor-d-flex">
					<div class="tutor-round-box">
						<span class="tutor-icon-star-bold" area-hidden="true"></span>
					</div>

					<div class="tutor-ml-20">
						<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo $totalReviews; ?></div>
						<div class="tutor-fs-7 tutor-color-secondary"><?php _e( 'Reviews', 'tutor-pro' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-analytics-wrapper tutor-analytics-graph tutor-mt-12">

		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-d-flex tutor-align-center tutor-justify-between tutor-mb-16">
			<div>
				<?php esc_html_e( 'Earning graph', 'tutor-pro' ); ?>
			</div>
			<div class="tutor-admin-report-frequency-wrapper" style="min-width: 260px;">
				<?php tutor_load_template_from_custom_path( TUTOR_REPORT()->path . 'templates/elements/frequency.php' ); ?>
				<div class="tutor-v2-date-range-picker inactive" style="width: 305px; position:absolute; z-index: 99;"></div>
			</div>
		</div>
		<div class="tutor-overview-month-graph">
			<!--analytics graph -->
			<?php
				/**
				 * Get analytics data
				 * sending user_id 0 for getting all data
				 *
				 * @since 1.9.9
				 */
				$user_id     = get_current_user_id();
				$earnings    = Analytics::get_earnings_by_user( 0, $time_period, $start_date, $end_date );
				$enrollments = Analytics::get_total_students_by_user( 0, $time_period, $start_date, $end_date );
				$discounts   = Analytics::get_discounts_by_user( 0, $time_period, $start_date, $end_date );
				$refunds     = Analytics::get_refunds_by_user( 0, $time_period, $start_date, $end_date );
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
			<!--analytics graph end -->
		</div>
	</div>

	<div class="tutor-mb-48" id="tutor-courses-overview-section">
		<div class="single-overview-section tutor-most-popular-courses">
			<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
				<?php esc_html_e( 'Most popular courses', 'tutor-pro' ); ?>
			</div>
			<?php if ( is_array( $most_popular_courses ) && count( $most_popular_courses ) ) : ?>
				<div class="tutor-table-responsive">
					<table class="tutor-table table-popular-courses">
						<thead>
							<tr>
								<th>
									<?php esc_html_e( 'Course Name', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Total Enrolled', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Rating', 'tutor-pro' ); ?>
								</th>
								<th></th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ( $most_popular_courses as $course ) : ?>
								<tr>
									<td>
										<?php echo esc_html( $course->post_title ); ?>
									</td>
									<td>
										<?php echo esc_html( $course->total_enrolled ); ?>
									</td>
									<td>
										<?php tutor_utils()->star_rating_generator_v2( isset( $course_rating->rating_avg ) ? $course_rating->rating_avg : 0, null, true ); ?>
									</td>
									<td>
										<a href="<?php echo esc_url( get_permalink( $course->course_id ) ); ?>" class="tutor-iconic-btn" target="_blank"><span class="tutor-icon-external-link"></span></a>
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

		<div class="single-overview-section tutor-last-enrolled-courses">
			<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24 tutor-mt-48">
				<?php esc_attr_e( 'Last enrolled courses', 'tutor-pro' ); ?>
			</div>
			<?php if ( is_array( $last_enrolled_courses ) && count( $last_enrolled_courses ) ) : ?>
				<div class="tutor-table-responsive">
					<table class="tutor-table table-popular-courses">
						<thead>
							<tr>
								<th>
									<?php esc_html_e( 'Course Name', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Date', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Rating', 'tutor-pro' ); ?>
								</th>
								<th></th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ( $last_enrolled_courses as $course ) : ?>
								<tr>
									<td>
										<?php echo esc_html( $course->post_title ); ?>
									</td>
									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( tutor_i18n_get_formated_date( $course->enrolled_time, get_option( 'date_format' ) ) ); ?>,
											<div class="tutor-fw-normal tutor-color-muted"><?php echo esc_html( tutor_i18n_get_formated_date( $course->enrolled_time, get_option( 'time_format' ) ) ); ?></div>
										</div>
									</td>
									<td>
										<?php tutor_utils()->star_rating_generator_v2( isset( $course_rating->rating_avg ) ? $course_rating->rating_avg : 0, null, true ); ?>
									</td>
									<td>
										<a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>" target="_blank" class="tutor-iconic-btn"><span class="tutor-icon-external-link"></span></a>
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

	<div id="tutor-courses-review-section" class="tutor-mb-48">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
			<?php esc_html_e( 'Recent Reviews', 'tutor-pro' ); ?>
		</div>
		<?php if ( is_array( $reviews ) && count( $reviews ) ) : ?>
			<div class="tutor-table-responsive">
				<table class="tutor-table tutor-table-middle tutor-table-report-tab-overview" id="tutor-admin-reviews-table">
					<thead>
						<tr>
							<th width="20%">
								<?php esc_html_e( 'Student', 'tutor-pro' ); ?>
							</th>
							<th width="10%">
								<?php esc_html_e( 'Date', 'tutor-pro' ); ?>
							</th>
							<th width="20%">
								<?php esc_html_e( 'Course', 'tutor-pro' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Feedback', 'tutor-pro' ); ?>
							</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ( $reviews as $review ) : ?>
							<tr>
								<td>
									<div class="tutor-d-flex tutor-align-center tutor-gap-1">
										<?php echo tutor_utils()->get_tutor_avatar( $review->user_id ); ?>
										<span>
											<?php echo esc_html( $review->display_name ); ?>
										</span>
										<a class="tutor-iconic-btn" href="<?php echo esc_url( tutor_utils()->profile_url( $review->user_id, false ) ); ?>"><span class="tutor-icon-external-link" area-hidden="true"></span></a>
									</div>
								</td>

								<td>
									<span class="tutor-fs-7">
										<?php echo esc_html( tutor_i18n_get_formated_date( $review->comment_date, get_option( 'date_format' ) ) ); ?>,
										<div class="tutor-color-muted tutor-mt-4"><?php echo esc_html( tutor_i18n_get_formated_date( $review->comment_date, get_option( 'time_format' ) ) ); ?></div>
									</span>
								</td>

								<td>
									<?php echo esc_html( get_the_title( $review->comment_post_ID ) ); ?>
								</td>

								<td>
									<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true ); ?>
									<div class="tutor-fw-normal tutor-color-muted tutor-mt-8"><?php echo esc_textarea( wp_unslash( $review->comment_content ) ); ?></div>
								</td>

								<td>
									<div class="tutor-d-flex tutor-align-center tutor-gap-1">
										<a data-tutor-modal-target="tutor-common-confirmation-modal" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-delete-recent-reviews" data-id="<?php echo esc_attr( $review->comment_ID ); ?>" style="cursor: pointer;">Delete</a>
										<a href="<?php echo esc_url( get_the_permalink( $review->comment_post_ID ) ); ?>" class="tutor-iconic-btn" target="_blank" >
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
			<?php tutor_utils()->tutor_empty_state(); ?>
		<?php endif; ?>
	</div>

	<div id="tutor-new-registered-section">
		<div class="single-new-registered-section">
			<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
				<?php esc_html_e( 'New Registered students', 'tutor-pro' ); ?>
			</div>
			<?php if ( is_array( $students ) && count( $students ) ) : ?>
				<div class="tutor-table-responsive">
					<table class="tutor-table tutor-table-middle">
						<thead>
							<tr>
								<th>
									<?php esc_html_e( 'Student', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Email', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Register at', 'tutor-pro' ); ?>
								</th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ( $students as $student ) : ?>
								<tr>
									<td>
										<div class="tutor-d-flex tutor-align-center tutor-gap-2">
											<?php echo tutor_utils()->get_tutor_avatar( $student->ID ); ?>
											<div class="tutor-fs-7">
												<?php echo esc_html( $student->display_name ); ?>
											</div>
											<a href="<?php echo esc_url( tutor_utils()->profile_url( $student->ID, false ) ); ?>" class="tutor-iconic-btn" target="_blank"><i class="tutor-icon-external-link" area-hidden="true"></i></a>
										</div>
									</td>

									<td>
										<span class="tutor-fs-7"><?php echo esc_html( $student->user_email ); ?></span>
									</td>

									<td>
										<span class="tutor-fs-7"><?php echo esc_html( tutor_i18n_get_formated_date( $student->user_registered ) ); ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<?php tutor_utils()->tutor_empty_state(); ?>
			<?php endif; ?>
		</div>

		<div class="single-new-registered-section">
			<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24 tutor-mt-48">
				<div class="heading">
					<?php esc_html_e( 'New Registered Teachers', 'tutor-pro' ); ?>
				</div>
			</div>
			<?php if ( is_array( $teachers ) && count( $teachers ) ) : ?>
				<div class="tutor-table-responsive">
					<table class="tutor-table tutor-table-middle">
						<thead>
							<tr>
								<th>
									<?php esc_html_e( 'Teacher', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Email', 'tutor-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Register at', 'tutor-pro' ); ?>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php foreach ( $teachers as $teacher ) : ?>
								<tr>
									<td>
										<div class="tutor-d-flex tutor-align-center tutor-gap-2">
											<?php echo tutor_utils()->get_tutor_avatar( $teacher->ID ); ?>
											<div class="tutor-fs-7">
												<?php echo esc_html( $teacher->display_name ); ?>
											</div>
											<a href="<?php echo esc_url( tutor_utils()->profile_url( $teacher->ID, true ) ); ?>" class="tutor-iconic-btn" target="_blank"><i class="tutor-icon-external-link" area-hidden="true"></i></a>
										</div>
									</td>

									<td>
										<span class="tutor-fs-7"><?php echo esc_html( $teacher->user_email ); ?></span>
									</td>

									<td>
										<span class="tutor-fs-7"><?php echo esc_html( tutor_i18n_get_formated_date( $teacher->user_registered ) ); ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<?php tutor_utils()->tutor_empty_state(); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php tutor_load_template_from_custom_path( tutor()->path . 'views/elements/common-confirm-popup.php' ); ?>
