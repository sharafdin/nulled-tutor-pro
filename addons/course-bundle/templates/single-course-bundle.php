<?php
/**
 * Template for single bundle details.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.2.0
 */

use TutorPro\CourseBundle\CustomPosts\ManagePostMeta;
use TutorPro\CourseBundle\MetaBoxes\BundlePrice;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\Utils;

global $is_enrolled;


$course_id         = get_the_ID();
$course_rating     = tutor_utils()->get_course_rating( $course_id );
$user_id           = get_current_user_id();
$post_author_id    = get_the_author_meta( 'ID' );
$is_bundle_creator = $user_id === $post_author_id;


if ( ! $is_enrolled ) {
	$is_enrolled = tutor_utils()->is_enrolled( $course_id, get_current_user_id() );
}

$is_public = \TUTOR\Course_List::is_public( $course_id );
$is_mobile = wp_is_mobile();

$enrollment_box_position = tutor_utils()->get_option( 'enrollment_box_position_in_mobile', 'bottom' );
if ( '-1' === $enrollment_box_position ) {
	$enrollment_box_position = 'bottom';
}

$student_must_login_to_view_course = tutor_utils()->get_option( 'student_must_login_to_view_course' );
tutor_utils()->tutor_custom_header();

if ( ! is_user_logged_in() && ! $is_public && $student_must_login_to_view_course ) {
	tutor_load_template( 'login' );
	tutor_utils()->tutor_custom_footer();
	return;
}

$thumb_url = get_tutor_course_thumbnail_src( 'post-thumbnail', $course_id );
?>
<div <?php tutor_post_class( 'tutor-full-width-course-top tutor-course-top-info tutor-page-wrap tutor-wrap-parent' ); ?>>
	<div class="tutor-course-details-page tutor-container">
		<?php
			tutor_load_template_from_custom_path( Utils::template_path( 'single/lead-info.php' ) );
		?>
		<div class="tutor-row tutor-gx-xl-5">
			<main class="tutor-col-xl-8">
				<div class="tutor-course-thumbnail">
					<img src="<?php echo esc_url( $thumb_url ); ?>" />
					<?php
					$bundle_course_ids = BundleModel::get_bundle_course_ids( $course_id );
					$ribbon_type       = ManagePostMeta::get_ribbon_type( $course_id );
					$bundle_sale_price = BundlePrice::get_bundle_sale_price( $course_id );
					?>
						<!-- Show bundle discount badge -->
						<?php if ( BundleModel::RIBBON_NONE !== $ribbon_type && $bundle_sale_price > 0 ) : ?>
						<div class="tutor-bundle-discount-info">
							<div class="tutor-bundle-save-text"><?php esc_html_e( 'SAVE', 'tutor-pro' ); ?></div>
							<div class="tutor-bundle-save-amount"><?php echo esc_html( BundlePrice::get_bundle_discount_by_ribbon( $course_id, $ribbon_type ) ); ?></div>
						</div>
						<?php endif; ?>
				</div>

				<?php if ( $is_mobile && 'top' === $enrollment_box_position ) : ?>
					<div class="tutor-mt-32">
						<?php if ( $is_bundle_creator ) : ?>
							<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-16"><?php esc_html_e( 'Bundle Overview', 'tutor-pro' ); ?></h3>
						<?php endif; ?>
						<?php tutor_load_template( 'single.course.course-entry-box' ); ?>
					</div>
				<?php endif; ?>

				<div class="tutor-course-details-tab tutor-mt-32">
					<div class="tutor-tab tutor-pt-24">

						<div>
							<?php
								tutor_course_content();
								tutor_course_benefits_html();
							?>

							<div class="tutor-mt-32">
								<?php require_once Utils::template_path( 'single/bundle-courses.php' ); ?>
							</div>
						</div>
					</div>
				</div>
			</main>
			<!-- Right sidebar -->
			<aside class="tutor-col-xl-4">
			<?php $sidebar_attr = apply_filters( 'tutor_course_details_sidebar_attr', '' ); ?>
			<div class="tutor-single-course-sidebar tutor-mt-40 tutor-mt-xl-0" <?php echo esc_attr( $sidebar_attr ); ?> >
					<?php if ( ( $is_mobile && 'bottom' === $enrollment_box_position ) || ! $is_mobile ) : ?>
						<?php if ( $is_bundle_creator ) : ?>
							<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-16"><?php esc_html_e( 'Bundle Overview', 'tutor-pro' ); ?></h3>
						<?php endif; ?>
						<?php tutor_load_template( 'single.course.course-entry-box' ); ?>
					<?php endif ?>

					<div class="tutor-single-course-sidebar-more tutor-mt-24">
						<?php tutor_course_tags_html(); ?>
					</div>
					<div class="tutor-bundle-author-list tutor-card tutor-card-md tutor-sidebar-card tutor-mt-24 tutor-py-24 tutor-px-32">
						<?php
							tutor_load_template_from_custom_path(
								Utils::template_path( 'single/bundle-authors.php' ),
								array( 'bundle_id' => $course_id )
							);
							?>
					</div>

				</div>
			</aside>
			<!-- End right sidebar -->
		</div>
	</div>
</div>

<?php
tutor_utils()->tutor_custom_footer();
