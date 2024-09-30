<?php
/**
 * Assignment Modal Form - Course Builder
 *
 * @author themeum
 * @link https://themeum.com
 * @package TutorPro\Assignment
 * @since 1.0.0
 */

$assignment_id = $post->ID;
?>

<form class="tutor_assignment_modal_form">
	<input type="hidden" name="action" value="tutor_modal_create_or_update_assignment">
	<input type="hidden" name="assignment_id" value="<?php echo esc_attr( $post->ID ); ?>">
	<input type="hidden" name="current_topic_id" value="<?php echo esc_attr( $topic_id ); ?>">

	<div class="assignment-modal-form-wrap">
		<?php do_action( 'tutor_assignment_edit_modal_form_before', $post ); ?>

		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Assignment Title', 'tutor-pro' ); ?></label>
			<input type="text" name="assignment_title" class="tutor-form-control" value="<?php echo esc_html( wp_unslash( $post->post_title ) ); ?>"/>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Summary', 'tutor-pro' ); ?></label>
			<?php wp_editor( $post->post_content, 'tutor_assignments_modal_editor', array( 'editor_height' => 150 ) ); ?>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Attachments', 'tutor-pro' ); ?></label>
			<?php
				$attachments = tutor_utils()->get_attachments( $post->ID, '_tutor_assignment_attachments' );
				tutor_load_template_from_custom_path(
					tutor()->path . '/views/fragments/attachments.php',
					array(
						'name'        => 'tutor_assignment_attachments[]',
						'attachments' => $attachments,
						'size_below'  => false,
						'add_button'  => true,
					)
				);
				?>
		</div>

		<?php do_action( 'tutor_assignment_edit_modal_form_after_attachments', $assignment_id ); ?>

		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Time Limit', 'tutor-pro' ); ?></label>
			<div class="tutor-row">
				<div class="tutor-col-auto">
					<input class="tutor-form-control" type="number" min="0" name="assignment_option[time_duration][value]" value="<?php echo esc_attr( tutor_utils()->get_assignment_option( $assignment_id, 'time_duration.value', 0 ) ); ?>">
				</div>
				<div class="tutor-col-auto">
					<select class="tutor-form-control" name="assignment_option[time_duration][time]">
						<option value="weeks" <?php selected( 'weeks', tutor_utils()->get_assignment_option( $assignment_id, 'time_duration.time' ) ); ?>><?php esc_html_e( 'Weeks', 'tutor-pro' ); ?></option>
						<option value="days"  <?php selected( 'days', tutor_utils()->get_assignment_option( $assignment_id, 'time_duration.time' ) ); ?>><?php esc_html_e( 'Days', 'tutor-pro' ); ?></option>
						<option value="hours"  <?php selected( 'hours', tutor_utils()->get_assignment_option( $assignment_id, 'time_duration.time' ) ); ?>><?php esc_html_e( 'Hours', 'tutor-pro' ); ?></option>
					</select>
				</div>
			</div>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Total Points', 'tutor-pro' ); ?></label>
			<div class="tutor-row">
				<div class="tutor-col-auto">
					<input type="number" name="assignment_option[total_mark]" min="0" class="tutor-form-control" value="<?php echo esc_attr( tutor_utils()->get_assignment_option( $assignment_id, 'total_mark', 10 ) ); ?>">
				</div>
			</div>
			<div class="tutor-form-feedback">
				<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
				<div>
					<?php esc_html_e( 'Maximum points a student can score', 'tutor-pro' ); ?>
				</div>
			</div>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Minimum Pass Points', 'tutor-pro' ); ?></label>
			<div class="tutor-row">
				<div class="tutor-col-auto">
					<input type="number" min="0" name="assignment_option[pass_mark]" class="tutor-form-control" value="<?php echo esc_attr( tutor_utils()->get_assignment_option( $assignment_id, 'pass_mark', 5 ) ); ?>">
				</div>
			</div>
			<div class="tutor-form-feedback">
				<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
				<div>
					<?php esc_html_e( 'Minimum points required for the student to pass this assignment.', 'tutor-pro' ); ?>
				</div>
			</div>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Allow to upload files', 'tutor-pro' ); ?></label>
			<div class="tutor-row">
				<div class="tutor-col-auto">
					<input type="number" min="0" name="assignment_option[upload_files_limit]" class="tutor-form-control" value="<?php echo esc_attr( tutor_utils()->get_assignment_option( $assignment_id, 'upload_files_limit', 1 ) ); ?>">
				</div>
			</div>
			<div class="tutor-form-feedback">
				<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
				<div>
					<?php esc_html_e( 'Define the number of files that a student can upload in this assignment. Input 0 to disable the option to upload.', 'tutor-pro' ); ?>
				</div>
			</div>
		</div>
		
		<div class="tutor-mb-32">
			<label class="tutor-form-label"><?php esc_html_e( 'Maximum file size limit', 'tutor-pro' ); ?></label>
			<div class="tutor-row">
				<div class="tutor-col-auto">
					<input type="number" min="0" name="assignment_option[upload_file_size_limit]" class="tutor-form-control" value="<?php echo esc_attr( tutor_utils()->get_assignment_option( $assignment_id, 'upload_file_size_limit', 2 ) ); ?>">
				</div>
			</div>
			<div class="tutor-form-feedback">
				<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
				<div>
					<?php esc_html_e( 'Define maximum file size attachment in MB', 'tutor-pro' ); ?>
				</div>
			</div>
		</div>

		<?php do_action( 'tutor_assignment_edit_modal_form_after', $assignment_id ); ?>
	</div>
</form>
