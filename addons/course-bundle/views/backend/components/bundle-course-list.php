<?php
/**
 * Bundle course list component
 *
 * @since 2.2.0
 *
 * @package TutorPro\CourseBundle\Views
 */

use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;


$bundle_id      = isset( $data['bundle_id'] ) ? $data['bundle_id'] : 0;
$courses        = BundleModel::get_bundle_course_ids( $bundle_id );
$total_enrolled = BundleModel::get_total_bundle_sold( $bundle_id );

?>
<?php if ( is_array( $courses ) && count( $courses ) ) : ?>
	<div class="tutor-form-label">
		<?php esc_html_e( 'Selected Courses', 'tutor-pro' ); ?>
	</div>
	<div class="tutor-grid tutor-grid-2">
		<?php
		foreach ( $courses as $course ) :
			$course_duration = get_tutor_course_duration_context( $course, true );
			$course_students = tutor_utils()->count_enrolled_users_by_course( $course );
			$thumbnail_url   = get_tutor_course_thumbnail_src( 'post-thumbnail', $course );
			$course_title    = get_the_title( $course );
			$course_url      = get_the_permalink( $course );
			$product_id      = tutor_utils()->get_course_product_id( $course );
			$course          = get_post( $course );
			?>
			<div class="tutor-card tutor-course-card">
				<div class="tutor-course-thumbnail">
					<a href="<?php echo esc_url( $course_url ); ?>" class="tutor-d-block" target="_blank">
						<div class="tutor-ratio tutor-ratio-16x9">
							<img class="tutor-card-image-top" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="Secret Tips Behind Perfect Diet &amp; Meal Plan" loading="lazy">
						</div>
					</a>
				</div>
				<div class="tutor-card-body">
					<span class="tutor-fs-6 tutor-color-secondary">
						<?php echo esc_html( tutor_i18n_get_formated_date( $course->post_update ) ); ?>
					</span>
					<h3 class="tutor-course-name tutor-fs-5 tutor-fw-medium" title="<?php echo esc_html( $course_title ); ?>">
						<a href="<?php echo esc_url( $course_url ); ?>" target="_blank">
							<?php echo esc_html( $course_title ); ?>
						</a>
					</h3>
					<div class="tutor-meta tutor-mt-12 tutor-mb-20">
						<div>
							<span class="tutor-meta-icon tutor-icon-user-line" area-hidden="true"></span>
							<span class="tutor-meta-value">
								<?php echo esc_html( $course_students ); ?>
							</span>
						</div>
						<div>
							<span class="tutor-icon-clock-line tutor-meta-icon" area-hidden="true"></span>
							<span class="tutor-meta-value">
								<?php echo tutor_utils()->clean_html_content( $course_duration ? $course_duration : 0 ); ?>
							</span>
						</div>
					</div>
				</div>
				<div class="tutor-card-footer tutor-card-footer tutor-d-flex tutor-justify-between tutor-align-center">
					<div>
						<span>
							<?php esc_html_e( 'Price: ', 'tutor-pro' ); ?>
						</span>
						<span class="tutor-meta-value">
						<?php
						$product = wc_get_product( $product_id );
						if ( $product ) {
                            echo $product->get_price_html(); //  phpcs:ignore
						}
						?>
						</span>
					</div>
				</div>
				<!-- don't show remove button if has enrollment  -->
				<?php if ( ! $total_enrolled ) : ?>
				<div class="tutor-bundle-course-delete">
					<a href="javascript:void(0);" class="tutor-btn tutor-btn-sm tutor-btn-danger tutor-remove-bundle-course" data-course-id="<?php echo esc_attr( $course->ID ); ?>">
						<span class="tutor-icon-times"></span>
						<?php esc_html_e( 'Remove', 'tutor-pro' ); ?>
					</a>
				</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

<?php else : ?>
	<?php Utils::course_bundle_empty_state(); ?>
<?php endif; ?>
