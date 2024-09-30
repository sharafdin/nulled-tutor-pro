<?php
/**
 * Sticky header for using on course / bundle frontend builder
 *
 * @package TutorPro\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' ) || current_user_can( 'administrator' );
?>
<header class="tutor-dashboard-builder-header tutor-mb-32">
	<div class="tutor-container-fluid">
		<div class="tutor-row tutor-align-center">
			<div class="tutor-col-auto">
				<div class="tutor-dashboard-builder-header-left">
					<div class="tutor-dashboard-builder-logo">
						<?php $tutor_course_builder_logo_src = apply_filters( 'tutor_course_builder_logo_src', tutor()->url . 'assets/images/tutor-logo.png' ); ?>
						<img src="<?php echo esc_url( $tutor_course_builder_logo_src ); ?>" alt="">
					</div>
				</div>
			</div>

			<div class="tutor-col tutor-mt-12 tutor-mb-12">
				<div class="tutor-dashboard-builder-header-right tutor-d-flex tutor-align-center tutor-justify-end">
					<?php if ( 'draft' === $post->post_status || 'auto-draft' === $post->post_status ) : ?>
						<a href="#" id="tutor-course-save-draft" class="tutor-btn tutor-btn-ghost tutor-btn-md tutor-mr-20" name="course_submit_btn" value="save_course_as_draft">
							<i class="tutor-icon-save-line tutor-mr-8" area-hidden="true"></i>
							<?php esc_html_e( 'Save as Draft', 'tutor-pro' ); ?>
						</a>
					<?php endif; ?>

					<a class="tutor-btn tutor-btn-secondary tutor-btn-md" href="<?php echo esc_url( get_the_permalink( get_the_ID() ) ); ?>" target="_blank">
						<?php esc_html_e( 'Preview', 'tutor-pro' ); ?>
					</a>

					<?php if ( $can_publish_course ) : ?>
						<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor-ml-20 tutor-static-loader" type="submit" name="course_submit_btn" value="publish_course">
							<?php esc_html_e( 'Publish', 'tutor-pro' ); ?>
						</button>
					<?php else : ?>
						<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor-ml-20" type="submit" name="course_submit_btn" value="submit_for_review" title="<?php esc_html_e( 'Submit for Review', 'tutor-pro' ); ?>">
							<?php esc_html_e( 'Submit', 'tutor-pro' ); ?>
						</button>
					<?php endif; ?>

					<a href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>" class="tutor-iconic-btn tutor-iconic-btn-md tutor-ml-12" title="<?php esc_html_e( 'Exit', 'tutor-pro' ); ?>"><i class="tutor-icon-times" area-hidden="true"></i></a>
				</div>
			</div>
		</div>
	</div>
</header>
