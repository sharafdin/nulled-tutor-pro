<?php

/**
 * Grade Book
 *
 * @since v.1.4.2
 * @author themeum
 * @url https://themeum.com
 */

$gradebooks = tutor_utils()->get_gradebooks();
if ( ! tutor_utils()->count( $gradebooks ) ) {
	?>
	<div class="tutor-no-announcements">
		<div class="tutor-fs-6 tutor-fw-medium tutor-color-black"><?php _e( 'No grading system found.', 'tutor-pro' ); ?></div>
		<div class="tutor-fs-6 tutor-color-secondary tutor-mt-12"> <?php _e( 'No grading system has been defined to manage student grades. Please contact instructor or site administrator.', 'tutor-pro' ); ?> </div>
	</div>
	<?php
	return;
}

$grades           = get_generated_gradebook( 'all', $course_id );
$final_grade      = get_generated_gradebook( 'final', $course_id );
$assignment_grade = get_assignment_gradebook_by_course( $course_id );
$quiz_grade       = get_quiz_gradebook_by_course( $course_id );
$final_stat       = tutor_generate_grade_html( $final_grade, null );

$icon_mapping = array(
	'quiz'       => 'tutor-icon-circle-question-mark',
	'assignment' => 'tutor-icon-clipboard',
);

if ( ! $quiz_grade || ! tutor_utils()->count( $grades ) ) {
	tutor_utils()->tutor_empty_state( __( 'No Gradebook Data', 'tutor-pro' ) );
	return;
}
?>

<div class="tutor-gradebook">
	<div class="tutor-gradebook-finalgrade tutor-px-32 tutor-py-28">
		<div class="tutor-d-flex tutor-align-center">
			<?php $grade_color = isset($final_grade->grade_config) ? tutor_utils()->array_get( 'grade_color', maybe_unserialize( $final_grade->grade_config ) ) : ''; ?>
			<span class="tutor-gradebook-grade-badge tutor-gradebook-grade-badge-lg" style='<?php echo !empty($grade_color) ? "background-color: {$grade_color}; color: #FFFFFF; border-color: {$grade_color};" : ""; ?>'>
				<?php echo $final_stat['gradename'] ?? ''; ?>
			</span>

			<div class="tutor-ml-20">
				<div class="tutor-fs-6 tutor-color-muted tutor=mb-8"><?php _e("Final Grade", "tutor-pro"); ?></div>
				<div class="tutor-fs-5"><strong><?php echo $final_stat['gradepoint_only'] ?? ''; ?></strong> <?php _e("out of", "tutor-pro"); ?> <strong><?php echo isset( $final_stat['gradescale'] ) ? $final_stat['gradescale'] : ''; ?></strong></div>
			</div>
		</div>
	</div>

	<div class="tutor-gradebook-grades tutor-mt-24">
		<div class="tutor-gradebook-grades-head tutor-fs-6 tutor-color-secondary tutor-d-none tutor-d-lg-block tutor-px-16 tutor-mb-12">
			<div class="tutor-row">
				<div class="tutor-col-4">
					<span><?php _e("Title", "tutor-pro"); ?></span>
				</div>
				<div class="tutor-col">
					<span><?php _e("Total Grade", "tutor-pro"); ?></span>
				</div>
				<div class="tutor-col-auto">
					<span><?php _e("Result", "tutor-pro"); ?></span>
				</div>
			</div>
		</div>
	
		<div class="tutor-gradebook-grades-body">
			<?php foreach ( $grades as $key => $grade ) : ?>
				<?php $stat = tutor_generate_grade_html( $grade, null ); ?>
				<div class="tutor-gradebook-grade tutor-card tutor-p-12<?php echo ( $key > 0 ) ? ' tutor-mt-12' : ''; ?>">
					<div class="tutor-row tutor-align-center">
						<div class="tutor-col-lg-8 tutor-mb-12 tutor-mb-lg-0">
							<span class="tutor-fs-6 tutor-fw-medium tutor-color-black">
								<?php
									$for       = strtolower( $grade->result_for );
									$content_id= $for === 'quiz' ? $grade->quiz_id : $grade->assignment_id;
									$permalink = get_permalink( $content_id );
									$title     = get_the_title( $content_id );
			
									echo '<a class="tutor-color-secondary" href="' . $permalink . '" target="_blank">' .
											( isset( $icon_mapping[ $for ] ) ? '<i class="' . $icon_mapping[ $for ] . ' tutor-color-muted tutor-mr-8"></i>' : '' ) .
											get_the_title( $content_id )
										. '</a>';
								?>
							</span>
						</div>
	
						<div class="tutor-col">
							<?php if ( ! is_null( $stat ) ) : ?>
							<span class="tutor-fs-7 tutor-fw-medium tutor-color-secondary">
								<span><?php echo $stat['gradepoint_only']; ?></span> <?php _e("out of", "tutor-pro"); ?> <?php echo $stat['gradescale']; ?>
							</span>
							<?php endif; ?>
						</div>
	
						<div class="tutor-col-auto">
							<span class="tutor-px-16">
								<?php $grade_color = isset($grade->grade_config) ? tutor_utils()->array_get( 'grade_color', maybe_unserialize( $grade->grade_config ) ) : ''; ?>
								<span class="tutor-gradebook-grade-badge" style='<?php echo !empty($grade_color) ? "color: {$grade_color}; border-color: {$grade_color};" : ""; ?>'>
									<?php echo isset( $stat['gradename'] ) ? $stat['gradename'] : ''; ?>
								</span>
							</span>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>