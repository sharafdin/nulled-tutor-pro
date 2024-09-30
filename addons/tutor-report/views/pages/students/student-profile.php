<?php

/**
 * Student details template
 *
 * @package Report
 */

if (!defined('ABSPATH')) {
	exit;
}
?>

<div id="tutor-report-student-details" class="tutor-report-common">
	<div class="tutor-mb-24">
		<div class="tutor-row tutor-align-lg-center">
			<div class="tutor-col-auto">
				<?php echo tutor_utils()->get_tutor_avatar($user_info->ID, 'lg'); ?>
			</div>

			<div class="tutor-col">
				<div class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-8">
					<?php echo esc_html($user_info->display_name); ?>
				</div>

				<div class="tutor-row">
					<div class="tutor-col-lg tutor-mb-12 tutor-mb-lg-0">
						<div class="tutor-meta">
							<div>
								<?php esc_html_e('Email:', 'tutor-pro'); ?>
								<span class="tutor-meta-value"><?php echo esc_html($user_info->user_email); ?></span>
							</div>

							<div>
								<?php esc_html_e('User Name:', 'tutor-pro'); ?>
								<span class="tutor-meta-value"><?php echo esc_html($user_info->user_login); ?></span>
							</div>

							<div>
								<?php esc_html_e('Registered at:', 'tutor-pro'); ?>
								<span class="tutor-meta-value"><?php echo esc_html(tutor_i18n_get_formated_date($user_info->user_registered, get_option('date_format'))); ?></span>
							</div>
						</div>
					</div>

					<div class="tutor-col-lg-auto">
						<a href="<?php echo esc_url(tutor_utils()->profile_url($user_info->ID, false)); ?>" class="tutor-btn tutor-btn-primary tutor-btn-md" target="_blank">
							<?php esc_html_e('View Profile', 'tutor-pro'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="student-report-stats" class="report-stats tutor-mb-24">
		<div class="tutor-row tutor-gx-4">
			<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
				<div class="tutor-card tutor-card-secondary tutor-p-24">
					<div class="tutor-d-flex">
						<div class="tutor-round-box">
							<span class="tutor-icon-mortarboard-o" area-hidden="true"></span>
						</div>

						<div class="tutor-ml-20">
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black"><?php echo esc_html($enrolled_course->found_posts ?? 0 ); ?></div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('Enrolled Courses', 'tutor-pro'); ?></div>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
				<div class="tutor-card tutor-card-secondary tutor-p-24">
					<div class="tutor-d-flex">
						<div class="tutor-round-box">
							<span class="tutor-icon-trophy" area-hidden="true"></span>
						</div>

						<div class="tutor-ml-20">
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black">
								<?php
								$completed_course = tutor_utils()->get_completed_courses_ids_by_user($user_info->ID);
								echo esc_html(count($completed_course));
								?>
							</div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('Completed Courses', 'tutor-pro'); ?></div>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
				<div class="tutor-card tutor-card-secondary tutor-p-24">
					<div class="tutor-d-flex">
						<div class="tutor-round-box">
							<span class="tutor-icon-flag" area-hidden="true"></span>
						</div>

						<div class="tutor-ml-20">
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black">
								<?php echo esc_html(( ($enrolled_course->found_posts ?? 0) - count($completed_course))); ?>
							</div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('In Progress Courses', 'tutor-pro'); ?></div>
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
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black">
								<?php
								$review_items = count(tutor_utils()->get_reviews_by_user($user_info->ID));
								echo esc_html($review_items);
								?>
							</div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('Reviews Placed', 'tutor-pro'); ?></div>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
				<div class="tutor-card tutor-card-secondary tutor-p-24">
					<div class="tutor-d-flex">
						<div class="tutor-round-box">
							<span class="tutor-icon-document-text" area-hidden="true"></span>
						</div>

						<div class="tutor-ml-20">
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black">
								<?php
								echo esc_html($lesson);
								?>
							</div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('Total Lessons', 'tutor-pro'); ?></div>
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
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black">
								<?php echo esc_html(tutor_utils()->get_total_quiz_attempts($user_info->user_email)); ?>
							</div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('Quizzes Taken', 'tutor-pro'); ?></div>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-col-md-6 tutor-col-xl-3 tutor-my-8 tutor-my-md-16">
				<div class="tutor-card tutor-card-secondary tutor-p-24">
					<div class="tutor-d-flex">
						<div class="tutor-round-box">
							<span class="tutor-icon-assignment" area-hidden="true"></span>
						</div>

						<div class="tutor-ml-20">
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black">
								<?php
								echo esc_html($total_assignments);
								?>
							</div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('Assignments', 'tutor-pro'); ?></div>
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
							<div class="tutor-fs-4 tutor-fw-bold tutor-color-black">
								<?php
								echo esc_html($total_discussion);
								?>
							</div>
							<div class="tutor-fs-7 tutor-color-secondary"><?php _e('Questions', 'tutor-pro'); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="tutor-course-details-list" class="tutor-mb-48">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
			<?php esc_html_e('Course', 'tutor-pro'); ?>
		</div>
		<?php if ( isset( $enrolled_course->posts ) && is_array($enrolled_course->posts) && count($enrolled_course->posts)) : ?>
			<div class="tutor-course-details-student-list-table">
				<div class="tutor-table-responsive">
					<table class="tutor-table tutor-table-data-td-target">
						<thead>
							<tr>
								<th class="tutor-table-rows-sorting" width="30%">
									<?php esc_html_e('Course', 'tutor-pro'); ?>
									<span class="tutor-icon-ordering-a-z a-to-z-sort-icon"></span>
								</th>
								<th class="tutor-table-rows-sorting" width="15%">
									<?php esc_html_e('Enroll Date', 'tutor-pro'); ?>
									<span class="tutor-icon-order-down up-down-icon"></span>
								</th>
								<th class="tutor-table-rows-sorting" width="10%">
									<?php esc_html_e('Lesson', 'tutor-pro'); ?>
									<span class="tutor-icon-order-down up-down-icon"></span>
								</th>
								<th class="tutor-table-rows-sorting" width="10%">
									<?php esc_html_e('Quiz', 'tutor-pro'); ?>
									<span class="tutor-icon-order-down up-down-icon"></span>
								</th>
								<th class="tutor-table-rows-sorting" width="10%">
									<?php esc_html_e('Assignment', 'tutor-pro'); ?>
									<span class="tutor-icon-order-down up-down-icon"></span>
								</th>
								<th width="15%">
									<?php esc_html_e('Progress', 'tutor-pro'); ?>
								</th>
								<th></th>
							</tr>
						</thead>

						<tbody>
							<?php
							foreach ($enrolled_course->posts as $course) :
								$lessons     = tutor_utils()->get_course_content_list(tutor()->lesson_post_type, tutor()->course_post_type, $course->ID);
								$assignments = tutor_utils()->get_course_content_list('tutor_assignments', tutor()->course_post_type, $course->ID);
								$quizzes     = tutor_utils()->get_course_content_list('tutor_quiz', tutor()->course_post_type, $course->ID);

								$course_progress      = tutor_utils()->get_course_completed_percent($course->ID, $student_id);
								$total_lessons        = is_array($lessons) ? count($lessons) : 0;
								$completed_lessons    = tutor_utils()->get_completed_lesson_count_by_course($course->ID, $student_id);
								$total_assignments    = is_array($assignments) ? count($assignments) : 0;
								$completed_assignment = tutor_utils()->get_completed_assignment($course->ID, $student_id);
								$total_quiz           = is_array($quizzes) ? count($quizzes) : 0;
								$completed_quiz       = tutor_utils()->get_completed_quiz($course->ID, $student_id);
								$enrolled_data		  = tutor_utils()->get_enrolled_data($student_id, $course->ID);
							?>
								<tr>
									<td>
										<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
											<?php echo esc_html($course->post_title); ?>
										</div>
									</td>
									<td>
										<div class="tutor-color-black tutor-fs-7">
											<?php echo esc_html( tutor_i18n_get_formated_date( tutor_utils()->get_local_time_from_unix($enrolled_data->post_date_gmt ) ) ); ?>
										</div>
									</td>
									<td>
										<span class="tutor-color-black tutor-fs-7"><?php echo esc_html($completed_lessons); ?></span><span class="tutor-color-muted tutor-fs-7"><?php echo esc_html('/' . $total_lessons); ?></span>
									</td>
									<td>
										<span class="tutor-color-black tutor-fs-7"><?php echo esc_html($completed_quiz); ?></span><span class="tutor-color-muted tutor-fs-7"><?php echo esc_html('/' . $total_quiz); ?></span>
									</td>
									<td>
										<span class="tutor-color-black tutor-fs-7"><?php echo esc_html($completed_assignment); ?></span><span class="tutor-color-muted tutor-fs-7"><?php echo esc_html('/' . $total_assignments); ?></span>
									</td>
									<td>
										<div class="tutor-d-flex tutor-align-center">
											<div class="tutor-progress-bar" style="min-width: 50px; --tutor-progress-value:<?php echo esc_attr($course_progress); ?>%;">
												<div class="tutor-progress-value" area-hidden="true"></div>
											</div>
											<div class="tutor-fs-7 tutor-color-muted tutor-ml-12">
												<?php echo esc_attr($course_progress); ?>%
											</div>
										</div>
									</td>
									<td class="tutor-text-right expand-btn" data-th="Collapse">
										<button class="tutor-iconic-btn tutor-icon-angle-down tutor-fs-6 tutor-color-primary has-data-td-target" data-td-target="<?php echo esc_attr("tutor-student-course-$course->ID"); ?>"></button>
									</td>
								</tr>
								<tr>
									<td colspan="100%" class="column-empty-state data-td-content" id="<?php echo esc_attr("tutor-student-course-$course->ID"); ?>" style="display: none;">
										<div class="td-toggle-content tutor-container-fluid">
											<div class="td-list-item-wrapper tutor-row">
												<div class="tutor-col-md-4">
													<div class="list-item-title tutor-fs-6 tutor-color-black tutor-py-12">
														<?php esc_html_e('Lesson', 'tutor-pro'); ?>
													</div>
													<?php if (is_array($lessons) && count($lessons)) : ?>
														<?php foreach ($lessons as $lesson) : ?>
															<div class="list-item-checklist">
																<div class="tutor-form-check">
																	<?php
																	$is_lesson_completed = tutor_utils()->is_completed_lesson($lesson->ID, $user_info->ID);
																	?>
																	<input name="item-checklist-11" type="checkbox" class="tutor-form-check-input tutor-form-check-circle" readonly <?php echo esc_attr($is_lesson_completed ? 'checked' : ''); ?> onclick="return false;" />
																	<span class="tutor-fs-7 tutor-color-secondary">
																		<?php echo esc_html($lesson->post_title); ?>
																	</span>
																</div>
															</div>
														<?php endforeach; ?>
													<?php endif; ?>
												</div>
												<div class="tutor-col-md-4">
													<div class="list-item-title tutor-fs-6 tutor-color-black tutor-py-12">
														<?php esc_html_e('Quiz', 'tutor-pro'); ?>
													</div>
													<?php if (is_array($quizzes) && count($quizzes)) : ?>
														<?php foreach ($quizzes as $quiz) : ?>
															<div class="list-item-checklist">
																<div class="tutor-form-check">
																	<?php
																	$has_attempted = tutor_utils()->has_attempted_quiz($user_info->ID, $quiz->ID);
																	?>
																	<input name="item-checklist-11" type="checkbox" class="tutor-form-check-input tutor-form-check-circle" readonly <?php echo esc_attr($has_attempted ? 'checked' : ''); ?> onclick="return false;" />
																	<span class="tutor-fs-7 tutor-color-secondary">
																		<?php echo esc_html($quiz->post_title); ?>
																	</span>
																</div>
															</div>
														<?php endforeach; ?>
													<?php endif; ?>
												</div>
												<div class="tutor-col-md-4">
													<div class="list-item-title tutor-fs-6 tutor-color-black tutor-py-12">
														<?php esc_html_e('Assignment', 'tutor-pro'); ?>
													</div>
													<?php if (is_array($assignments) && count($assignments)) : ?>
														<?php foreach ($assignments as $assignment) : ?>
															<div class="list-item-checklist">
																<div class="tutor-form-check">
																	<?php
																	$is_submitted = tutor_utils()->is_assignment_submitted($assignment->ID, $user_info->ID);
																	?>
																	<input name="item-checklist-11" type="checkbox" class="tutor-form-check-input tutor-form-check-circle" readonly <?php echo esc_attr($is_submitted ? 'checked' : ''); ?> onclick="return false;" />
																	<span class="tutor-fs-7 tutor-color-secondary">
																		<?php echo esc_html($assignment->post_title); ?>
																	</span>
																</div>
															</div>
														<?php endforeach; ?>
													<?php endif; ?>
												</div>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php else : ?>
			<?php tutor_utils()->tutor_empty_state(tutor_utils()->not_found_text()); ?>
		<?php endif; ?>
	</div>

	<div id="tutor-report-reviews">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24">
			<?php esc_html_e('Reviews', 'tutor-pro'); ?>
		</div>
		<?php if (is_array($reviews) && count($reviews)) : ?>
			<div class="tutor-table-responsive">
				<table class="tutor-table">
					<thead>
						<tr>
							<th width="10%">
								<?php esc_html_e('Date', 'tutor-pro'); ?>
							</th>
							<th width="25%">
								<?php esc_html_e('Course', 'tutor-pro'); ?>
							</th>
							<th width="50%">
								<?php esc_html_e('Feedback', 'tutor-pro'); ?>
							</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ($reviews as $review) : ?>
							<tr>
								<td>
									<div class="tutor-fs-7">
										<?php echo esc_html(tutor_i18n_get_formated_date($review->comment_date, get_option('date_format'))); ?>,
										<div class="tutor-fw-normal tutor-color-muted"><?php echo esc_html(tutor_i18n_get_formated_date($review->comment_date, get_option('time_format'))); ?></div>
									</div>
								</td>
								<td>
									<div class="tutor-fs-7">
										<?php echo esc_html(get_the_title($review->comment_post_ID)); ?>
									</div>
								</td>
								<td>
									<?php tutor_utils()->star_rating_generator_v2($review->rating, null, true); ?>
									<div class="tutor-fw-normal tutor-color-muted tutor-mt-8">
										<?php echo wp_kses_post($review->comment_content); ?>
									</div>
								</td>
								<td>
									<div class="tutor-text-right">
										<a href="<?php echo esc_url(get_the_permalink($review->comment_post_ID)); ?>" class="tutor-iconic-btn" target="_blank">
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
			<?php tutor_utils()->tutor_empty_state(tutor_utils()->not_found_text()); ?>
		<?php endif; ?>
	</div>
</div>