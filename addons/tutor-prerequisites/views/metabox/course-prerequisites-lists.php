<?php
/**
 * Course Lists
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @since v.1.0.0
 */
?>

<div class="<?php echo ! is_admin() ? 'tutor-mb-8' : ''; ?>">
	<label class="tutor-course-field-label"><?php _e( 'Select course', 'tutor-pro' ); ?></label>
	<div class="tooltip-wrap tutor-d-block">
		<span class="tooltip-txt tooltip-right" style="text-align:left;">
			<?php _e( 'Selected course should be complete before enroll this course.', 'tutor-pro' ); ?>
		</span>
		<?php
			$current_course_id     = ! is_admin() && isset( $_GET['course_ID'] ) ? $_GET['course_ID'] : get_the_ID();
			$courses               = tutor_utils()->get_courses( array( $current_course_id ) );
			$savedPrerequisitesIDS = (array) maybe_unserialize( get_post_meta( $current_course_id, '_tutor_course_prerequisites_ids', true ) );
		?>
		<input type="hidden" name="_tutor_prerequisites_main_edit" value="true" />
		<select name="_tutor_course_prerequisites_ids[]" class="tutor_select2 tutor-form-select no-tutor-dropdown" style="min-width: 300px;" multiple="multiple">
			<?php
			foreach ( $courses as $course ) {
				$selected = in_array( $course->ID, $savedPrerequisitesIDS ) ? ' selected="selected" ' : '';
				echo "<option value='{$course->ID}' {$selected} >{$course->post_title}</option>";
			}
			?>
		</select>
	</div>
</div>
