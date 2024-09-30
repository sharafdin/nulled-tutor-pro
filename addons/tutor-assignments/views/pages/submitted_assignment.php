<?php
/**
 * Submitted Assignment Page
 *
 * @author themeum
 * @link https://themeum.com
 * @package TutorPro\Assignment
 * @since 1.8.0
 */

use TUTOR\Input;

$submitted_assignment = tutor_utils()->get_assignment_submit_info( $assignment_submitted_id );

if ( ! $submitted_assignment || ! is_object( $submitted_assignment ) ) {
	tutor_utils()->tutor_empty_state( __( 'Assignment not found or access denied!', 'tutor-pro' ) );
	return;
}

$max_mark        = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
$given_mark      = get_comment_meta( $assignment_submitted_id, 'assignment_mark', true );
$instructor_note = get_comment_meta( $assignment_submitted_id, 'instructor_note', true );

$assignment_page_url = admin_url( '/admin.php?page=tutor-assignments' );
$assignment_id       = $submitted_assignment->comment_post_ID;
?>
<div class="tutor-admin-wrap">
	<div class="tutor-wp-dashboard-header tutor-px-24 tutor-pt-32 tutor-pb-20">
		<div class="tutor-mb-8">
			<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( $assignment_page_url ); ?>"><i class="tutor-icon-previous tutor-mr-8" area-hidden="true"></i> <?php esc_html_e( 'Back', 'tutor-pro' ); ?></a>
		</div>

		<div class="tutor-fs-3 tutor-color-black tutor-fw-medium">
			<?php esc_html_e( get_the_title( $submitted_assignment->comment_post_ID ), 'tutor-pro' ); ?>
		</div>

		<div class="tutor-row tutor-gx-xl-5 tutor-fs-6">
			<div class="tutor-col-auto tutor-my-12">
				<span class="tutor-color-black tutor-fw-medium"><?php esc_html_e( 'Course', 'tutor-pro' ); ?>:</span> <span class="tutor-color-secondary"><?php esc_html_e( get_the_title( $submitted_assignment->comment_parent ) ); ?></span>
			</div>

			<div class="tutor-col-auto tutor-my-12">
				<span class="tutor-color-black tutor-fw-medium"><?php esc_html_e( 'Student', 'tutor-pro' ); ?>:</span> <span class="tutor-color-secondary"><?php esc_html_e( $submitted_assignment->comment_author ); ?></span>
			</div>

			<div class="tutor-col-auto tutor-my-12">
				<span class="tutor-color-black tutor-fw-medium"><?php esc_html_e( 'Submitted Date', 'tutor-pro' ); ?>:</span> <span class="tutor-color-secondary"><?php esc_html_e( tutor_utils()->convert_date_into_wp_timezone( $submitted_assignment->comment_date_gmt ) ); ?></span>
			</div>
		</div>
	</div>

	<div class="tutor-assignment-wrap">
		<div class="tutor-assignment-details-wrap">
			<div class="tutor-assignment-details">
				<div class="assignment-details">
					<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-24">
						<?php esc_html_e( 'Assignment', 'tutor-pro' ); ?>
					</div>
					<div class="tutor-fs-6 tutor-color-secondary tutor-entry-content tutor-mb-lg-60 tutor-mb-40">
						<?php
							$context      = 'post';
							$allowed_html = wp_kses_allowed_html( $context );
							echo wp_kses( wp_unslash( $submitted_assignment->comment_content ), $allowed_html );
						?>
					</div>
				</div>
				<?php
				$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
				if ( $attached_files ) {
					$attached_files = json_decode( $attached_files, true );
					if ( tutor_utils()->count( $attached_files ) ) {
						?>
					<div class="assignment-files">
						<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-24">
							<?php esc_html_e( 'Assignment File(s)', 'tutor-pro' ); ?>
						</div>
						<div class="tutor-assignment-files">
							<?php
								$upload_dir     = wp_get_upload_dir();
								$upload_baseurl = trailingslashit( tutor_utils()->array_get( 'baseurl', $upload_dir ) );
							foreach ( $attached_files as $attached_file ) {
								?>
									<div class="uploaded-files">
										<a href="<?php echo esc_url( $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ) ); ?>" target="_blank"><?php esc_html_e( tutor_utils()->array_get( 'name', $attached_file ) ); ?> <i class="tutor-icon-download"></i></a>
									</div>
									<?php
							}
							?>
						</div>
					</div>
						<?php
					}
				}
				?>
			</div>
			
			<div class="tutor-assignment-evaluation">
				<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-24">
					<?php esc_html_e( 'Evaluation', 'tutor-pro' ); ?>
				</div>
				<form action="" method="post" class="tutor-form-submit-through-ajax" data-toast_success_message="<?php esc_attr_e( 'Assignment evaluated', 'tutor-pro' ); ?>">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" value="tutor_evaluate_assignment_submission" name="tutor_action"/>
					<input type="hidden" value="<?php echo esc_attr( $assignment_submitted_id ); ?>" name="assignment_submitted_id"/>
					<?php
						$assignment_post_id = Input::get( 'post-id', 0, Input::TYPE_INT );
					?>
					
					<input type="hidden" name="assignment_post_id" value="<?php echo esc_attr( $assignment_post_id ); ?>">
					
					<div class="tutor-mb-32">
						<label for="evaluate_assignment_mark" class="tutor-form-label"><?php esc_html_e( 'Your Points', 'tutor-pro' ); ?></label>
						<div class="tutor-row tutor-align-center">
							<div class="tutor-col-auto">
								<input type="number" class="tutor-form-control" id="evaluate_assignment_mark" name="evaluate_assignment[assignment_mark]" value="<?php echo $given_mark ? esc_attr( $given_mark ) : 0; ?>" min="0" max="<?php echo esc_attr( $max_mark ); ?>" title="<?php esc_attr_e( 'Only number is allowed', 'tutor-pro' ); ?>" required>
							</div>
							<div class="tutor-col-auto tutor-fs-7 tutor-color-muted">
								<?php echo sprintf( __( 'Evaluate this assignment out of %s', 'tutor-pro' ), "{$max_mark}" ); ?>
							</div>
						</div>
					</div>

					<div class="tutor-form-group">
						<label for="evaluate_assignment_instructor" class="tutor-form-label">
							<?php esc_html_e( 'Write a feedback', 'tutor-pro' ); ?>
						</label>
						<textarea name="evaluate_assignment[instructor_note]" id="evaluate_assignment_instructor" class="tutor-form-control" rows="6"><?php esc_html_e( $instructor_note ); ?></textarea>
					</div>
					<div class="tutor-form-group">
						<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-lg"><?php esc_html_e( 'Evaluate this submission', 'tutor-pro' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
