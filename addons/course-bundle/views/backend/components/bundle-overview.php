<?php
/**
 * Bundle overview component
 *
 * @since 2.2.0
 *
 * @package TutorPro\CourseBundle\Views
 */

use TutorPro\CourseBundle\Models\BundleModel;

$bundle_id = isset( $data['bundle_id'] ) ? $data['bundle_id'] : 0;
$overview  = BundleModel::get_bundle_meta( $bundle_id );
?>
<div class="tutor-courses-overview">
	<label class="tutor-form-label">
		<?php esc_html_e( 'Selection Overview', 'tutor-pro' ); ?>
	</label>
	<ul class="tutor-ul">
		<li class="tutor-d-flex tutor-mt-12">
			<span class="tutor-icon-book-open-o tutor-color-black tutor-mt-4 tutor-mr-12" aria-labelledby="Duration"></span>
			<span class="tutor-fs-6 tutor-color-secondary">
				<span class="tutor-meta-level tutor-color-secondary">
					<?php echo esc_html( $overview['total_courses'] ); ?>
				</span>
				<?php esc_html_e( 'Total Courses', 'tutor-pro' ); ?>					
			</span>
		</li>
		<li class="tutor-d-flex tutor-mt-12">
			<span class="tutor-icon-clock-line tutor-color-black tutor-mt-4 tutor-mr-12" aria-labelledby="Duration"></span>
			<span class="tutor-fs-6 tutor-color-secondary">
				<span class="tutor-meta-level tutor-color-secondary">
					<?php BundleModel::convert_seconds_into_human_readable_time( $overview['total_duration'] ?? 0 ); ?>
				</span>
				<?php esc_html_e( 'Total Duration', 'tutor-pro' ); ?>					
			</span>
		</li>
		<li class="tutor-d-flex tutor-mt-12">
			<span class="tutor-icon-video-camera-o tutor-color-black tutor-mt-4 tutor-mr-12" aria-labelledby="Duration"></span>
			<span class="tutor-fs-6 tutor-color-secondary">
				<span class="tutor-meta-level tutor-color-secondary">
					<?php echo esc_html( $overview['total_video_contents'] ?? 0 ); ?>
				</span>
				<?php esc_html_e( 'Video Contents', 'tutor-pro' ); ?>					
			</span>
		</li>

		<li class="tutor-d-flex tutor-mt-12">
			<span class="tutor-icon-download tutor-color-black tutor-mt-4 tutor-mr-12" aria-labelledby="Duration"></span>
			<span class="tutor-fs-6 tutor-color-secondary">
				<span class="tutor-meta-level tutor-color-secondary">
				<?php echo esc_html( $overview['total_resources'] ?? 0 ); ?>
				</span>
				<?php esc_html_e( 'Downloadable Resources', 'tutor-pro' ); ?>					
			</span>
		</li>

		<li class="tutor-d-flex tutor-mt-12">
			<span class="tutor-icon-circle-question-mark tutor-color-black tutor-mt-4 tutor-mr-12" aria-labelledby="Duration"></span>
			<span class="tutor-fs-6 tutor-color-secondary">
				<span class="tutor-meta-level tutor-color-secondary">
					<?php echo esc_html( $overview['total_quizzes'] ?? 0 ); ?>
				</span>
				<?php esc_html_e( 'Quiz Papers', 'tutor-pro' ); ?>					
			</span>
		</li>
		<!-- TODO Certificate will be used later on  -->
		<!-- <li class="tutor-d-flex tutor-mt-12"> -->
			<!-- <span class="tutor-icon-ribbon-o tutor-color-black tutor-mt-4 tutor-mr-12" aria-labelledby="Duration"></span> -->
			<!-- <span class="tutor-fs-6 tutor-color-secondary"> -->
				<?php // esc_html_e( 'Certificate of Completion', 'tutor-pro' ); ?>					
			<!-- </span> -->
		<!-- </li> -->
	</ul>
</div>
